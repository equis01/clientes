<?php
ini_set('display_errors','0');
error_reporting(E_ERROR|E_PARSE);
header('Content-Type: application/json; charset=utf-8');
session_start();
require_once dirname(__DIR__, 3).'/lib/env.php';
function norm($s){ return trim((string)$s); }
function json_store_path(){ $dir=dirname(__DIR__, 3).'/data'; if(!is_dir($dir)){ @mkdir($dir,0777,true); } return $dir.'/contract_requests.json'; }

if($_SERVER['REQUEST_METHOD']!=='POST'){ http_response_code(405); echo json_encode(['ok'=>false,'error'=>'Método inválido']); exit; }

$raw=file_get_contents('php://input');
$data=$raw?json_decode($raw,true):null;
if(!is_array($data)){ http_response_code(400); echo json_encode(['ok'=>false,'error'=>'JSON inválido']); exit; }

$tipo=norm($data['tipo']??'');
$sucursal=norm($data['sucursal']??'');
$datos=is_array($data['datos']??null)?$data['datos']:[];

// Validación básica
if($tipo!=='contrato' || empty($datos['nombre_fiscal'])){ 
    http_response_code(422);
    echo json_encode(['ok'=>false,'error'=>'Datos incompletos: Nombre fiscal (Razón Social) requerido']); 
    exit; 
}

// 1. Guardado Local (Backup)
$id=bin2hex(random_bytes(16));
$now=date('c');
$entry=[
  'request_id'=>$id,
  'status'=>'pending',
  'sucursal'=>$sucursal,
  'created_at'=>$now,
  'updated_at'=>$now,
  'datos'=>$datos,
  'meta'=>is_array($data['meta']??null)?$data['meta']:[]
];

$path=json_store_path();
$list=[];
if(is_file($path)){
  $rawFile=@file_get_contents($path);
  $j=$rawFile?json_decode($rawFile,true):null;
  $list=is_array($j)?$j:[];
}
$list[]=$entry;
@file_put_contents($path,json_encode($list,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT));

// 2. Envío a Google Apps Script (GAS)
$gasUrl = env('GAS_WEBAPP_URL');
$sharedSecret = env('GAS_SHARED_SECRET');
$gasResult = 'skipped';

if (!empty($gasUrl) && !empty($sharedSecret) && strpos($gasUrl, 'CAMBIA_ESTO') === false) {
    
    // Preparar payload
    $payloadToGas = $datos; 
    $payloadToGas['secret'] = $sharedSecret;
    $payloadToGas['ip'] = $_SERVER['REMOTE_ADDR'] ?? '';
    $payloadToGas['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
    if(empty($payloadToGas['tipo_persona']) && !empty($datos['tipo_persona'])) {
        $payloadToGas['tipo_persona'] = $datos['tipo_persona'];
    }

    if (function_exists('curl_init')) {
        $ch = curl_init($gasUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS => json_encode($payloadToGas, JSON_UNESCAPED_UNICODE),
            CURLOPT_TIMEOUT => 25,
            // Disable SSL verify if needed in dev, but risky
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0
        ]);

        $response = curl_exec($ch);
        $errno = curl_errno($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($errno !== 0) {
            error_log("Error enviando a GAS (curl): $error");
            $gasResult = 'error_curl';
        } else {
            $gasResult = 'sent';
        }
    } elseif (in_array('https', stream_get_wrappers())) {
        // Fallback to file_get_contents
        $opts = [
            'http' => [
                'method'  => 'POST',
                'header'  => 'Content-Type: application/json',
                'content' => json_encode($payloadToGas, JSON_UNESCAPED_UNICODE),
                'timeout' => 25,
                'ignore_errors' => true
            ]
        ];
        $context = stream_context_create($opts);
        $response = @file_get_contents($gasUrl, false, $context);
        if ($response === false) {
             error_log("Error enviando a GAS (file_get_contents)");
             $gasResult = 'error_fgc';
        } else {
             $gasResult = 'sent';
        }
    } else {
        error_log("No se puede enviar a GAS: falta curl y wrapper https");
        $gasResult = 'missing_extensions';
    }
}

// 3. Envío de Correo (SMTP)
require_once dirname(__DIR__, 3).'/lib/mailer.php';

// Definir sucursal y correos internos
$sucursalCode = strtolower($sucursal);
$internalEmails = [];

switch ($sucursalCode) {
    case 'mty':
        $internalEmails[] = 'facturasmty@mediosconvalor.com';
        $internalEmails[] = 'administracionmty@mediosconvalor.com';
        break;
    case 'ags':
        $internalEmails[] = 'ventasags@mediosconvalor.com';
        break;
    case 'qro':
    default:
        $internalEmails[] = 'calidadqro@mediosconvalor.com';
        $internalEmails[] = 'sistemas@mediosconvalor.com';
        break;
}

// Recopilar correos del cliente
$clientEmails = [];
$fieldsToCheck = [
    'contacto_recoleccion_correo',
    'contacto_compras_correo',
    'contacto_pagos_correo',
    'correo_envio_factura'
];

foreach ($fieldsToCheck as $field) {
    if (!empty($datos[$field])) {
        $email = trim($datos[$field]);
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $clientEmails[] = $email;
        }
    }
}

