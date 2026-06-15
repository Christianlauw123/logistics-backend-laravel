<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['name', 'province'])]
class City extends BaseModel
{

    protected $casts = [
        'deleted_at'  => 'datetime',
    ];

    // public function districts(): HasMany {
    //     return $this->hasMany(District::class);
    // }
}
