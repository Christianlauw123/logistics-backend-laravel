<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Attachment;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class TransactionDetail extends Model
{
    public function transaction(): BelongsTo{
        return $this->belongsTo(Transaction::class);
    }

    public function attachment(): HasOne{
        return $this->hasOne(Attachment::class);
    }
}
