<?php

namespace CannaPlan\Models;

use Illuminate\Database\Eloquent\Model;

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
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'expense';

    /**
     * @var array
     */
    protected $fillable = ['forecast_id', 'name', 'type', 'amount', 'start_date', 'deleted_at', 'remember_token', 'created_at', 'updated_at'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function forecast()
    {
        return $this->belongsTo('CannaPlan\Models\Forecast');
    }
}
