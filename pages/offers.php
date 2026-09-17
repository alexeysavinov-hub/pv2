<?php
$offers = pcm_offers();
foreach ($offers as $i => $o) { $offers[$i]['slug'] = pcm_slug($o['title']) ?: ('offer-' . ($o['id'] ?? $i)); }

$PAGE = [
  'title' => 'Специальные предложения сервиса Porsche — ' . $SITE_NAME,
  'desc'  => 'Актуальные акции и специальные предложения PCM: скидки на работы, детейлинг, шиномонтаж, хранение шин, trade-in и комиссионная продажа.',
  'url'   => '/service/offers/',
  'nav'   => '/service/',
  'image' => $offers[0]['image'] ?? '',
];
require __DIR__ . '/../inc/head.php';
require __DIR__ . '/../inc/header.php';
?>
<div class="wrap">
  <nav class="crumbs" aria-label="Хлебные крошки"><a href="/">Главная</a><span aria-hidden="true">/</span><a href="/service/">Сервис</a><span aria-hidden="true">/</span><b>Спецпредложения</b></nav>
  <div class="eyebrow">Специальные предложения</div>
  <h1>Актуальные предложения PCM</h1>
  <p class="lead">Постоянные и сезонные предложения для владельцев Porsche: обслуживание, детейлинг, шины и программы обмена автомобиля.</p>
  <div class="grid grid--3" style="margin-top:36px">
    <?php foreach ($offers as $o): ?>
      <a class="card" href="/service/offers/<?= e($o['slug']) ?>/">
        <div class="card__ph">
          <?php if (!empty($o['image'])): ?><img src="<?= e(pcm_img($o['image'])) ?>" alt="<?= e($o['title']) ?>" loading="lazy" decoding="async"><?php endif; ?>
        </div>
        <div class="card__b">
          <h2 class="card__t" style="font-size:19px;margin-top:0"><?= e($o['title']) ?></h2>
          <?php if (!empty($o['subtitle'])): ?><p class="card__x"><?= e($o['subtitle']) ?></p><?php endif; ?>
          <span class="card__more">Подробнее →</span>
        </div>
      </a>
    <?php endforeach; ?>
  </div>
</div>
<?php require __DIR__ . '/../inc/footer.php';
