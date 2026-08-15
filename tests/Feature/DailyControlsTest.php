<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\User;
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
        'desafio' => '0',
        'balanca' => null,
        'cafe_da_manha' => null,
        'ceia' => null,
        'cha_tarde' => null,
        'almoco' => null,
        'ceia_tarde' => null,
        'cha_noite' => null,
        'jantar' => null,
        'ceia_noite' => null,
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
            'desafio' => 0,
        ]);
        $daily = UserDaily::firstOrFail();
        $this->assertTrue($daily->check_in);
        $this->assertFalse($daily->desafio);
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
