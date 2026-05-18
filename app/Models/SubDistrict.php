<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Models\District;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubDistrict extends Model
{
    public function district(): BelongsTo {
        return $this->belongsTo(District::class);
    }

    public function trans_origins(): HasMany{
        return $this->hasMany(Transaction::class, 'origin_sub_district_id');
    }

    public function trans_destinations(): HasMany{
        return $this->hasMany(Transaction::class, 'dest_sub_district_id');
    }
}
