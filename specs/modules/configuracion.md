# Módulo: Configuración / Customización

- **Fase:** 6
- **Estado:** 📝 en definición
- **Pantalla(s) del design:** Configuración (ver `design_handoff_vendi_pos/README.md` §8)
- **Depende de:** Auth & Tenancy (Fase 0), Productos (Fase 1)

## Propósito
Es **el diferenciador del producto**: cada comercio adapta Vendi sin forks. Desde una
sola pantalla el admin prende/apaga módulos, define su marca (white-label), agrega
campos personalizados, carga sus datos fiscales y gestiona a su equipo. Materializa
las capas de customización del plan técnico (§3): feature flags, campos JSONB,
branding en vivo, reglas por tenant.

## Entidades y datos

- **tenant_modules** — `tenant_id` · `module` (enum: `facturacion`, `ecommerce`,
  `produccion`, `promociones`, `empleados`) · `enabled: bool` · timestamps. Único
  por `(tenant_id, module)`.
- **custom_field_definitions** — `tenant_id` · `entity` (enum: `producto`, `cliente`) ·
  `label` · `type` (enum: `texto`, `numero`, `fecha`, `lista`) · `options: json?`
  (para `lista`) · `order: int`.
- **Marca / datos fiscales** → viven en `tenants.settings` (JSONB, ya cableado en
  Fase 0), **no** en tabla aparte: `store_name`, `logo_path?`, `accent_color`
  (default `#6C4DF2`), `fiscal.condicion` (`monotributo`/`responsable_inscripto`),
  `fiscal.cuit`, `fiscal.punto_venta`, `fiscal.ingresos_brutos?`.

> `tenant_modules` y `custom_field_definitions` usan `BelongsToTenant`. El branding
> y los datos fiscales se leen/escriben sobre `tenants.settings` del tenant actual.

## Reglas de negocio
1. Solo `admin` accede a Configuración; `vendedor` recibe 403 en toda la sección.
2. El **toggle de módulo es un feature flag**: gatea el módulo en back (gate) y en
   front (condicional de nav/pantalla). No implementa el módulo — E-commerce,
   Producción, Promociones, Empleados están **diferidos**; prender el flag solo
   habilita el punto de extensión, no una feature construida.
3. Cambiar `accent_color` **re-tematiza la app en vivo** (se comparte por Inertia y
   lo aplica `apply-tenant-accent`); el default violeta no debe pisar el `--primary`
   del dark theme.
4. Los **campos personalizados** se *definen* acá (`custom_field_definitions`); sus
   *valores* los guarda cada entidad en su JSONB (`products.custom_fields`, etc. — ver
   Productos). Borrar una definición no borra valores históricos ya cargados.
5. `label` de un campo personalizado es único por `(tenant, entity)`.
6. Los **datos fiscales son visuales** en MVP: se persisten pero no emiten nada. La
   emisión real queda detrás de `FiscalDocumentIssuer` (`NullIssuer` hoy) → ver la
   spec diferida de AFIP.
7. La **gestión de usuarios del tenant** (invitar vendedor, listar, roles) vive en
   esta pantalla pero la lógica es de **Auth & Tenancy** (Invitation, `invitar`) — acá
   solo se referencia, no se re-especifica.

## Acciones / endpoints

| Acción | Ruta / método | Rol | Descripción |
|--------|---------------|-----|-------------|
| Ver configuración | `GET /configuracion` | admin | Tabs: Módulos · Marca · Campos · Fiscal · Usuarios |
| Toggle módulo | `PATCH /configuracion/modulos/{module}` | admin | Prende/apaga feature flag |
| Guardar marca | `PUT /configuracion/marca` | admin | `store_name`, `logo`, `accent_color` |
| Crear campo personalizado | `POST /configuracion/campos` | admin | entidad · label · tipo |
| Borrar campo personalizado | `DELETE /configuracion/campos/{def}` | admin | No borra valores históricos |
| Guardar datos fiscales | `PUT /configuracion/fiscal` | admin | Condición AFIP, CUIT, punto de venta |
| Invitar / gestionar usuarios | (ver Auth & Tenancy) | admin | Referencia, no re-spec |

## UI
Handoff §8: cards por sección. **Módulos**: filas label + descripción + pill toggle
(track violeta al encender). **Marca**: input nombre, tile de logo, swatch picker de
acento (Esmeralda/Violeta/Azul/Naranja/Magenta, default Violeta) + botón verde
"Compartir catálogo" (WhatsApp, ver ese módulo). **Campos personalizados**: lista
editor (badge entidad · label · tipo) con alta/baja. **Datos fiscales**: Condición
AFIP, CUIT, Punto de venta, Ingresos Brutos + "Guardar cambios" (violeta).
Componentes shadcn: card, switch, input, select, badge, dialog. Color de swatch vía
tokens de `app.css`, nunca hex hardcodeado.

## Multi-tenant & permisos
- Scoping: `TenantModule`, `CustomFieldDefinition` → `BelongsToTenant`. Marca/fiscal
  sobre `tenants.settings` del tenant actual.
- Roles: solo `admin`. `vendedor` no ve ni toca Configuración.

## Fuera de alcance
- Implementación real de los módulos diferidos que el flag habilita (E-commerce,
  Producción, Promociones, Empleados) → sus specs futuras.
- Emisión fiscal / AFIP (CAE, facturas) → spec diferida detrás de `FiscalDocumentIssuer`.
- Guardado de *valores* de campos personalizados → cada entidad de dominio (Productos, Clientes).
- Lógica de invitación/alta de usuarios → módulo **Auth & Tenancy**.

## Criterios de aceptación
- [ ] Un `admin` prende/apaga un módulo y el flag persiste en `tenant_modules`.
- [ ] Cambiar `accent_color` actualiza `tenants.settings` y se refleja en el front (accent en vivo).
- [ ] Un `admin` crea un campo personalizado (entidad·label·tipo) y aparece en la ficha de esa entidad.
- [ ] Un `label` de campo duplicado dentro de `(tenant, entity)` es rechazado.
- [ ] Borrar una definición de campo no borra los valores ya cargados en las entidades.
- [ ] Los datos fiscales se guardan pero **no** disparan emisión (issuer = `NullIssuer`).
- [ ] Un `vendedor` recibe 403 en `/configuracion` y sus sub-rutas.
- [ ] Un tenant nunca ve los módulos/campos de otro tenant (test de aislamiento).

## Notas / preguntas abiertas
- ¿El logo se sube como archivo (storage) o se referencia URL en MVP?
- ¿La paleta de acentos es fija (5 presets) o color libre? (el design muestra 5 presets)
- ¿Prender un flag de módulo diferido debe mostrar un estado "próximamente" o directamente ocultarse hasta construirlo?
