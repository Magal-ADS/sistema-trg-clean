@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-2xl">
        <div class="mb-5 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold">{{ $admin->exists ? 'Editar admin' : 'Novo admin' }}</h1>
                <p class="mt-1 text-sm text-slate-500">Altere os dados de acesso administrativo dos lancamentos.</p>
            </div>
            <a href="{{ route('launches.admin.admins') }}" class="inline-flex h-10 items-center justify-center rounded-md border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 hover:border-brand-secondary">Voltar para admins</a>
        </div>

        <form action="{{ $admin->exists ? route('launches.admin.admins.update', $admin) : route('launches.admin.admins.store') }}" method="POST" class="space-y-4 rounded-lg border border-slate-200 bg-white p-5">
            @csrf
            @if($admin->exists)
                @method('PUT')
            @endif

            <div>
                <label for="name" class="text-sm font-semibold text-slate-800">Nome</label>
                <input id="name" name="name" value="{{ old('name', $admin->name) }}" required class="mt-2 h-11 w-full rounded-md border border-slate-300 px-3 text-sm outline-none focus:border-brand-secondary">
            </div>

            <div>
                <label for="email" class="text-sm font-semibold text-slate-800">E-mail</label>
                <input id="email" name="email" type="email" value="{{ old('email', $admin->email) }}" required class="mt-2 h-11 w-full rounded-md border border-slate-300 px-3 text-sm outline-none focus:border-brand-secondary">
            </div>

            <div>
                <label for="password" class="text-sm font-semibold text-slate-800">Senha</label>
                <input id="password" name="password" type="password" @required(! $admin->exists) class="mt-2 h-11 w-full rounded-md border border-slate-300 px-3 text-sm outline-none focus:border-brand-secondary">
                @error('password')
                    <p class="mt-1 text-sm font-medium text-red-700">{{ $message }}</p>
                @enderror
                @if($admin->exists)
                    <p class="mt-1 text-xs text-slate-500">Deixe em branco para manter a senha atual. Se preencher, use pelo menos 4 caracteres.</p>
                @else
                    <p class="mt-1 text-xs text-slate-500">Use pelo menos 4 caracteres.</p>
                @endif
            </div>

            <input type="hidden" name="is_active" value="0">
            <label class="flex items-center gap-2 text-sm font-semibold text-slate-800">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $admin->exists ? $admin->is_active : true)) @disabled(($isCurrentAdmin ?? false)) class="rounded border-slate-300">
                Admin ativo
            </label>
            @if($isCurrentAdmin ?? false)
                <p class="-mt-2 text-xs text-slate-500">Seu proprio acesso permanece ativo para evitar bloqueio da conta em uso.</p>
            @endif

            <div class="flex flex-col gap-3 sm:flex-row">
                <button class="h-11 rounded-md bg-brand-primary px-5 text-sm font-bold text-white hover:bg-brand-secondary">
                    Salvar
                </button>
                <a href="{{ route('launches.admin.admins') }}" class="inline-flex h-11 items-center justify-center rounded-md border border-slate-200 bg-white px-5 text-sm font-semibold text-slate-700 hover:border-brand-secondary">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
@endsection
