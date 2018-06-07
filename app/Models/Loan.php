<?php

namespace CannaPlan\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
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
    use SoftDeletes;
    protected $dates=['deleted_at'];
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'loan';

    /**
     * @var array
     */
    protected $fillable = ['receive_date', 'receive_amount', 'interest_rate', 'interest_months'];
    public function funds()
    {
        return $this->morphMany('CannaPlan\Models\Financing', 'fund');
    }
}
