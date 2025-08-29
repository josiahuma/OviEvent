<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // currency
        if (! Schema::hasColumn('event_registrations', 'currency')) {
            Schema::table('event_registrations', function (Blueprint $table) {
                $table->string('currency', 3)->nullable()->after('amount');
            });
        }

        // quantity (paid events)
        if (! Schema::hasColumn('event_registrations', 'quantity')) {
            Schema::table('event_registrations', function (Blueprint $table) {
                // omit ->after() to avoid errors if 'currency' isn't there yet on some envs
                $table->unsignedInteger('quantity')->default(1);
            });
        }

        // party fields (free events)
        if (! Schema::hasColumn('event_registrations', 'party_adults')) {
            Schema::table('event_registrations', function (Blueprint $table) {
                $table->unsignedInteger('party_adults')->default(0);
            });
        }

        if (! Schema::hasColumn('event_registrations', 'party_children')) {
            Schema::table('event_registrations', function (Blueprint $table) {
                $table->unsignedInteger('party_children')->default(0);
            });
        }
    }

    public function down(): void
    {
        // Drop only if present (safe on all envs)
        if (Schema::hasColumn('event_registrations', 'party_children')) {
            Schema::table('event_registrations', function (Blueprint $table) {
                $table->dropColumn('party_children');
            });
        }
        if (Schema::hasColumn('event_registrations', 'party_adults')) {
            Schema::table('event_registrations', function (Blueprint $table) {
                $table->dropColumn('party_adults');
            });
        }
        if (Schema::hasColumn('event_registrations', 'quantity')) {
            Schema::table('event_registrations', function (Blueprint $table) {
                $table->dropColumn('quantity');
            });
        }
        if (Schema::hasColumn('event_registrations', 'currency')) {
            Schema::table('event_registrations', function (Blueprint $table) {
                $table->dropColumn('currency');
            });
        }
    }
};
