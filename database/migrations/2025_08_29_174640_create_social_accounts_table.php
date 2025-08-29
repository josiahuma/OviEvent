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
        Schema::create('social_accounts', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        $table->string('provider');               // 'google', 'github', etc.
        $table->string('provider_id');            // sub/id from provider
        $table->string('avatar')->nullable();
        $table->string('token', 1024)->nullable();
        $table->string('refresh_token', 1024)->nullable();
        $table->unsignedInteger('expires_in')->nullable();
        $table->timestamps();

        $table->unique(['provider', 'provider_id']);
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('social_accounts');
    }
};
