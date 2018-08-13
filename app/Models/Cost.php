<?php

namespace CannaPlan\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Auth;
use function Sodium\add;
use DateTime;
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

            //$table->charge->delete();
            if($table->charge_type=='direct')
            {
                $table->charge->direct_cost->delete();
            }
            else{
                $table->charge->delete();
            }

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
        $start_of_forecast=date($forecast->company->start_of_forecast);
        $start_of_forecast = new DateTime($start_of_forecast);

        $total_arr=array();
        $salaries_and_wages_arr=array();
        $employee_related_expenses_arr=array();
        $direct_labor_arr=array();
        $salaries_and_wages_arr['employees']=array();
        $on_staff_check=false;
        $labor_direct_check=false;

        $burden_rate_percent=$forecast->burden_rate/100;

        for ($j = 1; $j < 13; $j++) {
            $total_arr['amount_m_' . $j] = null;
            $salaries_and_wages_arr['amount_m_' . $j] = null;
            $employee_related_expenses_arr['amount_m_' . $j] = null;
            $direct_labor_arr['amount_m_' . $j] = null;
        }
        for ($j = 1; $j < 6; $j++) {
            $total_arr['amount_y_' . $j] = null;
            $salaries_and_wages_arr['amount_y_' . $j] = null;
            $employee_related_expenses_arr['amount_y_' . $j] = null;
            $direct_labor_arr['amount_y_' . $j] = null;
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
                    $date=date($forecast->costs[$i]->charge->direct_cost['cost_start_date']);
                    $d2 = new DateTime($date);
                    $diff_month=$start_of_forecast->diff($d2)->m;
                    $diff_year=$start_of_forecast->diff($d2)->y;
                    $year_1_cost=0;
                    for ($j = 1; $j < 13; $j++) {
                        if ($diff_year == 0 && $diff_month < $j) {
                            $forecast->costs[$i]->charge->direct_cost['amount_m_' . $j] = $forecast->costs[$i]->charge->direct_cost->amount;
                            $total_arr['amount_m_' . $j] = $total_arr['amount_m_' . $j] + $forecast->costs[$i]->charge->direct_cost['amount_m_' . $j];
                            $year_1_cost=$year_1_cost+$forecast->costs[$i]->charge->direct_cost['amount_m_' . $j];
                        }
                        else
                        {
                            $forecast->costs[$i]->charge->direct_cost['amount_m_' . $j] = null;
                        }
                    }
                    for ($j = 1; $j < 6; $j++) {
                        if($diff_year<$j)
                        {
                            if($j==1)
                            {
                                $forecast->costs[$i]->charge->direct_cost['amount_y_' . $j] = $year_1_cost;
                                $total_arr['amount_y_' . $j] = $total_arr['amount_y_' . $j]+$year_1_cost;
                            }
                            else
                            {
                                $forecast->costs[$i]->charge->direct_cost['amount_y_' . $j] = $forecast->costs[$i]->charge->direct_cost->amount*12;
                                $total_arr['amount_y_' . $j] = $total_arr['amount_y_' . $j]+$forecast->costs[$i]->charge->direct_cost['amount_y_' . $j];
                            }


                        }
                        else
                        {
                            $forecast->costs[$i]->charge->direct_cost['amount_y_' . $j] = null;
                        }
                    }
                }
                else if($forecast->costs[$i]->charge->direct_cost_type=='cost_on_revenue')
                {
                    $revenue=Revenue::find($forecast->costs[$i]->charge->direct_cost->revenue_id);

                    $date=date($revenue['revenuable']['revenue_start_date']);
                    $d2 = new DateTime($date);
                    $diff_month=$start_of_forecast->diff($d2)->m;
                    $diff_year=$start_of_forecast->diff($d2)->y;
                    $year_1_cost=0;

                    if($revenue->revenuable_type=='unit_sale')
                    {
                        for ($j = 1; $j < 13; $j++) {
                            if ($diff_year == 0 && $diff_month < $j)
                            {
                                $forecast->costs[$i]->charge->direct_cost['amount_m_' . $j] = $forecast->costs[$i]->charge->direct_cost->amount*$revenue->revenuable->unit_sold;
                                $total_arr['amount_m_' . $j] = $total_arr['amount_m_' . $j]+$forecast->costs[$i]->charge->direct_cost['amount_m_' . $j];
                                $year_1_cost=$year_1_cost+$forecast->costs[$i]->charge->direct_cost['amount_m_' . $j];
                            }
                            else
                            {
                                $forecast->costs[$i]->charge->direct_cost['amount_m_' . $j] =null;
                            }

                        }
                        for ($j = 1; $j < 6; $j++) {
                            if($diff_year<$j)
                            {
                                if ($j == 1)
                                {
                                    $forecast->costs[$i]->charge->direct_cost['amount_y_' . $j] = $year_1_cost;
                                    $total_arr['amount_y_' . $j] = $total_arr['amount_y_' . $j]+$year_1_cost;
                                }
                                else
                                {
                                    $forecast->costs[$i]->charge->direct_cost['amount_y_' . $j] = $forecast->costs[$i]->charge->direct_cost->amount*$revenue->revenuable->unit_sold*12;
                                    $total_arr['amount_y_' . $j] = $total_arr['amount_y_' . $j]+$forecast->costs[$i]->charge->direct_cost['amount_y_' . $j];

                                }
                            }
                            else
                            {
                                $forecast->costs[$i]->charge->direct_cost['amount_y_' . $j] = null;
                            }

                        }
                    }
                    else if($revenue->revenuable_type=='billable')
                    {
                        for ($j = 1; $j < 13; $j++) {
                            if ($diff_year == 0 && $diff_month < $j)
                            {
                                $forecast->costs[$i]->charge->direct_cost['amount_m_' . $j] = $forecast->costs[$i]->charge->direct_cost->amount*$revenue->revenuable->hour;
                                $total_arr['amount_m_' . $j] = $total_arr['amount_m_' . $j]+$forecast->costs[$i]->charge->direct_cost['amount_m_' . $j];
                                $year_1_cost=$year_1_cost+$forecast->costs[$i]->charge->direct_cost['amount_m_' . $j];
                            }
                            else
                            {
                                $forecast->costs[$i]->charge->direct_cost['amount_m_' . $j] =null;
                            }

                        }
                        for ($j = 1; $j < 6; $j++) {
                            if($diff_year<$j)
                            {
                                if ($j == 1)
                                {
                                    $forecast->costs[$i]->charge->direct_cost['amount_y_' . $j] = $year_1_cost;
                                    $total_arr['amount_y_' . $j] = $total_arr['amount_y_' . $j]+$year_1_cost;
                                }
                                else
                                {
                                    $forecast->costs[$i]->charge->direct_cost['amount_y_' . $j] = $forecast->costs[$i]->charge->direct_cost->amount*$revenue->revenuable->hour*12;
                                    $total_arr['amount_y_' . $j] = $total_arr['amount_y_' . $j]+$forecast->costs[$i]->charge->direct_cost['amount_y_' . $j];

                                }
                            }
                            else
                            {
                                $forecast->costs[$i]->charge->direct_cost['amount_y_' . $j] = null;
                            }

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
                    $labor_direct_check=true;

                    $date=date($forecast->costs[$i]->charge->start_date);
                    $d2 = new DateTime($date);
                    $diff_month=$start_of_forecast->diff($d2)->m;
                    $diff_year=$start_of_forecast->diff($d2)->y;
                    $year_1_total=0;

                    for ($j = 1; $j < 13; $j++) {

                        if($diff_year==0 && $diff_month<$j)
                        {

                            if($forecast->costs[$i]->charge->staff_role_type=='on_staff_employee')
                            {
                                $on_staff_check=true;
                                $forecast->costs[$i]->charge['amount_m_' . $j] = $forecast->costs[$i]->charge->number_of_employees*$forecast->costs[$i]->charge->pay;
                                $employee_related_expenses_arr['amount_m_' . $j]=$employee_related_expenses_arr['amount_m_' . $j]+round($forecast->costs[$i]->charge['amount_m_' . $j]*$burden_rate_percent);
                            }
                            else
                            {
                                $forecast->costs[$i]->charge['amount_m_' . $j] = $forecast->costs[$i]->charge->number_of_employees*$forecast->costs[$i]->charge->pay;
                            }
                            $salaries_and_wages_arr['amount_m_' . $j]=$salaries_and_wages_arr['amount_m_' . $j]+$forecast->costs[$i]->charge['amount_m_' . $j];
                            $year_1_total=$year_1_total+$forecast->costs[$i]->charge['amount_m_' . $j];
                        }
                        else{
                            $forecast->costs[$i]->charge['amount_m_' . $j]=null;
                        }

                    }
                    $labor_previous_val=0;
                    for ($j = 1; $j < 6; $j++) {
                        if($diff_year<$j) {

                            if ($j == 1) {

                                $forecast->costs[$i]->charge['amount_y_' . $j]=$year_1_total;

                                if($forecast->costs[$i]->charge->staff_role_type=='on_staff_employee')
                                {
                                    $employee_related_expenses_arr['amount_y_' . $j]=$employee_related_expenses_arr['amount_y_' . $j]+round($forecast->costs[$i]->charge['amount_y_' . $j]*$burden_rate_percent);
                                }

                                $labor_previous_val=$forecast->costs[$i]->charge->number_of_employees*$forecast->costs[$i]->charge->pay*12;

                                $salaries_and_wages_arr['amount_y_' . $j]=$salaries_and_wages_arr['amount_y_' . $j]+$forecast->costs[$i]->charge['amount_y_' . $j];

                            }
                            else if ($forecast->costs[$i]->charge->annual_raise_percent>0) {
                                $annual_raise_percent=1+($forecast->costs[$i]->charge->annual_raise_percent/100);

                                $forecast->costs[$i]->charge['amount_y_' . $j]=round($labor_previous_val*$annual_raise_percent);

                                if($forecast->costs[$i]->charge->staff_role_type=='on_staff_employee')
                                {
                                    $employee_related_expenses_arr['amount_y_' . $j]=$employee_related_expenses_arr['amount_y_' . $j]+round($forecast->costs[$i]->charge['amount_y_' . $j]*$burden_rate_percent);
                                }

                                $labor_previous_val=$forecast->costs[$i]->charge['amount_y_' . $j];

                                $salaries_and_wages_arr['amount_y_' . $j]=$salaries_and_wages_arr['amount_y_' . $j]+$forecast->costs[$i]->charge['amount_y_' . $j];
                            }
                            else
                            {

                                $forecast->costs[$i]->charge['amount_y_' . $j]=$forecast->costs[$i]->charge->number_of_employees*$forecast->costs[$i]->charge->pay*12;

                                if($forecast->costs[$i]->charge->staff_role_type=='on_staff_employee')
                                {
                                    $employee_related_expenses_arr['amount_y_' . $j]=$employee_related_expenses_arr['amount_y_' . $j]+round($forecast->costs[$i]->charge['amount_y_' . $j]*$burden_rate_percent);
                                }

                                $salaries_and_wages_arr['amount_y_' . $j]=$salaries_and_wages_arr['amount_y_' . $j]+$forecast->costs[$i]->charge['amount_y_' . $j];
                            }
                        }
                        else{
                            $forecast->costs[$i]->charge['amount_y_' . $j]=null;
                            $annual_raise_percent=1+($forecast->costs[$i]->charge->annual_raise_percent/100);
                            if($forecast->costs[$i]->charge->staff_role_type=='contract')
                            {
                                if($forecast->costs[$i]->charge->annual_raise_percent>0)
                                {
                                    $labor_previous_val=($forecast->costs[$i]->charge->pay*12)/$annual_raise_percent;
                                }
                                else{
                                    $labor_previous_val=$forecast->costs[$i]->charge->pay*12;
                                }

                            }
                            else{
                                if($forecast->costs[$i]->charge->annual_raise_percent>0)
                                {
                                    $labor_previous_val=($forecast->costs[$i]->charge->number_of_employees*$forecast->costs[$i]->charge->pay*12)/$annual_raise_percent;
                                }
                                else{
                                    $labor_previous_val=($forecast->costs[$i]->charge->number_of_employees*$forecast->costs[$i]->charge->pay*12);
                                }

                            }

                        }

                    }
                    $salaries_and_wages_arr['rows_hidden']=false;
                    array_push($salaries_and_wages_arr['employees'],$forecast->costs[$i]->charge);
                }
            }
        }
