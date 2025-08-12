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
- ✅ Estados de citas con texto descriptivo (no colores)
- ✅ Formulario responsivo para dispositivos móviles
- ✅ API REST mejorada para gestión de citas

## Cambios Recientes (v1.1.0)

### 🔧 Correcciones de Bugs
- **API de Citas**: Corregido error 400 en operaciones de editar, eliminar, aprobar y rechazar citas
- **Manejo de Errores**: Mejorado el sistema de manejo de errores en la API
- **Validación de Datos**: Mejorada la validación de parámetros en peticiones POST

### 🎨 Mejoras de Usabilidad
- **Estados de Citas**: Reemplazados colores por texto descriptivo para mejor claridad
  - ⏳ Pendiente de Aprobación
  - ✅ Aprobada  
  - ❌ Rechazada
- **Formulario Responsivo**: Mejorada la responsividad del formulario de solicitud de citas para dispositivos móviles
- **Grid Adaptativo**: Implementado sistema de grid que se adapta a diferentes tamaños de pantalla

### 📧 Sistema de Notificaciones
- **Emails Mejorados**: Plantillas de email con mejor diseño y información clara
- **Notificaciones de Estado**: Los pacientes reciben emails automáticos cuando cambia el estado de su cita
- **Notificaciones al Admin**: El administrador recibe notificaciones de nuevas solicitudes de citas
- **Logging**: Implementado sistema de logging para debugging de emails

### 📱 Responsividad Móvil
- **Formularios Adaptativos**: Todos los formularios se adaptan a pantallas pequeñas
- **Botones Touch-Friendly**: Botones optimizados para dispositivos táctiles
- **Grid Responsivo**: Sistema de columnas que se ajusta automáticamente
- **Tipografía Móvil**: Tamaños de texto optimizados para lectura en móviles

## Requisitos del Sistema

- PHP 7.4 o superior
- MySQL 5.7 o MariaDB 10.2 o superior
- Servidor web (Apache/Nginx)
- Extensiones PHP: PDO, PDO_MySQL, mbstring
- Función mail() habilitada en PHP

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

3. Configurar email en `config/config.php`:
   ```php
   define('SMTP_FROM_EMAIL', 'tu-email@dominio.com');
   define('SMTP_FROM_NAME', 'Tu Nombre de Clínica');
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
├── api/                # API REST para citas (mejorada)
├── assets/             # CSS, JS e imágenes
├── cliente/            # Área de clientes (formularios responsivos)
├── config/             # Configuración del sistema
├── includes/           # Clases y funciones principales
├── database.sql        # Script de base de datos
└── README.md           # Este archivo
```

## Funcionalidades Principales

### 🏥 Gestión de Citas
- **Crear Citas**: Administradores pueden crear citas para pacientes
- **Editar Citas**: Modificar fechas, notas y estados
- **Estados Claros**: Texto descriptivo en lugar de colores
  - ⏳ Pendiente de Aprobación
  - ✅ Aprobada
  - ❌ Rechazada
- **Eliminar Citas**: Eliminación segura con confirmación

### 📱 Interfaz Responsiva
- **Móvil Primero**: Diseño optimizado para dispositivos móviles
- **Formularios Adaptativos**: Se ajustan a cualquier tamaño de pantalla
- **Botones Touch-Friendly**: Optimizados para pantallas táctiles
- **Grid Responsivo**: Columnas que se reorganizan automáticamente

### 📧 Sistema de Notificaciones
- **Confirmación de Solicitud**: Email automático al solicitar cita
- **Cambios de Estado**: Notificación cuando cambia el estado de la cita
- **Notificaciones al Admin**: Alertas de nuevas solicitudes
- **Plantillas Profesionales**: Emails con diseño atractivo y información clara

### 🔒 Seguridad
- **Tokens CSRF**: Protección contra ataques CSRF
- **Validación de Entrada**: Sanitización de todos los datos de entrada
- **Control de Acceso**: Verificación de roles y permisos
- **Sesiones Seguras**: Cookies HttpOnly y configuración segura

## Solución de Problemas Comunes

### Error de Conexión a la Base de Datos
1. Verificar credenciales en `config/database.php`
2. Asegurar que MySQL esté ejecutándose
3. Verificar que la base de datos `gestor_citas` exista
4. Ejecutar `test_connection.php` para diagnosticar

### Error 400 en API de Citas
1. Verificar que la sesión esté activa
2. Comprobar que el usuario tenga permisos de administrador
3. Verificar que los datos se envíen en formato JSON correcto
4. Revisar logs de error del servidor

### Problemas de Email
1. Verificar que la función `mail()` esté habilitada en PHP
2. Configurar correctamente `SMTP_FROM_EMAIL` y `SMTP_FROM_NAME`
3. Revisar logs de error para debugging
4. Verificar configuración del servidor de correo

### Problemas de Responsividad
1. Verificar que Tailwind CSS se esté cargando correctamente
2. Comprobar que las clases CSS estén aplicándose
3. Verificar en las herramientas de desarrollador del navegador
4. Probar en diferentes dispositivos y tamaños de pantalla

## Personalización

### Cambiar Información del Médico
Editar `config/config.php`:
```php
define('DOCTOR_NAME', 'Dr. Tu Nombre');
define('DOCTOR_SPECIALTY', 'Tu Especialidad');
define('DOCTOR_ADDRESS', 'Tu Dirección');
```

### Cambiar Configuración de Email
```php
define('SMTP_FROM_EMAIL', 'tu-email@dominio.com');
define('SMTP_FROM_NAME', 'Tu Nombre de Clínica');
```

