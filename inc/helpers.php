<?php
/** Утилиты: экранирование, транслитерация, форматы. */

function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }

function pcm_slug($s) {
  $map = [
    'а'=>'a','б'=>'b','в'=>'v','г'=>'g','д'=>'d','е'=>'e','ё'=>'e','ж'=>'zh','з'=>'z',
    'и'=>'i','й'=>'y','к'=>'k','л'=>'l','м'=>'m','н'=>'n','о'=>'o','п'=>'p','р'=>'r',
    'с'=>'s','т'=>'t','у'=>'u','ф'=>'f','х'=>'h','ц'=>'c','ч'=>'ch','ш'=>'sh','щ'=>'sch',
    'ъ'=>'','ы'=>'y','ь'=>'','э'=>'e','ю'=>'yu','я'=>'ya'
  ];
  $s = mb_strtolower(trim((string)$s), 'UTF-8');
  $s = strtr($s, $map);
  $s = preg_replace('~[^a-z0-9]+~u', '-', $s);
  return trim((string)$s, '-');
}

function pcm_price($p) {
  $p = (int)$p;
  if ($p <= 0) return 'Цена по запросу';
  return number_format($p, 0, '', ' ') . ' ₽';
}

function pcm_num($n) { return number_format((int)$n, 0, '', ' '); }

function pcm_date($iso) {
  static $M = [1=>'января','февраля','марта','апреля','мая','июня','июля','августа','сентября','октября','ноября','декабря'];
  $ts = strtotime((string)$iso);
  if (!$ts) return (string)$iso;
  return (int)date('j', $ts) . ' ' . $M[(int)date('n', $ts)] . ' ' . date('Y', $ts);
}

/** Картинка: приводим к абсолютному пути от корня, data-URL оставляем как есть. */
function pcm_img($src) {
  $src = (string)$src;
  if ($src === '') return '';
  if (strpos($src, 'data:') === 0 || strpos($src, 'http') === 0 || $src[0] === '/') return $src;
  return '/' . ltrim($src, '/');
}

/** Разбивка текста на абзацы по пустой строке. */
function pcm_paras($body) {
  $parts = preg_split('~\R\s*\R~u', (string)$body);
  return array_values(array_filter(array_map('trim', $parts), 'strlen'));
}

function pcm_excerpt($s, $len = 160) {
  $s = trim(preg_replace('~\s+~u', ' ', strip_tags((string)$s)));
  if (mb_strlen($s, 'UTF-8') <= $len) return $s;
  return mb_substr($s, 0, $len - 1, 'UTF-8') . '…';
}

/**
 * Типографика заголовков: не даёт «висеть» коротким словам и цифрам.
 * Плюс перевод строки в тексте превращается в <br> — так редактор может
 * задать перенос вручную, просто нажав Enter в поле заголовка.
 * Возвращает ГОТОВЫЙ HTML — повторно экранировать не нужно.
 */
function pcm_type($s) {
  $s = e(trim((string)$s));

  // короткие слова и предлоги приклеиваем к следующему слову
  $s = preg_replace('~(^|\s)([a-zA-Zа-яА-ЯёЁ]{1,2}|от|до|на|за|по|из|для|при|над|под|без)\s+~u', '$1$2&nbsp;', $s);

  // одиночная цифра или короткое число после слова — тоже неразрывно (Mobil 1, Cayenne 4)
  $s = preg_replace('~\s+(\d{1,3})(?=\s|$|[.,])~u', '&nbsp;$1', $s);

  // пробел перед единицами измерения и валютой
  $s = preg_replace('~\s+(руб\.?|₽|км|л\.с\.|года?|лет|%)~u', '&nbsp;$1', $s);

  // разряды в числах: 24 911 -> неразрывно
  $s = preg_replace('~(\d)\s+(\d{3})~u', '$1&nbsp;$2', $s);

  // тире не должно начинать строку
  $s = preg_replace('~\s+([—–-])\s~u', '&nbsp;$1 ', $s);

  // явный перенос строки, заданный редактором
  return nl2br($s, false);
}

