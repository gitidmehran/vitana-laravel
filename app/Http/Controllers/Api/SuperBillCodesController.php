<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Patients;
use App\Models\Diagnosis;
use App\Models\Questionaires;
use App\Models\SuperBillCodes;

use PDF,Config;
use Carbon\Carbon;

class SuperBillCodesController extends Controller
{
    protected $view = "superbill.";

    public function index(Request $request,$id)
    {
        try {
            $row = Questionaires::with('superBill','patient.insurance','patient.doctor')->where('id',$id)->first()->toArray();
            $list = [
                'patient_name' => $row['patient']['name'] ?? '',
                'dob' => $row['patient']['dob'] ?? '',
                'date_of_service' => $row['date_of_service'] ?? '',
                'insurance' => $row['patient']['insurance']['name'] ?? '',
                'doctor' => $row['patient']['doctor']['name'] ?? ''
            ];

            $question = json_decode($row['questions_answers'], true);

            $patient_id = $row['patient']['id'];
            $clinic_id = $row['patient']['clinic_id'];
            $predefined_codes = $this->superBillCodes($id, $question, $patient_id, $clinic_id);

            $clause = [
                'patient_id' => $patient_id,
                'status' => "ACTIVE",
            ];
            $patient_diagnosis = Diagnosis::where($clause)->get()->toArray();

            $codes = $row['super_bill']['codes'] ?? [];
            $codes = !empty($codes)?json_decode($codes,true):[];
            
            $newCodes = $codes['new_codes'] ?? [];
            $dxCodes = $codes['dx_codes'] ?? [];

            /* Unset dx codes array from main array */
            // if(isset($codes['dx_codes'])) unset($codes['dx_codes']);

            /* Unset dx codes array from main array */
            // if(isset($codes['new_codes'])) unset($codes['new_codes']);

            foreach ($predefined_codes as $key => $value) {
                $predefined_codes[$key] = $value == 'true'?true:false;
            }

            

            // New Codes
            $newCodes = !empty($newCodes)?json_decode($newCodes,true):[];
            foreach ($newCodes as $key => $value) {
                $newCodes[$key] = $value=='true'?true:false;
            }
            
            // Dx Codes
            $dxCodes = !empty($dxCodes)?json_decode($dxCodes,true):[];
            foreach ($dxCodes as $key => $value) {
                $dxCodes[$key] = $value=='true'?true:false;
            }

            /*
            ** Getting patient Chronic disease codes
            ** Getting Chronic codes from the config constant
            ** getting common code to show the patient disease codes in super bill
             */
            $patient_diagnosis = array_column($patient_diagnosis, 'condition');
            $patient_diagnosis = array_values($patient_diagnosis);

            $chronic_diseases = Config::get('constants')['chronic_diseases'];
            $chronic_diseases = array_values($chronic_diseases);
            $chronic_diseases = array_merge(...$chronic_diseases);

            $common_codes = array_intersect($patient_diagnosis, $chronic_diseases);

            foreach ($common_codes as $key => $value) {
                $dxCodes[$value] = true;
            }


            $list['codes'] = $predefined_codes;
            $list['new_codes'] = $newCodes;
            $list['dxcodes'] = $dxCodes;
            $response = array('success'=>true,'message'=>'Data Retrived','data'=>$list);
        } catch (\Exception $e) {
            $response = array('success'=>false,'message'=>$e->getMessage());
        }
        return response()->json($response);
    }

    public function store(Request $request){
        try {
            $questionId = $request->get('question_id');
            $isDxcode =  $request->get('dx_codes') ?? '';
            $code = $request->get('code');
            $query = SuperBillCodes::where('question_id',$questionId)->first();
            $codes = !empty($query)?json_decode($query['codes'],true):[];    
            if(!empty($isDxcode)){
                $newCodes =!empty($codes['dx_codes'])?json_decode($codes['dx_codes'],true):[];
                $newCodes[$isDxcode] = "true";
                $codes['dx_codes'] = json_encode($newCodes);
            }else{
                $newCodes =!empty($codes['new_codes'])?json_decode($codes['new_codes'],true):[];
                $newCodes[$code] = "true";
                $codes['new_codes'] = json_encode($newCodes);
            }
            
            SuperBillCodes::where('question_id',$questionId)->update(['codes'=>json_encode($codes)]);
            if(!empty($isDxcode)){
                $response = array('success'=>true,'message'=>'Dx Code Added Successfully','data'=>$isDxcode);
            }else{
                $response = array('success'=>true,'message'=>'Code Added Successfully','data'=>$code);
            }
        } catch (\Exception $e) {
          $response = array('success'=>false,'message'=>$e->getMessage());   
        }
        return response()->json($response);
    }

    public function update(Request $request){
        try {
            $input = $request->all();
            $query = SuperBillCodes::where('question_id',$input['question_id'])->first();
            $codes = !empty($query)?json_decode($query['codes'],true):[];
            $code = str_replace('"', "", $input['code']);
            $codes[$code] = (string)$input['status'];
            SuperBillCodes::where('question_id',$input['question_id'])->update(['codes'=>json_encode($codes)]);
            $response = array('success'=>true,'message'=>'Code Updated Successfully');
        } catch (\Exception $e) {
            $response = array('success'=>false,'message'=>$e->getMessage());      
        }
        return response()->json($response);
    }

