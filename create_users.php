<?php
/**
 * Script para crear usuarios de prueba con contraseñas válidas
 * Gestor de Citas Médicas
 */

require_once 'config/config.php';
require_once 'config/database.php';

echo "<h1>Creación de Usuarios de Prueba</h1>";

try {
    $db = new Database();
    $conn = $db->getConnection();
    echo "<p style='color: green;'>✓ Conexión exitosa a la base de datos</p>";
    
    // Limpiar usuarios existentes (opcional - comentar si no quieres borrar)
    echo "<h2>Limpiando usuarios existentes...</h2>";
    $conn->exec("DELETE FROM citas");
    $conn->exec("DELETE FROM pacientes");
    $conn->exec("DELETE FROM usuarios");
    echo "<p>Usuarios anteriores eliminados</p>";
    
    // Crear usuario administrador
    echo "<h2>Creando usuario administrador...</h2>";
    $adminPassword = 'admin123';
    $adminHash = password_hash($adminPassword, PASSWORD_DEFAULT);
    
    $stmt = $conn->prepare("INSERT INTO usuarios (nombre, correo, contraseña, telefono, rol) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute(['Administrador', 'admin@clinica.com', $adminHash, '222-123-4567', 'admin']);
    $adminId = $conn->lastInsertId();
    echo "<p style='color: green;'>✓ Usuario administrador creado (ID: $adminId)</p>";
    echo "<p>Email: admin@clinica.com</p>";
    echo "<p>Contraseña: $adminPassword</p>";
    echo "<p>Hash: " . substr($adminHash, 0, 20) . "...</p>";
    
    // Crear usuario cliente Juan
    echo "<h2>Creando usuario cliente Juan...</h2>";
    $juanPassword = 'juan123';
    $juanHash = password_hash($juanPassword, PASSWORD_DEFAULT);
    
    $stmt = $conn->prepare("INSERT INTO usuarios (nombre, correo, contraseña, telefono, rol) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute(['Juan Pérez', 'juan@email.com', $juanHash, '222-111-1111', 'cliente']);
    $juanId = $conn->lastInsertId();
    echo "<p style='color: green;'>✓ Usuario Juan creado (ID: $juanId)</p>";
    echo "<p>Email: juan@email.com</p>";
    echo "<p>Contraseña: $juanPassword</p>";
    echo "<p>Hash: " . substr($juanHash, 0, 20) . "...</p>";
    
    // Crear usuario cliente María
    echo "<h2>Creando usuario cliente María...</h2>";
    $mariaPassword = 'maria123';
    $mariaHash = password_hash($mariaPassword, PASSWORD_DEFAULT);
    
    $stmt = $conn->prepare("INSERT INTO usuarios (nombre, correo, contraseña, telefono, rol) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute(['María García', 'maria@email.com', $mariaHash, '222-222-2222', 'cliente']);
    $mariaId = $conn->lastInsertId();
    echo "<p style='color: green;'>✓ Usuario María creado (ID: $mariaId)</p>";
    echo "<p>Email: maria@email.com</p>";
    echo "<p>Contraseña: $mariaPassword</p>";
    echo "<p>Hash: " . substr($mariaHash, 0, 20) . "...</p>";
    
    // Crear pacientes asociados
    echo "<h2>Creando pacientes...</h2>";
    $stmt = $conn->prepare("INSERT INTO pacientes (usuario_id, telefono, peso, correo) VALUES (?, ?, ?, ?)");
    
    $stmt->execute([$juanId, '222-111-1111', 70.5, 'juan@email.com']);
    echo "<p style='color: green;'>✓ Paciente Juan creado</p>";
    
    $stmt->execute([$mariaId, '222-222-2222', 65.2, 'maria@email.com']);
    echo "<p style='color: green;'>✓ Paciente María creado</p>";
    
    // Crear citas de ejemplo
    echo "<h2>Creando citas de ejemplo...</h2>";
    $stmt = $conn->prepare("INSERT INTO citas (paciente_id, fecha, notas, estado, creada_por) VALUES (?, ?, ?, ?, ?)");
    
    $stmt->execute([1, '2025-01-15 10:00:00', 'Consulta general', 'pendiente', 'cliente']);
    echo "<p style='color: green;'>✓ Cita 1 creada</p>";
    
    $stmt->execute([2, '2025-01-16 14:30:00', 'Control de peso', 'aprobada', 'admin']);
    echo "<p style='color: green;'>✓ Cita 2 creada</p>";
    
    // Crear días bloqueados
    echo "<h2>Creando días bloqueados...</h2>";
    $stmt = $conn->prepare("INSERT INTO dias_bloqueados (fecha, hora_apertura, hora_cierre) VALUES (?, ?, ?)");
    
    $stmt->execute(['2025-01-20', '08:00:00', '17:00:00']);
    echo "<p style='color: green;'>✓ Día bloqueado 1 creado</p>";
    
    $stmt->execute(['2025-01-25', '09:00:00', '16:00:00']);
    echo "<p style='color: green;'>✓ Día bloqueado 2 creado</p>";
    
    echo "<hr>";
    echo "<h2>Resumen de Credenciales</h2>";
    echo "<div style='background: #f0f0f0; padding: 15px; border-radius: 5px;'>";
    echo "<h3>Administrador:</h3>";
    echo "<p><strong>Email:</strong> admin@clinica.com</p>";
    echo "<p><strong>Contraseña:</strong> admin123</p>";
    echo "<br>";
    echo "<h3>Cliente Juan:</h3>";
    echo "<p><strong>Email:</strong> juan@email.com</p>";
    echo "<p><strong>Contraseña:</strong> juan123</p>";
    echo "<br>";
    echo "<h3>Cliente María:</h3>";
    echo "<p><strong>Email:</strong> maria@email.com</p>";
    echo "<p><strong>Contraseña:</strong> maria123</p>";
    echo "</div>";
    
    echo "<hr>";
    echo "<p><a href='test_login.php'>🧪 Probar Login</a></p>";
    echo "<p><a href='login.php'>🔐 Ir al Login</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
    echo "<p>Verifica que:</p>";
    echo "<ul>";
    echo "<li>XAMPP esté ejecutándose</li>";
    echo "<li>La base de datos 'gestor_citas' exista</li>";
    echo "<li>Las credenciales en config/database.php sean correctas</li>";
    echo "</ul>";
}
?>
