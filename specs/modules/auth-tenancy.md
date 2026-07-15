# Módulo: Auth & Tenancy (autenticación multi-tenant)

- **Fase:** 0 (base construida) + **completar en Fase 0.5** (huecos abajo)
- **Estado:** 🏗️ parcial — el scaffold de tenancy existe; el flujo de auth
  multi-tenant NO está completo (ver §Huecos).
- **Pantalla(s) del design:** Login / shell (el panel de plataforma y la pantalla de Equipo no están en el handoff — a diseñar con los tokens de Vendi).
- **Depende de:** —

## Propósito
Autenticar usuarios y aislar los datos de cada comercio (tenant). Cada usuario
pertenece a un tenant; nunca puede ver ni tocar datos de otro. Define cómo se da
de alta un comercio nuevo, cómo el comercio gestiona sus empleados, y qué puede
hacer cada rol.

## Modelo decidido (2026-07): SaaS vendido, dos niveles de administración
Vendi se **vende**; no hay registro público. Dos niveles:

- **Plataforma (Vendi / dueños):** usuarios **super-admin** (`tenant_id = null`,
  rol `super-admin`). Tienen un **panel de plataforma** para gestionar a sus
  comercios cliente: listar, **crear comercio + su primer admin**, activar/suspender.
- **Comercio (cada cliente):** el **admin** del comercio usa la app (POS, catálogo,
  etc.) y da de **alta directa** a sus **empleados** (`vendedor`): email + password
  temporal, sin infra de email. El `vendedor` opera pero no configura.

**Login único, ruteo por rol:** super-admin → `/plataforma`; usuario con tenant →
`/dashboard`; usuario sin tenant que no es super-admin → **bloqueado**.
**El registro público (signup del kit) se elimina.**

## Estado actual (construido en Fase 0)
- **Modelos:** `Tenant` (name, slug, settings JSON), `User` con `tenant_id`
  (nullable) + relación `tenant()` + `HasRoles` (spatie).
- **Aislamiento:** trait `BelongsToTenant` (global scope `TenantScope` +
  autocompleta `tenant_id` al crear) · `CurrentTenant` singleton ·
  middleware `SetCurrentTenant` (resuelve el tenant del usuario logueado).
- **Auth:** Laravel Fortify (login, registro, reset de password, verificación de
  email, 2FA, passkeys) — viene del starter kit.
- **Roles:** `admin` y `vendedor` seedeados (spatie). Tenant demo + admin en el seeder.
- **Tests:** aislamiento del scope y resolución del tenant (`TenantScopeTest`, `TenantcyTest`).

## Huecos a completar (Fase 0.5 — lo que falta para el esqueleto sólido) ⚠️
1. **Eliminar el registro público** (signup del kit / feature de Fortify).
2. **Rol y usuarios super-admin** (`super-admin`, `tenant_id = null`), seedeados
   (los dueños de Vendi).
3. **Panel de plataforma** (`/plataforma`): listar comercios, **crear comercio +
   su primer admin** (con settings/módulos MVP por defecto), activar/suspender.
   Solo super-admin.
4. **Alta directa de empleados** dentro del comercio: el `admin` crea `vendedor`
   con email + password temporal (sin email). Scopeado a su tenant.
5. **Guard de acceso.** Usuario sin `tenant_id` y sin `super-admin` → bloqueado.
   Ruteo por rol tras login (super-admin → `/plataforma`, tenant → `/dashboard`).
6. **Roles aplicados en rutas.** `super-admin` (plataforma), `admin` (gestión del
   comercio), `vendedor` (operación). Middleware/policies que devuelvan 403.
7. **Estado del comercio** (`active`/`suspended`): un comercio suspendido no opera.

## Entidades y datos
- **Tenant** — `name` · `slug` (único) · `status` (`active`/`suspended`) · `settings: json` (accent_color, logo, etc.) · timestamps
- **User** — `tenant_id?` (null = super-admin plataforma) · `name` · `email` · `password` · (2FA/passkey del kit)
- **Role** — spatie. Roles: `super-admin` (plataforma), `admin` (comercio), `vendedor` (comercio).

