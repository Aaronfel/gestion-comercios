# Módulo: Empleados

- **Fase:** diferido
- **Estado:** ⬜ diferido (post-MVP)
- **Pantalla(s) del design:** — (sin pantalla en el handoff; a diseñar)
- **Depende de:** Auth & Tenancy (Fase 0) · POS / Ventas (Fase 3) · Reportes & Dashboard (Fase 5)

> **Implementación post-MVP.** Esta spec queda como definición de referencia. En el MVP
> "vendedor" es **solo un rol** (ver `auth-tenancy.md`) y las ventas ya registran su
> vendedor (ver Reportes por vendedor). Este módulo agrega la capa de RRHH/comisiones
> por encima de eso; no se construye en el MVP.

## Propósito
Gestionar empleados/vendedores más allá del `User` básico: turnos y horarios, comisiones
por venta por vendedor y objetivos. Permite liquidar comisiones por período y medir el
desempeño de cada vendedor sobre las ventas que ya registran su autor.

## Entidades y datos

- **Employee** — `tenant_id` · `user_id?` (link al login; null = empleado sin usuario) ·
  `name` · `document` · `position` · `hire_date` · `commission_rate?` (% general por defecto) ·
  `active`
- **Shift** (turno) — `tenant_id` · `employee_id` · `branch_id` · `starts_at` · `ends_at` ·
  `check_in?` · `check_out?`
- **Commission** (comisión) — `tenant_id` · `employee_id` · `sale_id` · `base_amount` ·
  `rate` · `amount` · `period` (YYYY-MM) · `status` (pendiente/liquidada)
- **Goal** (objetivo) — `tenant_id` · `employee_id` · `period` · `target_amount` · (cumplimiento calculado)

> Comisiones, cumplimiento de objetivos y horas se calculan **server-side**. Modelos de
> dominio usan `BelongsToTenant`. `Employee` se vincula al `User` (que se scopea explícito por `tenant_id`).

## Reglas de negocio
1. La venta ya guarda su vendedor (`sale.seller_id`). Al completarse (`SaleCompleted`) se
   genera una `Commission` para el `Employee` correspondiente, si tiene esquema de comisión.
2. `amount = base_amount × rate` — `base_amount` según config (total de venta o margen);
   cálculo server-side, nunca en el front.
3. La **liquidación** agrupa comisiones `pendiente` de un `period` y las marca `liquidada`
   (inmutables desde ahí).
4. Cumplimiento de objetivo = ventas del empleado en el período ÷ `target_amount` — calculado, no almacenado.
5. Los turnos son **informativos** en v1 (no bloquean el POS). Vincular apertura de caja a
   turno queda como opción de config a evaluar.
6. Un `Employee` inactivo no genera nuevas comisiones ni objetivos, pero conserva su historial.

## Acciones / endpoints

| Acción | Ruta / método | Rol | Descripción |
|--------|---------------|-----|-------------|
| Listar/CRUD empleados | `/empleados…` | admin | ABM de empleados y su esquema |
| Cargar turnos | `/empleados/{employee}/turnos` | admin | Alta/edición de turnos |
| Ver comisiones | `/empleados/{employee}/comisiones` | admin | Comisiones por período |
| Liquidar período | `POST /empleados/comisiones/liquidar` | admin | Cierra comisiones de un `period` |
| Definir objetivos | `/empleados/{employee}/objetivos` | admin | Metas por período |
| Ver mis comisiones/objetivos | `/mi-desempeno` | vendedor | Solo lo propio |

## UI
Sin pantalla en el handoff — a diseñar con los tokens del design. Listado de empleados,
ficha con turnos/comisiones/objetivos, y una vista de liquidación por período. El vendedor
ve solo su propio desempeño. Componentes shadcn: table, card, badge, tabs, dialog. Números
en `font-display` tabular.

## Multi-tenant & permisos
- Scoping: `Employee`, `Shift`, `Commission`, `Goal` → `BelongsToTenant`.
- Roles: `admin` gestiona empleados, esquemas, turnos y liquidaciones; `vendedor` ve solo
  sus comisiones y objetivos.

## Fuera de alcance
- Liquidación de sueldos / nómina completa (aportes, recibos, impuestos) → fuera del producto.
- Fichada biométrica / control de acceso físico → futuro (los turnos son informativos).
- Definición del rol y del `seller_id` en la venta → módulos **Auth & Tenancy** y **POS**.

## Criterios de aceptación
- [ ] Un admin puede dar de alta un empleado y vincularlo (o no) a un `User`.
- [ ] Al completar una venta se genera la comisión del vendedor según su esquema.
- [ ] `amount = base_amount × rate` se calcula server-side sobre la base configurada.
- [ ] Liquidar un período marca las comisiones como `liquidada` y las vuelve inmutables.
- [ ] El cumplimiento de objetivo se muestra calculado sobre las ventas del período.
- [ ] Un `vendedor` ve solo su propio desempeño, no el de otros (403 al resto).
- [ ] Un tenant nunca ve empleados/comisiones de otro tenant (test de aislamiento).

## Notas / preguntas abiertas
- ¿Esquema de comisión: % general, por categoría/producto, o escalonado por objetivo?
- ¿`base_amount` = total de la venta o margen (venta − costo)?
- ¿Los turnos deben controlar la apertura de caja, o quedan solo informativos?
