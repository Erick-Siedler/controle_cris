<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserAdditionals;
use App\Models\UserDaily;
use App\Models\UserGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function index()
    {
        $users = User::orderBy('name')->get();

        return response()->json($users);
    }

    public function create()
    {
        return redirect()->route('groups.create');
    }

    public function store(Request $request)
    {
        $user = $this->createUser($request);

        return back()->with(
            'success',
            "Usuário {$user->name} criado com sucesso."
        );
    }

    public function storeByGroup(Request $request)
    {
        $user = $this->createUser($request);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Usuário criado e selecionado para o grupo.',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                ],
            ], 201);
        }

        return back()
            ->with('success', 'Usuário criado com sucesso.')
            ->with('created_user_id', $user->id);
    }

    public function show(User $user)
    {
        return response()->json($user);
    }

    public function edit(User $user)
    {
        return response()->json($user);
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'min:3', 'max:255'],
        ]);

        $user->update($data);

        return back()->with('success', 'Usuário atualizado com sucesso.');
    }

    public function destroy(User $user)
    {
        $user->delete();

        return back()->with('success', 'Usuário excluído com sucesso.');
    }

    public function storeAdditionals(Request $request)
    {
        $data = $this->validateAdditionals($request);
        $this->ensureUserBelongsToGroup(
            $data['users_id'],
            $data['groups_id']
        );

        UserAdditionals::updateOrCreate(
            [
                'users_id' => $data['users_id'],
                'groups_id' => $data['groups_id'],
            ],
            [
                'peso_inicial' => $data['peso_inicial'],
                'meta_peso' => $data['meta_peso'],
                'semana_bonus' => $data['semana_bonus'],
            ]
        );

        return back()->with('success', 'Adicionais do usuário salvos.');
    }

    public function storeAdditionalsBatch(Request $request)
    {
        $data = $request->validate([
            'groups_id' => ['required', 'exists:groups,id'],
            'additionals' => ['required', 'array', 'min:1'],
            'additionals.*.users_id' => [
                'required',
                'distinct',
                'exists:users,id',
            ],
            'additionals.*.peso_inicial' => [
                'required',
                'numeric',
                'min:1',
                'max:500',
            ],
            'additionals.*.meta_peso' => [
                'required',
                'numeric',
                'min:1',
                'max:500',
            ],
            'additionals.*.semana_bonus' => [
                'required',
                'boolean',
            ],
        ]);

        DB::transaction(function () use ($data) {
            foreach ($data['additionals'] as $additional) {
                $this->ensureUserBelongsToGroup(
                    $additional['users_id'],
                    $data['groups_id']
                );

                UserAdditionals::updateOrCreate(
                    [
                        'users_id' => $additional['users_id'],
                        'groups_id' => $data['groups_id'],
                    ],
                    [
                        'peso_inicial' => $additional['peso_inicial'],
                        'meta_peso' => $additional['meta_peso'],
                        'semana_bonus' => $additional['semana_bonus'],
                    ]
                );
            }
        });

        return back()->with(
            'success',
            'Adicionais dos usuários salvos com sucesso.'
        );
    }

    public function updateAdditionals(Request $request)
    {
        $data = $this->validateAdditionals($request);
        $this->ensureUserBelongsToGroup(
            $data['users_id'],
            $data['groups_id']
        );

        $additionalId = $request->validate([
            'additionals_id' => [
                'required',
                'exists:user_additionals,id',
            ],
        ])['additionals_id'];
        $additional = UserAdditionals::findOrFail($additionalId);

        abort_unless(
            (int) $additional->users_id === (int) $data['users_id']
                && (int) $additional->groups_id === (int) $data['groups_id'],
            404
        );

        $additional->update($data);

        return back()->with('success', 'Adicionais do usuário atualizados.');
    }

    public function updateDaily(Request $request)
    {
        $data = $request->validate([
            'users_id' => ['required', 'exists:users,id'],
            'groups_id' => ['required', 'exists:groups,id'],
            'date' => ['required', 'date', 'date_equals:today'],
            'peso' => ['nullable', 'numeric', 'min:1', 'max:500'],
            'check_in' => ['required', 'boolean'],
            'desafio' => ['required', 'boolean'],
            'balanca' => ['required', 'boolean'],
            'cafe_da_manha' => ['required', 'boolean'],
            'ceia' => ['required', 'boolean'],
            'cha_tarde' => ['required', 'boolean'],
            'almoco' => ['required', 'boolean'],
            'ceia_tarde' => ['required', 'boolean'],
            'cha_noite' => ['required', 'boolean'],
            'jantar' => ['required', 'boolean'],
            'ceia_noite' => ['required', 'boolean'],
            'check_out' => ['required', 'boolean'],
        ]);
        $this->ensureUserBelongsToGroup(
            $data['users_id'],
            $data['groups_id']
        );

        UserDaily::updateOrCreate(
            [
                'users_id' => $data['users_id'],
                'groups_id' => $data['groups_id'],
                'date' => $data['date'],
            ],
            $data
        );

        return back()->with('success', 'Daily de hoje salvo com sucesso.');
    }

    private function createUser(Request $request): User
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'min:3', 'max:255'],
        ]);

        return User::create($data);
    }

    private function validateAdditionals(Request $request): array
    {
        return $request->validate([
            'users_id' => ['required', 'exists:users,id'],
            'groups_id' => ['required', 'exists:groups,id'],
            'peso_inicial' => [
                'required',
                'numeric',
                'min:1',
                'max:500',
            ],
            'meta_peso' => [
                'required',
                'numeric',
                'min:1',
                'max:500',
            ],
            'semana_bonus' => ['required', 'boolean'],
        ]);
    }

    private function ensureUserBelongsToGroup(
        int $userId,
        int $groupId
    ): void {
        $belongsToGroup = UserGroup::where([
            'users_id' => $userId,
            'groups_id' => $groupId,
        ])->exists();

        abort_unless($belongsToGroup, 422);
    }
}
