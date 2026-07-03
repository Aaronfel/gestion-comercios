# Vendi — Specs funcionales por módulo

Esta carpeta contiene **una definición funcional por módulo**: qué se espera de
cada uno (entidades, reglas de negocio, acciones, UI, criterios de aceptación).
Es la fuente para el desarrollo spec-driven — se escribe/itera la spec **antes**
de implementar.

Compatible con herramientas de spec-driven development (OpenSpec / GitHub Spec
Kit) si se adoptan: estos archivos son las definiciones; la herramienta elegida
puede apuntar acá o migrarlos.

## Workflow por módulo

1. Copiar `_TEMPLATE.md` → `modules/<modulo>.md`.
2. Completar e iterar la spec (con el socio) hasta que quede precisa.
3. Implementar guiado por la spec, **TDD con Pest** (test → código → validar).
4. Marcar los criterios de aceptación al cumplirse.

El orden de las specs sigue el orden de build del plan técnico
(`docs/superpowers/specs/2026-07-01-plan-tecnico-mvp.md`).

## Índice de módulos

Estados: ✅ implementado · 🏗️ parcial · 📝 definido (a iterar) · ⬜ diferido.
Todas las specs base están escritas; se **iteran** con el socio antes de construir cada módulo.

### CORE (MVP)
| Módulo | Fase | Pantalla(s) | Estado | Spec |
|--------|------|-------------|--------|------|
| Auth & Tenancy | 0 + 0.5 | Login / shell | 🏗️ parcial (scaffold ok, faltan onboarding/invitaciones/roles) | [modules/auth-tenancy.md](modules/auth-tenancy.md) |
| Productos / Catálogo | 1 | Productos · Ficha | 📝 definido | [modules/productos.md](modules/productos.md) |
| Stock / Inventario | 2 | Stock | 📝 definido | [modules/stock.md](modules/stock.md) |
| POS / Ventas | 3 | Vender | 📝 definido | [modules/pos-ventas.md](modules/pos-ventas.md) |
| Caja | 3 | (header POS) | 📝 definido | [modules/caja.md](modules/caja.md) |
| Clientes / Cuenta corriente | 4 | Clientes | 📝 definido | [modules/clientes-cuenta-corriente.md](modules/clientes-cuenta-corriente.md) |
| Reportes & Dashboard | 5 | Inicio · Reportes | 📝 definido | [modules/reportes-dashboard.md](modules/reportes-dashboard.md) |
| Configuración / Customización | 6 | Configuración | 📝 definido | [modules/configuracion.md](modules/configuracion.md) |
| WhatsApp (touchpoints) | 3–6 | POS · Clientes · Config | 📝 definido | [modules/whatsapp.md](modules/whatsapp.md) |
| Insights "IA-lite" | 2–5 | Inicio · Stock | 📝 definido | [modules/insights-ia-lite.md](modules/insights-ia-lite.md) |

### DIFERIDO (spec escrita, implementación post-MVP)
| Módulo | Estado | Spec |
|--------|--------|------|
| AFIP / Facturación electrónica | ⬜ diferido | [modules/afip-facturacion.md](modules/afip-facturacion.md) |
| Proveedores & Compras | ⬜ diferido | [modules/proveedores-compras.md](modules/proveedores-compras.md) |
| E-commerce / catálogo online | ⬜ diferido | [modules/ecommerce.md](modules/ecommerce.md) |
| Integraciones marketplace | ⬜ diferido | [modules/integraciones-marketplace.md](modules/integraciones-marketplace.md) |
| Producción / manufactura | ⬜ diferido | [modules/produccion.md](modules/produccion.md) |
| Promociones & fidelización | ⬜ diferido | [modules/promociones-fidelizacion.md](modules/promociones-fidelizacion.md) |
| Empleados | ⬜ diferido | [modules/empleados.md](modules/empleados.md) |
| Re-precios masivos (% / dólar) | ⬜ diferido | [modules/reprecios-masivos.md](modules/reprecios-masivos.md) |

> AFIP se implementa post-MVP detrás de la interface `FiscalDocumentIssuer`
> (`NullIssuer` por ahora). La spec y el hueco de datos fiscales ya existen.
