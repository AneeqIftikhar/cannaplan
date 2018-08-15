<?php

namespace CannaPlan\Models;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
    public static function calculatePreviousAssetByForecast($id)
    {
        $forecast=Forecast::where('id',$id)->with('company','initialBalanceSettings')->first();
        $longterm=array();
        $current=array();
        for($i=1;$i<13;$i++)
        {
            $longterm['amount_m_'.$i]=null;
            $current['amount_m_'.$i]=null;
        }
        for($i=1;$i<6;$i++)
        {
            $longterm['amount_y_'.$i]=null;
            $current['amount_y_'.$i]=null;
        }
        if($forecast['initialBalanceSettings']['long_term_assets']!==null)
        {
            $value=$forecast['initialBalanceSettings']['long_term_assets']-$forecast['initialBalanceSettings']['accumulated_depreciation'];
            $year=$forecast['initialBalanceSettings']['depreciation_period'];
            if($year==0)
            {
                $dep_yearly=0;
                $dep_monthly=0;
            }
            else
            {
                $dep_yearly=$value/($year);
                $dep_monthly=$value/(12*$year);
            }



            if($value>=0)
            {
                for($i=1;$i<13;$i++)
                {
                    $value=$value-$dep_monthly;
                    if($value>=0)
                    {
                        $longterm['amount_m_'.$i]=round($value);
                    }
                    else
                    {
                        $longterm['amount_m_'.$i]=0;
                    }

                }
                if($value>=0)
                {
                    $longterm['amount_y_1']= $longterm['amount_m_12'];
                }
                else
                {
                    $longterm['amount_y_'.$i]=0;
                }
                for($i=2;$i<6;$i++)
                {
                    $value=$value-$dep_yearly;
                    if($value>=0)
                    {
                        $longterm['amount_y_'.$i]=round($value);
                    }
                    else
                    {
                        $longterm['amount_y_'.$i]=0;
                    }
                }
            }
            else if($value<0)
            {
                for($i=1;$i<13;$i++)
                {
                    $value=$value-$dep_monthly;
                    if($value<=0)
                    {
                        $longterm['amount_m_'.$i]=round($value);
                    }
                    else
                    {
                        $longterm['amount_m_'.$i]=0;
                    }

                }
                if($value<=0)
                {
                    $longterm['amount_y_1']= $longterm['amount_m_12'];
                }
                else
                {
                    $longterm['amount_y_'.$i]=0;
                }
                for($i=2;$i<6;$i++)
                {
                    $value=$value-$dep_yearly;
                    if($value<=0)
                    {
                        $longterm['amount_y_'.$i]=round($value);
                    }
                    else
                    {
                        $longterm['amount_y_'.$i]=0;
                    }
                }
            }
        }

        if($forecast['initialBalanceSettings']['other_current_assets']!==null){
            $value=$forecast['initialBalanceSettings']['other_current_assets'];
            $amortization_period=$forecast['initialBalanceSettings']['amortization_period'];

            for($i=1;$i<13;$i++)
            {
                if($amortization_period!=0)
                {
                    $amortization=$value/$amortization_period;
                    $current['amount_m_'.$i]=round($value-$amortization);
                    $value=$value-$amortization;
                    $amortization_period--;
                }
                else{
                    $current['amount_m_'.$i]=$value;
                }
            }
            for($i=1;$i<6;$i++)
            {
                $current['amount_y_'.$i]=0;
            }
        }
        return ['long_term'=>$longterm,'current'=>$current];

    }

    public static function calculatePreviousDepreciationAndAmortization($id)
    {
        $forecast=Forecast::where('id',$id)->with('company','initialBalanceSettings')->first();
        $longterm=array();
        $current=array();
        for($i=1;$i<13;$i++)
        {
            $longterm['amount_m_'.$i]=null;
            $current['amount_m_'.$i]=null;
        }
        for($i=1;$i<6;$i++)
        {
            $longterm['amount_y_'.$i]=null;
            $current['amount_y_'.$i]=null;
        }
        if($forecast['initialBalanceSettings']['long_term_assets']!==null)
        {
            $value=$forecast['initialBalanceSettings']['long_term_assets']-$forecast['initialBalanceSettings']['accumulated_depreciation'];
            $year=$forecast['initialBalanceSettings']['depreciation_period'];
            if($year==0)
            {
                $dep_yearly=0;
                $dep_monthly=0;
                for($i=1;$i<13;$i++)
                {
                    $longterm['amount_m_'.$i]=0;
                }
                for($i=1;$i<$year+1;$i++)
                {
                    $longterm['amount_y_'.$i]=0;
                }
            }
            else
            {
                $dep_yearly=$value/($year);
                $dep_monthly=$value/(12*$year);
                if($value>=0)
                {
                    for($i=1;$i<13;$i++)
                    {

                        if($value>=0)
                        {
                            $longterm['amount_m_'.$i]=round($dep_monthly);
                        }
                        else
                        {
                            $longterm['amount_m_'.$i]=0;
                        }
                        $value=$value-$dep_monthly;
                    }
                    if($value>=0)
                    {
                        $longterm['amount_y_1']=round($dep_yearly);
                    }
                    else
                    {
                        $longterm['amount_y_1']=0;
                    }
                    for($i=2;$i<6;$i++)
                    {
                        if($value>=0)
                        {
                            $longterm['amount_y_'.$i]=round($dep_yearly);
                        }
                        else
                        {
                            $longterm['amount_y_'.$i]=0;
                        }
                        $value=$value-$dep_yearly;
                    }
                }
                else if($value<0)
                {
                    for($i=1;$i<13;$i++)
                    {

                        if($value<=0)
                        {
                            $longterm['amount_m_'.$i]=round($dep_monthly);
                        }
                        else
                        {
                            $longterm['amount_m_'.$i]=0;
                        }
                        $value=$value-$dep_monthly;

                    }
                    if($value<=0)
                    {
                        $longterm['amount_y_1']=round($dep_yearly);
                    }
                    else
                    {
                        $longterm['amount_y_1']=0;
                    }
                    for($i=2;$i<6;$i++)
                    {

                        if($value<=0)
                        {
                            $longterm['amount_y_'.$i]=round($dep_yearly);
                        }
                        else
                        {
                            $longterm['amount_y_'.$i]=0;
                        }
                        $value=$value-$dep_yearly;
                    }
                }
            }




        }

        if($forecast['initialBalanceSettings']['other_current_assets']!==null){
            $value=$forecast['initialBalanceSettings']['other_current_assets'];
            $amortization_period=$forecast['initialBalanceSettings']['amortization_period'];
            if($amortization_period!==0)
            {
                $dep_monthly=$value/$amortization_period;
                $year_1_dep=0;
                for($i=1;$i<13;$i++)
                {
                    if($value>=0)
                    {
                        $current['amount_m_'.$i]=round($dep_monthly);
                        $year_1_dep=$year_1_dep+$current['amount_m_'.$i];
                    }
                    else
                    {
                        $current['amount_m_'.$i]=0;
                    }
                    $value=$value-$dep_monthly;

                }
                $current['amount_y_1']=$forecast['initialBalanceSettings']['other_current_assets'];
                for($i=2;$i<6;$i++)
                {
                    $current['amount_y_'.$i]=0;
                }
            }
        }
        return ['long_term'=>$longterm,'current'=>$current];
    }
}
