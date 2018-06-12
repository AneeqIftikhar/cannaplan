<?php

namespace CannaPlan\Models;
use CannaPlan\Helpers\PlanData;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
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
    public static function boot() {
        parent::boot();

        // create a event to happen on saving
        static::creating(function($table)  {
            $table->created_by = Auth::user()->id;
        });

        static::deleting(function($plan) {
            foreach ($plan->chapters()->get() as $chapter) {
                $chapter->delete();
            }
        });
    }
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
    public static function add_entries_in_plan_module($plan)
    {
        $data=json_decode(PlanData::get_json_data() , true);

        for ($i=0;$i<sizeof($data['chapter']);$i++)
        {
            $chap=$plan->chapters()->create(["name"=>$data['chapter'][$i]['name'],"order"=>$data['chapter'][$i]["order"]]);
            for ($j=0;$j<sizeof($data['chapter'][$i]['section']);$j++)
            {
                $section=$chap->sections()->create(["name"=>$data['chapter'][$i]['section'][$j]['name'],"order"=>$data['chapter'][$i]['section'][$j]["order"]]);
                for ($k=0;$k<sizeof($data['chapter'][$i]['section'][$j]['section_content']);$k++)
                {
                    $section->sectionContents()->create($data['chapter'][$i]['section'][$j]['section_content'][$k]);
                }
            }

        }


    }
}
