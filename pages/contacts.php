<?php
$PAGE = [
  'title' => 'Контакты — ' . $SITE_NAME,
  'desc'  => 'PCM: 125445, Москва, Ленинградское шоссе, д. 71А, стр. 10. Телефон ' . $SITE_PHONE . ', ' . $SITE_EMAIL . '. Ежедневно 9:00 — 21:00.',
  'url'   => '/contacts/',
  'nav'   => '/contacts/',
  'noform' => true,
  'image' => 'images/contacts-hero.png',
];
require __DIR__ . '/../inc/head.php';
require __DIR__ . '/../inc/header.php';
?>
<div class="wrap">
  <nav class="crumbs" aria-label="Хлебные крошки"><a href="/">Главная</a><span aria-hidden="true">/</span><b>Контакты</b></nav>
  <div class="hero hero--wide">
    <img src="/images/contacts-hero.png" alt="PCM — вход в центр" fetchpriority="high" decoding="async">
    <div class="hero__in hero__in--col">
      <div class="eyebrow" style="color:rgba(255,255,255,.7);margin-bottom:10px">Контакты</div>
      <h1>Свяжитесь с нами</h1>
    </div>
  </div>

  <div class="cgrid">
    <div>
      <dl class="cinfo">
        <div><dt>Адрес</dt><dd>125445, Москва,<br>Ленинградское шоссе, д. 71А, стр. 10</dd></div>
        <div><dt>Телефон</dt><dd><a href="tel:<?= e($SITE_PHONE_HREF) ?>" style="font-size:21px;font-weight:800;color:#6e1423"><?= e($SITE_PHONE) ?></a></dd></div>
        <div><dt>E-mail</dt><dd><a href="mailto:<?= e($SITE_EMAIL) ?>"><?= e($SITE_EMAIL) ?></a></dd></div>
        <div><dt>Режим работы</dt><dd>Ежедневно 9:00 — 21:00</dd></div>
      </dl>
      <div class="cmap">
        <iframe src="https://yandex.ru/map-widget/v1/?ll=37.452837%2C55.873921&amp;z=17&amp;mode=search&amp;oid=234619985554&amp;ol=biz" width="100%" height="100%" frameborder="0" allowfullscreen="true" loading="lazy" title="PCM на карте"></iframe>
      </div>
    </div>
    <div class="fcard fcard--flat" id="form">
      <h2 style="text-align:left;font-size:24px">Оставить заявку</h2>
      <p class="muted" style="font-size:15px;line-height:1.55;margin-top:6px">Заполните форму — мы свяжемся с вами в ближайшее время.</p>
      <?= pcm_form('contact', ['email' => true, 'message' => true, 'submit' => 'Отправить', 'thanks' => 'Спасибо за обращение! Мы свяжемся с вами в ближайшее время.']) ?>
    </div>
  </div>
</div>
<?php require __DIR__ . '/../inc/footer.php';
