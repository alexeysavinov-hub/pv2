<?php
/**
 * Импорт автомобилей из XML-фидов MaxPoster в каталог сайта.
 *
 * Запуск: /import-cars.php?key=ПАРОЛЬ_АДМИНКИ  (или по cron через php-cli)
 * Данные пишутся туда же, откуда их читает сайт: MySQL (если $USE_DB) или data/cars.json.
 *
 * Работает и без расширений simplexml/curl: есть свой разбор XML и скачивание
 * через file_get_contents.
 */

/** Скачивает URL: curl, если есть, иначе file_get_contents. */
function pcm_fetch($url) {
  if (function_exists('curl_init')) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_TIMEOUT        => 120,
      CURLOPT_SSL_VERIFYPEER => false,
      CURLOPT_USERAGENT      => 'PCM site importer',
    ]);
    $body = curl_exec($ch);
    $err  = curl_error($ch);
    $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if (PHP_VERSION_ID < 80000) curl_close($ch);
    if ($code >= 400) return ['ok' => false, 'body' => '', 'error' => "HTTP $code"];
    if ($body === false || $body === '') return ['ok' => false, 'body' => '', 'error' => $err !== '' ? $err : 'пустой ответ'];
    return ['ok' => true, 'body' => $body, 'error' => ''];
  }

  if (!ini_get('allow_url_fopen')) {
    return ['ok' => false, 'body' => '', 'error' => 'нет ни curl, ни allow_url_fopen — скачать фид нечем'];
  }
  $ctx = stream_context_create([
    'http' => ['timeout' => 120, 'user_agent' => 'PCM site importer', 'follow_location' => 1],
    'ssl'  => ['verify_peer' => false, 'verify_peer_name' => false],
  ]);
  $body = @file_get_contents($url, false, $ctx);
  $status = '';
  $hdrs = function_exists('http_get_last_response_headers') ? http_get_last_response_headers() : ($http_response_header ?? null);
  if (isset($hdrs[0]) && preg_match('~\s(\d{3})\s~', $hdrs[0], $m)) $status = ' (HTTP ' . $m[1] . ')';
  if ($body === false || $body === '') return ['ok' => false, 'body' => '', 'error' => 'не удалось скачать файл' . $status];
  return ['ok' => true, 'body' => $body, 'error' => ''];
}

