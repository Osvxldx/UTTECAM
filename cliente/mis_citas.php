<?php
require_once '../config/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Verificar que sea cliente
if (!isCliente()) {
    redirect('../login.php');
}

$functions = new Functions();
$doctorInfo = $functions->getDoctorInfo();

// Obtener citas del usuario actual
try {
    $db = new Database();
    $conn = $db->getConnection();
    
    $stmt = $conn->prepare("
        SELECT c.*, p.telefono as telefono_paciente, p.peso as peso_paciente, p.correo as correo_paciente,
               u.nombre as nombre_paciente
        FROM citas c
        JOIN pacientes p ON c.paciente_id = p.id
        JOIN usuarios u ON p.usuario_id = u.id
        WHERE u.id = ?
        ORDER BY c.fecha DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $citas = $stmt->fetchAll();
    
} catch (Exception $e) {
    $citas = [];
    $error = 'Error al cargar las citas: ' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Citas - Gestor de Citas Médicas</title>
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
        .status-pendiente { @apply bg-yellow-100 text-yellow-800; }
        .status-aprobada { @apply bg-green-100 text-green-800; }
        .status-rechazada { @apply bg-red-100 text-red-800; }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-indigo-50 to-blue-100">
    <!-- Header -->
    <header class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center">
                    <img src="../imagen/Logo.jpg" alt="Logo" class="w-12 h-12 object-contain mr-4">
                    <div>
                        <h1 class="text-xl font-semibold text-gray-900">Farmamedis</h1>
                        <p class="text-sm text-gray-600"><?php echo $doctorInfo['name']; ?></p>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-sm text-gray-600">Bienvenido, <?php echo $_SESSION['user_name']; ?></span>
                    <a href="../logout.php" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm transition duration-200">
                        <i class="ph-sign-out mr-2"></i>Cerrar Sesión
                    </a>
                </div>
            </div>
        </div>
    </header>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid lg:grid-cols-4 gap-8">
            <!-- Contenido principal -->
            <div class="lg:col-span-3">
                <div class="bg-white rounded-2xl shadow-xl p-8 fade-in">
                    <div class="flex items-center justify-between mb-8">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900">Mis Citas</h2>
                            <p class="text-gray-600 mt-2">Historial de todas sus citas médicas</p>
                        </div>
                        <a href="solicitar_cita.php" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-lg transition duration-200 flex items-center gap-2">
                            <i class="ph-plus"></i>
                            Nueva Cita
                        </a>
                    </div>

                    <?php if (empty($citas)): ?>
                        <div class="text-center py-12">
                            <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="ph-calendar text-gray-400 text-3xl"></i>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">No hay citas registradas</h3>
                            <p class="text-gray-500 mb-6">Aún no ha solicitado ninguna cita médica.</p>
                            <a href="solicitar_cita.php" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-lg transition duration-200">
                                Solicitar mi primera cita
                            </a>
                        </div>
                    <?php else: ?>
                        <!-- Lista de citas -->
                        <div class="space-y-6">
                            <?php foreach ($citas as $cita): ?>
                                <div class="border border-gray-200 rounded-lg p-6 hover:shadow-md transition duration-200">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-3 mb-3">
                                                <h3 class="text-lg font-semibold text-gray-900">
                                                    <?php echo htmlspecialchars($cita['nombre_paciente']); ?>
                                                </h3>
                                                <span class="px-3 py-1 rounded-full text-xs font-medium status-<?php echo $cita['estado']; ?>">
                                                    <?php 
                                                    switch($cita['estado']) {
                                                        case 'pendiente': echo 'Pendiente'; break;
                                                        case 'aprobada': echo 'Aprobada'; break;
                                                        case 'rechazada': echo 'Rechazada'; break;
                                                        default: echo ucfirst($cita['estado']);
                                                    }
                                                    ?>
                                                </span>
                                            </div>
                                            
                                            <div class="grid md:grid-cols-2 gap-4 text-sm text-gray-600">
                                                <div>
                                                    <p><strong>Fecha y Hora:</strong> <?php echo $functions->formatDateTime($cita['fecha']); ?></p>
                                                    <p><strong>Teléfono:</strong> <?php echo htmlspecialchars($cita['telefono_paciente']); ?></p>
                                                </div>
                                                <div>
                                                    <p><strong>Peso:</strong> <?php echo $cita['peso_paciente'] ? $cita['peso_paciente'] . ' kg' : 'No especificado'; ?></p>
                                                    <p><strong>Solicitada por:</strong> <?php echo ucfirst($cita['creada_por']); ?></p>
                                                </div>
                                            </div>
                                            
                                            <?php if (!empty($cita['notas'])): ?>
                                                <div class="mt-3">
                                                    <p class="text-sm"><strong>Notas:</strong></p>
                                                    <p class="text-gray-600 bg-gray-50 p-3 rounded mt-1"><?php echo nl2br(htmlspecialchars($cita['notas'])); ?></p>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <div class="mt-4 text-xs text-gray-500">
                                                <p>Solicitada el: <?php echo $functions->formatDateTime($cita['created_at']); ?></p>
                                            </div>
                                        </div>
                                        
                                        <!-- Acciones según estado -->
                                        <div class="ml-4 flex flex-col gap-2">
                                            <?php if ($cita['estado'] === 'pendiente'): ?>
                                                <span class="text-xs text-yellow-600 bg-yellow-50 px-2 py-1 rounded">
                                                    <i class="ph-clock mr-1"></i>En revisión
                                                </span>
                                            <?php elseif ($cita['estado'] === 'aprobada'): ?>
                                                <span class="text-xs text-green-600 bg-green-50 px-2 py-1 rounded">
                                                    <i class="ph-check-circle mr-1"></i>Confirmada
                                                </span>
                                            <?php elseif ($cita['estado'] === 'rechazada'): ?>
                                                <span class="text-xs text-red-600 bg-red-50 px-2 py-1 rounded">
                                                    <i class="ph-x-circle mr-1"></i>No disponible
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Información del médico -->
                <div class="bg-white rounded-2xl shadow-xl p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <i class="ph-user-md text-indigo-600 mr-2"></i>
                        Información del Médico
                    </h3>
                    <div class="space-y-3">
                        <div>
                            <p class="font-medium text-gray-900"><?php echo $doctorInfo['name']; ?></p>
                            <p class="text-sm text-gray-600"><?php echo $doctorInfo['specialty']; ?></p>
                        </div>
                        <div class="flex items-start">
                            <i class="ph-map-pin text-gray-400 mr-2 mt-1"></i>
                            <p class="text-sm text-gray-600"><?php echo $doctorInfo['address']; ?></p>
                        </div>
                    </div>
                </div>

                <!-- Estadísticas -->
                <div class="bg-white rounded-2xl shadow-xl p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <i class="ph-chart-bar text-indigo-600 mr-2"></i>
                        Resumen de Citas
                    </h3>
                    <div class="space-y-3">
                        <?php
                        $totalCitas = count($citas);
                        $citasPendientes = count(array_filter($citas, fn($c) => $c['estado'] === 'pendiente'));
                        $citasAprobadas = count(array_filter($citas, fn($c) => $c['estado'] === 'aprobada'));
                        $citasRechazadas = count(array_filter($citas, fn($c) => $c['estado'] === 'rechazada'));
                        ?>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Total:</span>
                            <span class="font-semibold text-gray-900"><?php echo $totalCitas; ?></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Pendientes:</span>
                            <span class="font-semibold text-yellow-600"><?php echo $citasPendientes; ?></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Aprobadas:</span>
                            <span class="font-semibold text-green-600"><?php echo $citasAprobadas; ?></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Rechazadas:</span>
                            <span class="font-semibold text-red-600"><?php echo $citasRechazadas; ?></span>
                        </div>
                    </div>
                </div>

                <!-- Estados de citas -->
                <div class="bg-white rounded-2xl shadow-xl p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Estados de Citas</h3>
                    <div class="space-y-3 text-sm">
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 bg-yellow-400 rounded-full"></div>
                            <span class="text-gray-600">Pendiente: En revisión por el médico</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 bg-green-400 rounded-full"></div>
                            <span class="text-gray-600">Aprobada: Cita confirmada</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 bg-red-400 rounded-full"></div>
                            <span class="text-gray-600">Rechazada: No disponible</span>
                        </div>
                    </div>
                </div>

                <!-- Enlaces útiles -->
                <div class="bg-white rounded-2xl shadow-xl p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Enlaces Útiles</h3>
                    <div class="space-y-3">
                        <a href="solicitar_cita.php" class="flex items-center text-indigo-600 hover:text-indigo-700 transition duration-200">
                            <i class="ph-calendar-plus mr-2"></i>
                            Solicitar nueva cita
                        </a>
                        <a href="../logout.php" class="flex items-center text-gray-600 hover:text-gray-700 transition duration-200">
                            <i class="ph-sign-out mr-2"></i>
                            Cerrar sesión
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-white border-t mt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="text-center">
                <p class="text-sm text-gray-500">
                    © 2025 Clínica Saludable. Todos los derechos reservados.
                </p>
                <p class="text-xs text-gray-400 mt-2">
                    <?php echo $doctorInfo['address']; ?>
                </p>
            </div>
        </div>
    </footer>
</body>
</html>
