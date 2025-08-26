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
        Schema::create('event_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null'); // null for guests
            $table->string('name');
            $table->string('email');
            $table->string('mobile')->nullable();
            $table->enum('status', ['pending', 'paid', 'free'])->default('pending'); // free=confirmed, paid=confirmed, pending=awaiting payment
            $table->string('stripe_session_id')->nullable();
            $table->decimal('amount', 8, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_registrations');
    }
};
