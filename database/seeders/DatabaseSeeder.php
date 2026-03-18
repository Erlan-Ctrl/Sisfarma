<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Store;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                // Cast "hashed" no model vai aplicar hash automaticamente.
                'password' => 'password',
                'role' => 'admin',
                'is_active' => true,
                'last_login_at' => now(),
            ]
        );

        // Dados locais para facilitar o uso do sistema (estoque/relatórios/etc).
        if (app()->environment('local') && Store::query()->count() === 0) {
            $this->call(DadosDeTesteSeeder::class);
        }
    }
}
