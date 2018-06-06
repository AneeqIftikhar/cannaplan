<?php

namespace CannaPlan\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property float $annual_interest
 * @property boolean $is_payable
 * @property string $start_date
 * @property string $deleted_at
 * @property string $remember_token
 * @property string $created_at
 * @property string $updated_at
 * @property Funding[] $fundings
 * @property Payment[] $payments
 */
class Other extends Model
{
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'other';

    /**
     * @var array
     */
    protected $fillable = ['annual_interest', 'is_payable', 'start_date', 'deleted_at', 'remember_token', 'created_at', 'updated_at'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function fundings()
    {
        return $this->hasMany('CannaPlan\Models\Funding');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function payments()
    {
        return $this->hasMany('CannaPlan\Models\Payment');
    }
}
