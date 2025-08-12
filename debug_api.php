<?php
/**
 * Debug específico de la API para identificar el error 400
 * Usar solo para debugging, eliminar en producción
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

echo "<h2>🔍 Debug Específico de la API</h2>";

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
    
    // Probar consulta simple de citas
    echo "<h3>📊 Probando consulta de citas:</h3>";
    
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM citas");
    $stmt->execute();
    $result = $stmt->fetch();
    
    echo "<p>Total de citas: " . $result['total'] . "</p>";
    
    // Probar consulta completa de citas
    echo "<h3>🔍 Probando consulta completa de citas:</h3>";
    
    $stmt = $conn->prepare("
        SELECT c.*, p.telefono as telefono_paciente, p.peso as peso_paciente, p.correo as correo_paciente,
               u.nombre as nombre_paciente
        FROM citas c
        JOIN pacientes p ON c.paciente_id = p.id
        JOIN usuarios u ON p.usuario_id = u.id
        ORDER BY c.fecha DESC
        LIMIT 1
    ");
    
    $stmt->execute();
    $appointment = $stmt->fetch();
    
    if ($appointment) {
        echo "<p>✅ Consulta de citas exitosa</p>";
        echo "<h4>Primera cita encontrada:</h4>";
        echo "<pre>" . print_r($appointment, true) . "</pre>";
        
        // Probar actualización de estado
        echo "<h3>🔄 Probando actualización de estado:</h3>";
        
        $updateStmt = $conn->prepare("UPDATE citas SET estado = ? WHERE id = ?");
        $result = $updateStmt->execute(['pendiente', $appointment['id']]);
        
        if ($result) {
            echo "<p>✅ Actualización de estado exitosa</p>";
        } else {
            echo "<p>❌ Error en actualización de estado</p>";
        }
        
    } else {
        echo "<p>⚠️ No hay citas en la base de datos</p>";
    }
    
    // Probar funciones de email
    echo "<h3>📧 Probando funciones de email:</h3>";
    
    $functions = new Functions();
    
    if (method_exists($functions, 'generateAppointmentEmail')) {
        echo "<p>✅ Función generateAppointmentEmail existe</p>";
        
        try {
            $emailContent = $functions->generateAppointmentEmail(
                'Paciente Test',
                '2024-01-01 10:00:00',
                'Nota de prueba',
                'aprobada'
            );
            echo "<p>✅ Generación de email exitosa</p>";
        } catch (Exception $e) {
            echo "<p>❌ Error generando email: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p>❌ Función generateAppointmentEmail NO existe</p>";
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
