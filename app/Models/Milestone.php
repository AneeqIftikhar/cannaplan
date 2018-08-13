<?php

namespace CannaPlan\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use DateTime;
/**
 * @property int $id
 * @property int $pitch_id
 * @property string $due_date
 * @property string $responsible
 * @property string $details
 * @property boolean $email_reminder
 * @property int $prospect_cost
 * @property string $deleted_at
 * @property string $remember_token
 * @property string $created_at
 * @property string $updated_at
 * @property Pitch $pitch
 */
class Milestone extends Model
{
    use SoftDeletes;
    protected $dates=['deleted_at'];
    protected $appends = ['is_late','due_days'];
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'milestone';

    public static function boot() {
        parent::boot();

        // create a event to happen on saving
        static::creating(function($table)  {
            $table->created_by = Auth::user()->id;
        });
    }

    /**
     * @var array
     */
    protected $fillable = ['title','due_date', 'responsible', 'details', 'email_reminder', 'is_completed'];
    protected $guarded = ['id','pitch_id','created_by'];
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function pitch()
    {
        return $this->belongsTo('CannaPlan\Models\Pitch');
    }
    public function getIsLateAttribute()
    {
        $now=date('Y-m-d',time());
        $now = new DateTime($now);
        $due_date=date($this->due_date);
        $due_date = new DateTime($due_date);
        if($due_date<$now)
        {
            return 1;
        }
        else
        {
            return 0;
        }
    }
    public function getDueDaysAttribute()
    {
        $now=date('Y-m-d',time());
        $now = new DateTime($now);
        $due_date=date($this->due_date);
        $due_date = new DateTime($due_date);
        if($due_date<$now)
        {
            if($now->diff($due_date)->format('%a')=='1')
            {
                return 'Yesterday';
            }
            return $now->diff($due_date)->format('%a Days Ago');
        }
        else if($due_date>$now)
        {
            if($now->diff($due_date)->format('%a')=='1')
            {
                return 'Tomorrow';
            }
            return $now->diff($due_date)->format('%a Days Away');
        }
        else
        {
            return 'Today';
        }
    }
    public static function getMilestoneByCompany($id)
    {
        $company=Company::where('id',$id)->first();
        $pitch=$company->pitches()->first();
        $milestones=$pitch->milestones()->get();
        $result=['company'=>$company,'milestones'=>$milestones];
        return $result;
    }
}
