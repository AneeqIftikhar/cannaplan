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

    public static function addDirect()
    {

    }

    public static function addLabor($number_of_employees, $labor_type, $pay, $start_date, $staff_role_type)
    {
        $labor=Labor::create(['number_of_employees'=>$number_of_employees , 'labor_type'=>$labor_type , 'start_date'=>$start_date , 'staff_role_type'=>$staff_role_type]);
        return $labor;
    }

    public static function addGeneral($amount , $cost_start_date)
    {

    }
}
