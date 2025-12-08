/***** CONFIGURACIÓN *****/

// Archivo donde están:
// - Hoja "BD RELLENO 2025" (operaciones)
// - Hoja "Plantilla reporte mensual_2025" (razones sociales)
const DATA_SPREADSHEET_ID   = '1BvnfT3XX5ayUaD8DWX5PyIgzjqpYYrv37AK8iTImhAQ';
const OPERACIONES_SHEET_ID  = 737149364;  // sheetId de "BD RELLENO 2025"

// Base de datos de clientes (carpeta integral)
const CLIENTS_SPREADSHEET_ID = '1b6AjVbrzR-rVXRewe1bss-QDdCmd74JOf36uQZdjJOY';
const CLIENTS_SHEET_NAME     = 'Usuarios';

// Hoja donde está ALIAS / RAZON SOCIAL (para facturas)
const RAZONES_SHEET_NAME   = 'Plantilla reporte mensual_2025';
const RAZONES_HEADER_ROW   = 3;  // fila de encabezados (No., STATUS, TIPO, ALIAS, RAZON SOCIAL...)
const RAZONES_FIRST_ROW    = 4;  // primera fila con datos

// Plantilla de Google Docs (con {{PLACEHOLDERS}})
const TEMPLATE_DOC_ID      = '1Ai4RuIJG4apRnI1H0eqpxZo88xIG2jyyORuHP4KcBKw';

// Zona horaria
const TIMEZONE             = 'America/Mexico_City';

// ¿Conservar la copia del Docs que se rellena?
const KEEP_DOC_COPY        = false;

// A quién pasarle la propiedad en el lockReport
// (si no quieres transferir, deja cadena vacía '')
const LOCK_NEW_OWNER       = 'pruebaapp@mediosconvalor.com';

// LOG de reportes (por defecto reutilizo la BD de clientes)
const LOG_SPREADSHEET_ID   = '1fZDapAigB-6DQQal27hjVIN8kP4Rr-liIzkGckqYfbA';
const LOG_SHEET_NAME       = 'Historial reportes';


/***** ENTRYPOINT WEB APP *****/

function doGet(e){ return handle(e); }
function doPost(e){ return handle(e); }

function handle(e){
  var params = (e && e.parameter) || {};
  var action = String(params.action || '').trim();

  if (action === 'generateReport') {
    return json(generateReportDoc(params));
  }

  if (action === 'lockReport') {
    return json(lockReport(params));
  }

  if (action === 'findReport') {
    return json(findReport(params));
  }

  if (action === 'diagnoseRazones') {
    return json(diagnoseRazones());
  }

  return json({ ok:false, error:'accion invalida' });
}


/***** 1) GENERAR REPORTE (DOCS + PDF) *****/

