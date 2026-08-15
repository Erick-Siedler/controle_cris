<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\User;
use App\Models\UserAdditionals;
use App\Models\UserDaily;
use App\Models\UserGroup;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GroupController extends Controller
{
    public function index()
    {
        $groups = Group::withCount('user_groups')
            ->orderByDesc('created_at')
            ->get();

        return view('groups.index', compact('groups'));
    }

    public function create()
    {
        $users = User::orderBy('name')->get();

        return view('groups.create', compact('users'));
    }

    public function store(Request $request)
    {
        $data = $this->validateGroup($request);

        $group = DB::transaction(function () use ($data) {
            $group = Group::create([
                'name' => $data['name'],
                'start_date' => $data['start_date'],
                'end_date' => Carbon::parse($data['start_date'])
                    ->addDays(27),
            ]);

            $this->syncUsers($group, $data['users']);

            return $group;
        });

        return redirect()
            ->route('groups.index')
            ->with('success', "Grupo {$group->name} criado com sucesso.");
    }

    public function show(Group $group)
    {
        $group->load('user_groups.user');
        $additionals = UserAdditionals::where('groups_id', $group->id)
            ->whereIn(
                'users_id',
                $group->user_groups->pluck('users_id')
            )
            ->get()
            ->keyBy('users_id');

        return view(
            'groups.show',
            compact('group', 'additionals')
        );
    }

    public function scope(Group $group)
    {
        $group->load('user_groups.user');
        $userIds = $group->user_groups->pluck('users_id');
        $additionals = UserAdditionals::where('groups_id', $group->id)
            ->whereIn('users_id', $userIds)
            ->get()
            ->keyBy('users_id');
        $today = now()->toDateString();
        $selectedDate = $requestDate = request('date', $today);
        if (! is_string($requestDate)
            || ! Carbon::hasFormat($requestDate, 'Y-m-d')
            || $requestDate < $group->start_date->toDateString()
            || $requestDate > $group->end_date->toDateString()) {
            $selectedDate = $today;
        }
        $selectedDate = min(
            max($selectedDate, $group->start_date->toDateString()),
            $group->end_date->toDateString()
        );
        $todayDailies = UserDaily::where('groups_id', $group->id)
            ->whereIn('users_id', $userIds)
            ->whereDate('date', $selectedDate)
            ->get()
            ->keyBy('users_id');
        $showToday = $todayDailies->contains(
            fn (UserDaily $daily) => $daily->peso !== null
        );
        $periodDays = collect(CarbonPeriod::create(
            $group->start_date,
            $group->end_date
        ))->map(fn (Carbon $date) => [
            'date' => $date->toDateString(),
            'label' => $date->format('d/m'),
        ]);
        $periodDailies = UserDaily::where('groups_id', $group->id)
            ->whereIn('users_id', $userIds)
            ->whereDate('date', '>=', $group->start_date->toDateString())
            ->whereDate('date', '<=', $group->end_date->toDateString())
            ->whereNotNull('peso')
            ->orderBy('date')
            ->get()
            ->groupBy('users_id')
            ->map(fn ($items) => $items->keyBy(
                fn (UserDaily $daily) => $daily->date->toDateString()
            ));
        $historyDays = $periodDays->filter(
            fn ($day) => $periodDailies->contains(
                fn ($userDailies) => $userDailies->has($day['date'])
            )
        )->values();
        $weightHistory = $group->user_groups->map(function (
            UserGroup $userGroup
        ) use ($additionals, $historyDays, $periodDailies) {
            $initialWeight = $additionals
                ->get($userGroup->users_id)?->peso_inicial;
            $userDailies = $periodDailies->get(
                $userGroup->users_id,
                collect()
            );
            $previousWeight = $initialWeight;
            $accumulated = [];
            $dailyElimination = [];

            foreach ($historyDays as $day) {
                $weight = $userDailies->get($day['date'])?->peso;

                if ($weight === null) {
                    $accumulated[] = null;
                    $dailyElimination[] = null;

                    continue;
                }

                $accumulated[] = $initialWeight !== null
                    ? round($weight - $initialWeight, 2)
                    : null;
                $dailyElimination[] = $previousWeight !== null
                    ? round($previousWeight - $weight, 2)
                    : null;
                $previousWeight = $weight;
            }

            return [
                'name' => $userGroup->user->name,
                'accumulated' => $accumulated,
                'daily' => $dailyElimination,
            ];
        });
        $accumulatedRows = $weightHistory->filter(
            fn ($row) => collect($row['accumulated'])->contains(
                fn ($value) => $value !== null
            )
        )->values();
        $dailyEliminationRows = $weightHistory->filter(
            fn ($row) => collect($row['daily'])->contains(
                fn ($value) => $value !== null
            )
        )->values();
        $dailyMessage = $this->buildDailyMessage(
            $group,
            $additionals,
            $periodDailies,
            $today
        );

        return view(
            'groups.scope',
            compact(
                'group',
                'additionals',
                'today',
                'selectedDate',
                'todayDailies',
                'showToday',
                'historyDays',
                'periodDailies',
                'accumulatedRows',
                'dailyEliminationRows',
                'dailyMessage'
            )
        );
    }

    public function participant(
        Request $request,
        Group $group,
        User $user
    ) {
        abort_unless(
            UserGroup::where('groups_id', $group->id)
                ->where('users_id', $user->id)
                ->exists(),
            404
        );

        $additional = UserAdditionals::where('groups_id', $group->id)
            ->where('users_id', $user->id)
            ->first();
        $groupStart = $group->start_date->copy()->startOfDay();
        $groupEnd = $group->end_date->copy()->startOfDay();
        $calendarStart = $groupStart->copy()->startOfWeek(Carbon::MONDAY);
        $weekCount = max(
            1,
            (int) ceil(($groupStart->diffInDays($groupEnd) + 1) / 7)
        );
        $selectedWeek = min(
            $weekCount,
            max(1, $request->integer('week', 1))
        );
        $weekStart = $calendarStart->copy()->addDays(
            ($selectedWeek - 1) * 7
        );
        $weekEnd = $weekStart->copy()->addDays(7);
        $weeks = collect(range(1, $weekCount))->map(function ($week) use (
            $calendarStart
        ) {
            $start = $calendarStart->copy()->addDays(($week - 1) * 7);
            $end = $start->copy()->addDays(7);

            return [
                'number' => $week,
                'start' => $start,
                'end' => $end,
            ];
        });
        $dailies = UserDaily::where('groups_id', $group->id)
            ->where('users_id', $user->id)
            ->whereDate('date', '>=', $weekStart->toDateString())
            ->whereDate('date', '<=', $weekEnd->toDateString())
            ->orderBy('date')
            ->get()
            ->keyBy(fn (UserDaily $daily) => $daily->date->toDateString());
        $days = collect(CarbonPeriod::create($weekStart, $weekEnd))
            ->map(function (Carbon $date) use ($dailies) {
                $key = $date->toDateString();

                return [
                    'date' => $key,
                    'label' => $date->format('d/m'),
                    'daily' => $dailies->get($key),
                ];
            });
        $chartDays = $days->filter(
            fn ($day) => $day['daily']?->peso !== null
        )->values();
        $allTimeDailies = UserDaily::where('groups_id', $group->id)
            ->where('users_id', $user->id)
            ->whereNotNull('peso')
            ->whereDate('date', '>=', $groupStart->toDateString())
            ->whereDate('date', '<=', $groupEnd->toDateString())
            ->orderBy('date')
            ->get()
            ->keyBy(fn (UserDaily $daily) => $daily->date->toDateString());
        $latestDaily = $allTimeDailies->last();
        $latestWeight = $latestDaily?->peso;
        $allTimeDays = $allTimeDailies->map(fn (UserDaily $daily) => [
            'date' => $daily->date->toDateString(),
            'label' => $daily->date->format('d/m'),
            'daily' => $daily,
        ])->values();

        return view(
            'groups.participants.show',
            compact(
                'group',
                'user',
                'additional',
                'weeks',
                'selectedWeek',
                'days',
                'chartDays',
                'allTimeDays',
                'latestWeight'
            )
        );
    }

    public function edit(Group $group)
    {
        $users = User::orderBy('name')->get();
        $selectedUserIds = $group->user_groups()
            ->pluck('users_id')
            ->all();

        return view(
            'groups.edit',
            compact('group', 'users', 'selectedUserIds')
        );
    }

    public function update(Request $request, Group $group)
    {
        $data = $this->validateGroup($request);

        DB::transaction(function () use ($group, $data) {
            $group->update([
                'name' => $data['name'],
                'start_date' => $data['start_date'],
                'end_date' => Carbon::parse($data['start_date'])
                    ->addDays(27),
            ]);

            $this->syncUsers($group, $data['users']);
        });

        return redirect()
            ->route('groups.index')
            ->with('success', 'Grupo atualizado com sucesso.');
    }

    public function destroy(Group $group)
    {
        $group->delete();

        return redirect()
            ->route('groups.index')
            ->with('success', 'Grupo excluído com sucesso.');
    }

    private function validateGroup(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'min:3', 'max:255'],
            'start_date' => ['required', 'date'],
            'users' => ['required', 'array', 'min:1'],
            'users.*' => ['integer', 'distinct', 'exists:users,id'],
        ]);
    }

    private function syncUsers(Group $group, array $userIds): void
    {
        $group->user_groups()
            ->whereNotIn('users_id', $userIds)
            ->delete();

        foreach ($userIds as $userId) {
            UserGroup::firstOrCreate([
                'groups_id' => $group->id,
                'users_id' => $userId,
            ]);
        }
    }

    private function buildDailyMessage(
        Group $group,
        $additionals,
        $periodDailies,
        string $today
    ): string {
        $eliminated = [];
        $increased = [];
        $maintained = [];
        $dayTotal = 0.0;
        $accumulatedTotal = 0.0;

        foreach ($group->user_groups as $userGroup) {
            $userDailies = $periodDailies->get(
                $userGroup->users_id,
                collect()
            );
            $todayWeight = $userDailies->get($today)?->peso;

            if ($todayWeight === null) {
                continue;
            }

            $additional = $additionals->get($userGroup->users_id);
            $initialWeight = $additional?->peso_inicial;
            $previousWeight = $userDailies
                ->filter(fn (UserDaily $daily) => (
                    $daily->date->toDateString() < $today
                    && $daily->peso !== null
                ))
                ->last()?->peso ?? $initialWeight;

            if ($previousWeight === null) {
                continue;
            }

            $dayChange = round($todayWeight - $previousWeight, 1);
            $accumulated = $initialWeight !== null
                ? round($todayWeight - $initialWeight, 1)
                : null;
            $line = '▪ '.$userGroup->user->name
                .' = *'.$this->formatMessageWeight($dayChange).'*'
                .($accumulated !== null
                    ? ' ('.$this->formatMessageWeight($accumulated).')'
                    : '');

            if ($dayChange < 0) {
                $eliminated[] = $line;
            } elseif ($dayChange > 0) {
                $increased[] = $line;
            } else {
                $maintained[] = $line;
            }

            $dayTotal += $dayChange;

            if ($accumulated !== null) {
                $accumulatedTotal += $accumulated;
            }
        }

        $groupDay = (int) max(
            1,
            $group->start_date->diffInDays(Carbon::parse($today), false) + 1
        );
        $lines = [
            '*'.mb_strtoupper($group->name)
                .' - RESULTADO DO '.$groupDay.'º DIA* 🎖',
            '',
            '✅ *Eliminou:*',
            '',
            ...($eliminated ?: ['▪ -']),
            '',
            '⚠️ *Aumentou:*',
            '',
            ...($increased ?: ['▪ -']),
            '',
            '🔷 *Manteve:*',
            ...($maintained ?: ['▪ -']),
            '',
            '🏆 *RESULTADO TOTAL DO DIA =* '
                .$this->formatMessageWeight(round($dayTotal, 1))
                .' ✨🔥🔥👏👏💃🏻',
            '',
            '*TOTAL EM '.$groupDay.' DIAS = ('
                .$this->formatMessageWeight(round($accumulatedTotal, 1))
                .') 🔥🔥🔥👏*',
        ];

        return implode("\n", $lines);
    }

    private function formatMessageWeight(float $weight): string
    {
        $sign = $weight > 0 ? '+' : ($weight < 0 ? '-' : '');
        $absolute = abs($weight);

        if ($absolute < 1) {
            return $sign.number_format(
                round($absolute * 1000),
                0,
                ',',
                '.'
            ).'gr';
        }

        return $sign.number_format($absolute, 1, ',', '.').'Kg';
    }
}
