<?

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) {
    die();
}

use \Bitrix\Main\Data\Cache;

define('BP_KEY', "MyLYWLBi/siVwashq1fWA6agr/UY00mt");
define('BP_CIPHER', "aes-128-gcm");
setlocale(LC_CTYPE, "ru_RU.UTF-8");

$morphFunct = function ($originalString, $case) {
    $originalString = trim($originalString);
    if (empty($originalString)) {
        return $originalString;
    }
    $res_word   = $originalString;
    $sCacheID = md5($originalString) . md5($case);

    if (!defined('NOT_WORK_MORPH')) {
        $obCache = \Bitrix\Main\Application::getInstance()->getManagedCache();

        if ($obCache->read(86400, $sCacheID)) {
            $res_word = $obCache->get($sCacheID)['morph'];
        } else {
            try {
                $morph_client = new SoapClient("http://s-1c-app07.tularegion.local/morph/ws/morpher.1cws?wsdl", ['trace' => 1, 'exception' => true]);
                $resp = $morph_client->Case(['OriginalString' => $originalString, 'Case' => $case]);
                if (!isset($resp->return)) {
                    throw new Exception("Ошибка morph");
                }
                $res_word = $resp->return;
                $obCache->set($sCacheID, array("morph" => $res_word));

            } catch (Exception $exc) {
            }
        }
    }

    return $res_word;
};

/**
 * склонение. Используется в бизнес процессах
 * @param int $num число относительно которого склоняем
 * @param string $titles массив типа ['Яблоко','Яблока','Яблок']
 */
$declOfNum = function ($num, $titles) {
    $cases = [2, 0, 1, 1, 1, 2];
    return $titles[($num % 100 > 4 && $num % 100 < 20) ? 2 : $cases[min($num % 10, 5)]];
};

/**
 * аналог lc_first.Используется в бизнес процессах
 * @param string $string строка
 */
$mb_lcfirst = function ($string, $encoding = "UTF-8") {
    $strlen = mb_strlen($string, $encoding);
    $firstChar = mb_substr($string, 0, 1, $encoding);
    $then = mb_substr($string, 1, $strlen - 1, $encoding);
    return mb_strtolower($firstChar, $encoding) . $then;
};

/**
 * аналог uc_first.Используется в бизнес процессах
 * @param string $string строка
 */
$mb_ucfirst = function ($string, $encoding = "UTF-8") {
    $strlen = mb_strlen($string, $encoding);
    $firstChar = mb_substr($string, 0, 1, $encoding);
    $then = mb_substr($string, 1, $strlen - 1, $encoding);
    return mb_strtoupper($firstChar, $encoding) . $then;
};

/**
 * Информация о пользователе. Используется в бизнес процессах
 * @param int $user_id ID битрикс
 */
