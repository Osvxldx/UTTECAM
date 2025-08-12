<?php
/**
 * Prueba específica de la API de citas
 * Usar solo para debugging
 */

// Simular sesión de administrador
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['user_role'] = 'admin';
$_SESSION['user_name'] = 'Admin';

// Incluir archivos necesarios
require_once 'config/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';
require_once 'config/database.php';

echo "<h2>Prueba de la API de Citas</h2>";

try {
    // Verificar autenticación
    if (!isAuthenticated()) {
        throw new Exception('Usuario no autenticado');
    }
    
    if (!isAdmin()) {
        throw new Exception('Usuario no es administrador');
    }
    
    echo "<p>✅ Autenticación correcta</p>";
    
    // Probar conexión a la base de datos
    $db = new Database();
    $conn = $db->getConnection();
    
    if (!$conn) {
        throw new Exception('No se pudo conectar a la base de datos');
    }
    
    echo "<p>✅ Conexión a base de datos exitosa</p>";
    
    // Probar obtener todas las citas
    echo "<h3>Probando obtener todas las citas:</h3>";
    
    $stmt = $conn->prepare("
        SELECT c.*, p.telefono as telefono_paciente, p.peso as peso_paciente, p.correo as correo_paciente,
               u.nombre as nombre_paciente
        FROM citas c
        JOIN pacientes p ON c.paciente_id = p.id
        JOIN usuarios u ON p.usuario_id = u.id
        ORDER BY c.fecha DESC
    ");
    
    $stmt->execute();
    $appointments = $stmt->fetchAll();
    
    echo "<p>📊 Total de citas encontradas: " . count($appointments) . "</p>";
    
    if (count($appointments) > 0) {
        echo "<h4>Primera cita:</h4>";
        echo "<pre>" . print_r($appointments[0], true) . "</pre>";
    }
    
    // Probar obtener días bloqueados
    echo "<h3>Probando obtener días bloqueados:</h3>";
    
    $stmt = $conn->prepare("SELECT * FROM dias_bloqueados ORDER BY fecha");
    $stmt->execute();
    $blockedDays = $stmt->fetchAll();
    
    echo "<p>📅 Total de días bloqueados: " . count($blockedDays) . "</p>";
    
    if (count($blockedDays) > 0) {
        echo "<h4>Primer día bloqueado:</h4>";
        echo "<pre>" . print_r($blockedDays[0], true) . "</pre>";
    }
    
    echo "<p>✅ Todas las pruebas pasaron correctamente</p>";
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
    echo "<p>Archivo: " . $e->getFile() . "</p>";
    echo "<p>Línea: " . $e->getLine() . "</p>";
    
    // Mostrar stack trace para debugging
    echo "<h4>Stack Trace:</h4>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>
