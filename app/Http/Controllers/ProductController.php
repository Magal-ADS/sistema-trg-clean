<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $products = Product::query()
            ->where('is_unavailable', false)
            ->select([
                'id',
                'category_id',
                'sub_category_id',
                'size_category_id',
                'sku',
                'name',
                'slug',
                'description',
                'price',
                'promotional_price',
                'has_variation',
                'is_featured',
                'is_best_seller',
                'is_unavailable',
                'stock',
                'image_url',
            ])
            ->with(['category:id,name,slug', 'subCategory:id,name,slug'])
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = $request->string('search')->toString();

                $query->where(function ($query) use ($search): void {
                    $query->whereRaw('lower(name) like ?', ['%'.mb_strtolower($search).'%'])
                        ->orWhereRaw('lower(description) like ?', ['%'.mb_strtolower($search).'%'])
                        ->orWhereRaw('lower(sku) like ?', ['%'.mb_strtolower($search).'%']);
                });
            })
            ->when($request->filled('category'), function ($query) use ($request): void {
                $query->whereHas('category', fn ($category) => $category->where('slug', $request->string('category')));
            })
            ->orderByDesc('is_featured')
            ->orderBy('name')
            ->paginate(24)
            ->withQueryString();

        return view('products.index', [
            'categories' => Category::query()
                ->select(['id', 'name', 'slug'])
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
            'products' => $products,
        ]);
    }

    public function show(Product $product): View
    {
        abort_if($product->is_unavailable, 404);

        return view('products.show', [
            'product' => $product->load([
                'category:id,name,slug',
                'subCategory:id,name,slug',
                'variationPrices' => fn ($query) => $query->where('is_active', true)
                    ->select(['id', 'product_id', 'size_id', 'fragrance_type_id', 'color_type_id', 'price', 'promotional_price', 'stock']),
                'variationPrices.size:id,name',
                'variationPrices.fragranceType:id,name',
                'variationPrices.colorType:id,name,hex',
            ]),
        ]);
    }
}
