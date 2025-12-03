<?php
if(session_status()!==PHP_SESSION_ACTIVE){ session_start(); }
if(!isset($_SESSION['user'])){header('Location: /login');exit;}
if(empty($_SESSION['is_admin'])){http_response_code(403); echo '403'; exit;}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <?php $pageTitle='Admin'; include dirname(__DIR__).'/../layout/head.php'; ?>
</head>
<body>
  <?php $client=(isset($_SESSION['client_name'])?$_SESSION['client_name']:$_SESSION['user']); include dirname(__DIR__).'/../layout/header.php'; ?>
  <main class="container">
    <div class="card">
      <h2 class="title">Panel de Administración</h2>
      <div>Bienvenido, <?php echo htmlspecialchars($client); ?></div>
      <div><a href="/admin/clientes">Gestión de usuarios y endpoints GAS.</a></div>
    </div>
  </main>
  <?php include dirname(__DIR__).'/../layout/footer.php'; ?>
</body>
</html>

