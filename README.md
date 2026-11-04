# Web Solar (Monitorización fotovoltaica)

Aplicación web para la monitorización y seguimiento de plantas fotovoltaicas. Permite visualizar datos en tiempo real, histórico de producción, alarmas de inversores, informes y gestión por usuario con control de acceso por sesión.

## Tecnologías utilizadas

### Backend
- **PHP 7.x/8.x** - Lógica de servidor y API REST-like
- **MySQL/MariaDB** - Base de datos principal `datalogger` y bases de datos mensuales `dataloggerYYYYMM`
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

La aplicación trabaja con dos tipos de bases de datos:

- **`datalogger`**: base de datos principal. Contiene usuarios, plantas, inversores, contadores, alarmas y el estado instantáneo de los inversores.
- **`dataloggerYYYYMM`**: una base de datos por mes (por ejemplo, `datalogger202606` para junio de 2026). Contiene las medidas históricas y las lecturas de los contadores de ese mes. Los endpoints construyen el nombre a partir del año y del mes solicitados.

A continuación se detalla la estructura inferida a partir del código fuente de los endpoints. Ajusta tipos/índices según tu instalación.

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
| `id` | VARCHAR(50) | NO | PK | Identificador de la planta (se usa en `usuarios_plantas.planta` y en el desplegable de plantas) |
| `nombre` | VARCHAR(150) | YES |  | Nombre descriptivo de la planta |
| `nombre_datos` | VARCHAR(150) | YES |  | Clave de planta usada en las tablas de medidas (`temporal`, `PAC`, `KDY`, `RAD`, `CONTADORES`, `CONT`, etc.) |
| `nombre_general` | VARCHAR(150) | YES |  | Nombre genérico utilizado en resúmenes y agrupaciones |
| `linea` | INT(11) | YES |  | Línea/agrupación asociada (se filtra por `0`/`1` en algunas consultas de radiación) |

### `inverters`
Catálogo de inversores por planta.

| Campo | Tipo | Nulo | Clave | Descripción |
|---|---|---|---|---|
| `idinverters` | INT(11) UNSIGNED | NO | PK, AI | Identificador único |
| `planta` | VARCHAR(50) | NO | IDX | Clave de planta (`plantas.nombre_datos`) |
| `linea` | INT(11) | YES |  | Línea asociada |
| `ct` | INT(11) | YES |  | Transformador/CT asociado |
| `inverter` | INT(11) | NO |  | Número/ID de inversor |
| `nombre` | VARCHAR(150) | YES |  | Nombre del inversor |
| `potencia_instalada` | DECIMAL(10,2) | YES |  | Potencia instalada (kW) |
| `potencia_pico` | DECIMAL(10,2) | YES |  | Potencia pico usada para calcular el rendimiento (`maxKDY / potencia_pico`) |
| `tipo` | VARCHAR(50) | YES |  | Tipo de inversor (para códigos de alarma) |

### `temporal`
Datos instantáneos/recientes de inversores (usado para vista en tiempo real).

| Campo | Tipo | Nulo | Clave | Descripción |
|---|---|---|---|---|
| `id` | BIGINT UNSIGNED | NO | PK, AI | Identificador |
| `planta` | VARCHAR(50) | NO | IDX | Clave de planta (`plantas.nombre_datos`) |
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
| `planta` | VARCHAR(50) | NO | IDX | Clave de planta (`plantas.nombre_datos`) |
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

### `usuarios_plantas`
Relación N:M entre usuarios y plantas; determina qué plantas puede ver cada usuario.

| Campo | Tipo | Nulo | Clave | Descripción |
|---|---|---|---|---|
| `usuario` | INT(11) UNSIGNED | NO | PK, FK | Usuario (`usuarios.idusuarios`) |
| `planta` | VARCHAR(50) | NO | PK, FK | Planta (`plantas.id`) |

> La clave primaria es compuesta (`usuario`, `planta`). Los administradores definidos en `API/config.php` ven todas las plantas sin necesidad de filas en esta tabla.

### `CONTADORES`
Catálogo de contadores eléctricos asociados a cada planta.

