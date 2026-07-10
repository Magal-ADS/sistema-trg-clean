<?php

namespace App\Http\Controllers;

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
    public function index(Request $request): View|Response
    {
        $seller = $this->currentSeller($request);

        if (! $seller) {
            return $this->noStoreView('launches.seller-login', [
                'sellers' => SellerAccount::query()
                    ->select(['id', 'name', 'city', 'state'])
                    ->where('is_active', true)
                    ->orderBy('name')
                    ->get(),
            ]);
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

    public function login(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'seller_account_id' => ['required', 'integer', 'exists:seller_accounts,id'],
            'password' => ['required', 'string'],
        ]);

        $seller = SellerAccount::query()
            ->where('is_active', true)
            ->find($validated['seller_account_id']);

        if (! $seller || ! Hash::check($validated['password'], $seller->password)) {
            return back()->withErrors(['password' => 'Vendedor ou senha inválida.'])->withInput();
        }

        $request->session()->put('launch_seller_id', $seller->id);
        $request->session()->regenerate();

        return redirect()->route('launches.index');
    }

    public function store(Request $request): RedirectResponse
    {
        $seller = $this->currentSeller($request);

        abort_unless($seller, 403);

        $validated = $this->entryData($request);

        $seller->dailyEntries()->create([
            'entry_date' => $validated['entry_date'],
            'presential_count' => (int) ($validated['presential_count'] ?? 0),
            'instagram_count' => (int) ($validated['instagram_count'] ?? 0),
            'whatsapp_count' => (int) ($validated['whatsapp_count'] ?? 0),
            'sales_count' => (int) ($validated['sales_count'] ?? 0),
            'sales_total' => $this->money($validated['sales_total'] ?? '0'),
            'notes' => null,
        ]);

        return redirect()->route('launches.index')->with('status', 'Lançamento salvo com sucesso.');
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
            'notes' => null,
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

        return redirect()->route('launches.index');
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

    private function entryData(Request $request): array
    {
        return $request->validate([
            'entry_date' => ['required', 'date'],
            'presential_count' => ['nullable', 'integer', 'min:0', 'max:99999'],
            'instagram_count' => ['nullable', 'integer', 'min:0', 'max:99999'],
            'whatsapp_count' => ['nullable', 'integer', 'min:0', 'max:99999'],
            'sales_count' => ['nullable', 'integer', 'min:0', 'max:99999'],
            'sales_total' => ['nullable', 'string', 'max:30'],
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
