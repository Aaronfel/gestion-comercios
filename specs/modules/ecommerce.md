# Módulo: E-commerce / catálogo online

- **Fase:** diferido
- **Estado:** ⬜ diferido (post-MVP)
- **Pantalla(s) del design:** — (sin pantalla en el handoff; a diseñar post-MVP:
  tienda pública + bandeja de pedidos entrantes)
- **Depende de:** Productos / Catálogo · Stock / Inventario · POS / Ventas · WhatsApp (touchpoints)

> **Por qué diferido (post-MVP):** el MVP resuelve la venta **presencial** (POS).
> La tienda online agrega superficie pública, checkout y cobro remoto — valioso,
> pero no bloquea vender en el mostrador. Se construye cuando el comercio quiere
> vender fuera del local, apoyándose en el mismo catálogo y stock ya existentes.

## Propósito
Publicar una **tienda online** con el catálogo del comercio, recibir **pedidos
entrantes**, resolver el checkout con **pago MercadoPago** y aceptar **pedidos por
WhatsApp**. El punto clave: es el **mismo stock** que el POS — no hay dos
inventarios que sincronizar.

## Arquitectura (clave — una sola fuente de verdad de stock)
La tienda **reusa `Product` / `ProductVariant` / `Stock`**: no existe un catálogo
ni un stock "online" separado. Un pedido online **reserva y descuenta las mismas
filas de `Stock`** que una venta del POS. Por eso "sync en tiempo real" no es una
sincronización real: **es una sola tabla**. Una venta física baja el stock que ve
la tienda, y un pedido online baja el stock que ve el POS, **automáticamente**.
El enganche es por **evento de dominio** (`SaleCompleted` / `StockLow`), no por
código acoplado ni un job de reconciliación.

## Entidades y datos

- **OnlineOrder** — `tenant_id` · `customer_id?` · `number` (correlativo por tenant) ·
  `channel` (web/whatsapp) · `status` (pendiente/pagado/preparando/enviado/entregado/cancelado) ·
  `payment_status` (pendiente/aprobado/rechazado) · `payment_provider_ref?` (id MercadoPago) ·
  `shipping` (retiro/envío) · `contact` (nombre/tel/dirección: json) · `subtotal` · `total`
- **OnlineOrderItem** — `tenant_id` · `online_order_id` · `product_variant_id` ·
  `qty` · `unit_price` — **reusa `Product`/`ProductVariant`/`Stock`**, no duplica catálogo.

> Precios/plata calculados **server-side**, formato `es-AR` solo en display. Todos
> los modelos de dominio usan `BelongsToTenant`. La tienda pública lee catálogo
> filtrado por el tenant dueño de la tienda.

## Reglas de negocio
1. La tienda muestra **solo** productos `status = activo` con **stock disponible**.
2. Al confirmar el pedido, el stock se **reserva**; al aprobarse el pago, se **descuenta** (mismas filas de `Stock`).
3. Si el stock **no alcanza** al momento de confirmar (lo tomó una venta física), el pedido se rechaza / avisa.
4. Un pedido **web** cobra con **MercadoPago**; el pedido pasa a `pagado` solo con confirmación del proveedor (webhook).
5. Un pedido por **WhatsApp** puede quedar `pendiente` de pago manual (no siempre pasa por checkout online).
6. Cancelar un pedido con stock reservado/descontado **repone** el stock.
7. El precio publicado sale de la **lista de precios del tenant** (venta minorista por defecto).

## Acciones / endpoints

| Acción | Ruta / método | Rol | Descripción |
|--------|---------------|-----|-------------|
| Tienda pública (catálogo) | `GET /t/{tienda}` | público | Vitrina del tenant, filtrada por stock |
| Detalle de producto | `GET /t/{tienda}/{product}` | público | Variantes con disponibilidad |
| Checkout / crear pedido | `POST /t/{tienda}/checkout` | público | Genera `OnlineOrder`, inicia pago MP |
| Webhook de pago | `POST /webhooks/mercadopago` | (firma) | Confirma/rechaza `payment_status` |
| Bandeja de pedidos | `GET /pedidos` | admin, vendedor | Pedidos entrantes, cambio de estado |
| Gestionar un pedido | `PATCH /pedidos/{order}` | admin, vendedor | Preparar/enviar/entregar/cancelar |

## UI
Sin pantalla en el handoff — a diseñar post-MVP: **tienda pública** (vitrina con las
fotos lookbook y el branding/accent del tenant, nunca placeholders grises) y
**bandeja de pedidos** interna (tabla con estado y badge de pago). WhatsApp visible
(verde) para pedidos por chat. Componentes shadcn: card, table, badge, dialog.

## Multi-tenant & permisos
- Scoping: `OnlineOrder`, `OnlineOrderItem` → `BelongsToTenant`. Catálogo/stock reusados ya son tenant-scoped.
- Roles: `admin` configura la tienda y MercadoPago; `admin`/`vendedor` gestionan pedidos entrantes.
- La tienda pública resuelve el tenant por el slug de la URL (`/t/{tienda}`), no por usuario logueado.

## Fuera de alcance
- Publicar en MercadoLibre / Tienda Nube (sistemas externos) → módulo **Integraciones marketplace**.
- Logística/envíos con transportistas (etiquetas, tracking) → futuro.
- Catálogo o stock "online" separado del POS → **no existe**: es el mismo (decisión de arquitectura).
- Facturación fiscal del pedido → módulo **AFIP**, post-MVP.

## Criterios de aceptación
- [ ] Un producto sin stock **no** aparece disponible en la tienda pública.
- [ ] Una **venta física** baja el stock que ve la tienda **sin** job de sync (misma tabla).
- [ ] Un **pedido online** aprobado descuenta las mismas filas de `Stock` que vería el POS.
- [ ] Un pago aprobado por webhook de MercadoPago pasa el pedido a `pagado`.
- [ ] Cancelar un pedido con stock reservado/descontado **repone** el stock.
- [ ] Un pedido por WhatsApp se crea con `channel = whatsapp` y puede quedar pendiente de pago.
- [ ] Pedidos de otro tenant nunca aparecen en la bandeja (test de aislamiento).

## Notas / preguntas abiertas
- ¿Reserva de stock con expiración (liberar si no se paga en X minutos)?
- ¿La tienda pública vive en subdominio (`bondi.vendi.app`) o en path (`/t/bondi`)?
- ¿Lista de precios online distinta de la del mostrador (precio web)?
- ¿MercadoPago Checkout Pro (redirect) o Bricks (embebido) en v1?
