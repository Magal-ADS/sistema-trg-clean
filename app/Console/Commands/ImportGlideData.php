<?php

namespace App\Console\Commands;

use App\Models\Banner;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\ColorType;
use App\Models\Coupon;
use App\Models\FragranceType;
use App\Models\HomeSetting;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\SellerCity;
use App\Models\Service;
use App\Models\Size;
use App\Models\SizeCategory;
use App\Models\SizeFragrancePrice;
use App\Models\SubCategory;
use App\Models\TodoItem;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use SplFileObject;

class ImportGlideData extends Command
{
    protected $signature = 'app:import-glide-csv {--path=glide_backups : Pasta dentro de storage/app}';

    protected $description = 'Importa CSVs exportados do Glide para as tabelas Laravel.';

    public function handle(): int
    {
        $basePath = storage_path('app/'.$this->option('path'));

        if (! is_dir($basePath)) {
            mkdir($basePath, 0755, true);
            $this->warn("Pasta criada: {$basePath}");
            $this->warn('Copie os CSVs do Glide para essa pasta e rode o comando novamente.');

            return self::SUCCESS;
        }

        DB::transaction(function () use ($basePath): void {
            $this->importNamed($basePath, 'Categorias.csv', fn (array $row) => $this->upsertNamed(Category::class, $row));
            $this->importNamed($basePath, 'Sub-Categorias.csv', fn (array $row) => $this->upsertSubCategory($row));
            $this->importNamed($basePath, 'Categoria de Tamanho.csv', fn (array $row) => $this->upsertNamed(SizeCategory::class, $row));
            $this->importNamed($basePath, 'Tamanhos.csv', fn (array $row) => $this->upsertSize($row));
            $this->importNamed($basePath, 'Tipos de Cores.csv', fn (array $row) => $this->upsertNamed(ColorType::class, $row));
            $this->importNamed($basePath, 'Tipos de Fragrâncias.csv', fn (array $row) => $this->upsertNamed(FragranceType::class, $row));
            $this->importNamed($basePath, 'Lista de Cupom.csv', fn (array $row) => $this->upsertCoupon($row));
            $this->importNamed($basePath, 'Cupom de desconto.csv', fn (array $row) => $this->upsertCoupon($row));
            $this->importNamed($basePath, 'Banners .csv', fn (array $row) => $this->upsertBanner($row));
            $this->importNamed($basePath, 'Home (1).csv', fn (array $row) => $this->upsertHomeSetting($row));
            $this->importNamed($basePath, 'Users (1).csv', fn (array $row) => $this->upsertUser($row));
            $this->importNamed($basePath, 'Produtos.csv', fn (array $row) => $this->upsertProduct($row));
            $this->importNamed($basePath, 'Valores Tamanhos e fragrancias.csv', fn (array $row) => $this->upsertVariationPrice($row));
            $this->importNamed($basePath, 'Serviços.csv', fn (array $row) => $this->upsertService($row));
            $this->importNamed($basePath, 'Vendedoras e cidades.csv', fn (array $row) => $this->upsertSellerCity($row));
            $this->importNamed($basePath, 'To do list.csv', fn (array $row) => $this->upsertTodo($row));
            $this->importNamed($basePath, 'Pedidos confirmados.csv', fn (array $row) => $this->upsertOrder($row));
            $this->importNamed($basePath, 'Produtos no carrinho.csv', fn (array $row) => $this->upsertCartOrOrderItem($row));
        });

        $this->info('Importacao concluida.');

        return self::SUCCESS;
    }

    private function importNamed(string $basePath, string $fileName, callable $callback): void
    {
        $path = $basePath.DIRECTORY_SEPARATOR.$fileName;

        if (! file_exists($path)) {
            $this->warn("Arquivo nao encontrado: {$fileName}");

            return;
        }

        $count = 0;

        foreach ($this->rows($path) as $row) {
            $callback($row);
            $count++;
        }

        $this->line("{$fileName}: {$count} linhas processadas");
    }

