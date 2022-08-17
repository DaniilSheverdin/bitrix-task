<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Loader;
use Bitrix\Main\UI\Extension;
use Bitrix\Main\Data\Cache;
use Bitrix\Main\Grid\Options as GridOptions;
use Bitrix\Main\UI\PageNavigation;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Bitrix\Main\Engine\Contract\Controllerable;

use Bitrix\Im\Integration\Intranet\Department;

Loader::includeModule('citto.integration');
use Citto\Integration\Docx;

use Bitrix\Iblock\SectionTable;



Extension::load('ui.bootstrap4');
Extension::load("ui.buttons.icons");
Extension::load("ui.buttons");
Extension::load("ui.forms");
Extension::load("ui.notification");


/**
 * Класс для работы c заявками на вакцинацию
 *
 * Class CittoVacComponent
 */
class CittoVacComponent extends CBitrixComponent implements Controllerable
{
    /**
     * Конфигурация аякс запросов
     *
     * @return array
     */
    public function configureActions(): array
    {
        return [
          'writeData'                => ['prefilters' => []],
          'deleteWrite'              => ['prefilters' => []],
          'getFreeTimes'             => ['prefilters' => []],
          'getTable'                 => ['prefilters' => []],
        ];
    }

    /** @var string */
    public const GRID_ID_VACCINATION_DATA    = 'vaccination_data';

    /** @var integer */
    public const IBLOCK_ID_VACCINATION   = IBLOCK_ID_VACCINATION;

    /** @var integer */
    public const GROUP_ID_VACCINATION    = GROUP_ID_VACCINATION;


    /** @var array */
    private $arAvailablePages = [
      'index',
      'data',
      'write',
      'new',
    ];

    /** @var string */
    private $strCurrentPage;

    /** @var boolean */
    private $bAccessData                = false;


    /** @var array */
    private $arTranslitParams           =
      [
        "max_len" => "100",
        "change_case" => "L",
        "replace_space" => "_",
        "replace_other" => "_",
        "delete_repeat_replace" => "true",
        "use_google" => "false",
      ];

    private $sections = [];







    /**
     * @param string $strPage Смотрит в $_REQUEST['page'].
     * @throws Exception Если нет страницы в запросе.
     * @return void
     */
    public function init(string $strPage)
    {

        $this->arResult['ACCESS'] = $this->checkRules();





        switch ($strPage) {
            case 'index':

                $this->arResult['ISSET'] = $this->fetchDataByUser();
                break;

        }

        switch ($strPage) {
            case 'data':
                $this->makeGridData();
                break;


        }

        switch ($strPage) {
            case 'write':
                $this->arResult['ACCESS_TO_SECOND'] = $this->checkRulesForSecondWrite();
                break;


        }

        switch ($strPage) {
            case 'new':
                $this->arResult['ACCESS_TO_NEW'] = $this->checkRulesForNewWrite();
                break;


        }
    }

    public function checkRules() {

        $bRes = false;
        if (CSite::InGroup(array(self::GROUP_ID_VACCINATION))) {
            $bRes = true;
        }

        return $bRes;
    }




    public function getDepartment() {

        $parentSections = [];
        $arDepartment = [];

        //ГАУ ТО «Центр информационных технологий»  57
        //Аппарат ПТО                               1710
        //Правительство Тульской области            53
        global $USER;


        $parentSectionIterator = SectionTable::getList([
            'select' => [
                'SECTION_ID' => 'SECTION_SECTION.ID',
                'IBLOCK_SECTION_ID' => 'SECTION_SECTION.IBLOCK_SECTION_ID',
                'DEPTH_LEVEL' => 'SECTION_SECTION.DEPTH_LEVEL',
                'NAME' => 'SECTION_SECTION.NAME'
            ],
            'filter' => [
                '=ID' => CIntranetUtils::GetUserDepartments($USER->GetID())[0],
            ],
            'runtime' => [
                'SECTION_SECTION' => [
                    'data_type' => '\Bitrix\Iblock\SectionTable',
                    'reference' => [
                        '=this.IBLOCK_ID' => 'ref.IBLOCK_ID',
                        '>=this.LEFT_MARGIN' => 'ref.LEFT_MARGIN',
                        '<=this.RIGHT_MARGIN' => 'ref.RIGHT_MARGIN',
                    ],
                    'join_type' => 'inner'
                ],
            ],
        ]);

        while ($parentSection = $parentSectionIterator->fetch()) {
            $parentSections[$parentSection['SECTION_ID']] = $parentSection;

            if ($parentSection['SECTION_ID'] == 57) {
                $arDepartment = $parentSection;
                break;
            } elseif ($parentSection['SECTION_ID'] == 1710) {
                $arDepartment = $parentSections[53];
                break;
            } elseif ($parentSection['DEPTH_LEVEL'] == 3) {
                $arDepartment = $parentSection;
                break;
            }
        }

        pre($parentSections);

        return $arDepartment['NAME'];



    }






