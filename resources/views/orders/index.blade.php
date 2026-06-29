@extends('layouts.app')

@section('content')
    <h1 class="text-2xl font-bold">Pedidos</h1>
    <div class="mt-5 overflow-hidden rounded-lg border border-slate-200 bg-white">
        @forelse($orders as $order)
            <div class="grid gap-3 border-b border-slate-100 p-4 last:border-b-0 md:grid-cols-[1fr_auto] md:items-center">
                <div>
                    <h2 class="font-semibold">Pedido {{ $order->code }}</h2>
                    <p class="text-sm text-slate-500">{{ $order->customer_name }} - {{ $order->customer_email }}</p>
                    <p class="text-xs uppercase tracking-wide text-slate-400">{{ $order->status }} {{ $order->confirmed_at?->format('d/m/Y H:i') }}</p>
                </div>
                <p class="text-lg font-bold text-brand-primary">R$ {{ number_format((float) $order->total, 2, ',', '.') }}</p>
            </div>
        @empty
            <div class="p-6 text-sm text-slate-500">Nenhum pedido importado.</div>
        @endforelse
    </div>
    <div class="mt-6">{{ $orders->links() }}</div>
@endsection
