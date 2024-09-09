<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Questionaires;
use App\Models\Patients;
use App\Models\Doctors;
use App\Models\Diagnosis;
use App\Models\Programs;
use App\Models\User;
use Validator,Session,Config;
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
        $row = Questionaires::with('patient:id,first_name,mid_name,last_name,gender,age,dob', 'program:id,name,short_name')->where('id', $id)->first()->toArray();
       // return response()->json($row);
        $questions_answers = json_decode($row['questions_answers'], true);

        $doctor = User::where(['role' => 2, 'id' => $row['doctor_id']])->select('first_name', 'mid_name', 'last_name')->first();

        if (!empty($doctor)) {
            $row['doctor'] = @$doctor['first_name'] . ' ' . @$doctor['mid_name'] . ' ' . @$doctor['last_name'];
        }

        $primary_care_physician = Patients::with('doctor:id,first_name,last_name')->where('id', $row['patient_id'])->first()->toArray();
        if (!empty($primary_care_physician['doctor'])) {
            $row['primary_care_physician'] = $primary_care_physician['doctor']['name'];
        }

        // PHYSICAL HEALTH - FALL SCREENING
        $fallScreeningOutcomes = $this->fallscreening($questions_answers['fall_screening']?? []);

        // DEPRESSION PHQ-9 Outcome
        $depression_OutComes = $this->depressionphq_9($questions_answers['depression_phq9'] ?? []);
        
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
        $seatBelt['outcome'] = (@$questions_answers['seatbelt_use']['wear_seal_belt'] == "Yes") ? 'Patient always uses seatbelt in the car.' : 'Patient counseled on the use of seat belt in the car.';
        $seatBelt['flag'] = (@$questions_answers['seatbelt_use']['wear_seal_belt'] == "No") ? true : false;

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
        if (!empty($questions_answers['misc'])) {
            $miscellaneous = $questions_answers['misc'];
        } else if (!empty($questions_answers['miscellaneous'])) {
            $miscellaneous = $questions_answers['miscellaneous'];
        }

        $data = [
            'page_title' => 'AWV Care plan',
            'row' => $row,
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


    /* Fall screening Careplan */
    private function fallscreening($fallscreening)
    {
        $fallScreeningOutcomes = [];
        if (!empty($fallscreening)) {

            $fallinpastYear = !empty($fall_screening['fall_in_one_year']) ? $fall_screening['fall_in_one_year'] : "";
            $noOfFall = !empty($fall_screening['number_of_falls']) ? $fall_screening['number_of_falls'] : 0;
            $fallInjury = !empty($fall_screening['injury']) ? $fall_screening['injury'] : '';
            $physicalTherapy = !empty($fall_screening['physical_therapy']) ? $fall_screening['physical_therapy'] : '';
            $blackingOut = !empty($fall_screening['blackingout_from_bed']) ? $fall_screening['blackingout_from_bed'] : '';
            $assistanceDevice = !empty($fall_screening['assistance_device']) ? $fall_screening['assistance_device'] : '';
            $unsteady = !empty($fall_screening['unsteady_todo_things']) ? $fall_screening['unsteady_todo_things'] : '';

            /* Outcome  */
            $blacking_out = "";
            if ($blackingOut == "Yes" && $unsteady == "Yes") {
                $blacking_out = 'Patient feels blacking out and is unsteady with ambulation';
            } else if ($blackingOut == "Yes" && $unsteady == "No") {
                $blacking_out = 'Patient feels blacking out';
            } else if ($blackingOut == "No" && $unsteady == "Yes") {
                $blacking_out = 'Patient is unsteady with ambulation';
            }

            $use_assistance_device = "";
            if ($blacking_out != "" && $assistanceDevice != "" && $assistanceDevice != 'None') {
                $use_assistance_device = ', will continue to use ' . $assistanceDevice . ' for mobilization. ';
            } else if ($blacking_out == "" && $assistanceDevice != "" && $assistanceDevice != 'None') {
                $use_assistance_device = 'Patient is using ' . $assistanceDevice . ' for mobilization. ';
            } else if ($assistanceDevice != "" && $assistanceDevice == 'None') {
                $use_assistance_device = '. Patient is not using any assistive device';
            }


            $physical_therapy_refferal = "";
            if ($physicalTherapy != "") {
                if ($physicalTherapy == 'Referred') {
                    $physical_therapy_refferal = "Physical therapy referral for muscle strengthening, gain training & balance, and home safety checklist provided.";
                } else if ($physicalTherapy == 'Already receiving') {
                    $physical_therapy_refferal = "Already receiving physical therapy.";
                } else {
                    $physical_therapy_refferal = "Patient refused Physical therapy.";
                }
            }


            if ($fallinpastYear == 'Yes') {
                $number_of_fall = $noOfFall != 0 ? $noOfFall . ' fall in the last 1 year' : '';
                $fall_with_injury = $fallInjury != "" ? ', with injury. ' : 'with no injury. ';
                $fallScreeningOutcomes['outcome'] = $number_of_fall . $fall_with_injury . $blacking_out . $use_assistance_device . $physical_therapy_refferal;
            } else {
                $fallScreeningOutcomes['outcome'] = 'No fall in last 1 year. ' . $blacking_out . $use_assistance_device . $physical_therapy_refferal;
            }
        }

        return $fallScreeningOutcomes;
    }


    /* Depression Careplan */
    private function depressionphq_9($depression)
    {
        $depression_OutComes = [];
        if (!empty($depression)) {
            $depression_score = array_sum($depression);

            if ($depression_score == 0) {
                $depression_OutComes['severity'] = "Patient is not depressed";
            } elseif ($depression_score > 0 && $depression_score <= 4) {
                $depression_OutComes['severity'] = "Minimal depression";
            } elseif ($depression_score > 4 && $depression_score <= 9) {
                $depression_OutComes['severity'] = "Mild depression";
            } elseif ($depression_score > 9 && $depression_score <= 14) {
                $depression_OutComes['severity'] = "Moderate depression";
            } elseif ($depression_score > 14 && $depression_score <= 20) {
                $depression_OutComes['severity'] = "Moderately Severe depression";
            } elseif ($depression_score > 20 && $depression_score <= 27) {
                $depression_OutComes['severity'] = "Severe depression";
            }

            if ($depression_score > 9) {
                $depression_OutComes['referrals'] = "Referred to Mental Health Professional.";
                $depression_OutComes['referrals1'] = "Referred to BHI program.";
                $depression_OutComes['flag'] = true;
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

            $careplan_outcome['social_empational_support'] = $social_emotional_support;
        }

        /* For Pain Screening */
        if (!empty($pain)) {
            $painLevel = (!empty($pain)) ? $pain['pain_felt'] : '';
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
                $reverseMonth = ($cognitive_assessment['reverse_month']) == '1 error' ? 2 : (($cognitive_assessment['reverse_month'] == 'more than 1 error') ? 4 : 0);
            }

            if (!empty($cognitive_assessment['address_recalled'])) {
                $errorArray = Config::get('constants')['error_options_c'];
                foreach ($errorArray as $key => $value) {
                    if ($cognitive_assessment['address_recalled'] == $value) {
                        $addressRecalled = (int)$key;
                    }
                }
            }

            $cogScore = $yearRecalled + $monthRecalled + $hourRecalled + $reverseCount + $reverseMonth + $addressRecalled;

            if ($cogScore <= 7) {
                $cognitiveOutcomes['outcome'] = 'Referral not necessary at present.';
            } elseif ($cogScore >= 8 && $cogScore <= 9) {
                $cognitiveOutcomes['outcome'] = 'Probably refer.';
            } elseif ($cogScore >= 10 && $cogScore <= 28) {
                $cognitiveOutcomes['outcome'] = 'Referral provided';
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
                    $physicalActivitiesOutComes['flag'] = true;
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
    
            $heavyDrinks = $bingDrinks = 0;
            $gender = $row['patient']['gender'];
            if ($gender == "Male") {
                $heavyDrinks = $drinksPerWeek > 15 ? true : false;
                $bingDrinks = $drinksPerCccasion > 5 ? true : false;
            } else {
                $heavyDrinks = $drinksPerWeek > 8 ? true : false;
                $bingDrinks = $drinksPerCccasion > 4 ? true : false;
            }
    
            if ($heavyDrinks && $bingDrinks) {
                $alcoholOutComes['outcome'] = "Patient is a heavy and a binge drinker. Counseled and dietary guidelines for alcohol provided.";
            } elseif ($heavyDrinks && !$bingDrinks) {
                $alcoholOutComes['outcome'] = "Patient is a heavy drinker. Counseled and dietary guidelines for alcohol provided.";
            } elseif (!$heavyDrinks && $bingDrinks) {
                $alcoholOutComes['outcome'] = "Patient is a binge drinker. Counseled and dietary guidelines for alcohol provided.";
            } else {
                $alcoholOutComes['outcome'] = "Patient is not accustomed to heavy and binge drinking. Counseled and dietary guidelines for alcohol provided.";
            }
    
            if ($heavyDrinks || $bingDrinks) {
                $alcoholOutComes['flag'] = true;
            }
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

            /* LDCT OUTCOME */
            if ($averagePacksperYear >= 30) {
                if ($performLdct) {
                    $tobaccoOutComes['ldct_counseling'] = "referral sent to KRMC for LDCT";
                } /*else {
                    $tobaccoOutComes['ldct_counseling'] = "Patient refused LDCT";
                }*/
            } else {
                $tobaccoOutComes['ldct_counseling'] = "Patient does not use tobacco, LDCT not applicable";
            }

            /* QUIT TOBACCO OUTCOME*/
            $quitTobacoo = "";
            $acceptQuittobacco = !empty($tobacco_usage['quit_tobacco']) ? $tobacco_usage['quit_tobacco'] : '';
            $tobacooAlternate = $tobacco_usage['tobacoo_alternate'] ?? '';
            $tobacooAlternateQty = !empty($tobacco_usage['tobacoo_alternate_qty']) ? $tobacco_usage['tobacoo_alternate_qty'] : "";
            if ($acceptQuittobacco == 'Yes' && $tobacooAlternate != "Refused") {
                $tobaccoOutComes['quit_tobacoo'] = 'Tobacco screening and cessation counseling perfomred. CDC guidelines given,' . ($tobacooAlternate != "" ? $tobacooAlternate : '') . ($tobacooAlternateQty != "" ? ' ' . $tobacooAlternateQty . ' started' : '');
            } elseif ($acceptQuittobacco == 'Yes' && $tobacooAlternate == "Refused") {
                $tobaccoOutComes['quit_tobacoo'] = 'Tobacco screening and cessation counseling perfomred. CDC guidelines given, Refused to start any other tobacoo alternate';
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
            $scriptFluVaccine = !empty($immunization['flu_vaccine_script_given']) && $immunization['flu_vaccine_script_given'] == "Yes" ? true : false;
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

            if ($scriptFluVaccine) {
                $immunizationOutcomes['flu_vaccine_script'] = "Script given for flu vaccine";
            } else {
                $immunizationOutcomes['flu_vaccine_script'] = "Script for flu vaccine is not provided.";
            }



            $refusedPneumococcalVaccine = !empty($immunization['pneumococcal_vaccine_refused']) && $immunization['pneumococcal_vaccine_refused'] == "Yes" ? true : false;
            $pneumococcalVaccine_received = !empty($immunization['pneumococcal_vaccine_recieved']) && $immunization['pneumococcal_vaccine_recieved'] == "Yes" ? true : false;
            
            $prevnarRecieved_on = !empty($immunization['pneumococcal_prevnar_recieved_on']) ? $immunization['pneumococcal_prevnar_recieved_on'] : "";
            $prevnarRecieved_at = !empty($immunization['pneumococcal_prevnar_recieved_at']) ? $immunization['pneumococcal_prevnar_recieved_at'] : "";

            $ppsvRecieved_on = !empty($immunization['pneumococcal_ppsv23_recieved_on']) ? $immunization['pneumococcal_ppsv23_recieved_on'] : "";
            $ppsvRecieved_at = !empty($immunization['pneumococcal_ppsv23_recieved_at']) ? $immunization['pneumococcal_ppsv23_recieved_at'] : "";

            $scriptPneumococcalVaccine = !empty($immunization['pneumococcal_vaccine_script_given']) && $immunization['pneumococcal_vaccine_script_given'] == "Yes" ? true : false;

            if ($refusedPneumococcalVaccine) {
                $immunizationOutcomes['pneumococcal_vaccine'] = "Patient refused Pneumococcal vaccine";
            } elseif ($pneumococcalVaccine_received) {
                if ($prevnarRecieved_on == "" && $ppsvRecieved_on == "") {
                    $immunizationOutcomes['pneumococcal_vaccine'] = 'Pneumococcal vaccine received';
                } else {
                    if (!empty($prevnarRecieved_on)) {
                        $immunizationOutcomes['pneumococcal_vaccine'] = "Received Prevnar 13 on " . $prevnarRecieved_on . ' at ' .$prevnarRecieved_at;
                    }

                    if (!empty($ppsvRecieved_on)) {
                        $br = !empty($prevnarRecieved_on) ? '<br>' : "";
                        $immunizationOutcomes['pneumococcal_vaccine'] = $br . "Received Prevnar 13 on " . $ppsvRecieved_on . ' at ' .$ppsvRecieved_at;
                    }
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

            if ($patientAge < 76 && $gender != "Male") {
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
                if ($gender == "Male") {
                    $screeningOutcomes["mammogram"] = 'Not Eligible';
                }
            }



            /* COLONOSCOPY */
            $refused_colonoscopy = !empty($screening['colonoscopy_refused']) && $screening['colonoscopy_refused'] == "Yes" ? true : false;
            $colonoscopy_done = !empty($screening['colonoscopy_done']) && $screening['colonoscopy_done'] == "Yes" ? true : false;
            $script_colonoscopy = !empty($screening['colonoscopy_script']) && $screening['colonoscopy_script'] == "Yes" ? true : false;
            $next_colonoscopy = !empty($screening['next_colonoscopy']) ? $screening['next_colonoscopy'] : "";
            $testType = !empty($screening['colon_test_type']) ? $screening['colon_test_type'] : "";


            if ($patientAge < 77) {
                if ($refused_colonoscopy) {
                    $screeningOutcomes["colonoscopy"] = "Refused Colonoscopy & FIT Test";
                } elseif ($colonoscopy_done) {
                    $colonoscopy_on = !empty($screening['colonoscopy_done_on']) ? $screening['colonoscopy_done_on'] : "";
                    $colonoscopy_at = !empty($screening['colonoscopy_done_at']) ? $screening['colonoscopy_done_at'] : "";
                    $colonoscopy_report_reviewed = !empty($screening['colonoscopy_report_reviewed']) && $screening['colonoscopy_report_reviewed'] == 1 ? "Report reviewed" : "";
                    if ($testType != "") {
                        $screeningOutcomes["colonoscopy"] = $testType . " done on " . $colonoscopy_on . ($colonoscopy_at != "" ? " at " . $colonoscopy_at : " ") . ' ' . $colonoscopy_report_reviewed;
                    }
                }

                if (!empty($next_colonoscopy)) {
                    $screeningOutcomes["next_colonoscopy"] = "Next " . $testType . " due on " . $next_colonoscopy;
                    $screeningOutcomes["test_type"] = $testType;
                    $screeningOutcomes["next_col_fit_guard"] = $next_colonoscopy;
                }

                if ($script_colonoscopy) {
                    $screeningOutcomes["colonoscopy_script"] = "Script given for the Screening Colonoscopy";
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
                                $diabetesOutcomes['next_hba1c_date'] = $nextHba1c_date;
                            }
                        } else {
                            if ($fbs_value > 100) {
                                $diabetesOutcomes['diabetes'] = 'Patient Fasting Blood Sugar is ' . $fbs_value . ' on ' . $fbs_date . '. HBA1C ordered.';
                            } else {
                                $diabetesOutcomes['diabetes'] = 'Patient Fasting Blood Sugar is ' . $fbs_value . ' on ' . $fbs_date . '.';
                            }
                        }
                    }

                    $nextFbs_date = Carbon::createFromDate($year_fbs, $month_fbs)->startOfMonth()->addMonth(12)->format('m/Y');
                    $diabetesOutcomes['next_fbs_date'] = $nextFbs_date;
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
                            $diabetesOutcomes['diabetes'] = 'Not applicable/ Controlled';
                        }
                    } else {
                        $diabetesOutcomes['diabetes'] = 'HBA1C ordered.';
                    }

                    $nextHba1c_date = Carbon::createFromDate($year, $month)->startOfMonth()->addMonth(6)->format('m/Y');
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
                }


                /* Diabetes NEPHROPATHY */
                $urine_microalbumin = !empty($diabatesScreening['urine_microalbumin']) ? $diabatesScreening['urine_microalbumin'] : '';
                $urine_microalbumin_ordered = !empty($diabatesScreening['urine_microalbumin_ordered']) ? $diabatesScreening['urine_microalbumin_ordered'] : '';
                $urine_microalbumin_date = !empty($diabatesScreening['urine_microalbumin_date']) ? $diabatesScreening['urine_microalbumin_date'] : '';
                $urine_microalbumin_report = !empty($diabatesScreening['urine_microalbumin_report']) ? $diabatesScreening['urine_microalbumin_report'] : '';

                $ace_inhibitor = !empty($diabatesScreening['urine_microalbumin_inhibitor']) ? $diabatesScreening['urine_microalbumin_inhibitor'] : '';
                $ckd_stage_4 = !empty($diabatesScreening['ckd_stage_4']) ? $diabatesScreening['ckd_stage_4'] : '';

                $urine_forMicroalbumin = $inhibitors = '';


                if (!empty($urine_microalbumin)) {
                    if ($urine_microalbumin == 'Yes') {
                        $urine_forMicroalbumin = 'Urine for Microalbumin is ' . $urine_microalbumin_report . ' on ' . $urine_microalbumin_date;
                    } else {
                        if ($urine_microalbumin_ordered != "") {
                            if ($urine_microalbumin_ordered == 'Yes') {
                                $urine_forMicroalbumin = "Urine for Micro-albumin ordered. ";
                            } else {
                                $urine_forMicroalbumin = "Patient refused urine for Micro-albuminemia. ";
                            }
                        }

                        if ($ace_inhibitor != "") {
                            if ($ace_inhibitor != "none") {
                                $ace_inhibitor = array_search($ace_inhibitor, Config::get('constants')['inhibitor']);
                                $inhibitors = 'Patient is receiving ' . $ace_inhibitor . ' therapy.';
                            } else {
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
                $systolic_bp = $bp_value['0'];
                $diastolic_bp = $bp_value['1'];

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
                    } else {
                        $referred_nutrionist = 'Patient refused Nutritionist referral.';
                    }
                    $weightAssessment['outcome'] = 'Dietary Guidelines summary 2020-2025 and CDC guidelines for physical activity provided to Patient. Counseled regarding Healthy eating and exercise. ' . $referred_nutrionist . ', advised to follow up with the PCP.';
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


    /* Filled Questionnaire */
    public function filledQuestionnaire(Request $request, $id)
    {
        try {
            $row = Questionaires::with('patient:id,first_name,mid_name,last_name,gender,age,dob', 'program:id,name,short_name')->where('id', $id)->first()->toArray();
            $questions_answers = json_decode($row['questions_answers'], true);
            $patient = $row['patient'];
            $program = $row['program'];
            $dateofService = $row['date_of_service'];
    
            if (!empty($questions_answers['depression_phq9'])) {
                $depressionArray = Config::get('constants')['depression_phq_9'];
                foreach ($questions_answers['depression_phq9'] as $key => $value) {
                    $depressionValue = array_search($value, $depressionArray);
                    if ($key != "problem_difficulty" && $key != "comments") {
                        $questions_answers['depression_phq9'][$key] = $depressionValue;
                    }
                }
            }
    
            $data = [
                'page_title' => 'Patient Survey Report' ?? [],
                'patient' => $patient ?? [],
                'program' => $program ?? [],
                'questionaire' => $questions_answers ?? [],
                'serial_no' => $serialno ?? [],
                'date_of_service' => $dateofService,
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
        $fluVaccineDate = Carbon::createFromDate($monthYear[1], $monthYear[0])->startOfMonth();
        $diffinMonths = Carbon::parse($fluVaccineDate)->diffInMonths($now);
        return $diffinMonths;
    }

    /* Calculate difference in YEARS */
    private function diffinYears($monthYear)
    {
        $now = Carbon::now();
        $fluVaccineDate = Carbon::createFromDate($monthYear[1], $monthYear[0])->startOfMonth();
        $diffinMonths = Carbon::parse($fluVaccineDate)->diffInYears($now);
        return $diffinMonths;
    }
}
