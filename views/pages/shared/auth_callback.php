<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// TODO: Estos valores deben ser reemplazados por los reales obtenidos al registrar el cliente en el IdP.
$clientId = '29f68cfc376872bdd70d'; 
$clientSecret = '52b66a6e8ae852690e10374304a47ccb82574e67';
$idpUrl = 'https://pruebas.mediosconvalor.com';

// Validar estado para prevenir CSRF
if (empty($_GET['state']) || empty($_SESSION['oauth_state']) || $_GET['state'] !== $_SESSION['oauth_state']) {
    die('Error: Estado de autenticación inválido (posible ataque CSRF).');
}

if (isset($_GET['error'])) {
    die('Error devuelto por el proveedor de identidad: ' . htmlspecialchars($_GET['error']));
}

if (empty($_GET['code'])) {
    die('Error: No se recibió código de autorización.');
}

$code = $_GET['code'];

// Construir Redirect URI (debe coincidir exactamente con la enviada en el login)
$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
// Forzar HTTPS si estamos en el dominio de producción, ya que el IdP espera https
if (strpos($_SERVER['HTTP_HOST'], 'clientes.mediosconvalor.com') !== false) {
    $protocol = 'https';
}
$redirectUri = $protocol . "://" . $_SERVER['HTTP_HOST'] . "/auth/callback";

// Intercambiar código por token de acceso
$tokenUrl = $idpUrl . '/token';
$postData = [
    'grant_type' => 'authorization_code',
    'code' => $code,
    'redirect_uri' => $redirectUri,
    'client_id' => $clientId,
    'client_secret' => $clientSecret
];

$ch = curl_init($tokenUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
// Deshabilitar verificación SSL si es entorno de pruebas y no tiene certificado válido
// curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$data = json_decode($response, true);

if ($httpCode !== 200 || !isset($data['access_token'])) {
    // Si falla, mostrar error detallado
    echo '<h1>Error en Token Exchange</h1>';
    echo '<p>HTTP Code: ' . $httpCode . '</p>';
    echo '<p>Response Body: ' . htmlspecialchars($response) . '</p>';
    echo '<p>Curl Error: ' . curl_error($ch) . '</p>';
    die('Error obteniendo token: ' . ($data['error'] ?? 'Respuesta inválida') . ' - ' . ($data['message'] ?? ''));
}

$accessToken = $data['access_token'];

// Obtener información del usuario
$userInfoUrl = $idpUrl . '/userinfo';
$ch = curl_init($userInfoUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $accessToken
]);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$userData = json_decode($response, true);

if ($httpCode !== 200 || !isset($userData['sub'])) {
    die('Error obteniendo datos de usuario: ' . ($userData['error'] ?? 'Respuesta inválida'));
}

// Iniciar sesión localmente
// Usamos el email como identificador de usuario local
$email = $userData['email'];

// Incluir librería de base de datos para crear tokens de sesión
require_once dirname(__DIR__, 3) . '/lib/db.php';

// Verificar si el administrador ya existe en la base de datos
$admin = findAdminByEmail($email);
if (!$admin) {
    // Si no existe, crearlo automáticamente
    $name = $userData['name'] ?? explode('@', $email)[0];
    $photo = $userData['picture'] ?? ''; // Intentar obtener foto si el IdP la provee
    // Generar un hash de contraseña aleatorio e inutilizable (ya que entran por MCV)
    $dummyHash = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);
    
    // Crear el administrador
    createAdmin($email, $name, $dummyHash, 'MCV Accounts', $photo);
}

// Configurar sesión como Administrador (Login con MCV = Login con Correo)
$_SESSION['user'] = $email;
$_SESSION['client_name'] = $userData['name'] ?? 'Admin';
$_SESSION['folder_url'] = null;
$_SESSION['is_admin'] = true;

// Verificar si es super admin
$superAdmins = ['rsaucedo@mediosconvalor.com', 'aguzman@mediosconvalor.com', 'sistemas@mediosconvalor.com'];
$_SESSION['is_super_admin'] = in_array(strtolower($email), $superAdmins, true);

// Crear token de sesión persistente
$t = createSessionToken('admin', $email, 30, 'light');
if ($t) {
    setcookie('mcv_token', $t, [
        'expires' => time() + 2592000,
        'path' => '/',
        'secure' => isset($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
}

$_SESSION['mcv_uid'] = $userData['sub'];
$_SESSION['mcv_token'] = $accessToken;

header('Location: /admin');
exit;
