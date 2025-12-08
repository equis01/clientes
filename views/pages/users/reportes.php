<?php
if(session_status()!==PHP_SESSION_ACTIVE){ session_start(); }
if(!isset($_SESSION['user'])){header('Location: /users/login');exit;}
if(isset($_GET['alias'])){ $p=$_GET; unset($p['alias']); $q=count($p)?('?'.http_build_query($p)):''; header('Location: /users/reportes'.$q); exit; }
require_once dirname(__DIR__,3).'/config/config.php';
require_once dirname(__DIR__,3).'/lib/env.php';
require_once dirname(__DIR__,3).'/lib/gas.php';
$client=isset($_SESSION['client_name'])?$_SESSION['client_name']:$_SESSION['user'];
$reportes=[];
$resp=gas_exec(['service'=>'bd','action'=>'metrics','alias'=>$client]);
if($resp['ok'] && is_array($resp['data']) && isset($resp['data']['reportes']) && is_array($resp['data']['reportes'])){
  $reportes=$resp['data']['reportes'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <?php $pageTitle='Reportes'; include dirname(__DIR__,2).'/layout/head.php'; ?>
</head>
<body>
  <?php include dirname(__DIR__,2).'/layout/header.php'; ?>
  <main class="container">
    <div class="card">
      <h2 class="title">Reportes</h2>
      <ul>
        <?php if(count($reportes)===0){ ?>
          <li>Sin reportes disponibles</li>
        <?php } else { foreach($reportes as $r){ $name=isset($r['name'])?$r['name']:'Reporte'; $url=isset($r['url'])?$r['url']:'#'; ?>
          <li><a target="_blank" href="<?php echo htmlspecialchars($url); ?>"><?php echo htmlspecialchars($name); ?></a></li>
        <?php } } ?>
      </ul>
    </div>
  </main>
  <?php include dirname(__DIR__,2).'/layout/footer.php'; ?>
</body>
</html>
