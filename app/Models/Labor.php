<?php

namespace CannaPlan\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

/**
 * @property int $id
 * @property string $name
 * @property int $number_of_employees
 * @property string $labor_type
 * @property string $staff_role_type
 * @property float $pay
 * @property int $start_date
 * @property float $annual_raise_percent
 * @property string $deleted_at
 * @property string $remember_token
 * @property string $created_at
 * @property string $updated_at
 */
class Labor extends Model
{
    use SoftDeletes;
    protected $dates=['deleted_at'];
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'labor';

    /**
     * @var array
     */
    protected $fillable = ['name', 'number_of_employees', 'labor_type', 'staff_role_type', 'pay', 'start_date', 'annual_raise_percent'];
    public static function boot()
    {
        parent::boot();

        // create a event to happen on saving
        static::creating(function ($table) {
            $table->created_by = Auth::user()->id;
        });
    }
    public function charges()
    {
        return $this->morphMany('CannaPlan\Models\Cost', 'charge');
    }
}
