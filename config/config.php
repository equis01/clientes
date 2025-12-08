<?php
require_once dirname(__DIR__) . '/lib/env.php';
define('DB_DRIVER', strtolower(env('DB_DRIVER','mysql')));
define('SQLITE_PATH', env('SQLITE_PATH', dirname(__DIR__) . '/data/clients.sqlite'));
define('REQUIRE_Q_PREFIX', true);
define('ADMIN_DOMAIN','@mediosconvalor.com');
