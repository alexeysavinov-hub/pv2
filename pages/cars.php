<?php
$all = pcm_cars();
$LINES = ['718'=>'718','911'=>'911','taycan'=>'Taycan','panamera'=>'Panamera','macan'=>'Macan','cayenne'=>'Cayenne','drugie'=>'Другие'];
$filter = isset($ARG) ? mb_strtolower($ARG) : '';
$fname  = $LINES[$filter] ?? '';

$cars = $fname === '' ? $all : array_values(array_filter($all, function($c) use ($fname){ return ($c['line'] ?? '') === $fname; }));

$h1 = $fname === '' ? 'Автомобили в наличии' : 'Porsche ' . $fname . ' в наличии';
if ($fname === 'Другие') $h1 = 'Другие автомобили в наличии';

$PAGE = [
  'title' => $h1 . ' — ' . $SITE_NAME,
  'desc'  => $fname === ''
      ? 'Каталог автомобилей Porsche с пробегом и новых в наличии в PCM: цены, пробег, комплектации. Trade-in и кредит. Тел. ' . $SITE_PHONE . '.'
      : 'Porsche ' . $fname . ' в наличии в PCM: актуальные цены, пробег и комплектации. Trade-in и кредит.',
  'url'   => '/cars/' . ($filter !== '' && $fname !== '' ? $filter . '/' : ''),
  'nav'   => '/cars/',
  'image' => $cars[0]['photos'][0] ?? '',
  'ldjson' => [
    '@context' => 'https://schema.org',
    '@type' => 'ItemList',
    'name' => $h1,
    'numberOfItems' => count($cars),
    'itemListElement' => array_values(array_map(function($c, $i) use ($SITE_URL){
      return ['@type'=>'ListItem','position'=>$i+1,'url'=>$SITE_URL.'/cars/'.$c['slug'].'/','name'=>$c['model']];
    }, $cars, array_keys($cars))),
  ],
];
require __DIR__ . '/../inc/head.php';
require __DIR__ . '/../inc/header.php';
?>
<div class="wrap">
  <nav class="crumbs" aria-label="Хлебные крошки">
    <a href="/">Главная</a><span aria-hidden="true">/</span>
    <?php if ($fname === ''): ?><b>Авто в наличии</b>
    <?php else: ?><a href="/cars/">Авто в наличии</a><span aria-hidden="true">/</span><b><?= e($fname) ?></b><?php endif; ?>
  </nav>
  <div class="eyebrow">Porsche с пробегом и новые</div>
  <div class="sechead" style="margin-bottom:0">
    <h1><?= e($h1) ?></h1>
    <div class="muted" style="font-size:15px">Найдено: <b style="color:#15151a"><?= count($cars) ?></b></div>
  </div>

  <nav class="chips" aria-label="Фильтр по модельному ряду">
    <a class="chip" href="/cars/"<?= $fname === '' ? ' aria-current="true"' : '' ?>>Все</a>
    <?php foreach ($LINES as $slug => $label):
      $n = count(array_filter($all, function($c) use ($label){ return ($c['line'] ?? '') === $label; }));
      if ($n === 0) continue; ?>
      <a class="chip" href="/cars/<?= e($slug) ?>/"<?= $fname === $label ? ' aria-current="true"' : '' ?>><?= e($label) ?> <span class="muted">(<?= $n ?>)</span></a>
    <?php endforeach; ?>
  </nav>

  <?php if ($cars): ?>
    <div class="grid grid--cars">
      <?php $sep = false; foreach ($cars as $c): $ph = $c['photos'][0] ?? ''; $sold = ($c['status'] ?? '') === 'sold';
        if ($fname === '' && !$sep && pcm_car_is_other($c)): $sep = true; ?>
        <div class="grid__sep" role="presentation"><span>Другие марки</span></div>
      <?php endif; ?>
        <a class="card" href="/cars/<?= e($c['slug']) ?>/">
          <div class="card__ph">
            <?php if ($ph): ?><img src="<?= e(pcm_img($ph)) ?>" alt="<?= e($c['model']) ?>, <?= e($c['year']) ?> год, <?= e($c['power']) ?> л.с." loading="lazy" decoding="async"><?php endif; ?>
            <?php if ($sold): ?><span class="sold">Продано</span><?php endif; ?>
          </div>
          <div class="card__b">
            <span class="pill"><?= e($c['bodyType']) ?> · <?= e($c['line']) ?></span>
            <h2 class="card__t" style="font-size:clamp(20px,2.4vw,25px)"><?= e($c['model']) ?></h2>
            <div class="muted" style="font-size:14px;font-weight:600;margin-top:7px"><?= e($c['year']) ?> · <?= e(pcm_num($c['mileage'])) ?> км · <?= e($c['power']) ?> л.с.</div>
            <div class="tags">
              <?php foreach (array_filter([$c['fuel'] ?? '', $c['engineVol'] ?? '', $c['transmission'] ?? '', $c['drive'] ?? '' , $c['color'] ?? '']) as $t): ?>
                <span><?= e($t) ?></span>
              <?php endforeach; ?>
            </div>
            <div style="display:flex;align-items:center;gap:14px;margin-top:auto;padding-top:18px">
              <div class="card__price"><?= e(pcm_price($c['price'])) ?></div>
              <span class="badge <?= $sold ? 'badge--sold' : 'badge--ok' ?>"><?= $sold ? 'Продано' : 'В наличии' ?></span>
            </div>
            <hr style="margin:16px 0 0">
            <span class="btn btn--wide" style="margin-top:16px">Подробнее</span>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <div style="border:1px dashed #d8d8dc;border-radius:10px;padding:60px 24px;text-align:center;color:#6e6e73">
      <div style="font-size:18px;font-weight:700;color:#5a5a60;margin-bottom:6px">В этой категории пока нет автомобилей</div>
      <div style="font-size:14px">Загляните позже или свяжитесь с отделом продаж: <?= e($SITE_PHONE) ?></div>
    </div>
  <?php endif; ?>
</div>
<?php require __DIR__ . '/../inc/footer.php';
