<?php

namespace CannaPlan\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $receive_date
 * @property int $receive_amount
 * @property int $interest_rate
 * @property int $interest_months
 * @property string $deleted_at
 * @property string $remember_token
 * @property string $created_at
 * @property string $updated_at
 */
class Loan extends Model
{
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'loan';

    /**
     * @var array
     */
    protected $fillable = ['receive_date', 'receive_amount', 'interest_rate', 'interest_months', 'deleted_at', 'remember_token', 'created_at', 'updated_at'];

}
