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
        Schema::table('questionaires', function (Blueprint $table) {
            $table->integer('coordinator_id')->nullable()->after('doctor_id');
        });
        
        Schema::table('ccm_monthly_assessments', function (Blueprint $table) {
            $table->integer('coordinator_id')->nullable()->after('patient_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('questionaires', function (Blueprint $table) {
            $table->dropColumn('coordinator_id');
        });
        
        Schema::table('ccm_monthly_assessments', function (Blueprint $table) {
            $table->dropColumn('coordinator_id');
        });
    }
};
