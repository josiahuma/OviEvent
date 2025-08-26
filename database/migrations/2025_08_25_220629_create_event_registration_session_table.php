<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('event_registration_session', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_registration_id')->constrained('event_registrations')->onDelete('cascade');
            $table->foreignId('event_session_id')->constrained('event_sessions')->onDelete('cascade');
            $table->timestamps();
            $table->unique(['event_registration_id', 'event_session_id'], 'event_reg_session_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
         Schema::table('event_registration_session', function (Blueprint $table) {
            // Drop the custom-named index first (safe even if missing)
            $table->dropUnique('event_reg_session_unique');
        });

        Schema::dropIfExists('event_registration_session');
    }
};
