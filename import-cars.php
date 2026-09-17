<?php
/**
 * Обновление каталога «Авто в наличии» из фидов MaxPoster.
 *
 * В браузере:  https://ваш-сайт/import-cars.php?key=ПАРОЛЬ_АДМИНКИ
 * По расписанию (cron, раз в час):
 *   0 * * * * /usr/bin/php /путь/к/сайту/import-cars.php >/dev/null 2>&1
 */
@ini_set('display_errors', '1');
@ini_set('memory_limit', '512M');
error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);
mb_internal_encoding('UTF-8');

$cli = (PHP_SAPI === 'cli');

require __DIR__ . '/inc/config.php';
require __DIR__ . '/inc/helpers.php';
require __DIR__ . '/inc/data.php';
require __DIR__ . '/inc/feed.php';

if (!$cli) {
  header('X-Robots-Tag: noindex, nofollow');
  if (($_GET['key'] ?? '') !== $ADMIN_PASSWORD) { http_response_code(403); exit('Добавьте к адресу ?key=пароль_админки'); }
}

@set_time_limit(600);

if ($cli) {
  $r = pcm_feed_import(false);
  echo "[" . date('d.m.Y H:i') . "] машин: {$r['cars']}, пропущено: {$r['skipped']}, сохранено в: {$r['saved']}"
     . ($r['error'] !== '' ? " | ОШИБКА: {$r['error']}" : '') . "\n";
  exit;
}

$e = function ($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); };
$dataDir = __DIR__ . '/data';
$need = ['xml' => extension_loaded('simplexml'), 'curl' => function_exists('curl_init'), 'mbstring' => extension_loaded('mbstring')];

// шапку отдаём сразу — если импорт упадёт, окружение всё равно будет видно
?><!doctype html>
<html lang="ru"><head><meta charset="utf-8"><meta name="robots" content="noindex, nofollow">
<title>Импорт автомобилей — PCM</title>
<style>
body{font:15px/1.6 -apple-system,Segoe UI,Roboto,sans-serif;max-width:880px;margin:40px auto;padding:0 20px;color:#15151a}
h1{font-size:24px;margin:0 0 6px}h2{font-size:18px;margin-top:30px}
.mut{color:#6e6e73}
.box{border:1px solid #e2e2e6;border-radius:12px;padding:16px 18px;margin:18px 0}
.ok{border-color:#1c8b3c;background:#f2fbf5}
.bad{border-color:#6e1423;background:#fff4f5}
a.btn{display:inline-block;background:#6e1423;color:#fff;text-decoration:none;padding:11px 20px;border-radius:8px;font-weight:600}
table{border-collapse:collapse;width:100%;font-size:14px}
th,td{text-align:left;padding:7px 10px;border-bottom:1px solid #ececed;vertical-align:top}
th{color:#6e6e73;font-weight:600}
code{font-size:13px;word-break:break-all}
pre{background:#f5f5f7;padding:12px;border-radius:8px;overflow:auto;font-size:13px;white-space:pre-wrap}
</style></head><body>
<h1>Импорт автомобилей из MaxPoster</h1>
<p class="mut">Обновляет раздел «Авто в наличии».</p>

<div class="box">
  <table>
    <tr><th style="width:220px">PHP</th><td><?= $e(PHP_VERSION) ?></td></tr>
    <tr><th>Разбор XML</th><td><?= $need['xml'] ? 'simplexml' : 'встроенный (simplexml не установлен, но это не мешает)' ?></td></tr>
    <tr><th>Скачивание фида</th><td><?= $need['curl'] ? 'curl' : (ini_get('allow_url_fopen') ? 'file_get_contents' : '<b>нет ни curl, ни allow_url_fopen — скачать нечем</b>') ?></td></tr>
    <tr><th>Память</th><td><?= $e(ini_get('memory_limit')) ?></td></tr>
    <tr><th>Хранилище каталога</th><td><?= pcm_pdo() ? 'база MySQL' : (is_writable($dataDir) ? 'файл data/cars.json' : '<b>база недоступна, папка data/ закрыта на запись</b>') ?></td></tr>
    <tr><th>Фидов в настройках</th><td><?= count((array)($FEEDS ?? [])) ?></td></tr>
  </table>
</div>
<?php
if (function_exists('ob_flush')) { @ob_flush(); } @flush();

$t0 = microtime(true);
$r = null; $fatal = '';
try {
  $r = pcm_feed_import(false);
} catch (Throwable $ex) {
  $fatal = get_class($ex) . ': ' . $ex->getMessage() . ' (' . basename($ex->getFile()) . ':' . $ex->getLine() . ')';
}
$sec = round(microtime(true) - $t0, 1);

if ($fatal !== ''): ?>
  <div class="box bad"><b>Импорт прервался ошибкой.</b><pre><?= $e($fatal) ?></pre></div>
<?php else: ?>
  <div class="box <?= $r['error'] === '' ? 'ok' : 'bad' ?>">
    <?php if ($r['error'] === ''): ?>
      <b>Каталог обновлён: <?= (int)$r['cars'] ?> автомобилей.</b> <span class="mut">(<?= $e($sec) ?> с)</span><br>
      Сохранено в <?= $e($r['saved']) ?>.
      <?php if ($r['skipped']): ?><br><span class="mut">Пропущено объявлений: <?= (int)$r['skipped'] ?> (проданы, зарезервированы, без цены или дубли).</span><?php endif; ?>
      <br><span class="mut">Каталог формируется только из фидов — машины, заведённые вручную в админке, не сохраняются.</span>
    <?php else: ?>
      <b>Импорт не выполнен.</b><br><?= $e($r['error']) ?>
    <?php endif; ?>
  </div>

  <h2>Фиды</h2>
  <table>
    <tr><th>Адрес</th><th>Машин</th><th>Статус</th></tr>
    <?php foreach ($r['feeds'] as $f): ?>
      <tr>
        <td><code><?= $e($f['url']) ?></code></td>
        <td><?= (int)$f['count'] ?></td>
        <td><?= $f['ok'] ? 'загружен' : '<b>' . $e($f['error']) . '</b>' ?></td>
      </tr>
    <?php endforeach; ?>
  </table>
<?php endif; ?>

<p style="margin-top:26px"><a class="btn" href="?key=<?= urlencode($ADMIN_PASSWORD) ?>">Запустить ещё раз</a></p>

<div class="box">
  <b>Автоматическое обновление</b>
  <p class="mut" style="margin:8px 0 0">Задание в cron — каталог обновляется сам каждый час:</p>
  <pre>0 * * * * /usr/bin/php <?= $e(__DIR__) ?>/import-cars.php</pre>
  <p class="mut" style="margin:8px 0 0">Список фидов — в <code>inc/config.php</code>, массив <code>$FEEDS</code>.</p>
</div>
</body></html>