    public function makeGridData()
    {

        global $DB;

        //FILTER

        $this->arResult['UI_FILTER'] = [
            [
                'id' => 'ATT_VAC_DATE',
                'name' => 'Дата и время записи',
                'type'=>'date',
                'default' => true,
                "exclude" => array(
                    \Bitrix\Main\UI\Filter\DateType::LAST_7_DAYS,
                    \Bitrix\Main\UI\Filter\DateType::LAST_30_DAYS,
                    \Bitrix\Main\UI\Filter\DateType::LAST_60_DAYS,
                    \Bitrix\Main\UI\Filter\DateType::LAST_90_DAYS,
                    \Bitrix\Main\UI\Filter\DateType::CURRENT_MONTH,
                    \Bitrix\Main\UI\Filter\DateType::CURRENT_QUARTER,
                    \Bitrix\Main\UI\Filter\DateType::CURRENT_WEEK,
                    \Bitrix\Main\UI\Filter\DateType::LAST_MONTH,
                    \Bitrix\Main\UI\Filter\DateType::LAST_WEEK,
                    \Bitrix\Main\UI\Filter\DateType::MONTH,
                    \Bitrix\Main\UI\Filter\DateType::NEXT_MONTH,
                    \Bitrix\Main\UI\Filter\DateType::NEXT_DAYS,
                    \Bitrix\Main\UI\Filter\DateType::YEAR,
                    \Bitrix\Main\UI\Filter\DateType::NEXT_WEEK,
                    \Bitrix\Main\UI\Filter\DateType::TOMORROW,
                    \Bitrix\Main\UI\Filter\DateType::QUARTER,
                    \Bitrix\Main\UI\Filter\DateType::YESTERDAY,
                    \Bitrix\Main\UI\Filter\DateType::CURRENT_DAY,
                    \Bitrix\Main\UI\Filter\DateType::PREV_DAYS,
                )
            ],
        ];

        $this->arResult['FILTER_DATA'] = [];
        $filterOption = new Bitrix\Main\UI\Filter\Options(self::GRID_ID_VACCINATION_DATA);
        $filterData = $filterOption->getFilter([]);

        foreach ($filterData as $k => $v) {
            if ($k == 'ATT_VAC_DATE_from') {
                $dateFrom = $DB->FormatDate($v, "DD.MM.YYYY HH:MI:SS", "DD.MM.YYYY");
            }
            if ($k == 'ATT_VAC_DATE_to') {
                $dateTo = $DB->FormatDate($v, "DD.MM.YYYY HH:MI:SS", "DD.MM.YYYY");
            }

            if ($dateFrom && $dateTo && $DB->CompareDates($dateFrom, $dateTo) == 0) {
                $this->arResult['FILTER_DATA']['PROPERTY_ATT_VAC_DATE'] = date('Y-m-d', strtotime($dateFrom)). '%';
            } elseif ($dateFrom && $dateTo) {
                $this->arResult['FILTER_DATA']['>=PROPERTY_ATT_VAC_DATE'] = date('Y-m-d', strtotime($dateFrom)). '%';
                $this->arResult['FILTER_DATA']['<=PROPERTY_ATT_VAC_DATE'] = date('Y-m-d', strtotime($dateTo)). '%';
                unset($this->arResult['FILTER_DATA']['PROPERTY_ATT_VAC_DATE']);
            }

//            if ($k == 'FIND' && $v) {
//                $this->arResult['FILTER_DATA']['UF_FIO'] = '%'.$v.'%';
//            }
        }



        // GRID.
        $this->arResult['grid_id'] = self::GRID_ID_VACCINATION_DATA;

        $this->arResult['grid_options'] = new GridOptions($this->arResult['grid_id']);
        $this->arResult['sort'] = $this->arResult['grid_options']
            ->GetSorting(['sort' => ['DATE_CREATE' => 'DESC'], 'vars' => ['by' => 'by', 'order' => 'order']]);
        $this->arResult['nav_params'] = $this->arResult['grid_options']->GetNavParams();

        $this->arResult['nav'] = new PageNavigation($this->arResult['grid_id']);
        $this->arResult['nav']->allowAllRecords(true)
            ->setPageSize($this->arResult['nav_params']['nPageSize'])
            ->initFromUri();
        if ($this->arResult['nav']->allRecordsShown()) {
            $this->arResult['nav_params'] = false;
        } else {
            $this->arResult['nav_params']['iNumPage'] = $this->arResult['nav']->getCurrentPage();
        }

        $this->arResult['columns'] = [];
        $this->arResult['columns'][] = ['id' => 'ID', 'name' => 'ID', 'sort' => 'ID', 'default' => false];
        $this->arResult['columns'][] = [
            'id' => 'NAME', 'name' => 'ФИО', 'sort' => 'NAME', 'default' => true, 'editable' => false
        ];

        $this->arResult['columns'][] = [
            'id' => 'DATE_CREATE', 'name' => 'Дата создания', 'sort' => 'DATE_CREATE', 'default' => true, 'editable' => false
        ];

        $this->arResult['columns'][] = [
            'id' => 'ATT_BIRTHDAY_DATE',
            'name' => 'Дата рождения',
            'sort' => 'PROPERTY_ATT_BIRTHDAY_DATE',
            'default' => true,
            'editable' => false
        ];
        $this->arResult['columns'][] = [
            'id' => 'ATT_SNILS',
            'name' => 'СНИЛС',
            'sort' => 'PROPERTY_ATT_SNILS',
            'default' => true,
            'editable' => false,
        ];
        $this->arResult['columns'][] = [
            'id' => 'ATT_PHONE',
            'name' => 'Номер телефона',
            'sort' => 'PROPERTY_ATT_PHONE',
            'default' => true,
            'editable' => false
        ];

        $this->arResult['columns'][] = [
            'id' => 'ATT_DEPARTMENT',
            'name' => 'Подразделение',
            'sort' => 'PROPERTY_ATT_DEPARTMENT',
            'default' => true,
            'editable' => false
        ];

        $this->arResult['columns'][] = [
            'id' => 'ATT_PASSPORT_SN',
            'name' => 'Серия, номер',
            'sort' => 'PROPERTY_ATT_PASSPORT_SN',
            'default' => true,
            'editable' => false,
        ];

        $this->arResult['columns'][] = [
            'id' => 'ATT_PASSPORT_ISSUED_BY',
            'name' => 'Кем выдан',
            'sort' => 'PROPERTY_ATT_PASSPORT_ISSUED_BY',
            'default' => true,
            'editable' => false,
        ];
        $this->arResult['columns'][] = [
            'id' => 'ATT_PASSPORT_ISSUED_DATE',
            'name' => 'Дата выдачи',
            'sort' => 'PROPERTY_ATT_PASSPORT_ISSUED_DATE',
            'default' => true,
            'editable' => false,
        ];
        $this->arResult['columns'][] = [
            'id' => 'ATT_PASSPORT_CODE',
            'name' => 'Код подразделения',
            'sort' => 'PROPERTY_ATT_PASSPORT_CODE',
            'default' => true,
            'editable' => false,
        ];
        $this->arResult['columns'][] = [
            'id' => 'ATT_PASSPORT_ADDRESS',
            'name' => 'Адрес регистрации (по прописке)',
            'sort' => 'PROPERTY_ATT_PASSPORT_ADDRESS',
            'default' => true,
            'editable' => false,
        ];
        $this->arResult['columns'][] = [
            'id' => 'ATT_OMS_NUMBER',
            'name' => 'OMC номер',
            'sort' => 'PROPERTY_ATT_OMS_NUMBER',
            'default' => true,
            'editable' => false,
        ];
        $this->arResult['columns'][] = [
            'id' => 'ATT_OMS_SERVICE',
            'name' => 'OMC номер',
            'sort' => 'PROPERTY_ATT_OMS_SERVICE',
            'default' => true,
            'editable' => false,
        ];
        $this->arResult['columns'][] = [
            'id' => 'ATT_VAC_DATE',
            'name' => 'Дата и время приема',
            'sort' => 'PROPERTY_ATT_VAC_DATE',
            'default' => true,
            'editable' => false,
            'type' => 'date'
        ];





        $arSelectDataGrid = array(
            'ID',
            'IBLOCK_ID',
            'CODE',
            'NAME',
            'DATE_CREATE',
            'PROPERTY_ATT_VAC_DATE',
            'PROPERTY_ATT_BIRTHDAY_DATE',
            'PROPERTY_ATT_SNILS',
            'PROPERTY_ATT_PHONE',
            'PROPERTY_ATT_PASSPORT_SN',
            'PROPERTY_ATT_PASSPORT_ISSUED_BY',
            'PROPERTY_ATT_PASSPORT_ISSUED_DATE',
            'PROPERTY_ATT_PASSPORT_CODE',
            'PROPERTY_ATT_PASSPORT_ADDRESS',
            'PROPERTY_ATT_OMS_NUMBER',
            'PROPERTY_ATT_OMS_SERVICE',
            'PROPERTY_ATT_DEPARTMENT',
        );

        $arFilterDataGrid = array(
            "IBLOCK_ID" => self::IBLOCK_ID_VACCINATION,
            "ACTIVE" => "Y",
        );

        $arFilterDataGrid = array_merge($arFilterDataGrid, $this->arResult['FILTER_DATA']);


        $rsDataGrid = \CIBlockElement::GetList(
            $this->arResult['sort']['sort'],
            $arFilterDataGrid,
            false,
            $this->arResult['nav_params'],
            $arSelectDataGrid
        );

        $this->arResult['nav']->setRecordCount($rsDataGrid->selectedRowsCount());

        while ($arFieldsDataGrid = $rsDataGrid->GetNext()) {


            $this->arResult['list'][] = [
                'data' => [
                    "ID" => $arFieldsDataGrid['ID'],
                    "NAME" => $arFieldsDataGrid['NAME'],
                    "DATE_CREATE" => $arFieldsDataGrid['DATE_CREATE'],
                    "ATT_VAC_DATE" => $arFieldsDataGrid['PROPERTY_ATT_VAC_DATE_VALUE'],
                    "ATT_BIRTHDAY_DATE" => $arFieldsDataGrid['PROPERTY_ATT_BIRTHDAY_DATE_VALUE'],
                    "ATT_SNILS" => $arFieldsDataGrid['PROPERTY_ATT_SNILS_VALUE'],
                    "ATT_PHONE" => $arFieldsDataGrid['PROPERTY_ATT_PHONE_VALUE'],
                    "ATT_PASSPORT_SN" => $arFieldsDataGrid['PROPERTY_ATT_PASSPORT_SN_VALUE'],
                    "ATT_PASSPORT_ISSUED_BY" => $arFieldsDataGrid['PROPERTY_ATT_PASSPORT_ISSUED_BY_VALUE'],
                    "ATT_PASSPORT_ISSUED_DATE" => $arFieldsDataGrid['PROPERTY_ATT_PASSPORT_ISSUED_DATE_VALUE'],
                    "ATT_PASSPORT_CODE" => $arFieldsDataGrid['PROPERTY_ATT_PASSPORT_CODE_VALUE'],
                    "ATT_PASSPORT_ADDRESS" => $arFieldsDataGrid['PROPERTY_ATT_PASSPORT_ADDRESS_VALUE'],
                    "ATT_OMS_NUMBER" => $arFieldsDataGrid['PROPERTY_ATT_OMS_NUMBER_VALUE'],
                    "ATT_OMS_SERVICE" => $arFieldsDataGrid['PROPERTY_ATT_OMS_SERVICE_VALUE'],
                    "ATT_DEPARTMENT" => $arFieldsDataGrid['PROPERTY_ATT_DEPARTMENT_VALUE'],

                ], 'editable' => false,

            ];
        }



    }


