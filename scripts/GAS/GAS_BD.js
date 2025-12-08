/***** CONFIG *****/
const SPREADSHEET_ID = '1BvnfT3XX5ayUaD8DWX5PyIgzjqpYYrv37AK8iTImhAQ';
const SHEET_ID       = 737149364;   // Hoja de OPERACIONES (BD RELLENO 2025)
const SHEET_FIN_ID   = 1176358526;  // Hoja de FINANZAS


/***** ROUTER *****/
function doGet(e){ return handle(e); }
function doPost(e){ return handle(e); }

function handle(e){
  var action = String(e && e.parameter && e.parameter.action || '').trim();
  // alias puede venir vacío; normalize aguanta string vacío
  var rawAlias = e && e.parameter && e.parameter.alias || '';
  var alias    = normalize(rawAlias);

  if (action === 'metrics') {
    // OPERACIONES (recolecciones)
    var mesParam  = String(e.parameter && (e.parameter.mes  || e.parameter.month) || '').trim();
    var anioParam = String(e.parameter && (e.parameter.anio || e.parameter.year ) || '').trim();
    return json(getMetrics(alias, mesParam, anioParam));
  }

  if (action === 'diagnose') {
    // Diagnóstico OPERACIONES
    return json(diagnose(alias));
  }

  if (action === 'finanzas') {
    // FINANZAS (facturación / excesos / tarifa)
    return json(getFinanzas(alias));
  }

  if (action === 'diagnoseFinanzas') {
    // Diagnóstico FINANZAS
    return json(diagnoseFinanzas(alias));
  }

  return json({ok:false,error:'accion invalida'});
}


/***** MÓDULO OPERACIONES (BD RELLENO) *****/

/**
 * Métricas por cliente, con filtro opcional por mes/año.
 * Ejemplos:
 *   .../exec?action=metrics&alias=120W
 *   .../exec?action=metrics&alias=120W&mes=abril&anio=2025
 */
function getMetrics(alias, mesParam, anioParam){
  if (!alias) return {ok:false,error:'Alias requerido'};

  var hoja = sheet(); // OPERACIONES
  if (!hoja) return {ok:false,error:'Hoja de operaciones no encontrada'};

  var lastRow = hoja.getLastRow();
  var lastCol = hoja.getLastColumn();
  if (lastRow <= 1) {
    return { ok:true, serviciosRealizados:0, ultimosServicios:[], resumenPorMes:[] };
  }

  // Detectar encabezados (CLIENTES / RAZON SOCIAL)
  var info = detectarEncabezados(hoja);
  var headerRow = info.headerRow;
  var headers   = info.headers;
  var IDX       = info.indices;

  if (IDX.CLIENTES === -1 && IDX.RAZON === -1) {
    return {
      ok:false,
      error:'No se encontró columna de CLIENTES / RAZÓN SOCIAL en las primeras filas',
      headerRow: headerRow,
      headers: headers,
      indices: IDX
    };
  }

  var dataStartRow = headerRow + 1;
  if (lastRow <= headerRow) {
    return { ok:true, serviciosRealizados:0, ultimosServicios:[], resumenPorMes:[] };
  }

  var datos = hoja.getRange(dataStartRow, 1, lastRow - headerRow, lastCol).getValues();

  var filtroPeriodo = parsePeriodoParams(mesParam, anioParam); // {activo, mes, anio}
  var servicios = 0;
  var out = [];
  var totMes = {}; // { '2025-04': 12, ... }

  for (var j = 0; j < datos.length; j++){
    var row = datos[j];

    var cliente = IDX.CLIENTES > -1 ? row[IDX.CLIENTES] : '';
    var razon   = IDX.RAZON    > -1 ? row[IDX.RAZON]    : '';

    if (!coincideConAlias(alias, cliente, razon)) continue;

    // Fecha para checks de periodo
    var d = null;
    if ( IDX.C > -1 ) {
      var fecha = row[IDX.C];
      d = fecha instanceof Date ? fecha : new Date(fecha);
      if (isNaN(d.getTime())) d = null;
    }

    // Aplicar filtro de mes/año si está activo
    if (filtroPeriodo.activo && d) {
      if (filtroPeriodo.mes && (d.getMonth()+1) !== filtroPeriodo.mes) continue;
      if (filtroPeriodo.anio && d.getFullYear() !== filtroPeriodo.anio) continue;
    }

    servicios++;

    // Contar por mes (para resumen)
    if (d) {
      var mesKey = d.getFullYear() + '-' + ('0' + (d.getMonth()+1)).slice(-2); // "2025-04"
      totMes[mesKey] = (totMes[mesKey] || 0) + 1;
    }

    out.push({
      c: safe(IDX.C > -1 ? row[IDX.C] : ''),  // FECHA DE RECOLECCION
      f: safe(IDX.F > -1 ? row[IDX.F] : ''),  // TIPO DE RESIDUO
      h: safe(IDX.H > -1 ? row[IDX.H] : ''),  // REMISION
      q: safe(IDX.Q > -1 ? row[IDX.Q] : ''),  // m3
      s: safe(IDX.S > -1 ? row[IDX.S] : ''),  // Kg / PESO NETO / TOTAL KG
      t: safe(IDX.T > -1 ? row[IDX.T] : ''),  // EXCESOS
      u: safe(IDX.U > -1 ? row[IDX.U] : ''),  // VOLUMEN CONTRATADO
      x: safe(IDX.X > -1 ? row[IDX.X] : '')   // TARIFA
    });
  }

  // Solo últimos 50 servicios
  out = out.slice(-50);

  // Resumen por mes, con nombre de mes en minúsculas
  var resumenPorMes = Object.keys(totMes).sort().map(function(k){
    var year  = parseInt(k.slice(0,4), 10);
    var month = parseInt(k.slice(5,7), 10);
    return {
      mes: k,                       // "2025-01"
      anio: year,                   // 2025
      mesTexto: mesNombreES(month), // "enero", "febrero", etc.
      servicios: totMes[k]
    };
  });

  return {
    ok:true,
    alias: alias,
    filtroPeriodo: filtroPeriodo,
    serviciosRealizados: servicios,
    ultimosServicios: out,
    resumenPorMes: resumenPorMes
  };
}

