@extends('errors.layout')

@section('code', '419')
@section('code-color', '#eab308')
@section('title', 'Session Expired')
@section('message')
    Your session has expired due to inactivity.<br>
    Please refresh the page or log in again to continue.
@endsection

@section('actions')
    <a href="{{ url()->previous() ?: url('/admin') }}" class="btn btn-primary">🔄 Refresh Page</a>
    <a href="{{ url('/admin/login') }}" class="btn btn-secondary">🔐 Log In Again</a>
@endsection
