<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\ProductVariant;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    public function checkAvailability(int $variantId, int $quantity): bool
    {
        $inventory = Inventory::where('variant_id', $variantId)->first();
        if (!$inventory) return false;

        return $inventory->available_quantity >= $quantity;
    }

    public function reserveStock(int $variantId, int $quantity, int $orderId): InventoryMovement
    {
        return DB::transaction(function () use ($variantId, $quantity, $orderId) {
            $inventory = Inventory::where('variant_id', $variantId)->lockForUpdate()->firstOrFail();

            if ($inventory->available_quantity < $quantity) {
                throw new \RuntimeException("Insufficient stock for variant {$variantId}. Available: {$inventory->available_quantity}, requested: {$quantity}");
            }

            $inventory->increment('reserved_quantity', $quantity);

            return InventoryMovement::create([
                'variant_id' => $variantId,
                'type' => InventoryMovement::TYPE_RESERVE,
                'quantity' => $quantity,
                'reference_type' => 'order',
                'reference_id' => $orderId,
            ]);
        });
    }

    public function releaseStock(int $variantId, int $quantity, int $orderId): InventoryMovement
    {
        return DB::transaction(function () use ($variantId, $quantity, $orderId) {
            $inventory = Inventory::where('variant_id', $variantId)->lockForUpdate()->firstOrFail();

            $release = min($quantity, $inventory->reserved_quantity);
            $inventory->decrement('reserved_quantity', $release);

            return InventoryMovement::create([
                'variant_id' => $variantId,
                'type' => InventoryMovement::TYPE_RELEASE,
                'quantity' => $release,
                'reference_type' => 'order',
                'reference_id' => $orderId,
            ]);
        });
    }

    public function deductStock(int $variantId, int $quantity, int $orderId): InventoryMovement
    {
        return DB::transaction(function () use ($variantId, $quantity, $orderId) {
            $inventory = Inventory::where('variant_id', $variantId)->lockForUpdate()->firstOrFail();

            $deduct = min($quantity, $inventory->reserved_quantity);
            $inventory->decrement('reserved_quantity', $deduct);
            $inventory->decrement('stock_quantity', $quantity);

            return InventoryMovement::create([
                'variant_id' => $variantId,
                'type' => InventoryMovement::TYPE_OUT,
                'quantity' => $quantity,
                'reference_type' => 'order',
                'reference_id' => $orderId,
            ]);
        });
    }

    public function adjustStock(int $variantId, int $quantity, string $note = ''): InventoryMovement
    {
        return DB::transaction(function () use ($variantId, $quantity, $note) {
            $inventory = Inventory::where('variant_id', $variantId)->lockForUpdate()->firstOrFail();

            $type = $quantity >= 0 ? InventoryMovement::TYPE_IN : InventoryMovement::TYPE_OUT;
            $inventory->increment('stock_quantity', $quantity);

            return InventoryMovement::create([
                'variant_id' => $variantId,
                'type' => $type,
                'quantity' => abs($quantity),
                'reference_type' => 'admin_adjustment',
                'note' => $note,
            ]);
        });
    }

    public function getLowStockVariants(): Collection
    {
        return Inventory::with('variant.product')
            ->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
            ->get();
    }
}
