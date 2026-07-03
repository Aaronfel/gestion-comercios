# Módulo: Insights "IA-lite"

- **Fase:** 2–5 (nace en Stock, se muestra en Inicio; consume ventas de POS)
- **Estado:** 📝 en definición
- **Pantalla(s) del design:** Inicio (AI hero, §1) · Stock (reposición sugerida, §5) (ver `design_handoff_vendi_pos/README.md`)
- **Depende de:** Stock (Fase 2), POS/Ventas (Fase 3, historial de ventas)

## Propósito
Dar al comerciante avisos accionables ("qué reponer y cuándo") sin que tenga que mirar
planillas. **NO es IA ni LLM**: es **aritmética simple** sobre datos que ya existen
(ventas + stock). El copy suena inteligente, pero abajo hay una división. El diferencial
es el *framing* (hero con sparkle, lenguaje humano), no un modelo.

## Entidades y datos
**Sin entidades nuevas.** Lee datos existentes:
- Ventas / `sale_items` (POS) — para la **velocidad de venta** (rotación).
- `stock` por variante/sucursal (Stock) — para las **unidades disponibles**.

Los resultados se calculan on-demand (server-side) o se cachean; no se persiste un
modelo de "insight". Opcionalmente se materializa vía el evento de dominio `StockLow`.

## Reglas de negocio
1. **Velocidad de venta** de un producto/variante = unidades vendidas ÷ días, sobre una
   ventana de **rotación de los últimos 30 días**.
2. **Días hasta agotar** = `stock disponible ÷ velocidad de venta`. Ej.: 6 u ÷ ~1,5 u/día
   = ~4 días → "se agota en ~4 días al ritmo actual".
3. **Velocidad = 0** (sin ventas en la ventana): **no** se calcula "días hasta agotar"
   (no dividir por cero, no mostrar "∞"); ese ítem no genera insight de agotamiento.
4. **Sugerencia de reposición**: cantidad a reponer según rotación, con forma
   "cubrir N días de venta al ritmo actual − stock disponible" (redondeado hacia
   arriba). El **N exacto** (horizonte de cobertura) queda como parámetro a definir
   (ver Notas) — no está fijado por el design.
5. Todo el cálculo es **server-side**; el front solo muestra. Montos/números en `es-AR`.
6. El copy es aproximado por diseño ("~4 días", "~1,5 por día"): se redondea y se
   antepone "~" para no prometer precisión falsa.
7. Se dispara/consume el evento `StockLow` (plan técnico §6) para que Insights (y
   WhatsApp/plugins) se enganchen por eventos, sin acoplar al core del POS/Stock.

## Acciones / endpoints
No tiene rutas propias; expone un **servicio + eventos** que consumen otras pantallas:

| Cálculo | Consumido por | Rol | Forma |
|---------|---------------|-----|-------|
| Días hasta agotar (top riesgo) | Inicio (AI hero) | admin, vendedor | 1 producto destacado + copy |
| Reposición sugerida | Stock (hero de reposición) | admin, vendedor | Lista producto·variante·razón·cantidad `+N` |
| Alertas de stock bajo | Inicio (lista) · evento `StockLow` | admin, vendedor | Filas por severidad |

Servicio sugerido: `App\Services\StockInsights` con p.ej. `daysUntilOut(variant)`,
`suggestedRestock(variant)`, `topRisk(tenant)`.

## UI
Handoff §1 (AI hero gradiente violeta→amber, ícono sparkle, copy tipo "El **Buzo
Oversize en Negro** se agota en ~4 días al ritmo actual. Quedan 6 unidades y vendés
~1,5 por día." + botones "Reponer 24 u" / "Descartar") y §5 (hero de reposición en
Stock: filas con producto · variante · razón · cantidad sugerida `+24` en amber +
botón "Reponer"). Un solo momento gradiente por pantalla. Números en `font-display`.

## Multi-tenant & permisos
- Scoping: sin modelos propios; los cálculos corren sobre ventas/stock ya scopeados por
  `BelongsToTenant`, así que un insight jamás mezcla datos de otro tenant.
- Roles: visible para `admin` y `vendedor` (ambos operan y reponen).

## Fuera de alcance
- **IA / LLM real** (recomendaciones generativas, texto libre) — diferido.
- **Carga de productos por foto** (visión) — diferido.
- **Búsqueda en lenguaje natural** — diferido.
- Predicción con estacionalidad / series temporales avanzadas — futuro; MVP es rotación simple.
- Ejecutar la reposición (crear orden de compra) → módulo Proveedores/Compras (diferido); acá solo se sugiere.

## Criterios de aceptación
- [ ] Con stock 6 y velocidad ~1,5 u/día, "días hasta agotar" da ~4 (rango redondeado esperado).
- [ ] Velocidad = 0 (sin ventas en 30 días) **no** produce "días hasta agotar" ni divide por cero.
- [ ] La velocidad se calcula sobre la ventana de 30 días (ventas fuera de la ventana no cuentan).
- [ ] La cantidad de reposición sugerida sigue la regla de cobertura y nunca es negativa.
- [ ] El copy sale en español rioplatense con números `es-AR` y "~" para lo aproximado.
- [ ] El cálculo es server-side (el front no recalcula) y usa solo datos del tenant actual (aislamiento).
- [ ] Un ítem con stock bajo emite/gatilla `StockLow` para que otros módulos se enganchen.

## Notas / preguntas abiertas
- **Horizonte de cobertura (N) de la reposición**: ¿fijo (p.ej. 15 días), configurable por tenant, o atado al stock mínimo del producto? (el "24 u" del design no es derivable de los datos).
- ¿La ventana de rotación es siempre 30 días o configurable? ¿Por producto o global?
- ¿"Días hasta agotar" se calcula por variante (talle×color) o agregado por producto? (el copy del design habla de un color puntual → sugiere por variante).
- ¿Se cachea el cálculo (job nocturno) o se computa en cada request?
