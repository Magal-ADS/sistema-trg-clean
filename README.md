# TRG Clean

Sistema web da TRG Clean desenvolvido em Laravel para organizar e exibir catalogo de produtos, categorias, subcategorias, banners, cupons, pedidos, carrinho, servicos, tamanhos, fragrancias, cores e dados de vendedores/cidades.

O projeto usa Laravel com PostgreSQL e possui importador de dados exportados do Glide em CSV, permitindo migrar informacoes de produtos, usuarios, pedidos e configuracoes para as tabelas da aplicacao.

## Tecnologias

- PHP 8.4 com Laravel
- PostgreSQL 16
- Nginx
- Vite
- Tailwind CSS
- Docker e Docker Compose
a
## Funcionalidades

- Home com banners, categorias e produtos em destaque.
- Listagem e visualizacao de produtos.
- Paginas de carrinho e pedidos.
- Estrutura de dados para catalogo, variacoes, cupons, servicos e pedidos.
- Comando Artisan para importar CSVs exportados do Glide.
- App de lancamentos para vendedores com area administrativa.

## Lancamentos de Vendedores

Atalhos de acesso:

- Vendedor: `/app-lancamentos`
- Admin: `/admin-lancamentos`

Rotas finais:

- Vendedor: `/lancamentos`
- Admin: `/admin/lancamentos/login`

Admin inicial criado pela migration:

- E-mail: `admin@weagles.com`
- Senha: `123`

Para criar as tabelas do modulo:

```bash
php artisan migrate
```

Para criar os acessos iniciais:

```bash
php artisan db:seed
```

Se estiver usando Docker, rode o comando dentro do container da aplicacao:

```bash
docker compose exec app php artisan migrate
docker compose exec app php artisan db:seed
```

Credenciais criadas pelo seed:

- Admin: `admin@weagles.com` / `123`
- Vendedor teste: `vendedor@weagles.com` / `1234`

Tabelas criadas pelas migrations:

- `launch_admin_accounts`
- `seller_accounts`
- `seller_daily_entries`

Fluxo basico de teste:

1. Acesse `/admin-lancamentos`.
2. Entre com `admin@weagles.com` e senha `123`.
3. Cadastre um vendedor em `Vendedores`.
4. Acesse `/app-lancamentos`.
5. Entre como o vendedor cadastrado, ou use `Vendedor Teste` com senha `1234`, e salve um lancamento.
6. Volte ao admin e confira o lancamento no relatorio.

## Docker

Os comandos para subir, instalar dependencias, migrar banco, importar dados e executar tarefas dentro dos containers estao em [docker/README.md](docker/README.md).
