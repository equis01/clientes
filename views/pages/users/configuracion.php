<?php
if(session_status()!==PHP_SESSION_ACTIVE){ session_start(); }
if(!isset($_SESSION['user'])){header('Location: /users/login');exit;}
require_once dirname(__DIR__,3).'/config/config.php';
require_once dirname(__DIR__,3).'/lib/env.php';
require_once dirname(__DIR__,3).'/lib/db.php';
$client=(isset($_SESSION['client_name'])?$_SESSION['client_name']:$_SESSION['user']);
require_once dirname(__DIR__,3).'/lib/gas.php';
$rec=gas_users(isset($_SESSION['user'])?$_SESSION['user']:null);
if($rec['ok'] && is_array($rec['user'])){
  $alias=isset($rec['user']['alias'])?$rec['user']['alias']:'';
  if(is_string($alias) && trim($alias)!==''){
    $_SESSION['client_name']=trim($alias);
    $client=trim($alias);
  }
}
$username=$_SESSION['user'];
$msg=null;$err=null;
if(isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD']==='POST'){
  $current=isset($_POST['current_password'])?trim($_POST['current_password']):'';
  $new=isset($_POST['new_password'])?trim($_POST['new_password']):'';
  $confirm=isset($_POST['confirm_password'])?trim($_POST['confirm_password']):'';
  if($current===''||$new===''||$confirm===''){ $err='Faltan datos'; }
  elseif(strlen($new)<8){ $err='La nueva contraseña debe tener al menos 8 caracteres'; }
  elseif($new!==$confirm){ $err='Las contraseñas no coinciden'; }
  if(!$err){
    $url=env('GAS_CHANGE_PASS_URL');
    if($url){
      $ch=curl_init($url);
      $body=http_build_query(['username'=>$username,'current_password'=>$current,'new_password'=>$new]);
      curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>20,CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>$body,CURLOPT_HTTPHEADER=>['Accept: application/json'],CURLOPT_FOLLOWLOCATION=>true,CURLOPT_MAXREDIRS=>5]);
      $raw=curl_exec($ch); $status=curl_getinfo($ch,CURLINFO_HTTP_CODE); $errCurl=curl_error($ch); curl_close($ch);
      $txt=is_string($raw)?trim($raw):'';
      if($status>=200 && $status<400 && $txt===''){ $msg='Contraseña actualizada'; }
      else if($txt!==''){
        $data=json_decode($txt,true);
        if(is_array($data) && !empty($data['ok'])){ $msg=isset($data['message'])?$data['message']:'Contraseña actualizada'; }
        else if($status>=200 && $status<400){ $msg='Contraseña actualizada'; }
        else { $err=is_array($data)?(isset($data['error'])?$data['error']:'Respuesta inválida'):'Conexión fallida'; }
      } else {
        $err='Conexión fallida'.($errCurl?(' - '.$errCurl):'');
      }
    } else { $err='Endpoint de cambio de contraseña no configurado'; }
  }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <?php $pageTitle='Configuración'; include dirname(__DIR__,2).'/layout/head.php'; ?>
</head>
<body>
  <?php include dirname(__DIR__,2).'/layout/header.php'; ?>
  <main class="container">
    <div class="card">
      <h2 class="title">Configuración</h2>
      <div class="subtitle">Usuario: <?php echo htmlspecialchars($_SESSION['user']); ?></div>
      <?php if($msg){ ?><div class="alert-success"><?php echo htmlspecialchars($msg); ?></div><?php } ?>
      <?php if($err){ ?><div class="alert-error"><?php echo htmlspecialchars($err); ?></div><?php } ?>
      <form method="post" class="form-grid">
        <div class="field">
          <label for="current_password">Contraseña actual</label>
          <input class="input" id="current_password" type="password" name="current_password" autocomplete="current-password" required>
        </div>
        <div class="field">
          <label for="new_password">Nueva contraseña</label>
          <input class="input" id="new_password" type="password" name="new_password" autocomplete="new-password" minlength="8" required>
        </div>
        <div class="field">
          <label for="confirm_password">Confirmar contraseña</label>
          <input class="input" id="confirm_password" type="password" name="confirm_password" autocomplete="new-password" minlength="8" required>
        </div>
        <div class="actions">
          <button type="submit" class="btn">Guardar</button>
        </div>
      </form>
    </div>
  </main>
  <?php include dirname(__DIR__,2).'/layout/footer.php'; ?>
</body>
</html>
