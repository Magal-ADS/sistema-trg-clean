# Docker

## Desenvolvimento

Subir os containers:

```bash
docker compose up -d --build
```

Parar os containers:

```bash
docker compose down
```

Parar e apagar o volume do banco:

```bash
docker compose down -v
```

Ver logs de todos os containers:

```bash
docker compose logs -f
```

Ver logs do app:

```bash
docker compose logs -f app
```

Entrar no container PHP:

```bash
docker compose exec app bash
```

Instalar dependencias PHP:

```bash
docker compose exec app composer install
```

Instalar dependencias Node:

```bash
docker compose exec app npm install
```

Gerar a chave da aplicacao:

```bash
docker compose exec app php artisan key:generate
```

Rodar migrations:

```bash
docker compose exec app php artisan migrate
```

Recriar o banco com migrations:

```bash
docker compose exec app php artisan migrate:fresh
```

Build dos assets:

```bash
docker compose exec app npm run build
```

Rodar Vite em modo desenvolvimento:

```bash
docker compose exec app npm run dev
```

Importar CSVs do Glide:

```bash
docker compose exec app php artisan app:import-glide-csv
```

Importar CSVs de outra pasta dentro de `storage/app`:

```bash
docker compose exec app php artisan app:import-glide-csv --path=nome-da-pasta
```

Limpar cache da aplicacao:

```bash
docker compose exec app php artisan optimize:clear
```

Rodar testes:

```bash
docker compose exec app php artisan test
```

Acessar o banco PostgreSQL:

```bash
docker compose exec postgres psql -U trg_clean -d trg_clean
```

Acessar o sistema:

```bash
http://localhost:8004
```

## Produção

Crie o `.env` de produção a partir do exemplo e ajuste pelo menos `APP_URL`, `APP_KEY` e `DB_PASSWORD`:

```bash
cp .env.production.example .env
docker compose -f docker-compose.prod.yml run --rm app php artisan key:generate --show
```

Coloque a chave gerada em `APP_KEY` no `.env`. Depois suba a stack:

```bash
docker compose -f docker-compose.prod.yml up -d --build
```

O compose de produção cria uma imagem fechada com `composer install --no-dev`, `npm run build`, OPcache sem validação de timestamp, Nginx, PHP-FPM e Postgres com healthcheck.

Comandos úteis em produção:

```bash
docker compose -f docker-compose.prod.yml logs -f
docker compose -f docker-compose.prod.yml exec app php artisan optimize:clear
docker compose -f docker-compose.prod.yml exec app php artisan migrate --force
docker compose -f docker-compose.prod.yml down
```
