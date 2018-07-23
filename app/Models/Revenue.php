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
            if($table->revenuable)
            {
                $table->revenuable->delete();
            }

            foreach($table->revenueTaxes as $revenue_tax)
            {
                $revenue_tax->delete();
            }
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
    public static function addRevenueOnlyVarying($input)
    {
        $array=array();
        $array['type']='varying';
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
    public static function addRevenueOnlyConstant($amount,$amount_duration,$revenue_start_date,$start_forecast)
    {
        $array=array();
        $array['amount']=$amount;
        $start_of_forecast=date($start_forecast);
        $start_of_forecast = new DateTime($start_of_forecast);
        $date=date($revenue_start_date);
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
                if($diff_year==0 && $diff_month<$i)
                {
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
    public static function updateRevenueOnlyVarying($input,$revenuable)
    {
        $array=array();
        $array['type']='varying';
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
        $array['amount']=$amount;
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

        $array['revenue_start_date']=$revenue_start_date;

        $revenuable->update($array);
    }


    public static function getRevenueByForecastId($id)
    {
        $forecast=Forecast::where('id',$id)->with(['company','revenues'])->first();
        for ($i=0;$i<count($forecast->revenues);$i++)
        {
            if(isset($forecast->revenues[$i]->revenuable_type))
            {
                $forecast->revenues[$i]->revenuable;
                //rows_hidden status for frontend hard coded
                $forecast->revenues[$i]['revenuable']['rows_hidden']=false;
            }
        }
        $start_of_forecast=date($forecast->company->start_of_forecast);
        $start_of_forecast = new DateTime($start_of_forecast);
        $total_arr=array();
        $unit_sale=array();
        $unit_price=array();
        $billable_hour=array();
        $hourly_rate=array();
        for ($j = 1; $j < 13; $j++) {
            $total_arr['amount_m_' . $j] = null;
            $unit_sale['amount_m_' . $j] = null;
            $unit_price['amount_m_' . $j] = null;
            $billable_hour['amount_m_' . $j] = null;
            $hourly_rate['amount_m_' . $j] = null;
        }
        for ($j = 1; $j < 6; $j++) {
            $total_arr['amount_y_' . $j] = null;
            $unit_sale['amount_y_' . $j] = null;
            $unit_price['amount_y_' . $j] = null;
            $billable_hour['amount_y_' . $j] = null;
            $hourly_rate['amount_y_' . $j] = null;
        }

        //ARRAY OF UNIT SALES AND UNIT PRICE IS NOT MADE
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

                    $sum_sale=0;
                    $year_1_billable=0;
                    for ($j = 1; $j < 13; $j++) {
                        if($diff_year==0 && $diff_month<$j)
                        {
                            $forecast->revenues[$i]['revenuable']['amount_m_' . $j] = $multiplyer * $multiplicand;
                            $year_1_total=$year_1_total+$forecast->revenues[$i]['revenuable']['amount_m_' . $j];

                            if($forecast->revenues[$i]->revenuable_type == 'unit_sale')
                            {
                                $unit_sale['amount_m_' . $j] = $multiplyer;
                                $sum_sale=$sum_sale+$multiplyer;
                                $unit_price['amount_m_' . $j] = $multiplicand;
                            }
                            else
                            {
                                $billable_hour['amount_m_' . $j] = $multiplyer;
                                $year_1_billable=$year_1_billable+$multiplyer;
                                $hourly_rate['amount_m_' . $j] = $multiplicand;
                            }


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

                                if($forecast->revenues[$i]->revenuable_type == 'unit_sale')
                                {
                                    $unit_sale['amount_y_'.$j]=$sum_sale;
                                    $unit_price['amount_y_' . $j] = $multiplicand;
                                }
                                else
                                {
                                    $billable_hour['amount_y_'.$j]=$year_1_billable;
                                    $hourly_rate['amount_y_' . $j] = $multiplicand;
                                }
                            }
                            else
                            {
                                $forecast->revenues[$i]['revenuable']['amount_y_'.$j] = $total;
                                if($forecast->revenues[$i]->revenuable_type == 'unit_sale')
                                {
                                    $unit_sale['amount_y_'.$j]=$multiplyer*12;
                                    $unit_price['amount_y_' . $j] = $multiplicand;
                                }
                                else
                                {
                                    $billable_hour['amount_y_'.$j]=$multiplyer*12;
                                    $hourly_rate['amount_y_' . $j] = $multiplicand;
                                }
                            }
                        }
                        else
                        {
                            $forecast->revenues[$i]['revenuable']['amount_y_'.$j] = null;
                        }
                    }
                    if($forecast->revenues[$i]->revenuable_type == 'unit_sale')
                    {
                        $forecast->revenues[$i]->revenuable['single_unit_price'] = $forecast->revenues[$i]->revenuable['unit_price'];
                        $forecast->revenues[$i]->revenuable['unit_sale'] = $unit_sale;
                        $forecast->revenues[$i]->revenuable['unit_price'] = $unit_price;
                    }
                    else
                    {
                        $forecast->revenues[$i]->revenuable['single_billable_hour'] = $forecast->revenues[$i]->revenuable['hour '];
                        $forecast->revenues[$i]->revenuable['single_hourly_rate'] = $forecast->revenues[$i]->revenuable['hourly_rate'];
                        $forecast->revenues[$i]->revenuable['billable_hour'] = $billable_hour;
                        $forecast->revenues[$i]->revenuable['hourly_rate'] = $hourly_rate;

                    }


                }

                for ($j = 1; $j < 13; $j++) {
                    $unit_sale['amount_m_' . $j] = null;
                    $unit_price['amount_m_' . $j] = null;
                    $billable_hour['amount_m_' . $j] = null;
                    $hourly_rate['amount_m_' . $j] = null;
                    if($forecast->revenues[$i]['revenuable']['amount_m_' . $j])
                    {
                        $total_arr['amount_m_' . $j] = $total_arr['amount_m_' . $j]+ $forecast->revenues[$i]['revenuable']['amount_m_' . $j];
                    }
                }
                for ($j = 1; $j < 6; $j++) {
                    $unit_sale['amount_y_' . $j] = null;
                    $unit_price['amount_y_' . $j] = null;
                    $billable_hour['amount_y_' . $j] = null;
                    $hourly_rate['amount_y_' . $j] = null;
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