    public function fetchDataByUser()
    {
        global $USER;

        $arRes = [];
        $bEditable = true;

        $arSelect = [
            'ID',
            'IBLOCK_ID',
            'CODE',
            'NAME',
            'PROPERTY_ATT_VAC_DATE',
            'PROPERTY_ATT_BIRTHDAY_DATE',
            'PROPERTY_ATT_SNILS',
            'PROPERTY_ATT_PHONE',
            'PROPERTY_ATT_PASSPORT_SN',
            'PROPERTY_ATT_PASSPORT_ISSUED_BY',
            'PROPERTY_ATT_PASSPORT_ISSUED_DATE',
            'PROPERTY_ATT_PASSPORT_CODE',
            'PROPERTY_ATT_PASSPORT_ADDRESS',
            'PROPERTY_ATT_OMS_NUMBER',
            'PROPERTY_ATT_OMS_SERVICE',
            'PROPERTY_ATT_FILES',
        ];
        $arFilter = ['IBLOCK_ID' => self::IBLOCK_ID_VACCINATION, 'ACTIVE' => 'Y', 'CREATED_USER_ID' => $USER->GetID()];
        $rs = CIBlockElement::GetList(['sort' => 'asc'], $arFilter, false, false, $arSelect);
        while($arFields = $rs->GetNext()) {


            $arRes[$arFields['ID']]['ID'] = $arFields['ID'];
            $arRes[$arFields['ID']]['NAME'] = $arFields['NAME'];
            $arRes[$arFields['ID']]['BIRTHDAY_DATE'] = $arFields['PROPERTY_ATT_BIRTHDAY_DATE_VALUE'];
            $arRes[$arFields['ID']]['SNILS'] = $arFields['PROPERTY_ATT_SNILS_VALUE'];
            $arRes[$arFields['ID']]['PHONE'] = $arFields['PROPERTY_ATT_PHONE_VALUE'];
            $arRes[$arFields['ID']]['PASSPORT_SN'] = $arFields['PROPERTY_ATT_PASSPORT_SN_VALUE'];
            $arRes[$arFields['ID']]['PASSPORT_ISSUED_BY'] = $arFields['PROPERTY_ATT_PASSPORT_ISSUED_BY_VALUE'];
            $arRes[$arFields['ID']]['PASSPORT_ISSUED_DATE'] = $arFields['PROPERTY_ATT_PASSPORT_ISSUED_DATE_VALUE'];
            $arRes[$arFields['ID']]['PASSPORT_CODE'] = $arFields['PROPERTY_ATT_PASSPORT_CODE_VALUE'];
            $arRes[$arFields['ID']]['PASSPORT_ADDRESS'] = $arFields['PROPERTY_ATT_PASSPORT_ADDRESS_VALUE'];
            $arRes[$arFields['ID']]['OMS_NUMBER'] = $arFields['PROPERTY_ATT_OMS_NUMBER_VALUE'];
            $arRes[$arFields['ID']]['OMS_SERVICE'] = $arFields['PROPERTY_ATT_OMS_SERVICE_VALUE'];
            $arRes[$arFields['ID']]['VAC_DATE'] = explode(' ', $arFields['PROPERTY_ATT_VAC_DATE_VALUE'])[0];
            $arRes[$arFields['ID']]['VAC_TIME'] = substr(explode(' ', $arFields['PROPERTY_ATT_VAC_DATE_VALUE'])[1], 0,5);
            foreach ($arFields['PROPERTY_ATT_FILES_VALUE'] as $fileID) {
              $arRes[$arFields['ID']]['FILES'][] = CFile::GetPath($fileID);
            }




        }
        if (count($arRes)) {
            $bEditable = false;
        }

        return ['DATA' => $arRes, 'EDITABLE' => $bEditable];

    }

