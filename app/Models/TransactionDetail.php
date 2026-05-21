<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['amount', 'note', 'purpose', 'status', 'transaction_id', 'deleted_at'])]
class TransactionDetail extends Model
{
    use HasUuids, SoftDeletes;
    protected $keyType = 'string';
    public $incrementing = false;

    protected $casts = [
        'deleted_at'  => 'datetime',
    ];

    public function transaction(): BelongsTo{
        return $this->belongsTo(Transaction::class);
    }

    public function attachment(): HasOne{
        return $this->hasOne(Attachment::class);
    }

    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }
}
