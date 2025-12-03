const VERSION='v1';
self.addEventListener('install',function(e){ self.skipWaiting(); });
self.addEventListener('activate',function(e){ self.clients.claim(); });
self.addEventListener('message',function(e){ var d=e&&e.data; if(d&&d.type==='SKIP_WAITING'){ self.skipWaiting(); } });
