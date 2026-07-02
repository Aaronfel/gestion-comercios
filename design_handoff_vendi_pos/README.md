# Handoff: Vendi — POS & retail management (Argentina)

## Overview
Vendi is an all-in-one point-of-sale and retail management system for Argentine shops (POS, stock, customers, reports). This package is the **hi-fi visual prototype** for the product, art-directed as "warm Argentine fintech" (mood refs: Ualá, Naranja X, Belo, Mercado Pago, Monzo). Demo tenant is **Bondi Indumentaria**, a streetwear shop. 8 navigable screens, light + dark themes, a full checkout flow.

## About the design files
The files in this bundle are **design references created in HTML** — a prototype showing the intended look, layout, and behavior. They are **not production code to copy directly**. The task is to **recreate these designs in your target codebase** using its established framework, component library, and patterns (React, Vue, Svelte, etc.). If no front-end environment exists yet, pick the most appropriate stack — a React + TypeScript SPA with a component library (e.g. Radix/shadcn) and a charting lib maps cleanly onto what's here.

Notes on the prototype's internal tech (for reference only — do **not** port it):
- `Vendi v2.dc.html` is a single-file "Design Component": an HTML template plus a `class Component` logic class, rendered by the bundled `support.js` runtime (a small React-based renderer). Treat `support.js` as scaffolding — you do not need to read or reuse it.
- All state lives in the one `Component` class (`state = {...}`, `renderVals()`), all styling is inline. This is prototype convenience, not an architectural recommendation. In a real app, split into components, move data to an API/store, and use your styling system.
- Product photos are simulated with **warm color blocks + a simple garment glyph** per product. In production these become real lookbook images (see Assets).

## Fidelity
**High-fidelity.** Colors, typography, spacing, radii, shadows, and interactions are final and intentional. Recreate the UI faithfully using your codebase's libraries. Exact tokens are listed below.

## Locale & formatting rules (important — this is an Argentine product)
- **Language:** Spanish, Rioplatense register, warm/informal tone ("vos" forms, "Cargá", "Vendé"). Keep all copy in Spanish.
- **Currency:** Argentine peso, format `$ 38.900` — space after `$`, dot as thousands separator, no decimals for whole amounts. The prototype uses `Math.round(n).toLocaleString('es-AR')`.
- **Domain terms to preserve:** *factura / CAE* (AFIP electronic invoice + auth code), *cuenta corriente* (store credit / running account), *fiado* (buy-now-pay-later balance), *sucursal* (branch), *Responsable Inscripto / Monotributo* (AFIP tax conditions), *CUIT*, *punto de venta*.
- **Numbers** use the display font, tabular feel, and `es-AR` grouping throughout.

---

## Design tokens

### Brand & signature
- The chromatic signature is a **violet + amber bitone** — never monochrome. Use both across the UI.
- The canvas is **warm cream, never pure white** (`#FFFFFF` is disallowed for backgrounds).
- One **gradient "hero" moment per screen** (violet→lilac→amber).

### Color — Light theme
| Token | Hex | Use |
|---|---|---|
| bg (canvas) | `#F8F3EC` | app background (warm cream) — signature #1 |
| bg2 | `#F1E8DA` | deeper cream |
| surface | `#FFFDF9` | cards (warm white) |
| surface2 | `#F4ECDF` | inset/secondary surfaces |
| surface3 | `#EADFCD` | tertiary |
| border | `#EADFCD` | hairlines |
| border2 | `#DCCCB1` | stronger borders |
| fg | `#241C12` | primary text (warm near-black) |
| fg2 | `#6E6047` | secondary text |
| fg3 | `#A89372` | tertiary/muted |
| **accent (primary)** | **`#6C4DF2`** | violet — primary buttons, active states, key data |
| **amber (secondary)** | **`#F2884B`** | burnt orange — bitone accent, chart highlights |
| danger | `#E5484D` | coral red — critical/errors |
| warn | `#E08A2B` | amber warning |
| success | `#2FA56A` | green |
| wa (WhatsApp) | `#25D366` | reserved for WhatsApp buttons only |

Warm shadows (multi-layer, warm-tinted — cards float over the cream):
- shadow: `0 1px 2px rgba(120,85,30,.05), 0 5px 16px rgba(120,85,30,.06)`
- shadowLg: `0 20px 46px rgba(110,75,25,.16)`

Hero gradient (light): `linear-gradient(122deg, #6C4DF2 0%, #8257EF 40%, #C667A7 73%, #F2894B 112%)`
Canvas has faint radial blobs: violet at 14%/12%, amber at 88%/6%.

