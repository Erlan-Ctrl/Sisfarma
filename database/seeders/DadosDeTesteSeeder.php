<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\Offer;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Store;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;

class DadosDeTesteSeeder extends Seeder
{
    public function run(): void
    {
        // UsuÃ¡rios de teste por papel (para validar permissÃµes).
        User::updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Admin (Teste)',
                'password' => 'password',
                'role' => 'admin',
                'is_active' => true,
                'last_login_at' => now(),
            ]
        );
        User::updateOrCreate(
            ['email' => 'gerente@example.com'],
            [
                'name' => 'Gerente (Teste)',
                'password' => 'password',
                'role' => 'gerente',
                'is_active' => true,
                'last_login_at' => now(),
            ]
        );
        User::updateOrCreate(
            ['email' => 'atendente@example.com'],
            [
                'name' => 'Atendente (Teste)',
                'password' => 'password',
                'role' => 'atendente',
                'is_active' => true,
                'last_login_at' => now(),
            ]
        );
        User::updateOrCreate(
            ['email' => 'caixa@example.com'],
            [
                'name' => 'Caixa (Teste)',
                'password' => 'password',
                'role' => 'caixa',
                'is_active' => true,
                'last_login_at' => now(),
            ]
        );

        $user = User::query()->orderBy('id')->first();

        $stores = [
            Store::updateOrCreate(
                ['slug' => 'matriz-centro'],
                [
                    'name' => 'Matriz - Centro',
                    'phone' => '(85) 3000-0000',
                    'whatsapp' => '(85) 99999-0000',
                    'email' => 'matriz@santasaude.test',
                    'zip_code' => '60000-000',
                    'state' => 'CE',
                    'city' => 'Fortaleza',
                    'district' => 'Centro',
                    'street' => 'Av. Principal',
                    'number' => '100',
                    'opening_hours' => 'Seg a Sab 08:00-20:00',
                    'is_active' => true,
                ]
            ),
            Store::updateOrCreate(
                ['slug' => 'filial-aldeota'],
                [
                    'name' => 'Filial - Aldeota',
                    'phone' => '(85) 3000-0001',
                    'whatsapp' => '(85) 99999-0001',
                    'email' => 'aldeota@santasaude.test',
                    'zip_code' => '60150-000',
                    'state' => 'CE',
                    'city' => 'Fortaleza',
                    'district' => 'Aldeota',
                    'street' => 'Rua Secundaria',
                    'number' => '200',
                    'opening_hours' => 'Seg a Sex 08:00-19:00',
                    'is_active' => true,
                ]
            ),
        ];

        $suppliers = [
            Supplier::updateOrCreate(
                ['cnpj' => '12.345.678/0001-90'],
                [
                    'name' => 'Distribuidora Fortaleza',
                    'phone' => '(85) 3333-3333',
                    'email' => 'contato@distribuidora.test',
                    'notes' => 'Fornecedor de teste.',
                    'is_active' => true,
                ]
            ),
            Supplier::updateOrCreate(
                ['cnpj' => '98.765.432/0001-10'],
                [
                    'name' => 'Atacado Saude',
                    'phone' => '(85) 3222-2222',
                    'email' => 'vendas@atacado.test',
                    'notes' => 'Fornecedor de teste.',
                    'is_active' => true,
                ]
            ),
        ];

        $categories = [
            Category::updateOrCreate(
                ['slug' => 'analgesicos'],
                ['name' => 'Analgésicos', 'description' => 'Categoria de teste.', 'is_active' => true, 'sort_order' => 10]
            ),
            Category::updateOrCreate(
                ['slug' => 'vitaminas'],
                ['name' => 'Vitaminas', 'description' => 'Categoria de teste.', 'is_active' => true, 'sort_order' => 20]
            ),
            Category::updateOrCreate(
                ['slug' => 'higiene'],
                ['name' => 'Higiene', 'description' => 'Categoria de teste.', 'is_active' => true, 'sort_order' => 30]
            ),
        ];

        $products = [
            Product::updateOrCreate(
                ['slug' => 'dipirona-500mg'],
                [
                    'name' => 'Dipirona 500mg (20 comprimidos)',
                    'sku' => 'DIP-500-20',
                    'ean' => '7891000000001',
                    'supplier_id' => $suppliers[0]->getKey(),
                    'short_description' => 'Medicamento para dor e febre.',
                    'price' => 12.90,
                    'requires_prescription' => false,
                    'is_active' => true,
                    'is_featured' => true,
                ]
            ),
            Product::updateOrCreate(
                ['slug' => 'paracetamol-750mg'],
                [
                    'name' => 'Paracetamol 750mg (10 comprimidos)',
                    'sku' => 'PARA-750-10',
                    'ean' => '7891000000002',
                    'supplier_id' => $suppliers[0]->getKey(),
                    'short_description' => 'Analgésico/antitérmico.',
                    'price' => 9.50,
                    'requires_prescription' => false,
                    'is_active' => true,
                    'is_featured' => false,
                ]
            ),
            Product::updateOrCreate(
                ['slug' => 'vitamina-c-1g'],
                [
                    'name' => 'Vitamina C 1g (20 comprimidos efervescentes)',
                    'sku' => 'VITC-1G-20',
                    'ean' => '7891000000003',
                    'supplier_id' => $suppliers[1]->getKey(),
                    'short_description' => 'Suplemento de vitamina C.',
                    'price' => 19.90,
                    'requires_prescription' => false,
                    'is_active' => true,
                    'is_featured' => false,
                ]
            ),
            // Sem EAN (para aparecer no relatório)
            Product::updateOrCreate(
                ['slug' => 'shampoo-neutro-300ml'],
                [
                    'name' => 'Shampoo neutro 300ml',
                    'sku' => 'SHA-NEU-300',
                    'ean' => null,
                    'supplier_id' => $suppliers[1]->getKey(),
                    'short_description' => 'Produto de higiene (teste).',
                    'price' => 22.00,
                    'requires_prescription' => false,
                    'is_active' => true,
                    'is_featured' => false,
                ]
            ),
        ];

        // Vínculos categoria-produto (sem remover o que já existir)
        $categories[0]->products()->syncWithoutDetaching([
            $products[0]->getKey() => ['position' => 10],
            $products[1]->getKey() => ['position' => 20],
        ]);
        $categories[1]->products()->syncWithoutDetaching([
            $products[2]->getKey() => ['position' => 10],
        ]);
        $categories[2]->products()->syncWithoutDetaching([
            $products[3]->getKey() => ['position' => 10],
        ]);

        $offer = Offer::updateOrCreate(
            ['slug' => 'oferta-semana'],
            [
                'title' => 'Oferta da semana',
                'description' => 'Oferta de teste para alimentar a tela.',
                'starts_at' => now()->subDays(2),
                'ends_at' => now()->addDays(5),
                'is_active' => true,
            ]
        );
        $offer->products()->syncWithoutDetaching([
            $products[0]->getKey() => ['offer_price' => 10.90, 'discount_percent' => 15, 'position' => 10],
            $products[2]->getKey() => ['offer_price' => 16.90, 'discount_percent' => 15, 'position' => 20],
        ]);

        // Estoque e movimentações iniciais
        foreach ($stores as $store) {
            foreach ($products as $idx => $product) {
                $min = match ($idx) {
                    0 => 10,
                    1 => 8,
                    2 => 6,
                    3 => 5,
                    default => 5,
                };
                $qty = match ($idx) {
                    0 => 7,  // abaixo do mínimo
                    1 => 0,  // zerado
                    2 => 14, // ok
                    3 => 3,  // abaixo do mínimo
                    default => 0,
                };

                $inventory = Inventory::updateOrCreate(
                    ['store_id' => $store->getKey(), 'product_id' => $product->getKey()],
                    ['quantity' => $qty, 'min_quantity' => $min]
                );

                // Cria uma movimentação "ajuste" inicial apenas se ainda não houver movimentações desse produto/loja.
                $hasMovements = InventoryMovement::query()
                    ->where('store_id', $store->getKey())
                    ->where('product_id', $product->getKey())
                    ->exists();
                if (! $hasMovements) {
                    InventoryMovement::create([
                        'store_id' => $store->getKey(),
                        'product_id' => $product->getKey(),
                        'user_id' => $user?->getKey(),
                        'type' => 'adjust',
                        'delta' => $qty,
                        'quantity_before' => 0,
                        'quantity_after' => $qty,
                        'reason' => 'Carga inicial (teste)',
                        'note' => null,
                        'occurred_at' => now()->subDays(3)->addMinutes(random_int(0, 180)),
                        'meta' => ['seed' => true],
                    ]);
                }
            }
        }

        // Compra e venda simples (para alimentar relatórios)
        if (! Purchase::query()->exists()) {
            $purchase = Purchase::create([
                'store_id' => $stores[0]->getKey(),
                'supplier_id' => $suppliers[0]->getKey(),
                'user_id' => $user?->getKey(),
                'reference' => 'NF-TESTE-001',
                'status' => 'posted',
                'occurred_at' => now()->subDays(2),
                'notes' => 'Compra de teste',
                'items_count' => 2,
                'total_cost' => 0,
            ]);

            $items = [
                ['product' => $products[0], 'qty' => 10, 'unit' => 7.50],
                ['product' => $products[1], 'qty' => 6, 'unit' => 6.10],
            ];
            $total = 0;
            foreach ($items as $it) {
                $total += $it['qty'] * $it['unit'];
                PurchaseItem::create([
                    'purchase_id' => $purchase->getKey(),
                    'product_id' => $it['product']->getKey(),
                    'quantity' => $it['qty'],
                    'unit_cost' => $it['unit'],
                ]);
            }
            $purchase->update(['total_cost' => $total]);
        }

        if (! Sale::query()->exists()) {
            $sale = Sale::create([
                'store_id' => $stores[0]->getKey(),
                'user_id' => $user?->getKey(),
                'reference' => 'VENDA-TESTE-001',
                'status' => 'posted',
                'payment_method' => 'pix',
                'occurred_at' => now()->subDay(),
                'notes' => 'Venda de teste',
                'items_count' => 2,
                'total_amount' => 0,
            ]);

            $items = [
                ['product' => $products[0], 'qty' => 2, 'unit' => 12.90],
                ['product' => $products[2], 'qty' => 1, 'unit' => 19.90],
            ];
            $total = 0;
            foreach ($items as $it) {
                $total += $it['qty'] * $it['unit'];
                SaleItem::create([
                    'sale_id' => $sale->getKey(),
                    'product_id' => $it['product']->getKey(),
                    'quantity' => $it['qty'],
                    'unit_price' => $it['unit'],
                ]);
            }
            $sale->update(['total_amount' => $total]);
        }
    }
}