/**
 * Diagnóstico de OPERACIONES.
 */
function diagnose(alias){
  var hoja = sheet();
  if (!hoja) return {ok:false,error:'Hoja de operaciones no encontrada'};

  var info = detectarEncabezados(hoja);
  var headerRow = info.headerRow;
  var headers   = info.headers;
  var IDX       = info.indices;
  var total     = hoja.getLastRow() - headerRow;

  return {
    ok:true,
    alias: alias,
    headerRow: headerRow,
    totalRowsDatos: total,
    indices: IDX,
    headers: headers
  };
}

/**
 * Detecta fila de encabezados en hoja de OPERACIONES.
 */
function detectarEncabezados(hoja) {
  var lastRow = hoja.getLastRow();
  var lastCol = hoja.getLastColumn();
  var maxScan = Math.min(lastRow, 10);

  var rango = hoja.getRange(1, 1, maxScan, lastCol).getValues();
  for (var r = 0; r < maxScan; r++) {
    var headers = rango[r].map(String);
    var IDX = indices(headers);
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
    indices: indices(headers0)
  };
}

/**
 * Hoja de OPERACIONES.
 */
function sheet(){
  var ss = SpreadsheetApp.openById(SPREADSHEET_ID);
  var list = ss.getSheets();
  for (var i = 0; i < list.length; i++){
    if (list[i].getSheetId() === SHEET_ID) return list[i];
  }
  return null;
}

/**
 * Mapeo encabezados → índices para OPERACIONES.
 */
function indices(h){
  var H = h.map(normalize);

  function find(names){
    for (var i=0; i<names.length; i++){
      var ref = normalize(names[i]);
      var k = H.indexOf(ref);
      if (k>-1) return k;
    }
    return -1;
  }

  return {
    CLIENTES: find(['CLIENTES','CLIENTE']),
    RAZON:    find(['RAZÓN SOCIAL','RAZON SOCIAL']),
    C:        find(['FECHA DE RECOLECCION','FECHA DE RECOLECCIÓN']),
    F:        find(['TIPO DE RESIDUO']),
    H:        find(['REMISION','REMISIÓN']),
    Q:        find(['m3']),
    S:        find(['Kg','PESO NETO','TOTAL KG','TOTAL KG (COLUMNA N)']),
    T:        find(['EXCESOS']),
    U:        find(['VOLUMEN CONTRATADO']),
    X:        find(['TARIFA'])
  };
}

/**
 * Coincidencia flexible de alias vs cliente/razón en OPERACIONES.
 */
function coincideConAlias(aliasBuscado, cliente, razon) {
  aliasBuscado = normalize(aliasBuscado || '');
  cliente      = normalize(cliente      || '');
  razon        = normalize(razon        || '');

  if (!aliasBuscado) return false;

  var campos = [cliente, razon];
  for (var i = 0; i < campos.length; i++) {
    var v = campos[i];
    if (!v) continue;

    if (v === aliasBuscado) return true;
    if (v.indexOf(aliasBuscado) !== -1) return true;
  }
  return false;
}


