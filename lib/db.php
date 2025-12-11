<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/env.php';

$GLOBALS['DB_FALLBACK'] = false;

function db(): ?PDO {
  static $pdo = null;
  if ($pdo !== null) return $pdo;
  if (DB_DRIVER === 'mysql') {
    try {
      $host = env('DB_HOST');
      $port = intval(env('DB_PORT',3306));
      $name = env('DB_NAME');
      $user = env('DB_USER');
      $pass = env('DB_PASS');
      $charset = env('DB_CHARSET','utf8mb4');
      if(!$host||!$name||!$user){ throw new RuntimeException('DB no configurada'); }
      $dsn = 'mysql:host='.$host.';port='.$port.';dbname='.$name.';charset='.$charset;
      $pdo = new PDO($dsn,$user,$pass);
      $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      return $pdo;
    } catch (Throwable $e) {
      $GLOBALS['DB_FALLBACK'] = true;
      return null;
    }
  } else if (DB_DRIVER === 'sqlite') {
    try {
      $dsn = 'sqlite:' . SQLITE_PATH;
      $pdo = new PDO($dsn);
      $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      return $pdo;
    } catch (Throwable $e) {
      $GLOBALS['DB_FALLBACK'] = true;
      return null;
    }
  }
  $GLOBALS['DB_FALLBACK'] = true;
  return null;
}

function ensureSchema(): void {
  $pdo = db();
  if ($GLOBALS['DB_FALLBACK'] || $pdo === null) { return; }
  if (DB_DRIVER === 'mysql') {
    $pdo->exec('CREATE TABLE IF NOT EXISTS admins (
      email VARCHAR(255) PRIMARY KEY,
      name VARCHAR(255) NOT NULL,
      password_hash VARCHAR(255) NOT NULL,
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      invitedBy VARCHAR(255),
      photo VARCHAR(255)
    )');
    $pdo->exec('CREATE TABLE IF NOT EXISTS invites (
      token VARCHAR(64) PRIMARY KEY,
      email VARCHAR(255) NOT NULL,
      name VARCHAR(255),
      expiresAt DATETIME NOT NULL,
      invitedBy VARCHAR(255) NOT NULL,
      usedAt DATETIME NULL,
      createdAt DATETIME DEFAULT CURRENT_TIMESTAMP
    )');
    $pdo->exec('CREATE TABLE IF NOT EXISTS session_tokens (
      token VARCHAR(64) PRIMARY KEY,
      user_type ENUM("user","admin") NOT NULL,
      user_id VARCHAR(255) NOT NULL,
      theme VARCHAR(10) DEFAULT "light",
      created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
      last_seen DATETIME DEFAULT CURRENT_TIMESTAMP,
      expires_at DATETIME NOT NULL,
      revoked_at DATETIME NULL
    )');
  } else {
    $pdo->exec('CREATE TABLE IF NOT EXISTS admins (
      email TEXT PRIMARY KEY,
      name TEXT NOT NULL,
      password_hash TEXT NOT NULL,
      created_at TEXT DEFAULT CURRENT_TIMESTAMP,
      invitedBy TEXT,
      photo TEXT
    )');
    $pdo->exec('CREATE TABLE IF NOT EXISTS invites (
      token TEXT PRIMARY KEY,
      email TEXT NOT NULL,
      name TEXT,
      expiresAt TEXT NOT NULL,
      invitedBy TEXT NOT NULL,
      usedAt TEXT,
      createdAt TEXT DEFAULT CURRENT_TIMESTAMP
    )');
    $pdo->exec('CREATE TABLE IF NOT EXISTS session_tokens (
      token TEXT PRIMARY KEY,
      user_type TEXT NOT NULL,
      user_id TEXT NOT NULL,
      theme TEXT DEFAULT "light",
      created_at TEXT DEFAULT CURRENT_TIMESTAMP,
      last_seen TEXT DEFAULT CURRENT_TIMESTAMP,
      expires_at TEXT NOT NULL,
      revoked_at TEXT
    )');
  }
}

 