### Color — Dark theme (first-class, not an inversion)
| Token | Hex |
|---|---|
| bg (canvas) | `#1B1714` (warm graphite) |
| bg2 | `#15110E` |
| surface | `#241F1B` |
| surface2 | `#2D2720` |
| surface3 | `#382F26` |
| border | `#352D23` |
| border2 | `#493D2E` |
| fg | `#F5EEE3` |
| fg2 | `#B8A98F` |
| fg3 | `#80715A` |
| accent | `#8B72F5` (luminous violet) |
| amber | `#F7A063` |
| danger | `#F2696D` · warn `#F5A742` · success `#46C088` |

Hero gradient (dark): `linear-gradient(122deg, #6E4FF0 0%, #8158EE 40%, #B45F9C 74%, #E07E48 112%)`

### Per-product accent palette (for lookbook color blocks / avatars)
Each is a `{color, soft}` pair, used as `soft` background + `color` glyph. Light / dark:
- lila `#6C4DF2` / soft `#E8E1FB` · dark `#9A82F7` / `#2E2743`
- amber `#F2884B` / `#FBE6D4` · dark `#F7A063` / `#392B1F`
- coral `#E5484D` / `#FBDEDC` · dark `#F2696D` / `#3A2421`
- green `#2FA56A` / `#DCEDE2` · dark `#46C088` / `#1E3328`
- butter `#CB8A1E` / `#F7E9C9` · dark `#E0A53E` / `#352B19`
- peach `#E76A8D` / `#FBDEE6` · dark `#F08AA6` / `#3A2530`

### Typography
- **Display (titles + all numbers):** **Clash Display** (weights 400–700), grotesque with character. Used for the wordmark, view titles, KPI numbers, prices, totals, chart values. Letter-spacing tightened on large sizes (`-.02em`).
- **Body / UI:** **General Sans** (400–700). All labels, table text, paragraphs.
- Both from Fontshare: `https://api.fontshare.com/v2/css?f[]=clash-display@400,500,600,700&f[]=general-sans@400,500,600,700&display=swap`
- Do **not** use Inter/Roboto/Arial.
- Rough type scale in use: KPI 26px/700, view title 17px/600, section title 14px/600, body 13–14px/400–500, labels 11–12px/500–600, big totals 26–34px/700.

### Shape & depth
- Radii: cards **22px**, inner cards/inputs 13–16px, chips & buttons **pill (999px)**.
- Icons: rounded style (Phosphor / Iconoir vibe), 1.2–2.2 stroke weight, never sharp.
- Avatars & product blocks use warm color fills; soft blobs sit behind hero zones.

---

## Screens / Views

Global chrome: fixed **248px left sidebar** (wordmark, grouped nav, footer with tenant avatar "BI" + "Bondi Indumentaria" / "Camila Ortiz · Admin" + theme toggle). **60px top header** with contextual title/subtitle, global search, and an **open/close-register** ("Caja") pill. Content area scrolls; max content width ~1180–1200px.

Nav groups: GENERAL (Inicio, Vender) · CATÁLOGO (Productos, Stock) · RELACIONES (Clientes, Reportes) · SISTEMA (Configuración).

### 1. Inicio / Dashboard
- **Purpose:** daily pulse of the shop.
- **Layout:** 4-up KPI card row; then 2-col (1.6fr / 1fr). Left col: "Ventas · últimos 7 días" bar chart card + AI-insight gradient hero. Right col: stock-alerts list card + quick-actions card.
- **KPIs:** Ventas de hoy `$ 284.600` (+12% vs ayer, green), Ticket promedio `$ 19.200`, Estado de caja (Abierta, accent) `$ 142.300 disponible`, Unidades vendidas `38`.
- **Chart:** 7 bars (Lun–Dom); weekend bars (≥320) render in **amber**, weekdays in **violet** with graded opacity. Total `$ 1.809.600`.
- **AI hero (gradient):** violet→amber card, sparkle icon, copy: "El **Buzo Oversize en Negro** se agota en ~4 días al ritmo actual. Quedan 6 unidades y vendés ~1,5 por día." Buttons: "Reponer 24 u" (white/violet) + "Descartar".
- **Stock alerts:** badge "4 críticas" (coral). Rows: product · variant (e.g. "Negro · Talle L") · qty, dot + qty colored by severity (danger/warn).
- **Quick actions:** Nueva venta → POS, Cargar producto → Productos, Ver reportes → Reportes.

