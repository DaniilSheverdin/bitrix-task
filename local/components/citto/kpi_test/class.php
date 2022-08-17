<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Iblock\Elements\ElementUsersDataTable;
use Bitrix\Main\Loader;
use Bitrix\Main\UI\Extension;
use Bitrix\Main\Data\Cache;
use Bitrix\Main\Grid\Options as GridOptions;
use Bitrix\Main\UI\PageNavigation;
use Bitrix\Main\Grid\Panel\DefaultValue;
use Bitrix\Main\Grid\Panel\Snippet\Button;
use Bitrix\Main\Grid\Panel\Snippet\Onchange;
use Bitrix\Main\Grid\Panel\Actions;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Highloadblock as HL;

Loader::includeModule('citto.integration');
use Citto\Integration\Docx;

include('evalmath.class.php');


Extension::load('ui.bootstrap4');
Extension::load("ui.buttons.icons");
Extension::load("ui.forms");


/**
 * Класс для работы c KPI
 *
 * Class CittoKPIComponent
 */
class CittoKPIComponent extends CBitrixComponent implements Controllerable
{
    /**
     * Конфигурация аякс запросов
     *
     * @return array
     */
    public function configureActions(): array
    {
        return [
          'getNextLabel'                => ['prefilters' => []],
          'addKPI'                      => ['prefilters' => []],
          'getKPIs'                     => ['prefilters' => []],
          'getEnumFieldByID'            => ['prefilters' => []],
          'checkIndicatorsWeightSum'    => ['prefilters' => []],
          'returnSavedKPI'              => ['prefilters' => []],
          'saveWorkPositionValues'      => ['prefilters' => []],
          'getFormulas'                 => ['prefilters' => []],
          'addRuleExtra'                => ['prefilters' => []],
          'saveRulesExtra'              => ['prefilters' => []],
          'returnSavedKPIExt'           => ['prefilters' => []],
          'getFormulasExt'              => ['prefilters' => []],
          'updateUsersKPI'              => ['prefilters' => []],
          'getCriticalKPIs'             => ['prefilters' => []],
          'getCalculateKPIValue'        => ['prefilters' => []],
          'dataToHRD'                   => ['prefilters' => []],
          'createWorkPosition'          => ['prefilters' => []],
          'addUserToDepartment'         => ['prefilters' => []],
          'cancelDepartmentChanges'     => ['prefilters' => []],
          'saveDepartmentChanges'       => ['prefilters' => []],
          'deleteWorkPosition'          => ['prefilters' => []],
          'saveDepartmentWPSalary'      => ['prefilters' => []],
          'saveNotifies'                => ['prefilters' => []],
          'addAccessToUsers'            => ['prefilters' => []],

        ];
    }

    /** @var string */
    public const GRID_ID_RULES_CHANGE           = 'kpi_test_rules_change';

    /** @var string */
    public const GRID_ID_STAFF_TO_WP            = 'kpi_test_staff_to_wp';

    /** @var integer */
    public const SECTION_KPI_EXTRA              = SECTION_ID_KPI_TEST_EXTRA;

    /** @var integer
     * 453 Главное управление государственной службы и кадров
     * 105 Управление Дистанционного обслуживания (Единый контактный центр)
     */
    public const SECTION_ID_ALL_STRUCTURE       = 105;

    /** @var integer */
    public const SECTION_ID_NOTIFIES            = SECTION_ID_KPI_TEST_NOTIFIES;

    /** @var integer */
    public const IBLOCK_ID_KPI_STRUCT           = IBLOCK_ID_KPI_TEST_STRUCTURE;

    /** @var integer */
    public const IBLOCK_ID_KPI                  = IBLOCK_ID_KPI_TEST;

    /** @var integer */
    public const IBLOCK_ID_KPI_USERS            = IBLOCK_ID_KPI_TEST_USERS;

    /** @var integer */
    public const IBLOCK_ID_KPI_WORK_POSITIONS   = IBLOCK_ID_KPI_TEST_WORK_POSITIONS;

    /** @var integer */
    public const HLBLOCK_ID_KPI_RETRO           = HLBLOCK_ID_KPI_TEST_RETRO;

    /** @var integer */
    public const SALARY_SPECIALIST              = 8516;

    /** @var integer */
    public const SALARY_LEADING_SPECIALIST      = 10808;

    /** @var integer */
    public const SALARY_CONSULTANT              = 12013;

    /** @var integer */
    public const SALARY_TEAM_LEADER             = 12596;

    /** @var integer */
    public const SALARY_DEPARTMENT_HEAD         = 13260;

    /** @var integer */
    public const SALARY_GOVERNMENT_HEAD         = 51106;

    /** @var array */
    private $arAvailablePages = [
      'index',
      'computed_rules',
      'insert_data_dep',
      'send_data_gov',
      'computed_rules_extra',
      'computed_rules_change',
      'update_data',
      'staff_to_wp',
      'set_salary',
      'notify',
      'show_kpi',
      'access',
    ];

    /** @var string */
    private $strCurrentPage;

    /** @var boolean */
    private $bAccessDepartment          = false;

    /** @var boolean */
    private $bAccessGovernment          = false;

    /** @var boolean */
    private $bForceWriteRetroData       = true;

    /** @var string */
    private $strGISID                   = '105';

    /** @var string */
    private $strHeadOfDepartmentID      = '270';

    /** @var array */
    private $arCitSectionsDepth         = [];

    /** @var array */
    private $arUsersAccessExtDep        = ['1460', '1801', '593', '1509', '1502', '1123', '5810'];

    /** @var array */
    private $arUsersAccessExtGov        = ['1460', '1801', '593', '1509', '1502', '1123', '5810'];

    /** @var array */
    private $arMonthDefault             =
        [
            'january' => 'Январь',
            'february' => 'Февраль',
            'march' => 'Март',
            'april' => 'Апрель',
            'may' => 'Май',
            'june' => 'Июнь',
            'july' => 'Июль',
            'august' => 'Август',
            'september' => 'Сентябрь',
            'october' => 'Октябрь',
            'november' => 'Ноябрь',
            'december' => 'Декабрь',
        ];

    /** @var array */
    private $arMonth                    = [];

    /** @var array */
    private $arCitSections              = [];

    /** @var array */
    private $arGISections               = [];

    /** @var integer */
    private $intCurrentDepartmentByUser = 0;

    /** @var array */
    private $arUsers                    = [];

    /** @var array */
    private $arUsersDepartment          = [];

    /** @var array */
    private $arDepartments              = [];

    /** @var array */
    private $arUserHelper               = ['IMAGE' => '', 'NAME' => 'Ваня'];

    /** @var array */
    private $titlesStaff                = ['%d сотрудник', '%d сотрудника', '%d сотрудников'];

    /** @var array */
    private $arIntegralWP                = ['специалист', 'ведущий специалист', 'консультант'];

    /** @var string */
    private $strIntegralKPIName            = 'Интегральный показатель';

    /** @var string */
    private $strTemplatePath            = '';

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



    /**
     * @param string $strPage Смотрит в $_REQUEST['page'].
     * @throws Exception Если нет страницы в запросе.
     * @return void
     */
    public function init(string $strPage)
    {


        $this->testMode();
        $arGIS = $this->getGovernmentInformationSystem($this->strGISID);
        $this->getListOfDepartmentsGIS($arGIS, $this->arGISections);
        $this->setAccessToUser();

        switch ($strPage) {
            case 'computed_rules':
                $this->getDepartments();
                $this->getDepartmentUsers();

                break;

            case 'set_salary':
                $this->arResult['WORK_POSITIONS'] = $this->getWorkPositionsByDepartment();

                break;

            case 'staff_to_wp':
                $this->arResult['CURRENT_DEPARTMENT'] = $this->getCurrentUserDepartment();
                $this->arResult['ACCESS'] = $this->accessToDepartmentsByUser();
                $this->getDepartmentUsersByUser();
                $this->arResult['USERS'] = $this->arUsersDepartment;
                $this->arResult['ALL_USERS'] = $this->prepareAllUsersToSelect();

                break;

            case 'computed_rules_change':
                $this->saveKPI();
                $this->arResult['DATA_WP'] = $this->getDataKPIForWorkPosition();
                $this->makeGridRulesChange();

                break;

            case 'computed_rules_extra':
                $this->saveKPIExt();
                $this->arResult['RULES_EXTRA'] = $this->getRulesExtra();

                break;

            case 'insert_data_dep':

                $this->arResult['CURRENT_DEPARTMENT'] = $this->getCurrentUserDepartment();
                $this->arResult['ACCESS'] = $this->accessToDepartmentsByUser();
                $this->arResult['DEPARTMENT_DATA'] = $this->getDataDepartment($this->intCurrentDepartmentByUser);


                break;

            case 'send_data_gov':
                $this->arResult['GOVERNMENT_DATA'] = $this->getDataGovernment();

                break;

            case 'notify':
                $this->arResult['NOTIFIES'] = $this->getNotifies();

                break;

            case 'show_kpi':
                $this->arResult['GOVERNMENTS'] = $this->getAllGovernments();
                $this->arResult['DEPARTMENTS'] = $this->getAllDepartments();
                $this->getFillRetroDataDate();
                $this->arResult['PERIOD'] = $this->arMonth;
                $this->arResult['DEPARTMENT_DATA'] = $this->getRetroData();

                break;

            case 'access':
                $this->arResult['ACCESS_DEPARTMENT'] = $this->getDepartmentsAccess();


                break;

            case 'update_data':
                switch ($_REQUEST['action']) {
                    case 'make_work_positions':
                        $this->makeWorkPositions();
                        break;
                    case 'update_user_work_position':
                        $this->updateUserWorkPosition();
                        break;
                    case 'set_default_salary':
                        $this->setDefaultSalary();
                        break;
                    case 'clear_kpi_values':
                        $this->clearKPIValues();

                        break;
                }

                break;
        }
    }


    public function testMode() {
        $arMainData = [
          'SECTION_KPI_EXTRA' => self::SECTION_KPI_EXTRA,
          'SECTION_ID_ALL_STRUCTURE' => self::SECTION_ID_ALL_STRUCTURE,
          'SECTION_ID_NOTIFIES' => self::SECTION_ID_NOTIFIES,
          'IBLOCK_ID_KPI_STRUCT' => self::IBLOCK_ID_KPI_STRUCT,
          'IBLOCK_ID_KPI' => self::IBLOCK_ID_KPI,
          'IBLOCK_ID_KPI_USERS' => self::IBLOCK_ID_KPI_USERS,
          'IBLOCK_ID_KPI_WORK_POSITIONS' => self::IBLOCK_ID_KPI_WORK_POSITIONS,
          'HLBLOCK_ID_KPI_RETRO' => self::HLBLOCK_ID_KPI_RETRO,
        ];



    }


    /**
     * Сброс данных всех отделов.
     */
    public function clearKPIValues()
    {

        $arSelect = [
          'ID',
          'IBLOCK_ID',
          'CODE',
          'NAME',
          'PROPERTY_ATT_VALUE_KPI',
          'PROPERTY_ATT_KPI_CRITICAL',
          'PROPERTY_ATT_KPI_PROGRESS',
          'PROPERTY_ATT_PERCENT_COMPLETE',
          'PROPERTY_ATT_RESULT_KPI',
          'PROPERTY_ATT_COMMENT',

        ];
        $arFilter = ['IBLOCK_ID' => self::IBLOCK_ID_KPI_USERS, 'ACTIVE' => 'Y', '!PROPERTY_ATT_WORK_POSITION' => false];
        $rs = CIBlockElement::GetList(['sort' => 'asc'], $arFilter, false, false, $arSelect);
        while($arFields = $rs->GetNext()) {

            $arKPIValues = [];
            foreach ($arFields['PROPERTY_ATT_VALUE_KPI_VALUE'] as $count => $kpi) {
                $arKPIValues[$count] = ['VALUE' => intval($kpi), 'DESCRIPTION' => false];
            }

            CIBlockElement::SetPropertyValuesEx(
              intval($arFields['ID']),
              self::IBLOCK_ID_KPI_USERS,
              ['ATT_VALUE_KPI' => $arKPIValues]
            );
            CIBlockElement::SetPropertyValuesEx(
              intval($arFields['ID']),
              self::IBLOCK_ID_KPI_USERS,
              ['ATT_KPI_CRITICAL' => false]
            );
            CIBlockElement::SetPropertyValuesEx(
              intval($arFields['ID']),
              self::IBLOCK_ID_KPI_USERS,
              ['ATT_KPI_PROGRESS' => 'N']
            );
            CIBlockElement::SetPropertyValuesEx(
              intval($arFields['ID']),
              self::IBLOCK_ID_KPI_USERS,
              ['ATT_PERCENT_COMPLETE' => false]
            );
            CIBlockElement::SetPropertyValuesEx(
              intval($arFields['ID']),
              self::IBLOCK_ID_KPI_USERS,
              ['ATT_RESULT_KPI' => false]
            );
            CIBlockElement::SetPropertyValuesEx(
              intval($arFields['ID']),
              self::IBLOCK_ID_KPI_USERS,
              ['ATT_COMMENT' => false]
            );

        }


    }


    /**
     * Выбирает доступные отделы на редактирование для текущего пользователя.
     *
     * @return array Доступные отделы.
     */
    public function accessToDepartmentsByUser(): array
    {

        global $USER;

        $intCurrentUserID = intval($USER->GetID());

        $rsUser = CUser::GetByID($intCurrentUserID);
        $arUser = $rsUser->Fetch();


        $arDepartments = [];
        foreach($arUser['UF_KPI_ACCESS_TO_DEPARTMENT'] as $departmentID) {
            $rsSection = CIBlockSection::GetByID($departmentID);
            if($arSection = $rsSection->GetNext()) {
                $arDepartments[$departmentID] = $arSection['NAME']; // TODO $arDepartments['ACCESS'][$departmentID] = $arSection['NAME'];
            }
        }

        foreach($arUser['UF_KPI_ASSISTANT_TO_DEPARTMENT'] as $departmentID) {
            $rsSection = CIBlockSection::GetByID($departmentID);
            if($arSection = $rsSection->GetNext()) {
                $arDepartments[$departmentID] = $arSection['NAME']; // TODO $arDepartments['ASSISTANT'][$departmentID] = $arSection['NAME'];
            }
        }

        if (isset($_REQUEST['department'])) {
            $intSelectedDepartment = intval($_REQUEST['department']);

            if ($intSelectedDepartment > 0 && in_array($intSelectedDepartment, array_keys($arDepartments))) {
                $this->intCurrentDepartmentByUser = $intSelectedDepartment;
            }
        }

        if (count($arDepartments) > 0) {
            $this->arUsersAccessExtDep[] = $intCurrentUserID;
            $this->setAccessToUser();
        }


        return $arDepartments;

    }


    /**
     * Выбирает пользователей с допонительными доступами к редактированию данных отделов
     *
     * @return array Данные доплнительных доступов.
     */
    public function getDepartmentsAccess(): array
    {

        $arGovernment = $this->getCurrentUserGov();
        $arDepartments = $arGovernment['DEPARTMENTS'];
        $arDepartments[$arGovernment['GOVERNMENT']] = $arGovernment['GOVERNMENT_NAME'];


        $arFilledDepartments = [];
        $arAccessDepartmentDefault = [];
        $arAccessDepartment = [];


//        $arSelect = ['ID', 'IBLOCK_ID', 'CODE', 'PROPERTY_ATT_DEPARTMENT'];
//        $arFilter = ['IBLOCK_ID' => self::IBLOCK_ID_KPI_STRUCT, 'ACTIVE' => 'Y', 'PROPERTY_ATT_DEPARTMENT' => array_keys($arDepartments)];
//        $rs = CIBlockElement::GetList(['sort' => 'asc'], $arFilter, false, false, $arSelect);
//        while($arFields = $rs->GetNext()) {
//            $arFilledDepartments[$arFields['PROPERTY_ATT_DEPARTMENT_VALUE']] = $arDepartments[$arFields['PROPERTY_ATT_DEPARTMENT_VALUE']];
//        }
        $arFilledDepartments = $arDepartments;

        $arWP = ['%начальник отдела%', '%руководитель%'];

        $by = 'id';
        $order = 'desc';
        $filterUsersHead = [
          'ACTIVE' => 'Y',
          'WORK_POSITION' => implode('|', $arWP),
          '!UF_KPI_TEST_WORK_POSITION' => false,
          'UF_DEPARTMENT' => array_keys($arFilledDepartments),
        ];
        $arParamsUsersHead = [
          'FIELDS' => ['WORK_POSITION', 'LAST_NAME', 'NAME', 'SECOND_NAME', 'ID'],
          'SELECT' => ['UF_DEPARTMENT', 'UF_KPI_TEST_WORK_POSITION', 'UF_KPI_ACCESS_TO_DEPARTMENT']
        ];

        $rsUsersHead = CUser::GetList($by, $order, $filterUsersHead, $arParamsUsersHead);

        while ($arUserHead = $rsUsersHead->Fetch()) {
            if ($arFilledDepartments[$arUserHead['UF_DEPARTMENT'][0]]) {
                $arAccessDepartmentDefault[$arUserHead['UF_DEPARTMENT'][0]]['NAME'] = $arFilledDepartments[$arUserHead['UF_DEPARTMENT'][0]];
                $arAccessDepartmentDefault[$arUserHead['UF_DEPARTMENT'][0]]['ACCESS'] = $arUserHead['ID'];
            }

        }

        $filterUsersAccess = [
          'ACTIVE' => 'Y',
          'UF_KPI_ACCESS_TO_DEPARTMENT' => array_keys($arFilledDepartments),
          '!UF_KPI_TEST_WORK_POSITION' => false,
        ];
        $arParamsUsersAccess = [
          'FIELDS' => ['WORK_POSITION', 'LAST_NAME', 'NAME', 'SECOND_NAME', 'ID'],
          'SELECT' => ['UF_KPI_TEST_WORK_POSITION', 'UF_KPI_ACCESS_TO_DEPARTMENT',]
        ];

        $rsUsersAccess = CUser::GetList($by, $order, $filterUsersAccess, $arParamsUsersAccess);

        while ($arUserAccess = $rsUsersAccess->Fetch()) {
            foreach ($arUserAccess['UF_KPI_ACCESS_TO_DEPARTMENT'] as $departmentID) {
                $arAccessDepartment[$departmentID]['ACCESS'] = $arUserAccess['ID'];
            }
        }


        $filterUsersAssistants = [
          'ACTIVE' => 'Y',
          'UF_KPI_ASSISTANT_TO_DEPARTMENT' => array_keys($arFilledDepartments),
          '!UF_KPI_TEST_WORK_POSITION' => false,
        ];
        $arParamsUsersAssistants = [
          'FIELDS' => ['WORK_POSITION', 'LAST_NAME', 'NAME', 'SECOND_NAME', 'ID'],
          'SELECT' => ['UF_KPI_TEST_WORK_POSITION', 'UF_KPI_ASSISTANT_TO_DEPARTMENT',]
        ];

        $rsUsersAssistants = CUser::GetList($by, $order, $filterUsersAssistants, $arParamsUsersAssistants);

        while ($arUserAssistants = $rsUsersAssistants->Fetch()) {
            foreach ($arUserAssistants['UF_KPI_ASSISTANT_TO_DEPARTMENT'] as $departmentID) {
                    $arAccessDepartmentDefault[$departmentID]['ASSISTANTS'] = $arUserAssistants['ID'];
            }
        }

        $arAllUsers = $this->prepareAllUsersToSelect(true);


        return [
          'ACCESS_DEFAULT' => $arAccessDepartmentDefault,
          'ACCESS' => $arAccessDepartment,
          'ALL_USERS' => $arAllUsers
        ];

    }


    /**
     * Вычисляет и записывает фактический показатель для интегрального KPI в текущем отделе
     *
     * на основе прроцента выполнения KPI сотрудников отдела у которых нет интегрального показателя в должностях $this->arIntegralWP
     */
    public function calcIntegralKPI() {

        global $USER;

        $logger = new Logger('calcIntegralKPI()');
        $logger->pushHandler(new StreamHandler($_SERVER['DOCUMENT_ROOT'] . '/local/logs/test-kpi/kpi_integral.log', Logger::INFO));
        $arLoggerData = [];

        $arCurrentDepartment = $this->getCurrentUserDepartment();

        $arSelect = ['ID', 'IBLOCK_ID', 'CODE', 'NAME', 'PROPERTY_ATT_VALUE_KPI', 'PROPERTY_ATT_PERCENT_COMPLETE'];
        $arFilter = [
          'IBLOCK_ID' => self::IBLOCK_ID_KPI_USERS,
          'ACTIVE' => 'Y',
          [
            'LOGIC' => 'OR',
            'PROPERTY_ATT_DEPARTMENT' => $arCurrentDepartment['ID'],
            'PROPERTY_ATT_OTHER_DEPARTMENT' => $arCurrentDepartment['ID'],

          ],
          '!PROPERTY_ATT_WORK_POSITION' => ['0', false, 0]
        ];


//        $by = 'id';
//        $order = 'desc';
//        $filterWP = [
//          'ACTIVE' => 'Y',
//          '!UF_KPI_TEST_WORK_POSITION' => false,
//        ];
//        $arParams = [
//          'FIELDS' => ['ID', 'WORK_POSITION'],
//          'SELECT' => []
//        ];

        $arData = [];
        $arIntegralKPIs = [];

        $rs = CIBlockElement::GetList(['sort' => 'asc'], $arFilter, false, false, $arSelect);
        while($arFields = $rs->GetNext()) {


            $filterWP['ID'] = intval($arFields['CODE']);

            $rsUser = CUser::GetByID(intval($arFields['CODE']));
            $arUser = $rsUser->Fetch();
            if ($arUser['WORK_POSITION'] && in_array(mb_strtolower($arUser['WORK_POSITION']), $this->arIntegralWP)) {
                $arData[$arFields['CODE']]['WP_NAME'] = $arUser['WORK_POSITION'];
            }

//            $rsUsers = CUser::GetList($by, $order, $filterWP, $arParams);
//            $arLoggerData['getUserData'] = [
//              '$by' => $by,
//              '$order' => $order,
//              '$filterWP' => $filterWP,
//              '$arParams' => $arParams,
//            ];
//            while ($arUser = $rsUsers->Fetch()) {
//                $arLoggerData['$arUser'][] = $arUser;
//                if ($arUser['WORK_POSITION'] && in_array(mb_strtolower($arUser['WORK_POSITION']), $this->arIntegralWP)) {
//                    $arData[$arFields['CODE']]['WP_NAME'] = $arUser['WORK_POSITION'];
//                }
//            }

            $arData[$arFields['CODE']]['NAME'] = $arFields['NAME'];

            $arLoggerData['PERCENT_COMPLETE'][] = intval($arFields['PROPERTY_ATT_PERCENT_COMPLETE_VALUE']);
            $arLoggerData['WP_NAME'][] = $arData[$arFields['CODE']]['WP_NAME'];

            if (intval($arFields['PROPERTY_ATT_PERCENT_COMPLETE_VALUE']) > 0 && $arData[$arFields['CODE']]['WP_NAME']) {
                $arData[$arFields['CODE']]['PERCENT_COMPLETE'] = $arFields['PROPERTY_ATT_PERCENT_COMPLETE_VALUE'];
            }

            foreach ($arFields['PROPERTY_ATT_VALUE_KPI_VALUE'] as $count => $valueID) {

                $arSelectKPI = ['ID', 'IBLOCK_ID', 'CODE', 'PROPERTY_ATT_DATA_SOURCE'];
                $arFilterKPI = ['IBLOCK_ID' => self::IBLOCK_ID_KPI, 'ACTIVE' => 'Y', 'ID' => $valueID];
                $rsKPI = CIBlockElement::GetList(['sort' => 'asc'], $arFilterKPI, false, false, $arSelectKPI);
                while($arFieldsKPI = $rsKPI->GetNext()) {

                    $arIntegralKPIs[$arFields['ID']]['KPIS']['VALUE'][$count] = $valueID;
                    $arIntegralKPIs[$arFields['ID']]['KPIS']['DESCRIPTION'][$count] = unserialize(htmlspecialcharsback($arFields['PROPERTY_ATT_VALUE_KPI_DESCRIPTION'][$count]));;
                    if ($arFieldsKPI['PROPERTY_ATT_DATA_SOURCE_VALUE'] == $this->strIntegralKPIName) {
                        $arIntegralKPIs[$arFields['ID']]['IS_INTEGRAL'] = $count;
                    }
                }
            }
        }

        $floatSumIntegral = 0;
        $intCountUsersWithPercentComplete = 0;
        foreach ($arData as $userID => $arUser) {
            if ($arUser['PERCENT_COMPLETE']) {
                $floatSumIntegral += floatval($arUser['PERCENT_COMPLETE']);
                $intCountUsersWithPercentComplete++;
            }
        }

        $arLoggerData['$arData'] = $arData;



        $floatIntegralKPIResult = round(($floatSumIntegral / $intCountUsersWithPercentComplete) , 2);



        $arKPIValues = [];
        foreach ($arIntegralKPIs as $userID => &$value) {
            if (intval($value['IS_INTEGRAL']) >= 0) {
                $value['KPIS']['DESCRIPTION'][$value['IS_INTEGRAL']] = $floatIntegralKPIResult;

                foreach ($value['KPIS']['VALUE'] as $count => $KPIValue) {
                    $arKPIValues[$count] = ['VALUE' => intval($KPIValue), 'DESCRIPTION' => $value['KPIS']['DESCRIPTION'][$count]];
                }

                CIBlockElement::SetPropertyValuesEx(intval($userID), self::IBLOCK_ID_KPI_USERS, ['ATT_VALUE_KPI' => $arKPIValues]);

            }
        }

        $arLoggerData['VALUES'] = [
          '$arIntegralKPIs' => $arIntegralKPIs,
          '$arKPIValues' => $arKPIValues,
          '$floatSumIntegral' => $floatSumIntegral,
          '$intCountUsersWithPercentComplete' => $intCountUsersWithPercentComplete,
          '$floatIntegralKPIResult' => $floatIntegralKPIResult,
        ];

        $logger->info('Изменено значение интегрального показателя: ', [$arLoggerData]);
    }