function findAdminByEmail($email){
  $pdo = db();
  if ($GLOBALS['DB_FALLBACK'] || $pdo === null) { return null; }
  $stmt = $pdo->prepare('SELECT * FROM admins WHERE email = ?');
  $stmt->execute([$email]);
  return $stmt->fetch(PDO::FETCH_ASSOC);
}

function hasAnyAdmins(): bool {
  ensureSchema();
  $pdo = db();
  if ($GLOBALS['DB_FALLBACK'] || $pdo === null) { return false; }
  $stmt = $pdo->query('SELECT COUNT(1) AS c FROM admins');
  $row = $stmt->fetch(PDO::FETCH_ASSOC);
  return $row && intval($row['c'])>0;
}

function listAdmins(): array {
  ensureSchema();
  $pdo = db();
  if ($GLOBALS['DB_FALLBACK'] || $pdo === null) { return []; }
  $stmt = $pdo->query('SELECT email,name,created_at,invitedBy,photo FROM admins ORDER BY created_at DESC');
  $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
  return is_array($rows)?$rows:[];
}

function deleteAdmin($email): bool {
  $pdo = db();
  if ($GLOBALS['DB_FALLBACK'] || $pdo === null) { return false; }
  ensureSchema();
  $stmt = $pdo->prepare('DELETE FROM admins WHERE email = ?');
  return $stmt->execute([$email]);
}

function updateAdmin($email,$name,$passwordHash): bool {
  $pdo = db();
  if ($GLOBALS['DB_FALLBACK'] || $pdo === null) { return false; }
  ensureSchema();
  $stmt = $pdo->prepare('UPDATE admins SET name = ?, password_hash = ? WHERE email = ?');
  return $stmt->execute([$name,$passwordHash,$email]);
}

function migrateAdminsFromJson(): void {
  $pdo = db();
  if ($GLOBALS['DB_FALLBACK'] || $pdo === null) { return; }
  $path = dirname(__DIR__) . '/data/admin.json';
  if(!is_file($path)) return;
  $raw = @file_get_contents($path);
  $j = $raw?json_decode($raw,true):null;
  $list = (is_array($j)&&isset($j['admins'])&&is_array($j['admins']))?$j['admins']:[];
  if(count($list)===0) return;
  ensureSchema();
  $sql = DB_DRIVER==='mysql'
    ? 'INSERT INTO admins(email,name,password_hash,created_at,invitedBy,photo) VALUES(?,?,?,?,?,?) ON DUPLICATE KEY UPDATE name=VALUES(name),password_hash=VALUES(password_hash),invitedBy=VALUES(invitedBy),photo=VALUES(photo)'
    : 'INSERT OR REPLACE INTO admins(email,name,password_hash,created_at,invitedBy,photo) VALUES(?,?,?,?,?,?)';
  $stmt = $pdo->prepare($sql);
  foreach($list as $a){
    $email=strtolower(trim($a['email']??''));
    $name=trim($a['name']??'');
    $hash=trim($a['password_hash']??($a['password']??''));
    $created=trim($a['createdAt']??date('c'));
    $by=trim($a['invitedBy']??'');
    $photo=trim($a['photo']??'');
    if($email==='') continue;
    $stmt->execute([$email,$name,$hash,$created,$by,$photo]);
  }
}

function createAdmin($email,$name,$passwordHash,$invitedBy,$photo=''){
  $pdo = db();
  if ($GLOBALS['DB_FALLBACK'] || $pdo === null) { return false; }
  ensureSchema();
  $sql = DB_DRIVER==='mysql'
    ? 'INSERT INTO admins(email,name,password_hash,created_at,invitedBy,photo) VALUES(?,?,?,?,?,?) ON DUPLICATE KEY UPDATE name=VALUES(name),password_hash=VALUES(password_hash),invitedBy=VALUES(invitedBy),photo=VALUES(photo)'
    : 'INSERT OR REPLACE INTO admins(email,name,password_hash,created_at,invitedBy,photo) VALUES(?,?,?,?,?,?)';
  $stmt=$pdo->prepare($sql);
  return $stmt->execute([$email,$name,$passwordHash,date('c'),$invitedBy,$photo]);
}

