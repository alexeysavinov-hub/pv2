<?php
$PAGE = [
  'title' => "Кузовной ремонт Porsche в Москве" . ' — ' . $SITE_NAME,
  'desc'  => "Кузовной и малярный ремонт Porsche в PCM: восстановление геометрии кузова, ремонт ЛКП, замена элементов оригинальными запчастями по технологии производителя.",
  'url'   => '/service/body/',
  'nav'   => '/service/',
  'image' => 'images/service-body.png',
];
require __DIR__ . '/../inc/head.php';
require __DIR__ . '/../inc/header.php';
?>
<div class="wrap wrap--mid">
  <nav class="crumbs" aria-label="Хлебные крошки"><a href="/">Главная</a><span aria-hidden="true">/</span><a href="/service/">Сервис</a><span aria-hidden="true">/</span><b>Кузовной ремонт Porsche</b></nav>
  <div class="hero">
    <img src="/images/service-body.png" alt="Кузовной и малярный ремонт Porsche в PCM" fetchpriority="high" decoding="async">
    <div class="hero__in">
      <div>
        <div class="eyebrow" style="color:rgba(255,255,255,.72);margin-bottom:10px">Сервис</div>
        <h1>Кузовной ремонт Porsche</h1>
      </div>
    </div>
  </div>
  <div class="prose" style="margin-top:clamp(24px,4vw,38px)">
    <p>В PCM выполняется кузовной ремонт автомобилей Porsche любой сложности — от локального восстановления до комплексного ремонта кузова.</p>
    <p>Специалисты центра проводят восстановление геометрии кузова, ремонт лакокрасочного покрытия и замену элементов с использованием оригинальных запасных частей Porsche.</p>
    <p>Работы выполняются на специализированном оборудовании с применением материалов и технологий, соответствующих требованиям производителя.</p>
    <p>Это позволяет восстановить заводские характеристики автомобиля и сохранить его эксплуатационные свойства.</p>
    <div class="cta">
      <a class="btn" href="tel:<?= e($SITE_PHONE_HREF) ?>">Позвонить <?= e($SITE_PHONE) ?></a>
      <a class="btn btn--ghost" href="/service/#form-service">Записаться на сервис</a>
    </div>
  </div>
</div>
<?php require __DIR__ . '/../inc/footer.php';
