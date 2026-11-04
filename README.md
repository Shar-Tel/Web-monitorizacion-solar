# Web Solar (Monitorización fotovoltaica)

Aplicación web para la monitorización y seguimiento de plantas fotovoltaicas. Permite visualizar datos en tiempo real, histórico de producción, alarmas de inversores, informes y gestión por usuario con control de acceso por sesión.

## Tecnologías utilizadas

### Backend
- **PHP 7.x/8.x** - Lógica de servidor y API REST-like
- **MySQL/MariaDB** - Base de datos principal (esquema `datalogger`)
- **Sessions PHP** - Autenticación con cookies `HttpOnly`, `SameSite=Lax` y `Secure` si hay HTTPS
- **mysqli** - Conexión y consultas preparadas

### Frontend
- **HTML5, CSS3, JavaScript** - Estructura, estilos y lógica cliente
- **jQuery** - Manipulación DOM, AJAX y utilidades
- **Bootstrap (3.x)** - Framework CSS
- **Feather Icons** - Iconografía
- **Bootstrap Datepicker (es)** - Selección de fechas
- **jquery.table2excel.js** - Exportación de tablas a Excel
- **jquery.md5.min.js** - Utilidades para MD5 (compatibilidad heredada)

### Seguridad (implementada)
- Cabeceras defensivas (`X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy`, `Permissions-Policy`, `Content-Security-Policy`, `Strict-Transport-Security` si HTTPS)
- Protección CSRF básica para peticiones AJAX (`X-Requested-With: XMLHttpRequest`)
- Autenticación por sesión con regeneración de ID al login
- Rate limiting por intentos de login (5 intentos / 60 segundos)
- Consultas preparadas en endpoints críticos
- Migración automática de contraseñas MD5 a `password_hash()` (bcrypt/Argon2) al iniciar sesión
- Bloqueo de listados de directorio (`Options -Indexes`)
- Protección de ficheros sensibles vía `.htaccess`

## Estructura del proyecto

```
Web solar/
├── API/                        # Endpoints PHP de la API
│   ├── auth.php               # Middleware de autenticación
│   ├── seguridad.php          # Helpers de validación, sesiones y cabeceras
│   ├── database_connection.php# Conexión a BD
│   ├── db_config.php          # Credenciales (NO subir a repos públicos)
│   ├── config.php             # Configuración general (usuarios admin)
│   └── consulta_*.php         # Distintos endpoints de consulta/actualización
├── index.html                 # Página de login
├── principal.html             # Dashboard principal
├── login.php                  # Endpoint de autenticación
├── *.js                       # Lógica frontend (general.js, inverters.js, informes.js, alarmas.js, ...)
├── *.css                      # Estilos
├── .htaccess                  # Reglas de seguridad Apache
└── README.md                  # Este archivo
```

## Configuración

1. Copia/edita `API/db_config.php` con las credenciales de tu base de datos:

```php
<?php
return array(
    'host'       => '127.0.0.1',
    'usuario'    => 'tu_usuario',
    'password'   => 'tu_contraseña',
    'base_datos' => 'datalogger',
);
```

2. Configura `API/config.php` si deseas marcar usuarios como administradores:

```php
<?php
$USUARIOS_ADMIN = array(1); // IDs de usuarios con privilegios administrativos
```

3. Asegura que tu servidor web tenga PHP con extensiones `mysqli`, `session`, etc. Para producción se recomienda habilitar HTTPS.

## Estructura de la base de datos

La aplicación espera la base de datos `datalogger`. A continuación se detalla la estructura inferida a partir del código fuente. Ajusta tipos/índices según tu instalación.

### `usuarios`
Almacena credenciales y acceso de usuarios.

| Campo | Tipo | Nulo | Clave | Descripción |
|---|---|---|---|---|
| `idusuarios` | INT(11) UNSIGNED / INT | NO | PK, AI | Identificador único del usuario |
| `login` | VARCHAR(100) | NO | UNIQUE | Nombre de usuario para login |
| `password` | VARCHAR(255) | NO |  | Hash de contraseña (`password_hash()`) o MD5 legado (migrado automáticamente) |

### `plantas`
Información de las plantas fotovoltaicas.

