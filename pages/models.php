<?php
$models = json_decode(file_get_contents(__DIR__ . '/../data/models.json'), true) ?: [];
$PAGE = [
  'title' => 'Модельный ряд Porsche — ' . $SITE_NAME,
  'desc'  => 'Модельный ряд Porsche в PCM: 911, 718, Taycan, Panamera, Macan и Cayenne. Версии, комплектации и автомобили в наличии.',
  'url'   => '/models/',
  'nav'   => '/models/',
  'image' => $models[0]['image'] ?? '',
];
require __DIR__ . '/../inc/head.php';
require __DIR__ . '/../inc/header.php';
?>
<div class="wrap">
  <nav class="crumbs" aria-label="Хлебные крошки"><a href="/">Главная</a><span aria-hidden="true">/</span><b>Модели</b></nav>
  <div class="eyebrow">Модельный ряд</div>
  <h1>Выберите свой Porsche</h1>
  <p class="lead">Шесть модельных рядов Porsche — от среднемоторного 718 до полностью электрического Taycan. Выберите модель, чтобы посмотреть версии и автомобили в наличии.</p>
  <div class="grid grid--3" style="margin-top:38px">
    <?php foreach ($models as $m): ?>
      <a class="card" href="/models/<?= e($m['slug']) ?>/">
        <div class="card__ph"><img src="<?= e(pcm_img($m['image'])) ?>" alt="Porsche <?= e($m['name']) ?> — <?= e($m['body']) ?>" loading="lazy" decoding="async"></div>
        <div class="card__b">
          <h2 class="card__t" style="margin-top:0">Porsche <?= e($m['name']) ?></h2>
          <p class="card__x"><?= e($m['tagline']) ?></p>
          <span class="card__more">Версии и наличие →</span>
        </div>
      </a>
    <?php endforeach; ?>
  </div>
</div>
<?php require __DIR__ . '/../inc/footer.php';
