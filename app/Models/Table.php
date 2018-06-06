<?php

namespace CannaPlan\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property string $deleted_at
 * @property string $remember_token
 * @property string $created_at
 * @property string $updated_at
 */
class Table extends Model
{
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'table';

    /**
     * @var array
     */
    protected $fillable = ['name', 'deleted_at', 'remember_token', 'created_at', 'updated_at'];
    public function contents()
    {
        return $this->morphMany('CannaPlan\Models\SectionContent', 'content');
    }
}
