<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\InventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryServiceTest extends TestCase
{
    use RefreshDatabase;

    private function createVariantWithInventory(int $stock = 50): array
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);
        $variant = ProductVariant::factory()->create(['product_id' => $product->id]);
        $inventory = Inventory::factory()->create([
            'variant_id' => $variant->id,
            'stock_quantity' => $stock,
            'reserved_quantity' => 0,
        ]);
        return compact('variant', 'inventory');
    }

    public function test_check_availability_returns_true_when_stock_sufficient(): void
    {
        $data = $this->createVariantWithInventory(50);
        $service = app(InventoryService::class);

        $this->assertTrue($service->checkAvailability($data['variant']->id, 30));
    }

    public function test_check_availability_returns_false_when_stock_insufficient(): void
    {
        $data = $this->createVariantWithInventory(5);
        $service = app(InventoryService::class);

        $this->assertFalse($service->checkAvailability($data['variant']->id, 10));
    }

    public function test_reserve_stock_increments_reserved_quantity(): void
    {
        $data = $this->createVariantWithInventory(50);
        $service = app(InventoryService::class);

        $movement = $service->reserveStock($data['variant']->id, 10, 1);

        $data['inventory']->refresh();
        $this->assertEquals(10, $data['inventory']->reserved_quantity);
        $this->assertEquals(InventoryMovement::TYPE_RESERVE, $movement->type);
        $this->assertEquals(10, $movement->quantity);
    }

    public function test_reserve_stock_fails_when_insufficient(): void
    {
        $data = $this->createVariantWithInventory(5);
        $service = app(InventoryService::class);

        $this->expectException(\RuntimeException::class);
        $service->reserveStock($data['variant']->id, 10, 1);
    }

    public function test_release_stock_decrements_reserved_quantity(): void
    {
        $data = $this->createVariantWithInventory(50);
        $service = app(InventoryService::class);

        $service->reserveStock($data['variant']->id, 10, 1);
        $service->releaseStock($data['variant']->id, 10, 1);

        $data['inventory']->refresh();
        $this->assertEquals(0, $data['inventory']->reserved_quantity);
    }

    public function test_deduct_stock_reduces_stock_and_reserved(): void
    {
        $data = $this->createVariantWithInventory(50);
        $service = app(InventoryService::class);

        $service->reserveStock($data['variant']->id, 10, 1);
        $service->deductStock($data['variant']->id, 10, 1);

        $data['inventory']->refresh();
        $this->assertEquals(40, $data['inventory']->stock_quantity);
        $this->assertEquals(0, $data['inventory']->reserved_quantity);
    }

    public function test_adjust_stock_adds_inventory(): void
    {
        $data = $this->createVariantWithInventory(50);
        $service = app(InventoryService::class);

        $movement = $service->adjustStock($data['variant']->id, 20, 'Restock');

        $data['inventory']->refresh();
        $this->assertEquals(70, $data['inventory']->stock_quantity);
        $this->assertEquals(InventoryMovement::TYPE_IN, $movement->type);
    }

    public function test_low_stock_detection(): void
    {
        $data = $this->createVariantWithInventory(3);
        $data['inventory']->update(['low_stock_threshold' => 5]);

        $service = app(InventoryService::class);
        $lowStock = $service->getLowStockVariants();

        $this->assertCount(1, $lowStock);
    }

    public function test_movement_audit_trail(): void
    {
        $data = $this->createVariantWithInventory(50);
        $service = app(InventoryService::class);

        $service->reserveStock($data['variant']->id, 5, 1);
        $service->deductStock($data['variant']->id, 5, 1);

        $movements = InventoryMovement::where('variant_id', $data['variant']->id)->get();
        $this->assertCount(2, $movements);
        $this->assertEquals(InventoryMovement::TYPE_RESERVE, $movements[0]->type);
        $this->assertEquals(InventoryMovement::TYPE_OUT, $movements[1]->type);
    }
}
