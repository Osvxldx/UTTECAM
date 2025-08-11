<?php
/**
 * Archivo de prueba para verificar la conexión a la base de datos
 * Este archivo debe ser eliminado en producción
 */

require_once 'config/config.php';
require_once 'config/database.php';

echo "<h1>Prueba de Conexión - Gestor de Citas Médicas</h1>";

try {
    $db = new Database();
    
    if ($db->testConnection()) {
        echo "<p style='color: green;'>✅ Conexión a la base de datos exitosa</p>";
        
        // Probar algunas consultas básicas
        $conn = $db->getConnection();
        
        // Verificar tablas
        $stmt = $conn->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        echo "<h3>Tablas encontradas:</h3>";
        echo "<ul>";
        foreach ($tables as $table) {
            echo "<li>$table</li>";
        }
        echo "</ul>";
        
        // Verificar usuarios
        $stmt = $conn->query("SELECT COUNT(*) as total FROM usuarios");
        $userCount = $stmt->fetch()['total'];
        echo "<p><strong>Total de usuarios:</strong> $userCount</p>";
        
        // Verificar citas
        $stmt = $conn->query("SELECT COUNT(*) as total FROM citas");
        $citaCount = $stmt->fetch()['total'];
        echo "<p><strong>Total de citas:</strong> $citaCount</p>";
        
        // Verificar pacientes
        $stmt = $conn->query("SELECT COUNT(*) as total FROM pacientes");
        $patientCount = $stmt->fetch()['total'];
        echo "<p><strong>Total de pacientes:</strong> $patientCount</p>";
        
        // Verificar días bloqueados
        $stmt = $conn->query("SELECT COUNT(*) as total FROM dias_bloqueados");
        $blockedCount = $stmt->fetch()['total'];
        echo "<p><strong>Total de días bloqueados:</strong> $blockedCount</p>";
        
    } else {
        echo "<p style='color: red;'>❌ Error al conectar con la base de datos</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><strong>Información del sistema:</strong></p>";
echo "<ul>";
echo "<li>PHP Version: " . phpversion() . "</li>";
echo "<li>Servidor: " . $_SERVER['SERVER_SOFTWARE'] . "</li>";
echo "<li>Base de datos: MySQL/MariaDB</li>";
echo "<li>Timezone: " . date_default_timezone_get() . "</li>";
echo "</ul>";

echo "<p><em>Este archivo debe ser eliminado después de las pruebas.</em></p>";
?>
