<?php
require_once __DIR__ . '/theme.php';
$title = $PAGE['title'] ?? $SITE_NAME;
$desc  = $PAGE['desc'] ?? '';
$canon = $SITE_URL . ($PAGE['url'] ?? '/');
$ogimg = isset($PAGE['image']) && $PAGE['image'] !== '' ? $PAGE['image'] : '/images/contacts-hero.png';
if (strpos($ogimg, 'http') !== 0 && strpos($ogimg, 'data:') !== 0) $ogimg = $SITE_URL . pcm_img($ogimg);
$noindex = !empty($NOINDEX);
if ($noindex && !headers_sent()) header('X-Robots-Tag: noindex, nofollow', true);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="theme-color" content="#0b0b0d">
<link rel="icon" type="image/png" href="/images/logo-pcm-square.png">
<link rel="apple-touch-icon" href="/images/logo-pcm-square.png">
<title><?= e($title) ?></title>
<meta name="description" content="<?= e($desc) ?>">
<link rel="canonical" href="<?= e($canon) ?>">
<meta name="robots" content="<?= $noindex ? 'noindex, nofollow' : e($PAGE['robots'] ?? 'index,follow') ?>">
<meta property="og:type" content="<?= e($PAGE['ogtype'] ?? 'website') ?>">
<meta property="og:title" content="<?= e($title) ?>">
<meta property="og:description" content="<?= e($desc) ?>">
<meta property="og:url" content="<?= e($canon) ?>">
<meta property="og:image" content="<?= e($ogimg) ?>">
<meta property="og:locale" content="ru_RU">
<meta property="og:site_name" content="<?= e($SITE_NAME) ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Exo+2:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/style3.css?v=<?= @filemtime(__DIR__ . '/../assets/style3.css') ?: 3 ?>">
<script type="application/ld+json"><?= json_encode([
  '@context' => 'https://schema.org',
  '@type' => 'AutoDealer',
  'name' => $SITE_NAME,
  'url' => $SITE_URL,
  'image' => $SITE_URL . '/images/contacts-hero.png',
  'telephone' => $SITE_PHONE_HREF,
  'email' => $SITE_EMAIL,
  'priceRange' => '$$$$',
  'address' => ['@type'=>'PostalAddress','streetAddress'=>'Ленинградское шоссе, 71А, стр. 10','addressLocality'=>'Москва','postalCode'=>'125445','addressCountry'=>'RU'],
  'brand' => ['@type'=>'Brand','name'=>'Porsche'],
  'openingHours' => 'Mo-Su 09:00-21:00'
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
<?php if (!empty($PAGE['ldjson'])): ?>
<script type="application/ld+json"><?= json_encode($PAGE['ldjson'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
<?php endif; ?>
<?php if (!empty($YM_ID)): ?>
<script>
(function(m,e,t,r,i,k,a){m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};
m[i].l=1*new Date();
for (var j=0;j<document.scripts.length;j++){if(document.scripts[j].src===r){return;}}
k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)})
(window,document,'script','https://mc.yandex.ru/metrika/tag.js','ym');
ym(<?= (int)$YM_ID ?>, 'init', {ssr:true, webvisor:true, clickmap:true, trackLinks:true, accurateTrackBounce:true});
<?php if (!empty($FORM_OK)): ?>
ym(<?= (int)$YM_ID ?>, 'reachGoal', 'form_<?= e($FORM_OK) ?>');
ym(<?= (int)$YM_ID ?>, 'reachGoal', 'lead');
<?php endif; ?>
document.addEventListener('click', function(ev){
  var a = ev.target.closest && ev.target.closest('a[href^="tel:"]');
  if (a) ym(<?= (int)$YM_ID ?>, 'reachGoal', 'call');
}, true);
</script>
<?php endif; ?>
</head>
<body>
<?php if (!empty($YM_ID)): ?>
<noscript><div><img src="https://mc.yandex.ru/watch/<?= (int)$YM_ID ?>" style="position:absolute;left:-9999px" alt=""></div></noscript>
<?php endif; ?>
