<?php

namespace CannaPlan\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
/**
 * @property int $id
 * @property string $name
 * @property string $deleted_at
 * @property string $remember_token
 * @property string $created_at
 * @property string $updated_at
 */
class Chart extends Model
{
    use SoftDeletes;
    protected $dates=['deleted_at'];
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'chart';

    /**
     * @var array
     */
    protected $fillable = ['name', 'deleted_at', 'remember_token', 'created_at', 'updated_at'];
    public function contents()
    {
        return $this->morphMany('CannaPlan\Models\SectionContent', 'content');
    }
}
