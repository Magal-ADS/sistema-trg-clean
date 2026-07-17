<?php

namespace App\Http\Controllers;

use App\Models\LaunchAdminAccount;
use App\Models\Order;
use App\Models\SellerAccount;
use App\Models\SellerDailyEntry;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;

class SellerLaunchController extends Controller
{
    public function loginForm(Request $request): View|RedirectResponse
    {
        if ($this->currentSeller($request)) {
            return redirect()->route('launches.index');
        }

        if ($this->currentAdmin($request)) {
            return redirect()->route('launches.admin.dashboard');
        }

        return view('launches.login');
    }

    public function index(Request $request): View|RedirectResponse|Response
    {
        $seller = $this->currentSeller($request);

        if (! $seller) {
            return redirect()->route('launches.login.form');
        }

        $monthStart = now()->startOfMonth()->toDateString();
        $monthEnd = now()->endOfMonth()->toDateString();

        $monthEntries = $seller->dailyEntries()
            ->whereBetween('entry_date', [$monthStart, $monthEnd])
            ->get();

        return $this->noStoreView('launches.seller-dashboard', [
            'seller' => $seller,
            'today' => now()->toDateString(),
            'monthSalesCount' => $monthEntries->sum('sales_count'),
            'monthSalesTotal' => $monthEntries->sum(fn (SellerDailyEntry $entry): float => (float) $entry->sales_total),
            'monthOpportunities' => $monthEntries->sum(fn (SellerDailyEntry $entry): int => $entry->presential_count + $entry->instagram_count + $entry->whatsapp_count),
            'entries' => $seller->dailyEntries()
                ->latest('entry_date')
                ->latest('id')
                ->limit(10)
                ->get(),
        ]);
    }

    public function orders(Request $request): RedirectResponse|Response
    {
        $seller = $this->currentSeller($request);

        if (! $seller) {
            return redirect()->route('launches.login.form');
        }

        $seller->load('cityRecord:id,name,state');

        $orders = Order::query()
            ->with(['items', 'city:id,name,state'])
            ->where('city_id', $seller->city_id)
            ->latest('id')
            ->paginate(10);

        return $this->noStoreView('launches.seller-orders', [
            'seller' => $seller,
            'orders' => $orders,
            'statusLabels' => [
                'pending' => 'Pendente',
                'confirmed' => 'Confirmado',
                'preparing' => 'Em separacao',
                'delivering' => 'Em entrega',
                'completed' => 'Finalizado',
                'cancelled' => 'Cancelado',
            ],
        ]);
    }

    public function login(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $admin = LaunchAdminAccount::query()
            ->where('email', $validated['email'])
            ->where('is_active', true)
            ->first();

        if ($admin && Hash::check($validated['password'], $admin->password)) {
            $request->session()->forget('launch_seller_id');
            $request->session()->put('launch_admin_id', $admin->id);
            $request->session()->regenerate();

            return redirect()->route('launches.admin.dashboard');
        }

        $seller = SellerAccount::query()
            ->where('email', $validated['email'])
            ->where('is_active', true)
            ->first();

        if (! $seller || ! Hash::check($validated['password'], $seller->password)) {
            return back()->withErrors(['email' => 'E-mail ou senha invalidos.'])->withInput();
        }

        $request->session()->forget('launch_admin_id');
        $request->session()->put('launch_seller_id', $seller->id);
        $request->session()->regenerate();

        return redirect()->route('launches.index');
    }

    public function store(Request $request): RedirectResponse
    {
        $seller = $this->currentSeller($request);

        abort_unless($seller, 403);

        $validated = $this->entryData($request);

        $seller->dailyEntries()->updateOrCreate(['entry_date' => $validated['entry_date']], [
            'presential_count' => (int) ($validated['presential_count'] ?? 0),
            'instagram_count' => (int) ($validated['instagram_count'] ?? 0),
            'whatsapp_count' => (int) ($validated['whatsapp_count'] ?? 0),
            'sales_count' => (int) ($validated['sales_count'] ?? 0),
            'sales_total' => $this->money($validated['sales_total'] ?? '0'),
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('launches.index')->with('status', 'Lancamento salvo com sucesso.');
    }

    public function edit(Request $request, SellerDailyEntry $entry): View|RedirectResponse|Response
    {
        $seller = $this->currentSeller($request);

        if (! $seller) {
            return redirect()->route('launches.index');
        }

        abort_unless($entry->seller_account_id === $seller->id, 403);

        return $this->noStoreView('launches.seller-entry-form', [
            'entry' => $entry,
            'seller' => $seller,
        ]);
    }

    public function update(Request $request, SellerDailyEntry $entry): RedirectResponse
    {
        $seller = $this->currentSeller($request);

        abort_unless($seller && $entry->seller_account_id === $seller->id, 403);

        $validated = $this->entryData($request);

        $entry->update([
            'entry_date' => $validated['entry_date'],
            'presential_count' => (int) ($validated['presential_count'] ?? 0),
            'instagram_count' => (int) ($validated['instagram_count'] ?? 0),
            'whatsapp_count' => (int) ($validated['whatsapp_count'] ?? 0),
            'sales_count' => (int) ($validated['sales_count'] ?? 0),
            'sales_total' => $this->money($validated['sales_total'] ?? '0'),
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('launches.index')->with('status', 'Lancamento atualizado com sucesso.');
    }

    public function destroy(Request $request, SellerDailyEntry $entry): RedirectResponse|JsonResponse
    {
        $seller = $this->currentSeller($request);

        abort_unless($seller && $entry->seller_account_id === $seller->id, 403);

        $entry->delete();

        if ($request->expectsJson()) {
            return response()->json(['status' => 'deleted']);
        }

        return redirect()->route('launches.index')->with('status', 'Lancamento excluido com sucesso.');
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget('launch_seller_id');
        $request->session()->regenerate();

        return redirect()->route('launches.login.form');
    }

    private function currentSeller(Request $request): ?SellerAccount
    {
        $sellerId = $request->session()->get('launch_seller_id');

        if (! $sellerId) {
            return null;
        }

        return SellerAccount::query()
            ->where('is_active', true)
            ->find($sellerId);
    }

    private function currentAdmin(Request $request): ?LaunchAdminAccount
    {
        $adminId = $request->session()->get('launch_admin_id');

        if (! $adminId) {
            return null;
        }

        return LaunchAdminAccount::query()
            ->where('is_active', true)
            ->find($adminId);
    }

    private function entryData(Request $request): array
    {
        return $request->validate([
            'entry_date' => ['required', 'date'],
            'presential_count' => ['nullable', 'integer', 'min:0', 'max:99999'],
            'instagram_count' => ['nullable', 'integer', 'min:0', 'max:99999'],
            'whatsapp_count' => ['nullable', 'integer', 'min:0', 'max:99999'],
            'sales_count' => ['nullable', 'integer', 'min:0', 'max:99999'],
            'sales_total' => ['nullable', 'string', 'max:30'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);
    }

    private function noStoreView(string $view, array $data = []): Response
    {
        return response()
            ->view($view, $data)
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    private function money(string $value): float
    {
        $normalized = str_replace(['R$', ' '], '', $value);

        if (str_contains($normalized, ',')) {
            $normalized = str_replace('.', '', $normalized);
            $normalized = str_replace(',', '.', $normalized);
        }

        return max(0, (float) preg_replace('/[^\d.-]/', '', $normalized));
    }
}