    public function checkRulesForSecondWrite()
    {
        global $USER;

        $arSelect = ['ID', 'IBLOCK_ID'];
        $arFilter = ['IBLOCK_ID' => self::IBLOCK_ID_VACCINATION, 'ACTIVE' => 'Y', 'CREATED_USER_ID' => $USER->GetID()];
        $rs = CIBlockElement::GetList(['sort' => 'asc'], $arFilter, false, false, $arSelect);
        $intCountWrites = $rs->SelectedRowsCount();

        return $intCountWrites == 1;

    }


    public function checkRulesForNewWrite()
    {
        global $USER;

        $arSelect = ['ID', 'IBLOCK_ID'];
        $arFilter = ['IBLOCK_ID' => self::IBLOCK_ID_VACCINATION, 'ACTIVE' => 'Y', 'CREATED_USER_ID' => $USER->GetID()];
        $rs = CIBlockElement::GetList(['sort' => 'asc'], $arFilter, false, false, $arSelect);
        $intCountWrites = $rs->SelectedRowsCount();

        return $intCountWrites == 0;

    }


    public function writeDataAction($data)
    {

        $logger = new Logger('VACCINATION_ADD');
        $logger->pushHandler(new StreamHandler($_SERVER['DOCUMENT_ROOT'] . '/local/logs/vaccination/add.log', Logger::INFO));

        global $USER;
        global $DB;

        $strFIO = '';
        $arProps = [];
        $arRes = [];
        $strDate = '';
        $strTime = '';
        foreach ($data as $field) {
            $arField = json_decode($field, true);
            if ($arField['name'] == 'fio') {
                $strFIO = $arField['value'];
            } elseif ($arField['name'] == 'agree') {
                continue;
            } elseif ($arField['name'] == 'vac_date') {
                $strDate = $arField['value'];
            } elseif ($arField['name'] == 'time') {
                $strTime = $arField['value'];
            } else {
                $arProps['ATT_' . strtoupper($arField['name'])] = $arField['value'];
            }
        }

        $arProps['ATT_VAC_DATE'] = $strDate . ' ' . $strTime;

        $strCompareWriteDate = $arProps['ATT_VAC_DATE'] . ':00';

        $arProps['ATT_DEPARTMENT'] = $this->getDepartment();

        $strCode = CUtil::translit($strFIO . '_' . $arProps['ATT_PASSPORT_SN'] . '_' . $arProps['ATT_VAC_DATE'], 'ru', $this->arTranslitParams);

        $objWrite = new CIBlockElement();


        $arLoadWrite = array(
            'MODIFIED_BY'         => $USER->GetID(),
            'IBLOCK_SECTION_ID'   => false,
            'IBLOCK_ID'           => self::IBLOCK_ID_VACCINATION,
            'PROPERTY_VALUES'     => $arProps,
            'NAME'                => $strFIO,
            'ACTIVE'              => "Y",
            'CODE'                => $strCode
        );

        $bIssetTime = false;
        $arSelectCheck = ['ID', 'IBLOCK_ID', 'PROPERTY_ATT_VAC_DATE'];
        $arFilterCheck = ['IBLOCK_ID' => self::IBLOCK_ID_VACCINATION, 'ACTIVE' => 'Y' ];
        $rsCheck = CIBlockElement::GetList(['sort' => 'asc'], $arFilterCheck, false, false, $arSelectCheck);
        while($arFieldsCheck = $rsCheck->GetNext()) {

            $result = $DB->CompareDates($strCompareWriteDate, $arFieldsCheck['PROPERTY_ATT_VAC_DATE_VALUE']);
            if ($result == 0) {
                $bIssetTime = true;
            }

        }


        if ($bIssetTime) {
            $arRes['message'] = 'На это время уже записались, выберите другое время.';
            return $arRes;
        }



        if ($intNewWriteID = $objWrite->Add($arLoadWrite)) {
            $arRes['id'] = $intNewWriteID;
            $logger->info('ADD: ' . $intNewWriteID, ['$arLoadWrite' => $arLoadWrite]);

            $arDataFile = [
                'FIO' => $strFIO,
                'ATT_BIRTHDAY_DATE' => $arProps['ATT_BIRTHDAY_DATE'],
                'ATT_PASSPORT_ADDRESS' => $arProps['ATT_PASSPORT_ADDRESS'],
                'ATT_PHONE' => $arProps['ATT_PHONE'],
            ];

            $emailTo = $USER->GetEmail();

            $arDataEmail = [
                'EMAIL_TO' => $emailTo,
                'TIME' => explode(' ', $arProps['ATT_VAC_DATE'])[1],
                'DATE' => explode(' ', $arProps['ATT_VAC_DATE'])[0],
            ];

            $arFilesIDs = [];
            $arFiles = [];


            $arRes['files'][] = $this->generateFile($arDataFile, 'anketa_pacienta-' . $strCode, 'vacc_anketa');
            $arRes['files'][] = $this->generateFile($arDataFile, 'soglasie_pacienta_na_vakcinaciyu-' . $strCode, 'vacc_agree');

            $intCountFile = 0;
            foreach ($arRes['files'] as $file) {
                $arFilesIDs[] = $file['id'];
                $arFiles[$intCountFile]['VALUE'] = CFile::MakeFileArray($file['path']);
                $arFiles[$intCountFile]['DESCRIPTION'] = '';

                $intCountFile++;
            }


            CIBlockElement::SetPropertyValuesEx($intNewWriteID, self::IBLOCK_ID_VACCINATION, array('ATT_FILES' => $arFiles));

            $intResSendEmail = CEvent::Send("VACCINATION_CONFIRM", 's1', $arDataEmail, 'Y', '', $arFilesIDs);

            if ($intResSendEmail) {
                $logger->info('EMAIL_SEND: ' . $intResSendEmail, ['$arDataEmail' => $arDataEmail, '$arFilesIDs' => $arFilesIDs]);
            }





        } else {
            $arRes['error'] = $objWrite->LAST_ERROR;
            if ( $arRes['error'] == "Элемент с таким символьным кодом уже существует.<br>") {
                $logger->info('ERROR: ', ['$arLoadWrite' => $arLoadWrite]);

                $arSelect = ['ID', 'IBLOCK_ID', 'CODE', 'PROPERTY_ATT_VAC_DATE'];
                $arFilter = ['IBLOCK_ID' => self::IBLOCK_ID_VACCINATION, 'ACTIVE' => 'Y', 'CODE' => $strCode];
                $rs = CIBlockElement::GetList(['sort' => 'asc'], $arFilter, false, false, $arSelect);
                while($arFields = $rs->GetNext()) {
                    $arRes['isset_date'] = $arFields['PROPERTY_ATT_VAC_DATE_VALUE'];
                }

            }
        }


        return $arRes;
    }


