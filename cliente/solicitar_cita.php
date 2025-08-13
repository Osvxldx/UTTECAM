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
$error = '';
$success = '';

// Procesar solicitud de cita
if ($_POST && isset($_POST['solicitar_cita'])) {
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        $error = 'Token de seguridad inválido';
    } else {
        $patientName = cleanInput($_POST['patientName']);
        $patientPhone = cleanInput($_POST['patientPhone']);
        $patientWeight = (float)$_POST['patientWeight'];
        $patientEmail = cleanInput($_POST['patientEmail']);
        $date = $_POST['appointmentDate'];
        $notes = cleanInput($_POST['notes']);
        
        // Validaciones
        if (empty($patientName) || empty($patientEmail) || empty($date)) {
            $error = 'Por favor complete todos los campos requeridos';
        } elseif (!$functions->validateEmail($patientEmail)) {
            $error = 'Formato de correo electrónico inválido';
        } elseif (!$functions->validatePhone($patientPhone)) {
            $error = 'Formato de teléfono inválido';
        } elseif (!$functions->validateWeight($patientWeight)) {
            $error = 'Peso debe estar entre 1 y 300 kg';
        } elseif (!$functions->canSchedule($date)) {
            $error = 'No se puede agendar en ese horario o día bloqueado';
        } else {
            // Crear la solicitud de cita
            try {
                $db = new Database();
                $conn = $db->getConnection();
                
                // Buscar o crear paciente
                $patientId = findOrCreatePatient($conn, [
                    'patientName' => $patientName,
                    'patientEmail' => $patientEmail,
                    'patientPhone' => $patientPhone,
                    'patientWeight' => $patientWeight
                ]);
                
                // Insertar cita con estado pendiente
                $stmt = $conn->prepare("
                    INSERT INTO citas (paciente_id, fecha, notas, estado, creada_por) 
                    VALUES (?, ?, ?, 'pendiente', 'cliente')
                ");
                $stmt->execute([$patientId, $date, $notes]);
                
                $citaId = $conn->lastInsertId();
                
                // Enviar email de confirmación al cliente
                $emailContent = $functions->generateAppointmentEmail(
                    $patientName, $date, $notes, 'pendiente'
                );
                
                $functions->sendEmail(
                    $patientEmail,
                    'Confirmación de Solicitud de Cita',
                    $emailContent
                );
                
                // Enviar notificación al admin
                $adminEmailContent = $functions->generateAdminNotificationEmail(
                    $patientName, $patientEmail, $patientPhone, $date, $notes
                );
                
                $functions->sendEmail(
                    ADMIN_EMAIL,
                    'Nueva Solicitud de Cita',
                    $adminEmailContent
                );
                
                $success = 'Solicitud de cita enviada exitosamente. Recibirás una confirmación por email.';
                $_POST = array(); // Limpiar formulario
                
            } catch (Exception $e) {
                $error = 'Error al enviar la solicitud: ' . $e->getMessage();
            }
        }
    }
}

