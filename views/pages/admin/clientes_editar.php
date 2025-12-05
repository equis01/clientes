<?php
if(session_status()!==PHP_SESSION_ACTIVE){ session_start(); }
if(!isset($_SESSION['user'])){header('Location: /login');exit;}
if(empty($_SESSION['is_admin'])){ http_response_code(403); header('Location: /errores?code=403&msg=Acceso restringido'); exit; }
require_once __DIR__.'/../../../lib/env.php';
$u=isset($_GET['u'])?trim($_GET['u']):'';
$msg=null;$err=null;$dataUser=null;
if($u!==''){
  $baseList=env('GAS_USERS_Q_URL');
  if($baseList){
    $url=$baseList.'?action=users&username='.urlencode($u);
    $raw=null; if(function_exists('curl_init')){ $ch=curl_init($url); curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>15,CURLOPT_HTTPHEADER=>['Accept: application/json'],CURLOPT_FOLLOWLOCATION=>true,CURLOPT_MAXREDIRS=>5]); $raw=curl_exec($ch); curl_close($ch);} else { $raw=@file_get_contents($url); }
    $j=$raw?json_decode($raw,true):null; if(is_array($j)&&!empty($j['ok'])&&is_array($j['users'])){ $dataUser=reset($j['users']); }
  }
}
  if($_SERVER['REQUEST_METHOD']==='POST'){
    $username=trim($_POST['username']??'');
    $password=trim($_POST['password']??'');
    $drive_url=trim($_POST['drive_url']??'');
    $email=trim($_POST['email']??'');
    if($email!==''){ $parts=array_map('trim', explode(',', $email)); $parts=array_filter($parts,function($s){ return $s!==''; }); $email=implode(',', $parts); }
    $alias=trim($_POST['alias']??'');
    $valid=isset($_POST['valid'])?trim($_POST['valid']):'';
    $portal=isset($_POST['portal'])?trim($_POST['portal']):'1';
  if($username===''){ $err='username requerido'; }
  else{
    $base=env('GAS_ADMINS_URL');
    if(!$base){ $err='Endpoint admins no configurado'; }
    else {
      $url=$base.'?action=create_user';
      if($password===''){
        $password=isset($dataUser['password_hash']) && $dataUser['password_hash']!=='' ? $dataUser['password_hash'] : '';
      }
      if($password===''){ $err='Falta contraseña actual en hoja'; }
      else {
        $body=http_build_query(['username'=>$username,'password'=>$password,'drive_url'=>$drive_url,'email'=>$email,'alias'=>$alias,'valid'=>$valid,'portal'=>$portal]);
      $raw=null;$status=null;$e=null; $data=null;
      if(function_exists('curl_init')){ $ch=curl_init($url); curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>20,CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>$body,CURLOPT_HTTPHEADER=>['Content-Type: application/x-www-form-urlencoded'],CURLOPT_FOLLOWLOCATION=>true,CURLOPT_MAXREDIRS=>5]); $raw=curl_exec($ch); $status=curl_getinfo($ch,CURLINFO_HTTP_CODE); $e=curl_error($ch); curl_close($ch);} else { $ctx=stream_context_create(['http'=>['method'=>'POST','timeout'=>20,'header'=>'Content-Type: application/x-www-form-urlencoded','content'=>$body,'ignore_errors'=>true]]); $raw=@file_get_contents($url,false,$ctx);} $data=$raw?json_decode($raw,true):null;
      if(is_array($data)&&!empty($data['ok'])){ $msg=$data['message']??'Usuario actualizado'; }
      else { $err=is_array($data)?($data['error']??'Error'):(($e&&$e!=='')?('Conexión fallida: '.$e):'Conexión fallida'); }
      }
    }
  }
}
$client=(isset($_SESSION['client_name'])?$_SESSION['client_name']:$_SESSION['user']);
?>
<!DOCTYPE html>
<html lang="es"><head><?php $pageTitle='Editar usuario'; include dirname(__DIR__).'/../layout/head.php'; ?></head>
<body>
<?php include dirname(__DIR__).'/../layout/header.php'; ?>
<?php include dirname(__DIR__).'/../layout/modal_admins.php'; ?>
<main class="container">
  <div class="card">
    <h2 class="title">Editar usuario</h2>
    <?php if($msg){ ?><div class="alert-success"><?php echo htmlspecialchars($msg); ?></div><?php } ?>
    <?php if($err){ ?><div class="alert-error"><?php echo htmlspecialchars($err); ?></div><?php } ?>
    <form method="post" class="form-grid">
      <div class="field"><label>Usuario<input class="input" type="text" name="username" required value="<?php echo htmlspecialchars($dataUser['username']??$u); ?>" readonly></label></div>
      <div class="field"><label>Contraseña<input class="input" type="text" name="password" placeholder="(opcional para cambiar)"></label></div>
      <div class="field"><label>URL<input class="input" type="text" name="drive_url" value="<?php echo htmlspecialchars($dataUser['drive_url']??''); ?>"></label></div>
      <div class="field"><label>Correo<button type="button" class="btn secondary" style="margin-left:6px;padding:0;width:24px;height:24px;line-height:24px;border-radius:50%" onclick="adminInfoOpen('emails')">?</button><input class="input" type="email" name="email" multiple value="<?php echo htmlspecialchars($dataUser['email']??''); ?>" placeholder="correo@dominio.com, otro@dominio.com"></label></div>
      <div class="field"><label>Alias<input class="input" type="text" name="alias" value="<?php echo htmlspecialchars($dataUser['alias']??''); ?>"></label></div>
      <div class="field"><label>¿Se envió correo con claves?<button type="button" class="btn secondary" style="margin-left:6px;padding:0;width:24px;height:24px;line-height:24px;border-radius:50%" onclick="adminInfoOpen('valid')">?</button><select class="input" name="valid"><option value="1" <?php echo intval($dataUser['valid']??0)===1?'selected':''; ?>>Sí</option><option value="0" <?php echo intval($dataUser['valid']??0)===0?'selected':''; ?>>No</option></select></label></div>
      <div class="field"><label>Acceso a portal<button type="button" class="btn secondary" style="margin-left:6px;padding:0;width:24px;height:24px;line-height:24px;border-radius:50%" onclick="adminInfoOpen('portal')">?</button><select class="input" name="portal"><option value="1" <?php echo intval($dataUser['portal_enabled']??1)===1?'selected':''; ?>>Sí</option><option value="0" <?php echo intval($dataUser['portal_enabled']??1)===0?'selected':''; ?>>No</option></select></label></div>
      <div class="actions"><button type="submit" class="btn">Guardar</button><a class="btn secondary" href="/admin">Volver</a></div>
    </form>
  </div>
</main>
<?php include dirname(__DIR__).'/../layout/footer.php'; ?>
</body></html>
