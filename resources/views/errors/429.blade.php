@extends('errors.layout')

@section('code', '429')
@section('code-color', '#eab308')
@section('title', 'Too Many Requests')
@section('message')
    You have made too many requests in a short period.<br>
    Please wait a moment and try again.
@endsection

@section('actions')
    <a href="{{ url('/admin') }}" class="btn btn-primary">⬅ Back to Dashboard</a>
@endsection
