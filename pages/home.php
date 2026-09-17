<?php
$slides  = pcm_slides();
$news    = array_slice(pcm_news(), 0, 3);
// Все автомобили в наличии: Porsche по возрастанию цены, непрофильные марки — в конце (порядок задаёт pcm_cars()).
$carsAll = array_values(array_filter(pcm_cars(), function($c){ return ($c['status'] ?? '') !== 'sold'; }));
$total   = count($carsAll);
// На главной — первые пять машин и тёмная плитка «весь каталог». Если машин мало, показываем все.
$cars    = $total > 6 ? array_slice($carsAll, 0, 5) : $carsAll;
$showAll = $total > 6;

// Фильтры по модельному ряду для шапки раздела.
$lineNames = ['cayenne'=>'Cayenne','macan'=>'Macan','taycan'=>'Taycan','911'=>'911','718'=>'718','panamera'=>'Panamera','drugie'=>'Другие марки'];
$lines = [];
foreach ($carsAll as $c) { $k = pcm_car_line_key($c); if (isset($lineNames[$k])) $lines[$k] = ($lines[$k] ?? 0) + 1; }

// Подпись над заголовком слайда — по разделу, куда он ведёт.
$kick = ['cars'=>'Автомобили в наличии','service'=>'Сервис PCM','contacts'=>'Обратная связь','finance'=>'Финансовые услуги'];

$PAGE = [
  'title' => 'PCM — ваш эксперт по Porsche · продажа, сервис и запчасти Porsche',
  'desc'  => 'PCM: автомобили Porsche с пробегом в наличии, сервисное обслуживание, оригинальные запчасти, кредит, лизинг и trade-in. Москва, Ленинградское шоссе, 71А. Тел. ' . $SITE_PHONE . '.',
  'url'   => '/',
  'nav'   => '/',
];
require __DIR__ . '/../inc/head.php';
require __DIR__ . '/../inc/header.php';
?>
<h1 class="sr">PCM — продажа, сервис и обслуживание Porsche в Москве</h1>

<?php if ($slides): $n = count($slides); ?>
<section class="stage" aria-label="Актуальные предложения">
  <div class="stage__track" id="promo">
    <?php foreach ($slides as $si => $s):
      // Клик по слайду ведёт на страницу этого предложения; «Прямая ссылка» из админки имеет приоритет.
      $slug = pcm_slug($s['title']) ?: ('slide-' . ($s['id'] ?? $si));
      $href = '/service/offers/' . $slug . '/';
      $ext  = !empty($s['external']);
      if ($ext) $href = $s['external'];
      // Слайд с флагом "cutout": true в slides.json — PNG автомобиля без фона «парит» в студии.
      $cut  = !empty($s['cutout']);
      $tint = preg_match('~^[a-z-]+\([^;{}<>]*\)$~i', (string)($s['tint'] ?? '')) ? $s['tint'] : '';
      $kicker = $ext ? 'Официальный конфигуратор' : ($kick[$s['link'] ?? ''] ?? 'Специальное предложение');
    ?>
    <a class="stage__i<?= $cut ? ' stage__i--cut' : '' ?>" href="<?= e($href) ?>"<?= $ext ? ' target="_blank" rel="noopener"' : '' ?><?= $tint ? ' style="--tint:' . e($tint) . '"' : '' ?>>
      <div class="stage__media">
        <?php if (!empty($s['image'])): ?>
          <img src="<?= e(pcm_img($s['image'])) ?>" alt="" <?= $si === 0 ? 'fetchpriority="high"' : 'loading="lazy" decoding="async"' ?>>
        <?php endif; ?>
      </div>
      <div class="stage__c">
        <div class="stage__t">
          <div class="eyebrow"><?= e($kicker) ?></div>
          <h2><?= pcm_type($s['title']) ?></h2>
          <?php if (!empty($s['subtitle'])): ?><p><?= pcm_type($s['subtitle']) ?></p><?php endif; ?>
          <?php if (!empty($s['buttonText'])): ?>
            <div class="stage__act"><span class="btn btn--light"><?= e($s['buttonText']) ?> <span aria-hidden="true">→</span></span></div>
          <?php endif; ?>
          <?php if (!empty($s['note'])): ?><span class="stage__note"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" aria-hidden="true"><circle cx="12" cy="12" r="9"></circle><path d="M12 8h.01M12 11.5v5"></path></svg><span><?= e($s['note']) ?></span></span><?php endif; ?>
        </div>
      </div>
    </a>
    <?php endforeach; ?>
  </div>
  <?php if ($n > 1): ?>
  <div class="stage__ui">
    <div class="stage__num" aria-live="polite"><b id="promo-cur">01</b><span>/ <?= $n < 10 ? '0' . $n : $n ?></span></div>
    <div class="stage__bars" id="promo-dots" role="tablist" aria-label="Предложения">
      <?php foreach ($slides as $i => $s): ?>
        <button type="button" role="tab" aria-label="<?= e($s['title']) ?>"<?= $i === 0 ? ' aria-current="true"' : '' ?>></button>
      <?php endforeach; ?>
    </div>
    <div class="stage__nxt">Далее · <b id="promo-next"><?= e($slides[1]['title']) ?></b></div>
    <div class="stage__arr">
      <button type="button" data-promo-prev aria-label="Предыдущее предложение"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M15 6l-6 6 6 6"></path></svg></button>
      <button type="button" data-promo-next aria-label="Следующее предложение"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M9 6l6 6-6 6"></path></svg></button>
    </div>
  </div>
  <?php endif; ?>
