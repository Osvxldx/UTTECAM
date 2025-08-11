<?php
/**
 * Script de prueba para diagnosticar problemas de login
 * Gestor de Citas Médicas
 */

require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/auth.php';

echo "<h1>Diagnóstico de Login - Gestor de Citas Médicas</h1>";

// 1. Verificar configuración de sesiones
echo "<h2>1. Configuración de Sesiones</h2>";
echo "<p>Session status: " . (session_status() === PHP_SESSION_ACTIVE ? 'Activa' : 'Inactiva') . "</p>";
echo "<p>Session name: " . session_name() . "</p>";
echo "<p>Session ID: " . (session_id() ?: 'No iniciada') . "</p>";

// 2. Verificar conexión a la base de datos
echo "<h2>2. Conexión a la Base de Datos</h2>";
try {
    $db = new Database();
    $conn = $db->getConnection();
    echo "<p style='color: green;'>✓ Conexión exitosa a la base de datos</p>";
    
    // Verificar si la base de datos existe
    $stmt = $conn->query("SELECT DATABASE() as current_db");
    $result = $stmt->fetch();
    echo "<p>Base de datos actual: " . $result['current_db'] . "</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error de conexión: " . $e->getMessage() . "</p>";
    exit;
}

// 3. Verificar tablas
echo "<h2>3. Verificación de Tablas</h2>";
$tables = ['usuarios', 'pacientes', 'citas', 'dias_bloqueados'];
foreach ($tables as $table) {
    try {
        $stmt = $conn->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "<p style='color: green;'>✓ Tabla '$table' existe</p>";
        } else {
            echo "<p style='color: red;'>✗ Tabla '$table' NO existe</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>✗ Error verificando tabla '$table': " . $e->getMessage() . "</p>";
    }
}

// 4. Verificar usuarios existentes
echo "<h2>4. Usuarios en la Base de Datos</h2>";
try {
    $stmt = $conn->query("SELECT id, nombre, correo, rol, created_at FROM usuarios ORDER BY id");
    $users = $stmt->fetchAll();
    
    if (count($users) > 0) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Nombre</th><th>Correo</th><th>Rol</th><th>Creado</th></tr>";
        foreach ($users as $user) {
            echo "<tr>";
            echo "<td>" . $user['id'] . "</td>";
            echo "<td>" . htmlspecialchars($user['nombre']) . "</td>";
            echo "<td>" . htmlspecialchars($user['correo']) . "</td>";
            echo "<td>" . $user['rol'] . "</td>";
            echo "<td>" . $user['created_at'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: orange;'>⚠ No hay usuarios en la base de datos</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error obteniendo usuarios: " . $e->getMessage() . "</p>";
}

// 5. Probar autenticación con credenciales conocidas
echo "<h2>5. Prueba de Autenticación</h2>";
$testCredentials = [
    ['correo' => 'admin@clinica.com', 'contraseña' => 'admin123', 'desc' => 'Admin'],
    ['correo' => 'juan@email.com', 'contraseña' => 'admin123', 'desc' => 'Cliente Juan'],
    ['correo' => 'maria@email.com', 'contraseña' => 'admin123', 'desc' => 'Cliente María']
];

foreach ($testCredentials as $cred) {
    try {
        $auth = new Auth();
        $result = $auth->login($cred['correo'], $cred['contraseña']);
        
        if ($result['success']) {
            echo "<p style='color: green;'>✓ {$cred['desc']}: Login exitoso</p>";
            // Cerrar sesión para la siguiente prueba
            $auth->logout();
        } else {
            echo "<p style='color: red;'>✗ {$cred['desc']}: " . $result['message'] . "</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>✗ {$cred['desc']}: Error - " . $e->getMessage() . "</p>";
    }
}

// 6. Verificar hash de contraseñas
echo "<h2>6. Verificación de Hash de Contraseñas</h2>";
try {
    $stmt = $conn->prepare("SELECT correo, contraseña FROM usuarios WHERE correo IN (?, ?, ?)");
    $stmt->execute(['admin@clinica.com', 'juan@email.com', 'maria@email.com']);
    $users = $stmt->fetchAll();
    
    foreach ($users as $user) {
        $isValid = password_verify('admin123', $user['contraseña']);
        $status = $isValid ? '✓ Válido' : '✗ Inválido';
        $color = $isValid ? 'green' : 'red';
        echo "<p style='color: $color;'>$status - {$user['correo']}</p>";
        
        if (!$isValid) {
            echo "<p style='font-size: 12px; color: gray;'>Hash actual: " . substr($user['contraseña'], 0, 20) . "...</p>";
        }
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error verificando hashes: " . $e->getMessage() . "</p>";
}

// 7. Información del servidor
echo "<h2>7. Información del Servidor</h2>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>Server Software: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'N/A') . "</p>";
echo "<p>Document Root: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'N/A') . "</p>";
echo "<p>Current Directory: " . getcwd() . "</p>";

// 8. Verificar permisos de archivos
echo "<h2>8. Permisos de Archivos</h2>";
$files = ['config/config.php', 'config/database.php', 'includes/auth.php', 'login.php'];
foreach ($files as $file) {
    if (file_exists($file)) {
        $perms = fileperms($file);
        $perms_octal = substr(sprintf('%o', $perms), -4);
        echo "<p style='color: green;'>✓ $file (permisos: $perms_octal)</p>";
    } else {
        echo "<p style='color: red;'>✗ $file NO existe</p>";
    }
}

echo "<hr>";
echo "<p><strong>Nota:</strong> Si hay errores, verifica:</p>";
echo "<ul>";
echo "<li>Que XAMPP esté ejecutándose (Apache y MySQL)</li>";
echo "<li>Que la base de datos 'gestor_citas' exista</li>";
echo "<li>Que las credenciales en config/database.php sean correctas</li>";
echo "<li>Que el archivo database.sql se haya ejecutado correctamente</li>";
echo "</ul>";

echo "<p><a href='login.php'>← Volver al Login</a></p>";
?>
