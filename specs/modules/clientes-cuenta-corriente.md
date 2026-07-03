# Módulo: Clientes / Cuenta corriente

- **Fase:** 4
- **Estado:** 📝 en definición
- **Pantalla(s) del design:** Clientes + Cuenta corriente (ver `design_handoff_vendi_pos/README.md` §6)
- **Depende de:** Auth & Tenancy (Fase 0). Referencia a Ventas/POS (venta a fiado) y a WhatsApp (recordar pago).

## Propósito
Registrar los clientes del comercio (minoristas y mayoristas) y llevarles la **cuenta
corriente** (fiado): saldo, movimientos debe/haber y su historial de compras. Le
permite al comerciante vender a cuenta y recordar cobros por WhatsApp.

## Entidades y datos

- **Customer** — `tenant_id` · `name` · `type` (minorista/mayorista) · `doc_type` (DNI/CUIT) ·
  `doc_number` · `phone?` · `email?` · `price_list_id?` (lista de precio asignada) ·
  `notes?` · `active` (bool) · timestamps
- **AccountMovement** *(cuenta corriente / fiado)* — `tenant_id` · `customer_id` · `date` ·
  `type` (venta_fiado/pago/ajuste) · `detail` · `amount` · `sale_id?` (venta que lo originó) ·
  timestamps

> El **saldo** del cliente se calcula **server-side** = `Σ debe − Σ haber` (no se
> almacena; precedente = margen de Productos). Las columnas Debe/Haber/Saldo de la
> tabla del §6 son proyecciones de lectura sobre los movimientos ordenados por fecha.
> Montos calculados server-side; formato `es-AR` (`$ 24.500`) solo en display. Todos
> los modelos usan `BelongsToTenant`.

## Reglas de negocio
1. `type = mayorista` toma por defecto la lista de precio mayorista; `minorista`, la de venta.
2. `doc_type` = **DNI** para minorista, **CUIT** para mayorista (validación básica de formato).
3. Saldo positivo = el cliente **debe** (fiado); `0` o negativo = al día / a favor.
4. Una **venta a fiado** en el POS genera un `AccountMovement` `type=venta_fiado` (`amount`
   al debe) enganchado por el evento de dominio `SaleCompleted`, con `sale_id`. El cobro a
   cuenta corriente como medio de pago lo define **Ventas/POS**, no este módulo.
5. Un **pago** genera un `AccountMovement` `type=pago` (`amount` al haber) y baja el saldo.
6. Un **ajuste** manual (`type=ajuste`) corrige el saldo; solo `admin`.
7. **"Recordar pago"** (WhatsApp) solo se habilita si `saldo > 0`; el envío efectivo lo
   resuelve el módulo **WhatsApp** (deep link `wa.me`) — acá solo se dispara.
8. El **historial de compras** lista las ventas del cliente (lectura del módulo Ventas).

## Acciones / endpoints

| Acción | Ruta / método | Rol | Descripción |
|--------|---------------|-----|-------------|
| Listar | `GET /clientes` | admin, vendedor | Lista lateral con saldo (ámbar si fiado) |
| Ver detalle | `GET /clientes/{customer}` | admin, vendedor | Datos + movimientos + historial de compras |
| Crear | `GET/POST /clientes/nuevo` | admin, vendedor | Alta (tipo, doc, teléfono, lista) |
| Editar | `PUT /clientes/{customer}` | admin, vendedor | Edición de datos |
| Registrar pago | `POST /clientes/{customer}/pagos` | admin, vendedor | Movimiento al haber |
| Ajuste de saldo | `POST /clientes/{customer}/ajustes` | admin | Movimiento manual |
| Recordar pago | `POST /clientes/{customer}/recordar-pago` | admin, vendedor | Delega en WhatsApp (solo si saldo > 0) |

## UI
Handoff §6: lista de 320px ("+ Nuevo cliente" dashed, filas avatar-iniciales + nombre +
tipo + saldo ámbar/"Al día") + detalle (card de encabezado con saldo, teléfono, doc y
botón verde **"Recordar pago"** solo con saldo > 0; tabla **Movimientos de cuenta
corriente** con Debe[danger]/Haber[accent]/Saldo; lista **Historial de compras**).
Componentes shadcn: card, table, badge, button, dialog, input. Números en `font-display`
tabular. Estado vacío: sin clientes → CTA de alta; sin movimientos → "Sin movimientos".
Datos de ejemplo: Lucas Fernández debe `$ 24.500`, La Rambla (mayorista) `$ 112.000`,
Tienda Norte `$ 48.300`; Martina Gómez y Sofía Ruiz al día.

## Multi-tenant & permisos
- Scoping: `Customer`, `AccountMovement` → `BelongsToTenant`.
- Roles: `admin` y `vendedor` gestionan clientes y registran pagos; **ajustes de saldo solo `admin`**.

## Fuera de alcance
- El cobro/medio de pago "cuenta corriente" en el checkout → módulo **Ventas/POS**.
- El envío efectivo del mensaje de WhatsApp → módulo **WhatsApp**.
- La definición de las listas de precio → módulos **Productos** / **Configuración**.
- Facturación / datos fiscales del cliente → **AFIP** (diferido).
- Límite de crédito, intereses por mora, imputación de pagos a ventas puntuales.

## Criterios de aceptación
- [ ] Un `admin`/`vendedor` puede crear un cliente **minorista con DNI** y uno **mayorista con CUIT**.
- [ ] Una venta a fiado genera un `AccountMovement` `venta_fiado` y sube el saldo del cliente.
- [ ] Registrar un pago genera un movimiento `pago` al haber y baja el saldo.
- [ ] El saldo se calcula server-side como `Σ debe − Σ haber` (no almacenado).
- [ ] "Recordar pago" está disponible solo si `saldo > 0` (oculto/deshabilitado si está al día).
- [ ] Un cliente `mayorista` toma la lista de precio mayorista por defecto.
- [ ] Un cliente/movimiento de otro tenant nunca aparece en el listado (test de aislamiento).

## Notas / preguntas abiertas
- ¿Listas de precio con nombre propio o alcanza con minorista→venta / mayorista→mayorista en v1?
- El modal de pago del POS (§checkout) no lista "cuenta corriente" como medio — coordinar con Ventas para que la venta a fiado lo contemple.
- ¿Validar CUIT con dígito verificador o solo formato en MVP?
- ¿Tope de fiado (límite de crédito) por cliente? (MVP: no)
- ¿Los pagos se imputan al saldo global o a ventas específicas? (MVP: saldo global)
