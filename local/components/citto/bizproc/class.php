<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}
require_once $_SERVER['DOCUMENT_ROOT'] . "/local/vendor/autoload.php";

use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Request;
use Bitrix\Main\Web\Json;
use Bitrix\Main\Context;
use Bitrix\Main\Page\Asset;

/**
 * Class CBizprocController
 */
class CBizprocController extends \CBitrixComponent
{
    private $_application;

    /**
     * @param $arParams
     * @return array
     */
    public function onPrepareComponentParams($arParams)
    {
        global $APPLICATION;
        $this->arParams = $arParams;
        $this->_application = $APPLICATION;

        return $arParams;
    }

    /**
     * @return mixed|void
     */
    public function executeComponent()
    {
        global $USER;
        try {
            $objRequest = Context::getCurrent()->getRequest();
            $this->arResult['REQUEST'] = array_merge($objRequest->getPostList()->toArray(), $objRequest->getQueryList()->toArray());
            
            $arHeaders = getallheaders();
            if (isset($arHeaders['HTTP_X_REQUESTED_WITH']) || $arHeaders['Content-Type'] == 'application/json;charset=utf-8') {
                $arHeaders['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
                $objRequest->getServer()->set('HTTP_X_REQUESTED_WITH', $arHeaders['HTTP_X_REQUESTED_WITH']);
                if (empty($_POST) && getenv("REQUEST_METHOD") == 'POST') {
                    $inputData = json_decode(file_get_contents('php://input'), true);
                    if (count($inputData) > 0) {
                        $this->arResult['REQUEST'] = $_REQUEST = $inputData;
                    } else {
                        throw new Exception("Не переданы исходные данные");
                    }
                }
            }

            if ($objRequest->isAjaxRequest()) {
                $this->_execAjax();
            } else {
                Asset::getInstance()->addJs("/local/components/citto/bizproc/js/fieldeventer.js");
                Asset::getInstance()->addCss("/local/components/citto/bizproc/css/bizproc_common.css");
                $this->includeComponentTemplate();
            }
        } catch (Exception $exception) {
            ShowError($exception->getMessage());
        }
    }

    /**
     * @throws \Bitrix\Main\ArgumentException
     */
    private function _execAjax()
    {
        $this->_application->RestartBuffer();

        $arResp = ['code' => "ERROR", 'message' => "Ошибка", 'ajaxid' => '', 'returncontent' => false];
        $this->arResult = array_merge($this->arResult, $arResp);

        ob_start();
        $this->includeComponentTemplate();
        $strContent = $this->arResult['returncontent'] ? ob_get_clean() : ob_clean();

        if (is_string($strContent)) {
            echo $strContent;
        } else {
            header('Content-Type: application/json');
            echo Json::encode($this->arResult);
        }
        exit;
    }

    public function getFieldsList($ID, $excludedFields = [], $arSpecialData = [])
    {
        $arFieldsList = [];
        $arOut = [];

        $obFieldsData = CIBlockProperty::GetList(['SORT' => 'ASC'], ['IBLOCK_ID' => $ID, 'ACTIVE' => 'Y']);

        while($arItem = $obFieldsData->Fetch()) {
            if(!in_array($arItem['CODE'], $excludedFields) &&
                $arItem['ACTIVE'] == 'Y') {
                $arOut[$arItem['CODE']]['label'] = $arItem['NAME'];
                $arOut[$arItem['CODE']]['idnum'] = $arItem['ID'];
                $arOut[$arItem['CODE']]['name'] = $arItem['CODE'];
                $arOut[$arItem['CODE']]['value'] = ($arSpecialData[$arItem['CODE']]['DEFAULT_VALUE']) ?? $arItem['DEFAULT_VALUE'];
                $arOut[$arItem['CODE']]['required'] = $arItem['IS_REQUIRED'] == 'Y';
                $arOut[$arItem['CODE']]['multiple'] = $arItem['MULTIPLE'] == 'Y';
                $arOut[$arItem['CODE']]['data_type'] = 'default';

                if($arItem['PROPERTY_TYPE'] == 'S') {
                    if($arItem['USER_TYPE'] == 'employee') {
                        $arOut[$arItem['CODE']]['type'] = 'user_select';
                        $arOut[$arItem['CODE']]['data_type'] = trim($arItem['USER_TYPE']);
                        $arOut[$arItem['CODE']]['id_html'] = trim('id_'.$arItem['CODE']);
                        $config = [
                            'valueInputName' => $arItem['CODE'],
                            'value' => ($arOut[$arItem['CODE']]['value']) ?? '',
                            'multiple' => ($arItem['MULTIPLE'] == 'Y'),
                            'required' => ($arItem['IS_REQUIRED'] == 'Y'),
                            'groups' => [['id' => 'author', 'name' => 'Автор']]
                        ];
                        $arOut[$arItem['CODE']]['config'] = htmlspecialcharsbx(Json::encode($config));
                    } elseif($arItem['USER_TYPE'] == 'Date') {
                        $arOut[$arItem['CODE']]['type'] = 'date';
                        $arOut[$arItem['CODE']]['data_type'] = trim($arItem['USER_TYPE']);
                    } else {
                        $arOut[$arItem['CODE']]['type'] = 'text';
                    }
                } elseif($arItem['PROPERTY_TYPE'] == 'N') {
                    $arOut[$arItem['CODE']]['type'] = 'number';
                }

                $arFieldsList[] = $arItem;
            }
        }

        return $arOut;
    }
}
