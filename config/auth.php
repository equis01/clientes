<?php
ini_set('display_errors','0');
error_reporting(E_ERROR | E_PARSE);
session_start();
header('Content-Type: application/json');
require_once __DIR__.'/config.php';
require_once dirname(__DIR__).'/lib/env.php';
require_once dirname(__DIR__).'/lib/db.php';
function http_get_raw($url,$insecure=false){
  $status=null;$raw=null;$error=null;
  if(function_exists('curl_init')){
    $ch=curl_init($url);
    $opts=[CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>15,CURLOPT_HTTPHEADER=>['Accept: application/json'],CURLOPT_FOLLOWLOCATION=>true,CURLOPT_MAXREDIRS=>5];
    if($insecure){ $opts[CURLOPT_SSL_VERIFYPEER]=false; $opts[CURLOPT_SSL_VERIFYHOST]=false; }
    curl_setopt_array($ch,$opts);
    $raw=curl_exec($ch);
    $status=curl_getinfo($ch,CURLINFO_HTTP_CODE);
    $err=curl_error($ch);
    curl_close($ch);
    if($raw===false){$error='Conexión fallida: '.$err;}
  } else {
    $ctx=stream_context_create([
      'http'=>['method'=>'GET','timeout'=>15,'header'=>'Accept: application/json','ignore_errors'=>true],
      'ssl'=>['verify_peer'=>!$insecure,'verify_peer_name'=>!$insecure]
    ]);
    $headers=@get_headers($url,1);
    $redir=null; if(is_array($headers)&&isset($headers['Location'])){$redir=is_array($headers['Location'])?end($headers['Location']):$headers['Location'];}
    $target=$redir?:$url;
    $raw=@file_get_contents($target,false,$ctx);
    if(is_array($headers)&&isset($headers[0])){ if(preg_match('/\s(\d{3})\s/',$headers[0],$m)){ $status=intval($m[1]); } }
    if($raw===false){$error='Conexión fallida';}
  }
  return ['raw'=>$raw,'status'=>$status,'error'=>$error];
}
function http_post_raw($url,$body,$insecure=false){
  $status=null;$raw=null;$error=null;
  if(function_exists('curl_init')){
    $ch=curl_init($url);
    $opts=[CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>15,CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>$body,CURLOPT_FOLLOWLOCATION=>true,CURLOPT_MAXREDIRS=>5];
    if($insecure){ $opts[CURLOPT_SSL_VERIFYPEER]=false; $opts[CURLOPT_SSL_VERIFYHOST]=false; }
    curl_setopt_array($ch,$opts);
    $raw=curl_exec($ch);
    $status=curl_getinfo($ch,CURLINFO_HTTP_CODE);
    $err=curl_error($ch);
    curl_close($ch);
    if($raw===false){$error='Conexión fallida: '.$err;}
  } else {
    $ctx=stream_context_create([
      'http'=>['method'=>'POST','timeout'=>15,'header'=>'Content-Type: application/x-www-form-urlencoded','content'=>$body,'ignore_errors'=>true],
      'ssl'=>['verify_peer'=>!$insecure,'verify_peer_name'=>!$insecure]
    ]);
    $raw=@file_get_contents($url,false,$ctx);
    $resp=@array_values($http_response_header??[]);
    if(is_array($resp)&&isset($resp[0])){ if(preg_match('/\s(\d{3})\s/',$resp[0],$m)){ $status=intval($m[1]); } }
    if($raw===false){$error='Conexión fallida';}
  }
  return ['raw'=>$raw,'status'=>$status,'error'=>$error];
}
if(isset($_GET['ping'])){
  $resp = array(
    'ok'=>true,
    'php'=>PHP_VERSION,
    'pdo_sqlite'=>extension_loaded('pdo_sqlite'),
    'sqlite3'=>extension_loaded('sqlite3'),
    'curl'=>function_exists('curl_init'),
    'openssl'=>extension_loaded('openssl'),
    'allow_url_fopen'=>filter_var(ini_get('allow_url_fopen'), FILTER_VALIDATE_BOOLEAN),
    'session_path'=>ini_get('session.save_path'),
    'db_fallback'=>$GLOBALS['DB_FALLBACK']
  );
  echo json_encode($resp);
  exit;
}
if(!isset($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD']!=='POST'){echo '{"ok":false,"error":"Método inválido"}';exit;}

if(isset($_POST['google_id_token'])){
  $token=trim($_POST['google_id_token']);
  $info=verifyGoogleIdToken($token);
  if(!$info||empty($info['email'])){echo '{"ok":false,"error":"Google inválido"}';exit;}
  $email=strtolower($info['email']);
  $isAdmin=function_exists('str_ends_with') ? str_ends_with($email, ADMIN_DOMAIN) : (substr($email, -strlen(ADMIN_DOMAIN))===ADMIN_DOMAIN);
  if($isAdmin){
    $_SESSION['user']=$email; $_SESSION['client_name']='Admin'; $_SESSION['folder_url']=null; $_SESSION['is_admin']=true;
    echo '{"ok":true,"admin":true}'; exit;
  }
  $u=findUserByEmail($email);
  if(!$u||intval($u['valid'])!==1){echo '{"ok":false,"error":"Usuario inactivo o no encontrado"}';exit;}
  if(REQUIRE_Q_PREFIX && strtoupper(substr($u['username'],0,1))!=='Q'){echo '{"ok":false,"error":"Solo QRO"}';exit;}
  $_SESSION['user']=$u['username']; $_SESSION['client_name']=$u['alias']?:$u['username']; $_SESSION['folder_url']=$u['drive_url']; $_SESSION['is_admin']=false;
  echo '{"ok":true}'; exit;
}

if(isset($_POST['firebase_id_token'])){
  $token=trim($_POST['firebase_id_token']);
  $api=env('FIREBASE_API_KEY');
  if($token===''){echo '{"ok":false,"error":"Faltan datos"}';exit;}
  if(!$api){echo '{"ok":false,"error":"Firebase no configurado"}';exit;}
  $url='https://identitytoolkit.googleapis.com/v1/accounts:lookup?key='.urlencode($api);
  $payload=json_encode(['idToken'=>$token]);
  $status=null;$raw=null;$err=null;
  if(function_exists('curl_init')){
    $ch=curl_init($url);
    curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>15,CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>$payload,CURLOPT_HTTPHEADER=>['Content-Type: application/json'],CURLOPT_FOLLOWLOCATION=>true,CURLOPT_MAXREDIRS=>5]);
    $raw=curl_exec($ch); $status=curl_getinfo($ch,CURLINFO_HTTP_CODE); $err=curl_error($ch); curl_close($ch);
  } else {
    $ctx=stream_context_create(['http'=>['method'=>'POST','timeout'=>15,'header'=>'Content-Type: application/json','content'=>$payload,'ignore_errors'=>true]]);
    $raw=@file_get_contents($url,false,$ctx);
  }
  $d=$raw?json_decode($raw,true):null;
  $users=is_array($d)&&isset($d['users'])&&is_array($d['users'])?$d['users']:[];
  $email=''; if(count($users)>0){ $email=strtolower($users[0]['email']??''); }
  if(!$email){ echo '{"ok":false,"error":"Firebase inválido"}'; exit; }
  $isAdmin=function_exists('str_ends_with') ? str_ends_with($email, ADMIN_DOMAIN) : (substr($email, -strlen(ADMIN_DOMAIN))===ADMIN_DOMAIN);
  if(!$isAdmin){ echo '{"ok":false,"error":"No autorizado"}'; exit; }
  $_SESSION['user']=$email; $_SESSION['client_name']='Admin'; $_SESSION['folder_url']=null; $_SESSION['is_admin']=true;
  echo '{"ok":true,"admin":true}'; exit;
}

