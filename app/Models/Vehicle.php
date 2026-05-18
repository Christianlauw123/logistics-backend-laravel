<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

#[Fillable(['name', 'plate_number', 'type', 'capacity', 'is_active', 'deleted_at'])]
class Vehicle extends Model
{
    use HasUuids;
    protected $keyType = 'string';
    public $incrementing = false;

    public function transactions(): HasMany{
        return $this->hasMany(Transaction::class);
    }
}
