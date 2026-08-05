@php
    $selected = collect(old('users', $selectedUserIds ?? []))->map(fn ($id) => (int) $id)->all();
@endphp

<form method="POST" action="{{ $action }}" class="space-y-6">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-semibold">Dados do grupo</h2>
        <div class="mt-5 grid gap-5 md:grid-cols-2">
            <label>
                <span class="mb-2 block text-sm font-medium text-slate-700">Nome</span>
                <input type="text" name="name" value="{{ old('name', $group->name ?? '') }}" required minlength="3" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 focus:border-purple-500 focus:ring-2 focus:ring-purple-100">
                @error('name') <small class="mt-1 block text-red-600">{{ $message }}</small> @enderror
            </label>
            <label>
                <span class="mb-2 block text-sm font-medium text-slate-700">Data de início</span>
                <input type="date" name="start_date" value="{{ old('start_date', isset($group) ? $group->start_date->format('Y-m-d') : '') }}" required class="w-full rounded-lg border border-slate-300 px-3 py-2.5 focus:border-purple-500 focus:ring-2 focus:ring-purple-100">
                @error('start_date') <small class="mt-1 block text-red-600">{{ $message }}</small> @enderror
                <small class="mt-1 block text-slate-500">O grupo terminará automaticamente quatro semanas após esta data.</small>
            </label>
        </div>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-start">
            <div>
                <h2 class="text-lg font-semibold">Usuários do grupo</h2>
                <p class="mt-1 text-sm text-slate-500">Selecione usuários existentes ou crie um novo.</p>
            </div>
            <button type="button" data-toggle-new-user class="rounded-lg border border-purple-200 px-4 py-2 text-sm font-semibold text-purple-700 hover:bg-purple-50">
                + Criar usuário
            </button>
        </div>

        <div data-new-user-panel class="mt-5 hidden rounded-lg border border-purple-100 bg-purple-50 p-4">
            <div class="flex flex-col gap-3 sm:flex-row">
                <input type="text" data-new-user-name minlength="3" placeholder="Nome do novo usuário" class="min-w-0 flex-1 rounded-lg border border-purple-200 bg-white px-3 py-2.5 focus:border-purple-500 focus:ring-2 focus:ring-purple-100">
                <button type="button" data-create-user data-url="{{ route('users.storeByGroup') }}" class="rounded-lg bg-purple-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-purple-800">
                    Criar e selecionar
                </button>
            </div>
            <p data-new-user-feedback class="mt-2 text-sm"></p>
        </div>

        <div data-users-list class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($users as $user)
                <label class="flex cursor-pointer items-center gap-3 rounded-lg border border-slate-200 p-3 hover:border-purple-300 hover:bg-purple-50">
                    <input type="checkbox" name="users[]" value="{{ $user->id }}" @checked(in_array($user->id, $selected, true)) class="h-4 w-4 accent-purple-700">
                    <span class="text-sm font-medium">{{ $user->name }}</span>
                </label>
            @endforeach
        </div>
        @error('users') <small class="mt-3 block text-red-600">{{ $message }}</small> @enderror
        @error('users.*') <small class="mt-3 block text-red-600">{{ $message }}</small> @enderror
    </div>

    <div class="flex justify-end gap-3">
        <a href="{{ route('groups.index') }}" class="rounded-lg border border-slate-300 px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-100">Cancelar</a>
        <button class="rounded-lg bg-purple-700 px-5 py-2.5 text-sm font-semibold text-white hover:bg-purple-800">{{ $submitLabel }}</button>
    </div>
</form>
