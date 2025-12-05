<?php $path=parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH); $isPortal=($path==='/portal'||$path==='/carpetas'); $isAdmin=!empty($_SESSION['is_admin']); if(!isset($client)){ $client=isset($_SESSION['client_name'])?$_SESSION['client_name']:(isset($_SESSION['user'])?$_SESSION['user']:''); } $brandName=isset($_SESSION['brand_name'])?$_SESSION['brand_name']:null; if(!$brandName && !$isAdmin && $client){ require_once dirname(__DIR__,2).'/lib/gas.php'; $finList=gas_finanzas_list($client); if($finList['ok'] && is_array($finList['resultados']) && count($finList['resultados'])>0){ $bn=trim((string)($finList['resultados'][0]['razonSocial']??'')); if($bn!==''){ $_SESSION['brand_name']=$bn; $brandName=$bn; } } } $displayName=$brandName?:$client; $u=strtolower($_SESSION['user']??''); $isQUser=(preg_match('/^q/',$u)===1); ?>
<header>
  <div class="topbar">
    <img class="logo logo-light" src="https://mediosconvalor.github.io/mcv/img/logo/logo.png" width="140" height="72" alt="">
    <img class="logo logo-dark" src="/assets/img/logo_blanco.png" width="140" height="72" alt="">
    <button class="menu-toggle" id="menuToggle" aria-label="Abrir menú" aria-expanded="false">
      <img class="icon-hamburger-light" src="/assets/icons/menu-hamburger.svg" alt="" aria-hidden="true">
      <img class="icon-hamburger-dark" src="/assets/icons/menu-hamburger-white.svg" alt="" aria-hidden="true">
      <img class="icon-close" src="/assets/icons/x.svg" alt="" aria-hidden="true">
    </button>
    <nav>
      <button class="nav-close" id="navClose" aria-label="Cerrar menú"><img src="/assets/icons/x.svg" alt="" aria-hidden="true"></button>
      <div class="nav-user"><?php echo htmlspecialchars($displayName); ?></div>
      <?php if(!$isAdmin){ ?>
        <a href="/portal" class="<?php echo $isPortal?'active':''; ?>">Portal</a>
        <?php if($isQUser){ ?><a href="/finanzas" class="<?php echo $path==='/finanzas'?'active':''; ?>">Finanzas</a><?php } ?>
        <?php if($isQUser){ ?><a href="/servicios" class="<?php echo $path==='/servicios'?'active':''; ?>">Servicios</a><?php } ?>
        <a href="/configuracion" class="<?php echo ($path==='/configuracion'||$path==='/reportes')?'active':''; ?>">Configuración</a>
      <?php } else { ?>
        <?php if(!empty($_SESSION['is_super_admin'])){ ?>
          <a href="/admin" class="<?php echo ($path==='/admin'||$path==='/admin/clientes')?'active':''; ?>">Clientes</a>
          <a href="/admin/invitaciones" class="<?php echo ($path==='/admin/invitaciones')?'active':''; ?>">Invitaciones</a>
          <a href="/admin/super" class="<?php echo ($path==='/admin/super')?'active':''; ?>">Super</a>
          <a href="/admin/configuracion" class="<?php echo ($path==='/admin/configuracion')?'active':''; ?>">Configuración</a>
        <?php } else { ?>
          <a href="/admin" class="<?php echo ($path==='/admin'||$path==='/admin/clientes')?'active':''; ?>">Clientes</a>
          <a href="/admin/invitaciones" class="<?php echo ($path==='/admin/invitaciones')?'active':''; ?>">Invitaciones</a>
          <a href="/admin/configuracion" class="<?php echo ($path==='/admin/configuracion')?'active':''; ?>">Configuración</a>
        <?php } ?>
      <?php } ?>
      <a href="/logout">Salir</a>
    </nav>
    <div class="client" style="margin-left:auto;font-weight:600"><?php echo htmlspecialchars($displayName); ?></div>
  </div>
</header>
<?php include __DIR__.'/loader.php'; ?>
<?php
  $u=strtolower($_SESSION['user']??'');
  $isQUser=(preg_match('/^q/',$u)===1);
  $isAdmin=!empty($_SESSION['is_admin']);
  $showWelcome = ($path==='/portal' || $path==='/admin' || $path==='/admin/clientes');
