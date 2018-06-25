<?php

namespace CannaPlan\Models;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
/**
 * @property int $id
 * @property int $company_id
 * @property string $company_name
 * @property string $logo
 * @property string $headlights
 * @property string $problem
 * @property string $solution
 * @property int $funds_required
 * @property string $funds_usage_description
 * @property string $sales_channel
 * @property string $marketing_activities
 * @property string $forecast_revenue
 * @property string $forecast_cost
 * @property string $forecast_type
 * @property string $deleted_at
 * @property string $remember_token
 * @property string $created_at
 * @property string $updated_at
 * @property Company $company
 * @property Competitor[] $competitors
 * @property Milestone[] $milestones
 * @property TargetMarketGraph[] $targetMarketGraphs
 * @property TeamRole[] $teamRoles
 */
class Pitch extends Model
{
    use SoftDeletes;
    protected $dates=['deleted_at'];
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'pitch';

    /**
     * @var array
     */
    protected $fillable = ['company_name', 'logo', 'headline', 'problem', 'solution', 'funds_required', 'funds_usage_description', 'sales_channel', 'marketing_activities', 'forecast_revenue', 'forecast_cost', 'forecast_type' , 'is_started'];
    protected $guarded = ['id','company_id','created_by'];

    public static function boot() {
        parent::boot();

        // create a event to happen on saving
        static::creating(function($table)  {
            $table->created_by = Auth::user()->id;
        });

        static::deleting(function($pitch) {
            foreach ($pitch->competitors()->get() as $competitor) {
                $competitor->delete();
            }
            foreach ($pitch->milestones()->get() as $milestone) {
                $milestone->delete();
            }
            foreach ($pitch->teamRoles()->get() as $teamRole) {
                $teamRole->delete();
            }
            foreach ($pitch->targetMarketGraphs()->get() as $targetMarketGraph) {
                $targetMarketGraph->delete();
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
    public function competitors()
    {
        return $this->hasMany('CannaPlan\Models\Competitor')->orderBy('order');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function milestones()
    {
        return $this->hasMany('CannaPlan\Models\Milestone');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function targetMarketGraphs()
    {
        return $this->hasMany('CannaPlan\Models\TargetMarketGraph');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function teamRoles()
    {
        return $this->hasMany('CannaPlan\Models\TeamRole')->orderBy('order');
    }

    public static function is_user_pitch($id){
        $company=Pitch::find($id)->company;
        $verify=Company::is_user_company($company->id);

        return $verify;
    }
}
