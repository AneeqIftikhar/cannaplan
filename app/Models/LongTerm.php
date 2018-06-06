<?php

namespace CannaPlan\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property float $year
 * @property boolean $will_sell
 * @property int $selling_amount
 * @property string $selling_date
 * @property string $deleted_at
 * @property string $remember_token
 * @property string $created_at
 * @property string $updated_at
 */
class LongTerm extends Model
{
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'long_term';

    /**
     * @var array
     */
    protected $fillable = ['year', 'will_sell', 'selling_amount', 'selling_date', 'deleted_at', 'remember_token', 'created_at', 'updated_at'];

}
