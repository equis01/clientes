<?php if(session_status()!==PHP_SESSION_ACTIVE){ session_start(); } if(!isset($_SESSION['user'])){header('Location: /users/login');exit;} $client=isset($_SESSION['client_name'])?$_SESSION['client_name']:$_SESSION['user']; $folder=isset($_SESSION['folder_url'])?$_SESSION['folder_url']:'#'; require_once dirname(__DIR__,3).'/lib/gas.php'; $emailsList=[]; $userRec=gas_users(isset($_SESSION['user'])?$_SESSION['user']:null); if($userRec['ok'] && is_array($userRec['user'])){ $alias=isset($userRec['user']['alias'])?$userRec['user']['alias']:''; if(is_string($alias)&&trim($alias)!==''){ $_SESSION['client_name']=trim($alias); $client=trim($alias); } $raw=isset($userRec['user']['email'])?$userRec['user']['email']:''; $parts=preg_split('/[;,]+/',$raw); if(is_array($parts)){ foreach($parts as $p){ $t=trim((string)$p); if($t!==''){ $emailsList[]=$t; } } } } ?>
<!DOCTYPE html>
<html lang="es">
<head>
  <?php $pageTitle='Portal'; include dirname(__DIR__,2).'/layout/head.php'; ?>
</head>
<body>
  <?php include dirname(__DIR__,2).'/layout/header.php'; ?>
  <main class="container">
    <div class="card">
      <h2 class="title">Portal</h2>
      <ul>
        <li><a id="driveLink" href="<?php echo htmlspecialchars($folder); ?>" target="_blank">Carpeta integral</a></li>
      </ul>
    </div>
  </main>
  <div id="driveInfoModal" class="modal" style="display:none">
    <div class="modal-content">
      <button type="button" class="modal-close" id="driveInfoX" aria-label="Cerrar">×</button>
      <div class="modal-title">Acceso a Carpeta Integral</div>
      <div class="modal-body">
        Para abrir tu carpeta en Google Drive, tu correo debe tener creada una Cuenta de Google.
        Tenemos dadas de alta estos correos para el acceso:
        <ul>
          <?php if(count($emailsList)===0){ ?><li>Sin correos registrados</li><?php } else { foreach($emailsList as $em){ ?><li><?php echo htmlspecialchars($em); ?></li><?php } } ?>
        </ul>
        Si necesitas ayuda para crear una Cuenta de Google con tu correo corporativo, puedes usar esta guía:
        <br>
        <a href="https://www.davirbonilla.com/como-crear-una-cuenta-de-google-desde-correo-corporativo/" target="_blank">Cómo crear una Cuenta de Google con tu correo corporativo</a>.
      </div>
      <div class="actions">
        <button type="button" class="btn" id="driveInfoContinue">Continuar</button>
        <button type="button" class="btn secondary" id="driveInfoHide">No volver a mostrar</button>
      </div>
    </div>
  </div>
  <script>
  (function(){
    var a=document.getElementById('driveLink');
    var m=document.getElementById('driveInfoModal');
    var btnGo=document.getElementById('driveInfoContinue');
    var btnHide=document.getElementById('driveInfoHide');
    var btnX=document.getElementById('driveInfoX');
    var targetHref=a?a.getAttribute('href'):null;
    if(a){
      a.addEventListener('click',function(e){
        var skip=localStorage.getItem('hideDriveInfo')==='1';
        if(skip) return; // dejar pasar
        e.preventDefault();
        if(m){ m.style.display='flex'; }
      });
    }
    if(btnGo){ btnGo.addEventListener('click',function(){ if(m) m.style.display='none'; if(targetHref){ window.open(targetHref,'_blank'); } }); }
    if(btnHide){ btnHide.addEventListener('click',function(){ localStorage.setItem('hideDriveInfo','1'); if(m) m.style.display='none'; if(targetHref){ window.open(targetHref,'_blank'); } }); }
    if(btnX){ btnX.addEventListener('click',function(){ if(m) m.style.display='none'; }); }
    document.addEventListener('keydown',function(e){ if(e.key==='Escape'&&m&&m.style.display!=='none'){ m.style.display='none'; } });
    m&&m.addEventListener('click',function(e){ var c=e.target.closest('.modal-content'); if(!c){ m.style.display='none'; } });
  })();
  </script>
  <?php include dirname(__DIR__,2).'/layout/footer.php'; ?>
</body>
</html>
