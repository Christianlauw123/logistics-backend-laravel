<?php

namespace App\Enums\TransactionDetails;

enum TransactionDetailStatus: string {
    case SUBMITTED = 'SUBMITTED';
    case APPROVED = 'APPROVED';
    case DONE = 'DONE';
    case CANCELLED = 'CANCELLED';
    case CANCELLED_FOR_REVISION = 'CANCELLED FOR REVISION';
    case REJECTED = 'REJECTED';

    // Goal: Get Transaction Detail that requested, to get the total of request that approved and done (which is tranferred)
    public static function approvedDefaults(): array{
        return [
            self::APPROVED,
            self::DONE
        ];
    }

    // Goal: Get Transaction Detail that requested, to get the total of requested
    public static function requestedDefaults(): array{
        return [
            self::SUBMITTED,
            self::APPROVED,
            self::DONE
        ];
    }

    public function canTransitionTo(self $target): bool
    {
        return match ($this) {
            self::SUBMITTED  => in_array($target, [self::APPROVED, self::DONE, self::CANCELLED, self::CANCELLED_FOR_REVISION, self::REJECTED], true),
            self::APPROVED  => in_array($target, [self::DONE, self::CANCELLED, self::CANCELLED_FOR_REVISION, self::REJECTED], true),
            self::DONE  => in_array($target, [self::CANCELLED, self::CANCELLED_FOR_REVISION, self::REJECTED], true),
            self::CANCELLED, self::CANCELLED_FOR_REVISION, self::REJECTED => false, // Cannot move anywhere
        };
    }
}

