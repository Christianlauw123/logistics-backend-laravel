<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;

#[Fillable(['base_price', 'customer_id', 'origin_sub_district_id', 'dest_sub_district_id', 'deleted_at', 'last_updated_by_id', 'user_id'])]
class TripPrice extends BaseModel
{

    protected $casts = [
        'deleted_at'  => 'datetime',
    ];

    public function customer(): BelongsTo{
        return $this->belongsTo(Customer::class);
    }

    public function transactions(): HasMany{
        return $this->hasMany(Transaction::class);
    }

    public function revisionTransactions(): HasMany{
        return $this->hasMany(Transaction::class, 'revision_trip_price_id');
    }

    // Named relations — same table, two different roles
    public function originSubDistrict(): BelongsTo
    {
        return $this->belongsTo(SubDistrict::class, 'origin_sub_district_id');
    }

    public function destinationSubDistrict(): BelongsTo
    {
        return $this->belongsTo(SubDistrict::class, 'dest_sub_district_id');
    }
}
