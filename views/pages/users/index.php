<?php
if(session_status()!==PHP_SESSION_ACTIVE){ session_start(); }
if(isset($_SESSION['user'])){ require __DIR__.'/portal.php'; }
else { require __DIR__.'/login.php'; }
