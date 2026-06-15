<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['name', 'plate_number', 'type', 'capacity', 'is_active', 'deleted_at', 'last_updated_by_id', 'user_id'])]
class Vehicle extends BaseModel
{
    protected $casts = [
        'deleted_at'  => 'datetime',
    ];

    public function transactions(): HasMany{
        return $this->hasMany(Transaction::class);
    }
}
