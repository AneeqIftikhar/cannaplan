<?php

namespace CannaPlan\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $plan_id
 * @property string $name
 * @property int $order
 * @property string $deleted_at
 * @property string $remember_token
 * @property string $created_at
 * @property string $updated_at
 * @property Plan $plan
 * @property Section[] $sections
 */
class Chapter extends Model
{
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'chapter';

    /**
     * @var array
     */
    protected $fillable = ['plan_id', 'name', 'order', 'deleted_at', 'remember_token', 'created_at', 'updated_at'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function plan()
    {
        return $this->belongsTo('CannaPlan\Models\Plan');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function sections()
    {
        return $this->hasMany('CannaPlan\Models\Section');
    }
}