/**
 * Разметка текста новости.
 * Редактор пишет обычным текстом, а на сайте получается вёрстка:
 *
 *   ## Заголовок              -> подзаголовок
 *   ### Подзаголовок          -> мелкий подзаголовок
 *   - пункт                   -> список
 *   1. пункт                  -> нумерованный список
 *   | Модель | Цена |         -> таблица (первая строка — шапка)
 *   | 911    | 24 911 ₽ |
 *   **важное**                -> жирным
 *   > примечание              -> выделенная врезка
 *   ---                       -> разделитель
 */
function pcm_rich($body) {
  $body = (string)$body;

  // если вставлен готовый HTML с таблицей — оставляем разметку, вычистив мусор
  if (stripos($body, '<table') !== false) {
    $body = preg_replace('~<(script|style)[^>]*>.*?</\1>~is', '', $body);
    $body = preg_replace('~\s(style|class|width|height|border|cellpadding|cellspacing|bgcolor|align|valign|lang|dir)="[^"]*"~i', '', $body);
    $body = preg_replace('~<(/?)(o:p|span|font|div)[^>]*>~i', '', $body);
    $body = preg_replace('~<table~i', '<table class="art__table"', $body);
    $allowed = '<p><br><b><strong><i><em><u><ul><ol><li><h2><h3><h4><a><table><thead><tbody><tr><th><td><hr><blockquote>';
    $body = strip_tags($body, $allowed);
    return '<div class="art__tablewrap art__tablewrap--raw">' . $body . '</div>';
  }

  $lines = preg_split('~\R~u', $body);
  $out = '';
  $buf = [];          // копим абзац
  $list = null;       // 'ul' | 'ol'
  $items = [];
  $table = [];

  $inline = function($s) {
    $s = e(trim($s));
    $s = preg_replace('~\*\*(.+?)\*\*~u', '<strong>$1</strong>', $s);
    $s = preg_replace('~(?<![\w/])\*(.+?)\*(?!\w)~u', '<em>$1</em>', $s);
    return $s;
  };

  $flushP = function() use (&$buf, &$out, $inline) {
    if (!$buf) return;
    $out .= '<p>' . $inline(implode(' ', $buf)) . "</p>\n";
    $buf = [];
  };
  $flushList = function() use (&$list, &$items, &$out, $inline) {
    if (!$list || !$items) { $list = null; $items = []; return; }
    $out .= '<' . $list . ' class="art__list">' . "\n";
    foreach ($items as $it) $out .= '  <li>' . $inline($it) . "</li>\n";
    $out .= '</' . $list . ">\n";
    $list = null; $items = [];
  };
  $flushTable = function() use (&$table, &$out, $inline) {
    if (!$table) return;
    $head = array_shift($table);
    $n = count($head);
    $out .= '<div class="art__tablewrap"><table class="art__table">' . "\n<thead><tr>";
    foreach ($head as $c) $out .= '<th>' . $inline($c) . '</th>';
    $out .= "</tr></thead>\n<tbody>\n";
    foreach ($table as $row) {
      $out .= '<tr>';
      foreach (array_values($row) as $i => $c) {
        $cls = $i === 0 ? ' class="c1"' : ($i === $n - 1 && $n > 2 ? ' class="cl"' : '');
        $out .= '<td' . $cls . '>' . $inline($c) . '</td>';
      }
      $out .= "</tr>\n";
    }
    $out .= "</tbody>\n</table></div>\n";
    $table = [];
  };
  $flushAll = function() use ($flushP, $flushList, $flushTable) { $flushP(); $flushList(); $flushTable(); };

  foreach ($lines as $raw) {
    $l = trim($raw);

    // пустая строка закрывает текущий блок
    if ($l === '') { $flushAll(); continue; }

    // таблица, вставленная из Excel или Word — колонки разделены табуляцией
    if (strpos($raw, "\t") !== false) {
      $cells = array_map('trim', explode("\t", rtrim($raw, "\t")));
      if (count($cells) >= 2) { $flushP(); $flushList(); $table[] = $cells; continue; }
    }

    // таблица в разметке: | Модель | Цена |
    if (strpos($l, '|') === 0 || (substr_count($l, '|') >= 2 && preg_match('~^\|?.+\|.+~u', $l) && strpos($l, '|') !== false)) {
      $cells = array_map('trim', explode('|', trim($l, '|')));
      // строка-разделитель шапки (|---|---|) пропускается
      $isSep = true;
      foreach ($cells as $c) { if (!preg_match('~^:?-{2,}:?$~', $c)) { $isSep = false; break; } }
      if ($isSep) continue;
      $flushP(); $flushList();
      $table[] = $cells;
      continue;
    }
    if ($table) $flushTable();

    // разделитель
    if (preg_match('~^-{3,}$~', $l)) { $flushAll(); $out .= "<hr class=\"art__hr\">\n"; continue; }

    // заголовки
    if (preg_match('~^(#{2,4})\s+(.+)$~u', $l, $m)) {
      $flushAll();
      $tag = ['##' => 'h2', '###' => 'h3', '####' => 'h4'][$m[1]] ?? 'h3';
      $out .= '<' . $tag . ' class="art__h">' . $inline($m[2]) . '</' . $tag . ">\n";
      continue;
    }

    // врезка
    if (preg_match('~^>\s?(.+)$~u', $l, $m)) {
      $flushAll();
      $out .= '<blockquote class="art__quote">' . $inline($m[1]) . "</blockquote>\n";
      continue;
    }

    // списки
    if (preg_match('~^[-*•]\s+(.+)$~u', $l, $m)) {
      $flushP();
      if ($list !== 'ul') { $flushList(); $list = 'ul'; }
      $items[] = $m[1];
      continue;
    }
    if (preg_match('~^\d+[.)]\s+(.+)$~u', $l, $m)) {
      $flushP();
      if ($list !== 'ol') { $flushList(); $list = 'ol'; }
      $items[] = $m[1];
      continue;
    }
    if ($list) $flushList();

    $buf[] = $l;
  }
  $flushAll();
  return $out;
}

