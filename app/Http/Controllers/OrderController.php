<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $orders = collect();
        $searched = $request->filled('customer_cpf') || $request->filled('customer_phone');
        $validated = [];

        if ($searched) {
            $validated = $request->validate([
                'customer_cpf' => ['required', 'string', 'max:20'],
                'customer_phone' => ['required', 'string', 'max:30'],
            ], [
                'customer_cpf.required' => 'Informe o CPF.',
                'customer_phone.required' => 'Informe o telefone usado no pedido.',
            ]);

            $cpf = $this->onlyDigits($validated['customer_cpf']);
            $phone = $this->onlyDigits($validated['customer_phone']);

            $orders = Order::query()
                ->with(['items', 'city'])
                ->where('customer_cpf', $cpf)
                ->whereRaw("regexp_replace(coalesce(customer_phone, ''), '[^0-9]', '', 'g') = ?", [$phone])
                ->latest()
                ->paginate(10)
                ->withQueryString();
        }

        return view('orders.index', [
            'orders' => $orders,
            'searched' => $searched,
            'statusLabels' => $this->statusLabels(),
            'customerCpf' => $validated['customer_cpf'] ?? '',
            'customerPhone' => $validated['customer_phone'] ?? '',
        ]);
    }

    private function onlyDigits(string $value): string
    {
        return preg_replace('/\D+/', '', $value) ?: $value;
    }

    private function statusLabels(): array
    {
        return [
            'pending' => 'Pendente',
            'confirmed' => 'Confirmado',
            'preparing' => 'Em separacao',
            'delivering' => 'Em entrega',
            'completed' => 'Finalizado',
            'cancelled' => 'Cancelado',
        ];
    }
}
