<?php

namespace CannaPlan\Models;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\SoftDeletes;
Relation::morphMap([
    'chart'=>'CannaPlan\Models\Chart',
    'table'=>'CannaPlan\Models\Table',
    'topic'=>'CannaPlan\Models\Topic'
]);
/**
 * @property int $id
 * @property int $section_id
 * @property string $name
 * @property int $order
 * @property int $content_id
 * @property string $content_type
 * @property string $deleted_at
 * @property string $remember_token
 * @property string $created_at
 * @property string $updated_at
 * @property Section $section
 */
class SectionContent extends Model
{
    use SoftDeletes;
    protected $dates=['deleted_at'];

    public static function boot() {
        parent::boot();

        // create a event to happen on saving
        static::creating(function($table)  {
            $table->created_by = Auth::user()->id;
        });
    }
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'section_content';

    /**
     * @var array
     */
    protected $fillable = ['section_id', 'name', 'order', 'content_id', 'content_type'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function section()
    {
        return $this->belongsTo('CannaPlan\Models\Section');
    }
    public function content()
    {
        return $this->morphTo();
    }
}
