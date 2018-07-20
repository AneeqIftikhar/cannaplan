<?php

namespace CannaPlan\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

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
    use SoftDeletes;
    protected $dates=['deleted_at'];
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'unit_sale';

    public static function boot()
    {
        parent::boot();

        // create a event to happen on saving
        static::creating(function ($table) {
            $table->created_by = Auth::user()->id;
        });
    }
    /**
     * @var array
     */
    protected $fillable = ['unit_sold', 'revenue_start_date', 'unit_price'];
    public function revenues()
    {
        return $this->morphMany('CannaPlan\Models\Revenue', 'revenuable');
    }
}
