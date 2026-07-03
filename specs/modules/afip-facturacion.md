# Módulo: AFIP / Facturación electrónica

- **Fase:** diferido
- **Estado:** ⬜ diferido (post-MVP)
- **Pantalla(s) del design:** Configuración → **Datos fiscales** (el hueco visual ya
  existe: condición IVA, CUIT, punto de venta). Falta diseñar: listado de
  comprobantes emitidos y detalle de comprobante.
- **Depende de:** POS / Ventas · Configuración (datos fiscales del tenant) · Clientes

> **Por qué diferido (post-MVP):** integrar facturación fiscal (WSAA/WSFE,
> certificado por tenant, CAE, tipos de comprobante, homologación) es el mayor
> costo/complejidad del producto y **frena la primera venta semanas** — el cuello
> de botella es vender, no facturar (ver design §2). En el MVP el POS emite
> **ticket/remito interno** y suficiente para indumentaria a consumidor final.
> **Es el diferido más importante: se necesita a futuro** y se monetiza como
> **add-on pago**. La arquitectura deja el punto de extensión listo.

## Propósito
Emitir comprobantes fiscales legales (factura electrónica con **CAE** ante
ARCA/AFIP), notas de crédito/débito, remito y factura global, según la condición
fiscal del comercio. Reemplaza el ticket interno del MVP por un comprobante
válido cuando el comercio lo necesita.

## Arquitectura (clave — el punto de extensión ya existe)
El POS **nunca** llama a AFIP directo: emite a través de la interface
`FiscalDocumentIssuer`.

- **`NullIssuer`** (default en MVP): "emite" el ticket/remito interno, sin CAE.
- **`AfipIssuer`** (este módulo, post-MVP): implementa la misma interface contra
  una **API tercerizada** (AFIP SDK / TusFacturas / Facturante), **NO** el web
  service crudo WSAA/WSFE. Enchufar `AfipIssuer` no toca el código del POS.

El **certificado y el punto de venta son por tenant**. Al encender el add-on se
sube el certificado y se selecciona el/los punto(s) de venta del comercio.

## Entidades y datos

- **FiscalDocument** — `tenant_id` · `sale_id?` (venta que factura, nullable p/ globales) ·
  `type` (A/B/C · NC-A/B/C · ND-A/B/C · remito · factura_global) · `point_of_sale` ·
  `number` (correlativo por punto de venta+tipo) · `cae?` · `cae_expires_at?` ·
  `status` (pendiente/autorizado/rechazado/anulado) · `net_amount` · `vat_amount` ·
  `total` · `related_document_id?` (comprobante origen de una NC/ND) ·
  `receiver_doc_type` · `receiver_doc_number` · `receiver_vat_condition` ·
  `provider_payload: json` · `provider_response: json` · `issued_at?`
  — **la tabla `fiscal_documents` ya existe inactiva** (plan técnico §5).

> Los datos fiscales del **emisor** (condición IVA mono/RI, CUIT, punto de venta,
> certificado) **no viven acá**: se cargan en **Configuración** y este módulo los
> consume. Plata calculada server-side, formato `es-AR` solo en display.

## Reglas de negocio
1. Emitir es **idempotente por venta**: una venta no genera dos comprobantes autorizados.
2. **Tipo de comprobante según la condición del emisor** (regla marco, precisar en Notas):
   monotributo → **C**; Responsable Inscripto → **A** (a otro RI, con IVA discriminado)
   o **B** (a consumidor final / no inscripto).
3. **IVA** solo se discrimina cuando el emisor es RI (comprobante A); en C no hay IVA discriminado.
4. Un comprobante **autorizado (con CAE) es inmutable**: no se edita ni borra; se corrige con **NC/ND**.
5. Una **NC/ND** referencia siempre un comprobante origen (`related_document_id`).
6. Numeración **correlativa por punto de venta + tipo**; el número lo asigna AFIP/CAE, no el usuario.
7. Sin CAE el comprobante queda `pendiente`/`rechazado` y **no** es válido fiscalmente.
8. La emisión es **asíncrona** (job en cola): la venta se cierra en el POS aunque AFIP demore.

## Acciones / endpoints

| Acción | Ruta / método | Rol | Descripción |
|--------|---------------|-----|-------------|
| Facturar una venta | `POST /ventas/{sale}/facturar` | admin, vendedor | Encola emisión vía `FiscalDocumentIssuer` |
| Listar comprobantes | `GET /facturacion` | admin | Grilla con tipo, número, CAE, estado |
| Ver comprobante | `GET /facturacion/{document}` | admin, vendedor | Detalle + PDF descargable |
| Nota de crédito/débito | `POST /facturacion/{document}/nota` | admin | NC/ND sobre comprobante origen |
| Factura global | `POST /facturacion/global` | admin | Comprobante consolidado de un período |
| Configurar datos fiscales / certificado | `/configuracion/fiscal` | admin | (vive en Configuración; ver ese módulo) |

## UI
Reutiliza el hueco de **Datos fiscales** en Configuración (condición IVA, CUIT,
punto de venta) para la carga del certificado y puntos de venta. A diseñar:
**listado de comprobantes** (tabla con badge de estado CAE) y **detalle**
(PDF, importes, IVA discriminado). Componentes shadcn: table, card, badge, dialog.
Números en `font-display` tabular. Estado vacío = "Todavía no emitiste comprobantes".

## Multi-tenant & permisos
- Scoping: `FiscalDocument` → `BelongsToTenant`. Certificado y punto de venta **por tenant**.
- Roles: `admin` configura fiscal y emite NC/ND; `vendedor` puede facturar una venta desde el POS.

## Fuera de alcance
- Los datos fiscales del emisor y la carga del certificado → módulo **Configuración**.
- El WSAA/WSFE crudo → se usa **API tercerizada**, no se implementa el web service.
- Multi-país / otros modelos fiscales → un solo modelo fiscal en el core, plugable a futuro.
- Libro IVA / exportación contable → módulo aparte, post-MVP.

## Criterios de aceptación
- [ ] Con el add-on **apagado**, el POS emite ticket interno vía `NullIssuer` (sin CAE) y la venta cierra igual.
- [ ] Con el add-on **encendido**, facturar una venta genera un `FiscalDocument` **autorizado con CAE**.
- [ ] Un emisor **monotributo** emite **C**; un **RI** emite **A** (a RI) o **B** (a consumidor final).
- [ ] Un comprobante autorizado no puede editarse ni borrarse; solo se corrige con NC/ND.
- [ ] Una NC/ND siempre referencia un comprobante origen (`related_document_id`).
- [ ] Enchufar `AfipIssuer` no requiere cambios en el código del POS (misma interface).
- [ ] Un comprobante de otro tenant nunca aparece en el listado (test de aislamiento).

## Notas / preguntas abiertas
- ¿Qué proveedor tercerizado (AFIP SDK / TusFacturas / Facturante)? Define costo del add-on y la forma exacta del payload.
- Mapeo fino tipo↔receptor (exactamente quién recibe A vs B, casos exento/no responsable) — precisar con contador antes de codear.
- ¿Homologación primero (ambiente de testing de AFIP) y luego producción? Flujo de alta del certificado por tenant.
- ¿Factura global por qué período (diaria/mensual) y para qué rubros aplica?
- Precio y empaquetado del add-on (por comprobante / mensual fijo).
