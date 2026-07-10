@extends('layouts.app')

@section('content')
    <div>
        <div class="mb-5 flex items-center justify-between gap-3">
            <div>
                <h1 class="text-xl font-bold">Configurações do Catálogo</h1>
                <p class="mt-1 text-sm text-slate-500">Cadastros auxiliares usados por produtos, variações e filtros.</p>
            </div>
            <a href="{{ route('launches.admin.dashboard') }}" class="inline-flex h-10 items-center rounded-md border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 hover:border-brand-secondary">Voltar</a>
        </div>

        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($settingsItems as $item)
                <a href="{{ route('launches.admin.modules.index', $item['module']) }}" class="flex min-h-16 items-center justify-between rounded-md border border-slate-200 bg-white px-4 py-4 text-sm font-bold text-slate-800 shadow-sm hover:border-brand-secondary hover:bg-brand-secondary-soft">
                    <span>{{ $item['label'] }}</span>
                    <span class="text-lg font-normal text-slate-400">&rsaquo;</span>
                </a>
            @endforeach
        </div>
    </div>
@endsection
