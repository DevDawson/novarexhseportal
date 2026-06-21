@extends('errors.layout')

@section('code', '404')
@section('code-color', '#6366f1')
@section('title', 'Page Not Found')
@section('message')
    The page you are looking for does not exist or has been moved.<br>
    Check the URL or return to the dashboard.
@endsection

@section('actions')
    <a href="{{ url('/admin') }}" class="btn btn-primary">⬅ Back to Dashboard</a>
    <a href="javascript:history.back()" class="btn btn-secondary">↩ Go Back</a>
@endsection
