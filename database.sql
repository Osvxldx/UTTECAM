-- Script de creación de la base de datos para el Gestor de Citas Médicas
-- Ejecutar en MySQL/MariaDB

-- Crear base de datos
CREATE DATABASE IF NOT EXISTS gestor_citas CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
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
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (paciente_id) REFERENCES pacientes(id) ON DELETE CASCADE
);

-- Tabla de días bloqueados
CREATE TABLE dias_bloqueados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fecha DATE NOT NULL,
    hora_apertura TIME NOT NULL,
    hora_cierre TIME NOT NULL
);

-- Crear índices para mejorar rendimiento
CREATE INDEX idx_fecha_cita ON citas(fecha);
CREATE INDEX idx_usuario_paciente ON pacientes(usuario_id);
CREATE INDEX idx_estado_cita ON citas(estado);
CREATE INDEX idx_fecha_bloqueado ON dias_bloqueados(fecha);

-- Insertar usuario administrador por defecto
-- Contraseña: admin123 (hash bcrypt)
INSERT INTO usuarios (nombre, correo, contraseña, telefono, rol) 
VALUES ('Administrador', 'admin@clinica.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '222-123-4567', 'admin');

-- Insertar algunos datos de ejemplo para pruebas
INSERT INTO usuarios (nombre, correo, contraseña, telefono, rol) VALUES
('Juan Pérez', 'juan@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '222-111-1111', 'cliente'),
('María García', 'maria@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '222-222-2222', 'cliente');

-- Insertar pacientes de ejemplo
INSERT INTO pacientes (usuario_id, telefono, peso, correo) VALUES
(2, '222-111-1111', 70.5, 'juan@email.com'),
(3, '222-222-2222', 65.2, 'maria@email.com');

-- Insertar citas de ejemplo
INSERT INTO citas (paciente_id, fecha, notas, estado, creada_por) VALUES
(1, '2025-01-15 10:00:00', 'Consulta general', 'pendiente', 'cliente'),
(2, '2025-01-16 14:30:00', 'Control de peso', 'aprobada', 'admin');

-- Insertar días bloqueados de ejemplo
INSERT INTO dias_bloqueados (fecha, hora_apertura, hora_cierre) VALUES
('2025-01-20', '08:00:00', '17:00:00'),
('2025-01-25', '09:00:00', '16:00:00');

-- Mostrar mensaje de confirmación
SELECT 'Base de datos creada exitosamente' as mensaje;
