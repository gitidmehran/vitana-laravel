<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Api\PatientsController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\Utility;



use App\Models\Patients;
use App\Models\Diagnosis;
use App\Models\SurgicalHistory;
use App\Models\User;
use App\Models\Questionaires;
use App\Models\Insurances;
use App\Models\Medications;
use App\Models\Clinic;
use App\Models\ClinicUser;
use App\Models\Programs;
use App\Models\CareGaps;

use Auth, Validator, DB;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;

class CareGapsController extends Controller
{
    protected $per_page = '';

    public function __construct(){
	    $this->per_page = Config('constants.perpage_showdata');
	}
    public function clinicData(){
        try{
        $clinicList = Clinic::select('id', 'name')->get()->toArray();
        $response = [
            'success' => true,
            'message' => 'Clinic Data Retrived Successfully',
            'clinic_list' => $clinicList
        ];
    } catch (\Exception $e) {
        $response = array('success' => false, 'message' => $e->getMessage(), 'line'=>$e->getLine());
    }

    return response()->json($response);

    } 
    public function storeBulkCareGaps(Request $request)
    {
        try {
                $validator = Validator::make($request->all(),
                    [
                        'clinicIds' => 'required',
                        'data'  => 'required',
                    ],
                    [
                        'clinicIds.required' => 'Please Select a Clinic.',
                        'data.required' => 'No data found to add in patients'
                    ]
                );

                if($validator->fails()) {
                    $error = $validator->getMessageBag()->first();
                    return response()->json(['success'=>false,'errors'=>$error]);
                };

                $clinic_id = $request->clinicIds;
                $data = $request->data;
                //return response()->json($data);
                $result = (new PatientsController)->storeBulkPatients($request, 1);
                
                //return response()->json($data);
                // return response()->json("Care Daps 121");


                    $valueCareGaps = [];
                    $bulktoStoreCareGaps = [];
                        foreach ($data as $key => $value) {
                            // print_r($data1);
                            // exit();
                            unset($value['s._no']);
                            unset($value['name']);
                    
                        
                        $valueCareGaps["member_id"] = @$value['member_id'] ?? '';
                        $valueCareGaps["high_risk"] ="";//$value["high_risk"];
                        $valueCareGaps["last_office_visit_(assigned_tin)"] = Carbon::now();//$value["last_office_visit_(assigned_tin)"] = date('Y-m-d', strtotime(@$value['last_office_visit_(assigned_tin)']));
                        $valueCareGaps["last_office_visit_(any_tin)"] = Carbon::now();//$value["last_office_visit_(any_tin)"] = date('Y-m-d', strtotime(@$value['last_office_visit_(any_tin)']));
                        $valueCareGaps["ed_visit"] = "";//$value["ed_visit"];
                        $valueCareGaps["ip_visit"] = @$value['inpatient_admits'] ?? '';

                        //new fields
                        $valueCareGaps["bpc"] = @$value['blood_pressure_control_for_patients_with_diabetes_-_blood_pressure_controlled_<140/90_mm_hg'] ?? '';
                        $valueCareGaps["hab1c8"] = @$value['hemoglobin_a1c_control_for_patients_with_diabetes_-_hba1c_control_(<8.0%)'] ?? '';
                        $valueCareGaps["hab1c9"] = @$value['hemoglobin_a1c_control_for_patients_with_diabetes_-_hba1c_poor_control_(>9.0%)'] ?? '';
                        $valueCareGaps["khe"] = @$value['kidney_health_evaluation_for_patients_with_diabetes_-_kidney_health_evaluation'] ?? '';
                    
                    
                    //end new fields
                        $patient_name = explode(', ', $value['patient_full_name']);
                        $patientLastName = strtoupper($patient_name['0']);
                        $patientFirstName = strtoupper($patient_name['1']);
                        
                        $patientDOB = date('Y-m-d', strtotime($value['dob']));
                        $existPatientData = Patients::where('last_name',$patientLastName)->where('first_name',$patientFirstName)->where('dob',$patientDOB)->get()->toArray();
                        //return response()->json($existPatientData[0]['id']);
                    
                        $valueCareGaps["hcc"] = "";//$value["hcc"];
                        $valueCareGaps["che"] = "";//$value["che"];

                        $valueCareGaps["iha"] = "";//$value["iha"];
                        $valueCareGaps["bcs"] = @$value['breast_cancer_screening'] ?? '';

                        $valueCareGaps["insurance_id"] = @$existPatientData[0]['insurance_id'] ?? NULL;
                        $valueCareGaps["clinic_id"]  = $clinic_id ;
                        $valueCareGaps["doctor_id"] =  @$existPatientData[0]['doctor_id'] ?? NULL;//@$doctor['id'] ?? NULL;

                        $valueCareGaps["cbp"] = @$value['controlling_high_blood_pressure'] ?? '';
                        $valueCareGaps["coa3"] = "";//$value["coa3"];

                        $valueCareGaps["coa2"] = "";//$value["coa2"];
                        $valueCareGaps["coa4"] = "";//$value["coa4"];

                        $valueCareGaps["col"] = @$value['colorectal_cancer_screening'] ?? '';
                        $valueCareGaps["cdc2"] = "";//$value["cdc2"];

                        $valueCareGaps["cdc4"] = @$value['eye_exam_for_patients_with_diabetes_-_eye_exam'] ?? '';
                        $valueCareGaps["trce"] = "";//$value["trce"];

                        $valueCareGaps["trcm"] = "";//$value["trcm"];
                        $valueCareGaps["created_user"] = Auth::id();
                        
                        $valueCareGaps["patient_id"] = @$existPatientData[0]['id'] ?? 1;

                        $valueCareGaps["created_at"] = Carbon::now();
                        $valueCareGaps["updated_at"] = Carbon::now();
                        $valueCareGaps["deleted_at"] = NULL;
                        // Care Gaps 
                        unset($value['member_id']);
                        unset($value['patient_full_name']);
                        unset($value['first_name']);
                        unset($value['last_name']);
                        unset($value['middle_name']);
                        unset($value['dob']);
                        unset($value['age']);
                        unset($value['gender']);
                        unset($value['patient_address_line_1']);
                        unset($value['city']);
                        unset($value['state']);
                        unset($value['zip_code']);
                        unset($value['phone']);
                        unset($value['cell']);
                        unset($value['email']);
                        unset($value['clinic']);
                        unset($value['primary_care_physician']);
                        unset($value['insurance']);

                        unset($value['pcp_npi']);
                        unset($value['pcp_name']);
                        unset($value['pcp_tax_id']);
                        unset($value['member_vbp_type']);
                        unset($value['emergency_room_visits']);
                        
                        unset($value['total_gaps']); 


                        unset($value['member_vbp_type']);
                        unset($value['kidney_health_evaluation_for_patients_with_diabetes_-_kidney_health_evaluation']);
                        unset($value['inpatient_admits']);
                        unset($value['hemoglobin_a1c_control_for_patients_with_diabetes_-_hba1c_poor_control_(>9.0%)']);
                        unset($value['hemoglobin_a1c_control_for_patients_with_diabetes_-_hba1c_control_(<8.0%)']);
                        unset($value['eye_exam_for_patients_with_diabetes_-_eye_exam']);
                        unset($value['emergency_room_visits']);
                        unset($value['controlling_high_blood_pressure']);
                        unset($value['breast_cancer_screening']);
                        unset($value['colorectal_cancer_screening']);
                        unset($value['clinic']);
                        unset($value['blood_pressure_control_for_patients_with_diabetes_-_blood_pressure_controlled_<140/90_mm_hg']);
                        


                        $bulktoStoreCareGaps[] = (array)$valueCareGaps;
                        
                    
                    }
                    //return response()->json($bulktoStoreCareGaps); 
                    if (!empty($bulktoStoreCareGaps)) {
                        $res = CareGaps::insert($bulktoStoreCareGaps);
                        $response = array('success'=>true,'message'=>'CareGaps Addedd successfully');
                    }
                    else {
                        $response = array('success'=>false,'message'=>'Duplicate CareGaps found');
                    }

            } catch (\Exception $e) {
                $response = array('success'=>false,'message'=>$e->getMessage(), 'line'=>$e->getLine());
            }

        return response()->json($response);
    }
 
 
 public function storeBulkCareGaps12(Request $request)
    {
        return response()->json($request->data);
        try {
            $validator = Validator::make($request->all(),
                [
                    'clinicIds' => 'required',
                    'data'  => 'required',
                ],
                [
                    'clinicIds.required' => 'Please Select a Clinic.',
                    'data.required' => 'No data found to add in patients'
                ]
            );

            if($validator->fails()) {
                $error = $validator->getMessageBag()->first();
                return response()->json(['success'=>false,'errors'=>$error]);
            };

            $clinic_id = $request->clinicIds;
            $data = json_decode(json_encode(array_filter($request->data)) ,true);

            $patientData = [];
            $caregarData = [];

            $this->storeBulkPatients($request);

            $doctorData = User::where('role', '21')->orWhere('role', '13')->whereRaw("FIND_IN_SET(?, clinic_id) > 0", [$clinic_id])->get()->toArray();
            $insurancesData = Insurances::where('type_id', '1')->where('clinic_id', $clinic_id)->get()->toArray();

            $allPatients = Patients::where('clinic_id', $clinic_id)->get()->toArray();
            $lastPatient = end($allPatients);

            $identity = '';
            $newidentity = '';
            if ($lastPatient) {
                $str = @$lastPatient['identity'] ?? "00000000";
                $a = +$str;
                $a = str_pad($a + 1, 8, '0', STR_PAD_LEFT);
                $identity = $a;
            } else {
                $identity = "00000000";
            }

            $bulktoStore = [];
            $valueCareGaps = [];
            $bulktoStoreCareGaps = [];
            foreach ($data as $key => $value) {
                //return response()->json($value);
                unset($value['s._no']);
                unset($value['name']);
                $memberId = @$value['member_id'] ?? "";
                unset($value['member_id']);
                
                // unset($value['member_vbp_type']);
                // unset($value['kidney_health_evaluation_for_patients_with_diabetes_-_kidney_health_evaluation']);
                // unset($value['inpatient_admits']);
                // unset($value['hemoglobin_a1c_control_for_patients_with_diabetes_-_hba1c_poor_control_(>9.0%)']);
                // unset($value['hemoglobin_a1c_control_for_patients_with_diabetes_-_hba1c_control_(<8.0%)']);
                // unset($value['eye_exam_for_patients_with_diabetes_-_eye_exam']);
                // unset($value['emergency_room_visits']);
                // unset($value['controlling_high_blood_pressure']);
                // unset($value['breast_cancer_screening']);
                // unset($value['colorectal_cancer_screening']);
                // unset($value['clinic']);
                // unset($value['blood_pressure_control_for_patients_with_diabetes_-_blood_pressure_controlled_<140/90_mm_hg']);
                
                $doctor_name = explode(', ', $value['primary_care_physician']);
                $lastName = strtoupper($doctor_name['0']);
                $firstName = strtoupper($doctor_name['1']);
                $doctor = array_filter($doctorData, function($item) use ($lastName, $firstName) {
                    return( strtoupper($item['last_name']) == $lastName && strtoupper($item['first_name']) == $firstName );
                });
                $doctor = reset($doctor);

                $patient_name = explode(', ', $value['patient_full_name']);
                $patientLastName = strtoupper($patient_name['0']);
                $patientFirstName = strtoupper($patient_name['1']);
                
                $patientDOB = date('Y-m-d', strtotime($value['dob']));

                $existPatientData = Patients::where('last_name',$patientLastName)->where('first_name',$patientFirstName)->where('dob',$patientDOB)->get()->toArray();
                

                $insuranceName = $value['insurance'];
                $insurance = array_filter($insurancesData, function($item) use ($insuranceName) {
                    return( strtoupper($item['name']) == strtoupper($insuranceName) || strpos(strtoupper($item['name']), strtoupper($insuranceName)) !== false );
                });
                if(empty($insurance)){
                    $insurance =  array();
                }
                //return response()->json($insurance);
                $insurance = reset($insurance);

                if ($newidentity) {
                    $str = $newidentity;
                    $a = +$str;
                    $a = str_pad($a + 1, 8, '0', STR_PAD_LEFT);
                    $identity = $a;
                }

                $unique_id = $value['last_name'].$value['first_name'].str_replace('/', '', $value['dob']);

                $duplicate_patient = array_filter($allPatients, function ($item) use ($unique_id) {
                    $exiting_patient_dob = str_replace('/', '', Carbon::parse($item['dob'])->format('m/d/Y'));
                    $exiting_patient_id = strtoupper($item['last_name']).strtoupper($item['first_name']).$exiting_patient_dob;
                    return ($item['unique_id'] === $unique_id || $unique_id === $exiting_patient_id);
                });

                $duplicate_patient = reset($duplicate_patient);

                // Break current otteration of loop if patient exist
                if ($duplicate_patient == true) {
                    continue;
                }
                
                
                $value["identity"] = $identity;
                $value["unique_id"] = $unique_id;
                $value["insurance_provide_pid"] = $memberId;
                $value["first_name"] = @$patientFirstName ?? "";
                $value["mid_name"] = @$value['middle_name'] ?? "";
                $value["last_name"] = @$patientLastName ?? "";
                $value["email"] = "";
                $value["contact_no"] = @$value['phone']?? NULL;
                $value["cell"] = @$value['cell']?? NULL;
                $value["doctor_id"] = @$doctor['id'] ?? NULL;
                $value["change_doctor_id"] = @$doctor['id'] ?? "";
                $value["insurance_id"] = @$insurance['id'] ?? NULL;
                $value["clinic_id"] = $clinic_id;
                $value["dob"] = date('Y-m-d', strtotime($value['dob']));
                $value["age"];
                $value["gender"];
                $value["address"] = @$value['patient_address_line_1'] ?? "";
                $value["change_address"] = @$value['address'] ?? "";
                $value["disease"] = "";
                $value["address_2"] = "";
                $value["city"];
                $value["state"];
                $value["zipCode"] = @$value['zip_code']?? NULL;
                $value["dod"] = NULL;
                $value["family_history"] = '[]';
                $value["social_history"] = NULL;
                $value["patient_consent"] = 0;
                $value["consent_data"] = "";
                $value["created_user"] = Auth::id();
                $value["created_at"] = Carbon::now();
                $value["updated_at"] = Carbon::now();
                $value["deleted_at"] = NULL;

                unset($value['insurance']);
                unset($value['middle_name']);
                unset($value['patient_address_line_1']);
                unset($value['patient_full_name']);
                unset($value['phone']);
                unset($value['primary_care_physician']);
                unset($value['zip_code']);



                // // Care Gaps 

                // unset($value['pcp_name']);
                // unset($value['pcp_npi']);
                // unset($value['pcp_tax_id']);
                // unset($value['pcp_tax_id_name']);
                
                // unset($value['total_gaps']); 


                // unset($value['member_vbp_type']);
                // //unset($value['kidney_health_evaluation_for_patients_with_diabetes_-_kidney_health_evaluation']);
                // unset($value['inpatient_admits']);
                // //unset($value['hemoglobin_a1c_control_for_patients_with_diabetes_-_hba1c_poor_control_(>9.0%)']);
                // //unset($value['hemoglobin_a1c_control_for_patients_with_diabetes_-_hba1c_control_(<8.0%)']);
                // //unset($value['eye_exam_for_patients_with_diabetes_-_eye_exam']);
                // unset($value['emergency_room_visits']);
                // //unset($value['controlling_high_blood_pressure']);
                // //unset($value['breast_cancer_screening']);
                // //unset($value['colorectal_cancer_screening']);
                // unset($value['clinic']);
                // //unset($value['blood_pressure_control_for_patients_with_diabetes_-_blood_pressure_controlled_<140/90_mm_hg']);
                

                $valueCareGaps["member_id"] = @$value['member_id'] ?? '';
                $valueCareGaps["high_risk"] ="";//$value["high_risk"];
                $valueCareGaps["last_office_visit_(assigned_tin)"] = Carbon::now();//$value["last_office_visit_(assigned_tin)"] = date('Y-m-d', strtotime(@$value['last_office_visit_(assigned_tin)']));
                $valueCareGaps["last_office_visit_(any_tin)"] = Carbon::now();//$value["last_office_visit_(any_tin)"] = date('Y-m-d', strtotime(@$value['last_office_visit_(any_tin)']));
                $valueCareGaps["ed_visit"] = "";//$value["ed_visit"];
                $valueCareGaps["ip_visit"] = @$value['inpatient_admits'] ?? '';

                //new fields
                $valueCareGaps["bpc"] = @$value['blood_pressure_control_for_patients_with_diabetes_-_blood_pressure_controlled_<140/90_mm_hg'] ?? '';
                $valueCareGaps["hab1c8"] = @$value['hemoglobin_a1c_control_for_patients_with_diabetes_-_hba1c_control_(<8.0%)'] ?? '';
                $valueCareGaps["hab1c9"] = @$value['hemoglobin_a1c_control_for_patients_with_diabetes_-_hba1c_poor_control_(>9.0%)'] ?? '';
                $valueCareGaps["khe"] = @$value['kidney_health_evaluation_for_patients_with_diabetes_-_kidney_health_evaluation'] ?? '';
               
               
               //end new fields
               
                $valueCareGaps["hcc"] = "";//$value["hcc"];
                $valueCareGaps["che"] = "";//$value["che"];

                $valueCareGaps["iha"] = "";//$value["iha"];
                $valueCareGaps["bcs"] = @$value['breast_cancer_screening'] ?? '';

                $valueCareGaps["insurance_id"] = @$insurance['id'] ?? NULL;
                $valueCareGaps["clinic_id"]  = $clinic_id ;
                $valueCareGaps["doctor_id"] =  @$doctor['id'] ?? NULL;

                $valueCareGaps["cbp"] = @$value['controlling_high_blood_pressure'] ?? '';
                $valueCareGaps["coa3"] = "";//$value["coa3"];

                $valueCareGaps["coa2"] = "";//$value["coa2"];
                $valueCareGaps["coa4"] = "";//$value["coa4"];

                $valueCareGaps["col"] = @$value['colorectal_cancer_screening'] ?? '';
                $valueCareGaps["cdc2"] = "";//$value["cdc2"];

                $valueCareGaps["cdc4"] = @$value['eye_exam_for_patients_with_diabetes_-_eye_exam'] ?? '';
                $valueCareGaps["trce"] = "";//$value["trce"];

                $valueCareGaps["trcm"] = "";//$value["trcm"];
                $valueCareGaps["created_user"] = Auth::id();
                $existPatientData = Patients::where('last_name',$patientLastName)->where('first_name',$patientFirstName)->where('dob',$patientDOB)->get()->toArray();
                //return response()->json($existPatientData[0]['id']);
                $valueCareGaps["patient_id"] = @$existPatientData[0]['id'] ?? NULL;

                $valueCareGaps["created_at"] = Carbon::now();
                $valueCareGaps["updated_at"] = Carbon::now();
                $valueCareGaps["deleted_at"] = NULL;
                // Care Gaps 

                unset($value['pcp_name']);
                unset($value['pcp_npi']);
                unset($value['pcp_tax_id']);
                unset($value['pcp_tax_id_name']);
                
                unset($value['total_gaps']); 


                unset($value['member_vbp_type']);
                unset($value['kidney_health_evaluation_for_patients_with_diabetes_-_kidney_health_evaluation']);
                unset($value['inpatient_admits']);
                unset($value['hemoglobin_a1c_control_for_patients_with_diabetes_-_hba1c_poor_control_(>9.0%)']);
                unset($value['hemoglobin_a1c_control_for_patients_with_diabetes_-_hba1c_control_(<8.0%)']);
                unset($value['eye_exam_for_patients_with_diabetes_-_eye_exam']);
                unset($value['emergency_room_visits']);
                unset($value['controlling_high_blood_pressure']);
                unset($value['breast_cancer_screening']);
                unset($value['colorectal_cancer_screening']);
                unset($value['clinic']);
                unset($value['blood_pressure_control_for_patients_with_diabetes_-_blood_pressure_controlled_<140/90_mm_hg']);
                


                $bulktoStore[] = (array)$value;
                $bulktoStoreCareGaps[] = (array)$valueCareGaps;
                $newidentity = $identity;
            }
return response()->json($data);
 
            // if(!empty($bulktoStoreCareGaps)) {
            //     $resCareGaps = CareGaps::insert($bulktoStoreCareGaps);
                
            // }
            if (!empty($bulktoStore)) {
                $res = Patients::insert($bulktoStore);
                          $CareGapsData = $this->CareGapsDataAdd($data,$clinic_id);
                //return response()->json("if 316".$CareGapsData);
                //$resCareGaps = CareGaps::insert($bulktoStoreCareGaps);
                $response = array('success'=>true,'message'=>'Patients Addedd successfully');
            }
            else {
                
                         $CareGapsData = $this->CareGapsDataAdd($data,$clinic_id);
               // return response()->json("else if 323".$CareGapsData);
                $response = array('success'=>false,'message'=>'Duplicate patients found');
            }

        } catch (\Exception $e) {
            $response = array('success'=>false,'message'=>$e->getMessage(), 'line'=>$e->getLine());
        }

        return response()->json($response);
    }
    





}