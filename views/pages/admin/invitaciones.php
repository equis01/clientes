<?php
if(session_status()!==PHP_SESSION_ACTIVE){ session_start(); }
if(empty($_SESSION['is_admin'])){ http_response_code(403); header('Location: /errores?code=403&msg=Acceso restringido'); exit; }
require_once dirname(__DIR__,3).'/lib/env.php';
require_once dirname(__DIR__,3).'/lib/format.php';
function smtp_send($host,$port,$secure,$user,$pass,$to,$subject,$body,$from){
  $timeout=25; $errno=0; $errstr='';
  if($secure==='ssl'){ $host='ssl://'.$host; }
  $fp=@fsockopen($host,$port,$errno,$errstr,$timeout);
  if(!$fp){ return false; }
  stream_set_timeout($fp,$timeout);
  $readAll=function() use ($fp){ $buf=''; while(($line=fgets($fp,512))!==false){ $buf.=$line; if(strlen($line)>=4 && $line[3]!== '-') break; } return $buf; };
  $write=function($cmd) use ($fp){ fwrite($fp,$cmd."\r\n"); };
  $banner=$readAll(); if(strpos($banner,'220')!==0){ fclose($fp); return false; }
  $write('EHLO mediosconvalor.com'); $ehlo=$readAll(); if(strpos($ehlo,'250')!==0){ fclose($fp); return false; }
  if($secure==='tls'){
    $write('STARTTLS'); $tls=$readAll(); if(strpos($tls,'220')!==0){ fclose($fp); return false; }
    @stream_socket_enable_crypto($fp,true,STREAM_CRYPTO_METHOD_TLS_CLIENT);
    $write('EHLO mediosconvalor.com'); $ehlo2=$readAll(); if(strpos($ehlo2,'250')!==0){ fclose($fp); return false; }
  }
  $write('AUTH LOGIN'); $auth1=$readAll(); if(strpos($auth1,'334')!==0){ fclose($fp); return false; }
  $write(base64_encode($user)); $auth2=$readAll(); if(strpos($auth2,'334')!==0){ fclose($fp); return false; }
  $write(base64_encode($pass)); $auth3=$readAll(); if(strpos($auth3,'235')!==0){ fclose($fp); return false; }
  $write('MAIL FROM:<'.$from.'>'); $mfrom=$readAll(); if(strpos($mfrom,'250')!==0){ fclose($fp); return false; }
  $write('RCPT TO:<'.$to.'>'); $rcpt=$readAll(); if(strpos($rcpt,'250')!==0){ fclose($fp); return false; }
  $write('DATA'); $data=$readAll(); if(strpos($data,'354')!==0){ fclose($fp); return false; }
  $headers='Date: '.date('r')."\r\n".
           'Message-ID: <'.bin2hex(random_bytes(8)).'@mediosconvalor.com>'."\r\n".
           'From: '.$from."\r\n".
           'To: '.$to."\r\n".
           'Subject: '.$subject."\r\n".
           'MIME-Version: 1.0' . "\r\n" .
           'Content-Type: text/html; charset=UTF-8' . "\r\n" .
           'Content-Transfer-Encoding: 8bit' . "\r\n";
  $msg=$headers."\r\n".$body."\r\n".".\r\n";
  fwrite($fp,$msg);
  $resp=$readAll();
  $write('QUIT');
  fclose($fp);
  return strpos($resp,'250')===0;
}
if(!isset($_SESSION['user'])){header('Location: /login');exit;}

