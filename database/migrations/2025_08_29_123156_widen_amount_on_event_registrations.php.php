<?php

// database/migrations/2025_08_29_XXXXXX_widen_amount_on_event_registrations.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('event_registrations', function (Blueprint $table) {
            // Increase precision to something generous
            $table->decimal('amount', 12, 2)->default(0)->change();
        });
    }

    public function down(): void
    {
        Schema::table('event_registrations', function (Blueprint $table) {
            // Revert if needed (adjust to your previous precision)
            $table->decimal('amount', 8, 2)->default(0)->change();
        });
    }
};

