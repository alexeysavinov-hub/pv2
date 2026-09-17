<?php
/**
 * API для админ-панели: GET/PUT /api/?r=cars|slides|news|offers|team, POST /api/?r=login
 *
 * Данные хранятся в JSON-файлах папки data/ — там же, откуда их читает сайт.
 * Поэтому обновление файлов по FTP работает напрямую: залили — сайт показал.
 *
 * Требование: папка data/ должна быть доступна для записи веб-серверу (права 775).
 * Перед каждой записью прежняя версия файла копируется в data/.backups/.
 */
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Methods: GET, PUT, POST, OPTIONS');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

mb_internal_encoding('UTF-8');
@ini_set('display_errors', '0');
error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);
require __DIR__ . '/../inc/config.php';

$RESOURCES = ['cars', 'slides', 'news', 'offers', 'team'];
$DATA_DIR  = __DIR__ . '/../data';
$r = isset($_GET['r']) ? $_GET['r'] : '';
$method = $_SERVER['REQUEST_METHOD'];

function body_json() { return json_decode(file_get_contents('php://input'), true); }

function fail($code, $msg) {
  http_response_code($code);
  echo json_encode(['error' => $msg], JSON_UNESCAPED_UNICODE);
  exit;
}

function require_auth() {
  global $ADMIN_TOKEN;
  $h = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
  if (!$h && function_exists('getallheaders')) {
    foreach (getallheaders() as $k => $v) { if (strtolower($k) === 'authorization') $h = $v; }
  }
  if ($h !== 'Bearer ' . $ADMIN_TOKEN) fail(401, 'Неавторизован');
}

/** Копия прежней версии в data/.backups/ — храним последние 20 на раздел. */
function backup_file($file, $name) {
  if (!is_file($file)) return;
  $dir = dirname($file) . '/.backups';
  if (!is_dir($dir)) @mkdir($dir, 0775, true);
  if (!is_dir($dir)) return;
  @copy($file, $dir . '/' . $name . '-' . date('Ymd-His') . '.json');
  $old = glob($dir . '/' . $name . '-*.json');
  if ($old && count($old) > 20) {
    sort($old);
    foreach (array_slice($old, 0, count($old) - 20) as $f) @unlink($f);
  }
}

/** Атомарная запись: сначала во временный файл, потом переименование. */
function save_json($file, $data) {
  $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
  if ($json === false) return 'не удалось собрать JSON';
  $tmp = $file . '.tmp' . getmypid();
  if (@file_put_contents($tmp, $json) === false) {
    @unlink($tmp);
    return 'нет прав на запись в папку data/ — поставьте ей права 775';
  }
  if (!@rename($tmp, $file)) {
    @unlink($tmp);
    return 'не удалось перезаписать файл ' . basename($file);
  }
  @chmod($file, 0664);
  return '';
}

if ($r === 'login' && $method === 'POST') {
  $b = body_json();
  if (($b['password'] ?? '') === $ADMIN_PASSWORD) echo json_encode(['token' => $ADMIN_TOKEN]);
  else fail(401, 'Неверный пароль');
  exit;
}

if (in_array($r, $RESOURCES, true)) {
  $file = $DATA_DIR . '/' . $r . '.json';

  if ($method === 'GET') {
    echo is_file($file) ? file_get_contents($file) : '[]';
    exit;
  }

  if ($method === 'PUT') {
    require_auth();
    $data = body_json();
    if (!is_array($data)) $data = [];
    backup_file($file, $r);
    $err = save_json($file, $data);
    if ($err !== '') fail(500, $err);
    echo json_encode(['ok' => true, 'count' => count($data), 'saved' => 'data/' . $r . '.json']);
    exit;
  }
}

fail(404, 'Не найдено');
