<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}
use Bitrix\Highloadblock as HL;
use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\Engine\Contract\Controllerable;

CModule::IncludeModule('iblock');
global $APPLICATION;
$APPLICATION->SetAdditionalCSS('/bitrix/templates/.default/bootstrap.min.css');

class GeoPosition extends CBitrixComponent implements Controllerable
{
    public function configureActions()
    {
        return [
            'GetAdress' => [
                'prefilters'  => [
                    new ActionFilter\Authentication(),
                    new ActionFilter\HttpMethod(
                        array(ActionFilter\HttpMethod::METHOD_GET, ActionFilter\HttpMethod::METHOD_POST)
                    ),
                ],
                'postfilters' => [],
            ]
        ];
    }

    public function file_get_contents_curl($url, $header = array())
    {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //Устанавливаем параметр, чтобы curl возвращал данные, вместо того, чтобы выводить их в браузер.
        if (count($header)>0) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }
        curl_setopt($ch, CURLOPT_URL, $url);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }

    public function GetAdressAction($lon, $lat)
    {
        $sData=$this->file_get_contents_curl(
            'https://suggestions.dadata.ru/suggestions/api/4_1/rs/geolocate/address?lat='.$lat.'&lon='.$lon,
            array('Authorization: Token 697e4e53b055f8cbb596f79570f2cbfd118a4a68')
        );
        $arData=json_decode($sData, true);
        return array('Adress'=>$arData);
    }

    public function onPrepareComponentParams($arParams)
    {
        $arParams["CACHE_TIME"] = (int) $arParams["CACHE_TIME"];

        return $arParams;
    }

    public function UserGetFullName($sUserId)
    {
        $rsUser = CUser::GetByID($sUserId);
        $arUser = $rsUser->Fetch();
        return $arUser['LAST_NAME'] . ' ' . $arUser['NAME'].' '.$arUser['SECOND_NAME'];
    }

    /**
     * Get results
     *
     * @return array
     */
    protected function getResult()
    {
        global $USER;
        if ($_REQUEST['submit']!='') {
            $el = new CIBlockElement();
            $arLoadProductArray = array(
                "MODIFIED_BY"       => $USER->GetID(),
                "IBLOCK_SECTION_ID" => false,
                "IBLOCK_ID"         => $this->arParams['IBLOCK_ID'],
                "PROPERTY_VALUES"   => $_REQUEST,
                "NAME"              => $_REQUEST['USER'] . '-' . $_REQUEST['DATE'],
                "ACTIVE"            => "Y", // активен
            );
            $now = new DateTime();
            $now->setTimestamp(strtotime($_REQUEST['DATE']));
            $arFilter = array(
                "IBLOCK_ID"         => IntVal($this->arParams['IBLOCK_ID']),
                "PROPERTY_USER"     =>$USER->GetID(),
                ">=PROPERTY_DATE"   => $now->modify('-1 day')->format('Y-m-d'). " 23:59:59",
                "<=PROPERTY_DATE"   => ConvertDateTime($_REQUEST['DATE'], "YYYY-MM-DD"). " 23:59:59",
                "ACTIVE"            => "Y",
            );
            $res = CIBlockElement::GetList(array(), $arFilter, false, false, ['ID']);
            if ($arFields = $res->GetNext()) {
                unset($arLoadProductArray['PROPERTY_VALUES']);
                if ($el->Update($arFields['ID'], $arLoadProductArray)) {
                    CIBlockElement::SetPropertyValuesEx($arFields['ID'], $this->arParams['IBLOCK_ID'], $_REQUEST);
                    $arResult['MESSAGE']='Данные обновлены!';
                }
            } else {
                if ($PRODUCT_ID = $el->Add($arLoadProductArray)) {
                    $arResult['MESSAGE'] = 'Данные добавлены!';
                }
            }
        }

        $rsUser = CUser::GetByID($USER->GetID());
        $arResult['USER'] = $rsUser->Fetch();
        $arSelect = array("ID", "NAME", "DATE_ACTIVE_FROM", "PROPERTY_DATE", "PROPERTY_USER", "PROPERTY_ADRESS");
        $arFilter = array("IBLOCK_ID" => IntVal($this->arParams['IBLOCK_ID']),"PROPERTY_USER"=>$USER->GetID(), "ACTIVE_DATE" => "Y", "ACTIVE" => "Y");
        $arSort = [
            'PROPERTY_DATE' => 'DESC',
        ];
        $res      = CIBlockElement::GetList($arSort, $arFilter, false, false, $arSelect);
        while ($arFields = $res->GetNext()) {
            $arFields['USER_FULLNAME']=$this->UserGetFullName($arFields['PROPERTY_USER_VALUE']);
            $arResult['TIMES'][]=$arFields;
        }

        $arResult['DATE']=date('d.m.Y');
        $arFilter = array('IBLOCK_ID'=>5,'UF_HEAD' => $USER->GetID());
        $rsParentSection = \CIBlockSection::GetList(array('left_margin' => 'asc'), $arFilter);
        if ($arParentSection = $rsParentSection->GetNext()) {
            $arSectionIds[]=$arParentSection['ID'];
            $arFilter = array('IBLOCK_ID' => $arParentSection['IBLOCK_ID'],'>LEFT_MARGIN' => $arParentSection['LEFT_MARGIN'],'<RIGHT_MARGIN' => $arParentSection['RIGHT_MARGIN'],'>DEPTH_LEVEL' => $arParentSection['DEPTH_LEVEL']);
            $rsSect = \CIBlockSection::GetList(array('left_margin' => 'asc'), $arFilter);
            while ($arSect = $rsSect->GetNext()) {
                   $arSectionIds[]=$arSect['ID'];
                   $arParentSection['CHILDS'][$arSect['ID']]=$arSect;
            }
            $arResult['SECTION']=$arParentSection;
            $arResult['USERS_SECTION']=[];

            $filter = array('UF_DEPARTMENT'=>$arSectionIds,'ACTIVE'=>'Y');
            $rsUsers = \CUser::GetList(($by = "NAME"), ($order = "desc"), $filter, array("SELECT"=>array("UF_*")));
            while ($arUser = $rsUsers->Fetch()) {
                $arUser['USER_FULLNAME']=$this->UserGetFullName($arUser['ID']);
                foreach ($arUser['UF_DEPARTMENT'] as $key => $value) {
                    $arResult['USERS_SECTION'][$value][]=$arUser;
                }
            }
            $now = new DateTime();
            $now->setTimestamp(strtotime($arResult['DATE']));
            $arSelect = array("ID", "NAME", "DATE_ACTIVE_FROM", "PROPERTY_DATE", "PROPERTY_USER", "PROPERTY_ADRESS");
            $arFilter = array(
                "IBLOCK_ID" => IntVal($this->arParams['IBLOCK_ID']),
                ">=PROPERTY_DATE"  => $now->modify('-1 day')->format('Y-m-d'). " 23:59:59",
                "<=PROPERTY_DATE"  => ConvertDateTime($arResult['DATE'], "YYYY-MM-DD"). " 23:59:59",
                "ACTIVE_DATE" => "Y",
                "ACTIVE" => "Y"
            );

            $res      = CIBlockElement::GetList(array(), $arFilter, false, false, $arSelect);
            while ($arFields = $res->GetNext()) {
                $arResult['LIST_TIMES'][$arFields['PROPERTY_USER_VALUE']]=$arFields;
            }
        }

        if ($_REQUEST['action']=='list') {
            if ($_REQUEST['date']) {
                $arResult['DATE']=$_REQUEST['date'];
            } else {
                $arResult['DATE']=date('d.m.Y');
            }
            $arToFull=[
                2136,
                82,
                100,
                2230
            ];
            $arSectionIds=[];
            $arSectionIds[]=79;
            foreach ($arToFull as $sKey => $iSection) {
                $arSectionIds[]=$iSection;
            }
            $rsParentSection = \CIBlockSection::GetByID(79);
            if ($arParentSection = $rsParentSection->GetNext()) {
                $arFilter = array('IBLOCK_ID' => $arParentSection['IBLOCK_ID'],'>LEFT_MARGIN' => $arParentSection['LEFT_MARGIN'],'<RIGHT_MARGIN' => $arParentSection['RIGHT_MARGIN'],'>DEPTH_LEVEL' => $arParentSection['DEPTH_LEVEL']);
                $rsSect = \CIBlockSection::GetList(array('left_margin' => 'asc'), $arFilter);
                while ($arSect = $rsSect->GetNext()) {
                    $arSectionIds[]=$arSect['ID'];
                    $arParentSection['CHILDS'][$arSect['ID']]=$arSect;
                }
            }
            foreach ($arToFull as $sKey => $iSection) {
                $rsSection = \CIBlockSection::GetByID($iSection);
                if ($arSection = $rsSection->GetNext()) {
                    $arParentSection['CHILDS'][$arSection['ID']]=$arSection;
                }
            }

            $arResult['SECTION']=$arParentSection;
            $arResult['USERS_SECTION']=[];

            $filter = array('UF_DEPARTMENT'=>$arSectionIds,'ACTIVE'=>'Y');
            $rsUsers = \CUser::GetList(($by = "NAME"), ($order = "desc"), $filter, array("SELECT"=>array("UF_*")));
            while ($arUser = $rsUsers->Fetch()) {
                $arUser['USER_FULLNAME']=$this->UserGetFullName($arUser['ID']);
                foreach ($arUser['UF_DEPARTMENT'] as $key => $value) {
                    $arResult['USERS_SECTION'][$value][]=$arUser;
                }
            }

            $now = new DateTime();
            $now->setTimestamp(strtotime($arResult['DATE']));
            $arSelect = array("ID", "NAME", "DATE_ACTIVE_FROM", "PROPERTY_DATE", "PROPERTY_USER", "PROPERTY_ADRESS");
            $arFilter = array(
                "IBLOCK_ID" => IntVal($this->arParams['IBLOCK_ID']),
                ">=PROPERTY_DATE"  => $now->modify('-1 day')->format('Y-m-d'). " 23:59:59",
                "<=PROPERTY_DATE"  => ConvertDateTime($arResult['DATE'], "YYYY-MM-DD"). " 23:59:59",
                "ACTIVE_DATE" => "Y",
                "ACTIVE" => "Y"
            );

            $res = CIBlockElement::GetList(array(), $arFilter, false, false, $arSelect);
            while ($arFields = $res->GetNext()) {
                $arResult['LIST_TIMES'][$arFields['PROPERTY_USER_VALUE']]=$arFields;
            }
        }

        return $arResult;
    }

    /**
     * Set cache tag from params
     */
    public function setCacheTag()
    {
        if (defined('BX_COMP_MANAGED_CACHE') && !empty($this->arParams['CACHE_TAGS']) && is_object($GLOBALS['CACHE_MANAGER'])) {
            foreach ($this->arParams['CACHE_TAGS'] as $tag) {
                $GLOBALS['CACHE_MANAGER']->RegisterTag($tag);
            }
        }
    }

    public function executeComponent()
    {
        try {
            $this->setFields();

            if ($this->StartResultCache($this->arParams["CACHE_TIME"], $addCacheParams)) {
                $this->setCacheTag();
                $this->arResult = $this->getResult();
                $this->includeComponentTemplate();
            }
        } catch (Exception $e) {
            ShowError($e->getMessage());
        }
    }

    /**
     * Set fields values
     */
    private function setFields()
    {
    }
}
