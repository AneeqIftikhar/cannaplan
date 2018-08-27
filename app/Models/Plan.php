<?php

namespace CannaPlan\Models;
use CannaPlan\Helpers\PlanData;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Auth;
Relation::morphMap([
    'topic'=>'CannaPlan\Models\Topic',
    'chart'=>'CannaPlan\Models\Chart',
    'table'=>'CannaPlan\Models\Table'
]);
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
    protected $fillable = [];
    protected $guarded = ['id','company_id','created_by'];
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
        return $this->hasMany('CannaPlan\Models\Chapter')->orderBy('order');
    }
    public static function add_entries_in_plan_module($plan)
    {
        $data=json_decode(PlanData::get_json_data() , true);
        $company=$plan->company;
        for ($i=0;$i<sizeof($data['chapter']);$i++)
        {
            $chap=$plan->chapters()->create(["name"=>$data['chapter'][$i]['name'],"order"=>$data['chapter'][$i]["order"]]);
            for ($j=0;$j<sizeof($data['chapter'][$i]['section']);$j++)
            {
                $section=$chap->sections()->create(["name"=>$data['chapter'][$i]['section'][$j]['name'],"order"=>$data['chapter'][$i]['section'][$j]["order"]]);
                for ($k=0;$k<sizeof($data['chapter'][$i]['section'][$j]['section_content']);$k++)
                {
                    if($data['chapter'][$i]['section'][$j]['section_content'][$k]['content_type']=='topic')
                    {
                        //$section->sectionContents()->create($data['chapter'][$i]['section'][$j]['section_content'][$k]);
                        $section_content=new SectionContent();
                        $section_content->section_id=$section->id;
                        $section_content->order=$data['chapter'][$i]['section'][$j]['section_content'][$k]['order'];
                        $section_content->save();
                        $topic=Topic::create(['name'=>$data['chapter'][$i]['section'][$j]['section_content'][$k]['name'],'is_removed'=>true,'company_id'=>$company->id]);
                        $topic->contents()->save($section_content);
                    }
                    else
                    {
                        $section->sectionContents()->create($data['chapter'][$i]['section'][$j]['section_content'][$k]);
                    }

                }
            }

        }
        $topic_data=json_decode(PlanData::get_topic_data() , true);
        for ($i=0;$i<sizeof($topic_data);$i++)
        {
            $topic=Topic::create(['name'=>$topic_data[$i]['name'],'is_removed'=>false,'company_id'=>$company->id]);
        }



    }

}
