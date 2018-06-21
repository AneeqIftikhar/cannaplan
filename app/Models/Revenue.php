<?php

namespace CannaPlan\Models;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Auth;
Relation::morphMap([
    'unit_sale'=>'CannaPlan\Models\UnitSale',
    'billable'=>'CannaPlan\Models\Billable',
    'revenue_only'=>'CannaPlan\Models\RevenueOnly'
]);
/**
 * @property int $id
 * @property int $company_id
 * @property string $name
 * @property int $earning_id
 * @property string $earning_type
 * @property string $deleted_at
 * @property string $remember_token
 * @property string $created_at
 * @property string $updated_at
 * @property Company $company
 * @property CostOnRevenue[] $costOnRevenues
 * @property RevenueTax[] $revenueTaxes
 */
class Revenue extends Model
{
    use SoftDeletes;
    protected $dates=['deleted_at'];
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    public static function boot() {
        parent::boot();

        // create a event to happen on saving
        static::creating(function($table)  {
            $table->created_by = Auth::user()->id;
        });

    }
    protected $table = 'revenue';

    /**
     * @var array
     */
    protected $fillable = ['forecast_id', 'name', 'revenuable_id', 'revenuable_type'];

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
    public function costOnRevenues()
    {
        return $this->hasMany('CannaPlan\Models\CostOnRevenue');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function revenueTaxes()
    {
        return $this->hasMany('CannaPlan\Models\RevenueTax');
    }
    /* many to many relation*/
    public function taxes()
    {
        return $this->belongsToMany('CannaPlan\Models\Tax', 'revenue_tax',
            'revenue_id', 'tax_id');
    }

    public function revenuable()
    {
        return $this->morphTo();
    }
}
