@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-3xl">
        <div class="mb-5 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold">{{ $item->exists ? 'Editar' : 'Novo' }} {{ strtolower($config['singular']) }}</h1>
                <p class="mt-1 text-sm text-slate-500">{{ $config['title'] }}</p>
            </div>
            <a href="{{ route('launches.admin.modules.index', $module) }}" class="inline-flex h-10 items-center justify-center rounded-md border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 hover:border-brand-secondary">Voltar</a>
        </div>

        @if($module === 'orders' && $item->exists)
            <div class="mb-5 rounded-lg border border-slate-200 bg-white p-4">
                <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Pedido</p>
                        <h2 class="text-lg font-bold">{{ $item->code }}</h2>
                    </div>
                    <strong class="text-lg text-brand-primary">R$ {{ number_format((float) $item->total, 2, ',', '.') }}</strong>
                </div>
                <div class="mt-4 divide-y divide-slate-100 text-sm">
                    @forelse($item->items as $orderItem)
                        <div class="py-3">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="font-semibold">{{ $orderItem->product_name }}</p>
                                    <p class="text-xs text-slate-500">{{ collect([$orderItem->size, $orderItem->color, $orderItem->fragrance])->filter()->join(' | ') }}</p>
                                </div>
                                <p class="shrink-0 text-right">{{ $orderItem->quantity }}x R$ {{ number_format((float) $orderItem->unit_price, 2, ',', '.') }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="py-3 text-slate-500">Nenhum item vinculado ao pedido.</p>
                    @endforelse
                </div>
            </div>
        @endif

        <form action="{{ $item->exists ? route('launches.admin.modules.update', [$module, $item->id]) : route('launches.admin.modules.store', $module) }}" method="POST" class="space-y-4 rounded-lg border border-slate-200 bg-white p-5">
            @csrf
            @if($item->exists)
                @method('PUT')
            @endif

            @foreach($config['fields'] as $name => $field)
                @php
                    $type = $field['type'] ?? 'text';
                    $value = old($name, data_get($item, $name));

                    if ($type === 'datetime-local' && $value) {
                        $value = $value instanceof \Illuminate\Support\Carbon ? $value->format('Y-m-d\TH:i') : \Illuminate\Support\Carbon::parse($value)->format('Y-m-d\TH:i');
                    }
                @endphp

                @if($type === 'checkbox')
                    <input type="hidden" name="{{ $name }}" value="0">
                    <label class="flex items-center gap-2 text-sm font-semibold text-slate-800">
                        <input type="checkbox" name="{{ $name }}" value="1" @checked(old($name, $item->exists ? (bool) data_get($item, $name) : true)) class="rounded border-slate-300">
                        {{ $field['label'] }}
                    </label>
                @else
                    <div>
                        <label for="{{ $name }}" class="text-sm font-semibold text-slate-800">{{ $field['label'] }}</label>

                        @if($type === 'textarea')
                            <textarea id="{{ $name }}" name="{{ $name }}" rows="4" class="mt-2 w-full rounded-md border border-slate-300 px-3 py-2 text-sm outline-none focus:border-brand-secondary">{{ $value }}</textarea>
                        @elseif($type === 'select')
                            <select id="{{ $name }}" name="{{ $name }}" class="mt-2 h-11 w-full rounded-md border border-slate-300 px-3 text-sm outline-none focus:border-brand-secondary">
                                @foreach($field['options'] as $optionValue => $optionLabel)
                                    <option value="{{ $optionValue }}" @selected((string) $value === (string) $optionValue)>{{ $optionLabel }}</option>
                                @endforeach
                            </select>
                        @elseif($type === 'relation')
                            <select id="{{ $name }}" name="{{ $name }}" class="mt-2 h-11 w-full rounded-md border border-slate-300 px-3 text-sm outline-none focus:border-brand-secondary">
                                <option value="">Selecione</option>
                                @foreach($options[$name] ?? [] as $optionValue => $optionLabel)
                                    <option value="{{ $optionValue }}" @selected((string) $value === (string) $optionValue)>{{ $optionLabel }}</option>
                                @endforeach
                            </select>
                        @else
                            <input
                                id="{{ $name }}"
                                name="{{ $name }}"
                                type="{{ $type === 'money' ? 'number' : $type }}"
                                @if($type === 'money') step="0.01" min="0" @endif
                                @if(($field['step'] ?? null)) step="{{ $field['step'] }}" @endif
                                value="{{ $value }}"
                                class="mt-2 h-11 w-full rounded-md border border-slate-300 px-3 text-sm outline-none focus:border-brand-secondary"
                            >
                        @endif

                        @error($name)
                            <p class="mt-1 text-sm font-medium text-red-700">{{ $message }}</p>
                        @enderror
                    </div>
                @endif
            @endforeach

            <div class="flex flex-col gap-3 pt-2 sm:flex-row">
                <button class="h-11 rounded-md bg-brand-primary px-5 text-sm font-bold text-white hover:bg-brand-secondary">Salvar</button>
                <a href="{{ route('launches.admin.modules.index', $module) }}" class="inline-flex h-11 items-center justify-center rounded-md border border-slate-200 bg-white px-5 text-sm font-semibold text-slate-700 hover:border-brand-secondary">Cancelar</a>
            </div>
        </form>

        @if($item->exists && ! ($config['readonly'] ?? false))
            <form action="{{ route('launches.admin.modules.destroy', [$module, $item->id]) }}" method="POST" class="mt-4" onsubmit="return confirm('Excluir este registro?')">
                @csrf
                @method('DELETE')
                <button class="text-sm font-semibold text-red-600 hover:text-red-700">Excluir registro</button>
            </form>
        @endif
    </div>
@endsection
