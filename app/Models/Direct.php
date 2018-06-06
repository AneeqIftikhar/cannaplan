<?php

namespace CannaPlan\Models;

use Illuminate\Database\Eloquent\Model;

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
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'direct';

    /**
     * @var array
     */
    protected $fillable = ['name', 'direct_cost_id', 'direct_cost_type', 'deleted_at', 'remember_token', 'created_at', 'updated_at'];

}
