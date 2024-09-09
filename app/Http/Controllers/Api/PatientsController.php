<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\Utility;

use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Api\ProgramController;
use App\Http\Controllers\Api\CommonFunctionController;

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
use App\Models\PatientStatusLogs;
use App\Models\PatientsChangesHistoryModel;

use App\Models\CareGaps;
use App\Models\HumanaCareGaps;
use App\Models\MedicareArizonaCareGaps;
use App\Models\AetnaMedicareCareGaps;
use App\Models\AllwellMedicareCareGaps;
use App\Models\HealthchoiceArizonaCareGaps;
use App\Models\UnitedHealthcareCareGaps;

use App\Models\CareGapsDetails;
use App\Models\CareGapsComments;

use Auth, Validator, DB;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;

class PatientsController extends Controller
{
    protected $per_page = '';

    public function __construct(){
	    $this->per_page = Config('constants.perpage_showdata');
	}

    /* Getting all Patients */
    public function index(Request $request)
    {
        try {
            $doctor_id = $request->input("doctor_id") ?? '';
            $patient_id = $request->input("patient_id") ?? '';
            $clinic_id = $request->input("clinic_id") ?? '';
            $insurance_id = $request->input("insurance_id") ?? '';
            $gaps_as_per = $request->input("gaps_as_per") ?? '';
            $checkGapAsPer =  '';
            if($checkGapAsPer == 'undefined'){
                $checkGapAsPer = "0";
            }

            $filterYear = "";
            if ($request->has('filter_year') && $request->filter_year !== "undefined") {
                $filterYear = $request->input("filter_year");
            }

            if(!empty($insurance_id)) {
                $insuranceAllInfo = (new DashboardController)->insuranceNameFind($insurance_id);
            }
           
            $caregap_name = $request->input("caregap_name") ?? '';
            $bulk_Assign = $request->has('bulk_assign') && (int)$request->bulk_assign == 1 ? 1 : 0;
            $get_assigned = @$request->get_assigned ?? "";

            $active = $request->input("active") ?? 1;

            $query = Patients::with(['insurance', 'doctor', 'coordinator', 'awvEncounter','diagnosis','medication','surgical_history',
            'insuranceHistories' => fn ($query) => $query->with('insurance'),
            'patients_history' => fn ($query) => $query->with('userinfo:id,first_name,mid_name,last_name'), 
            'careGapsData' => function ($query) use ($filterYear) {$query->ThisYearGaps($filterYear);},
            'careGapsDataHumana' => function ($query) use ($filterYear) {$query->ThisYearGaps($filterYear);}, 
            'careGapsDataMedicareArizona' => function ($query) use ($filterYear) {$query->ThisYearGaps($filterYear);},
            'careGapsDataAetnaMedicare' => function ($query) use ($filterYear) {$query->ThisYearGaps($filterYear);},
            'careGapsDataAllwellMedicare' => function ($query) use ($filterYear) {$query->ThisYearGaps($filterYear);},
            'careGapsDataHealthchoiceArizona' => function ($query) use ($filterYear) {$query->ThisYearGaps($filterYear);},
            'careGapsDataUnitedHealthcare' => function ($query) use ($filterYear) {$query->ThisYearGaps($filterYear);},
            //'CareGapsDetails' => function ($query) use ($filterYear) {$query->ThisYearGaps($filterYear)->with('userinfo:id,first_name,mid_name,last_name');},
            'CareGapsDetails' => fn ($query) => $query->with('userinfo:id,first_name,mid_name,last_name'),
            'careGapsCommentData' => fn ($query) => $query->with('userinfo:id,first_name,mid_name,last_name')
                ->when(!empty($caregap_name), function ($q) use ($caregap_name) {
                    $q->where('caregap_name', $caregap_name);
                })->orderBy('id', 'DESC')->get()
            ]);
          
            if ($request->has('my_patients') && $request->my_patients == 1) {
                $query->where('coordinator_id', Auth::id());
            }
          

            if(!empty($doctor_id)){
                $query->where('doctor_id',$doctor_id);
            }

            if(!empty($insurance_id)){
                $query->where('insurance_id',$insurance_id);
            }

            if(!empty($clinic_id) && count(explode(',', $clinic_id)) == 1 ) {
                $query->where('clinic_id',$clinic_id);
            }

            if(!empty($patient_id)){
                $patientIds = explode(",", $patient_id);
                $query->whereIn('id',$patientIds);
            }

            /*Search patient*/
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

                if (!is_array($search) && str_contains($search, '/')) {
                    $dob = date('Y-m-d', strtotime($search));
                }

                
                $query = $query
                        ->when(empty($first_name) && empty($last_name) && empty($dob), function($q) use($search, $get_assigned) {
                            $q->where('first_name', 'like', '%' . $search . '%')
                                ->orWhere('last_name', 'like', '%' . $search . '%')
                                ->orWhere('identity', trim($search))
                                ->orWhereHas('insurance', function($qu) use ($search) {
                                    $qu->where('name', 'like', '%' . $search . '%');
                                })
                                ->when($get_assigned === 'true', function ($q) use ($search) {
                                    $q->orWhereHas('coordinator', function ($qy) use ($search) {
                                        $qy->where('first_name', 'like', '%' . $search . '%')
                                        ->orWhere('last_name', 'like', '%' . $search . '%');
                                    });
                                });
                        })
                        ->when(!empty($dob), function ($q) use ($dob) {
                            $query = $q->where('dob', $dob);
                        })
                        ->when(!empty($first_name) && !empty($last_name), function($q) use ($first_name, $last_name) {
                            $q->where('first_name', 'like', '%' . $first_name . '%')
                            ->where('last_name', 'like', '%' . $last_name . '%');
                        });
            }
            

            if ($bulk_Assign == 0 && empty($caregap_name)) {
                $query = $query->orderBy('id','desc');
                $query = $query->paginate($this->per_page);
                $total = $query->total();
                $current_page = $query->currentPage();
                $result = $query->toArray();
                //return $result;
            } else if (!empty($caregap_name)) {
                $result = $query->get()->toArray();
            } else {
                if ($get_assigned === 'true') {
                    $bulk_Assign = 0;
                }
                $query = $query->OfCoordinatorID($bulk_Assign);
                $query = $query->orderBy('first_name');
                $result = $query->get()->toArray();
            }

            $result = @$result['data'] ?? $result;
            
            $clinicList = Clinic::select('id', 'name')->get()->toArray();

            $programs = Programs::when(!empty($clinic_id), function($query) use ($clinic_id) {
                $query->whereRaw("FIND_IN_SET('$clinic_id', clinic_id)");
            })->get()->toArray();
           
            $coordinators = User::where('role', '23')->get()->toArray();
            $list = [];
//return $result;
            foreach ($result as $key => $val) {
                $provider = @$val['insurance']['provider'] ?? '';
                $typeId = @$val['insurance']['type_id'] ?? '';
                if(!empty($val['care_gaps_details'])) {
                    foreach ($val['care_gaps_details'] as $det_key => $det_val) {
                        if(!empty($det_val['filename'])) {
                            $fileName = $det_val['filename'];
                            if (!Storage::disk(name:'s3')->exists($fileName)) {
                                $val['care_gaps_details'][$det_key]['filename'] = NULL;
                            }
                        }
                    }
                }

                $awvStatus = "";
                if (!empty($val['awv_encounter'])) {
                    $encounter = $val['awv_encounter'];
                    if ($encounter['status'] == 'Signed' || $encounter['status'] == "Seen") {
                        $awvStatus = "performed";
                    } else {
                        $awvStatus = "scheduled";
                    }
                } else {
                    $awvStatus = "not-performed";
                }

                
                if ( !empty($provider) &&  $typeId == 1 && $provider ==  "hum-001" ) {
                    $val['care_gaps_data'] = $val['care_gaps_data_humana'];
                }
                if ( !empty($provider) &&  $typeId == 1 && $provider ==  "med-arz-001" ) {
                    $val['care_gaps_data'] = $val['care_gaps_data_medicare_arizona'];
                }
                if ( !empty($provider) &&  $typeId == 1 && $provider ==  "aet-001" ) {
                    $val['care_gaps_data'] = $val['care_gaps_data_aetna_medicare'];
                }
                if ( !empty($provider) &&  $typeId == 1 && $provider ==  "allwell-001" ) {
                    $val['care_gaps_data'] = $val['care_gaps_data_allwell_medicare'];
                }
                if ( !empty($provider) &&  $typeId == 3 && $provider ==  "hcarz-001" ) {
                    $val['care_gaps_data'] = $val['care_gaps_data_healthchoice_arizona'];
                }
                if ( !empty($provider) &&  $typeId == 1 && $provider ==  "uhc-001" ) {
                    $val['care_gaps_data'] = $val['care_gaps_data_united_healthcare'];
                }
   
                $care_gap_status = "green";

                $care_gaps_array = [];

                if (!empty($val['care_gaps_data'])) {
                    $care_gaps = $val['care_gaps_data'];

                    $allNA = array_reduce($care_gaps, function($carry, $value) {
                        return $carry && ($value === 'N/A');
                    }, true);

                    $allnon_Compliant = array_reduce($care_gaps, function($carry, $value) {
                        return $carry && ($value === 'Non-Compliant');
                    }, true);

                    $all_compliant = array_reduce($care_gaps, function($carry, $value) {
                        return $carry && ($value === 'Compliant');
                    }, true);

                    $compliant = in_array('Compliant', $care_gaps);
                    $non_compliant = in_array('Non-Compliant', $care_gaps);

                    if ($allnon_Compliant) {
                        $care_gap_status = 'red';
                    } else if (($compliant || $non_compliant) && !$all_compliant) {
                        $care_gap_status = '#faad14';
                    } else if ($compliant && !$non_compliant && !$all_compliant && !$allNA) {
                        $care_gap_status = 'green';
                    }
                    
                    if ( !empty($provider) &&  $typeId == 1 && $provider ==  "hcpw-001" ) {
                        // breast cancer
                        $care_gaps_array[] = $this->bscData($val, $checkGapAsPer);
                        
                        // Colorectal Cancer
                        $care_gaps_array[] = $this->colData($val, $checkGapAsPer);
                        
                        // Blood Pressure control
                        $care_gaps_array[] = $this->cbpData($val, $checkGapAsPer);
                        
                        // Diabetes Care Poor
                        $care_gaps_array[] = $this->hba1cDataPoor($val, $checkGapAsPer);
                        
                        // EYE EXAM
                        $care_gaps_array[] = $this->eyeExamData($val, $checkGapAsPer);

                        // STATIN THERAPY
                        $care_gaps_array[] = $this->statinTherapyData($val, $checkGapAsPer);

                        // OSTEOPPROSIS MGMT
                        $care_gaps_array[] = $this->osteoporosisData($val, $checkGapAsPer);
                        
                        // ADULTS MEDICATION
                        $care_gaps_array[] = $this->adultsMedicationData($val, $checkGapAsPer);
                        
                        // ADULTS PAIN SCREENING
                        $care_gaps_array[] = $this->adultsPainData($val, $checkGapAsPer);
                        
                        // POST DISCHARGE GAP
                        $care_gaps_array[] = $this->postDischargeData($val, $checkGapAsPer);
                        
                        // ADULTS FUNC GAP
                        $care_gaps_array[] = $this->afterInpatientData($val, $checkGapAsPer);
                        
                        // AFTE INPATIENT DISCHARGE GAP
                        $care_gaps_array[] = $this->adultsFuncData($val, $checkGapAsPer);
                        
                        // AWV GAP
                        $care_gaps_array[] = $this->awvData($val, $checkGapAsPer);
                    } elseif ( !empty($provider) &&  $typeId == 1 && $provider ==  "hum-001" ) {
                        // Humana Gaps 

                        // breast cancer
                        // breast_cancer_gap 
                        $care_gaps_array[] = $this->bscData($val, $checkGapAsPer);
                      
                        // Colorectal Cancer
                        // colorectal_cancer_gap
                        $care_gaps_array[] = $this->colData($val, $checkGapAsPer);
                        
                        // Blood Pressure control
                        // high_bp_gap
                        $care_gaps_array[] = $this->cbpData($val, $checkGapAsPer);
                        
                        // EYE EXAM
                        // eye_exam_gap
                        $care_gaps_array[] = $this->eyeExamData($val, $checkGapAsPer);
                        
                        // Diabetes Care
                        // hba1c_poor_gap
                        $care_gaps_array[] = $this->hba1cDataPoor($val, $checkGapAsPer);
                        
                        // POST DISCHARGE GAP
                        // post_disc_gap
                        $care_gaps_array[] = $this->postDischargeData($val, $checkGapAsPer);
                        
                        // ADULTS FUNC GAP
                        // after_inp_disc_gap
                        $care_gaps_array[] = $this->afterInpatientData($val, $checkGapAsPer);
                                                 
                        // FAED_visit_gap
                        // Follow-Up After Emergency Department Visit for MCC (FMC)
                        $care_gaps_array[] = $this->fmcData($val, $checkGapAsPer);

                        // OMW_fracture_gap
                        // Osteoporosis Management in Women Who Had a Fracture (OMW)
                        $care_gaps_array[] = $this->omwData($val, $checkGapAsPer);

                        // pc_readmissions_gap
                        // Plan All-Cause Readmissions (PCR)
                        $care_gaps_array[] = $this->pcrData($val, $checkGapAsPer);

                        // spc_disease_gap
                        // Statin Therapy for Patients with Cardiovascular Disease: Received Statin Therapy (SPC_STATIN)
                        $care_gaps_array[] = $this->spcStatinData($val, $checkGapAsPer);

                        // ma_cholesterol_gap
                        // Medication Adherence for Cholesterol (Statins): Statins (ADH_STATIN)
                        $care_gaps_array[] = $this->adhStatinData($val, $checkGapAsPer);

                        // mad_medications_gap
                        // Medication Adherence for Diabetes Medications: Diabetes Medications (ADH_DIAB)
                        $care_gaps_array[] = $this->ahdDiabData($val, $checkGapAsPer);

                        // ma_hypertension_gap
                        // Medication Adherence for Hypertension (ACE or ARB): ACE (ADH_ACE)
                        $care_gaps_array[] = $this->ahdAceData($val, $checkGapAsPer);

                        // sup_diabetes_gap
                        // Statin Use in Persons with Diabetes (SUPD)
                        $care_gaps_array[] = $this->supdData($val, $checkGapAsPer);

                        // AWV GAP
                        // awv_gap
                        $care_gaps_array[] = $this->awvData($val, $checkGapAsPer);
                    } elseif ( !empty($provider) &&  $typeId == 1 && $provider ==  "med-arz-001" ) {
                       
                        // MedicareArizona Care Gaps 

                        // breast cancer
                        // breast_cancer_gap
                        $care_gaps_array[] = $this->bscData($val, $checkGapAsPer);
                       
                        // Colorectal Cancer
                        // colorectal_cancer_gap
                        $care_gaps_array[] = $this->colData($val, $checkGapAsPer);
                        
                        // Blood Pressure control
                        // high_bp_gap
                        $care_gaps_array[] = $this->cbpData($val, $checkGapAsPer);
                        
                        // Diabetes Care
                        // hba1c_gap
                        $care_gaps_array[] = $this->hba1cData($val, $checkGapAsPer);
                        
                        // AWV GAP
                        // awv_gap
                        $care_gaps_array[] = $this->awvData($val, $checkGapAsPer);
                    } elseif ( !empty($provider) &&  $typeId == 1 && $provider ==  "aet-001" ) {
                        // Aetna Gaps 

                        // breast cancer
                        // breast_cancer_gap
                        $care_gaps_array[] = $this->bscData($val, $checkGapAsPer);
                       
                        // Colorectal Cancer
                        // colorectal_cancer_gap
                        $care_gaps_array[] = $this->colData($val, $checkGapAsPer);
                        
                        // EYE EXAM
                        // eye_exam_gap
                        $care_gaps_array[] = $this->eyeExamData($val, $checkGapAsPer);
                        
                        // Diabetes Care
                        // hba1c_poor_gap
                        $care_gaps_array[] = $this->hba1cData($val, $checkGapAsPer);
                                             
                        // FAED_visit_gap
                        // Follow-Up After Emergency Department Visit for MCC (FMC)
                        $care_gaps_array[] = $this->fmcData($val, $checkGapAsPer);

                        // OMW_fracture_gap
                        // Osteoporosis Management in Women Who Had a Fracture (OMW)
                        $care_gaps_array[] = $this->omwData($val, $checkGapAsPer);

                        // pc_readmissions_gap
                        // Plan All-Cause Readmissions (PCR)
                        $care_gaps_array[] = $this->pcrData($val, $checkGapAsPer);

                        // spc_disease_gap
                        // Statin Therapy for Patients with Cardiovascular Disease: Received Statin Therapy (SPC_STATIN)
                        $care_gaps_array[] = $this->spcStatinData($val, $checkGapAsPer);

                        // ma_cholesterol_gap
                        // Medication Adherence for Cholesterol (Statins): Statins (ADH_STATIN)
                        $care_gaps_array[] = $this->adhStatinData($val, $checkGapAsPer);

                        // mad_medications_gap
                        // Medication Adherence for Diabetes Medications: Diabetes Medications (ADH_DIAB)
                        $care_gaps_array[] = $this->ahdDiabData($val, $checkGapAsPer);

                        // ma_hypertension_gap
                        // Medication Adherence for Hypertension (ACE or ARB): ACE (ADH_ACE)
                        $care_gaps_array[] = $this->ahdAceData($val, $checkGapAsPer);

                        // sup_diabetes_gap
                        // Statin Use in Persons with Diabetes (SUPD)
                        $care_gaps_array[] = $this->supdData($val, $checkGapAsPer);

                        // AWV GAP
                        $care_gaps_array[] = $this->awvData($val, $checkGapAsPer);

                    } elseif ( !empty($provider) &&  $typeId == 1 && $provider ==  "allwell-001" ) {
                        // Allwell Gaps

                        // breast cancer  img 1
                        // breast_cancer_gap
                        $care_gaps_array[] = $this->bscData($val, $checkGapAsPer);

                        // Blood Pressure control  img 2
                        // high_bp_gap
                        $care_gaps_array[] = $this->cbpData($val, $checkGapAsPer);

                        // EYE EXAM img 3
                        // eye_exam_gap
                        $care_gaps_array[] = $this->eyeExamData($val, $checkGapAsPer);

                        // Diabetes Care img 4
                        // hba1c_gap
                        $care_gaps_array[] = $this->hba1cData($val, $checkGapAsPer);

                        // ADULTS PAIN SCREENING img 5
                        // pain_screening_gap
                        $care_gaps_array[] = $this->adultsPainData($val, $checkGapAsPer);
                        
                        // COA - Care for Older Adults - Review img 6
                        // adults_medic_gap
                        $care_gaps_array[] = $this->adultsMedicationData($val, $checkGapAsPer);
                        
                       
                        // Colorectal Cancer img 7
                        // colorectal_cancer_gap
                        $care_gaps_array[] = $this->colData($val, $checkGapAsPer);
                        
                        //FMC - F/U ED Multiple High Risk Chronic Conditions img 8 new
                        // m_high_risk_cc_gap
                        $care_gaps_array[] = $this->highRiskData($val, $checkGapAsPer);
                        
                        // Med Adherence - Diabetic img 9 new
                        // med_adherence_diabetic_gap
                        $care_gaps_array[] = $this->adherenceDiabeticData($val, $checkGapAsPer);

                        // Med Adherence - RAS img 10 new
                        // med_adherence_ras_gap
                        $care_gaps_array[] = $this->adherenceRASData($val, $checkGapAsPer);

                        // Med Adherence - Statins img 11
                        // med_adherence_statins_gap
                        $care_gaps_array[] = $this->adherenceStatinsData($val, $checkGapAsPer);

                        // SPC - Statin Therapy for Patients with CVD img 12
                        // spc_statin_therapy_cvd_gap',
                        $care_gaps_array[] = $this->sPCCVDData($val, $checkGapAsPer);

                        // sup_diabetes_gap img 13
                        // Statin Use in Persons with Diabetes (SUPD)
                        $care_gaps_array[] = $this->supdData($val, $checkGapAsPer);

                        //TRC - Engagement After Discharge img 14
                        // trc_eng_after_disc_gap
                        $care_gaps_array[] = $this->tRCAfterDischargeData($val, $checkGapAsPer);

                        //TRC - Med Reconciliation Post Discharge img 15
                        // trc_mr_post_disc_gap
                        $care_gaps_array[] = $this->tRCPostDischarge($val, $checkGapAsPer);

                        //KED - Kidney Health for Patients With Diabetes Current img 16
                        // kidney_health_diabetes_gap
                        $care_gaps_array[] = $this->kHDabetesCurrentData($val, $checkGapAsPer);

                        // AWV GAP ig 17
                        $care_gaps_array[] = $this->awvData($val, $checkGapAsPer);

                    }
                    elseif ( !empty($provider) &&  $typeId == 3 && $provider ==  "hcarz-001" ) {
                        // Allwell Gaps

                        // breast cancer  img 1
                        // breast_cancer_gap
                        $care_gaps_array[] = $this->bscData($val, $checkGapAsPer);

                        // CCS - Cervical Cancer Screening
                        // cervical_cancer_gap
                        $care_gaps_array[] = $this->cervicalCancerData($val, $checkGapAsPer);

                        // HDO - Use of Opioids at High Dosage
                        // opioids_high_dosage_gap
                        $care_gaps_array[] = $this->opioidsHighDosageData($val, $checkGapAsPer);

                        // Diabetes Care img 4
                        // hba1c_gap
                        $care_gaps_array[] = $this->hba1cDataPoor($val, $checkGapAsPer);

                        // PPC1 - Timeliness of Prenatal Care
                        // ppc1_gap
                        $care_gaps_array[] = $this->timelinessPrenatalCare1Data($val, $checkGapAsPer);
                        
                        // PPC2 - Timeliness of Prenatal Care
                        // ppc2_gap
                        $care_gaps_array[] = $this->timelinessPrenatalCare2Data($val, $checkGapAsPer);
                        
                       
                        // WCV - Well-Child Visits for Age 3-21
                        // well_child_visits_gap
                        $care_gaps_array[] = $this->wellChildVisitsData($val, $checkGapAsPer);
                        
                        // Chlamydia Screening
                        // chlamydia_gap
                        $care_gaps_array[] = $this->chlamydiaData($val, $checkGapAsPer);
                        
                        // CBP - Controlling High Blood Pressure
                        // high_bp_gap
                        $care_gaps_array[] = $this->cbpData($val, $checkGapAsPer);

                        // Follow-Up After Hospitalization for Mental Illness (FUH 30-Day)
                        // fuh_30Day_gap
                        $care_gaps_array[] = $this->fuh_30DayData($val, $checkGapAsPer);

                        // Follow-Up After Hospitalization for Mental Illness (FUH 7-Day)
                        // fuh_7Day_gap
                        $care_gaps_array[] = $this->fuh_7DayData($val, $checkGapAsPer);

                        // AWV GAP ig 17
                        $care_gaps_array[] = $this->awvData($val, $checkGapAsPer);

                    }
                    elseif ( !empty($provider) &&  $typeId == 1 && $provider ==  "uhc-001" ) {
                        // United Health Care
                        // breast cancer 1
                        // breast_cancer_gap 
                        $care_gaps_array[] = $this->bscData($val, $checkGapAsPer);
                      
                        // Colorectal Cancer 2
                        // colorectal_cancer_gap 
                        $care_gaps_array[] = $this->colData($val, $checkGapAsPer);
                        
                        //adultsMedicationData 3
                        //C06-Care for Older Adults - Medication Review
                        // adults_medic_gap
                        $care_gaps_array[] = $this->adultsMedicationData($val, $checkGapAsPer); 

                        //AdultsFunStatus 4
                        //DMC10-Care for Older Adults - Functional Status Assessment .
                        // adults_fun_status_gap
                        $care_gaps_array[] = $this->AdultsFunStatus($val, $checkGapAsPer); 

                        //C07-Care for Older Adults - Pain Assessment
                        //pain_screening_gap 5
                        $care_gaps_array[] = $this->adultsPainData($val, $checkGapAsPer);

                        // EYE EXAM
                        // eye_exam_gap 6
                        $care_gaps_array[] = $this->eyeExamData($val, $checkGapAsPer);
                        
                        //C10-Kidney Health Evaluation for Patients With Diabetes
                        //kidney_health_diabetes_gap 7
                        $care_gaps_array[] = $this->kHDabetesCurrentData($val, $checkGapAsPer);

                        // Diabetes Care
                        // hba1c_gap 8
                        $care_gaps_array[] = $this->hba1cData($val, $checkGapAsPer);
                        
                        // Blood Pressure control
                        // high_bp_gap 9
                        $care_gaps_array[] = $this->cbpData($val, $checkGapAsPer);
                        
                        //C16 - Statin Therapy for Patients with Cardiovascular Disease
                        // statin_therapy_gap 10
                        $care_gaps_array[] = $this->statinTherapyData($val, $checkGapAsPer);

                        //D08-Med Ad. For Diabetes Meds Current Year Status
                        // med_adherence_diabetes_gap 11
                        $care_gaps_array[] = $this->MedAadherenceDiabetes($val, $checkGapAsPer);

                        // D09-Med Ad. (RAS antagonists) Current Year Statu 12
                        // med_adherence_ras_gap
                        $care_gaps_array[] = $this->adherenceRASData($val, $checkGapAsPer);

                        // D10-Med Ad. (Statins) Current Year Status
                        // med_adherence_statins_gap 13
                        $care_gaps_array[] = $this->adherenceStatinsData($val, $checkGapAsPer);
                        
                        //D11-MTM CMR Current Year Status
                        //mtm_cmr_gap 14
                        $care_gaps_array[] = $this->MTM_CMR($val, $checkGapAsPer);
                        
                        //D12-Statin Use in Persons with Diabetes Current Year Status 15
                        //sup_diabetes_gap
                        $care_gaps_array[] = $this->supdData($val, $checkGapAsPer);

                        // AWV GAP 16
                        // awv_gap
                        $care_gaps_array[] = $this->awvData($val, $checkGapAsPer);
                    }
                }
                
                if(empty($care_gaps_array)) {
                    $status = $val['status'];
                    if(!empty($status) && $status == 1) {
                        $insuranceID = $val['insurance_id'];
                        $insurancePrvider = (new DashboardController)->insuranceNameFind($insuranceID); 
                            
                        $care_gaps_array = [];           
                        if (!empty($insuranceID) &&  !empty($insurancePrvider) &&  $insurancePrvider->type_id == 1 && $insurancePrvider->provider ==  "hcpw-001" ) { 
                            $care_gaps_array =  $this->hPCEmptyGaps($val);
                        }elseif(!empty($insuranceID) && !empty($insurancePrvider) && $insurancePrvider->type_id == 1  && $insurancePrvider->provider ==  "hum-001" ) { 
                            $care_gaps_array =  $this->humanaEmptyGaps($val);
                        }elseif(!empty($insuranceID) && !empty($insurancePrvider) && $insurancePrvider->type_id == 1  && $insurancePrvider->provider ==  "med-arz-001" ) { 
                            $care_gaps_array =  $this->MedicareArizonaEmptyGaps($val);
                        }elseif(!empty($insuranceID) && !empty($insurancePrvider) && $insurancePrvider->type_id == 1  && $insurancePrvider->provider ==  "aet-001" ) { 
                            $care_gaps_array =  $this->AetnaMedicareEmptyGaps($val);
                        }elseif(!empty($insuranceID) && !empty($insurancePrvider) && $insurancePrvider->type_id == 1  && $insurancePrvider->provider ==  "allwell-001" ) { 
                            $care_gaps_array =  $this->AllwellMedicareEmptyGaps($val);
                        }elseif(!empty($insuranceID) && !empty($insurancePrvider) && $insurancePrvider->type_id == 1  && $insurancePrvider->provider ==  "hcarz-001" ) { 
                            $care_gaps_array =  $this->HealthChoiceArizonaEmptyGaps($val);
                        }elseif(!empty($insuranceID) && !empty($insurancePrvider) && $insurancePrvider->type_id == 1  && $insurancePrvider->provider ==  "uhc-001" ) { 
                            $care_gaps_array =  $this->UnitedHealthCareEmptyGaps($val);
                        }
                    }
                }

                // Fetch patients_history for each patient
                $patientHistory = $val['patients_history'] ?? NULL;
            
                // Do something with $patientHistory
                // For example, you can iterate over each history record
                foreach ($patientHistory as $key => $data) {
                    // Extract the differences string
                    $differencesStr = $data['differences'];
                    // Decode the JSON string into an associative array
                    $differences = json_decode($differencesStr, true);
                    // Access the group attribute
                    $abcClinic_id = @$differences['clinic_id'];
                    $abcDoctor_id = @$differences['doctor_id'];
                    $abcInsurance_id = @$differences['insurance_id'];
                    $abcGroup = @$differences['group'];
                    $abcStatus = @$differences['status'];
                    $abcCoordinator_id = @$differences['coordinator_id'];

                    if(isset($abcClinic_id)){
                        $a=  Clinic::where('id',$abcClinic_id)->first();
                        $patientHistory[$key]['clinicName'] = @$a->name;
                    }

                    if(isset($abcDoctor_id)){
                        $d = User::where('id',$abcDoctor_id)->first();
                        $patientHistory[$key]['doctorName'] =  @$d->first_name.' '.@$d->mid_name.' '.@$d->last_name;
                    }

                    if(isset($abcInsurance_id)){
                        $b = Insurances::where('id',$abcInsurance_id)->first();
                        $patientHistory[$key]['insuranceName'] = @$b->name;
                    }

                    if(isset($abcGroup)){
                        if($abcGroup == 1){ $patientHistory[$key]['group'] = "Group A1";}
                        elseif($abcGroup == 2){ $patientHistory[$key]['group'] = "Group A2";}
                        elseif($abcGroup == 3){ $patientHistory[$key]['group'] = "Group B";}
                        elseif($abcGroup == 4){ $patientHistory[$key]['group'] = "Group C";}
                    // else{ $patientHistory[$key]['Group'] = "Unknown Group";}
                    }

                    if(isset($abcStatus)){
                        if($abcStatus == 1){ $patientHistory[$key]['status'] = "Assigned";}
                        elseif($abcStatus == 2){ $patientHistory[$key]['status'] = "Assignable";}
                    // else{ $patientHistory[$key]['Status'] = "Unknown";}
                    }

                    if(isset($abcCoordinator_id)){
                        $u = User::where('id',$abcCoordinator_id)->first();
                        $patientHistory[$key]['coordinator'] = @$u->first_name.' '.@$u->mid_name.' '.@$u->last_name;
                    }
                    
                    
                }


                $list[] = [
                    'id' => $val['id'],
                    'identity' => $val['identity'],
                    'member_id' => @$val['member_id'],
                    'first_name' => $val['first_name'],
                    'mid_name' => $val['mid_name'],
                    'last_name' => $val['last_name'],
                    'name' => $val['name'],
                    'contact_no' => $val['contact_no'],
                    'doctor_id' => @$val['doctor_id'],
                    'coordinator_id' => @$val['coordinator_id'],
                    'clinic_id' => @$val['clinic_id'],
                    'doctor_name' =>  @$val['doctor']['name'],
                    'coordinator_name' =>  @$val['coordinator']['name'],
                    'insurance_id' => @$val['insurance_id'],
                    'insurance_name' =>  @$val['insurance']['name'],
                    'insurance_provider' =>  @$val['insurance']['provider'],
                    'age' => $val['age'],
                    'gender' => $val['gender'],
                    'address' => $val['address'],
                    'address_2' => $val['address_2'],
                    'dob' => $val['dob'],
                    'cell' => $val['cell'],
                    'email' => $val['email'],
                    'city' => $val['city'],
                    'state' => $val['state'],
                    'zipCode' => $val['zipCode'],
                    'preferred_contact' => @$val['preferred_contact'],
                    'covered_amount' => @$val['covered_amount'] ?? "",
                    'patient_consent' => $val['patient_consent'] === 1 ? true : false,
                    'consent_data' => json_decode($val['consent_data'], true),
                    'group' => $val['group'] ?? "",
                    'status' => $val['status'] ?? "",
                    'diagnosis' => $val['diagnosis'],
                    'medication' => $val['medication'],
                    'surgical_history' => $val['surgical_history'],
                    'patients_history' => $patientHistory,
                    'family_history' => json_decode($val['family_history'],true),
                    'social_history' => json_decode($val['social_history'],true),  
                    
                    'insurance_history' => @$val['insurance_histories'],
                    'gaps_as_per' => $gaps_as_per,
                                      
                    'care_gaps' =>  @$val['care_gaps_data'] ?? [],
                    'care_gap_status' => $care_gap_status,
                    'awvGap' => $awvStatus,
                    'care_gaps_array' => @$care_gaps_array ?? [],//@$val['care_gaps_data'],
                ];
            }



            $insurance_data = $insurances = Insurances::all();
            $insuranceList = [];
            foreach ($insurances as $key => $value) {
                $insuranceList[$value->id] = $value->name;
            }

            $doctorsData = User::where("role" ,21)->orWhere("role", 13)->get()->toArray();
            $doctorList = [];
            foreach ($doctorsData as $key => $value) {
                $doctorList[] = ['id' => $value['id'], 'name'=> $value['name']];
            }

            $response = [
                'success' => true,
                'message' => 'Patients Data Retrived Successfully',
                'current_page' => @$current_page ?? '',
                'total_records' => @$total ?? '',
                'per_page'   => $this->per_page,
                'insurances' => (object) $insuranceList,
                'insurance_data' => $insurance_data,
                'doctors' => $doctorList,
                'data' => $list,
                'clinic_list' => $clinicList,
                'programs_list' => @$programs??[],
                'coordinators' => @$coordinators??[],
                'bulk_assign' => $bulk_Assign,
            ];
        } catch (\Exception $e) {
            $response = array('success' => false, 'message' => $e->getMessage(), 'line'=>$e->getLine());
        }