| Campo | Tipo | Nulo | Clave | Descripción |
|---|---|---|---|---|
| `planta` | VARCHAR(50) | NO | PK, IDX | Clave de planta (`plantas.nombre_datos`) |
| `numero` | INT(11) | NO | PK, IDX | Número identificador del contador |
| `nombre` | VARCHAR(150) | YES |  | Nombre descriptivo del contador |
| `direccion` | VARCHAR(100) | YES |  | Dirección/registro de comunicación del contador |
| `punto_medida` | VARCHAR(100) | YES |  | Punto de medida |
| `clave` | VARCHAR(100) | YES |  | Clave de acceso al contador |
| `IP_TEL` | VARCHAR(100) | YES |  | IP o teléfono utilizado para la comunicación |
| `pmax` | DECIMAL(10,2) | YES |  | Potencia máxima/contratada usada en los cálculos de Heq y PR |

### `CONTADORES_ERROR`
Registro de incidencias de comunicación o de lectura de los contadores.

| Campo | Tipo | Nulo | Clave | Descripción |
|---|---|---|---|---|
| `planta` | VARCHAR(50) | NO | PK, IDX | Clave de planta (`plantas.nombre_datos`) |
| `numero` | INT(11) | NO | PK, IDX | Contador (`CONTADORES.numero`) |
| `fecha` | VARCHAR(10) | NO | IDX | Fecha del error en formato `DD-MM-YYYY` (se compara con `DATE_FORMAT(CURDATE(),'%d-%m-%Y')`) |
| `tipo` | TINYINT(1) | NO | IDX | Tipo de incidencia: `0` = error de comunicación, `1` = lectura a cero |

### Bases de datos mensuales (`dataloggerYYYYMM`)

Cada mes se guarda en una base de datos independiente cuyo nombre se forma concatenando `datalogger` + año + mes. Los endpoints que calculan el mes a partir de una fecha usan dos dígitos (por ejemplo, `datalogger202606` para junio de 2026); comprueba que el nombre generado por cada endpoint coincida con la base de datos mensual creada. Las tablas no llevan prefijo de mes: el mes se selecciona cambiando de base de datos en la consulta.

#### `PAC`
Potencia activa instantánea por inversor.

| Campo | Tipo | Nulo | Clave | Descripción |
|---|---|---|---|---|
| `idinverter` | INT(11) UNSIGNED | NO | PK, FK | Inversor (`datalogger.inverters.idinverters`) |
| `inverter` | INT(11) | NO | IDX | Número de inversor |
| `planta` | VARCHAR(50) | NO | IDX | Clave de planta (`plantas.nombre_datos`) |
| `linea` | INT(11) | YES | IDX | Línea/CT |
| `fecha` | DATETIME | NO | PK, IDX | Fecha/hora de la medida |
| `valor` | DECIMAL(12,3) | YES |  | Potencia activa (unidad del datalogger; la interfaz la muestra en kW) |
| `fallo_com` | TINYINT(1) | YES | IDX | Marca de fallo de comunicación; los cálculos de ratio usan `fallo_com=0` |

#### `KDY`
Energía diaria acumulada por inversor.

| Campo | Tipo | Nulo | Clave | Descripción |
|---|---|---|---|---|
| `idinverter` | INT(11) UNSIGNED | NO | PK, FK | Inversor (`datalogger.inverters.idinverters`) |
| `inverter` | INT(11) | NO | IDX | Número de inversor |
| `planta` | VARCHAR(50) | NO | IDX | Clave de planta (`plantas.nombre_datos`) |
| `linea` | INT(11) | YES | IDX | Línea/CT |
| `fecha` | DATETIME | NO | PK, IDX | Fecha/hora de la medida |
| `valor` | DECIMAL(12,3) | YES |  | Energía diaria acumulada (kWh) |

#### Tablas de medidas por inversor (`IL1`, `IL2`, `IL3`, `IDC`, `TKK`, `UL1`, `UL2`, `UL3`, `UDC`, `UGD`, `KMT`, `TEMP`)

