<?php
ini_set('display_errors','0'); error_reporting(E_ERROR | E_PARSE);
session_start(); header('Content-Type: application/json');
require_once __DIR__.'/../lib/db.php';
$token=isset($_COOKIE['mcv_token'])?$_COOKIE['mcv_token']:'';
if($token===''){ echo json_encode(['ok'=>false,'error'=>'Sin token']); exit; }
$row=findSessionToken($token);
if(!is_array($row)){ echo json_encode(['ok'=>false,'error'=>'Token inválido']); exit; }
$rev=$row['revoked_at']??''; $exp=$row['expires_at']??''; $okExp=$exp!=='' && (strtotime($exp)!==false) && time()<strtotime($exp);
if($rev || !$okExp){ echo json_encode(['ok'=>false,'error'=>'Token expirado']); exit; }
if($_SERVER['REQUEST_METHOD']==='POST'){
  $mode=strtolower(trim($_POST['mode']??'')); if(!in_array($mode,array('dark','light'),true)){ echo json_encode(['ok'=>false,'error'=>'Modo inválido']); exit; }
  setTokenTheme($token,$mode); echo json_encode(['ok'=>true,'mode'=>$mode]); exit;
}
echo json_encode(['ok'=>true,'mode'=>(isset($row['theme'])&&$row['theme']!==''?$row['theme']:'light')]);
