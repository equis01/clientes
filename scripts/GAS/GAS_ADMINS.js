const SPREADSHEET_ID   = '1b6AjVbrzR-rVXRewe1bss-QDdCmd74JOf36uQZdjJOY';
const USERS_SHEET_NAME = 'Usuarios';

// Puedes usar solo doPost, pero dejo doGet para un diagnose opcional
function doPost(e) {
  return handle(e);
}

function doGet(e) {
  // opcional: rápido diagnóstico si entras con ?action=diagnose
  return handle(e);
}

function handle(e) {
  var params = (e && e.parameter) || {};
  var action = (params.action || '').trim();

  if (action === 'create_user') {
    return json(createOrUpdateUser(params));
  }

  if (action === 'diagnose') {
    return json(diagnoseUsuarios());
  }

  return json({ ok:false, error:'acción inválida' });
}

/**
 * Crea o actualiza un usuario en la hoja "Usuarios".
 * Columnas esperadas:
 * A: Usuario
 * B: Contraseña
 * C: URL
 * D: Correo electrónico
 * E: Valida  (tu lógica de correo)
 * F: Alias
 * G: Portal activo / flag extra (checkbox / 1/0) ← NUEVA
 */
function createOrUpdateUser(params) {
  var username  = (params.username   || '').trim();
  var password  = (params.password   || '').trim();
  var driveUrl  = (params.drive_url  || '').trim();
  var email     = (params.email      || '').trim();
  var alias     = (params.alias      || '').trim();
  var validRaw  = (params.valid      || '').trim();               // E
  var portalRaw = (params.portal_enabled || params.portal || '').trim(); // G

  if (!username || !password) {
    return { ok:false, error:'username y password son requeridos' };
  }

  var valid        = normalizeValid(validRaw);            // 1 ó 0 → col E
  var portalFlag   = normalizeValid(portalRaw || '1');    // 1 ó 0 → col G (por defecto 1)

  var ss;
  try {
    ss = SpreadsheetApp.openById(SPREADSHEET_ID);
  } catch (err) {
    return { ok:false, error:'No se pudo abrir el archivo de usuarios' };
  }

  var hoja = ss.getSheetByName(USERS_SHEET_NAME);
  if (!hoja) {
    return { ok:false, error:'Hoja "Usuarios" no encontrada' };
  }

  var lastRow = hoja.getLastRow();
  // Si solo tiene encabezados, lastRow puede ser 1
  var dataStartRow = 2;
  var foundRow = null;

  if (lastRow >= dataStartRow) {
    var numRows = lastRow - dataStartRow + 1;
    // Solo columna A (Usuario)
    var usersCol = hoja.getRange(dataStartRow, 1, numRows, 1).getValues();

    for (var i = 0; i < usersCol.length; i++) {
      var existingUser = String(usersCol[i][0] || '').trim();
      if (existingUser && existingUser === username) {
        foundRow = dataStartRow + i;
        break;
      }
    }
  }

  var rowValues = [
    username,      // A Usuario
    password,      // B Contraseña (texto plano)
    driveUrl,      // C URL
    email,         // D Correo electrónico
    valid,         // E Valida (1/0, tu uso)
    alias,         // F Alias
    portalFlag     // G Portal activo / flag extra (1/0)
  ];

  var operation;
  if (foundRow) {
    // Actualizar
    hoja.getRange(foundRow, 1, 1, rowValues.length).setValues([rowValues]);
    operation = 'updated';
  } else {
    // Insertar al final
    var newRow = (lastRow >= 1 ? lastRow + 1 : 2);
    hoja.getRange(newRow, 1, 1, rowValues.length).setValues([rowValues]);
    operation = 'created';
  }

  return {
    ok: true,
    message: operation === 'created' ? 'Usuario creado' : 'Usuario actualizado',
    operation: operation,
    username: username
  };
}

/**
 * Diagnóstico rápido: qué headers ve y cuántas filas de datos hay.
 */
function diagnoseUsuarios() {
  var ss = SpreadsheetApp.openById(SPREADSHEET_ID);
  var hoja = ss.getSheetByName(USERS_SHEET_NAME);
  if (!hoja) {
    return { ok:false, error:'Hoja "Usuarios" no encontrada' };
  }

  var lastRow = hoja.getLastRow();
  var lastCol = hoja.getLastColumn();
  var headers = lastCol > 0
    ? hoja.getRange(1, 1, 1, lastCol).getValues()[0].map(String)
    : [];

  return {
    ok: true,
    totalRowsDatos: Math.max(0, lastRow - 1),
    headers: headers
  };
}

/**
 * Normaliza "valid" / flags a 1/0.
 * Acepta true, "true", "on", "1", "si", etc.
 */
function normalizeValid(v) {
  if (v === true) return 1;
  var s = String(v || '').trim().toLowerCase();
  if (!s) return 0;
  if (s === '1' || s === 'true' || s === 'on' || s === 'sí' || s === 'si' || s === 'yes') return 1;
  return 0;
}

/**
 * Helper para devolver JSON.
 */
function json(o) {
  return ContentService
    .createTextOutput(JSON.stringify(o))
    .setMimeType(ContentService.MimeType.JSON);
}