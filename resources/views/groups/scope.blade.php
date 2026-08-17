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

    <div class="mb-6 overflow-x-auto border-b border-slate-200" role="tablist" aria-label="Visualizações do grupo">
        <div class="flex min-w-max gap-1">
            <button
                type="button"
                role="tab"
                aria-selected="true"
                data-group-tab="tables"
                class="border-b-2 border-purple-700 px-4 py-3 text-sm font-semibold text-purple-800"
            >
                Tabelas principais
            </button>
            <button
                type="button"
                role="tab"
                aria-selected="false"
                data-group-tab="daily-control"
                class="border-b-2 border-transparent px-4 py-3 text-sm font-semibold text-slate-500 hover:text-purple-800"
            >
                Controle diário
            </button>
            <button
                type="button"
                role="tab"
                aria-selected="false"
                data-group-tab="notes"
                class="border-b-2 border-transparent px-4 py-3 text-sm font-semibold text-slate-500 hover:text-purple-800"
            >
                Anotações
            </button>
        </div>
    </div>

    <div data-group-tab-panel="tables" role="tabpanel">
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
    </div>

    @php
        $dailyPeriods = [
            'Manhã' => [
                'check_in' => 'Check-in',
                'interacao_livro' => 'Interação Livro',
                'balanca' => 'Balança',
                'cafe_da_manha' => 'Café da Manhã',
                'fruta_da_manha' => 'FRUTA da Manhã',
                'cha_da_manha' => 'Chá da Manhã',
            ],
            'Tarde' => [
                'almoco' => 'Almoço',
                'fruta_da_tarde' => 'FRUTA da Tarde',
                'cha_da_tarde' => 'Chá da Tarde',
            ],
            'Noite' => [
                'jantar' => 'Jantar',
                'fruta_da_noite' => 'FRUTA da Noite',
                'check_out' => 'Check-out',
            ],
        ];
    @endphp

    <div data-group-tab-panel="daily-control" role="tabpanel" class="hidden">
    @if ($group->user_groups->isNotEmpty())
        <section>
            <form method="GET" class="mb-5 flex flex-wrap items-end gap-3">
                <label>
                    <span class="mb-2 block text-sm font-medium text-slate-700">Dia dos controles</span>
                    <input type="date" name="date" value="{{ $selectedDate }}"
                        min="{{ $group->start_date->toDateString() }}" max="{{ $group->end_date->toDateString() }}"
                        class="rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-100">
                </label>
                <button class="rounded-lg bg-slate-800 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-900">Ver dia</button>
            </form>
            <div class="mb-3 flex items-center gap-3">
                <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-purple-700 text-sm font-bold text-white">✓</span>
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-purple-700">Registro do dia</p>
                    <h2 class="font-semibold text-slate-900">Controle diário · {{ \Carbon\Carbon::parse($selectedDate)->format('d/m/Y') }}</h2>
                </div>
            </div>

            <form method="POST" action="{{ route('users.updateDailyChecks') }}">
                @csrf
                <input type="hidden" name="groups_id" value="{{ $group->id }}">
                <input type="hidden" name="date" value="{{ $selectedDate }}">

                <div class="overflow-hidden rounded-lg border border-purple-300 bg-white shadow-sm">
                    <div class="overflow-x-auto">
                        <table
                            class="table-fixed border-collapse text-sm"
                            style="width: max(100%, {{ 10 + ($group->user_groups->count() * 8) }}rem)"
                        >
                            <thead>
                                <tr class="bg-purple-700 text-white">
                                    <th class="sticky left-0 z-20 w-40 border border-purple-600 bg-purple-700 px-4 py-3 text-left font-bold">Data / ação</th>
                                    @foreach ($group->user_groups as $userGroup)
                                        <th class="w-32 border border-purple-600 px-3 py-3 text-center font-bold break-words">{{ $userGroup->user->name }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($dailyPeriods as $period => $dailyFields)
                                    <tr>
                                        <th colspan="{{ $group->user_groups->count() + 1 }}" class="border border-purple-200 bg-gradient-to-r from-purple-100 via-violet-50 to-purple-100 px-4 py-2 text-center">
                                            <span class="inline-flex items-center gap-2 rounded-full bg-white/80 px-4 py-1 text-sm font-bold uppercase tracking-[0.16em] text-purple-900 shadow-sm ring-1 ring-purple-200">
                                                <span aria-hidden="true">{{ match ($period) { 'Manhã' => '☀', 'Tarde' => '◐', default => '☾' } }}</span>
                                                {{ $period }}
                                            </span>
                                        </th>
                                    </tr>
                                    @foreach ($dailyFields as $field => $label)
                                    <tr class="odd:bg-white even:bg-purple-50/50">
                                        <th class="sticky left-0 z-10 border border-slate-200 bg-inherit px-4 py-2.5 text-left font-semibold text-slate-800">
                                            <span class="block text-xs font-normal text-slate-500">{{ \Carbon\Carbon::parse($selectedDate)->format('d/m/Y') }}</span>
                                            {{ $label }}
                                        </th>
                                        @foreach ($group->user_groups as $userGroup)
                                            @php
                                                $daily = $todayDailies->get($userGroup->users_id);
                                                $state = $daily?->{$field};
                                            @endphp
                                            <td class="border border-slate-200 px-3 py-2 text-center">
                                                <input type="hidden" name="dailies[{{ $userGroup->users_id }}][{{ $field }}]" value="{{ $state === null ? '' : (int) $state }}" data-daily-state-input>
                                                <button
                                                    type="button"
                                                    data-daily-state-toggle
                                                    data-state="{{ $state === null ? 'unset' : ($state ? 'done' : 'not-done') }}"
                                                    class="inline-flex h-8 w-8 items-center justify-center rounded border text-base font-bold transition-colors"
                                                    aria-label="{{ $userGroup->user->name }} · {{ $label }}"
                                                ></button>
                                            </td>
                                        @endforeach
                                    </tr>
                                    @endforeach
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-3 flex flex-wrap items-center justify-between gap-3">
                    <div class="flex flex-wrap gap-4 text-xs text-slate-600">
                        <span><strong class="text-emerald-700">✓</strong> Fez</span>
                        <span><strong class="text-red-600">✕</strong> Informou que não fez</span>
                        <span><strong class="text-slate-400">□</strong> Não informou</span>
                    </div>
                    <button class="rounded-lg bg-purple-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-purple-800">Salvar controles do dia</button>
                </div>
            </form>
        </section>
    @endif
    </div>

    @php
        $noteColors = [
            'yellow' => 'bg-amber-100',
            'pink' => 'bg-rose-100',
            'blue' => 'bg-sky-100',
            'green' => 'bg-emerald-100',
            'peach' => 'bg-orange-100',
        ];
    @endphp
    <div data-group-tab-panel="notes" role="tabpanel" class="hidden">
        <div class="mb-5 flex flex-col justify-between gap-4 lg:flex-row lg:items-end">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-amber-800">Mural do grupo</p>
                <h2 class="mt-1 text-xl font-bold text-slate-900">Anotações de {{ \Carbon\Carbon::parse($selectedDate)->format('d/m/Y') }}</h2>
                <p class="mt-1 text-sm text-slate-500">Arraste os post-its livres. Fixe uma anotação para travá-la no lugar.</p>
            </div>
            <div class="flex flex-wrap items-end gap-3">
                <form method="GET" class="flex flex-wrap items-end gap-3">
                    <label>
                        <span class="mb-2 block text-sm font-medium text-slate-700">Dia das anotações</span>
                        <input type="date" name="date" value="{{ $selectedDate }}"
                            min="{{ $group->start_date->toDateString() }}" max="{{ $group->end_date->toDateString() }}"
                            class="rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm focus:border-amber-500 focus:ring-2 focus:ring-amber-100">
                    </label>
                    <button class="rounded-lg bg-slate-800 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-900">Ver dia</button>
                </form>
                <button type="button" data-dialog-open="note-composer"
                    class="rounded-lg bg-amber-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-amber-800">
                    + Nova anotação
                </button>
            </div>
        </div>

        <dialog id="note-composer" class="m-auto w-[calc(100%-2rem)] max-w-md rounded-2xl bg-white p-0 shadow-2xl backdrop:bg-slate-950/50">
            <div class="p-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-amber-700">Mural de {{ \Carbon\Carbon::parse($selectedDate)->format('d/m/Y') }}</p>
                        <h3 class="mt-1 text-xl font-bold text-slate-900">Novo post-it</h3>
                    </div>
                    <button type="button" data-dialog-close class="flex h-9 w-9 items-center justify-center rounded-full bg-slate-100 text-xl text-slate-500 hover:bg-slate-200">×</button>
                </div>
                <form method="POST" action="{{ route('groups.notes.store', $group) }}" class="mt-5">
                    @csrf
                    <input type="hidden" name="date" value="{{ $selectedDate }}">
                    <label class="block">
                        <span class="mb-2 block text-sm font-medium text-slate-700">Anotação</span>
                        <textarea name="content" rows="6" maxlength="1000" required placeholder="Escreva um lembrete..."
                            class="w-full resize-y rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-amber-500 focus:ring-2 focus:ring-amber-100"></textarea>
                    </label>
                    <fieldset class="mt-4">
                        <legend class="mb-2 text-sm font-medium text-slate-700">Cor do papel</legend>
                        <div class="flex gap-2">
                            @foreach ($noteColors as $color => $class)
                                <label class="cursor-pointer">
                                    <input type="radio" name="color" value="{{ $color }}" class="peer sr-only" @checked($color === 'yellow')>
                                    <span class="block h-8 w-8 rounded-full border-2 border-white shadow ring-1 ring-slate-300 {{ $class }} peer-checked:ring-2 peer-checked:ring-amber-700" title="{{ ucfirst($color) }}"></span>
                                </label>
                            @endforeach
                        </div>
                    </fieldset>
                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button" data-dialog-close class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-100">Cancelar</button>
                        <button class="rounded-lg bg-amber-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-amber-800">Adicionar ao mural</button>
                    </div>
                </form>
            </div>
        </dialog>

        <div class="overflow-x-auto rounded-sm bg-amber-950 p-1.5 shadow-xl sm:p-2">
                <div data-note-board class="note-board relative min-h-[38rem] min-w-[48rem] overflow-hidden border-[6px] border-amber-200 shadow-inner sm:border-8">
                    @forelse ($notes as $note)
                        <article
                            data-note
                            data-update-url="{{ route('groups.notes.update', [$group, $note]) }}"
                            data-pinned="{{ $note->is_pinned ? 'true' : 'false' }}"
                            class="absolute flex h-52 w-52 touch-none flex-col p-4 pt-8 shadow-[3px_6px_12px_rgba(55,36,15,0.28)] transition-shadow {{ $noteColors[$note->color] ?? $noteColors['yellow'] }} {{ $note->is_pinned ? 'cursor-default' : 'cursor-grab active:cursor-grabbing' }}"
                            style="left: {{ $note->position_x }}%; top: {{ $note->position_y }}%; z-index: {{ $note->z_index }}; transform: rotate({{ (($note->id % 5) - 2) * 0.7 }}deg)"
                        >
                            <button type="button" data-note-pin class="absolute left-1/2 top-1 h-7 w-7 -translate-x-1/2 rounded-full text-lg drop-shadow" title="{{ $note->is_pinned ? 'Desafixar anotação' : 'Fixar anotação' }}" aria-label="{{ $note->is_pinned ? 'Desafixar anotação' : 'Fixar anotação' }}">
                                {{ $note->is_pinned ? '📌' : '📍' }}
                            </button>
                            <textarea data-note-content maxlength="1000" aria-label="Texto da anotação"
                                class="min-h-0 flex-1 resize-none border-0 bg-transparent p-0 text-sm leading-relaxed text-slate-800 outline-none focus:ring-0">{{ $note->content }}</textarea>
                            <div class="mt-2 flex items-center justify-between gap-2 border-t border-black/10 pt-2">
                                <span data-note-status class="text-[11px] text-slate-500">{{ $note->is_pinned ? 'Fixada' : 'Arraste para mover' }}</span>
                                <form method="POST" action="{{ route('groups.notes.destroy', [$group, $note]) }}" data-note-delete-form>
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-xs font-semibold text-red-700 hover:text-red-900">Excluir</button>
                                </form>
                            </div>
                        </article>
                    @empty
                        <div data-empty-note-board class="absolute inset-0 flex items-center justify-center p-8 text-center">
                            <div class="rounded-xl bg-white/75 px-6 py-5 shadow-sm backdrop-blur-sm">
                                <p class="font-semibold text-amber-950">O mural está vazio neste dia.</p>
                                <p class="mt-1 text-sm text-amber-900/70">Use “Nova anotação” para criar o primeiro post-it.</p>
                            </div>
                        </div>
                    @endforelse
                </div>
        </div>
    </div>

    <div data-group-tab-panel="tables" role="tabpanel">
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
    </div>

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
        @php $daily = $todayDailies->get($userGroup->users_id); @endphp

        <dialog
            id="daily-{{ $userGroup->users_id }}"
            class="m-auto w-[calc(100%-2rem)] max-w-2xl rounded-2xl bg-white p-0 shadow-2xl backdrop:bg-slate-950/50"
        >
            <form method="POST" action="{{ route('users.updateDaily') }}" class="p-6 sm:p-7">
                @csrf
                <input type="hidden" name="users_id" value="{{ $userGroup->users_id }}">
                <input type="hidden" name="groups_id" value="{{ $group->id }}">

                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-purple-700">Peso por dia</p>
                        <h3 class="mt-1 text-xl font-bold text-slate-900">{{ $userGroup->user->name }}</h3>
                    </div>
                    <button type="button" data-dialog-close class="flex h-9 w-9 items-center justify-center rounded-full bg-slate-100 text-xl text-slate-500 hover:bg-slate-200">×</button>
                </div>

                <label class="mt-6 block">
                    <span class="mb-2 block text-sm font-medium text-slate-700">Dia da pesagem</span>
                    <input type="date" name="date" value="{{ $selectedDate }}"
                        min="{{ $group->start_date->toDateString() }}" max="{{ $group->end_date->toDateString() }}"
                        data-weight-date
                        class="w-full rounded-lg border border-slate-300 px-3 py-2.5 focus:border-purple-500 focus:ring-2 focus:ring-purple-100">
                </label>

                <label class="mt-4 block">
                    <span class="mb-2 block text-sm font-medium text-slate-700">Peso do dia (kg)</span>
                    <input
                        type="number"
                        name="peso"
                        value="{{ $daily?->peso }}"
                        min="1"
                        max="500"
                        step="0.01"
                        placeholder="Ex.: 72,50"
                        data-weight-value
                        class="w-full rounded-lg border border-slate-300 px-3 py-2.5 focus:border-purple-500 focus:ring-2 focus:ring-purple-100"
                    >
                </label>

                <div class="mt-7 flex justify-end gap-3">
                    <button type="button" data-dialog-close class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-100">Cancelar</button>
                    <button class="rounded-lg bg-purple-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-purple-800">Salvar peso</button>
                </div>
            </form>
            <script type="application/json" data-weight-history>{!! json_encode(
                ($periodDailies->get($userGroup->users_id, collect()))->mapWithKeys(
                    fn ($item, $date) => [$date => $item->peso]
                ),
                JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
            ) !!}</script>
        </dialog>
    @endforeach
@endsection
