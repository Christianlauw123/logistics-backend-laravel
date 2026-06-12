<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'deleted_at'])]
class Role extends BaseModel
{
    protected $casts = [
        'deleted_at'  => 'datetime',
    ];

    public function users(): HasMany {
        return $this->hasMany(User::class);
    }
}
