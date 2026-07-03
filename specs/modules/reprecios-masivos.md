# Módulo: Re-precios Masivos

- **Fase:** diferido
- **Estado:** ⬜ diferido (post-MVP)
- **Pantalla(s) del design:** — (sin pantalla en el handoff; a diseñar)
- **Depende de:** Productos / Catálogo (Fase 1)

> **Implementación post-MVP.** Esta spec queda como definición de referencia. No se
> construye en el MVP, **pero es un diferenciador muy valorado en Argentina**: es
> supervivencia inflacionaria y Pulpos no lo tiene. Prioridad alta apenas se pase el MVP.

## Propósito
Actualizar precios **en masa** sin editar producto por producto — el dolor cotidiano de
un comercio argentino con inflación. Permite subir todo un % , actualizar por
categoría/lista o por proveedor, y **atar precios al dólar** (precio en USD → recalcula
ARS según cotización). Con **preview** antes de aplicar, **historial** de cada corrida y
**rollback**.

## Entidades y datos

- **PriceUpdateBatch** (corrida de re-precio) — `tenant_id` · `name?` ·
  `strategy` (percent / category_list / usd / supplier) ·
  `target_field` (cost_price / sale_price / wholesale_price) ·
  `scope: json` (filtros: categoría, proveedor, lista) · `params: json` (%, cotización USD, redondeo) ·
  `status` (borrador/preview/aplicado/revertido) · `applied_at?` · `applied_by?` · `items_count`
- **PriceUpdateItem** (detalle por producto) — `tenant_id` · `price_update_batch_id` ·
  `product_id` · `target_field` · `old_price` · `new_price`

> Todo el recálculo es **server-side**; formato `es-AR` solo en display. Modelos usan
> `BelongsToTenant`. Opera sobre `Product` (`cost_price` / `sale_price` / `wholesale_price`) del módulo **Productos**.

## Reglas de negocio
1. **Preview obligatorio:** calcular `new_price` de cada producto del `scope` **sin** tocar
   `Product`; se persiste como `PriceUpdateItem` para revisar antes de confirmar.
2. **Aplicar:** actualiza los precios de `Product` en una **transacción atómica**,
   guardando `old_price`/`new_price` en cada item (esto **es** el historial). `status = aplicado`.
3. **Rollback:** restaura cada `old_price` desde los items (mientras no haya una corrida
   posterior sobre los mismos productos que lo invalide). `status = revertido`.
4. Estrategias: `percent` (todo × factor), `category_list` (filtrado por categoría/lista),
   `usd` (precio en USD × cotización), `supplier` (por proveedor).
5. **Redondeo** configurable (a $10 / $100 / terminación .90) aplicado tras el cálculo.
6. Estrategia `usd` requiere que el producto tenga precio/costo en USD; si falta, el item se
   marca omitido (no rompe la corrida).

## Acciones / endpoints

| Acción | Ruta / método | Rol | Descripción |
|--------|---------------|-----|-------------|
| Listar corridas | `GET /reprecios` | admin | Historial de batches |
| Nueva corrida | `GET/POST /reprecios/nuevo` | admin | Elegir estrategia, scope y params |
| Preview | `POST /reprecios/{batch}/preview` | admin | Calcula sin aplicar (tabla old→new) |
| Aplicar | `POST /reprecios/{batch}/aplicar` | admin | Persiste precios (atómico) |
| Ver detalle | `GET /reprecios/{batch}` | admin | Items old→new de la corrida |
| Revertir | `POST /reprecios/{batch}/revertir` | admin | Restaura `old_price` |

## UI
Sin pantalla en el handoff — a diseñar con los tokens del design. Wizard: elegir estrategia
→ definir scope (categoría/proveedor/lista) → parámetros (%, cotización, redondeo) →
**preview** en tabla `old → new` con delta y % → confirmar. Historial con badge de estado y
botón de revertir. Componentes shadcn: table, card, badge, dialog, input, select. Números en
`font-display` tabular, deltas en color según suba/baje.

## Multi-tenant & permisos
- Scoping: `PriceUpdateBatch`, `PriceUpdateItem` → `BelongsToTenant`.
- Roles: `admin` únicamente (afecta precios de todo el catálogo); `vendedor` sin acceso.

## Fuera de alcance
- Cotización del dólar automática (API BCRA / dólar blue) → futuro; en v1 se ingresa manual.
- Sincronización de los nuevos precios a e-commerce / marketplaces → módulos respectivos.
- Programar corridas a futuro / recurrentes → evaluar en v2.

## Criterios de aceptación
- [ ] El preview calcula `new_price` de todo el scope **sin** modificar los productos.
- [ ] Aplicar una corrida por % actualiza los precios atómicamente y guarda `old→new` por item.
- [ ] Una corrida atada al dólar recalcula ARS = precio USD × cotización ingresada.
- [ ] El redondeo configurado se aplica al `new_price` resultante.
- [ ] Revertir una corrida restaura los `old_price` de sus items.
- [ ] Un producto sin precio USD se omite en una corrida `usd` sin romper el resto.
- [ ] Un tenant nunca ve corridas ni precios de otro tenant (test de aislamiento).

## Notas / preguntas abiertas
- **Falta un campo de precio en USD en `Product`** (hoy Productos no lo tiene): ¿columna
  dedicada `usd_price`/`usd_cost` o vía `custom_fields`? Bloquea la estrategia `usd`.
- ¿El scope opera sobre `Product` o también por `ProductVariant`?
- ¿Bloquear el rollback si hubo una corrida posterior sobre los mismos productos?
- ¿Guardar la cotización usada en el batch para trazabilidad histórica? (recomendado)