function generateReportDoc(params){
  var step = 'validateParams';
  try {
    var aliasRaw      = (params.alias     || '').trim(); // ej: "ALIMENTOS CAROL"
    var mesParam      = (params.mes       || '').trim(); // "11" o "noviembre"
    var anioStr       = (params.anio      || '').trim(); // "2025"
    var driveUrlParam = (params.drive_url || '').trim(); // carpeta integral (opcional)
    var usuarioRaw    = (params.usuario   || params.username || '').trim(); // opcional

    if (!aliasRaw || !mesParam || !anioStr) {
      return { ok:false, error:'Faltan parámetros (alias, mes, anio)', step:step };
    }

    var mesNum = parseMesNumero(mesParam);     // 1-12
    if (!(mesNum >= 1 && mesNum <= 12)) {
      return { ok:false, error:'Mes inválido', step:step };
    }

    var anio = parseInt(anioStr,10);
    if (!(anio >= 2000 && anio <= 2100)) {
      return { ok:false, error:'Año inválido', step:step };
    }

    var aliasNorm       = normalize(aliasRaw);
    var mesNombreMayus  = monthNameEs(mesNum); // "NOVIEMBRE"
    var mes2            = toTwoDigits(mesNum);
    var todayStr        = Utilities.formatDate(new Date(), TIMEZONE, 'dd/MM/yyyy');

    /***** 1.1 Obtener datos de servicios directamente desde BD RELLENO 2025 *****/
    step = 'getMetricsLocal';
    var metrics = getMetricsForReport(aliasRaw, mesNum, anio);
    if (!metrics.ok) {
      return { ok:false, error:(metrics.error || 'Error al obtener métricas'), step:step };
    }
    var rows   = metrics.rows;    // [{fecha, tipo, volumen, kg, exceso}, ...]
    var totals = metrics.totals;  // {volumen, kg, exceso}

    /***** 1.2 Obtener RAZON SOCIAL desde Plantilla reporte mensual_2025 *****/
    step = 'getRazonSocial';
    var razonSocial = getRazonSocialFromPlantilla(aliasNorm) || aliasRaw;

    /***** 1.3 Resolver carpeta integral *****/
    step = 'resolveRootFolder';
    var rootFolder = resolveRootFolder(driveUrlParam, aliasNorm);

    /***** 1.4 Asegurar carpetas Reportes <alias>/<anio> *****/
    step = 'ensureFolders';
    var safeAlias          = sanitizeForFilename(aliasRaw);
    var reportesFolderName = 'Reportes ' + safeAlias;
    var yearFolderName     = String(anio);

    var reportesFolder = ensureChildFolder(rootFolder, reportesFolderName);
    var yearFolder     = ensureChildFolder(reportesFolder, yearFolderName);

    /***** 1.5 Copiar plantilla Docs y rellenar *****/
    step = 'fillTemplate';

    var baseName = mes2 + '.REPORTE DE RESIDUOS_' + safeAlias + '_' + mesNombreMayus + '_' + anio;

    var templateFile = DriveApp.getFileById(TEMPLATE_DOC_ID);
    var copyName     = 'TMP_' + baseName;
    var copyFile     = templateFile.makeCopy(copyName, yearFolder);
    var copyId       = copyFile.getId();

    var doc  = DocumentApp.openById(copyId);
    var body = doc.getBody();

    // Placeholders en el cuerpo del Docs
    body.replaceText('{{MES_MAYUS}}',        mesNombreMayus);
    body.replaceText('{{NOMBRE_COMERCIAL}}', aliasRaw);
    body.replaceText('{{NOMBRE_FISCAL}}',    razonSocial);
    body.replaceText('{{RFC}}',              ''); // si luego tienes RFC, aquí se llena
    body.replaceText('{{FECHA_REPORTE}}',    todayStr);

    body.replaceText('{{TOTAL_M3}}',      totals.volumen.toFixed(1));
    body.replaceText('{{TOTAL_KG}}',      totals.kg.toFixed(1));
    body.replaceText('{{TOTAL_EXCESOS}}', totals.exceso.toFixed(1));

    // Construir tabla de servicios
    var tableRows = [];
    tableRows.push(['FECHA','TIPO DE RESIDUO','VOLUMEN (m³)','PESO (kg)','EXCESOS (m³)']);

    rows.forEach(function(r){
      var fechaStr = '';
      if (r.fecha instanceof Date) {
        fechaStr = Utilities.formatDate(r.fecha, TIMEZONE, 'dd/MM/yyyy');
      } else if (r.fechaStr) {
        var d = parseAnyDate(r.fechaStr);
        fechaStr = d
          ? Utilities.formatDate(d, TIMEZONE, 'dd/MM/yyyy')
          : String(r.fechaStr);
      }
      tableRows.push([
        fechaStr,
        r.tipo || '',
        r.volumen || 0,
        r.kg || 0,
        r.exceso || 0
      ]);
    });

    // Reemplazar marcador {{TABLA_SERVICIOS}} por la tabla
    var marker = '{{TABLA_SERVICIOS}}';
    var rangeElement = body.findText(marker);
    if (rangeElement) {
      var el = rangeElement.getElement();
      el.asText().setText(''); // borramos el marcador
      body.appendTable(tableRows);
    } else {
      body.appendTable(tableRows);
    }

    // Poner encabezado de la última tabla en negritas
    var tables = body.getTables();
    if (tables.length > 0) {
      var table = tables[tables.length - 1];
      var headerRow = table.getRow(0);
      for (var c = 0; c < headerRow.getNumCells(); c++) {
        headerRow.getCell(c).editAsText().setBold(true);
      }
    }

    doc.saveAndClose();

    /***** 1.6 Exportar a PDF y compartir por enlace *****/
    step = 'exportPdf';
    var pdfName = makeUniquePdfName(yearFolder, baseName);
    var pdfBlob = exportDocToPdfBlob(copyId, pdfName);

    step = 'savePdf';
    var pdfFile = yearFolder.createFile(pdfBlob);

    // Permitir que cualquiera con el enlace pueda ver el PDF
    pdfFile.setSharing(DriveApp.Access.ANYONE_WITH_LINK, DriveApp.Permission.VIEW);

    if (!KEEP_DOC_COPY) {
      copyFile.setTrashed(true);
    }

    var fileId      = pdfFile.getId();
    var downloadUrl = 'https://drive.google.com/uc?export=download&id=' + fileId;
    var viewUrl     = pdfFile.getUrl();
    var folderPath  = reportesFolderName + '/' + yearFolderName;

    /***** 1.7 Registrar en LOG *****/
    step = 'logReport';
    logReportGeneration(usuarioRaw, aliasRaw, mesNum, anio, fileId, pdfFile.getName(), folderPath);

    return {
      ok: true,
      fileId: fileId,
      fileName: pdfFile.getName(),
      downloadUrl: downloadUrl,
      viewUrl: viewUrl,
      folderPath: folderPath
    };

  } catch (err) {
    return { ok:false, error:String(err), step:step || 'unknown' };
  }
}


