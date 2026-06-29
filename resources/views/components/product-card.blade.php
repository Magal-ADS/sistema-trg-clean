<article class="overflow-hidden rounded-lg border border-slate-200 bg-white">
    <a href="{{ route('products.show', $product) }}" class="block">
        <div class="aspect-square bg-slate-100">
            @if($product->image_url)
                <img src="{{ $product->image_url }}" alt="{{ $product->name }}" loading="lazy" decoding="async" class="h-full w-full object-cover">
            @else
                <div class="flex h-full items-center justify-center px-4 text-center text-sm text-slate-400">Sem imagem</div>
            @endif
        </div>
        <div class="space-y-2 p-4">
            <div class="min-h-12">
                <h3 class="line-clamp-2 text-sm font-semibold text-slate-900">{{ $product->name }}</h3>
                <p class="mt-1 text-xs text-slate-500">{{ $product->category?->name }}</p>
            </div>
            <div>
                @if($product->promotional_price)
                    <p class="text-xs text-slate-400 line-through">R$ {{ number_format((float) $product->price, 2, ',', '.') }}</p>
                    <p class="text-lg font-bold text-green-700">R$ {{ number_format((float) $product->promotional_price, 2, ',', '.') }}</p>
                @else
                    <p class="text-lg font-bold text-green-700">R$ {{ number_format((float) $product->price, 2, ',', '.') }}</p>
                @endif
            </div>
        </div>
    </a>
</article>
