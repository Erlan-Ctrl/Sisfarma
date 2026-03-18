<?php

namespace App\Services\Inventory;

use App\Models\Inventory;
use App\Models\InventoryMovement;
use DateTimeInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    /**
     * Aplica uma movimentacao no estoque e registra no historico.
     *
     * Importante: quando $useTransaction=false, esse metodo deve ser chamado dentro
     * de uma transacao para que o lockForUpdate funcione corretamente.
     *
     * @param  array<string, mixed>|null  $meta
     */
    public function apply(
        int $storeId,
        int $productId,
        string $type,
        int $quantity,
        ?int $userId = null,
        ?DateTimeInterface $occurredAt = null,
        ?string $reason = null,
        ?string $note = null,
        ?array $meta = null,
        ?int $minQuantity = null,
        ?float $lastUnitCost = null,
        ?DateTimeInterface $lastPurchaseAt = null,
        bool $useTransaction = true,
    ): InventoryMovement {
        $occurredAt = $occurredAt ?: now();

        $runner = function () use (
            $storeId,
            $productId,
            $type,
            $quantity,
            $userId,
            $occurredAt,
            $reason,
            $note,
            $meta,
            $minQuantity,
            $lastUnitCost,
            $lastPurchaseAt,
        ): InventoryMovement {
            if (! in_array($type, ['in', 'out', 'adjust'], true)) {
                throw new \InvalidArgumentException('Tipo de movimentação inválido.');
            }

            if (in_array($type, ['in', 'out'], true) && $quantity < 1) {
                throw new \InvalidArgumentException('Informe uma quantidade maior que zero.');
            }

            if ($type === 'adjust' && $quantity < 0) {
                throw new \InvalidArgumentException('Ajuste não pode ser negativo.');
            }

            $inventory = Inventory::query()
                ->where('store_id', $storeId)
                ->where('product_id', $productId)
                ->lockForUpdate()
                ->first();

            if (! $inventory) {
                $inventory = Inventory::create([
                    'store_id' => $storeId,
                    'product_id' => $productId,
                    'quantity' => 0,
                    'min_quantity' => null,
                    'last_unit_cost' => null,
                    'last_purchase_at' => null,
                ]);
            }

            $before = (int) $inventory->quantity;
            $after = $before;

            if ($type === 'in') {
                $after = $before + $quantity;
            } elseif ($type === 'out') {
                $after = $before - $quantity;
            } elseif ($type === 'adjust') {
                $after = $quantity;
            }

            if ($after < 0) {
                throw new \RuntimeException('Saída maior que o estoque atual. Use "Ajuste" se necessário.');
            }

            $delta = $after - $before;

            $inventory->quantity = $after;

            if ($minQuantity !== null) {
                $inventory->min_quantity = $minQuantity;
            }

            // Atualiza o custo mais recente (por loja) quando houver uma entrada com custo informado.
            if ($lastUnitCost !== null) {
                $inventory->last_unit_cost = $lastUnitCost;
                $inventory->last_purchase_at = Carbon::parse($lastPurchaseAt ?: $occurredAt);
            }

            $inventory->save();

            return InventoryMovement::create([
                'store_id' => $storeId,
                'product_id' => $productId,
                'user_id' => $userId,
                'type' => $type,
                'delta' => $delta,
                'quantity_before' => $before,
                'quantity_after' => $after,
                'reason' => $reason,
                'note' => $note,
                'occurred_at' => Carbon::parse($occurredAt),
                'meta' => $meta,
            ]);
        };

        if (! $useTransaction) {
            return $runner();
        }

        /** @var InventoryMovement */
        return DB::transaction($runner);
    }
}
