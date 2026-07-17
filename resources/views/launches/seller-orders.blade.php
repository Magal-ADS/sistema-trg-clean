<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0B2B54">
    <title>Pedidos da cidade - TRG Clean</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-brand-ice text-slate-950">
    <header class="sticky top-0 z-30 border-b border-slate-200 bg-white">
        <div class="mx-auto flex max-w-2xl items-center justify-between gap-3 px-4 py-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">TRG Clean</p>
                <h1 class="text-lg font-bold">Pedidos</h1>
            </div>
            <a href="{{ route('launches.index') }}" class="rounded-md border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700">Voltar</a>
        </div>
    </header>

    <main class="mx-auto max-w-2xl space-y-4 px-4 py-5">
        <section class="rounded-lg border border-slate-200 bg-white p-4">
            <p class="text-sm text-slate-500">Pedidos disponíveis para</p>
            <p class="mt-1 font-bold text-brand-primary">
                {{ $seller->cityRecord?->name ?: $seller->city ?: 'Cidade não vinculada' }}
                @if($seller->cityRecord?->state || $seller->state)
                    - {{ $seller->cityRecord?->state ?: $seller->state }}
                @endif
            </p>
        </section>

        @if(! $seller->city_id)
            <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
                Seu cadastro ainda não possui uma cidade vinculada. Entre em contato com o administrador.
            </div>
        @endif

        <section class="space-y-4">
            @forelse($orders as $order)
                @php($status = $statusLabels[$order->status] ?? $order->status)

                <article class="overflow-hidden rounded-lg border border-slate-200 bg-white">
                    <div class="border-b border-slate-100 p-4">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <h2 class="text-lg font-bold">Pedido {{ $order->code }}</h2>
                                    <span class="rounded-full bg-brand-secondary-soft px-3 py-1 text-xs font-bold text-brand-primary">{{ $status }}</span>
                                </div>
                                <p class="mt-1 text-sm text-slate-500">{{ $order->confirmed_at?->format('d/m/Y H:i') ?: $order->created_at?->format('d/m/Y H:i') }}</p>
                            </div>
                            <strong class="text-xl text-brand-primary">R$ {{ number_format((float) $order->total, 2, ',', '.') }}</strong>
                        </div>

                        <div class="mt-4 grid gap-2 rounded-md bg-slate-50 p-3 text-sm sm:grid-cols-2">
                            <p><span class="font-semibold text-slate-600">Cliente:</span> {{ $order->customer_name }}</p>
                            <p><span class="font-semibold text-slate-600">Telefone:</span> {{ $order->customer_phone ?: '-' }}</p>
                            <p><span class="font-semibold text-slate-600">Pagamento:</span> {{ $order->payment_method ?: '-' }}</p>
                            <p><span class="font-semibold text-slate-600">Entrega:</span> {{ $order->delivery_type ?: '-' }}</p>
                            <p class="sm:col-span-2"><span class="font-semibold text-slate-600">Endereço:</span> {{ $order->address ?: '-' }}{{ $order->complement ? ' - '.$order->complement : '' }}</p>
                        </div>
                    </div>

                    <div class="divide-y divide-slate-100 px-4">
                        @forelse($order->items as $item)
                            <div class="flex items-start justify-between gap-4 py-3 text-sm">
                                <div>
                                    <p class="font-semibold">{{ $item->product_name }}</p>
                                    <p class="text-xs text-slate-500">{{ collect([$item->size, $item->color, $item->fragrance])->filter()->join(' / ') }}</p>
                                </div>
                                <p class="shrink-0 text-right text-slate-600">{{ $item->quantity }} x R$ {{ number_format((float) $item->unit_price, 2, ',', '.') }}</p>
                            </div>
                        @empty
                            <p class="py-3 text-sm text-slate-500">Nenhum item vinculado ao pedido.</p>
                        @endforelse
                    </div>
                </article>
            @empty
                <div class="rounded-lg border border-slate-200 bg-white p-6 text-sm text-slate-500">
                    Nenhum pedido encontrado para a cidade do vendedor.
                </div>
            @endforelse
        </section>

        @if($orders->hasPages())
            <div>{{ $orders->links() }}</div>
        @endif
    </main>
</body>
</html>
