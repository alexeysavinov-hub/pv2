<?php
$docs = json_decode(file_get_contents(__DIR__ . '/../data/legal.json'), true) ?: [];
$doc = null;
foreach ($docs as $d) { if ($d['id'] === $ARG) { $doc = $d; break; } }
if (!$doc) { require __DIR__ . '/404.php'; return; }

$firstText = '';
foreach (($doc['blocks'] ?? []) as $b) { if (($b['t'] ?? '') === 'p') { $firstText = $b['v']; break; } }
if ($firstText === '') $firstText = $doc['paras'][0] ?? $doc['title'];

$PAGE = [
  'title'   => $doc['title'] . ' — ' . $SITE_NAME,
  'desc'    => pcm_excerpt($firstText, 180),
  'url'     => '/legal/' . $doc['id'] . '/',
  'robots'  => 'index,follow',
];
require __DIR__ . '/../inc/head.php';
require __DIR__ . '/../inc/header.php';
?>
<div class="wrap">
  <nav class="crumbs" aria-label="Хлебные крошки"><a href="/">Главная</a><span aria-hidden="true">/</span><b>Правовая информация</b></nav>
  <div class="lgrid">
    <nav class="ltabs" aria-label="Правовые документы">
      <?php foreach ($docs as $d): ?>
        <a href="/legal/<?= e($d['id']) ?>/"<?= $d['id'] === $doc['id'] ? ' aria-current="page"' : '' ?>><?= e($d['title']) ?></a>
      <?php endforeach; ?>
    </nav>
    <article>
      <h1 style="font-size:clamp(24px,3.2vw,36px)"><?= e($doc['title']) ?></h1>
      <div class="prose" style="margin-top:22px">
        <?php if (!empty($doc['blocks'])): ?>
          <?php foreach ($doc['blocks'] as $b): $t = $b['t'] ?? 'p'; $v = $b['v'] ?? ''; ?>
            <?php if ($t === 'h2'): ?>
              <h2 class="lgl__h2"><?= e($v) ?></h2>
            <?php elseif ($t === 'h3'): ?>
              <h3 class="lgl__h3"><?= e($v) ?></h3>
            <?php elseif ($t === 'ul'): ?>
              <ul class="lgl__ul"><?php foreach ((array)$v as $li): ?><li><?= e($li) ?></li><?php endforeach; ?></ul>
            <?php elseif ($t === 'note'): ?>
              <p class="lgl__note"><?= e($v) ?></p>
            <?php elseif ($t === 'sign'): ?>
              <div class="lgl__sign"><?php foreach ((array)$v as $li): ?><div><?= e($li) ?></div><?php endforeach; ?></div>
            <?php else: ?>
              <p><?= e($v) ?></p>
            <?php endif; ?>
          <?php endforeach; ?>
        <?php else: ?>
          <?php foreach (($doc['paras'] ?? []) as $t): ?><p><?= e($t) ?></p><?php endforeach; ?>
        <?php endif; ?>
      </div>
    </article>
  </div>
</div>
<?php require __DIR__ . '/../inc/footer.php';
