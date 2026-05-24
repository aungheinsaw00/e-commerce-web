<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\ProductVariant;
use App\Services\InventoryService;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function __construct(private readonly InventoryService $inventoryService)
    {}

    public function index()
    {
        $inventories = Inventory::with('variant.product')
            ->paginate(20);

        return view('admin.inventory.index', compact('inventories'));
    }

    public function adjust(Request $request, ProductVariant $variant)
    {
        $request->merge(['variant_id' => $variant->id]);

        $validated = $request->validate([
            'variant_id' => 'required|exists:product_variants,id',
            'quantity' => 'required|integer|min:1',
            'note' => 'nullable|string'
        ]);

        $inventory = Inventory::where('variant_id', $validated['variant_id'])->firstOrFail();
        $inventory->increment('stock_quantity', $validated['quantity']);

        InventoryMovement::create([
            'variant_id' => $validated['variant_id'],
            'type' => InventoryMovement::TYPE_IN,
            'quantity' => $validated['quantity'],
            'reference_type' => 'admin_adjust',
            'reference_id' => auth()->id(),
            'note' => $validated['note'],
        ]);

        return redirect()->back()->with('success', 'Stock adjusted.');
    }

    public function movements(Request $request)
    {
        $movements = InventoryMovement::with('variant.product')
            ->orderByDesc('created_at')
            ->paginate(30);

        return view('admin.inventory.movements', compact('movements'));
    }
}
