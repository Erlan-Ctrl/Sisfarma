<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('catalogo:importar-eans {arquivo : Caminho do arquivo .txt/.csv com EANs (1 por linha)} {--dry-run : Nao grava no banco} {--limit=0 : Limite de itens para processar}', function () {
    $path = (string) $this->argument('arquivo');
    $limit = (int) $this->option('limit');
    $dryRun = (bool) $this->option('dry-run');

    $fullPath = $path;
    if (! File::exists($fullPath)) {
        $fullPath = base_path($path);
    }

    if (! File::exists($fullPath)) {
        $this->error("Arquivo nao encontrado: {$path}");
        return 1;
    }

    $raw = File::get($fullPath);
    $lines = preg_split('/\\R+/', (string) $raw) ?: [];

    $codes = [];
    foreach ($lines as $line) {
        $digits = preg_replace('/\\D+/', '', (string) $line) ?? '';
        if ($digits === '' || strlen($digits) < 8) {
            continue;
        }
        $codes[] = $digits;
    }

    $codes = array_values(array_unique($codes));
    if ($limit > 0) {
        $codes = array_slice($codes, 0, $limit);
    }

    $this->info('EANs lidos: '.count($codes));

    /** @var \App\Services\Catalog\GtinSearchClient $client */
    $client = app(\App\Services\Catalog\GtinSearchClient::class);

    $created = 0;
    $skipped = 0;
    $notFound = 0;

    foreach ($codes as $idx => $ean) {
        $n = $idx + 1;

        $exists = \App\Models\Product::query()->where('ean', $ean)->exists();
        if ($exists) {
            $skipped++;
            $this->line("[{$n}/".count($codes)."] OK (ja existe): {$ean}");
            continue;
        }

        $data = $client->lookup($ean);
        if (! $data) {
            $notFound++;
            $this->warn("[{$n}/".count($codes)."] NAO ENCONTRADO: {$ean}");
            continue;
        }

        $name = (string) ($data['name'] ?? '');
        $name = trim($name);
        if ($name === '') {
            $name = 'Produto '.$ean;
        }

        if ($dryRun) {
            $created++;
            $this->info("[{$n}/".count($codes)."] DRY-RUN: {$ean} -> {$name}");
            continue;
        }

        $product = \App\Models\Product::create([
            'name' => $name,
            'ean' => $ean,
            'sku' => null,
            'supplier_id' => null,
            'short_description' => isset($data['size'], $data['brand'])
                ? trim((string) $data['brand']).' - '.trim((string) $data['size'])
                : ($data['size'] ?? $data['brand'] ?? null),
            'description' => null,
            'image_url' => null,
            'price' => null,
            'requires_prescription' => false,
            'is_active' => true,
            'is_featured' => false,
        ]);

        $created++;
        $this->info("[{$n}/".count($codes)."] CRIADO: {$product->ean} -> {$product->name}");
    }

    $this->newLine();
    $this->info("Criados: {$created}");
    $this->info("Pulados: {$skipped}");
    $this->info("Nao encontrados: {$notFound}");

    return 0;
})->purpose('Importa produtos por EAN/GTIN usando GTINSearch (prefill do mix)');

Artisan::command('estoque:recalcular-custos {--store=0 : ID da loja (0=todas)} {--dry-run : Nao grava no banco}', function () {
    $storeId = (int) $this->option('store');
    $dryRun = (bool) $this->option('dry-run');

    $q = \Illuminate\Support\Facades\DB::table('purchase_items as pi')
        ->join('purchases as p', 'p.id', '=', 'pi.purchase_id')
        ->whereNotNull('pi.unit_cost')
        ->select([
            'p.store_id as store_id',
            'pi.product_id as product_id',
            'pi.unit_cost as unit_cost',
            'p.occurred_at as occurred_at',
            'pi.id as purchase_item_id',
        ])
        ->when($storeId > 0, fn ($qq) => $qq->where('p.store_id', $storeId))
        ->orderBy('p.store_id')
        ->orderBy('pi.product_id')
        ->orderByDesc('p.occurred_at')
        ->orderByDesc('pi.id');

    $rows = $q->get();

    $seen = [];
    $updates = 0;

    foreach ($rows as $row) {
        $key = (int) $row->store_id.'-'.(int) $row->product_id;
        if (isset($seen[$key])) {
            continue;
        }
        $seen[$key] = true;

        $updates++;
        $line = "Loja {$row->store_id} / Produto {$row->product_id} -> custo {$row->unit_cost} ({$row->occurred_at})";

        if ($dryRun) {
            $this->line("DRY-RUN: {$line}");
            continue;
        }

        \App\Models\Inventory::updateOrCreate(
            ['store_id' => (int) $row->store_id, 'product_id' => (int) $row->product_id],
            ['last_unit_cost' => (float) $row->unit_cost, 'last_purchase_at' => $row->occurred_at]
        );

        $this->line("OK: {$line}");
    }

    $this->newLine();
    $this->info("Atualizacoes: {$updates}");

    return 0;
})->purpose('Recalcula o ultimo custo (por loja/produto) a partir das compras');
