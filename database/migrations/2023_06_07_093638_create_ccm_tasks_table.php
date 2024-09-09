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
        Schema::create('ccm_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('monthly_encounter_id')->constrained('ccm_monthly_assessments')->onDelete('cascade');
            $table->foreignId('annual_encounter_id')->constrained('questionaires')->onDelete('cascade');
            $table->foreignId('ccm_cordinator_id')->constrained('users')->onDelete('cascade');
            $table->string('task_type')->nullable();
            $table->date('task_date')->nullable();
            $table->string('task_time')->nullable();
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
        Schema::dropIfExists('ccm_tasks');
    }
};
