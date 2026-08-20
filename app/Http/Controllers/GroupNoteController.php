<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\GroupNote;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class GroupNoteController extends Controller
{
    private const COLORS = ['yellow', 'pink', 'blue', 'green', 'peach'];

    public function store(Request $request, Group $group): RedirectResponse
    {
        $data = $request->validate([
            'date' => ['required', 'date', 'after_or_equal:'.$group->start_date->toDateString(), 'before_or_equal:'.$group->end_date->toDateString()],
            'content' => ['required', 'string', 'max:1000'],
            'color' => ['required', Rule::in(self::COLORS)],
        ]);
        $noteCount = GroupNote::where('groups_id', $group->id)
            ->whereDate('date', $data['date'])
            ->count();

        $group->notes()->create([
            ...$data,
            'position_x' => 4 + (($noteCount * 19) % 70),
            'position_y' => 6 + (($noteCount * 17) % 62),
            'z_index' => $noteCount + 1,
        ]);

        return redirect()
            ->route('groups.scope', ['group' => $group, 'date' => $data['date']])
            ->with('success', 'Anotação adicionada ao mural.');
    }

    public function update(Request $request, Group $group, GroupNote $note): JsonResponse
    {
        abort_unless($note->groups_id === $group->id, 404);
        $data = $request->validate([
            'content' => ['sometimes', 'required', 'string', 'max:1000'],
            'color' => ['sometimes', Rule::in(self::COLORS)],
            'position_x' => ['sometimes', 'numeric', 'between:0,82'],
            'position_y' => ['sometimes', 'numeric', 'between:0,78'],
            'is_pinned' => ['sometimes', 'boolean'],
            'z_index' => ['sometimes', 'integer', 'min:1', 'max:1000000'],
        ]);

        $note->update($data);

        return response()->json(['note' => $note->fresh()]);
    }

    public function destroy(Group $group, GroupNote $note): RedirectResponse
    {
        abort_unless($note->groups_id === $group->id, 404);
        $date = $note->date->toDateString();
        $note->delete();

        return redirect()
            ->route('groups.scope', ['group' => $group, 'date' => $date])
            ->with('success', 'Anotação removida.');
    }
}
