<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\User;
use App\Models\UserAdditionals;
use App\Models\UserDaily;
use App\Models\UserGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class GoalAchievementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-08-07 12:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public static function weights(): array
    {
        return [
            'at goal' => [60, 60, true],
            'below goal' => [59.5, 60, true],
            'above goal' => [60.01, 60, false],
            'no weight' => [null, 60, false],
            'no goal' => [60, null, false],
        ];
    }

    #[DataProvider('weights')]
    public function test_saved_weight_celebration_and_goal_display(?float $weight, ?float $goal, bool $reached): void
    {
        [$group, $user] = $this->participant($goal);
        $scope = route('groups.scope', $group);
        $response = $this->from($scope)->post(route('users.updateDaily'), [
            'groups_id' => $group->id,
            'users_id' => $user->id,
            'date' => '2026-08-07',
            'peso' => $weight,
        ]);

        $response->assertRedirect($scope)->assertSessionHasNoErrors();
        if ($reached) {
            $response->assertSessionHas('goal_reached');
            $this->get($scope)->assertSee('data-goal-celebration', false)
                ->assertSee('title="Meta alcançada · Em manutenção"', false);
            $this->get($scope)->assertDontSee('data-goal-celebration', false);
        } else {
            $response->assertSessionMissing('goal_reached');
            $this->get($scope)->assertDontSee('title="Meta alcançada · Em manutenção"', false);
        }

        $participant = $this->get(route('groups.participants.show', [$group, $user]));
        $participant->assertOk();
        if ($reached) {
            $participant->assertSee('Em manutenção')->assertDontSee('Faltam para a meta');
        } else {
            $participant->assertSee('Faltam para a meta')->assertDontSee('Em manutenção');
        }
    }

    public function test_editing_an_existing_weight_at_goal_does_not_repeat_celebration(): void
    {
        [$group, $user] = $this->participant(60);
        UserDaily::create([
            'groups_id' => $group->id,
            'users_id' => $user->id,
            'date' => '2026-08-07',
            'peso' => 60,
        ]);

        $this->post(route('users.updateDaily'), [
            'groups_id' => $group->id,
            'users_id' => $user->id,
            'date' => '2026-08-07',
            'peso' => 59.8,
        ])->assertSessionMissing('goal_reached');
    }

    public function test_correcting_a_weight_to_reach_goal_triggers_celebration(): void
    {
        [$group, $user] = $this->participant(60);
        UserDaily::create([
            'groups_id' => $group->id,
            'users_id' => $user->id,
            'date' => '2026-08-07',
            'peso' => 60.1,
        ]);

        $this->post(route('users.updateDaily'), [
            'groups_id' => $group->id,
            'users_id' => $user->id,
            'date' => '2026-08-07',
            'peso' => 60,
        ])->assertSessionHas('goal_reached');
    }

    public function test_latest_weight_above_goal_returns_to_remaining_metric(): void
    {
        [$group, $user] = $this->participant(60);
        foreach (['2026-08-06' => 60, '2026-08-07' => 60.1] as $date => $weight) {
            UserDaily::create([
                'groups_id' => $group->id,
                'users_id' => $user->id,
                'date' => $date,
                'peso' => $weight,
            ]);
        }

        $this->get(route('groups.participants.show', [$group, $user]))
            ->assertSee('Faltam para a meta')->assertSee('0,1 kg')
            ->assertDontSee('Em manutenção');
    }

    private function participant(?float $goal): array
    {
        $group = Group::create([
            'name' => 'Grupo de teste',
            'start_date' => '2026-08-03',
            'end_date' => '2026-08-30',
        ]);
        $user = User::create(['name' => 'Participante de teste']);
        UserGroup::create(['groups_id' => $group->id, 'users_id' => $user->id]);
        if ($goal !== null) {
            UserAdditionals::create([
                'groups_id' => $group->id,
                'users_id' => $user->id,
                'peso_inicial' => 70,
                'meta_peso' => $goal,
            ]);
        }

        return [$group, $user];
    }
}
