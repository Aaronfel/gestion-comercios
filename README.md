# Vendi — POS & gestión para comercios (Argentina)

Sistema de gestión todo-en-uno para comercios argentinos (POS, stock, clientes,
reportes), multi-tenant, con customización por cliente. Rubro demo: indumentaria
(**Bondi Indumentaria**).

## Stack
Laravel 12 · Inertia 2 · React 19 + TypeScript · Tailwind v4 + shadcn/ui ·
PostgreSQL · Fortify (auth) · spatie/laravel-permission (roles) · Pest (tests).

## Requisitos
PHP 8.3 · Composer · Node 20 + pnpm · PostgreSQL.
(pnpm: `corepack enable` o `npm i -g pnpm`)

## Setup

```bash
cp .env.example .env         # ajustá DB_USERNAME/DB_PASSWORD a tu Postgres local
composer install
pnpm install
php artisan key:generate
createdb vendi               # o creá la DB "vendi" como prefieras
php artisan migrate --seed   # crea el schema + tenant demo
```

**Login demo:** `camila@bondi.test` / `password` (rol admin del tenant Bondi).

## Correr en dev

```bash
composer dev     # levanta PHP server + queue + Vite juntos (usa pnpm auto)
# o por separado:
php artisan serve
pnpm dev
```

## Verificación

```bash
php artisan test      # Pest (incl. tests de aislamiento multi-tenant)
pnpm types:check      # TypeScript
pnpm build            # build de producción de Vite
```

## Arquitectura clave

- **Multi-tenant row-level:** cada tabla de dominio lleva `tenant_id`. Los modelos
  usan el trait `App\Models\Concerns\BelongsToTenant`, que aplica el
  `TenantScope` (filtro automático) y autocompleta `tenant_id` al crear. El tenant
  actual se resuelve del usuario logueado (middleware `SetCurrentTenant` →
  singleton `App\Tenancy\CurrentTenant`).
- **White-label:** el accent color del tenant (`tenants.settings.accent_color`) se
  comparte por Inertia y se aplica en vivo (`components/apply-tenant-accent.tsx`).
- **Tokens del design:** en `resources/css/app.css` (paleta cálida violeta+ámbar,
  temas claro/oscuro afinados). Fuentes Clash Display + General Sans (Fontshare).

## Documentación

- **Plan técnico + módulos:** `docs/superpowers/specs/2026-07-01-plan-tecnico-mvp.md`
- **Diseño / producto:** `docs/superpowers/specs/2026-06-24-pos-argentina-design.md`
- **Specs funcionales por módulo:** `specs/` (template + ejemplo Productos)
- **Design de referencia (prototipo):** `design_handoff_vendi_pos/`

## Estado

**Fase 0 completa:** stack andando, tenancy + roles, shell temado (sidebar/header/
toggle claro-oscuro), auth. Próximo: Fase 1 (Productos) — ver el plan técnico.