        return response()->json($response);
    }


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'last_name'   => 'required',
            'first_name'  => 'required',
            'contact_no'  => 'required',
            'dob'         => 'required',
            'age'         => 'required',
            'doctor_id'   => 'required',
            'gender'      => 'required|string',
            'disease'    => 'sometimes|required',
            'address'     => 'sometimes|required',
            'insurance_id' => 'sometimes|required',
            'city'     => 'sometimes|required',
            'state'     => 'sometimes|required',
            'zipCode'     => 'sometimes|required',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->getMessageBag()]);
        };

        $input = $validator->valid();
        try {

            if (!empty($input['dob'])) {
                $input['dob'] = date('Y-m-d', strtotime($input['dob']));
            }

            if (!empty($input['dod'])) {
                $input['dod'] = date('Y-m-d', strtotime($input['dod']));
            }
            
            $patient = Patients::orderBy('id', 'desc')->first();
            
            if (!empty($patient)) {
                $str = $patient->identity ?? "00000000";
                $a = +$str;
                $a = str_pad($a + 1, 8, '0', STR_PAD_LEFT);
                $input['identity'] = $a;
            } else {
                $input['identity'] = "00000001";
            }

            /* Patient Family History */
            $family_history = $input['family_history'] ?? [];
            
            $input['family_history'] = json_encode($family_history);
            $input['clinic_id'] = $request->input('clinic_id');
            $input['change_address'] = $request->input('address');
            $input['change_doctor_id'] = $request->input('doctor_id');

            /* Using Utility to add data in clinic User functions */
            $input = Utility::appendRoles($input);

            $patient_dob = str_replace('/', '', Carbon::parse($input['dob'])->format('m/d/Y'));
            $unique_id = strtoupper($input['last_name']).strtoupper($input['first_name']).$patient_dob;

            $input['unique_id'] = $unique_id;            

            /* Gettin id after patient create */
            //Patients::create($input)->id;
            $exPatient = Patients::create($input);

            // $CareGapDone = $this->createCareGap($exPatient);
            // if($CareGapDone != "Successfull"){
            //     $response = [
            //         'success' => false,
            //         'message' => 'Some issue Found'
            //     ];
            // }
           
            /*Rizwan Start add for show data*/
            $active = $request->input("active") ?? 1;
            $list = Patients::with('insurance', 'doctor');

            if ($active == 2) {
                $list = $list->onlyTrashed();
            }

            $list = $list->orderBy('id', 'DESC')->get()->toArray();

            $response = [
                'success' => true,
                'message' => 'Add New Patients Successfully',
                'data' => $list
            ];
            /*end rizwan end add for show data */
        } catch (\Exception $e) {
           $response = array('success' => false, 'message' => $e->getMessage(), 'line'=>$e->getLine());
        }
        return response()->json($response);
    }
    
    
    // Edit existing Patient
    public function edit($id)
    {
        try {
            $note = Patients::with('diagnosis')->where('id', $id)->get();
            $note['insurances'] = Insurances::all();

            if ($note) {
                $response = [
                    'success' => true,
                    'message' => 'Sorry Patient not found',
                    'data' => $note
                ];
            }

            $response = [
                'success' => true,
                'message' => 'Patient data Successfully',
                'data' => $note
            ];



        } catch (\Exception $e) {
            $response = array('success'=>false,'message'=>$e->getMessage(), 'line'=>$e->getLine());
        }

        return response()->json($response);
    }


    /* Update patient Data */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'last_name'  => 'required',
            'first_name' => 'required',
            'contact_no'  => 'required:patients,contact_no,' . $id,
            'dob'        => 'required',
            'age'        => 'required',
            'doctor_id'  => 'required',
            'insurance_id'   => 'required',
            'gender'     => 'required|string',
            'disease'    => 'sometimes|required',
            'address'    => 'sometimes|required',
            'city'       => 'sometimes|required',
            'state'      => 'sometimes|required',
            'zipCode'    => 'sometimes|required',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->getMessageBag()]);
        };

        $input = $validator->valid();

        if ($request->has('coordinator_id')) {
            $input['coordinator_id'] = $request->coordinator_id;
        }
        
        $existingData = $note = Patients::with('insurance')->find($id);
        
        /* Check insurance ID */
        $exInsuranceId = $existingData->insurance_id;
        $newInsuranceId = $request->insurance_id;

        // Check Doctor ID
        $exDoctorId = $existingData->doctor_id;
        $newDoctorId = $request->doctor_id;
        try {
            $input['dob'] = date('Y-m-d', strtotime($input['dob']));

            $created_user =  Auth::check() ? Auth::id() : 1;
            
            $input = Utility::appendRoles($input);

            /* Patient Family History */
            $family_history = $input['family_history'] ?? [];
            $input['family_history'] = json_encode($family_history);

            // Creating Gap for updated Insurance of existing patient.
            if($exInsuranceId != $newInsuranceId) {
                $CareGapDone = $this->createCareGap($existingData, $newInsuranceId, "");
            } else if ($exDoctorId != $newDoctorId) {
                $CareGapDone = $this->createCareGap($existingData, "", $newDoctorId);
            }
            $previousPatientData = Patients::find($id);
            $source = "Patient Profile";
            $note->update($input);
            $patientsResult1 = (new CommonFunctionController)->patientsChangesHistoryCreate($previousPatientData, $id, $source, $fileLogId);
            
            // $currentPatientData = Patients::find($id);
            // $logHistory['patient_id'] = $id;
            // $logHistory['previous'] = json_encode($previousPatientData);
            // $logHistory['current'] = json_encode($currentPatientData);

            // // Convert JSON strings to associative arrays
            // $array1 = json_decode($logHistory['previous'], true);
            // $array2 = json_decode($logHistory['current'], true);
            // // Initialize an array to store differences
            // $differences = [];

            // // Iterate through each key in the first JSON object
            // foreach ($array1 as $key => $value1) {
            //     // Check if the key exists in the second JSON object
            //     if (isset($array2[$key])) {
            //         // Compare the values
            //         if ($array2[$key] !== $value1) {
            //             // Check if the key is 'updated_at', if so, skip it
            //             if ($key !== 'updated_at') {
            //                 $differences[$key] = $array2[$key];
            //             }
            //             // If values are different, store the previous and new values
            //             //$differences[$key] = ['previous' => $value1, 'new' => $array2[$key]];
            //             //$differences[$key] = ['new' => $array2[$key]];
                      
            //         }
            //     } 
            // }

            // // Print differences
            // $logHistory['differences'] = json_encode($differences);
            // $logHistory['created_user'] = $created_user;
            // if($logHistory['differences'] !== '[]'){
            //     PatientsChangesHistoryModel::create($logHistory);
            // }
            $clinicId = [
                'clinic_id' => $note->clinic_id,
            ];

            Questionaires::where('patient_id', $note->id)->update($clinicId);
            
            $response = [
                'success' => true,
                'message' => 'Update Patient Record Successfully',
                'data' => $note
            ];
        } catch (\Exception $e) {
            $response = array('success' => false, 'message' => $e->getMessage(), 'line'=>$e->getLine());
        }

        return response()->json($response);
    }


    public function updatePatientGroup(Request $request) {
        try {
            $patientId = $request->patient_id;
            $patientGroup = $request->group;

            $update = ['group' => $patientGroup];

            Patients::where('id', $patientId)->update($update);

            $response = [
                'success' => true,
                'message' => 'Group Updated Successfully'
            ];

        } catch (\Exception $e) {
            $response = array('success'=>false,'message'=>$e->getMessage(), 'line' => $e->getLine()); 
        }
        return response()->json($response);
    }
    

    public function bulkAssign(Request $request)
    {
        try {

            $validator = Validator::make($request->all(),[
                'coordinator_id'  => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'errors' => $validator->getMessageBag()]);
            };

            $input = $request->all();

            $patient_ids = $input['patient_ids'];
            $coordinator_id = $input['coordinator_id'];

            Patients::whereIn('id', $patient_ids)->update(['coordinator_id'=> $coordinator_id]);

            $response = [
                'success' => true,
                'message' => 'Update Patient Record Successfully',
            ];
        } catch (\Exception $e) {
            $response = array('success'=>false,'message'=>$e->getMessage(), 'line'=>$e->getLine()); 
        }

        return response()->json($response);
    }


    /* Setting patient InActive */
    public function destroy($id)
    {
        try {
            $note = Patients::find($id);
            $note->delete();

            Questionaires::where('patient_id', $id)->delete();
            
            $response = [
                'success' => true,
                'message' => 'Patients Deleted Successfully',
                'data' => $note
            ];

        } catch (\Exception $e) {
            $response = array('success' => false, 'message' => $e->getMessage());
        }
        return response()->json($response);
    }


    /* Add New Diagnosis from patient profile*/
    public function addDiagnosis(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'condition'   => 'required',
            'description'   => 'required',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->getMessageBag()]);
        };

        $input = $request->all();
        try {
            $patient_id = $input['patient_id'];
            $insertData = [
                'patient_id' => $patient_id,
                'created_user' => Auth::check()?Auth::id():1,
                'condition' => $input['condition'],
                'description' => $input['description'],
                'status' => $input['status'],
                'display' => $input['display'],
            ];

            $insert = Diagnosis::create($insertData);

            $patient_diagnosis = Diagnosis::where('patient_id', $patient_id)->get();

            $response = [
                'success' => true,
                'message' => 'Diagnosis Added Successfully',
                'data' => $patient_diagnosis
            ];
        } catch (\Exception $e) {
            $response = array('success' => false, 'message' => $e->getMessage());
        }
        return response()->json($response);
    }


    /* Update existing diagnosis
    ** get $id as param
    ** only to update status */
    public function updateDiagnosis(Request $request, $id)
    {
        $input = $request->all();

        try {

            if (isset($input['condition'])) {
                unset($input['condition']);
            }
            $patientId = $input['patient_id'];

            Diagnosis::where('id', $id)->update($input);

            $allDiagnosis = Diagnosis::where('patient_id', $patientId)->get()->toArray();

            $response = [
                'success' => true,
                'message' => 'Diagnosis Updated Successfully',
                'data' => $allDiagnosis,
            ];
        } catch (\Exception $e) {
            $response = array('success' => false, 'message' => $e->getMessage());
        }

        return response()->json($response);

    }
    

    /* Add new Medication from patient profile */
    public function addMedications(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'   => 'required',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->getMessageBag()]);
        };

        $input = $request->all();
        try {
            $patient_id = $input['patient_id'];
            $insertData = [
                'patient_id' => $patient_id,
                'created_user' => Auth::check()?Auth::id():1,
                'name' => $input['name'],
                'dose' => @$input['dose'] ?? "",
                'status' => @$input['status'] ?? "",
                'condition' => @$input['condition'] ?? "",
                'created_at' => Carbon::now()
            ];

            $insert = Medications::create($insertData);
            $patient_medications = Medications::where('patient_id', $patient_id)->get();

            $response = [
                'success' => true,
                'message' => 'Medication Added Successfully',
                'data' => $patient_medications
            ];
        } catch (\Exception $e) {
            $response = array('success' => false, 'message' => $e->getMessage());
        }
        return response()->json($response);
    }


    /* Update existing Medication
    ** get $id as param
    ** only to update Dosage and status */
    public function updateMedication(Request $request, $id)
    {
        $input = $request->all();
        try {

            if (isset($input['name'])) {
                unset($input['name']);
            }

            Medications::where('id', $id)->update($input);

            $patientId = $input['patient_id'];
            $allMedications = Medications::where('patient_id', $patientId)->get()->toArray();

            $response = [
                'success' => true,
                'message' => 'Medication Updated Successfully',
                'data' => $allMedications,
            ];
        } catch (\Exception $e) {
            $response = array('success' => false, 'message' => $e->getMessage());
        }

        return response()->json($response);

    }


    /* Add New surgery from patient profile */
    public function addSurgeries(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'procedure'   => 'required',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->getMessageBag()]);
        };

        $input = $request->all();

        try {
            $patient_id = $input['patient_id'];
            $surgery_date = @$input['date'] ? date('Y-m-d', strtotime($input['date'])) : Null;
            $insertData = [
                'patient_id' => $patient_id,
                'created_user' => Auth::check()?Auth::id():1,
                'procedure' => $input['procedure'],
                'reason' => @$input['reason'] ?? "",
                'date' => $surgery_date,
                'surgeon' => @$input['surgeon'] ?? "",
                'created_at' => Carbon::now()
            ];

            $insert = SurgicalHistory::create($insertData);

            $patient_diagnosis = SurgicalHistory::where('patient_id', $patient_id)->get();

            $response = [
                'success' => true,
                'message' => 'Surgery Added Successfully',
                'data' => $patient_diagnosis
            ];
        } catch (\Exception $e) {
            $response = array('success' => false, 'message' => $e->getMessage());
        }
        return response()->json($response);
    }


    /* Update existing Surgery
    ** get $id as param
    ** only to update Reason, Facility/Surgeon and Surgury Date */
    public function updateSurgery(Request $request, $id)
    {
        $input = $request->all();
        try {

            if (isset($input['procedure'])) {
                unset($input['procedure']);
            }

            SurgicalHistory::where('id', $id)->update($input);

            $patientId = $input['patient_id'];
            $allSurgeries = SurgicalHistory::where('patient_id', $patientId)->get()->toArray();

            $response = [
                'success' => true,
                'message' => 'Medication Updated Successfully',
                'data' => $allSurgeries,
            ];
        } catch (\Exception $e) {
            $response = array('success' => false, 'message' => $e->getMessage());
        }

        return response()->json($response);

    }


    /**
     * update Family history in patient table
     * @param int $id as patient id
     * @return \Illuminate\Http\Response
     */
    public function updateFamilyHistory(Request $request, $id)
    {
        try {
            $input = $request->all();
            $family_history = json_encode($input['family_history']);

            $update_column = [
                'family_history' => $family_history,
            ];

            Patients::where('id', $id)->update($update_column);

            $response = array(
                'success'=> true,
                'message'=> 'Family History Update Successfully',
                'data'=> json_decode($family_history, true),
            );
            
        } catch (\Exception $e) {
            $response = array(
                'status' => 'Failed',
                'success' => false,
                'Error' => $e->getMessage(),
                'Line' => $e->getLine(),
            );
        }

        return response()->json($response);
    }


    /**
     * Add social history to Patient's Table
     * @param int $id as patient id
     * @return \Illuminate\Http\Response
     */
    public function updateSocialHistory(Request $request, $id)
    {
        try {
            $input = $request->all();
            $social_history = json_encode($input['social_history']);

            $update_column = [
                'social_history' => $social_history,
            ];

            Patients::where('id', $id)->update($update_column);

            $response = array(
                'success'=> true,
                'message'=> 'Social History updates successfully',
                'data'=> json_decode($social_history, true),
            );
        } catch (\Exception $e) {
            $response = array(
                'status'=> 'Failed',
                'success'=> false,
                'Error'=> $e->getMessage(),
                'Line'=> $e->getLine(),
            );
        }
        
        return response()->json($response);
    }


    /* Get Patient Enounters */
    public function getEncounters($id)
    {
        try {
            $encounters = Questionaires::with('allMonthlyAssessment')->where('patient_id', $id)->get()->toArray();
            
            $encounterList = [];

            if ($encounters) {

                foreach ($encounters as $key => $value) {

                    if ($value['date_of_service'] != "") {
                        $encounterList[] = $value;
                    }

                    if (!empty($value['all_monthly_assessment'])) {
                        $monthly_encounter = $value['all_monthly_assessment'];
                        foreach ($monthly_encounter as $monthlykey => $monthlyvalue) {
                            $monthlyvalue['parent_id'] = $value['id'];
                            $encounterList[] = $monthlyvalue;
                        }
                    }
                }


                $response = [
                    'success' => true,
                    'message' => 'Encounters Found',
                    'data' => $encounterList
                ];
            } else {
                $response = [
                    'success' => true,
                    'message' => 'No Encounters Found',
                    'data' => $encounterList
                ];
            }
        } catch (\Exception $e) {
            $response = array('success' => false, 'message' => $e->getMessage());
        }

        return response()->json($response);
    }


    /* Get Insureance and Doctors against Clinins  */
    public function getInsuracneandPcp($clinicId)
    {
        $doctorList = User::OfClinicID($clinicId)->where('role', '21')->orWhere('role', '13')->get()->toArray();

        $pcp = [];
        foreach ($doctorList as $key => $value) {
            $pcp[] = ['id' => $value['id'], 'name'=> $value['name']];
        }

        $clinicId = explode(",", $clinicId);
        $insurancesList = Insurances::whereIn('clinic_id', $clinicId)->select('id', 'name' , 'type_id', 'provider')->get()->toArray();

        $response = [
            'insurances' => $insurancesList,
            'doctors' => $pcp,
        ];

        return response()->json($response);

    }


    /**
     * Store Pateint Consent Information
     * Column patient _consent Boolean
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function storePatientConsent(Request $request, $id)
    {
        try {
            $consent_given = $request->consent_given;
            $on_date = $request->on_date;
            $coordinator = $request->coordinator;

            $consent_data = [
                'consent_given' => $consent_given,
                'on_date' => $on_date,
                'coordinator' => $coordinator,
            ];

            $update = [
                'patient_consent' => @$consent_given == "Yes" ? true : false,
                'consent_data' => @$consent_data ? json_encode($consent_data) : null,
            ];

            $update = Patients::where('id', $id)->update($update);

            $response = [
                'success' => true,
                'message' => 'Consent Stored',
                'data' => $consent_data,
            ];

            return response()->json($response);

        } catch (\Exception $e) {
            $response = array('success' => false, 'message' => $e->getMessage(), 'line' => getLine());
        }
    }


    public function storeBulkPatients(Request $request, $fromCaregaps = "", $filterYear = "")
    {
       // return $request;
        try {
            $validator = Validator::make($request->all(),
                [
                    'clinicIds' => 'required',
                    'insuranceIds' => 'required',
                    'data'  => 'required',
                ],
                [
                    'clinicIds.required' => 'Please select a Clinic.',
                    'insuranceIds.required' => 'Please select an Insurance.',
                    'data.required' => 'No data found to add in patients',
                ]
            );

            
            if($validator->fails()) {
                $error = $validator->getMessageBag()->first();
                return response()->json(['success'=>false,'errors'=>$error]);
            };
            
            $clinic_id = $request->clinicIds;
            $insurance_id = @$request->insuranceIds ?? "";
            $data = json_decode(json_encode(array_filter($request->data)) ,true);

            // Updating status for existing patient not in new Updated population
            $currentYear = Carbon::now()->year;
            if ($fromCaregaps == 1 && ($filterYear == $currentYear)) {
                $check = $this->storeStatusLogs($insurance_id, $data);
            }


            $patientYear = @$request->gap_year ?? NULL;

            $doctorData = User::where('role', '21')->orWhere('role', '13')->whereRaw("FIND_IN_SET(?, clinic_id) > 0", [$clinic_id])->get()->toArray();
            $insurancesData = Insurances::where('type_id', '1')->where('clinic_id', $clinic_id)->get()->toArray();

            $allPatients = Patients::where('insurance_id', $insurance_id)->get()->toArray();
            // $allPatients = Patients::get()->toArray();
            // $lastPatient = end($allPatients);

            $lastPatients_ = Patients::get()->toArray();
            $lastPatient = end($lastPatients_);
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

            $bulktoStore = $dbcolumns = $duplicate_patientList = $insuranceFound = [];
            $duplicate_patient_IDs = [];

            foreach ($data as $key => $value) {
                                  
                if ($fromCaregaps == 1) {
                    $value['insurance_id'] = @$request->insuranceIds ?? '';
                    $value['address'] = @$value['address'] ?? '';

                }

                $memberId = @$value['member_id'] ?? NULL;

                // Getting doctor Id from table to save in patient table
                $doctor = [];
                if (!empty($value['doctor_id']) && $value['doctor_id'] != "-") {
                    $doctorValue['doctor_id'] = str_replace(', ', ' ', $value['doctor_id']);
                    $doctor_name = explode(' ', $doctorValue['doctor_id']);
              
                    $lastName = strtoupper($doctor_name[0]);
                    $firstName = strtoupper($doctor_name[1]);
              
                    $doctor = array_filter($doctorData, function($item) use ($lastName, $firstName) {
                        $dblast_name = trim(strtoupper($item['last_name']));
                        $dbfirst_name = trim(strtoupper($item['first_name']));

                        return( ($dblast_name == trim($lastName) && $dbfirst_name == trim($firstName)) || ($dblast_name == trim($firstName) && $dbfirst_name == trim($lastName))  );
                    });

                    $doctor = reset($doctor);
                }

                if (!empty($value['insurance_id']) && $fromCaregaps != 1) {
                    $insuranceName = str_replace(' ','', $value['insurance_id']);
                    $insurance = array_filter($insurancesData, function($item) use ($insuranceName) {
                        return( strtoupper(str_replace(' ', '', $item['name'])) == strtoupper($insuranceName) || strpos(strtoupper($item['name']), strtoupper($insuranceName)) !== false );
                    });
                    $insurance = reset($insurance);
                    $insuranceFound[] = $insurance;

                    $value['insurance_id'] = isset($insurance['id']) && $insurance_id == $insurance['id'] ? $insurance_id : "";
                }

                if ($newidentity) {
                    $str = $newidentity;
                    $a = +$str;
                    $a = str_pad($a + 1, 8, '0', STR_PAD_LEFT);
                    $identity = $a;
                }

                // Assign STATUS
                $status = !empty($value['status']) ? $value['status'] : NULL;
                if (strtoupper($status) == 'ASSIGNED') {
                    $value['status'] = '1';
                } elseif (strtoupper($status) == 'ASSIGNABLE') {
                    $value['status'] = '2';
                }

                // Group STATUS
                $string = @$value['groups'] ?? "";
                if (!empty($string) && str_contains($string, 'A1')) {
                    $value['groups'] = "1";
                } elseif (!empty($string) && str_contains($string, 'A2')) {
                    $value['groups'] = "2";
                } elseif (!empty($string) && str_contains($string, 'B')) {
                    $value['groups'] = "3";
                } else if (!empty($string) && str_contains($string, 'C')) {
                    $value['groups'] = "4";
                } else {
                    $value['groups'] = NULL;
                }


                // Logic to handle the patient with middle name attached with lastname
                if (strpos($value['last_name'], ' ') !== false) {
                    $patient_last_name = explode(' ', $value['last_name']);
                    if (count($patient_last_name) > 1) {
                        array_pop($patient_last_name);
                    }
                    
                    $value['last_name'] = implode(' ', $patient_last_name);
                }
                $dob_ = date("m-d-Y", strtotime($value['dob']));
                $unique_id = $value['last_name'].$value['first_name']. str_replace(['/', '-'], '', $dob_);
              
                $exiting_patient_id = "";
                $duplicate_patient = array_filter($allPatients, function ($item) use ($unique_id, $value) {
                    $exiting_patient_dob = str_replace('/', '', Carbon::parse($item['dob'])->format('m/d/Y'));
                    $exiting_patient_ln = explode(' ', $item['last_name']);

                    if (count($exiting_patient_ln) > 1) {
                        array_pop($exiting_patient_ln);
                    }
                    $exiting_patient_ln = implode(' ', $exiting_patient_ln);

                    $exiting_patient_id = strtoupper($exiting_patient_ln).strtoupper($item['first_name']).$exiting_patient_dob;
                    return ($item['unique_id'] === $unique_id || $unique_id === $exiting_patient_id);
                });
                
                
                $duplicate_patient = reset($duplicate_patient);
               
                if (!empty($duplicate_patient)) {

                    $update_columns = [];

                    if ($fromCaregaps == 1) {
                        $update_columns = [
                            'member_id'     => $memberId,
                            'insurance_id'  => @$value['insurance_id'] ?? NULL,
                            'status'        => @$value['status'] ?? NULL,
                            'group'         => @$value['groups'] ?? NULL,
                            'doctor_id'     => @$doctor['id'] ?? NULL,
                            'patient_year'  => $patientYear
                        ];    
                    } else {
                        if (empty($duplicate_patient['member_id'])) {
                            $update_columns['member_id'] = @$memberId ?? NULL;
                        }
                        
                        if (empty($duplicate_patient['unique_id'])) {
                            $update_columns['unique_id'] = @$memberId ?? NULL;
                        }
                        
                        if (@$duplicate_patient['status'] != 1) {
                            $update_columns['status'] = @$value['status'] ?? NULL;
                        }

                        if (empty($duplicate_patient['group'])) {
                            $update_columns['group'] = @$value['groups'] ?? NULL;
                        }

                        if (empty($duplicate_patient['doctor_id'])) {
                            $update_columns['doctor_id'] = @$doctor['id'] ?? NULL;
                        }
                    }

                    if (!empty($update_columns)) {
                        Patients::where('id', $duplicate_patient['id'])->update($update_columns);
                    }
                   
                    $duplicate_patient_IDs[] = $duplicate_patient['id'];
                }

                $duplicate_patientList[] = $duplicate_patient;

                // Break current itteration of loop if patient exist
                if ($duplicate_patient == true || empty($value['insurance_id'])) {
                    continue;
                }

                if (!empty($value['dob']) && empty($value['age'])) {
                    $dateOfBirth = Carbon::parse($value['dob'])->format('Y-m-d');
                    $value['age'] = Carbon::parse($dateOfBirth)->age;
                }
                
                $dbcolumns["unique_id"] = $unique_id;
                $dbcolumns["identity"] = @$identity?? NULL;
                $dbcolumns["member_id"] = $memberId;
                $dbcolumns["first_name"] = @$value['first_name'] ?? "";
                $dbcolumns["mid_name"] = @$value['mid_name'] ?? "";
                $dbcolumns["last_name"] = @$value['last_name'] ?? "";
                $dbcolumns["gender"] = @$value['gender'] ?? "";
                $dbcolumns["dob"] = date('Y-m-d', strtotime($value['dob']));
                $dbcolumns['age'] = @$value['age'] ?? '';
                $dbcolumns['cell'] = @$value['cell'] ?? '';
                $dbcolumns['contact_no'] = @$value['contact_no'] ?? @$value['cell'] ?? @$value['phone'] ?? '';
                $dbcolumns["address_2"] = @$value['address_2'] ?? "";
                $dbcolumns["address"]   = @$value['address'] ?? "";
                $dbcolumns["change_address"]   = @$value['address'] ?? "";
                $dbcolumns["change_doctor_id"] = @$doctor['id'] ??  NULL;
                $dbcolumns["clinic_id"] = $clinic_id;
                $dbcolumns["created_user"] = Auth::id();
                $dbcolumns["disease"] = "";
                $dbcolumns["doctor_id"] = @$doctor['id'] ?? NULL;
                $dbcolumns["dod"] = NULL;
                $dbcolumns["email"] = "";
                $dbcolumns["insurance_id"] = @$value['insurance_id'] ?? NULL;
                $dbcolumns["city"] =  @$value['city'] ?? "";
                $dbcolumns["state"] = @$value['state'] ?? "";
                $dbcolumns["zipCode"] =  !empty($value['zipCode']) ? str_replace(' ', '', $value['zipCode']) : NULL;
                $dbcolumns["preferred_contact"] =  @$value['preferred_contact'] ?? "";
                $dbcolumns["family_history"] = '[]';
                $dbcolumns["social_history"] = NULL;
                $dbcolumns["patient_consent"] = 0;
                $dbcolumns["consent_data"] = "";
                $dbcolumns["group"] = @$value['groups'] ?? NULL;
                $dbcolumns["status"] = @$value['status'] ?? NULL;
                $dbcolumns["patient_year"] = $patientYear;
                $dbcolumns["created_at"] = Carbon::now();
                $dbcolumns["updated_at"] = Carbon::now();
                $dbcolumns["deleted_at"] = NULL;

                $bulktoStore[] = (array)$dbcolumns;
                $newidentity = $identity;
            }
            //return $bulktoStore;
            if (!empty($bulktoStore)) {
                $res = Patients::insert($bulktoStore);
                $response = array('success'=>true,'message'=>'Patients Addedd successfully', 'total_added' => count($bulktoStore), 'duplicate' =>$duplicate_patientList , 'duplicate_patient_IDs' => $duplicate_patient_IDs);
            } else {
                $response = array('success'=>false,'message'=>'Duplicate patients found', 'duplicate' => $duplicate_patientList, 'duplicate_patient_IDs' => $duplicate_patient_IDs);
            }

        } catch (\Exception $e) {
            $response = array('success'=>false,'message'=>$e->getMessage(), 'line'=>$e->getLine());
        }

        return response()->json($response);
    }


    
    // Pre Processor File
    public function storeBulkPatientsPreProcessFile(Request $request, $fromCaregaps = "", $filterYear = "")
    {
       // return $request;
        try {
            $validator = Validator::make($request->all(),
                [
                    'clinicIds' => 'required',
                    'insuranceIds' => 'required',
                    'data'  => 'required',
                ],
                [
                    'clinicIds.required' => 'Please select a Clinic.',
                    'insuranceIds.required' => 'Please select an Insurance.',
                    'data.required' => 'No data found to add in patients',
                ]
            );

            
            if($validator->fails()) {
                $error = $validator->getMessageBag()->first();
                return response()->json(['success'=>false,'errors'=>$error]);
            };
            
            $clinic_id = $request->clinicIds;
            $insurance_id = @$request->insuranceIds ?? "";
            $data = json_decode(json_encode(array_filter($request->data)) ,true);

            // Updating status for existing patient not in new Updated population
            $currentYear = Carbon::now()->year;
            if ($fromCaregaps == 1 && ($filterYear == $currentYear)) {
                $check = $this->storeStatusLogs($insurance_id, $data);
            }


            $patientYear = @$request->gap_year ?? NULL;

            $doctorData = User::where('role', '21')->orWhere('role', '13')->whereRaw("FIND_IN_SET(?, clinic_id) > 0", [$clinic_id])->get()->toArray();
            $insurancesData = Insurances::where('type_id', '1')->where('clinic_id', $clinic_id)->get()->toArray();

            // //$allPatients = Patients::where('insurance_id', $insurance_id)->get()->toArray();
            // $allPatients = Patients::get()->toArray();
            // $lastPatient = end($allPatients);

            $lastPatients_ = Patients::get()->toArray();
            $lastPatient = end($lastPatients_);
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

            $bulktoStore = $dbcolumns = $duplicate_patientList = $insuranceFound = [];
            $duplicate_patient_IDs = [];

            foreach ($data as $key => $value) {
                if (isset($value['id'])) {
                    $allPatients = Patients::where('id', $value['id'])->get()->toArray();
                }else{
                    $allPatients = Patients::where('insurance_id', $insurance_id)->get()->toArray();
                }
                    
                if ($fromCaregaps == 1) {
                    $value['insurance_id'] = @$request->insuranceIds ?? '';
                    $value['address'] = @$value['address'] ?? '';

                    $dbcolumns['method']   = 'preProcessFile';
                    $dbcolumns['tab_name'] = @$value['tab_name'] ?? NULL;
                    if (is_int($value['doctor_id'])) {
                        unset($value['doctor_id']);
                    }
                    $value['doctor_id'] = $value['doctor_name'];
                }

                $memberId = @$value['member_id'] ?? NULL;

                // Getting doctor Id from table to save in patient table
                $doctor = [];
                if (!empty($value['doctor_id']) && $value['doctor_id'] != "-") {
                    $doctorValue['doctor_id'] = str_replace(', ', ' ', $value['doctor_id']);
                    $doctor_name = explode(' ', $doctorValue['doctor_id']);
              
                    $lastName = strtoupper($doctor_name[0]);
                    $firstName = strtoupper($doctor_name[1]);
              
                    $doctor = array_filter($doctorData, function($item) use ($lastName, $firstName) {
                        $dblast_name = trim(strtoupper($item['last_name']));
                        $dbfirst_name = trim(strtoupper($item['first_name']));

                        return( ($dblast_name == trim($lastName) && $dbfirst_name == trim($firstName)) || ($dblast_name == trim($firstName) && $dbfirst_name == trim($lastName))  );
                    });

                    $doctor = reset($doctor);
                }

                if (!empty($value['insurance_id']) && $fromCaregaps != 1) {
                    $insuranceName = str_replace(' ','', $value['insurance_id']);
                    $insurance = array_filter($insurancesData, function($item) use ($insuranceName) {
                        return( strtoupper(str_replace(' ', '', $item['name'])) == strtoupper($insuranceName) || strpos(strtoupper($item['name']), strtoupper($insuranceName)) !== false );
                    });
                    $insurance = reset($insurance);
                    $insuranceFound[] = $insurance;

                    $value['insurance_id'] = isset($insurance['id']) && $insurance_id == $insurance['id'] ? $insurance_id : "";
                }

                if ($newidentity) {
                    $str = $newidentity;
                    $a = +$str;
                    $a = str_pad($a + 1, 8, '0', STR_PAD_LEFT);
                    $identity = $a;
                }

                // Assign STATUS
                $status = !empty($value['status']) ? $value['status'] : NULL;
                if (strtoupper($status) == 'ASSIGNED') {
                    $value['status'] = '1';
                } elseif (strtoupper($status) == 'ASSIGNABLE') {
                    $value['status'] = '2';
                }

                // Group STATUS
                $string = @$value['groups'] ?? "";
                if (!empty($string) && str_contains($string, 'A1')) {
                    $value['groups'] = "1";
                } elseif (!empty($string) && str_contains($string, 'A2')) {
                    $value['groups'] = "2";
                } elseif (!empty($string) && str_contains($string, 'B')) {
                    $value['groups'] = "3";
                } else if (!empty($string) && str_contains($string, 'C')) {
                    $value['groups'] = "4";
                } else {
                    $value['groups'] = NULL;
                }


                // Logic to handle the patient with middle name attached with lastname
                if (strpos($value['last_name'], ' ') !== false) {
                    $patient_last_name = explode(' ', $value['last_name']);
                    if (count($patient_last_name) > 1) {
                        array_pop($patient_last_name);
                    }
                    
                    $value['last_name'] = implode(' ', $patient_last_name);
                }
                $dob_ = date("m-d-Y", strtotime($value['dob']));
                $unique_id = $value['last_name'].$value['first_name']. str_replace(['/', '-'], '', $dob_);
              
                $exiting_patient_id = "";
                $duplicate_patient = array_filter($allPatients, function ($item) use ($unique_id, $value) {
                    $exiting_patient_dob = str_replace('/', '', Carbon::parse($item['dob'])->format('m/d/Y'));
                    $exiting_patient_ln = explode(' ', $item['last_name']);

                    if (count($exiting_patient_ln) > 1) {
                        array_pop($exiting_patient_ln);
                    }
                    $exiting_patient_ln = implode(' ', $exiting_patient_ln);

                    $exiting_patient_id = strtoupper($exiting_patient_ln).strtoupper($item['first_name']).$exiting_patient_dob;
                    return ($item['unique_id'] === $unique_id || $unique_id === $exiting_patient_id);
                });
                
                
                $duplicate_patient = reset($duplicate_patient);
               
                if (!empty($duplicate_patient)) {

                    $update_columns = [];

                    if ($fromCaregaps == 1) {
                        $update_columns = [
                            'member_id'     => $memberId,
                            'insurance_id'  => @$value['insurance_id'] ?? NULL,
                            'status'        => @$value['status'] ?? NULL,
                            'group'         => @$value['groups'] ?? NULL,
                            'doctor_id'     => @$doctor['id'] ?? NULL,
                            'patient_year'  => $patientYear
                        ];    
                    } else {
                        if (empty($duplicate_patient['member_id'])) {
                            $update_columns['member_id'] = @$memberId ?? NULL;
                        }
                        
                        if (empty($duplicate_patient['unique_id'])) {
                            $update_columns['unique_id'] = @$memberId ?? NULL;
                        }
                        
                        if (@$duplicate_patient['status'] != 1) {
                            $update_columns['status'] = @$value['status'] ?? NULL;
                        }

                        if (empty($duplicate_patient['group'])) {
                            $update_columns['group'] = @$value['groups'] ?? NULL;
                        }

                        if (empty($duplicate_patient['doctor_id'])) {
                            $update_columns['doctor_id'] = @$doctor['id'] ?? NULL;
                        }
                    }
//                     echo "<pre>";
// print_r($duplicate_patient['id']);

                    $previousPatientData = Patients::find($duplicate_patient['id']);

                    if (!empty($update_columns)) {
                        Patients::where('id', $duplicate_patient['id'])->update($update_columns);
                    }
                    $source = "Pre Process File";
                    $patientsResult2 = (new CommonFunctionController)->patientsChangesHistoryCreate($previousPatientData ,$duplicate_patient['id'] ,$source);

                    $duplicate_patient_IDs[] = $duplicate_patient['id'];
                }

                $duplicate_patientList[] = $duplicate_patient;

                // Break current itteration of loop if patient exist
                if ($duplicate_patient == true || empty($value['insurance_id'])) {
                    continue;
                }

                if (!empty($value['dob']) && empty($value['age'])) {
                    $dateOfBirth = Carbon::parse($value['dob'])->format('Y-m-d');
                    $value['age'] = Carbon::parse($dateOfBirth)->age;
                }
                
                $dbcolumns["unique_id"] = $unique_id;
                $dbcolumns["identity"] = @$identity?? NULL;
                $dbcolumns["member_id"] = $memberId;
                $dbcolumns["first_name"] = @$value['first_name'] ?? "";
                $dbcolumns["mid_name"] = @$value['mid_name'] ?? "";
                $dbcolumns["last_name"] = @$value['last_name'] ?? "";
                $dbcolumns["gender"] = @$value['gender'] ?? "";
                $dbcolumns["dob"] = date('Y-m-d', strtotime($value['dob']));
                $dbcolumns['age'] = @$value['age'] ?? '';
                $dbcolumns['cell'] = @$value['cell'] ?? '';
                $dbcolumns['contact_no'] = @$value['contact_no'] ?? @$value['cell'] ?? @$value['phone'] ?? '';
                $dbcolumns["address_2"] = @$value['address_2'] ?? "";
                $dbcolumns["address"]   = @$value['address'] ?? "";
                $dbcolumns["change_address"]   = @$value['address'] ?? "";
                $dbcolumns["change_doctor_id"] = @$doctor['id'] ??  NULL;
                $dbcolumns["clinic_id"] = $clinic_id;
                $dbcolumns["created_user"] = Auth::id();
                $dbcolumns["disease"] = "";
                $dbcolumns["doctor_id"] = @$doctor['id'] ?? NULL;
                $dbcolumns["dod"] = NULL;
                $dbcolumns["email"] = "";
                $dbcolumns["insurance_id"] = @$value['insurance_id'] ?? NULL;
                $dbcolumns["city"] =  @$value['city'] ?? "";
                $dbcolumns["state"] = @$value['state'] ?? "";
                $dbcolumns["zipCode"] =  !empty($value['zipCode']) ? str_replace(' ', '', $value['zipCode']) : NULL;
                $dbcolumns["preferred_contact"] =  @$value['preferred_contact'] ?? "";
                $dbcolumns["family_history"] = '[]';
                $dbcolumns["social_history"] = NULL;
                $dbcolumns["patient_consent"] = 0;
                $dbcolumns["consent_data"] = "";
                $dbcolumns["group"] = @$value['groups'] ?? NULL;
                $dbcolumns["status"] = @$value['status'] ?? NULL;
                $dbcolumns["patient_year"] = $patientYear;
                $dbcolumns["created_at"] = Carbon::now();
                $dbcolumns["updated_at"] = Carbon::now();
                $dbcolumns["deleted_at"] = NULL;

                $bulktoStore[] = (array)$dbcolumns;
                $newidentity = $identity;
            }
            //return $bulktoStore;
            if (!empty($bulktoStore)) {
                $res = Patients::insert($bulktoStore);
                $response = array('success'=>true,'message'=>'Patients Addedd successfully', 'total_added' => count($bulktoStore), 'duplicate' =>$duplicate_patientList , 'duplicate_patient_IDs' => $duplicate_patient_IDs);
            } else {
                $response = array('success'=>false,'message'=>'Duplicate patients found', 'duplicate' => $duplicate_patientList, 'duplicate_patient_IDs' => $duplicate_patient_IDs);
            }

        } catch (\Exception $e) {
            $response = array('success'=>false,'message'=>$e->getMessage(), 'line'=>$e->getLine());
        }

        return response()->json($response);
    }

    /**
     * The function `storeCclfData` in PHP receives a request, validates the data, processes it,
     * updates the database, and returns a JSON response.
     * 
     * @param Request request The `` parameter is an instance of the `Illuminate\Http\Request`
     * class. It represents the HTTP request made to the server and contains information such as the
     * request method, headers, and input data.
     * 
     * @return a JSON response.
     */
    public function storeCclfData(Request $request)
    {
        try {
            $validator = Validator::make($request->all(),
                [
                    'data'  => 'required',
                ],
                [
                    'data.required' => 'No data found to add in patients',
                ]
            );

            if($validator->fails()) {
                $error = $validator->getMessageBag()->first();
                return response()->json(['success'=>false,'errors'=>$error]);
            };

            $cclf_data = @$request->data ?? "";

            $member_ids = array_values(array_unique(array_column($cclf_data, 'member_id')));

            $patientsList = Patients::select('member_id', 'covered_amount')->whereIn('member_id', $member_ids)->get()->toArray();

            $result = [];
            foreach ($cclf_data as $object) {
                $member_id = $object["member_id"];
                $covered_amounts = $object["covered_amount"];
            
                if (!isset($result[$member_id])) {
                    $result[$member_id] = $covered_amounts;
                } else {
                    $result[$member_id] += $covered_amounts;
                }
            }

            if (!empty($result)) {

                foreach ($result as $member_id => $value) {
                    $patient = array_filter($patientsList, function($item) use ($member_id) {
                        return( $item['member_id'] == $member_id);
                    });
                    $patient = reset($patient);
    
                    if (!empty($patient)) {
                        $result[$member_id] = $value + floatval($patient['covered_amount']);
                    }
                }

                $updateData = [];
                foreach ($result as $key => $value) {
                    $updateData[$key] = $value;
                }
    
                $caseStatement = collect($updateData)->map(function ($covered_amount, $memberId) {
                    return "WHEN '{$memberId}' THEN {$covered_amount}";
                })->implode(' ');
    
                Patients::whereIn('member_id', array_keys($updateData))
                ->update(['covered_amount' => DB::raw("CASE member_id {$caseStatement} END")]);
    
                $response = array('success'=>true,'message'=>'Record Updated successfully');
            } else {
                $response = array('success'=>true,'message'=>'No Data Found');
            }

        } catch (\Exception $e) {
            $response = array('success'=>false,'message'=>$e->getMessage(), 'line'=>$e->getLine());
        }

        return response()->json($response);
    }

    public function downloadFile(Request $request){
        try {
            
            if(Storage::disk('s3')->exists($request->filename)) { 
                return Storage::disk(name:'s3')->download($request->filename); 
            } else {
                // File not found, handle the error (e.g., show 404 page)
                abort(404, 'File not found');
            }
        } catch (\Exception $e) {
            $response = array('success'=>false,'message'=>$e->getMessage(), 'line'=>$e->getLine());
        }

        return response()->json($response);
    }

    // breast cancer data for each patient
    private function bscData ($val, $checkGapAsPer)
    {
        $applyGapAsPer = '';
        if($checkGapAsPer == "1") {
            $applyGapAsPer = "_insurance";
        }

        $breast_cancer = [];

        if ( !empty($provider) &&  $typeId == 1 && $provider ==  "hum-001" ) {
            $care_gaps = $val['care_gaps_data_humana'];
        } elseif ( !empty($provider) &&  $typeId == 1 && $provider ==  "med-arz-001" ) {
            $care_gaps = $val['care_gaps_data_medicare_arizona'];
        } elseif ( !empty($provider) &&  $typeId == 1 && $provider ==  "aet-001" ) {
            $care_gaps = $val['care_gaps_data_aetna_medicare'];
        } elseif ( !empty($provider) &&  $typeId == 1 && $provider ==  "allwell-001" ) {
            $care_gaps = $val['care_gaps_data_allwell_medicare'];
        } elseif ( !empty($provider) &&  $typeId == 1 && $provider ==  "uhc-001" ) {
            $care_gaps = $val['care_gaps_data_united_healthcare'];
        }
        $care_gaps = $val['care_gaps_data'];
        // For Care Gap Comments
        $bsc_comments = array_values(array_filter($val['care_gaps_comment_data'], function($item) {
            return( $item['caregap_name'] == 'breast_cancer_gap');
        }));
        rsort($bsc_comments);

        // For Care Gap Details
        $bsc_details = array_values(array_filter($val['care_gaps_details'], function($item) {
            return( $item['caregap_name'] == 'breast_cancer_gap');
        }));
        rsort($bsc_details);
        
        $breast_cancer['caregap_id'] = $val['care_gaps_data']['id'];
        $breast_cancer['db_column'] = 'breast_cancer_gap'.$applyGapAsPer;
        $breast_cancer['title'] = 'Breast Cancer Screening (BCS)'.$applyGapAsPer;
        $breast_cancer['comments'] = @$bsc_comments ?? [];
        $breast_cancer['details'] = @$bsc_details ?? [];
        $breast_cancer['gap_status'] = @$care_gaps['breast_cancer_gap'.$applyGapAsPer] ?? "";
        $breast_cancer['options'] = ['N/A', 'Non-Compliant', 'Compliant', 'Bilateral Mastectomy', 'Patient Refused', 'Refusal Reviewed', 'Scheduled'];
        return $breast_cancer;
    }

    // colorectal cancer gap data for each patient
    private function colData ($val, $checkGapAsPer)
    {  
        $applyGapAsPer = '';
        
        if($checkGapAsPer == "1") {
            $applyGapAsPer = "_insurance";
        }
        if ( !empty($provider) &&  $typeId == 1 && $provider ==  "hum-001" ) {
            $val['care_gaps_data'] = $val['care_gaps_data_humana'];
        } elseif ( !empty($provider) &&  $typeId == 1 && $provider ==  "med-arz-001" ) {
            $val['care_gaps_data'] = $val['care_gaps_data_medicare_arizona'];
        } elseif ( !empty($provider) &&  $typeId == 1 && $provider ==  "aet-001" ) {
            $val['care_gaps_data'] = $val['care_gaps_data_aetna_medicare'];
        } elseif ( !empty($provider) &&  $typeId == 1 && $provider ==  "allwell-001" ) {
            $val['care_gaps_data'] = $val['care_gaps_data_allwell_medicare'];
        } elseif ( !empty($provider) &&  $typeId == 1 && $provider ==  "uhc-001" ) {
            $val['care_gaps_data'] = $val['care_gaps_data_united_healthcare'];
        }

        $care_gaps = $val['care_gaps_data'];

        // For Care Gap Comments
        $col_comments =  array_values(array_filter($val['care_gaps_comment_data'], function($item) {
            return( $item['caregap_name'] == 'colorectal_cancer_gap' );
        }));
        rsort($col_comments);

        // For Care Gap Details
        $col_details =  array_values(array_filter($val['care_gaps_details'], function($item) {
            return( $item['caregap_name'] == 'colorectal_cancer_gap');
        }));
        rsort($col_details);
        
        $colon_cancer['caregap_id'] = $val['care_gaps_data']['id'];
        $colon_cancer['db_column'] = 'colorectal_cancer_gap'.$applyGapAsPer;
        $colon_cancer['title'] = 'Colorectal Cancer Screening (COL)'.$applyGapAsPer;
        $colon_cancer['comments'] = @$col_comments ?? [];
        $colon_cancer['details'] = @$col_details ?? [];
        $colon_cancer['gap_status'] = @$care_gaps['colorectal_cancer_gap'.$applyGapAsPer] ?? "";
        $colon_cancer['options'] = ['N/A', 'Non-Compliant', 'Compliant', 'Total Colectomy', 'Colorectal Cancer', 'Patient Refused', 'Refusal Reviewed', 'Scheduled'];
        return $colon_cancer;
    }

    // Blood Pressure data for each patient
    private function cbpData ($val, $checkGapAsPer)
    {
        $applyGapAsPer = '';
        
        if($checkGapAsPer == 1) {
            $applyGapAsPer = "_insurance";
        }
        if ( !empty($provider) &&  $typeId == 1 && $provider ==  "hum-001" ) {
            $val['care_gaps_data'] = $val['care_gaps_data_humana'];
        } elseif ( !empty($provider) &&  $typeId == 1 && $provider ==  "med-arz-001" ) {
            $val['care_gaps_data'] = $val['care_gaps_data_medicare_arizona'];
        } elseif ( !empty($provider) &&  $typeId == 1 && $provider ==  "aet-001" ) {
            $val['care_gaps_data'] = $val['care_gaps_data_aetna_medicare'];
        } elseif ( !empty($provider) &&  $typeId == 1 && $provider ==  "allwell-001" ) {
            $val['care_gaps_data'] = $val['care_gaps_data_allwell_medicare'];
        } elseif ( !empty($provider) &&  $typeId == 3 && $provider ==  "hcarz-001" ) {
            $val['care_gaps_data'] = $val['care_gaps_data_healthchoice_arizona'];
        } elseif ( !empty($provider) &&  $typeId == 1 && $provider ==  "uhc-001" ) {
            $val['care_gaps_data'] = $val['care_gaps_data_united_healthcare'];
        }
        $care_gaps = $val['care_gaps_data'];

        // For Care Gap Comments
        $cbp_comments =  array_values(array_filter($val['care_gaps_comment_data'], function($item) {
            return( $item['caregap_name'] == 'high_bp_gap' );
        }));
        rsort($cbp_comments);

        // For Care Gap Details
        $cbp_details =  array_values(array_filter($val['care_gaps_details'], function($item) {
            return( $item['caregap_name'] == 'high_bp_gap' );
        }));
        rsort($cbp_details);

        $blood_pressure['caregap_id'] = $val['care_gaps_data']['id'];
        $blood_pressure['db_column'] = 'high_bp_gap'.$applyGapAsPer;
        $blood_pressure['title'] = 'Controlling Blood Pressure (CBP)'.$applyGapAsPer;
        $blood_pressure['comments'] = @$cbp_comments ?? [];
        $blood_pressure['details'] = @$cbp_details ?? [];
        $blood_pressure['gap_status'] = @$care_gaps['high_bp_gap'.$applyGapAsPer] ?? "";
        $blood_pressure['options'] = ['N/A', 'Non-Compliant', 'Compliant', 'Patient Refused', 'Refusal Reviewed', 'Scheduled'];
        return $blood_pressure;
    }

    // Poor Diabetes Care data for each patient
    private function hba1cDataPoor ($val, $checkGapAsPer)
    {
        $applyGapAsPer = '';
        
        if($checkGapAsPer == 1) {
            $applyGapAsPer = "_insurance";
        }
        if ( !empty($provider) &&  $typeId == 1 && $provider ==  "hum-001" ) {
            $val['care_gaps_data'] = $val['care_gaps_data_humana'];
        } elseif ( !empty($provider) &&  $typeId == 3 && $provider ==  "hcarz-001" ) {
            $val['care_gaps_data'] = $val['care_gaps_data_healthchoice_arizona'];
        }
        
        $care_gaps = $val['care_gaps_data'];

        // For Care Gap Comments
        $hba1c_comments =  array_values(array_filter($val['care_gaps_comment_data'], function($item) {
            return( $item['caregap_name'] == 'hba1c_poor_gap' );
        }));
        rsort($hba1c_comments);

        // For Care Gap Details
        $hba1c_details = array_values(array_filter($val['care_gaps_details'], function($item) {
            return( $item['caregap_name'] == 'hba1c_poor_gap' );
        }));
        rsort($hba1c_details);

        $hba1c_control['caregap_id'] = $val['care_gaps_data']['id'];
        $hba1c_control['db_column'] = 'hba1c_poor_gap'.$applyGapAsPer;
        $hba1c_control['title'] = 'Diabetes Care - Blood Sugar Poor Control (>9%)'.$applyGapAsPer;
        $hba1c_control['comments'] = @$hba1c_comments ?? [];
        $hba1c_control['details'] = @$hba1c_details ?? [];
        $hba1c_control['gap_status'] = @$care_gaps['hba1c_poor_gap'.$applyGapAsPer] ?? "";
        $hba1c_control['options'] = ['N/A', 'Non-Compliant', 'Compliant', 'Not Reported', 'Diagnosis Incorrect', 'Patient Refused', 'Refusal Reviewed', 'Scheduled'];
        return $hba1c_control;
    }
    // Poor Diabetes Care data for each patient
    private function hba1cData ($val, $checkGapAsPer)
    { 
        $applyGapAsPer = '';
        
        if($checkGapAsPer == 1) {
            $applyGapAsPer = "_insurance";
        }
        if ( !empty($provider) &&  $typeId == 1 && $provider ==  "hum-001" ) {
            $val['care_gaps_data'] = $val['care_gaps_data_humana'];
        } elseif ( !empty($provider) &&  $typeId == 1 && $provider ==  "med-arz-001" ) {
            $val['care_gaps_data'] = $val['care_gaps_data_medicare_arizona'];
        } elseif ( !empty($provider) &&  $typeId == 1 && $provider ==  "aet-001" ) {
            $val['care_gaps_data'] = $val['care_gaps_data_aetna_medicare'];
        } elseif ( !empty($provider) &&  $typeId == 1 && $provider ==  "allwell-001" ) {
            $val['care_gaps_data'] = $val['care_gaps_data_allwell_medicare'];
        } elseif ( !empty($provider) &&  $typeId == 1 && $provider ==  "uhc-001" ) {
            $val['care_gaps_data'] = $val['care_gaps_data_united_healthcare'];
        }

        $care_gaps = $val['care_gaps_data'];

        // For Care Gap Comments
        $hba1c_comments =  array_values(array_filter($val['care_gaps_comment_data'], function($item) {
            return( $item['caregap_name'] == 'hba1c_gap' );
        }));
        rsort($hba1c_comments);

        // For Care Gap Details
        $hba1c_details = array_values(array_filter($val['care_gaps_details'], function($item) {
            return( $item['caregap_name'] == 'hba1c_gap' );
        }));
        rsort($hba1c_details);

        $hba1c_control['caregap_id'] = $val['care_gaps_data']['id'];
        $hba1c_control['db_column'] = 'hba1c_gap'.$applyGapAsPer;
        $hba1c_control['title'] = 'Diabetes Care - Blood Sugar Control (HBA1C)'.$applyGapAsPer;
        $hba1c_control['comments'] = @$hba1c_comments ?? [];
        $hba1c_control['details'] = @$hba1c_details ?? [];
        $hba1c_control['gap_status'] = @$care_gaps['hba1c_gap'.$applyGapAsPer] ?? "";
        $hba1c_control['options'] = ['N/A', 'Non-Compliant', 'Compliant', 'Not Reported', 'Diagnosis Incorrect', 'Patient Refused', 'Refusal Reviewed', 'Scheduled'];
        return $hba1c_control;
    }

    // EyeExam data for each patient
    private function eyeExamData ($val, $checkGapAsPer)
    {
        $applyGapAsPer = '';
        
        if($checkGapAsPer == 1) {
            $applyGapAsPer = "_insurance";
        }
        if ( !empty($provider) &&  $typeId == 1 && $provider ==  "hum-001" ) {
            $val['care_gaps_data'] = $val['care_gaps_data_humana'];
        } elseif ( !empty($provider) &&  $typeId == 1 && $provider ==  "med-arz-001" ) {
            $val['care_gaps_data'] = $val['care_gaps_data_medicare_arizona'];
        } elseif ( !empty($provider) &&  $typeId == 1 && $provider ==  "aet-001" ) {
            $val['care_gaps_data'] = $val['care_gaps_data_aetna_medicare'];
        } elseif ( !empty($provider) &&  $typeId == 1 && $provider ==  "allwell-001" ) {
            $val['care_gaps_data'] = $val['care_gaps_data_allwell_medicare'];
        } elseif ( !empty($provider) &&  $typeId == 1 && $provider ==  "uhc-001" ) {
            $val['care_gaps_data'] = $val['care_gaps_data_united_healthcare'];
        }

        $care_gaps = $val['care_gaps_data'];

        // For Care Gap Comments
        $eyeexam_comments =  array_values(array_filter($val['care_gaps_comment_data'], function($item) {
            return( $item['caregap_name'] == 'eye_exam_gap' );
        }));
        rsort($eyeexam_comments);

        // For Care Gap Details
        $eyeexam_details =  array_values(array_filter($val['care_gaps_details'], function($item) {
            return( $item['caregap_name'] == 'eye_exam_gap' );
        }));
        rsort($eyeexam_details);

        $eye_exam['caregap_id'] = $val['care_gaps_data']['id'];
        $eye_exam['db_column'] = 'eye_exam_gap'.$applyGapAsPer;
        $eye_exam['details'] = @$eyeexam_details ?? [];
        $eye_exam['title'] = 'Eye Exam for Patients With Diabetes - Eye Exam'.$applyGapAsPer;
        $eye_exam['comments'] = @$eyeexam_comments ?? [];
        $eye_exam['gap_status'] = @$care_gaps['eye_exam_gap'.$applyGapAsPer] ?? "";
        $eye_exam['options'] = ['N/A', 'Non-Compliant', 'Compliant', 'Patient Refused', 'Refusal Reviewed', 'Scheduled'];
        return $eye_exam;
    }

    // Statin Therapy data for each patient
    private function statinTherapyData ($val, $checkGapAsPer)
    {
        $applyGapAsPer = '';
        
        if($checkGapAsPer == 1) {
            $applyGapAsPer = "_insurance";
        }
        if ( !empty($provider) &&  $typeId == 1 && $provider ==  "hum-001" ) {
            $val['care_gaps_data'] = $val['care_gaps_data_humana'];
        } elseif ( !empty($provider) &&  $typeId == 1 && $provider ==  "med-arz-001" ) {
            $val['care_gaps_data'] = $val['care_gaps_data_medicare_arizona'];
        } elseif ( !empty($provider) &&  $typeId == 1 && $provider ==  "aet-001" ) {
            $val['care_gaps_data'] = $val['care_gaps_data_aetna_medicare'];
        } elseif ( !empty($provider) &&  $typeId == 1 && $provider ==  "uhc-001" ) {
            $val['care_gaps_data'] = $val['care_gaps_data_united_healthcare'];
        }

        $care_gaps = $val['care_gaps_data'];

        // For Care Gap Comments
        $statin_comments =  array_values(array_filter($val['care_gaps_comment_data'], function($item) {
            return( $item['caregap_name'] == 'statin_therapy_gap');
        }));
        rsort($statin_comments);

        // For Care Gap Comments
        $statin_details =  array_values(array_filter($val['care_gaps_details'], function($item) {
            return( $item['caregap_name'] == 'statin_therapy_gap' );
        }));
        rsort($statin_details);

        $statin_therapy['caregap_id'] = $val['care_gaps_data']['id'];
        $statin_therapy['db_column'] = 'statin_therapy_gap'.$applyGapAsPer;
        $statin_therapy['details'] = @$statin_details ?? [];
        $statin_therapy['title'] = 'Statin Therapy for Patients with Cardiovascular Disease (SPC)'.$applyGapAsPer;
        $statin_therapy['comments'] = @$statin_comments ?? [];
        $statin_therapy['gap_status'] = @$care_gaps['statin_therapy_gap'.$applyGapAsPer] ?? "";
        $statin_therapy['options'] = ['N/A', 'Non-Compliant', 'Compliant', 'Patient Refused', 'Refusal Reviewed', 'Scheduled'];
        return $statin_therapy;
    }

    // Osteoporosis Mgmt  data for each patient
    private function osteoporosisData ($val, $checkGapAsPer)
    {
        $applyGapAsPer = '';
        
        if($checkGapAsPer == 1) {
            $applyGapAsPer = "_insurance";
        }
        if ( !empty($provider) &&  $typeId == 1 && $provider ==  "hum-001" ) {
            $val['care_gaps_data'] = $val['care_gaps_data_humana'];
        } elseif ( !empty($provider) &&  $typeId == 1 && $provider ==  "med-arz-001" ) {
            $val['care_gaps_data'] = $val['care_gaps_data_medicare_arizona'];
        } elseif ( !empty($provider) &&  $typeId == 1 && $provider ==  "aet-001" ) {
            $val['care_gaps_data'] = $val['care_gaps_data_aetna_medicare'];
        }
        $care_gaps = $val['care_gaps_data'];

        // For Care Gap Comments
        $osteoporosis_comments =  array_values(array_filter($val['care_gaps_comment_data'], function($item) {
            return( $item['caregap_name'] == 'osteoporosis_mgmt_gap' );
        }));
        rsort($osteoporosis_comments);

        // For Care Gap Details
        $osteoporosis_details =  array_values(array_filter($val['care_gaps_details'], function($item) {
            return( $item['caregap_name'] == 'osteoporosis_mgmt_gap' );
        }));
        rsort($osteoporosis_details);

        $osteoporosis_gap['caregap_id'] = $val['care_gaps_data']['id'];
        $osteoporosis_gap['db_column'] = 'osteoporosis_mgmt_gap'.$applyGapAsPer;
        $osteoporosis_gap['details'] = @$osteoporosis_details ?? [];
        $osteoporosis_gap['title'] = 'Osteoporosis Mgmt in Women who had Fracture (OMW)'.$applyGapAsPer;
        $osteoporosis_gap['comments'] = @$osteoporosis_comments ?? [];
        $osteoporosis_gap['gap_status'] = @$care_gaps['osteoporosis_mgmt_gap'.$applyGapAsPer] ?? "";
        $osteoporosis_gap['options'] = ['N/A', 'Non-Compliant', 'Compliant', 'Patient Refused', 'Refusal Reviewed', 'Scheduled'];
        return $osteoporosis_gap;
    }

    // Care for Adults Medication data for each patient
    private function adultsMedicationData ($val, $checkGapAsPer)
    {
        $applyGapAsPer = '';
        
        if($checkGapAsPer == 1) {
            $applyGapAsPer = "_insurance";
        }
        if ( !empty($provider) &&  $typeId == 1 && $provider ==  "hum-001" ) {
            $val['care_gaps_data'] = $val['care_gaps_data_humana'];
        } elseif ( !empty($provider) &&  $typeId == 1 && $provider ==  "med-arz-001" ) {
            $val['care_gaps_data'] = $val['care_gaps_data_medicare_arizona'];
        } elseif ( !empty($provider) &&  $typeId == 1 && $provider ==  "aet-001" ) {
            $val['care_gaps_data'] = $val['care_gaps_data_aetna_medicare'];
        } elseif ( !empty($provider) &&  $typeId == 1 && $provider ==  "allwell-001" ) {
            $val['care_gaps_data'] = $val['care_gaps_data_allwell_medicare'];
        } elseif ( !empty($provider) &&  $typeId == 1 && $provider ==  "uhc-001" ) {
            $val['care_gaps_data'] = $val['care_gaps_data_united_healthcare'];
        }

        $care_gaps = $val['care_gaps_data'];

        // For Care Gap Comments
        $adults_med_comments =  array_values(array_filter($val['care_gaps_comment_data'], function($item) {
            return( $item['caregap_name'] == 'adults_medic_gap' );
        }));
        rsort($adults_med_comments);

        // For Care Gap Details
        $adults_med_details =  array_values(array_filter($val['care_gaps_details'], function($item) {
            return( $item['caregap_name'] == 'adults_medic_gap' );
        }));
        rsort($adults_med_details);

        $adults_med_gap['caregap_id'] = $val['care_gaps_data']['id'];
        $adults_med_gap['db_column'] = 'adults_medic_gap'.$applyGapAsPer;
        $adults_med_gap['details'] = @$adults_med_details ?? [];
        $adults_med_gap['title'] = 'Care for Older Adults - Medication Review (COA2)'.$applyGapAsPer;
        $adults_med_gap['comments'] = @$adults_med_comments ?? [];
        $adults_med_gap['gap_status'] = @$care_gaps['adults_medic_gap'.$applyGapAsPer] ?? "";
        $adults_med_gap['options'] = ['N/A', 'Non-Compliant', 'Compliant', 'Not Reported', 'Diagnosis Incorrect', 'Patient Refused', 'Refusal Reviewed', 'Scheduled'];
        return $adults_med_gap;
    }

    // Care for Adults Pain Screening data for each patient
    private function adultsPainData ($val, $checkGapAsPer)
    {
        $applyGapAsPer = '';
        
        if($checkGapAsPer == 1) {
            $applyGapAsPer = "_insurance";
        }
        if ( !empty($provider) &&  $typeId == 1 && $provider ==  "hum-001" ) {
            $val['care_gaps_data'] = $val['care_gaps_data_humana'];
        } elseif ( !empty($provider) &&  $typeId == 1 && $provider ==  "med-arz-001" ) {
            $val['care_gaps_data'] = $val['care_gaps_data_medicare_arizona'];
        } elseif ( !empty($provider) &&  $typeId == 1 && $provider ==  "aet-001" ) {
            $val['care_gaps_data'] = $val['care_gaps_data_aetna_medicare'];
        } elseif ( !empty($provider) &&  $typeId == 1 && $provider ==  "allwell-001" ) {
            $val['care_gaps_data'] = $val['care_gaps_data_allwell_medicare'];
        } elseif ( !empty($provider) &&  $typeId == 1 && $provider ==  "uhc-001" ) {
            $val['care_gaps_data'] = $val['care_gaps_data_united_healthcare'];
        }
        $care_gaps = $val['care_gaps_data'];

        // For Care Gap Comments
        $adults_pain_comments =  array_values(array_filter($val['care_gaps_comment_data'], function($item) {
            return( $item['caregap_name'] == 'pain_screening_gap' );
        }));
        rsort($adults_pain_comments);

        // For Care Gap Details
        $adults_pain_details =  array_values(array_filter($val['care_gaps_details'], function($item) {
            return( $item['caregap_name'] == 'pain_screening_gap' );
        }));
        rsort($adults_pain_details);

        $adults_pain_gap['caregap_id'] = $val['care_gaps_data']['id'];
        $adults_pain_gap['db_column'] = 'pain_screening_gap'.$applyGapAsPer;
        $adults_pain_gap['details'] = @$adults_pain_details ?? [];
        $adults_pain_gap['title'] = 'Care for Older Adults Pain-Screening (COA4)'.$applyGapAsPer;
        $adults_pain_gap['comments'] = @$adults_pain_comments ?? [];
        $adults_pain_gap['gap_status'] = @$care_gaps['pain_screening_gap'.$applyGapAsPer] ?? "";
        $adults_pain_gap['options'] = ['N/A', 'Non-Compliant', 'Compliant', 'Not Reported', 'Diagnosis Incorrect', 'Patient Refused', 'Refusal Reviewed', 'Scheduled'];
        return $adults_pain_gap;
    }

    //Transitions of Care-Medication Reconciliation Post-Discharge (TCRM) data for each patient
    private function postDischargeData ($val, $checkGapAsPer)
    {
        $applyGapAsPer = '';
        
        if($checkGapAsPer == 1) {
            $applyGapAsPer = "_insurance";
        }
        if ( !empty($provider) &&  $typeId == 1 && $provider ==  "hum-001" ) {
            $val['care_gaps_data'] = $val['care_gaps_data_humana'];
        } elseif ( !empty($provider) &&  $typeId == 1 && $provider ==  "med-arz-001" ) {
            $val['care_gaps_data'] = $val['care_gaps_data_medicare_arizona'];
        } elseif ( !empty($provider) &&  $typeId == 1 && $provider ==  "aet-001" ) {
            $val['care_gaps_data'] = $val['care_gaps_data_aetna_medicare'];
        }
        $care_gaps = $val['care_gaps_data'];

        // For Care Gap Comments
        $poast_disc_comments =  array_values(array_filter($val['care_gaps_comment_data'], function($item) {
            return( $item['caregap_name'] == 'post_disc_gap' );
        }));
        rsort($poast_disc_comments);
        
        // For Care Gap Details
        $poast_disc_details =  array_values(array_filter($val['care_gaps_details'], function($item) {
            return( $item['caregap_name'] == 'post_disc_gap' );
        }));
        rsort($poast_disc_details);

        $poast_disc_gap['caregap_id'] = $val['care_gaps_data']['id'];
        $poast_disc_gap['db_column'] = 'post_disc_gap'.$applyGapAsPer;
        $poast_disc_gap['details'] = @$poast_disc_details ?? [];
        $poast_disc_gap['title'] = 'Transitions of Care-Medication Reconciliation Post-Discharge (TCRM)'.$applyGapAsPer;
        $poast_disc_gap['comments'] = @$poast_disc_comments ?? [];
        $poast_disc_gap['gap_status'] = @$care_gaps['post_disc_gap'.$applyGapAsPer] ?? "";
        $poast_disc_gap['options'] = ['N/A', 'Non-Compliant', 'Compliant', 'Not Reported', 'Diagnosis Incorrect', 'Patient Refused', 'Refusal Reviewed', 'Scheduled'];
        return $poast_disc_gap;
    }

    //Transitions of Care-Patient Engagement After Inpatient Discharge (TRCE) data for each patient
    private function afterInpatientData ($val, $checkGapAsPer)
    {
        $applyGapAsPer = '';
        
        if($checkGapAsPer == 1) {
            $applyGapAsPer = "_insurance";
        }
        if ( !empty($provider) &&  $typeId == 1 && $provider ==  "hum-001" ) {
            $val['care_gaps_data'] = $val['care_gaps_data_humana'];
        } elseif ( !empty($provider) &&  $typeId == 1 && $provider ==  "med-arz-001" ) {
            $val['care_gaps_data'] = $val['care_gaps_data_medicare_arizona'];
        } elseif ( !empty($provider) &&  $typeId == 1 && $provider ==  "aet-001" ) {
            $val['care_gaps_data'] = $val['care_gaps_data_aetna_medicare'];
        }
        $care_gaps = $val['care_gaps_data'];

        // For Care Gap Comments
        $after_inp_comments =  array_values(array_filter($val['care_gaps_comment_data'], function($item) {
            return( $item['caregap_name'] == 'after_inp_disc_gap' );
        }));
        rsort($after_inp_comments);
        
        // For Care Gap Details
        $after_inp_details =  array_values(array_filter($val['care_gaps_details'], function($item) {
            return( $item['caregap_name'] == 'after_inp_disc_gap' );
        }));
        rsort($after_inp_details);

        $after_inp_gap['caregap_id'] = $val['care_gaps_data']['id'];
        $after_inp_gap['db_column'] = 'after_inp_disc_gap'.$applyGapAsPer;
        $after_inp_gap['details'] = @$after_inp_details ?? [];
        $after_inp_gap['title'] = 'Transitions of Care-Patient Engagement After Inpatient Discharge (TRCE)'.$applyGapAsPer;
        $after_inp_gap['comments'] = @$after_inp_comments ?? [];
        $after_inp_gap['gap_status'] = @$care_gaps['after_inp_disc_gap'.$applyGapAsPer] ?? "";
        $after_inp_gap['options'] = ['N/A', 'Non-Compliant', 'Compliant', 'Not Reported', 'Diagnosis Incorrect', 'Patient Refused', 'Refusal Reviewed', 'Scheduled'];
        return $after_inp_gap;
    }

    // Care for Older Adults Functional Status data for each patient
    private function adultsFuncData ($val, $checkGapAsPer)
    {
        $applyGapAsPer = '';
        
        if($checkGapAsPer == 1) {
            $applyGapAsPer = "_insurance";
        }
        if ( !empty($provider) &&  $typeId == 1 && $provider ==  "hum-001" ) {
            $val['care_gaps_data'] = $val['care_gaps_data_humana'];
        } elseif ( !empty($provider) &&  $typeId == 1 && $provider ==  "med-arz-001" ) {
            $val['care_gaps_data'] = $val['care_gaps_data_medicare_arizona'];
        } elseif ( !empty($provider) &&  $typeId == 1 && $provider ==  "aet-001" ) {
            $val['care_gaps_data'] = $val['care_gaps_data_aetna_medicare'];
        }
        $care_gaps = $val['care_gaps_data'];

        // For Care Gap Comments
        $adults_func_comments =  array_values(array_filter($val['care_gaps_comment_data'], function($item) {
            return( $item['caregap_name'] == 'adults_func_gap' );
        }));
        rsort($adults_func_comments);
        
        // For Care Gap Details
        $adults_func_details =  array_values(array_filter($val['care_gaps_details'], function($item) {
            return( $item['caregap_name'] == 'adults_func_gap' );
        }));
        rsort($adults_func_details);

        $adults_func['caregap_id'] = $val['care_gaps_data']['id'];
        $adults_func['db_column'] = 'adults_func_gap'.$applyGapAsPer;
        $adults_func['details'] = @$adults_func_details ?? [];
        $adults_func['title'] = 'Care for Older Adults Functional Status '.$applyGapAsPer;
        $adults_func['comments'] = @$adults_func_comments ?? [];
        $adults_func['gap_status'] = @$care_gaps['adults_func_gap'.$applyGapAsPer] ?? "";
        $adults_func['options'] = ['N/A', 'Non-Compliant', 'Compliant', 'Not Reported', 'Diagnosis Incorrect', 'Patient Refused', 'Refusal Reviewed', 'Scheduled'];
        return $adults_func;
    }

    
    private function awvData ($val, $checkGapAsPer)
    {
        $applyGapAsPer = '';
        
        if($checkGapAsPer == 1) {
            $applyGapAsPer = "_insurance";
        }
        if ( !empty($provider) &&  $typeId == 1 && $provider ==  "hum-001" ) {
            $val['care_gaps_data'] = $val['care_gaps_data_humana'];
        } elseif ( !empty($provider) &&  $typeId == 1 && $provider ==  "med-arz-001" ) {
            $val['care_gaps_data'] = $val['care_gaps_data_medicare_arizona'];
        } elseif ( !empty($provider) &&  $typeId == 1 && $provider ==  "aet-001" ) {
            $val['care_gaps_data'] = $val['care_gaps_data_aetna_medicare'];
        } elseif ( !empty($provider) &&  $typeId == 1 && $provider ==  "allwell-001" ) {
            $val['care_gaps_data'] = $val['care_gaps_data_allwell_medicare'];
        } elseif ( !empty($provider) &&  $typeId == 1 && $provider ==  "uhc-001" ) {
            $val['care_gaps_data'] = $val['care_gaps_data_united_healthcare'];
        }
        $care_gaps = $val['care_gaps_data'];

        // For Care Gap Comments
        $awv_comments =  array_values(array_filter($val['care_gaps_comment_data'], function($item) {
            return( $item['caregap_name'] == 'awv_gap' );
        }));
        rsort($awv_comments);
        
        // For Care Gap Details
        $awv_details =  array_values(array_filter($val['care_gaps_details'], function($item) {
            return( $item['caregap_name'] == 'awv_gap' );
        }));
        rsort($awv_details);

        $awvGap['caregap_id'] = $val['care_gaps_data']['id'];
        $awvGap['db_column'] = 'awv_gap'.$applyGapAsPer;
        $awvGap['details'] = @$awv_details ?? [];
        $awvGap['title'] = 'Annual Wellness Visit'.$applyGapAsPer;
        $awvGap['comments'] = @$awv_comments ?? [];
        $awvGap['gap_status'] = @$care_gaps['awv_gap'.$applyGapAsPer] ?? "";
        $awvGap['options'] = ['N/A', 'Changed PCP', 'Completed', 'Left Practice', 'Need To Schedule', 'Not An Established Patient', 'Not Found In PF', 'Not In Service', 'Pending Visit', 'Refused', 'Unable To Reach'];
        return $awvGap;
    }

    
    // Humana   
    // FAED_visit_gap
    // Follow-Up After Emergency Department Visit for MCC (FMC)
    private function fmcData ($val, $checkGapAsPer)
    {
        $applyGapAsPer = '';
        
        if($checkGapAsPer == 1) {
            $applyGapAsPer = "_insurance";
        }
        if ( !empty($provider) &&  $typeId == 1 && $provider ==  "hum-001" ) {
            $val['care_gaps_data'] = $val['care_gaps_data_humana'];
        } elseif ( !empty($provider) &&  $typeId == 1 && $provider ==  "med-arz-001" ) {
            $val['care_gaps_data'] = $val['care_gaps_data_medicare_arizona'];
        } elseif ( !empty($provider) &&  $typeId == 1 && $provider ==  "aet-001" ) {
            $val['care_gaps_data'] = $val['care_gaps_data_aetna_medicare'];
        }
        $care_gaps = $val['care_gaps_data'];

        // For Care Gap Comments
        $fmc_comments =  array_values(array_filter($val['care_gaps_comment_data'], function($item) {
            return( $item['caregap_name'] == 'faed_visit_gap' );
        }));
        rsort($fmc_comments);
        
        // For Care Gap Details
        $fmc_details =  array_values(array_filter($val['care_gaps_details'], function($item) {
            return( $item['caregap_name'] == 'faed_visit_gap' );
        }));
        rsort($fmc_details);
        
        $fmcGap['caregap_id'] = $val['care_gaps_data']['id'];
        $fmcGap['db_column'] = 'faed_visit_gap'.$applyGapAsPer;
        $fmcGap['details'] = @$fmc_details ?? [];
        $fmcGap['title'] = 'Follow-Up After Emergency Department Visit for MCC (FMC)'.$applyGapAsPer;
        $fmcGap['comments'] = @$fmc_comments ?? [];
        $fmcGap['gap_status'] = @$care_gaps['faed_visit_gap'.$applyGapAsPer] ?? "";
        $fmcGap['options'] = ['N/A', 'Changed PCP', 'Completed', 'Left Practice', 'Need To Schedule', 'Not An Established Patient', 'Not Found In PF', 'Not In Service', 'Pending Visit', 'Refused', 'Unable To Reach'];
        return $fmcGap;
    }
    // omw_fracture_gap
    // Osteoporosis Management in Women Who Had a Fracture (OMW)
    private function omwData ($val, $checkGapAsPer)
    {
        $applyGapAsPer = '';
        
        if($checkGapAsPer == 1) {
            $applyGapAsPer = "_insurance";
        }
        if ( !empty($provider) &&  $typeId == 1 && $provider ==  "hum-001" ) {
            $val['care_gaps_data'] = $val['care_gaps_data_humana'];
        } elseif ( !empty($provider) &&  $typeId == 1 && $provider ==  "med-arz-001" ) {
            $val['care_gaps_data'] = $val['care_gaps_data_medicare_arizona'];
        } elseif ( !empty($provider) &&  $typeId == 1 && $provider ==  "aet-001" ) {
            $val['care_gaps_data'] = $val['care_gaps_data_aetna_medicare'];
        }
        $care_gaps = $val['care_gaps_data'];

        // For Care Gap Comments
        $omw_comments =  array_values(array_filter($val['care_gaps_comment_data'], function($item) {
            return( $item['caregap_name'] == 'omw_fracture_gap' );
        }));
        rsort($omw_comments);
        
        // For Care Gap Details
        $omw_details =  array_values(array_filter($val['care_gaps_details'], function($item) {
            return( $item['caregap_name'] == 'omw_fracture_gap' );
        }));
        rsort($omw_details);
        
        $omwGap['caregap_id'] = $val['care_gaps_data']['id'];
        $omwGap['db_column'] = 'omw_fracture_gap'.$applyGapAsPer;
        $omwGap['details'] = @$omw_details ?? [];
        $omwGap['title'] = 'Osteoporosis Management in Women Who Had a Fracture (OMW)'.$applyGapAsPer;
        $omwGap['comments'] = @$omw_comments ?? [];
        $omwGap['gap_status'] = @$care_gaps['omw_fracture_gap'.$applyGapAsPer] ?? "";
        $omwGap['options'] = ['N/A', 'Changed PCP', 'Completed', 'Left Practice', 'Need To Schedule', 'Not An Established Patient', 'Not Found In PF', 'Not In Service', 'Pending Visit', 'Refused', 'Unable To Reach'];
        return $omwGap;
    }
    // pc_readmissions_gap
    // Plan All-Cause Readmissions (PCR)
    private function pcrData ($val, $checkGapAsPer)
    {
        $applyGapAsPer = '';
        
        if($checkGapAsPer == 1) {
            $applyGapAsPer = "_insurance";
        }
        if ( !empty($provider) &&  $typeId == 1 && $provider ==  "hum-001" ) {
            $val['care_gaps_data'] = $val['care_gaps_data_humana'];
        } elseif ( !empty($provider) &&  $typeId == 1 && $provider ==  "med-arz-001" ) {
            $val['care_gaps_data'] = $val['care_gaps_data_medicare_arizona'];
        } elseif ( !empty($provider) &&  $typeId == 1 && $provider ==  "aet-001" ) {
            $val['care_gaps_data'] = $val['care_gaps_data_aetna_medicare'];
        }
        $care_gaps = $val['care_gaps_data'];

        // For Care Gap Comments
        $pcr_comments =  array_values(array_filter($val['care_gaps_comment_data'], function($item) {
            return( $item['caregap_name'] == 'pc_readmissions_gap' );
        }));
        rsort($pcr_comments);
        
        // For Care Gap Details
        $pcr_details =  array_values(array_filter($val['care_gaps_details'], function($item) {
            return( $item['caregap_name'] == 'pc_readmissions_gap' );
        }));
        rsort($pcr_details);

        $pcrGap['caregap_id'] = $val['care_gaps_data']['id'];
        $pcrGap['db_column'] = 'pc_readmissions_gap'.$applyGapAsPer;
        $pcrGap['details'] = @$pcr_details ?? [];
        $pcrGap['title'] = 'Plan All-Cause Readmissions (PCR)'.$applyGapAsPer;
        $pcrGap['comments'] = @$pcr_comments ?? [];
        $pcrGap['gap_status'] = @$care_gaps['pc_readmissions_gap'.$applyGapAsPer] ?? "";
        $pcrGap['options'] = ['N/A', 'Changed PCP', 'Completed', 'Left Practice', 'Need To Schedule', 'Not An Established Patient', 'Not Found In PF', 'Not In Service', 'Pending Visit', 'Refused', 'Unable To Reach'];
        return $pcrGap;
    }
    // spc_disease_gap
    // Statin Therapy for Patients with Cardiovascular Disease: Received Statin Therapy (SPC_STATIN)
    private function spcStatinData ($val, $checkGapAsPer)
    {
        $applyGapAsPer = '';
        
        if($checkGapAsPer == 1) {
            $applyGapAsPer = "_insurance";
        }
        if ( !empty($provider) &&  $typeId == 1 && $provider ==  "hum-001" ) {
            $val['care_gaps_data'] = $val['care_gaps_data_humana'];
        } elseif ( !empty($provider) &&  $typeId == 1 && $provider ==  "med-arz-001" ) {
            $val['care_gaps_data'] = $val['care_gaps_data_medicare_arizona'];
        } elseif ( !empty($provider) &&  $typeId == 1 && $provider ==  "aet-001" ) {
            $val['care_gaps_data'] = $val['care_gaps_data_aetna_medicare'];
        }
        $care_gaps = $val['care_gaps_data'];

        // For Care Gap Comments
        $spcStatin_comments =  array_values(array_filter($val['care_gaps_comment_data'], function($item) {
            return( $item['caregap_name'] == 'spc_disease_gap' );
        }));
        rsort($spcStatin_comments);
        
        // For Care Gap Details
        $spcStatin_details =  array_values(array_filter($val['care_gaps_details'], function($item) {
            return( $item['caregap_name'] == 'spc_disease_gap' );
        }));
        rsort($spcStatin_details);

        $spcStatinGap['caregap_id'] = $val['care_gaps_data']['id'];
        $spcStatinGap['db_column'] = 'spc_disease_gap'.$applyGapAsPer;
        $spcStatinGap['details'] = @$spcStatin_details ?? [];
        $spcStatinGap['title'] = 'Statin Therapy for Patients with Cardiovascular Disease: Received Statin Therapy (SPC_STATIN)'.$applyGapAsPer;
        $spcStatinGap['comments'] = @$spcStatin_comments ?? [];
        $spcStatinGap['gap_status'] = @$care_gaps['spc_disease_gap'.$applyGapAsPer] ?? "";
        $spcStatinGap['options'] = ['N/A', 'Changed PCP', 'Completed', 'Left Practice', 'Need To Schedule', 'Not An Established Patient', 'Not Found In PF', 'Not In Service', 'Pending Visit', 'Refused', 'Unable To Reach'];
        return $spcStatinGap;
    }

    // ma_cholesterol_gap
    // Medication Adherence for Cholesterol (Statins): Statins (ADH_STATIN)
    private function adhStatinData ($val, $checkGapAsPer)
    {
        $applyGapAsPer = '';
        
        if($checkGapAsPer == 1) {
            $applyGapAsPer = "_insurance";
        }
        if ( !empty($provider) &&  $typeId == 1 && $provider ==  "hum-001" ) {
            $val['care_gaps_data'] = $val['care_gaps_data_humana'];
        } elseif ( !empty($provider) &&  $typeId == 1 && $provider ==  "med-arz-001" ) {
            $val['care_gaps_data'] = $val['care_gaps_data_medicare_arizona'];
        } elseif ( !empty($provider) &&  $typeId == 1 && $provider ==  "aet-001" ) {
            $val['care_gaps_data'] = $val['care_gaps_data_aetna_medicare'];
        }
        $care_gaps = $val['care_gaps_data'];

        // For Care Gap Comments
        $adhStatin_comments =  array_values(array_filter($val['care_gaps_comment_data'], function($item) {
            return( $item['caregap_name'] == 'ma_cholesterol_gap' );
        }));
        rsort($adhStatin_comments);
        
        // For Care Gap Details
        $adhStatin_details =  array_values(array_filter($val['care_gaps_details'], function($item) {
            return( $item['caregap_name'] == 'ma_cholesterol_gap' );
        }));
        rsort($adhStatin_details);
        
        $adhStatinGap['caregap_id'] = $val['care_gaps_data']['id'];
        $adhStatinGap['db_column'] = 'ma_cholesterol_gap'.$applyGapAsPer;
        $adhStatinGap['details'] = @$adhStatin_details ?? [];
        $adhStatinGap['title'] = 'Medication Adherence for Cholesterol (Statins): Statins (ADH_STATIN)'.$applyGapAsPer;
        $adhStatinGap['comments'] = @$adhStatin_comments ?? [];
        $adhStatinGap['gap_status'] = @$care_gaps['ma_cholesterol_gap'.$applyGapAsPer] ?? "";
        $adhStatinGap['options'] = ['N/A', 'Changed PCP', 'Completed', 'Left Practice', 'Need To Schedule', 'Not An Established Patient', 'Not Found In PF', 'Not In Service', 'Pending Visit', 'Refused', 'Unable To Reach'];
        return $adhStatinGap;
    }

    // mad_medications_gap
    // Medication Adherence for Diabetes Medications: Diabetes Medications (ADH_DIAB)
    private function ahdDiabData ($val, $checkGapAsPer)
    {
        $applyGapAsPer = '';
        
        if($checkGapAsPer == 1) {
            $applyGapAsPer = "_insurance";
        }
        if ( !empty($provider) &&  $typeId == 1 && $provider ==  "hum-001" ) {
            $val['care_gaps_data'] = $val['care_gaps_data_humana'];
        } elseif ( !empty($provider) &&  $typeId == 1 && $provider ==  "med-arz-001" ) {
            $val['care_gaps_data'] = $val['care_gaps_data_medicare_arizona'];
        } elseif ( !empty($provider) &&  $typeId == 1 && $provider ==  "aet-001" ) {
            $val['care_gaps_data'] = $val['care_gaps_data_aetna_medicare'];
        }
        $care_gaps = $val['care_gaps_data'];

        // For Care Gap Comments
        $adhDiab_comments =  array_values(array_filter($val['care_gaps_comment_data'], function($item) {
            return( $item['caregap_name'] == 'mad_medications_gap' );
        }));
        rsort($adhDiab_comments);

        // For Care Gap Details
        $adhDiab_details =  array_values(array_filter($val['care_gaps_details'], function($item) {
            return( $item['caregap_name'] == 'mad_medications_gap' );
        }));
        rsort($adhDiab_details);

        $adhDiabGap['caregap_id'] = $val['care_gaps_data']['id'];
        $adhDiabGap['db_column'] = 'mad_medications_gap'.$applyGapAsPer;
        $adhDiabGap['details'] = @$adhDiab_details ?? [];
        $adhDiabGap['title'] = 'Medication Adherence for Diabetes Medications: Diabetes Medications (ADH_DIAB)'.$applyGapAsPer;
        $adhDiabGap['comments'] = @$adhDiab_comments ?? [];
        $adhDiabGap['gap_status'] = @$care_gaps['mad_medications_gap'.$applyGapAsPer] ?? "";
        $adhDiabGap['options'] = ['N/A', 'Changed PCP', 'Completed', 'Left Practice', 'Need To Schedule', 'Not An Established Patient', 'Not Found In PF', 'Not In Service', 'Pending Visit', 'Refused', 'Unable To Reach'];
        return $adhDiabGap;
    }

    // ma_hypertension_gap
    // Medication Adherence for Hypertension (ACE or ARB): ACE (ADH_ACE)
    private function ahdAceData ($val, $checkGapAsPer)
    {
        $applyGapAsPer = '';
        
        if($checkGapAsPer == 1) {
            $applyGapAsPer = "_insurance";
        }
        if ( !empty($provider) &&  $typeId == 1 && $provider ==  "hum-001" ) {
            $val['care_gaps_data'] = $val['care_gaps_data_humana'];
        } elseif ( !empty($provider) &&  $typeId == 1 && $provider ==  "med-arz-001" ) {
            $val['care_gaps_data'] = $val['care_gaps_data_medicare_arizona'];
        } elseif ( !empty($provider) &&  $typeId == 1 && $provider ==  "aet-001" ) {
            $val['care_gaps_data'] = $val['care_gaps_data_aetna_medicare'];
        }
        $care_gaps = $val['care_gaps_data'];

        // For Care Gap Comments
        $adhAce_comments =  array_values(array_filter($val['care_gaps_comment_data'], function($item) {
            return( $item['caregap_name'] == 'ma_hypertension_gap' );
        }));        
        rsort($adhAce_comments);

        // For Care Gap Details
        $adhAce_details =  array_values(array_filter($val['care_gaps_details'], function($item) {
            return( $item['caregap_name'] == 'ma_hypertension_gap' );
        }));
        rsort($adhAce_details);

        $adhAceGap['caregap_id'] = $val['care_gaps_data']['id'];
        $adhAceGap['db_column'] = 'ma_hypertension_gap'.$applyGapAsPer;
        $adhAceGap['details'] = @$adhAce_details ?? [];
        $adhAceGap['title'] = 'Medication Adherence for Hypertension (ACE or ARB): ACE (ADH_ACE)'.$applyGapAsPer;
        $adhAceGap['comments'] = @$adhAce_comments ?? [];
        $adhAceGap['gap_status'] = @$care_gaps['ma_hypertension_gap'.$applyGapAsPer] ?? "";
        $adhAceGap['options'] = ['N/A', 'Changed PCP', 'Completed', 'Left Practice', 'Need To Schedule', 'Not An Established Patient', 'Not Found In PF', 'Not In Service', 'Pending Visit', 'Refused', 'Unable To Reach'];
        return $adhAceGap;
    }

    // sup_diabetes_gap
    // Statin Use in Persons with Diabetes (SUPD)
    private function supdData ($val, $checkGapAsPer)
    {
        $applyGapAsPer = '';
        
        if($checkGapAsPer == 1) {
            $applyGapAsPer = "_insurance";
        }
        if ( !empty($provider) &&  $typeId == 1 && $provider ==  "hum-001" ) {
            $val['care_gaps_data'] = $val['care_gaps_data_humana'];
        } elseif ( !empty($provider) &&  $typeId == 1 && $provider ==  "med-arz-001" ) {
            $val['care_gaps_data'] = $val['care_gaps_data_medicare_arizona'];
        } elseif ( !empty($provider) &&  $typeId == 1 && $provider ==  "aet-001" ) {
            $val['care_gaps_data'] = $val['care_gaps_data_aetna_medicare'];
        } elseif ( !empty($provider) &&  $typeId == 1 && $provider ==  "allwell-001" ) {
            $val['care_gaps_data'] = $val['care_gaps_data_allwell_medicare'];
        } elseif ( !empty($provider) &&  $typeId == 1 && $provider ==  "uhc-001" ) {
            $val['care_gaps_data'] = $val['care_gaps_data_united_healthcare'];
        }
        $care_gaps = $val['care_gaps_data'];

        // For Care Gap Comments
        $supd_comments =  array_values(array_filter($val['care_gaps_comment_data'], function($item) {
            return( $item['caregap_name'] == 'sup_diabetes_gap' );
        }));
        rsort($supd_comments);

        // For Care Gap Details
        $supd_details =  array_values(array_filter($val['care_gaps_details'], function($item) {
            return( $item['caregap_name'] == 'sup_diabetes_gap' );
        }));
        rsort($supd_details);
        
        $supdGap['caregap_id'] = $val['care_gaps_data']['id'];
        $supdGap['db_column'] = 'sup_diabetes_gap'.$applyGapAsPer;
        $supdGap['details'] = @$supd_details ?? [];
        $supdGap['title'] = 'Statin Use in Persons with Diabetes (SUPD)'.$applyGapAsPer;
        $supdGap['comments'] = @$supd_comments ?? [];
        $supdGap['gap_status'] = @$care_gaps['sup_diabetes_gap'.$applyGapAsPer] ?? "";
        $supdGap['options'] = ['N/A', 'Changed PCP', 'Completed', 'Left Practice', 'Need To Schedule', 'Not An Established Patient', 'Not Found In PF', 'Not In Service', 'Pending Visit', 'Refused', 'Unable To Reach'];
        return $supdGap;
    }
    //---------------------------------------------- ALLWELL START--------------------------------------------
    // m_high_risk_cc_gap
    //FMC - F/U ED Multiple High Risk Chronic Conditions img 8 new 
    private function highRiskData ($val, $checkGapAsPer)
    {
        $applyGapAsPer = '';
        
        if($checkGapAsPer == 1) {
            $applyGapAsPer = "_insurance";
        }
        if ( !empty($provider) &&  $typeId == 1 && $provider ==  "allwell-001" ) {
            $val['care_gaps_data'] = $val['care_gaps_data_allwell_medicare'];
        }
        $care_gaps = $val['care_gaps_data'];

        // For Care Gap Comments
        $highRisk_comments =  array_values(array_filter($val['care_gaps_comment_data'], function($item) {
            return( $item['caregap_name'] == 'm_high_risk_cc_gap' );
        }));
        rsort($highRisk_comments);

        // For Care Gap Details
        $highRisk_details =  array_values(array_filter($val['care_gaps_details'], function($item) {
            return( $item['caregap_name'] == 'm_high_risk_cc_gap' );
        }));
        rsort($highRisk_details);
        
        $highRiskGap['caregap_id'] = $val['care_gaps_data']['id'];
        $highRiskGap['db_column'] = 'm_high_risk_cc_gap'.$applyGapAsPer;
        $highRiskGap['details'] = @$highRisk_details ?? [];
        $highRiskGap['title'] = 'FMC - F/U ED Multiple High Risk Chronic Conditions '.$applyGapAsPer;
        $highRiskGap['comments'] = @$highRisk_comments ?? [];
        $highRiskGap['gap_status'] = @$care_gaps['m_high_risk_cc_gap'.$applyGapAsPer] ?? "";
        $highRiskGap['options'] = ['N/A', 'Changed PCP', 'Completed', 'Left Practice', 'Need To Schedule', 'Not An Established Patient', 'Not Found In PF', 'Not In Service', 'Pending Visit', 'Refused', 'Unable To Reach'];
        return $highRiskGap;
    }

    //Med Adherence - Diabetic img 9 new
    //med_adherence_diabetic_gap
    private function adherenceDiabeticData ($val, $checkGapAsPer)
    {
        $applyGapAsPer = '';
        
        if($checkGapAsPer == 1) {
            $applyGapAsPer = "_insurance";
        }
        if ( !empty($provider) &&  $typeId == 1 && $provider ==  "allwell-001" ) {
            $val['care_gaps_data'] = $val['care_gaps_data_allwell_medicare'];
        }
        $care_gaps = $val['care_gaps_data'];

        // For Care Gap Comments
        $adherenceDiabetic_comments =  array_values(array_filter($val['care_gaps_comment_data'], function($item) {
            return( $item['caregap_name'] == 'med_adherence_diabetic_gap' );
        }));
        rsort($adherenceDiabetic_comments);

        // For Care Gap Details
        $adherenceDiabetic_details =  array_values(array_filter($val['care_gaps_details'], function($item) {
            return( $item['caregap_name'] == 'med_adherence_diabetic_gap' );
        }));
        rsort($adherenceDiabetic_details);
        
        $adherenceDiabeticGap['caregap_id'] = $val['care_gaps_data']['id'];
        $adherenceDiabeticGap['db_column'] = 'med_adherence_diabetic_gap'.$applyGapAsPer;
        $adherenceDiabeticGap['details'] = @$adherenceDiabetic_details ?? [];
        $adherenceDiabeticGap['title'] = 'Med Adherence - Diabetic '.$applyGapAsPer;
        $adherenceDiabeticGap['comments'] = @$adherenceDiabetic_comments ?? [];
        $adherenceDiabeticGap['gap_status'] = @$care_gaps['med_adherence_diabetic_gap'.$applyGapAsPer] ?? "";
        $adherenceDiabeticGap['options'] = ['N/A', 'Changed PCP', 'Completed', 'Left Practice', 'Need To Schedule', 'Not An Established Patient', 'Not Found In PF', 'Not In Service', 'Pending Visit', 'Refused', 'Unable To Reach'];
        return $adherenceDiabeticGap;
    }

    //Med Adherence - RAS img 10 new
    //med_adherence_ras_gap
    private function adherenceRASData ($val, $checkGapAsPer)
    {
        $applyGapAsPer = '';
        
        if($checkGapAsPer == 1) {
            $applyGapAsPer = "_insurance";
        }
        if ( !empty($provider) &&  $typeId == 1 && $provider ==  "allwell-001" ) {
            $val['care_gaps_data'] = $val['care_gaps_data_allwell_medicare'];
        } elseif ( !empty($provider) &&  $typeId == 1 && $provider ==  "uhc-001" ) {
            $val['care_gaps_data'] = $val['care_gaps_data_united_healthcare'];
        }
        $care_gaps = $val['care_gaps_data'];

        // For Care Gap Comments
        $adherenceRAS_comments =  array_values(array_filter($val['care_gaps_comment_data'], function($item) {
            return( $item['caregap_name'] == 'med_adherence_ras_gap' );
        }));
        rsort($adherenceRAS_comments);

        // For Care Gap Details
        $adherenceRAS_details =  array_values(array_filter($val['care_gaps_details'], function($item) {
            return( $item['caregap_name'] == 'med_adherence_ras_gap' );
        }));
        rsort($adherenceRAS_details);
        
        $adherenceRASGap['caregap_id'] = $val['care_gaps_data']['id'];
        $adherenceRASGap['db_column'] = 'med_adherence_ras_gap'.$applyGapAsPer;
        $adherenceRASGap['details'] = @$adherenceRAS_details ?? [];
        $adherenceRASGap['title'] = 'Med Adherence - RAS '.$applyGapAsPer;
        $adherenceRASGap['comments'] = @$adherenceRAS_comments ?? [];
        $adherenceRASGap['gap_status'] = @$care_gaps['med_adherence_ras_gap'.$applyGapAsPer] ?? "";
        $adherenceRASGap['options'] = ['N/A', 'Changed PCP', 'Completed', 'Left Practice', 'Need To Schedule', 'Not An Established Patient', 'Not Found In PF', 'Not In Service', 'Pending Visit', 'Refused', 'Unable To Reach'];
        return $adherenceRASGap;
    }

    //Med Adherence - Statins img 11 new
    //med_adherence_statins_gap
    private function adherenceStatinsData ($val, $checkGapAsPer)
    {
        $applyGapAsPer = '';
        
        if($checkGapAsPer == 1) {
            $applyGapAsPer = "_insurance";
        }
        if ( !empty($provider) &&  $typeId == 1 && $provider ==  "allwell-001" ) {
            $val['care_gaps_data'] = $val['care_gaps_data_allwell_medicare'];
        } elseif ( !empty($provider) &&  $typeId == 1 && $provider ==  "uhc-001" ) {
            $val['care_gaps_data'] = $val['care_gaps_data_united_healthcare'];
        }
        $care_gaps = $val['care_gaps_data'];

        // For Care Gap Comments
        $adherenceStatins_comments =  array_values(array_filter($val['care_gaps_comment_data'], function($item) {
            return( $item['caregap_name'] == 'med_adherence_statins_gap' );
        }));
        rsort($adherenceStatins_comments);

        // For Care Gap Details
        $adherenceStatins_details =  array_values(array_filter($val['care_gaps_details'], function($item) {
            return( $item['caregap_name'] == 'med_adherence_statins_gap' );
        }));
        rsort($adherenceStatins_details);
        
        $adherenceStatinsGap['caregap_id'] = $val['care_gaps_data']['id'];
        $adherenceStatinsGap['db_column'] = 'med_adherence_statins_gap'.$applyGapAsPer;
        $adherenceStatinsGap['details'] = @$adherenceStatins_details ?? [];
        $adherenceStatinsGap['title'] = 'Med Adherence - Statins '.$applyGapAsPer;
        $adherenceStatinsGap['comments'] = @$adherenceStatins_comments ?? [];
        $adherenceStatinsGap['gap_status'] = @$care_gaps['med_adherence_statins_gap'.$applyGapAsPer] ?? "";
        $adherenceStatinsGap['options'] = ['N/A', 'Changed PCP', 'Completed', 'Left Practice', 'Need To Schedule', 'Not An Established Patient', 'Not Found In PF', 'Not In Service', 'Pending Visit', 'Refused', 'Unable To Reach'];
        return $adherenceStatinsGap;
    }

    //SPC - Statin Therapy for Patients with CVD img 12 new
    //spc_statin_therapy_cvd_gap
    private function sPCCVDData ($val, $checkGapAsPer)
    {
        $applyGapAsPer = '';
        
        if($checkGapAsPer == 1) {
            $applyGapAsPer = "_insurance";
        }
        if ( !empty($provider) &&  $typeId == 1 && $provider ==  "allwell-001" ) {
            $val['care_gaps_data'] = $val['care_gaps_data_allwell_medicare'];
        }
        $care_gaps = $val['care_gaps_data'];

        // For Care Gap Comments
        $sPCCVD_comments =  array_values(array_filter($val['care_gaps_comment_data'], function($item) {
            return( $item['caregap_name'] == 'spc_statin_therapy_cvd_gap' );
        }));
        rsort($sPCCVD_comments);

        // For Care Gap Details
        $sPCCVD_details =  array_values(array_filter($val['care_gaps_details'], function($item) {
            return( $item['caregap_name'] == 'spc_statin_therapy_cvd_gap' );
        }));
        rsort($sPCCVD_details);
        
        $sPCCVDGap['caregap_id'] = $val['care_gaps_data']['id'];
        $sPCCVDGap['db_column'] = 'spc_statin_therapy_cvd_gap'.$applyGapAsPer;
        $sPCCVDGap['details'] = @$sPCCVD_details ?? [];
        $sPCCVDGap['title'] = 'SPC - Statin Therapy for Patients with CVD '.$applyGapAsPer;
        $sPCCVDGap['comments'] = @$sPCCVD_comments ?? [];
        $sPCCVDGap['gap_status'] = @$care_gaps['spc_statin_therapy_cvd_gap'.$applyGapAsPer] ?? "";
        $sPCCVDGap['options'] = ['N/A', 'Changed PCP', 'Completed', 'Left Practice', 'Need To Schedule', 'Not An Established Patient', 'Not Found In PF', 'Not In Service', 'Pending Visit', 'Refused', 'Unable To Reach'];
        return $sPCCVDGap;
    }

    //TRC - Engagement After Discharge img 14
    // trc_eng_after_disc_gap
    private function tRCAfterDischargeData ($val, $checkGapAsPer)
    {
        $applyGapAsPer = '';
        
        if($checkGapAsPer == 1) {
            $applyGapAsPer = "_insurance";
        }
        if ( !empty($provider) &&  $typeId == 1 && $provider ==  "allwell-001" ) {
            $val['care_gaps_data'] = $val['care_gaps_data_allwell_medicare'];
        }
        $care_gaps = $val['care_gaps_data'];

        // For Care Gap Comments
        $tRCAD_comments =  array_values(array_filter($val['care_gaps_comment_data'], function($item) {
            return( $item['caregap_name'] == 'trc_eng_after_disc_gap' );
        }));
        rsort($tRCAD_comments);

        // For Care Gap Details
        $tRCAD_details =  array_values(array_filter($val['care_gaps_details'], function($item) {
            return( $item['caregap_name'] == 'trc_eng_after_disc_gap' );
        }));
        rsort($tRCAD_details);
        
        $tRCADGap['caregap_id'] = $val['care_gaps_data']['id'];
        $tRCADGap['db_column'] = 'trc_eng_after_disc_gap'.$applyGapAsPer;
        $tRCADGap['details'] = @$tRCAD_details ?? [];
        $tRCADGap['title'] = 'TRC - Engagement After Discharge '.$applyGapAsPer;
        $tRCADGap['comments'] = @$tRCAD_comments ?? [];
        $tRCADGap['gap_status'] = @$care_gaps['trc_eng_after_disc_gap'.$applyGapAsPer] ?? "";
        $tRCADGap['options'] = ['N/A', 'Changed PCP', 'Completed', 'Left Practice', 'Need To Schedule', 'Not An Established Patient', 'Not Found In PF', 'Not In Service', 'Pending Visit', 'Refused', 'Unable To Reach'];
        return $tRCADGap;
    }

    //TRC - Med Reconciliation Post Discharge img 15
    // trc_mr_post_disc_gap
    private function tRCPostDischarge ($val, $checkGapAsPer)
    {
        $applyGapAsPer = '';
        
        if($checkGapAsPer == 1) {
            $applyGapAsPer = "_insurance";
        }
        if ( !empty($provider) &&  $typeId == 1 && $provider ==  "allwell-001" ) {
            $val['care_gaps_data'] = $val['care_gaps_data_allwell_medicare'];
        }
        $care_gaps = $val['care_gaps_data'];

        // For Care Gap Comments
        $tRCPD_comments =  array_values(array_filter($val['care_gaps_comment_data'], function($item) {
            return( $item['caregap_name'] == 'trc_mr_post_disc_gap' );
        }));
        rsort($tRCPD_comments);

        // For Care Gap Details
        $tRCPD_details =  array_values(array_filter($val['care_gaps_details'], function($item) {
            return( $item['caregap_name'] == 'trc_mr_post_disc_gap' );
        }));
        rsort($tRCPD_details);
        
        $tRCPDGap['caregap_id'] = $val['care_gaps_data']['id'];
        $tRCPDGap['db_column'] = 'trc_mr_post_disc_gap'.$applyGapAsPer;
        $tRCPDGap['details'] = @$tRCAD_details ?? [];
        $tRCPDGap['title'] = 'TRC - Med Reconciliation Post Discharge '.$applyGapAsPer;
        $tRCPDGap['comments'] = @$tRCAD_comments ?? [];
        $tRCPDGap['gap_status'] = @$care_gaps['trc_mr_post_disc_gap'.$applyGapAsPer] ?? "";
        $tRCPDGap['options'] = ['N/A', 'Changed PCP', 'Completed', 'Left Practice', 'Need To Schedule', 'Not An Established Patient', 'Not Found In PF', 'Not In Service', 'Pending Visit', 'Refused', 'Unable To Reach'];
        return $tRCPDGap;
    }

    //KED - Kidney Health for Patients With Diabetes Current img 16
    // kidney_health_diabetes_gap
    private function kHDabetesCurrentData ($val, $checkGapAsPer)
    {
        $applyGapAsPer = '';
        
        if($checkGapAsPer == 1) {
            $applyGapAsPer = "_insurance";
        }
        if ( !empty($provider) &&  $typeId == 1 && $provider ==  "allwell-001" ) {
            $val['care_gaps_data'] = $val['care_gaps_data_allwell_medicare'];
        } elseif ( !empty($provider) &&  $typeId == 1 && $provider ==  "uhc-001" ) {
            $val['care_gaps_data'] = $val['care_gaps_data_united_healthcare'];
        }
        $care_gaps = $val['care_gaps_data'];

        // For Care Gap Comments
        $kHDC_comments =  array_values(array_filter($val['care_gaps_comment_data'], function($item) {
            return( $item['caregap_name'] == 'kidney_health_diabetes_gap' );
        }));
        rsort($kHDC_comments);

        // For Care Gap Details
        $kHDC_details =  array_values(array_filter($val['care_gaps_details'], function($item) {
            return( $item['caregap_name'] == 'kidney_health_diabetes_gap' );
        }));
        rsort($kHDC_details);
        
        $kHDCGap['caregap_id'] = $val['care_gaps_data']['id'];
        $kHDCGap['db_column'] = 'kidney_health_diabetes_gap'.$applyGapAsPer;
        $kHDCGap['details'] = @$kHDC_details ?? [];
        $kHDCGap['title'] = 'KED - Kidney Health for Patients With Diabetes Current '.$applyGapAsPer;
        $kHDCGap['comments'] = @$kHDC_comments ?? [];
        $kHDCGap['gap_status'] = @$care_gaps['kidney_health_diabetes_gap'.$applyGapAsPer] ?? "";
        $kHDCGap['options'] = ['N/A', 'Changed PCP', 'Completed', 'Left Practice', 'Need To Schedule', 'Not An Established Patient', 'Not Found In PF', 'Not In Service', 'Pending Visit', 'Refused', 'Unable To Reach'];
        return $kHDCGap;
    }
    // CCS - Cervical Cancer Screening
    // cervical_cancer_gap
    private function cervicalCancerData ($val, $checkGapAsPer)
    {
        $applyGapAsPer = '';
        
        if($checkGapAsPer == 1) {
            $applyGapAsPer = "_insurance";
        }
        if ( !empty($provider) &&  $typeId == 3 && $provider ==  "hcarz-001" ) {
            $val['care_gaps_data'] = $val['care_gaps_data_healthchoice_arizona'];
        }
        $care_gaps = $val['care_gaps_data'];

        // For Care Gap Comments
        $cc_comments =  array_values(array_filter($val['care_gaps_comment_data'], function($item) {
            return( $item['caregap_name'] == 'cervical_cancer_gap' );
        }));
        rsort($cc_comments);

        // For Care Gap Details
        $cc_details =  array_values(array_filter($val['care_gaps_details'], function($item) {
            return( $item['caregap_name'] == 'cervical_cancer_gap' );
        }));
        rsort($cc_details);
        
        $ccGap['caregap_id'] = $val['care_gaps_data']['id'];
        $ccGap['db_column'] = 'cervical_cancer_gap'.$applyGapAsPer;
        $ccGap['details'] = @$cc_details ?? [];
        $ccGap['title'] = 'CCS - Cervical Cancer Screening '.$applyGapAsPer;
        $ccGap['comments'] = @$cc_comments ?? [];
        $ccGap['gap_status'] = @$care_gaps['cervical_cancer_gap'.$applyGapAsPer] ?? "";
        $ccGap['options'] = ['N/A', 'Changed PCP', 'Completed', 'Left Practice', 'Need To Schedule', 'Not An Established Patient', 'Not Found In PF', 'Not In Service', 'Pending Visit', 'Refused', 'Unable To Reach'];
        return $ccGap;
    }
    // HDO - Use of Opioids at High Dosage
    // opioids_high_dosage_gap
    private function opioidsHighDosageData ($val, $checkGapAsPer)
    {
        $applyGapAsPer = '';
        
        if($checkGapAsPer == 1) {
            $applyGapAsPer = "_insurance";
        }
        if ( !empty($provider) &&  $typeId == 3 && $provider ==  "hcarz-001" ) {
            $val['care_gaps_data'] = $val['care_gaps_data_healthchoice_arizona'];
        }
        $care_gaps = $val['care_gaps_data'];

        // For Care Gap Comments
        $HDO_comments =  array_values(array_filter($val['care_gaps_comment_data'], function($item) {
            return( $item['caregap_name'] == 'opioids_high_dosage_gap' );
        }));
        rsort($HDO_comments);

        // For Care Gap Details
        $HDO_details =  array_values(array_filter($val['care_gaps_details'], function($item) {
            return( $item['caregap_name'] == 'opioids_high_dosage_gap' );
        }));
        rsort($HDO_details);
        
        $HDOGap['caregap_id'] = $val['care_gaps_data']['id'];
        $HDOGap['db_column'] = 'opioids_high_dosage_gap'.$applyGapAsPer;
        $HDOGap['details'] = @$HDO_details ?? [];
        $HDOGap['title'] = 'HDO - Use of Opioids at High Dosage '.$applyGapAsPer;
        $HDOGap['comments'] = @$HDO_comments ?? [];
        $HDOGap['gap_status'] = @$care_gaps['opioids_high_dosage_gap'.$applyGapAsPer] ?? "";
        $HDOGap['options'] = ['N/A', 'Changed PCP', 'Completed', 'Left Practice', 'Need To Schedule', 'Not An Established Patient', 'Not Found In PF', 'Not In Service', 'Pending Visit', 'Refused', 'Unable To Reach'];
        return $HDOGap;
    }
    // PPC1 - Timeliness of Prenatal Care
    // ppc1_gap
    private function timelinessPrenatalCare1Data ($val, $checkGapAsPer)
    {
        $applyGapAsPer = '';
        
        if($checkGapAsPer == 1) {
            $applyGapAsPer = "_insurance";
        }
        if ( !empty($provider) &&  $typeId == 3 && $provider ==  "hcarz-001" ) {
            $val['care_gaps_data'] = $val['care_gaps_data_healthchoice_arizona'];
        }
        $care_gaps = $val['care_gaps_data'];

        // For Care Gap Comments
        $PPC1_comments =  array_values(array_filter($val['care_gaps_comment_data'], function($item) {
            return( $item['caregap_name'] == 'ppc1_gap' );
        }));
        rsort($PPC1_comments);

        // For Care Gap Details
        $PPC1_details =  array_values(array_filter($val['care_gaps_details'], function($item) {
            return( $item['caregap_name'] == 'ppc1_gap' );
        }));
        rsort($PPC1_details);
        
        $PPC1Gap['caregap_id'] = $val['care_gaps_data']['id'];
        $PPC1Gap['db_column'] = 'ppc1_gap'.$applyGapAsPer;
        $PPC1Gap['details'] = @$PPC1_details ?? [];
        $PPC1Gap['title'] = 'PPC1 - Timeliness of Prenatal Care '.$applyGapAsPer;
        $PPC1Gap['comments'] = @$PPC1_comments ?? [];
        $PPC1Gap['gap_status'] = @$care_gaps['ppc1_gap'.$applyGapAsPer] ?? "";
        $PPC1Gap['options'] = ['N/A', 'Changed PCP', 'Completed', 'Left Practice', 'Need To Schedule', 'Not An Established Patient', 'Not Found In PF', 'Not In Service', 'Pending Visit', 'Refused', 'Unable To Reach'];
        return $PPC1Gap;
    }
    // PPC2 - Timeliness of Prenatal Care
    // ppc2_gap
    private function timelinessPrenatalCare2Data ($val, $checkGapAsPer)
    {
        $applyGapAsPer = '';
        
        if($checkGapAsPer == 1) {
            $applyGapAsPer = "_insurance";
        }
        if ( !empty($provider) &&  $typeId == 3 && $provider ==  "hcarz-001" ) {
            $val['care_gaps_data'] = $val['care_gaps_data_healthchoice_arizona'];
        }
        $care_gaps = $val['care_gaps_data'];

        // For Care Gap Comments
        $PPC2_comments =  array_values(array_filter($val['care_gaps_comment_data'], function($item) {
            return( $item['caregap_name'] == 'ppc2_gap' );
        }));
        rsort($PPC2_comments);

        // For Care Gap Details
        $PPC2_details =  array_values(array_filter($val['care_gaps_details'], function($item) {
            return( $item['caregap_name'] == 'ppc2_gap' );
        }));
        rsort($PPC2_details);
        
        $PPC2Gap['caregap_id'] = $val['care_gaps_data']['id'];
        $PPC2Gap['db_column'] = 'ppc2_gap'.$applyGapAsPer;
        $PPC2Gap['details'] = @$PPC2_details ?? [];
        $PPC2Gap['title'] = 'PPC2 - Timeliness of Prenatal Care '.$applyGapAsPer;
        $PPC2Gap['comments'] = @$PPC2_comments ?? [];
        $PPC2Gap['gap_status'] = @$care_gaps['ppc2_gap'.$applyGapAsPer] ?? "";
        $PPC2Gap['options'] = ['N/A', 'Changed PCP', 'Completed', 'Left Practice', 'Need To Schedule', 'Not An Established Patient', 'Not Found In PF', 'Not In Service', 'Pending Visit', 'Refused', 'Unable To Reach'];
        return $PPC2Gap;
    }
    // WCV - Well-Child Visits for Age 3-21
    // well_child_visits_gap
    private function wellChildVisitsData ($val, $checkGapAsPer)
    {
        $applyGapAsPer = '';
        
        if($checkGapAsPer == 1) {
            $applyGapAsPer = "_insurance";
        }
        if ( !empty($provider) &&  $typeId == 3 && $provider ==  "hcarz-001" ) {
            $val['care_gaps_data'] = $val['care_gaps_data_healthchoice_arizona'];
        }
        $care_gaps = $val['care_gaps_data'];

        // For Care Gap Comments
        $WCV_comments =  array_values(array_filter($val['care_gaps_comment_data'], function($item) {
            return( $item['caregap_name'] == 'well_child_visits_gap' );
        }));
        rsort($WCV_comments);

        // For Care Gap Details
        $WCV_details =  array_values(array_filter($val['care_gaps_details'], function($item) {
            return( $item['caregap_name'] == 'well_child_visits_gap' );
        }));
        rsort($WCV_details);
        
        $WCVGap['caregap_id'] = $val['care_gaps_data']['id'];
        $WCVGap['db_column'] = 'well_child_visits_gap'.$applyGapAsPer;
        $WCVGap['details'] = @$WCV_details ?? [];
        $WCVGap['title'] = 'WCV - Well-Child Visits for Age 3-21 '.$applyGapAsPer;
        $WCVGap['comments'] = @$WCV_comments ?? [];
        $WCVGap['gap_status'] = @$care_gaps['well_child_visits_gap'.$applyGapAsPer] ?? "";
        $WCVGap['options'] = ['N/A', 'Changed PCP', 'Completed', 'Left Practice', 'Need To Schedule', 'Not An Established Patient', 'Not Found In PF', 'Not In Service', 'Pending Visit', 'Refused', 'Unable To Reach'];
        return $WCVGap;
    }
    // Chlamydia Screening
    // chlamydia_gap
    private function chlamydiaData ($val, $checkGapAsPer)
    {
        $applyGapAsPer = '';
        
        if($checkGapAsPer == 1) {
            $applyGapAsPer = "_insurance";
        }
        if ( !empty($provider) &&  $typeId == 3 && $provider ==  "hcarz-001" ) {
            $val['care_gaps_data'] = $val['care_gaps_data_healthchoice_arizona'];
        }
        $care_gaps = $val['care_gaps_data'];

        // For Care Gap Comments
        $CS_comments =  array_values(array_filter($val['care_gaps_comment_data'], function($item) {
            return( $item['caregap_name'] == 'chlamydia_gap' );
        }));
        rsort($CS_comments);

        // For Care Gap Details
        $CS_details =  array_values(array_filter($val['care_gaps_details'], function($item) {
            return( $item['caregap_name'] == 'chlamydia_gap' );
        }));
        rsort($CS_details);
        
        $CSGap['caregap_id'] = $val['care_gaps_data']['id'];
        $CSGap['db_column'] = 'chlamydia_gap'.$applyGapAsPer;
        $CSGap['details'] = @$CS_details ?? [];
        $CSGap['title'] = 'Chlamydia Screening '.$applyGapAsPer;
        $CSGap['comments'] = @$CS_comments ?? [];
        $CSGap['gap_status'] = @$care_gaps['chlamydia_gap'.$applyGapAsPer] ?? "";
        $CSGap['options'] = ['N/A', 'Changed PCP', 'Completed', 'Left Practice', 'Need To Schedule', 'Not An Established Patient', 'Not Found In PF', 'Not In Service', 'Pending Visit', 'Refused', 'Unable To Reach'];
        return $CSGap;
    }
    // Follow-Up After Hospitalization for Mental Illness (FUH 30-Day)
    // fuh_30Day_gap
    private function fuh_30DayData ($val, $checkGapAsPer)
    {
        $applyGapAsPer = '';
        
        if($checkGapAsPer == 1) {
            $applyGapAsPer = "_insurance";
        }
        if ( !empty($provider) &&  $typeId == 3 && $provider ==  "hcarz-001" ) {
            $val['care_gaps_data'] = $val['care_gaps_data_healthchoice_arizona'];
        }
        $care_gaps = $val['care_gaps_data'];

        // For Care Gap Comments
        $FUH_30Day_comments =  array_values(array_filter($val['care_gaps_comment_data'], function($item) {
            return( $item['caregap_name'] == 'fuh_30Day_gap' );
        }));
        rsort($FUH_30Day_comments);

        // For Care Gap Details
        $FUH_30Day_details =  array_values(array_filter($val['care_gaps_details'], function($item) {
            return( $item['caregap_name'] == 'fuh_30Day_gap' );
        }));
        rsort($FUH_30Day_details);
        
        $FUH_30DayGap['caregap_id'] = $val['care_gaps_data']['id'];
        $FUH_30DayGap['db_column'] = 'fuh_30Day_gap'.$applyGapAsPer;
        $FUH_30DayGap['details'] = @$FUH_30Day_details ?? [];
        $FUH_30DayGap['title'] = 'Follow-Up After Hospitalization for Mental Illness (FUH 30-Day) '.$applyGapAsPer;
        $FUH_30DayGap['comments'] = @$FUH_30Day_comments ?? [];
        $FUH_30DayGap['gap_status'] = @$care_gaps['fuh_30Day_gap'.$applyGapAsPer] ?? "";
        $FUH_30DayGap['options'] = ['N/A', 'Changed PCP', 'Completed', 'Left Practice', 'Need To Schedule', 'Not An Established Patient', 'Not Found In PF', 'Not In Service', 'Pending Visit', 'Refused', 'Unable To Reach'];
        return $FUH_30DayGap;
    }
    // Follow-Up After Hospitalization for Mental Illness (FUH 7-Day)
    // fuh_7Day_gap
    private function fuh_7DayData ($val, $checkGapAsPer)
    {
        $applyGapAsPer = '';
        
        if($checkGapAsPer == 1) {
            $applyGapAsPer = "_insurance";
        }
        if ( !empty($provider) &&  $typeId == 3 && $provider ==  "hcarz-001" ) {
            $val['care_gaps_data'] = $val['care_gaps_data_healthchoice_arizona'];
        }
        $care_gaps = $val['care_gaps_data'];

        // For Care Gap Comments
        $FUH_7Day_comments =  array_values(array_filter($val['care_gaps_comment_data'], function($item) {
            return( $item['caregap_name'] == 'fuh_7Day_gap' );
        }));
        rsort($FUH_7Day_comments);

        // For Care Gap Details
        $FUH_7Day_details =  array_values(array_filter($val['care_gaps_details'], function($item) {
            return( $item['caregap_name'] == 'fuh_7Day_gap' );
        }));
        rsort($FUH_7Day_details);
        
        $FUH_7DayGap['caregap_id'] = $val['care_gaps_data']['id'];
        $FUH_7DayGap['db_column'] = 'fuh_7Day_gap'.$applyGapAsPer;
        $FUH_7DayGap['details'] = @$FUH_7Day_details ?? [];
        $FUH_7DayGap['title'] = 'Follow-Up After Hospitalization for Mental Illness (FUH 7-Day) '.$applyGapAsPer;
        $FUH_7DayGap['comments'] = @$FUH_7Day_comments ?? [];
        $FUH_7DayGap['gap_status'] = @$care_gaps['fuh_7Day_gap'.$applyGapAsPer] ?? "";
        $FUH_7DayGap['options'] = ['N/A', 'Changed PCP', 'Completed', 'Left Practice', 'Need To Schedule', 'Not An Established Patient', 'Not Found In PF', 'Not In Service', 'Pending Visit', 'Refused', 'Unable To Reach'];
        return $FUH_7DayGap;
    }

    // UHC
    //DMC10-Care for Older Adults - Functional Status Assessment .
    //adults_fun_status_gap
    private function AdultsFunStatus ($val, $checkGapAsPer)
    {
        $applyGapAsPer = '';
        
        if($checkGapAsPer == 1) {
            $applyGapAsPer = "_insurance";
        }
        if ( !empty($provider) &&  $typeId == 1 && $provider ==  "uhc-001" ) {
            $val['care_gaps_data'] = $val['care_gaps_data_united_healthcare'];
        }
        $care_gaps = $val['care_gaps_data'];

        // For Care Gap Comments
        $AdultsFunStatus_comments =  array_values(array_filter($val['care_gaps_comment_data'], function($item) {
            return( $item['caregap_name'] == 'adults_fun_status_gap' );
        }));
        rsort($AdultsFunStatus_comments);

        // For Care Gap Details
        $AdultsFunStatus_details =  array_values(array_filter($val['care_gaps_details'], function($item) {
            return( $item['caregap_name'] == 'adults_fun_status_gap' );
        }));
        rsort($AdultsFunStatus_details);
        
        $AdultsFunStatusGap['caregap_id'] = $val['care_gaps_data']['id'];
        $AdultsFunStatusGap['db_column'] = 'adults_fun_status_gap'.$applyGapAsPer;
        $AdultsFunStatusGap['details'] = @$AdultsFunStatus_details ?? [];
        $AdultsFunStatusGap['title'] = 'DMC10 - Care for Older Adults - Functional Status Assessment '.$applyGapAsPer;
        $AdultsFunStatusGap['comments'] = @$AdultsFunStatus_comments ?? [];
        $AdultsFunStatusGap['gap_status'] = @$care_gaps['adults_fun_status_gap'.$applyGapAsPer] ?? "";
        $AdultsFunStatusGap['options'] = ['N/A', 'Changed PCP', 'Completed', 'Left Practice', 'Need To Schedule', 'Not An Established Patient', 'Not Found In PF', 'Not In Service', 'Pending Visit', 'Refused', 'Unable To Reach'];
        return $AdultsFunStatusGap;
    }
    //D08-Med Ad. For Diabetes Meds Current Year Status
    //med_adherence_diabetes_gap
    private function MedAadherenceDiabetes ($val, $checkGapAsPer)
    {
        $applyGapAsPer = '';
        
        if($checkGapAsPer == 1) {
            $applyGapAsPer = "_insurance";
        }
        if ( !empty($provider) &&  $typeId == 1 && $provider ==  "uhc-001" ) {
            $val['care_gaps_data'] = $val['care_gaps_data_united_healthcare'];
        }
        $care_gaps = $val['care_gaps_data'];

        // For Care Gap Comments
        $MedAadherenceDiabetes_comments =  array_values(array_filter($val['care_gaps_comment_data'], function($item) {
            return( $item['caregap_name'] == 'med_adherence_diabetes_gap' );
        }));
        rsort($MedAadherenceDiabetes_comments);
      
        // For Care Gap Details
        $MedAadherenceDiabetes_details =  array_values(array_filter($val['care_gaps_details'], function($item) {
            return( $item['caregap_name'] == 'med_adherence_diabetes_gap' );
        }));
        rsort($MedAadherenceDiabetes_details);
        
        $MedAadherenceDiabetesGap['caregap_id'] = $val['care_gaps_data']['id'];
        $MedAadherenceDiabetesGap['db_column'] = 'med_adherence_diabetes_gap'.$applyGapAsPer;
        $MedAadherenceDiabetesGap['details'] = @$MedAadherenceDiabetes_details ?? [];
        $MedAadherenceDiabetesGap['title'] = 'D08-Med Ad. For Diabetes Meds Current Year Status '.$applyGapAsPer;
        $MedAadherenceDiabetesGap['comments'] = @$MedAadherenceDiabetes_comments ?? [];
        $MedAadherenceDiabetesGap['gap_status'] = @$care_gaps['med_adherence_diabetes_gap'.$applyGapAsPer] ?? "";
        $MedAadherenceDiabetesGap['options'] = ['N/A', 'Changed PCP', 'Completed', 'Left Practice', 'Need To Schedule', 'Not An Established Patient', 'Not Found In PF', 'Not In Service', 'Pending Visit', 'Refused', 'Unable To Reach'];
        return $MedAadherenceDiabetesGap;
    }
    //D11-MTM CMR Current Year Status
    // mtm_cmr_gap
    private function MTM_CMR ($val, $checkGapAsPer)
    {
        $applyGapAsPer = '';
        
        if($checkGapAsPer == 1) {
            $applyGapAsPer = "_insurance";
        }
        if ( !empty($provider) &&  $typeId == 1 && $provider ==  "uhc-001" ) {
            $val['care_gaps_data'] = $val['care_gaps_data_united_healthcare'];
        }
        $care_gaps = $val['care_gaps_data'];

        // For Care Gap Comments
        $MTM_CMR_comments =  array_values(array_filter($val['care_gaps_comment_data'], function($item) {
            return( $item['caregap_name'] == 'mtm_cmr_gap' );
        }));
        rsort($MTM_CMR_comments);

        // For Care Gap Details
        $MTM_CMR_details =  array_values(array_filter($val['care_gaps_details'], function($item) {
            return( $item['caregap_name'] == 'mtm_cmr_gap' );
        }));
        rsort($MTM_CMR_details);
        
        $MTM_CMRGap['caregap_id'] = $val['care_gaps_data']['id'];
        $MTM_CMRGap['db_column'] = 'mtm_cmr_gap'.$applyGapAsPer;
        $MTM_CMRGap['details'] = @$MTM_CMR_details ?? [];
        $MTM_CMRGap['title'] = 'MTM CMR Current Year Status '.$applyGapAsPer;
        $MTM_CMRGap['comments'] = @$MTM_CMR_comments ?? [];
        $MTM_CMRGap['gap_status'] = @$care_gaps['mtm_cmr_gap'.$applyGapAsPer] ?? "";
        $MTM_CMRGap['options'] = ['N/A', 'Changed PCP', 'Completed', 'Left Practice', 'Need To Schedule', 'Not An Established Patient', 'Not Found In PF', 'Not In Service', 'Pending Visit', 'Refused', 'Unable To Reach'];
        return $MTM_CMRGap;
    }

    public function createCareGap($existingRecord, $newInsuranceId, $newDoctorId) 
    {
        $exProvider = @$existingRecord['insurance']['provider'] ?? "";
        $patientId = @$existingRecord['id'];
        $patientId = @$existingRecord['id'];
        $patientId = @$existingRecord['id'];
        $filterYear = $now = Carbon::now()->year;
        $newInusrance = Insurances::find($newInsuranceId);
        $newProvider = @$newInusrance->provider ?? "";

        $exGapTable = "";
        switch ($exProvider) {
            case 'hcpw-001':
                $exGapTable = new CareGaps;
                break;
            case 'hum-001':
                $exGapTable = new HumanaCareGaps;
                break;
            case 'med-arz-001':
                $exGapTable = new MedicareArizonaCareGaps;
                break;
            case 'aet-001':
                $exGapTable = new AetnaMedicareCareGaps;
                break;
            case 'allwell-001':
                $exGapTable = new AllwellMedicareCareGaps;
                break;
            case 'hcarz-001':
                $exGapTable = new HealthchoiceArizonaCareGaps;
                break;
            case 'uhc-001':
                $exGapTable = new UnitedHealthcareCareGaps;
                break;

            default:
                break;
        }

        if (!empty($newInsuranceId)) {
            // Creating EmptyGaps for new Insurance
            $newCareGaps = [];
            $newTable = "";
            switch ($newProvider) {
                case 'hcpw-001':
                    $newTable = new CareGaps;
                    $newCareGaps = $this->hPCEmptyGaps($existingRecord);
                    break;
                case 'hum-001':
                    $newTable = new HumanaCareGaps;
                    $newCareGaps = $this->humanaEmptyGaps($existingRecord);
                    break;
                case 'med-arz-001':
                    $newTable = new MedicareArizonaCareGaps;
                    $newCareGaps = $this->MedicareArizonaEmptyGaps($existingRecord);
                    break;
                case 'aet-001':
                    $newTable = new AetnaMedicareCareGaps;
                    $newCareGaps = $this->AetnaMedicareEmptyGaps($existingRecord);
                    break;
                case 'allwell-001':
                    $newTable = new AllwellMedicareCareGaps;
                    $newCareGaps = $this->AllwellMedicareEmptyGaps($existingRecord);
                    break;
                case 'hcarz-001':
                    $newTable = new HealthchoiceArizonaCareGaps;
                    $newCareGaps = $this->HealthChoiceArizonaEmptyGaps($existingRecord);
                    break;
                case 'uhc-001':
                    $newTable = new UnitedHealthcareCareGaps;
                    $newCareGaps = $this->UnitedHealthCareEmptyGaps($existingRecord);
                    break;

                default:
                    break;
            }


            if (!empty($exProvider)) {
                $prevCareGapData = $exGapTable::thisYearGaps('2023')->where('patient_id', $patientId)->first();

                if (!empty($prevCareGapData)) {

                    $createRow = [];

                    foreach ($newCareGaps as $key => $value) {
                        $dbCol = $value['db_column'];
                        $dbColInsurance = $value['db_column'].'_insurance';

                        if (isset($prevCareGapData->$dbCol)) {
                            $createRow[$dbCol] = $prevCareGapData->$dbCol;
                            // $value['gap_status'] = $prevCareGapData->$dbCol;
                            // $newCareGaps[$key] = $value;
                        } else {
                            $createRow[$dbCol] = 'N/A';
                        }

                        if (isset($prevCareGapData->$dbColInsurance)) {
                            $createRow[$dbColInsurance] = $prevCareGapData->$dbColInsurance;
                        } else {
                            $createRow[$dbColInsurance] = 'N/A';
                        }
                    }

                    $createRow['patient_id'] = $patientId;
                    $createRow['insurance_id'] = $existingRecord['insurance_id'];
                    $createRow['doctor_id'] = $existingRecord['doctor_id'];
                    $createRow['clinic_id'] = $existingRecord['clinic_id'];
                    $createRow['created_user'] = Auth::user()->id;

                    if (!empty($createRow)) {
                        $newTable::create($createRow);
                    }
                }
            }
        } else if (!empty($newDoctorId)) {
            $exGapTable::update('doctor_id', $newDoctorId)->where('patient_id', $patientId);
        }
    }

    
    private function hPCEmptyGaps($patientData)
    {
        // 1
        $breast_cancer['db_column'] = 'breast_cancer_gap';
        $breast_cancer['title'] = 'Breast Cancer Screening (BCS) Healthchoice Pathways';
        $breast_cancer['comments'] = [];
        $breast_cancer['details'] = [];
        $breast_cancer['gap_status'] =  "";
        $breast_cancer['options'] = ['N/A', 'Non-Compliant', 'Compliant', 'Bilateral Mastectomy', 'Patient Refused', 'Refusal Reviewed', 'Scheduled'];
        $care_gaps_array[] = $breast_cancer;

        //2
        $colon_cancer['db_column'] = 'colorectal_cancer_gap';
        $colon_cancer['title'] = 'Colorectal Cancer Screening (COL) Healthchoice Pathways';
        $colon_cancer['comments'] = [];
        $colon_cancer['details'] = [];
        $colon_cancer['gap_status'] = "";
        $colon_cancer['options'] = ['N/A', 'Non-Compliant', 'Compliant', 'Total Colectomy', 'Colorectal Cancer', 'Patient Refused', 'Refusal Reviewed', 'Scheduled'];
        $care_gaps_array[] =  $colon_cancer;

        //3
        $blood_pressure['db_column'] = 'high_bp_gap';
        $blood_pressure['title'] = 'Controlling Blood Pressure (CBP)';
        $blood_pressure['comments'] = [];
        $blood_pressure['details'] = [];
        $blood_pressure['gap_status'] =  "";
        $blood_pressure['options'] = ['N/A', 'Non-Compliant', 'Compliant', 'Patient Refused', 'Refusal Reviewed', 'Scheduled'];
        $care_gaps_array[] = $blood_pressure;

        //4
        $hba1c_control['db_column'] = 'hba1c_poor_gap';
        $hba1c_control['title'] = 'Diabetes Care - Blood Sugar Poor Control (>9%)';
        $hba1c_control['comments'] = [];
        $hba1c_control['details'] = [];
        $hba1c_control['gap_status'] = "";
        $hba1c_control['options'] = ['N/A', 'Non-Compliant', 'Compliant', 'Not Reported', 'Diagnosis Incorrect', 'Patient Refused', 'Refusal Reviewed', 'Scheduled'];
        $care_gaps_array[] = $hba1c_control;

        //5
        $eye_exam['db_column'] = 'eye_exam_gap';
        $eye_exam['title'] = 'Eye Exam for Patients With Diabetes - Eye Exam';
        $eye_exam['db_column'] = 'eye_exam_gap';
        $eye_exam['details'] = [];
        $eye_exam['comments'] = [];
        $eye_exam['gap_status'] = "";
        $eye_exam['options'] = ['N/A', 'Non-Compliant', 'Compliant', 'Patient Refused', 'Refusal Reviewed', 'Scheduled'];
        $care_gaps_array[] = $eye_exam;

        //6
        $statin_therapy['db_column'] = 'statin_therapy_gap';
        $statin_therapy['details'] = [];
        $statin_therapy['title'] = 'Statin Therapy for Patients with Cardiovascular Disease (SPC)';
        $statin_therapy['comments'] = [];
        $statin_therapy['gap_status'] = "";
        $statin_therapy['options'] = ['N/A', 'Non-Compliant', 'Compliant', 'Patient Refused', 'Refusal Reviewed', 'Scheduled'];
        $care_gaps_array[] = $statin_therapy;

        //7
        $osteoporosis_gap['db_column'] = 'osteoporosis_mgmt_gap';
        $osteoporosis_gap['details'] = [];
        $osteoporosis_gap['title'] = 'Osteoporosis Mgmt in Women who had Fracture (OMW)';
        $osteoporosis_gap['comments'] = [];
        $osteoporosis_gap['gap_status'] = "";
        $osteoporosis_gap['options'] = ['N/A', 'Non-Compliant', 'Compliant', 'Patient Refused', 'Refusal Reviewed', 'Scheduled'];
        $care_gaps_array[] = $osteoporosis_gap;

        // 8
        $adults_med_gap['db_column'] = 'adults_medic_gap';
        $adults_med_gap['details'] = [];
        $adults_med_gap['title'] = 'Care for Older Adults - Medication Review (COA2)';
        $adults_med_gap['comments'] = [];
        $adults_med_gap['gap_status'] =  "";
        $adults_med_gap['options'] = ['N/A', 'Non-Compliant', 'Compliant', 'Not Reported', 'Diagnosis Incorrect', 'Patient Refused', 'Refusal Reviewed', 'Scheduled'];
        $care_gaps_array[] = $adults_med_gap;

        // 9
        $adults_pain_gap['db_column'] = 'pain_screening_gap';
        $adults_pain_gap['details'] = [];
        $adults_pain_gap['title'] = 'Care for Older Adults Pain-Screening (COA4)';
        $adults_pain_gap['comments'] = [];
        $adults_pain_gap['gap_status'] =  "";
        $adults_pain_gap['options'] = ['N/A', 'Non-Compliant', 'Compliant', 'Not Reported', 'Diagnosis Incorrect', 'Patient Refused', 'Refusal Reviewed', 'Scheduled'];
        $care_gaps_array[] = $adults_pain_gap;

        //10
        $poast_disc_gap['db_column'] = 'post_disc_gap';
        $poast_disc_gap['details'] = [];
        $poast_disc_gap['title'] = 'Transitions of Care-Medication Reconciliation Post-Discharge (TCRM)';
        $poast_disc_gap['comments'] = [];
        $poast_disc_gap['gap_status'] = "";
        $poast_disc_gap['options'] = ['N/A', 'Non-Compliant', 'Compliant', 'Not Reported', 'Diagnosis Incorrect', 'Patient Refused', 'Refusal Reviewed', 'Scheduled'];
        $care_gaps_array[] = $poast_disc_gap;

        //11
        $after_inp_gap['db_column'] = 'after_inp_disc_gap';
        $after_inp_gap['details'] = [];
        $after_inp_gap['title'] = 'Transitions of Care-Patient Engagement After Inpatient Discharge (TRCE)';
        $after_inp_gap['comments'] = [];
        $after_inp_gap['gap_status'] = "";
        $after_inp_gap['options'] = ['N/A', 'Non-Compliant', 'Compliant', 'Not Reported', 'Diagnosis Incorrect', 'Patient Refused', 'Refusal Reviewed', 'Scheduled'];
        $care_gaps_array[] = $after_inp_gap;

        //12
        $adults_func['db_column'] = 'adults_func_gap';
        $adults_func['details'] = [];
        $adults_func['title'] = 'Care for Older Adults Functional Status';
        $adults_func['comments'] = [];
        $adults_func['gap_status'] = "";
        $adults_func['options'] = ['N/A', 'Non-Compliant', 'Compliant', 'Not Reported', 'Diagnosis Incorrect', 'Patient Refused', 'Refusal Reviewed', 'Scheduled'];
        $care_gaps_array[] = $adults_func;

        //13
        $awvGap = [];
        $gapDetails = @$patientData['care_gaps_details'] ?? [];

        $awvDetails = array_values(array_filter($gapDetails, function($item) {
            return( $item['caregap_name'] == 'awv_gap' );
        }));
        rsort($awvDetails);

        if (empty($awvDetails)) {
            $awvGap['db_column'] = 'awv_gap';
            $awvGap['details'] = [];
            $awvGap['title'] = 'Annual Wellness Visit (AWV)';
            $awvGap['comments'] = [];
            $awvGap['gap_status'] = "";
            $awvGap['options'] = ['N/A', 'Changed PCP', 'Completed', 'Left Practice', 'Need To Schedule', 'Not An Established Patient', 'Not Found In PF', 'Not In Service', 'Pending Visit', 'Refused', 'Unable To Reach'];
        } else {
            $awvGap['caregap_id'] = $awvDetails['0']['caregap_id'];
            $awvGap['comments'] = [];
            $awvGap['db_column'] = $awvDetails['0']['caregap_name'];
            $awvGap['details'] = $awvDetails['0'];
            $awvGap['gap_status'] = $awvDetails['0']['status'];
            $awvGap['options'] = ['N/A', 'Changed PCP', 'Completed', 'Left Practice', 'Need To Schedule', 'Not An Established Patient', 'Not Found In PF', 'Not In Service', 'Pending Visit', 'Refused', 'Unable To Reach'];
        }

        $care_gaps_array[] = $awvGap;
        return $care_gaps_array;
    }

    private function humanaEmptyGaps($patientData)
    {
        // 1
        $breast_cancer['db_column'] = 'breast_cancer_gap';
        $breast_cancer['title'] = 'Breast Cancer Screening (BCS) Humana';
        $breast_cancer['comments'] = [];
        $breast_cancer['details'] = [];
        $breast_cancer['gap_status'] =  "";
        $breast_cancer['options'] = ['N/A', 'Non-Compliant', 'Compliant', 'Bilateral Mastectomy', 'Patient Refused', 'Refusal Reviewed', 'Scheduled'];
        $care_gaps_array[] = $breast_cancer;

        //2
        $colon_cancer['db_column'] = 'colorectal_cancer_gap';
        $colon_cancer['title'] = 'Colorectal Cancer Screening (COL) Humana';
        $colon_cancer['comments'] = [];
        $colon_cancer['details'] = [];
        $colon_cancer['gap_status'] =  "";
        $colon_cancer['options'] = ['N/A', 'Non-Compliant', 'Compliant', 'Total Colectomy', 'Colorectal Cancer', 'Patient Refused', 'Refusal Reviewed', 'Scheduled'];
        $care_gaps_array[] =  $colon_cancer;

        //3
        $blood_pressure['db_column'] = 'high_bp_gap';
        $blood_pressure['title'] = 'Controlling Blood Pressure (CBP)';
        $blood_pressure['comments'] = [];
        $blood_pressure['details'] = [];
        $blood_pressure['gap_status'] =  "";
        $blood_pressure['options'] = ['N/A', 'Non-Compliant', 'Compliant', 'Patient Refused', 'Refusal Reviewed', 'Scheduled'];
        $care_gaps_array[] = $blood_pressure;

        //4
        $hba1c_control['db_column'] = 'hba1c_poor_gap';
        $hba1c_control['title'] = 'Diabetes Care - Blood Sugar Poor Control (>9%)';
        $hba1c_control['comments'] = [];
        $hba1c_control['details'] =  [];
        $hba1c_control['gap_status'] = "";
        $hba1c_control['options'] = ['N/A', 'Non-Compliant', 'Compliant', 'Not Reported', 'Diagnosis Incorrect', 'Patient Refused', 'Refusal Reviewed', 'Scheduled'];
        $care_gaps_array[] = $hba1c_control;

        //5
        $eye_exam['db_column'] = 'eye_exam_gap';
        $eye_exam['title'] = 'Eye Exam for Patients With Diabetes - Eye Exam';
        $eye_exam['db_column'] = 'eye_exam_gap';
        $eye_exam['details'] = [];
        $eye_exam['comments'] = [];
        $eye_exam['gap_status'] = "";
        $eye_exam['options'] = ['N/A', 'Non-Compliant', 'Compliant', 'Patient Refused', 'Refusal Reviewed', 'Scheduled'];
        $care_gaps_array[] = $eye_exam;

        //6
        $poast_disc_gap['db_column'] = 'post_disc_gap';
        $poast_disc_gap['details'] =  [];
        $poast_disc_gap['title'] = 'Transitions of Care-Medication Reconciliation Post-Discharge (TCRM)';
        $poast_disc_gap['comments'] =  [];
        $poast_disc_gap['gap_status'] = "";
        $poast_disc_gap['options'] = ['N/A', 'Non-Compliant', 'Compliant', 'Not Reported', 'Diagnosis Incorrect', 'Patient Refused', 'Refusal Reviewed', 'Scheduled'];
        $care_gaps_array[] = $poast_disc_gap;

        //7
        $after_inp_gap['db_column'] = 'after_inp_disc_gap';
        $after_inp_gap['details'] =  [];
        $after_inp_gap['title'] = 'Transitions of Care-Patient Engagement After Inpatient Discharge (TRCE)';
        $after_inp_gap['comments'] =  [];
        $after_inp_gap['gap_status'] = "";
        $after_inp_gap['options'] = ['N/A', 'Non-Compliant', 'Compliant', 'Not Reported', 'Diagnosis Incorrect', 'Patient Refused', 'Refusal Reviewed', 'Scheduled'];
        $care_gaps_array[] = $after_inp_gap;

        //8
        $fmcGap['db_column'] = 'faed_visit_gap';
        $fmcGap['details'] = [];
        $fmcGap['title'] = 'Follow-Up After Emergency Department Visit for MCC (FMC)';
        $fmcGap['comments'] = [];
        $fmcGap['gap_status'] =  "";
        $fmcGap['options'] = ['N/A', 'Changed PCP', 'Completed', 'Left Practice', 'Need To Schedule', 'Not An Established Patient', 'Not Found In PF', 'Not In Service', 'Pending Visit', 'Refused', 'Unable To Reach'];
        $care_gaps_array[] = $fmcGap;

        //9
        $omwGap['db_column'] = 'omw_fracture_gap';
        $omwGap['details'] =  [];
        $omwGap['title'] = 'Osteoporosis Management in Women Who Had a Fracture (OMW)';
        $omwGap['comments'] =  [];
        $omwGap['gap_status'] =  "";
        $omwGap['options'] = ['N/A', 'Changed PCP', 'Completed', 'Left Practice', 'Need To Schedule', 'Not An Established Patient', 'Not Found In PF', 'Not In Service', 'Pending Visit', 'Refused', 'Unable To Reach'];
        $care_gaps_array[] = $omwGap;

        //10
        $pcrGap['db_column'] = 'pc_readmissions_gap';
        $pcrGap['details'] = [];
        $pcrGap['title'] = 'Plan All-Cause Readmissions (PCR)';
        $pcrGap['comments'] = [];
        $pcrGap['gap_status'] =  "";
        $pcrGap['options'] = ['N/A', 'Changed PCP', 'Completed', 'Left Practice', 'Need To Schedule', 'Not An Established Patient', 'Not Found In PF', 'Not In Service', 'Pending Visit', 'Refused', 'Unable To Reach'];
        $care_gaps_array[] = $pcrGap;

        //11
        $spcStatinGap['db_column'] = 'spc_disease_gap';
        $spcStatinGap['details'] = [];
        $spcStatinGap['title'] = 'Statin Therapy for Patients with Cardiovascular Disease: Received Statin Therapy (SPC_STATIN)';
        $spcStatinGap['comments'] = [];
        $spcStatinGap['gap_status'] = "";
        $spcStatinGap['options'] = ['N/A', 'Changed PCP', 'Completed', 'Left Practice', 'Need To Schedule', 'Not An Established Patient', 'Not Found In PF', 'Not In Service', 'Pending Visit', 'Refused', 'Unable To Reach'];
        $care_gaps_array[] = $spcStatinGap;

        //12
        $adhStatinGap['db_column'] = 'ma_cholesterol_gap';
        $adhStatinGap['details'] = [];
        $adhStatinGap['title'] = 'Medication Adherence for Cholesterol (Statins): Statins (ADH_STATIN)';
        $adhStatinGap['comments'] = [];
        $adhStatinGap['gap_status'] = "";
        $adhStatinGap['options'] = ['N/A', 'Changed PCP', 'Completed', 'Left Practice', 'Need To Schedule', 'Not An Established Patient', 'Not Found In PF', 'Not In Service', 'Pending Visit', 'Refused', 'Unable To Reach'];
        $care_gaps_array[] = $adhStatinGap;

        //13
        $adhDiabGap['db_column'] = 'mad_medications_gap';
        $adhDiabGap['details'] = [];
        $adhDiabGap['title'] = 'Medication Adherence for Diabetes Medications: Diabetes Medications (ADH_DIAB)';
        $adhDiabGap['comments'] = [];
        $adhDiabGap['gap_status'] = "";
        $adhDiabGap['options'] = ['N/A', 'Changed PCP', 'Completed', 'Left Practice', 'Need To Schedule', 'Not An Established Patient', 'Not Found In PF', 'Not In Service', 'Pending Visit', 'Refused', 'Unable To Reach'];
        $care_gaps_array[] = $adhDiabGap;

        //14
        $adhAceGap['db_column'] = 'ma_hypertension_gap';
        $adhAceGap['details'] =  [];
        $adhAceGap['title'] = 'Medication Adherence for Hypertension (ACE or ARB): ACE (ADH_ACE)';
        $adhAceGap['comments'] = [];
        $adhAceGap['gap_status'] =  "";
        $adhAceGap['options'] = ['N/A', 'Changed PCP', 'Completed', 'Left Practice', 'Need To Schedule', 'Not An Established Patient', 'Not Found In PF', 'Not In Service', 'Pending Visit', 'Refused', 'Unable To Reach'];
        $care_gaps_array[] = $adhAceGap;

        //15
        $supdGap['db_column'] = 'sup_diabetes_gap';
        $supdGap['details'] = [];
        $supdGap['title'] = 'Statin Use in Persons with Diabetes (SUPD)';
        $supdGap['comments'] = [];
        $supdGap['gap_status'] = "";
        $supdGap['options'] = ['N/A', 'Changed PCP', 'Completed', 'Left Practice', 'Need To Schedule', 'Not An Established Patient', 'Not Found In PF', 'Not In Service', 'Pending Visit', 'Refused', 'Unable To Reach'];
        $care_gaps_array[] = $supdGap;

        //16

        $awvGap = [];
        $gapDetails = @$patientData['care_gaps_details'] ?? [];

        $awvDetails = array_values(array_filter($gapDetails, function($item) {
            return( $item['caregap_name'] == 'awv_gap' );
        }));
        rsort($awvDetails);

        if (empty($awvDetails)) {
            $awvGap['db_column'] = 'awv_gap';
            $awvGap['details'] = [];
            $awvGap['title'] = 'Annual Wellness Visit (AWV)';
            $awvGap['comments'] = [];
            $awvGap['gap_status'] = "";
            $awvGap['options'] = ['N/A', 'Changed PCP', 'Completed', 'Left Practice', 'Need To Schedule', 'Not An Established Patient', 'Not Found In PF', 'Not In Service', 'Pending Visit', 'Refused', 'Unable To Reach'];
        } else {
            $awvGap['caregap_id'] = $awvDetails['0']['caregap_id'];
            $awvGap['comments'] = [];
            $awvGap['db_column'] = $awvDetails['0']['caregap_name'];
            $awvGap['details'] = $awvDetails['0'];
            $awvGap['gap_status'] = $awvDetails['0']['status'];
            $awvGap['options'] = ['N/A', 'Changed PCP', 'Completed', 'Left Practice', 'Need To Schedule', 'Not An Established Patient', 'Not Found In PF', 'Not In Service', 'Pending Visit', 'Refused', 'Unable To Reach'];
        }

        $care_gaps_array[] = $awvGap;

        return $care_gaps_array;
    }

    private function MedicareArizonaEmptyGaps($patientData)
    {
        // 1
        $breast_cancer['db_column'] = 'breast_cancer_gap';
        $breast_cancer['title'] = 'Breast Cancer Screening (BCS)';
        $breast_cancer['comments'] = [];
        $breast_cancer['details'] = [];
        $breast_cancer['gap_status'] =  "";
        $breast_cancer['options'] = ['N/A', 'Non-Compliant', 'Compliant', 'Bilateral Mastectomy', 'Patient Refused', 'Refusal Reviewed', 'Scheduled'];
        $care_gaps_array[] = $breast_cancer;

        //2
        $colon_cancer['db_column'] = 'colorectal_cancer_gap';
        $colon_cancer['title'] = 'Colorectal Cancer Screening (COL)';
        $colon_cancer['comments'] = [];
        $colon_cancer['details'] = [];
        $colon_cancer['gap_status'] =  "";
        $colon_cancer['options'] = ['N/A', 'Non-Compliant', 'Compliant', 'Total Colectomy', 'Colorectal Cancer', 'Patient Refused', 'Refusal Reviewed', 'Scheduled'];
        $care_gaps_array[] =  $colon_cancer;

        //3
        $blood_pressure['db_column'] = 'high_bp_gap';
        $blood_pressure['title'] = 'Controlling Blood Pressure (CBP)';
        $blood_pressure['comments'] = [];
        $blood_pressure['details'] = [];
        $blood_pressure['gap_status'] =  "";
        $blood_pressure['options'] = ['N/A', 'Non-Compliant', 'Compliant', 'Patient Refused', 'Refusal Reviewed', 'Scheduled'];
        $care_gaps_array[] = $blood_pressure;

        //4
        $hba1c_control['db_column'] = 'hba1c_gap';
        $hba1c_control['title'] = 'Diabetes Care - Blood Sugar Control (HBA1C)';
        $hba1c_control['comments'] = [];
        $hba1c_control['details'] =  [];
        $hba1c_control['gap_status'] = "";
        $hba1c_control['options'] = ['N/A', 'Non-Compliant', 'Compliant', 'Not Reported', 'Diagnosis Incorrect', 'Patient Refused', 'Refusal Reviewed', 'Scheduled'];
        $care_gaps_array[] = $hba1c_control;

        //5
        $awvGap = [];
        $gapDetails = @$patientData['care_gaps_details'] ?? [];

        $awvDetails = array_values(array_filter($gapDetails, function($item) {
            return( $item['caregap_name'] == 'awv_gap' );
        }));
        rsort($awvDetails);

        if (empty($awvDetails)) {
            $awvGap['db_column'] = 'awv_gap';
            $awvGap['details'] = [];
            $awvGap['title'] = 'Annual Wellness Visit (AWV)';
            $awvGap['comments'] = [];
            $awvGap['gap_status'] = "";
            $awvGap['options'] = ['N/A', 'Changed PCP', 'Completed', 'Left Practice', 'Need To Schedule', 'Not An Established Patient', 'Not Found In PF', 'Not In Service', 'Pending Visit', 'Refused', 'Unable To Reach'];
        } else {
            $awvGap['caregap_id'] = $awvDetails['0']['caregap_id'];
            $awvGap['comments'] = [];
            $awvGap['db_column'] = $awvDetails['0']['caregap_name'];
            $awvGap['details'] = $awvDetails['0'];
            $awvGap['gap_status'] = $awvDetails['0']['status'];
            $awvGap['options'] = ['N/A', 'Changed PCP', 'Completed', 'Left Practice', 'Need To Schedule', 'Not An Established Patient', 'Not Found In PF', 'Not In Service', 'Pending Visit', 'Refused', 'Unable To Reach'];
        }

        $care_gaps_array[] = $awvGap;
        return $care_gaps_array;
    }

    private function AetnaMedicareEmptyGaps($patientData)
    {
        // 1
        $breast_cancer['db_column'] = 'breast_cancer_gap';
        $breast_cancer['title'] = 'Breast Cancer Screening (BCS) AETNA MEDICARE';
        $breast_cancer['comments'] = [];
        $breast_cancer['details'] = [];
        $breast_cancer['gap_status'] =  "";
        $breast_cancer['options'] = ['N/A', 'Non-Compliant', 'Compliant', 'Bilateral Mastectomy', 'Patient Refused', 'Refusal Reviewed', 'Scheduled'];
        $care_gaps_array[] = $breast_cancer;

        //2
        $colon_cancer['db_column'] = 'colorectal_cancer_gap';
        $colon_cancer['title'] = 'Colorectal Cancer Screening (COL) AETNA MEDICARE';
        $colon_cancer['comments'] = [];
        $colon_cancer['details'] = [];
        $colon_cancer['gap_status'] =  "";
        $colon_cancer['options'] = ['N/A', 'Non-Compliant', 'Compliant', 'Total Colectomy', 'Colorectal Cancer', 'Patient Refused', 'Refusal Reviewed', 'Scheduled'];
        $care_gaps_array[] =  $colon_cancer;

        //3
        $eye_exam['db_column'] = 'eye_exam_gap';
        $eye_exam['title'] = 'Eye Exam for Patients With Diabetes - Eye Exam';
        $eye_exam['db_column'] = 'eye_exam_gap';
        $eye_exam['details'] = [];
        $eye_exam['comments'] = [];
        $eye_exam['gap_status'] = "";
        $eye_exam['options'] = ['N/A', 'Non-Compliant', 'Compliant', 'Patient Refused', 'Refusal Reviewed', 'Scheduled'];
        $care_gaps_array[] = $eye_exam;
        
        //4
        $hba1c_control['db_column'] = 'hba1c_gap';
        $hba1c_control['title'] = 'Diabetes Care - Blood Sugar Control (>9%)';
        $hba1c_control['comments'] = [];
        $hba1c_control['details'] =  [];
        $hba1c_control['gap_status'] = "";
        $hba1c_control['options'] = ['N/A', 'Non-Compliant', 'Compliant', 'Not Reported', 'Diagnosis Incorrect', 'Patient Refused', 'Refusal Reviewed', 'Scheduled'];
        $care_gaps_array[] = $hba1c_control;

         //5
         $spcStatinGap['db_column'] = 'spc_disease_gap';
         $spcStatinGap['details'] = [];
         $spcStatinGap['title'] = 'Statin Therapy for Patients with Cardiovascular Disease: Received Statin Therapy (SPC_STATIN)';
         $spcStatinGap['comments'] = [];
         $spcStatinGap['gap_status'] = "";
         $spcStatinGap['options'] = ['N/A', 'Changed PCP', 'Completed', 'Left Practice', 'Need To Schedule', 'Not An Established Patient', 'Not Found In PF', 'Not In Service', 'Pending Visit', 'Refused', 'Unable To Reach'];
         $care_gaps_array[] = $spcStatinGap;

         //6
        $fmcGap['db_column'] = 'faed_visit_gap';
        $fmcGap['details'] = [];
        $fmcGap['title'] = 'Follow-Up After Emergency Department Visit for MCC (FMC)';
        $fmcGap['comments'] = [];
        $fmcGap['gap_status'] =  "";
        $fmcGap['options'] = ['N/A', 'Changed PCP', 'Completed', 'Left Practice', 'Need To Schedule', 'Not An Established Patient', 'Not Found In PF', 'Not In Service', 'Pending Visit', 'Refused', 'Unable To Reach'];
        $care_gaps_array[] = $fmcGap;

        //7
        $adhDiabGap['db_column'] = 'mad_medications_gap';
        $adhDiabGap['details'] = [];
        $adhDiabGap['title'] = 'Medication Adherence for Diabetes Medications: Diabetes Medications (ADH_DIAB)';
        $adhDiabGap['comments'] = [];
        $adhDiabGap['gap_status'] = "";
        $adhDiabGap['options'] = ['N/A', 'Changed PCP', 'Completed', 'Left Practice', 'Need To Schedule', 'Not An Established Patient', 'Not Found In PF', 'Not In Service', 'Pending Visit', 'Refused', 'Unable To Reach'];
        $care_gaps_array[] = $adhDiabGap;

        //8
        $adhAceGap['db_column'] = 'ma_hypertension_gap';
        $adhAceGap['details'] =  [];
        $adhAceGap['title'] = 'Medication Adherence for Hypertension (ACE or ARB): ACE (ADH_ACE)';
        $adhAceGap['comments'] = [];
        $adhAceGap['gap_status'] =  "";
        $adhAceGap['options'] = ['N/A', 'Changed PCP', 'Completed', 'Left Practice', 'Need To Schedule', 'Not An Established Patient', 'Not Found In PF', 'Not In Service', 'Pending Visit', 'Refused', 'Unable To Reach'];
        $care_gaps_array[] = $adhAceGap;

        //9
        $supdGap['db_column'] = 'sup_diabetes_gap';
        $supdGap['details'] = [];
        $supdGap['title'] = 'Statin Use in Persons with Diabetes (SUPD)';
        $supdGap['comments'] = [];
        $supdGap['gap_status'] = "";
        $supdGap['options'] = ['N/A', 'Changed PCP', 'Completed', 'Left Practice', 'Need To Schedule', 'Not An Established Patient', 'Not Found In PF', 'Not In Service', 'Pending Visit', 'Refused', 'Unable To Reach'];
        $care_gaps_array[] = $supdGap;

        //10
        $adhStatinGap['db_column'] = 'ma_cholesterol_gap';
        $adhStatinGap['details'] = [];
        $adhStatinGap['title'] = 'Medication Adherence for Cholesterol (Statins): Statins (ADH_STATIN)';
        $adhStatinGap['comments'] = [];
        $adhStatinGap['gap_status'] = "";
        $adhStatinGap['options'] = ['N/A', 'Changed PCP', 'Completed', 'Left Practice', 'Need To Schedule', 'Not An Established Patient', 'Not Found In PF', 'Not In Service', 'Pending Visit', 'Refused', 'Unable To Reach'];
        $care_gaps_array[] = $adhStatinGap;

        //11
        $omwGap['db_column'] = 'omw_fracture_gap';
        $omwGap['details'] =  [];
        $omwGap['title'] = 'Osteoporosis Management in Women Who Had a Fracture (OMW)';
        $omwGap['comments'] =  [];
        $omwGap['gap_status'] =  "";
        $omwGap['options'] = ['N/A', 'Changed PCP', 'Completed', 'Left Practice', 'Need To Schedule', 'Not An Established Patient', 'Not Found In PF', 'Not In Service', 'Pending Visit', 'Refused', 'Unable To Reach'];
        $care_gaps_array[] = $omwGap;   
        
        //12
        $pcrGap['db_column'] = 'pc_readmissions_gap';
        $pcrGap['details'] = [];
        $pcrGap['title'] = 'Plan All-Cause Readmissions (PCR)';
        $pcrGap['comments'] = [];
        $pcrGap['gap_status'] =  "";
        $pcrGap['options'] = ['N/A', 'Changed PCP', 'Completed', 'Left Practice', 'Need To Schedule', 'Not An Established Patient', 'Not Found In PF', 'Not In Service', 'Pending Visit', 'Refused', 'Unable To Reach'];
        $care_gaps_array[] = $pcrGap;
       
        //13
        $awvGap = [];
        $gapDetails = @$patientData['care_gaps_details'] ?? [];

        $awvDetails = array_values(array_filter($gapDetails, function($item) {
            return( $item['caregap_name'] == 'awv_gap' );
        }));
        rsort($awvDetails);

        if (empty($awvDetails)) {
            $awvGap['db_column'] = 'awv_gap';
            $awvGap['details'] = [];
            $awvGap['title'] = 'Annual Wellness Visit (AWV)';
            $awvGap['comments'] = [];
            $awvGap['gap_status'] = "";
            $awvGap['options'] = ['N/A', 'Changed PCP', 'Completed', 'Left Practice', 'Need To Schedule', 'Not An Established Patient', 'Not Found In PF', 'Not In Service', 'Pending Visit', 'Refused', 'Unable To Reach'];
        } else {
            $awvGap['caregap_id'] = $awvDetails['0']['caregap_id'];
            $awvGap['comments'] = [];
            $awvGap['db_column'] = $awvDetails['0']['caregap_name'];
            $awvGap['details'] = $awvDetails['0'];
            $awvGap['gap_status'] = $awvDetails['0']['status'];
            $awvGap['options'] = ['N/A', 'Changed PCP', 'Completed', 'Left Practice', 'Need To Schedule', 'Not An Established Patient', 'Not Found In PF', 'Not In Service', 'Pending Visit', 'Refused', 'Unable To Reach'];
        }

        $care_gaps_array[] = $awvGap;
        return $care_gaps_array;
        
    } 
    
    private function AllwellMedicareEmptyGaps($patientData)
    {
        // 1
        $breast_cancer['db_column'] = 'breast_cancer_gap';
        $breast_cancer['title'] = 'Breast Cancer Screening (BCS) Allwell MEDICARE';
        $breast_cancer['comments'] = [];
        $breast_cancer['details'] = [];
        $breast_cancer['gap_status'] =  "";
        $breast_cancer['options'] = ['N/A', 'Non-Compliant', 'Compliant', 'Bilateral Mastectomy', 'Patient Refused', 'Refusal Reviewed', 'Scheduled'];
        $care_gaps_array[] = $breast_cancer;

        //2
        $colon_cancer['db_column'] = 'colorectal_cancer_gap';
        $colon_cancer['title'] = 'Colorectal Cancer Screening (COL) Allwell MEDICARE';
        $colon_cancer['comments'] = [];
        $colon_cancer['details'] = [];
        $colon_cancer['gap_status'] =  "";
        $colon_cancer['options'] = ['N/A', 'Non-Compliant', 'Compliant', 'Total Colectomy', 'Colorectal Cancer', 'Patient Refused', 'Refusal Reviewed', 'Scheduled'];
        $care_gaps_array[] =  $colon_cancer;

        //3
        $eye_exam['db_column'] = 'eye_exam_gap';
        $eye_exam['title'] = 'Eye Exam for Patients With Diabetes - Eye Exam';
        $eye_exam['db_column'] = 'eye_exam_gap';
        $eye_exam['details'] = [];
        $eye_exam['comments'] = [];
        $eye_exam['gap_status'] = "";
        $eye_exam['options'] = ['N/A', 'Non-Compliant', 'Compliant', 'Patient Refused', 'Refusal Reviewed', 'Scheduled'];
        $care_gaps_array[] = $eye_exam;
        
        //4
        $hba1c_control['db_column'] = 'hba1c_gap';
        $hba1c_control['title'] = 'Diabetes Care - Blood Sugar Control (<=9%)';
        $hba1c_control['comments'] = [];
        $hba1c_control['details'] =  [];
        $hba1c_control['gap_status'] = "";
        $hba1c_control['options'] = ['N/A', 'Non-Compliant', 'Compliant', 'Not Reported', 'Diagnosis Incorrect', 'Patient Refused', 'Refusal Reviewed', 'Scheduled'];
        $care_gaps_array[] = $hba1c_control;

         //5
        $blood_pressure['db_column'] = 'high_bp_gap';
        $blood_pressure['title'] = 'Controlling Blood Pressure (CBP)';
        $blood_pressure['comments'] = [];
        $blood_pressure['details'] = [];
        $blood_pressure['gap_status'] =  "";
        $blood_pressure['options'] = ['N/A', 'Non-Compliant', 'Compliant', 'Patient Refused', 'Refusal Reviewed', 'Scheduled'];
        $care_gaps_array[] = $blood_pressure;

         //6
        $adults_pain_gap['db_column'] = 'pain_screening_gap';
        $adults_pain_gap['details'] = [];
        $adults_pain_gap['title'] = 'Care for Older Adults Pain-Screening (COA4)';
        $adults_pain_gap['comments'] = [];
        $adults_pain_gap['gap_status'] =  "";
        $adults_pain_gap['options'] = ['N/A', 'Non-Compliant', 'Compliant', 'Not Reported', 'Diagnosis Incorrect', 'Patient Refused', 'Refusal Reviewed', 'Scheduled'];
        $care_gaps_array[] = $adults_pain_gap;

        //7
        $adults_med_gap['db_column'] = 'adults_medic_gap';
        $adults_med_gap['details'] = [];
        $adults_med_gap['title'] = 'Care for Older Adults - Medication Review (COA2)';
        $adults_med_gap['comments'] = [];
        $adults_med_gap['gap_status'] =  "";
        $adults_med_gap['options'] = ['N/A', 'Non-Compliant', 'Compliant', 'Not Reported', 'Diagnosis Incorrect', 'Patient Refused', 'Refusal Reviewed', 'Scheduled'];
        $care_gaps_array[] = $adults_med_gap;

        //8
        $highRiskGap['db_column'] = 'm_high_risk_cc_gap';
        $highRiskGap['details'] =  [];
        $highRiskGap['title'] = 'FMC - F/U ED Multiple High Risk Chronic Conditions';
        $highRiskGap['comments'] = [];
        $highRiskGap['gap_status'] =  "";
        $highRiskGap['options'] = ['N/A', 'Changed PCP', 'Completed', 'Left Practice', 'Need To Schedule', 'Not An Established Patient', 'Not Found In PF', 'Not In Service', 'Pending Visit', 'Refused', 'Unable To Reach'];
        $care_gaps_array[] = $highRiskGap;

        //9
        $supdGap['db_column'] = 'sup_diabetes_gap';
        $supdGap['details'] = [];
        $supdGap['title'] = 'Statin Use in Persons with Diabetes (SUPD)';
        $supdGap['comments'] = [];
        $supdGap['gap_status'] = "";
        $supdGap['options'] = ['N/A', 'Changed PCP', 'Completed', 'Left Practice', 'Need To Schedule', 'Not An Established Patient', 'Not Found In PF', 'Not In Service', 'Pending Visit', 'Refused', 'Unable To Reach'];
        $care_gaps_array[] = $supdGap;

        //10
        $adhDiabeticGap['db_column'] = 'med_adherence_diabetic_gap';
        $adhDiabeticGap['details'] = [];
        $adhDiabeticGap['title'] = 'Med Adherence - Diabetic';
        $adhDiabeticGap['comments'] = [];
        $adhDiabeticGap['gap_status'] = "";
        $adhDiabeticGap['options'] = ['N/A', 'Changed PCP', 'Completed', 'Left Practice', 'Need To Schedule', 'Not An Established Patient', 'Not Found In PF', 'Not In Service', 'Pending Visit', 'Refused', 'Unable To Reach'];
        $care_gaps_array[] = $adhDiabeticGap;

        //11
        $adhRasGap['db_column'] = 'med_adherence_ras_gap';
        $adhRasGap['details'] =  [];
        $adhRasGap['title'] = 'Med Adherence - RAS';
        $adhRasGap['comments'] =  [];
        $adhRasGap['gap_status'] =  "";
        $adhRasGap['options'] = ['N/A', 'Changed PCP', 'Completed', 'Left Practice', 'Need To Schedule', 'Not An Established Patient', 'Not Found In PF', 'Not In Service', 'Pending Visit', 'Refused', 'Unable To Reach'];
        $care_gaps_array[] = $adhRasGap;   
        
        //12
        $adhStatinsGap['db_column'] = 'med_adherence_statins_gap';
        $adhStatinsGap['details'] = [];
        $adhStatinsGap['title'] = 'Med Adherence - Statins';
        $adhStatinsGap['comments'] = [];
        $adhStatinsGap['gap_status'] =  "";
        $adhStatinsGap['options'] = ['N/A', 'Changed PCP', 'Completed', 'Left Practice', 'Need To Schedule', 'Not An Established Patient', 'Not Found In PF', 'Not In Service', 'Pending Visit', 'Refused', 'Unable To Reach'];
        $care_gaps_array[] = $adhStatinsGap;

        //13
        $spcStatinsCVDGap['db_column'] = 'spc_statin_therapy_cvd_gap';
        $spcStatinsCVDGap['details'] = [];
        $spcStatinsCVDGap['title'] = 'SPC - Statin Therapy for Patients with CVD';
        $spcStatinsCVDGap['comments'] = [];
        $spcStatinsCVDGap['gap_status'] =  "";
        $spcStatinsCVDGap['options'] = ['N/A', 'Changed PCP', 'Completed', 'Left Practice', 'Need To Schedule', 'Not An Established Patient', 'Not Found In PF', 'Not In Service', 'Pending Visit', 'Refused', 'Unable To Reach'];
        $care_gaps_array[] = $spcStatinsCVDGap;

        //14
        $trcAterDiscGap['db_column'] = 'trc_eng_after_disc_gap';
        $trcAterDiscGap['details'] = [];
        $trcAterDiscGap['title'] = 'TRC - Engagement After Discharge';
        $trcAterDiscGap['comments'] = [];
        $trcAterDiscGap['gap_status'] =  "";
        $trcAterDiscGap['options'] = ['N/A', 'Changed PCP', 'Completed', 'Left Practice', 'Need To Schedule', 'Not An Established Patient', 'Not Found In PF', 'Not In Service', 'Pending Visit', 'Refused', 'Unable To Reach'];
        $care_gaps_array[] = $trcAterDiscGap;

        //15
        $trcPostDiscGap['db_column'] = 'trc_mr_post_disc_gap';
        $trcPostDiscGap['details'] = [];
        $trcPostDiscGap['title'] = 'TRC - Med Reconciliation Post Discharge';
        $trcPostDiscGap['comments'] = [];
        $trcPostDiscGap['gap_status'] =  "";
        $trcPostDiscGap['options'] = ['N/A', 'Changed PCP', 'Completed', 'Left Practice', 'Need To Schedule', 'Not An Established Patient', 'Not Found In PF', 'Not In Service', 'Pending Visit', 'Refused', 'Unable To Reach'];
        $care_gaps_array[] = $trcPostDiscGap;

        //16
        $kidneyHealthDiabetesGap['db_column'] = 'kidney_health_diabetes_gap';
        $kidneyHealthDiabetesGap['details'] = [];
        $kidneyHealthDiabetesGap['title'] = 'KED - Kidney Health for Patients With Diabetes Current';
        $kidneyHealthDiabetesGap['comments'] = [];
        $kidneyHealthDiabetesGap['gap_status'] =  "";
        $kidneyHealthDiabetesGap['options'] = ['N/A', 'Changed PCP', 'Completed', 'Left Practice', 'Need To Schedule', 'Not An Established Patient', 'Not Found In PF', 'Not In Service', 'Pending Visit', 'Refused', 'Unable To Reach'];
        $care_gaps_array[] = $kidneyHealthDiabetesGap;
       
        //17
        $awvGap = [];
        $gapDetails = @$patientData['care_gaps_details'] ?? [];

        $awvDetails = array_values(array_filter($gapDetails, function($item) {
            return( $item['caregap_name'] == 'awv_gap' );
        }));
        rsort($awvDetails);

        if (empty($awvDetails)) {
            $awvGap['db_column'] = 'awv_gap';
            $awvGap['details'] = [];
            $awvGap['title'] = 'Annual Wellness Visit (AWV)';
            $awvGap['comments'] = [];
            $awvGap['gap_status'] = "";
            $awvGap['options'] = ['N/A', 'Changed PCP', 'Completed', 'Left Practice', 'Need To Schedule', 'Not An Established Patient', 'Not Found In PF', 'Not In Service', 'Pending Visit', 'Refused', 'Unable To Reach'];
        } else {
            $awvGap['caregap_id'] = $awvDetails['0']['caregap_id'];
            $awvGap['comments'] = [];
            $awvGap['db_column'] = $awvDetails['0']['caregap_name'];
            $awvGap['details'] = $awvDetails['0'];
            $awvGap['gap_status'] = $awvDetails['0']['status'];
            $awvGap['options'] = ['N/A', 'Changed PCP', 'Completed', 'Left Practice', 'Need To Schedule', 'Not An Established Patient', 'Not Found In PF', 'Not In Service', 'Pending Visit', 'Refused', 'Unable To Reach'];
        }

        $care_gaps_array[] = $awvGap;
        return $care_gaps_array;
    }
    
    private function HealthChoiceArizonaEmptyGaps($patientData)
    {
        // 1
        $breast_cancer['db_column'] = 'breast_cancer_gap';
        $breast_cancer['title'] = 'Breast Cancer Screening (BCS) Health Choice Arizona';
        $breast_cancer['comments'] = [];
        $breast_cancer['details'] = [];
        $breast_cancer['gap_status'] =  "";
        $breast_cancer['options'] = ['N/A', 'Non-Compliant', 'Compliant', 'Bilateral Mastectomy', 'Patient Refused', 'Refusal Reviewed', 'Scheduled'];
        $care_gaps_array[] = $breast_cancer;

        //2
        $cervical_cancer['db_column'] = 'cervical_cancer_gap';
        $cervical_cancer['title'] = 'CCS - Cervical Cancer Screening';
        $cervical_cancer['comments'] = [];
        $cervical_cancer['details'] = [];
        $cervical_cancer['gap_status'] =  "";
        $cervical_cancer['options'] = ['N/A', 'Non-Compliant', 'Compliant', 'Total Colectomy', 'Colorectal Cancer', 'Patient Refused', 'Refusal Reviewed', 'Scheduled'];
        $care_gaps_array[] =  $cervical_cancer;

        //3
        $opioids_high_dosage['db_column'] = 'opioids_high_dosage_gap';
        $opioids_high_dosage['title'] = 'HDO - Use of Opioids at High Dosage';
        $opioids_high_dosage['details'] = [];
        $opioids_high_dosage['comments'] = [];
        $opioids_high_dosage['gap_status'] = "";
        $opioids_high_dosage['options'] = ['N/A', 'Non-Compliant', 'Compliant', 'Patient Refused', 'Refusal Reviewed', 'Scheduled'];
        $care_gaps_array[] = $opioids_high_dosage;
        
        //4
        $hba1c_poor_gap['db_column'] = 'hba1c_poor_gap';
        $hba1c_poor_gap['title'] = 'HBD - Hemoglobin A1c (HbA1c) Poor Control ';
        $hba1c_poor_gap['comments'] = [];
        $hba1c_poor_gap['details'] =  [];
        $hba1c_poor_gap['gap_status'] = "";
        $hba1c_poor_gap['options'] = ['N/A', 'Non-Compliant', 'Compliant', 'Not Reported', 'Diagnosis Incorrect', 'Patient Refused', 'Refusal Reviewed', 'Scheduled'];
        $care_gaps_array[] = $hba1c_poor_gap;

         //5
        $ppc1_gap['db_column'] = 'ppc1_gap';
        $ppc1_gap['title'] = 'PPC1 - Timeliness of Prenatal Care';
        $ppc1_gap['comments'] = [];
        $ppc1_gap['details'] = [];
        $ppc1_gap['gap_status'] =  "";
        $ppc1_gap['options'] = ['N/A', 'Non-Compliant', 'Compliant', 'Patient Refused', 'Refusal Reviewed', 'Scheduled'];
        $care_gaps_array[] = $ppc1_gap;

         //6
         $ppc2_gap['db_column'] = 'ppc2_gap';
         $ppc2_gap['title'] = 'PPC2 - Timeliness of Prenatal Care';
         $ppc2_gap['comments'] = [];
         $ppc2_gap['details'] = [];
         $ppc2_gap['gap_status'] =  "";
         $ppc2_gap['options'] = ['N/A', 'Non-Compliant', 'Compliant', 'Not Reported', 'Diagnosis Incorrect', 'Patient Refused', 'Refusal Reviewed', 'Scheduled'];
         $care_gaps_array[] = $ppc2_gap;

        //7
        $well_child_visits_gap['db_column'] = 'well_child_visits_gap';
        $well_child_visits_gap['title'] = 'WCV - Well-Child Visits for Age 3-21';
        $well_child_visits_gap['comments'] = [];
        $well_child_visits_gap['details'] = [];
        $well_child_visits_gap['gap_status'] =  "";
        $well_child_visits_gap['options'] = ['N/A', 'Non-Compliant', 'Compliant', 'Not Reported', 'Diagnosis Incorrect', 'Patient Refused', 'Refusal Reviewed', 'Scheduled'];
        $care_gaps_array[] = $well_child_visits_gap;

        //8
        $chlamydia_gap['db_column'] = 'chlamydia_gap';
        $chlamydia_gap['title'] = 'Chlamydia Screening';
        $chlamydia_gap['comments'] = [];
        $chlamydia_gap['details'] =  [];
        $chlamydia_gap['gap_status'] =  "";
        $chlamydia_gap['options'] = ['N/A', 'Changed PCP', 'Completed', 'Left Practice', 'Need To Schedule', 'Not An Established Patient', 'Not Found In PF', 'Not In Service', 'Pending Visit', 'Refused', 'Unable To Reach'];
        $care_gaps_array[] = $chlamydia_gap;

        //9
        $high_bp_gap['db_column'] = 'high_bp_gap';
        $high_bp_gap['title'] = 'CBP - Controlling High Blood Pressure';
        $high_bp_gap['comments'] = [];
        $high_bp_gap['details'] = [];
        $high_bp_gap['gap_status'] = "";
        $high_bp_gap['options'] = ['N/A', 'Changed PCP', 'Completed', 'Left Practice', 'Need To Schedule', 'Not An Established Patient', 'Not Found In PF', 'Not In Service', 'Pending Visit', 'Refused', 'Unable To Reach'];
        $care_gaps_array[] = $high_bp_gap;

        //10
        $fuh_30Day_gap['db_column'] = 'fuh_30Day_gap';
        $fuh_30Day_gap['title'] = 'Follow-Up After Hospitalization for Mental Illness (FUH 30-Day)';
        $fuh_30Day_gap['comments'] = [];
        $fuh_30Day_gap['details'] = [];
        $fuh_30Day_gap['gap_status'] = "";
        $fuh_30Day_gap['options'] = ['N/A', 'Changed PCP', 'Completed', 'Left Practice', 'Need To Schedule', 'Not An Established Patient', 'Not Found In PF', 'Not In Service', 'Pending Visit', 'Refused', 'Unable To Reach'];
        $care_gaps_array[] = $fuh_30Day_gap;

        //11
        $fuh_7Day_gap['db_column'] = 'fuh_7Day_gap';
        $fuh_7Day_gap['details'] =  [];
        $fuh_7Day_gap['title'] = 'Follow-Up After Hospitalization for Mental Illness (FUH 7-Day)';
        $fuh_7Day_gap['comments'] =  [];
        $fuh_7Day_gap['gap_status'] =  "";
        $fuh_7Day_gap['options'] = ['N/A', 'Changed PCP', 'Completed', 'Left Practice', 'Need To Schedule', 'Not An Established Patient', 'Not Found In PF', 'Not In Service', 'Pending Visit', 'Refused', 'Unable To Reach'];
        $care_gaps_array[] = $fuh_7Day_gap;   
        
        //17
        $awvGap = [];
        $gapDetails = @$patientData['care_gaps_details'] ?? [];

        $awvDetails = array_values(array_filter($gapDetails, function($item) {
            return( $item['caregap_name'] == 'awv_gap' );
        }));
        rsort($awvDetails);

        if (empty($awvDetails)) {
            $awvGap['db_column'] = 'awv_gap';
            $awvGap['details'] = [];
            $awvGap['title'] = 'Annual Wellness Visit (AWV)';
            $awvGap['comments'] = [];
            $awvGap['gap_status'] = "";
            $awvGap['options'] = ['N/A', 'Changed PCP', 'Completed', 'Left Practice', 'Need To Schedule', 'Not An Established Patient', 'Not Found In PF', 'Not In Service', 'Pending Visit', 'Refused', 'Unable To Reach'];
        } else {
            $awvGap['caregap_id'] = $awvDetails['0']['caregap_id'];
            $awvGap['comments'] = [];
            $awvGap['db_column'] = $awvDetails['0']['caregap_name'];
            $awvGap['details'] = $awvDetails['0'];
            $awvGap['gap_status'] = $awvDetails['0']['status'];
            $awvGap['options'] = ['N/A', 'Changed PCP', 'Completed', 'Left Practice', 'Need To Schedule', 'Not An Established Patient', 'Not Found In PF', 'Not In Service', 'Pending Visit', 'Refused', 'Unable To Reach'];
        }

        $care_gaps_array[] = $awvGap;
        return $care_gaps_array;
    }
    
    private function UnitedHealthCareEmptyGaps($patientData)
    {
        // 1
        $breast_cancer['db_column'] = 'breast_cancer_gap';
        $breast_cancer['title'] = 'C01-Breast Cancer Screening United Health Care';
        $breast_cancer['comments'] = [];
        $breast_cancer['details'] = [];
        $breast_cancer['gap_status'] =  "";
        $breast_cancer['options'] = ['N/A', 'Non-Compliant', 'Compliant', 'Bilateral Mastectomy', 'Patient Refused', 'Refusal Reviewed', 'Scheduled'];
        $care_gaps_array[] = $breast_cancer;

        //2
        $colon_cancer['db_column'] = 'colorectal_cancer_gap';
        $colon_cancer['title'] = 'C02-Colorectal Cancer Screening';
        $colon_cancer['comments'] = [];
        $colon_cancer['details'] = [];
        $colon_cancer['gap_status'] =  "";
        $colon_cancer['options'] = ['N/A', 'Non-Compliant', 'Compliant', 'Total Colectomy', 'Colorectal Cancer', 'Patient Refused', 'Refusal Reviewed', 'Scheduled'];
        $care_gaps_array[] =  $colon_cancer;

        //3
        $adults_med_gap['db_column'] = 'adults_medic_gap';
        $adults_med_gap['details'] = [];
        $adults_med_gap['title'] = 'C06-Care for Older Adults - Medication Review';
        $adults_med_gap['comments'] = [];
        $adults_med_gap['gap_status'] =  "";
        $adults_med_gap['options'] = ['N/A', 'Non-Compliant', 'Compliant', 'Not Reported', 'Diagnosis Incorrect', 'Patient Refused', 'Refusal Reviewed', 'Scheduled'];
        $care_gaps_array[] = $adults_med_gap;
        
        //4
        $adults_fun_status_gap['db_column'] = 'adults_fun_status_gap';
        $adults_fun_status_gap['title'] = 'DMC10-Care for Older Adults - Functional Status Assessment';
        $adults_fun_status_gap['comments'] = [];
        $adults_fun_status_gap['details'] =  [];
        $adults_fun_status_gap['gap_status'] = "";
        $adults_fun_status_gap['options'] = ['N/A', 'Non-Compliant', 'Compliant', 'Not Reported', 'Diagnosis Incorrect', 'Patient Refused', 'Refusal Reviewed', 'Scheduled'];
        $care_gaps_array[] = $adults_fun_status_gap;

         //5
        $adults_pain_gap['db_column'] = 'pain_screening_gap';
        $adults_pain_gap['details'] = [];
        $adults_pain_gap['title'] = 'C07-Care for Older Adults - Pain Assessment';
        $adults_pain_gap['comments'] = [];
        $adults_pain_gap['gap_status'] =  "";
        $adults_pain_gap['options'] = ['N/A', 'Non-Compliant', 'Compliant', 'Not Reported', 'Diagnosis Incorrect', 'Patient Refused', 'Refusal Reviewed', 'Scheduled'];
        $care_gaps_array[] = $adults_pain_gap;

         //6
        $eye_exam['db_column'] = 'eye_exam_gap';
        $eye_exam['title'] = 'C09-Eye Exam for Patients With Diabetes';
        $eye_exam['db_column'] = 'eye_exam_gap';
        $eye_exam['details'] = [];
        $eye_exam['comments'] = [];
        $eye_exam['gap_status'] = "";
        $eye_exam['options'] = ['N/A', 'Non-Compliant', 'Compliant', 'Patient Refused', 'Refusal Reviewed', 'Scheduled'];
        $care_gaps_array[] = $eye_exam;

        //7
        $kidneyHealthDiabetesGap['db_column'] = 'kidney_health_diabetes_gap';
        $kidneyHealthDiabetesGap['details'] = [];
        $kidneyHealthDiabetesGap['title'] = 'C10-Kidney Health Evaluation for Patients With Diabetes';
        $kidneyHealthDiabetesGap['comments'] = [];
        $kidneyHealthDiabetesGap['gap_status'] =  "";
        $kidneyHealthDiabetesGap['options'] = ['N/A', 'Changed PCP', 'Completed', 'Left Practice', 'Need To Schedule', 'Not An Established Patient', 'Not Found In PF', 'Not In Service', 'Pending Visit', 'Refused', 'Unable To Reach'];
        $care_gaps_array[] = $kidneyHealthDiabetesGap;

        //8
        $hba1c_control['db_column'] = 'hba1c_gap';
        $hba1c_control['title'] = 'C11-Hemoglobin A1c Control for Patients With Diabetes';
        $hba1c_control['comments'] = [];
        $hba1c_control['details'] =  [];
        $hba1c_control['gap_status'] = "";
        $hba1c_control['options'] = ['N/A', 'Non-Compliant', 'Compliant', 'Not Reported', 'Diagnosis Incorrect', 'Patient Refused', 'Refusal Reviewed', 'Scheduled'];
        $care_gaps_array[] = $hba1c_control;

        //9
        $high_bp_gap['db_column'] = 'high_bp_gap';
        $high_bp_gap['title'] = 'C12 - Controlling Blood Pressure';
        $high_bp_gap['comments'] = [];
        $high_bp_gap['details'] = [];
        $high_bp_gap['gap_status'] = "";
        $high_bp_gap['options'] = ['N/A', 'Changed PCP', 'Completed', 'Left Practice', 'Need To Schedule', 'Not An Established Patient', 'Not Found In PF', 'Not In Service', 'Pending Visit', 'Refused', 'Unable To Reach'];
        $care_gaps_array[] = $high_bp_gap;

        //10
        $statin_therapy['db_column'] = 'statin_therapy_gap';
        $statin_therapy['details'] = [];
        $statin_therapy['title'] = 'C16 - Statin Therapy for Patients with Cardiovascular Disease';
        $statin_therapy['comments'] = [];
        $statin_therapy['gap_status'] = "";
        $statin_therapy['options'] = ['N/A', 'Non-Compliant', 'Compliant', 'Patient Refused', 'Refusal Reviewed', 'Scheduled'];
        $care_gaps_array[] = $statin_therapy;

        //11
        $med_adherence_diabetes_gap['db_column'] = 'med_adherence_diabetes_gap';
        $med_adherence_diabetes_gap['details'] =  [];
        $med_adherence_diabetes_gap['title'] = 'D08-Med Ad. For Diabetes Meds Current Year Status';
        $med_adherence_diabetes_gap['comments'] =  [];
        $med_adherence_diabetes_gap['gap_status'] =  "";
        $med_adherence_diabetes_gap['options'] = ['N/A', 'Changed PCP', 'Completed', 'Left Practice', 'Need To Schedule', 'Not An Established Patient', 'Not Found In PF', 'Not In Service', 'Pending Visit', 'Refused', 'Unable To Reach'];
        $care_gaps_array[] = $med_adherence_diabetes_gap;    
        
        //12
        $adhRasGap['db_column'] = 'med_adherence_ras_gap';
        $adhRasGap['details'] =  [];
        $adhRasGap['title'] = 'D09-Med Ad. (RAS antagonists) Current Year Status';
        $adhRasGap['comments'] =  [];
        $adhRasGap['gap_status'] =  "";
        $adhRasGap['options'] = ['N/A', 'Changed PCP', 'Completed', 'Left Practice', 'Need To Schedule', 'Not An Established Patient', 'Not Found In PF', 'Not In Service', 'Pending Visit', 'Refused', 'Unable To Reach'];
        $care_gaps_array[] = $adhRasGap;
        
        //13
        $med_adherence_statins_gap['db_column'] = 'med_adherence_statins_gap';
        $med_adherence_statins_gap['details'] = [];
        $med_adherence_statins_gap['title'] = 'D10-Med Ad. (Statins) Current Year Status';
        $med_adherence_statins_gap['comments'] = [];
        $med_adherence_statins_gap['gap_status'] =  "";
        $med_adherence_statins_gap['options'] = ['N/A', 'Non-Compliant', 'Compliant', 'Not Reported', 'Diagnosis Incorrect', 'Patient Refused', 'Refusal Reviewed', 'Scheduled'];
        $care_gaps_array[] = $med_adherence_statins_gap;
        
        //14
        $mtm_cmr_gap['db_column'] = 'mtm_cmr_gap';
        $mtm_cmr_gap['details'] = [];
        $mtm_cmr_gap['title'] = 'D11-MTM CMR Current Year Status';
        $mtm_cmr_gap['comments'] = [];
        $mtm_cmr_gap['gap_status'] =  "";
        $mtm_cmr_gap['options'] = ['N/A', 'Non-Compliant', 'Compliant', 'Not Reported', 'Diagnosis Incorrect', 'Patient Refused', 'Refusal Reviewed', 'Scheduled'];
        $care_gaps_array[] = $mtm_cmr_gap;

        //15
        $supdGap['db_column'] = 'sup_diabetes_gap';
        $supdGap['details'] = [];
        $supdGap['title'] = 'D12-Statin Use in Persons with Diabetes Current Year Status';
        $supdGap['comments'] = [];
        $supdGap['gap_status'] = "";
        $supdGap['options'] = ['N/A', 'Changed PCP', 'Completed', 'Left Practice', 'Need To Schedule', 'Not An Established Patient', 'Not Found In PF', 'Not In Service', 'Pending Visit', 'Refused', 'Unable To Reach'];
        $care_gaps_array[] = $supdGap;

        //16
        $awvGap = [];
        $gapDetails = @$patientData['care_gaps_details'] ?? [];

        $awvDetails = array_values(array_filter($gapDetails, function($item) {
            return( $item['caregap_name'] == 'awv_gap' );
        }));
        rsort($awvDetails);

        if (empty($awvDetails)) {
            $awvGap['db_column'] = 'awv_gap';
            $awvGap['details'] = [];
            $awvGap['title'] = 'Annual Wellness Visit (AWV)';
            $awvGap['comments'] = [];
            $awvGap['gap_status'] = "";
            $awvGap['options'] = ['N/A', 'Changed PCP', 'Completed', 'Left Practice', 'Need To Schedule', 'Not An Established Patient', 'Not Found In PF', 'Not In Service', 'Pending Visit', 'Refused', 'Unable To Reach'];
        } else {
            $awvGap['caregap_id'] = $awvDetails['0']['caregap_id'];
            $awvGap['comments'] = [];
            $awvGap['db_column'] = $awvDetails['0']['caregap_name'];
            $awvGap['details'] = $awvDetails['0'];
            $awvGap['gap_status'] = $awvDetails['0']['status'];
            $awvGap['options'] = ['N/A', 'Changed PCP', 'Completed', 'Left Practice', 'Need To Schedule', 'Not An Established Patient', 'Not Found In PF', 'Not In Service', 'Pending Visit', 'Refused', 'Unable To Reach'];
        }

        $care_gaps_array[] = $awvGap;
        return $care_gaps_array;
    }

    public function currentYearGaps(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'patient_id'   => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->getMessageBag()]);
        };

        $input = $request->all();
        $patientId = $input['patient_id'];
        $filterYear = @$input['filter_year'];

        try {
            $patientData = Patients::with('insurance')->where('id', $patientId)->first();
            $patientInsurance = @$patientData['insurance'] ?? "";

            $gapTable = "";

            if (!empty($patientInsurance)) {
                $insuranceProvider = $patientInsurance['provider'];
                
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
            }

            $careGapData = $gapTable::thisYearGaps($filterYear)->with('CareGapsDetails', 'caregapsComments')->where('patient_id', $patientId)->get()->toArray();

            $careGapData = @$careGapData['0'] ?? [];

            switch ($insuranceProvider) {
                case 'hcpw-001':
                    $careGapsArray = $this->hPCEmptyGaps($careGapData);
                    break;
                case 'hum-001':
                    $careGapsArray = $this->humanaEmptyGaps($careGapData);
                    break;
                case 'med-arz-001':
                    $careGapsArray = $this->MedicareArizonaEmptyGaps($careGapData);
                    break;
                case 'aet-001':
                    $careGapsArray = $this->AetnaMedicareEmptyGaps($careGapData);
                    break;
                case 'allwell-001':
                    $careGapsArray = $this->AllwellMedicareEmptyGaps($careGapData);
                    break;
                case 'hcarz-001':
                    $careGapsArray = $this->HealthchoiceArizonaEmptyGaps($careGapData);
                    break;
                case 'uhc-001':
                    $careGapsArray = $this->UnitedHealthCareEmptyGaps($careGapData);
                    break;
                    
                default:
                    break;
            }

            if (!empty($careGapData)) {
                foreach ($careGapsArray as $key => $value) {
                    $column = $value['db_column'];

                    $comments = array_values(array_filter($careGapData['caregaps_comments'], function($item) use ($column) {
                        return( $item['caregap_name'] == $column);
                    }));
                    rsort($comments);

                    $details = array_values(array_filter($careGapData['care_gaps_details'], function($item) use ($column) {
                        return( $item['caregap_name'] == $column);
                    }));
                    rsort($details);

                    $value['comments'] = @$comments ?? [];
                    $value['details'] = @$details ?? [];
                    $value['gap_status'] = $careGapData[$column];

                    $careGapsArray[$key] = $value;
                }
            }

            $response = [
                'success' => true,
                'data' => $careGapsArray,
            ];

        } catch (\Exception $e) {
            $response = array('success' => false, 'message' => $e->getMessage(), 'line'=>$e->getLine());
        }

        return response()->json($response);
    }

    private function storeStatusLogs($insurance_id, $currentPatients) {
        $data = "";
        try {    
            // Patients which are not in currentAssingned Population
            $previousYear = Carbon::now()->subYear()->year;

            // Storing all patient from previous year
            $where = [
                'insurance_id' => $insurance_id,
                'status' => '1',
                'patient_year' => $previousYear,
            ];

            $previousPopulation = Patients::where($where)->get()->toArray();
            $statusLogData = [];
            
            if (!empty($previousPopulation)) {
                foreach ($previousPopulation as $key => $value) {
                    $insertData = [
                        'patient_id' => $value['id'],
                        'insurance_id' => $value['insurance_id'],
                        'doctor_id' => $value['doctor_id'],
                        'clinic_id' => $value['clinic_id'],
                        'status' => $value['status'],
                        'group' => $value['group'],
                        'patient_year' => $value['patient_year'],
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                        'deleted_at' => NULL,
                    ];
    
                    $statusLogData[] = $insertData;
                }
    
                foreach ($statusLogData as $key => $value) {
                    $condition = [
                        'patient_id' => $value['patient_id'],
                    ];
                    $res = PatientStatusLogs::updateOrCreate($condition, $value);
                }
            }

            // Updating status in current patient Column
            $whereClause = [
                'insurance_id' => $insurance_id,
                'status' => '1',
            ];
    
            // Updating status of previous year patients missing in current year population
            $remainingPatients = Patients::where($whereClause)->whereNotIn('member_id', $memberId)->whereNotIn('unique_id', $uniqueId)->whereNotNull('patient_year')->get()->toArray();
            $remainingPatientId = array_column($remainingPatients, 'id');
            Patients::whereIn('id', $remainingPatientId)->update(['status' => '2']);

        } catch (\Exception $e) {
            $data = $e->getMessage();
        }

        return $data;
    }

}
