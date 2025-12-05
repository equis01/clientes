<?php
if(session_status()!==PHP_SESSION_ACTIVE){ session_start(); }
if(!isset($_SESSION['user'])){header('Location: /login');exit;}
if(empty($_SESSION['is_admin'])){ http_response_code(403); header('Location: /errores?code=403&msg=Acceso restringido'); exit; }
require_once __DIR__.'/../../../lib/env.php';
$msg=null;$err=null;
if($_SERVER['REQUEST_METHOD']==='POST'){
  $username=trim($_POST['username']??'');
  $password=trim($_POST['password']??'');
  $drive_url=trim($_POST['drive_url']??'');
  $email=trim($_POST['email']??'');
  if($email!==''){ $parts=array_map('trim', explode(',', $email)); $parts=array_filter($parts,function($s){ return $s!==''; }); $email=implode(',', $parts); }
  $alias=trim($_POST['alias']??'');
  $portal=isset($_POST['portal'])?trim($_POST['portal']):'1';
  $valid='0';
  if($username===''||$password===''){ $err='username y password son requeridos'; }
  else{
    $base=env('GAS_ADMINS_URL');
    if(!$base){ $err='Endpoint admins no configurado'; }
    else {
      $url=$base.'?action=create_user';
      $body=http_build_query(['username'=>$username,'password'=>$password,'drive_url'=>$drive_url,'email'=>$email,'alias'=>$alias,'valid'=>$valid,'portal'=>$portal]);
      $raw=null;$status=null;$e=null; $data=null;
      if(function_exists('curl_init')){ $ch=curl_init($url); curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>20,CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>$body,CURLOPT_HTTPHEADER=>['Content-Type: application/x-www-form-urlencoded'],CURLOPT_FOLLOWLOCATION=>true,CURLOPT_MAXREDIRS=>5]); $raw=curl_exec($ch); $status=curl_getinfo($ch,CURLINFO_HTTP_CODE); $e=curl_error($ch); curl_close($ch);} else { $ctx=stream_context_create(['http'=>['method'=>'POST','timeout'=>20,'header'=>'Content-Type: application/x-www-form-urlencoded','content'=>$body,'ignore_errors'=>true]]); $raw=@file_get_contents($url,false,$ctx);} $data=$raw?json_decode($raw,true):null;
      if(is_array($data)&&!empty($data['ok'])){ $msg=$data['message']??'Usuario creado'; }
      else { $err=is_array($data)?($data['error']??'Error'):(($e&&$e!=='')?('Conexión fallida: '.$e):'Conexión fallida'); }
    }
  }
}
$client=(isset($_SESSION['client_name'])?$_SESSION['client_name']:$_SESSION['user']);
?>
<!DOCTYPE html>
<html lang="es"><head><?php $pageTitle='Crear usuario'; include dirname(__DIR__).'/../layout/head.php'; ?></head>
<body data-users-api="<?php echo htmlspecialchars(env('GAS_USERS_Q_URL')); ?>">
<?php include dirname(__DIR__).'/../layout/header.php'; ?>
<?php include dirname(__DIR__).'/../layout/modal_admins.php'; ?>
<main class="container">
  <div class="card">
    <h2 class="title">Crear usuario</h2>
    <?php if($msg){ ?><div class="alert-success"><?php echo htmlspecialchars($msg); ?></div><?php } ?>
    <?php if($err){ ?><div class="alert-error"><?php echo htmlspecialchars($err); ?></div><?php } ?>
    <form method="post" class="form-grid">
      <div class="field"><label>Usuario<button type="button" class="btn secondary" style="margin-left:6px;padding:0;width:24px;height:24px;line-height:24px;border-radius:50%" onclick="adminInfoOpen('username')">?</button><input class="input" type="text" name="username" required placeholder="ej: QMCon165"></label></div>
      <div class="field"><label>Contraseña
        <div style="display:flex;gap:8px">
          <input class="input" type="text" name="password" id="genPassword" required placeholder="temporal" style="flex:1">
          <button type="button" class="btn secondary" id="btnGenerate">Generar</button>
        </div>
      </label></div>
      <div class="field"><label>URL<input class="input" type="text" name="drive_url" placeholder="https://drive.google.com/..."></label></div>
      <div class="field"><label>Correo<button type="button" class="btn secondary" style="margin-left:6px;padding:0;width:24px;height:24px;line-height:24px;border-radius:50%" onclick="adminInfoOpen('emails')">?</button><input class="input" type="email" name="email" multiple placeholder="correo@dominio.com, otro@dominio.com"></label></div>
      <div class="field"><label>Alias<button type="button" class="btn secondary" style="margin-left:6px;padding:0;width:24px;height:24px;line-height:24px;border-radius:50%" onclick="adminInfoOpen('alias')">?</button><input class="input" type="text" name="alias" placeholder="ej: MEDIOS CON VALOR QRO"></label></div>
      <div class="field"><label>Sucursal<select class="input" id="branchSel"><option value="Q">Querétaro</option><option value="A">Aguascalientes</option><option value="M">Monterrey</option></select></label></div>
      <div class="field"><label>¿Se envió correo con claves?<button type="button" class="btn secondary" style="margin-left:6px;padding:0;width:24px;height:24px;line-height:24px;border-radius:50%" onclick="adminInfoOpen('valid')">?</button><select class="input" disabled><option value="0">No</option></select></label></div>
      <div class="field"><label>Acceso a portal<button type="button" class="btn secondary" style="margin-left:6px;padding:0;width:24px;height:24px;line-height:24px;border-radius:50%" onclick="adminInfoOpen('portal')">?</button><select class="input" name="portal"><option value="1">Sí</option><option value="0">No</option></select></label></div>
      <div class="actions"><button type="submit" class="btn">Crear</button><a class="btn secondary" href="/admin">Volver</a></div>
    </form>
  </div>
