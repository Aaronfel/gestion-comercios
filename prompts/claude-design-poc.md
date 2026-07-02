# Prompt para Claude Design — POC "Vendi" (POS Argentina)

> Pegá todo lo que está dentro del bloque en Claude Design. Reemplazá el nombre
> **Vendi** por tu marca si ya la tenés. Está pensado para generar un prototipo
> visual navegable de 8 pantallas.

---

Diseñá un prototipo visual navegable (alta fidelidad) de **Vendi**, un sistema de
gestión todo-en-uno para comercios en **Argentina** — punto de venta, stock,
clientes y reportes. Posicionamiento: el primer POS *de categoría* (nivel
pulpos.com de México) para el mercado argentino. Usá un comercio de
**indumentaria** como datos de ejemplo.

## Dirección visual

- Estilo **SaaS moderno premium**, tipo Linear / Vercel: limpio, mucho espacio en
  blanco, tipografía clara (sans-serif tipo Inter), bordes suaves, sombras sutiles.
- **Incluí un toggle de modo claro / oscuro** funcional en el header. Diseñá ambos
  modos cuidados, no solo invertir colores.
- Un único **color de marca de acento** (proponé uno premium, ej. violeta o verde
  esmeralda) usado en botones primarios, estados activos y datos clave.
- **Sidebar de navegación fija** a la izquierda con: Inicio, Vender (POS),
  Productos, Stock, Clientes, Reportes, Configuración. En el footer del sidebar:
  avatar del usuario, nombre del comercio y el toggle de tema.
- **Desktop-first**, pero la pantalla de POS debe verse cómoda en **tablet/touch**
  (botones grandes, targets táctiles).
- Copy en **español rioplatense**. Montos en **pesos argentinos** (formato
  `$ 24.900`). Terminología local: factura/CAE, cuenta corriente, fiado, sucursal.

## Datos de ejemplo (comercio de indumentaria)

- Comercio demo: **"Bondi Indumentaria"** (streetwear), responsable inscripto.
- Productos con variantes **talle × color**: ej. "Buzo Oversize Frizado"
  (talles S/M/L/XL × colores Negro/Beige/Verde), "Remera Box Algodón", "Pantalón
  Cargo", "Campera Puffer", "Gorra Trucker". Cada uno con foto (placeholder),
  precio de venta, costo y precio mayorista.
- Clientes con cuenta corriente: algunos con saldo de fiado pendiente.
- Vendedores: 2-3 nombres para los reportes por vendedor.

## Pantallas (8 vistas navegables entre sí)

### 1. Inicio / Dashboard
KPIs del día en tarjetas: Ventas de hoy, Ticket promedio, Estado de caja,
Unidades vendidas. Gráfico de ventas de los últimos 7 días. Panel de **alertas de
stock bajo** (productos por debajo del mínimo). Una tarjeta destacada de **insight
de IA** (ej.: *"El Buzo Oversize en Negro se agota en ~4 días al ritmo actual.
¿Reponer?"*). Accesos rápidos: Nueva venta, Cargar producto, Ver reportes.

### 2. Vender / POS  ⭐ (pantalla estrella)
Layout de dos columnas. Izquierda: buscador de productos (con ícono de
escáner de código de barras) y grilla de productos con foto. Al elegir un producto
con variantes, abrir un **selector de talle × color** (matriz/chips). Derecha:
**carrito** con ítems, cantidades, subtotal, campo de descuento, y selector de
**cliente** (opcional, para fiado). Abajo: total grande y botón **"Cobrar"**.
Al cobrar, modal de **medios de pago** (efectivo, débito, crédito, transferencia,
QR/MercadoPago) y, al confirmar, opciones **"Imprimir ticket"** y **"Enviar por
WhatsApp"**. Mostrar botón de apertura/cierre de caja en el header de esta vista.

### 3. Productos (listado)
Tabla/grilla con foto, nombre, categoría, **stock total**, precio de venta y
estado. Filtros por categoría, búsqueda, y botón **"+ Nuevo producto"**.
Indicadores visuales para stock bajo. Toggle vista grilla/tabla.

### 4. Ficha de producto
Detalle de un producto. Galería de fotos. **Matriz de variantes talle × color con
el stock por celda** (editable). Bloque de **multi-precio**: costo, precio de
venta, precio mayorista, y margen calculado. Campos: categoría, código de barras,
proveedor, tags. **Mostrá una sección de "Campos personalizados"** (ej.:
Temporada, Material) para insinuar la customización por cliente.

### 5. Stock / Inventario
Existencias por **depósito/sucursal** (ej.: Local Centro, Depósito). Lista con
columnas por sucursal, stock mínimo y alertas. Acciones: **Ajustar stock**,
**Transferir entre sucursales**. Incluí un panel de **sugerencia de reposición por
IA** (lista de productos a reponer con cantidad sugerida según rotación).

### 6. Clientes + Cuenta corriente
Listado de clientes con saldo de **cuenta corriente (fiado)**. Ficha de cliente:
datos, historial de compras, **movimientos de cuenta corriente** (debe/haber/saldo)
y botón **"Enviar recordatorio de pago por WhatsApp"**. Botón "+ Nuevo cliente".

### 7. Reportes
Dashboard analítico: ventas por período (gráfico), productos más vendidos y más
rentables, **rotación de inventario**, ventas por vendedor y por sucursal. Filtro
de rango de fechas. Tarjetas de margen total y ganancia. Botón exportar.

### 8. Configuración / Customización  🧩 (muestra el diferenciador)
Panel de administración del *tenant*, en secciones:
- **Módulos**: lista de features con switches on/off (Facturación AFIP,
  E-commerce, Producción, Promociones, Empleados, etc.) — muestra cómo se
  personaliza por cliente.
- **Marca / White-label**: logo, color de acento, nombre del comercio.
- **Campos personalizados**: editor para agregar campos a productos/clientes.
- **Datos fiscales**: condición AFIP (monotributo / responsable inscripto), CUIT,
  punto de venta.

## Interacción

- Las 8 vistas deben ser **navegables** desde el sidebar.
- El toggle claro/oscuro debe cambiar el tema de todo el prototipo.
- El flujo estrella a dejar pulido: **POS → elegir producto → variante → cobrar →
  medio de pago → enviar por WhatsApp**.
- Estados realistas: datos cargados (no lorem vacío), badges de stock bajo, saldos
  de fiado, un insight de IA visible.

## Lo que NO incluir

Backend, login, configuración de impresoras, ni los módulos extendidos completos.
Es un **prototipo visual** para validar la propuesta y mostrársela a clientes.
