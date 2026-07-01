<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['name', 'deleted_at', 'last_updated_by_id', 'user_id', 'is_active'])]
class Driver extends BaseModel
{
    protected $casts = [
        'deleted_at'  => 'datetime',
        'is_active' => 'boolean',
    ];

    public function transactions(): HasMany {
        return $this->hasMany(Transaction::class);
    }
}
