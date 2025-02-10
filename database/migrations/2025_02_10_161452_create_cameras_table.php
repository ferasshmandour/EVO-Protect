<?php

use App\Enums\FacilitySystemStatus;
use App\Models\EvoSystem;
use App\Models\Facility;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cameras', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class, 'user_id')->constrained()->onDelete('cascade');
            $table->foreignIdFor(Facility::class, 'facility_id')->constrained()->onDelete('cascade');
            $table->foreignIdFor(EvoSystem::class, 'system_id')->constrained()->onDelete('cascade');
            $table->string('status')->default(FacilitySystemStatus::off);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cameras');
    }
};
