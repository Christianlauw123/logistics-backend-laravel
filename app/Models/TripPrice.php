<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['base_price', 'customer_id', 'origin_sub_district_id', 'dest_sub_district_id', 'deleted_at'])]
class TripPrice extends Model
{
    use HasUuids;
    protected $keyType = 'string';
    public $incrementing = false;

    public function customer(): BelongsTo{
        return $this->belongsTo(Customer::class);
    }

    public function transactions(): HasMany{
        return $this->hasMany(Transaction::class);
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
