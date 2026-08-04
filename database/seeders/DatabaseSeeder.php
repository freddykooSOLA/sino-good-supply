<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin@sinogood.com'],
            [
                'name' => '管理员',
                'password' => Hash::make('Sinogood@2026'),
            ]
        );

        $this->call([
            CategorySeeder::class,
            SettingSeeder::class,
        ]);
    }
}
