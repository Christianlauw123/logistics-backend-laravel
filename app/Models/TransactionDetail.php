<?php

namespace App\Models;

use App\Enums\TransactionDetails\TransactionDetailStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;

#[Fillable(['amount', 'note', 'purpose', 'status', 'transaction_id', 'transaction_detail_id', 'deleted_at', 'is_special_case', 'last_updated_by_id', 'user_id', 'amount_unique_number'])]
class TransactionDetail extends BaseModel
{
    use HasUuids, SoftDeletes, LogsActivity;
    protected $keyType = 'string';
    public $incrementing = false;

    protected $casts = [
        'deleted_at'  => 'datetime',
        'status' => TransactionDetailStatus::class,
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
