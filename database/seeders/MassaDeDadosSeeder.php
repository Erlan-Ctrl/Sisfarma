<?php

namespace Database\Seeders;

use App\Services\Inventory\InventoryService;
use App\Models\Category;
use App\Models\Inventory;
use App\Models\Offer;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Store;
use App\Models\Supplier;
use App\Models\Transfer;
use App\Models\TransferItem;
use App\Models\User;
use Faker\Factory as FakerFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MassaDeDadosSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        DB::disableQueryLog();

        // Garante o "mínimo" do sistema.
        $this->call(DadosDeTesteSeeder::class);

        $faker = FakerFactory::create('pt_BR');
        $runId = now()->format('YmdHis').'-'.Str::lower(Str::random(4));

        $daysBack = $this->intEnv('MASSA_DAYS_BACK', 60);
        $minStores = $this->intEnv('MASSA_STORES', 2);
        $createSuppliers = $this->intEnv('MASSA_SUPPLIERS', 30);
        $createCategories = $this->intEnv('MASSA_CATEGORIES', 20);
        $createProducts = $this->intEnv('MASSA_PRODUCTS', 2000);
        $createOffers = $this->intEnv('MASSA_OFFERS', 10);
        $createPurchases = $this->intEnv('MASSA_PURCHASES', 120);
        $createSales = $this->intEnv('MASSA_SALES', 300);
        $createTransfers = $this->intEnv('MASSA_TRANSFERS', 80);
        $inventoryAll = $this->boolEnv('MASSA_INVENTORY_ALL', true);

        $this->log("MassaDeDadosSeeder: runId={$runId}");

        $userId = User::query()->orderBy('id')->value('id');

        [$stores, $newStoreIds] = $this->ensureStores($minStores, $runId, $faker);
        $storeIds = $stores->pluck('id')->all();

        $this->createSuppliers($createSuppliers, $runId, $faker);
        $this->createCategories($createCategories, $runId, $faker);
        $newProductIds = $this->createProducts($createProducts, $runId, $faker);

        $supplierIds = Supplier::query()->where('is_active', true)->pluck('id')->all();
        $categoryIds = Category::query()->where('is_active', true)->pluck('id')->all();

        $this->attachCategoriesToProducts($newProductIds, $categoryIds, $faker);

        $this->ensureInventories($storeIds, $newStoreIds, $newProductIds, $inventoryAll, $faker, $daysBack);

        $offerIds = $this->createOffers($createOffers, $runId, $faker);
        $allProductRows = Product::query()->where('is_active', true)->get(['id', 'price']);
        $allProductIds = $allProductRows->pluck('id')->all();
        $priceByProductId = $allProductRows
            ->pluck('price', 'id')
            ->map(fn ($value) => $value !== null ? (float) $value : null)
            ->all();

        $this->attachOffersToProducts($offerIds, $allProductIds, $priceByProductId, $faker);
        $this->seedPurchases($createPurchases, $daysBack, $storeIds, $supplierIds, $allProductIds, $priceByProductId, $userId, $runId, $faker);
        $this->seedSales($createSales, $daysBack, $storeIds, $allProductIds, $priceByProductId, $userId, $runId, $faker);
        $this->seedTransfers($createTransfers, $daysBack, $storeIds, $allProductIds, $userId, $runId, $faker);
    }

    /**
     * @return array{0: \Illuminate\Support\Collection<int, Store>, 1: array<int,int>}
     */
    private function ensureStores(int $minStores, string $runId, \Faker\Generator $faker): array
    {
        $stores = Store::query()->where('is_active', true)->orderBy('id')->get();
        $newIds = [];

        $missing = max(0, $minStores - $stores->count());
        if ($missing === 0) {
            return [$stores, $newIds];
        }

        for ($i = 1; $i <= $missing; $i++) {
            $slug = 'massa-'.$runId.'-loja-'.str_pad((string) $i, 3, '0', STR_PAD_LEFT);
            $store = Store::create([
                'name' => 'Loja Massa '.str_pad((string) $i, 3, '0', STR_PAD_LEFT),
                'slug' => $slug,
                'phone' => $faker->phoneNumber(),
                'whatsapp' => $faker->phoneNumber(),
                'email' => "loja{$i}.massa.{$runId}@sisfarma.test",
                'zip_code' => $faker->postcode(),
                'state' => 'CE',
                'city' => 'Fortaleza',
                'district' => $faker->city(),
                'street' => $faker->streetName(),
                'number' => (string) $faker->buildingNumber(),
                'opening_hours' => 'Seg a Sab 08:00-20:00',
                'is_active' => true,
            ]);
            $newIds[] = (int) $store->getKey();
        }

        $stores = Store::query()->where('is_active', true)->orderBy('id')->get();

        return [$stores, $newIds];
    }

    private function createSuppliers(int $count, string $runId, \Faker\Generator $faker): void
    {
        if ($count < 1) {
            return;
        }

        $hash = str_pad((string) abs((int) crc32($runId)), 10, '0', STR_PAD_LEFT);
        $prefix8 = substr($hash, -8);

        $now = now();
        $rows = [];

        for ($i = 1; $i <= $count; $i++) {
            $cnpjDigits = $prefix8.str_pad((string) $i, 6, '0', STR_PAD_LEFT);

            $rows[] = [
                'name' => 'Fornecedor Massa '.str_pad((string) $i, 3, '0', STR_PAD_LEFT),
                'cnpj' => $this->formatCnpj($cnpjDigits),
                'phone' => $faker->phoneNumber(),
                'email' => "fornecedor{$i}.massa.{$runId}@sisfarma.test",
                'notes' => 'Fornecedor de teste (massa de dados).',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if (count($rows) >= 500) {
                DB::table('suppliers')->insert($rows);
                $rows = [];
            }
        }

        if ($rows !== []) {
            DB::table('suppliers')->insert($rows);
        }
    }

    private function createCategories(int $count, string $runId, \Faker\Generator $faker): void
    {
        if ($count < 1) {
            return;
        }

        $now = now();
        $rows = [];

        for ($i = 1; $i <= $count; $i++) {
            $name = 'Categoria Massa '.str_pad((string) $i, 3, '0', STR_PAD_LEFT);
            $slug = 'massa-'.$runId.'-categoria-'.str_pad((string) $i, 4, '0', STR_PAD_LEFT);

            $rows[] = [
                'name' => $name,
                'slug' => $slug,
                'description' => $faker->sentence(10),
                'is_active' => true,
                'sort_order' => 1000 + $i,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if (count($rows) >= 500) {
                DB::table('categories')->insert($rows);
                $rows = [];
            }
        }

        if ($rows !== []) {
            DB::table('categories')->insert($rows);
        }
    }

    /**
     * @return array<int,int> IDs dos produtos criados nesta execucao.
     */
    private function createProducts(int $count, string $runId, \Faker\Generator $faker): array
    {
        if ($count < 1) {
            return [];
        }

        $supplierIds = Supplier::query()->where('is_active', true)->pluck('id')->all();

        $seedBase = abs((int) crc32($runId));
        $now = now();
        $rows = [];

        for ($i = 1; $i <= $count; $i++) {
            $name = $this->makeProductName($faker);
            $slug = 'massa-'.$runId.'-produto-'.str_pad((string) $i, 7, '0', STR_PAD_LEFT);
            $sku = 'MS-'.preg_replace('/\\D+/', '', $runId).'-'.str_pad((string) $i, 7, '0', STR_PAD_LEFT);

            $ean = null;
            if (random_int(1, 100) > 3) {
                $ean = $this->ean13FromSeed($seedBase + $i);
            }

            $price = random_int(199, 39999) / 100;

            $rows[] = [
                'name' => $name,
                'slug' => $slug,
                'sku' => $sku,
                'ean' => $ean,
                'supplier_id' => $supplierIds !== [] ? $supplierIds[array_rand($supplierIds)] : null,
                'short_description' => $faker->sentence(12),
                'description' => $faker->paragraph(3),
                'image_url' => null,
                'price' => $price,
                'requires_prescription' => random_int(1, 100) <= 15,
                'is_active' => true,
                'is_featured' => random_int(1, 100) <= 3,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if (count($rows) >= 500) {
                DB::table('products')->insert($rows);
                $rows = [];
            }
        }

        if ($rows !== []) {
            DB::table('products')->insert($rows);
        }

        $prefix = 'massa-'.$runId.'-produto-';

        return Product::query()
            ->where('slug', 'like', $prefix.'%')
            ->orderBy('id')
            ->pluck('id')
            ->all();
    }

    private function makeProductName(\Faker\Generator $faker): string
    {
        $isMedicine = random_int(1, 100) <= 70;

        if ($isMedicine) {
            $active = $faker->randomElement([
                'Dipirona',
                'Paracetamol',
                'Ibuprofeno',
                'Loratadina',
                'Cetirizina',
                'Omeprazol',
                'Pantoprazol',
                'Amoxicilina',
                'Azitromicina',
                'Losartana',
                'Atenolol',
                'Metformina',
                'Sinvastatina',
                'Fluconazol',
                'Clonazepam',
                'Diazepam',
                'Prednisona',
                'Dexametasona',
                'Salbutamol',
                'Nimesulida',
            ]);
            $strength = $faker->randomElement([
                '5mg',
                '10mg',
                '20mg',
                '25mg',
                '40mg',
                '50mg',
                '100mg',
                '150mg',
                '250mg',
                '400mg',
                '500mg',
                '750mg',
                '1g',
            ]);
            $form = $faker->randomElement([
                'comprimidos',
                'capsulas',
                'drageas',
                'gotas',
                'xarope',
                'suspensao oral',
                'pomada',
                'creme',
                'spray',
            ]);
            $pack = $faker->randomElement(['6', '8', '10', '12', '14', '16', '20', '24', '28', '30', '60']);

            return "{$active} {$strength} ({$pack} {$form})";
        }

        $type = $faker->randomElement([
            'Shampoo',
            'Condicionador',
            'Sabonete',
            'Sabonete liquido',
            'Creme hidratante',
            'Protetor solar',
            'Pasta de dente',
            'Enxaguante bucal',
            'Algodao',
            'Curativo adesivo',
        ]);
        $variant = $faker->randomElement([
            'Neutro',
            'Aloe vera',
            'Vitaminado',
            'Suave',
            'Sem perfume',
            'Antisseptico',
            'Infantil',
            'Premium',
        ]);
        $size = $faker->randomElement(['50ml', '100ml', '120ml', '150ml', '200ml', '250ml', '300ml', '500ml']);

        return "{$type} {$variant} {$size}";
    }

    /**
     * @param  array<int,int>  $productIds
     * @param  array<int,int>  $categoryIds
     */
    private function attachCategoriesToProducts(array $productIds, array $categoryIds, \Faker\Generator $faker): void
    {
        if ($productIds === [] || $categoryIds === []) {
            return;
        }

        $rows = [];

        foreach ($productIds as $productId) {
            $max = min(3, count($categoryIds));
            $take = max(1, random_int(1, $max));

            $picked = [];
            while (count($picked) < $take) {
                $picked[(int) $categoryIds[array_rand($categoryIds)]] = true;
            }

            $position = random_int(1, 200);
            foreach (array_keys($picked) as $catId) {
                $rows[] = [
                    'category_id' => (int) $catId,
                    'product_id' => (int) $productId,
                    'position' => $position,
                ];
                $position += 10;
            }

            if (count($rows) >= 5000) {
                DB::table('category_product')->insertOrIgnore($rows);
                $rows = [];
            }
        }

        if ($rows !== []) {
            DB::table('category_product')->insertOrIgnore($rows);
        }
    }

    /**
     * Cria inventários sem sobrescrever os que já existirem.
     *
     * @param  array<int,int>  $storeIds
     * @param  array<int,int>  $newStoreIds
     * @param  array<int,int>  $newProductIds
     */
    private function ensureInventories(
        array $storeIds,
        array $newStoreIds,
        array $newProductIds,
        bool $inventoryAll,
        \Faker\Generator $faker,
        int $daysBack,
    ): void {
        if ($storeIds === []) {
            return;
        }

        $allProductIds = [];
        if ($inventoryAll || $newStoreIds !== []) {
            $allProductIds = Product::query()->where('is_active', true)->pluck('id')->all();
        }

        $now = now();
        $chunkSize = 2000;

        // Para lojas existentes: só cria inventário para produtos novos (ou todos, se MASSA_INVENTORY_ALL=true).
        foreach ($storeIds as $storeId) {
            $productIds = $inventoryAll ? $allProductIds : $newProductIds;
            if ($productIds === []) {
                continue;
            }

            foreach (array_chunk($productIds, $chunkSize) as $chunk) {
                $rows = [];

                foreach ($chunk as $productId) {
                    $min = random_int(3, 25);
                    $roll = random_int(1, 100);

                    if ($roll <= 5) {
                        $qty = 0;
                    } elseif ($roll <= 30) {
                        $qty = random_int(1, max(1, $min - 1));
                    } elseif ($roll <= 40) {
                        $qty = random_int($min * 3, $min * 10);
                    } else {
                        $qty = random_int($min, $min * 3);
                    }

                    $lastUnitCost = null;
                    $lastPurchaseAt = null;
                    if (random_int(1, 100) <= 70) {
                        $lastUnitCost = random_int(100, 25000) / 100;
                        $lastPurchaseAt = $faker->dateTimeBetween("-{$daysBack} days", 'now');
                    }

                    $rows[] = [
                        'store_id' => (int) $storeId,
                        'product_id' => (int) $productId,
                        'quantity' => $qty,
                        'min_quantity' => $min,
                        'last_unit_cost' => $lastUnitCost,
                        'last_purchase_at' => $lastPurchaseAt,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                DB::table('inventories')->insertOrIgnore($rows);
            }
        }

        if (! $inventoryAll && $newStoreIds !== []) {
            // Para lojas novas: garante inventário para todos os produtos ativos.
            foreach ($newStoreIds as $storeId) {
                if ($allProductIds === []) {
                    break;
                }

                foreach (array_chunk($allProductIds, $chunkSize) as $chunk) {
                    $rows = [];

                    foreach ($chunk as $productId) {
                        $min = random_int(3, 25);
                        $qty = random_int(0, $min * 6);

                        $rows[] = [
                            'store_id' => (int) $storeId,
                            'product_id' => (int) $productId,
                            'quantity' => $qty,
                            'min_quantity' => $min,
                            'last_unit_cost' => null,
                            'last_purchase_at' => null,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }

                    DB::table('inventories')->insertOrIgnore($rows);
                }
            }
        }
    }

    /**
     * @return array<int,int> IDs das ofertas criadas nesta execucao.
     */
    private function createOffers(int $count, string $runId, \Faker\Generator $faker): array
    {
        if ($count < 1) {
            return [];
        }

        $now = now();
        $rows = [];

        for ($i = 1; $i <= $count; $i++) {
            $slug = 'massa-'.$runId.'-oferta-'.str_pad((string) $i, 4, '0', STR_PAD_LEFT);

            $rows[] = [
                'title' => 'Oferta Massa '.str_pad((string) $i, 3, '0', STR_PAD_LEFT),
                'slug' => $slug,
                'description' => $faker->sentence(12),
                'banner_url' => null,
                'starts_at' => now()->subDays(random_int(0, 5)),
                'ends_at' => now()->addDays(random_int(5, 30)),
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if (count($rows) >= 200) {
                DB::table('offers')->insert($rows);
                $rows = [];
            }
        }

        if ($rows !== []) {
            DB::table('offers')->insert($rows);
        }

        $prefix = 'massa-'.$runId.'-oferta-';

        return Offer::query()
            ->where('slug', 'like', $prefix.'%')
            ->orderBy('id')
            ->pluck('id')
            ->all();
    }

    /**
     * @param  array<int,int>  $offerIds
     * @param  array<int,int>  $productIds
     * @param  array<int, float|null>  $priceByProductId
     */
    private function attachOffersToProducts(array $offerIds, array $productIds, array $priceByProductId, \Faker\Generator $faker): void
    {
        if ($offerIds === [] || $productIds === []) {
            return;
        }

        $rows = [];

        foreach ($offerIds as $offerId) {
            $take = min(count($productIds), random_int(10, 40));
            if ($take < 1) {
                continue;
            }

            $keys = array_rand($productIds, $take);
            $keys = is_array($keys) ? $keys : [$keys];

            $position = 10;
            foreach ($keys as $key) {
                $productId = (int) $productIds[$key];
                $price = $priceByProductId[$productId] ?? null;

                $discount = random_int(5, 30);
                $offerPrice = $price !== null ? round($price * (1 - ($discount / 100)), 2) : null;

                $rows[] = [
                    'offer_id' => (int) $offerId,
                    'product_id' => $productId,
                    'offer_price' => $offerPrice,
                    'discount_percent' => $price !== null ? $discount : null,
                    'position' => $position,
                ];
                $position += 10;
            }

            if (count($rows) >= 5000) {
                DB::table('offer_product')->insertOrIgnore($rows);
                $rows = [];
            }
        }

        if ($rows !== []) {
            DB::table('offer_product')->insertOrIgnore($rows);
        }
    }

    /**
     * @param  array<int,int>  $storeIds
     * @param  array<int,int>  $supplierIds
     * @param  array<int,int>  $productIds
     * @param  array<int, float|null>  $priceByProductId
     */
    private function seedPurchases(
        int $count,
        int $daysBack,
        array $storeIds,
        array $supplierIds,
        array $productIds,
        array $priceByProductId,
        ?int $userId,
        string $runId,
        \Faker\Generator $faker,
    ): void {
        if ($count < 1 || $storeIds === [] || $supplierIds === [] || $productIds === []) {
            return;
        }

        /** @var InventoryService $inventory */
        $inventory = app(InventoryService::class);

        $seedBase = abs((int) crc32($runId.'|purchase'));

        for ($i = 1; $i <= $count; $i++) {
            $storeId = (int) $storeIds[array_rand($storeIds)];
            $supplierId = (int) $supplierIds[array_rand($supplierIds)];
            $occurredAt = $faker->dateTimeBetween("-{$daysBack} days", 'now');

            $reference = 'MASSA-'.$runId.'-COMPRA-'.str_pad((string) $i, 7, '0', STR_PAD_LEFT);

            DB::transaction(function () use (
                $inventory,
                $storeId,
                $supplierId,
                $userId,
                $occurredAt,
                $reference,
                $productIds,
                $priceByProductId,
                $seedBase,
                $i,
                $runId,
            ): void {
                $hasXml = random_int(1, 100) <= 15;
                $nfeKey = null;
                $xmlHash = null;
                $xmlPath = null;
                $nfeNumber = null;
                $nfeSeries = null;

                if ($hasXml) {
                    // 44 digitos (chave NFe) so para testes (nao necessariamente valida).
                    $nfeKey = '35'.str_pad((string) ($seedBase + $i), 42, '0', STR_PAD_LEFT);
                    $xmlHash = hash('sha256', $runId.'|purchase|'.$i);
                    $xmlPath = 'storage/nfe/massa/'.$runId.'/'.$nfeKey.'.xml';
                    $nfeNumber = (string) random_int(1, 999999);
                    $nfeSeries = (string) random_int(1, 999);
                }

                $purchase = Purchase::create([
                    'store_id' => $storeId,
                    'supplier_id' => $supplierId,
                    'user_id' => $userId,
                    'reference' => $reference,
                    'nfe_key' => $nfeKey,
                    'nfe_number' => $nfeNumber,
                    'nfe_series' => $nfeSeries,
                    'xml_hash' => $xmlHash,
                    'xml_path' => $xmlPath,
                    'status' => 'posted',
                    'occurred_at' => $occurredAt,
                    'notes' => 'Compra (massa de dados).',
                    'items_count' => 0,
                    'total_cost' => 0,
                ]);

                $itemsWanted = random_int(2, 6);
                $take = min($itemsWanted, count($productIds));
                $keys = array_rand($productIds, $take);
                $keys = is_array($keys) ? $keys : [$keys];

                $now = now();
                $itemRows = [];
                $total = 0.0;

                foreach ($keys as $key) {
                    $productId = (int) $productIds[$key];
                    $qty = random_int(5, 60);

                    $price = $priceByProductId[$productId] ?? null;
                    $unitCost = $price !== null
                        ? round(max(0.5, $price * (random_int(40, 85) / 100)), 2)
                        : round(random_int(100, 25000) / 100, 2);

                    $total += $qty * $unitCost;

                    $inventory->apply(
                        $storeId,
                        $productId,
                        'in',
                        $qty,
                        $userId,
                        $occurredAt,
                        'Compra',
                        'Compra (massa de dados).',
                        ['purchase_id' => (int) $purchase->getKey()],
                        null,
                        (float) $unitCost,
                        $occurredAt,
                        false,
                    );

                    $itemRows[] = [
                        'purchase_id' => (int) $purchase->getKey(),
                        'product_id' => $productId,
                        'quantity' => $qty,
                        'unit_cost' => $unitCost,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                PurchaseItem::insert($itemRows);

                $purchase->update([
                    'items_count' => count($itemRows),
                    'total_cost' => round($total, 2),
                ]);
            });
        }
    }

    /**
     * @param  array<int,int>  $storeIds
     * @param  array<int,int>  $productIds
     * @param  array<int, float|null>  $priceByProductId
     */
    private function seedSales(
        int $count,
        int $daysBack,
        array $storeIds,
        array $productIds,
        array $priceByProductId,
        ?int $userId,
        string $runId,
        \Faker\Generator $faker,
    ): void {
        if ($count < 1 || $storeIds === [] || $productIds === []) {
            return;
        }

        /** @var InventoryService $inventory */
        $inventory = app(InventoryService::class);

        $created = 0;
        $attempt = 0;
        $maxAttempts = max($count * 5, 50);

        $paymentMethods = ['dinheiro', 'cartao', 'pix', 'outro'];

        while ($created < $count && $attempt < $maxAttempts) {
            $attempt++;

            $storeId = (int) $storeIds[array_rand($storeIds)];
            $occurredAt = $faker->dateTimeBetween("-{$daysBack} days", 'now');

            $reference = 'MASSA-'.$runId.'-VENDA-'.str_pad((string) $attempt, 8, '0', STR_PAD_LEFT);

            $ok = false;

            DB::transaction(function () use (
                $inventory,
                $storeId,
                $userId,
                $occurredAt,
                $reference,
                $productIds,
                $priceByProductId,
                $paymentMethods,
                &$ok,
            ): void {
                $sale = Sale::create([
                    'store_id' => $storeId,
                    'user_id' => $userId,
                    'reference' => $reference,
                    'status' => 'posted',
                    'payment_method' => $paymentMethods[array_rand($paymentMethods)],
                    'occurred_at' => $occurredAt,
                    'notes' => null,
                    'items_count' => 0,
                    'total_amount' => 0,
                ]);

                $itemsWanted = random_int(1, 5);
                $candidates = min(count($productIds), $itemsWanted + 5);
                $keys = array_rand($productIds, $candidates);
                $keys = is_array($keys) ? $keys : [$keys];

                $now = now();
                $itemRows = [];
                $total = 0.0;
                $used = [];

                foreach ($keys as $key) {
                    if (count($itemRows) >= $itemsWanted) {
                        break;
                    }

                    $productId = (int) $productIds[$key];
                    if (isset($used[$productId])) {
                        continue;
                    }
                    $used[$productId] = true;

                    $qty = random_int(1, 3);
                    $price = $priceByProductId[$productId] ?? null;
                    $unitPrice = $price !== null ? round($price, 2) : round(random_int(199, 39999) / 100, 2);

                    try {
                        $inventory->apply(
                            $storeId,
                            $productId,
                            'out',
                            $qty,
                            $userId,
                            $occurredAt,
                            'Venda',
                            'Venda (massa de dados).',
                            ['sale_id' => (int) $sale->getKey()],
                            null,
                            null,
                            null,
                            false,
                        );
                    } catch (\Throwable $e) {
                        continue;
                    }

                    $total += $qty * $unitPrice;

                    $itemRows[] = [
                        'sale_id' => (int) $sale->getKey(),
                        'product_id' => $productId,
                        'quantity' => $qty,
                        'unit_price' => $unitPrice,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                if ($itemRows === []) {
                    $sale->delete();

                    return;
                }

                SaleItem::insert($itemRows);

                $sale->update([
                    'items_count' => count($itemRows),
                    'total_amount' => round($total, 2),
                ]);

                $ok = true;
            });

            if ($ok) {
                $created++;
            }
        }
    }

    /**
     * @param  array<int,int>  $storeIds
     * @param  array<int,int>  $productIds
     */
    private function seedTransfers(
        int $count,
        int $daysBack,
        array $storeIds,
        array $productIds,
        ?int $userId,
        string $runId,
        \Faker\Generator $faker,
    ): void {
        if ($count < 1 || count($storeIds) < 2 || $productIds === []) {
            return;
        }

        /** @var InventoryService $inventory */
        $inventory = app(InventoryService::class);

        $created = 0;
        $attempt = 0;
        $maxAttempts = max($count * 5, 50);

        while ($created < $count && $attempt < $maxAttempts) {
            $attempt++;

            $fromStoreId = (int) $storeIds[array_rand($storeIds)];
            $toStoreId = (int) $storeIds[array_rand($storeIds)];
            if ($fromStoreId === $toStoreId) {
                continue;
            }

            $occurredAt = $faker->dateTimeBetween("-{$daysBack} days", 'now');
            $reference = 'MASSA-'.$runId.'-TRANSF-'.str_pad((string) $attempt, 8, '0', STR_PAD_LEFT);

            $ok = false;

            DB::transaction(function () use (
                $inventory,
                $fromStoreId,
                $toStoreId,
                $userId,
                $occurredAt,
                $reference,
                $productIds,
                &$ok,
            ): void {
                $transfer = Transfer::create([
                    'from_store_id' => $fromStoreId,
                    'to_store_id' => $toStoreId,
                    'user_id' => $userId,
                    'reference' => $reference,
                    'status' => 'posted',
                    'occurred_at' => $occurredAt,
                    'notes' => 'Transferencia (massa de dados).',
                    'items_count' => 0,
                ]);

                $itemsWanted = random_int(1, 4);
                $candidates = min(count($productIds), $itemsWanted + 6);
                $keys = array_rand($productIds, $candidates);
                $keys = is_array($keys) ? $keys : [$keys];

                $now = now();
                $itemRows = [];
                $used = [];

                foreach ($keys as $key) {
                    if (count($itemRows) >= $itemsWanted) {
                        break;
                    }

                    $productId = (int) $productIds[$key];
                    if (isset($used[$productId])) {
                        continue;
                    }
                    $used[$productId] = true;

                    $qty = random_int(1, 12);

                    try {
                        $inventory->apply(
                            $fromStoreId,
                            $productId,
                            'out',
                            $qty,
                            $userId,
                            $occurredAt,
                            'Transferencia',
                            'Transferencia (massa de dados).',
                            ['transfer_id' => (int) $transfer->getKey(), 'direction' => 'out'],
                            null,
                            null,
                            null,
                            false,
                        );

                        $inventory->apply(
                            $toStoreId,
                            $productId,
                            'in',
                            $qty,
                            $userId,
                            $occurredAt,
                            'Transferencia',
                            'Transferencia (massa de dados).',
                            ['transfer_id' => (int) $transfer->getKey(), 'direction' => 'in'],
                            null,
                            null,
                            null,
                            false,
                        );
                    } catch (\Throwable $e) {
                        continue;
                    }

                    $itemRows[] = [
                        'transfer_id' => (int) $transfer->getKey(),
                        'product_id' => $productId,
                        'quantity' => $qty,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                if ($itemRows === []) {
                    $transfer->delete();

                    return;
                }

                TransferItem::insert($itemRows);

                $transfer->update([
                    'items_count' => count($itemRows),
                ]);

                $ok = true;
            });

            if ($ok) {
                $created++;
            }
        }
    }

    private function log(string $message): void
    {
        $this->command?->line($message);
    }

    private function intEnv(string $key, int $default): int
    {
        $value = env($key);
        if ($value === null || $value === '') {
            return $default;
        }

        $int = (int) $value;

        return $int > 0 ? $int : $default;
    }

    private function boolEnv(string $key, bool $default): bool
    {
        $value = env($key);
        if ($value === null || $value === '') {
            return $default;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? $default;
    }

    private function formatCnpj(string $digits14): string
    {
        $digits14 = preg_replace('/\\D+/', '', $digits14) ?: '';
        $digits14 = str_pad(substr($digits14, 0, 14), 14, '0', STR_PAD_LEFT);

        return substr($digits14, 0, 2).'.'
            .substr($digits14, 2, 3).'.'
            .substr($digits14, 5, 3).'/'
            .substr($digits14, 8, 4).'-'
            .substr($digits14, 12, 2);
    }

    private function ean13FromSeed(int $seed, string $prefix3 = '789'): string
    {
        $prefix3 = preg_replace('/\\D+/', '', $prefix3) ?: '789';
        $prefix3 = str_pad(substr($prefix3, 0, 3), 3, '0', STR_PAD_LEFT);

        $body = $prefix3.str_pad((string) ($seed % 1000000000), 9, '0', STR_PAD_LEFT);
        $sum = 0;

        for ($i = 0; $i < 12; $i++) {
            $digit = (int) $body[$i];
            $sum += ($i % 2 === 0) ? $digit : ($digit * 3);
        }

        $check = (10 - ($sum % 10)) % 10;

        return $body.(string) $check;
    }
}
