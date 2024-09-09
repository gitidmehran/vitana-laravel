<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\DoctorResource;
use App\Http\Resources\InsuranceResource;

class PatientCollection extends JsonResource
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
            'first_name' => $this->first_name . $this->last_name,
            'last_name' => $this->last_name,
            'doctor' => new DoctorResource($this->doctor),
            'insurance' => new InsuranceResource($this->insurance)
        ];
    }
}
