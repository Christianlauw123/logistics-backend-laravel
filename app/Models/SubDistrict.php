<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'deleted_at'])]
class SubDistrict extends Model
{
    use HasUuids;
    protected $keyType = 'string';
    public $incrementing = false;

    public function district(): BelongsTo {
        return $this->belongsTo(District::class);
    }

    public function originTransactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'origin_sub_district_id');
    }

    public function destinationTransactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'dest_sub_district_id');
    }

    // Named relations — same table, two different roles
    public function originTripPrices(): HasMany
    {
        return $this->hasMany(TripPrice::class, 'origin_sub_district_id');
    }

    public function destinationTripPrices(): HasMany
    {
        return $this->hasMany(TripPrice::class, 'dest_sub_district_id');
    }
}




