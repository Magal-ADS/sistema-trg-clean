@extends('layouts.app')

@section('content')
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-bold">Admins</h1>
            <p class="mt-1 text-sm text-slate-500">Cadastre quem pode gerenciar vendedores e lancamentos.</p>
        </div>
        <div class="flex flex-col gap-2 sm:flex-row">
            <a href="{{ route('launches.admin.dashboard') }}" class="inline-flex h-10 items-center justify-center rounded-md border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 hover:border-brand-secondary">Voltar aos modulos</a>
            <a href="{{ route('launches.admin.admins.create') }}" class="inline-flex h-10 items-center justify-center rounded-md bg-brand-primary px-4 text-sm font-bold text-white hover:bg-brand-secondary">Novo admin</a>
        </div>
    </div>

    <div class="mt-6 overflow-hidden rounded-lg border border-slate-200 bg-white">
        @forelse($admins as $admin)
            <div class="flex flex-col gap-3 border-b border-slate-100 p-4 last:border-b-0 md:flex-row md:items-center md:justify-between">
                <div>
                    <h2 class="font-bold">{{ $admin->name }}</h2>
                    <p class="text-sm text-slate-500">{{ $admin->email }}</p>
                    <p class="mt-1 text-xs font-semibold {{ $admin->is_active ? 'text-emerald-600' : 'text-red-600' }}">
                        {{ $admin->is_active ? 'Ativo' : 'Inativo' }}{{ $currentAdminId === $admin->id ? ' | Voce' : '' }}
                    </p>
                </div>
                <a href="{{ route('launches.admin.admins.edit', $admin) }}" class="inline-flex h-10 items-center justify-center rounded-md border border-slate-200 px-4 text-sm font-semibold text-slate-700 hover:border-brand-secondary">Editar</a>
            </div>
        @empty
            <div class="p-6 text-sm text-slate-500">Nenhum admin cadastrado.</div>
        @endforelse
    </div>

    <div class="mt-5">{{ $admins->links() }}</div>
@endsection
