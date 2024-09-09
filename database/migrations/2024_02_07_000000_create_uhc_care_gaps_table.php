<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('uhc_care_gaps', function (Blueprint $table) {
            $table->id();
            $table->string('member_id')->nullable();
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->foreignId('doctor_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('insurance_id')->nullable()->constrained('insurances')->onDelete('cascade');
            $table->foreignId('clinic_id')->constrained('clinics')->onDelete('cascade');
        
            $table->string('gap_year')->nullable();
            //United Health Care
            //image 1
            //ExcelFile 1
            //C01-Breast Cancer Screening
            $table->string('breast_cancer_gap')->nullable();
            $table->string('breast_cancer_gap_insurance')->nullable();

            //image 2
            //ExcelFile 2 
            //C02-Colorectal Cancer Screening 
            $table->string('colorectal_cancer_gap')->nullable();
            $table->string('colorectal_cancer_gap_insurance')->nullable();

            //image 3
            //ExcelFile 3 
            //C06-Care for Older Adults - Medication Review
            $table->string('adults_medic_gap')->nullable();
            $table->string('adults_medic_gap_insurance')->nullable();

            //image 4
            //ExcelFile 4
            //DMC10-Care for Older Adults - Functional Status Assessment .
            $table->string('adults_fun_status_gap')->nullable();
            $table->string('adults_fun_status_gap_insurance')->nullable();

            //image 5
            //ExcelFile 5
            //C07-Care for Older Adults - Pain Assessment
            $table->string('pain_screening_gap')->nullable();
            $table->string('pain_screening_gap_insurance')->nullable();

            //image 6
            //ExcelFile 6
            //C09-Eye Exam for Patients With Diabetes
            $table->string('eye_exam_gap')->nullable();
            $table->string('eye_exam_gap_insurance')->nullable();

            //image 7
            //ExcelFile 7
            //C10-Kidney Health Evaluation for Patients With Diabetes
            $table->string('kidney_health_diabetes_gap')->nullable();
            $table->string('kidney_health_diabetes_gap_insurance')->nullable();

            //image 8
            //ExcelFile 8
            //C11-Hemoglobin A1c Control for Patients With Diabetes
            $table->string('hba1c_gap')->nullable();
            $table->string('hba1c_gap_insurance')->nullable();

            //image 9
            //ExcelFile 9 
            //C12 - Controlling Blood Pressure
            $table->string('high_bp_gap')->nullable();
            $table->string('high_bp_gap_insurance')->nullable();

            //image 10
            //ExcelFile 10
            //C16 - Statin Therapy for Patients with Cardiovascular Disease
            $table->string('statin_therapy_gap')->nullable();
            $table->string('statin_therapy_gap_insurance')->nullable();

            //image 11
            //ExcelFile 11
            //D08-Med Ad. For Diabetes Meds Current Year Status
            $table->string('med_adherence_diabetes_gap')->nullable();
            $table->string('med_adherence_diabetes_gap_insurance')->nullable();

            //image 12
            //ExcelFile 12 
            //D09-Med Ad. (RAS antagonists) Current Year Statu
            $table->string('med_adherence_ras_gap')->nullable();
            $table->string('med_adherence_ras_gap_insurance')->nullable();

            //image 13
            //ExcelFile 13
            //D10-Med Ad. (Statins) Current Year Status
            $table->string('med_adherence_statins_gap')->nullable();
            $table->string('med_adherence_statins_gap_insurance')->nullable();

            //image 14
            //ExcelFile 14
            //D11-MTM CMR Current Year Status
            $table->string('mtm_cmr_gap')->nullable();
            $table->string('mtm_cmr_gap_insurance')->nullable();

            //image 15
            //ExcelFile 15
            //D12-Statin Use in Persons with Diabetes Current Year Status
            $table->string('sup_diabetes_gap')->nullable();
            $table->string('sup_diabetes_gap_insurance')->nullable();

            //image 16
            //ExcelFile 16
            //AWV 
            $table->string('awv_gap')->nullable();
            $table->string('awv_gap_insurance')->nullable();

            $table->string('source')->nullable();
            $table->string('q_id')->nullable();
            $table->foreignId('created_user')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('uhc_care_gaps');
    }
};
