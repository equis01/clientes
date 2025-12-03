<?php
require_once __DIR__ . '/../config/config.php';

$GLOBALS['DB_FALLBACK'] = false;

function db(): ?PDO {
  static $pdo = null;
  if ($pdo !== null) return $pdo;
  if (DB_DRIVER === 'sqlite') {
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
  $pdo->exec('CREATE TABLE IF NOT EXISTS users (
    username TEXT PRIMARY KEY,
    password_hash TEXT NOT NULL,
    drive_url TEXT,
    email TEXT,
    valid INTEGER NOT NULL DEFAULT 1,
    alias TEXT,
    services_count INTEGER,
    created_at TEXT DEFAULT CURRENT_TIMESTAMP,
    updated_at TEXT DEFAULT CURRENT_TIMESTAMP
  )');
  $pdo->exec('CREATE INDEX IF NOT EXISTS idx_users_email ON users(email)');
}

function insertUser($u,$plain,$url,$email,$valid,$alias): void {
  ensureSchema();
  $hash = password_hash($plain, PASSWORD_DEFAULT);
  $pdo = db();
  if ($GLOBALS['DB_FALLBACK'] || $pdo === null) { return; }
  $stmt = $pdo->prepare('INSERT OR REPLACE INTO users(username,password_hash,drive_url,email,valid,alias,updated_at) VALUES(?,?,?,?,?,?,CURRENT_TIMESTAMP)');
  $stmt->execute([$u,$hash,$url,$email,$valid?1:0,$alias]);
}

function findUserByUsername($u){
  $pdo = db();
  if ($GLOBALS['DB_FALLBACK'] || $pdo === null) { return null; }
  $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ?');
  $stmt->execute([$u]);
  return $stmt->fetch(PDO::FETCH_ASSOC);
}

function findUserByEmail($email){
  $pdo = db();
  if ($GLOBALS['DB_FALLBACK'] || $pdo === null) { return null; }
  $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
  $stmt->execute([$email]);
  return $stmt->fetch(PDO::FETCH_ASSOC);
}

function updateServicesCount($u,$count){
  $pdo = db();
  if ($GLOBALS['DB_FALLBACK'] || $pdo === null) { return; }
  $pdo->exec('ALTER TABLE users ADD COLUMN services_count INTEGER');
  $stmt = $pdo->prepare('UPDATE users SET services_count=? , updated_at=CURRENT_TIMESTAMP WHERE username=?');
  $stmt->execute([$count,$u]);
}

function getServicesCount($u){
  $pdo = db();
  if ($GLOBALS['DB_FALLBACK'] || $pdo === null) { return null; }
  $stmt = $pdo->prepare('SELECT services_count FROM users WHERE username=?');
  $stmt->execute([$u]);
  $row = $stmt->fetch(PDO::FETCH_ASSOC);
  return $row ? $row['services_count'] : null;
}

function hasAnyUser(): bool {
  ensureSchema();
  $pdo = db();
  if ($GLOBALS['DB_FALLBACK'] || $pdo === null) { return false; }
  $stmt = $pdo->query('SELECT COUNT(1) AS c FROM users');
  $row = $stmt->fetch(PDO::FETCH_ASSOC);
  return $row && intval($row['c'])>0;
}
