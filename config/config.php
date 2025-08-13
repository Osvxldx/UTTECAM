<?php
/**
 * Configuración general del sistema
 * Gestor de Citas Médicas
 */

// Configuración de la aplicación
define('APP_NAME', 'Gestor de Citas Médicas');
define('APP_VERSION', '1.0.0');
define('ADMIN_EMAIL', 'admin@clinica.com');

// Configuración de sesión
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Cambiar a 1 si usas HTTPS
ini_set('session.cookie_samesite', 'Lax');
session_start();

// Configuración de zona horaria
date_default_timezone_set('America/Mexico_City');

// Configuración de correo (para PHPMailer)
define('SMTP_HOST', 'localhost');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', '');
define('SMTP_PASSWORD', '');
define('SMTP_FROM_EMAIL', 'noreply@clinica.com');
define('SMTP_FROM_NAME', 'Clínica Saludable');

// Información del médico
define('DOCTOR_NAME', 'Dr. Merdardo García Campos');
define('DOCTOR_SPECIALTY', 'Médico cirujano y partero');
define('DOCTOR_ADDRESS', '7 oriente 406, Tecamachalco, Puebla');
define('DOCTOR_PHONE', '+52 221 246 2420');

// Horarios por defecto
define('DEFAULT_OPEN_TIME', '08:00');
define('DEFAULT_CLOSE_TIME', '17:00');

// Estados de citas
define('CITA_PENDIENTE', 'pendiente');
define('CITA_APROBADA', 'aprobada');
define('CITA_RECHAZADA', 'rechazada');

// Roles de usuario
define('ROLE_ADMIN', 'admin');
define('ROLE_CLIENTE', 'cliente');

// Función para limpiar inputs
function cleanInput($data) {
    if (is_array($data)) {
        return array_map('cleanInput', $data);
    }
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// Función para validar y sanitizar emails
function validateAndCleanEmail($email) {
    $email = filter_var(trim($email), FILTER_SANITIZE_EMAIL);
    return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : false;
}

// Función para validar y sanitizar teléfonos
function validateAndCleanPhone($phone) {
    $phone = preg_replace('/[^0-9+\-\s]/', '', trim($phone));
    return strlen($phone) >= 7 ? $phone : false;
}

// Función para generar token CSRF
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Función para verificar token CSRF
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Función para verificar si el usuario está autenticado
function isAuthenticated() {
    return isset($_SESSION['user_id']) && isset($_SESSION['user_role']);
}

// Función para verificar si el usuario es admin
function isAdmin() {
    return isAuthenticated() && $_SESSION['user_role'] === ROLE_ADMIN;
}

// Función para verificar si el usuario es cliente
function isCliente() {
    return isAuthenticated() && $_SESSION['user_role'] === ROLE_CLIENTE;
}

// Función para redirigir
function redirect($url) {
    header("Location: $url");
    exit();
}
?>
