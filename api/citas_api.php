<?php
/**
 * API de Citas - Gestor de Citas Médicas
 * Maneja todas las operaciones CRUD de citas
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Verificar autenticación
if (!isAuthenticated()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autenticado']);
    exit;
}

// Verificar permisos para operaciones sensibles
function requireAdminPermission() {
    if (!isAdmin()) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Acceso denegado. Se requieren permisos de administrador']);
        exit;
    }
}

// Manejar preflight OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$db = new Database();
$conn = $db->getConnection();
$functions = new Functions();

try {
    $action = $_GET['action'] ?? ($_POST['action'] ?? '');
    
    switch ($action) {
        case 'getAll':
            getAllAppointments($conn);
            break;
            
        case 'getById':
            $id = $_GET['id'] ?? $_POST['id'] ?? null;
            if (!$id) {
                throw new Exception('ID de cita requerido');
            }
            getAppointmentById($conn, $id);
            break;
            
        case 'add':
            requireAdminPermission(); // Solo admin puede agregar citas
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input || !isset($input['appointment'])) {
                throw new Exception('Datos de cita requeridos');
            }
            addAppointment($conn, $functions, $input['appointment']);
            break;
            
        case 'update':
            requireAdminPermission(); // Solo admin puede actualizar citas
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input || !isset($input['id']) || !isset($input['appointment'])) {
                throw new Exception('ID y datos de cita requeridos');
            }
            updateAppointment($conn, $input['id'], $input['appointment']);
            break;
            
        case 'delete':
            requireAdminPermission(); // Solo admin puede eliminar citas
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input || !isset($input['id'])) {
                throw new Exception('ID de cita requerido');
            }
            deleteAppointment($conn, $input['id']);
            break;
            
        case 'getBlockedDays':
            getBlockedDays($conn);
            break;
            
        case 'addBlockedDay':
            requireAdminPermission(); // Solo admin puede bloquear días
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input || !isset($input['blockedDay'])) {
                throw new Exception('Datos de día bloqueado requeridos');
            }
            addBlockedDay($conn, $input['blockedDay']);
            break;
            
        case 'deleteBlockedDay':
            requireAdminPermission(); // Solo admin puede desbloquear días
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input || !isset($input['date'])) {
                throw new Exception('Fecha requerida');
            }
            deleteBlockedDay($conn, $input['date']);
            break;
            
        default:
            throw new Exception('Acción no válida');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} finally {
    $db->closeConnection();
}

/**
 * Obtener todas las citas
 */
