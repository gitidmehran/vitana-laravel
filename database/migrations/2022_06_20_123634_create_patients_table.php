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
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->string('identity');
            $table->string('first_name');
            $table->string('mid_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email')->nullable();
            $table->string('contact_no')->nullable();
            $table->string('cell')->nullable();
            $table->foreignId('doctor_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('change_doctor_id')->nullable();
            $table->foreignId('insurance_id')->nullable()->constrained('insurances')->onDelete('cascade');
            $table->foreignId('clinic_id')->constrained('clinics')->onDelete('cascade');
            $table->date('dob')->nullable();
            $table->integer('age');
            $table->string('gender');
            $table->string('address')->nullable();
            $table->string('change_address')->nullable();
            $table->string('disease')->nullable();
            $table->string('address_2')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->integer('zipCode')->nullable();
            $table->date('dod')->nullable();
            $table->json('family_history',10000);
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
        Schema::dropIfExists('patients');
    }
};
