@extends('layouts.app')

@section('content')
    <article class="grid gap-6 md:grid-cols-2">
        <div class="overflow-hidden rounded-lg border border-slate-200 bg-white">
            @if($product->image_url)
                <img src="{{ $product->image_url }}" alt="{{ $product->name }}" decoding="async" class="aspect-square w-full object-cover">
            @else
                <div class="flex aspect-square items-center justify-center text-slate-400">Sem imagem</div>
            @endif
        </div>

        <div>
            <p class="text-sm font-semibold text-green-700">{{ $product->category?->name }}</p>
            <h1 class="mt-2 text-3xl font-bold">{{ $product->name }}</h1>
            <div class="mt-4">
                @if($product->promotional_price)
                    <p class="text-sm text-slate-400 line-through">R$ {{ number_format((float) $product->price, 2, ',', '.') }}</p>
                    <p class="text-3xl font-bold text-green-700">R$ {{ number_format((float) $product->promotional_price, 2, ',', '.') }}</p>
                @else
                    <p class="text-3xl font-bold text-green-700">R$ {{ number_format((float) $product->price, 2, ',', '.') }}</p>
                @endif
            </div>
            <p class="mt-5 whitespace-pre-line text-sm leading-6 text-slate-600">{{ $product->description }}</p>

            @if($product->variationPrices->isNotEmpty())
                <div class="mt-6 rounded-lg border border-slate-200 bg-white p-4">
                    <h2 class="font-semibold">Variacoes</h2>
                    <div class="mt-3 space-y-2">
                        @foreach($product->variationPrices as $variation)
                            <div class="flex items-center justify-between rounded-md bg-slate-50 px-3 py-2 text-sm">
                                <span>{{ collect([$variation->size?->name, $variation->fragranceType?->name, $variation->colorType?->name])->filter()->join(' / ') ?: 'Opcao' }}</span>
                                <strong>R$ {{ number_format((float) ($variation->promotional_price ?: $variation->price), 2, ',', '.') }}</strong>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </article>
@endsection
