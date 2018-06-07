<?php

namespace CannaPlan\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
/**
 * @property int $id
 * @property string $amount_type
 * @property int $amount
 * @property string $start_date
 * @property string $deleted_at
 * @property string $remember_token
 * @property string $created_at
 * @property string $updated_at
 */
class Investment extends Model
{
    use SoftDeletes;
    protected $dates=['deleted_at'];
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'investment';

    /**
     * @var array
     */
    protected $fillable = ['amount_type', 'amount', 'start_date'];
    public function funds()
    {
        return $this->morphMany('CannaPlan\Models\Financing', 'fund');
    }
}
