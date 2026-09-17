<?php
/**
 * Отправка писем. Сначала пробует SMTP (если заполнен в config.php),
 * иначе — встроенную функцию mail().
 * Возвращает массив: ['ok' => bool, 'how' => 'smtp|mail', 'error' => 'текст'].
 */

function pcm_send_mail($to, $subject, $body, $replyTo = '') {
  global $SMTP_HOST, $SMTP_PORT, $SMTP_USER, $SMTP_PASS, $SMTP_SECURE, $SMTP_FROM;

  $subjEnc = '=?UTF-8?B?' . base64_encode($subject) . '?=';
  $from    = ($SMTP_FROM ?: ('no-reply@' . preg_replace('~^www\.~', '', $_SERVER['HTTP_HOST'] ?? 'localhost')));

  if (!empty($SMTP_HOST) && !empty($SMTP_USER)) {
    $r = pcm_smtp_send($to, $subjEnc, $body, $from, $replyTo);
    if ($r['ok']) return ['ok' => true, 'how' => 'smtp', 'error' => ''];
    // если SMTP не сработал — пробуем обычную почту
    $r2 = pcm_php_mail($to, $subjEnc, $body, $from, $replyTo);
    return ['ok' => $r2, 'how' => 'mail', 'error' => 'SMTP: ' . $r['error'] . ($r2 ? ' (отправлено через mail())' : '; mail() тоже не принял письмо')];
  }

  $ok = pcm_php_mail($to, $subjEnc, $body, $from, $replyTo);
  return ['ok' => $ok, 'how' => 'mail', 'error' => $ok ? '' : 'mail() вернула false — почта на хостинге не настроена'];
}

/** Куда писать копии заявок: сначала data/, если закрыта — во временную папку. */
function pcm_lead_log_path() {
  $primary = __DIR__ . '/../data/leads.log';
  $dir = dirname($primary);
  if ((is_file($primary) && is_writable($primary)) || (is_dir($dir) && is_writable($dir))) return $primary;
  return rtrim(sys_get_temp_dir(), '/\\') . '/pcm-leads.log';
}

/** Сохраняет заявку в файл. Возвращает путь или '' если записать не удалось. */
function pcm_lead_store($text) {
  $p = pcm_lead_log_path();
  return @file_put_contents($p, $text, FILE_APPEND) !== false ? $p : '';
}

function pcm_php_mail($to, $subjEnc, $body, $from, $replyTo = '') {
  $host = preg_replace('~^www\.~', '', $_SERVER['HTTP_HOST'] ?? 'localhost');
  $h  = "MIME-Version: 1.0\r\n";
  $h .= "Date: " . date('r') . "\r\n";
  $h .= "Message-ID: <" . bin2hex(random_bytes(8)) . '@' . $host . ">\r\n";
  $h .= "Content-Type: text/plain; charset=utf-8\r\n";
  $h .= "Content-Transfer-Encoding: 8bit\r\n";
  $h .= "From: PCM <$from>\r\n";
  $h .= "Return-Path: <$from>\r\n";
  if ($replyTo !== '') $h .= "Reply-To: $replyTo\r\n";
  return @mail($to, $subjEnc, $body, $h, '-f' . $from);
}

/** Минимальный SMTP-клиент (AUTH LOGIN), без внешних библиотек. */
function pcm_smtp_send($to, $subjEnc, $body, $from, $replyTo = '') {
  global $SMTP_HOST, $SMTP_PORT, $SMTP_USER, $SMTP_PASS, $SMTP_SECURE;

  $port   = (int)($SMTP_PORT ?: 465);
  $secure = strtolower((string)$SMTP_SECURE); // ssl | tls | ''
  $host   = ($secure === 'ssl' ? 'ssl://' : '') . $SMTP_HOST;
  $errno = 0; $errstr = '';
  $fp = @fsockopen($host, $port, $errno, $errstr, 10);
  if (!$fp) return ['ok' => false, 'error' => "не удалось подключиться к $SMTP_HOST:$port ($errstr)"];
  stream_set_timeout($fp, 10);

  $read = function () use ($fp) {
    $data = '';
    while (($line = fgets($fp, 515)) !== false) {
      $data .= $line;
      if (strlen($line) < 4 || $line[3] !== '-') break;
    }
    return $data;
  };
  $cmd = function ($c, $expect) use ($fp, $read, &$err) {
    if ($c !== null) fwrite($fp, $c . "\r\n");
    $r = $read();
    if ((int)substr($r, 0, 3) !== $expect) { $err = trim($r) . ($c !== null ? " (на команду: " . preg_replace('~^(AUTH|\S+)\s.*~', '$1 …', $c) . ")" : ''); return false; }
    return true;
  };

  $err = '';
  $ehlo = 'EHLO ' . (preg_replace('~^www\.~', '', $_SERVER['HTTP_HOST'] ?? 'localhost'));
  if (!$cmd(null, 220))   { fclose($fp); return ['ok' => false, 'error' => $err]; }
  if (!$cmd($ehlo, 250))  { fclose($fp); return ['ok' => false, 'error' => $err]; }

  if ($secure === 'tls') {
    if (!$cmd('STARTTLS', 220)) { fclose($fp); return ['ok' => false, 'error' => $err]; }
    if (!@stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) { fclose($fp); return ['ok' => false, 'error' => 'не удалось включить шифрование TLS']; }
    if (!$cmd($ehlo, 250)) { fclose($fp); return ['ok' => false, 'error' => $err]; }
  }

  if (!$cmd('AUTH LOGIN', 334))                  { fclose($fp); return ['ok' => false, 'error' => $err]; }
  if (!$cmd(base64_encode($SMTP_USER), 334))     { fclose($fp); return ['ok' => false, 'error' => 'логин не принят: ' . $err]; }
  if (!$cmd(base64_encode($SMTP_PASS), 235))     { fclose($fp); return ['ok' => false, 'error' => 'пароль не принят: ' . $err]; }
  if (!$cmd("MAIL FROM:<$from>", 250))           { fclose($fp); return ['ok' => false, 'error' => $err]; }
  if (!$cmd("RCPT TO:<$to>", 250))               { fclose($fp); return ['ok' => false, 'error' => $err]; }
  if (!$cmd('DATA', 354))                        { fclose($fp); return ['ok' => false, 'error' => $err]; }

  $headers = "From: PCM <$from>\r\nTo: <$to>\r\nSubject: $subjEnc\r\n";
  if ($replyTo !== '') $headers .= "Reply-To: $replyTo\r\n";
  $headers .= "Date: " . date('r') . "\r\nMIME-Version: 1.0\r\nContent-Type: text/plain; charset=utf-8\r\nContent-Transfer-Encoding: base64\r\n";
  $data = $headers . "\r\n" . chunk_split(base64_encode($body), 76, "\r\n");
  fwrite($fp, $data . "\r\n.\r\n");
  if (!$cmd(null, 250)) { fclose($fp); return ['ok' => false, 'error' => 'сервер не принял письмо: ' . $err]; }

  fwrite($fp, "QUIT\r\n");
  fclose($fp);
  return ['ok' => true, 'error' => ''];
}
