<?php

namespace CannaPlan\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
/**
 * @property int $id
 * @property int $company_id
 * @property string $deleted_at
 * @property string $remember_token
 * @property string $created_at
 * @property string $updated_at
 * @property Company $company
 * @property Chapter[] $chapters
 */
class Plan extends Model
{
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'plan';

    /**
     * @var array
     */
    protected $fillable = ['company_id'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function company()
    {
        return $this->belongsTo('CannaPlan\Models\Company');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function chapters()
    {
        return $this->hasMany('CannaPlan\Models\Chapter');
    }
}
