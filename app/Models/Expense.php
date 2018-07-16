<?php

namespace CannaPlan\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use DateTime;
/**
 * @property int $id
 * @property int $forecast_id
 * @property string $name
 * @property string $type
 * @property int $amount
 * @property string $start_date
 * @property string $deleted_at
 * @property string $remember_token
 * @property string $created_at
 * @property string $updated_at
 * @property Forecast $forecast
 */
class Expense extends Model
{
    use SoftDeletes;
    protected $dates=['deleted_at'];
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'expense';

    public static function boot() {
        parent::boot();

        // create a event to happen on saving
        static::creating(function($table)  {
            $table->created_by = Auth::user()->id;
        });
    }

    /**
     * @var array
     */
    protected $fillable = ['name', 'type', 'amount', 'start_date'];
    protected $guarded = ['id','forecast_id','created_by'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function forecast()
    {
        return $this->belongsTo('CannaPlan\Models\Forecast');
    }
    public static function getExpenseByForecastId($id)
    {
        $forecast=Forecast::where('id',$id)->with('company','expenses')->first();
        $start_of_forecast=date($forecast->company->start_of_forecast);
        $start_of_forecast = new DateTime($start_of_forecast);
        $total_arr=array();
        for ($j = 1; $j < 13; $j++) {
            $total_arr['amount_m_' . $j] = null;
        }
        for ($j = 1; $j < 6; $j++) {
            $total_arr['amount_y_' . $j] = null;
        }


        for ($i=0 ; $i<count($forecast->expenses);$i++)
        {
            $date=date($forecast->expenses[$i]['start_date']);
            $d2 = new DateTime($date);
            $diff_month=$start_of_forecast->diff($d2)->m;
            $diff_year=$start_of_forecast->diff($d2)->y;
            $year_1_total=0;
            for ($j = 1; $j < 13; $j++) {

                if($diff_year==0 && $diff_month<$j)
                {
                    $forecast->expenses[$i]['amount_m_' . $j] = $forecast->expenses[$i]['amount'];
                    $year_1_total=$year_1_total+$forecast->expenses[$i]['amount_m_' . $j];
                    $total_arr['amount_m_' . $j] = $total_arr['amount_m_' . $j] + $forecast->expenses[$i]['amount_m_' . $j];
                }
                else
                {
                    $forecast->expenses[$i]['amount_m_' . $j] = null;
                }

            }
            for ($j = 1; $j < 6; $j++) {
                if($diff_year<$j)
                {
                    if ($j == 1)
                    {
                        $forecast->expenses[$i]['amount_y_' . $j]=$year_1_total;
                        $total_arr['amount_y_' . $j] = $total_arr['amount_y_' . $j] +$forecast->expenses[$i]['amount_y_' . $j];
                    }
                    else
                    {
                        $forecast->expenses[$i]['amount_y_' . $j]=$forecast->expenses[$i]['amount']*12;
                        $total_arr['amount_y_' . $j] = $total_arr['amount_y_' . $j] +$forecast->expenses[$i]['amount_y_' . $j];
                    }
                }
                else
                {
                    $forecast->expenses[$i]['amount_y_' . $j]= null;
                }

            }

        }
        $forecast['total']=$total_arr;
        return $forecast;
    }
}
