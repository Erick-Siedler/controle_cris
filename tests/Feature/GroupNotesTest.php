<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\GroupNote;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GroupNotesTest extends TestCase
{
    use RefreshDatabase;

    public function test_note_is_created_for_a_group_and_date(): void
    {
        $group = $this->group();

        $response = $this->post(route('groups.notes.store', $group), [
            'date' => '2026-08-12',
            'content' => 'Confirmar a pesagem da tarde.',
            'color' => 'pink',
        ]);

        $response->assertRedirect(route('groups.scope', [
            'group' => $group,
            'date' => '2026-08-12',
        ]));
        $this->assertDatabaseHas('group_notes', [
            'groups_id' => $group->id,
            'date' => '2026-08-12 00:00:00',
            'content' => 'Confirmar a pesagem da tarde.',
            'color' => 'pink',
        ]);
    }

    public function test_scope_only_shows_notes_from_the_selected_group_day(): void
    {
        $group = $this->group();
        $otherGroup = $this->group('Outro grupo');
        $visible = GroupNote::create([
            'groups_id' => $group->id,
            'date' => '2026-08-12',
            'content' => 'Anotação visível',
            'color' => 'yellow',
        ]);
        GroupNote::create([
            'groups_id' => $group->id,
            'date' => '2026-08-13',
            'content' => 'Outro dia',
            'color' => 'blue',
        ]);
        GroupNote::create([
            'groups_id' => $otherGroup->id,
            'date' => '2026-08-12',
            'content' => 'Outro grupo',
            'color' => 'green',
        ]);

        $response = $this->get(route('groups.scope', [
            'group' => $group,
            'date' => '2026-08-12',
        ]));

        $response->assertOk();
        $this->assertTrue($response->viewData('notes')->contains($visible));
        $this->assertCount(1, $response->viewData('notes'));
        $response->assertSee('Anotação visível');
        $response->assertDontSee('Outro dia');
        $response->assertDontSee('Outro grupo');
    }

    public function test_note_position_and_pin_are_persisted(): void
    {
        $group = $this->group();
        $note = GroupNote::create([
            'groups_id' => $group->id,
            'date' => '2026-08-12',
            'content' => 'Mover este post-it',
            'color' => 'yellow',
        ]);

        $response = $this->patchJson(
            route('groups.notes.update', [$group, $note]),
            [
                'position_x' => 42.5,
                'position_y' => 31.25,
                'z_index' => 8,
                'is_pinned' => true,
            ]
        );

        $response->assertOk();
        $this->assertDatabaseHas('group_notes', [
            'id' => $note->id,
            'position_x' => 42.5,
            'position_y' => 31.25,
            'z_index' => 8,
            'is_pinned' => 1,
        ]);
    }

    public function test_note_cannot_be_changed_through_another_group(): void
    {
        $group = $this->group();
        $otherGroup = $this->group('Outro grupo');
        $note = GroupNote::create([
            'groups_id' => $group->id,
            'date' => '2026-08-12',
            'content' => 'Anotação protegida',
            'color' => 'yellow',
        ]);

        $response = $this->patchJson(
            route('groups.notes.update', [$otherGroup, $note]),
            ['content' => 'Conteúdo indevido']
        );

        $response->assertNotFound();
        $this->assertSame('Anotação protegida', $note->fresh()->content);
    }

    private function group(string $name = 'Grupo de teste'): Group
    {
        return Group::create([
            'name' => $name,
            'start_date' => '2026-08-03',
            'end_date' => '2026-08-30',
        ]);
    }
}
