# Vendi

Sistema de gestión / punto de venta (POS) para comercios de **Argentina** — POS,
stock, clientes, cuenta corriente, reportes. **Multi-tenant** con customización por
cliente (base sólida + config/plugins, no forks). Objetivo: primer POS "de
categoría" (nivel pulpos.com) para el mercado argentino. Rubro demo: indumentaria
(**Bondi Indumentaria**). Producto pensado para venderse a comerciantes; cada
comercio es un tenant, con su branding (white-label) y módulos.

## Stack
Laravel 12 (PHP 8.3) · Inertia 2 · React 19 + TypeScript · Tailwind v4 + shadcn/ui ·
PostgreSQL · Fortify (auth) · spatie/laravel-permission (roles) · Pest (tests).
- **Gestor JS: pnpm** — nunca npm (hay `packageManager` fijado).
- **Fuentes: Clash Display (display/números) + General Sans (body)** — nunca Inter/Arial.
- **Fondo crema `#F8F3EC`, nunca blanco puro `#FFFFFF`.**

## Commands
```bash
composer dev              # PHP server + queue + Vite (usa pnpm auto)
php artisan test          # Pest (incl. tests de aislamiento multi-tenant)
pnpm dev | pnpm build | pnpm types:check
php artisan migrate --seed   # schema + tenant demo
```
Login demo: `camila@bondi.test` / `password`.

## Architecture
- **Multi-tenant row-level.** Cada tabla de dominio lleva `tenant_id`; los modelos
  usan `App\Models\Concerns\BelongsToTenant` (aplica `TenantScope` + autocompleta
  `tenant_id`). Tenant actual = usuario logueado (`SetCurrentTenant` middleware →
  singleton `App\Tenancy\CurrentTenant`).
- **White-label.** `tenants.settings.accent_color` se comparte por Inertia
  (`HandleInertiaRequests`) y se aplica en vivo (`resources/js/components/apply-tenant-accent.tsx`).
- **Puntos de extensión** (dejar listos, no romper el core): facturación tras la
  interface `FiscalDocumentIssuer` (`NullIssuer` por ahora, AFIP post-MVP); eventos
  de dominio (`SaleCompleted`, `StockLow`) para plugins/insights/WhatsApp.
- **Dónde está cada cosa:** tokens del design → `resources/css/app.css`; front →
  `resources/js/{pages,components,layouts}`; tenancy → `app/Tenancy`, `app/Models/{Concerns,Scopes}`.

## Conventions
- WHEN creás un modelo de dominio → `use BelongsToTenant` + columna `tenant_id`.
  **Nunca** confiar en el front para el scoping por tenant.
- WHEN mostrás plata → cálculo **server-side**; formato `es-AR` (`$ 38.900`) solo en
  display. Copy en **español rioplatense** ("Cargá", "Vendé").
- WHEN tocás color/estilo → usá los tokens de `app.css` (variables CSS de shadcn +
  extras `amber`/`whatsapp`). No hardcodear hex en componentes.
- WHEN arrancás un módulo nuevo → escribí/iterá su spec en `specs/modules/<x>.md`
  **antes** de codear. Ver `specs/README.md` (workflow) y `specs/_TEMPLATE.md`.
- WHEN implementás → **TDD con Pest** (test → código → validar criterios de aceptación).
- WHEN corrés algo de JS → **pnpm**, no npm.

## Testing
Pest, `php artisan test` (sqlite `:memory:`). Para todo modelo tenant-scoped:
**test de aislamiento obligatorio** (un tenant no ve datos de otro) — ver
`tests/Feature/TenantScopeTest.php` como patrón.

## Gotchas
- **No** apliques el tenant global scope al modelo `User` — auth resuelve el user
  antes de conocer el tenant.
- El **dark theme está afinado a mano** (no es inversión del claro). El accent del
  tenant no debe pisar el `--primary` del dark cuando es el default (violeta).
- El **sidebar** apunta todo a `dashboard()` como placeholder hasta que cada módulo
  tenga su ruta (Fases 1–6). No asumas que navegan a pantallas propias todavía.
- AFIP está **diferido** (post-MVP), pero su spec y el hueco de datos fiscales existen.

## Documentación y diseño
- **Design de referencia (prototipo hi-fi, fuente de verdad visual):**
  `design_handoff_vendi_pos/README.md` (tokens, 8 pantallas, flujos) + `Vendi v2.dc.html`.
- **Plan técnico + módulos + orden de build:** `docs/superpowers/specs/2026-07-01-plan-tecnico-mvp.md`
- **Diseño/producto (mercado, arquitectura de customización):** `docs/superpowers/specs/2026-06-24-pos-argentina-design.md`
- **Specs funcionales por módulo (spec-driven):** `specs/` (template + ejemplo `modules/productos.md`)
- **Prompts de diseño usados:** `prompts/`

## Estado
**Fase 0 completa:** stack andando, tenancy + roles, shell temado (sidebar/toggle
claro-oscuro), auth. Próximo: **Fase 1 (Productos)**. Ver el plan técnico para el orden.
