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
 * @property string $amount_type
 * @property int $amount
 * @property string $start_date
 * @property string $deleted_at
 * @property string $remember_token
 * @property string $created_at
 * @property string $updated_at
 * @property Forecast $forecast
 */
class Dividend extends Model
{
    use SoftDeletes;
    protected $dates=['deleted_at'];

    public static function boot() {
        parent::boot();

        // create a event to happen on saving
        static::creating(function($table)  {
            $table->created_by = Auth::user()->id;
        });
    }
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'dividend';

    /**
     * @var array
     */
    protected $fillable = ['name', 'amount_type', 'amount', 'start_date','amount_distribution'];
    protected $guarded = ['id','forecast_id','created_by'];
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function forecast()
    {
        return $this->belongsTo('CannaPlan\Models\Forecast');
    }
    public static function getDividendByForecast($id)
    {
        $forecast = Forecast::where('id', $id)->with(['company', 'dividends'])->first();
        $start_of_forecast = new DateTime($forecast->company->start_of_forecast);

        $total = array();
        for($i=1;$i<13;$i++)
        {
            $total['amount_m_'.$i]=null;
        }
        for($i=1;$i<6;$i++)
        {
            $total['amount_y_'.$i]=null;
        }
        for ($i=0;$i<count($forecast->dividends);$i++)
        {
            $date=date($forecast->dividends[$i]->start_date);
            $d2 = new DateTime($date);
            $diff_month=$start_of_forecast->diff($d2)->m;
            $diff_year=$start_of_forecast->diff($d2)->y;
            if($forecast->dividends[$i]->amount_type=="one_time")
            {
                for($j=1;$j<13;$j++)
                {
                    if($diff_year==0 && $diff_month==$j-1)
                    {
                        $forecast->dividends[$i]['amount_m_' . $j]=$forecast->dividends[$i]->amount;
                    }
                    else
                    {
                        $forecast->dividends[$i]['amount_m_' . $j]=null;
                    }
                }
                for($j=1;$j<6;$j++)
                {
                    if($diff_year==$j-1)
                    {
                        $forecast->dividends[$i]['amount_y_' . $j]=$forecast->dividends[$i]->amount;
                    }
                    else
                    {
                        $forecast->dividends[$i]['amount_y_' . $j]= null;
                    }
                }
            }
            else if($forecast->dividends[$i]->amount_type=="constant")
            {
                if($forecast->dividends[$i]->amount_distribution=="month")
                {   $year_1_total=0;
                    for($j=1;$j<13;$j++)
                    {
                        if($diff_year==0 && $diff_month<$j)
                        {
                            $forecast->dividends[$i]['amount_m_' . $j]=$forecast->dividends[$i]->amount;
                            $year_1_total=$year_1_total+$forecast->dividends[$i]['amount_m_' . $j];
                        }
                        else
                        {
                            $forecast->dividends[$i]['amount_m_' . $j]=null;
                        }
                    }
                    for($j=1;$j<6;$j++)
                    {
                        if($diff_year<$j)
                        {
                            if($j==1)
                            {
                                $forecast->dividends[$i]['amount_y_' . $j]=$year_1_total;
                            }
                            else
                            {
                                $forecast->dividends[$i]['amount_y_' . $j]=$forecast->dividends[$i]->amount*12;
                            }

                        }
                        else
                        {
                            $forecast->dividends[$i]['amount_y_' . $j]= null;
                        }
                    }
                }
                else if($forecast->dividends[$i]->amount_distribution=="year")
                {
                    $year_1_total=0;
                    $index=1;
                    $amount=$forecast->dividends[$i]->amount;
                    $total_amount=$amount;
                    for($j=1;$j<13;$j++)
                    {
                        if($diff_year==0 && $diff_month<$j)
                        {
                            if ($j == 12 && $index==$j)
                            {
                                $forecast->dividends[$i]['amount_m_12']=$amount;
                                $year_1_total=$year_1_total+$forecast->dividends[$i]['amount_m_' . $j];
                            }
                            else
                            {

                                $forecast->dividends[$i]['amount_m_' . $j] = floor(($amount / (13 - $index)));
                                $year_1_total=$year_1_total+$forecast->dividends[$i]['amount_m_' . $j];
                                $amount = $amount - floor(($amount / (13 - $index)));
                                $index=$index+1;
                            }
                        }
                        else
                        {
                            $forecast->dividends[$i]['amount_m_' . $j]=null;
                        }
                    }
                    for($j=1;$j<6;$j++)
                    {
                        if($diff_year<$j)
                        {
                            if($j==1)
                            {
                                $forecast->dividends[$i]['amount_y_'.$j]=$year_1_total;
                            }
                            else
                            {
                                $forecast->dividends[$i]['amount_y_'.$j]=$total_amount;
                            }

                        }
                        else
                        {
                            $forecast->dividends[$i]['amount_y_' . $j] = null;
                        }
                    }
                }
            }
            for($j=1;$j<13;$j++)
            {
                if($forecast->dividends[$i]['amount_m_' . $j])
                {
                    $total['amount_m_'.$j]=$total['amount_m_'.$j]+$forecast->dividends[$i]['amount_m_' . $j];
                }
            }
            for($j=1;$j<6;$j++)
            {
                if($forecast->dividends[$i]['amount_y_' . $j])
                {
                    $total['amount_y_'.$j]=$total['amount_y_'.$j]+$forecast->dividends[$i]['amount_y_' . $j];
                }
            }
        }
        $forecast['total']=$total;
        return $forecast;
    }
}
