<?php

use Monolog\Logger;
use Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;
use Citto\Integration\Source1C;
use Citto\ControlOrders\Executors;
use Monolog\Handler\RotatingFileHandler;

$_SERVER['DOCUMENT_ROOT'] = realpath(dirname(__FILE__) . '/../..');
$DOCUMENT_ROOT            = $_SERVER['DOCUMENT_ROOT'];

define('NO_KEEP_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);
define('CHK_EVENT', true);
define('MODULE_NAME', 'citto.integration');

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';
require $_SERVER['DOCUMENT_ROOT'] . '/local/vendor/autoload.php';

Loader::includeModule(MODULE_NAME);
Loader::includeModule('iblock');
$arModulesOptions = unserialize(Option::get(MODULE_NAME, 'values'));

if ($arModulesOptions['user_id'] == '') {
    $arModulesOptions['user_id'] = 1;
}
if ($arModulesOptions['ac_password'] == '') {
    $arModulesOptions['ac_password'] = 'password';
}
if ($arModulesOptions['ActivateUser'] == '') {
    $arModulesOptions['ActivateUser'] = 'N';
}
if ($arModulesOptions['log_level'] == '') {
    $arModulesOptions['log_level'] = 'INFO';
}

$arOrganisations = [
    'ac0fdad2-c6e2-11e8-87fd-0050568633e9' => 's1', // Комитет по делам ЗАГС и ОД МР в ТО
    'cdb26956-340b-11e4-b02f-08edb9e6b700' => 's1', // Правительство Тульской области
];

$logger = new Logger('1c_import');
$logger->pushHandler(
    new RotatingFileHandler(
        $_SERVER['DOCUMENT_ROOT'] . '/local/logs/1c_import/users.log',
        60
    )
);

if ($_REQUEST['p'] != $arModulesOptions['ac_password']) {
    if ($argv[0] != '') {
        $logger->info('SBS: Started Users Import');
        $started_name = 'SBS:';
    } else {
        $logger->error('WBS: Not password accepted', ['password' => $_REQUEST['ac_password']]);
        die;
    }
} else {
    $started_name = 'WBS:';
    $logger->info('WBS: Started Users Import');
}

@set_time_limit(0);
@ignore_user_abort(true);
global $USER;
$USER->Authorize($arModulesOptions['user_id']);

$arHeadPositions = [
    '6704a0b2-47c8-11e4-91b7-00505686354c',
    '45250d78-397a-11e4-b368-50465d9db6a7',
    '45250d41-397a-11e4-b368-50465d9db6a7',
    'd2401b41-28e2-11e7-85b2-0050568633e9',
    '45250d56-397a-11e4-b368-50465d9db6a7',
    '45250d44-397a-11e4-b368-50465d9db6a7',
    '45250d03-397a-11e4-b368-50465d9db6a7',
    '45250cff-397a-11e4-b368-50465d9db6a7',
    '45250d43-397a-11e4-b368-50465d9db6a7',
    '45250d48-397a-11e4-b368-50465d9db6a7',
    '270b0b72-47c8-11e4-91b7-00505686354c',
    '45250d57-397a-11e4-b368-50465d9db6a7',
    '58724470-28e2-11e7-85b2-0050568633e9',
];

$arGroupsByName = [];
$rsGroups = CGroup::GetList(($by = 'c_sort'), ($order = 'desc'), []);
while ($arGroup = $rsGroups->GetNext()) {
    $NAME_DEP = explode(': Сотрудники', $arGroup['NAME']);
    if (count($NAME_DEP) > 1) {
        $arGroupsByName[ $NAME_DEP[0] ]['ID']      = $arGroup['ID'];
        $arGroupsByName[ $NAME_DEP[0] ]['SITE_ID'] = explode('_', $arGroup['STRING_ID'])[1];
    }
}

$arExecutors = Executors::getList();
$arControlOrders = [];
foreach ($arExecutors as $arExecutor) {
    $arControlOrders = array_merge($arControlOrders, [$arExecutor['PROPERTY_RUKOVODITEL_VALUE']]);
    $arControlOrders = array_merge($arControlOrders, $arExecutor['PROPERTY_ZAMESTITELI_VALUE']);
    $arControlOrders = array_merge($arControlOrders, $arExecutor['PROPERTY_ISPOLNITELI_VALUE']);
    $arControlOrders = array_merge($arControlOrders, $arExecutor['PROPERTY_IMPLEMENTATION_VALUE']);
}
$arControlOrders = array_unique(array_filter($arControlOrders));

$arSectionSyncs  = [];
$arSectionGroups = [];
$arFilter        = ['IBLOCK_ID' => 5];
$rsSect          = CIBlockSection::GetList(['left_margin' => 'asc'], $arFilter);
while ($arSect = $rsSect->GetNext()) {
    if ($arSect['XML_ID'] != '') {
        if ($arSect['DEPTH_LEVEL'] == 2) {
            $arGroup = $arGroupsByName['Корпоративный портал'];
        }
        if ($arGroupsByName[ $arSect['NAME'] ]['ID'] != '') {
            $arGroup = $arGroupsByName[ $arSect['NAME'] ];
        }

        $arSectionSyncs[ $arSect['XML_ID'] ]  = $arSect['ID'];
        $arSectionGroups[ $arSect['XML_ID'] ] = $arGroup;
    }
}

$rConnect = Source1C::Connect1C();
$arEmployees = Source1C::GetEmployyes($rConnect, false);
if (count($arEmployees['EMPLOYEES']) > 0) {
    $logger->info($started_name . 'Data loaded success', ['employees_count' => count($arEmployees['EMPLOYEES'])]);
} else {
    $logger->critical($started_name . 'Data not loaded', [$arEmployees]);
    $logger->info($started_name . 'Stopped');
    die;
}

$arUsersByLogin         = [];
$arUsersInnByID         = [];
$arUsersBySID           = [];
$arUsersByDepartment    = [];

$userParams     = [
    'SELECT' => [
        'UF_SID',
        'UF_INN',
        'UF_DEPARTMENT'
    ],
    'FIELDS' => [
        'ID',
        'LOGIN',
    ],
];
$rsUsers = CUser::GetList(($by = 'personal_country'), ($order = 'desc'), [], $userParams);

while ($arUser = $rsUsers->GetNext()) {
    if ($arUser['UF_SID'] != '') {
        $arUsersBySID[ $arUser['UF_SID'] ] = $arUser['ID'];
        $arUsersByDepartment[ $arUser['UF_SID'] ] = $arUser['UF_DEPARTMENT'];
    } else {
        $arUsersByLogin[ mb_strtolower($arUser['LOGIN']) ] = $arUser['ID'];
    }
    $arUsersInnByID[ $arUser['ID'] ]   = $arUser['UF_INN'];
}

$arCounts = [
    'ADD'               => 0,
    'UPDATE_BY_LOGIN'   => 0,
    'UPDATE_BY_SID'     => 0,
    'DEACTIVATE_BY_SID' => 0,
];
$arErrors = [
    'NO_SID'    => [],
    'NO_UPDATE' => [],
    'NO_ADD'    => [],
    'NO_DEP'    => [],
];

$obUser = new CUser();

$arUsedSid = [];

foreach ($arEmployees['EMPLOYEES'] as $k => $v) {
    // if (!array_key_exists($v['ORGANISATION'], $arOrganisations)) {
    //     continue;
    // }
    if ($v['SID'] != '') {
        /*
         * Прудникова Татьяна Александровна 2 раза в выгрузке.
         * Таких 21 пользователь
         */
        if (isset($arUsedSid[ $v['SID'] ])) {
            continue;
        }

        $arUsedSid[ $v['SID'] ] = $v['SID'];
        if ($arSectionSyncs[ $v['SUBDIVISION'] ] != '') {
            $v['NAME']   = str_replace('  ', ' ', $v['NAME']);
            $fullname    = explode(' ', $v['NAME']);
            $second_name = implode(' ', array_slice($fullname, 2));
            $arFields    = [
                'NAME'              => $fullname[1],
                'LAST_NAME'         => $fullname[0],
                'SECOND_NAME'       => $second_name,
                'EMAIL'             => $v['EMAIL'] ? $v['EMAIL'] : $v['LOGIN'] . '@tularegion.ru',
                'LOGIN'             => $v['LOGIN'],
                'LID'               => 's1',
                'EXTERNAL_AUTH_ID'  => 'LDAP#1',
                'UF_SID'            => $v['SID'],
                'XML_ID'            => $v['SID'],
                'GROUP_ID'          => [2, $arSectionGroups[ $v['SUBDIVISION'] ]['ID']],
                'WORK_POSITION'     => $arEmployees['POSITIONS'][ $v['POSITION'] ],
                'UF_DEPARTMENT'     => [$arSectionSyncs[ $v['SUBDIVISION'] ]],
                'UF_INN'            => $v['INN'],
                'UF_FIRST_LOGIN'    => '1',
            ];

            if (
                isset($arUsersByDepartment[$v['SID']]) &&
                json_encode($arFields['UF_DEPARTMENT']) != json_encode($arUsersByDepartment[$v['SID']])
            ) {
                $arFields['UF_FIRST_LOGIN'] = '0';
            }

            if (!empty($v['POSITION_TYPE'])) {
                $arFields['UF_GOV_EMPLOYEE'] = ($v['POSITION_TYPE'] != 'НеГосслужащие' ? '1' : '0');
            }

            if (!empty($v['DECRET'])) {
                $arFields['UF_DECRET'] = ($v['DECRET'] ? '1' : '0');
            }

            if (!empty($v['FULL_POSITION'])) {
                $arFields['UF_WORK_POSITION'] = $v['FULL_POSITION'];
            }
            if (!empty($v['FULL_POSITION_DAT'])) {
                $arFields['UF_WORK_POSITION_DAT'] = $v['FULL_POSITION_DAT'];
            }
            if (!empty($v['FULL_POSITION_ROD'])) {
                $arFields['UF_WORK_POSITION_ROD'] = $v['FULL_POSITION_ROD'];
            }
            if (!empty($v['FULL_POSITION_TV'])) {
                $arFields['UF_WORK_POSITION_TV'] = $v['FULL_POSITION_TV'];
            }

            $arFields['UF_LAST_1C_UPD'] = ConvertTimeStamp(time(), 'SHORT', 'ru');
            $arFields['UF_WORKBOOK_ELECTRONIC'] = $v['WORKBOOK_ELECTRONIC'];

            if ($arUsersBySID[ $arFields['UF_SID'] ]) {
                $userId = (int)$arUsersBySID[ $arFields['UF_SID'] ];
                if ($arModulesOptions['ActivateUser'] == 'Y') {
                    $arFields['ACTIVE'] = 'Y';
                }
                $arFields['GROUP_ID'] = CUser::GetUserGroup($userId);
                if (!in_array($arSectionGroups[ $v['SUBDIVISION'] ]['ID'], $arFields['GROUP_ID'])) {
                    $arFields['GROUP_ID'][] = $arSectionGroups[ $v['SUBDIVISION'] ]['ID'];
                }
                if (in_array($userId, $arControlOrders)) {
                    $arFields['GROUP_ID'][] = 96;
                } elseif (in_array(96, $arFields['GROUP_ID'])) {
                    // Удалить 96 группу, если нет в ИБ
                    $arFields['GROUP_ID'] = array_diff($arFields['GROUP_ID'], [96]);
                }

                // ГУ ТО "Ситуационный центр"
                if (in_array(138, $arFields['GROUP_ID'])) {
                    $arFields['LID'] = 'sc';
                }

                unset($arFields['EMAIL']);
                unset($arFields['LOGIN']);
                $obUser->Update($userId, $arFields);
                if ($error = $obUser->LAST_ERROR) {
                    $arErrors['NO_UPDATE'][] = [
                        'FIELDS'    => $arFields,
                        'ERROR'     => $error
                    ];
                    $logger->error($started_name . 'User not updated', ['FIELDS' => $arFields, 'EMPLOYEE' => $v]);
                } else {
                    $arCounts['UPDATE_BY_SID']++;
                }
                unset($arUsersBySID[ $arFields['UF_SID'] ]);
                $logger->debug($started_name . 'User updated by SID', ['FIELDS' => $arFields, 'EMPLOYEE' => $v]);
            } elseif ($arUsersByLogin[ mb_strtolower($arFields['LOGIN']) ]) {
                $userId = (int)$arUsersByLogin[ mb_strtolower($arFields['LOGIN']) ];
                $arFields['GROUP_ID'] = CUser::GetUserGroup($userId);
                if (!in_array($arSectionGroups[ $v['SUBDIVISION'] ]['ID'], $arFields['GROUP_ID'])) {
                    $arFields['GROUP_ID'][] = $arSectionGroups[ $v['SUBDIVISION'] ]['ID'];
                }
                if (in_array($userId, $arControlOrders)) {
                    $arFields['GROUP_ID'][] = 96;
                } elseif (in_array(96, $arFields['GROUP_ID'])) {
                    // Удалить 96 группу, если нет в ИБ
                    $arFields['GROUP_ID'] = array_diff($arFields['GROUP_ID'], [96]);
                }

                // ГУ ТО "Ситуационный центр"
                if (in_array(138, $arFields['GROUP_ID'])) {
                    $arFields['LID'] = 'sc';
                }

                $obUser->Update($userId, $arFields);
                if ($error = $obUser->LAST_ERROR) {
                    $logger->error($started_name . 'User not updated', ['FIELDS' => $arFields, 'EMPLOYEE' => $v]);
                    $arErrors['NO_UPDATE'][] = [
                        'FIELDS'    => $arFields,
                        'ERROR'     => $error
                    ];
                } else {
                    $arCounts['UPDATE_BY_LOGIN']++;
                }
                unset($arUsersByLogin[ mb_strtolower($arFields['LOGIN']) ]);
                $logger->debug($started_name . 'User updated by LOGIN', ['FIELDS' => $arFields, 'EMPLOYEE' => $v]);
            } else {
                $arFields['ACTIVE'] = 'Y';
                $arFields['UF_FIRST_LOGIN'] = '0';

                $userId = $obUser->Add($arFields);
                if ((int)$userId > 0) {
                    $arCounts['ADD']++;
                    $logger->debug($started_name . 'User Created', ['FIELDS' => $arFields, 'EMPLOYEE' => $v]);
                } else {
                    $logger->error($started_name . 'User not updated', ['ERROR' => $obUser->LAST_ERROR, 'FIELDS' => $arFields, 'EMPLOYEE' => $v]);
                    $arErrors['NO_ADD'][] = [
                        'FIELDS'    => $arFields,
                        'ERROR'     => $obUser->LAST_ERROR
                    ];
                }
            }

            if ((int)$userId > 0) {
                if (in_array($v['POSITION'], $arHeadPositions)) {
                    $bs       = new CIBlockSection();
                    $arFields = [
                        'UF_HEAD' => (int)$userId
                    ];

                    $res = $bs->Update($arSectionSyncs[ $v['SUBDIVISION'] ], $arFields);
                }
            }
        } else {
            $logger->error($started_name . 'User not DEP', ['EMPLOYEE' => $v]);
            $arErrors['NO_DEP'][] = $v;
        }
    } else {
        $logger->error($started_name . 'User not SID', ['EMPLOYEE' => $v]);
        $arErrors['NO_SID'][] = $v;
    }
}

$arUserList = [];
$userParams = [
    'FIELDS' => [
        'ID',
        'LOGIN',
        'ACTIVE',
    ],
];
$rsUsers = CUser::GetList(($by = 'personal_country'), ($order = 'desc'), [], $userParams);
while ($arUser = $rsUsers->GetNext()) {
    $arUserList[ $arUser['ID'] ] = $arUser;
}

unset($arUsersBySID['S-1-5-21-3257783013-1731373831-2674042523-59873']); // Марков Дмитрий Сергеевич
unset($arUsersBySID['S-1-5-21-3257783013-1731373831-2674042523-2025']); // Шерин Валерий Витальевич

foreach ($arUsersBySID as $k => $v) {
    $userId = (int)$v;
    if ($arUsersInnByID[ $userId ] != '') {
        if ($arUserList[ $userId ]['ACTIVE'] == 'N') {
            continue;
        }
        $obUser->Update($userId, ['ACTIVE' => 'N']);
        if ($error = $obUser->LAST_ERROR) {
            $arErrors['NO_UPDATE'][] = [
                'FIELDS'    => ['ACTIVE' => 'N'],
                'ERROR'     => $error
            ];
            $logger->error($started_name . 'User not deactivated', ['SID' => $k, 'ID' => $v]);
        } else {
            $logger->debug($started_name . 'User deactivated', ['SID' => $k, 'ID' => $v]);
            $arCounts['DEACTIVATE_BY_SID']++;
        }
    }
}
$logger->info($started_name . 'Stopped User Import Success', ['COUNTS' => $arCounts, 'ERRORS' => $arErrors]);

$USER->Logout();
