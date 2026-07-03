# Módulo: Integraciones marketplace

- **Fase:** diferido
- **Estado:** ⬜ diferido (post-MVP)
- **Pantalla(s) del design:** — (sin pantalla en el handoff; a diseñar post-MVP:
  panel de conexiones + mapeo de publicaciones)
- **Depende de:** Productos / Catálogo · Stock / Inventario

> **Por qué diferido (post-MVP):** integrar sistemas **externos** (MercadoLibre,
> Tienda Nube) por sus APIs, con OAuth, rate limits y modelos de datos distintos
> por marketplace, es complejo y **frágil** — no bloquea la primera venta. Se
> construye cuando el comercio ya vende en esos canales y quiere dejar de cargar
> stock a mano en dos lados.

## Propósito
Publicar y **sincronizar** el catálogo y el stock del comercio con marketplaces
externos (**MercadoLibre** y **Tienda Nube**), e **importar los pedidos** de esos
canales al sistema. A diferencia del E-commerce propio, acá hay **sistemas de
terceros**: la sincronización es **real y bidireccional**, con conflictos que hay
que resolver.

## Arquitectura (clave — sync bidireccional con sistemas externos)
Cada marketplace es un sistema externo con su **propio modelo de datos** y su
**API con rate limits**. No comparten tabla con nuestro `Stock` (a diferencia del
E-commerce propio, que sí): hay que **mapear** nuestro `Product`/`ProductVariant`
↔ el listing externo y **empujar/traer** cambios.

- **Stock bidireccional:** una venta local baja el stock del listing externo, y una
  venta en el marketplace baja nuestro `Stock` (al importar el pedido).
- **Rate limits:** las actualizaciones se hacen en **jobs en cola** con backoff;
  no se llama a la API por cada cambio de forma sincrónica.
- **Conflictos:** si ambos lados cambiaron entre syncs, gana una política definida
  (ej. el stock **local es la fuente de verdad**) y se registra la divergencia.

## Entidades y datos

- **MarketplaceConnection** — `tenant_id` · `provider` (mercadolibre/tiendanube) ·
  `status` (conectado/expirado/error) · `credentials: json` (tokens OAuth, refresh) ·
  `external_account_id` · `settings: json` (política de sync, precio, etc.) · `last_synced_at?`
- **MarketplaceListing** — `tenant_id` · `marketplace_connection_id` · `product_id` ·
  `product_variant_id?` · `external_listing_id` · `status`
  (publicado/pausado/no_publicado/error) · `sync_status` (ok/pendiente/conflicto) ·
  `external_data: json` (payload crudo del marketplace) · `last_pushed_at?` · `last_pulled_at?`
  — **mapea `Product`/`ProductVariant` ↔ listing externo**.

> Todos los modelos de dominio usan `BelongsToTenant`. Credenciales OAuth cifradas
> en reposo. La cantidad de stock es la del módulo **Stock** (fuente de verdad local).

## Reglas de negocio
1. Cada `MarketplaceConnection` es **por tenant y por proveedor**; los tokens se **refrescan** solos.
2. Publicar/actualizar un listing crea/actualiza el `MarketplaceListing` y **empuja** vía job en cola.
3. **Stock local = fuente de verdad** (por defecto): un cambio de `Stock` empuja la cantidad al listing.
4. Al **importar un pedido** del marketplace se crea la venta/pedido local y **descuenta stock** una sola vez (idempotente por `external_order_id`).
5. Las llamadas respetan **rate limits**: cola + backoff + reintentos; nunca sincrónico por cada cambio.
6. Si hay **conflicto** (ambos lados cambiaron), se aplica la política y el listing queda `sync_status = conflicto` para revisión.
7. Cada marketplace tiene su **mapeo de campos propio** (categorías, atributos): se traduce por proveedor, no se asume uno común.

## Acciones / endpoints

| Acción | Ruta / método | Rol | Descripción |
|--------|---------------|-----|-------------|
| Conectar marketplace (OAuth) | `GET /integraciones/{provider}/conectar` | admin | Flujo OAuth, guarda `MarketplaceConnection` |
| Panel de conexiones | `GET /integraciones` | admin | Estado, último sync, errores por proveedor |
| Publicar producto | `POST /integraciones/{connection}/publicar` | admin | Crea `MarketplaceListing` + encola push |
| Sincronizar ahora | `POST /integraciones/{connection}/sync` | admin | Fuerza push/pull manual |
| Webhook de pedidos | `POST /webhooks/{provider}` | (firma) | Importa pedidos del marketplace |
| Resolver conflicto | `PATCH /integraciones/listings/{listing}` | admin | Elige fuente de verdad de un listing en conflicto |

## UI
Sin pantalla en el handoff — a diseñar post-MVP: **panel de conexiones** (una card por
proveedor con estado, último sync, contador de errores) y **tabla de listings**
(mapeo producto ↔ publicación, con badge de `sync_status` y resolución de conflicto).
Componentes shadcn: card, table, badge, dialog, alert. Estado vacío = "Conectá tu primer canal".

## Multi-tenant & permisos
- Scoping: `MarketplaceConnection`, `MarketplaceListing` → `BelongsToTenant`.
- Roles: solo `admin` (conectar cuentas y publicar es configuración sensible). `vendedor` no accede.

## Fuera de alcance
- La **tienda online propia** (catálogo/checkout del comercio) → módulo **E-commerce**.
- Facturación fiscal de las ventas importadas → módulo **AFIP**, post-MVP.
- Cálculo de comisiones/rentabilidad por canal → módulo **Reportes**, a extender.
- Marketplaces fuera de MercadoLibre / Tienda Nube → se agregan por proveedor a demanda.

## Criterios de aceptación
- [ ] Un `admin` conecta MercadoLibre/Tienda Nube por OAuth y queda `MarketplaceConnection` con tokens.
- [ ] Publicar un producto crea un `MarketplaceListing` que mapea `Product` ↔ `external_listing_id`.
- [ ] Un cambio de `Stock` local **empuja** la nueva cantidad al listing (fuente de verdad local).
- [ ] Importar un pedido del marketplace crea la venta local y **descuenta stock una sola vez** (idempotente).
- [ ] Las actualizaciones se procesan en **cola** respetando rate limits (con reintento/backoff).
- [ ] Un listing con cambios en ambos lados queda en `sync_status = conflicto` para revisión.
- [ ] Conexiones/listings de otro tenant nunca aparecen (test de aislamiento).

## Notas / preguntas abiertas
- ¿Fuente de verdad de stock siempre local, o configurable por conexión?
- ¿Con qué frecuencia se hace el pull (webhooks + polling de respaldo)?
- ¿Cómo se resuelve el mapeo de **categorías/atributos** obligatorios de cada marketplace (ej. ficha técnica de ML)?
- ¿Publicación por producto o por variante en cada marketplace (modelos distintos)?
- ¿Qué se hace si el token OAuth expira y no se puede refrescar (aviso al admin)?
