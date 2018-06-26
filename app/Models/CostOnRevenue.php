<?php

namespace CannaPlan\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

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
    use SoftDeletes;
    protected $dates=['deleted_at'];
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'cost_on_revenue';

    /**
     * @var array
     */
    protected $fillable = ['revenue_id', 'amount'];

    public static function boot()
    {
        parent::boot();

        // create a event to happen on saving
        static::creating(function ($table) {
            $table->created_by = Auth::user()->id;
        });
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function revenue()
    {
        return $this->belongsTo('CannaPlan\Models\Revenue');
    }
    public function direct_costs()
    {
        return $this->morphMany('CannaPlan\Models\DirectCost', 'direct_cost');
    }
}