/***** 2) SEGUNDA LLAMADA: BLOQUEAR REPORTE (Cerrar modal) *****/

function lockReport(params) {
  var fileId = (params.fileId || '').trim();
  if (!fileId) {
    return { ok:false, error:'fileId requerido' };
  }

  try {
    var file = DriveApp.getFileById(fileId);

    // 1) Quitar "cualquiera con el enlace"
    file.setSharing(DriveApp.Access.PRIVATE, DriveApp.Permission.NONE);

    // 2) (Opcional) Transferir propiedad
    if (LOCK_NEW_OWNER) {
      try {
        file.setOwner(LOCK_NEW_OWNER);
      } catch (e) {
        // Puede fallar por políticas del dominio; no rompemos la respuesta
      }
    }

    return { ok:true };
  } catch (err) {
    return { ok:false, error:String(err) };
  }
}


/***** 3) TERCERA ACCIÓN: LOCALIZAR REPORTE YA GENERADO *****/

function findReport(params) {
  var step = 'validateParams';
  try {
    var aliasRaw      = (params.alias     || '').trim();
    var mesParam      = (params.mes       || '').trim();
    var anioStr       = (params.anio      || '').trim();
    var driveUrlParam = (params.drive_url || '').trim();

    if (!aliasRaw || !mesParam || !anioStr) {
      return { ok:false, error:'Faltan parámetros (alias, mes, anio)', step:step };
    }

    var mesNum = parseMesNumero(mesParam);
    if (!(mesNum >= 1 && mesNum <= 12)) {
      return { ok:false, error:'Mes inválido', step:step };
    }

    var anio = parseInt(anioStr,10);
    if (!(anio >= 2000 && anio <= 2100)) {
      return { ok:false, error:'Año inválido', step:step };
    }

    var aliasNorm      = normalize(aliasRaw);
    var safeAlias      = sanitizeForFilename(aliasRaw);
    var mesNombreMayus = monthNameEs(mesNum);
    var mes2           = toTwoDigits(mesNum);

    /***** 3.1 Intentar primero con el LOG *****/
    step = 'checkLog';
    var logEntry = lookupReportInLog(aliasRaw, mesNum, anio);
    if (logEntry && logEntry.fileId) {
      try {
        var fLog = DriveApp.getFileById(logEntry.fileId);
        var dl   = 'https://drive.google.com/uc?export=download&id=' + logEntry.fileId;
        return {
          ok:true,
          fileId: logEntry.fileId,
          fileName: logEntry.fileName || fLog.getName(),
          downloadUrl: dl,
          viewUrl: fLog.getUrl(),
          folderPath: logEntry.folderPath || ''
        };
      } catch (e) {
        // si falló (papelera, movido, etc.) seguimos al método viejo
      }
    }

    /***** 3.2 Fallback: buscar en Drive por nombre *****/

    // 1) Resolver carpeta integral (igual que generateReport)
    step = 'resolveRootFolder';
    var rootFolder = resolveRootFolder(driveUrlParam, aliasNorm);

    // 2) Localizar carpeta Reportes <alias>/<anio>
    step = 'locateFolders';
    var reportesFolderName = 'Reportes ' + safeAlias;
    var yearFolderName     = String(anio);

    var reportesFolder = getExistingChildFolder(rootFolder, reportesFolderName);
    if (!reportesFolder) {
      return { ok:false, error:'Carpeta de reportes no encontrada', step:step };
    }

    var yearFolder = getExistingChildFolder(reportesFolder, yearFolderName);
    if (!yearFolder) {
      return { ok:false, error:'Carpeta del año no encontrada', step:step };
    }

    // 3) Buscar archivo por nombre base
    step = 'findFile';
    var baseName  = mes2 + '.REPORTE DE RESIDUOS_' + safeAlias + '_' + mesNombreMayus + '_' + anio;
    var basePdf   = baseName + '.pdf';

    var files = yearFolder.getFiles();
    var chosenFile = null;

    while (files.hasNext()) {
      var f    = files.next();
      var name = f.getName();

      // Coincidencias: nombre exacto o versiones con _(<n>).pdf
      if (name === basePdf || name.indexOf(baseName + '_(') === 0) {
        if (!chosenFile) {
          chosenFile = f;
        } else {
          // nos quedamos con el más reciente
          if (f.getLastUpdated() > chosenFile.getLastUpdated()) {
            chosenFile = f;
          }
        }
      }
    }

    if (!chosenFile) {
      return { ok:false, error:'Reporte no encontrado en Drive', step:step };
    }

    var fileId      = chosenFile.getId();
    var downloadUrl = 'https://drive.google.com/uc?export=download&id=' + fileId;
    var viewUrl     = chosenFile.getUrl();
    var folderPath  = reportesFolderName + '/' + yearFolderName;

    return {
      ok: true,
      fileId: fileId,
      fileName: chosenFile.getName(),
      downloadUrl: downloadUrl,
      viewUrl: viewUrl,
      folderPath: folderPath
    };

  } catch (err) {
    return { ok:false, error:String(err), step:step || 'unknown' };
  }
}


