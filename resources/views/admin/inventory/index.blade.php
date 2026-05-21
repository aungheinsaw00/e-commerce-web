@extends('layouts.app')
@section('title', 'Inventory')
@section('content')
<h1>Inventory</h1>
@foreach($inventories as $inv)
    <div>{{ $inv->variant->product->name }} ({{ $inv->variant->sku }}) - Stock: {{ $inv->stock_quantity }} - Reserved: {{ $inv->reserved_quantity }}</div>
@endforeach
{{ $inventories->links() }}
@endsection
