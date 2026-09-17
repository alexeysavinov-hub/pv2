<?php
/**
 * Обработка заявок с форм. Письмо уходит на $SITE_EMAIL.
 * Результат: $FORM_OK (id отправленной формы) и $FORM_ERR (текст ошибки).
 */
require_once __DIR__ . '/mail.php';

$FORM_OK = '';
$FORM_ERR = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['pcm_form'])) {
  $id = preg_replace('~[^a-z]~', '', (string)$_POST['pcm_form']);

  // ловушка для спам-ботов: поле скрыто от людей и должно остаться пустым
  if (!empty($_POST['website'])) {
    $FORM_OK = $id;
  } else {
    $name  = trim((string)($_POST['name'] ?? ''));
    $phone = trim((string)($_POST['phone'] ?? ''));
    $email = trim((string)($_POST['email'] ?? ''));
    $model = trim((string)($_POST['model'] ?? ''));
    $date  = trim((string)($_POST['date'] ?? ''));
    $time  = trim((string)($_POST['time'] ?? ''));
    $msg   = trim((string)($_POST['message'] ?? ''));
    $digits = preg_replace('~\D~', '', $phone);

    if (mb_strlen($name) < 2) {
      $FORM_ERR = 'Укажите имя.';
    } elseif (mb_strlen($digits) < 11) {
      $FORM_ERR = 'Укажите телефон полностью — 11 цифр.';
    } elseif ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $FORM_ERR = 'Проверьте адрес электронной почты.';
    } else {
      $labels = ['contact'=>'Обращение с сайта','service'=>'Запись на сервис','finance'=>'Заявка на финансирование','car'=>'Вопрос по автомобилю','callback'=>'Обратный звонок'];
      $subject = ($labels[$id] ?? 'Заявка с сайта') . ' — PCM';

      $lines = ["Форма: " . ($labels[$id] ?? $id), "Имя: $name", "Телефон: $phone"];
      if ($email !== '') $lines[] = "E-mail: $email";
      if ($model !== '') $lines[] = "Автомобиль: $model";
      if ($date  !== '') $lines[] = "Желаемая дата: $date";
      if ($time  !== '') $lines[] = "Удобное время: $time";
      if ($msg   !== '') $lines[] = "Сообщение:\n$msg";
      $lines[] = '';
      $lines[] = 'Страница: ' . ($_POST['page'] ?? '');
      $lines[] = 'Время: ' . date('d.m.Y H:i');
      $body = implode("\n", $lines);

      $to = trim((string)($FORM_EMAILS[$id] ?? $SITE_EMAIL));
      $send = pcm_send_mail($to, $subject, $body, $email);

      // копия заявки в файл — сохраняется всегда, даже если письмо не ушло
      $mark = $send['ok'] ? ('ОТПРАВЛЕНО → ' . $to . ' (' . $send['how'] . ')') : ('НЕ ОТПРАВЛЕНО → ' . $to . ': ' . $send['error']);
      $log = pcm_lead_store("----- " . date('d.m.Y H:i') . " | " . $mark . "-----\n" . $body . "\n\n");

      if (!$send['ok'] && $log === '') {
        $FORM_ERR = 'Не удалось отправить заявку. Пожалуйста, позвоните нам: ' . $SITE_PHONE;
      } else {
        $FORM_OK = $id;
      }
    }
  }
}

/** Разметка формы заявки. $id: contact|service|finance. */
function pcm_form($id, $opts = []) {
  global $FORM_OK, $FORM_ERR, $SITE_PHONE;
  $sent = ($FORM_OK === $id);
  $err  = ($FORM_ERR !== '' && !empty($_POST['pcm_form']) && $_POST['pcm_form'] === $id) ? $FORM_ERR : '';
  $page = $_SERVER['REQUEST_URI'] ?? '';
  ob_start(); ?>
  <?php if ($sent): ?>
    <div class="fsent"><?= e($opts['thanks'] ?? 'Спасибо! Заявка отправлена — мы свяжемся с вами в ближайшее время.') ?></div>
  <?php else: ?>
    <form class="form<?= $id === 'service' ? ' form--grid' : '' ?>" method="post" action="#form-<?= e($id) ?>" id="form-<?= e($id) ?>">
      <input type="hidden" name="pcm_form" value="<?= e($id) ?>">
      <input type="hidden" name="page" value="<?= e($page) ?>">
      <label class="hp">Не заполняйте это поле<input type="text" name="website" tabindex="-1" autocomplete="off"></label>
      <input required name="name" autocomplete="name" placeholder="Ваше имя" value="<?= e($_POST['name'] ?? '') ?>">
      <input required name="phone" type="tel" autocomplete="tel" inputmode="tel" placeholder="+7 (___) ___-__-__" value="<?= e($_POST['phone'] ?? '') ?>">
      <?php if (!empty($opts['email'])): ?>
        <input type="email" name="email" autocomplete="email" inputmode="email" placeholder="E-mail" value="<?= e($_POST['email'] ?? '') ?>">
      <?php endif; ?>
      <?php if (!empty($opts['model'])): ?>
        <input name="model" placeholder="Модель автомобиля" value="<?= e($_POST['model'] ?? '') ?>">
        <input type="date" name="date" aria-label="Желаемая дата" min="<?= date('Y-m-d') ?>" value="<?= e($_POST['date'] ?? '') ?>">
      <?php endif; ?>
      <?php if (!empty($opts['message'])): ?>
        <textarea name="message" rows="4" placeholder="Сообщение"><?= e($_POST['message'] ?? '') ?></textarea>
      <?php endif; ?>
      <?php if ($err): ?><div class="ferr" role="alert"><?= e($err) ?></div><?php endif; ?>
      <button class="btn btn--wide" type="submit"><?= e($opts['submit'] ?? 'Отправить заявку') ?></button>
      <p class="fnote">Нажимая кнопку, вы соглашаетесь с <a href="/legal/consent/">обработкой персональных данных</a>.</p>
    </form>
  <?php endif;
  return ob_get_clean();
}

