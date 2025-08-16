<?php
require_once 'config/config.php';
require_once 'includes/auth.php';

// Si ya está logueado, redirigir
if (isAuthenticated()) {
    if (isAdmin()) {
        redirect('admin/dashboard.php');
    } else {
        redirect('cliente/solicitar_cita.php');
    }
}

$error = '';
$success = '';

// Procesar registro
if ($_POST && isset($_POST['register'])) {
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        $error = 'Token de seguridad inválido';
    } else {
        $nombre = cleanInput($_POST['nombre']);
        $correo = cleanInput($_POST['correo']);
        $telefono = cleanInput($_POST['telefono']);
        $contraseña = $_POST['contraseña'];
        $confirmar_contraseña = $_POST['confirmar_contraseña'];
        
        // Validaciones
        if (empty($nombre) || empty($correo) || empty($telefono) || empty($contraseña)) {
            $error = 'Por favor complete todos los campos';
        } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $error = 'Formato de correo electrónico inválido';
        } elseif (strlen($contraseña) < 6) {
            $error = 'La contraseña debe tener al menos 6 caracteres';
        } elseif ($contraseña !== $confirmar_contraseña) {
            $error = 'Las contraseñas no coinciden';
        } else {
            $auth = new Auth();
            $result = $auth->register($nombre, $correo, $contraseña, $telefono);
            
            if ($result['success']) {
                $success = 'Usuario registrado exitosamente. Ya puedes iniciar sesión.';
                // Limpiar formulario
                $_POST = array();
            } else {
                $error = $result['message'];
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Gestor de Citas Médicas</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Poppins', sans-serif; }
        .fade-in { animation: fade 0.6s ease-in-out both; }
        @keyframes fade {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-indigo-50 to-blue-100 flex items-center justify-center p-4">
    <div class="max-w-md w-full fade-in">
        <!-- Logo y título -->
        <div class="text-center mb-8">
            <img src="imagen/Logo.jpg" alt="Logo" class="w-20 h-20 bg-indigo-600 rounded-full flex items-center justify-center mx-auto mb-4">
            <h1 class="text-2xl font-bold text-gray-800">Farmamedis</h1>
            <p class="text-gray-600 mt-2"><?php echo DOCTOR_NAME; ?></p>
            <p class="text-sm text-gray-500"><?php echo DOCTOR_SPECIALTY; ?></p>
            <p class="text-xs text-gray-400 mt-1"><?php echo DOCTOR_ADDRESS; ?></p>
        </div>

        <!-- Formulario de registro -->
        <div class="bg-white rounded-2xl shadow-xl p-8">
            <h2 class="text-xl font-semibold text-center mb-6">Crear Cuenta</h2>
            
            <?php if ($error): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <i class="ph-warning"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    <i class="ph-check-circle"></i> <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-4">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                
                <div>
                    <label for="nombre" class="block text-sm font-medium text-gray-700 mb-1">
                        Nombre Completo
                    </label>
                    <div class="relative">
                        <i class="ph-user absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        <input type="text" id="nombre" name="nombre" required
                               value="<?php echo isset($_POST['nombre']) ? htmlspecialchars($_POST['nombre']) : ''; ?>"
                               class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                               placeholder="Tu nombre completo">
                    </div>
                </div>

                <div>
                    <label for="correo" class="block text-sm font-medium text-gray-700 mb-1">
                        Correo Electrónico
                    </label>
                    <div class="relative">
                        <i class="ph-envelope absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        <input type="email" id="correo" name="correo" required
                               value="<?php echo isset($_POST['correo']) ? htmlspecialchars($_POST['correo']) : ''; ?>"
                               class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                               placeholder="tu@email.com">
                    </div>
                </div>

                <div>
                    <label for="telefono" class="block text-sm font-medium text-gray-700 mb-1">
                        Teléfono
                    </label>
                    <div class="relative">
                        <i class="ph-phone absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        <input type="tel" id="telefono" name="telefono" required
                               value="<?php echo isset($_POST['telefono']) ? htmlspecialchars($_POST['telefono']) : ''; ?>"
                               class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                               placeholder="222-123-4567">
                    </div>
                </div>

                <div>
                    <label for="contraseña" class="block text-sm font-medium text-gray-700 mb-1">
                        Contraseña
                    </label>
                    <div class="relative">
                        <i class="ph-lock absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        <input type="password" id="contraseña" name="contraseña" required
                               class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                               placeholder="Mínimo 6 caracteres">
                    </div>
                </div>

                <div>
                    <label for="confirmar_contraseña" class="block text-sm font-medium text-gray-700 mb-1">
                        Confirmar Contraseña
                    </label>
                    <div class="relative">
                        <i class="ph-lock absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        <input type="password" id="confirmar_contraseña" name="confirmar_contraseña" required
                               class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                               placeholder="Repite tu contraseña">
                    </div>
                </div>

                <button type="submit" name="register" 
                        class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-3 px-4 rounded-lg transition duration-200 flex items-center justify-center gap-2">
                    <i class="ph-user-plus"></i>
                    Crear Cuenta
                </button>
            </form>

            <div class="mt-6 text-center">
                <p class="text-sm text-gray-600">
                    ¿Ya tienes cuenta? 
                    <a href="login.php" class="text-indigo-600 hover:text-indigo-700 font-medium">
                        Inicia sesión aquí
                    </a>
                </p>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center mt-6">
            <p class="text-xs text-gray-500">
                © 2025 Clínica Saludable. Todos los derechos reservados.
            </p>
        </div>
    </div>
</body>
</html>
