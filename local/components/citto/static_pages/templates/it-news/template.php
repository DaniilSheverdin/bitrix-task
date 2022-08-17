<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); ?>
<?php

$this->addExternalCss($arResult['TEMPLATE'] . "/assets/css/bootstrap.min.css");
$this->addExternalCss($arResult['TEMPLATE'] . "/assets/css/avz-reboot.css");
$this->addExternalCss($arResult['TEMPLATE'] . "/assets/css/main.css");
$this->addExternalJS($arResult['TEMPLATE'] .  "/assets/js/actions.js");
$this->addExternalJS($arResult['TEMPLATE'] .  "/assets/js/bootstrap.bundle.min.js");

$APPLICATION->SetTitle("IT TULAREGION");
?>
<main class="w-100 mw-em-80 m-auto bg-white rounded">
    <div class="px-4 px-sm-5 py-5">
        <h2 class="h1">Образ в глазах граждан</h2>
        <p>Имидж министерства — это совокупность представлений и восприятий граждан. Информацию они получают из разных источников в зависимости от возрастной группы.
            Чтобы создать позитивный образ, который вызывает доверие и лояльность, необходимо сформировать положительное инфополе в новостной ленте. Хороших новостей
            должно быть больше, чем плохих, а кто их размещает — не так важно. Источник забывается, а информация остаётся. </p>
        <p>Инфоповодом для новости может быть любая динамика: итоговые решения совещаний, открытия новых офисов, прогресс в достижении каких-либо показателей. Следует
            помнить, что большая цифра сама по себе не так важна, как факт улучшения жизни людей — именно это может послужить темой для создания материала.</p>
        <p>Чтобы получить отклик аудитории, необходимо говорить на её языке, то есть размещать новости в интересном для неё формате и с помощью подходящих для этого
            каналов. Что, где и зачем можно размещать расскажем ниже.</p>
        <hr>
        <h2 class="h1">Где искать свою аудиторию</h2>
        <p>Для понимания, где взаимодействовать с необходимой аудиторией, отталкивайтесь от актуальной статистики. Ниже приведены результаты опроса ВЦИОМ о пользовании
            интернетом и телевидением среди россиян. Данные актуальны на март 2021 года.</p>
        <div class="font-size-larger">
            <img src="<?=$arResult['TEMPLATE'] ?>/assets/img/graph.png" width="800" height="auto" class="img-fluid mb-3" alt="Диаграмма возрастного среза медиа-активности"/>
            <p class="ps-3"><font color="#C458F4" class="position-absolute start-0">■</font> Телезрители, интернетом почти не пользуются</p>
            <p class="ps-3"><font color="#0070C0" class="position-absolute start-0">■</font> Телезрители и пользователи интернета</p>
            <p class="ps-3"><font color="#22AAEE" class="position-absolute start-0">■</font> Пользователи интернета, телевизор почти не смотрят</p>
            <p class="ps-3"><font color="#ADB9CA" class="position-absolute start-0">■</font> Телевидением и интернетом почти не пользуются</p>
        </div>
    </div>
    <section class="alert-primary pb-3 rounded-bottom">
        <div class="px-4 pt-5 px-sm-5">
            <h2 class="h1">Форматы медиа-активностей</h2>
            <h3>Фильтровать по каналам</h3>
        </div>
        <div class="sticky-top mb-3 pt-1 px-4 px-sm-5 alert-primary">
            <div id="fltr" class="">
                <button href="#" class="btn me-1 mb-1 p-1 px-3 bg-white rounded-pill" id="smi">СМИ</button>
                <button href="#" class="btn me-1 mb-1 p-1 px-3 bg-white rounded-pill" id="oiv">Сайты ОИВ</button>
                <button href="#" class="btn me-1 mb-1 p-1 px-3 bg-white rounded-pill" id="soc">Соцсети</button>
            </div>
        </div>
        <div class="px-4 px-sm-5">
            <div class="channel smi">
                <h3 class="font-size-xx-large">Репортаж</h3>
                <iframe class="mb-3 rounded" src="https://vk.com/video_ext.php?oid=-68399874&id=456239072&hash=a7d4d06ecdb13eaf&hd=2" width="100%" height="480"
                        allow="autoplay; encrypted-media; fullscreen; picture-in-picture;" frameborder="0" allowfullscreen></iframe>
                <h4 class="h3 text-primary">Что можно показать</h4>
                <p>Освещаем самые масштабные и важные события, к которым причастно Министерство. Работа на площадке съёмок с представителями СМИ и пресс-службы
                    правительства.</p>
                <h4 class="h3 text-primary">Для чего</h4>
                <p>Повышаем узнаваемость брендов Министерства (МФЦ, ЦИТ, ЦУР) в широких массах (у тех, кто смотрит телевизор). Знакомим население региона с важностью
                    работы — подключение интернета в населённых пунктах, вывод новых услуг, образование и так далее.</p>
                <ol>
                    <li><p>Новостной сюжет на 2,5−3 минуты на региональном ТВ.</p></li>
                    <li><p>«Минутки» — короткая новость, которое сопровождается текстом и подходящим видеорядом. Например: МФЦ оказана миллионная услуга — телевидение
                            берёт наш пресс-релиз, начитывает и подставляет архивные кадры.</p></li>
                </ol>
                <h4 class="h3 text-primary">Где размещать</h4>
                <p><span class="d-inline-block mb-1 px-2 text-white bg-secondary rounded-pill">СМИ+ТВ</span></p>
            </div>
            <hr>
            <div class="channel smi oiv">
                <h3 class="font-size-xx-large">Статья</h3>
                <p>
                    <a href="https://it.tularegion.ru/press_center/news/besplatnye-obrazovatelnye-programmy-i-podgotovka-it-kadrov-kak-budet-razvivatsya-tsifrovaya-transformatsiya-tulskoy-oblasti/"
                       class="btn btn-primary rounded-pill arr-after">📰 Пример</a></p>
                <h4 class="h3 text-primary">Что можно показать</h4>
                <p>В таком материале рассказываем о важности наших сервисов, призываем их использовать, рассказываем о положительном опыте и так далее. Как правило, такой
                    материал пишется СМИ по заказу.</p>
                <h4 class="h3 text-primary">Для чего</h4>
                <p>Рассказываем о работе Министерства, МФЦ, ЦИТ и ЦУР в печатных изданиях и в Интернете. Затрагиваем читающую аудиторию.</p>
                <h4 class="h3 text-primary">Где размещать</h4>
                <p><span class="d-inline-block mb-1 px-2 text-white bg-secondary rounded-pill">СМИ</span> <span
                        class="d-inline-block mb-1 px-2 text-white bg-secondary rounded-pill">Сайты ОИВ</span></p>
            </div>
            <hr>
            <div class="channel soc">
                <h3 class="font-size-xx-large">Видео</h3>
                <div class="row flex-row-reverse">
                    <div class="col-lg-5 col-md-6 px-lg-5">
                        <a href="https://www.instagram.com/tv/CVz7IytgeSd" class="d-inline-block w-100 mb-3 border rounded overflow-hidden"><img src="<?=$arResult['TEMPLATE'] ?>/assets/img/video.webp"
                                                                                                                                                 width="100%"
                                                                                                                                                 height="auto"
                                                                                                                                                 alt="Пример видео в Инстаграм"></a>
                    </div>
                    <div class="col-lg-7 col-md-6">
                        <h4 class="h3 text-primary">Что можно показать</h4>
                        <p>Видео с места масштабных, важных и знаковых событий, к которым причастно Министерство. + Периодические статистические данные.</p>
                        <h4 class="h3 text-primary">Для чего</h4>
                        <p>Рассказываем о работе Министерства, МФЦ, ЦИТ и ЦУР в Интернете той аудитории, которая не читает большой текст, а потребляет контент визуально.
                            Для этого видео должно быть ёмким, информативным и интересным. Затрагиваем молодую аудитория — должна быть соответствующая подача.</p>
                        <ol>
                            <li><p>Горизонтальное видео с перебивками по 3−5 секунд (репортаж своими руками) — в ВК.</p></li>
                            <li><p>Вертикальное видео для соцсетей как Сториз / Reels / Tik-tok. Красивые кадры с плавными переходами + текст. Как в пабликах Тульская
                                    область.</p></li>
                        </ol>
                        <h4 class="h3 text-primary">Где размещать</h4>
                        <p><span class="d-inline-block mb-1 px-2 text-white bg-secondary rounded-pill">Соцсети</span></p>
                    </div>
                </div>
            </div>
            <hr>
            <div class="channel smi oiv soc" id="press">
                <h3 class="font-size-xx-large">Пресс-релиз</h3>
                <p><a href="https://it.tularegion.ru/press_center/news/v-tulskoy-oblasti-proydet-nastroyka-gosudarstvennykh-servisov/"
                      class="btn btn-primary rounded-pill arr-after">📰 Пример</a></p>
                <h4 class="h3 text-primary">Что можно показать</h4>
                <p>Рассказываем о масштабном или важном мероприятии. Информация с цифрами, фактами и комментариями.</p>
                <h4 class="h3 text-primary">Для чего</h4>
                <p>Материал для СМИ, которые распространят его в массы.</p>
                <h4 class="h3 text-primary">Где размещать</h4>
                <p><span class="d-inline-block mb-1 px-2 text-white bg-secondary rounded-pill">СМИ</span> <span
                        class="d-inline-block mb-1 px-2 text-white bg-secondary rounded-pill">Сайты правительства и ОИВ</span> <span
                        class="d-inline-block mb-1 px-2 text-white bg-secondary rounded-pill">Соцсети</span></p>
            </div>
            <hr>
            <div class="channel soc">
                <h3 class="font-size-xx-large">Тематический пост</h3>
                <div class="row">
                    <div class="col-lg-5 col-md-6">
                        <div class="mb-3 border rounded overflow-hidden">
                            <div id="vk_post_-68399874_3276"></div>
                            <style>#vk_post_-68399874_3276 {
                                    margin: -1px;
                                    width: 101% !important;
                                }</style>
                            <script type="text/javascript" src="https://vk.com/js/api/openapi.js?169"></script>
                            <script type="text/javascript">(function () {
                                    VK.Widgets.Post("vk_post_-68399874_3276", -68399874, 3276, 'zDqKE7brlCEQWalpNUdf7pZ7OA');
                                }());</script>
                        </div>
                    </div>
                    <div class="col-lg-7 col-md-6 px-md-5">
                        <p>Как правило, краткая выжимка <a href="#press">пресс-релиза</a>.</p>
                        <h4 class="h3 text-primary">Что можно показать</h4>
                        <p>Рассказываем о масштабном или важном мероприятии. Информация с цифрами, фактами и комментариями.</p>
                        <h4 class="h3 text-primary">Для чего</h4>
                        <p>Дублируем информацию, которая ушла в СМИ. Подача инфоповода проходит легче (не надо читать СМИ, а достаточно листать ленту соцсетей).</p>
                        <h4 class="h3 text-primary">Где размещать</h4>
                        <p><span class="d-inline-block mb-1 px-2 text-white bg-secondary rounded-pill">Соцсети</span></p>
                    </div>
                </div>
            </div>
            <hr>
            <div class="channel soc oiv" id="zametka">
                <h3 class="font-size-xx-large">Заметка</h3>
                <div class="row">
                    <div class="col-lg-5 col-md-6">
                        <div class="mb-3 border rounded overflow-hidden">
                            <div id="vk_post_-68399874_3283"></div>
                            <style>#vk_post_-68399874_3283 {
                                    margin: -1px;
                                    width: 101% !important;
                                }</style>
                            <script type="text/javascript" src="https://vk.com/js/api/openapi.js?169"></script>
                            <script type="text/javascript">(function () {
                                    VK.Widgets.Post("vk_post_-68399874_3283", -68399874, 3283, 'BmXzGTP84Hw18dQoA01J23W_xg');
                                }());</script>
                        </div>
                    </div>
                    <div class="col-lg-7 col-md-6 px-md-5">
                        <p>Краткая информация о чем либо, которая не пошла в <a href="#press">пресс-релиз</a>. Количество принятых обращений, звонков и так далее.</p>
                        <h4 class="h3 text-primary">Что можно показать</h4>
                        <p>Делимся своими достижениями через что-то интересное. Вызываем эмоции.</p>
                        <h4 class="h3 text-primary">Для чего</h4>
                        <p>Затрагиваем аудиторию, которая следит за цифрами и статистикой.</p>
                        <h4 class="h3 text-primary">Где размещать</h4>
                        <p><span class="d-inline-block mb-1 px-2 text-white bg-secondary rounded-pill">Соцсети</span> <span
                                class="d-inline-block mb-1 px-2 text-white bg-secondary rounded-pill">Сайты правительства и ОИВ</span></p>
                    </div>
                </div>
            </div>
            <hr>
            <div class="channel oiv soc">
                <h3 class="font-size-xx-large">Инфографика</h3>
                <div class="row flex-row-reverse">
                    <div class="col-lg-5 col-md-6 px-lg-5">
                        <a href="https://www.instagram.com/p/CQI5wUkqzFy/" class="d-block mb-3 border rounded overflow-hidden"><img src="<?=$arResult['TEMPLATE'] ?>/assets/img/infographic.webp"
                                                                                                                                    width="100%" height="auto"
                                                                                                                                    alt="Пример Сторис"></a>
                    </div>
                    <div class="col-lg-7 col-md-6">
                        <p>Краткая информация, как и в <a href="#zametka">Заметке</a>, но в виде картинки.</p>
                        <h4 class="h3 text-primary">Что можно показать</h4>
                        <p>Делимся своими достижениями через что-то интересное. Вызываем эмоции.</p>
                        <h4 class="h3 text-primary">Для чего</h4>
                        <p>Затрагиваем аудиторию, которая следит за цифрами и статистикой.</p>
                        <h4 class="h3 text-primary">Где размещать</h4>
                        <p><span class="d-inline-block mb-1 px-2 text-white bg-secondary rounded-pill">Сайты ОИВ</span> <span
                                class="d-inline-block mb-1 px-2 text-white bg-secondary rounded-pill">Соцсети</span></p>
                    </div>
                </div>
            </div>
            <hr>
            <div class="channel soc">
                <h3 class="font-size-xx-large">Карточки (инструкции)</h3>
                <div class="row flex-row-reverse">
                    <div class="col-lg-5 col-md-6 px-lg-5">
                        <a href="https://www.instagram.com/p/CVSic62g7db" class="d-block mb-3 border rounded overflow-hidden"><img src="<?=$arResult['TEMPLATE'] ?>/assets/img/cards.webp" width="100%"
                                                                                                                                   height="auto"
                                                                                                                                   alt="Пример видео в Инстаграм"></a>
                    </div>
                    <div class="col-lg-7 col-md-6">
                        <p>Полезная информация для граждан в картинках.</p>
                        <h4 class="h3 text-primary">Что можно показать</h4>
                        <p>Рассказываем о том, как записаться к врачу, как обратиться в ЕДС ГЖИ, и так далее.</p>
                        <h4 class="h3 text-primary">Для чего</h4>
                        <p>Основная задача — рассказать что-то важное и научить что-то делать. Объяснить, что это (что-либо, например, подтверждение аккаунта в ЕСИА)
                            не сложно.</p>
                        <h4 class="h3 text-primary">Где размещать</h4>
                        <p><span class="d-inline-block mb-1 px-2 text-white bg-secondary rounded-pill">Соцсети</span></p>
                    </div>
                </div>
            </div>
            <hr>
            <div class="channel soc">
                <h3 class="font-size-xx-large">Сториз / Reels / Tik-tok</h3>
                <div class="row flex-row-reverse">
                    <div class="col-lg-5 col-md-6 px-lg-5">
                        <a href="https://www.instagram.com/stories/highlights/17850513793657191/" class="d-block mb-3 border rounded overflow-hidden"><img
                                src="<?=$arResult['TEMPLATE'] ?>/assets/img/stories.webp" width="100%" height="auto" alt="Пример Сторис"></a>
                    </div>
                    <div class="col-lg-7 col-md-6">
                        <ol>
                            <li><p>Горизонтальное видео с перебивками по 3−5 секунд (репортаж своими руками) — в ВК.</p></li>
                            <li><p>Вертикальное видео для соцсетей как Сториз / Reels / Tik-tok. Красивые кадры с плавными переходами + текст. Как в пабликах Тульская
                                    область.</p></li>
                        </ol>
                        <h4 class="h3 text-primary">Что можно показать</h4>
                        <p>Видео с места масштабных, важные и знаковых событий, к которым причастно Министерство. + Периодические статистические данные.</p>
                        <h4 class="h3 text-primary">Для чего</h4>
                        <p>Достаточно экстраординарная подача новости. Есть вероятность получить широкое распространение. Затрагиваем молодую аудитория — должна быть
                            соответствующая подача.</p>
                        <h4 class="h3 text-primary">Где размещать</h4>
                        <p><span class="d-inline-block mb-1 px-2 text-white bg-secondary rounded-pill">Соцсети</span></p>
                    </div>
                </div>
            </div>
            <hr>
            <div class="channel soc">
                <h3 class="font-size-xx-large">Графика</h3>
                <div class="row">
                    <div class="col-lg-5 col-md-6">
                        <a href="https://www.instagram.com/p/CW3kL8GgkYX" class="d-block mb-3 border rounded overflow-hidden"><img src="<?=$arResult['TEMPLATE'] ?>/assets/img/graphic.webp" width="100%"
                                                                                                                                   height="auto"
                                                                                                                                   alt="Пример видео в Инстаграм"></a>
                    </div>
                    <div class="col-lg-7 col-md-6 px-lg-5">
                        <p>Картинка с краткой информацией.</p>
                        <h4 class="h3 text-primary">Что можно показать</h4>
                        <p>Рассказываем о статистических данных и делимся полезной информацией.</p>
                        <h4 class="h3 text-primary">Для чего</h4>
                        <p>Затрагиваем аудиторию, которая следит за цифрами и статистикой.</p>
                        <h4 class="h3 text-primary">Где размещать</h4>
                        <p><span class="d-inline-block mb-1 px-2 text-white bg-secondary rounded-pill">Соцсети</span></p>
                    </div>
                </div>
            </div>
            <hr>
            <div class="channel oiv soc smi">
                <h3 class="font-size-xx-large">Репортажное фото</h3>
                <div class="row">
                    <div class="col-lg-5 col-md-6">
                        <a href="https://www.instagram.com/p/CWDmoPPADUT" class="d-block mb-3 border rounded overflow-hidden"><img src="<?=$arResult['TEMPLATE'] ?>/assets/img/photo.webp" width="100%"
                                                                                                                                   height="auto"
                                                                                                                                   alt="Пример видео в Инстаграм"></a>
                    </div>
                    <div class="col-lg-7 col-md-6 px-lg-5">
                        <p>Фото для дополнения пресс-релиза, если представители СМИ не смогли прибыть. Прикрепляем к постам, <a href="#zametka">заметкам</a> и так далее.
                        </p>
                        <h4 class="h3 text-primary">Что можно показать</h4>
                        <p>Кадр с места события.</p>
                        <h4 class="h3 text-primary">Для чего</h4>
                        <p>Цепляем картинкой внимание человека. Основная задача — заставить прочитать текст. Должен быть необычный ракурс или красивое фото.</p>
                        <h4 class="h3 text-primary">Где размещать</h4>
                        <p><span class="d-inline-block mb-1 px-2 text-white bg-secondary rounded-pill">Сайты ОИВ</span> <span
                                class="d-inline-block mb-1 px-2 text-white bg-secondary rounded-pill">Соцсети</span> <span
                                class="d-inline-block mb-1 px-2 text-white bg-secondary rounded-pill" channel="smi">СМИ</span></p>
                    </div>
                </div>
            </div>
            <hr>
            <div class="channel oiv soc">
                <h3 class="font-size-xx-large">Перепечатка</h3>
                <div class="row">
                    <div class="col-lg-5 col-md-6">
                        <div class="mb-3 border rounded overflow-hidden">
                            <div id="vk_post_-68399874_3270"></div>
                            <style>#vk_post_-68399874_3270 {
                                    margin: -1px;
                                    width: 101% !important;
                                }</style>
                            <script type="text/javascript" src="https://vk.com/js/api/openapi.js?169"></script>
                            <script type="text/javascript">(function () {
                                    VK.Widgets.Post("vk_post_-68399874_3270", -68399874, 3270, 'UOLgatdPo6TQdB6nJ3vj0GZi2w');
                                }());</script>
                        </div>
                    </div>
                    <div class="col-lg-7 col-md-6 px-md-5">
                        <p>Информация с федеральных источников и из поручений.</p>
                        <h4 class="h3 text-primary">Что можно показать</h4>
                        <p>Доносим важные новости через привязанность к Тульской области.</p>
                        <h4 class="h3 text-primary">Для чего</h4>
                        <p>Затрагиваем очень важные темы, которые пользуются интересом у граждан. Через простую подачу говорим о сложном (как получить маткапитал).</p>
                        <h4 class="h3 text-primary">Где размещать</h4>
                        <p><span class="d-inline-block mb-1 px-2 text-white bg-secondary rounded-pill">Сайты ОИВ</span> <span
                                class="d-inline-block mb-1 px-2 text-white bg-secondary rounded-pill">Соцсети</span></p>
                    </div>
                </div>
            </div>
            <hr>
            <div class="channel soc">
                <h3 class="font-size-xx-large">Мем / Юмористическая картинка</h3>
                <div class="row flex-row-reverse">
                    <div class="col-lg-5 col-md-6 px-lg-5">
                        <div class="mb-3 border rounded overflow-hidden"><img src="<?=$arResult['TEMPLATE'] ?>/assets/img/mem.webp" width="100%" height="auto" alt="Пример мема"></div>
                    </div>
                    <div class="col-lg-7 col-md-6">
                        <p>Фотожаба для привлечения внимания. Возможно выделить под это одну из соцсетей.</p>
                        <h4 class="h3 text-primary">Что можно показать</h4>
                        <p>Повышаем активность и вовлеченность. Вызываем реакцию.</p>
                        <h4 class="h3 text-primary">Для чего</h4>
                        <p>Попытаться донести новость через привлечение внимание или юмор. Картинка должна быть «на злобу дня».</p>
                        <h4 class="h3 text-primary">Где размещать</h4>
                        <p><span class="d-inline-block mb-1 px-2 text-white bg-secondary rounded-pill">Соцсети</span></p>
                    </div>
                </div>
            </div>
            <hr>
            <div class="channel oiv soc">
                <h3 class="font-size-xx-large">Стоковое фото</h3>
                <div class="row flex-row-reverse">
                    <div class="col-lg-5 col-md-6 px-lg-5">
                        <a href="https://www.instagram.com/p/CVxeftugNTF" class="d-block mb-3 border rounded overflow-hidden"><img src="<?=$arResult['TEMPLATE'] ?>/assets/img/stock.webp" width="100%"
                                                                                                                                   height="auto"
                                                                                                                                   alt="Пример видео в Инстаграм"></a>
                    </div>
                    <div class="col-lg-7 col-md-6">
                        <p>Тематическая картинка из интернета, обязательно должна сопровождаться элементами фирменного стиля или вписана в шаблон.</p>
                        <h4 class="h3 text-primary">Что можно показать</h4>
                        <p>Для сопровождения текстового материала.</p>
                        <h4 class="h3 text-primary">Для чего</h4>
                        <p>Показываем оперативность и сопровождаем текстовый материал.</p>
                        <h4 class="h3 text-primary">Где размещать</h4>
                        <p><span class="d-inline-block mb-1 px-2 text-white bg-secondary rounded-pill">Сайты ОИВ</span> <span
                                class="d-inline-block mb-1 px-2 text-white bg-secondary rounded-pill">Соцсети</span></p>
                    </div>
                </div>
            </div>
            <script>
                $(function () {
                    "use strict";
                    $("#fltr button").click(function () {
                        $(this).toggleClass("bg-primary text-white");
                        $(".channel").hide();

                        if ($("#smi").is(".bg-primary")) {
                            $(".smi").show();
                        } else {
                            $(".smi").not(".oiv").hide();
                            $(".smi").not(".soc").hide();
                        }

                        if ($("#oiv").is(".bg-primary")) {
                            $(".oiv").show();
                        } else {
                            $(".oiv").not(".smi").hide();
                            $(".oiv").not(".soc").hide();
                        }

                        if ($("#soc").is(".bg-primary")) {
                            $(".soc").show();
                        } else {
                            $(".soc").not(".smi").hide();
                            $(".soc").not(".oiv").hide();
                        }

                        if (!$("#smi").is(".bg-primary") && !$("#oiv").is(".bg-primary") && !$("#soc").is(".bg-primary")) {
                            $(".channel").show();
                        }
                    });
                });
            </script>
        </div>
    </section>
</main>