$username=isset($_POST['username'])?trim($_POST['username']):'';
$password=isset($_POST['password'])?trim($_POST['password']):'';
if($username===''||$password===''){echo '{"ok":false,"error":"Faltan datos"}';exit;}
$isQ=strtoupper(substr($username,0,1))==='Q';

if($isQ){
  $base=env('GAS_USERS_Q_URL');
  if($base){
    $url=$base.'?action=users&username='.urlencode($username);
    $r=http_get_raw($url,false);
    if($r['raw']===false||$r['raw']===null){ $r=http_get_raw($url,true); }
    if($r['raw']===false||$r['raw']===null){echo '{"ok":false,"error":"Conexión fallida"}';exit;}
    $data=json_decode($r['raw'],true);
    if(!is_array($data)||empty($data['ok'])){echo '{"ok":false,"error":"Respuesta inválida"}';exit;}
    $users=is_array($data['users'])?$data['users']:[];
    $u=isset($users[$username])?$users[$username]:null;
    if(!$u){echo '{"ok":false,"error":"No encontrado"}';exit;}
  } else {
    echo '{"ok":false,"error":"Endpoint usuarios Q no configurado"}';
    exit;
  }
  if(intval($u['valid'])!==1){echo '{"ok":false,"error":"Usuario inactivo"}';exit;}
  if(isset($u['portal_enabled']) && intval($u['portal_enabled'])!==1){echo '{"ok":false,"error":"Portal restringido","code":"portal_disabled"}';exit;}
  $hash=isset($u['password_hash'])?$u['password_hash']:'';
  $ok=false;
  if(is_string($hash)&&strlen($hash)>0){
    if(strpos($hash,'$2')===0){ $ok=password_verify($password,$hash); }
    else { $ok=(trim($password)===trim($hash)); }
  }
  if(!$ok){echo '{"ok":false,"error":"Credenciales inválidas"}';exit;}
  $email=isset($u['email'])?$u['email']:'';
  $isAdmin = $email && (function_exists('str_ends_with') ? str_ends_with(strtolower($email), ADMIN_DOMAIN) : (substr(strtolower($email), -strlen(ADMIN_DOMAIN))===ADMIN_DOMAIN));
  $_SESSION['user']=$username;
  $_SESSION['client_name']=(isset($u['alias'])&&$u['alias']!=='')?$u['alias']:$username;
  $_SESSION['folder_url']=isset($u['drive_url'])?$u['drive_url']:null;
  $_SESSION['is_admin']=$isAdmin;
  echo json_encode([
    'ok'=>true,
    'admin'=>$isAdmin?true:false,
    'is_q'=>true,
    'folder_url'=>isset($u['drive_url'])?$u['drive_url']:null
  ]);
  exit;
} else {
  $base=env('GAS_USERS_OTHER_URL');
  if($base){
    $body=http_build_query(['username'=>$username,'password'=>$password]);
    $r=http_post_raw($base,$body,false);
    if($r['raw']===false||$r['raw']===null){ $r=http_post_raw($base,$body,true); }
    if($r['raw']===false||$r['raw']===null){echo '{"ok":false,"error":"Conexión fallida"}';exit;}
    $text=trim((string)$r['raw']);
    $data=json_decode($text,true);
    if(is_array($data)){
      $url=isset($data['url'])?$data['url']:'';
      $ok=!empty($data['ok']);
      if(!$ok || $url===''){ echo '{"ok":false,"error":"Credenciales inválidas"}'; exit; }
      echo json_encode(['ok'=>true,'admin'=>false,'is_q'=>false,'folder_url'=>$url]);
      exit;
    } else {
      if(strtolower($text)==='null'){echo '{"ok":false,"error":"Portal restringido","code":"portal_disabled"}';exit;}
      if($text===''){echo '{"ok":false,"error":"Credenciales inválidas"}';exit;}
      echo json_encode(['ok'=>true,'admin'=>false,'is_q'=>false,'folder_url'=>$text]);
      exit;
    }
  } else {
    echo '{"ok":false,"error":"Endpoint usuarios no-Q no configurado"}';
    exit;
  }
}

function verifyGoogleIdToken($token){
  $url='https://oauth2.googleapis.com/tokeninfo?id_token='.urlencode($token);
  $r=http_get_raw($url,false);
  if($r['raw']===false||$r['raw']===null){ $r=http_get_raw($url,true); }
  $res=$r['raw'];
  if(!$res) return null; $d=json_decode($res,true); return is_array($d)?$d:null;
}