/***** 4) MÉTRICAS DIRECTAS DESDE BD RELLENO 2025 *****/

function getMetricsForReport(aliasRaw, mesNum, anio){
  try {
    var hoja = sheetOperaciones();
    if (!hoja) return { ok:false, error:'Hoja de operaciones no encontrada' };

    var lastRow = hoja.getLastRow();
    var lastCol = hoja.getLastColumn();
    if (lastRow <= 1) {
      return { ok:true, rows:[], totals:{volumen:0,kg:0,exceso:0} };
    }

    // Detectar encabezados
    var info = detectarEncabezadosOperaciones(hoja);
    var headerRow = info.headerRow;
    var headers   = info.headers;
    var IDX       = info.indices;

    if (IDX.CLIENTES === -1 && IDX.RAZON === -1) {
      return {
        ok:false,
        error:'Faltan columnas requeridas en BD RELLENO',
        headerRow: headerRow,
        headers: headers,
        indices: IDX
      };
    }

    var dataStartRow = headerRow + 1;
    if (lastRow <= headerRow) {
      return { ok:true, rows:[], totals:{volumen:0,kg:0,exceso:0} };
    }

    var datos = hoja.getRange(dataStartRow, 1, lastRow - headerRow, lastCol).getValues();

    var aliasNorm = normalize(aliasRaw);
    var rows   = [];
    var totVol = 0, totKg = 0, totExc = 0;

    for (var j = 0; j < datos.length; j++){
      var row = datos[j];

      var cliente = IDX.CLIENTES > -1 ? row[IDX.CLIENTES] : '';
      var razon   = IDX.RAZON    > -1 ? row[IDX.RAZON]    : '';

      if (!coincideConAliasOper(aliasNorm, cliente, razon)) continue;

      // Fecha
      var fechaVal = IDX.C > -1 ? row[IDX.C] : '';
      var d = parseAnyDate(fechaVal);
      if (!d) continue;

      // Filtro mes/año
      if (d.getFullYear() !== anio) continue;
      if ((d.getMonth() + 1) !== mesNum) continue;

      var tipo = IDX.F > -1 ? row[IDX.F] : '';

      var vol = (IDX.Q > -1) ? toNumber(row[IDX.Q]) : 0;
      var kg  = (IDX.S > -1) ? toNumber(row[IDX.S]) : 0;
      var exc = (IDX.T > -1) ? toNumber(row[IDX.T]) : 0;

      rows.push({
        fecha: d,
        fechaStr: fechaVal,
        tipo: safe(tipo),
        volumen: vol,
        kg: kg,
        exceso: exc
      });

      totVol += vol;
      totKg  += kg;
      totExc += exc;
    }

    return {
      ok:true,
      rows: rows,
      totals: {
        volumen: totVol,
        kg: totKg,
        exceso: totExc
      }
    };
  } catch (err) {
    return { ok:false, error:String(err) };
  }
}

