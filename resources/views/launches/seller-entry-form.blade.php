<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0B2B54">
    <title>Editar lancamento - TRG Clean</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-brand-ice text-slate-950">
    <header class="sticky top-0 z-30 border-b border-slate-200 bg-white">
        <div class="mx-auto flex max-w-md items-center justify-between px-4 py-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">TRG Clean</p>
                <h1 class="text-lg font-bold">{{ $seller->name }}</h1>
            </div>
            <a href="{{ route('launches.index') }}" class="rounded-md border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700">Voltar</a>
        </div>
    </header>

    <main class="mx-auto max-w-md space-y-5 px-4 py-5">
        @if($errors->any())
            <div class="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-800">
                {{ $errors->first() }}
            </div>
        @endif

        <section class="rounded-lg border border-slate-200 bg-white p-5">
            <h2 class="text-lg font-bold">Editar lancamento</h2>

            <form action="{{ route('launches.entries.update', $entry) }}" method="POST" class="mt-4 space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label for="entry_date" class="text-sm font-semibold text-slate-800">Data</label>
                    <input id="entry_date" name="entry_date" type="date" value="{{ old('entry_date', $entry->entry_date->toDateString()) }}" required class="mt-2 h-11 w-full rounded-md border border-slate-300 bg-white px-3 text-sm outline-none focus:border-brand-secondary">
                </div>

                <div class="grid grid-cols-1 gap-3">
                    <label class="block text-sm font-semibold text-slate-800">Liga&ccedil;&otilde;es
                        <input name="presential_count" type="number" min="0" value="{{ old('presential_count', $entry->presential_count) }}" class="mt-2 h-11 w-full rounded-md border border-slate-300 px-3 text-sm outline-none focus:border-brand-secondary">
                    </label>
                    <label class="block text-sm font-semibold text-slate-800">Agendamentos
                        <input name="instagram_count" type="number" min="0" value="{{ old('instagram_count', $entry->instagram_count) }}" class="mt-2 h-11 w-full rounded-md border border-slate-300 px-3 text-sm outline-none focus:border-brand-secondary">
                    </label>
                    <label class="block text-sm font-semibold text-slate-800">Visitas
                        <input name="whatsapp_count" type="number" min="0" value="{{ old('whatsapp_count', $entry->whatsapp_count) }}" class="mt-2 h-11 w-full rounded-md border border-slate-300 px-3 text-sm outline-none focus:border-brand-secondary">
                    </label>
                    <label class="block text-sm font-semibold text-slate-800">Quantidade de vendas
                        <input name="sales_count" type="number" min="0" value="{{ old('sales_count', $entry->sales_count) }}" class="mt-2 h-11 w-full rounded-md border border-slate-300 px-3 text-sm outline-none focus:border-brand-secondary">
                    </label>
                    <label class="block text-sm font-semibold text-slate-800">Valor total das vendas
                        <input data-money-mask name="sales_total" inputmode="numeric" value="{{ old('sales_total', number_format((float) $entry->sales_total, 2, ',', '.')) }}" class="mt-2 h-11 w-full rounded-md border border-slate-300 px-3 text-sm outline-none focus:border-brand-secondary">
                    </label>
                </div>

                <div class="flex flex-col gap-3">
                    <button class="h-12 w-full rounded-md bg-brand-primary px-5 text-sm font-bold text-white hover:bg-brand-secondary">Salvar alteracao</button>
                    <a href="{{ route('launches.index') }}" class="inline-flex h-12 items-center justify-center rounded-md border border-slate-200 bg-white px-5 text-sm font-semibold text-slate-700">Cancelar</a>
                </div>
            </form>
        </section>
    </main>

    <script>
        document.querySelectorAll('[data-money-mask]').forEach((input) => {
            const format = () => {
                const cents = input.value.replace(/\D/g, '');
                const amount = Number(cents || '0') / 100;

                input.value = amount.toLocaleString('pt-BR', {
                    style: 'currency',
                    currency: 'BRL',
                });
            };

            input.addEventListener('input', format);

            if (input.value) {
                format();
            }
        });
    </script>
</body>
</html>
