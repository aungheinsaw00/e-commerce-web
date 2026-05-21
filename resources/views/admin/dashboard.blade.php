@extends('layouts.app')
@section('title', 'Admin Dashboard')
@section('content')
<h1>Admin Dashboard</h1>
<div>Total Orders: {{ $stats['total_orders'] }}</div>
<div>Pending: {{ $stats['pending_orders'] }}</div>
<div>Revenue: ${{ number_format($stats['revenue'], 2) }}</div>
@endsection
