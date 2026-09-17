<?php
$all = pcm_cars();
$car = null;
foreach ($all as $c) { if ($c['slug'] === $ARG) { $car = $c; break; } }
if (!$car) { require __DIR__ . '/404.php'; return; }

$photos = array_values(array_filter($car['photos'] ?? []));
$sold   = ($car['status'] ?? '') === 'sold';
$opts   = array_values(array_filter($car['options'] ?? []));
$dsc    = pcm_desc_split($car['description'] ?? '', $car['model'] ?? '');
$title  = $car['model'] . ', ' . $car['year'] . ' — ' . pcm_price($car['price']);

$specs = array_filter([
  'Год выпуска'   => (string)($car['year'] ?? ''),
  'Пробег'        => !empty($car['mileage']) ? pcm_num($car['mileage']) . ' км' : '',
  'Мощность'      => !empty($car['power']) ? $car['power'] . ' л.с.' : '',
  'Двигатель'     => (string)($car['engineVol'] ?? ''),
  'Тип топлива'   => (string)($car['fuel'] ?? ''),
  'Трансмиссия'   => (string)($car['transmission'] ?? ''),
  'Привод'        => (string)($car['drive'] ?? ''),
  'Кузов'         => (string)($car['bodyType'] ?? ''),
  'Цвет'          => (string)($car['color'] ?? ''),
], function($v){ return $v !== ''; });

