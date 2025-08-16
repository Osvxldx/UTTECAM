<?php
require_once __DIR__ . '/../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class EmailService {
    private $smtp_host = 'smtp.gmail.com';
    private $smtp_port = 587;
    private $smtp_user = 'doctorsito.2004.4002@gmail.com';
    private $smtp_pass = 'zztf ysap nsue vrbe';  
    private $from_email = 'doctorsito.2004.4002@gmail.com';
    private $from_name = 'Dr. Merdardo García Campos';
    
    public function enviarCorreo($destinatario, $asunto, $mensaje, $nombre_destinatario = '') {
        $mail = new PHPMailer(true);
        
        try {
            // Configuración del servidor
            $mail->isSMTP();
            $mail->Host       = $this->smtp_host;
            $mail->SMTPAuth   = true;
            $mail->Username   = $this->smtp_user;
            $mail->Password   = $this->smtp_pass;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = $this->smtp_port;
            
            // Codificación
            $mail->CharSet = 'UTF-8';
            
            // Configuración del remitente
            $mail->setFrom($this->from_email, $this->from_name);
            
            // Destinatario
            if ($nombre_destinatario) {
                $mail->addAddress($destinatario, $nombre_destinatario);
            } else {
                $mail->addAddress($destinatario);
            }
            
            // Contenido
            $mail->isHTML(true);
            $mail->Subject = $asunto;
            $mail->Body    = $mensaje;
            
            $mail->send();
            return ['success' => true, 'message' => 'Correo enviado correctamente'];
            
        } catch (Exception $e) {
            error_log("Error PHPMailer: " . $mail->ErrorInfo);
            return ['success' => false, 'message' => 'Error al enviar: ' . $mail->ErrorInfo];
        }
    }
    
    // Template para solicitud de cita (cliente)
    public function enviarConfirmacionSolicitud($email_cliente, $nombre_cliente, $fecha_cita, $notas = '') {
        $asunto = "Solicitud de cita recibida - Dr. García";
        
        $mensaje = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; color: #333; }
                .header { background: #4f46e5; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; }
                .footer { background: #f8f9fa; padding: 15px; text-align: center; color: #666; }
            </style>
        </head>
        <body>
            <div class='header'>
                <h1>Dr. Merdardo García Campos</h1>
                <p>Médico cirujano y partero</p>
            </div>
            <div class='content'>
                <h2>Hola {$nombre_cliente},</h2>
                <p>Hemos recibido su solicitud de cita médica con los siguientes datos:</p>
                <ul>
                    <li><strong>Fecha solicitada:</strong> {$fecha_cita}</li>
                    <li><strong>Notas:</strong> " . ($notas ?: 'Ninguna') . "</li>
                </ul>
                <p>Su solicitud está <strong>pendiente de aprobación</strong>. Le enviaremos otro correo una vez que el doctor confirme la disponibilidad.</p>
                <p>Gracias por confiar en nosotros.</p>
            </div>
            <div class='footer'>
                <p>📍 7 oriente 406, Tecamachalco, Puebla</p>
                <p>Este es un mensaje automático, no responder a este correo.</p>
            </div>
        </body>
        </html>";
        
        return $this->enviarCorreo($email_cliente, $asunto, $mensaje, $nombre_cliente);
    }
    
    // Template para notificar al admin
    public function notificarNuevaSolicitud($nombre_cliente, $email_cliente, $telefono, $fecha_cita, $notas = '') {
        $admin_email = $this->smtp_user; // Enviar al mismo email configurado
        $asunto = "🔔 Nueva solicitud de cita médica";
        
        $mensaje = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; color: #333; }
                .header { background: #dc2626; color: white; padding: 15px; text-align: center; }
                .content { padding: 20px; }
                .datos { background: #f1f5f9; padding: 15px; border-left: 4px solid #4f46e5; }
            </style>
        </head>
        <body>
            <div class='header'>
                <h2>Nueva Solicitud de Cita</h2>
            </div>
            <div class='content'>
                <p>Se ha recibido una nueva solicitud de cita médica:</p>
                <div class='datos'>
                    <p><strong>Cliente:</strong> {$nombre_cliente}</p>
                    <p><strong>Email:</strong> {$email_cliente}</p>
                    <p><strong>Teléfono:</strong> {$telefono}</p>
                    <p><strong>Fecha solicitada:</strong> {$fecha_cita}</p>
                    <p><strong>Notas:</strong> " . ($notas ?: 'Ninguna') . "</p>
                </div>
                <p>⏰ <strong>Acción requerida:</strong> Ingrese al sistema para aprobar o rechazar esta solicitud.</p>
                <p><a href='http://localhost/integradora/UTTECAM/admin/dashboard.php' style='background: #4f46e5; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Ver Dashboard</a></p>
            </div>
        </body>
        </html>";
        
        return $this->enviarCorreo($admin_email, $asunto, $mensaje);
    }
    
    // Template para cita aprobada
    public function enviarCitaAprobada($email_cliente, $nombre_cliente, $fecha_cita) {
        $asunto = "✅ Cita CONFIRMADA - Dr. García";
        
        $mensaje = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; color: #333; }
                .header { background: #16a34a; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; }
                .confirmed { background: #dcfce7; border: 2px solid #16a34a; padding: 15px; border-radius: 8px; }
                .footer { background: #f8f9fa; padding: 15px; text-align: center; color: #666; }
            </style>
        </head>
        <body>
            <div class='header'>
                <h1>¡Cita Confirmada!</h1>
            </div>
            <div class='content'>
                <h2>Hola {$nombre_cliente},</h2>
                <div class='confirmed'>
                    <p>✅ <strong>Su cita ha sido APROBADA</strong></p>
                    <p><strong>Fecha y hora:</strong> {$fecha_cita}</p>
                    <p><strong>Médico:</strong> Dr. Merdardo García Campos</p>
                    <p><strong>Ubicación:</strong> 7 oriente 406, Tecamachalco, Puebla</p>
                </div>
                <h3>Recomendaciones:</h3>
                <ul>
                    <li>Llegue 10 minutos antes de su cita</li>
                    <li>Traiga identificación oficial</li>
                    <li>Si tiene estudios médicos previos, tráigalos</li>
                </ul>
                <p>Si necesita cancelar o reprogramar, contáctenos con al menos 24 horas de anticipación.</p>
            </div>
            <div class='footer'>
                <p>📍 7 oriente 406, Tecamachalco, Puebla</p>
                <p>Gracias por confiar en nosotros</p>
            </div>
        </body>
        </html>";
        
        return $this->enviarCorreo($email_cliente, $asunto, $mensaje, $nombre_cliente);
    }
    
    // Template para cita rechazada
    public function enviarCitaRechazada($email_cliente, $nombre_cliente, $fecha_cita, $motivo = '') {
        $asunto = "❌ Solicitud de cita no aprobada - Dr. García";
        
        $mensaje = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; color: #333; }
                .header { background: #dc2626; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; }
                .rejected { background: #fef2f2; border: 2px solid #dc2626; padding: 15px; border-radius: 8px; }
                .footer { background: #f8f9fa; padding: 15px; text-align: center; color: #666; }
            </style>
        </head>
        <body>
            <div class='header'>
                <h1>Solicitud de Cita</h1>
            </div>
            <div class='content'>
                <h2>Hola {$nombre_cliente},</h2>
                <div class='rejected'>
                    <p>❌ <strong>Su solicitud de cita no pudo ser aprobada</strong></p>
                    <p><strong>Fecha solicitada:</strong> {$fecha_cita}</p>
                    " . ($motivo ? "<p><strong>Motivo:</strong> {$motivo}</p>" : "") . "
                </div>
                <p>Le sugerimos:</p>
                <ul>
                    <li>Solicitar una nueva cita en fechas diferentes</li>
                    <li>Contactarnos directamente para encontrar disponibilidad</li>
                </ul>
                <p>Lamentamos las molestias y esperamos poder atenderle pronto.</p>
            </div>
            <div class='footer'>
                <p>📍 7 oriente 406, Tecamachalco, Puebla</p>
                <p>Para consultas: [TELÉFONO_CONTACTO]</p>
            </div>
        </body>
        </html>";
        
        return $this->enviarCorreo($email_cliente, $asunto, $mensaje, $nombre_cliente);
    }
}

// Función global fácil de usar
function enviar_email($tipo, $datos) {
    $emailService = new EmailService();
    
    switch($tipo) {
        case 'solicitud_cliente':
            return $emailService->enviarConfirmacionSolicitud(
                $datos['email'], $datos['nombre'], $datos['fecha'], $datos['notas']
            );
            
        case 'notificar_admin':
            return $emailService->notificarNuevaSolicitud(
                $datos['nombre'], $datos['email'], $datos['telefono'], $datos['fecha'], $datos['notas']
            );
            
        case 'cita_aprobada':
            return $emailService->enviarCitaAprobada(
                $datos['email'], $datos['nombre'], $datos['fecha']
            );
            
        case 'cita_rechazada':
            return $emailService->enviarCitaRechazada(
                $datos['email'], $datos['nombre'], $datos['fecha'], $datos['motivo']
            );
            
        default:
            return ['success' => false, 'message' => 'Tipo de email no válido'];
    }
}
?>