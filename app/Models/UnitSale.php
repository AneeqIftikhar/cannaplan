<?php

namespace CannaPlan\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $unit_sold
 * @property string $revenue_start_date
 * @property int $unit_price
 * @property string $deleted_at
 * @property string $remember_token
 * @property string $created_at
 * @property string $updated_at
 */
class UnitSale extends Model
{
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'unit_sale';

    /**
     * @var array
     */
    protected $fillable = ['unit_sold', 'revenue_start_date', 'unit_price', 'deleted_at', 'remember_token', 'created_at', 'updated_at'];

}
