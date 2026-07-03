# Módulo: Producción / Manufactura

- **Fase:** diferido
- **Estado:** ⬜ diferido (post-MVP)
- **Pantalla(s) del design:** — (sin pantalla en el handoff; a diseñar cuando se construya)
- **Depende de:** Productos / Catálogo (Fase 1) · Stock / Inventario (Fase 2)

> **Implementación post-MVP.** Esta spec queda como definición de referencia. No se
> construye en el MVP; se enchufa cuando aparezca un comercio que fabrica (típico en
> indumentaria que produce sus propias prendas).

## Propósito
Para comercios que **fabrican** lo que venden: definir de qué insumos se compone un
producto terminado (receta / BOM) y ejecutar **órdenes de producción** que consumen
insumos y dan de alta stock de terminados. Convierte materia prima en producto
vendible, con la baja/alta de stock reflejada automáticamente.

## Entidades y datos

- **BillOfMaterials** (receta) — `tenant_id` · `product_id` (producto terminado) ·
  `name` · `version` · `active` · `notes?`
- **BomItem** (insumo de la receta) — `tenant_id` · `bill_of_materials_id` ·
  `component_product_id` (insumo/materia prima, también un `Product`) · `quantity` ·
  `unit` (u/m/kg…) · `waste_pct?` (merma)
- **ProductionOrder** (orden de producción) — `tenant_id` · `bill_of_materials_id` ·
  `product_id` (terminado) · `branch_id` (depósito destino) · `quantity_planned` ·
  `quantity_produced?` · `status` (borrador/en_proceso/finalizada/cancelada) ·
  `started_at?` · `finished_at?` · `cost_snapshot?` (costo unitario congelado al finalizar)

> Un insumo es un `Product` común (materia prima) con su stock. Cantidades y costos
> se calculan **server-side**. Todos los modelos usan `BelongsToTenant`.

## Reglas de negocio
1. Una `BillOfMaterials` define los insumos necesarios para **una unidad** de terminado.
2. Al **finalizar** una orden: se **baja** stock de cada insumo (`quantity × waste × quantity_produced`)
   y se **sube** stock del terminado (`quantity_produced`) en `branch_id`. Todo en una
   **transacción atómica** que emite `stock_movements`.
3. `cost_snapshot` = suma de costos de insumos consumidos / unidades producidas — calculado al finalizar.
4. Validar insumos suficientes antes de finalizar (config del tenant decide si permite negativo).
5. Transiciones válidas: `borrador → en_proceso → finalizada`; `borrador/en_proceso → cancelada`.
   Una orden `finalizada` o `cancelada` es inmutable (para no descuadrar el stock).
6. Cancelar una orden **finalizada** no está permitido; se revierte con un ajuste de stock manual.

## Acciones / endpoints

| Acción | Ruta / método | Rol | Descripción |
|--------|---------------|-----|-------------|
| Listar recetas | `GET /produccion/recetas` | admin | BOMs del tenant |
| Crear/editar receta | `GET/POST/PUT /produccion/recetas/{bom?}` | admin | Terminado + insumos |
| Listar órdenes | `GET /produccion/ordenes` | admin | Filtro por estado/fecha |
| Crear orden | `POST /produccion/ordenes` | admin | Elige receta y cantidad |
| Iniciar orden | `PATCH /produccion/ordenes/{order}/iniciar` | admin | `borrador → en_proceso` |
| Finalizar orden | `PATCH /produccion/ordenes/{order}/finalizar` | admin | Aplica movimientos de stock |
| Cancelar orden | `PATCH /produccion/ordenes/{order}/cancelar` | admin | Solo borrador/en_proceso |

## UI
Sin pantalla en el handoff — a diseñar respetando los tokens del design. Listado de
recetas y de órdenes (tabla con badge de estado). Wizard de receta (terminado + tabla
de insumos con cantidad/unidad). Al finalizar, resumen de insumos a consumir y stock a
generar. Componentes shadcn: table, card, badge, dialog, tabs. Números en `font-display` tabular.

## Multi-tenant & permisos
- Scoping: `BillOfMaterials`, `BomItem`, `ProductionOrder` → `BelongsToTenant`.
- Roles: `admin` gestiona recetas y órdenes. (Rol `produccion` a evaluar si hace falta.)

## Fuera de alcance
- Compra/recepción de insumos → módulo **Proveedores & Compras**.
- Costeo de mano de obra, gastos indirectos y planificación tipo MRP → futuro.
- Trazabilidad por lote/serie del terminado → módulo **Stock** (lotes) si se agrega.

## Criterios de aceptación
- [ ] Un admin puede definir una receta (terminado + N insumos con cantidad y unidad).
- [ ] Finalizar una orden **baja** el stock de los insumos y **sube** el del terminado, atómicamente.
- [ ] Finalizar con insumos insuficientes se rechaza (según config del tenant).
- [ ] El `cost_snapshot` del terminado se calcula a partir del costo de los insumos.
- [ ] Una orden finalizada no puede volver a editarse ni cancelarse.
- [ ] Un tenant nunca ve recetas ni órdenes de otro tenant (test de aislamiento).

## Notas / preguntas abiertas
- ¿La receta apunta a `Product` o a `ProductVariant` (talle×color) del terminado?
- ¿Permitir producción parcial (`quantity_produced` < `quantity_planned`) y saldo pendiente?
- ¿Se necesita permitir stock negativo de insumos, o bloquear siempre?
