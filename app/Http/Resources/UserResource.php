<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\ParentChildRelation;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $roles = [
            1 => 'Admin',
            3 => 'User',
        ];

        $data = [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'dob' => $this->dob,
            'profile_photo' => $this->profile_photo ? asset($this->profile_photo) : null,
            'phone_no' => $this->phone_no,
            'role' => $roles[$this->role] ?? 'Unknown',
            'status' => $this->status,
            'device_type' => $this->device_type,
            'device_id' => $this->device_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];



        return $data;
    }
}
