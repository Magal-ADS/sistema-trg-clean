@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-4xl">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold">Meus pedidos</h1>
                <p class="mt-1 text-sm text-slate-500">Acompanhe os pedidos feitos no site usando os dados informados na compra.</p>
            </div>
            <a href="{{ route('products.index') }}" class="inline-flex h-10 items-center justify-center rounded-md border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 hover:border-brand-secondary">
                Comprar novamente
            </a>
        </div>

        <form action="{{ route('orders.index') }}" method="GET" class="mt-5 rounded-lg border border-slate-200 bg-white p-4">
            <div class="grid gap-3 sm:grid-cols-[1fr_1fr_auto] sm:items-end">
                <div>
                    <label for="customer_cpf" class="text-sm font-semibold text-slate-800">CPF</label>
                    <input
                        id="customer_cpf"
                        name="customer_cpf"
                        value="{{ $customerCpf }}"
                        required
                        inputmode="numeric"
                        maxlength="14"
                        data-cpf-mask
                        class="mt-2 h-11 w-full rounded-md border border-slate-300 px-3 text-sm outline-none focus:border-brand-secondary"
                    >
                </div>
                <div>
                    <label for="customer_phone" class="text-sm font-semibold text-slate-800">Telefone com DDD</label>
                    <input
                        id="customer_phone"
                        name="customer_phone"
                        value="{{ $customerPhone }}"
                        required
                        inputmode="tel"
                        maxlength="15"
                        data-phone-mask
                        class="mt-2 h-11 w-full rounded-md border border-slate-300 px-3 text-sm outline-none focus:border-brand-secondary"
                    >
                </div>
                <button class="h-11 rounded-md bg-brand-primary px-5 text-sm font-bold text-white hover:bg-brand-secondary">Buscar</button>
            </div>
        </form>

        @if($searched)
            <div class="mt-5 space-y-4">
                @forelse($orders as $order)
                    @php
                        $status = $statusLabels[$order->status] ?? $order->status;
                    @endphp

                    <article class="overflow-hidden rounded-lg border border-slate-200 bg-white">
                        <div class="grid gap-3 border-b border-slate-100 p-4 sm:grid-cols-[1fr_auto] sm:items-start">
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <h2 class="text-lg font-bold">Pedido {{ $order->code }}</h2>
                                    <span class="rounded-full bg-brand-secondary-soft px-3 py-1 text-xs font-bold text-brand-primary">{{ $status }}</span>
                                </div>
                                <p class="mt-1 text-sm text-slate-500">
                                    {{ $order->confirmed_at?->format('d/m/Y H:i') ?: $order->created_at?->format('d/m/Y H:i') }}
                                    @if($order->delivery_type)
                                        - {{ $order->delivery_type }}
                                    @endif
                                    @if($order->payment_method)
                                        - {{ $order->payment_method }}
                                    @endif
                                </p>
                                <p class="mt-1 text-sm text-slate-500">
                                    {{ $order->city?->name ?: data_get($order->metadata, 'customer.city') }}
                                    @if($order->address)
                                        - {{ $order->address }}
                                    @endif
                                </p>
                            </div>
                            <strong class="text-xl text-brand-primary">R$ {{ number_format((float) $order->total, 2, ',', '.') }}</strong>
                        </div>

                        <div class="divide-y divide-slate-100 px-4">
                            @foreach($order->items as $item)
                                <div class="flex items-start justify-between gap-4 py-3 text-sm">
                                    <div>
                                        <p class="font-semibold">{{ $item->product_name }}</p>
                                        <p class="text-xs text-slate-500">{{ collect([$item->size, $item->color, $item->fragrance])->filter()->join(' / ') }}</p>
                                    </div>
                                    <p class="shrink-0 text-right text-slate-600">
                                        {{ $item->quantity }} x R$ {{ number_format((float) $item->unit_price, 2, ',', '.') }}
                                    </p>
                                </div>
                            @endforeach
                        </div>
                    </article>
                @empty
                    <div class="rounded-lg border border-slate-200 bg-white p-6 text-sm text-slate-500">
                        Nenhum pedido encontrado para estes dados.
                    </div>
                @endforelse
            </div>

            @if(method_exists($orders, 'links'))
                <div class="mt-5">{{ $orders->links() }}</div>
            @endif
        @endif
    </div>
@endsection
