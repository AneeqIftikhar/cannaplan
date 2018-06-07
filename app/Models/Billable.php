<?php

namespace CannaPlan\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
/**
 * @property int $id
 * @property int $hour
 * @property string $revenue_start_date
 * @property int $hourly_rate
 * @property string $deleted_at
 * @property string $remember_token
 * @property string $created_at
 * @property string $updated_at
 */
class Billable extends Model
{
    use SoftDeletes;
    protected $dates=['deleted_at'];
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'billable';

    /**
     * @var array
     */
    protected $fillable = ['hour', 'revenue_start_date', 'hourly_rate'];
    public function earnings()
    {
        return $this->morphMany('CannaPlan\Models\Revenue', 'earning');
    }
}
