function onOpen() {
  var ui = SpreadsheetApp.getUi();
  ui.createMenu("Contratos")
    .addItem("Generar Contrato", "generarContrato")
    .addItem('Enviar contrato', 'openModal')
    .addItem("Borrar Datos", "borrarDatos")
    .addToUi();
}

function verificarPermiso() {
  var email = Session.getActiveUser().getEmail();
  var sheetMCV = SpreadsheetApp.getActiveSpreadsheet().getSheetByName("Llenado x MCV");

  if (!email.endsWith("@mediosconvalor.com")) {
    SpreadsheetApp.getUi().alert("No tienes permiso para ejecutar esta función.");
    sheetMCV.hideSheet(); // Ocultamos la hoja si el usuario no tiene permiso
    throw new Error("Acceso denegado");
  } else {
    sheetMCV.showSheet(); // Mostramos la hoja si el usuario tiene el dominio correcto
  }
}

function generarContrato() {
  verificarPermiso();
  
  // Hoja y datos de "Llenado x Cliente"
  var sheet = SpreadsheetApp.getActiveSpreadsheet().getSheetByName("Llenado x Cliente");
  var data = sheet.getRange("D:D").getValues().flat();
  
  // Hoja y datos de "Llenado x MCV"
  var sheetMCV = SpreadsheetApp.getActiveSpreadsheet().getSheetByName("Llenado x MCV");
  var dataMCV = sheetMCV.getRange(2, 4, sheetMCV.getLastRow() - 1, 3).getValues(); // Ahora incluye D, E y F correctamente

  // Verificamos si alguna celda está vacía en "Llenado x Cliente"
  var celdasRequeridas = [
    data[6], data[7], data[8], data[9], data[10], data[11], data[12], 
    data[15], data[16], data[17], data[20], data[21], data[22], 
    data[25], data[26], data[27], data[29], data[30], data[31], 
    data[32], data[35], data[36], data[37], data[38], data[39], 
    data[40], data[41], data[42], data[43]
  ];

  var celdasVacias = celdasRequeridas.some(function(celda) {
    return !celda;
  });
  
  if (celdasVacias) {
    SpreadsheetApp.getUi().alert("Por favor, asegúrate de llenar todos los campos antes de generar el contrato.");
    return;
  }

  // Plantilla y creación del contrato
  var plantillaId = "1ykTiZNgS9NIqvV9bcCu6SbOurFPH5KsZVMoFuZpd158";
  var plantilla = DriveApp.getFileById(plantillaId);
  var carpetaMadre = plantilla.getParents().next();
  var añoActual = new Date().getFullYear();

  var nombreContrato = " qro_Contrato " + data[6] + " y MCV " + añoActual;
  var nuevaCopia = plantilla.makeCopy(nombreContrato, carpetaMadre);
  var docId = nuevaCopia.getId();
  var doc = DocumentApp.openById(docId);
  var body = doc.getBody();

  var reemplazos = {
    "<nombre comercial>": data[6],
    "<nombre fiscal>": data[7],
    "<rfc>": data[8],
    "<regimen fiscal>": data[9],
    "<direccion fiscal>": data[10],
    "<direccion servicio>": data[11],
    "<telefonos>": data[12],
    "<nombre recoleccion>": data[15],
    "<telefono recoleccion>": data[16],
    "<correo recoleccion>": data[17],
    "<nombre compras>": data[20],
    "<telefono compras>": data[21],
    "<correo compras>": data[22],
    "<nombre pagos>": data[25],
    "<telefono pagos>": data[26],
    "<correo pagos>": data[27],
    "<correo facturas>": data[29],
    "<banco>": data[30],
    "<4 digitos>": data[31],
    "<clabe>": data[32],
    "<representante legal>": data[35],
    "<municipio fiscal>": data[36],
    "<escritura publica>": data[37],
    "<volumen o tomo>": data[38],
    "<fecha de registro>": data[39],
    "<no de notaria>": data[40],
    "<notario titular>": data[41],
    "<registro público>": data[42],
    "<estado>": data[43],

    // **Datos de "Llenado x MCV" con corrección de filas y columnas**
    "<no de contrato>": dataMCV[19][0] + "/0" + dataMCV[19][2],
    "<representante legal mcv>": dataMCV[21][0],
    "<fecha de inicio>": new Date(dataMCV[20][0]).toLocaleDateString('es-ES', {
      day: '2-digit',
      month: 'long',
      year: 'numeric'
    }),
    "<tipo de cliente>": dataMCV[22][0],
    "<frecuencia>": dataMCV[23][0] + " " + dataMCV[23][1],
    "<precio x servicio>": Utilities.formatString("$%.2f", parseFloat(dataMCV[25][0])),
    "<volumen contratado>": dataMCV[24][0] + " " + dataMCV[24][2],
    "<exceso x m3>": Utilities.formatString("$%.2f", parseFloat(dataMCV[26][0])),
    "<user>": dataMCV[30][0],
    "<pass>": dataMCV[31][0]
  };

  // Reemplazamos los valores en la plantilla
  for (var key in reemplazos) {
    body.replaceText(key, reemplazos[key] || "");
  }

  doc.saveAndClose();

  // Mostrar la alerta de éxito con el enlace al contrato generado
  var html = HtmlService.createHtmlOutput(
    '<div style="background-color:#00dc2a; color:#535a6b; padding:20px; border-radius:10px; text-align:center; font-size:16px;">' +
    '<h2 style="color:#009eff;">¡Contrato generado con éxito!</h2>' +
    '<p><a href="' + nuevaCopia.getUrl() + '" target="_blank" style="color:#009eff; font-weight:bold;">Abrir contrato</a></p>' +
    '</div>'
  ).setWidth(400).setHeight(200);

  SpreadsheetApp.getUi().showModalDialog(html, "Contrato Generado");

  // **Enviar notificación al dueño del documento**
  notificarRegistroCompleto(docId);
}

