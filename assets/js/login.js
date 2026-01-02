document.addEventListener('DOMContentLoaded',function(){
  var current=null;
  var submitBtn=document.getElementById('submit');
  var forgotLink=document.getElementById('forgot-password');
  function showModal(text){
    var m=document.getElementById('loginModal');
    var b=document.getElementById('loginModalBody');
    var c=document.getElementById('loginModalClose');
    if(m&&b){ b.textContent=String(text||''); m.style.display='flex'; if(c){ c.onclick=function(){ m.style.display='none'; }; } }
    else { alert(String(text||'')); }
  }
  submitBtn.addEventListener('click',function(e){
    e.preventDefault();
    submitBtn.value='INGRESANDO…';
    submitBtn.disabled=true;
    var username=document.getElementById('username').value.trim();
    var password=document.getElementById('password').value.trim();
    var body=new URLSearchParams({username:username,password:password});
    fetch('/auth',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:body})
    .then(function(r){
      return r.text().then(function(text){
        try {
          return JSON.parse(text);
        } catch(e) {
          throw new Error('Respuesta inválida del servidor: ' + text.substring(0, 50) + '...');
        }
      });
    })
    .then(function(data){
      if(data&&data.ok){
        var uname=document.getElementById('username').value.trim();
        if(data.admin){ window.location.href='/admin'; return; }
        window.location.href='/users';
      }else{
        var email=document.getElementById('username').value.trim();
        var pass=document.getElementById('password').value.trim();
        var bodyAdmin=new URLSearchParams({admin_email:email,admin_password:pass});
        fetch('/auth',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:bodyAdmin})
          .then(function(r2){ return r2.json(); })
          .then(function(d2){
            if(d2 && d2.ok && d2.admin){ window.location.href='/admin'; }
            else {
              if(data && data.code==='portal_disabled'){
                var m=document.getElementById('portalModal'); var c=document.getElementById('portalClose');
                if(m){ m.style.display='flex'; if(c){ c.onclick=function(){ m.style.display='none'; }; } }
                else { alert('Tu acceso al portal está restringido.'); }
              } else {
                showModal((data&&data.error)||'Nombre de usuario o contraseña incorrectos');
              }
              submitBtn.value='INGRESAR'; submitBtn.disabled=false;
            }
          })
          .catch(function(e2){ showModal('Error de conexión (Admin): ' + e2.message); submitBtn.value='INGRESAR'; submitBtn.disabled=false; });
      }
    })
    .catch(function(err){
      showModal('Error: ' + err.message);
      submitBtn.value='INGRESAR';
      submitBtn.disabled=false;
    });
  });
  function onEnter(e){ if(e.key==='Enter'){ e.preventDefault(); submitBtn.click(); } }
  document.getElementById('username').addEventListener('keydown',onEnter);
  document.getElementById('password').addEventListener('keydown',onEnter);
  

  // Manejar resultado de redirect (para navegadores con bloqueo de popup/cookies)
  
  forgotLink.addEventListener('click',function(e){
    e.preventDefault();
    showModal('Favor de comunicarse con sistemas@mediosconvalor.com para realizar el cambio');
  });
  document.querySelector('#username').addEventListener('focus',function(){
    if(current)current.pause();
    current=anime({targets:'path',strokeDashoffset:{value:0,duration:700,easing:'easeOutQuart'},strokeDasharray:{value:'240 1386',duration:700,easing:'easeOutQuart'}});
  });
  document.querySelector('#password').addEventListener('focus',function(){
    if(current)current.pause();
    current=anime({targets:'path',strokeDashoffset:{value:-336,duration:700,easing:'easeOutQuart'},strokeDasharray:{value:'240 1386',duration:700,easing:'easeOutQuart'}});
  });
  document.querySelector('#submit').addEventListener('focus',function(){
    if(current)current.pause();
    current=anime({targets:'path',strokeDashoffset:{value:-730,duration:700,easing:'easeOutQuart'},strokeDasharray:{value:'530 1386',duration:700,easing:'easeOutQuart'}});
  });
});
