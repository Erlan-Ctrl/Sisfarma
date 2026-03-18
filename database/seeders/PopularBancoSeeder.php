<?php

namespace Database\Seeders;

use App\Models\AiConversation;
use App\Models\AiKnowledgeEntry;
use App\Models\AiMessage;
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
use Illuminate\Support\Str;

class PopularBancoSeeder extends Seeder
{
    public function run(): void
    {
        // Garante o "mínimo" do sistema (2 lojas + base).
        $this->call(DadosDeTesteSeeder::class);

        $user = User::query()->orderBy('id')->first();

        $extraStores = [
            [
                'slug' => 'teste-meireles',
                'name' => 'Loja Teste - Meireles',
                'state' => 'CE',
                'city' => 'Fortaleza',
                'district' => 'Meireles',
                'street' => 'Av. Beira Mar',
                'number' => '300',
                'opening_hours' => 'Seg a Dom 08:00-22:00',
            ],
        ];
        foreach ($extraStores as $s) {
            Store::firstOrCreate(
                ['slug' => $s['slug']],
                [
                    'name' => $s['name'],
                    'state' => $s['state'] ?? null,
                    'city' => $s['city'] ?? null,
                    'district' => $s['district'] ?? null,
                    'street' => $s['street'] ?? null,
                    'number' => $s['number'] ?? null,
                    'opening_hours' => $s['opening_hours'] ?? null,
                    'is_active' => true,
                ]
            );
        }

        $suppliers = [
            Supplier::firstOrCreate(
                ['cnpj' => '11.111.111/0001-11'],
                ['name' => 'Fornecedor Teste 01', 'phone' => '(85) 3111-1111', 'email' => 'fornecedor01@teste.local', 'is_active' => true]
            ),
            Supplier::firstOrCreate(
                ['cnpj' => '22.222.222/0001-22'],
                ['name' => 'Fornecedor Teste 02', 'phone' => '(85) 3222-2222', 'email' => 'fornecedor02@teste.local', 'is_active' => true]
            ),
            Supplier::firstOrCreate(
                ['cnpj' => '33.333.333/0001-33'],
                ['name' => 'Fornecedor Teste 03', 'phone' => '(85) 3333-3333', 'email' => 'fornecedor03@teste.local', 'is_active' => true]
            ),
        ];

        $categories = [
            Category::firstOrCreate(['slug' => 'antialergicos'], ['name' => 'Antialérgicos', 'is_active' => true, 'sort_order' => 40]),
            Category::firstOrCreate(['slug' => 'gastro'], ['name' => 'Gastro', 'is_active' => true, 'sort_order' => 50]),
            Category::firstOrCreate(['slug' => 'dermocosmeticos'], ['name' => 'Dermocosméticos', 'is_active' => true, 'sort_order' => 60]),
        ];

        $productSpecs = [
            ['slug' => 'ibuprofeno-400mg', 'name' => 'Ibuprofeno 400mg (10 cápsulas)', 'sku' => 'TST-0001', 'ean' => '7891000000010', 'price' => 14.90, 'cat' => 'analgesicos'],
            ['slug' => 'loratadina-10mg', 'name' => 'Loratadina 10mg (12 comprimidos)', 'sku' => 'TST-0002', 'ean' => '7891000000011', 'price' => 18.50, 'cat' => 'antialergicos'],
            ['slug' => 'omeprazol-20mg', 'name' => 'Omeprazol 20mg (28 cápsulas)', 'sku' => 'TST-0003', 'ean' => '7891000000012', 'price' => 24.90, 'cat' => 'gastro'],
            ['slug' => 'soro-fisiologico-500ml', 'name' => 'Soro fisiológico 0,9% 500ml', 'sku' => 'TST-0004', 'ean' => '7891000000013', 'price' => 7.90, 'cat' => 'higiene'],
            ['slug' => 'alcool-70-500ml', 'name' => 'Álcool 70% 500ml', 'sku' => 'TST-0005', 'ean' => '7891000000014', 'price' => 9.90, 'cat' => 'higiene'],
            ['slug' => 'protetor-solar-50', 'name' => 'Protetor solar FPS 50 (120g)', 'sku' => 'TST-0006', 'ean' => '7891000000015', 'price' => 49.90, 'cat' => 'dermocosmeticos'],
            // Alguns sem EAN para alimentar Relatórios
            ['slug' => 'creme-hidratante-200ml', 'name' => 'Creme hidratante 200ml', 'sku' => 'TST-0007', 'ean' => null, 'price' => 29.90, 'cat' => 'dermocosmeticos'],
            ['slug' => 'sabonete-liquido-250ml', 'name' => 'Sabonete líquido 250ml', 'sku' => 'TST-0008', 'ean' => null, 'price' => 15.90, 'cat' => 'higiene'],
        ];

        // Mapa slug->Category
        $catBySlug = Category::query()->whereIn('slug', collect($productSpecs)->pluck('cat')->filter()->unique()->values()->all())->get()->keyBy('slug');

        $products = [];
        foreach ($productSpecs as $idx => $spec) {
            $supplier = $suppliers[$idx % count($suppliers)];

            $product = Product::firstOrCreate(
                ['slug' => $spec['slug']],
                [
                    'name' => $spec['name'],
                    'sku' => $spec['sku'],
                    'ean' => $spec['ean'],
                    'supplier_id' => $supplier->getKey(),
                    'short_description' => 'Produto de teste para desenvolvimento.',
                    'price' => $spec['price'],
                    'requires_prescription' => false,
                    'is_active' => true,
                    'is_featured' => false,
                ]
            );
            $products[] = $product;

            $catSlug = $spec['cat'] ?? null;
            if ($catSlug && $catBySlug->has($catSlug)) {
                $catBySlug->get($catSlug)->products()->syncWithoutDetaching([
                    $product->getKey() => ['position' => 100 + $idx],
                ]);
            }
        }

        // Oferta extra
        $offer = Offer::firstOrCreate(
            ['slug' => 'combo-teste'],
            [
                'title' => 'Combo Teste',
                'description' => 'Oferta de teste com alguns itens.',
                'starts_at' => now()->subDays(1),
                'ends_at' => now()->addDays(10),
                'is_active' => true,
            ]
        );
        if ($products !== []) {
            $offer->products()->syncWithoutDetaching([
                $products[0]->getKey() => ['offer_price' => 12.90, 'discount_percent' => 10, 'position' => 10],
                $products[1]->getKey() => ['offer_price' => 16.90, 'discount_percent' => 8, 'position' => 20],
            ]);
        }

        // Estoque para todos os produtos em todas as lojas ativas (sem sobrescrever o que já existir)
        $stores = Store::query()->where('is_active', true)->orderBy('id')->get();
        $allProducts = Product::query()->where('is_active', true)->orderBy('id')->get();

        foreach ($stores as $store) {
            foreach ($allProducts as $p) {
                Inventory::firstOrCreate(
                    ['store_id' => $store->getKey(), 'product_id' => $p->getKey()],
                    [
                        'quantity' => random_int(0, 40),
                        'min_quantity' => random_int(5, 15),
                    ]
                );
            }
        }

        // Compras e vendas adicionais com impacto real em estoque/movimentações
        $this->seedPurchases($stores, $suppliers, $allProducts, $user);
        $this->seedSales($stores, $allProducts, $user);

        // Conhecimentos/IA (para testar a busca e o assistente)
        $this->seedAi($user);
    }

