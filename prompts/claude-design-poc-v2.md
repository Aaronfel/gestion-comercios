# Prompt v2 para Claude Design — Vendi (redesign con firma propia)

> Reemplazo del v1. El v1 salió correcto pero **genérico** (look default: verde +
> Inter + cards). Este prompt fuerza una dirección de arte con identidad:
> **"Fintech cálido argentino"**. Pegá todo el bloque en Claude Design.

---

Rediseñá **Vendi**, un sistema de gestión / punto de venta para comercios de
Argentina (rubro demo: indumentaria streetwear "Bondi Indumentaria"). Ya existe
una versión, pero quedó **genérica** — el típico dashboard SaaS de IA: acento
verde, Inter, cards redondeadas, todo blanco. **Quiero lo opuesto: un diseño con
firma propia, memorable, que no parezca un template.** Mantené la estructura de
producto (8 pantallas, sidebar, los mismos datos) pero dale una **dirección de
arte fuerte y coherente**.

## DIRECCIÓN DE ARTE: "Fintech cálido argentino"

Referencias de mood: Ualá, Naranja X, Belo, Mercado Pago, Monzo. Cálido,
cercano, humano — pero premium y prolijo. NO corporativo frío, NO el SaaS gris
de siempre.

### Color (proponé los hex exactos, estos son guía)
- **Lienzo crema cálido**, NO blanco puro: ~`#F8F3EC`. Es el sello #1 — apenas
  cambiás blanco por crema, deja de parecer template.
- **Superficies/cards**: blanco cálido sobre el crema, con sombra suave de tinte
  cálido (no gris frío).
- **Primario de marca: violeta vibrante** ~`#6C4DF2`.
- **Acento cálido secundario: ámbar / naranja quemado** ~`#F2884B` — para
  contraste y calidez. El bitono violeta+ámbar es la firma cromática.
- **Card "hero" con gradiente** violeta→lila suave (una por pantalla: el KPI
  principal o el insight de IA).
- Semánticos cálidos: éxito verde, alerta ámbar, crítico rojo coral `#E5484D`.
- **Verde WhatsApp** `#25D366` reservado para los botones de WhatsApp.

### Tipografía (con carácter, NO Inter por defecto)
- Títulos y números grandes: una grotesque con personalidad — **Clash Display**,
  **Cabinet Grotesk** o **General Sans**. Números **grandes, redondeados,
  tabulares** (los montos tienen que lucir).
- Cuerpo/UI: **Switzer** o **General Sans** (humanista, cálida).

### Forma y profundidad
- Radio generoso: cards 20–24px, **botones y chips tipo pill**.
- **Sombras cálidas multicapa**, suaves — las cards flotan sobre el crema.
- Iconografía **redondeada** (estilo Phosphor / Iconoir), nunca filosa.
- Avatares e íconos con **fondos de color cálido** (violeta/ámbar tenue).
- Toques de personalidad: textura sutil de grano o formas blob suaves detrás de
  las zonas hero; microcopy cálido rioplatense.

### Dark mode (protagonista, cuidado — no inversión)
- **Grafito cálido** ~`#1B1714` de lienzo, superficies `#241F1B`. Violeta
  luminoso + ámbar como acentos. Que el dark se sienta diseñado, no apagado.
- Toggle claro/oscuro visible en el sidebar.

## REGLAS QUE ROMPEN LO GENÉRICO (obligatorias)
1. **Crema, no blanco.** El fondo nunca es `#FFFFFF` puro.
2. **Imágenes reales de prendas** tipo lookbook en productos/POS. **Prohibido el
   placeholder gris-rayado.** Si no hay foto, usá un bloque de color cálido con la
   prenda ilustrada de forma simple.
3. **WhatsApp visible**, con su verde, en: POS (tras cobrar → "Enviar ticket por
   WhatsApp"), Clientes ("Enviar recordatorio de pago"), y Configuración/catálogo
   ("Compartir catálogo por WhatsApp").
4. **Bitono violeta + ámbar** en toda la UI, no monocromía.
5. **Tipografía display con carácter**, no Inter.
6. Un **momento hero con gradiente** por pantalla.

## PANTALLAS (8, mismas que ya existen — re-skin con la dirección de arte)
1. **Inicio / Dashboard** — KPIs (Ventas de hoy, Ticket promedio, Estado de caja,
   Unidades) con números enormes; gráfico de 7 días; alertas de stock con variante
   (Negro · Talle L); **card hero gradiente con Insight de IA**; accesos rápidos.
2. **Vender / POS** ⭐ — buscador con escáner, chips pill de categorías, grilla con
   **foto real de prenda**, selector talle×color, carrito, descuento, selector de
   cliente, total grande, **"Cobrar"** → modal de medios de pago (efectivo, débito,
   crédito, transferencia, QR/MercadoPago) → **"Enviar ticket por WhatsApp"**.
3. **Productos** — grilla/tabla con fotos reales, categorías pill, stock, precio,
   estados (Activo / Stock bajo), "+ Nuevo producto", toggle grilla/tabla.
4. **Ficha de producto** — galería, **matriz talle×color con stock por celda**,
   multi-precio (costo/venta/mayorista + margen), campos personalizados (Temporada,
   Material), código de barras, proveedor, tags.
5. **Stock** — existencias por sucursal (Local Centro / Depósito), mínimo, estado,
   ajustar/transferir, **card hero de sugerencia de reposición por IA**.
6. **Clientes + Cuenta corriente** — listado con saldo de fiado; ficha con
   movimientos (debe/haber/saldo) y **"Enviar recordatorio de pago por WhatsApp"**.
7. **Reportes** — ventas por período, más vendidos/rentables, rotación, por
   vendedor y sucursal, rango de fechas, margen y ganancia.
8. **Configuración / Customización** 🧩 — el diferenciador: **módulos on/off**
   (switches: Facturación AFIP, E-commerce, Producción, Promociones, Empleados),
   **marca/white-label** (logo, color de acento, nombre), **campos personalizados**,
   **datos fiscales** (mono/RI, CUIT, punto de venta), **"Compartir catálogo por
   WhatsApp"**.

## INTERACCIÓN
- 8 vistas navegables desde el sidebar. Toggle claro/oscuro funcional en todo.
- Flujo estrella pulido: POS → producto → variante → cobrar → medio de pago →
  WhatsApp.
- Datos realistas (no vacío): montos en ARS `$ 38.900`, badges de stock bajo,
  saldos de fiado, insight de IA visible. Copy rioplatense cálido.

## NO INCLUIR
Backend, login, impresoras, módulos extendidos completos. Es prototipo visual.
