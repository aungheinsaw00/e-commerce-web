@extends('layouts.app')
@section('title', 'Manage Products')
@section('content')
<h1>Products</h1>
@foreach($products as $product)
    <div>{{ $product->name }} - ${{ $product->base_price }}</div>
@endforeach
{{ $products->links() }}
@endsection