    /**
     * @param  \Illuminate\Support\Collection<int, Store>  $stores
     * @param  array<int, Supplier>  $suppliers
     * @param  \Illuminate\Support\Collection<int, Product>  $products
     */
    private function seedPurchases($stores, array $suppliers, $products, ?User $user): void
    {
        if ($stores->isEmpty() || $products->isEmpty() || $suppliers === []) {
            return;
        }

        for ($i = 1; $i <= 8; $i++) {
            $reference = 'COMPRA-TESTE-'.str_pad((string) $i, 4, '0', STR_PAD_LEFT);
            if (Purchase::query()->where('reference', $reference)->exists()) {
                continue;
            }

            /** @var Store $store */
            $store = $stores->random();
            $supplier = $suppliers[$i % count($suppliers)];
            $occurredAt = now()->subDays(random_int(1, 14))->addMinutes(random_int(0, 600));

            $purchase = Purchase::create([
                'store_id' => $store->getKey(),
                'supplier_id' => $supplier->getKey(),
                'user_id' => $user?->getKey(),
                'reference' => $reference,
                'status' => 'posted',
                'occurred_at' => $occurredAt,
                'notes' => 'Compra de teste gerada automaticamente.',
                'items_count' => 0,
                'total_cost' => 0,
            ]);

            $picked = $products->random(min(4, $products->count()))->values();
            $itemsCount = 0;
            $totalCost = 0.0;

            foreach ($picked as $p) {
                $qty = random_int(2, 20);
                $unit = (float) (random_int(300, 9000) / 100); // 3.00..90.00

                PurchaseItem::create([
                    'purchase_id' => $purchase->getKey(),
                    'product_id' => $p->getKey(),
                    'quantity' => $qty,
                    'unit_cost' => $unit,
                ]);

                $itemsCount += $qty;
                $totalCost += $qty * $unit;

                $inventory = Inventory::query()
                    ->where('store_id', $store->getKey())
                    ->where('product_id', $p->getKey())
                    ->first();
                if (! $inventory) {
                    $inventory = Inventory::create([
                        'store_id' => $store->getKey(),
                        'product_id' => $p->getKey(),
                        'quantity' => 0,
                        'min_quantity' => null,
                    ]);
                }

                $before = (int) $inventory->quantity;
                $after = $before + $qty;
                $inventory->update(['quantity' => $after]);

                InventoryMovement::create([
                    'store_id' => $store->getKey(),
                    'product_id' => $p->getKey(),
                    'user_id' => $user?->getKey(),
                    'type' => 'in',
                    'delta' => $qty,
                    'quantity_before' => $before,
                    'quantity_after' => $after,
                    'reason' => 'Compra #'.$purchase->getKey(),
                    'note' => $purchase->reference,
                    'occurred_at' => $occurredAt,
                    'meta' => [
                        'source' => 'purchase',
                        'purchase_id' => (int) $purchase->getKey(),
                        'unit_cost' => $unit,
                        'seed' => true,
                    ],
                ]);
            }

            $purchase->update([
                'items_count' => $itemsCount,
                'total_cost' => round($totalCost, 2),
            ]);
        }
    }

