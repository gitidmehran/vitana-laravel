<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\SuperBillCodesController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;


use App\Models\Questionaires;
use App\Models\CcmMonthlyAssessment;
use App\Models\Patients;
use App\Models\Doctors;
use App\Models\Diagnosis;
use App\Models\Programs;
use App\Models\Insurances;
use App\Models\User;
use App\Models\SuperBillCodes;
use Validator,Session,Config,PDF,Auth,Storage;
use Carbon\Carbon;

class CareplanController extends Controller
{
    protected $view = "reports.";
    protected $singular = "Questionaire Survey";

    public function index(Request $request,$id)
    {
        try {
            $data = $this->awvCareplanReport($id);
            $response = array('success'=>true, 'data'=>$data);
        } catch (\Exceptional $e) {
            $response = array('success'=>false,'message'=>$e->getMessage());
        }
        
        return response()->json($response);
    }


    /* Returning data with outcomes on behalf of formdata from the program 
    ** against the serial no*/
    private function awvCareplanReport($id)
    {
        $row = Questionaires::with('patient:id,first_name,mid_name,last_name,gender,age,dob,insurance_id', 'program:id,name,short_name','clinic:id,name,short_name')->where('id', $id)->first()->toArray();
        $questions_answers = json_decode($row['questions_answers'], true);

        $doctor = User::where(function ($query) {
            $query->where('role', 21)
            ->orWhere('role', 13);
        })->where('id', $row['doctor_id'])->select('first_name', 'mid_name', 'last_name', 'id')->first();

        if (!empty($doctor)) {
            $row['doctor'] = @$doctor['first_name'] . ' ' . @$doctor['mid_name'] . ' ' . @$doctor['last_name'];
        }

        $insuranceId = $row['patient']['insurance_id'];
        $insuranceName = Insurances::where('id', $insuranceId)->pluck('name');
        $row['patient']['insurance_name'] = @$insuranceName['0'] ?? "";

        if (Auth::id()) {
            $signer = User::where(function ($query) {
                $query->where('role', '21')
                    ->orWhere('role', '13');
            })
            ->where('id', Auth::id())
            ->first();

            if ($signer){
                $row['signer_doctor'] = $signer['id'];
            }
        }

        $primary_care_physician = Patients::with('doctor:id,first_name,last_name')->where('id', $row['patient_id'])->first()->toArray();
        if (!empty($primary_care_physician['doctor'])) {
            $row['primary_care_physician'] = $primary_care_physician['doctor']['name'];
        }

        $nextYeardue = Carbon::create($row['date_of_service'])->addYear(1)->format('m/d/Y');

        // PHYSICAL HEALTH - FALL SCREENING
        $fallScreeningOutcomes = $this->fallscreening($questions_answers['fall_screening']?? []);

        // DEPRESSION PHQ-9 Outcome
        $depression_OutComes = $this->depressionphq_9($questions_answers['depression_phq9'] ?? [], '', '' ,'');
        
        /* General health screening careplan including
        ** HIGH STRESS, GENERAL HEALTH SOCIAL AND EMOTIONAL SUPPORT, PAIN */
        $general_health_screening = $this->generalhealthscreening($questions_answers['high_stress']??[], $questions_answers['general_health']??[], $questions_answers['social_emotional_support']??[], $questions_answers['pain']??[]);

        
        // High Stress
        $high_stress = $general_health_screening['high_stress'] ?? [];
        
        // General Health
        $general_health = $general_health_screening['general_health'] ?? [];
        
        // Social/Emotional Supoort
        $social_emotional_support = $general_health_screening['social_emotional_support'] ?? [];
       
        // Pain
        $pain = $general_health_screening['pain'] ?? [];

        // COGNITIVE ASSESSMENT
        $cognitiveOutcomes = $this->cognitive_assessment($questions_answers['cognitive_assessment']?? []);


        /* Physical Activity Careplan */
        $physicalActivitiesOutComes = $this->physical_activity($questions_answers['physical_activities']?? []);


        // ALCOHOL USE Careplan
        $alcoholOutComes = $this->alcohol_use_screening($questions_answers['alcohol_use'] ?? [], $row);

        // TOBACOO USE OUTCOMES FILTER
        $tobaccoOutComes = $this->tobacco_use_screening($questions_answers['tobacco_use'] ?? []);

        // SEATBELT TEXT Filter
        $seatBelt = [];
        $seatBelt['outcome'] = (@$questions_answers['seatbelt_use']['wear_seat_belt'] == "Yes") ? 'Patient always uses seatbelt in the car.' : 'Patient counseled on the use of seat belt in the car.';
        $seatBelt['flag'] = (@$questions_answers['seatbelt_use']['wear_seat_belt'] == "No") ? true : false;

        // IMMUNIZATION
        $immunizationOutcomes = $this->immunization_screening($questions_answers['immunization'] ?? []);

        // SCREENING
        $screeningOutcomes = $this->screening($questions_answers['screening'] ?? [], $row);

        // DIABETES
        $diabetesOutcomes = $this->diabetes_screening($questions_answers['diabetes'] ?? []);

        // CHOLESTEROL ASSESSMENT
        $cholesterol_outcome = $this->cholesterol_screening($questions_answers['cholesterol_assessment'] ?? []);

        /* Blooad Pressure and Weight Screening */
        $bp_and_weight = $this->bp_and_weight_screening($questions_answers['bp_assessment'] ?? [], $questions_answers['weight_assessment'] ?? []);

        // BP ASSESSMENT
        $bpAssessment = $bp_and_weight['bp_assessment'] ?? [];
        
        // WEIGHT ASSESSMENT
        $weightAssessment = $bp_and_weight['weight_assessment'] ?? [];

        $miscellaneous = [];
        $height = $weigth = '';
        if (!empty($questions_answers['misc'])) {
            $miscellaneous = $questions_answers['misc'];
            $height = $questions_answers['misc']['height'] ?? "";
            $weigth = $questions_answers['misc']['weight'] ?? "";
        } else if (!empty($questions_answers['miscellaneous'])) {
            $miscellaneous = $questions_answers['miscellaneous'];
        }

        $data = [
            'page_title' => 'AWV Care plan',
            'row' => $row,
            'height' => $height,
            'weight' => $weigth,
            'next_year_due' => $nextYeardue,
            'fall_screening' => $fallScreeningOutcomes ?? [],
            'depression_out_comes' => $depression_OutComes ?? [],
            'high_stress' => $high_stress ?? [],
            'general_health' => $general_health ?? [],
            'social_emotional_support' => $social_emotional_support ?? [],
            'pain' => $pain ?? [],
            'cognitive_assessment' => $cognitiveOutcomes ?? [],
            'physical_out_comes' => $physicalActivitiesOutComes ?? [],
            'alcohol_out_comes' => $alcoholOutComes ?? [],
            'tobacco_out_comes' => $tobaccoOutComes ?? [],
            'seatbelt_use' => $seatBelt ?? [],
            'immunization' => $immunizationOutcomes ?? [],
            'screening' => $screeningOutcomes ?? [],
            'diabetes' => $diabetesOutcomes ?? [],
            'cholesterol_assessment' => $cholesterol_outcome ?? [],
            'bp_assessment' => $bpAssessment ?? [],
            'weight_assessment' => $weightAssessment ?? [],
            'miscellaneous' => $miscellaneous ?? []
        ];

        return $data;
    }



    public function ccmCareplanReport(Request $request, $id, $download="")
    {
        $input = $request->all();
        $child_id = @$input['monthly_assessment_id'] ?? "";
        $row = Questionaires::with('patient:id,first_name,mid_name,last_name,gender,age,dob', 'program:id,name,short_name')
            ->with('monthlyAssessment', function ($query) use ($child_id) {
                if (!empty($child_id)) {
                    $query->where('id', $child_id);
                }
            })->where('id', $id)->first()->toArray();

        $questions_answers = json_decode($row['questions_answers'], true);

        $patient = $row['patient'];
        $program = $row['program'];
        $dateofService = $row['date_of_service'];

        /* If input month filter availabe then show careplan as per month filter */
        $careplanType = @$input['monthly_careplan'] == 1 ? "monthly" : "general";
        $filter_month = @$input['filter_month'] ?? "";

        /* If monthly careplan is selected then fetching month from dateofservice */
        if (@$input['monthly_careplan'] == 1) {
            $filter_month = Carbon::parse($input['date_of_service'])->format('m');
        }

        $primary_care_physician = Patients::with('doctor:id,first_name,last_name')->where('id', $row['patient_id'])->first()->toArray();
        if (!empty($primary_care_physician['doctor'])) {
            $row['primary_care_physician'] = $primary_care_physician['doctor']['name'];
        }

        if (Auth::id()) {
            $signer = User::where(['role' => 21, 'id' => Auth::id()])->select('id')->first();
            if ($signer){
                $row['signer_doctor'] = $signer['id'];
            }
        }

        //Fall Screening
        $fallScreeningOutcomes = $this->fallscreening($questions_answers['fall_screening'] ?? []);

        // Cognitive Assesment 
        $cognitiveOutcomes = $this->cognitive_assessment($questions_answers['cognitive_assessment'] ?? []);

        // Caregiver Assesment
        $caregiver_assesment_outcomes = $this->caregiverAssesment($questions_answers);

        // Other Provider
        $other_providers_outcome = $this->otherProvider($questions_answers);

        //Immunization
        $immunization = $this->immunization_screening($questions_answers['immunization'] ?? []);

        //Screening
        $screening = $this->screening($questions_answers['screening'] ?? [], $row);

        
        //General Assesment
        $general_assessment_outcomes = $this->generalAssesment($questions_answers, $filter_month, $careplanType);

        // return response()->json($row);

        if (!empty($row['monthly_assessment'])) {
            $monthly_assessment = json_decode($row['monthly_assessment']['monthly_assessment'], true);
            
            /* Depression Function */
            $depression_data = @$monthly_assessment['depression_phq9'] ?? [];
            $depression_OutComes = $this->depressionphq_9($depression_data, 1,$filter_month, $careplanType);
            
            /* Hypercholesterolemia Function */
            $hypercholestrolemia_data = @$monthly_assessment['cholesterol_assessment'] ?? [];
            $hypercholestrolemia_outcomes = $this->hypercholestrolemiaAssessment($hypercholestrolemia_data, $filter_month, $careplanType);

            /* Diabetes Militus Calculation */
            $diabetesMilitus_data = @$monthly_assessment['diabetes_mellitus'] ?? [];
            $diabetes_outcome = $this->diabetesMilitusAssessment($diabetesMilitus_data, $filter_month, $careplanType);
            
            /* COPD Calculation */
            $copd_data = @$monthly_assessment['copd_assessment'] ?? [];
            $copd_outcomes = $this->copdAssessment($copd_data, $filter_month, $careplanType);
            
            /* CKD Calculation */
            $ckd_data = @$monthly_assessment['ckd_assessment'] ?? [];
            $ckd_outcomes = $this->ckdAssessment($ckd_data, $filter_month, $careplanType);
            
            /* Hypertenstion Calculation */
            $hypertenstion_data = @$monthly_assessment['hypertension'] ?? [];
            $hypertension_outcomes = $this->hypertensionAssessment($hypertenstion_data, $filter_month, $careplanType);
            
            /* Obesity Calculation */
            $obesity_data = @$monthly_assessment['obesity'] ?? [];
            $obesity_outcomes = $this->obesityAssessment($obesity_data, $filter_month, $careplanType);
            
            /* Obesity Calculation */
            $chf_data = @$monthly_assessment['cong_heart_failure'] ?? [];
            $chf_outcomes = $this->chfAssessment($chf_data, $filter_month, $careplanType);

        }


        /* Return Diagnosis to show only Disease section in monthlyCareplan */
        $patient_diseases = [
            "Depression" => false,
            "CongestiveHeartFailure" => false,
            "ChronicObstructivePulmonaryDisease" => false,
            "CKD" => false,
            "DiabetesMellitus" => false,
            "Hypertensions" => false,
            "Obesity" => false,
            "Hypercholesterolemia" => false,
            "anemia" => false,
            "hyperthyrodism" => false,
            "asthma" => false,
        ];
        
        $diagnosis = [
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

        $patientDiagnosis = Diagnosis::where('patient_id', $row['patient_id'])->get()->toArray();

        $chronic_diseases = Config::get('constants')['chronic_diseases'];
        
        
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
                $patient_diseases[$key] = true;
                $diagnosis[$key] = "true";
            }
        }

        $response = [
            'row' => $row,
            'fall_screening' => $fallScreeningOutcomes ?? [],
            'depression_out_comes' => $depression_OutComes ?? [],
            'cognitive_assessment' => $cognitiveOutcomes ?? [],
            'caregiver_assesment_outcomes' => $caregiver_assesment_outcomes ?? [],
            'other_providers_outcome' => $other_providers_outcome ?? [],
            'hypercholestrolemia_outcomes' => $hypercholestrolemia_outcomes ?? [],
            'general_assessment_outcomes' => $general_assessment_outcomes ?? [],
            'monthly_assessment_outcomes' => $monthly_assessment_outcomes ?? [],
            'screening' => $screening ?? [],
            'immunization' => $immunization ?? [],
            'ckd_outcomes' => $ckd_outcomes ?? [],
            'copd_outcomes' => $copd_outcomes ?? [],
            'diabetes_outcome' => $diabetes_outcome ?? [],
            'hypertension_outcomes' => $hypertension_outcomes ?? [],
            'obesity_outcomes' => $obesity_outcomes ?? [],
            'chf_outcomes' => $chf_outcomes ?? [],
            'chronic_disease'=>$patient_diseases ?? [],
            'diagnosis'=>$diagnosis ?? [],
            'filter_month'=>$filter_month,
        ];

