@extends('layouts.app')

@section('title', 'Grupos · Controle CRIS')

@section('content')
    <div class="mb-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <p class="text-sm font-semibold uppercase tracking-wider text-purple-700">Overview</p>
            <h1 class="mt-1 text-3xl font-bold">Grupos</h1>
            <p class="mt-2 text-sm text-slate-500">Visualize e gerencie os grupos cadastrados.</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <form method="POST" action="{{ route('system.update') }}" data-system-update-form>
                @csrf
                <button
                    type="submit"
                    data-system-update-button
                    class="inline-flex items-center justify-center rounded-lg border border-purple-300 bg-white px-4 py-2.5 text-sm font-semibold text-purple-800 hover:bg-purple-50 disabled:cursor-wait disabled:opacity-60"
                >
                    Atualizar sistema
                </button>
            </form>
            <a href="{{ route('groups.create') }}" class="inline-flex items-center justify-center rounded-lg bg-purple-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-purple-800">
                + Criar grupo
            </a>
        </div>
    </div>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-purple-50 text-left text-xs uppercase tracking-wider text-purple-900">
                    <tr>
                        <th class="px-5 py-4 font-semibold">Grupo</th>
                        <th class="px-5 py-4 font-semibold">Início</th>
                        <th class="px-5 py-4 font-semibold">Término</th>
                        <th class="px-5 py-4 font-semibold">Usuários</th>
                        <th class="px-5 py-4 text-right font-semibold">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($groups as $group)
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-4 font-semibold text-slate-900">{{ $group->name }}</td>
                            <td class="whitespace-nowrap px-5 py-4 text-slate-600">{{ $group->start_date->format('d/m/Y') }}</td>
                            <td class="whitespace-nowrap px-5 py-4 text-slate-600">{{ $group->end_date->format('d/m/Y') }}</td>
                            <td class="px-5 py-4 text-slate-600">{{ $group->user_groups_count }}</td>
                            <td class="px-5 py-4">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('groups.edit', $group) }}" class="rounded-lg border border-slate-200 px-3 py-2 font-medium text-slate-700 hover:bg-slate-100">Editar</a>
                                    <a href="{{ route('groups.show', $group) }}" class="rounded-lg border border-purple-200 px-3 py-2 font-medium text-purple-700 hover:bg-purple-50">Ver</a>
                                    <a href="{{ route('groups.scope', $group) }}" class="rounded-lg bg-purple-700 px-3 py-2 font-medium text-white hover:bg-purple-800">Entrar</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-14 text-center text-slate-500">
                                Nenhum grupo cadastrado.
                                <a href="{{ route('groups.create') }}" class="font-semibold text-purple-700">Criar o primeiro grupo</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
