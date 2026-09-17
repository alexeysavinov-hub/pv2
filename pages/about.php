<?php
$team = pcm_team();
$stats = [['19+','лет на рынке'],['2 500+','довольных клиентов'],['40+','специалистов'],['100%','оригинальные запчасти']];
$paras = [
  'Уже более 19 лет PCM работает с автомобилями Porsche, обеспечивая высокий уровень сервиса и глубокую техническую экспертизу марки.',
  'Мы приглашаем вас посетить наш центр на Ленинградском шоссе и познакомиться с модельным рядом Porsche, доступным к заказу и в наличии.',
  'Автомобили Porsche — это сочетание инженерных традиций, динамики и комфорта. Команда PCM помогает подобрать автомобиль и адаптировать его под индивидуальные предпочтения клиента.',
  'В распоряжении клиентов — современный технический центр, склад оригинальных запасных частей и отлаженная система поставок, позволяющая обеспечивать качественное и своевременное обслуживание.',
  'Специалисты PCM обладают опытом работы с автомобилями Porsche и предоставляют рекомендации по их эксплуатации и обслуживанию на высоком профессиональном уровне.',
  'Также в PCM доступна коллекция оригинальных аксессуаров Porsche — от повседневных и спортивных изделий до багажных решений и товаров для активного отдыха.',
];
$PAGE = [
  'title' => 'О компании — ' . $SITE_NAME,
  'desc'  => 'PCM — ваш эксперт по Porsche: более 19 лет работы с автомобилями Porsche в Москве. Продажа, сервис, оригинальные запчасти и аксессуары. Команда из 40+ специалистов.',
  'url'   => '/about/',
  'nav'   => '/about/',
  'image' => 'images/about-hero.png',
];
require __DIR__ . '/../inc/head.php';
require __DIR__ . '/../inc/header.php';
?>
<div class="wrap">
  <nav class="crumbs" aria-label="Хлебные крошки"><a href="/">Главная</a><span aria-hidden="true">/</span><b>О компании</b></nav>
  <div class="hero hero--wide">
    <img src="/images/about-hero.png" alt="PCM — шоурум на Ленинградском шоссе" fetchpriority="high" decoding="async">
    <div class="hero__in hero__in--col">
      <div class="eyebrow" style="color:rgba(255,255,255,.7);margin-bottom:10px">О компании</div>
      <h1>PCM — ваш эксперт по Porsche</h1>
      <p>Компания PCM предоставляет полный спектр услуг по продаже и сервисному обслуживанию автомобилей Porsche.</p>
    </div>
  </div>

  <div class="stats">
    <?php foreach ($stats as [$n, $l]): ?>
      <div><div class="stats__n"><?= e($n) ?></div><div class="stats__l"><?= e($l) ?></div></div>
    <?php endforeach; ?>
  </div>

  <div class="split">
    <div>
      <div class="eyebrow" style="color:#6e1423;font-weight:700">Почему PCM</div>
      <h2 style="font-size:clamp(21px,2.5vw,27px)">Более 19 лет с Porsche в Москве</h2>
    </div>
    <div class="prose">
      <?php foreach ($paras as $t): ?><p><?= e($t) ?></p><?php endforeach; ?>
    </div>
  </div>

  <h2 style="margin:clamp(36px,5vw,56px) 0 22px">Наша команда</h2>
  <div class="grid" style="grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:18px">
    <?php foreach ($team as $g): $n = count($g['members'] ?? []); ?>
      <a class="card" href="/about/team/<?= e($g['id']) ?>/">
        <div class="card__ph"><img src="<?= e(pcm_img($g['cardImg'])) ?>" alt="<?= e($g['dept']) ?> — PCM" loading="lazy" decoding="async"></div>
        <div class="card__b" style="padding:18px;flex-direction:row;align-items:center;justify-content:space-between;gap:12px">
          <div>
            <h3 style="font-size:17px"><?= e($g['dept']) ?></h3>
            <div class="muted" style="font-size:13px;margin-top:3px"><?= $n ?> <?= $n === 1 ? 'сотрудник' : ($n < 5 ? 'сотрудника' : 'сотрудников') ?></div>
          </div>
          <span style="color:#6e1423;font-weight:800">→</span>
        </div>
      </a>
    <?php endforeach; ?>
  </div>
</div>
<?php require __DIR__ . '/../inc/footer.php';
