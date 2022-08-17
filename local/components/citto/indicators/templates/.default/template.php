<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */
$this->setFrameMode(true);
CJSCore::Init(
    [
        'jquery3',
        'popup',
        'ui',
        'amcharts',
        'amcharts_i18n',
        'amcharts_serial',
        // 'amcharts_pie',
        // 'amcharts_xy',
        // 'amcharts_radar',
        'amcharts_export',
    ]
);
global $USER;
?>
<section class="official">
    <div class="wrapper">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-9">
                    <form class="official__search-form" action="?show=list">
                        <div class="input-group">
                            <input name="search" type="search"
                                   class="form-control search-form__input"
                                   placeholder="Поиск..."
                                   aria-label="Поиск..."
                                   value="<?=$_REQUEST['search']?>"
                            >
                            <input type="hidden" name="show" value="list">
                            <div class="input-group-append">
                                <button class="btn search-form__button" type="button">
                                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none"
                                         xmlns="http://www.w3.org/2000/svg">
                                        <path d="M19.6959 18.2168L14.7656 13.2662C16.0332 11.8113 16.7278 9.98069 16.7278 8.07499C16.7278 3.62251 12.9757 0 8.36391 0C3.75212 0 0 3.62251 0 8.07499C0 12.5275 3.75212 16.15 8.36391 16.15C10.0952 16.15 11.7451 15.6458 13.1557 14.6888L18.1235 19.677C18.3311 19.8852 18.6104 20 18.9097 20C19.193 20 19.4617 19.8957 19.6657 19.7061C20.0992 19.3034 20.113 18.6357 19.6959 18.2168ZM8.36391 2.10652C11.7727 2.10652 14.5459 4.78391 14.5459 8.07499C14.5459 11.3661 11.7727 14.0435 8.36391 14.0435C4.95507 14.0435 2.18189 11.3661 2.18189 8.07499C2.18189 4.78391 4.95507 2.10652 8.36391 2.10652Z"
                                              fill="#12183C"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="col-12">
                    <div class="official__indicators-block">
                        <?
                        foreach ($arResult['INDICATOR_THEMES_NAMES'] as $sKey => $sValue) {?>
                            <a href="?show=list&set_filter=y&arrFilter_2636_<?=abs(crc32($sValue))?>=Y" class="official__indicator btn"><?=$sKey?></a>
                        <?}
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?
global $arrFilter;
switch ($_REQUEST['show']) {
    case 'list':
        require_once('list.php');
        break;
    case 'passport':
        require_once('passport.php');
        break;
    default:
        require_once('main.php');
        break;
}
