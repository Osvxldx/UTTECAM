<?php
require_once 'config/config.php';
require_once 'includes/auth.php';

// Cerrar sesión
$auth = new Auth();
$auth->logout();

// Redirigir al login
redirect('login.php');
?>
