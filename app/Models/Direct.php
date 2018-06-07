<?php

namespace CannaPlan\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\Relation;
Relation::morphMap([
    'general_cost'=>'CannaPlan\Models\GeneralCost',
    'cost_on_revenue'=>'CannaPlan\Models\CostOnRevenue'
]);
/**
 * @property int $id
 * @property string $name
 * @property int $direct_cost_id
 * @property string $direct_cost_type
 * @property string $deleted_at
 * @property string $remember_token
 * @property string $created_at
 * @property string $updated_at
 */
class Direct extends Model
{
    use SoftDeletes;
    protected $dates=['deleted_at'];
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'direct';

    /**
     * @var array
     */
    protected $fillable = ['name', 'direct_cost_id', 'direct_cost_type'];
    public function charges()
    {
        return $this->morphMany('CannaPlan\Models\Cost', 'charge');
    }
    public function direct_cost()
    {
        return $this->morphTo();
    }
}
