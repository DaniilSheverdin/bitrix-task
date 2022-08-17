<?php


use CFile;
use CPHPCache;
use CUserOptions;
use Bitrix\Main\IO;
use CIBlockElement;
use CIBlockSection;
use CBitrixComponent;
use Bitrix\Main\Loader;
use Bitrix\Main\Web\Json;
use Bitrix\DocumentGenerator;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Engine\ActionFilter;
use Citto\Instructions\Component as MainComponent;
use Bitrix\DocumentGenerator\DataProvider\ArrayDataProvider;
use CIntranetUtils;


if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

require $_SERVER["DOCUMENT_ROOT"] . "/local/vendor/autoload.php";
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

if (Loader::includeModule("nkhost.phpexcel")) {
    global $PHPEXCELPATH;
    require_once($PHPEXCELPATH . '/PHPExcel/IOFactory.php');
}

Loader::includeModule('citto.filesigner');

class AjaxController extends Controller
{
    public function configureActions(): array
    {
        $arParams = [
            'prefilters' => [
                new ActionFilter\Authentication(),
                new ActionFilter\HttpMethod(
                    [ActionFilter\HttpMethod::METHOD_POST]
                ),
                new ActionFilter\Csrf(),
            ],
            'postfilters' => []
        ];

        return [
            'getTest' => $arParams,
        ];
    }

    public function getTestAction($iFileSignID, $iCardID)
    {
        global $USER;

        $iUserID = $USER->getID();

        $obPDF = new \Citto\Filesigner\PDFile();
        $obPDF->init($iFileSignID);
        $obPDF->insert(date('d.m.Y'), "#DATE_$iUserID#");
        $obPDF->save();

        $arFile = \CFile::MakeFileArray($iFileSignID);
        $arFile['DESCRIPTION'] = '';

        $arSelect = ["ID", "PROPERTY_UF_SIGNED_USERS"];
        $obCard = CIBlockElement::GetList([], ["ID" => $iCardID], false, [], $arSelect);
        $arUsersSigned = $obCard->GetNext()['PROPERTY_UF_SIGNED_USERS_VALUE'];

        if (!in_array($iUserID, $arUsersSigned)) {
            array_push($arUsersSigned, $iUserID);
        }

        $arUpdate = [
            'UF_DOCUMENT_SIGN' => $arFile,
            'UF_SIGNED_USERS' => $arUsersSigned
        ];

        CIBlockElement::SetPropertyValuesEx($iCardID, 0, $arUpdate);

        return $iFileSignID;
    }
}
