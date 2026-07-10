@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-2xl">
        <div class="mb-5 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold">{{ $city->exists ? 'Editar cidade' : 'Nova cidade' }}</h1>
                <p class="mt-1 text-sm text-slate-500">Essa lista alimenta os vendedores e o fechamento do carrinho.</p>
            </div>
            <a href="{{ route('launches.admin.cities') }}" class="inline-flex h-10 items-center justify-center rounded-md border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 hover:border-brand-secondary">Voltar para cidades</a>
        </div>

        <form action="{{ $city->exists ? route('launches.admin.cities.update', $city) : route('launches.admin.cities.store') }}" method="POST" class="space-y-4 rounded-lg border border-slate-200 bg-white p-5">
            @csrf
            @if($city->exists)
                @method('PUT')
            @endif

            <div>
                <label for="name" class="text-sm font-semibold text-slate-800">Cidade</label>
                <input id="name" name="name" value="{{ old('name', $city->name) }}" required class="mt-2 h-11 w-full rounded-md border border-slate-300 px-3 text-sm outline-none focus:border-brand-secondary">
            </div>

            <div>
                <label for="state" class="text-sm font-semibold text-slate-800">Estado</label>
                <input id="state" name="state" value="{{ old('state', $city->state) }}" maxlength="50" placeholder="SP" class="mt-2 h-11 w-full rounded-md border border-slate-300 px-3 text-sm outline-none focus:border-brand-secondary">
            </div>

            <input type="hidden" name="is_active" value="0">
            <label class="flex items-center gap-2 text-sm font-semibold text-slate-800">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $city->exists ? $city->is_active : true)) class="rounded border-slate-300">
                Cidade ativa
            </label>

            <div class="flex flex-col gap-3 sm:flex-row">
                <button class="h-11 rounded-md bg-brand-primary px-5 text-sm font-bold text-white hover:bg-brand-secondary">
                    Salvar
                </button>
                <a href="{{ route('launches.admin.cities') }}" class="inline-flex h-11 items-center justify-center rounded-md border border-slate-200 bg-white px-5 text-sm font-semibold text-slate-700 hover:border-brand-secondary">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
@endsection
