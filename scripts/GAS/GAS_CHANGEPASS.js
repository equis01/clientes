const SPREADSHEET_ID   = '1b6AjVbrzR-rVXRewe1bss-QDdCmd74JOf36uQZdjJOY';
const USERS_SHEET_NAME = 'Usuarios';

function doPost(e) {
  var username        = (e.parameter.username || '').trim();
  var currentPassword = e.parameter.current_password || e.parameter.old_password || '';
  var newPassword     = e.parameter.new_password || '';

  var resp = cambiarPassword(username, currentPassword, newPassword);

  return ContentService
    .createTextOutput(JSON.stringify(resp))
    .setMimeType(ContentService.MimeType.JSON);
}

/**
 * Cambia la contraseña de un usuario en la hoja "Usuarios".
 * - Verifica usuario + contraseña actual.
 * - Si coincide, escribe la nueva contraseña en columna B.
 */
function cambiarPassword(username, currentPassword, newPassword) {
  if (!username || !currentPassword || !newPassword) {
    return { ok:false, error:'Faltan datos' };
  }

  if (newPassword.length < 8) {
    return { ok:false, error:'La nueva contraseña debe tener al menos 8 caracteres' };
  }

  var ss;
  try {
    ss = SpreadsheetApp.openById(SPREADSHEET_ID);
  } catch (e) {
    return { ok:false, error:'No se pudo abrir el archivo de usuarios' };
  }

  var hoja = ss.getSheetByName(USERS_SHEET_NAME);
  if (!hoja) {
    return { ok:false, error:'Hoja "Usuarios" no encontrada' };
  }

  var lastRow = hoja.getLastRow();
  if (lastRow < 2) {
    return { ok:false, error:'Base de datos vacía' };
  }

  // A: usuario, B: contraseña
  var datos = hoja.getRange(2, 1, lastRow - 1, 2).getValues();

  for (var i = 0; i < datos.length; i++) {
    var user = String(datos[i][0]).trim();
    var pass = String(datos[i][1]);

    if (user === username) {
      // Validar contraseña actual
      if (pass !== currentPassword) {
        return { ok:false, error:'Contraseña actual incorrecta' };
      }

      // Actualizar contraseña (columna B)
      hoja.getRange(i + 2, 2).setValue(newPassword);

      return { ok:true, message:'Contraseña actualizada correctamente' };
    }
  }

  return { ok:false, error:'Usuario no encontrado' };
}