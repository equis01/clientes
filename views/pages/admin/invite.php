<?php
if(session_status()!==PHP_SESSION_ACTIVE){ session_start(); }
$jsonPath=dirname(__DIR__,3).'/data/admin.json';
$store=['admins'=>[],'invites'=>[]];
if(is_file($jsonPath)){
  $raw=@file_get_contents($jsonPath); $j=$raw?json_decode($raw,true):null; if(is_array($j)) $store=$j;
}
$token=isset($_GET['token'])?trim($_GET['token']):'';
$invite=null; foreach(($store['invites']??[]) as $i){ if(($i['token']??'')===$token){ $invite=$i; break; } }
$msg=null;$err=null;$ok=false;
function expired($iso){ $ts=strtotime($iso); return $ts!==false && time()>$ts; }
function used($i){ return !empty($i['usedAt']); }
if($_SERVER['REQUEST_METHOD']==='POST'){
  $email=strtolower(trim($_POST['email']??''));
  $name=trim($_POST['name']??'');
  $pass=trim($_POST['password']??'');
  if(!$invite){ $err='Invitación inválida'; }
  else if(expired($invite['expiresAt'])){ $err='Invitación caducada'; }
  else if(used($invite)){ $err='Invitación ya usada'; }
  else if($email===''||!preg_match('/@mediosconvalor\.com$/i',$email)){ $err='Correo inválido'; }
  else if($pass===''){ $err='Contraseña requerida'; }
  else {
    foreach(($store['admins']??[]) as $a){ if(strtolower($a['email']??'')===$email){ $err='Ya existe una cuenta'; break; } }
    if(!$err){
      $store['admins'][]=[ 'email'=>$email, 'name'=>($name?:($invite['name']??'')), 'password_hash'=>password_hash($pass, PASSWORD_BCRYPT), 'createdAt'=>date('c'), 'invitedBy'=>($invite['invitedBy']??'invite'), 'photo'=>'' ];
      // marcar usada
      for($idx=0;$idx<count($store['invites']);$idx++){ if(($store['invites'][$idx]['token']??'')===$token){ $store['invites'][$idx]['usedAt']=date('c'); break; } }
      @file_put_contents($jsonPath, json_encode($store, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
      $ok=true; $msg='Cuenta creada, ya puedes iniciar sesión';
    }
  }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <?php $pageTitle='Registro invitación'; include dirname(__DIR__).'/../layout/head.php'; ?>
  <link rel="stylesheet" href="/assets/css/login.css">
  <style>:root{--admin-only:1}</style>
</head>
<body>
  <div class="page">
    <div class="container">
      <div class="left">
        <div class="login"><img src="https://mediosconvalor.github.io/mcv/img/logo/logo.png" width="225" height="115" alt=""></div>
        <div class="success-message">
          <?php if($msg){ echo htmlspecialchars($msg).' — '; ?><a href="/admin/login">Ir al login</a><?php } ?>
        </div>
        <div class="error-message"><?php if($err){ echo htmlspecialchars($err); } ?></div>
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
          <?php if(!$err && !$ok){ ?>
          <label for="name">NOMBRE</label>
          <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($invite['name']??''); ?>" form="inviteForm">
          <label for="email">CORREO</label>
          <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($invite['email']??''); ?>" form="inviteForm" autocomplete="email" required>
          <label for="password">CONTRASEÑA</label>
          <input type="password" id="password" name="password" form="inviteForm" autocomplete="new-password" required>
          <form id="inviteForm" method="post"></form>
          <input type="submit" id="submit" value="REGISTRARME" form="inviteForm">
          <p class="forgot-password"><a href="/admin/login">Cancelar</a></p>
          <?php } ?>
        </div>
      </div>
    </div>
  </div>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/animejs/2.2.0/anime.min.js"></script>
  <script>
    (function(){
      var current=null; var path=document.querySelector('path');
      function focusAnim(offset,dash){ if(current) current.pause(); if(!path) return; current=anime({targets:'path',strokeDashoffset:{value:offset,duration:700,easing:'easeOutQuart'},strokeDasharray:{value:dash,duration:700,easing:'easeOutQuart'}}); }
      var n=document.getElementById('name'); var e=document.getElementById('email'); var p=document.getElementById('password'); var s=document.getElementById('submit');
      if(n) n.addEventListener('focus',function(){ focusAnim(0,'240 1386'); });
      if(e) e.addEventListener('focus',function(){ focusAnim(-336,'240 1386'); });
      if(p) p.addEventListener('focus',function(){ focusAnim(-336,'240 1386'); });
      if(s) s.addEventListener('focus',function(){ focusAnim(-730,'530 1386'); });
    })();
  </script>
</body>
</html>
