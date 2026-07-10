@extends('layouts.app')

@section('content')
    <h1 class="text-2xl font-bold">Carrinho</h1>
    <div class="mt-5 overflow-hidden rounded-lg border border-slate-200 bg-white">
        @forelse($items as $item)
            <div class="flex items-center justify-between gap-4 border-b border-slate-100 p-4 last:border-b-0">
                <div>
                    <h2 class="font-semibold">{{ $item->product_name }}</h2>
                    <p class="text-sm text-slate-500">{{ collect([$item->size, $item->color, $item->fragrance])->filter()->join(' / ') }}</p>
                    @if(data_get($item->metadata, 'customer.name'))
                        <p class="mt-1 text-xs text-slate-400">
                            {{ data_get($item->metadata, 'customer.name') }}
                            - {{ data_get($item->metadata, 'customer.city') }}
                            - {{ data_get($item->metadata, 'customer.fulfillment_type') }}
                            - {{ data_get($item->metadata, 'customer.payment_method') }}
                        </p>
                    @endif
                </div>
                <div class="text-right">
                    <p class="text-sm text-slate-500">{{ $item->quantity }} x R$ {{ number_format((float) $item->unit_price, 2, ',', '.') }}</p>
                    <p class="font-bold text-brand-primary">R$ {{ number_format((float) $item->total, 2, ',', '.') }}</p>
                </div>
            </div>
        @empty
            <div class="p-6 text-sm text-slate-500">Nenhum item no carrinho.</div>
        @endforelse
    </div>

    @if($items->count())
        <div class="mt-6 rounded-lg border border-slate-200 bg-white p-4">
            <div class="flex items-center justify-between gap-4">
                <span class="text-sm font-semibold text-slate-600">Total do pedido</span>
                <strong class="text-2xl text-brand-primary">R$ {{ number_format((float) $subtotal, 2, ',', '.') }}</strong>
            </div>

            @unless($isCheckoutStep)
                <div class="mt-5 flex flex-col gap-3 sm:flex-row">
                    <a href="{{ route('products.index') }}" class="inline-flex h-11 items-center justify-center rounded-md border border-slate-200 bg-white px-5 text-sm font-semibold text-slate-700 hover:border-brand-secondary hover:text-brand-primary">
                        Continuar comprando
                    </a>
                    <a href="{{ route('cart.index', ['checkout' => 1]) }}" class="inline-flex h-11 items-center justify-center rounded-md bg-brand-secondary px-5 text-sm font-bold text-white hover:bg-brand-primary">
                        Continuar
                    </a>
                </div>
            @endunless
        </div>

        @if($isCheckoutStep)
            <form action="{{ route('cart.checkout') }}" method="POST" class="mt-6 rounded-lg border border-slate-200 bg-white p-4">
                @csrf

                <h2 class="text-base font-bold text-slate-950">Preencha os dados</h2>

                <div class="mt-4 grid gap-3">
                    <input name="customer_name" value="{{ old('customer_name') }}" placeholder="Nome" required class="h-11 rounded-md border border-slate-200 bg-slate-100 px-3 text-sm outline-none focus:border-brand-secondary focus:bg-white focus:ring-2 focus:ring-brand-secondary-soft">
                    <input name="customer_cpf" value="{{ old('customer_cpf') }}" placeholder="CPF" required class="h-11 rounded-md border border-slate-200 bg-slate-100 px-3 text-sm outline-none focus:border-brand-secondary focus:bg-white focus:ring-2 focus:ring-brand-secondary-soft">
                    <input name="customer_phone" value="{{ old('customer_phone') }}" placeholder="Telefone com DDD" required inputmode="tel" maxlength="15" data-phone-mask class="h-11 rounded-md border border-slate-200 bg-slate-100 px-3 text-sm outline-none focus:border-brand-secondary focus:bg-white focus:ring-2 focus:ring-brand-secondary-soft">
                    <input name="customer_address" value="{{ old('customer_address') }}" placeholder="Endereco" required class="h-11 rounded-md border border-slate-200 bg-slate-100 px-3 text-sm outline-none focus:border-brand-secondary focus:bg-white focus:ring-2 focus:ring-brand-secondary-soft">
                    <input name="customer_reference" value="{{ old('customer_reference') }}" placeholder="Complemento / Referencia" class="h-11 rounded-md border border-slate-200 bg-slate-100 px-3 text-sm outline-none focus:border-brand-secondary focus:bg-white focus:ring-2 focus:ring-brand-secondary-soft">
                </div>

                @php
                    $chipClass = 'inline-flex h-9 cursor-pointer items-center rounded-full border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 has-[:checked]:border-brand-secondary has-[:checked]:bg-brand-secondary-soft has-[:checked]:text-brand-primary';
                @endphp

                <div class="mt-4">
                    <p class="text-sm font-bold text-slate-950">Selecione</p>
                    <div class="mt-2 flex flex-wrap gap-2">
                        @foreach(['Empresa', 'Casa'] as $type)
                            <label class="{{ $chipClass }}">
                                <input type="radio" name="customer_type" value="{{ $type }}" @checked(old('customer_type', 'Casa') === $type) required class="sr-only">
                                {{ $type }}
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="mt-4">
                    <p class="text-sm font-bold text-slate-950">Cidade</p>
                    <div class="mt-2 flex flex-wrap gap-2">
                        @foreach($cities as $city)
                            <label class="{{ $chipClass }}">
                                <input type="radio" name="city_id" value="{{ $city->id }}" @checked((int) old('city_id', $cities->first()?->id) === $city->id) required class="sr-only">
                                {{ $city->name }}{{ $city->state ? ' - '.$city->state : '' }}
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="mt-4">
                    <p class="text-sm font-bold text-slate-950">Entrega ou Retirada</p>
                    <div class="mt-2 flex flex-wrap gap-2">
                        @foreach(['Entrega', 'Retirar na Loja'] as $fulfillment)
                            <label class="{{ $chipClass }}">
                                <input type="radio" name="fulfillment_type" value="{{ $fulfillment }}" @checked(old('fulfillment_type', 'Retirar na Loja') === $fulfillment) required class="sr-only">
                                {{ $fulfillment }}
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="mt-4">
                    <p class="text-sm font-bold text-slate-950">Forma de Pagamento</p>
                    <div class="mt-2 flex flex-wrap gap-2">
                        @foreach(['Pix' => 'Pix', 'Cartao' => 'Cartao'] as $paymentValue => $paymentLabel)
                            <label class="{{ $chipClass }}">
                                <input type="radio" name="payment_method" value="{{ $paymentValue }}" @checked(old('payment_method', 'Pix') === $paymentValue) required class="sr-only">
                                {{ $paymentLabel }}
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="mt-5 flex flex-col gap-3 sm:flex-row">
                    <a href="{{ route('cart.index') }}" class="inline-flex h-11 items-center justify-center rounded-md border border-slate-200 bg-white px-5 text-sm font-semibold text-slate-700 hover:border-brand-secondary hover:text-brand-primary">
                        Voltar
                    </a>
                    <button type="submit" class="inline-flex h-11 items-center justify-center rounded-md bg-brand-secondary px-5 text-sm font-bold text-white hover:bg-brand-primary">
                        Confirmar
                    </button>
                </div>
            </form>
        @endif
    @endif

    <div class="mt-6">{{ $items->links() }}</div>
@endsection
