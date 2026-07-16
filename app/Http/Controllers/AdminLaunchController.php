<?php

namespace App\Http\Controllers;

use App\Models\LaunchAdminAccount;
use App\Models\City;
use App\Models\SellerAccount;
use App\Models\SellerDailyEntry;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminLaunchController extends Controller
{
    public function loginForm(Request $request): View|RedirectResponse
    {
        if ($this->isAdminLogged($request)) {
            return redirect()->route('launches.admin.dashboard');
        }

        return redirect()->route('launches.login.form');
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

        if (! $admin || ! Hash::check($validated['password'], $admin->password)) {
            return back()->withErrors(['email' => 'E-mail ou senha inválidos.'])->withInput();
        }

        $request->session()->forget('launch_seller_id');
        $request->session()->put('launch_admin_id', $admin->id);
        $request->session()->regenerate();

        return redirect()->route('launches.admin.dashboard');
    }

    public function dashboard(Request $request): View|RedirectResponse
    {
        if (! $this->isAdminLogged($request)) {
            return redirect()->route('launches.login.form');
        }

        return view('launches.admin-dashboard');
    }

    public function entries(Request $request): View|RedirectResponse
    {
        if (! $this->isAdminLogged($request)) {
            return redirect()->route('launches.login.form');
        }

        $dateFrom = $request->string('date_from')->toString() ?: now()->startOfMonth()->toDateString();
        $dateTo = $request->string('date_to')->toString() ?: now()->toDateString();
        $sellerId = $request->integer('seller_id') ?: null;

        $entriesQuery = SellerDailyEntry::query()
            ->with('seller:id,name,city,state')
            ->whereBetween('entry_date', [$dateFrom, $dateTo])
            ->when($sellerId, fn ($query) => $query->where('seller_account_id', $sellerId));

        $summaryEntries = (clone $entriesQuery)->get();

        return view('launches.admin-entries', [
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'sellerId' => $sellerId,
            'sellers' => SellerAccount::query()->orderBy('name')->get(),
            'entries' => $entriesQuery
                ->latest('entry_date')
                ->latest('id')
                ->paginate(30)
                ->withQueryString(),
            'salesCount' => $summaryEntries->sum('sales_count'),
            'salesTotal' => $summaryEntries->sum(fn (SellerDailyEntry $entry): float => (float) $entry->sales_total),
            'opportunities' => $summaryEntries->sum(fn (SellerDailyEntry $entry): int => $entry->presential_count + $entry->instagram_count + $entry->whatsapp_count),
        ]);
    }

    public function sellers(Request $request): View|RedirectResponse
    {
        if (! $this->isAdminLogged($request)) {
            return redirect()->route('launches.login.form');
        }

        return view('launches.admin-sellers', [
            'sellers' => SellerAccount::query()
                ->with('cityRecord:id,name,state')
                ->orderBy('name')
                ->paginate(30),
        ]);
    }

    public function createSeller(Request $request): View|RedirectResponse
    {
        if (! $this->isAdminLogged($request)) {
            return redirect()->route('launches.login.form');
        }

        return view('launches.admin-seller-form', [
            'seller' => new SellerAccount(),
            'cities' => City::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function storeSeller(Request $request): RedirectResponse
    {
        if (! $this->isAdminLogged($request)) {
            return redirect()->route('launches.login.form');
        }

        $validated = $this->sellerData($request);

        SellerAccount::query()->create($this->sellerPayload($validated));

        return redirect()->route('launches.admin.sellers')->with('status', 'Vendedor cadastrado.');
    }

    public function editSeller(Request $request, SellerAccount $seller): View|RedirectResponse
    {
        if (! $this->isAdminLogged($request)) {
            return redirect()->route('launches.login.form');
        }

        return view('launches.admin-seller-form', [
            'seller' => $seller,
            'cities' => City::query()
                ->where(function ($query) use ($seller): void {
                    $query->where('is_active', true)
                        ->orWhereKey($seller->city_id);
                })
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function updateSeller(Request $request, SellerAccount $seller): RedirectResponse
    {
        if (! $this->isAdminLogged($request)) {
            return redirect()->route('launches.login.form');
        }

        $validated = $this->sellerData($request, $seller);

        if (blank($validated['password'] ?? null)) {
            unset($validated['password']);
        }

        $seller->update($this->sellerPayload($validated));

        return redirect()->route('launches.admin.sellers')->with('status', 'Vendedor atualizado.');
    }

    public function cities(Request $request): View|RedirectResponse
    {
        if (! $this->isAdminLogged($request)) {
            return redirect()->route('launches.login.form');
        }

        return view('launches.admin-cities', [
            'cities' => City::query()
                ->withCount('sellers')
                ->orderBy('name')
                ->paginate(30),
        ]);
    }

    public function createCity(Request $request): View|RedirectResponse
    {
        if (! $this->isAdminLogged($request)) {
            return redirect()->route('launches.login.form');
        }

        return view('launches.admin-city-form', ['city' => new City()]);
    }

    public function storeCity(Request $request): RedirectResponse
    {
        if (! $this->isAdminLogged($request)) {
            return redirect()->route('launches.login.form');
        }

        City::query()->create($this->cityData($request));

        return redirect()->route('launches.admin.cities')->with('status', 'Cidade cadastrada.');
    }

    public function editCity(Request $request, City $city): View|RedirectResponse
    {
        if (! $this->isAdminLogged($request)) {
            return redirect()->route('launches.login.form');
        }

        return view('launches.admin-city-form', ['city' => $city]);
    }

    public function updateCity(Request $request, City $city): RedirectResponse
    {
        if (! $this->isAdminLogged($request)) {
            return redirect()->route('launches.login.form');
        }

        $city->update($this->cityData($request, $city));

        SellerAccount::query()
            ->where('city_id', $city->id)
            ->update([
                'city' => $city->name,
                'state' => $city->state,
            ]);

        return redirect()->route('launches.admin.cities')->with('status', 'Cidade atualizada.');
    }

    public function admins(Request $request): View|RedirectResponse
    {
        if (! $this->isAdminLogged($request)) {
            return redirect()->route('launches.login.form');
        }

        return view('launches.admin-admins', [
            'admins' => LaunchAdminAccount::query()->orderBy('name')->paginate(30),
            'currentAdminId' => $request->session()->get('launch_admin_id'),
        ]);
    }

    public function createAdmin(Request $request): View|RedirectResponse
    {
        if (! $this->isAdminLogged($request)) {
            return redirect()->route('launches.login.form');
        }

        return view('launches.admin-admin-form', ['admin' => new LaunchAdminAccount()]);
    }

    public function storeAdmin(Request $request): RedirectResponse
    {
        if (! $this->isAdminLogged($request)) {
            return redirect()->route('launches.login.form');
        }

        LaunchAdminAccount::query()->create($this->adminData($request));

        return redirect()->route('launches.admin.admins')->with('status', 'Admin cadastrado.');
    }

    public function editAdmin(Request $request, LaunchAdminAccount $admin): View|RedirectResponse
    {
        if (! $this->isAdminLogged($request)) {
            return redirect()->route('launches.login.form');
        }

        return view('launches.admin-admin-form', [
            'admin' => $admin,
            'isCurrentAdmin' => $request->session()->get('launch_admin_id') === $admin->id,
        ]);
    }

    public function updateAdmin(Request $request, LaunchAdminAccount $admin): RedirectResponse
    {
        if (! $this->isAdminLogged($request)) {
            return redirect()->route('launches.login.form');
        }

        $validated = $this->adminData($request, $admin);

        if (blank($validated['password'] ?? null)) {
            unset($validated['password']);
        }

        if ($request->session()->get('launch_admin_id') === $admin->id) {
            $validated['is_active'] = true;
        }

        $admin->update($validated);

        return redirect()->route('launches.admin.admins')->with('status', 'Admin atualizado.');
    }

    public function editEntry(Request $request, SellerDailyEntry $entry): View|RedirectResponse
    {
        if (! $this->isAdminLogged($request)) {
            return redirect()->route('launches.login.form');
        }

        return view('launches.admin-entry-form', [
            'entry' => $entry->load('seller:id,name'),
            'sellers' => SellerAccount::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function updateEntry(Request $request, SellerDailyEntry $entry): RedirectResponse
    {
        if (! $this->isAdminLogged($request)) {
            return redirect()->route('launches.login.form');
        }

        $validated = $request->validate([
            'seller_account_id' => ['required', 'integer', 'exists:seller_accounts,id'],
            'entry_date' => [
                'required',
                'date',
                Rule::unique('seller_daily_entries', 'entry_date')
                    ->where(fn ($query) => $query->where('seller_account_id', $request->integer('seller_account_id')))
                    ->ignore($entry),
            ],
            'presential_count' => ['nullable', 'integer', 'min:0', 'max:99999'],
            'instagram_count' => ['nullable', 'integer', 'min:0', 'max:99999'],
            'whatsapp_count' => ['nullable', 'integer', 'min:0', 'max:99999'],
            'sales_count' => ['nullable', 'integer', 'min:0', 'max:99999'],
            'sales_total' => ['nullable', 'string', 'max:30'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $entry->update([
            'seller_account_id' => $validated['seller_account_id'],
            'entry_date' => $validated['entry_date'],
            'presential_count' => (int) ($validated['presential_count'] ?? 0),
            'instagram_count' => (int) ($validated['instagram_count'] ?? 0),
            'whatsapp_count' => (int) ($validated['whatsapp_count'] ?? 0),
            'sales_count' => (int) ($validated['sales_count'] ?? 0),
            'sales_total' => $this->money($validated['sales_total'] ?? '0'),
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('launches.admin.entries.index')->with('status', 'Lançamento atualizado.');
    }

    public function destroyEntry(Request $request, SellerDailyEntry $entry): RedirectResponse
    {
        if (! $this->isAdminLogged($request)) {
            return redirect()->route('launches.login.form');
        }

        $entry->delete();

        return redirect()->route('launches.admin.entries.index')->with('status', 'Lançamento excluído.');
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget('launch_admin_id');
        $request->session()->regenerate();

        return redirect()->route('launches.login.form');
    }

    private function sellerData(Request $request, ?SellerAccount $seller = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('seller_accounts', 'email')->ignore($seller)],
            'phone' => ['nullable', 'string', 'max:50'],
            'city_id' => ['required', 'integer', 'exists:cities,id'],
            'password' => [$seller ? 'nullable' : 'required', 'string', 'min:4', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'name.required' => 'Informe o nome do vendedor.',
            'email.email' => 'Informe um e-mail valido.',
            'email.unique' => 'Ja existe um vendedor usando este e-mail.',
            'city_id.required' => 'Selecione a cidade do vendedor.',
            'city_id.exists' => 'A cidade selecionada e invalida.',
            'password.required' => 'Informe uma senha ou PIN para o vendedor.',
            'password.min' => 'A senha ou PIN precisa ter pelo menos 4 caracteres.',
            'password.max' => 'A senha ou PIN pode ter no maximo 255 caracteres.',
            'is_active.boolean' => 'O status do vendedor e invalido.',
        ]) + ['is_active' => false];
    }

    private function sellerPayload(array $validated): array
    {
        $city = City::query()->find($validated['city_id']);

        return $validated + [
            'city' => $city?->name,
            'state' => $city?->state,
        ];
    }

    private function cityData(Request $request, ?City $city = null): array
    {
        return $request->validate([
            'name' => [
                'required',
                'string',
                'max:120',
                Rule::unique('cities', 'name')
                    ->where(fn ($query) => $query->where('state', $request->input('state')))
                    ->ignore($city),
            ],
            'state' => ['nullable', 'string', 'max:50'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'name.required' => 'Informe o nome da cidade.',
            'name.unique' => 'Esta cidade ja esta cadastrada para este estado.',
            'is_active.boolean' => 'O status da cidade e invalido.',
        ]) + ['is_active' => false];
    }

    private function adminData(Request $request, ?LaunchAdminAccount $admin = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('launch_admin_accounts', 'email')->ignore($admin)],
            'password' => [$admin ? 'nullable' : 'required', 'string', 'min:4', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'name.required' => 'Informe o nome do admin.',
            'email.required' => 'Informe o e-mail do admin.',
            'email.email' => 'Informe um e-mail valido.',
            'email.unique' => 'Ja existe um admin usando este e-mail.',
            'password.required' => 'Informe uma senha para o admin.',
            'password.min' => 'A senha precisa ter pelo menos 4 caracteres.',
            'password.max' => 'A senha pode ter no maximo 255 caracteres.',
            'is_active.boolean' => 'O status do admin e invalido.',
        ]) + ['is_active' => false];
    }

    private function isAdminLogged(Request $request): bool
    {
        $adminId = $request->session()->get('launch_admin_id');

        return $adminId && LaunchAdminAccount::query()
            ->where('is_active', true)
            ->whereKey($adminId)
            ->exists();
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