    private function upsertNamed(string $modelClass, array $row): void
    {
        $glideId = $this->first($row, ['id', 'row id', 'categoria id', 'subcategoria id', 'categoria de tamanho id', 'tamanho id', 'cor id', 'fragrancia id']);
        $name = $this->first($row, ['nome', 'name', 'categoria', 'categorias nome', 'sub categoria', 'tamanho', 'cor', 'fragrancia']);

        if (! $name && ! $glideId) {
            return;
        }

        $attributes = $glideId ? ['glide_id' => $glideId] : ['slug' => $this->uniqueSlug($modelClass, $name)];

        $modelClass::updateOrCreate($attributes, [
            'name' => $name ?: $glideId,
            'slug' => $this->uniqueSlug($modelClass, $name ?: $glideId, $glideId),
            'description' => $this->first($row, ['descricao', 'description']),
            'image_url' => $this->first($row, ['imagem', 'image', 'foto']),
            'is_active' => ! $this->boolean($this->first($row, ['indisponivel', 'inativo'])),
            'metadata' => $row,
        ]);
    }

    private function upsertSubCategory(array $row): void
    {
        $category = $this->findByGlide(Category::class, $this->first($row, ['categoria', 'categoria id']));
        $name = $this->first($row, ['nome', 'sub categoria', 'subcategorias nome', 'sub-categorias nome']);

        if (! $name) {
            $this->upsertNamed(SubCategory::class, $row);

            return;
        }

        SubCategory::updateOrCreate(
            ['glide_id' => $this->first($row, ['id', 'subcategoria id', 'sub-categoria id']) ?: $this->stableKey($name)],
            [
                'category_id' => $category?->id,
                'name' => $name,
                'slug' => $this->uniqueSlug(SubCategory::class, $name, $this->first($row, ['id', 'subcategoria id'])),
                'description' => $this->first($row, ['descricao']),
                'image_url' => $this->first($row, ['imagem']),
                'metadata' => $row,
            ]
        );
    }

    private function upsertSize(array $row): void
    {
        $name = $this->first($row, ['nome', 'tamanho', 'tamanhos nome']);

        if (! $name) {
            return;
        }

        Size::updateOrCreate(
            ['glide_id' => $this->first($row, ['id', 'tamanho id']) ?: $this->stableKey($name)],
            [
                'size_category_id' => $this->findByGlide(SizeCategory::class, $this->first($row, ['categoria de tamanho id']))?->id,
                'name' => $name,
                'slug' => $this->uniqueSlug(Size::class, $name, $this->first($row, ['id', 'tamanho id'])),
                'metadata' => $row,
            ]
        );
    }

    private function upsertCoupon(array $row): void
    {
        $code = $this->first($row, ['codigo', 'cupom', 'cupom de desconto', 'lista de cupom']);

        if (! $code) {
            return;
        }

        Coupon::updateOrCreate(['code' => Str::upper($code)], [
            'glide_id' => $this->first($row, ['id']),
            'description' => $this->first($row, ['descricao']),
            'type' => Str::contains($this->first($row, ['tipo']) ?? '', '%') ? 'percentage' : 'fixed',
            'value' => $this->money($this->first($row, ['valor', 'desconto'])),
            'expires_at' => $this->date($this->first($row, ['validade', 'expira em'])),
            'metadata' => $row,
        ]);
    }

    private function upsertBanner(array $row): void
    {
        $title = $this->first($row, ['titulo', 'title', 'nome']) ?: 'Banner';

        Banner::updateOrCreate(
            ['glide_id' => $this->first($row, ['id']) ?: $this->stableKey($title)],
            [
                'title' => $title,
                'subtitle' => $this->first($row, ['subtitulo', 'subtitle']),
                'image_url' => $this->first($row, ['imagem', 'image']),
                'link_url' => $this->first($row, ['link', 'url']),
                'metadata' => $row,
            ]
        );
    }

