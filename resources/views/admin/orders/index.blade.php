@extends('layouts.app')
@section('title', 'Manage Orders')
@section('content')
<h1>Orders</h1>
@foreach($orders as $order)
    <div>Order #{{ $order->id }} - {{ $order->user->name }} - ${{ $order->total }} - {{ $order->status }}</div>
@endforeach
{{ $orders->links() }}
@endsection
