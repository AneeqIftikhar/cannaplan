<?php

namespace CannaPlan\Models;

use Illuminate\Database\Eloquent\Model;

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
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'general_cost';

    /**
     * @var array
     */
    protected $fillable = ['amount', 'cost_start_date', 'deleted_at', 'remember_token', 'created_at', 'updated_at'];

}
