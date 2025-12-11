<?php
?>
<footer>
  <div class="container">
    <div>© Medios Con Valor | Developed by: <a id="driveLink" href="https://evazquez.me" target="_blank">Eduardo Vázquez</a></div>
    <div>
      <button id="footerThemeToggle" class="theme-toggle" aria-label="Cambiar tema" title="Cambiar tema">
        <svg class="icon-sun" viewBox="0 0 24 24" aria-hidden="true">
          <circle cx="12" cy="12" r="4" fill="none"></circle>
          <path d="M12 2v3M12 19v3M2 12h3M19 12h3M4.9 4.9l2.1 2.1M17 17l2.1 2.1M4.9 19.1L7 17M17 7l2.1-2.1" fill="none"></path>
        </svg>
        <svg class="icon-moon" viewBox="0 0 24 24" aria-hidden="true">
          <path d="M21 12.5A8.5 8.5 0 1 1 11.5 3a6.5 6.5 0 0 0 9.5 9.5Z" fill="none"></path>
        </svg>
      </button>
    </div>
  </div>
</footer>
<script>
document.addEventListener('DOMContentLoaded',function(){
  var nav=document.querySelector('header nav');
  var menuBtn=document.getElementById('menuToggle');
  var overlay=document.getElementById('navOverlay');
  var themeBtn=document.getElementById('footerThemeToggle');
  var navClose=document.getElementById('navClose');
  function setExpanded(){ if(menuBtn) menuBtn.setAttribute('aria-expanded', nav&&nav.classList.contains('open')?'true':'false'); }
  function closeNav(){ if(nav){ nav.classList.remove('open'); setExpanded(); } if(overlay){ overlay.classList.remove('open'); } document.body.style.overflow=''; }
  if(menuBtn && nav){
    setExpanded();
    menuBtn.addEventListener('click',function(e){ e.stopPropagation(); nav.classList.toggle('open'); var isOpen=nav.classList.contains('open'); if(overlay){ overlay.classList.toggle('open', isOpen); } document.body.style.overflow=isOpen?'hidden':''; setExpanded(); });
    if(navClose){ navClose.addEventListener('click',function(e){ e.stopPropagation(); closeNav(); }); }
    document.addEventListener('pointerdown',function(e){ if(!nav.classList.contains('open')) return; var t=e.target; if(t.closest('#menuToggle')||t.closest('header nav')) return; closeNav(); },{passive:true});
    document.addEventListener('keydown',function(e){ if(e.key==='Escape') closeNav(); });
  }
  function applyTheme(mode){ var root=document.documentElement; if(mode==='dark'){ root.classList.add('theme-dark'); } else { root.classList.remove('theme-dark'); } }
  var saved=null; try{ saved=localStorage.getItem('theme'); }catch(_){}
  var media=(window.matchMedia&&window.matchMedia('(prefers-color-scheme: dark)'));
  if(saved){ applyTheme(saved); }
  else { applyTheme(media&&media.matches?'dark':'light'); }
  if(!saved && media && media.addEventListener){ media.addEventListener('change',function(e){ if(!localStorage.getItem('theme')){ applyTheme(e.matches?'dark':'light'); } }); }
  if(themeBtn){ themeBtn.addEventListener('click',function(){ var current=document.documentElement.classList.contains('theme-dark')?'dark':'light'; var next=current==='dark'?'light':'dark'; try{ localStorage.setItem('theme',next); }catch(_){ } applyTheme(next); try{ fetch('/theme',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded','Accept':'application/json'},body:'mode='+encodeURIComponent(next)}).catch(function(){}); }catch(_){ } }); }
});
</script>
