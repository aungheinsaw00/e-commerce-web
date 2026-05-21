@extends('layouts.app')
@section('title', 'Categories')
@section('content')
<div class="container">
    <h1>Categories</h1>
    <ul>
        @foreach ($categories as $category)
            <li>
                <a href="{{ route('products.index', ['category' => $category->id]) }}">
                    {{ $category->name }}
                </a>
                <span>({{ $category->products_count }} products)</span>
            </li>
        @endforeach
    </ul>
</div>
@endsection