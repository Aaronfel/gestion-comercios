# Módulo: Reportes & Dashboard

- **Fase:** 5
- **Estado:** 📝 en definición
- **Pantalla(s) del design:** Inicio / Dashboard (§1) · Reportes (§7) (ver `design_handoff_vendi_pos/README.md`)
- **Depende de:** Ventas/POS, Stock, Caja, Clientes (y referencia a Insights "IA-lite").

## Propósito
Darle al comerciante el pulso diario del negocio (dashboard) y el análisis por período
(reportes): ventas, ticket promedio, unidades, márgenes, rotación y rankings. Todo es
**lectura agregada** sobre datos de otros módulos — no genera datos propios.

## Entidades y datos
**Ninguna entidad propia.** Consume agregados (query services / read-models) sobre modelos
ya `tenant`-scoped: `sales` · `sale_items` · `payments` (Ventas) · `stock` · `stock_movements`
(Stock) · `cash_registers` · `cash_movements` (Caja) · `customers` · `account_movements`
(Clientes). Al leer esas tablas, el `TenantScope` ya filtra por tenant.

> Todos los KPIs, series y rankings se **calculan server-side** (SQL agregado sobre las
> ventas/costos del tenant); formato `es-AR` (`$ 284.600`) solo en display. Ventana "hoy"
> según timezone del comercio (`America/Argentina/Buenos_Aires`).

## Reglas de negocio
1. **Ventas de hoy** = suma de ventas del día + variación `%` vs ayer (verde si sube).
2. **Ticket promedio** = ventas del día / cantidad de tickets (`0` si no hubo ventas).
3. **Estado de caja** = del módulo Caja (abierta/cerrada + disponible); no se recalcula acá.
4. **Unidades vendidas** = suma de `qty` de los `sale_items` del día.
5. **Chart 7 días:** una barra por día; fin de semana con venta alta en ámbar, resto violeta;
   muestra el total del período.
6. **Alertas de stock bajo:** filas `qty ≤ mínimo` provistas por **Stock** (severidad danger/warn).
7. **Card de insight IA:** contenido del módulo **Insights "IA-lite"** (regla aritmética, sin LLM);
   este módulo solo la renderiza en el hero del dashboard.
8. **Reportes por rango de fechas:** ventas por período (mensual), más vendidos (unidades),
   más rentables (margen/unidad), rotación de inventario, ventas por vendedor, ventas por
   sucursal, **margen total** (`Σ venta − costo`) y **ganancia neta**.
9. **Reportes** expone costos/márgenes/ganancia → acceso **solo `admin`** (ver Notas).
10. **Exportar** genera el reporte del rango y scope actuales (CSV en v1).

## Acciones / endpoints

| Acción | Ruta / método | Rol | Descripción |
|--------|---------------|-----|-------------|
| Dashboard | `GET /` (inicio) | admin, vendedor | KPIs del día, chart 7 días, alertas, insight, accesos rápidos |
| Reportes | `GET /reportes?desde=&hasta=` | admin | KPIs de período + charts (rankings, vendedor, sucursal) |
| Exportar | `GET /reportes/exportar?desde=&hasta=&tipo=` | admin | Descarga CSV del rango |

## UI
Handoff §1 (fila de 4 KPIs; luego 2 col: chart "Ventas · últimos 7 días" + hero de insight
IA violeta→ámbar; y lista de alertas de stock + accesos rápidos Nueva venta/Cargar producto/
Ver reportes) y §7 (pill de rango + **Exportar**; 3 KPIs Margen total/Ganancia neta[accent]/
Rotación `2,4×`; bar chart por período; two-up Más vendidos[violeta]/Más rentables[ámbar];
two-up Ventas por vendedor/por sucursal). Charts con **Recharts**. Componentes shadcn: card,
badge, button, select/tabs (rango). Números `font-display` tabular. Estados: sin ventas en el
período → placeholder "Sin datos en el rango"; loading → skeletons de card/chart.

## Multi-tenant & permisos
- Scoping: sin modelos propios; los agregados heredan el `TenantScope` de las tablas que leen
  (Ventas/Stock/Caja/Clientes usan `BelongsToTenant`). **Nunca** consultar cross-tenant.
- Roles: **Dashboard** → `admin` y `vendedor` (KPIs operativos). **Reportes** → `admin` (expone costos/margen/ganancia).

## Fuera de alcance
- Generar ventas/stock/caja/clientes → sus módulos fuente.
- La lógica del insight ("se agota en ~4 días") → módulo **Insights "IA-lite"**.
- El cálculo de qué stock está bajo → módulo **Stock** (acá solo se listan sus alertas).
- Reportes fiscales / IVA / libro de ventas → **AFIP** (diferido).
- Dashboards configurables por usuario y exportación a PDF (post-MVP).

## Criterios de aceptación
- [ ] "Ventas de hoy" suma solo las ventas del día del tenant y calcula la variación `%` vs ayer.
- [ ] "Ticket promedio" = ventas del día / cantidad de tickets (`0` sin ventas).
- [ ] El chart de 7 días agrupa las ventas por día y marca el fin de semana con venta alta en ámbar.
- [ ] Reportes respeta el rango `desde`/`hasta` (una venta fuera del rango no cuenta).
- [ ] "Margen total" y "Ganancia neta" se calculan server-side con los costos del tenant.
- [ ] Un `vendedor` recibe 403 al entrar a Reportes (no ve costos/margen/ganancia).
- [ ] "Más vendidos" ordena por unidades desc y "Más rentables" por margen/unidad desc.
- [ ] Los agregados nunca incluyen ventas de otro tenant (test de aislamiento).

## Notas / preguntas abiertas
- **Reportes solo `admin`** es una decisión propuesta (dashboard operativo vs. reportes con costos) — confirmar con el socio.
- ¿Fórmula de **rotación** = unidades vendidas / stock promedio, o COGS / inventario valuado? (el `2,4×` del design necesita definición).
- ¿**Ganancia neta** = margen total − egresos/gastos de caja? (el §7 la muestra menor que el margen → descuenta algo).
- ¿Timezone fijo `America/Argentina/Buenos_Aires` o configurable por tenant?
- ¿El dashboard agrega todas las sucursales o permite elegir una?
