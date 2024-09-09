<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Http\Resources\PatientCollection;
use Illuminate\Http\Resources\Json\JsonResource;

class ScheduleCollection extends JsonResource
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
            'status' => $this->status,
            'confirmation' => $this->confirmation,
            'scheduled_date' => $this->scheduled_date,
            'scheduled_time' => $this->scheduled_time,
            'patient_id' => $this->patient->id,
            'patient_name' => $this->patient->first_name . ' ' . $this->patient->last_name,
            'doctor_id' => $this->patient->doctor->id,
            'doctor_name' => $this->patient->doctor->first_name . ' ' . $this->patient->doctor->last_name,
            'insurance_id' => $this->patient->insurance->id,
            'insurance_name' => $this->patient->insurance->name
        ];
    }
}
