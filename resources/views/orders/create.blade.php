@extends('layouts.app')
@section('title', 'Checkout')
@section('content')
<h1>Checkout</h1>
@if(isset($summary))
    @foreach($summary['items'] as $item)
        <div>{{ $item['product']->name }} x{{ $item['quantity'] }}</div>
    @endforeach
    <p>Total: ${{ number_format($summary['total'], 2) }}</p>
    <form method="POST" action="{{ route('orders.store') }}">
        @csrf
        <textarea name="notes"></textarea>
        <button type="submit">Place Order</button>
    </form>
@endif
@endsection
