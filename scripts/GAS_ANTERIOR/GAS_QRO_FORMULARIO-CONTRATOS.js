function doPost(e) {
  const ss = SpreadsheetApp.openById("1tmQTj0KGUstU_5TuThozuSbu_VkO_bfURbH7aXDeEWE");
  const hoja = ss.getSheetByName("Respuestas") || ss.insertSheet("Respuestas");
  const log = ss.getSheetByName("Log") || ss.insertSheet("Log");

  let data;
  try {
    data = JSON.parse(e.postData.contents);
  } catch (err) {
    log.appendRow([new Date(), "ERROR parsing JSON", e.postData.contents]);
    return ContentService.createTextOutput("ERROR").setMimeType(ContentService.MimeType.TEXT);
  }

  const headers = hoja.getRange(1, 1, 1, hoja.getLastColumn()).getValues()[0];
  const nuevaFila = headers.map(h => data[h] || "");
  hoja.appendRow(nuevaFila);

  return ContentService.createTextOutput("OK").setMimeType(ContentService.MimeType.TEXT);
}