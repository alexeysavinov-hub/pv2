<?php
/**
 * Слой данных: MySQL (если $USE_DB = true) с откатом на JSON-файлы в data/.
 */

function pcm_pdo() {
  static $pdo = null; static $tried = false;
  global $USE_DB, $DB_HOST, $DB_NAME, $DB_USER, $DB_PASS;
  if ($tried) return $pdo;
  $tried = true;
  if (!$USE_DB) return null;
  try {
    $pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4", $DB_USER, $DB_PASS,
      [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $pdo->exec("CREATE TABLE IF NOT EXISTS pcm_store (name VARCHAR(32) PRIMARY KEY, data LONGTEXT NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
  } catch (Exception $e) { $pdo = null; }
  return $pdo;
}

function pcm_data($name) {
  static $cache = [];
  if (isset($cache[$name])) return $cache[$name];
  $rows = [];
  $pdo = pcm_pdo();
  if ($pdo) {
    try {
      $st = $pdo->prepare("SELECT data FROM pcm_store WHERE name = ?");
      $st->execute([$name]);
      $row = $st->fetch(PDO::FETCH_ASSOC);
      if ($row) { $d = json_decode($row['data'], true); if (is_array($d)) $rows = $d; }
    } catch (Exception $e) {}
  }
  if (!$rows) {
    $f = __DIR__ . '/../data/' . $name . '.json';
    if (is_file($f)) { $d = json_decode(file_get_contents($f), true); if (is_array($d)) $rows = $d; }
  }
  usort($rows, function($a, $b){
    $ao = isset($a['order']) ? $a['order'] : 0;
    $bo = isset($b['order']) ? $b['order'] : 0;
    return $ao <=> $bo;
  });
  $cache[$name] = $rows;
  return $rows;
}

/** Опубликованные новости, свежие сверху. */
function pcm_news() {
  $all = array_values(array_filter(pcm_data('news'), function($n){
    return (!isset($n['status']) || $n['status'] !== 'hidden') && !empty($n['title']);
  }));
  usort($all, function($a, $b){ return strcmp($b['date'] ?? '', $a['date'] ?? ''); });
  foreach ($all as &$n) {
    $n['slug'] = pcm_news_slug($n);
    // спецпроект: карточка ведёт не на статью, а на свою страницу (поле link)
    $n['url']  = !empty($n['link']) ? $n['link'] : '/news/' . $n['slug'] . '/';
  }
  unset($n);
  return $all;
}

function pcm_news_slug($n) {
  $s = pcm_slug($n['title'] ?? '');
  return $s !== '' ? $s : ('news-' . ($n['id'] ?? '0'));
}

/**
 * Автомобили, видимые в каталоге. Порядок единый для всего сайта:
 * сначала Porsche, непрофильные марки — в конце списка; внутри группы — по возрастанию цены.
 * Проданные автомобили уходят в самый конец.
 */
function pcm_cars() {
  $all = array_values(array_filter(pcm_data('cars'), function($c){
    return (!isset($c['status']) || $c['status'] !== 'hidden') && !empty($c['model']);
  }));
  foreach ($all as $i => $c) { $all[$i]['slug'] = pcm_car_slug($c); }
  usort($all, function($a, $b){ return pcm_car_sort_key($a) <=> pcm_car_sort_key($b); });
  return $all;
}

/** Непрофильная марка (не Porsche). */
function pcm_car_is_other($c) {
  if (mb_stripos((string)($c['model'] ?? ''), 'porsche') !== false) return false;
  $line = (string)($c['line'] ?? '');
  return $line === '' || $line === 'Другие';
}

/** Ключ сортировки каталога: [продано, непрофильная марка, цена не указана, цена]. */
function pcm_car_sort_key($c) {
  $price = (int)($c['price'] ?? 0);
  return [
    (($c['status'] ?? '') === 'sold') ? 1 : 0,
    pcm_car_is_other($c) ? 1 : 0,
    $price <= 0 ? 1 : 0,
    $price,
  ];
}

function pcm_car_slug($c) {
  $base = pcm_slug(($c['model'] ?? '') . ' ' . ($c['year'] ?? ''));
  return ($base !== '' ? $base . '-' : '') . ($c['id'] ?? '0');
}

function pcm_slides() {
  return array_values(array_filter(pcm_data('slides'), function($s){
    return (!isset($s['status']) || $s['status'] !== 'hidden') && !empty($s['title']);
  }));
}

/** Спецпредложения сервиса (админка: вкладка «Спецпредложения сервиса»). */
function pcm_offers() {
  $all = array_values(array_filter(pcm_data('offers'), function($o){
    return (!isset($o['status']) || $o['status'] !== 'hidden') && !empty($o['title']);
  }));
  if (!$all) $all = pcm_slides(); // откат на слайдер, пока раздел не заполнен
  return $all;
}

/** Отделы и сотрудники (админка: вкладка «Наша команда»). */
function pcm_team() {
  $all = array_values(array_filter(pcm_data('team'), function($g){
    return !empty($g['id']) && !empty($g['dept']);
  }));
  foreach ($all as $i => $g) { if (!isset($g['members']) || !is_array($g['members'])) $all[$i]['members'] = []; }
  return $all;
}
