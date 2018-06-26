<?php

namespace CannaPlan\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Auth;

Relation::morphMap([
    'direct'=>'CannaPlan\Models\Direct',
    'labor'=>'CannaPlan\Models\Labor'
]);
/**
 * @property int $id
 * @property int $forecast_id
 * @property int $charge_id
 * @property string $charge_type
 * @property string $deleted_at
 * @property string $remember_token
 * @property string $created_at
 * @property string $updated_at
 * @property Forecast $forecast
 */
class Cost extends Model
{
    use SoftDeletes;
    protected $dates=['deleted_at'];
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'cost';

    public static function boot()
    {
        parent::boot();

        // create a event to happen on saving
        static::creating(function ($table) {
            $table->created_by = Auth::user()->id;
        });

        static::deleting(function($table) {

            $table->charge->delete();
            $table->charge->direct_cost->delete();
        });
    }

    /**
     * @var array
     */
    protected $fillable = ['charge_id', 'charge_type'];
    protected $guarded = ['id','forecast_id','created_by'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function forecast()
    {
        return $this->belongsTo('CannaPlan\Models\Forecast');
    }
    public function charge()
    {
        return $this->morphTo();
    }

    //insertion of costs
    public static function addCostOnRevenue($revenue_id , $amount)
    {
        $cost_on_revenue=CostOnRevenue::create(['revenue_id'=>$revenue_id , 'amount'=>$amount]);
        return $cost_on_revenue;
    }
    public static function addLabor($name, $number_of_employees, $labor_type, $pay, $start_date, $staff_role_type , $annual_raise_percent)
    {
        $labor=Labor::create(['name'=>$name, 'number_of_employees'=>$number_of_employees , 'labor_type'=>$labor_type ,'pay'=>$pay, 'start_date'=>$start_date , 'staff_role_type'=>$staff_role_type, 'annual_raise_percent'=>$annual_raise_percent]);
        return $labor;
    }
    public static function addGeneral($amount , $cost_start_date)
    {
        $general=GeneralCost::create(['amount'=>$amount , 'cost_start_date'=>$cost_start_date]);
        return $general;
    }

    public static function getCostByForecastId($id)
    {
        $forecast=Forecast::find($id);
        $total_arr=array();
        for ($j = 1; $j < 13; $j++) {
            $total_arr['amount_m_' . $j] = 0;
        }
        for ($j = 1; $j < 6; $j++) {
            $total_arr['amount_y_' . $j] = 0;
        }

        $forecast=$forecast->with(['company','costs','costs.charge'])->first();

        for ($i=0;$i<count($forecast->costs);$i++)
        {
            if($forecast->costs[$i]->charge_type=='direct')
            {
                 $forecast->costs[$i]->charge->direct_cost;
            }
        }

        for ($i=0;$i<count($forecast->costs);$i++)
        {
            if($forecast->costs[$i]->charge_type=='direct')
            {
                $forecast->costs[$i]->charge->direct_cost;

                    if($forecast->costs[$i]->charge->direct_cost_type=='general_cost')
                    {
                        $forecast->costs[$i]->charge['direct_cost']['amount'];
                    }
                    else if($forecast->costs[$i]->charge->direct_cost_type=='cost_on_revenue')
                    {
                        $forecast->costs[$i]->charge['direct_cost']['amount'];
                    }


            }
            else if($forecast->costs[$i]->charge_type=='labor')
            {
                $forecast->costs[$i]['charge']['number_of_employees']*$forecast->costs[$i]['charge']['pay'];
            }
        }
    }
}
