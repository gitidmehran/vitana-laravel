<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\CareplanController;
use Illuminate\Http\Request;
use App\Models\Questionaires;
use App\Models\Patients;
use App\Models\Doctors;
use App\Models\Diagnosis;
use App\Models\Programs;
use App\Models\User;
use App\Models\SuperBillCodes;
use App\Models\CcmMonthlyAssessment;


use App\Models\AetnaMedicareCareGaps;
use App\Models\AllwellMedicareCareGaps;
use App\Models\HealthchoiceArizonaCareGaps;
use App\Models\HumanaCareGaps;
use App\Models\CareGaps;
use App\Models\MedicareArizonaCareGaps;
use App\Models\UnitedHealthcareCareGaps;


use App\Models\CareGapsDetails;
use App\Models\CcmTasks;
use Validator,Config,Auth, DB,DateTime;
use Carbon\Carbon;

class QuestionaireController extends Controller
{
    protected $singular = "Encounter";
    protected $plural = "Questionaire Surveys";
    protected $action = "/dashboard/questionaires-survey";
    protected $view = "questionaries.";
    protected $per_page = '';
	
    public function __construct(){
	    $this->per_page = Config('constants.perpage_showdata');
	}


    public function index(Request $request)
    {
        try {
            $from = Carbon::now()->subMonths(6);
            $to = Carbon::now()->subMonths(12);

            $data = [
                'singular' => $this->singular,
                'page_title' => $this->plural.' List',
                'action'   => $this->action
            ];

            $doctor_id = $request->input("doctor_id") ?? '';
            $patient_id = $request->input("patient_id") ?? '';
            $clinic_id = $request->input("clinic_id") ?? '';
            $insurance_id = $request->input("insurance_id") ?? '';

            $activeUsers = $request->input("activeUsers") ?? '';
            $awv_completed_total = $request->input("awv_completed_total") ?? '';
            $awv_completed_A12 = $request->input("awv_completed_A12") ?? '';
            $awv_pending_A12 = $request->input("awv_pending_A12") ?? '';
            $awv_pending_population = $request->input("awv_pending_population") ?? '';
            $group_a1 = $request->input("group_A1") ?? '';
            $group_a2 = $request->input("group_A2") ?? '';
            $group_c = $request->input("group_c") ?? '';
            
            $query = Questionaires::with('patient.insurance','patient.diagnosis', 'patient.insuranceHistories.insurance', 'user','program:id,name,short_name','allMonthlyAssessment')->orderBy('id','desc');
            
            if (!empty($group_c)) {
                $group_c = $query->where("created_at","<", $to);
            }

            if (!empty($group_a2)) {
                $query = $query->whereBetween('created_at', [$to, $from]);
            }

            if (!empty($group_a1)) {
                $query = $query->where("created_at",">", $from);
            }

            if (!empty($activeUsers)) {
                $query = $query->where("created_at",">", $to);
            }

            if (!empty($awv_completed_total)) {
                $query = $query->where(['status' => 'Pre-screening completed']);
            }

            if (!empty($awv_pending_population)) {
                $query = $query->where(['status' => 'Pre-screening pending']);
            }

            if (!empty($awv_completed_A12)) {
                $query = $query->where("status","Pre-screening completed")->where("created_at",">", $to);
            }

            if (!empty($awv_pending_A12)) {
                $query = $query->where("status","!=","Pre-screening completed")->where("created_at",">", $to);
            }
            
            if(!empty($doctor_id)){
                $query->where('doctor_id',$doctor_id);
            }

            if(!empty($insurance_id)){
                $query->where('insurance_id',$insurance_id);
            }

            if(!empty($clinic_id) && count(explode(',', $clinic_id)) == 1){
                $query->where('clinic_id',$clinic_id);
            }

            if(!empty($patient_id)){
                $query->where('patient_id',$patient_id);
            }

            if ($request->has('search') && !empty($request->input('search'))) {
                $search = $request->get('search');

                $first_name = '';
                $last_name = '';
                $dob = "";

                if ($search == trim($search) && str_contains($search, ' ')) {
                    $search = explode(' ', $search);
                    $first_name = $search['0'];
                    $last_name = $search['1'];
                }

                $query = $query->whereHas('patient', function($q) use ($search, $first_name, $last_name) {
                    if (empty($first_name) && empty($last_name)) {
                        $q->where('first_name', 'like', '%' . $search . '%')
                        ->orWhere('last_name', 'like', '%' . $search . '%')
                        ->orWhere('status', 'like', '%' . $search . '%');
                    } else if (!empty($first_name) && !empty($last_name)) {
                        $q->where('first_name', 'like', '%' . $first_name . '%')
                        ->where('last_name', 'like', '%' . $last_name . '%');
                    }
                });
            }

            if ($request->has('my_patients') && $request->my_patients == 1) {
                $authId = Auth::id();
                $query->whereHas('patient', function ($q) use ($authId) {
                    $q->where('coordinator_id', $authId);
                });
            }

            if (!empty(Auth::user()->program_id)) {
                $program_id = Auth::user()->program_id;
                $allowed_program = explode(',', $program_id);
                $query->whereIn('program_id', $allowed_program);
            }

            $query = $query->paginate($this->per_page);
            $total = $query->total();
            $current_page = $query->currentPage();
            $query = $query->toArray();

            $list = [];

            if(!empty($query['data'])){
                
                foreach ($query['data'] as $key => $value) {

                    if (empty($value['patient'])) {
                        continue;
                    }

                    /* Chronic Diseases Array */
                    $patient_diseases = [
                        "Depression" => "false",
                        "CongestiveHeartFailure" => "false",
                        "ChronicObstructivePulmonaryDisease" => "false",
                        "CKD" => "false",
                        "DiabetesMellitus" => "false",
                        "Hypertensions" => "false",
                        "Obesity" => "false",
                        "Hypercholesterolemia" => "false",
                        "anemia" => "false",
                        "hyperthyrodism" => "false",
                        "asthma" => "false",
                    ];

                    $chronic_diseases = Config::get('constants')['chronic_diseases'];

                    /* Patient diagnosis for ccm */
                    $patientDiagnosis = $value['patient']['diagnosis'] ?? [];
                    if (!empty($patientDiagnosis)) {
                        foreach ($patientDiagnosis as $key1 => $value1) {
                            $condition_id = strtoupper(explode(' ', $value1['condition'])[0]);
                            $disease_status = $value1['status'];
        
                            $data = array_filter($chronic_diseases, function ($item) use ($condition_id, $disease_status) {
                                if ($disease_status == 'ACTIVE' || $disease_status == 'active') {
                                    return in_array($condition_id, $item);
                                }
                            });
                            
                            if ($data) {
                                $disease_name = array_keys($data)[0];
                                $patient_diseases[$disease_name] = "true";
                            }
                        }
                    }
                    
                    if ($value['date_of_service'] != "") {
                        $screening_data = json_decode(@$value['questions_answers'], true);

                        // getting date of service of the encounter
                        $dos = date('m/d/Y',strtotime($value['date_of_service']));
                        // Parse date strings into Carbon instances with the specified format
                        $dateOfService = Carbon::createFromFormat('m/d/Y', $dos);

                        // declaring variable for insurance name
                        $inactiveInsuranceName = "";

                        // checking the date of insurance with date of service to show the relevant insurance name
                        if (!empty($value['patient']['insurance_histories'])) {
                            $insuranceHistory = $value['patient']['insurance_histories'];
                            $dosYearInsurance = array_filter($insuranceHistory, function($data) use ($dateOfService){
                                /* logic to check if date of service is in between insurance start date and insurance end date
                                 *  Or if date of service is less then insurance end date incase insruance start date is missing */
                                if(!empty($data['insurance_start_date']) && !empty($data['insurance_end_date'])) {
                                    $insuranceStartDate = date('m/d/Y', strtotime($data['insurance_start_date']));
                                    $insuranceEndDate = date('m/d/Y', strtotime($data['insurance_end_date']));
                                    $insuranceStartDate = Carbon::createFromFormat('m/d/Y', $insuranceStartDate);
                                    $insuranceEndDate = Carbon::createFromFormat('m/d/Y', $insuranceEndDate);
                                    return $dateOfService->between($insuranceStartDate, $insuranceEndDate);
                                } else if (empty($data['insurance_start_date']) && !empty($data['insurance_end_date'])) {
                                    $insuranceEndDate = date('m/d/Y', strtotime($data['insurance_end_date']));
                                    $insuranceEndDate = Carbon::createFromFormat('m/d/Y', $insuranceEndDate);
                                    return $dateOfService < $insuranceEndDate;
                                }
                            });
                            
                            $dosYearInsurance = reset($dosYearInsurance);
                            // getting inactive insurance details
                            $insuranceFromHistory = @$dosYearInsurance['insurance'] ?? "";
                            
                            // Fetching Inactive insurance name from insurance details
                            if(!empty($dosYearInsurance) && !empty($insuranceFromHistory)) {
                                $inactiveInsuranceName = $insuranceFromHistory['name'];
                            }
                        }

                        /* setting insurance name as per logic above
                         * bydefault active insurance name */
                        $insuranceName = $value['patient']['insurance']['name'];
                        if (!empty($inactiveInsuranceName)) {
                            $insuranceName = $inactiveInsuranceName;
                        }


                        if (sizeof($screening_data) > 0) {
                            $list[] = [
                                'id' => $value['id'],
                                'serial_no' => $value['serial_no'],
                                'patient_id' => $value['patient_id'],
                                'patient_name' => $value['patient']['name'],
                                'patient_age' => $value['patient']['age'],
                                'patient_gender' => $value['patient']['gender'],
                                'program_id' => $value['program_id'],
                                'program_name' => $value['program']['short_name'],
                                'dob' => date('m/d/Y',strtotime($value['patient']['dob'])) ?? '',
                                'contact_no' => $value['patient']['contact_no'] ?? '',
                                'date_of_service' => date('m/d/Y',strtotime($value['date_of_service'])) ?? '',
                                'signed_date' => $value['signed_date'],
                                'insurance_name' => $insuranceName ?? "",
                                'status' => $value['status'],
                                'diagnosis' => (object)$patient_diseases ?? [],
                            ];
                        }

                    }

                    if (!empty($value['all_monthly_assessment'])) {
                        foreach ($value['all_monthly_assessment'] as $monthlykey => $monthlyvalue) {

                            if (!empty($monthlyvalue['date_of_service'])) {
                                
                                $dosYear = Carbon::parse($monthlyvalue['date_of_service'])->year;

                                $inactiveInsuranceName = "";

                                if (!empty($value['patient']['insurance_histories'])) {
                                    $insuranceHistory = $value['patient']['insurance_histories'];
                                    $dosYearInsurance = array_filter($insuranceHistory, function($data) use ($dosYear){
                                        $insuranceStartDate = date('Y', strtotime($data['insurance_start_date']));
                                        $insuranceEndDate = date('Y', strtotime($data['insurance_end_date']));
                                        return $insuranceStartDate = $dosYear || $insuranceEndDate == $dosYear;
                                    });
                                    
                                    $dosYearInsurance = reset($dosYearInsurance);

                                    $insuranceFromHistory = @$dosYearInsurance['insurance'] ?? "";

                                    if(!empty($dosYearInsurance) && !empty($insuranceFromHistory)) {
                                        $inactiveInsuranceName = $insuranceFromHistory['name'];
                                    }
                                }
                            }

                            $insuranceName =  "";
                            if (!empty($inactiveInsuranceName)) {
                                $insuranceName = $inactiveInsuranceName;
                            } else {
                                $insuranceName = $value['patient']['insurance']['name'];
                            }

                            $list[] = [
                                'id' => $monthlyvalue['id'],
                                'parent_id' => $value['id'],
                                'monthly_assessment' => 1,
                                'serial_no' => $monthlyvalue['serial_no'],
                                'patient_id' => $monthlyvalue['patient_id'],
                                'date_of_service' => $monthlyvalue['date_of_service'] != "" ? date('m/d/Y',strtotime($monthlyvalue['date_of_service'])) : '',
                                'status' => $monthlyvalue['status'],
                                'patient_name' => $value['patient']['name'],
                                'patient_age' => $value['patient']['age'],
                                'patient_gender' => $value['patient']['gender'],
                                'program_id' => $value['program_id'],
                                'program_name' => $value['program']['short_name'],
                                'dob' => date('m/d/Y',strtotime($value['patient']['dob'])) ?? '',
                                'contact_no' => $value['patient']['contact_no'] ?? '',
                                'signed_date' => $value['signed_date'],
                                'insurance_name' => @$insuranceName ?? "",
                                'diagnosis' => (object)$patient_diseases ?? [],
                            ];
                        }


                    }

                }
            }

            $response = [
                'success' => true,
                'message' => 'Questionaire Data Retrived Successfully',
                'current_page' => $current_page,
                'total_records' => $total,
                'per_page'   => $this->per_page,
                'data' => $list
            ];

        } catch (\Exception $e) {
            $response = array('success'=>false,'message'=>$e->getMessage(),'line'=>$e->getLine());
        }

        return response()->json($response);
    }


