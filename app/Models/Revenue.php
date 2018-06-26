<?php

namespace CannaPlan\Models;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
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
        $total=$input['amount_m_1']+$input['amount_m_2']+$input['amount_m_3']+$input['amount_m_4']+$input['amount_m_5']+$input['amount_m_6']
            +$input['amount_m_7']+$input['amount_m_8']+$input['amount_m_9']+$input['amount_m_10']+$input['amount_m_11']+$input['amount_m_12'];
        $array=array();
        $array['type']='varying';
        $array['revenue_start_date']=$revenue_start_date;
        $array['amount_m_1']=$input['amount_m_1'];
        $array['amount_m_2']=$input['amount_m_2'];
        $array['amount_m_3']=$input['amount_m_3'];
        $array['amount_m_4']=$input['amount_m_4'];
        $array['amount_m_5']=$input['amount_m_5'];
        $array['amount_m_6']=$input['amount_m_6'];
        $array['amount_m_7']=$input['amount_m_7'];
        $array['amount_m_8']=$input['amount_m_8'];
        $array['amount_m_9']=$input['amount_m_9'];
        $array['amount_m_10']=$input['amount_m_10'];
        $array[ 'amount_m_11']=$input['amount_m_11'];
        $array['amount_m_12']=$input['amount_m_12'];
        $array[ 'amount_y_1']=$total;
        $array['amount_y_2']=$total;
        $array['amount_y_3']=$total;
        $array['amount_y_4']=$total;
        $array['amount_y_5']=$total;
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
        $total=$input['amount_m_1']+$input['amount_m_2']+$input['amount_m_3']+$input['amount_m_4']+$input['amount_m_5']+$input['amount_m_6']
            +$input['amount_m_7']+$input['amount_m_8']+$input['amount_m_9']+$input['amount_m_10']+$input['amount_m_11']+$input['amount_m_12'];
        $array=array();
        $array['type']='varying';
        $array['revenue_start_date']=$revenue_start_date;
        $array['amount_m_1']=$input['amount_m_1'];
        $array['amount_m_2']=$input['amount_m_2'];
        $array['amount_m_3']=$input['amount_m_3'];
        $array['amount_m_4']=$input['amount_m_4'];
        $array['amount_m_5']=$input['amount_m_5'];
        $array['amount_m_6']=$input['amount_m_6'];
        $array['amount_m_7']=$input['amount_m_7'];
        $array['amount_m_8']=$input['amount_m_8'];
        $array['amount_m_9']=$input['amount_m_9'];
        $array['amount_m_10']=$input['amount_m_10'];
        $array[ 'amount_m_11']=$input['amount_m_11'];
        $array['amount_m_12']=$input['amount_m_12'];
        $array[ 'amount_y_1']=$total;
        $array['amount_y_2']=$total;
        $array['amount_y_3']=$total;
        $array['amount_y_4']=$total;
        $array['amount_y_5']=$total;
        $revenuable->update($array);
    }
    public static function updateRevenueOnlyConstant($amount,$amount_duration,$revenue_start_date,$revenuable)
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

        $revenuable->update($array);
    }


    public static function getRevenueByForecastId($id)
    {
        $forecast=Forecast::find($id);
        $total_arr=array();
        for ($j = 1; $j < 13; $j++) {
            $total_arr['amount_m_' . $j] = 0;
        }
        for ($j = 1; $j < 6; $j++) {
            $total_arr['amount_y_' . $j] = 0;
        }
        $forecast=$forecast->with(['company','revenues','revenues.revenuable'])->first();
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
                    for ($j = 1; $j < 13; $j++) {
                        $forecast->revenues[$i]['revenuable']['amount_m_' . $j] = $multiplyer * $multiplicand;
                    }
                    $total = $multiplyer * $multiplicand * 12;
                    $forecast->revenues[$i]['revenuable']['amount_y_1'] = $total;
                    $forecast->revenues[$i]['revenuable']['amount_y_2'] = $total;
                    $forecast->revenues[$i]['revenuable']['amount_y_3'] = $total;
                    $forecast->revenues[$i]['revenuable']['amount_y_4'] = $total;
                    $forecast->revenues[$i]['revenuable']['amount_y_5'] = $total;
                }
                for ($j = 1; $j < 13; $j++) {
                    $total_arr['amount_m_' . $j] = $total_arr['amount_m_' . $j]+ $forecast->revenues[$i]['revenuable']['amount_m_' . $j];
                }
                for ($j = 1; $j < 6; $j++) {
                    $total_arr['amount_y_' . $j] = $total_arr['amount_y_' . $j]+ $forecast->revenues[$i]['revenuable']['amount_y_' . $j];
                }

                $forecast['total'] = $total_arr;
            }

        }
        return $forecast;
    }
}
