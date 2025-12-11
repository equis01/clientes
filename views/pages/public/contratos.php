<?php if(session_status()!==PHP_SESSION_ACTIVE){ session_start(); } ?>
<!DOCTYPE html>
<html lang="es">
<head>
  <?php $pageTitle='Contratos'; include dirname(__DIR__).'/views/layout/head.php'; ?>
  <style>
    .page{display:flex;min-height:100vh;flex-direction:column}
    .container{max-width:980px;margin:0 auto;padding:20px 20px 84px}
    .header-min{display:flex;align-items:center;gap:16px;padding:16px 0}
    .header-min img{width:160px;height:auto}
    .form-wrapper{height:calc(100vh - 220px)}
    .form-wrapper form{height:100%}
    form fieldset{border:1px solid #cfd7e6;border-radius:10px;margin:20px 0;padding:16px;height:100%;overflow:auto}
    form legend{font-weight:600;color:#009eff}
    label{display:block;margin:10px 0}
    input,select{width:100%;padding:10px;border:1px solid #cfd7e6;border-radius:8px}
    .nav-buttons{display:flex;gap:10px;margin-top:10px}
    .btn{background:#009eff;color:#fff;border:0;border-radius:8px;padding:10px 16px;cursor:pointer}
    .btn.secondary{background:#535a6b;color:#fff}
    .steps{display:flex;gap:8px;margin:10px 0}
    .step{flex:1;height:4px;background:#cfd7e6;border-radius:4px}
    .step.active{background:#00dd2a}
    .captcha{display:flex;align-items:center;gap:12px;margin-top:12px}
    .modal{position:fixed;inset:0;display:none;align-items:center;justify-content:center;background:rgba(0,0,0,.35);z-index:50}
    .modal-content{background:#fff;color:#222;padding:16px;border-radius:12px;max-width:560px;width:92%}
    .modal-title{font-weight:700;margin-bottom:10px;color:#009eff}
    .floating-whatsapp{position:fixed;right:16px;bottom:16px;background:#25D366;color:#fff;padding:12px 16px;border-radius:30px;text-decoration:none;box-shadow:0 4px 12px rgba(0,0,0,.2);font-weight:600;z-index:60}
    .top-actions{display:flex;gap:8px;justify-content:flex-end}
  </style>
</head>
<body>
  <div class="page">
    <div class="container">
      <div class="header-min">
        <img src="https://mediosconvalor.github.io/mcv/img/logo/logo.png" alt="Medios con Valor">
        <h1 style="margin:0;font-size:22px">Solicitud de contrato</h1>
      </div>
      <div class="steps"><div class="step active" id="st1"></div><div class="step" id="st2"></div><div class="step" id="st3"></div><div class="step" id="st4"></div><div class="step" id="st5"></div></div>
      <div class="top-actions"><button id="borrarBorrador" class="btn secondary" type="button">Borrar borrador</button></div>
      <div class="form-wrapper">
      <form id="formContratos" autocomplete="off">
        <input type="hidden" name="sucursal" id="sucursalHidden" value="">
        <input type="hidden" name="tipo_persona" id="tipoPersonaHidden" value="">
        <fieldset data-step="1">
          <legend>Datos generales</legend>
          <label>Razón social:<input name="razon_social" required></label>
          <label>Nombre comercial:<input name="nombre_comercial"></label>
          <label>RFC:<input name="rfc"></label>
          <label>Régimen fiscal:
            <select name="regimen_fiscal" id="regimenSelect"></select>
          </label>
          <label>Dirección fiscal:<input name="direccion_fiscal"></label>
          <label>Estado fiscal:
            <select name="estado_fiscal" id="estadoSelect"></select>
          </label>
          <label>Municipio fiscal:
            <select name="municipio_fiscal" id="municipioSelect"></select>
          </label>
          <label>Dirección de servicio:<input name="direccion_servicio"></label>
          <label>Teléfonos:<input name="telefonos"></label>
          <div class="nav-buttons"><button type="button" class="btn" id="next1">Siguiente</button></div>
        </fieldset>
        <fieldset data-step="2" style="display:none">
          <legend>Recolección</legend>
          <label>Nombre recolección:<input name="nombre_recoleccion"></label>
          <label>Teléfono recolección:<input name="telefono_recoleccion"></label>
          <label>Correo recolección:<input name="correo_recoleccion" type="email"></label>
          <div class="nav-buttons"><button type="button" class="btn secondary" id="prev2">Anterior</button><button type="button" class="btn" id="next2">Siguiente</button></div>
        </fieldset>
        <fieldset data-step="3" style="display:none">
          <legend>Compras</legend>
          <label>Nombre compras:<input name="nombre_compras"></label>
          <label>Teléfono compras:<input name="telefono_compras"><small style="display:block;margin-top:6px"><a href="#" id="copyComprasTel">Igual que anterior</a></small></label>
          <label>Correo compras:<input name="correo_compras" type="email"><small style="display:block;margin-top:6px"><a href="#" id="copyComprasCorreo">Igual que anterior</a></small></label>
          <div class="nav-buttons"><button type="button" class="btn secondary" id="prev3">Anterior</button><button type="button" class="btn" id="next3">Siguiente</button></div>
        </fieldset>
        <fieldset data-step="4" style="display:none">
          <legend>Pagos</legend>
          <label>Nombre pagos:<input name="nombre_pagos"></label>
          <label>Teléfono pagos:<input name="telefono_pagos"><small style="display:block;margin-top:6px"><a href="#" id="copyPagosTel">Igual que anterior</a></small></label>
          <label>Correo pagos:<input name="correo_pagos" type="email"><small style="display:block;margin-top:6px"><a href="#" id="copyPagosCorreo">Igual que anterior</a></small></label>
          <label>Banco:
            <select name="banco" id="bancoSelect"></select>
          </label>
          <label id="bancoOtroLabel" style="display:none">Banco (otro):<input name="banco_otro" id="bancoOtroInput"></label>
          <label>Últimos dígitos cuenta:<input name="ultimos_digitos_cuenta"></label>
          <label>CLABE:<input name="clabe"></label>
          <div class="nav-buttons"><button type="button" class="btn secondary" id="prev4">Anterior</button><button type="button" class="btn" id="next4">Siguiente</button></div>
        </fieldset>
        <fieldset data-step="5" style="display:none">
          <legend>Representante / Escritura</legend>
          <label>Representante legal:<input name="representante_legal" required></label>
          <label>Escritura pública:<input name="escritura_publica"></label>
          <label>Tipo/Volumen/Tomo:<input name="tipo_volumen_tomo"></label>
          <label>Número Volumen/Tomo:<input name="numero_volumen_tomo"></label>
          <label>Fecha:<input name="fecha" type="date"></label>
          <label>Notaría pública:<input name="notaria_publica"></label>
          <label>Notario titular:<input name="notario_titular"></label>
          <label>Registro inscripción:<input name="registro_inscripcion"></label>
          <label>Estado de registro notarial:<select name="estado_fiscal_secundario" id="estadoSecSelect"></select></label>
          <label>Municipio de registro notarial:<select name="municipio_fiscal_secundario" id="municipioSecSelect"></select></label>
          <div class="captcha"><span id="captchaText"></span><input type="text" id="captchaInput" placeholder="Resultado" style="max-width:140px"></div>
          <div class="nav-buttons"><button type="button" class="btn secondary" id="prev5">Anterior</button><button type="submit" class="btn" id="submitBtn">Enviar</button></div>
        </fieldset>
      </form>
      </div>
    </div>
    <?php include dirname(__DIR__).'/views/layout/footer.php'; ?>
  </div>
  <div id="introModal" class="modal"><div class="modal-content"><div class="modal-title">Bienvenido</div><div class="modal-body">
    <p>Este formulario inicia tu proceso de contrato con Medios Con Valor.</p>
    <label>Tipo de persona:
      <select id="introTipoPersona">
        <option value="moral">Persona moral</option>
        <option value="fisica">Persona física</option>
      </select>
    </label>
    <label>Sucursal:
      <select id="introSucursal">
        <option value="mty">MCV Monterrey (MTY)</option>
        <option value="ags">MCV Aguascalientes (AGS)</option>
        <option value="qro">MCV Querétaro (QRO)</option>
      </select>
    </label>
    <p>Podrás adjuntar documentación cuando tu solicitud sea autorizada.</p>
  </div><div class="actions"><button type="button" class="btn" id="introClose">Continuar</button></div></div></div>
  <div id="successModal" class="modal"><div class="modal-content"><div class="modal-title">Envío exitoso</div><div class="modal-body" id="successBody"></div><div class="actions"><button type="button" class="btn" id="successClose">Aceptar</button></div></div></div>
  <div id="errorModal" class="modal"><div class="modal-content"><div class="modal-title">Error</div><div class="modal-body" id="errorBody"></div><div class="actions"><button type="button" class="btn" id="errorClose">Cerrar</button></div></div></div>
  <a id="whatsappFloat" href="#" class="floating-whatsapp" target="_blank" title="Contáctanos por WhatsApp">WhatsApp</a>
  <script src="/scripts/js_Anterior/contratos.js"></script>
  <script>
  (function(){
    var startTs=Date.now();
    function q(s){return document.querySelector(s)}
    function qa(s){return Array.prototype.slice.call(document.querySelectorAll(s));}
    function showStep(n){var f=document.querySelectorAll('fieldset[data-step]');f.forEach(function(el){el.style.display=(parseInt(el.getAttribute('data-step'))===n)?'block':'none'});[1,2,3,4,5].forEach(function(i){var e=q('#st'+i); if(e){ e.classList.toggle('active', i<=n); }});}
    function valEmail(v){return !v||/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v)}
    function collect(){var fd=new FormData(q('#formContratos'));var o={};fd.forEach(function(v,k){o[k]=v});return o}
    function modal(id,text){var m=q('#'+id);var b=q('#'+id.replace('Modal','Body'));if(m){ if(b){ b.textContent=String(text||''); } m.style.display='flex'; var btn=q('#'+id.replace('Modal','Close')); if(btn){ btn.onclick=function(){ m.style.display='none'; }; } }
    }
    function makeCaptcha(){var a=Math.floor(Math.random()*8)+2;var b=Math.floor(Math.random()*8)+2;var t=a+' + '+b+' = ?';var exp=a+b;var el=q('#captchaText'); if(el){ el.textContent=t; el.setAttribute('data-exp', String(exp)); }}
    function initSelects(){var rs=q('#regimenSelect'); if(rs && window.regimenesMX){ var opts=['<option value="">Selecciona…</option>'].concat((regimenesMX.regimenes||[]).map(function(r){return '<option>'+r+'</option>'})); rs.innerHTML=opts.join(''); }
      // sucursal se define en el modal inicial
      var es=q('#estadoSelect'); var ms=q('#municipioSelect'); if(es && window.estadosMunicipiosMX){ var estados=(estadosMunicipiosMX.estados||[]); var ehtml=['<option value="">Selecciona…</option>'].concat(estados.map(function(e){return '<option>'+e+'</option>'})).join(''); es.innerHTML=ehtml; es.onchange=function(){ var sel=es.value; var municipios=(window.estadosMunicipiosMX.municipios||{}); var list=municipios[sel]||[]; var mhtml=['<option value="">Selecciona…</option>'].concat(list.map(function(m){return '<option>'+m+'</option>'})).join(''); if(ms){ ms.innerHTML=mhtml; } } }
      var es2=q('#estadoSecSelect'); var ms2=q('#municipioSecSelect'); if(es2 && window.estadosMunicipiosMX){ es2.innerHTML=es.innerHTML; es2.onchange=function(){ var sel=es2.value; var municipios=(window.estadosMunicipiosMX.municipios||{}); var list=municipios[sel]||[]; var mhtml=['<option value="">Selecciona…</option>'].concat(list.map(function(m){return '<option>'+m+'</option>'})).join(''); if(ms2){ ms2.innerHTML=mhtml; } } }
      var bs=q('#bancoSelect'); if(bs && window.bancosMX){ var list=(bancosMX.instituciones||[]); var bhtml=['<option value="">Selecciona…</option>'].concat(list.map(function(b){return '<option>'+b+'</option>'})).join(''); bs.innerHTML=bhtml; bs.onchange=function(){ var v=bs.value; var l=q('#bancoOtroLabel'); if(l){ l.style.display=(v==='Otros'||v==='Otro')?'block':'none'; } } }
    }
    function updateWhatsapp(){ var s=(q('#sucursalSelect')&&q('#sucursalSelect').value)||q('#sucursalHidden').value||''; var map={mty:'8184689400',ags:'4492832288',qro:'4461385019',default:'4423565508'}; var num=map[s]||map.default; var a=q('#whatsappFloat'); if(a){ a.href='https://wa.me/'+num; a.textContent='WhatsApp ('+num+')'; } }
    function handleNav(){q('#next1').onclick=function(){showStep(2)}; q('#prev2').onclick=function(){showStep(1)}; q('#next2').onclick=function(){showStep(3)}; q('#prev3').onclick=function(){showStep(2)}; q('#next3').onclick=function(){showStep(4)}; q('#prev4').onclick=function(){showStep(3)}; q('#next4').onclick=function(){showStep(5)}; q('#prev5').onclick=function(){showStep(4)}; }
    function applyPersona(){ var t=(q('#tipoPersonaHidden').value||'moral'); var isFisica=(t==='fisica'); var s5=q('fieldset[data-step="5"]'); var navPagos=q('#navPagos'); if(isFisica){ if(s5){ s5.style.display='none'; } if(navPagos){ q('#next4').style.display='none'; var btnFis=document.createElement('button'); btnFis.type='submit'; btnFis.className='btn'; btnFis.id='btnEnviarFisica'; btnFis.textContent='Enviar'; navPagos.appendChild(btnFis); } } else { if(s5){ s5.style.display='block'; } if(navPagos){ var btnFis=q('#btnEnviarFisica'); if(btnFis){ btnFis.remove(); } q('#next4').style.display='inline-block'; } }
    }
    function bindIgual(){ var cTel=q('#copyComprasTel'); var cCor=q('#copyComprasCorreo'); var pTel=q('#copyPagosTel'); var pCor=q('#copyPagosCorreo'); if(cTel){ cTel.addEventListener('click',function(e){ e.preventDefault(); var v=q('input[name="telefono_recoleccion"]').value||''; q('input[name="telefono_compras"]').value=v; }); } if(cCor){ cCor.addEventListener('click',function(e){ e.preventDefault(); var v=q('input[name="correo_recoleccion"]').value||''; q('input[name="correo_compras"]').value=v; }); } if(pTel){ pTel.addEventListener('click',function(e){ e.preventDefault(); var v=q('input[name="telefono_compras"]').value||''; q('input[name="telefono_pagos"]').value=v; }); } if(pCor){ pCor.addEventListener('click',function(e){ e.preventDefault(); var v=q('input[name="correo_compras"]').value||''; q('input[name="correo_pagos"]').value=v; }); } }
    function cascadeFill(){ var rTel=q('input[name="telefono_recoleccion"]'); var rCor=q('input[name="correo_recoleccion"]'); var cTel=q('input[name="telefono_compras"]'); var cCor=q('input[name="correo_compras"]'); var pTel=q('input[name="telefono_pagos"]'); var pCor=q('input[name="correo_pagos"]'); if(rTel){ rTel.addEventListener('blur',function(){ if(cTel && !cTel.value){ cTel.value=rTel.value; } }); } if(rCor){ rCor.addEventListener('blur',function(){ if(cCor && !cCor.value){ cCor.value=rCor.value; } }); } if(cTel){ cTel.addEventListener('blur',function(){ if(pTel && !pTel.value){ pTel.value=cTel.value; } }); } if(cCor){ cCor.addEventListener('blur',function(){ if(pCor && !pCor.value){ pCor.value=cCor.value; } }); }
    }
    function saveCache(){ var data=collect(); try{ localStorage.setItem('contracts_cache_inprogress', JSON.stringify(data)); }catch(_){} }
    function loadCache(){ var raw=null; try{ raw=localStorage.getItem('contracts_cache_inprogress'); }catch(_){} var lastRaw=null; try{ lastRaw=localStorage.getItem('contracts_cache_last_answers'); }catch(_){} var obj=raw?JSON.parse(raw): (lastRaw?JSON.parse(lastRaw):null); if(obj){ Object.keys(obj).forEach(function(k){ var el=q('[name="'+k+'"]'); if(el && !el.value){ el.value=obj[k]; } }); }
    }
    function submit(){var form=q('#formContratos'); form.addEventListener('submit',function(ev){ev.preventDefault(); var data=collect(); if(!data.razon_social){ modal('errorModal','Razón social es requerida'); return; } if(!valEmail(data.correo_recoleccion)||!valEmail(data.correo_compras)||!valEmail(data.correo_pagos)){ modal('errorModal','Verifica los correos'); return; } var cap=q('#captchaText'); var ans=(q('#captchaInput').value||'').trim(); var exp=cap?cap.getAttribute('data-exp'):'0'; if(ans!==exp){ modal('errorModal','Captcha incorrecto'); return; } var dwell=Date.now()-startTs; if(dwell<8000){ modal('errorModal','Completa el formulario antes de enviar'); return; }
        var payload={
          tipo:'contrato',
          sucursal:data.sucursal||'',
          datos:data,
          meta:{user_agent:navigator.userAgent||'', dwell_ms:dwell}
        };
        fetch('/contratos/submit',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(payload)})
          .then(function(r){return r.json();})
          .then(function(d){ if(d&&d.ok){ try{ localStorage.setItem('contracts_cache_last_answers', JSON.stringify(data)); localStorage.removeItem('contracts_cache_inprogress'); }catch(_){} modal('successModal','Tu solicitud fue enviada. Recibirás un correo para adjuntar documentos cuando sea autorizada.'); } else { modal('errorModal', (d&&d.error)||'Error al enviar'); } })
          .catch(function(){ modal('errorModal','Error de conexión'); });
      });
    }
    document.addEventListener('DOMContentLoaded',function(){ showStep(1); initSelects(); updateWhatsapp(); handleNav(); bindIgual(); cascadeFill(); makeCaptcha(); loadCache(); var sc=q('#successClose'); if(sc){ sc.onclick=function(){ q('#successModal').style.display='none'; } } var ec=q('#errorClose'); if(ec){ ec.onclick=function(){ q('#errorModal').style.display='none'; } }
      var im=q('#introModal'); var ic=q('#introClose'); var isuc=q('#introSucursal'); var itp=q('#introTipoPersona'); if(im){ im.style.display='flex'; if(ic){ ic.onclick=function(){ var suc=isuc?isuc.value:''; var tp=itp?itp.value:'moral'; q('#sucursalHidden').value=suc; q('#tipoPersonaHidden').value=tp; updateWhatsapp(); applyPersona(); im.style.display='none'; }; } }
      qa('input,select').forEach(function(el){ el.addEventListener('change',saveCache); el.addEventListener('keydown',function(){ setTimeout(saveCache,100); }); });
      var bb=q('#borrarBorrador'); if(bb){ bb.addEventListener('click',function(){ try{ localStorage.removeItem('contracts_cache_inprogress'); modal('successModal','Borrador eliminado'); }catch(_){ } }); }
    });
  })();
  </script>
</body>
</html>

