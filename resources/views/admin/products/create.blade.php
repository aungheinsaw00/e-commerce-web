@extends('layouts.app')
@section('title', 'Create Product')
@section('content')
<h1>Create Product</h1>
<form method="POST" action="{{ route('admin.products.store') }}">
    @csrf
    <input name="name" placeholder="Name">
    <textarea name="description"></textarea>
    <input name="base_price" type="number" step="0.01">
    <select name="category_id">
        @foreach($categories as $cat)
            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
        @endforeach
    </select>
    <input name="sku" placeholder="SKU">
    <input name="initial_stock" type="number">
    <button type="submit">Create</button>
</form>
@endsection
