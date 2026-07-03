# Módulo: Promociones & Fidelización

- **Fase:** diferido
- **Estado:** ⬜ diferido (post-MVP)
- **Pantalla(s) del design:** — (sin pantalla en el handoff; a diseñar). Se aplica dentro de **Vender** (POS).
- **Depende de:** POS / Ventas (Fase 3) · Clientes / Cuenta corriente (Fase 4) · Productos (Fase 1)

> **Implementación post-MVP.** Esta spec queda como definición de referencia. No se
> construye en el MVP; se enchufa cuando el comercio quiera correr promos o premiar clientes.

## Propósito
Herramientas de descuento y fidelización que se aplican **en el POS al cobrar**:
cupones de descuento, promociones (2x1, % por categoría, combos) y un programa de
**puntos** por cliente. Objetivo: subir el ticket promedio y la recurrencia sin tocar
los precios de lista.

## Entidades y datos

- **Coupon** (cupón) — `tenant_id` · `code` (único por tenant) · `type` (percent/fixed) ·
  `value` · `min_amount?` · `usage_limit?` · `used_count` · `valid_from` · `valid_to` · `active`
- **Promotion** (promo) — `tenant_id` · `name` · `type` (2x1 / percent_category / combo) ·
  `config: json` (categorías, productos, cantidades, % ) · `valid_from` · `valid_to` ·
  `priority` · `active`
- **LoyaltyTransaction** (movimiento de puntos) — `tenant_id` · `customer_id` · `sale_id?` ·
  `type` (earn/redeem/adjust) · `points` · `balance_after` · `created_at`
- **Customer.points_balance** — saldo de puntos denormalizado en el cliente (config del
  programa —puntos por $, valor de canje— vive en Configuración del tenant).

> Todos los cálculos de descuento/puntos son **server-side** (nunca confiar en el POS
> del front). Modelos de dominio usan `BelongsToTenant`.

## Reglas de negocio
1. Un `Coupon` se valida al cobrar: activo, dentro de vigencia, `min_amount` alcanzado,
   y `used_count < usage_limit`. Al confirmar la venta incrementa `used_count`.
2. Las `Promotion` se evalúan automáticamente sobre el carrito según `type`/`config`;
   `priority` resuelve el orden de aplicación.
3. **Combinación:** por defecto **no se stackean** cupón + promo sobre el mismo ítem
   (config del tenant puede permitirlo). El descuento total nunca deja el ítem en negativo.
4. **Puntos:** al completar la venta (evento `SaleCompleted`) el cliente **acumula**
   puntos según la config; el **canje** genera una `LoyaltyTransaction` de tipo `redeem`
   que descuenta del total. `balance_after` se recalcula server-side.
5. Toda venta con promo/cupón/puntos queda referenciada para reportes y auditoría.

## Acciones / endpoints

| Acción | Ruta / método | Rol | Descripción |
|--------|---------------|-----|-------------|
| CRUD cupones | `/promociones/cupones…` | admin | Alta/baja/edición de cupones |
| CRUD promos | `/promociones/promos…` | admin | Alta/baja/edición de promociones |
| Config programa de puntos | `/configuracion/fidelizacion` | admin | Puntos por $, valor de canje |
| Validar cupón (POS) | `POST /pos/cupon` | admin, vendedor | Devuelve descuento aplicable |
| Aplicar/recalcular carrito | `POST /pos/promos` | admin, vendedor | Descuentos server-side del carrito |
| Ver puntos del cliente | `GET /clientes/{customer}/puntos` | admin, vendedor | Saldo + historial |

## UI
Sin pantalla propia en el handoff. Se integra en **Vender**: campo de cupón, badge de
promo aplicada en el carrito, y saldo de puntos del cliente seleccionado con opción de
canje. ABM de cupones/promos como listados. Componentes shadcn: table, card, badge,
dialog, input, switch. Números en `font-display` tabular.

## Multi-tenant & permisos
- Scoping: `Coupon`, `Promotion`, `LoyaltyTransaction` → `BelongsToTenant`. `points_balance` en `Customer` (ya scopeado).
- Roles: `admin` crea/configura promos, cupones y programa; `vendedor` solo aplica en el POS.

## Fuera de alcance
- Envío de campañas/promos por WhatsApp → módulo **WhatsApp (touchpoints)**.
- Segmentación avanzada de clientes / marketing automation → futuro.
- Vencimiento y expiración automática de puntos → evaluar en v2 (dejar el hueco).

## Criterios de aceptación
- [ ] Un cupón vencido, inactivo o sin usos disponibles es rechazado en el POS.
- [ ] Una promo 2x1 y un % por categoría se calculan correctamente server-side sobre el carrito.
- [ ] Cupón y promo no se stackean sobre el mismo ítem salvo que la config lo permita.
- [ ] Completar una venta acumula puntos al cliente y el canje descuenta del total.
- [ ] El `balance_after` de puntos nunca queda negativo ni permite canjear más de lo disponible.
- [ ] Un tenant nunca ve cupones/promos/puntos de otro tenant (test de aislamiento).

## Notas / preguntas abiertas
- ¿Reglas de combinación por defecto: nunca stackear, o stackear con tope de descuento?
- ¿Puntos con vencimiento (ej. 12 meses) desde v1 o después?
- ¿Cupones nominales (atados a un cliente) además de genéricos por código?
