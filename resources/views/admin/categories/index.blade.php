@extends('layouts.app')
@section('title', 'Manage Categories')
@section('content')
<h1>Categories</h1>
@foreach($categories as $category)
    <div>{{ $category->name }} ({{ $category->products_count }} products)</div>
@endforeach
@endsection