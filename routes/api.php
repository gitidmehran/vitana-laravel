<?php
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CommonFunctionController; // CommonFunctionController for Common/Gernal Function 
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ProgramController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\QuestionaireController;
use App\Http\Controllers\Api\QuestionnaireController;
use App\Http\Controllers\Api\ClinicController;
use App\Http\Controllers\Api\SuperBillCodesController;
use App\Http\Controllers\Api\ClinicAdminController;
use App\Http\Controllers\Api\PatientsController;
use App\Http\Controllers\Api\InsurancesController;
use App\Http\Controllers\Api\ScheduleController;
use App\Http\Controllers\Api\SpecialistController;
use App\Http\Controllers\Api\PhysiciansController;
use App\Http\Controllers\Api\CareplanController;
use App\Http\Controllers\Api\CcmTaskController;
use App\Http\Controllers\Api\CareGapsController;
use App\Http\Controllers\Api\HumanaCareGapsController;
use App\Http\Controllers\Api\MedicareArizonaCareGapsController;
use App\Http\Controllers\Api\AetnaMedicareCareGapsController;
use App\Http\Controllers\Api\AllwellMedicareCareGapsController;
use App\Http\Controllers\Api\HealthchoiceArizonaCareGapsController;
use App\Http\Controllers\Api\UnitedHealthCareController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::middleware('auth:passport')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('login', [AuthController::class,'login']);

