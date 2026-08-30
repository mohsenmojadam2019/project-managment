<?php

namespace Database\Seeders;

use App\Models\AwardIcon;
use App\Models\DatabaseBackupSetting;
use App\Models\GdprSetting;
use App\Models\LanguageSetting;
use App\Models\PusherSetting;
use App\Models\PushNotificationSetting;
use App\Models\SocialAuthSetting;
use App\Models\StorageSetting;
use App\Models\TranslateSetting;
use Illuminate\Database\Seeder;

class CoreDatabaseSeeder extends Seeder
{
    /**
     * Seed global application defaults.
     *
     * This seeder is intentionally idempotent: it can be executed repeatedly
     * without creating duplicate global settings, languages or award icons.
     */
    public function run(): void
    {
        $this->dashboardBackupSetting();
        $this->fileStorageSetting();
        $this->gdprSetting();
        $this->languageSettings();
        $this->socialAuth();
        $this->appreciationIcon();
        $this->translateSetting();
        $this->pushNotification();
    }

    private function dashboardBackupSetting(): void
    {
        DatabaseBackupSetting::query()->firstOrCreate([], [
            'status' => 'inactive',
            'hour_of_day' => '',
            'backup_after_days' => '0',
            'delete_backup_after_days' => '0',
        ]);
    }

    private function fileStorageSetting(): void
    {
        StorageSetting::query()->firstOrCreate([
            'filesystem' => 'local',
        ], [
            'status' => 'enabled',
        ]);
    }

    private function gdprSetting(): void
    {
        GdprSetting::query()->firstOrCreate([]);
    }

    private function languageSettings(): void
    {
        foreach (LanguageSetting::LANGUAGES as $language) {
            // Normalise a legacy typo in the source language catalogue.
            if (($language['status'] ?? null) === 'diabled') {
                $language['status'] = 'disabled';
            }

            $code = $language['language_code'];
            unset($language['language_code']);

            LanguageSetting::query()->updateOrCreate(
                ['language_code' => $code],
                $language
            );
        }
    }

    private function socialAuth(): void
    {
        SocialAuthSetting::query()->firstOrCreate([], [
            'facebook_status' => 'disable',
            'google_status' => 'disable',
            'linkedin_status' => 'disable',
            'twitter_status' => 'disable',
        ]);
    }

    private function translateSetting(): void
    {
        TranslateSetting::query()->firstOrCreate([], [
            'google_key' => null,
        ]);
    }

    private function pushNotification(): void
    {
        PushNotificationSetting::query()->firstOrCreate([], [
            'onesignal_app_id' => null,
            'onesignal_rest_api_key' => null,
            'notification_logo' => null,
        ]);

        PusherSetting::query()->firstOrCreate([]);
    }

    private function appreciationIcon(): void
    {
        $icons = [
            ['title' => 'Trophy', 'icon' => 'trophy'],
            ['title' => 'Thumbs Up', 'icon' => 'hand-thumbs-up'],
            ['title' => 'Award', 'icon' => 'award'],
            ['title' => 'Book', 'icon' => 'book'],
            ['title' => 'Gift', 'icon' => 'gift'],
            ['title' => 'Watch', 'icon' => 'watch'],
            ['title' => 'Cup', 'icon' => 'cup-hot'],
            ['title' => 'Puzzle', 'icon' => 'puzzle'],
            ['title' => 'Plane', 'icon' => 'airplane'],
            ['title' => 'Money', 'icon' => 'piggy-bank'],
        ];

        foreach ($icons as $icon) {
            AwardIcon::query()->updateOrCreate(
                ['title' => $icon['title']],
                ['icon' => $icon['icon']]
            );
        }
    }
}
