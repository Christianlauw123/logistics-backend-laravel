<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['name', 'deleted_at'])]
class Role extends Model
{
    use HasUuids, SoftDeletes;
    protected $keyType = 'string';
    public $incrementing = false;

    public function users(): HasMany {
        return $this->hasMany(User::class);
    }
}
