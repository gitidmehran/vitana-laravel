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
        // Aetna Medicare Start 
        Schema::table('aetna_medicare_care_gaps', function (Blueprint $table) {
            $table->string('gap_year')->nullable()->after('clinic_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('aetna_medicare_care_gaps', function (Blueprint $table) {
            $table->dropColumn('gap_year');
        });
    }
};
