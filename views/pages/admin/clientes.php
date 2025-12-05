<?php
if(session_status()!==PHP_SESSION_ACTIVE){ session_start(); }
if(!isset($_SESSION['user'])){header('Location: /login');exit;}
if(empty($_SESSION['is_admin'])){ http_response_code(403); header('Location: /errores?code=403&msg=Acceso restringido'); exit; }
require_once __DIR__.'/../../../config/config.php';
require_once __DIR__.'/../../../lib/env.php';
$msg=null;$err=null;
function http_get_json($url){
  $raw=null;$status=null;$err=null;
  if(function_exists('curl_init')){
    $ch=curl_init($url);
    curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>15,CURLOPT_HTTPHEADER=>['Accept: application/json'],CURLOPT_FOLLOWLOCATION=>true,CURLOPT_MAXREDIRS=>5]);
    $raw=curl_exec($ch); $status=curl_getinfo($ch,CURLINFO_HTTP_CODE); $err=curl_error($ch); curl_close($ch);
  } else {
    $ctx=stream_context_create(['http'=>['method'=>'GET','timeout'=>15,'header'=>'Accept: application/json','ignore_errors'=>true]]);
    $raw=@file_get_contents($url,false,$ctx);
  }
  $data=$raw?json_decode($raw,true):null; return [$data,$status,$err];
}
function http_post_json($url,$body){
  $raw=null;$status=null;$err=null;
  if(function_exists('curl_init')){
    $ch=curl_init($url);
    curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>20,CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>$body,CURLOPT_HTTPHEADER=>['Content-Type: application/x-www-form-urlencoded'],CURLOPT_FOLLOWLOCATION=>true,CURLOPT_MAXREDIRS=>5]);
    $raw=curl_exec($ch); $status=curl_getinfo($ch,CURLINFO_HTTP_CODE); $err=curl_error($ch); curl_close($ch);
  } else {
    $ctx=stream_context_create(['http'=>['method'=>'POST','timeout'=>20,'header'=>'Content-Type: application/x-www-form-urlencoded','content'=>$body,'ignore_errors'=>true]]);
    $raw=@file_get_contents($url,false,$ctx);
  }
  $data=$raw?json_decode($raw,true):null; return [$data,$status,$err];
}
if(isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD']==='POST'){
  $username=trim($_POST['username']??'');
  $password=trim($_POST['password']??'');
  $drive_url=trim($_POST['drive_url']??'');
  $email=trim($_POST['email']??'');
  $alias=trim($_POST['alias']??'');
  $valid=isset($_POST['valid'])?trim($_POST['valid']):'';
  if($username===''||$password===''){ $err='Faltan datos'; }
  else {
    $base=env('GAS_ADMINS_URL');
    if(!$base){ $err='Endpoint admins no configurado'; }
    else {
      $url=$base.'?action=create_user';
      $body=http_build_query(['username'=>$username,'password'=>$password,'drive_url'=>$drive_url,'email'=>$email,'alias'=>$alias,'valid'=>$valid]);
      list($data,$status,$e)=http_post_json($url,$body);
      if(is_array($data)&&!empty($data['ok'])){ $msg=$data['message']??'Actualizado'; }
      else { $err=is_array($data)?($data['error']??'Error'):('Conexión fallida'); }
    }
  }
}
$users=[];$headers=[];
$baseList=env('GAS_USERS_Q_URL');
if($baseList){
  $url=$baseList.'?action=users';
  list($data,$status,$e)=http_get_json($url);
  if(is_array($data)&&!empty($data['ok'])&&is_array($data['users'])){ $users=$data['users']; }
}
$client=(isset($_SESSION['client_name'])?$_SESSION['client_name']:$_SESSION['user']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <?php $pageTitle='Portal'; include dirname(__DIR__).'/../layout/head.php'; ?>
</head>
<body>
<?php include dirname(__DIR__).'/../layout/header.php'; ?>
<?php include dirname(__DIR__).'/../layout/modal_admins.php'; ?>
  <main class="container">
    <div class="card">
      <h2 class="title">Portal</h2>
      <div style="overflow:auto">
        <div style="display:flex;gap:12px;align-items:flex-end;margin-bottom:10px">
          <div class="field" style="max-width:320px"><label>Buscar<input class="input" type="text" id="searchUser" placeholder="Buscar por usuario, alias o correo"></label></div>
          <div class="field"><label>Por página<select class="input" id="pageSize"><option value="25">25</option><option value="50">50</option><option value="75">75</option></select></label></div>
          <div class="actions" style="margin-top:0"><button type="button" class="btn secondary" id="prevPage">Anterior</button><button type="button" class="btn secondary" id="nextPage">Siguiente</button><span id="pageInfo" style="margin-left:8px;font-weight:600"></span></div>
        </div>
        <table style="width:100%;border-collapse:collapse">
          <thead>
            <tr>
              <th style="text-align:left">Usuario</th>
              <th style="text-align:left">Alias</th>
              <th style="text-align:left">Correo</th>
              <th style="text-align:left">URL</th>
              <th style="text-align:left">Acceso a portal <button type="button" class="btn secondary" style="margin-left:4px;padding:0;width:22px;height:22px;line-height:22px;border-radius:50%" onclick="adminInfoOpen('portal')">?</button></th>
              <th style="text-align:left">Editar</th>
            </tr>
          </thead>
          <tbody>
            <?php if(count($users)===0){ ?>
              <tr><td colspan="5">Sin usuarios</td></tr>
            <?php } else { foreach($users as $u){ ?>
              <tr>
                <td><?php echo htmlspecialchars($u['username']??''); ?></td>
                <td><?php echo htmlspecialchars($u['alias']??''); ?></td>
                <td><?php echo htmlspecialchars($u['email']??''); ?></td>
                <td><a target="_blank" href="<?php echo htmlspecialchars($u['drive_url']??'#'); ?>">abrir</a></td>
                <td><?php echo intval($u['portal_enabled']??1)===1?'✔':'✘'; ?></td>
                <td><button class="btn secondary" type="button" data-edit="<?php echo htmlspecialchars($u['username']??''); ?>">Editar</button></td>
              </tr>
            <?php } } ?>
          </tbody>
        </table>
      </div>
    </div>
    <div class="actions" style="justify-content:flex-end;margin-top:12px"><a class="btn" href="/admin/clientes/crear">Crear usuario</a></div>
  </main>
  <?php include dirname(__DIR__).'/../layout/footer.php'; ?>
  <script>
  (function(){
    var q=document.getElementById('searchUser');
    var ps=document.getElementById('pageSize');
    var prev=document.getElementById('prevPage');
    var next=document.getElementById('nextPage');
    var info=document.getElementById('pageInfo');
    var rows=[].slice.call(document.querySelectorAll('tbody tr'));
    var page=1; var size=ps?parseInt(ps.value,10):25;
    function filtered(){
      var v=(q&&q.value||'').toLowerCase();
      return rows.filter(function(tr){ return tr.textContent.toLowerCase().indexOf(v)>-1; });
    }
    function render(){
      var list=filtered(); var total=list.length; var pages=Math.max(1, Math.ceil(total/size)); if(page>pages) page=pages; if(page<1) page=1;
      rows.forEach(function(tr){ tr.style.display='none'; });
      var start=(page-1)*size; var end=Math.min(start+size,total);
      for(var i=start;i<end;i++){ list[i].style.display=''; }
      if(info) info.textContent='Página '+page+' de '+pages+' ('+total+' usuarios)';
    }
    if(q) q.addEventListener('input',function(){ page=1; render(); });
    if(ps) ps.addEventListener('change',function(){ size=parseInt(ps.value,10)||25; page=1; render(); });
    if(prev) prev.addEventListener('click',function(){ page=Math.max(1,page-1); render(); });
    if(next) next.addEventListener('click',function(){ page=page+1; render(); });
    render();
    document.querySelectorAll('button[data-edit]').forEach(function(btn){
      btn.addEventListener('click',function(){
        var u=this.getAttribute('data-edit')||'';
        window.location.href='/admin/clientes/editar?u='+encodeURIComponent(u);
      });
    });
  })();
  </script>
</body>
</html>
