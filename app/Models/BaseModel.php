<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Illuminate\Support\Facades\Auth;

class BaseModel extends Model
{
    use HasUuids, SoftDeletes, LogsActivity;
    protected $keyType = 'string';
    public $incrementing = false;

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
}
