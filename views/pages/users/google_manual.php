<?php
if(session_status()!==PHP_SESSION_ACTIVE){ session_start(); }
if(!isset($_SESSION['user'])){header('Location: /users/login');exit;}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <?php $pageTitle='Cómo crear una Cuenta de Google'; include dirname(__DIR__,2).'/layout/head.php'; ?>
  <style>
    .docs-content {
      background: var(--card);
      border: 1px solid var(--border);
      border-radius: 12px;
      padding: 40px;
      max-width: 800px;
      margin: 0 auto;
    }
    .docs-content h1 {
      margin-top: 0;
      font-size: 28px;
      border-bottom: 1px solid var(--border);
      padding-bottom: 16px;
      margin-bottom: 30px;
      color: var(--text);
    }
    .docs-content h2 {
      font-size: 24px;
      margin-top: 40px;
      margin-bottom: 16px;
      color: var(--text);
    }
    .docs-content h3 {
      font-size: 20px;
      margin-top: 30px;
      margin-bottom: 14px;
      color: var(--text);
    }
    .docs-content p {
      line-height: 1.6;
      color: var(--text);
      opacity: 0.9;
      margin-bottom: 16px;
    }
    .docs-content img {
      max-width: 100%;
      height: auto;
      border-radius: 8px;
      border: 1px solid var(--border);
      margin: 20px 0;
      display: block;
      margin-left: auto;
      margin-right: auto;
    }
    .docs-content a {
      color: #00DC2A;
      text-decoration: none;
      font-weight: 500;
    }
    .docs-content a:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>
  <?php include dirname(__DIR__,2).'/layout/header.php'; ?>
  <main class="container">
    <div class="docs-content">
      <h1>Cómo crear una Cuenta de Google con correo corporativo</h1>
      
      <h2>1- Acceder a la Plataforma para Crear una Cuenta de Google (Correo Gmail)</h2>
      <p>En el siguiente enlace podrás comenzar el proceso &gt;&gt;&nbsp;<a href="https://accounts.google.com/signup" target="_blank" rel="noopener">https://accounts.google.com/signup</a></p>
      <p><em>*Este acceso es el mismo para crear un correo Gmail.</em></p>
      <p>
        <img src="https://www.davirbonilla.com/wp-content/uploads/crear-una-cuenta-de-google-con-un-gmail.jpg" alt="crear una cuenta de google con un gmail">
      </p>
      <p>Podrás ver que se te ofrece la creación de un correo Gmail, porque es lo más común; si lo que quieres es crear un correo Gmail por aquí lo puedes seguir haciendo.</p>
      
      <h2>2- Seleccionar Cuenta de Google con un Correo Corporativo</h2>
      <p>Si lo que quieres es crear una cuenta de Google con tu Correo Corporativo, deberás seleccionar la opción siguiente:</p>
      <p>
        <img src="https://www.davirbonilla.com/wp-content/uploads/seleccionar-usar-correo-corporativo-para-crear-cuenta-de-google.jpg" alt="seleccionar usar correo corporativo para crear cuenta de google">
      </p>
      
      <h2>3- Crear la Cuenta de Google con el Correo Corporativo</h2>
      <p>Luego de la selección del paso anterior (paso 2), deberás ingresar el correo corporativo que quieres usar y luego definir la contraseña.</p>
      <p>
        <img src="https://www.davirbonilla.com/wp-content/uploads/CREAR-CUENTA-DE-GOOGLE-CON-CORREO-CORPORATIVO.jpg" alt="CREAR CUENTA DE GOOGLE CON CORREO CORPORATIVO">
      </p>
      <p>Ten en cuenta, que a ese correo corporativo le llegará un email de verificación y validación donde Google confirmará que es de tu propiedad.</p>
      <p>Para más detalles en el siguiente enlace tienes todo el soporte de Google: <a href="https://support.google.com/accounts/answer/27441?hl=es-419" target="_blank" rel="noopener">Cómo Crear una Cuenta de Google Support</a>.</p>
      <p>Con esto ya no tendrás limitación y podrás ordenar mejor el uso de tus correos electrónicos.</p>
    </div>
  </main>
  <?php include dirname(__DIR__,2).'/layout/footer.php'; ?>
</body>
</html>
