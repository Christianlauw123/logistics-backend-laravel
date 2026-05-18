<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\District;
use Illuminate\Database\Eloquent\Relations\HasMany;

class City extends Model
{
    public function districts(): HasMany {
        return $this->hasMany(District::class);
    }
}
