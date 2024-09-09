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
        Schema::create('questionaires', function (Blueprint $table) {
            $table->id();         
            $table->string('serial_no');  
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->foreignId('program_id')->constrained('programs')->onDelete('cascade');
            $table->bigInteger('insurance_id')->default('0');
            $table->foreignId('clinic_id')->constrained('clinics')->onDelete('cascade');
            $table->json('questions_answers',10000);
            $table->foreignId('created_user')->constrained('users')->onDelete('cascade');
            $table->foreignId('doctor_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->timestamp('signed_date')->nullable();
            $table->date('date_of_service')->nullable();
            $table->string('status')->default('Pre-screening pending');
            $table->bigInteger('status_id')->nullable();
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
        Schema::dropIfExists('questionaires');
    }
};
