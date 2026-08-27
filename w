php artisan migrate --path=UniversalBundle/Modules/RestAPI/Database/Migrations
 php artisan migrate --path=UniversalBundle/Modules/*/Database/Migrations

// php artisan db:seed --class="UniversalBundle\Modules\Letter\Database\Seeders\LetterDatabaseSeeder"

php artisan db:seed --class="Modules\Letter\Database\Seeders\LetterDatabaseSeeder"
 php artisan db:seed --class="Modules\Asset\Database\Seeders\AssetDatabaseSeeder"
php artisan db:seed --class="Modules\ProjectRoadmap\Database\Seeders\ProjectRoadmapDatabaseSeeder"
ALTER TABLE devices ADD COLUMN deleted_at TIMESTAMP NULL DEFAULT NULL;
 php artisan vendor:publish --tag=device-tracking-migrations
ALTER TABLE devices ADD COLUMN data JSON NULL AFTER details;
ALTER TABLE devices ADD COLUMN device_type VARCHAR(255) NULL AFTER device_uuid;
ALTER TABLE devices ADD COLUMN ip VARCHAR(45) NULL AFTER device_type;


---------------------------------------------------
ALTER TABLE `devices`
DROP FOREIGN KEY `devices_user_id_foreign`;
//
ALTER TABLE `devices`
MODIFY COLUMN `user_id` BIGINT UNSIGNED NOT NULL;

ALTER TABLE device_user DROP FOREIGN KEY device_user_device_id_foreign;