    public function generateFile ($arData, $strFileName, $strTemplateName )
    {

        $logger = new Logger('VACCINATION_ADD');
        $logger->pushHandler(new StreamHandler($_SERVER['DOCUMENT_ROOT'] . '/local/logs/vaccination/add.log', Logger::INFO));

        $strTemplatePath = '/local/components/citto/vaccination_old/templates/main/';
        $strDirSave = 'docs';
        $strPathToSaveDir = $_SERVER['DOCUMENT_ROOT'] . $strTemplatePath . $strDirSave;

        $arDataDocx['FIO'] = $arData['FIO'];
        $arDataDocx['BIRTHDAY_DATE'] = $arData['ATT_BIRTHDAY_DATE'];
        $arDataDocx['PASSPORT_ADDRESS'] = $arData['ATT_PASSPORT_ADDRESS'];
        $arDataDocx['PHONE'] = $arData['ATT_PHONE'];


        $strDateNow = date('d-m-Y');

        $strFileNameDocx = $strFileName;

        if (!file_exists($strPathToSaveDir)) {
            mkdir($strPathToSaveDir, 0775, true);
        }

        if (CModule::IncludeModule("citto.integration")) {
            $pathDocx = Docx::generateDocument($strFileNameDocx, $arDataDocx, $strTemplateName);
        }

        $strRootPathDocx = $_SERVER['DOCUMENT_ROOT'] . $pathDocx;
        $arFileDocx = CFile::MakeFileArray($strRootPathDocx);
        $inrFileReportDocxID = CFile::SaveFile($arFileDocx, 'vaccination');
        $strNewFileDocxPath = CFile::GetPath($inrFileReportDocxID);

        $logger->info('GENERATE_FILE: ' . $inrFileReportDocxID, ['path' => $strNewFileDocxPath]);

        return ['path' => $strNewFileDocxPath, 'id' => $inrFileReportDocxID];

    }

