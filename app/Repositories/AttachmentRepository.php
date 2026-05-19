<?php

namespace App\Repositories;

use App\Models\Attachment;

class AttachmentRepository
{
    public function findByIdOrFail(string $id): Attachment
    {
        return Attachment::findOrFail($id);
    }

    public function create(array $data): Attachment
    {
        return Attachment::create($data);
    }

    public function update(Attachment $attachment, array $data): Attachment
    {
        $attachment->update($data);
        return $attachment->refresh();
    }

    public function updateStatus(Attachment $attachment, string $status): Attachment
    {
        $attachment->update(['status' => $status]);

        return $attachment->refresh();
    }

    public function delete(Attachment $attachment): void
    {
        $attachment->update(['deleted_at' => now()]);
    }
}
