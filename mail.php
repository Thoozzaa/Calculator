<?php
    // Include library PHPMailer
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;
    use PHPMailer\PHPMailer\SMTP;

    require 'PHPMailer/src/Exception.php';
    require 'PHPMailer/src/PHPMailer.php';
    require 'PHPMailer/src/SMTP.php';

    function mymail($to, $subject, $message){
        $mail = new PHPMailer(true);
        
        try {
            // Server instellingen
            $mail->isSMTP();
            $mail->Host       = 'smtp.sunnus.nl';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'powershell@sunnus.nl';
            $mail->Password   = 'xMVtxxpCDRrUgJMf6qgN';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;
            
            // SSL/TLS instellingen voor lokale ontwikkeling
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );
            
            // Debug uitschakelen (zet op 2 voor debugging)
            $mail->SMTPDebug = 0;
            
            // Afzender
            $mail->setFrom('powershell@sunnus.nl', 'J&N Finance');
            $mail->addAddress($to);
            
            // Content
            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8';
            $mail->Subject = $subject;
            $mail->Body    = $message;
            
            $mail->send();
            return true;
            
        } catch (Exception $e) {
            error_log("PHPMailer Error: " . $mail->ErrorInfo);
            throw new Exception("Email kon niet worden verzonden: " . $mail->ErrorInfo);
        }
    }
?>