  /**
   * Возвращает данные KPI за отчетный период.
   *
   * @return array
   * @throws \Bitrix\Main\ArgumentException
   * @throws \Bitrix\Main\ObjectPropertyException
   * @throws \Bitrix\Main\SystemException
   */
    public function getRetroData()
    {
        $arRes = [];

        if (isset($_REQUEST['government']) && intval($_REQUEST['government']) > 0) {

            $department = $_REQUEST['government'];

            if (isset($_REQUEST['department']) && intval($_REQUEST['department']) > 0) {
                $department = $_REQUEST['department'];
            }

            if (isset($_REQUEST['date']) && in_array($_REQUEST['date'], array_keys($this->arMonth))) {

                $strMonth = explode('-', $_REQUEST['date'])[0];
                $strYear = explode('-', $_REQUEST['date'])[1];

                $hlblock = HL\HighloadBlockTable::getById(self::HLBLOCK_ID_KPI_RETRO)->fetch();
                $entity = HL\HighloadBlockTable::compileEntity($hlblock);
                $entity_data_class = $entity->getDataClass();

                $date = new DateTimeImmutable('now');
                $strFirstDay = $date->modify("first day of $strMonth $strYear");
                $strLastDay = $date->modify("last day of $strMonth $strYear");


                $arFilter = [
                  '>=UF_HL_KPI_DATE' => $strFirstDay->format('d.m.Y'),
                  '<=UF_HL_KPI_DATE' => $strLastDay->format('d.m.Y'),
                  'UF_HL_KPI_DEPARTMENT' => $department
                ];

                $rsData = $entity_data_class::getList(
                  array(
                    "select" => ['*'],
                    "order" => ['ID' => 'ASC'],
                    "filter" => $arFilter,
                  ));

                while ($arData = $rsData->fetch()) {

                    if ($arData['UF_HL_KPI_FE']) {
                        $arRes[$arData['UF_HL_KPI_DEPARTMENT']][$arData['UF_HL_KPI_FE']]['FORMULA'] = $arData['UF_HL_KPI_FE_FORMULA'];

                        $rsUser = CUser::GetByID(intval($arData['UF_HL_KPI_USER_ID']));
                        $arUser = $rsUser->Fetch();

                        $strFIO = $arUser['LAST_NAME'] . ' ' . $arUser['NAME'] . ' ' . $arUser['SECOND_NAME'];
                        $arRes[$arData['UF_HL_KPI_DEPARTMENT']][$arData['UF_HL_KPI_FE']]['USERS'][$arData['UF_HL_KPI_USER_ID']]['FIO'] = $strFIO;
                        $arRes[$arData['UF_HL_KPI_DEPARTMENT']][$arData['UF_HL_KPI_FE']]['USERS'][$arData['UF_HL_KPI_USER_ID']]['RATE'] = $arData['UF_HL_KPI_RATE'];


                        foreach ($arData['UF_HL_KPI_KPIS'] as $count => $kpi) {

                            $strKPIValue = explode('///', $kpi)[0];
                            $strKPIName = explode('///', $kpi)[1];

                            if (!in_array($strKPIName, $arRes[$arData['UF_HL_KPI_DEPARTMENT']][$arData['UF_HL_KPI_FE']]['KPI_NAMES'])) {
                                $arRes[$arData['UF_HL_KPI_DEPARTMENT']][$arData['UF_HL_KPI_FE']]['KPI_NAMES'][] = $strKPIName;

                            }


                            $arRes[$arData['UF_HL_KPI_DEPARTMENT']][$arData['UF_HL_KPI_FE']]['USERS'][$arData['UF_HL_KPI_USER_ID']]['KPIS'][$count]['VALUE'] = $strKPIValue;
                            $arRes[$arData['UF_HL_KPI_DEPARTMENT']][$arData['UF_HL_KPI_FE']]['USERS'][$arData['UF_HL_KPI_USER_ID']]['KPIS'][$count]['NAME'] = $strKPIName;

                        }

                        $arRes[$arData['UF_HL_KPI_DEPARTMENT']][$arData['UF_HL_KPI_FE']]['USERS'][$arData['UF_HL_KPI_USER_ID']]['CRITICAL'] = $arData['UF_HL_KPI_CRITICAL'];
                        $strProgress = '';
                        if ($arData['UF_HL_KPI_PROGRESS'] == 'Y') {
                            $strProgress = 'Активирован';
                        }
                        $arRes[$arData['UF_HL_KPI_DEPARTMENT']][$arData['UF_HL_KPI_FE']]['USERS'][$arData['UF_HL_KPI_USER_ID']]['PROGRESS'] = $strProgress;
                        $arRes[$arData['UF_HL_KPI_DEPARTMENT']][$arData['UF_HL_KPI_FE']]['USERS'][$arData['UF_HL_KPI_USER_ID']]['COMMENT'] = $arData['UF_HL_KPI_COMMENT'];
                        $arRes[$arData['UF_HL_KPI_DEPARTMENT']][$arData['UF_HL_KPI_FE']]['USERS'][$arData['UF_HL_KPI_USER_ID']]['RESULT'] = $arData['UF_HL_KPI_RESULT'];

                    }
                }
            }
        }

        return $arRes;

    }


    /**
     * Записывает в массив $this->arMonth месяца в которых есть данные.
     *
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function getFillRetroDataDate ()
    {

        $arFilledDateYear = [];

        $hlblock = HL\HighloadBlockTable::getById(self::HLBLOCK_ID_KPI_RETRO)->fetch();
        $entity = HL\HighloadBlockTable::compileEntity($hlblock);
        $entity_data_class = $entity->getDataClass();


        $rsData = $entity_data_class::getList(
            array(
                "select" => ['UF_HL_KPI_DATE'],
                "order" => ['ID' => 'ASC'],
                "filter" => [],
            ));


        while ($arData = $rsData->fetch()) {

            $month = strtolower($arData['UF_HL_KPI_DATE']->format('F'));
            $year = $arData['UF_HL_KPI_DATE']->format('Y');

            $strMonthYear = $month . '-'. $year;

            $arFilledDateYear[$strMonthYear] = $this->arMonthDefault[$month] . ' ' . $year;

        }

        $this->arMonth = $arFilledDateYear;

    }


    /**
     * Возвращает колличество записей за текущий месяц.
     *
     * @return integer Колличество записей.
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function checkGovernmentData(): int
    {

        $hlblock = HL\HighloadBlockTable::getById(self::HLBLOCK_ID_KPI_RETRO)->fetch();
        $entity = HL\HighloadBlockTable::compileEntity($hlblock);
        $entity_data_class = $entity->getDataClass();

        $date = new DateTimeImmutable('now');
        $strFirstDay = $date->modify('first day of this month');
        $strLastDay = $date->modify('last day of this month');


        $arFilter = [
            '>=UF_HL_KPI_DATE' => $strFirstDay->format('d.m.Y'),
            '<=UF_HL_KPI_DATE' => $strLastDay->format('d.m.Y'),
        ];

        $rsData = $entity_data_class::getList(
            array(
                "select" => ['ID'],
                "order" => ['ID' => 'ASC'],
                "filter" => $arFilter,
            ));

        return $rsData->getSelectedRowsCount();

    }


    /**
     * Сохранение данных за отчетный период.
     *
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function saveRetroData() {

        if ($this->bForceWriteRetroData || $this->checkGovernmentData() <= 0) {

            $logger = new Logger('saveRetroData()');
            $logger->pushHandler(new StreamHandler($_SERVER['DOCUMENT_ROOT'] . '/local/logs/test-kpi/kpi_save_retro_data.log', Logger::INFO));

            $strMaxValueCriticalKPI = explode('/', $this->getCriticalKPIsAction()[0]['label'])[1];

            $arSelect = [
                'ID',
                'IBLOCK_ID',
                'CODE',
                'ATT_WORK_POSITION.NAME',
                'PROPERTY_ATT_DEPARTMENT',
                'PROPERTY_ATT_WORK_POSITION.NAME', // ФЕ
                'PROPERTY_ATT_WORK_POSITION.PROPERTY_ATT_FORMULA',
                'PROPERTY_ATT_VALUE_KPI', // множественное
                'PROPERTY_ATT_VALUE_KPI.NAME', // множественное
                'PROPERTY_ATT_KPI_CRITICAL', // Label
                'PROPERTY_ATT_KPI_PROGRESS', // Y
                'PROPERTY_ATT_RESULT_KPI',
                'PROPERTY_ATT_SALARY', // Ставка
                'PROPERTY_ATT_COMMENT',

            ];
            $arFilter = ['IBLOCK_ID' => self::IBLOCK_ID_KPI_USERS, 'ACTIVE' => 'Y', ];
            $rs = CIBlockElement::GetList(['sort' => 'asc'], $arFilter, false, false, $arSelect);
            while($arFields = $rs->GetNext()) {

                $arDataAdd = [];
                $arDataAdd['UF_HL_KPI_USER_ID'] = $arFields['CODE'];
                $arDataAdd['UF_HL_KPI_DEPARTMENT'] = $arFields['PROPERTY_ATT_DEPARTMENT_VALUE'];
                $arDataAdd['UF_HL_KPI_FE'] = $arFields['PROPERTY_ATT_WORK_POSITION_NAME'];
                $arDataAdd['UF_HL_KPI_COMMENT'] = $arFields['PROPERTY_ATT_COMMENT_VALUE'];
                $arDataAdd['UF_HL_KPI_PROGRESS'] = $arFields['PROPERTY_ATT_KPI_PROGRESS_VALUE'];
                $arDataAdd['UF_HL_KPI_RATE'] = $arFields['PROPERTY_ATT_SALARY_VALUE'];
                $arDataAdd['UF_HL_KPI_RESULT'] = $arFields['PROPERTY_ATT_RESULT_KPI_VALUE'];
                $arDataAdd['UF_HL_KPI_FE_FORMULA'] = str_replace(',', '', $arFields['PROPERTY_ATT_WORK_POSITION_PROPERTY_ATT_FORMULA_VALUE']);
                if ($arFields['PROPERTY_ATT_KPI_CRITICAL_VALUE']) {
                    $arDataAdd['UF_HL_KPI_CRITICAL'] = 'Активирован ' . $arFields['PROPERTY_ATT_KPI_CRITICAL_VALUE'] . '/' . $strMaxValueCriticalKPI;
                }


                $arDataKPI = [];
                foreach ($arFields['PROPERTY_ATT_VALUE_KPI_VALUE'] as $index => $strKpiID) {
                    $arDataKPI[$index] = unserialize(htmlspecialcharsback($arFields['PROPERTY_ATT_VALUE_KPI_DESCRIPTION'][$index]));

                    $rsKPI = CIBlockElement::GetByID($strKpiID);
                    if($arKPI = $rsKPI->GetNext()) {
                        $arDataKPI[$index] .= '///' . $arKPI['NAME'];
                    }
                }
                $arDataAdd['UF_HL_KPI_KPIS'] = $arDataKPI;
                $arDataAdd['UF_HL_KPI_DATE'] = date("d.m.Y");

                $hlblock = HL\HighloadBlockTable::getById(self::HLBLOCK_ID_KPI_RETRO)->fetch();
                $entity = HL\HighloadBlockTable::compileEntity($hlblock);
                $entity_data_class = $entity->getDataClass();

                $obResultAdd = $entity_data_class::add($arDataAdd);


                if (!$obResultAdd->isSuccess()) {
                    $errors = $obResultAdd->getErrorMessages();
                    $logger->info('Ошибка добавления: ', ['$errors' => $errors]);
                } else {
                    $intID = $obResultAdd->getId();
                    $logger->info('Добавлена  с ID: ' . $intID, ['$arLoad' => $arDataAdd]);
                }
            }
        }
        if (!$this->bForceWriteRetroData) {
            $this->clearKPIValues();
        }
    }


    /**
     * Возвращает все управления
     *
     * @return array
     */
    public function getAllGovernments(): array
    {

        $arRes = [];

        $this->getCitSections();

        foreach ($this->arCitSectionsDepth['child'] as $id => $government) {
            $arRes[$id] = $government['NAME'];
        }

        return $arRes;

    }

    /**
     * Возвращает все вложенные отделы и группы в управление
     *
     * @return array
     */
    public function getAllDepartments(): array
    {

        $arRes = [];

        if (isset($_REQUEST['government']) && intval($_REQUEST['government']) > 0) {

            $strGovID =  $_REQUEST['government'];

            $arGIS = $this->getGovernmentInformationSystem($strGovID);
            $this->getListOfDepartmentsGIS($arGIS, $arRes);

            unset($arRes[$strGovID]);
        }


        return $arRes;

    }


    /**
     * Назначает права для доступа KPI
     * @return void
     */
    public function setAccessToUser()
    {
        global $USER;

        $strUserID = $USER->GetID();


        $arWP = ['%начальник отдела%', '%руководитель%', '%Начальник отдела%', '%Руководитель группы%'];

        $arAccessDepartmentData = [];

        $by = 'id';
        $order = 'desc';
        $filterWP = [
          'ACTIVE' => 'Y',
          'WORK_POSITION' => implode('|', $arWP),
          '!UF_KPI_TEST_WORK_POSITION' => false,
          'UF_DEPARTMENT' => array_keys($this->arGISections),
        ];
        $arParams = [
          'FIELDS' => ['WORK_POSITION', 'LAST_NAME', 'NAME', 'SECOND_NAME', 'ID'],
          'SELECT' => ['UF_DEPARTMENT', 'UF_KPI_TEST_WORK_POSITION']
        ];

        $rsUsers = CUser::GetList($by, $order, $filterWP, $arParams);
        while ($arUser = $rsUsers->Fetch()) {

            $arAccessDepartmentData[] = $arUser['ID'];
        }


        array_push($this->arUsersAccessExtGov, $this->strHeadOfDepartmentID);

        $arMergeAccessDepartmentData = array_merge($arAccessDepartmentData, $this->arUsersAccessExtDep);


        if (in_array($strUserID, $arMergeAccessDepartmentData)) {
            $this->bAccessDepartment = $this->arResult['ACCESS_DEPARTMENT'] = true;
        }

        if (in_array($strUserID, $this->arUsersAccessExtGov)) {
            $this->bAccessGovernment = $this->arResult['ACCESS_GOVERNMENT'] = true;
        }


    }

    /**
     * Возвращает вложенный массив одного управления
     *
     * @param string $govID ID Управления.
     * @return array
     */
    public function getGovernmentInformationSystem(string $govID) {

        $arRes = [];

        $this->getCitSections();

        if ($this->arCitSectionsDepth['ID'] == $govID) {
            $arRes = $this->arCitSectionsDepth;
        }

//        foreach ($this->arCitSectionsDepth[''] as $id => $gov) {
//            if ($gov['ID'] == $govID) {
//                $arRes = $gov;
//            }
//        }
        return $arRes;
    }


    /**
     * Возвращает список всех отделов управления информационных систем
     *
     * @param array $arDepthOfDepsGIS
     * @param array $result
     * @return mixed
     */
    public function getListOfDepartmentsGIS(array $arDepthOfDepsGIS, array &$result) {

        if (!in_array($arDepthOfDepsGIS['NAME'], $result)) {
            $result[$arDepthOfDepsGIS['ID']] = $arDepthOfDepsGIS['NAME'];
        }

        foreach ($arDepthOfDepsGIS['child'] as $id => $department) {
            $result[$id] = $department['NAME'];
            if ($department['child']) {
                $this->getListOfDepartmentsGIS($department, $result);
            }
        }

        return $result;
    }


    /**
     * Возвращает массив уведомлений. Сортирует по разделам ИБ
     * @return mixed
     */
    public function getNotifies()
    {
        $this->arResult['NOTIFY_VALUES'] = $arNotifyValues = $this->getEnumFields('ATT_NOTIFY');

        $arDayValues = [];
        for ($i = 1; $i <= 28; $i++) {
            $arDayValues[] = $i;
        }

        $arNotifySections = $this->getSectionList(
            array(
            'IBLOCK_ID' => self::IBLOCK_ID_KPI,
            'SECTION_ID' => self::SECTION_ID_NOTIFIES
            ),
            array(
            'NAME',
            'CODE',
            )
        );

        foreach ($arNotifySections['child'] as $sectionID => $arSection) {
            $intCount = 0;
            $arSelect = ['ID', 'IBLOCK_ID', 'NAME', 'PROPERTY_ATT_NOTIFY', 'PROPERTY_ATT_DEADLINE_DAY'];
            $arFilter = ['IBLOCK_ID' => self::IBLOCK_ID_KPI, 'ACTIVE' => 'Y', 'IBLOCK_SECTION_ID' => $sectionID];
            $rs = CIBlockElement::GetList(['sort' => 'asc'], $arFilter, false, false, $arSelect);
            while ($arFields = $rs->GetNext()) {
                $arNotifySections['child'][$sectionID]['ITEMS'][$arFields['ID']]['NAME'] = $arFields['NAME'];
                $arNotifySections['child'][$sectionID]['ITEMS'][$arFields['ID']]['NOTIFIES']['VALUES'] = $arFields['PROPERTY_ATT_NOTIFY_VALUE'];

                if ($arFields['NAME'] == 'Оповещать о появлении данных по компании за очередной отчетный период'
                  || $arFields['NAME'] == 'Оповещать о просрочке со стороны отдела'
                  || $arFields['NAME'] == 'Оповещать о появлении данных за очередной отчетный период'
                ) {
                    foreach ($arNotifyValues as $key => $notifyValue) {
                        if ($notifyValue == 'Включено' || $notifyValue == 'Выключено') {
                            $arNotifySections['child'][$sectionID]['ITEMS'][$arFields['ID']]['NOTIFIES']['VALUES_LIST'][$key] = $notifyValue;

                        }
                    }
                } else {
                    foreach ($arNotifyValues as $key => $notifyValue) {
                        if ($notifyValue == 'Включено' || $notifyValue == 'Выключено') {

                        } else {
                            $arNotifySections['child'][$sectionID]['ITEMS'][$arFields['ID']]['NOTIFIES']['VALUES_LIST'][$key] = $notifyValue;
                            $arNotifySections['child'][$sectionID]['ITEMS'][$arFields['ID']]['NOTIFIES']['MULTIPLE'] = 'Y';
                        }
                    }
                }



                if ($arFields['PROPERTY_ATT_DEADLINE_DAY_VALUE']) {
                    $arNotifySections['child'][$sectionID]['ITEMS'][$arFields['ID']]['NOTIFIES']['DEADLINE']['VALUE'] = $arFields['PROPERTY_ATT_DEADLINE_DAY_VALUE'];
                    $arNotifySections['child'][$sectionID]['ITEMS'][$arFields['ID']]['NOTIFIES']['DEADLINE']['ALL_VALUES'] = $arDayValues;
                }

                $intCount++;
            }
        }

        return $arNotifySections['child'];
    }


    /**
     * Устанавливает дефолтные значения оклада должностей
     * @return void
     */
    public function setDefaultSalary()
    {
        $arSelect = ['ID', 'IBLOCK_ID', 'NAME'];
        $arFilter = ['IBLOCK_ID' => self::IBLOCK_ID_KPI_WORK_POSITIONS, 'ACTIVE' => 'Y'];
        $rs = CIBlockElement::GetList(['sort' => 'asc'], $arFilter, false, false, $arSelect);
        while ($arFields = $rs->GetNext()) {
            $strName = mb_strtolower($arFields['NAME']);

            $intSalary = 0;

            switch ($strName) {
                case 'ведущий специалист':
                    $intSalary = self::SALARY_LEADING_SPECIALIST;
                    break;
                case 'специалист':
                    $intSalary = self::SALARY_SPECIALIST;
                    break;
                case 'руководитель группы':
                    $intSalary = self::SALARY_TEAM_LEADER;
                    break;
                case 'консультант':
                    $intSalary = self::SALARY_CONSULTANT;
                    break;
                case 'начальник отдела':
                    $intSalary = self::SALARY_DEPARTMENT_HEAD;
                    break;
                case 'начальник управления':
                    $intSalary = self::SALARY_GOVERNMENT_HEAD;
                    break;
            }

            if ($intSalary == 0) {
                if (stristr($strName, 'руководитель группы')) {
                    $intSalary = self::SALARY_TEAM_LEADER;
                }
                if (stristr($strName, 'начальник отдела')) {
                    $intSalary = self::SALARY_DEPARTMENT_HEAD;
                }
                if (stristr($strName, 'начальник управления')) {
                    $intSalary = self::SALARY_GOVERNMENT_HEAD;
                }
            }

            if ($intSalary > 0) {
                CIBlockElement::SetPropertyValuesEx(
                    $arFields['ID'],
                    self::IBLOCK_ID_KPI_WORK_POSITIONS,
                    array('ATT_SALARY' => $intSalary)
                );
                if (isset($_REQUEST['debug'])) {
                    pre(['salary' => $intSalary, 'name' => $strName]);
                }
            }
        }
    }


    /**
     * Возвращает все должности текущего отдела
     *
     * @return array
     */
    public function getWorkPositionsByDepartment()
    {
        $arRes = [];

        if (isset($_REQUEST['department']) && intval($_REQUEST['department']) > 0) {
            $intDepartmentID = intval($_REQUEST['department']);

            $arSelect = ['ID', 'IBLOCK_ID', 'CODE', 'NAME', 'PROPERTY_ATT_SALARY'];
            $arFilter = [
              'IBLOCK_ID' => self::IBLOCK_ID_KPI_WORK_POSITIONS,
              'ACTIVE' => 'Y',
              'PROPERTY_ATT_DEPARTMENT' => $intDepartmentID
            ];
            $rs = CIBlockElement::GetList(['sort' => 'asc'], $arFilter, false, false, $arSelect);
            while ($arFields = $rs->GetNext()) {
                $arRes[$arFields['ID']]['NAME'] = $arFields['NAME'];
                $arRes[$arFields['ID']]['SALARY'] = $arFields['PROPERTY_ATT_SALARY_VALUE'];
            }
        }

        return $arRes;
    }


    /**
     * WARNING: Метод изменяет данные в ИБ без подтверждения.
     *
     *
     * Получает массив структуры ГАУ ТО «Центр информационных технологий» из общего инфоблока
     * Добавляет должности в новый инфоблок
     *
     * @return void
     * @throws Exception Без комментариев.
     */
    public function makeWorkPositions()
    {
        global $USER;
        $logger = new Logger('makeWorkPositions()');
        $logger->pushHandler(new StreamHandler($_SERVER['DOCUMENT_ROOT'] . '/local/logs/test-kpi/kpi_make_work_positions.log', Logger::INFO));

        $this->getCitSections();
        $this->getAllUsers();

        $arWorkPositions = [];
        $intWWP = 0;

        foreach ($this->arUsers as $user) {
            if ($user['WORK_POSITION']) {
                $user['WORK_POSITION'] = $this->changeFirstLetter(htmlspecialcharsback(trim($user['WORK_POSITION'])));

                if (!in_array($user['WORK_POSITION'], $arWorkPositions[$user['UF_DEPARTMENT'][0]])) {
                    $arWorkPositions[$user['UF_DEPARTMENT'][0]][] = $user['WORK_POSITION'];
                }
            } else {
                $logger->info('Отсутствует должность у пользователя', ['$user' => $user]);
                $intWWP++;
            }
        }

        foreach ($arWorkPositions as $departmentID => $arWorkPosition) {
            foreach ($arWorkPosition as $workPosition) {
                $objWorkPosition = new CIBlockElement();

                $arLoad = array(
                  "MODIFIED_BY"         => $USER->GetID(),
                  "IBLOCK_SECTION_ID"   => false,
                  "IBLOCK_ID"           => self::IBLOCK_ID_KPI_WORK_POSITIONS,
                  "PROPERTY_VALUES"     => ['ATT_DEPARTMENT' => $departmentID],
                  "NAME"                => $workPosition,
                  "CODE"                => CUtil::translit($workPosition . '_' . $departmentID, 'ru', $this->arTranslitParams),
                  "ACTIVE"              => "Y",
                );

                if ($id = $objWorkPosition->Add($arLoad)) {
                    $logger->info('Добавлена должность с ID: ' . $id, ['$arLoad' => $arLoad]);
                } else {
                    $logger->info('Ошибка добавления должности ' . $objWorkPosition->LAST_ERROR, ['$arLoad' => $arLoad]);
                }
            }
        }

        $logger->info('ИТОГ', ['count_all_users' => count($this->arUsers), 'without_work_position' => $intWWP]);
    }


