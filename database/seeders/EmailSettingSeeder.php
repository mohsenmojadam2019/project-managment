<?php

namespace Database\Seeders;

use App\Models\EmailNotificationSetting;
use Illuminate\Database\Seeder;

class EmailSettingSeeder extends Seeder
{
    public function run(): void
    {
        foreach (EmailNotificationSetting::NOTIFICATIONS as $setting) {
            EmailNotificationSetting::query()->updateOrCreate(
                ['slug' => $setting['slug']],
                $setting
            );
        }
    }
}
