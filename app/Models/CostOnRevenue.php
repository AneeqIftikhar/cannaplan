<?php

namespace CannaPlan\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $revenue_id
 * @property int $amount
 * @property string $deleted_at
 * @property string $remember_token
 * @property string $created_at
 * @property string $updated_at
 * @property Revenue $revenue
 */
class CostOnRevenue extends Model
{
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'cost_on_revenue';

    /**
     * @var array
     */
    protected $fillable = ['revenue_id', 'amount', 'deleted_at', 'remember_token', 'created_at', 'updated_at'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function revenue()
    {
        return $this->belongsTo('CannaPlan\Models\Revenue');
    }
}