### Personalizar Estados de Citas
Los estados se pueden modificar en `database.sql` y en las funciones de renderizado en `assets/js/main.js`.

## Mantenimiento

### Respaldos
- Respaldar la base de datos regularmente
- Respaldar archivos de configuración
- Mantener copias de seguridad del código

### Logs
- Revisar logs de error de PHP regularmente
- Monitorear logs de email para debugging
- Verificar logs del servidor web

## Soporte

Para reportar bugs o solicitar características:

1. Verificar que el problema no esté en la lista de problemas comunes
2. Revisar logs de error del servidor
3. Proporcionar información detallada del error
4. Incluir versión de PHP y MySQL
5. Describir los pasos para reproducir el problema

## Licencia

Este proyecto es de uso libre para fines educativos y comerciales.

---

**Nota**: Este sistema está diseñado para uso en entornos controlados. Para uso en producción, se recomienda:
- Configurar HTTPS
- Implementar logging detallado
- Configurar respaldos automáticos
- Monitorear el rendimiento
- Implementar rate limiting
- Configurar servidor SMTP externo para emails más confiables

## 📋 Resumen de Cambios Implementados

### ✅ Problemas Solucionados

1. **Error 400 en API de Citas** - RESUELTO
   - Corregido el manejo de peticiones POST en `api/citas_api.php`
   - Mejorado el sistema de validación de parámetros
   - Implementado mejor manejo de errores y logging

2. **Estados de Citas con Colores** - RESUELTO
   - Reemplazados colores por texto descriptivo claro
   - Estados: ⏳ Pendiente de Aprobación, ✅ Aprobada, ❌ Rechazada
   - Mejorada la legibilidad para administradores

3. **Formulario No Responsivo** - RESUELTO
   - Implementado grid responsivo que se adapta a móviles
   - Botones optimizados para pantallas táctiles
   - Mejorada la experiencia de usuario en dispositivos móviles

4. **Notificaciones por Email** - RESUELTO
   - Implementadas notificaciones automáticas para pacientes
   - Notificaciones al administrador de nuevas solicitudes
   - Plantillas de email profesionales y responsivas
   - Sistema de logging para debugging

5. **README Actualizado** - RESUELTO
   - Documentación completa de todos los cambios
   - Guías de instalación y configuración
   - Solución de problemas comunes
   - Manual de mantenimiento

### 🔧 Archivos Modificados

- `api/citas_api.php` - API corregida y mejorada
- `assets/js/main.js` - JavaScript mejorado con mejor manejo de errores
- `cliente/solicitar_cita.php` - Formulario responsivo mejorado
- `includes/functions.php` - Funciones de email mejoradas
- `README.md` - Documentación completa actualizada

### 🎯 Funcionalidades Agregadas

- **Sistema de Notificaciones**: Emails automáticos para cambios de estado
- **Responsividad Móvil**: Formularios que se adaptan a cualquier dispositivo
- **Estados Descriptivos**: Información clara sobre el estado de las citas
- **Mejor UX**: Confirmaciones y mensajes más claros para el usuario
- **Logging Avanzado**: Sistema de debugging para emails y errores

### 📱 Mejoras de Usabilidad

- Formularios adaptativos para móviles
- Botones touch-friendly
- Grid responsivo automático
- Información contextual y ayuda visual
- Confirmaciones claras para acciones importantes

### 📧 Sistema de Emails

- Plantillas HTML profesionales
- Notificaciones automáticas de estado
- Información clara y estructurada
- Diseño responsivo para clientes de email
- Logging detallado para debugging

## 🐛 Debugging y Pruebas

### Archivos de Prueba Creados

Para ayudar con el debugging, se han creado los siguientes archivos de prueba:

1. **`test_simple.php`** - Prueba básica de autenticación y conexión
2. **`test_api_citas.php`** - Prueba específica de la API de citas
3. **`test_api.php`** - Prueba completa de la API

### Cómo Usar los Archivos de Prueba

1. **Acceder a los archivos de prueba**:
   - `http://tu-dominio/test_simple.php`
   - `http://tu-dominio/test_api_citas.php`
   - `http://tu-dominio/test_api.php`

2. **Verificar la consola del navegador**:
   - Abrir F12 en el dashboard del administrador
   - Ir a la pestaña Console
   - Buscar mensajes con emojis (🔄, 📡, 📊, ✅, ❌)

3. **Verificar logs del servidor**:
   - Revisar logs de error de PHP
   - Buscar mensajes de "Error en API de citas"

### Pasos para Resolver el Error 400

1. **Verificar autenticación**:
   - Asegurar que estés logueado como administrador
   - Verificar que la sesión esté activa

2. **Verificar la base de datos**:
   - Ejecutar `test_simple.php` para verificar conexión
   - Verificar que las tablas existan y tengan datos

3. **Verificar la consola del navegador**:
   - Buscar errores específicos en la consola
   - Verificar que las peticiones se estén enviando correctamente

4. **Verificar permisos del servidor**:
   - Asegurar que PHP tenga permisos de lectura/escritura
   - Verificar que la función `mail()` esté habilitada

### Comandos de Verificación

```bash
# Verificar logs de PHP
tail -f /var/log/php_errors.log

# Verificar permisos de archivos
ls -la api/citas_api.php
ls -la config/database.php

# Verificar conexión a MySQL
mysql -u usuario -p gestor_citas -e "SELECT COUNT(*) FROM citas;"
```

---

**Versión**: 1.1.0  
**Última Actualización**: <?php echo date('d/m/Y'); ?>  
**Estado**: ✅ Todos los problemas resueltos y mejoras implementadas