    public function destroy(Request $request)
    {
        try {
            $questionId = $request->get('question_id');
            $code = $request->get('code');
            $code = str_replace('"', "", $code);
            $query = SuperBillCodes::where('question_id',$questionId)->first();
            $codes = !empty($query)?json_decode($query['codes'],true):[];
            $newCodes =!empty($codes['new_codes'])?json_decode($codes['new_codes'],true):[];
            if (isset($newCodes[$code])) {
                unset($newCodes[$code]);
            }
            $codes['new_codes'] = json_encode($newCodes);
            SuperBillCodes::where('question_id',$questionId)->update(['codes'=>json_encode($codes)]);
            $response = array('success'=>true,'message'=>'Code Deleted Successfully','data'=>json_decode($codes['new_codes'], true));

        } catch (\Exception $e) {
            $response = array('success'=>false,'message'=>$e->getMessage());      
        }
        return response()->json($response);
    }

    public function destroy_dx(Request $request)
    {
        try {
            $questionId = $request->get('question_id');
            $code = $request->get('dx_codes');
            $code = str_replace('"', "", $code);
            $query = SuperBillCodes::where('question_id',$questionId)->first();           
            $codes = !empty($query)?json_decode($query['codes'],true):[];
            $newCodes =!empty($codes['dx_codes'])?json_decode($codes['dx_codes'],true):[];
            
            if (isset($newCodes[$code])) {
                unset($newCodes[$code]);
            }
            
            $codes['dx_codes'] = json_encode($newCodes);  
            SuperBillCodes::where('question_id',$questionId)->update(['codes'=>json_encode($codes)]);
            
            $response = array('success'=>true,'message'=>'Dx Code Deleted Successfully','data'=>json_decode($codes['dx_codes'], true));
        } catch (\Exception $e) {
            $response = array('success'=>false,'message'=>$e->getMessage());      
        }
        return response()->json($response);
    }


    /** 
     * Will update the status of super bill codes as per saved questionnaire
     * @param  int  $questionid
     * @param  array  $questions
     * @param  int  $patientId
     * @param  int  $clinicId
     * @return Array of super codes
    */
    private function superBillCodes($questionId, $questions, $patientId='', $clinicId)
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


                case 'depression_phq9':
                    $depression_score = array_sum($row);

                    $codes[] = [
                        'G8510' => $depression_score < 9 ? "true" : "false",
                        'G8431' => $depression_score > 9 ? "true" : "false",
                        'G9717' => !empty($depressionCodeMatched) ? "true" : "false",
                        'G0444' => !empty($row) ? "true" : "false",
                    ];
                    break;

                case 'pain':
                    $pain_felt = $row['pain_felt'];

                    $codes[] = [
                        '1125F' => ($pain_felt != "" && $pain_felt != "None") ? "true" : "false",
                        '1126F' => $pain_felt == "None"  ? "true" : "false",
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
                        '3017F' => $colonoscopyReportReviewed == "0" ? "true" : "false",
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
                        '99406' => $currentSmoker == "Yes" ? "true" : "false",
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
                    $asprinUse = @$row['patient_on_asprin'] ?? "";
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

        $data = [
            'codes' => $codes,
            'insurance_type' => $insurance_type,
            'medicare_option' => $medicare_option,
            'patient'=> $patient
        ];

        /* Check based on insurances */
        if ($insurance_code == "hum-001") {
            $codes[] = [
                "96160" => "true",
                "99397" => "true",
            ];
        }
        if ($insurance_type == "2") {
            /* Typeid-2 is for commercial */
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
        } else if ($insurance_type == "1") {
            $codes[] = [
                '99385' => "false",
                '99386' => "false",
                '99395' => "false",
                '99396' => "false",
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

        $new_predefined_code = 0;

        if(!empty($codes)){
            foreach ($codes as $key => $value) {
                foreach($value as $code => $status) {
                    if(array_key_exists($code, $defaultcodes)){
                        $defaultcodes[$code] = $status;
                    } else {
                        $new_predefined_code = 1;
                        $defaultcodes[$code] = $status;
                    }
                }

            }
        }

        SuperBillCodes::where('question_id',$questionId)->update(['codes'=>json_encode($defaultcodes)]);


        return $defaultcodes;

        /* if(empty($superBill)){
            $data = ['question_id'=>$questionId,'codes'=>json_encode($defaultcodes), 'clinic_id'=>$clinicId, 'created_user'=>Auth::id()];
            SuperBillCodes::create($data);
        }else{
            SuperBillCodes::where('question_id',$questionId)->update(['codes'=>json_encode($defaultcodes)]);
        } */
    }

    /* Downloadsuperbill pdf */
    public function downloadSuperBill(Request $request, $id)
    {
        $data = $this->index($request, $id)->getData();

        try {
            $superBillData = [];
            foreach ($data->data as $key => $value) {
                if ($key == "codes") {
                    $superBillData[$key] = json_decode(json_encode($value), true);
                } else {
                    $superBillData[$key] = $value;
                }
            }

            $superBillData['date_of_service'] = Carbon::parse($superBillData['date_of_service'])->format('m/d/Y');
            $superBillData['dob'] = Carbon::parse($superBillData['dob'])->format('m/d/Y');

            ini_set('max_execution_time', 120);
            $pdf = PDF::loadView($this->view.'super-bill-pdf',$superBillData);
            $headers = array(
                'Content-Type: application/pdf',
            );
            return $pdf->download('super-bill-pdf.pdf', $headers);
        } catch (\Exception $e) {
            $data = [
                'success' => false,
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
            ];
            return response()->json($data);
        }

    }

    /* Checking HTML of super bill */
    public function superbillHtml(Request $request, $id)
    {
        $data = $this->index($request, $id)->getData();
        $superBillData = [];

        // return response()->json($data, 200);

        foreach ($data->data as $key => $value) {
            if ($key == "codes") {
                $superBillData[$key] = json_decode(json_encode($value), true);
            } else {
                $superBillData[$key] = $value;
            }
        }
        // dd($superBillData);
        return view($this->view.'super-bill-pdf', $superBillData);
    }
}