<?php

namespace Database\Seeders;

use App\Models\Group;
use App\Models\User;
use App\Models\UserAdditionals;
use App\Models\UserDaily;
use App\Models\UserGroup;
use Carbon\CarbonPeriod;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VisualDemoSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $group = Group::updateOrCreate(
                ['name' => 'T58 — Demonstração completa'],
                [
                    'start_date' => '2026-07-08',
                    'end_date' => '2026-08-04',
                ]
            );

            $participants = [
                [
                    'name' => 'Alice Demo',
                    'initial' => 82.4,
                    'goal' => 77.5,
                    'bonus' => true,
                    'weights' => [
                        '2026-07-08' => 82.0,
                        '2026-07-09' => 81.7,
                        '2026-07-11' => 81.3,
                        '2026-07-14' => 80.8,
                        '2026-07-16' => 80.4,
                        '2026-07-19' => 80.1,
                        '2026-07-22' => 79.6,
                        '2026-07-25' => 79.2,
                        '2026-07-29' => 78.8,
                        '2026-08-01' => 78.4,
                        '2026-08-03' => 78.1,
                        '2026-08-05' => 77.8,
                    ],
                ],
                [
                    'name' => 'Bianca Demo',
                    'initial' => 71.8,
                    'goal' => 68.0,
                    'bonus' => false,
                    'weights' => [
                        '2026-07-08' => 71.5,
                        '2026-07-10' => 71.1,
                        '2026-07-12' => 70.9,
                        '2026-07-15' => 70.4,
                        '2026-07-18' => 70.6,
                        '2026-07-21' => 69.9,
                        '2026-07-24' => 69.5,
                        '2026-07-27' => 69.2,
                        '2026-07-30' => 68.9,
                        '2026-08-02' => 68.5,
                        '2026-08-05' => 68.2,
                    ],
                ],
                [
                    'name' => 'Carla Demo',
                    'initial' => 95.2,
                    'goal' => 90.0,
                    'bonus' => true,
                    'weights' => [
                        '2026-07-09' => 94.7,
                        '2026-07-13' => 94.1,
                        '2026-07-17' => 93.5,
                        '2026-07-20' => 93.0,
                        '2026-07-23' => 92.4,
                        '2026-07-26' => 92.0,
                        '2026-07-28' => 91.6,
                        '2026-07-31' => 91.1,
                        '2026-08-03' => 90.8,
                        '2026-08-05' => 90.3,
                    ],
                ],
                [
                    'name' => 'Daniela Demo',
                    'initial' => 63.5,
                    'goal' => 60.0,
                    'bonus' => false,
                    'weights' => [
                        '2026-07-08' => 63.2,
                        '2026-07-11' => 62.8,
                        '2026-07-14' => 62.5,
                        '2026-07-17' => 62.1,
                        '2026-07-20' => 61.9,
                        '2026-07-23' => 61.5,
                        '2026-07-26' => 61.2,
                        '2026-07-29' => 60.9,
                        '2026-08-01' => 60.5,
                        '2026-08-05' => 60.1,
                    ],
                ],
            ];

            foreach ($participants as $index => $participant) {
                $user = User::firstOrCreate([
                    'name' => $participant['name'],
                ]);

                UserGroup::firstOrCreate([
                    'users_id' => $user->id,
                    'groups_id' => $group->id,
                ]);

                UserAdditionals::updateOrCreate(
                    [
                        'users_id' => $user->id,
                        'groups_id' => $group->id,
                    ],
                    [
                        'peso_inicial' => $participant['initial'],
                        'meta_peso' => $participant['goal'],
                        'semana_bonus' => $participant['bonus'],
                    ]
                );

                $lastDate = array_key_last($participant['weights']);
                $finalWeight = $participant['weights'][$lastDate];
                $period = collect(CarbonPeriod::create(
                    $group->start_date,
                    $group->end_date
                ));
                $lastDayIndex = max(1, $period->count() - 1);

                foreach ($period as $dayIndex => $day) {
                    $date = $day->toDateString();
                    $progress = $dayIndex / $lastDayIndex;
                    $weight = round(
                        $participant['initial']
                            + ($finalWeight - $participant['initial'])
                            * $progress,
                        2
                    );
                    $completed = ((int) str_replace('-', '', $date) + $index) % 3;

                    UserDaily::updateOrCreate(
                        [
                            'users_id' => $user->id,
                            'groups_id' => $group->id,
                            'date' => $date,
                        ],
                        [
                            'peso' => $weight,
                            'check_in' => true,
                            'desafio' => $completed !== 0,
                            'balanca' => true,
                            'cafe_da_manha' => true,
                            'ceia' => $completed === 2,
                            'cha_tarde' => $completed !== 1,
                            'almoco' => true,
                            'ceia_tarde' => $completed === 1,
                            'cha_noite' => $completed !== 0,
                            'jantar' => true,
                            'ceia_noite' => $completed === 2,
                            'check_out' => $completed !== 0,
                        ]
                    );
                }
            }
        });
    }
}
