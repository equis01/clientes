<?php
ini_set('display_errors','0');
error_reporting(E_ERROR | E_PARSE);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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
    if ($raw === false) { $error = 'Conexión fallida: ' . $err; }
  } else {
    $ctx=stream_context_create([
      'http'=>['method'=>'GET','timeout'=>15,'header'=>'Accept: application/json','ignore_errors'=>true],
      'ssl'=>['verify_peer'=>!$insecure,'verify_peer_name'=>!$insecure]
    ]);
    if(!filter_var(ini_get('allow_url_fopen'), FILTER_VALIDATE_BOOLEAN)){
      return ['raw'=>false,'status'=>0,'error'=>'allow_url_fopen is disabled'];
    }
    $headers=@get_headers($url,1);
    $redir=null; if(is_array($headers)&&isset($headers['Location'])){$redir=is_array($headers['Location'])?end($headers['Location']):$headers['Location'];}
    $target=$redir?:$url;
    $raw=@file_get_contents($target,false,$ctx);
    if(is_array($headers)&&isset($headers[0])){ if(preg_match('/\s(\d{3})\s/',$headers[0],$m)){ $status=intval($m[1]); } }
    if($raw===false){$error='Conexión fallida (file_get_contents)';}
  }
  
  // PowerShell fallback for GET
  if (($raw === false || $raw === null) && strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
      $cmd = 'powershell -Command "try { $r = Invoke-WebRequest -Uri \''.$url.'\' -UseBasicParsing; $r.Content } catch { exit 1 }"';
      $output = [];
      $ret = 0;
      exec($cmd, $output, $ret);
      if ($ret === 0) {
          $raw = implode("\n", $output);
          $status = 200;
          $error = null;
      }
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
    if(!filter_var(ini_get('allow_url_fopen'), FILTER_VALIDATE_BOOLEAN)){
      return ['raw'=>false,'status'=>0,'error'=>'allow_url_fopen is disabled'];
    }
    $raw=@file_get_contents($url,false,$ctx);
    $resp=@array_values($http_response_header??[]);
    if(is_array($resp)&&isset($resp[0])){ if(preg_match('/\s(\d{3})\s/',$resp[0],$m)){ $status=intval($m[1]); } }
    if($raw===false){$error='Conexión fallida (file_get_contents)';}
  }
  
  // PowerShell fallback for POST
  if (($raw === false || $raw === null) && strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
      $cmd = 'powershell -Command "try { $r = Invoke-WebRequest -Uri \''.$url.'\' -Method Post -Body \''.$body.'\' -UseBasicParsing; $r.Content } catch { exit 1 }"';
      $output = [];
      $ret = 0;
      exec($cmd, $output, $ret);
      if ($ret === 0) {
          $raw = implode("\n", $output);
          $status = 200;
          $error = null;
      }
  }

  return ['raw'=>$raw,'status'=>$status,'error'=>$error];
}

if(isset($_GET['ping'])){
  $resp = array(
    'ok'=>true,
    'php'=>PHP_VERSION,
    'pdo_mysql'=>extension_loaded('pdo_mysql'),
    'pdo_sqlite'=>extension_loaded('pdo_sqlite'),
    'sqlite3'=>extension_loaded('sqlite3'),
    'curl'=>function_exists('curl_init'),
    'openssl'=>extension_loaded('openssl'),
    'allow_url_fopen'=>filter_var(ini_get('allow_url_fopen'), FILTER_VALIDATE_BOOLEAN),
    'session_path'=>ini_get('session.save_path'),
    'db_fallback'=>$GLOBALS['DB_FALLBACK']
  );
  header('Content-Type: application/json');
  echo json_encode($resp);
  exit;
}

