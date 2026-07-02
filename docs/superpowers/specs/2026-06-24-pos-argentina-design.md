# Design Brief — Sistema de gestión para comercios (POC)

**Fecha:** 2026-06-24
**Estado:** Aprobado para POC visual (Claude Design)
**Nombre provisorio del producto:** Vendi *(reemplazar por la marca real)*

---

## 1. Visión y posicionamiento

Sistema de gestión todo-en-uno para comerciantes (POS + stock + facturación +
clientes + reportes), inspirado en [pulpos.com](https://pulpos.com/) (México),
adaptado a **Argentina**. Objetivo declarado: ser **el primer POS de categoría
tipo Pulpos en Argentina**, nicho hoy vacío.

**Diferenciador central:** una base sólida multi-tenant + **customizaciones a
medida por cliente**, resueltas 90% por configuración y 10% por plugins aislados
(no por forks de código).

## 2. Mercado y modelo fiscal

- País: Argentina (ARCA/AFIP).
- Moneda: ARS. Sensibilidad inflacionaria (re-precios masivos como feature futura).

### Decisión (2026-07): AFIP DIFERIDO en el MVP
- El MVP **NO** integra facturación fiscal. Emite **ticket/remito interno** +
  compartir por WhatsApp. Suficiente para indumentaria a consumidor final.
- Motivo: la integración a WSFE/WSAA (certificados por tenant, CAE, tipos de
  comprobante, homologación) es el mayor costo/complejidad y frena la primera
  venta semanas. El cuello de botella es vender, no facturar.
- **Se necesita a futuro** — no es descartado, es diferido. La arquitectura deja
  el **punto de extensión** listo: la pantalla de Configuración ya expone datos
  fiscales (mono/RI, CUIT, punto de venta), y el emisor de comprobantes se diseña
  detrás de una interfaz para enchufar facturación sin tocar el core.
- Cuando se acople: usar **API tercerizada** (AFIP SDK / TusFacturas / Facturante),
  NO el web service crudo. Se monetiza como **add-on pago** (encaja con el modelo
  base + customización paga).
- Un solo modelo fiscal en el core, plugable para otros países más adelante.

## 3. Arquitectura de customización (6 capas)

El 90% de lo que un comerciante pide como "a medida" es configuración, no código.
Una sola base de código, una sola DB, cada cliente es un *tenant*.

| Capa | Qué personaliza | Esfuerzo / cliente |
|------|-----------------|--------------------|
| 0. Core multi-tenant | El motor compartido | — |
| 1. Feature flags | Prender/apagar módulos | clic |
| 2. Campos personalizados | Atributos extra en entidades | clic |
| 3. Branding / white-label | Logo, colores, dominio | clic |
| 4. Reglas de negocio | Listas de precio, descuentos, impuestos, flujos | config |
| 5. Plugins aislados | Código bespoke encapsulado (el 10%) | dev |

Los puntos de extensión (capa 5) se diseñan desde el día 1, pero el *marketplace*
de plugins NO se construye hasta tener demanda real (evita over-engineering).

Descartados: fork por cliente (muerte por mantenimiento), marketplace completo
desde el día 1 (infra antes de ingresos).

## 4. Catálogo de funcionalidades

### CORE (MVP — todo cliente lo usa)
1. **Productos & catálogo** — variantes (talle/color), kits/combos, categorías,
   código de barras, fotos, multi-precio (costo/venta/mayorista), tags.
2. **Stock** — multi-depósito/sucursal, ajustes, transferencias, alertas de
   mínimo, lotes/vencimientos, valuación.
3. **POS** — venta rápida, lector de barras, múltiples medios de pago, ticket,
   descuentos, devoluciones, apertura/cierre de caja.
4. **Clientes + cuenta corriente** — ficha, fiado, historial, lista de precio.
5. **Proveedores & compras** — órdenes de compra, recepción, costos, cta cte.
6. **Facturación AFIP** — factura electrónica (CAE), NC/ND, remito, mono/RI.
7. **Caja & finanzas** — arqueo, ingresos/egresos, cheques, conciliación.
8. **Reportes & dashboard** — ventas, margen, rotación, por vendedor/sucursal.
9. **Usuarios & permisos** — roles, multi-dispositivo, auditoría.

### EXTENDED (módulos plugables, se prenden por cliente)
E-commerce/catálogo online · integraciones marketplace (MercadoLibre, Tienda
Nube) · producción/manufactura · pedidos/comandas (gastronomía) · promociones &
fidelización · empleados (turnos, comisiones).

### DIFERENCIADORES
- 🧩 Customización a medida (core).
- 💬 **WhatsApp-first** — catálogo, ventas, avisos de cuenta corriente, pedidos.
- 🤖 **IA** — insights automáticos, sugerencia de reposición, carga por foto,
  búsqueda en lenguaje natural.
- 📈 Re-precios masivos por % / lista dólar (supervivencia inflacionaria).

## 5. Alcance del POC (prototipo visual)

Rubro demo: **indumentaria**. 8 pantallas navegables. WhatsApp e IA embebidos
donde tienen sentido (no como módulos sueltos).

1. **Dashboard** — KPIs del día, alertas de stock, 1 insight IA, accesos rápidos.
2. **POS** ⭐ — buscar/escanear, elegir variante talle×color, carrito, descuento,
   medios de pago, cobrar → ticket / enviar por WhatsApp.
3. **Productos (listado)** — grilla con fotos, filtros, stock, precio.
4. **Ficha de producto** — matriz talle×color con stock por celda, multi-precio, fotos.
5. **Stock** — existencias por depósito, alertas de mínimo, ajuste/transferencia,
   sugerencia de reposición IA.
6. **Clientes + cuenta corriente** — listado + ficha con saldo fiado, cobro WhatsApp.
7. **Reportes** — ventas por período, rentabilidad, rotación, por vendedor.
8. **Configuración / Customización** 🧩 — panel multi-tenant: módulos on/off,
   branding, campos personalizados.

## 6. Dirección visual

**Dirección de arte elegida: "Fintech cálido argentino"** (iteración tras un
primer POC que salió correcto pero genérico — look default de IA: verde + Inter +
cards). La firma vive en estructura/grilla/tipografía (queda fija, sirve al
white-label); el color es un token que cada cliente cambia.

- Lienzo **crema cálido** (no blanco puro). Inspiración: fintech AR (Ualá,
  Naranja X, Belo), Monzo, Mercado Pago — cercano pero premium.
- Bitono: **violeta** primario + **ámbar/naranja quemado** de acento cálido.
  Gradiente violeta→lila en una card "hero" por pantalla.
- Tipografía con carácter (grotesque tipo Clash Display / Cabinet Grotesk /
  General Sans), NO Inter por defecto. Números grandes, redondeados, tabulares.
- Formas redondeadas (radio generoso), botones/chips pill, sombras cálidas suaves.
- Iconografía redondeada, avatares con fondos de color cálido, textura sutil.
- **Dark mode cálido protagonista** (grafito cálido, no inversión fría).
- **WhatsApp visible** (verde WhatsApp) en POS, Clientes y compartir catálogo.
- Fotos de prendas tipo lookbook — **nunca placeholders gris-rayado.**
- Sidebar de navegación fija + 8 vistas. Desktop-first; POS táctil.
- Copy español rioplatense cálido, montos en ARS.

## 7. Fuera de alcance del POC

Backend real, integración AFIP real, autenticación, persistencia, el marketplace
de plugins, módulos EXTENDED completos. El POC es prototipo visual navegable.