// Función auxiliar para buscar o crear paciente
function findOrCreatePatient($conn, $patientData) {
    // Asociar siempre al usuario logueado
    $userId = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT id FROM pacientes WHERE usuario_id = ?");
    $stmt->execute([$userId]);
    $patient = $stmt->fetch();
    if ($patient) {
        // Actualizar información del paciente existente
        $stmt = $conn->prepare("UPDATE pacientes SET telefono = ?, peso = ?, correo = ? WHERE id = ?");
        $stmt->execute([
            $patientData['patientPhone'],
            $patientData['patientWeight'],
            $patientData['patientEmail'],
            $patient['id']
        ]);
        return $patient['id'];
    }
    // Si no existe paciente, créalo para el usuario logueado
    $stmt = $conn->prepare("INSERT INTO pacientes (usuario_id, telefono, peso, correo) VALUES (?, ?, ?, ?)");
    $stmt->execute([
        $userId,
        $patientData['patientPhone'],
        $patientData['patientWeight'],
        $patientData['patientEmail']
    ]);
    return $conn->lastInsertId();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitar Cita - Gestor de Citas Médicas</title>
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
<body class="min-h-screen bg-gradient-to-br from-indigo-50 to-blue-100">
    <!-- Header -->
    <header class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center">
                    <div class="w-20 h-20 rounded-full overflow-hidden mx-auto mb-4 bg-white">
                <img src="../imagen/Logo.jpg" alt="Logo" class="w-full h-full object-contain">
            </div>

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
        <div class="grid lg:grid-cols-3 gap-8">
            <!-- Formulario de solicitud -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-2xl shadow-xl p-8 fade-in">
                    <div class="text-center mb-8">
                        
                        <h2 class="text-2xl font-bold text-gray-900">Solicitar Cita Médica</h2>
                        <p class="text-gray-600 mt-2">Complete el formulario para solicitar su cita</p>
                    </div>

                    <?php if ($error): ?>
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                            <i class="ph-warning mr-2"></i> <?php echo $error; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                            <i class="ph-check-circle mr-2"></i> <?php echo $success; ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" class="space-y-6">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        
                        <!-- Grid responsivo mejorado para móviles -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                            <div class="sm:col-span-2">
                                <label for="patientName" class="block text-sm font-medium text-gray-700 mb-2">
                                    Nombre Completo <span class="text-red-500">*</span>
                                </label>
                                <input type="text" id="patientName" name="patientName" required
                                       value="<?php echo isset($_POST['patientName']) ? htmlspecialchars($_POST['patientName']) : ''; ?>"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-base"
                                       placeholder="Su nombre completo">
                            </div>

                            <div>
                                <label for="patientPhone" class="block text-sm font-medium text-gray-700 mb-2">
                                    Teléfono <span class="text-red-500">*</span>
                                </label>
                                <input type="tel" id="patientPhone" name="patientPhone" required
                                       value="<?php echo isset($_POST['patientPhone']) ? htmlspecialchars($_POST['patientPhone']) : ''; ?>"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-base"
                                       placeholder="222-123-4567">
                                <p class="text-xs text-gray-500 mt-1">Formato: XXX-XXX-XXXX</p>
                            </div>

                            <div>
                                <label for="patientWeight" class="block text-sm font-medium text-gray-700 mb-2">
                                    Peso (kg) <span class="text-red-500">*</span>
                                </label>
                                <input type="number" id="patientWeight" name="patientWeight" required min="1" max="300" step="0.1"
                                       value="<?php echo isset($_POST['patientWeight']) ? htmlspecialchars($_POST['patientWeight']) : ''; ?>"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-base"
                                       placeholder="70.5">
                                <p class="text-xs text-gray-500 mt-1">Entre 1 y 300 kg</p>
                            </div>

                            <div class="sm:col-span-2">
                                <label for="patientEmail" class="block text-sm font-medium text-gray-700 mb-2">
                                    Correo Electrónico <span class="text-red-500">*</span>
                                </label>
                                <input type="email" id="patientEmail" name="patientEmail" required
                                       value="<?php echo isset($_POST['patientEmail']) ? htmlspecialchars($_POST['patientEmail']) : ''; ?>"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-base"
                                       placeholder="su@email.com">
                                <p class="text-xs text-gray-500 mt-1">Recibirá confirmaciones por este correo</p>
                            </div>

                            <div class="sm:col-span-2">
                                <label for="appointmentDate" class="block text-sm font-medium text-gray-700 mb-2">
                                    Fecha y Hora de la Cita <span class="text-red-500">*</span>
                                </label>
                                <input type="datetime-local" id="appointmentDate" name="appointmentDate" required
                                       value="<?php echo isset($_POST['appointmentDate']) ? htmlspecialchars($_POST['appointmentDate']) : ''; ?>"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-base">
                                <div class="text-sm text-gray-600 mt-2 p-3 bg-blue-50 rounded-lg">
                                    <p class="font-medium">📅 Horario de Atención:</p>
                                    <ul class="mt-1 space-y-1">
                                        <li>• Lunes a Viernes: 8:00 AM - 5:00 PM</li>
                                        <li>• Sábados: 8:00 AM - 12:00 PM</li>
                                        <li>• Domingos: Cerrado</li>
                                    </ul>
                                </div>
                            </div>

                            <div class="sm:col-span-2">
                                <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                                    Motivo / Notas
                                </label>
                                <textarea id="notes" name="notes" rows="4"
                                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-base resize-none"
                                          placeholder="Describa el motivo de su consulta, síntomas, o cualquier información adicional que considere importante..."><?php echo isset($_POST['notes']) ? htmlspecialchars($_POST['notes']) : ''; ?></textarea>
                                <p class="text-xs text-gray-500 mt-1">Opcional pero recomendado para mejor atención</p>
                            </div>
                        </div>

                        <!-- Botones responsivos mejorados -->
                        <div class="flex flex-col sm:flex-row gap-3 pt-6">
                            <button type="submit" name="solicitar_cita" 
                                    class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-4 rounded-lg text-base font-medium transition duration-200 shadow-lg hover:shadow-xl flex items-center justify-center gap-2">
                                <i class="ph-paper-plane-right text-lg"></i>
                                <span class="hidden sm:inline">Enviar Solicitud de Cita</span>
                                <span class="sm:hidden">Solicitar Cita</span>
                            </button>
                            <a href="mis_citas.php" 
                               class="flex-1 bg-gray-500 hover:bg-gray-600 text-white px-6 py-4 rounded-lg text-base font-medium transition duration-200 text-center flex items-center justify-center gap-2">
                                <i class="ph-list text-lg"></i>
                                <span class="hidden sm:inline">Ver Mis Citas</span>
                                <span class="sm:hidden">Mis Citas</span>
                            </a>
                        </div>

                        <!-- Información adicional -->
                        <div class="mt-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                            <h4 class="font-medium text-green-800 mb-2">ℹ️ Información Importante</h4>
                            <ul class="text-sm text-green-700 space-y-1">
                                <li>• Recibirá una confirmación por email inmediatamente</li>
                                <li>• El médico revisará su solicitud en las próximas 24 horas</li>
                                <li>• Se le notificará cuando la cita sea aprobada o rechazada</li>
                                <li>• Puede cancelar o modificar su cita desde "Mis Citas"</li>
                            </ul>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Información lateral -->
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
                        <div class="flex items-start">
                            <i class="ph-phone text-gray-400 mr-2 mt-1"></i>
                            <p class="text-sm text-gray-600"><?php echo $doctorInfo['phone']; ?></p>
                        </div>
                        
                    </div>
                </div>

                <!-- Instrucciones -->
                <div class="bg-blue-50 rounded-2xl p-6">
                    <h3 class="text-lg font-semibold text-blue-900 mb-4 flex items-center">
                        <i class="ph-info text-blue-600 mr-2"></i>
                        Instrucciones
                    </h3>
                    <ul class="text-sm text-blue-800 space-y-2">
                        <li class="flex items-start">
                            <i class="ph-check-circle text-blue-600 mr-2 mt-0.5"></i>
                            -Complete todos los campos marcados con *
                        </li>
                        <li class="flex items-start">
                            <i class="ph-check-circle text-blue-600 mr-2 mt-0.5"></i>
                            -Seleccione una fecha y hora disponible
                        </li>
                        <li class="flex items-start">
                            <i class="ph-check-circle text-blue-600 mr-2 mt-0.5"></i>
                            -Recibirá confirmación por email
                        </li>
                        
                    </ul>
                </div>

                <!-- Enlaces útiles -->
                <div class="bg-white rounded-2xl shadow-xl p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Enlaces Útiles</h3>
                    <div class="space-y-3">
                        <a href="mis_citas.php" class="flex items-center text-indigo-600 hover:text-indigo-700 transition duration-200">
                            <i class="ph-calendar mr-2"></i>
                            Ver mis citas
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
