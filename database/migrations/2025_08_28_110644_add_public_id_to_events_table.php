<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        // 0) If a unique index already exists from a prior attempt, drop it now
        $indexes = collect(DB::select('SHOW INDEX FROM `events`'))->pluck('Key_name');
        if ($indexes->contains('events_public_id_unique')) {
            Schema::table('events', function (Blueprint $table) {
                $table->dropUnique('events_public_id_unique');
            });
        }

        // 1) Add column if missing (nullable to start)
        if (! Schema::hasColumn('events', 'public_id')) {
            Schema::table('events', function (Blueprint $table) {
                $table->string('public_id', 16)->nullable()->after('id');
            });
        }

        // 2) Backfill unique tokens for:
        //    - rows with NULL public_id
        //    - rows with '' (empty string)
        //    - rows where public_id collides with another row
        DB::table('events')
            ->select('id', 'public_id')
            ->orderBy('id')
            ->chunkById(500, function ($rows) {
                foreach ($rows as $row) {
                    $needsNew = false;

                    // needs token if null or empty
                    if (is_null($row->public_id) || $row->public_id === '') {
                        $needsNew = true;
                    } else {
                        // or if value is duplicated on another row
                        $dup = DB::table('events')
                            ->where('public_id', $row->public_id)
                            ->where('id', '<>', $row->id)
                            ->exists();
                        if ($dup) $needsNew = true;
                    }

                    if ($needsNew) {
                        do {
                            $token = Str::upper(Str::random(12));
                        } while (
                            DB::table('events')->where('public_id', $token)->exists()
                        );

                        DB::table('events')
                            ->where('id', $row->id)
                            ->update(['public_id' => $token]);
                    }
                }
            });

        // 3) Make column NOT NULL (avoid doctrine/dbal by using raw SQL)
        DB::statement("ALTER TABLE `events` MODIFY `public_id` VARCHAR(16) NOT NULL");

        // 4) Add unique index (if not already there)
        $indexes = collect(DB::select('SHOW INDEX FROM `events`'))->pluck('Key_name');
        if (! $indexes->contains('events_public_id_unique')) {
            Schema::table('events', function (Blueprint $table) {
                $table->unique('public_id', 'events_public_id_unique');
            });
        }
    }

    public function down(): void
    {
        $indexes = collect(DB::select('SHOW INDEX FROM `events`'))->pluck('Key_name');
        if ($indexes->contains('events_public_id_unique')) {
            Schema::table('events', function (Blueprint $table) {
                $table->dropUnique('events_public_id_unique');
            });
        }

        if (Schema::hasColumn('events', 'public_id')) {
            Schema::table('events', function (Blueprint $table) {
                $table->dropColumn('public_id');
            });
        }
    }
};