function sheetOperaciones(){
  var ss = SpreadsheetApp.openById(DATA_SPREADSHEET_ID);
  var sheets = ss.getSheets();
  for (var i = 0; i < sheets.length; i++){
    if (sheets[i].getSheetId() === OPERACIONES_SHEET_ID) return sheets[i];
  }
  return null;
}

function detectarEncabezadosOperaciones(hoja) {
  var lastRow = hoja.getLastRow();
  var lastCol = hoja.getLastColumn();
  var maxScan = Math.min(lastRow, 10);

  var rango = hoja.getRange(1, 1, maxScan, lastCol).getValues();
  for (var r = 0; r < maxScan; r++) {
    var headers = rango[r].map(String);
    var IDX = indicesOperaciones(headers);
    if (IDX.CLIENTES > -1 || IDX.RAZON > -1) {
      return {
        headerRow: r + 1,
        headers: headers,
        indices: IDX
      };
    }
  }

  var headers0 = rango[0].map(String);
  return {
    headerRow: 1,
    headers: headers0,
    indices: indicesOperaciones(headers0)
  };
}

function indicesOperaciones(h){
  var H = h.map(normalizeHeader);

  function find(names){
    for (var i=0; i<names.length; i++){
      var ref = normalizeHeader(names[i]);
      var k = H.indexOf(ref);
      if (k>-1) return k;
    }
    return -1;
  }

  return {
    CLIENTES: find(['CLIENTES','CLIENTE']),
    RAZON:    find(['RAZON SOCIAL','RAZÓN SOCIAL']),
    C:        find(['FECHA DE RECOLECCION','FECHA DE RECOLECCIÓN']),
    F:        find(['TIPO DE RESIDUO']),
    Q:        find(['M3']),
    S:        find(['KG','PESO NETO','TOTAL KG','TOTAL KG (COLUMNA N)']),
    T:        find(['EXCESOS'])
  };
}

function coincideConAliasOper(aliasNorm, cliente, razon) {
  aliasNorm = aliasNorm || '';
  cliente   = normalize(cliente || '');
  razon     = normalize(razon   || '');

  if (!aliasNorm) return false;

  if (cliente && (cliente === aliasNorm || cliente.indexOf(aliasNorm) !== -1)) return true;
  if (razon   && (razon   === aliasNorm || razon.indexOf(aliasNorm)   !== -1)) return true;

  return false;
}


/***** 5) RAZÓN SOCIAL DESDE Plantilla reporte mensual_2025 *****/

function getRazonSocialFromPlantilla(aliasNorm){
  var ss   = SpreadsheetApp.openById(DATA_SPREADSHEET_ID);
  var hoja = ss.getSheetByName(RAZONES_SHEET_NAME);
  if (!hoja) return '';

  var lastRow = hoja.getLastRow();
  var lastCol = hoja.getLastColumn();
  if (lastRow < RAZONES_FIRST_ROW) return '';

  var headers = hoja.getRange(RAZONES_HEADER_ROW, 1, 1, lastCol).getValues()[0].map(String);
  var H = headers.map(normalizeHeader);

  var idxAlias = H.indexOf(normalizeHeader('ALIAS'));
  var idxRazon = H.indexOf(normalizeHeader('RAZON SOCIAL'));

  if (idxAlias === -1 || idxRazon === -1) return '';

  var numRows = lastRow - RAZONES_FIRST_ROW + 1;
  var datos   = hoja.getRange(RAZONES_FIRST_ROW, 1, numRows, lastCol).getValues();

  for (var i=0; i<datos.length; i++){
    var row = datos[i];
    var aliasCellNorm = normalize(row[idxAlias] || '');
    if (aliasCellNorm && aliasCellNorm === aliasNorm) {
      return String(row[idxRazon] || '').trim();
    }
  }

  return '';
}