    private function upsertHomeSetting(array $row): void
    {
        $key = $this->first($row, ['key', 'chave', 'nome', 'titulo']) ?: $this->stableKey(json_encode($row));

        HomeSetting::updateOrCreate(['key' => Str::slug($key)], [
            'label' => $key,
            'value' => $row,
            'is_active' => true,
        ]);
    }

    private function upsertUser(array $row): void
    {
        $email = $this->first($row, ['email']);

        if (! $email) {
            return;
        }

        User::updateOrCreate(['email' => Str::lower($email)], [
            'name' => $this->first($row, ['name', 'nome']) ?: $email,
            'password' => bcrypt(Str::random(32)),
            'cpf' => $this->first($row, ['cpf']),
            'phone' => $this->first($row, ['telefone', 'whatsapp']),
            'address' => $this->first($row, ['endereco']),
            'reference' => $this->first($row, ['referencia']),
            'city' => $this->first($row, ['cidade da vendedora', 'cidade nova']),
            'seller_name' => $this->first($row, ['nome da vendedora']),
            'seller_email' => $this->first($row, ['email da vendedora']),
            'seller_phone' => $this->first($row, ['celular da vendedora']),
            'is_admin' => $this->boolean($this->first($row, ['administrador'])),
            'is_developer' => $this->boolean($this->first($row, ['desenvolvedor'])),
            'metadata' => $row,
        ]);
    }

    private function upsertProduct(array $row): void
    {
        $glideId = $this->first($row, ['produto id']);
        $name = $this->first($row, ['produto', 'nome']);

        if (! $glideId && ! $name) {
            return;
        }

        Product::updateOrCreate(
            ['glide_id' => $glideId ?: $this->stableKey($name)],
            [
                'category_id' => $this->findByGlide(Category::class, $this->first($row, ['categoria']))?->id,
                'sub_category_id' => $this->findByGlide(SubCategory::class, $this->first($row, ['subcategoria id']))?->id,
                'size_category_id' => $this->findByGlide(SizeCategory::class, $this->first($row, ['categoria de tamanho id']))?->id,
                'sku' => $this->first($row, ['codigo ref']),
                'name' => $name ?: 'Produto sem nome',
                'slug' => $this->uniqueSlug(Product::class, $name ?: $glideId, $glideId),
                'description' => $this->first($row, ['descricao']),
                'price' => $this->money($this->first($row, ['valor original', 'valor correto original', 'tmp valor vitrine'])),
                'promotional_price' => $this->nullableMoney($this->first($row, ['promocao original', 'tmp promocao variacao'])),
                'has_variation' => Str::contains($this->first($row, ['possui variacao']) ?? '', 'vari'),
                'is_featured' => $this->boolean($this->first($row, ['novidades'])),
                'is_best_seller' => $this->boolean($this->first($row, ['mais vendidos'])),
                'is_unavailable' => $this->boolean($this->first($row, ['indisponivel'])),
                'image_url' => $this->first($row, ['primeira imagem', 'imagem']),
                'variations' => [
                    'sizes' => $this->first($row, ['entry tamanhos']),
                    'colors' => $this->first($row, ['entry cor', 'cores dos produtos']),
                    'fragrances' => $this->first($row, ['entry fragrancia']),
                ],
                'metadata' => $row,
            ]
        );
    }

    private function upsertVariationPrice(array $row): void
    {
        $product = $this->findByGlide(Product::class, $this->first($row, ['produto id', 'produto']));
        $key = $this->first($row, ['id', 'tmp chave tripla']) ?: $this->stableKey(json_encode($row));

        SizeFragrancePrice::updateOrCreate(['glide_id' => $key], [
            'product_id' => $product?->id,
            'price' => $this->money($this->first($row, ['valor', 'valor original', 'preco'])),
            'promotional_price' => $this->nullableMoney($this->first($row, ['promocao', 'valor promocional'])),
            'metadata' => $row,
        ]);
    }

