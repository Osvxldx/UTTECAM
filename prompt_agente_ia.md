# 🩺 Prompt para Agente IA: Gestor de Citas Médicas

## 📋 INSTRUCCIONES GENERALES
Necesito que transformes un sistema de gestión de citas médicas siguiendo EXACTAMENTE la especificación técnica proporcionada. El sistema actual está en HTML/CSS/JS puro y necesita convertirse a PHP con MySQL, manteniendo el diseño actual pero refactorizando el código de manera limpia y organizada.

## 🛠️ STACK TECNOLÓGICO OBLIGATORIO
- **Backend**: PHP (sin frameworks)
- **Base de datos**: MySQL
- **Frontend**: HTML5, CSS3, JavaScript vanilla
- **Servidor local**: XAMPP
- **Librerías permitidas**: Solo las ya incluidas (jsPDF, XLSX, Phosphor Icons, Tailwind CSS)

## 🎯 INFORMACIÓN DEL MÉDICO (INTEGRAR EN EL DASHBOARD)
```
Dr. Merdardo García Campos
Médico cirujano y partero
📍 7 oriente 406, Tecamachalco, Puebla
```

## 📁 ESTRUCTURA DE ARCHIVOS REQUERIDA
```
/gestor_citas/
├── /config/
│   ├── database.php (conexión MySQL)
│   └── config.php (configuración general)
├── /includes/
│   ├── auth.php (funciones de autenticación)
│   └── functions.php (funciones generales)
├── /admin/
│   ├── dashboard.php (interfaz actual adaptada)
│   ├── gestionar_citas.php
│   └── exportar.php
├── /cliente/
│   ├── solicitar_cita.php
│   └── mis_citas.php
├── /api/
│   ├── citas_api.php
│   ├── auth_api.php
│   └── reportes_api.php
├── /assets/
│   ├── /css/style.css
│   ├── /js/main.js
│   └── /uploads/ (para futuras imágenes)
├── login.php
├── registro.php
├── logout.php
└── index.php (redirección)
```

## 🗄️ BASE DE DATOS - IMPLEMENTAR EXACTAMENTE ESTE SCRIPT
```sql
CREATE DATABASE gestor_citas CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE gestor_citas;

CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    correo VARCHAR(150) NOT NULL UNIQUE,
    contraseña VARCHAR(255) NOT NULL,
    telefono VARCHAR(20),
    rol ENUM('admin','cliente') NOT NULL DEFAULT 'cliente',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE pacientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    telefono VARCHAR(20),
    peso FLOAT,
    correo VARCHAR(150),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

CREATE TABLE citas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    paciente_id INT NOT NULL,
    fecha DATETIME NOT NULL,
    notas TEXT,
    estado ENUM('pendiente','aprobada','rechazada') NOT NULL DEFAULT 'pendiente',
    creada_por ENUM('cliente','admin') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (paciente_id) REFERENCES pacientes(id) ON DELETE CASCADE
);

CREATE TABLE dias_bloqueados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fecha DATE NOT NULL,
    hora_apertura TIME NOT NULL,
    hora_cierre TIME NOT NULL
);

-- Usuario admin por defecto
INSERT INTO usuarios (nombre, correo, contraseña, telefono, rol) 
VALUES ('Administrador', 'admin@clinica.com', '$2y$10$ejemplo_hash', '222-123-4567', 'admin');
```

## 🔧 REFACTORIZACIÓN REQUERIDA

### 1. SEPARAR LÓGICA DE PRESENTACIÓN
- Crear archivos PHP separados para cada funcionalidad
- Implementar patrón MVC básico sin frameworks
- Separar JavaScript en módulos organizados
- CSS modular y bien comentado

### 2. SISTEMA DE AUTENTICACIÓN
- Implementar login/logout con sesiones PHP
- Validación de permisos por rol (admin/cliente)
- Protección CSRF básica
- Hashing seguro de contraseñas

### 3. API ENDPOINTS
Crear endpoints PHP que respondan JSON para:
- `POST /api/auth_api.php` - Login/logout
- `GET/POST /api/citas_api.php` - CRUD de citas
- `GET /api/reportes_api.php` - Datos para reportes

## 🎨 DISEÑO Y UX

### MANTENER EXACTAMENTE:
- Diseño visual actual (colores, tipografía, layout)
- Sidebar navigation
- Calendario mensual/semanal
- Tablas responsivas
- Iconografía Phosphor

### MODIFICAR SOLO:
- Agregar información del doctor en header/sidebar
- Formularios de login/registro (diseño coherente)
- Estados de citas (pendiente/aprobada/rechazada)
- Diferenciación visual admin vs cliente

## ⚙️ FUNCIONALIDADES ESPECÍFICAS

### CLIENTE (usuarios registrados):
1. **Registro**: `registro.php` con validación
2. **Login**: `login.php` 
3. **Solicitar cita**: Crear cita con estado "pendiente"
4. **Ver mis citas**: Historial con estados
5. **Recibir email**: Confirmación de solicitud enviada

### ADMINISTRADOR:
1. **Dashboard actual**: Mantener funcionalidades existentes
2. **Gestión de citas**: Aprobar/rechazar citas pendientes
3. **Vista calendario**: Mostrar todas las citas con estados
4. **Reportes**: Excel/PDF con filtros
5. **Días bloqueados**: Funcionalidad actual
6. **Recibir email**: Notificación de nuevas solicitudes

## 📧 SISTEMA DE NOTIFICACIONES
- Configurar PHPMailer o función mail() nativa
- Template básico HTML para emails
- Envío automático en eventos específicos

## 🔒 VALIDACIONES OBLIGATORIAS
- Sanitización de inputs
- Validación de horarios contra días bloqueados
- Verificación de permisos por rol
- Prevención de inyección SQL (prepared statements)
- Validación de formularios (cliente y servidor)

## 📝 ENTREGABLES ESPERADOS
1. **Código PHP limpio y comentado**
2. **Base de datos con datos de ejemplo**
3. **Frontend adaptado manteniendo diseño**
4. **Sistema de autenticación funcional**
5. **Documentación básica de instalación**

## 🚨 RESTRICCIONES IMPORTANTES
- NO usar frameworks PHP (Laravel, CodeIgniter, etc.)
- NO cambiar el diseño visual significativamente
- NO añadir funcionalidades no especificadas
- SÍ mantener la simplicidad del código actual
- SÍ seguir al pie de la letra el markdown técnico

## 📋 CHECKLIST DE COMPLETACIÓN
- [ ] Base de datos creada e implementada
- [ ] Sistema de autenticación funcional
- [ ] Dashboard admin con todas las funciones actuales
- [ ] Interfaz cliente para solicitar citas
- [ ] Estados de citas (pendiente/aprobada/rechazada)
- [ ] Sistema de notificaciones por email
- [ ] Exportación Excel/PDF funcional
- [ ] Validaciones de seguridad implementadas
- [ ] Código refactorizado y organizado
- [ ] Información del doctor integrada

## ❗ NOTA FINAL
El objetivo es mantener la funcionalidad y diseño actual, pero implementar correctamente el sistema multiusuario con autenticación y base de datos siguiendo la especificación técnica. El código debe ser limpio, seguro y fácil de mantener.