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
    if(isset($_SESSION['user'])){ if(!empty($_SESSION['is_admin'])){ header('Location: /admin'); } else { header('Location: /users'); } exit; }
    else { header('Location: /login'); exit; }
    break;
  case '/users':
    $b=dirname(__DIR__);
    $pIndex=$b.'/views/pages/users/index.php';
    $pPortal=$b.'/views/pages/users/portal.php';
    if(is_file($pIndex)){ require $pIndex; }
    else if(is_file($pPortal)){ require $pPortal; }
    else { require $b.'/views/pages/portal.php'; }
    break;
  case '/login':
    $b=dirname(__DIR__); $p=$b.'/views/pages/shared/login.php'; if(is_file($p)){ require $p; } else { $p2=$b.'/views/pages/users/login.php'; if(is_file($p2)){ require $p2; } else { require $b.'/views/pages/login.php'; } }
    break;
  case '/users/login':
    header('Location: /login'); exit;
    break;
  case '/logout':
    $b=dirname(__DIR__); $p=$b.'/views/pages/shared/logout.php'; if(is_file($p)){ require $p; } else { require $b.'/views/pages/logout.php'; }
    break;
  case '/servicios':
    header('Location: /users/servicios'); exit;
    break;
  case '/users/servicios':
    $b=dirname(__DIR__); $p=$b.'/views/pages/users/servicios.php'; if(is_file($p)){ require $p; } else { require $b.'/views/pages/servicios.php'; }
    break;
  case '/finanzas':
    header('Location: /users/finanzas'); exit;
    break;
  case '/users/finanzas':
    $b=dirname(__DIR__); $p=$b.'/views/pages/users/finanzas.php'; if(is_file($p)){ require $p; } else { require $b.'/views/pages/finanzas.php'; }
    break;
  case '/reportes':
    header('Location: /users/reportes'); exit;
    break;
  case '/users/reportes':
    $b=dirname(__DIR__); $p=$b.'/views/pages/users/reportes.php'; if(is_file($p)){ require $p; } else { require $b.'/views/pages/reportes.php'; }
    break;
  case '/configuracion':
    header('Location: /users/configuracion'); exit;
    break;
  case '/portal':
    header('Location: /users'); exit;
    break;
  case '/users/portal':
    header('Location: /users'); exit;
    break;
  case '/carpetas':
    header('Location: /users'); exit;
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
    header('Location: /login'); exit;
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
    $b=dirname(__DIR__); $p=$b.'/views/pages/shared/errores.php'; if(is_file($p)){ require $p; } else { require $b.'/views/pages/errores.php'; }
    break;
  default:
    http_response_code(404);
    $_GET['code']=404; $_GET['path']=$uri;
    $b=dirname(__DIR__); $p=$b.'/views/pages/shared/errores.php'; if(is_file($p)){ require $p; } else { require $b.'/views/pages/errores.php'; }
}
