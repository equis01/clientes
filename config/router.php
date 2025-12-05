<?php
session_start();
$uri=parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$file=dirname(__DIR__).$uri;
if($uri!==null && $uri!=='/' && is_file($file)){
  return false;
}
// Si es admin, restringir acceso solo a rutas /admin y acciones necesarias
if(isset($_SESSION['is_admin']) && $_SESSION['is_admin']){
  $allowed=[
    '/admin',
    '/admin/login',
    '/admin/invitaciones',
    '/admin/invite',
    '/admin/clientes/crear',
    '/admin/clientes/editar',
    '/admin/super',
    '/admin/configuracion',
    '/logout',
    '/auth'
  ];
  $isAllowed=false;
  if(strpos($uri,'/admin')===0){ $isAllowed=true; }
  else { $isAllowed=in_array($uri,$allowed,true); }
  if(!$isAllowed){ header('Location: /admin'); exit; }
}
switch(rtrim($uri,'/')){
  case '':
    if(isset($_SESSION['user'])){ require dirname(__DIR__).'/views/pages/portal.php'; }
    else { require dirname(__DIR__).'/views/pages/login.php'; }
    break;
  case '/login':
    require dirname(__DIR__).'/views/pages/login.php';
    break;
  case '/logout':
    require dirname(__DIR__).'/views/pages/logout.php';
    break;
  case '/servicios':
    require dirname(__DIR__).'/views/pages/servicios.php';
    break;
  case '/finanzas':
    require dirname(__DIR__).'/views/pages/finanzas.php';
    break;
  case '/reportes':
    require dirname(__DIR__).'/views/pages/configuracion.php';
    break;
  case '/configuracion':
    require dirname(__DIR__).'/views/pages/configuracion.php';
    break;
  case '/portal':
    require dirname(__DIR__).'/views/pages/portal.php';
    break;
  case '/carpetas':
    require dirname(__DIR__).'/views/pages/portal.php';
    break;
  case '/auth':
    require dirname(__DIR__).'/config/auth.php';
    break;
  case '/admin':
    require dirname(__DIR__).'/views/pages/admin/clientes.php';
    break;
  case '/admin/login':
    require dirname(__DIR__).'/views/pages/admin/login.php';
    break;
  case '/admin/invitaciones':
    require dirname(__DIR__).'/views/pages/admin/invitaciones.php';
    break;
  case '/admin/invite':
    require dirname(__DIR__).'/views/pages/admin/invite.php';
    break;
  case '/admin/clientes/crear':
    require dirname(__DIR__).'/views/pages/admin/clientes_crear.php';
    break;
  case '/admin/clientes/editar':
    require dirname(__DIR__).'/views/pages/admin/clientes_editar.php';
    break;
  case '/admin/configuracion':
    require dirname(__DIR__).'/views/pages/admin/configuracion.php';
    break;
  case '/admin/super':
    require dirname(__DIR__).'/views/pages/admin/super.php';
    break;
  case '/errores':
    require dirname(__DIR__).'/views/pages/errores.php';
    break;
  default:
    http_response_code(404);
    $_GET['code']=404; $_GET['path']=$uri;
    require dirname(__DIR__).'/views/pages/errores.php';
}
