<?php

namespace CannaPlan\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\Relation;
Relation::morphMap([
    'current'=>'CannaPlan\Models\Current',
    'long_term'=>'CannaPlan\Models\LongTerm'
]);
/**
 * @property int $id
 * @property int $forecast_id
 * @property string $name
 * @property string $amount_type
 * @property int $amount
 * @property string $start_date
 * @property int $asset_duration_id
 * @property string $asset_duration_value
 * @property string $deleted_at
 * @property string $remember_token
 * @property string $created_at
 * @property string $updated_at
 * @property Forecast $forecast
 */
class Asset extends Model
{
    use SoftDeletes;
    protected $dates=['deleted_at'];
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'asset';

    /**
     * @var array
     */
    protected $fillable = ['forecast_id', 'name', 'amount_type', 'amount', 'start_date', 'asset_duration_id', 'asset_duration_type '];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function forecast()
    {
        return $this->belongsTo('CannaPlan\Models\Forecast');
    }
    public function asset_duration()
    {
        return $this->morphTo();
    }
}
