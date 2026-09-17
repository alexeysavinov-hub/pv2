<?php
/**
 * Фронт-контроллер: разбирает URL и подключает нужную страницу.
 */
mb_internal_encoding('UTF-8');
@ini_set('display_errors', '0');
error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);
require __DIR__ . '/inc/config.php';
require __DIR__ . '/inc/helpers.php';
require __DIR__ . '/inc/data.php';
require __DIR__ . '/inc/form.php';

$path = isset($_GET['p']) ? trim($_GET['p'], '/') : '';
$seg  = $path === '' ? [] : array_values(array_filter(explode('/', $path), 'strlen'));
$s0   = $seg[0] ?? '';
$s1   = $seg[1] ?? '';

$PAGE = [];
$page = null;

switch ($s0) {
  case '':
    $page = 'home'; break;

  case 'news':
    if ($s1 !== '') { $page = 'article'; $ARG = $s1; } else { $page = 'news'; }
    break;

  case 'cars':
    if ($s1 !== '') {
      $ARG = $s1;
      $lines = ['718','911','taycan','panamera','macan','cayenne','drugie'];
      $page = in_array(mb_strtolower($s1), $lines, true) ? 'cars' : 'car';
    } else { $page = 'cars'; }
    break;

  case 'models':   $page = $s1 !== '' ? 'model' : 'models'; $ARG = $s1; break;
  case 'finance':  $page = $s1 !== '' ? 'finance-item' : 'finance'; $ARG = $s1; break;
  case 'about':    $page = $s1 === 'team' ? 'team' : 'about'; $ARG = $seg[2] ?? 'management'; break;
  case 'contacts': $page = 'contacts'; break;
  case 'legal':    $page = 'legal'; $ARG = $s1 !== '' ? $s1 : 'pdn'; break;
  case 'service':
    $sub = ['parts'=>'parts','body'=>'body','poa'=>'poa','offers'=>'offers'];
    if ($s1 === 'offers' && isset($seg[2])) { $page = 'offer'; $ARG = $seg[2]; }
    else { $page = ($s1 !== '' && isset($sub[$s1])) ? $sub[$s1] : 'service'; }
    break;
  case '404': $page = '404'; break;
}

$file = $page ? __DIR__ . '/pages/' . $page . '.php' : null;
if (!$file || !is_file($file)) {
  http_response_code(404);
  $file = __DIR__ . '/pages/404.php';
}
require $file;
