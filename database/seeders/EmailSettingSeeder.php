<?php

namespace Database\Seeders;

use App\Models\EmailNotificationSetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class EmailSettingSeeder extends Seeder
{
    public function run(): void
    {
        $notificationSettings = [
            ['setting_name' => 'User Registration/Added by Admin', 'send_email' => 'yes'],
            ['setting_name' => 'Employee Assign to Project', 'send_email' => 'yes'],
            ['setting_name' => 'New Notice Published', 'send_email' => 'no'],
            ['setting_name' => 'User Assign to Task', 'send_email' => 'yes'],
        ];

        foreach ($notificationSettings as $setting) {
            $slug = Str::slug($setting['setting_name']);

            EmailNotificationSetting::query()->updateOrCreate(
                ['slug' => $slug],
                [
                    'setting_name' => $setting['setting_name'],
                    'send_email' => $setting['send_email'],
                ]
            );
        }
    }
}
