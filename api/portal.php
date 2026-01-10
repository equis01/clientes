<?php
require_once __DIR__ . '/cors.php';
require_once __DIR__ . '/../web/config/config.php';
require_once __DIR__ . '/../web/lib/env.php';
require_once __DIR__ . '/../web/lib/db.php';
require_once __DIR__ . '/../web/lib/gas.php';

header('Content-Type: application/json');

// Get Token from Header
$headers = getallheaders();
$authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : (isset($headers['authorization']) ? $headers['authorization'] : '');
$token = '';

if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
    $token = $matches[1];
}

if (!$token) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'No autorizado (Token faltante)']);
    exit;
}

// Validate Token
$row = findSessionToken($token);

if (!is_array($row)) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'Token inválido']);
    exit;
}

$rev = $row['revoked_at'] ?? '';
$exp = $row['expires_at'] ?? '';
$okExp = $exp !== '' && (strtotime($exp) !== false) && time() < strtotime($exp);

if ($rev || !$okExp) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'Token expirado o revocado']);
    exit;
}

// Update last seen
touchSessionToken($token);

$userId = $row['user_id'];
$userType = $row['user_type'];

if ($userType === 'admin') {
     // Admin logic (simplified for now, usually just returns admin info)
     echo json_encode([
         'ok' => true,
         'user' => [
             'email' => $userId,
             'type' => 'admin',
             'client_name' => 'Admin'
         ]
     ]);
     exit;
}

// User Logic
$u = gas_users($userId);

if ($u['ok'] && is_array($u['user'])) {
    $userData = $u['user'];
    // Filter sensitive data if needed, but GAS response seems safe
    echo json_encode([
        'ok' => true,
        'user' => [
            'username' => $userId,
            'alias' => $userData['alias'] ?? '',
            'email' => $userData['email'] ?? '',
            'drive_url' => $userData['drive_url'] ?? '',
            // Include other fields if useful
        ]
    ]);
} else {
    // If gas_users fails but token is valid, maybe user was deleted in GAS?
    // Or it's an "Other" user (which gas_users might not find if it only looks at Q sheet?)
    // gas_users checks 'Q' logic.
    // If login was "Other", gas_users might fail if it only checks Q.
    // Let's check gas_users implementation in gas.php again.
    // It calls action='users'.
    
    // If the user is not found via gas_users (e.g. "Other" type login), we might still want to return basic info if we have it.
    // But "Other" users don't have stored profiles in GAS (based on auth.php logic, they just get a URL).
    // However, the token table only stores user_id.
    // If it's an "Other" user, we don't have a way to re-fetch the Drive URL unless we stored it in the DB or session.
    // The current DB schema for session_tokens does NOT store the drive URL.
    // The web app uses $_SESSION to store 'folder_url'.
    // API is stateless.
    
    // PROBLEM: "Other" users get their Drive URL *only* during login (returned as text/plain from GAS).
    // If I don't store it, subsequent API calls can't retrieve it without re-authenticating with password (which we don't have).
    
    // Solution:
    // 1. Modify session_tokens table? No, shouldn't touch existing DB structure if possible.
    // 2. Just return what we know (user_id). The App should cache the Drive URL upon login.
    // 3. Or, the App should send the Drive URL back? No, that's insecure/weird.
    
    // Best Approach for now:
    // The App receives 'folder_url' on Login. It should store it locally (AsyncStorage).
    // The Portal endpoint verifies the token is still valid.
    // If valid, it returns "ok".
    // If it's a "Q" user, we can fetch fresh data (and updated Drive URL).
    // If it's "Other", we just return "ok" and the App uses cached URL.
    
    echo json_encode([
        'ok' => true,
        'user' => [
            'username' => $userId,
            'type' => 'user',
            'note' => 'User data not found in Q-list (possibly Other type)'
        ]
    ]);
}
