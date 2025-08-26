<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('event_payouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // organizer
            $table->integer('amount')->comment('minor units: pence');
            $table->string('currency', 10)->default('gbp');

            // Bank details (UK)
            $table->string('account_name');
            $table->string('sort_code', 16);
            $table->string('account_number', 20);
            $table->string('iban')->nullable();

            // processing | paid | canceled
            $table->string('status', 20)->default('processing');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_payouts');
    }
};
