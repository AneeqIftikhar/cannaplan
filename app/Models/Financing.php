<?php

namespace CannaPlan\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Auth;
use function Sodium\add;
use DateTime;
Relation::morphMap([
    'loan'=>'CannaPlan\Models\Loan',
    'investment'=>'CannaPlan\Models\Investment',
    'other'=>'CannaPlan\Models\Other'
]);
/**
 * @property int $id
 * @property int $forecast_id
 * @property string $name
 * @property int $fund_id
 * @property string $fund_type
 * @property string $deleted_at
 * @property string $remember_token
 * @property string $created_at
 * @property string $updated_at
 * @property Forecast $forecast
 */
class Financing extends Model
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'financing';

    public static function boot()
    {
        parent::boot();

        // create a event to happen on saving
        static::creating(function ($table) {
            $table->created_by = Auth::user()->id;
        });

        static::deleting(function ($table) {

            $table->fundable->delete();
        });
    }

    /**
     * @var array
     */
    protected $fillable = ['name', 'fundable_id', 'fundable_type'];
    protected $guarded = ['id', 'forecast_id', 'created_by'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function forecast()
    {
        return $this->belongsTo('CannaPlan\Models\Forecast');
    }

    public function fundable()
    {
        return $this->morphTo();
    }

    public static function addLoan($receive_date, $amount, $interest_rate, $interest_months , $remaining_amount)
    {
        $loan = Loan::create(['receive_date' => $receive_date, 'amount' => $amount , 'interest_rate'=>$interest_rate , 'interest_months'=>$interest_months , 'remaining_amount'=>$remaining_amount]);
        return $loan;
    }
    public static function addInvestment($amount_type,$investment_start_date , $amount , $payable_span)
    {
        $investment=Investment::create(['amount_type' => $amount_type, 'investment_start_date'=>$investment_start_date ,'amount' => $amount , 'payable_span'=>$payable_span]);
        return $investment;
    }
    Public static function addOther($annual_interest , $is_payable)
    {
        $other=Other::create(['annual_interest'=>$annual_interest , 'is_payable'=>$is_payable]);
        return $other;
    }
    Public static function addFunding($input , $other)
    {
        $array=array();
        for($i=1 ; $i<13 ; $i++)
        {
            $array['amount_m_'.$i]=$input['amount_m_'.$i];
        }
        for($i=1 ; $i<6 ; $i++)
        {
            $array['amount_y_'.$i]=$input['amount_y_'.$i];
        }
        $other->fundings()->create($array);

        return $other;
    }
    Public static function addPayment($input , $other)
    {
        $array=array();
        for($i=1 ; $i<13 ; $i++)
        {
            $array['amount_m_'.$i]=$input['amount_m_'.$i];
        }
        for($i=1 ; $i<6 ; $i++)
        {
            $array['amount_y_'.$i]=$input['amount_y_'.$i];
        }
        $other->payments()->create($array);

        return $other;
    }

    public static function updateLoan($receive_date, $amount, $interest_rate, $interest_months , $remaining_amount , $fundable)
    {
        $fundable->update(['receive_date' => $receive_date, 'amount' => $amount , 'interest_rate'=>$interest_rate , 'interest_months'=>$interest_months , 'remaining_amount'=>$remaining_amount]);
    }
    public static function updateInvestment($amount_type,$investment_start_date , $amount , $payable_span , $fundable)
    {
        $fundable->update(['amount_type' => $amount_type, 'investment_start_date'=>$investment_start_date ,'amount' => $amount , 'payable_span'=>$payable_span]);
    }
    public static function updateOther($annual_interest , $is_payable , $fundable)
    {
        $fundable->update(['annual_interest'=>$annual_interest , 'is_payable'=>$is_payable]);
    }
    Public static function updateFunding($input , $other)
    {
        $array=array();
        for($i=1 ; $i<13 ; $i++)
        {
            $array['amount_m_'.$i]=$input['amount_m_'.$i];
        }
        for($i=1 ; $i<6 ; $i++)
        {
            $array['amount_y_'.$i]=$input['amount_y_'.$i];
        }
        $other->fundings()->update($array);

        return $other;
    }
    Public static function updatePayment($input , $other)
    {
        $array=array();
        for($i=1 ; $i<13 ; $i++)
        {
            $array['amount_m_'.$i]=$input['amount_m_'.$i];
        }
        for($i=1 ; $i<6 ; $i++)
        {
            $array['amount_y_'.$i]=$input['amount_y_'.$i];
        }
        $other->payments()->update($array);

        return $other;
    }

    //Projected Cash Flow doesn't include Financing which will be included later
    public static function getProjectedCashFlow($id)
    {
        //getting revenue total
        $profit=array();
        $revenue=Revenue::getRevenueByForecastId($id);
        $revenue_total=$revenue['total'];

        //getting cost total excluding the cost on labor that will be calculated from personal
        $cost=Cost::getCostByForecastId($id);
        $cost_total=array();
        for($i=1;$i<13;$i++)
        {
            if(isset($cost['direct_labor']))
            {
                $cost_total['amount_m_'.$i] = $cost['total']['amount_m_'.$i]-$cost['direct_labor']['amount_m_'.$i];
            }
            else
            {
                $cost_total['amount_m_'.$i] = $cost['total']['amount_m_'.$i];
            }

        }
        for($i=1;$i<6;$i++)
        {
            if(isset($cost['direct_labor']))
            {
                $cost_total['amount_y_'.$i] = $cost['total']['amount_y_'.$i]-$cost['direct_labor']['amount_y_'.$i];
            }
            else
            {
                $cost_total['amount_y_'.$i] = $cost['total']['amount_y_'.$i];
            }
        }



        $labor=Cost::getPersonnelByForecastId($id);
        $labor_total=$labor['total'];

        $expense=Expense::getExpenseByForecastId($id);
        $expense_total=$expense['total'];
        $assets=Asset::getAssetByForecast($id);
        $asset_total=array();
        for($i=1;$i<13;$i++)
        {
            $asset_total['amount_m_'.$i] = 0;
        }
        for($i=1;$i<6;$i++)
        {
            $asset_total['amount_y_'.$i] = 0;
        }
        foreach ($assets->assets as $asset)
        {
            if($asset->amount_type=="constant")
            {
                for($i=1;$i<13;$i++)
                {
                    if($asset['amount_m_'.$i])
                    {
                        $asset_total['amount_m_'.$i] = $asset_total['amount_m_'.$i]+$asset->amount;
                    }

                }
                for($i=1;$i<6;$i++)
                {
                    if($asset['amount_y_'.$i])
                    {
                        $asset_total['amount_y_'.$i] = $asset_total['amount_y_'.$i]+$asset->amount*12;
                    }
                }
            }
            else
            {
                $found=0;
                for($i=1;$i<13;$i++)
                {
                    if($asset['amount_m_'.$i] && $found==0)
                    {
                        $asset_total['amount_m_'.$i] = $asset_total['amount_m_'.$i]+$asset->amount;
                        $found=1;
                    }

                }
                for($i=1;$i<6;$i++)
                {
                    if($asset['amount_y_'.$i] && $found==0)
                    {
                        $asset_total['amount_y_'.$i] = $asset_total['amount_y_'.$i]+$asset->amount*12;
                        $found=1;
                    }
                }
//                $start_of_forecast = new DateTime($asset->company->start_of_forecast);
            }

        }
        for($i=1;$i<13;$i++)
        {
            $profit['amount_m_'.$i] = 0;
            $profit['amount_m_'.$i] = $revenue_total['amount_m_'.$i]-$cost_total['amount_m_'.$i]-$labor_total['amount_m_'.$i]-$expense_total['amount_m_'.$i]-$asset_total['amount_m_'.$i];
//            if($i>1)
//            {
//                $profit['amount_m_'.$i] =$profit['amount_m_'.$i] +$profit['amount_m_'.($i-1)];
//            }
        }
        for($i=1;$i<6;$i++)
        {
            $profit['amount_y_'.$i] = 0;
            $profit['amount_y_'.$i] = $revenue_total['amount_y_'.$i]-$cost_total['amount_y_'.$i]-$labor_total['amount_y_'.$i]-$expense_total['amount_y_'.$i]- $asset_total['amount_y_'.$i];
//            if($i>1)
//            {
//                $profit['amount_y_'.$i] =$profit['amount_y_'.$i] +$profit['amount_y_'.($i-1)];
//            }
        }

        return $profit;
    }

    public static function getFinancingByForecastId($id)
    {
        $forecast=Forecast::where('id',$id)->with(['company','financings','financings.fundable'])->first();
        $forecast['before_start_status']=false;//for front end to check if a before start of forecast is present
        $start_of_forecast = new DateTime( $forecast->company->start_of_forecast );
        $amount_received_arr['finance']=array();
        $principal_paid = array();
        $loan=array();
        $interest_paid=array();
        $balance=array();
        $short_term=array();
        $long_term=array();
        $short_term['finance']=array();
        $long_term['finance']=array();
        $payments['finance']=array();
        $amount_received_arr['amount_m_0'] = null;

        $temp=array();
        for ($j = 0; $j < 13; $j++) {
            $amount_received_arr['amount_m_' . $j] = null;
            $payments['amount_m_' . $j] = null;
            $principal_paid['amount_m_' . $j] = null;
            $interest_paid['amount_m_' . $j] = null;
            $loan['amount_m_' . $j] = null;
            $balance['amount_m_' . $j] = null;
            $short_term['amount_m_' . $j] = null;

            $temp['amount_m_' . $j] = null;

        }
        for ($j = 0; $j < 6; $j++) {
            $amount_received_arr['amount_y_' . $j] = null;
            $payments['amount_y_' . $j] = null;
            $principal_paid['amount_y_' . $j] = null;
            $interest_paid['amount_y_' . $j] = null;
            $loan['amount_y_' . $j] = null;
            $balance['amount_y_' . $j] = null;
            $short_term['amount_y_' . $j] = null;

            $temp['amount_y_' . $j] = null;

        }

        for ($j = 0; $j < 61; $j++) {
            $long_term['amount_m_' . $j] = 0;
        }
        for ($j = 1; $j < 6; $j++) {
            $long_term['amount_y_' . $j] = 0;
        }

        for ($i=0;$i<count($forecast->financings);$i++)
        {
            if(isset($forecast->financings[$i]->fundable)) {
                $forecast['rows_hidden']=false;
                if ($forecast->financings[$i]->fundable_type == 'investment') {
                    $date=date($forecast->financings[$i]->fundable->investment_start_date);
                    $d2 = new DateTime($date);
                    $diff_month=$start_of_forecast->diff($d2)->m;
                    $diff_year=$start_of_forecast->diff($d2)->y;
                    $temp=clone($forecast->financings[$i]);
                    if($forecast->financings[$i]->fundable->amount_type=="one_time")
                    {
                        for ($j = 1; $j < 13; $j++) {
                            if($diff_year==0 && $diff_month==$j-1)
                            {

                                $temp['amount_m_' . $j]= $forecast->financings[$i]->fundable->amount;

                            }
                            else
                            {
                                $temp['amount_m_' . $j]= null;
                            }
                        }
                        for ($j = 1; $j < 6; $j++) {
                            if($diff_year==0 && $j==1)
                            {
                                $temp['amount_y_' . $j]= $forecast->financings[$i]->fundable->amount;

                            }
                            else if($diff_year!=0 && $diff_year==$j-1)
                            {
                                $temp['amount_y_' . $j]= $forecast->financings[$i]->fundable->amount;
                            }
                            else
                            {
                                $temp['amount_y_' . $j]= null;
                            }
                        }
                    }
                    else if($forecast->financings[$i]->fundable->amount_type=="constant") {
                        if ($forecast->financings[$i]->fundable->payable_span == "month") {
                            $year_1_toal = 0;
                            for ($j = 1; $j < 13; $j++) {

                                if ($diff_year == 0 && $diff_month < $j) {
                                    $temp['amount_m_' . $j] = $forecast->financings[$i]->fundable->amount;
                                    $year_1_toal = $year_1_toal + $forecast->financings[$i]->fundable->amount;
                                } else {
                                    $temp['amount_m_' . $j] = null;
                                }
                            }
                            for ($j = 1; $j < 6; $j++) {
                                if ($diff_year < $j) {
                                    if ($j == 1) {
                                        $temp['amount_y_' . $j] = $year_1_toal;
                                    } else {
                                        $temp['amount_y_' . $j] = $forecast->financings[$i]->fundable->amount * 12;
                                    }
                                } else {
                                    $temp['amount_y_' . $j] = null;
                                }
                            }
                        } else //year
                        {
                            $amount=$forecast->financings[$i]->fundable->amount;
                            $total_year_1=0;
                            $index = 1;
                            for ($j = 1; $j < 13; $j++) {
                                if ($diff_year == 0 && $diff_month < $j) {
                                    if ($j == 12 && $index == $j) {
                                        $temp['amount_m_12'] = $amount;
                                        $total_year_1 = $total_year_1 + $temp['amount_m_' . $j];
                                    } else {

                                        $temp['amount_m_' . $j] = floor(($amount / (13 - $index)));
                                        $total_year_1 = $total_year_1 + $temp['amount_m_' . $j];
                                        $amount = $amount - floor(($amount / (13 - $index)));
                                        $index = $index + 1;
                                    }

                                } else {
                                    $temp['amount_m_' . $j] = null;
                                }
                            }
                            for ($j = 1; $j < 6; $j++) {
                                if ($diff_year < $j) {
                                    if ($j == 1) {
                                        $temp['amount_y_' . $j] = $total_year_1;
                                    } else {
                                        $temp['amount_y_' . $j] = $forecast->financings[$i]->fundable->amount;
                                    }
                                } else {
                                    $temp['amount_y_' . $j] = null;
                                }
                            }

                        }
                    }

                    for($j=1 ; $j<13 ; $j++)
                    {
                        if($temp['amount_m_' . $j])
                        {
                            $amount_received_arr['amount_m_' . $j] = $amount_received_arr['amount_m_' . $j]+$temp['amount_m_' . $j];
                        }
                    }
                    for($j=1 ; $j<6 ; $j++)
                    {
                        if($temp['amount_y_' . $j])
                        {
                            $amount_received_arr['amount_y_' . $j] = $amount_received_arr['amount_y_' . $j]+$temp['amount_y_' . $j];
                        }

                    }
                    $amount_received_arr['rows_hidden']=false;
                    array_push($amount_received_arr['finance'],$temp);
                    $forecast['amount_received']=$amount_received_arr;

                }
                else if($forecast->financings[$i]->fundable_type == 'loan'){
                    $date=date($forecast->financings[$i]->fundable->receive_date);
                    $d2 = new DateTime($date);
                    if($date<$start_of_forecast)
                    {
                        $diff_month=$start_of_forecast->diff($d2)->m;
                        $diff_month=$diff_month*-1;
                        $forecast['before_start_status']=true;
                    }
                    else
                    {
                        $diff_month=$start_of_forecast->diff($d2)->m;
                    }
                    $diff_year=$start_of_forecast->diff($d2)->y;

                    //calculating amount received

                    $temp=clone($forecast->financings[$i]);
                    //renew temp
                    for ($j = 1; $j < 13; $j++) {
                        $temp['amount_m_'.$j]=null;
                    }
                    for ($j = 1; $j < 6; $j++) {
                        $temp['amount_y_'.$j]=null;
                    }

                    for ($j = 0; $j < 13; $j++) {
                        if($diff_year==0 && $diff_month==$j-1)
                        {
                            $temp['amount_m_' . $j]= $forecast->financings[$i]->fundable->amount;
                        }
                        else
                        {
                            $temp['amount_m_' . $j]= null;
                        }
                    }
                    for ($j = 1; $j < 6; $j++) {
                        if($diff_month!=-1 && $diff_year==0 && $diff_year==$j-1)
                        {
                            $temp['amount_y_' . $j]= $forecast->financings[$i]->fundable->amount;
                        }
                        else if($diff_year!=0 && $diff_year==$j-1)
                        {
                            $temp['amount_y_' . $j]= $forecast->financings[$i]->fundable->amount;
                        }
                        else
                        {
                            $temp['amount_y_' . $j]= null;
                        }
                    }
                    //storing fundable in amount received
                    array_push($amount_received_arr['finance'],$temp);

                    //storing in amount received
                    for ($j = 0; $j < 13; $j++) {
                        if(isset($temp['amount_m_' . $j]))
                        {
                            $amount_received_arr['amount_m_' . $j] = $amount_received_arr['amount_m_' . $j]+$temp['amount_m_' . $j];
                        }

                    }
                    for ($j = 1; $j < 6; $j++) {
                        if(isset($temp['amount_y_' . $j]))
                        {
                            $amount_received_arr['amount_y_' . $j] = $amount_received_arr['amount_y_' . $j]+$temp['amount_y_' . $j];
                        }
                    }

                    //$temp=array();
                    $temp=clone($forecast->financings[$i]);

                    //renew temp
                    for ($j = 0; $j < 13; $j++) {
                        $temp['amount_m_'.$j]=null;
                        $principal_paid['amount_m_'.$j]=null;
                        $interest_paid['amount_m_'.$j]=null;
                    }
                    for ($j = 1; $j < 6; $j++) {
                        $temp['amount_y_'.$j]=null;
                        $principal_paid['amount_y_'.$j]=null;
                        $interest_paid['amount_y_'.$j]=null;
                    }

                    $interest_rate=$forecast->financings[$i]->fundable->interest_rate;
                    $absolute_interest_rate_monthly=($interest_rate/12)/100;
                    if($diff_month==-1)
                    {
                        $divisor=(1-pow((1+$absolute_interest_rate_monthly) , ($forecast->financings[$i]->fundable->remaining_amount*-1)));
                        $end=$forecast->financings[$i]->fundable->remaining_amount;
                    }
                    else
                    {
                        $divisor=(1-pow((1+$absolute_interest_rate_monthly) , ($forecast->financings[$i]->fundable->interest_months*-1)));
                        $end=$forecast->financings[$i]->fundable->interest_months;
                    }

                    $dividend=$forecast->financings[$i]->fundable->amount*$absolute_interest_rate_monthly;
                    $monthly_interest=$dividend/$divisor;

                    //calculating principal paid and interest paid
                    $previous_month_balance=$forecast->financings[$i]->fundable->amount;


                    if($diff_year==0)
                    {
                        $start=$diff_month+2;
                    }
                    else
                    {
                        $start=$diff_year*12+1;
                    }
                    for($j=1;$j<$start+$end+12;$j++){
                        if($j>=$start && $j<$start+$end)
                        {
                            $interest_paid['amount_m_'.$j]=$absolute_interest_rate_monthly* $previous_month_balance;

                            $principal_paid['amount_m_'.$j]=$monthly_interest-$interest_paid['amount_m_'.$j];

                            $previous_month_balance=$previous_month_balance-$principal_paid['amount_m_'.$j];
                        }
                        else
                        {
                            $interest_paid['amount_m_'.$j]=null;
                            $principal_paid['amount_m_'.$j]=null;
                        }
                    }
                    for($j=1;$j<6;$j++){
                        for($k=1;$k<13;$k++)
                        {
                            if(isset($interest_paid['amount_m_'.((($j-1)*12)+$k)]) && $interest_paid['amount_m_'.((($j-1)*12)+$k)])
                                $interest_paid['amount_y_'.$j]=$interest_paid['amount_y_'.$j]+$interest_paid['amount_m_'.((($j-1)*12)+$k)];
                            if(isset($principal_paid['amount_m_'.((($j-1)*12)+$k)]) && $principal_paid['amount_m_'.((($j-1)*12)+$k)])
                                $principal_paid['amount_y_'.$j]=$principal_paid['amount_y_'.$j]+$principal_paid['amount_m_'.((($j-1)*12)+$k)];
                        }
                    }

                    //calculating fundable
                    for($j=1 ; $j<13 ; $j++)
                    {
                        if($interest_paid['amount_m_'.$j] || $principal_paid['amount_m_'.$j])
                        {
                            $temp['amount_m_'.$j]=$principal_paid['amount_m_'.$j]+$interest_paid['amount_m_'.$j];
                            if($interest_paid['amount_m_'.$j])
                            {
                                $interest_paid['amount_m_'.$j]=round($interest_paid['amount_m_'.$j]);
                            }
                            if($principal_paid['amount_m_'.$j])
                            {
                                $principal_paid['amount_m_'.$j]=round($principal_paid['amount_m_'.$j]);
                            }
                        }
                        else
                        {
                            $temp['amount_m_'.$j]=null;
                        }
                    }
                    for($j=1 ; $j<6 ; $j++)
                    {
                        if($interest_paid['amount_y_'.$j] || $principal_paid['amount_y_'.$j])
                        {
                            $temp['amount_y_'.$j]=$principal_paid['amount_y_'.$j]+$interest_paid['amount_y_'.$j];
                            if($interest_paid['amount_y_'.$j])
                            {
                                $interest_paid['amount_y_'.$j]=round($interest_paid['amount_y_'.$j]);
                            }
                            if($principal_paid['amount_y_'.$j])
                            {
                                $principal_paid['amount_y_'.$j]=round($principal_paid['amount_y_'.$j]);
                            }
                        }
                        else
                        {
                            $temp['amount_y_'.$j]=null;
                        }
                    }


                    //storing principal paid and interest paid in fundable
                    $temp['principal_paid']=$principal_paid;
                    $temp['interest_paid']=$interest_paid;

                    //calculate payment
                    for($j=1 ; $j<13 ; $j++)
                    {
                        if($temp['amount_m_'.$j])
                        {
                            $payments['amount_m_'.$j]=round($payments['amount_m_'.$j]+$temp['amount_m_'.$j]);
                            $temp['amount_m_'.$j]=round($temp['amount_m_'.$j]);
                        }
                    }
                    for($j=1 ; $j<6 ; $j++)
                    {
                        if($temp['amount_y_'.$j])
                        {
                            $payments['amount_y_'.$j]=round($payments['amount_y_'.$j]+$temp['amount_y_'.$j]);
                            $temp['amount_y_'.$j]=round($temp['amount_y_'.$j]);
                        }
                    }

                    //storing fundable in payment
                    array_push($payments['finance'],$temp);

                    $temp=array();
                    //balance calculation
                    $iterate_start=$start;
                    $sum_temp=0;

                    $temp_short=clone($forecast->financings[$i]);
                    $temp_long=clone($forecast->financings[$i]);
                    for ($j = 0; $j < 13; $j++) {
                        $temp_short['amount_m_'.$j]=null;
                        $temp['amount_m_'.$j]=null;
                        $temp_long['amount_m_'.$j]=0;

                    }
                    for ($j = 1; $j < 6; $j++) {
                        $temp_short['amount_y_'.$j]=null;
                        $temp['amount_y_'.$j]=null;
                        $temp_long['amount_y_'.$j]=0;
                    }



                    for($j=0 ; $j<61 ; $j++)
                    {
                        for($k=$iterate_start; $k<$iterate_start+12;$k++){
                            if(isset($principal_paid['amount_m_'.$k]))
                                $sum_temp=$sum_temp+$principal_paid['amount_m_'.$k];
                                if(isset($principal_paid['amount_m_'.$k]) && $principal_paid['amount_m_'.$k])
                                {
                                    $principal_paid['amount_m_'.$k]=round($principal_paid['amount_m_'.$k]);
                                }
                        }
                        $temp_short['amount_m_'.$j]=round($sum_temp);
                        if($j!=0 && $j%12==0)
                        {
                            $temp_short['amount_y_'.($j/12)]=$temp_short['amount_m_'.$j];
                        }
                        $sum_temp=0;

                        if($end>12)
                        {
                            for ($k=$iterate_start+12 ; $k<=$end ; $k++)
                            {
                                if(isset($principal_paid['amount_m_'.$k])) {
                                    $sum_temp = $sum_temp + $principal_paid['amount_m_' . $k];
                                    if ($principal_paid['amount_m_' . $k]) {
                                        $principal_paid['amount_m_' . $k] = round($principal_paid['amount_m_' . $k]);
                                    }
                                }
                            }
                            $temp_long['amount_m_'.$j]=round($sum_temp);

                            if($j!=0 && $j%12==0)
                            {
                                $temp_long['amount_y_'.($j/12)]=$temp_long['amount_m_'.$j];
                            }
                        }

                        $sum_temp=0;
                        $iterate_start++;
                    }


                    array_push($short_term['finance'],$temp_short);
                    array_push($long_term['finance'],$temp_long);

                    //calculating short term and long term
                    for($j=0 ; $j<13 ; $j++)
                    {
                        if($temp_short['amount_m_'.$j])
                        {
                            $short_term['amount_m_'.$j]=($short_term['amount_m_'.$j]+$temp_short['amount_m_'.$j]);
                        }
                        if($temp_long['amount_m_'.$j])
                        {
                            $long_term['amount_m_'.$j]=($long_term['amount_m_'.$j]+$temp_long['amount_m_'.$j]);
                        }
                    }

                    for($j=1;$j<6;$j++)
                    {
                        $short_term['amount_y_'.$j]=$short_term['amount_y_'.$j]+$temp_short['amount_y_'.$j];

                        $long_term['amount_y_'.$j]=$long_term['amount_y_'.$j]+$temp_long['amount_y_'.$j];

                    }


                    $balance['short_term']=  $short_term;
                    $balance['long_term'] = $long_term;

                    //calculating balance
                    for($j=0 ; $j<13 ; $j++)
                    {
                        if($short_term['amount_m_'.$j] || $long_term['amount_m_'.$j])
                        {
                            $balance['amount_m_'.$j]=round($short_term['amount_m_'.$j]+$long_term['amount_m_'.$j]);

                            if($short_term['amount_m_'.$j])
                            {
                                $short_term['amount_m_'.$j]=round($short_term['amount_m_'.$j]);
                            }
                            if($long_term['amount_m_'.$j])
                            {
                                $long_term['amount_m_'.$j]=round($long_term['amount_m_'.$j]);
                            }
                        }
                    }
 
                    for($j=1 ; $j<6 ; $j++)
                    {
                        if($short_term['amount_y_'.$j] || $long_term['amount_y_'.$j])
                        {
                            $balance['amount_y_'.$j]=round($balance['amount_y_'.$j]+$short_term['amount_y_'.$j]+$long_term['amount_y_'.$j]);
                            if($short_term['amount_y_'.$j])
                            {
                                $short_term['amount_y_'.$j]=round($short_term['amount_y_'.$j]);
                            }
                            if($long_term['amount_y_'.$j])
                            {
                                $long_term['amount_y_'.$j]=round($long_term['amount_y_'.$j]);
                            }
                        }
                    }

                    $amount_received_arr['rows_hidden']=false;
                    $payments['rows_hidden']=false;
                    $balance['rows_hidden']=false;
                    $forecast['amount_received']=$amount_received_arr;
                    $forecast['payments']=$payments;
                    $forecast['balance']=$balance;
                }
                else if($forecast->financings[$i]->fundable_type == 'other') {
                    //calculate monthly interest rate
                    $monthly_interest_rate = ($forecast->financings[$i]->fundable->annual_interest / 12) / 100;

                    $funding = array();
                    $funding=Funding::where('other_id' , '=' , $forecast->financings[$i]->fundable->id)->first();

                    $payment = array();
                    $payment=Payment::where('other_id' , '=' , $forecast->financings[$i]->fundable->id)->first();

                    for($j=1 ; $j<13 ; $j++)
                    {
                        if($funding['amount_m_'.$j])
                        {
                            $amount_received_arr['amount_m_'.$j]=$amount_received_arr['amount_m_'.$j]+$funding['amount_m_'.$j];
                        }
                    }
                    for($j=1 ; $j<6 ; $j++)
                    {
                        if($funding['amount_y_'.$j])
                        {
                            $amount_received_arr['amount_y_'.$j]=$amount_received_arr['amount_y_'.$j]+$funding['amount_y_'.$j];
                        }
                    }

                    $temp=clone($forecast->financings[$i]);
                    //populating temp
                    for ($j = 1; $j < 13; $j++) {
                        $temp['amount_m_'.$j]=$funding['amount_m_'.$j];
                    }
                    for ($j = 1; $j < 6; $j++) {
                        $temp['amount_y_'.$j]=$funding['amount_y_'.$j];
                    }

                    //saving temp of amount received
                    array_push($amount_received_arr['finance'],$temp);

                    //balance and payment part

                    //renew temp , principal paid and interest paid , renew temp , temp short
                    $temp=clone($forecast->financings[$i]);
                    $temp_short=clone($forecast->financings[$i]);
                    $temp_long=clone($forecast->financings[$i]);
                    for ($j = 1; $j < 13; $j++) {
                        $temp['amount_m_'.$j]=null;
                        $principal_paid['amount_m_'.$j]=null;
                        $interest_paid['amount_m_'.$j]=null;
                        $temp_short['amount_m_'.$j]=null;
                        $temp_long['amount_m_'.$j]=null;
                    }
                    for ($j = 1; $j < 6; $j++) {
                        $temp['amount_y_'.$j]=null;
                        $principal_paid['amount_y_'.$j]=null;
                        $interest_paid['amount_y_'.$j]=null;
                        $temp_short['amount_y_'.$j]=null;
                        $temp_long['amount_y_'.$j]=null;
                    }

                    $funding_check=false;
                    $previous_balance=0;
                    $year_1_payment_total=null;
                    for ($j = 1; $j < 13; $j++) {
                        //populating payment array
                        $temp['amount_m_'.$j]=$payment['amount_m_'.$j];
                        $year_1_payment_total=$year_1_payment_total+$temp['amount_m_'.$j];

                        //populating principal paid , interest paid and temp short arrays
                        if($funding['amount_m_'.$j]!=null && $funding_check==false)//for first funding
                        {
                            $funding_check=true;

                            $principal_paid['amount_m_'.$j]=$payment['amount_m_'.$j];

                            if($forecast->financings[$i]->fundable->is_payable==0)
                            {
                                $temp_short['amount_m_'.$j]=$funding['amount_m_'.$j]-$payment['amount_m_'.$j];
                                $previous_balance=$temp_short['amount_m_'.$j];
                            }
                            else{
                                $temp_long['amount_m_'.$j]=$funding['amount_m_'.$j]-$payment['amount_m_'.$j];
                                $previous_balance=$temp_long['amount_m_'.$j];
                            }
                        }
                        elseif ($funding_check==true)//for other fundings
                        {
                            $interest_paid['amount_m_'.$j]=$previous_balance*$monthly_interest_rate;

                            $principal_paid['amount_m_'.$j]=$payment['amount_m_'.$j]-$interest_paid['amount_m_'.$j];


                            if($forecast->financings[$i]->fundable->is_payable==0)
                            {
                                $temp_short['amount_m_'.$j]=$previous_balance+($funding['amount_m_'.$j]-$payment['amount_m_'.$j])+$interest_paid['amount_m_'.$j];
                                $previous_balance=$temp_short['amount_m_'.$j];
                            }
                            else{
                                $temp_long['amount_m_'.$j]=$previous_balance+$payment['amount_m_'.$j]+$interest_paid['amount_m_'.$j];
                                $previous_balance=$temp_long['amount_m_'.$j];
                            }

                        }
                    }

                    //calculation for years
                    //year calculation remains

                    $temp['amount_y_1']=$year_1_payment_total;
                    if($forecast->financings[$i]->fundable->is_payable==0)
                    {
                        $temp_short['amount_y_1']=$temp_short['amount_m_12'];
                    }
                    else{
                        $temp_long['amount_y_1']=$temp_long['amount_m_12'];

                    }
                    for ($j = 2; $j < 6; $j++) {
                        $tmp=$funding['amount_y_'.$j]/12;

                        $tmp1=$payment['amount_y_'.$j]/12;
                        $temp['amount_y_'.$j]=$payment['amount_y_'.$j];

                        $total=0;
                        $total_int=0;

                        for($k=1;$k<13;$k++)
                        {
                            $interest_paid_temp=$previous_balance*$monthly_interest_rate;
                            $total_int=$total_int+$interest_paid_temp;

                            $principal_paid_temp=$tmp1-$interest_paid_temp;

                            $total=$total+$principal_paid_temp;

                            $previous_balance=$previous_balance+($tmp-$tmp1)+$interest_paid_temp;

                            //$interest_paid_temp=$interest_paid_temp+($previous_balance_temp*$monthly_interest_rate);
                            //$previous_balance_temp=$previous_balance_temp+($previous_balance_temp*$monthly_interest_rate);
                        }
                        $principal_paid['amount_y_'.$j]=$total;
                        $interest_paid['amount_y_'.$j]=$total_int;
                        if($forecast->financings[$i]->fundable->is_payable==0)
                        {
                            $temp_short['amount_y_'.$j]=$temp_short['amount_y_'.($j-1)]+($funding['amount_y_'.$j]-$payment['amount_y_'.$j])+$interest_paid['amount_y_'.$j];

                        }
                        else{
                            $temp_long['amount_y_'.$j]=$temp_long['amount_y_'.($j-1)]+$payment['amount_y_'.$j]+$interest_paid['amount_y_'.$j];

                        }
                        //$temp['amount_y_'.$j]=$payment['amount_y_'.$j];
                        //$previous_balance=$temp_short['amount_m_12'];
                        //$previous_balance_temp=$previous_balance;
                        //$interest_paid_temp=0;

//                        return $interest_paid;
//
                    }

                    //updating payment array
                    for($j=1 ; $j<13 ; $j++)
                    {
                        if($temp['amount_m_'.$j])
                        {
                            $payments['amount_m_'.$j]=$payments['amount_m_'.$j]+$temp['amount_m_'.$j];
                        }

                    }
                    for($j=1 ; $j<6 ; $j++)
                    {
                        if($temp['amount_y_'.$j])
                        {
                            $payments['amount_y_'.$j]=$payments['amount_y_'.$j]+$temp['amount_y_'.$j];
                        }
                    }
                    //storing principal paid and interest paid in fundable
                    $temp['rows_hidden']=false;
                    $temp['principal_paid']=$principal_paid;
                    $temp['interest_paid']=$interest_paid;

                    //pushing temp in patments
                    array_push($payments['finance'],$temp);

                    //pushing temp balance in long term and short term respectively
                    if($forecast->financings[$i]->fundable->is_payable==0)
                    {
                        //pushing a fundable in short term
                        array_push($short_term['finance'],$temp_short);

                        //updating short term and balance array
                        for ($j = 1; $j < 13; $j++) {
                            if($temp_short['amount_m_'.$j])
                            {
                                $short_term['amount_m_'.$j]=$short_term['amount_m_'.$j]+$temp_short['amount_m_'.$j];
                                $balance['amount_m_'.$j]=$balance['amount_m_'.$j]+$short_term['amount_m_'.$j];
                            }
                        }
                        for ($j = 1; $j < 6; $j++) {
                            if($temp_short['amount_y_'.$j])
                            {
                                $short_term['amount_y_'.$j]=$short_term['amount_y_'.$j]+$temp_short['amount_y_'.$j];
                                $balance['amount_y_'.$j]=$balance['amount_y_'.$j]+$short_term['amount_y_'.$j];
                            }
                        }

                        //updating balance short term array
                        $balance['short_term']=$short_term;
                    }
                    else
                    {
                        //pushing a fundable in long term array
                        array_push($long_term['finance'],$temp_long);

                        //updating long term and balance array
                        for ($j = 1; $j < 13; $j++) {
                            if($long_term['amount_m_'.$j])
                            {
                                $long_term['amount_m_'.$j]=$long_term['amount_m_'.$j]+$temp_long['amount_m_'.$j];
                                $balance['amount_m_'.$j]=$balance['amount_m_'.$j]+$long_term['amount_m_'.$j];
                            }
                        }
                        for ($j = 1; $j < 6; $j++) {
                            if($long_term['amount_y_'.$j])
                            {
                                $long_term['amount_y_'.$j]=$long_term['amount_y_'.$j]+$temp_long['amount_y_'.$j];
                                $balance['amount_y_'.$j]=$balance['amount_y_'.$j]+$long_term['amount_y_'.$j];
                            }
                        }

                        //updating balance long term array
                        $balance['long_term']=$long_term;

                    }

                    $amount_received_arr['rows_hidden']=false;
                    $payments['rows_hidden']=false;
                    $balance['rows_hidden']=false;
                    $balance['long_term']['rows_hidden']=false;
                    $balance['short_term']['rows_hidden']=false;
                    $forecast['amount_received']=$amount_received_arr;
                    $forecast['payments']=$payments;
                    $forecast['balance']=$balance;
                }

            }
        }
        $cash_flow_arr=Financing::getProjectedCashFlow($id);
        for ($j = 1; $j < 13; $j++) {
            $cash_flow_arr['amount_m_' . $j]=$cash_flow_arr['amount_m_' . $j]+$amount_received_arr['amount_m_' . $j]-$payments['amount_m_'.$j];
            if($j>1)
            {
                $cash_flow_arr['amount_m_'.$j] =$cash_flow_arr['amount_m_'.$j] +$cash_flow_arr['amount_m_'.($j-1)];
            }

        }
        for ($j = 1; $j < 6; $j++) {
            $cash_flow_arr['amount_y_' . $j]=$cash_flow_arr['amount_y_' . $j]+$amount_received_arr['amount_y_' . $j]-$payments['amount_y_' . $j];
            if($j>1)
            {
                $cash_flow_arr['amount_y_'.$j] =$cash_flow_arr['amount_y_'.$j] +$cash_flow_arr['amount_y_'.($j-1)];
            }

        }
        $forecast['projected_cash_flow']=$cash_flow_arr;

        return $forecast;
    }

}

