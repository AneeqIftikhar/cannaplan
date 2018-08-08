<?php

namespace CannaPlan\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

/**
 * @property int $id
 * @property int $forecast_id
 * @property string $name
 * @property float $coorporate_tax
 * @property string $coorporate_payable_time
 * @property float $sales_tax
 * @property string $sales_payable_time
 * @property string $deleted_at
 * @property string $remember_token
 * @property string $created_at
 * @property string $updated_at
 * @property Forecast $forecast
 * @property RevenueTax[] $revenueTaxes
 */
class Tax extends Model
{
    use SoftDeletes;
    protected $dates=['deleted_at'];
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'tax';

    /**
     * @var array
     */
    protected $fillable = ['coorporate_tax', 'coorporate_payable_time', 'sales_tax', 'sales_payable_time' , 'is_started'];
    protected $gaurded=['id' , 'forecast_id', 'created_by'];

    public static function boot()
    {
        parent::boot();

        // create a event to happen on saving
        static::creating(function ($table) {
            $table->created_by = Auth::user()->id;
        });
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function forecast()
    {
        return $this->belongsTo('CannaPlan\Models\Forecast');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function revenueTaxes()
    {
        return $this->hasMany('CannaPlan\Models\RevenueTax');
    }
    /* many to many relation*/
    public function revenues()
    {
        return $this->belongsToMany('CannaPlan\Models\Revenue', 'revenue_tax',
            'tax_id', 'revenue_id');
    }

    public static function getTaxByForecastId($id)
    {
        $tax=new Tax();
        $taxes=array();

        $taxes['sales_tax']=$tax->calculateSalesTax($id);


        $taxes['income_tax']=$tax->getIncomeTax($id);



        return $taxes;

    }

    public static function calculateSalesTax($id)
    {
        $rev=Revenue::getRevenueByForecastId($id);

        $forecast=Forecast::where('id','=',$id)->with(['company','taxes' , 'taxes.revenueTaxes'])->first();

        $tax=new Tax();
        $taxes=array();

        $accrued=array();
        $paid=array();
        for($i=1 ; $i<13 ; $i++)
        {
            $accrued['amount_m_'.$i]=null;
            $paid['amount_m_'.$i]=null;
        }
        for($i=1 ; $i<6 ; $i++)
        {
            $accrued['amount_y_'.$i]=null;
            $paid['amount_y_'.$i]=null;
        }


        $quarterly_sum=0;
        $total_quarterly=0;

        $sales_tax_abs=$forecast->taxes[0]->sales_tax/100;

        for($i=1 ; $i<13 ; $i++)
        {
            $accrued['amount_m_'.$i]=$rev->total['amount_m_'.$i]*$sales_tax_abs;



            if($forecast->taxes[0]->sales_payable_time=='annually')
            {
                $paid['amount_m_'.$i]=0;
            }
            else
            {
                if($i==4 || $i==7 || $i==10)
                {
                    $paid['amount_m_'.$i]=$quarterly_sum;
                    $total_quarterly=$total_quarterly+$paid['amount_m_'.$i];
                    $quarterly_sum=0;
                }
                $quarterly_sum=$quarterly_sum+$accrued['amount_m_'.$i];
            }

        }
        for($i=1 ; $i<6 ; $i++)
        {
            $accrued['amount_y_'.$i]=$rev->total['amount_y_'.$i]*$sales_tax_abs;
            if($forecast->taxes[0]->sales_payable_time=='annually')
            {
                if($i==1)
                {
                    $paid['amount_y_'.$i]=0;
                }
                else{
                    $paid['amount_y_'.$i]=$accrued['amount_y_'.($i-1)];
                }

            }
            else
            {
                if($i==1)
                {
                    $paid['amount_y_'.$i]=$total_quarterly;
                }
                else{
                    $paid['amount_y_'.$i]=$accrued['amount_y_'.$i];
                }
            }
        }

        $taxes=['accrued'=>$accrued , 'paid'=>$paid];
        return $taxes;
    }
    public static function getProfitArray($id)
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
        $total_interest_paid=array();
        for($i=1;$i<13;$i++)
        {
            $asset_total['amount_m_'.$i] = 0;
            $total_interest_paid['amount_m_'.$i] = 0;
        }
        for($i=1;$i<6;$i++)
        {
            $asset_total['amount_y_'.$i] = 0;
            $total_interest_paid['amount_y_'.$i] = 0;
        }
        foreach ($assets->assets as $asset)
        {
            if($asset->amount_type=="constant")
            {
                $index=0;
                $year_1_total=0;
                for($i=1;$i<13;$i++)
                {
                    if($asset['amount_m_'.$i])
                    {
                        if($index==0)
                        {
                            $asset_total['amount_m_'.$i] = $asset['amount']- $asset['amount_m_'.$i];
                            $year_1_total=$year_1_total+$asset_total['amount_m_'.$i];
                            $index++;
                        }
                        else
                        {
                            $asset_total['amount_m_'.$i] = ($asset['amount'])- ($asset['amount_m_'.$i]-$asset['amount_m_'.($i-1)]);
                            $year_1_total=$year_1_total+$asset_total['amount_m_'.$i];
                            $index++;
                        }
                    }

                }
                for($i=1;$i<6;$i++)
                {
                    if($i==1)
                    {
                        $asset_total['amount_y_'.$i] =$year_1_total;
                    }
                    else
                    {
                        $asset_total['amount_y_'.$i] = ($asset['amount']*12)- ($asset['amount_y_'.$i]-$asset['amount_y_'.($i-1)]);
                    }
                }


            }

        }
        $financing=Financing::getFinancingByForecastId($id);
        if(isset($financing['payments']['finance']))
        {
            $payment=$financing['payments']['finance'];
            foreach ($payment as $p)
            {
                for($i=1;$i<13;$i++)
                {
                    $total_interest_paid['amount_m_'.$i] =$total_interest_paid['amount_m_'.$i] +$p['interest_paid']['amount_m_'.$i];
                }
                for($i=1;$i<5;$i++)
                {
                    $total_interest_paid['amount_y_'.$i] =$total_interest_paid['amount_y_'.$i] +$p['interest_paid']['amount_y_'.$i];
                }

            }
        }


        for($i=1;$i<13;$i++)
        {
            $profit['amount_m_'.$i] = 0;
            $profit['amount_m_'.$i] = $revenue_total['amount_m_'.$i]-$cost_total['amount_m_'.$i]-$labor_total['amount_m_'.$i]-$expense_total['amount_m_'.$i]-$asset_total['amount_m_'.$i]-$total_interest_paid['amount_m_'.$i];
        }
        for($i=1;$i<6;$i++)
        {
            $profit['amount_y_'.$i] = 0;
            $profit['amount_y_'.$i] = $revenue_total['amount_y_'.$i]-$cost_total['amount_y_'.$i]-$labor_total['amount_y_'.$i]-$expense_total['amount_y_'.$i]- $asset_total['amount_y_'.$i]-$total_interest_paid['amount_y_'.$i];
        }

        return $profit;
    }
    public static function getIncomeTax($id)
    {
        $profit= Tax::getProfitArray($id);
        $forecast=Forecast::where('id',$id)->with('company','taxes')->first();
        $coorporate_tax=$forecast->taxes[0]->coorporate_tax;
        $paid=array();
        $sum=0;
        $year_1_paid=0;
        for($i=1;$i<13;$i++)
        {
            $paid['amount_m_'.$i]=0;
            $profit['amount_m_'.$i] = round(($coorporate_tax/100)*$profit['amount_m_'.$i]);
            if($profit['amount_m_'.$i]<0)
                $profit['amount_m_'.$i]=0;
            if($forecast->taxes[0]->coorporate_payable_time=='quarterly')
            {

                if($i==4 || $i==7 || $i==10)
                {
                    $paid['amount_m_'.$i]=$sum;
                    $year_1_paid=$year_1_paid+$sum;
                    $sum=0;
                }
                $sum=$sum+$profit['amount_m_'.$i];
            }
            else
            {
                $year_1_paid=$year_1_paid+$profit['amount_m_'.$i];
            }

        }
        $paid['amount_y_1']=$year_1_paid;
        $profit['amount_y_1'] = round(($coorporate_tax/100)*$profit['amount_y_1']);
        if($profit['amount_y_1']<0)
            $profit['amount_y_1']=0;
        $previous_remaining=$profit['amount_y_1']-$paid['amount_y_1'];
        for($i=2;$i<6;$i++)
        {
            $profit['amount_y_'.$i] = round(($coorporate_tax/100)*$profit['amount_y_'.$i]);
            if($profit['amount_y_'.$i]<0)
                $profit['amount_y_'.$i]=0;
            if($forecast->taxes[0]->coorporate_payable_time=='quarterly')
            {
                $paid['amount_y_'.$i]=$previous_remaining+(($profit['amount_y_'.$i]/4)*3);
                $previous_remaining=($profit['amount_y_'.$i]/4);
            }
            else
            {
                $paid['amount_y_'.$i]=$profit['amount_y_'.$i];
            }
        }
        $income_tax=['accrued'=>$profit,'paid'=>$paid];
        return $income_tax;

    }

}
