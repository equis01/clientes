document.addEventListener('DOMContentLoaded',function(){
  var current=null;
  var submitBtn=document.getElementById('submit');
  var forgotLink=document.getElementById('forgot-password');
  var clientLink=document.getElementById('client-login');

  function setSubmitting(on){
    if(on){ submitBtn.value='INGRESANDO…'; submitBtn.disabled=true; }
    else { submitBtn.value='INGRESAR'; submitBtn.disabled=false; }
  }

  function showModal(text){
    var m=document.getElementById('adminModal');
    var b=document.getElementById('adminModalBody');
    var c=document.getElementById('adminModalClose');
    if(m&&b){ b.textContent=String(text||''); m.style.display='flex'; if(c){ c.onclick=function(){ m.style.display='none'; }; } }
    else { alert(String(text||'')); }
  }

  submitBtn.addEventListener('click',function(e){
    e.preventDefault();
    setSubmitting(true);
    var email=document.getElementById('username').value.trim();
    var password=document.getElementById('password').value.trim();
    var body=new URLSearchParams({admin_email:email,admin_password:password});
    fetch('/auth',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:body})
    .then(function(r){return r.json();})
    .then(function(data){
      if(data&&data.ok&&data.admin){ window.location.href='/admin'; }
      else {
        showModal((data&&data.error)||'Correo o contraseña incorrectos');
        setSubmitting(false);
      }
    })
    .catch(function(){
      showModal('Error de conexión');
      setSubmitting(false);
    });
  });

  function onEnter(e){ if(e.key==='Enter'){ e.preventDefault(); submitBtn.click(); } }
  var u=document.getElementById('username');
  var p=document.getElementById('password');
  if(u) u.addEventListener('keydown',onEnter);
  if(p) p.addEventListener('keydown',onEnter);

  if(clientLink){
    clientLink.addEventListener('click',function(e){ e.preventDefault(); window.location.href='/login'; });
  }
  if(forgotLink){
    forgotLink.addEventListener('click',function(e){ e.preventDefault(); showModal('Favor de comunicarse con sistemas@mediosconvalor.com para realizar el cambio'); });
  }

  var path=document.querySelector('path');
  function focusAnim(offset, dash){
    if(current) current.pause();
    if(!path) return;
    current=anime({
      targets:'path',
      strokeDashoffset:{value:offset,duration:700,easing:'easeOutQuart'},
      strokeDasharray:{value:dash,duration:700,easing:'easeOutQuart'}
    });
  }
  if(u){ u.addEventListener('focus',function(){ focusAnim(0,'240 1386'); }); }
  if(p){ p.addEventListener('focus',function(){ focusAnim(-336,'240 1386'); }); }
  if(submitBtn){ submitBtn.addEventListener('focus',function(){ focusAnim(-730,'530 1386'); }); }
});
