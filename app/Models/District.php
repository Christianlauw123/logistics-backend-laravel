<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['name', 'deleted_at', 'last_updated_by_id', 'user_id'])]
class District extends BaseModel
{

    protected $casts = [
        'deleted_at'  => 'datetime',
    ];

    // public function city(): BelongsTo {
    //     return $this->belongsTo(City::class);
    // }

    public function subDistricts(): HasMany {
        return $this->hasMany(SubDistrict::class);
    }
}