### 2. Vender / POS (star screen — must be touch-comfortable / tablet-friendly)
- **Layout:** two columns. Left = product picker (flex:1), right = **392px cart panel**.
- **Left:** search input with **barcode-scanner icon**, category pill filters (Todos/Buzos/Remeras/Pantalones/Camperas/Accesorios), then a responsive product grid (`minmax(158px,1fr)`). Each product card: square color-block image + garment glyph, "Stock bajo" pill if low, code, name, variant hint ("4 talles · 3 colores"), price. Tapping a product with variants opens the **variant modal**; single-variant products add directly.
- **Right (cart):** header "Carrito" + item count. **Client selector** button (Consumidor final by default; dropdown lists customers with fiado balance shown in amber). Cart line items: thumbnail, name, "Talle M · Negro", qty stepper (− / n / +), line total, remove (×). Footer: **Descuento** % input, Subtotal, dashed divider, **Total** (large display font), and a big **"Cobrar $…"** button (violet, 54px, card-radius 13px). Empty state: cart icon + "El carrito está vacío".
- **Register control** lives in the top header on this screen too.
- Touch: hit targets ≥44px, large tap cards, big Cobrar button.

### 3. Productos (list)
- Category pill filters, **grid/table view toggle**, "+ Nuevo producto" (violet).
- **Grid:** cards with 4:3 color-block image, name, status pill (Activo = accent-soft / Stock bajo = danger-soft), category, price + total stock (`u`), stock number turns danger when low.
- **Table:** columns Producto (thumb+name+code) · Categoría · Stock (right) · Precio (right) · Estado (right). Rows clickable → product detail.

### 4. Ficha de producto (detail)
- "Volver a productos" back button.
- **Layout:** 2-col (0.85fr / 1.15fr). Left = gallery: big square color-block hero (with "foto · CODE" chip) + 4 thumbnails (first selected, ring in accent). Right = title block, **Precios** card (Costo / Venta[accent] / Mayorista) + margin row ("Margen sobre venta" %, computed), metadata card (código de barras, proveedor, tags), **Campos personalizados** card (Temporada, Material…) with a "Vendi" badge.
- **Below full-width:** **variant matrix** — rows = colors (dot + name), columns = sizes; each cell is an editable stock number. Cell border/color encodes state: 0 = danger + danger-soft bg, ≤2 = warn, else normal. Header shows total stock `u`.

### 5. Stock / Inventario
- Branch pills (**Local Centro** active, **Depósito**). Actions: **Ajustar stock** (outline), **Transferir** (violet).
- **Table:** Producto (thumb+name+code) · Local Centro · Depósito · Total · Mínimo · Estado (Reponer = danger / OK = accent). Total turns danger when low. (Prototype splits total 62%/38% across the two branches.)
- **AI reposición hero (gradient):** violet→amber card listing suggested restocks — product · variant · reason (e.g. "Se agota en ~4 días…"), suggested qty `+24` (amber number), "Reponer" button per row.

### 6. Clientes + Cuenta corriente
- **Layout:** 320px customer list (left) + detail (right).
- **List:** "+ Nuevo cliente" dashed button; rows = avatar initials, name, type (Minorista/Mayorista), balance (amber if fiado, else "Al día"). Selected row highlighted.
- **Detail:** header card — avatar, name, "Minorista · desde ene 2024"; a divider row with **Saldo cuenta corriente** (amber if owing), phone, doc (DNI/CUIT), and a green **"Recordar pago"** WhatsApp button (only when balance > 0). Then **Movimientos de cuenta corriente** table (Fecha · Detalle · Debe[danger] · Haber[accent] · Saldo). Then **Historial de compras** list.
- Sample data: Lucas Fernández owes `$ 24.500`, La Rambla (mayorista) `$ 112.000`, Tienda Norte `$ 48.300`; Martina Gómez & Sofía Ruiz al día.

### 7. Reportes
- Date-range pill (`1 – 24 jun 2026`) + **Exportar** button.
- 3 KPI cards: Margen total `$ 648.200`, Ganancia neta `$ 412.900` (accent), Rotación de inventario `2,4×`.
- **Ventas por período** bar chart (Ene–Jun; top months in amber, rest violet).
- Two-up: **Más vendidos** (unit bars, violet) + **Más rentables** (margin per unit, amber bars). Then two-up: **Ventas por vendedor** (Camila Ortiz / Nahuel Díaz / Brenda Sosa, avatar + bar) + **Ventas por sucursal** (Local Centro / Depósito, amber bars).

