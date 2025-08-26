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
        Schema::table('events', function (Blueprint $table) {
            //
            $table->string('organizer')->nullable()->after('name');
            $table->string('category')->nullable()->after('organizer');
            $table->json('tags')->nullable()->after('category');
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            //
            $table->dropColumn(['organizer', 'category', 'tags']);
        });
    }
};
