<?php

namespace CannaPlan\Models;

use Illuminate\Database\Eloquent\Model;

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
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'labor';

    /**
     * @var array
     */
    protected $fillable = ['name', 'number_of_employees', 'labor_type', 'staff_role_type', 'pay', 'start_date', 'annual_raise_percent', 'deleted_at', 'remember_token', 'created_at', 'updated_at'];

}
