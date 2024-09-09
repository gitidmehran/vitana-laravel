<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Api\PatientsController;
use App\Http\Controllers\Api\CommonFunctionController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\Utility;
use App\Models\Patients;
use App\Models\Diagnosis;
use App\Models\SurgicalHistory;
use App\Models\User;
use App\Models\Questionaires;
use App\Models\Insurances;

use App\Models\CareGapsDetails;
use App\Models\Programs;
use App\Models\CareGaps;
use App\Models\HumanaCareGaps;
use App\Models\Clinic;
use App\Models\CareGapsComments;
use App\Models\InsuranceHistory;

use Auth, Validator, DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Schema\Blueprint;

class HumanaCareGapsController extends Controller
{
    protected $per_page = '';
    protected $directory = "/public/CareGapsDetailsFiles";

    public function __construct(){
	    $this->per_page = Config('constants.perpage_showdata');
	}
    public function index(Request $request)
    {    
        try{
            $doctor_id = $request->input("doctor_id") ?? '';
            $patient_id = $request->input("patient_id") ?? '';
            $clinic_id = $request->input("clinic_id") ?? '';
            $insurance_id = $request->input("insurance_id") ?? '';
            $month_no = $request->input("month") ?? '';

            $query = HumanaCareGaps::where('deleted_at',NULL);
            if(!empty($doctor_id)){
                $query->where('doctor_id',$doctor_id);
            }

            if(!empty($insurance_id)){
                $query->where('insurance_id',$insurance_id);
            }

            if(!empty($clinic_id) && count(explode(',', $clinic_id)) == 1 ){
                $query->where('clinic_id',$clinic_id);
            }

            if(!empty($patient_id)){
                $query->where('patient_id',$patient_id);
            }

            if(!empty($month_no))
            {
                $query->whereBetween('created_at', [Carbon::now()->subMonth($month_no), Carbon::now()] );
            }
            $careGapsData = $query->get();
            
            $response = [
                'success' => true,
                'message' => 'Data Retrived Successfully',
                'total_Records' => $careGapsData->count(),
                'careGapsData' => $careGapsData
            ];
        } catch (\Exception $e) {
            $response = array('success' => false, 'message' => $e->getMessage(), 'line'=>$e->getLine());
        }

        return response()->json($response);
    } 
    public function allComments(Request $request)
    {    
        try{ 
            $caregap_name = $request->input("caregap_name") ?? '';
            $patient_id = $request->input("patient_id") ?? '';
            $insurance_id = $request->input("insurance_id") ?? '';
            $caregap_id = $request->input("caregap_id") ?? '';
            $query = CareGapsComments::with('userinfo:id,first_name,mid_name,last_name')->where('caregap_name',$caregap_name);
            if(!empty($patient_id)){
                $patient_id = explode(',', $patient_id);
                $query = $query->where('caregap_id',$caregap_id);
            }
            if(!empty($caregap_id)) {
                $query = $query->where('caregap_id',$caregap_id);
            } 
            if(!empty($insurance_id)) {
                $query = $query->where('insurance_id',$insurance_id);
            }  
            $query = $query->get();
            $response = [
                'success' => true,
                'message' => 'Care Gaps Comments Retrived Successfully',
                'careGapsComments' => $query,
            ];
        } catch (\Exception $e) {
            $response = array('success' => false, 'message' => $e->getMessage(), 'line'=>$e->getLine());
        }

        return response()->json($response);
    } 
    public function clinicData(){
        
        try{
            $clinicList = Clinic::select('id', 'name')->get()->toArray();
            $insurances = Insurances::all();
                $insuranceList = [];
                foreach ($insurances as $key => $value) {
                    $insuranceList[$value->id] = $value->name;
                }
            $response = [
                'success' => true,
                'message' => 'Data Retrived Successfully',
                'insurances' => (object) $insuranceList,
                'clinic_list' => $clinicList
            ];
        } catch (\Exception $e) {
            $response = array('success' => false, 'message' => $e->getMessage(), 'line'=>$e->getLine());
        }

        return response()->json($response);
    } 
    public function insuranceHistory(Request $request)
    {
        $insurance_history = $request->insurance_history;
        $patientsData = json_decode($insurance_history, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return response()->json(['error' => 'Invalid JSON data'], 400);
        }
        if (is_array($patientsData)) {
            foreach ($patientsData as $patientData) {
                InsuranceHistory::create($patientData);
            }
            return $response = ['success'=>true, 'message'=> 'Insurance History Create Successfully!'];
        } else {
            return response()->json(['error' => 'Invalid JSON data format'], 400);
        }
    }
    public function storeBulkHumanaCareGaps(Request $request)
    {
        if(!empty($request->existingPatients) && !empty($request->newPatients)){
            $request['data'] =  array_merge($request->existingPatients,$request->newPatients);
        } elseif(empty($request->newPatients) && !empty($request->existingPatients)) {
            $request['data'] = $request->existingPatients;
        } elseif(empty($request->existingPatients) && !empty($request->newPatients)) {
            $request['data'] = $request->newPatients;
        } else{
            return "Nothing Change ";
        }
        $insurance_history = @$request->insurance_history;
        
        if(!empty($insuranceHistoryData)){
            $insuranceHistoryData = json_decode($insurance_history, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json(['error' => 'Invalid JSON data'], 400);
            }
            if (is_array($insuranceHistoryData)) {
                foreach ($insuranceHistoryData as $patientData) {
                    InsuranceHistory::create($patientData);
                }
                //return $response = ['success'=>true, 'message'=> 'Insurance History Create Successfully!'];
            } else {
                return response()->json(['error' => 'Invalid JSON data format'], 400);
            }
        }
        //return $request;
        //return "Rizwan";
        $currentDate = Carbon::now();
        $currentYear = $currentDate->year;
        try {
                $validator = Validator::make($request->all(),
                    [
                        'clinicIds' => 'required',
                        'data'  => 'required',
                        'gap_year'  => 'required',
                    ],
                    [
                        'clinicIds.required' => 'Please Select a Clinic.',
                        'gap_year.required' => 'Please Select a Gap Year.',
                        'data.required' => 'No data found to add in patients'
                    ]
                );

                if($validator->fails()) {
                    $error = $validator->getMessageBag()->first();
                    return response()->json(['success'=>false,'errors'=>$error]);
                };

                $clinic_id = $request->clinicIds;
                $gap_year = $request->gap_year;
                $insuranceIdFromUser = $request->insuranceIds;

                $fileLogId = @$request->fileLogId;

                $Result = (new CommonFunctionController)->patientsFileLogs($request, 1, $gap_year, $fileLogId)->getOriginalContent();
                
                
                $data = $request->data;
                // Check the last entry of existing data, as caregaps new sheet will be uploaded after 3 months
                $lastDataInsert = HumanaCareGaps::select('id','created_at')->where('source','like','%CareGap_File%')->where('gap_year', $gap_year)->orderBy('id', 'desc')->first();
                
                if(empty($lastDataInsert)){
                    $db_date =  Carbon::parse('1970-01-01 00:00:00.000000');
                }else{
                    $db_date =  $lastDataInsert->created_at;
                }


                $fromDate = Carbon::now();
                $today = Carbon::parse($fromDate);
                $months = $db_date->diffInMonths($today);

                if ($months <= 3){
                    $patientsResult = (new PatientsController)->storeBulkPatients($request, 1, $gap_year)->getOriginalContent();
                    $patientConcrollerResponse =  $patientsResult['message'];

                    $valueOfCareGaps = [];
                    $bulktoStoreCareGaps = [];
                    $scheduledAwv = [];

                    /* Fetching this insurance gap names from constants
                    ** Don't forget to add the new gap in this array if new gap is in system database */
                    $constantsCareGaps = Config('constants.caregaps.hum-001');

                    // checking the records of non excel created gaps to delete when creating the gaps from sheet
                    if ($gap_year == $currentYear) {
                        $currentYearGaps = HumanaCareGaps::where('gap_year', $currentYear)->where('source', '!=', 'CareGap_File')->delete();
                    }

                    // looping on Excel sheet data
                    foreach ($data as $key => $value) {
                        // $breastCancerGap = $colorectalCancerGap = $bpGap = $EyeExamGap = $HBA1CGap = $statinGap = "N/A";
                        
                        unset($value['s._no']);
                        unset($value['name']);

                        $member_id = @$value['member_id'] ?? '';
                        
                        // Logic to handle the patient with middle name attached with lastname 
                        if (strpos($value['last_name'], ' ') !== false) {
                            $patient_last_name = explode(' ', $value['last_name']);
                            
                            if (count($patient_last_name) > 1) {
                                array_pop($patient_last_name);
                            }

                            $value['last_name'] = implode(' ', $patient_last_name);
                        }

                        $unique_id = $value['last_name'].$value['first_name'].str_replace('/', '', $value['dob']);
                        
                        // getting Existing patients with Encounters if available
                        $existPatientData = Patients::with(['awvEncounter'])->where('patient_year', $gap_year)->where('member_id', $member_id)->orWhere('unique_id', $unique_id)->first();

                        if (!empty($existPatientData)) {
                            $existPatientData = $existPatientData->toArray();

                            // Patient Information to save in caregap table
                            $valueOfCareGaps["member_id"] = $member_id;
                            if(!empty($insuranceIdFromUser)){
                                $valueOfCareGaps["insurance_id"] = $insuranceIdFromUser;
                            } else {
                                $valueOfCareGaps["insurance_id"] = @$existPatientData['insurance_id'] ?? NULL;
                            }
                            
                            $valueOfCareGaps["gap_year"] = $gap_year;
                            $valueOfCareGaps["clinic_id"] = $clinic_id ;
                            $valueOfCareGaps["doctor_id"] =  @$existPatientData['doctor_id'] ?? NULL;
                            //$valueOfCareGaps["total_gaps"] = @$value['total_gaps'] ?? '';
                            $valueOfCareGaps["q_id"] = @$existPatientData['awv_encounter']['id'] ?? "";
                            $valueOfCareGaps["source"] = 'CareGap_File';
                            $valueOfCareGaps["created_user"] = Auth::id();
                            $valueOfCareGaps["patient_id"] = @$existPatientData['id'] ?? NULL;
                            $valueOfCareGaps["created_at"] = Carbon::now();
                            $valueOfCareGaps["updated_at"] = Carbon::now();
                            $valueOfCareGaps["deleted_at"] = NULL;

                            // Declared Variable for gaps
                            $breastCancerGap = $colorectalCancerGap = '';

                            // Checking Encounter for Breast Cancer & Colorectal Cancer gap
                            if (!empty($existPatientData['awv_encounter'])) {

                                // Decoding stringify object of encounter data
                                $encounterData = json_decode($existPatientData['awv_encounter']['questions_answers'], true);

                                // Screening section from AWV Encounter
                                $screening = @$encounterData['screening'] ?? "";
                                if(!empty($screening)){
                                    //Breast cancer Screening
                                    $mammogram = @$screening['mammogram_done'] ?? '';
                                    if($mammogram == 'Yes') {
                                        $mammogram_done_on = @$screening['mammogram_done_on'] ?? "";

                                        // Update gap status as per month criteria of Mommogram
                                        if(!empty($mammogram_done_on)){
                                            // Parse the date string using Carbon
                                            $currentDate = Carbon::now()->endOfMonth();
                                            $mammogramDate = Carbon::createFromFormat('m/Y', $mammogram_done_on)->endOfMonth();

                                            // Calculate the month difference
                                            $monthDifference = $currentDate->diffInMonths($mammogramDate);

                                            $breastCancerGap = $monthDifference <= 27 ? "Compliant" : "Non-Compliant";
                                        }else{
                                            $breastCancerGap = "Non-Compliant";
                                        }
                                    }else {
                                        $breastCancerGap = "Non-Compliant";
                                    }

                                    //Colon Cancer Screening
                                    $colonScreening = @$screening['colonoscopy_done'] ?? '';
                                    $testType = @$screening['colon_test_type'] ?? "";
                                    $colonDate = @$screening['colonoscopy_done_on'] ?? "";
                                    if ($colonScreening == 'Yes') {
                                        if (!empty($testType)) {
                                            if ($testType == 'Colonoscopy') {
                                                $currentDate = Carbon::now()->endOfMonth();
                                                $colonDate = Carbon::createFromFormat('m/Y', $colonDate)->endOfMonth();

                                                // Calculate the year difference
                                                $yearDifference = $currentDate->diffInYears($colonDate);
                                                
                                                // Updating gap status
                                                $colorectalCancerGap = $yearDifference <= 10 ? "Compliant" : "Non-Compliant";
                                            } elseif ($testType == 'FIT Test') {
                                                // getting year of current Date and Fit Date year
                                                $date1 = Carbon::now();
                                                $date1Year = (explode("-",$date1));
                                                $currentDateYear = $date1Year[0];
                                                $colonDateYears = (explode("/",$colonDate));
                                                $colonDateYear = $colonDateYears[1];


                                                //$colonDateYear = Carbon::parse($colonDate)->year();

                                                // Updating gap status
                                                $colorectalCancerGap = $currentDateYear == $colonDateYear ? "Compliant" : "Non-Compliant";
                                            } else if ($testType == 'Cologuard') {
                                                $currentDate = Carbon::now()->endOfMonth();
                                                $colonDate = Carbon::createFromFormat('m/Y', $colonDate)->endOfMonth();

                                                // Calculate the year difference
                                                $yearDifference = $currentDate->diffInYears($colonDate);

                                                // Updating gap status
                                                $colorectalCancerGap = $yearDifference <= 2 ? "Compliant" : "Non-Compliant";
                                            }
                                        }
                                    } else {
                                        $colorectalCancerGap = "Non-Compliant";
                                    }
                                }
                            }

                            /* Looping through the Gaps from Constant fetch above
                            ** Assigning gap values as per requirement 
                            ** In value variable of loop from excel we have 'current' word with gap name to read the current status of gap
                            ** only gap name represents the status from insurance column  */
                            foreach ($constantsCareGaps as $key => $gapname) {
                                $pendingVisit = [];
                                
                                // Creating gap name with '_insurance' to store value in database gap insurance column
                                $gapnameInsurance = $gapname.'_insurance';

                                // If no gap statuses are provided from excel sheet
                                if (!array_key_exists($gapname,$value)) {
                                    if ($gapname == 'breast_cancer_gap') {
                                        $valueOfCareGaps[$gapname] = !empty($breastCancerGap) ? $breastCancerGap : "Non-Compliant";
                                        $valueOfCareGaps[$gapnameInsurance] = "Non-Compliant";
                                    } elseif ($gapname == 'colorectal_cancer_gap') {
                                        $valueOfCareGaps[$gapname] = !empty($colorectalCancerGap) ? $colorectalCancerGap : "Non-Compliant";
                                        $valueOfCareGaps[$gapnameInsurance] = "Non-Compliant";
                                    } else {
                                        $valueOfCareGaps[$gapname] = "Non-Compliant";
                                        $valueOfCareGaps[$gapnameInsurance] = "Non-Compliant";
                                    }
                                } else {
                                    // creating gap name with '_current' to read current data from excel sheet and store in db column 
                                    $gapnameCurrent = $gapname.'_current';
        
                                    // Awv Gap has single Column in excel sheet, storing same status in both current and insurance column
                                    if ($gapname == "awv_gap") {
                                        $valueOfCareGaps[$gapname] = $value[$gapname];
                                        $valueOfCareGaps[$gapnameInsurance] = $value[$gapname];

                                        if ($value[$gapname] == 'Pending Visit' && !empty($value['awv_scheduled_date'])) {
                                            $pendingVisit['caregap_name'] = 'awv_gap';
                                            $pendingVisit['patient_id'] = $existPatientData['id'];
                                            $pendingVisit['caregap_id'] = "";
                                            $pendingVisit['insurance_id'] = $existPatientData['insurance_id'];
                                            $pendingVisit['clinic_id'] = $existPatientData['clinic_id'];
                                            $pendingVisit['caregap_details'] = json_encode(array('date'=> $value['awv_scheduled_date']));
                                            $pendingVisit['status'] = $value[$gapname];
                                            $pendingVisit["created_user"] = Auth::id();
                                            $pendingVisit["created_at"] = Carbon::now();
                                            $pendingVisit["updated_at"] = Carbon::now();
                                            $pendingVisit["deleted_at"] = NULL;

                                            $scheduledAwv[] = $pendingVisit;
                                        }

                                    } else {

                                        /* Proritizing Compliant status for Breast cancer and Colorectal Cancer gaps 
                                        ** Status checked from Encounter above*/
                                        if ($gapname == 'breast_cancer_gap') {

                                            /* if Current status is compliant from any source Excel or Encounter
                                            ** Gap status should be compliant
                                            ** Else whatever the status excel hold will be assigned */
                                            if ($breastCancerGap == 'Compliant' || $value[$gapnameCurrent] == 'Compliant') {
                                                $valueOfCareGaps[$gapname] = 'Compliant';
                                            } else {
                                                $valueOfCareGaps[$gapname] = @$value[$gapnameCurrent] ?? $value[$gapname] ?? 'N/A';
                                            }

                                            $valueOfCareGaps[$gapnameInsurance] = $value[$gapname] ?? 'N/A';
                                        } elseif ($gapname == 'colorectal_cancer_gap') {
                                            /* if Current status is compliant from any source Excel or Encounter
                                            ** Gap status should be compliant
                                            ** Else whatever the status excel hold will be assigned */
                                            if ($colorectalCancerGap == 'Compliant' || $value[$gapnameCurrent] == 'Compliant') {
                                                $valueOfCareGaps[$gapname] = 'Compliant';
                                            } else {
                                                $valueOfCareGaps[$gapname] = @$value[$gapnameCurrent] ?? $value[$gapname] ?? 'N/A';
                                            }

                                            $valueOfCareGaps[$gapnameInsurance] = $value[$gapname] ?? 'N/A';
                                        } else {
                                            // assign current status or insurance status as per availablity (if current status else insurance status)
                                            $valueOfCareGaps[$gapname] = @$value[$gapnameCurrent] ?? $value[$gapname] ?? 'N/A';

                                            // Assiging insurance status to gapname_insurance column
                                            $valueOfCareGaps[$gapnameInsurance] = $value[$gapname] ?? 'N/A';
                                        }
                                    }
                                }
                            }

                            // inserting single row array to main array to store in database
                            $bulktoStoreCareGaps[] = (array)$valueOfCareGaps;
                        }
                    }

                    if (!empty($bulktoStoreCareGaps)) {
                        $res = HumanaCareGaps::insert($bulktoStoreCareGaps);

                        // Checking for AWV gap if scheduled then inserting scheduled date in caregap details table
                        $insertedRows = [];
                        if ($res) {
                            // Get an instance of the model to access non-static methods
                            $modelInstance = new HumanaCareGaps();

                            // Retrieve the last inserted IDs
                            $lastInsertedId = $modelInstance->getConnection()->getPdo()->lastInsertId();

                            // Retrieve the count of the inserted rows
                            $numberOfInsertedRows = count($bulktoStoreCareGaps); // Assuming $values is the array passed to insert()
                        
                            // Retrieve the first inserted ID
                            $firstInsertedId = $modelInstance->orderBy('id', 'asc')->first()->id;

                            // Retrieve the last inserted ID
                            $lastInsertedId = $modelInstance->orderBy('id', 'desc')->first()->id;

                            // Generate an array containing all inserted IDs
                            $insertedIds = range($firstInsertedId, $lastInsertedId);
                        
                            // Retrieve the inserted rows by their IDs
                            $insertedRows = HumanaCareGaps::whereIn('id', $insertedIds)->get();
                        }

                        if (!empty($insertedRows)) {
                            $insertedRows = $insertedRows->toArray();
                            foreach ($scheduledAwv as $key => $value) {
                                $caregapid = array_filter($insertedRows, function($row) use ($value) {
                                    return ($row['patient_id'] == $value['patient_id'] && $row['awv_gap'] == 'Pending Visit');
                                });

                                $caregapid = reset($caregapid);

                                if (!empty($caregapid)) {
                                    $scheduledAwv[$key]['caregap_id'] = $caregapid['id'];
                                }
                            }
                        }

                        if (!empty($scheduledAwv)) {
                            CareGapsDetails::insert($scheduledAwv);
                        }

                        $response = array('success'=>true, 'scheduled'=> $scheduledAwv, 'message'=> 'CareGaps Added Successfully And '.$patientConcrollerResponse);
                    } else {
                        $response = array('success'=>false,'message'=>'Duplicate CareGaps found And '.$patientConcrollerResponse);
                    }
                } else {
                    $response = array('success'=>true,'message'=> 'Duplicate data found');
                }

                // $lastDataInsert = HumanaCareGaps::select('id','created_at')->where('source','like','%HumanaCareGap_File%')->orderBy('id', 'desc')->first();
                
                // if(empty($lastDataInsert)){
                //     $db_date =  Carbon::parse('1970-01-01 00:00:00.000000');
                // }else{
                //     $db_date =  $lastDataInsert->created_at;
                // }

                // $fromDate = Carbon::now();
                // $today = Carbon::parse($fromDate);
                // $months = $db_date->diffInMonths($today);

                // //return $months;
                // if($months <= 3) {
                //     $response = [
                //         'success' => true,
                //         'message'=>'You have already uploaded Care gaps within last 3 Months',
                //     ];
                //     return response()->json($response);
                // }

                // $patientsResult = (new PatientsController)->storeBulkPatients($request, 1)->getOriginalContent();

                // $patientConcrollerResponse =  $patientsResult['message'];
                // $duplicate_patients_ids = $patientsResult['duplicate_patient_IDs'];

                // $valueOfCareGaps = [];
                // $bulktoStoreHumanaCareGaps = [];
                // $missedPatients = [];
                
                // foreach ($data as $key => $value) {
                    
                //     $breastCancerGap = $colorectalCancerGap = $bpGap = $EyeExamGap = $HBA1CGap = $statinGap = "N/A";
                //     unset($value['s._no']);
                //     unset($value['name']);

                //     $member_id = @$value['member_id'] ?? '';
                    
                //     // Logic to handle the patient with middle name attached with lastname 
                //     if (strpos($value['last_name'], ' ') !== false) {
                //         $patient_last_name = explode(' ', $value['last_name']);
                        
                //         if (count($patient_last_name) > 1) {
                //             array_pop($patient_last_name);
                //         }

                //         $value['last_name'] = implode(' ', $patient_last_name);
                //     }
                //     $unique_id = $value['last_name'].$value['first_name'].str_replace('/', '', $value['dob']);
                    
                //     $existPatientData = Patients::with(['question'])->where('member_id', $member_id)->orWhere('unique_id', $unique_id)->get()->toArray();
                //    // return $existPatientData[0]['question'][0]['questions_answers'];
                    
                //     if (!empty($existPatientData)) {
                //         $valueOfCareGaps["member_id"] = $member_id;
                //         $questionaires_id = @$existPatientData[0]['question'][0]['id'] ?? NULL;
                //         $questionaireStatus = @$existPatientData[0]['question'][0]['status'];
                  
                //         $dateOfService =  @$existPatientData[0]['question'][0]['date_of_service'];
                //         $dateOfServiceYears = explode("-", $dateOfService);
                //         $dateOfServiceYear = $dateOfServiceYears[0];
                        
                //         if(!empty($questionaireStatus) && ($questionaireStatus = "Seen" || $questionaireStatus = "Seen pending" ) && $dateOfServiceYear == $currentYear ){
                //             $awvGap = 'Completed';
                //         }else{
                //             $awvGap ='Non-Completed';
                //         }
                //         $questionData =$existPatientData[0]['question'][0]['questions_answers'] ?? '';// 
                //         //$questionData = Questionaires::select('questions_answers')->with('patient')->latest('id')->first();
                        
                //         if(!empty($questionData)) {
                //             $check =  json_decode($questionData,true);

                //             $Colonoscopy = @$check['screening']['colonoscopy_done'];
                //             //Breast cancer starting
                //             $B_C_S = @$check['screening']['mammogram_done'] ?? '';
                //             $statin = @$check['cholesterol_assessment']['statin_prescribed'];
                //             $bp_assessment = @$check['bp_assessment'];
                //            // return $bp_assessment['bp_value'];
                //             $HBA1C= @$check['diabetes']['hba1c_value'];
                //             $EyeExam = @$check['diabetes']['diabetec_eye_exam']; 


                //             if($Colonoscopy == 'Yes'){
                //                 $colonoscopy_done_on = @$check['screening']['colonoscopy_done_on'];        
                //                 $year = explode("/", $colonoscopy_done_on);
                //                 $colonoscopy_done_on_year = $year[1];

                //                 if(!empty($colonoscopy_done_on_year)){
                //                     if(@$check['screening']['colon_test_type'] == 'Colonoscopy'){
                //                     // Add 9 years to the current date
                //                         $sub9Year = $currentDate->subYears(9);
                //                         $older9Year = $sub9Year->year;
                //                         $colorectalCancerGap = !empty($colonoscopy_done_on_year == $currentYear || $colonoscopy_done_on_year >=$older9Year ) ? 'Compliant' : "Non-Compliant";
        
                //                     }else if(@$check['screening']['colon_test_type'] == 'FIT Test'){
                //                         $colorectalCancerGap = !empty($colonoscopy_done_on_year == $currentYear ) ? 'Compliant' : "Non-Compliant";
        
                //                     }else if(@$check['screening']['colon_test_type'] == 'Cologuard'){
                //                         // Add 2 years to the current date
                //                         $sub2Year = $currentDate->subYears(2);
                //                         $older2Year = $sub2Year->year;
                //                         // return $colonoscopy_done_on_year;
                //                         $colorectalCancerGap = !empty($colonoscopy_done_on_year == $currentYear || $colonoscopy_done_on_year >= $older2Year) ? 'Compliant' : "Non-Compliant";
                //                     }
                //                 }else{
                //                     $colorectalCancerGap = "Non-Compliant";
                //                 }
                //                 // }else if(!empty($awvGapIsEmpty)){
                //                 //     $awvGapIsEmpty = $awvGapIsEmpty;
                //                 // }
    
    
                //             }
                            
                //             if(isset($B_C_S) && $B_C_S == 'Yes'){
                //                 $mammogram_done_on = $check['screening']['mammogram_done_on'];        
                //                 $year = explode("/", $mammogram_done_on);
                //                 $mammogram_done_on_year = $year[1];
                //                 if(!empty($mammogram_done_on_year)){
                //                     $breastCancerGap = !empty($mammogram_done_on_year ==  $currentYear) ? "Compliant" : "Non-Compliant";
                //                 }else{
                //                     $breastCancerGap = "Non-Compliant";
                //                 }
                //             }
                //             //return $breastCancerGap;

                //             if(!empty($bp_assessment)){
                //                 $bpValue = $bp_assessment['bp_value'];
                //                 $bpValueIS = explode("/",$bpValue);
    
                //                 $systolicBp = $bpValueIS[0];
                //                 $diastolicBp = $bpValueIS[1];
    
                //                 if ($systolicBp <140 && $diastolicBp <90) {
                //                     $bpGap =  "Compliant";
                //                 }else{
                //                     $bpGap =  "Non-Compliant";
                //                 }

                //             }

                //             if(!empty($EyeExamGap) && $EyeExam == 'Yes'){
                //                 $EyeExamGap = "Compliant";
                //             }

                //             if(!empty($HBA1C) && $HBA1C<=9){
                //                 $HBA1CGap =  "Compliant";
                //             }

                //             if(!empty($statin) && $statin == 'Yes'){ 
                //                 $statinGap =  "Compliant";
                            
                //             }

                //         } else {
                //             $check = NULL;
                //         }

                        
                //         if (@$value['breast_cancer_gap'] == 'Compliant' && @$value['breast_cancer_gap_current'] != 'Compliant') {
                //             $valueOfCareGaps["breast_cancer_gap_insurance"] = $valueOfCareGaps['breast_cancer_gap'] = 'Compliant';
                //             $value['breast_cancer_gap_current'] = 'Compliant';
                //         }
                //         //rizwan start work on breast_cancer_gap
                //         else if(@$value['breast_cancer_gap'] != 'Compliant'){
                //             $valueOfCareGaps["breast_cancer_gap"] = @$value['breast_cancer_gap_current'] ?? $breastCancerGap;
                //             $valueOfCareGaps["breast_cancer_gap_insurance"] = @$value['breast_cancer_gap'] ?? $breastCancerGap;
                //         }
                //         // rizwan end work on breast_cancer_gap
                //         // $valueOfCareGaps["breast_cancer_gap"] = @$value['breast_cancer_gap_current'] ?? 'N/A';
                //         // $valueOfCareGaps["breast_cancer_gap_insurance"] = @$value['breast_cancer_gap'] ?? 'N/A';
                        
                //         if (@$value['colorectal_cancer_gap'] == 'Compliant' && @$value['colorectal_cancer_gap_current'] != 'Compliant') {
                //             $valueOfCareGaps['colorectal_cancer_gap'] = $valueOfCareGaps["colorectal_cancer_gap_insurance"] = 'Compliant';
                //             $value['colorectal_cancer_gap_current'] = 'Compliant';
                //         }
                //         else if (@$value['colorectal_cancer_gap'] != 'Compliant' ){
                //             $valueOfCareGaps["colorectal_cancer_gap"] = @$value['colorectal_cancer_gap_current'] ?? $colorectalCancerGap;
                //             $valueOfCareGaps["colorectal_cancer_gap_insurance"] = @$value['colorectal_cancer_gap'] ?? $colorectalCancerGap;
        
                //         }
                //         // $valueOfCareGaps["colorectal_cancer_gap"] = @$value['colorectal_cancer_gap_current'] ?? 'N/A';
                //         // $valueOfCareGaps["colorectal_cancer_gap_insurance"] = @$value['colorectal_cancer_gap'] ?? 'N/A';
    
                //         if (@$value['high_bp_gap'] == 'Compliant' && @$value['high_bp_gap_current'] != 'Compliant') {
                //             $valueOfCareGaps["high_bp_gap"] = $valueOfCareGaps["high_bp_gap_insurance"] = 'Compliant';
                //             $value['high_bp_gap_current'] = 'Compliant';
                //         }
                //         else if (@$value['high_bp_gap'] != 'Compliant'){
                //             $valueOfCareGaps["high_bp_gap"] = @$value['high_bp_gap_current'] ?? $bpGap;
                //             $valueOfCareGaps["high_bp_gap_insurance"] = @$value['high_bp_gap'] ?? $bpGap;
                //         }

                //         // $valueOfCareGaps["high_bp_gap"] = @$value['high_bp_gap_current'] ?? 'N/A';
                //         // $valueOfCareGaps["high_bp_gap_insurance"] = @$value['high_bp_gap'] ?? 'N/A';
    
                //         if (@$value['eye_exam_gap'] == 'Compliant' && @$value['eye_exam_gap_current'] != 'Compliant') {
                //             $valueOfCareGaps["eye_exam_gap"] =  $valueOfCareGaps["eye_exam_gap_insurance"] = 'Compliant';
                //             $value['eye_exam_gap_current'] = 'Compliant';
                //         }
                //         else if (@$value['eye_exam_gap'] != 'Compliant' ){
                //             $valueOfCareGaps["eye_exam_gap"] = @$value['eye_exam_gap_current'] ?? $EyeExamGap;
                //             $valueOfCareGaps["eye_exam_gap_insurance"] = @$value['eye_exam_gap'] ?? $EyeExamGap;
                //         }
                //         // $valueOfCareGaps["eye_exam_gap"] = @$value['eye_exam_gap_current'] ?? 'N/A';
                //         // $valueOfCareGaps["eye_exam_gap_insurance"] = @$value['eye_exam_gap'] ?? 'N/A';
    
                //         if (@$value['faed_visit_gap'] == 'Compliant' && @$value['faed_visit_gap_current'] != 'Compliant') {
                //             $value['faed_visit_gap_current'] = 'Compliant';
                //         }
                //         $valueOfCareGaps["faed_visit_gap"] = @$value['faed_visit_gap_current'] ?? 'N/A';
                //         $valueOfCareGaps["faed_visit_gap_insurance"] = @$value['faed_visit_gap'] ?? 'N/A';
    
                //         if (@$value['hba1c_poor_gap'] == 'Compliant' && @$value['hba1c_poor_gap_current'] != 'Compliant') {
                //             $valueOfCareGaps["hba1c_poor_gap"] = $valueOfCareGaps["hba1c_poor_gap_insurance"] = 'Compliant';
                //             $value['hba1c_poor_gap_current'] = 'Compliant';
                //         }
                //         else if (@$value['hba1c_poor_gap'] != 'Compliant'){
                //             $valueOfCareGaps["hba1c_poor_gap"] = @$value['hba1c_poor_gap_current'] ?? $HBA1CGap;
                //             $valueOfCareGaps["hba1c_poor_gap_insurance"] = @$value['hba1c_poor_gap'] ?? $HBA1CGap;
                //         }
                //         // $valueOfCareGaps["hba1c_poor_gap"] = @$value['hba1c_poor_gap_current'] ?? 'N/A';
                //         // $valueOfCareGaps["hba1c_poor_gap_insurance"] = @$value['hba1c_poor_gap'] ?? 'N/A';
    
                //         if (@$value['omw_fracture_gap'] == 'Compliant' && @$value['omw_fracture_gap_current'] != 'Compliant') {
                //             $value['omw_fracture_gap_current'] = 'Compliant';
                //         }
                //         $valueOfCareGaps["omw_fracture_gap"] = @$value['omw_fracture_gap_current'] ?? 'N/A';
                //         $valueOfCareGaps["omw_fracture_gap_insurance"] = @$value['omw_fracture_gap'] ?? 'N/A';
    
                //         if (@$value['pc_readmissions_gap'] == 'Compliant' && @$value['pc_readmissions_gap_current'] != 'Compliant') {
                //             $value['pc_readmissions_gap_current'] = 'Compliant';
                //         }
                //         $valueOfCareGaps["pc_readmissions_gap"] = @$value['pc_readmissions_gap_current'] ?? 'N/A';
                //         $valueOfCareGaps["pc_readmissions_gap_insurance"] = @$value['pc_readmissions_gap'] ?? 'N/A';
    
                //         if (@$value['spc_disease_gap'] == 'Compliant' && @$value['spc_disease_gap_current'] != 'Compliant') {
                //             $value['spc_disease_gap_current'] = 'Compliant';
                //         }
                //         $valueOfCareGaps["spc_disease_gap"] = @$value['spc_disease_gap_current'] ?? 'N/A';
                //         $valueOfCareGaps["spc_disease_gap_insurance"] = @$value['spc_disease_gap'] ?? 'N/A';
    
                //         if (@$value['post_disc_gap'] == 'Compliant' && @$value['post_disc_gap_current'] != 'Compliant') {
                //             $value['post_disc_gap_current'] = 'Compliant';
                //         }
                //         $valueOfCareGaps["post_disc_gap"] = @$value['post_disc_gap_current'] ?? 'N/A';
                //         $valueOfCareGaps["post_disc_gap_insurance"] = @$value['post_disc_gap'] ?? 'N/A';
    
                //         if (@$value['after_inp_disc_gap'] == 'Compliant' && @$value['after_inp_disc_gap_current'] != 'Compliant') {
                //             $value['after_inp_disc_gap_current'] = 'Compliant';
                //         }
                //         $valueOfCareGaps["after_inp_disc_gap"] = @$value['after_inp_disc_gap_current'] ?? 'N/A';
                //         $valueOfCareGaps["after_inp_disc_gap_insurance"] = @$value['after_inp_disc_gap'] ?? 'N/A';
                        
                //         if (@$value['ma_cholesterol_gap'] == 'Compliant' && @$value['ma_cholesterol_gap_current'] != 'Compliant') {
                //             $valueOfCareGaps["ma_cholesterol_gap"] = $valueOfCareGaps["ma_cholesterol_gap_insurance"] = 'Compliant';
                //             $value['ma_cholesterol_gap_current'] = 'Compliant';
                //         }
                //         else if (@$value['ma_cholesterol_gap'] != 'Compliant'){
                //             $valueOfCareGaps["ma_cholesterol_gap"] = @$value['ma_cholesterol_gap_current'] ?? $statinGap;
                //             $valueOfCareGaps["ma_cholesterol_gap_insurance"] = @$value['ma_cholesterol_gap'] ?? $statinGap;
        
                //         }
                //         // $valueOfCareGaps["ma_cholesterol_gap"] = @$value['ma_cholesterol_gap_current'] ?? 'N/A';
                //         // $valueOfCareGaps["ma_cholesterol_gap_insurance"] = @$value['ma_cholesterol_gap'] ?? 'N/A';
    
                //         if (@$value['mad_medications_gap'] == 'Compliant' && @$value['mad_medications_gap_current'] != 'Compliant') {
                //             $value['mad_medications_gap_current'] = 'Compliant';
                //         }
                //         $valueOfCareGaps["mad_medications_gap"] = @$value['mad_medications_gap_current'] ?? 'N/A';
                //         $valueOfCareGaps["mad_medications_gap_insurance"] = @$value['mad_medications_gap'] ?? 'N/A';
    
                //         if (@$value['ma_hypertension_gap'] == 'Compliant' && @$value['ma_hypertension_gap_current'] != 'Compliant') {
                //             $value['ma_hypertension_gap_current'] = 'Compliant';
                //         }
                //         $valueOfCareGaps["ma_hypertension_gap"] = @$value['ma_hypertension_gap_current'] ?? 'N/A';
                //         $valueOfCareGaps["ma_hypertension_gap_insurance"] = @$value['ma_hypertension_gap'] ?? 'N/A';
    
                //         if (@$value['sup_diabetes_gap'] == 'Compliant' && @$value['sup_diabetes_gap_current'] != 'Compliant') {
                //             $value['sup_diabetes_gap_current'] = 'Compliant';
                //         }
                //         $valueOfCareGaps["sup_diabetes_gap"] = @$value['sup_diabetes_gap_current'] ?? 'N/A';
                //         $valueOfCareGaps["sup_diabetes_gap_insurance"] = @$value['sup_diabetes_gap'] ?? 'N/A';
    
                //         if(@$value['awv_gap'] != 'Compliant'){
                //             $valueOfCareGaps["awv_gap"] = @$value['awv_gap'] ?? $awvGap;
                //             $valueOfCareGaps["awv_gap_insurance"] = @$value['awv_gap'] ?? $awvGap;
                //         }else{
                //             $valueOfCareGaps["awv_gap"] = @$value['awv_gap'] ?? 'N/A';
                //             $valueOfCareGaps["awv_gap_insurance"] = @$value['awv_gap'] ?? 'N/A';
                //         }
    
                //         if (strpos($value['last_name'], ' ') !== false) {
                //             $patient_last_name = explode(' ', $value['last_name']);
                //             array_pop($patient_last_name);
                //             $value['last_name'] = implode(' ', $patient_last_name);
                //         }
                        
                //         if (!empty($existPatientData[0]['id'])) {
                //             $PID = $existPatientData[0]['id'];
    
                //             // $questionData = Questionaires::select('questions_answers')->where('patient_id',$PID)->latest('id')->first();
                //             // if (!empty($questionData)) {
                //             //     $check =  json_decode($questionData['questions_answers'],true);
                //             // } else {
                //             //     $check = NULL;
                //             // }
                //         }
    
                //         if(!empty($insuranceIdFromUser)) {
                //             $valueOfCareGaps["insurance_id"] = $insuranceIdFromUser;
                //         } else {
                //             $valueOfCareGaps["insurance_id"] = @$existPatientData[0]['insurance_id'] ?? NULL;
                //         }
                        
                        
                //         $valueOfCareGaps["gap_year"]        = $gap_year;
                //         $valueOfCareGaps["clinic_id"]       = $clinic_id ;
                //         $valueOfCareGaps["q_id"]            = @$questionaires_id ;
                //         $valueOfCareGaps["doctor_id"]       =  @$existPatientData[0]['doctor_id'] ?? NULL;
                //         $valueOfCareGaps["source"]          = 'HumanaCareGap_File';
                //         $valueOfCareGaps["created_user"]    = Auth::id();
                //         $valueOfCareGaps["patient_id"]      = @$existPatientData[0]['id'] ?? NULL;
                //         $valueOfCareGaps["created_at"]      = Carbon::now();
                //         $valueOfCareGaps["updated_at"]      = Carbon::now();
                //         $valueOfCareGaps["deleted_at"]      = NULL;
                        
                //         $bulktoStoreHumanaCareGaps[] = (array)$valueOfCareGaps;
                //     } else {
                //         $missedPatients[] = $whereClause;
                //     }
                // }

                // if (!empty($bulktoStoreHumanaCareGaps)) {
                //         $res = HumanaCareGaps::insert($bulktoStoreHumanaCareGaps);
                //         $response = [
                //             'success' => true,
                //             'message'=> 'HumanaCareGaps Added Successfully And '.$patientConcrollerResponse,
                //             'duplicate_patients_ids' => $duplicate_patients_ids,
                //             'missed_patients' => $missedPatients,
                //         ];
                    
                // } else {
                //     $response = [
                //         'success' => true,
                //         'message'=>'No data to insurt ',//Duplicate HumanaCareGaps found And '. $patientConcrollerResponse,
                //         // 'duplicate_patients_ids' => $duplicate_patients_ids,
                //         // 'missed_patients' => $missedPatients,
                //     ];
                // }
            } catch (\Exception $e) {
                $response = array('success'=>false,'message'=>$e->getMessage(), 'line'=>$e->getLine());
            }

        return response()->json($response);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'col_name'  => 'required',
            'col_value'  => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->getMessageBag()]);
        };
        
        $input = $validator->valid();
        $folder_name = '/HumanaCareGaps/';//$request->folder_name ?? "";
        
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $t=time();
            $time = date("His",$t);
            $date = date("Ymd",$t);
            $dateTime = $date.'_'.$time;
            $fileName = $id .'_'. $input['col_name'] . '_' . $dateTime . '_' . $file->getClientOriginalName();
            $filePath = $folder_name . $fileName;
            //Storage::disk('s3')->putFileAs($folder_name, $file, $fileName, 'public');
            Storage::disk('s3')->put($filePath, file_get_contents($file));
           
           $urlPath = Storage::disk('s3')->url($filePath);
           $input121['filename'] = $filePath;
        }
        
        $note = HumanaCareGaps::find($id);
        $setYear  = $request->filter_year ?? '';
        if ($id != 0) {
            $input121['caregap_details'] = $request->caregap_details;
            $input121['caregap_name'] = $request->col_name;
            $input121['caregap_id'] =  $id;
            $input121['created_user'] =  Auth::id();
            $input121['patient_id'] = $request->patient_id;
            $input121['insurance_id'] = $note->insurance_id;
            $input121['clinic_id'] = $request->clinic_id;
            $input121['gap_year'] = $setYear;
            $CareGapsDetailsData  = CareGapsDetails::create($input121);
        } else {
            $patientRecord = Patients::where('id',$request->patient_id)->first();
            $input121['caregap_details'] = $request->caregap_details;
            $input121['caregap_name'] = $request->col_name;
            $input121['created_user'] =  Auth::id();
            $input121['patient_id'] = $request->patient_id;
            $input121['doctor_id'] = $request->doctor_id ?? NULL;
            $input121['insurance_id'] = $patientRecord->insurance_id;
            $input121['clinic_id'] = $patientRecord->clinic_id;
            $input121['member_id'] = $patientRecord->member_id;
            $input121['source'] = "Patients Profile";
            $input121['gap_year'] = $setYear;

            $gapInsert = HumanaCareGaps::create($input121);
            unset($input121['source']);
            //unset($input121['insurance_id']);
            unset($input121['doctor_id']);
            
            $input121['caregap_id'] =  $gapInsert->id;
            $CareGapsDetailsData  = CareGapsDetails::create($input121);
        }

        if ($id==0) {
            $note = HumanaCareGaps::find($gapInsert->id);
        }

        try {
            
            if ($request->col_name =='breast_cancer_gap') {

                $breastCancerGapCheck = array("Bilateral Mastectomy", "Patient Refused", "Refusal Reviewed", "Non-Compliant", "N/A", "Scheduled");
                
                if (in_array($request->col_value, $breastCancerGapCheck)) {
                    $note->update([$input['col_name']=> $input['col_value']]);
                    $CareGapsDetails = $input['col_value'];
                } else {
                    $abc = $request->caregap_details;
                    $arr = json_decode($abc, true);
                    if (isset($arr["result"]) && isset($arr["date"]) ) {
                        if ($arr["result"]=='Abnormal') {
                            $note->update([$input['col_name']=> "Non-Compliant"]);
                            $CareGapsDetails = "Non-Compliant";
                        } elseif ($arr["result"]=='Normal') {
                            $note->update([$input['col_name']=> "Compliant"]);
                            $CareGapsDetails = "Compliant";
                        }
                    } else {
                        return $response = [
                            'success' => false,
                            'message' => 'Please fill all Breast Cancer required fields.'
                        ]; 
                    }
                }
            } elseif ($request->col_name =='colorectal_cancer_gap' ) {
                $colorectalCancerGapCheck = array("Total Colectomy", "Colorectal Cancer", "Patient Refused", "Refusal Reviewed", "Non-Compliant", "N/A", "Scheduled");
                if (in_array($request->col_value, $colorectalCancerGapCheck)) {
                    $note->update([$input['col_name']=> $input['col_value']]);
                    $CareGapsDetails = $input['col_value'];
                } else {
                    $abc = $request->caregap_details;
                    $arr = json_decode($abc, true);
                    
                    if( isset($arr["result"]) && isset($arr["testType"]) && isset($arr["date"]) ) {  
                        if ($arr["result"]=='Positive') {
                            $note->update([$input['col_name']=> "Non-Compliant"]);
                            $CareGapsDetails = "Non-Compliant";
                        } elseif ($arr["result"]=='Negative') {
                            $note->update([$input['col_name']=> "Compliant"]);
                            $CareGapsDetails = "Compliant";
                        }
                    } else {
                        return $response = [
                            'success' => false,
                            'message' => 'Please fill all Colorectal Cancer required fields.'
                        ]; 
                    }
                }
                
            } elseif ($request->col_name =='high_bp_gap') {
                $highBPGapCheck = array("Patient Refused", "Refusal Reviewed", "Non-Compliant", "N/A", "Scheduled");
                if (in_array($request->col_value, $highBPGapCheck)) {
                    $note->update([$input['col_name']=> $input['col_value']]);
                    $CareGapsDetails = $input['col_value'];
                } else {
                    $abc = $request->caregap_details;
                    $arr = json_decode($abc, true);
                    
                    if (isset($arr["systolicBp"]) && isset($arr["diastolicBp"]) && isset($arr["date"])) {
                        if ($arr["systolicBp"]<140 && $arr["diastolicBp"] <90) {
                            $note->update([$input['col_name']=> "Compliant"]);
                            $CareGapsDetails = "Compliant";
                        } else {
                            $note->update([$input['col_name']=> "Non-Compliant"]);
                            $CareGapsDetails = "Non-Compliant";
                        }
                    } else {
                        return $response = [
                            'success' => false,
                            'message' => 'Please fill all High BP Control required fields.'
                        ]; 
                    }
                }
            } elseif ($request->col_name =='hba1c_poor_gap') {
                $hba1cPoorGapCheck = array("Diagnosis Incorrect", "Not Reported", "Non-Compliant", "Patient Refused", "Refusal Reviewed", "N/A", "Scheduled");
                if (in_array($request->col_value, $hba1cPoorGapCheck)) {
                    $note->update([$input['col_name']=> $input['col_value']]);
                    $CareGapsDetails = $input['col_value'];
                } else {
                    $abc = $request->caregap_details;
                    $arr = json_decode($abc, true);
                    if (isset($arr["examResult"]) && isset($arr["date"]) ) {
                        if($arr["examResult"]>=9) {
                            $note->update([$input['col_name']=> "Non-Compliant"]);
                            $CareGapsDetails = "Non-Compliant";
                        } else {
                            $note->update([$input['col_name']=> "Compliant"]);
                            $CareGapsDetails = "Compliant";
                        }  
                    } else {
                        return $response = [
                            'success' => false,
                            'message' => 'Please fill all HBA1C Poor required fields.'
                        ];
                    }  
                }    
            } elseif ($request->col_name =='bp_control_gap') {
                if($request->col_value == "Non-Compliant" || $request->col_value == "Patient Refused" || $request->col_value == "N/A" || $request->col_value == "Scheduled") {
                    $note->update([$input['col_name']=> $input['col_value']]);
                    $CareGapsDetails = $input['col_value'];
                } else {
                    $abc = $request->caregap_details;
                    $arr = json_decode($abc, true);
                    
                    if( isset($arr["systolicBp"]) && isset($arr["diastolicBp"]) && isset($arr["date"]) ) {
                        if($arr["systolicBp"]<140 && $arr["diastolicBp"] <90) {
                            $note->update([$input['col_name']=> "Compliant"]);
                            $CareGapsDetails = "Compliant";
                        }else{
                            $note->update([$input['col_name']=> "Non-Compliant"]);
                            $CareGapsDetails = "Non-Compliant";
                        }
                    } else {
                        return $response = [
                            'success' => false,
                            'message' => 'Please fill all BP Control required fields.'
                        ]; 
                    }
                }
            } elseif ($request->col_name =='statin_therapy_gap') {
                $statinTherapyGapCheck = array("Compliant", "Patient Refused", "Non-Compliant", "N/A", "Scheduled");
                if (in_array($request->col_value, $statinTherapyGapCheck)) {
                    $note->update([$input['col_name']=> $input['col_value']]);
                    $CareGapsDetails = $input['col_value'];
                }
            } elseif ($request->col_name =='osteoporosis_mgmt_gap') {
                $osteoporosisMgmtGapCheck = array("Compliant", "Patient Refused", "Non-Compliant", "N/A", "Scheduled");
                if (in_array($request->col_value, $osteoporosisMgmtGapCheck)) {
                    $note->update([$input['col_name']=> $input['col_value']]);
                    $CareGapsDetails = $input['col_value'];
                }
            } elseif ($request->col_name =='hba1c_gap') {
                $hba1cGapCheck = array("Diagnosis Incorrect", "Not Reported", "Non-Compliant", "Patient Refused", "N/A", "Scheduled");
                if (in_array($request->col_value, $hba1cGapCheck)) {
                    $note->update([$input['col_name']=> $input['col_value']]);
                    $CareGapsDetails = $input['col_value'];
                } else {
                    $abc = $request->caregap_details;
                    $arr = json_decode($abc, true);
                    if( isset($arr["examResult"]) && isset($arr["date"]) ) {
                        if($arr["examResult"]<9) {
                            $note->update([$input['col_name']=> "Compliant"]);
                            $CareGapsDetails = "Compliant";
                        } else {
                            $note->update([$input['col_name']=> "Non-Compliant"]);
                            $CareGapsDetails = "Non-Compliant";
                        }
                    } else {
                        return $response = [
                            'success' => false,
                            'message' => 'Please fill all HBA1C required fields.'
                        ];
                    }
                
                }
                
            } elseif ($request->col_name =='awv_gap') {

                $awvGapCheck = array("Non-Compliant", "Changed PCP", "Left Practice", "Need To Schedule", "Not An Established Patient", "Not Found In PF", "Not In Service", "Unable To Reach");
                if (in_array($request->col_value, $awvGapCheck)){
                    $note->update([$input['col_name']=> $input['col_value']]);
                    $CareGapsDetails = $input['col_value'];
                    // $note->update([$input['col_name']=> "Non-Compliant"]);
                    // $CareGapsDetails = "Non-Compliant";
                } elseif($request->col_value == "Completed"){
                    $note->update([$input['col_name']=> "Completed"]);
                    $CareGapsDetails = "Completed";
                } elseif($request->col_value == "Pending Visit"){
                    $note->update([$input['col_name']=> "Pending Visit"]);
                    $CareGapsDetails = "Pending Visit";
                } elseif($request->col_value == "Refused"){
                    $note->update([$input['col_name']=> "Patient Refused"]);
                    $CareGapsDetails = "Patient Refused";
                } elseif($request->col_value == "N/A"){
                    $note->update([$input['col_name']=> $input['col_value']]);
                    $CareGapsDetails = $input['col_value'];
                }
                
                
            } else {
                if($request->col_value == "Completed"){
                    $input['col_value']= "Compliant";
                }
                $note->update([$input['col_name']=> $input['col_value']]);
                $CareGapsDetails = $input['col_value'];
            }

            $cgdd_id = $CareGapsDetailsData->id;
            CareGapsDetails::where('id', $cgdd_id)->update(['status' => $CareGapsDetails]);
            $CareGapsDetailsDataShow = CareGapsDetails::with('userinfo:id,first_name,mid_name,last_name')->find($cgdd_id);
           // $CareGapsDetailsDataShow = CareGapsDetails::find($cgdd_id);
            $response = [
                    'success' => true,
                    'message' => 'CareGap Data Updated Successfully',
                    //'data' => $note,
                    'CareGapsDetailsData' => $CareGapsDetailsDataShow
                ];
        } catch (\Exception $e) {
            $response = array('success' => false, 'message' => $e->getMessage());
        }
        return response()->json($response);
    }
    public function addComment(Request $request){
        try {
            $validator = Validator::make($request->all(),
                [
                    'patient_id' => 'required',
                    'caregap_id'  => 'required',
                    'caregap_name' => 'required',                    
                ]
            );

            if($validator->fails()) {
                $error = $validator->getMessageBag()->first();
                return response()->json(['success'=>false,'errors'=>$error]);
            };

            $onlyOnePatientData = Patients::find($request->patient_id);
            $input["patient_id"] = $request->patient_id;
            $input["caregap_id"] = $request->caregap_id;
            $input["insurance_id"] = $onlyOnePatientData->insurance_id;
            $input["caregap_name"] = $request->caregap_name;
            $input["comment"] = $request->comment;
            $input["created_user"] = Auth::id();
            $CareGapsCommentsData  = CareGapsComments::create($input);
            $commentId =  $CareGapsCommentsData->id;
            $query = CareGapsComments::with('userinfo:id,first_name,mid_name,last_name')->where('id',$commentId)->first();
            
            $response = [
                'success'       => true,
                'message'       => 'Comment Add Successfully',
                'CommentsData'  => $query
            ];
        } catch (\Exception $e) {
            $response = array('success'=>false,'message'=>$e->getMessage(), 'line'=>$e->getLine()); 
        }
        return response()->json($response);
    }
    public function updateComment(Request $request, $id)
    {
        try{
            $validator = Validator::make($request->all(), [
                'comment'  => 'required'
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'errors' => $validator->getMessageBag()]);
            };
            
            $input = $validator->valid();
            $updateComment = CareGapsComments::find($id); 
            $authId =  Auth::id();
            $updateComment->update(['comment'=> $input['comment'],'created_user' => $authId ]);
            $commentId = $updateComment->id;
            $query = CareGapsComments::with('userinfo:id,first_name,mid_name,last_name')->where('id',$commentId)->first();
            
            $response = [
                'success'       => true,
                'message'       => 'Comment Update Successfully',
                'CommentsData'  => $query,
            ];
        } catch (\Exception $e) {
            $response = array('success'=>false,'message'=>$e->getMessage(), 'line'=>$e->getLine()); 
        }
        return response()->json($response);
    }


}