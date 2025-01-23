<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('system_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facility_id')->references('id')->on('facilities')->onDelete('cascade');
            $table->foreignId('system_id')->references('id')->on('evo_systems')->onDelete('cascade');
            $table->string('temperature')->nullable();
            $table->string('smoke')->nullable();
            $table->string('spray')->nullable();
            $table->string('status')->nullable();
            $table->string('movement')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_values');
    }
};
