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
                <table class="min-w-full border-collapse text-sm">
                    <thead>
                        <tr class="bg-purple-100 text-purple-950">
                            <th class="px-4 py-3 text-left">Data</th>
                            <th class="px-4 py-3 text-center">Peso</th>
                            <th class="px-4 py-3 text-center">Check-in</th>
                            <th class="px-4 py-3 text-center">Desafio</th>
                            <th class="px-4 py-3 text-center">Balança</th>
                            <th class="px-4 py-3 text-center">Check-out</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($days as $day)
                            @php $daily = $day['daily']; @endphp
                            <tr>
                                <td class="px-4 py-3 font-medium">{{ $day['label'] }}</td>
                                <td class="px-4 py-3 text-center">{{ $daily?->peso !== null ? number_format($daily->peso, 2, ',', '.') : '—' }}</td>
                                <td class="px-4 py-3 text-center">{{ $daily?->check_in ? '✓' : '—' }}</td>
                                <td class="px-4 py-3 text-center">{{ $daily?->desafio ? '✓' : '—' }}</td>
                                <td class="px-4 py-3 text-center">{{ $daily?->balanca ? '✓' : '—' }}</td>
                                <td class="px-4 py-3 text-center">{{ $daily?->check_out ? '✓' : '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div data-participant-panel="chart" class="hidden p-4 sm:p-6">
            <div class="mb-4 flex flex-wrap gap-5 text-xs font-semibold">
                <span class="flex items-center gap-2 text-slate-600"><i class="h-0.5 w-7 bg-blue-600"></i> Meta de peso</span>
                <span class="flex items-center gap-2 text-slate-600"><i class="h-0.5 w-7 bg-purple-500"></i> Peso nas dailies</span>
            </div>
            <div data-weight-chart data-chart-source="participant-chart-data" class="min-h-80 overflow-x-auto"></div>
        </div>

        <div data-participant-panel="all-time" class="hidden p-4 sm:p-6">
            <div class="mb-4 flex flex-wrap items-center justify-between gap-4">
                <div class="flex flex-wrap gap-5 text-xs font-semibold">
                    <span class="flex items-center gap-2 text-slate-600"><i class="h-0.5 w-7 bg-blue-600"></i> Meta de peso</span>
                    <span class="flex items-center gap-2 text-slate-600"><i class="h-0.5 w-7 bg-purple-500"></i> Peso nas dailies</span>
                </div>
                @if ($allTimeDays->isNotEmpty())
                    <span class="text-xs text-slate-500">
                        {{ $allTimeDays->first()['label'] }} até {{ $allTimeDays->last()['label'] }}
                    </span>
                @endif
            </div>
            <div data-weight-chart data-chart-source="participant-all-time-chart-data" class="min-h-80 overflow-x-auto"></div>
        </div>
    </section>

    <script id="participant-chart-data" type="application/json">{!! json_encode([
        'name' => $user->name,
        'goal' => $goalWeight,
        'days' => $days->map(fn ($day) => [
            'date' => $day['date'],
            'label' => $day['label'],
            'weight' => $day['daily']?->peso,
        ])->values(),
    ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>

    <script id="participant-all-time-chart-data" type="application/json">{!! json_encode([
        'name' => $user->name,
        'goal' => $goalWeight,
        'days' => $allTimeDays->map(fn ($day) => [
            'date' => $day['date'],
            'label' => $day['label'],
            'weight' => $day['daily']?->peso,
        ])->values(),
    ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
@endsection
