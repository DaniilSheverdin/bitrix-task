<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}
/*CModule::AddAutoloadClasses(
    '',
    array(
        // ключ - имя класса, значение - путь относительно корня сайта к файлу с классом
        'Example\ExampleChild' => '/local/lib/Example/ExampleChild.php',
    )
);*/

//use Example\ExampleChild;

use Bitrix\Main\UI\PageNavigation;

// saving template name to cache array
$arResult["__TEMPLATE_FOLDER"] = $this->__folder;
// writing new $arResult to cache file
$this->__component->arResult = $arResult;

global $USER, $APPLICATION;

//define('AUTH_TEST', true);
define("HLBID", 6);
define("COUNT_ON_PAGE", 20);
define("ACCESS_GUBERNATOR_CALL", 107);

use Bitrix\Highloadblock\HighloadBlockTable as HL;

$APPLICATION->SetAdditionalCss("/bitrix/css/main/bootstrap_v4/bootstrap.min.css");

if (getenv("HTTP_HOST") == 'mfc') {
    $USER->Authorize(10873);
}

$arResult['IS_AJAX'] = ($arParams['IS_AJAX'] == "Y");

$arResult['edit_result'] = false;
$arResult['message_result'] = '';

$accessGroups = \CUser::GetUserGroupArray();
if (in_array(ACCESS_GUBERNATOR_CALL, $accessGroups) || $USER->IsAdmin()) {
    $arResult['access_edit'] = true;

    if (CModule::IncludeModule('highloadblock')) {
        $classEntityData = HL::compileEntity(HL::getById(HLBID)->fetch())->getDataClass();
        // редактирование данных
        if ($arResult['IS_AJAX']) {
            $arRecordData = $_REQUEST['gub'];

            if ($arRecordData['action'] == 'edit') {
                $arResult['edit_result'] = true;
                $arResult['class_result'] = 'danger';
                $arResult['message_result'] = 'Ошибка при добавлении';

                $addResult = $classEntityData::add([
                    'UF_DATECALL' => (new DateTime($arRecordData['date_call']))->format("d.m.Y") . ' ' . $arRecordData['time_call'] . ':00',
                    'UF_FIOCALL' => htmlspecialcharsbx($arRecordData['fio_call']),
                    'UF_ORGCALL' => htmlspecialcharsbx($arRecordData['organization_call']),
                    'UF_QUESTION' => htmlspecialcharsbx($arRecordData['question_call']),
                    'UF_NOTECALL' => htmlspecialcharsbx($arRecordData['note_call']),
                ]);

                if ($addResult->getId()) {
                    $arResult['class_result'] = true;
                    $arResult['class_result'] = 'success';
                    $arResult['message_result'] = 'Успешно добавлено';
                }
            } elseif ($arRecordData['action'] == 'delete') {
                $delResult = $classEntityData::delete($arRecordData['ID']);
                if ($delResult->isSuccess()) {
                    //echo 'OK';
                } else {
                    //echo 'ERROR_DELETE';
                }
            } elseif ($arRecordData['action'] == 'inwork') {
                $editResult = $classEntityData::update($arRecordData['ID'], [
                    'UF_INWORK' => $arRecordData['inwork']
                ]);
                if ($editResult->isSuccess()) {
                    //echo 'OK';
                } else {
                    //echo 'ERROR_DELETE';
                }
            }
        }

        // вывод уже имеющихся данных
        $arParamList = [
            'select' => array("*"),
            'order' => array(
                'UF_DATECALL' => 'desc',
                'UF_FIOCALL' => 'asc'
            ),
            "count_total" => true
        ];

        if ($arRecordData['action'] == 'handle') {
            $arParamList['filter'] = ['UF_INWORK' => '2'];
        } elseif ($arRecordData['action'] == 'nohandle') {
            $arParamList['filter'] = ['UF_INWORK' => '1'];
        }

        $arResult['filter_list'] = isset($arRecordData['action']) ? $arRecordData['action'] : 'all';

        $nav = new PageNavigation("nav-more-news");
        $nav->allowAllRecords(true)->setPageSize(COUNT_ON_PAGE)->initFromUri();

        $arParamList["offset"] = $nav->getOffset();
        $arParamList["limit"] = $nav->getLimit();

        $resList = $classEntityData::getList($arParamList);
        $nav->setRecordCount($resList->getCount());

        $arResult['objNAV'] = $nav;

        $arResult['CALL_LIST'] = $resList->fetchAll();
        $arResult['CALL_COUNT'] = count($arResult['CALL_LIST']);
        $arResult['CALL_LIST_EXIST'] = ($arResult['CALL_COUNT'] > 0);
    }
} else {
    $arResult['access_edit'] = false;
}
