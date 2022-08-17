<?

use Bitrix\Main\Page\Asset;
use Bitrix\Main\UI\Extension;
use Bitrix\Intranet\Integration\Templates\Bitrix24\ThemePicker;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$arDetail = $this->__component->getById((int)$_REQUEST['detail']);
$arDetailData = $arDetail['raw'];


$arDetailData['KURATOR'] = json_decode(htmlspecialcharsback($arDetailData['PROPERTY_KURATOR_VALUE']), true);
$arDetailData['TEHNADZOR'] = json_decode(htmlspecialcharsback($arDetailData['PROPERTY_TEHNADZOR_VALUE']), true);
$arDetailData['FINANCE'] = json_decode(htmlspecialcharsback($arDetailData['PROPERTY_FINANCE_VALUE']), true);

//pre($arDetailData);

$APPLICATION->SetTitle('Просмотр заявки на финансирование');

$bIframe = (isset($_REQUEST['IFRAME']) && $_REQUEST['IFRAME'] == 'Y');

$arButtons = [];
if ($arDetail['can']['edit']) {
    $arButtons[] = '<a href="' .$arDetailData['~EDIT_PAGE_URL'] . '" class="ui-btn ui-btn-primary ui-btn-icon-edit">Изменить</a>';
}

if ($bIframe) {
    global $APPLICATION;
    $APPLICATION->RestartBuffer();
    CJSCore::Init("sidepanel");

    ThemePicker::getInstance()->showHeadAssets();
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <?
        $APPLICATION->ShowHead();
        ?>
    </head>
    <body class="template-bitrix24 p-3 bitrix24-<?=ThemePicker::getInstance()->getCurrentBaseThemeId()?>-theme" style="margin-top:50px">
        <?$APPLICATION->ShowViewContent('inside_pagetitle')?>
    <?
} else {
    $arButtons[] = '<a href="' . ($arDetailData['LIST_PAGE_URL']??'/edu/financing/') . '" class="ui-btn ui-btn-icon-back">Возврат к списку</a>';
}

