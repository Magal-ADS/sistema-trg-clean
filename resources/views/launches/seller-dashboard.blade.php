<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0B2B54">
    <title>Lançamentos - TRG Clean</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-brand-ice text-slate-950">
    <header class="sticky top-0 z-30 border-b border-slate-200 bg-white">
        <div class="mx-auto flex max-w-md items-center justify-between px-4 py-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">TRG Clean</p>
                <h1 class="text-lg font-bold">{{ $seller->name }}</h1>
            </div>
            <form action="{{ route('launches.logout') }}" method="POST">
                @csrf
                <button class="rounded-md border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700">Sair</button>
            </form>
        </div>
    </header>

    <main class="mx-auto max-w-md space-y-5 px-4 py-5">
        @if(session('status'))
            <div class="rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
                {{ session('status') }}
            </div>
        @endif

        @if($errors->any())
            <div class="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-800">
                {{ $errors->first() }}
            </div>
        @endif

        <section class="rounded-lg border border-slate-200 bg-white p-5">
            <p class="text-sm font-semibold text-slate-500">Resumo do mês</p>
            <p data-summary-sales-total data-value="{{ (float) $monthSalesTotal }}" class="mt-2 text-3xl font-bold text-brand-primary">R$ {{ number_format((float) $monthSalesTotal, 2, ',', '.') }}</p>
            <div class="mt-4 grid grid-cols-2 gap-3 text-sm">
                <div class="rounded-md bg-slate-50 p-3">
                    <p class="text-slate-500">Quantidade de vendas</p>
                    <strong data-summary-sales-count class="text-xl">{{ $monthSalesCount }}</strong>
                </div>
                <div class="rounded-md bg-slate-50 p-3">
                    <p class="text-slate-500">Atividades</p>
                    <strong data-summary-activities class="text-xl">{{ $monthOpportunities }}</strong>
                </div>
            </div>
        </section>

        <section class="rounded-lg border border-slate-200 bg-white p-5">
            <h2 class="text-lg font-bold">Novo lançamento</h2>
            <form action="{{ route('launches.store') }}" method="POST" class="mt-4 space-y-4">
                @csrf

                <div>
                    <label for="entry_date" class="text-sm font-semibold text-slate-800">Data</label>
                    <input id="entry_date" name="entry_date" type="date" value="{{ old('entry_date', $today) }}" required class="mt-2 h-11 w-full rounded-md border border-slate-300 bg-white px-3 text-sm outline-none focus:border-brand-secondary">
                </div>

                <div class="grid grid-cols-1 gap-3">
                    <label class="block text-sm font-semibold text-slate-800">Liga&ccedil;&otilde;es
                        <input name="presential_count" type="number" min="0" value="{{ old('presential_count', 0) }}" class="mt-2 h-11 w-full rounded-md border border-slate-300 px-3 text-sm outline-none focus:border-brand-secondary">
                    </label>
                    <label class="block text-sm font-semibold text-slate-800">Agendamentos
                        <input name="instagram_count" type="number" min="0" value="{{ old('instagram_count', 0) }}" class="mt-2 h-11 w-full rounded-md border border-slate-300 px-3 text-sm outline-none focus:border-brand-secondary">
                    </label>
                    <label class="block text-sm font-semibold text-slate-800">Visitas
                        <input name="whatsapp_count" type="number" min="0" value="{{ old('whatsapp_count', 0) }}" class="mt-2 h-11 w-full rounded-md border border-slate-300 px-3 text-sm outline-none focus:border-brand-secondary">
                    </label>
                    <label class="block text-sm font-semibold text-slate-800">Observa&ccedil;&atilde;o das visitas <span class="font-normal text-slate-500">(opcional)</span>
                        <textarea name="notes" rows="3" maxlength="2000" placeholder="Ex.: locais ou clientes visitados" class="mt-2 w-full rounded-md border border-slate-300 px-3 py-2 text-sm outline-none focus:border-brand-secondary">{{ old('notes') }}</textarea>
                    </label>
                    <label class="block text-sm font-semibold text-slate-800">Quantidade de vendas
                        <input name="sales_count" type="number" min="0" value="{{ old('sales_count', 0) }}" class="mt-2 h-11 w-full rounded-md border border-slate-300 px-3 text-sm outline-none focus:border-brand-secondary">
                    </label>
                    <label class="block text-sm font-semibold text-slate-800">Valor total das vendas
                        <input data-money-mask name="sales_total" inputmode="numeric" placeholder="R$ 0,00" value="{{ old('sales_total') }}" class="mt-2 h-11 w-full rounded-md border border-slate-300 px-3 text-sm outline-none focus:border-brand-secondary">
                    </label>
                </div>

                <button class="h-12 w-full rounded-md bg-brand-primary px-5 text-sm font-bold text-white hover:bg-brand-secondary">
                    Salvar lançamento
                </button>
            </form>
        </section>

        <section class="rounded-lg border border-slate-200 bg-white p-5">
            <h2 class="text-lg font-bold">Últimos lançamentos</h2>
            <div data-entries-list class="mt-4 space-y-3">
                @forelse($entries as $entry)
                    <div data-entry-card data-presential-count="{{ $entry->presential_count }}" data-instagram-count="{{ $entry->instagram_count }}" data-whatsapp-count="{{ $entry->whatsapp_count }}" data-sales-count="{{ $entry->sales_count }}" data-sales-total="{{ (float) $entry->sales_total }}" class="rounded-md bg-slate-50 p-3 text-sm">
                        <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                            <strong>{{ $entry->entry_date->format('d/m/Y') }}</strong>
                            <span class="font-bold text-brand-primary">R$ {{ number_format((float) $entry->sales_total, 2, ',', '.') }}</span>
                        </div>
                        <p class="mt-1 text-slate-600">
                            Liga&ccedil;&otilde;es {{ $entry->presential_count }} | Agendamentos {{ $entry->instagram_count }} | Visitas {{ $entry->whatsapp_count }} | Quantidade de vendas {{ $entry->sales_count }}
                        </p>
                        @if($entry->notes)
                            <p class="mt-2 whitespace-pre-line text-slate-600"><strong>Observa&ccedil;&atilde;o das visitas:</strong> {{ $entry->notes }}</p>
                        @endif
                        <div class="mt-3 grid grid-cols-2 gap-2">
                            <a href="{{ route('launches.entries.edit', $entry) }}" class="inline-flex h-10 items-center justify-center rounded-md border border-slate-200 bg-white px-3 text-xs font-semibold text-slate-700">Editar</a>
                            <form action="{{ route('launches.entries.destroy', $entry) }}" method="POST" data-confirm-delete>
                                @csrf
                                @method('DELETE')
                                <button class="h-10 w-full rounded-md border border-red-200 bg-white px-3 text-xs font-semibold text-red-600">Excluir</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">Nenhum lançamento registrado.</p>
                @endforelse
            </div>
        </section>
    </main>

    <div id="delete-confirmation" style="display: none; position: fixed; inset: 0; z-index: 9999; align-items: center; justify-content: center; padding: 16px; background: rgba(15, 23, 42, 0.55);">
        <div style="width: 100%; max-width: 360px; border-radius: 8px; background: #fff; padding: 20px; box-shadow: 0 20px 50px rgba(15, 23, 42, 0.25);">
            <h2 class="text-lg font-bold text-slate-950">Excluir lan&ccedil;amento?</h2>
            <p class="mt-2 text-sm text-slate-600">Tem certeza que deseja excluir este lan&ccedil;amento?</p>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 20px;">
                <button type="button" data-confirm-cancel style="height: 44px; border: 1px solid #e2e8f0; border-radius: 6px; background: #fff; color: #334155; font-size: 14px; font-weight: 600;">Cancelar</button>
                <button type="button" data-confirm-submit style="height: 44px; border: 0; border-radius: 6px; background: #dc2626; color: #fff; font-size: 14px; font-weight: 700;">Excluir</button>
            </div>
        </div>
    </div>

    <script>
        window.addEventListener('pageshow', (event) => {
            if (event.persisted) {
                window.location.reload();
            }
        });

        let pendingDeleteForm = null;
        const deleteConfirmation = document.getElementById('delete-confirmation');

        document.querySelectorAll('[data-confirm-delete]').forEach((form) => {
            form.addEventListener('submit', (event) => {
                event.preventDefault();
                pendingDeleteForm = form;
                deleteConfirmation.style.display = 'flex';
                document.body.classList.add('overflow-hidden');
            });
        });

        const closeDeleteConfirmation = () => {
            pendingDeleteForm = null;
            deleteConfirmation.style.display = 'none';
            document.body.classList.remove('overflow-hidden');
        };

        document.querySelector('[data-confirm-cancel]')?.addEventListener('click', closeDeleteConfirmation);

        const moneyFormatter = new Intl.NumberFormat('pt-BR', {
            style: 'currency',
            currency: 'BRL',
        });

        const updateSummaryAfterDelete = (card) => {
            const totalElement = document.querySelector('[data-summary-sales-total]');
            const salesCountElement = document.querySelector('[data-summary-sales-count]');
            const activitiesElement = document.querySelector('[data-summary-activities]');
            const cardActivities = Number(card.dataset.presentialCount || 0) + Number(card.dataset.instagramCount || 0) + Number(card.dataset.whatsappCount || 0);
            const nextTotal = Math.max(0, Number(totalElement?.dataset.value || 0) - Number(card.dataset.salesTotal || 0));

            if (totalElement) {
                totalElement.dataset.value = String(nextTotal);
                totalElement.textContent = moneyFormatter.format(nextTotal);
            }

            if (salesCountElement) {
                salesCountElement.textContent = String(Math.max(0, Number(salesCountElement.textContent || 0) - Number(card.dataset.salesCount || 0)));
            }

            if (activitiesElement) {
                activitiesElement.textContent = String(Math.max(0, Number(activitiesElement.textContent || 0) - cardActivities));
            }
        };

        document.querySelector('[data-confirm-submit]')?.addEventListener('click', async () => {
            if (! pendingDeleteForm) {
                return;
            }

            const form = pendingDeleteForm;
            const card = form.closest('[data-entry-card]');
            const submitButton = document.querySelector('[data-confirm-submit]');

            submitButton.disabled = true;
            submitButton.textContent = 'Excluindo...';

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: new FormData(form),
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (! response.ok) {
                    throw new Error('delete_failed');
                }

                if (card) {
                    updateSummaryAfterDelete(card);
                    card.remove();
                }

                if (! document.querySelector('[data-entry-card]')) {
                    document.querySelector('[data-entries-list]')?.insertAdjacentHTML('beforeend', '<p class="text-sm text-slate-500">Nenhum lancamento registrado.</p>');
                }

                closeDeleteConfirmation();
            } catch (error) {
                form.submit();
            } finally {
                submitButton.disabled = false;
                submitButton.textContent = 'Excluir';
            }
        });

        deleteConfirmation?.addEventListener('click', (event) => {
            if (event.target === deleteConfirmation) {
                closeDeleteConfirmation();
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && deleteConfirmation.style.display === 'flex') {
                closeDeleteConfirmation();
            }
        });

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
