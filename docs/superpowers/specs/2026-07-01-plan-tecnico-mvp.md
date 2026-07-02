# Vendi — Plan Técnico (MVP + arquitectura a escala)

**Fecha:** 2026-07-01
**Estado:** Borrador para revisión (Aaron + socio)
**Design de referencia:** `design_handoff_vendi_pos/Vendi v2.dc.html` + `README.md`
**Specs previas:** `2026-06-24-pos-argentina-design.md`

> Este documento define el stack, la arquitectura y el catálogo de módulos a gran
> escala. NO es la spec de implementación de cada módulo — eso se hace después,
> una spec funcional por módulo con openspec/speckit, iterando cada una.

---

## 1. Stack (decidido)

| Capa | Elección | Por qué |
|------|----------|---------|
| Backend | **Laravel 12** (PHP 8.3) | Maduro para comercio/ERP, ecosistema AR, libs AFIP probadas |
| Scaffold | **Starter kit oficial de React** | Trae Inertia 2 + React 19 + TS + Tailwind v4 + shadcn/ui + auth (Fortify) en un comando |
| Puente front-back | **Inertia.js 2** | React sin API REST separada; menos piezas, un solo repo |
| Frontend | **React 19 + TypeScript** | El design ya es React; tipado para 2 devs |
| UI | **Tailwind CSS v4 + shadcn/ui** | Tokens del design en el bloque `@theme` de `app.css` |
| Charts | **Recharts** | Bar charts del dashboard/reportes |
| DB | **PostgreSQL** | JSONB (campos personalizados), robusto |
| ORM | **Eloquent** | Nativo Laravel; global scopes para tenancy |
| Auth | **Laravel Fortify** (incluido en el kit) | Login, registro, reset, 2FA, passkeys |
| Permisos | **spatie/laravel-permission** | Roles/permisos por tenant |
| Jobs/colas | **Laravel Queue** | WhatsApp, AFIP futuro, cálculos de reposición |
| Build | **Vite** | Viene con Laravel |
| Tests | **Pest** | DX moderno para el back |

**Monorepo único** (Laravel + front Inertia en el mismo repo). Un deploy, un lenguaje
de front (TS), PHP en el back.

## 2. Arquitectura multi-tenant

**Modelo: single database, row-level** (`tenant_id` en cada tabla) con **Global
Scopes de Eloquent** que filtran por tenant automáticamente.

- **Por qué row-level y no multi-DB:** para MVP y decenas/cientos de comercios es
  más simple de operar — una DB, migraciones únicas, reportes cross-tenant
  triviales. Es lo que hace la mayoría de los SaaS. Multi-DB (paquete
  `stancl/tenancy`) se puede adoptar después si un cliente enterprise lo exige.
- **Resolución de tenant:** MVP = por usuario logueado (un `user` pertenece a un
  `tenant`). Futuro: subdominio (`bondi.vendi.app`).
- **Aislamiento:** middleware setea el tenant actual en el request; global scope en
  todos los modelos filtra por `tenant_id`. **Nunca** confiar en el front para el
  scoping. Tests que verifiquen que un tenant no ve datos de otro.

> ⚠️ Decisión load-bearing: `tenant_id` en TODAS las tablas desde el día 1. Barato
> ahora, carísimo de retrofitear. Confirmar en revisión.

## 3. Capas de customización → implementación

Recordatorio del modelo de 6 capas (spec de diseño) y cómo se construye cada una:

| Capa | Implementación en Laravel |
|------|---------------------------|
| 1. Feature flags | tabla `tenant_modules(module, enabled)` → gate en back + condicional en front |
| 2. Campos personalizados | `custom_field_definitions` (tabla) + valores en columna **JSONB** por entidad. JSONB por simplicidad en MVP |
| 3. Branding / white-label | `tenant_settings(store_name, accent_color, logo_path)` → inyectado como variables CSS en el front (re-tematiza en vivo, como en el design) |
| 4. Reglas de negocio | config por tenant (listas de precio, % descuento máx, etc.) |
| 5. Plugins aislados | **interfaces/contracts** + Service Providers. En MVP solo el punto de extensión, no el marketplace |

## 4. Catálogo de módulos

Cada módulo = una **spec funcional con openspec**. Marca MVP vs diferido.

### CORE (MVP)
1. **Auth & Tenancy** — login, usuarios, roles/permisos, tenant actual, aislamiento.
2. **Catálogo / Productos** — productos, variantes talle×color (matriz), categorías,
   precios (costo/venta/mayorista + margen), código de barras, tags, campos
   personalizados, fotos (color-block fallback del design).
3. **Stock / Inventario** — existencias por sucursal, mínimos, alertas, ajustes,
   transferencias entre sucursales.
4. **POS / Ventas** — carrito, modal de variante, descuento, selección de cliente,
   medios de pago, apertura/cierre de caja, ticket. *El corazón vendible.*
5. **Clientes / Cuenta corriente** — clientes, fiado, movimientos (debe/haber/saldo),
   historial de compras.
6. **Caja** — apertura/cierre, arqueo, ingresos/egresos.
7. **Reportes & Dashboard** — KPIs, ventas por período/vendedor/sucursal, rotación,
   margen, ganancia.
8. **Configuración / Customización** — módulos on/off, branding/white-label, campos
   personalizados, datos fiscales (visual, sin backend AFIP aún).