    /**
     * WARNING: Метод изменяет данные в пользовательском поле UF_KPI_TEST_WORK_POSITION без подтверждения.
     * Привязка осуществляется по доп полю UF_KPI_TEST_WORK_POSITION в объекте USER
     *
     * @throws Exception Без комментариев.
     * @return void
     */
    public function updateUserWorkPosition()
    {
        $this->getCitSections();
        $this->getAllUsers();


        $loggerDebug = new Logger('USER_UF_KPI_WORK_POSITION');
        $loggerDebug->pushHandler(new StreamHandler($_SERVER['DOCUMENT_ROOT'] . '/local/logs/test-kpi/kpi_add_work_position.log', Logger::INFO));

        foreach ($this->arUsers as $key => $user) {
            $objUser = new CUser();
            $intWorkPositionID = 0;

            foreach ($user['UF_DEPARTMENT'] as $departmentID) {
                $arSelectWP = ["ID", "IBLOCK_ID", "NAME", "IBLOCK_SECTION_ID"];
                $arFilterWP = [
                  "IBLOCK_ID"               => self::IBLOCK_ID_KPI_WORK_POSITIONS,
                  "ACTIVE"                  => "Y",
                  "NAME"                    => htmlspecialcharsBack($user['WORK_POSITION']),
                  "PROPERTY_ATT_DEPARTMENT" => intval($departmentID)
                ];

                $rsWP = CIBlockElement::GetList(['sort' => 'asc'], $arFilterWP, false, false, $arSelectWP);
                while ($arFieldsWP = $rsWP->GetNext()) {
                    $intWorkPositionID = $arFieldsWP['ID'];
                }
            }

            if ($intWorkPositionID == 0) {
                $arLogData = [
                  'ID' => $user['ID'],
                  'LAST_NAME' => $user['LAST_NAME'],
                  'WORK_POSITION' => $user['WORK_POSITION'],
                  'UF_DEPARTMENT_KPI' => $user['UF_DEPARTMENT_KPI'],
                  'UF_DEPARTMENT' => $user['UF_DEPARTMENT']
                ];

                $loggerDebug->info('ERROR', ['$arLogData' => $arLogData]);
            } elseif ($intWorkPositionID > 0) {
                $arUpdateUser = ['UF_KPI_TEST_WORK_POSITION' => $intWorkPositionID];
                $objUser->Update($key, $arUpdateUser);
            }
        }
    }


    /**
     * Возвращает массив всех пользователей для выбора из другого отдела
     * @return array
     */
    public function prepareAllUsersToSelect(bool $selfDepartment = false): array
    {
        $this->getCitSections();
        $this->getAllUsers(false);
//        pre($this->intCurrentDepartmentByUser);

//        pre($this->arUsers);

        $arData = [];
        foreach ($this->arUsers as $id => $arUser) {
            if ($arUser['UF_DEPARTMENT'][0] != $this->intCurrentDepartmentByUser || $selfDepartment) {
//                pre($arUser);
                $arUser['WORK_POSITION'] = '';
                $arUser['FULL_NAME'] = $this->formatName(
                    [$arUser['LAST_NAME'], $arUser['NAME'], $arUser['SECOND_NAME']]
                );
                $arData[$id] = $arUser;

                $arSelectFU = [
                  'ID',
                  'IBLOCK_ID',
                  'CODE',
                  'PROPERTY_ATT_DEPARTMENT',
                  'PROPERTY_ATT_OTHER_DEPARTMENT',
                  'PROPERTY_ATT_WORK_POSITION'
                ];
                $arFilterFU = [
                  'IBLOCK_ID' => self::IBLOCK_ID_KPI_USERS,
                  'ACTIVE' => 'Y',
                  'CODE' => $id,
                ];
                $rsFU = CIBlockElement::GetList(['sort' => 'asc'], $arFilterFU, false, false, $arSelectFU);
                while ($arFieldsFU = $rsFU->GetNext()) {
                    if ($arFieldsFU['PROPERTY_ATT_WORK_POSITION_VALUE']) {
                        $rsUserWP = CIBlockElement::GetByID($arFieldsFU['PROPERTY_ATT_WORK_POSITION_VALUE']);
                        if ($arUserWP = $rsUserWP->GetNext()) {
                            $arData[$id]['WORK_POSITION'] = $arUserWP['NAME'];
                        }
                        $rsUserDepartment = CIBlockSection::GetByID($arFieldsFU['PROPERTY_ATT_DEPARTMENT_VALUE']);
                        if ($arUserDepartment = $rsUserDepartment->GetNext()) {
                            $arData[$id]['DEPARTMENT_NAME'] = $arUserDepartment['NAME'];
                            $arData[$id]['DISABLED'] = true;
                        }
                    }

                    if ($arFieldsFU['PROPERTY_ATT_OTHER_DEPARTMENT_VALUE'] == $this->intCurrentDepartmentByUser) {
                        unset($arData[$id]);
                    }
                }
            }
        }

        return $arData;
    }


    /**
     * Возвращает массив с данными начальника управления
     * @param integer $departmentID ID отдела.
     * @return array
     */
    public function getDirectorByDepartment(int $departmentID): array
    {
        $arRes = [];

        $by = 'id';
        $order = 'desc';
        $filter = ['ACTIVE' => 'Y', 'UF_DEPARTMENT' => $departmentID];
        $arParams = [
          'FIELDS' => ['WORK_POSITION', 'LAST_NAME', 'NAME', 'SECOND_NAME', 'ID'],
          'SELECT' => ['UF_DEPARTMENT', 'UF_KPI_TEST_WORK_POSITION']
        ];

        $rsUsers = CUser::GetList($by, $order, $filter, $arParams);
        while ($arUser = $rsUsers->Fetch()) {
            if (stristr($arUser['WORK_POSITION'], 'начальник управления')) {
                $arRes['WORK_POSITION'] = $arUser['WORK_POSITION'];
                $arRes['NSL'] = "{$arUser['NAME']} {$arUser['SECOND_NAME']} {$arUser['LAST_NAME']}";
            }
        }

        return $arRes;
    }


    /**
     * Возвращает масив данных для всех отделов управления относительно авторизованного пользователя
     * @return array Масив данных для всех отделов управления относительно авторизованного пользователя.
     */
    public function getDataGovernment(): array
    {
        $arRes = [];

        $arGovernment = $this->getCurrentUserGov();


        $arDepartments = $arGovernment['DEPARTMENTS'];

        foreach ($arDepartments as $id => $department) {
            $arRes['GOVERNMENT'] = $arGovernment['GOVERNMENT'];
            $arRes['GOVERNMENT_NAME'] = $arGovernment['GOVERNMENT_NAME'];
            $arRes['DEPARTMENT_DATA'][$id] = $this->getDataDepartment($id);
            $arRes['DEPARTMENT_DATA'][$arRes['GOVERNMENT']] = $this->getDataDepartment($arRes['GOVERNMENT']);
            $arRes['CURRENT_DEPARTMENT'][$id]['NAME'] = $department;
            $arRes['CURRENT_DEPARTMENT'][$arRes['GOVERNMENT']]['NAME'] = $arRes['GOVERNMENT_NAME'];
        }

        return $arRes;
    }


    /**
     * Возвращает массив данных по управлению относительно авторизованного пользователя
     * @return array
     */
    public function getCurrentUserGov(): array
    {
        $this->getCitSections();

        global $USER;
        $arRes = [];


        $rsUser = CUser::GetByID($USER->GetID());
        $arUser = $rsUser->Fetch();
        $this->intCurrentDepartmentByUser = intval($arUser['UF_DEPARTMENT'][0]);

//pre($this->intCurrentDepartmentByUser); //109

//        if ($this->intCurrentDepartmentByUser == 57) {
//            $this->intCurrentDepartmentByUser = intval($this->strGISID);
//        }

//        pre($this->arCitSectionsDepth);

        foreach ($this->arCitSectionsDepth['child'] as $idGov => $departments) {
//            if (in_array($this->intCurrentDepartmentByUser, array_keys($departments['child'])) || $this->intCurrentDepartmentByUser == $idGov) {
//                pre('!!!!!');
//                pre($departments);
                $this->recursiveDeps($arRes, $departments);
//            }
        }
        return $arRes;
    }


    /**
     * Рекурсивно обходит все отделы управления.
     *
     * @param array $arRes Создаваемый массив.
     * @param array $departments Массив отделов по которым нужно пройтись.
     */
    public function recursiveDeps(array &$arRes, array &$departments)
    {
        if ($departments['child']) {
            foreach ($departments['child'] as $id => $department) {
                $arRes['DEPARTMENTS'][$id] = $department['NAME'];
                $arRes['GOVERNMENT'] = $department['IBLOCK_SECTION_ID'];
                $arRes['GOVERNMENT_NAME'] = $departments['NAME'];
                if ($department['child']) {
                    $this->recursiveDeps($arRes, $department);
                }
            }
        } else {
            $arRes['DEPARTMENTS'][$departments['ID']] = $departments['NAME'];
            $arRes['GOVERNMENT'] = $departments['IBLOCK_SECTION_ID'];
            $arRes['GOVERNMENT_NAME'] = $departments['NAME'];

        }


    }


    /**
     * Высчитывает итоговое значение KPI по формулам
     * @param  integer $intUserIDs ID пользователя.
     * @param  boolean $realValue  Применять ограничения или нет.
     * @return float
     * @throws Exception Комментарий.
     */
    public function calculateKPIValue(int $intUserIDs, bool $realValue, $log = true): float
    {
        if ($log) {
            $logger = new Logger('calculateKPIValue');
            $logger->pushHandler(new StreamHandler($_SERVER['DOCUMENT_ROOT'] . '/local/logs/test-kpi/kpi_update_user_kpi.log', Logger::INFO));
        }



        $intWorkPositionID = 0;
        $arFilterKPIIds = [];
        $arDataWP = [];

        $strCriticalNumber = '';
        $strFactValue = '0';
        $boolProgress = false;

        $arFormulaCritical = [];
        $arFormulaProgress = [];

        $strProgressValue = '';


        $arSelectSectionExt = ["UF_KPI_TEST_FORMULA_EXT_CRITICAL", "UF_KPI_TEST_FORMULA_EXT_PROGRESS", "UF_KPI_TEST_PROGRESS"];
        $arFilterSectionExt = ["IBLOCK_ID" => self::IBLOCK_ID_KPI, "ID" => self::SECTION_KPI_EXTRA];

        $rsWPF = CIBlockSection::GetList(['sort' => 'asc'], $arFilterSectionExt, false, $arSelectSectionExt);
        while ($arWPF = $rsWPF->GetNext()) {
            $arFormulaCritical = explode(',', $arWPF['UF_KPI_TEST_FORMULA_EXT_CRITICAL']);
            $arFormulaProgress = explode(',', $arWPF['UF_KPI_TEST_FORMULA_EXT_PROGRESS']);
            $strProgressValue = $arWPF['UF_KPI_TEST_PROGRESS'];
        }

        $arLoggerData = [
          '$arFormulaCritical' => $arFormulaCritical,
          '$arFormulaProgress' => $arFormulaProgress,
          '$strProgressValue' => $strProgressValue,
        ];

        if ($log) {
            $logger->info('CALC', ['$arLoggerData' => $arLoggerData]);
        }

        $arSelect = [
          "ID",
          "IBLOCK_ID",
          "NAME",
          "PROPERTY_ATT_VALUE_KPI",
          "PROPERTY_ATT_WORK_POSITION",
          "PROPERTY_ATT_KPI_CRITICAL",
          "PROPERTY_ATT_KPI_PROGRESS",
        ];
        $arFilter = ["IBLOCK_ID" => self::IBLOCK_ID_KPI_USERS, "ACTIVE" => "Y", "ID" => $intUserIDs];
        $rs = CIBlockElement::GetList(['sort' => 'asc'], $arFilter, false, false, $arSelect);
        while ($arFields = $rs->GetNext()) {
            $intWorkPositionID = intval($arFields['PROPERTY_ATT_WORK_POSITION_VALUE']);
            foreach ($arFields['PROPERTY_ATT_VALUE_KPI_VALUE'] as $count => $idKPI) {
                $strValueKPI = unserialize(htmlspecialcharsback($arFields['PROPERTY_ATT_VALUE_KPI_DESCRIPTION'][$count]));
                if ($strValueKPI == false) {
                    $strValueKPI = '0';
                }
                $arFilterKPIIds[$idKPI] = $strValueKPI;
            }

            if ($arFields['PROPERTY_ATT_KPI_CRITICAL_VALUE']) {
                $strCriticalNumber = $arFields['PROPERTY_ATT_KPI_CRITICAL_VALUE'];
            }
            if ($arFields['PROPERTY_ATT_KPI_PROGRESS_VALUE'] == 'Y') {
                $boolProgress = true;
            }
        }

        $arLoggerData = [
          '$arFilterKPIIds' => $arFilterKPIIds,
          '$strCriticalNumber' => $strCriticalNumber,
          '$boolProgress' => $boolProgress,
          '$intWorkPositionID' => $intWorkPositionID,
        ];

        if ($log) {
            $logger->info('CALC2', ['$arLoggerData' => $arLoggerData]);
        }
        if ($strCriticalNumber != '') {
            $arSelectExtKPI = ["ID", "IBLOCK_ID", "NAME", "PROPERTY_ATT_LABEL", "PROPERTY_ATT_FACT", ];
            $arFilterExtKPI = ["IBLOCK_ID" => self::IBLOCK_ID_KPI, "ACTIVE" => "Y", "IBLOCK_SECTION_ID" => self::SECTION_KPI_EXTRA, "PROPERTY_ATT_LABEL" => $strCriticalNumber];
            $rsExtKPI = CIBlockElement::GetList(['sort' => 'asc'], $arFilterExtKPI, false, false, $arSelectExtKPI);
            while ($arFieldsExtKPI = $rsExtKPI->GetNext()) {
                $strFactValue = $arFieldsExtKPI['PROPERTY_ATT_FACT_VALUE'];
            }
        }


        if ($intWorkPositionID > 0) {
            $arSelect = ["ID", "IBLOCK_ID", "NAME", "PROPERTY_ATT_FORMULA", "PROPERTY_ATT_FORMULA_INDICATORS", "PROPERTY_ATT_SALARY"];
            $arFilter = ["IBLOCK_ID" => self::IBLOCK_ID_KPI_STRUCT, "ACTIVE" => "Y", "ID" => $intWorkPositionID];
            $rs = CIBlockElement::GetList(['sort' => 'asc'], $arFilter, false, false, $arSelect);
            while ($arFields = $rs->GetNext()) {
                $arDataWP['NAME'] = $arFields['NAME'];
                $arDataWP['FORMULA'] = $arFields['PROPERTY_ATT_FORMULA_VALUE'];
                $arDataWP['SALARY'] = $arFields['PROPERTY_ATT_SALARY_VALUE'];
            }
        }

        $arFormula = explode(',', $arDataWP['FORMULA']);

        $arLoggerData = [
          '$arFormula' => $arFormula,
          '$arDataWP' => $arDataWP,
          '$strFactValue' => $strFactValue,
        ];
        if ($log) {
            $logger->info('CALC3', ['$arLoggerData' => $arLoggerData]);
        }
        $arFormulaLabel = [];

        $arSelectKPI = ["ID", "IBLOCK_ID", "NAME", "PROPERTY_ATT_WEIGHT", "PROPERTY_ATT_TARGET_VALUE", "PROPERTY_ATT_LABEL"];
        $arFilterKPI = ["IBLOCK_ID" => self::IBLOCK_ID_KPI, "ACTIVE" => "Y", "ID" => array_keys($arFilterKPIIds)];
        $rsKPI = CIBlockElement::GetList(['sort' => 'asc'], $arFilterKPI, false, false, $arSelectKPI);
        while ($arFieldsKPI = $rsKPI->GetNext()) {
            $arCalculatesKPI[$arFieldsKPI['PROPERTY_ATT_LABEL_VALUE']] = [
              'ВЕС' => $arFieldsKPI['PROPERTY_ATT_WEIGHT_VALUE'],
              'ФАКТ' => $arFilterKPIIds[$arFieldsKPI['ID']],
              'ПЛАН' => $arFieldsKPI['PROPERTY_ATT_TARGET_VALUE_VALUE'],
            ];
        }



        $strFormula = '';
        foreach ($arFormula as $F) {
            if (stristr($F, '_')) {
                // $arNamesFormula[0] ВЕС ФАКТ ПЛАН
                // $arNamesFormula[1] K
                $arNamesFormula = explode('_', $F);

                $strFormula .= $arCalculatesKPI[$arNamesFormula[1]][$arNamesFormula[0]];
            } else {
                $strFormula .= $F;
            }
        }

        $floatPercentCompleteKPI = $this->eval($strFormula);


        $arLoggerData = [
          '$arFilterKPIIds' => $arFilterKPIIds,
          '$strFormula' => $strFormula,
          '$arFormula' => $arFormula,
          '$arCalculatesKPI' => $arCalculatesKPI,
        ];
        if ($log) {
            $logger->info('CALC4', ['$arLoggerData' => $arLoggerData]);
        }
        $floatBaseKPI = $this->eval($strFormula);

        $arLinkFC = [
          'B' => $floatBaseKPI,
          'N' => $strFactValue
        ];

        $strCriticalFormula = '';


        foreach ($arFormulaCritical as $FC) {
            if ($arLinkFC[$FC] || $arLinkFC[$FC] == '0') {
                $strCriticalFormula .= $arLinkFC[$FC];
            } else {
                $strCriticalFormula .= $FC;
            }
        }

        $strResultFormula = $strCriticalFormula;

        if ($boolProgress) {
            $strProgressFormula = '';

            $arLinkFP = [
              'K' => $strResultFormula,
              'R' => $strProgressValue
            ];

            foreach ($arFormulaProgress as $FP) {
                if ($arLinkFP[$FP]) {
                    $strProgressFormula .= $arLinkFP[$FP];
                } else {
                    $strProgressFormula .= $FP;
                }
            }

            $strResultFormula = $strProgressFormula;
        }



        $floatPercentComplete = $this->eval($strResultFormula);
        CIBlockElement::SetPropertyValuesEx($intUserIDs, self::IBLOCK_ID_KPI_USERS, array('ATT_PERCENT_COMPLETE' => $floatPercentComplete));

        $arLoggerData = [
          '$strResultFormula' => $strResultFormula,
          '$floatPercentComplete' => $floatPercentComplete,
        ];

        if ($log) {
            $logger->info('CALC5', ['$arLoggerData' => $arLoggerData]);
        }
        return $this->individualRulesChangeFormula($strResultFormula, $intUserIDs, $floatPercentCompleteKPI, $realValue, $log);



    }


    /**
     * Изменяет итоговое значения KPI по условиям
     *
     * @param string  $strFormula      Формула KPI.
     * @param integer $userIDFromKPI   ID пользователя.
     * @param float   $floatPercentKPI Процент выполнения KPI.
     * @param boolean $realValue       Применять ограничения или нет.
     * @return float
     * @throws Exception Комментарий.
     */
    public function individualRulesChangeFormula(string $strFormula, int $userIDFromKPI, float $floatPercentKPI, bool $realValue, bool $log = true): float
    {


        $floatFakeValue = 0;
        $userID = 0;
        $logger = new Logger('calculateKPIValue');
        $logger->pushHandler(new StreamHandler($_SERVER['DOCUMENT_ROOT'] . '/local/logs/test-kpi/kpi_update_user_kpi.log', Logger::INFO));

        $rsUserFromKPI = CIBlockElement::GetByID($userIDFromKPI);
        if ($arUserFromKPI = $rsUserFromKPI->GetNext()) {
            $userID = intval($arUserFromKPI['CODE']);
        }

        $strRatio = '0.65';
        if ($userID == intval($this->strHeadOfDepartmentID)) {
            $strRatio = '0.24';
        }

        $strFormulaResult = $this->eval("$strFormula");
        $floatResultValue = $this->eval("$strFormulaResult*$strRatio/120*100");
        $floatResultValue = round($floatResultValue, 2);

        $arLoggerData = [
          '$strFormula' => $strFormula,
          '$userID' => $userID,
          '$strRatio' => $strRatio,
          '$floatResultValue' => $floatResultValue,
        ];

        if ($log) {
            $logger->info('CALC6', [$arLoggerData]);
        }
        if ($realValue) {
            $floatFakeValue = $floatResultValue;
        }


        if ($userID == intval($this->strHeadOfDepartmentID)) {

            if ($floatPercentKPI < 70) {
                return $floatFakeValue;
            } else {
               return $floatResultValue;
            }

        } elseif ($floatResultValue > 65) {
            if ($realValue) {
                return $floatFakeValue;
            } else {
                return 65;
            }
        }
        elseif ($floatResultValue < 40) {
            return $floatFakeValue;
        }
        else {
            return $floatResultValue;
        }

    }





