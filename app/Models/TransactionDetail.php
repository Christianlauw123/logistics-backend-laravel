<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['amount', 'note', 'purpose', 'status', 'transaction_id', 'deleted_at'])]
class TransactionDetail extends Model
{
    use HasUuids;
    protected $keyType = 'string';
    public $incrementing = false;

    public function transaction(): BelongsTo{
        return $this->belongsTo(Transaction::class);
    }

    public function attachment(): HasOne{
        return $this->hasOne(Attachment::class);
    }
}