</main>
<script>
(function(){
  var btn=document.getElementById('btnGenerate');
  var out=document.getElementById('genPassword');
  function gen(){
    var letters='abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    var digits='0123456789';
    var punct='.?';
    var all=letters+digits+punct;
    function pick(){ return all[Math.floor(Math.random()*all.length)]; }
    function valid(s){
      return /[a-zA-Z]/.test(s) && /\d/.test(s) && /[\.\?]/.test(s);
    }
    var s='';
    do{ s=''; for(var i=0;i<8;i++){ s+=pick(); } } while(!valid(s));
    return s;
  }
  if(btn&&out){ btn.addEventListener('click',function(){ out.value=gen(); out.focus(); out.select(); try{ document.execCommand('copy'); }catch(_){ } }); }
})();
</script>
<script>
(function(){
  var aliasInp=document.querySelector('input[name="alias"]');
  var userInp=document.querySelector('input[name="username"]');
  var branchSel=document.getElementById('branchSel');
  var api=document.body.getAttribute('data-users-api')||'';
  var count=0;
  function proper(s){ s=String(s||'').toLowerCase(); return s? (s[0].toUpperCase()+s.slice(1)) : ''; }
  function initial(s){ var m=String(s||'').trim().match(/[A-Za-zÁÉÍÓÚÜÑ]/); return (m? m[0] : '').toUpperCase().replace('Á','A').replace('É','E').replace('Í','I').replace('Ó','O').replace('Ú','U').replace('Ü','U').replace('Ñ','N'); }
  function pickWord(alias){ var toks=String(alias||'').trim().split(/\s+/); if(toks.length<=1) return toks[0]||''; for(var i=1;i<toks.length;i++){ if(/[A-Za-zÁÉÍÓÚÜÑ]/.test(toks[i])) return toks[i]; } return toks[0]||''; }
  function build(){ var b=(branchSel&&branchSel.value)||'Q'; var a=aliasInp?aliasInp.value:''; var first=initial(a); var word=proper(pickWord(a)); var num=(count>0)?(count+1):Math.floor(100+Math.random()*900); var s=(b||'Q')+(first||'X')+word+String(num); if(userInp){ userInp.value=s; } }
  function getCount(){ if(!api) { build(); return; } try{ fetch(api+'?action=users',{credentials:'omit'}).then(function(r){ return r.json(); }).then(function(j){ if(j&&j.ok&&j.users){ try{ count=Object.keys(j.users).length||0; }catch(_){ count=0; } } }).finally(build); }catch(_){ build(); } }
  if(aliasInp){ aliasInp.addEventListener('input',build); aliasInp.addEventListener('blur',build); }
  if(branchSel){ branchSel.addEventListener('change',build); }
  getCount();
})();
</script>
<?php include dirname(__DIR__).'/../layout/footer.php'; ?>
</body></html>
