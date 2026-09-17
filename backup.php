<?php
/**
 * Резервная копия контента: скачивает все data/*.json одним архивом.
 *
 * Использование: https://uitop.ru/backup.php?key=ПАРОЛЬ_АДМИНКИ
 * Скачается файл pcm-backup-ГГГГММДД-ЧЧММ.zip (или .json, если zip недоступен).
 *
 * Восстановление: распаковать архив и залить json-файлы по FTP в папку /data/.
 */
@ini_set('display_errors', '0');
error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);
require __DIR__ . '/inc/config.php';
if (($_GET['key'] ?? '') !== $ADMIN_PASSWORD) { http_response_code(403); die('Нет доступа: добавьте ?key=ПАРОЛЬ_АДМИНКИ'); }

$dir = __DIR__ . '/data';
$files = glob($dir . '/*.json');
if (!$files) { http_response_code(404); die('В папке data/ нет json-файлов'); }
$stamp = date('Ymd-Hi');

if (class_exists('ZipArchive')) {
  $tmp = tempnam(sys_get_temp_dir(), 'pcmbak');
  $zip = new ZipArchive();
  $zip->open($tmp, ZipArchive::OVERWRITE);
  foreach ($files as $f) $zip->addFile($f, 'data/' . basename($f));
  $zip->close();
  header('Content-Type: application/zip');
  header('Content-Disposition: attachment; filename="pcm-backup-' . $stamp . '.zip"');
  header('Content-Length: ' . filesize($tmp));
  readfile($tmp);
  @unlink($tmp);
} else {
  // Без zip: один общий JSON {"cars.json": [...], ...}
  $all = [];
  foreach ($files as $f) $all[basename($f)] = json_decode(file_get_contents($f), true);
  header('Content-Type: application/json; charset=utf-8');
  header('Content-Disposition: attachment; filename="pcm-backup-' . $stamp . '.json"');
  echo json_encode($all, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
}
