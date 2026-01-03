<?php
require_once __DIR__.'/../config/config.php';
require_once __DIR__.'/env.php';
function gas_url($service){
  $s=strtolower($service??'');
  $urlBd=env('GAS_EXEC_URL_BD_QRO');
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
    $opts=[CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>30,CURLOPT_HTTPHEADER=>['Accept: application/json'],CURLOPT_FOLLOWLOCATION=>true,CURLOPT_MAXREDIRS=>5];
    if($insecure){ $opts[CURLOPT_SSL_VERIFYPEER]=false; $opts[CURLOPT_SSL_VERIFYHOST]=false; }
    curl_setopt_array($ch,$opts);
    $raw=curl_exec($ch);
    $status=curl_getinfo($ch,CURLINFO_HTTP_CODE);
    $curlErr=curl_error($ch);
    curl_close($ch);
    $method='curl';
    if($raw===false){ $error='Conexión fallida: '.$curlErr; }
  } else {
    $context=stream_context_create(['http'=>['method'=>'GET','timeout'=>30,'header'=>'Accept: application/json','ignore_errors'=>true],'ssl'=>['verify_peer'=>!$insecure,'verify_peer_name'=>!$insecure]]);
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
  // 1. Resolvemos qué año/mes se solicita
  // Usamos zona horaria de México para consistencia con servidor
  $tz = new DateTimeZone('America/Mexico_City');
  $now = new DateTime('now', $tz);
  $curY = (int)$now->format('Y');
  $curM = (int)$now->format('n'); // 1-12
  
  $reqY = $anio ? (int)$anio : $curY;
  // Normalizar mes: '' o 'anual' -> 'anual'
  $reqM = ($mes==='' || strtolower($mes)==='anual') ? 'anual' : (int)$mes;
  
  // Archivo de caché por año
  $cacheDir = __DIR__ . '/data';
  if(!is_dir($cacheDir)){ @mkdir($cacheDir, 0755, true); }
  $cacheFile = $cacheDir . "/metrics_{$alias}_{$reqY}.json";
  
  // 2. Determinar si necesitamos actualizar desde GAS
  $update = false;
  
  if(!file_exists($cacheFile)){
    $update = true; // No existe caché -> actualizar
  } else {
    // Si existe caché, verificamos reglas de negocio
    if($reqY === $curY){
      // Año actual
      $mtime = filemtime($cacheFile);
      $age = time() - $mtime;
      
      // Regla: "si quiero ver registros del mes y hoy es 15... revisa gas"
      // Si estamos viendo el mes en curso (o anual del año en curso), actualizamos
      // para tener los datos más recientes.
      // Damos un margen de 5 minutos (300s) para no saturar si recargan rápido.
      if($reqM === 'anual' || $reqM === $curM){
        if($age > 300) { $update = true; }
      } else {
        // Viendo mes pasado del año actual (ej. ver Enero en Marzo)
        // Si el archivo es de "hoy" (o muy reciente), asumimos que tiene los datos históricos bien.
        // Si es viejo (> 24h), actualizamos por si hubo correcciones.
        if($age > 86400) { $update = true; }
      }
    } else {
      // Año pasado. Asumimos que es estático.
      // Solo actualizamos si no existe (ya cubierto arriba).
    }
  }
  
  $r = null;
  $source = 'cache';
  
  // 3. Ejecutar actualización si es necesario
  if($update){
    // Pedimos TODO el año (mes vacio) para llenar el caché
    $params = ['service'=>'bd','action'=>'metrics','alias'=>$alias,'anio'=>$reqY,'mes'=>''];
    $r = gas_exec($params, $insecure);
    
    if($r['ok'] && is_array($r['data'])){
      // Guardamos la respuesta completa en JSON
      @file_put_contents($cacheFile, json_encode($r['data']));
      $source = 'gas';
    } else {
      // Falló GAS. Si tenemos caché viejo, lo usamos como fallback
      if(file_exists($cacheFile)){
        $r['data'] = json_decode(file_get_contents($cacheFile), true);
        $r['ok'] = true; // Forzamos OK para mostrar datos viejos
        $source = 'cache_fallback';
      }
    }
  } else {
    // Cargar de caché
    $json = file_get_contents($cacheFile);
    $data = json_decode($json, true);
    if(is_array($data)){
      $r = ['ok'=>true, 'data'=>$data];
    } else {
      // JSON corrupto, forzar update
      $update = true;
      // ... (repetir lógica update, simplificado: retornamos error si falla)
      $r = ['ok'=>false, 'error'=>'Cache corrupto'];
    }
  }
  
  // 4. Procesar y Filtrar datos para la respuesta
  $servicios='-'; $rows=[]; $periodo=['activo'=>false,'mes'=>null,'anio'=>null,'mesTexto'=>null];
  $error = $r['error'] ?? null;
  $status = $r['status'] ?? null;
  $raw = $r['raw'] ?? null;
  $method = $r['method'] ?? null;
  $url = $r['url'] ?? null;
  $public_url = $r['public_url'] ?? null;
  
  if($r['ok'] && is_array($r['data'])){
    $d = $r['data'];
    // Datos crudos del año completo
    $allRows = isset($d['ultimosServicios']) && is_array($d['ultimosServicios']) ? $d['ultimosServicios'] : [];
    
    // Filtramos si se pidió un mes específico
    if($reqM !== 'anual'){
      $filteredRows = [];
      foreach($allRows as $row){
        // Asumimos que $row['c'] es la fecha (YYYY-MM-DD o similar)
        // Verificamos si la fecha corresponde al mes solicitado
        $dateStr = $row['c'] ?? '';
        if($dateStr){
           // Extraer mes con mayor flexibilidad
           $ts = strtotime($dateStr);
           if(!$ts){
             // Intentar limpieza si falla (ej. formatos con zona horaria o texto extra)
             $c1 = preg_replace('/\s*\([^)]*\)\s*/','',$dateStr);
             $c1 = preg_replace('/GMT([+-]\d{4})/','$1',$c1);
             $ts = strtotime($c1);
           }
           
           if($ts){
             $mPart = (int)date('m', $ts);
             if($mPart === $reqM){
               $filteredRows[] = $row;
             }
           }
        }
      }
      $rows = $filteredRows;
      $servicios = count($rows); // Recalculamos total para el mes
      
      // Reconstruimos info de periodo para la vista
      $mesesN = [1=>'Enero',2=>'Febrero',3=>'Marzo',4=>'Abril',5=>'Mayo',6=>'Junio',7=>'Julio',8=>'Agosto',9=>'Septiembre',10=>'Octubre',11=>'Noviembre',12=>'Diciembre'];
      $periodo = [
        'activo' => true,
        'mes' => $reqM,
        'anio' => $reqY,
        'mesTexto' => $mesesN[$reqM] ?? ''
      ];
      
    } else {
      // Anual: pasamos todo directo
      $rows = $allRows;
      if(isset($d['serviciosRealizados'])){ $servicios=$d['serviciosRealizados']; }
      else { $servicios = count($rows); }
      
      if(isset($d['filtroPeriodo']) && is_array($d['filtroPeriodo'])){ 
          $periodo = $d['filtroPeriodo']; 
      }
    }
  }
  
  return ['ok'=>$r['ok'],'servicios'=>$servicios,'rows'=>$rows,'periodo'=>$periodo,'error'=>$error,'status'=>$status,'raw'=>$raw,'method'=>$method,'url'=>$url,'public_url'=>$public_url,'source'=>$source];
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

function gas_finanzas_list($alias,$insecure=false){
  $params=['service'=>'bd','action'=>'finanzas','alias'=>$alias];
  $r=gas_exec($params,$insecure);
  $list=[];
  if($r['ok'] && is_array($r['data'])){
    $d=$r['data'];
    if(isset($d['resultados']) && is_array($d['resultados'])){ $list=$d['resultados']; }
  }
  return ['ok'=>$r['ok'],'resultados'=>$list,'error'=>$r['error'],'status'=>$r['status'],'raw'=>$r['raw'],'method'=>$r['method'],'url'=>$r['url'],'public_url'=>$r['public_url']];
}

function gas_generate_report($alias,$mes,$anio,$driveUrl,$insecure=false,$usuario=null){
  $mraw=strtolower(trim((string)$mes));
  if($mraw==='anual' || $mraw==='0' || $mraw==='00'){ $mm='anual'; }
  else { $mm=preg_replace('/\D/','', (string)$mes); if($mm===''){ $mm=''; } else { $mm=str_pad((string)intval($mm),2,'0',STR_PAD_LEFT); } }
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
  $mraw=strtolower(trim((string)$mes));
  if($mraw==='anual' || $mraw==='0' || $mraw==='00'){ $mm='anual'; }
  else { $mm=preg_replace('/\D/','', (string)$mes); if($mm===''){ $mm=''; } else { $mm=str_pad((string)intval($mm),2,'0',STR_PAD_LEFT); } }
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
  $params=['service'=>'usuarios_q','action'=>'users'];
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
