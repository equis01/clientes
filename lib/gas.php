<?php
require_once __DIR__.'/../config/config.php';
require_once __DIR__.'/env.php';
function gas_url($service){
  $s=strtolower($service??'');
  $urlBd=env('GAS_EXEC_URL_BD');
  $urlRep=env('GAS_EXEC_URL_REPORTES');
  $urlUsersQ=env('GAS_USERS_Q_URL');
  $urlUsersOther=env('GAS_USERS_OTHER_URL');
  if($s==='reportes'){ return $urlRep?:$urlBd; }
  if($s==='usuarios_q'){ return $urlUsersQ?:($urlUsersOther?:($urlBd?:$urlRep)); }
  if($s==='usuarios_other'){ return $urlUsersOther?:($urlUsersQ?:($urlBd?:$urlRep)); }
  return $urlBd?:$urlRep;
}
function gas_exec($params,$insecure=false){
  $service=strtolower($params['service'] ?? 'servicios');
  unset($params['service']);
  $qs=http_build_query($params);
  $base=gas_url($service);
  if(!$base){
    return ['ok'=>false,'data'=>null,'status'=>null,'error'=>'endpoint BD no configurado','raw'=>null,'method'=>null,'url'=>null,'public_url'=>null];
  }
  $l=strtolower($base);
  if(strpos($l,'/macros/s/')===false || strpos($l,'/exec')===false || strpos($l,'/library/')!==false){
    return ['ok'=>false,'data'=>null,'status'=>null,'error'=>'url invalida: usar Web App /macros/s/.../exec','raw'=>null,'method'=>null,'url'=>$base,'public_url'=>null];
  }
  $url=$base.'?'.$qs;
  $method='';
  $status=null;$raw=null;$error=null;
  if(function_exists('curl_init')){
    $ch=curl_init($url);
    $opts=[CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>15,CURLOPT_HTTPHEADER=>['Accept: application/json'],CURLOPT_FOLLOWLOCATION=>true,CURLOPT_MAXREDIRS=>5];
    if($insecure){ $opts[CURLOPT_SSL_VERIFYPEER]=false; $opts[CURLOPT_SSL_VERIFYHOST]=false; }
    curl_setopt_array($ch,$opts);
    $raw=curl_exec($ch);
    $status=curl_getinfo($ch,CURLINFO_HTTP_CODE);
    $curlErr=curl_error($ch);
    curl_close($ch);
    $method='curl';
    if($raw===false){ $error='Conexión fallida: '.$curlErr; }
  } else {
    $context=stream_context_create(['http'=>['method'=>'GET','timeout'=>15,'header'=>'Accept: application/json','ignore_errors'=>true],'ssl'=>['verify_peer'=>!$insecure,'verify_peer_name'=>!$insecure]]);
    $headers=@get_headers($url,1);
    $redir=null;
    if(is_array($headers) && isset($headers['Location'])){ $redir=is_array($headers['Location'])?end($headers['Location']):$headers['Location']; }
    $target=$redir?:$url;
    $raw=@file_get_contents($target,false,$context);
    $method='stream';
    if(is_array($headers) && isset($headers[0])){ if(preg_match('/\s(\d{3})\s/',$headers[0],$m)){ $status=intval($m[1]); } }
    if($raw===false){ $error='Conexión fallida'; }
  }
  $data=null;
  if($raw!==false && $raw!==null){ $data=json_decode($raw,true); }
  $ok=is_array($data) && !empty($data['ok']) && ($status===null || $status<400);
  $public_url=preg_replace('/([?&]alias=)[^&]+/','$1***',$url);
  return ['ok'=>$ok,'data'=>$data,'status'=>$status,'error'=>is_array($data)&&empty($data['ok'])?(isset($data['error'])?$data['error']:'ok=false'):$error,'raw'=>$raw,'method'=>$method,'url'=>$url,'public_url'=>$public_url];
}
function gas_metrics($alias,$mes,$anio,$insecure=false){
  $params=['service'=>'bd','action'=>'metrics','alias'=>$alias];
  if($mes!==''){ $params['mes']=$mes; }
  if($anio!==''){ $params['anio']=$anio; }
  $r=gas_exec($params,$insecure);
  $servicios='-'; $rows=[]; $periodo=['activo'=>false,'mes'=>null,'anio'=>null,'mesTexto'=>null];
  if($r['ok'] && is_array($r['data'])){
    $d=$r['data'];
    if(isset($d['serviciosRealizados'])){ $servicios=$d['serviciosRealizados']; }
    if(isset($d['ultimosServicios']) && is_array($d['ultimosServicios'])){ $rows=$d['ultimosServicios']; }
    if(isset($d['filtroPeriodo']) && is_array($d['filtroPeriodo'])){ $periodo=$d['filtroPeriodo']; if(isset($d['resumenPorMes']) && is_array($d['resumenPorMes']) && count($d['resumenPorMes'])>0){ $periodo['mesTexto']=$d['resumenPorMes'][0]['mesTexto']??$periodo['mesTexto']; } }
  }
  return ['ok'=>$r['ok'],'servicios'=>$servicios,'rows'=>$rows,'periodo'=>$periodo,'error'=>$r['error'],'status'=>$r['status'],'raw'=>$r['raw'],'method'=>$r['method'],'url'=>$r['url'],'public_url'=>$r['public_url']];
}