    /* Store Questionnaire in database */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(),
                [
                'patient_id' => 'required',
                'program_id'  => 'required',
                'date_of_service' => 'required'
                ],

                [
                    'patient_id.required' => 'Please Select a Patient.',
                    'program_id.required' => 'Please Select a Program.',
                    'date_of_service.required' => 'Please Select Date of service.'
                ]
            );

            if($validator->fails()) {
                $error = $validator->getMessageBag()->first();
                return response()->json(['success'=>false,'errors'=>$error]);
            };

            $input = $request->all();

            $patientId = $input['patient_id'];
            $patient_data = Patients::with('insurance')->where('id',$patientId)->first();

            
            $where = [
                'program_id' => '1',
                'patient_id' => $patientId,
            ];
            
            $lastAwv = Questionaires::where($where)->first();
            
            $question = [];
            $not_needed = ['patient_id', 'program_id', 'date_of_service', 'isMonthly', 'monthly_assessment', 'depression_phq9', 'obesity', 'copd_assessment', 'ckd_assessment', 'cong_heart_failure', 'cholesterol_assessment', 'hypertension', 'diabetes_mellitus'];

            if (@$input['program_id'] == "1") {
                $depression_key = array_search('depression_phq9', $not_needed);
                $cholesterol_key = array_search('cholesterol_assessment', $not_needed);
                unset($not_needed[$depression_key]);
                unset($not_needed[$cholesterol_key]);

                if (!empty($lastAwv)) {
                    $data_of_service = Carbon::parse($input['date_of_service']);
                    $last_data_of_service = !empty($lastAwv['date_of_service']) ? Carbon::parse($lastAwv['date_of_service']) : "";
    
                    $eligible = ($data_of_service->year > $last_data_of_service->year && $data_of_service->month >= $last_data_of_service->month) ? true : false;

                    if (!$eligible && $patient_data['insurance']['provider'] == 'med-arz-001') {
                        return response()->json(['success' => false, 'errors' => 'AWV is already performed for this patient']);
                    } else if (!empty($last_data_of_service) && $patient_data['insurance']['provider'] != 'med-arz-001') {
                        $dos_year = $data_of_service->format('Y');
                        $last_dos_year = $last_data_of_service->format('Y');
    
                        if ($dos_year == $last_dos_year) {
                            return response()->json(['success' => false, 'errors' => 'AWV is already performed for this patient']);
                        }
                    }
                }
            }
            
            foreach ($input as $key => $value) {
                if (!in_array($key, $not_needed)) {
                    $question[$key] = $input[$key] ?? [];
                }
            }


            $program = Programs::find($input['program_id'])->toArray();

            $lastProgramEntry = Questionaires::where('program_id',$input['program_id'])->orderBy('id','desc')->first();

            if(!empty($lastProgramEntry)){
                $number = explode('-', $lastProgramEntry['serial_no'])[1];
                $newNumber = (int)$number+1;
                $serialNo = $program['short_name'].'-'.$newNumber;
            }else{
                $serialNo = $program['short_name'].'-1001';
            }

            $clinic_id = $patient_data->clinic_id;
            $insurance_info = $patient_data->insurance_id;

            if (empty($insurance_info)) {
                $response = array('success'=>false,'errors'=>'Patient does not belong to any insurance');
                return response()->json($response);
            }

            /*rizwan end*/
           
            $coordinator_id = @$patient_data['coordinator_id'] ?? NULL;

            $row = [
                'program_id' => $input['program_id'],
                'patient_id' => $input['patient_id'],
                'clinic_id' => $clinic_id,
                'insurance_id' => $insurance_info,
                'serial_no' => $serialNo,
                'coordinator_id' => $coordinator_id,
                'date_of_service' => ($input['program_id'] == "1" || ($input["program_id"] == "2" && $input['isMonthly'] != "1") ? Carbon::parse($input['date_of_service']) : null),
                'questions_answers' => json_encode($question),
                'created_user' => 1,
                'status' => @$input['program_id'] == "2" ? "completed" : "Pre-screening pending",
            ];
            //return $question;
            $record = Questionaires::create($row);
           
            $datagaps = $this->careGap($input,$question,$record->id,true);

            $this->saveCodes($record['id'], $question, $input['patient_id'], $record['clinic_id']);

            $monthly_response = "";
            $monthly_assessment_id = "";
            if ($input['program_id'] == "2") {
                // STORE MONTHLY ASSESSMENT
                $monthly_response = $this->storeMonthlyAssessment($serialNo, $record['id'], $input['patient_id'], $clinic_id, $input['program_id'],$input, $patient_data);
                $monthly_assessment_id = isset($monthly_response['id']) ? $monthly_response['id'] : "";
            }

            $response = array('success'=>true,'message'=>$this->singular.' Added Successfully', 'questionnaire_id'=> $record['id'], 'monthly_assessment_id'=>$monthly_assessment_id, 'monthly_assessment'=>$monthly_response);

        } catch (\Exception $e) {
            $response = array('success'=>false,'message'=>$e->getMessage(), $e->getLine());
        }

        return response()->json($response);

    }


    /* Fetch Questionnaire to edit */
    public function edit(Request $request, $id)
    {
        try {
            $child_id = $request->has('monthly_assessment_id') ? $request->monthly_assessment_id : "";
            $row = Questionaires::with('patient')->with('monthlyAssessment', function($query) use ($child_id) {
                if (!empty($child_id)) {
                    $query->where('id', $child_id);
                }
            })->find($id)->toArray();

            $questions_answers = json_decode($row['questions_answers'], true);

            $path = $row['program_id'] === 1 ? 'awv' : 'ccm';
            
            $patient_diseases = [
                "Depression" => "false",
                "CongestiveHeartFailure" => "false",
                "ChronicObstructivePulmonaryDisease" => "false",
                "CKD" => "false",
                "DiabetesMellitus" => "false",
                "Hypertensions" => "false",
                "Obesity" => "false",
                "Hypercholesterolemia" => "false",
                "anemia" => "false",
                "hyperthyrodism" => "false",
                "asthma" => "false",
            ];

            if ($row['program_id'] == 2) {

                $patientDiagnosis = Diagnosis::where('patient_id', $row['patient_id'])->get()->toArray();
                $chronic_diseases = Config::get('constants')['chronic_diseases'];
                $arrayofChronic = [];
                
                foreach ($patientDiagnosis as $key => $value) {
                    $condition_id = strtoupper(explode(' ', $value['condition'])[0]);
                    $disease_status = $value['status'];

                    $data = array_filter($chronic_diseases, function ($item) use ($condition_id, $disease_status) {
                        if ($disease_status == 'ACTIVE' || $disease_status == 'active') {
                            return in_array($condition_id, $item);
                        }
                    });
                    
                    if ($data) {
                        $disease_name = array_keys($data)[0];
                        $patient_diseases[$disease_name] = "true";
                        
                        if (!in_array($disease_name, $arrayofChronic)) {
                            $arrayofChronic[] = $disease_name;
                        }
                    }
                }

                /* Fetching tobacco Use section from AWV Screening if available */
                $tobacco_use = "";
                if ($patient_diseases['ChronicObstructivePulmonaryDisease'] == "true") {
                    $where_clause = [
                        'patient_id' => $row['patient_id'],
                        'program_id' => '1'
                    ];
                    $awv_screening_data = Questionaires::where($where_clause)->select('questions_answers')->first();
                    
                    if (!empty($awv_screening_data)) {
                        $awv_screening_data = json_decode($awv_screening_data['questions_answers'], true);
                        $tobacco_use = @$awv_screening_data['tobacco_use'] ?? "";
                    }
                }

                $lastMonthlyAssessment = $row['monthly_assessment'] ? json_decode($row['monthly_assessment']['monthly_assessment'], true) : [];
                
                foreach ($lastMonthlyAssessment as $key => $value) {
                    $questions_answers[$key] = $value;
                }

                $questions_answers['tobacco_use'] = $tobacco_use;

            }

            $data = [
                'row' => $row,
                'path' => $path,
                'list' => $questions_answers,
                'diagnosis' => (object)$patient_diseases
            ];

            $response = array('success'=>true, 'data'=>$data);

        } catch (\Exception $e) {
            $response = array('success'=>false,'message'=>$e->getMessage());
        }

        return response()->json($response);
    }


    /* Update Existing Questionnaire */
    public function update(Request $request, $id)
    {
        try {
            /* Validation */
            $validator = Validator::make($request->all(),
                [
                    'patient_id' => 'required',
                    'program_id'  => 'required',
                    'date_of_service' => 'required'
                ],
                [
                    'patient_id.required' => 'Please Select a Patient.',
                    'program_id.required' => 'Please Select a Program.',
                    'date_of_service.required' => 'Please Select Date of service.'
                ]
            );

            if($validator->fails()) {
                $error = $validator->getMessageBag()->first();
                return response()->json(['success'=>false,'errors'=>$error]);
            };

            $input = $request->all();
            
            $question = [];
            $questionnaireData = Questionaires::where(['id'=>$id, 'patient_id'=>$input['patient_id'], 'program_id'=>$input['program_id']])->first();
            $questionnaire = json_decode($questionnaireData['questions_answers'], true);

            /* Code block assign value to questionnaire array only dor assessment sections and update in table*/
            $not_needed = ['patient_id', 'program_id', 'date_of_service', 'isMonthly', 'monthly_assessment', 'depression_phq9', 'obesity', 'copd_assessment', 'ckd_assessment', 'cong_heart_failure', 'cholesterol_assessment', 'hypertension', 'diabetes_mellitus'];
            
            if (@$input['program_id'] == "1") {
                $depression_key = array_search('depression_phq9', $not_needed);
                $cholesterol_key = array_search('cholesterol_assessment', $not_needed);
                unset($not_needed[$depression_key]);
                unset($not_needed[$cholesterol_key]);
            }
            
            foreach ($input as $key => $value) {
                if (!in_array($key, $not_needed)) {
                    $questionnaire[$key] = $input[$key] ?? [];
                }
            }

            $data = [
                'date_of_service' => Carbon::parse($input['date_of_service'])->toDateString() ?? '',
                'questions_answers' => json_encode($questionnaire)
            ];

            if (@$input['isMonthly'] != "1" || !empty(@$input['general_assessment'])) {
                if (@$input['isMonthly'] == 1) {
                    unset($data['date_of_service']);
                }
                Questionaires::where('id', $id)->update($data);
                $dataGaps = $this->careGap($input,$questionnaire,$id,true);
            }
            
            /* Plucking clininc id from patient data */
            $patientData = Patients::where('id', $input['patient_id'])->select('coordinator_id', 'clinic_id')->first();
            $clinic_id = $patientData['clinic_id'];

            
            /* Code block assign value for monthly assessment section and update in table */
            if ($input['program_id'] == "2") {
                $monthlyAssessmentSections = ['monthly_assessment', 'depression_phq9', 'obesity', 'copd_assessment', 'ckd_assessment', 'cong_heart_failure', 'cholesterol_assessment', 'hypertension', 'diabetes_mellitus'];

                /* Getting previous encounter if exist on same date of service */
                $dos = Carbon::parse($input['date_of_service'])->format("Y-m-d");

                $monthOfService = Carbon::parse($dos)->month;
                $yearOfService = Carbon::parse($dos)->year;

                $where_clause = [
                    'questionnaire_id' => $id,
                    'date_of_service' => $dos
                ];
                $monthlyAssesment = CcmMonthlyAssessment::where('questionnaire_id', $id)->whereMonth('date_of_service', $monthOfService)->whereYear('date_of_service', $yearOfService)->first();
                
                $data = [
                    'monthlyAssesment' => $monthlyAssesment,
                    'test' => 'test',
                ];

                $monthly_assessment_id = "";

                /* Condition to update the existing encounter of same date of service */
                if ($monthlyAssesment) {
                    $monthlyAssesmentData = json_decode($monthlyAssesment['monthly_assessment'], true);
                    
                    $currentMonth = Carbon::parse($input['date_of_service'])->format('m');
                    $lastAssessmentMonth = Carbon::parse($monthlyAssesment->date_of_service)->format('m');

                    foreach ($input as $key => $value) {
                        if (in_array($key, $monthlyAssessmentSections)) {
                            $monthlyAssesmentData[$key] = $input[$key];
                        }
                    }
                    $updateColumn = [
                        'date_of_service' => $dos,
                        'monthly_assessment' => json_encode($monthlyAssesmentData),
                    ];
                    CcmMonthlyAssessment::where('id', $monthlyAssesment->id)->update($updateColumn);

                    $monthly_assessment_id = $monthlyAssesment->id;

                     // Updating date_of_service of all tasks of this months in case if date_of_service of encounter is updated
                    if (!empty($monthly_assessment_id)) {
                        $updateTasks = [
                            'date_of_service' => $dos,
                        ];
                        CcmTasks::where('monthly_encounter_id', $monthly_assessment_id)->update($updateTasks);
                    }

                } else {
                    /* Create new Encounter */
                    $monthly_response = $this->storeMonthlyAssessment($questionnaireData['serial_no'], $id, $input['patient_id'], $clinic_id, $input['program_id'],$input, $patientData);

                    $monthly_assessment_id = !empty($monthly_response['id']) ? $monthly_response['id'] : "" ;
                }
            }

            $clinicId = $questionnaireData['clinic_id'];
            $check = $this->saveCodes($id, $questionnaire, $input['patient_id'], $clinicId);

            $response = [
                'success' => true,
                'message' => 'Questionaire Data Updated Successfully',
                'monthly_assessment_id'=> @$monthly_assessment_id ?? "",
                'check' => $check,
            ];
        } catch (\Exception $e) {
            $response = array('success' => false, 'message' => $e->getMessage(), 'line' => $e->getLine());
        }

        return response()->json($response);
    }


    /* UPDATE Questionnaire Status */
    public function updateQuestionnaireStatus(Request $request, $id)
    {
        try {
            $input = $request->all();
            $questionnaireStatus = $input['selected'];
            $data = [
                'status' => $questionnaireStatus,
            ];
            if (@$input['monthlyAssessment'] == 1 && @$input['programId'] == 2) {
                $updated = CcmMonthlyAssessment::where('id', $id)->update($data);
            } else {
                $questionnaire = Questionaires::find($id);
                $updated = $questionnaire->update($data);

                if ($updated) {
                    $patient_id = $questionnaire->patient_id;

                    $patient_data = Patients::with('insurance')->where('id',$patient_id)->first();
                    $insuranceData = @$patient_data->insurance ?? "";
                    $insuranceProvider = $insuranceData->provider;

                    $gapTable = "";
                    switch ($insuranceProvider) {
                        case 'hcpw-001':
                            $gapTable = new CareGaps;
                            break;
                        case 'hum-001':
                            $gapTable = new HumanaCareGaps;
                            break;
                        case 'med-arz-001':
                            $gapTable = new MedicareArizonaCareGaps;
                            break;
                        case 'aet-001':
                            $gapTable = new AetnaMedicareCareGaps;
                            break;
                        case 'allwell-001':
                            $gapTable = new AllwellMedicareCareGaps;
                            break;
                        case 'hcarz-001':
                            $gapTable = new HealthchoiceArizonaCareGaps;
                            break;
                        case 'uhc-001':
                            $gapTable = new UnitedHealthcareCareGaps;
                            break;
                        default:
                            break;
                    }
                    
                    $update = [
                        'awv_gap' => 'Completed',
                    ];

                    if ($questionnaireStatus == 'Seen' || $questionnaireStatus == 'Signed') {
                        $caregap_note = CareGaps::where('patient_id', $patient_id)->first();
                        
                        if ($caregap_note) {
                            $update = $gapTable->where('patient_id', $patient_id)->update($update);
                            
                            $update_data = [
                                'caregap_details' => NULL,
                                'caregap_name' => 'awv_gap',
                                'caregap_id' => $caregap_note->id,
                                'created_user' => Auth::id(),
                                'patient_id' => $patient_id,
                                'clinic_id' => $questionnaire->clinic_id,
                                'status' => 'Completed',
                            ];

                            $caregap_details_note = CareGapsDetails::create($update_data);
                        }

                    }
                }
            }

            if ($updated) {
                $response = [
                    'success' => true,
                    'message' => 'Questionaire Data Updated Successfully',
                    'status' => $questionnaireStatus,
                ];
            } else {
                $response = [
                    'success' => true,
                    'message' => 'No Record found to update',
                    'status' => $questionnaireStatus,
                ];
            }
        } catch (\Exception $e) {
            $response = array('success' => false, 'message' => $e->getMessage(), 'line'=>$e->getLine());
        }

        return response()->json($response);
    }


    /* Delete Existing Questionnaire */
    public function destroy(Request $request, $id)
    {
        try {
            if ($request->has('monthly_assessment') && $request->get('monthly_assessment') == 1) {
                CcmMonthlyAssessment::where('id', $id)->delete();
            } else {
                $note = Questionaires::find($id);
                $note->delete();
            }
            $response = array('success' => true, 'message' => $this->singular . ' Deleted!');
        } catch (\Exception $e) {
            $response = array('success' => false, 'message' => $e->getMessage());
        }
        return response()->json($response);
    }


    /* Get Program */
    public function getProgramms(Request $request)
    {
        try {
            $input = $request->all();
            $lastAwv = [];
            $lastCcm = [];


            $patient_diseases = [
                "Depression" => "false",
                "CongestiveHeartFailure" => "false",
                "ChronicObstructivePulmonaryDisease" => "false",
                "CKD" => "false",
                "DiabetesMellitus" => "false",
                "Hypertensions" => "false",
                "Obesity" => "false",
                "Hypercholesterolemia" => "false",
                "anemia" => "false",
                "hyperthyrodism" => "false",
                "asthma" => "false",
            ];

            /* FOR CCM */
            if ($input['program_id'] == 2) {
                
                $awvData = Questionaires::where('patient_id', $input['patient_id'])->where('program_id', 1)->orderBy('id', 'desc')->first();
                
                if (!empty($awvData)) {
                    $awvData->toArray();
                }
                $lastAwv = !empty($awvData) ? json_decode($awvData['questions_answers'], true) : [];

                // Replicating the AWV diabetes to CCM Diabeted Mellitus
                if (isset($lastAwv['diabetes']) && !empty($lastAwv['diabetes'])) {
                    $awv_diabetes = $lastAwv['diabetes'];

                    // report question
                    $report_available = "";
                    $report_request = "";
                    if (isset($awv_diabetes['diabetec_eye_exam_report'])) {
                        $eyeExamReport = $awv_diabetes['diabetec_eye_exam_report'];

                        if ($eyeExamReport === "report_available") {
                            $report_available = "Yes";
                        } elseif ($eyeExamReport === "report_requested") {
                            $report_request = "Yes";
                        }
                    }

                    // retinavue_ordered merge
                    $retinavue_ordered = "";
                    $script_given = "";

                    if (isset($awv_diabetes['ratinavue_ordered'])) {
                        if ($awv_diabetes['ratinavue_ordered'] == "Yes") {
                            $retinavue_ordered = "Yes";
                        } elseif ($awv_diabetes['ratinavue_ordered'] == "No") {
                            $script_given = "Yes";
                        }
                    }

                    $lastAwv['diabetes_mellitus']['hb_result'] = @$awv_diabetes['hba1c_value'] ?? "";
                    $lastAwv['diabetes_mellitus']['result_month'] = @$awv_diabetes['hba1c_date'] ?? "";
                    $lastAwv['diabetes_mellitus']['eye_examination'] = @$awv_diabetes['diabetec_eye_exam'] ?? "";
                    $lastAwv['diabetes_mellitus']['name_of_doctor'] = @$awv_diabetes['eye_exam_doctor'] ?? "";
                    $lastAwv['diabetes_mellitus']['name_of_facility'] = @$awv_diabetes['eye_exam_facility'] ?? "";
                    $lastAwv['diabetes_mellitus']['checkup_date'] = @$awv_diabetes['eye_exam_date'] ?? "";
                    $lastAwv['diabetes_mellitus']['report_available'] = $report_request != "" ? "No" : $report_available;
                    $lastAwv['diabetes_mellitus']['report_requested'] = $report_request;
                    $lastAwv['diabetes_mellitus']['retinavue_ordered'] = $script_given != "" ? "No" : $retinavue_ordered;
                    $lastAwv['diabetes_mellitus']['eye_examination_script'] = $script_given;
                }

                
                /* Getting ccm data to get show in the Newly ccm Assessment */
                $alreadyPerformedCCM = Questionaires::where('patient_id', $input['patient_id'])->where('program_id', 2)->select('id')->first();
                $lastMonthlyAssessment = [];
                
                if (!empty($alreadyPerformedCCM)) {
                    $row = CcmMonthlyAssessment::where('questionnaire_id', $alreadyPerformedCCM->id)->latest('id')->get()->toArray();
                    $lastMonthlyAssessment = isset($row['monthly_assessment']) ? json_decode($row['monthly_assessment'], true) : [];
                }
                
                if (!empty($lastMonthlyAssessment)) {
                    foreach ($lastMonthlyAssessment as $key => $value) {
                        $lastAwv[$key] = $value;
                    }
                }

                $patientDiagnosis = Diagnosis::where('patient_id', $input['patient_id'])->get()->toArray();

                $chronic_diseases = Config::get('constants')['chronic_diseases'];
                
                $arrayofChronic = [];
                foreach ($patientDiagnosis as $key => $value) {
                    $condition_id = strtoupper(explode(' ', $value['condition'])[0]);
                    $disease_status = $value['status'];

                    $data = array_filter($chronic_diseases, function ($item) use ($condition_id, $disease_status) {
                        if ($disease_status == 'ACTIVE' || $disease_status == 'active') {
                            return in_array($condition_id, $item);
                        }
                    });

                    if ($data) {
                        $key = array_keys($data)[0];
                        $patient_diseases[$key] = "true";

                        if (!in_array($key, $arrayofChronic)) {
                            $arrayofChronic[] = $key;
                        }
                    }
                }

                if (count($arrayofChronic) < 2) {
                    return response()->json(['success' => false, 'errors' => 'Patient is not eligible for CCM']);
                }
            }

            /* FOR AWV */
            if ($input['program_id'] == 1) {

                /* Get last CCM data if it is performed before AWV to show data of common screen */
                $awvData = Questionaires::where('patient_id', $input['patient_id'])->where('program_id', 2)->orderBy('id', 'desc')->first();
                if (!empty($awvData))
                    $awvData->toArray();

                $lastccm = !empty($awvData) ? json_decode($awvData['questions_answers'], true) : [];

                /* Checking the date of service of last performed AWV to calculate the eligibility criteria */
                $last_awv = Questionaires::where('patient_id', $input['patient_id'])->where('program_id', 1)->select('date_of_service')->orderBy('id', 'desc')->first();

                /* if ($last_awv) {
                    $last_awv = Carbon::parse($last_awv['date_of_service']);
                    $diffYears = \Carbon\Carbon::now()->diffInYears($last_awv);

                    if ($diffYears <= 1) {
                        return response()->json(['success' => false, 'errors' => 'AWV is already performed for this patient']);
                    }
                } */
            }

            $patient_data = Patients::where('id', $input['patient_id'])->first();
            $patient_consent = @$patient_data['patient_consent'] ?? "" ;
            $consent_data = @$patient_data['consent_data'] ?? [];

            $coordinator = User::all();

            $response = [
                'success' => true,
                'awv_data' => $lastAwv,
                'last_awv_dos' => @$last_awv['date_of_service'] ?? "",
                'ccm_id' => @$alreadyPerformedCCM['id'] ?? '',
                'diagnosis' => (object)$patient_diseases,
                'patient_consent' => $patient_consent === 1 ? true : false,
                'consent_data' => $consent_data,
                'coordinator' => @$coordinator ?? [],
            ];

            return response()->json($response);
        } catch (\Exception $e) {
            $response = array('success' => false, 'message' => $e->getMessage(), 'line'=>$e->getLine());
        }
    }
  
    private function saveCodes($questionId, $questions, $patientId='', $clinicId)
    {
        $patientDiagnosis = Diagnosis::where('patient_id', $patientId)->get()->toArray();
        $patientDiagnosis = array_column($patientDiagnosis, 'condition');
        $depressionBipolar = [  
                                "F32.A",
                                "F32.0",
                                "F32.1",
                                "F32.2",
                                "F32.3",
                                "F32.4",
                                "F32.5",
                                "F32.81",
                                "F32.89",
                                "F32.9",
                                "F31.0",
                                "F31.10",
                                "F31.11",
                                "F31.12",
                                "F31.13",
                                "F31.2",
                                "F31.30",
                                "F31.31",
                                "F31.32",
                                "F31.4",
                                "F31.5",
                                "F31.60",
                                "F31.61",
                                "F31.62",
                                "F31.63",
                                "F31.64",
                                "F31.70",
                                "F31.71",
                                "F31.72",
                                "F31.73",
                                "F31.74",
                                "F31.75",
                                "F31.76",
                                "F31.77",
                                "F31.78",
                                "F31.81",
                                "F31.89",
                                "F31.9",
                            ];

        $depressionCodeMatched = array_intersect($patientDiagnosis, $depressionBipolar);

        $codes = [];

        $patient = Patients::with("insurance")->where('id', $patientId)->first();
        $insurance_code = @$patient->insurance->provider ?? "";
        $insurance_type = @$patient->insurance->type_id ?? "";

        foreach ($questions as $type => $value) {
            $row = $questions[$type];
            switch ($type) {
                case 'fall_screening':
                    $fallInYear = @$row['fall_in_one_year'] ?? '';
                    $noOfFalls = @$row['number_of_falls'] ?? '';
                    $noOfInjuries = @$row['injury'] ?? '';

                    $codes[] = [
                        '1100F' => ($noOfFalls == "More then one" || $noOfInjuries == "Yes")  ? "true" : "false",
                        '1101F' => ($fallInYear == "No" || ($fallInYear == "One" && $noOfInjuries == "One"))  ? "true" : "false",
                        '3288F' => ($noOfFalls == "One" || $noOfFalls == "More then one")  ? "true" : "false",
                    ];
                    break;

                case 'pain':
                    $pain_felt = $row['pain_felt'];

                    $codes[] = [
                        '1125F' => ($pain_felt != "" && $pain_felt != "None") ? "true" : "false",
                        '1126F' => $pain_felt == "None"  ? "true" : "false",
                    ];
                    break;


                case 'depression_phq9':
                    $depression_score = array_sum($row);

                    $codes[] = [
                        'G8510' => $depression_score < 9 ? "true" : "false",
                        'G8431' => $depression_score > 9 ? "true" : "false",
                        'G9717' => !empty($depressionCodeMatched) ? "true" : "false",
                        'G0444' => !empty($row) ? "true" : "false",
                    ];
                    break;
    
                case 'immunization':
                    $prevanarRecieved = @$row['pneumococcal_vaccine_recieved'] ?? '';
                    $fluRecieved =      @$row['flu_vaccine_recieved'] ?? '';
                    $prevanarRefused =  @$row['pneumococcal_vaccine_refused'] ?? '';
                    $fluVaccineRecieved = @$row['flu_vaccine_recieved_on'] ?? '';
                    $fluVaccineRefused = @$row['flu_vaccine_refused'] ?? '';
                    
                    $codes[] = [
                        //'4040' => $prevanarRecieved =="yes" && $fluVaccineRecieved !="" ? "true" : "false",
                        '4040' => $fluRecieved =="Yes" && $fluVaccineRecieved !="" ? "true" : "false",
                        'G8482' => $fluRecieved =="Yes" ? "true" : "false",
                        'G8483' => $fluVaccineRefused =="Yes"  ? "true" : "false",
                    ];
                    break;
                
                case 'screening':
                    $mammogramDone = @$row['mammogram_done'] ?? '';
                    $colonoscopyDone = @$row['colonoscopy_done'] ?? '';
                    $colonoscopyReportReviewed = @$row['colonoscopy_report_reviewed'] ?? '';
                    $codes[] = [
                        'G9899' => $mammogramDone =="Yes"  ? "true" : "false",
                        '3017F' => $colonoscopyReportReviewed =="0" ? "true" : "false",
                    ];
                    break;
    
                case 'diabetes':
                    $hba1cValue = @$row['hba1c_value'] ?? '';
                    $diabetecEyeExam = @$row['diabetec_eye_exam'] ?? '';
                    $eyeExamReportReviewed = @$row['eye_exam_report_reviewed'] ?? '';
                    $urineMicroalbuminInhibitor = @$row['urine_microalbumin_inhibitor'] ?? '';
                    $ckdStage4 = @$row['ckd_stage_4'] ?? '';
                    $urineMicroalbuminReport = @$row['urine_microalbumin_report'] ?? '';
                    $codes[] = [
                        '3044F' => $hba1cValue < 7 ? "true" : "false",
                        '3046F' => $hba1cValue > 9 ? "true" : "false",
                        '3051F' => $hba1cValue >= 7 && $hba1cValue < 8 ? "true" : "false",
                        '3052F' => $hba1cValue >= 8 && $hba1cValue <= 9 ? "true" : "false",
                        '2022F' => $eyeExamReportReviewed == 1 ? "true" : "false",
                        '4010F' => $urineMicroalbuminInhibitor == 'ARB' || $urineMicroalbuminInhibitor == 'ACE Inhibitor' ? "true" : "false",
                        '3066F' => $ckdStage4 == 'CKD Stage 4' ? "true" : "false",
                        '3060F' => $urineMicroalbuminReport == 'Positive' ? "true" : "false",
                        '3061F' => $urineMicroalbuminReport == 'Negative' ? "true" : "false",
                    ];
                    break;
    
                case 'cholesterol_assessment':
                    $statintypeDosage = @$row['statintype_dosage'] ?? '';
                    $medicalReasonForNostatin1 = @$row['medical_reason_for_nostatin1'] ?? '';
                    $codes[] = [
                        'G9664' => $statintypeDosage!='' ? "true" : "false",
                        'G9781' => $medicalReasonForNostatin1=='yes' ? "true" : "false",
                    ];
                    break;
    
                case 'bp_assessment':
                    $bpValue = @$row['bp_value'] ?? '';
                    $bp_value = explode("/",$bpValue);
                    
                    $codes[] = [
                        '3074F' => (isset($bp_value[0]) && $bp_value[0] < 130) ? "true" : "false",
                        '3075F' => (isset($bp_value[0]) && $bp_value[0] >= 130 && $bp_value[0] < 140) ? "true" : "false",
                        '3078F' => (isset($bp_value[1]) && $bp_value[1] < 80 )? "true" : "false",
                        '3079F' => (isset($bp_value[1]) && $bp_value[1] >= 80 && $bp_value[1] < 90 )? "true" : "false",
                    ];
                    break;
    
                case 'tobacco_use':
                    $avgPackPerYear = @$row['average_packs_per_year'] ?? '';
                    $currentSmoker = @$row['smoked_in_thirty_days'] ?? '';
                    $agreed_ldct = @$row['perform_ldct'] ?? '';
                    $codes[] = [
                        '4004F' => $currentSmoker == "Yes" ? "true" : "false",
                        //else condtion
                        '1036F' => $currentSmoker == "No" || $avgPackPerYear == 0 ? "true" : "false",
                        '99406' => $currentSmoker == "Yes" || $avgPackPerYear == 0 ? "true" : "false",
                        'G0296' => $agreed_ldct == "Yes" ? "true" : "false",
                    ];
                    break;

                case 'alcohol_use':
                    $codes[] = [
                        'G0442' => !empty($row) ? "true" : "false",
                    ];
                    break;

                case 'cognitive_assessment':
                
                    $ca1 = @$row['year_recalled'] ?? '';
                    $ca2 = @$row['month_recalled'] ?? '';
                    $ca3 = @$row['hour_recalled'] ?? '';
                    $ca4 = @$row['reverse_count'] ?? '';
                    $ca5 = @$row['reverse_month'] ?? '';
                    $ca6 = @$row['address_recalled'] ?? '';
                    if($ca1 != "" || $ca2 != "" || $ca3 != "" || $ca4 != "" || $ca5 != "" || $ca6 != "")
                    {
                        $abc = '1122';
                    }
                    else{
                          $abc = '1234';
                    }
                        $codes[] = [
                        '99483' =>  $abc == '1122' ? "true" : "false",
                        
                    ];
                    break;

                case 'weight_assessment':
                    $bmiValue = @$row['bmi_value'] ?? '';
                    
                    $avgPackPerYear = @$row['average_packs_per_year'] ?? '';           
                    $codes[] = [
                        'G8420' => $bmiValue > '18.5' && $bmiValue < '25' ? "true" : "false",
                        'G8417' => $bmiValue >= '25' ? "true" : "false",
                        'G8418' => $bmiValue <= '18.5' ? "true" : "false",
                        //else condtion
                        'G8422' => $bmiValue == '' ? "true" : "false",
                    ];
                    break;
                case 'misc':
                    $timeSpent = @$row['time_spent'] ?? '';
                    $asprinUse = @$row['asprin_use'] ?? "";
                    $behavioralCounselling = @$row['behavioral_counselling'] ? true : false;
                    $highBloodPressure = @$row['high_blood_pressure'] ? true : false;
                    
                    if ($timeSpent || $behavioralCounselling || $highBloodPressure) {
                        $codes[] = [ '99497(33)' => "true"];
                    } else {
                        $codes[] = [ '99497(33)' => "false"];
                    }

                    if ($asprinUse == "Yes") {
                        $codes[] = [ 'G8598' => "true"];
                    } else {
                        $codes[] = [ 'G8598' => "false"];
                    }
                    

                    break;

                case 'medicareOptions':
                    $medicare_option = @$row ?? "";
                    if ($medicare_option == "welcomeMedicare") {
                        $codes[] = ["G0402"=> "true", "G0438"=> "false", "G0439"=> "false"];
                    } elseif ($medicare_option == "initial") {
                        $codes[] = ["G0438"=> "true", "G0402"=> "false", "G0439"=> "false"];
                    } elseif ($medicare_option == "subsequent") {
                        $codes[] = ["G0439"=> "true","G0438"=> "false", "G0402"=> "false"];
                    } elseif ($medicare_option == "") {
                        $codes[] = ["G0439"=> "true","G0438"=> "false", "G0402"=> "false"];
                    }

                    if (!empty($patient)) {
                        $codes[] = [
                            /* Initial */
                            '99385' => ($patient['age'] > 18 && $patient['age'] <= 39 && $medicare_option == "initial") ? "true" : "false", //18-39years
                            '99386' => ($patient['age'] > 39 && $patient['age'] <= 64 && $medicare_option == "initial") ? "true" : "false", //39-64 years
                            
                            '99395' => ($patient['age'] > 18 && $patient['age'] < 39 && $medicare_option == "subsequent") ? "true" : "false", //18-39years
                            '99396' => ($patient['age'] > 39 && $patient['age'] < 64 && $medicare_option == "subsequent") ? "true" : "false", //39-64 years
    
                            'G0446' => $patient['age'] == 65 || $patient['age'] == 66 || $patient['age'] >= 67 ? "true" : "false",
                        ];
                    }

                    break;
    
                default:
                    // code...
                    break;
            }
        }

        /* Check based on insurances */
        if ($insurance_code == "hum-001") {
            $codes[] = [
                "96160" => "true",
                "99397" => "true",
            ];
        } elseif ($insurance_code == "care1st-001" || $insurance_code == "hcarz-001") {
            $codes[] = [
                "G0402" => "false",
                "G0438" => "false",
                "G0439" => "false",
                "99497(33)" => "false",
                "G0444" => "false",
                "G0442" => "false",
                "G0446" => "false",
                "96160" => "false",
                "99397" => "false",
            ];
        }

        // DEFINE DEFUALT CODES 
        $defaultcodes = [
            'G8420' => "false",
            'G8417' => "false",
            'G8418' => "false",
            'G8422' => "false",
            '3074F' => "false",
            '3075F' => "false",
            '3078F' => "false",
            '3079F' => "false",
            '4004F' => "false",
            '1036F' => "false",
            'G8510' => "false",
            'G8431' => "false",
            'G9717' => "false",
            '1100F' => "false",
            '3288F' => "false",
            '1101F' => "false",
            '1125F' => "false",
            '1126F' => "false",
            '4040'  => "false",
            'G8482' => "false",
            'G8483' => "false",
            '3017F' => "false",
            'G9711' => "false",
            'G9899' => "false",
            'G9708' => "false",
            '3044F' => "false",
            '3046F' => "false",
            '3051F' => "false",
            '3052F' => "false",
            '2024F' => "false",
            '3072F' => "false",
            '2022F' => "false",
            '3060F' => "false",
            '3061F' => "false",
            '3066F' => "false",
            '4010F' => "false",
            'G8598' => "false",
            'G9724' => "false",
            'G9664' => "false",
            'G9781' => "false",
            '3066F' => "false",
            '3060F' => "false",
            'G9781' => "false",
            '99213' => "false",
            '99214' => "false",
            '99441' => "false",
            '99442' => "false",
            '99443' => "false",
            'G0101' => "false",
            'G0091' => "false",
            'G0402' => "false",
            '99397' => "false",
            'G0439' => "false",
            '99395' => "false",
            '99396' => "false",
            '99385' => "false",
            '99386' => "false",
            'G0442' => "false",
            'G0446' => "false",
            '99497(33)' => "false",
            'G0444' => "false",
            '99495' => "false",
            '99496' => "false",
            '1111F' => "false",
            '99483' => "false",
            '99406' => "false",
            'G0296' => "false",
        ];

        $superBill = SuperBillCodes::where('question_id',$questionId)->first();
        $defaultcodes = !empty($superBill)?json_decode($superBill['codes'],true):$defaultcodes;

        if(!empty($codes)){
            foreach ($codes as $key => $value) {
                foreach($value as $code => $status) {
                    if(array_key_exists($code, $defaultcodes)){
                        $defaultcodes[$code] = $status;
                    }
                }

            }
        }

        if(empty($superBill)){
            $data = ['question_id'=>$questionId,'codes'=>json_encode($defaultcodes), 'clinic_id'=>$clinicId, 'created_user'=>Auth::id()];
            SuperBillCodes::create($data);
        }else{
            SuperBillCodes::where('question_id',$questionId)->update(['codes'=>json_encode($defaultcodes)]);
        }

        return response()->json($defaultcodes);
    }


    /* Store monthly assessment section from ccm program */
    private function storeMonthlyAssessment($serial_no, $questionnaireId, $patientId, $clinic_id, $programId, $questionnaireData, $patientInfo)
    {
        if (@$questionnaireData['isMonthly'] == "1") {
            $monthlyAssesmentArray = [];
            $monthlyAssessmentSections = ['monthly_assessment', 'depression_phq9', 'obesity', 'copd_assessment', 'ckd_assessment', 'cong_heart_failure', 'cholesterol_assessment', 'hypertension', 'diabetes_mellitus'];
    
            foreach ($questionnaireData as $key => $value) {
                if (in_array($key, $monthlyAssessmentSections)) {
                    $monthlyAssesmentArray[$key] = $questionnaireData[$key];
                }
            }

            $date_ofService = null;
            if (@$questionnaireData['isMonthly'] == "1") {
                $date_ofService = Carbon::parse($questionnaireData['date_of_service']);
            }

            $coordinator_id = @$patientInfo['coordinator_id'] ?? NULL;
    
            $row = [
                'questionnaire_id' => $questionnaireId ?? "",
                'serial_no' => $serial_no ?? "",
                'patient_id' => $patientId ?? "",
                'coordinator_id' => $coordinator_id,
                'clinic_id' =>$clinic_id,
                'program_id' => $programId ?? "",
                'date_of_service' => $date_ofService,
                'monthly_assessment' => json_encode($monthlyAssesmentArray),
                'status' => 'completed',
                'created_at' => Carbon::now(),
            ];

            $response = CcmMonthlyAssessment::create($row);
            return $response;
        }
    }


    /**
     * Fetch unsigned AWV Encounters
     * @param  Request $request
     * @return \Illuminate\Http\Response
     */
    public function unsignedEncounters(Request $request)
    {
        try {
            $authRole = Auth::user()->role;
            $authId = Auth::id();

            $provider = @$request['provider'] ?? "";
            $dateRange = @$request['dateRange'] ?? "";
            $perPage = @$request['page_size'] ?? $this->per_page;

            if ($authRole == '21' || $authRole == '13' || $authRole == '1') {
                $whereClause = [
                    'signed_date'=> Null,
                    'program_id'=> "1",
                ];

                /* Filter By Date Range */
                $whereBetween = "";
                if (!empty($dateRange)) {
                    $whereBetween = [
                        Carbon::parse($dateRange['from'])->format("Y-m-d"),
                        Carbon::parse($dateRange['to'])->format("Y-m-d"),
                    ];
                }
                
                $query = Questionaires::with('patient.insurance')->where($whereClause)
                ->when(!empty($whereBetween), function($query) use ($whereBetween) {
                    $query->whereBetween('date_of_service', $whereBetween);
                })
                ->when(!empty($provider), function ($query) use ($provider) {
                    $query = $query->whereHas('patient', function($q) use ($provider) {
                        $q->where('doctor_id', $provider);
                    });
                })
                ->select('id', 'patient_id', 'serial_no', 'date_of_service', 'status');

                $query = $query->paginate($perPage);
                $total = $query->total();
                $current_page = $query->currentPage();

                $awvEncounters = $query->toArray();
                $awvEncounters = @$awvEncounters['data'] ?? [];

                $providersList = User::where('role', '21')->orWhere('role', '13')->get()->toArray();

                $response = [
                    'success'=> true,
                    'message'=> 'Data Found',
                    'data'=>$awvEncounters,
                    'providers'=>$providersList,
                    'current_page' => $current_page,
                    'total_records' => $total,
                    'per_page'   => $perPage,
                ];
            }
        } catch (\Exception $e) {
            $response = array('success'=> false, 'message'=>$e->getMessage(), 'line'=>$e->getLine());
        }

        return response()->json($response);
    }

    public function completedEncounters(Request $request)
    {
        try {
            $ccm_coordinator = @$request['ccm_coordinator'] ?? "";
            $dateRange = @$request['dateRange'] ?? "";
            $type = @$request['type'] ?? "";
            $pageSize = @$request['page_size'] ?? $this->per_page;

            $whereClause = [
                'program_id'=> "2",
                'status'=> "completed",
            ];

            /* Filter By Date Range */
            $whereBetween = "";
            if (!empty($dateRange)) {
                $whereBetween = [
                    Carbon::parse($dateRange['from'])->format("Y-m-d"),
                    Carbon::parse($dateRange['to'])->format("Y-m-d"),
                ];
            } else {
                $whereBetween = [
                    Carbon::now()->startOfMonth()->format("Y-m-d"),
                    Carbon::today()->format("Y-m-d"),
                ];
            }
            
            $encounters = Questionaires::with('patient.coordinator')->where($whereClause)
            ->where(function($query) use ($ccm_coordinator) {
                if (!empty($ccm_coordinator)) {
                    $query->where('coordinator_id', $ccm_coordinator)
                    ->orWhere(function ($query) use ($ccm_coordinator) {
                        $query->whereNull('coordinator_id')
                            ->whereHas('patient', function ($query) use ($ccm_coordinator) {
                                $query->where('coordinator_id', $ccm_coordinator);
                            });
                    });
                }
            })
            ->where(function($query) use ($whereBetween) {
                $query->whereBetween('date_of_service', $whereBetween);
            })
            ->select('id', 'patient_id', 'coordinator_id', 'serial_no', 'date_of_service', 'status')->get()->toArray();

            $annual_total = count($encounters);
            
            $monthlyencounters = CcmMonthlyAssessment::with('patient.coordinator')->where($whereClause)
            ->where(function($query) use ($ccm_coordinator) {
                if (!empty($ccm_coordinator)) {
                    $query->where('coordinator_id', $ccm_coordinator)
                    ->orWhere(function ($query) use ($ccm_coordinator) {
                        $query->whereNull('coordinator_id')
                            ->whereHas('patient', function ($query) use ($ccm_coordinator) {
                                $query->where('coordinator_id', $ccm_coordinator);
                            });
                    });
                }
            })
            ->where(function($query) use ($whereBetween) {
                if (!empty($whereBetween)) {
                    $query->whereBetween('date_of_service', $whereBetween);
                }
            })
            ->select('id', 'patient_id', 'coordinator_id', 'serial_no', 'date_of_service', 'status')->get()->toArray();

            $monthly_total = count($monthlyencounters);

            $encounters = array_merge($encounters, $monthlyencounters);

            $total = (int)$annual_total + (int)$monthly_total;

            $ccmCoordinatorsList = User::where('role', '23')->get()->toArray();

            $response = [
                'success'=> true,
                'message'=> 'Data Found',
                'data'=>$encounters,
                'ccm_coordinators'=>$ccmCoordinatorsList,
                'current_page' => @$current_page ?? "",
                'total_records' => $total,
                'per_page'   => $this->per_page,
            ];
        } catch (\Exception $e) {
            $response = array('success'=> false, 'message'=>$e->getMessage(), 'line'=>$e->getLine());
        }

        return response()->json($response);
    }


    
    /**
     * This function fetches billable records of patients with a diagnosis and tasks that have a total
     * time of at least 1200 seconds.
     * 
     * @param Request request The  parameter is an instance of the Request class, which
     * contains the HTTP request information such as headers, parameters, and input data. It is used to
     * retrieve data from the client-side and pass it to the server-side for processing.
     * 
     * @return a JSON response containing an array with the key "data". The value of "data" is the
     * result of a query that retrieves CcmMonthlyAssessment records with related CcmTask and Patient
     * records, filtered by CcmTask records that have a total task time of at least 1200 seconds (20
     * minutes). If an exception is caught during the execution of the function, the
     */
    public function fetchBillables(Request $request)
    {
        try {

            $whereBetween = [];
            if ($request->has('startDate') && $request->has('endDate')) {
                $whereBetween = [
                    Carbon::parse($request['startDate'])->format("Y-m-d"),
                    Carbon::parse($request['endDate'])->format("Y-m-d"),
                ];
            } else {
                $whereBetween = [
                    Carbon::now()->startOfMonth()->format("Y-m-d"),
                    Carbon::today()->format("Y-m-d"),
                ];
            }

            $data = CcmTasks::with('annualAssessment.patient.diagnosis','monthlyAssessment.patient.diagnosis')
                ->when(!empty($whereBetween), function ($q) use ($whereBetween) {
                    $q->whereBetween('date_of_service', $whereBetween);
                })->orderBy('id', 'DESC')->get()->toArray();

            $list = [];
            foreach ($data as $key => $value) {
                $uniqueKey = $value['annual_encounter_id'].'_'.$value['date_of_service'];
                if (!in_array($uniqueKey, array_column($list, 'id'))) {
                    $serial_no = !empty($value['monthly_encounter_id']) ? $value['monthly_assessment']['serial_no'] : $value['annual_assessment']['serial_no'];
                    $patient_name = !empty($value['monthly_encounter_id']) ? $value['monthly_assessment']['patient']['name'] : $value['annual_assessment']['patient']['name'];
                    $patient_age = !empty($value['monthly_encounter_id']) ? $value['monthly_assessment']['patient']['age'] : $value['annual_assessment']['patient']['age'];
                    $patient_dob = !empty($value['monthly_encounter_id']) ? $value['monthly_assessment']['patient']['dob'] : $value['annual_assessment']['patient']['dob'];
                    $diagnosis = !empty($value['monthly_encounter_id']) ? array_column($value['monthly_assessment']['patient']['diagnosis'], 'condition') : array_column($value['annual_assessment']['patient']['diagnosis'], 'condition');
                    $totalTime = 0;

                    $seconds= strtotime($value['task_time']) - strtotime('00:00:00');

                    $list[] = [
                        'id' => $uniqueKey,
                        'serial_no' => $serial_no,
                        'patient_name' => $patient_name,
                        'patient_age' => $patient_age,
                        'patient_dob' => $patient_dob,
                        'date_of_service' => $value['date_of_service'],
                        'diagnosis' => $diagnosis,
                        'total_seconds' => $seconds,
                        'total_time' =>  date("H:i:s",$seconds),
                    ];
                } else {
                    $item = array_filter($list, function($record) use ($uniqueKey) {
                        return $record['id'] == $uniqueKey;
                    });

                    $index = array_keys($item)['0'];
                    
                    $item = reset($item);
                    $seconds = strtotime($value['task_time']) - strtotime('00:00:00');
                    $totalSeconds = (int)$item['total_seconds'] + $seconds;

                    $list[$index]['total_seconds'] = $totalSeconds;
                    $list[$index]['total_time'] = date("H:i:s",$totalSeconds);
                }
            }

            
            $data = collect($list)->where('total_seconds', '>=', '1200')->all();
            // $data = reset($data);
            $listData = [];
            array_push($listData, ...$data);
            $response = [
                'success'=> true,
                'data' => $listData,
            ];
        } catch (\Exception $e) {
            $response = array('success'=> false, 'message'=>$e->getMessage(), 'line'=>$e->getLine());
        }

        return response()->json($response);
    }


    public function careGap($input,$question,$id = "",$check = false)
    {
        try {
            $patientId = $input['patient_id'];
            $patient_data = Patients::with('insurance')->where('id',$patientId)->first();


            $clinic_id = $patient_data->clinic_id;
            $insurance_info = $patient_data->insurance_id;
            $insuranceData = @$patient_data->insurance ?? "";



            $careGapData['member_id'] = $patient_data->member_id;
            $careGapData['clinic_id'] = $clinic_id;
            $careGapData['insurance_id'] = $insurance_info;
            $careGapData['patient_id'] = $patientId;
            $careGapData['doctor_id '] = $patient_data->doctor_id;

            $insuranceProvider = $insuranceData->provider;

            $gapTable = "";
            switch ($insuranceProvider) {
                case 'hcpw-001':
                    $gapTable = new CareGaps;
                    break;
                case 'hum-001':
                    $gapTable = new HumanaCareGaps;
                    break;
                case 'med-arz-001':
                    $gapTable = new MedicareArizonaCareGaps;
                    break;
                case 'aet-001':
                    $gapTable = new AetnaMedicareCareGaps;
                    break;
                case 'allwell-001':
                    $gapTable = new AllwellMedicareCareGaps;
                    break;
                case 'hcarz-001':
                    $gapTable = new HealthchoiceArizonaCareGaps;
                    break;
                case 'uhc-001':
                    $gapTable = new UnitedHealthcareCareGaps;
                    break;
                default:
                    break;
            }

            // Fetching Existing
            $caregap_row = $gapTable->where('patient_id',$patientId)->latest()->first();

            $caregap_row = !empty($caregap_row) ? $caregap_row->toArray() : "";

            $existingBcsStatus = !empty($caregap_row['breast_cancer_gap_insurance']) ? $caregap_row['breast_cancer_gap_insurance'] : "";
            $existingColStatus = !empty($caregap_row['colorectal_cancer_gap_insurance']) ? $caregap_row['colorectal_cancer_gap_insurance'] : "";

            if(isset($question['screening'])){
                $screening = @$question['screening'] ?? [];

                //Breast cancer Screening
                $mammogram = @$screening['mammogram_done'] ?? '';
                if($mammogram == 'Yes' && $existingBcsStatus != 'N/A') {
                    $mammogram_done_on = @$screening['mammogram_done_on'] ?? "";

                    // Update gap status as per month criteria of Mommogram
                    if(!empty($mammogram_done_on)){
                        // Parse the date string using Carbon
                        $currentDate = Carbon::now()->endOfMonth();
                        $mammogramDate = Carbon::createFromFormat('m/Y', $mammogram_done_on)->endOfMonth();

                        // Calculate the month difference
                        $monthDifference = $currentDate->diffInMonths($mammogramDate);

                        $careGapData["breast_cancer_gap"] = $monthDifference <= 27 ? "Compliant" : (!empty($caregap_row['breast_cancer_gap']) ? $caregap_row['breast_cancer_gap'] : "");
                        $careGapData["breast_cancer_gap_insurance"] = $existingBcsStatus;
                    }else{
                        $careGapData["breast_cancer_gap"] = !empty($caregap_row['breast_cancer_gap']) ? $caregap_row['breast_cancer_gap'] : "";
                        $careGapData["breast_cancer_gap_insurance"] = $existingBcsStatus;
                    }
                } else {
                    $careGapData["breast_cancer_gap"] = !empty($caregap_row['breast_cancer_gap']) ? $caregap_row['breast_cancer_gap'] : "";
                    $careGapData["breast_cancer_gap_insurance"] = $existingBcsStatus;
                }

                //Colon Cancer Screening
                $colonScreening = @$screening['colonoscopy_done'] ?? '';
                $testType = @$screening['colon_test_type'] ?? "";
                $colonDate = @$screening['colonoscopy_done_on'] ?? "";
                if ($colonScreening == 'Yes' && $existingColStatus != 'N/A') {
                    if (!empty($testType)) {
                        if ($testType == 'Colonoscopy') {
                            $currentDate = Carbon::now()->endOfMonth();
                            $colonDate = Carbon::createFromFormat('m/Y', $colonDate)->endOfMonth();

                            // Calculate the year difference
                            $yearDifference = $currentDate->diffInYears($colonDate);
                            
                            // Updating gap status
                            $careGapData["colorectal_cancer_gap"] = $yearDifference <= 10 ? "Compliant" : (!empty($caregap_row['colorectal_cancer_gap']) ? $caregap_row['colorectal_cancer_gap'] : "");
                        } elseif ($testType == 'FIT Test') {
                            // getting year of current Date and Fit Date year
                            $currentDateYear = Carbon::now()->year;
                            $colonDateYear = Carbon::createFromFormat('m/Y', $colonDate)->endOfMonth()->year;

                            // Updating gap status
                            $careGapData["colorectal_cancer_gap"] = $currentDateYear == $colonDateYear ? "Compliant" : (!empty($caregap_row['colorectal_cancer_gap']) ? $caregap_row['colorectal_cancer_gap'] : "");
                        } else if ($testType == 'Cologuard') {
                            $currentDate = Carbon::now()->endOfMonth();
                            $colonDate = Carbon::createFromFormat('m/Y', $colonDate)->endOfMonth();

                            // Calculate the year difference
                            $yearDifference = $currentDate->diffInYears($colonDate);

                            // Updating gap status
                            $careGapData["colorectal_cancer_gap"] = $yearDifference <= 2 ? "Compliant" : (!empty($caregap_row['colorectal_cancer_gap']) ? $caregap_row['colorectal_cancer_gap'] : "");
                            $careGapData["colorectal_cancer_gap_insurance"] = $existingColStatus;
                        }
                    }
                } else {
                    $careGapData["colorectal_cancer_gap"] = !empty($caregap_row['colorectal_cancer_gap']) ? $caregap_row['colorectal_cancer_gap'] : "";
                    $careGapData["colorectal_cancer_gap_insurance"] = $existingColStatus;
                }

                $currentYear = Carbon::now()->year;

                $source = !empty($caregap_row['gap_year']) && ($caregap_row['gap_year'] == $currentYear && $caregap_row['source'] == 'CareGap_File') ? 'CareGap_File' : 'Questionaire Surveys';
                $careGapData["source"] = $source;
                
                $careGapData["gap_year"] = !empty($caregap_row['gap_year']) ? $caregap_row['gap_year'] : $currentYear;
                $careGapData["created_user"] = Auth::id();
                $careGapData["q_id"] =  $id;
                

                
                if (!empty($caregap_row) && $caregap_row['gap_year'] == $currentYear) {
                    // unset($careGapData["q_id"]);
                    unset($careGapData['member_id']);
                    unset($careGapData['clinic_id']);
                    unset($careGapData['insurance_id']);
                    unset($careGapData['patient_id']);
                    unset($careGapData['doctor_id ']);
                    $gapTable->where('id',$caregap_row['id'])->update($careGapData);
                } else {
                    $careGapData['gap_year'] = $currentYear;
                    $gapTable->create($careGapData);
                }

                $response = [
                    'success'=> true,
                    'data' => $careGapData,
                ];
            }else{
                $response = [
                    'success'=> false,
                    //'data' => $careGapData,
                ];
            }
        } catch (\Exception $e) {
            $response = array('success'=> false, 'message'=>$e->getMessage(), 'line'=>$e->getLine());
        }

        return $response;
        
    }
}