</section>
<?php endif; ?>

<?php if ($cars): ?>
<section class="wrap" aria-labelledby="h-cars">
  <div class="sechead">
    <div>
      <div class="eyebrow">Porsche с пробегом и новые</div>
      <h2 id="h-cars">Автомобили <em class="thin">в наличии</em></h2>
    </div>
    <nav class="seclinks" aria-label="Модельный ряд">
      <a href="/cars/" aria-current="true">Все · <?= $total ?></a>
      <?php foreach ($lineNames as $k => $label): if (empty($lines[$k])) continue; ?>
        <a href="/cars/<?= e($k) ?>/"><?= e($label) ?></a>
      <?php endforeach; ?>
    </nav>
  </div>
  <div class="grid grid--3">
    <?php $sep = false; foreach ($cars as $c):
      $ph   = $c['photos'][0] ?? ($c['photo'] ?? '');
      $isPh = false;
      if ($ph === '') { $ph = pcm_car_ph($c); $isPh = true; }
      $new  = (($c['condition'] ?? '') === 'Новый') || ((int)($c['mileage'] ?? 0) < 100 && (int)($c['year'] ?? 0) >= (int)date('Y') - 1);
      $tag  = pcm_car_tag($c, $new);
      $meta = implode(' · ', array_filter([$c['year'] ?? '', !empty($c['mileage']) || !$new ? pcm_num($c['mileage'] ?? 0) . ' км' : '', !empty($c['power']) ? $c['power'] . ' л.с.' : '']));
      $spec = implode(' · ', array_filter([$c['color'] ?? '', $c['fuel'] ?? '', $c['transmission'] ?? '']));
      // непрофильные марки идут в конце — отбиваем их подзаголовком (на главной — только если показываем всё)
      if (!$showAll && !$sep && pcm_car_is_other($c)): $sep = true; ?>
      <div class="grid__sep" role="presentation"><span>Другие марки</span></div>
    <?php endif; ?>
      <a class="card" href="/cars/<?= e($c['slug']) ?>/">
        <div class="card__ph<?= $isPh ? ' card__ph--ph' : '' ?>">
          <?php if ($ph): ?>
            <img src="<?= e(pcm_img($ph)) ?>" alt="<?= e($c['model']) ?>, <?= e($c['year']) ?> год, <?= e($c['power']) ?> л.с." loading="lazy" decoding="async">
          <?php else: ?>
            <span class="card__brand"><?= e(pcm_car_brand($c)) ?></span>
          <?php endif; ?>
          <?php if ($tag): ?><span class="card__tag"><?= e($tag) ?></span><?php endif; ?>
        </div>
        <div class="card__b">
          <span class="pill"><?= e($meta) ?></span>
          <h3><?= e($c['model']) ?></h3>
          <?php if ($spec !== ''): ?><div class="card__spec"><?= e($spec) ?></div><?php endif; ?>
          <div class="card__foot">
            <span class="card__price"><?= e(pcm_price($c['price'])) ?></span>
            <span class="card__more">Подробнее →</span>
          </div>
        </div>
      </a>
    <?php endforeach; ?>
    <?php if ($showAll): ?>
      <a class="card card--dark" href="/cars/">
        <div>
          <div class="card__big"><?= $total ?></div>
          <div class="card__lbl">автомобилей в наличии</div>
        </div>
        <div>
          <p><?= e(implode(', ', array_slice(array_values(array_intersect_key($lineNames, array_diff_key($lines, ['drugie'=>1]))), 0, 3))) ?><?= !empty($lines['drugie']) ? ' и другие марки' : '' ?>. Обновляем каталог ежедневно.</p>
          <span class="btn btn--line">Весь каталог <span aria-hidden="true">→</span></span>
        </div>
      </a>
    <?php endif; ?>
  </div>
