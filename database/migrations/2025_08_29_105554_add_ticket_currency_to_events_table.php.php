<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        Schema::table('events', function (Blueprint $table) {
            $table->string('ticket_currency', 3)->default('GBP')->after('ticket_cost');
        });

        DB::table('events')->whereNull('ticket_currency')->update(['ticket_currency' => 'GBP']);
    }

    public function down(): void {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('ticket_currency');
        });
    }
};