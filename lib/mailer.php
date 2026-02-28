<?php
/**
 * Envío de correos por SMTP (sin dependencias externas)
 */

function smtp_send_email($to, $subject, $body, $isHtml = true) {
    // Cargar configuración de env si no está disponible
    if (!function_exists('env')) {
        $envPath = __DIR__ . '/env.php';
        if (file_exists($envPath)) require_once $envPath;
    }

    $host = env('SMTP_HOST');
    $port = env('SMTP_PORT', 587); // 465 for SSL, 587 for TLS usually
    $user = env('SMTP_USER');
    $pass = env('SMTP_PASS');
    $secure = env('SMTP_SECURE', 'tls'); // ssl or tls
    $from = env('SMTP_FROM', $user);
    $fromName = env('SMTP_FROM_NAME', 'Medios con Valor');

    if (!$host || !$user || !$pass) {
        error_log("SMTP config missing");
        return false;
    }

    $timeout = 30;
    $errno = 0;
    $errstr = '';

    $protocol = '';
    if ($secure === 'ssl') {
        $protocol = 'ssl://';
    }
    
    // Conectar
    $fp = @fsockopen($protocol . $host, $port, $errno, $errstr, $timeout);
    if (!$fp) {
        error_log("SMTP Connect failed: $errstr ($errno)");
        return false;
    }

    stream_set_timeout($fp, $timeout);

    // Helper para leer respuesta
    $read = function() use ($fp) {
        $s = '';
        while (($line = fgets($fp, 512)) !== false) {
            $s .= $line;
            if (substr($line, 3, 1) == ' ') break;
        }
        return $s;
    };

    // Helper para escribir comando
    $write = function($cmd) use ($fp) {
        fwrite($fp, $cmd . "\r\n");
    };

    // Handshake inicial
    $s = $read();
    if (substr($s, 0, 3) != '220') { fclose($fp); return false; }

    // EHLO
    $serverName = $_SERVER['SERVER_NAME'] ?? gethostname() ?? 'localhost';
    $write('EHLO ' . $serverName);
    $s = $read();
    if (substr($s, 0, 3) != '250') { fclose($fp); return false; }

    // STARTTLS si es necesario (y no es SSL implícito)
    if ($secure === 'tls') {
        $write('STARTTLS');
        $s = $read();
        if (substr($s, 0, 3) != '220') { fclose($fp); return false; }
        
        if (!stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
            fclose($fp); return false;
        }
        
        // EHLO de nuevo tras TLS
        $write('EHLO ' . $_SERVER['SERVER_NAME']);
        $s = $read();
        if (substr($s, 0, 3) != '250') { fclose($fp); return false; }
    }

    // AUTH LOGIN
    $write('AUTH LOGIN');
    $s = $read();
    if (substr($s, 0, 3) != '334') { fclose($fp); return false; }

    $write(base64_encode($user));
    $s = $read();
    if (substr($s, 0, 3) != '334') { fclose($fp); return false; }

    $write(base64_encode($pass));
    $s = $read();
    if (substr($s, 0, 3) != '235') { fclose($fp); return false; }

    // MAIL FROM
    $write("MAIL FROM: <$from>");
    $s = $read();
    if (substr($s, 0, 3) != '250') { fclose($fp); return false; }

    // RCPT TO (Múltiples destinatarios)
    $recipients = is_array($to) ? $to : explode(',', $to);
    $validRecipients = [];
    foreach ($recipients as $email) {
        $email = trim($email);
        if (empty($email)) continue;
        $write("RCPT TO: <$email>");
        $s = $read();
        if (substr($s, 0, 3) == '250') {
            $validRecipients[] = $email;
        }
    }

    if (empty($validRecipients)) {
        $write('QUIT');
        fclose($fp);
        return false;
    }

    // DATA
    $write('DATA');
    $s = $read();
    if (substr($s, 0, 3) != '354') { fclose($fp); return false; }

    // Headers
    $headers = [];
    $headers[] = "Date: " . date('r');
    $headers[] = "From: =?UTF-8?B?" . base64_encode($fromName) . "?= <$from>";
    // To header is display only, but good practice
    $headers[] = "To: " . implode(', ', $validRecipients);
    $headers[] = "Subject: =?UTF-8?B?" . base64_encode($subject) . "?=";
    $headers[] = "MIME-Version: 1.0";
    if ($isHtml) {
        $headers[] = "Content-Type: text/html; charset=UTF-8";
    } else {
        $headers[] = "Content-Type: text/plain; charset=UTF-8";
    }

    $write(implode("\r\n", $headers) . "\r\n");
    $write($body);
    $write("\r\n.");

    $s = $read();
    $success = (substr($s, 0, 3) == '250');

    $write('QUIT');
    fclose($fp);

    return $success;
}
