@extends('errors.layout')

@section('code', '403')
@section('code-color', '#f97316')
@section('title', 'Access Denied')
@section('message')
    You do not have permission to access this page.<br>
    If you believe this is a mistake, please contact your system administrator.
@endsection

@section('actions')
    <a href="{{ url('/admin') }}" class="btn btn-primary">⬅ Back to Dashboard</a>
    <a href="{{ url('/admin/login') }}" class="btn btn-secondary">🔐 Login as Different User</a>
@endsection
