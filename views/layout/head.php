<?php
$fullTitle = isset($pageTitle) && $pageTitle!=='' ? ($pageTitle . ' Clientes | Medios con Valor') : 'Clientes | Medios con Valor';
?>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo htmlspecialchars($fullTitle); ?></title>
<meta name="title" content="Portal Clientes | Medios con Valor">
<meta property="og:title" content="Portal Clientes | Medios con Valor">
<meta name="twitter:title" content="Portal Clientes | Medios con Valor">
<meta property="og:image" content="https://mediosconvalor.github.io/mcv/img/logo/logo.png">
  <meta name="twitter:image" content="https://mediosconvalor.github.io/mcv/img/logo/logo.png">
<meta name="author" content="Eduardo Vázquez">
<link rel="canonical" href="https://mediosconvalor.com">
<script>
(function(){ try{ var t=localStorage.getItem('theme'); if(t){ if(t==='dark'){ document.documentElement.classList.add('theme-dark'); } else { document.documentElement.classList.remove('theme-dark'); } } else { var mq=(window.matchMedia&&window.matchMedia('(prefers-color-scheme: dark)')); if(mq && mq.matches){ document.documentElement.classList.add('theme-dark'); } else { document.documentElement.classList.remove('theme-dark'); } } }catch(_){} })();
</script>
<link rel="stylesheet" href="/assets/css/app.css">
<link rel="icon" href="https://mediosconvalor.com/wp-content/uploads/2019/03/cropped-favicon-32x32-1-32x32.png" type="image/webp">
<script>
(function(){
  try{
    var saved=null; try{ saved=localStorage.getItem('theme'); }catch(_){}
    var supports=!!window.matchMedia;
    if(saved===null && !supports){ fetch('/theme',{headers:{'Accept':'application/json'}}).then(function(r){return r.json();}).then(function(d){ if(d&&d.ok&&d.mode==='dark'){ document.documentElement.classList.add('theme-dark'); } else { document.documentElement.classList.remove('theme-dark'); } }).catch(function(){}); }
  }catch(_){ }
})();
</script>
