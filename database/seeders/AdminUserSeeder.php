<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::updateOrCreate(
            ['email' => 'jmakunga1@gmail.com'],
            [
                'name'              => 'J. Makunga',
                'email'             => 'jmakunga1@gmail.com',
                'password'          => Hash::make('makunga@2025'),
                'email_verified_at' => now(),
            ]
        );

        $user->syncRoles(['md']);

        $this->command->info('MD user created: jmakunga1@gmail.com');
    }
}
