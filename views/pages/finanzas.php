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
      <div>Tarifa exceso m3 (sin IVA): <?php echo htmlspecialchars($tarifaExcesoFmt); ?></div>
      <div>Volumen contratado (m3): <?php echo htmlspecialchars($volumen); ?></div>
      <div>Excesos (m3): <?php echo htmlspecialchars($excesosFmt); ?></div>
      <div>Última factura: <?php echo htmlspecialchars($folio); ?></div>
      <div>Servicios realizados: <?php echo htmlspecialchars($serviciosFmt); ?></div>
    </div>
    <?php if($error){ ?>
    <div class="card" style="margin-top:12px">
      <h2 class="title">Diagnóstico</h2>
      <div>Alias: <?php echo htmlspecialchars($client); ?></div>
      <div>Método: <?php echo htmlspecialchars($method); ?></div>
      <div>Estado: <?php echo htmlspecialchars($status===null?'sin estado':(string)$status); ?></div>
      <div>Error: <?php echo htmlspecialchars($error); ?></div>
    </div>
    <?php } ?>
  </main>
  <?php include __DIR__.'/../layout/footer.php'; ?>
</body>
</html>