### 8. Configuración / Customización (the differentiator)
- **Módulos:** on/off switches — Facturación AFIP (on), E-commerce (off), Producción (off), Promociones (on), Empleados (on). Each row: label + description + pill toggle (track violet when on).
- **Marca / white-label:** nombre del comercio (editable input), logo tile, **color de acento** swatch picker (Esmeralda/Violeta/Azul/Naranja/Magenta — but default is Violeta `#6C4DF2`; selecting one re-themes the whole app live). Plus a **"Compartir catálogo"** green WhatsApp button.
- **Campos personalizados:** editor list (entity badge · label · type) with add/remove — Temporada (Producto/Lista), Material (Producto/Texto), Cumpleaños (Cliente/Fecha).
- **Datos fiscales:** Condición AFIP (Responsable Inscripto), CUIT `30-71045882-3`, Punto de venta `0003`, Ingresos Brutos. "Guardar cambios" (violet).

---

## Interactions & behavior
- **Navigation:** sidebar switches the active view (single-page). Product cards (Productos) open the Ficha; "Volver" returns. Quick actions and the AI-insight "Reponer" jump between views.
- **Theme toggle:** button in sidebar footer flips light/dark across the entire app instantly. Both themes are hand-tuned (see tokens) — do not derive one from the other by inversion.
- **Register (Caja):** header pill toggles open/closed; reflected on the dashboard "Estado de caja" KPI; shows a toast on change.
- **Star flow (POS checkout):** tap product → **variant modal** (pick color chip → size cell; unavailable sizes disabled/"agotado"; add button enables once size chosen) → item in cart → adjust qty/discount/client → **Cobrar** → **payment modal** (Efectivo, Débito, Crédito, Transferencia, QR/MercadoPago — QR spans full width; one selectable) → **Confirmar cobro** → **receipt modal** ("Venta registrada", ticket #, method, total, item count, client) with **Imprimir ticket** + green **Enviar por WhatsApp** + "Nueva venta". Confirming clears the cart.
- **WhatsApp touchpoints (green #25D366):** POS receipt "Enviar por WhatsApp"; Clientes "Recordar pago"; Configuración "Compartir catálogo". Each fires a confirmation toast.
- **Toasts:** bottom-center pill, dark chip on light (and vice-versa), auto-dismiss ~2.6s. Used for add-to-cart, register changes, WhatsApp actions, saves, demo actions.
- **Animations:** modal overlays fade + pop (`transform` scale/translate, ~.15–.18s ease); toast slides up; dropdowns pop. Keep transitions transform/opacity-based and short.
- **Hover:** cards/buttons lighten border to `border2` or `accent`; nav items get `surface2` bg.
- **Micro-interactions/state:** qty stepper min 1; discount clamped 0–90%; variant add disabled until a size is picked; payment confirm disabled until a method is chosen.

## State management
Prototype keeps everything in one component's state. In a real build, model roughly:
- **UI:** `view`, `theme` (`'light'|'dark'`), `accentColor`, `gridView` (Productos), `cashOpen`, active toast.
- **Cart/checkout:** `cart[]` (id, name, size, color, qty, price, key), `client`, `discount`, and modal flags: `variantFor`+`pickColor`/`pickSize`, `payOpen`+`payMethod`, `receiptOpen`+`lastSale`.
- **Selections:** `selectedProduct` (Ficha), `selectedClient` (Clientes).
- **Config:** `modules{}`, `customFields[]`, `storeName`.
- **Derived:** subtotal, total (after discount), item count, per-product total stock, computed margins, chart aggregates.
- **Data fetching (real app):** products (with talle×color variant stock matrix, costo/venta/mayorista prices, barcode, supplier, tags, custom fields), customers (with cuenta-corriente movements & balances), sellers, per-branch stock, sales/reports aggregates, AFIP fiscal config. Money math server-side; format `es-AR` on display.

## Assets
- **Fonts:** Clash Display + General Sans (Fontshare, link above).
- **Icons:** inline SVG, rounded (Phosphor/Iconoir equivalents) — replace with your icon lib.
- **Product images:** the prototype uses **warm color block + garment glyph** placeholders (hoodie/tee/pants/jacket/cap paths). In production, use **real lookbook photography**; the color-block treatment is the intended graceful fallback when a product has no photo (never a gray striped placeholder).
- **No external image files** are shipped in this bundle.

## Files
- `Vendi v2.dc.html` — the full hi-fi prototype (all 8 screens, both themes, all flows). Open in a browser to interact. This is the design-of-record.
- `support.js` — prototype runtime only; **do not port**. Included so the HTML opens standalone.
- (In the project, `Vendi.dc.html` is the earlier, more generic v1 — superseded by v2. Ignore for development.)

## How to review the prototype
Open `Vendi v2.dc.html` in a browser. Use the sidebar to visit all 8 screens, toggle the theme in the sidebar footer, and run the POS checkout end-to-end (Vender → tap "Buzo Oversize Frizado" → pick color+size → Cobrar → choose a payment method → Confirmar → Enviar por WhatsApp).
