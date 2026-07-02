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

### CORE (MVP)
| Módulo | Fase | Pantalla(s) | Estado | Spec |
|--------|------|-------------|--------|------|
| Auth & Tenancy | 0 | Login / shell | ✅ implementado (Fase 0) | — |
| Productos / Catálogo | 1 | Productos · Ficha | 📝 ejemplo escrito | [modules/productos.md](modules/productos.md) |
| Stock / Inventario | 2 | Stock | ⬜ pendiente | — |
| POS / Ventas | 3 | Vender | ⬜ pendiente | — |
| Clientes / Cuenta corriente | 4 | Clientes | ⬜ pendiente | — |
| Caja | 3 | (header POS) | ⬜ pendiente | — |
| Reportes & Dashboard | 5 | Inicio · Reportes | ⬜ pendiente | — |
| Configuración / Customización | 6 | Configuración | ⬜ pendiente | — |
| WhatsApp (touchpoints) | 3–6 | POS · Clientes · Config | ⬜ pendiente | — |
| Insights "IA-lite" | 2–5 | Inicio · Stock | ⬜ pendiente | — |

### DIFERIDO (spec a escribir, implementación post-MVP)
| Módulo | Pantalla(s) | Estado | Spec |
|--------|-------------|--------|------|
| AFIP / Facturación electrónica | (Config datos fiscales) | ⬜ pendiente | — |
| Proveedores & Compras | — | ⬜ pendiente | — |
| E-commerce / catálogo online | — | ⬜ pendiente | — |
| Integraciones marketplace | — | ⬜ pendiente | — |
| Producción / manufactura | — | ⬜ pendiente | — |
| Promociones & fidelización | — | ⬜ pendiente | — |
| Empleados | — | ⬜ pendiente | — |
| Re-precios masivos (% / dólar) | — | ⬜ pendiente | — |

> AFIP se escribe como spec desde ya (queda como definición lista para enchufar),
> pero se implementa post-MVP detrás de la interface `FiscalDocumentIssuer`.
