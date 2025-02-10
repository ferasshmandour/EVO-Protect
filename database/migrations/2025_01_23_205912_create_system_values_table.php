<?php

use App\Models\EvoSystem;
use App\Models\Facility;
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
            $table->foreignIdFor(Facility::class, 'facility_id')->constrained()->onDelete('cascade');
            $table->foreignIdFor(EvoSystem::class, 'system_id')->constrained()->onDelete('cascade');
            $table->string('temperature')->nullable();
            $table->string('smoke')->nullable();
            $table->string('horn')->nullable();
            $table->string('movement')->nullable();
            $table->string('face_status')->nullable();
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
