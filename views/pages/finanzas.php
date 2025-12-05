<?php
if(session_status()!==PHP_SESSION_ACTIVE){ session_start(); }
if(!isset($_SESSION['user'])){header('Location: /login');exit;}
if(isset($_GET['alias'])){ $p=$_GET; unset($p['alias']); $q=count($p)?('?'.http_build_query($p)):''; header('Location: finanzas.php'.$q); exit; }
require_once __DIR__.'/../../config/config.php';
require_once __DIR__.'/../../lib/env.php';
require_once __DIR__.'/../../lib/gas.php';
require_once __DIR__.'/../../lib/format.php';
$client=isset($_SESSION['client_name'])?$_SESSION['client_name']:$_SESSION['user'];
$fin=gas_finanzas($client);
$error=$fin['error'];
$status=$fin['status'];
$method=$fin['method'];
$tarifa=$fin['data']['tarifa'];
$tarifaExceso=$fin['data']['tarifaExceso'];
$volumen=$fin['data']['volumen'];
$excesos=$fin['data']['excesos'];
$folio=$fin['data']['factura'];
$servicios=$fin['data']['servicios'];
$tarifaFmt=(is_string($tarifa)&&is_numeric(str_replace(',','.', $tarifa)))?('$'.number_format((float)str_replace(',','.', $tarifa),2,'.','')):$tarifa;
$tarifaExcesoFmt=(is_string($tarifaExceso)&&is_numeric(str_replace(',','.', $tarifaExceso)))?('$'.number_format((float)str_replace(',','.', $tarifaExceso),2,'.','')):$tarifaExceso;
$excesosFmt=fmt_decimal($excesos,1);
$serviciosFmt=(is_string($servicios)&&is_numeric(str_replace(',','.', $servicios)))?(number_format((float)str_replace(',','.', $servicios),0,'.','')):$servicios;
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <?php $pageTitle='Finanzas'; include __DIR__.'/../layout/head.php'; ?>
</head>
<body>
  <?php include __DIR__.'/../layout/header.php'; ?>
  <main class="container">
    <div class="card">
      <h2 class="title">Finanzas</h2>
      <div>Tarifa actual (sin IVA): <?php echo htmlspecialchars($tarifaFmt); ?></div>
      <div>Tarifa exceso m3 (sin IVA): <?php echo htmlspecialchars($tarifaExcesoFmt); ?> <button type="button" class="btn secondary" id="excesoInfoBtn">¿Cómo se calcula?</button></div>
      <div>Volumen contratado (m3): <?php echo htmlspecialchars($volumen); ?></div>
      <div>Excesos (m3): <?php echo htmlspecialchars($excesosFmt); ?></div>
      <div>Última factura: <?php echo htmlspecialchars($folio); ?></div>
      <div>Servicios realizados este mes: <?php echo htmlspecialchars($serviciosFmt); ?></div>
    </div>
    <?php if($error){ ?>
    <div class="card" style="margin-top:12px">
      <h2 class="title diagnose">Diagnóstico — Si no carga, presiona Ctrl + F5 para actualizar completamente</h2>
      <div>Alias: <?php echo htmlspecialchars($client); ?></div>
      <div>Método: <?php echo htmlspecialchars($method); ?></div>
      <div>Estado: <?php echo htmlspecialchars($status===null?'sin estado':(string)$status); ?></div>
      <div>Error: <?php echo htmlspecialchars($error); ?></div>
    </div>
    <?php } ?>
  </main>
  <div id="excesoModal" class="modal" style="display:none">
    <div class="modal-content">
      <button type="button" class="modal-close" id="excesoClose" aria-label="Cerrar">×</button>
      <div class="modal-title">Cálculo de exceso</div>
      <div class="modal-body">
        La tarifa de exceso es el precio por cada m³ que se excede del volumen contratado. Si generas exceso, se cobra proporcionalmente usando regla de 3.
        <div style="margin-top:12px" id="calc" data-tarifa="<?php echo htmlspecialchars((string)$tarifaExceso); ?>">
          <label style="display:block;margin-bottom:8px">Exceso generado (m³)<input class="input" type="number" step="0.01" min="0" id="excesoM3" style="width:140px"></label>
          <div id="excesoResult" style="margin-top:8px;font-weight:600"></div>
        </div>
      </div>
      <div class="actions"><button type="button" class="btn" id="excesoOk">Entendido</button></div>
    </div>
  </div>
  <?php include __DIR__.'/../layout/footer.php'; ?>
  <script>
  (function(){
    var btn=document.getElementById('excesoInfoBtn');
    var m=document.getElementById('excesoModal');
    var x=document.getElementById('excesoClose');
    var ok=document.getElementById('excesoOk');
    var calc=document.getElementById('calc');
    var inp=document.getElementById('excesoM3');
    function open(){ if(m) m.style.display='flex'; }
    function close(){ if(m) m.style.display='none'; }
    function fmt(n){ try{ var v=parseFloat(n); if(isNaN(v)) return ''; return '$'+v.toFixed(2); }catch(_){ return ''; } }
    function update(){ var t=parseFloat(calc?calc.dataset.tarifa:''||''); var e=parseFloat(inp.value||'0'); if(isNaN(t)||isNaN(e)) return; var r=t*e; var el=document.getElementById('excesoResult'); if(el){ el.textContent='Cobro estimado: '+fmt(r)+' (sin IVA)'; } }
    if(btn) btn.addEventListener('click',open);
    if(x) x.addEventListener('click',close);
    if(ok) ok.addEventListener('click',close);
    if(m) m.addEventListener('click',function(ev){ if(!ev.target.closest('.modal-content')) close(); });
    document.addEventListener('keydown',function(e){ if(e.key==='Escape') close(); });
    if(inp){ inp.addEventListener('input',update); }
  })();
  </script>
</body>
</html>
