<?php

namespace App\Enums\Transactions;

enum TransactionStatus: string {
    case SUBMITTED = 'SUBMITTED';
    case APPROVED = 'APPROVED';
    case CANCELLED = 'CANCELLED';
    case CANCELLED_NO_REFUND = 'CANCELLED NO REFUND';
    case CANCELLED_AND_REFUND = 'CANCELLED AND REFUND';
    case DONE = 'DONE';
    case DONE_AND_WAITING_DOCUMENT = 'DONE AND WAITING_DOCUMENT';
    case REJECTED = 'REJECTED';

    // Goal: Updating details if Parent in this status
    public static function allowUpdates(): array{
        return [
            self::SUBMITTED->value,
            self::APPROVED->value
        ];
    }

    public function canTransitionTo(self $target): bool
    {
        return match ($this) {
            self::SUBMITTED  => in_array($target, [self::APPROVED, self::CANCELLED, self::CANCELLED_NO_REFUND, self::CANCELLED_AND_REFUND, self::DONE, self::DONE_AND_WAITING_DOCUMENT, self::REJECTED], true),
            self::APPROVED  => in_array($target, [self::CANCELLED, self::CANCELLED_NO_REFUND, self::CANCELLED_AND_REFUND, self::DONE, self::DONE_AND_WAITING_DOCUMENT, self::REJECTED], true),
            self::DONE, self::CANCELLED, self::CANCELLED_NO_REFUND, self::CANCELLED_AND_REFUND, self::DONE_AND_WAITING_DOCUMENT, self::REJECTED => false, // Cannot move anywhere
        };
    }
}
