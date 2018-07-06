<?php

namespace CannaPlan\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Auth;

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
        $forecast=Forecast::where('id',$id)->with(['company','revenues','revenues.revenuable'])->first();
        $total_arr=array();
        for ($j = 1; $j < 13; $j++) {
            $total_arr['amount_m_' . $j] = 0;
        }
        for ($j = 1; $j < 6; $j++) {
            $total_arr['amount_y_' . $j] = 0;
        }
        for ($i=0;$i<count($forecast->revenues);$i++)
        {
            if(isset($forecast->revenues[$i]->revenuable_type)) {
                if ($forecast->revenues[$i]->revenuable_type !== 'revenue_only') {
                    $multiplyer = 1;
                    $multiplicand = 1;
                    if ($forecast->revenues[$i]->revenuable_type == 'unit_sale') {
                        $multiplyer = $forecast->revenues[$i]['revenuable']['unit_sold'];
                        $multiplicand = $forecast->revenues[$i]['revenuable']['unit_price'];
                    } else {
                        $multiplyer = $forecast->revenues[$i]['revenuable']['hour'];
                        $multiplicand = $forecast->revenues[$i]['revenuable']['hourly_rate'];
                    }
                    //$forecast->revenues[$i]['revenuable']['amount_m_1'] = 250;
                    for ($j = 1; $j < 13; $j++) {
                        $forecast->revenues[$i]['revenuable']['amount_m_' . $j] = $multiplyer * $multiplicand;
                    }
                    $total = $multiplyer * $multiplicand * 12;
                    $forecast->revenues[$i]['revenuable']['amount_y_1'] = $total;
                    $forecast->revenues[$i]['revenuable']['amount_y_2'] = $total;
                    $forecast->revenues[$i]['revenuable']['amount_y_3'] = $total;
                    $forecast->revenues[$i]['revenuable']['amount_y_4'] = $total;
                    $forecast->revenues[$i]['revenuable']['amount_y_5'] = $total;
                }
                for ($j = 1; $j < 13; $j++) {
                    $total_arr['amount_m_' . $j] = $total_arr['amount_m_' . $j]+ $forecast->revenues[$i]['revenuable']['amount_m_' . $j];
                }
                for ($j = 1; $j < 6; $j++) {
                    $total_arr['amount_y_' . $j] = $total_arr['amount_y_' . $j]+ $forecast->revenues[$i]['revenuable']['amount_y_' . $j];
                }


            }

        }
        $forecast['total'] = $total_arr;
        return $forecast;
    }

}

