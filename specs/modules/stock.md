# Módulo: Stock / Inventario

- **Fase:** 2
- **Estado:** ⬜ pendiente
- **Pantalla(s) del design:** Stock (ver `design_handoff_vendi_pos/README.md` §5)
- **Depende de:** Productos / Catálogo (Fase 1)

## Propósito
Controlar las existencias del comercio por sucursal (Local Centro / Depósito):
cuánto hay de cada variante y dónde, cuándo hay que reponer, y dejar auditado todo
movimiento (ajustes, transferencias, ventas). Es la fuente de verdad que el POS
descuenta al vender y que alimenta las alertas de stock bajo.

## Entidades y datos

- **Branch** (sucursal) — `tenant_id` · `name` · `is_default: bool` (una por tenant)
- **Stock** — `tenant_id` · `variant_id` · `branch_id` · `qty: int` · `min: int`
  · (único por `variant_id`+`branch_id`)
- **StockMovement** (auditoría) — `tenant_id` · `variant_id` · `branch_id` ·
  `type` (ajuste/transferencia_in/transferencia_out/venta) · `qty_delta: int` (con signo)
  · `reason?` · `user_id` · `reference_type?`/`reference_id?` (ej. venta) · `created_at`

> Existencias en enteros (unidades). `min` define el umbral de reposición. Todos los
> modelos usan `BelongsToTenant`. Sin filas de `Stock` para una variante → se asume 0.

## Reglas de negocio
1. El **estado** por variante×sucursal es calculado, no almacenado: `qty ≤ min` → **Reponer** (danger); si no → **OK** (accent).
2. Un **ajuste** setea `qty` a un valor absoluto o suma/resta un delta; siempre genera un `StockMovement` con el `qty_delta` resultante y `reason`.
3. Una **transferencia** entre sucursales es atómica (transacción): resta en origen (`transferencia_out`) y suma en destino (`transferencia_in`) por la misma cantidad; no puede dejar el origen en negativo.
4. El **stock nunca queda negativo**: ajustes y transferencias validan disponibilidad antes de aplicar.
5. Toda variación de `qty` (ajuste, transferencia, venta del POS) deja **un** `StockMovement` — el histórico es inmutable (append-only, no se edita ni borra).
6. El **total** de una variante = suma de `qty` en todas las sucursales; se muestra en danger cuando el total cae en Reponer.

## Acciones / endpoints

| Acción | Ruta / método | Rol | Descripción |
|--------|---------------|-----|-------------|
| Listar | `GET /stock` | admin, vendedor | Tabla por sucursal, filtro de pills por branch |
| Ajustar stock | `POST /stock/ajustar` | admin | Setea/ajusta `qty` de una variante×sucursal + genera movimiento |
| Transferir | `POST /stock/transferir` | admin | Mueve cantidad entre sucursales (atómico) |
| Ver movimientos | `GET /stock/movimientos` | admin | Auditoría (filtro por variante/sucursal/tipo) |

## UI
Handoff §5: pills de sucursal (**Local Centro** activa / **Depósito**), botones
**Ajustar stock** (outline) y **Transferir** (violeta). Tabla: Producto (thumb+nombre+code)
· Local Centro · Depósito · Total · Mínimo · Estado (Reponer=danger / OK=accent).
El hero de **sugerencia de reposición IA** (gradiente violeta→ámbar) lo provee el
módulo **Insights "IA-lite"** (regla `días hasta agotar = stock ÷ velocidad`) — acá
solo se referencia, no se define. Componentes shadcn: table, badge, dialog, tabs.
Números en `font-display` tabular.

## Multi-tenant & permisos
- Scoping: `Branch`, `Stock`, `StockMovement` → `BelongsToTenant`.
- Roles: `admin` ajusta/transfiere y ve la auditoría; `vendedor` solo lectura.

## Fuera de alcance
- Definición de productos/variantes/precios → módulo **Productos**.
- Descuento de stock por venta → lo dispara el **POS** (esta spec expone la operación atómica de baja).
- Sugerencia de reposición / "se agota en ~4 días" → módulo **Insights "IA-lite"**.
- Compras/recepción de mercadería a proveedor → **Proveedores & Compras** (diferido).

## Criterios de aceptación
- [ ] Un admin puede ajustar el stock de una variante en una sucursal y queda registrado un `StockMovement` con el delta correcto.
- [ ] Una transferencia mueve la cantidad de origen a destino sin cambiar el total, y genera dos movimientos (out + in).
- [ ] Una transferencia que dejaría el origen en negativo es rechazada.
- [ ] El estado se calcula: `qty ≤ min` muestra **Reponer**; `qty > min` muestra **OK**.
- [ ] El total por variante es la suma de las sucursales.
- [ ] `vendedor` no puede ajustar ni transferir stock (403).
- [ ] El stock de otro tenant nunca aparece en el listado (test de aislamiento).

## Notas / preguntas abiertas
- ¿Las sucursales son fijas (Local Centro / Depósito seedeadas) o el admin las gestiona (ABM) en v1?
- ¿El ajuste requiere motivo obligatorio (rotura, conteo, robo) para reportería futura?
- ¿La transferencia es inmediata o hay estado "en tránsito" (recepción confirmada)? MVP: inmediata.