Route::group(['middleware'=>['auth:api'], 'excluded_middleware' => 'throttle:api'],function(){

    // Users ROUTES
    Route::get('users' , [UserController::class,'index']);
    Route::post('user/create' , [UserController::class,'store']);
    Route::get('user/edit/{id}' , [UserController::class,'edit']);
    Route::post('user/update/{id}' , [UserController::class,'update']);
    Route::post('user/delete/{id}' , [UserController::class,'destroy']);
    Route::post('logout', [UserController::class,'logout']);

    // Clinics ROUTES
    Route::get('clinics',  [ClinicController::class,'index']);
    Route::post('clinic/create' , [ClinicController::class,'store']);
    Route::post('clinic/update/{id}' , [ClinicController::class,'update']);
    Route::get('clinic/edit/{id}' , [ClinicController::class,'edit']);
    Route::post('clinic/delete/{id}' , [ClinicController::class,'destroy']);

    // ClinicAdmin ROUTES
    Route::get('clinicAdmins',  [ClinicAdminController::class,'index']);
    Route::post('clinicAdmin/create' , [ClinicAdminController::class,'store']);
    Route::get('clinicAdmin/edit/{id}' , [ClinicAdminController::class,'edit']);
    Route::post('clinicAdmin/update/{id}' , [ClinicAdminController::class,'update']);
    Route::post('clinicAdmin/delete/{id}' , [ClinicAdminController::class,'destroy']);
    Route::post('clinicAdmin/update-password/{id}' , [ClinicAdminController::class,'updatePassword']);


    // Patients ROUTES
    Route::get('patients',  [PatientsController::class,'index']);
    Route::post('patient/create' , [PatientsController::class,'store']);
    Route::get('patient/edit/{id}' , [PatientsController::class,'edit']);
    Route::post('patient/update/{id}' , [PatientsController::class,'update']);
    Route::post('patient/delete/{id}' , [PatientsController::class,'destroy']);
    Route::post('patient/add-disease', [PatientsController::class, 'addDiagnosis']);
    Route::post('patient/update-disease/{id}', [PatientsController::class, 'updateDiagnosis']);
    Route::post('patient/add-medication', [PatientsController::class, 'addMedications']);
    Route::post('patient/update-medication/{id}', [PatientsController::class, 'updateMedication']);
    Route::post('patient/add-surgery', [PatientsController::class, 'addSurgeries']);
    Route::post('patient/update-surgery/{id}', [PatientsController::class, 'updateSurgery']);
    Route::get('patient/encounters/{id}', [PatientsController::class, 'getEncounters']);
    Route::get('patient/insurance-pcp/{id}', [PatientsController::class, 'getInsuracneandPcp']);
    Route::post('patient/update-family-history/{id}', [PatientsController::class, 'updateFamilyHistory']);
    Route::post('patient/add-social-history/{id}', [PatientsController::class, 'addSocialHistory']);
    Route::post('patient/update-social-history/{id}', [PatientsController::class, 'updateSocialHistory']);
    Route::post('patient/update-consent/{id}', [PatientsController::class, 'storePatientConsent']);
    Route::post('patient/add-bulkpatients', [PatientsController::class, 'storeBulkPatients']);
    Route::post('patient/bulk-assign', [PatientsController::class, 'bulkAssign']);
    Route::post('patient/add-cclfdata', [PatientsController::class, 'storeCclfData']);
    Route::post('patient/update_patient_group',  [PatientsController::class,'updatePatientGroup']);
    Route::post('patient/currentyeargaps',  [PatientsController::class,'currentYearGaps']);

    // for ajax call request soft delection 
    Route::post('surgical_history_destroy/{id}' , [PatientsController::class,'surgical_history_destroy']);
    Route::get('surgical_history_spellMistake/{id}' , [PatientsController::class,'surgical_history_spellMistake']);
    Route::post('diagnosis_destroy/{id}' , [PatientsController::class,'diagnosis_destroy']);
    Route::get('diagnosis_spellMistake/{id}' , [PatientsController::class,'diagnosis_spellMistake']);
    Route::post('medication_destroy/{id}' , [PatientsController::class,'medication_destroy']);
    Route::get('medication_spellMistake/{id}' , [PatientsController::class,'medication_spellMistake']);
    Route::post('status_change/{id}', [PatientsController::class,'status_change']);
    Route::post('downloadFile',  [PatientsController::class,'downloadFile']);
    //Route::post('Inactive_patients', [PatientsController::class,'Inactive_patients']);

    // ScheduleControlle ROUTES
    Route::get('schedules',  [ScheduleController::class,'index']);
    Route::post('schedule/create' , [ScheduleController::class,'store']);
    Route::get('schedule/edit/{id}' , [ScheduleController::class,'single']);
    Route::post('schedule/update/{id}' , [ScheduleController::class,'update']);
    Route::post('schedule/delete/{id}' , [ScheduleController::class,'destroy']);

    // DashboardController route
    Route::get('dashboard',  [DashboardController::class,'index']);
    Route::post('dashboard/findPatients',  [DashboardController::class,'find_patients']);
    Route::post('dashboard/findAllPatients',  [DashboardController::class,'find_all_patients']);
    Route::post('dashboard/patientByStatus', [DashboardController::class,'patientByStatus']);
    // Route::get('dashboard/doctor/{data}' , [DashboardController::class,'doctor_id']);

    //Specialist
    Route::post('specialist/delete/{id}', [SpecialistController::class, 'destroy']);
    Route::post('specialist/create', [SpecialistController::class, 'store']);
    Route::get('specialist', [SpecialistController::class, 'index']);
    Route::get('specialist/list', [SpecialistController::class, 'list']);
    Route::post('specialist/update/{id}', [SpecialistController::class, 'update']);
    Route::get('specialist/edit/{id}', [SpecialistController::class, 'single']);

    //Insurance
    Route::get('insurance', [InsurancesController::class, 'index']);
    Route::post('insurance/create', [InsurancesController::class, 'store']);
    Route::post('insurance/update/{id}', [InsurancesController::class, 'update']);
    Route::post('insurance/delete/{id}', [InsurancesController::class, 'destroy']);

    //Programs
    Route::get('programs', [ProgramController::class, 'index']);
    Route::post('program/create', [ProgramController::class, 'store']);
    Route::post('program/update/{id}', [ProgramController::class, 'update']);
    Route::post('program/delete/{id}', [ProgramController::class, 'destroy']);
    Route::get('program/edit/{id}', [ProgramController::class, 'single']);

    //Physician
    Route::get('physicians', [PhysiciansController::class, 'index']);
    Route::post('physician/create', [PhysiciansController::class, 'store']);
    Route::post('physician/update/{id}', [PhysiciansController::class, 'update']);
    Route::post('physician/delete/{id}', [PhysiciansController::class, 'destroy']);
    Route::get('physician/edit/{id}', [PhysiciansController::class, 'single']);

    /* CAREPLAN Routes */
    Route::get('careplan/awv-careplan/{id}', [CareplanController::class,'index']);
    Route::post('careplan/ccm-careplan/{id}', [CareplanController::class,'ccmCareplanReport']);
    Route::post('careplan/filledquestionnaire/{id}', [CareplanController::class,'filledQuestionnaire']);
    Route::get('careplan/careplanpdf/{id}', [CareplanController::class,'downloadCareplanpdf']);
    Route::post('careplan/savesignature/{id}', [CareplanController::class,'saveSignature']);
    Route::post('careplan/monthlyassessment/{id}', [CareplanController::class,'filterMonthlyAssessment']);
    Route::get('careplan/monthlyassessmentpdf/{id}', [CareplanController::class,'downloadMonthlyAssessment']);
    Route::get('careplan/CCMCarePlanpdf/{id}', [CareplanController::class,'downloadCCMCarePlan']);

    // QUESTIONNAIRE ROUTES
    Route::apiResource('questionaire' , QuestionaireController::class);
    Route::post('questionaire/edit/{id}' , [QuestionaireController::class, 'edit']);
    Route::post('questionaire/superbill/{id}' , [QuestionaireController::class, 'superbill']);
    Route::post('questionaire/update/{id}' , [QuestionaireController::class, 'update']);
    Route::post('questionaire/delete/{id}' , [QuestionaireController::class, 'destroy']);
    Route::post('questionaire/get-programm-data', [QuestionaireController::class,'getProgramms']);
    Route::post('questionaire/abc', [QuestionaireController::class,'storeMonthlyAssessment']);
    Route::post('questionaire/update-questionnaire-status/{id}', [QuestionaireController::class,'updateQuestionnaireStatus']);
    Route::post('questionaire/unsigned-encounters', [QuestionaireController::class,'unsignedEncounters']);
    Route::post('questionaire/completed-encounters', [QuestionaireController::class,'completedEncounters']);
    Route::post('questionaire/billables', [QuestionaireController::class,'fetchBillables']);

    // CCM TASKS ROUTES
    Route::get('ccmtasks' , [CcmTaskController::class, 'index']);
    Route::post('ccmtasks/get-coordinators' , [CcmTaskController::class, 'fetchCoordinators']);
    Route::post('ccmtasks/store' , [CcmTaskController::class, 'store']);
    Route::post('ccmtasks/get-logs' , [CcmTaskController::class, 'fetchLogs']);
    Route::post('ccmtasks/updatetask/{id}' , [CcmTaskController::class, 'update']);
    Route::post('ccmtasks/deletetask/{id}' , [CcmTaskController::class, 'delete']);


    
    /* SuperBill Codes Routes */
    Route::get('superbill/{id}', [SuperBillCodesController::class,'index']);
    Route::post('superbill/add-code', [SuperBillCodesController::class,'store']);
    Route::post('superbill/update-code', [SuperBillCodesController::class,'update']);
    Route::post('superbill/delete-code', [SuperBillCodesController::class,'destroy']);
    Route::post('superbill/delete-dx-code', [SuperBillCodesController::class,'destroy_dx']);
    Route::get('superbill/super-bill/{id}', [SuperBillCodesController::class,'downloadSuperBill']);

    // CareGaps Routes
    Route::get('caregap', [CareGapsController::class, 'clinicData']);
    
    Route::get('healthchoice/index',  [CareGapsController::class,'index']);
    Route::post('healthchoice/add-bulkcaregaps', [CareGapsController::class, 'storeBulkCareGaps']);
    Route::post('healthchoice/add-preprocessor', [CareGapsController::class, 'storePreProcessor']);
    // Analysis
    Route::post('healthchoice/analyse', [CommonFunctionController::class, 'analyse']); 
    Route::post('healthchoice/update/{id}', [CareGapsController::class, 'update']);
    
    Route::get('caregap/comments',  [CareGapsController::class,'allComments']);
    Route::post('caregap/addComment', [CareGapsController::class, 'addComment']);
    Route::post('caregap/update-comment/{id}', [CareGapsController::class, 'updateComment']);
    Route::post('caregap/duplicateCareGapRecord', [CareGapsController::class,'duplicateCareGapRecord']);
    
    // Humana CareGaps Routes
    //Route::get('caregap', [HumanaCareGapsController::class, 'clinicData']);
    Route::get('humana/index',  [HumanaCareGapsController::class,'index']);
    Route::post('humana/add-bulkcaregaps', [HumanaCareGapsController::class, 'storeBulkHumanaCareGaps']);
    Route::post('humana/insurance-history', [HumanaCareGapsController::class, 'insuranceHistory']);

    Route::post('humana/update/{id}', [HumanaCareGapsController::class, 'update']);
    
    // Analysis
    Route::post('humana/analyse', [CommonFunctionController::class, 'analyse']); 
        
    // Medicare Arizona CareGaps Routes
    //Route::get('caregap', [MedicareArizonaCareGapsController::class, 'clinicData']);
    Route::get('medicarearizona/index',  [MedicareArizonaCareGapsController::class,'index']);
    Route::post('medicarearizona/add-bulkcaregaps', [MedicareArizonaCareGapsController::class, 'storeBulkMedicareArizonaCareGaps']);
    // Analysis
    Route::post('medicarearizona/analyse', [CommonFunctionController::class, 'analyse']); 
    Route::post('medicarearizona/update/{id}', [MedicareArizonaCareGapsController::class, 'update']);
    
    // AetnaMedicareCareGapsController;
    Route::get('aetnamedicare/index',  [AetnaMedicareCareGapsController::class,'index']);
    Route::post('aetnamedicare/add-bulkcaregaps', [AetnaMedicareCareGapsController::class, 'storeBulkAetnaMedicareCareGaps']);
    Route::post('aetnamedicare/update/{id}', [AetnaMedicareCareGapsController::class, 'update']);
    
    // AllwellMedicareCareGapsController;
    Route::get('allwellmedicare/index',  [AllwellMedicareCareGapsController::class,'index']);
    Route::post('allwellmedicare/add-bulkcaregaps', [AllwellMedicareCareGapsController::class, 'storeBulkAllwellMedicareCareGaps']);
    // Analysis
    Route::post('allwellmedicare/analyse', [CommonFunctionController::class, 'analyse']); 
    Route::post('allwellmedicare/update/{id}', [AllwellMedicareCareGapsController::class, 'update']);
     
    // HealthchoiceArizona;
    Route::get('healthchoicearizona/index',  [HealthchoiceArizonaCareGapsController::class,'index']);
    Route::post('healthchoicearizona/add-bulkcaregaps', [HealthchoiceArizonaCareGapsController::class, 'storeBulkHealthchoiceArizonaCareGaps']);
    // Analysis
    Route::post('healthchoicearizona/analyse', [CommonFunctionController::class, 'analyse']); 
    Route::post('healthchoicearizona/update/{id}', [HealthchoiceArizonaCareGapsController::class, 'update']);
    
    // UnitedHealthCareCareGapsController;
    Route::get('unitedhealthcare/index',  [UnitedHealthCareController::class,'index']);
    Route::post('unitedhealthcare/add-bulkcaregaps', [UnitedHealthCareController::class, 'storeBulkUnitedHealthcareCareGaps']);
    // Analysis
    Route::post('unitedhealthcare/analyse', [CommonFunctionController::class, 'analyse']); 
    Route::post('unitedhealthcare/update/{id}', [UnitedHealthCareController::class, 'update']);
    
    Route::post('duplicateCareGapRecord', [DashboardController::class,'duplicateCareGapRecord']);

    Route::post('patients-file-log', [CommonFunctionController::class,'patientsFileLogs']);
    Route::post('caregap/parserEdit/{id}' , [CommonFunctionController::class, 'parserEdit']);
    Route::post('patients-changes-history', [CommonFunctionController::class,'patientsChangesHistory']);

    
    Route::post('insurance-start-date-found', [CommonFunctionController::class,'insuranceStartDateFound']);
    Route::post('patient-insurance-history', [CommonFunctionController::class,'patientInsuranceHistory']);
    
    Route::post('patient-update-insurance', [CommonFunctionController::class,'patientUpdateInsurance']);
    Route::post('inActive-insurance-history-update', [CommonFunctionController::class,'inActiveInsuranceHistoryUpdate']);
});
    // Analysis





Route::get('checkcareplan/{id}', [CareplanController::class,'checkCareplanHtml']);
Route::get('checksuperbill/{id}', [SuperBillCodesController::class,'superbillHtml']);
Route::post('test' , [ClinicAdminController::class,'testUpdate']);

Route::post('deleteFileFromS3', [DashboardController::class,'deleteFileFromS3']);




/* The above code is defining a route in a PHP Laravel application. The route is a POST route with the
URL '/run_migration'. When this route is accessed, it executes a function that performs the
following steps: 
* The function runs a migration in PHP using the specified migration file.
*/

Route::post('run_migration', function (Request $request)
{
    try {
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
    } catch (\Exception $e) {
        $data = $e->getMessage();
    }

    return response()->json($data, 200);
});

