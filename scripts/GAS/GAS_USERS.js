// enlace del gas: https://script.google.com/macros/s/AKfycbwoGu1nai1P4BL2jNq6SdOm6qzJ9qZ6Uw08Oh6udZg1BWadu8MElLl6HR_2vgxOtSIS/exec

const SPREADSHEET_ID = '1b6AjVbrzR-rVXRewe1bss-QDdCmd74JOf36uQZdjJOY';
// nombre de la pestaña donde está la tabla de usuarios
const USERS_SHEET_NAME = 'Usuarios';

function doGet(e){ return handle(e); }
function doPost(e){ return handle(e); }

function handle(e){
  var action = String(e && e.parameter && e.parameter.action || '').trim();

  // action=users → devuelve todos los usuarios (o uno en específico)
  if (action === 'users') {
    var username = String(e.parameter && (e.parameter.user || e.parameter.username || '')).trim();
    return json(getUsers(username));
  }

  // action=diagnose → ver encabezados e índices de la hoja Usuarios (debug)
  if (action === 'diagnose') {
    return json(diagnoseUsers());
  }

  return json({ ok:false, error:'accion invalida' });
}

/**
 * Devuelve el JSON de usuarios.
 * Si se pasa username → solo ese usuario.
 * Si no → todos.
 */
function getUsers(usernameFilter){
  var hoja = usersSheet();
  if (!hoja) return { ok:false, error:'Hoja "Usuarios" no encontrada' };

  var lastRow = hoja.getLastRow();
  var lastCol = hoja.getLastColumn();
  if (lastRow <= 1) {
    return { ok:true, generated_at: new Date().toISOString(), users:{} };
  }

  // Encabezados
  var headers = hoja.getRange(1,1,1,lastCol).getValues()[0].map(String);
  var IDX = indicesUsuarios(headers);

  if (IDX.USUARIO === -1) {
    return {
      ok:false,
      error:'No se encontró la columna "Usuario" en la hoja Usuarios',
      headers: headers,
      indices: IDX
    };
  }

  var datos = hoja.getRange(2,1,lastRow-1,lastCol).getValues();
  var users = {};
  var nowIso = new Date().toISOString();
  var filter = String(usernameFilter || '').trim();

  for (var i=0; i<datos.length; i++){
    var row = datos[i];

    var username = String(row[IDX.USUARIO] || '').trim();
    if (!username) continue; // fila vacía

    // Si nos pidieron un usuario específico, filtramos
    if (filter && username !== filter) continue;

    var passwordCell = IDX.CONTRASENA > -1 ? row[IDX.CONTRASENA] : '';
    var urlCell      = IDX.URL        > -1 ? row[IDX.URL]        : '';
    var emailCell    = IDX.CORREO     > -1 ? row[IDX.CORREO]     : '';
    var validaCell   = IDX.VALIDA     > -1 ? row[IDX.VALIDA]     : '';
    var aliasCell    = IDX.ALIAS      > -1 ? row[IDX.ALIAS]      : '';
    var portalCell   = IDX.PORTAL     > -1 ? row[IDX.PORTAL]     : '';

    // La columna "Valida" suele ser un checkbox → boolean → lo mapeamos a 0/1
    var validFlag = 0;
    if (validaCell === true || validaCell === 1 || String(validaCell).toUpperCase() === 'TRUE') {
      validFlag = 1;
    }

    // Columna G: portal activo (checkbox) → también la pasamos a 0/1
    var portalFlag = 0;
    if (portalCell === true || portalCell === 1 || String(portalCell).toUpperCase() === 'TRUE') {
      portalFlag = 1;
    }

    users[username] = {
      username: username,
      // aquí asumimos que en "Contraseña" ya tienes el hash bcrypt;
      // si todavía es texto plano, se va tal cual (y lo hasheas en tu backend).
      password_hash: safe(passwordCell),
      drive_url:     safe(urlCell),
      email:         safe(emailCell),
      valid:         validFlag,
      alias:         safe(aliasCell),
      portal_enabled: portalFlag,   // ← NUEVO CAMPO basado en la columna G
      updated_at:    nowIso
    };
  }

  return { ok:true, generated_at: nowIso, users: users };
}

/**
 * Diagnóstico rápido para ver cómo está leyendo la hoja de Usuarios.
 */
function diagnoseUsers(){
  var hoja = usersSheet();
  if (!hoja) return { ok:false, error:'Hoja "Usuarios" no encontrada' };

  var lastRow = hoja.getLastRow();
  var lastCol = hoja.getLastColumn();
  var headers = hoja.getRange(1,1,1,lastCol).getValues()[0].map(String);
  var IDX = indicesUsuarios(headers);

  return {
    ok:true,
    totalRows: lastRow - 1,
    headers: headers,
    indices: IDX
  };
}

/**
 * Devuelve la hoja Usuarios.
 */
function usersSheet(){
  var ss = SpreadsheetApp.openById(SPREADSHEET_ID);
  return ss.getSheetByName(USERS_SHEET_NAME);
}

/**
 * Mapea encabezados de la hoja Usuarios → índices de columna.
 */
function indicesUsuarios(h){
  var H = h.map(function(v){ return normalizeHeader(v); });

  function find(names){
    for (var i=0; i<names.length; i++){
      var target = normalizeHeader(names[i]);
      var idx = H.indexOf(target);
      if (idx > -1) return idx;
    }
    return -1;
  }

  return {
    USUARIO:   find(['Usuario']),
    CONTRASENA:find(['Contraseña','Contrasena','Password']),
    URL:       find(['URL']),
    CORREO:    find(['Correo electrónico','Correo electronico','Email']),
    VALIDA:    find(['Valida','Válida','Valid']),
    ALIAS:     find(['Alias']),
    PORTAL:    find(['Portal activo','Portal','Portal habilitado']) // ← NUEVA COLUMNA G
  };
}

/**
 * Normalización solo para encabezados.
 */
function normalizeHeader(v){
  v = String(v || '').trim().toUpperCase();
  v = v.normalize('NFD').replace(/[\u0300-\u036f]/g,''); // sin acentos
  v = v.replace(/\s+/g,' ');
  return v;
}

/**
 * Convierte null/undefined a string vacío.
 */
function safe(v){
  return v == null ? '' : String(v);
}

/**
 * Respuesta JSON.
 */
function json(o){
  return ContentService
    .createTextOutput(JSON.stringify(o))
    .setMimeType(ContentService.MimeType.JSON);
}