        if ($download == "1") {
            return $response;
        } else {
            return response()->json($response);
        }
    }


    /* Fall screening Careplan */
    private function fallscreening($fallscreening)
    {
        $fallScreeningOutcomes = [];
        if (!empty($fallscreening)) {

            $fallinpastYear = !empty($fallscreening['fall_in_one_year']) ? $fallscreening['fall_in_one_year'] : "";
            $noOfFall = !empty($fallscreening['number_of_falls']) ? $fallscreening['number_of_falls'] : 0;
            $fallInjury = !empty($fallscreening['injury']) ? $fallscreening['injury'] : '';
            $physicalTherapy = !empty($fallscreening['physical_therapy']) ? $fallscreening['physical_therapy'] : '';
            $blackingOut = !empty($fallscreening['blackingout_from_bed']) ? $fallscreening['blackingout_from_bed'] : '';
            $assistanceDevice = !empty($fallscreening['assistance_device']) ? $fallscreening['assistance_device'] : '';
            $unsteady = !empty($fallscreening['unsteady_todo_things']) ? $fallscreening['unsteady_todo_things'] : '';

            /* Fall and no of falls */
            $fallStatement = "";
            if ($fallinpastYear == 'Yes') {
                $number_of_fall = $noOfFall != 0 ? $noOfFall . ' fall in the last 1 year' : '';
                $fall_with_injury = "";
                if ($fallInjury != "" && $fallInjury == "Yes") {
                    $fall_with_injury = ', with injury';
                } else if ($fallInjury != "" && $fallInjury == "No") {
                    $fall_with_injury = ',with no injury';
                }
                $fallStatement = $number_of_fall.$fall_with_injury;
            } else if ($fallinpastYear == 'No') {
                $fallStatement = "No fall in last 1 year";
            }

            /* Outcome  */
            $blackingOutStatement = "";
            if ($blackingOut == "Yes" && $unsteady == "Yes") {
                $blackingOutStatement = '. Patient feels blacking out and is unsteady with ambulation';
            } else if ($blackingOut == "Yes" && $unsteady == "No") {
                $blackingOutStatement = '. Patient feels blacking out';
            } else if ($blackingOut == "No" && $unsteady == "Yes") {
                $blackingOutStatement = '. Patient is unsteady with ambulation';
            }

            $assistiveDeviceStatement = "";
            if ($blackingOutStatement != "" && $assistanceDevice != "" && $assistanceDevice != 'None') {
                $assistiveDeviceStatement = ', will continue to use ' . $assistanceDevice . ' for mobilization';
            } else if ($blackingOutStatement == "" && $assistanceDevice != "" && $assistanceDevice != 'None') {
                $assistiveDeviceStatement = '. Patient is using ' . $assistanceDevice . ' for mobilization';
            } else if ($assistanceDevice != "" && $assistanceDevice == 'None') {
                $assistiveDeviceStatement = '. Patient is not using any assistive device';
            }


            $physical_therapy_refferal = "";
            if ($physicalTherapy != "") {
                if ($physicalTherapy == 'Referred') {
                    $physical_therapy_refferal = ". Physical therapy referral for muscle strengthening, gain training & balance, and home safety checklist provided.";
                } else if ($physicalTherapy == 'Already receiving') {
                    $physical_therapy_refferal = ". Already receiving physical therapy.";
                } else {
                    $physical_therapy_refferal = ". Patient refused Physical therapy.";
                }
            }
            
            $fallScreeningOutcomes['outcome'] = $fallStatement.$blackingOutStatement.$assistiveDeviceStatement.$physical_therapy_refferal;
        }

        return $fallScreeningOutcomes;
    }


    /* Depression Careplan */
    private function depressionphq_9($depression, $is_ccm=Null, $filter_month, $careplanType)
    {
        $depression_OutComes = [];
        if (!empty($depression)) {
            unset($depression['completed']);

            $depression_score= array_sum(array_filter($depression, function ($key) {
                return stripos($key, '_date') === false;
            }, ARRAY_FILTER_USE_KEY));

            if ($is_ccm === 1) {
                switch ($depression_score) {
                    /* Scenario 1 */
                    case ($depression_score < 5):
                        $depression_OutComes['prognosis'] = "Good";
                        $depression_OutComes['assessment'] = "PHQ-9 score is ".$depression_score.". Depression is in remission. Patient advised to continue treatment and have routine follow-up with PCP for evaluation.";
                        break;
                    
                    /* Scenario 2 */
                    case ($depression_score >= 5 && $depression_score <= 9):
                        $depression_OutComes['prognosis'] = "Fair";
                        $depression_OutComes['assessment'] = "PHQ-9 score is ".$depression_score.". Patient had mild depression. Patient advised to continue treatment and have routine follow-up with PCP for evaluation.";
                        break;
                    
                    /* Scenario 3 */
                    case ($depression_score >= 10 && $depression_score <= 14):
                        $depression_OutComes['prognosis'] = "Fair";
                        $depression_OutComes['assessment'] = "PHQ-9 score is ".$depression_score.". Patient has Moderate Depression. Patient advised to follow up with PCP or psychiatrist to be reevaluated for treatment options.";
                        break;
                    
                    /* Scenario 4 */
                    case ($depression_score >= 15 && $depression_score <= 19):
                        $depression_OutComes['prognosis'] = "Guarded";
                        $depression_OutComes['assessment'] = "PHQ-9 score is ".$depression_score.". Patient has moderately severe depression. Patient advised to walk-in to the clinic as soon as possible to be evaluated for treatment options.";
                        break;
                    
                    /* Scenario 5 */
                    case ($depression_score >= 20):
                        $depression_OutComes['prognosis'] = "Guarded";
                        $depression_OutComes['assessment'] = "PHQ-9 score is ".$depression_score.". Patient has severe depression. Patient advised to walk-in to the clinic as soon as possible to be evaluated for treatment options.";
                        break;
                    
                    default:
                        $depression_OutComes['prognosis'] = "";
                        $depression_OutComes['assessment'] = "";
                        break;
                }

                
                // GOALS and Tasks
                if ($filter_month != "") {
                    $depression_goals = $this->filterMonthlyAssessment($depression, $filter_month, $careplanType);
        
                    if (is_array($depression_goals)) {
                        foreach ($depression as $key => $value) {
                            if ((strpos($key, "start_date") || strpos($key, "end_date")) && !array_key_exists($key, $depression_goals)) {
                                unset($depression[$key]);
                            }
                        }
                    }
                }

                // GOAL 1
                $understand_about_disease_start_date = @$depression['understand_about_disease_start_date'] ?? "";
                $understand_about_disease_end_date = @$depression['understand_about_disease_end_date'] ?? "";
                $understand_about_disease_status = $this->calculateStatus($understand_about_disease_start_date, $understand_about_disease_end_date);
                
                $monitor_phq9_start_date = @$depression['monitor_phq9_start_date'] ?? "";
                $monitor_phq9_end_date = @$depression['monitor_phq9_end_date'] ?? "";
                $monitor_phq9_status = $this->calculateStatus($monitor_phq9_start_date, $monitor_phq9_end_date);
                
                $advantages_of_phq9_start_date = @$depression['advantages_of_phq9_start_date'] ?? "";
                $advantages_of_phq9_end_date = @$depression['advantages_of_phq9_end_date'] ?? "";
                $advantages_of_phq9_status = $this->calculateStatus($advantages_of_phq9_start_date, $advantages_of_phq9_end_date);

                $depression_OutComes['goal1_status'] = "";
                $goal1_task_status = [
                    $understand_about_disease_status,
                    $monitor_phq9_status,
                    $advantages_of_phq9_status,
                ];
                $counts = array_count_values($goal1_task_status);

                if (@$counts['Completed'] === sizeof($goal1_task_status)) {
                    $depression_OutComes['goal1_status'] = "Completed";
                } else if (@$counts['Completed'] > 0 && @$counts['Completed'] < sizeof($goal1_task_status)) {
                    $depression_OutComes['goal1_status'] = "In progress";
                } else if (@$counts['Started'] > 0) {
                    $depression_OutComes['goal1_status'] = "In progress";
                }

                // TASKS DATES AND STATUS
                $depression_OutComes['understand_about_disease_start_date'] = $understand_about_disease_start_date;
                $depression_OutComes['understand_about_disease_end_date'] = $understand_about_disease_end_date;
                $depression_OutComes['understand_about_disease_status'] = $understand_about_disease_status;

                $depression_OutComes['monitor_phq9_start_date'] = $monitor_phq9_start_date;
                $depression_OutComes['monitor_phq9_end_date'] = $monitor_phq9_end_date;
                $depression_OutComes['monitor_phq9_status'] = $monitor_phq9_status;

                $depression_OutComes['advantages_of_phq9_start_date'] = $advantages_of_phq9_start_date;
                $depression_OutComes['advantages_of_phq9_end_date'] = $advantages_of_phq9_end_date;
                $depression_OutComes['advantages_of_phq9_status'] = $advantages_of_phq9_status;
                // GOAL 1 ENDS

                // GOAL 2
                $effect_with_other_problems_start_date = @$depression['effect_with_other_problems_start_date'] ?? "";
                $effect_with_other_problems_end_date = @$depression['effect_with_other_problems_end_date'] ?? "";
                $effect_with_other_problems_status = $this->calculateStatus($effect_with_other_problems_start_date, $effect_with_other_problems_end_date);

                $depression_OutComes['goal2_status'] = "";
                $goal2_task_status = [
                    $effect_with_other_problems_status,
                ];
                $counts = array_count_values($goal2_task_status);

                if (@$counts['Completed'] === sizeof($goal2_task_status)) {
                    $depression_OutComes['goal2_status'] = "Completed";
                } else if (@$counts['Completed'] > 0 && @$counts['Completed'] < sizeof($goal2_task_status)) {
                    $depression_OutComes['goal2_status'] = "In progress";
                } else if (@$counts['Started'] > 0) {
                    $depression_OutComes['goal2_status'] = "In progress";
                }

                // TASKS DATES AND STATUS
                $depression_OutComes['effect_with_other_problems_start_date'] = $effect_with_other_problems_start_date;
                $depression_OutComes['effect_with_other_problems_end_date'] = $effect_with_other_problems_end_date;
                $depression_OutComes['effect_with_other_problems_status'] = $effect_with_other_problems_status;
                // GOAL 2 ENDS


                // GOAL 3
                $relieve_depression_start_date = @$depression['relieve_depression_start_date'] ?? "";
                $relieve_depression_end_date = @$depression['relieve_depression_end_date'] ?? "";
                $relieve_depression_status = $this->calculateStatus($relieve_depression_start_date, $relieve_depression_end_date);
                
                $understand_cbt_start_date = @$depression['understand_cbt_start_date'] ?? "";
                $understand_cbt_end_date = @$depression['understand_cbt_end_date'] ?? "";
                $understand_cbt_status = $this->calculateStatus($understand_cbt_start_date, $understand_cbt_end_date);
                
                $physical_activity_importance_start_date = @$depression['physical_activity_importance_start_date'] ?? "";
                $physical_activity_importance_end_date = @$depression['physical_activity_importance_end_date'] ?? "";
                $physical_activity_importance_status = $this->calculateStatus($physical_activity_importance_start_date, $physical_activity_importance_end_date);
                
                $waves_treatment_start_date = @$depression['waves_treatment_start_date'] ?? "";
                $waves_treatment_end_date = @$depression['waves_treatment_end_date'] ?? "";
                $waves_treatment_status = $this->calculateStatus($waves_treatment_start_date, $waves_treatment_end_date);

                $depression_OutComes['goal3_status'] = "";
                $goal3_task_status = [
                    $relieve_depression_status,
                    $understand_cbt_status,
                    $physical_activity_importance_status,
                    $waves_treatment_status,
                ];
                $counts = array_count_values($goal3_task_status);

                if (@$counts['Completed'] === sizeof($goal3_task_status)) {
                    $depression_OutComes['goal3_status'] = "Completed";
                } else if (@$counts['Completed'] > 0 && @$counts['Completed'] < sizeof($goal3_task_status)) {
                    $depression_OutComes['goal3_status'] = "In progress";
                } else if (@$counts['Started'] > 0) {
                    $depression_OutComes['goal3_status'] = "In progress";
                }

                // TASKS DATES AND STATUS
                $depression_OutComes['relieve_depression_start_date'] = $relieve_depression_start_date;
                $depression_OutComes['relieve_depression_end_date'] = $relieve_depression_end_date;
                $depression_OutComes['relieve_depression_status'] = $relieve_depression_status;
                
                $depression_OutComes['understand_cbt_start_date'] = $understand_cbt_start_date;
                $depression_OutComes['understand_cbt_end_date'] = $understand_cbt_end_date;
                $depression_OutComes['understand_cbt_status'] = $understand_cbt_status;
            
                $depression_OutComes['physical_activity_importance_start_date'] = $physical_activity_importance_start_date;
                $depression_OutComes['physical_activity_importance_end_date'] = $physical_activity_importance_end_date;
                $depression_OutComes['physical_activity_importance_status'] = $physical_activity_importance_status;
            
                $depression_OutComes['waves_treatment_start_date'] = $waves_treatment_start_date;
                $depression_OutComes['waves_treatment_end_date'] = $waves_treatment_end_date;
                $depression_OutComes['waves_treatment_status'] = $waves_treatment_status;

                // GOAL 3 ENDS
                
                // GOAL 4
                $exercise_start_date = @$depression['exercise_start_date'] ?? "";
                $exercise_end_date = @$depression['exercise_end_date'] ?? "";
                $exercise_status = $this->calculateStatus($exercise_start_date, $exercise_end_date);

                $depression_OutComes['goal4_status'] = "";
                $goal4_task_status = [
                    $exercise_status,
                ];
                $counts = array_count_values($goal4_task_status);

                if (@$counts['Completed'] === sizeof($goal4_task_status)) {
                    $depression_OutComes['goal4_status'] = "Completed";
                } else if (@$counts['Completed'] > 0 && @$counts['Completed'] < sizeof($goal4_task_status)) {
                    $depression_OutComes['goal4_status'] = "In progress";
                } else if (@$counts['Started'] > 0) {
                    $depression_OutComes['goal4_status'] = "In progress";
                }

                // TASKS DATES AND STATUS
                $depression_OutComes['exercise_start_date'] = $exercise_start_date;
                $depression_OutComes['exercise_end_date'] = $exercise_end_date;
                $depression_OutComes['exercise_status'] = $exercise_status;
                // GOAL 4 ENDS
                
                // GOAL 5
                $regular_follow_ups_start_date = @$depression['regular_follow_ups_start_date'] ?? "";
                $regular_follow_ups_end_date = @$depression['regular_follow_ups_end_date'] ?? "";
                $regular_follow_ups_status = $this->calculateStatus($regular_follow_ups_start_date, $regular_follow_ups_end_date);

                $depression_OutComes['goal5_status'] = "";
                $goal5_task_status = [
                    $regular_follow_ups_status,
                ];
                $counts = array_count_values($goal5_task_status);

                if (@$counts['Completed'] === sizeof($goal5_task_status)) {
                    $depression_OutComes['goal5_status'] = "Completed";
                } else if (@$counts['Completed'] > 0 && @$counts['Completed'] < sizeof($goal5_task_status)) {
                    $depression_OutComes['goal5_status'] = "In progress";
                } else if (@$counts['Started'] > 0) {
                    $depression_OutComes['goal5_status'] = "In progress";
                }

                // TASKS DATES AND STATUS
                $depression_OutComes['regular_follow_ups_start_date'] = $regular_follow_ups_start_date;
                $depression_OutComes['regular_follow_ups_end_date'] = $regular_follow_ups_end_date;
                $depression_OutComes['regular_follow_ups_status'] = $regular_follow_ups_status;
                // GOAL 5 ENDS
                
                // GOAL 6
                $helping_guides_start_date = @$depression['helping_guides_start_date'] ?? "";
                $helping_guides_end_date = @$depression['helping_guides_end_date'] ?? "";
                $helping_guides_status = $this->calculateStatus($helping_guides_start_date, $helping_guides_end_date);

                $depression_OutComes['goal6_status'] = "";
                $goal6_task_status = [
                    $helping_guides_status,
                ];
                $counts = array_count_values($goal6_task_status);

                if (@$counts['Completed'] === sizeof($goal6_task_status)) {
                    $depression_OutComes['goal6_status'] = "Completed";
                } else if (@$counts['Completed'] > 0 && @$counts['Completed'] < sizeof($goal6_task_status)) {
                    $depression_OutComes['goal6_status'] = "In progress";
                } else if (@$counts['Started'] > 0) {
                    $depression_OutComes['goal6_status'] = "In progress";
                }

                // TASKS DATES AND STATUS
                $depression_OutComes['helping_guides_start_date'] = $helping_guides_start_date;
                $depression_OutComes['helping_guides_end_date'] = $helping_guides_end_date;
                $depression_OutComes['helping_guides_status'] = $helping_guides_status;
                // GOAL 6 ENDS
                
                // GOAL 7
                $improve_relations_start_date = @$depression['improve_relations_start_date'] ?? "";
                $improve_relations_end_date = @$depression['improve_relations_end_date'] ?? "";
                $improve_relations_status = $this->calculateStatus($improve_relations_start_date, $improve_relations_end_date);

                $psychotherapy_start_date = @$depression['psychotherapy_start_date'] ?? "";
                $psychotherapy_end_date = @$depression['psychotherapy_end_date'] ?? "";
                $psychotherapy_status = $this->calculateStatus($psychotherapy_start_date, $psychotherapy_end_date);

                $depression_OutComes['goal7_status'] = "";
                $goal7_task_status = [
                    $improve_relations_status,
                    $psychotherapy_status,
                ];
                $counts = array_count_values($goal7_task_status);

                if (@$counts['Completed'] === sizeof($goal7_task_status)) {
                    $depression_OutComes['goal7_status'] = "Completed";
                } else if (@$counts['Completed'] > 0 && @$counts['Completed'] < sizeof($goal7_task_status)) {
                    $depression_OutComes['goal7_status'] = "In progress";
                } else if (@$counts['Started'] > 0) {
                    $depression_OutComes['goal7_status'] = "In progress";
                }

                // TASKS DATES AND STATUS
                $depression_OutComes['improve_relations_start_date'] = $improve_relations_start_date;
                $depression_OutComes['improve_relations_end_date'] = $improve_relations_end_date;
                $depression_OutComes['improve_relations_status'] = $improve_relations_status;

                $depression_OutComes['psychotherapy_start_date'] = $psychotherapy_start_date;
                $depression_OutComes['psychotherapy_end_date'] = $psychotherapy_end_date;
                $depression_OutComes['psychotherapy_status'] = $psychotherapy_status;
                // GOAL 7 ENDS
            } else {
                if ($depression_score == 0) {
                    $depression_OutComes['severity'] = "Depression PHQ9 score is ".$depression_score.". Patient is not depressed";
                } elseif ($depression_score > 0 && $depression_score <= 4) {
                    $depression_OutComes['severity'] = "Depression PHQ9 score is ".$depression_score.". Minimal depression";
                } elseif ($depression_score > 4 && $depression_score <= 9) {
                    $depression_OutComes['severity'] = "Depression PHQ9 score is ".$depression_score.". Mild depression";
                } elseif ($depression_score > 9 && $depression_score <= 14) {
                    $depression_OutComes['severity'] = "Depression PHQ9 score is ".$depression_score.". Moderate depression";
                } elseif ($depression_score > 14 && $depression_score <= 20) {
                    $depression_OutComes['severity'] = "Depression PHQ9 score is ".$depression_score.". Moderately Severe depression";
                } elseif ($depression_score > 20 && $depression_score <= 27) {
                    $depression_OutComes['severity'] = "Depression PHQ9 score is ".$depression_score.". Severe depression";
                }
    
                $referral_tomhProfessional = @$depression['referred_to_mh_professional'] ?? "";
                $enrolled_in_bhi = @$depression['enroll_in_bhi'] ?? "";
    
    
                if ($depression_score > 9) {
                    $depression_OutComes['referrals'] = "";
                    $depression_OutComes['referrals1'] = "";
    
                    if ($referral_tomhProfessional == "Yes") {
                        $depression_OutComes['referrals'] = "Referred to Mental Health Professional.";
                    } else if ($referral_tomhProfessional == "No") {
                        $depression_OutComes['referrals'] = "Refused to see Mental Health Professional.";
                    }
                    
                    if ($enrolled_in_bhi == "Yes") {
                        $depression_OutComes['referrals1'] = "Referred to BHI program.";
                    } else if ($enrolled_in_bhi == "No") {
                        $depression_OutComes['referrals1'] = "Refused to be enrolled in the BHI program.";
                    }
    
                    $depression_OutComes['flag'] = true;
                }
            }
            

        }
        return $depression_OutComes;
    }


    /* General Health Careplan */
    private function generalhealthscreening($highstress, $general_health, $social_emotional_support, $pain)
    {
        $careplan_outcome = [];

        /* For High Stress */
        if (!empty($highstress)) {
            $stressLevel = $highstress['stress_problem'];

            switch ($stressLevel) {
                case 'Always':
                case 'Often':
                    $high_stress['outcome'] = $stressLevel . ': Options for therapy discussed with patient.';
                    break;

                case 'Never or Rarely':
                    $high_stress['outcome'] = 'Never or rarely';
                    break;

                case 'Sometimes':
                    $high_stress['outcome'] = $stressLevel;
                    break;
                default:
                    $high_stress['outcome'] = "";
                    break;
            }

            $careplan_outcome['high_stress'] = $high_stress;
        }

        /* For general health Screening */
        if (!empty($general_health)) {

            $general_health['health_level'] = (!empty($general_health['health_level'])) ? $general_health['health_level'] . ' for age' : '';
            $general_health['mouth_and_teeth'] = (!empty($general_health['mouth_and_teeth'])) ? $general_health['mouth_and_teeth'] : '';
            $general_health['feelings_cause_distress'] = (!empty($general_health['feeling_caused_distress'])) ? $general_health['feeling_caused_distress'] : '';

            if ($general_health['health_level'] == 'Poor' || $general_health['mouth_and_teeth'] == 'Poor' || $general_health['feelings_cause_distress'] == 'Yes') {
                $general_health['flag'] = true;
            }

            $careplan_outcome['general_health'] = $general_health;
        }

        /* For Social and Emotional Supoort */
        if (!empty($social_emotional_support)) {
            $supportLevel = (!empty($social_emotional_support)) ? $social_emotional_support['get_social_emotional_support'] : '';
            $social_emotional_support['outcome'] = "";

            if ($supportLevel != "") {
                switch ($supportLevel) {
                    case 'Always':
                        $social_emotional_support['outcome'] = $supportLevel . ' available.';
                        break;

                    default:
                        $social_emotional_support['outcome'] = $supportLevel . ' available: Options for therapy discussed with patient.';
                        break;
                }
            }

            $careplan_outcome['social_emotional_support'] = $social_emotional_support;
        }

        /* For Pain Screening */
        if (!empty($pain)) {
            $painLevel = !empty($pain) ? $pain['pain_felt'] : '';
            $pain['outcome'] = $painLevel;
            if ($painLevel == 'Alot') {
                $pain['outcome'] = $pain['outcome'] . ': Pain management considered.';
            }

            $careplan_outcome['pain'] = $pain;
        }

        return $careplan_outcome;
    }


    /* Cognitive Assessment Careplan */
    private function cognitive_assessment($cognitive_assessment) 
    {
        $cognitiveOutcomes = [];
        if (!empty($cognitive_assessment)) {

            $yearRecalled = $monthRecalled = $hourRecalled = $reverseCount = $reverseMonth = $addressRecalled = 0;

            if (!empty($cognitive_assessment['year_recalled'])) {
                $yearRecalled = $cognitive_assessment['year_recalled'] == 'incorrect' ? 4 : 0;
            }

            if (!empty($cognitive_assessment['month_recalled'])) {
                $monthRecalled = $cognitive_assessment['month_recalled'] == 'incorrect' ? 3 : 0;
            }

            if (!empty($cognitive_assessment['hour_recalled'])) {
                $hourRecalled = $cognitive_assessment['hour_recalled'] == 'incorrect' ? 3 : 0;
            }

            if (!empty($cognitive_assessment['reverse_count'])) {
                $reverseCount = ($cognitive_assessment['reverse_count']) == '1 error' ? 2 : (($cognitive_assessment['reverse_count'] == 'more than 1 error') ? 4 : 0);
            }

            if (!empty($cognitive_assessment['reverse_month'])) {
                $reverseMonth = strtolower(($cognitive_assessment['reverse_month'])) == '1 error' ? 2 : (($cognitive_assessment['reverse_month'] == 'more than 1 error') ? 4 : 0);
            }

            if (!empty($cognitive_assessment['address_recalled'])) {
                $errorArray = Config::get('constants')['error_options_c'];
                $address_recalled_value = $cognitive_assessment['address_recalled'];
                foreach ($errorArray as $key => $value) {

                    /* concatinating "s" in the end to fix the issue with score  */
                    if ($address_recalled_value == $value || $address_recalled_value.'s' == $value) {
                        $addressRecalled = (int)$key;
                    }
                }
            }

            /* Calcluating scores */
            $cogScore = $yearRecalled + $monthRecalled + $hourRecalled + $reverseCount + $reverseMonth + $addressRecalled;
            $cognitiveOutcomes['score'] = $cogScore;

            if ($cogScore <= 7) {
                $cognitiveOutcomes['outcome'] = 'Cognitive assessment score is '.$cogScore.'. Referral not necessary at present.';
            } elseif ($cogScore >= 8 && $cogScore <= 9) {
                $cognitiveOutcomes['outcome'] = 'Cognitive assessment score is '.$cogScore.'. Probably refer.';
            } elseif ($cogScore >= 10 && $cogScore <= 28) {
                $cognitiveOutcomes['outcome'] = 'Cognitive assessment score is '.$cogScore.'. Referral provided';
            }
        }

        return $cognitiveOutcomes;
    }


    /* Physical Activity Screening */
    private function physical_activity($physical_activities)
    {
        $physicalActivitiesOutComes = [];
        if (!empty($physical_activities)) {
            if (!empty($physical_activities['does_not_apply'])) {
                $physicalActivitiesOutComes['outcome'] = 'Not Applicable, N/A, Patient is unable to perform exercise due to medical issue';
            } else {
                $totalMinuts = @$physical_activities['days_of_exercise'] * @$physical_activities['mins_of_exercise'];
                $intensity = !empty($physical_activities['exercise_intensity']) ? $physical_activities['exercise_intensity'] : "";
    
                $highIntensityArray = ['moderate', 'heavy', 'veryheavy'];
    
                if ($totalMinuts >= 150 && in_array($intensity, $highIntensityArray)) {
                    $physicalActivitiesOutComes['outcome'] = 'Patient is exercising as per recommendation. CDC guidelines for physical activity given.';
                } else {
                    $physicalActivitiesOutComes['outcome'] = 'Patient counseled to exercise - recommended 150 minutes of moderate activity per week. CDC guidelines for physical activity provided.';
                    // $physicalActivitiesOutComes['flag'] = true;
                }
            }
        }

        return $physicalActivitiesOutComes;
    }


    /* Alcohol Use screening */
    private function alcohol_use_screening($alcohol_usage, $row)
    {
        $alcoholOutComes = [];
        if (!empty($alcohol_usage)) {
            $drinksPerWeek = (int)@$alcohol_usage['days_of_alcoholuse'] * (int)@$alcohol_usage['drinks_per_day'];
            $drinksPerCccasion = (int)@$alcohol_usage['drinks_per_occasion'];
    
            $heavyDrinks = $bingDrinks = $excessive_drinking = 0;
            $gender = $row['patient']['gender'];
            if ($gender == "Male" || $gender == "MALE") {
                $heavyDrinks = $drinksPerWeek > 15 ? true : false;
                $bingDrinks = $drinksPerCccasion > 5 ? true : false;
                $excessive_drinking  = (int)@$alcohol_usage['drinks_per_day'] > 2 ? true : false;
            } else {
                $heavyDrinks = $drinksPerWeek > 8 ? true : false;
                $bingDrinks = $drinksPerCccasion > 4 ? true : false;
            }

            if ($drinksPerWeek == 0 || $drinksPerWeek == "") {
                $alcoholOutComes['outcome'] = "Patient doesn’t drink alcohol";
            } else {
                /* Heavy Excessive and Binge */
                if ($heavyDrinks && $excessive_drinking && $bingDrinks) {
                    $alcoholOutComes['outcome'] = "Patient is a heavy, excessive & a binge drinker. Counseled & dietary guidelines for alcohol provided.";
                } 
                /* Heavy and excessive */
                elseif ($heavyDrinks && $excessive_drinking && !$bingDrinks) {
                    $alcoholOutComes['outcome'] = "Patient is a heavy & an excessive drinker. Counseled & dietary guidelines for alcohol provided.";
                } 
                /* Heavy and binge */
                elseif ($heavyDrinks && !$excessive_drinking && $bingDrinks) {
                    $alcoholOutComes['outcome'] = "Patient is a heavy & binge drinker. Counseled & dietary guidelines for alcohol provided.";
                } 
                /* only heavy */
                elseif ($heavyDrinks && !$excessive_drinking && !$bingDrinks) {
                    $alcoholOutComes['outcome'] = "Patient is a heavy drinker. Counseled & dietary guidelines for alcohol provided.";
                } 
                /* Excessive and binge */
                elseif (!$heavyDrinks && $excessive_drinking && $bingDrinks) {
                    $alcoholOutComes['outcome'] = "Patient is an excessive & a binge drinker. Counseled & dietary guidelines for alcohol provided.";
                }
                /* Only Binge */
                elseif (!$heavyDrinks && !$excessive_drinking && $bingDrinks) {
                    $alcoholOutComes['outcome'] = "Patient is a binge drinker. Counseled & dietary guidelines for alcohol provided.";
                }
                /* Only Excessive */
                elseif (!$heavyDrinks && $excessive_drinking && !$bingDrinks) {
                    $alcoholOutComes['outcome'] = "Patient is an excessive drinker. Counseled & dietary guidelines for alcohol provided.";
                }
                else {
                    $alcoholOutComes['outcome'] = "Patient is not accustomed to heavy & binge drinking.";
                }
            }
    
    
            /* if ($heavyDrinks || $bingDrinks) {
                $alcoholOutComes['flag'] = true;
            } */
        }

        return $alcoholOutComes;
    }


    /* Tobacoo screening */
    private function tobacco_use_screening($tobacco_usage)
    {
        $tobaccoOutComes = [];
        if (!empty($tobacco_usage)) {
            $ldctCounseling = "";

            $averagePacksperYear = !empty($tobacco_usage['average_packs_per_year']) ? $tobacco_usage['average_packs_per_year'] : 0;
            $performLdct = (!empty($tobacco_usage['perform_ldct']) && $tobacco_usage['perform_ldct'] == "Yes" ? true : false);
            $currentSmoker = @$tobacco_usage['smoked_in_thirty_days'] ?? "";
            $smokedinFifteenYears = @$tobacco_usage['smoked_in_fifteen_years'] ?? "";

            /* LDCT OUTCOME */
            if ($averagePacksperYear >= 30) {
                if ($currentSmoker == "No" && $smokedinFifteenYears == "Yes" && $performLdct) {
                    $tobaccoOutComes['ldct_counseling'] = "Patient has quit smoking less than 15 years back. Patient smoked ".$averagePacksperYear." PY. Patient agrees for LDCT. Referral sent to Radiology for LDCT.";
                } else if ($performLdct == false && $smokedinFifteenYears == "Yes" && $currentSmoker == "No") {
                    $tobaccoOutComes['ldct_counseling'] = "Patient has quit smoking less than 15 years back and has been a ".$averagePacksperYear." PY smoking. Patient is advised to get LDCT but refuses to do it.";
                } elseif ($currentSmoker == "Yes" && $performLdct == true) {
                    $tobaccoOutComes['ldct_counseling'] = "Patient use tobacco. Patient smoked ".$averagePacksperYear." PY. Patient agrees for LDCT. Referral sent to Radiology for LDCT.";
                } elseif ($currentSmoker == "Yes" && $performLdct == false) {
                    $tobaccoOutComes['ldct_counseling'] = "Patient use tobacco. Patient smoked ".$averagePacksperYear." PY. Patient is advised to get LDCT but refuses to do it.";
                }
            } elseif ($currentSmoker == "Yes") {
                $tobaccoOutComes['ldct_counseling'] = "Patient use tobacoo, LDCT not applicable";
            } else {
                $tobaccoOutComes['ldct_counseling'] = "Patient does not use tobacco, LDCT not applicable";
            }

            /* QUIT TOBACCO OUTCOME*/
            $acceptQuittobacco = !empty($tobacco_usage['quit_tobacco']) ? $tobacco_usage['quit_tobacco'] : '';
            $tobacooAlternate = @$tobacco_usage['tobacoo_alternate'] ?? '';
            $tobacooAlternateQty = !empty($tobacco_usage['tobacoo_alternate_qty']) ? $tobacco_usage['tobacoo_alternate_qty'] : "";
            
            if ($acceptQuittobacco == 'Yes' && $tobacooAlternate != "Refused") {
                $tobaccoOutComes['quit_tobacoo'] = 'Tobacco screening and cessation counseling performed. CDC guidelines given,' . ($tobacooAlternate != "" ? $tobacooAlternate : '') . ($tobacooAlternateQty != "" ? ' ' . $tobacooAlternateQty . ' started' : '');
            } elseif ($acceptQuittobacco == 'Yes' && $tobacooAlternate == "Refused") {
                $tobaccoOutComes['quit_tobacoo'] = 'Tobacco screening and cessation counseling performed. CDC guidelines given, Refused to start any other tobacoo alternate';
            } elseif ($acceptQuittobacco == 'No') {
                $tobaccoOutComes['quit_tobacoo'] = 'Patient is not interested in quitting tobacco use. ';
            }

            if ($averagePacksperYear >= 30 && !$performLdct) {
                $tobaccoOutComes['flag'] = true;
            }
        }

        return $tobaccoOutComes;
    }


    /* Immunization screening careplan */
    private function immunization_screening($immunization)
    {
        $immunizationOutcomes = [];
        if (!empty($immunization)) {
            $refusedFluVaccine = !empty($immunization['flu_vaccine_refused']) && $immunization['flu_vaccine_refused'] == 'Yes' ? true : false;
            $scriptFluVaccine = !empty($immunization['flu_vaccine_script_given']) ? $immunization['flu_vaccine_script_given'] : "";
            $recievedFluvaccineOn = !empty($immunization['flu_vaccine_recieved_on']) ? $immunization['flu_vaccine_recieved_on'] : "";
            $recievedFluvaccine = !empty($immunization['flu_vaccine_recieved']) ? $immunization['flu_vaccine_recieved'] : "";
            $recievedFluvaccineAt = !empty($immunization['flu_vaccine_recieved_at']) ? $immunization['flu_vaccine_recieved_at'] : "";

            if ($refusedFluVaccine) {
                $immunizationOutcomes['flu_vaccine'] = "Patient refused flu vaccine";
            } else if ($recievedFluvaccine != "" && $recievedFluvaccine == 'Yes') {
                $immunizationOutcomes['flu_vaccine'] = "Received flu vaccine " . ($recievedFluvaccineOn != "" ? "on " . $recievedFluvaccineOn : "") . ($recievedFluvaccineAt != "" ? " at " . $recievedFluvaccineAt : "");
            } else if (!$refusedFluVaccine && $recievedFluvaccine == "No") {
                $immunizationOutcomes['flu_vaccine'] = "Patient did not received flu vaccine.";
            }

            if ($scriptFluVaccine == "Yes") {
                $immunizationOutcomes['flu_vaccine_script'] = "Script given for flu vaccine";
            } else if ($scriptFluVaccine  == "No") {
                $immunizationOutcomes['flu_vaccine_script'] = "Script for flu vaccine is not provided.";
            }



            $refusedPneumococcalVaccine = !empty($immunization['pneumococcal_vaccine_refused']) && $immunization['pneumococcal_vaccine_refused'] == "Yes" ? true : false;
            $pneumococcalVaccine_received = !empty($immunization['pneumococcal_vaccine_recieved']) ? $immunization['pneumococcal_vaccine_recieved'] : "";
            
            $prevnarRecieved_on = !empty($immunization['pneumococcal_prevnar_recieved_on']) ? $immunization['pneumococcal_prevnar_recieved_on'] : "";
            $prevnarRecieved_at = !empty($immunization['pneumococcal_prevnar_recieved_at']) ? $immunization['pneumococcal_prevnar_recieved_at'] : "";

            $ppsvRecieved_on = !empty($immunization['pneumococcal_ppsv23_recieved_on']) ? $immunization['pneumococcal_ppsv23_recieved_on'] : "";
            $ppsvRecieved_at = !empty($immunization['pneumococcal_ppsv23_recieved_at']) ? $immunization['pneumococcal_ppsv23_recieved_at'] : "";

            $scriptPneumococcalVaccine = !empty($immunization['pneumococcal_vaccine_script_given']) && $immunization['pneumococcal_vaccine_script_given'] == "Yes" ? true : false;

            if ($refusedPneumococcalVaccine) {
                $immunizationOutcomes['pneumococcal_vaccine'] = "Patient refused Pneumococcal vaccine";
            } elseif ($pneumococcalVaccine_received != "") {
                if ($pneumococcalVaccine_received === "Yes") {
                    if ($prevnarRecieved_on == "" && $ppsvRecieved_on == "") {
                        $immunizationOutcomes['pneumococcal_vaccine'] = 'Pneumococcal vaccine received';
                    } else {
                        if (!empty($prevnarRecieved_on)) {
                            $immunizationOutcomes['pneumococcal_vaccine'] = "Received Prevnar 13 on " . $prevnarRecieved_on . ' at ' .$prevnarRecieved_at;
                        }
    
                        if (!empty($ppsvRecieved_on)) {
                            if (isset($immunizationOutcomes['pneumococcal_vaccine'])) {
                                $immunizationOutcomes['pneumococcal_vaccine'] .= ". Received PPSV 23 on " . $ppsvRecieved_on . ' at ' .$ppsvRecieved_at;
                            } else {
                                $immunizationOutcomes['pneumococcal_vaccine'] = "Received PPSV 23 on " . $ppsvRecieved_on . ' at ' .$ppsvRecieved_at;
                            }
                        }
                    }
                } else {
                    $immunizationOutcomes['pneumococcal_vaccine'] = "Patient did not received Pneumococcal vaccine.";
                }
            }

            if ($scriptPneumococcalVaccine) {
                $immunizationOutcomes['pneumococcal_vaccine_script'] = "Script given for Prevnar 13 / PPSV 23";
            }


            $fluNextDue = $lastFluVaccine = '';
            if (!empty($recievedFluvaccineOn)) {
                $fluVaccineDate = $recievedFluvaccineOn;
                $monthYear = explode('/', $fluVaccineDate);

                $current_month = Carbon::now()->format('m');
                $current_Year = Carbon::now()->format('Y');

                $lastFluVaccine = $this->diffinMonths($monthYear, '1');

                $fluNextDue = Carbon::createFromDate($monthYear[1], $monthYear[0])->startOfMonth()->addMonth(12)->format('m/Y');

                $next_flu_vaccine = explode('/', $fluNextDue);


                if ($lastFluVaccine > 12 && $current_Year >= $next_flu_vaccine[1]) {

                    $flu_months = ['1', '2', '3', '4', '8', '9', '10', '11', '12'];
                    if (in_array($current_month, $flu_months)) {
                        $fluNextDue = Carbon::create()->startOfMonth()->month($current_month)->year($current_Year)->format('m/Y');
                    } else {
                        $fluNextDue = Carbon::create()->startOfMonth()->month(8)->year($current_Year)->format('m/Y');
                    }
                }

                $immunizationOutcomes['nextFluVaccine'] = $fluNextDue;
            }

            $fluNextDue = $lastFluVaccine = '';


            if ($lastFluVaccine >= 12) {
                $immunizationOutcomes['flag'] = true;
            }
        }

        return $immunizationOutcomes;
    }


    /* Screening Care plan */
    private function screening($screening, $row)
    {
        $screeningOutcomes = [];

        if (!empty($screening)) {
            /* MAMMOGRAM */
            $refused_mammogram = !empty($screening['mammogram_refused']) && $screening['mammogram_refused'] == "Yes" ? true : false;
            $mammogram_done = !empty($screening['mammogram_done']) && $screening['mammogram_done'] == "Yes" ? true : false;
            $script_mammogram = !empty($screening['mammogram_script']) && $screening['mammogram_script'] == "Yes" ? true : false;
            $next_mammogram = !empty($screening['next_mommogram']) ? $screening['next_mommogram'] : "";
            $lastMammogramDiff = '';

            $patientAge = $row['patient']['age'];
            $gender = $row['patient']['gender']; 

            if ($patientAge >=50 && $patientAge < 76 && strtoupper($gender) != "MALE") {
                if ($refused_mammogram) {
                    $screeningOutcomes["mammogram"] = "Refused Mammogram";
                } elseif ($mammogram_done) {
                    $mammogram_on = !empty($screening['mammogram_done_on']) ? $screening['mammogram_done_on'] : "";
                    $mammogram_at = !empty($screening['mammogram_done_at']) ? $screening['mammogram_done_at'] : "";
                    $mammogram_report_reviewed = !empty($screening['mommogram_report_reviewed']) && $screening['mommogram_report_reviewed'] == 1 ? "Report reviewed" : "";
                    $screeningOutcomes["mammogram"] = "Mammogram done on " . $mammogram_on . ($mammogram_at != "" ? " at " . $mammogram_at : " ") . '. ' . $mammogram_report_reviewed;

                    if ($mammogram_on != "") {
                        $monthYear = explode('/', $mammogram_on);
                        $lastMammogramDiff = $this->diffinMonths($monthYear, '2');
                    }
                }

                if ($refused_mammogram || !$mammogram_done || $lastMammogramDiff > 27) {
                    $screeningOutcomes["mammogaram_flag"] = true;
                }

                if (!empty($next_mammogram)) {
                    $screeningOutcomes["next_mammogram"] = "Next Mammogram due on " . $next_mammogram;
                    $screeningOutcomes["next_mammogram_date"] = $next_mammogram;
                }

                if ($script_mammogram) {
                    $screeningOutcomes["mammogram_script"] = "Script given for the Screening Mammogram";
                }
            } else {
                $screeningOutcomes["mammogram"] = 'N/A due to age';
                if ($gender == "Male" || $gender == "MALE") {
                    $screeningOutcomes["mammogram"] = 'Not Applicable';
                }
            }



            /* COLONOSCOPY */
            $refused_colonoscopy = !empty($screening['colonoscopy_refused']) && $screening['colonoscopy_refused'] == "Yes" ? true : false;
            $refused_colonoscopy_type = !empty($screening['refused_colonoscopy']) && $screening['refused_colonoscopy'] == "1" ? true : false;
            $refused_fit_type = !empty($screening['refused_fit_test']) && $screening['refused_fit_test'] == "1" ? true : false;
            $refused_cologuard_type = !empty($screening['refused_cologuard']) && $screening['refused_cologuard'] == "1" ? true : false;


            $colonoscopy_done = !empty($screening['colonoscopy_done']) && $screening['colonoscopy_done'] == "Yes" ? true : false;
            $script_colonoscopy = !empty($screening['colonoscopy_script']) && $screening['colonoscopy_script'] == "Yes" ? true : false;
            $script_given_for = !empty($screening['script_given_for']) ? $screening['script_given_for'] : "";
            $next_colonoscopy = !empty($screening['next_colonoscopy']) ? $screening['next_colonoscopy'] : "";
            $testType = !empty($screening['colon_test_type']) ? $screening['colon_test_type'] : "";
            $colonoscopy_on = "";
            
            if ($patientAge >= 50 && $patientAge < 77) {
                if ($refused_colonoscopy) {
                    $screeningOutcomes["colonoscopy"] = "Refused ";

                    $refused_testtype = "";
                    if ($refused_colonoscopy_type) {
                        $refused_testtype .= "Colonoscopy";
                    }
                    
                    if ($refused_fit_type) {
                        $refused_testtype .= " FIT Test";
                    }
                    
                    if ($refused_cologuard_type) {
                        $refused_testtype .= " Cologuard";
                    }

                    $refused_testtype = preg_replace('/ ([^ ]+)$/', ' ' . ' & ' . ' $1', $refused_testtype);

                    if ($refused_testtype === "") {
                        $screeningOutcomes["colonoscopy"] .= "Colonoscopy & FIT Test";
                    } else {
                        $screeningOutcomes["colonoscopy"] .= $refused_testtype;
                    }
                    

                } elseif ($colonoscopy_done) {
                    $colonoscopy_on = !empty($screening['colonoscopy_done_on']) ? $screening['colonoscopy_done_on'] : "";
                    $colonoscopy_at = !empty($screening['colonoscopy_done_at']) ? $screening['colonoscopy_done_at'] : "";
                    $colonoscopy_report_reviewed = !empty($screening['colonoscopy_report_reviewed']) && $screening['colonoscopy_report_reviewed'] == 0 ? "Report reviewed" : "";
                    if ($testType != "") {
                        $screeningOutcomes["colonoscopy"] = $testType . " done on " . $colonoscopy_on . ($colonoscopy_at != "" ? " at " . $colonoscopy_at : " ") . ' ' . $colonoscopy_report_reviewed;
                    }
                }

                if (!empty($next_colonoscopy)) {
                    $screeningOutcomes["next_colonoscopy"] = "Next " . $testType . " due on " . $next_colonoscopy;
                    $screeningOutcomes["test_type"] = $testType;

                    // preg_match('/(\d{2})\/(\d{4})/', $next_colonoscopy, $next_date);

                    $screeningOutcomes["next_col_fit_guard"] = $next_colonoscopy;
                }

                if ($script_colonoscopy) {
                    $screeningOutcomes["colonoscopy_script"] = "Script given for the Screening Colonoscopy";
                } else if ($script_given_for != "") {
                    $screeningOutcomes["colonoscopy_script"] = $script_given_for;
                }


                $lastTestExpired = false;
                if ($testType != "") {
                    if ($colonoscopy_on != "") {
                        $monthYear = explode('/', $colonoscopy_on);
                        $lastTestDiff = $this->diffinMonths($monthYear, '3');

                        if ($testType == 'Colonoscopy') {
                            $lastTestExpired = ($lastTestDiff > 120 ? true : false);
                        } elseif ($testType == 'Fit Test') {
                            $lastTestExpired = ($lastTestDiff > 12 ? true : false);
                        } elseif ($testType == 'Cologuard') {
                            $lastTestExpired = ($lastTestDiff > 24 ? true : false);
                        }
                    }
                }

                if (($refused_colonoscopy || !$colonoscopy_done || $lastTestExpired)) {
                    $screeningOutcomes["colo_flag"] = true;
                }
            } else {
                $screeningOutcomes["colonoscopy"] = 'N/A due to age';
            }
        }

        return $screeningOutcomes;
    }


    /* Diabetes Screening */
    private function diabetes_screening($diabatesScreening)
    {
        $diabetesOutcomes = [];
        if (!empty($diabatesScreening)) {
            $diabetec_patient = !empty($diabatesScreening['diabetec_patient']) ? $diabatesScreening['diabetec_patient'] : false;
            $fbs_in_year = !empty($diabatesScreening['fbs_in_year']) && $diabatesScreening['fbs_in_year'] == "Yes" ? true : false;
            $fbs_value = !empty($diabatesScreening['fbs_value']) ? (int)$diabatesScreening['fbs_value'] : '';
            $fbs_date = !empty($diabatesScreening['fbs_date']) ? $diabatesScreening['fbs_date'] : '';
            $hba1c_value = !empty($diabatesScreening['hba1c_value']) ? $diabatesScreening['hba1c_value'] : '';
            $hba1c_date = !empty($diabatesScreening['hba1c_date']) ? $diabatesScreening['hba1c_date'] : '';

            /* FASTING BLOOD SUGAR (FBS) SECTION */
            if ($diabetec_patient == 'No') {
                if (!$fbs_in_year || $fbs_value == '') {
                    $diabetesOutcomes['diabetes'] = "Fasting Blood Sugar ordered";
                    // $diabetesOutcomes['flag'] = true;
                } else {
                    if (!empty($fbs_value) && !empty($fbs_date)) {
                        $current_Date = Carbon::now()->floorMonth();
                        $dateMonthArray = explode('/', $fbs_date);
                        $month_fbs = $dateMonthArray[0];
                        $year_fbs = $dateMonthArray[1];

                        $date_format = Carbon::createFromDate($year_fbs, $month_fbs)->startOfMonth();
                        $lastfbs_monthdiff = $current_Date->diffInMonths($date_format, '4');

                        if ($lastfbs_monthdiff > 12) {
                            $diabetesOutcomes['diabetes'] = 'Patient Fasting Blood Sugar is ' . $fbs_value . ' on ' . $fbs_date . '. FBS ordered. ';
                        } elseif ($hba1c_value != "" && $hba1c_date != "") {
                            $current_Date = Carbon::now()->floorMonth();
                            $dateMonthArray = explode('/', $hba1c_date);
                            $month_hba1c = $dateMonthArray[0];
                            $year_hba1c = $dateMonthArray[1];
                            $date_format = Carbon::createFromDate($year_hba1c, $month_hba1c)->startOfMonth();

                            $lasthba1c_monthdiff = $current_Date->diffInMonths($date_format, '5');

                            if ($lasthba1c_monthdiff > 6) {
                                $diabetesOutcomes['diabetes'] = 'Patient Fasting Blood Sugar is ' . $fbs_value . ' on ' . $fbs_date . '. HBA1C ordered';
                            } else {
                                if ($hba1c_value <= 5.6) {
                                    $diabetesOutcomes['diabetes'] = 'Patient Fasting Blood Sugar is ' . $fbs_value . ' on ' . $fbs_date . '. Patient HBA1C is ' . $hba1c_value . ' on ' . $hba1c_date;
                                } elseif ($hba1c_value > 5.6 && $hba1c_value <= 6.4) {
                                    $diabetesOutcomes['diabetes'] = 'Patient Fasting Blood Sugar is ' . $fbs_value . ' on ' . $fbs_date . '. Patient HBA1C is ' . $hba1c_value . ' on ' . $hba1c_date . ' will monitor HBA1C';
                                } elseif ($hba1c_value >= 6.5 && $hba1c_value <= 6.9) {
                                    $diabetesOutcomes['diabetes'] = 'HBA1C is ' . $hba1c_value . '. Patient has new onset DM. Urine Microalbuminemia and Eye examination ordered.';
                                } elseif ($hba1c_value >= 6.9 && $hba1c_value <= 8.5) {
                                    $diabetesOutcomes['diabetes'] = 'HBA1C is ' . $hba1c_value . '. Patient has new onset DM. Urina Microalbuminemia and Eye examination ordered. “Notify Doctor” ';
                                } elseif ($hba1c_value >= 8.5) {
                                    $diabetesOutcomes['diabetes'] = 'HBA1C is ' . $hba1c_value . '. Patient has new onset DM. Urina Microalbuminemia and Eye examination ordered. Referred to Diabetic Clinic for intensive Diabetic control. ';
                                    if ($fbs_value > 110) {
                                        $diabetesOutcomes['flag'] = true;
                                    }
                                }

                                if ($hba1c_value >= 6.5) {
                                    $diabetesOutcomes['is_diabetic'] = 'Yes';
                                }
                            }

                            if (!($lasthba1c_monthdiff > 6)) {
                                $nextHba1c_date = Carbon::createFromDate($year_hba1c, $month_hba1c)->startOfMonth()->addMonth(6)->format('m/Y');
                                $getYear = Carbon::createFromFormat('m/Y', $nextHba1c_date)->format('Y');
                                $currentYear = $year = Carbon::now()->format('Y');

                                if ($getYear < $currentYear) {
                                    $nextHba1c_date = Carbon::now()->format('m/Y');
                                }

                                $diabetesOutcomes['next_hba1c_date'] = $nextHba1c_date;
                            }
                        } else {
                            if ($fbs_value > 100) {
                                $diabetesOutcomes['diabetes'] = 'Patient Fasting Blood Sugar is ' . $fbs_value . ' on ' . $fbs_date . '. HBA1C ordered.';
                            } else {
                                $diabetesOutcomes['diabetes'] = 'Patient Fasting Blood Sugar is ' . $fbs_value . ' on ' . $fbs_date . '.';
                            }
                        }
                        $nextFbs_date = Carbon::createFromDate($year_fbs, $month_fbs)->startOfMonth()->addMonth(12)->format('m/Y');
                        $diabetesOutcomes['next_fbs_date'] = $nextFbs_date;
                    }

                }
            } elseif ($diabetec_patient == 'Yes') {    //HBA1C SECTION
                if ($hba1c_value != "" && $hba1c_date != "") {
                    $current_Date = Carbon::now()->floorMonth();
                    $dateMonthArray = explode('/', $hba1c_date);
                    $month = $dateMonthArray[0];
                    $year = $dateMonthArray[1];

                    $date_format = Carbon::createFromDate($year, $month)->startOfMonth();

                    $lasthba1c_monthdiff = $current_Date->diffInMonths($date_format, '6');

                    if ($lasthba1c_monthdiff > 6) {
                        $diabetesOutcomes['diabetes'] = 'HBA1C ordered';
                        $diabetesOutcomes['flag'] = true;
                    } elseif ($hba1c_value != "") {
                        if ($hba1c_value >= 8.5) {
                            $diabetesOutcomes['diabetes'] = 'HBA1C is ' . $hba1c_value . '. Referred to Diabetic Clinic for intensive Diabetic control. ';
                            $diabetesOutcomes['flag'] = true;
                        } else {
                            $diabetesOutcomes['diabetes'] = 'HBA1C is ' . $hba1c_value . ($hba1c_date != "" ? ' on '.$hba1c_date :"").'. Controlled';
                        }
                    } else {
                        $diabetesOutcomes['diabetes'] = 'HBA1C ordered.';
                    }

                    $nextHba1c_date = Carbon::createFromDate($year, $month)->startOfMonth()->addMonth(6)->format('m/Y');
                    $getYear = Carbon::createFromFormat('m/Y', $nextHba1c_date)->format('Y');
                    $currentYear = $year = Carbon::now()->format('Y');

                    if ($getYear < $currentYear) {
                        $nextHba1c_date = Carbon::now()->format('m/Y');
                    }

                    $diabetesOutcomes['next_hba1c_date'] = $nextHba1c_date;
                    $diabetesOutcomes['is_diabetic'] = 'Yes';
                }
            }

            if ($diabetec_patient == 'Yes' || ($diabetec_patient == 'No' && $hba1c_value >= 6.5)) {
                /* Dibetes EYE EXAM */
                $diabetec_eye_exam = !empty($diabatesScreening['diabetec_eye_exam']) && $diabatesScreening['diabetec_eye_exam'] == "Yes" ? true : false;
                $diabetec_eye_exam_report = !empty($diabatesScreening['diabetec_eye_exam_report']) ? $diabatesScreening['diabetec_eye_exam_report'] : '';
                $eye_exam_doctor = !empty($diabatesScreening['eye_exam_doctor']) ? ' by Dr.' . $diabatesScreening['eye_exam_doctor'] : '';
                $eye_exam_date = !empty($diabatesScreening['eye_exam_date']) ? $diabatesScreening['eye_exam_date'] : '';
                $eye_exam_facility = !empty($diabatesScreening['eye_exam_facility']) ? $diabatesScreening['eye_exam_facility'] : '';
                $diabetec_eye_exam_reviewed = !empty($diabatesScreening['eye_exam_report_reviewed']) && $diabatesScreening['eye_exam_report_reviewed'] == "1" ? true : false;
                $diabetec_diabetec_ratinopathy = !empty($diabatesScreening['diabetec_ratinopathy']) && $diabatesScreening['diabetec_ratinopathy'] == "Yes" ? true : false;
                $ratinavueordered = !empty($diabatesScreening['ratinavue_ordered']) ? $diabatesScreening['ratinavue_ordered'] : '';

                if (!$diabetec_eye_exam) {
                    if ($ratinavueordered == 'Yes') {
                        $diabetesOutcomes['diabetec_eye_exam'] = 'Ratinavue Ordered';
                    } elseif ($ratinavueordered == 'No') {
                        $diabetesOutcomes['diabetec_eye_exam'] = 'Script given for Eye Examination';
                    }
                    $diabetesOutcomes['eye_exam_flag'] = true;
                } else {

                    $last_performed = "";
                    if ($eye_exam_date != "" && $eye_exam_facility == "") {
                        $last_performed = 'Last perfomed on ' . $eye_exam_date;
                    } else if ($eye_exam_date == "" && $eye_exam_facility != "") {
                        $last_performed = 'Last perfomed at ' . $eye_exam_facility;
                    } else if ($eye_exam_date != "" && $eye_exam_facility != "") {
                        $last_performed = 'Last perfomed on ' . $eye_exam_date . ' at ' . $eye_exam_facility;
                    }

                    if ($diabetec_eye_exam_report == "report_available") {
                        $diabetesOutcomes['diabetec_eye_exam'] = $last_performed . $eye_exam_doctor . '. ' . ($diabetec_eye_exam_reviewed ? 'Report reviewed' : "") . " " . ($diabetec_diabetec_ratinopathy ? 'and shows Diabetic Retinopathy' : "and shows No Diabetec Retinopathy");
                    } elseif ($diabetec_eye_exam_report == "report_requested") {
                        $diabetesOutcomes['diabetec_eye_exam'] = $last_performed != "" ? $last_performed . ', Report requested.' : 'Report Requested';
                    } elseif ($diabetec_eye_exam_report == "patient_call_doctor") {
                        $diabetesOutcomes['diabetec_eye_exam'] = 'Patient will call with the name of the doctor to request report';
                    }

                    if ($diabetec_eye_exam_report != "report_available") {
                        $diabetesOutcomes['eye_exam_flag'] = true;
                    }
                }


                /* Diabetes NEPHROPATHY */
                $urine_microalbumin = !empty($diabatesScreening['urine_microalbumin']) ? $diabatesScreening['urine_microalbumin'] : '';
                $urine_microalbumin_ordered = !empty($diabatesScreening['urine_microalbumin_ordered']) ? $diabatesScreening['urine_microalbumin_ordered'] : '';
                $urine_microalbumin_date = !empty($diabatesScreening['urine_microalbumin_date']) ? $diabatesScreening['urine_microalbumin_date'] : '';
                $urine_microalbumin_report = !empty($diabatesScreening['urine_microalbumin_report']) ? $diabatesScreening['urine_microalbumin_report'] : '';
                $urine_microalbumin_value = !empty($diabatesScreening['urine_microalbumin_value']) ? $diabatesScreening['urine_microalbumin_value'] : '';

                $ace_inhibitor = !empty($diabatesScreening['urine_microalbumin_inhibitor']) ? $diabatesScreening['urine_microalbumin_inhibitor'] : '';
                $ckd_stage_4 = !empty($diabatesScreening['ckd_stage_4']) ? $diabatesScreening['ckd_stage_4'] : '';

                $urine_forMicroalbumin = $inhibitors = '';


                if (!empty($urine_microalbumin)) {
                    if ($urine_microalbumin == 'Yes') {
                        if ($urine_microalbumin_value != "") {
                            $urine_forMicroalbumin = 'Urine for Microalbumin is ' . $urine_microalbumin_value. ' on ' . $urine_microalbumin_date.'. Report is' .$urine_microalbumin_report ;
                        } else {
                            $urine_forMicroalbumin = 'Urine for Microalbumin is performed on '. $urine_microalbumin_date.'. Report is '.$urine_microalbumin_report;
                        }
                    } else {
                        if ($urine_microalbumin_ordered != "") {
                            if ($urine_microalbumin_ordered == 'Yes') {
                                $urine_forMicroalbumin = "Urine for Micro-albumin ordered. ";
                            } else {
                                $urine_forMicroalbumin = "Patient refused urine for Micro-albuminuria. ";
                            }
                        }

                        if ($ace_inhibitor != "") {
                            if ($ace_inhibitor != "none") {
                                $ace_inhibitor = array_search($ace_inhibitor, Config::get('constants')['inhibitor']);
                                $inhibitors = 'Patient is receiving ' . $ace_inhibitor . ' therapy.';
                            } else if ($ckd_stage_4 != "") {
                                $inhibitors = 'Patient ' . ($ckd_stage_4 == "ckd_stage_4" ? 'has CKD Stage 4' : "sees a Nephrologist");
                            }
                        }
                    }

                    $diabetesOutcomes['nepropathy'] = $urine_forMicroalbumin . '' . $inhibitors;
                }

                if ($urine_microalbumin && $urine_microalbumin_ordered && $ckd_stage_4 != "patient_see_nephrologist") {
                    $diabetesOutcomes['nephropathy_flag'] = true;
                }
            }
        }

        return $diabetesOutcomes;
    }


    private function cholesterol_screening($cholesterolAssessment)
    {
        $cholesterol_outcome = [];
        if (!empty($cholesterolAssessment)) {
            $ldlValue = !empty($cholesterolAssessment['ldl_value']) ? $cholesterolAssessment['ldl_value'] : '';
            $lastLDLdate = !empty($cholesterolAssessment['ldl_date']) ? $cholesterolAssessment['ldl_date'] : '';
            $lipidProfile = !empty($cholesterolAssessment['ldl_in_last_12months']) ? $cholesterolAssessment['ldl_in_last_12months'] : '';
            $useStatin = !empty($cholesterolAssessment['statin_prescribed']) ? $cholesterolAssessment['statin_prescribed'] : '';
            $statinDosage = !empty($cholesterolAssessment['statintype_dosage']) ? $cholesterolAssessment['statintype_dosage'] : '';
            $activeDiabetes = !empty($cholesterolAssessment['active_diabetes']) ? $cholesterolAssessment['active_diabetes'] : '';
            $ldlinPasttwoyears = !empty($cholesterolAssessment['ldl_range_in_past_two_years']) ? $cholesterolAssessment['ldl_range_in_past_two_years'] : '';

            if ($ldlValue != "") {
                $cholesterol_outcome['ldl_result'] = 'Patient LDL is ' . $ldlValue . ' mg/dL' . ($lastLDLdate != '' ? ' on ' . $lastLDLdate . '.' : '');
            }

            if ($lipidProfile != '' && $lipidProfile == 'No') {
                $cholesterol_outcome['outcome'] = "Ordered Fasting Lipid Profile";
            } elseif ($ldlinPasttwoyears != "" && $ldlinPasttwoyears == 'No') {
                $cholesterol_outcome['outcome'] = "Documented medical reason for not being on statin therapy is most recent fasting or direct LDL-C<70 mg/dL";
            } elseif ($activeDiabetes != "" && $activeDiabetes == 'No') {
                $cholesterol_outcome['outcome'] = "Patient was screened for requirement of statin therapy and does not require a statin prescription at this time.";
            } else {
                if ($useStatin != '') {
                    if ($useStatin == 'Yes') {
                        $cholesterol_outcome['outcome'] = 'Patient is receiving statin therapy with ' . $statinDosage . ' as prescribed by PCP';
                    } else {
                        $reasonFornoStatin = '';
                        $reasonArray = $depressionArray = Config::get('constants')['statin_medical_reason'];
                        foreach ($reasonArray as $key => $value) {
                            if (!empty($cholesterolAssessment['medical_reason_for_nostatin' . $key])) {
                                $reasonFornoStatin .= $cholesterolAssessment['medical_reason_for_nostatin' . $key] . ', ';
                            }
                        }
    
                        if ($reasonFornoStatin != '') {
                            $cholesterol_outcome['outcome'] = 'Documented medical reason for not being on statin therapy is ' . $reasonFornoStatin . '.';
                        } else {
                            $cholesterol_outcome['outcome'] = 'Counseled and started statin therapy for cardiovascular disease';
                        }
                    }
            }
            }

            if ($lastLDLdate != '') {
                $lastLDLdate = $cholesterolAssessment['ldl_date'];
                $monthYear = explode('/', $lastLDLdate);
                $ldlNextDue = Carbon::createFromDate($monthYear[1], $monthYear[0])->startOfMonth()->addMonth(12)->format('m/Y');
                $cholesterol_outcome['ldl_next_due'] = $ldlNextDue;
            }
        }

        return $cholesterol_outcome;
    }


    /* Bp and Weight Assessment  */
    private function bp_and_weight_screening($bp_assessment, $weight_assessment)
    {
        $outcome = [];
        if (!empty($bp_assessment)) {
            $bpAssessment = [];
            $bp_value = !empty($bp_assessment['bp_value']) ? explode('/', $bp_assessment['bp_value']) : '';
            $bp_date = !empty($bp_assessment['bp_date']) ? $bp_assessment['bp_date'] : '';

            if ($bp_value != '') {
                $systolic_bp = $bp_value['0'] ?? "";
                $diastolic_bp = $bp_value['1'] ?? "";

                if ($systolic_bp != "" && $diastolic_bp != "") {
                    $bpAssessment['bp_result'] = 'Patient BP is ' . $bp_assessment['bp_value'] . ($bp_date != "" ? ' on ' . $bp_date . '.' : '.');
    
    
                    if ($systolic_bp <= 120 && $diastolic_bp <= 80) {
                        $bpAssessment['outcome'] = 'Patient BP is controlled.';
                    } else if ($systolic_bp > 120 && $diastolic_bp <= 80) {
                        $bpAssessment['outcome'] = 'Systolic BP is raised while diastolic in controlled.';
                    } else if ($systolic_bp <= 120 && $diastolic_bp > 80) {
                        $bpAssessment['outcome'] = 'Systolic BP is controlled while diastolic in raised.';
                    } elseif ($systolic_bp > 120 && $diastolic_bp > 80) {
                        $bpAssessment['outcome'] = 'Blood pressure is raised, patient counseled regarding monitoring and control.';
                    }
    
                    if ($systolic_bp > 120 || $diastolic_bp > 80) {
                        $bpAssessment['flag'] = true;
                    }
                }
            }

            $outcome['bp_assessment'] = $bpAssessment;
        }

        if (!empty($weight_assessment)) {
            $weightAssessment = [];
            $bmi_value = !empty($weight_assessment['bmi_value']) ? $weight_assessment['bmi_value'] : '';

            if (!empty($bmi_value)) {
                $nutritionist_referral = !empty($weight_assessment['followup_withnutritionist']) ? $weight_assessment['followup_withnutritionist'] : '';

                $weightAssessment['bmi_result'] = 'Patient BMI is ' . $weight_assessment['bmi_value'] . '.';

                if ($bmi_value >= 30) {
                    $referred_nutrionist = '';
                    if ($nutritionist_referral == 'Yes') {
                        $referred_nutrionist = 'Patient referred to the Nutritionist.';
                    } else if ($nutritionist_referral == 'No') {
                        $referred_nutrionist = 'Patient refused Nutritionist referral. Advised to follow up with the PCP.';
                    }
                    $weightAssessment['outcome'] = 'Dietary Guidelines summary 2020-2025 and CDC guidelines for physical activity provided to Patient. Counseled regarding Healthy eating and exercise. ' .$referred_nutrionist;
                } elseif ($bmi_value > 25 && $bmi_value < 30) {
                    $weightAssessment['outcome'] = 'Patient is over weight.';
                } elseif ($bmi_value > 15 && $bmi_value < 25) {
                    $weightAssessment['outcome'] = 'Patient has ideal BMI.';
                } elseif ($bmi_value < 15) {
                    $weightAssessment['outcome'] = 'Patient is underweight.';
                }
            }

            
            $outcome['weight_assessment'] = $weightAssessment;
        }

        return $outcome;
    }


    /* GENERAL ASSESEMENT */
    private function generalAssesment($questions_answers, $filter_month, $careplanType)
    {
        // General Assessment
        $general_assessment_outcomes = [];
        if (!empty($questions_answers['general_assessment'])) {
            $general_assessment = $questions_answers['general_assessment'];

            if ($filter_month != "") {
                $general_assessment_goals = $this->filterMonthlyAssessment($general_assessment, $filter_month, $careplanType);
    
                if (is_array($general_assessment_goals)) {
                    foreach ($general_assessment as $key => $value) {
                        if ((strpos($key, "start_date") || strpos($key, "end_date")) && !array_key_exists($key, $general_assessment_goals)) {
                            unset($general_assessment[$key]);
                        }
                    }
                }
            }

            $taking_medications = !empty($general_assessment['is_taking_medication']) && $general_assessment['is_taking_medication'] == "Yes" ? true : false;
            $tobacco_consumption = !empty($general_assessment['is_consuming_tobacco']) && $general_assessment['is_consuming_tobacco'] == "Yes" ? true : false;
            $quitting_tobacco = !empty($general_assessment['quitting_tobacco']) && $general_assessment['quitting_tobacco'] == "Yes" ? true : false;
            $reason_medication = $general_assessment['reason_for_not_taking_medication'] ?? '';
            $physical_exercise = $general_assessment['physical_exercises'] ?? '';
            $physical_exercise_intensity = $general_assessment['physical_exercise_level'] ?? '';
            $prescribed_medications = $general_assessment['prescribed_medications'] ?? '';
            
            $imp_handwash_start_date = $general_assessment['imp_handwash_start_date'] ?? '';
            $imp_handwash_end_date = $general_assessment['imp_handwash_end_date'] ?? '';
            $imp_handwash_status = $this->calculateStatus($imp_handwash_start_date, $imp_handwash_end_date) ?? '';
            
            $und_handwash_start_date = $general_assessment['und_handwash_start_date'] ?? '';
            $und_handwash_end_date = $general_assessment['und_handwash_end_date'] ?? '';
            $und_handwash_status = $this->calculateStatus($und_handwash_start_date, $und_handwash_end_date) ?? '';

            $washwithsoap_start_date = $general_assessment['washwithsoap_start_date'] ?? '';
            $washwithsoap_end_date = $general_assessment['washwithsoap_end_date'] ?? '';
            $washwithsoap_status = $this->calculateStatus($washwithsoap_start_date, $washwithsoap_end_date) ?? '';
            
            $und_washhands_start_date = $general_assessment['und_washhands_start_date'] ?? '';
            $und_washhands_end_date = $general_assessment['und_washhands_end_date'] ?? '';
            $und_washhands_status = $this->calculateStatus($und_washhands_start_date, $und_washhands_end_date) ?? '';
            
            $turnoff_faucet_start_date = $general_assessment['turnoff_faucet_start_date'] ?? '';
            $turnoff_faucet_end_date = $general_assessment['turnoff_faucet_end_date'] ?? '';
            $turnoff_faucet_status = $this->calculateStatus($turnoff_faucet_start_date, $turnoff_faucet_end_date) ?? '';
            
            $understand_faucet_start_date = $general_assessment['understand_faucet_start_date'] ?? '';
            $understand_faucet_end_date = $general_assessment['understand_faucet_end_date'] ?? '';
            $understand_faucet_status = $this->calculateStatus($understand_faucet_start_date, $understand_faucet_end_date) ?? '';
            
            $plain_soap_usage_start_date = $general_assessment['plain_soap_usage_start_date'] ?? '';
            $plain_soap_usage_end_date = $general_assessment['plain_soap_usage_end_date'] ?? '';
            $plain_soap_usage_status = $this->calculateStatus($plain_soap_usage_start_date, $plain_soap_usage_end_date) ?? '';
            
            $bar_or_liquid_start_date = $general_assessment['bar_or_liquid_start_date'] ?? '';
            $bar_or_liquid_end_date = $general_assessment['bar_or_liquid_end_date'] ?? '';
            $bar_or_liquid_status = $this->calculateStatus($bar_or_liquid_start_date, $bar_or_liquid_end_date) ?? '';
            
            $uips_start_date = $general_assessment['uips_start_date'] ?? '';
            $uips_end_date = $general_assessment['uips_end_date'] ?? '';
            $uips_status = $this->calculateStatus($uips_start_date, $uips_end_date) ?? '';
            
            $no_soap_condition_start_date = $general_assessment['no_soap_condition_start_date'] ?? '';
            $no_soap_condition_end_date = $general_assessment['no_soap_condition_end_date'] ?? '';
            $no_soap_condition_status = $this->calculateStatus($no_soap_condition_start_date, $no_soap_condition_end_date) ?? '';
            
            $understand_hand_sanitizer_start_date = $general_assessment['understand_hand_sanitizer_start_date'] ?? '';
            $understand_hand_sanitizer_end_date = $general_assessment['understand_hand_sanitizer_end_date'] ?? '';
            $understand_hand_sanitizer_status = $this->calculateStatus($understand_hand_sanitizer_start_date, $understand_hand_sanitizer_end_date) ?? '';

            /* Everyday activities */
            if (!$taking_medications) {
                $medicationsList = '';
                if ($prescribed_medications != '') {
                    $medicationsList = implode(',' , $prescribed_medications);
                }
                $general_assessment_outcomes['is_taking_medication'] = "Medication reconciliation was performed and patient is not taking ".$medicationsList.' as prescribed because ' . $reason_medication.'.';
            } else {
                $general_assessment_outcomes['is_taking_medication'] = "Medication reconciliation was performed, and patient is taking all medications as prescribed.";
            }

            if (!$tobacco_consumption) {
                $general_assessment_outcomes['is_consuming_tobacco'] = "Patient is not consuming tobacco";
            } else {
                if (!$quitting_tobacco) {
                    $general_assessment_outcomes['is_consuming_tobacco'] = "Patient is consuming tobacco and not interested in quitting";
                } else {
                    $general_assessment_outcomes['is_consuming_tobacco'] = "Patient is consuming tobacco and interested in quitting";
                }
            }

            if ($physical_exercise <= 2) {
                $general_assessment_outcomes['physical_exercises'] = "Low engagement in physical exercises";
            } elseif ($physical_exercise <= 5) {
                $general_assessment_outcomes['physical_exercises'] = "Moderate engagement in physical exercises";
            } else {
                $general_assessment_outcomes['physical_exercises'] = "Regular engagement in physical exercises";
            }

            if ($physical_exercise_intensity <= 20) {
                $general_assessment_outcomes['physical_exercise_level'] = "Patient is engaged in low intensity physical exercises";
            } elseif ($physical_exercise <= 40) {
                $general_assessment_outcomes['physical_exercise_level'] = "Patient is engaged in moderate intensity physical exercises";
            } else {
                $general_assessment_outcomes['physical_exercise_level'] = "Patient is engaged in high intensity physical exercises";
            }

            $general_assessment_outcomes['imp_handwash_start_date'] = $imp_handwash_start_date;
            $general_assessment_outcomes['imp_handwash_end_date'] = $imp_handwash_end_date;
            $general_assessment_outcomes['imp_handwash_status'] = $imp_handwash_status;

            $general_assessment_outcomes['und_handwash_start_date'] = $und_handwash_start_date;
            $general_assessment_outcomes['und_handwash_end_date'] = $und_handwash_end_date;
            $general_assessment_outcomes['und_handwash_status'] = $und_handwash_status;
            
            $general_assessment_outcomes['washwithsoap_start_date'] = $washwithsoap_start_date;
            $general_assessment_outcomes['washwithsoap_end_date'] = $washwithsoap_end_date;
            $general_assessment_outcomes['washwithsoap_status'] = $washwithsoap_status;
            
            $general_assessment_outcomes['und_washhands_start_date'] = $und_washhands_start_date;
            $general_assessment_outcomes['und_washhands_end_date'] = $und_washhands_end_date;
            $general_assessment_outcomes['und_washhands_status'] = $und_washhands_status;
            
            $general_assessment_outcomes['turnoff_faucet_start_date'] = $turnoff_faucet_start_date;
            $general_assessment_outcomes['turnoff_faucet_end_date'] = $turnoff_faucet_end_date;
            $general_assessment_outcomes['turnoff_faucet_status'] = $turnoff_faucet_status;
            
            $general_assessment_outcomes['understand_faucet_start_date'] = $understand_faucet_start_date;
            $general_assessment_outcomes['understand_faucet_end_date'] = $understand_faucet_end_date;
            $general_assessment_outcomes['understand_faucet_status'] = $understand_faucet_status;
            
            $general_assessment_outcomes['plain_soap_usage_start_date'] = $plain_soap_usage_start_date;
            $general_assessment_outcomes['plain_soap_usage_end_date'] = $plain_soap_usage_end_date;
            $general_assessment_outcomes['plain_soap_usage_status'] = $plain_soap_usage_status;
            
            $general_assessment_outcomes['bar_or_liquid_start_date'] = $bar_or_liquid_start_date;
            $general_assessment_outcomes['bar_or_liquid_end_date'] = $bar_or_liquid_end_date;
            $general_assessment_outcomes['bar_or_liquid_status'] = $bar_or_liquid_status;
            
            $general_assessment_outcomes['uips_start_date'] = $uips_start_date;
            $general_assessment_outcomes['uips_end_date'] = $uips_end_date;
            $general_assessment_outcomes['uips_status'] = $uips_status;
            
            $general_assessment_outcomes['no_soap_condition_start_date'] = $no_soap_condition_start_date;
            $general_assessment_outcomes['no_soap_condition_end_date'] = $no_soap_condition_end_date;
            $general_assessment_outcomes['no_soap_condition_status'] = $no_soap_condition_status;
            
            $general_assessment_outcomes['understand_hand_sanitizer_start_date'] = $understand_hand_sanitizer_start_date;
            $general_assessment_outcomes['understand_hand_sanitizer_end_date'] = $understand_hand_sanitizer_end_date;
            $general_assessment_outcomes['understand_hand_sanitizer_status'] = $understand_hand_sanitizer_status;
        }

        return $general_assessment_outcomes;
    }


    //CAREGIVER ASSESEMENT
    public function caregiverAssesment($questions_answers)
    {
        $caregiver_assesment_outcomes = [];
        if (!empty($questions_answers['caregiver_assessment'])) {
            $caregiver_assesment = $questions_answers['caregiver_assessment'];

            $needHelp = !empty($caregiver_assesment['every_day_activities']) && $caregiver_assesment['every_day_activities'] == "Yes" ? true : false;
            $medications_help = !empty($caregiver_assesment['medications']) && $caregiver_assesment['medications'] == "Yes" ? true : false;
            $adls = !empty($caregiver_assesment['adls']) && $caregiver_assesment['adls'] == "Yes" ? true : false;
            $adls_no = !empty($caregiver_assesment['adls_no']) && $caregiver_assesment['adls_no'] == "Yes" ? true : false;
            $wife_help = @$caregiver_assesment['your_help_wife'] ?? '';
            $live_with_patient = @$caregiver_assesment['live_patient'] ?? '';
            /* Everyday activities */
            if (!$needHelp) {
                $caregiver_assesment_outcomes['every_day_activities'] = "No need of anyone else for every day activities";
            } else {
                $caregiver_assesment_outcomes['every_day_activities'] = "Need someone else for every day activities";
            }
            if (!$medications_help) {
                $caregiver_assesment_outcomes['medications'] = "No need for help to take medications";
            } else {
                if (!$adls && !$adls_no) {
                    $caregiver_assesment_outcomes['medications'] = "Need help to take medications but no care giver and not referred to home health";
                } elseif (!$adls && $adls_no) {
                    $caregiver_assesment_outcomes['medications'] = "Need help to take medications but no care giver, referred to home health";
                } else {
                    $caregiver_assesment_outcomes['medications'] = "Need help to take medications from "  . $wife_help;
                }
            }
        }
        return $caregiver_assesment_outcomes;
    }

    //OTHER PROVIDER
    public function otherProvider($questions_answers)
    {
        //Other Providers
        $other_providers_outcome = [];
        if (!empty($questions_answers['other_Provider'])) {
            $other_provider = $questions_answers['other_Provider'];

            $otherProvider = !empty($other_provider['other_provider_beside_pcp']) && $other_provider['other_provider_beside_pcp'] == "Yes" ? true : false;
            
            $providerList = @$other_provider['provider'] ?? "";
            $provider_name = "";
            $speciality = "";

            if ($providerList != "") {
                foreach ($providerList as $key => $value) {
                    if ($key > 0 && $key != sizeof($providerList) -1) {
                        $provider_name .= ', '.$value['full_name'];
                        $speciality .= ', '.$value['speciality'];
                    } elseif ($key == sizeof($providerList) -1 && sizeof($providerList) > 1) {
                        $provider_name .= ' & '.$value['full_name'];
                        $speciality .= ' & '.$value['speciality']. ' repectively';
                    } else {
                        $provider_name .= $value['full_name'];
                        $speciality .= $value['speciality'];
                    }
                }
            }

            /* Everyday activities */
            if (!$otherProvider) {
                $other_providers_outcome['other_provider_beside_pcp'] = "The patient is not seeing any Provider other beside PCP";
            } else {
                $other_providers_outcome['other_provider_beside_pcp'] = "The patient is seeing " . $provider_name . " having spciality in " . $speciality;
            }
        }
        return $other_providers_outcome;
    }


    /**
     * Hypercholesterolemia Careplan 
     * @param  Object $hypercholestrolemia
     * @param  month  $filter month
     * @return \Illuminate\Http\Response
     */
    private function hypercholestrolemiaAssessment($hypercholestrolemia, $filter_month, $careplanType)
    {
        $hypercholestrolemia_outcomes = [];
        if (!empty($hypercholestrolemia)) {

            /* Assessment and Prognosis */
            $ldl_done = @$hypercholestrolemia['ldl_in_last_12months'] ?? "";
            $ldl_value = @$hypercholestrolemia['ldl_value'] ?? "";
            $ldl_date = @$hypercholestrolemia['ldl_date'] ?? "";
            $ascvd_patient = @$hypercholestrolemia['patient_has_ascvd'] ?? "";
            $fasting_direct_ldl_value = @$hypercholestrolemia['ldlvalue_190ormore'] ?? "";
            $pure_hypercholesterolemia = @$hypercholestrolemia['pure_hypercholesterolemia'] ?? "";
            $active_diabetes = @$hypercholestrolemia['active_diabetes'] ?? "";
            $statin_prescribed = @$hypercholestrolemia['statin_prescribed'] ?? "";
            $statintype_dosage = @$hypercholestrolemia['statintype_dosage'] ?? "";


            /* Scenario 1 */
            if ($ldl_done == "No") {
                $hypercholestrolemia_outcomes['prognosis'] = 'Guarded';
                $hypercholestrolemia_outcomes['assessment'] = 'Ordered Fasting Lipid Profile';
            }

            /* Scenario 2 */
            $other_questions = (($ascvd_patient == "No" || $ascvd_patient == "" && $fasting_direct_ldl_value == "No" || $fasting_direct_ldl_value == "" && $pure_hypercholesterolemia == "No" || $pure_hypercholesterolemia == "" && $active_diabetes == "No" || $active_diabetes == "") ? "No" : "");
            if ($ldl_done == "Yes" && $ldl_value >= "160" && $other_questions == "No") {
                $hypercholestrolemia_outcomes['prognosis'] = 'Guarded';
                
                if ($ldl_value != "" && $ldl_date != "") {
                    $hypercholestrolemia_outcomes['assessment'] = "Patient LDL is " .$ldl_value. " mg/dl on " .$ldl_date. ". Patient should follow up with PCP for possible statin therapy.";
                }elseif ($ldl_value != "" && $ldl_date == "") {
                    $hypercholestrolemia_outcomes['assessment'] = "Patient LDL is " .$ldl_value. " mg/dl. Patient should follow up with PCP for possible statin therapy.";
                }
            }

            /* Scenario 3 */
            if ($ldl_done == "Yes" && $ldl_value < "160" && $other_questions == "No") {
                $hypercholestrolemia_outcomes['prognosis'] = 'Good';
                
                if ($ldl_value != "" && $ldl_date != "") {
                    $hypercholestrolemia_outcomes['assessment'] = "Patient LDL is " .$ldl_value. " mg/dl on ".$ldl_date;
                }elseif ($ldl_value != "" && $ldl_date == "") {
                    $hypercholestrolemia_outcomes['assessment'] = "Patient LDL is " .$ldl_value. " mg/dl";
                }
            }

            /* Scenario 4 && scenario 5 Prognosis is based on LDL value
            ** Assessment is same */
            if ($ascvd_patient == "Yes" && $statin_prescribed == "Yes" && $statintype_dosage != "") {

                if ($ldl_value < "100") {
                    $hypercholestrolemia_outcomes['prognosis'] = 'Good';
                }elseif ($ldl_value > "100") {
                    $hypercholestrolemia_outcomes['prognosis'] = 'Fair';
                }

                if ($ldl_value != "" && $ldl_date != "") {
                    $hypercholestrolemia_outcomes['assessment'] = "Patient LDL is " .$ldl_value. " mg/dl on " .$ldl_date. ". Patient is receiving statin therapy with " .$statintype_dosage. " as prescribed by PCP";
                }elseif ($ldl_value != "" && $ldl_date == "" && $statintype_dosage != "") {
                    $hypercholestrolemia_outcomes['assessment'] = "Patient LDL is " .$ldl_value. " mg/dl. Patient is receiving statin therapy with " .$statintype_dosage. " as prescribed by PCP";
                }elseif ($ldl_value != "" && $ldl_date == "" && $statintype_dosage == "") {
                    $hypercholestrolemia_outcomes['assessment'] = "Patient LDL is " .$ldl_value. " mg/dl. Patient is receiving statin therapy as prescribed by PCP";
                }
            }

            /* Scenario 6 && 7*/
            if ($ascvd_patient == "Yes" && $statin_prescribed == "No") {

                $assessment_statement = "";
                if ($ldl_value == "90") {
                    $hypercholestrolemia_outcomes['prognosis'] = 'Fair';
                    $assessment_statement = "Counseled and advised to follow up with PCP for Statin therapy.";
                }elseif ($ldl_value > "100") {
                    $hypercholestrolemia_outcomes['prognosis'] = 'Guarded';
                    $assessment_statement = "Counseled and advised to follow up with PCP.";
                }

                if ($ldl_value != "" && $ldl_date != "") {
                    $hypercholestrolemia_outcomes['assessment'] = "Patient LDL is " .$ldl_value. " mg/dl on ".$ldl_date. ". " .$assessment_statement;
                }elseif ($ldl_value != "" && $ldl_date == "") {
                    $hypercholestrolemia_outcomes['assessment'] = "Patient LDL is " .$ldl_value. " mg/dl.". $assessment_statement;
                }
            }

            /* GOALS and Tasks */
            if ($filter_month != "") {
                $hypercholestrolemia_goals = $this->filterMonthlyAssessment($hypercholestrolemia, $filter_month, $careplanType);
    
                if (is_array($hypercholestrolemia_goals)) {
                    foreach ($hypercholestrolemia as $key => $value) {
                        if ((strpos($key, "start_date") || strpos($key, "end_date")) && !array_key_exists($key, $hypercholestrolemia_goals)) {
                            unset($hypercholestrolemia[$key]);
                        }
                    }
                }
            }
            
        
            // $assesment_done = !empty($hypercholestrolemia['assesment_done']) ? $hypercholestrolemia['assesment_done'] : "";
            // $statin_intensity = !empty($hypercholestrolemia['statin_intensity']) ? $hypercholestrolemia['statin_intensity'] : "";
            // $ldl_goal = !empty($hypercholestrolemia['ldl_goal']) ? $hypercholestrolemia['ldl_goal'] : "";
        
            /* GOAL 1 */
            $causes_of_hyperlipidemia_start_date = @$hypercholestrolemia['causes_of_hyperlipidemia_start_date'] ?? "";
            $causes_of_hyperlipidemia_end_date = @$hypercholestrolemia['causes_of_hyperlipidemia_end_date'] ?? "";
            $causes_of_hyperlipidemia_status = $this->calculateStatus($causes_of_hyperlipidemia_start_date, $causes_of_hyperlipidemia_end_date);
            
            $saturated_trans_fat_start_date = @$hypercholestrolemia['saturated_trans_fat_start_date'] ?? "";
            $saturated_trans_fat_end_date = @$hypercholestrolemia['saturated_trans_fat_end_date'] ?? "";
            $saturated_trans_fat_status = $this->calculateStatus($saturated_trans_fat_start_date, $saturated_trans_fat_end_date);

            $lab_mandatory_start_date = @$hypercholestrolemia['lab_mandatory_start_date'] ??  "";
            $lab_mandatory_end_date = @$hypercholestrolemia['lab_mandatory_end_date'] ??  "";
            $lab_mandatory_status = $this->calculateStatus($lab_mandatory_start_date, $lab_mandatory_end_date);

            $monitor_comorbid_start_date = @$hypercholestrolemia['monitor_comorbid_start_date'] ??  "";
            $monitor_comorbid_end_date = @$hypercholestrolemia['monitor_comorbid_end_date'] ??  "";
            $monitor_comorbid_status = $this->calculateStatus($monitor_comorbid_start_date, $monitor_comorbid_end_date);

            $hypercholestrolemia_outcomes['causes_of_hyperlipidemia_start_date'] = $causes_of_hyperlipidemia_start_date;
            $hypercholestrolemia_outcomes['causes_of_hyperlipidemia_end_date'] = $causes_of_hyperlipidemia_end_date;
            $hypercholestrolemia_outcomes['causes_of_hyperlipidemia_status'] = $causes_of_hyperlipidemia_status;
            
            $hypercholestrolemia_outcomes['saturated_trans_fat_start_date'] = $saturated_trans_fat_start_date;
            $hypercholestrolemia_outcomes['saturated_trans_fat_end_date'] = $saturated_trans_fat_end_date;
            $hypercholestrolemia_outcomes['saturated_trans_fat_status'] = $saturated_trans_fat_status;

            $hypercholestrolemia_outcomes['lab_mandatory_start_date'] = $lab_mandatory_start_date;
            $hypercholestrolemia_outcomes['lab_mandatory_end_date'] = $lab_mandatory_end_date;
            $hypercholestrolemia_outcomes['lab_mandatory_status'] = $lab_mandatory_status;

            $hypercholestrolemia_outcomes['monitor_comorbid_start_date'] = $monitor_comorbid_start_date;
            $hypercholestrolemia_outcomes['monitor_comorbid_end_date'] = $monitor_comorbid_end_date;
            $hypercholestrolemia_outcomes['monitor_comorbid_status'] = $monitor_comorbid_status;

            $hypercholestrolemia_outcomes['goal1_status'] = "";
            $goal1_task_status = [
                $causes_of_hyperlipidemia_status,
                $saturated_trans_fat_status,
                $lab_mandatory_status,
                $monitor_comorbid_status,
            ];
            $counts = array_count_values($goal1_task_status);

            if (@$counts['Completed'] === sizeof($goal1_task_status)) {
                $hypercholestrolemia_outcomes['goal1_status'] = "Completed";
            } else if (@$counts['Completed'] > 0 && @$counts['Completed'] < sizeof($goal1_task_status)) {
                $hypercholestrolemia_outcomes['goal1_status'] = "In progress";
            } else if (@$counts['Started'] > 0) {
                $hypercholestrolemia_outcomes['goal1_status'] = "In progress";
            }
            /* GOAL 1 Ends Here */

            // return $hypercholestrolemia_outcomes;
            
            /* Goal 2 TASKS AND GOAL STATUS*/
            $understand_etiology_start_date = @$hypercholestrolemia['understand_etiology_start_date'] ?? "";
            $understand_etiology_end_date = @$hypercholestrolemia['understand_etiology_end_date'] ?? "";
            $understand_etiology_status = $this->calculateStatus($understand_etiology_start_date, $understand_etiology_end_date);
        
            $calculate_ASCVD_start_date = @$hypercholestrolemia['calculate_ASCVD_start_date'] ?? "";
            $calculate_ASCVD_end_date = @$hypercholestrolemia['calculate_ASCVD_end_date'] ?? "";
            $calculate_ASCVD_status = $this->calculateStatus($calculate_ASCVD_start_date, $calculate_ASCVD_end_date);
            
            /* Creating response with task dates and task status */
            $hypercholestrolemia_outcomes['understand_etiology_start_date'] = $understand_etiology_start_date;
            $hypercholestrolemia_outcomes['understand_etiology_end_date'] = $understand_etiology_end_date;
            $hypercholestrolemia_outcomes['understand_etiology_status'] = $understand_etiology_status;
            
            $hypercholestrolemia_outcomes['calculate_ASCVD_start_date'] = $calculate_ASCVD_start_date;
            $hypercholestrolemia_outcomes['calculate_ASCVD_end_date'] = $calculate_ASCVD_end_date;
            $hypercholestrolemia_outcomes['calculate_ASCVD_status'] = $calculate_ASCVD_status;

            /* Assign Goal Status as per Task status */
            $hypercholestrolemia_outcomes['goal2_status'] = "";
            $goal2_task_status = [
                $understand_etiology_status,
                $calculate_ASCVD_status,
            ];
            $counts = array_count_values($goal2_task_status);

            if (@$counts['Completed'] === sizeof($goal2_task_status)) {
                $hypercholestrolemia_outcomes['goal2_status'] = "Completed";
            } else if (@$counts['Completed'] > 0 && @$counts['Completed'] < sizeof($goal2_task_status)) {
                $hypercholestrolemia_outcomes['goal2_status'] = "In progress";
            } else if (@$counts['Started'] > 0) {
                $hypercholestrolemia_outcomes['goal2_status'] = "In progress";
            }
            /* GOAL 2 Ends Here */


            /* GOAL 3 */
            $dietary_factors_start_date = @$hypercholestrolemia['dietary_factors_start_date'] ?? "";
            $dietary_factors_end_date = @$hypercholestrolemia['dietary_factors_end_date'] ?? "";
            $dietary_factors_status = $this->calculateStatus($dietary_factors_start_date, $dietary_factors_end_date);

            $visiting_nutritionist_start_date = @$hypercholestrolemia['visiting_nutritionist_start_date'] ?? "";
            $visiting_nutritionist_end_date = @$hypercholestrolemia['visiting_nutritionist_end_date'] ?? "";
            $visiting_nutritionist_status = $this->calculateStatus($visiting_nutritionist_start_date, $visiting_nutritionist_end_date);

            /* Creating response with task dates and task status */
            $hypercholestrolemia_outcomes['dietary_factors_start_date'] = $dietary_factors_start_date;
            $hypercholestrolemia_outcomes['dietary_factors_end_date'] = $dietary_factors_end_date;
            $hypercholestrolemia_outcomes['dietary_factors_status'] = $dietary_factors_status;
            
            $hypercholestrolemia_outcomes['visiting_nutritionist_start_date'] = $visiting_nutritionist_start_date;
            $hypercholestrolemia_outcomes['visiting_nutritionist_end_date'] = $visiting_nutritionist_end_date;
            $hypercholestrolemia_outcomes['visiting_nutritionist_status'] = $visiting_nutritionist_status;

            /* Assign Goal Status as per Task status */
            $hypercholestrolemia_outcomes['goal3_status'] = "";
            $goal3_task_status = [
                $dietary_factors_status,
                $visiting_nutritionist_status,
            ];
            $counts = array_count_values($goal3_task_status);

            if (@$counts['Completed'] === sizeof($goal3_task_status)) {
                $hypercholestrolemia_outcomes['goal3_status'] = "Completed";
            } else if (@$counts['Completed'] > 0 && @$counts['Completed'] < sizeof($goal3_task_status)) {
                $hypercholestrolemia_outcomes['goal3_status'] = "In progress";
            } else if (@$counts['Started'] > 0) {
                $hypercholestrolemia_outcomes['goal3_status'] = "In progress";
            }
            /* GOAL 3 Ends Here */


            /* GOAL 4 With TASKS*/
            /* $ue_exercise_start_date = @$hypercholestrolemia['ue_exercise_start_date'] ?? "";
            $ue_exercise_end_date = @$hypercholestrolemia['ue_exercise_end_date'] ?? "";
            $ue_exercise_status = $this->calculateStatus($ue_exercise_start_date, $ue_exercise_end_date); */

            $amount_of_exercise_start_date = @$hypercholestrolemia['amount_of_exercise_start_date'] ?? "";
            $amount_of_exercise_end_date = @$hypercholestrolemia['amount_of_exercise_end_date'] ?? "";
            $amount_of_exercise_status = $this->calculateStatus($amount_of_exercise_start_date, $amount_of_exercise_end_date);

            $effect_of_exercise_start_date = @$hypercholestrolemia['effect_of_exercise_start_date'] ?? "";
            $effect_of_exercise_end_date = @$hypercholestrolemia['effect_of_exercise_end_date'] ?? "";
            $effect_of_exercise_status = $this->calculateStatus($effect_of_exercise_start_date, $effect_of_exercise_end_date);

            /* Assign Goal Status as per Task status */
            $hypercholestrolemia_outcomes['goal4_status'] = "";
            $goal4_task_status = [
                $amount_of_exercise_status,
                $effect_of_exercise_status,
            ];
            $counts = array_count_values($goal4_task_status);

            if (@$counts['Completed'] === sizeof($goal4_task_status)) {
                $hypercholestrolemia_outcomes['goal4_status'] = "Completed";
            } else if (@$counts['Completed'] > 0 && @$counts['Completed'] < sizeof($goal4_task_status)) {
                $hypercholestrolemia_outcomes['goal4_status'] = "In progress";
            } else if (@$counts['Started'] > 0) {
                $hypercholestrolemia_outcomes['goal4_status'] = "In progress";
            }


            /* Creating response with task dates and task status */
            $hypercholestrolemia_outcomes['amount_of_exercise_start_date'] = $amount_of_exercise_start_date;
            $hypercholestrolemia_outcomes['amount_of_exercise_end_date'] = $amount_of_exercise_end_date;
            $hypercholestrolemia_outcomes['amount_of_exercise_status'] = $amount_of_exercise_status;
            
            $hypercholestrolemia_outcomes['effect_of_exercise_start_date'] = $effect_of_exercise_start_date;
            $hypercholestrolemia_outcomes['effect_of_exercise_end_date'] = $effect_of_exercise_end_date;
            $hypercholestrolemia_outcomes['effect_of_exercise_status'] = $effect_of_exercise_status;


            /* if ($statin_intensity == "Yes" && $ldl_goal == "Yes") {
                $hypercholestrolemia_outcomes['prognosis'] = "Patient has a good prognosis as patient is on moderate to high intensity statin and his LDL is at goal.";
            } elseif ($statin_intensity == "Yes" && $ldl_goal == "No") {
                $hypercholestrolemia_outcomes['prognosis'] = "Patient has a fair prognosis as patient is on moderate to high intensity statin but his LDL is not at goal";
            } elseif ($statin_intensity == "No" && $ldl_goal == "Yes") {
                $hypercholestrolemia_outcomes['prognosis'] = "Patient has a fair prognosis as he is not on Statin but his LDL is at goal";
            } elseif ($statin_intensity == "No" && $ldl_goal == "No") {
                $hypercholestrolemia_outcomes['prognosis'] = "Patient has poor prognosis as he is not on moderate to high intensity statin and his LDL is not at goal";
            } else if ($assesment_done == "Yes") {
                $hypercholestrolemia_outcomes['prognosis'] = "Hypercholesterolemia assessment has already performed";
            } */
        }
        return $hypercholestrolemia_outcomes;
    }

    /**
     * Diabetes Militus Careplan 
     * @param  Object $diabetes
     * @param  month  $filter month
     * @return \Illuminate\Http\Response
     */
    private function diabetesMilitusAssessment($diabetes, $filter_month, $careplanType)
    {
        $diabetes_outcome = [];
        if (!empty($diabetes)) {

            if ($filter_month != "") {
                $diabetes_mellitus_goals = $this->filterMonthlyAssessment($diabetes, $filter_month, $careplanType);
    
                if (is_array($diabetes_mellitus_goals)) {
                    foreach ($diabetes as $key => $value) {
                        if ((strpos($key, "start_date") || strpos($key, "end_date")) && !array_key_exists($key, $diabetes_mellitus_goals)) {
                            unset($diabetes[$key]);
                        }
                    }
                }
            }

            $hba1c_result = @$diabetes['hb_result'] ?? "";
            $hba1c_date = @$diabetes['result_month'] ?? "";
            $ldl_value = @$diabetes['ldl_value'] ?? "";
            $ldl_date = @$diabetes['ldl_date'] ?? "";

            $hba1c_record = "";
            if ($hba1c_result!= "" && $hba1c_date!= "") {
                $hba1c_record = "Patient A1c is ".$hba1c_result." on ".$hba1c_date.", ";
            }
            
            $ldl_record = "";
            if ($hba1c_result!= "" && $hba1c_date!= "") {
                $ldl_record = "LDL is ".$ldl_value." on ".$ldl_date.". ";
            }

            switch ([$hba1c_result, $ldl_value]) {
                /* Scenario 1 */
                case ($hba1c_result < '7' && $ldl_value < '70'):
                    $diabetes_outcome['prognosis'] = "Good";
                    $diabetes_outcome['assessment'] = $hba1c_record.$ldl_record."Patient’s Diabetes is under control. Advised compliance to treatment and follow up with PCP to have A1c checked every 3 months.";
                break;
                
                /* Scenario 2 */
                case ($hba1c_result < '7' && ($ldl_value >= '70' && $ldl_value <= '90')):
                    $diabetes_outcome['prognosis'] = "Fair";
                    $diabetes_outcome['assessment'] = $hba1c_record.$ldl_record."Patient’s Diabetes is under control. Advised compliance to treatment and follow up with PCP to have A1c checked every 3 months. Patient cholesterol is also elevated, advised on a low cholesterol diet and exercise and the need to lower cholesterol more.";
                break;
                
                /* Scenario 3 */
                case ($hba1c_result < '7' && $ldl_value > '90'):
                    $diabetes_outcome['prognosis'] = "Guarded";
                    $diabetes_outcome['assessment'] = $hba1c_record.$ldl_record."Patient diabetes is well controlled. Advised compliance to treatment and follow up with PCP to have A1c checked every 3 months. Patient cholesterol is slightly elevated, advised on a low cholesterol diet and exercise and the need to lower cholesterol more. Advise to follow up with PCP for control of cholesterol.";
                break;
                
                /* Scenario 4 */
                case (($hba1c_result >= '7' && $hba1c_result <= '8') && $ldl_value < '70'):
                    $diabetes_outcome['prognosis'] = "Fair";
                    $diabetes_outcome['assessment'] = $hba1c_record.$ldl_record."Patient diabetes is not well controlled, advised to maintain BS log and see the PCP regularly.";
                break;
                
                /* Scenario 5 */
                case ($hba1c_result >= '7' && $hba1c_result <= '8' && $ldl_value > '70'):
                    $diabetes_outcome['prognosis'] = "Guarded";
                    $diabetes_outcome['assessment'] = $hba1c_record.$ldl_record."Patient diabetes is not well controlled, advised to bring BS log and see PCP regularly. Patient cholesterol is also elevated, advised on a low cholesterol diet and exercise.";
                break;
                
                /* Scenario 6 */
                case ($hba1c_result > '8' || $ldl_value > '90'):
                    $diabetes_outcome['prognosis'] = "Guarded";
                    $diabetes_outcome['assessment'] = $hba1c_record.$ldl_record."Patient diabetes is poorly controlled. Patient advised to schedule appointment in 2 weeks and bring BS log to the next PCP appointment.";
                break;
                
                default:
                    $diabetes_outcome['prognosis'] = "";
                    $diabetes_outcome['assessment'] = "";
                break;
            }

            /* Goal 1 tasks */
            $monitoring_blood_sugar_start_date = !empty($diabetes['monitoring_blood_sugar_start_date']) ? $diabetes['monitoring_blood_sugar_start_date'] : "";
            $monitoring_blood_sugar_end_date = !empty($diabetes['monitoring_blood_sugar_end_date']) ? $diabetes['monitoring_blood_sugar_end_date'] : "";
            $monitoring_blood_sugar_status = $this->calculateStatus($monitoring_blood_sugar_start_date, $monitoring_blood_sugar_end_date);
            
            $importance_of_weight_start_date = !empty($diabetes['importance_of_weight_start_date']) ? $diabetes['importance_of_weight_start_date'] : "";
            $importance_of_weight_end_date = !empty($diabetes['importance_of_weight_end_date']) ? $diabetes['importance_of_weight_end_date'] : "";
            $importance_of_weight_status = $this->calculateStatus($importance_of_weight_start_date, $importance_of_weight_end_date);
           
            $assess_the_pattern_start_date = !empty($diabetes['assess_the_pattern_start_date']) ? $diabetes['assess_the_pattern_start_date'] : "";
            $assess_the_pattern_end_date = !empty($diabetes['assess_the_pattern_end_date']) ? $diabetes['assess_the_pattern_end_date'] : "";
            $assess_the_pattern_status = $this->calculateStatus($assess_the_pattern_start_date, $assess_the_pattern_end_date);
           
            $monitor_blood_glucose_start_date = !empty($diabetes['monitor_blood_glucose_start_date']) ? $diabetes['monitor_blood_glucose_start_date'] : "";
            $monitor_blood_glucose_end_date = !empty($diabetes['monitor_blood_glucose_end_date']) ? $diabetes['monitor_blood_glucose_end_date'] : "";
            $monitor_blood_glucose_status = $this->calculateStatus($monitor_blood_glucose_start_date, $monitor_blood_glucose_end_date);

            /* Assign Goal Status as per Task status */
            $diabetes_outcome['goal1_status'] = "";
            $goal1_task_status = [
                $monitoring_blood_sugar_status,
                $importance_of_weight_status,
                $assess_the_pattern_status,
                $monitor_blood_glucose_status,
            ];
            $counts = array_count_values($goal1_task_status);

            if (@$counts['Completed'] === sizeof($goal1_task_status)) {
                $diabetes_outcome['goal1_status'] = "Completed";
            } else if (@$counts['Completed'] > 0 && @$counts['Completed'] < sizeof($goal1_task_status)) {
                $diabetes_outcome['goal1_status'] = "In progress";
            } else if (@$counts['Started'] > 0) {
                $diabetes_outcome['goal1_status'] = "In progress";
            }
            /* GOAL 1 ENDS HERE */

            // GOAL 2 STARTS
                $abc_of_diabetes_start_date = !empty($diabetes['abc_of_diabetes_start_date']) ? $diabetes['abc_of_diabetes_start_date'] : "";
                $abc_of_diabetes_end_date = !empty($diabetes['abc_of_diabetes_end_date']) ? $diabetes['abc_of_diabetes_end_date'] : "";
                $abc_of_diabetes_status = $this->calculateStatus($abc_of_diabetes_start_date, $abc_of_diabetes_end_date);
                
                $undercontrol_weight_start_date = !empty($diabetes['undercontrol_weight_start_date']) ? $diabetes['undercontrol_weight_start_date'] : "";
                $undercontrol_weight_end_date = !empty($diabetes['undercontrol_weight_end_date']) ? $diabetes['undercontrol_weight_end_date'] : "";
                $undercontrol_weight_status = $this->calculateStatus($undercontrol_weight_start_date, $undercontrol_weight_end_date);
                
                $seeing_dietician_start_date = !empty($diabetes['seeing_dietician_start_date']) ? $diabetes['seeing_dietician_start_date'] : "";
                $seeing_dietician_end_date = !empty($diabetes['seeing_dietician_end_date']) ? $diabetes['seeing_dietician_end_date'] : "";
                $seeing_dietician_status = $this->calculateStatus($seeing_dietician_start_date, $seeing_dietician_end_date);

                /* Assign Goal Status as per Task status */
                $diabetes_outcome['goal2_status'] = "";
                $goal2_task_status = [
                    $abc_of_diabetes_status,
                    $undercontrol_weight_status,
                    $seeing_dietician_status,
                ];
                $counts = array_count_values($goal2_task_status);

                if (@$counts['Completed'] === sizeof($goal2_task_status)) {
                    $diabetes_outcome['goal2_status'] = "Completed";
                } else if (@$counts['Completed'] > 0 && @$counts['Completed'] < sizeof($goal2_task_status)) {
                    $diabetes_outcome['goal2_status'] = "In progress";
                } else if (@$counts['Started'] > 0) {
                    $diabetes_outcome['goal2_status'] = "In progress";
                }
            // GOAL 2 ENDS
            

            /* GOAL 3 tasks*/
                $signs_of_hyperglycemia_start_date = !empty($diabetes['signs_of_hyperglycemia_start_date']) ? $diabetes['signs_of_hyperglycemia_start_date'] : "";
                $signs_of_hyperglycemia_end_date = !empty($diabetes['signs_of_hyperglycemia_end_date']) ? $diabetes['signs_of_hyperglycemia_end_date'] : "";
                $signs_of_hyperglycemia_status = $this->calculateStatus($signs_of_hyperglycemia_start_date, $signs_of_hyperglycemia_end_date);
            
                $prevention_of_hyperglycemia_start_date = !empty($diabetes['prevention_of_hyperglycemia_start_date']) ? $diabetes['prevention_of_hyperglycemia_start_date'] : "";
                $prevention_of_hyperglycemia_end_date = !empty($diabetes['prevention_of_hyperglycemia_end_date']) ? $diabetes['prevention_of_hyperglycemia_end_date'] : "";
                $prevention_of_hyperglycemia_status = $this->calculateStatus($prevention_of_hyperglycemia_start_date, $prevention_of_hyperglycemia_end_date);
            
                $lower_blood_sugar_start_date = !empty($diabetes['lower_blood_sugar_start_date']) ? $diabetes['lower_blood_sugar_start_date'] : "";
                $lower_blood_sugar_end_date = !empty($diabetes['lower_blood_sugar_end_date']) ? $diabetes['lower_blood_sugar_end_date'] : "";
                $lower_blood_sugar_status = $this->calculateStatus($lower_blood_sugar_start_date, $lower_blood_sugar_end_date);

                /* Assign Goal Status as per Task status */
                $diabetes_outcome['goal3_status'] = "";
                $goal3_task_status = [
                    $signs_of_hyperglycemia_status,
                    $prevention_of_hyperglycemia_status,
                    $lower_blood_sugar_status,
                ];
                $counts = array_count_values($goal3_task_status);

                if (@$counts['Completed'] === sizeof($goal3_task_status)) {
                    $diabetes_outcome['goal3_status'] = "Completed";
                } else if (@$counts['Completed'] > 0 && @$counts['Completed'] < sizeof($goal3_task_status)) {
                    $diabetes_outcome['goal3_status'] = "In progress";
                } else if (@$counts['Started'] > 0) {
                    $diabetes_outcome['goal3_status'] = "In progress";
                }
            /* GOAL 3 ENDS HERE */
            
            /* GOAL 4 tasks*/
                $sugar_effect_on_eye_start_date = !empty($diabetes['sugar_effect_on_eye_start_date']) ? $diabetes['sugar_effect_on_eye_start_date'] : "";
                $sugar_effect_on_eye_end_date = !empty($diabetes['sugar_effect_on_eye_end_date']) ? $diabetes['sugar_effect_on_eye_end_date'] : "";
                $sugar_effect_on_eye_status = $this->calculateStatus($sugar_effect_on_eye_start_date, $sugar_effect_on_eye_end_date);
            
                $sugar_ways_to_effect_on_eye_start_date = !empty($diabetes['sugar_ways_to_effect_on_eye_start_date']) ? $diabetes['sugar_ways_to_effect_on_eye_start_date'] : "";
                $sugar_ways_to_effect_on_eye_end_date = !empty($diabetes['sugar_ways_to_effect_on_eye_end_date']) ? $diabetes['sugar_ways_to_effect_on_eye_end_date'] : "";
                $sugar_ways_to_effect_on_eye_status = $this->calculateStatus($sugar_ways_to_effect_on_eye_start_date, $sugar_ways_to_effect_on_eye_end_date);

                /* Assign Goal Status as per Task status */
                $diabetes_outcome['goal4_status'] = "";
                $goal4_task_status = [
                    $sugar_effect_on_eye_status,
                    $sugar_ways_to_effect_on_eye_status,
                ];
                $counts = array_count_values($goal4_task_status);

                if (@$counts['Completed'] === sizeof($goal4_task_status)) {
                    $diabetes_outcome['goal4_status'] = "Completed";
                } else if (@$counts['Completed'] > 0 && @$counts['Completed'] < sizeof($goal4_task_status)) {
                    $diabetes_outcome['goal4_status'] = "In progress";
                } else if (@$counts['Started'] > 0) {
                    $diabetes_outcome['goal4_status'] = "In progress";
                }
            /* GOAL 4 ENDS HERE */

            /* GOAL 5 STARTS HERE */
                $foot_nerves_damage_start_date = !empty($diabetes['foot_nerves_damage_start_date']) ? $diabetes['foot_nerves_damage_start_date'] : "";
                $foot_nerves_damage_end_date = !empty($diabetes['foot_nerves_damage_end_date']) ? $diabetes['foot_nerves_damage_end_date'] : "";
                $foot_nerves_damage_status = $this->calculateStatus($foot_nerves_damage_start_date, $foot_nerves_damage_end_date);
                
                $protect_feet_start_date = !empty($diabetes['protect_feet_start_date']) ? $diabetes['protect_feet_start_date'] : "";
                $protect_feet_end_date = !empty($diabetes['protect_feet_end_date']) ? $diabetes['protect_feet_end_date'] : "";
                $protect_feet_status = $this->calculateStatus($protect_feet_start_date, $protect_feet_end_date);
                
                $foot_examination_start_date = !empty($diabetes['foot_examination_start_date']) ? $diabetes['foot_examination_start_date'] : "";
                $foot_examination_end_date = !empty($diabetes['foot_examination_end_date']) ? $diabetes['foot_examination_end_date'] : "";
                $foot_examination_status = $this->calculateStatus($foot_examination_start_date, $foot_examination_end_date);

                /* Assign Goal Status as per Task status */
                $diabetes_outcome['goal5_status'] = "";
                $goal5_task_status = [
                    $foot_nerves_damage_status,
                    $protect_feet_status,
                    $foot_examination_status,
                ];
                $counts = array_count_values($goal5_task_status);

                if (@$counts['Completed'] === sizeof($goal5_task_status)) {
                    $diabetes_outcome['goal5_status'] = "Completed";
                } else if (@$counts['Completed'] > 0 && @$counts['Completed'] < sizeof($goal5_task_status)) {
                    $diabetes_outcome['goal5_status'] = "In progress";
                } else if (@$counts['Started'] > 0) {
                    $diabetes_outcome['goal5_status'] = "In progress";
                }
            /* GOAL 5 ENDS HERE */

            /* GOAL 6 STARTS HERE */
                $death_cause_in_diabetes_start_date = !empty($diabetes['death_cause_in_diabetes_start_date']) ? $diabetes['death_cause_in_diabetes_start_date'] : "";
                $death_cause_in_diabetes_end_date = !empty($diabetes['death_cause_in_diabetes_end_date']) ? $diabetes['death_cause_in_diabetes_end_date'] : "";
                $death_cause_in_diabetes_status = $this->calculateStatus($death_cause_in_diabetes_start_date, $death_cause_in_diabetes_end_date);
                
                $risk_of_cardio_disease_start_date = !empty($diabetes['risk_of_cardio_disease_start_date']) ? $diabetes['risk_of_cardio_disease_start_date'] : "";
                $risk_of_cardio_disease_end_date = !empty($diabetes['risk_of_cardio_disease_end_date']) ? $diabetes['risk_of_cardio_disease_end_date'] : "";
                $risk_of_cardio_disease_status = $this->calculateStatus($risk_of_cardio_disease_start_date, $risk_of_cardio_disease_end_date);
                
                $cholesterol_healthy_range_start_date = !empty($diabetes['cholesterol_healthy_range_start_date']) ? $diabetes['cholesterol_healthy_range_start_date'] : "";
                $cholesterol_healthy_range_end_date = !empty($diabetes['cholesterol_healthy_range_end_date']) ? $diabetes['cholesterol_healthy_range_end_date'] : "";
                $cholesterol_healthy_range_status = $this->calculateStatus($cholesterol_healthy_range_start_date, $cholesterol_healthy_range_end_date);
                
                $low_dose_aspirin_start_date = !empty($diabetes['low_dose_aspirin_start_date']) ? $diabetes['low_dose_aspirin_start_date'] : "";
                $low_dose_aspirin_end_date = !empty($diabetes['low_dose_aspirin_end_date']) ? $diabetes['low_dose_aspirin_end_date'] : "";
                $low_dose_aspirin_status = $this->calculateStatus($low_dose_aspirin_start_date, $low_dose_aspirin_end_date);

                /* Assign Goal Status as per Task status */
                $diabetes_outcome['goal6_status'] = "";
                $goal6_task_status = [
                    $death_cause_in_diabetes_status,
                    $risk_of_cardio_disease_status,
                    $cholesterol_healthy_range_status,
                    $low_dose_aspirin_status,
                ];
                $counts = array_count_values($goal6_task_status);

                if (@$counts['Completed'] === sizeof($goal6_task_status)) {
                    $diabetes_outcome['goal6_status'] = "Completed";
                } else if (@$counts['Completed'] > 0 && @$counts['Completed'] < sizeof($goal6_task_status)) {
                    $diabetes_outcome['goal6_status'] = "In progress";
                } else if (@$counts['Started'] > 0) {
                    $diabetes_outcome['goal6_status'] = "In progress";
                }
            /* GOAL 6 ENDS HERE */

            /* GOAL 7 STARTS HERE */
                $diabetes_effect_on_kidneys_start_date = !empty($diabetes['diabetes_effect_on_kidneys_start_date']) ? $diabetes['diabetes_effect_on_kidneys_start_date'] : "";
                $diabetes_effect_on_kidneys_end_date = !empty($diabetes['diabetes_effect_on_kidneys_end_date']) ? $diabetes['diabetes_effect_on_kidneys_end_date'] : "";
                $diabetes_effect_on_kidneys_status = $this->calculateStatus($diabetes_effect_on_kidneys_start_date, $diabetes_effect_on_kidneys_end_date);
                
                $know_how_kidneys_effected_start_date = !empty($diabetes['know_how_kidneys_effected_start_date']) ? $diabetes['know_how_kidneys_effected_start_date'] : "";
                $know_how_kidneys_effected_end_date = !empty($diabetes['know_how_kidneys_effected_end_date']) ? $diabetes['know_how_kidneys_effected_end_date'] : "";
                $know_how_kidneys_effected_status = $this->calculateStatus($know_how_kidneys_effected_start_date, $know_how_kidneys_effected_end_date);
                
                $protect_kidneys_start_date = !empty($diabetes['protect_kidneys_start_date']) ? $diabetes['protect_kidneys_start_date'] : "";
                $protect_kidneys_end_date = !empty($diabetes['protect_kidneys_end_date']) ? $diabetes['protect_kidneys_end_date'] : "";
                $protect_kidneys_status = $this->calculateStatus($protect_kidneys_start_date, $protect_kidneys_end_date);

                /* Assign Goal Status as per Task status */
                $diabetes_outcome['goal7_status'] = "";
                $goal7_task_status = [
                    $diabetes_effect_on_kidneys_status,
                    $know_how_kidneys_effected_status,
                    $protect_kidneys_status,
                ];
                $counts = array_count_values($goal7_task_status);

                if (@$counts['Completed'] === sizeof($goal7_task_status)) {
                    $diabetes_outcome['goal7_status'] = "Completed";
                } else if (@$counts['Completed'] > 0 && @$counts['Completed'] < sizeof($goal7_task_status)) {
                    $diabetes_outcome['goal7_status'] = "In progress";
                } else if (@$counts['Started'] > 0) {
                    $diabetes_outcome['goal7_status'] = "In progress";
                }
            /* GOAL 7 ENDS HERE */

            /* GOAL 8 STARTS HERE */
                $bp_recommendation_start_date = !empty($diabetes['bp_recommendation_start_date']) ? $diabetes['bp_recommendation_start_date'] : "";
                $bp_recommendation_end_date = !empty($diabetes['bp_recommendation_end_date']) ? $diabetes['bp_recommendation_end_date'] : "";
                $bp_recommendation_status = $this->calculateStatus($bp_recommendation_start_date, $bp_recommendation_end_date);
                
                $how_to_lower_bp_start_date = !empty($diabetes['how_to_lower_bp_start_date']) ? $diabetes['how_to_lower_bp_start_date'] : "";
                $how_to_lower_bp_end_date = !empty($diabetes['how_to_lower_bp_end_date']) ? $diabetes['how_to_lower_bp_end_date'] : "";
                $how_to_lower_bp_status = $this->calculateStatus($how_to_lower_bp_start_date, $how_to_lower_bp_end_date);

                /* Assign Goal Status as per Task status */
                $diabetes_outcome['goal8_status'] = "";
                $goal8_task_status = [
                    $bp_recommendation_status,
                    $how_to_lower_bp_status,
                ];
                $counts = array_count_values($goal8_task_status);

                if (@$counts['Completed'] === sizeof($goal8_task_status)) {
                    $diabetes_outcome['goal8_status'] = "Completed";
                } else if (@$counts['Completed'] > 0 && @$counts['Completed'] < sizeof($goal8_task_status)) {
                    $diabetes_outcome['goal8_status'] = "In progress";
                } else if (@$counts['Started'] > 0) {
                    $diabetes_outcome['goal8_status'] = "In progress";
                }
            /* GOAL 8 ENDS HERE */


            /* GOAL 9 TASKS */
                $monitor_hunger_and_fatigue_start_date = !empty($diabetes['monitor_hunger_and_fatigue_start_date']) ? $diabetes['monitor_hunger_and_fatigue_start_date'] : "";
                $monitor_hunger_and_fatigue_end_date = !empty($diabetes['monitor_hunger_and_fatigue_end_date']) ? $diabetes['monitor_hunger_and_fatigue_end_date'] : "";
                $monitor_hunger_and_fatigue_status = $this->calculateStatus($monitor_hunger_and_fatigue_start_date, $monitor_hunger_and_fatigue_end_date);
                
                $assess_frequent_urination_start_date = !empty($diabetes['assess_frequent_urination_start_date']) ? $diabetes['assess_frequent_urination_start_date'] : "";
                $assess_frequent_urination_end_date = !empty($diabetes['assess_frequent_urination_end_date']) ? $diabetes['assess_frequent_urination_end_date'] : "";
                $assess_frequent_urination_status = $this->calculateStatus($assess_frequent_urination_start_date, $assess_frequent_urination_end_date);
                
                $assess_slow_healing_start_date = !empty($diabetes['assess_slow_healing_start_date']) ? $diabetes['assess_slow_healing_start_date'] : "";
                $assess_slow_healing_end_date = !empty($diabetes['assess_slow_healing_end_date']) ? $diabetes['assess_slow_healing_end_date'] : "";
                $assess_slow_healing_status = $this->calculateStatus($assess_slow_healing_start_date, $assess_slow_healing_end_date);

                /* Assign Goal Status as per Task status */
                $diabetes_outcome['goal9_status'] = "";
                $goal9_task_status = [
                    $monitor_hunger_and_fatigue_status,
                    $assess_frequent_urination_status,
                    $assess_slow_healing_status,
                ];
                $counts = array_count_values($goal9_task_status);

                if (@$counts['Completed'] === sizeof($goal9_task_status)) {
                    $diabetes_outcome['goal9_status'] = "Completed";
                } else if (@$counts['Completed'] > 0 && @$counts['Completed'] < sizeof($goal9_task_status)) {
                    $diabetes_outcome['goal9_status'] = "In progress";
                } else if (@$counts['Started'] > 0) {
                    $diabetes_outcome['goal9_status'] = "In progress";
                }
            /* GOAL 9 ENDS HERE */

            // GOAL 1
            $diabetes_outcome['monitoring_blood_sugar_start_date'] = $monitoring_blood_sugar_start_date;
            $diabetes_outcome['monitoring_blood_sugar_end_date'] = $monitoring_blood_sugar_end_date;
            $diabetes_outcome['monitoring_blood_sugar_status'] = $monitoring_blood_sugar_status;

            $diabetes_outcome['importance_of_weight_start_date'] = $importance_of_weight_start_date;
            $diabetes_outcome['importance_of_weight_end_date'] = $importance_of_weight_end_date;
            $diabetes_outcome['importance_of_weight_status'] = $importance_of_weight_status;

            $diabetes_outcome['assess_the_pattern_start_date'] = $assess_the_pattern_start_date;
            $diabetes_outcome['assess_the_pattern_end_date'] = $assess_the_pattern_end_date;
            $diabetes_outcome['assess_the_pattern_status'] = $assess_the_pattern_status;

            $diabetes_outcome['monitor_blood_glucose_start_date'] = $monitor_blood_glucose_start_date;
            $diabetes_outcome['monitor_blood_glucose_end_date'] = $monitor_blood_glucose_end_date;
            $diabetes_outcome['monitor_blood_glucose_status'] = $monitor_blood_glucose_status;

            // GOAL 2
            $diabetes_outcome['abc_of_diabetes_start_date'] = $abc_of_diabetes_start_date;
            $diabetes_outcome['abc_of_diabetes_end_date'] = $abc_of_diabetes_end_date;
            $diabetes_outcome['abc_of_diabetes_status'] = $abc_of_diabetes_status;

            $diabetes_outcome['undercontrol_weight_start_date'] = $undercontrol_weight_start_date;
            $diabetes_outcome['undercontrol_weight_end_date'] = $undercontrol_weight_end_date;
            $diabetes_outcome['undercontrol_weight_status'] = $undercontrol_weight_status;

            $diabetes_outcome['seeing_dietician_start_date'] = $seeing_dietician_start_date;
            $diabetes_outcome['seeing_dietician_end_date'] = $seeing_dietician_end_date;
            $diabetes_outcome['seeing_dietician_status'] = $seeing_dietician_status;

            // GOAL 3
            $diabetes_outcome['signs_of_hyperglycemia_start_date'] = $signs_of_hyperglycemia_start_date;
            $diabetes_outcome['signs_of_hyperglycemia_end_date'] = $signs_of_hyperglycemia_end_date;
            $diabetes_outcome['signs_of_hyperglycemia_status'] = $signs_of_hyperglycemia_status;

            $diabetes_outcome['prevention_of_hyperglycemia_start_date'] = $prevention_of_hyperglycemia_start_date;
            $diabetes_outcome['prevention_of_hyperglycemia_end_date'] = $prevention_of_hyperglycemia_end_date;
            $diabetes_outcome['prevention_of_hyperglycemia_status'] = $prevention_of_hyperglycemia_status;

            $diabetes_outcome['lower_blood_sugar_start_date'] = $lower_blood_sugar_start_date;
            $diabetes_outcome['lower_blood_sugar_end_date'] = $lower_blood_sugar_end_date;
            $diabetes_outcome['lower_blood_sugar_status'] = $lower_blood_sugar_status;

            // GOAL 4
            $diabetes_outcome['sugar_effect_on_eye_start_date'] = $sugar_effect_on_eye_start_date;
            $diabetes_outcome['sugar_effect_on_eye_end_date'] = $sugar_effect_on_eye_end_date;
            $diabetes_outcome['sugar_effect_on_eye_status'] = $sugar_effect_on_eye_status;

            $diabetes_outcome['sugar_ways_to_effect_on_eye_start_date'] = $sugar_ways_to_effect_on_eye_start_date;
            $diabetes_outcome['sugar_ways_to_effect_on_eye_end_date'] = $sugar_ways_to_effect_on_eye_end_date;
            $diabetes_outcome['sugar_ways_to_effect_on_eye_status'] = $sugar_ways_to_effect_on_eye_status;

            // GOAL 5
            $diabetes_outcome['foot_nerves_damage_start_date'] = $foot_nerves_damage_start_date;
            $diabetes_outcome['foot_nerves_damage_end_date'] = $foot_nerves_damage_end_date;
            $diabetes_outcome['foot_nerves_damage_status'] = $foot_nerves_damage_status;

            $diabetes_outcome['protect_feet_start_date'] = $protect_feet_start_date;
            $diabetes_outcome['protect_feet_end_date'] = $protect_feet_end_date;
            $diabetes_outcome['protect_feet_status'] = $protect_feet_status;

            $diabetes_outcome['foot_examination_start_date'] = $foot_examination_start_date;
            $diabetes_outcome['foot_examination_end_date'] = $foot_examination_end_date;
            $diabetes_outcome['foot_examination_status'] = $foot_examination_status;

            // GOAL 6
            $diabetes_outcome['death_cause_in_diabetes_start_date'] = $death_cause_in_diabetes_start_date;
            $diabetes_outcome['death_cause_in_diabetes_end_date'] = $death_cause_in_diabetes_end_date;
            $diabetes_outcome['death_cause_in_diabetes_status'] = $death_cause_in_diabetes_status;

            $diabetes_outcome['risk_of_cardio_disease_start_date'] = $risk_of_cardio_disease_start_date;
            $diabetes_outcome['risk_of_cardio_disease_end_date'] = $risk_of_cardio_disease_end_date;
            $diabetes_outcome['risk_of_cardio_disease_status'] = $risk_of_cardio_disease_status;

            $diabetes_outcome['cholesterol_healthy_range_start_date'] = $cholesterol_healthy_range_start_date;
            $diabetes_outcome['cholesterol_healthy_range_end_date'] = $cholesterol_healthy_range_end_date;
            $diabetes_outcome['cholesterol_healthy_range_status'] = $cholesterol_healthy_range_status;

            $diabetes_outcome['low_dose_aspirin_start_date'] = $low_dose_aspirin_start_date;
            $diabetes_outcome['low_dose_aspirin_end_date'] = $low_dose_aspirin_end_date;
            $diabetes_outcome['low_dose_aspirin_status'] = $low_dose_aspirin_status;

            // GOAL 7
            $diabetes_outcome['diabetes_effect_on_kidneys_start_date'] = $diabetes_effect_on_kidneys_start_date;
            $diabetes_outcome['diabetes_effect_on_kidneys_end_date'] = $diabetes_effect_on_kidneys_end_date;
            $diabetes_outcome['diabetes_effect_on_kidneys_status'] = $diabetes_effect_on_kidneys_status;

            $diabetes_outcome['know_how_kidneys_effected_start_date'] = $know_how_kidneys_effected_start_date;
            $diabetes_outcome['know_how_kidneys_effected_end_date'] = $know_how_kidneys_effected_end_date;
            $diabetes_outcome['know_how_kidneys_effected_status'] = $know_how_kidneys_effected_status;

            $diabetes_outcome['protect_kidneys_start_date'] = $protect_kidneys_start_date;
            $diabetes_outcome['protect_kidneys_end_date'] = $protect_kidneys_end_date;
            $diabetes_outcome['protect_kidneys_status'] = $protect_kidneys_status;

            // GOAL 8
            $diabetes_outcome['bp_recommendation_start_date'] = $bp_recommendation_start_date;
            $diabetes_outcome['bp_recommendation_end_date'] = $bp_recommendation_end_date;
            $diabetes_outcome['bp_recommendation_status'] = $bp_recommendation_status;

            $diabetes_outcome['how_to_lower_bp_start_date'] = $how_to_lower_bp_start_date;
            $diabetes_outcome['how_to_lower_bp_end_date'] = $how_to_lower_bp_end_date;
            $diabetes_outcome['how_to_lower_bp_status'] = $how_to_lower_bp_status;

            // GOAL 9
            $diabetes_outcome['monitor_hunger_and_fatigue_start_date'] = $monitor_hunger_and_fatigue_start_date;
            $diabetes_outcome['monitor_hunger_and_fatigue_end_date'] = $monitor_hunger_and_fatigue_end_date;
            $diabetes_outcome['monitor_hunger_and_fatigue_status'] = $monitor_hunger_and_fatigue_status;

            $diabetes_outcome['assess_frequent_urination_start_date'] = $assess_frequent_urination_start_date;
            $diabetes_outcome['assess_frequent_urination_end_date'] = $assess_frequent_urination_end_date;
            $diabetes_outcome['assess_frequent_urination_status'] = $assess_frequent_urination_status;

            $diabetes_outcome['assess_slow_healing_start_date'] = $assess_slow_healing_start_date;
            $diabetes_outcome['assess_slow_healing_end_date'] = $assess_slow_healing_end_date;
            $diabetes_outcome['assess_slow_healing_status'] = $assess_slow_healing_status;
        }
        return $diabetes_outcome;
    }

    /**
     * COPD Careplan 
     * @param  Object $copd_assessment
     * @param  month  $filter month
     * @return \Illuminate\Http\Response
     */
    private function copdAssessment($copd_assessment, $filter_month, $careplanType)
    {
        $copd_outcomes = [];
        if (!empty($copd_assessment)) {
            
            if ($filter_month != "") {
                $copd_goals = $this->filterMonthlyAssessment($copd_assessment, $filter_month, $careplanType);
    
                if (is_array($copd_goals)) {
                    foreach ($copd_assessment as $key => $value) {
                        if ((strpos($key, "start_date") || strpos($key, "end_date")) && !array_key_exists($key, $copd_goals)) {
                            unset($copd_assessment[$key]);
                        }
                    }
                }
            }


            $cough = !empty($copd_assessment['cough']) ? $copd_assessment['cough'] : "";
            $phlegum_in_chest = !empty($copd_assessment['phlegum_in_chest']) ? $copd_assessment['phlegum_in_chest'] : "";
            $tight_chest = !empty($copd_assessment['tight_chest']) ? $copd_assessment['tight_chest'] : "";
            $breathless = !empty($copd_assessment['breathless']) ? $copd_assessment['breathless'] : "";
            $limited_activities = !empty($copd_assessment['limited_activities']) ? $copd_assessment['limited_activities'] : "";
            $lung_condition = !empty($copd_assessment['lung_condition']) ? $copd_assessment['lung_condition'] : "";
            $sound_sleep = !empty($copd_assessment['sound_sleep']) ? $copd_assessment['sound_sleep'] : "";
            $energy_level = !empty($copd_assessment['energy_level']) ? $copd_assessment['energy_level'] : "";

            /* GOAL 1 TASKS */
            $educate_on_disease_start_date = !empty($copd_assessment['educate_on_disease_start_date']) ? $copd_assessment['educate_on_disease_start_date'] : "";
            $educate_on_disease_end_date = !empty($copd_assessment['educate_on_disease_end_date']) ? $copd_assessment['educate_on_disease_end_date'] : "";
            $educate_on_disease_status = $this->calculateStatus($educate_on_disease_start_date, $educate_on_disease_end_date);

            /* Assign Goal Status as per Task status */
            $copd_outcomes['goal1_status'] = "";
            $goal1_task_status = [
                $educate_on_disease_status,
            ];
            $counts = array_count_values($goal1_task_status);

            if (@$counts['Completed'] === sizeof($goal1_task_status)) {
                $copd_outcomes['goal1_status'] = "Completed";
            } else if (@$counts['Completed'] > 0 && @$counts['Completed'] < sizeof($goal1_task_status)) {
                $copd_outcomes['goal1_status'] = "In progress";
            } else if (@$counts['Started'] > 0) {
                $copd_outcomes['goal1_status'] = "In progress";
            }
            /* GOAL 1 ends here */


            /* GOAL 2 TASKS */
            $smoking_cessation_start_date = !empty($copd_assessment['smoking_cessation_start_date']) ? $copd_assessment['smoking_cessation_start_date'] : "";
            $smoking_cessation_end_date = !empty($copd_assessment['smoking_cessation_end_date']) ? $copd_assessment['smoking_cessation_end_date'] : "";
            $smoking_cessation_status = $this->calculateStatus($smoking_cessation_start_date, $smoking_cessation_end_date);

            /* Assign Goal Status as per Task status */
            $copd_outcomes['goal2_status'] = "";
            $goal2_task_status = [
                $smoking_cessation_status,
            ];
            $counts = array_count_values($goal2_task_status);

            if (@$counts['Completed'] === sizeof($goal2_task_status)) {
                $copd_outcomes['goal2_status'] = "Completed";
            } else if (@$counts['Completed'] > 0 && @$counts['Completed'] < sizeof($goal2_task_status)) {
                $copd_outcomes['goal2_status'] = "In progress";
            } else if (@$counts['Started'] > 0) {
                $copd_outcomes['goal2_status'] = "In progress";
            }
            /* GOAL 2 ends here */


            /* GOAL 3 TASKS */
            $lowering_infection_risk_start_date = !empty($copd_assessment['lowering_infection_risk_start_date']) ? $copd_assessment['lowering_infection_risk_start_date'] : "";
            $lowering_infection_risk_end_date = !empty($copd_assessment['lowering_infection_risk_end_date']) ? $copd_assessment['lowering_infection_risk_end_date'] : "";
            $lowering_infection_risk_status = $this->calculateStatus($lowering_infection_risk_start_date, $lowering_infection_risk_end_date);

            /* Assign Goal Status as per Task status */
            $copd_outcomes['goal3_status'] = "";
            $goal3_task_status = [
                $lowering_infection_risk_status,
            ];
            $counts = array_count_values($goal3_task_status);

            if (@$counts['Completed'] === sizeof($goal3_task_status)) {
                $copd_outcomes['goal3_status'] = "Completed";
            } else if (@$counts['Completed'] > 0 && @$counts['Completed'] < sizeof($goal3_task_status)) {
                $copd_outcomes['goal3_status'] = "In progress";
            } else if (@$counts['Started'] > 0) {
                $copd_outcomes['goal3_status'] = "In progress";
            }
            /* GOAL 3 ends here */

            /* GOAL 4 TASKS */
            $educate_on_lifestyle_start_date = !empty($copd_assessment['educate_on_lifestyle_start_date']) ? $copd_assessment['educate_on_lifestyle_start_date'] : "";
            $educate_on_lifestyle_end_date = !empty($copd_assessment['educate_on_lifestyle_end_date']) ? $copd_assessment['educate_on_lifestyle_end_date'] : "";
            $educate_on_lifestyle_status = $this->calculateStatus($educate_on_lifestyle_start_date, $educate_on_lifestyle_end_date);

            /* Assign Goal Status as per Task status */
            $copd_outcomes['goal4_status'] = "";
            $goal4_task_status = [
                $educate_on_lifestyle_status,
            ];
            $counts = array_count_values($goal4_task_status);

            if (@$counts['Completed'] === sizeof($goal4_task_status)) {
                $copd_outcomes['goal4_status'] = "Completed";
            } else if (@$counts['Completed'] > 0 && @$counts['Completed'] < sizeof($goal4_task_status)) {
                $copd_outcomes['goal4_status'] = "In progress";
            } else if (@$counts['Started'] > 0) {
                $copd_outcomes['goal4_status'] = "In progress";
            }
            /* GOAL 4 ends here */


            /* GOALS 5 STARTS */
            $educate_on_emergency_start_date = !empty($copd_assessment['educate_on_emergency_start_date']) ? $copd_assessment['educate_on_emergency_start_date'] : "";
            $educate_on_emergency_end_date = !empty($copd_assessment['educate_on_emergency_end_date']) ? $copd_assessment['educate_on_emergency_end_date'] : "";
            $educate_on_emergency_status = $this->calculateStatus($educate_on_emergency_start_date, $educate_on_emergency_end_date);

            /* Assign Goal Status as per Task status */
            $copd_outcomes['goal5_status'] = "";
            $goal5_task_status = [
                $educate_on_emergency_status,
            ];
            $counts = array_count_values($goal5_task_status);

            if (@$counts['Completed'] === sizeof($goal5_task_status)) {
                $copd_outcomes['goal5_status'] = "Completed";
            } else if (@$counts['Completed'] > 0 && @$counts['Completed'] < sizeof($goal5_task_status)) {
                $copd_outcomes['goal5_status'] = "In progress";
            } else if (@$counts['Started'] > 0) {
                $copd_outcomes['goal5_status'] = "In progress";
            }
            /* GOAL 5 ends here */


            /* GOALS 6 STARTS */
            $having_copd_flare_start_date = !empty($copd_assessment['having_copd_flare_start_date']) ? $copd_assessment['having_copd_flare_start_date'] : "";
            $having_copd_flare_end_date = !empty($copd_assessment['having_copd_flare_end_date']) ? $copd_assessment['having_copd_flare_end_date'] : "";
            $having_copd_flare_status = $this->calculateStatus($having_copd_flare_start_date, $having_copd_flare_end_date);

            /* Assign Goal Status as per Task status */
            $copd_outcomes['goal6_status'] = "";
            $goal6_task_status = [
                $having_copd_flare_status,
            ];
            $counts = array_count_values($goal6_task_status);

            if (@$counts['Completed'] === sizeof($goal6_task_status)) {
                $copd_outcomes['goal6_status'] = "Completed";
            } else if (@$counts['Completed'] > 0 && @$counts['Completed'] < sizeof($goal6_task_status)) {
                $copd_outcomes['goal6_status'] = "In progress";
            } else if (@$counts['Started'] > 0) {
                $copd_outcomes['goal6_status'] = "In progress";
            }
            /* GOAL 6 ends here */


            /* GOALS 7 STARTS */
            $prevention_copd_flare_start_date = !empty($copd_assessment['prevention_copd_flare_start_date']) ? $copd_assessment['prevention_copd_flare_start_date'] : "";
            $prevention_copd_flare_end_date = !empty($copd_assessment['prevention_copd_flare_end_date']) ? $copd_assessment['prevention_copd_flare_end_date'] : "";
            $prevention_copd_flare_status = $this->calculateStatus($prevention_copd_flare_start_date, $prevention_copd_flare_end_date);

            /* Assign Goal Status as per Task status */
            $copd_outcomes['goal7_status'] = "";
            $goal7_task_status = [
                $prevention_copd_flare_status,
            ];
            $counts = array_count_values($goal7_task_status);

            if (@$counts['Completed'] === sizeof($goal7_task_status)) {
                $copd_outcomes['goal7_status'] = "Completed";
            } else if (@$counts['Completed'] > 0 && @$counts['Completed'] < sizeof($goal7_task_status)) {
                $copd_outcomes['goal7_status'] = "In progress";
            } else if (@$counts['Started'] > 0) {
                $copd_outcomes['goal7_status'] = "In progress";
            }
            /* GOAL 7 ends here */


            /* GOALS 8 STARTS */
            $followup_imp_start_date = !empty($copd_assessment['followup_imp_start_date']) ? $copd_assessment['followup_imp_start_date'] : "";
            $followup_imp_end_date = !empty($copd_assessment['followup_imp_end_date']) ? $copd_assessment['followup_imp_end_date'] : "";
            $followup_imp_status = $this->calculateStatus($followup_imp_start_date, $followup_imp_end_date);

            /* Assign Goal Status as per Task status */
            $copd_outcomes['goal8_status'] = "";
            $goal8_task_status = [
                $followup_imp_status,
            ];
            $counts = array_count_values($goal8_task_status);

            if (@$counts['Completed'] === sizeof($goal8_task_status)) {
                $copd_outcomes['goal8_status'] = "Completed";
            } else if (@$counts['Completed'] > 0 && @$counts['Completed'] < sizeof($goal8_task_status)) {
                $copd_outcomes['goal8_status'] = "In progress";
            } else if (@$counts['Started'] > 0) {
                $copd_outcomes['goal8_status'] = "In progress";
            }
            /* GOAL 8 ends here */

            $total_assessment_score = !empty($copd_assessment['total_assessment_score']) ? $copd_assessment['total_assessment_score'] : "";

            $copd_outcomes['careplan'] = 'Patient’s total score on the COPD Assessment Test (CAT™) was '.$total_assessment_score.' on date of assessment. ';

            if ($total_assessment_score < 10) {
                $copd_outcomes['prognosis'] = "Good";
                $copd_outcomes['assessment'] = "Patient advised to continue current treatment.";
            } elseif ($total_assessment_score >= 10 && $total_assessment_score <= 20) {
                $copd_outcomes['prognosis'] = "Fair";
                $copd_outcomes['assessment'] = "Patient advised to schedule a routine follow up with PCP or Pulmonologist for better COPD management.";
            } elseif ($total_assessment_score > 20) {
                $copd_outcomes['prognosis'] = "Guarded";
                $copd_outcomes['assessment'] = "Patient advised to schedule an appointment right away with Pulmonologist for adjustment of treatment.";
            }

            $copd_outcomes['educate_on_disease_start_date'] = $educate_on_disease_start_date;
            $copd_outcomes['educate_on_disease_end_date'] = $educate_on_disease_end_date;
            $copd_outcomes['educate_on_disease_status'] = $educate_on_disease_status;

            $copd_outcomes['smoking_cessation_start_date'] = $smoking_cessation_start_date;
            $copd_outcomes['smoking_cessation_end_date'] = $smoking_cessation_end_date;
            $copd_outcomes['smoking_cessation_status'] = $smoking_cessation_status;
            
            $copd_outcomes['lowering_infection_risk_start_date'] = $lowering_infection_risk_start_date;
            $copd_outcomes['lowering_infection_risk_end_date'] = $lowering_infection_risk_end_date;
            $copd_outcomes['lowering_infection_risk_status'] = $lowering_infection_risk_status;

            $copd_outcomes['educate_on_lifestyle_start_date'] = $educate_on_lifestyle_start_date;
            $copd_outcomes['educate_on_lifestyle_end_date'] = $educate_on_lifestyle_end_date;
            $copd_outcomes['educate_on_lifestyle_status'] = $educate_on_lifestyle_status;

            $copd_outcomes['educate_on_emergency_start_date'] = $educate_on_emergency_start_date;
            $copd_outcomes['educate_on_emergency_end_date'] = $educate_on_emergency_end_date;
            $copd_outcomes['educate_on_emergency_status'] = $educate_on_emergency_status;

            $copd_outcomes['having_copd_flare_start_date'] = $having_copd_flare_start_date;
            $copd_outcomes['having_copd_flare_end_date'] = $having_copd_flare_end_date;
            $copd_outcomes['having_copd_flare_status'] = $having_copd_flare_status;

            $copd_outcomes['prevention_copd_flare_start_date'] = $prevention_copd_flare_start_date;
            $copd_outcomes['prevention_copd_flare_end_date'] = $prevention_copd_flare_end_date;
            $copd_outcomes['prevention_copd_flare_status'] = $prevention_copd_flare_status;

            $copd_outcomes['followup_imp_start_date'] = $followup_imp_start_date;
            $copd_outcomes['followup_imp_end_date'] = $followup_imp_end_date;
            $copd_outcomes['followup_imp_status'] = $followup_imp_status;
        }
        return $copd_outcomes;
    }


    /**
     * CKD Careplan 
     * @param  Object $ckd_assessment
     * @param  month  $filter month
     * @return \Illuminate\Http\Response
     */
    private function ckdAssessment($ckd_assessment, $filter_month, $careplanType)
    {
        $ckd_outcomes = [];
        if (!empty($ckd_assessment)) {

            if ($filter_month != "") {
                $ckd_goals = $this->filterMonthlyAssessment($ckd_assessment, $filter_month, $careplanType);
    
                if (is_array($ckd_goals)) {
                    foreach ($ckd_assessment as $key => $value) {
                        if ((strpos($key, "start_date") || strpos($key, "end_date")) && !array_key_exists($key, $ckd_goals)) {
                            unset($ckd_assessment[$key]);
                        }
                    }
                }
            }

            // Prognosis and Assessment
            $egfr_result = @$ckd_assessment['egfr_result_one_report'] ?? "";
            $egfr_date = @$ckd_assessment['egfr_date'] ?? "";
            $see_nephrologist = @$ckd_assessment['nephrologist_question'] ?? "";
            $nephrologist_name = @$ckd_assessment['nephrologist_name'] ?? "";

            $nephrologist_statement = "";
            if ($see_nephrologist == 'Yes' && $nephrologist_name != "") {
                $nephrologist_statement = ' Patient see '.$nephrologist_name.'.';
            } elseif ($see_nephrologist == 'Yes' && $nephrologist_name == "") {
                $nephrologist_statement = ' Patient sees the Nephrologist.';
            } else {
                $nephrologist_statement = ' Patient advised to talk to the PCP to see a Nephrologist.';
            }

            $egfr_date = $egfr_date != "" ? ' on '.$egfr_date.'.' : ".";

            if ($egfr_result >= '45' && $egfr_result <= '59') {
                $ckd_outcomes['prognosis'] = 'Good';
                $ckd_outcomes['assessment'] = 'Patient eGFR is '.$egfr_result.$egfr_date.' Patient has CKD Stage 3A. Patient advised to control blood pressure, avoid over all NSAIDS (Aleve, Motrin, Advil etc). BMP needs to be checked every 3 months.';
            } elseif ($egfr_result >= '30' && $egfr_result <= '44') {
                $ckd_outcomes['prognosis'] = 'Fair';
                $ckd_outcomes['assessment'] = 'Patient eGFR is '.$egfr_result.$egfr_date.' Patient has CKD Stage 3A. Patient advised to control blood pressure, avoid over all NSAIDS (Aleve, Motrin, Advil etc). BMP needs to be checked every 3 months.';
            } elseif ($egfr_result >= '15' && $egfr_result <= '29') {
                $ckd_outcomes['prognosis'] = 'Guarded';
                $ckd_outcomes['assessment'] = 'Patient eGFR is '.$egfr_result.$egfr_date.' Patient has CKD Stage 3A. Patient advised to control blood pressure, avoid over all NSAIDS (Aleve, Motrin, Advil etc). BMP needs to be checked every 3 months.'.$nephrologist_statement;
            } elseif ($egfr_result < '15') {
                $ckd_outcomes['prognosis'] = 'Poor';
                $ckd_outcomes['assessment'] = 'Patient eGFR is '.$egfr_result.$egfr_date.' Patient has CKD Stage 5. Patient advised to control blood pressure, avoid over all NSAIDS (Aleve, Motrin, Advil etc). BMP needs to be checked every 3 months.'.$nephrologist_statement;
            }

            /* GOAL 1 TASKS */
            $educate_on_ckd_start_date = !empty($ckd_assessment['educate_on_ckd_start_date']) ? $ckd_assessment['educate_on_ckd_start_date'] : "";
            $educate_on_ckd_end_date = !empty($ckd_assessment['educate_on_ckd_end_date']) ? $ckd_assessment['educate_on_ckd_end_date'] : "";
            $educate_on_ckd_status = $this->calculateStatus($educate_on_ckd_start_date, $educate_on_ckd_end_date);

            $worsening_symptoms_start_date = !empty($ckd_assessment['worsening_symptoms_start_date']) ? $ckd_assessment['worsening_symptoms_start_date'] : "";
            $worsening_symptoms_end_date = !empty($ckd_assessment['worsening_symptoms_end_date']) ? $ckd_assessment['worsening_symptoms_end_date'] : "";
            $worsening_symptoms_status = $this->calculateStatus($worsening_symptoms_start_date, $worsening_symptoms_end_date);

            $followup_importance_start_date = !empty($ckd_assessment['followup_importance_start_date']) ? $ckd_assessment['followup_importance_start_date'] : "";
            $followup_importance_end_date = !empty($ckd_assessment['followup_importance_end_date']) ? $ckd_assessment['followup_importance_end_date'] : "";
            $followup_importance_status = $this->calculateStatus($followup_importance_start_date, $followup_importance_end_date);

            $prevent_worsening_start_date = !empty($ckd_assessment['prevent_worsening_start_date']) ? $ckd_assessment['prevent_worsening_start_date'] : "";
            $prevent_worsening_end_date = !empty($ckd_assessment['prevent_worsening_end_date']) ? $ckd_assessment['prevent_worsening_end_date'] : "";
            $prevent_worsening_status = $this->calculateStatus($prevent_worsening_start_date, $prevent_worsening_end_date);

            $aviod_medications_start_date = !empty($ckd_assessment['aviod_medications_start_date']) ? $ckd_assessment['aviod_medications_start_date'] : "";
            $aviod_medications_end_date = !empty($ckd_assessment['aviod_medications_end_date']) ? $ckd_assessment['aviod_medications_end_date'] : "";
            $aviod_medications_status = $this->calculateStatus($aviod_medications_start_date, $aviod_medications_end_date);

            $ckd_treatment_start_date = !empty($ckd_assessment['ckd_treatment_start_date']) ? $ckd_assessment['ckd_treatment_start_date'] : "";
            $ckd_treatment_end_date = !empty($ckd_assessment['ckd_treatment_end_date']) ? $ckd_assessment['ckd_treatment_end_date'] : "";
            $ckd_treatment_status = $this->calculateStatus($ckd_treatment_start_date, $ckd_treatment_end_date);

            /* Assign Goal Status as per Task status */
            $ckd_outcomes['goal1_status'] = "";
            $goal1_task_status = [
                $educate_on_ckd_status,
                $worsening_symptoms_status,
                $followup_importance_status,
                $prevent_worsening_status,
                $aviod_medications_status,
                $ckd_treatment_status
            ];
            $counts = array_count_values($goal1_task_status);

            if (@$counts['Completed'] === sizeof($goal1_task_status)) {
                $ckd_outcomes['goal1_status'] = "Completed";
            } else if (@$counts['Completed'] > 0 && @$counts['Completed'] < sizeof($goal1_task_status)) {
                $ckd_outcomes['goal1_status'] = "In progress";
            } else if (@$counts['Started'] > 0) {
                $ckd_outcomes['goal1_status'] = "In progress";
            }
            /* GOAL 1 ENDS HERE */

            /* GOAL 2 TASKS */
            $educate_on_risk_factors_start_date = !empty($ckd_assessment['educate_on_risk_factors_start_date']) ? $ckd_assessment['educate_on_risk_factors_start_date'] : "";
            $educate_on_risk_factors_end_date = !empty($ckd_assessment['educate_on_risk_factors_end_date']) ? $ckd_assessment['educate_on_risk_factors_end_date'] : "";
            $educate_on_risk_factors_status = $this->calculateStatus($educate_on_risk_factors_start_date, $educate_on_risk_factors_end_date);

            $educate_on_lowering_risk_start_date = !empty($ckd_assessment['educate_on_lowering_risk_start_date']) ? $ckd_assessment['educate_on_lowering_risk_start_date'] : "";
            $educate_on_lowering_risk_end_date = !empty($ckd_assessment['educate_on_lowering_risk_end_date']) ? $ckd_assessment['educate_on_lowering_risk_end_date'] : "";
            $educate_on_lowering_risk_status = $this->calculateStatus($educate_on_lowering_risk_start_date, $educate_on_lowering_risk_end_date);

            $hypertension_effects_risk_start_date = !empty($ckd_assessment['hypertension_effects_risk_start_date']) ? $ckd_assessment['hypertension_effects_risk_start_date'] : "";
            $hypertension_effects_risk_end_date = !empty($ckd_assessment['hypertension_effects_risk_end_date']) ? $ckd_assessment['hypertension_effects_risk_end_date'] : "";
            $hypertension_effects_risk_status = $this->calculateStatus($hypertension_effects_risk_start_date, $hypertension_effects_risk_end_date);

            $healthy_diet_start_date = !empty($ckd_assessment['healthy_diet_start_date']) ? $ckd_assessment['healthy_diet_start_date'] : "";
            $healthy_diet_end_date = !empty($ckd_assessment['healthy_diet_end_date']) ? $ckd_assessment['healthy_diet_end_date'] : "";
            $healthy_diet_status = $this->calculateStatus($healthy_diet_start_date, $healthy_diet_end_date);

            $protein_effects_start_date = !empty($ckd_assessment['protein_effects_start_date']) ? $ckd_assessment['protein_effects_start_date'] : "";
            $protein_effects_end_date = !empty($ckd_assessment['protein_effects_end_date']) ? $ckd_assessment['protein_effects_end_date'] : "";
            $protein_effects_status = $this->calculateStatus($protein_effects_start_date, $protein_effects_end_date);

            $elevated_cholesterol_start_date = !empty($ckd_assessment['elevated_cholesterol_start_date']) ? $ckd_assessment['elevated_cholesterol_start_date'] : "";
            $elevated_cholesterol_end_date = !empty($ckd_assessment['elevated_cholesterol_end_date']) ? $ckd_assessment['elevated_cholesterol_end_date'] : "";
            $elevated_cholesterol_status = $this->calculateStatus($elevated_cholesterol_start_date, $elevated_cholesterol_end_date);

            /* Assign Goal Status as per Task status */
            $ckd_outcomes['goal2_status'] = "";
            $goal2_task_status = [
                $educate_on_risk_factors_status,
                $educate_on_lowering_risk_status,
                $hypertension_effects_risk_status,
                $healthy_diet_status,
                $protein_effects_status,
                $elevated_cholesterol_status,
            ];
            $counts = array_count_values($goal2_task_status);

            if (@$counts['Completed'] === sizeof($goal2_task_status)) {
                $ckd_outcomes['goal2_status'] = "Completed";
            } else if (@$counts['Completed'] > 0 && @$counts['Completed'] < sizeof($goal2_task_status)) {
                $ckd_outcomes['goal2_status'] = "In progress";
            } else if (@$counts['Started'] > 0) {
                $ckd_outcomes['goal2_status'] = "In progress";
            }
            /* GOAL 2 ENDS HERE */

            /* GOAL 3 TASKS */
            $educate_on_dkd_start_date = !empty($ckd_assessment['educate_on_dkd_start_date']) ? $ckd_assessment['educate_on_dkd_start_date'] : "";
            $educate_on_dkd_end_date= !empty($ckd_assessment['educate_on_dkd_end_date']) ? $ckd_assessment['educate_on_dkd_end_date'] : "";
            $educate_on_dkd_status = $this->calculateStatus($educate_on_dkd_start_date, $educate_on_dkd_end_date);

            $dkd_symptoms_start_date = !empty($ckd_assessment['dkd_symptoms_start_date']) ? $ckd_assessment['dkd_symptoms_start_date'] : "";
            $dkd_symptoms_end_date = !empty($ckd_assessment['dkd_symptoms_end_date']) ? $ckd_assessment['dkd_symptoms_end_date'] : "";
            $dkd_symptoms_status = $this->calculateStatus($dkd_symptoms_start_date, $dkd_symptoms_end_date);

            $dkd_risk_factors_start_date = !empty($ckd_assessment['dkd_risk_factors_start_date']) ? $ckd_assessment['dkd_risk_factors_start_date'] : "";
            $dkd_risk_factors_end_date = !empty($ckd_assessment['dkd_risk_factors_end_date']) ? $ckd_assessment['dkd_risk_factors_end_date'] : "";
            $dkd_risk_factors_status = $this->calculateStatus($dkd_risk_factors_start_date, $dkd_risk_factors_end_date);

            $dkd_progression_start_date = !empty($ckd_assessment['dkd_progression_start_date']) ? $ckd_assessment['dkd_progression_start_date'] : "";
            $dkd_progression_end_date = !empty($ckd_assessment['dkd_progression_end_date']) ? $ckd_assessment['dkd_progression_end_date'] : "";
            $dkd_progression_status = $this->calculateStatus($dkd_progression_start_date, $dkd_progression_end_date);

            $healthy_lifestyle_effect_start_date = !empty($ckd_assessment['healthy_lifestyle_effect_start_date']) ? $ckd_assessment['healthy_lifestyle_effect_start_date'] : "";
            $healthy_lifestyle_effect_end_date = !empty($ckd_assessment['healthy_lifestyle_effect_end_date']) ? $ckd_assessment['healthy_lifestyle_effect_end_date'] : "";
            $healthy_lifestyle_effect_status = $this->calculateStatus($healthy_lifestyle_effect_start_date, $healthy_lifestyle_effect_end_date);

            $blood_sugar_control_start_date = !empty($ckd_assessment['blood_sugar_control_start_date']) ? $ckd_assessment['blood_sugar_control_start_date'] : "";
            $blood_sugar_control_end_date = !empty($ckd_assessment['blood_sugar_control_end_date']) ? $ckd_assessment['blood_sugar_control_end_date'] : "";
            $blood_sugar_control_status = $this->calculateStatus($blood_sugar_control_start_date, $blood_sugar_control_end_date);

            $hba1c_importance_start_date = !empty($ckd_assessment['hba1c_importance_start_date']) ? $ckd_assessment['hba1c_importance_start_date'] : "";
            $hba1c_importance_end_date = !empty($ckd_assessment['hba1c_importance_end_date']) ? $ckd_assessment['hba1c_importance_end_date'] : "";
            $hba1c_importance_status = $this->calculateStatus($hba1c_importance_start_date, $hba1c_importance_end_date);

            $control_blood_sugar_start_date = !empty($ckd_assessment['control_blood_sugar_start_date']) ? $ckd_assessment['control_blood_sugar_start_date'] : "";
            $control_blood_sugar_end_date = !empty($ckd_assessment['control_blood_sugar_end_date']) ? $ckd_assessment['control_blood_sugar_end_date'] : "";
            $control_blood_sugar_status = $this->calculateStatus($control_blood_sugar_start_date, $control_blood_sugar_end_date);

            $bp_effect_on_dkd_start_date = !empty($ckd_assessment['bp_effect_on_dkd_start_date']) ? $ckd_assessment['bp_effect_on_dkd_start_date'] : "";
            $bp_effect_on_dkd_end_date = !empty($ckd_assessment['bp_effect_on_dkd_end_date']) ? $ckd_assessment['bp_effect_on_dkd_end_date'] : "";
            $bp_effect_on_dkd_status = $this->calculateStatus($bp_effect_on_dkd_start_date, $bp_effect_on_dkd_end_date);

            $hypertension_treatment_start_date = !empty($ckd_assessment['hypertension_treatment_start_date']) ? $ckd_assessment['hypertension_treatment_start_date'] : "";
            $hypertension_treatment_end_date = !empty($ckd_assessment['hypertension_treatment_end_date']) ? $ckd_assessment['hypertension_treatment_end_date'] : "";
            $hypertension_treatment_status = $this->calculateStatus($hypertension_treatment_start_date, $hypertension_treatment_end_date);

            /* Assign Goal Status as per Task status */
            $ckd_outcomes['goal3_status'] = "";
            $goal3_task_status = [
                $educate_on_dkd_status,
                $dkd_symptoms_status,
                $dkd_risk_factors_status,
                $dkd_progression_status,
                $healthy_lifestyle_effect_status,
                $blood_sugar_control_status,
                $hba1c_importance_status,
                $control_blood_sugar_status,
                $bp_effect_on_dkd_status,
                $hypertension_treatment_status
            ];
            $counts = array_count_values($goal3_task_status);

            if (@$counts['Completed'] === sizeof($goal3_task_status)) {
                $ckd_outcomes['goal3_status'] = "Completed";
            } else if (@$counts['Completed'] > 0 && @$counts['Completed'] < sizeof($goal3_task_status)) {
                $ckd_outcomes['goal3_status'] = "In progress";
            } else if (@$counts['Started'] > 0) {
                $ckd_outcomes['goal3_status'] = "In progress";
            }
            /* GOAL 3 ENDS HERE */

            /* GOAL 4 TASKS */
            $ckd_heart_start_date = !empty($ckd_assessment['ckd_heart_start_date']) ? $ckd_assessment['ckd_heart_start_date'] : "";
            $ckd_heart_end_date = !empty($ckd_assessment['ckd_heart_end_date']) ? $ckd_assessment['ckd_heart_end_date'] : "";
            $ckd_heart_status = $this->calculateStatus($ckd_heart_start_date, $ckd_heart_end_date);

            /* Assign Goal Status as per Task status */
            $ckd_outcomes['goal4_status'] = "";
            $goal4_task_status = [
                $ckd_heart_status,
            ];
            $counts = array_count_values($goal4_task_status);

            if (@$counts['Completed'] === sizeof($goal4_task_status)) {
                $ckd_outcomes['goal4_status'] = "Completed";
            } else if (@$counts['Completed'] > 0 && @$counts['Completed'] < sizeof($goal4_task_status)) {
                $ckd_outcomes['goal4_status'] = "In progress";
            } else if (@$counts['Started'] > 0) {
                $ckd_outcomes['goal4_status'] = "In progress";
            }
            /* GOAL 4 ENDS HERE */

            // $egfr_date = !empty($ckd_assessment['egfr_date']) ? $ckd_assessment['egfr_date'] : "";
            // $egfr_result_one_report = !empty($ckd_assessment['egfr_result_one_report']) ? $ckd_assessment['egfr_result_one_report'] : "";
            // $egfr_result_two_start_date = !empty($ckd_assessment['egfr_result_two_start_date']) ? $ckd_assessment['egfr_result_two_start_date'] : "";
            // $egfr_result_two_report = !empty($ckd_assessment['egfr_result_two_report']) ? $ckd_assessment['egfr_result_two_report'] : "";



            // GOAL 1
            $ckd_outcomes['educate_on_ckd_start_date'] = $educate_on_ckd_start_date;
            $ckd_outcomes['educate_on_ckd_end_date'] = $educate_on_ckd_end_date;
            $ckd_outcomes['educate_on_ckd_status'] = $educate_on_ckd_status;

            $ckd_outcomes['worsening_symptoms_start_date'] = $worsening_symptoms_start_date;
            $ckd_outcomes['worsening_symptoms_end_date'] = $worsening_symptoms_end_date;
            $ckd_outcomes['worsening_symptoms_status'] = $worsening_symptoms_status;

            $ckd_outcomes['followup_importance_start_date'] = $followup_importance_start_date;
            $ckd_outcomes['followup_importance_end_date'] = $followup_importance_end_date;
            $ckd_outcomes['followup_importance_status'] = $followup_importance_status;

            $ckd_outcomes['prevent_worsening_start_date'] = $prevent_worsening_start_date;
            $ckd_outcomes['prevent_worsening_end_date'] = $prevent_worsening_end_date;
            $ckd_outcomes['prevent_worsening_status'] = $prevent_worsening_status;

            $ckd_outcomes['aviod_medications_start_date'] = $aviod_medications_start_date;
            $ckd_outcomes['aviod_medications_end_date'] = $aviod_medications_end_date;
            $ckd_outcomes['aviod_medications_status'] = $aviod_medications_status;

            $ckd_outcomes['ckd_treatment_start_date'] = $ckd_treatment_start_date;
            $ckd_outcomes['ckd_treatment_end_date'] = $ckd_treatment_end_date;
            $ckd_outcomes['ckd_treatment_status'] = $ckd_treatment_status;

            // GOAL 2
            $ckd_outcomes['educate_on_risk_factors_start_date'] = $educate_on_risk_factors_start_date;
            $ckd_outcomes['educate_on_risk_factors_end_date'] = $educate_on_risk_factors_end_date;
            $ckd_outcomes['educate_on_risk_factors_status'] = $educate_on_risk_factors_status;

            $ckd_outcomes['educate_on_lowering_risk_start_date'] = $educate_on_lowering_risk_start_date;
            $ckd_outcomes['educate_on_lowering_risk_end_date'] = $educate_on_lowering_risk_end_date;
            $ckd_outcomes['educate_on_lowering_risk_status'] = $educate_on_lowering_risk_status;

            $ckd_outcomes['hypertension_effects_risk_start_date'] = $hypertension_effects_risk_start_date;
            $ckd_outcomes['hypertension_effects_risk_end_date'] = $hypertension_effects_risk_end_date;
            $ckd_outcomes['hypertension_effects_risk_status'] = $hypertension_effects_risk_status;

            $ckd_outcomes['healthy_diet_start_date'] = $healthy_diet_start_date;
            $ckd_outcomes['healthy_diet_end_date'] = $healthy_diet_end_date;
            $ckd_outcomes['healthy_diet_status'] = $healthy_diet_status;

            $ckd_outcomes['protein_effects_start_date'] = $protein_effects_start_date;
            $ckd_outcomes['protein_effects_end_date'] = $protein_effects_end_date;
            $ckd_outcomes['protein_effects_status'] = $protein_effects_status;

            $ckd_outcomes['elevated_cholesterol_start_date'] = $elevated_cholesterol_start_date;
            $ckd_outcomes['elevated_cholesterol_end_date'] = $elevated_cholesterol_end_date;
            $ckd_outcomes['elevated_cholesterol_status'] = $elevated_cholesterol_status;

            // GOAL 3
            $ckd_outcomes['educate_on_dkd_start_date']= $educate_on_dkd_start_date;
            $ckd_outcomes['educate_on_dkd_end_date']= $educate_on_dkd_end_date;
            $ckd_outcomes['educate_on_dkd_status']= $educate_on_dkd_status;

            $ckd_outcomes['dkd_symptoms_start_date']= $dkd_symptoms_start_date;
            $ckd_outcomes['dkd_symptoms_end_date']= $dkd_symptoms_end_date;
            $ckd_outcomes['dkd_symptoms_status']= $dkd_symptoms_status;

            $ckd_outcomes['dkd_risk_factors_start_date']= $dkd_risk_factors_start_date;
            $ckd_outcomes['dkd_risk_factors_end_date']= $dkd_risk_factors_end_date;
            $ckd_outcomes['dkd_risk_factors_status']= $dkd_risk_factors_status;

            $ckd_outcomes['dkd_progression_start_date']= $dkd_progression_start_date;
            $ckd_outcomes['dkd_progression_end_date']= $dkd_progression_end_date;
            $ckd_outcomes['dkd_progression_status']= $dkd_progression_status;

            $ckd_outcomes['healthy_lifestyle_effect_start_date']= $healthy_lifestyle_effect_start_date;
            $ckd_outcomes['healthy_lifestyle_effect_end_date']= $healthy_lifestyle_effect_end_date;
            $ckd_outcomes['healthy_lifestyle_effect_status']= $healthy_lifestyle_effect_status;

            $ckd_outcomes['blood_sugar_control_start_date']= $blood_sugar_control_start_date;
            $ckd_outcomes['blood_sugar_control_end_date']= $blood_sugar_control_end_date;
            $ckd_outcomes['blood_sugar_control_status']= $blood_sugar_control_status;

            $ckd_outcomes['hba1c_importance_start_date']= $hba1c_importance_start_date;
            $ckd_outcomes['hba1c_importance_end_date']= $hba1c_importance_end_date;
            $ckd_outcomes['hba1c_importance_status']= $hba1c_importance_status;

            $ckd_outcomes['control_blood_sugar_start_date']= $control_blood_sugar_start_date;
            $ckd_outcomes['control_blood_sugar_end_date']= $control_blood_sugar_end_date;
            $ckd_outcomes['control_blood_sugar_status']= $control_blood_sugar_status;

            $ckd_outcomes['bp_effect_on_dkd_start_date']= $bp_effect_on_dkd_start_date;
            $ckd_outcomes['bp_effect_on_dkd_end_date']= $bp_effect_on_dkd_end_date;
            $ckd_outcomes['bp_effect_on_dkd_status']= $bp_effect_on_dkd_status;

            $ckd_outcomes['hypertension_treatment_start_date']= $hypertension_treatment_start_date;
            $ckd_outcomes['hypertension_treatment_end_date']= $hypertension_treatment_end_date;
            $ckd_outcomes['hypertension_treatment_status']= $hypertension_treatment_status;

            // GOAL 4
            $ckd_outcomes['ckd_heart_start_date'] = $ckd_heart_start_date;
            $ckd_outcomes['ckd_heart_end_date'] = $ckd_heart_end_date;
            $ckd_outcomes['ckd_heart_status'] = $ckd_heart_status;
        }

        return $ckd_outcomes;
    }
    
    
    /**
     * Hypertenstion Careplan 
     * @param  Object $hypertension
     * @param  month  $filter month
     * @return \Illuminate\Http\Response
     */
    private function hypertensionAssessment($hypertension, $filter_month, $careplanType)
    {
        $hypertension_outcomes = [];
        if (!empty($hypertension)) {

            if ($filter_month != "") {
                $hypertension_goals = $this->filterMonthlyAssessment($hypertension, $filter_month, $careplanType);
    
                if (is_array($hypertension_goals)) {
                    foreach ($hypertension as $key => $value) {
                        if ((strpos($key, "start_date") || strpos($key, "end_date")) && !array_key_exists($key, $hypertension_goals)) {
                            unset($hypertension[$key]);
                        }
                    }
                }
            }


            /* GOAL 1 Tasks */
            $understanding_regarding_disease_start_date = !empty($hypertension['understanding_regarding_disease_start_date']) ? $hypertension['understanding_regarding_disease_start_date'] : "";
            $understanding_regarding_disease_end_date = !empty($hypertension['understanding_regarding_disease_end_date']) ? $hypertension['understanding_regarding_disease_end_date'] : "";
            $understanding_regarding_disease_status = $this->calculateStatus($understanding_regarding_disease_start_date, $understanding_regarding_disease_end_date);

            /* Assign Goal Status as per Task status */
            $hypertension_outcomes['goal1_status'] = "";
            $goal1_task_status = [
                $understanding_regarding_disease_status,
            ];
            $counts = array_count_values($goal1_task_status);

            if (@$counts['Completed'] === sizeof($goal1_task_status)) {
                $hypertension_outcomes['goal1_status'] = "Completed";
            } else if (@$counts['Completed'] > 0 && @$counts['Completed'] < sizeof($goal1_task_status)) {
                $hypertension_outcomes['goal1_status'] = "In progress";
            } else if (@$counts['Started'] > 0) {
                $hypertension_outcomes['goal1_status'] = "In progress";
            }
            /* GOAL 1 ENDS HERE */

            /* GOAL 2 Task and status */
            $educate_about_dash_diet_start_date = !empty($hypertension['educate_about_dash_diet_start_date']) ? $hypertension['educate_about_dash_diet_start_date'] : "";
            $educate_about_dash_diet_end_date = !empty($hypertension['educate_about_dash_diet_end_date']) ? $hypertension['educate_about_dash_diet_end_date'] : "";
            $educate_about_dash_diet_status = $this->calculateStatus($educate_about_dash_diet_start_date, $educate_about_dash_diet_end_date);
            
            $educate_about_sodium_diet_start_date = !empty($hypertension['educate_about_sodium_diet_start_date']) ? $hypertension['educate_about_sodium_diet_start_date'] : "";
            $educate_about_sodium_diet_end_date = !empty($hypertension['educate_about_sodium_diet_end_date']) ? $hypertension['educate_about_sodium_diet_end_date'] : "";
            $educate_about_sodium_diet_status = $this->calculateStatus($educate_about_sodium_diet_start_date, $educate_about_sodium_diet_end_date);
            
            $educate_about_excercise_start_date = !empty($hypertension['educate_about_excercise_start_date']) ? $hypertension['educate_about_excercise_start_date'] : "";
            $educate_about_excercise_end_date = !empty($hypertension['educate_about_excercise_end_date']) ? $hypertension['educate_about_excercise_end_date'] : "";
            $educate_about_excercise_status = $this->calculateStatus($educate_about_excercise_start_date, $educate_about_excercise_end_date);
            
            $educate_about_alcoholeffects_start_date = !empty($hypertension['educate_about_alcoholeffects_start_date']) ? $hypertension['educate_about_alcoholeffects_start_date'] : "";
            $educate_about_alcoholeffects_end_date = !empty($hypertension['educate_about_alcoholeffects_end_date']) ? $hypertension['educate_about_alcoholeffects_end_date'] : "";
            $educate_about_alcoholeffects_status = $this->calculateStatus($educate_about_alcoholeffects_start_date, $educate_about_alcoholeffects_end_date);
            
            $educate_about_smokingeffects_start_date = !empty($hypertension['educate_about_smokingeffects_start_date']) ? $hypertension['educate_about_smokingeffects_start_date'] : "";
            $educate_about_smokingeffects_end_date = !empty($hypertension['educate_about_smokingeffects_end_date']) ? $hypertension['educate_about_smokingeffects_end_date'] : "";
            $educate_about_smokingeffects_status = $this->calculateStatus($educate_about_smokingeffects_start_date, $educate_about_smokingeffects_end_date);

            /* Assign Goal Status as per Task status */
            $hypertension_outcomes['goal2_status'] = "";
            $goal2_task_status = [
                $educate_about_dash_diet_status,
                $educate_about_sodium_diet_status,
                $educate_about_excercise_status,
                $educate_about_alcoholeffects_status,
                $educate_about_smokingeffects_status,
            ];
            $counts = array_count_values($goal2_task_status);

            if (@$counts['Completed'] === sizeof($goal2_task_status)) {
                $hypertension_outcomes['goal2_status'] = "Completed";
            } else if (@$counts['Completed'] > 0 && @$counts['Completed'] < sizeof($goal2_task_status)) {
                $hypertension_outcomes['goal2_status'] = "In progress";
            } else if (@$counts['Started'] > 0) {
                $hypertension_outcomes['goal2_status'] = "In progress";
            }
            /* Goal 2 Ends */

            /* GOAL 3 Tasks */
            $regular_bp_monitoring_start_date = !empty($hypertension['regular_bp_monitoring_start_date']) ? $hypertension['regular_bp_monitoring_start_date'] : "";
            $regular_bp_monitoring_end_date = !empty($hypertension['regular_bp_monitoring_end_date']) ? $hypertension['regular_bp_monitoring_end_date'] : "";
            $regular_bp_monitoring_status = $this->calculateStatus($regular_bp_monitoring_start_date, $regular_bp_monitoring_end_date);

            /* GOAL 3 status */
            $hypertension_outcomes['goal3_status'] = "";
            $goal3Status = [
                $regular_bp_monitoring_status,
            ];
            $counts = array_count_values($goal3Status);

            if (@$counts['Completed'] === sizeof($goal3Status)) {
                $hypertension_outcomes['goal3_status'] = "Completed";
            } else if (@$counts['Completed'] > 0 && @$counts['Completed'] < sizeof($goal3Status)) {
                $hypertension_outcomes['goal3_status'] = "In progress";
            } else if (@$counts['Started'] > 0) {
                $hypertension_outcomes['goal3_status'] = "In progress";
            }

            /* GOAL 3 Ends */

            /* GOALS 4 task */
            $regular_pcp_folloup_start_date = !empty($hypertension['regular_pcp_folloup_start_date']) ? $hypertension['regular_pcp_folloup_start_date'] : "";
            $regular_pcp_folloup_end_date = !empty($hypertension['regular_pcp_folloup_end_date']) ? $hypertension['regular_pcp_folloup_end_date'] : "";
            $regular_pcp_folloup_status = $this->calculateStatus($regular_pcp_folloup_start_date, $regular_pcp_folloup_end_date);

            /* GOAL 4 status */
            $hypertension_outcomes['goal4_status'] = "";
            $goal4Status = [
                $regular_pcp_folloup_status,
            ];
            $counts = array_count_values($goal4Status);

            if (@$counts['Completed'] === sizeof($goal4Status)) {
                $hypertension_outcomes['goal4_status'] = "Completed";
            } else if (@$counts['Completed'] > 0 && @$counts['Completed'] < sizeof($goal4Status)) {
                $hypertension_outcomes['goal4_status'] = "In progress";
            } else if (@$counts['Started'] > 0) {
                $hypertension_outcomes['goal4_status'] = "In progress";
            }
            /* GOAL 4 Ends */


            /* Goal 1 */
            $hypertension_outcomes['understanding_regarding_disease_start_date'] = $understanding_regarding_disease_start_date;
            $hypertension_outcomes['understanding_regarding_disease_end_date'] = $understanding_regarding_disease_end_date;
            $hypertension_outcomes['understanding_regarding_disease_status'] = $understanding_regarding_disease_status;
            /* Goal 1 Ends */
            
            /* Goal 2 */
            $hypertension_outcomes['educate_about_dash_diet_start_date'] = $educate_about_dash_diet_start_date;
            $hypertension_outcomes['educate_about_dash_diet_end_date'] = $educate_about_dash_diet_end_date;
            $hypertension_outcomes['educate_about_dash_diet_status'] = $educate_about_dash_diet_status;
            
            $hypertension_outcomes['educate_about_sodium_diet_start_date'] = $educate_about_sodium_diet_start_date;
            $hypertension_outcomes['educate_about_sodium_diet_end_date'] = $educate_about_sodium_diet_end_date;
            $hypertension_outcomes['educate_about_sodium_diet_status'] = $educate_about_sodium_diet_status;
            
            $hypertension_outcomes['educate_about_excercise_start_date'] = $educate_about_excercise_start_date;
            $hypertension_outcomes['educate_about_excercise_end_date'] = $educate_about_excercise_end_date;
            $hypertension_outcomes['educate_about_excercise_status'] = $educate_about_excercise_status;
            
            $hypertension_outcomes['educate_about_alcoholeffects_start_date'] = $educate_about_alcoholeffects_start_date;
            $hypertension_outcomes['educate_about_alcoholeffects_end_date'] = $educate_about_alcoholeffects_end_date;
            $hypertension_outcomes['educate_about_alcoholeffects_status'] = $educate_about_alcoholeffects_status;
            
            $hypertension_outcomes['educate_about_smokingeffects_start_date'] = $educate_about_smokingeffects_start_date;
            $hypertension_outcomes['educate_about_smokingeffects_end_date'] = $educate_about_smokingeffects_end_date;
            $hypertension_outcomes['educate_about_smokingeffects_status'] = $educate_about_smokingeffects_status;
            /* Goal 2 Ends */

            /* Goal 3 */
            $hypertension_outcomes['regular_bp_monitoring_start_date'] = $regular_bp_monitoring_start_date;
            $hypertension_outcomes['regular_bp_monitoring_end_date'] = $regular_bp_monitoring_end_date;
            $hypertension_outcomes['regular_bp_monitoring_status'] = $regular_bp_monitoring_status;
            /* Goal 3 Ends */
            
            /* Goal 4 */
            $hypertension_outcomes['regular_pcp_folloup_start_date'] = $regular_pcp_folloup_start_date;
            $hypertension_outcomes['regular_pcp_folloup_end_date'] = $regular_pcp_folloup_end_date;
            $hypertension_outcomes['regular_pcp_folloup_status'] = $regular_pcp_folloup_status;
            /* Goal $ Ends */

            if (!empty($hypertension['bp'])) {
                # code...
                for ($i = 0; $i <= sizeof($hypertension['bp'])-1; $i = $i + 1) {
                    $date = ($hypertension['bp'][$i])['bp_day'];
                    $systolic = ($hypertension['bp'][$i])['systolic_day'];
                    $diastolic = ($hypertension['bp'][$i])['diastolic_day'];
                    $hyper_result[] = "Patient bp is " . $systolic . "/" . $diastolic . " on " . $date;
                }
    
                $hypertension_outcomes['result'] = $hyper_result;
    
                for ($i = 0; $i <= sizeof($hypertension['bp'])-1; $i++) {
                    $hp_systolics[] = $hypertension['bp'][$i]['systolic_day'];
                    $hp_diastolics[] = $hypertension['bp'][$i]['diastolic_day'];
                }
    
                $hp_systolics_count = count($hp_systolics);
                $hp_diastolics_count = count($hp_diastolics);
    
                $hp_systolic_total = 0;
                foreach ($hp_systolics as $item) {
                    $hp_systolic_total += $item;
                }
    
                $hp_diastolic_total = 0;
                foreach ($hp_diastolics as $item) {
                    $hp_diastolic_total += $item;
                }
    
                $hp_systolic_final = $hp_systolic_total / $hp_systolics_count;
                $hp_diastolic_final = $hp_diastolic_total / $hp_diastolics_count;
    
                $array = [
                    "hp_systolic_final" => $hp_systolic_final,
                    "hp_diastolic_final" => $hp_diastolic_final,
                ];

                // return $array;
    
                if ($hp_systolic_final <= 140 && $hp_diastolic_final <= 90) {
                    $hypertension_outcomes['prognosis'] = "Good";
                    $hypertension_outcomes['assessment'] = "BP well controlled. Patient advised to continue monitoring BP twice a day for at least 1 week every month.";
                } else if (($hp_systolic_final >= 140 && $hp_systolic_final <= 149) && ($hp_diastolic_final >= 90 && $hp_diastolic_final <= 99)) {
                    $hypertension_outcomes['prognosis'] = "Fair";
                    $hypertension_outcomes['assessment'] = "Patient BP is ".$hp_systolic_final."/".$hp_diastolic_final.". BP is elevated. Patient advised to monitor BP twice a day and maintain BP log for 4 weeks. Advised to schedule appointment with PCP in 4 weeks.";
                } else if ($hp_systolic_final >= 150 && $hp_diastolic_final >= 100) {
                    $hypertension_outcomes['prognosis'] = "Guarded";
                    $hypertension_outcomes['assessment'] = "Patient BP is ".$hp_systolic_final."/".$hp_diastolic_final.". BP is high. Patient advised to monitor BP twice a day and maintain BP log for 2 weeks. Advised to schedule appointment with PCP in 2 weeks.";
                }

                // counting how many diastolic values are 120 or greater
                $hypertensive_count = array_filter($hp_diastolics, function($value) {
                    return $value >= 120;
                });

                $hypertensive_urgency = [];
                foreach ($hypertension as $key => $value) {
                    if (strpos($key, 'hypertensive_urgency') !== false && $value != "") {
                        $hypertensive_urgency[] = $value;
                    }
                }

                if ($hypertensive_count >= 2 && !empty($hypertensive_urgency)) {
                    $hypertension_outcomes['prognosis'] = "Guarded";
                    $hypertension_outcomes['assessment'] = "Patient advised to go to the ER.";
                }
            } elseif (@$hypertension['daily_bp_reading'] != "" || @$hypertension['daily_bp_reading'] == "No") {
                $hypertension_outcomes['prognosis'] = "Guarded";
                $hypertension_outcomes['assessment'] = "Patient is not checking BP. Patient advised to see PCP in 2 weeks.";
            }
        }

        return $hypertension_outcomes;
    }
    
    
    /**
     * Obesity Careplan 
     * @param  Object $obesity
     * @param  month  $filter month
     * @return \Illuminate\Http\Response
     */
    private function obesityAssessment($obesity, $filter_month, $careplanType)
    {
        $obesity_outcomes = [];
        if (!empty($obesity)) {

            /* PROGNOSIS & ASSESSMENT*/
            $bmi = !empty($obesity['bmi']) ? (float)$obesity['bmi'] : "";

            if ($bmi >= 25 && $bmi <= 29.9) {
                $obesity_outcomes['prognosis'] = "Good";
                $obesity_outcomes['assessment'] = "Patient currently in overweight range, patient is working on diet and exercise.";
            } elseif ($bmi >= 30 && $bmi <= 34.9) {
                $obesity_outcomes['prognosis'] = "Fair";
                $obesity_outcomes['assessment'] = "Patient currently in obese range, patient is working on diet and exercise.";
            } elseif ($bmi >= 35 && $bmi <= 39.9) {
                $obesity_outcomes['prognosis'] = "Guarded";
                $obesity_outcomes['assessment'] = "Patient currently in obesity class 2 range, patient is working on diet and exercise. Patient referred to Nutritionist for better diet management.";
            } elseif ($bmi >= 40) {
                $obesity_outcomes['prognosis'] = "Poor";
                $obesity_outcomes['assessment'] = "Patient currently in obesity class 3 or morbid obesity range of BMI, patient is working on diet and exercise. Patient referred to Nutritionist for better diet management.";
            }
            /* PROGNOSIS ENDS */


            if ($filter_month != "") {
                $obesity_goals = $this->filterMonthlyAssessment($obesity, $filter_month, $careplanType);
    
                if (is_array($obesity_goals)) {
                    foreach ($obesity as $key => $value) {
                        if ((strpos($key, "start_date") || strpos($key, "end_date")) && !array_key_exists($key, $obesity_goals)) {
                            unset($obesity[$key]);
                        }
                    }
                }
            }

            /* Goal 1 Task */
            $bmi_awareness_start_date = !empty($obesity['bmi_awareness_start_date']) ? $obesity['bmi_awareness_start_date'] : "";
            $bmi_awareness_end_date = !empty($obesity['bmi_awareness_end_date']) ? $obesity['bmi_awareness_end_date'] : "";
            $bmi_awareness_status = $this->calculateStatus($bmi_awareness_start_date, $bmi_awareness_end_date);
            
            $weight_effect_start_date = !empty($obesity['weight_effect_start_date']) ? $obesity['weight_effect_start_date'] : "";
            $weight_effect_end_date = !empty($obesity['weight_effect_end_date']) ? $obesity['weight_effect_end_date'] : "";
            $weight_effect_status = $this->calculateStatus($weight_effect_start_date, $weight_effect_end_date);
            
            $maintain_healthy_weight_start_date = !empty($obesity['maintain_healthy_weight_start_date']) ? $obesity['maintain_healthy_weight_start_date'] : "";
            $maintain_healthy_weight_end_date = !empty($obesity['maintain_healthy_weight_end_date']) ? $obesity['maintain_healthy_weight_end_date'] : "";
            $maintain_healthy_weight_status = $this->calculateStatus($maintain_healthy_weight_start_date, $maintain_healthy_weight_end_date);
            
            $advertised_diets_start_date = !empty($obesity['advertised_diets_start_date']) ? $obesity['advertised_diets_start_date'] : "";
            $advertised_diets_end_date = !empty($obesity['advertised_diets_end_date']) ? $obesity['advertised_diets_end_date'] : "";
            $advertised_diets_status = $this->calculateStatus($advertised_diets_start_date, $advertised_diets_end_date);
            
            $healthy_habits_start_date = !empty($obesity['healthy_habits_start_date']) ? $obesity['healthy_habits_start_date'] : "";
            $healthy_habits_end_date = !empty($obesity['healthy_habits_end_date']) ? $obesity['healthy_habits_end_date'] : "";
            $healthy_habits_status = $this->calculateStatus($healthy_habits_start_date, $healthy_habits_end_date);

            /* Assign Goal Status as per Task status */
            $obesity_outcomes['goal1_status'] = "";
            $goal1_task_status = [
                $bmi_awareness_status,
                $weight_effect_status,
                $maintain_healthy_weight_status,
                $advertised_diets_status,
                $healthy_habits_status,
            ];
            $counts = array_count_values($goal1_task_status);

            if (@$counts['Completed'] === sizeof($goal1_task_status)) {
                $obesity_outcomes['goal1_status'] = "Completed";
            } else if (@$counts['Completed'] > 0 && @$counts['Completed'] < sizeof($goal1_task_status)) {
                $obesity_outcomes['goal1_status'] = "In progress";
            } else if (@$counts['Started'] > 0) {
                $obesity_outcomes['goal1_status'] = "In progress";
            }
            /* Goal 1 End */



            /* GOAL 2 */
            $weight_loss_program_start_date = !empty($obesity['weight_loss_program_start_date']) ? $obesity['weight_loss_program_start_date'] : "";
            $weight_loss_program_end_date = !empty($obesity['weight_loss_program_end_date']) ? $obesity['weight_loss_program_end_date'] : "";
            $weight_loss_program_status = $this->calculateStatus($weight_loss_program_start_date, $weight_loss_program_end_date);

            $bmi_importance_start_date = !empty($obesity['bmi_importance_start_date']) ? $obesity['bmi_importance_start_date'] : "";
            $bmi_importance_end_date = !empty($obesity['bmi_importance_end_date']) ? $obesity['bmi_importance_end_date'] : "";
            $bmi_importance_status = $this->calculateStatus($bmi_importance_start_date, $bmi_importance_end_date);

            $waist_circumference_start_date = !empty($obesity['waist_circumference_start_date']) ? $obesity['waist_circumference_start_date'] : "";
            $waist_circumference_end_date = !empty($obesity['waist_circumference_end_date']) ? $obesity['waist_circumference_end_date'] : "";
            $waist_circumference_status = $this->calculateStatus($waist_circumference_start_date, $waist_circumference_end_date);

            $treatment_type_start_date = !empty($obesity['treatment_type_start_date']) ? $obesity['treatment_type_start_date'] : "";
            $treatment_type_end_date = !empty($obesity['treatment_type_end_date']) ? $obesity['treatment_type_end_date'] : "";
            $treatment_type_status = $this->calculateStatus($treatment_type_start_date, $treatment_type_end_date);

            $weight_loss_start_date = !empty($obesity['weight_loss_start_date']) ? $obesity['weight_loss_start_date'] : "";
            $weight_loss_end_date = !empty($obesity['weight_loss_end_date']) ? $obesity['weight_loss_end_date'] : "";
            $weight_loss_status = $this->calculateStatus($weight_loss_start_date, $weight_loss_end_date);

            $eating_triggers_start_date = !empty($obesity['eating_triggers_start_date']) ? $obesity['eating_triggers_start_date'] : "";
            $eating_triggers_end_date = !empty($obesity['eating_triggers_end_date']) ? $obesity['eating_triggers_end_date'] : "";
            $eating_triggers_status = $this->calculateStatus($eating_triggers_start_date, $eating_triggers_end_date);

            $healthy_unhealthy_start_date = !empty($obesity['healthy_unhealthy_start_date']) ? $obesity['healthy_unhealthy_start_date'] : "";
            $healthy_unhealthy_end_date = !empty($obesity['healthy_unhealthy_end_date']) ? $obesity['healthy_unhealthy_end_date'] : "";
            $healthy_unhealthy_status = $this->calculateStatus($healthy_unhealthy_start_date, $healthy_unhealthy_end_date);

            $weightloss_factors_start_date = !empty($obesity['weightloss_factors_start_date']) ? $obesity['weightloss_factors_start_date'] : "";
            $weightloss_factors_end_date = !empty($obesity['weightloss_factors_end_date']) ? $obesity['weightloss_factors_end_date'] : "";
            $weightloss_factors_status = $this->calculateStatus($weightloss_factors_start_date, $weightloss_factors_end_date);

            $calories_needed_start_date = !empty($obesity['calories_needed_start_date']) ? $obesity['calories_needed_start_date'] : "";
            $calories_needed_end_date = !empty($obesity['calories_needed_end_date']) ? $obesity['calories_needed_end_date'] : "";
            $calories_needed_status = $this->calculateStatus($calories_needed_start_date, $calories_needed_end_date);

            $calories_count_start_date = !empty($obesity['calories_count_start_date']) ? $obesity['calories_count_start_date'] : "";
            $calories_count_end_date = !empty($obesity['calories_count_end_date']) ? $obesity['calories_count_end_date'] : "";
            $calories_count_status = $this->calculateStatus($calories_count_start_date, $calories_count_end_date);

            $reduce_fat_start_date = !empty($obesity['reduce_fat_start_date']) ? $obesity['reduce_fat_start_date'] : "";
            $reduce_fat_end_date = !empty($obesity['reduce_fat_end_date']) ? $obesity['reduce_fat_end_date'] : "";
            $reduce_fat_status = $this->calculateStatus($reduce_fat_start_date, $reduce_fat_end_date);

            $reduce_carbs_start_date = !empty($obesity['reduce_carbs_start_date']) ? $obesity['reduce_carbs_start_date'] : "";
            $reduce_carbs_end_date = !empty($obesity['reduce_carbs_end_date']) ? $obesity['reduce_carbs_end_date'] : "";
            $reduce_carbs_status = $this->calculateStatus($reduce_carbs_start_date, $reduce_carbs_end_date);

            $mediterranean_diet_start_date = !empty($obesity['mediterranean_diet_start_date']) ? $obesity['mediterranean_diet_start_date'] : "";
            $mediterranean_diet_end_date = !empty($obesity['mediterranean_diet_end_date']) ? $obesity['mediterranean_diet_end_date'] : "";
            $mediterranean_diet_status = $this->calculateStatus($mediterranean_diet_start_date, $mediterranean_diet_end_date);

            /* Assign Goal Status as per Task status */
            $obesity_outcomes['goal2_status'] = "";
            $goal2_task_status = [
                $weight_loss_program_status,
                $bmi_importance_status,
                $waist_circumference_status,
                $treatment_type_status,
                $weight_loss_status,
                $eating_triggers_status,
                $healthy_unhealthy_status,
                $weightloss_factors_status,
                $calories_needed_status,
                $calories_count_status,
                $reduce_fat_status,
                $reduce_carbs_status,
                $mediterranean_diet_status,
            ];
            $counts = array_count_values($goal2_task_status);

            if (@$counts['Completed'] === sizeof($goal2_task_status)) {
                $obesity_outcomes['goal2_status'] = "Completed";
            } else if (@$counts['Completed'] > 0 && @$counts['Completed'] < sizeof($goal2_task_status)) {
                $obesity_outcomes['goal2_status'] = "In progress";
            } else if (@$counts['Started'] > 0) {
                $obesity_outcomes['goal2_status'] = "In progress";
            }
            /* Goal 2 End */

            /* GOAL 3 WITHOUT TASK */
            
            $weightloss_medication_start_date = !empty($obesity['weightloss_medication_start_date']) ? $obesity['weightloss_medication_start_date'] : "";
            $weightloss_medication_end_date = !empty($obesity['weightloss_medication_end_date']) ? $obesity['weightloss_medication_end_date'] : "";
            $weightloss_medication_status = $this->calculateStatus($weightloss_medication_start_date, $weightloss_medication_end_date);

            $dietary_supplements_start_date = !empty($obesity['dietary_supplements_start_date']) ? $obesity['dietary_supplements_start_date'] : "";
            $dietary_supplements_end_date = !empty($obesity['dietary_supplements_end_date']) ? $obesity['dietary_supplements_end_date'] : "";
            $dietary_supplements_status = $this->calculateStatus($dietary_supplements_start_date, $dietary_supplements_end_date);

            $weightloss_method_start_date = !empty($obesity['weightloss_method_start_date']) ? $obesity['weightloss_method_start_date'] : "";
            $weightloss_method_end_date = !empty($obesity['weightloss_method_end_date']) ? $obesity['weightloss_method_end_date'] : "";
            $weightloss_method_status = $this->calculateStatus($weightloss_method_start_date, $weightloss_method_end_date);

            $seeing_dietitian_start_date = !empty($obesity['seeing_dietitian_start_date']) ? $obesity['seeing_dietitian_start_date'] : "";
            $seeing_dietitian_end_date = !empty($obesity['seeing_dietitian_end_date']) ? $obesity['seeing_dietitian_end_date'] : "";
            $seeing_dietitian_status = $this->calculateStatus($seeing_dietitian_start_date, $seeing_dietitian_end_date);

            /* Assign Goal Status as per Task status */
            $obesity_outcomes['goal3_status'] = "";
            $goal3_task_status = [
                $weightloss_medication_status,
                $dietary_supplements_status,
                $weightloss_method_status,
                $seeing_dietitian_status,
            ];
            $counts = array_count_values($goal3_task_status);

            if (@$counts['Completed'] === sizeof($goal3_task_status)) {
                $obesity_outcomes['goal3_status'] = "Completed";
            } else if (@$counts['Completed'] > 0 && @$counts['Completed'] < sizeof($goal3_task_status)) {
                $obesity_outcomes['goal3_status'] = "In progress";
            } else if (@$counts['Started'] > 0) {
                $obesity_outcomes['goal3_status'] = "In progress";
            }
            /* GOAL 3 Ends */


            /* GOAL 1 */
            $obesity_outcomes['bmi_awareness_start_date'] = $bmi_awareness_start_date;
            $obesity_outcomes['bmi_awareness_end_date'] = $bmi_awareness_end_date;
            $obesity_outcomes['bmi_awareness_status'] = $bmi_awareness_status;

            $obesity_outcomes['weight_effect_start_date'] = $weight_effect_start_date;
            $obesity_outcomes['weight_effect_end_date'] = $weight_effect_end_date;
            $obesity_outcomes['weight_effect_status'] = $weight_effect_status;

            $obesity_outcomes['maintain_healthy_weight_start_date'] = $maintain_healthy_weight_start_date;
            $obesity_outcomes['maintain_healthy_weight_end_date'] = $maintain_healthy_weight_end_date;
            $obesity_outcomes['maintain_healthy_weight_status'] = $maintain_healthy_weight_status;

            $obesity_outcomes['advertised_diets_start_date'] = $advertised_diets_start_date;
            $obesity_outcomes['advertised_diets_end_date'] = $advertised_diets_end_date;
            $obesity_outcomes['advertised_diets_status'] = $advertised_diets_status;

            $obesity_outcomes['healthy_habits_start_date'] = $healthy_habits_start_date;
            $obesity_outcomes['healthy_habits_end_date'] = $healthy_habits_end_date;
            $obesity_outcomes['healthy_habits_status'] = $healthy_habits_status;
            /* Goal 1 Ends */

            /* Goal 2 */
            
            $obesity_outcomes['weight_loss_program_start_date'] = $weight_loss_program_start_date;
            $obesity_outcomes['weight_loss_program_end_date'] = $weight_loss_program_end_date;
            $obesity_outcomes['weight_loss_program_status'] = $weight_loss_program_status;
            
            $obesity_outcomes['bmi_importance_start_date'] = $bmi_importance_start_date;
            $obesity_outcomes['bmi_importance_end_date'] = $bmi_importance_end_date;
            $obesity_outcomes['bmi_importance_status'] = $bmi_importance_status;
            
            $obesity_outcomes['waist_circumference_start_date'] = $waist_circumference_start_date;
            $obesity_outcomes['waist_circumference_end_date'] = $waist_circumference_end_date;
            $obesity_outcomes['waist_circumference_status'] = $waist_circumference_status;
            
            $obesity_outcomes['treatment_type_start_date'] = $treatment_type_start_date;
            $obesity_outcomes['treatment_type_end_date'] = $treatment_type_end_date;
            $obesity_outcomes['treatment_type_status'] = $treatment_type_status;
            
            $obesity_outcomes['weight_loss_start_date'] = $weight_loss_start_date;
            $obesity_outcomes['weight_loss_end_date'] = $weight_loss_end_date;
            $obesity_outcomes['weight_loss_status'] = $weight_loss_status;
            
            $obesity_outcomes['eating_triggers_start_date'] = $eating_triggers_start_date;
            $obesity_outcomes['eating_triggers_end_date'] = $eating_triggers_end_date;
            $obesity_outcomes['eating_triggers_status'] = $eating_triggers_status;
            
            $obesity_outcomes['healthy_unhealthy_start_date'] = $healthy_unhealthy_start_date;
            $obesity_outcomes['healthy_unhealthy_end_date'] = $healthy_unhealthy_end_date;
            $obesity_outcomes['healthy_unhealthy_status'] = $healthy_unhealthy_status;
            
            $obesity_outcomes['weightloss_factors_start_date'] = $weightloss_factors_start_date;
            $obesity_outcomes['weightloss_factors_end_date'] = $weightloss_factors_end_date;
            $obesity_outcomes['weightloss_factors_status'] = $weightloss_factors_status;
            
            $obesity_outcomes['calories_needed_start_date'] = $calories_needed_start_date;
            $obesity_outcomes['calories_needed_end_date'] = $calories_needed_end_date;
            $obesity_outcomes['calories_needed_status'] = $calories_needed_status;
            
            $obesity_outcomes['calories_count_start_date'] = $calories_count_start_date;
            $obesity_outcomes['calories_count_end_date'] = $calories_count_end_date;
            $obesity_outcomes['calories_count_status'] = $calories_count_status;
            
            $obesity_outcomes['reduce_fat_start_date'] = $reduce_fat_start_date;
            $obesity_outcomes['reduce_fat_end_date'] = $reduce_fat_end_date;
            $obesity_outcomes['reduce_fat_status'] = $reduce_fat_status;
            
            $obesity_outcomes['reduce_carbs_start_date'] = $reduce_carbs_start_date;
            $obesity_outcomes['reduce_carbs_end_date'] = $reduce_carbs_end_date;
            $obesity_outcomes['reduce_carbs_status'] = $reduce_carbs_status;
            
            $obesity_outcomes['mediterranean_diet_start_date'] = $mediterranean_diet_start_date;
            $obesity_outcomes['mediterranean_diet_end_date'] = $mediterranean_diet_end_date;
            $obesity_outcomes['mediterranean_diet_status'] = $mediterranean_diet_status;
            /* Goal 2 Ends */

            /* Goal 3 */
            
            $obesity_outcomes['weightloss_medication_start_date'] = $weightloss_medication_start_date;
            $obesity_outcomes['weightloss_medication_end_date'] = $weightloss_medication_end_date;
            $obesity_outcomes['weightloss_medication_status'] = $weightloss_medication_status;

            $obesity_outcomes['dietary_supplements_start_date'] = $dietary_supplements_start_date;
            $obesity_outcomes['dietary_supplements_end_date'] = $dietary_supplements_end_date;
            $obesity_outcomes['dietary_supplements_status'] = $dietary_supplements_status;

            $obesity_outcomes['weightloss_method_start_date'] = $weightloss_method_start_date;
            $obesity_outcomes['weightloss_method_end_date'] = $weightloss_method_end_date;
            $obesity_outcomes['weightloss_method_status'] = $weightloss_method_status;

            $obesity_outcomes['seeing_dietitian_start_date'] = $seeing_dietitian_start_date;
            $obesity_outcomes['seeing_dietitian_end_date'] = $seeing_dietitian_end_date;
            $obesity_outcomes['seeing_dietitian_status'] = $seeing_dietitian_status;
            /* Goal 3 Ends */
        }

        return $obesity_outcomes;
    }
    
    
    /**
     * Congestive heart Failure Careplan 
     * @param  Object $chf
     * @param  month  $filter month
     * @return \Illuminate\Http\Response
     */
    private function chfAssessment($chf, $filter_month, $careplanType)
    {
        $chf_outcomes = [];
        if (!empty($chf)) {

            if ($filter_month != "") {
                $chf_goals = $this->filterMonthlyAssessment($chf, $filter_month, $careplanType);
    
                if (is_array($chf_goals)) {
                    foreach ($chf as $key => $value) {
                        if ((strpos($key, "start_date") || strpos($key, "end_date")) && !array_key_exists($key, $chf_goals)) {
                            unset($chf[$key]);
                        }
                    }
                }
            }

            /* GOAL 1 TASKS */
            $understanding_regarding_disease_start_date = !empty($chf['understanding_regarding_disease_start_date']) ? $chf['understanding_regarding_disease_start_date'] : "";
            $understanding_regarding_disease_end_date = !empty($chf['understanding_regarding_disease_end_date']) ? $chf['understanding_regarding_disease_end_date'] : "";
            $understanding_regarding_disease_status = $this->calculateStatus($understanding_regarding_disease_start_date, $understanding_regarding_disease_end_date);
            
            $monitor_blood_pressure_start_date = !empty($chf['monitor_blood_pressure_start_date']) ? $chf['monitor_blood_pressure_start_date'] : "";
            $monitor_blood_pressure_end_date = !empty($chf['monitor_blood_pressure_end_date']) ? $chf['monitor_blood_pressure_end_date'] : "";
            $monitor_blood_pressure_status = $this->calculateStatus($monitor_blood_pressure_start_date, $monitor_blood_pressure_end_date);
            
            $monitor_ECG_levels_start_date = !empty($chf['monitor_ECG_levels_start_date']) ? $chf['monitor_ECG_levels_start_date'] : "";
            $monitor_ECG_levels_end_date = !empty($chf['monitor_ECG_levels_end_date']) ? $chf['monitor_ECG_levels_end_date'] : "";
            $monitor_ECG_levels_status = $this->calculateStatus($monitor_ECG_levels_start_date, $monitor_ECG_levels_end_date);

            /* Assign Goal Status as per Task status */
            $chf_outcomes['goal1_status'] = "";
            $goal1_task_status = [
                $understanding_regarding_disease_status,
                $monitor_blood_pressure_status,
                $monitor_ECG_levels_status,
            ];
            $counts = array_count_values($goal1_task_status);

            if (@$counts['Completed'] === sizeof($goal1_task_status)) {
                $chf_outcomes['goal1_status'] = "Completed";
            } else if (@$counts['Completed'] > 0 && @$counts['Completed'] < sizeof($goal1_task_status)) {
                $chf_outcomes['goal1_status'] = "In progress";
            } else if (@$counts['Started'] > 0) {
                $chf_outcomes['goal1_status'] = "In progress";
            }
            /* GOAL 1 ENDS HERE */

            /* GOAL 2 TASKS */
            $adequate_cardiac_start_date = !empty($chf['adequate_cardiac_start_date']) ? $chf['adequate_cardiac_start_date'] : "";
            $adequate_cardiac_end_date = !empty($chf['adequate_cardiac_end_date']) ? $chf['adequate_cardiac_end_date'] : "";
            $adequate_cardiac_status = $this->calculateStatus($adequate_cardiac_start_date, $adequate_cardiac_end_date);
            
            $cerebral_hypoperfusion_start_date = !empty($chf['cerebral_hypoperfusion_start_date']) ? $chf['cerebral_hypoperfusion_start_date'] : "";
            $cerebral_hypoperfusion_end_date = !empty($chf['cerebral_hypoperfusion_end_date']) ? $chf['cerebral_hypoperfusion_end_date'] : "";
            $cerebral_hypoperfusion_status = $this->calculateStatus($cerebral_hypoperfusion_start_date, $cerebral_hypoperfusion_end_date);

            /* Assign Goal Status as per Task status */
            $chf_outcomes['goal2_status'] = "";
            $goal2_task_status = [
                $adequate_cardiac_status,
                $cerebral_hypoperfusion_status,
            ];
            $counts = array_count_values($goal2_task_status);

            if (@$counts['Completed'] === sizeof($goal2_task_status)) {
                $chf_outcomes['goal2_status'] = "Completed";
            } else if (@$counts['Completed'] > 0 && @$counts['Completed'] < sizeof($goal2_task_status)) {
                $chf_outcomes['goal2_status'] = "In progress";
            } else if (@$counts['Started'] > 0) {
                $chf_outcomes['goal2_status'] = "In progress";
            }
            /* GOAL 2 ENDS */

            /* GOAL 3 TASKS */
            $pulmonary_hygiene_start_date = !empty($chf['pulmonary_hygiene_start_date']) ? $chf['pulmonary_hygiene_start_date'] : "";
            $pulmonary_hygiene_end_date = !empty($chf['pulmonary_hygiene_end_date']) ? $chf['pulmonary_hygiene_end_date'] : "";
            $pulmonary_hygiene_status = $this->calculateStatus($pulmonary_hygiene_start_date, $pulmonary_hygiene_end_date);
            
            $respiratory_distress_start_date = !empty($chf['respiratory_distress_start_date']) ? $chf['respiratory_distress_start_date'] : "";
            $respiratory_distress_end_date = !empty($chf['respiratory_distress_end_date']) ? $chf['respiratory_distress_end_date'] : "";
            $respiratory_distress_status = $this->calculateStatus($respiratory_distress_start_date, $respiratory_distress_end_date);
            
            $monitor_ABG_levels_start_date = !empty($chf['monitor_ABG_levels_start_date']) ? $chf['monitor_ABG_levels_start_date'] : "";
            $monitor_ABG_levels_end_date = !empty($chf['monitor_ABG_levels_end_date']) ? $chf['monitor_ABG_levels_end_date'] : "";
            $monitor_ABG_levels_status = $this->calculateStatus($monitor_ABG_levels_start_date, $monitor_ABG_levels_end_date);

            /* Assign Goal Status as per Task status */
            $chf_outcomes['goal3_status'] = "";
            $goal3_task_status = [
                $pulmonary_hygiene_status,
                $respiratory_distress_status,
                $monitor_ABG_levels_status,
            ];
            $counts = array_count_values($goal3_task_status);

            if (@$counts['Completed'] === sizeof($goal3_task_status)) {
                $chf_outcomes['goal3_status'] = "Completed";
            } else if (@$counts['Completed'] > 0 && @$counts['Completed'] < sizeof($goal3_task_status)) {
                $chf_outcomes['goal3_status'] = "In progress";
            } else if (@$counts['Started'] > 0) {
                $chf_outcomes['goal3_status'] = "In progress";
            }
            /* GOAL 3 ENDS */

            /* GOAL 4 TASKS */
            $pulmonary_edemas_start_date = !empty($chf['pulmonary_edemas_start_date']) ? $chf['pulmonary_edemas_start_date'] : "";
            $pulmonary_edemas_end_date = !empty($chf['pulmonary_edemas_end_date']) ? $chf['pulmonary_edemas_end_date'] : "";
            $pulmonary_edemas_status = $this->calculateStatus($pulmonary_edemas_start_date, $pulmonary_edemas_end_date);
            
            $conditions_of_Arrhythmias_start_date = !empty($chf['conditions_of_Arrhythmias_start_date']) ? $chf['conditions_of_Arrhythmias_start_date'] : "";
            $conditions_of_Arrhythmias_end_date = !empty($chf['conditions_of_Arrhythmias_end_date']) ? $chf['conditions_of_Arrhythmias_end_date'] : "";
            $conditions_of_Arrhythmias_status = $this->calculateStatus($conditions_of_Arrhythmias_start_date, $conditions_of_Arrhythmias_end_date);
            
            $cardiologist_visit_start_date = !empty($chf['cardiologist_visit_start_date']) ? $chf['cardiologist_visit_start_date'] : "";
            $cardiologist_visit_end_date = !empty($chf['cardiologist_visit_end_date']) ? $chf['cardiologist_visit_end_date'] : "";
            $cardiologist_visit_status = $this->calculateStatus($cardiologist_visit_start_date, $cardiologist_visit_end_date);

            /* Assign Goal Status as per Task status */
            $chf_outcomes['goal4_status'] = "";
            $goal4_task_status = [
                $pulmonary_edemas_status,
                $conditions_of_Arrhythmias_status,
                $cardiologist_visit_status,
            ];
            $counts = array_count_values($goal4_task_status);

            if (@$counts['Completed'] === sizeof($goal4_task_status)) {
                $chf_outcomes['goal4_status'] = "Completed";
            } else if (@$counts['Completed'] > 0 && @$counts['Completed'] < sizeof($goal4_task_status)) {
                $chf_outcomes['goal4_status'] = "In progress";
            } else if (@$counts['Started'] > 0) {
                $chf_outcomes['goal4_status'] = "In progress";
            }
            /* GOAL 4 ENDS */

            /* GOAL 5 TASKS */
            $fluid_status_start_date = !empty($chf['fluid_status_start_date']) ? $chf['fluid_status_start_date'] : "";
            $fluid_status_end_date = !empty($chf['fluid_status_end_date']) ? $chf['fluid_status_end_date'] : "";
            $fluid_status_status = $this->calculateStatus($fluid_status_start_date, $fluid_status_end_date);
          
            /* Assign Goal Status as per Task status */
            $chf_outcomes['goal5_status'] = "";
            $goal5_task_status = [
                $fluid_status_status,
            ];
            $counts = array_count_values($goal5_task_status);

            if (@$counts['Completed'] === sizeof($goal5_task_status)) {
                $chf_outcomes['goal5_status'] = "Completed";
            } else if (@$counts['Completed'] > 0 && @$counts['Completed'] < sizeof($goal5_task_status)) {
                $chf_outcomes['goal5_status'] = "In progress";
            } else if (@$counts['Started'] > 0) {
                $chf_outcomes['goal5_status'] = "In progress";
            }
            /* GOAL 5 ENDS */

            /* GOAL 6 TASKS */
            $antiarrhythmias_start_date = !empty($chf['antiarrhythmias_start_date']) ? $chf['antiarrhythmias_start_date'] : "";
            $antiarrhythmias_end_date = !empty($chf['antiarrhythmias_end_date']) ? $chf['antiarrhythmias_end_date'] : "";
            $antiarrhythmias_status = $this->calculateStatus($antiarrhythmias_start_date, $antiarrhythmias_end_date);
          
            /* Assign Goal Status as per Task status */
            $chf_outcomes['goal6_status'] = "";
            $goal6_task_status = [
                $antiarrhythmias_status,
            ];
            $counts = array_count_values($goal6_task_status);

            if (@$counts['Completed'] === sizeof($goal6_task_status)) {
                $chf_outcomes['goal6_status'] = "Completed";
            } else if (@$counts['Completed'] > 0 && @$counts['Completed'] < sizeof($goal6_task_status)) {
                $chf_outcomes['goal6_status'] = "In progress";
            } else if (@$counts['Started'] > 0) {
                $chf_outcomes['goal6_status'] = "In progress";
            }
            /* GOAL 6 ENDS */

            /* GOALS 7 & 8 Without tasks */
            $followup_pcp_start_date = !empty($chf['followup_pcp_start_date']) ? $chf['followup_pcp_start_date'] : "";
            $followup_pcp_end_date = !empty($chf['followup_pcp_end_date']) ? $chf['followup_pcp_end_date'] : "";
            $followup_pcp_status = $this->calculateStatus($followup_pcp_start_date, $followup_pcp_end_date);

            $importance_medication_start_date = !empty($chf['importance_medication_start_date']) ? $chf['importance_medication_start_date'] : "";
            $importance_medication_end_date = !empty($chf['importance_medication_end_date']) ? $chf['importance_medication_end_date'] : "";
            $importance_medication_status = $this->calculateStatus($importance_medication_start_date, $importance_medication_end_date);
            /* GOALS 7 & 8 Ends */


            $follow_up_cardio = !empty($chf['follow_up_cardio']) ? $chf['follow_up_cardio'] : "";
            $echocardiogram = !empty($chf['echocardiogram']) ? $chf['echocardiogram'] : "";
            $freq_recom_cardio = !empty($chf['freq_recom_cardio']) ? $chf['freq_recom_cardio'] : "";
            $no_echocardiogram = !empty($chf['no_echocardiogram']) ? $chf['no_echocardiogram'] : "";


            if ($follow_up_cardio == "No" && $echocardiogram == "No") {
                if ($no_echocardiogram == "patient_refused") {
                    $chf_outcomes['no_echodiogram'] = "Patient did not receive an echocardiogram in the last 1 year. Patient refused to get echocardiogram at this time. Patient advised in detail on the possible complications of not following up regularly to evaluate heart function";
                } elseif ($no_echocardiogram == "patient_adviced") {
                    $chf_outcomes['no_echodiogram'] = "Patient did not receive an echocardiogram in the last 1 year and was advised on the importance of echocardiograms done every 1-2 years to evaluate heart function in patients with CHF. Patient agrees to get echocardiogram done.";
                }
                $chf_outcomes['careplan'] = "Patient is not following up per recommendation, advised to set up and appointment with Cardiologist. " . @$chf_outcomes['no_echodiogram'] ?? "";
                $chf_outcomes['prognosis'] = "Poor – Patient is not having regular follow up, and not getting regular echocardiograms";
            } elseif ($follow_up_cardio == "No" && $echocardiogram == "Yes") {
                $chf_outcomes['careplan'] = "Patient received an echocardiogram in the last 1 year. Patient advised on importance of echocardiograms done every 1-2 years to evaluate heart function in patients with CHF.";
                $chf_outcomes['careplan'] = "Patient is not following up per recommendation, advised to set up and appointment with Cardiologist";
                $chf_outcomes['prognosis'] = "Patient is not having regular follow up but following up for regular echocardiograms";
            } elseif ($follow_up_cardio == "Yes" && $echocardiogram == "No") {
                if ($no_echocardiogram == "patient_refused") {
                    $chf_outcomes['patient_refused'] = "Patient did not receive an echocardiogram in the last 1 year. Patient refused to get echocardiogram at this time. Patient advised in detail on the possible complications of not following up regularly to evaluate heart function";
                } elseif ($no_echocardiogram == "patient_adviced") {
                    $chf_outcomes['patient_refused'] = "Patient did not receive an echocardiogram in the last 1 year and was advised on the importance of echocardiograms done every 1-2 years to evaluate heart function in patients with CHF. Patient agrees to get echocardiogram done.";
                }
                $chf_outcomes['careplan'] = "Patient follows up with their cardiologist as recommended. " . @$chf_outcomes['patient_refused'] ?? "";
                $chf_outcomes['prognosis'] = "Fair – Patient is having regular follow up but not following up for regular echocardiograms.";
            } elseif ($follow_up_cardio == "Yes" && $echocardiogram == "Yes") {
                $chf_outcomes['careplan'] = "Patient received an echocardiogram in the last 1 year. Patient advised on importance of echocardiograms done every 1-2 years to evaluate heart function in patients with CHF.";
                $chf_outcomes['careplan'] = "Patient follows up with their cardiologist as recommended. ";
                $chf_outcomes['prognosis'] = "Good -- Patient is having regular follow up and echocardiograms and compliant to treatment";
            }


            /* GOAL 1 RETURN */
            $chf_outcomes['understanding_regarding_disease_start_date'] = $understanding_regarding_disease_start_date;
            $chf_outcomes['understanding_regarding_disease_end_date'] = $understanding_regarding_disease_end_date;
            $chf_outcomes['understanding_regarding_disease_status'] = $understanding_regarding_disease_status;
            
            $chf_outcomes['monitor_blood_pressure_start_date'] = $monitor_blood_pressure_start_date;
            $chf_outcomes['monitor_blood_pressure_end_date'] = $monitor_blood_pressure_end_date;
            $chf_outcomes['monitor_blood_pressure_status'] = $monitor_blood_pressure_status;
            
            $chf_outcomes['monitor_ECG_levels_start_date'] = $monitor_ECG_levels_start_date;
            $chf_outcomes['monitor_ECG_levels_end_date'] = $monitor_ECG_levels_end_date;
            $chf_outcomes['monitor_ECG_levels_status'] = $monitor_ECG_levels_status;
            /* GOAL 1 END */
            
            /* GOAL 2 RETURN */
            $chf_outcomes['adequate_cardiac_start_date'] = $adequate_cardiac_start_date;
            $chf_outcomes['adequate_cardiac_end_date'] = $adequate_cardiac_end_date;
            $chf_outcomes['adequate_cardiac_status'] = $adequate_cardiac_status;
            
            $chf_outcomes['cerebral_hypoperfusion_start_date'] = $cerebral_hypoperfusion_start_date;
            $chf_outcomes['cerebral_hypoperfusion_end_date'] = $cerebral_hypoperfusion_end_date;
            $chf_outcomes['cerebral_hypoperfusion_status'] = $cerebral_hypoperfusion_status;
            /* GOAL 2 END */
            
            /* GOAL 3 RETURN */
            $chf_outcomes['pulmonary_hygiene_start_date'] = $pulmonary_hygiene_start_date;
            $chf_outcomes['pulmonary_hygiene_end_date'] = $pulmonary_hygiene_end_date;
            $chf_outcomes['pulmonary_hygiene_status'] = $pulmonary_hygiene_status;
            
            $chf_outcomes['respiratory_distress_start_date'] = $respiratory_distress_start_date;
            $chf_outcomes['respiratory_distress_end_date'] = $respiratory_distress_end_date;
            $chf_outcomes['respiratory_distress_status'] = $respiratory_distress_status;
            
            $chf_outcomes['monitor_ABG_levels_start_date'] = $monitor_ABG_levels_start_date;
            $chf_outcomes['monitor_ABG_levels_end_date'] = $monitor_ABG_levels_end_date;
            $chf_outcomes['monitor_ABG_levels_status'] = $monitor_ABG_levels_status;
            /* GOAL 3 END */
            
            /* GOAL 4 RETURN */
            $chf_outcomes['pulmonary_edemas_start_date'] = $pulmonary_edemas_start_date;
            $chf_outcomes['pulmonary_edemas_end_date'] = $pulmonary_edemas_end_date;
            $chf_outcomes['pulmonary_edemas_status'] = $pulmonary_edemas_status;
            
            $chf_outcomes['conditions_of_Arrhythmias_start_date'] = $conditions_of_Arrhythmias_start_date;
            $chf_outcomes['conditions_of_Arrhythmias_end_date'] = $conditions_of_Arrhythmias_end_date;
            $chf_outcomes['conditions_of_Arrhythmias_status'] = $conditions_of_Arrhythmias_status;
            
            $chf_outcomes['cardiologist_visit_start_date'] = $cardiologist_visit_start_date;
            $chf_outcomes['cardiologist_visit_end_date'] = $cardiologist_visit_end_date;
            $chf_outcomes['cardiologist_visit_status'] = $cardiologist_visit_status;
            /* GOAL 4 END */
            
            /* GOAL 5 RETURN */
            $chf_outcomes['fluid_status_start_date'] = $fluid_status_start_date;
            $chf_outcomes['fluid_status_end_date'] = $fluid_status_end_date;
            $chf_outcomes['fluid_status_status'] = $fluid_status_status;
            /* GOAL 5 END */
            
            /* GOAL 6 RETURN */
            $chf_outcomes['antiarrhythmias_start_date'] = $antiarrhythmias_start_date;
            $chf_outcomes['antiarrhythmias_end_date'] = $antiarrhythmias_end_date;
            $chf_outcomes['antiarrhythmias_status'] = $antiarrhythmias_status;
            /* GOAL 6 END */

            /* GOAL 7 & 8 RETURN */
            $chf_outcomes['followup_pcp_start_date'] = $followup_pcp_start_date;
            $chf_outcomes['followup_pcp_end_date'] = $followup_pcp_end_date;
            $chf_outcomes['followup_pcp_status'] = $followup_pcp_status;

            $chf_outcomes['importance_medication_start_date'] = $importance_medication_start_date;
            $chf_outcomes['importance_medication_end_date'] = $importance_medication_end_date;
            $chf_outcomes['importance_medication_status'] = $importance_medication_status;
            /* GOAL 7 & 8 END */
        }

        return $chf_outcomes;
    }


    /**
     * Calculate goals and tasks status 
     * @param  datestring $startDate
     * @param  datestring  $endDate
     * @return String
     */
    private function calculateStatus($startDate, $endDate)
    {
        $status = "Not Started";
        if (!empty($startDate) && !empty($endDate)) {
            $status = "Completed";
        } elseif (!empty($startDate) && empty($endDate)) {
            $status = "Started";
        }
        return $status;
    }



    /* Filled Questionnaire */
    public function filledQuestionnaire(Request $request, $id)
    {
        try {
            
            $date_of_service = $request->has('date_of_service') ? $request->date_of_service : "";
            $monthly_assessment_id = $request->has('monthly_assessment_id') ? $request->monthly_assessment_id : "";
            $is_monthly = $request->has('is_monthly') ? $request->is_monthly : "";

            $row = Questionaires::with('patient:id,first_name,mid_name,last_name,gender,age,dob,doctor_id,coordinator_id', 'program:id,name,short_name')
            ->with('monthlyAssessment', function ($query) use ($monthly_assessment_id) {
                if (!empty($monthly_assessment_id)) {
                    $query->where('id', $monthly_assessment_id);
                }
            })
            ->where('id', $id)->first()->toArray();

            $dateofService = $row['date_of_service'];
            $nextYeardue = Carbon::create($dateofService)->addYear(1)->format('m/d/Y');
            $questions_answers = json_decode($row['questions_answers'], true);

            if ($is_monthly == 1 && isset($row['monthly_assessment'])) {
                $data = $row['monthly_assessment'];
                $questions_answers = json_decode($data['monthly_assessment'], true);
                $dateofService = $data['date_of_service'];
                $nextYeardue = Carbon::create($dateofService)->addMonth(1)->format('m/d/Y');
            }

            // return response()->json($questions_answers, 200);

            $patient = $row['patient'];
            $program = $row['program'];
            
            
            $serialno = $row['serial_no'];
    
            if (!empty($questions_answers['depression_phq9'])) {
                $depressionArray = Config::get('constants')['depression_phq_9'];
                
                foreach ($questions_answers['depression_phq9'] as $key => $value) {
                    $depressionValue = array_search($value, $depressionArray);

                    if ($key != "problem_difficulty" && $key != "comments" && (strpos($key, "_start_date") === false && strpos($key, "_end_date") === false)) {
                        $questions_answers['depression_phq9'][$key] = $depressionValue;
                    } else {
                        $questions_answers['depression_phq9'][$key] = $value;
                    }
                }
            }

            if (!empty($questions_answers['cognitive_assessment'])) {
                $cognitive_assessment = $questions_answers['cognitive_assessment'];
                $yearRecalled = $monthRecalled = $hourRecalled = $reverseCount = $reverseMonth = $addressRecalled = 0;

                if (!empty($cognitive_assessment['year_recalled'])) {
                    $yearRecalled = $cognitive_assessment['year_recalled'] == 'incorrect' ? 4 : 0;
                }

                if (!empty($cognitive_assessment['month_recalled'])) {
                    $monthRecalled = $cognitive_assessment['month_recalled'] == 'incorrect' ? 3 : 0;
                }

                if (!empty($cognitive_assessment['hour_recalled'])) {
                    $hourRecalled = $cognitive_assessment['hour_recalled'] == 'incorrect' ? 3 : 0;
                }

                if (!empty($cognitive_assessment['reverse_count'])) {
                    $reverseCount = ($cognitive_assessment['reverse_count']) == '1 error' ? 2 : (($cognitive_assessment['reverse_count'] == 'more than 1 error') ? 4 : 0);
                }

                if (!empty($cognitive_assessment['reverse_month'])) {
                    $reverseMonth = strtolower(($cognitive_assessment['reverse_month'])) == '1 error' ? 2 : (($cognitive_assessment['reverse_month'] == 'more than 1 error') ? 4 : 0);
                }

                if (!empty($cognitive_assessment['address_recalled'])) {
                    $errorArray = Config::get('constants')['error_options_c'];
                    $address_recalled_value = $cognitive_assessment['address_recalled'];
                    foreach ($errorArray as $key => $value) {

                        /* concatinating "s" in the end to fix the issue with score  */
                        if ($address_recalled_value == $value || $address_recalled_value.'s' == $value) {
                            $addressRecalled = (int)$key;
                        }
                    }
                }

                /* Calcluating scores */
                $cogScore = $yearRecalled + $monthRecalled + $hourRecalled + $reverseCount + $reverseMonth + $addressRecalled;
                $questions_answers['cognitive_assessment']['score'] = $cogScore;
            }

            // if ($is_monthly != 1) {
            // }

            $pcp_name = User::where('id', $patient['doctor_id'])->first();
            $coordinator_name = User::where('id', $patient['coordinator_id'])->first();

            $patient['pcp_name'] = @$pcp_name['name'] ?? "";
            $patient['coordinator_name'] = @$coordinator_name['name'] ?? "";
    
            $data = [
                'page_title' => 'Patient Survey Report' ?? [],
                'patient' => $patient ?? [],
                'program' => $program ?? [],
                'questionaire' => $questions_answers ?? [],
                'serial_no' => $serialno ?? '',
                'date_of_service' => $dateofService,
                'next_due' => $nextYeardue,
                'created_at' => $row['created_at']
            ];

            $response = array('success'=>true, 'data'=>$data);
        } catch (\Exception $e) {
            
            $response = array('success'=>false,'message'=>$e->getMessage());
        }

        return response()->json($response);
    }


    /* Calculate difference in MONTHS */
    private function diffinMonths($monthYear, $add)
    {
        $now = Carbon::now();
        $date = Carbon::createFromDate($monthYear[1], $monthYear[0])->startOfMonth();
        $diffinMonths = Carbon::parse($date)->diffInMonths($now);
        return $diffinMonths;
    }

    /* Calculate difference in YEARS */
    private function diffinYears($monthYear)
    {
        $now = Carbon::now();
        $date = Carbon::createFromDate($monthYear[1], $monthYear[0])->startOfMonth();
        $diffinMonths = Carbon::parse($date)->diffInYears($now);
        return $diffinMonths;
    }

    /**
     * Download Careplan Pdf
     * 
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function downloadCareplanpdf(Request $request,$id)
    {
        ini_set('max_execution_time', 120);
        $data = $this->awvCareplanReport($id);

        $pdf = PDF::loadView($this->view.'analytics-pdf-report',$data);
        $headers = array(
            'Content-Type: application/pdf',
        );

        return $pdf->download('awv-care-plan.pdf', $headers);
    }


    /**
     * Download CCM Careplan Pdf
     * 
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function downloadCCMCarePlan(Request $request, $id)
    {
        ini_set('max_execution_time', 120);
        $data = $this->ccmCareplanReport($request, $id, "1");

        $pdf = PDF::loadView($this->view.'ccm-careplan-pdf-report',$data);
        $headers = array(
            'Content-Type: application/pdf',
        );

        return $pdf->download('ccm-care-plan.pdf', $headers);
    }


    /**
     * Download CCM Monthly Careplan Pdf
     * 
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function downloadMonthlyAssessment(Request $request, $id)
    {
        ini_set('max_execution_time', 120);
        $data = $this->ccmCareplanReport($request, $id, "1");

        $pdf = PDF::loadView($this->view.'ccm-monthly-care-plan', $data);

        $headers = array(
            'Content-Type: application/pdf',
        );

        return $pdf->download('ccm-monthly-careplan.pdf', $headers);
    }


    /** 
     * Save Signature of Doctor Only against a careplan
     * @param int $id
     * @return \Illuminate\Http\Response
     * */
    public function saveSignature(Request $request, $id)
    {
        $doctor_id = $request->doctor_id;

        try {
            // Set the timezone to Arizona
            $current_Date = Carbon::now()->setTimezone('America/Phoenix')->toDateTimeString();
            
            $data = [
                'doctor_id' => $doctor_id,
                'status' => 'Signed',
                'signed_date' => $current_Date,
            ];
    
            Questionaires::where('id',$id)->update($data);
            
            $response = array('success'=>true,'message'=>'Signed Successfully');
        } catch (\Exception $e) {
            $response = array('success'=>false,'message'=>$e->getMessage());
        }
        return response()->json($response);
    }


    public function checkCareplanHtml(Request $request, $id="")
    {
        // $input = $request->all();
        // return $input;
        /* $data = $this->awvCareplanReport($id);
        $codes = (new SuperBillCodesController)->index($request, $id)->getData();
        $superBillData = [];
        
        foreach ($codes->data as $key => $value) {
            if ($key == "codes") {
                $superBillData[$key] = json_decode(json_encode($value), true);
            } else {
                $superBillData[$key] = $value;
            }
        }
        $data['superBilldata'] = $superBillData; */
        $data = $this->ccmCareplanReport($request, $id, "1");

        // dd($data);
        return view($this->view.'ccm-monthly-care-plan', $data);
    }


    public function filterMonthlyAssessment($screenData, $filter_month, $careplanType, $debug=null)
    {
        try {
            if (!empty($screenData)) {
                $fields = [];
                $currentMonthGoals = [];

                foreach ($screenData as $field => $value) {
                    if (strpos($field, "_start_date")) {
                        $valueMonth = Carbon::parse($value)->format('m');

                        if ($valueMonth == $filter_month) {
                            $currentMonthGoals[$field] = $value;
                        } elseif ($valueMonth < $filter_month && $careplanType == "monthly") {
                            /* Tp show the goals which are started in the previous month but not yet completed */
                            $substring = "_start_date";
                            $position = strpos($field, $substring);
                            $result = substr($field, 0, $position);
                            $field_name = $result.'_end_date';

                            if (@$screenData[$field_name] == "") {
                                $currentMonthGoals[$field] = $value;
                            }
                        }
                    } elseif (strpos($field, "_end_date")) {
                        $valueMonth = Carbon::parse($value)->format('m');
                        if ($valueMonth == $filter_month) {
                            $currentMonthGoals[$field] = $value;
                            
                            /* Saving Start date of the goal/task if the start date of previous months and it ends in current month  */
                            $substring = "_end_date";
                            $position = strpos($field, $substring);
                            $result = substr($field, 0, $position);
                            $field_name = $result.'_start_date';
                            $start_date_value = Carbon::parse($screenData[$field_name])->format('m');
    
                            
                            if (!in_array($field_name, $currentMonthGoals) && $start_date_value <= $filter_month) {
                                $currentMonthGoals[$field_name] = $screenData[$field_name];
                            }
                        }

                    }
                }
            }
            return $currentMonthGoals;
        } catch (\Exception $e) {
            return $e->getMessage().' '.$e->getLine();
        }
    }
}
