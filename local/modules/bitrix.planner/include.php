<?

use Bitrix\Main\Loader;
use Bitrix\Main\UserTable;
use Citto\Integration\Source1C;

Loader::includeModule('workflow');
Loader::includeModule('bizproc');
Loader::registerAutoLoadClasses(
    'bitrix.planner',
    [
        '\HolidayList\CStructure' => '/lib/CStructure.php',
        '\HolidayList\CUsers' => '/lib/CUsers.php',
        '\HolidayList\CVacations' => '/lib/CVacations.php',
        '\HolidayList\CActions' => '/lib/CActions.php',
        '\HolidayList\CBeforeInit' => '/lib/CBeforeInit.php',
        '\HolidayList\CEditVacations' => '/lib/CEditVacations.php',
    ]
);

class CBitrixPlanner
{
    function Agent1sUsers()
    {
        set_time_limit(0);
        $rConnect = Source1C::Connect1C();

//      $order = array('sort' => 'asc');
//      $tmp = 'sort'; // параметр проигнорируется методом, но обязан быть
//      $rsUsers = CUser::GetList($order, $tmp, array(), ['FIELDS' => ['XML_ID']]);

//      $arXML = [];
//      while ($r = $rsUsers->getNext()) {
//          if ($r['XML_ID'][0] == 'S')
//              array_push($arXML, $r['XML_ID']);
//      }
        $arXML = '';
        $obCache = new CPHPCache();

        $obCache->InitCache(86400, 'allusers1s', '/bitrix/cache/VacationLeftovers');
        $obCache->StartDataCache();
        $rRespone = Source1C::GetArray($rConnect, 'VacationLeftovers', ['SIDorINNList' => $arXML]);
        $rRespone = $rRespone['Data']['VacationLeftovers']['EmployeeVacationLeftovers'];
        $sids = [];

        if (isset($rRespone['SID'])) {
            foreach ($rRespone as $item) {
                if (is_array($item)) {
                    $sids[ $rRespone['SID'] ]['item'] = $item;
                }
            }
        } else {
            foreach ($rRespone as $item) {
                $sid = $item['SID'];
                $sids[ $sid ]['item'] = $item;
            }
        }

        $obCache->EndDataCache(['result' => $sids]);
        return 'CBitrixPlanner::Agent1sUsers();';
    }

