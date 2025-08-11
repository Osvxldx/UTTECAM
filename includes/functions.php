<?php
/**
 * Funciones generales del sistema
 * Gestor de Citas Médicas
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

class Functions {
    private $db;
    private $conn;

    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->getConnection();
    }

    /**
     * Formatear fecha y hora para mostrar
     */
    public function formatDateTime($dateTime) {
        $date = new DateTime($dateTime);
        return $date->format('d/m/Y H:i');
    }

    /**
     * Formatear solo fecha
     */
    public function formatDate($date) {
        $dateObj = new DateTime($date);
        return $dateObj->format('d/m/Y');
    }

    /**
     * Formatear solo hora
     */
    public function formatTime($time) {
        $timeObj = new DateTime($time);
        return $timeObj->format('H:i');
    }

    /**
     * Obtener estadísticas de citas
     */
    public function getAppointmentStats() {
        try {
            $today = date('Y-m-d');
            $startWeek = date('Y-m-d', strtotime('monday this week'));
            $endWeek = date('Y-m-d', strtotime('sunday this week'));
            $startMonth = date('Y-m-01');
            $endMonth = date('Y-m-t');

            // Citas de hoy
            $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM citas WHERE DATE(fecha) = ?");
            $stmt->execute([$today]);
            $todayCount = $stmt->fetch()['count'];

            // Citas de esta semana
            $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM citas WHERE DATE(fecha) BETWEEN ? AND ?");
            $stmt->execute([$startWeek, $endWeek]);
            $weekCount = $stmt->fetch()['count'];

            // Citas de este mes
            $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM citas WHERE DATE(fecha) BETWEEN ? AND ?");
            $stmt->execute([$startMonth, $endMonth]);
            $monthCount = $stmt->fetch()['count'];

            return [
                'today' => $todayCount,
                'week' => $weekCount,
                'month' => $monthCount
            ];
        } catch (PDOException $e) {
            return ['today' => 0, 'week' => 0, 'month' => 0];
        }
    }

    /**
     * Verificar si un día está bloqueado
     */
    public function isBlockedDay($date) {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM dias_bloqueados WHERE fecha = ?");
            $stmt->execute([$date]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Verificar si se puede agendar en una fecha/hora
     */
    public function canSchedule($dateTime) {
        $date = date('Y-m-d', strtotime($dateTime));
        $time = date('H:i:s', strtotime($dateTime));
        
        // Verificar si el día está bloqueado
        $blockedDay = $this->isBlockedDay($date);
        
        if ($blockedDay) {
            // Si está bloqueado, verificar horario personalizado
            return $time >= $blockedDay['hora_apertura'] && $time <= $blockedDay['hora_cierre'];
        } else {
            // Horario estándar
            return $time >= DEFAULT_OPEN_TIME && $time <= DEFAULT_CLOSE_TIME;
        }
    }

    /**
     * Obtener horarios disponibles para una fecha
     */
    public function getAvailableSlots($date, $duration = 30) {
        $slots = [];
        $blockedDay = $this->isBlockedDay($date);
        
        if ($blockedDay) {
            $start = $blockedDay['hora_apertura'];
            $end = $blockedDay['hora_cierre'];
        } else {
            $start = DEFAULT_OPEN_TIME;
            $end = DEFAULT_CLOSE_TIME;
        }

        $startTime = strtotime($start);
        $endTime = strtotime($end);
        
        for ($time = $startTime; $time <= $endTime - ($duration * 60); $time += ($duration * 60)) {
            $timeSlot = date('H:i', $time);
            $dateTime = $date . ' ' . $timeSlot . ':00';
            
            if ($this->canSchedule($dateTime)) {
                $slots[] = $timeSlot;
            }
        }

        return $slots;
    }

    /**
     * Enviar correo de notificación
     */
    public function sendEmail($to, $subject, $message, $from = null) {
        if ($from === null) {
            $from = SMTP_FROM_EMAIL;
        }

        $headers = "From: " . SMTP_FROM_NAME . " <" . $from . ">\r\n";
        $headers .= "Reply-To: " . $from . "\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

        return mail($to, $subject, $message, $headers);
    }

    /**
     * Generar plantilla de correo para confirmación de cita
     */
    public function generateAppointmentEmail($patientName, $appointmentDate, $notes, $status = 'pendiente') {
        $date = $this->formatDateTime($appointmentDate);
        
        $html = "
        <html>
        <head>
            <title>Confirmación de Cita Médica</title>
        </head>
        <body>
            <h2>Clínica Saludable</h2>
            <h3>Confirmación de Cita Médica</h3>
            <p>Estimado/a <strong>{$patientName}</strong>,</p>
            <p>Su cita médica ha sido <strong>{$status}</strong> con los siguientes detalles:</p>
            <ul>
                <li><strong>Fecha y Hora:</strong> {$date}</li>
                <li><strong>Médico:</strong> " . DOCTOR_NAME . "</li>
                <li><strong>Especialidad:</strong> " . DOCTOR_SPECIALTY . "</li>
                <li><strong>Dirección:</strong> " . DOCTOR_ADDRESS . "</li>
            </ul>";
        
        if ($notes) {
            $html .= "<p><strong>Notas:</strong> {$notes}</p>";
        }
        
        $html .= "
            <p>Si tiene alguna pregunta, no dude en contactarnos.</p>
            <p>Atentamente,<br>Equipo de " . DOCTOR_NAME . "</p>
        </body>
        </html>";
        
        return $html;
    }

    /**
     * Generar plantilla de correo para notificación al admin
     */
    public function generateAdminNotificationEmail($patientName, $patientEmail, $patientPhone, $appointmentDate, $notes) {
        $date = $this->formatDateTime($appointmentDate);
        
        $html = "
        <html>
        <head>
            <title>Nueva Solicitud de Cita</title>
        </head>
        <body>
            <h2>Nueva Solicitud de Cita Médica</h2>
            <p>Se ha recibido una nueva solicitud de cita con los siguientes detalles:</p>
            <ul>
                <li><strong>Paciente:</strong> {$patientName}</li>
                <li><strong>Correo:</strong> {$patientEmail}</li>
                <li><strong>Teléfono:</strong> {$patientPhone}</li>
                <li><strong>Fecha y Hora Solicitada:</strong> {$date}</li>
                <li><strong>Notas:</strong> {$notes}</li>
            </ul>
            <p>Por favor, revise y apruebe o rechace esta solicitud desde el panel de administración.</p>
        </body>
        </html>";
        
        return $html;
    }

    /**
     * Validar formato de teléfono mexicano
     */
    public function validatePhone($phone) {
        // Formato: XXX-XXX-XXXX o XXXXXXXXXX
        $pattern = '/^(\d{3}[-.\s]?\d{3}[-.\s]?\d{4}|\d{10})$/';
        return preg_match($pattern, $phone);
    }

    /**
     * Validar formato de correo electrónico
     */
    public function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    /**
     * Validar peso (entre 1 y 300 kg)
     */
    public function validateWeight($weight) {
        return is_numeric($weight) && $weight >= 1 && $weight <= 300;
    }

    /**
     * Obtener información del médico
     */
    public function getDoctorInfo() {
        return [
            'name' => DOCTOR_NAME,
            'specialty' => DOCTOR_SPECIALTY,
            'address' => DOCTOR_ADDRESS
        ];
    }

    /**
     * Destructor para cerrar conexión
     */
    public function __destruct() {
        $this->db->closeConnection();
    }
}
?>
