<?php
session_start();
$uri=parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$file=dirname(__DIR__).$uri;
if($uri!==null && $uri!=='/' && is_file($file)){
  return false;
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
  case '/admin/clientes/crear':
    require dirname(__DIR__).'/views/pages/admin/clientes_crear.php';
    break;
  case '/admin/clientes/editar':
    require dirname(__DIR__).'/views/pages/admin/clientes_editar.php';
    break;
  default:
    http_response_code(404);
    echo '404';
}

