<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttachmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                    => $this->id,
            'amount'                => $this->amount,
            'file_url'              => $this->file_url,
            'extracted_do_number'   => $this->extracted_do_number,
            'extracted_do_date'     => $this->extracted_do_date,
            'upload_status'         => $this->upload_status,
            'upload_status_error'   => $this->upload_status_error,
            'status'                => $this->status,
            'uploaded_at'           => $this->uploaded_at,
            'created_at'            => $this->created_at->toDateTimeString(),
            'updated_at'            => $this->updated_at->toDateTimeString(),
            'deleted_at'            => $this->deleted_at->toDateTimeString(),
        ];
    }
}
