<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('devices') && !Schema::hasColumn('devices', 'deleted_at')) {
            Schema::table('devices', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        if (Schema::hasTable('device_user')) {
            $hasData = Schema::hasColumn('device_user', 'data');
            $hasType = Schema::hasColumn('device_user', 'type');
            $hasIp = Schema::hasColumn('device_user', 'ip');

            if (!$hasData || !$hasType || !$hasIp) {
                Schema::table('device_user', function (Blueprint $table) use ($hasData, $hasType, $hasIp) {
                    if (!$hasData) {
                        $table->json('data')->nullable();
                    }
                    if (!$hasType) {
                        $table->string('type')->nullable();
                    }
                    if (!$hasIp) {
                        $table->string('ip', 45)->nullable();
                    }
                });
            }
        }

        if (!Schema::hasTable('devices') || !Schema::hasColumn('devices', 'user_id')) {
            return;
        }

        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            $foreignKey = DB::selectOne(
                "SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE
                 WHERE TABLE_SCHEMA = DATABASE()
                   AND TABLE_NAME = 'devices'
                   AND COLUMN_NAME = 'user_id'
                   AND REFERENCED_TABLE_NAME = 'users'
                 LIMIT 1"
            );

            if ($foreignKey && isset($foreignKey->CONSTRAINT_NAME)) {
                $name = str_replace('`', '``', $foreignKey->CONSTRAINT_NAME);
                DB::statement("ALTER TABLE `devices` DROP FOREIGN KEY `{$name}`");
            }

            DB::statement('ALTER TABLE `devices` MODIFY `user_id` BIGINT UNSIGNED NOT NULL');

            $existing = DB::selectOne(
                "SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS
                 WHERE TABLE_SCHEMA = DATABASE()
                   AND TABLE_NAME = 'devices'
                   AND CONSTRAINT_NAME = 'devices_user_id_foreign'
                 LIMIT 1"
            );

            if (!$existing) {
                DB::statement(
                    'ALTER TABLE `devices` ADD CONSTRAINT `devices_user_id_foreign` '
                    . 'FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE'
                );
            }
        }
    }

    public function down(): void
    {
        // Deliberately non-destructive. This migration normalizes legacy installations
        // where these columns may already contain production data. Reversing it by
        // dropping columns would risk data loss.
    }
};