## Reglas de negocio
1. Todo usuario con rol `admin`/`vendedor` **debe** tener `tenant_id`. `super-admin`
   tiene `tenant_id = null`.
2. El `TenantScope` filtra toda entidad de dominio por el tenant actual; **nunca**
   confiar en el front para el scoping.
3. **NO** aplicar el tenant scope al modelo `User` (auth resuelve el user antes de
   conocer el tenant). Las queries de usuarios se scopean explícitamente por `tenant_id`.
4. Un `super-admin` gestiona comercios en el panel de plataforma; **no** opera la app de un comercio.
5. Un `admin` gestiona su tenant (empleados, catálogo, config, precios). Un
   `vendedor` opera (POS, ver catálogo/stock, clientes) pero no configura ni edita catálogo.
6. Al crear un comercio se crea su **primer usuario `admin`**; el comercio nace `active`.
7. Un comercio `suspended` no puede operar (sus usuarios quedan bloqueados de la app).
8. El registro público **no existe**: usuarios de comercio los crea el super-admin
   (primer admin) o el admin del comercio (empleados).

## Acciones / endpoints
| Acción | Ruta / método | Rol | Estado |
|--------|---------------|-----|--------|
| Login / logout / reset / 2FA | (Fortify, del kit) | — | ✅ construido |
| Registro público | — | — | ❌ eliminado |
| Panel: listar comercios | `GET /plataforma` | super-admin | ⬜ Fase 0.5 |
| Panel: crear comercio + admin | `GET/POST /plataforma/comercios` | super-admin | ⬜ Fase 0.5 |
| Panel: activar/suspender comercio | `PATCH /plataforma/comercios/{t}/estado` | super-admin | ⬜ Fase 0.5 |
| Listar/crear empleados del comercio | `GET/POST /equipo` | admin | ⬜ Fase 0.5 |

## UI
Login: pantalla del kit re-temada a Vendi (ya andando), sin link a registro. A
construir: **panel de plataforma** (`/plataforma`: lista de comercios + crear
comercio con su admin + activar/suspender) y **Equipo** (`/equipo`: lista de
empleados del comercio + alta directa de `vendedor`). Ambas con tokens del design.

## Multi-tenant & permisos
- Scoping: todas las entidades de dominio → `BelongsToTenant`. `User` se scopea
  explícito por `tenant_id`.
- Roles: `super-admin` (plataforma) · `admin` (gestión comercio) · `vendedor` (operación).
  Aplicar por middleware (`role:...`) y ruteo por rol tras login.

## Fuera de alcance
- Subdominios por tenant (`bondi.vendi.app`) — futuro; se resuelve por usuario.
- Invitación por email + auto-registro de empleados — se usa alta directa (post-MVP: email).
- Facturación/billing de la plataforma a los comercios — post-MVP.
- SSO / login social — el kit tiene la variante WorkOS, no se usa.

## Criterios de aceptación
- [ ] No existe registro público (la ruta de signup no está disponible).
- [ ] Un `super-admin` puede crear un comercio + su primer `admin` desde `/plataforma`.
- [ ] Tras login, un `super-admin` va a `/plataforma` y un usuario de comercio a `/dashboard`.
- [ ] Un usuario autenticado sin `tenant_id` y sin `super-admin` no accede a la app.
- [ ] Un `admin` puede crear un `vendedor`; el nuevo usuario queda en el **mismo tenant**.
- [ ] Un `vendedor` recibe 403 en rutas de `admin` (`/equipo`) y de plataforma (`/plataforma`).
- [ ] Un usuario de un comercio `suspended` no puede operar la app.
- [ ] Un usuario del tenant A nunca ve usuarios/datos del tenant B (test de aislamiento).

## Notas / preguntas abiertas
- ✅ RESUELTO: sin registro público; super-admin crea comercios, admin crea empleados (alta directa).
- ¿Un usuario puede pertenecer a varios comercios? (MVP: no — 1 user, 1 tenant)
- ¿Verificación de email obligatoria antes de operar? (MVP: relajada — mail es log; el super-admin/admin setea la password)
- ¿El super-admin puede "entrar como" un comercio para soporte (impersonar)? (post-MVP)
