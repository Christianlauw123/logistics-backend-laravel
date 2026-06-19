<?php

namespace App\Services;

use App\Repositories\ActivityRepository;
use Illuminate\Support\Str;

class ActivityService
{
    public function __construct(
        private readonly ActivityRepository $activityRepository
    ) {}

    /**
     * Get logs depending on user permissions.
     */
    public function getTransactionHistory(string $transactionId): array
    {
        $logs = $this->activityRepository->getLogsByTransactionId($transactionId);
        return $this->reformatResults($logs);
    }

    public function getTransactionDetailHistory(string $transactionId): array
    {
        $logs = $this->activityRepository->getLogsTransactionDetailsByTransactionId($transactionId);

        return $this->reformatResults($logs);

    }

    private function reformatResults($logs){
        $datas = $logs
            ->filter(function ($log) {
                // 1. Run a quick check: If it's an update but no attributes changed, skip it!
                $event = strtolower($log->event ?? $log->description ?? 'update');
                if ($event === 'updated') {
                    $properties = $log->attribute_changes ?? $log->properties->toArray() ?? [];
                    $attributes = $properties['attributes'] ?? [];
                    unset($attributes['updated_at']); // ignore timestamp updates

                    foreach (array_keys($attributes) as $key) {
                        if (str_ends_with(strtolower($key), '_id') || strtolower($key) === 'id') {
                            unset($attributes[$key]);
                        }
                    }
                    return !empty($attributes); // Keep only if there are real changes
                }
                return true; // Always keep creates and deletes
            })
            ->map(function ($log) {
                // 2. Map your clean logs exactly like before
                $event = strtolower($log->event ?? $log->description ?? 'update');
                $action = match($event) {
                    'created' => 'create',
                    'updated' => 'update',
                    'deleted' => 'delete',
                    default   => $event
                };

                $effect = '-';
                if ($action === 'update') {
                    $properties = $log->attribute_changes ?? $log->properties->toArray() ?? [];
                    $attributes = $properties['attributes'] ?? [];
                    $old        = $properties['old'] ?? [];
                    unset($attributes['updated_at'], $old['updated_at']);

                    $changeLines = [];
                    foreach ($attributes as $key => $newValue) {
                        // 💡 NEW: Skip formatting if the property tracks an ID system
                        if (str_ends_with(strtolower($key), '_id') || strtolower($key) === 'id') {
                            continue;
                        }

                        $fieldName = Str::headline($key);
                        $oldValue = $old[$key] ?? '-';
                        $changeLines[] = "{$fieldName}: \"{$oldValue}\" -> \"{$newValue}\"";
                    }
                    $effect = implode("\n", $changeLines);
                } elseif ($action === 'create') {
                    $effect = "Record initialized";
                } elseif ($action === 'delete') {
                    $effect = "Record removed from active state";
                }

                return [
                    'state'    => $log->created_at->diffForHumans(),
                    'eventTime'=> $log->created_at->timezone('Asia/Jakarta')->format('Y-m-d H:i'),
                    'wodunnit' => $log->causer?->name ?? 'System',
                    'effect'   => $effect,
                    'action'   => $action,
                ];
            })
            ->values() // 3. Reset array keys back to 0, 1, 2...
        ->toArray();
        return [
            'count' => $logs->count(), // Handy reference of total log entries
            'data' => $datas            // Returns a flat array of all entries
        ];
    }
}
