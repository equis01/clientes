# Portal Clientes — Medios con Valor

Plataforma web para clientes y equipo de Medios con Valor. Incluye portal de clientes, administración, generación de reportes y consulta de servicios/finanzas.

## Requisitos

- PHP 8.1+ con `curl` y `sqlite3` habilitados
- Acceso a los Web Apps de Google Apps Script utilizados por el portal

## Ejecución local

1. Configura variables en `.env` (ver sección Variables).  
2. Ejecuta servidor embebido de PHP:

```
php -S localhost:8000 -t . config/router.php
```

3. Abre `http://localhost:8000`.

## Estructura

- `views/layout/` cabecera, pie, loader y componentes comunes
- `views/pages/` páginas del portal (clientes y administración)
- `config/` ruteo y autenticación
- `lib/` utilidades: llamados a GAS, BD y helpers
- `assets/` CSS/JS e imágenes

## Variables de entorno

- `TIMEZONE` zona horaria (ej. `America/Mexico_City`)
- `GAS_EXEC_URL_BD_QRO` Web App principal (BD/reportes)
- `GAS_EXEC_URL_REPORTES` Web App de reportes (opcional)
- `GAS_USERS_Q_URL` Web App de usuarios (Q y no‑Q)
- `GAS_USERS_OTHER_URL` Web App de autenticación para no‑Q
- `GAS_ADMINS_URL` Web App de administración (altas/ediciones)
- `GAS_CHANGE_PASS_URL` Web App para cambio de contraseña
- `FIREBASE_API_KEY` API key si se usa login admin vía Firebase

## Autenticación

- Clientes Q: validación contra `GAS_USERS_Q_URL` (usuario/contraseña, alias, drive).  
- Clientes no‑Q: autenticación contra `GAS_USERS_OTHER_URL`; la ficha (alias/email/drive) se consulta en `GAS_USERS_Q_URL` para homogeneidad.  
- Admin: correo corporativo `@mediosconvalor.com` (Firebase o archivo `data/admin.json`).

## UI y comportamiento

- Loader global verde (incluido desde `header.php`).  
- Menú adaptable con overlay en móvil.  
- Bienvenida inicial con mensajes distintos por tipo de usuario.  
- Portal: acceso a carpeta de Drive y aviso sobre cuentas Google.  
- Servicios: filtro por periodo y generación de reportes.

## Reglas de reportes

- El reporte del mes anterior solo puede generarse a partir del día 4 del mes actual.

## Administración

- Secciones: Clientes, Invitaciones, Configuración, Super.  
- Gestión de usuarios vía `GAS_ADMINS_URL`.

## Desarrollo

- Estilos principales en `assets/css/app.css` (modo claro/oscuro).  
- Lógica de autenticación en `config/auth.php`.  
- Integraciones GAS en `lib/gas.php`.

## Notas

- No almacenar secretos en el repositorio.  
- Mantener URLs de Web Apps en `.env`.