    /**
     * Возвращает данные по отделу
     * @param  integer $intDepartmentID ID отдела.
     * @return array
     */
    public function getDataDepartment(int $intDepartmentID)
    {
        $arRes = [];

        $arWorkPositionNames = [];
        $arWorkPositionFormulas = [];
        $arWorkPositionSalary = [];
        $arKPILabels = [];
        $arExtKPI = [];
        $arWPNames = [];
        $arWPSalary = [];

        $arSelectExtKPI = ["ID", "IBLOCK_ID", "PROPERTY_ATT_LABEL"];
        $arFilterExtKPI = [
          "IBLOCK_ID" => self::IBLOCK_ID_KPI,
          "ACTIVE" => "Y",
          "IBLOCK_SECTION_ID" => self::SECTION_KPI_EXTRA
        ];
        $rsExtKPI = CIBlockElement::GetList(
            ['sort' => 'asc'],
            $arFilterExtKPI,
            false,
            false,
            $arSelectExtKPI
        );
        while ($arFieldsExtKPI = $rsExtKPI->GetNext()) {
            $arExtKPI[$arFieldsExtKPI['ID']] = $arFieldsExtKPI['PROPERTY_ATT_LABEL_VALUE'];
        }

        $arSelect = [
          "ID",
          "IBLOCK_ID",
          "NAME",
          "CODE",
          "PROPERTY_ATT_WORK_POSITION",
          "PROPERTY_ATT_VALUE_KPI",
          "PROPERTY_ATT_KPI_CRITICAL",
          "PROPERTY_ATT_KPI_PROGRESS",
          "PROPERTY_ATT_RESULT_KPI",
          "PROPERTY_ATT_SALARY",
          "PROPERTY_ATT_COMMENT",
          "PROPERTY_ATT_OTHER_DEPARTMENT"
        ];

        $strParentSectionID = '';

        $arFilter = [
          "IBLOCK_ID" => self::IBLOCK_ID_KPI_USERS,
          "ACTIVE" => "Y",
          "!PROPERTY_ATT_WORK_POSITION" => [false, 0],
          [
            'LOGIC' => 'OR',
            'PROPERTY_ATT_DEPARTMENT' => $intDepartmentID,
            'PROPERTY_ATT_OTHER_DEPARTMENT' => $intDepartmentID,
          ],


        ];
        $rs = CIBlockElement::GetList(['sort' => 'asc'], $arFilter, false, false, $arSelect);
        while ($arFields = $rs->GetNext()) {
            $intOtherDepartment = intval($arFields['PROPERTY_ATT_OTHER_DEPARTMENT_VALUE']);

            if ($intOtherDepartment > 0 && $intOtherDepartment != $intDepartmentID) {
// TODO как переписать выражение?
            } else {
                $rsUser = CUser::GetByID(intval($arFields['CODE']));
                $arUser = $rsUser->Fetch();

                $intUserID = intval($arFields['ID']);


                // повторный расчет результата всех KPI перед показом данных для обновления интегрального KPI
                $floatResultKPIValue = $this->calculateKPIValue($intUserID, true, false);

                if (floatval($floatResultKPIValue) > 0) {
                    CIBlockElement::SetPropertyValuesEx(
                      $intUserID,
                      self::IBLOCK_ID_KPI_USERS,
                      ['ATT_RESULT_KPI' => $floatResultKPIValue]
                    );
                } else {
                    CIBlockElement::SetPropertyValuesEx($intUserID, self::IBLOCK_ID_KPI_USERS, ['ATT_RESULT_KPI' => '0']);
                }

                $arRes[$arFields['PROPERTY_ATT_WORK_POSITION_VALUE']][$arFields['ID']]['NAME'] = $arFields['NAME'];
                $arRes[$arFields['PROPERTY_ATT_WORK_POSITION_VALUE']][$arFields['ID']]['WPID'] = intval($arUser['UF_KPI_TEST_WORK_POSITION']);
                $arRes[$arFields['PROPERTY_ATT_WORK_POSITION_VALUE']][$arFields['ID']]['ATT_SALARY'] = $arFields['PROPERTY_ATT_SALARY_VALUE'];
                $arRes[$arFields['PROPERTY_ATT_WORK_POSITION_VALUE']][$arFields['ID']]['ATT_COMMENT'] = $arFields['PROPERTY_ATT_COMMENT_VALUE'];
                $arRes[$arFields['PROPERTY_ATT_WORK_POSITION_VALUE']][$arFields['ID']]['ATT_KPI_CRITICAL'] = $arFields['PROPERTY_ATT_KPI_CRITICAL_VALUE'];
                $arRes[$arFields['PROPERTY_ATT_WORK_POSITION_VALUE']][$arFields['ID']]['ATT_KPI_PROGRESS'] = $arFields['PROPERTY_ATT_KPI_PROGRESS_VALUE'];
                $arRes[$arFields['PROPERTY_ATT_WORK_POSITION_VALUE']][$arFields['ID']]['ATT_RESULT_KPI'] = $arFields['PROPERTY_ATT_RESULT_KPI_VALUE'];


                if ($arFields['PROPERTY_ATT_VALUE_KPI_VALUE'][0]) {
                    foreach ($arFields['PROPERTY_ATT_VALUE_KPI_VALUE'] as $keyKPI => $kpiValueID) {

                        if (intval($kpiValueID) > 0) {
                            $strManualInput = '';
                            $strManualInputName = '';
                            $strDataSourceName = '';
                            $strWeight = '';
                            $strTargetValue = '';
                            $strIsRed = 'N';

                            $arSelectKPIValue = [
                              "ID",
                              "IBLOCK_ID",
                              "NAME",
                              "PROPERTY_ATT_MANUAL_INPUT",
                              "PROPERTY_ATT_WEIGHT",
                              "PROPERTY_ATT_TARGET_VALUE",
                              "PROPERTY_ATT_DATA_SOURCE",
                            ];
                            $arFilterKPIValue = [
                              "IBLOCK_ID" => self::IBLOCK_ID_KPI,
                              "ACTIVE" => "Y",
                              "ID" => intval($kpiValueID)
                            ];
                            $rsKPIValue = CIBlockElement::GetList(
                                ['sort' => 'asc'],
                                $arFilterKPIValue,
                                false,
                                false,
                                $arSelectKPIValue
                            );
                            while ($arFieldsKPIValue = $rsKPIValue->GetNext()) {


                                if (!in_array($arFieldsKPIValue['NAME'], $arKPILabels[$arFields['PROPERTY_ATT_WORK_POSITION_VALUE']]) && $arFieldsKPIValue['NAME']) {
                                    $strManualInput = $arFieldsKPIValue['PROPERTY_ATT_MANUAL_INPUT_VALUE'];
                                    $strDataSourceName = $arFieldsKPIValue['PROPERTY_ATT_DATA_SOURCE_VALUE'];
                                    $strManualInputName = $arFieldsKPIValue['NAME'];
                                    $strWeight = $arFieldsKPIValue['PROPERTY_ATT_WEIGHT_VALUE'];
                                    $strTargetValue = $arFieldsKPIValue['PROPERTY_ATT_TARGET_VALUE_VALUE'];

                                    $arKPILabels[$arFields['PROPERTY_ATT_WORK_POSITION_VALUE']][$arFieldsKPIValue['ID']]['ID'] = $kpiValueID;
                                    $arKPILabels[$arFields['PROPERTY_ATT_WORK_POSITION_VALUE']][$arFieldsKPIValue['ID']]['NAME'] = $strManualInputName;
                                    $arKPILabels[$arFields['PROPERTY_ATT_WORK_POSITION_VALUE']][$arFieldsKPIValue['ID']]['EDITABLE'] = $strManualInput;
                                    $arKPILabels[$arFields['PROPERTY_ATT_WORK_POSITION_VALUE']][$arFieldsKPIValue['ID']]['TARGET'] = $strTargetValue;
                                }
                            }

                            $strFactKPIValue = unserialize(
                                htmlspecialcharsback($arFields['PROPERTY_ATT_VALUE_KPI_DESCRIPTION'][$keyKPI])
                            );


                            $arRes[$arFields['PROPERTY_ATT_WORK_POSITION_VALUE']][$arFields['ID']]['ATT_VALUE_KPI'][$keyKPI]['ID'] = $kpiValueID;
                            $arRes[$arFields['PROPERTY_ATT_WORK_POSITION_VALUE']][$arFields['ID']]['ATT_VALUE_KPI'][$keyKPI]['NAME'] = $strManualInputName;
                            $arRes[$arFields['PROPERTY_ATT_WORK_POSITION_VALUE']][$arFields['ID']]['ATT_VALUE_KPI'][$keyKPI]['VALUE'] = $strFactKPIValue;
                            $arRes[$arFields['PROPERTY_ATT_WORK_POSITION_VALUE']][$arFields['ID']]['ATT_VALUE_KPI'][$keyKPI]['EDITABLE'] = $strManualInput;
                            $arRes[$arFields['PROPERTY_ATT_WORK_POSITION_VALUE']][$arFields['ID']]['ATT_VALUE_KPI'][$keyKPI]['WEIGHT'] = $strWeight;
                            $arRes[$arFields['PROPERTY_ATT_WORK_POSITION_VALUE']][$arFields['ID']]['ATT_VALUE_KPI'][$keyKPI]['TARGET_VALUE'] = $strTargetValue;
                            $arRes[$arFields['PROPERTY_ATT_WORK_POSITION_VALUE']][$arFields['ID']]['ATT_VALUE_KPI'][$keyKPI]['DATA_SOURCE'] = $strDataSourceName;
                            if (intval($strFactKPIValue) < intval($strTargetValue)) {
                                $strIsRed = 'Y';
                            }
                            $arRes[$arFields['PROPERTY_ATT_WORK_POSITION_VALUE']][$arFields['ID']]['ATT_VALUE_KPI'][$keyKPI]['IS_RED'] = $strIsRed;
                        }
                    }
                }


                $arSelectFE = ['ID', 'IBLOCK_ID', 'NAME', 'PROPERTY_ATT_SALARY', 'PROPERTY_ATT_FORMULA'];
                $arFilterFE = [
                  "IBLOCK_ID" => self::IBLOCK_ID_KPI_STRUCT,
                  "ACTIVE" => "Y",
                  'ID' => intval($arFields['PROPERTY_ATT_WORK_POSITION_VALUE'])
                ];
                $rsFE = CIBlockElement::GetList(['sort' => 'asc'], $arFilterFE, false, false, $arSelectFE);
                while ($arFieldsFE = $rsFE->GetNext()) {
                    $arWorkPositionNames[$arFields['PROPERTY_ATT_WORK_POSITION_VALUE']] = $arFieldsFE['NAME'];
                    $arWorkPositionSalary[$arFields['PROPERTY_ATT_WORK_POSITION_VALUE']] = $arFieldsFE['PROPERTY_ATT_SALARY_VALUE'];
                    $arWorkPositionFormulas[$arFields['PROPERTY_ATT_WORK_POSITION_VALUE']] = str_replace(
                        ',',
                        ' ',
                        $arFieldsFE['PROPERTY_ATT_FORMULA_VALUE']
                    );
                }


                $arSelectWP = ['ID', 'IBLOCK_ID', 'NAME', 'PROPERTY_ATT_SALARY'];
                $arFilterWP = [
                  'IBLOCK_ID' => self::IBLOCK_ID_KPI_WORK_POSITIONS,
                  'ACTIVE' => 'Y',
                  'PROPERTY_ATT_DEPARTMENT' => $intDepartmentID
                ];
                $rsWP = CIBlockElement::GetList(['sort' => 'asc'], $arFilterWP, false, false, $arSelectWP);
                while ($arFieldsWP = $rsWP->GetNext()) {
                    $arWPNames[$arFieldsWP['ID']] = $arFieldsWP['NAME'];
                    $arWPSalary[$arFieldsWP['ID']] = $arFieldsWP['PROPERTY_ATT_SALARY_VALUE'];
                }
            }
        }


//        pre([
//              'RESULT' => $arRes,
//              'WP_NAMES' => $arWorkPositionNames,
//              'WP_FORMULAS' => $arWorkPositionFormulas,
//              'WP_SALARY' => $arWorkPositionSalary,
//              'KPI_LABELS' => $arKPILabels,
//              'KPI_EXT' => $arExtKPI,
//              'WP_NAMES_REAL' => $arWPNames,
//              'WP_SALARY_REAL' => $arWPSalary,
//            ]);

        return [
          'RESULT' => $arRes,
          'WP_NAMES' => $arWorkPositionNames,
          'WP_FORMULAS' => $arWorkPositionFormulas,
          'WP_SALARY' => $arWorkPositionSalary,
          'KPI_LABELS' => $arKPILabels,
          'KPI_EXT' => $arExtKPI,
          'WP_NAMES_REAL' => $arWPNames,
          'WP_SALARY_REAL' => $arWPSalary,
        ];
    }


    /**
     * Форматирует ФИО (Фамилия И. О.)
     * @param  string[] $arNameComponents ФИО.
     * @return string
     */
    public function formatName(array $arNameComponents)
    {
        [$lastName, $name, $secondName] = $arNameComponents;
        return $lastName . ' ' . mb_substr($name, 0, 1) . '.' . mb_substr($secondName, 0, 1) . '.';
    }


    /**
     * Записывает в переменную класса intCurrentDepartmentByUser текущий отдел относительно авторизованного пользователя
     * Возвращает название отдела
     * @return array
     */
    public function getCurrentUserDepartment()
    {
        global $USER;
        $arRes = [];

        $rsUser = CUser::GetByID($USER->GetID());
        $arUser = $rsUser->Fetch();
        $this->intCurrentDepartmentByUser = $arRes['ID'] = intval($arUser['UF_DEPARTMENT'][0]);


        if ($arRes['ID'] == 57) {
            $this->intCurrentDepartmentByUser = intval($this->strGISID);
        }

        $rsSectionStruct = CIBlockSection::GetByID($this->intCurrentDepartmentByUser);
        if ($arSectionStruct = $rsSectionStruct->GetNext()) {
            $arRes['NAME'] = $arSectionStruct['NAME'];
        }

        return $arRes;
    }


    /**
     * Сохраняет дополнительные KPI для возможности отменить изменения
     *
     * @param  boolean $boolForceMethod Безусловно сохраняет значения.
     * @return void
     */
    public function saveKPIExt(bool $boolForceMethod = false)
    {
        $arSaveKPIs = [];
        $arSelectKPIExt = [
          "ID",
          "IBLOCK_ID",
          "NAME",
          "PROPERTY_ATT_FACT",
          "PROPERTY_ATT_LABEL",
        ];

        $arFilterKPIExt = ["IBLOCK_ID" => self::IBLOCK_ID_KPI, "IBLOCK_SECTION_ID" => self::SECTION_KPI_EXTRA];
        $rsWP = CIBlockElement::GetList(['sort' => 'asc'], $arFilterKPIExt, false, false, $arSelectKPIExt);
        while ($arFields = $rsWP->GetNext()) {
            $arSaveKPIs[$arFields['ID']]['NAME'] = $arFields['NAME'];
            $arSaveKPIs[$arFields['ID']]['ATT_FACT'] = $arFields['PROPERTY_ATT_FACT_VALUE'];
            $arSaveKPIs[$arFields['ID']]['ATT_LABEL'] = $arFields['PROPERTY_ATT_LABEL_VALUE'];
        }

        $arSelectSectionExt = ["UF_KPI_TEST_FACTS_CRITICAL"];
        $arFilterSectionExt = ["IBLOCK_ID" => self::IBLOCK_ID_KPI, "ID" => self::SECTION_KPI_EXTRA];
        $arFilterSectionExt["UF_KPI_TEST_FACTS_CRITICAL"] = false;

        $rsWPF = CIBlockSection::GetList(['sort' => 'asc'], $arFilterSectionExt, false, $arSelectSectionExt);
        if ($rsWPF->SelectedRowsCount()) {
            $this->updateSection(self::SECTION_KPI_EXTRA, ["UF_KPI_TEST_FACTS_CRITICAL" => 'empty']);
        }

        $arFilterSectionExt["!UF_KPI_TEST_FACTS_CRITICAL"] = 'empty';
        if ($boolForceMethod) {
            unset($arFilterSectionExt["UF_KPI_TEST_FACTS_CRITICAL"]);
            unset($arFilterSectionExt["!UF_KPI_TEST_FACTS_CRITICAL"]);
        }

        $rsWP = CIBlockSection::GetList(['sort' => 'asc'], $arFilterSectionExt, false, $arSelectSectionExt);
        if ($rsWP->SelectedRowsCount() && count($arSaveKPIs) > 0) {
            $this->updateSection(self::SECTION_KPI_EXTRA, ["UF_KPI_TEST_FACTS_CRITICAL" => serialize($arSaveKPIs)]);
        }
    }


    /**
     * Обновляет данные привязанные к разделу ИБ
     *
     * @param  integer $intID  ID раздела.
     * @param  array   $arLoad Новые данные.
     * @return void
     */
    public function updateSection(int $intID, array $arLoad)
    {
        $objSection = new CIBlockSection();
        $objSection->Update($intID, $arLoad);
    }


    /**
     * Возвращает дополнительные правила расчета KPI
     *
     * @param  integer $intID ID.
     * @return array
     */
    public function getRulesExtra(int $intID = 0)
    {
        $arRes = [];

        $arSelect = ["ID", "IBLOCK_ID", "NAME", "PROPERTY_ATT_FACT", "PROPERTY_ATT_LABEL"];
        $arFilter = ["IBLOCK_ID" => self::IBLOCK_ID_KPI, "ACTIVE" => "Y", "IBLOCK_SECTION_ID" => self::SECTION_KPI_EXTRA ];
        if ($intID > 0) {
            $arFilter['ID'] = $intID;
        }
        $rs = CIBlockElement::GetList(['sort' => 'asc'], $arFilter, false, false, $arSelect);
        while ($arFields = $rs->GetNext()) {
            $arRes['CRITICAL'][$arFields['ID']]['NAME'] = $arFields['NAME'];
            $arRes['CRITICAL'][$arFields['ID']]['VALUE'] = $arFields['PROPERTY_ATT_FACT_VALUE'];
            $arRes['CRITICAL'][$arFields['ID']]['LABEL'] = $arFields['PROPERTY_ATT_LABEL_VALUE'];
        }

        $arFilter = array('IBLOCK_ID' => self::IBLOCK_ID_KPI, "ID" => self::SECTION_KPI_EXTRA);
        $rsSection = CIBlockSection::GetList(['sort' => 'asc'], $arFilter, false, ['UF_KPI_TEST_PROGRESS']);
        while ($arSection = $rsSection->GetNext()) {
            $arRes['PROGRESS']['VALUE'] = $arSection['UF_KPI_TEST_PROGRESS'];
        }

        return $arRes;
    }


    /**
     * Сохраняет значения KPI показателей для отмены внесенных данных
     *
     * @param boolean $boolForceMethod Безусловно сохраняет значения.
     * @throws Exception Комментарий.
     * @return void
     */
    public function saveKPI(bool $boolForceMethod = false)
    {
        if (isset($_REQUEST['work_position']) && intval($_REQUEST['work_position']) > 0) {
            $intWorkPositionID = intval($_REQUEST['work_position']);

            $arSaveKPIs = [];
            $arSelectKPI = [
              "ID",
              "IBLOCK_ID",
              "NAME",
              "PROPERTY_ATT_LABEL",
              "PROPERTY_ATT_DATA_SOURCE",
              "PROPERTY_ATT_WEIGHT",
              "PROPERTY_ATT_TARGET_VALUE",
              "PROPERTY_ATT_MANUAL_INPUT",
              "PROPERTY_ATT_WORK_POSITION",
            ];
            $arFilterKPI = ["IBLOCK_ID" => self::IBLOCK_ID_KPI, "PROPERTY_ATT_WORK_POSITION" => $intWorkPositionID];
            $rsWP = CIBlockElement::GetList(['sort' => 'asc'], $arFilterKPI, false, false, $arSelectKPI);
            while ($arFields = $rsWP->GetNext()) {
                $arSaveKPIs[$arFields['ID']]['NAME'] = $arFields['NAME'];
                $arSaveKPIs[$arFields['ID']]['ATT_LABEL'] = $arFields['PROPERTY_ATT_LABEL_VALUE'];
                $arSaveKPIs[$arFields['ID']]['ATT_DATA_SOURCE'] = $arFields['PROPERTY_ATT_DATA_SOURCE_VALUE'];
                $arSaveKPIs[$arFields['ID']]['ATT_DATA_SOURCE'] = $arFields['PROPERTY_ATT_DATA_SOURCE_ENUM_ID'];
                $arSaveKPIs[$arFields['ID']]['ATT_WEIGHT'] = $arFields['PROPERTY_ATT_WEIGHT_VALUE'];
                $arSaveKPIs[$arFields['ID']]['ATT_TARGET_VALUE'] = $arFields['PROPERTY_ATT_TARGET_VALUE_VALUE'];
                $arSaveKPIs[$arFields['ID']]['ATT_MANUAL_INPUT'] = $arFields['PROPERTY_ATT_MANUAL_INPUT_VALUE'];
                $arSaveKPIs[$arFields['ID']]['ATT_WORK_POSITION'] = $arFields['PROPERTY_ATT_WORK_POSITION_VALUE'];
            }

            $arSelectWP = ["ID", "IBLOCK_ID", "NAME", "PROPERTY_ATT_SAVED_KPI"];
            $arFilterWP = ["IBLOCK_ID" => self::IBLOCK_ID_KPI_STRUCT, "ID" => $intWorkPositionID];
            $arFilterWP["PROPERTY_ATT_SAVED_KPI"] = false;

            $rsWPF = CIBlockElement::GetList(['sort' => 'asc'], $arFilterWP, false, false, $arSelectWP);
            if ($rsWPF->SelectedRowsCount()) {
                CIBlockElement::SetPropertyValuesEx($intWorkPositionID, self::IBLOCK_ID_KPI_STRUCT, ['ATT_SAVED_KPI' => 'empty']);
            }

            $arFilterWP["!PROPERTY_ATT_SAVED_KPI"] = 'empty';
            if ($boolForceMethod) {
                unset($arFilterWP["PROPERTY_ATT_SAVED_KPI"]);
                unset($arFilterWP["!PROPERTY_ATT_SAVED_KPI"]);
                if (count($arSaveKPIs) > 0) {
                    $arSelect = ["ID", "IBLOCK_ID", "NAME", "PROPERTY_ATT_WORK_POSITION", "PROPERTY_ATT_VALUE_KPI"];
                    $arFilter = ["IBLOCK_ID" => self::IBLOCK_ID_KPI_USERS, "ACTIVE" => "Y", "PROPERTY_ATT_WORK_POSITION" => $intWorkPositionID];
                    $rs = CIBlockElement::GetList(['sort' => 'asc'], $arFilter, false, false, $arSelect);
                    while ($arFields = $rs->GetNext()) {
                        $arDefaultKPIValues = [];
                        $intCountKPIValues = 0;
                        $arAddKPIValues = array_keys($arSaveKPIs);
                        if (count($arFields['PROPERTY_ATT_VALUE_KPI_VALUE']) > 0) {
                            foreach ($arFields['PROPERTY_ATT_VALUE_KPI_VALUE'] as $key => $value) {
                                if (intval($value) > 0) {
                                    $strDescription = unserialize(htmlspecialcharsback($arFields['PROPERTY_ATT_VALUE_KPI_DESCRIPTION'][$key]));
                                    $arDefaultKPIValues[$intCountKPIValues] = ['VALUE' => intval($value), 'DESCRIPTION' => $strDescription];
                                    $intCountKPIValues++;
                                }
                            }
                            foreach ($arAddKPIValues as $id) {
                                if (!in_array($id, $arFields['PROPERTY_ATT_VALUE_KPI_VALUE']) && intval($id) > 0) {
                                    $arDefaultKPIValues[$intCountKPIValues] = ['VALUE' => intval($id), 'DESCRIPTION' => ''];
                                    $intCountKPIValues++;
                                }
                            }
                        }

                        $loggerDebug = new Logger('DEBUG');
                        $loggerDebug->pushHandler(new StreamHandler($_SERVER['DOCUMENT_ROOT'] . '/local/logs/test-kpi/kpi_debug.log', Logger::INFO));
                        $loggerDebug->info('debug', ['ATT_VALUE_KPI' => $arDefaultKPIValues]);

                        CIBlockElement::SetPropertyValuesEx(intval($arFields['ID']), self::IBLOCK_ID_KPI_USERS, ['ATT_VALUE_KPI' => $arDefaultKPIValues]);
                    }
                }
            }

            $rsWP = CIBlockElement::GetList(['sort' => 'asc'], $arFilterWP, false, false, $arSelectWP);
            if ($rsWP->SelectedRowsCount() && count($arSaveKPIs) > 0) {
                CIBlockElement::SetPropertyValuesEx($intWorkPositionID, self::IBLOCK_ID_KPI_STRUCT, ['ATT_SAVED_KPI' => serialize($arSaveKPIs)]);
            }
        }
    }


