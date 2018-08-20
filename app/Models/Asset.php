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
        $total_current['rows_hidden'] = false;
        $total_long_term['rows_hidden'] = false;
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
            if($diff_year>0)
                $diff_month=0;
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
                        $forecast->assets[$i]->asset_duration['dep_monthly']=$dep;
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
                    $forecast->assets[$i]->asset_duration['dep_monthly']=$dep_monthly;
                    $forecast->assets[$i]->asset_duration['dep_yearly']=$dep_yearly;
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
                                if($selling_diff_year==0 && ($selling_diff_month+$diff_month)>=$j)
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
                        $forecast->assets[$i]->asset_duration['dep_monthly']=$dep;
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
                        //yearly new
                        $monthly_array=array();
                        for ($j = 1; $j < 61; $j++)
                        {
                            $monthly_array[$j] = 0;
                        }
                        $orignal_value=$forecast->assets[$i]->amount;
                        $decreasing_amount=$forecast->assets[$i]->amount;
                        $months=$forecast->assets[$i]->asset_duration->month;
                        $dep=$orignal_value / $months;
                        $final_amount=0;

                        for ($j = 1; $j < 61; $j++) {
                            if($diff_year<($j/12) && $diff_month<$j) {
                                    $new_value = $decreasing_amount - $dep;
                                    $decreasing_amount = $new_value;
                                    if($decreasing_amount >= 0)
                                        $monthly_array[$j] = round($decreasing_amount);
                                    $decreasing_amount2 = $orignal_value;
                                    for ($k = 1; $k < $j-($diff_month+($diff_year*12)); $k++) {
                                        $new_value2 = $decreasing_amount2 - $dep;
                                        $decreasing_amount2 = $new_value2;
                                        if($decreasing_amount2 >= 0)
                                            $monthly_array[$j] =  $monthly_array[$j] + round($decreasing_amount2);

                                    }
                                    $final_amount =  $monthly_array[$j];

                            }
                            else
                            {
                                $monthly_array[$j] = null;
                            }

                        }

                        for($j=1 ; $j<6 ; $j++)
                        {
                            $forecast->assets[$i]['amount_y_'.$j] = $monthly_array[$j*12];
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
                    $forecast->assets[$i]->asset_duration['dep_monthly']=$dep_monthly;
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
            $intial_assets=InitialBalanceSettings::calculatePreviousAssetByForecast($id);
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
        $intial_assets=InitialBalanceSettings::calculatePreviousAssetByForecast($id);
        for ($j = 1; $j < 13; $j++) {
            if($intial_assets['long_term']['amount_m_' . $j]!==null)
            {
                $total_long_term['amount_m_' . $j]=$total_long_term['amount_m_' . $j]+$intial_assets['long_term']['amount_m_' . $j];
            }
            if($intial_assets['current']['amount_m_' . $j]!==null)
            {
                $total_current['amount_m_' . $j] = $total_current['amount_m_' . $j]+ $intial_assets['current']['amount_m_' . $j];

            }
        }
        for ($j = 1; $j < 6; $j++) {
            if($intial_assets['long_term']['amount_y_' . $j]!==null)
            {
                $total_long_term['amount_y_' . $j]=$total_long_term['amount_y_' . $j]+$intial_assets['long_term']['amount_y_' . $j];
            }
            if($intial_assets['current']['amount_y_' . $j]!==null)
            {
                $total_current['amount_y_' . $j] = $total_current['amount_y_' . $j]+ $intial_assets['current']['amount_y_' . $j];

            }
        }


        $forecast['total_current'] = $total_current;
        $forecast['total_long_term'] = $total_long_term;
        return $forecast;
    }
    public static function getDepreciationOfAssetByForecast($id)
    {
        $forecast=Forecast::where('id',$id)->with('company')->first();
        $assets=Asset::getAssetByForecast($id);
        $depreciation_and_amortization_current=array();
        $depreciation_and_amortization_long_term=array();
        $current_include_status=false;
        $long_term_include_status=false;

        for($i=1;$i<13;$i++)
        {
            $depreciation_and_amortization_current['amount_m_'.$i] = null;
            $depreciation_and_amortization_long_term['amount_m_'.$i] = null;
        }
        for($i=1;$i<6;$i++)
        {
            $depreciation_and_amortization_current['amount_y_'.$i] = null;
            $depreciation_and_amortization_long_term['amount_y_'.$i] = null;
        }

        foreach ($assets->assets as $asset)
        {
            $include_asset_status=true;
            $total_months = 0;
            $total_years=0;
            if($asset->amount_type=="constant")
            {
                if($asset->asset_duration_type=='current')
                {

                    $current_include_status=true;
                    $total_months=0;
                    for($i=1;$i<13;$i++)
                    {
                        if($asset['amount_m_'.$i])
                        {
                            $total_months++;
                            if($i==1)
                            {
                                $depreciation_and_amortization_current['amount_m_'.$i]=$depreciation_and_amortization_current['amount_m_'.$i]+((($i)*$asset->amount)-($asset['amount_m_'.($i)]));
                            }
                            else
                            {
                                $depreciation_and_amortization_current['amount_m_'.$i]=$depreciation_and_amortization_current['amount_m_'.$i]+(($asset->amount)-($asset['amount_m_'.($i)]-$asset['amount_m_'.($i-1)]));
                            }

                        }

                    }

                    for($i=1;$i<6;$i++)
                    {
                        if($asset['amount_y_'.$i]) {

                            if ($i == 1) {
                                $depreciation_and_amortization_current['amount_y_' . $i] = $depreciation_and_amortization_current['amount_y_' . $i] + (($total_months * $asset->amount) - ($asset['amount_y_' . ($i)]));
                            } else {
                                $depreciation_and_amortization_current['amount_y_' . $i] = $depreciation_and_amortization_current['amount_y_' . $i] + (12 * $asset->amount - ($asset['amount_y_' . ($i)] - $asset['amount_y_' . ($i - 1)]));
                            }
                        }

                    }
                }
                else//longterm
                {
                    $long_term_include_status=true;
                    $total_months=0;
                    for($i=1;$i<13;$i++)
                    {
                        if($asset['amount_m_'.$i])
                        {
                            $total_months++;
                            if($i==1)
                            {
                                $depreciation_and_amortization_long_term['amount_m_'.$i]=$depreciation_and_amortization_long_term['amount_m_'.$i]+((($i)*$asset->amount)-($asset['amount_m_'.($i)]));
                            }
                            else
                            {
                                $depreciation_and_amortization_long_term['amount_m_'.$i]=$depreciation_and_amortization_long_term['amount_m_'.$i]+(($asset->amount)-($asset['amount_m_'.($i)]-$asset['amount_m_'.($i-1)]));
                            }

                        }

                    }

                    for($i=1;$i<6;$i++)
                    {
                        if($asset['amount_y_'.$i]) {

                            if ($i == 1) {
                                $depreciation_and_amortization_long_term['amount_y_' . $i] = $depreciation_and_amortization_long_term['amount_y_' . $i] + (($total_months * $asset->amount) - ($asset['amount_y_' . ($i)]));
                            } else {
                                $depreciation_and_amortization_long_term['amount_y_' . $i] = $depreciation_and_amortization_long_term['amount_y_' . $i] + (12 * $asset->amount - ($asset['amount_y_' . ($i)] - $asset['amount_y_' . ($i - 1)]));
                            }
                        }

                    }
                }

            }
            else if ($asset->amount_type=="one_time")
            {
                if($asset->asset_duration_type=='current')
                {
                    $current_include_status=true;
                    $start_of_forecast= new DateTime($forecast->company['start_of_forecast']);
                    $date=date($asset['start_date']);
                    $d1 = new DateTime($date);
                    $diff_month=$start_of_forecast->diff($d1)->m;
                    $diff_year=$start_of_forecast->diff($d1)->y;
                    if($diff_year==0)
                    {
                        for($i=1;$i<13;$i++)
                        {
                            if($diff_month<$i && ($i-$diff_month)<=$asset['asset_duration']['month'])
                            {
                                $depreciation_and_amortization_current['amount_m_'.$i]=$depreciation_and_amortization_current['amount_m_'.$i]+round($asset['asset_duration']['dep_monthly']);
                            }
                        }
                        if($asset['asset_duration']['month']+$diff_month>12)
                        {
                            $depreciation_and_amortization_current['amount_y_1']=$depreciation_and_amortization_current['amount_y_1']+round($asset['asset_duration']['dep_monthly']*(12-$diff_month));
                            $depreciation_and_amortization_current['amount_y_2']=$depreciation_and_amortization_current['amount_y_2']+round($asset['asset_duration']['dep_monthly']*($asset['asset_duration']['month']+$diff_month-12));

                        }
                        else {
                            $depreciation_and_amortization_current['amount_y_1']=$depreciation_and_amortization_current['amount_y_1']+round($asset['asset_duration']['dep_monthly']*$asset['asset_duration']['month']);

                        }



                    }
                    else
                    {
                        $depreciation_and_amortization_current['amount_y_'.($diff_year+1)]=$depreciation_and_amortization_current['amount_y_'.($diff_year+1)]+($asset['asset_duration']['dep_monthly']*($asset['asset_duration']['month']));

                    }


                }
                else if($asset->asset_duration_type=='long_term') {
                    $long_term_include_status=true;
                    $start_of_forecast = new DateTime($forecast->company['start_of_forecast']);
                    $date = date($asset['start_date']);
                    $d1 = new DateTime($date);
                    $diff_month = $start_of_forecast->diff($d1)->m;
                    $diff_year = $start_of_forecast->diff($d1)->y;
                    $date2 = date($asset['asset_duration']['selling_date']);
                    $d2 = new DateTime($date2);
                    $sell_month = $start_of_forecast->diff($d2)->m;
                    $sell_year = $start_of_forecast->diff($d2)->y;
                    $dep_year=$asset['asset_duration']['year'];
                    $total_years=0;
                    $total_months=0;
                    if($sell_year>0)
                    {
                        $sell_month=12;
                    }
                    if($diff_year>0)
                    {
                        $diff_month=0;
                    }
                    if ($diff_year == 0) {
                        if ($asset['asset_duration']['will_sell'] == 1) {

                            $total_dep=0;
                            for ($i = 1; $i < 13; $i++) {
                                if ($diff_month < $i && $i<=$sell_month) {
                                    $depreciation_and_amortization_long_term['amount_m_' . $i] = $depreciation_and_amortization_long_term['amount_m_' . $i] + round($asset['asset_duration']['dep_monthly']);
                                    $total_months++;
                                }
                            }
                            if($total_months != 0)
                            {
                                $depreciation_and_amortization_long_term['amount_y_1'] = $depreciation_and_amortization_long_term['amount_y_1'] + round($asset['asset_duration']['dep_monthly'] * $total_months);
                                $total_years++;
                                $total_dep=$total_dep+round($asset['asset_duration']['dep_monthly'] * $total_months);
                            }
                            for ($i = 2; $i < 6; $i++) {

                                if($i<=$sell_year  && $total_years<$dep_year)
                                {
                                    $depreciation_and_amortization_long_term['amount_y_' . $i] = $depreciation_and_amortization_long_term['amount_y_' . $i] + round($asset['asset_duration']['dep_monthly'] * 12);
                                    $total_years++;
                                    $total_dep=$total_dep+round($asset['asset_duration']['dep_monthly'] * 12);
                                }
                                else if($i<=$sell_year  && $total_years==$dep_year && $total_months<12)
                                {
                                    $depreciation_and_amortization_long_term['amount_y_' . $i] = $depreciation_and_amortization_long_term['amount_y_' . $i] + round($asset['asset_duration']['dep_monthly'] *(12-$total_months));
                                    $total_years++;
                                    $total_dep=$total_dep+round($asset['asset_duration']['dep_monthly'] *(12-$total_months));
                                }
                                else if($i-1==$sell_year && $total_years<=$dep_year && $total_months<12)
                                {
                                    $depreciation_and_amortization_long_term['amount_y_' . $i] = $depreciation_and_amortization_long_term['amount_y_' . $i] + round($asset['asset_duration']['dep_monthly'] * (12-$total_months));
                                    $total_dep=$total_dep+round($asset['asset_duration']['dep_monthly'] *(12-$total_months));
                                }
                            }

                        } else {
                            $total_months = 0;
                            for ($i = 1; $i < 13; $i++) {
                                if ($diff_month < $i) {
                                    $depreciation_and_amortization_long_term['amount_m_' . $i] = $depreciation_and_amortization_long_term['amount_m_' . $i] + round($asset['asset_duration']['dep_monthly']);
                                    $total_months++;
                                }
                            }
                            for ($i = 1; $i < 6; $i++) {
                                if ($i == 1) {
                                    $depreciation_and_amortization_long_term['amount_y_' . $i] = $depreciation_and_amortization_long_term['amount_y_' . $i] + round($asset['asset_duration']['dep_monthly'] * $total_months);
                                } else {
                                    $depreciation_and_amortization_long_term['amount_y_' . $i] = $depreciation_and_amortization_long_term['amount_y_' . $i] + round($asset['asset_duration']['dep_monthly'] * 12);
                                }
                            }

                        }
                    } else {
                        if ($asset['asset_duration']['will_sell'] == 1) {
                            $total_years=0;
                            $total_dep=0;
                            $include_asset_gain_loss_status=true;
                            for ($i = 2; $i < 6; $i++) {
                                if($i<=$sell_year  && $total_years<$dep_year)
                                {
                                    $depreciation_and_amortization_long_term['amount_y_' . $i] = $depreciation_and_amortization_long_term['amount_y_' . $i] + round($asset['asset_duration']['dep_monthly'] * 12);
                                    $total_dep=$total_dep+round($asset['asset_duration']['dep_monthly'] * 12);
                                    $total_years++;
                                }
                            }
                        } else {
                            for ($i = 2; $i < 6; $i++) {
                                if($diff_year<$i)
                                    $depreciation_and_amortization_long_term['amount_y_' . $i] = $depreciation_and_amortization_long_term['amount_y_' . $i] + round($asset['asset_duration']['dep_monthly'] * 12);
                            }
                        }
                    }
                }

            }


        }
        $intial_depreciation_and_amortization=InitialBalanceSettings::calculatePreviousDepreciationAndAmortization($id);
        for ($i = 1; $i < 13; $i++) {
            if ($intial_depreciation_and_amortization['long_term']['amount_m_' . $i]) {
                $depreciation_and_amortization_long_term['amount_m_' . $i] = $depreciation_and_amortization_long_term['amount_m_' . $i] + $intial_depreciation_and_amortization['long_term']['amount_m_' . $i];
                $long_term_include_status=true;
            }
            if($intial_depreciation_and_amortization['current']['amount_m_' . $i])
            {
                $depreciation_and_amortization_current['amount_m_' . $i] = $depreciation_and_amortization_current['amount_m_' . $i] +$intial_depreciation_and_amortization['current']['amount_m_' . $i];
                $current_include_status=true;
            }
        }
        for ($i = 1; $i < 6; $i++) {
            if ($intial_depreciation_and_amortization['long_term']['amount_y_' . $i]) {
                $depreciation_and_amortization_long_term['amount_y_' . $i] = $depreciation_and_amortization_long_term['amount_y_' . $i] + $intial_depreciation_and_amortization['long_term']['amount_y_' . $i];

            }
            if($intial_depreciation_and_amortization['current']['amount_y_' . $i])
            {
                $depreciation_and_amortization_current['amount_y_' . $i] = $depreciation_and_amortization_current['amount_y_' . $i] +$intial_depreciation_and_amortization['current']['amount_y_' . $i];
            }
        }
        return ['current_include_status'=>$current_include_status,'long_term_include_status'=>$long_term_include_status,'current'=>$depreciation_and_amortization_current,'long_term'=>$depreciation_and_amortization_long_term];
    }
}
