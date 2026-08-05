@extends('layouts.app')

@section('title', 'Criar grupo · Controle CRIS')

@section('content')
    <div class="mb-6">
        <a href="{{ route('groups.index') }}" class="text-sm font-semibold text-purple-700">← Voltar aos grupos</a>
        <h1 class="mt-3 text-3xl font-bold">Criar grupo</h1>
    </div>

    @include('groups._form', [
        'action' => route('groups.store'),
        'method' => 'POST',
        'submitLabel' => 'Criar grupo',
        'selectedUserIds' => [],
    ])
@endsection
