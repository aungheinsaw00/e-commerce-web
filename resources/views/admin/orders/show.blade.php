@extends('layouts.app')
@section('title', 'Order Details')
@section('content')
<h1>Order #{{ $order->id }}</h1>
<p>Customer: {{ $order->user->name }}</p>
<p>Status: {{ $order->status }}</p>
<p>Total: ${{ $order->total }}</p>
@foreach($order->orderItems as $item)
    <div>{{ $item->product_name_snapshot }} x{{ $item->quantity }} - ${{ $item->final_price }}</div>
@endforeach
@endsection
