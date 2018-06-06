<?php

namespace CannaPlan\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $pitch_id
 * @property string $name
 * @property string $advantage
 * @property string $deleted_at
 * @property string $remember_token
 * @property string $created_at
 * @property string $updated_at
 * @property Pitch $pitch
 */
class Competitor extends Model
{
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'competitor';

    /**
     * @var array
     */
    protected $fillable = ['pitch_id', 'name', 'advantage', 'deleted_at', 'remember_token', 'created_at', 'updated_at'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function pitch()
    {
        return $this->belongsTo('CannaPlan\Models\Pitch');
    }
}
