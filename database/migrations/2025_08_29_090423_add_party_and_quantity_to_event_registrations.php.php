<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('event_registrations', function (Blueprint $table) {
            // how many tickets were purchased (paid flow)
            $table->unsignedInteger('quantity')->default(1)->after('amount');
            // for free events: extra people coming with the registrant
            $table->unsignedInteger('party_adults')->default(0)->after('quantity');
            $table->unsignedInteger('party_children')->default(0)->after('party_adults');
        });
    }

    public function down(): void
    {
        Schema::table('event_registrations', function (Blueprint $table) {
            $table->dropColumn(['quantity', 'party_adults', 'party_children']);
        });
    }
};
