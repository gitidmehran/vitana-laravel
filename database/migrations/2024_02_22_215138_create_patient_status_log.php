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
        Schema::create('patient_status_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->foreignId('insurance_id')->constrained('insurances')->onDelete('cascade');
            $table->foreignId('doctor_id')->nullable();
            $table->foreignId('clinic_id')->constrained('clinics')->onDelete('cascade');
            $table->integer('status')->nullable();
            $table->integer('group')->nullable();
            $table->string('patient_year', 4);
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
        Schema::dropIfExists('patient_status_log');
    }
};
