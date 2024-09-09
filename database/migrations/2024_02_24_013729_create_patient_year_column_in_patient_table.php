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
        Schema::table('patients', function (Blueprint $table) {
            // Check if the column exists before adding it
            if (!Schema::hasColumn('patients', 'patient_year')) {
                $table->string('patient_year')->nullable()->after('status');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('patients', function (Blueprint $table) {
            // Drop the column if it exists
            if (Schema::hasColumn('patients', 'patient_year')) {
                $table->dropColumn('patient_year');
            }
        });
    }
};
