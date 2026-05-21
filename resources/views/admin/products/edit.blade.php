@extends('layouts.app')
@section('title', 'Edit Product')
@section('content')
<h1>Edit: {{ $product->name }}</h1>
<form method="POST" action="{{ route('admin.products.update', $product) }}">
    @csrf @method('PUT')
    <input name="name" value="{{ $product->name }}">
    <textarea name="description">{{ $product->description }}</textarea>
    <input name="base_price" value="{{ $product->base_price }}">
    <button type="submit">Update</button>
</form>
@endsection
