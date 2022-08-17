<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}
CJSCore::Init(["jquery"]);

if (empty($arResult['FILES'])) {
    echo '<div class="alert alert-danger">Файлы не найдены</div>';
    return;
}
?>
<script id="filesigner">
var filesigner_pos    = <?=json_encode($arParams['POS'])?>;
var filesigner_clearf = <?=json_encode($arParams['CLEARF']?:[])?>;
var filesigner_files  = <?=json_encode(array_values(array_map(function ($file) {
                            return [
                                'id'        => $file['ID'],
                                'src'       => $file['SRC'],
                                'name'      => $file['ORIGINAL_NAME'],
                                'extension' => $file['EXTENSION'],
                                'signed'    => $file['SIGNED'],
                                'content'   => "#filesigner_file_".$file['ID']
                            ];
                        }, $arResult['FILES'])));?>;
var filesigner_double_sign = <?=json_encode($arParams['DOUBLE_SIGN'])?>;
var filesigner_check_sign  = <?=$arParams['CHECK_SIGN'] == 'Y' ? 'true' : 'false'?>;
</script>
<div class="docsign-files">
    <ol>
       <?foreach ($arResult['FILES'] as $file) : ?>
        <li class="<?=($file['SIGNED']?'docsign-file--signed':'');?>" data-id="<?=htmlentities($file['ID']);?>">
            <input type="hidden" name="filesigner_file_<?=$file['ID']?>" id="filesigner_file_<?=$file['ID']?>" value="<?
                echo ($file['EXTENSION'] == "p7s" ? file_get_contents($file['PATH']) : base64_encode(file_get_contents($file['PATH'])))
                ?>">
            <div>
                <a href="<?=$file['SRC']?>" target="_blank" download="<?=htmlentities($file['ORIGINAL_NAME'])?>"><?=htmlentities($file['ORIGINAL_NAME'])?></a>
                <?if ($file['SIGNS']) : ?>
                    <div class="docsign-file__signed-other">
                        <div>Подписано:</div>
                    <?
                    foreach ($file['SIGNS'] as $signer) :
                        foreach ($signer['SIGNS'] as $sign) : ?>
                            <div><a href="<?=$sign['SRC']?>" target="_blank" bx-tooltip-user-id="<?=$sign_user?>" bx-tooltip-classname="intrantet-user-selector-tooltip">&mdash; <?
                                echo $sign['TIMESTAMP_X']->format("d.m.Y H:i:s")." ".$signer['SIGNER_NAME'];
                            ?></a></div>
                        <?endforeach;?>
                    <?endforeach;?>
                    </div>
                <?endif;?>
                <div class="docsign-file__signed-self">&mdash; Подписано Вами</div>
            </div>
        </li>
       <?endforeach;?> 
    </ol>
</div>

