<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use App\Models\Customer;
use App\Models\TripPrice;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\BankAccount;
use App\Models\Attachment;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transaction extends Model
{
    public function customer(): BelongsTo{
        return $this->belongsTo(Customer::class);
    }

    public function trip_price(): BelongsTo{
        return $this->belongsTo(TripPrice::class);
    }

    public function user(): BelongsTo{
        return $this->belongsTo(User::class);
    }

    public function vehicle(): BelongsTo{
        return $this->belongsTo(Vehicle::class);
    }

    public function bank_account(): BelongsTo{
        return $this->belongsTo(BankAccount::class);
    }

    public function attachments(): HasMany{
        return $this->hasMany(Attachment::class);
    }

    public function origin_sub_district(): BelongsTo{
        return $this->belongsTo(SubDistrict::class, 'origin_sub_district_id');
    }

    public function dest_sub_district(): BelongsTo{
        return $this->belongsTo(SubDistrict::class, 'dest_sub_district_id');
    }
}