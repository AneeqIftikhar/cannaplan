<?php

namespace CannaPlan\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\Relation;
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

    /**
     * @var array
     */
    protected $fillable = ['forecast_id', 'charge_id', 'charge_type'];

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
}
