# Módulo: WhatsApp (touchpoints)

- **Fase:** 3–6 (cross-cutting: se engancha a POS, Clientes y Configuración)
- **Estado:** 📝 en definición
- **Pantalla(s) del design:** POS (recibo) · Clientes (recordar pago) · Configuración (compartir catálogo) (ver `design_handoff_vendi_pos/README.md` §2, §6, §8 y "WhatsApp touchpoints")
- **Depende de:** POS/Ventas (Fase 3), Clientes (Fase 4), Configuración (Fase 6)

## Propósito
Que el comerciante hable con su cliente por el canal que ya usa, sin infra ni costos.
En MVP **no** es un módulo con entidades: es un **servicio + botones**. Cada acción
arma un mensaje pre-cargado y abre un **deep link `wa.me`** con el número y el texto.
Cero backend de mensajería.

## Entidades y datos
**No persiste — sin entidades ni tablas.** Es un servicio *stateless*. El número de
teléfono lo aporta el contexto (cliente en Clientes, tenant en Configuración) y el
texto se compone en el momento. Post-MVP con WhatsApp Business API podría requerir
persistir plantillas/estados (diferido, ver Fuera de alcance).

## Contrato del servicio
`App\Services\WhatsAppLinkBuilder` (o similar) — único punto que arma los links, para
que POS/Clientes/Config lo consuman sin duplicar lógica. Testeable en aislamiento:

- `forTicket(Sale $sale): string` — mensaje con detalle del ticket (items, total, comercio).
- `forPaymentReminder(Customer $customer): string` — recordatorio de saldo de cuenta corriente.
- `forCatalog(Tenant $tenant): string` — invitación a ver el catálogo (link/PDF del comercio).
- Interno: `build(string $phone, string $message): string` → `https://wa.me/<phone>?text=<urlencoded>`.

## Reglas de negocio
1. El link tiene forma `https://wa.me/<phone>?text=<message>` con el `text`
   **URL-encodeado**; se abre en pestaña/app nueva (`target=_blank`).
2. **Normalización del teléfono**: se envía en formato internacional sin `+`, espacios
   ni guiones (Argentina: prefijo país `54`). El número guardado del cliente puede venir
   en cualquier formato → el builder lo normaliza. (La convención exacta de dígitos AR
   —cuándo va el `9` de celular— queda como pregunta abierta.)
3. "Recordar pago" (Clientes) **solo se ofrece si el saldo de cuenta corriente > 0**;
   con saldo 0 o a favor, el botón no aparece.
4. "Enviar por WhatsApp" (POS) se ofrece en el recibo, **después de confirmar el cobro**.
5. "Compartir catálogo" (Configuración) usa el número/identidad del tenant.
6. El texto es **español rioplatense**, con nombre del comercio y montos en formato
   `es-AR` (`$ 38.900`); los montos se calculan **server-side**.
7. Cada acción dispara un **toast de confirmación** (el link se abrió); Vendi no
   confirma la entrega del mensaje (es un deep link, no un envío).

## Acciones / endpoints
No requiere endpoints propios: el builder corre server-side (o expone el link vía la
prop Inertia de la pantalla que lo usa) y el front abre `wa.me`. Puntos de uso:

| Touchpoint | Pantalla | Método | Rol | Mensaje |
|------------|----------|--------|-----|---------|
| Enviar ticket | POS (recibo) | `forTicket(sale)` | admin, vendedor | Detalle de la venta + total |
| Recordar pago | Clientes (detalle) | `forPaymentReminder(customer)` | admin, vendedor | Saldo adeudado (solo si > 0) |
| Compartir catálogo | Configuración (Marca) | `forCatalog(tenant)` | admin | Invitación al catálogo del comercio |

## UI
Botones verdes de WhatsApp, color vía el token `whatsapp` de `app.css` (nunca hex
`#25D366` hardcodeado en el componente). Ícono de WhatsApp. En POS: dentro del modal
de recibo ("Enviar por WhatsApp"). En Clientes: "Recordar pago" en el header del
detalle (condicionado a saldo). En Config: "Compartir catálogo" en la card de Marca.
Cada click abre `wa.me` y muestra un toast (pill inferior-centro, ~2.6s).

## Multi-tenant & permisos
- Scoping: no tiene modelos propios. Opera sobre `Sale`/`Customer`/`Tenant` ya
  scopeados por `BelongsToTenant`; el builder nunca cruza tenants (recibe entidades ya scopeadas).
- Roles: ticket y recordatorio → `admin` y `vendedor`. Compartir catálogo → `admin` (vive en Config).

## Fuera de alcance
- **WhatsApp Business API** (envío real, plantillas aprobadas, sesión 24h) — diferido.
- **Pedidos entrantes** por WhatsApp / bot conversacional — diferido.
- Confirmación de entrega/lectura del mensaje — imposible con deep links, no aplica.
- Envío automático programado (recordatorios en lote) — futuro (requiere API).

## Criterios de aceptación
- [ ] `WhatsAppLinkBuilder::build()` devuelve `https://wa.me/<phone>?text=...` con el texto URL-encodeado.
- [ ] Un teléfono con espacios/guiones/`+` se normaliza a solo dígitos con prefijo país.
- [ ] `forTicket()` incluye total y comercio, con montos en formato `es-AR`.
- [ ] "Recordar pago" **no** se ofrece cuando el saldo del cliente es 0 (o a favor).
- [ ] "Recordar pago" arma el link con el saldo adeudado cuando el saldo es > 0.
- [ ] El texto generado está en español rioplatense (no placeholders en inglés).
- [ ] Ningún touchpoint expone datos de otro tenant (las entidades llegan ya scopeadas).

## Notas / preguntas abiertas
- Convención exacta del número AR en `wa.me`: ¿siempre `54` + `9` + área + número? ¿Guardamos el teléfono ya normalizado en el alta del cliente?
- "Compartir catálogo": ¿link a un catálogo web, PDF generado, o texto con lista de productos? (define qué arma `forCatalog`).
- ¿El ticket incluye link a un comprobante web o solo texto plano del detalle?
