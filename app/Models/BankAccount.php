<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['bank_name', 'account_identifier_number', 'account_number', 'account_name', 'deleted_at', 'last_updated_by_id', 'user_id'])]
class BankAccount extends BaseModel
{

    protected $casts = [
        'deleted_at'  => 'datetime',
    ];

    public function transactions(): HasMany {
        return $this->hasMany(Transaction::class);
    }
}