9. **WhatsApp (touchpoints)** — compartir ticket, recordar pago, compartir catálogo.
   MVP = **deep links `wa.me`** (cero infra). Futuro = WhatsApp Business API.
10. **Insights "IA-lite"** — **reglas simples, sin LLM**: días hasta agotar =
    stock ÷ velocidad de venta; sugerencia de reposición por rotación. El copy del
    design ("se agota en ~4 días") es aritmética, no IA.

### DIFERIDO (spec escrita, NO construido en MVP)
- **AFIP / Facturación electrónica** — detrás de interface `FiscalDocumentIssuer`
  (con `NullIssuer` por defecto en MVP). Integración vía **SDK PHP**
  (afipsdk / TusFacturas / Facturante), NC/ND, homologación. **Add-on pago.**
- **Proveedores & Compras** — órdenes de compra, recepción, cta cte proveedor.
- **E-commerce / catálogo online** (sync stock, MercadoPago).
- **Integraciones marketplace** (MercadoLibre, Tienda Nube).
- **Producción / manufactura** (recetas/BOM).
- **Promociones & fidelización** (cupones, puntos).
- **Empleados** (turnos, comisiones).
- **Re-precios masivos** (% / atado al dólar) — dolor inflacionario.
- **WhatsApp Business API** (real, plantillas) e **IA real** (LLM, carga por foto,
  búsqueda en lenguaje natural).

> AFIP se escribe como spec YA (lo pediste), pero se implementa post-MVP. La
> interface y la pantalla de datos fiscales quedan listas para enchufarlo.

## 5. Modelo de datos (alto nivel, MVP)

```
tenants · users · roles · permissions · tenant_modules · tenant_settings
custom_field_definitions
categories · products · product_variants(size, color, barcode) · prices
branches (sucursales) · stock(variant_id, branch_id, qty, min) · stock_movements
customers · account_movements (cuenta corriente / fiado)
cash_registers (caja) · cash_movements
sales · sale_items · payments
sellers (o users con rol vendedor)
fiscal_documents  ← tabla lista pero inactiva (AFIP futuro)
```

Reglas: plata calculada **server-side**; formato `es-AR` (`$ 38.900`) solo en display.
Stock consistente por transacción (una venta descuenta stock atómicamente).

## 6. Puntos de extensión a dejar listos desde el día 1

Baratos ahora, caros de retrofitear:

- `tenant_id` + global scope en todo.
- **`FiscalDocumentIssuer` interface** (`NullIssuer` ahora) → AFIP se enchufa sin
  tocar el POS.
- **`tenant_modules` gate** → prender/apagar módulos sin deploy.
- **Campos personalizados (JSONB)** → el cliente agrega campos sin código.
- **Eventos de dominio** (`SaleCompleted`, `StockLow`, `PaymentReceived`) →
  plugins, insights y WhatsApp se enganchan por eventos, no por código acoplado.

## 7. Cómo encaja openspec/speckit

- Carpeta `openspec/` (o `specs/`) con **una spec funcional por módulo**.
- Cada spec: propósito · entidades · reglas de negocio · acciones/endpoints ·
  referencia a la pantalla del design · **criterios de aceptación** · fuera de alcance.
- Flujo por módulo: **escribir/iterar spec → implementar (TDD con Pest) → validar
  contra criterios de aceptación**.
- El orden de las specs = el orden de build (abajo).

## 8. Orden de construcción del MVP

| Fase | Entrega | Pantallas |
|------|---------|-----------|
| **0. Fundaciones** | Repo, Laravel+Inertia+React+TS, Tailwind con tokens del design, shadcn, Postgres, CI. Tenancy + Auth + roles. Layout base: sidebar, header, toggle claro/oscuro, `tenant_settings` → accent color | Shell / login |
| **1. Catálogo** | Productos, variantes, categorías, precios, campos personalizados | Productos · Ficha |
| **2. Stock** | Existencias por sucursal, mínimos, ajustes, transferencias, insight de reposición (regla) | Stock |
| **3. POS** ⭐ | Caja abrir/cerrar, carrito, modal variante, modal pago, ticket, WhatsApp deep link | Vender |
| **4. Clientes/fiado** | Clientes, cuenta corriente, movimientos, recordar pago WhatsApp | Clientes |
| **5. Dashboard + Reportes** | KPIs, charts, agregados | Inicio · Reportes |
| **6. Configuración** | Módulos on/off, branding, campos personalizados, datos fiscales (visual) | Configuración |

**→ Al terminar la Fase 6 tenés el MVP vendible.** Fase 7+ (AFIP, proveedores,
e-commerce…) según demanda y plata entrando.

## 9. Hosting (referencia, decidir después)

Laravel Forge + VPS (DigitalOcean / Hetzner) o **Laravel Cloud**; Postgres
gestionado; build con Vite. Colas con Redis. Se define al llegar a deploy.

---

## Decisiones tomadas en Fase 0
1. Multi-tenancy **row-level** (`tenant_id` + global scope). ✅ implementado.
2. Scaffold con el **starter kit oficial de React** (auth vía Fortify) +
   spatie/permission. ✅ (reemplaza el Breeze/Jetstream que barajábamos: el kit
   oficial ya trae auth, y Breeze/Jetstream quedaron sin updates).
3. Campos personalizados en **JSONB**. ✅ adoptado (`tenants.settings`, y
   `custom_fields` en las entidades de dominio en Fase 1).
4. `sellers` = **users con rol**. ✅ adoptado (roles `admin` / `vendedor`).
