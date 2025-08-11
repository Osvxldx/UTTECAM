<?php
/**
 * Funciones de autenticación y manejo de sesiones
 * Gestor de Citas Médicas
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

class Auth {
    private $db;
    private $conn;

    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->getConnection();
    }

    /**
     * Registrar un nuevo usuario
     */
    public function register($nombre, $correo, $contraseña, $telefono) {
        try {
            // Verificar si el correo ya existe
            $stmt = $this->conn->prepare("SELECT id FROM usuarios WHERE correo = ?");
            $stmt->execute([$correo]);
            
            if ($stmt->rowCount() > 0) {
                return ['success' => false, 'message' => 'El correo ya está registrado'];
            }

            // Hash de la contraseña
            $hashedPassword = password_hash($contraseña, PASSWORD_DEFAULT);

            // Insertar usuario
            $stmt = $this->conn->prepare("INSERT INTO usuarios (nombre, correo, contraseña, telefono, rol) VALUES (?, ?, ?, ?, 'cliente')");
            $stmt->execute([$nombre, $correo, $hashedPassword, $telefono]);
            
            $userId = $this->conn->lastInsertId();

            // Crear paciente asociado
            $stmt = $this->conn->prepare("INSERT INTO pacientes (usuario_id, telefono, correo) VALUES (?, ?, ?)");
            $stmt->execute([$userId, $telefono, $correo]);

            return ['success' => true, 'message' => 'Usuario registrado exitosamente', 'user_id' => $userId];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error al registrar usuario: ' . $e->getMessage()];
        }
    }

    /**
     * Iniciar sesión
     */
    public function login($correo, $contraseña) {
        try {
            $stmt = $this->conn->prepare("SELECT id, nombre, correo, contraseña, telefono, rol FROM usuarios WHERE correo = ?");
            $stmt->execute([$correo]);
            
            if ($stmt->rowCount() === 0) {
                return ['success' => false, 'message' => 'Credenciales incorrectas'];
            }

            $user = $stmt->fetch();
            
            if (!password_verify($contraseña, $user['contraseña'])) {
                return ['success' => false, 'message' => 'Credenciales incorrectas'];
            }

            // Crear sesión
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['nombre'];
            $_SESSION['user_email'] = $user['correo'];
            $_SESSION['user_phone'] = $user['telefono'];
            $_SESSION['user_role'] = $user['rol'];
            $_SESSION['logged_in'] = true;

            return ['success' => true, 'message' => 'Sesión iniciada correctamente', 'user' => $user];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error al iniciar sesión: ' . $e->getMessage()];
        }
    }

    /**
     * Cerrar sesión
     */
    public function logout() {
        session_unset();
        session_destroy();
        return ['success' => true, 'message' => 'Sesión cerrada correctamente'];
    }

    /**
     * Obtener información del usuario actual
     */
    public function getCurrentUser() {
        if (!isAuthenticated()) {
            return null;
        }

        try {
            $stmt = $this->conn->prepare("SELECT id, nombre, correo, telefono, rol FROM usuarios WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            return null;
        }
    }

    /**
     * Cambiar contraseña
     */
    public function changePassword($userId, $currentPassword, $newPassword) {
        try {
            // Verificar contraseña actual
            $stmt = $this->conn->prepare("SELECT contraseña FROM usuarios WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();
            
            if (!password_verify($currentPassword, $user['contraseña'])) {
                return ['success' => false, 'message' => 'Contraseña actual incorrecta'];
            }

            // Hash nueva contraseña
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

            // Actualizar contraseña
            $stmt = $this->conn->prepare("UPDATE usuarios SET contraseña = ? WHERE id = ?");
            $stmt->execute([$hashedPassword, $userId]);

            return ['success' => true, 'message' => 'Contraseña actualizada correctamente'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error al cambiar contraseña: ' . $e->getMessage()];
        }
    }

    /**
     * Verificar si el usuario tiene permisos para una acción
     */
    public function hasPermission($permission) {
        if (!isAuthenticated()) {
            return false;
        }

        switch ($permission) {
            case 'admin':
                return $_SESSION['user_role'] === 'admin';
            case 'cliente':
                return $_SESSION['user_role'] === 'cliente';
            case 'any':
                return true;
            default:
                return false;
        }
    }

    /**
     * Obtener todos los usuarios (solo admin)
     */
    public function getAllUsers() {
        if (!$this->hasPermission('admin')) {
            return ['success' => false, 'message' => 'Acceso denegado'];
        }

        try {
            $stmt = $this->conn->prepare("SELECT id, nombre, correo, telefono, rol, created_at FROM usuarios ORDER BY created_at DESC");
            $stmt->execute();
            return ['success' => true, 'users' => $stmt->fetchAll()];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error al obtener usuarios: ' . $e->getMessage()];
        }
    }

    /**
     * Destructor para cerrar conexión
     */
    public function __destruct() {
        $this->db->closeConnection();
    }
}
?>
