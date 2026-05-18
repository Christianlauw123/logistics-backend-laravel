<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use App\Models\SubDistrict;

class District extends Model
{
    public function city(): BelongsTo {
        return $this->belongsTo(City::class);
    }

    public function sub_districts(): HasMany {
        return $this->hasMany(SubDistrict::class);
    }
}