    public function deleteWriteAction($id) {
        global $DB;
        global $USER;
        $arRes = [];
        $bIsOwner = false;

        $logger = new Logger('VACCINATION_DELETE');
        $logger->pushHandler(new StreamHandler($_SERVER['DOCUMENT_ROOT'] . '/local/logs/vaccination/delete.log', Logger::INFO));

        $rs = CIBlockElement::GetByID(intval($id));
        if ($arResult = $rs->GetNext()) {
            if ($arResult['CREATED_BY'] == $USER->GetID()) {
                $bIsOwner = true;
            }
        }

        if (CIBlock::GetPermission(self::IBLOCK_ID_VACCINATION) >= 'W' || $bIsOwner) {
            $DB->StartTransaction();
            if (!CIBlockElement::Delete(intval($id))) {
                $arRes['message'] = 'Ошибка удаления записи, недостаточно прав.';
                $arRes['success'] = false;

                $DB->Rollback();
            } else {
                $DB->Commit();

                $arRes['message'] = 'Запись удалена';
                $arRes['success'] = true;
            }
        }

        $logger->info('DELETE: ' . $id, ['$arRes' => $arRes]);

        return $arRes;
    }

    public function getFreeTimesAction($date)
    {

        $arRes = [];
        $intMinutesInterval = 5;
        $arDefaultDateTimeList = ['09:00'];
        $arDefaultRestDateTimeList = ['12:30'];
        if($date=='25.06.2021' || $date=='28.06.2021'){
            $arDefaultRestDateTimeList[] = '11:00';
        }
        if($date=='29.06.2021'){
            $arDefaultRestDateTimeList[] = '14:00';
        }
        $strStartTime = '09:00:00';
        $strEndTime = '14:55:00';
        $intHours = 6;
        $intCountIntervals = 60 / $intMinutesInterval * $intHours;
        $arIssetTimes = [];

        $strRestStartTime = '12:30:00';
        $intRestHours = 0.5;
        $intCountRestIntervals = 60 / $intMinutesInterval * $intRestHours;


        $oDateStart = date('Y-m-d H:i:s', strtotime($date. ' ' . $strStartTime));
        $oDateEnd = date('Y-m-d H:i:s', strtotime($date. ' ' . $strEndTime));

        $oDateRestStart = date('Y-m-d H:i:s', strtotime($date. ' ' . $strRestStartTime));


        $time = new DateTime($oDateStart);
        $timeRest = new DateTime($oDateRestStart);

        for ($i = 1; $i < $intCountIntervals; $i++) {
            $time->add(new DateInterval('PT' . $intMinutesInterval . 'M'));
            $arDefaultDateTimeList[] = $time->format('H:i');
        }

        for ($i = 1; $i < $intCountRestIntervals; $i++) {
            $timeRest->add(new DateInterval('PT' . $intMinutesInterval . 'M'));
            $arDefaultRestDateTimeList[] = $timeRest->format('H:i');
        }
        if($date=='25.06.2021' || $date=='28.06.2021'){
            $strRestStartTime = '11:00:00';
            $oDateRestStart = date('Y-m-d H:i:s', strtotime($date. ' ' . $strRestStartTime));
            $timeRest = new DateTime($oDateRestStart);
            $intRestHours = 1;
            $intCountRestIntervals = 60 / $intMinutesInterval * $intRestHours;
            for ($i = 1; $i < $intCountRestIntervals; $i++) {
                $timeRest->add(new DateInterval('PT' . $intMinutesInterval . 'M'));
                $arDefaultRestDateTimeList[] = $timeRest->format('H:i');
            }
        }

        if($date=='29.06.2021'){
            $strRestStartTime = '14:00:00';
            $oDateRestStart = date('Y-m-d H:i:s', strtotime($date. ' ' . $strRestStartTime));
            $timeRest = new DateTime($oDateRestStart);
            $intRestHours = 1;
            $intCountRestIntervals = 60 / $intMinutesInterval * $intRestHours;
            for ($i = 1; $i < $intCountRestIntervals; $i++) {
                $timeRest->add(new DateInterval('PT' . $intMinutesInterval . 'M'));
                $arDefaultRestDateTimeList[] = $timeRest->format('H:i');
            }
        }


        $arSelect = ['ID', 'IBLOCK_ID', 'PROPERTY_ATT_VAC_DATE'];
        $arFilter = [
            'IBLOCK_ID' => self::IBLOCK_ID_VACCINATION,
            'ACTIVE' => 'Y',
            '>=PROPERTY_ATT_VAC_DATE' => $oDateStart,
            '<=PROPERTY_ATT_VAC_DATE' => $oDateEnd,

        ];
        $rs = CIBlockElement::GetList(['sort' => 'asc'], $arFilter, false, false, $arSelect);
        while($arFields = $rs->GetNext()) {
            $arIssetTimes[] = substr(explode(' ', $arFields['PROPERTY_ATT_VAC_DATE_VALUE'])[1], 0,5);
        }

        $arIssetTimes = array_merge($arIssetTimes, $arDefaultRestDateTimeList);

        foreach ($arDefaultDateTimeList as $time) {
            if (!in_array($time, $arIssetTimes)) {
                $arRes[] = $time;
            }
        }

        return $arRes;


    }


