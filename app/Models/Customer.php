<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['name', 'phone', 'address', 'deleted_at'])]
class Customer extends Model
{
    use HasUuids, SoftDeletes;
    protected $keyType = 'string';
    public $incrementing = false;

    public function transactions(): HasMany {
        return $this->hasMany(Transaction::class);
    }

    public function tripPrices(): HasMany {
        return $this->hasMany(TripPrice::class);
    }
}
