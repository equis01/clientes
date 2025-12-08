<?php
if(session_status()!==PHP_SESSION_ACTIVE){ session_start(); }
require_once __DIR__.'/../../lib/db.php';
$tok=isset($_COOKIE['mcv_token'])?$_COOKIE['mcv_token']:null; if($tok){ revokeSessionToken($tok); setcookie('mcv_token','',time()-42000,'/'); }
$_SESSION=[];
if(ini_get('session.use_cookies')){
  $params=session_get_cookie_params();
  setcookie(session_name(),'',time()-42000,$params['path'],$params['domain'],$params['secure'],$params['httponly']);
}
session_destroy();
header('Location: /login');
exit;
