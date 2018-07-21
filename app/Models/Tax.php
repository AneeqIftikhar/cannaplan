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
        $forecast=Forecast::where('id','=',$id)->with(['company','taxes' , 'taxes.revenueTaxes'])->first();

        $tax=new Tax();

        $coorporate_tax_abs=$forecast->taxes[0]->coorporate_tax/100;

        $taxes=['income_tax' , 'sales_tax'];

        $revenue=array();
        $accrued=array();
        $paid=array();
        for($i=1 ; $i<13 ; $i++)
        {
            $revenue['m_'.$i]=null;
            $accrued['m_'.$i]=null;
            $paid['m_'.$i]=null;
        }
        for($i=1 ; $i<6 ; $i++)
        {
            $revenue['y_'.$i]=null;
            $accrued['y_'.$i]=null;
            $paid['y_'.$i]=null;
        }

        $tax->calculateSalesTax($paid , $accrued , $taxes , $forecast);

        for($i=1 ; $i<13 ; $i++)
        {
            $revenue['m_'.$i]=null;
            $accrued['m_'.$i]=null;
            $paid['m_'.$i]=null;
        }
        for($i=1 ; $i<6 ; $i++)
        {
            $revenue['y_'.$i]=null;
            $accrued['y_'.$i]=null;
            $paid['y_'.$i]=null;
        }



        return $taxes;

    }

    public static function calculateSalesTax($paid , $accrued , $taxes , $forecast)
    {
        $rev=Revenue::getRevenueByForecastId($forecast->id);
        $first=false;
        $sum=0;
        $quarterly_sum=0;
        $total=0;
        $quarterly_total=0;

        $sales_tax_abs=$forecast->taxes[0]->sales_tax/100;

        //Fetching revenues
        foreach($forecast->taxes[0]->revenueTaxes as $rev_temp)
        {
            $quarterly_sum=0;
            $sum=0;
            for($i = 0 ; $i < count($rev->revenues) ; $i++)
            {
                if($rev->revenues[$i]->id== $rev_temp->revenue_id)
                {
                    for($j=1 ; $j<13 ; $j++)
                    {
                        if($rev->revenues[$i]->revenuable['amount_m_'.$j])
                        {
                            $first=true;

                            if($forecast->taxes[0]->sales_payable_time=='annually')
                            {
                                $paid['m_'.$j]=0;
                            }
                            else
                            {
                                if($j==4 || $j==7 || $j==10)
                                {
                                    $paid['m_'.$j]=$paid['m_'.$j]+$quarterly_sum;
                                    $quarterly_total=$quarterly_total+$quarterly_sum;
                                    $quarterly_sum=0;
                                }
                                else
                                {
                                    $paid['m_'.$j]=0;
                                }
                            }
                            $accrued['m_'.$j]=$accrued['m_'.$j]+round($rev->revenues[$i]->revenuable['amount_m_'.$j]*$sales_tax_abs);
                            $sum=$sum+round($rev->revenues[$i]->revenuable['amount_m_'.$j]*$sales_tax_abs);
                            $quarterly_sum=$quarterly_sum+round($rev->revenues[$i]->revenuable['amount_m_'.$j]*$sales_tax_abs);
                            //$revenue['m_'.$j]=$revenue['m_'.$j]+$rev->revenues[$i]->revenuable['amount_m_'.$j];
                        }
                        else if($rev->revenues[$i]->revenuable['amount_m_'.$j]==null && $first==true)
                        {
                            $accrued['m_'.$j]=$accrued['m_'.$j]+0;
                            if($forecast->taxes[0]->sales_payable_time=='annually')
                            {
                                $paid['m_'.$j]=0;
                            }
                            else
                            {
                                if($j==4 || $j==7 || $j==10)
                                {
                                    $paid['m_'.$j]=$paid['m_'.$j]+$quarterly_sum;
                                    $quarterly_total=$quarterly_total+$quarterly_sum;
                                    $quarterly_sum=0;
                                }
                                else
                                {
                                    $paid['m_'.$j]=0;
                                }
                            }
                        }
                        else{
                            $accrued['m_'.$j]=null;
                            $paid['m_'.$j]=null;
                            //$revenue['m_'.$j]=null;
                        }
                    }

                    if($sum!=0)
                    {
                        $accrued['y_1']=$accrued['y_1']+$sum;
                    }

                    $total=$total+$sum;
                    if($forecast->taxes[0]->sales_payable_time=='annually' && $sum!=0)
                    {
                        $paid['y_1']=0;
                    }
                    else
                    {
                        $paid['y_1']=$quarterly_total;
                    }
                    $sum=0;

                    $first=false;
                    for($j=2 ; $j<6 ; $j++)
                    {
                        if($rev->revenues[$i]->revenuable['amount_y_'.$j])
                        {
                            $accrued['y_'.$j]=$accrued['y_'.$j]+round($rev->revenues[$i]->revenuable['amount_y_'.$j]*$sales_tax_abs);
                            if($forecast->taxes[0]->sales_payable_time=='annually')
                            {
                                if($first==false)
                                {
                                    $paid['y_'.$j]=0;
                                    $first=true;
                                }
                                else{
                                    $paid['y_'.$j]=$accrued['y_'.($j-1)];
                                }

                            }
                            else
                            {
                                if($rev->revenues[$i]->revenuable_type=='revenue_only' && $rev->revenues[$i]->revenuable->type=='varying')
                                {
                                    if($first==false)
                                    {
                                        $paid['y_'.$j]=$accrued['y_'.($j-1)]-$paid['y_'.($j-1)];
                                        $first=true;
                                    }
                                    else{
                                        $paid['y_'.$j]=$accrued['y_'.$j];
                                    }
                                }
                            }
                            //$revenue['y_'.$j]=$revenue['y_'.$j]+$rev->revenues[$i]->revenuable['amount_y_'.$j];
                        }
                        else if($rev->revenues[$i]->revenuable['amount_y_'.$j]==null && $accrued['y_'.$j]!=null)
                        {
                            $accrued['y_'.$j]=$accrued['y_'.$j]+0;

                            if($forecast->taxes[0]->sales_payable_time=='annually')
                            {
                                $paid['y_'.$j]=$accrued['y_'.($j-1)];
                            }
                            else
                            {
                                if($rev->revenues[$i]->revenuable_type=='revenue_only' && $rev->revenues[$i]->revenuable->type=='varying')
                                {
                                    if($first==false)
                                    {
                                        $paid['y_'.$j]=$paid['y_'.$j]+$accrued['y_'.($j-1)]-$paid['y_'.($j-1)];
                                        $first=true;
                                    }
                                    else{
                                        $paid['y_'.$j]=$paid['y_'.$j]+$accrued['y_'.$j];
                                    }
                                }
                            }
                        }
                        else{
                            $accrued['y_'.$j]=null;
                            $paid['y_'.$j]=null;
                            //$revenue['y_'.$j]=null;
                        }
                    }

                }
            }

        }

        $taxes['sales_tax']=['accrued'=>$accrued , 'paid'=>$paid];
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
        for($i=1;$i<13;$i++)
        {
            $profit['amount_m_'.$i] = 0;
            $profit['amount_m_'.$i] = $revenue_total['amount_m_'.$i]-$cost_total['amount_m_'.$i]-$labor_total['amount_m_'.$i]-$expense_total['amount_m_'.$i]-$asset_total['amount_m_'.$i];
        }
        for($i=1;$i<6;$i++)
        {
            $profit['amount_y_'.$i] = 0;
            $profit['amount_y_'.$i] = $revenue_total['amount_y_'.$i]-$cost_total['amount_y_'.$i]-$labor_total['amount_y_'.$i]-$expense_total['amount_y_'.$i]- $asset_total['amount_y_'.$i];
        }

        return $profit;
    }
    public static function getIncomeTax($id)
    {
        $profit= Tax::getProfitArray($id);
        $forecast=Forecast::where('id',$id)->with('company','taxes')->first();
        $coorporate_tax=$forecast->taxes[0]->coorporate_tax;
        for($i=1;$i<13;$i++)
        {
            $profit['amount_m_'.$i] = round(($coorporate_tax/100)*$profit['amount_m_'.$i]);
        }
        for($i=1;$i<6;$i++)
        {
            $profit['amount_y_'.$i] = round(($coorporate_tax/100)*$profit['amount_y_'.$i]);
        }
        return $profit;

    }

}
