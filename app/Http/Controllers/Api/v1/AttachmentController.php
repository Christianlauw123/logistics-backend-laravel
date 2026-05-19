<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;

use App\Http\Requests\Attachment\UpdateAttachmentStatusRequest;
use App\Http\Requests\Attachment\StoreAttachmentRequest;

use App\Http\Resources\AttachmentResource;
use App\Services\AttachmentService;
use Illuminate\Http\JsonResponse;

class AttachmentController extends Controller
{
    public function __construct(
        private readonly AttachmentService $attachmentService,
    ) {}

    /**
     * POST /api/v1/Attachments
     */
    public function store(StoreAttachmentRequest $request): AttachmentResource
    {
        return new AttachmentResource(
            $this->attachmentService->create(
                $request->validated(),
                $request->user()?->id,
            )
        );
    }

    /**
     * GET /api/v1/attachments/{attachment}
     */
    public function show(string $attachment): AttachmentResource
    {
        return new AttachmentResource(
            $this->attachmentService->findOrFail($attachment)
        );
    }

    /**
     * PATCH /api/v1/attachments/{attachment}/status
     */
    public function updateStatus(UpdateAttachmentStatusRequest $request, string $attachment): AttachmentResource
    {
        return new AttachmentResource(
            $this->attachmentService->changeStatus($attachment, $request->validated('status'))
        );
    }

    /**
     * DELETE /api/v1/attachments/{attachment}
     */
    public function destroy(string $attachment): JsonResponse
    {
        $this->attachmentService->delete($attachment);

        return response()->json(['message' => 'Attachment deleted.']);
    }
}