    public function getTableAction($columns, $filter)
    {


        global $DB;

        $columns = json_decode($columns, true);
        $filterT = json_decode($filter, true);


        if ($filterT['>=PROPERTY_ATT_VAC_DATE'] && $filterT['<=PROPERTY_ATT_VAC_DATE']) {
            $filterT['>=PROPERTY_ATT_VAC_DATE'] = $DB->FormatDate($filterT['>=PROPERTY_ATT_VAC_DATE'], "DD.MM.YYYY", "YYYY-MM-DD"). '%';
            $filterT['<=PROPERTY_ATT_VAC_DATE'] = $DB->FormatDate($filterT['<=PROPERTY_ATT_VAC_DATE'], "DD.MM.YYYY", "YYYY-MM-DD"). '%';
        } else if ($filterT['PROPERTY_ATT_VAC_DATE']) {
            $filterT['PROPERTY_ATT_VAC_DATE'] = $DB->FormatDate($filterT['PROPERTY_ATT_VAC_DATE'], "DD.MM.YYYY", "YYYY-MM-DD"). '%';

        }


        $arSelectDefault = ['ID', 'IBLOCK_ID', 'NAME', 'DATE_CREATE'];
        $arAllProps = $result = $arColsCode = [];

        foreach ($columns as $key => $prop) {
            $arColsCode[$prop['code']] = $prop['name'];
            if ($prop['code'] != 'ID' && $prop['code'] != 'NAME' && $prop['code'] != 'DATE_CREATE') {
                $arAllProps[] = 'PROPERTY_'.$prop['code'];
            }
        }

        $arSelect = array_merge($arSelectDefault, $arAllProps);

        $count = 0;
        $arFilter = array("IBLOCK_ID"=> self::IBLOCK_ID_VACCINATION, "ACTIVE"=>"Y", );
        if (is_array($filterT)) {
            $arFilter = array_merge($arFilter, $filterT);
        }


        $res = CIBlockElement::GetList(array('NAME' => 'ASC'), $arFilter, false, false, $arSelect);
        while ($arFields = $res->GetNext()) {
            foreach ($arFields as $key => $value) {
                $arPatterns = ['/PROPERTY_/', '/_VALUE/'];
                $arReplacements = ['', ''];
                $newKey = preg_replace($arPatterns, $arReplacements, $key);

                if (array_key_exists($newKey, $arColsCode)) {

                    $result['list'][$count][$arColsCode[$newKey]] = $value;
                }
            }

            $count++;
        }
        $result['filter'] = $arFilter;

        return $result;
    }









































