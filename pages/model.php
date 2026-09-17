<?php
$models   = json_decode(file_get_contents(__DIR__ . '/../data/models.json'), true) ?: [];
$versions = json_decode(file_get_contents(__DIR__ . '/../data/versions.json'), true) ?: [];
$m = null;
foreach ($models as $x) { if ($x['slug'] === mb_strtolower($ARG)) { $m = $x; break; } }
if (!$m) { require __DIR__ . '/404.php'; return; }

$vers = $versions[$m['name']] ?? [];
$inStock = array_values(array_filter(pcm_cars(), function($c) use ($m){ return ($c['line'] ?? '') === $m['name']; }));

$PAGE = [
  'title' => 'Porsche ' . $m['name'] . ' — версии и наличие — ' . $SITE_NAME,
  'desc'  => 'Porsche ' . $m['name'] . ': ' . mb_strtolower($m['tagline']) . ' Версии и комплектации, автомобили в наличии в PCM.',
  'url'   => '/models/' . $m['slug'] . '/',
  'nav'   => '/models/',
  'image' => $m['image'],
];
require __DIR__ . '/../inc/head.php';
require __DIR__ . '/../inc/header.php';
?>
<div class="wrap">
  <nav class="crumbs" aria-label="Хлебные крошки"><a href="/">Главная</a><span aria-hidden="true">/</span><a href="/models/">Модели</a><span aria-hidden="true">/</span><b><?= e($m['name']) ?></b></nav>
  <div class="hero hero--wide">
    <img src="<?= e(pcm_img($m['image'])) ?>" alt="Porsche <?= e($m['name']) ?> — <?= e($m['body']) ?>" fetchpriority="high" decoding="async">
    <div class="hero__in hero__in--col">
      <div class="eyebrow" style="color:rgba(255,255,255,.7);margin-bottom:10px"><?= e($m['body']) ?></div>
      <h1>Porsche <?= e($m['name']) ?></h1>
      <p><?= e($m['tagline']) ?></p>
    </div>
  </div>

  <?php if ($vers): ?>
    <section style="margin-top:clamp(30px,4vw,46px)">
      <h2>Версии <?= e($m['name']) ?></h2>
      <p class="lead">Доступные версии и комплектации модельного ряда. Уточните наличие и сроки поставки у менеджера отдела продаж.</p>
      <div class="vers">
        <?php foreach ($vers as $v): ?><span><?= e($v) ?></span><?php endforeach; ?>
      </div>
    </section>
  <?php endif; ?>

  <?php if ($inStock): ?>
    <section style="margin-top:clamp(36px,5vw,56px)">
      <div class="sechead">
        <h2><?= e($m['name']) ?> в наличии</h2>
        <a class="seclink" href="/cars/<?= e($m['slug']) ?>/">Все <?= e($m['name']) ?> →</a>
      </div>
      <div class="grid grid--3">
        <?php foreach (array_slice($inStock, 0, 3) as $c): $ph = $c['photos'][0] ?? ''; ?>
          <a class="card" href="/cars/<?= e($c['slug']) ?>/">
            <div class="card__ph">
              <?php if ($ph): ?><img src="<?= e(pcm_img($ph)) ?>" alt="<?= e($c['model']) ?>, <?= e($c['year']) ?> год" loading="lazy" decoding="async"><?php endif; ?>
            </div>
            <div class="card__b">
              <span class="pill"><?= e($c['year']) ?> · <?= e(pcm_num($c['mileage'])) ?> км</span>
              <h3><?= e($c['model']) ?></h3>
              <div class="card__price" style="margin-top:14px"><?= e(pcm_price($c['price'])) ?></div>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    </section>
  <?php endif; ?>

  <div class="cta" style="margin-top:clamp(36px,5vw,52px)">
    <a class="btn" href="tel:<?= e($SITE_PHONE_HREF) ?>">Позвонить <?= e($SITE_PHONE) ?></a>
    <a class="btn btn--ghost" href="/contacts/#form">Запросить предложение</a>
  </div>
</div>
<?php require __DIR__ . '/../inc/footer.php';