    /**
     * @param  \Illuminate\Support\Collection<int, Store>  $stores
     * @param  \Illuminate\Support\Collection<int, Product>  $products
     */
    private function seedSales($stores, $products, ?User $user): void
    {
        if ($stores->isEmpty() || $products->isEmpty()) {
            return;
        }

        $methods = ['dinheiro', 'cartao', 'pix', 'outro'];

        for ($i = 1; $i <= 12; $i++) {
            $reference = 'VENDA-TESTE-'.str_pad((string) $i, 4, '0', STR_PAD_LEFT);
            if (Sale::query()->where('reference', $reference)->exists()) {
                continue;
            }

            /** @var Store $store */
            $store = $stores->random();
            $occurredAt = now()->subDays(random_int(0, 10))->addMinutes(random_int(0, 600));

            $sale = Sale::create([
                'store_id' => $store->getKey(),
                'user_id' => $user?->getKey(),
                'reference' => $reference,
                'status' => 'posted',
                'payment_method' => $methods[$i % count($methods)],
                'occurred_at' => $occurredAt,
                'notes' => 'Venda de teste gerada automaticamente.',
                'items_count' => 0,
                'total_amount' => 0,
            ]);

            $picked = $products->random(min(3, $products->count()))->values();
            $itemsCount = 0;
            $total = 0.0;

            foreach ($picked as $p) {
                $inventory = Inventory::query()
                    ->where('store_id', $store->getKey())
                    ->where('product_id', $p->getKey())
                    ->first();
                if (! $inventory) {
                    $inventory = Inventory::create([
                        'store_id' => $store->getKey(),
                        'product_id' => $p->getKey(),
                        'quantity' => 0,
                        'min_quantity' => null,
                    ]);
                }

                $available = (int) $inventory->quantity;
                $qty = min(max(1, random_int(1, 6)), max(1, $available));
                if ($available <= 0) {
                    // Garante ao menos 1 unidade para não estourar estoque negativo no seed.
                    $inventory->update(['quantity' => 1]);
                    $available = 1;
                    $qty = 1;
                }

                $unit = $p->price !== null ? (float) $p->price : (float) (random_int(500, 12000) / 100);

                SaleItem::create([
                    'sale_id' => $sale->getKey(),
                    'product_id' => $p->getKey(),
                    'quantity' => $qty,
                    'unit_price' => $unit,
                ]);

                $itemsCount += $qty;
                $total += $qty * $unit;

                $before = (int) $available;
                $after = $before - $qty;
                if ($after < 0) {
                    $after = 0;
                }
                $inventory->update(['quantity' => $after]);

                InventoryMovement::create([
                    'store_id' => $store->getKey(),
                    'product_id' => $p->getKey(),
                    'user_id' => $user?->getKey(),
                    'type' => 'out',
                    'delta' => -$qty,
                    'quantity_before' => $before,
                    'quantity_after' => $after,
                    'reason' => 'Venda #'.$sale->getKey(),
                    'note' => $sale->reference,
                    'occurred_at' => $occurredAt,
                    'meta' => [
                        'source' => 'sale',
                        'sale_id' => (int) $sale->getKey(),
                        'payment_method' => $sale->payment_method,
                        'unit_price' => $unit,
                        'seed' => true,
                    ],
                ]);
            }

            $sale->update([
                'items_count' => $itemsCount,
                'total_amount' => round($total, 2),
            ]);
        }
    }

