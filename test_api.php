<?php
/**
 * Archivo de prueba para la API de citas
 * Usar solo para debugging, eliminar en producción
 */

// Simular una sesión de administrador
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['user_role'] = 'admin';
$_SESSION['user_name'] = 'Administrador';

// Incluir la API
include_once 'api/citas_api.php';
?>
