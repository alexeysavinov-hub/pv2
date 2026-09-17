# PCM — сайт дилера Porsche (pcm2.ru)

Многостраничный сайт на PHP без фреймворков и сборки: красивые URL через `.htaccess`,
контент в JSON-файлах (`data/`), админ-панель, импорт каталога из фидов MaxPoster.
Дизайн v3 «Студия» (тёмная шапка, Exo 2, тёплая бумажная подложка), адаптив 1040 / 900 / 720 px.

Требования: PHP 7.4+ (проверено на 8.5), `mod_rewrite`, права на запись в `data/`.

## Структура

```
.htaccess          красивые адреса, кэш, сжатие, редирект HTTPS (закомментирован)
index.php          фронт-контроллер: разбирает URL → pages/*.php
admin.html         админ-панель (открывается по /admin/)
api/               JSON-API для админки (GET/PUT cars, news, slides, offers, team …)
assets/
  style3.css       тема v3 — единственный стиль сайта (style.css, style2.css — старые темы)
  site.js          карусель главного экрана
data/*.json        контент: cars, news, slides, offers, models, versions, finance, team, legal
images/            изображения сайта
inc/
  config.sample.php  образец настроек → скопировать в inc/config.php (в git не попадает)
  helpers.php        экранирование, транслит, типографика, разметка новостей
  data.php           чтение JSON, сортировка каталога, слаги
  form.php, mail.php формы заявок, отправка почты (mail() или SMTP), журнал data/leads.log
  feed.php           разбор фидов MaxPoster
  head.php / header.php / footer.php / theme.php   каркас страницы
pages/             шаблоны разделов (home, cars, car, models, service, finance, news, about, contacts, legal …)
tractors/          отдельный лендинг
import-cars.php    импорт каталога (по ключу или cron)
backup.php         архив data/*.json одним файлом
sitemap.php        /sitemap.xml
docs/              инструкции по заливке и дизайну v3
```

## Запуск на хостинге

1. Залить содержимое репозитория в корень сайта (nic.ru: `/home/pcm7937440/pcm2.ru/docs/`).
2. `cp inc/config.sample.php inc/config.php` и заполнить: `$SITE_URL`, `$SITE_EMAIL`, `$ADMIN_PASSWORD`,
   `$ADMIN_TOKEN`, SMTP-данные почтового ящика, при необходимости `$FEEDS`.
3. Права: `chmod 775 data/` (сюда пишут админка, импорт и журнал заявок).
4. Проверить: `/`, `/cars/`, `/news/`, `/admin/`, `/sitemap.xml`.

## Обслуживание

- **Каталог из фида**: `/import-cars.php?key=ПАРОЛЬ_АДМИНКИ` или cron раз в час:
  `0 * * * * /usr/bin/php /home/pcm7937440/pcm2.ru/docs/import-cars.php >/dev/null 2>&1`
- **Бэкап контента**: `/backup.php?key=ПАРОЛЬ_АДМИНКИ` — скачивает `data/*.json` архивом.
- **Открыть индексацию**: `robots.txt` (раскомментировать блок) и `$NOINDEX = false` в `inc/config.php`.
- **HTTPS**: после установки сертификата раскомментировать редирект в `.htaccess`.
- **Заявки**: уходят на `$SITE_EMAIL` (сейчас ящик на домене pc-moscow.ru — при отключении почты
  указать новый адрес), копия всегда пишется в `data/leads.log`.

## Дизайн v3

Подробности — `docs/ДИЗАЙН-V3.txt`. Кратко:

- Все классы прежние, тема меняется одним файлом `assets/style3.css`.
- Слайд карусели с автомобилем без фона: в `data/slides.json` добавить `"cutout": true`
  и PNG с прозрачным фоном от 1600 px.
- Шрифт Exo 2 подключён с Google Fonts; для локального варианта — блок `@font-face` в начале `style3.css`.
- Машины без фото показывают силуэт модели (`images/model-*-wide.png`), другие марки — название бренда.

## Известные хвосты

- Ссылки в `tractors/index.html` ведут на pc-moscow.ru.
- Изображения весят 1–2 МБ — стоит конвертировать в WebP.
- Файл `images/911-carrera.png` — 640 px, для главного экрана нужен hi-res.