<div class="docsign-pp" id="docsign-pp">
    <div class="docsign-pp__form">
        <div class="docsign-pp__form__title">
            <div class="docsign-pp__form__st1">Соединение с ключом электронной подписи</div>
            <div class="docsign-pp__form__st2">Подписать файлы электронной подписью</div>
            <div class="docsign-pp__form__st3">Подписать файлы электронной подписью</div>
            <div class="docsign-pp__form__st4">Файлы подписаны</div>
            <div class="docsign-pp__form__err1">Ошибка —  у вас не установлен плагин</div>
            <div class="docsign-pp__form__err2">Ошибка —  не найден сертификат ЭП</div>
            <div class="docsign-pp__form__err3">Ошибка — нет соединения с сервером</div>
            <div class="docsign-pp__form__err4">Обновите браузер</div>
        </div>
        <div class="docsign-pp__form__status">
            <div class="docsign-pp__form__st1 docsign-pp__form__st3">
                <svg width="114" height="114" viewBox="0 0 114 114" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M105.5 57C105.5 83.7858 83.7858 105.5 57 105.5C30.2142 105.5 8.5 83.7858 8.5 57C8.5 30.2142 30.2142 8.5 57 8.5C83.7858 8.5 105.5 30.2142 105.5 57Z" stroke="#BCDDFF" stroke-width="3"/>
                    <path d="M57 98.5C79.9198 98.5 98.5 79.9198 98.5 57C98.5 34.0802 79.9198 15.5 57 15.5C34.0802 15.5 15.5 34.0802 15.5 57C15.5 79.9198 34.0802 98.5 57 98.5Z" fill="white" stroke="#208CFF" stroke-width="3"/>
                    <mask id="maskfile12" mask-type="alpha" maskUnits="userSpaceOnUse" x="14" y="14" width="86" height="86">
                    <path d="M57 98.5C79.9198 98.5 98.5 79.9198 98.5 57C98.5 34.0802 79.9198 15.5 57 15.5C34.0802 15.5 15.5 34.0802 15.5 57C15.5 79.9198 34.0802 98.5 57 98.5Z" fill="white" stroke="white" stroke-width="3"/>
                    </mask>
                    <g mask="url(#maskfile12)">
                    <path d="M38 43.5L74 43.5C74.2761 43.5 74.5 43.7239 74.5 44V70C74.5 70.2761 74.2761 70.5 74 70.5H38C37.7239 70.5 37.5 70.2761 37.5 70L37.5 44C37.5 43.7239 37.7239 43.5 38 43.5Z" fill="white" stroke="#208CFF" stroke-width="3"/>
                    <path d="M-38 35.5L47 35.5C47.1777 35.5 47.3148 35.5657 47.3986 35.6428C47.4804 35.718 47.5 35.7887 47.5 35.84L47.5 78.16C47.5 78.2113 47.4804 78.282 47.3986 78.3572C47.3148 78.4343 47.1777 78.5 47 78.5L-38 78.5C-38.1777 78.5 -38.3148 78.4343 -38.3986 78.3572C-38.4804 78.282 -38.5 78.2113 -38.5 78.16L-38.5 35.84C-38.5 35.7887 -38.4804 35.718 -38.3986 35.6428C-38.3148 35.5657 -38.1777 35.5 -38 35.5Z" fill="white" stroke="#208CFF" stroke-width="3"/>
                    <rect x="13" y="67" width="33" height="10" fill="#BCDDFF"/>
                    <rect x="49" y="66" width="24" height="3" fill="#BCDDFF"/>
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M65 50V55H60V50H65Z" fill="#BCDDFF"/>
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M65 58V63H60V58H65Z" fill="#BCDDFF"/>
                    </g>
                    <path opacity="0.5" d="M112.5 57C112.5 87.6518 87.6518 112.5 57 112.5C26.3482 112.5 1.5 87.6518 1.5 57C1.5 26.3482 26.3482 1.5 57 1.5C87.6518 1.5 112.5 26.3482 112.5 57Z" stroke="#BCDDFF" stroke-width="3"/>
                    <path d="M112.5 93.5C112.5 103.993 103.993 112.5 93.5 112.5C83.0066 112.5 74.5 103.993 74.5 93.5C74.5 83.0066 83.0066 74.5 93.5 74.5C103.993 74.5 112.5 83.0066 112.5 93.5Z" fill="white" stroke="#FF7712" stroke-width="3"/>
                    <path d="M92.5352 84.3613V95.3613H100.535" stroke="#208CFF" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <div class="docsign-pp__form__st2">
                <svg width="114" height="114" viewBox="0 0 114 114" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M57 98.5C79.9198 98.5 98.5 79.9198 98.5 57C98.5 34.0802 79.9198 15.5 57 15.5C34.0802 15.5 15.5 34.0802 15.5 57C15.5 79.9198 34.0802 98.5 57 98.5Z" fill="white" stroke="#208CFF" stroke-width="3"/>
                    <mask id="maskfile10" mask-type="alpha" maskUnits="userSpaceOnUse" x="14" y="14" width="86" height="86">
                    <path d="M57 98.5C79.9198 98.5 98.5 79.9198 98.5 57C98.5 34.0802 79.9198 15.5 57 15.5C34.0802 15.5 15.5 34.0802 15.5 57C15.5 79.9198 34.0802 98.5 57 98.5Z" fill="white" stroke="white" stroke-width="3"/>
                    </mask>
                    <g mask="url(#maskfile10)">
                    <path d="M38 43.5L74 43.5C74.2761 43.5 74.5 43.7239 74.5 44V70C74.5 70.2761 74.2761 70.5 74 70.5H38C37.7239 70.5 37.5 70.2761 37.5 70L37.5 44C37.5 43.7239 37.7239 43.5 38 43.5Z" fill="white" stroke="#208CFF" stroke-width="3"/>
                    <path d="M-38 35.5L47 35.5C47.1777 35.5 47.3148 35.5657 47.3986 35.6428C47.4804 35.718 47.5 35.7887 47.5 35.84L47.5 78.16C47.5 78.2113 47.4804 78.282 47.3986 78.3572C47.3148 78.4343 47.1777 78.5 47 78.5L-38 78.5C-38.1777 78.5 -38.3148 78.4343 -38.3986 78.3572C-38.4804 78.282 -38.5 78.2113 -38.5 78.16L-38.5 35.84C-38.5 35.7887 -38.4804 35.718 -38.3986 35.6428C-38.3148 35.5657 -38.1777 35.5 -38 35.5Z" fill="white" stroke="#208CFF" stroke-width="3"/>
                    <rect x="13" y="67" width="33" height="10" fill="#BCDDFF"/>
                    <rect x="49" y="66" width="24" height="3" fill="#BCDDFF"/>
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M65 50V55H60V50H65Z" fill="#BCDDFF"/>
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M65 58V63H60V58H65Z" fill="#BCDDFF"/>
                    </g>
                    <path d="M105.5 57C105.5 83.7858 83.7858 105.5 57 105.5C30.2142 105.5 8.5 83.7858 8.5 57C8.5 30.2142 30.2142 8.5 57 8.5C83.7858 8.5 105.5 30.2142 105.5 57Z" stroke="#BCDDFF" stroke-width="3"/>
                    <path opacity="0.5" d="M112.5 57C112.5 87.6518 87.6518 112.5 57 112.5C26.3482 112.5 1.5 87.6518 1.5 57C1.5 26.3482 26.3482 1.5 57 1.5C87.6518 1.5 112.5 26.3482 112.5 57Z" stroke="#BCDDFF" stroke-width="3"/>
                </svg>
            </div>
            <div class="docsign-pp__form__st4">
                <svg width="114" height="114" viewBox="0 0 114 114" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M105.5 57C105.5 83.7858 83.7858 105.5 57 105.5C30.2142 105.5 8.5 83.7858 8.5 57C8.5 30.2142 30.2142 8.5 57 8.5C83.7858 8.5 105.5 30.2142 105.5 57Z" stroke="#BCDDFF" stroke-width="3"/>
                    <path d="M57 98.5C79.9198 98.5 98.5 79.9198 98.5 57C98.5 34.0802 79.9198 15.5 57 15.5C34.0802 15.5 15.5 34.0802 15.5 57C15.5 79.9198 34.0802 98.5 57 98.5Z" fill="white" stroke="#208CFF" stroke-width="3"/>
                    <mask id="maskfile11" mask-type="alpha" maskUnits="userSpaceOnUse" x="14" y="14" width="86" height="86">
                    <path d="M57 98.5C79.9198 98.5 98.5 79.9198 98.5 57C98.5 34.0802 79.9198 15.5 57 15.5C34.0802 15.5 15.5 34.0802 15.5 57C15.5 79.9198 34.0802 98.5 57 98.5Z" fill="white" stroke="white" stroke-width="3"/>
                    </mask>
                    <g mask="url(#maskfile11)">
                    <path d="M38 43.5L74 43.5C74.2761 43.5 74.5 43.7239 74.5 44V70C74.5 70.2761 74.2761 70.5 74 70.5H38C37.7239 70.5 37.5 70.2761 37.5 70L37.5 44C37.5 43.7239 37.7239 43.5 38 43.5Z" fill="white" stroke="#208CFF" stroke-width="3"/>
                    <path d="M-38 35.5L47 35.5C47.1777 35.5 47.3148 35.5657 47.3986 35.6428C47.4804 35.718 47.5 35.7887 47.5 35.84L47.5 78.16C47.5 78.2113 47.4804 78.282 47.3986 78.3572C47.3148 78.4343 47.1777 78.5 47 78.5L-38 78.5C-38.1777 78.5 -38.3148 78.4343 -38.3986 78.3572C-38.4804 78.282 -38.5 78.2113 -38.5 78.16L-38.5 35.84C-38.5 35.7887 -38.4804 35.718 -38.3986 35.6428C-38.3148 35.5657 -38.1777 35.5 -38 35.5Z" fill="white" stroke="#208CFF" stroke-width="3"/>
                    <rect x="13" y="67" width="33" height="10" fill="#BCDDFF"/>
                    <rect x="49" y="66" width="24" height="3" fill="#BCDDFF"/>
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M65 50V55H60V50H65Z" fill="#BCDDFF"/>
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M65 58V63H60V58H65Z" fill="#BCDDFF"/>
                    </g>
                    <path opacity="0.5" d="M112.5 57C112.5 87.6518 87.6518 112.5 57 112.5C26.3482 112.5 1.5 87.6518 1.5 57C1.5 26.3482 26.3482 1.5 57 1.5C87.6518 1.5 112.5 26.3482 112.5 57Z" stroke="#BCDDFF" stroke-width="3"/>
                    <path d="M112.5 93.5C112.5 103.993 103.993 112.5 93.5 112.5C83.0066 112.5 74.5 103.993 74.5 93.5C74.5 83.0066 83.0066 74.5 93.5 74.5C103.993 74.5 112.5 83.0066 112.5 93.5Z" fill="white" stroke="#FF7712" stroke-width="3"/>
                    <path d="M98.7031 90.0889L91.9292 98.5263L88.0234 94.5898" stroke="#208CFF" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <div class="docsign-pp__form__err1 docsign-pp__form__err2 docsign-pp__form__err3 docsign-pp__form__err4">
                <svg width="86" height="86" viewBox="0 0 86 86" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M0 43C0 66.7482 19.2518 86 43 86C66.7482 86 86 66.7482 86 43C86 19.2518 66.7482 0 43 0C19.2518 0 0 19.2518 0 43ZM83 43C83 65.0914 65.0914 83 43 83C20.9086 83 3 65.0914 3 43C3 20.9086 20.9086 3 43 3C65.0914 3 83 20.9086 83 43Z" fill="#208CFF"/>
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M7 43C7 62.8823 23.1177 79 43 79C62.8823 79 79 62.8823 79 43C79 23.1177 62.8823 7 43 7C23.1177 7 7 23.1177 7 43ZM76 43C76 61.2254 61.2254 76 43 76C24.7746 76 10 61.2254 10 43C10 24.7746 24.7746 10 43 10C61.2254 10 76 24.7746 76 43Z" fill="#BCDDFF"/>
                    <path d="M29.3966 58.7053C28.8108 59.2911 27.8611 59.2911 27.2753 58.7053C26.6895 58.1195 26.6895 57.1698 27.2753 56.584L55.5595 28.2997C56.1453 27.7139 57.0951 27.7139 57.6809 28.2997C58.2667 28.8855 58.2667 29.8352 57.6809 30.421L29.3966 58.7053Z" fill="#208CFF"/>
                    <path d="M29.3966 28.2996C28.8108 27.7138 27.8611 27.7138 27.2753 28.2996C26.6895 28.8854 26.6895 29.8351 27.2753 30.4209L55.5595 58.7052C56.1453 59.291 57.0951 59.291 57.6809 58.7052C58.2667 58.1194 58.2667 57.1697 57.6809 56.5839L29.3966 28.2996Z" fill="#208CFF"/>
                </svg>
            </div>
        </div>
        <div class="docsign-pp__form__descr">
            <div class="docsign-pp__form__st1">
                <div>Происходит соединение к ключу электронной подписи. Этот процесс может занять около минуты.</div>
                <div><center><img src="<?=$this->GetFolder()?>/img/preloader.gif" alt="Загрузка"></center></div>
            </div>
            <div class="docsign-pp__form__st2">Подключите к компьютеру носитель ключа электронной подписи и выберите сертификат</div>
            <div class="docsign-pp__form__st3">
                <div>Происходит подписание файла</div>
                <small class="docsign-pp__signfile"></small>
                <div><center><img src="<?=$this->GetFolder()?>/img/preloader.gif" alt="Загрузка"></center></div>
            </div>
            <div class="docsign-pp__form__err1 docsign-pp__form__err2 docsign-pp__form__err3">Ознакомьтесь с подсказкой и попробуйте подключение снова </div>
            <div class="docsign-pp__form__err4">Необходимо обновить браузер для использования функций подписи</div>
            <small class="docsign-pp__form__errmess docsign-pp__form__err1 docsign-pp__form__err2 docsign-pp__form__err3 docsign-pp__form__err4"></small>
        </div>
        <div class="docsign-pp__form__certs docsign-pp__form__st2">
            <div class="docsign-cryptoplugin">
                <div class="docsign-cryptoplugin__certs">
                    <div>
                        <span class="docsign-cryptoplugin__certs__label">Сертификат ЭП</span>
                        <div class="docsign-cryptoplugin__certs__selected"></div>
                        <select class="docsign-cryptoplugin__certs__select" onchange="var _this = $(this); _this.siblings('.docsign-cryptoplugin__certs__selected').text(_this.find('option:selected').text());"></select>
                    </div>
                </div>
            </div>
        </div>
        <div class="docsign-pp__form__controls">
            <div class="docsign-pp__form__st2">
                <button type="button" class="docsign-pp__form__bsign">Подписать</button>
            </div>
            <div class="docsign-pp__form__st4">
                <button type="button" onclick="filesignerSigned();">Закрыть</button>
            </div>
            <div class="docsign-pp__form__err1 docsign-pp__form__err2 docsign-pp__form__err3">
                <button type="button" class="docsign-pp__form__btnw" onclick="filesignerInit()">Попробовать снова</button>
            </div>
        </div>
    </div>
    <div class="docsign-pp__info">
        <div class="docsign-pp__info-title">
            <div class="docsign-pp__info__err1">Для входа с ЭП необходимо:</div>
            <div class="docsign-pp__info__err2">Для входа с ЭП необходимо:</div>
            <div class="docsign-pp__info__err3">Что произошло:</div>
        </div>
        <div class="docsign-pp__info-descr">
            <div class="docsign-pp__info__err1">
                <ul>
                    <li><span><a href="https://www.cryptopro.ru/products/cades/plugin" target="_blank">Установить плагин</a> для работы с ЭП, включите его и разрешите доступ плагину.</span></li>
                    <li><span>Присоединить к компьютеру сертификат ЭП: USB-ключ, УЭК или смарт-карту.</span></li>
                    <li><span>Убедитесь, что у вас подключен только 1 носитель с корректным сертификатом.</span></li>
                    <li><span>Перезагрузите страницу</span></li>
                </ul>
            </div>
            <div class="docsign-pp__info__err2">
                <ul>
                    <li><span>Проверьте подключение носителя с сертифкатом ЭП к вашему компьютеру.</span></li>
                    <li><span>В случае если сертификат не найден - обратитесь в Удостоверяющий центр, где была получена ЭП.</span></li>
                    <li><span>Перезагрузите страницу</span></li>
                </ul>
            </div>
            <div class="docsign-pp__info__err3">
                В данный момент подключение к ключу электронной подписи невозможно, либо происходит очень медленно. Пожалуйста, попробуйте подписать документ позднее.
            </div>
        </div>
    </div>
    <div class="docsign-pp__close"><button type="button" onclick="filesignerHide()"><span>&times;</span> Закрыть</button></div>
</div>