function getAllAppointments($conn) {
    $stmt = $conn->prepare("
        SELECT c.*, p.telefono as telefono_paciente, p.peso as peso_paciente, p.correo as correo_paciente,
               u.nombre as nombre_paciente
        FROM citas c
        JOIN pacientes p ON c.paciente_id = p.id
        JOIN usuarios u ON p.usuario_id = u.id
        ORDER BY c.fecha DESC
    ");
    $stmt->execute();
    $appointments = $stmt->fetchAll();
    
    echo json_encode(['success' => true, 'appointments' => $appointments]);
}

/**
 * Obtener cita por ID
 */
function getAppointmentById($conn, $id) {
    $stmt = $conn->prepare("
        SELECT c.*, p.telefono as telefono_paciente, p.peso as peso_paciente, p.correo as correo_paciente,
               u.nombre as nombre_paciente
        FROM citas c
        JOIN pacientes p ON c.paciente_id = p.id
        JOIN usuarios u ON p.usuario_id = u.id
        WHERE c.id = ?
    ");
    $stmt->execute([$id]);
    $appointment = $stmt->fetch();
    
    if (!$appointment) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Cita no encontrada']);
        return;
    }
    
    echo json_encode(['success' => true, 'appointment' => $appointment]);
}

/**
 * Agregar nueva cita
 */
function addAppointment($conn, $functions, $appointmentData) {
    // Validar datos
    if (empty($appointmentData['patientName']) || empty($appointmentData['patientEmail']) || empty($appointmentData['date'])) {
        throw new Exception('Nombre, correo y fecha son requeridos');
    }
    
    // Verificar si se puede agendar
    if (!$functions->canSchedule($appointmentData['date'])) {
        throw new Exception('No se puede agendar en ese horario o día bloqueado');
    }
    
    // Buscar o crear paciente
    $patientId = findOrCreatePatient($conn, $appointmentData);
    
    // Insertar cita
    $stmt = $conn->prepare("
        INSERT INTO citas (paciente_id, fecha, notas, estado, creada_por) 
        VALUES (?, ?, ?, 'pendiente', 'admin')
    ");
    $stmt->execute([
        $patientId,
        $appointmentData['date'],
        $appointmentData['notes'] ?? ''
    ]);
    
    $citaId = $conn->lastInsertId();
    
    // Enviar notificación por email
    if (isset($appointmentData['patientEmail'])) {
        $emailContent = $functions->generateAppointmentEmail(
            $appointmentData['patientName'],
            $appointmentData['date'],
            $appointmentData['notes'] ?? '',
            'pendiente'
        );
        
        $functions->sendEmail(
            $appointmentData['patientEmail'],
            'Confirmación de Cita Médica',
            $emailContent
        );
    }
    
    echo json_encode(['success' => true, 'message' => 'Cita agregada exitosamente', 'id' => $citaId]);
}

/**
 * Actualizar cita existente
 */
function updateAppointment($conn, $id, $appointmentData) {
    // Verificar que la cita existe
    $stmt = $conn->prepare("SELECT * FROM citas WHERE id = ?");
    $stmt->execute([$id]);
    $existingAppointment = $stmt->fetch();
    
    if (!$existingAppointment) {
        throw new Exception('Cita no encontrada');
    }
    
    // Si se está cambiando la fecha, validar
    if (isset($appointmentData['date']) && $appointmentData['date'] !== $existingAppointment['fecha']) {
        $functions = new Functions();
        if (!$functions->canSchedule($appointmentData['date'])) {
            throw new Exception('No se puede agendar en ese horario o día bloqueado');
        }
    }
    
    // Actualizar cita
    $updateFields = [];
    $params = [];
    
    if (isset($appointmentData['fecha'])) {
        $updateFields[] = "fecha = ?";
        $params[] = $appointmentData['fecha'];
    }
    
    if (isset($appointmentData['notas'])) {
        $updateFields[] = "notas = ?";
        $params[] = $appointmentData['notas'];
    }
    
    if (isset($appointmentData['estado'])) {
        $updateFields[] = "estado = ?";
        $params[] = $appointmentData['estado'];
    }
    
    if (empty($updateFields)) {
        throw new Exception('No hay campos para actualizar');
    }
    
    $params[] = $id;
    $sql = "UPDATE citas SET " . implode(", ", $updateFields) . " WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    
    // Si se cambió el estado, enviar email de notificación
    if (isset($appointmentData['estado']) && $appointmentData['estado'] !== $existingAppointment['estado']) {
        // Obtener información del paciente para el email
        $stmt = $conn->prepare("
            SELECT p.correo, u.nombre
            FROM pacientes p
            JOIN usuarios u ON p.usuario_id = u.id
            WHERE p.id = ?
        ");
        $stmt->execute([$existingAppointment['paciente_id']]);
        $patientInfo = $stmt->fetch();
        
        if ($patientInfo) {
            $functions = new Functions();
            $emailContent = $functions->generateAppointmentEmail(
                $patientInfo['nombre'],
                $existingAppointment['fecha'],
                $existingAppointment['notas'],
                $appointmentData['estado']
            );
            
            $functions->sendEmail(
                $patientInfo['correo'],
                'Actualización de Cita Médica',
                $emailContent
            );
        }
    }
    
    echo json_encode(['success' => true, 'message' => 'Cita actualizada exitosamente']);
}

/**
 * Eliminar cita
 */
function deleteAppointment($conn, $id) {
    // Verificar que la cita existe
    $stmt = $conn->prepare("SELECT * FROM citas WHERE id = ?");
    $stmt->execute([$id]);
    $appointment = $stmt->fetch();
    
    if (!$appointment) {
        throw new Exception('Cita no encontrada');
    }
    
    // Eliminar cita
    $stmt = $conn->prepare("DELETE FROM citas WHERE id = ?");
    $stmt->execute([$id]);
    
    echo json_encode(['success' => true, 'message' => 'Cita eliminada exitosamente']);
}

/**
 * Obtener días bloqueados
 */
function getBlockedDays($conn) {
    $stmt = $conn->prepare("SELECT * FROM dias_bloqueados ORDER BY fecha");
    $stmt->execute();
    $blockedDays = $stmt->fetchAll();
    
    echo json_encode(['success' => true, 'blockedDays' => $blockedDays]);
}

/**
 * Agregar día bloqueado
 */
function addBlockedDay($conn, $blockedDayData) {
    if (empty($blockedDayData['fecha']) || empty($blockedDayData['hora_apertura']) || empty($blockedDayData['hora_cierre'])) {
        throw new Exception('Fecha, hora de apertura y cierre son requeridos');
    }
    
    // Verificar que no exista ya
    $stmt = $conn->prepare("SELECT id FROM dias_bloqueados WHERE fecha = ?");
    $stmt->execute([$blockedDayData['fecha']]);
    if ($stmt->rowCount() > 0) {
        throw new Exception('Ese día ya está bloqueado');
    }
    
    // Insertar día bloqueado
    $stmt = $conn->prepare("
        INSERT INTO dias_bloqueados (fecha, hora_apertura, hora_cierre) 
        VALUES (?, ?, ?)
    ");
    $stmt->execute([
        $blockedDayData['fecha'],
        $blockedDayData['hora_apertura'],
        $blockedDayData['hora_cierre']
    ]);
    
    echo json_encode(['success' => true, 'message' => 'Día bloqueado exitosamente']);
}

/**
 * Eliminar día bloqueado
 */
function deleteBlockedDay($conn, $date) {
    $stmt = $conn->prepare("DELETE FROM dias_bloqueados WHERE fecha = ?");
    $stmt->execute([$date]);
    
    if ($stmt->rowCount() === 0) {
        throw new Exception('Día bloqueado no encontrado');
    }
    
    echo json_encode(['success' => true, 'message' => 'Bloqueo eliminado exitosamente']);
}

/**
 * Buscar o crear paciente
 */
function findOrCreatePatient($conn, $appointmentData) {
    // Buscar paciente por correo
    $stmt = $conn->prepare("
        SELECT p.id FROM pacientes p 
        JOIN usuarios u ON p.usuario_id = u.id 
        WHERE u.correo = ?
    ");
    $stmt->execute([$appointmentData['patientEmail']]);
    $patient = $stmt->fetch();
    
    if ($patient) {
        // Actualizar información del paciente existente
        $stmt = $conn->prepare("
            UPDATE pacientes 
            SET telefono = ?, peso = ? 
            WHERE id = ?
        ");
        $stmt->execute([
            $appointmentData['patientPhone'] ?? '',
            $appointmentData['patientWeight'] ?? null,
            $patient['id']
        ]);
        
        return $patient['id'];
    }
    
    // Crear nuevo usuario y paciente
    $conn->beginTransaction();
    
    try {
        // Crear usuario
        $stmt = $conn->prepare("
            INSERT INTO usuarios (nombre, correo, contraseña, telefono, rol) 
            VALUES (?, ?, ?, ?, 'cliente')
        ");
        $hashedPassword = password_hash('temp123', PASSWORD_DEFAULT);
        $stmt->execute([
            $appointmentData['patientName'],
            $appointmentData['patientEmail'],
            $hashedPassword,
            $appointmentData['patientPhone'] ?? ''
        ]);
        
        $userId = $conn->lastInsertId();
        
        // Crear paciente
        $stmt = $conn->prepare("
            INSERT INTO pacientes (usuario_id, telefono, peso, correo) 
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([
            $userId,
            $appointmentData['patientPhone'] ?? '',
            $appointmentData['patientWeight'] ?? null,
            $appointmentData['patientEmail']
        ]);
        
        $patientId = $conn->lastInsertId();
        
        $conn->commit();
        return $patientId;
        
    } catch (Exception $e) {
        $conn->rollBack();
        throw $e;
    }
}
?>