function notificarRegistroCompleto(docId) {
  var sheet = SpreadsheetApp.getActiveSpreadsheet().getSheetByName("Llenado x Cliente");
  var data = sheet.getRange("D:D").getValues().flat();

  // Verificamos si las celdas están llenas
  var celdasRequeridas = [
    data[6], data[7], data[8], data[9], data[10], data[11], data[12], 
    data[15], data[16], data[17], data[20], data[21], data[22], 
    data[25], data[26], data[27], data[29], data[30], data[31], 
    data[32], data[35], data[36], data[37], data[38], data[39], 
    data[40], data[41], data[42], data[43]
  ];

  var celdasVacias = celdasRequeridas.some(function(celda) {
    return !celda;
  });

  if (!celdasVacias) {
    // Obtener el dueño del documento
    var propietario = SpreadsheetApp.getActiveSpreadsheet().getOwner().getEmail(); // Obtiene el correo del propietario del documento
    var nombreComercial = data[6]; // Nombre comercial del cliente
    var enlaceContrato = "Contrato generado: " + "https://docs.google.com/document/d/" + docId; // Enlace al contrato generado
    
    // Enviar el correo al propietario del documento
    var asunto = "Nuevo contrato registrado: " + nombreComercial;
    var mensaje = "El cliente " + nombreComercial + " ha registrado toda la información en el contrato.\n\nPuedes revisar el documento aquí: " + enlaceContrato;
    
    // Cuerpo del correo con imagen como firma
    var firma = '<br><br><img src="https://drive.google.com/uc?export=view&id=1I7JzPA-XSKY1-GSJE5PTyLhwa_nAJ_vv" alt="Firma" width="400"/>';
    
    GmailApp.sendEmail(
      propietario, 
      asunto, 
      mensaje + firma, 
      { htmlBody: mensaje + firma } // Para enviar el correo en formato HTML con la imagen de la firma
    );
  }
}

function borrarDatos() {
  borrarDatosLlenadoxCliente();
  eliminarLlenadoxMCV()
}

function borrarDatosLlenadoxCliente() {
  verificarPermiso();
  var sheet = SpreadsheetApp.getActiveSpreadsheet().getSheetByName("Llenado x Cliente");
  var filas = [6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36, 37, 38, 39, 40, 41, 42, 43, 44];
  for (var i = 0; i < filas.length; i++) {
    sheet.getRange(filas[i], 4).setValue(""); 
  }
}

function eliminarLlenadoxMCV() {
  var spreadsheet = SpreadsheetApp.getActive();
  var sheet = spreadsheet.getSheetByName('Llenado x MCV'); // Seleccionamos la hoja "Llenado x MCV"
  
  // Limpiar los rangos especificados en la hoja "Llenado x MCV"
  sheet.getRange('F21').clear({contentsOnly: true, skipFilteredRows: true});
  sheet.getRange('D23:F23').clear({contentsOnly: true, skipFilteredRows: true});
  sheet.getRange('D25').clear({contentsOnly: true, skipFilteredRows: true});
  sheet.getRange('D26:E26').clear({contentsOnly: true, skipFilteredRows: true});
  sheet.getRange('D27:F27').clear({contentsOnly: true, skipFilteredRows: true});
  sheet.getRange('D32:F33').clear({contentsOnly: true, skipFilteredRows: true});
}

function obtenerNombreDocumento() {
    return SpreadsheetApp.getActiveSpreadsheet().getName();
}

// Abre la ventana emergente con el formulario
function openModal() {
  var html = HtmlService.createHtmlOutputFromFile('modalForm')
    .setWidth(400)
    .setHeight(300);
  SpreadsheetApp.getUi().showModalDialog(html, 'Enviar PDF');
}

