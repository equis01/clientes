<?php
if(session_status()!==PHP_SESSION_ACTIVE){ session_start(); }
if(!isset($_SESSION['user'])){header('Location: /login');exit;}
if(empty($_SESSION['is_admin'])){ http_response_code(403); header('Location: /errores?code=403&msg=Acceso restringido'); exit; }
$jsonPath=dirname(__DIR__,3).'/data/admin.json';
$store=['admins'=>[],'invites'=>[]];
if(is_file($jsonPath)){
  $raw=@file_get_contents($jsonPath); $j=$raw?json_decode($raw,true):null; if(is_array($j)) $store=$j;
}
$email=strtolower($_SESSION['user']);
$current=['email'=>$email,'name'=>$_SESSION['client_name']??'Admin'];
foreach(($store['admins']??[]) as $a){ if(strtolower($a['email']??'')===$email){ $current=$a; break; } }
$msg=null;$err=null;
if($_SERVER['REQUEST_METHOD']==='POST'){
  $name=trim($_POST['name']??'');
  $pass=trim($_POST['password']??'');
  $currentPass=trim($_POST['current_password']??'');
  if($name===''){ $err='Nombre requerido'; }
  else {
    $updated=false;
    foreach($store['admins'] as &$a){
      if(strtolower($a['email']??'')===$email){
        $a['name']=$name; $updated=true;
        if($pass!==''){
          $hash=$a['password_hash']??''; $plain=$a['password']??'';
          $ok=false;
          if($hash){ $ok=password_verify($currentPass,$hash); }
          else if($plain!==''){ $ok=($currentPass===$plain); }
          else { $ok=true; }
          if(!$ok){ $err='Contraseña actual incorrecta'; break; }
          $a['password_hash']=password_hash($pass,PASSWORD_DEFAULT); unset($a['password']);
        }
        break;
      }
    }
    if(!$err){
      if(!$updated){
        $entry=['email'=>$email,'name'=>$name];
        if($pass!==''){
          $hash=$current['password_hash']??''; $plain=$current['password']??'';
          if($hash||$plain){
            $ok=$hash?password_verify($currentPass,$hash):($currentPass===$plain);
            if(!$ok){ $err='Contraseña actual incorrecta'; }
          }
          if(!$err){ $entry['password_hash']=password_hash($pass,PASSWORD_DEFAULT); }
        } else {
          if(isset($current['password_hash'])) $entry['password_hash']=$current['password_hash'];
        }
        if(!$err){ $store['admins'][]=$entry; }
      }
    }
    if(!$err){
      @file_put_contents($jsonPath, json_encode($store, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
      $_SESSION['client_name']=$name; $msg='Datos actualizados';
    }
  }
}
?>
<!DOCTYPE html>
<html lang="es"><head><?php $pageTitle='Configuración Admin'; include dirname(__DIR__).'/../layout/head.php'; ?></head>
<body>
<?php include dirname(__DIR__).'/../layout/header.php'; ?>
<main class="container">
  <div class="card">
    <h2 class="title">Configuración de administrador</h2>
    <?php if($msg){ ?><div class="alert-success"><?php echo htmlspecialchars($msg); ?></div><?php } ?>
    <?php if($err){ ?><div class="alert-error"><?php echo htmlspecialchars($err); ?></div><?php } ?>
    <form method="post" class="form-grid">
      <div class="field"><label>Nombre<input class="input" type="text" name="name" value="<?php echo htmlspecialchars($current['name']??''); ?>" required></label></div>
      <div class="field"><label>Contraseña actual<input class="input" type="password" name="current_password" autocomplete="current-password" placeholder="(requerida si cambias contraseña)"></label></div>
      <div class="field"><label>Nueva contraseña<input class="input" type="password" name="password" autocomplete="new-password" placeholder="(opcional)"></label></div>
      <div class="actions"><button type="submit" class="btn">Guardar</button><a class="btn secondary" href="/admin">Volver</a></div>
    </form>
  </div>
</main>
<?php include dirname(__DIR__).'/../layout/footer.php'; ?>
</body></html>
