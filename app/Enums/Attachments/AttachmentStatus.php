<?php

namespace App\Enums\Attachments;

enum AttachmentStatus: string {
    case PENDING = 'PENDING';
    case VERIFIED = 'VERIFIED';
    case REJECTED = 'REJECTED';

    public function canTransitionTo(self $target): bool
    {
        return match ($this) {
            self::PENDING  => in_array($target, [self::VERIFIED, self::REJECTED], true),
            self::VERIFIED, self::REJECTED => false, // Cannot move anywhere
        };
    }
}
