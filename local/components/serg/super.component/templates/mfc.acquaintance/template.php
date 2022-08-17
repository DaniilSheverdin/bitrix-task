<?php

use CBPRuntime;
use CIBlockElement;
use ReflectionClass;
use Bitrix\Main\Loader;
use Bitrix\Main\UserTable;
use Bitrix\Main\Page\Asset;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) {
    die();
}

global $USER, $userFields;

Asset::getInstance()->addJs('/bitrix/templates/.default/bootstrap.min.js');
Asset::getInstance()->addCss('/bitrix/templates/.default/bootstrap.min.css');

Loader::includeModule('bizproc');
Loader::includeModule('iblock');

if (!isset($arParams['wf'])) {
    LocalRedirect('/company/personal/bizproc/');
}

/**
 * Как достать приватное свойство активити?
 * Можно было сделать свою активити, но лучше через рефлексию достать.
 * @param type $object
 * @param type $property
 * @return mixed
 */
function getPrivateField($object, $property)
{
    $refClass = new ReflectionClass(get_class($object));
    $refProp = $refClass->getProperty($property);
    $refProp->setAccessible(true);

    return $refProp->getValue($object);
}

function mySorter($key)
{
    return function ($a, $b) use ($key) {
        return strnatcmp($a[ $key ], $b[ $key ]);
    };
}

$arWorkflowState = CBPStateService::GetWorkflowState($arParams["wf"]);

$bAccess = ($USER->GetID() == $arWorkflowState['STARTED_BY']);
$bAccess = ($bAccess || $USER->IsAdmin());

if (!$bAccess) {
    ShowError('Доступ запрещён');
} else {
    try {
        $documentId     = $arWorkflowState['DOCUMENT_ID'][2];
        $arReviews      = [];
        $arTaskFilter   = [
            'WORKFLOW_ID'   => $arWorkflowState['ID'],
            'ACTIVITY_NAME' => 'OZNAKOMLENIE'
        ];
        $res = CBPAllTaskService::GetList([], $arTaskFilter);
        while ($row = $res->Fetch()) {
            $arTaskUsers = CBPAllTaskService::getTaskUsers($row['ID']);
            foreach ($arTaskUsers[ $row['ID'] ] as $arUser) {
                $arUserData = $userFields($arUser['USER_ID']);
                $bIsMfc = false;
                foreach ($arUserData['DEPARTMENTS'] as $depName) {
                    if (false !== mb_strpos($depName, 'МФЦ')) {
                        $bIsMfc = true;
                    }
                }
                $arUserData['DEPARTMENTS_SHORT'] = array_reverse(
                    $bIsMfc ?
                        array_slice(
                            $arUserData['DEPARTMENTS'],
                            0,
                            count($arUserData['DEPARTMENTS'])-4
                        ) :
                        $arUserData['DEPARTMENTS']
                );
                if ($arUser['STATUS'] > 0) {
                    $arReviews[] = [
                        'NAME'          => $arUserData['FIO'],
                        'DEPARTMENT'    => implode(' / ', $arUserData['DEPARTMENTS_SHORT']),
                        'WORK_POSITION' => $arUserData['DOLJNOST_CLEAR'],
                        'DATE'          => date('d.m.Y', strtotime($arUser['DATE_UPDATE'])),
                    ];
                } else {
                    $arSkip[] = [
                        'NAME'          => $arUserData['FIO'],
                        'DEPARTMENT'    => implode(' / ', $arUserData['DEPARTMENTS_SHORT']),
                        'WORK_POSITION' => $arUserData['DOLJNOST_CLEAR'],
                    ];
                }
            }
        }

        usort($arReviews, mySorter('NAME'));
        usort($arSkip, mySorter('NAME'));

        $arElement = CIBlockElement::GetByID($documentId)->Fetch();
        $APPLICATION->SetTitle($arElement['NAME']);

        if (!empty($arReviews)) {
            ?>
            <h2>Ознакомились</h2>

            <button onclick="tableToExcel('reviewed', 'Ознакомились с <?=$arElement['NAME'];?>')" class="btn btn-success">Выгрузить</button>
            <br/><br/>

            <table class="table table-bordered" id="reviewed">
                <thead>
                    <tr>
                        <th>ФИО</th>
                        <th>Подразделение</th>
                        <th>Должность</th>
                        <th>Дата ознакомления</th>
                    </tr>
                </thead>
                <?foreach ($arReviews as $arUser) :?>
                    <tr>
                        <td><?=$arUser['NAME'];?></td>
                        <td><?=$arUser['DEPARTMENT'];?></td>
                        <td><?=$arUser['WORK_POSITION'];?></td>
                        <td>Ознакомлен&nbsp;<?=$arUser['DATE'];?></td>
                    </tr>
                <?endforeach;?>
            </table>
            <?
        }

        if (!empty($arSkip)) {
            ?>
            <h2>Не ознакомились</h2>

            <button onclick="tableToExcel('notreviewed', 'Не ознакомились с <?=$arElement['NAME'];?>')" class="btn btn-success">Выгрузить</button>
            <br/><br/>

            <table class="table table-bordered" id="notreviewed">
                <thead>
                    <tr>
                        <th>ФИО</th>
                        <th>Подразделение</th>
                        <th>Должность</th>
                    </tr>
                </thead>
                <?foreach ($arSkip as $arUser) :?>
                    <tr>
                        <td><?=$arUser['NAME'];?></td>
                        <td><?=$arUser['DEPARTMENT'];?></td>
                        <td><?=$arUser['WORK_POSITION'];?></td>
                    </tr>
                <?endforeach;?>
            </table>
            <?
        }
    } catch (Exception $e) {
        ShowError($e->getMessage());
    }
}
