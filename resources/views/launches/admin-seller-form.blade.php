@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-2xl">
        <div class="mb-5 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold">{{ $seller->exists ? 'Editar vendedora' : 'Nova vendedora' }}</h1>
                <p class="mt-1 text-sm text-slate-500">Altere os dados de acesso usados no app de lancamentos.</p>
            </div>
            <a href="{{ route('launches.admin.sellers') }}" class="inline-flex h-10 items-center justify-center rounded-md border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 hover:border-brand-secondary">Voltar para vendedoras</a>
        </div>

        <form action="{{ $seller->exists ? route('launches.admin.sellers.update', $seller) : route('launches.admin.sellers.store') }}" method="POST" class="space-y-4 rounded-lg border border-slate-200 bg-white p-5">
            @csrf
            @if($seller->exists)
                @method('PUT')
            @endif

            <div>
                <label for="name" class="text-sm font-semibold text-slate-800">Nome</label>
                <input id="name" name="name" value="{{ old('name', $seller->name) }}" required class="mt-2 h-11 w-full rounded-md border border-slate-300 px-3 text-sm outline-none focus:border-brand-secondary">
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label for="email" class="text-sm font-semibold text-slate-800">E-mail</label>
                    <input id="email" name="email" type="email" value="{{ old('email', $seller->email) }}" class="mt-2 h-11 w-full rounded-md border border-slate-300 px-3 text-sm outline-none focus:border-brand-secondary">
                </div>
                <div>
                    <label for="phone" class="text-sm font-semibold text-slate-800">Telefone</label>
                    <input id="phone" name="phone" value="{{ old('phone', $seller->phone) }}" class="mt-2 h-11 w-full rounded-md border border-slate-300 px-3 text-sm outline-none focus:border-brand-secondary">
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label for="city" class="text-sm font-semibold text-slate-800">Cidade</label>
                    <input id="city" name="city" value="{{ old('city', $seller->city) }}" class="mt-2 h-11 w-full rounded-md border border-slate-300 px-3 text-sm outline-none focus:border-brand-secondary">
                </div>
                <div>
                    <label for="state" class="text-sm font-semibold text-slate-800">Estado</label>
                    <input id="state" name="state" value="{{ old('state', $seller->state) }}" class="mt-2 h-11 w-full rounded-md border border-slate-300 px-3 text-sm outline-none focus:border-brand-secondary">
                </div>
            </div>

            <div>
                <label for="password" class="text-sm font-semibold text-slate-800">Senha ou PIN</label>
                <input id="password" name="password" type="password" @required(! $seller->exists) class="mt-2 h-11 w-full rounded-md border border-slate-300 px-3 text-sm outline-none focus:border-brand-secondary">
                @error('password')
                    <p class="mt-1 text-sm font-medium text-red-700">{{ $message }}</p>
                @enderror
                @if($seller->exists)
                    <p class="mt-1 text-xs text-slate-500">Deixe em branco para manter a senha atual. Se preencher, use pelo menos 4 caracteres.</p>
                @else
                    <p class="mt-1 text-xs text-slate-500">Use pelo menos 4 caracteres.</p>
                @endif
            </div>

            <input type="hidden" name="is_active" value="0">
            <label class="flex items-center gap-2 text-sm font-semibold text-slate-800">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $seller->exists ? $seller->is_active : true)) class="rounded border-slate-300">
                Vendedora ativa
            </label>

            <div class="flex flex-col gap-3 sm:flex-row">
                <button class="h-11 rounded-md bg-brand-primary px-5 text-sm font-bold text-white hover:bg-brand-secondary">
                    Salvar
                </button>
                <a href="{{ route('launches.admin.sellers') }}" class="inline-flex h-11 items-center justify-center rounded-md border border-slate-200 bg-white px-5 text-sm font-semibold text-slate-700 hover:border-brand-secondary">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
@endsection
