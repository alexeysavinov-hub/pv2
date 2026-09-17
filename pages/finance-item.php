<?php
$items = json_decode(file_get_contents(__DIR__ . '/../data/finance.json'), true) ?: [];
$item = null;
foreach ($items as $i) { if ($i['id'] === $ARG) { $item = $i; break; } }
if (!$item) { require __DIR__ . '/404.php'; return; }

$PAGE = [
  'title' => ($item['metaTitle'] ?? $item['title']) . ' — ' . $SITE_NAME,
  'desc'  => $item['metaDesc'] ?? pcm_excerpt($item['intro'][0] ?? $item['short'], 180),
  'url'   => '/finance/' . $item['id'] . '/',
  'nav'   => '/finance/',
  'image' => $item['hero'] ?? 'images/finance-hero.png',
];
require __DIR__ . '/../inc/head.php';
require __DIR__ . '/../inc/header.php';
?>
<div class="wrap" style="padding-bottom:0">
  <nav class="crumbs" aria-label="Хлебные крошки">
    <a href="/">Главная</a><span aria-hidden="true">/</span>
    <a href="/finance/">Финансовые услуги</a><span aria-hidden="true">/</span>
    <b><?= e($item['title']) ?></b>
  </nav>
  <div class="hero hero--wide">
    <img src="/<?= e($item['hero'] ?? 'images/finance-hero.png') ?>" alt="<?= e($item['title']) ?> — PCM" fetchpriority="high" decoding="async">
    <div class="hero__in hero__in--col">
      <div class="eyebrow" style="color:rgba(255,255,255,.7);margin-bottom:10px">Финансовые услуги</div>
      <h1><?= e($item['title']) ?></h1>
      <p><?= e($item['short']) ?></p>
    </div>
  </div>
</div>

<section class="wrap">
  <div class="fdetail">
    <article class="prose">
      <?php foreach (($item['intro'] ?? []) as $p): ?><p><?= e($p) ?></p><?php endforeach; ?>
      <?php if (!empty($item['list'])): ?>
        <?php if (!empty($item['listTitle'])): ?><h2 class="fdetail__h2"><?= e($item['listTitle']) ?></h2><?php endif; ?>
        <ul class="lgl__ul"><?php foreach ($item['list'] as $li): ?><li><?= e($li) ?></li><?php endforeach; ?></ul>
      <?php endif; ?>
      <?php foreach (($item['outro'] ?? []) as $p): ?><p><?= e($p) ?></p><?php endforeach; ?>
      <p class="fdetail__tel">Подробности уточняйте в PCM: <a href="tel:<?= e($SITE_PHONE_HREF) ?>"><?= e($SITE_PHONE) ?></a></p>
    </article>

    <aside class="fdetail__side">
      <div class="fdetail__rate"><?= e($item['rate']) ?><span> <?= e($item['note']) ?></span></div>
      <div class="fdetail__nav">
        <div class="fdetail__navt">Другие программы</div>
        <?php foreach ($items as $i): if ($i['id'] === $item['id']) continue; ?>
          <a href="/finance/<?= e($i['id']) ?>/"><?= e($i['title']) ?><span aria-hidden="true">→</span></a>
        <?php endforeach; ?>
      </div>
      <a class="btn btn--wide" href="/finance/#form-finance">Рассчитать предложение</a>
    </aside>
  </div>
</section>
<?php require __DIR__ . '/../inc/footer.php';
