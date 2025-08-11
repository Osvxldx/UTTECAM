# Gestor de Citas Médicas

Sistema web para la gestión de citas médicas desarrollado en PHP con MySQL.

## Características

- ✅ Sistema de autenticación seguro
- ✅ Gestión de citas médicas
- ✅ Panel de administración
- ✅ Calendario de citas
- ✅ Bloqueo de días sin servicio
- ✅ Exportación a Excel y PDF
- ✅ Notificaciones por email
- ✅ Interfaz responsive con Tailwind CSS

## Requisitos del Sistema

- PHP 7.4 o superior
- MySQL 5.7 o MariaDB 10.2 o superior
- Servidor web (Apache/Nginx)
- Extensiones PHP: PDO, PDO_MySQL, mbstring

## Instalación

### 1. Configurar la Base de Datos

1. Crear una base de datos MySQL llamada `gestor_citas`
2. Importar el archivo `database.sql` en tu base de datos
3. Verificar que las tablas se crearon correctamente

### 2. Configurar la Aplicación

1. Editar `config/database.php` con tus credenciales de base de datos:
   ```php
   private $host = 'localhost';
   private $db_name = 'gestor_citas';
   private $username = 'tu_usuario';
   private $password = 'tu_contraseña';
   ```

2. Configurar el timezone en `config/config.php` si es necesario:
   ```php
   date_default_timezone_set('America/Mexico_City');
   ```

### 3. Verificar la Instalación

1. Acceder a `test_connection.php` para verificar la conexión a la base de datos
2. Eliminar `test_connection.php` después de las pruebas

### 4. Acceder al Sistema

- **URL del sistema**: `http://tu-dominio/`
- **Usuario Admin**: `admin@clinica.com` / `admin123`
- **Usuario Cliente**: `juan@email.com` / `admin123`

## Estructura del Proyecto

```
UTTECAM/
├── admin/              # Panel de administración
├── api/                # API REST para citas
├── assets/             # CSS, JS e imágenes
├── cliente/            # Área de clientes
├── config/             # Configuración del sistema
├── includes/           # Clases y funciones principales
├── database.sql        # Script de base de datos
└── README.md           # Este archivo
```

## Solución de Problemas Comunes

### Error de Conexión a la Base de Datos

1. Verificar credenciales en `config/database.php`
2. Asegurar que MySQL esté ejecutándose
3. Verificar que la base de datos `gestor_citas` exista
4. Ejecutar `test_connection.php` para diagnosticar

### Error de Sesión

1. Verificar permisos de escritura en el directorio temporal
2. Asegurar que las cookies estén habilitadas
3. Verificar configuración de PHP en `php.ini`

### Error de Permisos

1. Verificar que el usuario de la base de datos tenga permisos completos en `gestor_citas`
2. Asegurar permisos de lectura/escritura en directorios de uploads

### Problemas de Email

1. Verificar configuración SMTP en `config/config.php`
2. Asegurar que la función `mail()` esté habilitada en PHP
3. Verificar logs del servidor de correo

## Seguridad

- ✅ Tokens CSRF en todos los formularios
- ✅ Validación y sanitización de entradas
- ✅ Prepared statements para prevenir SQL injection
- ✅ Control de acceso basado en roles
- ✅ Sesiones seguras con cookies HttpOnly

## Personalización

### Cambiar Información del Médico

Editar `config/config.php`:
```php
define('DOCTOR_NAME', 'Dr. Tu Nombre');
define('DOCTOR_SPECIALTY', 'Tu Especialidad');
define('DOCTOR_ADDRESS', 'Tu Dirección');
```

### Cambiar Horarios por Defecto

```php
define('DEFAULT_OPEN_TIME', '09:00');
define('DEFAULT_CLOSE_TIME', '18:00');
```

### Cambiar Configuración de Email

```php
define('SMTP_HOST', 'tu-servidor-smtp.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'tu-email@dominio.com');
define('SMTP_PASSWORD', 'tu-contraseña');
```

## Mantenimiento

### Respaldos

- Respaldar la base de datos regularmente
- Respaldar archivos de configuración
- Mantener copias de seguridad del código

### Actualizaciones

- Verificar compatibilidad de PHP antes de actualizar
- Probar en entorno de desarrollo antes de producción
- Mantener respaldos antes de actualizar

## Soporte

Para reportar bugs o solicitar características:

1. Verificar que el problema no esté en la lista de problemas comunes
2. Revisar logs de error del servidor
3. Proporcionar información detallada del error
4. Incluir versión de PHP y MySQL

## Licencia

Este proyecto es de uso libre para fines educativos y comerciales.

---

**Nota**: Este sistema está diseñado para uso en entornos controlados. Para uso en producción, se recomienda:
- Configurar HTTPS
- Implementar logging detallado
- Configurar respaldos automáticos
- Monitorear el rendimiento
- Implementar rate limiting
