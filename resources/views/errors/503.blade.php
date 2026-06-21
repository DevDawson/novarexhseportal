@extends('errors.layout')

@section('code', '503')
@section('code-color', '#94a3b8')
@section('title', 'System Maintenance')
@section('message')
    PortalHSE is temporarily offline for scheduled maintenance.<br>
    We will be back shortly. Thank you for your patience.
@endsection

@section('actions')
    <a href="javascript:location.reload()" class="btn btn-primary">🔄 Check Again</a>
@endsection
