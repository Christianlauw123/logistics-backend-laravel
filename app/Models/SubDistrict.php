<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;

#[Fillable(['name', 'deleted_at','district_id', 'last_updated_by_id', 'user_id'])]
class SubDistrict extends BaseModel
{
    protected $casts = [
        'deleted_at'  => 'datetime',
    ];

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

    public function revisionDestinationTransactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'revision_dest_sub_district_id');
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




