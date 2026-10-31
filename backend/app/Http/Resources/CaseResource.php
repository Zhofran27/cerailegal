<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CaseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'case_number' => $this->case_number,
            'reason' => $this->reason,
            'status' => $this->status,
            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                ];
            }),
            'court' => new CourtResource($this->whenLoaded('court')),
            'spouse_detail' => $this->whenLoaded('spouseDetail', function () {
                return [
                    'id' => $this->spouseDetail->id,
                    'full_name' => $this->spouseDetail->full_name,
                    'nik' => $this->spouseDetail->nik,
                    'birth_date' => $this->spouseDetail->birth_date?->format('Y-m-d'),
                    'birth_place' => $this->spouseDetail->birth_place,
                    'gender' => $this->spouseDetail->gender,
                    'religion' => $this->spouseDetail->religion,
                    'occupation' => $this->spouseDetail->occupation,
                    'phone' => $this->spouseDetail->phone,
                    'address' => $this->spouseDetail->address,
                ];
            }),
            'marriage_detail' => $this->whenLoaded('marriageDetail', function () {
                return [
                    'id' => $this->marriageDetail->id,
                    'marriage_date' => $this->marriageDetail->marriage_date?->format('Y-m-d'),
                    'marriage_certificate_number' => $this->marriageDetail->marriage_certificate_number,
                    'kua_or_civil_registry' => $this->marriageDetail->kua_or_civil_registry,
                    'has_children' => (bool) $this->marriageDetail->has_children,
                    'children_count' => (int) $this->marriageDetail->children_count,
                    'separation_date' => $this->marriageDetail->separation_date?->format('Y-m-d'),
                    'last_address' => $this->marriageDetail->last_address,
                ];
            }),
            'documents' => DocumentResource::collection($this->whenLoaded('documents')),
            'status_logs' => $this->whenLoaded('statusLogs', function () {
                return $this->statusLogs->map(function ($log) {
                    return [
                        'id' => $log->id,
                        'status' => $log->status,
                        'notes' => $log->notes,
                        'changed_at' => $log->created_at?->toISOString(),
                    ];
                });
            }),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}

