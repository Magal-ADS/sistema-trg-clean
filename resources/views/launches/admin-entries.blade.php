@extends('layouts.app')

@section('content')
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-bold">Lançamentos dos vendedores</h1>
            <p class="mt-1 text-sm text-slate-500">Acompanhe e corrija os lançamentos registrados pelo app.</p>
        </div>
        <a href="{{ route('launches.admin.dashboard') }}" class="inline-flex h-10 items-center justify-center rounded-md border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 hover:border-brand-secondary">Voltar aos módulos</a>
    </div>

    <form action="{{ route('launches.admin.entries.index') }}" method="GET" class="mt-6 grid gap-3 rounded-lg border border-slate-200 bg-white p-4 md:grid-cols-4">
        <label class="text-sm font-semibold text-slate-800">De
            <input name="date_from" type="date" value="{{ $dateFrom }}" class="mt-2 h-10 w-full rounded-md border border-slate-300 px-3 text-sm">
        </label>
        <label class="text-sm font-semibold text-slate-800">Até
            <input name="date_to" type="date" value="{{ $dateTo }}" class="mt-2 h-10 w-full rounded-md border border-slate-300 px-3 text-sm">
        </label>
        <label class="text-sm font-semibold text-slate-800">Vendedor
            <select name="seller_id" class="mt-2 h-10 w-full rounded-md border border-slate-300 px-3 text-sm">
                <option value="">Todas</option>
                @foreach($sellers as $seller)
                    <option value="{{ $seller->id }}" @selected($sellerId === $seller->id)>{{ $seller->name }}</option>
                @endforeach
            </select>
        </label>
        <div class="flex items-end">
            <button class="h-10 w-full rounded-md bg-brand-primary px-4 text-sm font-bold text-white hover:bg-brand-secondary">Filtrar</button>
        </div>
    </form>

    <div class="mt-5 grid gap-3 md:grid-cols-3">
        <div class="rounded-lg border border-slate-200 bg-white p-4">
            <p class="text-sm text-slate-500">Atividades</p>
            <strong class="text-2xl">{{ $opportunities }}</strong>
        </div>
        <div class="rounded-lg border border-slate-200 bg-white p-4">
            <p class="text-sm text-slate-500">Quantidade de vendas</p>
            <strong class="text-2xl">{{ $salesCount }}</strong>
        </div>
        <div class="rounded-lg border border-slate-200 bg-white p-4">
            <p class="text-sm text-slate-500">Valor total das vendas</p>
            <strong class="text-2xl text-brand-primary">R$ {{ number_format((float) $salesTotal, 2, ',', '.') }}</strong>
        </div>
    </div>

    <div class="mt-5 overflow-hidden rounded-lg border border-slate-200 bg-white">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-bold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Data</th>
                        <th class="px-4 py-3">Vendedor</th>
                        <th class="px-4 py-3">Atividades</th>
                        <th class="px-4 py-3">Quantidade de vendas</th>
                        <th class="px-4 py-3">Valor total das vendas</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($entries as $entry)
                        <tr>
                            <td class="px-4 py-3">{{ $entry->entry_date->format('d/m/Y') }}</td>
                            <td class="px-4 py-3 font-semibold">{{ $entry->seller?->name }}</td>
                            <td class="px-4 py-3 text-slate-600">Ligações {{ $entry->presential_count }} | Agendamentos {{ $entry->instagram_count }} | Visitas {{ $entry->whatsapp_count }}</td>
                            <td class="px-4 py-3">{{ $entry->sales_count }}</td>
                            <td class="px-4 py-3 font-bold text-brand-primary">R$ {{ number_format((float) $entry->sales_total, 2, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('launches.admin.entries.edit', $entry) }}" class="font-semibold text-brand-secondary hover:text-brand-primary">Editar</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-6 text-center text-slate-500">Nenhum lançamento no período.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-5">{{ $entries->links() }}</div>
@endsection