/***** MÓDULO FINANZAS *****/

/**
 * Finanzas por cliente (primero ALIAS, luego RAZON SOCIAL).
 * Ejemplo:
 *   .../exec?action=finanzas&alias=120W
 */
function getFinanzas(alias){
  if (!alias) return {ok:false,error:'Alias requerido'};

  var hoja = sheetFinanzas();
  if (!hoja) return {ok:false,error:'Hoja de finanzas no encontrada'};

  var lastRow = hoja.getLastRow();
  var lastCol = hoja.getLastColumn();
  if (lastRow <= 1) {
    return { ok:true, alias: alias, encontrados:0, resultados:[] };
  }

  // Detectar encabezados por ALIAS / RAZON SOCIAL
  var info = detectarEncabezadosFinanzas(hoja);
  var headerRow = info.headerRow;
  var headers   = info.headers;
  var IDX       = info.indices;

  if (IDX.ALIAS === -1 && IDX.RAZON === -1) {
    return {
      ok:false,
      error:'No se encontró columna de ALIAS / RAZON SOCIAL en las primeras filas',
      headerRow: headerRow,
      headers: headers,
      indices: IDX
    };
  }

  var dataStartRow = headerRow + 1;
  if (lastRow <= headerRow) {
    return { ok:true, alias: alias, encontrados:0, resultados:[] };
  }

  var datos = hoja.getRange(dataStartRow, 1, lastRow - headerRow, lastCol).getValues();

  // Primero coincidencias por ALIAS, luego por RAZON SOCIAL
  var aliasMatches = [];
  var razonMatches = [];

  for (var j=0; j<datos.length; j++){
    var row = datos[j];

    var aliasCelda = IDX.ALIAS > -1 ? row[IDX.ALIAS] : '';
    var razonCelda = IDX.RAZON > -1 ? row[IDX.RAZON] : '';

    if (coincideValor(alias, aliasCelda)) {
      aliasMatches.push(row);
    } else if (coincideValor(alias, razonCelda)) {
      razonMatches.push(row);
    }
  }

  var usados = aliasMatches.length ? aliasMatches : razonMatches;

  var resultados = usados.map(function(row){
    return {
      alias:                  safe(IDX.ALIAS > -1 ? row[IDX.ALIAS] : ''),
      razonSocial:            safe(IDX.RAZON > -1 ? row[IDX.RAZON] : ''),
      factura:                safe(IDX.FACTURA > -1 ? row[IDX.FACTURA] : ''),
      serviciosRealizados:    safe(IDX.SERVICIOS_REALIZADOS > -1 ? row[IDX.SERVICIOS_REALIZADOS] : ''),
      volumenContratadoM3:    safe(IDX.VOL_CONTRATADO > -1 ? row[IDX.VOL_CONTRATADO] : ''),
      excesosM3:              safe(IDX.EXCESOS_M3 > -1 ? row[IDX.EXCESOS_M3] : ''),
      tarifaSinIva:           safe(IDX.TARIFA_SIN_IVA > -1 ? row[IDX.TARIFA_SIN_IVA] : ''),
      tarifaExcesoM3SinIva:   safe(IDX.TARIFA_EXCESO_SIN_IVA > -1 ? row[IDX.TARIFA_EXCESO_SIN_IVA] : '')
    };
  });

  return {
    ok:true,
    alias: alias,
    encontrados: resultados.length,
    resultados: resultados
  };
}

/**
 * Diagnóstico FINANZAS.
 */
function diagnoseFinanzas(alias){
  var hoja = sheetFinanzas();
  if (!hoja) return {ok:false,error:'Hoja de finanzas no encontrada'};

  var info = detectarEncabezadosFinanzas(hoja);
  var headerRow = info.headerRow;
  var headers   = info.headers;
  var IDX       = info.indices;
  var total     = hoja.getLastRow() - headerRow;

  return {
    ok:true,
    alias: alias,
    headerRow: headerRow,
    totalRowsDatos: total,
    indices: IDX,
    headers: headers
  };
}

/**
 * Detecta fila de encabezados en FINANZAS (buscando ALIAS / RAZON SOCIAL).
 */
