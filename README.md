# Sisfarma (Santa Saúde)

Sistema interno para gestão de farmácias com foco em **velocidade no balcão**, consistência de estoque e rastreabilidade.

## O que tem hoje
- Produtos (busca por nome/EAN/SKU + autocomplete no header)
- Estoque por loja + histórico de movimentações
- Compras, Vendas e Transferências entre filiais (com impacto real no estoque)
- Categorias e Ofertas
- Auditoria (base para rastreabilidade de ações)
- Scanner (EAN) dentro do menu do usuário
- Landing page (`/`) para apresentação do sistema
- Assistente IA (opcional) via Ollama (local)

## Stack
- Laravel 12 (PHP 8.2+)
- PostgreSQL
- Tailwind CSS v4 + Vite

## Rodando localmente
Pré-requisitos: PHP, Composer, Node.js, PostgreSQL.

```bash
composer install
copy .env.example .env
php artisan key:generate
php artisan migrate
npm install
npm run dev
php artisan serve
```

Alternativa (modo “tudo junto”):
```bash
composer run dev
```

Rotas úteis:
- Landing: `/`
- Admin: `/admin/login`
- Registro (primeiro acesso): `/admin/registro`

## Banco de dados
Configure no `.env`:
- `DB_CONNECTION=pgsql`
- `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`

## Popular o banco (dados de teste)
Seeder básico (dados mínimos):
```bash
php artisan db:seed
```

Seeder de massa (milhares de registros, útil para testar performance e telas):
```powershell
php artisan db:seed --class="Database\Seeders\MassaDeDadosSeeder"
```

Você pode controlar os volumes via env vars (exemplo):
```powershell
$env:MASSA_PRODUCTS=10000
$env:MASSA_SALES=3000
$env:MASSA_PURCHASES=600
$env:MASSA_TRANSFERS=300
php artisan db:seed --class="Database\Seeders\MassaDeDadosSeeder"
```

Variáveis suportadas: `MASSA_DAYS_BACK`, `MASSA_STORES`, `MASSA_SUPPLIERS`, `MASSA_CATEGORIES`, `MASSA_PRODUCTS`, `MASSA_OFFERS`, `MASSA_PURCHASES`, `MASSA_SALES`, `MASSA_TRANSFERS`, `MASSA_INVENTORY_ALL`.

### Acessos de teste (DadosDeTesteSeeder)
- Admin: `test@example.com` / `password`
- Gerente: `gerente@example.com` / `password`
- Atendente: `atendente@example.com` / `password`
- Caixa: `caixa@example.com` / `password`

## IA (opcional)
Por padrão o projeto suporta Ollama local (gratuito):
- Instale: https://ollama.com
- Baixe um modelo (ex.: `ollama pull llama3.1:8b`)
- Ajuste no `.env`:
  - `AI_PROVIDER=ollama`
  - `AI_OLLAMA_URL=http://127.0.0.1:11434`
  - `AI_OLLAMA_MODEL=llama3.1:8b`

## Observações
Este projeto é um sistema interno (admin). Antes de publicar em produção, revise configurações de segurança, logs, rate limits e backups.
