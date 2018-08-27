<?php

namespace CannaPlan\Models;
use Illuminate\Support\Facades\Auth;
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

    public static function boot() {
        parent::boot();

        // create a event to happen on saving
        static::creating(function($table)  {
            $table->created_by = Auth::user()->id;
        });

        static::deleting(function($sections) {
            foreach ($sections->sectionContents()->get() as $sectionContent) {
                if($sectionContent->content_type=='topic')
                {
                    $sectionContent->content()->update(['is_removed'=>false]);
                    $sectionContent->delete();
                }
                else
                {
                    $sectionContent->delete();
                }

            }
        });
    }
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
    protected $guarded = ['id','created_by'];

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
        return $this->hasMany('CannaPlan\Models\SectionContent')->orderBy('order');
    }
}
