@extends('layouts.app')

@section('content')
    @php
        $moduleCards = [
            ['label' => 'Lançamentos', 'url' => route('launches.admin.entries.index')],
        ];

        foreach (\App\Http\Controllers\AdminCatalogController::menu() as $item) {
            $moduleCards[] = [
                'label' => $item['label'],
                'url' => route('launches.admin.modules.index', $item['module']),
            ];
        }

        $moduleCards[] = ['label' => 'Vendedores', 'url' => route('launches.admin.sellers')];
        $moduleCards[] = ['label' => 'Cidades', 'url' => route('launches.admin.cities')];
        $moduleCards[] = ['label' => 'Configurações do Catálogo', 'url' => route('launches.admin.catalog.settings')];
        $moduleCards[] = ['label' => 'Administradores', 'url' => route('launches.admin.admins')];
    @endphp

    <div>
        <div class="mb-5 flex items-center justify-between gap-3">
            <div>
                <h1 class="text-xl font-bold">Administrador</h1>
                <p class="mt-1 text-sm text-slate-500">Escolha um módulo para gerenciar.</p>
            </div>
            <a href="{{ route('home') }}" class="hidden h-10 items-center rounded-md border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 hover:border-brand-secondary sm:inline-flex">Site</a>
        </div>

        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @foreach($moduleCards as $card)
                <a href="{{ $card['url'] }}" class="flex min-h-20 items-center justify-between rounded-md border border-slate-200 bg-white px-4 py-4 text-sm font-bold text-slate-800 shadow-sm hover:border-brand-secondary hover:bg-brand-secondary-soft">
                    <span>{{ $card['label'] }}</span>
                    <span class="text-lg font-normal text-slate-400">&rsaquo;</span>
                </a>
            @endforeach
        </div>
    </div>
@endsection
