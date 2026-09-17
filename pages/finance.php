<?php
$cards = json_decode(file_get_contents(__DIR__ . '/../data/finance.json'), true) ?: [];
$PAGE = [
  'title' => 'Кредит, лизинг и страхование Porsche — ' . $SITE_NAME,
  'desc'  => 'Финансовые услуги PCM: кредит от 7,9% годовых, лизинг с авансом от 0%, КАСКО и ОСАГО по партнёрским тарифам. Расчёт персонального предложения.',
  'url'   => '/finance/',
  'nav'   => '/finance/',
  'noform' => true,
  'image' => 'images/finance-hero.png',
];
require __DIR__ . '/../inc/head.php';
require __DIR__ . '/../inc/header.php';
?>
<div class="wrap" style="padding-bottom:0">
  <nav class="crumbs" aria-label="Хлебные крошки"><a href="/">Главная</a><span aria-hidden="true">/</span><b>Финансовые услуги</b></nav>
  <div class="hero hero--wide">
    <img src="/images/finance-hero.png" alt="Финансовые услуги PCM" fetchpriority="high" decoding="async">
    <div class="hero__in hero__in--col">
      <div class="eyebrow" style="color:rgba(255,255,255,.7);margin-bottom:10px">Финансовые услуги</div>
      <h1>Кредит, лизинг и страхование</h1>
      <p>Индивидуальные финансовые решения для приобретения вашего Porsche — на выгодных условиях и с минимумом формальностей.</p>
    </div>
  </div>
</div>

<section class="wrap" aria-label="Программы финансирования">
  <div class="grid grid--3">
    <?php foreach ($cards as $c): ?>
      <a class="tile tile--link" href="/finance/<?= e($c['id']) ?>/">
        <h2 style="font-size:22px"><?= e($c['title']) ?></h2>
        <p class="muted" style="font-size:15px"><?= e($c['short']) ?></p>
        <span class="tile__more">Подробнее <span aria-hidden="true">→</span></span>
      </a>
    <?php endforeach; ?>
  </div>
</section>

<section class="band">
  <div class="fcard">
    <h2>Рассчитать персональное предложение</h2>
    <p class="muted" style="text-align:center;font-size:15px;margin-top:12px">Оставьте заявку — менеджер свяжется с вами и подберёт оптимальные условия.</p>
    <?= pcm_form('finance', ['submit' => 'Отправить заявку']) ?>
  </div>
</section>
<?php require __DIR__ . '/../inc/footer.php';
