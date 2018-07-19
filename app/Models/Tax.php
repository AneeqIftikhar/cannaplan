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

        $coorporate_tax_abs=$forecast->taxes[0]->coorporate_tax/100;
        $sales_tax_abs=$forecast->taxes[0]->sales_tax/100;


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

        $rev=Revenue::getRevenueByForecastId($id);

        //Fetching revenues
        foreach($forecast->taxes[0]->revenueTaxes as $rev_temp)
        {
            for($i = 0 ; $i < count($rev->revenues) ; $i++)
            {
                if($rev->revenues[$i]->id== $rev_temp->revenue_id)
                {
                    for($j=1 ; $j<13 ; $j++)
                    {
                        if($rev->revenues[$i]->revenuable['amount_m_'.$j])
                        {
                            $revenue['m_'.$j]=$revenue['m_'.$j]+$rev->revenues[$i]->revenuable['amount_m_'.$j];
                        }
                        else{
                            $revenue['m_'.$j]=null;
                        }
                    }
                    for($j=1 ; $j<6 ; $j++)
                    {
                        if($rev->revenues[$i]->revenuable['amount_y_'.$j])
                        {
                            $revenue['y_'.$j]=$revenue['y_'.$j]+$rev->revenues[$i]->revenuable['amount_y_'.$j];
                        }
                        else{
                            $revenue['y_'.$j]=null;
                        }
                    }
                }
            }

        }


        $taxes=['income_tax' , 'sales_tax'];
        $sum=0;
        $total=0;

        if($forecast->taxes[0]->sales_payable_time=='annually')
        {
            for($j=1 ; $j<13 ; $j++)
            {

                if($revenue['m_'.$j]==null)
                {
                    $accrued['m_'.$j]=null;
                    $paid['m_'.$j]=null;
                }
                else{
                    $accrued['m_'.$j]=$revenue['m_'.$j]*$sales_tax_abs;
                    $paid['m_'.$j]=0;
                }
            }
            $check_first=false;
            for($j=1 ; $j<6 ; $j++)
            {
                if($revenue['y_'.$j]!=null && $check_first==false)
                {
                    $accrued['y_'.$j]=$revenue['y_'.$j]*$sales_tax_abs;
                    $check_first=true;
                    $paid['y_'.$j]=0;
                }
                else if($revenue['y_'.$j]==null)
                {
                    $accrued['y_'.$j]=null;
                    $paid['y_'.$j]=null;
                }
                else
                {
                    $accrued['y_'.$j]=$revenue['y_'.$j]*$sales_tax_abs;
                    $paid['y_'.$j]=$revenue['y_'.($j-1)];
                }
            }
        }
        else{
            $check_first=true;
            $temp=0;
            for($j=1 ; $j<13 ; $j++)
            {

                if($revenue['m_'.$j]==null)
                {
                    $accrued['m_'.$j]=null;
                    $paid['m_'.$j]=null;
                }
                else{

                    $accrued['m_'.$j]=$revenue['m_'.$j]*$sales_tax_abs;
                    $temp=$accrued['m_'.$j];
                    $sum=$sum+$accrued['m_'.$j];
                    if($j==4 || $j==7 || $j==10)
                    {
                        if($accrued['m_'.($j-1)]==null)
                        {
                            $check_first=false;
                            $paid['m_'.$j]=0;
                            $sum=0;
                        }
                        else {
                            if($check_first==true)
                            {
                                $paid['m_'.$j]=$sum-$accrued['m_'.$j];
                                $total=$total+$sum;
                                $sum=0;
                                $check_first=false;
                            }
                            else{
                                $paid['m_'.$j]=$sum;
                                $total=$total+$sum;
                                $sum=0;
                            }

                        }
                    }
                    else{
                        $paid['m_'.$j]=0;
                    }
                }
            }
            $check_first=false;
            for($j=1 ; $j<6 ; $j++)
            {
                if($revenue['y_'.$j]==null)
                {
                    $accrued['y_'.$j]=null;
                    $paid['y_'.$j]=null;
                }
                else{
                    if($revenue['y_'.$j]!=null && $check_first==false)
                    {
                        $paid['y_'.$j]=$total;
                        $accrued['y_'.$j]=$revenue['y_'.$j]*$sales_tax_abs;
                        $check_first=true;
                    }
                    else
                    {
                        $paid['y_'.$j]=$temp*12;
                    }
                }
            }
        }


        $taxes['income_tax']=['accrued'=>$accrued , 'paid'=>$paid];
        $taxes['sales_tax']=['accrued'=>$accrued , 'paid'=>$paid];

        return $taxes;

    }

}
