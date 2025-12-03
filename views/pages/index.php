<?php
if(session_status()!==PHP_SESSION_ACTIVE){ session_start(); }
if(isset($_SESSION['user'])){header('Location: /portal');exit;}
require dirname(__DIR__).'/login.php';
