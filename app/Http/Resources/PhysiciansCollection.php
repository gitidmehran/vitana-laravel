<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

use Illuminate\Http\Resources\Json\JsonResource;

class PhysiciansCollection extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'mid_name' => $this->mid_name,
            'contact_no' => $this->contact_no,
            'gender' => $this->gender,
            'address' => $this->address,
            'speciality' => $this->speciality
        ];
    }
}
