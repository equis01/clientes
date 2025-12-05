<div id="adminModalValid" class="modal" style="display:none">
  <div class="modal-content">
    <button type="button" class="modal-close" id="adminCloseValid" aria-label="Cerrar">×</button>
    <div class="modal-title">¿Se envió correo con claves?</div>
    <div class="modal-body">
      Selecciona <strong>Sí</strong> solo si tú ya enviaste manualmente las claves.
      Si lo dejas en <strong>No</strong> (o desactivado), el sistema enviará automáticamente al cliente un correo desde una cuenta sin respuestas, con sus claves y acceso.
    </div>
    <div class="actions"><button type="button" class="btn" id="adminOkValid">Entendido</button></div>
  </div>
</div>
<div id="adminModalPortal" class="modal" style="display:none">
  <div class="modal-content">
    <button type="button" class="modal-close" id="adminClosePortal" aria-label="Cerrar">×</button>
    <div class="modal-title">Acceso a portal</div>
    <div class="modal-body">
      Controla si el cliente puede entrar al portal.<br>
      Coloca <strong>No</strong> si el cliente no ha pagado, si ya no se le brinda el servicio, o si aún no hay suficiente información (por ejemplo, antes del primer servicio). Usa <strong>Sí</strong> cuando esté listo para ver su información.
      Para modificarlo, tienes que "Editar" el cliente y cambiar la opción.
    </div>
    <div class="actions"><button type="button" class="btn" id="adminOkPortal">Entendido</button></div>
  </div>
</div>
<div id="adminModalAlias" class="modal" style="display:none">
  <div class="modal-content">
    <button type="button" class="modal-close" id="adminCloseAlias" aria-label="Cerrar">×</button>
    <div class="modal-title">Alias del cliente</div>
    <div class="modal-body">
      El alias es como se reconoce al cliente en la BD Relleno. Si tiene varios puntos, usa la razón social seguida de la sucursal, por ejemplo: <strong>MEDIOS CON VALOR QRO</strong>, <strong>MEDIOS CON VALOR MTY</strong>.
    </div>
    <div class="actions"><button type="button" class="btn" id="adminOkAlias">Entendido</button></div>
  </div>
</div>
<div id="adminModalUsername" class="modal" style="display:none">
  <div class="modal-content">
    <button type="button" class="modal-close" id="adminCloseUsername" aria-label="Cerrar">×</button>
    <div class="modal-title">Cómo se genera el usuario</div>
    <div class="modal-body">
      El usuario se sugiere automáticamente combinando:
      <ul>
        <li>Inicial de sucursal: Q (Querétaro), A (Aguascalientes), M (Monterrey)</li>
        <li>Inicial del alias</li>
        <li>Segunda palabra del alias (o la primera si solo hay una)</li>
        <li># Aleatorio</li>
      </ul>
      Ejemplo: alias <strong>Medios Con Valor</strong>, sucursal <strong>Querétaro</strong>, # aleatorio <strong>165</strong> ⇒ <strong>QMCon165</strong>.
      Si necesitas editar usuarios de clientes nuevos, contácta a <strong>Eduardo</strong>.
    </div>
    <div class="actions"><button type="button" class="btn" id="adminOkUsername">Entendido</button></div>
  </div>
</div>
<div id="adminModalEmails" class="modal" style="display:none">
  <div class="modal-content">
    <button type="button" class="modal-close" id="adminCloseEmails" aria-label="Cerrar">×</button>
    <div class="modal-title">Correos electrónicos</div>
    <div class="modal-body">
      Puedes capturar uno o varios correos separados por comas. Ejemplos: <strong>cliente@dominio.com</strong> o <strong>finanzas@empresa.com, contacto@empresa.com</strong>.
    </div>
    <div class="actions"><button type="button" class="btn" id="adminOkEmails">Entendido</button></div>
  </div>
</div>
<script>
(function(){
  function bind(id){
    var m=document.getElementById(id);
    if(!m) return;
    var close=m.querySelector('.modal-close');
    var ok=m.querySelector('.btn');
    function hide(){ m.style.display='none'; }
    if(close) close.addEventListener('click',hide);
    if(ok) ok.addEventListener('click',hide);
    m.addEventListener('click',function(ev){ if(!ev.target.closest('.modal-content')) hide(); });
    document.addEventListener('keydown',function(e){ if(e.key==='Escape') hide(); });
  }
  bind('adminModalValid');
  bind('adminModalPortal');
  bind('adminModalAlias');
  bind('adminModalUsername');
  bind('adminModalEmails');
  window.adminInfoOpen=function(which){ var map={valid:'adminModalValid',portal:'adminModalPortal',alias:'adminModalAlias',username:'adminModalUsername'}; var m=document.getElementById(map[which]||''); if(m){ m.style.display='flex'; } };
  window.adminInfoOpen=function(which){ var map={valid:'adminModalValid',portal:'adminModalPortal',alias:'adminModalAlias',username:'adminModalUsername',emails:'adminModalEmails'}; var m=document.getElementById(map[which]||''); if(m){ m.style.display='flex'; } };
})();
</script>
