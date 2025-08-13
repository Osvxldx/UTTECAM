
<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

if (!defined('SMTP_FROM_EMAIL')) {
    define('SMTP_FROM_EMAIL', 'TU_CORREO@gmail.com'); // Cambia esto
}
if (!defined('SMTP_FROM_NAME')) {
    define('SMTP_FROM_NAME', 'Nombre del Remitente'); // Cambia esto
}

function sendSMTPMail($to, $subject, $body, $fromEmail = SMTP_FROM_EMAIL, $fromName = SMTP_FROM_NAME) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'TU_CORREO@gmail.com'; // Cambia esto
        $mail->Password = 'TU_CONTRASEÑA_O_APP_PASSWORD'; // Cambia esto
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom($fromEmail, $fromName);
        $mail->addAddress($to);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;

        $mail->send();
        error_log("Email enviado exitosamente a: $to");
        return true;
    } catch (Exception $e) {
        error_log("Error enviando correo: {$mail->ErrorInfo}");
        return false;
    }
}

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
        return sendSMTPMail($to, $subject, $message, $from, SMTP_FROM_NAME);
    }

    /**
     * Generar plantilla de correo para confirmación de cita
     */
    public function generateAppointmentEmail($patientName, $appointmentDate, $notes, $status = 'pendiente') {
        $date = $this->formatDateTime($appointmentDate);
        $statusText = '';
        $statusMessage = '';
        switch($status) {
            case 'pendiente':
                $statusText = '⏳ Pendiente de Aprobación';
                $statusMessage = 'Su cita está pendiente de aprobación. Recibirá una notificación cuando sea revisada por el médico.';
                break;
            case 'aprobada':
                $statusText = '✅ Aprobada';
                $statusMessage = 'Su cita ha sido aprobada. Por favor, llegue 10 minutos antes de la hora programada.';
                break;
            case 'rechazada':
                $statusText = '❌ Rechazada';
                $statusMessage = 'Su cita ha sido rechazada. Por favor, contacte al consultorio para más información o solicite una nueva fecha.';
                break;
        }
        $html = "
        <html>
        <head>
            <title>Confirmación de Cita</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
                .container { max-width: 600px; margin: 0 auto; background: #f9fafb; }
                .header { background: #2563eb; color: white; padding: 30px; text-align: center; }
                .header h1 { margin: 0; font-size: 24px; }
                .header p { margin: 10px 0 0 0; opacity: 0.9; }
                .content { background: white; padding: 30px; margin: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
                .status-badge { font-size: 18px; font-weight: bold; margin-bottom: 10px; }
                .details { background: #f0f9ff; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #2563eb; }
                .details h3 { margin-top: 0; color: #2563eb; }
                .details p { margin: 8px 0; }
                .important { background: #fef3c7; border: 1px solid #fde68a; padding: 20px; border-radius: 8px; margin: 20px 0; }
                .footer { text-align: center; margin: 20px; color: #6b7280; font-size: 14px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>📅 Confirmación de Cita</h1>
                    <p>{$statusText}</p>
                </div>
                <div class='content'>
                    <div class='status-badge'>{$statusText}</div>
                    <p>{$statusMessage}</p>
                    <div class='details'>
                        <h3>📋 Detalles de la Cita</h3>
                        <p><strong>Paciente:</strong> {$patientName}</p>
                        <p><strong>Fecha y Hora:</strong> {$date}</p>
                        <p><strong>Médico:</strong> " . DOCTOR_NAME . "</p>
                        <p><strong>Especialidad:</strong> " . DOCTOR_SPECIALTY . "</p>
                        <p><strong>Dirección:</strong> " . DOCTOR_ADDRESS . "</p>";
        if ($notes) {
            $html .= "<p><strong>Notas:</strong> {$notes}</p>";
        }
        $html .= "</div>";
        if ($status === 'aprobada') {
            $html .= "
            <div class='important'>
                <h4>⚠️ Información Importante</h4>
                <ul style='margin: 10px 0; padding-left: 20px;'>
                    <li>Llegue 10 minutos antes de la hora programada</li>
                    <li>Traiga identificación oficial</li>
                    <li>Si no puede asistir, cancele con al menos 24 horas de anticipación</li>
                </ul>
            </div>";
        }
        $html .= "
                    <div class='contact-info'>
                        <h4>📞 Información de Contacto</h4>
                        <p><strong>Teléfono:</strong> " . (defined('DOCTOR_PHONE') ? DOCTOR_PHONE : 'No disponible') . "</p>
                        <p><strong>Horario de Atención:</strong> Lunes a Viernes de 8:00 AM a 5:00 PM</p>
                    </div>
                    <p>Si tiene alguna pregunta, no dude en contactarnos.</p>
                    <p>Atentamente,<br><strong>Equipo de " . DOCTOR_NAME . "</strong></p>
                </div>
                <div class='footer'>
                    <p>Este es un mensaje automático, por favor no responda a este correo.</p>
                    <p>© " . date('Y') . " Clínica Saludable. Todos los derechos reservados.</p>
                </div>
            </div>
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
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
                .container { max-width: 600px; margin: 0 auto; background: #f9fafb; }
                .header { background: #dc2626; color: white; padding: 30px; text-align: center; }
                .header h1 { margin: 0; font-size: 24px; }
                .header p { margin: 10px 0 0 0; opacity: 0.9; }
                .content { background: white; padding: 30px; margin: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
                .alert { background: #fef2f2; border: 1px solid #fecaca; padding: 20px; border-radius: 8px; margin: 20px 0; }
                .patient-info { background: #f0f9ff; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #0ea5e9; }
                .patient-info h3 { margin-top: 0; color: #0369a1; }
                .patient-info p { margin: 8px 0; }
                .action-buttons { background: #f0fdf4; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #22c55e; }
                .action-buttons h3 { margin-top: 0; color: #15803d; }
                .footer { text-align: center; margin: 20px; color: #6b7280; font-size: 14px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🚨 Nueva Solicitud de Cita</h1>
                    <p>Requiere revisión inmediata</p>
                </div>
                <div class='content'>
                    <div class='alert'>
                        <h3 style='margin-top: 0; color: #dc2626;'>⚠️ Atención Administrador</h3>
                        <p>Se ha recibido una nueva solicitud de cita que requiere su revisión y aprobación.</p>
                    </div>
                    
                    <div class='patient-info'>
                        <h3>👤 Información del Paciente</h3>
                        <p><strong>Nombre:</strong> {$patientName}</p>
                        <p><strong>Correo Electrónico:</strong> {$patientEmail}</p>
                        <p><strong>Teléfono:</strong> {$patientPhone}</p>
                        <p><strong>Fecha y Hora Solicitada:</strong> {$date}</p>";
        
        if ($notes) {
            $html .= "<p><strong>Motivo/Notas:</strong> {$notes}</p>";
        }
        
        $html .= "</div>
                    
                    <div class='action-buttons'>
                        <h3>✅ Acciones Requeridas</h3>
                        <p>Por favor, revise y tome una de las siguientes acciones desde el panel de administración:</p>
                        <ul style='margin: 10px 0; padding-left: 20px;'>
                            <li><strong>Aprobar:</strong> La cita se confirma y se envía notificación al paciente</li>
                            <li><strong>Rechazar:</strong> La cita se rechaza y se notifica al paciente</li>
                            <li><strong>Modificar:</strong> Cambiar fecha/hora si es necesario</li>
                        </ul>
                        <p><strong>Enlace al Panel:</strong> <a href='http://" . $_SERVER['HTTP_HOST'] . "/admin/dashboard.php' style='color: #15803d;'>Acceder al Dashboard</a></p>
                    </div>
                    
                    <p>Esta notificación se genera automáticamente. Por favor, responda a la brevedad posible.</p>
                    <p>Atentamente,<br><strong>Sistema de Gestión de Citas</strong></p>
                </div>
                <div class='footer'>
                    <p>Este es un mensaje automático del sistema.</p>
                    <p>© " . date('Y') . " Clínica Saludable. Todos los derechos reservados.</p>
                </div>
            </div>
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
            'phone' => DOCTOR_PHONE,
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
