<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Patients;
use App\Models\PharmacistPatient;
use Auth;

class AssignmentController extends Controller
{
    protected $singular = "Assignment";
    protected $plural   = "Assignments";
    protected $action   = "/dashboard/assignment";
    protected $view     = "assignments.";
    public function patientAssignmentView(Request $request){
        $pharmacist = $requst->get('params');
        $alreadyAddedPatients = PharmacistPatient::all()->toArray();
        $patientIds = array_column($alreadyAddedPatients,'patient_id');
        $patient = Patients::withFilters($patientIds)->get()->toArray();
        $data = [
            'page_title' => 'Patient '.$this->singular,
            'action' => url($this->action.'/save-patient-assignment'),
            'pharmacist_id' => $pharmacist,
            'patients' =
        ];
    }
}
