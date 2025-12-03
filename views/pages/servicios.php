<?php
if(session_status()!==PHP_SESSION_ACTIVE){ session_start(); }
if(!isset($_SESSION['user'])){header('Location: /login');exit;}
if(isset($_GET['alias'])){ $p=$_GET; unset($p['alias']); $q=count($p)?('?'.http_build_query($p)):''; header('Location: servicios.php'.$q); exit; }
require_once __DIR__.'/../../config/config.php';
require_once __DIR__.'/../../lib/env.php';
require_once __DIR__.'/../../lib/gas.php';
require_once __DIR__.'/../../lib/format.php';
$tz=env('TIMEZONE','America/Mexico_City');
if(function_exists('date_default_timezone_set')){date_default_timezone_set($tz);} 
$client=(isset($_SESSION['client_name'])?$_SESSION['client_name']:$_SESSION['user']);
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
$res=gas_metrics($client, $todoAnio? '' : $mes, $anio, $insecure);
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
        <label class="field" style="margin-left:8px">
          <input type="checkbox" name="todo_anio" value="1"<?php echo $todoAnio?' checked':''; ?>> Todo el año
        </label>
        <button type="submit" class="btn">Filtrar</button>
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
  <script>
  (function(){
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
  <?php if($error){ ?>
  <div class="container">
    <div class="card">
      <h2 class="title">Diagnóstico</h2>
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
