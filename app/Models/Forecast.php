<?php

namespace CannaPlan\Models;
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
            if(($personnel->personnel_expenses || $expense->expenses) && $check_other==false)
            {
                $operating_expenses['amount_m_'.$i]=$personnel->personnel_expenses['amount_m_'.$i]+$expense->total['amount_m_'.$i];
            }
            else if(($personnel->other_labor || $expense->expenses) && $check_other==true)
            {
                $operating_expenses['amount_m_'.$i]=$personnel->other_labor['amount_m_'.$i]+$expense->total['amount_m_'.$i];
            }
        }
        for($i=1 ; $i<6 ; $i++)
        {
            if(($personnel->personnel_expenses || $expense->expenses) && $check_other==false)
            {
                $operating_expenses['amount_y_'.$i]=$personnel->personnel_expenses['amount_y_'.$i]+$expense->total['amount_y_'.$i];
            }
            else if(($personnel->other_labor || $expense->expenses) && $check_other==true)
            {
                $operating_expenses['amount_y_'.$i]=$personnel->other_labor['amount_y_'.$i]+$expense->total['amount_y_'.$i];
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
                        $total_interest_paid['amount_m_'.$j]=$total_interest_paid['amount_m_'.$j]+$payments['finance'][$i]['interest_paid']['amount_m_'.$j];
                    }
                     for($j=1 ; $j<6 ; $j++)
                     {
                         $total_interest_paid['amount_y_'.$j]=$total_interest_paid['amount_y_'.$j]+$payments['finance'][$i]['interest_paid']['amount_y_'.$j];
                     }
            }
            $profit_loss['interest_expense']=$total_interest_paid;
        }

        //adding income tax
        $tax=Tax::getTaxByForecastId($id);
        $income_tax=$tax['income_tax']['accrued'];
        $profit_loss['income_tax']=$income_tax;

        //calculating total expenses
        for($i=1 ; $i<13 ; $i++)
        {
            if($cost->costs || $expense->expenses)
            {
                if($cost->total['amount_m_'.$i] || $expense->total['amount_m_'.$i] || $income_tax['amount_m_'.$i])
                {
                    $total_expenses['amount_m_'.$i]=$income_tax['amount_m_'.$i]+$cost->total['amount_m_'.$i]+$operating_expenses['amount_m_'.$i]+$total_interest_paid['amount_m_'.$i];
                }

            }
        }
        for($i=1 ; $i<6 ; $i++)
        {
            if($cost->costs || $expense->expenses)
            {
                if($cost->total['amount_y_'.$i] || $expense->total['amount_y_'.$i] || $income_tax['amount_y_'.$i])
                {
                    $total_expenses['amount_y_'.$i]=$income_tax['amount_y_'.$i]+$cost->total['amount_y_'.$i]+$operating_expenses['amount_y_'.$i]+$total_interest_paid['amount_y_'.$i];
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
            if($asset->amount_type=="constant")
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
                    if($asset['amount_y_'.$i]!==null)
                    {
//                        if($asset->asset_duration_type=='current')
//                        {
//                            if($i==1)
//                            {
//                                $asset_total['amount_y_'.$i] = $asset_total['amount_y_'.$i]-($asset->amount*$total_months);
//                                $depreciation_and_amortization['amount_y_'.$i]=$depreciation_and_amortization['amount_y_'.$i]+(($total_months*$asset->amount)-($asset['amount_y_'.($i)]));
//                            }
//                            else
//                            {
//                                $asset_total['amount_y_'.$i] = $asset_total['amount_y_'.$i]-($asset->amount*12);
//                                $depreciation_and_amortization['amount_y_'.$i]=$depreciation_and_amortization['amount_y_'.$i]+(12*$asset->amount-($asset['amount_y_'.($i)]-$asset['amount_y_'.($i-1)]));
//                            }
//                        }
//                        else{
//                            if($i==1)
//                            {
//                                $asset_total['amount_y_'.$i] = $asset_total['amount_y_'.$i]-($asset->amount*$total_months);
//                                $depreciation_and_amortization['amount_y_'.$i]=$depreciation_and_amortization['amount_y_'.$i]+(($total_months*$asset->amount)-($asset['amount_y_'.($i)]));
//                            }
//                            else
//                            {
//                                $asset_total['amount_y_'.$i] = $asset_total['amount_y_'.$i]-($asset->amount*12);
//                                $depreciation_and_amortization['amount_y_'.$i]=$depreciation_and_amortization['amount_y_'.$i]+(12*$asset->amount-($asset['amount_y_'.($i)]-$asset['amount_y_'.($i-1)]));
//                            }
//                        }
                        if($i==1)
                        {
                            $asset_total['amount_y_'.$i] = $asset_total['amount_y_'.$i]-($asset->amount*$total_months);
                            $depreciation_and_amortization['amount_y_'.$i]=$depreciation_and_amortization['amount_y_'.$i]+(($total_months*$asset->amount)-($asset['amount_y_'.($i)]));
                        }
                        else
                        {
                            $asset_total['amount_y_'.$i] = $asset_total['amount_y_'.$i]-($asset->amount*12);
                            $depreciation_and_amortization['amount_y_'.$i]=$depreciation_and_amortization['amount_y_'.$i]+(12*$asset->amount-($asset['amount_y_'.($i)]-$asset['amount_y_'.($i-1)]));
                        }

                    }
                }
            }
            else
            {
                $found=0;
                $total_months=0;
                $total_years=0;
                $is_depreciation_end=true;
                $total_depreciation=0;
                for($i=1;$i<13;$i++)
                {
                    if($asset['amount_m_'.$i] && $found==0)
                    {
                        $asset_total['amount_m_'.$i] = $asset_total['amount_m_'.$i]-$asset->amount;
                        $found=1;
                    }
                    if($asset['amount_m_'.$i]!==null && $is_depreciation_end)
                    {
                        $total_months++;
                        $depreciation_and_amortization['amount_m_'.$i]=$depreciation_and_amortization['amount_m_'.$i]+round($asset['asset_duration']['dep_monthly']);

                        $total_depreciation=$total_depreciation+$depreciation_and_amortization['amount_m_'.$i];
                        if ($asset['amount_m_'.$i]===0)
                        {
                            $is_depreciation_end=false;
                            $total_months--;
                        }

                    }
                }
                $found=0;
                $is_current_added=true;
                $will_sell_cost_added=false;
                for($i=1;$i<6;$i++)
                {
                    if($asset['amount_y_'.$i] && $found==0)
                    {
                        $asset_total['amount_y_'.$i] = $asset_total['amount_y_'.$i]-$asset->amount;
                        $found=1;
                    }
                    else if($asset->asset_duration_type=='current' && $found==0)
                    {
                        $asset_total['amount_y_'.$i] = $asset_total['amount_y_'.$i]-$asset->amount;
                        $found=1;
                    }
                    if($asset['amount_y_'.$i]!==null)
                    {
                        $total_years++;
                        if($i==1)
                        {
                            $depreciation_and_amortization['amount_y_'.$i]=$depreciation_and_amortization['amount_y_'.$i]+round($asset['asset_duration']['dep_monthly']*$total_months);
                            if($asset->asset_duration_type=='current')
                            {
                                $is_current_added=false;
                            }
                        }
                        else if($asset->asset_duration_type=='current' && $is_current_added)
                        {
                            $depreciation_and_amortization['amount_y_'.$i]=$depreciation_and_amortization['amount_y_'.$i]+($asset['asset_duration']['dep_monthly']*($asset['asset_duration']['month']));
                            $is_current_added=false;
                        }
                        else if($asset->asset_duration_type=='long_term')
                        {
                            if($total_years<=$asset['asset_duration']['year'])
                                $depreciation_and_amortization['amount_y_'.$i]=$depreciation_and_amortization['amount_y_'.$i]+round($asset['asset_duration']['dep_yearly']);
                        }
                    }
                    else if(!$will_sell_cost_added && $asset['asset_duration']['will_sell']==1)
                    {
                        $depreciation_and_amortization['amount_y_'.$i]=$depreciation_and_amortization['amount_y_'.$i]+round($asset['asset_duration']['dep_yearly']-$asset['asset_duration']['dep_monthly']);
                        $will_sell_cost_added=true;
                    }
                }
                if($asset['asset_duration']['will_sell']==1)
                {
                    $include_asset_gain_loss_status=true;
                    $start_of_forecast= new DateTime($forecast->company['start_of_forecast']);
                    $date=date($asset['asset_duration']['selling_date']);
                    $d1 = new DateTime($date);
                    $diff_month=$start_of_forecast->diff($d1)->m;
                    $diff_year=$start_of_forecast->diff($d1)->y;

                    if($diff_year==0)
                    {
                        if($diff_month==0)
                        {
                            $asset_total['amount_m_1'] = $asset_total['amount_m_1']-$asset->amount;
                            $asset_total['amount_y_1'] = $asset_total['amount_y_1']-($asset['amount']-$asset['asset_duration']['selling_amount']);
                            $asset_total['amount_m_1'] = $asset_total['amount_m_1']+$asset['asset_duration']['selling_amount'];
                            $asset_sale_gain_loss['amount_m_1']=$asset_sale_gain_loss['amount_m_1']+round($asset->amount-$asset['asset_duration']['selling_amount']-(($total_months)*$asset['asset_duration']['dep_monthly']));
                            $asset_sale_gain_loss['amount_y_1']=$asset_sale_gain_loss['amount_y_1']+$asset_sale_gain_loss['amount_m_1'];

                        }
                        else
                        {
                            $asset_total['amount_m_'.($diff_month)] = $asset_total['amount_m_'.($diff_month)]+$asset['asset_duration']['selling_amount'];
                            $asset_sale_gain_loss['amount_m_'.($diff_month)]=$asset_sale_gain_loss['amount_m_'.($diff_month)]+round($asset->amount-$asset['asset_duration']['selling_amount']-(($total_months)*$asset['asset_duration']['dep_monthly']));
                        }
                    }
                    else
                    {
                        $asset_total['amount_y_'.($diff_year+1)] = $asset_total['amount_y_'.($diff_year+1)]+$asset['asset_duration']['selling_amount'];
                        $asset_sale_gain_loss['amount_y_'.($diff_year+1)]=$asset_sale_gain_loss['amount_y_'.($diff_year+1)]+round($asset->amount-$asset['asset_duration']['selling_amount']-((($total_years*12)+($total_months-1))*$asset['asset_duration']['dep_monthly']));
                    }
                }

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

        return $project_cash_flow;

    }
}
