<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL-safe ALTERs without requiring doctrine/dbal
        DB::statement("ALTER TABLE `events` MODIFY `ticket_cost` DECIMAL(12,2) NULL");
        DB::statement("ALTER TABLE `events` MODIFY `public_id` CHAR(26) NOT NULL");
        // If you don't already have a unique index on public_id, uncomment:
        // DB::statement("CREATE UNIQUE INDEX `events_public_id_unique` ON `events` (`public_id`)");
    }

    public function down(): void
    {
        // Revert to something conservative (adjust if you know your old sizes)
        DB::statement("ALTER TABLE `events` MODIFY `ticket_cost` DECIMAL(8,2) NULL");
        DB::statement("ALTER TABLE `events` MODIFY `public_id` VARCHAR(21) NOT NULL");
        // If you created an index in up(), drop it here:
        // DB::statement("DROP INDEX `events_public_id_unique` ON `events`");
    }
};
