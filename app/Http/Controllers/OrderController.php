<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Contracts\View\View;

class OrderController extends Controller
{
    public function index(): View
    {
        return view('orders.index', [
            'orders' => Order::query()
                ->select(['id', 'code', 'customer_name', 'customer_email', 'status', 'total', 'confirmed_at', 'created_at'])
                ->latest()
                ->paginate(20),
        ]);
    }
}
