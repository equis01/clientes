<?php 
if(session_status()!==PHP_SESSION_ACTIVE){ session_start(); } 
if(isset($_SESSION['user'])){header('Location: /users');exit;} 

// Configuración MCV Accounts
$clientId = '29f68cfc376872bdd70d'; 
$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
// Forzar HTTPS si estamos en el dominio de producción
if (strpos($_SERVER['HTTP_HOST'], 'clientes.mediosconvalor.com') !== false) {
    $protocol = 'https';
}
$redirectUri = $protocol . "://" . $_SERVER['HTTP_HOST'] . "/auth/callback";
$idpBaseUrl = 'https://pruebas.mediosconvalor.com';

// Generar estado anti-CSRF
if (empty($_SESSION['oauth_state'])) {
    $_SESSION['oauth_state'] = bin2hex(random_bytes(16));
}
$state = $_SESSION['oauth_state'];

$authUrl = $idpBaseUrl . '/authorize?' . http_build_query([
    'client_id' => $clientId,
    'redirect_uri' => $redirectUri,
    'response_type' => 'code',
    'state' => $state
]);

$client=''; 
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <?php $pageTitle='Login'; include dirname(__DIR__,2).'/layout/head.php'; ?>
  <link rel="stylesheet" href="/assets/css/login.css">
</head>
<body>
  <div class="page">
    
    <div class="login-card">
      
      <!-- Columna Izquierda: Logo -->
      <div class="card-left">
        <img src="https://mediosconvalor.github.io/mcv/img/logo/logo.png" alt="Medios con Valor" class="logo-mcv">
      </div>

      <!-- Columna Derecha: Formulario -->
      <div class="card-right">
        
        <div class="form-container">
          
          <div class="input-group">
            <label for="username">USUARIO</label>
            <div class="input-wrapper">
                <!-- Icono Usuario -->
                <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/></svg>
                <input type="text" id="username" autocomplete="username" placeholder="Q1Prueba1">
            </div>
          </div>

          <div class="input-group">
            <label for="password">CONTRASEÑA</label>
            <div class="input-wrapper">
                <!-- Icono Candado -->
                <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C9.243 2 7 4.243 7 7v3H6c-1.103 0-2 .897-2 2v8c0 1.103.897 2 2 2h12c1.103 0 2-.897 2-2v-8c0-1.103-.897-2-2-2h-1V7c0-2.757-2.243-5-5-5zm6 18H6v-8h12v8zm-9-8V7c0-1.654 1.346-3 3-3s3 1.346 3 3v3H9z"/></svg>
                <input type="password" id="password" autocomplete="current-password" placeholder="•••••">
            </div>
          </div>

          <div class="messages">
             <div class="success-message"></div>
             <div class="error-message"></div>
          </div>

          <input type="submit" id="submit" value="INGRESAR">
          
          <div class="separator">- O -</div>
          
          <a href="<?php echo htmlspecialchars($authUrl); ?>" class="btn-mcv-login">
            <img src="https://mediosconvalor.github.io/mcv/img/logo/logo.png" alt="MCV">
            Iniciar sesión con MCVAccounts
          </a>

          <p class="forgot-password">
            <a href="#" id="forgot-password">¿Olvidó su usuario o contraseña?</a>
          </p>

          <div class="card-footer">
            <div class="footer-item">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="14" height="14"><path d="M12 2C9.243 2 7 4.243 7 7v3H6a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8a2 2 0 0 0-2-2h-1V7c0-2.757-2.243-5-5-5zM9 7c0-1.654 1.346-3 3-3s3 1.346 3 3v3H9V7zm4 10.723V20h-2v-2.277a1.993 1.993 0 0 1 .567-3.677A2 2 0 0 1 13 17.723z"/></svg>
                <span>Acceso seguro</span>
            </div>
            <div class="footer-item">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="14" height="14"><path d="M20 4H4c-1.103 0-2 .897-2 2v12c0 1.103.897 2 2 2h16c1.103 0 2-.897 2-2V6c0-1.103-.897-2-2-2zm0 2v.511l-8 6.223-8-6.222V6h16zM4 18V9.044l7.386 5.745a.994.994 0 0 0 1.228 0L20 9.044 20.002 18H4z"/></svg>
                <span>Soporte: sistemas@mediosconvalor.com</span>
            </div>
          </div>

        </div>
      </div>
    </div>

    <!-- Modals (Mantenidos para funcionalidad) -->
    <div id="portalModal" class="modal" style="display:none">
        <div class="modal-content">
            <div class="modal-title">Acceso restringido</div>
            <div class="modal-body">Tu acceso al portal está restringido. Puede ser un error del sistema, o se bloqueó tu acceso por falta de pago, o finalización de servicio. Favor de comunicarte con tu contacto de Medios Con Valor, para revisar este tema.</div>
            <div class="actions"><button type="button" class="btn" id="portalClose">Aceptar</button></div>
        </div>
    </div>
    <div id="loginModal" class="modal" style="display:none">
        <div class="modal-content">
            <div class="modal-title">Aviso</div>
            <div class="modal-body" id="loginModalBody"></div>
            <div class="actions"><button type="button" class="btn" id="loginModalClose">Aceptar</button></div>
        </div>
    </div>

  </div>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/animejs/2.2.0/anime.min.js"></script>
  <script src="https://www.gstatic.com/firebasejs/9.6.10/firebase-app-compat.js"></script>
  <script src="https://www.gstatic.com/firebasejs/9.6.10/firebase-auth-compat.js"></script>
  <script>
    window.firebaseConfig={
      apiKey:"AIzaSyClubpIQ2jPNO874Lt4LTO-Fhu7kqDTSiw",
      authDomain:"mcv-portal-clientes.firebaseapp.com",
      projectId:"mcv-portal-clientes",
      storageBucket:"mcv-portal-clientes.firebasestorage.app",
      messagingSenderId:"700467520218",
      appId:"1:700467520218:web:6724118daefac9f4e53be3",
      measurementId:"G-4F1FPMDRCR"
    };
    firebase.initializeApp(window.firebaseConfig);
  </script>
  <script src="/assets/js/login.js"></script>
</body>
</html>