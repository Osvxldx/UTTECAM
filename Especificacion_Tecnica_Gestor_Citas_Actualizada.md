# 📄 Especificación Técnica — Gestor de Citas Médicas (Versión Actualizada)

---

## 1. 🎯 Objetivo del Sistema
Desarrollar una plataforma web que permita a **clientes con cuenta registrada** solicitar citas médicas y a un **administrador** gestionarlas (aprobarlas o rechazarlas), junto con la administración de pacientes y horarios de atención, de forma centralizada y eficiente.

---

## 2. 🧑‍💼 Tipos de Usuarios

- **Administrador**: Acceso total (gestión de citas, aprobación/rechazo, pacientes, reportes, configuración).
- **Cliente**: Puede iniciar sesión, solicitar una cita y consultar el estado de sus solicitudes.

---

## 3. 🔐 Autenticación

- **Página**: `login.php` (para clientes y admin)  
- **Registro de clientes**: `registro.php` (campos: nombre, correo, teléfono, contraseña)  
- **Validación**: Contra tabla `usuarios` en MySQL  
- **Control de sesión**: `$_SESSION` en PHP  

---

## 4. 🗄️ Base de Datos MySQL

**Tablas principales**: `usuarios`, `pacientes`, `citas`, `dias_bloqueados`  

### Script SQL recomendado
```sql
-- Crear base de datos
CREATE DATABASE gestor_citas CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE gestor_citas;

-- Tabla de usuarios
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    correo VARCHAR(150) NOT NULL UNIQUE,
    contraseña VARCHAR(255) NOT NULL,
    telefono VARCHAR(20),
    rol ENUM('admin','cliente') NOT NULL DEFAULT 'cliente',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de pacientes
CREATE TABLE pacientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    telefono VARCHAR(20),
    peso FLOAT,
    correo VARCHAR(150),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Tabla de citas
CREATE TABLE citas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    paciente_id INT NOT NULL,
    fecha DATETIME NOT NULL,
    notas TEXT,
    estado ENUM('pendiente','aprobada','rechazada') NOT NULL DEFAULT 'pendiente',
    creada_por ENUM('cliente','admin') NOT NULL,
    FOREIGN KEY (paciente_id) REFERENCES pacientes(id) ON DELETE CASCADE
);

-- Tabla de días bloqueados
CREATE TABLE dias_bloqueados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fecha DATE NOT NULL,
    hora_apertura TIME NOT NULL,
    hora_cierre TIME NOT NULL
);

-- Índices recomendados
CREATE INDEX idx_fecha_cita ON citas(fecha);
CREATE INDEX idx_usuario_paciente ON pacientes(usuario_id);
```

---

## 5. ⚙️ Funcionalidades

### Cliente
- Registro e inicio de sesión  
- Formulario de solicitud de cita (solo usuarios logueados)  
- Consultar historial y estado de citas  
- Recibir correo de confirmación de solicitud enviada  

### Administrador
- CRUD de pacientes  
- Aprobar o rechazar citas solicitadas por clientes  
- Ver citas en calendario semanal/mensual  
- Exportar reportes a Excel/PDF  
- Bloquear días no disponibles  
- Recibir correo de notificación cada vez que un cliente solicite una cita  

---

## 6. 📧 Notificaciones por Correo
- **Cliente**: recibe correo de confirmación de solicitud  
- **Admin**: recibe correo con detalles de la cita pendiente para su aprobación  

---

## 7. 🔁 Conexión Frontend <-> Backend
Frontend usará JavaScript (fetch o AJAX) para comunicarse con scripts PHP.

**Ejemplo flujo para solicitud de cita**:
1. Cliente llena formulario en `solicitar_cita.php`  
2. PHP guarda la cita en MySQL con estado `"pendiente"`  
3. PHP envía correo al admin con detalles de la solicitud  
4. Admin aprueba o rechaza la cita en el dashboard  
5. PHP actualiza el estado y notifica al cliente por correo  

---

## 8. 🛡️ Validaciones Importantes
- Solo clientes registrados pueden solicitar citas  
- Validar días bloqueados y horarios permitidos  
- Evitar duplicados  
- Contraseñas seguras (`password_hash`)  
- Validaciones en cliente y servidor  

---

## 9. 🔮 Recomendaciones Futuras
- Pasarela de pagos para citas pagadas en línea  
- Confirmaciones por WhatsApp  
- Integración con calendario de Google o Outlook  
