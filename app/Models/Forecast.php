<?php

namespace CannaPlan\Models;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
/**
 * @property int $id
 * @property int $company_id
 * @property string $name
 * @property int $burden_rate
 * @property string $deleted_at
 * @property string $remember_token
 * @property string $created_at
 * @property string $updated_at
 * @property Company $company
 * @property Asset[] $assets
 * @property Cost[] $costs
 * @property Dividend[] $dividends
 * @property Expense[] $expenses
 * @property Financing[] $financings
 * @property Tax[] $taxes
 */
class Forecast extends Model
{
    use SoftDeletes;
    protected $dates=['deleted_at'];
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'forecast';

    /**
     * @var array
     */
    protected $fillable = ['company_id', 'name', 'burden_rate'];

    public static function boot() {
        parent::boot();

        // create a event to happen on saving
        static::creating(function($table)  {
            $table->created_by = Auth::user()->id;
        });

        static::deleting(function($forecast) {
            foreach ($forecast->revenues()->get() as $revenue) {
                $revenue->delete();
            }
            foreach ($forecast->assets()->get() as $asset) {
                $asset->delete();
            }
        });
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function company()
    {
        return $this->belongsTo('CannaPlan\Models\Company');
    }

    public function revenues()
    {
        return $this->hasMany('CannaPlan\Models\Revenue');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function assets()
    {
        return $this->hasMany('CannaPlan\Models\Asset');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function costs()
    {
        return $this->hasMany('CannaPlan\Models\Cost');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function dividends()
    {
        return $this->hasMany('CannaPlan\Models\Dividend');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function expenses()
    {
        return $this->hasMany('CannaPlan\Models\Expense');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function financings()
    {
        return $this->hasMany('CannaPlan\Models\Financing');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function taxes()
    {
        return $this->hasMany('CannaPlan\Models\Tax');
    }
}
