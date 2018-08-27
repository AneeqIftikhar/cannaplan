<?php

namespace CannaPlan\Models;
use CannaPlan\Http\Controllers\InitialBalanceSettingsController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use DateTime;
/**
 * @property int $id
 * @property int $company_id
 * @property string $name
 * @property int $burden_rate
 * @property string $deleted_at
 * @property string $remember_token
 * @property string $created_at
 * @property string $updated_at
 * @property Company $company
 * @property Asset[] $assets
 * @property Cost[] $costs
 * @property Dividend[] $dividends
 * @property Expense[] $expenses
 * @property Financing[] $financings
 * @property Tax[] $taxes
 */
class Forecast extends Model
{
    use SoftDeletes;
    protected $dates=['deleted_at'];
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'forecast';

    /**
     * @var array
     */
    protected $fillable = ['name', 'burden_rate', 'is_started'];
    protected $gaurded =['id', 'company_id' , 'created_by'];

    public static function boot() {
        parent::boot();

        // create a event to happen on saving
        static::creating(function($table)  {
            $table->created_by = Auth::user()->id;
        });

        static::deleting(function($forecast) {
            foreach ($forecast->revenues()->get() as $revenue) {
                $revenue->delete();
            }
            foreach ($forecast->assets()->get() as $asset) {
                $asset->delete();
            }
            foreach ($forecast->costs()->get() as $cost) {
                $cost->delete();
            }
            foreach ($forecast->dividends()->get() as $dividend) {
                $dividend->delete();
            }
            foreach ($forecast->expenses()->get() as $expense) {
                $expense->delete();
            }
            foreach ($forecast->financings()->get() as $finance) {
                $finance->delete();
            }
            foreach ($forecast->taxes()->get() as $tax) {
                $tax->delete();
            }
            $forecast->initialBalanceSettings()->delete();
        });
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function company()
    {
        return $this->belongsTo('CannaPlan\Models\Company');
    }

    public function revenues()
    {
        return $this->hasMany('CannaPlan\Models\Revenue');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function assets()
    {
        return $this->hasMany('CannaPlan\Models\Asset');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function costs()
    {
        return $this->hasMany('CannaPlan\Models\Cost');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function dividends()
    {
        return $this->hasMany('CannaPlan\Models\Dividend');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function expenses()
    {
        return $this->hasMany('CannaPlan\Models\Expense');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function financings()
    {
        return $this->hasMany('CannaPlan\Models\Financing');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function taxes()
    {
        return $this->hasMany('CannaPlan\Models\Tax');
    }

    public function initialBalanceSettings()
    {
        return $this->hasOne('CannaPlan\Models\InitialBalanceSettings');
    }

    public static function getProfitLossByForecastId($id)
    {
        $forecast=Forecast::where('id',$id)->first();
        $initial_balance_settings=$forecast->initialBalanceSettings;

        $profit_loss=array();

        $gross_margin=array();
        $gross_margin_percent=array();
        $operating_expenses=array();
        $operating_income=array();
        $total_expenses=array();
        $net_profit=array();
        $net_profit_percent=array();
        $total_interest_paid=array();


        //initializing arrays
        for($i=1 ; $i<13 ; $i++)
        {
            $gross_margin['amount_m_'.$i]=null;
            $gross_margin_percent['amount_m_'.$i]=null;
            $operating_expenses['amount_m_'.$i]=null;
            $operating_income['amount_m_'.$i]=null;
            $total_expenses['amount_m_'.$i]=null;
            $net_profit['amount_m_'.$i]=null;
            $net_profit_percent['amount_m_'.$i]=null;
            $total_interest_paid['amount_m_'.$i]=null;
        }
        for($i=1 ; $i<6 ; $i++)
        {
            $gross_margin['amount_y_'.$i]=null;
            $gross_margin_percent['amount_y_'.$i]=null;
            $operating_expenses['amount_y_'.$i]=null;
            $operating_income['amount_y_'.$i]=null;
            $total_expenses['amount_y_'.$i]=null;
            $net_profit['amount_y_'.$i]=null;
            $net_profit_percent['amount_y_'.$i]=null;
            $total_interest_paid['amount_y_'.$i]=null;
        }

        //adding revenue
        $revenue=Revenue::getRevenueByForecastId($id);
        if($revenue->revenues)
        {
            $profit_loss['revenue']=$revenue;
        }
        else{//if no revenue is entered
            $profit_loss['revenue']=$revenue->total;
        }

        //adding cost
        $cost=Cost::getCostByForecastId($id);

        if($cost->costs)
        {
            $profit_loss['cost']=$cost;
        }
        else{//if no cost is entered
            $profit_loss['cost']=$cost->total;
        }

        //calculating gross margin
        for($i=1 ; $i<13 ; $i++)
        {
            if($revenue->revenues || $cost->costs)
            {
                if($revenue->total['amount_m_'.$i] || $cost->total['amount_m_'.$i])
                {
                    $gross_margin['amount_m_'.$i]=$revenue->total['amount_m_'.$i]-$cost->total['amount_m_'.$i];
                }

            }
        }
        for($i=1 ; $i<6 ; $i++)
        {
            if($revenue->revenues || $cost->costs)
            {
                if($revenue->total['amount_y_'.$i] || $cost->total['amount_y_'.$i])
                {
                    $gross_margin['amount_y_'.$i]=$revenue->total['amount_y_'.$i]-$cost->total['amount_y_'.$i];
                }
            }
        }
        $profit_loss['gross_margin']=$gross_margin;

        //calculating gross margin %
        for($i=1 ; $i<13 ; $i++)
        {
            if($revenue->revenues)
            {
                if($revenue->total['amount_m_'.$i] && $revenue->total['amount_m_'.$i]>0)
                {
                    $gross_margin_percent['amount_m_'.$i]=round(($gross_margin['amount_m_'.$i]/$revenue->total['amount_m_'.$i])*100).'%';
                }

            }
        }
        for($i=1 ; $i<6 ; $i++)
        {
            if($revenue->revenues)
            {
                if($revenue->total['amount_y_'.$i] && $revenue->total['amount_y_'.$i]>0)
                {
                    $gross_margin_percent['amount_y_'.$i]=round(($gross_margin['amount_y_'.$i]/$revenue->total['amount_y_'.$i])*100).'%';
                }

            }
        }
        $profit_loss['gross_margin_percent']=$gross_margin_percent;

        //adding operating expense
        $expense=Expense::getExpenseByForecastId($id);
        $personnel=Cost::getPersonnelByForecastId($id);
        $check_other=false;

        //calling asset depreciation
        $asset=Asset::getDepreciationOfAssetByForecast($id);

        if($asset['current_include_status'])
        {
            $operating_expenses['amortization_of_other_current_assets']=$asset['current'];
        }
        if($personnel->personnel_expenses)
        {
            $operating_expenses['saleries_and_wages']=$personnel->personnel_expenses['saleries_and_wages'];
            $operating_expenses['employee_related_expanses']=$personnel->personnel_expenses['employee_related_expanses'];
        }
        else if($personnel->other_labor)
        {
            $check_other=true;
            $operating_expenses['saleries_and_wages']=$personnel->other_labor['saleries_and_wages'];
            $operating_expenses['employee_related_expanses']=$personnel->other_labor['employee_related_expanses'];
        }
        if($expense->expenses)
        {
            $operating_expenses['expenses']=$expense->expenses;
        }

        //calculating operating expenses
        for($i=1 ; $i<13 ; $i++)
        {
            if(($personnel->personnel_expenses || $expense->expenses || $asset->current_include_status) && $check_other==false)
            {
                $operating_expenses['amount_m_'.$i]=$personnel->personnel_expenses['amount_m_'.$i]+$expense->total['amount_m_'.$i]+$asset['current']['amount_m_'.$i];
            }
            else if(($personnel->other_labor || $expense->expenses || $asset->current_include_status) && $check_other==true)
            {
                $operating_expenses['amount_m_'.$i]=$personnel->other_labor['amount_m_'.$i]+$expense->total['amount_m_'.$i]+$asset['current']['amount_m_'.$i];
            }
        }
        for($i=1 ; $i<6 ; $i++)
        {
            if(($personnel->personnel_expenses || $expense->expenses || $asset->current_include_status) && $check_other==false)
            {
                $operating_expenses['amount_y_'.$i]=$personnel->personnel_expenses['amount_y_'.$i]+$expense->total['amount_y_'.$i]+$asset['current']['amount_y_'.$i];
            }
            else if(($personnel->other_labor || $expense->expenses || $asset->current_include_status) && $check_other==true)
            {
                $operating_expenses['amount_y_'.$i]=$personnel->other_labor['amount_y_'.$i]+$expense->total['amount_y_'.$i]+$asset['current']['amount_y_'.$i];
            }
        }
        $profit_loss['operating_expenses']=$operating_expenses;

        //calculating operating income
        for($i=1 ; $i<13 ; $i++)
        {
            if($revenue->revenues || $cost->costs || $expense->expenses)
            {
                if($revenue->total['amount_m_'.$i] || $cost->total['amount_m_'.$i] || $operating_expenses['amount_m_'.$i])
                {
                    $operating_income['amount_m_'.$i]=$revenue->total['amount_m_'.$i]-($operating_expenses['amount_m_'.$i]+$cost->total['amount_m_'.$i]);
                }

            }
        }
        for($i=1 ; $i<6 ; $i++)
        {
            if($revenue->revenues || $cost->costs || $expense->expenses)
            {
                if($revenue->total['amount_y_'.$i] || $cost->total['amount_y_'.$i] || $operating_expenses['amount_y_'.$i])
                {
                    $operating_income['amount_y_'.$i]=$revenue->total['amount_y_'.$i]-($operating_expenses['amount_y_'.$i]+$cost->total['amount_y_'.$i]);
                }
            }
        }
        $profit_loss['operating_income']=$operating_income;

        //Interest Expense Calculation
        $finance=Financing::getFinancingByForecastId($id);
        if(isset($finance->payments))
        {
            $payments=$finance->payments;

            for($i=0;$i<count($payments['finance']);$i++)
            {

                    for($j=1 ; $j<13 ; $j++)
                    {
                        if($payments['finance'][$i]['interest_paid']['amount_m_'.$j])
                        {
                            $total_interest_paid['amount_m_'.$j]=$total_interest_paid['amount_m_'.$j]+$payments['finance'][$i]['interest_paid']['amount_m_'.$j];
                        }
                    }
                     for($j=1 ; $j<6 ; $j++)
                     {
                         if($payments['finance'][$i]['interest_paid']['amount_y_'.$j])
                         {
                             $total_interest_paid['amount_y_'.$j]=$total_interest_paid['amount_y_'.$j]+$payments['finance'][$i]['interest_paid']['amount_y_'.$j];
                         }
                     }
            }
            $profit_loss['interest_expense']=$total_interest_paid;
        }

        //adding income tax
        $tax=Tax::getTaxByForecastId($id);
        $income_tax=$tax['income_tax']['accrued'];
        $profit_loss['income_tax']=$income_tax;

        if($asset['long_term_include_status'])
        {
            $profit_loss['depreciation_and_amortization']=$asset['long_term'];
        }

        //calculating total expenses
        for($i=1 ; $i<13 ; $i++)
        {
            if($cost->costs || $expense->expenses)
            {
                if($cost->total['amount_m_'.$i] || $operating_expenses['amount_m_'.$i] || $income_tax['amount_m_'.$i] || $total_interest_paid['amount_m_'.$i] || $asset['long_term_include_status'])
                {
                    $total_expenses['amount_m_'.$i]=$income_tax['amount_m_'.$i]+$cost->total['amount_m_'.$i]+$operating_expenses['amount_m_'.$i]+$total_interest_paid['amount_m_'.$i]+$asset['long_term']['amount_m_'.$i];
                }
                else{
                    $total_expenses['amount_m_'.$i]=0;
                }
            }
        }
        for($i=1 ; $i<6 ; $i++)
        {
            if($cost->costs || $expense->expenses)
            {
                if($cost->total['amount_y_'.$i] || $operating_expenses['amount_y_'.$i] || $income_tax['amount_y_'.$i] || $total_interest_paid['amount_y_'.$i] || $asset['long_term_include_status'])
                {
                    $total_expenses['amount_y_'.$i]=$income_tax['amount_y_'.$i]+$cost->total['amount_y_'.$i]+$operating_expenses['amount_y_'.$i]+$total_interest_paid['amount_y_'.$i]+$asset['long_term']['amount_y_'.$i];
                }
                else{
                    $total_expenses['amount_y_'.$i]=0;
                }
            }
        }
        $profit_loss['total_expenses']=$total_expenses;

        //calculating net profit
        for($i=1 ; $i<13 ; $i++)
        {
            if($revenue->revenues)
            {
                if($revenue->total['amount_m_'.$i] || $total_expenses['amount_m_'.$i])
                {
                    $net_profit['amount_m_'.$i]=$revenue->total['amount_m_'.$i]-$total_expenses['amount_m_'.$i];
                }

            }
        }
        for($i=1 ; $i<6 ; $i++)
        {
            if($revenue->revenues)
            {
                if($revenue->total['amount_y_'.$i] || $total_expenses['amount_y_'.$i])
                {
                    $net_profit['amount_y_'.$i]=$revenue->total['amount_y_'.$i]-$total_expenses['amount_y_'.$i];
                }

            }
        }
        $profit_loss['net_profit']=$net_profit;

        //calculating net profit percent
        for($i=1 ; $i<13 ; $i++)
        {
            if($revenue->revenues)
            {
                if($revenue->total['amount_m_'.$i] && $revenue->total['amount_m_'.$i]>0)
                {
                    $net_profit_percent['amount_m_'.$i]=round(($net_profit['amount_m_'.$i]/$revenue->total['amount_m_'.$i])*100).'%';
                }
            }
        }
        for($i=1 ; $i<6 ; $i++)
        {
            if($revenue->revenues)
            {
                if($revenue->total['amount_y_'.$i] && $revenue->total['amount_y_'.$i]>0)
                {
                    $net_profit_percent['amount_y_'.$i]=round(($net_profit['amount_y_'.$i]/$revenue->total['amount_y_'.$i])*100).'%';
                }
            }
        }
        $profit_loss['net_profit_percent']=$net_profit_percent;

        return $profit_loss;
    }

    public static function getBalanceSheetByForecastId($id)
    {
        //declaring arrays
        $projected_balance_sheet=array();

        $begin_value_check=false;

        $liabilities_and_equities=array();

        $assets=array();

        $current_asset=array();
        $cash=array();
        $other_current_assets=array();

        $accounts_receivable=array();
        $inventory=array();

        //calling cash flow
        $forecast=Forecast::where('id',$id)->first();
        $cash_flow=Forecast::getCashFlowByForecastId($id);

        //calling asset
        $asset=Asset::getAssetByForecast($id);

        $accumulated_depreciation=array();
        $long_term_assets=array();
        $long_term_assets_total=array();
        $include_other_current_asset=false;
        $include_long_term_asset=false;

        //Liabilities portion
        $liabilities=array();

        $current_liabilities=array();
        $accounts_payable=array();
        $income_tax_payable=array();
        $sales_tax_payable=array();
        $short_term_debt=array();

        $long_term_liabilities=array();
        $long_term_debt=array();
        $include_short_and_long_term_financing=false;
        $include_tax=false;

        //Equity Portion
        $equity=array();
        $retained_earnings=array();
        $earnings=array();

        //calling initial balance settings
        $initial_balance_settings=$forecast->initialBalanceSettings;

        //initializing arrays
        for($i=0 ; $i<13 ; $i++)
        {
            $long_term_assets['amount_m_'.$i]=null;
            $long_term_assets_total['amount_m_'.$i]=null;
            if($initial_balance_settings['long_term_assets']!==null)
            {
                $begin_value_check=true;
                $long_term_assets['amount_m_0']=$initial_balance_settings['long_term_assets'];
                $long_term_assets['amount_m_1']=$initial_balance_settings['long_term_assets'];
                $long_term_assets['amount_y_1']=$initial_balance_settings['long_term_assets'];
            }
            $current_asset['amount_m_'.$i]=null;
            $cash['amount_m_'.$i]=null;
            $other_current_assets['amount_m_'.$i]=null;
            if($initial_balance_settings['long_term_assets']!==null)
            {
                $begin_value_check=true;
                $other_current_assets['amount_m_0']=$initial_balance_settings['other_current_assets'];
            }
            $assets['amount_m_'.$i]=null;

            $liabilities['amount_m_'.$i]=null;

            $current_liabilities['amount_m_'.$i]=null;
            $accounts_payable['amount_m_'.$i]=null;
            $income_tax_payable['amount_m_'.$i]=null;
            $sales_tax_payable['amount_m_'.$i]=null;

            $long_term_liabilities['amount_m_'.$i]=null;

            $equity['amount_m_'.$i]=null;
            $retained_earnings['amount_m_'.$i]=null;
            $earnings['amount_m_'.$i]=null;

            $liabilities_and_equities['amount_m_'.$i]=null;

            $accounts_receivable['amount_m_'.$i]=null;
            $inventory['amount_m_'.$i]=null;

            $accumulated_depreciation['amount_m_'.$i]=null;


            $short_term_debt['amount_m_'.$i]=null;
            $long_term_debt['amount_m_'.$i]=null;

        }
        for($i=1 ; $i<6 ; $i++)
        {
            $long_term_assets['amount_y_'.$i]=null;
            if($initial_balance_settings['long_term_assets']!==null)
            {
                $begin_value_check=true;
                $long_term_assets['amount_y_1']=$initial_balance_settings['long_term_assets'];
            }
            $long_term_assets_total['amount_y_'.$i]=null;
            $current_asset['amount_y_'.$i]=null;
            $cash['amount_y_'.$i]=null;
            $other_current_assets['amount_y_'.$i]=null;
            $assets['amount_y_'.$i]=null;

            $liabilities['amount_y_'.$i]=null;

            $current_liabilities['amount_y_'.$i]=null;
            $accounts_payable['amount_y_'.$i]=null;
            $income_tax_payable['amount_y_'.$i]=null;
            $sales_tax_payable['amount_y_'.$i]=null;

            $long_term_liabilities['amount_y_'.$i]=null;

            $equity['amount_y_'.$i]=null;
            $retained_earnings['amount_y_'.$i]=null;
            $earnings['amount_y_'.$i]=null;

            $liabilities_and_equities['amount_y_'.$i]=null;

            $accounts_receivable['amount_y_'.$i]=null;
            $inventory['amount_y_'.$i]=null;

            $accumulated_depreciation['amount_y_'.$i]=null;


            $short_term_debt['amount_y_'.$i]=null;
            $long_term_debt['amount_y_'.$i]=null;
        }

        //Asset portion
            //current asset
                //cash flow's cash at end of the period will come in cash array
                //if current asset is present it will come in other current asset array
           //long term asset
                //if long asset is present it will come in long term asset with accumulated depreciation from cash flow

        //Liabilities protion
            //CurrentLiabilities
                //income tax payable is accumulated income tax from tax model
                //sales tax payable is accumulated sales tax from tax model
                //short term debt comes from financing if added
            //Long Term Liabilities
                //long term debt comes from financing if added

        //Equity portion
            //accumulated dividend will come in retained earnings
            //accumulated interest paid will come in earnings

        //populating inventory
        if($initial_balance_settings['inventory']!==null)
        {
            $begin_value_check=true;
            for($i=0 ; $i<13 ; $i++)
            {
                $inventory['amount_m_' . $i]=$initial_balance_settings['inventory'];
            }
            for($i=1 ; $i<6 ; $i++)
            {
                $inventory['amount_y_' . $i]=$initial_balance_settings['inventory'];
            }

            $current_asset['inventory']=$inventory;
        }

        //populating accounts receivable
        if($initial_balance_settings['accounts_receivable']!==null)
        {
            $begin_value_check=true;
            $months_to_get_paid=$initial_balance_settings['days_to_get_paid']/30;

            $receivable_depreciation=$initial_balance_settings['accounts_receivable']/$months_to_get_paid;

            $temp=$initial_balance_settings['accounts_receivable'];
            $accounts_receivable['amount_m_0']=$initial_balance_settings['accounts_receivable'];
            for($i=1 ; $i<13 ; $i++)
            {
                if($temp>0)
                {
                    $accounts_receivable['amount_m_'.$i]=round($temp-$receivable_depreciation);
                    $temp=$temp-$receivable_depreciation;
                }
                else
                {
                    $accounts_receivable['amount_m_'.$i]=0;
                }
            }
            for($i=1 ; $i<6 ; $i++)
            {
                $accounts_receivable['amount_y_'.$i]=0;
            }


        }
        $current_asset['accounts_receivable']=$accounts_receivable;

        //tax
        //calling tax
        $tax=Tax::getTaxByForecastId($id);
        $tax_details=$forecast->taxes[0];
        $tax['tax_details']=$tax_details;
        $income_accumulated=0;
        $sales_accumulated=0;
        if($initial_balance_settings['corporate_taxes_payable'])
        {
            $begin_value_check=true;
            $income_accumulated=$initial_balance_settings['corporate_taxes_payable'];
            $income_tax_payable['amount_m_0']=$initial_balance_settings['corporate_taxes_payable'];
        }
        if($initial_balance_settings['sales_taxes_payable'])
        {
            $begin_value_check=true;
            $sales_accumulated=$initial_balance_settings['sales_taxes_payable'];
            $sales_tax_payable['amount_m_0'] =$initial_balance_settings['sales_taxes_payable'];
        }
        for($i=1 ; $i<13 ; $i++) {
            if ($tax['tax_details']['is_started'] || $initial_balance_settings['corporate_taxes_payable'] || $initial_balance_settings['sales_taxes_payable']) {
                $include_tax = true;
                //calculating income tax payable
                $income_tax_payable['amount_m_' . $i] = $income_accumulated + $tax['income_tax']['accrued']['amount_m_' . $i]-$tax['income_tax']['paid']['amount_m_' . $i];
                $income_accumulated = $income_tax_payable['amount_m_' . $i];

                //calculating sales tax payable
                $sales_tax_payable['amount_m_' . $i] = $sales_accumulated + $tax['sales_tax']['accrued']['amount_m_' . $i]-$tax['sales_tax']['paid']['amount_m_' . $i];
                $sales_accumulated = $sales_tax_payable['amount_m_' . $i];
            }
        }
        $income_accumulated=0;
        $sales_accumulated=0;
        if($initial_balance_settings['corporate_taxes_payable'])
        {
            $income_accumulated=$initial_balance_settings['corporate_taxes_payable'];
        }
        if($initial_balance_settings['sales_taxes_payable'])
        {
            $sales_accumulated=$initial_balance_settings['sales_taxes_payable'];
        }
        for($i=1 ; $i<6 ; $i++) {
            if ($tax['tax_details']['is_started'] || $initial_balance_settings['corporate_taxes_payable'] || $initial_balance_settings['sales_taxes_payable']) {
                $include_tax = true;
                //calculating income tax payable
                $income_tax_payable['amount_y_' . $i] = $income_accumulated + $tax['income_tax']['accrued']['amount_y_' . $i] - $tax['income_tax']['paid']['amount_y_' . $i];
                $income_accumulated = $income_tax_payable['amount_y_' . $i];

                //calculating sales tax payable
                $sales_tax_payable['amount_y_' . $i] = $sales_accumulated + $tax['sales_tax']['accrued']['amount_y_' . $i] - $tax['sales_tax']['paid']['amount_y_' . $i];
                $sales_accumulated = $sales_tax_payable['amount_y_' . $i];
            }
        }

        //calling finance
        $financing=Financing::getFinancingByForecastId($id);


        for($i=1 ; $i<13 ; $i++) {
            //populating cash array
            if($cash_flow['cash_at_the_end']['amount_m_' . $i])
            {
                if($initial_balance_settings['cash']!==null)
                {
                    $begin_value_check=true;
                    $cash['amount_m_0']=$initial_balance_settings['cash'];
                }
                $cash['amount_m_' . $i] = $cash_flow['cash_at_the_end']['amount_m_' . $i];
            }

            //populating other current asset array
            if ($asset['total_current']['amount_m_' . $i]) {
                $include_other_current_asset=true;
                $other_current_assets['amount_m_' . $i] = $asset['total_current']['amount_m_' . $i];
            }
            else{
                $other_current_assets['amount_m_' . $i] = 0;
            }

            //calculating current assets
            if($cash['amount_m_'.$i] || $other_current_assets['amount_m_'.$i] || $initial_balance_settings['accounts_receivable'] || $initial_balance_settings['inventory'])
            {
                $current_asset['amount_m_'.$i]=$cash['amount_m_'.$i]+$other_current_assets['amount_m_'.$i]+$accounts_receivable['amount_m_'.$i]+$inventory['amount_m_'.$i];

                if($initial_balance_settings['accounts_receivable'] || $initial_balance_settings['cash'] || $initial_balance_settings['inventory'] || $initial_balance_settings['other_current_assets'])
                {
                    $current_asset['amount_m_0']=$accounts_receivable['amount_m_0']+$inventory['amount_m_0']+$cash['amount_m_0']+$other_current_assets['amount_m_0'];
                }
            }

            //populating long term assets total array
            if ($asset['total_long_term']['amount_m_' . $i]!==null) {
                $include_long_term_asset=true;
                $long_term_assets_total['amount_m_' . $i] = $asset['total_long_term']['amount_m_' . $i];
            }


            //calculating assets array
            if($long_term_assets_total['amount_m_' . $i] || $current_asset['amount_m_'.$i])
            {
                $assets['amount_m_'.$i]=$long_term_assets_total['amount_m_' . $i]+$current_asset['amount_m_'.$i];
            }




            //populating short term debt and long term debt calculating current liabilities and long term liabilities
            if(isset($financing['balance']))
            {
                $include_short_and_long_term_financing=true;

                $short_term_debt['amount_m_'.$i]=$financing['balance']['short_term']['amount_m_'.$i];
                if($short_term_debt['amount_m_'.$i] || $income_tax_payable['amount_m_'.$i] || $sales_tax_payable['amount_m_'.$i])
                {
                    $current_liabilities['amount_m_'.$i]=$income_tax_payable['amount_m_'.$i]+$sales_tax_payable['amount_m_'.$i]+$short_term_debt['amount_m_'.$i];
                    $current_liabilities['amount_m_0']=$income_tax_payable['amount_m_0']+$sales_tax_payable['amount_m_0']+$short_term_debt['amount_m_0'];
                }

                $long_term_debt['amount_m_'.$i]=$financing['balance']['long_term']['amount_m_'.$i];
                $long_term_liabilities['amount_m_'.$i]=$long_term_debt['amount_m_'.$i];

                $liabilities['amount_m_'.$i]=$long_term_liabilities['amount_m_'.$i]+$current_liabilities['amount_m_'.$i];
                $liabilities['amount_m_0']=$long_term_liabilities['amount_m_0']+$current_liabilities['amount_m_0'];
            }

        }

        $sales_accumulated=0;
        $income_accumulated=0;
        for($i=1 ; $i<6 ; $i++)
        {
            //populating cash array
            if($cash_flow['cash_at_the_end']['amount_y_' . $i])
            {
                $cash['amount_y_' . $i] = $cash_flow['cash_at_the_end']['amount_y_' . $i];
            }

            //populating other asset array
            if($asset['total_current']['amount_y_'.$i])
            {
                $other_current_assets['amount_y_'.$i]=$asset['total_current']['amount_y_'.$i];
            }
            else{
                $other_current_assets['amount_y_'.$i]=0;
            }

            //calculating current assets
            if($cash['amount_y_'.$i] || $other_current_assets['amount_y_'.$i] || $initial_balance_settings['accounts_receivable'] || $initial_balance_settings['inventory'])
            {
                $current_asset['amount_y_'.$i]=$cash['amount_y_'.$i]+$other_current_assets['amount_y_'.$i]+$accounts_receivable['amount_y_'.$i]+$inventory['amount_y_'.$i];
            }

            if($asset['total_long_term']['amount_y_'.$i])
            {
                $long_term_assets_total['amount_y_'.$i]=$asset['total_long_term']['amount_y_'.$i];
            }

            //calculating assets array
            if($long_term_assets_total['amount_y_' . $i] || $current_asset['amount_y_'.$i])
            {
                $assets['amount_y_'.$i]=$long_term_assets_total['amount_y_' . $i]+$current_asset['amount_y_'.$i];
            }



            //populating short term debt and long term debt calculating current liabilities and long term liabilities
            if(isset($financing['balance']))
            {
                $include_short_and_long_term_financing=true;
                $short_term_debt['amount_y_'.$i]=$financing['balance']['short_term']['amount_y_'.$i];
                if($short_term_debt['amount_y_'.$i] || $income_tax_payable['amount_y_'.$i] || $sales_tax_payable['amount_y_'.$i])
                {
                    $current_liabilities['amount_y_'.$i]=$income_tax_payable['amount_y_'.$i]+$sales_tax_payable['amount_y_'.$i]+$short_term_debt['amount_y_'.$i];
                }

                $long_term_debt['amount_y_'.$i]=$financing['balance']['long_term']['amount_y_'.$i];
                $long_term_liabilities['amount_y_'.$i]=$long_term_debt['amount_y_'.$i];

                $liabilities['amount_y_'.$i]=$long_term_liabilities['amount_y_'.$i]+$current_liabilities['amount_y_'.$i];
            }
        }







        //calculating accumulated long term assets
        foreach ($asset->assets as $as)
        {
            if($as->asset_duration_type=='long_term')
            {
                $include_long_term_asset = true;
                $start_of_forecast= new DateTime($forecast->company['start_of_forecast']);
                $date=date($as['start_date']);
                $d1 = new DateTime($date);
                $diff_month=$start_of_forecast->diff($d1)->m;
                $diff_year=$start_of_forecast->diff($d1)->y;

                if($diff_year==0)
                {
                    if($as->amount_type=='constant')//for  constant amount type
                    {
                        for($i=1 ; $i<13 ; $i++)
                        {
                            if($diff_month<$i)
                            {
                                $long_term_assets['amount_m_'.$i]=$long_term_assets['amount_m_'.$i]+$as->amount;
                            }
                        }
                        for($i=1 ; $i<6 ; $i++)
                        {
                            if ($i==1)
                            {
                                $long_term_assets['amount_y_'.$i]=$long_term_assets['amount_y_'.$i]+$as->amount*(12-$diff_month);
                            }
                            else
                            {
                                $long_term_assets['amount_y_'.$i]=$long_term_assets['amount_y_'.$i]+$as->amount*12;
                            }
                        }
                    }
                    else{//for one time
                        $long_term_assets['amount_m_'.($diff_month+1)]=$long_term_assets['amount_m_'.($diff_month+1)]+$as->amount;
                        if($as['asset_duration']['will_sell']==1)
                        {
                            $date2 = date($as['asset_duration']['selling_date']);
                            $d2 = new DateTime($date2);
                            $sell_month = $start_of_forecast->diff($d2)->m;
                            $sell_year = $start_of_forecast->diff($d2)->y;
                            if($sell_year==0)
                            {
                                $long_term_assets['amount_m_'.($sell_month+1)]=$long_term_assets['amount_m_'.($sell_month+1)]-$as->amount;
                            }
                            else
                            {
                                $long_term_assets['amount_y_1']=$long_term_assets['amount_y_1']+$as->amount;
                                $long_term_assets['amount_y_'.($sell_year+1)]=$long_term_assets['amount_y_'.($sell_year+1)]-$as->amount;
                            }
                        }
                        else
                        {
                            $long_term_assets['amount_y_1']=$long_term_assets['amount_y_1']+$as->amount;
                        }
                    }
                }
                else{//if diff year is greater than 0

                    if($as->amount_type=='constant')//for  constant amount type
                    {
                        for($i=2 ; $i<6 ; $i++)
                        {
                            if($diff_year<$i)
                            {
                                $long_term_assets['amount_y_'.$i]=$long_term_assets['amount_y_'.$i]+$as->amount*12;
                            }
                        }
                    }
                    else{//for one time
                        $long_term_assets['amount_m_'.($diff_year+1)]=$long_term_assets['amount_m_'.($diff_year+1)]+$as->amount;
                        if($as['asset_duration']['will_sell']==1) {
                            $date2 = date($as['asset_duration']['selling_date']);
                            $d2 = new DateTime($date2);
                            $sell_month = $start_of_forecast->diff($d2)->m;
                            $sell_year = $start_of_forecast->diff($d2)->y;
                            $long_term_assets['amount_m_'.($sell_year+1)]=$long_term_assets['amount_m_'.($sell_year+1)]-$as->amount;
                        }
                    }
                }
            }
        }
        for($i=2;$i<13;$i++)
        {
            $long_term_assets['amount_m_'.$i]=$long_term_assets['amount_m_'.$i]+$long_term_assets['amount_m_'.($i-1)];
        }
        for($i=2;$i<6;$i++)
        {
            $long_term_assets['amount_y_'.$i]=$long_term_assets['amount_y_'.$i]+$long_term_assets['amount_y_'.($i-1)];
        }

        for($i=1 ; $i<13 ; $i++) {
            //calculating accumulated depreciation

            if($long_term_assets_total['amount_m_'.$i] || $long_term_assets['amount_m_'.$i])
            {
                $accumulated_depreciation['amount_m_0']=$initial_balance_settings['accumulated_depreciation']*-1;
                $accumulated_depreciation['amount_m_' . $i] = $long_term_assets_total['amount_m_'.$i]-$long_term_assets['amount_m_'.$i];
            }
//            if($initial_balance_settings['accumulated_depreciation']!==null)
//            {
//                $accumulated_depreciation['amount_m_0']=$initial_balance_settings['accumulated_depreciation']*-1;
//                $accumulated_depreciation['amount_m_'.$i]=($initial_balance_settings['accumulated_depreciation']*-1);
//            }
        }
        for($i=1 ; $i<6 ; $i++)
        {
            //calculating accumulated depreciation
            if($long_term_assets_total['amount_y_'.$i] || $long_term_assets['amount_y_'.$i])
            {
                $accumulated_depreciation['amount_y_' . $i] = $long_term_assets_total['amount_y_'.$i]-$long_term_assets['amount_y_'.$i];
            }
//            if($initial_balance_settings['accumulated_depreciation']!==null)
//            {
//                $accumulated_depreciation['amount_y_' . $i]=($initial_balance_settings['accumulated_depreciation']*-1);
//            }
        }
        if($long_term_assets['amount_m_0'] || $accumulated_depreciation['amount_m_0'])
        {
            $long_term_assets_total['amount_m_0']=$long_term_assets['amount_m_0']+$accumulated_depreciation['amount_m_0'];
        }
        if($long_term_assets_total['amount_m_0'] || $current_asset['amount_m_0'])
        {
            $assets['amount_m_0']=$long_term_assets_total['amount_m_0']+$current_asset['amount_m_0'];
        }

        //calculating accounts payable
        if($initial_balance_settings['accounts_payable']!==null)
        {
            $begin_value_check=true;
            $months_to_pay=$initial_balance_settings['days_to_pay']/30;
            $payable_depreciation=0;
            $temp=$initial_balance_settings['accounts_payable'];

            for($i=0 ; $i<13 ; $i++)
            {
                if($temp>0)
                {
                    $accounts_payable['amount_m_'.$i]=round($temp-$payable_depreciation);
                    $payable_depreciation=$initial_balance_settings['accounts_payable']/$months_to_pay;
                    $temp=$temp-$payable_depreciation;
                }
                else
                {
                    $accounts_payable['amount_m_'.$i]=0;
                }
                $current_liabilities['amount_m_'.$i]=$current_liabilities['amount_m_'.$i]+$accounts_payable['amount_m_'.$i];

            }
            $liabilities['amount_m_0']=$current_liabilities['amount_m_0'];
            for($i=0 ; $i<6 ; $i++)
            {
                $accounts_payable['amount_y_'.$i]=0;
            }


        }
        $current_liabilities['accounts_payable']=$accounts_payable;


        //calling dividend
        $dividend=Dividend::getDividendByForecast($id);

        for($i=1 ; $i<13 ; $i++)
        {
            if($i==1)
            {
                if($dividend['total']['amount_m_'.$i])
                    $retained_earnings['amount_m_'.$i]=($dividend['total']['amount_m_'.$i]*-1);
            }
            else{
                if($retained_earnings['amount_m_' . ($i-1)] || $dividend['total']['amount_m_' . $i])
                {
                    $retained_earnings['amount_m_' . $i] = $retained_earnings['amount_m_' . ($i-1)] + ($dividend['total']['amount_m_' . $i] * -1);
                }
            }

            //calculating equity
            if($liabilities['amount_m_' . $i] || $assets['amount_m_' . $i])
            {
                $equity['amount_m_' . $i]=$assets['amount_m_' . $i]-$liabilities['amount_m_' . $i];
                $equity['amount_m_0']=$assets['amount_m_0']-$liabilities['amount_m_0'];
            }

            //calculating Liabilities and Equities
            if($equity['amount_m_' . $i] || $liabilities['amount_m_' . $i])
            {
                $liabilities_and_equities['amount_m_' . $i]=$equity['amount_m_' . $i] + $liabilities['amount_m_' . $i];
                $liabilities_and_equities['amount_m_0']=$equity['amount_m_0'] + $liabilities['amount_m_0'];
            }

        }
        for($i=1 ; $i<6 ; $i++)
        {
            if($i==1)
            {
                if($dividend['total']['amount_y_'.$i])
                    $retained_earnings['amount_y_'.$i]=($dividend['total']['amount_y_'.$i]*-1);
            }
            else{
                if($retained_earnings['amount_y_' . ($i-1)] || $dividend['total']['amount_y_' . $i])
                {
                    $retained_earnings['amount_y_' . $i] = $retained_earnings['amount_y_' . ($i-1)] + ($dividend['total']['amount_y_' . $i] * -1);
                }
            }

            //calculating equity
            if($liabilities['amount_y_' . $i] || $assets['amount_y_' . $i])
            {
                $equity['amount_y_' . $i]=$assets['amount_y_' . $i]-$liabilities['amount_y_' . $i];
            }

            //calculating Liabilities and Equities
            if($equity['amount_y_' . $i] || $liabilities['amount_y_' . $i])
            {
                $liabilities_and_equities['amount_y_' . $i]=$equity['amount_y_' . $i] + $liabilities['amount_y_' . $i];
            }
        }

        //calculating retained earnings
        if($initial_balance_settings['retained_earnings'])
        {
            for($i=0 ; $i<13 ; $i++) {

                $retained_earnings['amount_m_' . $i]=$retained_earnings['amount_m_' . $i]+$initial_balance_settings['retained_earnings'];
            }
            for($i=1 ; $i<6 ; $i++)
            {
                if($i==1)
                {
                    $retained_earnings['amount_y_' . $i]=$retained_earnings['amount_y_' . $i]+$initial_balance_settings['retained_earnings'];
                }
                else{
                    $retained_earnings['amount_y_' . $i]=$retained_earnings['amount_y_' . $i]+$equity['amount_y_'.($i-1)];
                }
            }
        }

        //calculating earnings
        for($i=1 ; $i<13 ; $i++)
        {
            $earnings['amount_m_'.$i]=$equity['amount_m_'.$i]-$retained_earnings['amount_m_'.$i];
        }
        for($i=1 ; $i<6 ; $i++)
        {
            $earnings['amount_y_'.$i]=$equity['amount_y_'.$i]-$retained_earnings['amount_y_'.$i];
        }


        //storing arrays
        if($include_other_current_asset)
        {
            $current_asset['other_current_asset']=$other_current_assets;
        }
        $current_asset['cash']=$cash;

        if($include_long_term_asset)
        {
            $long_term_assets_total['long_term_assets']=$long_term_assets;
            $long_term_assets_total['accumulated_depreciation']=$accumulated_depreciation;
        }
        if($include_short_and_long_term_financing)
        {
            $long_term_liabilities['long_term_debt']=$long_term_debt;
            $current_liabilities['short_term_debt']=$short_term_debt;
        }
        if($include_tax)
        {
            $current_liabilities['income_taxes_payable']=$income_tax_payable;
            $current_liabilities['sales_taxes_payable']=$sales_tax_payable;
        }

        //Including Tax paid incurrent liabilities



        $liabilities['current_liabilities']=$current_liabilities;
        $liabilities['long_term_liabilities']=$long_term_liabilities;

        $equity['retained_earnings']=$retained_earnings;
        $equity['earnings']=$earnings;

        $assets['current_assets']=$current_asset;
        $assets['long_term_assets']=$long_term_assets_total;

        $liabilities_and_equities['liabilities']=$liabilities;
        $liabilities_and_equities['equity']=$equity;

        $projected_balance_sheet['liabilities_and_equities']=$liabilities_and_equities;
        $projected_balance_sheet['assets']=$assets;
        $projected_balance_sheet['company']=$forecast->company;
        $projected_balance_sheet['month_0_present']=$begin_value_check;

        return $projected_balance_sheet;
    }

    public static function getCashFlowByForecastId($id)
    {

        $forecast=Forecast::where('id',$id)->first();
        $project_cash_flow=array();
        $net_cash_from_operations=array();
        $project_cash_flow['net_cash_from_operations']=array();
        $project_cash_flow['net_cash_from_financing']=array();
        $project_cash_flow['net_cash_from_investing']=array();
        $net_cash_from_operations=array();
        /*Adding net profit*/
        $profit_loss=Forecast::getProfitLossByForecastId($id);
        $net_profit=$profit_loss['net_profit'];
        $include_tax=false;
        $net_cash_from_operations['net_profit']=$net_profit;

        /*Calculating receivable and payable*/
        $change_in_accounts_receivable=array();
        $change_in_accounts_payable=array();
        for($i=1;$i<13;$i++)
        {
            $change_in_accounts_receivable['amount_m_'.$i] = null;
            $change_in_accounts_payable['amount_m_'.$i] = null;
            $net_cash_from_financing['amount_m_'.$i] = null;
            $net_cash_from_operations['amount_m_'.$i] = null;
        }
        for($i=1;$i<6;$i++)
        {
            $change_in_accounts_receivable['amount_y_'.$i] = null;
            $change_in_accounts_payable['amount_y_'.$i] = null;
            $net_cash_from_financing['amount_y_'.$i] = null;
            $net_cash_from_operations['amount_y_'.$i] = null;
        }

        $intial_balance=$forecast->initialBalanceSettings()->first();
        if($intial_balance['accounts_receivable'])
        {
            if($intial_balance['days_to_get_paid']/30<1)
            {
                $intial_balance['days_to_get_paid']=15;
            }
            $accounts_receivable_months=round($intial_balance['days_to_get_paid']/30);
            $per_month_receivable=$intial_balance['accounts_receivable']/$accounts_receivable_months;
            for($i=1;$i<=$accounts_receivable_months;$i++)
            {
                $change_in_accounts_receivable['amount_m_'.$i] = round($per_month_receivable);
            }
            $change_in_accounts_receivable['amount_y_1'] = $intial_balance['accounts_receivable'];
        }
        if($intial_balance['accounts_payable'])
        {
            $accounts_payable_months=round($intial_balance['days_to_pay']/30);
            if($accounts_payable_months==0)
            {
                $accounts_payable_months=1;
            }
            $per_month_payable=$intial_balance['accounts_payable']/$accounts_payable_months;
            for($i=1;$i<=$accounts_payable_months;$i++)
            {
                $change_in_accounts_payable['amount_m_'.$i] = (-1)*round($per_month_payable);
            }
            $change_in_accounts_payable['amount_y_1'] = (-1)*$intial_balance['accounts_payable'];
        }
        $net_cash_from_operations['change_in_account_receivable']=$change_in_accounts_receivable;//dependent on table to be added
        $net_cash_from_operations['change_in_account_payable']=$change_in_accounts_payable;//dependent on table to be added


        /* Tax calculation */
        if(count($forecast->revenues)>0)//check if there will be tax in cash flow
        {
            $include_tax=true;
            $accrued_paid_difference_income=array();
            $accrued_paid_difference_sales=array();
            $tax=Tax::getTaxByForecastId($id);
            for($i=1;$i<13;$i++)
            {
                $accrued_paid_difference_income['amount_m_'.$i]=$tax['income_tax']['accrued']['amount_m_'.$i]-$tax['income_tax']['paid']['amount_m_'.$i];
                $accrued_paid_difference_sales['amount_m_'.$i]=$tax['sales_tax']['accrued']['amount_m_'.$i]-$tax['sales_tax']['paid']['amount_m_'.$i];

            }
            for($i=1;$i<6;$i++)
            {
                $accrued_paid_difference_income['amount_y_'.$i]=$tax['income_tax']['accrued']['amount_y_'.$i]-$tax['income_tax']['paid']['amount_y_'.$i];
                $accrued_paid_difference_sales['amount_y_'.$i]=$tax['sales_tax']['accrued']['amount_y_'.$i]-$tax['sales_tax']['paid']['amount_y_'.$i];

            }
            $net_cash_from_operations['change_in_income_tax_payable']=$accrued_paid_difference_income;
            $net_cash_from_operations['change_in_sales_tax_payable']=$accrued_paid_difference_sales;

        }

        /* Loan Short and Long Term*/
        $financing=Financing::getFinancingByForecastId($id);
        $include_investment_status=false;
        $include_loan_status=false;
        if(count($financing['financings'])>0)
        {
            $total_investment=array();
            $short_term_change=array();
            $long_term_change=array();
            for($i=1;$i<13;$i++)
            {
                $total_investment['amount_m_'.$i]=null;
                $short_term_change['amount_m_'.$i]=null;
                $long_term_change['amount_m_'.$i]=null;
            }
            for($i=1;$i<6;$i++)
            {
                $total_investment['amount_y_'.$i]=null;
                $short_term_change['amount_y_'.$i]=null;
                $long_term_change['amount_y_'.$i]=null;
            }
            foreach ($financing['amount_received']['finance'] as $amt_rcv)
            {
                if($amt_rcv['fundable_type']=='investment')
                {
                    $include_investment_status=true;
                    for($i=1;$i<13;$i++)
                    {
                        if($amt_rcv['amount_m_'.$i])
                        {
                            $total_investment['amount_m_'.$i]=$total_investment['amount_m_'.$i]+$amt_rcv['amount_m_'.$i];
                        }
                    }
                    for($i=1;$i<6;$i++)
                    {
                        if($amt_rcv['amount_y_'.$i])
                        {
                            $total_investment['amount_y_'.$i]=$total_investment['amount_y_'.$i]+$amt_rcv['amount_y_'.$i];
                        }
                    }
                }
            }
            if($include_investment_status)
                $net_cash_from_financing['investment_received']=$total_investment;

            if(isset($financing['balance']))
            {
                $include_loan_status=true;
                for($i=1;$i<13;$i++)
                {
                    if($i==1)
                    {
                        if(isset($financing['balance']['short_term']['amount_m_0']))
                        {
                            $short_term_change['amount_m_'.$i]=$financing['balance']['short_term']['amount_m_'.$i]-$financing['balance']['short_term']['amount_m_0'];
                        }
                        else
                        {
                            $short_term_change['amount_m_'.$i]=$financing['balance']['short_term']['amount_m_'.$i];
                        }
                        if(isset($financing['balance']['long_term']['amount_m_0']))
                        {
                            $long_term_change['amount_m_'.$i]=$financing['balance']['long_term']['amount_m_'.$i]-$financing['balance']['long_term']['amount_m_0'];
                        }
                        else
                        {
                            $long_term_change['amount_m_'.$i]=$financing['balance']['long_term']['amount_m_'.$i];
                        }
                    }
                    else
                    {
                        $short_term_change['amount_m_'.$i]=$financing['balance']['short_term']['amount_m_'.$i]-$financing['balance']['short_term']['amount_m_'.($i-1)];
                        $long_term_change['amount_m_'.$i]=$financing['balance']['long_term']['amount_m_'.$i]-$financing['balance']['long_term']['amount_m_'.($i-1)];

                    }
                }
                for($i=1;$i<6;$i++)
                {
                    if($i==1)
                    {
                        $short_term_change['amount_y_'.$i]=$financing['balance']['short_term']['amount_y_'.$i];
                        $long_term_change['amount_y_'.$i]=$financing['balance']['long_term']['amount_y_'.$i];
                    }
                    else
                    {
                        $short_term_change['amount_y_'.$i]=$financing['balance']['short_term']['amount_y_'.$i]-$financing['balance']['short_term']['amount_y_'.($i-1)];
                        $long_term_change['amount_y_'.$i]=$financing['balance']['long_term']['amount_y_'.$i]-$financing['balance']['long_term']['amount_y_'.($i-1)];

                    }
                }
                $net_cash_from_financing['change_in_short_term']=$short_term_change;
                $net_cash_from_financing['change_in_long_term']=$long_term_change;

            }

        }


        /*Including Assets, depreciatuion and amortization, Asset Sale and Gain Loss*/
        $test=array();
        $assets=Asset::getAssetByForecast($id);
        $asset_total=array();
        $asset_sale_gain_loss=array();
        $depreciation_and_amortization=array();
        $dividend_and_distributions=array();
        $include_asset_status=false;
        $include_asset_gain_loss_status=false;
        //return $assets;
        for($i=1;$i<13;$i++)
        {
            $asset_total['amount_m_'.$i] = 0;
            $asset_sale_gain_loss['amount_m_'.$i] = 0;
            $depreciation_and_amortization['amount_m_'.$i] = null;
        }
        for($i=1;$i<6;$i++)
        {
            $asset_total['amount_y_'.$i] = 0;
            $asset_sale_gain_loss['amount_y_'.$i] = 0;
            $depreciation_and_amortization['amount_y_'.$i] = null;
        }

        foreach ($assets->assets as $asset)
        {
            $include_asset_status=true;
            $total_months = 0;
            $total_years=0;
            if($asset->amount_type=="constant")
            {
                if($asset->asset_duration_type=='current' || $asset->asset_duration_type=='long_term')
                {

                    $total_months=0;
                    for($i=1;$i<13;$i++)
                    {
                        if($asset['amount_m_'.$i])
                        {
                            $total_months++;
                            $asset_total['amount_m_'.$i] = $asset_total['amount_m_'.$i]-$asset->amount;
                            if($i==1)
                            {
                                $depreciation_and_amortization['amount_m_'.$i]=$depreciation_and_amortization['amount_m_'.$i]+((($i)*$asset->amount)-($asset['amount_m_'.($i)]));
                            }
                            else
                            {
                                $depreciation_and_amortization['amount_m_'.$i]=$depreciation_and_amortization['amount_m_'.$i]+(($asset->amount)-($asset['amount_m_'.($i)]-$asset['amount_m_'.($i-1)]));
                            }

                        }

                    }

                    for($i=1;$i<6;$i++)
                    {
                        if($asset['amount_y_'.$i]) {

                            if ($i == 1) {
                                $asset_total['amount_y_' . $i] = $asset_total['amount_y_' . $i] - ($asset->amount * $total_months);
                                $depreciation_and_amortization['amount_y_' . $i] = $depreciation_and_amortization['amount_y_' . $i] + (($total_months * $asset->amount) - ($asset['amount_y_' . ($i)]));
                            } else {
                                $asset_total['amount_y_' . $i] = $asset_total['amount_y_' . $i] - ($asset->amount * 12);
                                $depreciation_and_amortization['amount_y_' . $i] = $depreciation_and_amortization['amount_y_' . $i] + (12 * $asset->amount - ($asset['amount_y_' . ($i)] - $asset['amount_y_' . ($i - 1)]));
                            }
                        }

                    }
                }

            }
            else if ($asset->amount_type=="one_time")
            {
                if($asset->asset_duration_type=='current')
                {
                    $start_of_forecast= new DateTime($forecast->company['start_of_forecast']);
                    $date=date($asset['start_date']);
                    $d1 = new DateTime($date);
                    $diff_month=$start_of_forecast->diff($d1)->m;
                    $diff_year=$start_of_forecast->diff($d1)->y;
                    if($diff_year==0)
                    {
                        $asset_total['amount_m_'.($diff_month+1)] = $asset_total['amount_m_'.($diff_month+1)]-$asset->amount;
                        $asset_total['amount_y_1'] = $asset_total['amount_y_1']-$asset->amount;
                        for($i=1;$i<13;$i++)
                        {
                            if($diff_month<$i && ($i-$diff_month)<=$asset['asset_duration']['month'])
                            {
                                $depreciation_and_amortization['amount_m_'.$i]=$depreciation_and_amortization['amount_m_'.$i]+round($asset['asset_duration']['dep_monthly']);
                            }
                        }
                        if($asset['asset_duration']['month']+$diff_month>12)
                        {
                            $depreciation_and_amortization['amount_y_1']=$depreciation_and_amortization['amount_y_1']+round($asset['asset_duration']['dep_monthly']*(12-$diff_month));
                            $depreciation_and_amortization['amount_y_2']=$depreciation_and_amortization['amount_y_2']+round($asset['asset_duration']['dep_monthly']*($asset['asset_duration']['month']+$diff_month-12));

                        }
                        else {
                            $depreciation_and_amortization['amount_y_1']=$depreciation_and_amortization['amount_y_1']+round($asset['asset_duration']['dep_monthly']*$asset['asset_duration']['month']);

                        }



                    }
                    else
                    {
                        $asset_total['amount_y_'.($diff_year+1)] = $asset_total['amount_y_'.($diff_year+1)]-$asset->amount;
                        $depreciation_and_amortization['amount_y_'.($diff_year+1)]=$depreciation_and_amortization['amount_y_'.($diff_year+1)]+($asset['asset_duration']['dep_monthly']*($asset['asset_duration']['month']));

                    }


                }
                else if($asset->asset_duration_type=='long_term') {
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
                        $asset_total['amount_m_' . ($diff_month + 1)] = $asset_total['amount_m_' . ($diff_month + 1)] - $asset->amount;
                        $asset_total['amount_y_1'] = $asset_total['amount_y_1'] - $asset->amount;
                        if ($asset['asset_duration']['will_sell'] == 1) {

                            $total_dep=0;
                            $include_asset_gain_loss_status=true;
                            for ($i = 1; $i < 13; $i++) {
                                if ($diff_month < $i && $i<=$sell_month) {
                                    $depreciation_and_amortization['amount_m_' . $i] = $depreciation_and_amortization['amount_m_' . $i] + round($asset['asset_duration']['dep_monthly']);
                                    $total_months++;
                                }
                            }
                            if($total_months != 0)
                            {
                                $depreciation_and_amortization['amount_y_1'] = $depreciation_and_amortization['amount_y_1'] + round($asset['asset_duration']['dep_monthly'] * $total_months);
                                $total_years++;
                                $total_dep=$total_dep+round($asset['asset_duration']['dep_monthly'] * $total_months);
                            }
                            for ($i = 2; $i < 6; $i++) {

                                if($i<=$sell_year  && $total_years<$dep_year)
                                {
                                    $depreciation_and_amortization['amount_y_' . $i] = $depreciation_and_amortization['amount_y_' . $i] + round($asset['asset_duration']['dep_monthly'] * 12);
                                    $total_years++;
                                    $total_dep=$total_dep+round($asset['asset_duration']['dep_monthly'] * 12);
                                }
                                else if($i<=$sell_year  && $total_years==$dep_year && $total_months<12)
                                {
                                    $depreciation_and_amortization['amount_y_' . $i] = $depreciation_and_amortization['amount_y_' . $i] + round($asset['asset_duration']['dep_monthly'] *(12-$total_months));
                                    $total_years++;
                                    $total_dep=$total_dep+round($asset['asset_duration']['dep_monthly'] *(12-$total_months));
                                }
                                else if($i-1==$sell_year && $total_years<=$dep_year && $total_months<12)
                                {
                                    $depreciation_and_amortization['amount_y_' . $i] = $depreciation_and_amortization['amount_y_' . $i] + round($asset['asset_duration']['dep_monthly'] * (12-$total_months));
                                    $total_dep=$total_dep+round($asset['asset_duration']['dep_monthly'] *(12-$total_months));
                                }
                            }
                            if($sell_year==0)
                            {
                                $asset_sale_gain_loss['amount_m_'.($sell_month+1)]=$asset_sale_gain_loss['amount_m_'.($sell_month+1)]+round($asset->amount-$asset['asset_duration']['selling_amount']-$total_dep);
                                $asset_total['amount_m_'.($sell_month+1)] = $asset_total['amount_m_'.($sell_month+1)]+$asset['asset_duration']['selling_amount'];
                            }
                            else
                            {
                                $asset_sale_gain_loss['amount_y_'.($sell_year+1)]=$asset_sale_gain_loss['amount_y_'.($sell_year+1)]+round($asset->amount-$asset['asset_duration']['selling_amount']-$total_dep);
                                $asset_total['amount_y_'.($sell_year+1)] = $asset_total['amount_y_'.($sell_year+1)]+$asset['asset_duration']['selling_amount'];

                            }
                        } else {
                            $total_months = 0;
                            for ($i = 1; $i < 13; $i++) {
                                if ($diff_month < $i) {
                                    $depreciation_and_amortization['amount_m_' . $i] = $depreciation_and_amortization['amount_m_' . $i] + round($asset['asset_duration']['dep_monthly']);
                                    $total_months++;
                                }
                            }
                            for ($i = 1; $i < 6; $i++) {
                                if ($i == 1) {
                                    $depreciation_and_amortization['amount_y_' . $i] = $depreciation_and_amortization['amount_y_' . $i] + round($asset['asset_duration']['dep_monthly'] * $total_months);
                                } else {
                                    $depreciation_and_amortization['amount_y_' . $i] = $depreciation_and_amortization['amount_y_' . $i] + round($asset['asset_duration']['dep_monthly'] * 12);
                                }
                            }

                        }
                    } else {
                        $asset_total['amount_y_' . ($diff_year + 1)] = $asset_total['amount_y_' . ($diff_year + 1)] - $asset->amount;
                        if ($asset['asset_duration']['will_sell'] == 1) {
                            $total_years=0;
                            $total_dep=0;
                            $include_asset_gain_loss_status=true;
                            for ($i = 2; $i < 6; $i++) {
                                if($i<=$sell_year  && $total_years<$dep_year)
                                {
                                    $depreciation_and_amortization['amount_y_' . $i] = $depreciation_and_amortization['amount_y_' . $i] + round($asset['asset_duration']['dep_monthly'] * 12);
                                    $total_dep=$total_dep+round($asset['asset_duration']['dep_monthly'] * 12);
                                    $total_years++;
                                }
                            }
                            $asset_total['amount_y_'.($sell_year+1)] = $asset_total['amount_y_'.($sell_year+1)]+$asset['asset_duration']['selling_amount'];
                            $asset_sale_gain_loss['amount_y_'.($sell_year+1)]=$asset_sale_gain_loss['amount_y_'.($sell_year+1)]+round($asset->amount-$asset['asset_duration']['selling_amount']-$total_dep);
                        } else {
                            for ($i = 2; $i < 6; $i++) {
                                if($diff_year<$i)
                                    $depreciation_and_amortization['amount_y_' . $i] = $depreciation_and_amortization['amount_y_' . $i] + round($asset['asset_duration']['dep_monthly'] * 12);
                            }
                        }
                    }
                }

//                if($asset['asset_duration']['will_sell']==1)
//                {
//                    $include_asset_gain_loss_status=true;
//                    $start_of_forecast= new DateTime($forecast->company['start_of_forecast']);
//                    $date=date($asset['asset_duration']['selling_date']);
//                    $d1 = new DateTime($date);
//                    $diff_month=$start_of_forecast->diff($d1)->m;
//                    $diff_year=$start_of_forecast->diff($d1)->y;
//
//                    if($diff_year==0)
//                    {
//                        if($diff_month==0)
//                        {
//                            $asset_total['amount_m_1'] = $asset_total['amount_m_1']-$asset->amount;
//                            $asset_total['amount_y_1'] = $asset_total['amount_y_1']-($asset['amount']-$asset['asset_duration']['selling_amount']);
//                            $asset_total['amount_m_1'] = $asset_total['amount_m_1']+$asset['asset_duration']['selling_amount'];
//                            $asset_sale_gain_loss['amount_m_1']=$asset_sale_gain_loss['amount_m_1']+round($asset->amount-$asset['asset_duration']['selling_amount']-(($total_months)*$asset['asset_duration']['dep_monthly']));
//                            $asset_sale_gain_loss['amount_y_1']=$asset_sale_gain_loss['amount_y_1']+$asset_sale_gain_loss['amount_m_1'];
//
//                        }
//                        else
//                        {
//                            $asset_total['amount_m_'.($diff_month)] = $asset_total['amount_m_'.($diff_month)]+$asset['asset_duration']['selling_amount'];
//                            $asset_sale_gain_loss['amount_m_'.($diff_month)]=$asset_sale_gain_loss['amount_m_'.($diff_month)]+round($asset->amount-$asset['asset_duration']['selling_amount']-(($total_months)*$asset['asset_duration']['dep_monthly']));
//                        }
//                    }
//                    else
//                    {
//                        if($total_months==0)
//                        {
//                            $asset_total['amount_y_'.($diff_year+1)] = $asset_total['amount_y_'.($diff_year+1)]+$asset['asset_duration']['selling_amount'];
//                            $asset_sale_gain_loss['amount_y_'.($diff_year+1)]=$asset_sale_gain_loss['amount_y_'.($diff_year+1)]+round($asset->amount-$asset['asset_duration']['selling_amount']-((($total_years*12))*$asset['asset_duration']['dep_monthly']));
//                            $total_dep=0;
//                            for($i=1;$i<6;$i++)
//                            {
//
//                            }
//                        }
//                        else
//                        {
//                            $asset_total['amount_y_'.($diff_year+1)] = $asset_total['amount_y_'.($diff_year+1)]+$asset['asset_duration']['selling_amount'];
//                            $asset_sale_gain_loss['amount_y_'.($diff_year+1)]=$asset_sale_gain_loss['amount_y_'.($diff_year+1)]+round($asset->amount-$asset['asset_duration']['selling_amount']-((($total_years*12)+($total_months-1))*$asset['asset_duration']['dep_monthly']));
//
//                        }
//                    }
//                }

            }


        }
        $intial_depreciation_and_amortization=InitialBalanceSettings::calculatePreviousDepreciationAndAmortization($id);
        for ($i = 1; $i < 13; $i++) {
            if ($intial_depreciation_and_amortization['long_term']['amount_m_' . $i] || $intial_depreciation_and_amortization['current']['amount_m_' . $i]) {
                $depreciation_and_amortization['amount_m_' . $i] = $depreciation_and_amortization['amount_m_' . $i] + $intial_depreciation_and_amortization['long_term']['amount_m_' . $i] + $intial_depreciation_and_amortization['current']['amount_m_' . $i];
                $include_asset_status=true;
            }
        }
        for ($i = 1; $i < 6; $i++) {
            if ($intial_depreciation_and_amortization['long_term']['amount_y_' . $i] || $intial_depreciation_and_amortization['current']['amount_y_' . $i]) {
                $depreciation_and_amortization['amount_y_' . $i] = $depreciation_and_amortization['amount_y_' . $i] + $intial_depreciation_and_amortization['long_term']['amount_y_' . $i] + $intial_depreciation_and_amortization['current']['amount_y_' . $i];
                $include_asset_status=true;
            }
        }


        //adding dividend and distributions
        $dividend=Dividend::getDividendByForecast($id);
        $include_dividend=false;

        if(count($dividend['dividends'])>0)
        {
            $include_dividend=true;

            for ($i=1 ; $i<13 ; $i++)
            {
                $dividend_and_distributions['amount_m_'.$i]=$dividend['total']['amount_m_'.$i]*-1;
            }
            for ($i=1 ; $i<6 ; $i++)
            {
                $dividend_and_distributions['amount_y_'.$i]=$dividend['total']['amount_y_'.$i]*-1;
            }

            $net_cash_from_financing['dividend_and_distributions']=$dividend_and_distributions;
        }


        if($include_asset_status)
        {
            $net_cash_from_investing['asset_sold_or_purchased']=$asset_total;
            $net_cash_from_operations['depreciation_and_amortization']=$depreciation_and_amortization;
        }

        if($include_asset_gain_loss_status)
            $net_cash_from_operations['asset_sale_gain_loss']=$asset_sale_gain_loss;

        for($i=1;$i<13;$i++)
        {
            $net_cash_from_operations['amount_m_'.$i]=null;
            $net_cash_from_investing['amount_m_'.$i]=null;

        }
        for($i=1;$i<6;$i++)
        {
            $net_cash_from_operations['amount_y_'.$i]=null;
            $net_cash_from_investing['amount_y_'.$i]=null;

        }

        $cash_at_the_beginning=array();
        $net_change_in_cash=array();
        $cash_at_the_end=array();

        for($i=1;$i<13;$i++)
        {
            $cash_at_the_beginning['amount_m_'.$i]=null;
            $net_change_in_cash['amount_m_'.$i]=null;
            $cash_at_the_end['amount_m_'.$i]=null;

            if($net_profit['amount_m_'.$i])
            {
                $net_cash_from_operations['amount_m_'.$i]=$net_cash_from_operations['amount_m_'.$i]+$net_profit['amount_m_'.$i];
            }
            if($change_in_accounts_receivable['amount_m_'.$i])
            {
                $net_cash_from_operations['amount_m_'.$i]=$net_cash_from_operations['amount_m_'.$i]+$change_in_accounts_receivable['amount_m_'.$i];
            }
            if($change_in_accounts_payable['amount_m_'.$i])
            {
                $net_cash_from_operations['amount_m_'.$i]=$net_cash_from_operations['amount_m_'.$i]+$change_in_accounts_payable['amount_m_'.$i];
            }
            if($include_tax)
            {
                if($net_cash_from_operations['change_in_income_tax_payable']['amount_m_'.$i])
                {
                    $net_cash_from_operations['amount_m_'.$i]=$net_cash_from_operations['amount_m_'.$i]+$net_cash_from_operations['change_in_income_tax_payable']['amount_m_'.$i];
                }
                if($net_cash_from_operations['change_in_sales_tax_payable']['amount_m_'.$i])
                {
                    $net_cash_from_operations['amount_m_'.$i]=$net_cash_from_operations['amount_m_'.$i]+$net_cash_from_operations['change_in_sales_tax_payable']['amount_m_'.$i];
                }
            }
            if($include_asset_status)
            {
                if($net_cash_from_operations['depreciation_and_amortization']['amount_m_'.$i])
                {
                    $net_cash_from_operations['amount_m_'.$i]=$net_cash_from_operations['amount_m_'.$i]+$net_cash_from_operations['depreciation_and_amortization']['amount_m_'.$i];
                }
                if($net_cash_from_investing['asset_sold_or_purchased']['amount_m_'.$i])
                {
                    $net_cash_from_investing['amount_m_'.$i]=$net_cash_from_investing['amount_m_'.$i]+$net_cash_from_investing['asset_sold_or_purchased']['amount_m_'.$i];
                }
            }
            if($include_asset_gain_loss_status && $net_cash_from_operations['asset_sale_gain_loss']['amount_m_'.$i])
            {
                $net_cash_from_operations['amount_m_'.$i]=$net_cash_from_operations['amount_m_'.$i]+$net_cash_from_operations['asset_sale_gain_loss']['amount_m_'.$i];

            }

            if($include_investment_status && $net_cash_from_financing['investment_received']['amount_m_'.$i])
            {
                $net_cash_from_financing['amount_m_'.$i]=$net_cash_from_financing['amount_m_'.$i]+$net_cash_from_financing['investment_received']['amount_m_'.$i];
            }

            if($include_dividend && $net_cash_from_financing['dividend_and_distributions']['amount_m_'.$i])
            {
                $net_cash_from_financing['amount_m_'.$i]=$net_cash_from_financing['amount_m_'.$i]+$net_cash_from_financing['dividend_and_distributions']['amount_m_'.$i];
            }

            if($include_loan_status )
            {
                if($net_cash_from_financing['change_in_short_term']['amount_m_'.$i])
                {
                    $net_cash_from_financing['amount_m_'.$i]=$net_cash_from_financing['amount_m_'.$i]+$net_cash_from_financing['change_in_short_term']['amount_m_'.$i];
                }
                if($net_cash_from_financing['change_in_long_term']['amount_m_'.$i])
                {
                    $net_cash_from_financing['amount_m_'.$i]=$net_cash_from_financing['amount_m_'.$i]+$net_cash_from_financing['change_in_long_term']['amount_m_'.$i];
                }
            }

            $net_change_in_cash['amount_m_'.$i]=$net_cash_from_financing['amount_m_'.$i]+$net_cash_from_investing['amount_m_'.$i]+$net_cash_from_operations['amount_m_'.$i];
            if($i==1)
            {
                $cash_at_the_beginning['amount_m_'.$i]=0;
                if($intial_balance['cash'])
                {
                    $cash_at_the_beginning['amount_m_'.$i]=$intial_balance['cash'];
                }
            }
            else{

                $cash_at_the_beginning['amount_m_'.$i]=$net_change_in_cash['amount_m_'.($i-1)]+$cash_at_the_beginning['amount_m_'.($i-1)];
            }
            $cash_at_the_end['amount_m_'.$i]=$net_change_in_cash['amount_m_'.$i]+$cash_at_the_beginning['amount_m_'.$i];
        }
        for($i=1;$i<6;$i++)
        {
            if($net_profit['amount_y_'.$i])
            {
                $net_cash_from_operations['amount_y_'.$i]=$net_cash_from_operations['amount_y_'.$i]+$net_profit['amount_y_'.$i];
            }
            if($change_in_accounts_receivable['amount_y_'.$i])
            {
                $net_cash_from_operations['amount_y_'.$i]=$net_cash_from_operations['amount_y_'.$i]+$change_in_accounts_receivable['amount_y_'.$i];
            }
            if($change_in_accounts_payable['amount_y_'.$i])
            {
                $net_cash_from_operations['amount_y_'.$i]=$net_cash_from_operations['amount_y_'.$i]+$change_in_accounts_payable['amount_y_'.$i];
            }
            if($include_tax)
            {
                if($net_cash_from_operations['change_in_income_tax_payable']['amount_y_'.$i])
                {
                    $net_cash_from_operations['amount_y_'.$i]=$net_cash_from_operations['amount_y_'.$i]+$net_cash_from_operations['change_in_income_tax_payable']['amount_y_'.$i];
                }
                if($net_cash_from_operations['change_in_sales_tax_payable']['amount_y_'.$i])
                {
                    $net_cash_from_operations['amount_y_'.$i]=$net_cash_from_operations['amount_y_'.$i]+$net_cash_from_operations['change_in_sales_tax_payable']['amount_y_'.$i];
                }
            }
            if($include_asset_status)
            {
                if($net_cash_from_operations['depreciation_and_amortization']['amount_y_'.$i])
                {
                    $net_cash_from_operations['amount_y_'.$i]=$net_cash_from_operations['amount_y_'.$i]+$net_cash_from_operations['depreciation_and_amortization']['amount_y_'.$i];
                }
                if($net_cash_from_investing['asset_sold_or_purchased']['amount_y_'.$i])
                {
                    $net_cash_from_investing['amount_y_'.$i]=$net_cash_from_investing['amount_y_'.$i]+$net_cash_from_investing['asset_sold_or_purchased']['amount_y_'.$i];
                }
            }
            if($include_asset_gain_loss_status && $net_cash_from_operations['asset_sale_gain_loss']['amount_y_'.$i])
            {
                $net_cash_from_operations['amount_y_'.$i]=$net_cash_from_operations['amount_y_'.$i]+$net_cash_from_operations['asset_sale_gain_loss']['amount_y_'.$i];

            }

            if($include_investment_status && $net_cash_from_financing['investment_received']['amount_y_'.$i])
            {
                $net_cash_from_financing['amount_y_'.$i]=$net_cash_from_financing['amount_y_'.$i]+$net_cash_from_financing['investment_received']['amount_y_'.$i];
            }

            if($include_dividend && $net_cash_from_financing['dividend_and_distributions']['amount_y_'.$i])
            {
                $net_cash_from_financing['amount_y_'.$i]=$net_cash_from_financing['amount_y_'.$i]+$net_cash_from_financing['dividend_and_distributions']['amount_y_'.$i];
            }

            if($include_loan_status )
            {
                if($net_cash_from_financing['change_in_short_term']['amount_y_'.$i])
                {
                    $net_cash_from_financing['amount_y_'.$i]=$net_cash_from_financing['amount_y_'.$i]+$net_cash_from_financing['change_in_short_term']['amount_y_'.$i];
                }
                if($net_cash_from_financing['change_in_long_term']['amount_y_'.$i])
                {
                    $net_cash_from_financing['amount_y_'.$i]=$net_cash_from_financing['amount_y_'.$i]+$net_cash_from_financing['change_in_long_term']['amount_y_'.$i];
                }
            }

            $net_change_in_cash['amount_y_'.$i]=$net_cash_from_financing['amount_y_'.$i]+$net_cash_from_investing['amount_y_'.$i]+$net_cash_from_operations['amount_y_'.$i];
            if($i==1)
            {
                $cash_at_the_beginning['amount_y_'.$i]=0;
                if($intial_balance['cash'])
                {
                    $cash_at_the_beginning['amount_y_'.$i]=$intial_balance['cash'];
                }
            }
            else{

                $cash_at_the_beginning['amount_y_'.$i]=$net_change_in_cash['amount_y_'.($i-1)]+$cash_at_the_beginning['amount_y_'.($i-1)];
            }
            $cash_at_the_end['amount_y_'.$i]=$net_change_in_cash['amount_y_'.$i]+$cash_at_the_beginning['amount_y_'.$i];
        }

        $project_cash_flow['net_cash_from_operations']=$net_cash_from_operations;
        $project_cash_flow['net_cash_from_financing']=$net_cash_from_financing;
        $project_cash_flow['net_cash_from_investing']=$net_cash_from_investing;
        $project_cash_flow['cash_at_the_beginning']=$cash_at_the_beginning;
        $project_cash_flow['net_change_in_cash']=$net_change_in_cash;
        $project_cash_flow['cash_at_the_end']=$cash_at_the_end;

        $project_cash_flow['include_tax_status']=$include_tax;
        $project_cash_flow['include_investment_status']=$include_investment_status;
        $project_cash_flow['include_loan_status']=$include_loan_status;
        $project_cash_flow['include_asset_status']=$include_asset_status;
        $project_cash_flow['include_asset_gain_loss_status']=$include_asset_gain_loss_status;
        $project_cash_flow['include_dividend_status']=$include_dividend;
        $project_cash_flow['company']=$forecast->company;
        return $project_cash_flow;

    }


}
