<?php

namespace CannaPlan\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
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
    protected $dates=['deleted_at'];
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'target_market_graph';

    /**
     * @var array
     */
    protected $fillable = ['pitch_id', 'segment_name', 'segment_prospect', 'prospect_cost'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function pitch()
    {
        return $this->belongsTo('CannaPlan\Models\Pitch');
    }
}
