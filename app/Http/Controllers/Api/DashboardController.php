<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserCollection;

use App\Models\Questionaires;
use App\Models\Patients;
use App\Models\PatientStatusLogs;
use App\Models\User;
use App\Models\Programs;
use App\Models\Clinic;
use App\Models\Insurances;
use App\Models\Schedule;
// CareGaps = Healthchoice Pathways
use App\Models\CareGaps;
use App\Models\HumanaCareGaps;
use App\Models\MedicareArizonaCareGaps;
use App\Models\AetnaMedicareCareGaps;
use App\Models\AllwellMedicareCareGaps;
use App\Models\HealthchoiceArizonaCareGaps;
use App\Models\UnitedHealthcareCareGaps;

use App\Models\CareGapsDetails;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

use Auth,Validator,Hash,DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    
    public function index(Request $request)
    {
        $per_page = Config('constants.perpage_showdata');
                   
        try {
            $doctor_id = @$request->get('doctor_id') ?? '';
            $insurance_id = @$request->get('insurance_id') ?? '';
            $assigned_status = @$request->get('assignedstatus') ?? '';
            $gaps_filterYear = @$request->get('filteryear') ?? "";

            if (empty($insurance_id)) {
                $insurance_id = $this->FindLargestPopulationInsurance();
            }
            $insurancePrvider= $this->insuranceNameFind($insurance_id);

            $clinic_id = $request->get('clinic_id') ?? '';
            $program_id = $request->get('program_id') ?? '';
            $careGap = $request->get('careGap') ?? '';
            $currentYear = Carbon::now()->year;

            $logPopulation = PatientStatusLogs::where('insurance_id', $insurance_id)->where('patient_year', $gaps_filterYear)->count();

            $tableSource = new Patients();

            if ($gaps_filterYear < $currentYear && $logPopulation > 0) {
                $tableSource = new PatientStatusLogs();
            }

            $query = $tableSource->whereNotNull('patient_year')->where(['insurance_id'=> $insurance_id])
            ->orWhereHas('insuranceHistories', function ($query) use ($insurance_id, $gaps_filterYear) {
                $query->where('insurance_id', $insurance_id)->whereYear('insurance_end_date', $gaps_filterYear);
            })
            ->when(!empty($doctor_id), function ($query) use ($doctor_id) {
                $query->where('doctor_id', $doctor_id);
            })
            ->when(!empty($clinic_id), function ($query) use ($clinic_id) {
                $query->where('clinic_id', $clinic_id);
            })
            ->when(!empty($assigned_status), function ($query) use ($assigned_status, $gaps_filterYear, $insurance_id) {
                if ($assigned_status == "true") {
                    $query->where('status', '1');
                } else {
                    $query->where(function ($query) {
                        $query->where('status', 2)->orWhereNull('status');
                    });
                }
            });

            $data = $query->get();

            
            // Filter rows where patient_year matches the provided year
            $yearPopulation = array_filter($data->toArray(), function($row) use ($gaps_filterYear) {
                return $row['patient_year'] == $gaps_filterYear;
            });
            
            $yearPopulation = count($yearPopulation);

            // Filter rows where patient_year is null
            $lastYearPopulation = array_filter($data->toArray(), function($row) use ($gaps_filterYear) {
                return $row['patient_year'] ==  Carbon::now()->subYear()->year;
            });

            $lastYearPopulation = count($lastYearPopulation);

            $total['totalPopulation'] = $yearPopulation > 0 ? $yearPopulation : $lastYearPopulation;
            // $total['totalPopulation'] = $data->count();

            // $yearFilter = $logPopulation != 0 && $gaps_filterYear == $currentYear ? Carbon::now()->subYear()->year : $gaps_filterYear;
            // $yearFilter = class_basename($tableSource) == 'Patients' && $gaps_filterYear == $currentYear ? $currentYear : $gaps_filterYear;

            $yearFilter = $gaps_filterYear;

            if ($gaps_filterYear == $currentYear) {
                $prevYear = Carbon::now()->subYear()->year;
                $prevPopulation = PatientStatusLogs::where('insurance_id', $insurance_id)->where('patient_year', $prevYear)->count();
                if ($prevPopulation == 0) {
                    $yearFilter = $prevYear;
                }
            }


            $total['group_a1'] = $data->where('group', '1')->where('patient_year', $yearFilter)->count();
            $total['group_a2'] = $data->where('group', '2')->where('patient_year', $yearFilter)->count();
            $total['group_b'] = $data->where('group', '3')->where('patient_year', $yearFilter)->count();
            $total['group_c'] = $data->where('group', '4')->where('patient_year', $yearFilter)->count();


            if($total['totalPopulation'] > 0){
                $total['group_b_percentage'] = round(($total['group_b'] / $total['totalPopulation']) * 100);
                $total['group_c_percentage'] = round(($total['group_c'] / $total['totalPopulation']) * 100);
                $total['uncategorized'] = $data->WhereNull('group')->count();
                $total['activeUsers'] = $total['group_a1'] + $total['group_a2'];
                $total['activePercentage'] = round(($total['group_a1'] + $total['group_a2'])/$total['totalPopulation'] * 100);
            } else {
                $total['group_b_percentage'] = '0';
                $total['group_c_percentage'] = '0';
                $total['uncategorized'] = !empty($data) ? $data->WhereNull('group')->count() : 0;
                $total['activeUsers'] = $total['group_a1'] + $total['group_a2'];
                $total['activePercentage'] = '0';
            }

            /* Getting Dcotors List */
            $doctor_data = User::OfClinicID($clinic_id)->whereIn('role',['21', '13'])->get()->toArray();

            $insurance_data = Insurances::select('id','name','short_name','type_id','provider')->get()->toArray();
            
            $program_data = Programs::select('id','name','short_name')->get()->toArray();
            
            $clinic_data = Clinic::select('id','name','short_name')->get()->toArray();
            
            // Health Choice Pathway Start
            if (!empty($insurance_id) && $insurancePrvider->type_id == 1 && $insurancePrvider->provider == "hcpw-001") {
                $lastFileInsertIntoCareGaps = CareGaps::where('source','CareGap_File')->latest()->get()->first();
                $total['date'] = !empty($lastFileInsertIntoCareGaps) ? date('m/d/Y', strtotime($lastFileInsertIntoCareGaps->created_at)) : "Record Not Exist";
            
            // ---------------------------- End of Health Choice Pathway ---------------------------------
            // Humana Start 
            } elseif (!empty($insurance_id) && $insurancePrvider->type_id == 1 && $insurancePrvider->provider == "hum-001") {
                $lastFileInsertIntoCareGaps = HumanaCareGaps::where('source','HumanaCareGap_File')->latest()->get()->first();
                $total['date'] = !empty($lastFileInsertIntoCareGaps) ? date('m/d/Y', strtotime($lastFileInsertIntoCareGaps->created_at)) : "Record Not Exist";
            
            // ---------------------------- End of Humana ---------------------------------
            // Arizona Medicare Start 
            } elseif (!empty($insurance_id) && $insurancePrvider->type_id == 1 && $insurancePrvider->provider == "med-arz-001") {
                $lastFileInsertIntoCareGaps = MedicareArizonaCareGaps::where('source','MedicareArizonaCareGap_File')->latest()->get()->first();
                $total['date'] = !empty($lastFileInsertIntoCareGaps) ? date('m/d/Y', strtotime($lastFileInsertIntoCareGaps->created_at)) : "Record Not Exist";
            
            // ---------------------------- End of Medicare Arizona ---------------------------------
            // Aetna Medicare Start 
            } elseif (!empty($insurance_id) && $insurancePrvider->type_id == 1 && $insurancePrvider->provider == "aet-001") {
                $lastFileInsertIntoCareGaps = AetnaMedicareCareGaps::where('source','AetnaMedicareCareGap_File')->latest()->get()->first();
                $total['date'] = !empty($lastFileInsertIntoCareGaps) ? date('m/d/Y', strtotime($lastFileInsertIntoCareGaps->created_at)) : "Record Not Exist";
            
            // ---------------------------- End of Aetna Medicare ---------------------------------
            // Allwell Medicare Start 
            } elseif (!empty($insurance_id) && $insurancePrvider->type_id == 1 && $insurancePrvider->provider == "allwell-001") {
                $lastFileInsertIntoCareGaps = AllwellMedicareCareGaps::where('source','AllwellMedicareCareGap_File')->latest()->get()->first();
                $total['date'] = !empty($lastFileInsertIntoCareGaps) ? date('m/d/Y', strtotime($lastFileInsertIntoCareGaps->created_at)) : "Record Not Exist";
            
            // ---------------------------- End of Allwell Medicare ---------------------------------
                // Healthchoice Arizona Start 
            } elseif (!empty($insurance_id) && $insurancePrvider->type_id == 3 && $insurancePrvider->provider == "hcarz-001") {
                $lastFileInsertIntoCareGaps = HealthchoiceArizonaCareGaps::where('source','HealthchoiceArizonaCareGap_File')->latest()->get()->first();
                $total['date'] = !empty($lastFileInsertIntoCareGaps) ? date('m/d/Y', strtotime($lastFileInsertIntoCareGaps->created_at)) : "Record Not Exist";

            // ---------------------------- End of Healthchoice Arizona ---------------------------------
                // United Healthcare Start 
            } elseif (!empty($insurance_id) && $insurancePrvider->type_id == 1 && $insurancePrvider->provider == "uhc-001") {
                $lastFileInsertIntoCareGaps = UnitedHealthcareCareGaps::where('source','UnitedHealthcareCareGap_File')->latest()->get()->first();
                $total['date'] = !empty($lastFileInsertIntoCareGaps) ? date('m/d/Y', strtotime($lastFileInsertIntoCareGaps->created_at)) : "Record Not Exist";

            }
            // ---------------------------- End of United Healthcare ---------------------------------
            
            //last_visit
            if (empty($total)) {
                $total = 0;
                $responseData = [
                    'success' => true,
                    'message' => 'Sorry Dashboard Data Not Found',
                    'data' => $total,
                    'doctor_data' => @$doctor_data,
                    'insurance_data' => @$insurance_data,
                    'program_data' => @$program_data,
                    'clinic_data' => @$clinic_data,
                    'total_clinics' => count($clinic_data),
                    'total_insurances' => count($insurance_data),
                    'insurance_type_id' => (int)$insurancePrvider->type_id,
                ];
            } else {
                $responseData = [
                    'success' => true,
                    'message' => 'Data Found Successfully',
                    'perpage_showdata' =>$per_page,
                    'data' => $total,
                    'doctor_data' => @$doctor_data,
                    'insurance_data' => @$insurance_data,
                    'program_data' => @$program_data,
                    'clinic_data' => @$clinic_data,
                    'total_clinics' => count($clinic_data),
                    'total_insurances' => count($insurance_data),
                    'insurance_id' => (int)$insurance_id,
                    'insurance_type_id' => (int)$insurancePrvider->type_id,
                ];
            }
            
            // $insurancePrvider= $this->insuranceNameFind($insurance_id); 
            if (!empty($insurance_id) && $insurancePrvider->type_id == 1 && $insurancePrvider->provider == "all-001") {
                // health Choice Pathway Start
            } elseif (!empty($insurance_id) && $insurancePrvider->type_id == 1 && $insurancePrvider->provider == "hcpw-001") {
                //$BCS Breast Cancer Screening 
                $BCS = $this->breastCancerScreening($doctor_id, $insurance_id, $clinic_id, $careGap, $gaps_filterYear, $insurancePrvider);

                // $CCS Colorectal Cancer Screening
                $CCS = $this->colorectalCancerGap($doctor_id, $insurance_id, $clinic_id, $careGap, $gaps_filterYear, $insurancePrvider);

                //$CHBP Controlling High Blood Pressure 
                $CHBP = $this->controllingHighBloodPressure($doctor_id, $insurance_id, $clinic_id, $careGap, $gaps_filterYear, $insurancePrvider);

                // $BSPC Diabetes Care - Blood Sugar Poor Control (CDC>9.0%) 
                $BSPC = $this->bloodSugarPoorGap($doctor_id, $insurance_id, $clinic_id, $careGap, $gaps_filterYear, $insurancePrvider);

                //$EyeExam  Diabetes Care - Eye Exam 
                $EyeExam = $this->diabetesEyeExam($doctor_id, $insurance_id, $clinic_id, $careGap, $gaps_filterYear, $insurancePrvider);

                // $STG Diabetes Care - Blood Sugar Control (CDC < 8%) 
                $STG = $this->statinTherapyGap($doctor_id, $insurance_id, $clinic_id, $careGap, $gaps_filterYear, $insurancePrvider);

                // osteoporosis Mgmt Gap
                $OMG = $this->osteoporosisMgmtGap($doctor_id, $insurance_id, $clinic_id, $careGap, $gaps_filterYear, $insurancePrvider);

                //$COA2  Care for Older Adults - Medication Review
                $COA2 = $this->olderAdultsMedicationReview($doctor_id, $insurance_id, $clinic_id, $careGap, $gaps_filterYear, $insurancePrvider);

                //$COA4  Care for Older Adults - Medication Review
                $COA4 = $this->olderAdultsPainAssessment($doctor_id, $insurance_id, $clinic_id, $careGap, $gaps_filterYear, $insurancePrvider);

                //$TRCM  Transitions of Care-Medication Reconciliation Post-Discharge
                $TRCM = $this->medicalReconciliationPostDischarge($doctor_id, $insurance_id, $clinic_id, $careGap, $gaps_filterYear, $insurancePrvider);
                
                //$AWV  Annual Wellness Visit
                $AWV = $this->awvCareGapStatus($doctor_id, $insurance_id, $clinic_id, $careGap, $gaps_filterYear, $insurancePrvider);

                $healthChoicePathway = [
                    $BCS,
                    $CCS,
                    $CHBP,
                    $BSPC,
                    $EyeExam,
                    $STG,
                    $OMG,
                    $COA2,
                    $COA4,
                    $TRCM,
                    $AWV,
                ];
                $responseData['insurancesName'] = "healthChoicePathway";
                $responseData['healthChoicePathway'] = $healthChoicePathway;
                
            // ---------------------------- End of Health Choice Pathway ---------------------------------
            // Humana Start 
            } elseif (!empty($insurance_id) && $insurancePrvider->type_id == 1 && $insurancePrvider->provider == "hum-001") {
                //$BCS Breast Cancer Screening 
                $BCS = $this->breastCancerScreening($doctor_id, $insurance_id, $clinic_id, $careGap, $gaps_filterYear, $insurancePrvider);

                // $CCS Colorectal Cancer Screening
                $CCS = $this->colorectalCancerGap($doctor_id, $insurance_id, $clinic_id, $careGap, $gaps_filterYear, $insurancePrvider);

                //$CHBP Controlling High Blood Pressure 
                $CHBP = $this->controllingHighBloodPressure($doctor_id, $insurance_id, $clinic_id, $careGap, $gaps_filterYear, $insurancePrvider);
        
                //$EyeExam  Diabetes Care - Eye Exam 
                $EyeExam = $this->diabetesEyeExam($doctor_id, $insurance_id, $clinic_id, $careGap, $gaps_filterYear, $insurancePrvider);
               
                // faed_visit_gap Follow-Up After Emergency Department Visit for MCC (FMC)
                $FMC = $this->FollowUpAfterEmergencyDepartmentVisit($doctor_id, $insurance_id, $clinic_id, $careGap, $gaps_filterYear, $insurancePrvider);
            
                // $BSPC Diabetes Care - Blood Sugar Poor Control (CDC>9.0%) 
                $BSPC = $this->bloodSugarPoorGap($doctor_id, $insurance_id, $clinic_id, $careGap, $gaps_filterYear, $insurancePrvider);

                // omw_fracture_gap Osteoporosis Management in Women Who Had a Fracture (OMW)
                $OMW = $this->OsteoporosisManagementWomenHumana($doctor_id, $insurance_id, $clinic_id, $careGap, $gaps_filterYear, $insurancePrvider);

                // pc_readmissions_gap Plan All-Cause Readmissions (PCR)
                $PCR = $this->PlanAllCauseReadmissionsHumana($doctor_id, $insurance_id, $clinic_id, $careGap, $gaps_filterYear, $insurancePrvider);

                // spc_disease_gap Statin Therapy for Patients with Cardiovascular Disease: Received Statin Therapy (SPC_STATIN)
                $SPC_STATIN = $this->StatinTherapyPatientsCardiovascularDisease($doctor_id, $insurance_id, $clinic_id, $careGap, $gaps_filterYear, $insurancePrvider);

                // post_disc_gap Transitions of Care: Medication Reconciliation Post Discharge (TRC_MRP)
                $TRC_MRP = $this->TransitionsMedicationReconciliationPostDischargeHumana($doctor_id, $insurance_id, $clinic_id, $careGap, $gaps_filterYear, $insurancePrvider);

                // after_inp_disc_gap Transitions of Care: Patient Engagement After Inpatient Discharge (TRC_PED)
                $TRC_PED = $this->TransitionsPatientEngagementAfterInpatientDischargeHumana($doctor_id, $insurance_id, $clinic_id, $careGap, $gaps_filterYear, $insurancePrvider);

                // ma_cholesterol_gap Medication Adherence for Cholesterol (Statins): Statins (ADH_STATIN)
                $ADH_STATIN = $this->MedicationAdherenceCholesterolStatinsHumana($doctor_id, $insurance_id, $clinic_id, $careGap, $gaps_filterYear, $insurancePrvider);

                //mad_medications_gap Medication Adherence for Diabetes Medications: Diabetes Medications (ADH_DIAB)
                $ADH_DIAB = $this->MedicationAdherenceDiabetesMedicationsHumana($doctor_id, $insurance_id, $clinic_id, $careGap, $gaps_filterYear, $insurancePrvider);

                //ma_hypertension_gap  Medication Adherence for Hypertension (ACE or ARB): ACE (ADH_ACE)
                $ADH_ACE = $this->MedicationAdherenceHypertensionHumana($doctor_id, $insurance_id, $clinic_id, $careGap, $gaps_filterYear, $insurancePrvider);

                //sup_diabetes_gap Statin Use in Persons with Diabetes (SUPD)
                $SUPD = $this->StatinPersonsDiabetes($doctor_id, $insurance_id, $clinic_id, $careGap, $gaps_filterYear, $insurancePrvider);
                
                //$awv_gap  Annual Wellness Visit
                $AWV = $this->awvCareGapStatus($doctor_id, $insurance_id, $clinic_id, $careGap, $gaps_filterYear, $insurancePrvider);

                $humana = [
                    $BCS,
                    $CCS,
                    $CHBP,
                    $BSPC,
                    $EyeExam,
                    $FMC,
                    $OMW,
                    $PCR,
                    $SPC_STATIN,
                    $TRC_MRP,
                    $TRC_PED,
                    $ADH_STATIN,
                    $ADH_DIAB,
                    $ADH_ACE,
                    $SUPD,
                    $AWV,
                ];
                $responseData['insurancesName'] = "Humana";
                $responseData['humana'] = $humana;
                
            // ---------------------------- End of Humana ---------------------------------
            //Medicare Arizona Start
            } elseif (!empty($insurance_id) && $insurancePrvider->type_id == 1 && $insurancePrvider->provider == "med-arz-001") {
                //$BCS Breast Cancer Screening 
                $BCS = $this->breastCancerScreening($doctor_id, $insurance_id, $clinic_id, $careGap, $gaps_filterYear, $insurancePrvider);

                // $CCS Colorectal Cancer Screening
                $CCS = $this->colorectalCancerGap($doctor_id, $insurance_id, $clinic_id, $careGap, $gaps_filterYear, $insurancePrvider);

                //$CHBP Controlling High Blood Pressure 
                $CHBP = $this->controllingHighBloodPressure($doctor_id, $insurance_id, $clinic_id, $careGap, $gaps_filterYear, $insurancePrvider);
        
                // $BSPC Diabetes Care - Blood Sugar Control (CDC>9.0%) 
                $BSPC = $this->bloodSugarGap($doctor_id, $insurance_id, $clinic_id, $careGap, $gaps_filterYear, $insurancePrvider);

                //$awv_gap  Annual Wellness Visit
                $AWV = $this->awvCareGapStatus($doctor_id, $insurance_id, $clinic_id, $careGap, $gaps_filterYear, $insurancePrvider);

                $medicareArizona = [
                    $BCS,
                    $CCS,
                    $CHBP,
                    $BSPC,
                    $AWV,
                ];
                $responseData['insurancesName'] = "medicareArizona";
                $responseData['medicareArizona'] = $medicareArizona;

            // ---------------------------- End of Medicare Arizona ---------------------------------
            // Aetna Medicare Start          
            } elseif (!empty($insurance_id) && $insurancePrvider->type_id == 1 && $insurancePrvider->provider == "aet-001") {
                //$BCS Breast Cancer Screening 
                $BCS = $this->breastCancerScreening($doctor_id, $insurance_id, $clinic_id, $careGap, $gaps_filterYear, $insurancePrvider);

                // $CCS Colorectal Cancer Screening
                $CCS = $this->colorectalCancerGap($doctor_id, $insurance_id, $clinic_id, $careGap, $gaps_filterYear, $insurancePrvider);
        
                //$EyeExam  Diabetes Care - Eye Exam 
                $EyeExam = $this->diabetesEyeExam($doctor_id, $insurance_id, $clinic_id, $careGap, $gaps_filterYear, $insurancePrvider);
               
                // faed_visit_gap Follow-Up After Emergency Department Visit for MCC (FMC)
                $FMC = $this->FollowUpAfterEmergencyDepartmentVisit($doctor_id, $insurance_id, $clinic_id, $careGap, $gaps_filterYear, $insurancePrvider);
            
                // $BSPC Diabetes Care - Blood Sugar Poor Control (CDC>9.0%) 
                $BSPC = $this->bloodSugarGap($doctor_id, $insurance_id, $clinic_id, $careGap, $gaps_filterYear, $insurancePrvider);

                // omw_fracture_gap Osteoporosis Management in Women Who Had a Fracture (OMW)
                $OMW = $this->OsteoporosisManagementWomenAetnaMedicare($doctor_id, $insurance_id, $clinic_id, $careGap, $gaps_filterYear, $insurancePrvider);

                // pc_readmissions_gap Plan All-Cause Readmissions (PCR)
                $PCR = $this->PlanAllCauseReadmissionsAetnaMedicare($doctor_id, $insurance_id, $clinic_id, $careGap, $gaps_filterYear, $insurancePrvider);

                // spc_disease_gap Statin Therapy for Patients with Cardiovascular Disease: Received Statin Therapy (SPC_STATIN)
                $SPC_STATIN = $this->StatinTherapyPatientsCardiovascularDisease($doctor_id, $insurance_id, $clinic_id, $careGap, $gaps_filterYear, $insurancePrvider);

                // ma_cholesterol_gap Medication Adherence for Cholesterol (Statins): Statins (ADH_STATIN)
                $ADH_STATIN = $this->MedicationAdherenceCholesterolStatinsAetnaMedicare($doctor_id, $insurance_id, $clinic_id, $careGap, $gaps_filterYear, $insurancePrvider);

                //mad_medications_gap Medication Adherence for Diabetes Medications: Diabetes Medications (ADH_DIAB)
                $ADH_DIAB = $this->MedicationAdherenceDiabetesMedicationsAetnaMedicare($doctor_id, $insurance_id, $clinic_id, $careGap, $gaps_filterYear, $insurancePrvider);

                //ma_hypertension_gap  Medication Adherence for Hypertension (ACE or ARB): ACE (ADH_ACE)
                $ADH_ACE = $this->MedicationAdherenceHypertensionAetnaMedicare($doctor_id, $insurance_id, $clinic_id, $careGap, $gaps_filterYear, $insurancePrvider);

                //sup_diabetes_gap Statin Use in Persons with Diabetes (SUPD)
                $SUPD = $this->StatinPersonsDiabetes($doctor_id, $insurance_id, $clinic_id, $careGap, $gaps_filterYear, $insurancePrvider);
                
                //$awv_gap  Annual Wellness Visit
                $AWV = $this->awvCareGapStatus($doctor_id, $insurance_id, $clinic_id, $careGap, $gaps_filterYear, $insurancePrvider);

                $AetnaMedicare = [
                    $BCS,
                    $CCS,
                    $BSPC,
                    $EyeExam,
                    $FMC,
                    $OMW,
                    $PCR,
                    $SPC_STATIN,
                    $ADH_STATIN,
                    $ADH_DIAB,
                    $ADH_ACE,
                    $SUPD,
                    $AWV,
                ];
                $responseData['insurancesName'] = "AetnaMedicare";
                $responseData['aetnaMedicare'] = $AetnaMedicare;
                
            
            //---------------------------- End of Aetna Medicare ---------------------------------
            // Allwell Medicare Start 
            } elseif (!empty($insurance_id) && $insurancePrvider->type_id == 1 && $insurancePrvider->provider == "allwell-001") {
                //$BCS Breast Cancer Screening 
                $BCS = $this->breastCancerScreening($doctor_id, $insurance_id, $clinic_id, $careGap, $gaps_filterYear, $insurancePrvider);

                //$CHBP Controlling High Blood Pressure 
                $CHBP = $this->controllingHighBloodPressure($doctor_id, $insurance_id, $clinic_id, $careGap, $gaps_filterYear, $insurancePrvider);
        
                // $CCS Colorectal Cancer Screening
                $CCS = $this->colorectalCancerGap($doctor_id, $insurance_id, $clinic_id, $careGap, $gaps_filterYear, $insurancePrvider);
        
                //$EyeExam  Diabetes Care - Eye Exam 
                $EyeExam = $this->diabetesEyeExam($doctor_id, $insurance_id, $clinic_id, $careGap, $gaps_filterYear, $insurancePrvider);
            
                // 
                $Pain = $this->olderAdultsPainAssessment($doctor_id, $insurance_id, $clinic_id, $careGap, $gaps_filterYear, $insurancePrvider);
            
                // $BSPC Diabetes Care - Blood Sugar Control (CDC<=9.0%) 
                $BSPC = $this->bloodSugarGap($doctor_id, $insurance_id, $clinic_id, $careGap, $gaps_filterYear, $insurancePrvider);

                // 
                $Review = $this->olderAdultsMedicationReview($doctor_id, $insurance_id, $clinic_id, $careGap, $gaps_filterYear, $insurancePrvider);

                // 
                $HighRisk = $this->multipleHighRiskChronicConditionsAllwellMedicare($doctor_id, $insurance_id, $clinic_id, $careGap, $gaps_filterYear, $insurancePrvider);

                // 
                $ADH_RAS = $this->medAdherenceRAS($doctor_id, $insurance_id, $clinic_id, $careGap, $gaps_filterYear, $insurancePrvider);

                // ma_cholesterol_gap Medication Adherence for Cholesterol (Statins): Statins (ADH_STATIN)
                $ADH_STATIN = $this->medAdherenceStatins($doctor_id, $insurance_id, $clinic_id, $careGap, $gaps_filterYear, $insurancePrvider);

                //mad_medications_gap Medication Adherence for Diabetes Medications: Diabetes Medications (ADH_DIAB)
                $ADH_DIAB = $this->medAdherenceDiabeticAllwellMedicare($doctor_id, $insurance_id, $clinic_id, $careGap, $gaps_filterYear, $insurancePrvider);

                //
                $SPC_STATIN_CVD = $this->SPCStatinTherapyCVDAllwellMedicare($doctor_id, $insurance_id, $clinic_id, $careGap, $gaps_filterYear, $insurancePrvider);

                //sup_diabetes_gap Statin Use in Persons with Diabetes (SUPD)
                $SUPD = $this->StatinPersonsDiabetes($doctor_id, $insurance_id, $clinic_id, $careGap, $gaps_filterYear, $insurancePrvider);

                // 
                $TRC_AD = $this->TRCAfterDischargeAllwellMedicare($doctor_id, $insurance_id, $clinic_id, $careGap, $gaps_filterYear, $insurancePrvider);

                // 
                $TRC_PD = $this->TRCPostDischargeAllwellMedicare($doctor_id, $insurance_id, $clinic_id, $careGap, $gaps_filterYear, $insurancePrvider);

                //
                $KED = $this->KidneyHealth($doctor_id, $insurance_id, $clinic_id, $careGap, $gaps_filterYear, $insurancePrvider);

                //$awv_gap  Annual Wellness Visit
                $AWV = $this->awvCareGapStatus($doctor_id, $insurance_id, $clinic_id, $careGap, $gaps_filterYear, $insurancePrvider);

                $AllwellMedicare = [
                    $BCS,
                    $CCS,
                    $CHBP,
                    $BSPC,
                    $EyeExam,
                    $Pain,
                    $Review,
                    $HighRisk,
                    $ADH_RAS,
                    $ADH_STATIN,
                    $ADH_DIAB,
                    $SPC_STATIN_CVD,
                    $SUPD,
                    $TRC_AD,
                    $TRC_PD,
                    $KED,
                    $AWV,
                ];
                $responseData['insurancesName'] = "AllwellMedicare";
                $responseData['allwellMedicare'] = $AllwellMedicare;
                
            
            //---------------------------- End of Allwell Medicare ---------------------------------
            // Health Choice Arizona Start 
            } elseif (!empty($insurance_id) && $insurancePrvider->type_id == 3 && $insurancePrvider->provider == "hcarz-001") {
                //$BCS Breast Cancer Screening 
                $BCS = $this->breastCancerScreening($doctor_id, $insurance_id, $clinic_id, $careGap, $gaps_filterYear, $insurancePrvider);

                //CCS - Cervical Cancer Screening
                //'cervical_cancer_gap'
                $CCS = $this->CervicalCancerScreening($doctor_id, $insurance_id, $clinic_id, $careGap, $gaps_filterYear, $insurancePrvider);
 
                $HDO = $this->OpioidsHighDosage($doctor_id, $insurance_id, $clinic_id, $careGap, $gaps_filterYear, $insurancePrvider);
 
                $BSPC = $this->bloodSugarPoorGap($doctor_id, $insurance_id, $clinic_id, $careGap, $gaps_filterYear, $insurancePrvider);
 
                //$CHBP Controlling High Blood Pressure 
                $PPC1 = $this->TimelinessPrenatalCare1($doctor_id, $insurance_id, $clinic_id, $careGap, $gaps_filterYear, $insurancePrvider);
                $PPC2 = $this->TimelinessPrenatalCare2($doctor_id, $insurance_id, $clinic_id, $careGap, $gaps_filterYear, $insurancePrvider);
                $WCV = $this->WellChildVisits($doctor_id, $insurance_id, $clinic_id, $careGap, $gaps_filterYear, $insurancePrvider);
                $CS = $this->ChlamydiaScreening($doctor_id, $insurance_id, $clinic_id, $careGap, $gaps_filterYear, $insurancePrvider);
                $CBP = $this->controllingHighBloodPressure($doctor_id, $insurance_id, $clinic_id, $careGap, $gaps_filterYear, $insurancePrvider);
                $FUH_30DAY = $this->fuh_30Day($doctor_id, $insurance_id, $clinic_id, $careGap, $gaps_filterYear, $insurancePrvider);
                $FUH_7DAY = $this->fuh_7Day($doctor_id, $insurance_id, $clinic_id, $careGap, $gaps_filterYear, $insurancePrvider);
                
                //$awv_gap  Annual Wellness Visit
                $AWV = $this->awvCareGapStatus($doctor_id, $insurance_id, $clinic_id, $careGap, $gaps_filterYear, $insurancePrvider);

                $HealthChoiceArizona = [
                    $BCS,
                    $CCS,
                    $HDO,
                    $BSPC,
                    $PPC1,
                    $PPC2,
                    $WCV,
                    $CS,
                    $CBP,
                    $FUH_30DAY,
                    $FUH_7DAY,
                    $AWV,
                ];
                $responseData['insurancesName'] = "HealthChoiceArizona";
                $responseData['healthChoiceArizona'] = $HealthChoiceArizona;
                
            
            //---------------------------- End of Health Choice Arizona ---------------------------------
            // 	United Healthcare - MCR Start 
            } elseif (!empty($insurance_id) && $insurancePrvider->type_id == 1 && $insurancePrvider->provider == "uhc-001") {
                //$BCS Breast Cancer Screening 
                //breast_cancer_gap 1
                $BCS = $this->breastCancerScreening($doctor_id, $insurance_id, $clinic_id, $careGap, $gaps_filterYear, $insurancePrvider);

                // $CCS Colorectal Cancer Screening
                //colorectal_cancer_gap 2
                $CCS = $this->colorectalCancerGap($doctor_id, $insurance_id, $clinic_id, $careGap, $gaps_filterYear, $insurancePrvider);

                //$CHBP Controlling High Blood Pressure
                // high_bp_gap  9
                $CHBP = $this->controllingHighBloodPressure($doctor_id, $insurance_id, $clinic_id, $careGap, $gaps_filterYear, $insurancePrvider);
        
                //$EyeExam  Diabetes Care - Eye Exam 
                //eye_exam_gap 6
                $EyeExam = $this->diabetesEyeExam($doctor_id, $insurance_id, $clinic_id, $careGap, $gaps_filterYear, $insurancePrvider);
            
                // C06-Care for Older Adults - Medication Review
                //adults_medic_gap 3
                $Review = $this->olderAdultsMedicationReview($doctor_id, $insurance_id, $clinic_id, $careGap, $gaps_filterYear, $insurancePrvider);
            
                // C07-Care for Older Adults - Pain Assessment
                //pain_screening_gap 5
                $Pain = $this->olderAdultsPainAssessment($doctor_id, $insurance_id, $clinic_id, $careGap, $gaps_filterYear, $insurancePrvider);

                // DMC10-Care for Older Adults - Functional Status Assessment
                // adults_fun_status_gap 4
                $FunctionalStatus = $this->OlderAdultsFunctionalStatusAssessment($doctor_id, $insurance_id, $clinic_id, $careGap, $gaps_filterYear, $insurancePrvider);

                //C10-Kidney Health Evaluation for Patients With Diabetes 
                //kidney_health_diabetes_gap 7
                $KED = $this->KidneyHealth($doctor_id, $insurance_id, $clinic_id, $careGap, $gaps_filterYear, $insurancePrvider);

                //C11-Hemoglobin A1c Control for Patients With Diabetes
                //'hba1c_gap', 8
                $BSC = $this->bloodSugarGap($doctor_id, $insurance_id, $clinic_id, $careGap, $gaps_filterYear, $insurancePrvider);

                //C16 - Statin Therapy for Patients with Cardiovascular Disease
                //statin_therapy_gap', 10
                $StatinTherapy = $this->StatinTherapyPatientsCardiovascularUHC($doctor_id, $insurance_id, $clinic_id, $careGap, $gaps_filterYear, $insurancePrvider);

                //D08-Med Ad. For Diabetes Meds Current Year Status
                //'med_adherence_diabetes_gap', 11
                $MedAdherence = $this->MedAdherenceDiabetes($doctor_id, $insurance_id, $clinic_id, $careGap, $gaps_filterYear, $insurancePrvider);
                
                //D09-Med Ad. (RAS antagonists) Current Year Status
                // med_adherence_ras_gap 12
                $ADH_RAS = $this->medAdherenceRAS($doctor_id, $insurance_id, $clinic_id, $careGap, $gaps_filterYear, $insurancePrvider);

                //D10-Med Ad. (Statins) Current Year Status
                // med_adherence_statins_gap 13
                $ADH_STATIN = $this->medAdherenceStatins($doctor_id, $insurance_id, $clinic_id, $careGap, $gaps_filterYear, $insurancePrvider);

                //D11-MTM CMR Current Year Status
                //mtm_cmr_gap 14
                $MTM_CMR = $this->MTM_CMR($doctor_id, $insurance_id, $clinic_id, $careGap, $gaps_filterYear, $insurancePrvider);

                //D12-Statin Use in Persons with Diabetes Current Year Status
                //sup_diabetes_gap 15
                $SUPD = $this->StatinPersonsDiabetes($doctor_id, $insurance_id, $clinic_id, $careGap, $gaps_filterYear, $insurancePrvider);
                
                //$awv_gap  Annual Wellness Visit 16
                $AWV = $this->awvCareGapStatus($doctor_id, $insurance_id, $clinic_id, $careGap, $gaps_filterYear, $insurancePrvider);

                $unitedHealthcare = [
                    $BCS,
                    $CCS,
                    $CHBP,
                    $EyeExam,
                    $Review,
                    $Pain,
                    $FunctionalStatus,
                    $KED,
                    $BSC,
                    $StatinTherapy,
                    $MedAdherence,
                    $ADH_RAS,
                    $ADH_STATIN,
                    $MTM_CMR,
                    $SUPD,
                    $AWV,
                ];
                $responseData['insurancesName'] = "United Healthcare";
                $responseData['unitedHealthcare'] = $unitedHealthcare;
                
            //---------------------------- End of 	United Healthcare - MCR ---------------------------------
            }
         
        } catch (\Exception $e) {
            $responseData = array('success'=>false,'message'=>$e->getMessage(), 'line'=>$e->getLine());
        }
        return response()->json($responseData);
    }

    
    
    /**
     * The function `ActiveNonComp` returns the count of Non-Compliant active patients for a given gap
     * name, doctor ID, insurance ID, and clinic ID.
     * 
     * @param gapName The variable "gapName" is a string that represents the name of a specific gap. It
     * is used as a condition in the query to filter the patients based on their compliance status with
     * that particular gap.
     * @param passValue An array containing the values for doctor_id, insurance_id, and clinic_id.
     * These values are used to filter the patients based on their respective IDs.
     * 
     * @return the count of active Non-Compliant patients for a specific care gap.
     */
    private function ActiveNonComp($gapName, $doctor_id, $insurance_id, $clinic_id, $gap_status, $gaps_filterYear)
    {
        $doctor_id = $doctor_id ?? '';
        $insurance_id = $insurance_id ?? '';
        $clinic_id = $clinic_id ?? '';
        // Count of NON Complaint Active Patient of each gap
        $source = $this->insuranceSourceFind($insurance_id, $gap_status);
        if(empty($source)){
            return $response = [
                'success' => false,
                'message' => 'Sorry Data Not Found',
            ];
        }
        $count = Patients::where('patient_year', $gaps_filterYear)
            ->whereHas( $source , function($query) use ($gapName, $gap_status, $gaps_filterYear) {
                $query->thisYearGaps($gaps_filterYear)->when(!empty($gap_status), function ($query) use ($gapName, $gap_status) {
                    $query->where($gapName, $gap_status);
                    if ($gap_status === 'Patient Refused') {
                        $query->orWhere($gapName, 'Patient Refused(Dr Reviewed)');
                    }
                }, function ($query) use ($gapName, $gaps_filterYear, $gap_status) {
                    if ($gapName == "awv_gap" && $gap_status != 'Pending Visit') {
                        // Include NULL values for awv_gap
                        $query->where(function ($query) use ($gapName) {
                            $query->whereNotIn($gapName, ['Completed'])
                                ->orWhereNull($gapName);
                        });
                    } else {
                        $query->whereNotIn($gapName, ['Compliant', 'N/A']);
                    }
            });
        })
        ->when(!empty($doctor_id), function($query) use ($doctor_id){
            $query->where('doctor_id', $doctor_id);
        })->when(!empty($insurance_id), function($query) use ($insurance_id){
            $query->where('insurance_id', $insurance_id);
        })->when(!empty($clinic_id), function($query) use ($clinic_id){
            $query->where('clinic_id', $clinic_id);
        })
        ->whereIn('group', ['1', '2'])->count();
    
        return $count;       
    }

    public function deleteFileFromS3(Request $request)
    {
        $fileName = $request->fileName;
        $bucket = "HumanaCareGaps/";
        $filePath = $bucket . $fileName;
        try {
            if (Storage::disk('s3')->exists($filePath)) {
                Storage::disk('s3')->delete($bucket. $fileName);
                $response = [
                    'success' => true,
                    'message' => 'File deleted successfully'
                ];
            }else{
                $response = [
                    'success' => false,
                    'message' => 'File Not Exist'
                ];
            }
            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function find_patients(Request $request)
    {
        $validator = Validator::make($request->all(), [
           // 'insurance_id'  => 'required',
            'col_name'  => 'required',
            'col_value'  => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->getMessageBag()]);
        };
        $input = $validator->valid();

        $insurance_id = $input['insurance_id'];
        $doctor_id = @$input['doctor_id'] ?? "";
        $filterYear = @$input['filterYear'] ?? "";

        try {
            if($input['col_value'] === 'ClosedPatients'){
                $input['col_value'] = "Compliant";
            } else if ($input['col_value'] === 'OpenPatients') {
                $input['col_value'] = "Non-Compliant";
            }  
            if ($input['col_name'] === 'awv_gap') {
                $input['col_value'] = "Completed";
            }
            

            $col_name = $input['col_name'];
            $col_value = $input['col_value'];
            $insurancePrvider = $this->insuranceNameFind($insurance_id);  
            // Find Models Name for care gaps 
            $CGModelsName = $this->FindModelsName($insurance_id, $insurancePrvider);
            
                $patientData =  $CGModelsName::thisYearGaps($filterYear)->where($col_name, $col_value)
                ->where($col_name.'_insurance', '!=', $col_value)
                ->when(!empty($insurance_id), function ($query) use ($insurance_id) {
                    $query->where('insurance_id', $insurance_id);
                })->when(!empty($doctor_id), function ($query) use ($doctor_id) {
                    $query->where('doctor_id', $doctor_id);
                })
                ->pluck('patient_id')->toArray();
                
        

            $CareGapsDetailsData = array_unique($patientData);
            $CareGapsDetailsData = array_values($CareGapsDetailsData);

            $response = [
                'success' => true,
                'message' => $input['col_value'] .' CareGap Data Found Successfully',
                //'perpage_showdata' =>$per_page,
                'totalRecord'=> count($CareGapsDetailsData),
                'data' => $CareGapsDetailsData,
            ];
        } catch (\Exception $e) {
            $response = array('success'=>false,'message'=>$e->getMessage()); 
        }
        return response()->json($response);
    }
    
    public function find_all_patients(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'col_name'      => 'required',
            'col_value'     => 'required',
            'insurance_id'  => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->getMessageBag()]);
        };

        $input = $validator->valid();

        $filterYear = @$request->filterYear ?? "";

        try {
            $CareGapsDetailsData = [];
            $patientData = [];
            $insurance_id = $input['insurance_id'];
            $doctor_id = @$input['doctor_id'] ?? "";
            $source = $this->insuranceSourceFind($insurance_id, $input['col_value']);

            if(empty($source)){
                return $response = [
                    'success' => false,
                    'message' => 'Sorry Data Not Found',
                ];
            }

            $insurancePrvider= $this->insuranceNameFind($insurance_id);
         
            if ($input['col_value'] === "ActiveNonComp") {
                $gapName = $input['col_name'];
                $notIn = ['Compliant', 'N/A'];
                $awvStatus = ['Completed', 'N/A'];
                $patientData = Patients::whereHas($source, function($query) use ($gapName, $notIn, $awvStatus,  $filterYear) {
                    if ($gapName == "awv_gap" || $gapName == "awv_gap_insurance") {
                        $query->thisYearGaps($filterYear)->whereNotIn($gapName, $awvStatus);
                    } else {
                        $query->thisYearGaps($filterYear)->whereNotIn($gapName, $notIn);
                    }
                })->whereIn('group', ['1', '2'])
                ->when(!empty($doctor_id), function($query) use ($doctor_id){
                    $query->where('doctor_id', $doctor_id);
                })->pluck('id')->toArray();
                //->whereYear('created_at', $gaps_filterYear)
                //return $patientData;
            } else if ($input['col_value'] === "UnScheduled") {
                $gapName = $input['col_name'];

                $awvStatus = ['Completed', 'N/A', 'Pending Visit'];

                $patientData = Patients::whereHas($source, function($query) use ($gapName, $awvStatus,  $filterYear) {
                    if ($gapName == "awv_gap" || $gapName == "awv_gap_insurance") {
                        $query->thisYearGaps($filterYear)->whereNotIn($gapName, $awvStatus);
                    } else {
                        $query->thisYearGaps($filterYear)->where($gapName, 'Non-Compliant');
                    }
                })->whereIn('group', ['1', '2'])
                ->when(!empty($doctor_id), function($query) use ($doctor_id){
                    $query->where('doctor_id', $doctor_id);
                })->pluck('id')->toArray();

                $active_patient = true;
            } else if ($input['col_value'] === "Scheduled") {
                $gapName = $input['col_name'];
                $patientData = Patients::whereHas($source, function($query) use ($gapName, $filterYear) {
                    if ($gapName == "awv_gap" || $gapName == "awv_gap_insurance") {
                        $query->thisYearGaps($filterYear)->where($gapName, 'Pending Visit');
                    } else {
                        $query->thisYearGaps($filterYear)->where($gapName, 'Scheduled');
                    }
                })->whereIn('group', ['1', '2'])
                ->when(!empty($doctor_id), function($query) use ($doctor_id){
                    $query->where('doctor_id', $doctor_id);
                })->pluck('id')->toArray();

                $active_patient = true;
            } else if ($input['col_value'] === "Patient Refused") {
                $gapName = $input['col_name'];

                $patientData = Patients::whereHas($source, function($query) use ($gapName , $filterYear) {
                    if ($gapName == "awv_gap" || $gapName == "awv_gap_insurance") {
                        $query->thisYearGaps($filterYear)->where($gapName, 'Refused');
                    } else {
                        $query->thisYearGaps($filterYear)->where($gapName, 'Patient Refused')
                        ->orWhere($gapName, 'Patient Refused(Dr Reviewed)');
                    }
                })->whereIn('group', ['1', '2'])
                ->when(!empty($doctor_id), function($query) use ($doctor_id){
                    $query->where('doctor_id', $doctor_id);
                })->pluck('id')->toArray();

            } else if($input['col_value'] === 'ClosedPatients') {

                $CGModelsName = $this->FindModelsName($insurance_id, $insurancePrvider);
                
                    if ($input['col_name'] == "awv_gap" || $input['col_name'] == "awv_gap_insurance") {
                        $patientData = $CGModelsName::where($input['col_name'], 'Completed')
                        ->when(!empty($doctor_id), function($query) use ($doctor_id){
                            $query->where('doctor_id', $doctor_id);
                        })->thisYearGaps($filterYear)->pluck('patient_id')->toArray();
                    }else{
                        $patientData = $CGModelsName::where($input['col_name'], 'Compliant')
                        ->when(!empty($doctor_id), function($query) use ($doctor_id){
                            $query->where('doctor_id', $doctor_id);
                        })->thisYearGaps($filterYear)->pluck('patient_id')->toArray();
                    }    
            } else if ($input['col_value'] === 'OpenPatients') {
                // Find Models Name for care gaps
                $CGModelsName = $this->FindModelsName($insurance_id, $insurancePrvider);

                    if ($input['col_name'] == "awv_gap" || $input['col_name'] == "awv_gap_insurance") {
                        $complaint_status = ["Completed"];
                        $patientData = $CGModelsName::thisYearGaps($filterYear)
                            ->where(function ($query) use ($input, $complaint_status) {
                               $query->whereNotIn($input['col_name'], $complaint_status)
                               ->orWhereNull($input['col_name']);
                            })
                            ->when(!empty($doctor_id), function($query) use ($doctor_id){
                                $query->where('doctor_id', $doctor_id);
                            })->pluck('patient_id')->toArray();
                    }else{
                        $complaint_status = ["Compliant", "N/A"];
                        $patientData = $CGModelsName::thisYearGaps($filterYear)
                        ->whereNotIn($input['col_name'], $complaint_status)
                        ->when(!empty($doctor_id), function($query) use ($doctor_id){
                            $query->where('doctor_id', $doctor_id);
                        })->pluck('patient_id')->toArray(); 
                        //->whereYear('created_at', $gaps_filterYear)   
                    }
                  
            }

            $CareGapsDetailsData = !empty($patientData) ? array_unique($patientData) : [];
            $data = [
                'patientData'=>$patientData,
                'CareGapsDetailsData'=>$CareGapsDetailsData,
            ];
            
            $response = [
                'success' => true,
                'message' => 'Overall '. $input['col_value'] .' CareGap Data Found Successfully',
                'totalRecord'=> count($CareGapsDetailsData),
                'data' => $CareGapsDetailsData,
            ];

            } catch (\Exception $e) {
                $response = array('success'=>false,'message'=>$e->getMessage(), 'line'=>$e->getLine()); 
            }
        return response()->json($response);
    }

    private function breastCancerScreening($doctor_id = "" , $insurance_id = "", $clinic_id = "", $careGap , $gaps_filterYear , $insurancePrvider)
    {
        // Find Models Name for care gaps 
        $CGModelsName = $this->FindModelsName($insurance_id, $insurancePrvider);

        $BCS = [];
        $Title = "Breast Cancer Screening (BCS)";
        $column_name = 'breast_cancer_gap';
        $ShortTitle = "BCS";
        
        if ($careGap == 1) {
            $Title = "Breast Cancer Screening (BCS) Insurance";
            $column_name = 'breast_cancer_gap_insurance';
        }

        // As per current status
        // $ClosedPatients = $CGModelsName::thisYearGaps($gaps_filterYear)->with('patientinfo')->whereHas('patientinfo')->where($column_name,'Compliant');
        $ClosedPatients = $CGModelsName::thisYearGaps($gaps_filterYear)->where('insurance_id', $insurance_id)
            ->whereHas('patientinfo', function ($query) use ($insurance_id, $doctor_id, $clinic_id) {
                if (!empty($clinic_id)) {
                    $query->where('clinic_id', $clinic_id);
                }
                if (!empty($doctor_id)) {
                    $query->where('doctor_id', $doctor_id);
                }
        })->where($column_name,'Compliant')->count();

        // $OpenPatients = $CGModelsName::thisYearGaps($gaps_filterYear)->whereNotIn($column_name, ['Compliant', 'N/A']);
        $OpenPatients = $CGModelsName::thisYearGaps($gaps_filterYear)->where('insurance_id', $insurance_id)
            ->whereHas('patientinfo', function ($query) use ($insurance_id, $doctor_id, $clinic_id) {
            // $query->where('insurance_id', $insurance_id);
            
            if (!empty($clinic_id)) {
                $query->where('clinic_id', $clinic_id);
            }

            if (!empty($doctor_id)) {
                $query->where('doctor_id', $doctor_id);
            }
        })->whereNotIn($column_name, ['Compliant', 'N/A'])->count();

        // As per insurance
        $ClosedPatientsCareGapFile = $CGModelsName::thisYearGaps($gaps_filterYear)->with('patientinfo')->whereHas('patientinfo')->where('breast_cancer_gap_insurance','Compliant');
        
        $Refused = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, 'Patient Refused', $gaps_filterYear);
        $Scheduled = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, 'Scheduled', $gaps_filterYear);

        $Total = $CGModelsName::thisYearGaps($gaps_filterYear)->where($column_name, '!=', 'N/A');
        
        if(!empty($doctor_id)){
            // $ClosedPatients->where('doctor_id',$doctor_id);
            $ClosedPatientsCareGapFile->where('doctor_id',$doctor_id);
            // $OpenPatients->where('doctor_id',$doctor_id);
            $Total->where('doctor_id',$doctor_id);
        }
        
        if(!empty($insurance_id)){
            // $ClosedPatients->where('insurance_id',$insurance_id);
            $ClosedPatientsCareGapFile->where('insurance_id',$insurance_id);
            // $OpenPatients->where('insurance_id',$insurance_id);
            $Total->where('insurance_id',$insurance_id);
        }
        
        if(!empty($clinic_id)){
            $clinic_id = explode(',', $clinic_id);
            // $ClosedPatients->where('clinic_id',$clinic_id);
            $ClosedPatientsCareGapFile->where('clinic_id',$clinic_id);
            $Total->where('clinic_id',$clinic_id);
        }

        $ActiveNonComp = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, "", $gaps_filterYear);
        $ClosedPatientsCareGapFile = $ClosedPatientsCareGapFile->count();
        $ClosedPatients = $ClosedPatients;
        $ClosedPatientsDifference = $ClosedPatients - $ClosedPatientsCareGapFile;
        $ClosedPatientsDifference = ($ClosedPatientsDifference>=1 ) ? (string) $ClosedPatientsDifference : '0' ;
        $OpenPatients = $OpenPatients;
        $Refused = $Refused;
        $Scheduled = $Scheduled;
        $UnScheduled = $ActiveNonComp - $Scheduled - $Refused ;
        $UnScheduled = ($UnScheduled>=1 ) ? (string) $UnScheduled : '0' ;
        $Total = $Total->count();
        // $Eligible = $Refused + $Scheduled + $UnScheduled;
        
        if($Total != 0)
        {
            $Acheived = number_format($ClosedPatients * 100 / $Total);
        }else{
            $Acheived = "0";
        }
        $BCSReturn = $this->breastCancerScreeningStarConditions($insurancePrvider, $Acheived);
        $Required_Par = $BCSReturn['Required_Par'];
        $Star = $BCSReturn['Star'];

        $Members_remaining =  ((($Required_Par - $Acheived) * $Total) / 100)  ;  
        if($Members_remaining <= 0 ){
            $Members_remaining = "-";
        }else if($Members_remaining > 0 && $Members_remaining < 1){
            $Members_remaining = 1;
        }else{
            $Members_remaining = number_format($Members_remaining);
        }
        
        $BCS = [
            'Title'                     => $Title,
            'ShortTitle'                => $ShortTitle,
            'ActiveNonComp'             => $ActiveNonComp,
            'ClosedPatients'            => $ClosedPatients,
            'ClosedPatientsDifference'  => $ClosedPatientsDifference,
            'OpenPatients'              => $OpenPatients,
            'Refused'                   => $Refused,
            'Scheduled'                 => $Scheduled,
            'UnScheduled'               => $UnScheduled,
            'Total'                     => $Total,
            'Acheived'                  => $Acheived,
            'Required_Par'              => $Required_Par,
            'Star'                      => $Star,
            'Members_remaining'         => $Members_remaining,
            'db_col_name'               => $column_name,
        ];

        return $BCS;
    }
    
    private function colorectalCancerGap ($doctor_id = "" , $insurance_id = "", $clinic_id = "", $careGap = "", $gaps_filterYear, $insurancePrvider)
    {
        // Find Models Name for care gaps 
        $CGModelsName = $this->FindModelsName($insurance_id, $insurancePrvider);

        $CCS = [];
        $Title = "Colorectal Cancer Screening (COL)";
        $column_name = 'colorectal_cancer_gap';
        $ShortTitle = "COL";
        
        if ($careGap == 1) {
            $Title = "Colorectal Cancer Screening (COL) Insurance";
            $column_name = 'colorectal_cancer_gap_insurance';
        }

        // As per current status
        // $ClosedPatients = $CGModelsName::thisYearGaps($gaps_filterYear)->with('patientinfo')->whereHas('patientinfo')->where($column_name,'Compliant');
        $ClosedPatients = $CGModelsName::thisYearGaps($gaps_filterYear)->where('insurance_id', $insurance_id)
            ->whereHas('patientinfo', function ($query) use ($insurance_id, $doctor_id, $clinic_id) {
                if (!empty($clinic_id)) {
                    $query->where('clinic_id', $clinic_id);
                }
                if (!empty($doctor_id)) {
                    $query->where('doctor_id', $doctor_id);
                }
        })->where($column_name,'Compliant')->count();
        
        // $OpenPatients = $CGModelsName::thisYearGaps($gaps_filterYear)->whereNotIn($column_name, ['Compliant', 'N/A']);
        $OpenPatients = $CGModelsName::thisYearGaps($gaps_filterYear)->where('insurance_id', $insurance_id)
            ->whereHas('patientinfo', function ($query) use ($insurance_id, $doctor_id, $clinic_id) {
            // $query->where('insurance_id', $insurance_id);
            
            if (!empty($clinic_id)) {
                $query->where('clinic_id', $clinic_id);
            }

            if (!empty($doctor_id)) {
                $query->where('doctor_id', $doctor_id);
            }
        })->whereNotIn($column_name, ['Compliant', 'N/A'])->count();
        
        // As per insurance
        $ClosedPatientsCareGapFile = $CGModelsName::thisYearGaps($gaps_filterYear)->with('patientinfo')->whereHas('patientinfo')->where('colorectal_cancer_gap_insurance','Compliant');
        
        $Refused = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, 'Patient Refused', $gaps_filterYear);
        $Scheduled = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, 'Scheduled', $gaps_filterYear);
        
        $Total = $CGModelsName::thisYearGaps($gaps_filterYear)->where($column_name, '!=', ['N/A']);

        if(!empty($doctor_id)){
            $doctor_id = (string) $doctor_id;
            // $ClosedPatientsCareGapFile->where('doctor_id',$doctor_id);
            $ClosedPatients->where('doctor_id',$doctor_id);
            // $OpenPatients->where('doctor_id',$doctor_id); 
            $Total->where('doctor_id',$doctor_id);
            
        }
        if(!empty($insurance_id)){
            $ClosedPatientsCareGapFile->where('insurance_id',$insurance_id);
            // $ClosedPatients->where('insurance_id',$insurance_id);
            // $OpenPatients->where('insurance_id',$insurance_id);
            $Total->where('insurance_id',$insurance_id);
        }
        if(!empty($clinic_id)){
            $clinic_id = explode(',', $clinic_id);
            $ClosedPatientsCareGapFile->where('clinic_id',$clinic_id);
            // $ClosedPatients->where('clinic_id',$clinic_id);
            // $OpenPatients->where('clinic_id',$clinic_id);        
            $Total->where('clinic_id',$clinic_id);
        }

        $ActiveNonComp = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, "", $gaps_filterYear);
        $ClosedPatientsCareGapFile = $ClosedPatientsCareGapFile->count();
        $ClosedPatients = $ClosedPatients;
        $ClosedPatientsDifference = $ClosedPatients - $ClosedPatientsCareGapFile;
        $ClosedPatientsDifference = ($ClosedPatientsDifference>=1 ) ? (string) $ClosedPatientsDifference : '0' ;
        $OpenPatients = $OpenPatients;
        $Refused = $Refused;
        $Scheduled = $Scheduled;
        $UnScheduled = $ActiveNonComp - $Scheduled - $Refused;
        $UnScheduled = ($UnScheduled>=1 ) ? (string) $UnScheduled : '0' ; 
        $Total = $Total->count();
        // $Eligible = $Refused + $Scheduled + $UnScheduled;

        if($Total != 0) {
            $Acheived = number_format( $ClosedPatients * 100 / $Total , 1, '.', '') ;
        } else {
            $Acheived = "0";
        }
        $CCSReturn = $this->colorectalCancerGapStarConditions($insurancePrvider, $Acheived);
        $Required_Par = $CCSReturn['Required_Par'];
        $Star = $CCSReturn['Star'];

        $Members_remaining =  ((($Required_Par - $Acheived) * $Total) / 100)  ;  
        if($Members_remaining <= 0 ){
            $Members_remaining = "-";
        }else if($Members_remaining > 0 && $Members_remaining < 1){
            $Members_remaining = 1;
        }else{
            $Members_remaining = number_format($Members_remaining);
        }

        $CCS = [
            'Title'                     => $Title,
            'ShortTitle'                => $ShortTitle,
            'ActiveNonComp'             => $ActiveNonComp,
            'ClosedPatients'            => $ClosedPatients,
            'ClosedPatientsDifference'  => $ClosedPatientsDifference,
            'OpenPatients'              => $OpenPatients,
            'Refused'                   => $Refused,
            'Scheduled'                 => $Scheduled,
            'UnScheduled'               => $UnScheduled,
            'Total'                     => $Total,
            'Acheived'                  => $Acheived,
            'Required_Par'              => $Required_Par,
            'Star'                      => $Star,
            'Members_remaining'         => $Members_remaining,
            'db_col_name'               => $column_name,
        ];

        return $CCS;
    }

    private function controllingHighBloodPressure($doctor_id = "" , $insurance_id = "", $clinic_id = "", $careGap = "", $gaps_filterYear, $insurancePrvider)
    {
        // Find Models Name for care gaps 
        $CGModelsName = $this->FindModelsName($insurance_id, $insurancePrvider);

        $CHBP = [];
        $Title = "Controlling Blood Pressure (CBP)";
        $column_name = 'high_bp_gap';
        $ShortTitle = "CBP";
        
        if ($careGap == 1) {
            $Title = "Controlling Blood Pressure (CBP) Insurance";
            $column_name = 'high_bp_gap_insurance';
        }
                
        // As per current data
        $ClosedPatients = $CGModelsName::thisYearGaps($gaps_filterYear)->with('patientinfo')->whereHas('patientinfo')->where($column_name,'Compliant');
        $OpenPatients = $CGModelsName::thisYearGaps($gaps_filterYear)->whereNotIn($column_name, ['Compliant', 'N/A']);

        // As per insurance data
        $ClosedPatientsCareGapFile = $CGModelsName::thisYearGaps($gaps_filterYear)->with('patientinfo')->whereHas('patientinfo')->where('high_bp_gap_insurance','Compliant');
        
        $Refused = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, 'Patient Refused', $gaps_filterYear);
        $Scheduled = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, 'Scheduled', $gaps_filterYear);

        $Total = $CGModelsName::thisYearGaps($gaps_filterYear)->where($column_name, '!=', ['N/A']);
    
        if(!empty($doctor_id)){
            $ClosedPatients->where('doctor_id',$doctor_id);
            $ClosedPatientsCareGapFile->where('doctor_id',$doctor_id);
            $OpenPatients->where('doctor_id',$doctor_id);
            $Total->where('doctor_id',$doctor_id);
        }
        if(!empty($insurance_id)){
            $ClosedPatients->where('insurance_id',$insurance_id);
            $OpenPatients->where('insurance_id',$insurance_id);
            $ClosedPatientsCareGapFile->where('insurance_id',$insurance_id);
           
            $Total->where('insurance_id',$insurance_id);
        }
        if(!empty($clinic_id)) {
            $ClosedPatients->where('clinic_id',$clinic_id);
            $ClosedPatientsCareGapFile->where('clinic_id',$clinic_id);
            $OpenPatients->where('clinic_id',$clinic_id);
            $Total->where('clinic_id',$clinic_id);
        }

        $ActiveNonComp = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, "", $gaps_filterYear);
        $ClosedPatientsCareGapFile = $ClosedPatientsCareGapFile->count();
        $ClosedPatients = $ClosedPatients->count();
        $ClosedPatientsDifference = $ClosedPatients - $ClosedPatientsCareGapFile;
        $ClosedPatientsDifference = ($ClosedPatientsDifference>=1 ) ? (string) $ClosedPatientsDifference : '0' ;
        $OpenPatients = $OpenPatients->count();
        $Refused = $Refused;
        $Scheduled = $Scheduled;
        $UnScheduled = $ActiveNonComp - $Scheduled - $Refused ;
        $UnScheduled = ($UnScheduled>=1 ) ? (string) $UnScheduled : '0' ;
        $Total = $Total->count();
        
        if($Total != 0) {
            $Acheived = number_format( $ClosedPatients * 100 / $Total , 2, '.', '') ;
        } else {
            $Acheived = "0";
        }

        $CHBPReturn = $this->controllingHighBloodPressureStarConditions($insurancePrvider, $Acheived);
        $Required_Par = $CHBPReturn['Required_Par'];
        $Star = $CHBPReturn['Star'];
        

        $Members_remaining =  ((($Required_Par - $Acheived) * $Total) / 100)  ;  
        if($Members_remaining <= 0 ){
            $Members_remaining = "-";
        }else if($Members_remaining > 0 && $Members_remaining < 1){
            $Members_remaining = 1;
        }else{
            $Members_remaining = number_format($Members_remaining);
        }

        $CHBP = [
            'Title'                     => $Title,
            'ShortTitle'                => $ShortTitle,
            'ActiveNonComp'             => $ActiveNonComp,
            'ClosedPatients'            => $ClosedPatients,
            'ClosedPatientsDifference'  => $ClosedPatientsDifference,
            'OpenPatients'              => $OpenPatients,
            'Refused'                   => $Refused,
            'Scheduled'                 => $Scheduled,
            'UnScheduled'               => $UnScheduled,
            'Total'                     => $Total,
            'Acheived'                  => $Acheived,
            'Required_Par'              => $Required_Par,
            'Star'                      => $Star,
            'Members_remaining'         => $Members_remaining,
            'db_col_name'               => $column_name
        ];

        return $CHBP; 
    }

    private function statinTherapyGap($doctor_id = "" , $insurance_id = "", $clinic_id = "", $careGap = "", $gaps_filterYear, $insurancePrvider)
    {
        // Find Models Name for care gaps 
        $CGModelsName = $this->FindModelsName($insurance_id, $insurancePrvider);

        $STG = [];
        $Title = "Statin Therapy for Patients with Cardiovascular Disease (SPC)";
        $column_name = 'statin_therapy_gap';
        $ShortTitle = "SPC";
        
        if ($careGap == 1) {
            $Title = "Statin Therapy for Patients with Cardiovascular Disease (SPC) Insurance";
            $column_name = 'statin_therapy_gap_insurance';
        }

        // As per current data
        $ClosedPatients = $CGModelsName::thisYearGaps($gaps_filterYear)->with('patientinfo')->whereHas('patientinfo')->where($column_name,'Compliant');
        $OpenPatients = $CGModelsName::thisYearGaps($gaps_filterYear)->whereNotIn($column_name, ['Compliant', 'N/A']);
        
        // As per insurance
        $ClosedPatientsCareGapFile = $CGModelsName::thisYearGaps($gaps_filterYear)->with('patientinfo')->whereHas('patientinfo')->where('statin_therapy_gap_insurance','Compliant');
        
        $Refused = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, 'Patient Refused', $gaps_filterYear);
        $Scheduled = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, 'Scheduled', $gaps_filterYear);
        
        $Total = $CGModelsName::thisYearGaps($gaps_filterYear)->where($column_name, '!=', 'N/A');
        
        if(!empty($doctor_id)){
            $ClosedPatients->where('doctor_id',$doctor_id);
            $ClosedPatientsCareGapFile->where('doctor_id',$doctor_id);
            $OpenPatients->where('doctor_id',$doctor_id);
            $Total->where('doctor_id',$doctor_id);
        }
        if(!empty($insurance_id)){
            $ClosedPatients->where('insurance_id',$insurance_id);
            $ClosedPatientsCareGapFile->where('insurance_id',$insurance_id);
            $OpenPatients->where('insurance_id',$insurance_id);
            $Total->where('insurance_id',$insurance_id);
        }
        if(!empty($clinic_id)){
            $clinic_id = explode(',', $clinic_id);
            $ClosedPatients->where('clinic_id',$clinic_id);
            $ClosedPatientsCareGapFile->where('clinic_id',$clinic_id);
            $OpenPatients->where('clinic_id',$clinic_id);
            $Total->where('clinic_id',$clinic_id);
        }

        $ActiveNonComp = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, "", $gaps_filterYear);
        $ClosedPatientsCareGapFile = $ClosedPatientsCareGapFile->count();
        $ClosedPatients = $ClosedPatients->count();
        $ClosedPatientsDifference = $ClosedPatients - $ClosedPatientsCareGapFile;
        $ClosedPatientsDifference = ($ClosedPatientsDifference>=1 ) ? (string) $ClosedPatientsDifference : '0' ;
        $OpenPatients = $OpenPatients->count();
        $Refused = $Refused;
        $Scheduled = $Scheduled;
        $UnScheduled = $ActiveNonComp - $Scheduled - $Refused ;
        $UnScheduled = ($UnScheduled>=1 ) ? (string) $UnScheduled : '0' ;
        $Total = $Total->count();

        if($Total != 0) {
            $Acheived = number_format( $ClosedPatients * 100 / $Total , 1, '.', '') ;
        } else {
            $Acheived = "0";
        }

        $STGReturn = $this->statinTherapyGapStarConditions($insurancePrvider, $Acheived);
        $Required_Par = $STGReturn['Required_Par'];
        $Star = $STGReturn['Star'];

        $Members_remaining =  ((($Required_Par - $Acheived) * $Total) / 100)  ;  
        if($Members_remaining <= 0 ){
            $Members_remaining = "-";
        }else if($Members_remaining > 0 && $Members_remaining < 1){
            $Members_remaining = 1;
        }else{
            $Members_remaining = number_format($Members_remaining);
        }

        $STG = [
            'Title'                     => $Title,
            'ShortTitle'                => $ShortTitle,
            'ActiveNonComp'             => $ActiveNonComp,
            'ClosedPatients'            => $ClosedPatients,
            'ClosedPatientsDifference'  => $ClosedPatientsDifference,
            'OpenPatients'              => $OpenPatients,
            'Refused'                   => $Refused,
            'Scheduled'                 => $Scheduled,
            'UnScheduled'               => $UnScheduled,
            'Total'                     => $Total,
            'Acheived'                  => $Acheived,
            'Required_Par'              => $Required_Par,
            'Star'                      => $Star,
            'Members_remaining'         => $Members_remaining,
            'db_col_name'               => $column_name,
        ];

        return $STG;
    }
    
    private function diabetesEyeExam($doctor_id = "" , $insurance_id = "", $clinic_id = "", $careGap = "", $gaps_filterYear, $insurancePrvider)
    {
        // Find Models Name for care gaps 
        $CGModelsName = $this->FindModelsName($insurance_id, $insurancePrvider);

        $EyeExam = [];
        $Title = "Diabetes Care - Eye Exam (EED)";
        $column_name = 'eye_exam_gap';
        $ShortTitle = "EED";
        
        if ($careGap == 1) {
            $Title = "Diabetes Care - Eye Exam (EED) Insurance";
            $column_name = 'eye_exam_gap_insurance';
        }

        // As per current status
        $ClosedPatients = $CGModelsName::thisYearGaps($gaps_filterYear)->with('patientinfo')->whereHas('patientinfo')->where($column_name,'Compliant');
        $OpenPatients = $CGModelsName::thisYearGaps($gaps_filterYear)->whereNotIn($column_name, ['Compliant', 'N/A']);
        
        // As per insurance
        $ClosedPatientsCareGapFile = $CGModelsName::thisYearGaps($gaps_filterYear)->with('patientinfo')->whereHas('patientinfo')->where('eye_exam_gap_insurance','Compliant');
        
        $Refused = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, 'Patient Refused', $gaps_filterYear);
        $Scheduled = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, 'Scheduled', $gaps_filterYear);
        
        $Total = $CGModelsName::thisYearGaps($gaps_filterYear)->where($column_name, '!=', ['N/A']);
        
        if(!empty($doctor_id)){
            $ClosedPatients->where('doctor_id',$doctor_id);
            $OpenPatients->where('doctor_id',$doctor_id);
            $ClosedPatientsCareGapFile->where('doctor_id',$doctor_id);
            $Total->where('doctor_id',$doctor_id);
        }
        if(!empty($insurance_id)){
            $ClosedPatients->where('insurance_id',$insurance_id);
            $ClosedPatientsCareGapFile->where('insurance_id',$insurance_id);
            $OpenPatients->where('insurance_id',$insurance_id);
            $Total->where('insurance_id',$insurance_id);
        }
        if(!empty($clinic_id)){
            $clinic_id = explode(',', $clinic_id);
            $ClosedPatients->where('clinic_id',$clinic_id);
            $ClosedPatientsCareGapFile->where('clinic_id',$clinic_id);
            $OpenPatients->where('clinic_id',$clinic_id);
            $Total->where('clinic_id',$clinic_id);
        }
        $ActiveNonComp = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, "", $gaps_filterYear);
        $ClosedPatientsCareGapFile = $ClosedPatientsCareGapFile->count();
        $ClosedPatients = $ClosedPatients->count();
        $ClosedPatientsDifference = $ClosedPatients - $ClosedPatientsCareGapFile;
        $ClosedPatientsDifference = ($ClosedPatientsDifference>=1 ) ? (string) $ClosedPatientsDifference : '0' ;
        $OpenPatients = $OpenPatients->count();
        $Refused = $Refused;
        $Scheduled = $Scheduled;
        $UnScheduled = $ActiveNonComp - $Scheduled - $Refused ;
        $UnScheduled = ($UnScheduled>=1 ) ? (string) $UnScheduled : '0' ;
        $Total = $Total->count();
        // $Eligible = $Refused + $Scheduled + $UnScheduled;
        
        if($Total != 0) {
            $Acheived = number_format( $ClosedPatients * 100 / $Total , 2, '.', '') ;
        }else{
            $Acheived = "0";
        }
        
        $EEDReturn = $this->diabetesEyeExamStarConditions($insurancePrvider, $Acheived);
        $Required_Par = $EEDReturn['Required_Par'];
        $Star = $EEDReturn['Star'];

        $Members_remaining =  ((($Required_Par - $Acheived) * $Total) / 100)  ;  
        if($Members_remaining <= 0 ){
            $Members_remaining = "-";
        }else if($Members_remaining > 0 && $Members_remaining < 1){
            $Members_remaining = 1;
        }else{
            $Members_remaining = number_format($Members_remaining);
        }

        $EyeExam = [
            'Title'                     => $Title,
            'ShortTitle'                => $ShortTitle,
            'ActiveNonComp'             => $ActiveNonComp,
            'ClosedPatients'            => $ClosedPatients,
            'ClosedPatientsDifference'  => $ClosedPatientsDifference,
            'OpenPatients'              => $OpenPatients,
            'Refused'                   => $Refused,
            'Scheduled'                 => $Scheduled,
            'UnScheduled'               => $UnScheduled,
            'Total'                     => $Total,
            'Acheived'                  => $Acheived,
            'Required_Par'              => $Required_Par,
            'Star'                      => $Star,
            'Members_remaining'         => $Members_remaining,
            'db_col_name'               => $column_name,
        ];
        return $EyeExam;
    }

    private function bloodSugarPoorGap($doctor_id = "" , $insurance_id = "", $clinic_id = "", $careGap = "", $gaps_filterYear, $insurancePrvider)
    {
        // Find Models Name for care gaps 
        $CGModelsName = $this->FindModelsName($insurance_id, $insurancePrvider);

        $BSPC = [];
        $Title = "Diabetes Care - Blood Sugar Control (>9%) (HBD_HBAPOOR)";
        $column_name = 'hba1c_poor_gap';
        $ShortTitle = "HBAPOOR";
        
        if ($careGap == 1) {
            $Title = "Diabetes Care - Blood Sugar Control (>9%) (HBD_HBAPOOR) ";
            $column_name = 'hba1c_poor_gap_insurance';
        }

        // As per current status
        $ClosedPatients = $CGModelsName::thisYearGaps($gaps_filterYear)->with('patientinfo')->whereHas('patientinfo')->where($column_name,'Compliant');
        $OpenPatients = $CGModelsName::thisYearGaps($gaps_filterYear)->whereNotIn($column_name, ['Compliant', 'N/A']);

        // As per insurance
        $ClosedPatientsCareGapFile = $CGModelsName::thisYearGaps($gaps_filterYear)->with('patientinfo')->whereHas('patientinfo')->where('hba1c_poor_gap_insurance','Compliant');

        $Refused = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, 'Patient Refused', $gaps_filterYear);
        $Scheduled = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, 'Scheduled', $gaps_filterYear);
        
        $Total = $CGModelsName::thisYearGaps($gaps_filterYear)->where($column_name, '!=', ['N/A']);
        
        if(!empty($doctor_id)){
            $ClosedPatients->where('doctor_id',$doctor_id);
            $ClosedPatientsCareGapFile->where('doctor_id',$doctor_id);
            $OpenPatients->where('doctor_id',$doctor_id);
            $Total->where('doctor_id',$doctor_id);
        }
        if(!empty($insurance_id)){
            $ClosedPatients->where('insurance_id',$insurance_id);
            $ClosedPatientsCareGapFile->where('insurance_id',$insurance_id);
            $OpenPatients->where('insurance_id',$insurance_id);
            $Total->where('insurance_id',$insurance_id);
        }
        if(!empty($clinic_id)){
            $clinic_id = explode(',', $clinic_id);
            $ClosedPatients->where('clinic_id',$clinic_id);
            $ClosedPatientsCareGapFile->where('clinic_id',$clinic_id);
            $OpenPatients->where('clinic_id',$clinic_id);
            $Total->where('clinic_id',$clinic_id);
        }
        $ActiveNonComp = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, "", $gaps_filterYear);
        $ClosedPatientsCareGapFile = $ClosedPatientsCareGapFile->count();
        $ClosedPatients = $ClosedPatients->count();
        $ClosedPatientsDifference = $ClosedPatients - $ClosedPatientsCareGapFile;
        $ClosedPatientsDifference = ($ClosedPatientsDifference>=1 ) ? (string) $ClosedPatientsDifference : '0' ;
        $OpenPatients = $OpenPatients->count();
        $Refused = $Refused;
        $Scheduled = $Scheduled;
        $UnScheduled = $ActiveNonComp - $Scheduled - $Refused ;
        $UnScheduled = ($UnScheduled>=1 ) ? (string) $UnScheduled : '0' ;
        $Total = $Total->count();
        // $Eligible = $Refused + $Scheduled + $UnScheduled;

        if($Total != 0) {
            $Acheived = number_format( $ClosedPatients * 100 / $Total , 1, '.', '') ;
        }else{
            $Acheived = "0";
        }

        $BSPCReturn = $this->bloodSugarPoorGapStarConditions($insurancePrvider, $Acheived);
        $Required_Par = $BSPCReturn['Required_Par'];
        $Star = $BSPCReturn['Star'];

        $Members_remaining =  ((($Required_Par - $Acheived) * $Total) / 100)  ;  
        if($Members_remaining <= 0 ){
            $Members_remaining = "-";
        }else if($Members_remaining > 0 && $Members_remaining < 1){
            $Members_remaining = 1;
        }else{
            $Members_remaining = number_format($Members_remaining);
        }

        $BSPC = [
            'Title'                     => $Title,
            'ShortTitle'                => $ShortTitle,
            'ActiveNonComp'             => $ActiveNonComp,
            'ClosedPatients'            => $ClosedPatients,
            'ClosedPatientsDifference'  => $ClosedPatientsDifference,
            'OpenPatients'              => $OpenPatients,
            'Refused'                   => $Refused,
            'Scheduled'                 => $Scheduled,
            'UnScheduled'               => $UnScheduled,
            'Total'                     => $Total,
            'Acheived'                  => $Acheived,
            'Required_Par'              => $Required_Par,
            'Star'                      => $Star,
            'Members_remaining'         => $Members_remaining,
            'db_col_name'               => $column_name,
        ];
        
        return $BSPC;
    }

    private function osteoporosisMgmtGap($doctor_id = "" , $insurance_id = "", $clinic_id = "", $careGap = "", $gaps_filterYear, $insurancePrvider)
    {
        $OMG = [];
        $Title = "Osteoporosis Mgmt in Women who had Fracture (OMW)";
        $column_name = 'osteoporosis_mgmt_gap';
        $ShortTitle = "OMW";
        
        if ($careGap == 1) {
            $Title = "Osteoporosis Mgmt in Women who had Fracture (OMW) Insurance";
            $column_name = 'osteoporosis_mgmt_gap_insurance';
        }

        // As per current staus
        $ClosedPatients = CareGaps::thisYearGaps($gaps_filterYear)->with('patientinfo')->whereHas('patientinfo')->where($column_name,'Compliant');
        $OpenPatients = CareGaps::thisYearGaps($gaps_filterYear)->whereNotIn($column_name, ['Compliant', 'N/A']);

        // As per insurance 
        $ClosedPatientsCareGapFile = CareGaps::thisYearGaps($gaps_filterYear)->with('patientinfo')->whereHas('patientinfo')->where('osteoporosis_mgmt_gap_insurance','Compliant');
        
        $Refused = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, 'Patient Refused', $gaps_filterYear);
        $Scheduled = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, 'Scheduled', $gaps_filterYear);
        
        $Total = CareGaps::thisYearGaps($gaps_filterYear)->where($column_name, '!=', ['N/A']);
        
        if(!empty($doctor_id)){
            $ClosedPatients->where('doctor_id',$doctor_id);
            $OpenPatients->where('doctor_id',$doctor_id);
            $ClosedPatientsCareGapFile->where('doctor_id',$doctor_id);
            $Total->where('doctor_id',$doctor_id);
        }
        if(!empty($insurance_id)){
            $ClosedPatients->where('insurance_id',$insurance_id);
            $ClosedPatientsCareGapFile->where('insurance_id',$insurance_id);
            $OpenPatients->where('insurance_id',$insurance_id);
            $Total->where('insurance_id',$insurance_id);
        }
        if(!empty($clinic_id)){
            $clinic_id = explode(',', $clinic_id);
            $ClosedPatients->where('clinic_id',$clinic_id);
            $ClosedPatientsCareGapFile->whereIn('clinic_id',$clinic_id);
            $OpenPatients->whereIn('clinic_id',$clinic_id);
            $Total->whereIn('clinic_id',$clinic_id);
        }
        $ActiveNonComp = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, "", $gaps_filterYear);
        $ClosedPatientsCareGapFile = $ClosedPatientsCareGapFile->count();
        $ClosedPatients = $ClosedPatients->count();
        $ClosedPatientsDifference = $ClosedPatients - $ClosedPatientsCareGapFile;
        $ClosedPatientsDifference = ($ClosedPatientsDifference>=1 ) ? (string) $ClosedPatientsDifference : '0' ;
        $OpenPatients = $OpenPatients->count();
        $Refused = $Refused;
        $Scheduled = $Scheduled;
        $UnScheduled = $ActiveNonComp - $Scheduled - $Refused ;
        $UnScheduled = ($UnScheduled>=1 ) ? (string) $UnScheduled : '0' ;
        $Total = $Total->count();
        // $Eligible = $Refused + $Scheduled + $UnScheduled;
        
        if($Total != 0) {
            $Acheived = number_format( $ClosedPatients * 100 / $Total , 1, '.', '') ;
        } else {
            $Acheived = "0";
        }

        // return $Acheived;
        $Required_Par = "60" ;  

        if( $Acheived  >= "60") {
            $Star = "4";
        } else if( $Acheived >= "59") {
            $Star = "3";
        } else if( $Acheived >= "58") {
            $Star = "2";
        } else if( $Acheived < "58") {
            $Star = "1";
        }

        $Members_remaining =  ((($Required_Par - $Acheived) * $Total) / 100)  ;  
        if($Members_remaining <= 0 ){
            $Members_remaining = "-";
        }else if($Members_remaining > 0 && $Members_remaining < 1){
            $Members_remaining = 1;
        }else{
            $Members_remaining = number_format($Members_remaining);
        }

        $OMG = [
            'Title'                     => $Title,
            'ShortTitle'                => $ShortTitle,
            'ActiveNonComp'             => $ActiveNonComp,
            'ClosedPatients'            => $ClosedPatients,
            'ClosedPatientsDifference'  => $ClosedPatientsDifference,
            'OpenPatients'              => $OpenPatients,
            'Refused'                   => $Refused,
            'Scheduled'                 => $Scheduled,
            'UnScheduled'               => $UnScheduled,
            'Total'                     => $Total,
            'Acheived'                  => $Acheived,
            'Required_Par'              => $Required_Par,
            'Members_remaining'         => $Members_remaining,
            'Star'                      => $Star,
            'db_col_name'               => $column_name
        ];

        return $OMG;
    }

    private function olderAdultsMedicationReview($doctor_id = "" , $insurance_id = "", $clinic_id = "", $careGap = "", $gaps_filterYear, $insurancePrvider)
    {
        // Find Models Name for care gaps 
        $CGModelsName = $this->FindModelsName($insurance_id, $insurancePrvider);

        $COA2 = [];
        $Title = "Care for Older Adults - Medication Review (COA2)";
        $ShortTitle = "COA2";
        $column_name = 'adults_medic_gap';
        
        if ($careGap == 1) {
            $Title = "Care for Older Adults - Medication Review (COA2) Insurance";
            $column_name = 'adults_medic_gap_insurance';
        }

        // As per current status
        $ClosedPatients = $CGModelsName::thisYearGaps($gaps_filterYear)->with('patientinfo')->whereHas('patientinfo')->where($column_name,'Compliant');
        $OpenPatients = $CGModelsName::thisYearGaps($gaps_filterYear)->whereNotIn($column_name, ['Compliant', 'N/A']);
        
        // As per insurance
        $ClosedPatientsCareGapFile = $CGModelsName::thisYearGaps($gaps_filterYear)->with('patientinfo')->whereHas('patientinfo')->where('adults_medic_gap_insurance','Compliant');
        
        $Refused = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, 'Patient Refused', $gaps_filterYear);
        $Scheduled = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, 'Scheduled', $gaps_filterYear);
        
        $Total = $CGModelsName::thisYearGaps($gaps_filterYear)->where($column_name, '!=', ['N/A']);
    
        if(!empty($doctor_id)){
            $ClosedPatients->where('doctor_id',$doctor_id);
            $ClosedPatientsCareGapFile->where('doctor_id',$doctor_id);
            $OpenPatients->where('doctor_id',$doctor_id);
            $Total->where('doctor_id',$doctor_id);
        }
        if(!empty($insurance_id)){
            $ClosedPatients->where('insurance_id',$insurance_id);
            $ClosedPatientsCareGapFile->where('insurance_id',$insurance_id);
            $OpenPatients->where('insurance_id',$insurance_id);
            $Total->where('insurance_id',$insurance_id);
        }
        if(!empty($clinic_id)){
            $ClosedPatients->where('clinic_id',$clinic_id);
            $ClosedPatientsCareGapFile->where('clinic_id',$clinic_id);
            $OpenPatients->where('clinic_id',$clinic_id);
            $Total->where('clinic_id',$clinic_id);
        }
        $ActiveNonComp = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, "", $gaps_filterYear);
        $ClosedPatientsCareGapFile = $ClosedPatientsCareGapFile->count();
        $ClosedPatients = $ClosedPatients->count();
        $ClosedPatientsDifference = $ClosedPatients - $ClosedPatientsCareGapFile;
        $ClosedPatientsDifference = ($ClosedPatientsDifference>=1 ) ? (string) $ClosedPatientsDifference : '0' ;
        $OpenPatients = $OpenPatients->count();
        $Refused = $Refused;
        $Scheduled = $Scheduled;
        $UnScheduled = $ActiveNonComp - $Scheduled - $Refused ;
        $UnScheduled = ($UnScheduled>=1 ) ? (string) $UnScheduled : '0' ;
        $Total = $Total->count();
        // $Eligible = $Refused + $Scheduled + $UnScheduled;
        
        if($Total != 0) {
            $Acheived = number_format( $ClosedPatients * 100 / $Total , 1, '.', '') ;
        }else{
            $Acheived = "0";
        }

        

        $ReviewReturn = $this->olderAdultsMedicationReviewStarConditions($insurancePrvider, $Acheived);
        $Required_Par = $ReviewReturn['Required_Par'];
        $Star = $ReviewReturn['Star'];

        $Members_remaining =  ((($Required_Par - $Acheived) * $Total) / 100)  ;  
        if($Members_remaining <= 0 ){
            $Members_remaining = "-";
        }else if($Members_remaining > 0 && $Members_remaining < 1){
            $Members_remaining = 1;
        }else{
            $Members_remaining = number_format($Members_remaining);
        }

        // End Care for Older Adults - Medication Review (COA2)  =============================> COA2  <===========================

        $COA2 = [
            'Title'                     => $Title,
            'ShortTitle'                => $ShortTitle,
            'ActiveNonComp'             => $ActiveNonComp,
            'ClosedPatients'            => $ClosedPatients,
            'ClosedPatientsDifference'  => $ClosedPatientsDifference,
            'OpenPatients'              => $OpenPatients,
            'Refused'                   => $Refused,
            'Scheduled'                 => $Scheduled,
            'UnScheduled'               => $UnScheduled,
            'Total'                     => $Total,
            'Acheived'                  => $Acheived,
            'Required_Par'              => $Required_Par,
            'Members_remaining'         => $Members_remaining,
            'Star'                      => $Star,
            'db_col_name'               => $column_name,
        ];

        return $COA2;
    }

    private function olderAdultsPainAssessment($doctor_id = "" , $insurance_id = "", $clinic_id = "", $careGap = "", $gaps_filterYear, $insurancePrvider)
    {
        
        // Find Models Name for care gaps 
        $CGModelsName = $this->FindModelsName($insurance_id, $insurancePrvider);

        $COA4 = [];
        $Title = "Care for Older Adults - Pain Assessment (COA4)";
        $column_name = 'pain_screening_gap';
        $ShortTitle = "COA4";
        
        if ($careGap == 1) {
            $Title = "Care for Older Adults - Pain Assessment (COA4) Insurance";
            $column_name = 'pain_screening_gap_insurance';
        }

        // As per current status
        $ClosedPatients = $CGModelsName::thisYearGaps($gaps_filterYear)->with('patientinfo')->whereHas('patientinfo')->where($column_name,'Compliant');
        $OpenPatients = $CGModelsName::thisYearGaps($gaps_filterYear)->whereNotIn($column_name, ['Compliant', 'N/A']);
        
        // As per insurance
        $ClosedPatientsCareGapFile = $CGModelsName::thisYearGaps($gaps_filterYear)->with('patientinfo')->whereHas('patientinfo')->where('pain_screening_gap_insurance','Compliant');
        
        $Refused = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, 'Patient Refused', $gaps_filterYear);
        $Scheduled = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, 'Scheduled', $gaps_filterYear);
        
        $Total = $CGModelsName::thisYearGaps($gaps_filterYear)->where($column_name, '!=', ['N/A']);
    
        if(!empty($doctor_id)){
            $ClosedPatients->where('doctor_id',$doctor_id);
            $ClosedPatientsCareGapFile->where('doctor_id',$doctor_id);
            $OpenPatients->where('doctor_id',$doctor_id);
            $Total->where('doctor_id',$doctor_id);
        }
        if(!empty($insurance_id)){
            $ClosedPatients->where('insurance_id',$insurance_id);
            $ClosedPatientsCareGapFile->where('insurance_id',$insurance_id);
            $OpenPatients->where('insurance_id',$insurance_id);
            $Total->where('insurance_id',$insurance_id);
        }
        if(!empty($clinic_id)){
            $ClosedPatients->where('clinic_id',$clinic_id);
            $ClosedPatientsCareGapFile->where('clinic_id',$clinic_id);
            $OpenPatients->where('clinic_id',$clinic_id);
            $Total->where('clinic_id',$clinic_id);
        }
        $ActiveNonComp = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, "", $gaps_filterYear);
        $ClosedPatientsCareGapFile = $ClosedPatientsCareGapFile->count();
        $ClosedPatients = $ClosedPatients->count();
        $ClosedPatientsDifference = $ClosedPatients - $ClosedPatientsCareGapFile;
        $ClosedPatientsDifference = ($ClosedPatientsDifference>=1 ) ? (string) $ClosedPatientsDifference : '0' ;
        $OpenPatients = $OpenPatients->count();
        $Refused = $Refused;
        $Scheduled = $Scheduled;
        $UnScheduled = $ActiveNonComp - $Scheduled - $Refused ;
        $UnScheduled = ($UnScheduled>=1 ) ? (string) $UnScheduled : '0' ;
        $Total = $Total->count();
        // $Eligible = $Refused + $Scheduled + $UnScheduled;
        
        if($Total != 0) {
            $Acheived = number_format( $ClosedPatients * 100 / $Total , 1, '.', '') ;
        }else{
            $Acheived = "0";
        }

        $COA4Return = $this->olderAdultsPainAssessmentStarConditions($insurancePrvider, $Acheived);
        $Required_Par = $COA4Return['Required_Par'];
        $Star = $COA4Return['Star'];

        $Members_remaining =  ((($Required_Par - $Acheived) * $Total) / 100)  ;  
        if($Members_remaining <= 0 ){
            $Members_remaining = "-";
        }else if($Members_remaining > 0 && $Members_remaining < 1){
            $Members_remaining = 1;
        }else{
            $Members_remaining = number_format($Members_remaining);
        }

        // End Care for Older Adults - Medication Review (COA2)  =============================> COA2  <===========================

        $COA4 = [
            'Title'                     => $Title,
            'ShortTitle'                => $ShortTitle,
            'ActiveNonComp'             => $ActiveNonComp,
            'ClosedPatients'            => $ClosedPatients,
            'ClosedPatientsDifference'  => $ClosedPatientsDifference,
            'OpenPatients'              => $OpenPatients,
            'Refused'                   => $Refused,
            'Scheduled'                 => $Scheduled,
            'UnScheduled'               => $UnScheduled,
            'Total'                     => $Total,
            'Acheived'                  => $Acheived,
            'Required_Par'              => $Required_Par,
            'Star'                      => $Star,
            'db_col_name'               => $column_name
        ];

        return $COA4;
    }

    private function medicalReconciliationPostDischarge($doctor_id = "" , $insurance_id = "", $clinic_id = "", $careGap = "", $gaps_filterYear, $insurancePrvider)
    {
        $TRCM = [];
        $Title = "Transitions of Care-Medication Reconciliation Post-Discharge (TRCM)";
        $column_name = 'post_disc_gap';
        $ShortTitle = "TRCM";
        
        if ($careGap == 1) {
            $Title = "Transitions of Care-Medication Reconciliation Post-Discharge (TRCM) Insurance";
            $column_name = 'post_disc_gap_insurance';
        }

        // As per current status
        $ClosedPatients = CareGaps::thisYearGaps($gaps_filterYear)->with('patientinfo')->whereHas('patientinfo')->where($column_name,'Compliant');
        $OpenPatients = CareGaps::thisYearGaps($gaps_filterYear)->whereNotIn($column_name, ['Compliant', 'N/A']);
        
        // As per insurance
        $ClosedPatientsCareGapFile = CareGaps::thisYearGaps($gaps_filterYear)->with('patientinfo')->whereHas('patientinfo')->where('post_disc_gap_insurance','Compliant');
        
        $Refused = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, 'Patient Refused',$gaps_filterYear);
        $Scheduled = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, 'Scheduled',$gaps_filterYear);
        
        $Total = CareGaps::thisYearGaps($gaps_filterYear)->where($column_name, '!=', ['N/A']);
    
        if(!empty($doctor_id)){
            $ClosedPatients->where('doctor_id',$doctor_id);
            $ClosedPatientsCareGapFile->where('doctor_id',$doctor_id);
            $OpenPatients->where('doctor_id',$doctor_id);
            $Total->where('doctor_id',$doctor_id);
        }
        if(!empty($insurance_id)){
            $ClosedPatients->where('insurance_id',$insurance_id);
            $ClosedPatientsCareGapFile->where('insurance_id',$insurance_id);
            $OpenPatients->where('insurance_id',$insurance_id);
            $Total->where('insurance_id',$insurance_id);
        }
        if(!empty($clinic_id)){
            $ClosedPatients->where('clinic_id',$clinic_id);
            $ClosedPatientsCareGapFile->where('clinic_id',$clinic_id);
            $OpenPatients->where('clinic_id',$clinic_id);
            $Total->where('clinic_id',$clinic_id);
        }
        $ActiveNonComp = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, "",$gaps_filterYear);
        $ClosedPatientsCareGapFile = $ClosedPatientsCareGapFile->count();
        $ClosedPatients = $ClosedPatients->count();
        $ClosedPatientsDifference = $ClosedPatients - $ClosedPatientsCareGapFile;
        $ClosedPatientsDifference = ($ClosedPatientsDifference>=1 ) ? (string) $ClosedPatientsDifference : '0' ;
        $OpenPatients = $OpenPatients->count();
        $Refused = $Refused;
        $Scheduled = $Scheduled;
        $UnScheduled = $ActiveNonComp - $Scheduled - $Refused ;
        $UnScheduled = ($UnScheduled>=1 ) ? (string) $UnScheduled : '0' ;
        $Total = $Total->count();
        // $Eligible = $Refused + $Scheduled + $UnScheduled;
        
        if($Total != 0){
            $Acheived = number_format( $ClosedPatients * 100 / $Total , 1, '.', '') ;
        }else{
            $Acheived = "0";
        }

        $Required_Par =  "70";
       
        if($Acheived >= "70"){
            $Star = "4";
        }else if($Acheived >= "69"){
            $Star = "3";
        }else if($Acheived >= "68"){
            $Star = "2";
        }else if($Acheived < "68"){
            $Star = "1";
        }

        $Members_remaining =  ((($Required_Par - $Acheived) * $Total) / 100)  ;  
        if($Members_remaining <= 0 ){
            $Members_remaining = "-";
        }else if($Members_remaining > 0 && $Members_remaining < 1){
            $Members_remaining = 1;
        }else{
            $Members_remaining = number_format($Members_remaining);
        }

        // End Care for Older Adults - Medication Review (COA2)  =============================> COA2  <===========================

        $TRCM = [
            'Title'                     => $Title,
            'ShortTitle'                => $ShortTitle,
            'ActiveNonComp'             => $ActiveNonComp,
            'ClosedPatients'            => $ClosedPatients,
            'ClosedPatientsDifference'  => $ClosedPatientsDifference,
            'OpenPatients'              => $OpenPatients,
            'Refused'                   => $Refused,
            'Scheduled'                 => $Scheduled,
            'UnScheduled'               => $UnScheduled,
            'Total'                     => $Total,
            'Acheived'                  => $Acheived,
            'Required_Par'              => $Required_Par,
            'Members_remaining'         => $Members_remaining,
            'Star'                      => $Star,
            'db_col_name'               => $column_name,
        ];

        return $TRCM;
    }

    private function awvCareGapStatus($doctor_id = "" , $insurance_id = "", $clinic_id = "", $careGap = "", $gaps_filterYear, $insurancePrvider)
    {
        // Find Models Name for care gaps 
        $CGModelsName = $this->FindModelsName($insurance_id, $insurancePrvider);

        if (!empty($CGModelsName)) {
            $AWV = [];
           
            $activePatients = Patients::whereIn('group', ['1', '2'])->count();
            $Title = "Annual Wellness Visit";
            $column_name = 'awv_gap';
            $ShortTitle = "AWV";
            
            if ($careGap == 1) {
                $Title = "Annual Wellness Visit Insurance";
                $column_name = 'awv_gap_insurance';
            }
    
            // As per current status
            $ClosedPatients = $CGModelsName::thisYearGaps($gaps_filterYear)->where('insurance_id', $insurance_id)
                ->whereHas('patientinfo', function ($query) use ($insurance_id, $doctor_id, $clinic_id, $gaps_filterYear) {
                // $query->where('insurance_id', $insurance_id);
                $query->where('status', '1')->orWhereHas('statusLogs', function ($query) use ($gaps_filterYear) {
                    $query->where('status', 1)->where('patient_year', $gaps_filterYear);
                });
                
                if (!empty($clinic_id)) {
                    $query->where('clinic_id', $clinic_id);
                }

                if (!empty($doctor_id)) {
                    $query->where('doctor_id', $doctor_id);
                }
            })->where($column_name,'Completed')->count();

            $OpenPatients = $CGModelsName::thisYearGaps($gaps_filterYear)
            ->whereHas('patientinfo', function ($query) use ($insurance_id, $doctor_id, $clinic_id) {
                if (!empty($clinic_id)) {
                    $query->where('clinic_id', $clinic_id);
                }

                if (!empty($doctor_id)) {
                    $query->where('doctor_id', $doctor_id);
                }
            })
            ->where(function ($query) use ($column_name) {
                $query->whereNull($column_name)
                    ->orWhere($column_name, '!=', 'Completed');
            })
            ->count();
            
            // As per insurance
            $ClosedPatientsCareGapFile = $CGModelsName::thisYearGaps($gaps_filterYear)->where(['insurance_id' => $insurance_id])
            ->whereHas('patientinfo', function ($query) use ($insurance_id, $doctor_id, $clinic_id){
                // $query->where('insurance_id', $insurance_id);
                $query->where('status', '1');
                
                if (!empty($clinic_id)) {
                    $query->where('clinic_id', $clinic_id);
                }

                if (!empty($doctor_id)) {
                    $query->where('doctor_id', $doctor_id);
                }
            })->where('awv_gap_insurance','Completed')->count();
            
            $Refused = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, 'Refused', $gaps_filterYear);
            $Scheduled = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, 'Pending Visit', $gaps_filterYear);
            
            $Total = $CGModelsName::thisYearGaps($gaps_filterYear)
            ->when(!empty($doctor_id), function ($query) use ($doctor_id) {
                $query->where('doctor_id', $doctor_id);
            })
            ->when(!empty($clinic_id), function ($query) use ($doctor_id) {
                $query->where('clinic_id', $clinic_id);
            })->count();
            
            $ActiveNonComp = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, "", $gaps_filterYear);
            $ClosedPatientsCareGapFile = $ClosedPatientsCareGapFile;
            $ClosedPatients = $ClosedPatients;
            $ClosedPatientsDifference = $ClosedPatients - $ClosedPatientsCareGapFile;
            $ClosedPatientsDifference = ($ClosedPatientsDifference>=1 ) ? (string) $ClosedPatientsDifference : '0' ;
            $OpenPatients = $OpenPatients;
            $Refused = $Refused;
            $Scheduled = $Scheduled;
            $UnScheduled = $ActiveNonComp - $Scheduled - $Refused ;
            $UnScheduled = ($UnScheduled>=1 ) ? (string) $UnScheduled : '0' ;
            $Total = $Total;
            // $Eligible = $Refused + $Scheduled + $UnScheduled;
            
            if($Total != 0){
                $Acheived = number_format( $ClosedPatients * 100 / $Total , 1, '.', '') ;
            }else{
                $Acheived = "0";
            }
    
            $Required_Par =  "80";//(int)$activePatients * 80 /100;
           
            if($Acheived >= "70"){
                $Star = "4";
            }else if($Acheived >= "65"){
                $Star = "3";
            }else if($Acheived >= "60"){
                $Star = "2";
            }else if($Acheived < "60"){
                $Star = "1";
            }            
    
            $Members_remaining =  ((($Required_Par - $Acheived) * $Total) / 100)  ;  
            if($Members_remaining <= 0 ){
                $Members_remaining = "-";
            }else if($Members_remaining > 0 && $Members_remaining < 1){
                $Members_remaining = 1;
            }else{
                $Members_remaining = number_format($Members_remaining);
            }
    
            // Annual Wellness Visit  =============================> AWV  <===========================
    
            $AWV = [
                'Title'                     => $Title,
                'ShortTitle'                => $ShortTitle,
                'ActiveNonComp'             => $ActiveNonComp,
                'ClosedPatients'            => $ClosedPatients,
                'ClosedPatientsDifference'  => $ClosedPatientsDifference,
                'OpenPatients'              => $OpenPatients,
                'Refused'                   => $Refused,
                'Scheduled'                 => $Scheduled,
                'UnScheduled'               => $UnScheduled,
                'Total'                     => $Total,
                'Acheived'                  => $Acheived,
                'Required_Par'              => $Required_Par,
                'Members_remaining'         => $Members_remaining,
                'Star'                      => $Star,
                'db_col_name'               => $column_name,
            ];
    
            return $AWV;
        }

    }

    
    // ========================================  End HCP ======================================= 
    
    
    private function FollowUpAfterEmergencyDepartmentVisit($doctor_id = "" , $insurance_id = "", $clinic_id = "", $careGap = "", $gaps_filterYear, $insurancePrvider)
    {
        // Find Models Name for care gaps 
        $CGModelsName = $this->FindModelsName($insurance_id, $insurancePrvider);
        
        $FMC = [];
        $Title = "Follow-Up After Emergency Department Visit for MCC (FMC)";
        $column_name = 'faed_visit_gap';
        $ShortTitle = "FMC";
        
        if ($careGap == 1) {
            $Title = "Follow-Up After Emergency Department Visit for MCC (FMC) Insurance";
            $column_name = 'faed_visit_gap_insurance';
        }

        
        // As per current status
        $ClosedPatients = $CGModelsName::thisYearGaps($gaps_filterYear)->with('patientinfo')->whereHas('patientinfo')->where($column_name,'Compliant');
        $OpenPatients = $CGModelsName::thisYearGaps($gaps_filterYear)->whereNotIn($column_name, ['Compliant', 'N/A']);
        
        // As per insurance
        $ClosedPatientsCareGapFile = $CGModelsName::thisYearGaps($gaps_filterYear)->with('patientinfo')->whereHas('patientinfo')->where('faed_visit_gap_insurance','Compliant');//->where('source','CareGap_File');
       
        $Refused = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, 'Patient Refused', $gaps_filterYear);
        $Scheduled = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, 'Scheduled', $gaps_filterYear);
        // echo "<pre>";
        // print_r($Refused);exit;
        $Total = $CGModelsName::thisYearGaps($gaps_filterYear)->where($column_name, '!=', ['N/A']);
        
        if(!empty($doctor_id)){
            $ClosedPatients->where('doctor_id',$doctor_id);
            $OpenPatients->where('doctor_id',$doctor_id);
            $ClosedPatientsCareGapFile->where('doctor_id',$doctor_id);
            $Total->where('doctor_id',$doctor_id);
        }
        if(!empty($insurance_id)){
            $ClosedPatients->where('insurance_id',$insurance_id);
            $ClosedPatientsCareGapFile->where('insurance_id',$insurance_id);
            $OpenPatients->where('insurance_id',$insurance_id);
            $Total->where('insurance_id',$insurance_id);
        }
        if(!empty($clinic_id)){
            $clinic_id = explode(',', $clinic_id);
            $ClosedPatients->where('clinic_id',$clinic_id);
            $ClosedPatientsCareGapFile->where('clinic_id',$clinic_id);
            $OpenPatients->where('clinic_id',$clinic_id);
            $Total->where('clinic_id',$clinic_id);
        }
        $ActiveNonComp = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, "", $gaps_filterYear);
        $ClosedPatientsCareGapFile = $ClosedPatientsCareGapFile->count();
        $ClosedPatients = $ClosedPatients->count();
        $ClosedPatientsDifference = $ClosedPatients - $ClosedPatientsCareGapFile;
        $ClosedPatientsDifference = ($ClosedPatientsDifference>=1 ) ? (string) $ClosedPatientsDifference : '0' ;
        $OpenPatients = $OpenPatients->count();
        $Refused = $Refused;
        $Scheduled = $Scheduled;
        $UnScheduled = $ActiveNonComp - $Scheduled - $Refused ;
        $UnScheduled = ($UnScheduled>=1 ) ? (string) $UnScheduled : '0' ;
        $Total = $Total->count();
        // $Eligible = $Refused + $Scheduled + $UnScheduled;
        
        if($Total != 0) {
            $Acheived = number_format( $ClosedPatients * 100 / $Total , 2, '.', '') ;
        }else{
            $Acheived = "0";
        }
        
        $FMC_Return = $this->FollowUpAfterEmergencyDepartmentVisitStarConditions($insurancePrvider, $Acheived);
        $Required_Par = $FMC_Return['Required_Par'];
        $Star = $FMC_Return['Star'];

        $Members_remaining =  ((($Required_Par - $Acheived) * $Total) / 100)  ;  
        if($Members_remaining <= 0 ){
            $Members_remaining = "-";
        }else if($Members_remaining > 0 && $Members_remaining < 1){
            $Members_remaining = 1;
        }else{
            $Members_remaining = number_format($Members_remaining);
        }

        $FMC = [
            'Title'                     => $Title,
            'ShortTitle'                => $ShortTitle,
            'ActiveNonComp'             => $ActiveNonComp,
            'ClosedPatients'            => $ClosedPatients,
            'ClosedPatientsDifference'  => $ClosedPatientsDifference,
            'OpenPatients'              => $OpenPatients,
            'Refused'                   => $Refused,
            'Scheduled'                 => $Scheduled,
            'UnScheduled'               => $UnScheduled,
            'Total'                     => $Total,
            'Acheived'                  => $Acheived,
            'Required_Par'              => $Required_Par,
            'Star'                      => $Star,
            'Members_remaining'         => $Members_remaining,
            'db_col_name'               => $column_name,
        ];

        return $FMC;
    }

    private function OsteoporosisManagementWomenHumana($doctor_id = "" , $insurance_id = "", $clinic_id = "", $careGap = "", $gaps_filterYear, $insurancePrvider)
    {
        $OMW = [];

        $Title = "Osteoporosis Management in Women Who Had a Fracture (OMW)";
        $ShortTitle = "OMW";
        $column_name = 'omw_fracture_gap';
        
        if ($careGap == 1) {
            $Title = "Osteoporosis Management in Women Who Had a Fracture (OMW) Insurance";
            $column_name = 'omw_fracture_gap_insurance';
        }
        
        // As per current status
        $ClosedPatients = HumanaCareGaps::thisYearGaps($gaps_filterYear)->with('patientinfo')->whereHas('patientinfo')->where($column_name,'Compliant');
        $OpenPatients = HumanaCareGaps::thisYearGaps($gaps_filterYear)->whereNotIn($column_name, ['Compliant', 'N/A']);

        // As per insurance
        $ClosedPatientsCareGapFile = HumanaCareGaps::thisYearGaps($gaps_filterYear)->with('patientinfo')->whereHas('patientinfo')->where('omw_fracture_gap_insurance','Compliant');

        $Refused = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, 'Patient Refused', $gaps_filterYear);
        $Scheduled = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, 'Scheduled', $gaps_filterYear);
        
        $Total = HumanaCareGaps::thisYearGaps($gaps_filterYear)->where($column_name, '!=', ['N/A']);
        
        if(!empty($doctor_id)){
            $ClosedPatients->where('doctor_id',$doctor_id);
            $ClosedPatientsCareGapFile->where('doctor_id',$doctor_id);
            $OpenPatients->where('doctor_id',$doctor_id);
            $Total->where('doctor_id',$doctor_id);
        }
        if(!empty($insurance_id)){
            $ClosedPatients->where('insurance_id',$insurance_id);
            $ClosedPatientsCareGapFile->where('insurance_id',$insurance_id);
            $OpenPatients->where('insurance_id',$insurance_id);
            $Total->where('insurance_id',$insurance_id);
        }
        if(!empty($clinic_id)){
            $clinic_id = explode(',', $clinic_id);
            $ClosedPatients->where('clinic_id',$clinic_id);
            $ClosedPatientsCareGapFile->where('clinic_id',$clinic_id);
            $OpenPatients->where('clinic_id',$clinic_id);
            $Total->where('clinic_id',$clinic_id);
        }
        $ActiveNonComp = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, "", $gaps_filterYear);
        $ClosedPatientsCareGapFile = $ClosedPatientsCareGapFile->count();
        $ClosedPatients = $ClosedPatients->count();
        $ClosedPatientsDifference = $ClosedPatients - $ClosedPatientsCareGapFile;
        $ClosedPatientsDifference = ($ClosedPatientsDifference>=1 ) ? (string) $ClosedPatientsDifference : '0' ;
        $OpenPatients = $OpenPatients->count();
        $Refused = $Refused;
        $Scheduled = $Scheduled;
        $UnScheduled = $ActiveNonComp - $Scheduled - $Refused ;
        $UnScheduled = ($UnScheduled>=1 ) ? (string) $UnScheduled : '0' ;
        $Total = $Total->count();
        // $Eligible = $Refused + $Scheduled + $UnScheduled;

        if($Total != 0) {
            $Acheived = number_format( $ClosedPatients * 100 / $Total , 1, '.', '') ;
        }else{
            $Acheived = "0";
        }
        $Required_Par = "73" ; 

        if($Acheived >= "73"){
            $Star = "5";
        }else if($Acheived >= "55"){
            $Star = "4";
        }else if($Acheived >= "45"){
            $Star = "3";
        }else if($Acheived >= "32"){
            $Star = "2";
        }else{
            $Star = "1";
        }

        $Members_remaining =  ((($Required_Par - $Acheived) * $Total) / 100)  ;  
        if($Members_remaining <= 0 ){
            $Members_remaining = "-";
        }else if($Members_remaining > 0 && $Members_remaining < 1){
            $Members_remaining = 1;
        }else{
            $Members_remaining = number_format($Members_remaining);
        }

        $OMW = [
            'Title'                     => $Title,
            'ShortTitle'                => $ShortTitle,
            'ActiveNonComp'             => $ActiveNonComp,
            'ClosedPatients'            => $ClosedPatients,
            'ClosedPatientsDifference'  => $ClosedPatientsDifference,
            'OpenPatients'              => $OpenPatients,
            'Refused'                   => $Refused,
            'Scheduled'                 => $Scheduled,
            'UnScheduled'               => $UnScheduled,
            'Total'                     => $Total,
            'Acheived'                  => $Acheived,
            'Required_Par'              => $Required_Par,
            'Star'                      => $Star,
            'Members_remaining'         => $Members_remaining,
            'db_col_name'               => $column_name,
        ];

        return $OMW;
    }

    private function PlanAllCauseReadmissionsHumana($doctor_id = "" , $insurance_id = "", $clinic_id = "", $careGap = "", $gaps_filterYear, $insurancePrvider)
    {

        $PCR = [];
        $Title = "Plan All-Cause Readmissions (PCR)";
        $column_name = 'pc_readmissions_gap';
        $ShortTitle = "PCR";
        
        if ($careGap == 1) {
            $Title = "Plan All-Cause Readmissions (PCR) Insurance";
            $column_name = 'pc_readmissions_gap_insurance';
        }

        // As per current status
        $ClosedPatients = HumanaCareGaps::thisYearGaps($gaps_filterYear)->with('patientinfo')->whereHas('patientinfo')->where($column_name,'Compliant');
        $OpenPatients = HumanaCareGaps::thisYearGaps($gaps_filterYear)->whereNotIn($column_name, ['Compliant', 'N/A']);

        // As per insurance
        $ClosedPatientsCareGapFile = HumanaCareGaps::thisYearGaps($gaps_filterYear)->with('patientinfo')->whereHas('patientinfo')->where('pc_readmissions_gap_insurance','Compliant');

        $Refused = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, 'Patient Refused', $gaps_filterYear);
        $Scheduled = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, 'Scheduled', $gaps_filterYear);
        
        $Total = HumanaCareGaps::thisYearGaps($gaps_filterYear)->where($column_name, '!=', ['N/A']);
        
        if(!empty($doctor_id)){
            $ClosedPatients->where('doctor_id',$doctor_id);
            $ClosedPatientsCareGapFile->where('doctor_id',$doctor_id);
            $OpenPatients->where('doctor_id',$doctor_id);
            $Total->where('doctor_id',$doctor_id);
        }
        if(!empty($insurance_id)){
            $ClosedPatients->where('insurance_id',$insurance_id);
            $ClosedPatientsCareGapFile->where('insurance_id',$insurance_id);
            $OpenPatients->where('insurance_id',$insurance_id);
            $Total->where('insurance_id',$insurance_id);
        }
        if(!empty($clinic_id)){
            $clinic_id = explode(',', $clinic_id);
            $ClosedPatients->where('clinic_id',$clinic_id);
            $ClosedPatientsCareGapFile->where('clinic_id',$clinic_id);
            $OpenPatients->where('clinic_id',$clinic_id);
            $Total->where('clinic_id',$clinic_id);
        }
        $ActiveNonComp = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, "", $gaps_filterYear);
        $ClosedPatientsCareGapFile = $ClosedPatientsCareGapFile->count();
        $ClosedPatients = $ClosedPatients->count();
        $ClosedPatientsDifference = $ClosedPatients - $ClosedPatientsCareGapFile;
        $ClosedPatientsDifference = ($ClosedPatientsDifference>=1 ) ? (string) $ClosedPatientsDifference : '0' ;
        $OpenPatients = $OpenPatients->count();
        $Refused = $Refused;
        $Scheduled = $Scheduled;
        $UnScheduled = $ActiveNonComp - $Scheduled - $Refused ;
        $UnScheduled = ($UnScheduled>=1 ) ? (string) $UnScheduled : '0' ;
        $Total = $Total->count();
        // $Eligible = $Refused + $Scheduled + $UnScheduled;

        if($Total != 0) {
            $Acheived = number_format( $OpenPatients * 100 / $Total , 1, '.', '') ;
        }else{
            $Acheived = "0";
        }

        $Required_Par = "7" ; 

        if($Acheived <= "7"){
            $Star = "5";
        }else if($Acheived <= "10" && $Acheived > "7"){
            $Star = "4";
        }else if($Acheived <= "12" && $Acheived > "10"){
            $Star = "3";
        }else if($Acheived <= "14" && $Acheived > "12"){
            $Star = "2";
        }else{
            $Star = "1";
        }

        $Members_remaining =  ((($Required_Par - $Acheived) * $Total) / 100)  ;  
        if($Members_remaining <= 0 ){
            $Members_remaining = "-";
        }else if($Members_remaining > 0 && $Members_remaining < 1){
            $Members_remaining = 1;
        }else{
            $Members_remaining = number_format($Members_remaining);
        }

        $PCR = [
            'Title'                     => $Title,
            'ShortTitle'                => $ShortTitle,
            'ActiveNonComp'             => $ActiveNonComp,
            'ClosedPatients'            => $ClosedPatients,
            'ClosedPatientsDifference'  => $ClosedPatientsDifference,
            'OpenPatients'              => $OpenPatients,
            'Refused'                   => $Refused,
            'Scheduled'                 => $Scheduled,
            'UnScheduled'               => $UnScheduled,
            'Total'                     => $Total,
            'Acheived'                  => $Acheived,
            'Required_Par'              => $Required_Par,
            'Star'                      => $Star,
            'Members_remaining'         => $Members_remaining,
            'db_col_name'               => $column_name,
        ];

        return $PCR;
    }
    private function OlderAdultsFunctionalStatusAssessment($doctor_id = "" , $insurance_id = "", $clinic_id = "", $careGap = "", $gaps_filterYear, $insurancePrvider)
    {

        $DMC10 = [];
        $Title = "DMC10-Care for Older Adults - Functional Status Assessment";
        $column_name = 'adults_fun_status_gap';
        $ShortTitle = "DMC10";
        
        if ($careGap == 1) {
            $Title = "DMC10-Care for Older Adults - Functional Status Assessment Insurance";
            $column_name = 'adults_fun_status_gap_insurance';
        }

        // As per current status
        $ClosedPatients = UnitedHealthcareCareGaps::thisYearGaps($gaps_filterYear)->with('patientinfo')->whereHas('patientinfo')->where($column_name,'Compliant');
        $OpenPatients = UnitedHealthcareCareGaps::thisYearGaps($gaps_filterYear)->whereNotIn($column_name, ['Compliant', 'N/A']);

        // As per insurance
        $ClosedPatientsCareGapFile = UnitedHealthcareCareGaps::thisYearGaps($gaps_filterYear)->with('patientinfo')->whereHas('patientinfo')->where('adults_fun_status_gap_insurance','Compliant');

        $Refused = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, 'Patient Refused', $gaps_filterYear);
        $Scheduled = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, 'Scheduled', $gaps_filterYear);
        
        $Total = UnitedHealthcareCareGaps::thisYearGaps($gaps_filterYear)->where($column_name, '!=', ['N/A']);
        
        if(!empty($doctor_id)){
            $ClosedPatients->where('doctor_id',$doctor_id);
            $ClosedPatientsCareGapFile->where('doctor_id',$doctor_id);
            $OpenPatients->where('doctor_id',$doctor_id);
            $Total->where('doctor_id',$doctor_id);
        }
        if(!empty($insurance_id)){
            $ClosedPatients->where('insurance_id',$insurance_id);
            $ClosedPatientsCareGapFile->where('insurance_id',$insurance_id);
            $OpenPatients->where('insurance_id',$insurance_id);
            $Total->where('insurance_id',$insurance_id);
        }
        if(!empty($clinic_id)){
            $clinic_id = explode(',', $clinic_id);
            $ClosedPatients->where('clinic_id',$clinic_id);
            $ClosedPatientsCareGapFile->where('clinic_id',$clinic_id);
            $OpenPatients->where('clinic_id',$clinic_id);
            $Total->where('clinic_id',$clinic_id);
        }
        $ActiveNonComp = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, "", $gaps_filterYear);
        $ClosedPatientsCareGapFile = $ClosedPatientsCareGapFile->count();
        $ClosedPatients = $ClosedPatients->count();
        $ClosedPatientsDifference = $ClosedPatients - $ClosedPatientsCareGapFile;
        $ClosedPatientsDifference = ($ClosedPatientsDifference>=1 ) ? (string) $ClosedPatientsDifference : '0' ;
        $OpenPatients = $OpenPatients->count();
        $Refused = $Refused;
        $Scheduled = $Scheduled;
        $UnScheduled = $ActiveNonComp - $Scheduled - $Refused ;
        $UnScheduled = ($UnScheduled>=1 ) ? (string) $UnScheduled : '0' ;
        $Total = $Total->count();
        // $Eligible = $Refused + $Scheduled + $UnScheduled;

        if($Total != 0) {
            $Acheived = number_format( $ClosedPatients * 100 / $Total , 1, '.', '') ;
        }else{
            $Acheived = "0";
        }

        $Required_Par = "94.0" ; 

        if($Acheived >=  $Required_Par){
            $Star = "5";
        }else if($Acheived >= "93"){
            $Star = "4";
        }else if($Acheived >= "92.0"){
            $Star = "3";
        }else if($Acheived >= "91.0"){
            $Star = "2";
        }else{
            $Star = "1";
        }

        $Members_remaining =  ((($Required_Par - $Acheived) * $Total) / 100)  ;  
        if($Members_remaining <= 0 ){
            $Members_remaining = "-";
        }else if($Members_remaining > 0 && $Members_remaining < 1){
            $Members_remaining = 1;
        }else{
            $Members_remaining = number_format($Members_remaining);
        }

        $DMC10 = [
            'Title'                     => $Title,
            'ShortTitle'                => $ShortTitle,
            'ActiveNonComp'             => $ActiveNonComp,
            'ClosedPatients'            => $ClosedPatients,
            'ClosedPatientsDifference'  => $ClosedPatientsDifference,
            'OpenPatients'              => $OpenPatients,
            'Refused'                   => $Refused,
            'Scheduled'                 => $Scheduled,
            'UnScheduled'               => $UnScheduled,
            'Total'                     => $Total,
            'Acheived'                  => $Acheived,
            'Required_Par'              => $Required_Par,
            'Star'                      => $Star,
            'Members_remaining'         => $Members_remaining,
            'db_col_name'               => $column_name,
        ];

        return $DMC10;
    }

    private function StatinTherapyPatientsCardiovascularDisease($doctor_id = "" , $insurance_id = "", $clinic_id = "", $careGap = "", $gaps_filterYear, $insurancePrvider)
    {
        // Find Models Name for care gaps 
        $CGModelsName = $this->FindModelsName($insurance_id, $insurancePrvider);

        $SPC_STATIN = [];
        $Title = "Statin Therapy for Patients with Cardiovascular Disease (SPC_STATIN)";
        $column_name = 'spc_disease_gap';
        $ShortTitle = "SPC_STATIN";
        
        if ($careGap == 1) {
            $Title = "Statin Therapy for Patients with Cardiovascular Disease (SPC_STATIN) Insurance";
            $column_name = 'spc_disease_gap_insurance';
        }

        // As per current status
        // $ClosedPatients = $CGModelsName::thisYearGaps($gaps_filterYear)->with('patientinfo')->whereHas('patientinfo')->where($column_name,'Compliant');
        $ClosedPatients = $CGModelsName::thisYearGaps($gaps_filterYear)->where('insurance_id', $insurance_id)
            ->whereHas('patientinfo', function ($query) use ($insurance_id, $doctor_id, $clinic_id, $gaps_filterYear) {
            $query->where('status', '1')->orWhereHas('statusLogs', function ($query) use ($gaps_filterYear) {
                $query->where('status', 1)->where('patient_year', $gaps_filterYear);
            });
            
            if (!empty($clinic_id)) {
                $query->where('clinic_id', $clinic_id);
            }

            if (!empty($doctor_id)) {
                $query->where('doctor_id', $doctor_id);
            }
        })->where($column_name,'Compliant')->count();
        
        // $OpenPatients = $CGModelsName::thisYearGaps($gaps_filterYear)->whereNotIn($column_name, ['Compliant', 'N/A']);
        $OpenPatients = $CGModelsName::thisYearGaps($gaps_filterYear)->where(['insurance_id' => $insurance_id])
            ->whereHas('patientinfo', function ($query) use ($insurance_id, $doctor_id, $clinic_id) {
                // $query->where('insurance_id', $insurance_id);
                // $query->where('status', '1');
                
                if (!empty($clinic_id)) {
                    $query->where('clinic_id', $clinic_id);
                }

                if (!empty($doctor_id)) {
                    $query->where('doctor_id', $doctor_id);
                }
            })->whereNotIn($column_name, ['Compliant', 'N/A'])->count();

        // As per insurance
        // $ClosedPatientsCareGapFile = $CGModelsName::thisYearGaps($gaps_filterYear)->with('patientinfo')->whereHas('patientinfo')->where('spc_disease_gap_insurance','Compliant');
        $ClosedPatientsCareGapFile = $CGModelsName::thisYearGaps($gaps_filterYear)->where(['insurance_id' => $insurance_id])
            ->whereHas('patientinfo', function ($query) use ($insurance_id, $doctor_id, $clinic_id){
                // $query->where('insurance_id', $insurance_id);
                $query->where('status', '1');
                
                if (!empty($clinic_id)) {
                    $query->where('clinic_id', $clinic_id);
                }

                if (!empty($doctor_id)) {
                    $query->where('doctor_id', $doctor_id);
                }
        })->where('spc_disease_gap_insurance','Completed')->count();

        $Refused = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, 'Patient Refused', $gaps_filterYear);
        $Scheduled = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, 'Scheduled', $gaps_filterYear);
        
        // $Total = $CGModelsName::thisYearGaps($gaps_filterYear)->where($column_name, '!=', ['N/A']);
        
        // if(!empty($doctor_id)){
        //     $ClosedPatients->where('doctor_id',$doctor_id);
        //     $ClosedPatientsCareGapFile->where('doctor_id',$doctor_id);
        //     $OpenPatients->where('doctor_id',$doctor_id);
        //     $Total->where('doctor_id',$doctor_id);
        // }
        // if(!empty($insurance_id)){
        //     $ClosedPatients->where('insurance_id',$insurance_id);
        //     $ClosedPatientsCareGapFile->where('insurance_id',$insurance_id);
        //     $OpenPatients->where('insurance_id',$insurance_id);
        //     $Total->where('insurance_id',$insurance_id);
        // }
        // if(!empty($clinic_id)){
        //     $clinic_id = explode(',', $clinic_id);
        //     $ClosedPatients->where('clinic_id',$clinic_id);
        //     $ClosedPatientsCareGapFile->where('clinic_id',$clinic_id);
        //     $OpenPatients->where('clinic_id',$clinic_id);
        //     $Total->where('clinic_id',$clinic_id);
        // }

        $ActiveNonComp = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, "", $gaps_filterYear);
        $ClosedPatientsCareGapFile = $ClosedPatientsCareGapFile;
        $ClosedPatients = $ClosedPatients;
        $ClosedPatientsDifference = $ClosedPatients - $ClosedPatientsCareGapFile;
        $ClosedPatientsDifference = ($ClosedPatientsDifference>=1 ) ? (string) $ClosedPatientsDifference : '0' ;
        $OpenPatients = $OpenPatients;
        $Refused = $Refused;
        $Scheduled = $Scheduled;
        $UnScheduled = $ActiveNonComp - $Scheduled - $Refused ;
        $UnScheduled = ($UnScheduled>=1 ) ? (string) $UnScheduled : '0' ;
        $Total = $ClosedPatients + $OpenPatients;
        // $Eligible = $Refused + $Scheduled + $UnScheduled;

        if($Total != 0) {
            $Acheived = number_format( $ClosedPatients * 100 / $Total , 1, '.', '') ;
        }else{
            $Acheived = "0";
        }

        $SPC_STATINReturn = $this->StatinTherapyPatientsCardiovascularDiseaseStartConditions($insurancePrvider, $Acheived);
        $Required_Par = $SPC_STATINReturn['Required_Par'];
        $Star = $SPC_STATINReturn['Star'];

        $Members_remaining =  ((($Required_Par - $Acheived) * $Total) / 100)  ;  
        if($Members_remaining <= 0 ){
            $Members_remaining = "-";
        }else if($Members_remaining > 0 && $Members_remaining < 1){
            $Members_remaining = 1;
        }else{
            $Members_remaining = number_format($Members_remaining);
        }

        $SPC_STATIN = [
            'Title'                     => $Title,
            'ShortTitle'                => $ShortTitle,
            'ActiveNonComp'             => $ActiveNonComp,
            'ClosedPatients'            => $ClosedPatients,
            'ClosedPatientsDifference'  => $ClosedPatientsDifference,
            'OpenPatients'              => $OpenPatients,
            'Refused'                   => $Refused,
            'Scheduled'                 => $Scheduled,
            'UnScheduled'               => $UnScheduled,
            'Total'                     => $Total,
            'Acheived'                  => $Acheived,
            'Required_Par'              => $Required_Par,
            'Star'                      => $Star,
            'Members_remaining'         => $Members_remaining,
            'db_col_name'               => $column_name,
        ];

        return $SPC_STATIN;
    }

    private function TransitionsMedicationReconciliationPostDischargeHumana($doctor_id = "" , $insurance_id = "", $clinic_id = "", $careGap = "", $gaps_filterYear, $insurancePrvider)
    {
        $TRC_MRP = [];
        $Title = "Transitions of Care: Medication Reconciliation Post Discharge (TRC_MRP)";
        $column_name = 'post_disc_gap';
        $ShortTitle = "TRC_MRP";

        if ($careGap == 1) {    
            $Title = "Transitions of Care: Medication Reconciliation Post Discharge (TRC_MRP) Insurance";
            $column_name = 'post_disc_gap_insurance';
        }
    
        // As per current status
        $ClosedPatients = HumanaCareGaps::thisYearGaps($gaps_filterYear)->with('patientinfo')->whereHas('patientinfo')->where($column_name, 'Compliant');
        $OpenPatients = HumanaCareGaps::thisYearGaps($gaps_filterYear)->whereNotIn($column_name, ['Compliant', 'N/A']);

        // As per insurance
        $ClosedPatientsCareGapFile = HumanaCareGaps::thisYearGaps($gaps_filterYear)->with('patientinfo')->whereHas('patientinfo')->where('post_disc_gap_insurance','Compliant');

        $Refused = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, 'Patient Refused', $gaps_filterYear);
        $Scheduled = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, 'Scheduled', $gaps_filterYear);
        
        $Total = HumanaCareGaps::thisYearGaps($gaps_filterYear)->where($column_name, '!=', ['N/A']);
        
        if(!empty($doctor_id)){
            $ClosedPatients->where('doctor_id',$doctor_id);
            $ClosedPatientsCareGapFile->where('doctor_id',$doctor_id);
            $OpenPatients->where('doctor_id',$doctor_id);
            $Total->where('doctor_id',$doctor_id);
        }
        if(!empty($insurance_id)){
            $ClosedPatients->where('insurance_id',$insurance_id);
            $ClosedPatientsCareGapFile->where('insurance_id',$insurance_id);
            $OpenPatients->where('insurance_id',$insurance_id);
            $Total->where('insurance_id',$insurance_id);
        }
        if(!empty($clinic_id)){
            $clinic_id = explode(',', $clinic_id);
            $ClosedPatients->where('clinic_id',$clinic_id);
            $ClosedPatientsCareGapFile->where('clinic_id',$clinic_id);
            $OpenPatients->where('clinic_id',$clinic_id);
            $Total->where('clinic_id',$clinic_id);
        }
        $ActiveNonComp = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, "", $gaps_filterYear);
        $ClosedPatientsCareGapFile = $ClosedPatientsCareGapFile->count();
        $ClosedPatients = $ClosedPatients->count();
        $ClosedPatientsDifference = $ClosedPatients - $ClosedPatientsCareGapFile;
        $ClosedPatientsDifference = ($ClosedPatientsDifference>=1 ) ? (string) $ClosedPatientsDifference : '0' ;
        $OpenPatients = $OpenPatients->count();
        $Refused = $Refused;
        $Scheduled = $Scheduled;
        $UnScheduled = $ActiveNonComp - $Scheduled - $Refused ;
        $UnScheduled = ($UnScheduled>=1 ) ? (string) $UnScheduled : '0' ;
        $Total = $Total->count();
        // $Eligible = $Refused + $Scheduled + $UnScheduled;

        if($Total != 0) {
            $Acheived = number_format( $ClosedPatients * 100 / $Total , 1, '.', '') ;
        }else{
            $Acheived = "0";
        }

        $Required_Par = "82" ; 

        if($Acheived >= "82"){
            $Star = "5";
        }else if($Acheived >= "69"){
            $Star = "4";
        }else if($Acheived >= "57"){
            $Star = "3";
        }else if($Acheived >= "43"){
            $Star = "2";
        }else{
            $Star = "1";
        }

        $Members_remaining =  ((($Required_Par - $Acheived) * $Total) / 100)  ;  
        if($Members_remaining <= 0 ){
            $Members_remaining = "-";
        }else if($Members_remaining > 0 && $Members_remaining < 1){
            $Members_remaining = 1;
        }else{
            $Members_remaining = number_format($Members_remaining);
        }

        $TRC_MRP = [
            'Title'                     => $Title,
            'ShortTitle'                => $ShortTitle,
            'ActiveNonComp'             => $ActiveNonComp,
            'ClosedPatients'            => $ClosedPatients,
            'ClosedPatientsDifference'  => $ClosedPatientsDifference,
            'OpenPatients'              => $OpenPatients,
            'Refused'                   => $Refused,
            'Scheduled'                 => $Scheduled,
            'UnScheduled'               => $UnScheduled,
            'Total'                     => $Total,
            'Acheived'                  => $Acheived,
            'Required_Par'              => $Required_Par,
            'Star'                      => $Star,
            'Members_remaining'         => $Members_remaining,
            'db_col_name'               => $column_name,
        ];

        return $TRC_MRP;
    }

    private function TransitionsPatientEngagementAfterInpatientDischargeHumana($doctor_id = "" , $insurance_id = "", $clinic_id = "", $careGap = "", $gaps_filterYear, $insurancePrvider)
    {
        $TRC_PED = [];

        $Title = "Transitions of Care: Patient Engagement After Inpatient Discharge (TRC_PED)";
        $column_name = 'after_inp_disc_gap';
        $ShortTitle = "TRC_PED";
        
        if ($careGap == 1) {
            $Title = "Transitions of Care: Patient Engagement After Inpatient Discharge (TRC_PED) Insurance";
            $column_name = 'after_inp_disc_gap_insurance';
        }

        // As per current status
        $ClosedPatients = HumanaCareGaps::thisYearGaps($gaps_filterYear)->with('patientinfo')->whereHas('patientinfo')->where($column_name,'Compliant');
        $OpenPatients = HumanaCareGaps::thisYearGaps($gaps_filterYear)->whereNotIn($column_name, ['Compliant', 'N/A']);

        // As per insurance
        $ClosedPatientsCareGapFile = HumanaCareGaps::thisYearGaps($gaps_filterYear)->with('patientinfo')->whereHas('patientinfo')->where('after_inp_disc_gap_insurance','Compliant');

        $Refused = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, 'Patient Refused', $gaps_filterYear);
        $Scheduled = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, 'Scheduled', $gaps_filterYear);
        
        $Total = HumanaCareGaps::thisYearGaps($gaps_filterYear)->where($column_name, '!=', ['N/A']);
        
        if(!empty($doctor_id)){
            $ClosedPatients->where('doctor_id',$doctor_id);
            $ClosedPatientsCareGapFile->where('doctor_id',$doctor_id);
            $OpenPatients->where('doctor_id',$doctor_id);
            $Total->where('doctor_id',$doctor_id);
        }
        if(!empty($insurance_id)){
            $ClosedPatients->where('insurance_id',$insurance_id);
            $ClosedPatientsCareGapFile->where('insurance_id',$insurance_id);
            $OpenPatients->where('insurance_id',$insurance_id);
            $Total->where('insurance_id',$insurance_id);
        }
        if(!empty($clinic_id)){
            $clinic_id = explode(',', $clinic_id);
            $ClosedPatients->where('clinic_id',$clinic_id);
            $ClosedPatientsCareGapFile->where('clinic_id',$clinic_id);
            $OpenPatients->where('clinic_id',$clinic_id);
            $Total->where('clinic_id',$clinic_id);
        }
        $ActiveNonComp = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, "", $gaps_filterYear);
        $ClosedPatientsCareGapFile = $ClosedPatientsCareGapFile->count();
        $ClosedPatients = $ClosedPatients->count();
        $ClosedPatientsDifference = $ClosedPatients - $ClosedPatientsCareGapFile;
        $ClosedPatientsDifference = ($ClosedPatientsDifference>=1 ) ? (string) $ClosedPatientsDifference : '0' ;
        $OpenPatients = $OpenPatients->count();
        $Refused = $Refused;
        $Scheduled = $Scheduled;
        $UnScheduled = $ActiveNonComp - $Scheduled - $Refused ;
        $UnScheduled = ($UnScheduled>=1 ) ? (string) $UnScheduled : '0' ;
        $Total = $Total->count();
        // $Eligible = $Refused + $Scheduled + $UnScheduled;

        if($Total != 0) {
            $Acheived = number_format( $ClosedPatients * 100 / $Total , 1, '.', '') ;
        }else{
            $Acheived = "0";
        }

        $Required_Par = "91" ; 

        if($Acheived >= "91"){
            $Star = "5";
        }else if($Acheived >= "85"){
            $Star = "4";
        }else if($Acheived >= "79"){
            $Star = "3";
        }else if($Acheived >= "73"){
            $Star = "2";
        }else{
            $Star = "1";
        }

        $Members_remaining =  ((($Required_Par - $Acheived) * $Total) / 100)  ;  
        if($Members_remaining <= 0 ){
            $Members_remaining = "-";
        }else if($Members_remaining > 0 && $Members_remaining < 1){
            $Members_remaining = 1;
        }else{
            $Members_remaining = number_format($Members_remaining);
        }

        $TRC_PED = [
            'Title'                     => $Title,
            'ShortTitle'                => $ShortTitle,
            'ActiveNonComp'             => $ActiveNonComp,
            'ClosedPatients'            => $ClosedPatients,
            'ClosedPatientsDifference'  => $ClosedPatientsDifference,
            'OpenPatients'              => $OpenPatients,
            'Refused'                   => $Refused,
            'Scheduled'                 => $Scheduled,
            'UnScheduled'               => $UnScheduled,
            'Total'                     => $Total,
            'Acheived'                  => $Acheived,
            'Required_Par'              => $Required_Par,
            'Star'                      => $Star,
            'Members_remaining'         => $Members_remaining,
            'db_col_name'               => $column_name,
        ];

        return $TRC_PED;
    }

    private function MedAdherenceDiabetes($doctor_id = "" , $insurance_id = "", $clinic_id = "", $careGap = "", $gaps_filterYear, $insurancePrvider)
    {
        $MedAdherence = [];

        $Title = "D08-Med Ad. For Diabetes Meds Current Year Status";
        $column_name = 'med_adherence_diabetes_gap';
        $ShortTitle = "Med Ad";
        
        if ($careGap == 1) {
            $Title = "D08-Med Ad. For Diabetes Meds Current Year Status Insurance";
            $column_name = 'med_adherence_diabetes_gap_insurance';
        }

        // As per current status
        $ClosedPatients = UnitedHealthcareCareGaps::thisYearGaps($gaps_filterYear)->with('patientinfo')->whereHas('patientinfo')->where($column_name,'Compliant');
        $OpenPatients = UnitedHealthcareCareGaps::thisYearGaps($gaps_filterYear)->whereNotIn($column_name, ['Compliant', 'N/A']);

        // As per insurance
        $ClosedPatientsCareGapFile = UnitedHealthcareCareGaps::thisYearGaps($gaps_filterYear)->with('patientinfo')->whereHas('patientinfo')->where('med_adherence_diabetes_gap_insurance','Compliant');

        $Refused = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, 'Patient Refused', $gaps_filterYear);
        $Scheduled = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, 'Scheduled', $gaps_filterYear);
        
        $Total = UnitedHealthcareCareGaps::thisYearGaps($gaps_filterYear)->where($column_name, '!=', ['N/A']);
        
        if(!empty($doctor_id)){
            $ClosedPatients->where('doctor_id',$doctor_id);
            $ClosedPatientsCareGapFile->where('doctor_id',$doctor_id);
            $OpenPatients->where('doctor_id',$doctor_id);
            $Total->where('doctor_id',$doctor_id);
        }
        if(!empty($insurance_id)){
            $ClosedPatients->where('insurance_id',$insurance_id);
            $ClosedPatientsCareGapFile->where('insurance_id',$insurance_id);
            $OpenPatients->where('insurance_id',$insurance_id);
            $Total->where('insurance_id',$insurance_id);
        }
        if(!empty($clinic_id)){
            $clinic_id = explode(',', $clinic_id);
            $ClosedPatients->where('clinic_id',$clinic_id);
            $ClosedPatientsCareGapFile->where('clinic_id',$clinic_id);
            $OpenPatients->where('clinic_id',$clinic_id);
            $Total->where('clinic_id',$clinic_id);
        }
        $ActiveNonComp = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, "", $gaps_filterYear);
        $ClosedPatientsCareGapFile = $ClosedPatientsCareGapFile->count();
        $ClosedPatients = $ClosedPatients->count();
        $ClosedPatientsDifference = $ClosedPatients - $ClosedPatientsCareGapFile;
        $ClosedPatientsDifference = ($ClosedPatientsDifference>=1 ) ? (string) $ClosedPatientsDifference : '0' ;
        $OpenPatients = $OpenPatients->count();
        $Refused = $Refused;
        $Scheduled = $Scheduled;
        $UnScheduled = $ActiveNonComp - $Scheduled - $Refused ;
        $UnScheduled = ($UnScheduled>=1 ) ? (string) $UnScheduled : '0' ;
        $Total = $Total->count();
        // $Eligible = $Refused + $Scheduled + $UnScheduled;

        if($Total != 0) {
            $Acheived = number_format( $ClosedPatients * 100 / $Total , 1, '.', '') ;
        }else{
            $Acheived = "0";
        }

        $Required_Par = "90.0" ; 

        if($Acheived >= $Required_Par){
            $Star = "5";
        }else if($Acheived >= "88"){
            $Star = "4";
        }else if($Acheived >= "87.0"){
            $Star = "3";
        }else if($Acheived >= "86.0"){
            $Star = "2";
        }else{
            $Star = "1";
        }

        $Members_remaining =  ((($Required_Par - $Acheived) * $Total) / 100)  ;  
        if($Members_remaining <= 0 ){
            $Members_remaining = "-";
        }else if($Members_remaining > 0 && $Members_remaining < 1){
            $Members_remaining = 1;
        }else{
            $Members_remaining = number_format($Members_remaining);
        }

        $MedAdherence = [
            'Title'                     => $Title,
            'ShortTitle'                => $ShortTitle,
            'ActiveNonComp'             => $ActiveNonComp,
            'ClosedPatients'            => $ClosedPatients,
            'ClosedPatientsDifference'  => $ClosedPatientsDifference,
            'OpenPatients'              => $OpenPatients,
            'Refused'                   => $Refused,
            'Scheduled'                 => $Scheduled,
            'UnScheduled'               => $UnScheduled,
            'Total'                     => $Total,
            'Acheived'                  => $Acheived,
            'Required_Par'              => $Required_Par,
            'Star'                      => $Star,
            'Members_remaining'         => $Members_remaining,
            'db_col_name'               => $column_name,
        ];

        return $MedAdherence;
    }

    private function StatinTherapyPatientsCardiovascularUHC($doctor_id = "" , $insurance_id = "", $clinic_id = "", $careGap = "", $gaps_filterYear, $insurancePrvider)
    {
        $StatinTherapy = [];

        $Title = "C16 - Statin Therapy for Patients with Cardiovascular Disease";
        $column_name = 'statin_therapy_gap';
        $ShortTitle = "C16";
        
        if ($careGap == 1) {
            $Title = "C16 - Statin Therapy for Patients with Cardiovascular Disease Insurance";
            $column_name = 'statin_therapy_gap_insurance';
        }

        // As per current status
        $ClosedPatients = UnitedHealthcareCareGaps::thisYearGaps($gaps_filterYear)->with('patientinfo')->whereHas('patientinfo')->where($column_name,'Compliant');
        $OpenPatients = UnitedHealthcareCareGaps::thisYearGaps($gaps_filterYear)->whereNotIn($column_name, ['Compliant', 'N/A']);

        // As per insurance
        $ClosedPatientsCareGapFile = UnitedHealthcareCareGaps::thisYearGaps($gaps_filterYear)->with('patientinfo')->whereHas('patientinfo')->where('statin_therapy_gap_insurance','Compliant');

        $Refused = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, 'Patient Refused', $gaps_filterYear);
        $Scheduled = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, 'Scheduled', $gaps_filterYear);
        
        $Total = UnitedHealthcareCareGaps::thisYearGaps($gaps_filterYear)->where($column_name, '!=', ['N/A']);
        
        if(!empty($doctor_id)){
            $ClosedPatients->where('doctor_id',$doctor_id);
            $ClosedPatientsCareGapFile->where('doctor_id',$doctor_id);
            $OpenPatients->where('doctor_id',$doctor_id);
            $Total->where('doctor_id',$doctor_id);
        }
        if(!empty($insurance_id)){
            $ClosedPatients->where('insurance_id',$insurance_id);
            $ClosedPatientsCareGapFile->where('insurance_id',$insurance_id);
            $OpenPatients->where('insurance_id',$insurance_id);
            $Total->where('insurance_id',$insurance_id);
        }
        if(!empty($clinic_id)){
            $clinic_id = explode(',', $clinic_id);
            $ClosedPatients->where('clinic_id',$clinic_id);
            $ClosedPatientsCareGapFile->where('clinic_id',$clinic_id);
            $OpenPatients->where('clinic_id',$clinic_id);
            $Total->where('clinic_id',$clinic_id);
        }
        $ActiveNonComp = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, "", $gaps_filterYear);
        $ClosedPatientsCareGapFile = $ClosedPatientsCareGapFile->count();
        $ClosedPatients = $ClosedPatients->count();
        $ClosedPatientsDifference = $ClosedPatients - $ClosedPatientsCareGapFile;
        $ClosedPatientsDifference = ($ClosedPatientsDifference>=1 ) ? (string) $ClosedPatientsDifference : '0' ;
        $OpenPatients = $OpenPatients->count();
        $Refused = $Refused;
        $Scheduled = $Scheduled;
        $UnScheduled = $ActiveNonComp - $Scheduled - $Refused ;
        $UnScheduled = ($UnScheduled>=1 ) ? (string) $UnScheduled : '0' ;
        $Total = $Total->count();
        // $Eligible = $Refused + $Scheduled + $UnScheduled;

        if($Total != 0) {
            $Acheived = number_format( $ClosedPatients * 100 / $Total , 1, '.', '') ;
        }else{
            $Acheived = "0";
        }

        $Required_Par = "90.0" ; 

        if($Acheived >= $Required_Par){
            $Star = "5";
        }else if($Acheived >= "89"){
            $Star = "4";
        }else if($Acheived >= "88.0"){
            $Star = "3";
        }else if($Acheived >= "87.0"){
            $Star = "2";
        }else{
            $Star = "1";
        }

        $Members_remaining =  ((($Required_Par - $Acheived) * $Total) / 100)  ;  
        if($Members_remaining <= 0 ){
            $Members_remaining = "-";
        }else if($Members_remaining > 0 && $Members_remaining < 1){
            $Members_remaining = 1;
        }else{
            $Members_remaining = number_format($Members_remaining);
        }

        $StatinTherapy = [
            'Title'                     => $Title,
            'ShortTitle'                => $ShortTitle,
            'ActiveNonComp'             => $ActiveNonComp,
            'ClosedPatients'            => $ClosedPatients,
            'ClosedPatientsDifference'  => $ClosedPatientsDifference,
            'OpenPatients'              => $OpenPatients,
            'Refused'                   => $Refused,
            'Scheduled'                 => $Scheduled,
            'UnScheduled'               => $UnScheduled,
            'Total'                     => $Total,
            'Acheived'                  => $Acheived,
            'Required_Par'              => $Required_Par,
            'Star'                      => $Star,
            'Members_remaining'         => $Members_remaining,
            'db_col_name'               => $column_name,
        ];

        return $StatinTherapy;
    }

    private function MedicationAdherenceCholesterolStatinsHumana($doctor_id = "" , $insurance_id = "", $clinic_id = "", $careGap = "", $gaps_filterYear, $insurancePrvider)
    {
        $ADH_STATIN = [];

        $Title = "Medication Adherence for Cholesterol (Statins): Statins (ADH_STATIN)";
        $column_name = 'ma_cholesterol_gap';
        $ShortTitle = "ADH_STATIN";
        
        if ($careGap == 1) {
            $Title = "Medication Adherence for Cholesterl (Statins): Statins (ADH_STATIN) Insurance";
            $column_name = 'ma_cholesterol_gap_insurance';
        }
        
        // As per current status
        $ClosedPatients = HumanaCareGaps::thisYearGaps($gaps_filterYear)->with('patientinfo')->whereHas('patientinfo')->where($column_name,'Compliant');
        $OpenPatients = HumanaCareGaps::thisYearGaps($gaps_filterYear)->whereNotIn($column_name, ['Compliant', 'N/A']);

        // As per insurance
        $ClosedPatientsCareGapFile = HumanaCareGaps::thisYearGaps($gaps_filterYear)->with('patientinfo')->whereHas('patientinfo')->where('ma_cholesterol_gap_insurance','Compliant');

        $Refused = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, 'Patient Refused', $gaps_filterYear);
        $Scheduled = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, 'Scheduled', $gaps_filterYear);
        
        $Total = HumanaCareGaps::thisYearGaps($gaps_filterYear)->where($column_name, '!=', ['N/A']);
        
        if(!empty($doctor_id)){
            $ClosedPatients->where('doctor_id',$doctor_id);
            $ClosedPatientsCareGapFile->where('doctor_id',$doctor_id);
            $OpenPatients->where('doctor_id',$doctor_id);
            $Total->where('doctor_id',$doctor_id);
        }
        if(!empty($insurance_id)){
            $ClosedPatients->where('insurance_id',$insurance_id);
            $ClosedPatientsCareGapFile->where('insurance_id',$insurance_id);
            $OpenPatients->where('insurance_id',$insurance_id);
            $Total->where('insurance_id',$insurance_id);
        }
        if(!empty($clinic_id)){
            $clinic_id = explode(',', $clinic_id);
            $ClosedPatients->where('clinic_id',$clinic_id);
            $ClosedPatientsCareGapFile->where('clinic_id',$clinic_id);
            $OpenPatients->where('clinic_id',$clinic_id);
            $Total->where('clinic_id',$clinic_id);
        }
        $ActiveNonComp = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, "", $gaps_filterYear);
        $ClosedPatientsCareGapFile = $ClosedPatientsCareGapFile->count();
        $ClosedPatients = $ClosedPatients->count();
        $ClosedPatientsDifference = $ClosedPatients - $ClosedPatientsCareGapFile;
        $ClosedPatientsDifference = ($ClosedPatientsDifference>=1 ) ? (string) $ClosedPatientsDifference : '0' ;
        $OpenPatients = $OpenPatients->count();
        $Refused = $Refused;
        $Scheduled = $Scheduled;
        $UnScheduled = $ActiveNonComp - $Scheduled - $Refused ;
        $UnScheduled = ($UnScheduled>=1 ) ? (string) $UnScheduled : '0' ;
        $Total = $Total->count();
        // $Eligible = $Refused + $Scheduled + $UnScheduled;

        if($Total != 0) {
            $Acheived = number_format( $ClosedPatients * 100 / $Total , 1, '.', '') ;
        }else{
            $Acheived = "0";
        }

        $Required_Par = "90.7" ; 

        if($Acheived >= "90.7"){
            $Star = "5";
        }else if($Acheived >= "88"){
            $Star = "4";
        }else if($Acheived >= "85"){
            $Star = "3";
        }else if($Acheived >= "81"){
            $Star = "2";
        }else{
            $Star = "1";
        }

        $Members_remaining =  ((($Required_Par - $Acheived) * $Total) / 100)  ;  
        if($Members_remaining <= 0 ){
            $Members_remaining = "-";
        }else if($Members_remaining > 0 && $Members_remaining < 1){
            $Members_remaining = 1;
        }else{
            $Members_remaining = number_format($Members_remaining);
        }

        $ADH_STATIN = [
            'Title'                     => $Title,
            'ShortTitle'                => $ShortTitle,
            'ActiveNonComp'             => $ActiveNonComp,
            'ClosedPatients'            => $ClosedPatients,
            'ClosedPatientsDifference'  => $ClosedPatientsDifference,
            'OpenPatients'              => $OpenPatients,
            'Refused'                   => $Refused,
            'Scheduled'                 => $Scheduled,
            'UnScheduled'               => $UnScheduled,
            'Total'                     => $Total,
            'Acheived'                  => $Acheived,
            'Required_Par'              => $Required_Par,
            'Star'                      => $Star,
            'Members_remaining'         => $Members_remaining,
            'db_col_name'               => $column_name,
        ];

        return $ADH_STATIN;
    }

    private function MedicationAdherenceDiabetesMedicationsHumana($doctor_id = "" , $insurance_id = "", $clinic_id = "", $careGap = "", $gaps_filterYear, $insurancePrvider)
    {
        $ADH_DIAB = [];
        $Title = "Medication Adherence for Diabetes Medications: Diabetes Medications (ADH_DIAB)";
        $column_name = 'mad_medications_gap';
        $ShortTitle = "ADH_DIAB";
        
        if ($careGap == 1) {
            $Title = "Medication Adherence for Diabetes Medications: Diabetes Medications (ADH_DIAB) Insurance";
            $column_name = 'mad_medications_gap_insurance';
        }
        
        // As per current status
        $ClosedPatients = HumanaCareGaps::thisYearGaps($gaps_filterYear)->with('patientinfo')->whereHas('patientinfo')->where($column_name,'Compliant');
        $OpenPatients = HumanaCareGaps::thisYearGaps($gaps_filterYear)->whereNotIn($column_name, ['Compliant', 'N/A']);

        // As per insurance
        $ClosedPatientsCareGapFile = HumanaCareGaps::thisYearGaps($gaps_filterYear)->with('patientinfo')->whereHas('patientinfo')->where('mad_medications_gap_insurance','Compliant');

        $Refused = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, 'Patient Refused', $gaps_filterYear);
        $Scheduled = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, 'Scheduled', $gaps_filterYear);
        
        $Total = HumanaCareGaps::thisYearGaps($gaps_filterYear)->where($column_name, '!=', ['N/A']);
        
        if(!empty($doctor_id)){
            $ClosedPatients->where('doctor_id',$doctor_id);
            $ClosedPatientsCareGapFile->where('doctor_id',$doctor_id);
            $OpenPatients->where('doctor_id',$doctor_id);
            $Total->where('doctor_id',$doctor_id);
        }
        if(!empty($insurance_id)){
            $ClosedPatients->where('insurance_id',$insurance_id);
            $ClosedPatientsCareGapFile->where('insurance_id',$insurance_id);
            $OpenPatients->where('insurance_id',$insurance_id);
            $Total->where('insurance_id',$insurance_id);
        }
        if(!empty($clinic_id)){
            $clinic_id = explode(',', $clinic_id);
            $ClosedPatients->where('clinic_id',$clinic_id);
            $ClosedPatientsCareGapFile->where('clinic_id',$clinic_id);
            $OpenPatients->where('clinic_id',$clinic_id);
            $Total->where('clinic_id',$clinic_id);
        }
        $ActiveNonComp = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, "", $gaps_filterYear);
        $ClosedPatientsCareGapFile = $ClosedPatientsCareGapFile->count();
        $ClosedPatients = $ClosedPatients->count();
        $ClosedPatientsDifference = $ClosedPatients - $ClosedPatientsCareGapFile;
        $ClosedPatientsDifference = ($ClosedPatientsDifference>=1 ) ? (string) $ClosedPatientsDifference : '0' ;
        $OpenPatients = $OpenPatients->count();
        $Refused = $Refused;
        $Scheduled = $Scheduled;
        $UnScheduled = $ActiveNonComp - $Scheduled - $Refused ;
        $UnScheduled = ($UnScheduled>=1 ) ? (string) $UnScheduled : '0' ;
        $Total = $Total->count();
        // $Eligible = $Refused + $Scheduled + $UnScheduled;

        if($Total != 0) {
            $Acheived = number_format( $ClosedPatients * 100 / $Total , 1, '.', '') ;
        }else{
            $Acheived = "0";
        }

        $Required_Par = "90.4" ; 

        if($Acheived >= "90.4"){
            $Star = "5";
        }else if($Acheived >= "88"){
            $Star = "4";
        }else if($Acheived >= "85"){
            $Star = "3";
        }else if($Acheived >= "79"){
            $Star = "2";
        }else{
            $Star = "1";
        }

        $Members_remaining =  ((($Required_Par - $Acheived) * $Total) / 100)  ;  
        if($Members_remaining <= 0 ){
            $Members_remaining = "-";
        }else if($Members_remaining > 0 && $Members_remaining < 1){
            $Members_remaining = 1;
        }else{
            $Members_remaining = number_format($Members_remaining);
        }

        $ADH_DIAB = [
            'Title'                     => $Title,
            'ShortTitle'                => $ShortTitle,
            'ActiveNonComp'             => $ActiveNonComp,
            'ClosedPatients'            => $ClosedPatients,
            'ClosedPatientsDifference'  => $ClosedPatientsDifference,
            'OpenPatients'              => $OpenPatients,
            'Refused'                   => $Refused,
            'Scheduled'                 => $Scheduled,
            'UnScheduled'               => $UnScheduled,
            'Total'                     => $Total,
            'Acheived'                  => $Acheived,
            'Required_Par'              => $Required_Par,
            'Star'                      => $Star,
            'Members_remaining'         => $Members_remaining,
            'db_col_name'               => $column_name,
        ];

        return $ADH_DIAB;
    }

    private function MedicationAdherenceHypertensionHumana($doctor_id = "" , $insurance_id = "", $clinic_id = "", $careGap = "", $gaps_filterYear, $insurancePrvider)
    {
        $ADH_ACE = [];

        $Title = "Medication Adherence for Hypertension (ACE or ARB): ACE (ADH_ACE)";
        $column_name = 'ma_hypertension_gap';
        $ShortTitle = "ADH_ACE";
        
        if ($careGap == 1) {
            $Title = "Medication Adherence for Hypertension (ACE or ARB): ACE (ADH_ACE) Insurance";
            $column_name = 'ma_hypertension_gap_insurance';
        }
        
        // As per current status
        $ClosedPatients = HumanaCareGaps::thisYearGaps($gaps_filterYear)->with('patientinfo')->whereHas('patientinfo')->where($column_name,'Compliant');
        $OpenPatients = HumanaCareGaps::thisYearGaps($gaps_filterYear)->whereNotIn($column_name, ['Compliant', 'N/A']);

        // As per insurance
        $ClosedPatientsCareGapFile = HumanaCareGaps::thisYearGaps($gaps_filterYear)->with('patientinfo')->whereHas('patientinfo')->where('ma_hypertension_gap_insurance','Compliant');

        $Refused = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, 'Patient Refused', $gaps_filterYear);
        $Scheduled = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, 'Scheduled', $gaps_filterYear);
        
        $Total = HumanaCareGaps::thisYearGaps($gaps_filterYear)->where($column_name, '!=', ['N/A']);
        
        if(!empty($doctor_id)){
            $ClosedPatients->where('doctor_id',$doctor_id);
            $ClosedPatientsCareGapFile->where('doctor_id',$doctor_id);
            $OpenPatients->where('doctor_id',$doctor_id);
            $Total->where('doctor_id',$doctor_id);
        }
        if(!empty($insurance_id)){
            $ClosedPatients->where('insurance_id',$insurance_id);
            $ClosedPatientsCareGapFile->where('insurance_id',$insurance_id);
            $OpenPatients->where('insurance_id',$insurance_id);
            $Total->where('insurance_id',$insurance_id);
        }
        if(!empty($clinic_id)){
            $clinic_id = explode(',', $clinic_id);
            $ClosedPatients->where('clinic_id',$clinic_id);
            $ClosedPatientsCareGapFile->where('clinic_id',$clinic_id);
            $OpenPatients->where('clinic_id',$clinic_id);
            $Total->where('clinic_id',$clinic_id);
        }
        $ActiveNonComp = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, "", $gaps_filterYear);
        $ClosedPatientsCareGapFile = $ClosedPatientsCareGapFile->count();
        $ClosedPatients = $ClosedPatients->count();
        $ClosedPatientsDifference = $ClosedPatients - $ClosedPatientsCareGapFile;
        $ClosedPatientsDifference = ($ClosedPatientsDifference>=1 ) ? (string) $ClosedPatientsDifference : '0' ;
        $OpenPatients = $OpenPatients->count();
        $Refused = $Refused;
        $Scheduled = $Scheduled;
        $UnScheduled = $ActiveNonComp - $Scheduled - $Refused ;
        $UnScheduled = ($UnScheduled>=1 ) ? (string) $UnScheduled : '0' ;
        $Total = $Total->count();
        // $Eligible = $Refused + $Scheduled + $UnScheduled;

        if($Total != 0) {
            $Acheived = number_format( $ClosedPatients * 100 / $Total , 1, '.', '') ;
        }else{
            $Acheived = "0";
        }

        $Required_Par = "91" ; 

        if($Acheived >= "91"){
            $Star = "5";
        }else if($Acheived >= "89"){
            $Star = "4";
        }else if($Acheived >= "86"){
            $Star = "3";
        }else if($Acheived >= "78"){
            $Star = "2";
        }else{
            $Star = "1";
        }

        $Members_remaining =  ((($Required_Par - $Acheived) * $Total) / 100)  ;  
        if($Members_remaining <= 0 ){
            $Members_remaining = "-";
        }else if($Members_remaining > 0 && $Members_remaining < 1){
            $Members_remaining = 1;
        }else{
            $Members_remaining = number_format($Members_remaining);
        }

        $ADH_ACE = [
            'Title'                     => $Title,
            'ShortTitle'                => $ShortTitle,
            'ActiveNonComp'             => $ActiveNonComp,
            'ClosedPatients'            => $ClosedPatients,
            'ClosedPatientsDifference'  => $ClosedPatientsDifference,
            'OpenPatients'              => $OpenPatients,
            'Refused'                   => $Refused,
            'Scheduled'                 => $Scheduled,
            'UnScheduled'               => $UnScheduled,
            'Total'                     => $Total,
            'Acheived'                  => $Acheived,
            'Required_Par'              => $Required_Par,
            'Star'                      => $Star,
            'Members_remaining'         => $Members_remaining,
            'db_col_name'               => $column_name,
        ];

        return $ADH_ACE;    
    }

    private function MTM_CMR($doctor_id = "" , $insurance_id = "", $clinic_id = "", $careGap = "", $gaps_filterYear, $insurancePrvider)
    {
        $MTM_CMR = [];

        $Title = "D11-MTM CMR Current Year Status";
        $column_name = 'mtm_cmr_gap';
        $ShortTitle = "ADH_ACE";
        
        if ($careGap == 1) {
            $Title = "D11-MTM CMR Current Year Status Insurance";
            $column_name = 'mtm_cmr_gap_insurance';
        }
        
        // As per current status
        $ClosedPatients = UnitedHealthcareCareGaps::thisYearGaps($gaps_filterYear)->with('patientinfo')->whereHas('patientinfo')->where($column_name,'Compliant');
        $OpenPatients = UnitedHealthcareCareGaps::thisYearGaps($gaps_filterYear)->whereNotIn($column_name, ['Compliant', 'N/A']);

        // As per insurance
        $ClosedPatientsCareGapFile = UnitedHealthcareCareGaps::thisYearGaps($gaps_filterYear)->with('patientinfo')->whereHas('patientinfo')->where('mtm_cmr_gap_insurance','Compliant');

        $Refused = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, 'Patient Refused', $gaps_filterYear);
        $Scheduled = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, 'Scheduled', $gaps_filterYear);
        
        $Total = UnitedHealthcareCareGaps::thisYearGaps($gaps_filterYear)->where($column_name, '!=', ['N/A']);
        
        if(!empty($doctor_id)){
            $ClosedPatients->where('doctor_id',$doctor_id);
            $ClosedPatientsCareGapFile->where('doctor_id',$doctor_id);
            $OpenPatients->where('doctor_id',$doctor_id);
            $Total->where('doctor_id',$doctor_id);
        }
        if(!empty($insurance_id)){
            $ClosedPatients->where('insurance_id',$insurance_id);
            $ClosedPatientsCareGapFile->where('insurance_id',$insurance_id);
            $OpenPatients->where('insurance_id',$insurance_id);
            $Total->where('insurance_id',$insurance_id);
        }
        if(!empty($clinic_id)){
            $clinic_id = explode(',', $clinic_id);
            $ClosedPatients->where('clinic_id',$clinic_id);
            $ClosedPatientsCareGapFile->where('clinic_id',$clinic_id);
            $OpenPatients->where('clinic_id',$clinic_id);
            $Total->where('clinic_id',$clinic_id);
        }
        $ActiveNonComp = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, "", $gaps_filterYear);
        $ClosedPatientsCareGapFile = $ClosedPatientsCareGapFile->count();
        $ClosedPatients = $ClosedPatients->count();
        $ClosedPatientsDifference = $ClosedPatients - $ClosedPatientsCareGapFile;
        $ClosedPatientsDifference = ($ClosedPatientsDifference>=1 ) ? (string) $ClosedPatientsDifference : '0' ;
        $OpenPatients = $OpenPatients->count();
        $Refused = $Refused;
        $Scheduled = $Scheduled;
        $UnScheduled = $ActiveNonComp - $Scheduled - $Refused ;
        $UnScheduled = ($UnScheduled>=1 ) ? (string) $UnScheduled : '0' ;
        $Total = $Total->count();
        // $Eligible = $Refused + $Scheduled + $UnScheduled;

        if($Total != 0) {
            $Acheived = number_format( $ClosedPatients * 100 / $Total , 1, '.', '') ;
        }else{
            $Acheived = "0";
        }

        $Required_Par = "92.0" ; 

        if($Acheived >= $Required_Par){
            $Star = "5";
        }else if($Acheived >= "91"){
            $Star = "4";
        }else if($Acheived >= "90.0"){
            $Star = "3";
        }else if($Acheived >= "89.0"){
            $Star = "2";
        }else{
            $Star = "1";
        }

        $Members_remaining =  ((($Required_Par - $Acheived) * $Total) / 100)  ;  
        if($Members_remaining <= 0 ){
            $Members_remaining = "-";
        }else if($Members_remaining > 0 && $Members_remaining < 1){
            $Members_remaining = 1;
        }else{
            $Members_remaining = number_format($Members_remaining);
        }

        $MTM_CMR = [
            'Title'                     => $Title,
            'ShortTitle'                => $ShortTitle,
            'ActiveNonComp'             => $ActiveNonComp,
            'ClosedPatients'            => $ClosedPatients,
            'ClosedPatientsDifference'  => $ClosedPatientsDifference,
            'OpenPatients'              => $OpenPatients,
            'Refused'                   => $Refused,
            'Scheduled'                 => $Scheduled,
            'UnScheduled'               => $UnScheduled,
            'Total'                     => $Total,
            'Acheived'                  => $Acheived,
            'Required_Par'              => $Required_Par,
            'Star'                      => $Star,
            'Members_remaining'         => $Members_remaining,
            'db_col_name'               => $column_name,
        ];

        return $MTM_CMR;    
    }


    private function StatinPersonsDiabetes($doctor_id = "" , $insurance_id = "", $clinic_id = "", $careGap = "", $gaps_filterYear, $insurancePrvider)
    {
        // Find Models Name for care gaps 
        $CGModelsName = $this->FindModelsName($insurance_id, $insurancePrvider);

        $SUPD = [];
        $Title = "Statin Use in Persons with Diabetes (SUPD)";
        $column_name = 'sup_diabetes_gap';
        $ShortTitle = "SUPD";
        
        if ($careGap == 1) {
            $Title = "Statin Use in Persons with Diabetes (SUPD) Insurance";
            $column_name = 'sup_diabetes_gap_insurance';
        }



        // As per current status
        $ClosedPatients = $CGModelsName::thisYearGaps($gaps_filterYear)->with('patientinfo')->whereHas('patientinfo')->where($column_name,'Compliant');
        $OpenPatients = $CGModelsName::thisYearGaps($gaps_filterYear)->whereNotIn($column_name, ['Compliant', 'N/A']);

        // As per insurance
        $ClosedPatientsCareGapFile = $CGModelsName::thisYearGaps($gaps_filterYear)->with('patientinfo')->whereHas('patientinfo')->where('sup_diabetes_gap_insurance','Compliant');

        $Refused = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, 'Patient Refused', $gaps_filterYear);
        $Scheduled = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, 'Scheduled', $gaps_filterYear);
        
        $Total = $CGModelsName::thisYearGaps($gaps_filterYear)->where($column_name, '!=', ['N/A']);
        
        if(!empty($doctor_id)){
            $ClosedPatients->where('doctor_id',$doctor_id);
            $ClosedPatientsCareGapFile->where('doctor_id',$doctor_id);
            $OpenPatients->where('doctor_id',$doctor_id);
            $Total->where('doctor_id',$doctor_id);
        }
        if(!empty($insurance_id)){
            $ClosedPatients->where('insurance_id',$insurance_id);
            $ClosedPatientsCareGapFile->where('insurance_id',$insurance_id);
            $OpenPatients->where('insurance_id',$insurance_id);
            $Total->where('insurance_id',$insurance_id);
        }
        if(!empty($clinic_id)){
            $clinic_id = explode(',', $clinic_id);
            $ClosedPatients->where('clinic_id',$clinic_id);
            $ClosedPatientsCareGapFile->where('clinic_id',$clinic_id);
            $OpenPatients->where('clinic_id',$clinic_id);
            $Total->where('clinic_id',$clinic_id);
        }
        $ActiveNonComp = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, "", $gaps_filterYear);
        $ClosedPatientsCareGapFile = $ClosedPatientsCareGapFile->count();
        $ClosedPatients = $ClosedPatients->count();
        $ClosedPatientsDifference = $ClosedPatients - $ClosedPatientsCareGapFile;
        $ClosedPatientsDifference = ($ClosedPatientsDifference>=1 ) ? (string) $ClosedPatientsDifference : '0' ;
        $OpenPatients = $OpenPatients->count();
        $Refused = $Refused;
        $Scheduled = $Scheduled;
        $UnScheduled = $ActiveNonComp - $Scheduled - $Refused ;
        $UnScheduled = ($UnScheduled>=1 ) ? (string) $UnScheduled : '0' ;
        $Total = $Total->count();
        // $Eligible = $Refused + $Scheduled + $UnScheduled;

        if($Total != 0) {
            $Acheived = number_format( $ClosedPatients * 100 / $Total , 1, '.', '') ;
        }else{
            $Acheived = "0";
        }

        $SUPDReturn = $this->StatinPersonsDiabetesStarConditions($insurancePrvider, $Acheived);
        $Required_Par = $SUPDReturn['Required_Par'];
        $Star = $SUPDReturn['Star'];

        $Members_remaining =  ((($Required_Par - $Acheived) * $Total) / 100)  ;  
        if($Members_remaining <= 0 ){
            $Members_remaining = "-";
        }else if($Members_remaining > 0 && $Members_remaining < 1){
            $Members_remaining = 1;
        }else{
            $Members_remaining = number_format($Members_remaining);
        }

        $SUPD = [
            'Title'                     => $Title,
            'ShortTitle'                => $ShortTitle,
            'ActiveNonComp'             => $ActiveNonComp,
            'ClosedPatients'            => $ClosedPatients,
            'ClosedPatientsDifference'  => $ClosedPatientsDifference,
            'OpenPatients'              => $OpenPatients,
            'Refused'                   => $Refused,
            'Scheduled'                 => $Scheduled,
            'UnScheduled'               => $UnScheduled,
            'Total'                     => $Total,
            'Acheived'                  => $Acheived,
            'Required_Par'              => $Required_Par,
            'Star'                      => $Star,
            'Members_remaining'         => $Members_remaining,
            'db_col_name'               => $column_name,
        ];

        return $SUPD;
    }

    // ========================================  End Human ======================================= 
    // Medicare Arizona
    
    private function bloodSugarGap($doctor_id = "" , $insurance_id = "", $clinic_id = "", $careGap = "", $gaps_filterYear, $insurancePrvider) 
    {
        // Find Models Name for care gaps 
        $CGModelsName = $this->FindModelsName($insurance_id, $insurancePrvider);

        $BSC = [];
        $Title = "Diabetes Care - Blood Sugar Control (HBA1C)";
        $column_name = 'hba1c_gap';
        $ShortTitle = "HBA1C";
        
        if ($careGap == 1) {
            $Title = "Diabetes Care - Blood Sugar Control (HBA1C) Insurance";
            $column_name = 'hba1c_gap_insurance';
        }

        // As per current status
        $ClosedPatients = $CGModelsName::thisYearGaps($gaps_filterYear)->with('patientinfo')->whereHas('patientinfo')->where($column_name,'Compliant');
        $OpenPatients = $CGModelsName::thisYearGaps($gaps_filterYear)->whereNotIn($column_name, ['Compliant', 'N/A']);

        // As per insurance
        $ClosedPatientsCareGapFile = $CGModelsName::thisYearGaps($gaps_filterYear)->with('patientinfo')->whereHas('patientinfo')->where('hba1c_gap_insurance','Compliant');

        $Refused = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, 'Patient Refused', $gaps_filterYear);
        $Scheduled = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, 'Scheduled', $gaps_filterYear);
        
        $Total = $CGModelsName::thisYearGaps($gaps_filterYear)->where($column_name, '!=', ['N/A']);
        
        if(!empty($doctor_id)){
            $ClosedPatients->where('doctor_id',$doctor_id);
            $ClosedPatientsCareGapFile->where('doctor_id',$doctor_id);
            $OpenPatients->where('doctor_id',$doctor_id);
            $Total->where('doctor_id',$doctor_id);
        }
        if(!empty($insurance_id)){
            $ClosedPatients->where('insurance_id',$insurance_id);
            $ClosedPatientsCareGapFile->where('insurance_id',$insurance_id);
            $OpenPatients->where('insurance_id',$insurance_id);
            $Total->where('insurance_id',$insurance_id);
        }
        if(!empty($clinic_id)){
            $clinic_id = explode(',', $clinic_id);
            $ClosedPatients->where('clinic_id',$clinic_id);
            $ClosedPatientsCareGapFile->where('clinic_id',$clinic_id);
            $OpenPatients->where('clinic_id',$clinic_id);
            $Total->where('clinic_id',$clinic_id);
        }
        $ActiveNonComp = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, "", $gaps_filterYear);
        $ClosedPatientsCareGapFile = $ClosedPatientsCareGapFile->count();
        $ClosedPatients = $ClosedPatients->count();
        $ClosedPatientsDifference = $ClosedPatients - $ClosedPatientsCareGapFile;
        $ClosedPatientsDifference = ($ClosedPatientsDifference>=1 ) ? (string) $ClosedPatientsDifference : '0' ;
        $OpenPatients = $OpenPatients->count();
        $Refused = $Refused;
        $Scheduled = $Scheduled;
        $UnScheduled = $ActiveNonComp - $Scheduled - $Refused ;
        $UnScheduled = ($UnScheduled>=1 ) ? (string) $UnScheduled : '0' ;
        $Total = $Total->count();
        // $Eligible = $Refused + $Scheduled + $UnScheduled;

        if($Total != 0) {
            $Acheived = number_format( $ClosedPatients * 100 / $Total , 1, '.', '') ;
        }else{
            $Acheived = "0";
        }

        $BSC_Return = $this->bloodSugarGapStarConditions($insurancePrvider, $Acheived);
        //return $BSC_Return;
        $Required_Par = $BSC_Return['Required_Par'];
        $Star = $BSC_Return['Star'];

        $Members_remaining =  ((($Required_Par - $Acheived) * $Total) / 100)  ;  
        if($Members_remaining <= 0 ){
            $Members_remaining = "-";
        }else if($Members_remaining > 0 && $Members_remaining < 1){
            $Members_remaining = 1;
        }else{
            $Members_remaining = number_format($Members_remaining);
        }

        $BSC = [
            'Title'                     => $Title,
            'ShortTitle'                => $ShortTitle,
            'ActiveNonComp'             => $ActiveNonComp,
            'ClosedPatients'            => $ClosedPatients,
            'ClosedPatientsDifference'  => $ClosedPatientsDifference,
            'OpenPatients'              => $OpenPatients,
            'Refused'                   => $Refused,
            'Scheduled'                 => $Scheduled,
            'UnScheduled'               => $UnScheduled,
            'Total'                     => $Total,
            'Acheived'                  => $Acheived,
            'Required_Par'              => $Required_Par,
            'Star'                      => $Star,
            'Members_remaining'         => $Members_remaining,
            'db_col_name'               => $column_name,
        ];

        return $BSC;
    }

    // ========================================  End Medicare Arizona ======================================= 
    

    private function OsteoporosisManagementWomenAetnaMedicare($doctor_id = "" , $insurance_id = "", $clinic_id = "", $careGap = "" , $gaps_filterYear)
    {
        $OMW = [];

        $Title = "Osteoporosis Management in Women Who Had a Fracture (OMW)";
        $column_name = 'omw_fracture_gap';
        $ShortTitle = "OMW";
        
        if ($careGap == 1) {
            $Title = "Osteoporosis Management in Women Who Had a Fracture (OMW) Insurance";
            $column_name = 'omw_fracture_gap_insurance';
        }
                    
        // As per current status
        $ClosedPatients = AetnaMedicareCareGaps::thisYearGaps($gaps_filterYear)->with('patientinfo')->whereHas('patientinfo')->where($column_name, 'Compliant');
        $OpenPatients = AetnaMedicareCareGaps::thisYearGaps($gaps_filterYear)->whereNotIn($column_name, ['Compliant', 'N/A']);//where('omw_fracture_gap','Non-Compliant');

        // As per insurance
        $ClosedPatientsCareGapFile = AetnaMedicareCareGaps::thisYearGaps($gaps_filterYear)->with('patientinfo')->whereHas('patientinfo')->where('omw_fracture_gap_insurance','Compliant');//->where('source','CareGap_File');

        $Refused = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, 'Patient Refused', $gaps_filterYear);
        $Scheduled = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, 'Scheduled', $gaps_filterYear);
        
        $Total = AetnaMedicareCareGaps::thisYearGaps($gaps_filterYear)->where($column_name, '!=', ['N/A']);
        
        if(!empty($doctor_id)){
            $ClosedPatients->where('doctor_id',$doctor_id);
            $ClosedPatientsCareGapFile->where('doctor_id',$doctor_id);
            $OpenPatients->where('doctor_id',$doctor_id);
            $Total->where('doctor_id',$doctor_id);
        }
        if(!empty($insurance_id)){
            $ClosedPatients->where('insurance_id',$insurance_id);
            $ClosedPatientsCareGapFile->where('insurance_id',$insurance_id);
            $OpenPatients->where('insurance_id',$insurance_id);
            $Total->where('insurance_id',$insurance_id);
        }
        if(!empty($clinic_id)){
            $clinic_id = explode(',', $clinic_id);
            $ClosedPatients->where('clinic_id',$clinic_id);
            $ClosedPatientsCareGapFile->where('clinic_id',$clinic_id);
            $OpenPatients->where('clinic_id',$clinic_id);
            $Total->where('clinic_id',$clinic_id);
        }
        $ActiveNonComp = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, "", $gaps_filterYear);
        $ClosedPatientsCareGapFile = $ClosedPatientsCareGapFile->count();
        $ClosedPatients = $ClosedPatients->count();
        $ClosedPatientsDifference = $ClosedPatients - $ClosedPatientsCareGapFile;
        $ClosedPatientsDifference = ($ClosedPatientsDifference>=1 ) ? (string) $ClosedPatientsDifference : '0' ;
        $OpenPatients = $OpenPatients->count();
        $Refused = $Refused;
        $Scheduled = $Scheduled;
        $UnScheduled = $ActiveNonComp - $Scheduled - $Refused ;
        $UnScheduled = ($UnScheduled>=1 ) ? (string) $UnScheduled : '0' ;
        $Total = $Total->count();
        // $Eligible = $Refused + $Scheduled + $UnScheduled;

        if($Total != 0) {
            $Acheived = number_format( $ClosedPatients * 100 / $Total , 1, '.', '') ;
        }else{
            $Acheived = "0";
        }
        $Required_Par = "75" ; 

        if($Acheived >= "75"){
            $Star = "5";
        }else if($Acheived >= "57"){
            $Star = "4";
        }else if($Acheived >= "47"){
            $Star = "3";
        }else if($Acheived >= "34"){
            $Star = "2";
        }else{
            $Star = "1";
        }

        $Members_remaining =  ((($Required_Par - $Acheived) * $Total) / 100)  ;  
        if($Members_remaining <= 0 ){
            $Members_remaining = "-";
        }else if($Members_remaining > 0 && $Members_remaining < 1){
            $Members_remaining = 1;
        }else{
            $Members_remaining = number_format($Members_remaining);
        }

        $OMW = [
            'Title'                     => $Title,
            'ShortTitle'                => $ShortTitle,
            'ActiveNonComp'             => $ActiveNonComp,
            'ClosedPatients'            => $ClosedPatients,
            'ClosedPatientsDifference'  => $ClosedPatientsDifference,
            'OpenPatients'              => $OpenPatients,
            'Refused'                   => $Refused,
            'Scheduled'                 => $Scheduled,
            'UnScheduled'               => $UnScheduled,
            'Total'                     => $Total,
            'Acheived'                  => $Acheived,
            'Required_Par'              => $Required_Par,
            'Star'                      => $Star,
            'Members_remaining'         => $Members_remaining,
            'db_col_name'               => $column_name,
        ];
       
        return $OMW;
    }

    private function PlanAllCauseReadmissionsAetnaMedicare($doctor_id = "" , $insurance_id = "", $clinic_id = "", $careGap = "" , $gaps_filterYear)
    {

        $PCR = [];

        $Title = "Plan All-Cause Readmissions (PCR)";
        $column_name = 'pc_readmissions_gap';
        $ShortTitle = "PCR";
        
        if ($careGap == 1) {
            $Title = "Plan All-Cause Readmissions (PCR) Insurance";
            $column_name = 'pc_readmissions_gap_insurance';
        }
                    
        // As per current status
        $ClosedPatients = AetnaMedicareCareGaps::thisYearGaps($gaps_filterYear)->with('patientinfo')->whereHas('patientinfo')->where($column_name, 'Compliant');
        $OpenPatients = AetnaMedicareCareGaps::thisYearGaps($gaps_filterYear)->whereNotIn($column_name, ['Compliant', 'N/A']);//where('pc_readmissions_gap','Non-Compliant');

        // As per insurance
        $ClosedPatientsCareGapFile = AetnaMedicareCareGaps::thisYearGaps($gaps_filterYear)->with('patientinfo')->whereHas('patientinfo')->where('pc_readmissions_gap_insurance','Compliant');//->where('source','CareGap_File');

        $Refused = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, 'Patient Refused', $gaps_filterYear);
        $Scheduled = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, 'Scheduled', $gaps_filterYear);
        
        $Total = AetnaMedicareCareGaps::thisYearGaps($gaps_filterYear)->where($column_name, '!=', ['N/A']);
        
        if(!empty($doctor_id)){
            $ClosedPatients->where('doctor_id',$doctor_id);
            $ClosedPatientsCareGapFile->where('doctor_id',$doctor_id);
            $OpenPatients->where('doctor_id',$doctor_id);
            $Total->where('doctor_id',$doctor_id);
        }
        if(!empty($insurance_id)){
            $ClosedPatients->where('insurance_id',$insurance_id);
            $ClosedPatientsCareGapFile->where('insurance_id',$insurance_id);
            $OpenPatients->where('insurance_id',$insurance_id);
            $Total->where('insurance_id',$insurance_id);
        }
        if(!empty($clinic_id)){
            $clinic_id = explode(',', $clinic_id);
            $ClosedPatients->where('clinic_id',$clinic_id);
            $ClosedPatientsCareGapFile->where('clinic_id',$clinic_id);
            $OpenPatients->where('clinic_id',$clinic_id);
            $Total->where('clinic_id',$clinic_id);
        }
        $ActiveNonComp = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, "", $gaps_filterYear);
        $ClosedPatientsCareGapFile = $ClosedPatientsCareGapFile->count();
        $ClosedPatients = $ClosedPatients->count();
        $ClosedPatientsDifference = $ClosedPatients - $ClosedPatientsCareGapFile;
        $ClosedPatientsDifference = ($ClosedPatientsDifference>=1 ) ? (string) $ClosedPatientsDifference : '0' ;
        $OpenPatients = $OpenPatients->count();
        $Refused = $Refused;
        $Scheduled = $Scheduled;
        $UnScheduled = $ActiveNonComp - $Scheduled - $Refused ;
        $UnScheduled = ($UnScheduled>=1 ) ? (string) $UnScheduled : '0' ;
        $Total = $Total->count();
        // $Eligible = $Refused + $Scheduled + $UnScheduled;

        if($Total != 0) {
            $Acheived = number_format( $OpenPatients * 100 / $Total , 1, '.', '') ;
        }else{
            $Acheived = "0";
        }

        $Required_Par = "3" ; 

        if($Acheived <= "3"){
            $Star = "5";
        }else if($Acheived <= "10" && $Acheived > "9"){
            $Star = "4";
        }else if($Acheived <= "12" && $Acheived > "11"){
            $Star = "3";
        }else if($Acheived <= "14" && $Acheived > "15"){
            $Star = "2";
        }else{
            $Star = "1";
        }

        $Members_remaining =  ((($Required_Par - $Acheived) * $Total) / 100)  ;  
        if($Members_remaining <= 0 ){
            $Members_remaining = "-";
        }else if($Members_remaining > 0 && $Members_remaining < 1){
            $Members_remaining = 1;
        }else{
            $Members_remaining = number_format($Members_remaining);
        }

        $PCR = [
            'Title'                     => $Title,
            'ShortTitle'                => $ShortTitle,
            'ActiveNonComp'             => $ActiveNonComp,
            'ClosedPatients'            => $ClosedPatients,
            'ClosedPatientsDifference'  => $ClosedPatientsDifference,
            'OpenPatients'              => $OpenPatients,
            'Refused'                   => $Refused,
            'Scheduled'                 => $Scheduled,
            'UnScheduled'               => $UnScheduled,
            'Total'                     => $Total,
            'Acheived'                  => $Acheived,
            'Required_Par'              => $Required_Par,
            'Star'                      => $Star,
            'Members_remaining'         => $Members_remaining,
            'db_col_name'               => $column_name,
        ];
       
        return $PCR;
    }


    private function MedicationAdherenceCholesterolStatinsAetnaMedicare($doctor_id = "" , $insurance_id = "", $clinic_id = "", $careGap = "" , $gaps_filterYear)
    {
        $ADH_STATIN = [];

        $Title = "Medication Adherence for Cholesterol (Statins): Statins (ADH_STATIN)";
        $column_name = 'ma_cholesterol_gap';
        $ShortTitle = "ADH_STATIN";
        
        if ($careGap == 1) {
            $Title = "Medication Adherence for Cholesterol (Statins): Statins (ADH_STATIN) Insurance";
            $column_name = 'ma_cholesterol_gap_insurance';
        }
       
        // As per current status
        $ClosedPatients = AetnaMedicareCareGaps::thisYearGaps($gaps_filterYear)->with('patientinfo')->whereHas('patientinfo')->where($column_name, 'Compliant');
        $OpenPatients = AetnaMedicareCareGaps::thisYearGaps($gaps_filterYear)->whereNotIn($column_name, ['Compliant', 'N/A']);//where('ma_cholesterol_gap','Non-Compliant');

        // As per insurance
        $ClosedPatientsCareGapFile = AetnaMedicareCareGaps::thisYearGaps($gaps_filterYear)->with('patientinfo')->whereHas('patientinfo')->where('ma_cholesterol_gap_insurance','Compliant');//->where('source','CareGap_File');

        $Refused = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, 'Patient Refused', $gaps_filterYear);
        $Scheduled = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, 'Scheduled', $gaps_filterYear);
        
        $Total = AetnaMedicareCareGaps::thisYearGaps($gaps_filterYear)->where($column_name, '!=', ['N/A']);
        
        if(!empty($doctor_id)){
            $ClosedPatients->where('doctor_id',$doctor_id);
            $ClosedPatientsCareGapFile->where('doctor_id',$doctor_id);
            $OpenPatients->where('doctor_id',$doctor_id);
            $Total->where('doctor_id',$doctor_id);
        }
        if(!empty($insurance_id)){
            $ClosedPatients->where('insurance_id',$insurance_id);
            $ClosedPatientsCareGapFile->where('insurance_id',$insurance_id);
            $OpenPatients->where('insurance_id',$insurance_id);
            $Total->where('insurance_id',$insurance_id);
        }
        if(!empty($clinic_id)){
            $clinic_id = explode(',', $clinic_id);
            $ClosedPatients->where('clinic_id',$clinic_id);
            $ClosedPatientsCareGapFile->where('clinic_id',$clinic_id);
            $OpenPatients->where('clinic_id',$clinic_id);
            $Total->where('clinic_id',$clinic_id);
        }
        $ActiveNonComp = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, "", $gaps_filterYear);
        $ClosedPatientsCareGapFile = $ClosedPatientsCareGapFile->count();
        $ClosedPatients = $ClosedPatients->count();
        $ClosedPatientsDifference = $ClosedPatients - $ClosedPatientsCareGapFile;
        $ClosedPatientsDifference = ($ClosedPatientsDifference>=1 ) ? (string) $ClosedPatientsDifference : '0' ;
        $OpenPatients = $OpenPatients->count();
        $Refused = $Refused;
        $Scheduled = $Scheduled;
        $UnScheduled = $ActiveNonComp - $Scheduled - $Refused ;
        $UnScheduled = ($UnScheduled>=1 ) ? (string) $UnScheduled : '0' ;
        $Total = $Total->count();
        // $Eligible = $Refused + $Scheduled + $UnScheduled;

        if($Total != 0) {
            $Acheived = number_format( $ClosedPatients * 100 / $Total , 1, '.', '') ;
        }else{
            $Acheived = "0";
        }

        $Required_Par = "94" ; 

        if($Acheived >= "94"){
            $Star = "5";
        }else if($Acheived >= "90"){
            $Star = "4";
        }else if($Acheived >= "87"){
            $Star = "3";
        }else if($Acheived >= "83"){
            $Star = "2";
        }else{
            $Star = "1";
        }

        $Members_remaining =  ((($Required_Par - $Acheived) * $Total) / 100)  ;  
        if($Members_remaining <= 0 ){
            $Members_remaining = "-";
        }else if($Members_remaining > 0 && $Members_remaining < 1){
            $Members_remaining = 1;
        }else{
            $Members_remaining = number_format($Members_remaining);
        }

        $ADH_STATIN = [
            'Title'                     => $Title,
            'ShortTitle'                => $ShortTitle,
            'ActiveNonComp'             => $ActiveNonComp,
            'ClosedPatients'            => $ClosedPatients,
            'ClosedPatientsDifference'  => $ClosedPatientsDifference,
            'OpenPatients'              => $OpenPatients,
            'Refused'                   => $Refused,
            'Scheduled'                 => $Scheduled,
            'UnScheduled'               => $UnScheduled,
            'Total'                     => $Total,
            'Acheived'                  => $Acheived,
            'Required_Par'              => $Required_Par,
            'Star'                      => $Star,
            'Members_remaining'         => $Members_remaining,
            'db_col_name'               => $column_name,
        ];
       
        return $ADH_STATIN;
    }

    private function MedicationAdherenceDiabetesMedicationsAetnaMedicare($doctor_id = "" , $insurance_id = "", $clinic_id = "", $careGap = "" , $gaps_filterYear)
    {
        $ADH_DIAB = [];

        $Title = "Medication Adherence for Diabetes Medications: Diabetes Medications (ADH_DIAB)";
        $column_name = 'mad_medications_gap';
        $ShortTitle = "ADH_DIAB";
        
        if ($careGap == 1) {
            $Title = "Medication Adherence for Diabetes Medications: Diabetes Medications (ADH_DIAB) Insurance";
            $column_name = 'mad_medications_gap_insurance';
        }
        
        // As per current status
        $ClosedPatients = AetnaMedicareCareGaps::thisYearGaps($gaps_filterYear)->with('patientinfo')->whereHas('patientinfo')->where($column_name, 'Compliant');
        $OpenPatients = AetnaMedicareCareGaps::thisYearGaps($gaps_filterYear)->whereNotIn($column_name, ['Compliant', 'N/A']);//where('mad_medications_gap','Non-Compliant');

        // As per insurance
        $ClosedPatientsCareGapFile = AetnaMedicareCareGaps::thisYearGaps($gaps_filterYear)->with('patientinfo')->whereHas('patientinfo')->where('mad_medications_gap_insurance','Compliant');//->where('source','CareGap_File');

        $Refused = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, 'Patient Refused', $gaps_filterYear);
        $Scheduled = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, 'Scheduled', $gaps_filterYear);
        
        $Total = AetnaMedicareCareGaps::thisYearGaps($gaps_filterYear)->where($column_name, '!=', ['N/A']);
        
        if(!empty($doctor_id)){
            $ClosedPatients->where('doctor_id',$doctor_id);
            $ClosedPatientsCareGapFile->where('doctor_id',$doctor_id);
            $OpenPatients->where('doctor_id',$doctor_id);
            $Total->where('doctor_id',$doctor_id);
        }
        if(!empty($insurance_id)){
            $ClosedPatients->where('insurance_id',$insurance_id);
            $ClosedPatientsCareGapFile->where('insurance_id',$insurance_id);
            $OpenPatients->where('insurance_id',$insurance_id);
            $Total->where('insurance_id',$insurance_id);
        }
        if(!empty($clinic_id)){
            $clinic_id = explode(',', $clinic_id);
            $ClosedPatients->where('clinic_id',$clinic_id);
            $ClosedPatientsCareGapFile->where('clinic_id',$clinic_id);
            $OpenPatients->where('clinic_id',$clinic_id);
            $Total->where('clinic_id',$clinic_id);
        }
        $ActiveNonComp = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, "", $gaps_filterYear);
        $ClosedPatientsCareGapFile = $ClosedPatientsCareGapFile->count();
        $ClosedPatients = $ClosedPatients->count();
        $ClosedPatientsDifference = $ClosedPatients - $ClosedPatientsCareGapFile;
        $ClosedPatientsDifference = ($ClosedPatientsDifference>=1 ) ? (string) $ClosedPatientsDifference : '0' ;
        $OpenPatients = $OpenPatients->count();
        $Refused = $Refused;
        $Scheduled = $Scheduled;
        $UnScheduled = $ActiveNonComp - $Scheduled - $Refused ;
        $UnScheduled = ($UnScheduled>=1 ) ? (string) $UnScheduled : '0' ;
        $Total = $Total->count();
        // $Eligible = $Refused + $Scheduled + $UnScheduled;

        if($Total != 0) {
            $Acheived = number_format( $ClosedPatients * 100 / $Total , 1, '.', '') ;
        }else{
            $Acheived = "0";
        }

        $Required_Par = "94" ; 

        if($Acheived >= "94"){
            $Star = "5";
        }else if($Acheived >= "90"){
            $Star = "4";
        }else if($Acheived >= "87"){
            $Star = "3";
        }else if($Acheived >= "82"){
            $Star = "2";
        }else{
            $Star = "1";
        }

        $Members_remaining =  ((($Required_Par - $Acheived) * $Total) / 100)  ;  
        if($Members_remaining <= 0 ){
            $Members_remaining = "-";
        }else if($Members_remaining > 0 && $Members_remaining < 1){
            $Members_remaining = 1;
        }else{
            $Members_remaining = number_format($Members_remaining);
        }

        $ADH_DIAB = [
            'Title'                     => $Title,
            'ShortTitle'                => $ShortTitle,
            'ActiveNonComp'             => $ActiveNonComp,
            'ClosedPatients'            => $ClosedPatients,
            'ClosedPatientsDifference'  => $ClosedPatientsDifference,
            'OpenPatients'              => $OpenPatients,
            'Refused'                   => $Refused,
            'Scheduled'                 => $Scheduled,
            'UnScheduled'               => $UnScheduled,
            'Total'                     => $Total,
            'Acheived'                  => $Acheived,
            'Required_Par'              => $Required_Par,
            'Star'                      => $Star,
            'Members_remaining'         => $Members_remaining,
            'db_col_name'               => $column_name,
        ];
       
        return $ADH_DIAB;
    }

    private function MedicationAdherenceHypertensionAetnaMedicare($doctor_id = "" , $insurance_id = "", $clinic_id = "", $careGap = "" , $gaps_filterYear)
    {
        $ADH_ACE = [];

        $Title = "Medication Adherence for Hypertension (ACE or ARB): ACE (ADH_ACE)";
        $column_name = 'ma_hypertension_gap';
        $ShortTitle = "ADH_ACE";
        
        if ($careGap == 1) {
            $Title = "Medication Adherence for Hypertension (ACE or ARB): ACE (ADH_ACE) Insurance";
            $column_name = 'ma_hypertension_gap_insurance';
        }
                    
        // As per current status
        $ClosedPatients = AetnaMedicareCareGaps::thisYearGaps($gaps_filterYear)->with('patientinfo')->whereHas('patientinfo')->where($column_name, 'Compliant');
        $OpenPatients = AetnaMedicareCareGaps::thisYearGaps($gaps_filterYear)->whereNotIn($column_name, ['Compliant', 'N/A']);//where('ma_hypertension_gap','Non-Compliant');

        // As per insurance
        $ClosedPatientsCareGapFile = AetnaMedicareCareGaps::thisYearGaps($gaps_filterYear)->with('patientinfo')->whereHas('patientinfo')->where('ma_hypertension_gap_insurance','Compliant');//->where('source','CareGap_File');

        $Refused = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, 'Patient Refused', $gaps_filterYear);
        $Scheduled = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, 'Scheduled', $gaps_filterYear);
        
        $Total = AetnaMedicareCareGaps::thisYearGaps($gaps_filterYear)->where($column_name, '!=', ['N/A']);
        
        if(!empty($doctor_id)){
            $ClosedPatients->where('doctor_id',$doctor_id);
            $ClosedPatientsCareGapFile->where('doctor_id',$doctor_id);
            $OpenPatients->where('doctor_id',$doctor_id);
            $Total->where('doctor_id',$doctor_id);
        }
        if(!empty($insurance_id)){
            $ClosedPatients->where('insurance_id',$insurance_id);
            $ClosedPatientsCareGapFile->where('insurance_id',$insurance_id);
            $OpenPatients->where('insurance_id',$insurance_id);
            $Total->where('insurance_id',$insurance_id);
        }
        if(!empty($clinic_id)){
            $clinic_id = explode(',', $clinic_id);
            $ClosedPatients->where('clinic_id',$clinic_id);
            $ClosedPatientsCareGapFile->where('clinic_id',$clinic_id);
            $OpenPatients->where('clinic_id',$clinic_id);
            $Total->where('clinic_id',$clinic_id);
        }
        $ActiveNonComp = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, "", $gaps_filterYear);
        $ClosedPatientsCareGapFile = $ClosedPatientsCareGapFile->count();
        $ClosedPatients = $ClosedPatients->count();
        $ClosedPatientsDifference = $ClosedPatients - $ClosedPatientsCareGapFile;
        $ClosedPatientsDifference = ($ClosedPatientsDifference>=1 ) ? (string) $ClosedPatientsDifference : '0' ;
        $OpenPatients = $OpenPatients->count();
        $Refused = $Refused;
        $Scheduled = $Scheduled;
        $UnScheduled = $ActiveNonComp - $Scheduled - $Refused ;
        $UnScheduled = ($UnScheduled>=1 ) ? (string) $UnScheduled : '0' ;
        $Total = $Total->count();
        // $Eligible = $Refused + $Scheduled + $UnScheduled;

        if($Total != 0) {
            $Acheived = number_format( $ClosedPatients * 100 / $Total , 1, '.', '') ;
        }else{
            $Acheived = "0";
        }

        $Required_Par = "93" ; 

        if($Acheived >= "93"){
            $Star = "5";
        }else if($Acheived >= "91"){
            $Star = "4";
        }else if($Acheived >= "88"){
            $Star = "3";
        }else if($Acheived >= "80"){
            $Star = "2";
        }else{
            $Star = "1";
        }

        $Members_remaining =  ((($Required_Par - $Acheived) * $Total) / 100)  ;  
        if($Members_remaining <= 0 ){
            $Members_remaining = "-";
        }else if($Members_remaining > 0 && $Members_remaining < 1){
            $Members_remaining = 1;
        }else{
            $Members_remaining = number_format($Members_remaining);
        }

        $ADH_ACE = [
            'Title'                     => $Title,
            'ShortTitle'                => $ShortTitle,
            'ActiveNonComp'             => $ActiveNonComp,
            'ClosedPatients'            => $ClosedPatients,
            'ClosedPatientsDifference'  => $ClosedPatientsDifference,
            'OpenPatients'              => $OpenPatients,
            'Refused'                   => $Refused,
            'Scheduled'                 => $Scheduled,
            'UnScheduled'               => $UnScheduled,
            'Total'                     => $Total,
            'Acheived'                  => $Acheived,
            'Required_Par'              => $Required_Par,
            'Star'                      => $Star,
            'Members_remaining'         => $Members_remaining,
            'db_col_name'               => $column_name,
        ];
       
        return $ADH_ACE;
    }

    private function StatinPersonsDiabetesAetnaMedicare($doctor_id = "" , $insurance_id = "", $clinic_id = "", $careGap = "" , $gaps_filterYear)
    {
        $SUPD = [];

        $Title = "Statin Use in Persons with Diabetes (SUPD)";
        $column_name = 'sup_diabetes_gap';
        $ShortTitle = "SUPD";
        
        if ($careGap == 1) {
            $Title = "Statin Use in Persons with Diabetes (SUPD)Insurance";
            $column_name = 'sup_diabetes_gap_insurance';
        }
        
        // As per current status
        $ClosedPatients = AetnaMedicareCareGaps::thisYearGaps($gaps_filterYear)->with('patientinfo')->whereHas('patientinfo')->where($column_name, 'Compliant');
        $OpenPatients = AetnaMedicareCareGaps::thisYearGaps($gaps_filterYear)->whereNotIn($column_name, ['Compliant', 'N/A']);//where('sup_diabetes_gap','Non-Compliant');

        // As per insurance
        $ClosedPatientsCareGapFile = AetnaMedicareCareGaps::thisYearGaps($gaps_filterYear)->with('patientinfo')->whereHas('patientinfo')->where('sup_diabetes_gap_insurance','Compliant');//->where('source','CareGap_File');

        $Refused = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, 'Patient Refused', $gaps_filterYear);
        $Scheduled = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, 'Scheduled', $gaps_filterYear);
        
        $Total = AetnaMedicareCareGaps::thisYearGaps($gaps_filterYear)->where($column_name, '!=', ['N/A']);
        
        if(!empty($doctor_id)){
            $ClosedPatients->where('doctor_id',$doctor_id);
            $ClosedPatientsCareGapFile->where('doctor_id',$doctor_id);
            $OpenPatients->where('doctor_id',$doctor_id);
            $Total->where('doctor_id',$doctor_id);
        }
        if(!empty($insurance_id)){
            $ClosedPatients->where('insurance_id',$insurance_id);
            $ClosedPatientsCareGapFile->where('insurance_id',$insurance_id);
            $OpenPatients->where('insurance_id',$insurance_id);
            $Total->where('insurance_id',$insurance_id);
        }
        if(!empty($clinic_id)){
            $clinic_id = explode(',', $clinic_id);
            $ClosedPatients->where('clinic_id',$clinic_id);
            $ClosedPatientsCareGapFile->where('clinic_id',$clinic_id);
            $OpenPatients->where('clinic_id',$clinic_id);
            $Total->where('clinic_id',$clinic_id);
        }
        $ActiveNonComp = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, "", $gaps_filterYear);
        $ClosedPatientsCareGapFile = $ClosedPatientsCareGapFile->count();
        $ClosedPatients = $ClosedPatients->count();
        $ClosedPatientsDifference = $ClosedPatients - $ClosedPatientsCareGapFile;
        $ClosedPatientsDifference = ($ClosedPatientsDifference>=1 ) ? (string) $ClosedPatientsDifference : '0' ;
        $OpenPatients = $OpenPatients->count();
        $Refused = $Refused;
        $Scheduled = $Scheduled;
        $UnScheduled = $ActiveNonComp - $Scheduled - $Refused ;
        $UnScheduled = ($UnScheduled>=1 ) ? (string) $UnScheduled : '0' ;
        $Total = $Total->count();
        // $Eligible = $Refused + $Scheduled + $UnScheduled;

        if($Total != 0) {
            $Acheived = number_format( $ClosedPatients * 100 / $Total , 1, '.', '') ;
        }else{
            $Acheived = "0";
        }


        $Members_remaining =  ((($Required_Par - $Acheived) * $Total) / 100)  ;  
        if($Members_remaining <= 0 ){
            $Members_remaining = "-";
        }else if($Members_remaining > 0 && $Members_remaining < 1){
            $Members_remaining = 1;
        }else{
            $Members_remaining = number_format($Members_remaining);
        }

        $SUPD = [
            'Title'                     => $Title,
            'ShortTitle'                => $ShortTitle,
            'ActiveNonComp'             => $ActiveNonComp,
            'ClosedPatients'            => $ClosedPatients,
            'ClosedPatientsDifference'  => $ClosedPatientsDifference,
            'OpenPatients'              => $OpenPatients,
            'Refused'                   => $Refused,
            'Scheduled'                 => $Scheduled,
            'UnScheduled'               => $UnScheduled,
            'Total'                     => $Total,
            'Acheived'                  => $Acheived,
            'Required_Par'              => $Required_Par,
            'Star'                      => $Star,
            'Members_remaining'         => $Members_remaining,
            'db_col_name'               => $column_name,
        ];
       
        return $SUPD;
    }

    // ========================================  End AETNA ======================================= 


    private function multipleHighRiskChronicConditionsAllwellMedicare($doctor_id = "" , $insurance_id = "", $clinic_id = "", $careGap = "", $gaps_filterYear, $insurancePrvider)
    {
        $HighRisk = [];
        $Title = "FMC - F/U ED Multiple High Risk Chronic Conditions";
        $column_name = 'm_high_risk_cc_gap';
        $ShortTitle = "HighRisk";
        
        if ($careGap == 1) {
            $Title = "FMC - F/U ED Multiple High Risk Chronic Conditions Insurance";
            $column_name = 'm_high_risk_cc_gap_insurance';
        }

        // As per current status
        $ClosedPatients = AllwellMedicareCareGaps::thisYearGaps($gaps_filterYear)->with('patientinfo')->whereHas('patientinfo')->where($column_name,'Compliant');
        $OpenPatients = AllwellMedicareCareGaps::thisYearGaps($gaps_filterYear)->whereNotIn($column_name, ['Compliant', 'N/A']);
        
        // As per insurance
        $ClosedPatientsCareGapFile = AllwellMedicareCareGaps::thisYearGaps($gaps_filterYear)->with('patientinfo')->whereHas('patientinfo')->where('m_high_risk_cc_gap_insurance','Compliant');
        
        $Refused = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, 'Patient Refused', $gaps_filterYear);
        $Scheduled = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, 'Scheduled', $gaps_filterYear);
        
        $Total = AllwellMedicareCareGaps::thisYearGaps($gaps_filterYear)->where($column_name, '!=', ['N/A']);

        if(!empty($doctor_id)){
            $doctor_id = (string) $doctor_id;
            $ClosedPatientsCareGapFile->where('doctor_id',$doctor_id);
            $ClosedPatients->where('doctor_id',$doctor_id);
            $OpenPatients->where('doctor_id',$doctor_id); 
            $Total->where('doctor_id',$doctor_id);
            
        }
        if(!empty($insurance_id)){
            $ClosedPatientsCareGapFile->where('insurance_id',$insurance_id);
            $ClosedPatients->where('insurance_id',$insurance_id);
            $OpenPatients->where('insurance_id',$insurance_id);
            $Total->where('insurance_id',$insurance_id);
        }
        if(!empty($clinic_id)){
            $clinic_id = explode(',', $clinic_id);
            $ClosedPatientsCareGapFile->where('clinic_id',$clinic_id);
            $ClosedPatients->where('clinic_id',$clinic_id);
            $OpenPatients->where('clinic_id',$clinic_id);        
            $Total->where('clinic_id',$clinic_id);
        }

        $ActiveNonComp = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, "", $gaps_filterYear);
        $ClosedPatientsCareGapFile = $ClosedPatientsCareGapFile->count();
        $ClosedPatients = $ClosedPatients->count();
        $ClosedPatientsDifference = $ClosedPatients - $ClosedPatientsCareGapFile;
        $ClosedPatientsDifference = ($ClosedPatientsDifference>=1 ) ? (string) $ClosedPatientsDifference : '0' ;
        $OpenPatients = $OpenPatients->count();
        $Refused = $Refused;
        $Scheduled = $Scheduled;
        $UnScheduled = $ActiveNonComp - $Scheduled - $Refused;
        $UnScheduled = ($UnScheduled>=1 ) ? (string) $UnScheduled : '0' ; 
        $Total = $Total->count();
        // $Eligible = $Refused + $Scheduled + $UnScheduled;

        if($Total != 0) {
            $Acheived = number_format( $ClosedPatients * 100 / $Total , 1, '.', '') ;
        } else {
            $Acheived = "0";
        }
        
        $Required_Par = "74";
        $Star = "-";

        if($Acheived >= "74") {
            $Star = "5";
        } else if($Acheived >= "64") {
            $Star = "4";
        } else if($Acheived >= "54") {
            $Star = "3";
        } elseif ($Acheived >= "41")  {
            $Star = "2";
        } else{
            $Star = "1";
        }

        $Members_remaining =  ((($Required_Par - $Acheived) * $Total) / 100)  ;  
        if($Members_remaining <= 0 ){
            $Members_remaining = "-";
        }else if($Members_remaining > 0 && $Members_remaining < 1){
            $Members_remaining = 1;
        }else{
            $Members_remaining = number_format($Members_remaining);
        }

        $HighRisk = [
            'Title'                     => $Title,
            'ShortTitle'                => $ShortTitle,
            'ActiveNonComp'             => $ActiveNonComp,
            'ClosedPatients'            => $ClosedPatients,
            'ClosedPatientsDifference'  => $ClosedPatientsDifference,
            'OpenPatients'              => $OpenPatients,
            'Refused'                   => $Refused,
            'Scheduled'                 => $Scheduled,
            'UnScheduled'               => $UnScheduled,
            'Total'                     => $Total,
            'Acheived'                  => $Acheived,
            'Required_Par'              => $Required_Par,
            'Star'                      => $Star,
            'Members_remaining'         => $Members_remaining,
            'db_col_name'               => $column_name,
        ];

        return $HighRisk;
    }

    private function medAdherenceDiabeticAllwellMedicare($doctor_id = "" , $insurance_id = "", $clinic_id = "", $careGap = "", $gaps_filterYear, $insurancePrvider)
    {
        $MedAdherence = [];
        $Title = "Med Adherence - Diabetic";
        $column_name = 'med_adherence_diabetic_gap';
        $ShortTitle = "DIAB";
        
        if ($careGap == 1) {
            $Title = "Med Adherence - Diabetic Insurance";
            $column_name = 'med_adherence_diabetic_gap_insurance';
        }

        // As per current status
        $ClosedPatients = AllwellMedicareCareGaps::thisYearGaps($gaps_filterYear)->with('patientinfo')->whereHas('patientinfo')->where($column_name,'Compliant');
        $OpenPatients = AllwellMedicareCareGaps::thisYearGaps($gaps_filterYear)->whereNotIn($column_name, ['Compliant', 'N/A']);
        
        // As per insurance
        $ClosedPatientsCareGapFile = AllwellMedicareCareGaps::thisYearGaps($gaps_filterYear)->with('patientinfo')->whereHas('patientinfo')->where('med_adherence_diabetic_gap_insurance','Compliant');
        
        $Refused = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, 'Patient Refused', $gaps_filterYear);
        $Scheduled = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, 'Scheduled', $gaps_filterYear);
        
        $Total = AllwellMedicareCareGaps::thisYearGaps($gaps_filterYear)->where($column_name, '!=', ['N/A']);

        if(!empty($doctor_id)){
            $doctor_id = (string) $doctor_id;
            $ClosedPatientsCareGapFile->where('doctor_id',$doctor_id);
            $ClosedPatients->where('doctor_id',$doctor_id);
            $OpenPatients->where('doctor_id',$doctor_id); 
            $Total->where('doctor_id',$doctor_id);
            
        }
        if(!empty($insurance_id)){
            $ClosedPatientsCareGapFile->where('insurance_id',$insurance_id);
            $ClosedPatients->where('insurance_id',$insurance_id);
            $OpenPatients->where('insurance_id',$insurance_id);
            $Total->where('insurance_id',$insurance_id);
        }
        if(!empty($clinic_id)){
            $clinic_id = explode(',', $clinic_id);
            $ClosedPatientsCareGapFile->where('clinic_id',$clinic_id);
            $ClosedPatients->where('clinic_id',$clinic_id);
            $OpenPatients->where('clinic_id',$clinic_id);        
            $Total->where('clinic_id',$clinic_id);
        }

        $ActiveNonComp = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, "", $gaps_filterYear);
        $ClosedPatientsCareGapFile = $ClosedPatientsCareGapFile->count();
        $ClosedPatients = $ClosedPatients->count();
        $ClosedPatientsDifference = $ClosedPatients - $ClosedPatientsCareGapFile;
        $ClosedPatientsDifference = ($ClosedPatientsDifference>=1 ) ? (string) $ClosedPatientsDifference : '0' ;
        $OpenPatients = $OpenPatients->count();
        $Refused = $Refused;
        $Scheduled = $Scheduled;
        $UnScheduled = $ActiveNonComp - $Scheduled - $Refused;
        $UnScheduled = ($UnScheduled>=1 ) ? (string) $UnScheduled : '0' ; 
        $Total = $Total->count();
        // $Eligible = $Refused + $Scheduled + $UnScheduled;

        if($Total != 0) {
            $Acheived = number_format( $ClosedPatients * 100 / $Total , 1, '.', '') ;
        } else {
            $Acheived = "0";
        }
        
        $Required_Par = "100";
        $Star = "-";

        if($Acheived >= "100") {
            $Star = "5";
        } else if($Acheived >= "99") {
            $Star = "4";
        } else if($Acheived >= "97") {
            $Star = "3";
        } elseif ($Acheived >= "92")  {
            $Star = "2";
        } else{
            $Star = "1";
        }

        $Members_remaining =  ((($Required_Par - $Acheived) * $Total) / 100)  ;  
        if($Members_remaining <= 0 ){
            $Members_remaining = "-";
        }else if($Members_remaining > 0 && $Members_remaining < 1){
            $Members_remaining = 1;
        }else{
            $Members_remaining = number_format($Members_remaining);
        }

        $MedAdherenceDiabetic = [
            'Title'                     => $Title,
            'ShortTitle'                => $ShortTitle,
            'ActiveNonComp'             => $ActiveNonComp,
            'ClosedPatients'            => $ClosedPatients,
            'ClosedPatientsDifference'  => $ClosedPatientsDifference,
            'OpenPatients'              => $OpenPatients,
            'Refused'                   => $Refused,
            'Scheduled'                 => $Scheduled,
            'UnScheduled'               => $UnScheduled,
            'Total'                     => $Total,
            'Acheived'                  => $Acheived,
            'Required_Par'              => $Required_Par,
            'Star'                      => $Star,
            'Members_remaining'         => $Members_remaining,
            'db_col_name'               => $column_name,
        ];

        return $MedAdherenceDiabetic;
    }
    
    private function medAdherenceRAS($doctor_id = "" , $insurance_id = "", $clinic_id = "", $careGap = "", $gaps_filterYear, $insurancePrvider)
    {       
        // Find Models Name for care gaps 
        $CGModelsName = $this->FindModelsName($insurance_id, $insurancePrvider);

        $MedAdherenceRAS = [];
        $Title = "Med Adherence - RAS";
        $column_name = 'med_adherence_ras_gap';
        $ShortTitle = "RAS";
        
        if ($careGap == 1) {
            $Title = "Med Adherence - RAS Insurance";
            $column_name = 'med_adherence_ras_gap_insurance';
        }

        // As per current status
        $ClosedPatients = $CGModelsName::thisYearGaps($gaps_filterYear)->with('patientinfo')->whereHas('patientinfo')->where($column_name,'Compliant');
        $OpenPatients = $CGModelsName::thisYearGaps($gaps_filterYear)->whereNotIn($column_name, ['Compliant', 'N/A']);
        
        // As per insurance
        $ClosedPatientsCareGapFile = $CGModelsName::thisYearGaps($gaps_filterYear)->with('patientinfo')->whereHas('patientinfo')->where('med_adherence_ras_gap_insurance','Compliant');
        
        $Refused = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, 'Patient Refused', $gaps_filterYear);
        $Scheduled = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, 'Scheduled', $gaps_filterYear);
        
        $Total = $CGModelsName::thisYearGaps($gaps_filterYear)->where($column_name, '!=', ['N/A']);

        if(!empty($doctor_id)){
            $doctor_id = (string) $doctor_id;
            $ClosedPatientsCareGapFile->where('doctor_id',$doctor_id);
            $ClosedPatients->where('doctor_id',$doctor_id);
            $OpenPatients->where('doctor_id',$doctor_id); 
            $Total->where('doctor_id',$doctor_id);
            
        }
        if(!empty($insurance_id)){
            $ClosedPatientsCareGapFile->where('insurance_id',$insurance_id);
            $ClosedPatients->where('insurance_id',$insurance_id);
            $OpenPatients->where('insurance_id',$insurance_id);
            $Total->where('insurance_id',$insurance_id);
        }
        if(!empty($clinic_id)){
            $clinic_id = explode(',', $clinic_id);
            $ClosedPatientsCareGapFile->where('clinic_id',$clinic_id);
            $ClosedPatients->where('clinic_id',$clinic_id);
            $OpenPatients->where('clinic_id',$clinic_id);        
            $Total->where('clinic_id',$clinic_id);
        }

        $ActiveNonComp = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, "", $gaps_filterYear);
        $ClosedPatientsCareGapFile = $ClosedPatientsCareGapFile->count();
        $ClosedPatients = $ClosedPatients->count();
        $ClosedPatientsDifference = $ClosedPatients - $ClosedPatientsCareGapFile;
        $ClosedPatientsDifference = ($ClosedPatientsDifference>=1 ) ? (string) $ClosedPatientsDifference : '0' ;
        $OpenPatients = $OpenPatients->count();
        $Refused = $Refused;
        $Scheduled = $Scheduled;
        $UnScheduled = $ActiveNonComp - $Scheduled - $Refused;
        $UnScheduled = ($UnScheduled>=1 ) ? (string) $UnScheduled : '0' ; 
        $Total = $Total->count();
        // $Eligible = $Refused + $Scheduled + $UnScheduled;

        if($Total != 0) {
            $Acheived = number_format( $ClosedPatients * 100 / $Total , 1, '.', '') ;
        } else {
            $Acheived = "0";
        }
        
        $ADH_RASReturn = $this->medAdherenceRASStarConditions($insurancePrvider, $Acheived);
        $Required_Par = $ADH_RASReturn['Required_Par'];
        $Star = $ADH_RASReturn['Star'];

        $Members_remaining =  ((($Required_Par - $Acheived) * $Total) / 100)  ;  
        if($Members_remaining <= 0 ){
            $Members_remaining = "-";
        }else if($Members_remaining > 0 && $Members_remaining < 1){
            $Members_remaining = 1;
        }else{
            $Members_remaining = number_format($Members_remaining);
        }

        $MedAdherenceRAS = [
            'Title'                     => $Title,
            'ShortTitle'                => $ShortTitle,
            'ActiveNonComp'             => $ActiveNonComp,
            'ClosedPatients'            => $ClosedPatients,
            'ClosedPatientsDifference'  => $ClosedPatientsDifference,
            'OpenPatients'              => $OpenPatients,
            'Refused'                   => $Refused,
            'Scheduled'                 => $Scheduled,
            'UnScheduled'               => $UnScheduled,
            'Total'                     => $Total,
            'Acheived'                  => $Acheived,
            'Required_Par'              => $Required_Par,
            'Star'                      => $Star,
            'Members_remaining'         => $Members_remaining,
            'db_col_name'               => $column_name,
        ];

        return $MedAdherenceRAS;
    }
    
    private function medAdherenceStatins($doctor_id = "" , $insurance_id = "", $clinic_id = "", $careGap = "", $gaps_filterYear, $insurancePrvider)
    {
             
        // Find Models Name for care gaps 
        $CGModelsName = $this->FindModelsName($insurance_id, $insurancePrvider);

        $MedAdherenceStatins = [];
        $Title = "Med Adherence - Statins";
        $column_name = 'med_adherence_statins_gap';
        $ShortTitle = "STATIN";
        
        if ($careGap == 1) {
            $Title = "Med Adherence - Statins Insurance";
            $column_name = 'med_adherence_statins_gap_insurance';
        }

        // As per current status
        $ClosedPatients = $CGModelsName::thisYearGaps($gaps_filterYear)->with('patientinfo')->whereHas('patientinfo')->where($column_name,'Compliant');
        $OpenPatients = $CGModelsName::thisYearGaps($gaps_filterYear)->whereNotIn($column_name, ['Compliant', 'N/A']);
        
        // As per insurance
        $ClosedPatientsCareGapFile = $CGModelsName::thisYearGaps($gaps_filterYear)->with('patientinfo')->whereHas('patientinfo')->where('med_adherence_statins_gap_insurance','Compliant');
        
        $Refused = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, 'Patient Refused', $gaps_filterYear);
        $Scheduled = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, 'Scheduled', $gaps_filterYear);
        
        $Total = $CGModelsName::thisYearGaps($gaps_filterYear)->where($column_name, '!=', ['N/A']);

        if(!empty($doctor_id)){
            $doctor_id = (string) $doctor_id;
            $ClosedPatientsCareGapFile->where('doctor_id',$doctor_id);
            $ClosedPatients->where('doctor_id',$doctor_id);
            $OpenPatients->where('doctor_id',$doctor_id); 
            $Total->where('doctor_id',$doctor_id);
            
        }
        if(!empty($insurance_id)){
            $ClosedPatientsCareGapFile->where('insurance_id',$insurance_id);
            $ClosedPatients->where('insurance_id',$insurance_id);
            $OpenPatients->where('insurance_id',$insurance_id);
            $Total->where('insurance_id',$insurance_id);
        }
        if(!empty($clinic_id)){
            $clinic_id = explode(',', $clinic_id);
            $ClosedPatientsCareGapFile->where('clinic_id',$clinic_id);
            $ClosedPatients->where('clinic_id',$clinic_id);
            $OpenPatients->where('clinic_id',$clinic_id);        
            $Total->where('clinic_id',$clinic_id);
        }

        $ActiveNonComp = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, "", $gaps_filterYear);
        $ClosedPatientsCareGapFile = $ClosedPatientsCareGapFile->count();
        $ClosedPatients = $ClosedPatients->count();
        $ClosedPatientsDifference = $ClosedPatients - $ClosedPatientsCareGapFile;
        $ClosedPatientsDifference = ($ClosedPatientsDifference>=1 ) ? (string) $ClosedPatientsDifference : '0' ;
        $OpenPatients = $OpenPatients->count();
        $Refused = $Refused;
        $Scheduled = $Scheduled;
        $UnScheduled = $ActiveNonComp - $Scheduled - $Refused;
        $UnScheduled = ($UnScheduled>=1 ) ? (string) $UnScheduled : '0' ; 
        $Total = $Total->count();
        // $Eligible = $Refused + $Scheduled + $UnScheduled;

        if($Total != 0) {
            $Acheived = number_format( $ClosedPatients * 100 / $Total , 1, '.', '') ;
        } else {
            $Acheived = "0";
        }
        
        $STATINReturn = $this->medAdherenceStatinsStarConditions($insurancePrvider, $Acheived);
        $Required_Par = $STATINReturn['Required_Par'];
        $Star = $STATINReturn['Star'];

        $Members_remaining =  ((($Required_Par - $Acheived) * $Total) / 100)  ;  
        if($Members_remaining <= 0 ){
            $Members_remaining = "-";
        }else if($Members_remaining > 0 && $Members_remaining < 1){
            $Members_remaining = 1;
        }else{
            $Members_remaining = number_format($Members_remaining);
        }

        $MedAdherenceStatins = [
            'Title'                     => $Title,
            'ShortTitle'                => $ShortTitle,
            'ActiveNonComp'             => $ActiveNonComp,
            'ClosedPatients'            => $ClosedPatients,
            'ClosedPatientsDifference'  => $ClosedPatientsDifference,
            'OpenPatients'              => $OpenPatients,
            'Refused'                   => $Refused,
            'Scheduled'                 => $Scheduled,
            'UnScheduled'               => $UnScheduled,
            'Total'                     => $Total,
            'Acheived'                  => $Acheived,
            'Required_Par'              => $Required_Par,
            'Star'                      => $Star,
            'Members_remaining'         => $Members_remaining,
            'db_col_name'               => $column_name,
        ];

        return $MedAdherenceStatins;
    }
    
    private function SPCStatinTherapyCVDAllwellMedicare($doctor_id = "" , $insurance_id = "", $clinic_id = "", $careGap = "", $gaps_filterYear, $insurancePrvider)
    {
        $SPC_CVD = [];
        $Title = "SPC - Statin Therapy for Patients with CVD";
        $column_name = 'spc_statin_therapy_cvd_gap';
        $ShortTitle = "SpcCvd";
        
        if ($careGap == 1) {
            $Title = "SPC - Statin Therapy for Patients with CVD Insurance";
            $column_name = 'spc_statin_therapy_cvd_gap_insurance';
        }

        // As per current status
        $ClosedPatients = AllwellMedicareCareGaps::thisYearGaps($gaps_filterYear)->with('patientinfo')->whereHas('patientinfo')->where($column_name,'Compliant');
        $OpenPatients = AllwellMedicareCareGaps::thisYearGaps($gaps_filterYear)->whereNotIn($column_name, ['Compliant', 'N/A']);
        
        // As per insurance
        $ClosedPatientsCareGapFile = AllwellMedicareCareGaps::thisYearGaps($gaps_filterYear)->with('patientinfo')->whereHas('patientinfo')->where('spc_statin_therapy_cvd_gap_insurance','Compliant');
        
        $Refused = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, 'Patient Refused', $gaps_filterYear);
        $Scheduled = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, 'Scheduled', $gaps_filterYear);
        
        $Total = AllwellMedicareCareGaps::thisYearGaps($gaps_filterYear)->where($column_name, '!=', ['N/A']);

        if(!empty($doctor_id)){
            $doctor_id = (string) $doctor_id;
            $ClosedPatientsCareGapFile->where('doctor_id',$doctor_id);
            $ClosedPatients->where('doctor_id',$doctor_id);
            $OpenPatients->where('doctor_id',$doctor_id); 
            $Total->where('doctor_id',$doctor_id);
            
        }
        if(!empty($insurance_id)){
            $ClosedPatientsCareGapFile->where('insurance_id',$insurance_id);
            $ClosedPatients->where('insurance_id',$insurance_id);
            $OpenPatients->where('insurance_id',$insurance_id);
            $Total->where('insurance_id',$insurance_id);
        }
        if(!empty($clinic_id)){
            $clinic_id = explode(',', $clinic_id);
            $ClosedPatientsCareGapFile->where('clinic_id',$clinic_id);
            $ClosedPatients->where('clinic_id',$clinic_id);
            $OpenPatients->where('clinic_id',$clinic_id);        
            $Total->where('clinic_id',$clinic_id);
        }

        $ActiveNonComp = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, "", $gaps_filterYear);
        $ClosedPatientsCareGapFile = $ClosedPatientsCareGapFile->count();
        $ClosedPatients = $ClosedPatients->count();
        $ClosedPatientsDifference = $ClosedPatients - $ClosedPatientsCareGapFile;
        $ClosedPatientsDifference = ($ClosedPatientsDifference>=1 ) ? (string) $ClosedPatientsDifference : '0' ;
        $OpenPatients = $OpenPatients->count();
        $Refused = $Refused;
        $Scheduled = $Scheduled;
        $UnScheduled = $ActiveNonComp - $Scheduled - $Refused;
        $UnScheduled = ($UnScheduled>=1 ) ? (string) $UnScheduled : '0' ; 
        $Total = $Total->count();
        // $Eligible = $Refused + $Scheduled + $UnScheduled;

        if($Total != 0) {
            $Acheived = number_format( $ClosedPatients * 100 / $Total , 1, '.', '') ;
        } else {
            $Acheived = "0";
        }
        
        $Required_Par = "90";
        $Star = "-";

        if($Acheived >= "90") {
            $Star = "5";
        } else if($Acheived >= "86") {
            $Star = "4";
        } else if($Acheived >= "82") {
            $Star = "3";
        } elseif ($Acheived >= "78")  {
            $Star = "2";
        } else{
            $Star = "1";
        }

        $Members_remaining =  ((($Required_Par - $Acheived) * $Total) / 100)  ;  
        if($Members_remaining <= 0 ){
            $Members_remaining = "-";
        }else if($Members_remaining > 0 && $Members_remaining < 1){
            $Members_remaining = 1;
        }else{
            $Members_remaining = number_format($Members_remaining);
        }

        $SPC_CVD = [
            'Title'                     => $Title,
            'ShortTitle'                => $ShortTitle,
            'ActiveNonComp'             => $ActiveNonComp,
            'ClosedPatients'            => $ClosedPatients,
            'ClosedPatientsDifference'  => $ClosedPatientsDifference,
            'OpenPatients'              => $OpenPatients,
            'Refused'                   => $Refused,
            'Scheduled'                 => $Scheduled,
            'UnScheduled'               => $UnScheduled,
            'Total'                     => $Total,
            'Acheived'                  => $Acheived,
            'Required_Par'              => $Required_Par,
            'Star'                      => $Star,
            'Members_remaining'         => $Members_remaining,
            'db_col_name'               => $column_name,
        ];

        return $SPC_CVD;
    }
    
    private function StatinPersonsDiabetesAllwellMedicare($doctor_id = "" , $insurance_id = "", $clinic_id = "", $careGap = "", $gaps_filterYear, $insurancePrvider)
    {
        $SUPD = [];
        $Title = "Statin Use in Persons with Diabetes (SUPD)";
        $column_name = 'sup_diabetes_gap';
        $ShortTitle = "SUPD";
        
        if ($careGap == 1) {
            $Title = "Statin Use in Persons with Diabetes (SUPD) Insurance";
            $column_name = 'sup_diabetes_gap_insurance';
        }



        // As per current status
        $ClosedPatients = AllwellMedicareCareGaps::thisYearGaps($gaps_filterYear)->with('patientinfo')->whereHas('patientinfo')->where($column_name,'Compliant');
        $OpenPatients = AllwellMedicareCareGaps::thisYearGaps($gaps_filterYear)->whereNotIn($column_name, ['Compliant', 'N/A']);

        // As per insurance
        $ClosedPatientsCareGapFile = AllwellMedicareCareGaps::thisYearGaps($gaps_filterYear)->with('patientinfo')->whereHas('patientinfo')->where('sup_diabetes_gap_insurance','Compliant');

        $Refused = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, 'Patient Refused', $gaps_filterYear);
        $Scheduled = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, 'Scheduled', $gaps_filterYear);
        
        $Total = AllwellMedicareCareGaps::thisYearGaps($gaps_filterYear)->where($column_name, '!=', ['N/A']);
        
        if(!empty($doctor_id)){
            $ClosedPatients->where('doctor_id',$doctor_id);
            $ClosedPatientsCareGapFile->where('doctor_id',$doctor_id);
            $OpenPatients->where('doctor_id',$doctor_id);
            $Total->where('doctor_id',$doctor_id);
        }
        if(!empty($insurance_id)){
            $ClosedPatients->where('insurance_id',$insurance_id);
            $ClosedPatientsCareGapFile->where('insurance_id',$insurance_id);
            $OpenPatients->where('insurance_id',$insurance_id);
            $Total->where('insurance_id',$insurance_id);
        }
        if(!empty($clinic_id)){
            $clinic_id = explode(',', $clinic_id);
            $ClosedPatients->where('clinic_id',$clinic_id);
            $ClosedPatientsCareGapFile->where('clinic_id',$clinic_id);
            $OpenPatients->where('clinic_id',$clinic_id);
            $Total->where('clinic_id',$clinic_id);
        }
        $ActiveNonComp = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, "", $gaps_filterYear);
        $ClosedPatientsCareGapFile = $ClosedPatientsCareGapFile->count();
        $ClosedPatients = $ClosedPatients->count();
        $ClosedPatientsDifference = $ClosedPatients - $ClosedPatientsCareGapFile;
        $ClosedPatientsDifference = ($ClosedPatientsDifference>=1 ) ? (string) $ClosedPatientsDifference : '0' ;
        $OpenPatients = $OpenPatients->count();
        $Refused = $Refused;
        $Scheduled = $Scheduled;
        $UnScheduled = $ActiveNonComp - $Scheduled - $Refused ;
        $UnScheduled = ($UnScheduled>=1 ) ? (string) $UnScheduled : '0' ;
        $Total = $Total->count();
        // $Eligible = $Refused + $Scheduled + $UnScheduled;

        if($Total != 0) {
            $Acheived = number_format( $ClosedPatients * 100 / $Total , 1, '.', '') ;
        }else{
            $Acheived = "0";
        }

        $Members_remaining =  ((($Required_Par - $Acheived) * $Total) / 100)  ;  
        if($Members_remaining <= 0 ){
            $Members_remaining = "-";
        }else if($Members_remaining > 0 && $Members_remaining < 1){
            $Members_remaining = 1;
        }else{
            $Members_remaining = number_format($Members_remaining);
        }

        $SUPD = [
            'Title'                     => $Title,
            'ShortTitle'                => $ShortTitle,
            'ActiveNonComp'             => $ActiveNonComp,
            'ClosedPatients'            => $ClosedPatients,
            'ClosedPatientsDifference'  => $ClosedPatientsDifference,
            'OpenPatients'              => $OpenPatients,
            'Refused'                   => $Refused,
            'Scheduled'                 => $Scheduled,
            'UnScheduled'               => $UnScheduled,
            'Total'                     => $Total,
            'Acheived'                  => $Acheived,
            'Required_Par'              => $Required_Par,
            'Star'                      => $Star,
            'Members_remaining'         => $Members_remaining,
            'db_col_name'               => $column_name,
        ];

        return $SUPD;
    }
    
    private function TRCAfterDischargeAllwellMedicare($doctor_id = "" , $insurance_id = "", $clinic_id = "", $careGap = "", $gaps_filterYear, $insurancePrvider)
    {
        $TRCAfter = [];
        $Title = "TRC - Engagement After Discharge";
        $column_name = 'trc_eng_after_disc_gap';
        $ShortTitle = "TRCAfter";
        
        if ($careGap == 1) {
            $Title = "TRC - Engagement After Discharge Insurance";
            $column_name = 'trc_eng_after_disc_gap_insurance';
        }



        // As per current status
        $ClosedPatients = AllwellMedicareCareGaps::thisYearGaps($gaps_filterYear)->with('patientinfo')->whereHas('patientinfo')->where($column_name,'Compliant');
        $OpenPatients = AllwellMedicareCareGaps::thisYearGaps($gaps_filterYear)->whereNotIn($column_name, ['Compliant', 'N/A']);

        // As per insurance
        $ClosedPatientsCareGapFile = AllwellMedicareCareGaps::thisYearGaps($gaps_filterYear)->with('patientinfo')->whereHas('patientinfo')->where('trc_eng_after_disc_gap_insurance','Compliant');

        $Refused = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, 'Patient Refused', $gaps_filterYear);
        $Scheduled = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, 'Scheduled', $gaps_filterYear);
        
        $Total = AllwellMedicareCareGaps::thisYearGaps($gaps_filterYear)->where($column_name, '!=', ['N/A']);
        
        if(!empty($doctor_id)){
            $ClosedPatients->where('doctor_id',$doctor_id);
            $ClosedPatientsCareGapFile->where('doctor_id',$doctor_id);
            $OpenPatients->where('doctor_id',$doctor_id);
            $Total->where('doctor_id',$doctor_id);
        }
        if(!empty($insurance_id)){
            $ClosedPatients->where('insurance_id',$insurance_id);
            $ClosedPatientsCareGapFile->where('insurance_id',$insurance_id);
            $OpenPatients->where('insurance_id',$insurance_id);
            $Total->where('insurance_id',$insurance_id);
        }
        if(!empty($clinic_id)){
            $clinic_id = explode(',', $clinic_id);
            $ClosedPatients->where('clinic_id',$clinic_id);
            $ClosedPatientsCareGapFile->where('clinic_id',$clinic_id);
            $OpenPatients->where('clinic_id',$clinic_id);
            $Total->where('clinic_id',$clinic_id);
        }
        $ActiveNonComp = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, "", $gaps_filterYear);
        $ClosedPatientsCareGapFile = $ClosedPatientsCareGapFile->count();
        $ClosedPatients = $ClosedPatients->count();
        $ClosedPatientsDifference = $ClosedPatients - $ClosedPatientsCareGapFile;
        $ClosedPatientsDifference = ($ClosedPatientsDifference>=1 ) ? (string) $ClosedPatientsDifference : '0' ;
        $OpenPatients = $OpenPatients->count();
        $Refused = $Refused;
        $Scheduled = $Scheduled;
        $UnScheduled = $ActiveNonComp - $Scheduled - $Refused ;
        $UnScheduled = ($UnScheduled>=1 ) ? (string) $UnScheduled : '0' ;
        $Total = $Total->count();
        // $Eligible = $Refused + $Scheduled + $UnScheduled;

        if($Total != 0) {
            $Acheived = number_format( $ClosedPatients * 100 / $Total , 1, '.', '') ;
        }else{
            $Acheived = "0";
        }

        $Required_Par = "72"; 

        if($Acheived >= "72"){
            $Star = "5";
        }else if($Acheived >= "54"){
            $Star = "4";
        }else if($Acheived >= "46"){
            $Star = "3";
        }else if($Acheived >= "36"){
            $Star = "2";
        }else{
            $Star = "1";
        }

        $Members_remaining =  ((($Required_Par - $Acheived) * $Total) / 100)  ;  
        if($Members_remaining <= 0 ){
            $Members_remaining = "-";
        }else if($Members_remaining > 0 && $Members_remaining < 1){
            $Members_remaining = 1;
        }else{
            $Members_remaining = number_format($Members_remaining);
        }

        $TRCAfter = [
            'Title'                     => $Title,
            'ShortTitle'                => $ShortTitle,
            'ActiveNonComp'             => $ActiveNonComp,
            'ClosedPatients'            => $ClosedPatients,
            'ClosedPatientsDifference'  => $ClosedPatientsDifference,
            'OpenPatients'              => $OpenPatients,
            'Refused'                   => $Refused,
            'Scheduled'                 => $Scheduled,
            'UnScheduled'               => $UnScheduled,
            'Total'                     => $Total,
            'Acheived'                  => $Acheived,
            'Required_Par'              => $Required_Par,
            'Star'                      => $Star,
            'Members_remaining'         => $Members_remaining,
            'db_col_name'               => $column_name,
        ];

        return $TRCAfter;
    }

    private function TRCPostDischargeAllwellMedicare($doctor_id = "" , $insurance_id = "", $clinic_id = "", $careGap = "" , $gaps_filterYear)
    {
        $TRCPost = [];
        $Title = "TRC - Med Reconciliation Post Discharge";
        $column_name = 'trc_mr_post_disc_gap';
        $ShortTitle = "TRCPost";
        
        if ($careGap == 1) {
            $Title = "TRC - Med Reconciliation Post Discharge Insurance";
            $column_name = 'trc_mr_post_disc_gap_insurance';
        }



        // As per current status
        $ClosedPatients = AllwellMedicareCareGaps::thisYearGaps($gaps_filterYear)->with('patientinfo')->whereHas('patientinfo')->where($column_name,'Compliant');
        $OpenPatients = AllwellMedicareCareGaps::thisYearGaps($gaps_filterYear)->whereNotIn($column_name, ['Compliant', 'N/A']);

        // As per insurance
        $ClosedPatientsCareGapFile = AllwellMedicareCareGaps::thisYearGaps($gaps_filterYear)->with('patientinfo')->whereHas('patientinfo')->where('trc_mr_post_disc_gap_insurance','Compliant');

        $Refused = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, 'Patient Refused', $gaps_filterYear);
        $Scheduled = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, 'Scheduled', $gaps_filterYear);
        
        $Total = AllwellMedicareCareGaps::thisYearGaps($gaps_filterYear)->where($column_name, '!=', ['N/A']);
        
        if(!empty($doctor_id)){
            $ClosedPatients->where('doctor_id',$doctor_id);
            $ClosedPatientsCareGapFile->where('doctor_id',$doctor_id);
            $OpenPatients->where('doctor_id',$doctor_id);
            $Total->where('doctor_id',$doctor_id);
        }
        if(!empty($insurance_id)){
            $ClosedPatients->where('insurance_id',$insurance_id);
            $ClosedPatientsCareGapFile->where('insurance_id',$insurance_id);
            $OpenPatients->where('insurance_id',$insurance_id);
            $Total->where('insurance_id',$insurance_id);
        }
        if(!empty($clinic_id)){
            $clinic_id = explode(',', $clinic_id);
            $ClosedPatients->where('clinic_id',$clinic_id);
            $ClosedPatientsCareGapFile->where('clinic_id',$clinic_id);
            $OpenPatients->where('clinic_id',$clinic_id);
            $Total->where('clinic_id',$clinic_id);
        }
        $ActiveNonComp = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, "", $gaps_filterYear);
        $ClosedPatientsCareGapFile = $ClosedPatientsCareGapFile->count();
        $ClosedPatients = $ClosedPatients->count();
        $ClosedPatientsDifference = $ClosedPatients - $ClosedPatientsCareGapFile;
        $ClosedPatientsDifference = ($ClosedPatientsDifference>=1 ) ? (string) $ClosedPatientsDifference : '0' ;
        $OpenPatients = $OpenPatients->count();
        $Refused = $Refused;
        $Scheduled = $Scheduled;
        $UnScheduled = $ActiveNonComp - $Scheduled - $Refused ;
        $UnScheduled = ($UnScheduled>=1 ) ? (string) $UnScheduled : '0' ;
        $Total = $Total->count();
        // $Eligible = $Refused + $Scheduled + $UnScheduled;

        if($Total != 0) {
            $Acheived = number_format( $ClosedPatients * 100 / $Total , 1, '.', '') ;
        }else{
            $Acheived = "0";
        }

        $Required_Par = "72"; 

        if($Acheived >= "72"){
            $Star = "5";
        }else if($Acheived >= "54"){
            $Star = "4";
        }else if($Acheived >= "46"){
            $Star = "3";
        }else if($Acheived >= "36"){
            $Star = "2";
        }else{
            $Star = "1";
        }

        $Members_remaining =  ((($Required_Par - $Acheived) * $Total) / 100)  ;  
        if($Members_remaining <= 0 ){
            $Members_remaining = "-";
        }else if($Members_remaining > 0 && $Members_remaining < 1){
            $Members_remaining = 1;
        }else{
            $Members_remaining = number_format($Members_remaining);
        }

        $TRCPost = [
            'Title'                     => $Title,
            'ShortTitle'                => $ShortTitle,
            'ActiveNonComp'             => $ActiveNonComp,
            'ClosedPatients'            => $ClosedPatients,
            'ClosedPatientsDifference'  => $ClosedPatientsDifference,
            'OpenPatients'              => $OpenPatients,
            'Refused'                   => $Refused,
            'Scheduled'                 => $Scheduled,
            'UnScheduled'               => $UnScheduled,
            'Total'                     => $Total,
            'Acheived'                  => $Acheived,
            'Required_Par'              => $Required_Par,
            'Star'                      => $Star,
            'Members_remaining'         => $Members_remaining,
            'db_col_name'               => $column_name,
        ];

        return $TRCPost;
    }

    private function KidneyHealth ($doctor_id = "" , $insurance_id = "", $clinic_id = "", $careGap = "" , $gaps_filterYear, $insurancePrvider)
    {
        // Find Models Name for care gaps 
        $CGModelsName = $this->FindModelsName($insurance_id, $insurancePrvider);

        $KED = [];
        $Title = "KED - Kidney Health for Patients With Diabetes ";
        $column_name = 'kidney_health_diabetes_gap';
        $ShortTitle = "KED";
        
        if ($careGap == 1) {
            $Title = "KED - Kidney Health for Patients With Diabetes Insurance";
            $column_name = 'kidney_health_diabetes_gap_insurance';
        }



        // As per current status
        $ClosedPatients =  $CGModelsName::thisYearGaps($gaps_filterYear)->with('patientinfo')->whereHas('patientinfo')->where($column_name,'Compliant');
        $OpenPatients =  $CGModelsName::thisYearGaps($gaps_filterYear)->whereNotIn($column_name, ['Compliant', 'N/A']);

        // As per insurance
        $ClosedPatientsCareGapFile =  $CGModelsName::thisYearGaps($gaps_filterYear)->with('patientinfo')->whereHas('patientinfo')->where('kidney_health_diabetes_gap_insurance','Compliant');

        $Refused = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, 'Patient Refused', $gaps_filterYear);
        $Scheduled = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, 'Scheduled', $gaps_filterYear);
        
        $Total =  $CGModelsName::thisYearGaps($gaps_filterYear)->where($column_name, '!=', ['N/A']);
        
        if(!empty($doctor_id)){
            $ClosedPatients->where('doctor_id',$doctor_id);
            $ClosedPatientsCareGapFile->where('doctor_id',$doctor_id);
            $OpenPatients->where('doctor_id',$doctor_id);
            $Total->where('doctor_id',$doctor_id);
        }
        if(!empty($insurance_id)){
            $ClosedPatients->where('insurance_id',$insurance_id);
            $ClosedPatientsCareGapFile->where('insurance_id',$insurance_id);
            $OpenPatients->where('insurance_id',$insurance_id);
            $Total->where('insurance_id',$insurance_id);
        }
        if(!empty($clinic_id)){
            $clinic_id = explode(',', $clinic_id);
            $ClosedPatients->where('clinic_id',$clinic_id);
            $ClosedPatientsCareGapFile->where('clinic_id',$clinic_id);
            $OpenPatients->where('clinic_id',$clinic_id);
            $Total->where('clinic_id',$clinic_id);
        }
        $ActiveNonComp = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, "", $gaps_filterYear);
        $ClosedPatientsCareGapFile = $ClosedPatientsCareGapFile->count();
        $ClosedPatients = $ClosedPatients->count();
        $ClosedPatientsDifference = $ClosedPatients - $ClosedPatientsCareGapFile;
        $ClosedPatientsDifference = ($ClosedPatientsDifference>=1 ) ? (string) $ClosedPatientsDifference : '0' ;
        $OpenPatients = $OpenPatients->count();
        $Refused = $Refused;
        $Scheduled = $Scheduled;
        $UnScheduled = $ActiveNonComp - $Scheduled - $Refused ;
        $UnScheduled = ($UnScheduled>=1 ) ? (string) $UnScheduled : '0' ;
        $Total = $Total->count();
        // $Eligible = $Refused + $Scheduled + $UnScheduled;

        if($Total != 0) {
            $Acheived = number_format( $ClosedPatients * 100 / $Total , 1, '.', '') ;
        }else{
            $Acheived = "0";
        }

        $KED_Return = $this->KidneyHealthStarConditions($insurancePrvider, $Acheived);
        $Required_Par = $KED_Return['Required_Par'];
        $Star = $KED_Return['Star'];

        $Members_remaining =  ((($Required_Par - $Acheived) * $Total) / 100)  ;  
        if($Members_remaining <= 0 ){
            $Members_remaining = "-";
        }else if($Members_remaining > 0 && $Members_remaining < 1){
            $Members_remaining = 1;
        }else{
            $Members_remaining = number_format($Members_remaining);
        }

        $KED = [
            'Title'                     => $Title,
            'ShortTitle'                => $ShortTitle,
            'ActiveNonComp'             => $ActiveNonComp,
            'ClosedPatients'            => $ClosedPatients,
            'ClosedPatientsDifference'  => $ClosedPatientsDifference,
            'OpenPatients'              => $OpenPatients,
            'Refused'                   => $Refused,
            'Scheduled'                 => $Scheduled,
            'UnScheduled'               => $UnScheduled,
            'Total'                     => $Total,
            'Acheived'                  => $Acheived,
            'Required_Par'              => $Required_Par,
            'Star'                      => $Star,
            'Members_remaining'         => $Members_remaining,
            'db_col_name'               => $column_name,
        ];

        return $KED;
    }

    // ========================================  End ALLWELL ======================================= 
    
    //Health Choice Arizona Start
    private function CervicalCancerScreening($doctor_id = "" , $insurance_id = "", $clinic_id = "", $careGap , $gaps_filterYear , $insurancePrvider)
    {
        // Find Models Name for care gaps 
        $CGModelsName = $this->FindModelsName($insurance_id, $insurancePrvider);

        $CCS = [];
        $Title = "Cervical Cancer Screening (CCS)";
        $column_name = 'cervical_cancer_gap';
        $ShortTitle = "CCS";
        
        if ($careGap == 1) {
            $Title = "Cervical Cancer Screening (CCS) Insurance";
            $column_name = 'cervical_cancer_gap_insurance';
        }

        // As per current status
        $ClosedPatients = $CGModelsName::thisYearGaps($gaps_filterYear)->with('patientinfo')->whereHas('patientinfo')->where($column_name,'Compliant');
        $OpenPatients = $CGModelsName::thisYearGaps($gaps_filterYear)->whereNotIn($column_name, ['Compliant', 'N/A']);

        // As per insurance
        $ClosedPatientsCareGapFile = $CGModelsName::thisYearGaps($gaps_filterYear)->with('patientinfo')->whereHas('patientinfo')->where('cervical_cancer_gap_insurance','Compliant');
        
        $Refused = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, 'Patient Refused', $gaps_filterYear);
        $Scheduled = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, 'Scheduled', $gaps_filterYear);

        $Total = $CGModelsName::thisYearGaps($gaps_filterYear)->where($column_name, '!=', ['N/A']);
        
        if(!empty($doctor_id)){
            $ClosedPatients->where('doctor_id',$doctor_id);
            $ClosedPatientsCareGapFile->where('doctor_id',$doctor_id);
            $OpenPatients->where('doctor_id',$doctor_id);
            $Total->where('doctor_id',$doctor_id);
        }
        
        if(!empty($insurance_id)){
            $ClosedPatients->where('insurance_id',$insurance_id);
            $ClosedPatientsCareGapFile->where('insurance_id',$insurance_id);
            $OpenPatients->where('insurance_id',$insurance_id);
            $Total->where('insurance_id',$insurance_id);
        }
        
        if(!empty($clinic_id)){
            $clinic_id = explode(',', $clinic_id);
            $ClosedPatients->where('clinic_id',$clinic_id);
            $ClosedPatientsCareGapFile->where('clinic_id',$clinic_id);
            $Total->where('clinic_id',$clinic_id);
        }

        $ActiveNonComp = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, "", $gaps_filterYear);
        $ClosedPatientsCareGapFile = $ClosedPatientsCareGapFile->count();
        $ClosedPatients = $ClosedPatients->count();
        $ClosedPatientsDifference = $ClosedPatients - $ClosedPatientsCareGapFile;
        $ClosedPatientsDifference = ($ClosedPatientsDifference>=1 ) ? (string) $ClosedPatientsDifference : '0' ;
        $OpenPatients = $OpenPatients->count();
        $Refused = $Refused;
        $Scheduled = $Scheduled;
        $UnScheduled = $ActiveNonComp - $Scheduled - $Refused ;
        $UnScheduled = ($UnScheduled>=1 ) ? (string) $UnScheduled : '0' ;
        $Total = $Total->count();
        // $Eligible = $Refused + $Scheduled + $UnScheduled;
        
        if($Total != 0)
        {
            $Acheived = number_format($ClosedPatients * 100 / $Total);
        }else{
            $Acheived = "0";
        }
        $CCSReturn = $this->CervicalCancerScreeningStarConditions($insurancePrvider, $Acheived);
        $Required_Par = $CCSReturn['Required_Par'];
        $Star = $CCSReturn['Star'];

        $Members_remaining =  ((($Required_Par - $Acheived) * $Total) / 100)  ;  
        if($Members_remaining <= 0 ){
            $Members_remaining = "-";
        }else if($Members_remaining > 0 && $Members_remaining < 1){
            $Members_remaining = 1;
        }else{
            $Members_remaining = number_format($Members_remaining);
        }
        
        $CCS = [
            'Title'                     => $Title,
            'ShortTitle'                => $ShortTitle,
            'ActiveNonComp'             => $ActiveNonComp,
            'ClosedPatients'            => $ClosedPatients,
            'ClosedPatientsDifference'  => $ClosedPatientsDifference,
            'OpenPatients'              => $OpenPatients,
            'Refused'                   => $Refused,
            'Scheduled'                 => $Scheduled,
            'UnScheduled'               => $UnScheduled,
            'Total'                     => $Total,
            'Acheived'                  => $Acheived,
            'Required_Par'              => $Required_Par,
            'Star'                      => $Star,
            'Members_remaining'         => $Members_remaining,
            'db_col_name'               => $column_name,
        ];

        return $CCS;
    } 

    //HDO - Use of Opioids at High Dosage
    //'opioids_high_dosage_gap',
    private function OpioidsHighDosage($doctor_id = "" , $insurance_id = "", $clinic_id = "", $careGap , $gaps_filterYear , $insurancePrvider)
    {
        // Find Models Name for care gaps 
        $CGModelsName = $this->FindModelsName($insurance_id, $insurancePrvider);

        $HDO = [];
        $Title = "HDO - Use of Opioids at High Dosage";
        $column_name = 'opioids_high_dosage_gap';
        $ShortTitle = "HDO";
        
        if ($careGap == 1) {
            $Title = "HDO - Use of Opioids at High Dosage Insurance";
            $column_name = 'opioids_high_dosage_gap_insurance';
        }

        // As per current status
        $ClosedPatients = $CGModelsName::thisYearGaps($gaps_filterYear)->with('patientinfo')->whereHas('patientinfo')->where($column_name,'Compliant');
        $OpenPatients = $CGModelsName::thisYearGaps($gaps_filterYear)->whereNotIn($column_name, ['Compliant', 'N/A']);

        // As per insurance
        $ClosedPatientsCareGapFile = $CGModelsName::thisYearGaps($gaps_filterYear)->with('patientinfo')->whereHas('patientinfo')->where('opioids_high_dosage_gap_insurance','Compliant');
        
        $Refused = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, 'Patient Refused', $gaps_filterYear);
        $Scheduled = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, 'Scheduled', $gaps_filterYear);

        $Total = $CGModelsName::thisYearGaps($gaps_filterYear)->where($column_name, '!=', ['N/A']);
        
        if(!empty($doctor_id)){
            $ClosedPatients->where('doctor_id',$doctor_id);
            $ClosedPatientsCareGapFile->where('doctor_id',$doctor_id);
            $OpenPatients->where('doctor_id',$doctor_id);
            $Total->where('doctor_id',$doctor_id);
        }
        
        if(!empty($insurance_id)){
            $ClosedPatients->where('insurance_id',$insurance_id);
            $ClosedPatientsCareGapFile->where('insurance_id',$insurance_id);
            $OpenPatients->where('insurance_id',$insurance_id);
            $Total->where('insurance_id',$insurance_id);
        }
        
        if(!empty($clinic_id)){
            $clinic_id = explode(',', $clinic_id);
            $ClosedPatients->where('clinic_id',$clinic_id);
            $ClosedPatientsCareGapFile->where('clinic_id',$clinic_id);
            $Total->where('clinic_id',$clinic_id);
        }

        $ActiveNonComp = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, "", $gaps_filterYear);
        $ClosedPatientsCareGapFile = $ClosedPatientsCareGapFile->count();
        $ClosedPatients = $ClosedPatients->count();
        $ClosedPatientsDifference = $ClosedPatients - $ClosedPatientsCareGapFile;
        $ClosedPatientsDifference = ($ClosedPatientsDifference>=1 ) ? (string) $ClosedPatientsDifference : '0' ;
        $OpenPatients = $OpenPatients->count();
        $Refused = $Refused;
        $Scheduled = $Scheduled;
        $UnScheduled = $ActiveNonComp - $Scheduled - $Refused ;
        $UnScheduled = ($UnScheduled>=1 ) ? (string) $UnScheduled : '0' ;
        $Total = $Total->count();
        // $Eligible = $Refused + $Scheduled + $UnScheduled;
        
        if($Total != 0)
        {
            $Acheived = number_format($ClosedPatients * 100 / $Total);
        }else{
            $Acheived = "0";
        }

        $Required_Par = "4.70" ; 

        if($Acheived <= "4.70"){
            $Star = "5";
        }else if($Acheived <= "5.70"){
            $Star = "4";
        }else if($Acheived <= "6.70"){
            $Star = "3";
        }else if($Acheived <= "7.70"){
            $Star = "2";
        }else if($Acheived <= "8.70"){
            $Star = "1";
        }else{
            $Star = "-";
        }

        $Members_remaining =  ((($Required_Par - $Acheived) * $Total) / 100)  ;  
        if($Members_remaining <= 0 ){
            $Members_remaining = "-";
        }else if($Members_remaining > 0 && $Members_remaining < 1){
            $Members_remaining = 1;
        }else{
            $Members_remaining = number_format($Members_remaining);
        }
        
        $HDO = [
            'Title'                     => $Title,
            'ShortTitle'                => $ShortTitle,
            'ActiveNonComp'             => $ActiveNonComp,
            'ClosedPatients'            => $ClosedPatients,
            'ClosedPatientsDifference'  => $ClosedPatientsDifference,
            'OpenPatients'              => $OpenPatients,
            'Refused'                   => $Refused,
            'Scheduled'                 => $Scheduled,
            'UnScheduled'               => $UnScheduled,
            'Total'                     => $Total,
            'Acheived'                  => $Acheived,
            'Required_Par'              => $Required_Par,
            'Star'                      => $Star,
            'Members_remaining'         => $Members_remaining,
            'db_col_name'               => $column_name,
        ];

        return $HDO;
    } 

    //PPC1 Timeliness of Prenatal Care
    //ppc1_gap
    private function TimelinessPrenatalCare1($doctor_id = "" , $insurance_id = "", $clinic_id = "", $careGap , $gaps_filterYear , $insurancePrvider)
    {
        // Find Models Name for care gaps 
        $CGModelsName = $this->FindModelsName($insurance_id, $insurancePrvider);

        $PPC1 = [];
        $Title = "PPC1 Timeliness of Prenatal Care";
        $column_name = 'ppc1_gap';
        $ShortTitle = "PPC1";
        
        if ($careGap == 1) {
            $Title = "PPC1 Timeliness of Prenatal Care Insurance";
            $column_name = 'ppc1_gap_insurance';
        }

        // As per current status
        $ClosedPatients = $CGModelsName::thisYearGaps($gaps_filterYear)->with('patientinfo')->whereHas('patientinfo')->where($column_name,'Compliant');
        $OpenPatients = $CGModelsName::thisYearGaps($gaps_filterYear)->whereNotIn($column_name, ['Compliant', 'N/A']);

        // As per insurance
        $ClosedPatientsCareGapFile = $CGModelsName::thisYearGaps($gaps_filterYear)->with('patientinfo')->whereHas('patientinfo')->where('ppc1_gap_insurance','Compliant');
        
        $Refused = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, 'Patient Refused', $gaps_filterYear);
        $Scheduled = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, 'Scheduled', $gaps_filterYear);

        $Total = $CGModelsName::thisYearGaps($gaps_filterYear)->where($column_name, '!=', ['N/A']);
        
        if(!empty($doctor_id)){
            $ClosedPatients->where('doctor_id',$doctor_id);
            $ClosedPatientsCareGapFile->where('doctor_id',$doctor_id);
            $OpenPatients->where('doctor_id',$doctor_id);
            $Total->where('doctor_id',$doctor_id);
        }
        
        if(!empty($insurance_id)){
            $ClosedPatients->where('insurance_id',$insurance_id);
            $ClosedPatientsCareGapFile->where('insurance_id',$insurance_id);
            $OpenPatients->where('insurance_id',$insurance_id);
            $Total->where('insurance_id',$insurance_id);
        }
        
        if(!empty($clinic_id)){
            $clinic_id = explode(',', $clinic_id);
            $ClosedPatients->where('clinic_id',$clinic_id);
            $ClosedPatientsCareGapFile->where('clinic_id',$clinic_id);
            $Total->where('clinic_id',$clinic_id);
        }

        $ActiveNonComp = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, "", $gaps_filterYear);
        $ClosedPatientsCareGapFile = $ClosedPatientsCareGapFile->count();
        $ClosedPatients = $ClosedPatients->count();
        $ClosedPatientsDifference = $ClosedPatients - $ClosedPatientsCareGapFile;
        $ClosedPatientsDifference = ($ClosedPatientsDifference>=1 ) ? (string) $ClosedPatientsDifference : '0' ;
        $OpenPatients = $OpenPatients->count();
        $Refused = $Refused;
        $Scheduled = $Scheduled;
        $UnScheduled = $ActiveNonComp - $Scheduled - $Refused ;
        $UnScheduled = ($UnScheduled>=1 ) ? (string) $UnScheduled : '0' ;
        $Total = $Total->count();
        // $Eligible = $Refused + $Scheduled + $UnScheduled;
        
        if($Total != 0)
        {
            $Acheived = number_format($ClosedPatients * 100 / $Total);
        }else{
            $Acheived = "0";
        }

        $Required_Par = "81.30" ; 

        if($Acheived >= "81.30"){
            $Star = "5";
        }else if($Acheived >= "80.30"){
            $Star = "4";
        }else if($Acheived >= "79.30"){
            $Star = "3";
        }else if($Acheived >= "78.30"){
            $Star = "2";
        }else{
            $Star = "-";
        }

        $Members_remaining =  ((($Required_Par - $Acheived) * $Total) / 100)  ;  
        if($Members_remaining <= 0 ){
            $Members_remaining = "-";
        }else if($Members_remaining > 0 && $Members_remaining < 1){
            $Members_remaining = 1;
        }else{
            $Members_remaining = number_format($Members_remaining);
        }
        
        $PPC1 = [
            'Title'                     => $Title,
            'ShortTitle'                => $ShortTitle,
            'ActiveNonComp'             => $ActiveNonComp,
            'ClosedPatients'            => $ClosedPatients,
            'ClosedPatientsDifference'  => $ClosedPatientsDifference,
            'OpenPatients'              => $OpenPatients,
            'Refused'                   => $Refused,
            'Scheduled'                 => $Scheduled,
            'UnScheduled'               => $UnScheduled,
            'Total'                     => $Total,
            'Acheived'                  => $Acheived,
            'Required_Par'              => $Required_Par,
            'Star'                      => $Star,
            'Members_remaining'         => $Members_remaining,
            'db_col_name'               => $column_name,
        ];

        return $PPC1;
    } 

    //PPC2 Timeliness of Prenatal Care
    //ppc2_gap
    private function TimelinessPrenatalCare2($doctor_id = "" , $insurance_id = "", $clinic_id = "", $careGap , $gaps_filterYear , $insurancePrvider)
    {
        // Find Models Name for care gaps 
        $CGModelsName = $this->FindModelsName($insurance_id, $insurancePrvider);

        $PPC2 = [];
        $Title = "PPC2 Timeliness of Prenatal Care";
        $column_name = 'ppc2_gap';
        $ShortTitle = "PPC2";
        
        if ($careGap == 1) {
            $Title = "PPC2 Timeliness of Prenatal Care Insurance";
            $column_name = 'ppc2_gap_insurance';
        }

        // As per current status
        $ClosedPatients = $CGModelsName::thisYearGaps($gaps_filterYear)->with('patientinfo')->whereHas('patientinfo')->where($column_name,'Compliant');
        $OpenPatients = $CGModelsName::thisYearGaps($gaps_filterYear)->whereNotIn($column_name, ['Compliant', 'N/A']);

        // As per insurance
        $ClosedPatientsCareGapFile = $CGModelsName::thisYearGaps($gaps_filterYear)->with('patientinfo')->whereHas('patientinfo')->where('ppc2_gap_insurance','Compliant');
        
        $Refused = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, 'Patient Refused', $gaps_filterYear);
        $Scheduled = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, 'Scheduled', $gaps_filterYear);

        $Total = $CGModelsName::thisYearGaps($gaps_filterYear)->where($column_name, '!=', ['N/A']);
        
        if(!empty($doctor_id)){
            $ClosedPatients->where('doctor_id',$doctor_id);
            $ClosedPatientsCareGapFile->where('doctor_id',$doctor_id);
            $OpenPatients->where('doctor_id',$doctor_id);
            $Total->where('doctor_id',$doctor_id);
        }
        
        if(!empty($insurance_id)){
            $ClosedPatients->where('insurance_id',$insurance_id);
            $ClosedPatientsCareGapFile->where('insurance_id',$insurance_id);
            $OpenPatients->where('insurance_id',$insurance_id);
            $Total->where('insurance_id',$insurance_id);
        }
        
        if(!empty($clinic_id)){
            $clinic_id = explode(',', $clinic_id);
            $ClosedPatients->where('clinic_id',$clinic_id);
            $ClosedPatientsCareGapFile->where('clinic_id',$clinic_id);
            $Total->where('clinic_id',$clinic_id);
        }

        $ActiveNonComp = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, "", $gaps_filterYear);
        $ClosedPatientsCareGapFile = $ClosedPatientsCareGapFile->count();
        $ClosedPatients = $ClosedPatients->count();
        $ClosedPatientsDifference = $ClosedPatients - $ClosedPatientsCareGapFile;
        $ClosedPatientsDifference = ($ClosedPatientsDifference>=1 ) ? (string) $ClosedPatientsDifference : '0' ;
        $OpenPatients = $OpenPatients->count();
        $Refused = $Refused;
        $Scheduled = $Scheduled;
        $UnScheduled = $ActiveNonComp - $Scheduled - $Refused ;
        $UnScheduled = ($UnScheduled>=1 ) ? (string) $UnScheduled : '0' ;
        $Total = $Total->count();
        // $Eligible = $Refused + $Scheduled + $UnScheduled;
        
        if($Total != 0)
        {
            $Acheived = number_format($ClosedPatients * 100 / $Total);
        }else{
            $Acheived = "0";
        }

        $Required_Par = "76.20" ; 

        if($Acheived >= "76.20"){
            $Star = "5";
        }else if($Acheived >= "75.20"){
            $Star = "4";
        }else if($Acheived >= "74.20"){
            $Star = "3";
        }else if($Acheived >= "73.20"){
            $Star = "2";
        }else{
            $Star = "-";
        }

        $Members_remaining =  ((($Required_Par - $Acheived) * $Total) / 100)  ;  
        if($Members_remaining <= 0 ){
            $Members_remaining = "-";
        }else if($Members_remaining > 0 && $Members_remaining < 1){
            $Members_remaining = 1;
        }else{
            $Members_remaining = number_format($Members_remaining);
        }
        
        $PPC2 = [
            'Title'                     => $Title,
            'ShortTitle'                => $ShortTitle,
            'ActiveNonComp'             => $ActiveNonComp,
            'ClosedPatients'            => $ClosedPatients,
            'ClosedPatientsDifference'  => $ClosedPatientsDifference,
            'OpenPatients'              => $OpenPatients,
            'Refused'                   => $Refused,
            'Scheduled'                 => $Scheduled,
            'UnScheduled'               => $UnScheduled,
            'Total'                     => $Total,
            'Acheived'                  => $Acheived,
            'Required_Par'              => $Required_Par,
            'Star'                      => $Star,
            'Members_remaining'         => $Members_remaining,
            'db_col_name'               => $column_name,
        ];

        return $PPC2;
    } 

    //WCV - Well-Child Visits for Age 3-21
    //well_child_visits_gap
    private function WellChildVisits($doctor_id = "" , $insurance_id = "", $clinic_id = "", $careGap , $gaps_filterYear , $insurancePrvider)
    {
        // Find Models Name for care gaps 
        $CGModelsName = $this->FindModelsName($insurance_id, $insurancePrvider);

        $WCV = [];
        $Title = "WCV - Well-Child Visits for Age 3-21";
        $column_name = 'well_child_visits_gap';
        $ShortTitle = "WCV";
        
        if ($careGap == 1) {
            $Title = "WCV - Well-Child Visits for Age 3-21 Insurance";
            $column_name = 'well_child_visits_gap_insurance';
        }

        // As per current status
        $ClosedPatients = $CGModelsName::thisYearGaps($gaps_filterYear)->with('patientinfo')->whereHas('patientinfo')->where($column_name,'Compliant');
        $OpenPatients = $CGModelsName::thisYearGaps($gaps_filterYear)->whereNotIn($column_name, ['Compliant', 'N/A']);

        // As per insurance
        $ClosedPatientsCareGapFile = $CGModelsName::thisYearGaps($gaps_filterYear)->with('patientinfo')->whereHas('patientinfo')->where('well_child_visits_gap_insurance','Compliant');
        
        $Refused = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, 'Patient Refused', $gaps_filterYear);
        $Scheduled = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, 'Scheduled', $gaps_filterYear);

        $Total = $CGModelsName::thisYearGaps($gaps_filterYear)->where($column_name, '!=', ['N/A']);
        
        if(!empty($doctor_id)){
            $ClosedPatients->where('doctor_id',$doctor_id);
            $ClosedPatientsCareGapFile->where('doctor_id',$doctor_id);
            $OpenPatients->where('doctor_id',$doctor_id);
            $Total->where('doctor_id',$doctor_id);
        }
        
        if(!empty($insurance_id)){
            $ClosedPatients->where('insurance_id',$insurance_id);
            $ClosedPatientsCareGapFile->where('insurance_id',$insurance_id);
            $OpenPatients->where('insurance_id',$insurance_id);
            $Total->where('insurance_id',$insurance_id);
        }
        
        if(!empty($clinic_id)){
            $clinic_id = explode(',', $clinic_id);
            $ClosedPatients->where('clinic_id',$clinic_id);
            $ClosedPatientsCareGapFile->where('clinic_id',$clinic_id);
            $Total->where('clinic_id',$clinic_id);
        }

        $ActiveNonComp = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, "", $gaps_filterYear);
        $ClosedPatientsCareGapFile = $ClosedPatientsCareGapFile->count();
        $ClosedPatients = $ClosedPatients->count();
        $ClosedPatientsDifference = $ClosedPatients - $ClosedPatientsCareGapFile;
        $ClosedPatientsDifference = ($ClosedPatientsDifference>=1 ) ? (string) $ClosedPatientsDifference : '0' ;
        $OpenPatients = $OpenPatients->count();
        $Refused = $Refused;
        $Scheduled = $Scheduled;
        $UnScheduled = $ActiveNonComp - $Scheduled - $Refused ;
        $UnScheduled = ($UnScheduled>=1 ) ? (string) $UnScheduled : '0' ;
        $Total = $Total->count();
        // $Eligible = $Refused + $Scheduled + $UnScheduled;
        
        if($Total != 0)
        {
            $Acheived = number_format($ClosedPatients * 100 / $Total);
        }else{
            $Acheived = "0";
        }

        $Required_Par = "43.50" ; 

        if($Acheived >= "43.50"){
            $Star = "5";
        }else if($Acheived >= "42.50"){
            $Star = "4";
        }else if($Acheived >= "41.50"){
            $Star = "3";
        }else if($Acheived >= "40.50"){
            $Star = "2";
        }else{
            $Star = "-";
        }

        $Members_remaining =  ((($Required_Par - $Acheived) * $Total) / 100)  ;  
        if($Members_remaining <= 0 ){
            $Members_remaining = "-";
        }else if($Members_remaining > 0 && $Members_remaining < 1){
            $Members_remaining = 1;
        }else{
            $Members_remaining = number_format($Members_remaining);
        }
        
        $WCV = [
            'Title'                     => $Title,
            'ShortTitle'                => $ShortTitle,
            'ActiveNonComp'             => $ActiveNonComp,
            'ClosedPatients'            => $ClosedPatients,
            'ClosedPatientsDifference'  => $ClosedPatientsDifference,
            'OpenPatients'              => $OpenPatients,
            'Refused'                   => $Refused,
            'Scheduled'                 => $Scheduled,
            'UnScheduled'               => $UnScheduled,
            'Total'                     => $Total,
            'Acheived'                  => $Acheived,
            'Required_Par'              => $Required_Par,
            'Star'                      => $Star,
            'Members_remaining'         => $Members_remaining,
            'db_col_name'               => $column_name,
        ];

        return $WCV;
    }

    //Chlamydia Screening
    //chlamydia_gap
    private function ChlamydiaScreening($doctor_id = "" , $insurance_id = "", $clinic_id = "", $careGap , $gaps_filterYear , $insurancePrvider)
    {
       // Find Models Name for care gaps 
       $CGModelsName = $this->FindModelsName($insurance_id, $insurancePrvider);

        $CS = [];
        $Title = "Chlamydia Screening";
        $column_name = 'chlamydia_gap';
        $ShortTitle = "CS";
        
        if ($careGap == 1) {
            $Title = "Chlamydia Screening Insurance";
            $column_name = 'chlamydia_gap_insurance';
        }

        // As per current status
        $ClosedPatients = $CGModelsName::thisYearGaps($gaps_filterYear)->with('patientinfo')->whereHas('patientinfo')->where($column_name,'Compliant');
        $OpenPatients = $CGModelsName::thisYearGaps($gaps_filterYear)->whereNotIn($column_name, ['Compliant', 'N/A']);

        // As per insurance
        $ClosedPatientsCareGapFile = $CGModelsName::thisYearGaps($gaps_filterYear)->with('patientinfo')->whereHas('patientinfo')->where('chlamydia_gap_insurance','Compliant');
        
        $Refused = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, 'Patient Refused', $gaps_filterYear);
        $Scheduled = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, 'Scheduled', $gaps_filterYear);

        $Total = $CGModelsName::thisYearGaps($gaps_filterYear)->where($column_name, '!=', ['N/A']);
        
        if(!empty($doctor_id)){
            $ClosedPatients->where('doctor_id',$doctor_id);
            $ClosedPatientsCareGapFile->where('doctor_id',$doctor_id);
            $OpenPatients->where('doctor_id',$doctor_id);
            $Total->where('doctor_id',$doctor_id);
        }
        
        if(!empty($insurance_id)){
            $ClosedPatients->where('insurance_id',$insurance_id);
            $ClosedPatientsCareGapFile->where('insurance_id',$insurance_id);
            $OpenPatients->where('insurance_id',$insurance_id);
            $Total->where('insurance_id',$insurance_id);
        }
        
        if(!empty($clinic_id)){
            $clinic_id = explode(',', $clinic_id);
            $ClosedPatients->where('clinic_id',$clinic_id);
            $ClosedPatientsCareGapFile->where('clinic_id',$clinic_id);
            $Total->where('clinic_id',$clinic_id);
        }

        $ActiveNonComp = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, "", $gaps_filterYear);
        $ClosedPatientsCareGapFile = $ClosedPatientsCareGapFile->count();
        $ClosedPatients = $ClosedPatients->count();
        $ClosedPatientsDifference = $ClosedPatients - $ClosedPatientsCareGapFile;
        $ClosedPatientsDifference = ($ClosedPatientsDifference>=1 ) ? (string) $ClosedPatientsDifference : '0' ;
        $OpenPatients = $OpenPatients->count();
        $Refused = $Refused;
        $Scheduled = $Scheduled;
        $UnScheduled = $ActiveNonComp - $Scheduled - $Refused ;
        $UnScheduled = ($UnScheduled>=1 ) ? (string) $UnScheduled : '0' ;
        $Total = $Total->count();
        // $Eligible = $Refused + $Scheduled + $UnScheduled;
        
        if($Total != 0)
        {
            $Acheived = number_format($ClosedPatients * 100 / $Total);
        }else{
            $Acheived = "0";
        }

        $Required_Par = "55.30" ; 

        if($Acheived >= "55.30"){
            $Star = "5";
        }else if($Acheived >= "54.30"){
            $Star = "4";
        }else if($Acheived >= "53.30"){
            $Star = "3";
        }else if($Acheived >= "52.30"){
            $Star = "2";
        }else{
            $Star = "-";
        }

        $Members_remaining =  ((($Required_Par - $Acheived) * $Total) / 100)  ;  
        if($Members_remaining <= 0 ){
            $Members_remaining = "-";
        }else if($Members_remaining > 0 && $Members_remaining < 1){
            $Members_remaining = 1;
        }else{
            $Members_remaining = number_format($Members_remaining);
        }
        
        $CS = [
            'Title'                     => $Title,
            'ShortTitle'                => $ShortTitle,
            'ActiveNonComp'             => $ActiveNonComp,
            'ClosedPatients'            => $ClosedPatients,
            'ClosedPatientsDifference'  => $ClosedPatientsDifference,
            'OpenPatients'              => $OpenPatients,
            'Refused'                   => $Refused,
            'Scheduled'                 => $Scheduled,
            'UnScheduled'               => $UnScheduled,
            'Total'                     => $Total,
            'Acheived'                  => $Acheived,
            'Required_Par'              => $Required_Par,
            'Star'                      => $Star,
            'Members_remaining'         => $Members_remaining,
            'db_col_name'               => $column_name,
        ];

        return $CS;
    } 

    //Follow-Up After Hospitalization for Mental Illness (FUH 30-Day)
    //fuh_30Day_gap
    private function fuh_30Day($doctor_id = "" , $insurance_id = "", $clinic_id = "", $careGap , $gaps_filterYear , $insurancePrvider)
    {
        // Find Models Name for care gaps 
        $CGModelsName = $this->FindModelsName($insurance_id, $insurancePrvider);

        $FUH_30DAY = [];
        $Title = "Follow-Up After Hospitalization for Mental Illness (FUH 30-Day)";
        $column_name = 'fuh_30Day_gap';
        $ShortTitle = "FUH_30DAY";
        
        if ($careGap == 1) {
            $Title = "Follow-Up After Hospitalization for Mental Illness (FUH 30-Day) Insurance";
            $column_name = 'fuh_30Day_gap_insurance';
        }

        // As per current status
        $ClosedPatients = $CGModelsName::thisYearGaps($gaps_filterYear)->with('patientinfo')->whereHas('patientinfo')->where($column_name,'Compliant');
        $OpenPatients = $CGModelsName::thisYearGaps($gaps_filterYear)->whereNotIn($column_name, ['Compliant', 'N/A']);

        // As per insurance
        $ClosedPatientsCareGapFile = $CGModelsName::thisYearGaps($gaps_filterYear)->with('patientinfo')->whereHas('patientinfo')->where('fuh_30Day_gap_insurance','Compliant');
        
        $Refused = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, 'Patient Refused', $gaps_filterYear);
        $Scheduled = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, 'Scheduled', $gaps_filterYear);

        $Total = $CGModelsName::thisYearGaps($gaps_filterYear)->where($column_name, '!=', ['N/A']);
        
        if(!empty($doctor_id)){
            $ClosedPatients->where('doctor_id',$doctor_id);
            $ClosedPatientsCareGapFile->where('doctor_id',$doctor_id);
            $OpenPatients->where('doctor_id',$doctor_id);
            $Total->where('doctor_id',$doctor_id);
        }
        
        if(!empty($insurance_id)){
            $ClosedPatients->where('insurance_id',$insurance_id);
            $ClosedPatientsCareGapFile->where('insurance_id',$insurance_id);
            $OpenPatients->where('insurance_id',$insurance_id);
            $Total->where('insurance_id',$insurance_id);
        }
        
        if(!empty($clinic_id)){
            $clinic_id = explode(',', $clinic_id);
            $ClosedPatients->where('clinic_id',$clinic_id);
            $ClosedPatientsCareGapFile->where('clinic_id',$clinic_id);
            $Total->where('clinic_id',$clinic_id);
        }

        $ActiveNonComp = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, "", $gaps_filterYear);
        $ClosedPatientsCareGapFile = $ClosedPatientsCareGapFile->count();
        $ClosedPatients = $ClosedPatients->count();
        $ClosedPatientsDifference = $ClosedPatients - $ClosedPatientsCareGapFile;
        $ClosedPatientsDifference = ($ClosedPatientsDifference>=1 ) ? (string) $ClosedPatientsDifference : '0' ;
        $OpenPatients = $OpenPatients->count();
        $Refused = $Refused;
        $Scheduled = $Scheduled;
        $UnScheduled = $ActiveNonComp - $Scheduled - $Refused ;
        $UnScheduled = ($UnScheduled>=1 ) ? (string) $UnScheduled : '0' ;
        $Total = $Total->count();
        // $Eligible = $Refused + $Scheduled + $UnScheduled;
        
        if($Total != 0)
        {
            $Acheived = number_format($ClosedPatients * 100 / $Total);
        }else{
            $Acheived = "0";
        }

        $Required_Par = "58.70" ; 

        if($Acheived >= "58.70"){
            $Star = "5";
        }else if($Acheived >= "57.70"){
            $Star = "4";
        }else if($Acheived >= "56.70"){
            $Star = "3";
        }else if($Acheived >= "55.70"){
            $Star = "2";
        }else{
            $Star = "-";
        }

        $Members_remaining =  ((($Required_Par - $Acheived) * $Total) / 100)  ;  
        if($Members_remaining <= 0 ){
            $Members_remaining = "-";
        }else if($Members_remaining > 0 && $Members_remaining < 1){
            $Members_remaining = 1;
        }else{
            $Members_remaining = number_format($Members_remaining);
        }
        
        $FUH_30DAY = [
            'Title'                     => $Title,
            'ShortTitle'                => $ShortTitle,
            'ActiveNonComp'             => $ActiveNonComp,
            'ClosedPatients'            => $ClosedPatients,
            'ClosedPatientsDifference'  => $ClosedPatientsDifference,
            'OpenPatients'              => $OpenPatients,
            'Refused'                   => $Refused,
            'Scheduled'                 => $Scheduled,
            'UnScheduled'               => $UnScheduled,
            'Total'                     => $Total,
            'Acheived'                  => $Acheived,
            'Required_Par'              => $Required_Par,
            'Star'                      => $Star,
            'Members_remaining'         => $Members_remaining,
            'db_col_name'               => $column_name,
        ];

        return $FUH_30DAY;
    } 

    //Follow-Up After Hospitalization for Mental Illness (FUH 7-Day)
    //fuh_7Day_gap
    private function fuh_7Day($doctor_id = "" , $insurance_id = "", $clinic_id = "", $careGap , $gaps_filterYear , $insurancePrvider)
    {
        // Find Models Name for care gaps 
        $CGModelsName = $this->FindModelsName($insurance_id, $insurancePrvider);

        $FUH_7DAY = [];
        $Title = "Follow-Up After Hospitalization for Mental Illness (FUH 7-Day)";
        $column_name = 'fuh_7Day_gap';
        $ShortTitle = "FUH_7DAY";
        
        if ($careGap == 1) {
            $Title = "Follow-Up After Hospitalization for Mental Illness (FUH 7-Day) Insurance";
            $column_name = 'fuh_7Day_gap_insurance';
        }

        // As per current status
        $ClosedPatients = $CGModelsName::thisYearGaps($gaps_filterYear)->with('patientinfo')->whereHas('patientinfo')->where($column_name,'Compliant');
        $OpenPatients = $CGModelsName::thisYearGaps($gaps_filterYear)->whereNotIn($column_name, ['Compliant', 'N/A']);

        // As per insurance
        $ClosedPatientsCareGapFile = $CGModelsName::thisYearGaps($gaps_filterYear)->with('patientinfo')->whereHas('patientinfo')->where('fuh_7Day_gap_insurance','Compliant');
        
        $Refused = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, 'Patient Refused', $gaps_filterYear);
        $Scheduled = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, 'Scheduled', $gaps_filterYear);

        $Total = $CGModelsName::thisYearGaps($gaps_filterYear)->where($column_name, '!=', ['N/A']);
        
        if(!empty($doctor_id)){
            $ClosedPatients->where('doctor_id',$doctor_id);
            $ClosedPatientsCareGapFile->where('doctor_id',$doctor_id);
            $OpenPatients->where('doctor_id',$doctor_id);
            $Total->where('doctor_id',$doctor_id);
        }
        
        if(!empty($insurance_id)){
            $ClosedPatients->where('insurance_id',$insurance_id);
            $ClosedPatientsCareGapFile->where('insurance_id',$insurance_id);
            $OpenPatients->where('insurance_id',$insurance_id);
            $Total->where('insurance_id',$insurance_id);
        }
        
        if(!empty($clinic_id)){
            $clinic_id = explode(',', $clinic_id);
            $ClosedPatients->where('clinic_id',$clinic_id);
            $ClosedPatientsCareGapFile->where('clinic_id',$clinic_id);
            $Total->where('clinic_id',$clinic_id);
        }

        $ActiveNonComp = $this->ActiveNonComp($column_name,$doctor_id,$insurance_id,$clinic_id, "", $gaps_filterYear);
        $ClosedPatientsCareGapFile = $ClosedPatientsCareGapFile->count();
        $ClosedPatients = $ClosedPatients->count();
        $ClosedPatientsDifference = $ClosedPatients - $ClosedPatientsCareGapFile;
        $ClosedPatientsDifference = ($ClosedPatientsDifference>=1 ) ? (string) $ClosedPatientsDifference : '0' ;
        $OpenPatients = $OpenPatients->count();
        $Refused = $Refused;
        $Scheduled = $Scheduled;
        $UnScheduled = $ActiveNonComp - $Scheduled - $Refused ;
        $UnScheduled = ($UnScheduled>=1 ) ? (string) $UnScheduled : '0' ;
        $Total = $Total->count();
        // $Eligible = $Refused + $Scheduled + $UnScheduled;
        
        if($Total != 0)
        {
            $Acheived = number_format($ClosedPatients * 100 / $Total);
        }else{
            $Acheived = "0";
        }

        $Required_Par = "38.0" ; 

        if($Acheived >= "38.0"){
            $Star = "5";
        }else if($Acheived >= "37.0"){
            $Star = "4";
        }else if($Acheived >= "36.0"){
            $Star = "3";
        }else if($Acheived >= "35.0"){
            $Star = "2";
        }else{
            $Star = "-";
        }

        $Members_remaining =  ((($Required_Par - $Acheived) * $Total) / 100)  ;  
        if($Members_remaining <= 0 ){
            $Members_remaining = "-";
        }else if($Members_remaining > 0 && $Members_remaining < 1){
            $Members_remaining = 1;
        }else{
            $Members_remaining = number_format($Members_remaining);
        }
        
        $FUH_7DAY = [
            'Title'                     => $Title,
            'ShortTitle'                => $ShortTitle,
            'ActiveNonComp'             => $ActiveNonComp,
            'ClosedPatients'            => $ClosedPatients,
            'ClosedPatientsDifference'  => $ClosedPatientsDifference,
            'OpenPatients'              => $OpenPatients,
            'Refused'                   => $Refused,
            'Scheduled'                 => $Scheduled,
            'UnScheduled'               => $UnScheduled,
            'Total'                     => $Total,
            'Acheived'                  => $Acheived,
            'Required_Par'              => $Required_Par,
            'Star'                      => $Star,
            'Members_remaining'         => $Members_remaining,
            'db_col_name'               => $column_name,
        ];

        return $FUH_7DAY;
    } 


    public function patientByStatus(Request $request) 
    {
        $status = @$request->status_type ?? "";
        $insurance_id = @$request->insurance_id ?? "";
        $doctor_id = @$request->doctor_id ?? "";
        $assignedTitle = @$request->assignedTitle ?? "";
        $filterYear = @$request->filter_year ?? "";
        $currentYear = Carbon::now()->year;

        $assignedTitle = '1';
        if ($assignedTitle == "Assignable patients") {
            $assignedTitle = '2';
        }

        $tableSource = new Patients();
        $logPopulation = PatientStatusLogs::where('insurance_id', $insurance_id)->where('patient_year', $filterYear)->count();

        if ($filterYear < $currentYear && $logPopulation > 0) {
            $tableSource = new PatientStatusLogs();
        }

        try {
            $patientIds = $tableSource->where(function ($query) use ($insurance_id, $filterYear) {
                $query->where('insurance_id', $insurance_id)
                ->orWhereHas('insuranceHistories', function ($query) use ($insurance_id, $filterYear) {
                    $query->where('insurance_id', $insurance_id)->whereYear('insurance_end_date', $filterYear);
                });
            })
            ->when(!empty($status), function ($query) use ($status) {
                if ($status == 'active') {
                    $query->whereIn('group', ['1', '2']);
                } elseif ($status == 'inactive') {
                    $query->where('group', '3');
                } elseif ($status == 'lost') {
                    $query->where('group', '4');
                } elseif ($status == 'uncategorized') {
                    $query->WhereNull('group');
                }
            })
            ->when (!empty($assignedTitle), function ($query) use ($assignedTitle) {
                if ($assignedTitle == "1") {
                    $query->where(function ($query) {
                        $query->where('status', 1)->whereNotNull('status');
                    });
                } else {
                    $query->where(function ($query) {
                        $query->where('status', 2)->orWhereNull('status');
                    });
                }
            })
            ->when(!empty($doctor_id), function ($query) use ($doctor_id) {
                $query->where('doctor_id', $doctor_id);
            })->get()->toArray();
            

            $currentYear = Carbon::now()->year;
            if ($logPopulation == 0 && $filterYear == $currentYear) {
                $filterYear = Carbon::now()->subYear()->year;
                // Filter rows where patient_year matches the provided year
                $yearPopulation = array_filter($patientIds, function($row) use ($filterYear) {
                    return $row['patient_year'] == $filterYear;
                });
            } else {
                $yearPopulation = array_filter($patientIds, function($row) use ($filterYear) {
                    return $row['patient_year'] == $filterYear;
                });
            }

            $patientIds = class_basename($tableSource) == 'Patients' ? array_column($yearPopulation, 'id') : array_column($yearPopulation, 'patient_id');

            $response = [
                'success' => true,
                'data' => $patientIds,
            ];

        } catch (\Exception $e) {
            $response = array('success'=>false,'message'=>$e->getMessage(), 'line'=>$e->getLine());
        }
        return response()->json($response);
    }

    // Getting the insurance with largest population
    public function FindLargestPopulationInsurance()
    {
        $insuranceWithMostPatients = Insurances::withCount('patients')->orderByDesc('patients_count')->first();
        return $insuranceWithMostPatients->id;
    }

    // Getting insurance name from id.
    public function insuranceNameFind($insurance_id)
    {
        $insuranceNameFound = Insurances::whereNull('deleted_at')->where('id', $insurance_id)->get()->first();
        return $insuranceNameFound;
    }

    // TO create a variable fetch the data from as respective table
    public function insuranceSourceFind($insurance_id, $gap_status)
    {
        $source = [];
        $insurancePrvider = $this->insuranceNameFind($insurance_id);  
        if ( !empty($insurance_id) &&   $insurancePrvider->type_id == 1  && $insurancePrvider->provider == "hcpw-001" ) {
            $source = $gap_status != 'Pending Visit' ? 'careGapsData' : 'careGapsData.caregapsDetails';
        } 
        elseif ( !empty($insurance_id) &&   $insurancePrvider->type_id == 1  && $insurancePrvider->provider == "hum-001" ) { 
            $source = $gap_status != 'Pending Visit' ? 'careGapsDataHumana' : 'careGapsDataHumana.caregapsDetails';
        } 
        elseif ( !empty($insurance_id) &&   $insurancePrvider->type_id == 1  && $insurancePrvider->provider == "med-arz-001" ) { 
            $source = $gap_status != 'Pending Visit' ? 'careGapsDataMedicareArizona' : 'careGapsDataMedicareArizona.caregapsDetails';
        }
        elseif ( !empty($insurance_id) &&   $insurancePrvider->type_id == 1  && $insurancePrvider->provider == "aet-001" ) {
            $source = $gap_status != 'Pending Visit' ? 'careGapsDataAetnaMedicare' : 'careGapsDataAetnaMedicare.caregapsDetails';
        }
        elseif ( !empty($insurance_id) &&   $insurancePrvider->type_id == 1  && $insurancePrvider->provider == "allwell-001" ) {
            $source = $gap_status != 'Pending Visit' ? 'careGapsDataAllwellMedicare' : 'careGapsDataAllwellMedicare.caregapsDetails';
        } 
        elseif ( !empty($insurance_id) &&   $insurancePrvider->type_id == 3  && $insurancePrvider->provider == "hcarz-001" ) {
            $source = $gap_status != 'Pending Visit' ? 'careGapsDataHealthchoiceArizona' : 'careGapsDataHealthchoiceArizona.caregapsDetails';
        } 
        elseif ( !empty($insurance_id) &&   $insurancePrvider->type_id == 1  && $insurancePrvider->provider == "uhc-001" ) {
            $source = $gap_status != 'Pending Visit' ? 'careGapsDataUnitedHealthcare' : 'careGapsDataUnitedHealthcare.caregapsDetails';
        }
        return $source;
    }

    public function FindModelsName ($insurance_id, $insurancePrvider)
    {
        if ( !empty($insurance_id) &&   $insurancePrvider->type_id == 1  && $insurancePrvider->provider ==  "hcpw-001" ) {
            $CGModelsName = new CareGaps;
        }
        // Humana 
        elseif (!empty($insurance_id) &&   $insurancePrvider->type_id == 1  && $insurancePrvider->provider ==  "hum-001"){
            $CGModelsName = new HumanaCareGaps;
        }
        // Medicare Arizona
        elseif (!empty($insurance_id) &&   $insurancePrvider->type_id == 1  && $insurancePrvider->provider ==  "med-arz-001"){
            $CGModelsName = new MedicareArizonaCareGaps;
        }
        // Aetna Medicare
        elseif (!empty($insurance_id) &&   $insurancePrvider->type_id == 1  && $insurancePrvider->provider ==  "aet-001"){
            $CGModelsName = new AetnaMedicareCareGaps;
        }
        // Allwell Medicare
        elseif (!empty($insurance_id) &&   $insurancePrvider->type_id == 1  && $insurancePrvider->provider ==  "allwell-001"){
            $CGModelsName = new AllwellMedicareCareGaps;
        }
        // Healthchoice Arizona
        elseif (!empty($insurance_id) &&   $insurancePrvider->type_id == 3  && $insurancePrvider->provider ==  "hcarz-001"){
            $CGModelsName = new HealthchoiceArizonaCareGaps;
        }
        // United Health Care
        elseif (!empty($insurance_id) &&   $insurancePrvider->type_id == 1  && $insurancePrvider->provider ==  "uhc-001"){
            $CGModelsName = new UnitedHealthcareCareGaps;
        }
        return $CGModelsName;
    }

    public function breastCancerScreeningStarConditions($insurancePrvider, $Acheived)
    {
        // Health Choice Pathway
        if(!empty($insurancePrvider) && $insurancePrvider->type_id == 1 && $insurancePrvider->provider == "hcpw-001"){
            $Required_Par = "74";

            if($Acheived >= "74"){
                $Star = "4";
            }else if($Acheived >= "73"){
                $Star = "3";
            }else if($Acheived > "72"){
                $Star = "2";
            }else if($Acheived < "72"){
                $Star = "1";
            }else{
                $Star = "-";
            }
        // humana
        } elseif(!empty($insurancePrvider) && $insurancePrvider->type_id == 1 && $insurancePrvider->provider == "hum-001"){
            $Required_Par = "78.3";

            if($Acheived >= "78.3"){
                $Star = "5";
            }else if($Acheived >= "70"){
                $Star = "4";
            }else if($Acheived >= "62"){
                $Star = "3";
            }else if($Acheived >= "43"){
                $Star = "2";
            }else{
                $Star = "1";
            }
        // Medicare Arizona
        } elseif(!empty($insurancePrvider) && $insurancePrvider->type_id == 1 && $insurancePrvider->provider == "med-arz-001"){
            $Required_Par = "78.3";

            if($Acheived >= "78.3"){
                $Star = "5";
            }else if($Acheived >= "70"){
                $Star = "4";
            }else if($Acheived >= "62"){
                $Star = "3";
            }else if($Acheived >= "43"){
                $Star = "2";
            }else{
                $Star = "1";
            }
        // Aetna Medicare
        } elseif(!empty($insurancePrvider) && $insurancePrvider->type_id == 1 && $insurancePrvider->provider == "aet-001"){
            $Required_Par = "79";

            if($Acheived >= "79"){
                $Star = "5";
            }else if($Acheived >= "72"){
                $Star = "4";
            }else if($Acheived >= "64"){
                $Star = "3";
            }else if($Acheived >= "45"){
                $Star = "2";
            }else{
                $Star = "1";
            }
        // Allwell Medicare
        } elseif(!empty($insurancePrvider) && $insurancePrvider->type_id == 1 && $insurancePrvider->provider == "allwell-001"){
            $Required_Par = "79";

            if($Acheived >= "79"){
                $Star = "5";
            }else if($Acheived >= "72"){
                $Star = "4";
            }else if($Acheived >= "64"){
                $Star = "3";
            }else if($Acheived >= "45"){
                $Star = "2";
            }else{
                $Star = "1";
            }
        // Health Choice Arizona
        } elseif(!empty($insurancePrvider) && $insurancePrvider->type_id == 3 && $insurancePrvider->provider == "hcarz-001"){
            $Required_Par = "47.80";

            if($Acheived >= "47.80"){
                $Star = "5";
            }else if($Acheived >= "46.80"){
                $Star = "4";
            }else if($Acheived >= "45.80"){
                $Star = "3";
            }else if($Acheived >= "44.80"){
                $Star = "2";
            }else{
                $Star = "1";
            }
        // United Healthcare - MCR
        } elseif(!empty($insurancePrvider) && $insurancePrvider->type_id == 1 && $insurancePrvider->provider == "uhc-001"){
            $Required_Par = "79.0";

            if($Acheived >= $Required_Par){
                $Star = "5";
            }else if($Acheived >= "71"){
                $Star = "4";
            }else if($Acheived >= "70.0"){
                $Star = "3";
            }else if($Acheived >= "69.0"){
                $Star = "2";
            }else{
                $Star = "1";
            }
        }

        
        $BCSSC['Required_Par'] = $Required_Par;
        $BCSSC['Star'] = $Star;
        return $BCSSC;
    }

    public function colorectalCancerGapStarConditions ($insurancePrvider, $Acheived)
    {
        // Health Choice Pathway
        if(!empty($insurancePrvider) && $insurancePrvider->type_id == 1 && $insurancePrvider->provider == "hcpw-001"){
            $Required_Par = "75";
            $Star = "-";

            if($Acheived >= $Required_Par) {
                $Star = "4";
            } else if($Acheived >= "74") {
                $Star = "3";
            } else if($Acheived >= "73") {
                $Star = "2";
            } elseif ($Acheived < "73")  {
                $Star = "1";
            } else{
                $Star = "-";
            }
        // humana
        } elseif(!empty($insurancePrvider) && $insurancePrvider->type_id == 1 && $insurancePrvider->provider == "hum-001"){
            $Required_Par = "78.6";
    
            if($Acheived >= "78.6"){
                $Star = "5";
            }else if($Acheived >= "71"){
                $Star = "4";
            }else if($Acheived >= "60"){
                $Star = "3";
            }else if($Acheived >= "43"){
                $Star = "2";
            }else{
                $Star = "1";
            }
        // Medicare Arizona
        } elseif(!empty($insurancePrvider) && $insurancePrvider->type_id == 1 && $insurancePrvider->provider == "med-arz-001"){
            $Required_Par = "78.6";

            if($Acheived >= "78.6"){
                $Star = "5";
            }else if($Acheived >= "71"){
                $Star = "4";
            }else if($Acheived >= "60"){
                $Star = "3";
            }else if($Acheived >= "43"){
                $Star = "2";
            }else{
                $Star = "1";
            }
        // Aetna Medicare
        } elseif(!empty($insurancePrvider) && $insurancePrvider->type_id == 1 && $insurancePrvider->provider == "aet-001"){
            $Required_Par = "82";
            $Star = "-";

            if($Acheived >= "82"){
                $Star = "5";
            }else if($Acheived >= "73"){
                $Star = "4";
            }else if($Acheived >= "64"){
                $Star = "3";
            }else if($Acheived >= "48"){
                $Star = "2";
            }else{
                $Star = "1";
            }
        // Allwell Medicare
        } elseif(!empty($insurancePrvider) && $insurancePrvider->type_id == 1 && $insurancePrvider->provider == "allwell-001"){
            $Required_Par = "80";

            if($Acheived >= "80"){
                $Star = "5";
            }else if($Acheived >= "71"){
                $Star = "4";
            }else if($Acheived >= "64"){
                $Star = "3";
            }else if($Acheived >= "48"){
                $Star = "2";
            }else{
                $Star = "1";
            }
        // Health Choice Arizona
        } elseif(!empty($insurancePrvider) && $insurancePrvider->type_id == 3 && $insurancePrvider->provider == "hcarz-001"){
            $Required_Par = "47.80";

            if($Acheived >= "47.80"){
                $Star = "5";
            }else if($Acheived >= "46.80"){
                $Star = "4";
            }else if($Acheived >= "45.80"){
                $Star = "3";
            }else if($Acheived >= "44.80"){
                $Star = "2";
            }else{
                $Star = "1";
            }
        // United Healthcare - MCR
        } elseif(!empty($insurancePrvider) && $insurancePrvider->type_id == 1 && $insurancePrvider->provider == "uhc-001"){
            $Required_Par = "80.0";

            if($Acheived >= $Required_Par){
                $Star = "5";
            }else if($Acheived >= "71"){
                $Star = "4";
            }else if($Acheived >= "70.0"){
                $Star = "3";
            }else if($Acheived >= "69.0"){
                $Star = "2";
            }else{
                $Star = "1";
            }
        }

        
        $CCS['Required_Par'] = $Required_Par;
        $CCS['Star'] = $Star;
        return $CCS;
    }

    public function statinTherapyGapStarConditions($insurancePrvider, $Acheived)
    {
        // Health Choice Pathway
        if(!empty($insurancePrvider) && $insurancePrvider->type_id == 1 && $insurancePrvider->provider == "hcpw-001"){
            
            $Required_Par = "88" ; 

            if($Acheived >= $Required_Par){
                $Star = "4";
            }else if($Acheived >= "87"){
                $Star = "3";
            }else if($Acheived >= "86"){
                $Star = "2";
            }else if($Acheived < "86"){
                $Star = "1";
            }else{
                $Star = "-";
            }
         // United Healthcare - MCR
        } elseif(!empty($insurancePrvider) && $insurancePrvider->type_id == 1 && $insurancePrvider->provider == "uhc-001"){
            $Required_Par = "90.0";

            if($Acheived >= $Required_Par){
                $Star = "5";
            }else if($Acheived >= "89.0"){
                $Star = "4";
            }else if($Acheived >= "88.0"){
                $Star = "3";
            }else if($Acheived >= "87.0"){
                $Star = "2";
            }else{
                $Star = "1";
            }
        }

        
        $STG['Required_Par'] = $Required_Par;
        $STG['Star'] = $Star;
        return $STG;
    }

    public function bloodSugarPoorGapStarConditions($insurancePrvider, $Acheived)
    {
        //$Required_Par = "0";
        // Health Choice Pathway
        if(!empty($insurancePrvider) && $insurancePrvider->type_id == 1 && $insurancePrvider->provider == "hcpw-001"){
            $Required_Par = "83" ; 

            if( $Acheived  >= "83"){
                    $Star = "4";
            }else if( $Acheived  >= "82"){
                    $Star = "3";
            }else if( $Acheived  >= "81"){
                    $Star = "2";
            }else if( $Acheived  < " 81"){
                    $Star = "1";
            }else{
                $Star = "-";
            }
        // humana
        } elseif(!empty($insurancePrvider) && $insurancePrvider->type_id == 1 && $insurancePrvider->provider == "hum-001"){
            $Required_Par = "83" ; 

            if($Acheived >= "83"){
                $Star = "5";
            }else if($Acheived >= "75"){
                $Star = "4";
            }else if($Acheived >= "62"){
                $Star = "3";
            }else if($Acheived >= "39"){
                $Star = "2";
            }else{
                $Star = "1";
            }
        // Medicare Arizona
        } elseif(!empty($insurancePrvider) && $insurancePrvider->type_id == 1 && $insurancePrvider->provider == "med-arz-001"){
            $Required_Par = "78.3";

            if($Acheived >= "78.3"){
                $Star = "5";
            }else if($Acheived >= "70"){
                $Star = "4";
            }else if($Acheived >= "62"){
                $Star = "3";
            }else if($Acheived >= "43"){
                $Star = "2";
            }else{
                $Star = "1";
            }
        // Aetna Medicare
        } elseif(!empty($insurancePrvider) && $insurancePrvider->type_id == 1 && $insurancePrvider->provider == "aet-001"){
            $Required_Par = "79";

            if($Acheived >= "79"){
                $Star = "5";
            }else if($Acheived >= "72"){
                $Star = "4";
            }else if($Acheived >= "64"){
                $Star = "3";
            }else if($Acheived >= "45"){
                $Star = "2";
            }else{
                $Star = "1";
            }
        // Allwell Medicare
        } elseif(!empty($insurancePrvider) && $insurancePrvider->type_id == 1 && $insurancePrvider->provider == "allwell-001"){
            $Required_Par = "79";

            if($Acheived >= "79"){
                $Star = "5";
            }else if($Acheived >= "72"){
                $Star = "4";
            }else if($Acheived >= "64"){
                $Star = "3";
            }else if($Acheived >= "45"){
                $Star = "2";
            }else{
                $Star = "1";
            }
        // Health Choice Arizona
        } elseif(!empty($insurancePrvider) && $insurancePrvider->type_id == 3 && $insurancePrvider->provider == "hcarz-001"){
            $Required_Par = "39.90" ; 

            if( $Acheived  >= "39.90"){
                    $Star = "5";
            }else if( $Acheived  >= "38.90"){
                    $Star = "4";
            }else if( $Acheived  >= "37.90"){
                    $Star = "3";
            }else if( $Acheived  >= " 36.90"){
                    $Star = "2";
            }else{
                    $Star = "1";
            }
        }

        
        $BSPC['Required_Par'] = $Required_Par;
        $BSPC['Star'] = $Star;
        return $BSPC;
    }


    public function bloodSugarGapStarConditions ($insurancePrvider, $Acheived)
    {
        // Health Choice Pathway
        if(!empty($insurancePrvider) && $insurancePrvider->type_id == 1 && $insurancePrvider->provider == "hcpw-001"){
            $Required_Par = "83" ; 

            if($Acheived >= "83"){
                $Star = "5";
            }else if($Acheived >= "78"){
                $Star = "4";
            }else if($Acheived >= "65"){
                $Star = "3";
            }else if($Acheived >= "40"){
                $Star = "2";
            }else{
                $Star = "1";
            }   
        // humana
        } elseif(!empty($insurancePrvider) && $insurancePrvider->type_id == 1 && $insurancePrvider->provider == "hum-001"){
           
        // Medicare Arizona
        } elseif(!empty($insurancePrvider) && $insurancePrvider->type_id == 1 && $insurancePrvider->provider == "med-arz-001"){
           
            $Required_Par = "83" ; 

            if($Acheived >= "83"){
                $Star = "5";
            }else if($Acheived >= "75"){
                $Star = "4";
            }else if($Acheived >= "62"){
                $Star = "3";
            }else if($Acheived >= "39"){
                $Star = "2";
            }else{
                $Star = "1";
            }

        // Aetna Medicare
        } elseif(!empty($insurancePrvider) && $insurancePrvider->type_id == 1 && $insurancePrvider->provider == "aet-001"){
            $Required_Par = "85" ; 

            if($Acheived >= "85"){
                $Star = "5";
            }else if($Acheived >= "77"){
                $Star = "4";
            }else if($Acheived >= "64"){
                $Star = "3";
            }else if($Acheived >= "43"){
                $Star = "2";
            }else{
                $Star = "1";
            }
        // Allwell Medicare
        } elseif(!empty($insurancePrvider) && $insurancePrvider->type_id == 1 && $insurancePrvider->provider == "allwell-001"){
           
            $Required_Par = "83" ; 

            if($Acheived >= "83"){
                $Star = "5";
            }else if($Acheived >= "78"){
                $Star = "4";
            }else if($Acheived >= "65"){
                $Star = "3";
            }else if($Acheived >= "40"){
                $Star = "2";
            }else{
                $Star = "1";
            }

         // United Healthcare - MCR
        } elseif (!empty($insurancePrvider) &&   $insurancePrvider->type_id == 1  && $insurancePrvider->provider == "uhc-001"){
            $Required_Par = "87.0" ; 

            if($Acheived >= $Required_Par){
                $Star = "5";
            }else if($Acheived >= "80"){
                $Star = "4";
            }else if($Acheived >= "89.0"){
                $Star = "3";
            }else if($Acheived >= "78.0"){
                $Star = "2";
            }else{
                $Star = "1";
            }
            
        }
        $BSC['Required_Par'] = $Required_Par;
        $BSC['Star'] = $Star;
        return $BSC;
    }

    public function controllingHighBloodPressureStarConditions ($insurancePrvider, $Acheived)
    {
        // Health Choice Pathway
        if(!empty($insurancePrvider) && $insurancePrvider->type_id == 1 && $insurancePrvider->provider == "hcpw-001"){
            $Required_Par = "76" ; 

            if($Acheived >= "76"){
                $Star = "4";
            }else if($Acheived >= "75"){
                $Star = "3";
            }else if($Acheived >= "74"){
                $Star = "2";
            }else if($Acheived < "74"){
                $Star = "1";
            }else{
                $Star = "-";
            }
        // humana
        } elseif(!empty($insurancePrvider) && $insurancePrvider->type_id == 1 && $insurancePrvider->provider == "hum-001"){
            $Required_Par = "80" ; 

            if($Acheived >= "80"){
                $Star = "5";
            }else if($Acheived >= "73"){
                $Star = "4";
            }else if($Acheived >= "63"){
                $Star = "3";
            }else if($Acheived >= "48"){
                $Star = "2";
            }else{
                $Star = "1";
            }
        // Medicare Arizona
        } elseif(!empty($insurancePrvider) && $insurancePrvider->type_id == 1 && $insurancePrvider->provider == "med-arz-001"){
            $Required_Par = "80" ; 

            if($Acheived >= "80"){
                $Star = "5";
            }else if($Acheived >= "73"){
                $Star = "4";
            }else if($Acheived >= "63"){
                $Star = "3";
            }else if($Acheived >= "48"){
                $Star = "2";
            }else{
                $Star = "1";
            }
        // Aetna Medicare
        } elseif(!empty($insurancePrvider) && $insurancePrvider->type_id == 1 && $insurancePrvider->provider == "aet-001"){
            $Required_Par = "79";

            if($Acheived >= "79"){
                $Star = "5";
            }else if($Acheived >= "72"){
                $Star = "4";
            }else if($Acheived >= "64"){
                $Star = "3";
            }else if($Acheived >= "45"){
                $Star = "2";
            }else{
                $Star = "1";
            }
        // Allwell Medicare
        } elseif(!empty($insurancePrvider) && $insurancePrvider->type_id == 1 && $insurancePrvider->provider == "allwell-001"){
            $Required_Par = "80" ; 

            if($Acheived >= "80"){
                $Star = "5";
            }else if($Acheived >= "71"){
                $Star = "4";
            }else if($Acheived >= "64"){
                $Star = "3";
            }else if($Acheived >= "48"){
                $Star = "2";
            }else{
                $Star = "1";
            }
        // Health Choice Arizona
        } elseif(!empty($insurancePrvider) && $insurancePrvider->type_id == 3 && $insurancePrvider->provider == "hcarz-001"){
            $Required_Par = "58.60" ; 

            if( $Acheived  >= "58.60"){
                    $Star = "5";
            }else if( $Acheived  >= "57.60"){
                    $Star = "4";
            }else if( $Acheived  >= "56.60"){
                    $Star = "3";
            }else if( $Acheived  >= " 55.60"){
                    $Star = "2";
            }else{
                    $Star = "1";
            }
        
        // United Healthcare - MCR
        } elseif(!empty($insurancePrvider) && $insurancePrvider->type_id == 1 && $insurancePrvider->provider == "uhc-001"){
            $Required_Par = "92.0" ; 

            if( $Acheived  >=  $Required_Par){
                    $Star = "5";
            }else if( $Acheived  >= "91.0"){
                    $Star = "4";
            }else if( $Acheived  >= "90.0"){
                    $Star = "3";
            }else if( $Acheived  >= "89.0"){
                    $Star = "2";
            }else{
                    $Star = "1";
            }
        }

        
        $CHBP['Required_Par'] = $Required_Par;
        $CHBP['Star'] = $Star;
        return $CHBP;
    }

    public function StatinPersonsDiabetesStarConditions($insurancePrvider, $Acheived)
    {
        // humana
        if(!empty($insurancePrvider) && $insurancePrvider->type_id == 1 && $insurancePrvider->provider == "hum-001"){
            
            $Required_Par = "90" ; 

            if($Acheived >= "90"){
                $Star = "5";
            }else if($Acheived >= "86"){
                $Star = "4";
            }else if($Acheived >= "84"){
                $Star = "3";
            }else if($Acheived >= "80"){
                $Star = "2";
            }else{
                $Star = "1";
            }

        // Aetna Medicare
        } elseif(!empty($insurancePrvider) && $insurancePrvider->type_id == 1 && $insurancePrvider->provider == "aet-001"){
            
            $Required_Par = "93" ; 

            if($Acheived >= "93"){
                $Star = "5";
            }else if($Acheived >= "89"){
                $Star = "4";
            }else if($Acheived >= "87"){
                $Star = "3";
            }else if($Acheived >= "83"){
                $Star = "2";
            }else{
                $Star = "1";
            }
        // Allwell Medicare
        } elseif(!empty($insurancePrvider) && $insurancePrvider->type_id == 1 && $insurancePrvider->provider == "allwell-001"){
            
            $Required_Par = "91" ; 
            if($Acheived >= "91"){
                $Star = "5";
            }else if($Acheived >= "88"){
                $Star = "4";
            }else if($Acheived >= "84"){
                $Star = "3";
            }else if($Acheived >= "78"){
                $Star = "2";
            }else{
                $Star = "1";
            }

        // Health Choice Arizona
        } elseif(!empty($insurancePrvider) && $insurancePrvider->type_id == 3 && $insurancePrvider->provider == "hcarz-001"){
            $Required_Par = "47.80";

            if($Acheived >= "47.80"){
                $Star = "5";
            }else if($Acheived >= "46.80"){
                $Star = "4";
            }else if($Acheived >= "45.80"){
                $Star = "3";
            }else if($Acheived >= "44.80"){
                $Star = "2";
            }else{
                $Star = "1";
            }
        // United Healthcare - MCR
        } elseif(!empty($insurancePrvider) && $insurancePrvider->type_id == 1 && $insurancePrvider->provider == "uhc-001"){
            $Required_Par = "92.0";

            if($Acheived >= $Required_Par){
                $Star = "5";
            }else if($Acheived >= "91.0"){
                $Star = "4";
            }else if($Acheived >= "90.0"){
                $Star = "3";
            }else if($Acheived >= "89.0"){
                $Star = "2";
            }else{
                $Star = "1";
            }
        }

        
        $SUPD['Required_Par'] = $Required_Par;
        $SUPD['Star'] = $Star;
        return $SUPD;
    }

    public function olderAdultsMedicationReviewStarConditions($insurancePrvider, $Acheived)
    {
        // Health Choice Pathway
        if(!empty($insurancePrvider) && $insurancePrvider->type_id == 1 && $insurancePrvider->provider == "hcpw-001"){
            
            $Required_Par =  "94";
            if($Acheived >= "94"){
                $Star = "4";
            }else if($Acheived >= "93"){
                $Star = "3";
            }else if($Acheived >= "92"){
                $Star = "2";
            }else if($Acheived < "92"){
                $Star = "1";
            }
        // humana
        } elseif(!empty($insurancePrvider) && $insurancePrvider->type_id == 1 && $insurancePrvider->provider == "hum-001"){
            $Required_Par = "78.3";

            if($Acheived >= "78.3"){
                $Star = "5";
            }else if($Acheived >= "70"){
                $Star = "4";
            }else if($Acheived >= "62"){
                $Star = "3";
            }else if($Acheived >= "43"){
                $Star = "2";
            }else{
                $Star = "1";
            }
        // Medicare Arizona
        } elseif(!empty($insurancePrvider) && $insurancePrvider->type_id == 1 && $insurancePrvider->provider == "med-arz-001"){
            $Required_Par = "78.3";

            if($Acheived >= "78.3"){
                $Star = "5";
            }else if($Acheived >= "70"){
                $Star = "4";
            }else if($Acheived >= "62"){
                $Star = "3";
            }else if($Acheived >= "43"){
                $Star = "2";
            }else{
                $Star = "1";
            }
        // Aetna Medicare
        } elseif(!empty($insurancePrvider) && $insurancePrvider->type_id == 1 && $insurancePrvider->provider == "aet-001"){
            $Required_Par = "79";

            if($Acheived >= "79"){
                $Star = "5";
            }else if($Acheived >= "72"){
                $Star = "4";
            }else if($Acheived >= "64"){
                $Star = "3";
            }else if($Acheived >= "45"){
                $Star = "2";
            }else{
                $Star = "1";
            }
        // Allwell Medicare
        } elseif(!empty($insurancePrvider) && $insurancePrvider->type_id == 1 && $insurancePrvider->provider == "allwell-001"){
            $Required_Par =  "97";
       
            if($Acheived >= "97"){
                $Star = "5";
            }else if($Acheived >= "86"){
                $Star = "4";
            }else if($Acheived >= "74"){
                $Star = "3";
            }else if($Acheived >= "45"){
                $Star = "2";
            }else{
                $Star = "1";        }

        // Health Choice Arizona
        } elseif(!empty($insurancePrvider) && $insurancePrvider->type_id == 3 && $insurancePrvider->provider == "hcarz-001"){
            $Required_Par = "47.80";

            if($Acheived >= "47.80"){
                $Star = "5";
            }else if($Acheived >= "46.80"){
                $Star = "4";
            }else if($Acheived >= "45.80"){
                $Star = "3";
            }else if($Acheived >= "44.80"){
                $Star = "2";
            }else{
                $Star = "1";
            }
        // United Healthcare - MCR
        } elseif(!empty($insurancePrvider) && $insurancePrvider->type_id == 1 && $insurancePrvider->provider == "uhc-001"){
            $Required_Par = "98.0";

            if($Acheived >= $Required_Par){
                $Star = "5";
            }else if($Acheived >= "97"){
                $Star = "4";
            }else if($Acheived >= "96.0"){
                $Star = "3";
            }else if($Acheived >= "95.0"){
                $Star = "2";
            }else{
                $Star = "1";
            }
        }

        
        $Review['Required_Par'] = $Required_Par;
        $Review['Star'] = $Star;
        return $Review;
    }

    public function CervicalCancerScreeningStarConditions($insurancePrvider, $Acheived)
    {
        if(!empty($insurancePrvider) && $insurancePrvider->type_id == 3 && $insurancePrvider->provider == "hcarz-001"){
            $Required_Par = "52.40";

            if($Acheived >= "52.40"){
                $Star = "5";
            }else if($Acheived >= "51.40"){
                $Star = "4";
            }else if($Acheived >= "50.40"){
                $Star = "3";
            }else if($Acheived >= "49.40"){
                $Star = "2";
            }else{
                $Star = "1";
            }
        }
        $CCS['Required_Par'] = $Required_Par;
        $CCS['Star'] = $Star;
        return $CCS;
    }

    public function diabetesEyeExamStarConditions($insurancePrvider, $Acheived)
    {
        // Health Choice Pathway
        if(!empty($insurancePrvider) && $insurancePrvider->type_id == 1 && $insurancePrvider->provider == "hcpw-001"){
            $Required_Par = "73" ; 

            if($Acheived >= "73"){
                $Star = "4";
            }else if($Acheived >= "72"){
                $Star = "3";
            }else if($Acheived >= "71"){
                $Star = "2";
            }else if($Acheived < "71"){
                $Star = "1";
            }else{
                $Star = "-";
            }
        // humana
        } elseif(!empty($insurancePrvider) && $insurancePrvider->type_id == 1 && $insurancePrvider->provider == "hum-001"){
            
            $Required_Par = "87.8" ; 

            if($Acheived >= "87.8"){
                $Star = "5";
            }else if($Acheived >= "71"){
                $Star = "4";
            }else if($Acheived >= "61"){
                $Star = "3";
            }else if($Acheived >= "47"){
                $Star = "2";
            }else{
                $Star = "1";
            }
        // Medicare Arizona
        } elseif(!empty($insurancePrvider) && $insurancePrvider->type_id == 1 && $insurancePrvider->provider == "med-arz-001"){
            $Required_Par = "80" ; 

            if($Acheived >= "80"){
                $Star = "5";
            }else if($Acheived >= "73"){
                $Star = "4";
            }else if($Acheived >= "63"){
                $Star = "3";
            }else if($Acheived >= "48"){
                $Star = "2";
            }else{
                $Star = "1";
            }
        // Aetna Medicare
        } elseif(!empty($insurancePrvider) && $insurancePrvider->type_id == 1 && $insurancePrvider->provider == "aet-001"){
            $Required_Par = "81" ; 

            if($Acheived >= "81"){
                $Star = "5";
            }else if($Acheived >= "73"){
                $Star = "4";
            }else if($Acheived >= "64"){
                $Star = "3";
            }else if($Acheived >= "52"){
                $Star = "2";
            }else{
                $Star = "1";
            }
        // Allwell Medicare
        } elseif(!empty($insurancePrvider) && $insurancePrvider->type_id == 1 && $insurancePrvider->provider == "allwell-001"){
            $Required_Par = "82" ; 

            if($Acheived >= "82"){
                $Star = "5";
            }else if($Acheived >= "75"){
                $Star = "4";
            }else if($Acheived >= "64"){
                $Star = "3";
            }else if($Acheived >= "50"){
                $Star = "2";
            }else{
                $Star = "1";
            }
        // Health Choice Arizona
        } elseif(!empty($insurancePrvider) && $insurancePrvider->type_id == 3 && $insurancePrvider->provider == "hcarz-001"){
            $Required_Par = "58.60" ; 

            if( $Acheived  >=$Required_Par){
                    $Star = "5";
            }else if( $Acheived  >= "57.60"){
                    $Star = "4";
            }else if( $Acheived  >= "56.60"){
                    $Star = "3";
            }else if( $Acheived  >= " 55.60"){
                    $Star = "2";
            }else{
                    $Star = "1";
            }
         // United Health Care
        } elseif(!empty($insurancePrvider) && $insurancePrvider->type_id == 1 && $insurancePrvider->provider == "uhc-001"){
            $Required_Par = "81.0" ; 

            if( $Acheived  >= $Required_Par){
                    $Star = "5";
            }else if( $Acheived  >= "80.0"){
                    $Star = "4";
            }else if( $Acheived  >= "79.0"){
                    $Star = "3";
            }else if( $Acheived  >= "78.0"){
                    $Star = "2";
            }else{
                    $Star = "1";
            }
        }
        $EyeExam['Required_Par'] = $Required_Par;
        $EyeExam['Star'] = $Star;
        return $EyeExam;
    }

    public function StatinTherapyPatientsCardiovascularDiseaseStartConditions($insurancePrvider, $Acheived)
    {
        // Humana 
        if (!empty($insurancePrvider) &&   $insurancePrvider->type_id == 1  && $insurancePrvider->provider ==  "hum-001"){
            $Required_Par = "89" ; 

            if($Acheived >= "89"){
                $Star = "5";
            }else if($Acheived >= "85"){
                $Star = "4";
            }else if($Acheived >= "81"){
                $Star = "3";
            }else if($Acheived >= "75"){
                $Star = "2";
            }else{
                $Star = "1";
            }
        }
        // Aetna Medicare
        elseif (!empty($insurancePrvider) &&   $insurancePrvider->type_id == 1  && $insurancePrvider->provider ==  "aet-001"){
         
            $Required_Par = "93" ; 

            if($Acheived >= "93"){
                $Star = "5";
            }else if($Acheived >= "89"){
                $Star = "4";
            }else if($Acheived >= "87"){
                $Star = "3";
            }else if($Acheived >= "83"){
                $Star = "2";
            }else{
                $Star = "1";
            }

        }
        
        $SPC_STATIN['Required_Par'] = $Required_Par;
        $SPC_STATIN['Star'] = $Star;
        return $SPC_STATIN;
    }

    public function olderAdultsPainAssessmentStarConditions($insurancePrvider, $Acheived)
    {
        // Health Choice Pathway
        if(!empty($insurancePrvider) && $insurancePrvider->type_id == 1 && $insurancePrvider->provider == "hcpw-001"){
            $Required_Par =  "93";
       
            if($Acheived >= "93"){
                $Star = "4";
            }else if($Acheived >= "92"){
                $Star = "3";
            }else if($Acheived >= "91"){
                $Star = "2";
            }else if($Acheived < "91"){
                $Star = "1";
            }
    
        // humana
        } elseif(!empty($insurancePrvider) && $insurancePrvider->type_id == 1 && $insurancePrvider->provider == "hum-001"){
            $Required_Par = "78.3";

            if($Acheived >= "78.3"){
                $Star = "5";
            }else if($Acheived >= "70"){
                $Star = "4";
            }else if($Acheived >= "62"){
                $Star = "3";
            }else if($Acheived >= "43"){
                $Star = "2";
            }else{
                $Star = "1";
            }
        // Medicare Arizona
        } elseif(!empty($insurancePrvider) && $insurancePrvider->type_id == 1 && $insurancePrvider->provider == "med-arz-001"){
            $Required_Par = "78.3";

            if($Acheived >= "78.3"){
                $Star = "5";
            }else if($Acheived >= "70"){
                $Star = "4";
            }else if($Acheived >= "62"){
                $Star = "3";
            }else if($Acheived >= "43"){
                $Star = "2";
            }else{
                $Star = "1";
            }
        // Aetna Medicare
        } elseif(!empty($insurancePrvider) && $insurancePrvider->type_id == 1 && $insurancePrvider->provider == "aet-001"){
            $Required_Par = "79";

            if($Acheived >= "79"){
                $Star = "5";
            }else if($Acheived >= "72"){
                $Star = "4";
            }else if($Acheived >= "64"){
                $Star = "3";
            }else if($Acheived >= "45"){
                $Star = "2";
            }else{
                $Star = "1";
            }
        // Allwell Medicare
        } elseif(!empty($insurancePrvider) && $insurancePrvider->type_id == 1 && $insurancePrvider->provider == "allwell-001"){
            $Required_Par =  "96";
       
            if($Acheived >= "96"){
                $Star = "5";
            }else if($Acheived >= "89"){
                $Star = "4";
            }else if($Acheived >= "75"){
                $Star = "3";
            }else if($Acheived >= "52"){
                $Star = "2";
            }else{
                $Star = "1";
            }
        // Health Choice Arizona
        } elseif(!empty($insurancePrvider) && $insurancePrvider->type_id == 3 && $insurancePrvider->provider == "hcarz-001"){
            $Required_Par = "47.80";

            if($Acheived >= "47.80"){
                $Star = "5";
            }else if($Acheived >= "46.80"){
                $Star = "4";
            }else if($Acheived >= "45.80"){
                $Star = "3";
            }else if($Acheived >= "44.80"){
                $Star = "2";
            }else{
                $Star = "1";
            }
        // United Healthcare - MCR
        } elseif(!empty($insurancePrvider) && $insurancePrvider->type_id == 1 && $insurancePrvider->provider == "uhc-001"){
            $Required_Par = "96.0";

            if($Acheived >= $Required_Par){
                $Star = "5";
            }else if($Acheived >= "95"){
                $Star = "4";
            }else if($Acheived >= "94.0"){
                $Star = "3";
            }else if($Acheived >= "93.0"){
                $Star = "2";
            }else{
                $Star = "1";
            }
        }

        
        $COA4['Required_Par'] = $Required_Par;
        $COA4['Star'] = $Star;
        return $COA4;
    }

    public function medAdherenceRASStarConditions($insurancePrvider, $Acheived)
    {
        // Allwell Medicare
        if(!empty($insurancePrvider) && $insurancePrvider->type_id == 1 && $insurancePrvider->provider == "allwell-001"){
            $Required_Par = "100";
            $Star = "-";

            if($Acheived >= "100") {
                $Star = "5";
            } else if($Acheived >= "99") {
                $Star = "4";
            } else if($Acheived >= "94") {
                $Star = "3";
            } elseif ($Acheived >= "86")  {
                $Star = "2";
            } else{
                $Star = "1";
            }
        
        // United Healthcare - MCR
        } elseif(!empty($insurancePrvider) && $insurancePrvider->type_id == 1 && $insurancePrvider->provider == "uhc-001"){
            $Required_Par = "91.0";

            if($Acheived >= $Required_Par){
                $Star = "5";
            }else if($Acheived >= "90"){
                $Star = "4";
            }else if($Acheived >= "89.0"){
                $Star = "3";
            }else if($Acheived >= "88.0"){
                $Star = "2";
            }else{
                $Star = "1";
            }
        }

        
        $ADH_RAS['Required_Par'] = $Required_Par;
        $ADH_RAS['Star'] = $Star;
        return $ADH_RAS;
    }

    public function medAdherenceStatinsStarConditions($insurancePrvider, $Acheived)
    {
        // Allwell Medicare
        if(!empty($insurancePrvider) && $insurancePrvider->type_id == 1 && $insurancePrvider->provider == "allwell-001"){
            $Required_Par = "100";
            $Star = "-";

            if($Acheived >= "100") {
                $Star = "5";
            } else if($Acheived >= "100") {
                $Star = "4";
            } else if($Acheived >= "97") {
                $Star = "3";
            } elseif ($Acheived >= "92")  {
                $Star = "2";
            } else{
                $Star = "1";
            }
            
        // United Healthcare - MCR
        } elseif(!empty($insurancePrvider) && $insurancePrvider->type_id == 1 && $insurancePrvider->provider == "uhc-001"){
            $Required_Par = "91.0";

            if($Acheived >= $Required_Par){
                $Star = "5";
            }else if($Acheived >= "88"){
                $Star = "4";
            }else if($Acheived >= "87.0"){
                $Star = "3";
            }else if($Acheived >= "86.0"){
                $Star = "2";
            }else{
                $Star = "1";
            }
        }

        
        $STATIN['Required_Par'] = $Required_Par;
        $STATIN['Star'] = $Star;
        return $STATIN;
    }

    public function KidneyHealthStarConditions($insurancePrvider, $Acheived)
    {
         // Allwell Medicare
         if(!empty($insurancePrvider) && $insurancePrvider->type_id == 1 && $insurancePrvider->provider == "allwell-001"){
            $Required_Par = "100";
            $Star = "-";

            if($Acheived >= "100") {
                $Star = "5";
            } else if($Acheived >= "80") {
                $Star = "4";
            } else if($Acheived >= "60") {
                $Star = "3";
            } elseif ($Acheived >= "40")  {
                $Star = "2";
            } else{
                $Star = "1";
            }
            
        // United Healthcare - MCR
        } elseif(!empty($insurancePrvider) && $insurancePrvider->type_id == 1 && $insurancePrvider->provider == "uhc-001"){
            $Required_Par = "70.0";

            if($Acheived >= $Required_Par){
                $Star = "5";
            }else if($Acheived >= "69"){
                $Star = "4";
            }else if($Acheived >= "68.0"){
                $Star = "3";
            }else if($Acheived >= "67.0"){
                $Star = "2";
            }else{
                $Star = "1";
            }
        }
        $KED['Required_Par'] = $Required_Par;
        $KED['Star'] = $Star;
        return $KED;
    }

    public function FollowUpAfterEmergencyDepartmentVisitStarConditions($insurancePrvider, $Acheived)
    {
        // humana
        if(!empty($insurancePrvider) && $insurancePrvider->type_id == 1 && $insurancePrvider->provider == "hum-001"){
                
            $Required_Par = "78" ; 

            if($Acheived >= "78"){
                $Star = "5";
            }else if($Acheived >= "68"){
                $Star = "4";
            }else if($Acheived >= "60"){
                $Star = "3";
            }else if($Acheived >= "45"){
                $Star = "2";
            }else{
                $Star = "1";
            }
        
        // Aetna Medicare
        } 
        elseif(!empty($insurancePrvider) && $insurancePrvider->type_id == 1 && $insurancePrvider->provider == "aet-001"){
            
            $Required_Par = "77" ; 

            if($Acheived >= "77"){
                $Star = "5";
            }else if($Acheived >= "67"){
                $Star = "4";
            }else if($Acheived >= "59"){
                $Star = "3";
            }else if($Acheived >= "44"){
                $Star = "2";
            }else{
                $Star = "1";
            }
        }
        $FMC['Required_Par'] = $Required_Par;
        $FMC['Star'] = $Star;
        return $FMC;
    }
}
