<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\User;
use App\Models\UserDaily;
use App\Models\UserGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ParticipantPeriodTest extends TestCase
{
    use RefreshDatabase;

    public function test_week_selector_has_four_monday_to_monday_periods(): void
    {
        [$group, $user] = $this->groupWithUser();

        $response = $this->get(route('groups.participants.show', [$group, $user]));

        $response->assertOk();
        $this->assertCount(4, $response->viewData('weeks'));
        $days = $response->viewData('days');
        $this->assertCount(8, $days);
        $this->assertSame('2026-08-03', $days->first()['date']);
        $this->assertSame('2026-08-10', $days->last()['date']);
        $response->assertDontSee('Semana 5');
    }

    public function test_general_chart_keeps_last_weight_after_final_selectable_week(): void
    {
        [$group, $user] = $this->groupWithUser();
        UserDaily::create([
            'groups_id' => $group->id,
            'users_id' => $user->id,
            'date' => '2026-09-01',
            'peso' => 70,
        ]);

        $response = $this->get(route('groups.participants.show', [$group, $user]));

        $response->assertOk();
        $allTimeDays = $response->viewData('allTimeDays');
        $lastWeightDay = $allTimeDays->first(
            fn ($day) => $day['date'] === '2026-09-01'
        );
        $this->assertNotNull($lastWeightDay);
        $this->assertSame(70.0, $lastWeightDay['daily']->peso);
    }

    public function test_charts_only_receive_days_that_have_a_weight(): void
    {
        [$group, $user] = $this->groupWithUser();
        UserDaily::create([
            'groups_id' => $group->id,
            'users_id' => $user->id,
            'date' => '2026-08-06',
            'check_in' => true,
        ]);
        UserDaily::create([
            'groups_id' => $group->id,
            'users_id' => $user->id,
            'date' => '2026-08-07',
            'peso' => 72.3,
        ]);

        $response = $this->get(route('groups.participants.show', [$group, $user]));

        $response->assertOk();
        $this->assertSame(
            ['2026-08-07'],
            $response->viewData('chartDays')->pluck('date')->all()
        );
        $this->assertSame(
            ['2026-08-07'],
            $response->viewData('allTimeDays')->pluck('date')->all()
        );
    }

    public function test_daily_tab_uses_week_dates_as_columns_for_all_checks(): void
    {
        [$group, $user] = $this->groupWithUser();
        UserDaily::create([
            'groups_id' => $group->id,
            'users_id' => $user->id,
            'date' => '2026-08-06',
            'peso' => 71.8,
            'check_in' => true,
            'interacao_livro' => false,
            'fruta_da_noite' => true,
        ]);

        $response = $this->get(route('groups.participants.show', [
            'group' => $group,
            'user' => $user,
            'tab' => 'daily',
        ]));

        $response->assertOk();
        $response->assertSeeInOrder(['03/08', '04/08', '05/08', '06/08']);
        $response->assertSeeInOrder(['Manhã', 'Tarde', 'Noite']);
        $response->assertSee('Interação Livro');
        $response->assertSee('FRUTA da Noite');
        $response->assertDontSee('Dia dos controles');
    }

    private function groupWithUser(): array
    {
        $group = Group::create([
            'name' => 'Grupo de teste',
            'start_date' => '2026-08-05',
            'end_date' => '2026-09-01',
        ]);
        $user = User::create(['name' => 'Participante de teste']);
        UserGroup::create([
            'groups_id' => $group->id,
            'users_id' => $user->id,
        ]);

        return [$group, $user];
    }
}
