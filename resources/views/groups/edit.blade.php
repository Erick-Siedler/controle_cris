@extends('layouts.app')

@section('title', 'Editar grupo · Controle CRIS')

@section('content')
    <div class="mb-6">
        <a href="{{ route('groups.index') }}" class="text-sm font-semibold text-purple-700">← Voltar aos grupos</a>
        <h1 class="mt-3 text-3xl font-bold">Editar {{ $group->name }}</h1>
    </div>

    @include('groups._form', [
        'action' => route('groups.update', $group),
        'method' => 'PUT',
        'submitLabel' => 'Salvar alterações',
    ])
@endsection
