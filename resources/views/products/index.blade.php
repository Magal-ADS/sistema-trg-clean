@extends('layouts.app')

@section('content')
    <div class="mb-5 flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
        <div>
            <h1 class="text-2xl font-bold">Produtos</h1>
            <p class="mt-1 text-sm text-slate-500">{{ $products->total() }} itens encontrados</p>
        </div>
        <div class="flex gap-2 overflow-x-auto pb-1">
            <a href="{{ route('products.index') }}" class="shrink-0 rounded-md border border-slate-200 bg-white px-3 py-2 text-sm font-semibold">Todos</a>
            @foreach($categories as $category)
                <a href="{{ route('products.index', ['category' => $category->slug]) }}" class="shrink-0 rounded-md border border-slate-200 bg-white px-3 py-2 text-sm font-semibold">{{ $category->name }}</a>
            @endforeach
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4 md:grid-cols-4 lg:grid-cols-6">
        @forelse($products as $product)
            @include('components.product-card', ['product' => $product])
        @empty
            <div class="col-span-full rounded-lg border border-slate-200 bg-white p-6 text-sm text-slate-500">Nenhum produto encontrado.</div>
        @endforelse
    </div>

    <div class="mt-6">{{ $products->links() }}</div>
@endsection
