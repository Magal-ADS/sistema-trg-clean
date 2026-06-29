<?php

use App\Http\Controllers\CartItemController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home', [
        'banners' => Banner::query()
            ->select(['id', 'title', 'subtitle', 'image_url', 'link_url'])
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->limit(4)
            ->get(),
        'categories' => Category::query()
            ->select(['id', 'name', 'slug'])
            ->where('is_active', true)
            ->orderBy('name')
            ->limit(10)
            ->get(),
        'featuredProducts' => Product::query()
            ->select(['id', 'category_id', 'name', 'slug', 'price', 'promotional_price', 'image_url', 'is_featured', 'is_best_seller', 'is_unavailable'])
            ->with('category:id,name,slug')
            ->where('is_unavailable', false)
            ->orderByDesc('is_featured')
            ->orderByDesc('is_best_seller')
            ->limit(12)
            ->get(),
    ]);
})->name('home');

Route::get('/produtos', [ProductController::class, 'index'])->name('products.index');
Route::get('/produtos/{product:slug}', [ProductController::class, 'show'])->name('products.show');
Route::get('/carrinho', [CartItemController::class, 'index'])->name('cart.index');
Route::get('/pedidos', [OrderController::class, 'index'])->name('orders.index');
