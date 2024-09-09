<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\SpecialistsController;
use App\Http\Controllers\ProgramsController;
use App\Http\Controllers\PatientsController;
use App\Http\Controllers\PhysiciansController;
use App\Http\Controllers\QuestionairesController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\InsurancesController;

use App\Http\Controllers\UserController;
use App\Http\Controllers\ClinicController;

use App\Http\Controllers\ClinicAdminController;
use App\Http\Controllers\AssignmentController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('auth.login');
});

Route::group(['prefix'=>'dashboard','middleware'=>['auth']], function(){
    Route::get('/', [LoginAuthController::class, 'dashboard']);
    Route::get('report', [LoginAuthController::class, 'report'])->name('report');
    Route::get('logout' , [AuthenticatedSessionController::class,'destroy']);

    // PATIENTS ROUTES
    Route::get('patients/delete/{id}' , [PatientsController::class,'destroy']);
    Route::resource('patients',PatientsController::class);

// for ajax call request soft delection 
    Route::delete('surgical_history_destroy/{id}' , [PatientsController::class,'surgical_history_destroy']);
     Route::get('surgical_history_spellMistake/{id}' , [PatientsController::class,'surgical_history_spellMistake']);

     Route::delete('diagnosis_destroy/{id}' , [PatientsController::class,'diagnosis_destroy']);
     Route::get('diagnosis_spellMistake/{id}' , [PatientsController::class,'diagnosis_spellMistake']);

     Route::delete('medication_destroy/{id}' , [PatientsController::class,'medication_destroy']);
     Route::get('medication_spellMistake/{id}' , [PatientsController::class,'medication_spellMistake']);
     Route::post('status_change/{id}', [PatientsController::class,'status_change']);

     Route::post('Inactive_patients', [PatientsController::class,'Inactive_patients']);

    // specialists ROUTES
    Route::get('specialists/delete/{id}' , [SpecialistsController::class,'destroy']);
    Route::resource('specialists',SpecialistsController::class);

    // Users ROUTES
    Route::get('users/delete/{id}' , [UserController::class,'destroy']);
    Route::resource('users',UserController::class);

    // Clinics ROUTES
    Route::get('clinics/delete/{id}' , [ClinicController::class,'destroy']);
    Route::resource('clinics',ClinicController::class);

    // ClinicAdminController ROUTES
    Route::get('clinicAdmins/delete/{id}' , [ClinicAdminController::class,'destroy']);
    Route::resource('clinicAdmins',ClinicAdminController::class);

    // PHYSICIANS ROUTES
    Route::get('physicians/delete/{id}' , [PhysiciansController::class,'destroy']);
    Route::resource('physicians',PhysiciansController::class);

    // PROGRAMS ROUTES
    Route::get('programs/delete/{id}' , [ProgramsController::class,'destroy']);
    Route::resource('programs',ProgramsController::class);

    // InsuranceS ROUTES
    Route::get('insurances/delete/{id}' , [InsurancesController::class,'destroy']);
    Route::resource('insurances', InsurancesController::class);

    // QUESTIONAIRES ROUTES
    Route::post('get-programm-data', [QuestionairesController::class,'getProgramms']);
    Route::post('questionaires-survey/update/{id}', [QuestionairesController::class,'update']);
    Route::get('questionaires-survey/delete/{id}' , [QuestionairesController::class,'destroy']);
    Route::post('update-session-data', [QuestionairesController::class,'updateSessionData']);
    Route::resource('questionaires-survey', QuestionairesController::class);
    

    // REPORT ROUTES
    Route::get('reports/analytics-report/{serialno}', [ReportsController::class,'index']);
    Route::get('reports/core-report/{serialno}', [ReportsController::class,'coreReport']);
    Route::get('reports/download-analyticalreport-pdf/{serialno}', [ReportsController::class,'downloadAnalyticalReport']);
    Route::get('reports/download-fullreport-pdf/{serialno}', [ReportsController::class,'downloadFullReport']);
    Route::post('reports/analytics-report/savesignature/{serialno}', [ReportsController::class,'saveSignature']);

    // ASSIGNMENT ROUTES
    Route::get('assignment/patient-assignment', [AssignmentController::class,'patientAssignmentView']);

    Route::get('autocomplete', [QuestionairesController::class,'autocomplete']);
});



require __DIR__.'/auth.php';
