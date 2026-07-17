@extends('layouts.app')

@section('content')
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-bold">{{ $config['title'] }}</h1>
            <p class="mt-1 text-sm text-slate-500">Gerencie os dados importados e usados pelo catalogo.</p>
        </div>
        <div class="flex flex-col gap-2 sm:flex-row">
            <a href="{{ route('launches.admin.dashboard') }}" class="inline-flex h-10 items-center justify-center rounded-md border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 hover:border-brand-secondary">Voltar</a>
            @if($module === 'orders')
                <a href="{{ route('launches.admin.modules.index', ['module' => 'orders', 'view' => 'kanban']) }}" class="inline-flex h-10 items-center justify-center rounded-md bg-brand-primary px-4 text-sm font-bold text-white hover:bg-brand-secondary">Visualizar Kanban</a>
            @endif
            @if(($config['create'] ?? true) && ! ($config['readonly'] ?? false))
                <a href="{{ route('launches.admin.modules.create', $module) }}" class="inline-flex h-10 items-center justify-center rounded-md bg-brand-primary px-4 text-sm font-bold text-white hover:bg-brand-secondary">Novo</a>
            @endif
        </div>
    </div>

    <form action="{{ route('launches.admin.modules.index', $module) }}" method="GET" class="mt-5 flex flex-col gap-3 rounded-lg border border-slate-200 bg-white p-4 sm:flex-row">
        <input
            name="search"
            value="{{ $search }}"
            placeholder="Buscar"
            class="h-10 flex-1 rounded-md border border-slate-300 px-3 text-sm outline-none focus:border-brand-secondary"
        >
        <button class="h-10 rounded-md bg-brand-primary px-4 text-sm font-bold text-white hover:bg-brand-secondary">Buscar</button>
    </form>

    <div class="mt-5 overflow-hidden rounded-lg border border-slate-200 bg-white">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-bold uppercase tracking-wide text-slate-500">
                    <tr>
                        @foreach($config['columns'] as $label)
                            <th class="px-4 py-3">{{ $label }}</th>
                        @endforeach
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($items as $item)
                        <tr>
                            @foreach($config['columns'] as $column => $label)
                                @php($value = data_get($item, $column))
                                <td class="px-4 py-3">
                                    @if(is_bool($value))
                                        <span class="rounded-full px-2 py-1 text-xs font-bold {{ $value ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700' }}">{{ $value ? 'Sim' : 'Nao' }}</span>
                                    @elseif($value instanceof \Illuminate\Support\Carbon)
                                        {{ $value->format('d/m/Y H:i') }}
                                    @elseif(is_numeric($value) && str_contains($column, 'price') || in_array($column, ['total', 'subtotal', 'discount', 'shipping', 'value'], true))
                                        R$ {{ number_format((float) $value, 2, ',', '.') }}
                                    @elseif($column === 'hex' && $value)
                                        <span class="inline-flex items-center gap-2">
                                            <span class="h-5 w-5 rounded border border-slate-200" style="background: {{ $value }}"></span>
                                            {{ $value }}
                                        </span>
                                    @else
                                        {{ filled($value) ? $value : '-' }}
                                    @endif
                                </td>
                            @endforeach
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('launches.admin.modules.edit', [$module, $item->id]) }}" class="font-semibold text-brand-secondary hover:text-brand-primary">Editar</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($config['columns']) + 1 }}" class="px-4 py-6 text-center text-slate-500">Nenhum registro encontrado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-5">{{ $items->links() }}</div>
@endsection
