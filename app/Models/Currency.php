<?php

namespace CannaPlan\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property string $symbol
 * @property string $code
 * @property string $deleted_at
 * @property string $remember_token
 * @property string $created_at
 * @property string $updated_at
 * @property Company[] $companies
 */
class Currency extends Model
{
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'currency';

    /**
     * @var array
     */
    protected $fillable = ['name', 'symbol', 'code', 'deleted_at', 'remember_token', 'created_at', 'updated_at'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function companies()
    {
        return $this->hasMany('CannaPlan\Models\Company');
    }
}
