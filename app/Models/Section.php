<?php

namespace CannaPlan\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
/**
 * @property int $id
 * @property int $chapter_id
 * @property string $name
 * @property int $order
 * @property string $deleted_at
 * @property string $remember_token
 * @property string $created_at
 * @property string $updated_at
 * @property Chapter $chapter
 * @property SectionContent[] $sectionContents
 */
class Section extends Model
{
    use SoftDeletes;
    protected $dates=['deleted_at'];
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'section';

    /**
     * @var array
     */
    protected $fillable = ['chapter_id', 'name', 'order'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function chapter()
    {
        return $this->belongsTo('CannaPlan\Models\Chapter');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function sectionContents()
    {
        return $this->hasMany('CannaPlan\Models\SectionContent');
    }
}
