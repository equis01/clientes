<?php
if(session_status()!==PHP_SESSION_ACTIVE){ session_start(); }
if(!isset($_SESSION['user'])){header('Location: /login');exit;}
if(empty($_SESSION['is_admin']) || empty($_SESSION['is_super_admin'])){ http_response_code(403); header('Location: /errores?code=403&msg=Acceso restringido'); exit; }
require_once dirname(__DIR__,3).'/lib/db.php';
$jsonPath=dirname(__DIR__,3).'/data/admin.json';
$store=['admins'=>[],'invites'=>[]];
// Preferir BD si está disponible, con fallback a JSON
$adminsDb=listAdmins();
if(is_array($adminsDb) && count($adminsDb)>0){ $store['admins']=$adminsDb; }
else if(is_file($jsonPath)){
  $raw=@file_get_contents($jsonPath); $j=$raw?json_decode($raw,true):null; if(is_array($j) && is_array($j['admins']??null)) $store['admins']=$j['admins'];
}
$msg=null;$err=null;
$supers=['rsaucedo@mediosconvalor.com','aguzman@mediosconvalor.com','sistemas@mediosconvalor.com'];
$self=strtolower($_SESSION['user']);
if($_SERVER['REQUEST_METHOD']==='POST'){
  $action=trim($_POST['action']??'');
  if($action==='create'){
    $email=strtolower(trim($_POST['email']??''));
    $name=trim($_POST['name']??'');
    $pass=trim($_POST['password']??'');
    if($email===''||!preg_match('/@mediosconvalor\.com$/i',$email)){ $err='Correo inválido'; }
    else if($pass===''){ $err='Contraseña requerida'; }
    else {
      $exists=is_array(findAdminByEmail($email));
      if($exists){ $err='Ya existe'; }
      else {
        $ok=createAdmin($email,$name,password_hash($pass,PASSWORD_DEFAULT),$_SESSION['user']??'');
        if($ok){ $msg='Admin creado'; $store['admins']=listAdmins(); }
        else { $err='Error al crear admin'; }
      }
    }
  } else if($action==='delete'){
    $email=strtolower(trim($_POST['email']??''));
    if(in_array($email,$supers,true)){ $err='No se puede eliminar un super admin'; }
    else if($email===$self){ $err='No puedes eliminar tu propia cuenta'; }
    else {
      if(deleteAdmin($email)){ $msg='Admin eliminado'; $store['admins']=listAdmins(); }
      else { $err='Error al eliminar admin'; }
    }
  } else if($action==='edit'){
    $email=strtolower(trim($_POST['email']??''));
    $name=trim($_POST['name']??'');
    $pass=trim($_POST['password']??'');
    if(in_array($email,$supers,true)){ $err='No se puede editar un super admin'; }
    else if($name===''){ $err='Nombre requerido'; }
    else if($pass===''){ $err='Contraseña requerida'; }
    else {
      $ok=updateAdmin($email,$name,password_hash($pass,PASSWORD_DEFAULT));
      if($ok){ $msg='Admin actualizado'; $store['admins']=listAdmins(); }
      else { $err='Error al actualizar admin'; }
    }
  }
}
?>
<!DOCTYPE html>
<html lang="es"><head><?php $pageTitle='Super Admin'; include dirname(__DIR__).'/../layout/head.php'; ?></head>
<body>
<?php include dirname(__DIR__).'/../layout/header.php'; ?>
<main class="container">
  <div class="card">
    <h2 class="title">Administradores</h2>
    <form method="get" action="/admin/super" class="filter" style="margin-bottom:8px;display:flex;gap:8px;align-items:center;flex-wrap:wrap">
      <input type="text" class="input" name="q" placeholder="Buscar por email o nombre" value="<?php echo htmlspecialchars($_GET['q']??''); ?>" id="adminSearch">
      <button type="submit" class="btn">Buscar</button>
      <button type="button" class="btn secondary" id="openCreate">Crear</button>
    </form>
    <?php if($msg){ ?><div class="alert-success"><?php echo htmlspecialchars($msg); ?></div><?php } ?>
    <?php if($err){ ?><div class="alert-error"><?php echo htmlspecialchars($err); ?></div><?php } ?>
    <div style="overflow:auto">
      <table style="width:100%;border-collapse:collapse">
        <thead><tr><th>Email</th><th>Nombre</th><th>Acciones</th></tr></thead>
        <tbody>
          <?php $q=strtolower(trim($_GET['q']??'')); foreach(($store['admins']??[]) as $a){ $ae=strtolower($a['email']??''); $isSuperEntry=in_array($ae,$supers,true); $isSelf=($ae===$self); if($q!=='' && strpos($ae,$q)===false && strpos(strtolower($a['name']??''),$q)===false) continue; ?>
            <tr>
              <td><?php echo htmlspecialchars($a['email']??''); ?></td>
              <td><?php echo htmlspecialchars($a['name']??''); ?></td>
              <td>
                <?php if(!$isSuperEntry){ ?>
                  <?php if($isSelf){ ?>
                    <a class="btn secondary" href="/admin/configuracion">Editar</a>
                  <?php } else { ?>
                    <button type="button" class="btn secondary btn-edit" data-email="<?php echo htmlspecialchars($a['email']??''); ?>" data-name="<?php echo htmlspecialchars($a['name']??''); ?>">Editar</button>
                  <?php } ?>
                  <?php if(!$isSelf){ ?>
                    <form method="post" style="display:inline">
                      <input type="hidden" name="action" value="delete">
                      <input type="hidden" name="email" value="<?php echo htmlspecialchars($a['email']??''); ?>">
                      <button type="submit" class="btn secondary">Eliminar</button>
                    </form>
                  <?php } ?>
                <?php } else { echo $isSelf?'<a class="btn secondary" href="/admin/configuracion">Editar</a>':'-'; } ?>
              </td>
            </tr>
          <?php } ?>
        </tbody>
      </table>
    </div>
  </div>
  <!-- Crear modal -->
  <div id="createModal" class="modal" style="display:none">
    <div class="modal-content">
      <div class="modal-title">Crear administrador</div>
      <form method="post" class="form-grid" id="createForm">
        <input type="hidden" name="action" value="create">
        <div class="field"><label>Correo<input class="input" type="email" name="email" placeholder="alguien@mediosconvalor.com" required></label></div>
        <div class="field"><label>Nombre<input class="input" type="text" name="name"></label></div>
        <div class="field"><label>Contraseña<input class="input" type="password" name="password" required></label></div>
        <div class="actions"><button type="submit" class="btn">Crear</button><button type="button" class="btn secondary" id="closeCreate">Cancelar</button></div>
      </form>
    </div>
  </div>
  <!-- Editar modal -->
  <div id="editModal" class="modal" style="display:none">
    <div class="modal-content">
      <div class="modal-title">Editar administrador</div>
      <form method="post" class="form-grid" id="editForm">
        <input type="hidden" name="action" value="edit">
        <input type="hidden" name="email" id="editEmailHidden">
        <div class="field"><label>Correo<input class="input" type="email" id="editEmail" readonly></label></div>
        <div class="field"><label>Nombre<input class="input" type="text" name="name" id="editName" required></label></div>
        <div class="field"><label>Nueva contraseña<input class="input" type="password" name="password" required></label></div>
        <div class="actions"><button type="submit" class="btn">Guardar</button><button type="button" class="btn secondary" id="closeEdit">Cancelar</button></div>
      </form>
    </div>
  </div>
