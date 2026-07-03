# Módulo: Auth & Tenancy (autenticación multi-tenant)

- **Fase:** 0 (base construida) + **completar en Fase 0.5** (huecos abajo)
- **Estado:** 🏗️ parcial — el scaffold de tenancy existe; el flujo de auth
  multi-tenant NO está completo (ver §Huecos).
- **Pantalla(s) del design:** Login / shell (no hay pantalla de onboarding/invitación en el handoff — a diseñar).
- **Depende de:** —

## Propósito
Autenticar usuarios y aislar los datos de cada comercio (tenant). Cada usuario
pertenece a un tenant; nunca puede ver ni tocar datos de otro. Define cómo se da
de alta un comercio nuevo, cómo se invita a empleados, y qué puede hacer cada rol.

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

## Huecos a completar (esto es lo que falta) ⚠️
1. **El registro no crea tenant.** El registro de Fortify crea un `User` con
   `tenant_id = null` → usuario huérfano sin comercio. Falta decidir e implementar:
   registro = **onboarding de un comercio nuevo** (crea Tenant + primer admin +
   settings/módulos por defecto) **o** registro público deshabilitado a favor de invitaciones.
2. **No hay onboarding de comercio.** Alta de un tenant nuevo (nombre, datos
   fiscales mínimos, accent por defecto, módulos MVP encendidos) + su primer admin.
3. **No hay invitación de usuarios.** Un admin no puede invitar a un `vendedor` a
   su tenant (email de invitación → alta con `tenant_id` del que invita + rol).
4. **Usuario sin tenant puede loguear.** Falta un guard: un user autenticado sin
   `tenant_id` (que no sea super-admin) no debe entrar a la app (redirige a
   onboarding o error). Hoy entraría con `CurrentTenant` vacío.
5. **Roles no se aplican en rutas.** spatie está instalado y los roles seedeados,
   pero ninguna ruta/policy distingue `admin` de `vendedor` todavía.
6. **No hay super-admin de plataforma.** `tenant_id` nullable lo permite, pero no
   hay área ni guard de plataforma (para soporte/gestión de tenants). Post-MVP, pero definir el hueco.

## Entidades y datos
- **Tenant** — `name` · `slug` (único) · `settings: json` (accent_color, logo, etc.) · timestamps
- **User** — `tenant_id?` (null = super-admin plataforma) · `name` · `email` · `password` · (2FA/passkey del kit)
- **Role / Permission** — spatie (`roles`, `permissions`, pivotes). Roles MVP: `admin`, `vendedor`.
- **Invitation** *(a crear)* — `tenant_id` · `email` · `role` · `token` · `expires_at` · `accepted_at?`

## Reglas de negocio
1. Todo usuario no-super-admin **debe** tener `tenant_id`.
2. El `TenantScope` filtra toda entidad de dominio por el tenant actual; **nunca**
   confiar en el front para el scoping.
3. **NO** aplicar el tenant scope al modelo `User` (auth resuelve el user antes de
   conocer el tenant). Las queries de usuarios se scopean explícitamente por `tenant_id`.
4. Un `admin` gestiona su tenant (productos, usuarios, config, precios). Un
   `vendedor` opera (POS, ver catálogo/stock, clientes) pero no configura ni edita catálogo.
5. Una invitación es de un solo uso, expira, y ata al invitado al `tenant_id` del que invita.
6. El primer usuario de un tenant (onboarding) es `admin`.

## Acciones / endpoints
| Acción | Ruta / método | Rol | Estado |
|--------|---------------|-----|--------|
| Login / logout / reset / 2FA | (Fortify, del kit) | — | ✅ construido |
| Registro → onboarding de comercio | `GET/POST /registro` | guest | ⬜ a definir/construir |
| Invitar usuario | `POST /configuracion/usuarios/invitar` | admin | ⬜ |
| Aceptar invitación | `GET/POST /invitacion/{token}` | guest | ⬜ |
| Listar/gestionar usuarios del tenant | `/configuracion/usuarios` | admin | ⬜ |

## UI
Login: pantalla del kit re-temada a Vendi (ya andando). A diseñar: pantalla de
**onboarding** (crear comercio) y **aceptar invitación**. Gestión de usuarios vive
dentro de Configuración (módulo aparte). Usar tokens del design.

## Multi-tenant & permisos
- Scoping: todas las entidades de dominio → `BelongsToTenant`. `User`/`Invitation`
  se scopean explícito por `tenant_id`.
- Roles: `admin` (gestión) vs `vendedor` (operación). Aplicar por middleware/policy.

## Fuera de alcance
- Subdominios por tenant (`bondi.vendi.app`) — futuro; MVP resuelve por usuario.
- Área de super-admin de plataforma — post-MVP (dejar el hueco).
- SSO / login social — el kit tiene la variante WorkOS, no se usa en MVP.

## Criterios de aceptación
- [ ] Registrarse crea un **Tenant nuevo** + su primer usuario `admin` con `tenant_id`.
- [ ] Un usuario autenticado **sin** `tenant_id` no accede a la app (redirige a onboarding).
- [ ] Un `admin` puede invitar a un `vendedor`; el invitado queda en el **mismo tenant**.
- [ ] Una invitación usada o vencida no puede reutilizarse.
- [ ] Una ruta de `admin` devuelve 403 a un `vendedor` (roles aplicados).
- [ ] Un usuario del tenant A nunca ve usuarios/datos del tenant B (test de aislamiento).

## Notas / preguntas abiertas
- ¿Registro público (self-service onboarding) o alta manual + invitaciones? (afecta go-to-market)
- ¿Un usuario puede pertenecer a varios comercios? (MVP: no — 1 user, 1 tenant)
- ¿Verificación de email obligatoria antes de operar? (hoy `verified` está en las rutas)
