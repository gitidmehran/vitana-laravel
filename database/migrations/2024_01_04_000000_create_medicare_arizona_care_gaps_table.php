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
        Schema::create('medicare_arizona_care_gaps', function (Blueprint $table) {
            $table->id();
            $table->string('member_id')->nullable();
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->foreignId('doctor_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('insurance_id')->nullable()->constrained('insurances')->onDelete('cascade');
            $table->foreignId('clinic_id')->constrained('clinics')->onDelete('cascade');

            $table->string('gap_year')->nullable();
            
            $table->string('breast_cancer_gap')->nullable();
            $table->string('breast_cancer_gap_insurance')->nullable();

            $table->string('colorectal_cancer_gap')->nullable();
            $table->string('colorectal_cancer_gap_insurance')->nullable();

            $table->string('high_bp_gap')->nullable();
            $table->string('high_bp_gap_insurance')->nullable();

            $table->string('hba1c_gap')->nullable();
            $table->string('hba1c_gap_insurance')->nullable();
            
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
        Schema::dropIfExists('medicare_arizona_care_gaps');
    }
};