| Campo | Tipo | Nulo | Clave | Descripción |
|---|---|---|---|---|
| `id` | VARCHAR(50) | NO | PK | Identificador de la planta |
| `nombre` | VARCHAR(150) | YES |  | Nombre descriptivo de la planta |
| `nombre_datos` | VARCHAR(150) | YES |  | Nombre utilizado para visualización |
| `linea` | INT(11) | YES |  | Línea/agrupación asociada |

### `inverters`
Catálogo de inversores por planta.

| Campo | Tipo | Nulo | Clave | Descripción |
|---|---|---|---|---|
| `idinverters` | INT(11) UNSIGNED | NO | PK, AI | Identificador único |
| `planta` | VARCHAR(50) | NO | IDX | Relación con `plantas.id` |
| `linea` | INT(11) | YES |  | Línea asociada |
| `ct` | INT(11) | YES |  | Transformador/CT asociado |
| `inverter` | INT(11) | NO |  | Número/ID de inversor |
| `nombre` | VARCHAR(150) | YES |  | Nombre del inversor |
| `potencia_instalada` | DECIMAL(10,2) | YES |  | Potencia instalada (kW) |
| `tipo` | VARCHAR(50) | YES |  | Tipo de inversor (para códigos de alarma) |

### `temporal`
Datos instantáneos/recientes de inversores (usado para vista en tiempo real).

| Campo | Tipo | Nulo | Clave | Descripción |
|---|---|---|---|---|
| `id` | BIGINT UNSIGNED | NO | PK, AI | Identificador |
| `planta` | VARCHAR(50) | NO | IDX | Planta |
| `linea` | INT(11) | YES |  | Línea |
| `ct` | INT(11) | YES |  | CT |
| `inverter` | INT(11) | NO |  | Inversor |
| `fecha` | DATETIME/TIMESTAMP | NO | IDX | Fecha/hora de lectura |
| `PAC` | DECIMAL(12,2) | YES |  | Potencia activa (W) |
| `KDY` | DECIMAL(12,3) | YES |  | Energía diaria (kWh) |
| `KMT` | DECIMAL(12,3) | YES |  | Energía mensual (kWh) |

### `alarmas`
Registro de alarmas de inversores.

| Campo | Tipo | Nulo | Clave | Descripción |
|---|---|---|---|---|
| `idregistros` | BIGINT UNSIGNED | NO | PK, AI | Identificador único |
| `planta` | VARCHAR(50) | NO | IDX | Planta |
| `linea` | INT(11) | YES |  | Línea |
| `inverter` | INT(11) | NO |  | Inversor |
| `SAL` | VARCHAR(50) | YES |  | Código de alarma |
| `activa` | TINYINT(1) | NO | DEFAULT 1 | 1 = activa, 0 = finalizada |
| `fecha_inicio` | DATETIME | NO | IDX | Fecha/hora de inicio |
| `fecha_fin` | DATETIME | YES |  | Fecha/hora de finalización |

### `codigos_alarmas`
Descripción de códigos de alarma por tipo de inversor.

| Campo | Tipo | Nulo | Clave | Descripción |
|---|---|---|---|---|
| `id` | INT(11) UNSIGNED | NO | PK, AI | Identificador |
| `tipo_inverter` | VARCHAR(50) | NO | IDX | Tipo de inversor |
| `codigo` | VARCHAR(50) | NO | IDX | Código de alarma (`SAL`) |
| `error` | VARCHAR(255) | YES |  | Descripción del error |

### `lectura_instant`
Tabla de control para lectura instantánea de contadores.

| Campo | Tipo | Nulo | Clave | Descripción |
|---|---|---|---|---|
| `id` | INT(11) UNSIGNED | NO | PK, AI | Identificador |
| `PLANTA` | VARCHAR(50) | YES |  | Planta a leer |
| `NUMERO` | INT(11) | YES |  | Contador/número a leer |
| `RESPUESTA` | TEXT/LONGTEXT | YES |  | Respuesta/lectura almacenada |

> **Notas:** Existen otras tablas utilizadas por la aplicación (`contadores`, `informes`, datos agregados, etc.) según los endpoints. El esquema anterior cubre las consultas principales. Se recomienda crear índices en campos usados en `JOIN`, `WHERE` y `ORDER BY` (`planta`, `fecha`, `inverter`, `activa`) para mejorar el rendimiento.
