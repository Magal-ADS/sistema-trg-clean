@extends('layouts.app')

@section('content')
    <div class="mb-4">
        <a href="{{ route('products.index') }}" class="inline-flex items-center rounded-md border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 hover:border-brand-secondary hover:text-brand-primary">
            Voltar
        </a>
    </div>

    <article class="grid gap-6 md:grid-cols-2">
        <div class="overflow-hidden rounded-lg border border-slate-200 bg-white">
            @if($product->image_url)
                <img src="{{ $product->image_url }}" alt="{{ $product->name }}" decoding="async" class="aspect-square w-full object-cover">
            @else
                <div class="flex aspect-square items-center justify-center text-slate-400">Sem imagem</div>
            @endif
        </div>

        <div>
            <p class="text-sm font-semibold text-brand-secondary">{{ $product->category?->name }}</p>
            <h1 class="mt-2 text-3xl font-bold">{{ $product->name }}</h1>
            <div class="mt-4">
                @if($product->promotional_price)
                    <p class="text-sm text-slate-400 line-through">R$ {{ number_format((float) $product->price, 2, ',', '.') }}</p>
                    <p class="text-3xl font-bold text-brand-primary">R$ {{ number_format((float) $product->promotional_price, 2, ',', '.') }}</p>
                @else
                    <p class="text-3xl font-bold text-brand-primary">R$ {{ number_format((float) $product->price, 2, ',', '.') }}</p>
                @endif
            </div>
            <p class="mt-5 whitespace-pre-line text-sm leading-6 text-slate-600">{{ $product->description }}</p>

            <form action="{{ route('cart.store', $product) }}" method="POST" class="mt-6 rounded-lg border border-slate-200 bg-white p-4">
                @csrf

                @if($product->variationPrices->isNotEmpty())
                    @php
                        $variationFields = [
                            'size' => $product->variationPrices->contains(fn ($variation) => $variation->size),
                            'fragrance' => $product->variationPrices->contains(fn ($variation) => $variation->fragranceType),
                            'color' => $product->variationPrices->contains(fn ($variation) => $variation->colorType),
                        ];
                        $variationFieldLabels = ['size' => 'Tamanho', 'fragrance' => 'Fragrância', 'color' => 'Cor'];
                        $activeVariationFields = collect($variationFields)->filter()->keys();
                        $variationSelectorLabel = $activeVariationFields->count() === 1
                            ? $variationFieldLabels[$activeVariationFields->first()]
                            : 'Variação';
                    @endphp
                    <div>
                        <label for="variation_id" class="text-sm font-semibold text-slate-800">{{ $variationSelectorLabel }}</label>
                        <select
                            id="variation_id"
                            name="variation_id"
                            required
                            class="mt-2 h-11 w-full rounded-md border border-slate-300 bg-white px-3 text-sm outline-none focus:border-brand-secondary focus:ring-2 focus:ring-brand-secondary-soft"
                        >
                            <option value="">Selecione {{ Str::lower($variationSelectorLabel === 'Variação' ? 'uma variação' : 'o '.$variationSelectorLabel) }}</option>
                            @foreach($product->variationPrices as $variation)
                                @php
                                    $variationLabel = collect([$variation->size?->name, $variation->fragranceType?->name, $variation->colorType?->name])->filter()->join(' / ');
                                    $variationPrice = (float) ($variation->promotional_price ?: $variation->price);
                                @endphp
                                <option value="{{ $variation->id }}" @selected(old('variation_id') == $variation->id)>
                                    {{ $variationLabel }} - R$ {{ number_format($variationPrice, 2, ',', '.') }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="mt-4">
                    <label for="quantity" class="text-sm font-semibold text-slate-800">Quantidade</label>
                    <input
                        id="quantity"
                        name="quantity"
                        type="number"
                        min="1"
                        max="999"
                        value="{{ old('quantity', 1) }}"
                        required
                        class="mt-2 h-11 w-28 rounded-md border border-slate-300 bg-white px-3 text-sm outline-none focus:border-brand-secondary focus:ring-2 focus:ring-brand-secondary-soft"
                    >
                </div>

                <div class="mt-5 flex flex-col gap-3 sm:flex-row">
                    <button type="submit" class="inline-flex h-11 items-center justify-center rounded-md bg-brand-primary px-5 text-sm font-bold text-white hover:bg-brand-secondary">
                        Adicionar ao carrinho
                    </button>
                    <a href="{{ route('cart.index') }}" class="inline-flex h-11 items-center justify-center rounded-md border border-slate-200 bg-white px-5 text-sm font-semibold text-slate-700 hover:border-brand-secondary hover:text-brand-primary">
                        Ver carrinho
                    </a>
                </div>
            </form>

            @if($product->variationPrices->isNotEmpty())
                <div class="mt-4 rounded-lg border border-slate-200 bg-white p-4">
                    <h2 class="font-semibold">Variacoes disponiveis</h2>
                    <div class="mt-3 space-y-2">
                        @foreach($product->variationPrices as $variation)
                            <div class="flex items-center justify-between gap-4 rounded-md bg-slate-50 px-3 py-2 text-sm">
                                <span>{{ collect([$variation->size?->name, $variation->fragranceType?->name, $variation->colorType?->name])->filter()->join(' / ') }}</span>
                                <strong class="shrink-0">R$ {{ number_format((float) ($variation->promotional_price ?: $variation->price), 2, ',', '.') }}</strong>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </article>
@endsection
