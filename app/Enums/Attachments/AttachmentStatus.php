<?php

namespace App\Enums\Attachments;

enum AttachmentStatus: string {
    case PENDING = 'PENDING';
    case VERIFIED = 'VERIFIED';
    case REJECTED = 'REJECTED';
}
