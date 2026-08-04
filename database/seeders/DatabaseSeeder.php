<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin@sinogood.com'],
            [
                'name' => '管理员',
                'password' => 'Sinogood@2026',
                'role' => User::ROLE_ADMIN,
            ]
        );

        $this->call([
            CategorySeeder::class,
            SettingSeeder::class,
        ]);
    }
}
