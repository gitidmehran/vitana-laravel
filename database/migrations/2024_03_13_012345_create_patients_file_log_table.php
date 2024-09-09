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
        Schema::create('patients_file_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('insurance_id')->constrained('insurances')->onDelete('cascade');
            $table->string('gap_year', 4);
            $table->foreignId('clinic_id')->constrained('clinics')->onDelete('cascade');
            $table->string('file_name')->nullable();
            $table->integer('total_records')->nullable();
            $table->text('missingMemberID')->nullable();
            $table->text('existingPatient')->nullable();
            $table->text('newPatient')->nullable();
            $table->text('lastNameIssue')->nullable();
            $table->text('firstNameIssue')->nullable();
            $table->text('dobIssue')->nullable();
            $table->text('insuranceIssue')->nullable();
            $table->text('genderIssue')->nullable();
            $table->text('multipleIssue')->nullable();
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
        Schema::dropIfExists('patients_file_log');
    }
};
