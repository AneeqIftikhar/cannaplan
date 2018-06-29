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
        $now=date('Y-m-d',time());


        $now = new DateTime($now);


        for ($i=0 ; $i<count($forecast->expenses);$i++)
        {
            $date=date($forecast->expenses[$i]['start_date']);
            $d2 = new DateTime($date);
            $diff=$now->diff($d2)->m;
            for ($j = 1; $j < 13; $j++) {

                if($diff<$j)
                {
                    $forecast->expenses[$i]['amount_m_' . $j] = $forecast->expenses[$i]['amount'];
                }
                else
                {
                    $forecast->expenses[$i]['amount_m_' . $j] = 0;
                }

            }
            for ($j = 1; $j < 6; $j++) {
                $forecast->expenses[$i]['amount_y_' . $j]=$forecast->expenses[$i]['amount']*12;
            }
        }
        return $forecast;
    }
}
