@extends('layouts.app')
@section('title', 'Cart')
@section('content')
<h1>Shopping Cart</h1>
@if(isset($items) && count($items))
    @foreach($items as $item)
        <div>{{ $item['product']->name }} x{{ $item['quantity'] }} - ${{ number_format($item['final_line_total'], 2) }}</div>
    @endforeach
    <p>Subtotal: ${{ number_format($subtotal, 2) }}</p>
    <p>Discount: ${{ number_format($discount_total, 2) }}</p>
    <p>Total: ${{ number_format($total, 2) }}</p>
@else
    <p>Your cart is empty.</p>
@endif
@endsection