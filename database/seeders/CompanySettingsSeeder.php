<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class CompanySettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            Setting::KEY_COMPANY_NAME    => 'Novarex HSE & Sustainability Consultancy',
            Setting::KEY_COMPANY_TAGLINE => 'HSE & Sustainability Consulting — Mwanza, Tanzania',
            Setting::KEY_COMPANY_ADDRESS => 'Mwanza, Tanzania',
            Setting::KEY_COMPANY_EMAIL   => 'info@novaportal.co.tz',
            Setting::KEY_COMPANY_PHONE   => '',
            Setting::KEY_COMPANY_TIN     => '',
            Setting::KEY_BANK_NAME       => '',
            Setting::KEY_BANK_BRANCH     => '',
            Setting::KEY_BANK_ACCOUNT_NAME   => '',
            Setting::KEY_BANK_ACCOUNT_NUMBER => '',
            Setting::KEY_BANK_SWIFT          => '',
        ];

        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        $this->command->info('Company settings seeded for novaportal.co.tz');
    }
}
