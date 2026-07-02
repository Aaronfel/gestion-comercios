# Módulo: Productos / Catálogo

- **Fase:** 1
- **Estado:** 📝 en definición (ejemplo de referencia — iterar antes de construir)
- **Pantalla(s) del design:** Productos (listado) · Ficha de producto (ver `design_handoff_vendi_pos/README.md` §3 y §4)
- **Depende de:** Auth & Tenancy (Fase 0)

## Propósito
Gestionar el catálogo del comercio: productos con variantes talle×color, precios
(costo/venta/mayorista), categorías, código de barras y campos personalizados.
Es la base de datos que consumen POS, Stock y Reportes.

## Entidades y datos

- **Category** — `tenant_id` · `name` · `slug`
- **Product** — `tenant_id` · `category_id` · `name` · `code` (SKU) · `status` (activo/inactivo) ·
  `cost_price` · `sale_price` · `wholesale_price` · `barcode?` · `supplier?` · `tags: json` ·
  `custom_fields: json` · `photo_path?`
- **ProductVariant** — `tenant_id` · `product_id` · `size` (talle) · `color` · `barcode?`
  · (el stock por variante/sucursal vive en el módulo Stock, no acá)

> Precios en centavos/enteros, cálculo de margen **server-side**. Formato `es-AR`
> (`$ 38.900`) solo en display. Todos los modelos usan `BelongsToTenant`.

## Reglas de negocio
1. `code` (SKU) único por tenant.
2. Margen sobre venta = `(sale_price − cost_price) / sale_price` — calculado, no almacenado.
3. Un producto puede no tener variantes (venta directa) o tener matriz talle×color.
4. `status = inactivo` lo oculta del POS pero lo conserva en historial.
5. Campos personalizados: definidos a nivel tenant (ver módulo Configuración), guardados en `custom_fields` (JSONB).
6. Foto opcional; fallback = bloque de color cálido + glifo de prenda (nunca placeholder gris).

## Acciones / endpoints

| Acción | Ruta / método | Rol | Descripción |
|--------|---------------|-----|-------------|
| Listar | `GET /productos` | admin, vendedor | Grilla/tabla, filtros por categoría, búsqueda |
| Ver ficha | `GET /productos/{product}` | admin, vendedor | Detalle + matriz de variantes |
| Crear | `GET/POST /productos/nuevo` | admin | Alta con variantes y precios |
| Editar | `PUT /productos/{product}` | admin | Incluye edición de matriz de stock |
| Activar/desactivar | `PATCH /productos/{product}/estado` | admin | Cambia `status` |

## UI
Handoff §3 (listado: toggle grilla/tabla, pills de categoría, badge "Stock bajo",
"+ Nuevo producto" violeta) y §4 (ficha: galería, matriz talle×color editable con
color de celda por estado de stock, card de Precios con margen, card de Campos
personalizados con badge "Vendi"). Componentes shadcn: table, card, badge, tabs,
dialog. Números en `font-display` tabular.

## Multi-tenant & permisos
- Scoping: `Category`, `Product`, `ProductVariant` → `BelongsToTenant`.
- Roles: `admin` crea/edita; `vendedor` solo lectura.

## Fuera de alcance
- Existencias / ajustes / transferencias → módulo **Stock**.
- Definición de qué campos personalizados existen → módulo **Configuración**.
- Fotos reales de lookbook (assets) → se cargan luego; en MVP el fallback de color.

## Criterios de aceptación
- [ ] Un admin puede crear un producto con matriz talle×color y 3 precios.
- [ ] El SKU duplicado dentro del mismo tenant es rechazado.
- [ ] El margen se muestra calculado correctamente en la ficha.
- [ ] Un producto de otro tenant nunca aparece en el listado (test de aislamiento).
- [ ] `vendedor` no puede crear ni editar productos (403).
- [ ] La ficha muestra los campos personalizados definidos por el tenant.

## Notas / preguntas abiertas
- ¿Fotos por producto o por variante (color) en v1? (el design sugiere por producto)
- ¿Códigos de barra por variante obligatorios para el escáner del POS?
