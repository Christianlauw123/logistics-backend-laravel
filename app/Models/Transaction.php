<?php

namespace App\Models;

use App\Enums\Transactions\TransactionStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['trip_price_id', 'note', 'file_folder_id', 'driver_name', 'driver_id', 'file_sub_folder_id', 'file_provider', 'status', 'customer_id', 'vehicle_id', 'bank_account_id', 'origin_sub_district_id', 'dest_sub_district_id', 'user_id', 'trip_price_amount', 'origin_district', 'destination_district', 'dest_address', 'customer_name', 'vehicle_plate', 'bank_account_num', 'do_number', 'do_date', 'do_actual_date', 'vehicle_type', 'vehicle_capacity', 'transaction_capacity', 'transaction_items', 'deleted_at'])]
class Transaction extends Model
{
    use HasUuids, SoftDeletes;
    protected $keyType = 'string';
    public $incrementing = false;

    protected $casts = [
        'deleted_at'  => 'datetime',
        'status' => TransactionStatus::class,
    ];

    public function driver(): BelongsTo{
        return $this->belongsTo(Driver::class);
    }

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

    public function transactionDetails(): HasMany{
        return $this->hasMany(TransactionDetail::class);
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

    public function getDistrictLabelAttribute(SubDistrict $subDistrict)
    {
        return $subDistrict?->name . ', ' .$subDistrict?->district?->name;
    }

}
