# Módulo: POS / Ventas

- **Fase:** 3 ⭐ (el corazón vendible)
- **Estado:** ⬜ pendiente
- **Pantalla(s) del design:** Vender (ver `design_handoff_vendi_pos/README.md` §2) — táctil/tablet
- **Depende de:** Productos, Stock, Clientes, Caja

## Propósito
Cobrar rápido y en pantalla táctil: buscar/escanear un producto, elegir variante,
armar el carrito, aplicar descuento, asociar cliente (para fiado) y registrar el
cobro. Descuenta stock de forma atómica, deja la venta y sus pagos asentados contra
la caja abierta, y entrega el ticket (imprimir + enviar por WhatsApp).

## Entidades y datos

- **Sale** — `tenant_id` · `number` (correlativo por tenant) · `branch_id` · `customer_id?`
  (null = Consumidor final) · `cash_register_id` · `user_id` (vendedor) · `subtotal` ·
  `discount_pct` · `total` · `status` (completada/anulada) · `completed_at`
- **SaleItem** — `tenant_id` · `sale_id` · `variant_id` · `qty` · `unit_price` · `line_total`
- **Payment** — `tenant_id` · `sale_id` · `cash_register_id` · `method`
  (efectivo/débito/crédito/transferencia/qr_mp) · `amount`

> Toda la plata se calcula **server-side** (subtotal, descuento, total, líneas); el
> front solo muestra `es-AR` (`$ 38.900`). `unit_price` se congela al momento de la
> venta (no referencia el precio vivo del producto). Todos usan `BelongsToTenant`.

## Reglas de negocio
1. **Requiere caja abierta**: no se puede cobrar sin una `CashRegister` en estado `abierta`; la venta se ata a esa caja (`cash_register_id`).
2. **Descuento atómico de stock** (una transacción DB): confirmar el cobro descuenta cada `SaleItem` del `Stock` de la sucursal y genera un `StockMovement` (`type=venta`). Si una variante no tiene stock suficiente, **toda** la venta se revierte.
3. Cálculo: `subtotal = Σ line_total`; `total = subtotal · (1 − discount_pct/100)`. `discount_pct` clamp 0–90. Stepper de qty mínimo 1.
4. El **fiado** (pago a cuenta corriente) requiere `customer_id` (no Consumidor final) y genera un movimiento en el módulo Clientes; ver §Fuera de alcance.
5. La venta emite el evento de dominio **`SaleCompleted`** (para Insights, WhatsApp, reportes) y registra su cobro en la caja (ver módulo **Caja**: `Payment` contra `cash_register_id`; efectivo impacta el arqueo, los demás medios entran a conciliación).
6. **Ticket interno** (no fiscal): el comprobante es interno. La emisión fiscal (AFIP) queda detrás de la interface `FiscalDocumentIssuer` (`NullIssuer` en MVP) — el hueco existe pero no se dispara.
7. La suma de `Payment.amount` debe igualar el `total` de la venta (un solo medio por ahora; pago mixto es pregunta abierta).

## Acciones / endpoints

| Acción | Ruta / método | Rol | Descripción |
|--------|---------------|-----|-------------|
| Abrir POS | `GET /vender` | admin, vendedor | Picker de productos + panel carrito (estado server: caja, catálogo) |
| Buscar/escanear | `GET /vender/buscar?q=` | admin, vendedor | Por nombre/código/barcode (variante) |
| Registrar venta | `POST /ventas` | admin, vendedor | Valida caja+stock, descuenta atómico, crea Sale/Items/Payments, emite `SaleCompleted` |
| Ticket | `GET /ventas/{sale}/ticket` | admin, vendedor | Comprobante interno (imprimir / deep link `wa.me`) |

## UI
Handoff §2 (pantalla estrella, táctil): dos columnas — izquierda picker (search con
icono **escáner**, pills de categoría, grilla `minmax(158px,1fr)`), derecha **panel
carrito de 392px**. Flujo estrella: tap producto → **modal de variante** (chip color →
celda talle; talles agotados deshabilitados/"agotado"; "Agregar" se habilita al elegir
talle) → carrito (stepper −/n/+, remover ×, selector de cliente con saldo fiado en
ámbar) → **Descuento %** → botón **"Cobrar $…"** (violeta, 54px) → **modal medios de
pago** (Efectivo/Débito/Crédito/Transferencia/QR-MercadoPago, QR full-width, uno
seleccionable; Confirmar deshabilitado hasta elegir) → **modal ticket** ("Venta
registrada", nº, medio, total, ítems, cliente) con **Imprimir** + **Enviar por
WhatsApp** (verde) + "Nueva venta" (limpia el carrito). Vacío: icono + "El carrito está
vacío". Hit targets ≥44px. shadcn: dialog, card, badge, input, button.

## Multi-tenant & permisos
- Scoping: `Sale`, `SaleItem`, `Payment` → `BelongsToTenant`.
- Roles: `admin` y `vendedor` pueden vender (es la operación). La anulación de ventas → admin (pregunta abierta).

## Fuera de alcance
- Apertura/cierre/arqueo de caja → módulo **Caja** (el POS solo la requiere abierta y le asienta el cobro).
- Alta de la deuda en cuenta corriente (fiado) → módulo **Clientes** (el POS dispara el movimiento).
- Existencias, ajustes, transferencias → módulo **Stock** (el POS invoca la baja atómica).
- Facturación fiscal / CAE / AFIP → **diferido**, detrás de `FiscalDocumentIssuer` (hueco listo, sin backend).
- WhatsApp API real → MVP usa deep link `wa.me` (módulo WhatsApp touchpoints).

## Criterios de aceptación
- [ ] Registrar una venta descuenta el stock de cada variante en la sucursal y crea un `StockMovement` por ítem (transacción atómica).
- [ ] Si una variante no tiene stock suficiente, la venta entera se revierte (nada de stock, Sale ni Payment queda persistido).
- [ ] No se puede cobrar sin caja abierta (rechazo).
- [ ] El total = subtotal con `discount_pct` aplicado, calculado server-side; el descuento fuera de 0–90 es rechazado.
- [ ] Una venta completada emite el evento `SaleCompleted`.
- [ ] El cobro queda registrado contra la caja abierta (`Payment.cash_register_id`).
- [ ] El ticket es interno (no se llama a AFIP); `FiscalDocumentIssuer` es `NullIssuer`.
- [ ] Una venta de otro tenant nunca aparece ni afecta el stock propio (test de aislamiento).

## Notas / preguntas abiertas
- **Sucursal del POS:** MVP asume que el POS opera sobre la **sucursal actual** (default Local Centro) y descuenta de ahí. ¿Selector de sucursal en el header o fijo por caja/usuario?
- ¿Pago **mixto** (efectivo + tarjeta en una venta) en v1, o un solo medio por venta?
- ¿La **anulación**/devolución de una venta entra en MVP? (reintegra stock + revierte cobro) — probablemente Fase posterior.
- ¿El correlativo `number` es global por tenant o por sucursal/punto de venta?