</main>
<?php include dirname(__DIR__).'/../layout/footer.php'; ?>
<script>
(function(){
  var q=document.getElementById('adminSearch');
  if(q){ q.addEventListener('input',function(){ var v=q.value.toLowerCase(); document.querySelectorAll('tbody tr').forEach(function(tr){ var t=tr.textContent.toLowerCase(); tr.style.display=t.indexOf(v)>=0?'':'none'; }); }); }
  var cm=document.getElementById('createModal'); var oc=document.getElementById('openCreate'); var cc=document.getElementById('closeCreate');
  if(oc){ oc.addEventListener('click',function(){ if(cm) cm.style.display='flex'; }); }
  if(cc){ cc.addEventListener('click',function(){ if(cm) cm.style.display='none'; }); }
  document.querySelectorAll('.btn-edit').forEach(function(b){ b.addEventListener('click',function(){ var em=b.getAttribute('data-email'); var nm=b.getAttribute('data-name'); var m=document.getElementById('editModal'); if(m){ document.getElementById('editEmail').value=em; document.getElementById('editEmailHidden').value=em; document.getElementById('editName').value=nm; m.style.display='flex'; } }); });
  var ce=document.getElementById('closeEdit'); if(ce){ ce.addEventListener('click',function(){ var m=document.getElementById('editModal'); if(m) m.style.display='none'; }); }
})();
</script>
</body></html>
