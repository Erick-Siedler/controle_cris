@extends('layouts.app')

@section('title', $group->name . ' · Controle CRIS')

@section('content')
    @php
        $pendingUsers = $group->user_groups->filter(
            fn ($userGroup) => !$additionals->has($userGroup->users_id)
        );
        $configuredUsers = $group->user_groups->filter(
            fn ($userGroup) => $additionals->has($userGroup->users_id)
        );
    @endphp

    <div class="mb-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <a href="{{ route('groups.index') }}" class="text-sm font-semibold text-purple-700">
                ← Voltar aos grupos
            </a>
            <h1 class="mt-3 text-3xl font-bold">{{ $group->name }}</h1>
            <p class="mt-2 text-slate-500">
                {{ $group->start_date->format('d/m/Y') }} até
                {{ $group->end_date->format('d/m/Y') }}
            </p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('groups.edit', $group) }}" class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold hover:bg-slate-100">
                Editar grupo
            </a>
            <a href="{{ route('groups.scope', $group) }}" class="rounded-lg bg-purple-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-purple-800">
                Entrar no grupo
            </a>
        </div>
    </div>

    @if ($errors->any())
        <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ $errors->first() }}
        </div>
    @endif

    <section>
        <div class="mb-4">
            <h2 class="text-xl font-semibold">Adicionais dos usuários</h2>
            <p class="mt-1 text-sm text-slate-500">
                Cadastre peso, meta e semana bônus em conjunto. Depois, edite individualmente quando necessário.
            </p>
        </div>

        @if ($pendingUsers->isNotEmpty())
            <form method="POST" action="{{ route('users.storeAdditionalsBatch') }}">
                @csrf
                <input type="hidden" name="groups_id" value="{{ $group->id }}">

                <div class="mb-3 flex items-center justify-between gap-4">
                    <div>
                        <h3 class="font-semibold text-slate-900">Preenchimento inicial</h3>
                        <p class="text-sm text-slate-500">Preencha todos os usuários pendentes e confirme uma única vez.</p>
                    </div>
                    <span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700">
                        {{ $pendingUsers->count() }} pendente{{ $pendingUsers->count() > 1 ? 's' : '' }}
                    </span>
                </div>

                <div class="grid gap-5 lg:grid-cols-2">
                    @foreach ($pendingUsers as $userGroup)
                        <article class="rounded-xl border border-amber-200 bg-white p-6 shadow-sm">
                            <input
                                type="hidden"
                                name="additionals[{{ $userGroup->users_id }}][users_id]"
                                value="{{ $userGroup->users_id }}"
                            >

                            <div class="flex items-center justify-between gap-4">
                                <h4 class="font-semibold text-slate-900">{{ $userGroup->user->name }}</h4>
                                <span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700">Pendente</span>
                            </div>

                            <div class="mt-5 grid gap-4 sm:grid-cols-2">
                                <label>
                                    <span class="mb-2 block text-sm font-medium text-slate-700">Peso atual (kg)</span>
                                    <input
                                        type="number"
                                        name="additionals[{{ $userGroup->users_id }}][peso_inicial]"
                                        value="{{ old("additionals.{$userGroup->users_id}.peso_inicial") }}"
                                        min="1"
                                        max="500"
                                        step="0.01"
                                        required
                                        class="w-full rounded-lg border border-slate-300 px-3 py-2.5 focus:border-purple-500 focus:ring-2 focus:ring-purple-100"
                                    >
                                </label>
                                <label>
                                    <span class="mb-2 block text-sm font-medium text-slate-700">Meta de peso (kg)</span>
                                    <input
                                        type="number"
                                        name="additionals[{{ $userGroup->users_id }}][meta_peso]"
                                        value="{{ old("additionals.{$userGroup->users_id}.meta_peso") }}"
                                        min="1"
                                        max="500"
                                        step="0.01"
                                        required
                                        class="w-full rounded-lg border border-slate-300 px-3 py-2.5 focus:border-purple-500 focus:ring-2 focus:ring-purple-100"
                                    >
                                </label>
                            </div>

                            <label class="mt-4 flex cursor-pointer items-center gap-3 rounded-lg border border-slate-200 p-3 hover:bg-purple-50">
                                <input type="hidden" name="additionals[{{ $userGroup->users_id }}][semana_bonus]" value="0">
                                <input
                                    type="checkbox"
                                    name="additionals[{{ $userGroup->users_id }}][semana_bonus]"
                                    value="1"
                                    @checked(old("additionals.{$userGroup->users_id}.semana_bonus"))
                                    class="h-4 w-4 accent-purple-700"
                                >
                                <span class="text-sm font-medium text-slate-700">Participará da semana bônus</span>
                            </label>
                        </article>
                    @endforeach
                </div>

                <div class="mt-5 flex justify-end">
                    <button class="rounded-lg bg-purple-700 px-5 py-2.5 text-sm font-semibold text-white hover:bg-purple-800">
                        Salvar adicionais
                    </button>
                </div>
            </form>
        @endif

        @if ($configuredUsers->isNotEmpty())
            <div class="{{ $pendingUsers->isNotEmpty() ? 'mt-10' : '' }} mb-3">
                <h3 class="font-semibold text-slate-900">Edição individual</h3>
                <p class="text-sm text-slate-500">Altere somente o usuário necessário.</p>
            </div>

            <div class="grid gap-5 lg:grid-cols-2">
                @foreach ($configuredUsers as $userGroup)
                    @php $additional = $additionals->get($userGroup->users_id); @endphp

                    <article class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="flex items-center justify-between gap-4">
                            <h4 class="font-semibold text-slate-900">{{ $userGroup->user->name }}</h4>
                            <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">Configurado</span>
                        </div>

                        <form method="POST" action="{{ route('users.updateAdditionals') }}" class="mt-5 space-y-4">
                            @csrf
                            <input type="hidden" name="users_id" value="{{ $userGroup->users_id }}">
                            <input type="hidden" name="groups_id" value="{{ $group->id }}">
                            <input type="hidden" name="additionals_id" value="{{ $additional->id }}">

                            <div class="grid gap-4 sm:grid-cols-2">
                                <label>
                                    <span class="mb-2 block text-sm font-medium text-slate-700">Peso atual (kg)</span>
                                    <input type="number" name="peso_inicial" value="{{ $additional->peso_inicial }}" min="1" max="500" step="0.01" required class="w-full rounded-lg border border-slate-300 px-3 py-2.5 focus:border-purple-500 focus:ring-2 focus:ring-purple-100">
                                </label>
                                <label>
                                    <span class="mb-2 block text-sm font-medium text-slate-700">Meta de peso (kg)</span>
                                    <input type="number" name="meta_peso" value="{{ $additional->meta_peso }}" min="1" max="500" step="0.01" required class="w-full rounded-lg border border-slate-300 px-3 py-2.5 focus:border-purple-500 focus:ring-2 focus:ring-purple-100">
                                </label>
                            </div>

                            <label class="flex cursor-pointer items-center gap-3 rounded-lg border border-slate-200 p-3 hover:bg-purple-50">
                                <input type="hidden" name="semana_bonus" value="0">
                                <input type="checkbox" name="semana_bonus" value="1" @checked($additional->semana_bonus) class="h-4 w-4 accent-purple-700">
                                <span class="text-sm font-medium text-slate-700">Participará da semana bônus</span>
                            </label>

                            <div class="flex justify-end">
                                <button class="rounded-lg border border-purple-200 px-4 py-2.5 text-sm font-semibold text-purple-700 hover:bg-purple-50">
                                    Salvar alteração
                                </button>
                            </div>
                        </form>
                    </article>
                @endforeach
            </div>
        @endif

        @if ($group->user_groups->isEmpty())
            <div class="rounded-xl border border-slate-200 bg-white px-6 py-12 text-center text-sm text-slate-500">
                Nenhum usuário vinculado a este grupo.
            </div>
        @endif
    </section>
@endsection
