<?php

namespace App\Enums\Attachments;

enum AttachmentUploadStatus: string {
    case PENDING = 'PENDING';
    case COMPLETED = 'COMPLETED';
    case ERROR = 'ERROR';
}