    function AgentAlertVacation()
    {
        $iBlockPlanner = 3;
        $iBlockBPCit = 638;
        $iBlockTemplateBPCit = 1298;

        $iBlockBPCZN = 633;
        $iBlockTemplateBPCZN = 1260;

        $arDepartments = CIntranetUtils::GetDeparmentsTree(57, true);
        $arDepartments[] = 57;
        $arDepartments = array_merge(CIntranetUtils::GetDeparmentsTree(2971, true), $arDepartments);
        $arDepartments[] = 2971;

        $arUsersCIT = [];
        $arUsersCZN = [];
        $arDepartmentsCIT = CIntranetUtils::GetIBlockSectionChildren(57);
        $arDepartmentsCZN = CIntranetUtils::GetIBlockSectionChildren(2971);
        $obUsers = CUser::GetList($by = 'LAST_NAME', $order = 'ASC', ['ID'], ['SELECT' => ['UF_DEPARTMENT']]);
        while ($arUser = $obUsers->getNext()) {
            foreach ($arUser['UF_DEPARTMENT'] as $iDepartID) {
                if (in_array($iDepartID, $arDepartmentsCIT)) {
                    array_push($arUsersCIT, $arUser['ID']);
                } elseif (in_array($iDepartID, $arDepartmentsCZN)) {
                    array_push($arUsersCZN, $arUser['ID']);
                }
            }
        }

        $arCitUsers = [];
        $orm = UserTable::getList([
            'select'    => ['ID', 'UF_DEPARTMENT'],
            'filter'    => ['ACTIVE' => 'Y']
        ]);
        while ($arUser = $orm->fetch()) {
            $arDiff = array_intersect($arUser['UF_DEPARTMENT'], $arDepartments);
            if (!empty($arDiff)) {
                $arCitUsers[] = $arUser['ID'];
            }
        }

        $obDate = new DateTime('NOW');
        $sCurrdate = $obDate->format('d.m.Y');
        $sCurrDateMargin = $obDate->add(new DateInterval('P30D'))->format('d.m.Y');

        $obUsers = HolidayList\CUsers::getInstance();
        $arUsersCadrs = $obUsers->getUsersCadrs();

        $arFilter = [
            'IBLOCK_ID'             => IntVal($iBlockPlanner),
            '><DATE_ACTIVE_FROM'    => [$sCurrdate, $sCurrDateMargin],
            // 'ACTIVE' => 'Y',
            'PROPERTY_USER'         => $arCitUsers
        ];
        $obVacations = CIBlockElement::GetList(
            [
                'SORT'              => 'ASC',
                'PROPERTY_PRIORITY' => 'ASC'
            ],
            $arFilter,
            [
                'DATE_ACTIVE_FROM',
                'DATE_ACTIVE_TO',
                'PROPERTY_USER',
                'PROPERTY_UF_WHO_APPROVE',
            ]
        );
        $arHolidays = (new HolidayList\CVacations())->getHolidays(date('Y'))['holydays'];
        $arVacations = [];
        while ($arVacation = $obVacations->GetNext()) {
            $arWhoApprove = json_decode($arVacation['~PROPERTY_UF_WHO_APPROVE_VALUE']);
//            foreach ($arWhoApprove as $iUserID) {
//                if (in_array($iUserID, $arUsersCadrs)) {
                    $iUserID = $arVacation['~PROPERTY_USER_VALUE'];
                    $sDateStart = $arVacation['DATE_ACTIVE_FROM'];
                    $iCountDays = (new DateTime($sDateStart))->diff(new DateTime($arVacation['DATE_ACTIVE_TO']))->d + 1;

                    $iTimestampFrom = strtotime($sDateStart);
                    $iTimestampTo = strtotime($arVacation['DATE_ACTIVE_TO']);

                    for ($iCurrent = $iTimestampFrom; $iCurrent <= $iTimestampTo; $iCurrent += 86400) {
                        if (in_array($iCurrent, $arHolidays)) {
                            $iCountDays--;
                        }
                    }

                    $arVacations[$iUserID] = [
                        'DATE_FROM' => $sDateStart,
                        'DAYS' => $iCountDays
                    ];
//                }
//            }
        }

        $arBPvacations = [];
        $obBPvacations = CIBlockElement::GetList(
            [
                'SORT'              => 'ASC',
                'PROPERTY_PRIORITY' => 'ASC'
            ],
            [
                'IBLOCK_ID'                 => [
                    $iBlockBPCit,
                    $iBlockBPCZN
                ],
                'PROPERTY_POLZOVATEL'       => $arCitUsers,
                '><PROPERTY_DATA_NACHALA'   => [
                    date('Y-m-d', strtotime($sCurrdate)),
                    date('Y-m-d', strtotime($sCurrDateMargin))
                ],
            ],
            [
                'PROPERTY_POLZOVATEL',
                'PROPERTY_DATA_NACHALA'
            ]
        );

        while ($arBPvacation = $obBPvacations->GetNext()) {
            $arBPvacations[$arBPvacation['PROPERTY_POLZOVATEL_VALUE']][] = $arBPvacation['PROPERTY_DATA_NACHALA_VALUE'];
        }

        $obEl = new CIBlockElement();
        foreach ($arVacations as $iUserID => $arVacation) {
            if (!in_array($arVacation['DATE_FROM'], $arBPvacations[$iUserID])) {
                $bIsCit = in_array($iUserID, $arUsersCIT);
                $arLoadProductArray = [
                    'MODIFIED_BY'       => $iUserID,
                    'CREATED_BY'        => $iUserID,
                    'IBLOCK_SECTION_ID' => false,
                    'IBLOCK_ID'         => $bIsCit ? $iBlockBPCit : $iBlockBPCZN,
                    'PROPERTY_VALUES'   => [
                        'POLZOVATEL'        => $iUserID,
                        'DATA_NACHALA'      => $arVacation['DATE_FROM'],
                        'KOLICHESTVO_DNEY'  => $arVacation['DAYS']
                    ],
                    'NAME'              => $bIsCit ? 'Отпуска ЦИТ' : 'Отпуска ЦЗН',
                    'ACTIVE'            => 'Y',
                    'PREVIEW_TEXT'      => '',
                ];

                $bDocumentid = $obEl->Add($arLoadProductArray);
                $iBlockTemplate = $bIsCit ? $iBlockTemplateBPCit : $iBlockTemplateBPCZN;

                if ($bDocumentid) {
                    $arErrorsTmp = [];
                    $sWfId = CBPDocument::StartWorkflow(
                        $iBlockTemplate,
                        ['lists', 'BizprocDocument', $bDocumentid],
                        ['TargetUser' => $iUserID],
                        $arErrorsTmp
                    );
                }
            }
        }

        return 'CBitrixPlanner::AgentAlertVacation();';
    }

