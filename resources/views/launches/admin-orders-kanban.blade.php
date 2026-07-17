@extends('layouts.app')

@section('content')
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-bold">Kanban de pedidos</h1>
            <p class="mt-1 text-sm text-slate-500">Arraste os pedidos entre as colunas para atualizar o status.</p>
        </div>
        <div class="flex flex-col gap-2 sm:flex-row">
            <a href="{{ route('launches.admin.dashboard') }}" class="inline-flex h-10 items-center justify-center rounded-md border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700">Voltar</a>
            <a href="{{ route('launches.admin.modules.index', 'orders') }}" class="inline-flex h-10 items-center justify-center rounded-md bg-brand-primary px-4 text-sm font-bold text-white">Visualizar lista</a>
        </div>
    </div>

    <form action="{{ route('launches.admin.modules.index', 'orders') }}" method="GET" class="mt-5 flex flex-col gap-3 rounded-lg border border-slate-200 bg-white p-4 sm:flex-row">
        <input type="hidden" name="view" value="kanban">
        <input name="search" value="{{ $search }}" placeholder="Buscar pedido ou cliente" class="h-10 flex-1 rounded-md border border-slate-300 px-3 text-sm outline-none focus:border-brand-secondary">
        <button class="h-10 rounded-md bg-brand-primary px-4 text-sm font-bold text-white">Buscar</button>
    </form>

    <div class="mt-5">
        @include('launches.partials.order-kanban', [
            'orders' => $orders,
            'statusLabels' => $statusLabels,
            'statusRouteName' => 'launches.admin.orders.status',
            'showCity' => true,
            'showEdit' => true,
        ])
    </div>
@endsection
