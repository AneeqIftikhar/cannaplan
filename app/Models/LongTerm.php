<?php

namespace CannaPlan\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
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
    use SoftDeletes;
    protected $dates=['deleted_at'];
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'long_term';

    /**
     * @var array
     */
    protected $fillable = ['year', 'will_sell', 'selling_amount', 'selling_date'];
    public function asset_durations()
    {
        return $this->morphMany('CannaPlan\Models\Asset', 'asset_duration');
    }
}
