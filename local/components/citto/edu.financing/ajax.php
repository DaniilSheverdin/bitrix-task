<?php

namespace Citto\Edu\Financing;

use CFile;
use CPHPCache;
use Dompdf\Dompdf;
use Exception;
use CUserOptions;
use Bitrix\Main\IO;
use CIBlockElement;
use CIBlockSection;
use CBitrixComponent;
use RuntimeException;
use Bitrix\Main\Loader;
use Bitrix\Main\Web\Json;
use Citto\Filesigner\Signer;
use Bitrix\DocumentGenerator;
use Citto\ControlOrders\Notify;
use Citto\ControlOrders\Orders;
use Citto\ControlOrders\Settings;
use Citto\Controlorders\Executors;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Engine\ActionFilter;
use Sprint\Migration\Helpers\HlblockHelper;
use Bitrix\Main\Grid\Options as GridOptions;
use Bitrix\Highloadblock\HighloadBlockTable as HLTable;
use Citto\Edu\Financing\Component as MainComponent;
use Bitrix\DocumentGenerator\DataProvider\ArrayDataProvider;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

require $_SERVER["DOCUMENT_ROOT"] . "/local/vendor/autoload.php";

Loader::includeModule('citto.filesigner');

class AjaxController extends Controller
{
    /**
     * Конфигурация действий
     *
     * @return array
     */
    public function configureActions(): array
    {
        $arParams = [
            'prefilters' => [
                new ActionFilter\Authentication(),
                new ActionFilter\HttpMethod(
                    [ActionFilter\HttpMethod::METHOD_POST]
                ),
                new ActionFilter\ContentType(
                    [ActionFilter\ContentType::JSON]
                ),
                new ActionFilter\Csrf(),
            ],
            'postfilters' => []
        ];

        return [
            'sendToNew'      => $arParams,
            'sendToReject'   => $arParams,
            'sendToRepeat'   => $arParams,
            'sendToSuccess'  => $arParams,
            'makePDF'        => $arParams,
        ];
    }

    public function sendToNewAction(int $id = 0)
    {
        CBitrixComponent::includeComponentClass('citto:edu.financing');
        $obComponent = new MainComponent();

        $arEnums = $obComponent->getEnums();

        $strStatusProcess = json_encode(['STATUS' => $arEnums['STATUS']['PROCESS']['VALUE'], 'COLOR' => 'grey'], JSON_UNESCAPED_UNICODE);

        $arUpdate = [
            'STATUS'    => $arEnums['STATUS']['NEW']['ID'],
            'DATE'      => date('d.m.Y'),
            'KURATOR'   => $strStatusProcess,
            'TEHNADZOR' => $strStatusProcess,
        ];
        CIBlockElement::SetPropertyValuesEx($id, 0, $arUpdate);
    }

