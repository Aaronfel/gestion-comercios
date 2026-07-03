# Módulo: Proveedores & Compras

- **Fase:** diferido
- **Estado:** ⬜ diferido (post-MVP)
- **Pantalla(s) del design:** — (sin pantalla en el handoff; a diseñar post-MVP)
- **Depende de:** Productos / Catálogo · Stock / Inventario

> **Por qué diferido (post-MVP):** el MVP se enfoca en **vender** (POS, stock,
> clientes). La compra a proveedores es el lado espejo pero no bloquea la primera
> venta: el stock inicial se carga por ajuste manual desde el módulo Stock. Se
> construye cuando el comercio necesita ordenar la reposición y llevar la deuda a
> proveedores de forma sistemática.

## Propósito
Gestionar la **compra** de mercadería: órdenes de compra a proveedores, recepción
(que **suma stock**), costos, y la **cuenta corriente del proveedor** (lo que el
comercio le debe). Es el espejo de Clientes / cuenta corriente, del lado compra.

## Entidades y datos

- **Supplier** — `tenant_id` · `name` · `cuit?` · `email?` · `phone?` · `address?` ·
  `notes?` · `is_active`
- **PurchaseOrder** — `tenant_id` · `supplier_id` · `number` (correlativo por tenant) ·
  `status` (borrador/enviada/recibida_parcial/recibida/cancelada) · `expected_at?` ·
  `received_at?` · `subtotal` · `total` · `notes?`
- **PurchaseOrderItem** — `tenant_id` · `purchase_order_id` · `product_variant_id` ·
  `qty_ordered` · `qty_received` · `unit_cost`
- **SupplierAccountMovement** — `tenant_id` · `supplier_id` · `type`
  (compra/pago/ajuste) · `purchase_order_id?` · `amount` · `balance` (saldo corrido) ·
  `note?` · `occurred_at` — mismo patrón que `account_movements` de Clientes (plan técnico §5).

> Costos en centavos/enteros, cálculo **server-side**, formato `es-AR` solo en
> display. Todos los modelos usan `BelongsToTenant`.

## Reglas de negocio
1. Una OC nace en `borrador`; al enviarse queda `enviada`; se recibe total o parcialmente.
2. **Recibir una OC (o parte) suma stock**: por cada `PurchaseOrderItem` recibido se
   crea un `stock_movement` de entrada (integra con **Stock**), y `qty_received` se acumula.
3. Recepción **parcial** permitida: `recibida_parcial` hasta completar; `qty_received ≤ qty_ordered`.
4. La recepción puede **actualizar el costo** del producto (`cost_price`) con el `unit_cost`
   recibido (según política del tenant: último costo / promedio ponderado).
5. Una compra (OC recibida) genera un `SupplierAccountMovement` tipo **compra** (aumenta la deuda);
   un **pago** la disminuye. El `balance` es saldo corrido **server-side**.
6. `balance > 0` significa que el comercio **le debe** al proveedor (espejo del fiado de clientes).
7. Una OC `recibida` o `cancelada` no se edita; los ajustes van por movimientos/NC futuras.

## Acciones / endpoints

| Acción | Ruta / método | Rol | Descripción |
|--------|---------------|-----|-------------|
| Listar proveedores | `GET /proveedores` | admin | Grilla + saldo de cuenta corriente |
| Ficha proveedor | `GET /proveedores/{supplier}` | admin | Datos, OCs, movimientos, saldo |
| Alta/edición proveedor | `GET/POST/PUT /proveedores/...` | admin | ABM de proveedor |
| Nueva orden de compra | `GET/POST /compras/nueva` | admin | OC con ítems (variante × cantidad × costo) |
| Recibir mercadería | `POST /compras/{order}/recibir` | admin | Suma stock + actualiza costo + movimiento |
| Registrar pago a proveedor | `POST /proveedores/{supplier}/pago` | admin | Disminuye el saldo de cuenta corriente |

## UI
Sin pantalla en el handoff — a diseñar post-MVP, **reutilizando el patrón visual de
Clientes / cuenta corriente** (listado con saldo, ficha con movimientos debe/haber,
recepción como formulario de OC). Componentes shadcn: table, card, badge, dialog,
tabs. Montos en `font-display` tabular. Estado vacío = "Todavía no cargaste proveedores".

## Multi-tenant & permisos
- Scoping: `Supplier`, `PurchaseOrder`, `PurchaseOrderItem`, `SupplierAccountMovement` → `BelongsToTenant`.
- Roles: solo `admin` (compras es gestión, no operación de mostrador). `vendedor` no accede.

## Fuera de alcance
- Facturas de compra fiscales (recepción de comprobantes AFIP) → módulo **AFIP**, post-MVP.
- Sugerencia de reposición automática → módulo **Insights** (regla stock ÷ velocidad).
- Descuento/ajuste de stock por venta → módulo **Stock / POS** (este módulo solo **suma**).
- Multi-moneda / compra en dólares → futuro (atado a re-precios).

## Criterios de aceptación
- [ ] Un `admin` crea una OC con ítems (variante × cantidad × costo) y la envía.
- [ ] Recibir una OC **suma stock** (crea `stock_movement` de entrada por ítem recibido).
- [ ] Una recepción **parcial** deja la OC en `recibida_parcial` y no permite recibir de más.
- [ ] Recibir actualiza el `cost_price` del producto según la política del tenant.
- [ ] Una compra aumenta el saldo del proveedor y un pago lo disminuye (saldo corrido correcto).
- [ ] `vendedor` no accede a compras/proveedores (403).
- [ ] Proveedores/OC/movimientos de otro tenant nunca aparecen (test de aislamiento).

## Notas / preguntas abiertas
- ¿Política de costeo: último costo o promedio ponderado? (afecta margen en Reportes)
- ¿La recepción impacta un solo depósito/sucursal o se elige al recibir?
- ¿Se necesita OC multi-depósito o alcanza con una sucursal por OC en v1?
- ¿Adjuntar la factura del proveedor (archivo) a la OC desde el día 1?
