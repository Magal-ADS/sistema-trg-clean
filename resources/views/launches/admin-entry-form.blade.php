@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-2xl">
        <div class="mb-5 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold">Editar lancamento</h1>
                <p class="mt-1 text-sm text-slate-500">Corrija os dados enviados pelo vendedor.</p>
            </div>
            <a href="{{ route('launches.admin.entries.index') }}" class="inline-flex h-10 items-center justify-center rounded-md border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 hover:border-brand-secondary">Voltar</a>
        </div>

        <form action="{{ route('launches.admin.entries.update', $entry) }}" method="POST" class="space-y-4 rounded-lg border border-slate-200 bg-white p-5">
            @csrf
            @method('PUT')

            <div>
                <label for="seller_account_id" class="text-sm font-semibold text-slate-800">Vendedor</label>
                <select id="seller_account_id" name="seller_account_id" required class="mt-2 h-11 w-full rounded-md border border-slate-300 px-3 text-sm outline-none focus:border-brand-secondary">
                    @foreach($sellers as $seller)
                        <option value="{{ $seller->id }}" @selected(old('seller_account_id', $entry->seller_account_id) == $seller->id)>{{ $seller->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="entry_date" class="text-sm font-semibold text-slate-800">Data</label>
                <input id="entry_date" name="entry_date" type="date" value="{{ old('entry_date', $entry->entry_date->toDateString()) }}" required class="mt-2 h-11 w-full rounded-md border border-slate-300 px-3 text-sm outline-none focus:border-brand-secondary">
            </div>

            <div class="grid gap-4 md:grid-cols-3">
                <label class="text-sm font-semibold text-slate-800">Liga&ccedil;&otilde;es
                    <input name="presential_count" type="number" min="0" value="{{ old('presential_count', $entry->presential_count) }}" class="mt-2 h-11 w-full rounded-md border border-slate-300 px-3 text-sm">
                </label>
                <label class="text-sm font-semibold text-slate-800">Agendamentos
                    <input name="instagram_count" type="number" min="0" value="{{ old('instagram_count', $entry->instagram_count) }}" class="mt-2 h-11 w-full rounded-md border border-slate-300 px-3 text-sm">
                </label>
                <label class="text-sm font-semibold text-slate-800">Visitas
                    <input name="whatsapp_count" type="number" min="0" value="{{ old('whatsapp_count', $entry->whatsapp_count) }}" class="mt-2 h-11 w-full rounded-md border border-slate-300 px-3 text-sm">
                </label>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <label class="text-sm font-semibold text-slate-800">Quantidade de vendas
                    <input name="sales_count" type="number" min="0" value="{{ old('sales_count', $entry->sales_count) }}" class="mt-2 h-11 w-full rounded-md border border-slate-300 px-3 text-sm">
                </label>
                <label class="text-sm font-semibold text-slate-800">Valor total das vendas
                    <input data-money-mask name="sales_total" inputmode="numeric" value="{{ old('sales_total', number_format((float) $entry->sales_total, 2, ',', '.')) }}" class="mt-2 h-11 w-full rounded-md border border-slate-300 px-3 text-sm">
                </label>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row">
                <button class="h-11 rounded-md bg-brand-primary px-5 text-sm font-bold text-white hover:bg-brand-secondary">Salvar alteracao</button>
                <a href="{{ route('launches.admin.entries.index') }}" class="inline-flex h-11 items-center justify-center rounded-md border border-slate-200 bg-white px-5 text-sm font-semibold text-slate-700 hover:border-brand-secondary">Cancelar</a>
            </div>
        </form>

        <form action="{{ route('launches.admin.entries.destroy', $entry) }}" method="POST" class="mt-4" data-confirm-delete>
            @csrf
            @method('DELETE')
            <button class="h-11 w-full rounded-md border border-red-200 bg-white px-4 text-sm font-semibold text-red-600 hover:bg-red-50 sm:w-auto">Excluir lancamento</button>
        </form>
    </div>

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

        document.querySelector('[data-confirm-submit]')?.addEventListener('click', () => {
            pendingDeleteForm?.submit();
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
@endsection
