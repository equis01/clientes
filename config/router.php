<?php
session_start();
$cookieToken=isset($_COOKIE['mcv_token'])?$_COOKIE['mcv_token']:null;
if(!isset($_SESSION['user']) && $cookieToken){
  require_once dirname(__DIR__).'/lib/db.php';
  require_once dirname(__DIR__).'/lib/env.php';
  require_once dirname(__DIR__).'/lib/gas.php';
  $row=findSessionToken($cookieToken);
  if(is_array($row)){
    $rev=$row['revoked_at']??''; $exp=$row['expires_at']??''; $okExp=$exp!=='' && (strtotime($exp)!==false) && time()<strtotime($exp);
    if(!$rev && $okExp){
      touchSessionToken($cookieToken);
      if(($row['user_type']??'')==='admin'){
        $_SESSION['user']=$row['user_id']; $_SESSION['client_name']='Admin'; $_SESSION['folder_url']=null; $_SESSION['is_admin']=true; $_SESSION['is_super_admin']=in_array(strtolower($row['user_id']),['rsaucedo@mediosconvalor.com','aguzman@mediosconvalor.com','sistemas@mediosconvalor.com'],true);
      } else {
        $uname=$row['user_id']; $_SESSION['user']=$uname; $_SESSION['is_admin']=false; $_SESSION['client_name']=$uname; $_SESSION['folder_url']=null;
        $u=gas_users($uname);
        if($u['ok'] && is_array($u['user'])){ $alias=trim((string)($u['user']['alias']??'')); $drv=trim((string)($u['user']['drive_url']??'')); if($alias!==''){ $_SESSION['client_name']=$alias; } if($drv!==''){ $_SESSION['folder_url']=$drv; } }
      }
    }
  }
}
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
    if(isset($_SESSION['user'])){ header('Location: /users'); exit; }
    else { require dirname(__DIR__).'/views/pages/login.php'; }
    break;
  case '/users':
    require dirname(__DIR__).'/views/pages/portal.php';
    break;
  case '/login':
    require dirname(__DIR__).'/views/pages/login.php';
    break;
  case '/logout':
    require dirname(__DIR__).'/views/pages/logout.php';
    break;
  case '/servicios':
    header('Location: /users/servicios'); exit;
    break;
  case '/users/servicios':
    require dirname(__DIR__).'/views/pages/servicios.php';
    break;
  case '/finanzas':
    header('Location: /users/finanzas'); exit;
    break;
  case '/users/finanzas':
    require dirname(__DIR__).'/views/pages/finanzas.php';
    break;
  case '/reportes':
    header('Location: /users/reportes'); exit;
    break;
  case '/users/reportes':
    require dirname(__DIR__).'/views/pages/reportes.php';
    break;
  case '/configuracion':
    require dirname(__DIR__).'/views/pages/configuracion.php';
    break;
  case '/portal':
    header('Location: /users/portal'); exit;
    break;
  case '/users/portal':
    require dirname(__DIR__).'/views/pages/portal.php';
    break;
  case '/carpetas':
    header('Location: /users/portal'); exit;
    break;
  case '/auth':
    require dirname(__DIR__).'/config/auth.php';
    break;
  case '/theme':
    require dirname(__DIR__).'/config/theme.php';
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