/** Виджет обратного звонка: плавающая кнопка + модальное окно. */
function pcm_callback_widget() {
  global $FORM_OK, $FORM_ERR, $SITE_PHONE, $SITE_PHONE_HREF;
  $sent = ($FORM_OK === 'callback');
  $err  = ($FORM_ERR !== '' && ($_POST['pcm_form'] ?? '') === 'callback') ? $FORM_ERR : '';
  $open = $sent || $err !== '';
  $page = $_SERVER['REQUEST_URI'] ?? '';
  $slots = ['Как можно скорее', 'Сегодня до 14:00', 'Сегодня после 14:00', 'Завтра'];
  ?>
  <div class="cbw" data-cbw<?= $open ? ' data-cbw-open' : '' ?>>
    <button class="cbw__fab" type="button" data-cbw-open-btn aria-label="Заказать обратный звонок">
      <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <path d="M6.6 10.8a15.1 15.1 0 0 0 6.6 6.6l2.2-2.2a1 1 0 0 1 1-.25 11.4 11.4 0 0 0 3.6.58 1 1 0 0 1 1 1V20a1 1 0 0 1-1 1A17 17 0 0 1 3 4a1 1 0 0 1 1-1h3.5a1 1 0 0 1 1 1c0 1.25.2 2.46.57 3.6a1 1 0 0 1-.25 1z"></path>
      </svg>
      <span class="cbw__fab-t">Обратный звонок</span>
    </button>

    <div class="cbw__ovl" data-cbw-close hidden></div>
    <div class="cbw__box" role="dialog" aria-modal="true" aria-label="Обратный звонок" hidden>
      <button class="cbw__x" type="button" data-cbw-close aria-label="Закрыть">&times;</button>
      <?php if ($sent): ?>
        <div class="cbw__hd">
          <div class="cbw__ttl">Заявка принята</div>
          <div class="cbw__sub">Менеджер перезвонит вам в рабочее время: пн–вс, 9:00–21:00.</div>
        </div>
        <a class="btn btn--ghost btn--wide" href="tel:<?= e($SITE_PHONE_HREF) ?>"><?= e($SITE_PHONE) ?></a>
      <?php else: ?>
        <div class="cbw__hd">
          <div class="cbw__ttl">Заказать обратный звонок</div>
          <div class="cbw__sub">Перезвоним в течение 15 минут в рабочее время.</div>
        </div>
        <form class="form cbw__form" method="post" action="<?= e($page) ?>">
          <input type="hidden" name="pcm_form" value="callback">
          <input type="hidden" name="page" value="<?= e($page) ?>">
          <label class="hp">Не заполняйте это поле<input type="text" name="website" tabindex="-1" autocomplete="off"></label>
          <input required name="name" autocomplete="name" placeholder="Ваше имя" value="<?= e($_POST['name'] ?? '') ?>">
          <input required name="phone" type="tel" autocomplete="tel" inputmode="tel" placeholder="+7 (___) ___-__-__" value="<?= e($_POST['phone'] ?? '') ?>">
          <div class="cbw__slots">
            <?php foreach ($slots as $i => $s): ?>
              <label class="cbw__slot">
                <input type="radio" name="time" value="<?= e($s) ?>"<?= (($_POST['time'] ?? ($i === 0 ? $s : '')) === $s) ? ' checked' : '' ?>>
                <span><?= e($s) ?></span>
              </label>
            <?php endforeach; ?>
          </div>
          <?php if ($err): ?><div class="ferr" role="alert"><?= e($err) ?></div><?php endif; ?>
          <button class="btn btn--wide" type="submit">Жду звонка</button>
          <p class="fnote">Нажимая кнопку, вы соглашаетесь с <a href="/legal/consent/">обработкой персональных данных</a>.</p>
        </form>
      <?php endif; ?>
    </div>
  </div>
  <script>
  (function(){
    var w=document.querySelector('[data-cbw]'); if(!w) return;
    var ovl=w.querySelector('.cbw__ovl'), box=w.querySelector('.cbw__box');
    function open(){ovl.hidden=false;box.hidden=false;w.classList.add('is-open');document.documentElement.style.overflow='hidden';var f=box.querySelector('input[name=name]');if(f)setTimeout(function(){f.focus()},80);}
    function close(){w.classList.remove('is-open');document.documentElement.style.overflow='';setTimeout(function(){ovl.hidden=true;box.hidden=true},200);}
    w.querySelector('[data-cbw-open-btn]').addEventListener('click',open);
    w.querySelectorAll('[data-cbw-close]').forEach(function(el){el.addEventListener('click',close)});
    document.addEventListener('keydown',function(e){if(e.key==='Escape'&&w.classList.contains('is-open'))close()});
    document.querySelectorAll('a[href="#callback"],[data-callback]').forEach(function(el){el.addEventListener('click',function(e){e.preventDefault();open()})});
    if(w.hasAttribute('data-cbw-open')) open();
  })();
  </script>
  <?php
}
