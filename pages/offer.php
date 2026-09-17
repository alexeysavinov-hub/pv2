<?php
$offers = pcm_offers();
$item = null;
foreach ($offers as $i => $o) {
  $slug = pcm_slug($o['title']) ?: ('offer-' . ($o['id'] ?? $i));
  if ($slug === $ARG) { $item = $o; $item['slug'] = $slug; break; }
}
if (!$item) { // слайды главной без пары в спецпредложениях тоже имеют свою страницу
  foreach (pcm_slides() as $i => $s) {
    $slug = pcm_slug($s['title']) ?: ('slide-' . ($s['id'] ?? $i));
    if ($slug === $ARG) { $item = $s; $item['slug'] = $slug; break; }
  }
}
if (!$item) { require __DIR__ . '/404.php'; return; }

$desc  = $item['subtitle'] ?? '';
$map   = ['cars'=>'/cars/','models'=>'/models/','finance'=>'/finance/','service'=>'/service/','about'=>'/about/','contacts'=>'/contacts/'];
$cta   = !empty($item['external']) ? $item['external'] : ($map[$item['link'] ?? ''] ?? '/contacts/');
$ext   = !empty($item['external']);

$PAGE = [
  'title'  => $item['title'] . ' — ' . $SITE_NAME,
  'desc'   => pcm_excerpt($desc !== '' ? $desc : strip_tags(pcm_rich($item['body'] ?? '')), 180),
  'url'    => '/service/offers/' . $item['slug'] . '/',
  'nav'    => '/service/',
  'image'  => $item['image'] ?? '',
  'ogtype' => 'article',
];
require __DIR__ . '/../inc/head.php';
require __DIR__ . '/../inc/header.php';
?>
<article class="wrap wrap--narrow art">
  <nav class="crumbs" aria-label="Хлебные крошки"><a href="/">Главная</a><span aria-hidden="true">/</span><a href="/service/">Сервис</a><span aria-hidden="true">/</span><a href="/service/offers/">Спецпредложения</a></nav>
  <div class="art__date">Специальное предложение</div>
  <h1><?= e($item['title']) ?></h1>
  <?php if ($desc !== ''): ?><p class="art__lead"><?= e($desc) ?></p><?php endif; ?>
  <?php if (!empty($item['image'])): ?>
    <img class="art__img" src="<?= e(pcm_img($item['image'])) ?>" alt="<?= e($item['title']) ?>" loading="lazy" decoding="async">
  <?php endif; ?>
  <div class="art__body"><?= pcm_rich($item['body'] ?? '') ?></div>
  <hr style="margin:36px 0 28px">
  <div class="cta">
    <a class="btn" href="<?= e($cta) ?>"<?= $ext ? ' target="_blank" rel="noopener"' : '' ?>><?= e($item['buttonText'] ?: 'Узнать подробности') ?></a>
    <a class="btn btn--ghost" href="tel:<?= e($SITE_PHONE_HREF) ?>"><?= e($SITE_PHONE) ?></a>
  </div>
  <div style="margin-top:30px"><a href="/service/offers/" style="font-weight:700;font-size:15px">← Все предложения</a></div>
</article>
<?php require __DIR__ . '/../inc/footer.php';
