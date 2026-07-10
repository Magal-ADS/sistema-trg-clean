@extends('layouts.app')

@section('content')
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-bold">Vendedores</h1>
            <p class="mt-1 text-sm text-slate-500">Cadastre acessos para o app de lançamentos.</p>
        </div>
        <div class="flex flex-col gap-2 sm:flex-row">
            <a href="{{ route('launches.admin.dashboard') }}" class="inline-flex h-10 items-center justify-center rounded-md border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 hover:border-brand-secondary">Voltar aos modulos</a>
            <a href="{{ route('launches.admin.sellers.create') }}" class="inline-flex h-10 items-center justify-center rounded-md bg-brand-primary px-4 text-sm font-bold text-white hover:bg-brand-secondary">Novo vendedor</a>
        </div>
    </div>

    <div class="mt-6 overflow-hidden rounded-lg border border-slate-200 bg-white">
        @forelse($sellers as $seller)
            <div class="flex flex-col gap-3 border-b border-slate-100 p-4 last:border-b-0 md:flex-row md:items-center md:justify-between">
                <div>
                    <h2 class="font-bold">{{ $seller->name }}</h2>
                    <p class="text-sm text-slate-500">{{ collect([$seller->phone, $seller->email, $seller->city])->filter()->join(' | ') }}</p>
                    <p class="mt-1 text-xs font-semibold {{ $seller->is_active ? 'text-emerald-600' : 'text-red-600' }}">{{ $seller->is_active ? 'Ativo' : 'Inativo' }}</p>
                </div>
                <a href="{{ route('launches.admin.sellers.edit', $seller) }}" class="inline-flex h-10 items-center justify-center rounded-md border border-slate-200 px-4 text-sm font-semibold text-slate-700 hover:border-brand-secondary">Editar</a>
            </div>
        @empty
            <div class="p-6 text-sm text-slate-500">Nenhum vendedor cadastrado.</div>
        @endforelse
    </div>

    <div class="mt-5">{{ $sellers->links() }}</div>
@endsection