function gas_finanzas($alias,$insecure=false){
  $params=['service'=>'bd','action'=>'finanzas','alias'=>$alias];
  $r=gas_exec($params,$insecure);
  $res=['tarifa'=>'-','tarifaExceso'=>'-','volumen'=>'-','excesos'=>'-','factura'=>'-','servicios'=>'-'];
  if($r['ok'] && is_array($r['data'])){
    $d=$r['data'];
    if(isset($d['resultados']) && is_array($d['resultados']) && count($d['resultados'])>0){
      $x=$d['resultados'][0];
      $res['tarifa']=isset($x['tarifaSinIva'])?$x['tarifaSinIva']:$res['tarifa'];
      $res['tarifaExceso']=isset($x['tarifaExcesoM3SinIva'])?$x['tarifaExcesoM3SinIva']:$res['tarifaExceso'];
      $res['volumen']=isset($x['volumenContratadoM3'])?$x['volumenContratadoM3']:$res['volumen'];
      $res['excesos']=isset($x['excesosM3'])?$x['excesosM3']:$res['excesos'];
      $res['factura']=isset($x['factura'])?$x['factura']:$res['factura'];
      $res['servicios']=isset($x['serviciosRealizados'])?$x['serviciosRealizados']:$res['servicios'];
    }
  } else {
    // Fallback de diagnóstico si el endpoint no reconoce action=finanzas
    $rd=gas_exec(['service'=>'bd','action'=>'diagnoseFinanzas','alias'=>$alias],$insecure);
    if($rd['ok'] && is_array($rd['data'])){
      $extra='; diagnoseFinanzas OK';
      $r['error'] = ($r['error']?$r['error']:'accion invalida').$extra;
    }
  }
  return ['ok'=>$r['ok'],'data'=>$res,'error'=>$r['error'],'status'=>$r['status'],'raw'=>$r['raw'],'method'=>$r['method'],'url'=>$r['url'],'public_url'=>$r['public_url']];
}