    /**
     * Для работы компонента
     *
     * @return array|mixed
     * @throws Exception Коментарий.
     */
    public function executeComponent()
    {
        global $APPLICATION;
        global $USER;

        $cacheId = implode('|', [
          SITE_ID,
          $APPLICATION->GetCurPage(),
          $USER->GetGroups()
        ]);

        foreach ($this->arParams as $k => $v) {
            if (strncmp('~', $k, 1)) {
                $cacheId .= ',' . $k . '=' . $v;
            }
        }

        $cacheDir = '/' . SITE_ID . $this->GetRelativePath();
        $cache    = Cache::createInstance();

        $templateCachedData = $this->GetTemplateCachedData();

        if ($cache->startDataCache($this->arParams['CACHE_TIME'], $cacheId, $cacheDir)) {

            $this->strCurrentPage = $_REQUEST['page'];
            if (!in_array($this->strCurrentPage, $this->arAvailablePages)) {
                $this->strCurrentPage = 'index';
            }
            $this->arResult['INCLUDE_FILE'] = 'page_' . strtolower($this->strCurrentPage) . '.php';
            $this->init($this->strCurrentPage);

            $this->IncludeComponentTemplate();

            $cache->endDataCache([
                                   'arResult'           => $this->arResult,
                                   'templateCachedData' => $templateCachedData,
                                 ]);
        } else {
            extract($cache->GetVars());
            $this->SetTemplateCachedData($templateCachedData);
        }

        $this->strTemplatePath = $this->__template->GetFolder();

        return $this->arResult;
    }


}