// Obtiene los documentos de Google Docs dentro de una carpeta específica
function getFilesFromFolder() {
  var folderId = '1WD-1Ea7XAiY0rBVlyyCCCFpvVxgukqQ-';  // ID de la carpeta de Drive
  var folder = DriveApp.getFolderById(folderId);
  var files = folder.getFiles();
  var fileArray = [];

  while (files.hasNext()) {
    var file = files.next();
    fileArray.push({ id: file.getId(), name: file.getName() });
  }

  return fileArray;
}

function sendDocAsPdfByEmail(fileId, recipient) {
  try {
    var folder = DriveApp.getFolderById('1WD-1Ea7XAiY0rBVlyyCCCFpvVxgukqQ-'); // Carpeta con los documentos
    var files = folder.getFilesByType(MimeType.GOOGLE_DOCS); // Solo archivos de tipo Google Docs

    var file; 
    // Buscar el documento correcto
    while (files.hasNext()) {
      var currentFile = files.next();
      if (currentFile.getId() === fileId) {
        file = currentFile;
        break;
      }
    }

    if (!file) {
      throw new Error('Documento no encontrado en la carpeta');
    }

    var pdf = file.getAs('application/pdf');
    
    // Obtener nombre del archivo y convertirlo en PDF
    var fileName = file.getName();
    var pdfName = fileName.replace(/\.docx$/, "") + ".pdf";

    // Obtener el nombre comercial de la nomenclatura del documento
    var nombreComercial = fileName.split("qro_Contrato ")[1].split(" y MCV")[0];

    // Determinar el número de celular según el correo
    var email = Session.getActiveUser().getEmail();
    var numeroCelular = "4423565508";  // Número por defecto
    if (email.includes("ags")) {
      numeroCelular = "4492832288";
    } else if (email.includes("qro")) {
      numeroCelular = "4461385019";
    } else if (email.includes("mty")) {
      numeroCelular = "8184689400";
    } else if (email.includes("rsaucedo")) {
      numeroCelular = "4491556254";
    } else if (email.includes("aguzman")) {
      numeroCelular = "8115161315";
    }

    // Determinar el emisor del contrato
    var emisorContrato = "Operaciones Mcv";
    if (email.includes("aguzman")) {
      emisorContrato = "Alejandro Guzman";
    } else if (email.includes("rsaucedo")) {
      emisorContrato = "Ricardo Saucedo";
    } else if (email.includes("qro")) {
      emisorContrato = "Eduardo Vázquez";
    } else if (email.includes("ags")) {
      emisorContrato = "Andrea Pacheco";
    } else if (email.includes("mty")) {
      emisorContrato = "Natalia Ibarra";
    }

    // Cuerpo del correo
    var body = `
      Estimado equipo de <strong>${nombreComercial}</strong>,<br><br>
      Espero que este mensaje le encuentre bien. Adjunto a este correo, encontrará el contrato correspondiente a los servicios de recolección de residuos proporcionados por la empresa Medios con Valor.<br><br>
      Le insto cordialmente a que dediquen un tiempo para revisar minuciosamente cada término y condición establecidos en el contrato adjunto. Una vez revisado, le solicito amablemente que se proceda a firmar cada página del mismo para confirmar la aceptación de los términos estipulados.<br><br>
      Por favor, una vez que se haya firmado el contrato, les agradecería que me lo devolvieran por este mismo medio.<br><br>
      Tan pronto como recibamos el contrato debidamente firmado, emitiremos la factura correspondiente como prepago por nuestros servicios. Esto les permitirá realizar el pago y comenzar a coordinar los detalles de acuerdo con los términos del contrato.<br><br>
      Adicionalmente, quisiera solicitar información referente a la ubicación exacta donde se encontrarán los puntos, favor de compartir la información por <a href="https://wa.me/${numeroCelular}">WhatsApp</a> o por un enlace de Google Maps en respuesta a este correo, con imágenes para compartir la información a los choferes en cuanto se realice la entrega del contenedor.<br><br>
      Agradezco sinceramente su confianza en nuestros servicios y espero poder servirle de la mejor manera posible. Quedo a su disposición para cualquier pregunta o aclaración adicional que pueda surgir.<br><br>
      ______________<br><br>
      Saludos cordiales,<br><br>
      ${emisorContrato}<br><br>
      <img src="https://drive.google.com/uc?export=view&id=1nxOV8VXwPDz5iD9lv1y4RUuI5XpRRrf3" width="400" alt="Firma"><br>
      <img src="https://drive.google.com/uc?export=view&id=1I7JzPA-XSKY1-GSJE5PTyLhwa_nAJ_vv" width="400" alt="Mcvealo"><br>
    `;

    // Asunto del correo
    var subject = "Contrato de recolección de residuos - Medios con Valor";

    // Enviar el correo
    MailApp.sendEmail({
      to: recipient,
      subject: subject,
      htmlBody: body,
      attachments: [pdf],
    });

    return { success: true, message: "Correo enviado exitosamente" };
  } catch (error) {
    return { success: false, message: "Error al enviar el correo: " + error.message };
  }
}