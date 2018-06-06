<?php

namespace CannaPlan\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $month
 * @property string $deleted_at
 * @property string $remember_token
 * @property string $created_at
 * @property string $updated_at
 */
class Current extends Model
{
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'current';

    /**
     * @var array
     */
    protected $fillable = ['month', 'deleted_at', 'remember_token', 'created_at', 'updated_at'];

}
