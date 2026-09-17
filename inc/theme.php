<?php
/** Тема v3: вспомогательные функции для карточек автомобилей. */

/** Ключ модельного ряда: cayenne | macan | taycan | 911 | 718 | panamera | drugie | '' */
function pcm_car_line_key($c) {
  if (function_exists('pcm_car_is_other') && pcm_car_is_other($c)) return 'drugie';
  $line = mb_strtolower(trim((string)($c['line'] ?? '')), 'UTF-8');
  $known = ['cayenne','macan','taycan','911','718','panamera'];
  if (in_array($line, $known, true)) return $line;
  $model = mb_strtolower((string)($c['model'] ?? ''), 'UTF-8');
  foreach ($known as $k) if (mb_strpos($model, $k) !== false) return $k;
  return $line === 'другие' ? 'drugie' : '';
}

/** Силуэт модели, если у машины ещё нет фотографий. */
function pcm_car_ph($c) {
  $k = pcm_car_line_key($c);
  if ($k === '' || $k === 'drugie') return '';
  return is_file(__DIR__ . '/../images/model-' . $k . '-wide.png') ? '/images/model-' . $k . '-wide.png' : '';
}

/** Марка для непрофильных автомобилей без фото: «Mercedes-Benz», «Nissan». */
function pcm_car_brand($c) {
  $m = trim((string)($c['model'] ?? ''));
  $parts = preg_split('~\s+~u', $m);
  return $parts[0] ?? $m;
}

/** Ярлык на фото: Новый · Электро · Гибрид. */
function pcm_car_tag($c, $new) {
  if ($new) return 'Новый';
  $fuel = mb_strtolower((string)($c['fuel'] ?? ''), 'UTF-8');
  if (mb_strpos($fuel, 'электро') !== false) return 'Электро';
  if (mb_strpos($fuel, 'гибрид') !== false) return 'Гибрид';
  return '';
}