$PAGE = [
  'title'  => $title . ' — ' . $SITE_NAME,
  'desc'   => pcm_excerpt(($car['model'] ?? '') . ', ' . ($car['year'] ?? '') . ' год, пробег ' . pcm_num($car['mileage'] ?? 0) . ' км, ' . ($car['power'] ?? '') . ' л.с. ' . ($car['description'] ?? ''), 180),
  'url'    => '/cars/' . $car['slug'] . '/',
  'nav'    => '/cars/',
  'noform' => true,
  'image'  => $photos[0] ?? '',
  'ogtype' => 'product',
  'ldjson' => [
    '@context' => 'https://schema.org',
    '@type' => 'Car',
    'name' => $car['model'],
    'brand' => ['@type'=>'Brand','name'=>'Porsche'],
    'vehicleModelDate' => (string)($car['year'] ?? ''),
    'mileageFromOdometer' => ['@type'=>'QuantitativeValue','value'=>(int)($car['mileage'] ?? 0),'unitCode'=>'KMT'],
    'vehicleTransmission' => $car['transmission'] ?? '',
    'fuelType' => $car['fuel'] ?? '',
    'color' => $car['color'] ?? '',
    'bodyType' => $car['bodyType'] ?? '',
    'image' => !empty($photos[0]) ? $SITE_URL . pcm_img($photos[0]) : null,
    'description' => $car['description'] ?? '',
    'offers' => [
      '@type' => 'Offer',
      'price' => (int)($car['price'] ?? 0),
      'priceCurrency' => 'RUB',
      'availability' => $sold ? 'https://schema.org/SoldOut' : 'https://schema.org/InStock',
      'url' => $SITE_URL . '/cars/' . $car['slug'] . '/',
      'seller' => ['@type'=>'AutoDealer','name'=>$SITE_NAME],
    ],
  ],
];
require __DIR__ . '/../inc/head.php';
require __DIR__ . '/../inc/header.php';
?>
<div class="wrap wrap--mid">
  <nav class="crumbs" aria-label="Хлебные крошки">
    <a href="/">Главная</a><span aria-hidden="true">/</span><a href="/cars/">Авто в наличии</a><span aria-hidden="true">/</span><b><?= e($car['model']) ?></b>
  </nav>

  <?php if ($photos): $pc = count($photos); ?>
    <div class="gal" data-gal>
      <div class="gal__stage">
        <img class="gal__img" data-gal-img src="<?= e(pcm_img($photos[0])) ?>" alt="<?= e($car['model']) ?>, <?= e($car['year']) ?> год" fetchpriority="high" decoding="async">
        <?php if ($pc > 1): ?>
          <button class="gal__nav gal__nav--prev" type="button" data-gal-prev aria-label="Предыдущее фото">
            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="15 18 9 12 15 6"></polyline></svg>
          </button>
          <button class="gal__nav gal__nav--next" type="button" data-gal-next aria-label="Следующее фото">
            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="9 18 15 12 9 6"></polyline></svg>
          </button>
          <div class="gal__count"><span data-gal-cur>1</span> / <?= $pc ?></div>
        <?php endif; ?>
        <button class="gal__zoom" type="button" data-gal-zoom aria-label="Открыть фото на весь экран">
          <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="7"></circle><line x1="16.5" y1="16.5" x2="21" y2="21"></line><line x1="11" y1="8" x2="11" y2="14"></line><line x1="8" y1="11" x2="14" y2="11"></line></svg>
        </button>
      </div>

      <?php if ($pc > 1): ?>
        <div class="gal__thumbs" data-gal-thumbs>
          <?php foreach ($photos as $i => $p): ?>
            <button class="gal__thumb<?= $i === 0 ? ' is-on' : '' ?>" type="button" data-gal-i="<?= $i ?>" aria-label="Фото <?= $i + 1 ?>">
              <img src="<?= e(pcm_img($p)) ?>" alt="<?= e($car['model']) ?> — фото <?= $i + 1 ?>" loading="lazy" decoding="async">
            </button>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <div class="gal__lb" data-gal-lb hidden>
      <button class="gal__lbx" type="button" data-gal-close aria-label="Закрыть">&times;</button>
      <img data-gal-lbimg src="" alt="">
      <?php if ($pc > 1): ?>
        <button class="gal__nav gal__nav--prev" type="button" data-gal-prev aria-label="Предыдущее фото">
          <svg viewBox="0 0 24 24" width="26" height="26" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="15 18 9 12 15 6"></polyline></svg>
        </button>
        <button class="gal__nav gal__nav--next" type="button" data-gal-next aria-label="Следующее фото">
          <svg viewBox="0 0 24 24" width="26" height="26" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="9 18 15 12 9 6"></polyline></svg>
        </button>
        <div class="gal__count gal__count--lb"><span data-gal-cur>1</span> / <?= $pc ?></div>
      <?php endif; ?>
    </div>

    <script>
    (function(){
      var photos = <?= json_encode(array_map('pcm_img', $photos), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
      var wrap = document.querySelector('[data-gal]'), lb = document.querySelector('[data-gal-lb]');
      if (!wrap || !photos.length) return;
      var img = wrap.querySelector('[data-gal-img]'), lbImg = lb.querySelector('[data-gal-lbimg]');
      var thumbs = [].slice.call(wrap.querySelectorAll('[data-gal-i]'));
      var strip = wrap.querySelector('[data-gal-thumbs]');
      var i = 0;

      function show(n){
        i = (n + photos.length) % photos.length;
        img.src = photos[i];
        if (!lb.hidden) lbImg.src = photos[i];
        thumbs.forEach(function(t, k){ t.classList.toggle('is-on', k === i); });
        [].forEach.call(document.querySelectorAll('[data-gal-cur]'), function(el){ el.textContent = i + 1; });
        var t = thumbs[i];
        if (t && strip) {
          var left = t.offsetLeft - (strip.clientWidth - t.clientWidth) / 2;
          strip.scrollTo ? strip.scrollTo({left: left, behavior: 'smooth'}) : strip.scrollLeft = left;
        }
      }
      function openLb(){ lbImg.src = photos[i]; lb.hidden = false; document.documentElement.style.overflow = 'hidden'; }
      function closeLb(){ lb.hidden = true; document.documentElement.style.overflow = ''; }

      thumbs.forEach(function(t){ t.addEventListener('click', function(){ show(+t.getAttribute('data-gal-i')); }); });
      [].forEach.call(document.querySelectorAll('[data-gal-prev]'), function(b){ b.addEventListener('click', function(e){ e.stopPropagation(); show(i - 1); }); });
      [].forEach.call(document.querySelectorAll('[data-gal-next]'), function(b){ b.addEventListener('click', function(e){ e.stopPropagation(); show(i + 1); }); });
      wrap.querySelector('[data-gal-zoom]').addEventListener('click', openLb);
      img.addEventListener('click', openLb);
      lb.querySelector('[data-gal-close]').addEventListener('click', closeLb);
      lb.addEventListener('click', function(e){ if (e.target === lb || e.target === lbImg) closeLb(); });
      document.addEventListener('keydown', function(e){
        if (e.key === 'Escape' && !lb.hidden) closeLb();
        else if (e.key === 'ArrowLeft') show(i - 1);
        else if (e.key === 'ArrowRight') show(i + 1);
      });
      var x0 = null;
      function ts(e){ x0 = e.touches[0].clientX; }
      function te(e){ if (x0 === null) return; var d = e.changedTouches[0].clientX - x0; if (Math.abs(d) > 40) show(d < 0 ? i + 1 : i - 1); x0 = null; }
      [img, lbImg].forEach(function(el){ el.addEventListener('touchstart', ts, {passive:true}); el.addEventListener('touchend', te); });
    })();
    </script>
  <?php endif; ?>

  <div class="sechead" style="margin-top:30px;margin-bottom:0;align-items:flex-start">
    <div>
      <span class="pill"><?= e($car['bodyType']) ?> · <?= e($car['line']) ?></span>
      <h1 style="margin-top:8px"><?= e($car['model']) ?></h1>
      <div class="muted" style="font-size:16px;font-weight:600;margin-top:10px"><?= e($car['year']) ?> · <?= e(pcm_num($car['mileage'])) ?> км · <?= e($car['power']) ?> л.с.</div>
    </div>
    <div style="text-align:right">
      <div class="card__price" style="font-size:clamp(24px,3.4vw,36px)"><?= e(pcm_price($car['price'])) ?></div>
      <span class="badge <?= $sold ? 'badge--sold' : 'badge--ok' ?>" style="margin-top:8px;display:inline-block"><?= $sold ? 'Продано' : 'В наличии' ?></span>
    </div>
  </div>

  <?php if ($dsc['lead']): ?>
    <div class="prose" style="margin-top:18px">
      <?php foreach ($dsc['lead'] as $p): ?><p><?= e($p) ?></p><?php endforeach; ?>
    </div>
  <?php endif; ?>

  <dl class="specs">
    <?php foreach ($specs as $k => $v): ?>
      <div><dt><?= e($k) ?></dt><dd><?= e($v) ?></dd></div>
    <?php endforeach; ?>
  </dl>

  <?php if ($dsc['opts']): ?>
    <section style="margin-top:40px">
      <h2 style="font-size:clamp(21px,2.6vw,28px)">Комплектация автомобиля</h2>
      <ul class="opts">
        <?php foreach ($dsc['opts'] as $o): ?><li><?= e($o) ?></li><?php endforeach; ?>
      </ul>
    </section>
  <?php endif; ?>

  <?php if ($opts): ?>
    <section style="margin-top:40px">
      <h2 style="font-size:clamp(21px,2.6vw,28px)">Оснащение</h2>
      <ul class="opts">
        <?php foreach ($opts as $o): ?><li><?= e($o) ?></li><?php endforeach; ?>
      </ul>
    </section>
  <?php endif; ?>

  <section style="margin-top:44px;background:#fff;border:1px solid #e7e7ea;border-radius:12px;padding:clamp(24px,4vw,40px)">
    <h2 style="font-size:clamp(21px,2.6vw,28px)">Узнать подробности</h2>
    <p class="muted" style="font-size:15px;line-height:1.7;margin-top:10px;max-width:560px">Менеджер отдела продаж расскажет об истории автомобиля, условиях trade-in и вариантах кредита. Возможен подбор по вашим параметрам.</p>
    <div class="cta" style="margin-top:22px">
      <a class="btn" href="tel:<?= e($SITE_PHONE_HREF) ?>"><?= e($SITE_PHONE) ?></a>
      <a class="btn btn--ghost" href="/finance/">Рассчитать кредит</a>
    </div>
    <div style="max-width:420px;margin-top:24px">
      <?= pcm_form('car', ['model' => false, 'submit' => 'Оставить заявку', 'thanks' => 'Спасибо! Менеджер отдела продаж свяжется с вами по этому автомобилю.']) ?>
    </div>
  </section>

  <div style="margin-top:34px"><a href="/cars/" style="font-weight:700;font-size:15px">← Весь каталог</a></div>
</div>
<?php require __DIR__ . '/../inc/footer.php';