function diagnoseRazones(){
  var ss   = SpreadsheetApp.openById(DATA_SPREADSHEET_ID);
  var hoja = ss.getSheetByName(RAZONES_SHEET_NAME);
  if (!hoja) return { ok:false, error:'Hoja no encontrada: '+RAZONES_SHEET_NAME };

  var lastRow = hoja.getLastRow();
  var lastCol = hoja.getLastColumn();
  var headers = hoja.getRange(RAZONES_HEADER_ROW,1,1,lastCol).getValues()[0].map(String);

  return {
    ok:true,
    headerRow: RAZONES_HEADER_ROW,
    totalRows: lastRow - RAZONES_HEADER_ROW,
    headers: headers
  };
}


/***** 6) RESOLVER CARPETA INTEGRAL *****/

// Usa drive_url si viene; si falla o no viene, busca en BD de clientes por alias
function resolveRootFolder(driveUrlParam, aliasNorm) {
  // 1) Si tenemos drive_url, intentamos usarla
  if (driveUrlParam) {
    var idFromUrl = extractFolderIdFromUrl(driveUrlParam);
    if (idFromUrl) {
      try {
        return DriveApp.getFolderById(idFromUrl);
      } catch (e) {
        // Si falla, seguimos al fallback
      }
    }
  }
  // 2) Fallback: hoja "Usuarios"
  return resolveRootFolderFromClientes(aliasNorm);
}

function resolveRootFolderFromClientes(aliasNorm){
  var ss   = SpreadsheetApp.openById(CLIENTS_SPREADSHEET_ID);
  var hoja = ss.getSheetByName(CLIENTS_SHEET_NAME);
  if (!hoja) throw new Error('Hoja "Usuarios" no encontrada en BD de clientes');

  var lastRow = hoja.getLastRow();
  var lastCol = hoja.getLastColumn();
  if (lastRow <= 1) throw new Error('BD de clientes sin datos');

  var headers = hoja.getRange(1,1,1,lastCol).getValues()[0].map(String);
  var H = headers.map(normalizeHeader);

  var idxAlias = H.indexOf(normalizeHeader('ALIAS'));
  var idxUrl   = H.indexOf(normalizeHeader('URL'));

  if (idxAlias === -1 || idxUrl === -1) {
    throw new Error('Faltan columnas Alias/URL en hoja Usuarios');
  }

  var datos = hoja.getRange(2,1,lastRow-1,lastCol).getValues();

  for (var i=0; i<datos.length; i++){
    var row = datos[i];
    var aliasCellNorm = normalize(row[idxAlias] || '');
    if (aliasCellNorm && aliasCellNorm === aliasNorm) {
      var url = String(row[idxUrl] || '').trim();
      var id  = extractFolderIdFromUrl(url);
      if (!id) throw new Error('URL de carpeta integral inválida para alias');
      return DriveApp.getFolderById(id);
    }
  }

  throw new Error('Alias no encontrado en hoja Usuarios');
}


/***** 7) UTILIDADES FECHAS / TEXTO *****/

function parseMesNumero(m){
  var s = String(m || '').trim().toLowerCase();
  s = s.normalize('NFD').replace(/[\u0300-\u036f]/g,'');

  if (!s) return NaN;

  var n = parseInt(s,10);
  if (!isNaN(n) && n>=1 && n<=12) return n;

  var nombres = [
    'enero','febrero','marzo','abril','mayo','junio',
    'julio','agosto','septiembre','octubre','noviembre','diciembre'
  ];
  var idx = nombres.indexOf(s);
  if (idx !== -1) return idx+1;

  return NaN;
}

function monthNameEs(m){
  var nombres = [
    'ENERO','FEBRERO','MARZO','ABRIL','MAYO','JUNIO',
    'JULIO','AGOSTO','SEPTIEMBRE','OCTUBRE','NOVIEMBRE','DICIEMBRE'
  ];
  return nombres[m-1] || '';
}

function toTwoDigits(n){
  n = parseInt(n,10);
  return (n<10 ? '0' : '') + n;
}

function parseAnyDate(v){
  if (!v) return null;
  if (Object.prototype.toString.call(v) === '[object Date]') {
    return isNaN(v.getTime()) ? null : v;
  }
  var d = new Date(v);
  if (!isNaN(d.getTime())) return d;

  var s = String(v).trim();
  var parts = s.split('/');
  if (parts.length === 3) {
    var dd = parseInt(parts[0],10);
    var mm = parseInt(parts[1],10)-1;
    var yy = parseInt(parts[2],10);
    var d2 = new Date(yy,mm,dd);
    if (!isNaN(d2.getTime())) return d2;
  }
  return null;
}


/***** 8) UTILIDADES NÚMEROS / DRIVE *****/

