<?php

namespace CannaPlan\Models;

use Illuminate\Database\Eloquent\Model;

class InitialBalanceSettings extends Model
{
    use SoftDeletes;
    protected $dates=['deleted_at'];
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'initial_balance_settings';

    protected $fillable = ['cash', 'accounts_receivable', 'days_to_get_paid' , 'inventory' , 'long_term_assets' , 'accumulated_depreciation' , 'depreciation_period', 'other_current_assets', 'amortization_period', 'accounts_payable', 'days_to_pay', 'corporate_taxes_payable', 'sales_taxes_payable', 'prepaid_revenue', 'short_term_debt', 'long_term_debt', 'paid_in_capital', 'retained_earnings'];
    protected $gaurded =['id', 'forecast_id' , 'created_by'];

    public static function boot() {
        parent::boot();

        // create a event to happen on saving
        static::creating(function($table)  {
            $table->created_by = Auth::user()->id;
        });
    }

    public function forecast()
    {
        return $this->belongsTo('CannaPlan\Models\Forecast');
    }
}
