<?php

use App\Http\Controllers\CartItemController;
use App\Http\Controllers\AdminLaunchController;
use App\Http\Controllers\AdminCatalogController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SellerLaunchController;
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
            ->orderBy('sort_order')
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
Route::post('/produtos/{product:slug}/carrinho', [CartItemController::class, 'store'])->name('cart.store');
Route::get('/carrinho', [CartItemController::class, 'index'])->name('cart.index');
Route::get('/pedidos', [OrderController::class, 'index'])->name('orders.index');

Route::get('/lancamentos', [SellerLaunchController::class, 'index'])->name('launches.index');
Route::get('/lancamentos/login', [SellerLaunchController::class, 'loginForm'])->name('launches.login.form');
Route::post('/lancamentos/login', [SellerLaunchController::class, 'login'])->name('launches.login');
Route::post('/lancamentos', [SellerLaunchController::class, 'store'])->name('launches.store');
Route::get('/lancamentos/{entry}/editar', [SellerLaunchController::class, 'edit'])->name('launches.entries.edit');
Route::put('/lancamentos/{entry}', [SellerLaunchController::class, 'update'])->name('launches.entries.update');
Route::delete('/lancamentos/{entry}', [SellerLaunchController::class, 'destroy'])->name('launches.entries.destroy');
Route::post('/lancamentos/logout', [SellerLaunchController::class, 'logout'])->name('launches.logout');

Route::redirect('/app-lancamentos', '/lancamentos')->name('launches.shortcut');
Route::redirect('/admin-lancamentos', '/admin/lancamentos')->name('launches.admin.shortcut');

Route::prefix('admin/lancamentos')->name('launches.admin.')->group(function (): void {
    Route::get('/login', [AdminLaunchController::class, 'loginForm'])->name('login');
    Route::post('/login', [AdminLaunchController::class, 'login'])->name('login.store');
    Route::post('/logout', [AdminLaunchController::class, 'logout'])->name('logout');

    Route::get('/', [AdminLaunchController::class, 'dashboard'])->name('dashboard');
    Route::get('/entradas', [AdminLaunchController::class, 'entries'])->name('entries.index');
    Route::get('/configuracoes-catalogo', [AdminCatalogController::class, 'settings'])->name('catalog.settings');
    Route::redirect('/vendedoras', '/admin/lancamentos/vendedores')->name('sellers.legacy');
    Route::redirect('/vendedoras/nova', '/admin/lancamentos/vendedores/novo')->name('sellers.create.legacy');
    Route::get('/vendedores', [AdminLaunchController::class, 'sellers'])->name('sellers');
    Route::get('/vendedores/novo', [AdminLaunchController::class, 'createSeller'])->name('sellers.create');
    Route::post('/vendedores', [AdminLaunchController::class, 'storeSeller'])->name('sellers.store');
    Route::get('/vendedores/{seller}/editar', [AdminLaunchController::class, 'editSeller'])->name('sellers.edit');
    Route::put('/vendedores/{seller}', [AdminLaunchController::class, 'updateSeller'])->name('sellers.update');

    Route::get('/admins', [AdminLaunchController::class, 'admins'])->name('admins');
    Route::get('/admins/novo', [AdminLaunchController::class, 'createAdmin'])->name('admins.create');
    Route::post('/admins', [AdminLaunchController::class, 'storeAdmin'])->name('admins.store');
    Route::get('/admins/{admin}/editar', [AdminLaunchController::class, 'editAdmin'])->name('admins.edit');
    Route::put('/admins/{admin}', [AdminLaunchController::class, 'updateAdmin'])->name('admins.update');

    Route::get('/entradas/{entry}/editar', [AdminLaunchController::class, 'editEntry'])->name('entries.edit');
    Route::put('/entradas/{entry}', [AdminLaunchController::class, 'updateEntry'])->name('entries.update');
    Route::delete('/entradas/{entry}', [AdminLaunchController::class, 'destroyEntry'])->name('entries.destroy');

    Route::prefix('modulos/{module}')->name('modules.')->group(function (): void {
        Route::get('/', [AdminCatalogController::class, 'index'])->name('index');
        Route::get('/novo', [AdminCatalogController::class, 'create'])->name('create');
        Route::post('/', [AdminCatalogController::class, 'store'])->name('store');
        Route::get('/{id}/editar', [AdminCatalogController::class, 'edit'])->name('edit');
        Route::put('/{id}', [AdminCatalogController::class, 'update'])->name('update');
        Route::delete('/{id}', [AdminCatalogController::class, 'destroy'])->name('destroy');
    });
});
