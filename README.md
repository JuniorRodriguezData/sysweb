# SysWeb – Sistema Web de Compras, Ventas y Stock

Proyecto integrador de **Lenguaje de Programación III** – Universidad Tecnológica Intercontinental (UTIC).
Desarrollado por **Junior Rodriguez** en PHP y MySQL sobre la plantilla AdminLTE (Bootstrap 3), siguiendo las
etapas del proyecto de la cátedra.

## Etapas implementadas

| Etapa | Contenido | Archivos principales |
|---|---|---|
| **Proyecto 1** – Base de datos y acceso | Base de datos `sysweb` y tablas; login y su proceso | `sql/sysweb.sql`, `index.php`, `login-check.php` |
| **Proyecto 2** – Estructura y navegación | Menú superior, menú lateral, salir del sistema y footer | `main.php`, `top-menu.php`, `sidebar-menu.php`, `logout.php` |
| **Proyecto 3** – Contenido y contraseña | `content.php`, pantalla de inicio y cambio de contraseña | `content.php`, `modules/start/`, `modules/password/` |
| **Proyecto 4** – Gestión de usuarios | Tabla de usuarios: agregar, editar, activar y bloquear | `modules/user/view.php`, `form.php`, `proses.php` |
| **Proyecto 5** – Perfil y niveles | Ver y modificar el perfil (con foto) y menú según el nivel de usuario | `modules/profile/`, `sidebar-menu.php`, `config/funciones.php` |
| **Proyecto 6** – Datos referenciales | Referencial Departamento: tabla de datos, agregar, modificar (y eliminar si no tiene ciudades) | `modules/departamento/` |
| **Proyecto 7** – Referencial Ciudad | Vista, formulario para agregar y editar, y eliminación verificada | `modules/ciudad/` |
| **Proyecto 8** – Reportes PDF | Composer + librería HTML2PDF; botón **Imprimir** que genera el PDF | `composer.json`, `config/reporte.php`, `modules/*/reporte.php` |
| **Proyecto 9** – Clientes | Lista, agregar, modificar, eliminar y reporte PDF de clientes | `modules/clientes/` |

## Niveles de usuario

| Nivel | Puede usar |
|---|---|
| **Super Admin** | Todo: referenciales generales (departamento, ciudad), de compras y de ventas, y la administración de usuarios |
| **Compras** | Referenciales de compras, compras y stock |
| **Ventas** | Referenciales de ventas (clientes y su reporte), ventas y stock |

Todos pueden ver y modificar su perfil y cambiar su contraseña. Los permisos están definidos en
`config/funciones.php` (`PERMISOS_MODULOS`): el menú se arma con ellos y **`content.php` y cada `proses.php` los
vuelven a verificar en el servidor**, porque ocultar una opción del menú no es suficiente como seguridad.

## Seguridad aplicada (Unidades IV y V)

- **Contraseñas con `password_hash()` / `password_verify()`** en lugar de MD5.
- **Sentencias preparadas** (`$mysqli->prepare()` + `bind_param()`) en todas las consultas con datos del usuario:
  sin riesgo de inyección SQL.
- **Sesión segura** (`config/session.php`): cookie `httponly` y `samesite`, modo estricto y
  `session_regenerate_id(true)` después del login.
- **Token CSRF** en todos los formularios que modifican datos; activar/bloquear usuarios se hace por POST.
- **Salida escapada** con `htmlspecialchars()` (función `e()`) para evitar XSS.
- **Subida de fotos segura**: se verifica el contenido real de la imagen (JPG/PNG), tamaño máximo 1 MB y nombre
  de archivo único generado por el sistema.
- **Errores**: excepciones de mysqli registradas en `logs/error.log`; el usuario solo ve un mensaje genérico.
- Un usuario bloqueado no puede ingresar y el administrador no puede bloquearse a sí mismo.
- **Eliminación controlada**: antes de borrar se verifica que el registro exista y que el usuario tenga permiso; no se
  elimina un departamento con ciudades, una ciudad con clientes ni un cliente con ventas (integridad referencial).
- **Reportes PDF** protegidos: solo los genera un usuario con sesión y permiso sobre el módulo.

## Requisitos

- XAMPP (PHP 8.x y MariaDB/MySQL) o WAMP.
- Extensión **gd** de PHP activada (en `C:\xampp\php\php.ini`, quitar el `;` de `;extension=gd`): la necesita HTML2PDF.
- [Composer](https://getcomposer.org/) para instalar la librería de reportes.

## Instalación

1. Copiar la carpeta del proyecto (por ejemplo en `C:\xampp\htdocs\sysweb`).
2. Instalar las dependencias (HTML2PDF) desde la carpeta del proyecto:
   ```
   composer install
   ```
3. Crear la base de datos:
   ```
   C:\xampp\mysql\bin\mysql.exe -u root --default-character-set=utf8mb4 < sql\sysweb.sql
   ```
   o importar `sql/sysweb.sql` desde phpMyAdmin.
4. Si hace falta, ajustar usuario y contraseña de MySQL en `config/database.php`.
5. Abrir `http://localhost/sysweb/` (o, sin Apache, ejecutar `ABRIR_PROGRAMA.bat`, que usa el servidor de PHP en
   `http://localhost:8070`).

## Usuarios de prueba

| Usuario | Contraseña | Nivel |
|---|---|---|
| `admin` | `Admin2026` | Super Admin |
| `compras` | `Compras2026` | Compras |
| `ventas` | `Ventas2026` | Ventas |

## Estructura

```
sysweb/
├── index.php            Login
├── login-check.php      Proceso del login
├── main.php             Plantilla principal (menús, contenido y footer)
├── top-menu.php         Menú superior (usuario, perfil, salir)
├── sidebar-menu.php     Menú lateral según el nivel
├── content.php          Carga del módulo pedido con control de permisos
├── logout.php           Salir del sistema
├── config/              Conexión, sesión segura, funciones comunes y reportes PDF
├── composer.json        Dependencias (spipu/html2pdf)
├── modules/
│   ├── start/           Inicio
│   ├── password/        Cambio de contraseña
│   ├── user/            Gestión de usuarios
│   ├── profile/         Perfil del usuario
│   ├── departamento/    Referencial Departamento (+ reporte PDF)
│   ├── ciudad/          Referencial Ciudad (+ reporte PDF)
│   └── clientes/        Referencial Clientes (+ reporte PDF)
├── sql/sysweb.sql       Base de datos
├── assets/              CSS, JS y plugins (AdminLTE, Bootstrap, DataTables…)
├── images/user/         Fotos de los usuarios
└── logs/                Registro de errores
```

## Próximas etapas

Referenciales de compras (depósito, proveedor, producto, unidad de medida) y los movimientos de compras, ventas y
stock, sobre las tablas que ya están creadas en `sql/sysweb.sql`.
