<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'phone', 'address', 'deleted_at', 'last_updated_by_id', 'user_id'])]
class Customer extends BaseModel
{

    protected $casts = [
        'deleted_at'  => 'datetime',
    ];

    public function transactions(): HasMany {
        return $this->hasMany(Transaction::class);
    }

    public function tripPrices(): HasMany {
        return $this->hasMany(TripPrice::class);
    }
}
