<?php

namespace CannaPlan\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

/**
 * @property int $id
 * @property int $forecast_id
 * @property string $name
 * @property float $coorporate_tax
 * @property string $coorporate_payable_time
 * @property float $sales_tax
 * @property string $sales_payable_time
 * @property string $deleted_at
 * @property string $remember_token
 * @property string $created_at
 * @property string $updated_at
 * @property Forecast $forecast
 * @property RevenueTax[] $revenueTaxes
 */
class Tax extends Model
{
    use SoftDeletes;
    protected $dates=['deleted_at'];
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'tax';

    /**
     * @var array
     */
    protected $fillable = ['coorporate_tax', 'coorporate_payable_time', 'sales_tax', 'sales_payable_time'];
    protected $gaurded=['id' , 'forecast_id', 'created_by'];

    public static function boot()
    {
        parent::boot();

        // create a event to happen on saving
        static::creating(function ($table) {
            $table->created_by = Auth::user()->id;
        });

        static::deleting(function($table) {
            foreach ($table->revenueTaxes()->get() as $revenueTaxes) {
                $revenueTaxes->delete();
            }
        });
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function forecast()
    {
        return $this->belongsTo('CannaPlan\Models\Forecast');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function revenueTaxes()
    {
        return $this->hasMany('CannaPlan\Models\RevenueTax');
    }
    /* many to many relation*/
    public function revenues()
    {
        return $this->belongsToMany('CannaPlan\Models\Revenue', 'revenue_tax',
            'tax_id', 'revenue_id');
    }

    public static function getTaxByForecast($id)
    {

    }

}
