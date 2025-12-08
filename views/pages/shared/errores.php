<?php
if(session_status()!==PHP_SESSION_ACTIVE){ session_start(); }
$code=isset($_GET['code'])?intval($_GET['code']):($_SERVER['REDIRECT_STATUS']??0);
$message=isset($_GET['msg'])?trim($_GET['msg']):'';
$path=isset($_GET['path'])?trim($_GET['path']):($_SERVER['REQUEST_URI']??'');
if(!isset($_SESSION['user'])){ header('Location: /users/login'); exit; }
if($code){ http_response_code($code); }
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <?php $pageTitle='Error'; include dirname(__DIR__,2).'/layout/head.php'; ?>
</head>
<body>
  <?php include dirname(__DIR__,2).'/layout/header.php'; ?>
  <main class="container">
    <div class="card">
      <h2 class="title diagnose">Ocurrió un error</h2>
      <div>Código: <?php echo htmlspecialchars($code?:''); ?></div>
      <?php if($path){ ?><div>Ruta: <?php echo htmlspecialchars($path); ?></div><?php } ?>
      <?php if($message){ ?><div>Detalle: <?php echo htmlspecialchars($message); ?></div><?php } ?>
      <div style="margin-top:8px">Si el problema continúa, favor de comunicarse con sistemas@mediosconvalor.com</div>
      <div class="actions" style="margin-top:12px">
        <a class="btn" href="javascript:history.back()">Regresar</a>
        <a class="btn secondary" href="/">Inicio</a>
      </div>
    </div>
  </main>
  <?php include dirname(__DIR__,2).'/layout/footer.php'; ?>
</body>
</html>
