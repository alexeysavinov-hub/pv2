<?php
$news = pcm_news();
$PAGE = [
  'title' => 'Новости и события — ' . $SITE_NAME,
  'desc'  => 'Новости PCM: поступления автомобилей, сервисные акции, мероприятия для клиентов и анонсы моделей.',
  'url'   => '/news/',
  'nav'   => '/news/',
  'image' => $news[0]['image'] ?? '',
  'ldjson' => [
    '@context' => 'https://schema.org',
    '@type' => 'CollectionPage',
    'name' => 'Новости и события — ' . $SITE_NAME,
    'url' => $SITE_URL . '/news/',
    'breadcrumb' => ['@type'=>'BreadcrumbList','itemListElement'=>[
      ['@type'=>'ListItem','position'=>1,'name'=>'Главная','item'=>$SITE_URL . '/'],
      ['@type'=>'ListItem','position'=>2,'name'=>'Новости','item'=>$SITE_URL . '/news/'],
    ]],
  ],
];
require __DIR__ . '/../inc/head.php';
require __DIR__ . '/../inc/header.php';
?>
<div class="wrap">
  <nav class="crumbs" aria-label="Хлебные крошки"><a href="/">Главная</a><span aria-hidden="true">/</span><b>Новости</b></nav>
  <div class="eyebrow">Пресс-центр PCM</div>
  <div class="sechead" style="margin-bottom:0">
    <h1>Новости и события</h1>
    <div class="muted" style="font-size:15px">Публикаций: <b style="color:#15151a"><?= count($news) ?></b></div>
  </div>
  <p class="lead">Новости PCM: поступления автомобилей, сервисные акции, мероприятия для клиентов и анонсы моделей.</p>

  <?php if ($news): ?>
    <div class="grid grid--news" style="margin-top:38px">
      <?php foreach ($news as $n): ?>
        <a class="card" href="<?= e($n['url']) ?>">
          <div class="card__ph">
            <?php if (!empty($n['image'])): ?><img src="<?= e(pcm_img($n['image'])) ?>" alt="<?= e($n['title']) ?>" loading="lazy" decoding="async"><?php endif; ?>
          </div>
          <div class="card__b">
            <div class="card__date"><?= e(pcm_date($n['date'])) ?></div>
            <h2 class="card__t"><?= e($n['title']) ?></h2>
            <p class="card__x"><?= e($n['excerpt']) ?></p>
            <span class="card__more"><?= !empty($n['link']) ? 'Открыть спецпроект →' : 'Читать полностью →' ?></span>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <div style="border:1px dashed #d8d8dc;border-radius:10px;padding:60px 24px;text-align:center;color:#6e6e73;margin-top:38px">
      <div style="font-size:18px;font-weight:700;color:#5a5a60;margin-bottom:6px">Публикаций пока нет</div>
      <div style="font-size:14px">Загляните позже — мы регулярно рассказываем о новостях центра.</div>
    </div>
  <?php endif; ?>
</div>
<?php require __DIR__ . '/../inc/footer.php';