    private function upsertService(array $row): void
    {
        $name = $this->first($row, ['servico', 'servicos', 'nome', 'produto']);

        if (! $name) {
            return;
        }

        Service::updateOrCreate(
            ['glide_id' => $this->first($row, ['id', 'servico id']) ?: $this->stableKey($name)],
            [
                'name' => $name,
                'slug' => $this->uniqueSlug(Service::class, $name, $this->first($row, ['id', 'servico id'])),
                'description' => $this->first($row, ['descricao']),
                'price' => $this->nullableMoney($this->first($row, ['valor', 'preco'])),
                'image_url' => $this->first($row, ['imagem']),
                'metadata' => $row,
            ]
        );
    }

    private function upsertSellerCity(array $row): void
    {
        $seller = $this->first($row, ['nome da vendedora', 'vendedora', 'nome']) ?: 'Vendedora';
        $city = $this->first($row, ['cidade', 'cidade da vendedora']) ?: 'Cidade nao informada';

        SellerCity::updateOrCreate(
            ['glide_id' => $this->first($row, ['id']) ?: $this->stableKey($seller.$city)],
            [
                'seller_name' => $seller,
                'seller_email' => $this->first($row, ['email', 'email da vendedora']),
                'seller_phone' => $this->first($row, ['telefone', 'celular da vendedora']),
                'city' => $city,
                'state' => $this->first($row, ['estado', 'uf']),
                'metadata' => $row,
            ]
        );
    }

    private function upsertTodo(array $row): void
    {
        $title = $this->first($row, ['titulo', 'title', 'tarefa', 'todo']) ?: 'Tarefa';

        TodoItem::updateOrCreate(
            ['glide_id' => $this->first($row, ['id']) ?: $this->stableKey($title)],
            [
                'title' => $title,
                'description' => $this->first($row, ['descricao', 'description']),
                'status' => $this->first($row, ['status']) ?: 'pending',
                'priority' => $this->first($row, ['prioridade']) ?: 'normal',
                'metadata' => $row,
            ]
        );
    }

    private function upsertOrder(array $row): void
    {
        $code = $this->first($row, ['codigo do pedido']);

        if (! $code) {
            return;
        }

        $email = $this->first($row, ['email']);

        Order::updateOrCreate(['code' => $code], [
            'user_id' => User::where('email', Str::lower((string) $email))->value('id'),
            'customer_name' => $this->first($row, ['nome']) ?: 'Cliente',
            'customer_email' => $email ?: 'sem-email-'.$code.'@example.test',
            'customer_phone' => $this->first($row, ['telefone']),
            'delivery_type' => $this->first($row, ['entrega ou retirada']),
            'payment_method' => $this->first($row, ['forma de pagamento']),
            'status' => 'confirmed',
            'subtotal' => $this->money($this->first($row, ['valor do pedido'])),
            'total' => $this->money($this->first($row, ['valor do pedido'])),
            'address' => $this->first($row, ['endereco']),
            'complement' => $this->first($row, ['complemento']),
            'confirmed_at' => $this->date($this->first($row, ['data'])),
            'metadata' => $row,
        ]);
    }