if(!isset($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD']!=='POST'){ 
    header('Content-Type: application/json');
    echo '{"ok":false,"error":"Método inválido"}'; 
    exit;
}

if(isset($_POST['google_id_token'])){
  $token=trim($_POST['google_id_token']);
  $info=verifyGoogleIdToken($token);
  if(!$info||empty($info['email'])){ 
      header('Content-Type: application/json');
      echo '{"ok":false,"error":"Google inválido"}'; 
      exit;
  }
  $email=strtolower($info['email']);
  $isAdmin=function_exists('str_ends_with') ? str_ends_with($email, ADMIN_DOMAIN) : (substr($email, -strlen(ADMIN_DOMAIN))===ADMIN_DOMAIN);
  if($isAdmin){
    $_SESSION['user']=$email; $_SESSION['client_name']='Admin'; $_SESSION['folder_url']=null; $_SESSION['is_admin']=true; $_SESSION['is_super_admin']=in_array($email,['rsaucedo@mediosconvalor.com','aguzman@mediosconvalor.com','sistemas@mediosconvalor.com'],true);
    $t=createSessionToken('admin',$email,30,'light'); if($t){ setcookie('mcv_token',$t,['expires'=>time()+2592000,'path'=>'/','secure'=>isset($_SERVER['HTTPS']),'httponly'=>true,'samesite'=>'Lax']); }
    header('Content-Type: application/json');
    echo '{"ok":true,"admin":true}'; 
    exit;
  } else {
    header('Content-Type: application/json');
    echo '{"ok":false,"error":"No autorizado"}'; 
    exit;
  }
}

if(isset($_POST['firebase_id_token'])){
  $token=trim($_POST['firebase_id_token']);
  $api=env('FIREBASE_API_KEY');
  if($token===''){ 
      header('Content-Type: application/json');
      echo '{"ok":false,"error":"Faltan datos"}'; 
      exit;
  }
  if(!$api){ 
      header('Content-Type: application/json');
      echo '{"ok":false,"error":"Firebase no configurado"}'; 
      exit;
  }
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
  if(!$email){ 
      header('Content-Type: application/json');
      echo '{"ok":false,"error":"Firebase inválido"}'; 
      exit;
  }
  $isAdmin=function_exists('str_ends_with') ? str_ends_with($email, ADMIN_DOMAIN) : (substr($email, -strlen(ADMIN_DOMAIN))===ADMIN_DOMAIN);
  if(!$isAdmin){ 
      header('Content-Type: application/json');
      echo '{"ok":false,"error":"No autorizado"}'; 
      exit;
  }
  $_SESSION['user']=$email; $_SESSION['client_name']='Admin'; $_SESSION['folder_url']=null; $_SESSION['is_admin']=true; $_SESSION['is_super_admin']=in_array($email,['rsaucedo@mediosconvalor.com','aguzman@mediosconvalor.com','sistemas@mediosconvalor.com'],true);
  $t=createSessionToken('admin',$email,30,'light'); if($t){ setcookie('mcv_token',$t,['expires'=>time()+2592000,'path'=>'/','secure'=>isset($_SERVER['HTTPS']),'httponly'=>true,'samesite'=>'Lax']); }
  header('Content-Type: application/json');
  echo '{"ok":true,"admin":true}'; 
  exit;
}

// Support for old-style dedicated admin login
if(isset($_POST['admin_email']) && isset($_POST['admin_password'])){
  $email=strtolower(trim($_POST['admin_email']));
  $pass=trim($_POST['admin_password']);
  if($email===''||$pass===''){ 
      header('Content-Type: application/json');
      echo '{"ok":false,"error":"Faltan datos"}'; 
      exit;
  }
  $isAdminDomain=function_exists('str_ends_with') ? str_ends_with($email, ADMIN_DOMAIN) : (substr($email, -strlen(ADMIN_DOMAIN))===ADMIN_DOMAIN);
  if(!$isAdminDomain){ 
      header('Content-Type: application/json');
      echo '{"ok":false,"error":"Dominio no permitido"}'; 
      exit;
  }
  ensureSchema();
  if(!hasAnyAdmins()){ migrateAdminsFromJson(); }
  $ok=false; $name='Admin';
  $a=findAdminByEmail($email);
  if(is_array($a)){
    $hash=$a['password_hash']??''; $plain=''; $name=$a['name']??$name;
    if($hash){ $ok=password_verify($pass,$hash) || trim($pass)===trim($hash); }
  }
  if(!$ok){ 
      header('Content-Type: application/json');
      echo '{"ok":false,"error":"Credenciales inválidas"}'; 
      exit;
  }
  $_SESSION['user']=$email; $_SESSION['client_name']=$name; $_SESSION['folder_url']=null; $_SESSION['is_admin']=true; $_SESSION['is_super_admin']=in_array($email,['rsaucedo@mediosconvalor.com','aguzman@mediosconvalor.com','sistemas@mediosconvalor.com'],true);
  $t=createSessionToken('admin',$email,30,'light'); if($t){ setcookie('mcv_token',$t,['expires'=>time()+2592000,'path'=>'/','secure'=>isset($_SERVER['HTTPS']),'httponly'=>true,'samesite'=>'Lax']); }
  header('Content-Type: application/json');
  echo '{"ok":true,"admin":true}'; 
  exit;
}

$username=isset($_POST['username'])?trim($_POST['username']):'';
$password=isset($_POST['password'])?trim($_POST['password']):'';
if($username===''||$password===''){ 
    header('Content-Type: application/json');
    echo '{"ok":false,"error":"Faltan datos"}'; 
    exit;
}

// 1. Intentar login como ADMIN primero si parece un correo
$isEmail = strpos($username, '@') !== false;
if ($isEmail) {
    $email = strtolower($username);
    $isAdminDomain = function_exists('str_ends_with') ? str_ends_with($email, ADMIN_DOMAIN) : (substr($email, -strlen(ADMIN_DOMAIN)) === ADMIN_DOMAIN);
    
    if ($isAdminDomain) {
        ensureSchema();
        if(!hasAnyAdmins()){ migrateAdminsFromJson(); }
        $ok=false; $name='Admin';
        $a=findAdminByEmail($email);
        if(is_array($a)){
            $hash=$a['password_hash']??''; $name=$a['name']??$name;
            if($hash){ $ok=password_verify($password,$hash) || trim($password)===trim($hash); }
        }
        
        if ($ok) {
            $_SESSION['user']=$email; 
            $_SESSION['client_name']=$name; 
            $_SESSION['folder_url']=null; 
            $_SESSION['is_admin']=true; 
            $_SESSION['is_super_admin']=in_array($email,['rsaucedo@mediosconvalor.com','aguzman@mediosconvalor.com','sistemas@mediosconvalor.com'],true);
            $t=createSessionToken('admin',$email,30,'light'); 
            if($t){ setcookie('mcv_token',$t,['expires'=>time()+2592000,'path'=>'/','secure'=>isset($_SERVER['HTTPS']),'httponly'=>true,'samesite'=>'Lax']); }
            header('Content-Type: application/json');
            echo '{"ok":true,"admin":true}'; 
            exit;
        }
    }
}

// 2. Intentar login como USUARIO (Q o Otros)
$isQ=strtoupper(substr($username,0,1))==='Q';

if($isQ){
  $base=env('GAS_USERS_Q_URL');
  if($base){
    $url=$base.'?action=users&username='.urlencode($username);
    $r=http_get_raw($url,false);
    if($r['raw']===false||$r['raw']===null){ $r=http_get_raw($url,true); }
    if($r['raw']===false||$r['raw']===null){
      $err=$r['error']??'Conexión fallida';
      file_put_contents(__DIR__.'/debug_auth.log', date('Y-m-d H:i:s')." Error Q: $err\n", FILE_APPEND);
      header('Content-Type: application/json');
      echo '{"ok":false,"error":"'.$err.'"}';
      exit;
    }
    $data=json_decode($r['raw'],true);
    if(!is_array($data)||empty($data['ok'])){
      file_put_contents(__DIR__.'/debug_auth.log', date('Y-m-d H:i:s')." Error decoding JSON: ".json_last_error_msg()."\nRaw: ".substr($r['raw'],0,200)."\nError cURL: ".($r['error']??'none')."\n", FILE_APPEND);
      header('Content-Type: application/json');
      echo '{"ok":false,"error":"Respuesta inválida"}';
      exit;
    }
    $users=is_array($data['users'])?$data['users']:[];
    $u=isset($users[$username])?$users[$username]:null;
    if(!$u){ 
        header('Content-Type: application/json');
        echo '{"ok":false,"error":"No encontrado"}'; 
        exit;
    }
  } else {
    header('Content-Type: application/json');
    echo '{"ok":false,"error":"Endpoint usuarios Q no configurado"}';
    exit;
  }
  
  if(intval($u['valid'])!==1){ 
      header('Content-Type: application/json');
      echo '{"ok":false,"error":"Usuario inactivo"}'; 
      exit;
  }
  if(isset($u['portal_enabled']) && intval($u['portal_enabled'])!==1){ 
      header('Content-Type: application/json');
      echo '{"ok":false,"error":"Portal restringido","code":"portal_disabled"}'; 
      exit;
  }
  
  $hash=isset($u['password_hash'])?$u['password_hash']:'';
  $ok=false;
  if(is_string($hash)&&strlen($hash)>0){
    if(strpos($hash,'$2')===0){ $ok=password_verify($password,$hash); }
    else { $ok=(trim($password)===trim($hash)); }
  }
  
  if(!$ok){ 
      header('Content-Type: application/json');
      echo '{"ok":false,"error":"Credenciales inválidas"}'; 
      exit;
  }
  
  $email=isset($u['email'])?$u['email']:'';
  $isAdmin = $email && (function_exists('str_ends_with') ? str_ends_with(strtolower($email), ADMIN_DOMAIN) : (substr(strtolower($email), -strlen(ADMIN_DOMAIN))===ADMIN_DOMAIN));
  $_SESSION['user']=$username;
  $_SESSION['client_name']=(isset($u['alias'])&&$u['alias']!=='')?$u['alias']:$username;
  $_SESSION['folder_url']=isset($u['drive_url'])?$u['drive_url']:null;
  $_SESSION['is_admin']=$isAdmin;
  $t=createSessionToken('user',$username,30,'light'); if($t){ setcookie('mcv_token',$t,['expires'=>time()+2592000,'path'=>'/','secure'=>isset($_SERVER['HTTPS']),'httponly'=>true,'samesite'=>'Lax']); }
  
  header('Content-Type: application/json');
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
    if($r['raw']===false||$r['raw']===null){
      $err=$r['error']??'Conexión fallida';
      file_put_contents(__DIR__.'/debug_auth.log', date('Y-m-d H:i:s')." Error Other: $err\n", FILE_APPEND);
      header('Content-Type: application/json');
      echo '{"ok":false,"error":"'.$err.'"}';
      exit;
    }
    $text=trim((string)$r['raw']);
    
    // El script de GAS para "Otros" devuelve texto plano (URL o "null"), no JSON.
    if ($text === 'null' || $text === '') {
        header('Content-Type: application/json');
        echo '{"ok":false,"error":"Credenciales inválidas o usuario no encontrado"}';
        exit;
    }

    // Si llegamos aquí, $text es la URL del drive folder
    $_SESSION['user'] = $username;
    $_SESSION['client_name'] = $username; // El script "Otros" no devuelve nombre/alias
    $_SESSION['folder_url'] = $text;
    $_SESSION['is_admin'] = false;
    
    $t=createSessionToken('user',$username,30,'light'); 
    if($t){ setcookie('mcv_token',$t,['expires'=>time()+2592000,'path'=>'/','secure'=>isset($_SERVER['HTTPS']),'httponly'=>true,'samesite'=>'Lax']); }
    
    header('Content-Type: application/json');
    echo json_encode([
        'ok' => true,
        'admin' => false,
        'is_q' => false,
        'folder_url' => $text
    ]);
    exit;
  } else {
    header('Content-Type: application/json');
    echo '{"ok":false,"error":"Endpoint usuarios Other no configurado"}';
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