    /**
     * @return string
     * Если руководители деактивированы, отправляем оповещения сотрудникам
     */
    function AgentAlertDeactivateHeads()
    {
        $obUsers = CUser::GetList($by = '', $order = '', [], ['SELECT' => ['UF_THIS_HEADS']]);
        $arUsers = [
            'ALL' => [],
            'DEACTIVATED' => []
        ];

        while ($arUser = $obUsers->GetNext()) {
            $arHeads = [];
            $sHeads = $arUser['UF_THIS_HEADS'];

            if ($sHeads) {
                $arHeads = explode('|', $sHeads);
            }

            if ($arUser['ACTIVE'] != 'Y') {
                $sFIO = "{$arUser['LAST_NAME']} {$arUser['NAME']} {$arUser['SECOND_NAME']}";
                $arUsers['DEACTIVATED'][$arUser['ID']] = $sFIO;
            }

            $arUsers['ALL'][$arUser['ID']] = [
                'STATUS' => $arUser['ACTIVE'],
                'EMAIL' => $arUser['EMAIL'],
                'HEADS' => $arHeads
            ];
        }

        foreach ($arUsers['ALL'] as $iUserID => $arUser) {
            if ($arUser['STATUS'] == 'Y') {
                $arDeactivatedHeads = [];
                foreach ($arUser['HEADS'] as $iHead) {
                    if ($arUsers['DEACTIVATED'][$iHead]) {
                        $arDeactivatedHeads[] = $arUsers['DEACTIVATED'][$iHead];
                    }
                }
                if (!empty($arDeactivatedHeads)) {
                    $sDeactivatedHeads = implode(', ', $arDeactivatedHeads);
                    $sText = "Внимание! Следующие пользователи были деактивированы: $sDeactivatedHeads. Замените своих руководителей в модуле 'График отпусков'";

                    CIMMessenger::Add(array(
                        'TITLE' => "Корректировки в модуле графике 'График отпусков'",
                        'MESSAGE' => $sText,
                        'TO_USER_ID' => $iUserID,
                        'FROM_USER_ID' => 2661,
                        'MESSAGE_TYPE' => 'S',
                        'NOTIFY_MODULE' => 'intranet',
                        'NOTIFY_TYPE' => 2,
                    ));

                    if ($sEmail = $arUser['EMAIL']) {
                        CEvent::Send("ALERT_LPA", 's1', ['TEXT' => $sEmail, 'THEME' => "Корректировки в модуле графике 'График отпусков'", 'EMAIL_TO' => $sEmail]);
                    }
                }
            }
        }

        return 'CBitrixPlanner::AgentAlertDeactivateHeads();';
    }

    function OnBuildGlobalMenu(&$aGlobalMenu, &$aModuleMenu)
    {
        if ($GLOBALS['APPLICATION']->GetGroupRight('main') < 'R') {
            return;
        }

        $MODULE_ID = basename(dirname(__FILE__));
        $aMenu = [
            'parent_menu'   => 'global_menu_settings',
            'section'       => $MODULE_ID,
            'sort'          => 50,
            'text'          => $MODULE_ID,
            'title'         => '',
            'icon'          => '',
            'page_icon'     => '',
            'items_id'      => $MODULE_ID.'_items',
            'more_url'      => [],
            'items'         => [],
        ];

        if (file_exists($path = dirname(__FILE__).'/admin')) {
            if ($dir = opendir($path)) {
                $arFiles = [];

                while (false !== $item = readdir($dir)) {
                    if (in_array($item, ['.', '..', 'menu.php'])) {
                        continue;
                    }

                    if (!file_exists($file = $_SERVER['DOCUMENT_ROOT'].'/bitrix/admin/'.$MODULE_ID.'_'.$item)) {
                        file_put_contents($file, '<'.'? require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/'.$MODULE_ID.'/admin/'.$item.'");?'.'>');
                    }

                    $arFiles[] = $item;
                }

                sort($arFiles);

                foreach ($arFiles as $item) {
                    $aMenu['items'][] = [
                        'text'      => $item,
                        'url'       => $MODULE_ID . '_' . $item,
                        'module_id' => $MODULE_ID,
                        'title'     => '',
                    ];
                }
            }
        }

        $aModuleMenu[] = $aMenu;
    }
}
