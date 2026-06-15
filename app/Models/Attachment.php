<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['amount', 'file_url', 'file_id', 'file_provider', 'extracted_do_number', 'extracted_do_date', 'upload_status', 'upload_status_error', 'status', 'uploaded_at', 'transaction_id', 'transaction_detail_id', 'last_updated_by_id'])]
class Attachment extends BaseModel
{
    protected $casts = [
        'uploaded_at' => 'datetime',
        'deleted_at'  => 'datetime',
    ];

    public function transaction(): BelongsTo {
        return $this->belongsTo(Transaction::class);
    }

    public function transactionDetail(): BelongsTo {
        return $this->belongsTo(TransactionDetail::class);
    }

    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }
}
