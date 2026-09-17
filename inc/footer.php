<?php if (empty($PAGE['noform'])): // страницы со своей формой (контакты, сервис, финансы, карточка авто) ставят noform ?>
<section class="band band--lead" id="lead" aria-labelledby="h-lead">
  <div class="lb">
    <div class="lb__t">
      <div class="eyebrow">Обратная связь</div>
      <h2 id="h-lead">Остались <em class="thin">вопросы?</em></h2>
      <p>Оставьте заявку — менеджер PCM свяжется с вами в ближайшее время и ответит на вопросы о наличии, сервисе и условиях покупки.</p>
      <a class="lb__tel" href="tel:<?= e($SITE_PHONE_HREF) ?>"><?= e($SITE_PHONE) ?></a>
      <div class="lb__hours">Ежедневно 9:00 – 21:00</div>
    </div>
    <div class="fcard lb__f">
      <?= pcm_form('contact', ['message' => true, 'submit' => 'Отправить заявку']) ?>
    </div>
  </div>
</section>
<?php endif; ?>
</main>
<footer class="ftr">
  <div class="ftr__in">
    <div class="ftr__top">
      <div>
        <div class="ftr__mark">
          <img class="logo__img" src="/images/logo-pcm-light.png" width="536" height="344" alt="PCM">
          <span class="logo__sep" aria-hidden="true"></span>
          <span>
            <span class="logo__t1">Ваш эксперт по Porsche</span>
            <span class="logo__t2">с 2007 года</span>
          </span>
        </div>
        <p class="ftr__about">Продажа Porsche с пробегом, сервисное обслуживание, оригинальные запчасти, кредит, лизинг и trade-in в Москве.</p>
      </div>
      <div class="ftr__contact">
        <a class="ftr__tel" href="tel:<?= e($SITE_PHONE_HREF) ?>"><?= e($SITE_PHONE) ?></a>
        <div class="ftr__addr"><?= e($SITE_ADDR) ?><br>Ежедневно 9:00 – 21:00</div>
      </div>
    </div>
    <div class="ftr__cols">
      <div>
        <h2>Автомобили</h2>
        <a href="/cars/">В наличии</a>
        <a href="/models/">Модельный ряд</a>
        <a href="/finance/">Финансовые услуги</a>
      </div>
      <div>
        <h2>Сервис</h2>
        <a href="/service/">Обслуживание</a>
        <a href="/service/parts/">Запасные части</a>
        <a href="/service/body/">Кузовной ремонт</a>
        <a href="/service/offers/">Спецпредложения</a>
      </div>
      <div>
        <h2>Компания</h2>
        <a href="/news/">Новости</a>
        <a href="/about/">О компании</a>
        <a href="/contacts/">Контакты</a>
        <a href="https://hh.ru/employer/12649940?tab=VACANCIES" target="_blank" rel="noopener">Присоединиться к команде</a>
      </div>
      <div>
        <h2>Правовая информация</h2>
        <a href="/legal/pdn/">Политика обработки персональных данных</a>
        <a href="/legal/cookie/">Политика использования файлов cookie</a>
        <a href="/legal/consent/">Согласие на обработку персональных данных</a>
      </div>
    </div>
    <div class="ftr__bot">
      <div>© <?= date('Y') ?> PCM. Все права защищены.</div>
      <div>Сайт носит информационный характер и не является публичной офертой. Стоимость автомобилей уточняйте у менеджеров отдела продаж.</div>
    </div>
  </div>
</footer>
<?php pcm_callback_widget(); ?>
<script src="/assets/site.js?v=<?= @filemtime(__DIR__ . '/../assets/site.js') ?: 3 ?>" defer></script>
</body>
</html>