function detectarEncabezadosFinanzas(hoja){
  var lastRow = hoja.getLastRow();
  var lastCol = hoja.getLastColumn();
  var maxScan = Math.min(lastRow, 10);

  var rango = hoja.getRange(1,1,maxScan,lastCol).getValues();
  for (var r=0; r<maxScan; r++){
    var headers = rango[r].map(String);
    var IDX = indicesFinanzas(headers);
    if (IDX.ALIAS > -1 || IDX.RAZON > -1) {
      return {
        headerRow: r+1,
        headers: headers,
        indices: IDX
      };
    }
  }

  var headers0 = rango[0].map(String);
  return {
    headerRow: 1,
    headers: headers0,
    indices: indicesFinanzas(headers0)
  };
}

/**
 * Hoja de FINANZAS.
 */
function sheetFinanzas(){
  var ss = SpreadsheetApp.openById(SPREADSHEET_ID);
  var list = ss.getSheets();
  for (var i=0; i<list.length; i++){
    if (list[i].getSheetId() === SHEET_FIN_ID) return list[i];
  }
  return null;
}

/**
 * Mapeo encabezados → índices para FINANZAS.
 * (Usamos "contains" para aguantar variaciones ligeras.)
 */
function indicesFinanzas(h){
  var H = h.map(function(v){ return normalize(v); });

  function find(patterns){
    for (var i=0; i<H.length; i++){
      var col = H[i];
      for (var j=0; j<patterns.length; j++){
        var pat = normalize(patterns[j]);
        if (col === pat || col.indexOf(pat) !== -1) return i;
      }
    }
    return -1;
  }

  return {
    ALIAS:                find(['ALIAS']),
    RAZON:                find(['RAZON SOCIAL','RAZÓN SOCIAL']),
    FACTURA:              find(['FACTURA']),
    SERVICIOS_REALIZADOS: find(['NO. DE SERVICIOS REALIZADOS','SERVICIOS REALIZADOS']),
    VOL_CONTRATADO:       find(['VOLUMEN CONTRATADO (M3)','VOLUMEN CONTRATADO']),
    EXCESOS_M3:           find(['EXCESOS (M3)','EXCESOS']),
    TARIFA_SIN_IVA:       find(['TARIFA (SIN IVA)','TARIFA SIN IVA']),
    TARIFA_EXCESO_SIN_IVA:find(['$ M3 EXCESO (SIN IVA)','$ M3 EXCESO'])
  };
}

/**
 * Coincidencia simple valor↔alias (para ALIAS / RAZON en FINANZAS).
 */
function coincideValor(aliasBuscado, celda){
  aliasBuscado = normalize(aliasBuscado || '');
  celda        = normalize(celda        || '');
  if (!aliasBuscado || !celda) return false;
  return celda === aliasBuscado || celda.indexOf(aliasBuscado) !== -1;
}


/***** HELPERS GENERALES *****/

/**
 * Interpreta mes/anio desde la URL para OPERACIONES.
 */
function parsePeriodoParams(mesParam, anioParam) {
  var res = {activo:false, mes:null, anio:null};

  // Año
  if (anioParam) {
    var y = parseInt(anioParam, 10);
    if (!isNaN(y)) res.anio = y;
  }

  // Mes
  if (mesParam) {
    var raw = normalizarMesTexto(mesParam);

    // 1) número (ej: "2025-04", "04", "4")
    var mMatch = raw.match(/(\d{1,2})/);
    if (mMatch) {
      var m = parseInt(mMatch[1], 10);
      if (m >= 1 && m <= 12) {
        res.mes = m;
      }
    } else {
      // 2) nombre del mes, ej: "abril"
      var nombres = [
        'enero','febrero','marzo','abril','mayo','junio',
        'julio','agosto','septiembre','octubre','noviembre','diciembre'
      ];
      var idx = nombres.indexOf(raw);
      if (idx !== -1) res.mes = idx + 1;
    }
  }

  if (res.mes || res.anio) res.activo = true;
  return res;
}

/**
 * Nombre de mes en español, minúsculas.
 */
function mesNombreES(m){
  var nombres = [
    'enero','febrero','marzo','abril','mayo','junio',
    'julio','agosto','septiembre','octubre','noviembre','diciembre'
  ];
  if (m < 1 || m > 12) return '';
  return nombres[m - 1];
}

function normalizarMesTexto(s){
  s = String(s || '').trim().toLowerCase();
  s = s.normalize('NFD').replace(/[\u0300-\u036f]/g,'');
  return s;
}

/**
 * Normaliza texto general.
 */
function normalize(v){
  v = String(v || '').trim().toUpperCase();
  v = v.normalize('NFD').replace(/[\u0300-\u036f]/g,'');
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