Estas tablas almacenan una única magnitud por fila y comparten la estructura básica de `KDY`: `idinverter`, `inverter`, `planta`, `linea`, `fecha` y `valor` (según la instalación pueden faltar `inverter` o `linea`). La tabla consultada se elige mediante el parámetro `valor` de los endpoints, validado contra una lista blanca.

| Tabla | Magnitud | Unidad mostrada en la interfaz |
|---|---|---|
| `IL1`, `IL2`, `IL3` | Corriente de fase L1/L2/L3 | A |
| `IDC` | Corriente de continua | A |
| `TKK` | Temperatura de la unidad de potencia | °C |
| `UL1`, `UL2`, `UL3` | Tensión de fase L1/L2/L3 | V |
| `UDC` | Tensión de continua | V |
| `UGD` | Tensión Ugnd | V |
| `KMT` | Energía mensual acumulada | kWh |
| `TEMP` | Temperatura | °C |

#### `RAD`, `TMP_PL`, `PIR` y `VIE`
Tablas de medidas ambientales por planta. Comparten la estructura mínima `planta`, `fecha` y `valor`.

| Tabla | Descripción | Unidad |
|---|---|---|
| `RAD` | Radiación solar | W/m² |
| `TMP_PL` | Temperatura de placa | °C |
| `PIR` | Piranómetro | W/m² |
| `VIE` | Velocidad del viento | km/h |

#### `CONT`
Lecturas de los contadores eléctricos.

| Campo | Tipo | Nulo | Clave | Descripción |
|---|---|---|---|---|
| `Planta` | VARCHAR(50) | NO | PK, FK | Clave de planta (`plantas.nombre_datos`) |
| `Numero` | INT(11) | NO | PK, FK | Contador (`CONTADORES.numero`) |
| `Fecha` | DATE | NO | PK, IDX | Fecha de la lectura |
| `Hora` | TIME | NO | PK, IDX | Hora de la lectura |
| `Activa_I` | DECIMAL(12,3) | YES |  | Energía activa importada (kWh) |
| `Activa_E` | DECIMAL(12,3) | YES |  | Energía activa exportada (kWh) |
| `Reactiva_Q1` | DECIMAL(12,3) | YES |  | Energía reactiva Q1 (kVArh) |
| `Reactiva_Q2` | DECIMAL(12,3) | YES |  | Energía reactiva Q2 (kVArh) |
| `Reactiva_Q3` | DECIMAL(12,3) | YES |  | Energía reactiva Q3 (kVArh) |
| `Reactiva_Q4` | DECIMAL(12,3) | YES |  | Energía reactiva Q4 (kVArh) |

### Relaciones principales

- `usuarios_plantas.usuario` → `usuarios.idusuarios`
- `usuarios_plantas.planta` → `plantas.id`
- `inverters.planta` → `plantas.nombre_datos`
- `temporal` (`planta`, `linea`, `inverter`) → `inverters` (`planta`, `linea`, `inverter`)
- `alarmas` (`planta`, `linea`, `inverter`) → `inverters` (`planta`, `linea`, `inverter`)
- `alarmas.SAL` + `inverters.tipo` → `codigos_alarmas.codigo` + `codigos_alarmas.tipo_inverter`
- `PAC.idinverter`, `KDY.idinverter` y las tablas de medidas por inversor → `inverters.idinverters`
- `CONTADORES.planta`, `PAC.planta`, `KDY.planta`, `CONT.Planta`, `RAD.planta`, `TMP_PL.planta`, `PIR.planta` y `VIE.planta` → `plantas.nombre_datos`
- `CONT` (`Planta`, `Numero`) → `CONTADORES` (`planta`, `numero`)
- `CONTADORES_ERROR` (`planta`, `numero`) → `CONTADORES` (`planta`, `numero`)

> **Notas:** No existe una tabla `informes`: los informes de energía se calculan a partir de `KDY` y de las lecturas de `CONT`. Los nombres de las tablas de las bases de datos mensuales se usan directamente en las consultas, por lo que deben coincidir exactamente con los esperados por la API. Se recomienda crear índices en los campos usados en `JOIN`, `WHERE` y `ORDER BY` (`planta`, `fecha`, `inverter`, `idinverter`, `linea`, `numero`, `activa`) para mejorar el rendimiento.
