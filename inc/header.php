<?php
$NAVI = [
  '/cars/'     => 'Авто в наличии',
  '/models/'   => 'Модели',
  '/finance/'  => 'Финансы',
  '/service/'  => 'Сервис',
  '/news/'     => 'Новости',
  '/about/'    => 'О компании',
  '/contacts/' => 'Контакты',
];
$cur = $PAGE['nav'] ?? '';
?>
<div class="tbar">
  <div class="tbar__in">
    <div class="tbar__l">
      <span>Москва, Ленинградское шоссе, 71А</span>
      <span class="tbar__sep" aria-hidden="true">·</span>
      <span>Ежедневно 9:00 – 21:00</span>
    </div>
    <a href="mailto:<?= e($SITE_EMAIL) ?>"><?= e($SITE_EMAIL) ?></a>
  </div>
</div>
<header class="hdr">
  <input type="checkbox" id="mtoggle" aria-label="Меню">
  <div class="hdr__in">
    <a class="logo" href="/" aria-label="<?= e($SITE_NAME) ?> — на главную">
      <img class="logo__img" src="/images/logo-pcm-light.png" width="536" height="344" alt="PCM — ваш эксперт по Porsche">
      <span class="logo__sep" aria-hidden="true"></span>
      <span>
        <span class="logo__t1">Ваш эксперт по Porsche</span>
        <span class="logo__t2">с 2007 года</span>
      </span>
    </a>
    <nav class="nav" aria-label="Основная навигация">
      <?php foreach ($NAVI as $href => $label): ?>
        <a href="<?= e($href) ?>"<?= $cur === $href ? ' aria-current="page"' : '' ?>><?= e($label) ?></a>
      <?php endforeach; ?>
    </nav>
    <a class="hdr__tel" href="tel:<?= e($SITE_PHONE_HREF) ?>"><?= e($SITE_PHONE) ?></a>
    <a class="hdr__cta" href="#callback" data-callback>Обратный звонок</a>
    <a class="hdr__telm" href="tel:<?= e($SITE_PHONE_HREF) ?>" aria-label="Позвонить">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
    </a>
    <label class="hdr__mbtn" for="mtoggle" aria-label="Открыть меню"><b>Меню</b><i><span></span><span></span><span></span></i></label>
  </div>
  <nav class="mnav" aria-label="Мобильная навигация">
    <?php foreach ($NAVI as $href => $label): ?>
      <a href="<?= e($href) ?>"<?= $cur === $href ? ' aria-current="page"' : '' ?>><?= e($label) ?></a>
    <?php endforeach; ?>
    <a class="mnav__tel" href="tel:<?= e($SITE_PHONE_HREF) ?>"><?= e($SITE_PHONE) ?></a>
    <div class="mnav__meta"><?= e($SITE_ADDR) ?><br>Ежедневно 9:00 – 21:00</div>
    <a class="btn btn--light" href="#callback" data-callback>Обратный звонок</a>
  </nav>
</header>
<main>