$clientEmails = array_unique($clientEmails);
$allRecipients = array_merge($clientEmails, $internalEmails);
$allRecipients = array_unique($allRecipients);

if (!empty($allRecipients)) {
    $subject = "Confirmación de recepción de información - Medios con Valor";
    
    // Construir cuerpo del correo
    $nombreFiscal = htmlspecialchars($datos['nombre_fiscal'] ?? '');
    $tipoPersona = htmlspecialchars($datos['tipo_persona'] ?? 'moral');
    $fecha = date('d/m/Y H:i');
    
    // Lista de documentos dinámica
    $docsList = '';
    if ($tipoPersona === 'fisica') {
        $docsList .= '<li>Constancia de Situación Fiscal (actualizada)</li>';
        $docsList .= '<li>Comprobante de Domicilio (no mayor a 3 meses)</li>';
        $docsList .= '<li>INE (frente y reverso)</li>';
        $docsList .= '<li>Carátula bancaria (donde sea visible la CLABE)</li>';
    } else {
        // Moral
        $docsList .= '<li>Acta Constitutiva</li>';
        $docsList .= '<li>Poder del Representante Legal</li>';
        $docsList .= '<li>Constancia de Situación Fiscal (actualizada)</li>';
        $docsList .= '<li>Comprobante de Domicilio (no mayor a 3 meses)</li>';
        $docsList .= '<li>INE del Representante Legal (frente y reverso)</li>';
        $docsList .= '<li>Carátula bancaria (donde sea visible la CLABE)</li>';
    }

    // Add environmental permit if applicable
    if (!empty($datos['cuenta_registro_ambiental']) && $datos['cuenta_registro_ambiental'] === 'Si') {
        $docsList .= '<li style="color:#009eff;font-weight:600">Copia del permiso ambiental estatal</li>';
    }
    
    $body = "
    <html>
    <head>
      <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f4f6f9; color: #333; margin: 0; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); overflow: hidden; }
        .header { background: linear-gradient(90deg, #00dd2a, #009eff); padding: 4px; }
        .header-content { padding: 30px 40px; text-align: center; border-bottom: 1px solid #f1f3f5; }
        .header-title { font-size: 24px; font-weight: 700; color: #009eff; margin: 0; }
        .content { padding: 40px; line-height: 1.6; color: #555; }
        .docs-box { background-color: #f8f9fa; border-radius: 12px; padding: 20px; margin-top: 20px; border: 1px solid #e9ecef; }
        .docs-title { font-weight: 600; color: #2c3e50; margin-top: 0; margin-bottom: 12px; font-size: 16px; }
        .footer { font-size: 12px; color: #999; text-align: center; padding: 20px; background-color: #f8fafc; border-top: 1px solid #eee; }
        ul { padding-left: 20px; margin: 0; }
        li { margin-bottom: 8px; }
      </style>
    </head>
    <body>
      <div class='container'>
        <div class='header'></div>
        <div class='header-content'>
           <h1 class='header-title'>Solicitud Recibida</h1>
        </div>
        <div class='content'>
          <p>Estimado cliente,</p>
          <p>Hemos recibido correctamente la información para la solicitud de contrato de <strong style='color:#333'>$nombreFiscal</strong>.</p>
          <p>Nuestro equipo revisará los datos. Una vez autorizada la solicitud, te pediremos adjuntar la siguiente documentación:</p>
          
          <div class='docs-box'>
            <div class='docs-title'>Documentación requerida (" . ($tipoPersona === 'fisica' ? 'Persona Física' : 'Persona Moral') . "):</div>
            <ul>
              $docsList
            </ul>
          </div>
          
          <p style='margin-top:30px;font-size:14px;color:#888'>
            <strong>Detalles de la solicitud:</strong><br>
            Fecha: $fecha<br>
            Sucursal: " . strtoupper($sucursalCode) . "
          </p>
        </div>
        <div class='footer'>
          <p>Medios con Valor<br>Este es un correo automático, por favor no respondas a este mensaje.</p>
        </div>
      </div>
    </body>
    </html>
    ";

    // Enviar (silencioso, no bloquea error al usuario si falla mail)
    @smtp_send_email($allRecipients, $subject, $body, true);
}

// Respuesta al cliente (Frontend)
// Incluimos advertencia si falló GAS pero se guardó local
$msg = 'Solicitud recibida correctamente';
if ($gasResult !== 'sent' && $gasResult !== 'skipped') {
    // $msg .= ' (Nota: Se guardó localmente, pero hubo error de conexión con Google)';
}

echo json_encode(['ok'=>true,'request_id'=>$id, 'message'=>$msg, 'gas_status'=>$gasResult]);
exit;
