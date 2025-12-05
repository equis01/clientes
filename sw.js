self.addEventListener('install',function(){});
self.addEventListener('activate',function(e){ e.waitUntil(self.clients.claim()); });
self.addEventListener('message',function(e){ if(e && e.data==='SKIP_WAITING'){ self.skipWaiting(); } });
