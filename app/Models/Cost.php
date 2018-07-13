<?php

namespace CannaPlan\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Auth;
use function Sodium\add;

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
    public static function addGeneral($amount , $cost_start_date)
    {
        $general=GeneralCost::create(['amount'=>$amount , 'cost_start_date'=>$cost_start_date]);
        return $general;
    }
    public static function addLabor($name, $number_of_employees, $labor_type, $pay, $start_date, $staff_role_type , $annual_raise_percent)
    {
        $labor=Labor::create(['name'=>$name, 'number_of_employees'=>$number_of_employees , 'labor_type'=>$labor_type ,'pay'=>$pay, 'start_date'=>$start_date , 'staff_role_type'=>$staff_role_type, 'annual_raise_percent'=>$annual_raise_percent]);
        return $labor;
    }

    //updating cost
    public static function updateCostOnRevenue($revenue_id , $amount , $charge)
    {
        $charge->update(['revenue_id'=>$revenue_id , 'amount'=>$amount]);
    }
    public static function updateGeneral($amount , $cost_start_date , $charge)
    {
        $charge->update(['amount'=>$amount , 'cost_start_date'=>$cost_start_date]);
    }
    public static function updateLabor($name, $number_of_employees, $labor_type, $pay, $start_date, $staff_role_type , $annual_raise_percent , $charge)
    {
        $charge->update(['name'=>$name, 'number_of_employees'=>$number_of_employees , 'labor_type'=>$labor_type ,'pay'=>$pay, 'start_date'=>$start_date , 'staff_role_type'=>$staff_role_type, 'annual_raise_percent'=>$annual_raise_percent]);
    }

    //show
    public static function getCostByForecastId($id)
    {
        $forecast=Forecast::where('id','=',$id)->with(['company','costs','costs.charge'])->first();

        $total_arr=array();
        $salaries_and_wages_arr=array();
        $employee_related_expenses_arr=array();

        $burden_rate_percent=$forecast->burden_rate/100;

        for ($j = 1; $j < 13; $j++) {
            $total_arr['amount_m_' . $j] = null;
            $salaries_and_wages_arr['amount_m_' . $j] = null;
            $employee_related_expenses_arr['amount_m_' . $j] = null;
        }
        for ($j = 1; $j < 6; $j++) {
            $total_arr['amount_y_' . $j] = null;
            $salaries_and_wages_arr['amount_y_' . $j] = null;
            $employee_related_expenses_arr['amount_y_' . $j] = null;
        }

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
                if($forecast->costs[$i]->charge->direct_cost_type=='general_cost')
                {
                    for ($j = 1; $j < 13; $j++) {
                        $forecast->costs[$i]->charge->direct_cost['amount_m_' . $j] = $forecast->costs[$i]->charge->direct_cost->amount;
                        $total_arr['amount_m_' . $j] = $total_arr['amount_m_' . $j]+$forecast->costs[$i]->charge->direct_cost['amount_m_' . $j];
                    }
                    for ($j = 1; $j < 6; $j++) {
                        $forecast->costs[$i]->charge->direct_cost['amount_y_' . $j] = $forecast->costs[$i]->charge->direct_cost->amount*12;
                        $total_arr['amount_y_' . $j] = $total_arr['amount_y_' . $j]+$forecast->costs[$i]->charge->direct_cost['amount_y_' . $j];
                    }
                }
                else if($forecast->costs[$i]->charge->direct_cost_type=='cost_on_revenue')
                {
                    $revenue=Revenue::find($forecast->costs[$i]->charge->direct_cost->revenue_id);

                    if($revenue->revenuable_type=='unit_sale')
                    {
                        for ($j = 1; $j < 13; $j++) {
                            $forecast->costs[$i]->charge->direct_cost['amount_m_' . $j] = $forecast->costs[$i]->charge->direct_cost->amount*$revenue->revenuable->unit_sold;
                            $total_arr['amount_m_' . $j] = $total_arr['amount_m_' . $j]+$forecast->costs[$i]->charge->direct_cost['amount_m_' . $j];
                        }
                        for ($j = 1; $j < 6; $j++) {
                            $forecast->costs[$i]->charge->direct_cost['amount_y_' . $j] = $forecast->costs[$i]->charge->direct_cost->amount*$revenue->revenuable->unit_sold*12;
                            $total_arr['amount_y_' . $j] = $total_arr['amount_y_' . $j]+$forecast->costs[$i]->charge->direct_cost['amount_y_' . $j];
                        }
                    }
                    else if($revenue->revenuable_type=='billable')
                    {
                        for ($j = 1; $j < 13; $j++) {
                            $forecast->costs[$i]->charge->direct_cost['amount_m_' . $j] = $forecast->costs[$i]->charge->direct_cost->amount*$revenue->revenuable->hour;
                            $total_arr['amount_m_' . $j] = $total_arr['amount_m_' . $j]+$forecast->costs[$i]->charge->direct_cost['amount_m_' . $j];
                        }
                        for ($j = 1; $j < 6; $j++) {
                            $forecast->costs[$i]->charge->direct_cost['amount_y_' . $j] = $forecast->costs[$i]->charge->direct_cost->amount*$revenue->revenuable->hour*12;
                            $total_arr['amount_y_' . $j] = $total_arr['amount_y_' . $j]+$forecast->costs[$i]->charge->direct_cost['amount_y_' . $j];
                        }
                    }
                    else
                    {
                        for ($j = 1; $j < 13; $j++) {
                            $forecast->costs[$i]->charge->direct_cost['amount_m_' . $j] = $forecast->costs[$i]->charge->direct_cost->amount;
                            $total_arr['amount_m_' . $j] = $total_arr['amount_m_' . $j]+$forecast->costs[$i]->charge->direct_cost['amount_m_' . $j];
                        }
                        for ($j = 1; $j < 6; $j++) {
                            $forecast->costs[$i]->charge->direct_cost['amount_y_' . $j] = $forecast->costs[$i]->charge->direct_cost->amount*12;
                            $total_arr['amount_y_' . $j] = $total_arr['amount_y_' . $j]+$forecast->costs[$i]->charge->direct_cost['amount_y_' . $j];
                        }
                    }
                }
            }
            else if($forecast->costs[$i]->charge_type=='labor')
            {
                if($forecast->costs[$i]->charge->labor_type=='direct')
                {
                    $annual_raise_percent=$forecast->costs[$i]->charge->annual_raise_percent/100;

                    for ($j = 1; $j < 13; $j++) {
                        $forecast->costs[$i]->charge['amount_m_' . $j] = $forecast->costs[$i]->charge->number_of_employees*$forecast->costs[$i]->charge->pay;
                        $salaries_and_wages_arr['amount_m_' . $j]=$salaries_and_wages_arr['amount_m_' . $j]+$forecast->costs[$i]->charge['amount_m_' . $j];
                        if($forecast->costs[$i]->charge->staff_role_type=='on_staff_employee')
                        {
                            $employee_related_expenses_arr['amount_m_' . $j]=$employee_related_expenses_arr['amount_m_' . $j]+round($forecast->costs[$i]->charge['amount_m_' . $j]*$burden_rate_percent);
                        }

                        $total_arr['amount_m_' . $j] = $total_arr['amount_m_' . $j]+$forecast->costs[$i]->charge['amount_m_' . $j]+round($forecast->costs[$i]->charge['amount_m_' . $j]*$burden_rate_percent);
                    }
                    $labor_previous_val=0;
                    for ($j = 1; $j < 6; $j++) {
                        if ($j > 1 && $forecast->costs[$i]->charge->annual_raise_percent>0) {
                            $labor_raise=round($labor_previous_val*$annual_raise_percent);
                            $forecast->costs[$i]->charge['amount_y_' . $j]=$labor_raise+$labor_previous_val;
                            $labor_previous_val=$forecast->costs[$i]->charge['amount_y_' . $j];

                            $salaries_and_wages_arr['amount_y_' . $j]=$salaries_and_wages_arr['amount_y_' . $j]+$forecast->costs[$i]->charge['amount_y_' . $j];
                        }
                        else{
                            $forecast->costs[$i]->charge['amount_y_' . $j] = 12*($forecast->costs[$i]->charge->number_of_employees*$forecast->costs[$i]->charge->pay);
                            $labor_previous_val=$forecast->costs[$i]->charge['amount_y_' . $j];

                            $salaries_and_wages_arr['amount_y_' . $j]=$salaries_and_wages_arr['amount_y_' . $j]+$forecast->costs[$i]->charge['amount_y_' . $j];
                        }
                    }
                }
            }
        }

        //employee related expenses are added here
        for($i=0 ; $i<count($forecast->costs) ; $i++)
        {
            for($j=1 ; $j<6 ; $j++)
            {
                if($forecast->costs[$i]->charge->staff_role_type=='on_staff_employee')
                {
                    $employee_related_expenses_arr['amount_y_' . $j]=$employee_related_expenses_arr['amount_y_' . $j]+round($forecast->costs[$i]->charge['amount_y_'.$j]*$burden_rate_percent);
                }

            }
        }
        //total array updated here
        for($j=1 ; $j<6 ; $j++)
        {
            $total_arr['amount_y_' . $j] = $total_arr['amount_y_'.$j]+$employee_related_expenses_arr['amount_y_' . $j]+$salaries_and_wages_arr['amount_y_'.$j];
        }

        $forecast['total']=$total_arr;
        $forecast['salaries_and_wages']=$salaries_and_wages_arr;
        $forecast['$employee_related_expenses']=$employee_related_expenses_arr;
        return $forecast;
    }
}