$userFields = function ($user_id) use (&$morphFunct, $mb_lcfirst) {
    $arUser = CUser::GetById($user_id)->Fetch();
    if (!$arUser) {
        throw new Exception('Не найден сотрудник');
    }

    $getDepartmentName = function ($id) use (&$getDepartmentName) {
        \Bitrix\Main\Loader::includeModule('iblock');
        $arDepartments = null;
        if (!empty($id)) {
            $department = \Bitrix\Iblock\SectionTable::getRow([
                'filter'  => ['ID' => $id],
                'select'  => ['ID', 'IBLOCK_SECTION_ID', 'NAME']
            ]);
            if ($department) {
                $arDepartments = [
                    $department['ID'] => $department['NAME']
                ];
                if ($department['IBLOCK_SECTION_ID']) {
                    $deps = $getDepartmentName($department['IBLOCK_SECTION_ID']);
                    foreach ($deps as $id => $dep) {
                        $arDepartments[ $id ] = $dep;
                    }
                }
            }
        }

        return $arDepartments;
    };

    $arUser['FIRST_NAME']           = trim($arUser['NAME']);
    $arUser['LAST_NAME']            = trim($arUser['LAST_NAME']);
    $arUser['MIDDLE_NAME']          = trim($arUser['SECOND_NAME']);
    $arUser['FIO']                  = trim($arUser['LAST_NAME'] . ' ' . $arUser['NAME'] . ' ' . $arUser['SECOND_NAME']);
    $arUser['FIO_ROD']              = $arUser['FIO'];
    $arUser['FIO_DAT']              = $arUser['FIO'];
    $arUser['FIO_VIN']              = $arUser['FIO'];
    $arUser['FIO_INIC']             = '';
    $arUser['FIO_INIC_REV']         = '';
    $arUser['FIO_INIC_DAT']         = '';
    $arUser['FIO_INIC_DAT_REV']     = '';
    $arUser['OBRASHENIE']           = $arUser['PERSONAL_GENDER'] == 'M' ? 'Уважаемый' : 'Уважаемая';
    $arUser['UVEDOMLEN']            = $arUser['PERSONAL_GENDER'] == 'M' ? 'уведомлен' : 'уведомлена';
    $arUser['XML_ID']               = trim($arUser['XML_ID']);
    $arUser['UF_SID']               = trim($arUser['UF_SID']);
    $arUser['DEPARTMENT']           = '';
    $arUser['DEPARTMENTS']          = [];
    $arUser['PODRAZDELENIE']        = [];
    $arUser['WORK_POSITION_CLEAR']  = $arUser['WORK_POSITION'];

    if (!empty($arUser['UF_DEPARTMENT'])) {
        foreach ($arUser['UF_DEPARTMENT'] as $department_id) {
            $arUser['DEPARTMENTS'] = $getDepartmentName($department_id);
            if (empty($arUser['DEPARTMENTS'])) {
                continue;
            }

            if (array_key_exists(57, $arUser['DEPARTMENTS'])) {
                unset($arUser['DEPARTMENTS'][ 204 ]); // Министерство по информатизации, связи и вопросам открытого управления Тульской области
                unset($arUser['DEPARTMENTS'][ 1727 ]); // Органы исполнительной власти Тульской области
                unset($arUser['DEPARTMENTS'][ 53 ]); // Правительство Тульской области
            }
            if (array_key_exists(2331, $arUser['DEPARTMENTS'])) {
                unset($arUser['DEPARTMENTS'][ 2331 ]); // ОМСУ
                unset($arUser['DEPARTMENTS'][ 2137 ]); // Контроль поручений
                unset($arUser['DEPARTMENTS'][ 53 ]); // Правительство Тульской области
            }

            // Отдел аналитики, статистики и развития проекта «Ситуационный центр» не входит в состав УИС, но подчиняется непосредственно Прокудину В.Ю.;
            if (array_key_exists(2136, $arUser['DEPARTMENTS'])) {
                unset($arUser['DEPARTMENTS'][ 79 ]);
            }
            // Группа мониторинга информационных ресурсов не входит в состав управления сервиса и эксплуатации, но подчиняется непосредственно Даниленко В.В.;
            if (array_key_exists(72, $arUser['DEPARTMENTS'])) {
                unset($arUser['DEPARTMENTS'][ 60 ]);
            }
            // Отдел продаж не входит в состав управления по обеспечению деятельности и развитию, но подчиняется непосредственно Зенину И.В.
            if (array_key_exists(99, $arUser['DEPARTMENTS'])) {
                unset($arUser['DEPARTMENTS'][ 90 ]);
            }
            // отдел технической поддержки и сопровождения РИСЗ ТО - убрать отдел развития РИСЗ ТО.
            if (array_key_exists(299, $arUser['DEPARTMENTS'])) {
                unset($arUser['DEPARTMENTS'][ 151 ]);
            }

            $arUser['DEPARTMENT'] = array_slice($arUser['DEPARTMENTS'], -3, 1)[0];

            $i = 0;
            foreach ($arUser['DEPARTMENTS'] as $department) {
                if (!in_array($department, ['Правительство Тульской области', 'Органы исполнительной власти Тульской области'])) {
                    if ($i > 3) {
                        break;
                    }
                    if (mb_stripos($department, 'ГАУ') === false) {
                        $department = $mb_lcfirst($department);
                    }
                    $arUser['PODRAZDELENIE'][] = trim($department);
                }
                $i++;
            }

            break;
        }
    }

    if (empty($arUser['UF_LAST_1C_UPD']) || strtotime($arUser['UF_LAST_1C_UPD']) < strtotime('-3 DAYS')) {
        $arUser['UF_WORK_POSITION']     = '';
        $arUser['UF_WORK_POSITION_DAT'] = '';
        $arUser['UF_WORK_POSITION_ROD'] = '';
        $arUser['UF_WORK_POSITION_TV']  = '';
        $arUser['UF_LAST_1C_UPD']       = ConvertTimeStamp(time(), 'SHORT', 'ru');
        $user = new CUser();
        $user->Update($arUser['ID'], [
            'UF_LAST_1C_UPD' => $arUser['UF_LAST_1C_UPD']
        ]);
    }

    if (empty($arUser['UF_WORK_POSITION_DAT'])) {
        if (!empty($arUser['PODRAZDELENIE'])) {
            $WORK_POSITION = $arUser['WORK_POSITION'];
            
            $DEPARTMENT_PARTS_STR = str_replace(
                [
                    ' государств',
                    ' Министерство ',
                    ' министерство ',
                    ' департамент ',
                    ' управление ',
                    ' отдел ',
                    ' группа ',
                ],
                [
                    ' Государств',
                    ' министерства ',
                    ' министерства ',
                    ' департамента ',
                    ' управления ',
                    ' отдела ',
                    ' группы ',
                ],
                $morphFunct(' ' . implode(' ', $arUser['PODRAZDELENIE']), 'Р')
            );

            $wpString = trim($WORK_POSITION . ' ' . $DEPARTMENT_PARTS_STR);

            $wpString = str_replace(
                [
                    '  ',
                    'гУ ТО',
                    'гКУ ТО',
                    'гУЗ ТО',
                    'гУЗ "',
                    'гУКСа',
                    'гБУ ТО',
                    'секретариаты, советники аппарат ПТО',
                    'аппарат ПТО',
                    ' оМСУ контроль поручений',
                    ' ГАУ ТОГО ',
                    ' уРМа в ',
                    ' гУЗ  "',
                    ' отдела отдела ',
                    ' отдела отдел',
                    ' группы группы ',
                    ' главного управления главного управления',
                    ' администрация МО ',
                    ' администрации администрации',
                ],
                [
                    ' ',
                    'ГУ ТО',
                    'ГКУ ТО',
                    'ГУЗ ТО',
                    'ГУЗ "',
                    'ГУКСа',
                    'ГБУ ТО',
                    'в аппарате правительства Тульской области',
                    'аппарата правительства Тульской области',
                    '',
                    ' ГАУ ТО ',
                    ' УРМа в ',
                    ' ГУЗ  "',
                    ' отдела ',
                    ' отдела',
                    ' группы ',
                    ' главного управления',
                    ' администрации МО ',
                    ' администрации',
                ],
                $wpString
            );

            $arUser['UF_WORK_POSITION']     = $wpString;
            $arUser['UF_WORK_POSITION_ROD'] = $morphFunct($wpString, 'Р');
            $arUser['UF_WORK_POSITION_DAT'] = $morphFunct($wpString, 'Д');
            $arUser['UF_WORK_POSITION_TV']  = $morphFunct($wpString, 'Т');
        } else {
            $arUser['UF_WORK_POSITION']     = $arUser['WORK_POSITION'];
            $arUser['UF_WORK_POSITION_ROD'] = $morphFunct($arUser['WORK_POSITION'], 'Р');
            $arUser['UF_WORK_POSITION_DAT'] = $morphFunct($arUser['WORK_POSITION'], 'Д');
            $arUser['UF_WORK_POSITION_TV']  = $morphFunct($arUser['WORK_POSITION'], 'Т');
        }

        $user = new CUser();
        $user->Update($arUser['ID'], [
            'UF_WORK_POSITION'      => $arUser['UF_WORK_POSITION'],
            'UF_WORK_POSITION_ROD'  => $arUser['UF_WORK_POSITION_ROD'],
            'UF_WORK_POSITION_DAT'  => $arUser['UF_WORK_POSITION_DAT'],
            'UF_WORK_POSITION_TV'   => $arUser['UF_WORK_POSITION_TV']
        ]);
    }

    $arUser['DOLJNOST_CLEAR']   = trim($arUser['WORK_POSITION']);
    $arUser['WORK_POSITION']    = $arUser['UF_WORK_POSITION'];
    $arUser['DOLJNOST']         = $arUser['UF_WORK_POSITION'];
    $arUser['DOLJNOST_DAT']     = $arUser['UF_WORK_POSITION_DAT'];
    $arUser['DOLJNOST_ROD']     = $arUser['UF_WORK_POSITION_ROD'];
    $arUser['DOLJNOST_VIN']     = $morphFunct($arUser['UF_WORK_POSITION'], 'В');

    if ($arUser['FIRST_NAME']) {
        $arUser['FIO_INIC'] .= mb_substr($arUser['FIRST_NAME'], 0, 1) . '.';
    }
    if ($arUser['MIDDLE_NAME']) {
        $arUser['FIO_INIC'] .= mb_substr($arUser['MIDDLE_NAME'], 0, 1) . '.';
    }
    if ($arUser['LAST_NAME']) {
        $arUser['FIO_INIC'] .= ' ' . $arUser['LAST_NAME'];
    }

    if ($arUser['LAST_NAME']) {
        $arUser['FIO_INIC_REV'] .= $arUser['LAST_NAME'] . ' ';
    }
    if ($arUser['FIRST_NAME']) {
        $arUser['FIO_INIC_REV'] .= mb_substr($arUser['FIRST_NAME'], 0, 1) . '.';
    }
    if ($arUser['MIDDLE_NAME']) {
        $arUser['FIO_INIC_REV'] .= mb_substr($arUser['MIDDLE_NAME'], 0, 1) . '.';
    }

    if ($arUser['FIO']) {
        $arUser['FIO_ROD'] = $morphFunct($arUser['FIO_ROD'], 'Р');
        $arUser['FIO_DAT'] = $morphFunct($arUser['FIO_DAT'], 'Д');
        $arUser['FIO_VIN'] = $morphFunct($arUser['FIO'], 'В');
    }

    if ($arUser['FIO_INIC']) {
        $arUser['FIO_INIC_DAT']     = $morphFunct($arUser['FIO_INIC'], 'Д');
        $arUser['FIO_INIC_DAT_REV'] = $morphFunct($arUser['FIO_INIC_REV'], 'Д');
    }
    
    return $arUser;
};

