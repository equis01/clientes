<?php if(session_status()!==PHP_SESSION_ACTIVE){ session_start(); } if(isset($_SESSION['user'])){header('Location: /users');exit;} $client=''; ?>
<!DOCTYPE html>
<html lang="es">
<head>
  <?php $pageTitle='Login'; include dirname(__DIR__,2).'/layout/head.php'; ?>
  <link rel="stylesheet" href="/assets/css/login.css">
</head>
<body>
  <div class="page">
    <div class="container">
      <div class="left">
        <div class="login"><img src="https://mediosconvalor.github.io/mcv/img/logo/logo.png" width="225" height="115" alt=""></div>
        <div class="success-message"></div>
        <div class="error-message"></div>
      </div>
      <div class="right">
        <svg viewBox="0 0 320 300">
          <defs>
            <linearGradient id="linearGradient" x1="13" y1="193.5" x2="307" y2="193.5" gradientUnits="userSpaceOnUse">
              <stop style="stop-color:#00dd2a;" offset="0"/>
              <stop style="stop-color:#009eff;" offset="1"/>
            </linearGradient>
          </defs>
          <path d="m 40,120 240,0 c 0,0 25,0.8 25,35 0,34.2 -25,35 -25,35 h -240 c 0,0 -25,4 -25,38.5 0,34.5 25,38.5 25,38.5 h 215 c 0,0 20,-1 20,-25 0,-24 -20,-25 -20,-25 h -190 c 0,0 -20,1.7 -20,25 0,24 20,25 20,25 h 168.6"/>
        </svg>
        <div class="form">
          <label for="username">USUARIO</label>
          <input type="text" id="username" autocomplete="username">
          <label for="password">CONTRASEÑA</label>
          <input type="password" id="password" autocomplete="current-password">
          <input type="submit" id="submit" value="INGRESAR">
          <p class="forgot-password">
            <a href="#" id="forgot-password">¿Olvidó su usuario, o contraseña?</a>
          </p>
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
  </script>
  <script src="/assets/js/login.js"></script>
</body>
</html>
