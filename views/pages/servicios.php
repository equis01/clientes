<?php
if(session_status()!==PHP_SESSION_ACTIVE){ session_start(); }
if(!isset($_SESSION['user'])){header('Location: /login');exit;}
if(isset($_GET['alias'])){ $p=$_GET; unset($p['alias']); $q=count($p)?('?'.http_build_query($p)):''; header('Location: servicios.php'.$q); exit; }
require_once __DIR__.'/../../config/config.php';
require_once __DIR__.'/../../lib/env.php';
require_once __DIR__.'/../../lib/gas.php';
require_once __DIR__.'/../../lib/format.php';
$tzTmp=env('TIMEZONE','America/Mexico_City');
if(isset($_GET['generate_report']) && $_SERVER['REQUEST_METHOD']==='POST'){
  header('Content-Type: application/json; charset=utf-8');
  $aliasSel=isset($_POST['alias_sel'])?trim($_POST['alias_sel']):'';
  $alias=$aliasSel!==''? $aliasSel : (isset($_SESSION['brand_name']) && $_SESSION['brand_name']?$_SESSION['brand_name']:(isset($_SESSION['client_name'])?$_SESSION['client_name']:$_SESSION['user']));
  $mesReq=isset($_POST['mes'])?trim($_POST['mes']):'';
  $anioReq=isset($_POST['anio'])?trim($_POST['anio']):'';
  $drive=isset($_SESSION['folder_url'])?$_SESSION['folder_url']:null;
  $insecure=isset($_POST['insecure']);
  $usuario=(isset($_SESSION['user'])?$_SESSION['user']:null);
  $nowSrv=new DateTime('now', new DateTimeZone($tzTmp));
  $daySrv=(int)$nowSrv->format('d');
  $curMSrv=(int)$nowSrv->format('m');
  $curYSrv=(int)$nowSrv->format('Y');
  $selM=(int)preg_replace('/\D/','',$mesReq);
  $selY=(int)preg_replace('/\D/','',$anioReq);
  $prevM=$curMSrv===1?12:($curMSrv-1);
  $prevY=$curMSrv===1?($curYSrv-1):$curYSrv;
  if($selM===$prevM && $selY===$prevY && $daySrv<=3){ echo json_encode(['ok'=>false,'error'=>'Disponible a partir del día 4 del mes actual']); exit; }
  $gen=gas_generate_report($alias,$mesReq,$anioReq,$drive,$insecure,$usuario);
  echo json_encode($gen);
  exit;
}
$tz=$tzTmp;
if(isset($_GET['lock_report']) && $_SERVER['REQUEST_METHOD']==='POST'){
  header('Content-Type: application/json; charset=utf-8');
  $fileId=isset($_POST['fileId'])?trim($_POST['fileId']):'';
  $lock=gas_lock_report($fileId);
  echo json_encode($lock);
  exit;
}
if(isset($_GET['find_report']) && $_SERVER['REQUEST_METHOD']==='POST'){
  header('Content-Type: application/json; charset=utf-8');
  $aliasSel=isset($_POST['alias_sel'])?trim($_POST['alias_sel']):'';
  $alias=$aliasSel!==''? $aliasSel : (isset($_SESSION['brand_name']) && $_SESSION['brand_name']?$_SESSION['brand_name']:(isset($_SESSION['client_name'])?$_SESSION['client_name']:$_SESSION['user']));
  $mesReq=isset($_POST['mes'])?trim($_POST['mes']):'';
  $anioReq=isset($_POST['anio'])?trim($_POST['anio']):'';
  $drive=isset($_SESSION['folder_url'])?$_SESSION['folder_url']:null;
  $insecure=isset($_POST['insecure']);
  $find=gas_find_report($alias,$mesReq,$anioReq,$drive,$insecure);
  echo json_encode($find);
  exit;
}
if(function_exists('date_default_timezone_set')){date_default_timezone_set($tz);} 
$client=(isset($_SESSION['client_name'])?$_SESSION['client_name']:$_SESSION['user']);
$brand=isset($_SESSION['brand_name'])?$_SESSION['brand_name']:null;
if(!$brand && $client){ $fin0=gas_finanzas($client); if($fin0['ok'] && is_array($fin0['data'])){ $bn=isset($fin0['data']['razonSocial'])?$fin0['data']['razonSocial']:null; if(!$bn && isset($fin0['raw'])){ } $_SESSION['brand_name']=$bn?:$_SESSION['brand_name']??null; $brand=$_SESSION['brand_name']; } }
$listRes=gas_finanzas_list($brand?:$client);
$aliases=[]; if($listRes['ok'] && is_array($listRes['resultados'])){ foreach($listRes['resultados'] as $row){ $al=trim((string)($row['alias']??'')); if($al!==''){ $aliases[$al]=true; } } }
$aliasSel=isset($_GET['alias_sel'])?trim($_GET['alias_sel']):'';
$aliasList=array_keys($aliases);
if(count($aliasList)>0){ if($aliasSel===''){ $aliasSel=$aliasList[0]; } elseif(!in_array($aliasSel,$aliasList,true)){ $aliasSel=$aliasList[0]; } }
$mes=isset($_GET['mes'])?trim($_GET['mes']):'';
$anio=isset($_GET['anio'])?trim($_GET['anio']):'';
$todoAnio=isset($_GET['todo_anio']) && ($_GET['todo_anio']==='1' || strtolower($_GET['todo_anio'])==='true');
$now=new DateTime('now', new DateTimeZone($tz));
$currentYear=$now->format('Y');
if($anio===''){$anio=$currentYear;} else {$anio=$currentYear;}
if(!$todoAnio && $mes===''){
  $day=(int)$now->format('d');
  $m=(int)$now->format('m');
  if($day<=7 && $m>1){ $mes=str_pad((string)($m-1),2,'0',STR_PAD_LEFT); }
  else { $mes=$now->format('m'); }
}
$servicios='-';
$rows=[];
$periodo=['activo'=>false,'mes'=>null,'anio'=>null,'mesTexto'=>null];
$error=null;$status=null;$raw=null;$debug=isset($_GET['debug']);$insecure=isset($_GET['insecure']);$method='';$gasPublic=null;
$queryAlias=$aliasSel!==''? $aliasSel : ($brand?:$client);
$res=gas_metrics($queryAlias, $todoAnio? '' : $mes, $anio, $insecure);
$servicios=$res['servicios'];
$rows=$res['rows'];
$periodo=$res['periodo'];
$error=$res['error'];
$status=$res['status'];
$raw=$res['raw'];
$method=$res['method'];
$gasPublic=$res['public_url'];
$gasReal=$res['url'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <?php $pageTitle='Servicios'; include __DIR__.'/../layout/head.php'; ?>
</head>
<body>
  <?php include __DIR__.'/../layout/header.php'; ?>
  <main class="container">
    <div class="card">
      <h2 class="title">Servicios</h2>
      <div>Realizados: <?php echo htmlspecialchars($servicios); ?></div>
      <?php if(!empty($periodo['activo'])){ ?>
        <div>Periodo: <?php echo htmlspecialchars($todoAnio?('Año '.$anio):(($periodo['mesTexto']?:'') . ' ' . ($periodo['anio']?:''))); ?></div>
      <?php } ?>
      <form method="get" action="/servicios" class="filter">
        <?php $meses=[1=>'Enero',2=>'Febrero',3=>'Marzo',4=>'Abril',5=>'Mayo',6=>'Junio',7=>'Julio',8=>'Agosto',9=>'Septiembre',10=>'Octubre',11=>'Noviembre',12=>'Diciembre']; ?>
        <div class="field">
          <label for="mes">Mes</label>
          <select id="mes" name="mes"<?php echo $todoAnio?' disabled':''; ?> data-default="<?php echo (int)$mes; ?>">
            <option value=""<?php echo $todoAnio?' selected':''; ?>>—</option>
            <?php foreach($meses as $mN=>$mNomb){ $sel=((int)$mes)===$mN? ' selected': ''; ?>
              <option value="<?php echo $mN; ?>"<?php echo $sel; ?>><?php echo htmlspecialchars($mNomb); ?></option>
            <?php } ?>
          </select>
        </div>
        <div class="field">
          <label for="anio">Año</label>
          <select id="anio" name="anio">
            <option value="<?php echo htmlspecialchars($currentYear); ?>" selected><?php echo htmlspecialchars($currentYear); ?></option>
          </select>
        </div>
        <?php if(count($aliases)>1){ ?>
        <div class="field">
          <label for="alias_sel">Alias</label>
          <select id="alias_sel" name="alias_sel">
            <?php foreach($aliasList as $al){ $sel=($aliasSel!=='' && $aliasSel===$al)?' selected':''; ?>
              <option value="<?php echo htmlspecialchars($al); ?>"<?php echo $sel; ?>><?php echo htmlspecialchars($al); ?></option>
            <?php } ?>
          </select>
        </div>
        <?php } ?>
        <label class="field" style="margin-left:8px">
          <input type="checkbox" name="todo_anio" value="1"<?php echo $todoAnio?' checked':''; ?>> Todo el año
        </label>
        <button type="submit" class="btn">Filtrar</button>
        <button type="button" class="btn secondary" id="btnGenerarReporte">Generar reporte</button>
      </form>
      <div style="margin-top:12px;overflow:auto">
        <table style="width:100%;border-collapse:collapse">
          <thead>
            <tr>
              <th style="text-align:left">Fecha reco.</th>
              <th style="text-align:left">Tipo de residuo</th>
              <th style="text-align:left">Remisión</th>
              <th style="text-align:right">m3</th>
              <th style="text-align:right">Kg</th>
              <th style="text-align:right">Excesos</th>
            </tr>
          </thead>
          <tbody>
            <?php if(count($rows)===0){ ?>
              <tr><td colspan="6">Sin datos disponibles</td></tr>
            <?php } else { foreach($rows as $r){ ?>
              <tr>
                <td><?php echo htmlspecialchars(fmt_date($r['c']??'', $tz)); ?></td>
                <td><?php echo htmlspecialchars($r['f']??''); ?></td>
                <td><?php echo htmlspecialchars($r['h']??''); ?></td>
                <td style="text-align:right"><?php echo htmlspecialchars(fmt_decimal($r['q']??'',1)); ?></td>
                <td style="text-align:right"><?php echo htmlspecialchars(fmt_decimal($r['s']??'',1)); ?></td>
                <td style="text-align:right"><?php echo htmlspecialchars(fmt_decimal($r['t']??'',1)); ?></td>
              </tr>
            <?php } } ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>
  <style>
  @keyframes spin{to{transform:rotate(360deg)}}
  .loader{width:16px;height:16px;border:2px solid #00DC2A;border-top-color:transparent;border-radius:50%;animation:spin .8s linear infinite}
  .loader.hidden{display:none}
  .status{font-weight:600}
  .status.ok{color:#00DC2A}
  .status.err{color:#d93025}
  </style>
  <div id="reportModal" class="modal" style="display:none">
    <div class="modal-content">
      <div class="modal-title">Generación de reporte</div>
      <div style="display:flex;align-items:center;gap:8px;margin:6px 0"><div id="reportLoader" class="loader"></div><div id="reportStatus" class="status">Generando...</div></div>
      <div id="downloadRow" style="display:none;align-items:center;gap:8px;margin:6px 0"><div id="downloadLoader" class="loader"></div><div id="downloadStatus" class="status">Descargando...</div><span style="margin-left:8px">Si no se descarga, <a id="downloadLink" href="#" target="_blank">da clic aquí</a></span></div>
      <div class="actions"><button type="button" class="btn" id="btnCerrarReporte">Cerrar</button></div>
    </div>
  </div>
  <script>
  (function(){
    var form=document.querySelector('form.filter');
    if(form){ form.addEventListener('submit',function(){ try{ window.showLoader('Cargando…'); }catch(_){} }); }
    var chk=document.querySelector('input[name="todo_anio"]');
    var sel=document.getElementById('mes');
    if(!chk||!sel) return;
    function sync(){
      if(chk.checked){ sel.disabled=true; sel.value=''; }
      else { sel.disabled=false; if(!sel.value){ sel.value=String(sel.dataset.default||''); } }
    }
    chk.addEventListener('change',sync);
    sync();
  })();
  </script>
  <script>
  (function(){
    var gen=document.getElementById('btnGenerarReporte');
    var modal=document.getElementById('reportModal');
    var loader=document.getElementById('reportLoader');
    var status=document.getElementById('reportStatus');
    var downloadRow=document.getElementById('downloadRow');
    var downloadLoader=document.getElementById('downloadLoader');
    var downloadStatus=document.getElementById('downloadStatus');
    var downloadLink=document.getElementById('downloadLink');
    var btnClose=document.getElementById('btnCerrarReporte');
    var fileId=null; var dotsTimer1=null; var dotsTimer2=null; var dots=1;
    if(!gen) return;
    gen.addEventListener('click',function(){
      var mSel=document.getElementById('mes');
      var aSel=document.getElementById('anio');
      var m=(mSel&&mSel.value)|| (mSel&&mSel.dataset.default)|| '';
      var y=aSel?aSel.value:'';
      if(!m||!y){ alert('Selecciona mes y año'); return; }
      try{
        var now=new Date();
        var d=now.getDate();
        var cm=now.getMonth()+1; var cy=now.getFullYear();
        var pm=cm===1?12:(cm-1); var py=cm===1?(cy-1):cy;
        if(parseInt(m,10)===pm && parseInt(y,10)===py && d<=3){ alert('Disponible a partir del día 4 del mes actual'); return; }
      }catch(_){}
      gen.disabled=true; gen.textContent='Generando...';
      if(modal){ modal.style.display='flex'; }
      loader.classList.remove('hidden');
      status.classList.remove('ok'); status.classList.remove('err');
      if(downloadRow){ downloadRow.style.display='none'; }
      function startDotsOn(el, base){ var t=el===downloadStatus? 'timer2':'timer1'; if(t==='timer2'){ if(dotsTimer2) clearInterval(dotsTimer2); } else { if(dotsTimer1) clearInterval(dotsTimer1); } dots=1; el.textContent=base+'.'; var h=setInterval(function(){ dots=(dots%3)+1; el.textContent=base+(dots===1?'.':(dots===2?'..':'...')); },600); if(t==='timer2'){ dotsTimer2=h; } else { dotsTimer1=h; } }
      startDotsOn(status,'Generando');
      var aSel2=document.getElementById('alias_sel'); var aVal=(aSel2&&aSel2.value)||'';
      var bodyFind='mes='+encodeURIComponent(m)+'&anio='+encodeURIComponent(y)+'&alias_sel='+encodeURIComponent(aVal);
      fetch('/servicios?find_report=1',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:bodyFind})
        .then(function(r){ return r.json(); })
        .then(function(f){
          if(f&&f.ok){
            if(dotsTimer1) clearInterval(dotsTimer1);
            loader.classList.add('hidden');
            status.classList.add('ok'); status.textContent='Reporte adjunto a su carpeta de Drive';
            fileId=f.fileId||null;
            var u=f.downloadUrl || (f.fileId?('https://drive.google.com/uc?export=download&id='+f.fileId):null);
            if(downloadRow){ downloadRow.style.display='flex'; }
            if(downloadLoader){ downloadLoader.classList.remove('hidden'); }
            if(downloadStatus){ downloadStatus.classList.remove('ok'); downloadStatus.classList.remove('err'); startDotsOn(downloadStatus,'Descargando'); }
            if(downloadLink && u){ downloadLink.href=u; }
            if(u){ try{ window.open(u,'_blank'); }catch(_){ } }
            gen.disabled=false; gen.textContent='Generar reporte';
          } else {
            var bodyGen='mes='+encodeURIComponent(m)+'&anio='+encodeURIComponent(y)+'&alias_sel='+encodeURIComponent(aVal);
            status.classList.remove('err');
            startDotsOn(status,'Generando');
            fetch('/servicios?generate_report=1',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:bodyGen})
              .then(function(r2){ return r2.json(); })
              .then(function(j){
                if(j&&j.ok){
                  if(dotsTimer1) clearInterval(dotsTimer1);
                  loader.classList.add('hidden');
                  status.classList.add('ok'); status.textContent='Reporte adjunto a su carpeta de Drive';
                  fileId=j.fileId||null;
                  var url=j.downloadUrl || (j.fileId?('https://drive.google.com/uc?export=download&id='+j.fileId):null);
                  if(downloadRow){ downloadRow.style.display='flex'; }
                  if(downloadLoader){ downloadLoader.classList.remove('hidden'); }
                  if(downloadStatus){ downloadStatus.classList.remove('ok'); downloadStatus.classList.remove('err'); startDotsOn(downloadStatus,'Descargando'); }
                  if(downloadLink && url){ downloadLink.href=url; }
                  if(url){ try{ window.open(url,'_blank'); }catch(_){ } }
                  gen.disabled=false; gen.textContent='Generar reporte';
                } else {
                  var bodyFind2='mes='+encodeURIComponent(m)+'&anio='+encodeURIComponent(y)+'&alias_sel='+encodeURIComponent(aVal);
                  startDotsOn(status,'Generando');
                  fetch('/servicios?find_report=1',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:bodyFind2})
                    .then(function(r3){ return r3.json(); })
                    .then(function(ff){
                      gen.disabled=false; gen.textContent='Generar reporte';
                      if(dotsTimer1) clearInterval(dotsTimer1);
                      if(ff&&ff.ok){
                        loader.classList.add('hidden');
                        status.classList.add('ok'); status.textContent='Reporte adjunto a su carpeta de Drive';
                        fileId=ff.fileId||null;
                        var url2=ff.downloadUrl || (ff.fileId?('https://drive.google.com/uc?export=download&id='+ff.fileId):null);
                        if(downloadRow){ downloadRow.style.display='flex'; }
                        if(downloadLoader){ downloadLoader.classList.remove('hidden'); }
                        if(downloadStatus){ downloadStatus.classList.remove('ok'); downloadStatus.classList.remove('err'); startDotsOn(downloadStatus,'Descargando'); }
                        if(downloadLink && url2){ downloadLink.href=url2; }
                        if(url2){ try{ window.open(url2,'_blank'); }catch(_){ } }
                      } else {
                        loader.classList.add('hidden');
                        if(downloadRow){ downloadRow.style.display='none'; }
                        status.classList.add('err'); status.textContent='Error: '+(ff&&ff.error||'Fallo');
                      }
                    })
                    .catch(function(){ gen.disabled=false; gen.textContent='Generar reporte'; if(dotsTimer1) clearInterval(dotsTimer1); loader.classList.add('hidden'); if(downloadRow){ downloadRow.style.display='none'; } status.classList.add('err'); status.textContent='Error: Conexión fallida'; });
                }
              })
              .catch(function(){ gen.disabled=false; gen.textContent='Generar reporte'; if(dotsTimer1) clearInterval(dotsTimer1); loader.classList.add('hidden'); if(downloadRow){ downloadRow.style.display='none'; } status.classList.add('err'); status.textContent='Error: Conexión fallida'; });
          }
        })
        .catch(function(){ gen.disabled=false; gen.textContent='Generar reporte'; if(dotsTimer1) clearInterval(dotsTimer1); loader.classList.add('hidden'); if(downloadRow){ downloadRow.style.display='none'; } status.classList.add('err'); status.textContent='Error: Conexión fallida'; });
    });
    if(btnClose){ btnClose.addEventListener('click',function(){
      if(modal){ modal.style.display='none'; }
      if(dotsTimer1) clearInterval(dotsTimer1);
      if(dotsTimer2) clearInterval(dotsTimer2);
      if(fileId){ var b='fileId='+encodeURIComponent(fileId); fetch('/servicios?lock_report=1',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:b}).catch(function(){}); }
    }); }
  })();
  </script>
  <?php if($error){ ?>
  <div class="container">
    <div class="card">
      <h2 class="title diagnose">Diagnóstico — Si no carga, presiona Ctrl + F5 para actualizar completamente</h2>
      <div>Alias: <?php echo htmlspecialchars($client); ?></div>
      <div>Método: <?php echo htmlspecialchars($method); ?></div>
      <div>Estado: <?php echo htmlspecialchars($status===null?'sin estado':(string)$status); ?></div>
      <div>Error: <?php echo htmlspecialchars($error); ?></div>
      <?php if($debug && $raw){ ?>
        <div style="margin-top:8px;white-space:pre-wrap;word-break:break-word">Respuesta: <?php echo htmlspecialchars(substr($raw,0,800)); ?></div>
      <?php } ?>
      <div style="margin-top:8px">Prueba SSL relajado: <a href="<?php echo htmlspecialchars('/servicios?debug=1&insecure=1'); ?>">abrir</a></div>
    </div>
  </div>
  <?php } ?>
  <?php include __DIR__.'/../layout/footer.php'; ?>
</body>
</html>
