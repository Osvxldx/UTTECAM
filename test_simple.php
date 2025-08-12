<?php
/**
 * Prueba simple de la API
 * Usar solo para debugging
 */

// Simular sesión de administrador
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['user_role'] = 'admin';
$_SESSION['user_name'] = 'Admin';

// Probar la API directamente
try {
    // Incluir archivos necesarios
    require_once 'config/config.php';
    require_once 'includes/auth.php';
    require_once 'includes/functions.php';
    require_once 'config/database.php';
    
    echo "<h2>Prueba de la API de Citas</h2>";
    
    // Verificar autenticación
    if (isAuthenticated()) {
        echo "<p>✅ Usuario autenticado correctamente</p>";
        if (isAdmin()) {
            echo "<p>✅ Usuario es administrador</p>";
        } else {
            echo "<p>❌ Usuario NO es administrador</p>";
        }
    } else {
        echo "<p>❌ Usuario NO autenticado</p>";
    }
    
    // Probar conexión a la base de datos
    $db = new Database();
    $conn = $db->getConnection();
    if ($conn) {
        echo "<p>✅ Conexión a base de datos exitosa</p>";
        
        // Probar consulta simple
        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM citas");
        $stmt->execute();
        $result = $stmt->fetch();
        echo "<p>📊 Total de citas en la base de datos: " . $result['total'] . "</p>";
        
    } else {
        echo "<p>❌ Error en conexión a base de datos</p>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
    echo "<p>Archivo: " . $e->getFile() . "</p>";
    echo "<p>Línea: " . $e->getLine() . "</p>";
}
?>
