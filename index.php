<?php
require_once 'config/config.php';

// Redirigir según el estado de autenticación
if (isAuthenticated()) {
    if (isAdmin()) {
        redirect('admin/dashboard.php');
    } else {
        redirect('cliente/solicitar_cita.php');
    }
} else {
    redirect('login.php');
}
?>
