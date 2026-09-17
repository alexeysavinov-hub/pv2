<?php
$all = pcm_news();
$item = null;
foreach ($all as $n) { if ($n['slug'] === $ARG) { $item = $n; break; } }
if (!$item) { require __DIR__ . '/404.php'; return; }
if (!empty($item['link'])) { header('Location: ' . $item['link'], true, 302); exit; } // спецпроект живёт на своей странице

$more  = array_slice(array_values(array_filter($all, function($n) use ($item){ return $n['slug'] !== $item['slug']; })), 0, 3);
$paras = pcm_paras($item['body'] ?? '');
$desc  = $item['excerpt'] !== '' ? $item['excerpt'] : pcm_excerpt($paras[0] ?? '');

$PAGE = [
  'title'  => $item['title'] . ' — ' . $SITE_NAME,
  'desc'   => pcm_excerpt($desc, 180),
  'url'    => '/news/' . $item['slug'] . '/',
  'nav'    => '/news/',
  'image'  => $item['image'] ?? '',
  'ogtype' => 'article',
  'ldjson' => [
    '@context' => 'https://schema.org',
    '@type' => 'NewsArticle',
    'headline' => $item['title'],
    'description' => pcm_excerpt($desc, 180),
    'datePublished' => $item['date'] ?? '',
    'dateModified' => $item['date'] ?? '',
    'mainEntityOfPage' => $SITE_URL . '/news/' . $item['slug'] . '/',
    'image' => !empty($item['image']) ? $SITE_URL . pcm_img($item['image']) : null,
    'author' => ['@type'=>'Organization','name'=>$SITE_NAME],
    'publisher' => ['@type'=>'Organization','name'=>$SITE_NAME,'logo'=>['@type'=>'ImageObject','url'=>$SITE_URL.'/images/contacts-hero.png']],
    'breadcrumb' => ['@type'=>'BreadcrumbList','itemListElement'=>[
      ['@type'=>'ListItem','position'=>1,'name'=>'Главная','item'=>$SITE_URL.'/'],
      ['@type'=>'ListItem','position'=>2,'name'=>'Новости','item'=>$SITE_URL.'/news/'],
      ['@type'=>'ListItem','position'=>3,'name'=>$item['title'],'item'=>$SITE_URL.'/news/'.$item['slug'].'/'],
    ]],
  ],
];
require __DIR__ . '/../inc/head.php';
require __DIR__ . '/../inc/header.php';
?>
<article class="wrap wrap--narrow art">
  <nav class="crumbs" aria-label="Хлебные крошки"><a href="/">Главная</a><span aria-hidden="true">/</span><a href="/news/">Новости</a></nav>
  <time class="art__date" datetime="<?= e($item['date']) ?>"><?= e(pcm_date($item['date'])) ?></time>
  <h1><?= e($item['title']) ?></h1>
  <?php if (!empty($item['excerpt'])): ?><p class="art__lead"><?= e($item['excerpt']) ?></p><?php endif; ?>
  <?php if (!empty($item['image'])): ?>
    <img class="art__img" src="<?= e(pcm_img($item['image'])) ?>" alt="<?= e($item['title']) ?>" loading="lazy" decoding="async">
  <?php endif; ?>
  <div class="art__body"><?= pcm_rich($item['body'] ?? '') ?></div>
  <hr style="margin:36px 0 28px">
  <div class="art__foot">
    <a href="/news/" style="font-weight:700;font-size:15px">← Все новости</a>
    <a class="btn" href="tel:<?= e($SITE_PHONE_HREF) ?>"><?= e($SITE_PHONE) ?></a>
  </div>

  <?php if ($more): ?>
  <section style="margin-top:clamp(44px,6vw,72px)">
    <h2 style="font-size:clamp(21px,2.6vw,28px);margin-bottom:22px">Другие публикации</h2>
    <div class="grid" style="grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:18px">
      <?php foreach ($more as $n): ?>
        <a class="card" href="<?= e($n['url']) ?>">
          <div class="card__ph">
            <?php if (!empty($n['image'])): ?><img src="<?= e(pcm_img($n['image'])) ?>" alt="<?= e($n['title']) ?>" loading="lazy" decoding="async"><?php endif; ?>
          </div>
          <div class="card__b" style="padding:18px 20px 20px">
            <div class="card__date"><?= e(pcm_date($n['date'])) ?></div>
            <h3 style="font-size:17px;line-height:1.28;margin-top:8px"><?= e($n['title']) ?></h3>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>
</article>
<?php require __DIR__ . '/../inc/footer.php';
