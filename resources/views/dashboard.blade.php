@extends('layouts.app')

@section('content')
<div class="flex min-h-screen">
    @include('components.sidebar')
    <div class="flex-1 flex flex-col">
        @include('components.header')
        @include('components.document-table')
    </div>
</div>
@endsection
