<?php
mb_internal_encoding('UTF-8');
@ini_set('display_errors', '0');
error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);
require __DIR__ . '/inc/config.php';
require __DIR__ . '/inc/helpers.php';
require __DIR__ . '/inc/data.php';
header('Content-Type: application/xml; charset=utf-8');
if (!empty($NOINDEX)) { header('X-Robots-Tag: noindex', true); http_response_code(404); echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\"></urlset>\n"; exit; }

$urls = [
  ['/', '1.0', 'daily'],
  ['/cars/', '0.9', 'daily'],
  ['/models/', '0.8', 'weekly'],
  ['/service/', '0.8', 'weekly'],
  ['/service/parts/', '0.6', 'monthly'],
  ['/service/body/', '0.6', 'monthly'],
  ['/service/poa/', '0.5', 'monthly'],
  ['/service/offers/', '0.7', 'weekly'],
  ['/finance/', '0.7', 'monthly'],
  ['/finance/credits/', '0.6', 'monthly'],
  ['/finance/leasing/', '0.6', 'monthly'],
  ['/finance/insurance/', '0.6', 'monthly'],
  ['/news/', '0.8', 'daily'],
  ['/about/', '0.6', 'monthly'],
  ['/contacts/', '0.7', 'monthly'],
  ['/legal/pdn/', '0.2', 'yearly'],
  ['/legal/cookie/', '0.2', 'yearly'],
  ['/legal/consent/', '0.2', 'yearly'],
];

$models = json_decode(@file_get_contents(__DIR__ . '/data/models.json'), true) ?: [];
foreach ($models as $m) $urls[] = ['/models/' . $m['slug'] . '/', '0.7', 'weekly'];

foreach (['management','sales','service','clients'] as $d) $urls[] = ['/about/team/' . $d . '/', '0.4', 'monthly'];

foreach (pcm_cars() as $c) $urls[] = ['/cars/' . $c['slug'] . '/', '0.8', 'weekly'];

$news = pcm_news();
foreach ($news as $n) $urls[] = [$n['url'], '0.6', 'monthly', $n['date'] ?? ''];

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
foreach ($urls as $u) {
  echo "  <url>\n";
  echo '    <loc>' . e($SITE_URL . $u[0]) . "</loc>\n";
  if (!empty($u[3])) echo '    <lastmod>' . e(date('Y-m-d', strtotime($u[3]))) . "</lastmod>\n";
  echo '    <changefreq>' . e($u[2]) . "</changefreq>\n";
  echo '    <priority>' . e($u[1]) . "</priority>\n";
  echo "  </url>\n";
}
echo '</urlset>' . "\n";
