<?php if(session_status()!==PHP_SESSION_ACTIVE){ session_start(); } if(isset($_SESSION['is_admin']) && $_SESSION['is_admin']){header('Location: /admin');exit;} ?>
<!DOCTYPE html>
<html lang="es">
<head>
  <?php $pageTitle='Login Admin'; include dirname(__DIR__).'/../layout/head.php'; ?>
  <link rel="stylesheet" href="/assets/css/login.css">
  <style>:root{--admin-only:1}</style>
  <!-- admin usa el mismo layout visual del login general -->
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
          <label for="username">CORREO</label>
          <input type="text" id="username" autocomplete="username" placeholder="tu@mediosconvalor.com">
          <label for="password">CONTRASEÑA</label>
          <input type="password" id="password" autocomplete="current-password">
          <input type="submit" id="submit" value="INGRESAR">
          <p class="forgot-password">
            <a href="#" id="forgot-password">¿Olvidó su usuario, o contraseña?</a>
          </p>
          <p class="forgot-password">
            <a href="#" id="client-login">¿Eres cliente? Da clic aquí</a>
          </p>
        </div>
      </div>
    </div>
  </div>
  <div id="adminModal" class="modal" style="display:none">
    <div class="modal-content">
      <div class="modal-title">Aviso</div>
      <div class="modal-body" id="adminModalBody"></div>
      <div class="actions"><button type="button" class="btn" id="adminModalClose">Aceptar</button></div>
    </div>
  </div>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/animejs/2.2.0/anime.min.js"></script>
  <script src="/assets/js/admin_login.js"></script>
</body>
</html>
