<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Store;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Somente criar conta de teste em ambiente local ou quando explicitamente autorizado via env.
        // Isso evita deixar uma conta administrativa previsível em produção.
        if (app()->environment('local') || env('CREATE_TEST_ADMIN', false)) {
            $password = env('TEST_ADMIN_PASSWORD');

            if (empty($password)) {
                // Gera senha aleatória para uso local e registra em storage apenas em ambiente local.
                $password = Str::random(12);
                if (app()->environment('local')) {
                    // Atenção: arquivo gerado localmente apenas para facilitar o desenvolvimento.
                    @file_put_contents(storage_path('app/test-admin.txt'), "test@example.com\n{$password}\n");
                }
            }

            User::updateOrCreate(
                ['email' => 'test@example.com'],
                [
                    'name' => 'Test User',
                    // O cast 'password' => 'hashed' no model vai aplicar hash automaticamente.
                    'password' => $password,
                    'role' => 'admin',
                    'is_active' => true,
                    'last_login_at' => now(),
                ]
            );
        }

        // Dados locais para facilitar o uso do sistema (estoque/relatórios/etc).
        if (app()->environment('local') && Store::query()->count() === 0) {
            $this->call(DadosDeTesteSeeder::class);
        }
    }
}
