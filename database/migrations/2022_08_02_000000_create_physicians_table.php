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
        Schema::create('physicians', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('mid_name')->nullable();
            $table->string('last_name')->nullable();
            $table->integer('role');
            $table->integer('age')->nullable();
            $table->string('contact_no')->nullable();
            $table->string('gender')->nullable();
            $table->string('address')->nullable();
            $table->string('speciality')->nullable();
            $table->foreignId('clinic_id')->constrained('clinics')->onDelete('cascade');
            $table->foreignId('created_user')->nullable()->constrained('users')->onDelete('cascade');
            $table->rememberToken();
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
        Schema::dropIfExists('physicians');
    }
};