$reestGen = function ($bp_item) {
    \Bitrix\Main\Loader::includeModule("iblock");

    $BP_IBLOCK      = 526;
    $BP_LINK_IBLOCK = 525;
    $TABLE_T1       = "";
    $TABLE_T2       = "";
    $ZAYAVKI        = [];
    $zayavki_item   = [];
    $NAZVANIE_OIV   = "";
    $RUKOVODITEL_OIV = [];

    $db_props = CIBlockElement::GetProperty($BP_IBLOCK, $bp_item, [], ['CODE'=>"ZAYAVKI"]);
    while ($ar_props = $db_props->Fetch()) {
        if (empty($ar_props['VALUE'])) {
            continue;
        }
        $ZAYAVKI[$ar_props['VALUE']] = [];
    }
    $db_props = CIBlockElement::GetProperty($BP_IBLOCK, $bp_item, [], ['CODE'=>"NAZVANIE_OIV"]);
    if ($ar_props = $db_props->Fetch()) {
        $NAZVANIE_OIV = $ar_props['VALUE'];
    }
    $db_props = CIBlockElement::GetProperty($BP_IBLOCK, $bp_item, [], ['CODE'=>"RUKOVODITEL_OIV"]);
    if ($ar_props = $db_props->Fetch()) {
        $RUKOVODITEL_OIV = $GLOBALS['userFields']($ar_props['VALUE']);
    }
    if (empty($ZAYAVKI)) {
        throw new Exception("Заявки не найдены");
    }
    
    $res = CIBlockElement::GetList(
        [],
        [
            'IBLOCK_ID' => $BP_LINK_IBLOCK,
            'ID'        => array_keys($ZAYAVKI)
        ],
        false,
        false,
        [
            'ID',
            'CREATED_BY',
            'DATE_CREATE',
            'PROPERTY_FIO',
            'PROPERTY_ORGAN',
            'PROPERTY_DOLZHNOST',
            'PROPERTY_FAYL_ZAYAVKI',
            'PROPERTY_TIP_DOLZHNOSTI',
            'PROPERTY_PROSHU_PREDOSTAVIT',
            'PROPERTY_VYPLATA_PROIZVODILAS',
            'PROPERTY_OBRABOTANA',
            'PROPERTY_DATA_PRIKAZA',
            'PROPERTY_NOMER_PRIKAZA',
            'PROPERTY_DATA_NACHALA_OTPUSKA',
            'PROPERTY_YEAR',
        ]
    );
                                    
    while ($ob = $res->fetch()) {
        $ZAYAVKI[ $ob['ID'] ] = $ob;
    }

    $TABLE_T1 = '<table style="width:500px; border-collapse: collapse;" >
                    <thead>
                        <tr>
                            <td style="width:25%; border:1px solid #000; text-align:center;">Фамилия И.О.</td>
                            <td style="width:25%; border:1px solid #000; text-align:center;">Должность</td>
                            <td style="width:25%; border:1px solid #000; text-align:center;">Дата и номер приказа на отпуск</td>
                            <td style="width:25%; border:1px solid #000; text-align:center;">Дата начала отпуска</td>
                        </tr>
                    </thead>
                    <tbody>
                    ';
    $TABLE_T2 = '<table style="width:500px; border-collapse: collapse;">
                    <thead>
                        <tr>
                            <td style="width:50%; border:1px solid #000; text-align:center;">Фамилия И.О.</td>
                            <td style="width:50%; border:1px solid #000; text-align:center;">Должность</td>
                        </tr>
                    </thead>
                    <tbody>
                    ';
    foreach ($ZAYAVKI as $zayavka_id => $zayavka) {
        if (empty($zayavka)) {
            throw new Exception("Заявка не найдена ".$zayavka_id);
        }
        $arSotrudnik = $GLOBALS['userFields']($zayavka['CREATED_BY']);
        $TABLE_T1 .= '  <tr>
                            <td style="width:25%; border:1px solid #000; vertical-align:top;">'.$arSotrudnik['FIO'].'</td>
                            <td style="width:25%; border:1px solid #000; vertical-align:top;">'.$arSotrudnik['DOLJNOST'].'</td>
                            <td style="width:25%; border:1px solid #000; vertical-align:top;"> №'.$zayavka['PROPERTY_NOMER_PRIKAZA_VALUE'].' от '.$zayavka['PROPERTY_DATA_PRIKAZA_VALUE'].'</td>
                            <td style="width:25%; border:1px solid #000; vertical-align:top;">'.$zayavka['PROPERTY_DATA_NACHALA_OTPUSKA_VALUE'].'</td>
                        </tr>';
        $TABLE_T2 .= '  <tr>
                            <td style="width:50%; border:1px solid #000; vertical-align:top;">'.$arSotrudnik['FIO'].'</td>
                            <td style="width:50%; border:1px solid #000; vertical-align:top;">'.$arSotrudnik['DOLJNOST'].'</td>
                        </tr>';
        $zayavki_item = $zayavka;
    }
    $TABLE_T1 .= '  </tbody>
                </table>
    ';
    $TABLE_T2 .= '  </tbody>
                </table>
    ';

    $year = date('Y', strtotime($zayavki_item['DATE_CREATE']));
    if (!empty($zayavki_item['PROPERTY_YEAR_VALUE']) && in_array((int)$zayavki_item['PROPERTY_YEAR_VALUE'], [2020, 2021])) {
        $year = (int)$zayavki_item['PROPERTY_YEAR_VALUE'];
    }

    $PROPS = [
        '#ORGAN#'                   => $NAZVANIE_OIV,
        '#TABLE_T1#'                => $TABLE_T1,
        '#TABLE_T2#'                => $TABLE_T2,
        '#YEAR#'                    => $year,
        '#RUKOVODITEL_FIO_INIC#'    => $RUKOVODITEL_OIV['FIO_INIC'],
        '#RUKOVODITEL_DOLJNOST#'    => $RUKOVODITEL_OIV['DOLJNOST'],
    ];

    $doc_content = str_replace(
        array_keys($PROPS),
        array_values($PROPS),
        file_get_contents(__DIR__.'/bizproz/zayavka-na-mp/reestr_'.$zayavki_item['PROPERTY_TIP_DOLZHNOSTI_ENUM_ID'].'_'.$zayavki_item['PROPERTY_PROSHU_PREDOSTAVIT_ENUM_ID'].'.html')
    );
    
    if (!$GLOBALS['setElementPDFValue']($bp_item, 'REESTR', $doc_content, "Реестр ".$NAZVANIE_OIV." (".$zayavki_item['PROPERTY_PROSHU_PREDOSTAVIT_VALUE'].")")) {
        return ("Ошибка создания pdf");
    }

    return true;
};

