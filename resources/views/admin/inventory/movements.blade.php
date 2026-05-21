@extends('layouts.app')
@section('title', 'Inventory Movements')
@section('content')
<h1>Inventory Movements</h1>
@foreach($movements as $m)
    <div>{{ $m->variant->product->name }} - {{ $m->type }} - {{ $m->quantity }} - {{ $m->created_at }}</div>
@endforeach
{{ $movements->links() }}
@endsection
