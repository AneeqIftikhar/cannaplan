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

    public static function updateLoan($receive_date, $amount, $interest_rate, $interest_months , $remaining_amount , $fundable)
    {
        $fundable->update(['receive_date' => $receive_date, 'amount' => $amount , 'interest_rate'=>$interest_rate , 'interest_months'=>$interest_months , 'remaining_amount'=>$remaining_amount]);
    }
    public static function updateInvestment($amount_type,$investment_start_date , $amount , $payable_span , $fundable)
    {
        $fundable->update(['amount_type' => $amount_type, 'investment_start_date'=>$investment_start_date ,'amount' => $amount , 'payable_span'=>$payable_span]);
    }
}