/** Значение простого тега внутри куска XML. */
function pcm_xml_val($chunk, $tag) {
  if (!preg_match('~<' . $tag . '\b[^>]*?(?:/>|>(.*?)</' . $tag . '>)~su', $chunk, $m)) return '';
  $v = $m[1] ?? '';
  if ($v === '') return '';
  if (preg_match('~<!\[CDATA\[(.*?)\]\]>~s', $v, $c)) $v = $c[1];
  return trim(html_entity_decode($v, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
}

/** Все значения повторяющегося тега. */
function pcm_xml_all($chunk, $tag) {
  if (!preg_match_all('~<' . $tag . '\b[^>]*>(.*?)</' . $tag . '>~su', $chunk, $m)) return [];
  $out = [];
  foreach ($m[1] as $v) {
    if (preg_match('~<!\[CDATA\[(.*?)\]\]>~s', $v, $c)) $v = $c[1];
    $v = trim(html_entity_decode($v, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    if ($v !== '') $out[] = $v;
  }
  return $out;
}

/** XML фида → массив машин (обычные PHP-массивы, без simplexml). */
function pcm_feed_vehicles($body) {
  if (!preg_match_all('~<vehicle\b[^>]*>(.*?)</vehicle>~su', $body, $m)) return [];
  $tags = ['id','vin','type','year','brand','model','generation','bodyConfiguration','modification','complectation',
           'engineType','engineVolume','enginePower','bodyType','bodyColor','driveType','gearboxType',
           'mileage','price','previousPrice','availability','ownersCount','description'];
  $out = [];
  foreach ($m[1] as $chunk) {
    $v = [];
    foreach ($tags as $t) $v[$t] = pcm_xml_val($chunk, $t);

    $photos = [];
    if (preg_match('~<photos\b[^>]*>(.*?)</photos>~su', $chunk, $p)) $photos = pcm_xml_all($p[1], 'photo');
    $v['photos'] = $photos;

    $opts = [];
    if (preg_match('~<equipment\b[^>]*>(.*?)</equipment>~su', $chunk, $q)) $opts = pcm_xml_all($q[1], 'element');
    $v['options'] = $opts;

    $v['reserved'] = '';
    if (preg_match('~<status\b[^>]*>(.*?)</status>~su', $chunk, $s)) $v['reserved'] = pcm_xml_val($s[1], 'reserved');

    $out[] = $v;
  }
  return $out;
}

/** Словари кодов MaxPoster в русские подписи. */
function pcm_feed_dict($group, $code) {
  $d = [
    'engineType'  => ['petrol'=>'Бензин','diesel'=>'Дизель','hybrid'=>'Гибрид','electric'=>'Электро','gas'=>'Газ','petrol_gas'=>'Бензин / газ'],
    'gearboxType' => ['automatic'=>'Автомат','manual'=>'Механика','robotized'=>'Робот','variator'=>'Вариатор'],
    'driveType'   => ['full_4wd'=>'Полный','full_awd'=>'Полный','front'=>'Передний','rear'=>'Задний'],
    'bodyType'    => ['suv'=>'Внедорожник','sedan'=>'Седан','coupe'=>'Купе','cabrio'=>'Кабриолет','hatchback'=>'Хэтчбек','wagon'=>'Универсал','liftback'=>'Лифтбек','targa'=>'Тарга','roadster'=>'Родстер','minivan'=>'Минивэн','pickup'=>'Пикап','van'=>'Фургон'],
    'bodyColor'   => ['black'=>'Чёрный','white'=>'Белый','silver'=>'Серебристый','grey'=>'Серый','gray'=>'Серый','blue'=>'Синий','azure'=>'Голубой','red'=>'Красный','green'=>'Зелёный','yellow'=>'Жёлтый','orange'=>'Оранжевый','brown'=>'Коричневый','beige'=>'Бежевый','gold'=>'Золотистый','violet'=>'Фиолетовый','pink'=>'Розовый','burgundy'=>'Бордовый'],
  ];
  $code = strtolower(trim((string)$code));
  return $d[$group][$code] ?? '';
}

/** Модельная линейка Porsche для фильтра каталога. */
function pcm_feed_line($brand, $model) {
  if (mb_strtolower($brand) !== 'porsche') return 'Другие';
  $m = mb_strtolower(trim($model));
  $lines = ['718' => '718', '911' => '911', 'taycan' => 'Taycan', 'panamera' => 'Panamera', 'macan' => 'Macan', 'cayenne' => 'Cayenne'];
  foreach ($lines as $key => $label) { if (mb_strpos($m, $key) !== false) return $label; }
  return 'Другие';
}

/** Машина из фида → строка каталога сайта. */
function pcm_feed_car($v, $order) {
  $brand = $v['brand'];
  $model = $v['model'];
  $compl = $v['complectation'];
  $name  = trim($brand . ' ' . $model . ($compl !== '' && mb_strtolower($compl) !== 'базовая' ? ' ' . $compl : ''));

  $bodyType = pcm_feed_dict('bodyType', $v['bodyType']);
  if ($bodyType === '' && $v['bodyConfiguration'] !== '') {
    $bodyType = trim(preg_replace('~\s+\d+\s*дв\..*$~u', '', $v['bodyConfiguration']));
  }

  $vol = (int)$v['engineVolume'];
  $volTxt = $vol > 0 ? number_format($vol / 1000, 1, ',', '') . ' л' : '';

  $photos = array_values(array_unique($v['photos']));
  $options = array_values(array_unique($v['options']));

  return [
    'id'           => 'mp' . $v['id'],
    'vin'          => $v['vin'],
    'model'        => $name !== '' ? $name : $model,
    'line'         => pcm_feed_line($brand, $model),
    'bodyType'     => $bodyType,
    'year'         => (int)$v['year'],
    'mileage'      => (int)$v['mileage'],
    'price'        => (int)$v['price'],
    'oldPrice'     => (int)$v['previousPrice'],
    'power'        => (int)$v['enginePower'],
    'engineVol'    => $volTxt,
    'fuel'         => pcm_feed_dict('engineType', $v['engineType']),
    'transmission' => pcm_feed_dict('gearboxType', $v['gearboxType']),
    'drive'        => pcm_feed_dict('driveType', $v['driveType']),
    'color'        => pcm_feed_dict('bodyColor', $v['bodyColor']),
    'owners'       => (int)$v['ownersCount'],
    'modification' => $v['modification'],
    'generation'   => $v['generation'],
    'condition'    => $v['type'] === 'new' ? 'Новый' : 'С пробегом',
    'photo'        => $photos[0] ?? '',
    'photos'       => $photos,
    'description'  => $v['description'],
    'options'      => $options,
    'source'       => 'maxposter',
    'order'        => $order,
    'status'       => 'available',
  ];
}

/**
 * Импортирует все фиды. $keepManual — сохранять ли машины, добавленные вручную в админке.
 */
function pcm_feed_import($keepManual = false) {
  global $FEEDS;
  $report = ['feeds' => [], 'cars' => 0, 'skipped' => 0, 'kept' => 0, 'saved' => '', 'error' => ''];
  $cars = [];
  $order = 0;
  $seen = [];

  foreach ((array)$FEEDS as $url) {
    $url = trim((string)$url);
    if ($url === '') continue;

    $r = pcm_fetch($url);
    if (!$r['ok']) { $report['feeds'][] = ['url' => $url, 'ok' => false, 'count' => 0, 'error' => $r['error']]; continue; }

    $vehicles = pcm_feed_vehicles($r['body']);
    if (!$vehicles) {
      $report['feeds'][] = ['url' => $url, 'ok' => false, 'count' => 0, 'error' => 'в файле нет ни одного <vehicle> — это не фид MaxPoster'];
      continue;
    }

    $n = 0;
    foreach ($vehicles as $v) {
      $avail = strtolower($v['availability']);
      if ($avail !== '' && $avail !== 'available') { $report['skipped']++; continue; }
      if ($v['reserved'] !== '' && $v['reserved'] !== '0') { $report['skipped']++; continue; }

      $car = pcm_feed_car($v, $order);
      if ($car['price'] <= 0 || $car['model'] === '') { $report['skipped']++; continue; }

      $key = $car['vin'] !== '' ? $car['vin'] : $car['id'];
      if (isset($seen[$key])) { $report['skipped']++; continue; }   // одна машина в двух фидах
      $seen[$key] = true;

      $cars[] = $car;
      $order++; $n++;
    }
    $report['feeds'][] = ['url' => $url, 'ok' => true, 'count' => $n, 'error' => ''];
  }

  if (!$cars) { $report['error'] = 'Ни одной машины не получено — каталог не менялся.'; return $report; }

  if ($keepManual) {
    foreach (pcm_data('cars') as $old) {
      if (($old['source'] ?? '') === 'maxposter') continue;   // прошлый импорт выбрасываем
      $old['order'] = $order++;
      $cars[] = $old;
      $report['kept']++;
    }
  }

  $report['cars'] = count($cars);
  $json = json_encode($cars, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

  // Каталог сохраняем в файл — оттуда же его читает сайт и админка.
  $f = __DIR__ . '/../data/cars.json';
  $tmp = $f . '.tmp' . getmypid();
  if (@file_put_contents($tmp, $json) !== false && @rename($tmp, $f)) {
    @chmod($f, 0664);
    $report['saved'] = 'файл data/cars.json';
  } else {
    @unlink($tmp);
    $report['error'] = 'нет прав на запись в data/cars.json — поставьте папке data права 775';
  }

  return $report;
}
