@extends('layouts.app')

@section('title', 'Escopo de ' . $group->name)

@section('content')
    <div class="mb-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <a href="{{ route('groups.index') }}" class="text-sm font-semibold text-purple-700">
                ← Voltar aos grupos
            </a>
            <p class="mt-5 text-sm font-semibold uppercase tracking-wider text-purple-700">
                Escopo do grupo
            </p>
            <h1 class="mt-2 text-3xl font-bold">{{ $group->name }}</h1>
            <p class="mt-2 text-sm text-slate-500">
                Acompanhamento de peso e metas dos usuários.
            </p>
        </div>
        <button
            type="button"
            data-dialog-open="daily-message"
            class="rounded-lg bg-purple-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-purple-800"
        >
            Gerar mensagem do dia
        </button>
    </div>

    @if ($errors->any())
        <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="mb-3 flex items-center gap-3">
        <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-purple-700 text-sm font-bold text-white">01</span>
        <div>
            <p class="text-xs font-bold uppercase tracking-wider text-purple-700">Sessão 1</p>
            <h2 class="font-semibold text-slate-900">Acompanhamento diário</h2>
        </div>
    </div>

    <section class="overflow-hidden rounded-xl border border-purple-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table
                class="table-fixed border-collapse text-sm"
                style="width: max(100%, {{ 34 + ($historyDays->count() * 7) }}rem)"
            >
                <thead>
                    <tr class="bg-purple-400 text-purple-950">
                        <th class="sticky left-0 z-30 w-48 min-w-48 max-w-48 border-r border-purple-500 bg-purple-400 px-4 py-4 text-left font-bold">
                            Usuário
                        </th>
                        <th class="sticky left-48 z-30 w-28 min-w-28 max-w-28 border-r border-purple-500 bg-purple-400 px-4 py-4 text-center font-bold">
                            Peso atual
                        </th>
                        <th class="sticky left-[19rem] z-30 w-28 min-w-28 max-w-28 border-r border-purple-500 bg-purple-400 px-4 py-4 text-center font-bold shadow-[8px_0_12px_-10px_rgba(15,23,42,0.55)]">
                            Meta
                        </th>
                        @foreach ($historyDays as $day)
                            <th class="w-28 min-w-28 max-w-28 px-4 py-4 text-center font-bold">
                                {{ $day['label'] }}
                            </th>
                        @endforeach
                        <th class="sticky right-0 z-20 w-32 min-w-32 max-w-32 border-l border-purple-500 bg-purple-400 px-4 py-4 text-center font-bold shadow-[-8px_0_12px_-10px_rgba(15,23,42,0.55)]">
                            Participante
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse ($group->user_groups as $userGroup)
                        @php
                            $additional = $additionals->get($userGroup->users_id);
                            $todayDaily = $todayDailies->get($userGroup->users_id);
                            $userPeriodDailies = $periodDailies->get(
                                $userGroup->users_id,
                                collect()
                            );
                        @endphp
                        <tr class="hover:bg-purple-50">
                            <td class="sticky left-0 z-20 border-r border-slate-200 bg-white px-4 py-3 font-medium text-slate-900">
                                <button
                                    type="button"
                                    data-dialog-open="daily-{{ $userGroup->users_id }}"
                                    class="font-semibold text-purple-800 underline decoration-purple-300 underline-offset-4 hover:text-purple-950"
                                >
                                    {{ $userGroup->user->name }}
                                </button>
                            </td>
                            <td class="sticky left-48 z-20 border-r border-slate-200 bg-white px-4 py-3 text-center text-slate-700">
                                {{ $additional ? number_format($additional->peso_inicial, 2, ',', '.') : '—' }}
                            </td>
                            <td class="sticky left-[19rem] z-20 border-r border-slate-200 bg-white px-4 py-3 text-center font-medium text-slate-700 shadow-[8px_0_12px_-10px_rgba(15,23,42,0.4)]">
                                {{ $additional ? number_format($additional->meta_peso, 2, ',', '.') : '—' }}
                            </td>
                            @foreach ($historyDays as $day)
                                @php
                                    $dayDaily = $userPeriodDailies->get($day['date']);
                                @endphp
                                <td class="px-4 py-3 text-center font-semibold {{ $dayDaily ? 'text-purple-800' : 'text-slate-300' }}">
                                    {{ $dayDaily ? number_format($dayDaily->peso, 2, ',', '.') : '' }}
                                </td>
                            @endforeach
                            <td class="sticky right-0 z-10 border-l border-slate-200 bg-white px-4 py-3 text-center shadow-[-8px_0_12px_-10px_rgba(15,23,42,0.4)]">
                                <a
                                    href="{{ route('groups.participants.show', [$group, $userGroup->user]) }}"
                                    class="font-semibold text-purple-700 hover:text-purple-900"
                                >
                                    Abrir →
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $historyDays->count() + 4 }}" class="px-6 py-12 text-center text-slate-500">
                                Nenhum usuário vinculado a este grupo.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    @if ($accumulatedRows->isNotEmpty())
        <section class="mt-8">
            <div class="mb-3 flex items-center gap-3">
                <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-orange-500 text-sm font-bold text-white">02</span>
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-orange-700">Sessão 2</p>
                    <h2 class="font-semibold text-slate-900">Eliminação acumulada</h2>
                </div>
            </div>

            <div class="overflow-hidden rounded-xl border border-orange-300 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-max border-collapse text-xs">
                        <thead>
                            <tr>
                                <th colspan="{{ $historyDays->count() + 1 }}" class="border border-orange-600 bg-orange-500 px-4 py-3 text-left text-sm font-bold uppercase text-white">
                                    Eliminação acumulada · {{ $group->name }}
                                </th>
                            </tr>
                            <tr class="bg-orange-50">
                                <th class="sticky left-0 z-10 min-w-40 border border-slate-300 bg-orange-50 px-3 py-2 text-left font-bold">Usuário</th>
                                @foreach ($historyDays as $day)
                                    <th class="min-w-20 border border-slate-300 px-3 py-2 text-center font-semibold">{{ $day['label'] }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($accumulatedRows as $row)
                                <tr>
                                    <td class="sticky left-0 z-10 border border-slate-300 bg-white px-3 py-2 font-medium">{{ $row['name'] }}</td>
                                    @foreach ($row['accumulated'] as $value)
                                        <td class="border border-slate-300 px-3 py-2 text-center {{ $value !== null && $value < 0 ? 'text-emerald-700' : ($value !== null && $value > 0 ? 'text-red-600' : '') }}">
                                            {{ $value !== null ? number_format($value, 2, ',', '.') : '' }}
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    @endif

    @if ($dailyEliminationRows->isNotEmpty())
        <section class="mt-8">
            <div class="mb-3 flex items-center gap-3">
                <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-purple-700 text-sm font-bold text-white">03</span>
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-purple-700">Sessão 3</p>
                    <h2 class="font-semibold text-slate-900">Eliminação diária</h2>
                </div>
            </div>

            <div class="overflow-hidden rounded-xl border border-purple-200 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-max border-collapse text-xs">
                        <thead>
                            <tr>
                                <th colspan="{{ $historyDays->count() + 1 }}" class="border border-purple-500 bg-purple-400 px-4 py-3 text-left text-sm font-bold uppercase text-purple-950">
                                    Eliminação do dia · {{ $group->name }}
                                </th>
                            </tr>
                            <tr class="bg-purple-100">
                                <th class="sticky left-0 z-10 min-w-40 border border-slate-300 bg-purple-100 px-3 py-2 text-left font-bold">Usuário</th>
                                @foreach ($historyDays as $day)
                                    <th class="min-w-20 border border-slate-300 px-3 py-2 text-center font-semibold">{{ $day['label'] }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($dailyEliminationRows as $row)
                                <tr>
                                    <td class="sticky left-0 z-10 border border-slate-300 bg-white px-3 py-2 font-medium">{{ $row['name'] }}</td>
                                    @foreach ($row['daily'] as $value)
                                        <td class="border border-slate-300 px-3 py-2 text-center {{ $value !== null && $value > 0 ? 'text-emerald-700' : ($value !== null && $value < 0 ? 'text-red-600' : '') }}">
                                            {{ $value !== null ? number_format($value, 2, ',', '.') : '' }}
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    @endif

    <dialog
        id="daily-message"
        class="m-auto w-[calc(100%-2rem)] max-w-2xl rounded-2xl bg-white p-0 shadow-2xl backdrop:bg-slate-950/50"
    >
        <div class="p-6 sm:p-7">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-purple-700">Mensagem pronta</p>
                    <h2 class="mt-1 text-xl font-bold">Resultado do dia</h2>
                </div>
                <button type="button" data-dialog-close class="flex h-9 w-9 items-center justify-center rounded-full bg-slate-100 text-xl text-slate-500 hover:bg-slate-200">×</button>
            </div>

            <textarea
                data-daily-message
                readonly
                rows="22"
                class="mt-5 w-full resize-y rounded-lg border border-slate-300 bg-slate-50 p-4 font-mono text-sm leading-relaxed focus:border-purple-500 focus:ring-2 focus:ring-purple-100"
            >{{ $dailyMessage }}</textarea>

            <p data-copy-feedback class="mt-2 min-h-5 text-sm text-emerald-700"></p>

            <div class="mt-4 flex justify-end gap-3">
                <button type="button" data-dialog-close class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-100">Fechar</button>
                <button type="button" data-copy-daily-message class="rounded-lg bg-purple-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-purple-800">Copiar mensagem</button>
            </div>
        </div>
    </dialog>

    @foreach ($group->user_groups as $userGroup)
        @php
            $daily = $todayDailies->get($userGroup->users_id);
            $dailyFields = [
                'check_in' => 'Check-in',
                'desafio' => 'Desafio',
                'balanca' => 'Balança',
                'cafe_da_manha' => 'Café da manhã',
                'ceia' => 'Ceia',
                'cha_tarde' => 'Chá da tarde',
                'almoco' => 'Almoço',
                'ceia_tarde' => 'Ceia da tarde',
                'cha_noite' => 'Chá da noite',
                'jantar' => 'Jantar',
                'ceia_noite' => 'Ceia da noite',
                'check_out' => 'Check-out',
            ];
        @endphp

        <dialog
            id="daily-{{ $userGroup->users_id }}"
            class="m-auto w-[calc(100%-2rem)] max-w-2xl rounded-2xl bg-white p-0 shadow-2xl backdrop:bg-slate-950/50"
        >
            <form method="POST" action="{{ route('users.updateDaily') }}" class="p-6 sm:p-7">
                @csrf
                <input type="hidden" name="users_id" value="{{ $userGroup->users_id }}">
                <input type="hidden" name="groups_id" value="{{ $group->id }}">
                <input type="hidden" name="date" value="{{ $today }}">

                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-purple-700">Daily de hoje</p>
                        <h3 class="mt-1 text-xl font-bold text-slate-900">{{ $userGroup->user->name }}</h3>
                        <p class="mt-1 text-sm text-slate-500">{{ \Carbon\Carbon::parse($today)->format('d/m/Y') }}</p>
                    </div>
                    <button type="button" data-dialog-close class="flex h-9 w-9 items-center justify-center rounded-full bg-slate-100 text-xl text-slate-500 hover:bg-slate-200">×</button>
                </div>

                <label class="mt-6 block">
                    <span class="mb-2 block text-sm font-medium text-slate-700">Peso do dia (kg)</span>
                    <input
                        type="number"
                        name="peso"
                        value="{{ $daily?->peso }}"
                        min="1"
                        max="500"
                        step="0.01"
                        placeholder="Ex.: 72,50"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2.5 focus:border-purple-500 focus:ring-2 focus:ring-purple-100"
                    >
                </label>

                <div class="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($dailyFields as $field => $label)
                        <label class="flex cursor-pointer items-center gap-3 rounded-lg border border-slate-200 p-3 hover:border-purple-300 hover:bg-purple-50">
                            <input type="hidden" name="{{ $field }}" value="0">
                            <input
                                type="checkbox"
                                name="{{ $field }}"
                                value="1"
                                @checked($daily?->{$field})
                                class="h-4 w-4 accent-purple-700"
                            >
                            <span class="text-sm font-medium text-slate-700">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>

                <div class="mt-7 flex justify-end gap-3">
                    <button type="button" data-dialog-close class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-100">Cancelar</button>
                    <button class="rounded-lg bg-purple-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-purple-800">Salvar daily</button>
                </div>
            </form>
        </dialog>
    @endforeach
@endsection
