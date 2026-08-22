<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\User;
use App\Models\UserAdditionals;
use App\Models\UserDaily;
use App\Models\UserGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class DailyControlsTest extends TestCase
{
    use RefreshDatabase;

    private const CHECKS = [
        'check_in' => '1',
        'interacao_livro' => '0',
        'balanca' => null,
        'cafe_da_manha' => null,
        'fruta_da_manha' => null,
        'cha_da_manha' => null,
        'almoco' => null,
        'fruta_da_tarde' => null,
        'cha_da_tarde' => null,
        'jantar' => null,
        'fruta_da_noite' => null,
        'check_out' => null,
    ];

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-08-15 12:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_daily_controls_store_three_states_without_changing_weight(): void
    {
        [$group, $user] = $this->groupWithUser();
        UserDaily::create([
            'groups_id' => $group->id,
            'users_id' => $user->id,
            'date' => now()->toDateString(),
            'peso' => 72.5,
        ]);

        $response = $this->post(route('users.updateDailyChecks'), [
            'groups_id' => $group->id,
            'date' => now()->toDateString(),
            'dailies' => [$user->id => self::CHECKS],
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseCount('user_dailies', 1);
        $this->assertDatabaseHas('user_dailies', [
            'users_id' => $user->id,
            'check_in' => 1,
            'interacao_livro' => 0,
        ]);
        $daily = UserDaily::firstOrFail();
        $this->assertTrue($daily->check_in);
        $this->assertFalse($daily->interacao_livro);
        $this->assertNull($daily->balanca);
        $this->assertSame(72.5, $daily->peso);
    }

    public function test_invalid_participant_does_not_partially_save_controls(): void
    {
        [$group, $user] = $this->groupWithUser();
        $outsider = User::create(['name' => 'Participante externa']);

        $response = $this->post(route('users.updateDailyChecks'), [
            'groups_id' => $group->id,
            'date' => now()->toDateString(),
            'dailies' => [
                $user->id => self::CHECKS,
                $outsider->id => self::CHECKS,
            ],
        ]);

        $response->assertStatus(422);
        $this->assertDatabaseCount('user_dailies', 0);
    }

    public function test_controls_and_weight_can_be_changed_for_a_selected_group_day(): void
    {
        [$group, $user] = $this->groupWithUser();
        $date = '2026-08-08';

        $checksResponse = $this->post(route('users.updateDailyChecks'), [
            'groups_id' => $group->id,
            'date' => $date,
            'dailies' => [$user->id => self::CHECKS],
        ]);
        $weightResponse = $this->post(route('users.updateDaily'), [
            'groups_id' => $group->id,
            'users_id' => $user->id,
            'date' => $date,
            'peso' => 71.4,
        ]);

        $checksResponse->assertSessionHasNoErrors();
        $weightResponse->assertSessionHasNoErrors();
        $this->assertDatabaseHas('user_dailies', [
            'groups_id' => $group->id,
            'users_id' => $user->id,
            'date' => $date.' 00:00:00',
            'peso' => 71.4,
            'check_in' => 1,
            'interacao_livro' => 0,
        ]);
    }

    public function test_daily_changes_reject_days_outside_the_group_period(): void
    {
        [$group, $user] = $this->groupWithUser();

        $response = $this->post(route('users.updateDaily'), [
            'groups_id' => $group->id,
            'users_id' => $user->id,
            'date' => '2026-07-31',
            'peso' => 71.4,
        ]);

        $response->assertStatus(422);
        $this->assertDatabaseCount('user_dailies', 0);
    }

    public function test_scope_loads_checks_from_the_selected_day(): void
    {
        [$group, $user] = $this->groupWithUser();
        UserDaily::create([
            'groups_id' => $group->id,
            'users_id' => $user->id,
            'date' => '2026-08-09',
            'check_in' => true,
            'peso' => 70.8,
        ]);

        $response = $this->get(route('groups.scope', [
            'group' => $group,
            'date' => '2026-08-09',
        ]));

        $response->assertOk();
        $this->assertSame('2026-08-09', $response->viewData('selectedDate'));
        $selectedDaily = $response->viewData('todayDailies')->get($user->id);
        $this->assertTrue($selectedDaily->check_in);
        $this->assertSame(70.8, $selectedDaily->peso);
    }

    public function test_daily_message_is_generated_for_the_selected_past_day(): void
    {
        [$group, $user] = $this->groupWithUser();
        $group->update(['name' => 'T59']);
        UserAdditionals::create([
            'groups_id' => $group->id,
            'users_id' => $user->id,
            'peso_inicial' => 70,
            'meta_peso' => 60,
        ]);
        UserDaily::create([
            'groups_id' => $group->id,
            'users_id' => $user->id,
            'date' => '2026-08-04',
            'peso' => 69.8,
        ]);
        UserDaily::create([
            'groups_id' => $group->id,
            'users_id' => $user->id,
            'date' => '2026-08-05',
            'peso' => 69.4,
        ]);

        $response = $this->get(route('groups.scope', [
            'group' => $group,
            'date' => '2026-08-05',
        ]));

        $message = $response->viewData('dailyMessage');
        $this->assertStringContainsString(
            '*T59 - PROGRAMA DE EMAGRECIMENTO EMOCIONAL - RESULTADO DO 2º DIA* 🎖',
            $message
        );
        $this->assertStringContainsString(
            '▪ Participante de teste = *-400gr* (-600gr)',
            $message
        );
        $this->assertStringContainsString(
            '🏆 *RESULTADO TOTAL DO DIA = -400gr ✨🔥🔥👏👏💃🏻*',
            $message
        );
    }

    public function test_scope_does_not_allow_message_dates_after_today(): void
    {
        [$group] = $this->groupWithUser();

        $response = $this->get(route('groups.scope', [
            'group' => $group,
            'date' => '2026-08-20',
        ]));

        $this->assertSame('2026-08-15', $response->viewData('selectedDate'));
        $this->assertSame('2026-08-15', $response->viewData('messageMaxDate'));
    }

    public function test_scope_uses_the_current_date_in_sao_paulo(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-08-16 02:30:00 UTC'));
        [$group] = $this->groupWithUser();

        $response = $this->get(route('groups.scope', $group));

        $response->assertOk();
        $this->assertSame('America/Sao_Paulo', config('app.timezone'));
        $this->assertSame('2026-08-15', $response->viewData('today'));
        $this->assertSame('2026-08-15', $response->viewData('selectedDate'));
    }

    private function groupWithUser(): array
    {
        $group = Group::create([
            'name' => 'Grupo de teste',
            'start_date' => '2026-08-03',
            'end_date' => '2026-08-30',
        ]);
        $user = User::create(['name' => 'Participante de teste']);
        UserGroup::create([
            'groups_id' => $group->id,
            'users_id' => $user->id,
        ]);

        return [$group, $user];
    }
}