    private function upsertCartOrOrderItem(array $row): void
    {
        $order = Order::where('code', $this->first($row, ['codigo do pedido']))->first();
        $product = Product::where('glide_id', $this->first($row, ['produto id']))->first();
        $productName = $this->first($row, ['nome do produto', 'tmp nome do produto']) ?: 'Produto';

        if ($order) {
            OrderItem::updateOrCreate(
                ['source_hash' => $this->stableKey(json_encode($row))],
                [
                    'order_id' => $order->id,
                    'product_id' => $product?->id,
                    'product_name' => $productName,
                    'quantity' => (int) ($this->first($row, ['quantidade']) ?: 1),
                    'unit_price' => $this->money($this->first($row, ['valor do produto'])),
                    'total' => $this->money($this->first($row, ['total'])),
                    'size' => $this->first($row, ['tamanho']),
                    'color' => $this->first($row, ['cor']),
                    'fragrance' => $this->first($row, ['fragrancia']),
                    'metadata' => $row,
                ]
            );

            return;
        }

        CartItem::updateOrCreate(
            ['glide_id' => $this->stableKey(json_encode($row))],
            [
                'user_id' => User::where('email', Str::lower((string) $this->first($row, ['email'])))->value('id'),
                'product_id' => $product?->id,
                'product_name' => $productName,
                'quantity' => (int) ($this->first($row, ['quantidade']) ?: 1),
                'unit_price' => $this->money($this->first($row, ['valor do produto'])),
                'total' => $this->money($this->first($row, ['total'])),
                'size' => $this->first($row, ['tamanho']),
                'color' => $this->first($row, ['cor']),
                'fragrance' => $this->first($row, ['fragrancia']),
                'metadata' => $row,
            ]
        );
    }

    private function rows(string $path): iterable
    {
        $file = new SplFileObject($path);
        $file->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY | SplFileObject::DROP_NEW_LINE);
        $file->setCsvControl(',');
        $headers = [];

        foreach ($file as $index => $data) {
            if (! is_array($data) || $data === [null]) {
                continue;
            }

            if ($index === 0) {
                $headers = array_map(fn (?string $header): string => $this->normalize((string) $header), $data);

                continue;
            }

            if (count($headers) === 0) {
                continue;
            }

            yield array_combine($headers, array_pad($data, count($headers), null));
        }
    }

    private function first(array $row, array $keys): ?string
    {
        foreach ($keys as $key) {
            $value = $row[$this->normalize($key)] ?? null;

            if (! blank($value)) {
                return trim((string) $value);
            }
        }

        return null;
    }

    private function normalize(string $value): string
    {
        return Str::of($value)
            ->replace(['Ã³', 'Ã§', 'Ã£', 'Ãª', 'Ã¢', 'Ã¡', 'Ã©', 'Ã­', 'Ãº', 'Ãµ'], ['o', 'c', 'a', 'e', 'a', 'a', 'e', 'i', 'u', 'o'])
            ->ascii()
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', ' ')
            ->trim()
            ->toString();
    }

    private function money(?string $value): float
    {
        $normalized = str_replace(',', '.', preg_replace('/[^\d,.-]/', '', $value ?: '0'));

        return (float) $normalized;
    }

    private function nullableMoney(?string $value): ?float
    {
        return blank($value) ? null : $this->money($value);
    }

    private function boolean(?string $value): bool
    {
        return in_array(Str::lower(trim((string) $value)), ['1', 'sim', 'true', 'yes', 'x', 'ativo'], true);
    }

    private function date(?string $value): ?Carbon
    {
        if (! $value) {
            return null;
        }

        foreach (['d/m/Y, H:i:s', 'd/m/Y H:i:s', 'd/m/Y'] as $format) {
            try {
                return Carbon::createFromFormat($format, $value);
            } catch (\Throwable) {
            }
        }

        return null;
    }

    private function findByGlide(string $modelClass, ?string $glideId): ?Model
    {
        return $glideId ? $modelClass::where('glide_id', $glideId)->first() : null;
    }

    private function stableKey(?string $value): string
    {
        return sha1((string) $value);
    }

    private function uniqueSlug(string $modelClass, string $value, ?string $glideId = null): string
    {
        $base = Str::slug($value) ?: $this->stableKey($value);
        $slug = $base;
        $counter = 2;

        while ($modelClass::where('slug', $slug)->when($glideId, fn ($query) => $query->where('glide_id', '!=', $glideId))->exists()) {
            $slug = "{$base}-{$counter}";
            $counter++;
        }

        return $slug;
    }
}