function gas_generate_report($alias,$mes,$anio,$driveUrl,$insecure=false,$usuario=null){
  $mm=preg_replace('/\D/','', (string)$mes);
  if($mm===''){ $mm=''; } else { $mm=str_pad((string)intval($mm),2,'0',STR_PAD_LEFT); }
  $yy=preg_replace('/\D/','', (string)$anio);
  $params=['service'=>'reportes','action'=>'generateReport','alias'=>$alias];
  if($mm!==''){ $params['mes']=$mm; }
  if($yy!==''){ $params['anio']=$yy; }
  if($driveUrl){ $params['drive_url']=$driveUrl; }
  if($usuario){ $params['usuario']=$usuario; }
  $r=gas_exec($params,$insecure);
  $fileId=null; $fileName=null; $downloadUrl=null; $folderPath=null;
  if($r['ok'] && is_array($r['data'])){
    $d=$r['data'];
    $fileId=$d['fileId']??($d['id']??null);
    $fileName=$d['fileName']??($d['name']??null);
    $downloadUrl=$d['downloadUrl']??($fileId?('https://drive.google.com/uc?export=download&id='.$fileId):null);
    $folderPath=$d['folderPath']??null;
  }
  return ['ok'=>$r['ok'],'fileId'=>$fileId,'fileName'=>$fileName,'downloadUrl'=>$downloadUrl,'folderPath'=>$folderPath,'error'=>$r['error'],'status'=>$r['status'],'method'=>$r['method'],'public_url'=>$r['public_url']];
}

function gas_lock_report($fileId,$insecure=false){
  $fid=trim((string)$fileId);
  if($fid===''){ return ['ok'=>false,'error'=>'fileId requerido']; }
  $params=['service'=>'reportes','action'=>'lockReport','fileId'=>$fid];
  $r=gas_exec($params,$insecure);
  return ['ok'=>$r['ok'],'error'=>$r['error'],'status'=>$r['status'],'method'=>$r['method'],'public_url'=>$r['public_url']];
}

function gas_find_report($alias,$mes,$anio,$driveUrl,$insecure=false){
  $mm=preg_replace('/\D/','', (string)$mes);
  if($mm===''){ $mm=''; } else { $mm=str_pad((string)intval($mm),2,'0',STR_PAD_LEFT); }
  $yy=preg_replace('/\D/','', (string)$anio);
  $params=['service'=>'reportes','action'=>'findReport','alias'=>$alias];
  if($mm!==''){ $params['mes']=$mm; }
  if($yy!==''){ $params['anio']=$yy; }
  if($driveUrl){ $params['drive_url']=$driveUrl; }
  $r=gas_exec($params,$insecure);
  $fileId=null; $fileName=null; $downloadUrl=null; $folderPath=null;
  if($r['ok'] && is_array($r['data'])){
    $d=$r['data'];
    $fileId=$d['fileId']??($d['id']??null);
    $fileName=$d['fileName']??($d['name']??null);
    $downloadUrl=$d['downloadUrl']??($fileId?('https://drive.google.com/uc?export=download&id='.$fileId):null);
    $folderPath=$d['folderPath']??null;
  }
  return ['ok'=>$r['ok'],'fileId'=>$fileId,'fileName'=>$fileName,'downloadUrl'=>$downloadUrl,'folderPath'=>$folderPath,'error'=>$r['error'],'status'=>$r['status'],'method'=>$r['method'],'public_url'=>$r['public_url']];
}

function gas_users($username=null,$insecure=false){
  $isQ=$username? (preg_match('/^q/i',(string)$username)===1) : true;
  $service=$isQ?'usuarios_q':'usuarios_other';
  $params=['service'=>$service,'action'=>'users'];
  if($username){ $params['user']=$username; }
  $r=gas_exec($params,$insecure);
  $users=[]; $one=null;
  if($r['ok'] && is_array($r['data'])){
    $d=$r['data'];
    if(isset($d['users']) && is_array($d['users'])){ $users=$d['users']; }
    if($username && isset($users[$username]) && is_array($users[$username])){ $one=$users[$username]; }
  }
  return ['ok'=>$r['ok'],'users'=>$users,'user'=>$one,'error'=>$r['error'],'status'=>$r['status'],'method'=>$r['method'],'public_url'=>$r['public_url']];
}
