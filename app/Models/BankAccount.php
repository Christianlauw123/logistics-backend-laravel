<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['bankName', 'accountIdentifierNumber', 'accountNumber', 'accountName', 'deleted_at'])]
class BankAccount extends Model
{
    use HasUuids;
    protected $keyType = 'string';
    public $incrementing = false;

    public function transactions(): HasMany {
        return $this->hasMany(Transaction::class);
    }
}