    public function sendToRejectAction(int $id = 0)
    {

        global $USER;

        CBitrixComponent::includeComponentClass('citto:edu.financing');
        $obComponent = new MainComponent();

        $arDetail = $obComponent->getById($id);
        $arElement = $arDetail['raw'];

        $arNumber = $obComponent->generateNumber($id);

        $arEnums = $obComponent->getEnums();
        $arFields = $obComponent->getDefaultFilter();
        $arFields['NAME'] = $arElement['NAME'];

        $arRole = $obComponent->getRoles();
        $strRole = null;
        $bIsCanMakeNextVersion = false;
        $bIsCanRejected = true;

        foreach ($arRole as $strRoleKey => $value) {
            if ($value) {
                $strRole = $strRoleKey;
            }
        }

        $strStatusK = json_decode(htmlspecialcharsback($arElement['PROPERTY_KURATOR_VALUE']), true)['STATUS'];
        $strStatusT = json_decode(htmlspecialcharsback($arElement['PROPERTY_TEHNADZOR_VALUE']), true)['STATUS'];


        $bIsCanMakeNextVersion = $strStatusK == $arEnums['STATUS']['AGREED']['VALUE'] ||
                                 $strStatusT == $arEnums['STATUS']['AGREED']['VALUE'];

        $bIsCanRejected        = $strStatusK == $arEnums['STATUS']['PROCESS']['VALUE'] ||
                                 $strStatusT == $arEnums['STATUS']['PROCESS']['VALUE'];



        if ($arRole['FINANCE'] && $bIsCanMakeNextVersion) {

            $el = new CIBlockElement();
            $arFields['PROPERTY_VALUES'] = [
                'PROGRAM'       => $arElement['PROPERTY_PROGRAM_VALUE'],
                'EVENT'         => $arElement['PROPERTY_EVENT_VALUE'],
                'MUNICIPALITY'  => $arElement['PROPERTY_MUNICIPALITY_VALUE'],
                'ORGAN'         => $arElement['PROPERTY_ORGAN_VALUE'],
                'ADDRESS'       => $arElement['PROPERTY_ADDRESS_VALUE'],
                'AMOUNT'        => $arElement['PROPERTY_AMOUNT_VALUE'],
                'FILES'         => $arElement['PROPERTY_FILES_VALUE'] ?? [],
                'FILES_DESC'    => $arElement['PROPERTY_FILES_DESC_VALUE'] ?? [],
                'YEAR'          => $arNumber['YEAR'],
                'NUMBER'        => $arNumber['NUMBER'],
                'VERSION'       => $arNumber['VERSION'],
                'STATUS'        => $arEnums['STATUS']['DRAFT']['ID'],
            ];

            if (!$elID = $el->Add($arFields)) {
                throw new Exception($el->LAST_ERROR);
            }

            $el->Update($id, ['ACTIVE' => 'N']);

            $arResultRole = json_encode(['STATUS' => 'Отказ', 'DATE' => date('d.m.Y H:i'), 'FIO' => $USER->GetFullName(), 'COLOR' => 'red'], JSON_UNESCAPED_UNICODE);

            CIBlockElement::SetPropertyValuesEx($id, 0, ['STATUS' => $arEnums['STATUS']['REJECT']['ID']]);
            CIBlockElement::SetPropertyValuesEx($id, 0, [$strRole => $arResultRole]);

            $arDetail = $obComponent->getById($elID);

            return ['returnUrl' => $arDetail['raw']['~DETAIL_PAGE_URL'], 'success' => 'ok'];


        } else {




            $arResultRole = json_encode(['STATUS' => 'Отказ', 'DATE' => date('d.m.Y H:i'), 'FIO' => $USER->GetFullName(), 'COLOR' => 'red'], JSON_UNESCAPED_UNICODE);


            if ($arRole['FINANCE']) {

                if (!$bIsCanRejected) {
                    CIBlockElement::SetPropertyValuesEx($id, 0, ['STATUS' => $arEnums['STATUS']['REJECT']['ID']]);
                } else {
                    CIBlockElement::SetPropertyValuesEx($id, 0, ['STATUS' => $arEnums['STATUS']['PROCESS']['ID']]);
                }
            } else {
                CIBlockElement::SetPropertyValuesEx($id, 0, ['STATUS' => $arEnums['STATUS']['PROCESS']['ID']]);
            }


            CIBlockElement::SetPropertyValuesEx($id, 0, [$strRole => $arResultRole]);

            $strOtherRole = 'KURATOR';
            if ($strRole == 'KURATOR') {
                $strOtherRole = 'TEHNADZOR';
            }

            if(json_decode(htmlspecialcharsback($arElement['PROPERTY_'.$strOtherRole.'_VALUE']), true)['STATUS'] !== $arEnums['STATUS']['PROCESS']['VALUE']) {
                CIBlockElement::SetPropertyValuesEx($id, 0, ['FINANCE' => json_encode(['STATUS' => $arEnums['STATUS']['PROCESS']['VALUE'], 'COLOR' => 'grey'])]);
            }



            $arDetail = $obComponent->getById($id);

            return ['returnUrl' => $arDetail['raw']['~DETAIL_PAGE_URL'], 'success' => 'ok'];

        }


    }



