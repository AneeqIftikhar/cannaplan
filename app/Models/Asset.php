<?php

namespace CannaPlan\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Auth;
use DateTime;
Relation::morphMap([
    'current'=>'CannaPlan\Models\Current',
    'long_term'=>'CannaPlan\Models\LongTerm'
]);
/**
 * @property int $id
 * @property int $forecast_id
 * @property string $name
 * @property string $amount_type
 * @property int $amount
 * @property string $start_date
 * @property int $asset_duration_id
 * @property string $asset_duration_value
 * @property string $deleted_at
 * @property string $remember_token
 * @property string $created_at
 * @property string $updated_at
 * @property Forecast $forecast
 */
class Asset extends Model
{
    use SoftDeletes;
    protected $dates=['deleted_at'];
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'asset';

    public static function boot() {
        parent::boot();

        // create a event to happen on saving
        static::creating(function($table)  {
            $table->created_by = Auth::user()->id;
        });


    }

    /**
     * @var array
     */
    protected $fillable = ['name', 'amount_type', 'amount', 'start_date', 'asset_duration_id', 'asset_duration_type '];
    protected $guarded = ['id','forecast_id','created_by'];
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function forecast()
    {
        return $this->belongsTo('CannaPlan\Models\Forecast');
    }
    public function asset_duration()
    {
        return $this->morphTo();
    }
    public static function getAssetByForecast($id)
    {
        $forecast = Forecast::where('id', $id)->with(['company', 'assets', 'assets.asset_duration'])->first();
        $now = new DateTime($forecast->company->start_of_forecast);
        $total_current = array();
        $total_long_term = array();
        for ($j = 1; $j < 13; $j++) {
            $total_current['amount_m_' . $j] = null;
            $total_long_term['amount_m_' . $j] = null;
        }
        for ($j = 1; $j < 6; $j++) {
            $total_current['amount_y_' . $j] = null;
            $total_long_term['amount_y_' . $j] = null;
        }

        for ($i=0;$i<count($forecast->assets);$i++)
        {
            $date=date($forecast->assets[$i]['start_date']);
            $d2 = new DateTime($date);
            $diff_month=$now->diff($d2)->m;
            $diff_year=$now->diff($d2)->y;
            if($forecast->assets[$i]->amount_type=='one_time')
            {
                if($forecast->assets[$i]->asset_duration_type=='current')
                {
                    if($forecast->assets[$i]->asset_duration->month!=0)
                    {
                        $orignal_value=$forecast->assets[$i]->amount;
                        $decreasing_amount=$forecast->assets[$i]->amount;
                        $months=$forecast->assets[$i]->asset_duration->month;
                        $dep=$orignal_value / $months;
                        for ($j = 1; $j < 13; $j++) {
                            if($decreasing_amount>0)
                            {
                                if($diff_year==0 && $diff_month<$j)
                                {
                                    $new_value= $decreasing_amount - $dep;
                                    $decreasing_amount=$new_value;
                                    $forecast->assets[$i]['amount_m_' . $j]=round($decreasing_amount);
                                }
                                else
                                {
                                    $forecast->assets[$i]['amount_m_' . $j]=null;
                                }

                            }
                            else
                            {
                                $forecast->assets[$i]['amount_m_' . $j] = 0;
                            }

                        }
                        for ($j = 1; $j < 6; $j++)
                        {
                            if($diff_year<$j)
                            {
                                if($j==1)
                                {
                                    $forecast->assets[$i]['amount_y_'.$j] = round($decreasing_amount);
                                }
                                else
                                {
                                    $forecast->assets[$i]['amount_y_'.$j] =0;
                                }
                            }
                            else
                            {
                                $forecast->assets[$i]['amount_y_'.$j] = null;
                            }
                        }


                    }
                    else
                    {
                        for ($j = 1; $j < 13; $j++) {
                            if($diff_year==0 && $diff_month<$j)
                            {
                                $forecast->assets[$i]['amount_m_' . $j] = $forecast->assets[$i]->amount;
                            }
                            else
                            {
                                $forecast->assets[$i]['amount_m_' . $j]=null;
                            }

                        }
                        for ($j = 1; $j < 6; $j++)
                        {
                            if($diff_year<$j)
                            {
                                $forecast->assets[$i]['amount_y_'.$j]=$forecast->assets[$i]->amount;
                            }
                            else
                            {
                                $forecast->assets[$i]['amount_y_1'] = null;
                            }
                        }

                    }


                }
                else if($forecast->assets[$i]->asset_duration_type=='long_term')
                {
                    $orignal_value=$forecast->assets[$i]->amount;
                    $decreasing_amount=$forecast->assets[$i]->amount;
                    $year=$forecast->assets[$i]->asset_duration->year;
                    $dep_monthly=$orignal_value / ($year*12);
                    $dep_yearly=$orignal_value / ($year);
                    if($forecast->assets[$i]->asset_duration->will_sell==1)
                    {
                        $selling_date=new DateTime($forecast->assets[$i]->asset_duration->selling_date);
                        $selling_amount=$forecast->assets[$i]->selling_amount;
                        $asset_start_date=new DateTime($forecast->assets[$i]->start_date);
                        $selling_diff_month=$selling_date->diff($asset_start_date)->m;
                        $selling_diff_year=$selling_date->diff($asset_start_date)->y;
                    }
                    else
                    {
                        $selling_diff_month=-1;
                        $selling_diff_year=6;
                    }
                    for ($j = 1; $j < 13; $j++)
                    {
                        if($diff_year==0 && $diff_month<$j)
                        {
                            if($forecast->assets[$i]->asset_duration->will_sell==1)
                            {
                                if($selling_diff_year==0 && $selling_diff_month>=$j)
                                {
                                    $new_value= $decreasing_amount - $dep_monthly;
                                    $decreasing_amount=$new_value;
                                    $forecast->assets[$i]['amount_m_' . $j]=round($decreasing_amount);
                                }
                                else if($selling_diff_year>0)
                                {
                                    $new_value= $decreasing_amount - $dep_monthly;
                                    $decreasing_amount=$new_value;
                                    $forecast->assets[$i]['amount_m_' . $j]=round($decreasing_amount);
                                }
                                else
                                {
                                    $forecast->assets[$i]['amount_m_' . $j]=null;
                                }
                            }
                            else
                            {
                                $new_value= $decreasing_amount - $dep_monthly;
                                $decreasing_amount=$new_value;
                                $forecast->assets[$i]['amount_m_' . $j]=round($decreasing_amount);

                            }


                        }
                        else
                        {
                            $forecast->assets[$i]['amount_m_' . $j]=null;
                        }

                    }

                    for ($j = 1; $j < 6; $j++)
                    {
                        if($diff_year<$j && $selling_diff_year>=$j) {
                            if($j==1)
                            {
                                $forecast->assets[$i]['amount_y_1']=round($decreasing_amount);
                            }
                            else
                            {
                                if ($j < $year) {
                                    $new_value = $decreasing_amount - $dep_yearly;
                                    $decreasing_amount = $new_value;
                                    $forecast->assets[$i]['amount_y_' . $j] = round($decreasing_amount);
                                } else {
                                    $forecast->assets[$i]['amount_y_' . $j] = 0;
                                }
                            }

                        }
                        else
                        {
                            $forecast->assets[$i]['amount_y_' . $j] = null;
                        }

                    }

                }
            }
            else if ($forecast->assets[$i]->amount_type=='constant')
            {
                if($forecast->assets[$i]->asset_duration_type=='current')
                {
                    if($forecast->assets[$i]->asset_duration->month!=0)
                    {

                        $orignal_value=$forecast->assets[$i]->amount;
                        $decreasing_amount=$forecast->assets[$i]->amount;
                        $months=$forecast->assets[$i]->asset_duration->month;
                        $dep=$orignal_value / $months;
                        $final_amount=0;
                        for ($j = 1; $j < 13; $j++) {
                            if($diff_year==0 && $diff_month<$j) {
                                if ($j < $months+$diff_month) {
                                    $new_value = $decreasing_amount - $dep;
                                    $decreasing_amount = $new_value;
                                    $forecast->assets[$i]['amount_m_' . $j] = round($decreasing_amount);
                                    $decreasing_amount2 = $orignal_value;
                                    for ($k = 1; $k < $j-$diff_month; $k++) {
                                        $new_value2 = $decreasing_amount2 - $dep;
                                        $decreasing_amount2 = $new_value2;
                                        $forecast->assets[$i]['amount_m_' . $j] = $forecast->assets[$i]['amount_m_' . $j] + round($decreasing_amount2);

                                    }
                                    $final_amount = $forecast->assets[$i]['amount_m_' . $j];
                                } else {
                                    $forecast->assets[$i]['amount_m_' . $j] = $final_amount;
                                }
                            }
                            else
                            {
                                $forecast->assets[$i]['amount_m_' . $j] = null;
                            }

                        }

                        for($j=1 ; $j<6 ; $j++)
                        {
                            if($diff_year<$j)
                            {
                                if($j==1)
                                {
                                    $forecast->assets[$i]['amount_y_'.$j] = $final_amount;
                                }
                                else
                                {
                                        $temp = 0;
                                        $decreasing_amount2 = $orignal_value;
                                        for ($k = 1; $k < 13; $k++) {
                                            $new_value2 = $decreasing_amount2 - $dep;
                                            $decreasing_amount2 = $new_value2;
                                            $temp = $temp + round($decreasing_amount2);

                                        }
                                        $forecast->assets[$i]['amount_y_'.$j] = $temp;


                                }
                            }
                            else
                            {
                                $forecast->assets[$i]['amount_y_'.$j] = null;
                            }


                        }
                    }
                    else
                    {
                        for ($j = 1; $j < 13; $j++) {
                            if($diff_year==0 && $diff_month<$j) {
                                $forecast->assets[$i]['amount_m_' . $j] = $forecast->assets[$i]->amount * ($j-$diff_month);
                            }
                            else
                            {
                                $forecast->assets[$i]['amount_m_' . $j] = null;
                            }

                        }
                        for($j=1 ; $j<6 ; $j++)
                        {
                            $last_year_total=0;
                            if ($diff_year < $j)
                            {

                                if($j==1)
                                {
                                    $forecast->assets[$i]['amount_y_'.$j] = $forecast->assets[$i]['amount_m_12'];
                                }
                                else
                                {
                                    $last_year_total=$last_year_total+$forecast->assets[$i]['amount_y_'.($j-1)];
                                    $forecast->assets[$i]['amount_y_'.$j] = $last_year_total+$forecast->assets[$i]->amount * 12;

                                }

                            }
                            else
                            {
                                $forecast->assets[$i]['amount_y_'.$j] = null;
                            }
                        }

                    }


                }
                else if($forecast->assets[$i]->asset_duration_type=='long_term')
                {
                    $monthly_array=array();
                    for ($j = 1; $j < 61; $j++)
                    {
                        $monthly_array[$j] = 0;
                    }
                    $orignal_value=$forecast->assets[$i]->amount;
                    $decreasing_amount=$forecast->assets[$i]->amount;
                    $year=$forecast->assets[$i]->asset_duration->year;
                    $dep_monthly=$orignal_value / ($year*12);
                    $final_amount=0;
                    for ($j = 1; $j < 61; $j++)
                    {
                        if($diff_year==0 && $diff_month<$j)
                        {
                            $new_value= $decreasing_amount - $dep_monthly;
                            $decreasing_amount=$new_value;
                            $monthly_array[$j]=0;
                            if($decreasing_amount>=0)
                            {
                                $monthly_array[$j]=round($decreasing_amount);
                            }

                            $decreasing_amount2=$orignal_value;
                            for ($k = 1; $k < $j-$diff_month; $k++) {
                                if($decreasing_amount2 - $dep_monthly>=0)
                                {
                                    $new_value2= $decreasing_amount2 - $dep_monthly;
                                    $decreasing_amount2=$new_value2;

                                    $monthly_array[$j]=$monthly_array[$j]+round($decreasing_amount2);
                                }


                            }
                            $final_amount=$monthly_array[$j];
                        }
                        else
                        {
                            $monthly_array[$j] = null;
                        }

                    }
//                    return $monthly_array;
                    for ($j = 1; $j < 13; $j++)
                    {
                        if($diff_year==0 && $diff_month<$j)
                        {
                            $forecast->assets[$i]['amount_m_' . $j]=$monthly_array[$j];
                        }
                        else
                        {
                            $forecast->assets[$i]['amount_m_' . $j] = null;
                        }

                    }
                    for($j=1 ; $j<6 ; $j++) {
                        if ($diff_year < $j) {
                                $forecast->assets[$i]['amount_y_' . $j] = $monthly_array[$j*12];
                            }
                         else {
                            $forecast->assets[$i]['amount_y_' . $j] = null;
                        }
                    }


                }
            }
            for ($j = 1; $j < 13; $j++) {
                if($forecast->assets[$i]['amount_m_' . $j])
                {
                    if($forecast->assets[$i]->asset_duration_type=='long_term')
                    {
                        $total_long_term['amount_m_' . $j]=$total_long_term['amount_m_' . $j]+$forecast->assets[$i]['amount_m_' . $j];
                    }
                    else
                    {
                        $total_current['amount_m_' . $j] = $total_current['amount_m_' . $j]+ $forecast->assets[$i]['amount_m_' . $j];

                    }
                }

            }
            for ($j = 1; $j < 6; $j++) {
                if($forecast->assets[$i]['amount_y_' . $j])
                {
                    if($forecast->assets[$i]->asset_duration_type=='long_term')
                    {
                        $total_long_term['amount_y_' . $j]=$total_long_term['amount_y_' . $j]+$forecast->assets[$i]['amount_y_' . $j];
                    }
                    else
                    {
                        $total_current['amount_y_' . $j] = $total_current['amount_y_' . $j]+ $forecast->assets[$i]['amount_y_' . $j];

                    }

                }
            }
        }

        $forecast['total_current'] = $total_current;
        $forecast['total_long_term'] = $total_long_term;
        return $forecast;
    }
}
