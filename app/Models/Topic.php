<?php

namespace CannaPlan\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $description
 * @property string $deleted_at
 * @property string $remember_token
 * @property string $created_at
 * @property string $updated_at
 */
class Topic extends Model
{
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'topic';

    /**
     * @var array
     */
    protected $fillable = ['description', 'deleted_at', 'remember_token', 'created_at', 'updated_at'];

}
