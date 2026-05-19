<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['bank_name', 'account_identifier_number', 'account_number', 'account_name', 'deleted_at'])]
class BankAccount extends Model
{
    use HasUuids, SoftDeletes;
    protected $keyType = 'string';
    public $incrementing = false;

    public function transactions(): HasMany {
        return $this->hasMany(Transaction::class);
    }
}
