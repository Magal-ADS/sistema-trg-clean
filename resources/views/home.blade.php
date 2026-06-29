@extends('layouts.app')

@section('content')
    <section class="grid gap-4 md:grid-cols-[1.5fr_1fr]">
        <div class="rounded-lg bg-green-700 p-6 text-white md:p-10">
            <p class="text-sm font-semibold uppercase tracking-wide text-green-100">Catalogo TRG Clean</p>
            <h1 class="mt-3 max-w-2xl text-3xl font-bold md:text-5xl">Produtos de limpeza para comprar com rapidez.</h1>
            <p class="mt-4 max-w-xl text-green-50">Encontre itens, consulte variacoes e acompanhe pedidos em uma experiencia instalada no celular.</p>
            <a href="{{ route('products.index') }}" class="mt-6 inline-flex h-11 items-center rounded-md bg-white px-5 text-sm font-bold text-green-700">Ver produtos</a>
        </div>

        <div class="grid gap-4">
            @forelse($banners as $banner)
                <a href="{{ $banner->link_url ?: route('products.index') }}" class="overflow-hidden rounded-lg border border-slate-200 bg-white">
                    @if($banner->image_url)
                        <img src="{{ $banner->image_url }}" alt="{{ $banner->title }}" loading="lazy" decoding="async" class="h-36 w-full object-cover">
                    @endif
                    <div class="p-4">
                        <h2 class="font-semibold">{{ $banner->title }}</h2>
                        <p class="text-sm text-slate-500">{{ $banner->subtitle }}</p>
                    </div>
                </a>
            @empty
                <div class="rounded-lg border border-slate-200 bg-white p-5">
                    <h2 class="font-semibold">Banners da home</h2>
                    <p class="mt-1 text-sm text-slate-500">Importe os CSVs para popular campanhas e destaques.</p>
                </div>
            @endforelse
        </div>
    </section>

    <section class="mt-8">
        <div class="mb-4 flex items-center justify-between">
            <h2 class="text-xl font-bold">Categorias</h2>
            <a href="{{ route('products.index') }}" class="text-sm font-semibold text-green-700">Ver tudo</a>
        </div>
        <div class="flex gap-3 overflow-x-auto pb-2">
            @forelse($categories as $category)
                <a href="{{ route('products.index', ['category' => $category->slug]) }}" class="shrink-0 rounded-md border border-slate-200 bg-white px-4 py-3 text-sm font-semibold">
                    {{ $category->name }}
                </a>
            @empty
                <div class="rounded-md border border-slate-200 bg-white px-4 py-3 text-sm text-slate-500">Nenhuma categoria importada.</div>
            @endforelse
        </div>
    </section>

    <section class="mt-8">
        <div class="mb-4 flex items-center justify-between">
            <h2 class="text-xl font-bold">Produtos em destaque</h2>
            <a href="{{ route('products.index') }}" class="text-sm font-semibold text-green-700">Abrir catalogo</a>
        </div>
        <div class="grid grid-cols-2 gap-4 md:grid-cols-4 lg:grid-cols-6">
            @forelse($featuredProducts as $product)
                @include('components.product-card', ['product' => $product])
            @empty
                <div class="col-span-full rounded-lg border border-slate-200 bg-white p-6 text-sm text-slate-500">Importe os produtos para montar a vitrine.</div>
            @endforelse
        </div>
    </section>
@endsection
