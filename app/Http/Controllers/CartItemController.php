<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\City;
use App\Models\Order;
use App\Models\Product;
use App\Models\SizeFragrancePrice;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CartItemController extends Controller
{
    public function index(): View
    {
        $items = CartItem::query()
            ->select(['id', 'product_id', 'session_id', 'product_name', 'size', 'color', 'fragrance', 'quantity', 'unit_price', 'total', 'metadata', 'created_at'])
            ->with('product:id,name,slug,image_url')
            ->where('session_id', request()->session()->getId())
            ->latest()
            ->paginate(30);

        return view('cart.index', [
            'items' => $items,
            'subtotal' => CartItem::query()
                ->where('session_id', request()->session()->getId())
                ->sum('total'),
            'isCheckoutStep' => request()->boolean('checkout'),
            'cities' => City::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'state']),
        ]);
    }

    public function store(Request $request, Product $product): RedirectResponse
    {
        abort_if($product->is_unavailable, 404);

        $variationIds = $product->variationPrices()
            ->where('is_active', true)
            ->pluck('id');

        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:999'],
            'variation_id' => [
                $variationIds->isNotEmpty() ? 'required' : 'nullable',
                'integer',
                Rule::in($variationIds->all()),
            ],
        ], [
            'variation_id.required' => 'Escolha uma opcao do produto antes de adicionar ao carrinho.',
            'quantity.required' => 'Informe a quantidade.',
            'quantity.min' => 'A quantidade precisa ser pelo menos 1.',
        ]);

        $variation = null;

        if (! empty($validated['variation_id'])) {
            $variation = SizeFragrancePrice::query()
                ->with(['size:id,name', 'fragranceType:id,name', 'colorType:id,name'])
                ->where('product_id', $product->id)
                ->where('is_active', true)
                ->findOrFail($validated['variation_id']);
        }

        $quantity = (int) $validated['quantity'];
        $unitPrice = (float) ($variation?->promotional_price ?: $variation?->price ?: $product->promotional_price ?: $product->price);
        $size = $variation?->size?->name;
        $color = $variation?->colorType?->name;
        $fragrance = $variation?->fragranceType?->name;

        CartItem::query()->create([
            'product_id' => $product->id,
            'session_id' => $request->session()->getId(),
            'product_name' => $product->name,
            'size' => $size,
            'color' => $color,
            'fragrance' => $fragrance,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total' => $unitPrice * $quantity,
            'metadata' => [
                'source' => 'product_detail',
                'variation_id' => $variation?->id,
            ],
        ]);

        return back()
            ->with('status', "{$product->name} foi adicionado ao carrinho.");
    }

    public function checkout(Request $request): RedirectResponse
    {
        $items = CartItem::query()
            ->with('product:id,name')
            ->where('session_id', $request->session()->getId())
            ->get();

        if ($items->isEmpty()) {
            return redirect()
                ->route('cart.index')
                ->withErrors(['cart' => 'Adicione pelo menos um produto antes de fechar o pedido.']);
        }

        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_cpf' => ['required', 'string', 'max:20', function (string $attribute, mixed $value, \Closure $fail): void {
                if (! $this->isValidCpf((string) $value)) {
                    $fail('Informe um CPF valido.');
                }
            }],
            'customer_phone' => ['required', 'string', 'max:30'],
            'customer_address' => ['required', 'string', 'max:255'],
            'customer_reference' => ['nullable', 'string', 'max:255'],
            'customer_type' => ['required', Rule::in(['Empresa', 'Casa'])],
            'city_id' => ['required', 'integer', Rule::exists('cities', 'id')->where('is_active', true)],
            'fulfillment_type' => ['required', Rule::in(['Entrega', 'Retirar na Loja'])],
            'payment_method' => ['required', Rule::in(['Pix', 'Cartao'])],
        ], [
            'customer_name.required' => 'Informe o nome.',
            'customer_cpf.required' => 'Informe o CPF.',
            'customer_phone.required' => 'Informe o telefone com DDD.',
            'customer_address.required' => 'Informe o endereco.',
            'customer_type.required' => 'Selecione se e empresa ou casa.',
            'city_id.required' => 'Selecione a cidade.',
            'city_id.exists' => 'A cidade selecionada e invalida.',
            'fulfillment_type.required' => 'Selecione entrega ou retirada.',
            'payment_method.required' => 'Selecione a forma de pagamento.',
        ]);

        $city = City::query()->find($validated['city_id']);
        $subtotal = $items->sum(fn (CartItem $item): float => (float) $item->total);

        $customer = [
            'name' => $validated['customer_name'],
            'cpf' => $validated['customer_cpf'],
            'cpf_digits' => $this->onlyDigits($validated['customer_cpf']),
            'phone' => $validated['customer_phone'],
            'address' => $validated['customer_address'],
            'reference' => $validated['customer_reference'] ?? null,
            'type' => $validated['customer_type'],
            'city_id' => $city?->id,
            'city' => $city?->name,
            'state' => $city?->state,
            'fulfillment_type' => $validated['fulfillment_type'],
            'payment_method' => $validated['payment_method'],
        ];

        $order = DB::transaction(function () use ($items, $customer, $city, $subtotal): Order {
            $order = Order::query()->create([
                'code' => $this->nextOrderCode(),
                'customer_name' => $customer['name'],
                'customer_cpf' => $customer['cpf_digits'],
                'customer_phone' => $customer['phone'],
                'customer_type' => $customer['type'],
                'city_id' => $city?->id,
                'delivery_type' => $customer['fulfillment_type'],
                'payment_method' => $customer['payment_method'],
                'status' => 'pending',
                'subtotal' => $subtotal,
                'discount' => 0,
                'shipping' => 0,
                'total' => $subtotal,
                'address' => $customer['address'],
                'complement' => $customer['reference'],
                'confirmed_at' => now(),
                'metadata' => [
                    'customer' => $customer,
                    'source' => 'site_checkout',
                ],
            ]);

            foreach ($items as $item) {
                $order->items()->create([
                    'product_id' => $item->product_id,
                    'product_name' => $item->product_name,
                    'size' => $item->size,
                    'color' => $item->color,
                    'fragrance' => $item->fragrance,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'total' => $item->total,
                    'metadata' => [
                        'cart_item_id' => $item->id,
                        'cart_metadata' => $item->metadata,
                    ],
                ]);
            }

            CartItem::query()->whereKey($items->modelKeys())->delete();

            return $order;
        });

        return redirect()
            ->route('cart.index')
            ->with('status', "Pedido {$order->code} criado com sucesso.");
    }

    private function nextOrderCode(): string
    {
        do {
            $code = 'PED-'.now()->format('Ymd').'-'.str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        } while (Order::query()->where('code', $code)->exists());

        return $code;
    }

    private function onlyDigits(string $value): string
    {
        return preg_replace('/\D+/', '', $value) ?: $value;
    }

    private function isValidCpf(string $value): bool
    {
        $cpf = $this->onlyDigits($value);

        if (strlen($cpf) !== 11 || preg_match('/^(\d)\1{10}$/', $cpf)) {
            return false;
        }

        for ($position = 9; $position <= 10; $position++) {
            $sum = 0;
            for ($index = 0; $index < $position; $index++) {
                $sum += (int) $cpf[$index] * (($position + 1) - $index);
            }
            $digit = ($sum * 10) % 11;
            if ($digit === 10) {
                $digit = 0;
            }
            if ($digit !== (int) $cpf[$position]) {
                return false;
            }
        }

        return true;
    }
}
