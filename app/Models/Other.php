<?php

namespace CannaPlan\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

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
    use SoftDeletes;
    protected $dates=['deleted_at'];
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'other';

    /**
     * @var array
     */
    protected $fillable = ['annual_interest', 'is_payable', 'start_date'];

    public static function boot()
    {
        parent::boot();

        // create a event to happen on saving
        static::creating(function ($table) {
            $table->created_by = Auth::user()->id;
        });

        static::deleting(function ($table) {

            foreach ($table->fundings()->get() as $funding) {
                $funding->delete();
            }
            foreach ($table->payments()->get() as $payment) {
                $payment->delete();
            }
        });
    }

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
    public function funds()
    {
        return $this->morphMany('CannaPlan\Models\Financing', 'fundable');
    }
}
