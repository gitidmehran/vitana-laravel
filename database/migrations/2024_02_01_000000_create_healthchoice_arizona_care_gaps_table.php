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
        Schema::create('healthchoice_arizona_care_gaps', function (Blueprint $table) {
            $table->id();
            $table->string('member_id')->nullable();
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->foreignId('doctor_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('insurance_id')->nullable()->constrained('insurances')->onDelete('cascade');
            $table->foreignId('clinic_id')->constrained('clinics')->onDelete('cascade');
        
            $table->string('gap_year')->nullable();
            // Health Choice Arizona


            //BCS - Breast Cancer Screening
            $table->string('breast_cancer_gap')->nullable();
            $table->string('breast_cancer_gap_insurance')->nullable();

            //CCS - Cervical Cancer Screening
            $table->string('cervical_cancer_gap')->nullable();
            $table->string('cervical_cancer_gap_insurance')->nullable();

            //HDO - Use of Opioids at High Dosage
            $table->string('opioids_high_dosage_gap')->nullable();
            $table->string('opioids_high_dosage_gap_insurance')->nullable();

            //HBD - Hemoglobin A1c (HbA1c) Poor Control >9%
            $table->string('hba1c_poor_gap')->nullable();
            $table->string('hba1c_poor_gap_insurance')->nullable();

           //PPC1 - Timeliness of Prenatal Care
           $table->string('ppc1_gap')->nullable();
           $table->string('ppc1_gap_insurance')->nullable();

           //PPC2 - Timeliness of Prenatal Care
           $table->string('ppc2_gap')->nullable();
           $table->string('ppc2_gap_insurance')->nullable();

           //WCV - Well-Child Visits for Age 3-21
           $table->string('well_child_visits_gap')->nullable();
           $table->string('well_child_visits_gap_insurance')->nullable();

           //Chlamydia Screening
           $table->string('chlamydia_gap')->nullable();
           $table->string('chlamydia_gap_insurance')->nullable();

           //CBP - Controlling High Blood Pressure
           $table->string('high_bp_gap')->nullable();
           $table->string('high_bp_gap_insurance')->nullable();

           //Follow-Up After Hospitalization for Mental Illness (FUH 30-Day)
           $table->string('fuh_30Day_gap')->nullable();
           $table->string('fuh_30Day_gap_insurance')->nullable();

           //Follow-Up After Hospitalization for Mental Illness (FUH 7-Day)
           $table->string('fuh_7Day_gap')->nullable();
           $table->string('fuh_7Day_gap_insurance')->nullable();
           
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
        Schema::dropIfExists('healthchoice_arizona_care_gaps');
    }
};