</section>
<?php endif; ?>

<section class="svc" aria-labelledby="h-svc">
  <div class="svc__in">
    <div>
      <div class="eyebrow">Сервис PCM</div>
      <h2 id="h-svc">Забота о вашем Porsche <em class="thin">на каждом этапе</em></h2>
      <p class="svc__lead">Сертифицированные специалисты, оригинальные запасные части и фирменное оборудование. От планового ТО до кузовного ремонта — в одном центре на Ленинградском шоссе.</p>
      <div class="svc__act">
        <a class="btn btn--light" href="/service/">Запись на сервис <span aria-hidden="true">→</span></a>
        <a class="btn btn--line" href="/finance/">Финансовые услуги</a>
      </div>
      <div class="svc__pic">
        <img src="/images/service-hero.png" alt="Сервисный центр PCM" loading="lazy" decoding="async">
        <div class="svc__cap">Скидка 30% на работы для автомобилей старше 2 лет</div>
      </div>
    </div>
    <div class="svc__list">
      <a class="svc__it" href="/service/">
        <span class="svc__n">01</span>
        <span><h3>Техническое обслуживание</h3><p>Плановое ТО, диагностика и ремонт на фирменном оборудовании Porsche.</p></span>
        <span class="svc__arr" aria-hidden="true">→</span>
      </a>
      <a class="svc__it" href="/service/body/">
        <span class="svc__n">02</span>
        <span><h3>Кузовной ремонт</h3><p>Восстановление геометрии и окраска по заводской технологии, оригинальные детали.</p></span>
        <span class="svc__arr" aria-hidden="true">→</span>
      </a>
      <a class="svc__it" href="/service/parts/">
        <span class="svc__n">03</span>
        <span><h3>Оригинальные запчасти</h3><p>Склад запасных частей и аксессуаров, заказ под ваш VIN.</p></span>
        <span class="svc__arr" aria-hidden="true">→</span>
      </a>
      <a class="svc__it" href="/service/offers/">
        <span class="svc__n">04</span>
        <span><h3>Детейлинг и хранение шин</h3><p>Керамическая защита, полировка, оклейка плёнкой. Сезонное хранение колёс — 8 300 ₽.</p></span>
        <span class="svc__arr" aria-hidden="true">→</span>
      </a>
    </div>
  </div>
</section>

<?php if ($news): ?>
<section class="wrap" aria-labelledby="h-news">
  <div class="sechead">
    <div>
      <div class="eyebrow">Пресс-центр</div>
      <h2 id="h-news">Новости <em class="thin">и события</em></h2>
    </div>
    <a class="seclink" href="/news/">Все новости →</a>
  </div>
  <div class="grid grid--3">
    <?php foreach ($news as $nw): ?>
      <a class="card card--flat" href="<?= e($nw['url']) ?>">
        <div class="card__ph">
          <?php if (!empty($nw['image'])): ?><img src="<?= e(pcm_img($nw['image'])) ?>" alt="<?= e($nw['title']) ?>" loading="lazy" decoding="async"><?php endif; ?>
        </div>
        <div class="card__b">
          <div class="card__date"><?= e(pcm_date($nw['date'])) ?></div>
          <h3 class="card__t"><?= e($nw['title']) ?></h3>
          <p class="card__x"><?= e($nw['excerpt']) ?></p>
          <span class="card__more"><?= !empty($nw['link']) ? 'Открыть спецпроект →' : 'Читать →' ?></span>
        </div>
      </a>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>
<?php require __DIR__ . '/../inc/footer.php';
