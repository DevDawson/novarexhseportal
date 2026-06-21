@extends('errors.layout')

@section('code', '500')
@section('code-color', '#ef4444')
@section('title', 'Server Error')
@section('message')
    Something went wrong on our end. The error has been logged.<br>
    Please try again — if the problem persists, contact the system administrator.
@endsection

@section('actions')
    <a href="{{ url('/admin') }}" class="btn btn-primary">⬅ Back to Dashboard</a>
    <a href="javascript:location.reload()" class="btn btn-secondary">🔄 Try Again</a>
@endsection