function createInvite($email,$name,$token,$invitedBy,$expiresAt){
  $pdo = db();
  if ($GLOBALS['DB_FALLBACK'] || $pdo === null) { return false; }
  ensureSchema();
  $stmt=$pdo->prepare('INSERT INTO invites(token,email,name,expiresAt,invitedBy,createdAt) VALUES(?,?,?,?,?,CURRENT_TIMESTAMP)');
  return $stmt->execute([$token,$email,$name,$expiresAt,$invitedBy]);
}

function findInviteByToken($token){
  $pdo = db();
  if ($GLOBALS['DB_FALLBACK'] || $pdo === null) { return null; }
  $stmt=$pdo->prepare('SELECT * FROM invites WHERE token=?');
  $stmt->execute([$token]);
  return $stmt->fetch(PDO::FETCH_ASSOC);
}

function hasActiveInviteForEmail($email): bool {
  $pdo = db();
  if ($GLOBALS['DB_FALLBACK'] || $pdo === null) { return false; }
  $stmt=$pdo->prepare('SELECT COUNT(1) AS c FROM invites WHERE email=? AND (usedAt IS NULL OR usedAt="") AND STR_TO_DATE(expiresAt, "%Y-%m-%dT%H:%i:%s") > NOW()');
  $stmt->execute([$email]);
  $row=$stmt->fetch(PDO::FETCH_ASSOC);
  return $row && intval($row['c'])>0;
}

function markInviteUsed($token){
  $pdo = db();
  if ($GLOBALS['DB_FALLBACK'] || $pdo === null) { return; }
  $stmt=$pdo->prepare('UPDATE invites SET usedAt=CURRENT_TIMESTAMP WHERE token=?');
  $stmt->execute([$token]);
}

function listInvites(): array {
  $pdo = db();
  if ($GLOBALS['DB_FALLBACK'] || $pdo === null) { return []; }
  $stmt=$pdo->query('SELECT token,email,name,expiresAt,invitedBy,usedAt,createdAt FROM invites ORDER BY createdAt DESC');
  $rows=$stmt->fetchAll(PDO::FETCH_ASSOC);
  return is_array($rows)?$rows:[];
}

function createSessionToken($userType,$userId,$days=30,$theme='light'){
  $pdo = db(); if ($GLOBALS['DB_FALLBACK'] || $pdo === null) { return null; }
  ensureSchema(); $token=bin2hex(random_bytes(32)); $exp=date('Y-m-d H:i:s', time()+($days*86400));
  $stmt=$pdo->prepare('INSERT INTO session_tokens(token,user_type,user_id,theme,created_at,last_seen,expires_at) VALUES(?,?,?,?,CURRENT_TIMESTAMP,CURRENT_TIMESTAMP,?)');
  $stmt->execute([$token,$userType,$userId,$theme,$exp]); return $token;
}

function findSessionToken($token){
  $pdo=db(); if($GLOBALS['DB_FALLBACK']||$pdo===null){ return null; }
  $stmt=$pdo->prepare('SELECT * FROM session_tokens WHERE token=?'); $stmt->execute([$token]); $row=$stmt->fetch(PDO::FETCH_ASSOC); return $row?:null;
}

function touchSessionToken($token){
  $pdo=db(); if($GLOBALS['DB_FALLBACK']||$pdo===null){ return; }
  $stmt=$pdo->prepare('UPDATE session_tokens SET last_seen=CURRENT_TIMESTAMP WHERE token=?'); $stmt->execute([$token]);
}

function revokeSessionToken($token){
  $pdo=db(); if($GLOBALS['DB_FALLBACK']||$pdo===null){ return; }
  $stmt=$pdo->prepare('UPDATE session_tokens SET revoked_at=CURRENT_TIMESTAMP WHERE token=?'); $stmt->execute([$token]);
}

function setTokenTheme($token,$theme){
  $pdo=db(); if($GLOBALS['DB_FALLBACK']||$pdo===null){ return; }
  $stmt=$pdo->prepare('UPDATE session_tokens SET theme=? WHERE token=?'); $stmt->execute([$theme,$token]);
}
