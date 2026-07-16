<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerLookupController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'cpf' => ['required', 'string', 'max:20'],
            'phone' => ['required', 'string', 'max:30'],
        ]);

        $cpf = $this->onlyDigits($validated['cpf']);
        $phone = $this->onlyDigits($validated['phone']);

        if (! $this->isValidCpf($cpf) || strlen($phone) < 10) {
            return response()->json(['message' => 'CPF ou telefone invalidos.'], 422);
        }

        $order = Order::query()
            ->where('customer_cpf', $cpf)
            ->whereRaw($this->phoneDigitsExpression('customer_phone').' = ?', [$phone])
            ->latest()
            ->first();

        if ($order) {
            return response()->json([
                'found' => true,
                'customer' => [
                    'name' => $order->customer_name,
                    'cpf' => $order->customer_cpf,
                    'phone' => $order->customer_phone,
                    'address' => $order->address,
                    'reference' => $order->complement,
                    'type' => $order->customer_type,
                    'city_id' => $order->city_id,
                    'fulfillment_type' => $order->delivery_type,
                    'payment_method' => $order->payment_method,
                ],
            ]);
        }

        $formattedCpf = $this->formatCpf($cpf);
        $user = User::query()
            ->whereIn('cpf', array_unique([$cpf, $formattedCpf, $validated['cpf']]))
            ->whereRaw($this->phoneDigitsExpression('phone').' = ?', [$phone])
            ->latest()
            ->first();

        if (! $user) {
            return response()->json(['found' => false], 404);
        }

        $city = $user->city
            ? City::query()
                ->where('is_active', true)
                ->where('name', $user->city)
                ->first(['id'])
            : null;

        return response()->json([
            'found' => true,
            'customer' => [
                'name' => $user->name,
                'cpf' => $user->cpf,
                'phone' => $user->phone,
                'address' => $user->address,
                'reference' => $user->reference,
                'type' => data_get($user->metadata, 'customer_type', 'Casa'),
                'city_id' => $city?->id,
                'city' => $user->city,
                'fulfillment_type' => 'Entrega',
                'payment_method' => 'Pix',
            ],
        ]);
    }

    private function onlyDigits(string $value): string
    {
        return preg_replace('/\D+/', '', $value) ?: $value;
    }

    private function formatCpf(string $value): string
    {
        $digits = $this->onlyDigits($value);

        if (strlen($digits) !== 11) {
            return $value;
        }

        return substr($digits, 0, 3).'.'.substr($digits, 3, 3).'.'.substr($digits, 6, 3).'-'.substr($digits, 9, 2);
    }

    private function phoneDigitsExpression(string $column): string
    {
        return "replace(replace(replace(replace(replace(replace(coalesce({$column}, ''), '(', ''), ')', ''), ' ', ''), '-', ''), '+', ''), '.', '')";
    }

    private function isValidCpf(string $cpf): bool
    {
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
