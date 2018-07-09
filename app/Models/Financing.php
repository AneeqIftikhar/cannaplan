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

    /*
     *  double original_value = 103;
	double current_value = original_value;
    double number_of_periods = 11;
	double new_value;
	double dep = original_value / number_of_periods;
	for (int i = 1; i <=number_of_periods; i++)
	{
		new_value = current_value - dep;
		current_value = new_value;
		cout << dep << " - " << round(current_value) << endl;
     * */
    public static function getFinancingByForecastId($id)
    {
        $forecast=Forecast::where('id',$id)->with(['company','financings','financings.fundable'])->first();
        return $forecast;

        $start_of_forecast = new Carbon( $forecast->company->start_of_forecast );

        $financing=array();

        $amount_received_arr=array();
        for ($j = 1; $j < 13; $j++) {
            $amount_received_arr['amount_m_' . $j] = null;
        }
        for ($j = 1; $j < 6; $j++) {
            $amount_received_arr['amount_y_' . $j] = null;
        }

        $temp_arr=array();
        for ($j = 1; $j < 13; $j++) {
            $temp_arr['amount_m_' . $j] = null;
        }
        for ($j = 1; $j < 6; $j++) {
            $temp_arr['amount_y_' . $j] = null;
        }

        //$amount_received_arr[0]=['loan'=>$temp_arr];
        //$financing[0]=['amount_received'=>$amount_received_arr];


        //return $forecast;

        for ($i=0;$i<count($forecast->financings);$i++)
        {
            if(isset($forecast->financings[$i]->fundable)) {
                if ($forecast->financings[$i]->fundable == 'loan') {

                    $receive_date=new Carbon($forecast->financings[$i]->fundable->receive_date);
                    $temp_arr=array();
                    if($receive_date->year>$start_of_forecast->year)
                    {
                        for($j=1 ; $j<6 ; $j++)
                        {
                            if(($receive_date->year-$start_of_forecast->year)+1==$j)
                            {
                                $temp_arr['amount_y_'.$j]=$forecast->financings[$i]->fundable->amount;
                                $amount_received_arr['amount_y_'.$j]=$temp_arr['amount_y_'.$j];
                                if($amount_received_arr['amount_y_'.$j]==null)
                                {
                                    $amount_received_arr['amount_y_'.$j]=$temp_arr['amount_y_'.$j];
                                }
                                else{
                                    $amount_received_arr['amount_y_'.$j]=$amount_received_arr['amount_y_'.$j]+$temp_arr['amount_y_'.$j];
                                }
                            }
                        }
                    }
                    else{

                        for ($j = 1; $j < 13; $j++) {
                            if(($receive_date->month-$start_of_forecast->month)+1==$j)
                            {
                                $temp_arr['amount_m_'.$j]=$forecast->financings[$i]->fundable->amount;
                                if($amount_received_arr['amount_m_'.$j]==null)
                                {
                                    $amount_received_arr['amount_m_'.$j]=$temp_arr['amount_m_'.$j];
                                }
                                else{
                                    $amount_received_arr['amount_m_'.$j]=$amount_received_arr['amount_m_'.$j]+$temp_arr['amount_m_'.$j];
                                }
                            }
                        }
                    }

                    $amount_received_arr[$i]=[$forecast->financings[$i]->name => $temp_arr];
                }



            }

        }
        return $financing;
    }

}