    /**
     * Обрабатывет редактирование и удаление в GRID self::GRID_ID_RULES_CHANGE
     * Создает и записывает в $this->arResult данные для работы GRID
     *
     * @throws Exception Комментарий.
     * @return mixed
     */
    public function makeGridRulesChange()
    {
        $arProtectedProps = ['ATT_LABEL', 'ATT_WORK_POSITION'];

        if (isset($_REQUEST['work_position']) && intval($_REQUEST['work_position']) > 0) {
            $intWorkPositionID = intval($_REQUEST['work_position']);

            if ($_POST["FIELDS"]) {
                $this->updateElement($arProtectedProps);
            }

            if ($_REQUEST['action_button_' . self::GRID_ID_RULES_CHANGE] == 'delete') {
                $this->deleteElements(self::IBLOCK_ID_KPI);
            }


            $this->arResult['grid_id'] = self::GRID_ID_RULES_CHANGE;
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
              'id' => 'ATT_LABEL', 'name' => '#', 'sort' => 'PROPERTY_ATT_LABEL', 'default' => true, 'editable' => false
            ];
            $this->arResult['columns'][] = [
              'id' => 'NAME', 'name' => 'Название', 'sort' => 'NAME', 'default' => true, 'editable' => true
            ];
            $this->arResult['columns'][] = [
              'id' => 'ATT_DATA_SOURCE',
              'name' => 'Источник данных',
              'sort' => 'PROPERTY_ATT_DATA_SOURCE',
              'default' => true,
              'editable' => ['TYPE' => 'DROPDOWN', 'items' => $this->getEnumFields('ATT_DATA_SOURCE')],
            ];
            $this->arResult['columns'][] = [
              'id' => 'ATT_WEIGHT', 'name' => 'Вес, %', 'sort' => 'PROPERTY_ATT_WEIGHT', 'default' => true, 'editable' => true, 'type' => 'number'
            ];
            $this->arResult['columns'][] = [
              'id' => 'ATT_TARGET_VALUE', 'name' => 'Плановое значение, %', 'sort' => 'PROPERTY_ATT_TARGET_VALUE',
              'default' => true, 'editable' => true, 'type' => 'number'
            ];
            $this->arResult['columns'][] = [
              'id' => 'ATT_MANUAL_INPUT', 'name' => 'Разрешить ручной ввод', 'sort' => 'PROPERTY_ATT_MANUAL_INPUT',
              'default' => true, 'editable' => true, 'type' => 'checkbox'
            ];

            $arSelect = array(
              "ID",
              "IBLOCK_ID",
              "PROPERTY_ATT_LABEL",
              "NAME",
              "PROPERTY_ATT_DATA_SOURCE",
              "PROPERTY_ATT_WEIGHT",
              "PROPERTY_ATT_TARGET_VALUE",
              "PROPERTY_ATT_MANUAL_INPUT",
            );

            $arFilter = array(
              "IBLOCK_ID" => self::IBLOCK_ID_KPI, "ACTIVE" => "Y", "PROPERTY_ATT_WORK_POSITION" => $intWorkPositionID
            );

            $rsKPI = \CIBlockElement::GetList($this->arResult['sort']['sort'], $arFilter, false, $this->arResult['nav_params'], $arSelect);

            $this->arResult['nav']->setRecordCount($rsKPI->selectedRowsCount());

            while ($arFieldsKPI = $rsKPI->GetNext()) {
                $bollEditableManualInput = false;

                $this->arResult['list'][] = [
                  'data' => [
                    "ID" => $arFieldsKPI['ID'],
                    "ATT_LABEL" => $arFieldsKPI['PROPERTY_ATT_LABEL_VALUE'],
                    "NAME" => $arFieldsKPI['NAME'],
                    "ATT_DATA_SOURCE" => $arFieldsKPI['PROPERTY_ATT_DATA_SOURCE_ENUM_ID'],
                    "ATT_WEIGHT" => $arFieldsKPI['PROPERTY_ATT_WEIGHT_VALUE'],
                    "ATT_TARGET_VALUE" => $arFieldsKPI['PROPERTY_ATT_TARGET_VALUE_VALUE'],
                    "ATT_MANUAL_INPUT" => $arFieldsKPI['PROPERTY_ATT_MANUAL_INPUT_VALUE'] == 'Y' ? "Y" : "N"
                  ],
                  'columns' =>
                    [
                      "ATT_DATA_SOURCE" => $arFieldsKPI['PROPERTY_ATT_DATA_SOURCE_VALUE'],
                    ],

                  'editable' => true,

                ];
            }


            /**
             * Возвращает массив с кнопкой.
             *
             * @return array
             */
            function getSaveEditButton()
            {
                $onchange = new Onchange();
                $onchange->addAction(array("ACTION" => Actions::CALLBACK, "DATA" => array(array("JS" => "customSave(self.parent)"))));
                $saveButton = new Button();

                $saveButton->setClass(DefaultValue::SAVE_BUTTON_CLASS);
                $saveButton->setText("Сохранить");
                $saveButton->setId(DefaultValue::SAVE_BUTTON_ID);
                $saveButton->setOnchange($onchange);

                return $saveButton->toArray();
            }


            /**
             * Возвращает массив с кнопкой.
             *
             * @return array
             */
            function getCancelEditButton()
            {
                $onchange = new Onchange();
                $onchange->addAction(array("ACTION" => Actions::SHOW_ALL, "DATA" => array()));
                $onchange->addAction(array("ACTION" => Actions::CALLBACK, "DATA" => array(array("JS" => "Grid.editSelectedCancel()"))));
                $onchange->addAction(array("ACTION" => Actions::REMOVE, "DATA" => array(array("ID" => DefaultValue::SAVE_BUTTON_ID), array("ID" => DefaultValue::CANCEL_BUTTON_ID))));

                $cancelButton = new Button();
                $cancelButton->setClass(DefaultValue::CANCEL_BUTTON_CLASS);
                $cancelButton->setText("Отменить");
                $cancelButton->setId(DefaultValue::CANCEL_BUTTON_ID);
                $cancelButton->setOnchange($onchange);

                return $cancelButton->toArray();
            }

            $onchange2 = new Onchange();
            $onchange2->addAction(
                [
                  "ACTION" => Actions::CREATE,
                  "DATA" => array(getSaveEditButton(), getCancelEditButton())
                ]
            );
            $onchange2->addAction(
                [
                  "ACTION" => Actions::CALLBACK,
                  "DATA" => array(array("JS" => "customEdit(self)"))
                ]
            );
            $onchange2->addAction(
                [
                  "ACTION" => Actions::HIDE_ALL_EXPECT,
                  "DATA" => array(
                  array("ID" => DefaultValue::SAVE_BUTTON_ID),
                  array("ID" => DefaultValue::CANCEL_BUTTON_ID))
                ]
            );


            $this->arResult['onchange2'] = $onchange2;
        }
    }


    /**
     * Возвращает данные KPI по должности. Значение должности берется из GET параметра work_position
     *
     * @return array
     */
    public function getDataKPIForWorkPosition()
    {
        $arDataWP = [];

        if (isset($_REQUEST['work_position']) && intval($_REQUEST['work_position']) > 0) {
            $intWorkPositionID = intval($_REQUEST['work_position']);

            $arSelect = ["ID", "IBLOCK_ID", "NAME", "PROPERTY_ATT_FORMULA", "PROPERTY_ATT_SALARY"];
            $arFilter = ["IBLOCK_ID" => self::IBLOCK_ID_KPI_STRUCT, "ACTIVE" => "Y", "ID" => $intWorkPositionID];
            $rs = \CIBlockElement::GetList(['sort' => 'asc'], $arFilter, false, false, $arSelect);
            while ($arFields = $rs->GetNext()) {
                $arDataWP['NAME'] = $arFields['NAME'];
                $arDataWP['FORMULA'] = $arFields['PROPERTY_ATT_FORMULA_VALUE'];
                $arDataWP['SALARY'] = $arFields['PROPERTY_ATT_SALARY_VALUE'];
            }
        }

        return $arDataWP;
    }

    /**
     * Использует класс для расчета значения из любого выражения представленного в виде строки
     * @param  string $strFormula Формула в строковом виде.
     * @return boolean|mixed|null
     */
    public function eval(string $strFormula)
    {
        $m = new EvalMath();
        $m->suppress_errors = true;

        return $m->e($strFormula);
    }


    /**
     * $this->arCitSections
     * Выбирает все отделы и группы без управлений, сортирует
     *
     * @return void
     */
    public function getDepartments()
    {
        $this->getCitSections();


        foreach ($this->arCitSections as $key => $section) {
            if (!stristr($section, 'управление')) {
                $this->arDepartments[$key] = $section;
            }
        }
        asort($this->arDepartments);
    }


    /**
     * Фильтрует общий массив структуры
     *
     * @param array   $arSections   Общий массив всей структуры tularegion.ru.
     * @param integer $intParentID  ID раздела ЦИТа.
     * @param array   $arCitSection Пустой для создания нужной структуры.
     * @param array   $idx          ID каждого раздела.
     * @param boolean $selfStruct   False для общей структуры true для своей.
     * @return void
     */
    public function filterSections(array $arSections, int $intParentID, array &$arCitSection = [], array &$idx = [], bool $selfStruct = false)
    {
        foreach ($arSections['child'] as $key => $section) {
            if (empty($arCitSection)) {
                $this->filterSections($section, $intParentID, $arCitSection, $idx);
            }
            if ($key == $intParentID && !$selfStruct) {
                $arCitSection = $section;
                $this->getIDS($section, $idx);

                break;
// TODO Возможен косяк (не все разделы подтянет).
            } elseif ($selfStruct) {
                $arCitSection = $section;
                $this->getIDS($section, $idx);
            }
        }
    }

    /**
     * Получает данные по разделам и записывает в:
     * $this->arCitSectionsDepth Array структура вложенным списком
     * $this->arCitSections Array структура одномерным списком
     *
     * @return void
     */
    public function getCitSections()
    {
        $arAllSections = $this->getSectionList(
            array(
            'IBLOCK_ID' => IBLOCK_ID_STRUCTURE,
            ),
            array(
            'NAME',
            'CODE',
            )
        );

        $this->filterSections(
            $arAllSections,
            self::SECTION_ID_ALL_STRUCTURE,
            $this->arCitSectionsDepth,
            $this->arCitSections
        );
    }


    /**
     * Записывает всех сотрудников из ЦИТа в $this->arUsers
     *
     * @return void
     */
    public function getAllUsers(bool $scanRequestDepartment = true)
    {
        $by = 'id';
        $order = 'desc';
        $filter = ['ACTIVE' => 'Y', 'UF_DEPARTMENT' => array_keys($this->arCitSections)];
        if (isset($_REQUEST['department']) && intval($_REQUEST['department']) > 0 && $scanRequestDepartment) {
            $filter['UF_DEPARTMENT'] = intval($_REQUEST['department']);
        }
        $arParams = [
          'FIELDS' => ['WORK_POSITION', 'LAST_NAME', 'NAME', 'SECOND_NAME', 'ID', 'DATE_REGISTER'],
          'SELECT' => ['UF_DEPARTMENT', 'UF_KPI_TEST_WORK_POSITION']
        ];

        $rsUsers = CUser::GetList($by, $order, $filter, $arParams);
        while ($arUser = $rsUsers->Fetch()) {
            $this->arUsers[$arUser['ID']] = $arUser;
        }
    }


    /**
     * Записывает сотрудников в $this->arUsersDepartment
     * Зависит от выбранного отдела $_REQUEST['department']
     *
     * @return void
     */
    public function getDepartmentUsers()
    {
        if (isset($_REQUEST['department']) && intval($_REQUEST['department']) > 0) {
            $by = 'id';
            $order = 'desc';
            $filter = ['ACTIVE' => 'Y', 'UF_DEPARTMENT' => intval($_REQUEST['department'])];
            $arParams = [
              'FIELDS' => ['WORK_POSITION', 'LAST_NAME', 'NAME', 'SECOND_NAME', 'ID'],
              'SELECT' => ['UF_DEPARTMENT']
            ];

            $rsUsers = CUser::GetList($by, $order, $filter, $arParams);
            while ($arUser = $rsUsers->Fetch()) {
                if ($arUser['WORK_POSITION']) {
                    $this->arUsersDepartment[$arUser['ID']] = $arUser;
                }
            }
        }
    }


    /**
     * Привязывает KPI к пользователю при создании KPI
     *
     * @param mixed $userID       Ид пользователя.
     * @param mixed $workPosition Ид должности.
     * @return void
     */
    public function linkKPIsToUser($userID, $workPosition)
    {
        $arNewKPIValues = [];

        $arSelect = ['ID', 'IBLOCK_ID', 'CODE'];
        $arFilter = ['IBLOCK_ID' => self::IBLOCK_ID_KPI, 'ACTIVE' => 'Y', 'PROPERTY_ATT_WORK_POSITION' => $workPosition];
        $rs = CIBlockElement::GetList(['sort' => 'asc'], $arFilter, false, false, $arSelect);
        while ($arFields = $rs->GetNext()) {
            $arNewKPIValues[] = ['VALUE' => $arFields['ID'], 'DESCRIPTION' => ''];
        }

        CIBlockElement::SetPropertyValuesEx($userID, self::IBLOCK_ID_KPI_USERS, ['ATT_VALUE_KPI' => $arNewKPIValues]);
    }


    /**
     * Сохраняет значения пользователя в отделе для возможности восстановления
     *
     * @param  integer $departmentID ID отдела.
     * @param  boolean $boolForce    Безусловно сохраняет значения.
     * @return void
     */
    public function saveDepartmentUserData(int $departmentID, bool $boolForce = false)
    {
        $arSelect = [
          'ID',
          'IBLOCK_ID',
          'PROPERTY_ATT_WORK_POSITION',
          'PROPERTY_ATT_SALARY',
          'PROPERTY_ATT_OTHER_DEPARTMENT',
          'PROPERTY_ATT_PROBATION_END',
          'PROPERTY_ATT_SAVE_DEPARTMENT_DATA',
          'PROPERTY_ATT_VALUE_KPI'
        ];
        $arFilter = [
          'IBLOCK_ID' => self::IBLOCK_ID_KPI_USERS,
          'ACTIVE' => 'Y',
          [
            'LOGIC' => 'OR',
            'PROPERTY_ATT_DEPARTMENT' => $departmentID,
            'PROPERTY_ATT_OTHER_DEPARTMENT' => $departmentID,
          ],

        ];

        if (!$boolForce) {
            $arFilter['PROPERTY_ATT_SAVE_DEPARTMENT_DATA'] = false;
        }

        $rs = CIBlockElement::GetList(['sort' => 'asc'], $arFilter, false, false, $arSelect);
        while ($arFields = $rs->GetNext()) {
            $arSaveUser = [];
            $arSaveUser['ATT_WORK_POSITION'] = $arFields['PROPERTY_ATT_WORK_POSITION_VALUE'];
            $arSaveUser['ATT_VALUE_KPI'] = $arFields['PROPERTY_ATT_VALUE_KPI_VALUE'];
            $arSaveUser['ATT_SALARY'] = $arFields['PROPERTY_ATT_SALARY_VALUE'];
            $arSaveUser['ATT_OTHER_DEPARTMENT'] = $arFields['PROPERTY_ATT_OTHER_DEPARTMENT_VALUE'];
            $arSaveUser['ATT_PROBATION_END'] = $arFields['PROPERTY_ATT_PROBATION_END_VALUE'];

            $strSaveData = base64_encode(serialize($arSaveUser));

            CIBlockElement::SetPropertyValuesEx(
                $arFields['ID'],
                self::IBLOCK_ID_KPI_USERS,
                array('ATT_SAVE_DEPARTMENT_DATA' => $strSaveData)
            );

            if ($boolForce) {
                CIBlockElement::SetPropertyValuesEx(
                    $arFields['ID'],
                    self::IBLOCK_ID_KPI_USERS,
                    array('ATT_NOT_CONFIRMED' => false)
                );
            }
        }
    }


    /**
     * Восстанавливает значения пользователя в отделе
     *
     * @param  integer $departmentID ID отдела.
     * @return void
     */
    public function returnDepartmentUserData(int $departmentID)
    {
        $arSelect = [
          'ID',
          'IBLOCK_ID',
          'PROPERTY_ATT_WORK_POSITION',
          'PROPERTY_ATT_SALARY',
          'PROPERTY_ATT_VALUE_KPI',
          'PROPERTY_ATT_OTHER_DEPARTMENT',
          'PROPERTY_ATT_PROBATION_END',
          'PROPERTY_ATT_SAVE_DEPARTMENT_DATA',
          'PROPERTY_ATT_NOT_CONFIRMED',
        ];
        $arFilter = [
          'IBLOCK_ID' => self::IBLOCK_ID_KPI_USERS,
          'ACTIVE' => 'Y',
          [
            'LOGIC' => 'OR',
            'PROPERTY_ATT_DEPARTMENT' => $departmentID,
            'PROPERTY_ATT_OTHER_DEPARTMENT' => $departmentID,
          ],
          '!PROPERTY_ATT_SAVE_DEPARTMENT_DATA' => false
        ];


        $rs = CIBlockElement::GetList(['sort' => 'asc'], $arFilter, false, false, $arSelect);
        while ($arFields = $rs->GetNext()) {
            $arDefaultData = unserialize(base64_decode($arFields['PROPERTY_ATT_SAVE_DEPARTMENT_DATA_VALUE']));

            foreach ($arDefaultData as $key => $value) {
                CIBlockElement::SetPropertyValuesEx($arFields['ID'], self::IBLOCK_ID_KPI_USERS, array($key => $value));
            }

            if ($arFields['PROPERTY_ATT_NOT_CONFIRMED_VALUE'] == 'Y') {
                CIBlockElement::SetPropertyValuesEx($arFields['ID'], self::IBLOCK_ID_KPI_USERS, array('ATT_OTHER_DEPARTMENT' => false));
                CIBlockElement::SetPropertyValuesEx($arFields['ID'], self::IBLOCK_ID_KPI_USERS, array('ATT_NOT_CONFIRMED' => false));
            }
        }
    }


    /**
     * Используется для обработки и логирования обновлений значений пользователя в GRID
     *
     * @throws Exception Комментарий.
     * @return void
     */
    public function updateGridDepartmentUsers()
    {
        $logger = new Logger('UPDATE GRID DEPARTMENT USERS');
        $logger->pushHandler(new StreamHandler($_SERVER['DOCUMENT_ROOT'] . '/local/logs/test-kpi/kpi_upd_grid_users.log', Logger::INFO));

        foreach ($_POST["FIELDS"] as $key => $value) {
            if (intval($key) > 0) {
                $logger->info('Обновлен элемент с ID: ' . $key, array('post' => $_POST, 'new_params' => $value));

                foreach ($value as $k => $val) {
                    CIBlockElement::SetPropertyValuesEx($key, false, array($k => $val));
                }

                $this->linkKPIsToUser($key, $value['ATT_WORK_POSITION']);


                if ($value['ATT_WORK_POSITION'] == 0) {
                    CIBlockElement::SetPropertyValuesEx($key, false, array('ATT_OTHER_DEPARTMENT' => false));
                    CIBlockElement::SetPropertyValuesEx($key, false, array('ATT_NOT_CONFIRMED' => false));
                }
            }
        }
    }


    /**
     * Записывает сотрудников в $this->arUsersDepartment
     * Зависит от функции определяющей department по текущему пользователю
     *
     * @throws Exception Комментарий.
     * @return mixed
     */
    public function getDepartmentUsersByUser()
    {
        if ($this->intCurrentDepartmentByUser) {
            if (isset($_POST['FIELDS'])) {
                $this->updateGridDepartmentUsers();
            }


            global $USER;
            global $DB;

            $by = 'id';
            $order = 'desc';
            $filter = ['ACTIVE' => 'Y', 'UF_DEPARTMENT' => $this->intCurrentDepartmentByUser];
            $arParams = [
              'FIELDS' => ['WORK_POSITION', 'LAST_NAME', 'NAME', 'SECOND_NAME', 'ID', 'DATE_REGISTER'],
              'SELECT' => ['UF_DEPARTMENT']
            ];

            $rsUsers = CUser::GetList($by, $order, $filter, $arParams);
            while ($arUser = $rsUsers->Fetch()) {
                $this->arUsersDepartment[$arUser['ID']] = $arUser;

                $strFIO = $arUser['LAST_NAME'] . ' ' . $arUser['NAME'] . ' ' . $arUser['SECOND_NAME'];
                $strUserID = $arUser['ID'];

                $arSelectIssetUser = ['ID', 'IBLOCK_ID', 'CODE'];
                $arFilterIssetUser = ['IBLOCK_ID' => self::IBLOCK_ID_KPI_USERS, 'ACTIVE' => 'Y', 'CODE' => $strUserID];
                $rsIssetUser = CIBlockElement::GetList(['sort' => 'asc'], $arFilterIssetUser, false, false, $arSelectIssetUser);
                if ($rsIssetUser->SelectedRowsCount() == 0) {
                    // Добавление пользователя в ИБ IBLOCK_ID_KPI_USERS если не существует.
                    $strDateProbationEnd = '';
                    $strRegisterDate = $arUser['DATE_REGISTER'];
                    $strRegisterDateFormat = 'DD.MM.YYYY HH:MI:SS';
                    $strRegisterDateFormatNew = 'DD.MM.YYYY';
                    $strRegisterDate = $DB->FormatDate($strRegisterDate, $strRegisterDateFormat, $strRegisterDateFormatNew);

                    $strDateThreeMonthAgo = date('d.m.Y', strtotime('-3 month'));

                    $intCompareResult = $DB->CompareDates($strRegisterDate, $strDateThreeMonthAgo);

                    if ($intCompareResult > -1) {
                        $strDateProbationEnd = date('d.m.Y', strtotime('+3 month', strtotime($strRegisterDate)));
                    }

                    $objNewUser = new CIBlockElement();

                    $arPropsNewUser = [
                      'ATT_DEPARTMENT' => $arUser['UF_DEPARTMENT'][0],
                      'ATT_PROBATION_END' => $strDateProbationEnd,
                    ];

                    $arLoadNewUser = array(
                      'MODIFIED_BY'         => $USER->GetID(),
                      'IBLOCK_SECTION_ID'   => false,
                      'IBLOCK_ID'           => self::IBLOCK_ID_KPI_USERS,
                      'PROPERTY_VALUES'     => $arPropsNewUser,
                      'NAME'                => $strFIO,
                      'ACTIVE'              => "Y",
                      'CODE'                => $strUserID
                    );

                    $objNewUser->Add($arLoadNewUser);
                }
            }

            $this->saveDepartmentUserData($this->intCurrentDepartmentByUser);




            // list work_positions.
            $arListWorkPositions[] = 'Не выбрано';
            $arSelect = ['ID', 'IBLOCK_ID', 'NAME'];
            $arFilter = [
              'IBLOCK_ID' => self::IBLOCK_ID_KPI_STRUCT,
              'ACTIVE' => 'Y',
              'PROPERTY_ATT_DEPARTMENT' => $this->intCurrentDepartmentByUser
            ];
            $rs = CIBlockElement::GetList(['sort' => 'asc'], $arFilter, false, false, $arSelect);
            while ($arFields = $rs->GetNext()) {
                $arListWorkPositions[$arFields['ID']] = $arFields['NAME'];
            }

            // GRID.
            $this->arResult['grid_id'] = self::GRID_ID_STAFF_TO_WP;

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
              'id' => 'ATT_WORK_POSITION',
              'name' => 'Функциональная единица',
              'sort' => 'PROPERTY_ATT_WORK_POSITION',
              'default' => true,
              'editable' => ['TYPE' => 'DROPDOWN', 'items' => $arListWorkPositions],
            ];
            $this->arResult['columns'][] = [
              'id' => 'ATT_SALARY',
              'name' => 'Ставка',
              'sort' => 'PROPERTY_ATT_SALARY',
              'default' => true,
              'editable' => true,
              'type' => 'number'
            ];
            $this->arResult['columns'][] = [
              'id' => 'ATT_OTHER_DEPARTMENT',
              'name' => 'Назначен в другом отделе',
              'sort' => 'PROPERTY_ATT_OTHER_DEPARTMENT',
              'default' => true,
              'editable' => false
            ];

            $this->arResult['columns'][] = [
              'id' => 'ATT_PROBATION_END',
              'name' => 'Испытательный срок',
              'sort' => 'PROPERTY_ATT_PROBATION_END',
              'default' => true,
              'editable' => true,
              'type' => 'date'
            ];


            $arSelectDepartmentGrid = array(
              'ID',
              'IBLOCK_ID',
              'NAME',
              'PROPERTY_ATT_WORK_POSITION',
              'PROPERTY_ATT_SALARY',
              'PROPERTY_ATT_OTHER_DEPARTMENT',
              'PROPERTY_ATT_PROBATION_END',
            );

            $arFilterDepartmentGrid = array(
              "IBLOCK_ID" => self::IBLOCK_ID_KPI_USERS,
              "ACTIVE" => "Y",
              [
                'LOGIC' => 'OR',
                "PROPERTY_ATT_DEPARTMENT" => $this->intCurrentDepartmentByUser,
                'PROPERTY_ATT_OTHER_DEPARTMENT' => $this->intCurrentDepartmentByUser,
              ],

            );

            $rsDepartmentGrid = \CIBlockElement::GetList(
                $this->arResult['sort']['sort'],
                $arFilterDepartmentGrid,
                false,
                $this->arResult['nav_params'],
                $arSelectDepartmentGrid
            );

            $this->arResult['nav']->setRecordCount($rsDepartmentGrid->selectedRowsCount());

            while ($arFieldsDepartmentGrid = $rsDepartmentGrid->GetNext()) {
                $boolEditable = true;
                $strOtherDepartmentName = '';
                $intOtherDepartmentID = 0;

                if ($arFieldsDepartmentGrid['PROPERTY_ATT_OTHER_DEPARTMENT_VALUE']) {
                    $intOtherDepartmentID = intval($arFieldsDepartmentGrid['PROPERTY_ATT_OTHER_DEPARTMENT_VALUE']);
                }

                if ($intOtherDepartmentID > 0 && $intOtherDepartmentID != $this->intCurrentDepartmentByUser) {
                    $resOtherDepartment = CIBlockSection::GetByID(intval($arFieldsDepartmentGrid['PROPERTY_ATT_OTHER_DEPARTMENT_VALUE']));
                    if ($arOtherDepartment = $resOtherDepartment->GetNext()) {
                        $strOtherDepartmentName = $arOtherDepartment['NAME'];
                    }
                    $boolEditable = false;
                }

                $color = '';
                $colorWhite = '';
                $colorYellow = '1';
                $colorRed = '2';

                if (!$arFieldsDepartmentGrid['PROPERTY_ATT_WORK_POSITION_VALUE'] && !$arFieldsDepartmentGrid['PROPERTY_ATT_PROBATION_END_VALUE'] && !$strOtherDepartmentName) {
                    $color = $colorRed;
                } elseif ($arFieldsDepartmentGrid['PROPERTY_ATT_PROBATION_END_VALUE'] || $strOtherDepartmentName) {
                    $color = $colorYellow;
                } elseif ($arFieldsDepartmentGrid['PROPERTY_ATT_WORK_POSITION_VALUE']) {
                    $color = $colorWhite;
                }


                $this->arResult['list'][] = [
                  'data' => [
                    "ID" => $arFieldsDepartmentGrid['ID'],
                    "NAME" => $arFieldsDepartmentGrid['NAME'],
                    "ATT_SALARY" => $arFieldsDepartmentGrid['PROPERTY_ATT_SALARY_VALUE'],
                    "ATT_WORK_POSITION" => $arFieldsDepartmentGrid['PROPERTY_ATT_WORK_POSITION_VALUE'] ?? 0,
                    "ATT_OTHER_DEPARTMENT" => $strOtherDepartmentName,
                    "ATT_PROBATION_END" => $arFieldsDepartmentGrid['PROPERTY_ATT_PROBATION_END_VALUE']
                  ],
                  'columns' =>
                    [
                      "ATT_WORK_POSITION" => $arListWorkPositions[$arFieldsDepartmentGrid['PROPERTY_ATT_WORK_POSITION_VALUE']] ?? 'Не выбрано',
                    ],
                  'depth' => $color,

                  'editable' => $boolEditable,

                ];
            }

            /**
             * Создает кнопку.
             *
             * @return array
             */
            function getSaveEditButton()
            {
                $onchange = new Onchange();
                $onchange->addAction(array("ACTION" => Actions::CALLBACK, "DATA" => array(array("JS" => "customSaveDepartment(self.parent)"))));
                $saveButton = new Button();

                $saveButton->setClass(DefaultValue::SAVE_BUTTON_CLASS);
                $saveButton->setText("Сохранить");
                $saveButton->setId(DefaultValue::SAVE_BUTTON_ID);
                $saveButton->setOnchange($onchange);

                return $saveButton->toArray();
            }

            /**
             * Создает кнопку.
             *
             * @return array
             */
            function getCancelEditButton()
            {
                $onchange = new Onchange();
                $onchange->addAction(array("ACTION" => Actions::SHOW_ALL, "DATA" => array()));
                $onchange->addAction(array("ACTION" => Actions::CALLBACK, "DATA" => array(array("JS" => "Grid.editSelectedCancel()"))));
                $onchange->addAction(array("ACTION" => Actions::REMOVE, "DATA" => array(array("ID" => DefaultValue::SAVE_BUTTON_ID), array("ID" => DefaultValue::CANCEL_BUTTON_ID))));

                $cancelButton = new Button();
                $cancelButton->setClass(DefaultValue::CANCEL_BUTTON_CLASS);
                $cancelButton->setText("Отменить");
                $cancelButton->setId(DefaultValue::CANCEL_BUTTON_ID);
                $cancelButton->setOnchange($onchange);

                return $cancelButton->toArray();
            }

            $onchange2 = new Onchange();
            $onchange2->addAction(
                [
                  "ACTION" => Actions::CREATE,
                  "DATA" => array(getSaveEditButton(), getCancelEditButton())
                ]
            );
            $onchange2->addAction(
                [
                  "ACTION" => Actions::CALLBACK,
                  "DATA" => array(array("JS" => "customEditDepartment(self)"))
                ]
            );
            $onchange2->addAction(
                [
                  "ACTION" => Actions::HIDE_ALL_EXPECT,
                  "DATA" => array(
                    array("ID" => DefaultValue::SAVE_BUTTON_ID),
                    array("ID" => DefaultValue::CANCEL_BUTTON_ID))
                ]
            );

            $this->arResult['onchange2'] = $onchange2;
        }
    }


    /**
     * Возвращает данные по отделу
     *
     * @return array
     */
    public function makeDepartmentDataNew()
    {
        $arDepData = [];

        if ($this->arUsersDepartment) {
            $arSelectWorkPosition = [
              'ID',
              'IBLOCK_ID',
              'NAME',
              'PROPERTY_ATT_FORMULA',
              'PROPERTY_ATT_DEPARTMENT',
              'PROPERTY_ATT_SALARY'
            ];
            $arFilterWorkPosition = [
              "IBLOCK_ID" => self::IBLOCK_ID_KPI_STRUCT,
              "ACTIVE" => "Y",
              "PROPERTY_ATT_DEPARTMENT" => intval($_REQUEST['department']),

            ];
            $rsWorkPosition = CIBlockElement::GetList(array('sort' => 'asc'), $arFilterWorkPosition, false, false, $arSelectWorkPosition);
            while ($arFieldsWorkPosition = $rsWorkPosition->GetNext()) {
                $intWorkPositionID = intval($arFieldsWorkPosition['ID']);
                $strWorkPositionFormula = $arFieldsWorkPosition['PROPERTY_ATT_FORMULA_VALUE'];

                if ($intWorkPositionID) {
                    $arDepData[$intWorkPositionID]['NAME'] = $arFieldsWorkPosition['NAME'];
                    $arDepData[$intWorkPositionID]['FORMULA'] = $strWorkPositionFormula;
                    // TODO добавить колличество сотрудников.
                }
            }
        }

        return $arDepData;
    }


    /**
     * Устанавливает подключаемую страницу в шаблоне
     * Передает данные в шаблоны через $this->arResult
     *
     * @throws Exception Комментарий.
     * @return void
     */
    public function executeData()
    {
        $this->strCurrentPage = $_REQUEST['page'];
        if (!in_array($this->strCurrentPage, $this->arAvailablePages)) {
            $this->strCurrentPage = 'index';
        }
        $this->arResult['INCLUDE_FILE'] = 'page_' . strtolower($this->strCurrentPage) . '.php';
        $this->init($this->strCurrentPage);


        switch ($this->strCurrentPage) {
            case 'index':
                $this->arResult['USER_HELPER'] = $this->arUserHelper;

                break;

            case 'computed_rules':
            case 'set_salary':
                $this->arResult['ALL_USERS'] = $this->arUsers;
                $this->arResult['DEPARTMENTS'] = $this->arDepartments;
                $this->arResult['USERS'] = $this->arUsersDepartment;
                $this->arResult['DEPARTMENT_DATA'] = $this->makeDepartmentDataNew();

                break;
        }
    }


    /**
     * Вспомогательная функция. Возвращает ENUM id & value по коду
     *
     * @param  string $propertyCode Код свойства.
     * @return array
     */
    public function getEnumFields(string $propertyCode): array
    {
        $arRes = [];
        $rsEnums = CIBlockPropertyEnum::GetList(
            ["ID" => "ASC", "SORT" => "ASC"],
            ["IBLOCK_ID" => self::IBLOCK_ID_KPI, "CODE" => [$propertyCode]]
        );

        while ($arEnums = $rsEnums->GetNext()) {
            $arRes[$arEnums['PROPERTY_CODE']][$arEnums["ID"]] = $arEnums["VALUE"];
        }

        return $arRes[$propertyCode];
    }


    /**
     * Удаляет KPI и убирает привязку у пользователей
     *
     * @param integer $IBLOCK_ID ID инфоблока.
     * @throws Exception Комментарий.
     * @return void
     */
    protected function deleteElements(int $IBLOCK_ID)
    {
        global $DB;

        $arDeleteIDs = [];

        foreach ($_REQUEST['ID'] as $elementID) {
            if (intval($elementID) > 0) {
                $logger = new Logger('DELETE_KPI');
                $logger->pushHandler(new StreamHandler($_SERVER['DOCUMENT_ROOT'] . '/local/logs/test-kpi/kpi_rules_change.log', Logger::INFO));

                $intElementID = intval($elementID);

                if (CIBlock::GetPermission($IBLOCK_ID) >= 'W') {
                    $DB->StartTransaction();
                    if (!\CIBlockElement::Delete($intElementID)) {
                        $logger->info('Ошибка удаления элемента');
                        $DB->Rollback();
                    } else {
                        $DB->Commit();
                        $logger->info('Удален элемент с ID: ' . $intElementID . ' в ИБ: ' . $IBLOCK_ID);
                        $arDeleteIDs[] = $intElementID;
                    }
                }
            }
        }

        $this->deleteKPILinkUser($arDeleteIDs);
    }


    /**
     * Удаляет формулу.
     * Используется при удалении KPI которые записываются в формулу
     *
     * @param  integer $WPID ID должности.
     * @return void
     */
    public function clearWpFormula(int $WPID)
    {
        CIBlockElement::SetPropertyValuesEx($WPID, self::IBLOCK_ID_KPI_STRUCT, ['ATT_FORMULA' => '']);
    }


    /**
     * Обновляет значения KPI пользователя относительно существующих KPI
     *
     * @param  array $arElementIDs Массив ID которые надо удалить.
     * @throws Exception Комментарий.
     * @return void
     */
    protected function deleteKPILinkUser(array $arElementIDs)
    {
        if (isset($_REQUEST['work_position']) && intval($_REQUEST['work_position']) > 0) {
            $logger = new Logger('DELETE_KPI_VALUE_USERS');
            $logger->pushHandler(new StreamHandler($_SERVER['DOCUMENT_ROOT'] . '/local/logs/test-kpi/kpi_rules_change.log', Logger::INFO));

            $intWorkPosition = intval($_REQUEST['work_position']);

            $this->clearWpFormula($intWorkPosition);

            $arNewKPIValues = [];
            $arUsersIDs = [];
            $arSelect = ["ID", "IBLOCK_ID", "NAME", "PROPERTY_ATT_VALUE_KPI"];
            $arFilter = ["IBLOCK_ID" => self::IBLOCK_ID_KPI_USERS, "ACTIVE" => "Y", "PROPERTY_ATT_WORK_POSITION" => $intWorkPosition];
            $rs = CIBlockElement::GetList(['sort' => 'asc'], $arFilter, false, false, $arSelect);
            while ($arFields = $rs->GetNext()) {
                $arNewKPIValues = [];
                foreach ($arFields['PROPERTY_ATT_VALUE_KPI_VALUE'] as $count => $valueID) {
                    if (!in_array(intval($valueID), $arElementIDs)) {
                        $strDescription = unserialize(htmlspecialcharsback($arFields['PROPERTY_ATT_VALUE_KPI_DESCRIPTION'][$count]));
                        $arNewKPIValues[] = ['VALUE' => $valueID, 'DESCRIPTION' => $strDescription];
                    }
                }

                $arUsersIDs[] = $arFields['ID'];
            }

            if (count($arUsersIDs) > 0) {
                if (count($arNewKPIValues) == 0) {
                    $arNewKPIValues = false;
                }


                foreach ($arUsersIDs as $userID) {
                    CIBlockElement::SetPropertyValuesEx(
                        intval($userID),
                        self::IBLOCK_ID_KPI_USERS,
                        ['ATT_VALUE_KPI' => $arNewKPIValues]
                    );
                    $logger->info(
                        'Обновлено свойство ATT_VALUE_KPI',
                        ['deleteID' => $userID, '$arNewKPIValues' => $arNewKPIValues]
                    );
                }
            }
        }
    }


    /**
     * Обрабатывает обновления из GRID отслеживая передачу POST методом
     *
     * @param  array   $arProtectedProps Массив свойств которые не надо изменять.
     * @param  boolean $section          Раздел.
     * @throws Exception Комментарий.
     * @return void
     */
    protected function updateElement(array $arProtectedProps = [], bool $section = false)
    {
        global $USER;
        $oEl = new CIBlockElement();

        foreach ($_POST["FIELDS"] as $key => $value) {
            $arPropsValue = [];

            if (count($arProtectedProps) > 0 && intval($key) > 0) {
                $arPropForSelect = [];
                foreach ($arProtectedProps as $prop) {
                    $arPropForSelect[] = 'PROPERTY_' . $prop;
                }

                $arSelectBase = ["ID", "IBLOCK_ID", "NAME"];

                $arSelect = array_merge($arSelectBase, $arPropForSelect);

                $arFilter = ["IBLOCK_ID" => self::IBLOCK_ID_KPI, "ACTIVE" => "Y", "ID" => $key];
                $rs = CIBlockElement::GetList(['sort' => 'asc'], $arFilter, false, false, $arSelect);
                while ($arFields = $rs->GetNext()) {
                    foreach ($arProtectedProps as $prop) {
                        $arPropsValue[$prop] = $arFields['PROPERTY_' . $prop . '_VALUE'];
                    }
                }
            }


            $logger = new Logger('UPDATE ELEMENT');
            $arProps = $arPropsValue;

            $arEnumDataSource = $this->getEnumFields('ATT_DATA_SOURCE');


            foreach ($value as $k => $val) {
                $arProps[$k] = $val;
            }

            foreach ($arProps as $k => $val) {
                if ($k == 'ATT_DATA_SOURCE' && $arEnumDataSource[$val] == 'Ручной ввод') {
                    $arProps['ATT_MANUAL_INPUT'] = 'Y';
                }
            }


            if ($arProps['NAME']) {
                $arLoadArray = array(
                  "MODIFIED_BY"     => $USER->GetID(),
                  "IBLOCK_SECTION"  => $section,
                  "PROPERTY_VALUES" => $arProps,
                  "NAME"            => $arProps['NAME'],
                  "ACTIVE"          => "Y",
                );

                $strUserFullName = $USER->GetFullName();

                $logger->pushHandler(new StreamHandler($_SERVER['DOCUMENT_ROOT'] . '/local/logs/test-kpi/kpi_rules_change.log', Logger::INFO));

                if ($oEl->Update($key, $arLoadArray)) {
                    $logger->info('Обновлен элемент с ID: ' . $key, array('post' => $_POST, 'user' => $strUserFullName, 'new_params' => $arLoadArray));
                } else {
                    $logger->error("Ошибка: " . $oEl->LAST_ERROR);
                }
            }
        }
    }


    /**
     * Вспомагаетельная функция для $this->filterSections
     *
     * @param array $section Массив раздела.
     * @param array $idx     Массив ID.
     * @return void
     */
    public function getIDS(array $section, array &$idx)
    {
        foreach ($section['child'] as $key => $sect) {
            $idx[$key] = $sect['NAME'];
            $this->getIDS($sect, $idx);
        }
    }


    /**
     * mb_ucfirst - преобразует первый символ в верхний регистр
     *
     * @param  string $str      Строка.
     * @param  string $type     Тип изменения.
     * @param  string $encoding Кодировка, по-умолчанию UTF-8.
     * @return string
     */
    final protected function changeFirstLetter(string $str, string $type = 'upper', string $encoding = 'UTF-8')
    {
        $str = mb_ereg_replace('^[\ ]+', '', $str);
        if ($type = 'upper') {
            $str = mb_strtoupper(mb_substr($str, 0, 1, $encoding), $encoding) .
              mb_substr($str, 1, mb_strlen($str), $encoding);
        } elseif ($type = 'lower') {
            $str = mb_strtolower(mb_substr($str, 0, 1, $encoding), $encoding) .
              mb_substr($str, 1, mb_strlen($str), $encoding);
        }

        return $str;
    }


    /**
     * Возвращает склонения числительных
     *
     * @param integer $number Число.
     * @param array   $titles Массив склонений.
     * @return string
     */
    final protected function declOfNum(int $number, array $titles)
    {
        $cases = array(2, 0, 1, 1, 1, 2);
        $format = $titles[($number % 100 > 4 && $number % 100 < 20) ? 2 : $cases[min($number % 10, 5)]];
        return sprintf($format, $number);
    }


    /**
     * Вспомогательная функция
     * Возвращает список всех вложенных разделов в указанном разделе ИБ
     *
     * @param array $filter Массив для фильтрации.
     * @param array $select Массив для выбора.
     * @return mixed
     */
    final protected function getSectionList(array $filter, array $select)
    {
        $dbSection = CIBlockSection::GetList(
            ['LEFT_MARGIN' => 'ASC',],
            array_merge(['ACTIVE' => 'Y', 'GLOBAL_ACTIVE' => 'Y',], is_array($filter) ? $filter : array()),
            false,
            array_merge(['ID', 'IBLOCK_SECTION_ID'], is_array($select) ? $select : array())
        );
        while ($arSection = $dbSection-> GetNext(true, false)) {
            $SID = $arSection['ID'];
            $PSID = (int) $arSection['IBLOCK_SECTION_ID'];
            $arLinks[$PSID]['child'][$SID] = $arSection;
            $arLinks[$SID] = &$arLinks[$PSID]['child'][$SID];
        }

        return array_shift($arLinks);
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
            $this->executeData();
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


    /**
     * Возвращает значения критического KPI
     *
     * @param  string $strValue Значение.
     * @return array
     */
    public function getCriticalKPI(string $strValue)
    {
        $floatCriticalValue = 1;
        $floatCriticalBaseValue = 1;

// if ($strValue == '1') { // TODO написал хуйню
// return ['FORMULA_RESULT' => $floatCriticalValue, 'VALUE' => $floatCriticalValue];
// }
        $arSelect = ["ID", "IBLOCK_ID", "NAME", "PROPERTY_ATT_LABEL", "PROPERTY_ATT_FACT", ];
        $arFilter = ["IBLOCK_ID" => self::IBLOCK_ID_KPI, "ACTIVE" => "Y", "IBLOCK_SECTION_ID" => self::SECTION_KPI_EXTRA];
        $rs = CIBlockElement::GetList(['sort' => 'asc'], $arFilter, false, false, $arSelect);
        while ($arFields = $rs->GetNext()) {
            if ($arFields['PROPERTY_ATT_LABEL_VALUE'] == '1' && floatval($arFields['PROPERTY_ATT_FACT_VALUE']) > 1) {
                $floatCriticalBaseValue = floatval($arFields['PROPERTY_ATT_FACT_VALUE']);
            }

            if ($arFields['PROPERTY_ATT_LABEL_VALUE'] == $strValue) {
                $floatCriticalValue = floatval($arFields['PROPERTY_ATT_FACT_VALUE']);
            }
        }

        $arFormula = [];
        $arSelectCritical = ["UF_KPI_TEST_FORMULA_EXT_CRITICAL"];
        $arFilterCritical = ["IBLOCK_ID" => self::IBLOCK_ID_KPI, "ID" => self::SECTION_KPI_EXTRA];
        $rsCritical = CIBlockSection::GetList(['sort' => 'asc'], $arFilterCritical, false, $arSelectCritical);
        while ($arFields = $rsCritical->GetNext()) {
            $arFormula = explode(',', $arFields['UF_KPI_TEST_FORMULA_EXT_CRITICAL']);
        }

        $arFormulaValues = [
          'B' => $floatCriticalBaseValue,
          'N' => $floatCriticalValue
        ];

        $strFormula = '';
        foreach ($arFormula as $value) {
            if ($arFormulaValues[$value]) {
                $strFormula .= $arFormulaValues[$value];
            } else {
                $strFormula .= $value;
            }
        }


        return ['FORMULA_RESULT' => $this->eval($strFormula), 'VALUE' => $floatCriticalValue];
    }


    /**
     * Возвращает KPI прогресса
     *
     * @param  string $strValueCritical Значение критического KPI.
     * @return boolean|mixed|null
     */
    public function getKPIProgress(string $strValueCritical)
    {
        $floatKPIProgressValue = 1;

        $arFormula = [];
        $arSelectCritical = ['UF_KPI_TEST_PROGRESS', 'UF_KPI_TEST_FORMULA_EXT_PROGRESS'];
        $arFilterCritical = ["IBLOCK_ID" => self::IBLOCK_ID_KPI, "ID" => self::SECTION_KPI_EXTRA];
        $rsCritical = CIBlockSection::GetList(['sort' => 'asc'], $arFilterCritical, false, $arSelectCritical);
        while ($arFields = $rsCritical->GetNext()) {

            $floatKPIProgressValue = $arFields['UF_KPI_TEST_PROGRESS'];

            $arFormula = explode(',', $arFields['UF_KPI_TEST_FORMULA_EXT_PROGRESS']);
        }

        $arFormulaValues = [
          'K' => $this->getCriticalKPI($strValueCritical)['FORMULA_RESULT'],
          'R' => $floatKPIProgressValue
        ];

        $strFormula = '';
        foreach ($arFormula as $value) {
            if ($arFormulaValues[$value]) {
                $strFormula .= $arFormulaValues[$value];
            } else {
                $strFormula .= $value;
            }
        }


        return $this->eval($strFormula);
    }


    /**
     * Сохраняет значения KPI для пользователя
     *
     * @param array $arUser Массив пользователей.
     * @return array
     * @throws Exception Комментарий.
     */
    public function saveUserKPI(array $arUser)
    {
        $logger = new Logger('UPDATE');
        $logger->pushHandler(
            new StreamHandler($_SERVER['DOCUMENT_ROOT'] . '/local/logs/test-kpi/kpi_update_user_kpi.log', Logger::INFO)
        );

        $intUserID = intval($arUser['id']);
        $arValuesKPI = [];

        if (count($arUser['ATT_VALUE_KPI']) > 0) {
            foreach ($arUser['ATT_VALUE_KPI'] as $valueKPI) {
                $arValuesKPI[] = ['VALUE' => $valueKPI['id'], 'DESCRIPTION' => $valueKPI['value']];
            }
            CIBlockElement::SetPropertyValuesEx(
                $intUserID,
                self::IBLOCK_ID_KPI_USERS,
                ['ATT_VALUE_KPI' => $arValuesKPI]
            );
        }

        if (intval($arUser['ATT_KPI_CRITICAL']) > 0) {
            CIBlockElement::SetPropertyValuesEx(
                $intUserID,
                self::IBLOCK_ID_KPI_USERS,
                ['ATT_KPI_CRITICAL' => intval($arUser['ATT_KPI_CRITICAL'])]
            );
        } else {
            CIBlockElement::SetPropertyValuesEx($intUserID, self::IBLOCK_ID_KPI_USERS, ['ATT_KPI_CRITICAL' => false]);
        }
        CIBlockElement::SetPropertyValuesEx(
            $intUserID,
            self::IBLOCK_ID_KPI_USERS,
            ['ATT_KPI_PROGRESS' => $arUser['ATT_KPI_PROGRESS']]
        );
        CIBlockElement::SetPropertyValuesEx(
            $intUserID,
            self::IBLOCK_ID_KPI_USERS,
            ['ATT_COMMENT' => $arUser['ATT_COMMENT']]
        );

        $floatResultKPIValue = $this->calculateKPIValue($intUserID, true);

        if (floatval($floatResultKPIValue) > 0) {
            CIBlockElement::SetPropertyValuesEx(
                $intUserID,
                self::IBLOCK_ID_KPI_USERS,
                ['ATT_RESULT_KPI' => $floatResultKPIValue]
            );
        } else {
            CIBlockElement::SetPropertyValuesEx($intUserID, self::IBLOCK_ID_KPI_USERS, ['ATT_RESULT_KPI' => '0']);
        }


        $logger->info('UPDATE', ['data' => $arUser, '$floatResultKPIValue' => $floatResultKPIValue]);

        return ['ID' => $intUserID, 'KPI_RESULT' => $floatResultKPIValue];
    }

    /**
     * AJAX FUNCTIONS
     */

    /**
     * Возвращает LABEL KPI для select
     * @param $work_position
     * @return string
     */
    public function getNextLabelAction($work_position)
    {
        $arLabels = [];

        $arSelect = ["ID", "IBLOCK_ID", "NAME", "PROPERTY_ATT_LABEL"];
        $arFilter = ["IBLOCK_ID" => self::IBLOCK_ID_KPI, "ACTIVE" => "Y", "PROPERTY_ATT_WORK_POSITION" => intval($work_position)];
        $rs = CIBlockElement::GetList(['sort' => 'asc'], $arFilter, false, false, $arSelect);
        while ($arFields = $rs->GetNext()) {
            $arLabels[] = intval(str_replace('K', '', $arFields['PROPERTY_ATT_LABEL_VALUE']));
        }

        $intLastKPI = max($arLabels);
        $intNextKPI = $intLastKPI + 1;

        return 'K' . $intNextKPI;
    }


    /**
     * Добавляет KPI
     *
     * @param string $NAME
     * @param string $ATT_LABEL
     * @param string $ATT_DATA_SOURCE
     * @param string $ATT_WEIGHT
     * @param string $ATT_TARGET_VALUE
     * @param string $ATT_WORK_POSITION
     * @param string $ATT_MANUAL_INPUT
     * @return boolean
     * @throws Exception
     */
    public function addKPIAction(
        $NAME,
        $ATT_LABEL,
        $ATT_DATA_SOURCE,
        $ATT_WEIGHT,
        $ATT_TARGET_VALUE,
        $ATT_WORK_POSITION,
        $ATT_MANUAL_INPUT = 'off'
    ) {
        $bRes = false;

        global $USER;
        $objElement = new CIBlockElement();

        $loggerDebug = new Logger('DEBUG');
        $loggerDebug->pushHandler(new StreamHandler($_SERVER['DOCUMENT_ROOT'] . '/local/logs/test-kpi/kpi_add.log', Logger::INFO));

        $arEnumDataSource = $this->getEnumFields('ATT_DATA_SOURCE');


        if ($arEnumDataSource[$ATT_DATA_SOURCE] == 'Ручной ввод') {
            $ATT_MANUAL_INPUT = 'on';
        }


        switch ($ATT_MANUAL_INPUT) {
            case 'on':
                $ATT_MANUAL_INPUT = 'Y';
                break;
            case 'off':
                $ATT_MANUAL_INPUT = 'N';
                break;
        }

        $arProps = [
          'ATT_LABEL' => $ATT_LABEL,
          'ATT_DATA_SOURCE' => $ATT_DATA_SOURCE,
          'ATT_WEIGHT' => $ATT_WEIGHT,
          'ATT_WORK_POSITION' => $ATT_WORK_POSITION,
          'ATT_TARGET_VALUE' => $ATT_TARGET_VALUE,
          'ATT_MANUAL_INPUT' => $ATT_MANUAL_INPUT,
        ];

        $arLoadKPI = array(
          "MODIFIED_BY"         => $USER->GetID(),
          "IBLOCK_SECTION_ID"   => false,
          "IBLOCK_ID"           => self::IBLOCK_ID_KPI,
          "PROPERTY_VALUES"     => $arProps,
          "NAME"                => $NAME,
          "ACTIVE"              => "Y",
        );

        if ($intID = $objElement->Add($arLoadKPI)) {
            $bRes = true;
            $loggerDebug->info('Добавлен KPI ID: ' . $intID . ' пользователем: ' . $USER->GetFullName(), ['arLoad' => $arLoadKPI]);
        } else {
            $loggerDebug->info('Ошибка добавления KPI', ['error' => $objElement->LAST_ERROR, 'arLoad' => $arLoadKPI]);
        }

        return $bRes;
    }


    /**
     * Возвращает список LABEL для KPI
     *
     * @param $work_position
     * @return array
     */
    public function getKPIsAction($work_position)
    {
        $arLabels = [];

        $arSelect = ["ID", "IBLOCK_ID", "NAME", "PROPERTY_ATT_LABEL"];
        $arFilter = ["IBLOCK_ID" => self::IBLOCK_ID_KPI, "ACTIVE" => "Y", "PROPERTY_ATT_WORK_POSITION" => intval($work_position)];
        $rs = CIBlockElement::GetList(['sort' => 'asc'], $arFilter, false, false, $arSelect);
        while ($arFields = $rs->GetNext()) {
            $arLabels[] = $arFields['PROPERTY_ATT_LABEL_VALUE'];
        }

        return $arLabels;
    }


    /**
     * Возвращает ENUM по ID
     *
     * @param string $id
     * @return array
     */
    public function getEnumFieldByIDAction($id)
    {
        $arRes = [];
        $rsEnums = CIBlockPropertyEnum::GetList(
            ["ID" => "ASC", "SORT" => "ASC"],
            ["IBLOCK_ID" => self::IBLOCK_ID_KPI, "ID" => intval($id)]
        );

        while ($arEnums = $rsEnums->GetNext()) {
            $arRes['value'] = $arEnums["VALUE"];
        }

        return $arRes;
    }


    /**
     * Проверяет значение суммы всех показателей KPI (должна быть равна 100)
     *
     * @param string $work_position
     * @return integer
     */
    public function checkIndicatorsWeightSumAction($work_position)
    {
        $intWeightSum = 0;

        $arSelect = ["ID", "IBLOCK_ID", "NAME", "PROPERTY_ATT_WEIGHT"];
        $arFilter = ["IBLOCK_ID" => self::IBLOCK_ID_KPI, "ACTIVE" => "Y", "PROPERTY_ATT_WORK_POSITION" => intval($work_position)];
        $rs = CIBlockElement::GetList(['sort' => 'asc'], $arFilter, false, false, $arSelect);
        while ($arFields = $rs->GetNext()) {
            $intWeightSum += intval($arFields['PROPERTY_ATT_WEIGHT_VALUE']);
        }

        return $intWeightSum;
    }

    /**
     * Отменяет все изменения в KPI
     *
     * @param string $work_position
     * @return array
     * @throws Exception
     */
    public function returnSavedKPIAction($work_position)
    {
        $intWorkPositionID = intval($work_position);

        $arSaveKPIs = [];
        $arRes = [];
        global $USER;
        global $DB;

        $arSelectWP = ["ID", "IBLOCK_ID", "NAME", "PROPERTY_ATT_SAVED_KPI"];
        $arFilterWP = ["IBLOCK_ID" => self::IBLOCK_ID_KPI_STRUCT, "ID" => $intWorkPositionID];
        $rsWP = CIBlockElement::GetList(['sort' => 'asc'], $arFilterWP, false, false, $arSelectWP);
        while ($arFields = $rsWP->GetNext()) {
            $arSaveKPIs = unserialize(htmlspecialcharsBack($arFields['PROPERTY_ATT_SAVED_KPI_VALUE']));
        }

        $logger = new Logger('RETURN_KPI_VALUES');
        $logger->pushHandler(new StreamHandler($_SERVER['DOCUMENT_ROOT'] . '/local/logs/test-kpi/kpi_return.log', Logger::INFO));

        $objElementUpdate = new CIBlockElement();
        foreach ($arSaveKPIs as $id => $values) {
            $arLoadArray = [
              "MODIFIED_BY"     => $USER->GetID(),
              "PROPERTY_VALUES" => $values,
              "NAME"            => $values['NAME'],
              "ACTIVE"          => "Y",
            ];

            if ($res = $objElementUpdate->Update($id, $arLoadArray)) {
                $logger->info('UPDATE KPI VALUES', ['$arLoadArray' => $arLoadArray]);
            } else {
                $logger->info('ERROR return KPI VALUES', ['error' => $objElementUpdate->LAST_ERROR]);
            }
        }

        $arDeleteKPI = [];

        $arSelectKPI = ["ID", "IBLOCK_ID", "NAME"];
        $arFilterKPI = ["IBLOCK_ID" => self::IBLOCK_ID_KPI, "PROPERTY_ATT_WORK_POSITION" => $intWorkPositionID];
        $rsWP = CIBlockElement::GetList(['sort' => 'asc'], $arFilterKPI, false, false, $arSelectKPI);
        while ($arFields = $rsWP->GetNext()) {
            if (!in_array($arFields['ID'], array_keys($arSaveKPIs))) {
                $arDeleteKPI[] = $arFields['ID'];
            }
        }

        if (count($arDeleteKPI) > 0) {
            $logger->info('Delete array for KPI is ready', ['array' => $arDeleteKPI, 'keysIssetKPI' => array_keys($arSaveKPIs)]);


            if (CIBlock::GetPermission(self::IBLOCK_ID_KPI) >= 'W') {
                $DB->StartTransaction();

                foreach ($arDeleteKPI as $id) {
                    if (!CIBlockElement::Delete($id)) {
                        $messageError = 'Элемент с ID: ' . $id . 'не удален. Ошибка удаления';
                        $arRes['errors'][] = $messageError;
                        $logger->info($messageError);
                        $DB->Rollback();
                    } else {
                        $DB->Commit();
                        $logger->info('Delete KPI with ID: ' . $id);
                    }
                }
            } else {
                $messageError = 'Недостаточно прав для удаления';
                $arRes['errors'][] = $messageError;
            }
        }

        return $arRes;
    }


    /**
     * Сохраняет значения для должности
     *
     * @param string $work_position
     * @param string $formula_kpi
     * @param string $wp_name
     * @param string $department_id
     * @return boolean
     * @throws Exception
     */
    public function saveWorkPositionValuesAction($work_position, $formula_kpi, $wp_name, $department_id)
    {
        global $USER;

        $intWpID = intval($work_position);

        $arSave = [
          'ATT_FORMULA' => $formula_kpi
        ];

        foreach ($arSave as $code => $value) {
            CIBlockElement::SetPropertyValuesEx($intWpID, self::IBLOCK_ID_KPI_STRUCT, [$code => $value]);
        }

        $this->saveKPI(true);


        $objWorkPosition = new CIBlockElement();

        $arLoadWorkPosition = array(
          'MODIFIED_BY'    => $USER->GetID(),
// элемент изменен текущим пользователем
          'NAME'           => $wp_name,
          'CODE'           => CUtil::translit($wp_name . '_' . $department_id, 'ru', $this->arTranslitParams),
        );

        $objWorkPosition->Update($intWpID, $arLoadWorkPosition);

        return true;
    }


    /**
     * Получает значения формул
     *
     * @param string $work_position
     * @return array
     */
    public function getFormulasAction($work_position)
    {
        $arFormulas = [];

        $arSelect = ["ID", "IBLOCK_ID", "NAME", "PROPERTY_ATT_FORMULA", "PROPERTY_ATT_FORMULA_INDICATORS"];
        $arFilter = ["IBLOCK_ID" => self::IBLOCK_ID_KPI_STRUCT, "ACTIVE" => "Y", "ID" => intval($work_position)];
        $rs = CIBlockElement::GetList(['sort' => 'asc'], $arFilter, false, false, $arSelect);
        while ($arFields = $rs->GetNext()) {
            $arFormulas['kpi'] = $arFields['PROPERTY_ATT_FORMULA_VALUE'];
            $arFormulas['indicators'] = $arFields['PROPERTY_ATT_FORMULA_INDICATORS_VALUE'];
        }

        return $arFormulas;
    }


    /**
     * Добавляет значения дополнительных правил расчета KPI
     *
     * @param string $NAME
     * @param string $ATT_FACT
     * @param string $ATT_LABEL
     * @return mixed
     * @throws Exception
     */
    public function addRuleExtraAction($NAME, $ATT_FACT, $ATT_LABEL)
    {
        $arRes = [];
        global $USER;
        $logger = new Logger('ADD');
        $logger->pushHandler(new StreamHandler($_SERVER['DOCUMENT_ROOT'] . '/local/logs/test-kpi/kpi_extra.log', Logger::INFO));


        $objRule = new CIBlockElement();

        $arProps = [
          'ATT_FACT' => $ATT_FACT,
          'ATT_LABEL' => $ATT_LABEL
        ];

        $arLoad = array(
          "MODIFIED_BY"         => $USER->GetID(),
          "IBLOCK_SECTION_ID"   => self::SECTION_KPI_EXTRA,
          "IBLOCK_ID"           => self::IBLOCK_ID_KPI,
          "PROPERTY_VALUES"     => $arProps,
          "NAME"                => $NAME,
          "ACTIVE"              => "Y",
        );

        if ($ID = $objRule->Add($arLoad)) {
            $logger->info('add extra rules', ['$arLoad' => $arLoad]);

            $arRes = $this->getRulesExtra($ID)['CRITICAL'];
        } else {
            $logger->info('error ' . $objRule->LAST_ERROR, ['$arLoad' => $arLoad]);
        }

        return $arRes[$ID];
    }


    /**
     * Сохраняет значения дополнительных правил расчета KPI при изменении
     *
     * @param array $data
     * @return boolean
     */
    public function saveRulesExtraAction($data)
    {
        $arSave = [];

        foreach ($data as $item) {
            $arItem = json_decode($item, true);
            $arSave['UF_KPI_TEST_PROGRESS'] = $arItem['progress_value'];
            $arSave['UF_KPI_TEST_FORMULA_EXT_CRITICAL'] = $arItem['formula_critical'];
            $arSave['UF_KPI_TEST_FORMULA_EXT_PROGRESS'] = $arItem['formula_progress'];

            if ($arItem['id']) {
                CIBlockElement::SetPropertyValuesEx($arItem['id'], self::IBLOCK_ID_KPI, array('ATT_FACT' => $arItem['value']));
            }
        }

        $this->updateSection(self::SECTION_KPI_EXTRA, $arSave);
        $this->saveKPIExt(true);

        return true;
    }


    /**
     * Отменяет значения дополнительных правил расчета KPI при изменении
     *
     * @return array
     * @throws Exception
     */
    public function returnSavedKPIExtAction()
    {
        $arSaveKPIs = [];
        $arRes = [];
        global $USER;
        global $DB;


        $arSelectWP = ["UF_KPI_TEST_FACTS_CRITICAL"];
        $arFilterWP = ["IBLOCK_ID" => self::IBLOCK_ID_KPI, "ID" => self::SECTION_KPI_EXTRA];
        $rsWP = CIBlockSection::GetList(['sort' => 'asc'], $arFilterWP, false, $arSelectWP);
        while ($arFields = $rsWP->GetNext()) {
            $arSaveKPIs = unserialize(htmlspecialcharsBack($arFields['UF_KPI_TEST_FACTS_CRITICAL']));
        }


        $logger = new Logger('RETURN_KPI_EXT_VALUES');
        $logger->pushHandler(new StreamHandler($_SERVER['DOCUMENT_ROOT'] . '/local/logs/test-kpi/kpi_return.log', Logger::INFO));



        $objElementUpdate = new CIBlockElement();
        foreach ($arSaveKPIs as $id => $values) {
            $arLoadArray = [
              "MODIFIED_BY"         => $USER->GetID(),
              "IBLOCK_SECTION_ID"   => self::SECTION_KPI_EXTRA,
              "PROPERTY_VALUES"     => $values,
              "NAME"                => $values['NAME'],
              "ACTIVE"              => "Y",
            ];

            if ($res = $objElementUpdate->Update($id, $arLoadArray)) {
                $logger->info('UPDATE KPI EXT VALUES', ['$arLoadArray' => $arLoadArray]);
            } else {
                $logger->info('ERROR return KPI EXT VALUES', ['error' => $objElementUpdate->LAST_ERROR]);
            }
        }

        $arDeleteKPI = [];

        $arSelectKPI = ["ID", "IBLOCK_ID", "NAME"];
        $arFilterKPI = ["IBLOCK_ID" => self::IBLOCK_ID_KPI, "IBLOCK_SECTION_ID" => self::SECTION_KPI_EXTRA];
        $rsWP = CIBlockElement::GetList(['sort' => 'asc'], $arFilterKPI, false, false, $arSelectKPI);
        while ($arFields = $rsWP->GetNext()) {
            if (!in_array($arFields['ID'], array_keys($arSaveKPIs))) {
                $arDeleteKPI[] = $arFields['ID'];
            }
        }

        if (count($arDeleteKPI) > 0) {
            $logger->info('Delete array for KPI EXT is ready', ['array' => $arDeleteKPI, 'keysIssetKPI' => array_keys($arSaveKPIs)]);


            if (CIBlock::GetPermission(self::IBLOCK_ID_KPI) >= 'W') {
                $DB->StartTransaction();

                foreach ($arDeleteKPI as $id) {
                    if (!CIBlockElement::Delete($id)) {
                        $messageError = 'Элемент с ID: ' . $id . 'не удален. Ошибка удаления';
                        $arRes['errors'][] = $messageError;
                        $logger->info($messageError);
                        $DB->Rollback();
                    } else {
                        $DB->Commit();
                        $logger->info('Delete KPI with ID: ' . $id);
                    }
                }
            } else {
                $messageError = 'Недостаточно прав для удаления';
                $arRes['errors'][] = $messageError;
            }
        }

        return $arRes;
    }


    /**
     * Возвращает формулы дополнительных расчетов KPI
     *
     * @return array
     */
    public function getFormulasExtAction()
    {
        $arFormulas = [];

        $arSelect = ["UF_KPI_TEST_FORMULA_EXT_CRITICAL", "UF_KPI_TEST_FORMULA_EXT_PROGRESS"];
        $arFilter = ["IBLOCK_ID" => self::IBLOCK_ID_KPI, "ACTIVE" => "Y", "ID" => self::SECTION_KPI_EXTRA];
        $rs = CIBlockSection::GetList(['sort' => 'asc'], $arFilter, false, $arSelect);
        while ($arFields = $rs->GetNext()) {
            $arFormulas['critical'] = $arFields['UF_KPI_TEST_FORMULA_EXT_CRITICAL'];
            $arFormulas['progress'] = $arFields['UF_KPI_TEST_FORMULA_EXT_PROGRESS'];
        }

        return $arFormulas;
    }


    /**
     * Обновляет значения KPI для пользователей
     *
     * @param string[] $data
     * @return array
     * @throws Exception
     */
    public function updateUsersKPIAction($data)
    {
        $arUsers = [];

        foreach ($data as $user) {
            $arUser = json_decode($user, true);
            $arUsers[] = $this->saveUserKPI($arUser);
        }

        $this->calcIntegralKPI();


        return $arUsers;
    }


    /**
     * Сохраняет значения оклада для должности
     *
     * @param string[] $data
     * @return array
     */
    public function saveDepartmentWPSalaryAction($data)
    {
        $arWPs = [];

        foreach ($data as $wp) {
            $arWP = json_decode($wp, true);
            CIBlockElement::SetPropertyValuesEx($arWP['ID'], false, array('ATT_SALARY' => $arWP['ATT_SALARY']));
        }

        return $arWPs;
    }


    /**
     * Возвращает список критических KPI
     *
     * @return array
     */
    public function getCriticalKPIsAction()
    {
        $arRes = [];
        $intCount = 0;

        $arSelect = [
          "ID",
          "IBLOCK_ID",
          "PROPERTY_ATT_LABEL",
        ];

        $arFilter = ["IBLOCK_ID" => self::IBLOCK_ID_KPI, "IBLOCK_SECTION_ID" => self::SECTION_KPI_EXTRA];
        $rsWP = CIBlockElement::GetList(['sort' => 'asc'], $arFilter, false, false, $arSelect);
        $intAllCount = $rsWP->SelectedRowsCount();
        while ($arFields = $rsWP->GetNext()) {
            $arRes[$intCount]['value'] = $arFields['PROPERTY_ATT_LABEL_VALUE'];
            $arRes[$intCount]['label'] = $arFields['PROPERTY_ATT_LABEL_VALUE'] . '/' . $intAllCount;

            $intCount++;
        }

        return $arRes;
    }


    /**
     * Расчитывает KPI пользователей всего управления
     * Отправляет данные управления в виде документов docx и pdf
     *
     * @param string $debug
     * @return array
     * @throws PHPExcel_Exception
     * @throws PHPExcel_Writer_Exception
     */
    public function dataToHRDAction($debug)
    {
        FormatDate('f', false);
        $arDebug = [];
        $arPathDocx = [];



        if (CModule::IncludeModule("nkhost.phpexcel")) {
            global $PHPEXCELPATH;
            require_once($PHPEXCELPATH . '/PHPExcel.php');

            $xls = new PHPExcel();
            $xls->setActiveSheetIndex(0);
            $sheet = $xls->getActiveSheet();
            $sheet->setTitle('Данные');
// TODO Make real name sheet;
            $strTemplatePath = '/local/components/citto/kpi/templates/main/';
            $strDirSave = 'reports';
            $strPathToSaveDir = $_SERVER['DOCUMENT_ROOT'] . $strTemplatePath . $strDirSave;

            $strDateNow = date('d-m-Y');
            $strDateNowDots = date('d.m.Y');
            $strFileExt = '.xlsx';
            $strFileName = 'test-' . $strDateNow . $strFileExt;
            $strCurrentMonth = FormatDate('f');
            $strColor = 'FFFF00';

            $arDMY = explode(' ', FormatDateFromDB($strDateNow, 'DD MMMM YYYY'));
            $strD = $arDMY[0];
            $strMY = "$arDMY[1] $arDMY[2]";


            $arHeaderDepartment = [
              ['NAME' => 'ФИО', 'MERGE' => true, 'CELL' => 'A'],
              ['NAME' => 'Показатель Kn', 'MERGE' => true, 'CELL' => 'B'],
              ['NAME' => 'вес', 'MERGE' => true, 'CELL' => 'C'],
              ['NAME' => 'плановый показатель', 'MERGE' => true, 'CELL' => 'D'],
              ['NAME' => 'фактический показатель', 'MERGE' => false, 'CELL' => 'E'],
              ['NAME' => 'процент досстижения', 'MERGE' => false, 'CELL' => 'F'],
              ['NAME' => 'процент выполнения KPI ', 'MERGE' => false, 'CELL' => 'G'],
              ['NAME' => 'оклад (руб.)', 'MERGE' => false, 'CELL' => 'H'],
              ['NAME' => '% от оклада', 'MERGE' => false, 'CELL' => 'I'],
              ['NAME' => '% округленный', 'MERGE' => false, 'CELL' => 'J'],
              ['NAME' => 'размер выплаты руб.', 'MERGE' => false, 'CELL' => 'K'],
            ];
            $arGovernment = $this->getDataGovernment();

            $strGovName = $arGovernment['GOVERNMENT_NAME'];


            $director = $this->getDirectorByDepartment(intval($arGovernment['GOVERNMENT']));





            function SetTextCenterInCell($objSheet, string $strCell)
            {
                $objSheet->getStyle($strCell)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $objSheet->getStyle($strCell)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objSheet->getStyle($strCell)->getAlignment()->setWrapText(true);
            }

            $strNewFilePath = null;

            $arDataDocx['DIRECTOR_WP'] = $director['WORK_POSITION'];
            $arDataDocx['DIRECTOR_NAME'] = $director['NSL'];


            $strTextHeader = "Приложение к служебной записке\n {$director['WORK_POSITION']}\n {$director['NSL']}\n от $strDateNowDots г.";



            $sheet->mergeCells('A1:F1');
            $sheet->setCellValue("G1", $strTextHeader);
            $sheet->mergeCells('G1:K1');

            $sheet->getRowDimension('1')->setRowHeight(100);

            SetTextCenterInCell($sheet, 'G1');
            $sheet->getStyle('G1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
            $sheet->getStyle('G1')->getFill()->getStartColor()->setRGB($strColor);


            $COUNTER = 0;
            $strCountRows = '2';
            $strUsers = '';

            $intCountUsersForDocx = 0;
            foreach ($arGovernment['DEPARTMENT_DATA'] as $id => $department) {
                $arDataDocx = [];

                $intCountUsers = 1;

                $strDepartmentName = htmlspecialcharsback($arGovernment['CURRENT_DEPARTMENT'][$id]['NAME']);


                $strTextTableName = "Расчет показателей эффективности работы за месяц работников $strDepartmentName:";

                // DEPARTMENT NAME
                $sheet->mergeCells("A$strCountRows:K$strCountRows");
                $sheet->setCellValue("A$strCountRows", $strTextTableName);
                SetTextCenterInCell($sheet, "A$strCountRows");
                $sheet->getStyle("A$strCountRows")->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                $sheet->getStyle("A$strCountRows")->getFill()->getStartColor()->setRGB($strColor);
                // MONTH
                $strCountRows++;
                $sheet->mergeCells("E$strCountRows:K$strCountRows");
                $sheet->setCellValue("E$strCountRows", $strCurrentMonth);
                SetTextCenterInCell($sheet, "E$strCountRows");
                $sheet->getStyle("E$strCountRows")->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                $sheet->getStyle("E$strCountRows")->getFill()->getStartColor()->setRGB($strColor);

                $intNumRow = intval($strCountRows);
                foreach ($arHeaderDepartment as $cell) {
                    $intNumRow = intval($strCountRows) + 1;
                    $cellPlace = $cell['CELL'] . $intNumRow;
                    if ($cell['MERGE']) {
                        $intNumRow = intval($strCountRows);
                        $cellPlace = $cell['CELL'] . $intNumRow;
                        $cellPlaceNext = $cell['CELL'] . ($intNumRow + 1);
                        $sheet->mergeCells("$cellPlace:$cellPlaceNext");
                    } else {
                        $intNumRow = intval($strCountRows) + 1;
                        $cellPlace = $cell['CELL'] . $intNumRow;
                    }
                    $sheet->setCellValue($cellPlace, $cell['NAME']);
                    SetTextCenterInCell($sheet, $cellPlace);
                }

                $arDebug['$intCountNextRow'] = $intCountNextRow = intval($strCountRows) + 2;
// 5
                foreach ($department['RESULT'] as $depID => $arStaff) {
                    foreach ($arStaff as $userID => $arUser) {
                        $intCountKPIDepartment = count($arUser['ATT_VALUE_KPI']);
                        $arDebug['$intCountRowsForUser'][] = $intCountRowsForUsers = $intCountKPIDepartment + 1;

                        $cellFIO = 'A' . strval($intCountNextRow);

                        $cellIndicatorCritical = 'B' . strval($intCountNextRow);
                        $cellIndicatorCriticalWeight = 'C' . strval($intCountNextRow);
                        $cellIndicatorCriticalPlan = 'D' . strval($intCountNextRow);
                        $cellIndicatorCriticalFact = 'E' . strval($intCountNextRow);

                        $cellIndicatorProgress = 'B' . strval($intCountNextRow + 1);
                        $cellIndicatorProgressWeight = 'C' . strval($intCountNextRow + 1);
                        $cellIndicatorProgressPlan = 'D' . strval($intCountNextRow + 1);
                        $cellIndicatorProgressFact = 'E' . strval($intCountNextRow + 1);

                        $cellPercentComplete = 'G' . strval($intCountNextRow);
                        $cellSalary = 'H' . strval($intCountNextRow);
                        $cellSalaryPercent = 'I' . strval($intCountNextRow);
                        $cellSalaryPercentFloor = 'J' . strval($intCountNextRow);
                        $cellSalaryResult = 'K' . strval($intCountNextRow);


                        $intOffset = $intCountNextRow + $intCountRowsForUsers;

                        $cellFIOMerge = 'A' . strval($intOffset);
                        $cellPercentCompleteMerge = 'G' . strval($intOffset);
                        $cellSalaryMerge = 'H' . strval($intOffset);
                        $cellSalaryPercentMerge = 'I' . strval($intOffset);
                        $cellSalaryPercentFloorMerge = 'J' . strval($intOffset);
                        $cellSalaryResultMerge = 'K' . strval($intOffset);

                        $sheet->mergeCells("$cellFIO:$cellFIOMerge");
                        $sheet->mergeCells("$cellPercentComplete:$cellPercentCompleteMerge");
                        $sheet->mergeCells("$cellSalary:$cellSalaryMerge");
                        $sheet->mergeCells("$cellSalaryPercent:$cellSalaryPercentMerge");
                        $sheet->mergeCells("$cellSalaryPercentFloor:$cellSalaryPercentFloorMerge");
                        $sheet->mergeCells("$cellSalaryResult:$cellSalaryResultMerge");


                        $arDebug['CELLS'][] = [$cellFIO, $cellFIOMerge];

                        $arDebug['NAME'] = $arUser['NAME'];



                        $sheet->setCellValue($cellFIO, $arUser['NAME']);
                        SetTextCenterInCell($sheet, $cellFIO);

                        $intCountIndicator = 0;
                        $percentComplete = 0;
                        $salaryPercent = 1;
                        $salaryPercentFloor = 1;
                        foreach ($arUser['ATT_VALUE_KPI'] as $kpi) {
                            $cellIndicator = 'B' . strval($intCountNextRow + $intCountIndicator);
                            $cellWeight = 'C' . strval($intCountNextRow + $intCountIndicator);
                            $cellTargetValue = 'D' . strval($intCountNextRow + $intCountIndicator);
                            $cellValue = 'E' . strval($intCountNextRow + $intCountIndicator);
                            $cellAchievePercent = 'F' . strval($intCountNextRow + $intCountIndicator);

                            $sheet->setCellValue($cellIndicator, $kpi['NAME']);
                            SetTextCenterInCell($sheet, $cellIndicator);

                            $sheet->setCellValue($cellWeight, "{$kpi['WEIGHT']}%");
                            SetTextCenterInCell($sheet, $cellWeight);

                            $sheet->setCellValue($cellTargetValue, "{$kpi['TARGET_VALUE']}%");
                            SetTextCenterInCell($sheet, $cellTargetValue);

                            if ($kpi['VALUE']) {
                                $sheet->setCellValue($cellValue, "{$kpi['VALUE']}%");
                                SetTextCenterInCell($sheet, $cellValue);

                                $achievePercent = floatval($kpi['VALUE']) / floatval($kpi['TARGET_VALUE']) * 100;

                                $sheet->setCellValue($cellAchievePercent, "$achievePercent%");
                                SetTextCenterInCell($sheet, $cellAchievePercent);

                                $percentComplete += floatval($kpi['WEIGHT']) * $achievePercent / 100;
                            }


                            $strCriticalKPIValue = '1';
                            if (intval($arUser['ATT_KPI_CRITICAL']) > 0) {
                                $strCriticalKPIValue = $arUser['ATT_KPI_CRITICAL'];
                            }
                            $floatProgressKPIValue = $this->getKPIProgress($strCriticalKPIValue);


                            $salaryPercent = $this->calculateKPIValue($userID, true);
                            $salaryPercentFloor = floor($salaryPercent);

                            $sheet->setCellValue($cellSalaryPercent, "$salaryPercent%");
                            SetTextCenterInCell($sheet, $cellSalaryPercent);

                            $sheet->setCellValue($cellSalaryPercentFloor, "$salaryPercentFloor%");
                            SetTextCenterInCell($sheet, $cellSalaryPercentFloor);



                            $intCountIndicator += 1;
                        }

                        if ($arUser['ATT_KPI_PROGRESS'] == 'Y') {
                            $percentComplete = $percentComplete + 20;
                        }
                        if ($percentComplete > 0) {
                            $sheet->setCellValue($cellPercentComplete, "$percentComplete%");
                            SetTextCenterInCell($sheet, $cellPercentComplete);
                        }

                        if ($intCountIndicator > 0) {
                            $intCountCriticalNextRow = $intCountNextRow + $intCountIndicator;
                            $intCountProgressNextRow = $intCountCriticalNextRow + 1;

                            $cellIndicatorCritical = 'B' . strval($intCountCriticalNextRow);
                            $cellIndicatorCriticalWeight = 'C' . strval($intCountCriticalNextRow);
                            $cellIndicatorCriticalPlan = 'D' . strval($intCountCriticalNextRow);
                            $cellIndicatorCriticalFact = 'E' . strval($intCountCriticalNextRow);

                            $cellIndicatorProgress = 'B' . strval($intCountProgressNextRow);
                            $cellIndicatorProgressWeight = 'C' . strval($intCountProgressNextRow);
                            $cellIndicatorProgressPlan = 'D' . strval($intCountProgressNextRow);
                            $cellIndicatorProgressFact = 'E' . strval($intCountProgressNextRow);
                        }

                        $sheet->setCellValue($cellIndicatorCritical, 'Критичекий KPI');
                        SetTextCenterInCell($sheet, $cellIndicatorCritical);
                        $sheet->setCellValue($cellIndicatorCriticalWeight, '100%');
                        SetTextCenterInCell($sheet, $cellIndicatorCriticalWeight);
                        $sheet->setCellValue($cellIndicatorCriticalPlan, '0%');
                        SetTextCenterInCell($sheet, $cellIndicatorCriticalPlan);

                        $sheet->setCellValue($cellIndicatorProgress, 'KPI развития');
                        SetTextCenterInCell($sheet, $cellIndicatorProgress);
                        $sheet->setCellValue($cellIndicatorProgressWeight, '100%');
                        SetTextCenterInCell($sheet, $cellIndicatorProgressWeight);

                        $floatValProgressPlan = 0.0;
                        $floatValProgressFact = '0';
                        if ($arUser['ATT_KPI_PROGRESS'] == 'Y') {
                            $floatValProgressPlan = 20.0;
                            $floatValProgressFact = 20;
                        }

                        $sheet->setCellValue($cellIndicatorProgressPlan, "$floatValProgressPlan%");
                        SetTextCenterInCell($sheet, $cellIndicatorProgressPlan);


                        if (intval($arUser['ATT_KPI_CRITICAL']) > 0) {
                            $sheet->setCellValue($cellIndicatorCriticalFact, "{$this->getCriticalKPI($arUser['ATT_KPI_CRITICAL'])['VALUE']}%");
                            SetTextCenterInCell($sheet, $cellIndicatorCriticalFact);
                        } else {
                            $sheet->setCellValue($cellIndicatorCriticalFact, '0%');
                            SetTextCenterInCell($sheet, $cellIndicatorCriticalFact);
                        }

                        $sheet->setCellValue($cellIndicatorProgressFact, "$floatValProgressFact%");
                        SetTextCenterInCell($sheet, $cellIndicatorProgressFact);

                        $floatSalary = floatval($department['WP_SALARY_REAL'][$arUser['WPID']]);
                        if ($floatSalary > 0) {
                            $sheet->setCellValue($cellSalary, $floatSalary);
                            SetTextCenterInCell($sheet, $cellSalary);

                            $salaryResult = $floatSalary * $salaryPercentFloor / 100;

                            if (floatval($arUser['ATT_SALARY']) > 1) {
                                $salaryResult = $salaryResult * floatval($arUser['ATT_SALARY']);
                            }
                            if ($salaryResult > 0) {
                                $sheet->setCellValue($cellSalaryResult, $salaryResult);
                                SetTextCenterInCell($sheet, $cellSalaryResult);
                            }
                        }

                        $intCountNextRow = $intCountNextRow + $intCountRowsForUsers + 1;


                        if ($arUser['NAME'] && $percentComplete >= 40) {
                            $percentKPI =  floor($this->calculateKPIValue($userID, true));

                            $intCountUsersForDocx++;

                            $arUsersResult[$intCountUsersForDocx]['NAME'] = $this->formatName(explode(' ', $arUser['NAME']));
                            $arUsersResult[$intCountUsersForDocx]['WP_NAME'] = $department['WP_NAMES_REAL'][$arUser['WPID']];
                            $arUsersResult[$intCountUsersForDocx]['KPI'] = $percentKPI;
                            $arUsersResult[$intCountUsersForDocx]['DEPARTMENT_NAME'] = $arGovernment['CURRENT_DEPARTMENT'][$id]['NAME'];
                        }

                        $intCountUsers++;
                    }
                }


                $arDebug['$strCountRows'][] = $strCountRows =  $intCountNextRow + 1;
                $strCountRows = strval($strCountRows);

                $arDebug['CELLS'][] = ['1', '1'];
            }

            uasort($arUsersResult, function ($a, $b) {
                return strcmp($a['NAME'], $b['NAME']);
            });

            $intIndexCount = 1;
            foreach ($arUsersResult as $idx => $user) {
                $strUsers .= <<<EOT
<br>$intIndexCount. {$user['NAME']}, {$user['WP_NAME']}, {$user['DEPARTMENT_NAME']}, в размере {$user['KPI']}% месячного должностного оклада; 
EOT;
                $intIndexCount++;
            }


            $arDataDocx['USERS'] = $strUsers;
            $arDataDocx['GOVERNMENT_NAME'] = $strGovName;
            $arDataDocx['DATE_D'] = $strD;
            $arDataDocx['DATE_MY'] = $strMY;
            $arDataDocx['DIRECTOR_WP'] = $director['WORK_POSITION'];
            $arDataDocx['DIRECTOR_NAME'] = $director['NSL'];


            $strFileNameDocx = "gov-report-$strDateNow";

            if (!file_exists($strPathToSaveDir)) {
                mkdir($strPathToSaveDir, 0775, true);
            }

            $objWriter = new PHPExcel_Writer_Excel2007($xls);
            $objWriter->save($strPathToSaveDir . '/' . $strFileName);

            if (CModule::IncludeModule("citto.integration")) {
                $pathDocx = Docx::generateDocument($strFileNameDocx, $arDataDocx, 'kpi');
            }

            $strRootPathDocx = $_SERVER['DOCUMENT_ROOT'] . $pathDocx;

            $arFile = CFile::MakeFileArray($strPathToSaveDir . '/' . $strFileName);
            $arFileDocx = CFile::MakeFileArray($strRootPathDocx);

// TODO Send file to email by id
            $inrFileReportID = CFile::SaveFile($arFile, 'kpi_reports');
            $inrFileReportDocxID = CFile::SaveFile($arFileDocx, 'kpi_reports');
            unlink($strPathToSaveDir . '/' . $strFileName);
            unlink($strRootPathDocx);
            $strNewFilePath = CFile::GetPath($inrFileReportID);
            $strNewFileDocxPath = CFile::GetPath($inrFileReportDocxID);
        }


        if ($debug == 'true') {
            return $arPathDocx;
        } else {
            return ['excel' => $strNewFilePath, 'docx' => $strNewFileDocxPath];
        }
    }


    /**
     * Создает функциональную единицу (должность)
     *
     * @param string $department        id отдела
     * @param string $new_work_position
     * @return array
     * @throws Exception
     */
    public function createWorkPositionAction($department, $new_work_position)
    {
        $arData = [];

        $intDepartmentID = intval($department);
        $strWorkPositionName  = $new_work_position;

        global $USER;
        $logger = new Logger('createWorkPositionAction()');
        $logger->pushHandler(new StreamHandler($_SERVER['DOCUMENT_ROOT'] . '/local/logs/test-kpi/kpi_create_work_positions.log', Logger::INFO));


        $objWorkPosition = new CIBlockElement();

        $arLoad = array(
          "MODIFIED_BY"         => $USER->GetID(),
          "IBLOCK_SECTION_ID"   => false,
          "IBLOCK_ID"           => self::IBLOCK_ID_KPI_STRUCT,
          "PROPERTY_VALUES"     => ['ATT_DEPARTMENT' => $intDepartmentID],
          "NAME"                => $strWorkPositionName,
          "CODE"                => CUtil::translit($strWorkPositionName . '_' . $intDepartmentID, 'ru', $this->arTranslitParams),
          "ACTIVE"              => "Y",
        );

        if ($intWorkPositionID = $objWorkPosition->Add($arLoad)) {
            $arData['id'] = $intWorkPositionID;
            $arData['name'] = $strWorkPositionName;
            $logger->info('Добавлена должность с ID: ' . $intWorkPositionID, ['$arLoad' => $arLoad]);
        } else {
            $logger->info('Ошибка добавления должности ' . $objWorkPosition->LAST_ERROR, ['$arLoad' => $arLoad]);
        }


        return [$intDepartmentID, $new_work_position, $intWorkPositionID];
    }


    /**
     * Назначает поьзователю отдел
     *
     * @param string $user_id       ID Пользователя
     * @param string $department_id ID отдела
     * @return array
     */
    public function addUserToDepartmentAction($user_id, $department_id)
    {
        $arData = [];

        global $DB;
        global $USER;
        $arUsers = $this->prepareAllUsersToSelect();
        $arCurrentUser = $arUsers[$user_id];
        $intIssetUserID = 0;

        $arSelectFU = ['ID', 'IBLOCK_ID', 'CODE'];
        $arFilterFU = ['IBLOCK_ID' => self::IBLOCK_ID_KPI_USERS, 'ACTIVE' => 'Y', 'CODE' => $user_id];
        $rsFU = CIBlockElement::GetList(['sort' => 'asc'], $arFilterFU, false, false, $arSelectFU);
        if ($rsFU->SelectedRowsCount() > 0) {
            while ($arFieldsFU = $rsFU->GetNext()) {
                $intIssetUserID = intval($arFieldsFU['ID']);
            }

            if ($intIssetUserID > 0) {
                $arData['id'] = $intIssetUserID;
                CIBlockElement::SetPropertyValuesEx($intIssetUserID, false, array('ATT_OTHER_DEPARTMENT' => $department_id));
                CIBlockElement::SetPropertyValuesEx($intIssetUserID, false, array('ATT_NOT_CONFIRMED' => 'Y'));
            }
        } else {
            $obAddUserToDepartment = new CIBlockElement();

            $strName = $arCurrentUser['LAST_NAME'] . ' ' . $arCurrentUser['NAME'] . ' ' . $arCurrentUser['SECOND_NAME'];
            $strDateProbationEnd = '';
            $strRegisterDate = $arCurrentUser['DATE_REGISTER'];
            $strRegisterDateFormat = 'DD.MM.YYYY HH:MI:SS';
            $strRegisterDateFormatNew = 'DD.MM.YYYY';
            $strRegisterDate = $DB->FormatDate($strRegisterDate, $strRegisterDateFormat, $strRegisterDateFormatNew);

            $strDateThreeMonthAgo = date('d.m.Y', strtotime('-3 month'));

            $intCompareResult = $DB->CompareDates($strRegisterDate, $strDateThreeMonthAgo);

            if ($intCompareResult > -1) {
                $strDateProbationEnd = date('d.m.Y', strtotime('+3 month', strtotime($strRegisterDate)));
            }


            $arProps = [
              'ATT_DEPARTMENT' => $arCurrentUser['UF_DEPARTMENT'][0],
              'ATT_OTHER_DEPARTMENT' => $department_id,
              'ATT_PROBATION_END' => $strDateProbationEnd,
              'ATT_NOT_CONFIRMED' => 'Y',
            ];


            $arLoad = array(
              "MODIFIED_BY"         => $USER->GetID(),
              "IBLOCK_SECTION_ID"   => false,
              "IBLOCK_ID"           => self::IBLOCK_ID_KPI_USERS,
              "PROPERTY_VALUES"     => $arProps,
              "NAME"                => $strName,
              "ACTIVE"              => "Y",
              'CODE'                => $user_id
            );

            if ($intUserID = $obAddUserToDepartment->Add($arLoad)) {
                $arData['id'] = $intUserID;
            } else {
                $arData['error'] = $obAddUserToDepartment->LAST_ERROR;
            }
        }

        return $arData;
    }


    /**
     * Отменяет изменения в отделе
     *
     * @param string $department_id
     */
    public function cancelDepartmentChangesAction($department_id)
    {
        $this->returnDepartmentUserData($department_id);
    }


    /**
     * Сохраняет изменения в отделе
     *
     * @param string $department_id
     * @return array
     */
    public function saveDepartmentChangesAction($department_id)
    {
        global $DB;
        $arData = [];

        $arSelect = [
          'ID',
          'IBLOCK_ID',
          'NAME',
          'CODE',
          'PROPERTY_ATT_WORK_POSITION',
          'PROPERTY_ATT_SALARY',
          'PROPERTY_ATT_OTHER_DEPARTMENT',
          'PROPERTY_ATT_PROBATION_END',
          'PROPERTY_ATT_SAVE_DEPARTMENT_DATA',
          'PROPERTY_ATT_NOT_CONFIRMED',
        ];
        $arFilter = [
          'IBLOCK_ID' => self::IBLOCK_ID_KPI_USERS,
          'ACTIVE' => 'Y',
          [
            'LOGIC' => 'OR',
            'PROPERTY_ATT_DEPARTMENT' => $department_id,
            'PROPERTY_ATT_OTHER_DEPARTMENT' => $department_id,
          ],
        ];

        $issetErrors = false;
        $intCountUsers = 0;
        $rs = CIBlockElement::GetList(['sort' => 'asc'], $arFilter, false, false, $arSelect);
        while ($arFields = $rs->GetNext()) {
            $strDateProbationEnd = $arFields['PROPERTY_ATT_PROBATION_END_VALUE'];
            $intWorkPosition = intval($arFields['PROPERTY_ATT_WORK_POSITION_VALUE']);

            $arData[$intCountUsers]['id'] = $arFields['ID'];
            $arData[$intCountUsers]['fio'] = $arFields['NAME'];
            $arData[$intCountUsers]['user_id'] = $arFields['CODE'];
            $arData[$intCountUsers]['probation'] = $strDateProbationEnd;
            $arData[$intCountUsers]['work_position'] = $intWorkPosition;
            $arData[$intCountUsers]['error'] = false;

            if ($strDateProbationEnd && $intWorkPosition == 0) {
                $strDateNow = date('d.m.Y');
                $intCompareResult = $DB->CompareDates($strDateNow, $strDateProbationEnd);

                if ($intCompareResult > -1) {
                    $arData[$intCountUsers]['error'] = true;
                    $issetErrors = true;
                }
            }

            $intCountUsers++;
        }

        if (!$issetErrors) {
            $this->saveDepartmentUserData($department_id, true);
        }


        return $arData;
    }


    /**
     * Удаляет должность
     *
     * @param string $wp_id
     * @return array
     */
    public function deleteWorkPositionAction($wp_id)
    {
        $arData = [];
        global $DB;

        $intWorkPositionID = intval($wp_id);

        if ($intWorkPositionID > 0) {
            if (CIBlock::GetPermission(self::IBLOCK_ID_KPI_STRUCT) >= 'W') {
                $DB->StartTransaction();
                if (!CIBlockElement::Delete($intWorkPositionID)) {
                    $arData['error'] = true;
                    $DB->Rollback();
                } else {
                    $DB->Commit();
                }
            }
        }

        return $arData;
    }


    public function saveNotifiesAction($data)
    {
        $arNotifies = [];

        foreach ($data as $notify) {
            $arNotify = json_decode($notify, true);

            if ($arNotify['PROPERTY'] == 'ATT_NOTIFY') {
                $arNotifies[$arNotify['ID']][$arNotify['PROPERTY']][] = $arNotify['VALUE'];

            } else {
                $arNotifies[$arNotify['ID']][$arNotify['PROPERTY']] = $arNotify['VALUE'];
            }
        }

        foreach ($arNotifies as $id => $props) {
            CIBlockElement::SetPropertyValuesEx($id, false, array('ATT_NOTIFY' => $props['ATT_NOTIFY']));
            if ($props['ATT_DEADLINE_DAY']) {
                CIBlockElement::SetPropertyValuesEx($id, false, array('ATT_DEADLINE_DAY' => $props['ATT_DEADLINE_DAY']));
            }
        }

        return $arNotifies;
    }


    public function addAccessToUsersAction($data)
    {

        global $USER;
        $logger = new Logger('addAccessToUsersAction($data)');
        $logger->pushHandler(new StreamHandler($_SERVER['DOCUMENT_ROOT'] . '/local/logs/test-kpi/kpi_add_access_to_department.log', Logger::INFO));

        $objUser = new CUser;
        $arNewAccess = [];
        $arNewValuesAccess = [];
        $arNewValuesAssistant = [];

        foreach ($data as $arData) {
            $arValues = json_decode($arData, true);
            $logger->info('Данные ', ['$arValues' => $arValues,]);
            $arNewAccess[$arValues['USER_ID']][$arValues['UF_FIELD']][] = $arValues['DEPARTMENT_ID'];
        }
        $logger->info('Данные 2', ['$arNewAccess' => $arNewAccess,]);

        foreach ($arNewAccess as $userID => $values) {

            $rsUser = CUser::GetByID(intval($userID));
            $arUser = $rsUser->Fetch();

            foreach ($values['UF_KPI_ACCESS_TO_DEPARTMENT'] as $departmentID) {
                $arNewValuesAccess[$userID]['UF_KPI_ACCESS_TO_DEPARTMENT'][] = intval($departmentID);
            }
//            foreach ($arUser['UF_KPI_ACCESS_TO_DEPARTMENT'] as $oldDepartmentID) {
//                if (!in_array($oldDepartmentID, $arNewValuesAccess[$userID]['UF_KPI_ACCESS_TO_DEPARTMENT'])) {
//                    $arNewValuesAccess[$userID]['UF_KPI_ACCESS_TO_DEPARTMENT'][] = $oldDepartmentID;
//                }
//            }

            foreach ($values['UF_KPI_ASSISTANT_TO_DEPARTMENT'] as $departmentID) {
                $arNewValuesAccess[$userID]['UF_KPI_ASSISTANT_TO_DEPARTMENT'][] = intval($departmentID);
            }
//            foreach ($arUser['UF_KPI_ASSISTANT_TO_DEPARTMENT'] as $oldDepartmentID) {
//                if (!in_array($oldDepartmentID, $arNewValuesAccess[$userID]['UF_KPI_ASSISTANT_TO_DEPARTMENT'])) {
//                    $arNewValuesAccess[$userID]['UF_KPI_ASSISTANT_TO_DEPARTMENT'][] = $oldDepartmentID;
//                }
//            }






        }



        $arAccess = [];
        $arAssistant = [];
        foreach ($arNewValuesAccess as $userID => $values) {

            if ($values['UF_KPI_ACCESS_TO_DEPARTMENT'][0]) {
                $arAccess[$userID] = $values['UF_KPI_ACCESS_TO_DEPARTMENT'];
            }
            if ($values['UF_KPI_ASSISTANT_TO_DEPARTMENT'][0]) {
                $arAssistant[$userID] = $values['UF_KPI_ASSISTANT_TO_DEPARTMENT'];
            }
        }

        $by = 'id';
        $order = 'desc';
        $filterUsersAccess = [
          'ACTIVE' => 'Y',
          '!UF_KPI_ACCESS_TO_DEPARTMENT' => false,
        ];
        $arParamsUsersAccess = [
          'FIELDS' => ['WORK_POSITION', 'LAST_NAME', 'NAME', 'SECOND_NAME', 'ID'],
          'SELECT' => ['UF_KPI_ACCESS_TO_DEPARTMENT']
        ];
        $filterUsersAssistant = [
          'ACTIVE' => 'Y',
          '!UF_KPI_ASSISTANT_TO_DEPARTMENT' => false,
        ];
        $arParamsUsersAssistant = [
          'FIELDS' => ['WORK_POSITION', 'LAST_NAME', 'NAME', 'SECOND_NAME', 'ID'],
          'SELECT' => ['UF_KPI_ASSISTANT_TO_DEPARTMENT']
        ];


        $rsUsersAccess = CUser::GetList($by, $order, $filterUsersAccess, $arParamsUsersAccess);
        $rsUsersAssistant = CUser::GetList($by, $order, $filterUsersAssistant, $arParamsUsersAssistant);


        while ($arUserAccess = $rsUsersAccess->Fetch()) {
            $logger->info('--ACCESS-- Удаление доступа ' . $arUserAccess['LAST_NAME'], ['$arUserAccess' => $arUserAccess,]);
            $objUser->Update(intval($arUserAccess['ID']), ['UF_KPI_ACCESS_TO_DEPARTMENT' => array()]);

        }
        while ($arUserAssistant = $rsUsersAssistant->Fetch()) {
            $logger->info('--ASSISTANT-- Удаление доступа ' . $arUserAssistant['LAST_NAME'], ['$arUserAssistant' => $arUserAssistant,]);
            $objUser->Update(intval($arUserAssistant['ID']), ['UF_KPI_ASSISTANT_TO_DEPARTMENT' => array()]);

        }

        foreach ($arNewValuesAccess as $userID => $values) {

            $objUser->Update(intval($userID), ['UF_KPI_ACCESS_TO_DEPARTMENT' => $values['UF_KPI_ACCESS_TO_DEPARTMENT']]);
            $objUser->Update(intval($userID), ['UF_KPI_ASSISTANT_TO_DEPARTMENT' => $values['UF_KPI_ASSISTANT_TO_DEPARTMENT']]);

        }



        $logger->info('Добавление доступа ', ['$arAccess' => $arAccess, '$arAssistant' => $arAssistant, ]);

        return $arNewValuesAccess;
    }
}
