@extends('layouts.app')

@section('title', $user->name . ' · ' . $group->name)

@section('content')
    @php
        $initialWeight = $additional?->peso_inicial;
        $goalWeight = $additional?->meta_peso;
        $eliminated = $initialWeight !== null && $latestWeight !== null
            ? $initialWeight - $latestWeight
            : null;
        $remaining = $goalWeight !== null && $latestWeight !== null
            ? max(0, $latestWeight - $goalWeight)
            : null;
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

    <div class="mb-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <a href="{{ route('groups.scope', $group) }}" class="text-sm font-semibold text-purple-700">
                ← Voltar ao grupo
            </a>
            <p class="mt-5 text-xs font-bold uppercase tracking-wider text-purple-700">
                {{ $group->name }}
            </p>
            <h1 class="mt-1 text-3xl font-bold">{{ $user->name }}</h1>
        </div>

        <form method="GET" class="flex items-end gap-3">
            <input type="hidden" name="tab" value="{{ request('tab', 'daily') }}" data-selected-tab>
            <label>
                <span class="mb-2 block text-xs font-semibold uppercase tracking-wider text-slate-500">
                    Semana do grupo
                </span>
                <select name="week" data-week-select class="rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-100">
                    @foreach ($weeks as $week)
                        <option value="{{ $week['number'] }}" @selected($week['number'] === $selectedWeek)>
                            Semana {{ $week['number'] }} · {{ $week['start']->format('d/m') }}–{{ $week['end']->format('d/m') }}
                        </option>
                    @endforeach
                </select>
            </label>
            <button class="rounded-lg bg-purple-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-purple-800">
                Ver semana
            </button>
        </form>
    </div>

    <div class="mb-6 grid gap-4 sm:grid-cols-3">
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <span class="text-xs text-slate-500">Peso inicial</span>
            <strong class="mt-1 block text-xl">{{ $initialWeight !== null ? number_format($initialWeight, 2, ',', '.') . ' kg' : '—' }}</strong>
        </div>
        <div class="rounded-xl border border-purple-200 bg-purple-50 p-4 shadow-sm">
            <span class="text-xs text-purple-700">Total eliminado</span>
            <strong class="mt-1 block text-xl text-purple-900">{{ $eliminated !== null ? number_format($eliminated, 2, ',', '.') . ' kg' : '—' }}</strong>
        </div>
        <div class="rounded-xl border border-blue-200 bg-blue-50 p-4 shadow-sm">
            <span class="text-xs text-blue-700">Faltam para a meta</span>
            <strong class="mt-1 block text-xl text-blue-900">{{ $remaining !== null ? number_format($remaining, 2, ',', '.') . ' kg' : '—' }}</strong>
        </div>
    </div>

    <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="flex border-b border-slate-200 px-4 pt-3" role="tablist">
            <button type="button" data-participant-tab="daily" class="border-b-2 border-purple-700 px-4 py-3 text-sm font-semibold text-purple-800">
                Daily
            </button>
            <button type="button" data-participant-tab="chart" class="border-b-2 border-transparent px-4 py-3 text-sm font-semibold text-slate-500 hover:text-purple-800">
                Gráfico semanal
            </button>
            <button type="button" data-participant-tab="all-time" class="border-b-2 border-transparent px-4 py-3 text-sm font-semibold text-slate-500 hover:text-purple-800">
                Gráfico geral
            </button>
        </div>

        <div data-participant-panel="daily" class="p-5 sm:p-6">
            <div class="overflow-x-auto">
                <table class="table-fixed border-collapse text-sm" style="width: max(100%, {{ 12 + ($days->count() * 7) }}rem)">
                    <thead>
                        <tr class="bg-emerald-700 text-white">
                            <th class="sticky left-0 z-20 w-48 border border-emerald-600 bg-emerald-700 px-4 py-3 text-left">Ação</th>
                            @foreach ($days as $day)
                                <th class="w-28 border border-emerald-600 px-3 py-3 text-center">{{ $day['label'] }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($dailyPeriods as $period => $fields)
                            <tr>
                                <th colspan="{{ $days->count() + 1 }}" class="border border-slate-400 bg-white px-4 py-1.5 text-center text-base font-medium text-slate-950">{{ $period }}</th>
                            </tr>
                            @foreach ($fields as $field => $label)
                                <tr class="odd:bg-white even:bg-emerald-50/50">
                                    <th class="sticky left-0 z-10 border border-slate-200 bg-inherit px-4 py-2.5 text-left font-semibold text-slate-800">{{ $label }}</th>
                                    @foreach ($days as $day)
                                        @php $state = $day['daily']?->{$field}; @endphp
                                        <td class="border border-slate-200 px-3 py-2.5 text-center">
                                            @if ($state === null)
                                                <span class="inline-flex h-8 w-8 items-center justify-center rounded border border-slate-300 bg-white font-bold text-slate-400" title="Não informou">□</span>
                                            @elseif ($state)
                                                <span class="inline-flex h-8 w-8 items-center justify-center rounded border border-emerald-600 bg-emerald-600 font-bold text-white" title="Fez">✓</span>
                                            @else
                                                <span class="inline-flex h-8 w-8 items-center justify-center rounded border border-red-500 bg-red-50 font-bold text-red-600" title="Informou que não fez">✕</span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-3 flex flex-wrap gap-4 text-xs text-slate-600">
                <span><strong class="text-emerald-700">✓</strong> Fez</span>
                <span><strong class="text-red-600">✕</strong> Informou que não fez</span>
                <span><strong class="text-slate-400">□</strong> Não informou</span>
            </div>
        </div>

        <div data-participant-panel="chart" class="hidden p-4 sm:p-6">
            <div data-weight-chart data-chart-source="participant-chart-data" class="min-h-80 overflow-x-auto"></div>
        </div>

        <div data-participant-panel="all-time" class="hidden p-4 sm:p-6">
            <div data-weight-chart data-chart-source="participant-all-time-chart-data" class="min-h-80 overflow-x-auto"></div>
        </div>
    </section>

    <script id="participant-chart-data" type="application/json">{!! json_encode([
        'name' => $user->name,
        'program' => 'Programa de Emagrecimento Emocional',
        'logo' => asset('images/programa-emagrecimento-emocional.png'),
        'initial' => $initialWeight,
        'goal' => $goalWeight,
        'days' => $chartDays->map(fn ($day) => [
            'date' => $day['date'],
            'label' => $day['label'],
            'weight' => $day['daily']?->peso,
        ])->values(),
    ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>

    <script id="participant-all-time-chart-data" type="application/json">{!! json_encode([
        'name' => $user->name,
        'program' => 'Programa de Emagrecimento Emocional',
        'logo' => asset('images/programa-emagrecimento-emocional.png'),
        'initial' => $initialWeight,
        'goal' => $goalWeight,
        'days' => $allTimeDays->map(fn ($day) => [
            'date' => $day['date'],
            'label' => $day['label'],
            'weight' => $day['daily']?->peso,
        ])->values(),
    ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
@endsection
