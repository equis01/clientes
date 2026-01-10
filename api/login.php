<?php
require_once __DIR__ . '/cors.php';
require_once __DIR__ . '/../web/config/config.php';
require_once __DIR__ . '/../web/lib/env.php';
require_once __DIR__ . '/../web/lib/db.php';
require_once __DIR__ . '/../web/lib/gas.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$username = isset($input['username']) ? trim($input['username']) : '';
$password = isset($input['password']) ? trim($input['password']) : '';

if ($username === '' || $password === '') {
    echo json_encode(['ok' => false, 'error' => 'Faltan datos']);
    exit;
}

// 1. Intentar login como ADMIN
$isEmail = strpos($username, '@') !== false;
if ($isEmail) {
    $email = strtolower($username);
    $isAdminDomain = function_exists('str_ends_with') ? str_ends_with($email, ADMIN_DOMAIN) : (substr($email, -strlen(ADMIN_DOMAIN)) === ADMIN_DOMAIN);
    
    if ($isAdminDomain) {
        ensureSchema();
        if(!hasAnyAdmins()){ migrateAdminsFromJson(); }
        $ok=false; $name='Admin';
        $a=findAdminByEmail($email);
        if(is_array($a)){
            $hash=$a['password_hash']??''; $name=$a['name']??$name;
            if($hash){ $ok=password_verify($password,$hash) || trim($password)===trim($hash); }
        }
        
        if ($ok) {
            $t=createSessionToken('admin',$email,30,'light'); 
            echo json_encode([
                'ok' => true,
                'token' => $t,
                'admin' => true,
                'user' => $email,
                'name' => $name
            ]);
            exit;
        }
    }
}

// 2. Intentar login como USUARIO (Q o Otros)
$isQ = strtoupper(substr($username, 0, 1)) === 'Q';

if ($isQ) {
    $base = env('GAS_USERS_Q_URL');
    if ($base) {
        // Fetch user data from GAS
        $url = $base . '?action=users&username=' . urlencode($username);
        $r = http_get_raw($url, false);
        if ($r['raw'] === false || $r['raw'] === null) { $r = http_get_raw($url, true); }
        
        if ($r['raw'] === false || $r['raw'] === null) {
            echo json_encode(['ok' => false, 'error' => 'Error de conexión con GAS']);
            exit;
        }
        
        $data = json_decode($r['raw'], true);
        if (!is_array($data) || empty($data['ok'])) {
             echo json_encode(['ok' => false, 'error' => 'Respuesta inválida de GAS']);
             exit;
        }
        
        $users = is_array($data['users']) ? $data['users'] : [];
        $u = isset($users[$username]) ? $users[$username] : null;
        
        if (!$u) {
            echo json_encode(['ok' => false, 'error' => 'Usuario no encontrado']);
            exit;
        }
        
        // Verify Status
        if (intval($u['valid']) !== 1) {
            echo json_encode(['ok' => false, 'error' => 'Usuario inactivo']);
            exit;
        }
        if (isset($u['portal_enabled']) && intval($u['portal_enabled']) !== 1) {
            echo json_encode(['ok' => false, 'error' => 'Portal restringido']);
            exit;
        }
        
        // Verify Password
        $hash = isset($u['password_hash']) ? $u['password_hash'] : '';
        $ok = false;
        if (is_string($hash) && strlen($hash) > 0) {
            if (strpos($hash, '$2') === 0) { $ok = password_verify($password, $hash); }
            else { $ok = (trim($password) === trim($hash)); }
        }
        
        if (!$ok) {
            echo json_encode(['ok' => false, 'error' => 'Credenciales inválidas']);
            exit;
        }
        
        // Create Token
        $t = createSessionToken('user', $username, 30, 'light');
        
        echo json_encode([
            'ok' => true,
            'token' => $t,
            'admin' => false,
            'user' => $username,
            'name' => (isset($u['alias']) && $u['alias'] !== '') ? $u['alias'] : $username,
            'folder_url' => isset($u['drive_url']) ? $u['drive_url'] : null
        ]);
        exit;
        
    } else {
        echo json_encode(['ok' => false, 'error' => 'GAS Q URL no configurada']);
        exit;
    }
} else {
    // Other Users
    $base = env('GAS_USERS_OTHER_URL');
    if ($base) {
        $body = http_build_query(['username' => $username, 'password' => $password]);
        $r = http_post_raw($base, $body, false);
        if ($r['raw'] === false || $r['raw'] === null) { $r = http_post_raw($base, $body, true); }
        
        if ($r['raw'] === false || $r['raw'] === null) {
             echo json_encode(['ok' => false, 'error' => 'Error de conexión con GAS Other']);
             exit;
        }
        
        $text = trim((string)$r['raw']);
        if ($text === 'null' || $text === '') {
             echo json_encode(['ok' => false, 'error' => 'Credenciales inválidas']);
             exit;
        }
        
        // Success
        $t = createSessionToken('user', $username, 30, 'light');
        echo json_encode([
            'ok' => true,
            'token' => $t,
            'admin' => false,
            'user' => $username,
            'name' => $username,
            'folder_url' => $text
        ]);
        exit;
    } else {
         echo json_encode(['ok' => false, 'error' => 'GAS Other URL no configurada']);
         exit;
    }
}
