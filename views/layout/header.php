<?php $path=parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH); $isPortal=($path==='/portal'||$path==='/carpetas'); $isAdmin=!empty($_SESSION['is_admin']); if(!isset($client)){ $client=isset($_SESSION['client_name'])?$_SESSION['client_name']:(isset($_SESSION['user'])?$_SESSION['user']:''); } ?>
<header>
  <div class="topbar">
    <img class="logo logo-light" src="https://mediosconvalor.github.io/mcv/img/logo/logo.png" width="140" height="72" alt="">
    <img class="logo logo-dark" src="/assets/img/logo_blanco.png" width="140" height="72" alt="">
    <button class="menu-toggle" id="menuToggle" aria-label="Abrir menú"><span></span><span></span><span></span></button>
    <nav>
      <?php if(!$isAdmin){ ?>
        <a href="/portal" class="<?php echo $isPortal?'active':''; ?>">Portal</a>
        <a href="/finanzas" class="<?php echo $path==='/finanzas'?'active':''; ?>">Finanzas</a>
        <a href="/servicios" class="<?php echo $path==='/servicios'?'active':''; ?>">Servicios</a>
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
    <div class="client" style="margin-left:auto;font-weight:600"><?php echo htmlspecialchars($client); ?></div>
  </div>
</header>
