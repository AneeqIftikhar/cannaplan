<?php

namespace CannaPlan\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $revenue_id
 * @property int $tax_id
 * @property string $deleted_at
 * @property string $remember_token
 * @property string $created_at
 * @property string $updated_at
 * @property Revenue $revenue
 * @property Tax $tax
 */
class RevenueTax extends Model
{
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'revenue_tax';

    /**
     * @var array
     */
    protected $fillable = ['revenue_id', 'tax_id', 'deleted_at', 'remember_token', 'created_at', 'updated_at'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function revenue()
    {
        return $this->belongsTo('CannaPlan\Models\Revenue');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function tax()
    {
        return $this->belongsTo('CannaPlan\Models\Tax');
    }
}
