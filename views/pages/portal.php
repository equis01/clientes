<?php if(session_status()!==PHP_SESSION_ACTIVE){ session_start(); } if(!isset($_SESSION['user'])){header('Location: /login');exit;} $client=isset($_SESSION['client_name'])?$_SESSION['client_name']:$_SESSION['user']; $folder=isset($_SESSION['folder_url'])?$_SESSION['folder_url']:'#'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
  <?php $pageTitle='Portal'; include __DIR__.'/../layout/head.php'; ?>
</head>
<body>
  <?php include __DIR__.'/../layout/header.php'; ?>
  <main class="container">
    <div class="card">
      <h2 class="title">Portal</h2>
      <ul>
        <li><a href="<?php echo htmlspecialchars($folder); ?>" target="_blank">Carpeta integral</a></li>
      </ul>
    </div>
  </main>
  <?php include __DIR__.'/../layout/footer.php'; ?>
</body>
</html>

