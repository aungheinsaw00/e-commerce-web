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
        $request->validate([
            'quantity' => ['required', 'integer'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $this->inventoryService->adjustStock(
            $variant->id,
            $request->integer('quantity'),
            $request->input('note', '')
        );

        return redirect()->route('admin.inventory.index')
            ->with('success', 'Stock adjusted.');
    }

    public function movements(Request $request)
    {
        $movements = InventoryMovement::with('variant.product')
            ->orderByDesc('created_at')
            ->paginate(30);

        return view('admin.inventory.movements', compact('movements'));
    }
}
