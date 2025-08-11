<?php
/**
 * Configuración de la base de datos MySQL
 * Gestor de Citas Médicas
 */

class Database {
    private $host = 'localhost';
    private $db_name = 'gestor_citas';
    private $username = 'root';
    private $password = '';
    private $conn;

    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4",
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET time_zone = '+00:00'"
                ]
            );
        } catch(PDOException $exception) {
            error_log("Error de conexión a la base de datos: " . $exception->getMessage());
            throw new Exception("Error de conexión a la base de datos. Por favor, contacte al administrador.");
        }

        return $this->conn;
    }

    public function closeConnection() {
        $this->conn = null;
    }

    /**
     * Verificar si la conexión está activa
     */
    public function isConnected() {
        return $this->conn !== null;
    }

    /**
     * Ejecutar una consulta de prueba
     */
    public function testConnection() {
        try {
            $conn = $this->getConnection();
            $stmt = $conn->query("SELECT 1");
            return $stmt->fetch() !== false;
        } catch (Exception $e) {
            return false;
        }
    }
}
?>
