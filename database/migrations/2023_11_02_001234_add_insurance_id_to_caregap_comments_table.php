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
        Schema::table('caregap_comments', function (Blueprint $table) {
           
            $table->foreignId('insurance_id')->nullable()->after('caregap_id')->constrained('insurances')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('caregap_comments', function (Blueprint $table) {
            $table->dropColumn('insurance_id');
        });
    }
};
