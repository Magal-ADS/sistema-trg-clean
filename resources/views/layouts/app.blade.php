<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0B2B54">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="TRG Clean">
    <meta name="mobile-web-app-capable" content="yes">
    <link rel="manifest" href="/manifest.json">
    <link rel="icon" href="/icons/icon.svg" type="image/svg+xml">
    <title>{{ $title ?? 'TRG Clean' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen overflow-x-hidden bg-brand-ice text-slate-950 antialiased">
    @php
        $launchSessionActive = session()->has('launch_admin_id') || session()->has('launch_seller_id');
        $launchAdminActive = session()->has('launch_admin_id');
        $launchRoute = session()->has('launch_admin_id') ? 'launches.admin.dashboard' : ($launchSessionActive ? 'launches.index' : 'launches.login.form');
        $launchLabel = $launchSessionActive ? 'Lancamentos' : 'Login';
        $mobileNavItems = [
            ['label' => 'Inicio', 'route' => 'home', 'active' => request()->routeIs('home')],
            ['label' => 'Produtos', 'route' => 'products.index', 'active' => request()->routeIs('products.*')],
            ['label' => 'Carrinho', 'route' => 'cart.index', 'active' => request()->routeIs('cart.*')],
            ['label' => 'Pedidos', 'route' => 'orders.index', 'active' => request()->routeIs('orders.*')],
            ['label' => $launchLabel, 'route' => $launchRoute, 'active' => request()->routeIs('launches.*')],
        ];
    @endphp

    @if($launchAdminActive)
        <header class="sticky top-0 z-40 w-full border-b border-slate-200 bg-white/95 backdrop-blur">
            <div class="mx-auto flex h-14 w-full max-w-7xl items-center gap-3 px-4">
                <a href="{{ route('launches.admin.dashboard') }}" class="min-w-0 truncate text-sm font-bold text-slate-950">Administrador</a>
                <div class="ml-auto flex items-center gap-2">
                    <form action="{{ route('launches.admin.logout') }}" method="POST">
                        @csrf
                        <button class="rounded-md border border-slate-200 px-3 py-2 text-xs font-bold text-slate-700 hover:border-red-300 hover:text-red-600">Sair</button>
                    </form>
                </div>
            </div>
        </header>
    @else
        <header class="sticky top-0 z-40 border-b border-slate-200 bg-white/95 backdrop-blur">
            <div class="mx-auto flex h-16 max-w-7xl items-center gap-3 px-4 md:h-20 md:gap-5">
                <a href="{{ route('home') }}" class="flex shrink-0 items-center gap-2">
                    <img src="/images/trg-logo.jpg" alt="TRG Clean" class="h-11 w-auto md:h-16">
                </a>

                <form action="{{ route('products.index') }}" method="GET" class="hidden flex-1 md:block">
                    <input
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Buscar produtos"
                        class="h-11 w-full rounded-md border border-slate-300 bg-white px-4 text-sm outline-none focus:border-brand-secondary focus:ring-2 focus:ring-brand-secondary-soft"
                    >
                </form>

                <nav class="ml-auto grid flex-1 grid-cols-5 items-end gap-1 text-center text-[11px] font-semibold text-slate-600 md:hidden">
                    @foreach($mobileNavItems as $item)
                        <a
                            href="{{ route($item['route']) }}"
                            class="border-b-2 px-1 pb-2 pt-3 leading-none transition {{ $item['active'] ? 'border-brand-secondary text-brand-primary' : 'border-transparent hover:text-brand-primary' }}"
                        >
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                </nav>

                <nav class="ml-auto hidden items-center gap-6 text-sm font-semibold text-slate-700 md:flex">
                    <a href="{{ route('products.index') }}" class="hover:text-brand-primary">Produtos</a>
                    <a href="{{ route('cart.index') }}" class="hover:text-brand-primary">Carrinho</a>
                    <a href="{{ route('orders.index') }}" class="hover:text-brand-primary">Pedidos</a>
                    <a href="{{ route($launchRoute) }}" class="rounded-md bg-brand-primary px-4 py-2 text-white hover:bg-brand-secondary">{{ $launchLabel }}</a>
                </nav>
            </div>

            <form action="{{ route('products.index') }}" method="GET" class="border-t border-slate-100 px-4 py-3 md:hidden">
                <input
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Buscar produtos"
                    class="h-10 w-full rounded-md border border-slate-300 bg-white px-3 text-sm outline-none focus:border-brand-secondary"
                >
            </form>
        </header>
    @endif

    <main class="mx-auto w-full max-w-7xl px-4 pb-10 pt-6">
        @if(session('status'))
            <div class="mb-5 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
                {{ session('status') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-5 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-800">
                {{ $errors->first() }}
            </div>
        @endif

        @yield('content')
    </main>

    <footer class="hidden border-t border-slate-200 bg-white py-8 md:block">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-4 text-sm text-slate-500">
            <span>TRG Clean - catalogo digital e pedidos online.</span>
            <span>Atendimento rapido pelo WhatsApp.</span>
        </div>
    </footer>
</body>
</html>
