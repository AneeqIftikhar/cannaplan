<?php

namespace CannaPlan\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\Relation;
Relation::morphMap([
    'loan'=>'CannaPlan\Models\Loan',
    'investment'=>'CannaPlan\Models\Investment',
    'other'=>'CannaPlan\Models\Other'
]);
/**
 * @property int $id
 * @property int $forecast_id
 * @property string $name
 * @property int $fund_id
 * @property string $fund_type
 * @property string $deleted_at
 * @property string $remember_token
 * @property string $created_at
 * @property string $updated_at
 * @property Forecast $forecast
 */
class Financing extends Model
{
    use SoftDeletes;
    protected $dates=['deleted_at'];
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'financing';

    /**
     * @var array
     */
    protected $fillable = ['forecast_id', 'name', 'fund_id', 'fund_type'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function forecast()
    {
        return $this->belongsTo('CannaPlan\Models\Forecast');
    }
    public function fund()
    {
        return $this->morphTo();
    }
}
