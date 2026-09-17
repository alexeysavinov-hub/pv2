<?php
http_response_code(404);
$PAGE = ['title' => 'Страница не найдена — ' . $SITE_NAME, 'desc' => 'Запрошенная страница не найдена.', 'url' => '/404/', 'robots' => 'noindex,follow'];
require __DIR__ . '/../inc/head.php';
require __DIR__ . '/../inc/header.php';
?>
<div class="wrap wrap--narrow">
  <div class="eyebrow">Ошибка 404</div>
  <h1>Страница не найдена</h1>
  <p class="lead">Возможно, адрес указан с опечаткой или раздел был перемещён. Начните с главной или посмотрите автомобили в наличии.</p>
  <div style="display:flex;gap:12px;flex-wrap:wrap;margin-top:30px">
    <a class="btn" href="/">На главную</a>
    <a class="btn btn--ghost" href="/cars/">Авто в наличии</a>
  </div>
</div>
<?php require __DIR__ . '/../inc/footer.php';