$bp_task_updated = function ($task_id, $STATUS, $OTVETSTVENNYE_ID) {
    \Bitrix\Main\Loader::includeModule("bizproc");
    \Bitrix\Main\Loader::includeModule("im");
    $otvetstvennye      = [];
    $arStartedBy        = [];
    $ar_task            = $GLOBALS['DB']->Query('SELECT ID, NAME, DESCRIPTION, WORKFLOW_ID FROM b_bp_task WHERE ID='.((int)$task_id))->fetch();

    $TASK_OTVETSTVENNY  = [];
    $NOTIFY_TAG         = "BIZPROCMESSAGE|".$task_id;
    $connection         = \Bitrix\Main\Application::getConnection();
    $sqlHelper          = $connection->getSqlHelper();

    if (empty($ar_task)) {
        return;
    }

    CIMNotify::DeleteByTag($NOTIFY_TAG);

    $b_bp_workflow_state = $GLOBALS['DB']->Query('SELECT
        b_bp_workflow_state.STARTED_BY,
        b_bp_workflow_state.STATE_TITLE,
        b_bp_workflow_template.NAME,
        b_bp_workflow_template.DOCUMENT_TYPE
    FROM
        b_bp_workflow_state 
        LEFT JOIN
            b_bp_workflow_template
        ON
            b_bp_workflow_template.ID = b_bp_workflow_state.WORKFLOW_TEMPLATE_ID
    WHERE
        b_bp_workflow_state.ID="'.$GLOBALS['DB']->ForSql($ar_task['WORKFLOW_ID']).'"')->Fetch();

    $bSendMail = true;
    if (0 === mb_strpos($b_bp_workflow_state['DOCUMENT_TYPE'], 'iblock_')) {
        $iblockId = (int)str_replace('iblock_', '', $b_bp_workflow_state['DOCUMENT_TYPE']);

        /*
         * Не отправлять уведомления
         * @task 57537
         */
        if (in_array($iblockId, [600])) {
            $bSendMail = false;
        }
    }

    if (empty($b_bp_workflow_state['STARTED_BY'])) {
        return;
    }
    
    $arStartedBy = CUser::GetById($b_bp_workflow_state['STARTED_BY'])->fetch();
    if (!$arStartedBy) {
        return;
    }

    if ($OTVETSTVENNYE_ID) {
        $res = $GLOBALS['DB']->Query('SELECT ID, EMAIL, LAST_NAME, NAME, SECOND_NAME FROM b_user WHERE ID IN('.implode(",", $OTVETSTVENNYE_ID).')');
        while ($ob = $res->Fetch()) {
            $otvetstvennye[$ob['ID']] = $ob;
            $TASK_OTVETSTVENNY[$ob['ID']] = $ob['LAST_NAME'].' '.$ob['NAME'].' '.$ob['SECOND_NAME'].' ('.$ob['EMAIL'].')';
        }
    }

    if ($STATUS == "Поставлена") {
        if (!isset($otvetstvennye[$arStartedBy['ID']])) {
            if ($bSendMail) {
                \Bitrix\Main\Mail\Event::send([
                    'EVENT_NAME'=> "BP_CHANGES",
                    'LID'       => "s1",
                    'C_FIELDS'  => [
                        'TASK_ID'           => $task_id,
                        'EMAIL'             => $arStartedBy['EMAIL'],
                        'BP_NAME'           => $b_bp_workflow_state['NAME'],
                        'BP_STATE'          => $b_bp_workflow_state['STATE_TITLE'],
                        'TASK_NAME'         => $ar_task['NAME'],
                        'TASK_STATE'        => $STATUS,
                        'TASK_OTVETSTVENNY' => implode(", ", $TASK_OTVETSTVENNY),
                    ]
                ]);
            }
            
            CIMMessenger::Add([
                'NOTIFY_TAG'        => $NOTIFY_TAG,
                "MESSAGE_TYPE"      => "S",
                "TO_USER_ID"        => $arStartedBy['ID'],
                "FROM_USER_ID"      => 1,
                "MESSAGE"           => 'По Вашему бизнес процессу "'.$b_bp_workflow_state['NAME'].'" добавлена новая задача "'.$ar_task['NAME'].'"',
                'MESSAGE_OUT'       => '#SKIP#',
                "AUTHOR_ID"         => 1,
                "EMAIL_TEMPLATE"    => "some",
                "NOTIFY_TYPE"       => IM_NOTIFY_SYSTEM,
                "NOTIFY_MODULE"     => "main",
                "NOTIFY_BUTTONS" => []
            ]);
        }
        if ($otvetstvennye) {
            if (mb_substr($ar_task['NAME'], 0, 3) != "BP:" && $bSendMail) {
                \Bitrix\Main\Mail\Event::send([
                    'EVENT_NAME'=> "BP_TASK_ADDED",
                    'LID'       => "s1",
                    'C_FIELDS'  => [
                        'TASK_ID'   => $task_id,
                        'BP_NAME'   => $b_bp_workflow_state['NAME'],
                        'TASK_NAME' => $ar_task['NAME'],
                        'EMAIL'     => array_reduce($otvetstvennye, function ($carry, $item) {
                            return ($carry?$carry.",":"").$item['EMAIL'];
                        }, null),
                    ]
                ]);
            }

            if (mb_substr($ar_task['NAME'], 0, 3) != "BP:") {
                foreach ($otvetstvennye as $otvetstvennyy) {
                    CIMMessenger::Add([
                        'NOTIFY_TAG'        => $NOTIFY_TAG,
                        "MESSAGE_TYPE"      => "S",
                        "TO_USER_ID"        => $otvetstvennyy['ID'],
                        "FROM_USER_ID"      => 1,
                        "MESSAGE"           => 'Новая задача "'.$ar_task['NAME'].'" бизнес-процесса "'.$b_bp_workflow_state['NAME'].'"',
                        'MESSAGE_OUT'       => '#SKIP#',
                        "AUTHOR_ID"         => 1,
                        "EMAIL_TEMPLATE"    => "some",
                        "NOTIFY_TYPE"       => IM_NOTIFY_CONFIRM,
                        "NOTIFY_MODULE"     => "main",
                        "NOTIFY_BUTTONS" => [
                            [
                                'TITLE' => 'Приступить',
                                'VALUE' => 'Y',
                                'TYPE'  => 'accept' ,
                                'URL'   => (SITE_DIR?:'/').'company/personal/bizproc/'.$task_id.'/?back_url='.urlencode((SITE_DIR?:'/').'company/personal/bizproc/')
                            ]
                        ]
                    ]);
                }
            }
        }

        $ar_task['DESCRIPTION'] = str_replace('bizproc_show_file.php?', 'bizproc_show_file.php?cctok='.urlencode(randString()).'&', $ar_task['DESCRIPTION']);
        $connection->queryExecute('UPDATE b_bp_task SET DESCRIPTION="'.$sqlHelper->forSql($ar_task['DESCRIPTION']).'" WHERE ID='.$sqlHelper->forSql($task_id));
        if (mb_substr($ar_task['NAME'], 0, 3) == "BP:" && $bSendMail) {
            $ar_task['NAME']    = str_replace("BP:", "BP:", $ar_task['NAME']);
            $ar_task['FILES']   = [];
            $textParser         = new CTextParser();

            if (preg_match_all("/(?<urls>\[url=.*?(?=\/url)\/url\])/iu", $ar_task['DESCRIPTION'], $matches)) {
                foreach ($matches['urls'] as $descr_url) {
                    if (mb_strpos($descr_url, "bizproc_show_file") == false) {
                        continue;
                    }
                    if (!preg_match("/i=(?<fileid>\d+)/", $descr_url, $matches2)) {
                        continue;
                    }
                    if (empty($matches2['fileid'])) {
                        continue;
                    }
                    $ar_task['FILES'][] = $matches2['fileid'];
                    $ar_task['DESCRIPTION'] = str_replace($descr_url, "", $ar_task['DESCRIPTION']);
                }
            }
            
            $textParser->serverName = "https://corp.tularegion.ru";
            $ar_task['DESCRIPTION'] = $textParser->convertText($ar_task['DESCRIPTION']);
            $ar_task['DESCRIPTION'] = str_replace(
                [
                    'href="/',
                    'src="/'
                ],
                [
                    'href="https://corp.tularegion.ru/',
                    'src="https://corp.tularegion.ru/'
                ],
                $ar_task['DESCRIPTION']
            );
                
            foreach ($otvetstvennye as $otvetstvennyy) {
                $t_token = urlencode($GLOBALS['bp_enccc']($task_id."_".$otvetstvennyy['ID']));
                $t_controls = '<p>Если Вы находитесь внутри сети ПТО (на рабочем месте):<br/>
                    <a href="https://corp.tularegion.local/bp_approve_task.php?token='.$t_token.'&approve">Согласовано</a>
                    | 
                    <a href="https://corp.tularegion.local/bp_approve_task.php?token='.$t_token.'&nonapprove">Отклонено</a>
                </p>';
                $t_controls .= '<hr /><p>При удаленной работе:<br/>
                    <a href="https://corp.tularegion.ru/bp_approve_task.php?token='.$t_token.'&approve">Согласовано</a>
                    | 
                    <a href="https://corp.tularegion.ru/bp_approve_task.php?token='.$t_token.'&nonapprove">Отклонено</a>
                </p>';
                $TOKEN_AUTH = $GLOBALS['auth_token_get']($otvetstvennyy['ID']);
                \Bitrix\Main\Mail\Event::send([
                    'EVENT_NAME'=> "BP_APPROVE_TASK",
                    'LID'       => "s1",
                    'FILE'      => $ar_task['FILES'],
                    'C_FIELDS'  => [
                        'EMAIL_TO'      => $otvetstvennyy['EMAIL'],
                        'TASK_NAME'     => $ar_task['NAME'],
                        'DESCRIPTION'   => $ar_task['DESCRIPTION'].(empty($TOKEN_AUTH)?"":'<br/><br/><div><a href="https://corp.tularegion.local/company/personal/bizproc/'. (int)$task_id .'/?auth_token='.urlencode($TOKEN_AUTH).'">Перейти к задаче</a></div><br/>'),
                        'CONTROLS'      => $t_controls
                    ]
                ]);
                unset($t_token, $t_controls);
            }
        }
    } elseif ($STATUS == "Закрыта") {
        if (!isset($otvetstvennye[$arStartedBy['ID']])) {
            if ($bSendMail) {
                \Bitrix\Main\Mail\Event::send([
                    'EVENT_NAME'=> "BP_CHANGES",
                    'LID'       => "s1",
                    'C_FIELDS'  => [
                        'TASK_ID'           => $task_id,
                        'EMAIL'             => $arStartedBy['EMAIL'],
                        'BP_NAME'           => $b_bp_workflow_state['NAME'],
                        'BP_STATE'          => $b_bp_workflow_state['STATE_TITLE'],
                        'TASK_NAME'         => $ar_task['NAME'],
                        'TASK_STATE'        => $STATUS,
                        'TASK_OTVETSTVENNY' => implode(", ", $TASK_OTVETSTVENNY),
                    ]
                ]);
            }

            CIMMessenger::Add([
                'NOTIFY_TAG'        => $NOTIFY_TAG,
                "MESSAGE_TYPE"      => "S",
                "TO_USER_ID"        => $arStartedBy['ID'],
                "FROM_USER_ID"      => 1,
                "MESSAGE"           => 'Задача "'.$ar_task['NAME'].'" бизнес-процесса "'.$b_bp_workflow_state['NAME'].'" закрыта',
                'MESSAGE_OUT'       => '#SKIP#',
                "AUTHOR_ID"         => 1,
                "EMAIL_TEMPLATE"    => "some",
                "NOTIFY_TYPE"       => IM_NOTIFY_SYSTEM,
                "NOTIFY_MODULE"     => "main",
                "NOTIFY_BUTTONS" => []
            ]);
        }
    }
};

$bpAdd2archive = function ($activity, $storeDTS = "5 years") {
    \Bitrix\Main\Loader::includeModule("iblock");
    \Bitrix\Main\Loader::includeModule("bizproc");

    $rootActivity   = $activity->GetRootActivity();
    $docFields      = ($activity->workflow->GetService("DocumentService"))->GetDocument($rootActivity->GetDocumentId());
    $FIELDS_FILES   = [];

    if (empty($docFields)) {
        return;
    }
    
    $arch_dir = $_SERVER['DOCUMENT_ROOT']."/../newcorp_arch/".$docFields['IBLOCK_ID']."/".$docFields['CREATED_BY']."/".date('Y-m-d')."/".$docFields['ID']."/".rand();
    mkdir($arch_dir, 0775, true);
    if (!file_exists($arch_dir)) {
        return;
    }

    $res = CIBlockElement::GetProperty($docFields['IBLOCK_ID'], $docFields['ID'], ['sort'=>"asc"], ['PROPERTY_TYPE'=>"F", 'EMPTY'=>"N"]);
    while ($ob = $res->Fetch()) {
        $FIELDS_FILES[$ob['CODE']][] = CFile::GetFileArray($ob['VALUE']);
    }

    $wft = CAllBPWorkflowTemplateLoader::GetList([], ['ID' => $rootActivity->GetWorkflowTemplateId()], false, false, ['ID', 'VARIABLES'])->Fetch();
    if (empty($wft)) {
        return;
    }

    if (!empty($wft['VARIABLES'])) {
        foreach ($wft['VARIABLES'] as $wft_var_code => $wft_var) {
            $activity_var = $activity->GetVariable($wft_var_code);
            if (!$activity_var) {
                continue;
            }
            if (!is_array($activity_var)) {
                $activity_var = [$activity_var];
            }

            if ($wft_var['Type'] == "file") {
                foreach ($activity_var as $activity_var_val) {
                    $FIELDS_FILES[$wft_var_code][] = CFile::GetFileArray($activity_var_val);
                }
            } else {
                $docFields['VAR_'.$wft_var_code] = $activity_var;
            }
        }
    }

    file_put_contents($arch_dir."/docFields.txt", var_export($docFields, true), FILE_APPEND);

    foreach ($FIELDS_FILES as $files_code => $files) {
        foreach ($files as $file_indx => $file) {
            copy($_SERVER['DOCUMENT_ROOT'].$file['SRC'], $arch_dir."/".$files_code."_".$file_indx."_".$file['ORIGINAL_NAME']);
        }
    }
};

$bp478uvedomlenieGen = function ($DOCUMENT_ID) {
    \Bitrix\Main\Loader::includeModule("iblock");
    
    global $declOfNum, $userFields;
    $BP_IBLOCK = 478;
    $DATE = new DateTime();
    $arSotrudnik = null;
    $arOtvetsven = null;
    $arRukovoditel = null;
    $OTPUSK__FROM = null;
    $OTPUSK__DAYS = null;
    $ZAPUSHCHENO_SAMOSTOYATELNO = false;
    $PRICHINA_V_SVYAZI_S = null;
    
    $db_props = CIBlockElement::GetProperty($BP_IBLOCK, $DOCUMENT_ID, [], ['CODE'=>"SOTRUDNIK"]);
    if ($ar_props = $db_props->Fetch()) {
        $arSotrudnik = $userFields($ar_props['VALUE']);
    } else {
        return "Не указан сотрудник";
    }

    $db_props = CIBlockElement::GetProperty($BP_IBLOCK, $DOCUMENT_ID, [], ['CODE'=>"OTVETSTVENNY_OIV"]);
    if ($ar_props = $db_props->Fetch()) {
        $arOtvetsven = $userFields($ar_props['VALUE']);
    } else {
        return "Не указан ответственный";
    }

    $db_props = CIBlockElement::GetProperty($BP_IBLOCK, $DOCUMENT_ID, [], ['CODE'=>"RUKOVODITEL_OIV"]);
    if ($ar_props = $db_props->Fetch()) {
        $arRukovoditel = $userFields($ar_props['VALUE']);
    } else {
        return "Не указан руководитель";
    }

    $db_props = CIBlockElement::GetProperty($BP_IBLOCK, $DOCUMENT_ID, [], ['CODE'=>"OTPUSK__FROM"]);
    if ($ar_props = $db_props->Fetch()) {
        $OTPUSK__FROM = new DateTime($ar_props['VALUE']);
    } else {
        return "Не указана дата начала";
    }

    $db_props = CIBlockElement::GetProperty($BP_IBLOCK, $DOCUMENT_ID, [], ['CODE'=>"OTPUSK__DAYS"]);
    if ($ar_props = $db_props->Fetch()) {
        $OTPUSK__DAYS = $ar_props['VALUE'];
    } else {
        return "Не указано количество дней";
    }

    $db_props = CIBlockElement::GetProperty($BP_IBLOCK, $DOCUMENT_ID, [], ['CODE'=>"ZAPUSHCHENO_SAMOSTOYATELNO"]);
    if ($ar_props = $db_props->Fetch()) {
        $ZAPUSHCHENO_SAMOSTOYATELNO = mb_strtolower($ar_props['VALUE_ENUM']) == "да";
    }

    $db_props = CIBlockElement::GetProperty($BP_IBLOCK, $DOCUMENT_ID, [], ['CODE'=>"PRICHINA_V_SVYAZI_S"]);
    if ($ar_props = $db_props->Fetch()) {
        $PRICHINA_V_SVYAZI_S = $ar_props['VALUE'];
    }

    $doc_content = str_replace(
        [
            '#DATE#',
            '#SOTRUDNIK__DOLJNOST_DAT#',
            '#SOTRUDNIK__DOLJNOST_ROD#',
            '#SOTRUDNIK__FIO_ROD#',
            '#SOTRUDNIK__FIO_INIC_DAT#',
            '#SOTRUDNIK__FIO_INIC_DAT_REV#',
            '#SOTRUDNIK__OBRASHENIE#',
            '#SOTRUDNIK__UVEDOMLEN#',
            '#SOTRUDNIK__FIRST_NAME#',
            '#SOTRUDNIK__MIDDLE_NAME#',
            '#SOTRUDNIK__FIO#',
            '#OTPUSK__FROM#',
            '#OTPUSK__DAYS#',
            '#OTVETSTVENNY_OIV__DOLJNOST#',
            '#OTVETSTVENNY_OIV__FIO_INIC#',
            '#RUKOVODITEL_DOLJNOST#',
            '#RUKOVODITEL_FIO_INICT#',
            '#PRICHINA_V_SVYAZI_S#',
        ],
        [
            $DATE->format('d.m.Y'),
            $GLOBALS['mb_ucfirst']($arSotrudnik['DOLJNOST_DAT']),
            $GLOBALS['mb_lcfirst']($arSotrudnik['DOLJNOST_ROD']),
            $arSotrudnik['FIO_ROD'],
            $arSotrudnik['FIO_INIC_DAT'],
            $arSotrudnik['FIO_INIC_DAT_REV'],
            $arSotrudnik['OBRASHENIE'],
            $arSotrudnik['UVEDOMLEN'],
            $arSotrudnik['FIRST_NAME'],
            $arSotrudnik['MIDDLE_NAME'],
            $arSotrudnik['FIO'],
            $OTPUSK__FROM->format('d.m.Y'),
            $OTPUSK__DAYS  . " " . $declOfNum($OTPUSK__DAYS, ['календарный день', 'календарных дня', 'календарных дней']),
            $arOtvetsven['DOLJNOST'],
            $arOtvetsven['FIO_INIC'],
            $GLOBALS['mb_ucfirst']($arRukovoditel['UF_WORK_POSITION']),
            $arRukovoditel['FIO_INIC'],
            $PRICHINA_V_SVYAZI_S
        ],
        file_get_contents(
            $ZAPUSHCHENO_SAMOSTOYATELNO
                ? $_SERVER['DOCUMENT_ROOT'].'/local/php_interface/bizproz/uved_ob_otpuske/uvedomlenie_self.html'
                : $_SERVER['DOCUMENT_ROOT'].'/local/php_interface/bizproz/uved_ob_otpuske/uvedomlenie.html'
        )
    );

    $FILE_NAME = $ZAPUSHCHENO_SAMOSTOYATELNO ? "Заявление на отпуск ".$arSotrudnik['FIO_INIC_DAT'] : "Уведомление об отпуске ".$arSotrudnik['FIO_INIC_DAT'];

    if (!$GLOBALS['setElementPDFValue']($DOCUMENT_ID, 'UVED_FILE', $doc_content, $FILE_NAME)) {
        return ("Ошибка создания pdf");
    }
    return true;
};

$bp_deccc = function ($text) {
    $result = null;
    try {
        $text = base64_decode($text);
        $text = mb_substr($text, 5);
        if (mb_strlen($text) < 3) {
            throw new Exception("err");
        }

        $text = mb_substr($text, 0, mb_strlen($text)-3);
        $text = unserialize(base64_decode($text));
        if (empty($text) || count($text) !== 3) {
            throw new Exception("err");
        }
        $tag = base64_decode($text[0]);
        $iv = base64_decode($text[1]);
        $ciphertext = base64_decode($text[2]);
        $text = openssl_decrypt($ciphertext, BP_CIPHER, base64_decode(BP_KEY), 0, $iv, $tag);
        $text = base64_decode($text);

        $text = mb_substr($text, 8);
        $text = mb_substr($text, 0, mb_strlen($text)-5);

        $result = trim($text);
    } catch (Exception $exc) {
    }
    return $result;
};

$bp_enccc = function ($text) {
     $text = base64_encode(randString(8).$text.randString(5));
     $ivlen = openssl_cipher_iv_length(BP_CIPHER);
     $iv = openssl_random_pseudo_bytes($ivlen);
     $text = openssl_encrypt($text, BP_CIPHER, base64_decode(BP_KEY), 0, $iv, $tag);
     $text = base64_encode(serialize([base64_encode($tag), base64_encode($iv), base64_encode($text)]));
     $text = base64_encode(randString(5).$text.randString(3));
     return $text;
};

$cEscapeshellarg = function ($text) {
    setlocale(LC_CTYPE, "ru_RU.UTF-8");
    return escapeshellarg($text);
};

$setElementPDFValue = function ($ELEMENT_ID, $PROP_CODE, $FILE_CONTENT, $FILE_NAME, &$return = null, &$getIDFile = null) {
    try {
        \Bitrix\Main\Loader::includeModule('iblock');
        \Bitrix\Main\Loader::includeModule('citto.filesigner');
        include __DIR__."/set_property_values_ex.php";

        $pdfile1 = new \Citto\Filesigner\PDFile();
        $pdfile1->setName($FILE_NAME);
        $pdfile1->insert($FILE_CONTENT);
        $pdfile1->save();

        CIBlockElement::SetPropertyValuesEx($ELEMENT_ID, false, [$PROP_CODE => ['VALUE' => ['del' => "Y"]]]);
        $SetPropertyValuesEx($ELEMENT_ID, false, [
            $PROP_CODE => [
                'VALUE' => CFile::MakeFileArray($pdfile1->getId()) + ['ID' => $pdfile1->getId()]
            ]
        ]);

        $getIDFile = $pdfile1->getId();
    } catch (Exception $exc) {
        $return = $exc->getMessage();
        return false;
    }

    return true;
};

$appendData2File = function ($FILE_ID, $DATA, &$return = null, $pattern = null) {
    try {
        \Bitrix\Main\Loader::includeModule('citto.filesigner');
        $source_file = CFile::GetFileArray($FILE_ID);
        if (empty($source_file)) {
            throw new Exception("Файл не найден");
        }
        if ($source_file['CONTENT_TYPE'] == "application/pdf") {
            $pdfile1 = new \Citto\Filesigner\PDFile($source_file['ID']);
            $pdfile1->insert($DATA, $pattern);
            $pdfile1->save();
        } else {
            throw new Exception("Файл не поддерживает вставку");
        }
    } catch (Exception $exc) {
        $return = $exc->getMessage();
        return false;
    }
    return true;
};

$auth_token_get = function ($USER_ID) {
    if (in_array(1, CUser::GetUserGroup($USER_ID))) {
        return null;
    }
    
    $user_f = \Bitrix\Main\UserTable::getRow([
        'select' => ['ID', 'DATE_REGISTER'],
        'filter' => ['ID' => $USER_ID]
    ]);
    if (empty($user_f) || empty($user_f['DATE_REGISTER'])) {
        return null;
    }
    
    
    $auth_token = $GLOBALS['bp_enccc'](serialize([
            (int)$user_f['DATE_REGISTER']->format('U'),
            randString(9),
            (int)$USER_ID,
            time()
        ]));
    \Bitrix\Main\Application::getConnection()->queryExecute('INSERT INTO c_auth_token (token, created) VALUES ("'. \Bitrix\Main\Application::getConnection()->getSqlHelper()->forSql(md5($auth_token)) .'", NOW())');
    return $auth_token;
};

$auth_token_check = function ($auth_token, &$user_id) {
    try {
        $dec_data = $GLOBALS['bp_deccc']($auth_token);
        if (empty($dec_data)) {
            throw new Exception("Cant decode");
        }

        $dec_data = unserialize($dec_data);
        if (empty($dec_data)) {
            throw new Exception("Cant unserialize");
        }

        if (
            !is_array($dec_data)
            || count($dec_data) != 4
            || !is_int($dec_data[0])
            || !is_string($dec_data[1])
            || empty($dec_data[2])
            || !is_int($dec_data[2])
            || !is_int($dec_data[3])
        ) {
            throw new Exception("Wrong format ".var_export($dec_data, true));
        }
        
        if (strtotime("-3 days") > $dec_data[3]) {
            throw new Exception("Token expired ".print_r($dec_data, true));
        }
        
        $user_f = \Bitrix\Main\UserTable::getRow([
            'select' => ['ID', 'DATE_REGISTER'],
            'filter' => ['ID' => $dec_data[2]]
        ]);
        if (empty($user_f) || empty($user_f['DATE_REGISTER'])) {
            throw new Exception("User not found ".print_r($dec_data, true));
        }

        // if (!in_array(110, CUser::GetUserGroup($dec_data[2]))) throw new Exception("User not in group ".print_r($dec_data, true));
        if (in_array(1, CUser::GetUserGroup($dec_data[2]))) {
            throw new Exception("User wrong group ".print_r($dec_data, true));
        }

        if ($dec_data[0] !== (int)$user_f['DATE_REGISTER']->format('U')) {
            throw new Exception("DATE_REGISTER not match  ".print_r($dec_data, true));
        }

        $auth_token_md5 = \Bitrix\Main\Application::getConnection()->getSqlHelper()->forSql(md5($auth_token));
        if (
            empty(\Bitrix\Main\Application::getConnection()->queryScalar(
                'SELECT ID FROM c_auth_token WHERE used=0 AND created > SUBDATE(NOW(),3) AND token="'.$auth_token_md5.'"'
            ))
        ) {
            throw new Exception("c_auth_token not found or used  ".print_r($dec_data, true));
        }
        
        \Bitrix\Main\Application::getConnection()->queryExecute('UPDATE c_auth_token SET used=1 WHERE token="'.$auth_token_md5.'"');
    
        $user_id = $dec_data[2];
        return true;
    } catch (Exception $exc) {
        file_put_contents($_SERVER['DOCUMENT_ROOT']."/../newcorp_arch/auth_token_check.log", date('d.m.Y H:i ').$auth_token.PHP_EOL.$exc->getMessage().PHP_EOL.PHP_EOL, FILE_APPEND);
    }
    return false;
};

$getDepOtvpoKadram = function ($depId) {
    \Bitrix\Main\Loader::includeModule('iblock');
    $deps           = [];
    $depsIblockId   = 5;

    $res = CIBlockSection::GetNavChain($depsIblockId, $depId, ['ID', 'IBLOCK_ID']);
    while ($ob = $res->getNext()) {
        $deps[$ob['ID']] = null;
    }

    if ($deps) {
        $res = CIBlockSection::GetList([], ['IBLOCK_ID' => $depsIblockId, 'ID' => array_keys($deps)], false, ['ID', 'UF_OTV_KADR']);
        while ($ob = $res->getNext()) {
            if (empty($ob['UF_OTV_KADR'])) {
                unset($deps[$ob['ID']]);
                continue;
            }
            $deps[$ob['ID']] = $ob['UF_OTV_KADR'];
        }
    }
    return $deps ? end($deps) : null;
};

$getUserOtvpoKadram = function ($userId) use (&$getDepOtvpoKadram) {
    $deps = CIntranetUtils::GetUserDepartments($userId);
    if ($deps) {
        foreach ($deps as $dep) {
            $depOtv = $getDepOtvpoKadram($dep);
            if ($depOtv) {
                return $depOtv;
            }
        }
    }
    return null;
};

$getUserVacationsByXmlId = function ($userXmlId, $actual = false) {
    \Bitrix\Main\Loader::includeModule("citto.integration");
    $vacations_list = [];
    global $guvSConnect;
    if (!isset($guvSConnect)) {
        $guvSConnect = \Citto\Integration\Source1C::Connect1C(null, ['features' => SOAP_SINGLE_ELEMENT_ARRAYS]);
    }
    if (!empty($userXmlId)) {
        $rRespone = \Citto\Integration\Source1C::GetArray($guvSConnect, 'VacationSchedule', ['EmployeeID' => $userXmlId]);
        if ($rRespone['result'] == 1 && !empty($rRespone['Data']['VacationSchedule']['VacationScheduleRecord'])) {
            foreach ($rRespone['Data']['VacationSchedule']['VacationScheduleRecord'] as $VacationScheduleRecord) {
                if (!isset($VacationScheduleRecord['DateStart']) || !isset($VacationScheduleRecord['DaysCount'])) {
                    continue;
                }
                if ($actual && strtotime($VacationScheduleRecord['DateStart']) < time()) {
                    continue;
                }
                $vacations_list[] = [
                    'FROM' => new DateTime($VacationScheduleRecord['DateStart']),
                    'DAYS' => (int)$VacationScheduleRecord['DaysCount'],
                ];
            }
        }
    }
    return $vacations_list;
};

$getUserVacations = function ($userId, $actual = false) use (&$getUserVacationsByXmlId) {
    $userXmlId = current(
        \Bitrix\Main\UserTable::getList([
            'select' => ['XML_ID'],
            'filter' => ['ID' => (int)$userId],
            'limit'=>1
        ])->fetch() ?? []
    );
    return $getUserVacationsByXmlId($userXmlId, $actual);
};

/**
 * Получение руководителей по департаментам, расширение CIntranetUtils::GetDepartmentManager
 */
$GetDepartmentManager = function (
    $arDepartments,
    $skipUserId = false,
    $bRecursive = false,
    $ufHeadCodes = ['UF_HEAD', 'UF_HEAD2']
) use (&$GetDepartmentManager) {
    \Bitrix\Main\Loader::includeModule('iblock');

    $ibDept         = COption::GetOptionInt('intranet', 'iblock_structure', false);
    $SECTIONS_SETTINGS_CACHE = (function () {
        if (self::$SECTIONS_SETTINGS_CACHE == null) {
            self::_GetDeparmentsTree();
        }
        return self::$SECTIONS_SETTINGS_CACHE;
    })->bindTo(new CIntranetUtils(), 'CIntranetUtils')();

    if (!is_array($arDepartments) || empty($arDepartments) || empty($SECTIONS_SETTINGS_CACHE) || empty($ibDept)) {
        return array();
    }

    $dbRes = CIBlockSection::GetList(
        [],
        array('IBLOCK_ID' => $ibDept, 'ACTIVE' => 'Y'),
        false,
        ['ID']+$ufHeadCodes
    );
    while ($ob = $dbRes->fetch()) {
        if (!isset($SECTIONS_SETTINGS_CACHE['DATA'][$ob['ID']])) {
            continue;
        }
        $SECTIONS_SETTINGS_CACHE['DATA'][$ob['ID']] += $ob;
    }

    $arManagers = array();
    $arManagerIDs = array();
    foreach ($arDepartments as $section_id) {
        $arSection = $SECTIONS_SETTINGS_CACHE['DATA'][$section_id];
        foreach ($ufHeadCodes as $ufHeadCode) {
            if ($arSection[$ufHeadCode] && $arSection[$ufHeadCode] != $skipUserId) {
                $arManagers[$arSection[$ufHeadCode]] = null;
                $arManagerIDs[] = $arSection[$ufHeadCode];
            }
        }
    }

    if (count($arManagerIDs) > 0) {
        $dbRes = CUser::GetList($by = 'ID', $sort = 'ASC', array('ID' => implode('|', array_unique($arManagerIDs))));
        while ($arUser = $dbRes->GetNext()) {
            $arManagers[$arUser['ID']] = $arUser;
        }
    }

    foreach ($arDepartments as $section_id) {
        $arSection = $SECTIONS_SETTINGS_CACHE['DATA'][$section_id];

        foreach ($ufHeadCodes as $ufHeadCode) {
            $bFound = $arSection[$ufHeadCode]
                && $arSection[$ufHeadCode] != $skipUserId
                && array_key_exists($arSection[$ufHeadCode], $arManagers);
            if ($bFound) {
                break;
            }
        }
        
        if (!$bFound && $bRecursive && $arSection['IBLOCK_SECTION_ID']) {
            $ar = $GetDepartmentManager(array($arSection['IBLOCK_SECTION_ID']), $skipUserId, $bRecursive, $ufHeadCodes);
            $arManagers = $arManagers + $ar;
        }
    }

    return $arManagers;
};

/**
 * Получение руководителей по по пользователю
 */
$GetUserDepartmentManager = function ($USER_ID, $skipUserId = false, $bRecursive = false) use (&$GetDepartmentManager) {
    return $GetDepartmentManager(CIntranetUtils::GetUserDepartments($USER_ID), $skipUserId, $bRecursive);
};

/**
 * Уведомления для БП
 * @see https://corp.tularegion.local/company/personal/user/2440/tasks/task/view/45347/
 * @param int $iblockId
 * @param int $id
 * @return string
 */
function bizprocNotify(int $iblockId = 0, int $id = 0)
{
    if ($iblockId <= 0 || $id <= 0) {
        return '';
    }

    if ($iblockId == 590) {
        \Bitrix\Main\Loader::includeModule("iblock");
        $res = CIBlockElement::GetList(
            [],
            [
                'IBLOCK_ID' => $iblockId,
                'ID'        => $id
            ],
            false,
            false,
            [
                'ID',
                'PROPERTY_USER',
            ]
        );
        if ($row = $res->GetNext()) {
            $dbRes = \CUser::GetList($by = 'ID', $sort = 'ASC', array('ID' => $row['PROPERTY_USER_VALUE']));
            $arEmails = [];
            if ($arUser = $dbRes->GetNext()) {
                $arEmails[ $arUser['ID'] ] = $arUser['EMAIL'];
            }

            if (!empty($arEmails)) {
                $arFields = array(
                    'SENDER'    => 'corp-noreply@tularegion.ru',
                    'RECEIVER'  => implode(';', $arEmails),
                    'TITLE'     => 'Вам необходимо подписать уведомление о переходе на электронные трудовые книжки',
                    'MESSAGE'   => '<p style="font-size:14pt;"><a href="https://corp.tularegion.ru/">Уведомление от корпоративного портала!</a></p><p style="font-size:14pt;">Вам необходимо подписать уведомление о переходе на электронные трудовые книжки.</p><p style="font-size:14pt;">Для этого перейдите на вкладку "бизнес-процессы" в левом меню, нажмите "приступить" в соответствующем бизнес-процессе.</p><p style="font-size:14pt;">Ознакомьтесь с уведомлением и подпишите его электронной подписью.</p>'
                );

                $event = new \CEvent();
                $event->Send('BIZPROC_HTML_MAIL_TEMPLATE', 's1', $arFields, "N");
            }
        }
    }

    return '';
}
