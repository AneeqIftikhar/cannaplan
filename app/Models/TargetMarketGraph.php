<?php

namespace CannaPlan\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

/**
 * @property int $id
 * @property int $pitch_id
 * @property string $segment_name
 * @property int $segment_prospect
 * @property int $prospect_cost
 * @property string $deleted_at
 * @property string $remember_token
 * @property string $created_at
 * @property string $updated_at
 * @property Pitch $pitch
 */
class TargetMarketGraph extends Model
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'target_market_graph';
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
    protected $fillable = ['segment_name', 'segment_prospect', 'prospect_cost'];
    protected $guarded = ['id','pitch_id','created_by'];
    protected $dates=['deleted_at'];
    protected $appends = ['segment_percentage','segment_cost'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function pitch()
    {
        return $this->belongsTo('CannaPlan\Models\Pitch');
    }

    public function getSegmentPercentageAttribute()
    {
        $pitch=$this->pitch()->first();
        $total= $pitch->targetMarketGraphs()->sum('segment_prospect');

        $percent=(($this->segment_prospect)/($total))*100;
        if($percent<1)
        {
            return "<1%";
        }
        else
        {
            return round($percent)."%";
        }

    }
    public function getSegmentCostAttribute()
    {
        if(count($this->pitch()->first()->targetMarketGraphs()->get())>0)
        {
            $cost=($this->segment_prospect)*($this->prospect_cost);
            $cost=round($cost);
            if($cost>=1000000000)
            {
                return round($cost/1000000000,1)."B";
            }
            else if($cost>=1000000)
            {
                return round($cost/1000000,1)."M";
            }
            else if($cost>=1000)
            {
                return round($cost/1000)."K";
            }
            else
            {
                return $cost;
            }
        }


    }
}
