<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['trip_price_amount', 'dest_address', 'customer_name', 'vehicle_plate', 'bank_account_num', 'do_number', 'do_date', 'do_actual_date', 'deleted_at'])]
class Transaction extends Model
{
    use HasUuids;
    protected $keyType = 'string';
    public $incrementing = false;

    public function customer(): BelongsTo{
        return $this->belongsTo(Customer::class);
    }

    public function tripPrice(): BelongsTo{
        return $this->belongsTo(TripPrice::class);
    }

    public function user(): BelongsTo{
        return $this->belongsTo(User::class);
    }

    public function vehicle(): BelongsTo{
        return $this->belongsTo(Vehicle::class);
    }

    public function bankAccount(): BelongsTo{
        return $this->belongsTo(BankAccount::class);
    }

    public function attachments(): HasMany{
        return $this->hasMany(Attachment::class);
    }

    public function originSubDistrict(): BelongsTo{
        return $this->belongsTo(SubDistrict::class, 'origin_sub_district_id');
    }

    public function destinationSubDistrict(): BelongsTo{
        return $this->belongsTo(SubDistrict::class, 'dest_sub_district_id');
    }
}
