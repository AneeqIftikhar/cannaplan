<?php

namespace CannaPlan\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Auth;
use function Sodium\add;

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

    public static function getFinancingByForecastId($id)
    {
        $forecast=Forecast::where('id',$id)->with(['company','financings','financings.fundable'])->first();
        //return $forecast;

        $start_of_forecast = new Carbon( $forecast->company->start_of_forecast );

        $financing=array();

        $amount_received_arr=array();
        $payments=array();
        $principal_paid=array();
        $interest_paid=array();
        $balance=array();
        $short_term=array();
        $long_term=array();

        $amount_received_temp=array();
        $payment_temp=array();
        $short_term_temp=array();
        $long_term_temp=array();

        $sum_temp=0;
        $sum_temp_IP=0;
        $sum_temp_PP=0;

        for ($j = 1; $j < 13; $j++) {
            $amount_received_arr['amount_m_' . $j] = null;

            $payments['amount_m_' . $j] = null;
            $principal_paid['amount_m_' . $j] = null;
            $interest_paid['amount_m_' . $j] = null;

            $balance['amount_m_' . $j] = null;
            $short_term['amount_m_' . $j] = null;
            $long_term['amount_m_' . $j] = null;

            $amount_received_temp['amount_m_' . $j] = null;
            $payment_temp['amount_m_' . $j] = null;
            $short_term_temp['amount_m_' . $j] = null;
            $long_term_temp['amount_m_' . $j] = null;
        }
        for ($j = 1; $j < 6; $j++) {
            $amount_received_arr['amount_y_' . $j] = null;

            $payments['amount_y_' . $j] = null;
            $principal_paid['amount_y_' . $j] = null;
            $interest_paid['amount_y_' . $j] = null;

            $balance['amount_y_' . $j] = null;
            $short_term['amount_y_' . $j] = null;
            $long_term['amount_y_' . $j] = null;

            $amount_received_temp['amount_y_' . $j] = null;
            $payment_temp['amount_y_' . $j] = null;
            $short_term_temp['amount_y_' . $j] = null;
            $long_term_temp['amount_y_' . $j] = null;
        }

//        $amount_received_arr[0]=['loan'=>$amount_received_temp];
//
//        $financing[0]=['amount_received'=>$amount_received_arr];
//
//        $payment_temp[0]=['principal_paid'=>$principal_paid];
//        $payment_temp[1]=['interest_paid'=>$interest_paid];
//
//        $payments[0]=['loan'=>$payment_temp];
//
//        $financing[1]=['payments'=>$payments];
//
//        $short_term[0]=['loan'=>$short_term_temp];
//        $long_term[0]=['loan'=>$long_term_temp];
//
//        $balance[0]=['short_term_debt'=>$short_term];
//        $balance[1]=['long_term_debt'=>$long_term];
//
//        $financing[2]=['balance'=>$balance];
//
//        return $financing;

        for ($i=0;$i<count($forecast->financings);$i++)
        {
            if(isset($forecast->financings[$i]->fundable)) {

                if ($forecast->financings[$i]->fundable_type == 'loan') {

                    $receive_date=new Carbon($forecast->financings[$i]->fundable->receive_date);

                    //populate ammount received
                    if($receive_date->year>$start_of_forecast->year)
                    {
                        for($j=1 ; $j<6 ; $j++)
                        {
                            if(($receive_date->year-$start_of_forecast->year)+1==$j && ($receive_date->month-$start_of_forecast->month)<=12)
                            {
                                $amount_received_temp['amount_y_'.$j]=$forecast->financings[$i]->fundable->amount;

                                $amount_received_arr['amount_y_'.$j]=$amount_received_arr['amount_y_'.$j]+$amount_received_temp['amount_y_'.$j];
                            }
                        }
                    }
                    else{
                        for ($j = 1; $j < 13; $j++) {
                            if(($receive_date->month-$start_of_forecast->month)+1==$j)
                            {
                                $amount_received_temp['amount_m_'.$j]=$forecast->financings[$i]->fundable->amount;

                                $amount_received_arr['amount_m_'.$j]=$amount_received_arr['amount_m_'.$j]+$amount_received_temp['amount_m_'.$j];
                            }
                        }
                        $amount_received_temp['amount_y_1']=$forecast->financings[$i]->fundable->amount;
                        $amount_received_arr['amount_y_1']=$amount_received_arr['amount_y_1']+$amount_received_temp['amount_y_1'];
                    }
                    //storing in amount received array
                    $amount_received_arr[$i]=[$forecast->financings[$i]->name => $amount_received_temp];

                    //Payments Portion

                    //calculating monthly interest
                    $interest_rate=$forecast->financings[$i]->fundable->interest_rate;
                    $absolute_interest_rate_monthly=($interest_rate/12)/100;
                    $divisor=(1-pow((1+$absolute_interest_rate_monthly) , ($forecast->financings[$i]->fundable->interest_months*-1)));
                    $dividend=$forecast->financings[$i]->fundable->amount*$absolute_interest_rate_monthly;
                    $monthly_interest=round($dividend/$divisor);

                    //start of payments
                    $start=($receive_date->month-$start_of_forecast->month)+2;
                    //end of payments
                    if(($forecast->financings[$i]->fundable->interest_months+$start)<12)
                    {
                        $end=($forecast->financings[$i]->fundable->interest_months+$start);
                    }
                    else{
                        $end=12;
                    }
                    //populating payment temp array
                    for ($j = $start ; $j <=$end ; $j++) {
                        $payment_temp['amount_m_' . $j] = $monthly_interest;
                        $sum_temp=$sum_temp+$monthly_interest;
                    }

                    $payment_temp['amount_y_' . 1]=$sum_temp;
                    for ($j = 2; $j < intval($forecast->financings[$i]->fundable->interest_months/12)+2 ; $j++) {
                        $payment_temp['amount_y_' . $j] = $monthly_interest*((12-$receive_date->month)+$start_of_forecast->month);
                    }

                    //calculating principal paid and interest paid
                    $previous_month_balance=$forecast->financings[$i]->fundable->amount;

                    for ($j = $start ; $j <=$end ; $j++) {
                        $interest_paid['amount_m_' . $j] = round($previous_month_balance*$absolute_interest_rate_monthly);

                        $principal_paid['amount_m_' . $j] = $monthly_interest-$interest_paid['amount_m_' . $j];

                        $previous_month_balance=$previous_month_balance-$principal_paid['amount_m_' . $j];

                        $sum_temp_IP=$sum_temp_IP+$interest_paid['amount_m_' . $j];
                        $sum_temp_PP=$sum_temp_PP+$principal_paid['amount_m_' . $j];
                    }

                    $interest_paid['amount_y_' . 1]=$sum_temp_IP;
                    $principal_paid['amount_y_' . 1]=$sum_temp_PP;
                    for ($j = 2; $j < intval($forecast->financings[$i]->fundable->interest_months/12)+2 ; $j++) {
                        $sum_temp_IP=0;
                        $sum_temp_PP=0;
                        //iterate for 11 months to find the principal and interest paid for next years
                        for($k=0 ; $k<12 ; $k++)
                        {
                            $sum_temp_IP=round($previous_month_balance*$absolute_interest_rate_monthly);
                            $interest_paid['amount_y_' . $j] = $interest_paid['amount_y_' . $j]+$sum_temp_IP;

                            $sum_temp_PP=$monthly_interest-$sum_temp_IP;

                            $previous_month_balance=$previous_month_balance-$sum_temp_PP;
                        }
                        $principal_paid['amount_y_' . $j]=$payment_temp['amount_y_' . $j]-$interest_paid['amount_y_' . $j];
                    }

                    //storing principal and interest paid
                    $payment_temp[0]=['principal_paid'=>$principal_paid];
                    $payment_temp[1]=['interest_paid'=>$interest_paid];

                    $payments[$i]=[$forecast->financings[$i]->name => $payment_temp];

                    //populating payments array
                    for ($j = $start ; $j <=$end ; $j++) {
                        $payments['amount_m_' . $j]=$payments['amount_m_' . $j]+$payment_temp['amount_m_' . $j];
                    }
                    for ($j = 1 ; $j < 6 ; $j++) {
                        $payments['amount_y_' . $j]=$payments['amount_y_' . $j]+$payment_temp['amount_y_' . $j];
                    }

                    //Balance Portion
                    $pp=array();
                    $sum_temp=0;
                    $previous_month_balance=$forecast->financings[$i]->fundable->amount;
                    for($j=0 ; $j<$forecast->financings[$i]->fundable->interest_months ; $j++)
                    {
                        $sum_temp_IP=round($previous_month_balance*$absolute_interest_rate_monthly);

                        $sum_temp_PP=$monthly_interest-$sum_temp_IP;
                        $pp[$j]=$sum_temp_PP;


                        $previous_month_balance=$previous_month_balance-$sum_temp_PP;
                    }

                    if($forecast->financings[$i]->fundable->interest_months>12)
                    {
                        for($j=0 ; $j<$forecast->financings[$i]->fundable->interest_months ; $j++)
                        {
                            $sum_temp=$sum_temp+$pp[$j];
                            if($j>((12-$receive_date->month)+($start_of_forecast->month-1))+1)
                            {
                                $short_term_index=($j-((12-$receive_date->month)+$start_of_forecast->month))+($start-1);
                                $pp_index=$j-((12-$receive_date->month)+$start_of_forecast->month);

                                $short_term_temp['amount_m_'.$short_term_index]=$sum_temp-$pp[$pp_index];
                            }
                        }
                        return $short_term_temp;
                    }


                }
            }

        }
        $financing[0]=['amount_received'=>$amount_received_arr];
        $financing[1]=['payments'=>$payments];
        return $financing;
    }

}