$APPLICATION->AddViewContent('inside_pagetitle', '
<div class="float-right" style="margin-top:-50px">
    ' . implode(' ', $arButtons) . '
</div>
');
?>
<div class="js-pdf-content">
<div class="row">
    <div class="col-9">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Основная информация</h3>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-12">
                        <table class="table table-bordered">
                            <tr>
                                <td width="50%"><b>Наименование программы:</b></td>
                                <td><?=$arDetailData['PROPERTY_PROGRAM_VALUE'] ?
                                            $arResult['PROGRAM'][ $arDetailData['PROPERTY_PROGRAM_VALUE'] ]['NAME'] :
                                            'Не заполнено'?></td>
                            </tr>
                            <tr>
                                <td><b>Наименование мероприятия программы</b></td>
                                <td><?=$arDetailData['PROPERTY_EVENT_VALUE'] ?
                                            $arResult['EVENT'][ $arDetailData['PROPERTY_EVENT_VALUE'] ]['NAME'] :
                                            'Не заполнено'?></td>
                            </tr>
                            <tr>
                                <td><b>Наименование муниципального образования</b></td>
                                <td><?=$arDetailData['PROPERTY_MUNICIPALITY_VALUE'] ?
                                            $arResult['MUNICIPALITY'][ $arDetailData['PROPERTY_MUNICIPALITY_VALUE'] ]['NAME'] :
                                            'Не заполнено'?></td>
                            </tr>
                            <tr>
                                <td><b>Наименование учреждения, где проведено мероприятие</b></td>
                                <td><?=$arDetailData['PROPERTY_ORGAN_VALUE'] ?
                                            $arResult['ORGAN'][ $arDetailData['PROPERTY_ORGAN_VALUE'] ]['NAME'] :
                                            'Не заполнено'?></td>
                            </tr>
                            <tr>
                                <td><b>Адрес, где проведено мероприятие</b></td>
                                <td><?=$arDetailData['PROPERTY_ADDRESS_VALUE']??'Не заполнено'?></td>
                            </tr>
                            <tr>
                                <td><b>Запрашиваемая сумма</b></td>
                                <td><?=$arDetailData['PROPERTY_AMOUNT_VALUE']??'Не заполнено'?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-3">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Состояние заявки</h3>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-12">
                        <b>Регистрационный номер заявки:</b><br>
                        <?=$arDetailData['NUMBER']?>
                    </div>
                </div>
                <div class="row mt-4">
                    <div class="col-12">
                        <b>Статус:</b><br>
                        <?=$arDetailData['PROPERTY_STATUS_VALUE']?>
                    </div>
                </div>
                <?
                if ($arDetail['can']['edit']) {
                    ?>
                    <div class="row mt-4">
                        <div class="col-12">
                            <button
                                id="action-send"
                                class="ui-btn ui-btn-success ui-btn-icon-start js-send-new"
                                data-id="<?=$arDetailData['ID']?>"
                            >На согласование</button>
                        </div>
                    </div>
                    <?
                }
                ?>
                <?
                if ($arDetail['can']['send_to_SUCCESS']) {
                    ?>
                    <div class="row mt-4">
                        <div class="col-12">
                            <button
                                class="ui-btn ui-btn-success ui-btn-icon-start js-send-success"
                                data-id="<?=$arDetailData['ID']?>"
                            >Согласовать</button>
                        </div>
                    </div>
                    <?
                }
                ?>
                <?
                if ($arDetail['can']['send_to_REJECT']) {
                    ?>
                    <div class="row mt-4">
                        <div class="col-12">
                            <button
                                class="ui-btn ui-btn-success ui-btn-icon-start js-send-reject"
                                data-id="<?=$arDetailData['ID']?>"
                            >Отклонить</button>
                        </div>
                    </div>
                    <?
                }
                ?>


                <? if ($arDetail['can']['send_to_REPEAT']): ?>
                    <div class="row mt-4">
                        <div class="col-12">
                            <button
                              class="ui-btn ui-btn-success ui-btn-icon-start js-send-repeat"
                              data-id="<?=$arDetailData['ID']?>"
                            >Повторное согласование</button>
                        </div>
                    </div>
                <?endif;?>

            </div>
        </div>
    </div>
</div>
<?
if (!empty($arDetailData['PROPERTY_FILES_DESC_VALUE']) || !empty($arDetailData['PROPERTY_FILES_VALUE'])) {
    ?>
    <div class="row">
        <div class="col-12">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Подтверждающие документы</h3>
                </div>
                <div class="box-body">
                    <?
                    if (!empty($arDetailData['PROPERTY_FILES_DESC_VALUE'])) {
                        ?>
                        <div class="row">
                            <div class="col-12">
                                <div id="files-table"></div>
                                <script type="text/javascript">
                                    BX.Vue.create({
                                        el: '#files-table',
                                        template: '<files-table />',
                                        data: {
                                            edit: false,
                                            items: <?=json_encode($arDetailData['PROPERTY_FILES_DESC_VALUE']??[], JSON_UNESCAPED_UNICODE)?>,
                                        }
                                    });
                                </script>
                            </div>
                        </div>
                        <?
                    }

                    if (!empty($arDetailData['PROPERTY_FILES_VALUE'])) {
                        ?>
                        <div class="row">
                            <div class="col-12">
                                <b>Файлы:</b><br>
                                <?
                                $arFiles = [];
                                foreach ($arDetailData['PROPERTY_FILES_VALUE'] as $fId) {
                                    $arFile = CFile::GetFileArray($fId);
                                    $arFiles[] = '<a href="'.htmlspecialcharsbx($arFile["SRC"]).'" title="Скачать" target="_blank" download>'.htmlspecialcharsbx($arFile["ORIGINAL_NAME"]).'</a>';
                                }
                                ?>
                                <ul>
                                    <li><?=implode('</li><li>', $arFiles)?></li>
                                </ul>
                            </div>
                        </div>
                        <?
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
    <?
}

if ($arDetailData['PROPERTY_STATUS_ENUM_ID'] != $arResult['ENUMS']['STATUS']['DRAFT']['ID']) {
    ?>
    <? if ($arDetail['previous']):?>

        <? foreach ($arDetail['previous'] as $id => $versionData): ?>
            <div class="row">
              <div class="col-12">
                <div class="box box-primary">
                  <div class="box-header with-border">
                    <h3 class="box-title">Согласование <span><?=$versionData['NUMBER']?> / <?=$versionData['VERSION']?>, <?=$versionData['DATE']?></span></h3>
                  </div>
                  <div class="box-body">
                    <table class="results">
                      <tr class="results__row">

                        <td>Куратор программы</td>
                        <td class="colored <?=$versionData['KURATOR']['COLOR']?>"><?=$versionData['KURATOR']['STATUS']?><br>
                            <?=$versionData['KURATOR']['DATE']?><br>
                            <?=$versionData['KURATOR']['FIO']?></td>
                      </tr>
                      <tr class="results__row">

                        <td>Сотрудник технического надзора</td>
                        <td class="colored <?=$versionData['TEHNADZOR']['COLOR']?>"><?=$versionData['TEHNADZOR']['STATUS']?><br>
                            <?=$versionData['TEHNADZOR']['DATE']?><br>
                            <?=$versionData['TEHNADZOR']['FIO']?></td>
                      </tr>
                      <tr class="results__row">

                        <td>Специалист отдела финансирования</td>
                        <td class="colored <?=$versionData['FINANCE']['COLOR']?>"><?=$versionData['FINANCE']['STATUS']?><br>
                            <?=$versionData['FINANCE']['DATE']?><br>
                            <?=$versionData['FINANCE']['FIO']?></td>
                      </tr>
                    </table>
                  </div>
                </div>
              </div>
            </div>
        <?endforeach;?>

    <?endif;?>
    <div class="row">
        <div class="col-12">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Согласование <span><?=$arDetail['previous'] ? $arDetailData['PROPERTY_NUMBER_VALUE'].' / '.$arDetailData['PROPERTY_VERSION_VALUE'].', '.$arDetailData['PROPERTY_DATE_VALUE'] : ''?></span></h3>
                </div>
                <div class="box-body">
                    <table class="results">
                        <tr class="results__row">
                            <? if ($arResult['ROLES']['FINANCE']): ?>
                            <td class="results__checkbox"><input type="checkbox" name="kurator" class="js-checkbox-repeat"></td>
                            <?endif;?>
                            <td>Куратор программы</td>
                            <td class="colored <?=$arDetailData['KURATOR']['COLOR']?>"><?=$arDetailData['KURATOR']['STATUS']?><br>
                            <?=$arDetailData['KURATOR']['DATE']?><br>
                            <?=$arDetailData['KURATOR']['FIO']?></td>
                        </tr>
                        <tr class="results__row">
                            <? if ($arResult['ROLES']['FINANCE']): ?>
                              <td class="results__checkbox"><input type="checkbox" name="tech" class="js-checkbox-repeat"></td>
                            <?endif;?>
                            <td>Сотрудник технического надзора</td>
                          <td  class="colored <?=$arDetailData['TEHNADZOR']['COLOR']?>"><?=$arDetailData['TEHNADZOR']['STATUS']?><br>
                              <?=$arDetailData['TEHNADZOR']['DATE']?><br>
                              <?=$arDetailData['TEHNADZOR']['FIO']?></td>
                        </tr>
                        <tr class="results__row">
                            <? if ($arResult['ROLES']['FINANCE']): ?>
                              <td class="results__checkbox"></td>
                            <?endif;?>
                            <td>Специалист отдела финансирования</td>
                          <td class="colored <?=$arDetailData['FINANCE']['COLOR']?>"><?=$arDetailData['FINANCE']['STATUS']?><br>
                              <?=$arDetailData['FINANCE']['DATE']?><br>
                              <?=$arDetailData['FINANCE']['FIO']?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
    <?
}

if ($bIframe) {
    ?>
    </body>
    </html>
    <?
    require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_after.php');
    exit;
}

//pre($arDetailData);
//pre($arDetail);


