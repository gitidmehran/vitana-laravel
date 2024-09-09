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
        Schema::create('caregap_details', function (Blueprint $table) {
            $table->id();
            $table->string('caregap_name')->nullable();         
            $table->foreignId('patient_id')->nullable()->constrained('patients')->onDelete('cascade');
            $table->string('caregap_id')->nullable(); 
            $table->foreignId('insurance_id')->nullable()->constrained('insurances')->onDelete('cascade');
        //$table->foreignId('caregap_id')->constrained('care_gaps')->onDelete('cascade');
            $table->foreignId('clinic_id')->nullable()->constrained('clinics')->onDelete('cascade');
            $table->string('caregap_details',10000)->nullable();
            $table->string('filename')->nullable();
            $table->string('status')->nullable();
            $table->foreignId('created_user')->nullable()->constrained('users')->onDelete('cascade');
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
        Schema::dropIfExists('caregap_details');
    }
};
