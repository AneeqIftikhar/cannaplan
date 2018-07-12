<?php

namespace CannaPlan\Models;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use DateTime;
Relation::morphMap([
    'unit_sale'=>'CannaPlan\Models\UnitSale',
    'billable'=>'CannaPlan\Models\Billable',
    'revenue_only'=>'CannaPlan\Models\RevenueOnly'
]);
/**
 * @property int $id
 * @property int $company_id
 * @property string $name
 * @property int $earning_id
 * @property string $earning_type
 * @property string $deleted_at
 * @property string $remember_token
 * @property string $created_at
 * @property string $updated_at
 * @property Company $company
 * @property CostOnRevenue[] $costOnRevenues
 * @property RevenueTax[] $revenueTaxes
 */
class Revenue extends Model
{
    use SoftDeletes;
    protected $dates=['deleted_at'];
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    public static function boot() {
        parent::boot();

        // create a event to happen on saving
        static::creating(function($table)  {
            $table->created_by = Auth::user()->id;
        });
        static::deleting(function($table) {

            $table->revenuable->delete();
        });

    }
    protected $table = 'revenue';

    /**
     * @var array
     */
    protected $fillable = [ 'name', 'revenuable_id', 'revenuable_type'];
    protected $guarded = ['id','forecast_id','created_by'];
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
    public function costOnRevenues()
    {
        return $this->hasMany('CannaPlan\Models\CostOnRevenue');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function revenueTaxes()
    {
        return $this->hasMany('CannaPlan\Models\RevenueTax');
    }
    /* many to many relation*/
    public function taxes()
    {
        return $this->belongsToMany('CannaPlan\Models\Tax', 'revenue_tax',
            'revenue_id', 'tax_id');
    }

    public function revenuable()
    {
        return $this->morphTo();
    }

/*Adding Revenuables*/
    public static function addBillable($hour,$revenue_start_date,$hourly_rate)
    {
        $billable=Billable::create(['hour'=>$hour,'revenue_start_date'=>$revenue_start_date,'hourly_rate'=>$hourly_rate]);
        return $billable;
    }
    public static function addUnitSale($unit_sold,$revenue_start_date,$unit_price)
    {
        $unit_sale=UnitSale::create(['unit_sold'=>$unit_sold,'revenue_start_date'=>$revenue_start_date,'unit_price'=>$unit_price]);
        return $unit_sale;
    }
    public static function addRevenueOnlyVarying($revenue_start_date,$input)
    {
        $array=array();
        $array['type']='varying';
        $array['revenue_start_date']=$revenue_start_date;
        for($i=1;$i<13;$i++)
        {
            if(isset($input['amount_m_'.$i]))
            {
                $array['amount_m_'.$i]=$input['amount_m_'.$i];
            }

        }
        for($i=1;$i<6;$i++)
        {
            if(isset($input['amount_y_'.$i]))
            {
                $array['amount_y_'.$i]=$input['amount_y_'.$i];
            }

        }
        $revenue_only=RevenueOnly::create($array);
        return $revenue_only;
    }
    public static function addRevenueOnlyConstant($amount,$amount_duration,$revenue_start_date)
    {
        $array=array();
        if($amount_duration=="year")
        {
            $total=$amount;
            for($i=1;$i<12;$i++)
            {
                $array['amount_m_'.$i]=floor(($amount/(13-$i)));
                $amount=$amount-floor(($amount/(13-$i)));
            }
            $array['amount_m_12']=$amount;
        }
        else if($amount_duration=="month")
        {
            $total=$amount*12;
            for($i=1;$i<13;$i++)
            {
                $array['amount_m_'.$i]=$amount;
            }
        }
        $array['amount_y_1']=$total;
        $array['amount_y_2']=$total;
        $array['amount_y_3']=$total;
        $array['amount_y_4']=$total;
        $array['amount_y_5']=$total;
        $array['type']='constant';
        $array['amount_duration']=$amount_duration;
        $array['amount']=$amount;
        $array['revenue_start_date']=$revenue_start_date;

        $revenue_only=RevenueOnly::create($array);
        return $revenue_only;
    }
/*Updating Revenuables*/
    public static function updateBillable($hour,$revenue_start_date,$hourly_rate,$revenuable)
    {
        $revenuable->update(['hour'=>$hour,'revenue_start_date'=>$revenue_start_date,'hourly_rate'=>$hourly_rate]);
    }
    public static function updateUnitSale($unit_sold,$revenue_start_date,$unit_price,$revenuable)
    {
        $revenuable->update(['unit_sold'=>$unit_sold,'revenue_start_date'=>$revenue_start_date,'unit_price'=>$unit_price]);
    }
    public static function updateRevenueOnlyVarying($revenue_start_date,$input,$revenuable)
    {
        $array=array();
        $array['type']='varying';
        $array['revenue_start_date']=$revenue_start_date;
        for($i=1;$i<13;$i++)
        {
            if(isset($input['amount_m_'.$i]))
            {
                $array['amount_m_'.$i]=$input['amount_m_'.$i];
            }
            else
            {
                $array['amount_m_'.$i]=null;
            }


        }
        for($i=1;$i<6;$i++)
        {
            if(isset($input['amount_y_'.$i]))
            {
                $array['amount_y_'.$i]=$input['amount_y_'.$i];
            }
            else
            {
                $array['amount_y_'.$i]=null;
            }

        }
        $revenuable->update($array);
    }
    public static function updateRevenueOnlyConstant($amount,$amount_duration,$revenue_start_date,$revenuable)
    {
        $array=array();
        $start_of_forecast=date($revenuable->revenues[0]->forecast->company->start_of_forecast);
        $start_of_forecast = new DateTime($start_of_forecast);
        $date=date($revenuable->revenue_start_date);
        $d2 = new DateTime($date);
        $diff_month=$start_of_forecast->diff($d2)->m;
        $diff_year=$start_of_forecast->diff($d2)->y;
        $total_year_1=0;
        if($amount_duration=="year")
        {
            $total=$amount;
            $index=1;
            for($i=1;$i<13;$i++)
            {
                if($diff_year==0 && $diff_month<$i) {
                    if ($i == 12 && $index==$i)
                    {
                        $array['amount_m_12']=$amount;
                        $total_year_1=$total_year_1+$array['amount_m_' . $i];
                    }
                    else
                    {

                        $array['amount_m_' . $i] = floor(($amount / (13 - $index)));
                        $total_year_1=$total_year_1+$array['amount_m_' . $i];
                        $amount = $amount - floor(($amount / (13 - $index)));
                        $index=$index+1;
                    }

                }
                else
                {
                    $array['amount_m_' . $i] = null;
                }
            }

        }
        else if($amount_duration=="month")
        {
            $total=$amount*12;
            for($i=1;$i<13;$i++)
            {
                if($diff_year==0 && $diff_month<$i) {
                    $array['amount_m_'.$i]=$amount;
                    $total_year_1=$total_year_1+$array['amount_m_' . $i];
                }
                else
                {
                    $array['amount_m_' . $i] = null;
                }

            }
        }
        for($i=1;$i<6;$i++)
        {
            if($diff_year<$i)
            {
                if($i==1)
                {
                    $array['amount_y_'.$i]=$total_year_1;
                }
                else
                {
                    $array['amount_y_'.$i]=$total;
                }

            }
            else
            {
                $array['amount_y_' . $i] = null;
            }
        }
        $array['type']='constant';
        $array['amount_duration']=$amount_duration;
        $array['amount']=$amount;
        $array['revenue_start_date']=$revenue_start_date;

        $revenuable->update($array);
    }


    public static function getRevenueByForecastId($id)
    {
        $forecast=Forecast::where('id',$id)->with(['company','revenues','revenues.revenuable'])->first();
        $start_of_forecast=date($forecast->company->start_of_forecast);
        $start_of_forecast = new DateTime($start_of_forecast);
        $total_arr=array();
        for ($j = 1; $j < 13; $j++) {
            $total_arr['amount_m_' . $j] = null;
        }
        for ($j = 1; $j < 6; $j++) {
            $total_arr['amount_y_' . $j] = null;
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
                    $date=date($forecast->revenues[$i]['revenuable']['revenue_start_date']);
                    $d2 = new DateTime($date);
                    $diff_month=$start_of_forecast->diff($d2)->m;
                    $diff_year=$start_of_forecast->diff($d2)->y;
                    $year_1_total=0;
                    for ($j = 1; $j < 13; $j++) {
                        if($diff_year==0 && $diff_month<$j)
                        {
                            $forecast->revenues[$i]['revenuable']['amount_m_' . $j] = $multiplyer * $multiplicand;
                            $year_1_total=$year_1_total+$forecast->revenues[$i]['revenuable']['amount_m_' . $j];
                        }
                        else
                        {
                            $forecast->revenues[$i]['revenuable']['amount_m_' . $j] = null;
                        }
                    }
                    $total = $multiplyer * $multiplicand * 12;
                    for ($j = 1; $j < 6; $j++)
                    {
                        if($diff_year<$j)
                        {
                            if ($j == 1)
                            {
                                $forecast->revenues[$i]['revenuable']['amount_y_'.$j] = $year_1_total;
                            }
                            else
                            {
                                $forecast->revenues[$i]['revenuable']['amount_y_'.$j] = $total;

                            }
                        }
                        else
                        {
                            $forecast->revenues[$i]['revenuable']['amount_y_'.$j] = null;
                        }
                    }


                }
                for ($j = 1; $j < 13; $j++) {
                    if($forecast->revenues[$i]['revenuable']['amount_m_' . $j])
                    {
                        $total_arr['amount_m_' . $j] = $total_arr['amount_m_' . $j]+ $forecast->revenues[$i]['revenuable']['amount_m_' . $j];
                    }
                }
                for ($j = 1; $j < 6; $j++) {
                    if($forecast->revenues[$i]['revenuable']['amount_y_' . $j])
                    {
                        $total_arr['amount_y_' . $j] = $total_arr['amount_y_' . $j]+ $forecast->revenues[$i]['revenuable']['amount_y_' . $j];
                    }
                }


            }

        }
        $forecast['total'] = $total_arr;
        return $forecast;
    }
}
