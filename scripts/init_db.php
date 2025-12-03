<?php
require_once __DIR__ . '/../lib/db.php';
ensureSchema();
insertUser(
  'Q1120W43',
  '11030724#0953.',
  'https://drive.google.com/drive/folders/1EFTX8x4a4yD6vPRrttByR8UD9wJFQxVy?usp=drive_link',
  'corporativo@120w.mx',
  true,
  '120W'
);
insertUser(
  'Q1Grados26',
  '1G030724#0953.',
  'https://drive.google.com/drive/folders/1VT-WeTN3ZCd8GwbFufIoHruQtG3xaj5S?usp=drive_link',
  'contabilidad130qro@gmail.com',
  true,
  '130 Grados'
);
updateServicesCount('Q1120W43',52);
echo "OK\n";
