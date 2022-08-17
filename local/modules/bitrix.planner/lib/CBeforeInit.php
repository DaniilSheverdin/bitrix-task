<?php

namespace HolidayList;

use CUserTypeEntity;
use COption;
use CModule;
use CEventType;
use CSite;
use CEventMessage;
use CIBlockProperty;
use CIBlockPropertyEnum;

class CBeforeInit
{
    public static function start()
    {
        if (!CModule::IncludeModule('iblock') || !CModule::IncludeModule('intranet') || !CModule::IncludeModule('im')) {
            die("Плагин не установлен");
        }

        $iBlock = COption::GetOptionInt('intranet', 'iblock_structure');

        // Проверяем на существование пользовательские поля. Если их нет, то создаём
        // Поле: Отпуска всех пользователей подтверждены?
        $UF_COUNT_ALL_EMP = CUserTypeEntity::GetList(array($by => $order), array('ENTITY_ID' => 'IBLOCK_' . $iBlock . '_SECTION', 'FIELD_NAME' => 'UF_COUNT_ALL_EMP'));
        if (!$UF_COUNT_ALL_EMP->arResult) {
            $arFields = array(
                "ENTITY_ID" => 'IBLOCK_' . $iBlock . '_SECTION',
                "FIELD_NAME" => "UF_COUNT_ALL_EMP",
                "USER_TYPE_ID" => "string",
                "EDIT_FORM_LABEL" => array("ru" => "Отпуска всех пользователей подтверждены?", "en" => "UF_COUNT_ALL_EMP")
            );
            $obUserField = new CUserTypeEntity();
            $obUserField->Add($arFields);
        }

        // Поле: Отправлено ли министерство на согласование с отделом кадров?
        $UF_TO_CADRS = CUserTypeEntity::GetList(array($by => $order), array('ENTITY_ID' => 'IBLOCK_' . $iBlock . '_SECTION', 'FIELD_NAME' => 'UF_TO_CADRS'));
        if (!$UF_TO_CADRS->arResult) {
            $arFields = array(
                "ENTITY_ID" => 'IBLOCK_' . $iBlock . '_SECTION',
                "FIELD_NAME" => "UF_TO_CADRS",
                "USER_TYPE_ID" => "string",
                "EDIT_FORM_LABEL" => array("ru" => "Отправлена ли структура на согласование с отделом кадров?", "en" => "UF_TO_CADRS")
            );
            $obUserField = new CUserTypeEntity();
            $obUserField->Add($arFields);
        }

        // Почта
        $obEventType = new CEventType();
        $obEventType->Add(array(
            "EVENT_NAME"    => "ADV_PLANNER_EVENT",
            "NAME"          => "Оповещения на почту для модуля графика отпусков",
            "LID"           => "ru",
            "DESCRIPTION"   => "#DESCRIPTION#, #EMAIL_TO#"
        ));

        $arSites = [];
        $rsSites = CSite::GetList($by="sort", $order="desc");
        while ($a = $rsSites->Fetch()) {
            array_push($arSites, $a['LID']);
        }
        $rsMess = CEventMessage::GetList($by="site_id", $order="desc", ['TYPE_ID' => ["ADV_PLANNER_EVENT"], "SITE_ID" => $arSites]);
        if (!$rsMess->GetNext()) {
            $arr["ACTIVE"] = "Y";
            $arr["EVENT_NAME"] = "ADV_PLANNER_EVENT";
            $arr["LID"] = $arSites;
            $arr["EMAIL_FROM"] = "#DEFAULT_EMAIL_FROM#";
            $arr["EMAIL_TO"] = "#EMAIL_TO#";
            $arr["SUBJECT"] = "График отпусков";
            $arr["BODY_TYPE"] = "text";
            $arr["MESSAGE"] = "#DESCRIPTION#";
            $emess = new CEventMessage();
            $emess->Add($arr);
        }

        $obUserField = new CUserTypeEntity();
        $aUserFields    = array(
            'ENTITY_ID'         => 'IBLOCK_'.COption::GetOptionInt('intranet', 'iblock_structure').'_SECTION',
            'FIELD_NAME'        => 'UF_HIDEDEP',
            'USER_TYPE_ID'      => 'boolean',
            'SORT'              => 500,
            'MANDATORY'         => 'N',
            'SHOW_FILTER'       => 'N',
            'SHOW_IN_LIST'      => '',
            'EDIT_IN_LIST'      => '',
            'IS_SEARCHABLE'     => 'N',
            'EDIT_FORM_LABEL'   => array(
                'ru'    => 'Не показывать в графике отпусков',
                'en'    => '',
            ),
            'LIST_COLUMN_LABEL' => array(
                'ru'    => 'Не показывать в графике отпусков',
                'en'    => '',
            ),
        );
        $iUserFieldId = $obUserField->Add($aUserFields);

        $arFields = array(
            "ENTITY_ID" => 'USER',
            "FIELD_NAME" => "UF_THIS_HEADS",
            "USER_TYPE_ID" => "string",
            "EDIT_FORM_LABEL" => array("ru" => "Руководители", "en" => "UF_THIS_HEADS")
        );
        $obUserField->Add($arFields);

        $arFields = array(
            "ENTITY_ID" => 'USER',
            "FIELD_NAME" => "UF_SUBORDINATE",
            "USER_TYPE_ID" => "string",
            "EDIT_FORM_LABEL" => array("ru" => "Подчинённые", "en" => "UF_SUBORDINATE")
        );
        $obUserField->Add($arFields);

        if (!CIBlockProperty::GetByID("UF_WHO_APPROVE", COption::GetOptionInt('intranet', 'iblock_absence'))->GetNext()) {
            $arFields = array(
                "NAME" => "Лица, утвердившие отпуск",
                "ACTIVE" => "Y",
                "SORT" => "600",
                "CODE" => "UF_WHO_APPROVE",
                "PROPERTY_TYPE" => "S",
                "IBLOCK_ID" => COption::GetOptionInt('intranet', 'iblock_absence'),
            );
            $ibp = new CIBlockProperty();
            $ibp->Add($arFields);
        }

        $obEnum = CIBlockProperty::GetPropertyEnum('ABSENCE_TYPE', [], ['EXTERNAL_ID' => 'VACATION_ADD', 'IBLOCK_ID' => COption::GetOptionInt('intranet', 'iblock_absence')]);
        $arEnumVacationAdd = $obEnum->GetNext();
        if (!$arEnumVacationAdd) {
            $ibpenum = new CIBlockPropertyEnum;
            $iProperty = \CIBlockProperty::GetList(
                [],
                [
                    'IBLOCK_ID' => COption::GetOptionInt('intranet', 'iblock_absence'),
                    'CODE' => 'ABSENCE_TYPE'
                ])->Fetch()['ID'];
            $ibpenum->Add(['PROPERTY_ID' => $iProperty, 'VALUE' => 'отпуск дополнительный', 'XML_ID' => 'VACATION_ADD', 'EXTERNAL_ID' => 'VACATION_ADD']);
        }
    }
}
