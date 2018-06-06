<?php

namespace CannaPlan\Models;

use Illuminate\Database\Eloquent\Model;

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
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'billable';

    /**
     * @var array
     */
    protected $fillable = ['hour', 'revenue_start_date', 'hourly_rate', 'deleted_at', 'remember_token', 'created_at', 'updated_at'];

}