    public function sendToRepeatAction(string $kurator, string $tech, int $id = 0)
    {


        CBitrixComponent::includeComponentClass('citto:edu.financing');
        $obComponent = new MainComponent();


        $arDetail = $obComponent->getById($id);
        $arElement = $arDetail['raw'];


        $arFields = $obComponent->getDefaultFilter();
        $arFields['NAME'] = $arElement['NAME'];



        $arEnums = $obComponent->getEnums();
        $arRole = $obComponent->getRoles();
        $arNumber = $obComponent->generateNumber($id);



        if ($arRole['FINANCE']) {



            $arResultRole = json_encode(['STATUS' => $arEnums['STATUS']['PROCESS']['VALUE'], 'COLOR' => 'red'], JSON_UNESCAPED_UNICODE );



            $el = new CIBlockElement();
            $arFields['PROPERTY_VALUES'] = [
                'PROGRAM'       => $arElement['PROPERTY_PROGRAM_VALUE'],
                'EVENT'         => $arElement['PROPERTY_EVENT_VALUE'],
                'MUNICIPALITY'  => $arElement['PROPERTY_MUNICIPALITY_VALUE'],
                'ORGAN'         => $arElement['PROPERTY_ORGAN_VALUE'],
                'ADDRESS'       => $arElement['PROPERTY_ADDRESS_VALUE'],
                'AMOUNT'        => $arElement['PROPERTY_AMOUNT_VALUE'],
                'FILES'         => $arElement['PROPERTY_FILES_VALUE'] ?? [],
                'FILES_DESC'    => $arElement['PROPERTY_FILES_DESC_VALUE'] ?? [],
                'YEAR'          => $arNumber['YEAR'],
                'NUMBER'        => $arNumber['NUMBER'],
                'VERSION'       => $arNumber['VERSION'],
                'FINANCE'       => $arEnums['PROPERTY_FINANCE_VALUE'],
                'STATUS'        => $arEnums['STATUS']['DRAFT']['ID'],
            ];

            if ($kurator == 'Y') {

                $arFields['PROPERTY_VALUES']['KURATOR'] = $arResultRole;

            }
            if ($tech == 'Y') {

                $arFields['PROPERTY_VALUES']['TEHNADZOR'] = $arResultRole;

            }

            if (!$elID = $el->Add($arFields)) {
                throw new Exception($el->LAST_ERROR);
            }

            $el->Update($id, ['ACTIVE' => 'N']);


            CIBlockElement::SetPropertyValuesEx($id, 0, ['STATUS' => $arEnums['STATUS']['REJECT']['ID']]);

            $arDetail = $obComponent->getById($elID);
            return $arDetail['raw']['~DETAIL_PAGE_URL'];



        }

        return 'Нет прав';

    }


    public function sendToSuccessAction(int $id = 0)
    {

        global $USER;
        CBitrixComponent::includeComponentClass('citto:edu.financing');
        $obComponent = new MainComponent();

        $arEnums = $obComponent->getEnums();
        $arRole = $obComponent->getRoles();
        $strRole = null;

        $arDetail = $obComponent->getById($id);
        $arElement = $arDetail['raw'];


        foreach ($arRole as $strRoleKey => $value) {
            if ($value) {
                $strRole = $strRoleKey;
            }
        }

        $arResultRole = json_encode([
            'STATUS' => $arEnums['STATUS']['AGREED']['VALUE'],
            'DATE' => date('d.m.Y H:i'),
            'FIO' => $USER->GetFullName(),
            'COLOR' => 'green'],
            JSON_UNESCAPED_UNICODE
        );




        CIBlockElement::SetPropertyValuesEx($id, 0, ['STATUS' => $arEnums['STATUS']['PROCESS']['ID']]);
        CIBlockElement::SetPropertyValuesEx($id, 0, [$strRole => $arResultRole]);

        if ($strRole != 'FINANCE') {
            $strOtherRole = 'KURATOR';
            if ($strRole == 'KURATOR') {
                $strOtherRole = 'TEHNADZOR';
            }

            if(json_decode(htmlspecialcharsback($arElement['PROPERTY_'.$strOtherRole.'_VALUE']), true)['STATUS'] !== $arEnums['STATUS']['PROCESS']['VALUE']) {
                CIBlockElement::SetPropertyValuesEx($id, 0, ['FINANCE' => json_encode(['STATUS' => $arEnums['STATUS']['PROCESS']['VALUE'], 'COLOR' => 'grey'])]);
            }
        }

        $arDetail = $obComponent->getById($id);
        return ['returnUrl' => $arDetail['raw']['~DETAIL_PAGE_URL'], 'success' => 'ok'];

    }





    public function makePDFAction($id, $html, $type)
    {

        global $USER;

        CBitrixComponent::includeComponentClass('citto:edu.financing');
        $obComponent = new MainComponent();

        $arDetail = $obComponent->getById($id);
        $arElement = $arDetail['raw'];



        $resp = (object)['status'=>"ERROR", 'status_message'=>"", 'data'=>(object)[]];

        $pdfile1 = new \Citto\Filesigner\PDFile();
        $pdfile1->setClearf(['#PODPIS1#']);
        $pdfile1->setName($type.'-'.date('Y-m-dTH:i:s').'-'.$USER->GetID());
        $pdfile1->insert($html);
        $pdfile1->save();


        $src = '/podpis-fayla/?'.http_build_query([
                'FILES' => [$pdfile1->getId()],
                'POS'   => "#PODPIS1#",
                'CLEARF'=> ['#PODPIS1#', '#PODPIS2#'],
                'sessid'=> bitrix_sessid()
            ]);

        $resp->data->location   = $src;
        $resp->data->id         = $pdfile1->getId();


        $arElement['PROPERTY_FILES_VALUE'][] = $pdfile1->getId();
        CIBlockElement::SetPropertyValuesEx($id, false, ['FILES' =>  $arElement['PROPERTY_FILES_VALUE']]);


        return $resp;


    }
}
