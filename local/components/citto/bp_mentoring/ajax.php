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
            'getFile' => $arParams,
        ];
    }

    public function getFileAction($iFileID)
    {
        $arFile = CFile::GetFileArray($iFileID);
        $sContent = 'data:' . $arFile['CONTENT_TYPE'] . ';base64,' . base64_encode(file_get_contents($_SERVER['DOCUMENT_ROOT'] . $arFile['SRC']));
        $arData = [
            'CONTENT' => $sContent,
            'NAME' => $arFile['ORIGINAL_NAME']
        ];

        return $arData;
    }
}