function toNumber(v){
  if (v == null || v === '') return 0;
  var n = Number(v);
  return isNaN(n) ? 0 : n;
}

function extractFolderIdFromUrl(url){
  if (!url) return '';
  var m = url.match(/\/folders\/([a-zA-Z0-9_-]+)/);
  if (m && m[1]) return m[1];
  if (/^[a-zA-Z0-9_-]{20,}$/.test(url)) return url;
  return '';
}

function ensureChildFolder(parentFolder, name){
  var it = parentFolder.getFoldersByName(name);
  if (it.hasNext()) return it.next();
  return parentFolder.createFolder(name);
}

function getExistingChildFolder(parentFolder, name){
  var it = parentFolder.getFoldersByName(name);
  return it.hasNext() ? it.next() : null;
}

function sanitizeForFilename(name){
  return String(name || '').replace(/[\\\/:*?"<>|]/g,' ').trim();
}

function makeUniquePdfName(folder, baseName){
  var name = baseName + '.pdf';
  var n = 2;
  while (folder.getFilesByName(name).hasNext()) {
    name = baseName + '_(' + n + ').pdf';
    n++;
  }
  return name;
}

function exportDocToPdfBlob(docId, pdfName){
  var docFile = DriveApp.getFileById(docId);
  var blob = docFile.getAs('application/pdf').setName(pdfName);
  return blob;
}


/***** 9) NORMALIZACIÓN + HELPERS *****/

function normalize(v){
  v = String(v||'').trim().toUpperCase();
  v = v.normalize('NFD').replace(/[\u0300-\u036f]/g,'');
  v = v.replace(/\s+/g,' ');
  return v;
}

function normalizeHeader(v){
  v = String(v||'').trim().toUpperCase();
  v = v.normalize('NFD').replace(/[\u0300-\u036f]/g,'');
  v = v.replace(/\s+/g,' ');
  return v;
}

function safe(v){
  return v == null ? '' : String(v);
}

function json(o){
  return ContentService
    .createTextOutput(JSON.stringify(o))
    .setMimeType(ContentService.MimeType.JSON);
}


/***** 10) LOG DE REPORTES EN SHEETS *****/

function logReportGeneration(usuario, aliasRaw, mesNum, anio, fileId, fileName, folderPath){
  try {
    var ss   = SpreadsheetApp.openById(LOG_SPREADSHEET_ID);
    var hoja = ss.getSheetByName(LOG_SHEET_NAME);
    if (!hoja) {
      hoja = ss.insertSheet(LOG_SHEET_NAME);
    }

    var lastRow = hoja.getLastRow();
    if (lastRow === 0) {
      hoja.appendRow(['Timestamp','Usuario','Alias','Mes','Anio','FileId','FileName','FolderPath']);
    }

    hoja.appendRow([
      new Date(),
      usuario || '',
      aliasRaw || '',
      mesNum,
      anio,
      fileId || '',
      fileName || '',
      folderPath || ''
    ]);
  } catch (e) {
    // No rompemos la generación de reporte si falla el log
  }
}

function lookupReportInLog(aliasRaw, mesNum, anio){
  try {
    var ss   = SpreadsheetApp.openById(LOG_SPREADSHEET_ID);
    var hoja = ss.getSheetByName(LOG_SHEET_NAME);
    if (!hoja) return null;

    var lastRow = hoja.getLastRow();
    if (lastRow <= 1) return null;

    // columnas: Timestamp, Usuario, Alias, Mes, Anio, FileId, FileName, FolderPath
    var numRows = lastRow - 1;
    var data    = hoja.getRange(2,1,numRows,8).getValues();

    var aliasNorm = normalize(aliasRaw);

    // buscamos desde abajo (más reciente hacia atrás)
    for (var i = data.length - 1; i >= 0; i--){
      var row = data[i];
      var aliasCellNorm = normalize(row[2] || '');
      var mesCell  = parseInt(row[3],10);
      var anioCell = parseInt(row[4],10);
      var fileId   = String(row[5] || '').trim();

      if (!fileId) continue;
      if (aliasCellNorm !== aliasNorm) continue;
      if (mesCell !== mesNum) continue;
      if (anioCell !== anio) continue;

      return {
        fileId: fileId,
        fileName: String(row[6] || '').trim(),
        folderPath: String(row[7] || '').trim()
      };
    }

    return null;
  } catch (e) {
    return null;
  }
}