?>
<?php if($showWelcome){ ?>
<div id="welcomeModal" class="modal" style="display:none">
  <div class="modal-content">
    <div class="modal-title"><?php echo $isAdmin? 'Bienvenido Admin' : 'Bienvenido al Portal'; ?></div>
    <div class="modal-body">
      <?php if($isAdmin){ ?>
        <p>Este panel permite gestionar clientes e invitaciones, y configurar el sistema.</p>
        <ul>
          <li>Clientes: altas, ediciones, búsqueda y administración.</li>
          <li>Invitaciones: crear y administrar accesos del equipo.</li>
          <li>Configuración: ajustar datos y credenciales del panel.</li>
        </ul>
        <p>Los cambios impactan la experiencia de tus clientes.</p>
      <?php } else if($isQUser){ ?>
        <p>Tu portal de cliente centraliza información y reportes.</p>
        <ul>
          <li>Carpeta integral en Drive con documentación y reportes.</li>
          <li>Servicios realizados y métricas del periodo.</li>
          <li>Finanzas y facturación.</li>
          <li>Reportes: el del mes anterior está disponible desde el día 4.</li>
        </ul>
      <?php } else { ?>
        <p>Tu portal de cliente está listo para iniciar.</p>
        <ul>
          <li>Carpeta integral en Drive con documentación y reportes.</li>
          <li>Configuración de tu cuenta y contraseña.</li>
          <li>“Finanzas” y “Servicios” se activarán pronto.</li>
          <li>Asegúrate de tener una Cuenta de Google asociada a tu correo para abrir Drive.</li>
        </ul>
      <?php } ?>
    </div>
    <div class="actions"><button type="button" class="btn" id="welcomeContinue">Continuar</button><button type="button" class="btn secondary" id="welcomeHide">No volver a mostrar</button></div>
  </div>
</div>
<?php } ?>
<div id="swUpdateModal" class="modal" style="display:none">
  <div class="modal-content">
    <div class="modal-title">Actualización disponible</div>
    <div class="modal-body">Hay una nueva versión del portal. Por favor, refresca para actualizar.</div>
    <div class="actions"><button type="button" class="btn" id="swRefreshNow">Refrescar ahora</button><button type="button" class="btn secondary" id="swUpdateClose">Cerrar</button></div>
  </div>
</div>
<script>
(function(){
  var overlay=document.getElementById('globalLoader');
  var textEl=document.getElementById('globalLoaderText');
  window.showLoader=function(text){ if(textEl && typeof text==='string'){ textEl.textContent=text; } if(overlay){ overlay.style.display='flex'; } };
  window.hideLoader=function(){ if(overlay){ overlay.style.display='none'; } };
  try{ window.showLoader('Cargando…'); }catch(_){}
  window.addEventListener('load',function(){ try{ window.hideLoader(); }catch(_){} });
  var nav=document.querySelector('header nav');
  if(nav){
    nav.addEventListener('click',function(e){
      var a=e.target.closest('a');
      if(a && a.target!=='_blank' && a.href && a.origin===location.origin){
        try{ window.showLoader('Cargando…'); }catch(_){}
        try{ if(window.__swHasUpdate && window.__swWaitingWorker){ window.__swWaitingWorker.postMessage('SKIP_WAITING'); } }catch(_){ }
      }
    });
  }
  var w=document.getElementById('welcomeModal');
  var btnC=document.getElementById('welcomeContinue');
  var btnH=document.getElementById('welcomeHide');
  function openWelcome(){ if(w){ w.style.display='flex'; } }
  function closeWelcome(){ if(w){ w.style.display='none'; } }
  var SHOW_WELCOME = <?php echo $showWelcome?'true':'false'; ?>;
  try{
    if(SHOW_WELCOME && localStorage.getItem('hideWelcome')!=='1'){ setTimeout(openWelcome, 120); }
  }catch(_){ }
  if(btnC){ btnC.addEventListener('click',function(){ closeWelcome(); }); }
  if(btnH){ btnH.addEventListener('click',function(){ try{ localStorage.setItem('hideWelcome','1'); }catch(_){} closeWelcome(); }); }

  if('serviceWorker' in navigator){
    var updateModal=document.getElementById('swUpdateModal');
    var btnRefresh=document.getElementById('swRefreshNow');
    var btnClose=document.getElementById('swUpdateClose');
    var waitingWorker=null;
    function openUpdate(){ if(updateModal){ updateModal.style.display='flex'; } }
    function closeUpdate(){ if(updateModal){ updateModal.style.display='none'; } }
    navigator.serviceWorker.register('/sw.js',{scope:'/'}).then(function(reg){
      function promptUpdate(worker){ waitingWorker=worker; openUpdate(); }
      if(reg.waiting){ promptUpdate(reg.waiting); }
      reg.addEventListener('updatefound',function(){ var nw=reg.installing; if(!nw) return; nw.addEventListener('statechange',function(){ if(nw.state==='installed' && navigator.serviceWorker.controller){ promptUpdate(nw); } }); });
    }).catch(function(){});
    window.__swHasUpdate=false; window.__swWaitingWorker=null;
    function setUpdate(worker){ window.__swHasUpdate=true; window.__swWaitingWorker=worker; }
    if(waitingWorker){ setUpdate(waitingWorker); }
    document.addEventListener('click',function(ev){ var a=ev.target && ev.target.closest ? ev.target.closest('a') : null; if(a && a.target!=='_blank' && a.href && a.origin===location.origin){ try{ if(window.__swHasUpdate && window.__swWaitingWorker){ window.__swWaitingWorker.postMessage('SKIP_WAITING'); } }catch(_){ } } }, true);
    var origPrompt=promptUpdate; promptUpdate=function(worker){ waitingWorker=worker; setUpdate(worker); origPrompt(worker); };
    navigator.serviceWorker.addEventListener('controllerchange',function(){ closeUpdate(); try{ window.location.reload(); }catch(_){} });
    if(btnRefresh){ btnRefresh.addEventListener('click',function(){ if(waitingWorker){ waitingWorker.postMessage('SKIP_WAITING'); } }); }
    if(btnClose){ btnClose.addEventListener('click',function(){ closeUpdate(); }); }
  }
})();
</script>
