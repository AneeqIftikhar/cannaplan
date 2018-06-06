<?php

namespace CannaPlan\Models;

use Illuminate\Database\Eloquent\Model;

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
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'asset';

    /**
     * @var array
     */
    protected $fillable = ['forecast_id', 'name', 'amount_type', 'amount', 'start_date', 'asset_duration_id', 'asset_duration_value', 'deleted_at', 'remember_token', 'created_at', 'updated_at'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function forecast()
    {
        return $this->belongsTo('CannaPlan\Models\Forecast');
    }
}