$jsonPath=dirname(__DIR__,3).'/data/admin.json';
$store=['admins'=>[],'invites'=>[]];
if(is_file($jsonPath)){
  $raw=@file_get_contents($jsonPath); $j=$raw?json_decode($raw,true):null; if(is_array($j)) $store=$j;
}
$msg=null;$err=null;$created=null;
if($_SERVER['REQUEST_METHOD']==='POST'){
  $email=strtolower(trim($_POST['email']??''));
  $name=trim($_POST['name']??'');
  $days=intval($_POST['days']??7); if($days<1) $days=7; if($days>90) $days=90;
  if($email==='' || !preg_match('/@mediosconvalor\.com$/i',$email)){ $err='Correo inválido (solo @mediosconvalor.com)'; }
  else {
    $existsAdmin=false; foreach(($store['admins']??[]) as $a){ if(strtolower($a['email']??'')===$email){ $existsAdmin=true; break; } }
    if($existsAdmin){ $err='Ya existe una cuenta para este correo'; }
    if(!$err){
      $hasActiveInvite=false;
      foreach(($store['invites']??[]) as $i){
        $same=strtolower($i['email']??'')===$email;
        $used=!empty($i['usedAt']);
        $exp=strtotime($i['expiresAt']??''); $expired=($exp!==false && time()>$exp);
        if($same && !$used && !$expired){ $hasActiveInvite=true; break; }
      }
      if($hasActiveInvite){ $err='Ya existe una invitación activa para este correo'; }
    }
    if(!$err){
      $token=bin2hex(random_bytes(16));
      $expiresAt=date('c', time()+($days*86400));
      $inv=[
        'email'=>$email,
        'name'=>$name,
        'token'=>$token,
        'inviteLink'=>'/admin/invite?token='.$token,
        'invitedBy'=>$_SESSION['user'],
        'expiresAt'=>$expiresAt,
        'createdAt'=>date('c'),
      ];
      $store['invites'][]=$inv;
      @file_put_contents($jsonPath, json_encode($store, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
      $created=$inv['inviteLink'];
      $to=$email; $subject='Invitación al portal de admins';
      $host=isset($_SERVER['HTTP_HOST'])?('https://'.$_SERVER['HTTP_HOST']):'';
      $link=$host.$inv['inviteLink'];
      $body='<!DOCTYPE html><html><body style="margin:0;padding:0;background:#f6f7f9">'
        .'<div style="max-width:600px;margin:20px auto;background:#ffffff;border:1px solid #e2e8f0;border-radius:12px;padding:20px;font-family:Segoe UI,Roboto,Arial,sans-serif;color:#0b1222">'
        .'<h2 style="margin:0 0 12px;font-size:20px">Invitación al portal de administradores</h2>'
        .'<p style="margin:0 0 12px">Hola'.($name?' '.$name:'').',</p>'
        .'<p style="margin:0 0 16px">Has sido invitado al portal de administradores de Medios con Valor.</p>'
        .'<p style="margin:0 0 16px">Usa este botón para registrarte (expira el '.htmlspecialchars($expiresAt).'):</p>'
        .'<p style="margin:0 0 20px"><a href="'.$link.'" style="display:inline-block;background:#00DC2A;color:#0b1222;text-decoration:none;padding:12px 18px;border-radius:8px;font-weight:600">Registrarme</a></p>'
        .'<p style="margin:0 0 8px;font-size:13px;color:#64748b">Si el botón no funciona, copia y pega este enlace en tu navegador:</p>'
        .'<p style="margin:0 0 12px"><a href="'.$link.'" style="color:#0366d6">'.$link.'</a></p>'
        .'<hr style="border:0;border-top:1px solid #e2e8f0;margin:20px 0">'
        .'<p style="margin:0;font-size:12px;color:#64748b">Gracias,<br>MCVClientes</p>'
        .'</div></body></html>';
      $smtpHost=env('SMTP_HOST'); $smtpPort=intval(env('SMTP_PORT',587)); $smtpUser=env('SMTP_USER'); $smtpPass=env('SMTP_PASS'); $smtpSecure=strtolower(env('SMTP_SECURE','tls'));
      $fromSender=$smtpUser?:'pruebaapp@mediosconvalor.com';
      $headers='MIME-Version: 1.0' . "\r\n" . 'Content-Type: text/html; charset=UTF-8' . "\r\n" . 'From: '.$fromSender."\r\n".'Reply-To: sistemas@mediosconvalor.com' . "\r\n";
      $sent=false;
      if($smtpHost && $smtpUser && $smtpPass){ $sent=smtp_send($smtpHost,$smtpPort,$smtpSecure,$smtpUser,$smtpPass,$to,$subject,$body,$fromSender); }
      if(!$sent){ $sent=@mail($to,$subject,$body,$headers); }
      $msg=$sent?'Invitación enviada':'Invitación generada (no se pudo enviar correo, copia el enlace)';
    }
  }
}
?>
<!DOCTYPE html>
<html lang="es"><head><?php $pageTitle='Invitaciones'; include dirname(__DIR__).'/../layout/head.php'; ?></head>
<body>
<?php include dirname(__DIR__).'/../layout/header.php'; ?>
<main class="container">
  <div class="card">
    <h2 class="title">Crear invitación</h2>
    <?php if($msg){ ?><div class="alert-success"><?php echo htmlspecialchars($msg); ?><?php if($created){ ?> — <a href="#" id="copyInvLink" data-link="<?php echo htmlspecialchars($created); ?>">Copiar enlace</a><?php } ?></div><?php } ?>
    <?php if($err){ ?><div class="alert-error"><?php echo htmlspecialchars($err); ?></div><?php } ?>
    <form method="post" class="form-grid">
      <div class="field"><label>Nombre<input class="input" type="text" name="name"></label></div>
      <div class="field"><label>Correo<input class="input" type="email" name="email" placeholder="alguien@mediosconvalor.com" required></label></div>
      <div class="field"><label>Caducidad<select class="input" name="days"><option value="7">7 días</option><option value="14">14 días</option><option value="30">30 días</option></select></label></div>
      <div class="actions"><button type="submit" class="btn">Generar</button><a class="btn secondary" href="/admin">Volver</a></div>
    </form>
  </div>
  <div class="card" style="margin-top:12px">
    <h2 class="title">Invitaciones</h2>
    <form method="get" action="/admin/invitaciones" class="filter" style="margin-bottom:8px">
      <input type="text" class="input" name="q" placeholder="Buscar por email o invitó" value="<?php echo htmlspecialchars($_GET['q']??''); ?>">
      <label style="margin-left:8px">Por página
        <select name="per" class="input">
          <?php $perSel=intval($_GET['per']??10); foreach([10,20] as $opt){ $sel=$perSel===$opt?' selected':''; echo '<option value="'.$opt.'"'.$sel.'>'.$opt.'</option>'; } ?>
        </select>
      </label>
      <button type="submit" class="btn" style="margin-left:8px">Buscar</button>
    </form>
    <div style="overflow:auto">
      <table style="width:100%;border-collapse:collapse">
        <thead><tr><th>Email</th><th>Invitó</th><th>Caduca</th><th>Enlace</th></tr></thead>
        <tbody>
          <?php
            $invites=is_array($store['invites']??null)?$store['invites']:[];
            $q=strtolower(trim($_GET['q']??''));
            $invites=array_values($invites);
            if($q!==''){
              $invites=array_values(array_filter($invites,function($i) use ($q){
                $e=strtolower($i['email']??''); $by=strtolower($i['invitedBy']??'');
                return (strpos($e,$q)!==false) || (strpos($by,$q)!==false);
              }));
            }
            usort($invites,function($a,$b){ return strcmp($b['createdAt']??'', $a['createdAt']??''); });
            $page=max(1,intval($_GET['page']??1));
            $per=max(1,intval($_GET['per']??10)); if(!in_array($per,[10,20],true)) $per=10;
            $total=count($invites); $pages=max(1,intval(ceil($total/$per)));
            $page=min($page,$pages);
            $slice=array_slice($invites,($page-1)*$per,$per);
            foreach($slice as $i){
              $expTs=strtotime($i['expiresAt']??''); $expired=($expTs!==false && time()>$expTs);
              $used=!empty($i['usedAt']);
              $cadText=$used?'Aprobado':($expired?'Caducó':fmt_date($i['expiresAt']??'', env('TIMEZONE')));
            ?>
            <tr>
              <td><?php echo htmlspecialchars($i['email']); ?></td>
              <td><?php echo htmlspecialchars($i['invitedBy']); ?></td>
              <td><?php echo htmlspecialchars($cadText); ?></td>
              <td>
                <?php if(!$used && !$expired){ ?>
                  <a href="#" class="copy-link" data-link="<?php echo htmlspecialchars($i['inviteLink']); ?>">copiar</a>
                <?php } else { echo '-'; } ?>
              </td>
            </tr>
          <?php } ?>
        </tbody>
      </table>
    </div>
    <?php if(($pages??1)>1){ $base='/admin/invitaciones?per='.$per; $prev=$page>1?($base.'&page='.($page-1)):null; $next=$page<$pages?($base.'&page='.($page+1)):null; ?>
      <div class="pagination" style="margin-top:8px">
        <?php if($prev){ ?><a class="btn secondary" href="<?php echo htmlspecialchars($prev); ?>">« Anterior</a><?php } ?>
        <span style="margin:0 8px">Página <?php echo $page; ?> de <?php echo $pages; ?></span>
        <?php if($next){ ?><a class="btn secondary" href="<?php echo htmlspecialchars($next); ?>">Siguiente »</a><?php } ?>
      </div>
    <?php } ?>
  </div>
</main>
<?php include dirname(__DIR__).'/../layout/footer.php'; ?>
<script>
(function(){
  function copy(text){
    try{ navigator.clipboard.writeText(text); alert('Enlace copiado'); }
    catch(_){ var ta=document.createElement('textarea'); ta.value=text; document.body.appendChild(ta); ta.select(); try{ document.execCommand('copy'); alert('Enlace copiado'); }catch(_){ alert(text); } document.body.removeChild(ta); }
  }
  var btn=document.getElementById('copyInvLink'); if(btn){ btn.addEventListener('click',function(e){ e.preventDefault(); copy(location.origin + (btn.dataset.link||'')); }); }
  var links=document.querySelectorAll('.copy-link');
  links.forEach(function(a){ a.addEventListener('click',function(e){ e.preventDefault(); copy(location.origin + (a.dataset.link||'')); }); });
})();
</script>
</html>
