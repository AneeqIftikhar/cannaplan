<?php

namespace CannaPlan\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
/**
 * @property int $id
 * @property int $currency_id
 * @property int $user_id
 * @property string $title
 * @property string $business_stage
 * @property string $start_of_forecast
 * @property string $length_of_forecast
 * @property string $monthly_detail
 * @property string $deleted_at
 * @property string $remember_token
 * @property string $created_at
 * @property string $updated_at
 * @property Currency $currency
 * @property User $user
 * @property Forecast[] $forecasts
 * @property Pitch[] $pitches
 * @property Plan[] $plans
 * @property Revenue[] $revenues
 */
class Company extends Model
{
    use SoftDeletes;
    protected $dates=['deleted_at'];
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'company';

    /**
     * @var array
     */
    protected $fillable = ['currency_id', 'user_id', 'title', 'business_stage', 'start_of_forecast', 'length_of_forecast', 'monthly_detail'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function currency()
    {
        return $this->belongsTo('CannaPlan\Models\Currency');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('CannaPlan\Models\User');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function forecasts()
    {
        return $this->hasMany('CannaPlan\Models\Forecast');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function pitches()
    {
        return $this->hasMany('CannaPlan\Models\Pitch');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function plans()
    {
        return $this->hasMany('CannaPlan\Models\Plan');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function revenues()
    {
        return $this->hasMany('CannaPlan\Models\Revenue');
    }
}
