<?php

namespace CannaPlan\Models;

use Illuminate\Database\Eloquent\Model;

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
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'milestone';

    /**
     * @var array
     */
    protected $fillable = ['pitch_id', 'due_date', 'responsible', 'details', 'email_reminder', 'prospect_cost', 'deleted_at', 'remember_token', 'created_at', 'updated_at'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function pitch()
    {
        return $this->belongsTo('CannaPlan\Models\Pitch');
    }
}
