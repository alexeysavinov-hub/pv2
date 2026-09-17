<?php
$PAGE = [
  'title' => "Доверенность на приёмку автомобиля Porsche" . ' — ' . $SITE_NAME,
  'desc'  => "Как оформить доверенность на сдачу и получение автомобиля Porsche в сервисном центре PCM: какие документы нужны и что она позволяет доверенному лицу.",
  'url'   => '/service/poa/',
  'nav'   => '/service/',
  'image' => 'images/service-poa.png',
];
require __DIR__ . '/../inc/head.php';
require __DIR__ . '/../inc/header.php';
?>
<div class="wrap wrap--mid">
  <nav class="crumbs" aria-label="Хлебные крошки"><a href="/">Главная</a><span aria-hidden="true">/</span><a href="/service/">Сервис</a><span aria-hidden="true">/</span><b>Доверенность на приёмку автомобиля</b></nav>
  <div class="hero">
    <img src="/images/service-poa.png" alt="Оформление доверенности и приёмка автомобиля Porsche в PCM" fetchpriority="high" decoding="async">
    <div class="hero__in">
      <div>
        <div class="eyebrow" style="color:rgba(255,255,255,.72);margin-bottom:10px">Сервис</div>
        <h1>Доверенность на приёмку автомобиля</h1>
      </div>
    </div>
  </div>
  <div class="prose" style="margin-top:clamp(24px,4vw,38px)">
    <p>Если автомобиль на сервис привозит или забирает не собственник, потребуется доверенность на представление интересов в сервисном центре PCM.</p>
    <p>Доверенность позволяет доверенному лицу сдавать и получать автомобиль, подписывать заказ-наряды и акты выполненных работ, а также оплачивать услуги от имени владельца.</p>
    <p>Сотрудники PCM помогут корректно оформить документы и подскажут, какие данные потребуются. При себе необходимо иметь паспорт доверенного лица и данные владельца автомобиля.</p>
    <p>По вопросам оформления доверенности и приёмки автомобиля свяжитесь с нами — менеджеры сервиса проконсультируют и подготовят необходимые документы.</p>
    <div class="cta">
      <a class="btn" href="tel:<?= e($SITE_PHONE_HREF) ?>">Позвонить <?= e($SITE_PHONE) ?></a>
      <a class="btn btn--ghost" href="/service/#form-service">Записаться на сервис</a>
    </div>
  </div>
</div>
<?php require __DIR__ . '/../inc/footer.php';
