# Módulo: Caja

- **Fase:** 3
- **Estado:** ⬜ pendiente
- **Pantalla(s) del design:** pill de **Caja** en el header (abrir/cerrar) + KPI "Estado de caja" del dashboard (ver `design_handoff_vendi_pos/README.md` §Inicio y §Interactions)
- **Depende de:** Auth & Tenancy (Fase 0). Integra con POS/Ventas (Fase 3).

## Propósito
Controlar el dinero del turno: abrir la caja con un monto inicial, registrar los
cobros de las ventas y los ingresos/egresos manuales, y cerrarla con arqueo
(conteo de efectivo) y conciliación (totales por medio de pago). Es lo que hace
que "¿cuánta plata hay?" tenga una respuesta auditable al final del día.

## Entidades y datos

- **CashRegister** (caja) — `tenant_id` · `branch_id?` · `opened_by` (user) · `closed_by?` (user)
  · `opening_amount` · `counted_amount?` (efectivo contado al cierre) · `status`
  (abierta/cerrada) · `opened_at` · `closed_at?`
- **CashMovement** — `tenant_id` · `cash_register_id` · `type`
  (apertura/venta/ingreso/egreso) · `payment_method?` (efectivo/débito/…, para ventas)
  · `amount: int` (con signo) · `description?` · `sale_id?` · `user_id` · `created_at`

> Toda la plata se calcula **server-side**; el front muestra `es-AR` (`$ 38.900`).
> Solo puede haber **una** caja `abierta` por sucursal a la vez. Todos los modelos
> usan `BelongsToTenant`.

## Reglas de negocio
1. **Estados:** `abierta` → `cerrada` (irreversible). No se puede reabrir una caja cerrada; se abre una nueva.
2. **Apertura:** crea una `CashRegister` `abierta` con `opening_amount` y un `CashMovement` `type=apertura`. Falla si ya hay una caja abierta en esa sucursal.
3. **Una venta del POS** registra su cobro contra la caja abierta: un `CashMovement` `type=venta` con `payment_method` y `amount` (ligado al `sale_id`). El **efectivo** impacta el arqueo del cajón; los demás medios (débito/crédito/transferencia/QR) entran a la conciliación pero **no** al efectivo esperado.
4. **Ingresos/egresos manuales:** el usuario puede sumar (ingreso) o retirar (egreso) efectivo con descripción; cada uno es un `CashMovement`. El egreso no puede dejar el efectivo esperado en negativo.
5. **Efectivo esperado** (calculado) = `opening_amount` + Σ ingresos − Σ egresos + Σ ventas en efectivo.
6. **Cierre / arqueo:** al cerrar se ingresa `counted_amount` (lo contado); la **diferencia** = `counted_amount − efectivo esperado` (calculada, puede ser +/−). La **conciliación** muestra los totales por medio de pago del turno.
7. El **KPI "Estado de caja"** (dashboard) y la **pill del header** reflejan `status` y el disponible en tiempo real.

## Acciones / endpoints

| Acción | Ruta / método | Rol | Descripción |
|--------|---------------|-----|-------------|
| Abrir caja | `POST /caja/abrir` | admin, vendedor | Monto inicial → caja `abierta` + movimiento apertura |
| Ingreso/egreso | `POST /caja/movimiento` | admin, vendedor | Suma/retira efectivo con descripción |
| Cerrar caja | `POST /caja/cerrar` | admin | Arqueo (`counted_amount`) → diferencia + conciliación |
| Ver caja / arqueo | `GET /caja` | admin, vendedor | Estado, movimientos del turno, esperado vs contado |

## UI
Handoff: **pill de Caja** en el header (abrir/cerrar, toast al cambiar de estado) y
KPI **"Estado de caja"** en el dashboard (Abierta = accent, "`$ 142.300` disponible").
Modal de apertura (monto inicial) y de cierre (conteo de efectivo + resumen de
conciliación con diferencia en danger si no cuadra). shadcn: dialog, card, badge,
input. Montos en `font-display` tabular; disponible/diferencia con color por signo.

## Multi-tenant & permisos
- Scoping: `CashRegister`, `CashMovement` → `BelongsToTenant`.
- Roles: `admin` y `vendedor` abren y registran movimientos/ventas; el **cierre con arqueo** lo hace `admin` (pregunta abierta).

## Fuera de alcance
- Registro de la venta en sí (Sale/Items/Payment) → módulo **POS/Ventas** (la caja solo asienta el `CashMovement` del cobro).
- Reportes de caja por período/vendedor → módulo **Reportes & Dashboard**.
- Conciliación bancaria real (matcheo con extractos) → futuro.
- Múltiples cajas/puntos de venta simultáneos por sucursal → MVP: una abierta por sucursal.

## Criterios de aceptación
- [ ] Abrir una caja crea una `CashRegister` `abierta` con `opening_amount` y un movimiento de apertura.
- [ ] No se puede abrir una segunda caja si ya hay una `abierta` en la sucursal (rechazo).
- [ ] Una venta en efectivo del POS suma un `CashMovement` `type=venta` a la caja abierta y aumenta el efectivo esperado.
- [ ] Una venta con débito/transferencia queda en conciliación pero **no** altera el efectivo esperado.
- [ ] Un egreso mayor al efectivo esperado es rechazado.
- [ ] Cerrar la caja calcula la diferencia (`counted_amount − esperado`) y deja `status=cerrada`; no se puede reabrir.
- [ ] `vendedor` no puede cerrar la caja (403).
- [ ] La caja de otro tenant nunca aparece ni recibe movimientos propios (test de aislamiento).

## Notas / preguntas abiertas
- ¿El cierre lo restringimos a `admin` o el `vendedor` también arquea su turno?
- ¿Una caja es por **sucursal**, por **usuario/turno**, o por **terminal**? (afecta `branch_id`/multi-terminal)
- ¿Los medios no-efectivo se concilian solo como reporte o hay un "esperado" por medio (ej. lote de tarjeta)?
- ¿Retiro parcial de efectivo a banco (egreso) necesita comprobante/categoría?
