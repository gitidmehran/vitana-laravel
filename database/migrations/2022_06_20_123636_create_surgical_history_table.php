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
        Schema::create('surgical_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->foreignId('created_user')->constrained('users')->onDelete('cascade');
            $table->string('procedure')->nullable();
            $table->string('reason')->nullable();
            $table->date('date')->nullable();
            $table->string('surgeon')->nullable();
            $table->string('status')->nullable();
            $table->foreignId('deleted_user')->nullable()->constrained('users')->onDelete('cascade');
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
        Schema::dropIfExists('surgical_history');
    }
};
