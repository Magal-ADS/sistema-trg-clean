<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\City;
use App\Models\Product;
use App\Models\SizeFragrancePrice;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

        $variationIds = $product->variationPrices()->pluck('id');

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
            ->where('session_id', $request->session()->getId())
            ->get();

        if ($items->isEmpty()) {
            return redirect()
                ->route('cart.index')
                ->withErrors(['cart' => 'Adicione pelo menos um produto antes de fechar o pedido.']);
        }

        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_cpf' => ['required', 'string', 'max:20'],
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

        $customer = [
            'name' => $validated['customer_name'],
            'cpf' => $validated['customer_cpf'],
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

        foreach ($items as $item) {
            $metadata = $item->metadata ?? [];
            $metadata['customer'] = $customer;
            $metadata['checkout_confirmed_at'] = now()->toISOString();

            $item->forceFill(['metadata' => $metadata])->save();
        }

        return redirect()
            ->route('cart.index')
            ->with('status', 'Dados do pedido confirmados.');
    }
}
