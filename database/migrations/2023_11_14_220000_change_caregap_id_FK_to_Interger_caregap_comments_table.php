<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::table('caregap_comments', function (Blueprint $table) {
            $table->dropForeign('caregap_comments_caregap_id_foreign');
        });

        Schema::table('care_gaps', function (Blueprint $table) {
        $table->bigInteger('caregap_id')->change();
        });

        Schema::table('caregap_comments', function (Blueprint $table) {
            $table->bigInteger('caregap_id')->change();
            $table->foreign('caregap_id')
                ->references('id')->on('care_gaps')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        $table->foreignId('caregap_id')->constrained('care_gaps')->onDelete('cascade');
    }
};