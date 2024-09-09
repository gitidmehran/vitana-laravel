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
        Schema::create('aetna_medicare_care_gaps', function (Blueprint $table) {
            $table->id();
            $table->string('member_id')->nullable();
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->foreignId('doctor_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('insurance_id')->nullable()->constrained('insurances')->onDelete('cascade');
            $table->foreignId('clinic_id')->constrained('clinics')->onDelete('cascade');

            $table->string('gap_year')->nullable();
            
//1            
            $table->string('breast_cancer_gap')->nullable();
            $table->string('breast_cancer_gap_insurance')->nullable();
//2
            $table->string('colorectal_cancer_gap')->nullable();
            $table->string('colorectal_cancer_gap_insurance')->nullable();
//3
            $table->string('eye_exam_gap')->nullable();
            $table->string('eye_exam_gap_insurance')->nullable();

// Follow-Up After Emergency Department Visit for MCC (FMC)
//6
            $table->string('faed_visit_gap')->nullable();
            $table->string('faed_visit_gap_insurance')->nullable();
//4
            $table->string('hba1c_gap')->nullable();
            $table->string('hba1c_gap_insurance')->nullable();

// Osteoporosis Management in Women Who Had a Fracture (OMW)
//11
            $table->string('omw_fracture_gap')->nullable();
            $table->string('omw_fracture_gap_insurance')->nullable();

// Plan All-Cause Readmissions (PCR)
//12
            $table->string('pc_readmissions_gap')->nullable();
            $table->string('pc_readmissions_gap_insurance')->nullable();

// Statin Therapy for Patients with Cardiovascular Disease: Received Statin Therapy (SPC_STATIN)
//5
            $table->string('spc_disease_gap')->nullable();
            $table->string('spc_disease_gap_insurance')->nullable();

// Medication Adherence for Cholesterol (Statins): Statins (ADH_STATIN)
//10
            $table->string('ma_cholesterol_gap')->nullable();
            $table->string('ma_cholesterol_gap_insurance')->nullable();

// Medication Adherence for Diabetes Medications: Diabetes Medications (ADH_DIAB)
//7
            $table->string('mad_medications_gap')->nullable();
            $table->string('mad_medications_gap_insurance')->nullable();
            
// Medication Adherence for Hypertension (ACE or ARB): ACE (ADH_ACE)
//8
            $table->string('ma_hypertension_gap')->nullable();
            $table->string('ma_hypertension_gap_insurance')->nullable();
            
// Statin Use in Persons with Diabetes (SUPD)
//9
            $table->string('sup_diabetes_gap')->nullable();
            $table->string('sup_diabetes_gap_insurance')->nullable();
//13            
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
        Schema::dropIfExists('aetna_medicare_care_gaps');
    }
};
