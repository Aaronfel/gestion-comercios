# Módulo: <Nombre>

- **Fase:** <0–6 / diferido>
- **Estado:** ⬜ pendiente | 📝 en definición | 🏗️ en implementación | ✅ hecho
- **Pantalla(s) del design:** <ej. Productos · Ficha> (ver `design_handoff_vendi_pos/`)
- **Depende de:** <módulos previos>

## Propósito
<1–3 frases: qué problema resuelve para el comerciante, por qué existe.>

## Entidades y datos
<Tablas/modelos y sus campos. Marcá tipos, nullables, y qué lleva `tenant_id`.>

- **<Entidad>** — `campo: tipo` · `campo: tipo` · …

## Reglas de negocio
<Reglas invariables. Cálculos (server-side). Validaciones. Estados y transiciones.>

1. …

## Acciones / endpoints
<Rutas Inertia / controllers / acciones. Qué recibe, qué devuelve, permiso requerido.>

| Acción | Ruta / método | Rol | Descripción |
|--------|---------------|-----|-------------|
| … | … | … | … |

## UI
<Referencia a la pantalla del handoff. Componentes shadcn a usar. Estados vacíos,
loading, errores. Tokens/gradiente hero si aplica.>

## Multi-tenant & permisos
- Scoping: <modelos que usan `BelongsToTenant`>.
- Roles: <quién puede qué (admin / vendedor)>.

## Fuera de alcance
<Lo que este módulo NO hace (para no bleedear a otros).>

## Criterios de aceptación
<Checklist verificable. Cada uno debería mapear a un test Pest.>

- [ ] …
- [ ] …

## Notas / preguntas abiertas
<Dudas a resolver con el socio antes de implementar.>
