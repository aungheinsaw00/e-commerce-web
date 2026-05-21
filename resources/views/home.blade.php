@extends('layouts.app')
@section('title', 'Home')
@section('content')
<h1>Best Car Parts for Your Vehicle</h1>
@if(isset($discountedProducts))
    @foreach($discountedProducts as $product)
        <div>{{ $product->name }} - ${{ $product->base_price }}</div>
    @endforeach
@endif
@endsection