//        //employee related expenses are added here
//        for($i=0 ; $i<count($forecast->costs) ; $i++)
//        {
//            for($j=1 ; $j<6 ; $j++)
//            {
//                if($forecast->costs[$i]->charge->staff_role_type=='on_staff_employee')
//                {
//                    if($j==1)
//                    {
//                        return $employee_related_expenses_arr;
//                        for ($k=1 ; $k<13 ; $k++)
//                        {
//                            $employee_related_expenses_arr['amount_y_' . $j]=$employee_related_expenses_arr['amount_y_' . $j]+$employee_related_expenses_arr['amount_m_' . $k];
//                        }
//                    }
//                    else{
//                        $employee_related_expenses_arr['amount_y_' . $j]=$employee_related_expenses_arr['amount_y_' . $j]+round($forecast->costs[$i]->charge['amount_y_'.$j]*$burden_rate_percent);
//                    }
//
//                }
//
//            }
//        }


        //return round((10284+5/2)/5)*5;

        //total array updated here
        for($j=1 ; $j<13 ; $j++)
        {
            $direct_labor_arr['amount_m_' . $j] = $direct_labor_arr['amount_m_' . $j]+$employee_related_expenses_arr['amount_m_' . $j]+$salaries_and_wages_arr['amount_m_'.$j];
        }
        for($j=1 ; $j<6 ; $j++)
        {
            $direct_labor_arr['amount_y_' . $j] = $direct_labor_arr['amount_y_'.$j]+$employee_related_expenses_arr['amount_y_' . $j]+$salaries_and_wages_arr['amount_y_'.$j];
        }

        if($labor_direct_check)
        {
            if($on_staff_check)
            {
                $direct_labor_arr['saleries_and_wages']=$salaries_and_wages_arr;
                $direct_labor_arr['employee_related_expanses']=$employee_related_expenses_arr;
            }
            else
            {
                $direct_labor_arr['saleries_and_wages']=$salaries_and_wages_arr;
            }
            $direct_labor_arr['rows_hidden']=false;
            $forecast['direct_labor']=$direct_labor_arr;
        }


        
        //total array updated here
        for($j=1 ; $j<13 ; $j++)
        {
            if($employee_related_expenses_arr['amount_m_' . $j] || $salaries_and_wages_arr['amount_m_'.$j])
            {
                $total_arr['amount_m_' . $j] = $total_arr['amount_m_' . $j]+$employee_related_expenses_arr['amount_m_' . $j]+$salaries_and_wages_arr['amount_m_'.$j];

            }
        }
        for($j=1 ; $j<6 ; $j++) {
            if ($employee_related_expenses_arr['amount_y_' . $j] || $salaries_and_wages_arr['amount_y_' . $j]) {
                $total_arr['amount_y_' . $j] = $total_arr['amount_y_' . $j] + $employee_related_expenses_arr['amount_y_' . $j] + $salaries_and_wages_arr['amount_y_' . $j];

            }
        }

        $forecast['total']=$total_arr;
        return $forecast;
    }

    //personnel API
    public  static function getPersonnelByForecastId($id)
    {
        $forecast=Forecast::where('id','=',$id)->with(['company','costs','costs.charge'])->first();
        $start_of_forecast=date($forecast->company->start_of_forecast);
        $start_of_forecast = new DateTime($start_of_forecast);

        $total_arr=array();
        $direct_salaries_and_wages_arr=array();
        $direct_employee_related_expenses_arr=array();
        $direct_labor_arr=array();

        $regular_salaries_and_wages_arr=array();
        $regular_employee_related_expenses_arr=array();
        $regular_labor_arr=array();

        $direct_salaries_and_wages_arr['employees']=array();
        $regular_salaries_and_wages_arr['employees']=array();

        $head_count=array();
        $average_salary=array();
        $net_profit_per_employee=array();

        $direct_labor_check=false;
        $regular_labor_check=false;

        $burden_rate_percent=$forecast->burden_rate/100;

        for ($j = 1; $j < 13; $j++) {
            $total_arr['amount_m_' . $j] = null;

            $direct_salaries_and_wages_arr['amount_m_' . $j] = null;
            $direct_employee_related_expenses_arr['amount_m_' . $j] = null;
            $direct_labor_arr['amount_m_' . $j] = null;

            $regular_salaries_and_wages_arr['amount_m_' . $j] = null;
            $regular_employee_related_expenses_arr['amount_m_' . $j] = null;
            $regular_labor_arr['amount_m_' . $j] = null;

            $head_count['amount_m_' . $j] = null;
            $average_salary['amount_m_' . $j] = null;
            $net_profit_per_employee['amount_m_' . $j] = null;
        }
        for ($j = 1; $j < 6; $j++) {
            $total_arr['amount_y_' . $j] = null;

            $direct_salaries_and_wages_arr['amount_y_' . $j] = null;
            $direct_employee_related_expenses_arr['amount_y_' . $j] = null;
            $direct_labor_arr['amount_y_' . $j] = null;

            $regular_salaries_and_wages_arr['amount_y_' . $j] = null;
            $regular_employee_related_expenses_arr['amount_y_' . $j] = null;
            $regular_labor_arr['amount_y_' . $j] = null;

            $head_count['amount_y_' . $j] = null;
            $average_salary['amount_y_' . $j] = null;
            $net_profit_per_employee['amount_y_' . $j] = null;
        }

        for ($i=0;$i<count($forecast->costs);$i++)
        {
            if($forecast->costs[$i]->charge_type=='labor')
            {
                if($forecast->costs[$i]->charge->labor_type=='direct')
                {
                    $direct_labor_check=true;
                    $date=date($forecast->costs[$i]->charge->start_date);
                    $d2 = new DateTime($date);
                    $diff_month=$start_of_forecast->diff($d2)->m;
                    $diff_year=$start_of_forecast->diff($d2)->y;
                    $year_1_total=0;

                    for ($j = 1; $j < 13; $j++) {

                        if($diff_year==0 && $diff_month<$j)
                        {

                            if($forecast->costs[$i]->charge->staff_role_type=='on_staff_employee')
                            {
                                $forecast->costs[$i]->charge['amount_m_' . $j] = $forecast->costs[$i]->charge->number_of_employees*$forecast->costs[$i]->charge->pay;
                                $direct_employee_related_expenses_arr['amount_m_' . $j]=$direct_employee_related_expenses_arr['amount_m_' . $j]+round($forecast->costs[$i]->charge['amount_m_' . $j]*$burden_rate_percent);
                            }
                            else
                            {
                                $forecast->costs[$i]->charge['amount_m_' . $j] = $forecast->costs[$i]->charge->number_of_employees*$forecast->costs[$i]->charge->pay;
                            }
                            $head_count['amount_m_' . $j]=$head_count['amount_m_' . $j]+$forecast->costs[$i]->charge->number_of_employees;
                            $direct_salaries_and_wages_arr['amount_m_' . $j]=$direct_salaries_and_wages_arr['amount_m_' . $j]+$forecast->costs[$i]->charge['amount_m_' . $j];
                            $year_1_total=$year_1_total+$forecast->costs[$i]->charge['amount_m_' . $j];
                        }
                        else{
                            $forecast->costs[$i]->charge['amount_m_' . $j]=null;
                        }

                    }
                    $labor_previous_val=0;
                    for ($j = 1; $j < 6; $j++) {
                        if($diff_year<$j) {
                            $head_count['amount_y_' . $j]=$head_count['amount_y_' . $j]+$forecast->costs[$i]->charge->number_of_employees;

                            if ($j == 1) {
                                $forecast->costs[$i]->charge['amount_y_' . $j]=$year_1_total;

                                if($forecast->costs[$i]->charge->staff_role_type=='on_staff_employee')
                                {
                                    $direct_employee_related_expenses_arr['amount_y_' . $j]=$direct_employee_related_expenses_arr['amount_y_' . $j]+round($forecast->costs[$i]->charge['amount_y_' . $j]*$burden_rate_percent);
                                }
                                $labor_previous_val=$forecast->costs[$i]->charge->number_of_employees*$forecast->costs[$i]->charge->pay*12;


                                $direct_salaries_and_wages_arr['amount_y_' . $j]=$direct_salaries_and_wages_arr['amount_y_' . $j]+$forecast->costs[$i]->charge['amount_y_' . $j];
                            }
                            else if ($forecast->costs[$i]->charge->annual_raise_percent>0) {
                                $annual_raise_percent=1+($forecast->costs[$i]->charge->annual_raise_percent/100);

                                $forecast->costs[$i]->charge['amount_y_' . $j]=round($labor_previous_val*$annual_raise_percent);

                                if($forecast->costs[$i]->charge->staff_role_type=='on_staff_employee')
                                {
                                    $direct_employee_related_expenses_arr['amount_y_' . $j]=$direct_employee_related_expenses_arr['amount_y_' . $j]+round($forecast->costs[$i]->charge['amount_y_' . $j]*$burden_rate_percent);
                                }

                                $labor_previous_val=$forecast->costs[$i]->charge['amount_y_' . $j];

                                $direct_salaries_and_wages_arr['amount_y_' . $j]=$direct_salaries_and_wages_arr['amount_y_' . $j]+$forecast->costs[$i]->charge['amount_y_' . $j];
                            }
                            else
                            {

                                $forecast->costs[$i]->charge['amount_y_' . $j]=$forecast->costs[$i]->charge->number_of_employees*$forecast->costs[$i]->charge->pay*12;
                                if($forecast->costs[$i]->charge->staff_role_type=='on_staff_employee')
                                {
                                    $direct_employee_related_expenses_arr['amount_y_' . $j]=$direct_employee_related_expenses_arr['amount_y_' . $j]+round($forecast->costs[$i]->charge['amount_y_' . $j]*$burden_rate_percent);
                                }

                                $direct_salaries_and_wages_arr['amount_y_' . $j]=$direct_salaries_and_wages_arr['amount_y_' . $j]+$forecast->costs[$i]->charge['amount_y_' . $j];
                            }
                        }
                        else{
                            $forecast->costs[$i]->charge['amount_y_' . $j]=null;

                            $annual_raise_percent=1+($forecast->costs[$i]->charge->annual_raise_percent/100);

                            if($forecast->costs[$i]->charge->staff_role_type=='contract')
                            {
                                if($forecast->costs[$i]->charge->annual_raise_percent>0)
                                {
                                    $labor_previous_val=($forecast->costs[$i]->charge->number_of_employees*$forecast->costs[$i]->charge->pay*12)/$annual_raise_percent;
                                }
                                else{
                                    $labor_previous_val=$forecast->costs[$i]->charge->number_of_employees*$forecast->costs[$i]->charge->pay*12;
                                }

                            }
                            else{
                                if($forecast->costs[$i]->charge->annual_raise_percent>0)
                                {
                                    $labor_previous_val=($forecast->costs[$i]->charge->number_of_employees*$forecast->costs[$i]->charge->pay*12)/$annual_raise_percent;
                                }
                                else{
                                    $labor_previous_val=($forecast->costs[$i]->charge->number_of_employees*$forecast->costs[$i]->charge->pay*12);
                                }

                            }

                        }

                    }
                    array_push($direct_salaries_and_wages_arr['employees'],$forecast->costs[$i]->charge);
                }
                else if($forecast->costs[$i]->charge->labor_type=='regular')
                {
                    $regular_labor_check=true;
                    $date=date($forecast->costs[$i]->charge->start_date);
                    $d2 = new DateTime($date);
                    $diff_month=$start_of_forecast->diff($d2)->m;
                    $diff_year=$start_of_forecast->diff($d2)->y;
                    $year_1_total=0;

                    for ($j = 1; $j < 13; $j++) {

                        if($diff_year==0 && $diff_month<$j)
                        {
                            $head_count['amount_m_' . $j]=$head_count['amount_m_' . $j]+$forecast->costs[$i]->charge->number_of_employees;
                            if($forecast->costs[$i]->charge->staff_role_type=='on_staff_employee')
                            {
                                $forecast->costs[$i]->charge['amount_m_' . $j] = $forecast->costs[$i]->charge->number_of_employees*$forecast->costs[$i]->charge->pay;
                                $regular_employee_related_expenses_arr['amount_m_' . $j]=$regular_employee_related_expenses_arr['amount_m_' . $j]+round($forecast->costs[$i]->charge['amount_m_' . $j]*$burden_rate_percent);
                            }
                            else
                            {
                                $forecast->costs[$i]->charge['amount_m_' . $j] = $forecast->costs[$i]->charge->number_of_employees*$forecast->costs[$i]->charge->pay;
                            }
                            $regular_salaries_and_wages_arr['amount_m_' . $j]=$regular_salaries_and_wages_arr['amount_m_' . $j]+$forecast->costs[$i]->charge['amount_m_' . $j];
                            $year_1_total=$year_1_total+$forecast->costs[$i]->charge['amount_m_' . $j];
                        }
                        else{
                            $forecast->costs[$i]->charge['amount_m_' . $j]=null;
                        }

                    }
                    $labor_previous_val=0;
                    for ($j = 1; $j < 6; $j++) {
                        if($diff_year<$j) {
                            $head_count['amount_y_' . $j]=$head_count['amount_y_' . $j]+$forecast->costs[$i]->charge->number_of_employees;
                            if ($j == 1) {
                                $forecast->costs[$i]->charge['amount_y_' . $j]=$year_1_total;

                                if($forecast->costs[$i]->charge->staff_role_type=='on_staff_employee')
                                {
                                    $regular_employee_related_expenses_arr['amount_y_' . $j]=$regular_employee_related_expenses_arr['amount_y_' . $j]+round($forecast->costs[$i]->charge['amount_y_' . $j]*$burden_rate_percent);
                                }

                                $labor_previous_val=$forecast->costs[$i]->charge->number_of_employees*$forecast->costs[$i]->charge->pay*12;

                                $regular_salaries_and_wages_arr['amount_y_' . $j]=$regular_salaries_and_wages_arr['amount_y_' . $j]+$forecast->costs[$i]->charge['amount_y_' . $j];
                            }
                            else if ($forecast->costs[$i]->charge->annual_raise_percent>0) {
                                $annual_raise_percent=1+($forecast->costs[$i]->charge->annual_raise_percent/100);

                                $forecast->costs[$i]->charge['amount_y_' . $j]=round($labor_previous_val*$annual_raise_percent);

                                if($forecast->costs[$i]->charge->staff_role_type=='on_staff_employee')
                                {
                                    $regular_employee_related_expenses_arr['amount_y_' . $j]=$regular_employee_related_expenses_arr['amount_y_' . $j]+round($forecast->costs[$i]->charge['amount_y_' . $j]*$burden_rate_percent);
                                }

                                $labor_previous_val=$forecast->costs[$i]->charge['amount_y_' . $j];

                                $regular_salaries_and_wages_arr['amount_y_' . $j]=$regular_salaries_and_wages_arr['amount_y_' . $j]+$forecast->costs[$i]->charge['amount_y_' . $j];
                            }
                            else
                            {
                                $forecast->costs[$i]->charge['amount_y_' . $j]=$forecast->costs[$i]->charge->number_of_employees*$forecast->costs[$i]->charge->pay*12;

                                if($forecast->costs[$i]->charge->staff_role_type=='on_staff_employee')
                                {
                                    $regular_employee_related_expenses_arr['amount_y_' . $j]=$regular_employee_related_expenses_arr['amount_y_' . $j]+round($forecast->costs[$i]->charge['amount_y_' . $j]*$burden_rate_percent);
                                }

                                $regular_salaries_and_wages_arr['amount_y_' . $j]=$regular_salaries_and_wages_arr['amount_y_' . $j]+$forecast->costs[$i]->charge['amount_y_' . $j];
                            }
                        }
                        else{
                            $forecast->costs[$i]->charge['amount_y_' . $j]=null;
                            $annual_raise_percent=1+($forecast->costs[$i]->charge->annual_raise_percent/100);
                            if($forecast->costs[$i]->charge->staff_role_type=='contract')
                            {
                                if($forecast->costs[$i]->charge->annual_raise_percent>0)
                                {
                                    $labor_previous_val=($forecast->costs[$i]->charge->number_of_employees*$forecast->costs[$i]->charge->pay*12)/$annual_raise_percent;
                                }
                                else{
                                    $labor_previous_val=$forecast->costs[$i]->charge->number_of_employees*$forecast->costs[$i]->charge->pay*12;
                                }

                            }
                            else{
                                if($forecast->costs[$i]->charge->annual_raise_percent>0)
                                {
                                    $labor_previous_val=($forecast->costs[$i]->charge->number_of_employees*$forecast->costs[$i]->charge->pay*12)/$annual_raise_percent;
                                }
                                else{
                                    $labor_previous_val=($forecast->costs[$i]->charge->number_of_employees*$forecast->costs[$i]->charge->pay*12);
                                }

                            }

                        }

                    }
                    array_push($regular_salaries_and_wages_arr['employees'],$forecast->costs[$i]->charge);

                }
            }
        }
        //employee related expenses are added here
        for($i=0 ; $i<count($forecast->costs) ; $i++)
        {
            for($j=1 ; $j<6 ; $j++)
            {
                if($forecast->costs[$i]->charge->staff_role_type=='on_staff_employee' && $forecast->costs[$i]->charge_type=='regular')
                {
                    $regular_employee_related_expenses_arr['amount_y_' . $j]=$regular_employee_related_expenses_arr['amount_y_' . $j]+round($forecast->costs[$i]->charge['amount_y_'.$j]*$burden_rate_percent);
                }
                else if($forecast->costs[$i]->charge->staff_role_type=='on_staff_employee' && $forecast->costs[$i]->charge_type=='direct')
                {
                    $direct_employee_related_expenses_arr['amount_y_' . $j]=$direct_employee_related_expenses_arr['amount_y_' . $j]+round($forecast->costs[$i]->charge['amount_y_'.$j]*$burden_rate_percent);
                }

            }
        }

        //return round((10284+5/2)/5)*5;

        for($j=1 ; $j<13 ; $j++)
        {
            $direct_labor_arr['amount_m_' . $j] =$direct_employee_related_expenses_arr['amount_m_' . $j]+$direct_salaries_and_wages_arr['amount_m_'.$j];
        }
        for($j=1 ; $j<6 ; $j++)
        {
            $direct_labor_arr['amount_y_' . $j] =$direct_employee_related_expenses_arr['amount_y_' . $j]+$direct_salaries_and_wages_arr['amount_y_'.$j];
        }

        $direct_labor_arr['saleries_and_wages']=$direct_salaries_and_wages_arr;
        $direct_labor_arr['employee_related_expanses']=$direct_employee_related_expenses_arr;


        for($j=1 ; $j<13 ; $j++)
        {
            $regular_labor_arr['amount_m_' . $j] = $regular_employee_related_expenses_arr['amount_m_' . $j]+$regular_salaries_and_wages_arr['amount_m_'.$j];
        }
        for($j=1 ; $j<6 ; $j++)
        {
            $regular_labor_arr['amount_y_' . $j] = $regular_employee_related_expenses_arr['amount_y_' . $j]+$regular_salaries_and_wages_arr['amount_y_'.$j];
        }

        $regular_labor_arr['saleries_and_wages']=$regular_salaries_and_wages_arr;
        $regular_labor_arr['employee_related_expanses']=$regular_employee_related_expenses_arr;

        if($direct_labor_check && $regular_labor_check)
        {
            $forecast['direct_labor']=$direct_labor_arr;
            $forecast['other_labor']=$regular_labor_arr;
        }
        else if($regular_labor_check && !$direct_labor_check){
            $forecast['personnel_expenses']=$regular_labor_arr;
        }
        else{
            $forecast['direct_labor']=$direct_labor_arr;
        }

        for($j=1 ; $j<13 ; $j++)
        {
            if($direct_salaries_and_wages_arr['amount_m_' . $j] || $regular_salaries_and_wages_arr['amount_m_'.$j])
            {
                $average_salary['amount_m_'.$j]=round(($direct_salaries_and_wages_arr['amount_m_' . $j]+$regular_salaries_and_wages_arr['amount_m_'.$j])/$head_count['amount_m_'.$j]);
            }
        }
        for($j=1 ; $j<6 ; $j++) {
            if ($direct_salaries_and_wages_arr['amount_y_' . $j] || $regular_salaries_and_wages_arr['amount_y_' . $j]) {
                $average_salary['amount_y_'.$j]=round(($direct_salaries_and_wages_arr['amount_y_' . $j]+$regular_salaries_and_wages_arr['amount_y_'.$j])/$head_count['amount_y_'.$j]);
            }
        }

        //total array updated here
        for($j=1 ; $j<13 ; $j++)
        {
            if($direct_labor_arr['amount_m_' . $j] || $regular_labor_arr['amount_m_'.$j])
            {
                $total_arr['amount_m_' . $j] = $total_arr['amount_m_' . $j]+$direct_labor_arr['amount_m_' . $j]+$regular_labor_arr['amount_m_'.$j];
            }
        }
        for($j=1 ; $j<6 ; $j++) {
            if ($direct_labor_arr['amount_y_' . $j] || $regular_labor_arr['amount_y_' . $j]) {
                $total_arr['amount_y_' . $j] = $total_arr['amount_y_' . $j] + $direct_labor_arr['amount_y_' . $j] + $regular_labor_arr['amount_y_' . $j];
            }
        }
        $head_count['average_salary']=$average_salary;
        $forecast['head_count']=$head_count;
        $forecast['total']=$total_arr;
        
        return $forecast;

    }

}
