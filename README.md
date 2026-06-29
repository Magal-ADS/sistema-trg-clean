# TRG Clean

Sistema web da TRG Clean desenvolvido em Laravel para organizar e exibir catalogo de produtos, categorias, subcategorias, banners, cupons, pedidos, carrinho, servicos, tamanhos, fragrancias, cores e dados de vendedoras/cidades.

O projeto usa Laravel com PostgreSQL e possui importador de dados exportados do Glide em CSV, permitindo migrar informacoes de produtos, usuarios, pedidos e configuracoes para as tabelas da aplicacao.

## Tecnologias

- PHP 8.4 com Laravel
- PostgreSQL 16
- Nginx
- Vite
- Tailwind CSS
- Docker e Docker Compose

## Funcionalidades

- Home com banners, categorias e produtos em destaque.
- Listagem e visualizacao de produtos.
- Paginas de carrinho e pedidos.
- Estrutura de dados para catalogo, variacoes, cupons, servicos e pedidos.
- Comando Artisan para importar CSVs exportados do Glide.

## Docker

Os comandos para subir, instalar dependencias, migrar banco, importar dados e executar tarefas dentro dos containers estao em [docker/README.md](docker/README.md).
