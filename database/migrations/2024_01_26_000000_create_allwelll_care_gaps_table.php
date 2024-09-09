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
        Schema::create('allwell_medicare_care_gaps', function (Blueprint $table) {
            $table->id();
            $table->string('member_id')->nullable();
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->foreignId('doctor_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('insurance_id')->nullable()->constrained('insurances')->onDelete('cascade');
            $table->foreignId('clinic_id')->constrained('clinics')->onDelete('cascade');
        
            $table->string('gap_year')->nullable();
            // Allwell

            //image 1
            //ExcelFile 2 
            //BCS - Breast Cancer Screening
            $table->string('breast_cancer_gap')->nullable();
            $table->string('breast_cancer_gap_insurance')->nullable();

            //image 2
            //ExcelFile 1 
            //CBP - Controlling High Blood Pressure
            $table->string('high_bp_gap')->nullable();
            $table->string('high_bp_gap_insurance')->nullable();

            //image 3
            //ExcelFile 6
            //CDC - Diabetes - Dilated Eye Exam
            $table->string('eye_exam_gap')->nullable();
            $table->string('eye_exam_gap_insurance')->nullable();

            //image 4
            //ExcelFile 7
            //CDC - Diabetes HbA1c <= 9
            $table->string('hba1c_gap')->nullable();
            $table->string('hba1c_gap_insurance')->nullable();

            //image 5
            //ExcelFile 9
            //COA - Care for Older Adults - Pain Assessment
            $table->string('pain_screening_gap')->nullable();
            $table->string('pain_screening_gap_insurance')->nullable();

            //image 6
            //ExcelFile 10
            //COA - Care for Older Adults - Review
            $table->string('adults_medic_gap')->nullable();
            $table->string('adults_medic_gap_insurance')->nullable();

            //image 7
            //ExcelFile 3
            //COL - Colorectal Cancer Screen (50 - 75 yrs)
            $table->string('colorectal_cancer_gap')->nullable();
            $table->string('colorectal_cancer_gap_insurance')->nullable();

            //image 8 new
            //ExcelFile 11
            //FMC - F/U ED Multiple High Risk Chronic Conditions
            $table->string('m_high_risk_cc_gap')->nullable();
            $table->string('m_high_risk_cc_gap_insurance')->nullable();

            //image 9 new 
            //ExcelFile 14
            //Med Adherence - Diabetic
            $table->string('med_adherence_diabetic_gap')->nullable();
            $table->string('med_adherence_diabetic_gap_insurance')->nullable();

            //image 10 new
            //ExcelFile 4
            //Med Adherence - RAS
            $table->string('med_adherence_ras_gap')->nullable();
            $table->string('med_adherence_ras_gap_insurance')->nullable();

            //image 11 new
            //ExcelFile 5
            //Med Adherence - Statins
            $table->string('med_adherence_statins_gap')->nullable();
            $table->string('med_adherence_statins_gap_insurance')->nullable();

            //image 12 new 
            //ExcelFile 16
            //SPC - Statin Therapy for Patients with CVD
            $table->string('spc_statin_therapy_cvd_gap')->nullable();
            $table->string('spc_statin_therapy_cvd_gap_insurance')->nullable();

            //image 13
            //ExcelFile 8
            //SUPD - Statin Use in Persons With Diabetes
            $table->string('sup_diabetes_gap')->nullable();
            $table->string('sup_diabetes_gap_insurance')->nullable();

            //image 14 new
            //ExcelFile 12
            //TRC - Engagement After Discharge
            $table->string('trc_eng_after_disc_gap')->nullable();
            $table->string('trc_eng_after_disc_gap_insurance')->nullable();

            //image 15 new
            //ExcelFile 13
            //TRC - Med Reconciliation Post Discharge
            $table->string('trc_mr_post_disc_gap')->nullable();
            $table->string('trc_mr_post_disc_gap_insurance')->nullable();

            //image no show new 
            //ExcelFile 15
            $table->string('kidney_health_diabetes_gap')->nullable();
            $table->string('kidney_health_diabetes_gap_insurance')->nullable();

            //image no show
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
        Schema::dropIfExists('allwell_medicare_care_gaps');
    }
};
