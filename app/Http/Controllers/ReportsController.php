<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Questionaires;
use App\Models\User;
use PDF;
use Config;
use Carbon\Carbon;

class ReportsController extends Controller
{
    protected $view = "reports.";
    protected $singular = "Questionaire Survey";

    public function index(Request $request,$serialno)
    {
        $data = $this->calculateReportOutcomes($serialno);
        return view($this->view.'analytics-report',$data);
    }


    /* Returning data with outcomes on behalf of formdata from the program 
    ** against the serial no*/
    private function calculateReportOutcomes ($serialno) {
        $row = Questionaires::with('patient:id,first_name,mid_name,last_name,gender,age,dob','program:id,name,short_name')->where('serial_no',$serialno)->first()->toArray();
        $questions_answers = json_decode($row['questions_answers'],true);

        $doctor = User::where(['role'=>2, 'id'=>$row['doctor_id']])->select('first_name', 'mid_name', 'last_name')->first();

        if (!empty($doctor)) {
            $row['doctor'] = @$doctor['first_name'].' '.@$doctor['mid_name'].' '.@$doctor['last_name'];
        }

        $physicalActivitiesOutComes = $alcoholOutComes = [];
        $physical_activities = $questions_answers['physical_activities'] ?? [];
        
        // PHYSICAL ACTIVITIES OUTCOMES FILTERS
        if(!empty($physical_activities['does_not_apply'])){
            $physicalActivitiesOutComes['outcome'] = 'Not Applicable, N/A, Patient is unable to perform exercise due to medical issue';
        }else{
            $totalMinuts = @$physical_activities['days_of_exercise']*@$physical_activities['mins_of_exercise'];
            $intensity = !empty($physical_activities['exercise_intensity']) ? $physical_activities['exercise_intensity'] : "";

            $highIntensityArray = ['moderate','heavy','veryheavy'];

            if($totalMinuts>= 150 && in_array($intensity, $highIntensityArray)){
                $physicalActivitiesOutComes['outcome'] = 'Patient is exercising as per recommendation. CDC guidelines for physical activity given.';
            }else {
                $physicalActivitiesOutComes['outcome'] = 'Patient counseled to exercise - recommend 150 minutes of moderate activity per week. CDC guidelines for physical activity given.';
                $physicalActivitiesOutComes['flag'] = true;
            }
        }


        // ALCOHOL USE OUTCOMES FILTERS
        $drinksPerWeek = (int)@$questions_answers['alcohol_use']['days_of_alcoholuse']*(int)@$questions_answers['alcohol_use']['drinks_per_day'];
        $drinksPerCccasion = (int)@$questions_answers['alcohol_use']['drinks_per_occasion'];

        $heavyDrinks = $bingDrinks = 0;
        $gender = $row['patient']['gender'];
        if($gender=="Male"){
            $heavyDrinks = $drinksPerWeek > 15 ? true : false;
            $bingDrinks = $drinksPerCccasion > 5 ? true : false;
        }else{
            $heavyDrinks = $drinksPerWeek > 8 ? true : false;
            $bingDrinks = $drinksPerCccasion > 4 ? true : false;
        }

        if($heavyDrinks && $bingDrinks){
            $alcoholOutComes['outcome'] = "Patient is a heavy and a binge drinker. Counseled and dietary guidelines for alcohol given";
        }elseif($heavyDrinks && !$bingDrinks){
            $alcoholOutComes['outcome'] = "Patient is a heavy drinker. Counseled and dietary guidelines for alcohol given";
        }elseif (!$heavyDrinks && $bingDrinks) {
            $alcoholOutComes['outcome'] = "Patient is a binge drinker. Counseled and dietary guidelines for alcohol given";
        } else {
            $alcoholOutComes['outcome'] = "Patient is not a heavy and a binge drinker. Counseled and dietary guidelines for alcohol given";
        }

        if ($heavyDrinks || $bingDrinks) {
            $alcoholOutComes['flag'] = true;
        }

        // TOBACOO USE OUTCOMES FILTER
        $tobaccoOutComes=[];
        if (isset($questions_answers['tobacco_use'])) {
            $tobaccoUse = $questions_answers['tobacco_use'];
            $ldctCounseling = "";

            $averagePacksperYear = !empty($tobaccoUse['average_packs_per_year']) ? $tobaccoUse['average_packs_per_year'] : 0;
            $performLdct = (!empty($tobaccoUse['perform_ldct']) && $tobaccoUse['perform_ldct'] == "Yes" ? true : false);

            /* LDCT OUTCOME */
            if ($averagePacksperYear >= 30) {
                if ($performLdct) {
                    $tobaccoOutComes['ldct_counseling'] = "Referral sent to KRMC for LDCT";
                } else {
                    $tobaccoOutComes['ldct_counseling'] = "Patient refused LDCT";
                }
            } else {
                $tobaccoOutComes['ldct_counseling'] = "Patient does not use tobacco, LDCT not applicable";
            }

            /* QUIT TOBACCO OUTCOME*/
            $quitTobacoo = "";
            $acceptQuittobacco = !empty($tobaccoUse['quit_tobacco']) ? $tobaccoUse['quit_tobacco'] : '';
            $tobacooAlternate = $tobaccoUse['tobacoo_alternate'] ?? '';
            $tobacooAlternateQty = !empty ($tobaccoUse['tobacoo_alternate_qty']) ? $tobaccoUse['tobacoo_alternate_qty'] : "";
            if ($acceptQuittobacco == 'Yes' && $tobacooAlternate != "Refused") {
                $tobaccoOutComes['quit_tobacoo'] = 'Patient Councelled to quit smoking. CDC guidelines given, '.$tobacooAlternate.' '.$tobacooAlternateQty.' started';
            } elseif ($acceptQuittobacco == 'Yes' && $tobacooAlternate == "Refused") {
                $tobaccoOutComes['quit_tobacoo'] = 'Patient Councelled to quit smoking. CDC guidelines given, Refused to start any other tobacoo alternate';
            } elseif ($acceptQuittobacco == 'No') {
                $tobaccoOutComes['quit_tobacoo'] = 'Patient is not interested in quitting tobacco';
            }

            if ($averagePacksperYear>=30 && !$performLdct) {
                $tobaccoOutComes['flag'] = true;
            }

        }

        // SEATBELT TEXT Filter
        $seatBelt = [];
        $seatBelt['outcome'] = (@$questions_answers['seatbelt_use']['wear_seal_belt']=="Yes")?'Patient always uses seatbelt in the car.':'Patient counseled on the use of seat belt in the car.';
        $seatBelt['flag'] = (@$questions_answers['seatbelt_use']['wear_seal_belt']=="No") ? true : false;

        // DEPRESSION PHQ-9 Filter
        if (!empty($questions_answers['depression_phq9'])) {
            $depression_score = array_sum ($questions_answers['depression_phq9']);
            $depression_OutComes = [];

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

        // HIGH STRESS
        $high_stress = [];
        $stressLevel = (!empty($questions_answers['high_stress'])) ? $questions_answers['high_stress']['stress_problem'] :'';
        $high_stress['outcome'] = ($stressLevel == 'Never or Rarely') ? strtolower($stressLevel) : $stressLevel ;
        if ($stressLevel == 'Always') {
            $high_stress['flag'] = true;
        }
        

        // GENERAL HEALTH
        $general_health = [];
        if (!empty($questions_answers['general_health'])) {
            
            $general_health['health_level'] = (!empty($questions_answers['general_health']['health_level'])) ? $questions_answers['general_health']['health_level'].' for your age' : '';
            $general_health['mouth_and_teeth'] = (!empty($questions_answers['general_health']['mouth_and_teeth'])) ? $questions_answers['general_health']['mouth_and_teeth'] : '';
            $general_health['feelings_cause_distress'] = (!empty($questions_answers['general_health']['feeling_caused_distress'])) ? $questions_answers['general_health']['feeling_caused_distress'] : '';
        
            if ($general_health['health_level'] == 'Poor' || $general_health['mouth_and_teeth'] == 'Poor' || $general_health['feelings_cause_distress'] == 'Yes') {
                $general_health['flag'] = true;
            }
        }

        // SOCIAL/EMOTIONAL SUPPORT
        $social_emotional_support = [];
        $supportLevel = (!empty($questions_answers['social_emotional_support'])) ? $questions_answers['social_emotional_support']['get_social_emotional_support'] : '';
        $social_emotional_support['outcome'] = $supportLevel;
        if ($supportLevel == 'Never') {
            $social_emotional_support['flag'] = true;
        }

        // PAIN
        $pain = [];
        $painLevel = (!empty($questions_answers['pain'])) ? $questions_answers['pain']['pain_felt'] : '';
        $pain['outcome'] = $painLevel;
        if ($painLevel == 'Alot') {
            $pain['flag'] = true;
        }

        // PHYSICAL HEALTH - FALL SCREENING
        $fallScreeningOutcomes = [];
        if (!empty($questions_answers['fall_screening'])) {
            $fall_screening = $questions_answers['fall_screening'];

            $fallinpastYear = !empty($fall_screening['fall_in_one_year']) && $fall_screening['fall_in_one_year'] == "Yes" ? true : false;        
            $noOfFall = !empty($fall_screening['number_of_falls']) ? $fall_screening['number_of_falls'] : 0;
            $fallInjury = !empty($fall_screening['injury']) ? $fall_screening['injury'] : '';
            $physicalTherapy = !empty($fall_screening['physical_therapy']) ? $fall_screening['physical_therapy'] : '';
            
            /* Fall in past year Outcome */
            if (!$fallinpastYear) {
                $fallScreeningOutcomes['fall_outcome'] = "No fall in last 1 year";
            } elseif ($fallinpastYear && $noOfFall != 0) {
                $physical_therapy = ($physicalTherapy != "" && $physicalTherapy != "Already receiving") ? ', physical therapy '.lcfirst($physicalTherapy) : 'Already receiving physical therapy' ;
                $fallScreeningOutcomes['fall_outcome'] = $noOfFall." fall in the last 1 year". ($fallInjury != '' && $fallInjury == "Yes" ? ', with injury. ' : ', with no injury. '). ($physicalTherapy != "" ? $physical_therapy.'.' : '');
            }

            /* Unsteady Outcomes */
            $unsteady_todo_things = !empty($fall_screening['unsteady_todo_things']) && $fall_screening['unsteady_todo_things'] == "Yes" ? true : false;
            if ($unsteady_todo_things) {
                $therapyReferred = ' & referred to physical therapy for muscle strengthening, gain training  & balance';
                $noTherapy = 'but refused PT';
                $fallScreeningOutcomes['unsteady_outcome'] = 'Patient is unsteady with ambulation'. ($physicalTherapy == 'Referred' ? lcfirst($therapyReferred).'.' : $noTherapy);
            } else{
                $fallScreeningOutcomes['unsteady_outcome'] = "";
            }

            /* Blacking-out Outcomes */
            $blacking_out = !empty($fall_screening['blackingout_from_bed']) && $fall_screening['blackingout_from_bed'] == "Yes" ? true : false;
            if ($blacking_out) {
                $fallScreeningOutcomes['blackingout_outcome'] = 'Patient feels blacking out with ambulation.';
            } else {
                $fallScreeningOutcomes['blackingout_outcome'] = '';
            }

            /* Assistance Device */
            $assistance_device = !empty($fall_screening['assistance_device']) ? $fall_screening['assistance_device'] : '';

            if ($assistance_device != 'None') {
                $fallScreeningOutcomes['assistance_device_outcome'] = "Patient will continue to use ".$assistance_device." for mobilization.";
            } else {
                $fallScreeningOutcomes['assistance_device_outcome'] = "Patient is not using any assistive device.";
            }
        }


        // COGNITIVE ASSESSMENT
        $cognitiveOutcomes = [];
        if (!empty ($questions_answers['cognitive_assessment'])) {
            
            $yearRecalled = $monthRecalled = $hourRecalled = $reverseCount = $reverseMonth = $addressRecalled = 0;

            if (!empty($questions_answers['cognitive_assessment']['year_recalled'])) {
                $yearRecalled = $questions_answers['cognitive_assessment']['year_recalled'] == 'incorrect' ? 4 : 0;
            }
            
            if (!empty($questions_answers['cognitive_assessment']['month_recalled'])) {
                $monthRecalled = $questions_answers['cognitive_assessment']['month_recalled'] == 'incorrect' ? 3 : 0;
            }

            if (!empty($questions_answers['cognitive_assessment']['hour_recalled'])) {
                $hourRecalled = $questions_answers['cognitive_assessment']['hour_recalled'] == 'incorrect' ? 3 : 0;
            }

            if (!empty($questions_answers['cognitive_assessment']['reverse_count'])) {
                $reverseCount = ($questions_answers['cognitive_assessment']['reverse_count']) == '1 error' ? 2 : (($questions_answers['cognitive_assessment']['reverse_count'] == 'more than 1 error') ? 4 : 0);
            }

            if (!empty($questions_answers['cognitive_assessment']['reverse_month'])) {
                $reverseMonth = ($questions_answers['cognitive_assessment']['reverse_month']) == '1 error' ? 2 : (($questions_answers['cognitive_assessment']['reverse_month'] == 'more than 1 error') ? 4 : 0);
            }

            if (!empty($questions_answers['cognitive_assessment']['address_recalled'])) {
                $errorArray = Config::get('constants')['error_options_c'];
                foreach ($errorArray as $key => $value) {
                    if ($questions_answers['cognitive_assessment']['address_recalled'] == $value) {
                        $addressRecalled = (int)$key;
                    }
                }
            }
            
            $cogScore = $yearRecalled + $monthRecalled + $hourRecalled + $reverseCount + $reverseMonth + $addressRecalled;

            if ($cogScore <= 7) {
                $cognitiveOutcomes['outcome'] = 'Referral not necessary at present.';
            } elseif ($cogScore >= 8 && $cogScore <= 9) {
                $cognitiveOutcomes['outcome'] = 'Probably refer.';
                $cognitiveOutcomes['flag'] = true;
            } elseif ($cogScore >= 10 && $cogScore <= 28) {
                $cognitiveOutcomes['outcome'] = 'Refer';
                $cognitiveOutcomes['flag'] = true;
            }
        }


        // IMMUNIZATION
        $immunizationOutcomes = [];
        if (!empty($questions_answers['immunization'])) {
            $immunization = $questions_answers['immunization'];
            $refusedFluVaccine = !empty($immunization['flu_vaccine_refused']) && $immunization['flu_vaccine_refused'] == 'Yes' ? true : false;
            $scriptFluVaccine = !empty($immunization['flu_vaccine_script_given']) && $immunization['flu_vaccine_script_given'] == "Yes" ? true : false;
            $recievedFluvaccineOn = !empty($immunization['flu_vaccine_recieved_on']) ? $immunization['flu_vaccine_recieved_on'] : "";
            $recievedFluvaccineAt = !empty($immunization['flu_vaccine_recieved_at']) ? $immunization['flu_vaccine_recieved_at'] : "";

            if ($refusedFluVaccine) {
                $immunizationOutcomes['flu_vaccine'] = "Refused flu vaccine";
                $immunizationOutcomes['flag'] = true;
            } else if (!empty($immunization['flu_vaccine_recieved']) && $immunization['flu_vaccine_recieved'] == 'Yes') {
                $immunizationOutcomes['flu_vaccine'] = "Received flu vaccine ". ($recievedFluvaccineOn != "" ? "on ".$recievedFluvaccineOn : ""). ($recievedFluvaccineAt != "" ? " at ".$recievedFluvaccineAt : "");
            }
            
            if ($scriptFluVaccine) {
                $immunizationOutcomes['flu_vaccine_script'] = "Script given for flu vaccine";
            } else {
                $immunizationOutcomes['flu_vaccine_script'] = "";
            }



            $refusedPneumococcalVaccine = !empty($immunization['pneumococcal_vaccine_refused']) && $immunization['pneumococcal_vaccine_refused'] == "Yes" ? true : false;
            $pneumococcalVaccine_received = !empty($immunization['pneumococcal_vaccine_recieved']) && $immunization['pneumococcal_vaccine_recieved'] == "Yes" ? true : false;
            $prevnarRecieved_on = !empty($immunization['pneumococcal_prevnar_recieved_on']) ? $immunization['pneumococcal_prevnar_recieved_on'] : "";
            $ppsvRecieved_on = !empty($immunization['pneumococcal_ppsv23_recieved_on']) ? $immunization['pneumococcal_ppsv23_recieved_on'] : "";
            $scriptPneumococcalVaccine = !empty($immunization['pneumococcal_vaccine_script_given']) && $immunization['pneumococcal_vaccine_script_given'] == "Yes" ? true : false;
            

            if ($refusedPneumococcalVaccine) {
                $immunizationOutcomes['pneumococcal_vaccine'] = "Refused Pneumococcal vaccine";
            } elseif ($pneumococcalVaccine_received) {
                if ($prevnarRecieved_on == "" && $ppsvRecieved_on =="") {
                    $immunizationOutcomes['pneumococcal_vaccine'] = 'Pneumococcal vaccine received';
                } else {
                    if (!empty($prevnarRecieved_on)) {
                        $immunizationOutcomes['pneumococcal_vaccine'] = "Received Prevnar 13 on ".$prevnarRecieved_on.' at '.$immunization['pneumococcal_prevnar_recieved_at'];
                    }
        
                    if (!empty($ppsvRecieved_on)) {
                        $br = !empty($prevnarRecieved_on) ? '<br>' : "";
                        $immunizationOutcomes['pneumococcal_vaccine'] = $br."Received Prevnar 13 on ".$ppsvRecieved_on.' at '.$immunization['pneumococcal_ppsv23_recieved_at'];
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

                    $flu_months = ['1','2','3','4','8','9','10','11','12'];
                    if (in_array($current_month, $flu_months)) {
                        $fluNextDue = Carbon::create()->startOfMonth()->month($current_month)->year($current_Year)->format('m/Y');
                    } else {
                        $fluNextDue = Carbon::create()->startOfMonth()->month(8)->year($current_Year)->format('m/Y');
                    }
                }

                $immunizationOutcomes['nextFluVaccine'] = $fluNextDue;
            }

            $fluNextDue = $lastFluVaccine = '';


            if ($refusedFluVaccine || $lastFluVaccine >= 12) {
                $immunizationOutcomes['flag'] = true;
            }
        }

        // SCREENING
        $screeningOutcomes = [];
        if (!empty($questions_answers['screening'])) {

            /* MAMMOGRAM */
            $refused_mammogram = !empty($questions_answers['screening']['mammogram_refused']) && $questions_answers['screening']['mammogram_refused'] == "Yes" ? true : false;
            $mammogram_done = !empty($questions_answers['screening']['mammogram_done']) && $questions_answers['screening']['mammogram_done'] == "Yes" ? true : false;
            $script_mammogram = !empty($questions_answers['screening']['mammogram_script']) && $questions_answers['screening']['mammogram_script'] == "Yes" ? true : false;
            $next_mammogram = !empty($questions_answers['screening']['next_mommogram']) ? $questions_answers['screening']['next_mommogram'] : "";
            $lastMammogramDiff = '';
            
            if ($row['patient']['age'] < 76) {
                if ($refused_mammogram) {
                    $screeningOutcomes["mammogram"] = "Refused Mammogram";
                } elseif ($mammogram_done) {
                    $mammogram_on = !empty($questions_answers['screening']['mammogram_done_on']) ? $questions_answers['screening']['mammogram_done_on'] : "";
                    $mammogram_at = !empty($questions_answers['screening']['mammogram_done_at']) ? $questions_answers['screening']['mammogram_done_at'] : "";
                    $mammogram_report_reviewed = !empty($questions_answers['screening']['mommogram_report_reviewed']) && $questions_answers['screening']['mommogram_report_reviewed'] == 1 ? "Report reviewed" : "";
                    $screeningOutcomes["mammogram"] = "Mammogram done on ".$mammogram_on. ($mammogram_at != "" ? " at ".$mammogram_at : " ").'. '.$mammogram_report_reviewed;
    
                    if ($mammogram_on != "") {
                        $monthYear = explode('/', $mammogram_on);
                        $lastMammogramDiff = $this->diffinMonths($monthYear, '2');
                    }    
                }
    
                if ($refused_mammogram || !$mammogram_done || $lastMammogramDiff > 27) {
                    $screeningOutcomes["mammogaram_flag"] = true;
                }
    
                if (!empty($next_mammogram)) {
                    $screeningOutcomes["next_mammogram"] = "Next Mammogram due on ".$next_mammogram;
                    $screeningOutcomes["next_mammogram_date"] = $next_mammogram;
                }
    
                if($script_mammogram) {    
                    $screeningOutcomes["mammogram_script"] = "Script given for the Screening Mammogram";
                }
            } else {
                $screeningOutcomes["mammogram"] = 'N/A due to age';
            }



            /* COLONOSCOPY */
            $refused_colonoscopy = !empty($questions_answers['screening']['colonoscopy_refused']) && $questions_answers['screening']['colonoscopy_refused'] == "Yes" ? true : false;
            $colonoscopy_done = !empty($questions_answers['screening']['colonoscopy_done']) && $questions_answers['screening']['colonoscopy_done'] == "Yes" ? true : false;
            $script_colonoscopy = !empty($questions_answers['screening']['colonoscopy_script']) && $questions_answers['screening']['colonoscopy_script'] == "Yes" ? true : false;
            $next_colonoscopy = !empty($questions_answers['screening']['next_colonoscopy']) ? $questions_answers['screening']['next_colonoscopy'] : "";
            $testType = !empty($questions_answers['screening']['colon_test_type']) ? $questions_answers['screening']['colon_test_type'] : "";


            if ($row['patient']['age'] < 77) {
                if ($refused_colonoscopy) {
                    $screeningOutcomes["colonoscopy"] = "Refused Colonoscopy & FIT Test";
                } elseif ($colonoscopy_done) {
                    $colonoscopy_on = !empty($questions_answers['screening']['colonoscopy_done_on']) ? $questions_answers['screening']['colonoscopy_done_on'] : "";
                    $colonoscopy_at = !empty($questions_answers['screening']['colonoscopy_done_at']) ? $questions_answers['screening']['colonoscopy_done_at'] : "";
                    $colonoscopy_report_reviewed = !empty($questions_answers['screening']['colonoscopy_report_reviewed']) && $questions_answers['screening']['colonoscopy_report_reviewed'] == 1 ? "Report reviewed" : "";
                    if ($testType != "") {
                        $screeningOutcomes["colonoscopy"] = $testType." done on ".$colonoscopy_on. ($colonoscopy_at != "" ? " at ".$colonoscopy_at : " ").' '.$colonoscopy_report_reviewed;
                    }
                }
    
                if (!empty($next_colonoscopy)) {
                    $screeningOutcomes["next_colonoscopy"] = "Next ".$testType." due on ".$next_colonoscopy;
                    $screeningOutcomes["test_type"] = $testType;
                    $screeningOutcomes["next_col_fit_guard"] = $next_colonoscopy;
                }
        
                if($script_colonoscopy) {
                    $screeningOutcomes["colonoscopy_script"] = "Script given for the Screening Colonoscopy";
                }
    
    
                $lastTestExpired = false;
                if ($testType != "") {
                    if ($colonoscopy_on != "") {
                        $monthYear = explode('/', $colonoscopy_on);
                        $lastTestDiff = $this->diffinMonths($monthYear, '3');
                        
                        if ($testType == 'Colonoscopy') {
                            $lastTestExpired = ($lastTestDiff > 120 ? true: false);
                        } elseif ($testType == 'Fit Test') {
                            $lastTestExpired = ($lastTestDiff > 12 ? true: false);
                        } elseif ($testType == 'Cologuard') {
                            $lastTestExpired = ($lastTestDiff > 24 ? true: false);
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


        // DIABETES
        $diabetesOutcomes = [];
        if (!empty($questions_answers['diabetes'])) {

            $diabatesScreening = $questions_answers['diabetes'];

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
                    $diabetesOutcomes['flag'] = true;
                } else {
                    if (!empty($fbs_value) && !empty($fbs_date)) {
                        $current_Date = Carbon::now()->floorMonth();
                        $dateMonthArray = explode('/', $fbs_date);
                        $month_fbs = $dateMonthArray[0];
                        $year_fbs = $dateMonthArray[1];

                        $date_format = Carbon::createFromDate($year_fbs, $month_fbs)->startOfMonth();
                        $lastfbs_monthdiff = $current_Date->diffInMonths($date_format, '4');

                        if ($lastfbs_monthdiff > 12) {
                            $diabetesOutcomes['diabetes'] = 'Patient Fasting Blood Sugar is '.$fbs_value.' on '.$fbs_date.'. FBS ordered. ';
                        } elseif ($hba1c_value != "" && $hba1c_date != "") {
                            $current_Date = Carbon::now()->floorMonth();
                            $dateMonthArray = explode('/', $hba1c_date);
                            $month_hba1c = $dateMonthArray[0];
                            $year_hba1c = $dateMonthArray[1];
                            $date_format = Carbon::createFromDate($year_hba1c, $month_hba1c)->startOfMonth();

                            $lasthba1c_monthdiff = $current_Date->diffInMonths($date_format, '5');

                            if ($lasthba1c_monthdiff > 6) {
                                $diabetesOutcomes['diabetes'] = 'Patient Fasting Blood Sugar is '.$fbs_value.' on '.$fbs_date.'. HBA1C ordered';
                            } else {
                                if ($hba1c_value <= 5.6) {
                                    $diabetesOutcomes['diabetes'] = 'Patient Fasting Blood Sugar is '.$fbs_value.' on '.$fbs_date.'. Patient HBA1C is '.$hba1c_value. ' on '.$hba1c_date;
                                } elseif ($hba1c_value > 5.6 && $hba1c_value <= 6.4) {
                                    $diabetesOutcomes['diabetes'] = 'Patient Fasting Blood Sugar is '.$fbs_value.' on '.$fbs_date.'. Patient HBA1C is '.$hba1c_value. ' on '.$hba1c_date.' will monitor HBA1C';
                                } elseif ($hba1c_value >= 6.5 && $hba1c_value <= 6.9) {
                                    $diabetesOutcomes['diabetes'] = 'HBA1C is '.$hba1c_value.'. Patient has new onset DM. Urine Microalbuminemia and Eye examination ordered.';
                                } elseif ($hba1c_value >= 6.9 && $hba1c_value <= 8.5) {
                                    $diabetesOutcomes['diabetes'] = 'HBA1C is '.$hba1c_value.'. Patient has new onset DM. Urina Microalbuminemia and Eye examination ordered. “Notify Doctor” ';
                                } elseif ($hba1c_value >= 8.5) {
                                    $diabetesOutcomes['diabetes'] = 'HBA1C is '.$hba1c_value.'. Patient has new onset DM. Urina Microalbuminemia and Eye examination ordered. Referred to Diabetic Clinic for intensive Diabetic control. ';
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
                                $diabetesOutcomes['diabetes'] = 'Patient Fasting Blood Sugar is '.$fbs_value.' on '.$fbs_date.'. HBA1C ordered.';
                            } else {
                                $diabetesOutcomes['diabetes'] = 'Patient Fasting Blood Sugar is '.$fbs_value.' on '.$fbs_date.'.';
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
                            $diabetesOutcomes['diabetes'] = 'HBA1C is'.$hba1c_value.'. Referred to Diabetic Clinic for intensive Diabetic control. ';
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
                $eye_exam_doctor = !empty($diabatesScreening['eye_exam_doctor']) ? ' by Dr.'.$diabatesScreening['eye_exam_doctor'] : '';
                $eye_exam_facility = !empty($diabatesScreening['eye_exam_facility']) ? ' at '.$diabatesScreening['eye_exam_facility'] : '';
                $eye_exam_date = !empty($diabatesScreening['eye_exam_date']) ? $diabatesScreening['eye_exam_date'] : '';
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
                    if ($diabetec_eye_exam_report == "report_available") {
                        $diabetesOutcomes['diabetec_eye_exam'] = 'Diabetic Eye examination done on '.$eye_exam_date.$eye_exam_doctor.$eye_exam_facility.' '.($diabetec_eye_exam_reviewed ? 'Report reviewed' : "")." ".($diabetec_diabetec_ratinopathy ? 'and shows Diabetic Ratinopathy' : "and shows No Diabetec Ratinopathy");
                    } elseif ($diabetec_eye_exam_report == "report_requested") {
                        $diabetesOutcomes['diabetec_eye_exam'] = 'Report Requested';
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
                        $urine_forMicroalbumin = 'Urine for Microalbumin is '.$urine_microalbumin_report. ' on '.$urine_microalbumin_date;
                    } else {
                        if ($urine_microalbumin_ordered != "") {
                            if ($urine_microalbumin_ordered == 'Yes') {
                                $urine_forMicroalbumin = "Urine for Micro-albumin ordered. ";
                            } else {
                                $urine_forMicroalbumin = "Patient refused urine for Micro-albuminemia ";
                            }
                        }
                        
                        if ($ace_inhibitor != "") {
                            if ($ace_inhibitor != "none") {
                                $ace_inhibitor = array_search($ace_inhibitor, Config::get('constants')['inhibitor']);
                                $inhibitors = 'Patient is on '.  $ace_inhibitor;
                            } else {
                                $inhibitors = 'Patient '.($ckd_stage_4 == "ckd_stage_4" ? 'has CKD Stage 4' : "sees the Nephrologist");
                            }
                        }
                    }
                    
                    $diabetesOutcomes['nepropathy'] = $urine_forMicroalbumin.''.$inhibitors;
                }
    
                if($urine_microalbumin && $urine_microalbumin_ordered && $ckd_stage_4 != "patient_see_nephrologist" ) {
                    $diabetesOutcomes['nephropathy_flag'] = true;
                }
            }
        }

        // CHOLESTEROL ASSESSMENT
        $cholesterol_outcome = [];
        if (!empty($questions_answers['cholesterol_assessment'])) {
            
            $cholesterolAssessment = $questions_answers['cholesterol_assessment'];

            $ldlValue = !empty($cholesterolAssessment['ldl_value']) ? $cholesterolAssessment['ldl_value'] : '';
            $lastLDLdate = !empty($cholesterolAssessment['ldl_date']) ? $cholesterolAssessment['ldl_date'] : '';
            $lipidProfile = !empty($cholesterolAssessment['ldl_in_last_12months']) ? $cholesterolAssessment['ldl_in_last_12months'] : '';
            $useStatin = !empty($cholesterolAssessment['statin_prescribed']) ? $cholesterolAssessment['statin_prescribed'] : '';
            $statinDosage = !empty($cholesterolAssessment['statintype_dosage']) ? $cholesterolAssessment['statintype_dosage'] : '';
            $activeDiabetes = !empty($cholesterolAssessment['active_diabetes']) ? $cholesterolAssessment['active_diabetes'] : '';
            $ldlinPasttwoyears = !empty($cholesterolAssessment['ldl_range_in_past_two_years']) ? $cholesterolAssessment['ldl_range_in_past_two_years'] : '';

            if ($ldlValue != "") {
                $cholesterol_outcome['ldl_result'] = 'Patient LDL is '.$ldlValue. ($lastLDLdate != '' ? ' on '.$lastLDLdate : '');
            }

            if ($lipidProfile != '' && $lipidProfile == 'No') {
                $cholesterol_outcome['outcome'] = "Order Fasting Lipid Profile";
            } elseif ($ldlinPasttwoyears != "" && $ldlinPasttwoyears == 'No') {
                $cholesterol_outcome['outcome'] = "Documented medical reason for not being on statin therapy is most recent fasting or direct LDL-C<70 mg/dL";
            } elseif ($activeDiabetes != "" && $activeDiabetes == 'No') {
                $cholesterol_outcome['outcome'] = "Patient was screened for requirement of statin therapy and does not require a statin prescription at this time.";
            } else {
                if ($useStatin != '') {
                    if ($useStatin == 'Yes') {
                        $cholesterol_outcome['outcome'] = 'Patient is receiving statin therapy with '.$statinDosage.' as prescribed by PCP';
                    } else {
                        $reasonFornoStatin = '';
                        $reasonArray = $depressionArray = Config::get('constants')['statin_medical_reason'];
                        foreach ($reasonArray as $key => $value) {
                            if (!empty($cholesterolAssessment['medical_reason_for_nostatin'.$key])) {
                                $reasonFornoStatin .= $cholesterolAssessment['medical_reason_for_nostatin'.$key].', ';
                            }
                        }
    
                        if ($reasonFornoStatin != '') {
                            $cholesterol_outcome['outcome'] = 'Documented medical reason for not being on statin therapy is '.$reasonFornoStatin;
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


        // BP ASSESSMENT
        $bpAssessment = [];
        if (!empty($questions_answers['bp_assessment'])) {
            $bp_assessment = $questions_answers['bp_assessment'];
            $bp_value = !empty($bp_assessment['bp_value']) ? explode('/', $bp_assessment['bp_value']) : '';

            if ($bp_value != '') {
                $systolic_bp = $bp_value['0'];
                $diastolic_bp = $bp_value['1'];

                $bpAssessment['bp_result'] = 'Patient BP is '.$bp_assessment['bp_value'];

                if ($systolic_bp < 100 && $diastolic_bp < 90) {
                    $bpAssessment['outcome'] = 'Patient BP is controlled. It is '.$bp_value;
                } elseif ($systolic_bp >= 140 && $systolic_bp < 160 && $diastolic_bp >= 90 && $diastolic_bp < 100) {
                    $bpAssessment['outcome'] = 'Patient BP is elevated. Patient will follow up with PCP';
                } elseif ($systolic_bp >= 160 && $diastolic_bp >= 100) {
                    $bpAssessment['outcome'] = 'Patient BP is elevated. Patient will follow up with PCP. Notify PCP.';
                }

                if ($systolic_bp > 120 || $diastolic_bp > 80) {
                    $bpAssessment['flag'] = true;
                }
            }
        }


        // WEIGHT ASSESSMENT
        $weightAssessment = [];
        if (!empty($questions_answers['weight_assessment'])) {
            $weight_assessment = $questions_answers['weight_assessment'];
            $bmi_value = !empty($weight_assessment['bmi_value']) ? $weight_assessment['bmi_value'] : '';
            
            if (!empty($bmi_value)) {
                $nutritionist_referral = !empty($weight_assessment['followup_withnutritionist']) ? $weight_assessment['followup_withnutritionist'] : '';

                $weightAssessment['bmi_result'] = 'Patient BMI is '.$weight_assessment['bmi_value'].'.';
                
                if ($bmi_value >= 30) {    
                    $referred_nutrionist = '';
                    if ($nutritionist_referral == 'Yes') {
                        $referred_nutrionist = 'Patient referred to the Nutritionist.';
                    } else {
                        $referred_nutrionist = 'Patient refused Nutritionist referral.';
                    }
                    $weightAssessment['outcome'] = 'Patient given Dietary Guidelines summary  2020-2025 and councelled on Healthy eating. Patient advised to follow up with the PCP. '.$referred_nutrionist.' Patient counseled to exercise - recommend 150 minutes of moderate activity per week. CDC guidelines for physical activity given';
                } elseif ($bmi_value > 25 && $bmi_value < 30) {
                    $weightAssessment['outcome'] = 'Patient is over weight.';
                } elseif ($bmi_value > 15 && $bmi_value < 25) {
                    $weightAssessment['outcome'] = 'Patient has ideal BMI.';
                } elseif ($bmi_value < 15) {
                    $weightAssessment['outcome'] = 'Patient is underweight.';
                }

                if ($bmi_value > 25 && ($nutritionist_referral == '' || $nutritionist_referral == 'No' )) {
                    $weightAssessment['flag'] = true;
                }
            }
        }


        $physical_exam_outcome = [];
        if (!empty($questions_answers['physical_exam'])) {
            $physical_exam = $questions_answers['physical_exam'];

            $general = $physical_exam['general'] ?? '';
            $eyes = $physical_exam['eyes'] ?? '';
            $neck = $physical_exam['neck'] ?? '';
            $lungs = $physical_exam['lungs'] ?? '';
            $heart = $physical_exam['heart'] ?? '';
            $neuro = $physical_exam['neuro'] ?? '';
            $extremeties = $physical_exam['extremeties'] ?? '';
            $gi = $physical_exam['gi'] ?? '';
            $ears = $physical_exam['ears'] ?? '';
            $nose = $physical_exam['nose'] ?? '';
            $throat = $physical_exam['throat'] ?? '';
            $skin = $physical_exam['skin'] ?? '';
            $oral_cavity = $physical_exam['oral_cavity'] ?? '';
            $ms = $physical_exam['ms'] ?? '';
            
            if ($general == 'Abnormal') {
                $physical_exam_outcome['General'] = 'Obese/thin build, appearing older/younger to stated age, in distress/pain.';
            } else {
                $physical_exam_outcome['General'] = 'Normotensive, in no acute distress.';
            }

            if ($eyes == 'Abnormal') {
                $physical_exam_outcome['Eyes'] = 'Sclera icteric. Conjunctiva pale/pink/red, injected.';
            } else {
                $physical_exam_outcome['Eyes'] = "PERRLA, EOM's full, conjunctivae clear, fundi grossly normal.";
            }

            if ($neck == 'Abnormal') {
                $physical_exam_outcome['Neck'] = 'Supraclavicular or cervical adenopathy, thyromegaly, tender to palpation.';
            } else {
                $physical_exam_outcome['Neck'] = 'Supple, no masses, no thyromegaly, no bruits.';
            }

            if ($lungs == 'Abnormal') {
                $physical_exam_outcome['Lungs'] = 'Decreased air excursion, positive for wheezing, positive for crepitation, asymmetric chest movement on respiration.';
            } else {
                $physical_exam_outcome['Lungs'] = 'Lungs clear, no rales, no rhonchi, no wheezes.';
            }

            if ($heart == 'Abnormal') {
                $physical_exam_outcome['Heart'] = 'Pericardial rub present, positive for murmur, positive for gallop, irregular rate and rhythm.';
            } else {
                $physical_exam_outcome['Heart'] = 'RRR, no murmurs, no rubs, no gallops.';
            }

            if ($neuro == 'Abnormal') {
                $physical_exam_outcome['Neuro'] = 'Disoriented, semiconscious, cognition decreased, decreased sensation.';
            } else {
                $physical_exam_outcome['Neuro'] = 'Physiological, no localizing findings.';
            }

            if ($extremeties == 'Abnormal') {
                $physical_exam_outcome['Extremeties'] = 'Cold/clammy. Pedal pulses weak/absent. Varicose veins present. Pedal edema present.';
            } else {
                $physical_exam_outcome['Extremeties'] = 'Warm, well perfused, no edema.';
            }

            if ($gi == 'Abnormal') {
                $physical_exam_outcome['GI'] = 'Abdomen protuberant/doughy/rotund, bowel sounds sluggish/absent/increased, liver palpable, spleen palpable, abdomen rigid.';
            } else {
                $physical_exam_outcome['GI'] = 'Normal, no lesions, no discharge, no hernias noted.';
            }

            if ($ears == 'Abnormal') {
                $physical_exam_outcome['Ears'] = 'Tympanic membranes appear swollen, pus discharge.';
            } else {
                $physical_exam_outcome['Ears'] = "EAC's clear, TM's normal";
            }

            if ($nose == 'Abnormal') {
                $physical_exam_outcome['Nose'] = 'Congested, nasal discharge.';
            } else {
                $physical_exam_outcome['Nose'] = 'Mucosa normal, no obstruction.';
            }

            if ($throat == 'Abnormal') {
                $physical_exam_outcome['Throat'] = 'Swollen, red, tonsillar enlargement.';
            } else {
                $physical_exam_outcome['Throat'] = 'Clear, no exudates, no lesions.';
            }

            if ($skin == 'Abnormal') {
                $physical_exam_outcome['Skin'] = 'Tags present, discoloration present, moist/dry.';
            } else {
                $physical_exam_outcome['Skin'] = 'Normal, no rashes, no lesions noted.';
            }

            if ($oral_cavity == 'Abnormal') {
                $physical_exam_outcome['Oral cavity'] = 'Oral Cavity:  Missing teeth, edentulous, oropharynx red and swollen.';
            } else {
                $physical_exam_outcome['Oral cavity'] = 'There are no lesions or masses in the oral cavity or oropharynx. Palatal elevation is symmetric.';
            }

            if ($ms == 'Abnormal') {
                $physical_exam_outcome['MS'] = 'Decreased muscle strength, weakness of extremities, loss of muscle tone.';
            } else {
                $physical_exam_outcome['MS'] = 'Full range of motion, no muscle spasm or tenderness.';
            }
        }

        $data = [
            'page_title' => 'Analytics Report',
            'row' => $row,
            'seatbelt_use' => $seatBelt ?? [],
            'alcohol_out_comes' => $alcoholOutComes ?? [],
            'tobacco_out_comes' => $tobaccoOutComes ?? [],
            'depression_out_comes' => $depression_OutComes ?? [],
            'physical_out_comes' => $physicalActivitiesOutComes ?? [],
            'high_stress' => $high_stress ?? [],
            'general_health' => $general_health ?? [],
            'social_emotional_support' => $social_emotional_support ?? [],
            'pain' => $pain ?? [],
            'fall_screening' => $fallScreeningOutcomes ?? [],
            'cognitive_assessment' => $cognitiveOutcomes ?? [],
            'immunization' => $immunizationOutcomes ?? [],
            'screening' => $screeningOutcomes ?? [],
            'diabetes' => $diabetesOutcomes ?? [],
            'cholesterol_assessment' => $cholesterol_outcome ?? [],
            'bp_assessment' => $bpAssessment ?? [],
            'weight_assessment' => $weightAssessment ?? [],
            'miscellaneous' => $questions_answers['miscellaneous'] ?? [],
            'physical_exam' => $physical_exam_outcome ?? []
        ];

        return $data;
    }


    /* Returning form data from the Questionaire
    ** against the serialno */
    private function questionaireFormData($serialno)
    {
        $row = Questionaires::with('patient:id,first_name,mid_name,last_name,gender,age,dob','program:id,name,short_name')->where('serial_no',$serialno)->first()->toArray();
        $questions_answers = json_decode($row['questions_answers'],true);
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

        return $data;
    }


    /* Create Core Report including
    ** Questionaires: Question and Answers
    ** Patient and Program Data */
    public function coreReport (Request $request,$serialno)
    {
        $data = $this->questionaireFormData($serialno);
        return view($this->view.'corereport',$data);
    }


    /* To downlaod the analytical report of the Program */
    public function downloadAnalyticalReport(Request $request,$serialno)
    {
        $data = $this->calculateReportOutcomes($serialno);
        ini_set('max_execution_time', 120);
        $pdf = PDF::loadView($this->view.'analytics-pdf-report',$data);
        return $pdf->download('awv-care-plan.pdf');
    }


    /* Download the Core report in pdf  */
    public function downloadFullReport(Request $request,$serialno)
    {
        $data = $this->questionaireFormData($serialno);
        $pdf = PDF::loadView($this->view.'core-pdf-report', $data);
        return $pdf->download('awv-questionnaire.pdf');
    }


    public function diffinMonths($monthYear, $add)
    {
        $now = Carbon::now();
        $fluVaccineDate = Carbon::createFromDate($monthYear[1], $monthYear[0])->startOfMonth();
        $diffinMonths = Carbon::parse($fluVaccineDate)->diffInMonths($now);
        return $diffinMonths;
    }
    
    public function diffinYears($monthYear)
    {
        $now = Carbon::now();
        $fluVaccineDate = Carbon::createFromDate($monthYear[1], $monthYear[0])->startOfMonth();
        $diffinMonths = Carbon::parse($fluVaccineDate)->diffInYears($now);
        return $diffinMonths;
    }

    public function saveSignature(Request $request)
    {
        $doctor_id = $request->doctor_id;
        $questionnaire_id = $request->questionaire_id;
        $serialNo = $request->questionaire_serialno;


        try {

            $current_Date = Carbon::now()->toDateTimeString();

            $data = [
                'doctor_id' => $doctor_id,
                'signed_date' => $current_Date,
            ];
    
            Questionaires::where('id',$request->questionaire_id)->update($data);
            $response = array('success'=>true,'message'=>$this->singular.' Updated Successfully','action'=>'redirect','url'=>url('/dashboard/reports/analytics-report/'.$serialNo));
        } catch (\Throwable $th) {
            $response = array('success'=>false,'message'=>$e->getMessage());
        }

        return response()->json($response);
        
    }
}
