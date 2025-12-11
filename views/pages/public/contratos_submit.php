<?php
ini_set('display_errors','0');
error_reporting(E_ERROR|E_PARSE);
header('Content-Type: application/json');
session_start();
require_once dirname(__DIR__).'/lib/env.php';

function norm($s){ return trim((string)$s); }
function json_store_path(){ $dir=dirname(__DIR__).'/data'; if(!is_dir($dir)){ @mkdir($dir,0777,true); } return $dir.'/contract_requests.json'; }

if($_SERVER['REQUEST_METHOD']!=='POST'){ echo json_encode(['ok'=>false,'error'=>'Método inválido']); exit; }
$raw=file_get_contents('php://input');
$data=$raw?json_decode($raw,true):null;
if(!is_array($data)){ echo json_encode(['ok'=>false,'error'=>'JSON inválido']); exit; }

$tipo=norm($data['tipo']??'');
$sucursal=norm($data['sucursal']??'');
$datos=is_array($data['datos']??null)?$data['datos']:[];
if($tipo!=='contrato' || empty($datos['razon_social'])){ echo json_encode(['ok'=>false,'error'=>'Datos incompletos']); exit; }

$id=bin2hex(random_bytes(16));
$now=date('c');
$entry=[
  'request_id'=>$id,
  'status'=>'pending',
  'sucursal'=>$sucursal,
  'created_at'=>$now,
  'updated_at'=>$now,
  'datos'=>$datos,
  'meta'=>is_array($data['meta']??null)?$data['meta']:[]
];

$path=json_store_path();
$list=[];
if(is_file($path)){
  $raw=@file_get_contents($path);
  $j=$raw?json_decode($raw,true):null;
  $list=is_array($j)?$j:[];
}
$list[]=$entry;
@file_put_contents($path,json_encode($list,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT));

echo json_encode(['ok'=>true,'request_id'=>$id]);
exit;

