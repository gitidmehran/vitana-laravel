<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserCollection;

use App\Models\Questionaires;
use App\Models\Patients;
use App\Models\User;
use App\Models\Programs;
use App\Models\Clinic;
use App\Models\Insurances;
use App\Models\Schedule;

use App\Models\CareGaps;
use App\Models\CareGapsDetails;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

use Auth,Validator,Hash,DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function ActiveNonComp($gapName,$passValue){

        $doctor_id = $passValue['doctor_id'] ?? '';
        $insurance_id = $passValue['insurance_id'] ?? '';
        $clinic_id = $passValue['clinic_id'] ?? '';

        $aa = array();
        $fPatient = array();
        //$aa = CareGaps::select('patient_id')->where($gapName, '!=', 'Compliant')->get();
        $aa = CareGaps::select('patient_id')->where($gapName, 'Non-Compliant')->get();
        foreach ($aa as $p) {
            $pat = $p->patient_id;
            //$query123 = Patients::where('id',$pat)->whereIn('status', [1, 2]);//->get();
            $query123 = Patients::where('id',$pat)->whereIn('group', [1, 2]);
                if(!empty($doctor_id)){
                    $query123 = $query123->where('doctor_id',$doctor_id);
                }
                if(!empty($insurance_id)){
                    $query123 = $query123->where('insurance_id',$insurance_id);
                }
                if(!empty($clinic_id)){
                    $query123 = $query123->where('clinic_id',$clinic_id);
                }

                $Patients = $query123->get();
                $fPatient[] = $Patients->count();
            }
            $counts = array_count_values($fPatient);
            // Get the count of 1
            $count = isset($counts[1]) ? $counts[1] : 0;
            // Output the count
            return $count;
    }
    public function index(Request $request)
    {
        $per_page = Config('constants.perpage_showdata');
        
        try {
            $doctor_id = $request->get('doctor_id') ?? '';
            $insurance_id = $request->get('insurance_id') ?? '';
            $clinic_id = $request->get('clinic_id') ?? '';
            $program_id = $request->get('program_id') ?? '';
            $careGap = $request->get('careGap') ?? '';

            $query = Patients::where('deleted_at', '=', Null);

            // filtering by DOCTOR/PCP
            if(!empty($doctor_id)) {
                $query = $query->where('doctor_id',$doctor_id);
            }

            // filtering by insurance
            if(!empty($insurance_id)) {
                $query = $query->where('insurance_id',$insurance_id);
            }

            // filtering by clinic
            if(!empty($clinic_id)) {
                $clinic_id = explode(',', $clinic_id);
                $query = $query->whereIn('clinic_id',$clinic_id);
            }

            $data = $query->get()->toArray();

            $total['totalPopulation'] = count($data);

            $data = collect($data);

            $group_A1 = $data->where('group', '1')->toArray();
            $total['group_a1'] = count($group_A1);
            
            $group_A2 = $data->where('group', '2')->toArray();
            $total['group_a2'] = count($group_A2);

            $total['group_b'] = $data->where('group', '3')->count();
            $total['group_c'] = $data->where('group', '4')->count();
            $total['activeUsers'] = $total['group_a1'] + $total['group_a2'];

            /* Getting Dcotors List */
            $doctor_data = User::OfClinicID($clinic_id)->select('id','first_name','mid_name','Last_name', 'clinic_id')
            ->where('role',21)
            ->orWhere('role', 13)
            ->get()->toArray();

            $insurance_data = Insurances::select('id','name','short_name')->get()->toArray();
            $program_data = Programs::select('id','name','short_name')->get()->toArray();
            $clinic_data = Clinic::select('id','name','short_name')->get()->toArray();

            //last_visit
            if (empty($total))
            {
                $total = 0;
                $response = [
                    'success' => true,
                    'message' => 'Sorry Dashboard Data Not Found',
                    'data' => $total,
                    'doctor_data' => $doctor_data,
                    'insurance_data' => $insurance_data,
                    'program_data' => $program_data,
                    'clinic_data' => $clinic_data,
                    'total_clinics' => count($clinic_data),
                    'total_insurances' => count($insurance_data),
                ];
            }
            
            // Start Colorectal Cancer Screening  ===========================> CCS  <=======================
            $total_HCP['CCS_ClosedPatients'] = CareGaps::where('colorectal_cancer_gap','Compliant');
            $total_HCP['CCS_ClosedPatients_insurance'] = CareGaps::where('colorectal_cancer_gap_insurance','Compliant');
            
            $total_HCP['CCS_OpenPatients'] = CareGaps::where('colorectal_cancer_gap', '!=' , 'Compliant');//where('colorectal_cancer_gap','Non-Compliant');
            $total_HCP['CCS_OpenPatients_insurance'] = CareGaps::where('colorectal_cancer_gap_insurance', '!=' , 'Compliant');//where('colorectal_cancer_gap_insurance','Non-Compliant');
        
            $total_HCP['CCS_Total'] = CareGaps::WhereNull('deleted_at');//where('colorectal_cancer_gap','Compliant')->orWhere('colorectal_cancer_gap','Non-Compliant')->where('insurance_id',$insurance_id)->count();//where('colorectal_cancer_gap','Compliant');//->count();
            $total_HCP['CCS_Total_insurance'] = CareGaps::WhereNull('deleted_at');//where('colorectal_cancer_gap_insurance','Compliant');//->count();
            
            
            if(!empty($doctor_id)){
                $total_HCP['CCS_ClosedPatients']->where('doctor_id',$doctor_id);
                $total_HCP['CCS_ClosedPatients_insurance']->where('doctor_id',$doctor_id);
                
                $total_HCP['CCS_OpenPatients']->where('doctor_id',$doctor_id);
                $total_HCP['CCS_OpenPatients_insurance']->where('doctor_id',$doctor_id);

                $total_HCP['CCS_Total']->where('doctor_id',$doctor_id);
                $total_HCP['CCS_Total_insurance']->where('doctor_id',$doctor_id);
            }
            if(!empty($insurance_id)){
                $total_HCP['CCS_ClosedPatients']->where('insurance_id',$insurance_id);
                $total_HCP['CCS_ClosedPatients_insurance']->where('insurance_id',$insurance_id);

                $total_HCP['CCS_OpenPatients']->where('insurance_id',$insurance_id);
                $total_HCP['CCS_OpenPatients_insurance']->where('insurance_id',$insurance_id);

                $total_HCP['CCS_Total']->where('insurance_id',$insurance_id);
                $total_HCP['CCS_Total_insurance']->where('insurance_id',$insurance_id);
            }
            if(!empty($clinic_id)){
                //$clinic_id = explode(',', $clinic_id);
                $total_HCP['CCS_ClosedPatients']->where('clinic_id',$clinic_id);
                $total_HCP['CCS_ClosedPatients_insurance']->where('clinic_id',$clinic_id);
                
                $total_HCP['CCS_OpenPatients']->where('clinic_id',$clinic_id);
                $total_HCP['CCS_OpenPatients_insurance']->where('clinic_id',$clinic_id);

                $total_HCP['CCS_Total']->where('clinic_id',$clinic_id);
                $total_HCP['CCS_Total_insurance']->where('clinic_id',$clinic_id);
            }
            $total_HCP['CCS_ClosedPatients'] = $total_HCP['CCS_ClosedPatients']->count();
            $total_HCP['CCS_ClosedPatients_insurance'] = $total_HCP['CCS_ClosedPatients_insurance']->count();

            $total_HCP['CCS_OpenPatients'] = $total_HCP['CCS_OpenPatients']->count();
            $total_HCP['CCS_OpenPatients_insurance'] = $total_HCP['CCS_OpenPatients_insurance']->count();
            $total_HCP['CCS_Total'] = $total_HCP['CCS_Total']->count();
            $total_HCP['CCS_Total_insurance'] = $total_HCP['CCS_Total_insurance']->count();
            //$total_HCP['CCS_Total'] = $total_HCP['CCS_Total']->whereIn('colorectal_cancer_gap', ['Compliant', 'Non-Compliant'])->count();
            //$total_HCP['CCS_Total_insurance'] = $total_HCP['CCS_Total_insurance']->whereIn('colorectal_cancer_gap_insurance', ['Compliant', 'Non-Compliant'])->count();

            if($total_HCP['CCS_Total'] != 0)
            {
                $total_HCP['CCS_Acheived'] = number_format( $total_HCP['CCS_ClosedPatients'] * 100 / $total_HCP['CCS_Total'] , 2, '.', '') ;
            }
            else{
                $total_HCP['CCS_Acheived'] = 0;
            }

            if($total_HCP['CCS_Total_insurance'] != 0)
            {
                $total_HCP['CCS_Acheived_insurance'] = number_format( $total_HCP['CCS_ClosedPatients_insurance'] * 100 / $total_HCP['CCS_Total_insurance'] , 2, '.', '') ;
            }
            else{
                $total_HCP['CCS_Acheived_insurance'] = 0;
            }
            $CCS_Required_Par = 75 ; 
            $total_HCP['CCS_Members_remaining'] = number_format( ((($CCS_Required_Par - $total_HCP['CCS_Acheived']) * $total_HCP['CCS_Total']) / 100) ) ;
            if($total_HCP['CCS_Members_remaining'] > 0 ){
                $total_HCP['CCS_Members_remaining'];
            }else{
                $total_HCP['CCS_Members_remaining'] = "-";
            }
            $total_HCP['CCS_Members_remaining_insurance'] = number_format( ((($CCS_Required_Par - $total_HCP['CCS_Acheived_insurance']) * $total_HCP['CCS_Total_insurance']) / 100) ) ;
            if($total_HCP['CCS_Members_remaining_insurance'] > 0 ){
                $total_HCP['CCS_Members_remaining_insurance'];
            }else{
                $total_HCP['CCS_Members_remaining_insurance'] = "-";
            }


            // End Colorectal Cancer Screening  ===========================> CCS  <=======================            

            // Start Blood Sugar Control =========================> CDC2 <================================
            $total_HCP['CDC2_ClosedPatients'] = CareGaps::where('hba1c_gap','Compliant');
            $total_HCP['CDC2_ClosedPatients_insurance'] = CareGaps::where('hba1c_gap_insurance','Compliant');
            
            $total_HCP['CDC2_OpenPatients'] = CareGaps::where('hba1c_gap', '!=' , 'Compliant');//where('hba1c_gap','Non-Compliant');
            $total_HCP['CDC2_OpenPatients_insurance'] = CareGaps::where('hba1c_gap_insurance', '!=' ,'Compliant');
            
            $total_HCP['CDC2_Total'] = CareGaps::WhereNull('deleted_at');//where('hba1c_gap','Compliant')->orWhere('hba1c_gap','Non-Compliant');//->count();
            $total_HCP['CDC2_Total_insurance'] = CareGaps::WhereNull('deleted_at');//where('hba1c_gap_insurance','Compliant')->orWhere('hba1c_gap_insurance','Non-Compliant');//->count();
            
            if(!empty($doctor_id)){
                $total_HCP['CDC2_ClosedPatients']->where('doctor_id',$doctor_id);
                $total_HCP['CDC2_ClosedPatients_insurance']->where('doctor_id',$doctor_id);
                
                $total_HCP['CDC2_OpenPatients']->where('doctor_id',$doctor_id);
                $total_HCP['CDC2_OpenPatients_insurance']->where('doctor_id',$doctor_id);
                
                $total_HCP['CDC2_Total']->where('doctor_id',$doctor_id);
                $total_HCP['CDC2_Total_insurance']->where('doctor_id',$doctor_id);

            }
            if(!empty($insurance_id)){
                $total_HCP['CDC2_ClosedPatients']->where('insurance_id',$insurance_id);
                $total_HCP['CDC2_ClosedPatients_insurance']->where('insurance_id',$insurance_id);

                $total_HCP['CDC2_OpenPatients']->where('insurance_id',$insurance_id);
                $total_HCP['CDC2_OpenPatients_insurance']->where('insurance_id',$insurance_id);

                $total_HCP['CDC2_Total']->where('insurance_id',$insurance_id);
                $total_HCP['CDC2_Total_insurance']->where('insurance_id',$insurance_id);
            }
            if(!empty($clinic_id)){
                //$clinic_id = explode(',', $clinic_id);
                $total_HCP['CDC2_ClosedPatients']->where('clinic_id',$clinic_id);
                $total_HCP['CDC2_ClosedPatients_insurance']->where('clinic_id',$clinic_id);
                                
                $total_HCP['CDC2_OpenPatients']->where('clinic_id',$clinic_id);
                $total_HCP['CDC2_OpenPatients_insurance']->where('clinic_id',$clinic_id);

                $total_HCP['CDC2_Total']->where('clinic_id',$clinic_id);
                $total_HCP['CDC2_Total_insurance']->where('clinic_id',$clinic_id);
            }
            $total_HCP['CDC2_ClosedPatients'] = $total_HCP['CDC2_ClosedPatients']->count();
            $total_HCP['CDC2_ClosedPatients_insurance'] = $total_HCP['CDC2_ClosedPatients_insurance']->count();

            $total_HCP['CDC2_OpenPatients'] = $total_HCP['CDC2_OpenPatients']->count();
            $total_HCP['CDC2_OpenPatients_insurance'] = $total_HCP['CDC2_OpenPatients_insurance']->count();

            $total_HCP['CDC2_Total'] = $total_HCP['CDC2_Total']->count();//->whereIn('hba1c_gap', ['Compliant', 'Non-Compliant'])->count();//CareGaps::where('hba1c_gap','Compliant')->orWhere('hba1c_gap','Non-Compliant')->count();
            $total_HCP['CDC2_Total_insurance'] = $total_HCP['CDC2_Total_insurance']->count();//->whereIn('hba1c_gap_insurance', ['Compliant', 'Non-Compliant'])->count();//CareGaps::where('hba1c_gap_insurance','Compliant')->orWhere('hba1c_gap_insurance','Non-Compliant')->count();
            if($total_HCP['CDC2_Total'] != 0)
            {
                $total_HCP['CDC2_Acheived'] = number_format( $total_HCP['CDC2_ClosedPatients'] * 100 / $total_HCP['CDC2_Total'] , 2, '.', '') ;
            }else{
                $total_HCP['CDC2_Acheived'] = 0;
            }

            if($total_HCP['CDC2_Total_insurance'] != 0)
            {
                $total_HCP['CDC2_Acheived_insurance'] = number_format( $total_HCP['CDC2_ClosedPatients_insurance'] * 100 / $total_HCP['CDC2_Total_insurance'] , 2, '.', '') ;
            }else{
                $total_HCP['CDC2_Acheived_insurance'] = 0;
            }

            $CDC2_Required_Par = 83 ; 
            $total_HCP['CDC2_Members_remaining'] = number_format( ((($CDC2_Required_Par - $total_HCP['CDC2_Acheived']) * $total_HCP['CDC2_Total']) / 100) ) ;
            $total_HCP['CDC2_Members_remaining_insurance'] = number_format( ((($CDC2_Required_Par - $total_HCP['CDC2_Acheived_insurance']) * $total_HCP['CDC2_Total_insurance']) / 100) ) ;

            if($total_HCP['CDC2_Members_remaining'] > 0 ){
                $total_HCP['CDC2_Members_remaining'];
            }else{
                $total_HCP['CDC2_Members_remaining'] = "-";
            }
            
            if($total_HCP['CDC2_Members_remaining_insurance'] > 0 ){
                $total_HCP['CDC2_Members_remaining_insurance'];
            }else{
                $total_HCP['CDC2_Members_remaining_insurance'] = "-";
            }
            // Start Blood Sugar Control =========================> CDC2 <================================



            //Start  Diabetes Care - Eye Exam  ================> CDC4 <===========================
            $total_HCP['CDC4_ClosedPatients'] = CareGaps::where('eye_exam_gap','Compliant');
            $total_HCP['CDC4_ClosedPatients_insurance'] = CareGaps::where('eye_exam_gap_insurance','Compliant');
           
            $total_HCP['CDC4_OpenPatients'] = CareGaps::where('eye_exam_gap', '!=' ,'Compliant');
            $total_HCP['CDC4_OpenPatients_insurance'] = CareGaps::where('eye_exam_gap_insurance', '!=' ,'Compliant');
            
            $total_HCP['CDC4_Total'] = CareGaps::WhereNull('deleted_at');//where('eye_exam_gap','Compliant')->orWhere('eye_exam_gap','Non-Compliant');//->count();
            $total_HCP['CDC4_Total_insurance'] = CareGaps::WhereNull('deleted_at');//where('eye_exam_gap_insurance','Compliant')->orWhere('eye_exam_gap_insurance','Non-Compliant');//->count();
            
            
            if(!empty($doctor_id)){
                $total_HCP['CDC4_ClosedPatients']->where('doctor_id',$doctor_id);
                $total_HCP['CDC4_ClosedPatients_insurance']->where('doctor_id',$doctor_id);
                
                $total_HCP['CDC4_OpenPatients']->where('doctor_id',$doctor_id);
                $total_HCP['CDC4_OpenPatients_insurance']->where('doctor_id',$doctor_id);
                
                $total_HCP['CDC4_Total']->where('doctor_id',$doctor_id);
                $total_HCP['CDC4_Total_insurance']->where('doctor_id',$doctor_id);
            }
            if(!empty($insurance_id)){
                $total_HCP['CDC4_ClosedPatients']->where('insurance_id',$insurance_id);
                $total_HCP['CDC4_ClosedPatients_insurance']->where('insurance_id',$insurance_id);
                
                $total_HCP['CDC4_OpenPatients']->where('insurance_id',$insurance_id);
                $total_HCP['CDC4_OpenPatients_insurance']->where('insurance_id',$insurance_id);
                
                $total_HCP['CDC4_Total']->where('insurance_id',$insurance_id);
                $total_HCP['CDC4_Total_insurance']->where('insurance_id',$insurance_id);
            }
            if(!empty($clinic_id)){
                //$clinic_id = explode(',', $clinic_id);
                $total_HCP['CDC4_ClosedPatients']->where('clinic_id',$clinic_id);
                $total_HCP['CDC4_ClosedPatients_insurance']->where('clinic_id',$clinic_id);

                $total_HCP['CDC4_OpenPatients']->where('clinic_id',$clinic_id);
                $total_HCP['CDC4_OpenPatients_insurance']->where('clinic_id',$clinic_id);

                $total_HCP['CDC4_Total']->where('clinic_id',$clinic_id);
                $total_HCP['CDC4_Total_insurance']->where('clinic_id',$clinic_id);
            }
            $total_HCP['CDC4_ClosedPatients'] = $total_HCP['CDC4_ClosedPatients']->count();
            $total_HCP['CDC4_ClosedPatients_insurance'] = $total_HCP['CDC4_ClosedPatients_insurance']->count();

            $total_HCP['CDC4_OpenPatients'] = $total_HCP['CDC4_OpenPatients']->count();
            $total_HCP['CDC4_OpenPatients_insurance'] = $total_HCP['CDC4_OpenPatients_insurance']->count();
            
            $total_HCP['CDC4_Total'] = $total_HCP['CDC4_Total']->count();//->whereIn('eye_exam_gap', ['Compliant', 'Non-Compliant'])->count();
            $total_HCP['CDC4_Total_insurance'] = $total_HCP['CDC4_Total_insurance']->count();//->whereIn('eye_exam_gap_insurance', ['Compliant', 'Non-Compliant'])->count();


            if($total_HCP['CDC4_Total'] != 0)
            {
                $total_HCP['CDC4_Acheived'] = number_format( $total_HCP['CDC4_ClosedPatients'] * 100 / $total_HCP['CDC4_Total'] , 2, '.', '') ;
            }else{
                $total_HCP['CDC4_Acheived'] = 0;
            }
            if($total_HCP['CDC4_Total'] != 0)
            {
                $total_HCP['CDC4_Acheived_insurance'] = number_format( $total_HCP['CDC4_ClosedPatients_insurance'] * 100 / $total_HCP['CDC4_Total_insurance'] , 2, '.', '') ;
            }else{
                $total_HCP['CDC4_Acheived_insurance'] = 0;
            }
            $CDC4_Required_Par = 73 ; 
            $total_HCP['CDC4_Members_remaining'] = number_format( ((($CDC4_Required_Par - $total_HCP['CDC4_Acheived']) * $total_HCP['CDC4_Total']) / 100) ) ;
            $total_HCP['CDC4_Members_remaining_insurance'] = number_format( ((($CDC4_Required_Par - $total_HCP['CDC4_Acheived_insurance']) * $total_HCP['CDC4_Total_insurance']) / 100) ) ;
            
            if($total_HCP['CDC4_Members_remaining'] > 0 ){
                $total_HCP['CDC4_Members_remaining'];
            }else{
                $total_HCP['CDC4_Members_remaining'] = "-";
            }

            if($total_HCP['CDC4_Members_remaining_insurance'] > 0 ){
                $total_HCP['CDC4_Members_remaining_insurance'];
            }else{
                $total_HCP['CDC4_Members_remaining_insurance'] = "-";
                        }
            // End Diabetes Care - Eye Exam  ================> CDC4 <===========================


            //Start Breast Cancer Screening  ================> BCS <===========================
            $total_HCP['BCS_ClosedPatients'] = CareGaps::where('breast_cancer_gap','Compliant');
            $total_HCP['BCS_ClosedPatients_insurance'] = CareGaps::where('breast_cancer_gap_insurance','Compliant');
            
            $total_HCP['BCS_OpenPatients'] = CareGaps::where('breast_cancer_gap', '!=' ,'Compliant');
            $total_HCP['BCS_OpenPatients_insurance'] = CareGaps::where('breast_cancer_gap_insurance', '!=' ,'Compliant');
           
            $total_HCP['BCS_Total'] = CareGaps::WhereNull('deleted_at');//where('breast_cancer_gap','Compliant')->orWhere('breast_cancer_gap','Non-Compliant');
            $total_HCP['BCS_Total_insurance'] = CareGaps::WhereNull('deleted_at');//where('breast_cancer_gap_insurance','Compliant')->orWhere('breast_cancer_gap_insurance','Non-Compliant');
            if(!empty($doctor_id)){
                $total_HCP['BCS_ClosedPatients']->where('doctor_id',$doctor_id);
                $total_HCP['BCS_ClosedPatients_insurance']->where('doctor_id',$doctor_id);
                
                $total_HCP['BCS_OpenPatients']->where('doctor_id',$doctor_id);
                $total_HCP['BCS_OpenPatients_insurance']->where('doctor_id',$doctor_id);
                
                $total_HCP['BCS_Total']->where('doctor_id',$doctor_id);
                $total_HCP['BCS_Total_insurance']->where('doctor_id',$doctor_id);

            }
            if(!empty($insurance_id)){
                $total_HCP['BCS_ClosedPatients']->where('insurance_id',$insurance_id);
                $total_HCP['BCS_ClosedPatients_insurance']->where('insurance_id',$insurance_id);
                
                $total_HCP['BCS_OpenPatients']->where('insurance_id',$insurance_id);
                $total_HCP['BCS_OpenPatients_insurance']->where('insurance_id',$insurance_id);
                
                $total_HCP['BCS_Total']->where('insurance_id',$insurance_id);
                $total_HCP['BCS_Total_insurance']->where('insurance_id',$insurance_id);
            }
            if(!empty($clinic_id)){
                //$clinic_id = explode(',', $clinic_id);
                $total_HCP['BCS_ClosedPatients']->where('clinic_id',$clinic_id);
                $total_HCP['BCS_ClosedPatients_insurance']->where('clinic_id',$clinic_id);
                
                $total_HCP['BCS_OpenPatients']->where('clinic_id',$clinic_id);
                $total_HCP['BCS_OpenPatients_insurance']->where('clinic_id',$clinic_id);
                
                $total_HCP['BCS_Total']->where('clinic_id',$clinic_id);
                $total_HCP['BCS_Total_insurance']->where('clinic_id',$clinic_id);
            }
            $total_HCP['BCS_ClosedPatients'] = $total_HCP['BCS_ClosedPatients']->count();
            $total_HCP['BCS_ClosedPatients_insurance'] = $total_HCP['BCS_ClosedPatients_insurance']->count();

            $total_HCP['BCS_OpenPatients'] = $total_HCP['BCS_OpenPatients']->count();
            $total_HCP['BCS_OpenPatients_insurance'] = $total_HCP['BCS_OpenPatients_insurance']->count();
            
            $total_HCP['BCS_Total'] = $total_HCP['BCS_Total']->count();//->whereIn('breast_cancer_gap', ['Compliant', 'Non-Compliant'])->count();
            $total_HCP['BCS_Total_insurance'] = $total_HCP['BCS_Total_insurance']->count();//->whereIn('breast_cancer_gap_insurance', ['Compliant', 'Non-Compliant'])->count();
            
            if($total_HCP['BCS_Total'] != 0)
            {
                $total_HCP['BCS_Acheived'] = number_format( $total_HCP['BCS_ClosedPatients'] * 100 / $total_HCP['BCS_Total'] , 2, '.', '') ;
            }else{
                $total_HCP['BCS_Acheived'] = 0;
            }

            if($total_HCP['BCS_Total_insurance'] != 0)
            {
                $total_HCP['BCS_Acheived_insurance'] = number_format( $total_HCP['BCS_ClosedPatients_insurance'] * 100 / $total_HCP['BCS_Total_insurance'] , 2, '.', '') ;
            }else{
                $total_HCP['BCS_Acheived_insurance'] = 0;
            }

            $BCS_Required_Par = 74 ; 
            $total_HCP['BCS_Members_remaining'] = number_format( ((($BCS_Required_Par - $total_HCP['BCS_Acheived']) * $total_HCP['CDC4_Total']) / 100) ) ;
            $total_HCP['BCS_Members_remaining_insurance'] = number_format( ((($BCS_Required_Par - $total_HCP['BCS_Acheived_insurance']) * $total_HCP['CDC4_Total_insurance']) / 100) ) ;
            
            if($total_HCP['BCS_Members_remaining'] > 0 ){
                $total_HCP['BCS_Members_remaining'];
            }else{
                $total_HCP['BCS_Members_remaining'] = "-";
            }
            
            if($total_HCP['BCS_Members_remaining_insurance'] > 0 ){
                $total_HCP['BCS_Members_remaining_insurance'];
            }else{
                $total_HCP['BCS_Members_remaining_insurance'] = "-";
            }

            // End Breast Cancer Screening  ================> BCS <===========================

            //Start Controlling High Blood Pressure  ================> CBP <===========================
            $total_HCP['CBP_ClosedPatients'] = CareGaps::where('high_bp_gap','Compliant');
            $total_HCP['CBP_ClosedPatients_insurance'] = CareGaps::where('high_bp_gap_insurance','Compliant');
            
            $total_HCP['CBP_OpenPatients'] = CareGaps::where('high_bp_gap', '!=' ,'Compliant');
            $total_HCP['CBP_OpenPatients_insurance'] = CareGaps::where('high_bp_gap_insurance', '!=' ,'Compliant');

            $total_HCP['CBP_Total'] = CareGaps::WhereNull('deleted_at');//where('high_bp_gap','Compliant')->orWhere('high_bp_gap','Non-Compliant');
            $total_HCP['CBP_Total_insurance'] = CareGaps::WhereNull('deleted_at');//where('high_bp_gap_insurance','Compliant')->orWhere('high_bp_gap_insurance','Non-Compliant');

            if(!empty($doctor_id)){
                $total_HCP['CBP_ClosedPatients']->where('doctor_id',$doctor_id);
                $total_HCP['CBP_ClosedPatients_insurance']->where('doctor_id',$doctor_id);
                
                $total_HCP['CBP_OpenPatients']->where('doctor_id',$doctor_id);
                $total_HCP['CBP_OpenPatients_insurance']->where('doctor_id',$doctor_id);
                
                $total_HCP['CBP_Total']->where('doctor_id',$doctor_id);
                $total_HCP['CBP_Total_insurance']->where('doctor_id',$doctor_id);

            }
            if(!empty($insurance_id)){
                $total_HCP['CBP_ClosedPatients']->where('insurance_id',$insurance_id);
                $total_HCP['CBP_ClosedPatients_insurance']->where('insurance_id',$insurance_id);
                
                $total_HCP['CBP_OpenPatients']->where('insurance_id',$insurance_id);
                $total_HCP['CBP_OpenPatients_insurance']->where('insurance_id',$insurance_id);
                
                $total_HCP['CBP_Total']->where('insurance_id',$insurance_id);
                $total_HCP['CBP_Total_insurance']->where('insurance_id',$insurance_id);
            }
            if(!empty($clinic_id)){
                //$clinic_id = explode(',', $clinic_id);
                $total_HCP['CBP_ClosedPatients']->where('clinic_id',$clinic_id);
                $total_HCP['CBP_ClosedPatients_insurance']->where('clinic_id',$clinic_id);
                
                $total_HCP['CBP_OpenPatients']->where('clinic_id',$clinic_id);
                $total_HCP['CBP_OpenPatients_insurance']->where('clinic_id',$clinic_id);
                
                $total_HCP['CBP_Total']->where('clinic_id',$clinic_id);
                $total_HCP['CBP_Total_insurance']->where('clinic_id',$clinic_id);
            }
            $total_HCP['CBP_ClosedPatients'] = $total_HCP['CBP_ClosedPatients']->count();
            $total_HCP['CBP_ClosedPatients_insurance'] = $total_HCP['CBP_ClosedPatients_insurance']->count();

            $total_HCP['CBP_OpenPatients'] = $total_HCP['CBP_OpenPatients']->count();
            $total_HCP['CBP_OpenPatients_insurance'] = $total_HCP['CBP_OpenPatients_insurance']->count();
            
            $total_HCP['CBP_Total'] = $total_HCP['CBP_Total']->count();//->whereIn('high_bp_gap', ['Compliant', 'Non-Compliant'])->count();
            $total_HCP['CBP_Total_insurance'] = $total_HCP['CBP_Total_insurance']->count();//->whereIn('high_bp_gap_insurance', ['Compliant', 'Non-Compliant'])->count();

            if($total_HCP['CBP_Total'] != 0)
            {
                $total_HCP['CBP_Acheived'] = number_format( $total_HCP['CBP_ClosedPatients'] * 100 / $total_HCP['CBP_Total'] , 2, '.', '') ;
            }else{
                $total_HCP['CBP_Acheived'] = 0;
            }

            if($total_HCP['CBP_Total_insurance'] != 0)
            {
                $total_HCP['CBP_Acheived_insurance'] = number_format( $total_HCP['CBP_ClosedPatients_insurance'] * 100 / $total_HCP['CBP_Total_insurance'] , 2, '.', '') ;
            }else{
                $total_HCP['CBP_Acheived_insurance'] = 0;
            }

            $CBP_Required_Par = 76 ; 
            $total_HCP['CBP_Members_remaining'] = number_format( ((($CBP_Required_Par - $total_HCP['CBP_Acheived']) * $total_HCP['CBP_Total']) / 100) ) ;
            $total_HCP['CBP_Members_remaining_insurance'] = number_format( ((($CBP_Required_Par - $total_HCP['CBP_Acheived_insurance']) * $total_HCP['CBP_Total_insurance']) / 100) ) ;
            
            if($total_HCP['CBP_Members_remaining'] > 0 ){
                $total_HCP['CBP_Members_remaining'];
            }else{
                $total_HCP['CBP_Members_remaining'] = "-";
            }

            if($total_HCP['CBP_Members_remaining_insurance'] > 0 ){
                $total_HCP['CBP_Members_remaining_insurance'];
            }else{
                $total_HCP['CBP_Members_remaining_insurance'] = "-";
            }

            // End Controlling High Blood Pressure  ================> CBP <===========================

            //Start Kidney Health Evaluation  ================> KHE <===========================
            $total_HCP['KHE_ClosedPatients'] = CareGaps::where('kidney_health_gap','Compliant');
            $total_HCP['KHE_ClosedPatients_insurance'] = CareGaps::where('kidney_health_gap_insurance','Compliant');

            $total_HCP['KHE_OpenPatients'] = CareGaps::where('kidney_health_gap', '!=' ,'Compliant');
            $total_HCP['KHE_OpenPatients_insurance'] = CareGaps::where('kidney_health_gap_insurance', '!=' ,'Compliant');

            $total_HCP['KHE_Total'] = CareGaps::WhereNull('deleted_at');//where('kidney_health_gap','Compliant')->orWhere('kidney_health_gap','Non-Compliant');
            $total_HCP['KHE_Total_insurance'] = CareGaps::WhereNull('deleted_at');//where('kidney_health_gap_insurance','Compliant')->orWhere('kidney_health_gap_insurance','Non-Compliant');
            
            if(!empty($doctor_id)){
                $total_HCP['KHE_ClosedPatients']->where('doctor_id',$doctor_id);
                $total_HCP['KHE_ClosedPatients_insurance']->where('doctor_id',$doctor_id);
                
                $total_HCP['KHE_OpenPatients']->where('doctor_id',$doctor_id);
                $total_HCP['KHE_OpenPatients_insurance']->where('doctor_id',$doctor_id);
                
                $total_HCP['KHE_Total']->where('doctor_id',$doctor_id);
                $total_HCP['KHE_Total_insurance']->where('doctor_id',$doctor_id);
            }
            if(!empty($insurance_id)){
                $total_HCP['KHE_ClosedPatients']->where('insurance_id',$insurance_id);
                $total_HCP['KHE_ClosedPatients_insurance']->where('insurance_id',$insurance_id);
                
                $total_HCP['KHE_OpenPatients']->where('insurance_id',$insurance_id);
                $total_HCP['KHE_OpenPatients_insurance']->where('insurance_id',$insurance_id);
                
                $total_HCP['KHE_Total']->where('insurance_id',$insurance_id);
                $total_HCP['KHE_Total_insurance']->where('insurance_id',$insurance_id);
            }
            if(!empty($clinic_id)){
                //$clinic_id = explode(',', $clinic_id);
                $total_HCP['KHE_ClosedPatients']->where('clinic_id',$clinic_id);
                $total_HCP['KHE_ClosedPatients_insurance']->where('clinic_id',$clinic_id);
                
                $total_HCP['KHE_OpenPatients']->whereIn('clinic_id',$clinic_id);
                $total_HCP['KHE_OpenPatients_insurance']->where('clinic_id',$clinic_id);
                
                $total_HCP['KHE_Total']->whereIn('clinic_id',$clinic_id);
                $total_HCP['KHE_Total_insurance']->where('clinic_id',$clinic_id);
            }
            $total_HCP['KHE_ClosedPatients'] = $total_HCP['KHE_ClosedPatients']->count();
            $total_HCP['KHE_ClosedPatients_insurance'] = $total_HCP['KHE_ClosedPatients_insurance']->count();

            $total_HCP['KHE_OpenPatients'] = $total_HCP['KHE_OpenPatients']->count();
            $total_HCP['KHE_OpenPatients_insurance'] = $total_HCP['KHE_OpenPatients_insurance']->count();
            
            $total_HCP['KHE_Total'] = $total_HCP['KHE_Total']->count();//->whereIn('kidney_health_gap', ['Compliant', 'Non-Compliant'])->count();
            $total_HCP['KHE_Total_insurance'] = $total_HCP['KHE_Total_insurance']->count();//->whereIn('kidney_health_gap_insurance', ['Compliant', 'Non-Compliant'])->count();

            if($total_HCP['KHE_Total'] != 0)
            {
                $total_HCP['KHE_Acheived'] = number_format( $total_HCP['KHE_ClosedPatients'] * 100 / $total_HCP['KHE_Total'] , 2, '.', '') ;
            }else{
                $total_HCP['KHE_Acheived'] = 0;
            }
            
            if($total_HCP['KHE_Total'] != 0)
            {
                $total_HCP['KHE_Acheived_insurance'] = number_format( $total_HCP['KHE_ClosedPatients_insurance'] * 100 / $total_HCP['KHE_Total_insurance'] , 2, '.', '') ;
            }else{
                $total_HCP['KHE_Acheived_insurance'] = 0;
            }
            // $CBP_Required_Par =  100; 
            // $total_HCP['KHE_Members_remaining'] = number_format( ((($CBP_Required_Par - $total_HCP['KHE_Acheived']) * $total_HCP['KHE_Total']) / 100) ) ;

            // if($total_HCP['KHE_Members_remaining'] > 0 ){
            //     $total_HCP['KHE_Members_remaining'];
            // }else{
            //     $total_HCP['KHE_Members_remaining'] = "-";
            // }

            // End Kidney Health Evaluation  =============================> KHE  <===========================


            //Start  Blood Pressure Controlled <140/90 mm Hg  ================> BPC <===========================
            $total_HCP['BPC_ClosedPatients'] = CareGaps::where('bp_control_gap','Compliant');
            $total_HCP['BPC_ClosedPatients_insurance'] = CareGaps::where('bp_control_gap_insurance','Compliant');
            
            $total_HCP['BPC_OpenPatients'] = CareGaps::where('bp_control_gap', '!=' ,'Compliant');
            $total_HCP['BPC_OpenPatients_insurance'] = CareGaps::where('bp_control_gap_insurance', '!=' ,'Compliant');

            $total_HCP['BPC_Total'] = CareGaps::WhereNull('deleted_at');//where('bp_control_gap','Compliant')->orWhere('bp_control_gap','Non-Compliant');
            $total_HCP['BPC_Total_insurance'] = CareGaps::WhereNull('deleted_at');//where('bp_control_gap_insurance','Compliant')->orWhere('bp_control_gap_insurance','Non-Compliant');

            if(!empty($doctor_id)){
                $total_HCP['BPC_ClosedPatients']->where('doctor_id',$doctor_id);
                $total_HCP['BPC_ClosedPatients_insurance']->where('doctor_id',$doctor_id);
                
                $total_HCP['BPC_OpenPatients']->where('doctor_id',$doctor_id);
                $total_HCP['BPC_OpenPatients_insurance']->where('doctor_id',$doctor_id);
                
                $total_HCP['BPC_Total']->where('doctor_id',$doctor_id);
                $total_HCP['BPC_Total_insurance']->where('doctor_id',$doctor_id);
            }
            if(!empty($insurance_id)){
                $total_HCP['BPC_ClosedPatients']->where('insurance_id',$insurance_id);
                $total_HCP['BPC_ClosedPatients_insurance']->where('insurance_id',$insurance_id);
                
                $total_HCP['BPC_OpenPatients']->where('insurance_id',$insurance_id);
                $total_HCP['BPC_OpenPatients_insurance']->where('insurance_id',$insurance_id);
                
                $total_HCP['BPC_Total']->where('insurance_id',$insurance_id);
                $total_HCP['BPC_Total_insurance']->where('insurance_id',$insurance_id);
            }
            if(!empty($clinic_id)){
                $total_HCP['BPC_ClosedPatients']->where('clinic_id',$clinic_id);
                $total_HCP['BPC_ClosedPatients_insurance']->where('clinic_id',$clinic_id);
                
                $total_HCP['BPC_OpenPatients']->where('clinic_id',$clinic_id);
                $total_HCP['BPC_OpenPatients_insurance']->where('clinic_id',$clinic_id);
                
                $total_HCP['BPC_Total']->where('clinic_id',$clinic_id);
                $total_HCP['BPC_Total_insurance']->where('clinic_id',$clinic_id);
            }
            $total_HCP['BPC_ClosedPatients'] = $total_HCP['BPC_ClosedPatients']->count();
            $total_HCP['BPC_ClosedPatients_insurance'] = $total_HCP['BPC_ClosedPatients_insurance']->count();

            $total_HCP['BPC_OpenPatients'] = $total_HCP['BPC_OpenPatients']->count();
            $total_HCP['BPC_OpenPatients_insurance'] = $total_HCP['BPC_OpenPatients_insurance']->count();
            
            $total_HCP['BPC_Total'] = $total_HCP['BPC_Total']->count();//->whereIn('bp_control_gap', ['Compliant', 'Non-Compliant'])->count();
            //$total_HCP['BPC_OpenPatients'] + $total_HCP['BPC_ClosedPatients'];
            $total_HCP['BPC_Total_insurance'] = $total_HCP['BPC_Total_insurance']->count();//->whereIn('bp_control_gap_insurance', ['Compliant', 'Non-Compliant'])->count();
            //$total_HCP['BPC_OpenPatients_insurance'] + $total_HCP['BPC_ClosedPatients_insurance'];

            if($total_HCP['BPC_Total'] != 0)
            {
                $total_HCP['BPC_Acheived'] = number_format( $total_HCP['BPC_ClosedPatients'] * 100 / $total_HCP['BPC_Total'] , 2, '.', '') ;
            }else{
                $total_HCP['BPC_Acheived'] = 0;
            }

            if($total_HCP['BPC_Total_insurance'] != 0)
            {
                $total_HCP['BPC_Acheived_insurance'] = number_format( $total_HCP['BPC_ClosedPatients_insurance'] * 100 / $total_HCP['BPC_Total_insurance'] , 2, '.', '') ;
            }else{
                $total_HCP['BPC_Acheived_insurance'] = 0;
            }
            // $BPC_Required_Par =  100; 
            // $total_HCP['BPC_Members_remaining'] = number_format( ((($BPC_Required_Par - $total_HCP['BPC_Acheived']) * $total_HCP['BPC_Total']) / 100) ) ;

            // if($total_HCP['BPC_Members_remaining'] > 0 ){
            //     $total_HCP['BPC_Members_remaining'];
            // }else{
            //     $total_HCP['BPC_Members_remaining'] = "-";
            // }

            // End Blood Pressure Controlled <140/90 mm Hg  =============================> BPC  <===========================

            //Start  Blood Pressure Controlled <140/90 mm Hg  ================> BPC <===========================
            $total_HCP['BPC_ClosedPatients'] = CareGaps::where('bp_control_gap','Compliant');
            $total_HCP['BPC_ClosedPatients_insurance'] = CareGaps::where('bp_control_gap_insurance','Compliant');
            
            $total_HCP['BPC_OpenPatients'] = CareGaps::where('bp_control_gap', '!=' ,'Compliant');
            $total_HCP['BPC_OpenPatients_insurance'] = CareGaps::where('bp_control_gap_insurance', '!=' ,'Compliant');

            $total_HCP['BPC_Total'] = CareGaps::WhereNull('deleted_at');//where('bp_control_gap','Compliant')->orWhere('bp_control_gap','Non-Compliant');
            $total_HCP['BPC_Total_insurance'] = CareGaps::WhereNull('deleted_at');//where('bp_control_gap_insurance','Compliant')->orWhere('bp_control_gap_insurance','Non-Compliant');

            if(!empty($doctor_id)){
                $total_HCP['BPC_ClosedPatients']->where('doctor_id',$doctor_id);
                $total_HCP['BPC_ClosedPatients_insurance']->where('doctor_id',$doctor_id);
                
                $total_HCP['BPC_OpenPatients']->where('doctor_id',$doctor_id);
                $total_HCP['BPC_OpenPatients_insurance']->where('doctor_id',$doctor_id);
                
                $total_HCP['BPC_Total']->where('doctor_id',$doctor_id);
                $total_HCP['BPC_Total_insurance']->where('doctor_id',$doctor_id);
            }
            if(!empty($insurance_id)){
                $total_HCP['BPC_ClosedPatients']->where('insurance_id',$insurance_id);
                $total_HCP['BPC_ClosedPatients_insurance']->where('insurance_id',$insurance_id);
                
                $total_HCP['BPC_OpenPatients']->where('insurance_id',$insurance_id);
                $total_HCP['BPC_OpenPatients_insurance']->where('insurance_id',$insurance_id);
                
                $total_HCP['BPC_Total']->where('insurance_id',$insurance_id);
                $total_HCP['BPC_Total_insurance']->where('insurance_id',$insurance_id);
            }
            if(!empty($clinic_id)){
                $total_HCP['BPC_ClosedPatients']->where('clinic_id',$clinic_id);
                $total_HCP['BPC_ClosedPatients_insurance']->where('clinic_id',$clinic_id);
                
                $total_HCP['BPC_OpenPatients']->where('clinic_id',$clinic_id);
                $total_HCP['BPC_OpenPatients_insurance']->where('clinic_id',$clinic_id);
                
                $total_HCP['BPC_Total']->where('clinic_id',$clinic_id);
                $total_HCP['BPC_Total_insurance']->where('clinic_id',$clinic_id);
            }
            $total_HCP['BPC_ClosedPatients'] = $total_HCP['BPC_ClosedPatients']->count();
            $total_HCP['BPC_ClosedPatients_insurance'] = $total_HCP['BPC_ClosedPatients_insurance']->count();

            $total_HCP['BPC_OpenPatients'] = $total_HCP['BPC_OpenPatients']->count();
            $total_HCP['BPC_OpenPatients_insurance'] = $total_HCP['BPC_OpenPatients_insurance']->count();
            
            $total_HCP['BPC_Total'] = $total_HCP['BPC_Total']->count();//->whereIn('bp_control_gap', ['Compliant', 'Non-Compliant'])->count();
            //$total_HCP['BPC_OpenPatients'] + $total_HCP['BPC_ClosedPatients'];
            $total_HCP['BPC_Total_insurance'] = $total_HCP['BPC_Total_insurance']->count();//->whereIn('bp_control_gap_insurance', ['Compliant', 'Non-Compliant'])->count();
            //$total_HCP['BPC_OpenPatients_insurance'] + $total_HCP['BPC_ClosedPatients_insurance'];

            if($total_HCP['BPC_Total'] != 0)
            {
                $total_HCP['BPC_Acheived'] = number_format( $total_HCP['BPC_ClosedPatients'] * 100 / $total_HCP['BPC_Total'] , 2, '.', '') ;
            }else{
                $total_HCP['BPC_Acheived'] = 0;
            }

            if($total_HCP['BPC_Total_insurance'] != 0)
            {
                $total_HCP['BPC_Acheived_insurance'] = number_format( $total_HCP['BPC_ClosedPatients_insurance'] * 100 / $total_HCP['BPC_Total_insurance'] , 2, '.', '') ;
            }else{
                $total_HCP['BPC_Acheived_insurance'] = 0;
            }
            // $BPC_Required_Par =  100; 
            // $total_HCP['BPC_Members_remaining'] = number_format( ((($BPC_Required_Par - $total_HCP['BPC_Acheived']) * $total_HCP['BPC_Total']) / 100) ) ;

            // if($total_HCP['BPC_Members_remaining'] > 0 ){
            //     $total_HCP['BPC_Members_remaining'];
            // }else{
            //     $total_HCP['BPC_Members_remaining'] = "-";
            // }

            // End Blood Pressure Controlled <140/90 mm Hg  =============================> BPC  <===========================




            //Start  Care For Older Adults Functional  ================>  Adults  Func Gap <===========================
            $total_HCP['AdultsFunc_ClosedPatients'] = CareGaps::where('adults_func_gap','Compliant');
            $total_HCP['AdultsFunc_ClosedPatients_insurance'] = CareGaps::where('adults_func_gap_insurance','Compliant');

            $total_HCP['AdultsFunc_OpenPatients'] = CareGaps::where('adults_func_gap', '!=' ,'Compliant');
            $total_HCP['AdultsFunc_OpenPatients_insurance'] = CareGaps::where('adults_func_gap_insurance', '!=' ,'Compliant');

            $total_HCP['AdultsFunc_Total'] = CareGaps::WhereNull('deleted_at');//where('adults_func_gap','Compliant')->orWhere('adults_func_gap','Non-Compliant');//->count();
            $total_HCP['AdultsFunc_Total_insurance'] = CareGaps::WhereNull('deleted_at');//where('adults_func_gap_insurance','Compliant')->orWhere('adults_func_gap_insurance','Non-Compliant');//->count();


            if(!empty($doctor_id)){
                $total_HCP['AdultsFunc_ClosedPatients']->where('doctor_id',$doctor_id);
                $total_HCP['AdultsFunc_ClosedPatients_insurance']->where('doctor_id',$doctor_id);
                
                $total_HCP['AdultsFunc_OpenPatients']->where('doctor_id',$doctor_id);
                $total_HCP['AdultsFunc_OpenPatients_insurance']->where('doctor_id',$doctor_id);
                
                $total_HCP['AdultsFunc_Total']->where('doctor_id',$doctor_id);
                $total_HCP['AdultsFunc_Total_insurance']->where('doctor_id',$doctor_id);
            }
            if(!empty($insurance_id)){
                $total_HCP['AdultsFunc_ClosedPatients']->where('insurance_id',$insurance_id);
                $total_HCP['AdultsFunc_ClosedPatients_insurance']->where('insurance_id',$insurance_id);
                
                $total_HCP['AdultsFunc_OpenPatients']->where('insurance_id',$insurance_id);
                $total_HCP['AdultsFunc_OpenPatients_insurance']->where('insurance_id',$insurance_id);
                
                $total_HCP['AdultsFunc_Total']->where('insurance_id',$insurance_id);
                $total_HCP['AdultsFunc_Total_insurance']->where('insurance_id',$insurance_id);
            }
            if(!empty($clinic_id)){
                //$clinic_id = explode(',', $clinic_id);
                $total_HCP['AdultsFunc_ClosedPatients']->where('clinic_id',$clinic_id);
                $total_HCP['AdultsFunc_ClosedPatients_insurance']->where('clinic_id',$clinic_id);

                $total_HCP['AdultsFunc_OpenPatients']->where('clinic_id',$clinic_id);
                $total_HCP['AdultsFunc_OpenPatients_insurance']->where('clinic_id',$clinic_id);

                $total_HCP['AdultsFunc_Total']->where('clinic_id',$clinic_id);
                $total_HCP['AdultsFunc_Total_insurance']->where('clinic_id',$clinic_id);
            }
            $total_HCP['AdultsFunc_ClosedPatients'] = $total_HCP['AdultsFunc_ClosedPatients']->count();
            $total_HCP['AdultsFunc_ClosedPatients_insurance'] = $total_HCP['AdultsFunc_ClosedPatients_insurance']->count();

            $total_HCP['AdultsFunc_OpenPatients'] = $total_HCP['AdultsFunc_OpenPatients']->count();
            $total_HCP['AdultsFunc_OpenPatients_insurance'] = $total_HCP['AdultsFunc_OpenPatients_insurance']->count();

            $total_HCP['AdultsFunc_Total'] = $total_HCP['AdultsFunc_Total']->count();//->whereIn('adults_func_gap', ['Compliant', 'Non-Compliant'])->count();
            $total_HCP['AdultsFunc_Total_insurance'] = $total_HCP['AdultsFunc_Total_insurance']->count();//->whereIn('adults_func_gap_insurance', ['Compliant', 'Non-Compliant'])->count();


            if($total_HCP['AdultsFunc_Total'] != 0)
            {
                $total_HCP['AdultsFunc_Acheived'] = number_format( $total_HCP['AdultsFunc_ClosedPatients'] * 100 / $total_HCP['AdultsFunc_Total'] , 2, '.', '') ;
            }else{
                $total_HCP['AdultsFunc_Acheived'] = 0;
            }
            if($total_HCP['AdultsFunc_Total'] != 0)
            {
                $total_HCP['AdultsFunc_Acheived_insurance'] = number_format( $total_HCP['AdultsFunc_ClosedPatients_insurance'] * 100 / $total_HCP['AdultsFunc_Total_insurance'] , 2, '.', '') ;
            }else{
                $total_HCP['AdultsFunc_Acheived_insurance'] = 0;
            }
            $AdultsFunc_Required_Par = 86 ; 
            $total_HCP['AdultsFunc_Members_remaining'] = number_format( ((($AdultsFunc_Required_Par - $total_HCP['AdultsFunc_Acheived']) * $total_HCP['AdultsFunc_Total']) / 100) ) ;
            $total_HCP['AdultsFunc_Members_remaining_insurance'] = number_format( ((($AdultsFunc_Required_Par - $total_HCP['AdultsFunc_Acheived_insurance']) * $total_HCP['AdultsFunc_Total_insurance']) / 100) ) ;

            if($total_HCP['AdultsFunc_Members_remaining'] > 0 ){
                $total_HCP['AdultsFunc_Members_remaining'];
            }else{
                $total_HCP['AdultsFunc_Members_remaining'] = "-";
            }

            if($total_HCP['AdultsFunc_Members_remaining_insurance'] > 0 ){
                $total_HCP['AdultsFunc_Members_remaining_insurance'];
            }else{
                $total_HCP['AdultsFunc_Members_remaining_insurance'] = "-";
                        }
            // End Care For Older Adults Functional   ================>  Adults  Func <===========================


            //Start Transitions of Care-Medication Reconciliation Post-Discharge  ================>  Post Disc<===========================
            $total_HCP['PostDisc_ClosedPatients'] = CareGaps::where('post_disc_gap','Compliant');
            $total_HCP['PostDisc_ClosedPatients_insurance'] = CareGaps::where('post_disc_gap_insurance','Compliant');

            $total_HCP['PostDisc_OpenPatients'] = CareGaps::where('post_disc_gap', '!=' ,'Compliant');
            $total_HCP['PostDisc_OpenPatients_insurance'] = CareGaps::where('post_disc_gap_insurance', '!=' ,'Compliant');

            $total_HCP['PostDisc_Total'] = CareGaps::WhereNull('deleted_at');//where('post_disc_gap','Compliant')->orWhere('post_disc_gap','Non-Compliant');//->count();
            $total_HCP['PostDisc_Total_insurance'] = CareGaps::WhereNull('deleted_at');//where('post_disc_gap_insurance','Compliant')->orWhere('post_disc_gap_insurance','Non-Compliant');//->count();


            if(!empty($doctor_id)){
                $total_HCP['PostDisc_ClosedPatients']->where('doctor_id',$doctor_id);
                $total_HCP['PostDisc_ClosedPatients_insurance']->where('doctor_id',$doctor_id);
                
                $total_HCP['PostDisc_OpenPatients']->where('doctor_id',$doctor_id);
                $total_HCP['PostDisc_OpenPatients_insurance']->where('doctor_id',$doctor_id);
                
                $total_HCP['PostDisc_Total']->where('doctor_id',$doctor_id);
                $total_HCP['PostDisc_Total_insurance']->where('doctor_id',$doctor_id);
            }
            if(!empty($insurance_id)){
                $total_HCP['PostDisc_ClosedPatients']->where('insurance_id',$insurance_id);
                $total_HCP['PostDisc_ClosedPatients_insurance']->where('insurance_id',$insurance_id);
                
                $total_HCP['PostDisc_OpenPatients']->where('insurance_id',$insurance_id);
                $total_HCP['PostDisc_OpenPatients_insurance']->where('insurance_id',$insurance_id);
                
                $total_HCP['PostDisc_Total']->where('insurance_id',$insurance_id);
                $total_HCP['PostDisc_Total_insurance']->where('insurance_id',$insurance_id);
            }
            if(!empty($clinic_id)){
                //$clinic_id = explode(',', $clinic_id);
                $total_HCP['PostDisc_ClosedPatients']->where('clinic_id',$clinic_id);
                $total_HCP['PostDisc_ClosedPatients_insurance']->where('clinic_id',$clinic_id);

                $total_HCP['PostDisc_OpenPatients']->where('clinic_id',$clinic_id);
                $total_HCP['PostDisc_OpenPatients_insurance']->where('clinic_id',$clinic_id);

                $total_HCP['PostDisc_Total']->where('clinic_id',$clinic_id);
                $total_HCP['PostDisc_Total_insurance']->where('clinic_id',$clinic_id);
            }
            $total_HCP['PostDisc_ClosedPatients'] = $total_HCP['PostDisc_ClosedPatients']->count();
            $total_HCP['PostDisc_ClosedPatients_insurance'] = $total_HCP['PostDisc_ClosedPatients_insurance']->count();

            $total_HCP['PostDisc_OpenPatients'] = $total_HCP['PostDisc_OpenPatients']->count();
            $total_HCP['PostDisc_OpenPatients_insurance'] = $total_HCP['PostDisc_OpenPatients_insurance']->count();

            $total_HCP['PostDisc_Total'] = $total_HCP['PostDisc_Total']->count();//->whereIn('post_disc_gap', ['Compliant', 'Non-Compliant'])->count();
            $total_HCP['PostDisc_Total_insurance'] = $total_HCP['PostDisc_Total_insurance']->count();//->whereIn('post_disc_gap_insurance', ['Compliant', 'Non-Compliant'])->count();


            if($total_HCP['PostDisc_Total'] != 0)
            {
                $total_HCP['PostDisc_Acheived'] = number_format( $total_HCP['PostDisc_ClosedPatients'] * 100 / $total_HCP['PostDisc_Total'] , 2, '.', '') ;
            }else{
                $total_HCP['PostDisc_Acheived'] = 0;
            }
            if($total_HCP['PostDisc_Total'] != 0)
            {
                $total_HCP['PostDisc_Acheived_insurance'] = number_format( $total_HCP['PostDisc_ClosedPatients_insurance'] * 100 / $total_HCP['PostDisc_Total_insurance'] , 2, '.', '') ;
            }else{
                $total_HCP['PostDisc_Acheived_insurance'] = 0;
            }
            $PostDisc_Required_Par = 74 ; 
            $total_HCP['PostDisc_Members_remaining'] = number_format( ((($PostDisc_Required_Par - $total_HCP['PostDisc_Acheived']) * $total_HCP['PostDisc_Total']) / 100) ) ;
            $total_HCP['PostDisc_Members_remaining_insurance'] = number_format( ((($PostDisc_Required_Par - $total_HCP['PostDisc_Acheived_insurance']) * $total_HCP['PostDisc_Total_insurance']) / 100) ) ;

            if($total_HCP['PostDisc_Members_remaining'] > 0 ){
                $total_HCP['PostDisc_Members_remaining'];
            }else{
                $total_HCP['PostDisc_Members_remaining'] = "-";
            }

            if($total_HCP['PostDisc_Members_remaining_insurance'] > 0 ){
                $total_HCP['PostDisc_Members_remaining_insurance'];
            }else{
                $total_HCP['PostDisc_Members_remaining_insurance'] = "-";
                        }
            // End Transitions of Care-Medication Reconciliation Post-Discharge   ================>  Post Disc <===========================


            //Start Care For Older Adults Medication Review  ================>  Adults Medic <===========================
            $total_HCP['AdultsMedic_ClosedPatients'] = CareGaps::where('adults_medic_gap','Compliant');
            $total_HCP['AdultsMedic_ClosedPatients_insurance'] = CareGaps::where('adults_medic_gap_insurance','Compliant');

            $total_HCP['AdultsMedic_OpenPatients'] = CareGaps::where('adults_medic_gap', '!=' ,'Compliant');
            $total_HCP['AdultsMedic_OpenPatients_insurance'] = CareGaps::where('adults_medic_gap_insurance', '!=' ,'Compliant');

            $total_HCP['AdultsMedic_Total'] = CareGaps::WhereNull('deleted_at');//where('adults_medic_gap','Compliant')->orWhere('adults_medic_gap','Non-Compliant');//->count();
            $total_HCP['AdultsMedic_Total_insurance'] = CareGaps::WhereNull('deleted_at');//where('adults_medic_gap_insurance','Compliant')->orWhere('adults_medic_gap_insurance','Non-Compliant');//->count();


            if(!empty($doctor_id)){
                $total_HCP['AdultsMedic_ClosedPatients']->where('doctor_id',$doctor_id);
                $total_HCP['AdultsMedic_ClosedPatients_insurance']->where('doctor_id',$doctor_id);
                
                $total_HCP['AdultsMedic_OpenPatients']->where('doctor_id',$doctor_id);
                $total_HCP['AdultsMedic_OpenPatients_insurance']->where('doctor_id',$doctor_id);
                
                $total_HCP['AdultsMedic_Total']->where('doctor_id',$doctor_id);
                $total_HCP['AdultsMedic_Total_insurance']->where('doctor_id',$doctor_id);
            }
            if(!empty($insurance_id)){
                $total_HCP['AdultsMedic_ClosedPatients']->where('insurance_id',$insurance_id);
                $total_HCP['AdultsMedic_ClosedPatients_insurance']->where('insurance_id',$insurance_id);
                
                $total_HCP['AdultsMedic_OpenPatients']->where('insurance_id',$insurance_id);
                $total_HCP['AdultsMedic_OpenPatients_insurance']->where('insurance_id',$insurance_id);
                
                $total_HCP['AdultsMedic_Total']->where('insurance_id',$insurance_id);
                $total_HCP['AdultsMedic_Total_insurance']->where('insurance_id',$insurance_id);
            }
            if(!empty($clinic_id)){
                //$clinic_id = explode(',', $clinic_id);
                $total_HCP['AdultsMedic_ClosedPatients']->where('clinic_id',$clinic_id);
                $total_HCP['AdultsMedic_ClosedPatients_insurance']->where('clinic_id',$clinic_id);

                $total_HCP['AdultsMedic_OpenPatients']->where('clinic_id',$clinic_id);
                $total_HCP['AdultsMedic_OpenPatients_insurance']->where('clinic_id',$clinic_id);

                $total_HCP['AdultsMedic_Total']->where('clinic_id',$clinic_id);
                $total_HCP['AdultsMedic_Total_insurance']->where('clinic_id',$clinic_id);
            }
            $total_HCP['AdultsMedic_ClosedPatients'] = $total_HCP['AdultsMedic_ClosedPatients']->count();
            $total_HCP['AdultsMedic_ClosedPatients_insurance'] = $total_HCP['AdultsMedic_ClosedPatients_insurance']->count();

            $total_HCP['AdultsMedic_OpenPatients'] = $total_HCP['AdultsMedic_OpenPatients']->count();
            $total_HCP['AdultsMedic_OpenPatients_insurance'] = $total_HCP['AdultsMedic_OpenPatients_insurance']->count();

            $total_HCP['AdultsMedic_Total'] = $total_HCP['AdultsMedic_Total']->count();//->whereIn('adults_medic_gap', ['Compliant', 'Non-Compliant'])->count();
            $total_HCP['AdultsMedic_Total_insurance'] = $total_HCP['AdultsMedic_Total_insurance']->count();//->whereIn('adults_medic_gap_insurance', ['Compliant', 'Non-Compliant'])->count();


            if($total_HCP['AdultsMedic_Total'] != 0)
            {
                $total_HCP['AdultsMedic_Acheived'] = number_format( $total_HCP['AdultsMedic_ClosedPatients'] * 100 / $total_HCP['AdultsMedic_Total'] , 2, '.', '') ;
            }else{
                $total_HCP['AdultsMedic_Acheived'] = 0;
            }
            if($total_HCP['AdultsMedic_Total'] != 0)
            {
                $total_HCP['AdultsMedic_Acheived_insurance'] = number_format( $total_HCP['AdultsMedic_ClosedPatients_insurance'] * 100 / $total_HCP['AdultsMedic_Total_insurance'] , 2, '.', '') ;
            }else{
                $total_HCP['AdultsMedic_Acheived_insurance'] = 0;
            }
            $AdultsMedic_Required_Par = 82 ; 
            $total_HCP['AdultsMedic_Members_remaining'] = number_format( ((($AdultsMedic_Required_Par - $total_HCP['AdultsMedic_Acheived']) * $total_HCP['AdultsMedic_Total']) / 100) ) ;
            $total_HCP['AdultsMedic_Members_remaining_insurance'] = number_format( ((($AdultsMedic_Required_Par - $total_HCP['AdultsMedic_Acheived_insurance']) * $total_HCP['AdultsMedic_Total_insurance']) / 100) ) ;

            if($total_HCP['AdultsMedic_Members_remaining'] > 0 ){
                $total_HCP['AdultsMedic_Members_remaining'];
            }else{
                $total_HCP['AdultsMedic_Members_remaining'] = "-";
            }

            if($total_HCP['AdultsMedic_Members_remaining_insurance'] > 0 ){
                $total_HCP['AdultsMedic_Members_remaining_insurance'];
            }else{
                $total_HCP['AdultsMedic_Members_remaining_insurance'] = "-";
                        }
            // End Care For Older Adults Medication Review  ================>  Adults Medic <===========================



 //Start Transitions of Care-Medication Reconciliation After Inp-Discharge  ================>  After Inp-Disc<===========================
            $total_HCP['AfterInpDisc_ClosedPatients'] = CareGaps::where('after_inp_disc_gap','Compliant');
            $total_HCP['AfterInpDisc_ClosedPatients_insurance'] = CareGaps::where('after_inp_disc_gap_insurance','Compliant');

            $total_HCP['AfterInpDisc_OpenPatients'] = CareGaps::where('after_inp_disc_gap', '!=' ,'Compliant');
            $total_HCP['AfterInpDisc_OpenPatients_insurance'] = CareGaps::where('after_inp_disc_gap_insurance', '!=' ,'Compliant');

            $total_HCP['AfterInpDisc_Total'] = CareGaps::WhereNull('deleted_at');//where('after_inp_disc_gap','Compliant')->orWhere('after_inp_disc_gap','Non-Compliant');//->count();
            $total_HCP['AfterInpDisc_Total_insurance'] = CareGaps::WhereNull('deleted_at');//where('after_inp_disc_gap_insurance','Compliant')->orWhere('after_inp_disc_gap_insurance','Non-Compliant');//->count();


            if(!empty($doctor_id)){
                $total_HCP['AfterInpDisc_ClosedPatients']->where('doctor_id',$doctor_id);
                $total_HCP['AfterInpDisc_ClosedPatients_insurance']->where('doctor_id',$doctor_id);
                
                $total_HCP['AfterInpDisc_OpenPatients']->where('doctor_id',$doctor_id);
                $total_HCP['AfterInpDisc_OpenPatients_insurance']->where('doctor_id',$doctor_id);
                
                $total_HCP['AfterInpDisc_Total']->where('doctor_id',$doctor_id);
                $total_HCP['AfterInpDisc_Total_insurance']->where('doctor_id',$doctor_id);
            }
            if(!empty($insurance_id)){
                $total_HCP['AfterInpDisc_ClosedPatients']->where('insurance_id',$insurance_id);
                $total_HCP['AfterInpDisc_ClosedPatients_insurance']->where('insurance_id',$insurance_id);
                
                $total_HCP['AfterInpDisc_OpenPatients']->where('insurance_id',$insurance_id);
                $total_HCP['AfterInpDisc_OpenPatients_insurance']->where('insurance_id',$insurance_id);
                
                $total_HCP['AfterInpDisc_Total']->where('insurance_id',$insurance_id);
                $total_HCP['AfterInpDisc_Total_insurance']->where('insurance_id',$insurance_id);
            }
            if(!empty($clinic_id)){
                //$clinic_id = explode(',', $clinic_id);
                $total_HCP['AfterInpDisc_ClosedPatients']->where('clinic_id',$clinic_id);
                $total_HCP['AfterInpDisc_ClosedPatients_insurance']->where('clinic_id',$clinic_id);

                $total_HCP['AfterInpDisc_OpenPatients']->where('clinic_id',$clinic_id);
                $total_HCP['AfterInpDisc_OpenPatients_insurance']->where('clinic_id',$clinic_id);

                $total_HCP['AfterInpDisc_Total']->where('clinic_id',$clinic_id);
                $total_HCP['AfterInpDisc_Total_insurance']->where('clinic_id',$clinic_id);
            }
            $total_HCP['AfterInpDisc_ClosedPatients'] = $total_HCP['AfterInpDisc_ClosedPatients']->count();
            $total_HCP['AfterInpDisc_ClosedPatients_insurance'] = $total_HCP['AfterInpDisc_ClosedPatients_insurance']->count();

            $total_HCP['AfterInpDisc_OpenPatients'] = $total_HCP['AfterInpDisc_OpenPatients']->count();
            $total_HCP['AfterInpDisc_OpenPatients_insurance'] = $total_HCP['AfterInpDisc_OpenPatients_insurance']->count();

            $total_HCP['AfterInpDisc_Total'] = $total_HCP['AfterInpDisc_Total']->count();//->whereIn('after_inp_disc_gap', ['Compliant', 'Non-Compliant'])->count();
            $total_HCP['AfterInpDisc_Total_insurance'] = $total_HCP['AfterInpDisc_Total_insurance']->count();//->whereIn('after_inp_disc_gap_insurance', ['Compliant', 'Non-Compliant'])->count();


            if($total_HCP['AfterInpDisc_Total'] != 0)
            {
                $total_HCP['AfterInpDisc_Acheived'] = number_format( $total_HCP['AfterInpDisc_ClosedPatients'] * 100 / $total_HCP['AfterInpDisc_Total'] , 2, '.', '') ;
            }else{
                $total_HCP['AfterInpDisc_Acheived'] = 0;
            }
            if($total_HCP['AfterInpDisc_Total'] != 0)
            {
                $total_HCP['AfterInpDisc_Acheived_insurance'] = number_format( $total_HCP['AfterInpDisc_ClosedPatients_insurance'] * 100 / $total_HCP['AfterInpDisc_Total_insurance'] , 2, '.', '') ;
            }else{
                $total_HCP['AfterInpDisc_Acheived_insurance'] = 0;
            }
            $AfterInpDisc_Required_Par = 87 ; 
            $total_HCP['AfterInpDisc_Members_remaining'] = number_format( ((($AfterInpDisc_Required_Par - $total_HCP['AfterInpDisc_Acheived']) * $total_HCP['AfterInpDisc_Total']) / 100) ) ;
            $total_HCP['AfterInpDisc_Members_remaining_insurance'] = number_format( ((($AfterInpDisc_Required_Par - $total_HCP['AfterInpDisc_Acheived_insurance']) * $total_HCP['AfterInpDisc_Total_insurance']) / 100) ) ;

            if($total_HCP['AfterInpDisc_Members_remaining'] > 0 ){
                $total_HCP['AfterInpDisc_Members_remaining'];
            }else{
                $total_HCP['AfterInpDisc_Members_remaining'] = "-";
            }

            if($total_HCP['AfterInpDisc_Members_remaining_insurance'] > 0 ){
                $total_HCP['AfterInpDisc_Members_remaining_insurance'];
            }else{
                $total_HCP['AfterInpDisc_Members_remaining_insurance'] = "-";
            }
            // End Transitions of Care-Medication Reconciliation After Inp-Discharge  ================>  After Inp-Disc <===========================



            //Start Care for Older Adults - Pain Screening (Status)  ================>  Pain Screening <===========================
            $total_HCP['PainScreening_ClosedPatients'] = CareGaps::where('pain_screening_gap','Compliant');
            $total_HCP['PainScreening_ClosedPatients_insurance'] = CareGaps::where('pain_screening_gap_insurance','Compliant');

            $total_HCP['PainScreening_OpenPatients'] = CareGaps::where('pain_screening_gap', '!=' ,'Compliant');
            $total_HCP['PainScreening_OpenPatients_insurance'] = CareGaps::where('pain_screening_gap_insurance', '!=' ,'Compliant');

            $total_HCP['PainScreening_Total'] = CareGaps::WhereNull('deleted_at');//where('pain_screening_gap','Compliant')->orWhere('pain_screening_gap','Non-Compliant');//->count();
            $total_HCP['PainScreening_Total_insurance'] = CareGaps::WhereNull('deleted_at');//where('pain_screening_gap_insurance','Compliant')->orWhere('pain_screening_gap_insurance','Non-Compliant');//->count();


            if(!empty($doctor_id)){
                $total_HCP['PainScreening_ClosedPatients']->where('doctor_id',$doctor_id);
                $total_HCP['PainScreening_ClosedPatients_insurance']->where('doctor_id',$doctor_id);
                
                $total_HCP['PainScreening_OpenPatients']->where('doctor_id',$doctor_id);
                $total_HCP['PainScreening_OpenPatients_insurance']->where('doctor_id',$doctor_id);
                
                $total_HCP['PainScreening_Total']->where('doctor_id',$doctor_id);
                $total_HCP['PainScreening_Total_insurance']->where('doctor_id',$doctor_id);
            }
            if(!empty($insurance_id)){
                $total_HCP['PainScreening_ClosedPatients']->where('insurance_id',$insurance_id);
                $total_HCP['PainScreening_ClosedPatients_insurance']->where('insurance_id',$insurance_id);
                
                $total_HCP['PainScreening_OpenPatients']->where('insurance_id',$insurance_id);
                $total_HCP['PainScreening_OpenPatients_insurance']->where('insurance_id',$insurance_id);
                
                $total_HCP['PainScreening_Total']->where('insurance_id',$insurance_id);
                $total_HCP['PainScreening_Total_insurance']->where('insurance_id',$insurance_id);
            }
            if(!empty($clinic_id)){
                //$clinic_id = explode(',', $clinic_id);
                $total_HCP['PainScreening_ClosedPatients']->where('clinic_id',$clinic_id);
                $total_HCP['PainScreening_ClosedPatients_insurance']->where('clinic_id',$clinic_id);

                $total_HCP['PainScreening_OpenPatients']->where('clinic_id',$clinic_id);
                $total_HCP['PainScreening_OpenPatients_insurance']->where('clinic_id',$clinic_id);

                $total_HCP['PainScreening_Total']->where('clinic_id',$clinic_id);
                $total_HCP['PainScreening_Total_insurance']->where('clinic_id',$clinic_id);
            }
            $total_HCP['PainScreening_ClosedPatients'] = $total_HCP['PainScreening_ClosedPatients']->count();
            $total_HCP['PainScreening_ClosedPatients_insurance'] = $total_HCP['PainScreening_ClosedPatients_insurance']->count();

            $total_HCP['PainScreening_OpenPatients'] = $total_HCP['PainScreening_OpenPatients']->count();
            $total_HCP['PainScreening_OpenPatients_insurance'] = $total_HCP['PainScreening_OpenPatients_insurance']->count();

            $total_HCP['PainScreening_Total'] = $total_HCP['PainScreening_Total']->count();//->whereIn('pain_screening_gap', ['Compliant', 'Non-Compliant'])->count();
            $total_HCP['PainScreening_Total_insurance'] = $total_HCP['PainScreening_Total_insurance']->count();//->whereIn('pain_screening_gap_insurance', ['Compliant', 'Non-Compliant'])->count();


            if($total_HCP['PainScreening_Total'] != 0)
            {
                $total_HCP['PainScreening_Acheived'] = number_format( $total_HCP['PainScreening_ClosedPatients'] * 100 / $total_HCP['PainScreening_Total'] , 2, '.', '') ;
            }else{
                $total_HCP['PainScreening_Acheived'] = 0;
            }
            if($total_HCP['PainScreening_Total'] != 0)
            {
                $total_HCP['PainScreening_Acheived_insurance'] = number_format( $total_HCP['PainScreening_ClosedPatients_insurance'] * 100 / $total_HCP['PainScreening_Total_insurance'] , 2, '.', '') ;
            }else{
                $total_HCP['PainScreening_Acheived_insurance'] = 0;
            }
            $PainScreening_Required_Par = 86 ; 
            $total_HCP['PainScreening_Members_remaining'] = number_format( ((($PainScreening_Required_Par - $total_HCP['PainScreening_Acheived']) * $total_HCP['PainScreening_Total']) / 100) ) ;
            $total_HCP['PainScreening_Members_remaining_insurance'] = number_format( ((($PainScreening_Required_Par - $total_HCP['PainScreening_Acheived_insurance']) * $total_HCP['PainScreening_Total_insurance']) / 100) ) ;

            if($total_HCP['PainScreening_Members_remaining'] > 0 ){
                $total_HCP['PainScreening_Members_remaining'];
            }else{
                $total_HCP['PainScreening_Members_remaining'] = "-";
            }

            if($total_HCP['PainScreening_Members_remaining_insurance'] > 0 ){
                $total_HCP['PainScreening_Members_remaining_insurance'];
            }else{
                $total_HCP['PainScreening_Members_remaining_insurance'] = "-";
            }
            // End Care for Older Adults - Pain Screening   ================>  Pain Screening <===========================



            //Start AWV  ================>  AWV   <===========================
            $total_HCP['AWV_ClosedPatients'] = CareGaps::where('awv_gap','Compliant');
            $total_HCP['AWV_ClosedPatients_insurance'] = CareGaps::where('awv_gap_insurance','Compliant');

            $total_HCP['AWV_OpenPatients'] = CareGaps::where('awv_gap', '!=' ,'Compliant');
            $total_HCP['AWV_OpenPatients_insurance'] = CareGaps::where('awv_gap_insurance', '!=' ,'Compliant');

            $total_HCP['AWV_Total'] = CareGaps::WhereNull('deleted_at');//where('awv_gap','Compliant')->orWhere('awv_gap','Non-Compliant');//->count();
            $total_HCP['AWV_Total_insurance'] = CareGaps::WhereNull('deleted_at');//where('awv_gap_insurance','Compliant')->orWhere('awv_gap_insurance','Non-Compliant');//->count();


            if(!empty($doctor_id)){
                $total_HCP['AWV_ClosedPatients']->where('doctor_id',$doctor_id);
                $total_HCP['AWV_ClosedPatients_insurance']->where('doctor_id',$doctor_id);
                
                $total_HCP['AWV_OpenPatients']->where('doctor_id',$doctor_id);
                $total_HCP['AWV_OpenPatients_insurance']->where('doctor_id',$doctor_id);
                
                $total_HCP['AWV_Total']->where('doctor_id',$doctor_id);
                $total_HCP['AWV_Total_insurance']->where('doctor_id',$doctor_id);
            }

            if(!empty($insurance_id)){
                $total_HCP['AWV_ClosedPatients']->where('insurance_id',$insurance_id);
                $total_HCP['AWV_ClosedPatients_insurance']->where('insurance_id',$insurance_id);
                
                $total_HCP['AWV_OpenPatients']->where('insurance_id',$insurance_id);
                $total_HCP['AWV_OpenPatients_insurance']->where('insurance_id',$insurance_id);
                
                $total_HCP['AWV_Total']->where('insurance_id',$insurance_id);
                $total_HCP['AWV_Total_insurance']->where('insurance_id',$insurance_id);
            }

            if(!empty($clinic_id)){
                //$clinic_id = explode(',', $clinic_id);
                $total_HCP['AWV_ClosedPatients']->where('clinic_id',$clinic_id);
                $total_HCP['AWV_ClosedPatients_insurance']->where('clinic_id',$clinic_id);

                $total_HCP['AWV_OpenPatients']->where('clinic_id',$clinic_id);
                $total_HCP['AWV_OpenPatients_insurance']->where('clinic_id',$clinic_id);

                $total_HCP['AWV_Total']->where('clinic_id',$clinic_id);
                $total_HCP['AWV_Total_insurance']->where('clinic_id',$clinic_id);
            }

            $total_HCP['AWV_ClosedPatients'] = $total_HCP['AWV_ClosedPatients']->count();
            $total_HCP['AWV_ClosedPatients_insurance'] = $total_HCP['AWV_ClosedPatients_insurance']->count();

            $total_HCP['AWV_OpenPatients'] = $total_HCP['AWV_OpenPatients']->count();
            $total_HCP['AWV_OpenPatients_insurance'] = $total_HCP['AWV_OpenPatients_insurance']->count();

            $total_HCP['AWV_Total'] = $total_HCP['AWV_Total']->count();//->whereIn('awv_gap', ['Compliant', 'Non-Compliant'])->count();
            $total_HCP['AWV_Total_insurance'] = $total_HCP['AWV_Total_insurance']->count();//->whereIn('awv_gap_insurance', ['Compliant', 'Non-Compliant'])->count();


            if($total_HCP['AWV_Total'] != 0) {
                $total_HCP['AWV_Acheived'] = number_format( $total_HCP['AWV_ClosedPatients'] * 100 / $total_HCP['AWV_Total'] , 2, '.', '') ;
            } else {
                $total_HCP['AWV_Acheived'] = 0;
            }

            if($total_HCP['AWV_Total'] != 0) {
                $total_HCP['AWV_Acheived_insurance'] = number_format( $total_HCP['AWV_ClosedPatients_insurance'] * 100 / $total_HCP['AWV_Total_insurance'] , 2, '.', '') ;
            } else {
                $total_HCP['AWV_Acheived_insurance'] = 0;
            }

            $AWV_Required_Par = 74 ; 
            $total_HCP['AWV_Members_remaining'] = number_format( ((($AWV_Required_Par - $total_HCP['AWV_Acheived']) * $total_HCP['AWV_Total']) / 100) ) ;
            $total_HCP['AWV_Members_remaining_insurance'] = number_format( ((($AWV_Required_Par - $total_HCP['AWV_Acheived_insurance']) * $total_HCP['AWV_Total_insurance']) / 100) ) ;

            if($total_HCP['AWV_Members_remaining'] > 0 ) {
                $total_HCP['AWV_Members_remaining'];
            } else {
                $total_HCP['AWV_Members_remaining'] = "-";
            }

            if($total_HCP['AWV_Members_remaining_insurance'] > 0 ) {
                $total_HCP['AWV_Members_remaining_insurance'];
            } else {
                $total_HCP['AWV_Members_remaining_insurance'] = "-";
            }
            // End AWV   ================>  AWV <===========================


            if(empty($careGap) || $careGap == 0 ) {
                // Start Colorectal Cancer Screening  ===========================> CCS  <=======================

                $CCS['Title'] = "Colorectal Cancer Screening (Status)";
                $CCS['ClosedPatients'] = CareGaps::where('colorectal_cancer_gap','Compliant');
                //$CCS['OpenPatients'] = CareGaps::whereIn('colorectal_cancer_gap',['Non-Compliant', 'N/A']);
                $CCS['OpenPatients'] = CareGaps::where('colorectal_cancer_gap', 'Non-Compliant');
                $column_name = 'colorectal_cancer_gap';
                // $aa = array();
                // $fPatient = array();
                // $aa = CareGaps::select('patient_id')->where('colorectal_cancer_gap','Non-Compliant')->get();//->where("created_at",">", $to);
                // foreach ($aa as $p) {
                //     $pat = $p->patient_id;
                //     $fPatient[] = Patients::where('id',$pat)->whereIn('status', [1, 2])->count();//->where("created_at",">", $to);
                //     }
                //     $counts = array_count_values($fPatient);
                //     // Get the count of 1
                //     $count = isset($counts[1]) ? $counts[1] : 0;
                //     // Output the count
                
                
                $CCS['Refused'] = CareGaps::where('colorectal_cancer_gap', 'like' , '%Refuse%');//where('colorectal_cancer_gap','Non-Compliant'); 
                $CCS['Scheduled'] = CareGaps::where('colorectal_cancer_gap', 'like' , '%Schedu%');
                $CCS['Total'] = CareGaps::whereIn('colorectal_cancer_gap', ['Compliant', 'Non-Compliant']);//WhereNull('deleted_at');//where('colorectal_cancer_gap','Compliant')->orWhere('colorectal_cancer_gap','Non-Compliant')->where('insurance_id',$insurance_id)->count();//where('colorectal_cancer_gap','Compliant');//->count();
                
                if(!empty($doctor_id)){
                    $doctor_id = (string) $doctor_id;
                    $CCS['ClosedPatients']->where('doctor_id',$doctor_id);
                    $CCS['OpenPatients']->where('doctor_id',$doctor_id); 
                    $CCS['Refused']->where('doctor_id',$doctor_id); 
                    $CCS['Scheduled']->where('doctor_id',$doctor_id);
                    $CCS['doctor_id'] = $doctor_id;
                    $CCS['Total']->where('doctor_id',$doctor_id);
                    
                }
                if(!empty($insurance_id)){
                    $CCS['ClosedPatients']->where('insurance_id',$insurance_id);
                    $CCS['OpenPatients']->where('insurance_id',$insurance_id);
                    $CCS['Refused']->where('insurance_id',$insurance_id);
                    $CCS['Scheduled']->where('insurance_id',$insurance_id);
                    $CCS['insurance_id'] = $insurance_id;
                    $CCS['Total']->where('insurance_id',$insurance_id);
                }
                if(!empty($clinic_id)){
                    //$clinic_id = explode(',', $clinic_id);
                    $CCS['ClosedPatients']->where('clinic_id',$clinic_id);
                    $CCS['OpenPatients']->where('clinic_id',$clinic_id);
                    $CCS['Refused']->where('clinic_id',$clinic_id);
                    $CCS['Scheduled']->where('clinic_id',$clinic_id);
                    $CCS['clinic_id'] = $clinic_id;                
                    $CCS['Total']->where('clinic_id',$clinic_id);
                }
                
                $CCS['ActiveNonComp'] = $this->ActiveNonComp($column_name,$CCS);
                $CCS['ClosedPatients'] = $CCS['ClosedPatients']->count();
                $CCS['ClosedPatients'] = (string) $CCS['ClosedPatients'];
                $CCS['OpenPatients'] = $CCS['OpenPatients']->count();
                $CCS['OpenPatients'] = (string) $CCS['OpenPatients'];
                $CCS['Refused'] = $CCS['Refused']->count();
                $CCS['Refused'] = (string) $CCS['Refused'];
                $CCS['ActiveNonComp'] = (string) $CCS['ActiveNonComp'];
                $CCS['Scheduled'] = $CCS['Scheduled']->count();
                $CCS['Scheduled'] = (string) $CCS['Scheduled'];
                $CCS['UnScheduled'] = $CCS['ActiveNonComp'] - $CCS['Scheduled'] ;
                $CCS['UnScheduled'] = ($CCS['UnScheduled']>=1 ) ? (string) $CCS['UnScheduled'] : '0' ; 
                $CCS['Total'] = $CCS['Total']->count();
                $CCS['Total'] = (string) $CCS['Total'];

                if($CCS['Total'] != 0)
                {
                    $CCS['Acheived'] = number_format( $CCS['ClosedPatients'] * 100 / $CCS['Total'] , 1, '.', '') ;
                }
                else{
                    $CCS['Acheived'] = "0";
                }
                
                $CCS['Required_Par'] = "75" ; 

                if($CCS['Acheived'] >= 75){
                    $CCS['Star'] = "4";
                }else if($CCS['Acheived'] >= 56.25){
                    $CCS['Star'] = "3";
                }else if($CCS['Acheived'] >= 37.5){
                    $CCS['Star'] = "2";
                }else if($CCS['Acheived'] >= 18.75){
                    $CCS['Star'] = "1";
                }else{
                    $CCS['Star'] = "-";
                }

                $CCS['Members_remaining'] = number_format( ((($CCS['Required_Par'] - $CCS['Acheived']) * $CCS['Total']) / 100) ) ;
                if($CCS['Members_remaining'] > 0 ){
                    $CCS['Members_remaining'];
                }else{
                    $CCS['Members_remaining'] = "-";
                }


                // End Colorectal Cancer Screening  ===========================> CCS  <=======================  


                // Start Diabetes Care - Blood Sugar Control (CDC >9%) (Status) =========================> BSC <================================

                $BSC['Title'] = "Diabetes Care - Blood Sugar Control (CDC >9%) (Status)";
                $BSC['ClosedPatients'] = CareGaps::where('hba1c_gap','Compliant');
                //$BSC['OpenPatients'] = CareGaps::whereIn('hba1c_gap',['Non-Compliant', 'N/A']);//where('hba1c_gap','Non-Compliant');
                $BSC['OpenPatients'] = CareGaps::where('hba1c_gap', 'Non-Compliant');//where('hba1c_gap','Non-Compliant');
                $BSC['Refused'] = CareGaps::where('hba1c_gap', 'like' , '%Refuse%');
                $BSC['Scheduled'] = CareGaps::where('hba1c_gap', 'like' , '%Schedu%');
                $column_name = 'hba1c_gap';
                
                $BSC['Total'] = CareGaps::whereIn('hba1c_gap', ['Compliant', 'Non-Compliant']);//WhereNull('deleted_at');//where('hba1c_gap','Compliant')->orWhere('hba1c_gap','Non-Compliant');//->count();
                if(!empty($doctor_id)){
                    $BSC['ClosedPatients']->where('doctor_id',$doctor_id);
                    $BSC['OpenPatients']->where('doctor_id',$doctor_id);
                    $BSC['Refused']->where('doctor_id',$doctor_id);
                    $BSC['Scheduled']->where('doctor_id',$doctor_id);
                    $BSC['doctor_id'] = $doctor_id;
                    $BSC['Total']->where('doctor_id',$doctor_id);
                }
                if(!empty($insurance_id)){
                    $BSC['ClosedPatients']->where('insurance_id',$insurance_id);
                    $BSC['OpenPatients']->where('insurance_id',$insurance_id);
                    $BSC['Refused']->where('insurance_id',$insurance_id);
                    $BSC['Scheduled']->where('insurance_id',$insurance_id);
                    $BSC['insurance_id'] = $insurance_id;
                    $BSC['Total']->where('insurance_id',$insurance_id);
                }
                if(!empty($clinic_id)){
                    //$clinic_id = explode(',', $clinic_id);
                    $BSC['ClosedPatients']->where('clinic_id',$clinic_id);
                    $BSC['OpenPatients']->where('clinic_id',$clinic_id);
                    $BSC['Refused']->where('clinic_id',$clinic_id);
                    $BSC['Scheduled']->where('clinic_id',$clinic_id);
                    $BSC['clinic_id'] = $clinic_id;  
                    $BSC['Total']->where('clinic_id',$clinic_id);
                }
                $BSC['ActiveNonComp'] = $this->ActiveNonComp($column_name,$BSC);
                $BSC['ClosedPatients'] = $BSC['ClosedPatients']->count();
                $BSC['ClosedPatients'] = (string) $BSC['ClosedPatients'];
                $BSC['OpenPatients'] = $BSC['OpenPatients']->count();
                $BSC['OpenPatients'] = (string) $BSC['OpenPatients'];
                $BSC['Refused'] = $BSC['Refused']->count();
                $BSC['Refused'] = (string) $BSC['Refused'];
                $BSC['ActiveNonComp'] = (string) $BSC['ActiveNonComp'];
                $BSC['Scheduled'] = $BSC['Scheduled']->count();
                $BSC['Scheduled'] = (string) $BSC['Scheduled'];
                $BSC['UnScheduled'] = $BSC['ActiveNonComp'] - $BSC['Scheduled'] ;
                $BSC['UnScheduled'] = ($BSC['UnScheduled']>=1 ) ? (string) $BSC['UnScheduled'] : '0' ;
                $BSC['Total'] = $BSC['Total']->count();//->whereIn('hba1c_gap', ['Compliant', 'Non-Compliant'])->count();//CareGaps::where('hba1c_gap','Compliant')->orWhere('hba1c_gap','Non-Compliant')->count();
                $BSC['Total'] = (string) $BSC['Total'];
                if($BSC['Total'] != 0)
                {
                    $BSC['Acheived'] = number_format( $BSC['ClosedPatients'] * 100 / $BSC['Total'] , 1, '.', '') ;
                }else{
                    $BSC['Acheived'] = "0";
                }

                $BSC['Required_Par'] = "83" ; 

                if($BSC['Acheived'] >= 83){
                    $BSC['Star'] = "4";
                }else if($BSC['Acheived'] >= 62.25){
                    $BSC['Star'] = "3";
                }else if($BSC['Acheived'] >= 41.5){
                    $BSC['Star'] = "2";
                }else if($BSC['Acheived'] >= 20.75){
                    $BSC['Star'] = "1";
                }else{
                    $BSC['Star'] = "-";
                }

                $BSC['Members_remaining'] = number_format( ((($BSC['Required_Par'] - $BSC['Acheived']) * $BSC['Total']) / 100) ) ;
            
                if($BSC['Members_remaining'] > 0 ){
                    $BSC['Members_remaining'];
                }else{
                    $BSC['Members_remaining'] = "-";
                }


                // End Diabetes Care - Blood Sugar Control (CDC >9%) (Status) =========================> BSC <================================


                //Start  Diabetes Care - Eye Exam (Status)  ================> EyeExam <===========================

                $EyeExam['Title'] = "Diabetes Care - Eye Exam (Status)";
                $EyeExam['ClosedPatients'] = CareGaps::where('eye_exam_gap','Compliant');
                //$EyeExam['OpenPatients'] = CareGaps::whereIn('eye_exam_gap',['Non-Compliant', 'N/A']);
                $EyeExam['OpenPatients'] = CareGaps::where('eye_exam_gap', 'Non-Compliant');
                $EyeExam['Refused'] = CareGaps::where('eye_exam_gap', 'like' ,'%Refuse%');
                $EyeExam['Scheduled'] = CareGaps::where('eye_exam_gap', 'like' ,'%Schedu%');
                $column_name = 'eye_exam_gap';
                $EyeExam['Total'] = CareGaps::whereIn('eye_exam_gap', ['Compliant', 'Non-Compliant']);//WhereNull('deleted_at');//where('eye_exam_gap','Compliant')->orWhere('eye_exam_gap','Non-Compliant');//->count();
                
                if(!empty($doctor_id)){
                    $EyeExam['ClosedPatients']->where('doctor_id',$doctor_id);
                    $EyeExam['OpenPatients']->where('doctor_id',$doctor_id);
                    $EyeExam['Refused']->where('doctor_id',$doctor_id);
                    $EyeExam['Scheduled']->where('doctor_id',$doctor_id);
                    $EyeExam['doctor_id'] = $doctor_id;
                    $EyeExam['Total']->where('doctor_id',$doctor_id);
                }
                if(!empty($insurance_id)){
                    $EyeExam['ClosedPatients']->where('insurance_id',$insurance_id);
                    $EyeExam['OpenPatients']->where('insurance_id',$insurance_id);
                    $EyeExam['Refused']->where('insurance_id',$insurance_id);
                    $EyeExam['Scheduled']->where('insurance_id',$insurance_id);
                    $EyeExam['insurance_id'] = $insurance_id;
                    $EyeExam['Total']->where('insurance_id',$insurance_id);
                }
                if(!empty($clinic_id)){
                    //$clinic_id = explode(',', $clinic_id);
                    $EyeExam['ClosedPatients']->where('clinic_id',$clinic_id);
                    $EyeExam['OpenPatients']->where('clinic_id',$clinic_id);
                    $EyeExam['Refused']->where('clinic_id',$clinic_id);
                    $EyeExam['Scheduled']->where('clinic_id',$clinic_id);
                    $EyeExam['clinic_id'] = $clinic_id;  
                    $EyeExam['Total']->where('clinic_id',$clinic_id);
                }
                $EyeExam['ActiveNonComp'] = $this->ActiveNonComp($column_name,$EyeExam);
                $EyeExam['ClosedPatients'] = $EyeExam['ClosedPatients']->count();
                $EyeExam['ClosedPatients'] = (string) $EyeExam['ClosedPatients'];
                $EyeExam['OpenPatients'] = $EyeExam['OpenPatients']->count();
                $EyeExam['OpenPatients'] = (string) $EyeExam['OpenPatients'];
                $EyeExam['Refused'] = $EyeExam['Refused']->count();
                $EyeExam['Refused'] = (string) $EyeExam['Refused'];
                $EyeExam['ActiveNonComp'] = (string) $EyeExam['ActiveNonComp'];
                $EyeExam['Scheduled'] = $EyeExam['Scheduled']->count();
                $EyeExam['Scheduled'] = (string) $EyeExam['Scheduled'];
                $EyeExam['UnScheduled'] = $EyeExam['ActiveNonComp'] - $EyeExam['Scheduled'] ;
                $EyeExam['UnScheduled'] = ($EyeExam['UnScheduled']>=1 ) ? (string) $EyeExam['UnScheduled'] : '0' ;
                $EyeExam['Total'] = $EyeExam['Total']->count();//->whereIn('eye_exam_gap', ['Compliant', 'Non-Compliant'])->count();
                $EyeExam['Total'] = (string) $EyeExam['Total'];
                if($EyeExam['Total'] != 0)
                {
                    $EyeExam['Acheived'] = number_format( $EyeExam['ClosedPatients'] * 100 / $EyeExam['Total'] , 2, '.', '') ;
                }else{
                    $EyeExam['Acheived'] = "0";
                }
                
                $EyeExam['Required_Par'] = "73" ; 

                if($EyeExam['Acheived'] >= 73){
                    $EyeExam['Star'] = "4";
                }else if($EyeExam['Acheived'] >= 54.75){
                    $EyeExam['Star'] = "3";
                }else if($EyeExam['Acheived'] >= 36.5){
                    $EyeExam['Star'] = "2";
                }else if($EyeExam['Acheived'] >= 18.25){
                    $EyeExam['Star'] = "1";
                }else{
                    $EyeExam['Star'] = "-";
                }

                $EyeExam['Members_remaining'] = number_format( ((($EyeExam['Required_Par'] - $EyeExam['Acheived']) * $EyeExam['Total']) / 100) ) ;
                
                if($EyeExam['Members_remaining'] > 0 ){
                    $EyeExam['Members_remaining'];
                }else{
                    $EyeExam['Members_remaining'] = "-";
                }
                // End Diabetes Care - Eye Exam  ================> CDC4 <===========================


                //Start Breast Cancer Screening (Status)  ================> BCS <===========================
                $BCS['Title'] = "Breast Cancer Screening (Status)";
                $BCS['ClosedPatients'] = CareGaps::where('breast_cancer_gap','Compliant');
                //$BCS['OpenPatients'] = CareGaps::whereIn('breast_cancer_gap', ['Non-Compliant', 'N/A']);
                $BCS['OpenPatients'] = CareGaps::where('breast_cancer_gap', 'Non-Compliant');
                $column_name = 'breast_cancer_gap';
                $BCS['Refused'] = CareGaps::where('breast_cancer_gap', 'like' ,'%Refuse%');
                $BCS['Scheduled'] = CareGaps::where('breast_cancer_gap', 'like' ,'%Schedu%');
                $BCS['Total'] = CareGaps::whereIn('breast_cancer_gap', ['Compliant', 'Non-Compliant']);//WhereNull('deleted_at');//where('breast_cancer_gap','Compliant')->orWhere('breast_cancer_gap','Non-Compliant');
                if(!empty($doctor_id)){
                    $BCS['ClosedPatients']->where('doctor_id',$doctor_id);
                    $BCS['OpenPatients']->where('doctor_id',$doctor_id);
                    $BCS['Refused']->where('doctor_id',$doctor_id);
                    $BCS['Scheduled']->where('doctor_id',$doctor_id);
                    $BCS['doctor_id'] = $doctor_id;
                    $BCS['Total']->where('doctor_id',$doctor_id);
                }
                if(!empty($insurance_id)){
                    $BCS['ClosedPatients']->where('insurance_id',$insurance_id);
                    $BCS['OpenPatients']->where('insurance_id',$insurance_id);
                    $BCS['Refused']->where('insurance_id',$insurance_id);
                    $BCS['Scheduled']->where('insurance_id',$insurance_id);
                    $BCS['insurance_id'] = $insurance_id;
                    $BCS['Total']->where('insurance_id',$insurance_id);
                }
                if(!empty($clinic_id)){
                    //$clinic_id = explode(',', $clinic_id);
                    $BCS['ClosedPatients']->where('clinic_id',$clinic_id);
                    $BCS['ClosedPatients_insurance']->where('clinic_id',$clinic_id);
                    $BCS['Refused']->where('clinic_id',$clinic_id); 
                    $BCS['Scheduled']->where('clinic_id',$clinic_id); 
                    $BCS['clinic_id'] = $clinic_id ;  
                    $BCS['Total']->where('clinic_id',$clinic_id);
                }
                $BCS['ActiveNonComp'] = $this->ActiveNonComp($column_name,$BCS);
                $BCS['ClosedPatients'] = $BCS['ClosedPatients']->count();
                $BCS['ClosedPatients'] = (string) $BCS['ClosedPatients'];
                $BCS['OpenPatients'] = $BCS['OpenPatients']->count();
                $BCS['OpenPatients'] = (string) $BCS['OpenPatients'];
                $BCS['Refused'] = $BCS['Refused']->count();
                $BCS['Refused'] = (string) $BCS['Refused'];
                $BCS['ActiveNonComp'] = (string) $BCS['ActiveNonComp'];
                $BCS['Scheduled'] = $BCS['Scheduled']->count();
                $BCS['Scheduled'] = (string) $BCS['Scheduled'];
                $BCS['UnScheduled'] = $BCS['ActiveNonComp'] - $BCS['Scheduled'] ;
                $BCS['UnScheduled'] = ($BCS['UnScheduled']>=1 ) ? (string) $BCS['UnScheduled'] : '0' ;
                $BCS['Total'] = $BCS['Total']->count();//->whereIn('breast_cancer_gap', ['Compliant', 'Non-Compliant'])->count();
                $BCS['Total'] = (string) $BCS['Total'];
                if($BCS['Total'] != 0)
                {
                    $BCS['Acheived'] = number_format( $BCS['ClosedPatients'] * 100 / $BCS['Total'] , 1, '.', '') ;
                }else{
                    $BCS['Acheived'] = "0";
                }

                $BCS['Required_Par'] = "74" ; 

                if($BSC['Acheived'] >= 74){
                    $BSC['Star'] = "4";
                }else if($BSC['Acheived'] >= 55.5){
                    $BSC['Star'] = "3";
                }else if($BSC['Acheived'] >= 37){
                    $BSC['Star'] = "2";
                }else if($BSC['Acheived'] >= 18.5){
                    $BSC['Star'] = "1";
                }else{
                    $BSC['Star'] = "-";
                }

                $BCS['Members_remaining'] = number_format( ((($BCS['Required_Par'] - $BCS['Acheived']) * $BCS['Total']) / 100) ) ;
                
                if($BCS['Members_remaining'] > 0 ){
                    $BCS['Members_remaining'];
                }else{
                    $BCS['Members_remaining'] = "-";
                }

                // End Breast Cancer Screening  ================> BCS <===========================

                //Start Controlling High Blood Pressure (Status) ================> CHBP <===========================
                $CHBP['Title'] = "Controlling High Blood Pressure (Status)";
                $CHBP['ClosedPatients'] = CareGaps::where('high_bp_gap','Compliant');
                //$CHBP['OpenPatients'] = CareGaps::whereIn('high_bp_gap', ['Non-Compliant', 'N/A']);
                $CHBP['OpenPatients'] = CareGaps::where('high_bp_gap', 'Non-Compliant');
                $column_name = 'high_bp_gap';
                $CHBP['Refused'] = CareGaps::where('high_bp_gap', 'like' ,'%Refuse%');
                $CHBP['Scheduled'] = CareGaps::where('high_bp_gap', 'like' ,'%Schedu%');
                $CHBP['Total'] = CareGaps::whereIn('high_bp_gap', ['Compliant', 'Non-Compliant']);//WhereNull('deleted_at');//where('high_bp_gap','Compliant')->orWhere('high_bp_gap','Non-Compliant');
            
                if(!empty($doctor_id)){
                    $CHBP['ClosedPatients']->where('doctor_id',$doctor_id);
                    $CHBP['OpenPatients']->where('doctor_id',$doctor_id);
                    $CHBP['Refused']->where('doctor_id',$doctor_id);
                    $CHBP['Scheduled']->where('doctor_id',$doctor_id);
                    $CHBP['doctor_id'] = $doctor_id;
                    $CHBP['Total']->where('doctor_id',$doctor_id);
                }
                if(!empty($insurance_id)){
                    $CHBP['ClosedPatients']->where('insurance_id',$insurance_id);
                    $CHBP['OpenPatients']->where('insurance_id',$insurance_id);
                    $CHBP['Refused']->where('insurance_id',$insurance_id);
                    $CHBP['Scheduled']->where('insurance_id',$insurance_id);
                    $CHBP['insurance_id'] = $insurance_id;
                    $CHBP['Total']->where('insurance_id',$insurance_id);
                }
                if(!empty($clinic_id)){
                    //$clinic_id = explode(',', $clinic_id);
                    $CHBP['ClosedPatients']->where('clinic_id',$clinic_id);
                    $CHBP['OpenPatients']->where('clinic_id',$clinic_id);
                    $CHBP['Refused']->where('clinic_id',$clinic_id);
                    $CHBP['Scheduled']->where('clinic_id',$clinic_id); 
                    $CHBP['clinic_id'] = $clinic_id ;  
                    $CHBP['Total']->where('clinic_id',$clinic_id);
                }
                $CHBP['ActiveNonComp'] = $this->ActiveNonComp($column_name,$CHBP);
                $CHBP['ClosedPatients'] = $CHBP['ClosedPatients']->count();
                $CHBP['ClosedPatients'] = (string) $CHBP['ClosedPatients'];
                $CHBP['OpenPatients'] = $CHBP['OpenPatients']->count();
                $CHBP['OpenPatients'] = (string) $CHBP['OpenPatients'];
                $CHBP['Refused'] = $CHBP['Refused']->count();
                $CHBP['Refused'] = (string) $CHBP['Refused'];
                $CHBP['ActiveNonComp'] = (string) $CHBP['ActiveNonComp'];
                $CHBP['Scheduled'] = $CHBP['Scheduled']->count();
                $CHBP['Scheduled'] = (string) $CHBP['Scheduled'];
                $CHBP['UnScheduled'] = $CHBP['ActiveNonComp'] - $CHBP['Scheduled'] ;
                $CHBP['UnScheduled'] = ($CHBP['UnScheduled']>=1 ) ? (string) $CHBP['UnScheduled'] : '0' ;
                $CHBP['Total'] = $CHBP['Total']->count();//->whereIn('high_bp_gap', ['Compliant', 'Non-Compliant'])->count();
                $CHBP['Total'] = (string) $CHBP['Total'];
                if($CHBP['Total'] != 0)
                {
                    $CHBP['Acheived'] = number_format( $CHBP['ClosedPatients'] * 100 / $CHBP['Total'] , 2, '.', '') ;
                }else{
                    $CHBP['Acheived'] = "0";
                }

                $CHBP['Required_Par'] = "76" ; 

                if($CHBP['Acheived'] >= 76){
                    $CHBP['Star'] = "4";
                }else if($CHBP['Acheived'] >= 57){
                    $CHBP['Star'] = "3";
                }else if($CHBP['Acheived'] >= 38){
                    $CHBP['Star'] = "2";
                }else if($CHBP['Acheived'] >= 19){
                    $CHBP['Star'] = "1";
                }else{
                    $CHBP['Star'] = "-";
                }

                $CHBP['Members_remaining'] = number_format( ((($CHBP['Required_Par'] - $CHBP['Acheived']) * $CHBP['Total']) / 100) ) ;
                
                if($CHBP['Members_remaining'] > 0 ){
                    $CHBP['Members_remaining'];

                }else{
                    $CHBP['Members_remaining'] = "-";
                }

                // End Controlling High Blood Pressure  ================> CHBP <===========================

                //Start Kidney Health Evaluation for Patients With Diabetes - Kidney Health Evaluation (Status)  ================> KHE <===========================
                
                $KHE['Title'] = "Kidney Health Evaluation for Patients With Diabetes - Kidney Health Evaluation (Status)";
                $KHE['ClosedPatients'] = CareGaps::where('kidney_health_gap','Compliant');
                //$KHE['OpenPatients'] = CareGaps::whereIn('kidney_health_gap', ['Non-Compliant', 'N/A']);
                $KHE['OpenPatients'] = CareGaps::where('kidney_health_gap', 'Non-Compliant');
                $column_name = 'kidney_health_gap';
                $KHE['Refused'] = CareGaps::where('kidney_health_gap', 'like' ,'%Refuse%');
                $KHE['Scheduled'] = CareGaps::where('kidney_health_gap', 'like' ,'%Schedu%');
                $KHE['Total'] = CareGaps::whereIn('kidney_health_gap', ['Compliant', 'Non-Compliant']);//WhereNull('deleted_at');//where('kidney_health_gap','Compliant')->orWhere('kidney_health_gap','Non-Compliant');
                
                if(!empty($doctor_id)){
                    $KHE['ClosedPatients']->where('doctor_id',$doctor_id);
                    $KHE['OpenPatients']->where('doctor_id',$doctor_id);
                    $KHE['Refused']->where('doctor_id',$doctor_id);
                    $KHE['Scheduled']->where('doctor_id',$doctor_id);
                    $KHE['doctor_id'] = $doctor_id;
                    $KHE['Total']->where('doctor_id',$doctor_id);
                }
                if(!empty($insurance_id)){
                    $KHE['ClosedPatients']->where('insurance_id',$insurance_id);
                    $KHE['OpenPatients']->where('insurance_id',$insurance_id);
                    $KHE['Refused']->where('insurance_id',$insurance_id);
                    $KHE['Scheduled']->where('insurance_id',$insurance_id);
                    $KHE['insurance_id'] = $insurance_id;
                    $KHE['Total']->where('insurance_id',$insurance_id);
                }
                if(!empty($clinic_id)){
                    //$clinic_id = explode(',', $clinic_id);
                    $KHE['ClosedPatients']->where('clinic_id',$clinic_id);
                    $KHE['OpenPatients']->whereIn('clinic_id',$clinic_id);
                    $KHE['Refused']->where('clinic_id',$clinic_id); 
                    $KHE['Scheduled']->where('clinic_id',$clinic_id); 
                    $KHE['clinic_id'] = $clinic_id;  
                    $KHE['Total']->whereIn('clinic_id',$clinic_id);
                }
                $KHE['ActiveNonComp'] = $this->ActiveNonComp($column_name,$KHE);
                $KHE['ClosedPatients'] = $KHE['ClosedPatients']->count();
                $KHE['ClosedPatients'] = (string) $KHE['ClosedPatients'];
                $KHE['OpenPatients'] = $KHE['OpenPatients']->count();
                $KHE['OpenPatients'] = (string) $KHE['OpenPatients'];
                $KHE['Refused'] = $KHE['Refused']->count();
                $KHE['Refused'] = (string) $KHE['Refused'];
                $KHE['ActiveNonComp'] = (string) $KHE['ActiveNonComp'];
                $KHE['Scheduled'] = $KHE['Scheduled']->count();
                $KHE['Scheduled'] = (string) $KHE['Scheduled'];
                $KHE['UnScheduled'] = $KHE['ActiveNonComp'] - $KHE['Scheduled'] ;
                $KHE['UnScheduled'] = ($KHE['UnScheduled']>=1 ) ? (string) $KHE['UnScheduled'] : '0' ;
                $KHE['Total'] = $KHE['Total']->count();//->whereIn('kidney_health_gap', ['Compliant', 'Non-Compliant'])->count();
                $KHE['Total'] = (string) $KHE['Total'];
                if($KHE['Total'] != 0)
                {
                    $KHE['Acheived'] = number_format( $KHE['ClosedPatients'] * 100 / $KHE['Total'] , 1, '.', '') ;
                }else{
                    $KHE['Acheived'] = "0";
                }
                $KHE['Required_Par'] =  "-";
                $KHE['Star'] = "-";
                $KHE['Members_remaining'] = "-";
                
                // $KHE['Required_Par'] =  100; 

                // if($KHE['Acheived'] >= 83){
                //     $KHE['Star'] = "4";
                // }else if($KHE['Acheived'] >= 62.25){
                //     $KHE['Star'] = "3";
                // }else if($KHE['Acheived'] >= 41.5){
                //     $KHE['Star'] = "2";
                // }else if($KHE['Acheived'] >= 20.75){
                //     $KHE['Star'] = "1";
                // }else{
                //     $KHE['Star'] = "-";
                // }

                // $KHE['Members_remaining'] = number_format( ((($KHE['Required_Par'] - $KHE['Acheived']) * $KHE['Total']) / 100) ) ;

                // if($KHE['Members_remaining'] > 0 ){
                //     $KHE['Members_remaining'];
                // }else{
                //     $KHE['Members_remaining'] = "-";
                // }

                // End Kidney Health Evaluation  =============================> KHE  <===========================


                //Start  Blood Pressure Control for Patients With Diabetes - Blood Pressure Controlled <140/90 mm Hg  ================> BPC <===========================
                
                $BPC['Title'] = "Blood Pressure Control for Patients With Diabetes - Blood Pressure Controlled <140/90 mm Hg";
                $BPC['ClosedPatients'] = CareGaps::where('bp_control_gap','Compliant');
                //$BPC['OpenPatients'] = CareGaps::whereIn('bp_control_gap', ['Non-Compliant', 'N/A']);
                $BPC['OpenPatients'] = CareGaps::where('bp_control_gap', 'Non-Compliant');
                $column_name = 'bp_control_gap';
                $BPC['Refused'] = CareGaps::where('bp_control_gap', 'like' ,'%Refuse%');
                $BPC['Scheduled'] = CareGaps::where('bp_control_gap', 'like' ,'%Schedu%');
                $BPC['Total'] = CareGaps::whereIn('bp_control_gap', ['Compliant', 'Non-Compliant']);//WhereNull('deleted_at');//where('bp_control_gap','Compliant')->orWhere('bp_control_gap','Non-Compliant');
            
                if(!empty($doctor_id)){
                    $BPC['ClosedPatients']->where('doctor_id',$doctor_id);
                    $BPC['OpenPatients']->where('doctor_id',$doctor_id);
                    $BPC['Refused']->where('doctor_id',$doctor_id);
                    $BPC['Scheduled']->where('doctor_id',$doctor_id);
                    $BPC['doctor_id'] = $doctor_id;
                    $BPC['Total']->where('doctor_id',$doctor_id);
                }
                if(!empty($insurance_id)){
                    $BPC['ClosedPatients']->where('insurance_id',$insurance_id);
                    $BPC['OpenPatients']->where('insurance_id',$insurance_id);
                    $BPC['Refused']->where('insurance_id',$insurance_id);
                    $BPC['Scheduled']->where('insurance_id',$insurance_id);
                    $BPC['insurance_id'] = $insurance_id;
                    $BPC['Total']->where('insurance_id',$insurance_id);
                }
                if(!empty($clinic_id)){
                    $BPC['ClosedPatients']->where('clinic_id',$clinic_id);
                    $BPC['OpenPatients']->where('clinic_id',$clinic_id);
                    $BPC['Refused']->where('clinic_id',$clinic_id);
                    $BPC['Scheduled']->where('clinic_id',$clinic_id);
                    $BPC['clinic_id'] = $clinic_id;  
                    $BPC['Total']->where('clinic_id',$clinic_id);
                }
                $BPC['ActiveNonComp'] = $this->ActiveNonComp($column_name,$BPC);
                $BPC['ClosedPatients'] = $BPC['ClosedPatients']->count();
                $BPC['ClosedPatients'] = (string) $BPC['ClosedPatients'];
                $BPC['OpenPatients'] = $BPC['OpenPatients']->count();
                $BPC['OpenPatients'] = (string) $BPC['OpenPatients'];
                $BPC['Refused'] = $BPC['Refused']->count();
                $BPC['Refused'] = (string) $BPC['Refused'];
                $BPC['ActiveNonComp'] = (string) $BPC['ActiveNonComp'];
                $BPC['Scheduled'] = $BPC['Scheduled']->count();
                $BPC['Scheduled'] = (string) $BPC['Scheduled'];
                $BPC['UnScheduled'] = $BPC['ActiveNonComp'] - $BPC['Scheduled'] ;
                $BPC['UnScheduled'] = ($BPC['UnScheduled']>=1 ) ? (string) $BPC['UnScheduled'] : '0' ;
                $BPC['Total'] = $BPC['Total']->count();//->whereIn('bp_control_gap', ['Compliant', 'Non-Compliant'])->count();
                //$BPC['OpenPatients'] + $BPC['ClosedPatients'];
                $BPC['Total'] = (string) $BPC['Total'];
                if($BPC['Total'] != 0)
                {
                    $BPC['Acheived'] = number_format( $BPC['ClosedPatients'] * 100 / $BPC['Total'] , 1, '.', '') ;
                    
                }else{
                    $BPC['Acheived'] = "0";
                }
                $BPC['Required_Par'] =  "-"; 
                $BPC['Star'] = "-";
                $BPC['Members_remaining'] = "-";

                // $BPC['Required_Par'] =  100;
                
                // if($BPC['Acheived'] >= 83){
                //     $BPC['Star'] = "4";
                // }else if($BPC['Acheived'] >= 62.25){
                //     $BPC['Star'] = "3";
                // }else if($BPC['Acheived'] >= 41.5){
                //     $BPC['Star'] = "2";
                // }else if($BPC['Acheived'] >= 20.75){
                //     $BPC['Star'] = "1";
                // }else{
                //     $BPC['Star'] = "-";
                // }

                // $BPC['Members_remaining'] = number_format( ((($BPC['Required_Par'] - $BPC['Acheived']) * $BPC['Total']) / 100) ) ;

                // if($BPC['Members_remaining'] > 0 ){
                //     $BPC['Members_remaining'];
                // }else{
                //     $BPC['Members_remaining'] = "-";
                // }

                // End Blood Pressure Control for Patients With Diabetes - Blood Pressure Controlled <140/90 mm Hg  =============================> BPC  <===========================

                $healthChoicePathway = [
                    $CCS,
                    $BSC,
                    $EyeExam,
                    $BCS,
                    $CHBP,
                    $KHE,
                    $BPC,
                
                ];
            } elseif($careGap == 1 ) {              

                // Start Colorectal Cancer Screening  ===========================> CCS  <=======================

                $CCS['Title'] = "Colorectal Cancer Screening (Status)";
                $CCS['ClosedPatients'] = CareGaps::where('colorectal_cancer_gap_insurance','Compliant');
                //$CCS['OpenPatients'] = CareGaps::whereIn('colorectal_cancer_gap_insurance',['Non-Compliant', 'N/A']);
                $CCS['OpenPatients'] = CareGaps::where('colorectal_cancer_gap_insurance','Non-Compliant');
                $column_name = 'colorectal_cancer_gap_insurance';
                $CCS['Refused'] = CareGaps::where('colorectal_cancer_gap_insurance', 'like' , '%Refuse%');//where('colorectal_cancer_gap','Non-Compliant'); 
                $CCS['Scheduled'] = CareGaps::where('colorectal_cancer_gap_insurance', 'like' , '%Schedu%');//where('colorectal_cancer_gap','Non-Compliant'); 
                $CCS['Total'] = CareGaps::whereIn('colorectal_cancer_gap_insurance', ['Compliant', 'Non-Compliant']);//WhereNull('deleted_at');//where('colorectal_cancer_gap','Compliant')->orWhere('colorectal_cancer_gap','Non-Compliant')->where('insurance_id',$insurance_id)->count();//where('colorectal_cancer_gap','Compliant');//->count();
                
                if(!empty($doctor_id)){
                    $CCS['ClosedPatients']->where('doctor_id',$doctor_id);
                    $CCS['OpenPatients']->where('doctor_id',$doctor_id); 
                    $CCS['Refused']->where('doctor_id',$doctor_id); 
                    $CCS['Scheduled']->where('doctor_id',$doctor_id);
                    $CCS['doctor_id'] = $doctor_id;
                    $CCS['Total']->where('doctor_id',$doctor_id);
                    
                }

                if(!empty($insurance_id)){
                    $CCS['ClosedPatients']->where('insurance_id',$insurance_id);
                    $CCS['OpenPatients']->where('insurance_id',$insurance_id);
                    $CCS['Refused']->where('insurance_id',$insurance_id);
                    $CCS['Scheduled']->where('insurance_id',$insurance_id);
                    $CCS['insurance_id'] = $insurance_id;
                    $CCS['Total']->where('insurance_id',$insurance_id);
                }

                if(!empty($clinic_id)){
                    //$clinic_id = explode(',', $clinic_id);
                    $CCS['ClosedPatients']->where('clinic_id',$clinic_id);
                    $CCS['OpenPatients']->where('clinic_id',$clinic_id);
                    $CCS['Refused']->where('clinic_id',$clinic_id);
                    $CCS['Scheduled']->where('clinic_id',$clinic_id);
                    $CCS['clinic_id'] = $clinic_id;                
                    $CCS['Total']->where('clinic_id',$clinic_id);
                }

                $CCS['ActiveNonComp'] = $this->ActiveNonComp($column_name,$CCS);
                $CCS['ClosedPatients'] = $CCS['ClosedPatients']->count();
                $CCS['ClosedPatients'] = (string) $CCS['ClosedPatients'];
                $CCS['OpenPatients'] = $CCS['OpenPatients']->count();
                $CCS['OpenPatients'] = (string) $CCS['OpenPatients'];
                $CCS['Refused'] = $CCS['Refused']->count();
                $CCS['Refused'] = (string) $CCS['Refused'];
                $CCS['ActiveNonComp'] = (string) $CCS['ActiveNonComp'];
                $CCS['Scheduled'] = $CCS['Scheduled']->count();
                $CCS['Scheduled'] = (string) $CCS['Scheduled'];
                $CCS['UnScheduled'] = $CCS['ActiveNonComp'] - $CCS['Scheduled'] ;
                $CCS['UnScheduled'] = ($CCS['UnScheduled']>=1 ) ? (string) $CCS['UnScheduled'] : '0' ;
                $CCS['Total'] = $CCS['Total']->count();
                $CCS['Total'] = (string) $CCS['Total'];

                if($CCS['Total'] != 0)
                {
                    $CCS['Acheived'] = number_format( $CCS['ClosedPatients'] * 100 / $CCS['Total'] , 1, '.', '') ;
                }
                else{
                    $CCS['Acheived'] = "0";
                }
                
                $CCS['Required_Par'] = "75" ; 

                if($CCS['Acheived'] >= 75){
                    $CCS['Star'] = "4";
                }else if($CCS['Acheived'] >= 56.25){
                    $CCS['Star'] = "3";
                }else if($CCS['Acheived'] >= 37.5){
                    $CCS['Star'] = "2";
                }else if($CCS['Acheived'] >= 18.75){
                    $CCS['Star'] = "1";
                }else{
                    $CCS['Star'] = "-";
                }

                $CCS['Members_remaining'] = number_format( ((($CCS['Required_Par'] - $CCS['Acheived']) * $CCS['Total']) / 100) ) ;
                if($CCS['Members_remaining'] > 0 ){
                    $CCS['Members_remaining'];
                }else{
                    $CCS['Members_remaining'] = "-";
                }

                
                // End Colorectal Cancer Screening  ===========================> CCS  <=======================  


                // Start Diabetes Care - Blood Sugar Control (CDC >9%) (Status) =========================> BSC <================================

                $BSC['Title'] = "Diabetes Care - Blood Sugar Control (CDC >9%) (Status)";
                $BSC['ClosedPatients'] = CareGaps::where('hba1c_gap_insurance','Compliant');
                //$BSC['OpenPatients'] = CareGaps::whereIn('hba1c_gap_insurance',['Non-Compliant', 'N/A']);//where('hba1c_gap','Non-Compliant');
                $BSC['OpenPatients'] = CareGaps::where('hba1c_gap_insurance','Non-Compliant');//where('hba1c_gap','Non-Compliant');
                $BSC['Refused'] = CareGaps::where('hba1c_gap_insurance', 'like' , '%Refuse%');
                $BSC['Scheduled'] = CareGaps::where('hba1c_gap_insurance', 'like' , '%Schedu%');
                $column_name = 'hba1c_gap_insurance';
                $BSC['Total'] = CareGaps::whereIn('hba1c_gap_insurance', ['Compliant', 'Non-Compliant']);//WhereNull('deleted_at');//where('hba1c_gap','Compliant')->orWhere('hba1c_gap','Non-Compliant');//->count();
                if(!empty($doctor_id)){
                    $BSC['ClosedPatients']->where('doctor_id',$doctor_id);
                    $BSC['OpenPatients']->where('doctor_id',$doctor_id);
                    $BSC['Refused']->where('doctor_id',$doctor_id);
                    $BSC['Scheduled']->where('doctor_id',$doctor_id);
                    $BSC['doctor_id'] = $doctor_id;
                    $BSC['Total']->where('doctor_id',$doctor_id);
                }
                if(!empty($insurance_id)){
                    $BSC['ClosedPatients']->where('insurance_id',$insurance_id);
                    $BSC['OpenPatients']->where('insurance_id',$insurance_id);
                    $BSC['Refused']->where('insurance_id',$insurance_id);
                    $BSC['Scheduled']->where('insurance_id',$insurance_id);
                    $BSC['insurance_id'] = $insurance_id;
                    $BSC['Total']->where('insurance_id',$insurance_id);
                }
                if(!empty($clinic_id)){
                    //$clinic_id = explode(',', $clinic_id);
                    $BSC['ClosedPatients']->where('clinic_id',$clinic_id);
                    $BSC['OpenPatients']->where('clinic_id',$clinic_id);
                    $BSC['Refused']->where('clinic_id',$clinic_id);
                    $BSC['Scheduled']->where('clinic_id',$clinic_id);
                    $BSC['clinic_id'] = $clinic_id;  
                    $BSC['Total']->where('clinic_id',$clinic_id);
                }
                $BSC['ActiveNonComp'] = $this->ActiveNonComp($column_name,$BSC);
                $BSC['ClosedPatients'] = $BSC['ClosedPatients']->count();
                $BSC['ClosedPatients'] = (string) $BSC['ClosedPatients'];
                $BSC['OpenPatients'] = $BSC['OpenPatients']->count();
                $BSC['OpenPatients'] = (string) $BSC['OpenPatients'];
                $BSC['Refused'] = $BSC['Refused']->count();
                $BSC['Refused'] = (string) $BSC['Refused'];
                $BSC['ActiveNonComp'] = (string) $BSC['ActiveNonComp'];  
                $BSC['Scheduled'] = $BSC['Scheduled']->count();
                $BSC['Scheduled'] = (string) $BSC['Scheduled']; 
                $BSC['UnScheduled'] = $BSC['ActiveNonComp'] - $BSC['Scheduled'] ;
                $BSC['UnScheduled'] = ($BSC['UnScheduled']>=1 ) ? (string) $BSC['UnScheduled'] : '0' ;
                $BSC['Total'] = $BSC['Total']->count();//->whereIn('hba1c_gap', ['Compliant', 'Non-Compliant'])->count();//CareGaps::where('hba1c_gap','Compliant')->orWhere('hba1c_gap','Non-Compliant')->count();
                $BSC['Total'] = (string) $BSC['Total'];
                if($BSC['Total'] != 0)
                {
                    $BSC['Acheived'] = number_format( $BSC['ClosedPatients'] * 100 / $BSC['Total'] , 1, '.', '') ;
                }else{
                    $BSC['Acheived'] = "0";
                }

                $BSC['Required_Par'] = "83" ; 

                if($BSC['Acheived'] >= 83){
                    $BSC['Star'] = "4";
                }else if($BSC['Acheived'] >= 62.25){
                    $BSC['Star'] = "3";
                }else if($BSC['Acheived'] >= 41.5){
                    $BSC['Star'] = "2";
                }else if($BSC['Acheived'] >= 20.75){
                    $BSC['Star'] = "1";
                }else{
                    $BSC['Star'] = "-";
                }

                $BSC['Members_remaining'] = number_format( ((($BSC['Required_Par'] - $BSC['Acheived']) * $BSC['Total']) / 100) ) ;
            
                if($BSC['Members_remaining'] > 0 ){
                    $BSC['Members_remaining'];
                }else{
                    $BSC['Members_remaining'] = "-";
                }


                // End Diabetes Care - Blood Sugar Control (CDC >9%) (Status) =========================> BSC <================================


                //Start  Diabetes Care - Eye Exam (Status)  ================> EyeExam <===========================

                $EyeExam['Title'] = "Diabetes Care - Eye Exam (Status)";
                $EyeExam['ClosedPatients'] = CareGaps::where('eye_exam_gap_insurance','Compliant');
                //$EyeExam['OpenPatients'] = CareGaps::whereIn('eye_exam_gap_insurance',['Non-Compliant', 'N/A']);
                $EyeExam['OpenPatients'] = CareGaps::where('eye_exam_gap_insurance', 'Non-Compliant');
                $EyeExam['Refused'] = CareGaps::where('eye_exam_gap_insurance', 'like' ,'%Refuse%');
                $EyeExam['Scheduled'] = CareGaps::where('eye_exam_gap_insurance', 'like' ,'%Schedu%');
                $column_name = 'eye_exam_gap_insurance';
                $EyeExam['Total'] = CareGaps::whereIn('eye_exam_gap_insurance', ['Compliant', 'Non-Compliant']);//WhereNull('deleted_at');//where('eye_exam_gap','Compliant')->orWhere('eye_exam_gap','Non-Compliant');//->count();
                
                if(!empty($doctor_id)){
                    $EyeExam['ClosedPatients']->where('doctor_id',$doctor_id);
                    $EyeExam['OpenPatients']->where('doctor_id',$doctor_id);
                    $EyeExam['Refused']->where('doctor_id',$doctor_id);
                    $EyeExam['Scheduled']->where('doctor_id',$doctor_id);
                    $EyeExam['doctor_id'] = $doctor_id;
                    $EyeExam['Total']->where('doctor_id',$doctor_id);
                }
                if(!empty($insurance_id)){
                    $EyeExam['ClosedPatients']->where('insurance_id',$insurance_id);
                    $EyeExam['OpenPatients']->where('insurance_id',$insurance_id);
                    $EyeExam['Refused']->where('insurance_id',$insurance_id);
                    $EyeExam['Scheduled']->where('insurance_id',$insurance_id);
                    $EyeExam['insurance_id'] = $insurance_id;
                    $EyeExam['Total']->where('insurance_id',$insurance_id);
                }
                if(!empty($clinic_id)){
                    //$clinic_id = explode(',', $clinic_id);
                    $EyeExam['ClosedPatients']->where('clinic_id',$clinic_id);
                    $EyeExam['OpenPatients']->where('clinic_id',$clinic_id);
                    $EyeExam['Refused']->where('clinic_id',$clinic_id);
                    $EyeExam['Scheduled']->where('clinic_id',$clinic_id);
                    $EyeExam['clinic_id'] = $clinic_id;  
                    $EyeExam['Total']->where('clinic_id',$clinic_id);
                }
                $EyeExam['ActiveNonComp'] = $this->ActiveNonComp($column_name,$EyeExam);
                $EyeExam['ClosedPatients'] = $EyeExam['ClosedPatients']->count();
                $EyeExam['ClosedPatients'] = (string) $EyeExam['ClosedPatients'];
                $EyeExam['OpenPatients'] = $EyeExam['OpenPatients']->count();
                $EyeExam['OpenPatients'] = (string) $EyeExam['OpenPatients'];
                $EyeExam['Refused'] = $EyeExam['Refused']->count();
                $EyeExam['Refused'] = (string) $EyeExam['Refused'];
                $EyeExam['ActiveNonComp'] = (string) $EyeExam['ActiveNonComp'];
                $EyeExam['Scheduled'] = $EyeExam['Scheduled']->count();
                $EyeExam['Scheduled'] = (string) $EyeExam['Scheduled'];
                $EyeExam['UnScheduled'] = $EyeExam['ActiveNonComp'] - $EyeExam['Scheduled'] ;
                $EyeExam['UnScheduled'] = ($EyeExam['UnScheduled']>=1 ) ? (string) $EyeExam['UnScheduled'] : '0' ;
                $EyeExam['Total'] = $EyeExam['Total']->count();//->whereIn('eye_exam_gap', ['Compliant', 'Non-Compliant'])->count();
                $EyeExam['Total'] = (string) $EyeExam['Total'];
                if($EyeExam['Total'] != 0)
                {
                    $EyeExam['Acheived'] = number_format( $EyeExam['ClosedPatients'] * 100 / $EyeExam['Total'] , 2, '.', '') ;
                }else{
                    $EyeExam['Acheived'] = "0";
                }
                
                $EyeExam['Required_Par'] = "73" ; 

                if($EyeExam['Acheived'] >= 73){
                    $EyeExam['Star'] = "4";
                }else if($EyeExam['Acheived'] >= 54.75){
                    $EyeExam['Star'] = "3";
                }else if($EyeExam['Acheived'] >= 36.5){
                    $EyeExam['Star'] = "2";
                }else if($EyeExam['Acheived'] >= 18.25){
                    $EyeExam['Star'] = "1";
                }else{
                    $EyeExam['Star'] = "-";
                }

                $EyeExam['Members_remaining'] = number_format( ((($EyeExam['Required_Par'] - $EyeExam['Acheived']) * $EyeExam['Total']) / 100) ) ;
                
                if($EyeExam['Members_remaining'] > 0 ){
                    $EyeExam['Members_remaining'];
                }else{
                    $EyeExam['Members_remaining'] = "-";
                }
                // End Diabetes Care - Eye Exam  ================> CDC4 <===========================


                //Start Breast Cancer Screening (Status)  ================> BCS <===========================
                $BCS['Title'] = "Breast Cancer Screening (Status)";
                $BCS['ClosedPatients'] = CareGaps::where('breast_cancer_gap_insurance','Compliant');
                //$BCS['OpenPatients'] = CareGaps::whereIn('breast_cancer_gap_insurance', ['Non-Compliant', 'N/A']);
                $BCS['OpenPatients'] = CareGaps::where('breast_cancer_gap_insurance', 'Non-Compliant');
                $column_name = 'breast_cancer_gap_insurance';
                $BCS['Refused'] = CareGaps::where('breast_cancer_gap_insurance', 'like' ,'%Refuse%');
                $BCS['Scheduled'] = CareGaps::where('breast_cancer_gap_insurance', 'like' ,'%Schedu%');

                $BCS['Total'] = CareGaps::whereIn('breast_cancer_gap_insurance', ['Compliant', 'Non-Compliant']);//WhereNull('deleted_at');//where('breast_cancer_gap','Compliant')->orWhere('breast_cancer_gap','Non-Compliant');
                if(!empty($doctor_id)){
                    $BCS['ClosedPatients']->where('doctor_id',$doctor_id);
                    $BCS['OpenPatients']->where('doctor_id',$doctor_id);
                    $BCS['Refused']->where('doctor_id',$doctor_id);
                    $BCS['Scheduled']->where('doctor_id',$doctor_id);
                    $BCS['doctor_id'] = $doctor_id;
                    $BCS['Total']->where('doctor_id',$doctor_id);
                }
                if(!empty($insurance_id)){
                    $BCS['ClosedPatients']->where('insurance_id',$insurance_id);
                    $BCS['OpenPatients']->where('insurance_id',$insurance_id);
                    $BCS['Refused']->where('insurance_id',$insurance_id);
                    $BCS['Scheduled']->where('insurance_id',$insurance_id);
                    $BCS['insurance_id'] = $insurance_id;
                    $BCS['Total']->where('insurance_id',$insurance_id);
                }
                if(!empty($clinic_id)){
                    //$clinic_id = explode(',', $clinic_id);
                    $BCS['ClosedPatients']->where('clinic_id',$clinic_id);
                    $BCS['ClosedPatients_insurance']->where('clinic_id',$clinic_id);
                    $BCS['Refused']->where('clinic_id',$clinic_id); 
                    $BCS['Scheduled']->where('clinic_id',$clinic_id); 
                    $BCS['clinic_id'] = $clinic_id; 
                    $BCS['Total']->where('clinic_id',$clinic_id);
                }
                $BCS['ActiveNonComp'] = $this->ActiveNonComp($column_name,$BCS);
                $BCS['ClosedPatients'] = $BCS['ClosedPatients']->count();
                $BCS['ClosedPatients'] = (string) $BCS['ClosedPatients'];
                $BCS['OpenPatients'] = $BCS['OpenPatients']->count();
                $BCS['OpenPatients'] = (string) $BCS['OpenPatients'];
                $BCS['Refused'] = $BCS['Refused']->count();
                $BCS['Refused'] = (string) $BCS['Refused'];
                $BCS['ActiveNonComp'] = (string) $BCS['ActiveNonComp'];
                $BCS['Scheduled'] = $BCS['Scheduled']->count();
                $BCS['Scheduled'] = (string) $BCS['Scheduled'];
                $BCS['UnScheduled'] = $BCS['ActiveNonComp'] - $BCS['Scheduled'] ;
                $BCS['UnScheduled'] = ($BCS['UnScheduled']>=1 ) ? (string) $BCS['UnScheduled'] : '0' ;
                $BCS['Total'] = $BCS['Total']->count();//->whereIn('breast_cancer_gap', ['Compliant', 'Non-Compliant'])->count();
                $BCS['Total'] = (string) $BCS['Total'];
                if($BCS['Total'] != 0)
                {
                    $BCS['Acheived'] = number_format( $BCS['ClosedPatients'] * 100 / $BCS['Total'] , 1, '.', '') ;
                }else{
                    $BCS['Acheived'] = "0";
                }

                $BCS['Required_Par'] = "74" ; 

                if($BSC['Acheived'] >= 74){
                    $BSC['Star'] = "4";
                }else if($BSC['Acheived'] >= 55.5){
                    $BSC['Star'] = "3";
                }else if($BSC['Acheived'] >= 37){
                    $BSC['Star'] = "2";
                }else if($BSC['Acheived'] >= 18.5){
                    $BSC['Star'] = "1";
                }else{
                    $BSC['Star'] = "-";
                }

                $BCS['Members_remaining'] = number_format( ((($BCS['Required_Par'] - $BCS['Acheived']) * $BCS['Total']) / 100) ) ;
                
                if($BCS['Members_remaining'] > 0 ){
                    $BCS['Members_remaining'];
                }else{
                    $BCS['Members_remaining'] = "-";
                }

                // End Breast Cancer Screening  ================> BCS <===========================

                //Start Controlling High Blood Pressure (Status) ================> CHBP <===========================
                $CHBP['Title'] = "Controlling High Blood Pressure (Status)";
                $CHBP['ClosedPatients'] = CareGaps::where('high_bp_gap_insurance','Compliant');
                //$CHBP['OpenPatients'] = CareGaps::whereIn('high_bp_gap_insurance', ['Non-Compliant', 'N/A']);
                $CHBP['OpenPatients'] = CareGaps::where('high_bp_gap_insurance', 'Non-Compliant');
                $column_name = 'high_bp_gap_insurance';
                $CHBP['Refused'] = CareGaps::where('high_bp_gap_insurance', 'like' ,'%Refuse%');
                $CHBP['Scheduled'] = CareGaps::where('high_bp_gap_insurance', 'like' ,'%Schedu%');

                $CHBP['Total'] = CareGaps::whereIn('high_bp_gap_insurance', ['Compliant', 'Non-Compliant']);//WhereNull('deleted_at');//where('high_bp_gap','Compliant')->orWhere('high_bp_gap','Non-Compliant');
            
                if(!empty($doctor_id)){
                    $CHBP['ClosedPatients']->where('doctor_id',$doctor_id);
                    $CHBP['OpenPatients']->where('doctor_id',$doctor_id);
                    $CHBP['Refused']->where('doctor_id',$doctor_id);
                    $CHBP['Scheduled']->where('doctor_id',$doctor_id);
                    $CHBP['doctor_id'] = $doctor_id;
                    $CHBP['Total']->where('doctor_id',$doctor_id);
                }
                if(!empty($insurance_id)){
                    $CHBP['ClosedPatients']->where('insurance_id',$insurance_id);
                    $CHBP['OpenPatients']->where('insurance_id',$insurance_id);
                    $CHBP['Refused']->where('insurance_id',$insurance_id);
                    $CHBP['Scheduled']->where('insurance_id',$insurance_id);
                    $CHBP['insurance_id'] = $insurance_id;
                    $CHBP['Total']->where('insurance_id',$insurance_id);
                }
                if(!empty($clinic_id)){
                    //$clinic_id = explode(',', $clinic_id);
                    $CHBP['ClosedPatients']->where('clinic_id',$clinic_id);
                    $CHBP['OpenPatients']->where('clinic_id',$clinic_id);
                    $CHBP['Refused']->where('clinic_id',$clinic_id); 
                    $CHBP['Scheduled']->where('clinic_id',$clinic_id); 
                    $CHBP['clinic_id'] = $clinic_id; 
                    $CHBP['Total']->where('clinic_id',$clinic_id);
                }
                $CHBP['ActiveNonComp'] = $this->ActiveNonComp($column_name,$CHBP);
                $CHBP['ClosedPatients'] = $CHBP['ClosedPatients']->count();
                $CHBP['ClosedPatients'] = (string) $CHBP['ClosedPatients'];
                $CHBP['OpenPatients'] = $CHBP['OpenPatients']->count();
                $CHBP['OpenPatients'] = (string) $CHBP['OpenPatients'];
                $CHBP['Refused'] = $CHBP['Refused']->count();
                $CHBP['Refused'] = (string) $CHBP['Refused'];
                $CHBP['ActiveNonComp'] = (string) $CHBP['ActiveNonComp']; 
                $CHBP['Scheduled'] = $CHBP['Scheduled']->count();
                $CHBP['Scheduled'] = (string) $CHBP['Scheduled'];     
                $CHBP['UnScheduled'] = $CHBP['ActiveNonComp'] - $CHBP['Scheduled'] ;          
                $CHBP['UnScheduled'] = ($CHBP['UnScheduled']>=1 ) ? (string) $CHBP['UnScheduled'] : '0' ;
                $CHBP['Total'] = $CHBP['Total']->count();//->whereIn('high_bp_gap', ['Compliant', 'Non-Compliant'])->count();
                $CHBP['Total'] = (string) $CHBP['Total'];
                if($CHBP['Total'] != 0)
                {
                    $CHBP['Acheived'] = number_format( $CHBP['ClosedPatients'] * 100 / $CHBP['Total'] , 2, '.', '') ;
                }else{
                    $CHBP['Acheived'] = "0";
                }

                $CHBP['Required_Par'] = "76" ; 

                if($CHBP['Acheived'] >= 76){
                    $CHBP['Star'] = "4";
                }else if($CHBP['Acheived'] >= 57){
                    $CHBP['Star'] = "3";
                }else if($CHBP['Acheived'] >= 38){
                    $CHBP['Star'] = "2";
                }else if($CHBP['Acheived'] >= 19){
                    $CHBP['Star'] = "1";
                }else{
                    $CHBP['Star'] = "-";
                }

                $CHBP['Members_remaining'] = number_format( ((($CHBP['Required_Par'] - $CHBP['Acheived']) * $CHBP['Total']) / 100) ) ;
                
                if($CHBP['Members_remaining'] > 0 ){
                    $CHBP['Members_remaining'];

                }else{
                    $CHBP['Members_remaining'] = "-";
                }

                // End Controlling High Blood Pressure  ================> CHBP <===========================

                //Start Kidney Health Evaluation for Patients With Diabetes - Kidney Health Evaluation (Status)  ================> KHE <===========================
                
                $KHE['Title'] = "Kidney Health Evaluation for Patients With Diabetes - Kidney Health Evaluation (Status)";
                $KHE['ClosedPatients'] = CareGaps::where('kidney_health_gap_insurance','Compliant');
                //$KHE['OpenPatients'] = CareGaps::whereIn('kidney_health_gap_insurance', ['Non-Compliant', 'N/A']);
                $KHE['OpenPatients'] = CareGaps::where('kidney_health_gap_insurance', 'Non-Compliant');
                $column_name = 'kidney_health_gap_insurance';
                $KHE['Refused'] = CareGaps::where('kidney_health_gap_insurance', 'like' ,'%Refuse%');
                $KHE['Scheduled'] = CareGaps::where('kidney_health_gap_insurance', 'like' ,'%Schedu%');
                $KHE['Total'] = CareGaps::whereIn('kidney_health_gap_insurance', ['Compliant', 'Non-Compliant']);//WhereNull('deleted_at');//where('kidney_health_gap','Compliant')->orWhere('kidney_health_gap','Non-Compliant');
                
                if(!empty($doctor_id)){
                    $KHE['ClosedPatients']->where('doctor_id',$doctor_id);
                    $KHE['OpenPatients']->where('doctor_id',$doctor_id);
                    $KHE['Refused']->where('doctor_id',$doctor_id);
                    $KHE['Scheduled']->where('doctor_id',$doctor_id);
                    $KHE['doctor_id'] = $doctor_id;
                    $KHE['Total']->where('doctor_id',$doctor_id);
                }
                if(!empty($insurance_id)){
                    $KHE['ClosedPatients']->where('insurance_id',$insurance_id);
                    $KHE['OpenPatients']->where('insurance_id',$insurance_id);
                    $KHE['Refused']->where('insurance_id',$insurance_id);
                    $KHE['Scheduled']->where('insurance_id',$insurance_id);
                    $KHE['insurance_id'] = $insurance_id;
                    $KHE['Total']->where('insurance_id',$insurance_id);
                }
                if(!empty($clinic_id)){
                    //$clinic_id = explode(',', $clinic_id);
                    $KHE['ClosedPatients']->where('clinic_id',$clinic_id);
                    $KHE['OpenPatients']->whereIn('clinic_id',$clinic_id);
                    $KHE['Refused']->where('clinic_id',$clinic_id); 
                    $KHE['Scheduled']->where('clinic_id',$clinic_id); 
                    $KHE['clinic_id'] = $clinic_id; 
                    $KHE['Total']->whereIn('clinic_id',$clinic_id);
                }
                $KHE['ActiveNonComp'] = $this->ActiveNonComp($column_name,$KHE);
                $KHE['ClosedPatients'] = $KHE['ClosedPatients']->count();
                $KHE['ClosedPatients'] = (string) $KHE['ClosedPatients'];
                $KHE['OpenPatients'] = $KHE['OpenPatients']->count();
                $KHE['OpenPatients'] = (string) $KHE['OpenPatients'];
                $KHE['Refused'] = $KHE['Refused']->count();
                $KHE['Refused'] = (string) $KHE['Refused'];
                $KHE['ActiveNonComp'] = (string) $KHE['ActiveNonComp'];
                $KHE['Scheduled'] = $KHE['Scheduled']->count();
                $KHE['Scheduled'] = (string) $KHE['Scheduled'];    
                $KHE['UnScheduled'] = $KHE['ActiveNonComp'] - $KHE['Scheduled'] ;            
                $KHE['UnScheduled'] = ($KHE['UnScheduled']>=1 ) ? (string) $KHE['UnScheduled'] : '0' ;
                $KHE['Total'] = $KHE['Total']->count();//->whereIn('kidney_health_gap', ['Compliant', 'Non-Compliant'])->count();
                $KHE['Total'] = (string) $KHE['Total'];
                if($KHE['Total'] != 0)
                {
                    $KHE['Acheived'] = number_format( $KHE['ClosedPatients'] * 100 / $KHE['Total'] , 1, '.', '') ;
                }else{
                    $KHE['Acheived'] = "0";
                }
                $KHE['Required_Par'] =  "-";
                $KHE['Star'] = "-";
                $KHE['Members_remaining'] = "-";
                
                // $KHE['Required_Par'] =  100; 

                // if($KHE['Acheived'] >= 83){
                //     $KHE['Star'] = "4";
                // }else if($KHE['Acheived'] >= 62.25){
                //     $KHE['Star'] = "3";
                // }else if($KHE['Acheived'] >= 41.5){
                //     $KHE['Star'] = "2";
                // }else if($KHE['Acheived'] >= 20.75){
                //     $KHE['Star'] = "1";
                // }else{
                //     $KHE['Star'] = "-";
                // }

                // $KHE['Members_remaining'] = number_format( ((($KHE['Required_Par'] - $KHE['Acheived']) * $KHE['Total']) / 100) ) ;

                // if($KHE['Members_remaining'] > 0 ){
                //     $KHE['Members_remaining'];
                // }else{
                //     $KHE['Members_remaining'] = "-";
                // }

                // End Kidney Health Evaluation  =============================> KHE  <===========================


                //Start  Blood Pressure Control for Patients With Diabetes - Blood Pressure Controlled <140/90 mm Hg  ================> BPC <===========================
                
                $BPC['Title'] = "Blood Pressure Control for Patients With Diabetes - Blood Pressure Controlled <140/90 mm Hg";
                $BPC['ClosedPatients'] = CareGaps::where('bp_control_gap_insurance','Compliant');
                //$BPC['OpenPatients'] = CareGaps::whereIn('bp_control_gap_insurance', ['Non-Compliant', 'N/A']);
                $BPC['OpenPatients'] = CareGaps::where('bp_control_gap_insurance', 'Non-Compliant');
                $column_name = 'bp_control_gap_insurance';
                $BPC['Refused'] = CareGaps::where('bp_control_gap_insurance', 'like' ,'%Refuse%');
                $BPC['Scheduled'] = CareGaps::where('bp_control_gap_insurance', 'like' ,'%Schedu%');
                $BPC['Total'] = CareGaps::whereIn('bp_control_gap_insurance', ['Compliant', 'Non-Compliant']);//WhereNull('deleted_at');//where('bp_control_gap','Compliant')->orWhere('bp_control_gap','Non-Compliant');
            
                if(!empty($doctor_id)){
                    $BPC['ClosedPatients']->where('doctor_id',$doctor_id);
                    $BPC['OpenPatients']->where('doctor_id',$doctor_id);
                    $BPC['Refused']->where('doctor_id',$doctor_id);
                    $BPC['Scheduled']->where('doctor_id',$doctor_id);
                    $BPC['doctor_id'] = $doctor_id;
                    $BPC['Total']->where('doctor_id',$doctor_id);
                }
                if(!empty($insurance_id)){
                    $BPC['ClosedPatients']->where('insurance_id',$insurance_id);
                    $BPC['OpenPatients']->where('insurance_id',$insurance_id);
                    $BPC['Refused']->where('insurance_id',$insurance_id);
                    $BPC['Scheduled']->where('insurance_id',$insurance_id);
                    $BPC['insurance_id'] = $insurance_id;
                    $BPC['Total']->where('insurance_id',$insurance_id);
                }
                if(!empty($clinic_id)){
                    $BPC['ClosedPatients']->where('clinic_id',$clinic_id);
                    $BPC['OpenPatients']->where('clinic_id',$clinic_id);
                    $BPC['Refused']->where('clinic_id',$clinic_id);
                    $BPC['Scheduled']->where('clinic_id',$clinic_id);
                    $BPC['clinic_id'] = $clinic_id;
                    $BPC['Total']->where('clinic_id',$clinic_id);
                }
                $BPC['ActiveNonComp'] = $this->ActiveNonComp($column_name,$BPC);
                $BPC['ClosedPatients'] = $BPC['ClosedPatients']->count();
                $BPC['ClosedPatients'] = (string) $BPC['ClosedPatients'];
                $BPC['OpenPatients'] = $BPC['OpenPatients']->count();
                $BPC['OpenPatients'] = (string) $BPC['OpenPatients'];
                $BPC['Refused'] = $BPC['Refused']->count();
                $BPC['Refused'] = (string) $BPC['Refused'];
                $BPC['ActiveNonComp'] = (string) $BPC['ActiveNonComp'];
                $BPC['Scheduled'] = $BPC['Scheduled']->count();
                $BPC['Scheduled'] = (string) $BPC['Scheduled'];
                $BPC['UnScheduled'] = $BPC['ActiveNonComp'] - $BPC['Scheduled'] ;
                $BPC['UnScheduled'] = ($BPC['UnScheduled']>=1 ) ? (string) $BPC['UnScheduled'] : '0' ;
                $BPC['Total'] = $BPC['Total']->count();//->whereIn('bp_control_gap', ['Compliant', 'Non-Compliant'])->count();
                //$BPC['OpenPatients'] + $BPC['ClosedPatients'];
                $BPC['Total'] = (string) $BPC['Total'];
                if($BPC['Total'] != 0)
                {
                    $BPC['Acheived'] = number_format( $BPC['ClosedPatients'] * 100 / $BPC['Total'] , 1, '.', '') ;
                    
                }else{
                    $BPC['Acheived'] = "0";
                }
                $BPC['Required_Par'] =  "-"; 
                $BPC['Star'] = "-";
                $BPC['Members_remaining'] = "-";

                // $BPC['Required_Par'] =  100;
                
                // if($BPC['Acheived'] >= 83){
                //     $BPC['Star'] = "4";
                // }else if($BPC['Acheived'] >= 62.25){
                //     $BPC['Star'] = "3";
                // }else if($BPC['Acheived'] >= 41.5){
                //     $BPC['Star'] = "2";
                // }else if($BPC['Acheived'] >= 20.75){
                //     $BPC['Star'] = "1";
                // }else{
                //     $BPC['Star'] = "-";
                // }

                // $BPC['Members_remaining'] = number_format( ((($BPC['Required_Par'] - $BPC['Acheived']) * $BPC['Total']) / 100) ) ;

                // if($BPC['Members_remaining'] > 0 ){
                //     $BPC['Members_remaining'];
                // }else{
                //     $BPC['Members_remaining'] = "-";
                // }

                // End Blood Pressure Control for Patients With Diabetes - Blood Pressure Controlled <140/90 mm Hg  =============================> BPC  <===========================

                $healthChoicePathway = [
                    $CCS,
                    $BSC,
                    $EyeExam,
                    $BCS,
                    $CHBP,
                    $KHE,
                    $BPC,
                
                ];
            }

            $response = [
                'success' => true,
                'message' => 'Dashboard Data Found Successfully',
		        'perpage_showdata' =>$per_page,
                'data' => $total,
                'doctor_data' => $doctor_data,
                'insurance_data' => $insurance_data,
                'program_data' => $program_data,
                'clinic_data' => $clinic_data,
                'total_clinics' => count($clinic_data),
                'total_insurances' => count($insurance_data),
                //'healthChoicePathway' => $total_HCP,
                'healthChoicePathway' => $healthChoicePathway,
            ];
        } catch (\Exception $e) {
            $response = array('success'=>false,'message'=>$e->getMessage()); 
        }
        return response()->json($response);
    }



   
    /**
     * The function runs a migration in PHP using the specified migration file.
     * 
     * @param Request request The  parameter is an instance of the Request class, which
     * represents an HTTP request. It contains information about the request such as the request
     * method, headers, and input data.
     * 
     * @return a JSON response. If the validation fails, it returns a JSON response with the error
     * message. If the validation passes, it runs the migration using Artisan and returns the output of
     * the migration as a JSON response.
     */
    public function runMigration(Request $request)
    {
        $filename = $request->migration_file;

        $validator = Validator::make($request->all(),
            [ 'migration_file' => 'required' ],
            [ 'migration_file.required' => 'Migration file name is required']
        );

        if($validator->fails()) {
            $error = $validator->getMessageBag()->first();
            return response()->json(['success'=>false,'errors'=>$error]);
        };

            Artisan::call('migrate', array('--path' => '/database/migrations/'.$filename));

            $data = Artisan::output();

            return response()->json($data, 200);
    }



    public function find_patients(Request $request)
    {
        $validator = Validator::make($request->all(), [
            //col_name =='bp_control_gap'
            //col_value == "Non-Compliant"

            'col_name'  => 'required',
            'col_value'  => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->getMessageBag()]);
        };
        $input = $validator->valid();

        try {
                if($input['col_value'] === 'ClosedPatients'){
                    $input['col_value'] = "Compliant";
                }elseif($input['col_value'] === 'OpenPatients'){
                    $input['col_value'] = "Non-Compliant";
                }
                //$CareGapsDetailsData = CareGapsDetails::with(['patient', 'patient.clinic','patient.insurance', 'patient.doctor'])->where('caregap_name', $input['col_name'])->where('status', $input['col_value'])->get()->toArray();   
                $patientData = CareGapsDetails::where('caregap_name', $input['col_name'])->where('status', $input['col_value'])->pluck('patient_id')->toArray();
                $CareGapsDetailsData = array_unique($patientData);
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
            'col_name'  => 'required',
            'col_value'  => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->getMessageBag()]);
        };

        $input = $validator->valid();

        try {
            if($input['col_value'] === 'ClosedPatients') {
                $input['col_value'] = "Compliant";
            } elseif ($input['col_value'] === 'OpenPatients') {
                $input['col_value'] = "Non-Compliant";
            }
            //$CareGapsDetailsData = CareGapsDetails::with(['patient', 'patient.clinic','patient.insurance', 'patient.doctor'])->where('caregap_name', $input['col_name'])->where('status', $input['col_value'])->get()->toArray();   
            $patientData = CareGaps::where($input['col_name'], $input['col_value'])->pluck('patient_id')->toArray();
            $CareGapsDetailsData = array_unique($patientData);
            
            $response = [
                'success' => true,
                'message' => 'Overall '. $input['col_value'] .' CareGap Data Found Successfully',
                'totalRecord'=> count($CareGapsDetailsData),
                'data' => $CareGapsDetailsData,
            ];

            } catch (\Exception $e) {
                $response = array('success'=>false,'message'=>$e->getMessage()); 
            }
        return response()->json($response);
    }
    

}

