<?php
namespace App\Http\Controllers\Api;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\PatientsController;
use App\Http\Controllers\Api\CommonFunctionController;

use App\Models\Patients;
use App\Models\Questionaires;
use App\Models\Insurances;
use App\Models\Clinic;
use App\Models\CareGaps;
use App\Models\HumanaCareGaps;
use App\Models\MedicareArizonaCareGaps;
use App\Models\AetnaMedicareCareGaps;
use App\Models\AllwellMedicareCareGaps;
use App\Models\HealthchoiceArizonaCareGaps;
use App\Models\UnitedHealthcareCareGaps;
use App\Models\CareGapsDetails;
use App\Models\CareGapsComments;
use App\Models\PatientsFileLogModel;
use App\Models\InsuranceHistory;

use Auth, Validator, DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class CareGapsController extends Controller
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

            $query = CareGaps::where('deleted_at',NULL);
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
    public function allComments121(Request $request)
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
    public function clinicData121(){
        
        try{
            
            $patientsFileLog = PatientsFileLogModel::select('id','insurance_id', 'clinic_id', 'gap_year')->get()->toArray();
            $clinicList = Clinic::select('id', 'name')->get()->toArray();
            $insurances = Insurances::all();
                $insuranceList = [];
                foreach ($insurances as $key => $value) {
                    $insuranceList[$value->id] = $value->name;
                }
            $insuranceListArray = Insurances::select('id', 'name')->get()->toArray();
            $response = [
                'success'           => true,
                'message'           => 'Data Retrived Successfully',
                'insurances_list'   => $insuranceListArray,
                'clinic_list'       => $clinicList,
                'patientsFileLog'   => $patientsFileLog,
                
                'insurances'        => (object) $insuranceList
            ];

        } catch (\Exception $e) {
            $response = array('success' => false, 'message' => $e->getMessage(), 'line'=>$e->getLine());
        }

        return response()->json($response);
    }

    /* To Store the gaps data in the database from Excel sheet */
    public function storeBulkCareGaps(Request $request)
    {
        $currentDate = Carbon::now();
        $currentYear = $currentDate->year;
        try {
            $validator = Validator::make($request->all(),
                [
                    'clinicIds' => 'required',
                    'data'  => 'required',
                    'insuranceIds'  => 'required',
                    'gap_year'  => 'required',
                ],
                [
                    'clinicIds.required' => 'Please Select a Clinic.',
                    'insuranceIds.required' => 'Please Select an Insurance.',
                    'gap_year.required' => 'Please Select a Gap Year.',
                    'data.required' => 'No data found to add in patients'
                ]
            );

            if($validator->fails()) {
                $error = $validator->getMessageBag()->first();
                return response()->json(['success'=>false,'errors'=>$error]);
            };

            $clinic_id = $request->clinicIds;
            $data = $request->data;
            $gap_year = $request->gap_year;
            $insuranceIdFromUser = $request->insuranceIds;

            // Getting insurance Details and table Model
            $InsuranceDetails = $this->findInsurance($insuranceIdFromUser);

            $insuranceProvider = $InsuranceDetails['insurance_provider'];
            $tableModel = $InsuranceDetails['table_model'];
            
            // Function to store pateint in patients table from excel sheet
            $patientsResult = (new PatientsController)->storeBulkPatients($request, 1, $gap_year)->getOriginalContent();
            $patientConcrollerResponse =  $patientsResult['message'];

            $bulktoStoreCareGaps = [];
            $scheduledAwv = [];

            /* Fetching this insurance gap names from constants
            ** Don't forget to add the new gap in this array if new gap is in system database */
            $constantsCareGaps = Config('constants.caregaps.'.$insuranceProvider);

            // checking the records of non excel created gaps to delete when creating the gaps from sheet
            if ($gap_year == $currentYear) {
                $currentYearGaps = $tableModel->where('gap_year', $currentYear)->where('source', '!=', 'CareGap_File')->delete();
            }

            // looping on Excel sheet data
            foreach ($data as $key => $value) {
                $valueOfCareGaps = [];
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
                    $valueOfCareGaps["total_gaps"] = @$value['total_gaps'] ?? '';
                    $valueOfCareGaps["q_id"] = @$existPatientData['awv_encounter']['id'] ?? "";
                    $valueOfCareGaps["source"] = 'CareGap_File';
                    $valueOfCareGaps["created_user"] = Auth::id();
                    $valueOfCareGaps["patient_id"] = @$existPatientData['id'] ?? NULL;
                    $valueOfCareGaps["created_at"] = Carbon::now();
                    $valueOfCareGaps["updated_at"] = Carbon::now();
                    $valueOfCareGaps["deleted_at"] = NULL;

                    // Declared Variable for gaps
                    $breastCancerGap = ''; 
                    $colorectalCancerGap = '';

                    if ($value['breast_cancer_gap'] == "" && $value['breast_cancer_gap_current'] == "") {
                        $breastCancerGap = "N/A";
                    } else {
                        $breastCancerGap = !empty($value['breast_cancer_gap_current']) ? $value['breast_cancer_gap_current'] : $value['breast_cancer_gap'];
                    }

                    if ($value['colorectal_cancer_gap'] == "" && $value['colorectal_cancer_gap_current'] == "") {
                        $colorectalCancerGap = "N/A";
                    } else {
                        $colorectalCancerGap = !empty($value['colorectal_cancer_gap_current']) ? $value['colorectal_cancer_gap_current'] : $value['colorectal_cancer_gap'];
                    }

                    // Checking Encounter for Breast Cancer & Colorectal Cancer gap
                    if (!empty($existPatientData['awv_encounter'])) {

                        // Decoding stringify object of encounter data
                        $encounterData = json_decode($existPatientData['awv_encounter']['questions_answers'], true);

                        // Screening section from AWV Encounter
                        $screening = @$encounterData['screening'] ?? "";

                        //Breast cancer Screening
                        $mammogram = !empty($screening['mammogram_done']) ? $screening['mammogram_done'] : '';
                        if($mammogram == 'Yes'){
                            $mammogram_done_on = @$screening['mammogram_done_on'] ?? "";

                            // Update gap status as per month criteria of Mommogram
                            if(!empty($mammogram_done_on) && $breastCancerGap != "N/A"){
                                // Parse the date string using Carbon
                                $currentDate = Carbon::now()->endOfMonth();
                                $mammogramDate = Carbon::createFromFormat('m/Y', $mammogram_done_on)->endOfMonth();

                                // Calculate the month difference
                                $monthDifference = $currentDate->diffInMonths($mammogramDate);

                                $breastCancerGap = $monthDifference <= 27 ? "Compliant" : "Non-Compliant";
                            }
                        }

                        //Colon Cancer Screening
                        $colonScreening = !empty($screening['colonoscopy_done']) ? $screening['colonoscopy_done'] : '';
                        $testType = !empty($screening['colon_test_type']) ? $screening['colon_test_type'] : "";
                        $colonDate = !empty($screening['colonoscopy_done_on']) ? $screening['colonoscopy_done_on'] : "";
                        if ($colonScreening == 'Yes' && $colorectalCancerGap != "N/A") {
                            if (!empty($testType)) {
                                if ($testType == 'Colonoscopy') {
                                    if (!empty($colonDate)) {
                                        $currentDate = Carbon::now()->endOfMonth();
                                        $colonDate = Carbon::createFromFormat('m/Y', $colonDate)->endOfMonth();
    
                                        // Calculate the year difference
                                        $yearDifference = $currentDate->diffInYears($colonDate);
                                        
                                        // Updating gap status
                                        $colorectalCancerGap = $yearDifference <= 10 ? "Compliant" : "Non-Compliant";
                                    } else {
                                        $colorectalCancerGap = "Non-Compliant";
                                    }
                                } elseif ($testType == 'FIT Test') {
                                    // getting year of current Date and Fit Date year
                                    if (!empty($colonDate)) {
                                        $date1 = Carbon::now();
                                        $date1Year = (explode("-",$date1));
                                        $currentDateYear = $date1Year[0];
                                        $colonDateYears = (explode("/",$colonDate));
                                        $colonDateYear = $colonDateYears[1];
    
                                        // Updating gap status
                                        $colorectalCancerGap = $currentDateYear == $colonDateYear ? "Compliant" : "Non-Compliant";
                                    } else {
                                        $colorectalCancerGap = "Non-Compliant";
                                    }
                                } else if ($testType == 'Cologuard') {
                                    if (!empty($colonDate)) {
                                        $currentDate = Carbon::now()->endOfMonth();
                                        $colonDate = Carbon::createFromFormat('m/Y', $colonDate)->endOfMonth();
    
                                        // Calculate the year difference
                                        $yearDifference = $currentDate->diffInYears($colonDate);
    
                                        // Updating gap status
                                        $colorectalCancerGap = $yearDifference <= 2 ? "Compliant" : "Non-Compliant";
                                    } else {
                                        $colorectalCancerGap = "Non-Compliant";
                                    }
                                }
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

                foreach ($bulktoStoreCareGaps as $key => $value) {
                    $condition = [
                        'patient_id' => $value['patient_id'],
                        'gap_year' => $value['gap_year'],
                    ];
                    $res = $tableModel->updateOrCreate($condition, $value);
                }
                

                // Checking for AWV gap if scheduled then inserting scheduled date in caregap details table
                $insertedRows = [];
                if ($res) {

                    // Retrieve the last inserted IDs
                    $lastInsertedId = $tableModel->getConnection()->getPdo()->lastInsertId();

                    // Retrieve the count of the inserted rows
                    $numberOfInsertedRows = count($bulktoStoreCareGaps); // Assuming $values is the array passed to insert()
                
                    // Retrieve the first inserted ID
                    $firstInsertedId = $tableModel->orderBy('id', 'asc')->first()->id;

                    // Retrieve the last inserted ID
                    $lastInsertedId = $tableModel->orderBy('id', 'desc')->first()->id;

                    // Generate an array containing all inserted IDs
                    $insertedIds = range($firstInsertedId, $lastInsertedId);
                
                    // Retrieve the inserted rows by their IDs
                    $insertedRows = $tableModel->whereIn('id', $insertedIds)->where('gap_year', $gap_year)->get();

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

                    foreach ($scheduledAwv as $key => $value) {
                        $condition = [
                            'caregap_id' => $value['caregap_id']
                        ];
                        CareGapsDetails::updateOrCreate($condition, $value);
                    }
                }
                $response = array('success'=>true, 'scheduled'=> $scheduledAwv, 'patient_result' => $patientsResult,'message'=> 'CareGaps Added Successfully And '.$patientConcrollerResponse);
            } else {
                $response = array('success'=>false,'error'=>'Duplicate CareGaps found And '.$patientConcrollerResponse);
            }

        } catch (\Exception $e) {
            $response = array('success'=>false, 'error'=>$e->getMessage(), 'line'=>$e->getLine());
        }

        return response()->json($response);
    }

    /* To Store the gaps data in the database from Excel sheet */
    public function storePreProcessor(Request $request)
    {
    // return "Rizwan";
        if(!empty($request->existingPatients) && !empty($request->newPatients)){
            $request['data'] =  array_merge($request->existingPatients,$request->newPatients);
        } elseif(empty($request->newPatients) && !empty($request->existingPatients)) {
            $request['data'] = $request->existingPatients;
        } elseif(empty($request->existingPatients) && !empty($request->newPatients)) {
            $request['data'] = $request->newPatients;
        } else{
            $response = array('success'=>true, 'message'=> 'Nothing Change');
            
            //return "Nothing Change ";
            return response()->json($response);
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
        //return $request->insuranceIds;
        //return "Rizwan";
        $currentDate = Carbon::now();
        $currentYear = $currentDate->year;
        try {
            $validator = Validator::make($request->all(),
                [
                    'clinicIds' => 'required',
                    'data'  => 'required',
                    'insuranceIds'  => 'required',
                    'gap_year'  => 'required',
                ],
                [
                    'clinicIds.required' => 'Please Select a Clinic.',
                    'insuranceIds.required' => 'Please Select an Insurance.',
                    'gap_year.required' => 'Please Select a Gap Year.',
                    'data.required' => 'No data found to add in patients'
                ]
            );

            if($validator->fails()) {
                $error = $validator->getMessageBag()->first();
                return response()->json(['success'=>false,'errors'=>$error]);
            };

            $clinic_id = $request->clinicIds;
            $data = $request->data;
            $gap_year = $request->gap_year;
            $insuranceIdFromUser = $request->insuranceIds;
            
            $fileLogId = @$request->fileLogId;
            $Result = (new CommonFunctionController)->patientsFileLogs($request, 1, $gap_year, $fileLogId)->getOriginalContent();
            $data = $request->data;

            // Getting insurance Details and table Model
            $InsuranceDetails = $this->findInsurance($insuranceIdFromUser);

            $insuranceProvider = $InsuranceDetails['insurance_provider'];
            $tableModel = $InsuranceDetails['table_model'];
            
            // Function to store pateint in patients table from excel sheet
            $patientsResult = (new PatientsController)->storeBulkPatientsPreProcessFile($request, 1, $gap_year)->getOriginalContent();
            $patientConcrollerResponse =  $patientsResult['message'];

            $bulktoStoreCareGaps = [];
            $scheduledAwv = [];

            /* Fetching this insurance gap names from constants
            ** Don't forget to add the new gap in this array if new gap is in system database */
            $constantsCareGaps = Config('constants.caregaps.'.$insuranceProvider);

            // checking the records of non excel created gaps to delete when creating the gaps from sheet
            if ($gap_year == $currentYear) {
                $currentYearGaps = $tableModel->where('gap_year', $currentYear)->where('source', '!=', 'CareGap_File')->delete();
            }

            // looping on Excel sheet data
            foreach ($data as $key => $value) {
                
                $valueOfCareGaps = [];
                
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
                    $valueOfCareGaps["total_gaps"] = @$value['total_gaps'] ?? '';
                    $valueOfCareGaps["q_id"] = @$existPatientData['awv_encounter']['id'] ?? "";
                    $valueOfCareGaps["source"] = 'CareGap_File';
                    $valueOfCareGaps["created_user"] = Auth::id();
                    $valueOfCareGaps["patient_id"] = @$existPatientData['id'] ?? NULL;
                    $valueOfCareGaps["created_at"] = Carbon::now();
                    $valueOfCareGaps["updated_at"] = Carbon::now();
                    $valueOfCareGaps["deleted_at"] = NULL;

                    // Declared Variable for gaps
                    $breastCancerGap = ''; 
                    $colorectalCancerGap = '';

                    if (@$value['breast_cancer_gap'] == "" && @$value['breast_cancer_gap_current'] == "") {
                        $breastCancerGap = "N/A";
                    } else {
                        $breastCancerGap = !empty(@$value['breast_cancer_gap_current']) ? @$value['breast_cancer_gap_current'] : @$value['breast_cancer_gap'];
                    }

                    if (@$value['colorectal_cancer_gap'] == "" && @$value['colorectal_cancer_gap_current'] == "") {
                        $colorectalCancerGap = "N/A";
                    } else {
                        $colorectalCancerGap = !empty(@$value['colorectal_cancer_gap_current']) ? @$value['colorectal_cancer_gap_current'] : @$value['colorectal_cancer_gap'];
                    }

                    // Checking Encounter for Breast Cancer & Colorectal Cancer gap
                    if (!empty($existPatientData['awv_encounter'])) {

                        // Decoding stringify object of encounter data
                        $encounterData = json_decode($existPatientData['awv_encounter']['questions_answers'], true);

                        // Screening section from AWV Encounter
                        $screening = @$encounterData['screening'] ?? "";

                        //Breast cancer Screening
                        $mammogram = !empty($screening['mammogram_done']) ? $screening['mammogram_done'] : '';
                        if($mammogram == 'Yes'){
                            $mammogram_done_on = @$screening['mammogram_done_on'] ?? "";

                            // Update gap status as per month criteria of Mommogram
                            if(!empty($mammogram_done_on) && $breastCancerGap != "N/A"){
                                // Parse the date string using Carbon
                                $currentDate = Carbon::now()->endOfMonth();
                                $mammogramDate = Carbon::createFromFormat('m/Y', $mammogram_done_on)->endOfMonth();

                                // Calculate the month difference
                                $monthDifference = $currentDate->diffInMonths($mammogramDate);

                                $breastCancerGap = $monthDifference <= 27 ? "Compliant" : "Non-Compliant";
                            }
                        }

                        //Colon Cancer Screening
                        $colonScreening = !empty($screening['colonoscopy_done']) ? $screening['colonoscopy_done'] : '';
                        $testType = !empty($screening['colon_test_type']) ? $screening['colon_test_type'] : "";
                        $colonDate = !empty($screening['colonoscopy_done_on']) ? $screening['colonoscopy_done_on'] : "";
                        if ($colonScreening == 'Yes' && $colorectalCancerGap != "N/A") {
                            if (!empty($testType)) {
                                if ($testType == 'Colonoscopy') {
                                    if (!empty($colonDate)) {
                                        $currentDate = Carbon::now()->endOfMonth();
                                        $colonDate = Carbon::createFromFormat('m/Y', $colonDate)->endOfMonth();
    
                                        // Calculate the year difference
                                        $yearDifference = $currentDate->diffInYears($colonDate);
                                        
                                        // Updating gap status
                                        $colorectalCancerGap = $yearDifference <= 10 ? "Compliant" : "Non-Compliant";
                                    } else {
                                        $colorectalCancerGap = "Non-Compliant";
                                    }
                                } elseif ($testType == 'FIT Test') {
                                    // getting year of current Date and Fit Date year
                                    if (!empty($colonDate)) {
                                        $date1 = Carbon::now();
                                        $date1Year = (explode("-",$date1));
                                        $currentDateYear = $date1Year[0];
                                        $colonDateYears = (explode("/",$colonDate));
                                        $colonDateYear = $colonDateYears[1];
    
                                        // Updating gap status
                                        $colorectalCancerGap = $currentDateYear == $colonDateYear ? "Compliant" : "Non-Compliant";
                                    } else {
                                        $colorectalCancerGap = "Non-Compliant";
                                    }
                                } else if ($testType == 'Cologuard') {
                                    if (!empty($colonDate)) {
                                        $currentDate = Carbon::now()->endOfMonth();
                                        $colonDate = Carbon::createFromFormat('m/Y', $colonDate)->endOfMonth();
    
                                        // Calculate the year difference
                                        $yearDifference = $currentDate->diffInYears($colonDate);
    
                                        // Updating gap status
                                        $colorectalCancerGap = $yearDifference <= 2 ? "Compliant" : "Non-Compliant";
                                    } else {
                                        $colorectalCancerGap = "Non-Compliant";
                                    }
                                }
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
                                    if (@$breastCancerGap == 'Compliant' || @$value[$gapnameCurrent] == 'Compliant') {
                                        $valueOfCareGaps[$gapname] = 'Compliant';
                                    } else {
                                        $valueOfCareGaps[$gapname] = @$value[$gapnameCurrent] ?? $value[$gapname] ?? 'N/A';
                                    }

                                    $valueOfCareGaps[$gapnameInsurance] = $value[$gapname] ?? 'N/A';
                                } elseif ($gapname == 'colorectal_cancer_gap') {
                                    /* if Current status is compliant from any source Excel or Encounter
                                    ** Gap status should be compliant
                                    ** Else whatever the status excel hold will be assigned */
                                    if (@$colorectalCancerGap == 'Compliant' || @$value[$gapnameCurrent] == 'Compliant') {
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

                foreach ($bulktoStoreCareGaps as $key => $value) {
                    $condition = [
                        'patient_id' => $value['patient_id'],
                        'gap_year' => $value['gap_year'],
                    ];
                    $res = $tableModel->updateOrCreate($condition, $value);
                }
                

                // Checking for AWV gap if scheduled then inserting scheduled date in caregap details table
                $insertedRows = [];
                if ($res) {

                    // Retrieve the last inserted IDs
                    $lastInsertedId = $tableModel->getConnection()->getPdo()->lastInsertId();

                    // Retrieve the count of the inserted rows
                    $numberOfInsertedRows = count($bulktoStoreCareGaps); // Assuming $values is the array passed to insert()
                
                    // Retrieve the first inserted ID
                    $firstInsertedId = $tableModel->orderBy('id', 'asc')->first()->id;

                    // Retrieve the last inserted ID
                    $lastInsertedId = $tableModel->orderBy('id', 'desc')->first()->id;

                    // Generate an array containing all inserted IDs
                    $insertedIds = range($firstInsertedId, $lastInsertedId);
                
                    // Retrieve the inserted rows by their IDs
                    $insertedRows = $tableModel->whereIn('id', $insertedIds)->where('gap_year', $gap_year)->get();

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

                    foreach ($scheduledAwv as $key => $value) {
                        $condition = [
                            'caregap_id' => $value['caregap_id']
                        ];
                        CareGapsDetails::updateOrCreate($condition, $value);
                    }
                }
                $response = array('success'=>true, 'scheduled'=> $scheduledAwv, 'patient_result' => $patientsResult,'message'=> 'CareGaps Added Successfully And '.$patientConcrollerResponse);
            } else {
                $response = array('success'=>false,'error'=>'Duplicate CareGaps found And '.$patientConcrollerResponse);
            }

        } catch (\Exception $e) {
            $response = array('success'=>false, 'error'=>$e->getMessage(), 'line'=>$e->getLine());
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
        $folder_name = '/caregaps/';//$request->folder_name ?? "";
        
        if($request->hasFile('file')){
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
        $note = CareGaps::find($id);

        if($id != 0) {
            $input121['caregap_details'] = @$request->caregap_details ?? NULL;
            $input121['caregap_name'] = $request->col_name;
            $input121['caregap_id'] =  $id;
            $input121['created_user'] =  Auth::id();
            $input121['patient_id'] = $request->patient_id;
            $input121['insurance_id'] = $note->insurance_id;
            $input121['clinic_id'] = $request->clinic_id;
            $CareGapsDetailsData  = CareGapsDetails::create($input121);
        } else {
            $patientRecord = Patients::where('id',$request->patient_id)->first();
            //return $patientRecord->insurance_id;
            $input121['caregap_details'] = $request->caregap_details;
            $input121['caregap_name'] = $request->col_name;
            $input121['created_user'] =  Auth::id();
            $input121['patient_id'] = $request->patient_id;
            $input121['doctor_id'] = $request->doctor_id ?? NULL;
            $input121['insurance_id'] = $patientRecord->insurance_id;
            $input121['clinic_id'] = $patientRecord->clinic_id;
            $input121['member_id'] = $patientRecord->member_id;
            $input121['source'] = "Patients Profile";

            $gapInsert = CareGaps::create($input121);
            unset($input121['source']);
            //unset($input121['insurance_id']);
            unset($input121['doctor_id']);
            
            $input121['caregap_id'] =  $gapInsert->id;
            $CareGapsDetailsData  = CareGapsDetails::create($input121);
        }

        if($id==0){
            $note = CareGaps::find($gapInsert->id);
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
                    if(isset($arr["result"]) && isset($arr["date"]) ) {
                        if($arr["result"]=='Abnormal') {
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
            } else if ($request->col_name =='colorectal_cancer_gap' ) {
                $colorectalCancerGapCheck = array("Total Colectomy", "Colorectal Cancer", "Patient Refused", "Refusal Reviewed", "Non-Compliant", "N/A", "Scheduled");
                if (in_array($request->col_value, $colorectalCancerGapCheck)) {
                    $note->update([$input['col_name']=> $input['col_value']]);
                    $CareGapsDetails = $input['col_value'];
                } else {
                    $abc = $request->caregap_details;
                    $arr = json_decode($abc, true);
                    
                    if( isset($arr["result"]) && isset($arr["testType"]) && isset($arr["date"]) ) {  
                        if($arr["result"]=='Positive')
                        {
                            $note->update([$input['col_name']=> "Non-Compliant"]);
                            $CareGapsDetails = "Non-Compliant";
                        }
                        elseif ($arr["result"]=='Negative')
                        {
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
                    $details = @$request->caregap_details ? json_decode($request->caregap_details, true) : "";
                    
                    if( isset($details["systolicBp"]) && isset($details["diastolicBp"]) && isset($details["date"]) ) {

                        if($details["systolicBp"] > 140 || $details["diastolicBp"] > 90) {
                            $update = [
                                $input['col_name'] => 'Non-Compliant',
                            ];
                            $CareGapsDetails = "Non-Compliant";
                        } else {
                            $update = [
                                $input['col_name'] => 'Compliant',
                            ];
                            $CareGapsDetails = "Compliant";
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
                        if($arr["examResult"] > 9) {
                            $update = [
                                'hba1c_poor_gap' => 'Non-Compliant',
                            ];
                            $CareGapsDetails = "Non-Compliant";
                        } else {
                            $update = [
                                'hba1c_poor_gap' => 'Compliant',
                            ];
                            $CareGapsDetails = "Compliant";
                        }
                        $note->update($update);
                    } else {
                        return $response = [
                            'success' => false,
                            'message' => 'Please fill all HBA1C Poor required fields.'
                        ];
                    }  
                }    
            } elseif ($request->col_name =='statin_therapy_gap') {
                $statinTherapyGapCheck = array("Compliant", "Patient Refused", "Refusal Reviewed", "Non-Compliant", "N/A", "Scheduled");
                if (in_array($request->col_value, $statinTherapyGapCheck)) {
                    $note->update([$input['col_name']=> $input['col_value']]);
                    $CareGapsDetails = $input['col_value'];
                }
            } elseif ($request->col_name =='osteoporosis_mgmt_gap') {
                $osteoporosisMgmtGapCheck = array("Compliant", "Patient Refused", "Refusal Reviewed", "Non-Compliant", "N/A", "Scheduled");
                if (in_array($request->col_value, $osteoporosisMgmtGapCheck)) {
                    $note->update([$input['col_name']=> $input['col_value']]);
                    $CareGapsDetails = $input['col_value'];
                }
            } elseif ($request->col_name =='awv_gap') {

                $awvGapCheck = array("Non-Compliant", "Changed PCP", "Left Practice", "Need To Schedule", "Not An Established Patient", "Not Found In PF", "Not In Service", "Unable To Reach");
                
                if (in_array($request->col_value, $awvGapCheck)){
                    $note->update([$input['col_name']=> $input['col_value']]);
                    $CareGapsDetails = $input['col_value'];
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
                $note->update([$input['col_name']=> $input['col_value']]);
                $CareGapsDetails = $input['col_value'];
            }

            $cgdd_id = $CareGapsDetailsData->id;
            CareGapsDetails::where('id', $cgdd_id)->update(['status' => $CareGapsDetails]);
            $CareGapsDetailsDataShow = CareGapsDetails::with('userinfo:id,first_name,mid_name,last_name')->find($cgdd_id);
            $response = [
                    'success' => true,
                    'message' => 'CareGap Data Updated Successfully',
                    //'data' => $note,
                    'CareGapsDetailsData' => $CareGapsDetailsDataShow
                ];
        } catch (\Exception $e) {
            $response = array('success' => false, 'message' => $e->getMessage(), 'line' => $e->getLine());
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
            $response = array('success'=>false,'error'=>$e->getMessage(), 'line'=>$e->getLine()); 
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
            $response = array('success'=>false,'error'=>$e->getMessage(), 'line'=>$e->getLine()); 
        }
        return response()->json($response);
    }

    // Getting List of Clinic and Insurances
    public function clinicData()
    {
        
        try{
            $clinicList = Clinic::select('id', 'name')->get()->toArray();
            $insurances = Insurances::all();
                $insuranceList = [];
                foreach ($insurances as $key => $value) {
                    $insuranceList[$value->id] = $value->name;
                }

            $patientsFileLog = PatientsFileLogModel::with(['insuranceData' => function($query) {
                $query->select('id', 'provider');
            }])
            ->select('id','file_name','insurance_id', 'clinic_id', 'gap_year')->get()->toArray();
            $insuranceListArray = Insurances::select('id', 'name')->get()->toArray();
            $response = [
                'success' => true,
                'message' => 'Data Retrived Successfully',
                'insurances' => (object) $insuranceList,
                
                'insurances_list'   => $insuranceListArray,
                'patientsFileLog'   => $patientsFileLog,
                
                'clinic_list'       => $clinicList
            ];
        } catch (\Exception $e) {
            $response = array('success' => false, 'message' => $e->getMessage(), 'line'=>$e->getLine());
        }

        return response()->json($response);
    }
    
    /**
     * Method duplicateCareGapRecord
     *
     * @param Request $request [explicite description]
     * copying records from previous year to current year
     * @return void
     */
    public function duplicateCareGapRecord(Request $request){
        try{
            $currentDate = Carbon::now();
            $currentYear = $currentDate->year;

            $insurance_id = $request->insurance_id;
            $genraterCopyYear = $request->year;

            $insuranceDetails = $this->findInsurance($insurance_id);
            $CGModelsName = $insuranceDetails['table_model'];
            $dataExist = $CGModelsName::all()->count();
            
            if($dataExist == 0){
              $response = array('success'=>false,'message'=>"Sorry Care Gaps Table is Empty");
            }else{
              $GapData = $CGModelsName::where(['gap_year'=>$genraterCopyYear, 'source'=>'CareGap_File'])->get()->toArray();
            }

            if(empty($GapData)){
                $maxYear = $CGModelsName::where('source', 'CareGap_File')->max('gap_year');

                $GapData = $CGModelsName::whereHas('patientinfo', function ($q) use ($insurance_id) {
                            $q->where('insurance_id', $insurance_id);
                        })->where('gap_year',$maxYear)->get()->toArray();

                // find column name
                $tableName = $CGModelsName->getTable();

                // $dbColumns= Schema::getColumnListing($tableName);
                $encounterList = [];

                if (!empty($GapData)) {
                    // Getting All patient Ids from caregap data fetched from database
                    $patientIds = array_column($GapData, 'patient_id');

                    // getting awv encounters agains all above patient ids
                    $awvEncounters = Questionaires::whereIn('patient_id', $patientIds)->where('program_id', '1')->latest()->get()->toArray();

                    $arrayToStore = [];

                    // Looping through the GAP data found from the table
                    foreach ($GapData as $key => $value) {

                        // Declared Variable for gaps (BCS-COL) as per current status and as per insurance status from previous year
                        $breastCancerGap = $value['breast_cancer_gap'] == "N/A" ? "N/A" : "Non-Compliant";
                        $breastCancerGapInsurance = $value['breast_cancer_gap_insurance'];

                        $colorectalCancerGap = $value['colorectal_cancer_gap'] == "N/A" ? "N/A" : "Non-Compliant";
                        $colorectalCancerGapInsurance = $value['colorectal_cancer_gap_insurance'];

                        // filter spacific encouter against patient
                        $patientEncounter = array_filter($awvEncounters, function($row) use ($value) {
                            return $row['patient_id'] == $value['patient_id'];
                        });

                        if (!empty($patientEncounter)) {
                            $patientEncounter = reset($patientEncounter);
                            
                            // Decoding stringify object of encounter data
                            $encounterData = json_decode($patientEncounter['questions_answers'], true);

                            // Screening section from AWV Encounter
                            $screening = @$encounterData['screening'] ?? "";
                            if(!empty($screening)) {
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

                                        if ($value['breast_cancer_gap_insurance'] != 'N/A') {
                                            $breastCancerGap = $monthDifference <= 27 ? "Compliant" : "Non-Compliant";
                                        }
                                    }
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
                                            if ($value['colorectal_cancer_gap_insurance'] != 'N/A') {
                                                $colorectalCancerGap = $yearDifference <= 10 ? "Compliant" : "Non-Compliant";
                                            }
                                        } elseif ($testType == 'FIT Test') {
                                            // getting year of current Date and Fit Date year
                                            $date1 = Carbon::now();
                                            $date1Year = (explode("-",$date1));
                                            $currentDateYear = $date1Year[0];
                                            $colonDateYears = (explode("/",$colonDate));
                                            $colonDateYear = $colonDateYears[1];
    
                                            // Updating gap status
                                            if ($value['colorectal_cancer_gap_insurance'] != 'N/A') {
                                                $colorectalCancerGap = $currentDateYear == $colonDateYear ? "Compliant" : "Non-Compliant";
                                            }
                                        } else if ($testType == 'Cologuard') {
                                            $currentDate = Carbon::now()->endOfMonth();
                                            $colonDate = Carbon::createFromFormat('m/Y', $colonDate)->endOfMonth();
    
                                            // Calculate the year difference
                                            $yearDifference = $currentDate->diffInYears($colonDate);
    
                                            // Updating gap status
                                            if ($value['colorectal_cancer_gap_insurance'] != 'N/A') {
                                                $colorectalCancerGap = $yearDifference <= 2 ? "Compliant" : "Non-Compliant";
                                            }
                                        }
                                    }
                                }
                            }
                        }

                        
                        $rowToStore = [];

                        /* creating the column for each row which needs to be updated
                        ** either from AWV Encounter
                        ** either from previous year records */
                        $rowToStore['member_id'] = $value['member_id'];
                        $rowToStore['patient_id'] = $value['patient_id'];
                        $rowToStore['doctor_id'] = $value['doctor_id'];
                        $rowToStore['insurance_id'] = $value['insurance_id'];
                        $rowToStore['clinic_id'] = $value['clinic_id'];
                        $rowToStore['gap_year'] = $genraterCopyYear;
                        $rowToStore['breast_cancer_gap'] = $breastCancerGap;
                        $rowToStore['breast_cancer_gap_insurance'] = $breastCancerGapInsurance;
                        $rowToStore['colorectal_cancer_gap'] = $colorectalCancerGap;
                        $rowToStore['colorectal_cancer_gap_insurance'] = $colorectalCancerGapInsurance;
                        $rowToStore['source'] = 'previous year';
                        $rowToStore['q_id'] = @$encounterData[0]['id'] ?? NULL;
                        $rowToStore['created_user'] = Auth::user()->id;
                        $rowToStore['created_at'] = $currentDate;
                        $rowToStore['updated_at'] = $currentDate;
                        $rowToStore['deleted_at'] = NULL;

                        // looping thorugh the remaing gaps column which are not updated from Ecnounter
                        foreach ($value as $column => $dbValue) {
                            if (array_key_exists($column, $rowToStore) === false && $column !== "id") {
                                $rowToStore[$column] = $dbValue == "N/A" ? "N/A" : "Non-Compliant";
                            }
                        }

                        // Assinging each row to a new index of array Passed to the updateOrcreate Eloquent below
                        $arrayToStore[] = $rowToStore;
                    }
                }

                /* Declared Variable to get the row which rows are created and which existing row from same year with other source is updated */
                $createdRecords = [];
                $updatedRecords = [];

                if (!empty($arrayToStore)) {
                    // looping through the array to check whether it needs to be updated or created
                    foreach ($arrayToStore as $data) {
                        $recordStatus = $CGModelsName::updateOrCreate(['patient_id' => $data['patient_id'], 'gap_year'=>$genraterCopyYear], $data);

                        if ($recordStatus->wasRecentlyCreated) {
                            // New record was created
                            $createdRecords[] = $recordStatus;
                        } else {
                            // Existing record was updated
                            $updatedRecords[] = $recordStatus;
                        }
                    }
                }

                $response = array('success'=>true,'created_records' => $createdRecords, 'updated_records'=>$updatedRecords, 'message'=>'Generate New Year Gaps Successfully');
                return response()->json($response);
            } else {
                $response = array('success'=>false,'error'=>"You Can't Generate New Year Gaps Because Allready Generated");
            }
        } catch (\Exception $e) {
            $response = array('success'=>false,'error'=>$e->getMessage(), 'line'=>$e->getLine());
        }

        return response()->json($response);
    }

    // Getting insurance name and table model from insurance id.
    private function findInsurance($insurance_id)
    {
        $insuranceDate = Insurances::whereNull('deleted_at')->where('id', $insurance_id)->get()->first();

        if (!empty($insuranceDate)) {
            $insuranceProvider = $insuranceDate->provider;
            $tableModel = "";
            switch ($insuranceProvider) {
                case 'hcpw-001':
                    $tableModel = new CareGaps;
                    break;
                case 'hum-001':
                    $tableModel = new HumanaCareGaps;
                    break;
                case 'med-arz-001':
                    $tableModel = new MedicareArizonaCareGaps;
                    break;
                case 'aet-001':
                    $tableModel = new AetnaMedicareCareGaps;
                    break;
                case 'allwell-001':
                    $tableModel = new AllwellMedicareCareGaps;
                    break;
                case 'hcarz-001':
                    $tableModel = new HealthchoiceArizonaCareGaps;
                    break;
                case 'uhc-001':
                    $tableModel = new UnitedHealthcareCareGaps;
                    break;
    
                default:
                    break;
            }

            $data = [
                'insurance_provider' => $insuranceProvider,
                'table_model' => $tableModel,
            ];
            return $data;
        }
    }
    
}
