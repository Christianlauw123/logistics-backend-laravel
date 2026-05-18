<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use App\Models\Transaction;
use App\Models\TransactionDetail;

class Attachment extends Model
{
    public function transaction(): BelongsTo {
        return $this->belongsTo(Transaction::class);
    }

    public function transaction_detail(): BelongsTo {
        return $this->belongsTo(TransactionDetail::class);
    }
}