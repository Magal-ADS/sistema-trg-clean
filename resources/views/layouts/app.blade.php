<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#16a34a">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="TRG Clean">
    <meta name="mobile-web-app-capable" content="yes">
    <link rel="manifest" href="/manifest.json">
    <link rel="icon" href="/icons/icon.svg" type="image/svg+xml">
    <title>{{ $title ?? 'TRG Clean' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 text-slate-950 antialiased">
    <header class="sticky top-0 z-40 border-b border-slate-200 bg-white/95 backdrop-blur">
        <div class="mx-auto flex h-16 max-w-7xl items-center gap-4 px-4">
            <a href="{{ route('home') }}" class="flex shrink-0 items-center gap-2">
                <img src="/icons/icon.svg" alt="" class="h-9 w-9 rounded-xl">
                <span class="text-lg font-bold text-green-700">TRG Clean</span>
            </a>

            <form action="{{ route('products.index') }}" method="GET" class="hidden flex-1 md:block">
                <input
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Buscar produtos"
                    class="h-11 w-full rounded-md border border-slate-300 bg-white px-4 text-sm outline-none focus:border-green-600 focus:ring-2 focus:ring-green-100"
                >
            </form>

            <nav class="ml-auto hidden items-center gap-6 text-sm font-semibold text-slate-700 md:flex">
                <a href="{{ route('products.index') }}" class="hover:text-green-700">Produtos</a>
                <a href="{{ route('cart.index') }}" class="hover:text-green-700">Carrinho</a>
                <a href="{{ route('orders.index') }}" class="hover:text-green-700">Pedidos</a>
            </nav>
        </div>

        <form action="{{ route('products.index') }}" method="GET" class="border-t border-slate-100 px-4 py-3 md:hidden">
            <input
                name="search"
                value="{{ request('search') }}"
                placeholder="Buscar produtos"
                class="h-10 w-full rounded-md border border-slate-300 bg-white px-3 text-sm outline-none focus:border-green-600"
            >
        </form>
    </header>

    <main class="mx-auto max-w-7xl px-4 pb-24 pt-6 md:pb-10">
        @yield('content')
    </main>

    <footer class="hidden border-t border-slate-200 bg-white py-8 md:block">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-4 text-sm text-slate-500">
            <span>TRG Clean - catalogo digital e pedidos online.</span>
            <span>Atendimento rapido pelo WhatsApp.</span>
        </div>
    </footer>

    <nav class="fixed inset-x-0 bottom-0 z-50 border-t border-slate-200 bg-white md:hidden">
        <div class="grid h-16 grid-cols-4 text-xs font-semibold text-slate-600">
            <a href="{{ route('home') }}" class="flex flex-col items-center justify-center gap-1">Inicio</a>
            <a href="{{ route('products.index') }}" class="flex flex-col items-center justify-center gap-1">Produtos</a>
            <a href="{{ route('cart.index') }}" class="flex flex-col items-center justify-center gap-1">Carrinho</a>
            <a href="{{ route('orders.index') }}" class="flex flex-col items-center justify-center gap-1">Pedidos</a>
        </div>
    </nav>
</body>
</html>
