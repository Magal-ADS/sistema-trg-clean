@extends('layouts.app')

@section('content')
    <h1 class="text-2xl font-bold">Carrinho</h1>
    <div class="mt-5 overflow-hidden rounded-lg border border-slate-200 bg-white">
        @forelse($items as $item)
            <div class="flex items-center justify-between gap-4 border-b border-slate-100 p-4 last:border-b-0">
                <div>
                    <h2 class="font-semibold">{{ $item->product_name }}</h2>
                    <p class="text-sm text-slate-500">{{ collect([$item->size, $item->color, $item->fragrance])->filter()->join(' / ') }}</p>
                </div>
                <div class="text-right">
                    <p class="text-sm text-slate-500">{{ $item->quantity }} x R$ {{ number_format((float) $item->unit_price, 2, ',', '.') }}</p>
                    <p class="font-bold text-green-700">R$ {{ number_format((float) $item->total, 2, ',', '.') }}</p>
                </div>
            </div>
        @empty
            <div class="p-6 text-sm text-slate-500">Nenhum item no carrinho.</div>
        @endforelse
    </div>
    <div class="mt-6">{{ $items->links() }}</div>
@endsection
