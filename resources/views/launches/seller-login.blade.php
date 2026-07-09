<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0B2B54">
    <title>Lançamentos - TRG Clean</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-brand-ice px-4 py-8 text-slate-950">
    <main class="mx-auto max-w-md overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="bg-brand-primary p-6 text-white">
            <img src="/images/trg-logo.jpg" alt="TRG Clean" class="mb-5 h-16 rounded-md bg-white object-contain p-1">
            <h1 class="text-2xl font-bold">Lançamentos do dia</h1>
            <p class="mt-1 text-sm text-white/75">Selecione seu nome e informe sua senha.</p>
        </div>

        <form action="{{ route('launches.login') }}" method="POST" class="space-y-5 p-6">
            @csrf

            @if($errors->any())
                <div class="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-800">
                    {{ $errors->first() }}
                </div>
            @endif

            <div>
                <label for="seller_account_id" class="text-sm font-semibold text-slate-800">Quem é você?</label>
                <select id="seller_account_id" name="seller_account_id" required class="mt-2 h-12 w-full rounded-md border border-slate-300 bg-white px-3 text-sm outline-none focus:border-brand-secondary focus:ring-2 focus:ring-brand-secondary-soft">
                    <option value="">Selecione a vendedora</option>
                    @foreach($sellers as $seller)
                        <option value="{{ $seller->id }}" @selected(old('seller_account_id') == $seller->id)>
                            {{ $seller->name }}{{ $seller->city ? ' - '.$seller->city : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="password" class="text-sm font-semibold text-slate-800">Senha ou PIN</label>
                <input id="password" name="password" type="password" required class="mt-2 h-12 w-full rounded-md border border-slate-300 bg-white px-3 text-sm outline-none focus:border-brand-secondary focus:ring-2 focus:ring-brand-secondary-soft">
            </div>

            <button type="submit" class="h-12 w-full rounded-md bg-brand-primary px-5 text-sm font-bold text-white hover:bg-brand-secondary">
                Entrar
            </button>
        </form>
    </main>
</body>
</html>
