<?php

namespace CannaPlan\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
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
    use SoftDeletes;
    protected $dates=['deleted_at'];
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'topic';

    /**
     * @var array
     */
    protected $fillable = ['description','name','company_id','is_removed'];
    protected $guarded = ['id','created_by'];
    public function contents()
    {
        return $this->morphMany('CannaPlan\Models\SectionContent', 'content');
    }
}
