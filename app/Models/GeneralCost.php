<?php

namespace CannaPlan\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
/**
 * @property int $id
 * @property int $amount
 * @property string $cost_start_date
 * @property string $deleted_at
 * @property string $remember_token
 * @property string $created_at
 * @property string $updated_at
 */
class GeneralCost extends Model
{
    use SoftDeletes;
    protected $dates=['deleted_at'];
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'general_cost';

    /**
     * @var array
     */
    protected $fillable = ['amount', 'cost_start_date'];
    public function direct_costs()
    {
        return $this->morphMany('CannaPlan\Models\DirectCost', 'direct_cost');
    }
}
