<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\City;
use App\Models\ColorType;
use App\Models\Coupon;
use App\Models\FragranceType;
use App\Models\LaunchAdminAccount;
use App\Models\Order;
use App\Models\Product;
use App\Models\Service;
use App\Models\Size;
use App\Models\SizeCategory;
use App\Models\SizeFragrancePrice;
use App\Models\SubCategory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminCatalogController extends Controller
{
    public function settings(Request $request): View|RedirectResponse
    {
        if (! $this->isAdminLogged($request)) {
            return redirect()->route('launches.login.form');
        }

        return view('launches.admin-catalog-settings', [
            'settingsItems' => self::settingsMenu(),
        ]);
    }

    public function index(Request $request, string $module): View|RedirectResponse
    {
        if (! $this->isAdminLogged($request)) {
            return redirect()->route('launches.login.form');
        }

        $config = $this->module($module);
        $query = $this->baseQuery($module, $config);

        if ($search = $request->string('search')->trim()->toString()) {
            $query->where(function (Builder $query) use ($config, $search): void {
                foreach ($config['search'] as $column) {
                    $query->orWhere($column, 'ilike', "%{$search}%");
                }
            });
        }

        return view('launches.admin-module-index', [
            'module' => $module,
            'config' => $config,
            'items' => $query->paginate(30)->withQueryString(),
            'search' => $search ?? '',
        ]);
    }

    public function create(Request $request, string $module): View|RedirectResponse
    {
        if (! $this->isAdminLogged($request)) {
            return redirect()->route('launches.login.form');
        }

        $config = $this->module($module);
        abort_if(($config['readonly'] ?? false), 404);

        return view('launches.admin-module-form', [
            'module' => $module,
            'config' => $config,
            'item' => new $config['model'](),
            'options' => $this->options($config),
        ]);
    }

    public function store(Request $request, string $module): RedirectResponse
    {
        if (! $this->isAdminLogged($request)) {
            return redirect()->route('launches.login.form');
        }

        $config = $this->module($module);
        abort_if(($config['readonly'] ?? false), 404);

        $data = $this->validatedData($request, $config);
        $config['model']::query()->create($data);

        return redirect()->route('launches.admin.modules.index', $module)->with('status', "{$config['singular']} cadastrado.");
    }

    public function edit(Request $request, string $module, int $id): View|RedirectResponse
    {
        if (! $this->isAdminLogged($request)) {
            return redirect()->route('launches.login.form');
        }

        $config = $this->module($module);
        $item = $this->baseQuery($module, $config)->findOrFail($id);

        return view('launches.admin-module-form', [
            'module' => $module,
            'config' => $config,
            'item' => $item,
            'options' => $this->options($config),
        ]);
    }

    public function update(Request $request, string $module, int $id): RedirectResponse
    {
        if (! $this->isAdminLogged($request)) {
            return redirect()->route('launches.login.form');
        }

        $config = $this->module($module);
        $item = $config['model']::query()->findOrFail($id);
        $item->update($this->validatedData($request, $config, $item));

        return redirect()->route('launches.admin.modules.index', $module)->with('status', "{$config['singular']} atualizado.");
    }

    public function destroy(Request $request, string $module, int $id): RedirectResponse
    {
        if (! $this->isAdminLogged($request)) {
            return redirect()->route('launches.login.form');
        }

        $config = $this->module($module);
        abort_if(($config['readonly'] ?? false), 404);

        $config['model']::query()->findOrFail($id)->delete();

        return redirect()->route('launches.admin.modules.index', $module)->with('status', "{$config['singular']} excluido.");
    }

    public static function menu(): array
    {
        return [
            ['label' => 'Gerenciamento de Pedidos', 'module' => 'orders'],
            ['label' => 'Cadastro de Categorias', 'module' => 'categories'],
            ['label' => 'Cadastro de Produtos', 'module' => 'products'],
            ['label' => 'Servicos de Limpeza', 'module' => 'services'],
            ['label' => 'Cupom de desconto', 'module' => 'coupons'],
        ];
    }

    public static function settingsMenu(): array
    {
        return [
            ['label' => 'Cadastro de Sub-Categorias', 'module' => 'sub-categories'],
            ['label' => 'Tipos de Tamanho', 'module' => 'size-categories'],
            ['label' => 'Tamanhos', 'module' => 'sizes'],
            ['label' => 'Tamanhos e Variacoes', 'module' => 'variation-prices'],
            ['label' => 'Tipos de Fragrancias', 'module' => 'fragrance-types'],
            ['label' => 'Fragrancias dos Produtos', 'module' => 'product-fragrances'],
            ['label' => 'Tipos de Cores', 'module' => 'color-types'],
            ['label' => 'Cores dos Produtos', 'module' => 'product-colors'],
        ];
    }

    private function module(string $module): array
    {
        $modules = [
            'orders' => [
                'title' => 'Gerenciamento de Pedidos',
                'singular' => 'Pedido',
                'model' => Order::class,
                'readonly' => false,
                'create' => false,
                'search' => ['code', 'customer_name', 'customer_email', 'customer_cpf', 'customer_phone'],
                'with' => ['items', 'city'],
                'columns' => [
                    'code' => 'Codigo',
                    'customer_name' => 'Cliente',
                    'customer_cpf' => 'CPF',
                    'customer_phone' => 'Telefone',
                    'city.name' => 'Cidade',
                    'status' => 'Status',
                    'payment_method' => 'Pagamento',
                    'total' => 'Total',
                    'confirmed_at' => 'Confirmado em',
                ],
                'fields' => [
                    'customer_name' => ['label' => 'Cliente', 'type' => 'text', 'rules' => ['required', 'string', 'max:255']],
                    'customer_email' => ['label' => 'E-mail', 'type' => 'email', 'rules' => ['nullable', 'email', 'max:255']],
                    'customer_cpf' => ['label' => 'CPF', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:20']],
                    'customer_phone' => ['label' => 'Telefone', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:255']],
                    'customer_type' => ['label' => 'Empresa ou casa', 'type' => 'select', 'rules' => ['nullable', Rule::in(['Empresa', 'Casa'])], 'options' => ['Casa' => 'Casa', 'Empresa' => 'Empresa']],
                    'city_id' => ['label' => 'Cidade', 'type' => 'relation', 'model' => City::class, 'rules' => ['nullable', 'integer', 'exists:cities,id']],
                    'status' => ['label' => 'Status', 'type' => 'select', 'rules' => ['required', 'string', 'max:50'], 'options' => ['pending' => 'Pendente', 'confirmed' => 'Confirmado', 'preparing' => 'Em separacao', 'delivering' => 'Em entrega', 'completed' => 'Finalizado', 'cancelled' => 'Cancelado']],
                    'delivery_type' => ['label' => 'Entrega ou retirada', 'type' => 'select', 'rules' => ['nullable', Rule::in(['Entrega', 'Retirar na Loja'])], 'options' => ['Entrega' => 'Entrega', 'Retirar na Loja' => 'Retirar na Loja']],
                    'payment_method' => ['label' => 'Forma de pagamento', 'type' => 'select', 'rules' => ['nullable', Rule::in(['Pix', 'Cartao'])], 'options' => ['Pix' => 'Pix', 'Cartao' => 'Cartao']],
                    'address' => ['label' => 'Endereco', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:255']],
                    'complement' => ['label' => 'Complemento', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:255']],
                    'subtotal' => ['label' => 'Subtotal', 'type' => 'money', 'rules' => ['nullable', 'numeric', 'min:0']],
                    'discount' => ['label' => 'Desconto', 'type' => 'money', 'rules' => ['nullable', 'numeric', 'min:0']],
                    'shipping' => ['label' => 'Frete', 'type' => 'money', 'rules' => ['nullable', 'numeric', 'min:0']],
                    'total' => ['label' => 'Total', 'type' => 'money', 'rules' => ['required', 'numeric', 'min:0']],
                ],
                'order' => ['created_at', 'desc'],
            ],
            'categories' => $this->namedModule('Cadastro de Categorias', 'Categoria', Category::class, ['image_url' => true, 'description' => true]),
            'sub-categories' => $this->namedModule('Cadastro de Sub-Categorias', 'Sub-categoria', SubCategory::class, ['category_id' => true, 'image_url' => true, 'description' => true]),
            'size-categories' => $this->namedModule('Tipos de Tamanho', 'Tipo de tamanho', SizeCategory::class, ['description' => true]),
            'sizes' => [
                'title' => 'Tamanhos',
                'singular' => 'Tamanho',
                'model' => Size::class,
                'search' => ['name', 'slug', 'unit'],
                'with' => ['sizeCategory'],
                'columns' => ['name' => 'Nome', 'sizeCategory.name' => 'Tipo', 'volume' => 'Volume', 'unit' => 'Unidade', 'is_active' => 'Ativo'],
                'fields' => [
                    'size_category_id' => ['label' => 'Tipo de tamanho', 'type' => 'relation', 'model' => SizeCategory::class, 'rules' => ['nullable', 'integer', 'exists:size_categories,id']],
                    'name' => ['label' => 'Nome', 'type' => 'text', 'rules' => ['required', 'string', 'max:255']],
                    'slug' => ['label' => 'Slug', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:255']],
                    'volume' => ['label' => 'Volume', 'type' => 'number', 'step' => '0.01', 'rules' => ['nullable', 'numeric', 'min:0']],
                    'unit' => ['label' => 'Unidade', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:50']],
                    'sort_order' => ['label' => 'Ordem', 'type' => 'number', 'rules' => ['nullable', 'integer', 'min:0']],
                    'is_active' => ['label' => 'Ativo', 'type' => 'checkbox', 'rules' => ['nullable', 'boolean']],
                ],
                'order' => ['sort_order', 'asc'],
            ],
            'fragrance-types' => $this->namedModule('Tipos de Fragrancias', 'Tipo de fragrancia', FragranceType::class, ['description' => true, 'sort_order' => false]),
            'color-types' => [
                'title' => 'Tipos de Cores',
                'singular' => 'Tipo de cor',
                'model' => ColorType::class,
                'search' => ['name', 'slug', 'hex'],
                'columns' => ['name' => 'Nome', 'hex' => 'Cor', 'is_active' => 'Ativo'],
                'fields' => [
                    'name' => ['label' => 'Nome', 'type' => 'text', 'rules' => ['required', 'string', 'max:255']],
                    'slug' => ['label' => 'Slug', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:255']],
                    'hex' => ['label' => 'Cor', 'type' => 'color', 'rules' => ['nullable', 'string', 'max:20']],
                    'is_active' => ['label' => 'Ativo', 'type' => 'checkbox', 'rules' => ['nullable', 'boolean']],
                ],
                'order' => ['name', 'asc'],
            ],
            'products' => $this->productModule('Cadastro de Produtos'),
            'featured-products' => $this->productModule('Produtos da Vitrine (Novidades)', ['is_featured' => true]),
            'best-sellers' => $this->productModule('Mais Vendidos (Temporario)', ['is_best_seller' => true]),
            'most-accessed-products' => $this->productModule('Produtos mais acessados'),
            'variation-prices' => $this->variationModule('Tamanhos e Variacoes'),
            'product-fragrances' => $this->variationModule('Fragrancias dos Produtos', ['fragrance_type_id']),
            'product-colors' => $this->variationModule('Cores dos Produtos', ['color_type_id']),
            'services' => [
                'title' => 'Servicos de Limpeza',
                'singular' => 'Servico',
                'model' => Service::class,
                'search' => ['name', 'slug', 'description'],
                'columns' => ['name' => 'Nome', 'price' => 'Preco', 'is_active' => 'Ativo'],
                'fields' => [
                    'name' => ['label' => 'Nome', 'type' => 'text', 'rules' => ['required', 'string', 'max:255']],
                    'slug' => ['label' => 'Slug', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:255']],
                    'description' => ['label' => 'Descricao', 'type' => 'textarea', 'rules' => ['nullable', 'string']],
                    'price' => ['label' => 'Preco', 'type' => 'money', 'rules' => ['nullable', 'numeric', 'min:0']],
                    'image_url' => ['label' => 'URL da imagem', 'type' => 'url', 'rules' => ['nullable', 'string']],
                    'is_active' => ['label' => 'Ativo', 'type' => 'checkbox', 'rules' => ['nullable', 'boolean']],
                ],
                'order' => ['name', 'asc'],
            ],
            'coupons' => [
                'title' => 'Cupom de desconto',
                'singular' => 'Cupom',
                'model' => Coupon::class,
                'search' => ['code', 'description'],
                'columns' => ['code' => 'Codigo', 'type' => 'Tipo', 'value' => 'Valor', 'expires_at' => 'Expira em', 'is_active' => 'Ativo'],
                'fields' => [
                    'code' => ['label' => 'Codigo', 'type' => 'text', 'rules' => ['required', 'string', 'max:255']],
                    'description' => ['label' => 'Descricao', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:255']],
                    'type' => ['label' => 'Tipo', 'type' => 'select', 'rules' => ['required', Rule::in(['fixed', 'percentage'])], 'options' => ['fixed' => 'Valor fixo', 'percentage' => 'Percentual']],
                    'value' => ['label' => 'Valor', 'type' => 'money', 'rules' => ['required', 'numeric', 'min:0']],
                    'minimum_order_value' => ['label' => 'Pedido minimo', 'type' => 'money', 'rules' => ['nullable', 'numeric', 'min:0']],
                    'usage_limit' => ['label' => 'Limite de uso', 'type' => 'number', 'rules' => ['nullable', 'integer', 'min:0']],
                    'used_count' => ['label' => 'Usos', 'type' => 'number', 'rules' => ['nullable', 'integer', 'min:0']],
                    'starts_at' => ['label' => 'Inicio', 'type' => 'datetime-local', 'rules' => ['nullable', 'date']],
                    'expires_at' => ['label' => 'Expira em', 'type' => 'datetime-local', 'rules' => ['nullable', 'date']],
                    'is_active' => ['label' => 'Ativo', 'type' => 'checkbox', 'rules' => ['nullable', 'boolean']],
                ],
                'order' => ['created_at', 'desc'],
            ],
        ];

        abort_unless(isset($modules[$module]), 404);

        return $modules[$module];
    }

    private function namedModule(string $title, string $singular, string $model, array $features = []): array
    {
        $fields = [
            'name' => ['label' => 'Nome', 'type' => 'text', 'rules' => ['required', 'string', 'max:255']],
            'slug' => ['label' => 'Slug', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:255']],
        ];

        if ($features['category_id'] ?? false) {
            $fields = ['category_id' => ['label' => 'Categoria', 'type' => 'relation', 'model' => Category::class, 'rules' => ['nullable', 'integer', 'exists:categories,id']]] + $fields;
        }

        if ($features['description'] ?? false) {
            $fields['description'] = ['label' => 'Descricao', 'type' => 'textarea', 'rules' => ['nullable', 'string']];
        }

        if ($features['image_url'] ?? false) {
            $fields['image_url'] = ['label' => 'URL da imagem', 'type' => 'url', 'rules' => ['nullable', 'string']];
        }

        if (($features['sort_order'] ?? true) !== false) {
            $fields['sort_order'] = ['label' => 'Ordem', 'type' => 'number', 'rules' => ['nullable', 'integer', 'min:0']];
        }

        $fields['is_active'] = ['label' => 'Ativo', 'type' => 'checkbox', 'rules' => ['nullable', 'boolean']];

        $columns = ['name' => 'Nome'];

        if ($features['category_id'] ?? false) {
            $columns['category.name'] = 'Categoria';
        }

        if (($features['sort_order'] ?? true) !== false) {
            $columns['sort_order'] = 'Ordem';
        }

        $columns['is_active'] = 'Ativo';

        return [
            'title' => $title,
            'singular' => $singular,
            'model' => $model,
            'search' => ['name', 'slug', 'description'],
            'with' => ($features['category_id'] ?? false) ? ['category'] : [],
            'columns' => $columns,
            'fields' => $fields,
            'order' => (($features['sort_order'] ?? true) !== false) ? ['sort_order', 'asc'] : ['name', 'asc'],
        ];
    }

    private function productModule(string $title, array $fixed = []): array
    {
        return [
            'title' => $title,
            'singular' => 'Produto',
            'model' => Product::class,
            'search' => ['name', 'slug', 'sku', 'description'],
            'with' => ['category', 'subCategory', 'sizeCategory'],
            'fixed' => $fixed,
            'columns' => ['name' => 'Nome', 'sku' => 'Codigo', 'category.name' => 'Categoria', 'price' => 'Preco', 'is_featured' => 'Vitrine', 'is_best_seller' => 'Mais vendido', 'is_unavailable' => 'Indisponivel'],
            'fields' => [
                'category_id' => ['label' => 'Categoria', 'type' => 'relation', 'model' => Category::class, 'rules' => ['nullable', 'integer', 'exists:categories,id']],
                'sub_category_id' => ['label' => 'Sub-categoria', 'type' => 'relation', 'model' => SubCategory::class, 'rules' => ['nullable', 'integer', 'exists:sub_categories,id']],
                'size_category_id' => ['label' => 'Tipo de tamanho', 'type' => 'relation', 'model' => SizeCategory::class, 'rules' => ['nullable', 'integer', 'exists:size_categories,id']],
                'sku' => ['label' => 'Codigo', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:255']],
                'name' => ['label' => 'Nome', 'type' => 'text', 'rules' => ['required', 'string', 'max:255']],
                'slug' => ['label' => 'Slug', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:255']],
                'description' => ['label' => 'Descricao', 'type' => 'textarea', 'rules' => ['nullable', 'string']],
                'price' => ['label' => 'Preco', 'type' => 'money', 'rules' => ['nullable', 'numeric', 'min:0']],
                'promotional_price' => ['label' => 'Preco promocional', 'type' => 'money', 'rules' => ['nullable', 'numeric', 'min:0']],
                'stock' => ['label' => 'Estoque', 'type' => 'number', 'rules' => ['nullable', 'integer', 'min:0']],
                'image_url' => ['label' => 'URL da imagem', 'type' => 'url', 'rules' => ['nullable', 'string']],
                'has_variation' => ['label' => 'Possui variacao', 'type' => 'checkbox', 'rules' => ['nullable', 'boolean']],
                'is_featured' => ['label' => 'Produto da vitrine', 'type' => 'checkbox', 'rules' => ['nullable', 'boolean']],
                'is_best_seller' => ['label' => 'Mais vendido', 'type' => 'checkbox', 'rules' => ['nullable', 'boolean']],
                'is_unavailable' => ['label' => 'Indisponivel', 'type' => 'checkbox', 'rules' => ['nullable', 'boolean']],
            ],
            'order' => ['name', 'asc'],
        ];
    }

    private function variationModule(string $title, array $requiredRelations = []): array
    {
        return [
            'title' => $title,
            'singular' => 'Variacao',
            'model' => SizeFragrancePrice::class,
            'search' => ['glide_id'],
            'with' => ['product', 'size', 'fragranceType', 'colorType'],
            'required_relations' => $requiredRelations,
            'columns' => ['product.name' => 'Produto', 'size.name' => 'Tamanho', 'fragranceType.name' => 'Fragrancia', 'colorType.name' => 'Cor', 'price' => 'Preco', 'stock' => 'Estoque', 'is_active' => 'Ativo'],
            'fields' => [
                'product_id' => ['label' => 'Produto', 'type' => 'relation', 'model' => Product::class, 'rules' => ['nullable', 'integer', 'exists:products,id']],
                'size_id' => ['label' => 'Tamanho', 'type' => 'relation', 'model' => Size::class, 'rules' => ['nullable', 'integer', 'exists:sizes,id']],
                'fragrance_type_id' => ['label' => 'Fragrancia', 'type' => 'relation', 'model' => FragranceType::class, 'rules' => ['nullable', 'integer', 'exists:fragrance_types,id']],
                'color_type_id' => ['label' => 'Cor', 'type' => 'relation', 'model' => ColorType::class, 'rules' => ['nullable', 'integer', 'exists:color_types,id']],
                'price' => ['label' => 'Preco', 'type' => 'money', 'rules' => ['nullable', 'numeric', 'min:0']],
                'promotional_price' => ['label' => 'Preco promocional', 'type' => 'money', 'rules' => ['nullable', 'numeric', 'min:0']],
                'stock' => ['label' => 'Estoque', 'type' => 'number', 'rules' => ['nullable', 'integer', 'min:0']],
                'is_active' => ['label' => 'Ativo', 'type' => 'checkbox', 'rules' => ['nullable', 'boolean']],
            ],
            'order' => ['updated_at', 'desc'],
        ];
    }

    private function baseQuery(string $module, array $config): Builder
    {
        $query = $config['model']::query();

        if ($with = ($config['with'] ?? [])) {
            $query->with($with);
        }

        foreach (($config['fixed'] ?? []) as $column => $value) {
            $query->where($column, $value);
        }

        foreach (($config['required_relations'] ?? []) as $column) {
            $query->whereNotNull($column);
        }

        if ($module === 'most-accessed-products') {
            $query->orderByDesc('is_featured')->orderByDesc('is_best_seller');
        }

        [$column, $direction] = $config['order'] ?? ['id', 'desc'];

        return $query->orderBy($column, $direction)->orderBy('id');
    }

    private function validatedData(Request $request, array $config, ?Model $item = null): array
    {
        $rules = [];

        foreach ($config['fields'] as $name => $field) {
            $rules[$name] = $field['rules'];
        }

        if (isset($rules['slug'])) {
            $rules['slug'][] = Rule::unique($item?->getTable() ?? (new $config['model']())->getTable(), 'slug')->ignore($item);
        }

        if (isset($rules['code'])) {
            $rules['code'][] = Rule::unique('coupons', 'code')->ignore($item);
        }

        $data = $request->validate($rules);

        foreach ($config['fields'] as $name => $field) {
            if (($field['type'] ?? null) === 'checkbox') {
                $data[$name] = $request->boolean($name);
            }
        }

        if (array_key_exists('slug', $config['fields']) && blank($data['slug'] ?? null)) {
            $data['slug'] = $this->uniqueSlug($config['model'], $data['name'] ?? $data['code'] ?? Str::random(8), $item);
        }

        if (array_key_exists('code', $data)) {
            $data['code'] = Str::upper($data['code']);
        }

        foreach (($config['fixed'] ?? []) as $column => $value) {
            $data[$column] = $value;
        }

        return Arr::only($data, array_merge(array_keys($config['fields']), array_keys($config['fixed'] ?? [])));
    }

    private function options(array $config): array
    {
        $options = [];

        foreach ($config['fields'] as $name => $field) {
            if (($field['type'] ?? null) === 'relation') {
                $options[$name] = $field['model']::query()->orderBy('name')->pluck('name', 'id');
            }
        }

        return $options;
    }

    private function isAdminLogged(Request $request): bool
    {
        $adminId = $request->session()->get('launch_admin_id');

        return $adminId && LaunchAdminAccount::query()
            ->where('is_active', true)
            ->whereKey($adminId)
            ->exists();
    }

    private function uniqueSlug(string $model, string $value, ?Model $item = null): string
    {
        $base = Str::slug($value) ?: Str::random(12);
        $slug = $base;
        $counter = 2;

        while ($model::query()->where('slug', $slug)->when($item, fn (Builder $query) => $query->where($item->getKeyName(), '!=', $item->getKey()))->exists()) {
            $slug = "{$base}-{$counter}";
            $counter++;
        }

        return $slug;
    }
}
