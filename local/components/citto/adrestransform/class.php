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

class AdresTransform extends \CBitrixComponent implements Controllerable
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

    public function file_get_contents_curl($url, $header = [])
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
            $sFileName=time();
            $strExtFile = pathinfo($_FILES['FILE']['name'])['extension'];
            switch ($strExtFile) {
                case 'csv':
                    move_uploaded_file($_FILES['FILE']['tmp_name'], $_SERVER['DOCUMENT_ROOT'].'/upload/adres/'.$sFileName.'.csv');
                    break;

                case 'xls':
                    $fileID = CFile::SaveFile($_FILES['FILE'], "xls2csv");

                    if (intval($fileID > 0)) {
                        $arFileXls = CFile::GetFileArray($fileID);
                        $fileNameCsv = str_replace('.xls', '.csv', $arFileXls['SRC']);
                        shell_exec('convertxls2csv -x "' . $_SERVER["DOCUMENT_ROOT"] . $arFileXls['SRC'] . '" -b WINDOWS-1251 -c "' . $_SERVER["DOCUMENT_ROOT"] . $fileNameCsv . '" -a UTF-8');
                        $arFileCsv = CFile::MakeFileArray($_SERVER["DOCUMENT_ROOT"] . $fileNameCsv);

                        copy($arFileCsv['tmp_name'], $_SERVER['DOCUMENT_ROOT'].'/upload/adres/'.$sFileName.'.csv');
                    }

                    break;

                case 'xlsx':
                    $fileID = CFile::SaveFile($_FILES['FILE'], "xlsx2csv");

                    if (intval($fileID > 0)) {
                        $arFileXls = CFile::GetFileArray($fileID);
                        $fileNameCsv = str_replace('.xlsx', '.csv', $arFileXls['SRC']);
                        shell_exec('xlsx2csv -d "," -f "%d.%m.%Y %H:%M" "' . $_SERVER["DOCUMENT_ROOT"] . $arFileXls['SRC'] . '" "' . $_SERVER["DOCUMENT_ROOT"] . $fileNameCsv.'"');
                        $arFileCsv = CFile::MakeFileArray($_SERVER["DOCUMENT_ROOT"] . $fileNameCsv);
                        copy($arFileCsv['tmp_name'], $_SERVER['DOCUMENT_ROOT'].'/upload/adres/'.$sFileName.'.csv');
                    }
                    break;
            }

            if (file_exists($_SERVER['DOCUMENT_ROOT'].'/upload/adres/'.$sFileName.'.csv')) {
               shell_exec('/usr/bin/php72 -f '.realpath(dirname(__FILE__)).'/shell.php '.$sFileName.' &');
               localredirect('?status=ok&file='.$sFileName);
            } else {
                echo "Ошибка загрузки файла\n";
            }
        }

        $arFiles = scandir($_SERVER['DOCUMENT_ROOT'].'/upload/adres/');
        //pre($arFiles);
        $arResult['FILES']=[];
        foreach ($arFiles as $sKey => $sValue) {
            $sValue=explode('.', $sValue);
            if (array_pop($sValue)=='csv') {
                $FileData=explode('_', $sValue[0]);
                if ($FileData[1]=='result') {
                    $arResult['FILES'][$FileData[0]]['result']='/upload/adres/'.$FileData[0].'_result.csv';
                } elseif ($FileData[1]=='ok') {
                    $arResult['FILES'][$FileData[0]]['progress']='ok';
                } else {
                    $arResult['FILES'][$FileData[0]]['ish']='/upload/adres/'.$FileData[0].'.csv';
                }
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
