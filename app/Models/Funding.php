<?php

namespace CannaPlan\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

/**
 * @property int $id
 * @property int $other_id
 * @property string $start_date
 * @property int $amount_m_1
 * @property int $amount_m_2
 * @property int $amount_m_3
 * @property int $amount_m_4
 * @property int $amount_m_5
 * @property int $amount_m_6
 * @property int $amount_m_7
 * @property int $amount_m_8
 * @property int $amount_m_9
 * @property int $amount_m_10
 * @property int $amount_m_11
 * @property int $amount_m_12
 * @property int $amount_y_1
 * @property int $amount_y_2
 * @property int $amount_y_3
 * @property string $deleted_at
 * @property string $remember_token
 * @property string $created_at
 * @property string $updated_at
 * @property Other $other
 */
class Funding extends Model
{
    use SoftDeletes;
    protected $dates=['deleted_at'];
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'funding';

    /**
     * @var array
     */
    protected $fillable = ['other_id', 'amount_m_1', 'amount_m_2', 'amount_m_3', 'amount_m_4', 'amount_m_5', 'amount_m_6', 'amount_m_7', 'amount_m_8', 'amount_m_9', 'amount_m_10', 'amount_m_11', 'amount_m_12', 'amount_y_1', 'amount_y_2', 'amount_y_3', 'amount_y_4', 'amount_y_5'];

    public static function boot()
    {
        parent::boot();

        // create a event to happen on saving
        static::creating(function ($table) {
            $table->created_by = Auth::user()->id;
        });
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function other()
    {
        return $this->belongsTo('CannaPlan\Models\Other');
    }
}
