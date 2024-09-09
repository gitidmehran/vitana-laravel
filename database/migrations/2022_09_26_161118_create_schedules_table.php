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
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->bigInteger('doctor_id')->default('0');
            $table->bigInteger('clinic_id')->constrained('clinics')->onDelete('cascade');
            $table->bigInteger('program_id')->constrained('programs')->onDelete('cascade');
            $table->bigInteger('insurance_id')->constrained('insurances')->onDelete('cascade');
            $table->string('status')->default('not_confirmed');
            $table->timestamp('last_visit')->nullable();
            $table->string('confirmation')->default('not_confirmed');
            $table->date('scheduled_date')->nullable();
            $table->time('scheduled_time')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('schedules');
    }
};
