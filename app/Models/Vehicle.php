<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

use App\Models\Transaction;

class Vehicle extends Model
{
    public function transactions(): HasMany{
        return $this->hasMany(Transaction::class);
    }
}
