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
        $now=date('Y-m-d',time());
        $now = new DateTime($now);
        $total_current = array();
        $total_long_term = array();
        for ($j = 1; $j < 13; $j++) {
            $total_current['amount_m_' . $j] = 0;
            $total_long_term['amount_m_' . $j] = 0;
        }
        for ($j = 1; $j < 6; $j++) {
            $total_current['amount_y_' . $j] = 0;
            $total_long_term['amount_y_' . $j] = 0;
        }
        for ($i=0;$i<count($forecast->assets);$i++)
        {
            $date=date($forecast->assets[$i]['start_date']);
            $d2 = new DateTime($date);
            $diff=$now->diff($d2)->m;
            if($forecast->assets[$i]->amount_type=='one_time')
            {
                if($forecast->assets[$i]->asset_duration_type=='current')
                {
                    if($forecast->assets[$i]->asset_duration->month!=0)
                    {
                        /*
                          double original_value = 103;
                            double current_value = original_value;
                            double number_of_periods = 11;
                            double new_value;
                            double dep = original_value / number_of_periods;
                            for (int i = 1; i <=number_of_periods; i++)
                            {
                                new_value = current_value - dep;
                                current_value = new_value;
                                cout << dep << " - " << round(current_value) << endl;
                            }
                         */
                        $orignal_value=$forecast->assets[$i]->amount;
                        $decreasing_amount=$forecast->assets[$i]->amount;
                        $months=$forecast->assets[$i]->asset_duration->month;
                        $dep=$orignal_value / $months;
                        for ($j = 1; $j < 13; $j++) {
                            if($decreasing_amount>0)
                            {
                                if($diff<$j)
                                {
                                    $new_value= $decreasing_amount - $dep;
                                    $decreasing_amount=$new_value;
                                    $forecast->assets[$i]['amount_m_' . $j]=round($decreasing_amount);
                                }
                                else
                                {
                                    $forecast->assets[$i]['amount_m_' . $j]=0;
                                }

                            }
                            else
                            {
                                $forecast->assets[$i]['amount_m_' . $j] = 0;
                            }

                        }
                        $forecast->assets[$i]['amount_y_1'] = 0;
                        $forecast->assets[$i]['amount_y_2'] = 0;
                        $forecast->assets[$i]['amount_y_3'] = 0;
                        $forecast->assets[$i]['amount_y_4'] = 0;
                        $forecast->assets[$i]['amount_y_5'] = 0;
                    }
                    else
                    {
                        for ($j = 1; $j < 13; $j++) {
                            if($diff<$j)
                            {
                                $forecast->assets[$i]['amount_m_' . $j] = $forecast->assets[$i]->amount;
                            }
                            else
                            {
                                $forecast->assets[$i]['amount_m_' . $j]=0;
                            }

                        }
                        $forecast->assets[$i]['amount_y_1'] = $forecast->assets[$i]->amount;
                        $forecast->assets[$i]['amount_y_2'] = $forecast->assets[$i]->amount;
                        $forecast->assets[$i]['amount_y_3'] = $forecast->assets[$i]->amount;
                        $forecast->assets[$i]['amount_y_4'] = $forecast->assets[$i]->amount;
                        $forecast->assets[$i]['amount_y_5'] = $forecast->assets[$i]->amount;
                    }
                    for ($j = 1; $j < 13; $j++) {
                        $total_current['amount_m_' . $j] = $total_current['amount_m_' . $j]+ $forecast->assets[$i]['amount_m_' . $j];
                    }
                    for ($j = 1; $j < 6; $j++) {
                        $total_current['amount_y_' . $j] = $total_current['amount_y_' . $j]+ $forecast->assets[$i]['amount_y_' . $j];
                    }

                }
                else if($forecast->assets[$i]->asset_duration_type=='long_term')
                {
                    $orignal_value=$forecast->assets[$i]->amount;
                    $decreasing_amount=$forecast->assets[$i]->amount;
                    $year=$forecast->assets[$i]->asset_duration->year;
                    $dep_monthly=$orignal_value / ($year*12);
                    $dep_yearly=$orignal_value / ($year);
                    for ($j = 1; $j < 13; $j++)
                    {
                        if($diff<$j)
                        {
                            $new_value= $decreasing_amount - $dep_monthly;
                            $decreasing_amount=$new_value;
                             round($decreasing_amount);
                        }
                        else
                        {
                            $forecast->assets[$i]['amount_m_' . $j]=0;
                        }

                    }
                    $forecast->assets[$i]['amount_y_1']=round($decreasing_amount);
                    for ($j = 2; $j < 6; $j++)
                    {
                        if($j<$year)
                        {
                            $new_value= $decreasing_amount - $dep_yearly;
                            $decreasing_amount=$new_value;
                            $forecast->assets[$i]['amount_y_' . $j]=round($decreasing_amount);
                        }
                        else
                        {
                            $forecast->assets[$i]['amount_y_' . $j]=0;
                        }

                    }
                    for ($j = 1; $j < 13; $j++) {
                        $total_long_term['amount_m_' . $j] = $total_long_term['amount_m_' . $j]+ $forecast->assets[$i]['amount_m_' . $j];
                    }
                    for ($j = 1; $j < 6; $j++) {
                        $total_long_term['amount_y_' . $j] = $total_long_term['amount_y_' . $j]+ $forecast->assets[$i]['amount_y_' . $j];
                    }
                }
            }
            else if ($forecast->assets[$i]->amount_type=='constant')
            {
                if($forecast->assets[$i]->asset_duration_type=='current')
                {
                    if($forecast->assets[$i]->asset_duration->month!=0)
                    {
                        /*
                          double original_value = 103;
                            double current_value = original_value;
                            double number_of_periods = 11;
                            double new_value;
                            double dep = original_value / number_of_periods;
                            for (int i = 1; i <=number_of_periods; i++)
                            {
                                new_value = current_value - dep;
                                current_value = new_value;
                                cout << dep << " - " << round(current_value) << endl;
                            }
                         */
                        $orignal_value=$forecast->assets[$i]->amount;
                        $decreasing_amount=$forecast->assets[$i]->amount;
                        $decreasing_amount2=$forecast->assets[$i]->amount;
                        $months=$forecast->assets[$i]->asset_duration->month;
                        $dep=$orignal_value / $months;
                        $final_amount=0;
                        for ($j = 1; $j < 13; $j++) {
                            if($j<$months)
                            {
                                $new_value= $decreasing_amount - $dep;
                                $decreasing_amount=$new_value;
                                $forecast->assets[$i]['amount_m_' . $j]=round($decreasing_amount);
                                $decreasing_amount2=$orignal_value;
                                for ($k = 1; $k < $j; $k++) {
                                    $new_value2= $decreasing_amount2 - $dep;
                                    $decreasing_amount2=$new_value2;
                                    $forecast->assets[$i]['amount_m_' . $j]=$forecast->assets[$i]['amount_m_' . $j]+round($decreasing_amount2);
                                    $final_amount=$forecast->assets[$i]['amount_m_' . $j];
                                }

                            }
                            else
                            {
                                $forecast->assets[$i]['amount_m_' . $j] = $final_amount;
                            }

                        }
                        $forecast->assets[$i]['amount_y_1'] = $final_amount;
                        $forecast->assets[$i]['amount_y_2'] = $final_amount;
                        $forecast->assets[$i]['amount_y_3'] = $final_amount;
                        $forecast->assets[$i]['amount_y_4'] = $final_amount;
                        $forecast->assets[$i]['amount_y_5'] = $final_amount;
                    }
                    else
                    {
                        for ($j = 1; $j < 13; $j++) {
                            $forecast->assets[$i]['amount_m_' . $j] = $forecast->assets[$i]->amount * $j;
                        }
                        $forecast->assets[$i]['amount_y_1'] = $forecast->assets[$i]->amount * 12;
                        $forecast->assets[$i]['amount_y_2'] = $forecast->assets[$i]->amount * 12;
                        $forecast->assets[$i]['amount_y_3'] = $forecast->assets[$i]->amount * 12;
                        $forecast->assets[$i]['amount_y_4'] = $forecast->assets[$i]->amount * 12;
                        $forecast->assets[$i]['amount_y_5'] = $forecast->assets[$i]->amount * 12;
                    }
                    for ($j = 1; $j < 13; $j++) {
                        $total_current['amount_m_' . $j] = $total_current['amount_m_' . $j]+ $forecast->assets[$i]['amount_m_' . $j];
                    }
                    for ($j = 1; $j < 6; $j++) {
                        $total_current['amount_y_' . $j] = $total_current['amount_y_' . $j]+ $forecast->assets[$i]['amount_y_' . $j];
                    }

                }
                else if($forecast->assets[$i]->asset_duration_type=='long_term')
                {
                    $orignal_value=$forecast->assets[$i]->amount;
                    $decreasing_amount=$forecast->assets[$i]->amount;
                    $year=$forecast->assets[$i]->asset_duration->year;
                    $dep_monthly=$orignal_value / ($year*12);
                    for ($j = 1; $j < 13; $j++)
                    {
                        $new_value= $decreasing_amount - $dep_monthly;
                        $decreasing_amount=$new_value;
                        $forecast->assets[$i]['amount_m_' . $j]=round($decreasing_amount);
                        $decreasing_amount2=$orignal_value;
                        for ($k = 1; $k < $j; $k++) {
                            $new_value2= $decreasing_amount2 - $dep_monthly;
                            $decreasing_amount2=$new_value2;
                            $forecast->assets[$i]['amount_m_' . $j]=$forecast->assets[$i]['amount_m_' . $j]+round($decreasing_amount2);
                            $final_amount=$forecast->assets[$i]['amount_m_' . $j];
                        }
                    }
                    $forecast->assets[$i]['amount_y_1']=$final_amount;
                    $forecast->assets[$i]['amount_y_2'] = $final_amount;
                    $forecast->assets[$i]['amount_y_3'] = $final_amount;
                    $forecast->assets[$i]['amount_y_4'] = $final_amount;
                    $forecast->assets[$i]['amount_y_5'] = $final_amount;

                    for ($j = 1; $j < 13; $j++) {
                        $total_long_term['amount_m_' . $j] = $total_long_term['amount_m_' . $j]+ $forecast->assets[$i]['amount_m_' . $j];
                    }
                    for ($j = 1; $j < 6; $j++) {
                        $total_long_term['amount_y_' . $j] = $total_long_term['amount_y_' . $j]+ $forecast->assets[$i]['amount_y_' . $j];
                    }
                }
            }
        }

        $forecast['total_current'] = $total_current;
        $forecast['total_long_term'] = $total_long_term;
        return $forecast;
    }
}
