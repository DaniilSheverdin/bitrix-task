<?php

namespace Citto\ControlOrders\Register;

use CUser;
use Exception;
use CIBlockElement;
use CIBlockSection;
use CBitrixComponent;
use Bitrix\Main\Loader;
use CIBlockPropertyEnum;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

Loader::includeModule('iblock');
global $APPLICATION;
$APPLICATION->SetAdditionalCSS('/bitrix/templates/.default/bootstrap.min.css');

class Component extends CBitrixComponent
{
    private $arIspolnitels;
    private $arDepartments;
    private $arIspolnitelsTypes;
    private $arIspolnitelsNames;

    /**
     * Get results
     *
     * @return array
     */
    protected function getResult()
    {
        $obEnums = CIBlockPropertyEnum::GetList(
            array(
                'DEF'   => 'DESC',
                'SORT'  => 'ASC',
            ),
            array(
                'IBLOCK_ID' => $this->arParams['IBLOCK_ID_ISPOLNITEL'],
                'CODE'      => 'TYPE',
            )
        );
        while ($arFields = $obEnums->GetNext()) {
            $this->arIspolnitelsTypes[ $arFields['ID'] ] = $arFields;
        }

        $arSelect = array(
            'ID',
            'NAME',
            'DATE_ACTIVE_FROM',
            'PROPERTY_RUKOVODITEL',
            'PROPERTY_ZAMESTITELI',
            'PROPERTY_ISPOLNITELI',
            'PROPERTY_TYPE'
        );
        $arFilter = array(
            'IBLOCK_ID'     => intval($this->arParams['IBLOCK_ID_ISPOLNITEL']),
            'PROPERTY_TYPE' =>array(),
            'ACTIVE_DATE'   => 'Y',
            'ACTIVE'        => 'Y'
        );
        $res = CIBlockElement::GetList(array(), $arFilter, false, false, $arSelect);
        while ($arFields = $res->GetNext()) {
            if ($arFields['PROPERTY_TYPE_ENUM_ID'] != '') {
                if (isset($this->arIspolnitelsTypes[ $arFields['PROPERTY_TYPE_ENUM_ID'] ]['CNT'])) {
                    $this->arIspolnitelsTypes[ $arFields['PROPERTY_TYPE_ENUM_ID'] ]['CNT']++;
                } else {
                    $this->arIspolnitelsTypes[ $arFields['PROPERTY_TYPE_ENUM_ID'] ]['CNT'] = 0;
                }
            }

            $this->arIspolnitelsNames[ $arFields['NAME'] ] = $arFields['ID'];
            $this->arIspolnitels[ $arFields['ID'] ] = $arFields;
        }
        $arCreateDepartments = [
            $this->arParams['DEPARTMENT_ID_OMSU'],
            458
        ];
        foreach ($arCreateDepartments as $depId) {
            $rsParentSection = CIBlockSection::GetByID($depId);
            if ($arParentSection = $rsParentSection->GetNext()) {
                $arFilter = array(
                    'IBLOCK_ID'     => $arParentSection['IBLOCK_ID'],
                    '>LEFT_MARGIN'  => $arParentSection['LEFT_MARGIN'],
                    '<RIGHT_MARGIN' => $arParentSection['RIGHT_MARGIN'],
                    '>DEPTH_LEVEL'  => $arParentSection['DEPTH_LEVEL']
                );
                $rsSect = CIBlockSection::GetList(array('left_margin' => 'asc'), $arFilter);
                while ($arSect = $rsSect->GetNext()) {
                    $arSectFields = $GLOBALS['USER_FIELD_MANAGER']->GetUserFields('IBLOCK_5_SECTION', $arSect['ID'], LANGUAGE_ID);
                    if (
                        (
                            $arSectFields['UF_PODVED']['VALUE'] ||
                            $depId == $this->arParams['DEPARTMENT_ID_OMSU']
                        ) &&
                        $this->arIspolnitelsNames[ $arSect['NAME'] ]
                    ) {
                        $arSect['ISPOLNITEL_ID'] = $this->arIspolnitelsNames[ $arSect['NAME'] ];
                        $this->arDepartments[ $depId ][ $arSect['ID'] ] = $arSect;
                    }
                }
            }
        }

        $arResult['DEPARTMENTS']     = $this->arDepartments;
        $arResult['ISPOLNITELS']     = $this->arIspolnitels;
        $arResult['ISPOLNITELTYPES'] = $this->arIspolnitelsTypes;
        if ($_REQUEST['submit'] != '') {
            $obUser = new CUser();
            $arFields = $_REQUEST;
            $arFields = array_map('trim', $arFields);
            $arFields['ACTIVE'] = 'Y';
            $arFields['EMAIL'] = $arFields['LOGIN'];

            $arFields['GROUP_ID'] = [36, 96, 108];
            $arFields['LID'] = 's1';
            $arFields['UF_DEPARTMENT'] = [$_REQUEST['i']];
            $sUserId = $obUser->Add($arFields);

            if ((int)$sUserId > 0) {
                if ($_REQUEST['subaction'] == 'deputy') {
                    $arValues = array();
                    $obRes    = CIBlockElement::GetProperty(
                        $this->arParams['IBLOCK_ID_ISPOLNITEL'],
                        $arResult['DEPARTMENTS'][ $_REQUEST['PARENT'] ][ $_REQUEST['i'] ]['ISPOLNITEL_ID'],
                        'sort',
                        'asc',
                        array('CODE' => 'ZAMESTITELI')
                    );
                    while ($arValue = $obRes->GetNext()) {
                        $arValues[] = $arValue['~VALUE'];
                    }
                    $arValues[] = $sUserId;
                    $arPropFields = array('ZAMESTITELI' => $arValues);
                } elseif ($_REQUEST['subaction']=='introduction') {
                    $arValues = array();
                    $obRes    = CIBlockElement::GetProperty(
                        $this->arParams['IBLOCK_ID_ISPOLNITEL'],
                        $arResult['DEPARTMENTS'][ $_REQUEST['PARENT'] ][ $_REQUEST['i'] ]['ISPOLNITEL_ID'],
                        'sort',
                        'asc',
                        array('CODE' => 'IMPLEMENTATION')
                    );
                    while ($arValue = $obRes->GetNext()) {
                        $arValues[] = $arValue['~VALUE'];
                    }
                    $arValues[] = $sUserId;
                    $arPropFields = array('IMPLEMENTATION' => $arValues);
                } else {
                    $arValues = array();
                    $obRes    = CIBlockElement::GetProperty(
                        $this->arParams['IBLOCK_ID_ISPOLNITEL'],
                        $arResult['DEPARTMENTS'][ $_REQUEST['PARENT'] ][ $_REQUEST['i'] ]['ISPOLNITEL_ID'],
                        'sort',
                        'asc',
                        array('CODE' => 'ISPOLNITELI')
                    );
                    while ($arValue = $obRes->GetNext()) {
                        $arValues[] = $arValue['~VALUE'];
                    }
                    $arValues[] = $sUserId;
                    $arPropFields = array('ISPOLNITELI' => $arValues);
                }
                CIBlockElement::SetPropertyValuesEx(
                    $arResult['DEPARTMENTS'][ $_REQUEST['PARENT'] ][ $_REQUEST['i'] ]['ISPOLNITEL_ID'],
                    false,
                    $arPropFields
                );
                $arResult['MESSAGE'] = 'Пользователь успешно добавлен! <a href="/control-orders/">Авторизация</a>';
                $arResult['STATUS'] = 'success';
            } else {
                $arResult['MESSAGE'] = $obUser->LAST_ERROR;
                $arResult['STATUS'] = 'error';
            }
        }
        return $arResult;
    }

    public function executeComponent()
    {
        try {
            $this->arResult = $this->getResult();
            $this->includeComponentTemplate();
        } catch (Exception $e) {
            ShowError($e->getMessage());
        }
    }
}
