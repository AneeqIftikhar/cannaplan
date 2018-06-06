<?php

namespace CannaPlan\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $pitch_id
 * @property string $name
 * @property string $job_title
 * @property string $biography
 * @property string $image
 * @property string $deleted_at
 * @property string $remember_token
 * @property string $created_at
 * @property string $updated_at
 * @property Pitch $pitch
 */
class TeamRole extends Model
{
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'team_role';

    /**
     * @var array
     */
    protected $fillable = ['pitch_id', 'name', 'job_title', 'biography', 'image', 'deleted_at', 'remember_token', 'created_at', 'updated_at'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function pitch()
    {
        return $this->belongsTo('CannaPlan\Models\Pitch');
    }
}
