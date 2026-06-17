<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

#[Fillable(['name', 'email', 'password', 'role_id', 'deleted_at', 'last_updated_by_id', 'user_id'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, HasUuids, SoftDeletes, LogsActivity;

    protected $keyType = 'string';
    public $incrementing = false;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'deleted_at'  => 'datetime',
        ];
    }

    // Logs Activity
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

    protected static function booted(): void
    {
        parent::booted();

        static::creating(function ($model) {
            if (Auth::check()) {
                $model->setAttribute('user_id', Auth::id());
            }
        });

        static::updating(function ($model) {
            if (Auth::check()) {
                $model->setAttribute('last_updated_by_id', Auth::id());
            }
        });

        static::deleting(function ($model) {
            if (Auth::check()) {
                $model->setAttribute('last_updated_by_id', Auth::id());
                if (method_exists($model, 'isForceDeleting') && !$model->isForceDeleting()) {
                    $model->saveQuietly();
                }
            }
        });
    }
    // End of Logs Activity

    public function transactions(): HasMany {
        return $this->hasMany(Transaction::class);
    }

    public function lastUpdatedByTransactions(): hasMany{
        return $this->hasMany(Transaction::class, 'last_updated_by_id');
    }

    public function role(): BelongsTo {
        return $this->belongsTo(Role::class);
    }

    public function transactionDetails(): HasMany {
        return $this->hasMany(TransactionDetail::class);
    }

    public function lastUpdatedByTransactionDetails(): hasMany{
        return $this->hasMany(TransactionDetail::class, 'last_updated_by_id');
    }

    public function attachments(): HasMany {
        return $this->hasMany(Attachment::class);
    }
}
