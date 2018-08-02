<?php

namespace CannaPlan\Models;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
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
}
