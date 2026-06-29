<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
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
        return view('cart.index', [
            'items' => CartItem::query()
                ->select(['id', 'product_id', 'product_name', 'size', 'color', 'fragrance', 'quantity', 'unit_price', 'total', 'created_at'])
                ->with('product:id,name,slug,image_url')
                ->latest()
                ->paginate(30),
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

        return redirect()
            ->route('cart.index')
            ->with('status', "{$product->name} foi adicionado ao carrinho.");
    }
}
