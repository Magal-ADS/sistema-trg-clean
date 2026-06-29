<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use Illuminate\Contracts\View\View;

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
}
