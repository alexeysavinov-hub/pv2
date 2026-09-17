<?php
$team = pcm_team();
$g = null;
foreach ($team as $x) { if ($x['id'] === $ARG) { $g = $x; break; } }
if (!$g) { require __DIR__ . '/404.php'; return; }

$PAGE = [
  'title' => $g['dept'] . ' — команда ' . $SITE_NAME,
  'desc'  => $g['dept'] . ' PCM: контакты специалистов — телефоны и электронная почта. ' . count($g['members']) . ' сотрудников.',
  'url'   => '/about/team/' . $g['id'] . '/',
  'nav'   => '/about/',
  'image' => $g['hero'] ?? '',
];
require __DIR__ . '/../inc/head.php';
require __DIR__ . '/../inc/header.php';
?>
<div class="wrap">
  <nav class="crumbs" aria-label="Хлебные крошки"><a href="/">Главная</a><span aria-hidden="true">/</span><a href="/about/">О компании</a><span aria-hidden="true">/</span><b><?= e($g['dept']) ?></b></nav>
  <?php if (!empty($g['hero'])): ?>
    <div class="hero">
      <img src="<?= e(pcm_img($g['hero'])) ?>" alt="<?= e($g['dept']) ?> — PCM" fetchpriority="high" decoding="async">
      <div class="hero__in"><div>
        <div class="eyebrow" style="color:rgba(255,255,255,.72);margin-bottom:10px">Команда PCM</div>
        <h1><?= e($g['dept']) ?></h1>
      </div></div>
    </div>
  <?php else: ?>
    <h1><?= e($g['dept']) ?></h1>
  <?php endif; ?>

  <div class="grid" style="grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:18px;margin-top:clamp(26px,4vw,40px)">
    <?php foreach ($g['members'] as $m): ?>
      <div class="person">
        <div class="person__ph">
          <?php if (!empty($m['photo'])): ?>
            <img src="<?= e(pcm_img($m['photo'])) ?>" alt="<?= e($m['name']) ?> — <?= e($m['role']) ?>" loading="lazy" decoding="async">
          <?php else: ?>
            <span class="person__ini"><?= e($m['initials'] ?? '') ?></span>
          <?php endif; ?>
        </div>
        <div class="person__b">
          <h2 style="font-size:18px"><?= e($m['name']) ?></h2>
          <div class="muted" style="font-size:13.5px;line-height:1.5;margin-top:5px"><?= e($m['role']) ?></div>
          <?php if (!empty($m['phone'])): ?><div class="person__c"><?= e($m['phone']) ?></div><?php endif; ?>
          <?php if (!empty($m['email'])): ?><a class="person__c person__c--l" href="mailto:<?= e($m['email']) ?>"><?= e($m['email']) ?></a><?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="teamnav">
    <?php foreach ($team as $x): if ($x['id'] === $g['id']) continue; ?>
      <a class="chip" href="/about/team/<?= e($x['id']) ?>/"><?= e($x['dept']) ?></a>
    <?php endforeach; ?>
  </div>
</div>
<?php require __DIR__ . '/../inc/footer.php';
