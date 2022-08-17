<?

use Bitrix\Main\Page\Asset;
use Bitrix\Main\UI\Extension;
use Bitrix\Intranet\Integration\Templates\Bitrix24\ThemePicker;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$arDetail = $this->__component->getById((int)$_REQUEST['edit']);
$arDetailData = $arDetail['raw'];

$APPLICATION->SetTitle((empty($arDetailData) ? 'Создание' : 'Редактирование') . ' заявки на финансирование');

if (isset($_REQUEST['IFRAME']) && $_REQUEST['IFRAME'] == 'Y') {
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
    <?
} else {
    $APPLICATION->AddViewContent('inside_pagetitle', '
    <div class="float-right" style="margin-top:-50px">
        <a href="' . ($arDetailData['DETAIL_PAGE_URL']??'/edu/financing/') . '" class="ui-btn ui-btn-light-border ui-btn-icon-back">' . ($arDetailData['DETAIL_PAGE_URL']?'Просмотр':'Возврат к списку') . '</a>
    </div>
    ');
}

function renderTree(array $arData = [], int $selected = 0, $level = 1): string
{
    $return = '';
    // $bFirst = true;
    foreach ($arData as $row) {
        if (!empty($row['child'])) {
            $return .= '<optgroup label="' . $row['name'] . '">';
            $return .= renderTree($row['child'], $selected, ++$level);
            $return .= '</optgroup>';
        } else {
            // if ($bFirst && $level == 1) {
            //     $return .= '<option>Не выбрано</option>';
            //     $bFirst = false;
            // }
            $return .= '<option value="' . $row['id'] . '" ' . ($selected == $row['id'] ? 'selected' : '') . '>' . $row['name'] . '</option>';
        }
    }
    return $return;
}
?>
<form method="POST">
    <?=bitrix_sessid_post()?>
    <input type="hidden" name="ID" value="<?=$arDetailData['ID']??0?>" />
    <input type="hidden" name="do" value="update" />
    <div class="row">
        <div class="col-9">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Основная информация</h3>
                </div>
                <div class="box-body">
                    <div class="row mb-2">
                        <div class="col-12">
                            <b><span class="required">*</span>Наименование программы:</b><br>
                            <select class="form-control" required name="PROGRAM">
                                <?=renderTree($arResult['TREE']['PROGRAM'], (int)$arDetailData['PROPERTY_PROGRAM_VALUE'])?>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-12">
                            <b><span class="required">*</span>Наименование мероприятия программы:</b><br>
                            <?
                            foreach ($arResult['TREE']['EVENT'] as $parent => $events) {
                                ?>
                                <select class="form-control <?=$parent!=$arDetailData['PROPERTY_PROGRAM_VALUE']?'d-none':''?> event event-<?=$parent?>" required name="EVENT">
                                    <?=renderTree($events, (int)$arDetailData['PROPERTY_EVENT_VALUE'])?>
                                </select>
                                <?
                            }
                            ?>
                            <select class="form-control event event-0" required name="EVENT">
                                <option>Не выбрана программа</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-12">
                            <b><span class="required">*</span>Наименование муниципального образования:</b><br>
                            <select class="form-control" required name="MUNICIPALITY">
                                <?=renderTree($arResult['TREE']['MUNICIPALITY'], (int)$arDetailData['PROPERTY_MUNICIPALITY_VALUE'])?>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-12">
                            <b><span class="required">*</span>Наименование учреждения, где проведено мероприятие:</b><br>
                            <?
                            foreach ($arResult['TREE']['ORGAN'] as $parent => $events) {
                                ?>
                                <select class="form-control <?=$parent!=$arDetailData['PROPERTY_MUNICIPALITY_VALUE']?'d-none':''?> organ organ-<?=$parent?>" required name="ORGAN">
                                    <?=renderTree($events, (int)$arDetailData['PROPERTY_ORGAN_VALUE'])?>
                                </select>
                                <?
                            }
                            ?>
                            <select class="form-control organ organ-0" required name="ORGAN">
                                <option>Не выбрано МО</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-12">
                            <b><span class="required">*</span>Адрес, где проведено мероприятие:</b><br>
                            <textarea class="form-control" required name="ADDRESS"><?=$arDetailData['PROPERTY_ADDRESS_VALUE']?></textarea>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <b><span class="required">*</span>Запрашиваемая сумма:</b><br>
                            <textarea class="form-control" required name="AMOUNT"><?=$arDetailData['PROPERTY_AMOUNT_VALUE']?></textarea>
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
                            <i><?=$arDetailData['NUMBER']??'Ещё не присвоен'?></i>
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-12">
                            <b>Статус:</b><br>
                            <i><?=$arDetailData['PROPERTY_STATUS_VALUE']??'Не сохранён'?></i>
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-12">
                            <button type="submit" class="ui-btn ui-btn-primary ui-btn-icon-task"><?=$arDetailData['ID']>0?'Сохранить':'Добавить'?></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Подтверждающие документы</h3>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-12">
                            <div id="files-table"></div>
                            <script type="text/javascript">
                                BX.Vue.create({
                                    el: '#files-table',
                                    template: '<files-table />',
                                    data: {
                                        edit: true,
                                        items: <?=json_encode($arDetailData['PROPERTY_FILES_DESC_VALUE']??[], JSON_UNESCAPED_UNICODE)?>,
                                    }
                                });
                            </script>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <b>Файлы:</b><br>
                            <?$APPLICATION->IncludeComponent(
                                'bitrix:main.file.input',
                                'drag_n_drop',
                                array(
                                    'INPUT_NAME'        => 'FILES',
                                    'INPUT_VALUE'       => $arDetailData['PROPERTY_FILES_VALUE'],
                                    'MULTIPLE'          => 'Y',
                                    'MODULE_ID'         => 'edu_financing',
                                    'MAX_FILE_SIZE'     => '',
                                    'ALLOW_UPLOAD'      => 'A', 
                                    'ALLOW_UPLOAD_EXT'  => ''
                                ),
                                false
                            );?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
<?
if (isset($_REQUEST['IFRAME']) && $_REQUEST['IFRAME'] == 'Y') {
    ?>
    </body>
    </html>
    <?
    require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_after.php');
    exit;
}