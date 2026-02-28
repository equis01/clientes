<?php if(session_status()!==PHP_SESSION_ACTIVE){ session_start(); } ?>
<?php
// Cargar env para obtener URL de GAS
if(!function_exists('env')){
  $envPath = dirname(__DIR__, 3) . '/lib/env.php';
  if(file_exists($envPath)) require_once $envPath;
}
$gasUrl = function_exists('env') ? env('GAS_WEBAPP_URL') : '';
$gasSecret = function_exists('env') ? env('GAS_SHARED_SECRET') : '';

// PROXY PHP PARA GAS (Evitar CORS)
// Intentar leer JSON body primero
$inputJSON = json_decode(file_get_contents('php://input'), true);
$reqAction = $_POST['action'] ?? $inputJSON['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $reqAction === 'proxy_search') {
    header('Content-Type: application/json');
    
    $payload = $inputJSON ?: $_POST;
    // Asegurar que el payload tenga lo necesario (el JS ya lo manda, pero por seguridad reconstruimos o pasamos)
    // El JS manda: { action: 'proxy_search', search_type: ..., search_value: ... }
    // GAS espera: { secret: ..., action: 'search', search_type: ..., search_value: ... }
    
    $gasPayload = [
        'secret' => $gasSecret,
        'action' => 'search',
        'search_type' => $payload['search_type'] ?? '',
        'search_value' => $payload['search_value'] ?? ''
    ];

    $opts = [
        'http' => [
            'method'  => 'POST',
            'header'  => "Content-Type: application/json\r\n",
            'content' => json_encode($gasPayload),
            'ignore_errors' => true,
            'timeout' => 15
        ]
    ];
    
    $context  = stream_context_create($opts);
    $response = file_get_contents($gasUrl, false, $context);
    
    if ($response === false) {
        echo json_encode(['ok' => false, 'error' => 'Error de conexión con GAS']);
    } else {
        echo $response;
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <?php $pageTitle='Contratos'; include dirname(__DIR__, 2).'/layout/head.php'; ?>
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
  <script src="/assets/js/form-contratos.js"></script>

</head>
  <style>
    :root {
      --primary: #009eff;
      --mcv-green: #00dd2a;
      --bg-light: #f4f6f9;
      --card-light: #ffffff;
      --text-light: #333333;
      --border-light: #ced4da;
      
      --bg-dark: #121212;
      --card-dark: #1e1e1e;
      --text-dark: #e0e0e0;
      --border-dark: #333333;
      --input-bg-dark: #2c2c2c;
    }

    body{background-color:var(--bg-light);color:var(--text-light);font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif;margin:0;padding:0}
    .page{display:flex;min-height:100vh;flex-direction:column}
    
    /* Green Top Bar */
    .top-bar { height: 6px; background: linear-gradient(90deg, var(--mcv-green), var(--primary)); width: 100%; position: absolute; top: 0; left: 0; z-index: 10; }
    
    .container{max-width:800px;margin:0 auto;padding:40px 20px;position:relative}
    .header-min{display:flex;align-items:center;justify-content:center;flex-direction:column;gap:12px;margin-bottom:30px}
    .header-min img{width:180px;height:auto}
    .header-min h1{font-size:24px;color:#2c3e50;font-weight:700}
    
    /* Card Style */
    .form-wrapper{background:var(--card-light);border-radius:16px;box-shadow:0 10px 30px rgba(0,0,0,0.05);padding:40px;margin-bottom:40px;transition:background .3s, color .3s;min-height:600px;width:100%;box-sizing:border-box}
    
    /* Steps */
    .steps{display:flex;gap:6px;margin-bottom:30px;justify-content:center;flex-wrap:wrap}
    .step{width:40px;height:6px;background:#e9ecef;border-radius:3px;transition:all .3s ease}
    .step.active{background:var(--mcv-green);width:60px}
    
    /* Fieldset & Legend */
    form fieldset{border:0;padding:0;margin:0}
    form legend{font-size:22px;font-weight:700;color:var(--primary);margin-bottom:24px;padding-bottom:16px;border-bottom:2px solid #f1f3f5;display:block;width:100%}
    
    /* Inputs */
    label{display:block;margin-bottom:20px;font-weight:500;color:#495057;font-size:14px}
    input,select,textarea{width:100%;padding:12px 16px;border:1px solid var(--border-light);border-radius:8px;background-color:#fff;font-size:15px;transition:all .2s;margin-top:6px;box-sizing:border-box;color:inherit}
    input:focus,select:focus,textarea:focus{border-color:var(--primary);outline:0;box-shadow:0 0 0 4px rgba(0,158,255,0.1)}
    textarea{resize:vertical;min-height:100px}
    
    /* Buttons */
    .btn-link{background:none;border:0;color:var(--primary);cursor:pointer;padding:0;font-size:13px;text-decoration:underline}
    .btn-link:hover{color:#0077cc}
    .nav-buttons{display:flex;gap:16px;margin-top:40px;padding-top:24px;border-top:1px solid #f1f3f5;justify-content:flex-end}
    .btn{background:var(--primary);color:#fff;border:0;border-radius:8px;padding:12px 24px;cursor:pointer;font-weight:600;font-size:15px;transition:all .2s;box-shadow:0 4px 6px rgba(0,158,255,0.2)}
    .btn:hover{background:#0088dd;transform:translateY(-1px)}
    .btn:disabled{background:#ccc;cursor:not-allowed;transform:none;box-shadow:none}
    .btn.secondary{background:#e9ecef;color:#495057;box-shadow:none}
    .btn.secondary:hover{background:#dee2e6}
    
    .top-actions{display:flex;gap:8px;justify-content:flex-end;margin-bottom:16px}
    #borrarBorrador{background:transparent;color:#dc3545;padding:8px 12px;font-size:13px;border:1px solid #dc3545;border-radius:6px;cursor:pointer}
    #borrarBorrador:hover{background:#dc3545;color:#fff}

    .captcha{display:flex;align-items:center;gap:12px;margin-top:20px;background:#f8f9fa;padding:16px;border-radius:8px}
    .modal{position:fixed;inset:0;display:none;align-items:center;justify-content:center;background:rgba(0,0,0,.5);z-index:100;backdrop-filter:blur(2px);padding:20px}
    .modal-content{background:#fff;color:#222;padding:32px;border-radius:16px;max-width:500px;width:100%;box-shadow:0 20px 40px rgba(0,0,0,0.2);position:relative;max-height:90vh;overflow-y:auto}
    .modal-title{font-size:20px;font-weight:700;margin-bottom:16px;color:#2c3e50}
    .floating-whatsapp{position:fixed;right:20px;bottom:20px;background:#25D366;color:#fff;width:60px;height:60px;border-radius:50%;display:flex;align-items:center;justify-content:center;text-decoration:none;box-shadow:0 6px 16px rgba(37,211,102,0.4);z-index:90;transition:transform .2s}
    .floating-whatsapp svg{width:32px;height:32px;fill:#fff}
    .floating-whatsapp:hover{transform:scale(1.1)}
    
    /* Dark Mode (Brand Aligned) */
     .theme-dark body, .theme-dark .page { background-color: #0b1222; color: #e5e7eb; }
     .theme-dark .header-min h1 { color: #f1f5f9; }
     .theme-dark .form-wrapper { background: #0f1b33; box-shadow: 0 10px 30px rgba(0,0,0,0.5); color: #e5e7eb; border: 1px solid #1f2937; }
     .theme-dark .step { background: #1f2937; }
     .theme-dark .step.active { background: var(--mcv-green); box-shadow: 0 0 10px rgba(0,221,42,0.4); }
     .theme-dark form legend { color: var(--primary); border-bottom-color: #1f2937; }
     .theme-dark label { color: #cbd5e1; }
     .theme-dark input, .theme-dark select, .theme-dark textarea { background-color: #1e293b; border-color: #334155; color: #f1f5f9; }
     .theme-dark input:focus, .theme-dark select:focus, .theme-dark textarea:focus { border-color: var(--primary); box-shadow: 0 0 0 4px rgba(0,158,255,0.15); }
     .theme-dark .nav-buttons { border-top-color: #1f2937; }
     .theme-dark .btn.secondary { background: #1e293b; color: #cbd5e1; border: 1px solid #334155; }
     .theme-dark .btn.secondary:hover { background: #334155; }
     .theme-dark .captcha { background: #1e293b; color: #e5e7eb; border: 1px solid #1f2937; }
     .theme-dark .modal-content { background: #0f1b33; color: #e5e7eb; border: 1px solid #1f2937; }
     .theme-dark .modal-title { color: #f1f5f9; }
     .theme-dark #borrarBorrador { color: #f87171; border-color: #f87171; }
     .theme-dark #borrarBorrador:hover { background: #f87171; color: #fff; }
     .theme-dark #refreshCaptcha { color: #94a3b8 !important; }
     
     /* Dark Mode Logo Handling */
     .theme-dark .logo-light { display: none !important; }
     .theme-dark .logo-dark { display: block !important; }
     
     /* Docs List */
     .docs-list { text-align: left; padding-left: 24px; line-height: 1.6; color: #555; margin-bottom: 20px; }
     .theme-dark .docs-list { color: #e5e7eb; }

     /* Responsive */
    .search-container { position: relative; width: 100%; }
    .search-input { width: 100%; padding: 12px 16px; border: 1px solid var(--border-light); border-radius: 8px; font-size: 15px; background: #fff; box-sizing: border-box; }
    .search-input:focus { border-color: var(--primary); outline: 0; box-shadow: 0 0 0 4px rgba(0,158,255,0.1); }
    .search-results { display: none; position: absolute; top: 100%; left: 0; right: 0; background: #fff; border: 1px solid var(--border-light); border-radius: 8px; max-height: 250px; overflow-y: auto; z-index: 1000; box-shadow: 0 4px 12px rgba(0,0,0,0.1); margin: 4px 0 0; padding: 0; list-style: none; }
    .search-results li { padding: 10px 16px; cursor: pointer; border-bottom: 1px solid #f8f9fa; font-size: 14px; color: #333; }
    .search-results li:last-child { border-bottom: 0; }
    .search-results li:hover, .search-results li.active { background-color: #f1f3f5; color: var(--primary); }
    .theme-dark .search-input { background: #1e293b; border-color: #334155; color: #fff; }
    .theme-dark .search-results { background: #1e293b; border-color: #334155; }
    .theme-dark .search-results li { color: #e2e8f0; border-bottom-color: #334155; }
    .theme-dark .search-results li:hover, .theme-dark .search-results li.active { background-color: #334155; color: var(--mcv-green); }

    .btn-chip { background: #f1f3f5; border: 1px solid #dee2e6; border-radius: 16px; padding: 6px 12px; font-size: 13px; cursor: pointer; color: #495057; transition: all 0.2s; }
    .btn-chip:hover { background: #e9ecef; color: #212529; }
    .theme-dark .btn-chip { background: #334155; border-color: #475569; color: #e2e8f0; }
    .theme-dark .btn-chip:hover { background: #475569; color: #fff; }
    
    #emailList label { display: flex; align-items: center; gap: 12px; padding: 12px; border: 1px solid transparent; border-radius: 8px; cursor: pointer; transition: background 0.2s; margin-bottom: 4px; }
    #emailList label:hover { background: #f8f9fa; border-color: #dee2e6; }
    .theme-dark #emailList label:hover { background: #334155; border-color: #475569; }
    #emailList input[type="checkbox"] { width: 16px; height: 16px; margin: 0; cursor: pointer; accent-color: var(--primary); }

    @media(max-width:600px){
       .container{padding:20px 16px}
       .form-wrapper{padding:24px 16px}
       .btn{width:100%;justify-content:center}
       .nav-buttons{flex-direction:column-reverse;gap:12px}
       
       /* Steps optimized for mobile */
       .steps{gap:4px;margin-bottom:24px}
       .step{width:100%;height:4px;flex:1}
       .step.active{background:var(--mcv-green);height:4px}
       
       .modal-content{padding:24px 20px;width:95%;margin:0 10px;max-height:85vh}
       .captcha{flex-direction:column;align-items:stretch;text-align:center;gap:16px}
       .captcha > div { justify-content:center; }
       #captchaInput { max-width: 100% !important; margin: 0 auto; }
       .header-min img{width:160px}
       .header-min h1{font-size:22px;text-align:center}
       
       /* Adjust fieldset legend */
       form legend { font-size: 18px; margin-bottom: 20px; }
     }
  </style>
</head>
<body>
  <div class="page">
    <div class="top-bar"></div>
    <div class="container">
      <div class="header-min">
      <img src="https://mediosconvalor.github.io/mcv/img/logo/logo.png" alt="Medios con Valor" class="logo-light" width="200">
      <img src="/assets/img/logo_blanco.png" alt="Medios con Valor" class="logo-dark" width="200" style="display:none">
      <h1 style="margin:0;font-size:22px;text-align:center">Solicitud de contrato</h1>
    </div>
      <div class="steps"><div class="step active" id="st1"></div><div class="step" id="st2"></div><div class="step" id="st3"></div><div class="step" id="st4"></div><div class="step" id="st5"></div><div class="step" id="st6"></div><div class="step" id="st7"></div><div class="step" id="st8"></div></div>
      <div class="top-actions">
        <button id="borrarBorrador" class="btn secondary" type="button">Eliminar borrador</button>
      </div>
      <div class="form-wrapper">
      <form id="formContratos" autocomplete="off">
        <input type="hidden" name="sucursal" id="sucursalHidden" value="">
        <input type="hidden" name="tipo_persona" id="tipoPersonaHidden" value="">
        
        <!-- STEP 1: DATOS GENERALES -->
        <fieldset data-step="1">
          <legend>Datos generales</legend>
          <label>Nombre fiscal (Razón Social):<input name="nombre_fiscal" required></label>
          <label>Nombre comercial / Punto / Estación:<input name="nombre_comercial_punto_estacion"></label>
          <label>RFC:<input name="rfc" oninput="this.value = this.value.toUpperCase()" pattern="^[A-ZÑ&]{3,4}\d{6}[A-Z0-9]{3}$" title="RFC válido (12 o 13 caracteres)" maxlength="13" required></label>
          <label>Régimen fiscal:
            <select name="regimen_fiscal" id="regimenSelect"></select>
          </label>
          <label>Dirección fiscal:<input name="direccion_fiscal" placeholder="Calle y número"></label>
          <label>Estado fiscal:
            <select name="estado_fiscal" id="estadoSelect"></select>
          </label>
          <label>Municipio fiscal:
            <select name="municipio_fiscal" id="municipioSelect"></select>
          </label>
          <label id="municipioOtroLabel" style="display:none;margin-top:10px">Especifique municipio:
            <input id="municipioOtroInput" placeholder="Escriba el municipio">
          </label>
          <label>Código Postal:<input name="codigo_postal" id="cpInput" maxlength="5" inputmode="numeric" placeholder="00000"></label>
          <label>Dirección de servicio:
            <input id="dirServicioInput" name="direccion_servicio">
            <div style="display:flex;gap:8px;margin-top:8px;flex-wrap:wrap">
              <button type="button" id="copyFiscal" class="btn-chip" title="Copiar dirección fiscal">Igual que fiscal</button>
              <button type="button" id="openMap" class="btn-chip" title="Marcar ubicación en mapa">📍 Marcar en Mapa</button>
            </div>
          </label>
          <label>Teléfonos:</label>
          <div id="phonesContainer"></div>
          <button type="button" id="addPhoneBtn" class="btn-chip" style="margin-top:5px;font-weight:600;display:inline-flex;align-items:center;gap:6px">
             <span style="font-size:16px;line-height:1">+</span> Agregar teléfono
          </button>
          <input type="hidden" name="telefonos" id="telefonosHidden">



          <div class="nav-buttons"><button type="button" class="btn" id="next1">Siguiente</button></div>
        </fieldset>

        <!-- STEP 2: REGISTRO AMBIENTAL -->
        <fieldset data-step="2" style="display:none">
          <legend>Registro Ambiental</legend>
          <label>¿Cuenta con registro ambiental de la secretaría Estatal?
            <select name="cuenta_registro_ambiental" id="registroAmbientalSelect">
              <option value="No">No</option>
              <option value="Si">Si</option>
            </select>
          </label>
          <div id="permisoAmbientalContainer" style="display:none; margin-top: 10px;">
             <label>Permiso ambiental:
               <input name="permiso_ambiental" id="permisoAmbientalInput" placeholder="Ingrese el permiso ambiental">
             </label>
          </div>
          <div class="nav-buttons"><button type="button" class="btn secondary" id="prev2">Anterior</button><button type="button" class="btn" id="next2">Siguiente</button></div>
        </fieldset>

        <!-- STEP 3: RECOLECCIÓN -->
        <fieldset data-step="3" style="display:none">
          <legend>Recolección</legend>
          <p style="margin-top:-15px;margin-bottom:20px;color:#666;font-size:14px;line-height:1.4">
            Es a la persona con la que nos comunicaremos para temas relacionados a los accesos, o a la recolección.
          </p>
          <label>Nombre contacto recolección:<input name="contacto_recoleccion_nombre"></label>
          <label>Teléfono recolección:<input name="contacto_recoleccion_telefono"></label>
          <label>Correo recolección:<input name="contacto_recoleccion_correo" type="email"></label>
          <div class="nav-buttons"><button type="button" class="btn secondary" id="prev3">Anterior</button><button type="button" class="btn" id="next3">Siguiente</button></div>
        </fieldset>

        <!-- STEP 4: COMPRAS -->
        <fieldset data-step="4" style="display:none">
          <legend>Compras</legend>
          <p style="margin-top:-15px;margin-bottom:20px;color:#666;font-size:14px;line-height:1.4">
            Es la persona, que por lo general lleva el primer contacto con nosotros, o con quien nos comunicaremos en caso de que hayan cambiado de personal de recolección.
          </p>
          <label>Nombre contacto compras:<input name="contacto_compras_nombre"></label>
          <label>Teléfono compras:<input name="contacto_compras_telefono"><small style="display:block;margin-top:6px"><a href="#" id="copyComprasTel">Igual que anterior</a></small></label>
          <label>Correo compras:<input name="contacto_compras_correo" type="email"><small style="display:block;margin-top:6px"><a href="#" id="copyComprasCorreo">Igual que anterior</a></small></label>
          <div class="nav-buttons"><button type="button" class="btn secondary" id="prev4">Anterior</button><button type="button" class="btn" id="next4">Siguiente</button></div>
        </fieldset>

        <!-- STEP 5: CONTACTO PAGOS -->
        <fieldset data-step="5" style="display:none">
          <legend>Contacto Pagos</legend>
          <p style="margin-top:-15px;margin-bottom:20px;color:#666;font-size:14px;line-height:1.4">
            Es la persona con la que nos comunicaremos para temas pendientes de pagos, o suspensiones de servicios, entre otras cosas.
          </p>
          <label>Nombre contacto pagos:<input name="contacto_pagos_nombre"></label>
          <label>Teléfono pagos:<input name="contacto_pagos_telefono"><small style="display:block;margin-top:6px"><a href="#" id="copyPagosTel">Igual que anterior</a></small></label>
          <label>Correo pagos:<input name="contacto_pagos_correo" type="email"><small style="display:block;margin-top:6px"><a href="#" id="copyPagosCorreo">Igual que anterior</a></small></label>
          <div class="nav-buttons"><button type="button" class="btn secondary" id="prev5">Anterior</button><button type="button" class="btn" id="next5">Siguiente</button></div>
        </fieldset>

        <!-- STEP 6: FACTURACIÓN -->
        <fieldset data-step="6" style="display:none">
          <legend>Facturación</legend>
          <label>Correo envío factura:<input name="correo_envio_factura" type="email">
             <small style="display:block;margin-top:6px"><button type="button" id="selectPrevEmails" class="btn-link">Seleccionar de anteriores</button></small>
          </label>
          <label>Uso CFDI:<select name="uso_cfdi" id="usoCfdiSelect"></select></label>
          <label>Método de pago:<select name="metodo_pago" id="metodoPagoSelect"></select></label>
          <label>Forma de pago:<select name="forma_pago" id="formaPagoSelect"></select></label>
          <label>Plataforma para subir facturas:<select name="plataforma_subir_facturas"><option value="No">No</option><option value="Si">Si</option></select></label>
          <div class="nav-buttons"><button type="button" class="btn secondary" id="prev6">Anterior</button><button type="button" class="btn" id="next6">Siguiente</button></div>
        </fieldset>

        <!-- STEP 7: DATOS BANCARIOS -->
        <fieldset data-step="7" style="display:none">
          <legend>Datos Bancarios</legend>
          <label>Banco:
            <select name="banco" id="bancoSelect"></select>
          </label>
          <label id="bancoOtroLabel" style="display:none;margin-top:10px">Especifique banco:
            <input id="bancoOtroInput" placeholder="Escriba el banco">
          </label>
          <label>Sucursal (Banco):<input name="banco_sucursal"></label>
          <label>Últimos 4 dígitos cuenta:<input name="ultimos4_cuenta"></label>
          <label>CLABE:<input name="clabe"></label>
          
          <h4 style="margin:15px 0 5px;color:#555">Adicional</h4>
          <label>Comentarios adicionales:<textarea name="comentarios_adicionales"></textarea></label>

          <div class="nav-buttons"><button type="button" class="btn secondary" id="prev7">Anterior</button><button type="button" class="btn" id="next7">Siguiente</button></div>
        </fieldset>

        <!-- STEP 8: REPRESENTANTE / ESCRITURA (MORAL ONLY) -->
        <fieldset data-step="8" style="display:none">
          <legend>Representante / Escritura</legend>
          <label>Representante legal:<input name="representante_legal" required></label>
          <label>Escritura pública (número):<input name="escritura_publica_numero"></label>
          <label>Tipo (Volumen/Tomo):
            <select name="tipo_tomo_volumen">
              <option value="">Seleccione...</option>
              <option value="Volumen">Volumen</option>
              <option value="Tomo">Tomo</option>
              <option value="Libro">Libro</option>
            </select>
          </label>
          <label>Número (Volumen/Tomo):<input name="tomo_volumen"></label>
          <label>Fecha constitutiva:<input name="fecha_constitutiva" type="date"></label>
          <label>Notaría pública (número):<input name="notaria_publica_numero"></label>
          <label>Notario titular:<input name="notario_publico_titular"></label>
          <label>Registro inscripción (número):<input name="registrada_bajo_inscripcion_numero"></label>
          <label>Escriturado en Estado de:<select name="escriturado_en_estado_de" id="estadoSecSelect"></select></label>
          
          <div class="captcha">
            <div style="display:flex;align-items:center;gap:10px">
              <span id="captchaText" style="font-size:18px;font-weight:700;letter-spacing:1px"></span>
              <button type="button" id="refreshCaptcha" title="Cambiar operación" style="background:none;border:0;cursor:pointer;font-size:18px;color:#888">&#x21bb;</button>
            </div>
            <input type="text" id="captchaInput" placeholder="Respuesta" style="max-width:100px;text-align:center;font-weight:600">
            <span id="captchaCheck" style="font-size:20px;display:none"></span>
          </div>
          <div class="nav-buttons"><button type="button" class="btn secondary" id="prev8">Anterior</button><button type="submit" class="btn" id="submitBtn">Enviar</button></div>
        </fieldset>
      </form>
      </div>
    </div>
    <?php include dirname(__DIR__, 2).'/layout/footer.php'; ?>
  </div>
  <div id="introModal" class="modal"><div class="modal-content"><div class="modal-title">Bienvenido</div><div class="modal-body">
    <p>Este formulario inicia tu proceso de contrato con Medios Con Valor.</p>
    <label>Tipo de persona:
      <select id="introTipoPersona">
        <option value="moral">Persona moral</option>
        <option value="fisica">Persona física</option>
      </select>
    </label>
    <label>Sucursal:
      <select id="introSucursal">
        <option value="mty">MCV Monterrey (MTY)</option>
        <option value="ags">MCV Aguascalientes (AGS)</option>
        <option value="qro">MCV Querétaro (QRO)</option>
      </select>
    </label>
  </div><div class="actions"><button type="button" class="btn" id="introClose">Continuar</button></div></div></div>
  <div id="deleteModal" class="modal">
    <div class="modal-content">
      <div class="modal-title">¿Eliminar borrador?</div>
      <div class="modal-body">
        <p>Se perderán todos los datos que has llenado hasta ahora y el formulario se recargará.</p>
      </div>
      <div class="actions" style="margin-top:24px;display:flex;justify-content:flex-end;gap:12px">
        <button type="button" class="btn secondary" id="deleteCancel">Cancelar</button>
        <button type="button" class="btn" id="deleteConfirm" style="background:#dc3545">Eliminar</button>
      </div>
    </div>
  </div>
  <div id="successModal" class="modal"><div class="modal-content"><div class="modal-title">Envío exitoso</div><div class="modal-body" id="successBody"></div><div class="actions"><button type="button" class="btn" id="successClose">Aceptar</button></div></div></div>
  <div id="errorModal" class="modal"><div class="modal-content"><div class="modal-title">Error</div><div class="modal-body" id="errorBody"></div><div class="actions"><button type="button" class="btn" id="errorClose">Cerrar</button></div></div></div>

  <div id="emailSelectModal" class="modal">
    <div class="modal-content">
      <div class="modal-title">Seleccionar correos</div>
      <div class="modal-body">
        <p>Selecciona uno o más correos ingresados anteriormente:</p>
        <div id="emailList" style="display:flex;flex-direction:column;gap:10px;margin:15px 0"></div>
      </div>
      <div class="actions">
        <button type="button" class="btn secondary" id="emailSelectCancel">Cancelar</button>
        <button type="button" class="btn" id="emailSelectConfirm">Confirmar</button>
      </div>
    </div>
  </div>
  
  <!-- Map Modal -->
  <div id="mapModal" class="modal" style="z-index:200">
    <div class="modal-content" style="max-width:900px;width:95%;height:80vh;padding:0;display:flex;flex-direction:column;overflow:hidden">
      <div style="padding:16px;background:#fff;border-bottom:1px solid #eee;display:flex;justify-content:space-between;align-items:center">
        <h3 style="margin:0;font-size:18px;color:var(--text-light)">Marcar ubicación</h3>
        <button type="button" id="mapClose" style="background:none;border:0;font-size:24px;cursor:pointer;color:#888">&times;</button>
      </div>
      <div id="map" style="flex:1;width:100%;background:#eee;position:relative;z-index:1"></div>
      <div style="padding:16px;background:#fff;border-top:1px solid #eee;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px">
         <div id="mapStatus" style="font-size:13px;color:#666;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:60%;min-width:200px">Selecciona un punto en el mapa</div>
         <div style="display:flex;gap:10px">
           <button type="button" class="btn secondary" id="mapCancel">Cancelar</button>
           <button type="button" class="btn" id="mapConfirm" disabled>Marcar ubicación</button>
         </div>
      </div>
    </div>
  </div>

  <a id="whatsappFloat" href="#" class="floating-whatsapp" target="_blank" title="Contáctanos por WhatsApp">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
  </a>

  <div id="loadingModal" class="modal" style="z-index:300">
    <div class="modal-content" style="max-width:300px;text-align:center">
      <div class="modal-title">Buscando...</div>
      <div class="modal-body">
        <div class="spinner" style="border:4px solid #f3f3f3;border-top:4px solid var(--primary);border-radius:50%;width:40px;height:40px;animation:spin 1s linear infinite;margin:0 auto 20px"></div>
        <p>Obteniendo información del cliente.</p>
      </div>
    </div>
  </div>
  <style>@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }</style>

  <script>
  // Config injected from PHP
  var GAS_CONFIG = {
    url: "<?php echo htmlspecialchars($gasUrl); ?>",
    secret: "<?php echo htmlspecialchars($gasSecret); ?>"
  };

  (function(){
    // INIT PREFILL LOGIC
    function initPreFill() {
      const params = new URLSearchParams(window.location.search);
      const searchId = params.get('id');
      const searchRz = params.get('rz');

      if (!searchId && !searchRz) return;

      // Mostrar Loading
      var loadM = document.getElementById('loadingModal');
      if(loadM) loadM.style.display='flex';

      // Payload para PROXY local
      const payload = {
        action: 'proxy_search',
        search_type: searchId ? 'id' : 'rz',
        search_value: searchId || searchRz
      };

      fetch(window.location.href, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      })
      .then(function(r){ return r.json(); })
      .then(function(res){
        if(loadM) loadM.style.display='none';

        if (res.ok && res.data) {
          fillForm(res.data);
          // Configurar modal para solo pedir sucursal
          var intro = document.getElementById('introModal');
          var introTipo = document.getElementById('introTipoPersona');
          if(intro && introTipo) {
             // Ocultar selector de tipo de persona
             var labelTipo = introTipo.closest('label');
             if(labelTipo) labelTipo.style.display = 'none';
             
             // Actualizar texto
             var title = intro.querySelector('.modal-title');
             if(title) title.textContent = 'Confirma tu sucursal';
             
             var bodyP = intro.querySelector('.modal-body p');
             if(bodyP) bodyP.textContent = 'Información cargada correctamente. Por favor selecciona la sucursal más cercana.';
             
             // Asegurar que el modal esté visible
             intro.style.display = 'flex';
          }

          
          // Toast o notificación discreta (opcional)
          // console.log('Datos cargados');
        } else {
          // Mostrar Error
          var errM = document.getElementById('errorModal');
          var errB = document.getElementById('errorBody');
          var errC = document.getElementById('errorClose');
          
          if(errB) errB.textContent = "No se encontró información con los datos proporcionados.";
          if(errM) errM.style.display = 'flex';
          
          // Redireccionar al cerrar
          if(errC) {
            errC.onclick = function(){
              window.location.href = '/contratos';
            }
          }
        }
      })
      .catch(function(e){
        if(loadM) loadM.style.display='none';
        console.error("Error:", e);
        // Error genérico
        var errM = document.getElementById('errorModal');
        var errB = document.getElementById('errorBody');
        if(errB) errB.textContent = "Error al conectar con la base de datos.";
        if(errM) errM.style.display = 'flex';
      });
    }

    function fillForm(data) {
      var form = document.getElementById('formContratos');
      if(!form) return;
      
      // Mapear campos
      var ignore = ['id', 'timestamp', 'ip', 'user_agent', 'source_url', 'sucursal'];
      
      for (var key in data) {
        if (ignore.indexOf(key) !== -1) continue;
        if (!Object.prototype.hasOwnProperty.call(data, key)) continue;
        
        var field = form.querySelector('[name="'+key+'"]');
        if (field) {
           // Fechas
           if (field.type === 'date' && data[key]) {
             try {
               var d = new Date(data[key]);
               if(!isNaN(d.getTime())){
                 field.value = d.toISOString().split('T')[0];
               }
             } catch(e){}
           } else {
             field.value = data[key];
           }
           field.dispatchEvent(new Event('change'));
           field.dispatchEvent(new Event('input'));
        }
      }
      
      // Lógica de Tipo Persona
      if (data.tipo_persona) {
        var tipo = data.tipo_persona.toUpperCase(); // FISICA / MORAL
        var tVal = (tipo==='FISICA') ? 'fisica' : 'moral';
        
        var hT = document.getElementById('tipoPersonaHidden');
        if(hT) hT.value = tipo;
        
        var iS = document.getElementById('introTipoPersona');
        if(iS) iS.value = tVal;
        
        updateStepsForType(tipo);
      }
    }

    var startTs=Date.now();
    function q(s){return document.querySelector(s)}
    function qa(s){return Array.prototype.slice.call(document.querySelectorAll(s));}
    
    // Llamar initPreFill al inicio
    initPreFill();

    function showStep(n){
      var f=document.querySelectorAll('fieldset[data-step]');
      f.forEach(function(el){el.style.display=(parseInt(el.getAttribute('data-step'))===n)?'block':'none'});
      [1,2,3,4,5,6,7,8].forEach(function(i){
        var e=q('#st'+i); 
        if(e){ e.classList.toggle('active', i<=n); }
      });
      // Scroll to top
      window.scrollTo(0,0);
    }
      var introClose=q('#introClose');
      if(introClose){
        introClose.onclick=function(){
          var tp=q('#introTipoPersona').value;
          var suc=q('#introSucursal').value;
          q('#tipoPersonaHidden').value = (tp==='fisica')?'FISICA':'MORAL';
          q('#sucursalHidden').value = suc;
          q('#introModal').style.display='none';
          
          // Re-trigger showStep logic based on type
          updateStepsForType((tp==='fisica')?'FISICA':'MORAL');
          showStep(1);
        };
      }
      
      // Helper para actualizar pasos según tipo
      function updateStepsForType(type){
         var step8 = q('fieldset[data-step="8"]');
         if(!step8) return;
         
         // Campos exclusivos de Moral en paso 8
         var fieldsToToggle = [
             'representante_legal', 'escritura_publica_numero', 'tipo_tomo_volumen', 
             'tomo_volumen', 'fecha_constitutiva', 'notaria_publica_numero', 
             'notario_publico_titular', 'registrada_bajo_inscripcion_numero', 
             'escriturado_en_estado_de'
         ];
         
         fieldsToToggle.forEach(function(name){
             var input = step8.querySelector('[name="'+name+'"]');
             if(input){
                 var label = input.closest('label');
                 if(label) label.style.display = (type === 'FISICA') ? 'none' : 'block';
                 
                 if(name === 'representante_legal'){
                     if(type === 'FISICA') input.removeAttribute('required');
                     else input.setAttribute('required', 'true');
                 }
             }
         });
         
         var legend = step8.querySelector('legend');
         if(legend){
             legend.textContent = (type === 'FISICA') ? 'Finalizar' : 'Representante / Escritura';
         }
      }

      // MODIFICACIÓN: Si ya se prellenó el tipo, ejecutar lógica de inicio
      var preType = q('#tipoPersonaHidden').value;
      if(preType && q('#introModal').style.display === 'none'){
         // Si el modal está oculto (por prefill) y hay tipo, asegurar pasos correctos
         updateStepsForType(preType);
         showStep(1);
      }
    
    function valEmail(v){return !v||/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v)}
    function collect(){var fd=new FormData(q('#formContratos'));var o={};fd.forEach(function(v,k){o[k]=v});
      if((o.banco==='Otro'||o.banco==='Otros') && o.banco_otro){ o.banco=o.banco_otro; }
      return o;
    }
    function modal(id,text){
      var m=q('#'+id);
      var b=q('#'+id.replace('Modal','Body'));
      if(m){ 
        if(b && text){ b.innerHTML=String(text||''); } // Use innerHTML for HTML content
        m.style.display='flex'; 
        var btn=q('#'+id.replace('Modal','Close')); 
        if(btn && !btn.onclick){ 
          btn.onclick=function(){ m.style.display='none'; }; 
        } 
      }
    }
    
    function makeCaptcha(){
      var a=Math.floor(Math.random()*9)+1;
      var b=Math.floor(Math.random()*9)+1;
      var ops=['+']; // Simple math
      var op=ops[Math.floor(Math.random()*ops.length)];
      var t=a+' '+op+' '+b+' = ?';
      var exp=(op==='+')?(a+b):(a-b);
      var el=q('#captchaText'); 
      if(el){ 
        el.textContent=t; 
        el.setAttribute('data-exp', String(exp)); 
      }
      var inp=q('#captchaInput');
      var check=q('#captchaCheck');
      if(inp){ 
        inp.value=''; 
        inp.classList.remove('valid','invalid');
        if(check){ check.style.display='none'; }
        inp.oninput=function(){
          var val=parseInt(inp.value);
          if(val===exp){
             check.style.display='inline'; check.textContent='✅'; check.style.color='green';
          } else {
             check.style.display='none';
          }
        };
      }
    }
    
    function initSearchSelect(selId, options, onSelect){
      var sel = q(selId);
      if(!sel) return null;
      sel.style.display='none';
      
      var container = document.createElement('div');
      container.className = 'search-container';
      
      var input = document.createElement('input');
      input.className = 'search-input';
      input.placeholder = 'Escribe para buscar...';
      input.type = 'text';
      input.onkeypress = function(e){ if(e.key==='Enter') e.preventDefault(); };
      
      var list = document.createElement('ul');
      list.className = 'search-results';
      
      container.appendChild(input);
      container.appendChild(list);
      sel.parentNode.insertBefore(container, sel);
      
      var allOpts = options || [];
      
      function render(filter){
        list.innerHTML = '';
        var matches = allOpts.filter(function(o){ 
           return !filter || o.toLowerCase().indexOf(filter.toLowerCase()) !== -1; 
        });
        
        if(matches.length===0 && filter){
           var li = document.createElement('li');
           li.textContent = 'Sin resultados';
           li.style.color = '#999';
           list.appendChild(li);
        } else if(matches.length===0 && !filter) {
           matches = allOpts.slice(0, 100); 
           matches.forEach(addLi);
        } else {
           matches.slice(0, 100).forEach(addLi);
        }
        
        list.style.display = 'block';
      }
      
      function addLi(m){
         var li = document.createElement('li');
         li.textContent = m;
         li.onmousedown = function(){ 
            input.value = m;
            sel.value = m;
            var evt = new Event('change');
            sel.dispatchEvent(evt);
            list.style.display = 'none';
            if(onSelect) onSelect(m);
         };
         list.appendChild(li);
      }
      
      input.onfocus = function(){ render(input.value); };
      input.oninput = function(){ render(input.value); };
      
      input.onblur = function(){
         setTimeout(function(){ list.style.display='none'; }, 200);
      };
      
      sel.addEventListener('manual_update', function(){
         input.value = sel.value;
      });
      
      if(sel.value) input.value = sel.value;

      return {
        update: function(newOpts){
           allOpts = newOpts;
           input.value = '';
           sel.value = '';
        },
        setValue: function(val){
           input.value = val;
           sel.value = val;
        },
        input: input
      };
    }

    function handleOtro(type, val){
       var isOtro = (val === 'Otro' || val === 'Otros');
       var label = q('#' + type + 'OtroLabel');
       var input = q('#' + type + 'OtroInput');
       var select = q('#' + type + 'Select');
       
       if(label) label.style.display = isOtro ? 'block' : 'none';
       
       if(input && select){
           if(isOtro){
               // Swap names
               select.removeAttribute('name');
               input.setAttribute('name', (type==='municipio'?'municipio_fiscal':'banco'));
               input.required = true;
           } else {
               // Restore names
               input.removeAttribute('name');
               select.setAttribute('name', (type==='municipio'?'municipio_fiscal':'banco'));
               input.required = false;
               input.value = ''; 
           }
       }
    }

    function initCPValidation(){
       var cp = q('#cpInput');
       if(cp){
          cp.oninput = function(){
             this.value = this.value.replace(/\D/g,'').slice(0,5);
          };
       }
    }

    function initGeolocation(){
       var btn = q('#useMyLocation');
       if(btn){
          btn.onclick = function(){
             if(!navigator.geolocation){ alert('Geolocalización no soportada'); return; }
             var originalText = btn.textContent;
             btn.textContent = '...';
             btn.disabled = true;
             navigator.geolocation.getCurrentPosition(function(pos){
                var lat = pos.coords.latitude.toFixed(6);
                var lng = pos.coords.longitude.toFixed(6);
                var val = lat + ', ' + lng;
                var input = q('#dirServicioInput');
                if(input){
                   input.value = val;
                   var evt = new Event('change');
                   input.dispatchEvent(evt);
                }
                btn.textContent = originalText;
                btn.disabled = false;
             }, function(err){
                alert('Error: ' + err.message);
                btn.textContent = originalText;
                btn.disabled = false;
             });
          };
       }
    }

    function initEmailSelection(){
       var btn = q('#selectPrevEmails');
       var modal = q('#emailSelectModal');
       var listDiv = q('#emailList');
       var cancel = q('#emailSelectCancel');
       var confirm = q('#emailSelectConfirm');
       
       if(btn){
          btn.onclick = function(){
             var emails = [];
             ['contacto_recoleccion_correo','contacto_compras_correo','contacto_pagos_correo'].forEach(function(n){
                var val = (q('input[name="'+n+'"]').value||'').trim();
                if(val && val.indexOf('@')!==-1 && emails.indexOf(val)===-1) emails.push(val);
             });
             if(emails.length===0){ modal('errorModal', 'No se encontraron correos registrados en los pasos anteriores.'); return; }
             
             listDiv.innerHTML = '';
             emails.forEach(function(e){
                var div = document.createElement('div');
                div.innerHTML = '<label><input type="checkbox" value="'+e+'"> '+e+'</label>';
                listDiv.appendChild(div);
             });
             if(modal) modal.style.display='flex';
          };
       }
       if(cancel && modal) cancel.onclick = function(){ modal.style.display='none'; };
       if(confirm && modal){
          confirm.onclick = function(){
             var selected = [];
             listDiv.querySelectorAll('input:checked').forEach(function(cb){ selected.push(cb.value); });
             if(selected.length>0){
                var target = q('input[name="correo_envio_factura"]');
                if(target){
                   target.value = selected.join(', ');
                   target.dispatchEvent(new Event('change'));
                }
             }
             modal.style.display='none';
          };
       }
    }

    function initSelects(){
      var rs=q('#regimenSelect'); 
      if(rs && typeof regimenesMX !== 'undefined'){ 
         initSearchSelect('#regimenSelect', regimenesMX.regimenes);
      }
      
      var es=q('#estadoSelect'); 
      var ms=q('#municipioSelect'); 
      var searchMun = null;
      
      if(ms){
         searchMun = initSearchSelect('#municipioSelect', [], function(val){
             handleOtro('municipio', val);
         });
         // Disable initially
          if(searchMun && searchMun.input) searchMun.input.disabled = true;
          
          // Ensure input name mapping is correct on load
          handleOtro('municipio', ''); 
       }
      
      if(es && typeof estadosMunicipiosMX !== 'undefined'){ 
        var estados=(estadosMunicipiosMX.estados||[]); 
        
        initSearchSelect('#estadoSelect', estados, function(sel){
           var municipios=(estadosMunicipiosMX.municipios||{}); 
           var list=municipios[sel]||[]; 
           // Append Otro
           var finalList = list.slice();
           finalList.push('Otro');
           
           if(searchMun){ 
               searchMun.update(finalList);
               if(searchMun.input) searchMun.input.disabled = false;
           }
        });
      }
      
      var es2=q('#estadoSecSelect'); 
      if(es2 && typeof estadosMunicipiosMX !== 'undefined'){ 
         var estados=(estadosMunicipiosMX.estados||[]); 
         initSearchSelect('#estadoSecSelect', estados);
      }
      
      var bs=q('#bancoSelect'); 
      if(bs && typeof bancosMX !== 'undefined'){ 
        var list=(bancosMX.instituciones||[]); 
        // Append Otro if not present (usually is, but let's ensure)
        if(list.indexOf('Otro') === -1) list.push('Otro');
        
        initSearchSelect('#bancoSelect', list, function(v){
           handleOtro('banco', v);
        });
      }

      if(q('#usoCfdiSelect') && typeof usosCFDIMX !== 'undefined'){
         initSearchSelect('#usoCfdiSelect', usosCFDIMX.usos_cfdi);
      }
      if(q('#formaPagoSelect') && typeof formasPagoMX !== 'undefined'){
         initSearchSelect('#formaPagoSelect', formasPagoMX.formas_pago);
      }
      if(q('#metodoPagoSelect') && typeof metodosPagoMX !== 'undefined'){
         initSearchSelect('#metodoPagoSelect', metodosPagoMX.metodos_pago);
      }
    }

    function updateWhatsapp(){ 
      var s=(q('#sucursalSelect')&&q('#sucursalSelect').value)||q('#sucursalHidden').value||''; 
      var map={mty:'8184689400',ags:'4492832288',qro:'4461385019',default:'4423565508'}; 
      var num=map[s]||map.default; 
      var a=q('#whatsappFloat'); 
      if(a){ a.href='https://wa.me/52'+num; } 
    }

    function handleNav(){
      q('#next1').onclick=function(){showStep(2)};
      q('#prev2').onclick=function(){showStep(1)};
      q('#next2').onclick=function(){showStep(3)};
      q('#prev3').onclick=function(){showStep(2)};
      q('#next3').onclick=function(){showStep(4)};
      q('#prev4').onclick=function(){showStep(3)};
      q('#next4').onclick=function(){showStep(5)};
      q('#prev5').onclick=function(){showStep(4)};
      q('#next5').onclick=function(){showStep(6)};
      q('#prev6').onclick=function(){showStep(5)};
      q('#next6').onclick=function(){showStep(7)};
      q('#prev7').onclick=function(){showStep(6)};
      q('#next7').onclick=function(){showStep(8)};
      q('#prev8').onclick=function(){showStep(7)};
    }

    function applyPersona(){ 
       var t=(q('#tipoPersonaHidden').value||'moral'); 
       var isFisica=(t==='fisica'); 
       var s8=q('fieldset[data-step="8"]'); 
       var nav7=q('#next7').parentNode; 
       var next7=q('#next7');
       var submitBtn=q('#submitBtn');
       var captchaDiv=q('.captcha');
       var rfcInput = q('input[name="rfc"]');
       
       // Dynamic Label Update
       var nfInput = q('input[name="nombre_fiscal"]');
       if(nfInput && nfInput.parentNode){
         var txtNode = nfInput.parentNode.firstChild;
         if(txtNode && txtNode.nodeType===3){
            txtNode.textContent = isFisica ? 'Nombre completo:' : 'Nombre fiscal (Razón Social):';
         }
       }
       
       // RFC Validation Logic
       if(rfcInput){
          if(isFisica){
             // Fisica: 13 chars
             rfcInput.maxLength = 13;
             rfcInput.pattern = "^[A-ZÑ&]{4}\\d{6}[A-Z0-9]{3}$";
             rfcInput.title = "RFC de persona física debe tener 13 caracteres";
             rfcInput.placeholder = "AAAA000000XXX";
          } else {
             // Moral: 12 chars
             rfcInput.maxLength = 12;
             rfcInput.pattern = "^[A-ZÑ&]{3}\\d{6}[A-Z0-9]{3}$";
             rfcInput.title = "RFC de persona moral debe tener 12 caracteres";
             rfcInput.placeholder = "AAA000000XXX";
          }
       }
       
       if(isFisica){ 
         // Fisica ends at Step 7
         if(s8){ 
           s8.style.display='none'; 
           // Disable inputs to prevent validation error on hidden required fields
          var inputs=s8.querySelectorAll('input,select,textarea');
          inputs.forEach(function(el){ el.disabled=true; });
          
          // Re-enable captcha input as it is moved to active step
          var captchaInp = q('#captchaInput');
          if(captchaInp) captchaInp.disabled = false;
        }
         
         // Hide "Siguiente" in Step 7
         if(next7){ next7.style.display='none'; }
         
         // Move Captcha to Step 7
         if(captchaDiv && nav7 && !nav7.contains(captchaDiv)){
            nav7.parentNode.insertBefore(captchaDiv, nav7);
         } else if(captchaDiv && nav7){
            nav7.parentNode.insertBefore(captchaDiv, nav7);
         }
 
         // Move Submit Button to Step 7 nav
         if(submitBtn && nav7 && !nav7.contains(submitBtn)){
            nav7.appendChild(submitBtn);
         }
 
       } else { 
         // Moral ends at Step 8
         if(s8){ 
           s8.style.display='none'; // Will be shown by navigation
           // Enable inputs
           var inputs=s8.querySelectorAll('input,select,textarea');
           inputs.forEach(function(el){ el.disabled=false; });
         } 
         
         // Show "Siguiente" in Step 7
         if(next7){ next7.style.display='inline-block'; }
         
         // Move Captcha back to Step 8
         var nav8=q('#prev8').parentNode;
         if(captchaDiv && nav8){
            nav8.parentNode.insertBefore(captchaDiv, nav8);
         }
         
         // Move Submit Button back to Step 8 nav
         if(submitBtn && nav8 && !nav8.contains(submitBtn)){
            nav8.appendChild(submitBtn);
         }
       }
     }

    function bindIgual(){ 
      var cTel=q('#copyComprasTel'); var cCor=q('#copyComprasCorreo'); 
      var pTel=q('#copyPagosTel'); var pCor=q('#copyPagosCorreo'); 
      
      if(cTel){ cTel.addEventListener('click',function(e){ e.preventDefault(); var v=q('input[name="contacto_recoleccion_telefono"]').value||''; q('input[name="contacto_compras_telefono"]').value=v; }); } 
      if(cCor){ cCor.addEventListener('click',function(e){ e.preventDefault(); var v=q('input[name="contacto_recoleccion_correo"]').value||''; q('input[name="contacto_compras_correo"]').value=v; }); } 
      if(pTel){ pTel.addEventListener('click',function(e){ e.preventDefault(); var v=q('input[name="contacto_compras_telefono"]').value||''; q('input[name="contacto_pagos_telefono"]').value=v; }); } 
      if(pCor){ pCor.addEventListener('click',function(e){ e.preventDefault(); var v=q('input[name="contacto_compras_correo"]').value||''; q('input[name="contacto_pagos_correo"]').value=v; }); } 
    }

    function saveCache(){ var data=collect(); try{ localStorage.setItem('contracts_cache_inprogress', JSON.stringify(data)); }catch(_){} }
    function safeParse(s){ try{return JSON.parse(s);}catch(_){return null;} }
    function loadCache(){ 
      var raw=localStorage.getItem('contracts_cache_inprogress'); 
      var lastRaw=localStorage.getItem('contracts_cache_last_answers'); 
      var obj=safeParse(raw)||safeParse(lastRaw); 
      if(obj){ 
        Object.keys(obj).forEach(function(k){ 
          var el=q('[name="'+k+'"]'); 
          if(el && !el.value){ 
             el.value=obj[k]; 
             var evt = new Event('manual_update');
             el.dispatchEvent(evt);
          } 
        }); 
      }
    }
    
    function sendToGas(data){
      if(!GAS_CONFIG.url || GAS_CONFIG.url.indexOf('CAMBIA')!==-1) return;
      var payload = Object.assign({}, data);
      payload.secret = GAS_CONFIG.secret;
      payload.user_agent = navigator.userAgent;
      
      // Use no-cors mode to send data without reading response (opaque)
      // Use text/plain to avoid preflight OPTIONS request
      fetch(GAS_CONFIG.url, {
        method: 'POST',
        mode: 'no-cors', 
        headers: { 'Content-Type': 'text/plain' },
        body: JSON.stringify(payload)
      }).then(function(){ 
        // Sent to GAS
      }).catch(function(e){ 
        // Error GAS frontend
      });
    }

    function submit(){var form=q('#formContratos'); form.addEventListener('submit',function(ev){ev.preventDefault(); var data=collect(); if(!data.nombre_fiscal){ modal('errorModal','Nombre fiscal (Razón Social) es requerido'); return; } if(!valEmail(data.contacto_recoleccion_correo)||!valEmail(data.contacto_compras_correo)||!valEmail(data.contacto_pagos_correo)){ modal('errorModal','Verifica los correos'); return; } var cap=q('#captchaText'); var ans=(q('#captchaInput').value||'').trim(); var exp=cap?cap.getAttribute('data-exp'):'0'; if(ans!==exp){ modal('errorModal','Captcha incorrecto'); return; } var dwell=Date.now()-startTs; if(dwell<3000){ modal('errorModal','Completa el formulario antes de enviar'); return; }
        
        var btn=q('#submitBtn');
        if(btn){ btn.disabled=true; btn.textContent='Enviando...'; }

        // Send to GAS directly (bypass PHP proxy issues)
        sendToGas(data);

        var payload={
          tipo:'contrato',
          sucursal:data.sucursal||'',
          datos:data,
          meta:{user_agent:navigator.userAgent||'', dwell_ms:dwell}
        };
        fetch('/contratos/submit',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(payload)})
          .then(function(r){return r.json();})
          .then(function(d){ 
             if(d&&d.ok){ 
               try{ localStorage.removeItem('contracts_cache_last_answers'); localStorage.removeItem('contracts_cache_inprogress'); }catch(_){} 
               
               // Success Message with Docs
               var tipo = data.tipo_persona; // 'moral' or 'fisica'
               var msg = '<p style="margin-bottom:16px;font-size:16px">Tu solicitud ha sido enviada con éxito. Una vez autorizada, recibirás un correo para adjuntar la siguiente documentación:</p>';
               
               msg += '<ul class="docs-list">';
               if(tipo==='fisica'){
                 msg += '<li>Constancia de Situación Fiscal (actualizada)</li>';
                 msg += '<li>Comprobante de Domicilio (no mayor a 3 meses)</li>';
                 msg += '<li>INE (frente y reverso)</li>';
                 msg += '<li>Carátula bancaria (donde sea visible la CLABE)</li>';
               } else {
                 // Moral
                 msg += '<li>Acta Constitutiva</li>';
                 msg += '<li>Poder del Representante Legal</li>';
                 msg += '<li>Constancia de Situación Fiscal (actualizada)</li>';
                 msg += '<li>Comprobante de Domicilio (no mayor a 3 meses)</li>';
                 msg += '<li>INE del Representante Legal (frente y reverso)</li>';
                 msg += '<li>Carátula bancaria (donde sea visible la CLABE)</li>';
               }
               msg += '</ul>';
               
               // Add extra doc if Permiso Ambiental is Yes
               if(data.cuenta_registro_ambiental === 'Si'){
                  msg += '<p style="margin-top:10px;font-weight:600">Documentación adicional:</p>';
                  msg += '<ul class="docs-list">';
                  msg += '<li>Copia del permiso ambiental estatal</li>';
                  msg += '</ul>';
               }

               modal('successModal', msg); 
             } else { 
               if(btn){ btn.disabled=false; btn.textContent='Enviar'; }
               modal('errorModal', (d&&d.error)||'Error al enviar'); 
             } 
          })
          .catch(function(){ 
             if(btn){ btn.disabled=false; btn.textContent='Enviar'; }
             modal('errorModal','Error de conexión'); 
          });
      });
    }

    // --- Map & Copy Logic ---
    var map, marker;
    function initMapLogic(){
       var modal = q('#mapModal');
       var openBtn = q('#openMap');
       var closeBtn = q('#mapClose');
       var cancelBtn = q('#mapCancel');
       var confirmBtn = q('#mapConfirm');
       var statusDiv = q('#mapStatus');
       
       if(openBtn){
         openBtn.onclick = function(){
           if(modal) modal.style.display='flex';
           if(!map){
              // Init Leaflet
             setTimeout(function(){
                if(!window.L) return;
                map = L.map('map').setView([23.6345, -102.5528], 5); // Mexico
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap contributors'
                }).addTo(map);
                
                // Botón "Mi Ubicación" dentro del mapa
                var locBtn = L.Control.extend({
                   options: { position: 'topleft' },
                   onAdd: function(m){
                      var container = L.DomUtil.create('div', 'leaflet-bar leaflet-control');
                      container.style.backgroundColor = 'white';
                      container.style.padding = '8px 12px';
                      container.style.cursor = 'pointer';
                      container.style.display = 'flex';
                      container.style.alignItems = 'center';
                      container.style.justifyContent = 'center';
                      container.style.fontSize = '14px';
                      container.style.fontWeight = 'bold';
                      container.style.borderRadius = '4px';
                      container.title = 'Mi ubicación';
                      container.innerHTML = '<span style="font-size:18px; margin-right:6px;">📍</span> Mi Ubicación';
                      
                      container.onclick = function(e){
                         e.preventDefault();
                         e.stopPropagation();
                         m.locate({setView: true, maxZoom: 16});
                      };
                      return container;
                   }
                });
                map.addControl(new locBtn());
                
                map.on('locationfound', function(e){
                   placeMarker(e.latlng);
                });
                
                map.on('locationerror', function(e){
                   alert('No se pudo obtener tu ubicación. Por favor permite el acceso a la ubicación.');
                });
                
                map.on('click', function(e){
                   placeMarker(e.latlng);
                });
             }, 200);
           } else {
             setTimeout(function(){ map.invalidateSize(); }, 200);
           }
         };
       }
       
       function placeMarker(latlng){
          if(marker) map.removeLayer(marker);
          marker = L.marker(latlng).addTo(map);
          var lat = latlng.lat.toFixed(6);
          var lng = latlng.lng.toFixed(6);
          var coordsText = lat + ', ' + lng;
          
          statusDiv.textContent = 'Coordenadas: ' + coordsText;
          statusDiv.setAttribute('data-val', coordsText);
          
          if(confirmBtn) confirmBtn.disabled=false;
       }
       
       if(closeBtn) closeBtn.onclick = function(){ modal.style.display='none'; };
       if(cancelBtn) cancelBtn.onclick = function(){ modal.style.display='none'; };
       
       if(confirmBtn){
         confirmBtn.onclick = function(){
            var val = statusDiv.getAttribute('data-val');
            var input = q('#dirServicioInput');
            if(input && val){
               input.value = val;
               // Trigger change for autosave
               var event = new Event('change');
               input.dispatchEvent(event);
            }
            modal.style.display='none';
         };
       }
    }
    
    function initCopyFiscal(){
       var btn = q('#copyFiscal');
       if(btn){
          btn.onclick = function(){
             var calle = (q('input[name="direccion_fiscal"]').value||'').trim();
             var cp = (q('input[name="codigo_postal"]').value||'').trim();
             
             var munSel = q('#municipioSelect');
             var mun = (munSel && munSel.value)||'';
             if(mun === 'Otro' || mun === 'Otros'){
                 var munOtro = q('#municipioOtroInput');
                 if(munOtro) mun = munOtro.value;
             }
             mun = mun.trim();
             
             var est = (q('#estadoSelect').value||'').trim();
             
             var parts = [];
             if(calle) parts.push(calle);
             if(mun) parts.push(mun);
             if(est) parts.push(est);
             if(cp) parts.push('CP ' + cp);
             
             var full = parts.join(', ');
             var target = q('#dirServicioInput');
             if(target){
                target.value = full;
                // Trigger change for autosave
                var event = new Event('change');
                target.dispatchEvent(event);
             }
          };
       }
    }

     function initBranchDetection(){
       // Try Geolocation first
       if(navigator.geolocation){
          navigator.geolocation.getCurrentPosition(function(pos){
             selectClosestBranch(pos.coords.latitude, pos.coords.longitude);
          }, function(){
             // Fallback to IP Geolocation
             fetch('https://ipapi.co/json/')
             .then(function(r){ return r.json(); })
             .then(function(d){
                if(d.latitude && d.longitude){
                   selectClosestBranch(d.latitude, d.longitude);
                }
             }).catch(function(){});
          });
       } else {
           // Fallback if no geolocation support
           fetch('https://ipapi.co/json/')
           .then(function(r){ return r.json(); })
           .then(function(d){
              if(d.latitude && d.longitude){
                 selectClosestBranch(d.latitude, d.longitude);
              }
           }).catch(function(){});
       }
     }
     
     function selectClosestBranch(lat, lon){
        var branches = [
           {name:'Guadalajara', lat:20.659698, lon:-103.349609},
           {name:'Tijuana', lat:32.5149, lon:-117.0382},
           {name:'Querétaro', lat:20.5888, lon:-100.3899} 
        ];
        
        var closest = null;
        var minDist = Infinity;
        
        branches.forEach(function(b){
           var d = getDistance(lat, lon, b.lat, b.lon);
           if(d < minDist){
              minDist = d;
              closest = b.name;
           }
        });
        
        if(closest){
           var sel = q('#introSucursal');
           if(sel){
               sel.value = closest.toLowerCase() === 'querétaro' ? 'qro' : (closest.toLowerCase() === 'guadalajara' ? 'ags' : 'mty'); // Mapping approximation based on available options
               // Ajuste: El array de branches tiene 'Guadalajara' pero las opciones son mty, ags, qro. 
               // Asumiendo Guadalajara -> AGS (cercanía) o agregar opción.
               // Revisando opciones: mty (Monterrey), ags (Aguascalientes), qro (Querétaro).
               // Guadalajara está más cerca de Aguascalientes o se atiende desde ahí?
               // Por ahora mapeamos: Querétaro->qro, Tijuana->mty (?), Guadalajara->ags (?)
               
               if(closest === 'Querétaro') sel.value = 'qro';
               else if(closest === 'Guadalajara') sel.value = 'ags'; 
               else if(closest === 'Tijuana') sel.value = 'mty';
           }
        }
     }
     
     function getDistance(lat1, lon1, lat2, lon2) {
        var R = 6371; // Radius of the earth in km
        var dLat = deg2rad(lat2-lat1);
        var dLon = deg2rad(lon2-lon1); 
        var a = 
          Math.sin(dLat/2) * Math.sin(dLat/2) +
          Math.cos(deg2rad(lat1)) * Math.cos(deg2rad(lat2)) * 
          Math.sin(dLon/2) * Math.sin(dLon/2)
          ; 
        var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a)); 
        var d = R * c; // Distance in km
        return d;
     }

     function deg2rad(deg) {
       return deg * (Math.PI/180);
     }

    function initPhones(){
      var container = q('#phonesContainer');
      var addBtn = q('#addPhoneBtn');
      var hidden = q('#telefonosHidden');
      var maxPhones = 5;

      if(!container || !addBtn || !hidden) return;

      function updateHidden(){
        var inputs = container.querySelectorAll('.phone-input');
        var values = [];
        inputs.forEach(function(inp){
           var raw = inp.value.replace(/\D/g,'');
           if(raw.length >= 10) values.push(raw);
        });
        hidden.value = values.join(' / ');
        var evt = new Event('change');
        hidden.dispatchEvent(evt); 
      }

      function formatPhone(e){
         var input = e.target;
         var start = input.selectionStart;
         var oldVal = input.value;
         
         var val = input.value.replace(/\D/g,'');
         if(val.length > 10) val = val.substring(0,10);
         
         var formatted = '';
         if(val.length > 0) formatted += '(' + val.substring(0,3);
         if(val.length >= 3) formatted += ') ' + val.substring(3,6);
         if(val.length >= 6) formatted += ' - ' + val.substring(6,10);
         
         input.value = formatted;
         updateHidden();
      }

      function addPhone(val){
         var count = container.querySelectorAll('.phone-row').length;
         if(count >= maxPhones) return;

         var row = document.createElement('div');
         row.className = 'phone-row';
         row.style.cssText = 'display:flex;gap:10px;align-items:center;margin-bottom:8px';
         
         var label = document.createElement('span');
         label.className = 'phone-label';
         label.style.minWidth = '80px';
         label.style.fontSize = '14px';
         label.style.color = '#555';
         
         var input = document.createElement('input');
         input.type = 'tel';
         input.className = 'phone-input';
         input.placeholder = '(000) 000 - 0000';
         input.maxLength = 16; 
         input.style.flex = '1';
         input.oninput = formatPhone;
         
         if(val) { 
            // Format existing value
            var clean = val.replace(/\D/g,'');
            if(clean.length > 0){
               var f = '';
               if(clean.length > 0) f += '(' + clean.substring(0,3);
               if(clean.length >= 3) f += ') ' + clean.substring(3,6);
               if(clean.length >= 6) f += ' - ' + clean.substring(6,10);
               input.value = f;
            }
         }

         var delBtn = document.createElement('button');
          delBtn.type = 'button';
          delBtn.innerHTML = '&times;';
          delBtn.title = 'Eliminar';
          delBtn.style.cssText = 'width:30px;height:30px;border-radius:50%;background:#fee2e2;color:#ef4444;border:1px solid #fecaca;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:18px;line-height:1;transition:all 0.2s';
          
          delBtn.onmouseover = function(){ this.style.background='#fecaca'; this.style.borderColor='#fca5a5'; };
          delBtn.onmouseout = function(){ this.style.background='#fee2e2'; this.style.borderColor='#fecaca'; };
          
          delBtn.onclick = function(){
             container.removeChild(row);
             updateLabels();
             updateHidden();
             checkMax();
          };

         row.appendChild(label);
         row.appendChild(input);
         row.appendChild(delBtn);
         container.appendChild(row);
         
         updateLabels();
         checkMax();
      }
      
      function updateLabels(){
         var rows = container.querySelectorAll('.phone-row');
         rows.forEach(function(row, idx){
            var lbl = row.querySelector('.phone-label');
            if(lbl) lbl.textContent = 'Teléfono ' + (idx+1) + ':';
            
            var btn = row.querySelector('button');
            if(btn){
               // Hide delete button for the first row if it's the only one
               btn.style.display = (rows.length === 1) ? 'none' : 'block';
            }
         });
      }

      function checkMax(){
         var count = container.querySelectorAll('.phone-row').length;
         addBtn.style.display = (count >= maxPhones) ? 'none' : 'inline-block';
      }

      addBtn.onclick = function(){ addPhone(); };

      // Listen for cache loading
      hidden.addEventListener('manual_update', function(){
         var vals = (hidden.value || '').split(' / ');
         container.innerHTML = '';
         var added = false;
         vals.forEach(function(v){
            if(v.trim()){ addPhone(v.trim()); added=true; }
         });
         if(!added && container.children.length === 0) addPhone();
      });

      // Initial row if empty
      if(container.children.length === 0) addPhone();
    }

    function initInputFormatting(){
        // Select all text inputs and textareas
        var inputs = document.querySelectorAll('input:not([type]), input[type="text"], textarea');
        
        inputs.forEach(function(el){
           // Exclude RFC, Email, Numbers, etc.
           var name = (el.name || '').toLowerCase();
           var type = (el.type || '').toLowerCase();
           
           if(name.indexOf('rfc') !== -1) return;
           if(name.indexOf('email') !== -1 || name.indexOf('correo') !== -1) return;
           if(name.indexOf('clabe') !== -1 || name.indexOf('cuenta') !== -1) return;
           if(name.indexOf('telefono') !== -1) return;
           if(el.id === 'dirServicioInput') return; // Address usually handled by map or copy
          if(el.id === 'permisoAmbientalInput') return; // Keep as typed
          if(el.id === 'captchaInput') return; // Captcha is numeric
          
          // Listener: Uppercase while typing
           el.addEventListener('input', function(){
              var start = this.selectionStart;
              var end = this.selectionEnd;
              this.value = this.value.toUpperCase();
              this.setSelectionRange(start, end);
           });
           
           // Listener: Title Case on blur
           el.addEventListener('blur', function(){
              if(this.value){
                 // Special case for Permiso Ambiental: Keep uppercase? 
                 // User request: "El cliente escribe 'casa nORMAL 1', el frontend va a mostrar 'CASA NORMAL 1' y cuando termine de escribir, ahora dirá 'Casa Normal 1'."
                 // Permits are usually codes. But let's follow the general rule unless specified.
                 // Actually, permits are better in uppercase. Let's exclude permission from TitleCase.
                 if(el.name === 'permiso_ambiental') return; 

                 this.value = toTitleCase(this.value);
                 // Trigger change for autosave
                 var evt = new Event('change');
                 this.dispatchEvent(evt);
              }
           });
        });
     }
     
     function initEnvironmentalLogic(){
        var sel = q('#registroAmbientalSelect');
        var cont = q('#permisoAmbientalContainer');
        var input = q('#permisoAmbientalInput');
        
        if(sel && cont){
           sel.addEventListener('change', function(){
              if(this.value === 'Si'){
                 cont.style.display = 'block';
                 if(input) input.required = true;
              } else {
                 cont.style.display = 'none';
                 if(input) { input.required = false; input.value = ''; }
              }
           });
           // Trigger on load
           var evt = new Event('change');
           sel.dispatchEvent(evt);
        }
     }
    
    function toTitleCase(str) {
       return str.toLowerCase().split(' ').map(function(word) {
          return (word.charAt(0).toUpperCase() + word.slice(1));
       }).join(' ');
    }
    
    // fillDummyData removed

    // Init
      document.addEventListener('DOMContentLoaded',function(){

      // Bind buttons first (robustness)
      var bb=q('#borrarBorrador'); 
      if(bb){ 
        bb.addEventListener('click',function(){ 
           var dm=q('#deleteModal');
           if(dm) dm.style.display='flex';
        }); 
      }


      
      var dConfirm=q('#deleteConfirm');
      if(dConfirm){
        dConfirm.onclick=function(){
          try{ 
             localStorage.removeItem('contracts_cache_inprogress'); 
             localStorage.removeItem('contracts_cache_last_answers');
          }catch(_){} 
          location.reload(); 
        }
      }
      
      var dCancel=q('#deleteCancel');
      if(dCancel){
        dCancel.onclick=function(){
          var dm=q('#deleteModal');
          if(dm) dm.style.display='none';
        }
      }
      
      var refreshCap=q('#refreshCaptcha');
      if(refreshCap){
        refreshCap.onclick=function(){ makeCaptcha(); }
      }

      // Logic
      try{ showStep(1); }catch(_){}
      try{ initSelects(); }catch(_){}
      try{ updateWhatsapp(); }catch(_){}
      try{ handleNav(); }catch(_){}
      try{ bindIgual(); }catch(_){}
      try{ makeCaptcha(); }catch(_){}
      try{ initPhones(); }catch(_){}
      try{ loadCache(); }catch(_){}
      try{ initMapLogic(); }catch(_){}
      try{ initCopyFiscal(); }catch(_){}
      try{ initGeolocation(); }catch(_){}
      try{ initEmailSelection(); }catch(_){}
      try{ initCPValidation(); }catch(_){}
      try{ initBranchDetection(); }catch(_){}
      try{ initInputFormatting(); }catch(_){}
      try{ initEnvironmentalLogic(); }catch(_){}
      
      var sc=q('#successClose'); 
      if(sc){ 
        sc.onclick=function(){ 
          try{ 
             localStorage.removeItem('contracts_cache_inprogress'); 
             localStorage.removeItem('contracts_cache_last_answers');
          }catch(_){} 
          window.location.href = 'https://mediosconvalor.com/recoleccion/';
        } 
      }
      var ec=q('#errorClose'); if(ec){ ec.onclick=function(){ q('#errorModal').style.display='none'; } }

      // Modal Intro
      var im=q('#introModal'); var ic=q('#introClose'); var isuc=q('#introSucursal'); var itp=q('#introTipoPersona'); 
      if(im){ 
        im.style.display='flex'; 
        if(ic){ 
          ic.onclick=function(){ 
            var suc=isuc?isuc.value:''; 
            var tp=itp?itp.value:'moral'; 
            q('#sucursalHidden').value=suc; 
            q('#tipoPersonaHidden').value=tp; 
            updateWhatsapp(); 
            applyPersona(); 
            loadCache(); 
            im.style.display='none'; 
          }; 
        } 
      }
      
      // Auto-save on change
      qa('input,select,textarea').forEach(function(el){ el.addEventListener('change',saveCache); el.addEventListener('keydown',function(){ setTimeout(saveCache,100); }); });
      
      submit();
    });
  })();
  </script>
</body>
</html>