    private function seedAi(?User $user): void
    {
        // Knowledge entries reutilizáveis
        $entries = [
            [
                'title' => 'Procedimento: Movimentar estoque',
                'content' => "Passos:\n1) Acesse Estoque > Movimentar.\n2) Selecione a loja.\n3) Busque o produto por EAN ou nome.\n4) Escolha Entrada/Saída/Ajuste.\n5) Informe a quantidade e confirme.\n\nDica: use Ajuste quando precisar corrigir o saldo final.",
                'tags' => 'estoque,procedimento',
                'is_active' => true,
            ],
            [
                'title' => 'Procedimento: Cadastrar produto com EAN',
                'content' => "Passos:\n1) Vá em Produtos > Novo.\n2) Informe Nome, EAN e SKU (se existir).\n3) Salve.\n\nSe o produto não tiver EAN, deixe em branco e revise depois.",
                'tags' => 'produtos,cadastro,ean',
                'is_active' => true,
            ],
            [
                'title' => 'Checklist: Conferência de estoque',
                'content' => "Checklist:\n- Conferir itens zerados\n- Conferir itens abaixo do mínimo\n- Validar EAN/SKU\n- Registrar movimentações pendentes\n- Revisar produtos inativos",
                'tags' => 'estoque,checklist',
                'is_active' => true,
            ],
        ];

        foreach ($entries as $e) {
            $fingerprint = sha1(Str::lower(trim($e['title'])."\n".trim($e['content'])));
            AiKnowledgeEntry::firstOrCreate(
                ['fingerprint' => $fingerprint],
                [
                    'title' => $e['title'],
                    'content' => $e['content'],
                    'tags' => $e['tags'] ?? null,
                    'is_active' => (bool) ($e['is_active'] ?? true),
                    'source_type' => 'seed',
                    'source_ref' => 'PopularBancoSeeder',
                ]
            );
        }

        // Conversa exemplo (para aparecer no histórico do assistente)
        if (! AiConversation::query()->exists()) {
            $conv = AiConversation::create(['title' => 'Exemplo (seed)']);
            AiMessage::create([
                'ai_conversation_id' => $conv->getKey(),
                'role' => 'user',
                'content' => 'Como faço para movimentar estoque de um produto?',
                'meta' => null,
            ]);
            AiMessage::create([
                'ai_conversation_id' => $conv->getKey(),
                'role' => 'assistant',
                'content' => 'Acesse Estoque > Movimentar, selecione a loja, escolha o produto e registre Entrada/Saída/Ajuste conforme necessário.',
                'meta' => ['seed' => true],
            ]);
        }
    }
}
