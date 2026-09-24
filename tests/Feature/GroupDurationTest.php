<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GroupDurationTest extends TestCase
{
    use RefreshDatabase;

    public function test_group_is_created_with_the_selected_number_of_weeks(): void
    {
        $user = User::create(['name' => 'Participante']);

        $response = $this->post(route('groups.store'), [
            'name' => 'Projeto de seis semanas',
            'start_date' => '2026-08-03',
            // Campos numéricos de formulários HTML chegam como strings.
            'duration_weeks' => '6',
            'users' => [$user->id],
        ]);

        $response->assertRedirect(route('groups.index'));
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('groups', [
            'name' => 'Projeto de seis semanas',
            'start_date' => '2026-08-03 00:00:00',
            'end_date' => '2026-09-13 00:00:00',
        ]);
    }

    public function test_duration_must_be_between_one_and_fifty_two_weeks(): void
    {
        $user = User::create(['name' => 'Participante']);

        $response = $this->post(route('groups.store'), [
            'name' => 'Projeto inválido',
            'start_date' => '2026-08-03',
            'duration_weeks' => 0,
            'users' => [$user->id],
        ]);

        $response->assertSessionHasErrors('duration_weeks');
        $this->assertDatabaseCount('groups', 0);
    }

    public function test_existing_group_can_be_extended_by_more_weeks(): void
    {
        $group = $this->group();

        $response = $this->patch(route('groups.extend', $group), [
            // Campos numéricos de formulários HTML chegam como strings.
            'additional_weeks' => '2',
        ]);

        $response->assertRedirect(route('groups.show', $group));
        $response->assertSessionHasNoErrors();
        $this->assertSame('2026-09-13', $group->fresh()->end_date->toDateString());
    }

    public function test_editing_a_group_preserves_its_existing_duration(): void
    {
        $group = $this->group();
        $user = User::create(['name' => 'Participante']);

        $response = $this->put(route('groups.update', $group), [
            'name' => 'Grupo atualizado',
            'start_date' => '2026-08-10',
            'users' => [$user->id],
        ]);

        $response->assertSessionHasNoErrors();
        $group->refresh();
        $this->assertSame('2026-08-10', $group->start_date->toDateString());
        $this->assertSame('2026-09-06', $group->end_date->toDateString());
        $this->assertSame(4, $group->durationInWeeks());
    }

    private function group(): Group
    {
        return Group::create([
            'name' => 'Grupo de teste',
            'start_date' => '2026-08-03',
            'end_date' => '2026-08-30',
        ]);
    }
}
