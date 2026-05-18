<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    public function transactions(): HasMany {
        return $this->hasMany(Transaction::class);
    }
}