/**
 * Описание из фида MaxPoster: свободный текст вперемешку со списком опций.
 * Возвращает ['lead' => абзацы, 'opts' => пункты списка].
 */
function pcm_desc_split($text, $model = '') {
  $skip = ['комплектация автомобиля', 'комплектация', 'дополнительное оборудование', 'опции', 'оснащение'];
  $modelNorm = mb_strtolower(trim(preg_replace('~\s+~u', ' ', (string)$model)));
  $lead = []; $opts = [];

  foreach (preg_split('~\R~u', (string)$text) as $raw) {
    $l = trim(str_replace('*', '', (string)$raw));
    $l = trim(preg_replace('~[ \t]+~u', ' ', $l));
    $l = trim($l, " \t-–—•·:");
    if ($l === '') continue;

    $low = mb_strtolower($l);
    if (in_array(rtrim($low, ' :'), $skip, true)) continue;
    if ($modelNorm !== '' && ($low === $modelNorm || mb_strpos($modelNorm, $low) !== false && mb_strlen($low) > 6)) continue;

    // законченная фраза — во вступление (пока не начался список)
    $isSentence = preg_match('~[.!?]$~u', $l) || mb_strlen($l) > 90;
    if ($isSentence && !$opts) { $lead[] = $l; continue; }

    // убираем заводской код в начале строки: «8JU », «275 - », «P31 »
    $l = (string)preg_replace('~^([A-Z0-9]{2,4})\s*[-–—]?\s+(?=\S)~u', '', $l);
    $l = trim($l, " \t-–—:");
    if (mb_strlen($l) < 3) continue;

    $opts[$mb = mb_strtolower($l)] = $l;   // ключ — для отсева дублей
  }

  return ['lead' => $lead, 'opts' => array_values($opts)];
}
