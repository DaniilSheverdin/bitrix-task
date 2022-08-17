<?php

use Monolog\Logger;
use Bitrix\Main\Config\Option;
use Citto\Integration\Source1C;
use Monolog\Handler\RotatingFileHandler;

$_SERVER['DOCUMENT_ROOT'] = realpath(dirname(__FILE__) . '/../..');
$DOCUMENT_ROOT            = $_SERVER['DOCUMENT_ROOT'];

define('NO_KEEP_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);
define('CHK_EVENT', true);
define('MODULE_NAME', 'citto.integration');

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';
require $_SERVER['DOCUMENT_ROOT'] . '/local/vendor/autoload.php';

CModule::IncludeModule('iblock');
CModule::IncludeModule('intranet');
CModule::IncludeModule(MODULE_NAME);

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
        $_SERVER['DOCUMENT_ROOT'] . '/local/logs/1c_import/groups.log',
        60
    )
);

if ($_REQUEST['p'] != $arModulesOptions['ac_password']) {
    if ($argv[0] != '') {
        $logger->info('SBS: Started Subdivisions Import');
        $started_name = 'SBS:';
    } else {
        $logger->error('WBS: Not password accepted', ['password' => $_REQUEST['ac_password']]);
        die;
    }
} else {
    $started_name = 'WBS:';
    $logger->info('WBS: Started Subdivisions Import');
}

/**
 * Собрать дерево из структуры
 * @param array $elements Элементы.
 * @param mixed $parentId Родительский раздел.
 * @return array
 */
function buildTree(array &$elements, $parentId = '')
{
    $branch = [];
    foreach ($elements as $key => $element) {
        if ($element['PARENT_ID'] == $parentId) {
            $children = buildTree($elements, $key);
            if ($children) {
                $element['CHILD'] = $children;
            }
            $branch[ $key ] = $element;
            unset($elements[ $key ]);
        }
    }
    return $branch;
}

$rConnect               = Source1C::Connect1C();
$arSubdivisionsSud      = $arSubdivisions = Source1C::GetSubdivisions($rConnect, false);
$sSudGuid               = 'e4c65a83-c6e3-11e8-87fd-0050568633e9';
$arSubdivisionsTree     = buildTree($arSubdivisions);
$arSubdivisionsTreeSud  = buildTree($arSubdivisionsSud, $sSudGuid);
unset($arSubdivisionsTree[ $sSudGuid ]);

$arCounts       = 0;
$arErrors       = 0;
$arSkip         = 0;
$IBLOCK_ID      = COption::GetOptionString('intranet', 'iblock_structure', '');
$arSectionSyncs = [];
$arSectionNames = [];
$arFilter       = ['IBLOCK_ID' => $IBLOCK_ID];
$rsSect         = CIBlockSection::GetList(
    ['left_margin' => 'asc'],
    $arFilter,
    false,
    [
        'ID',
        'XML_ID',
        'NAME',
        'IBLOCK_ID',
        'ACTIVE',
        'DESCRIPTION',
        'IBLOCK_SECTION_ID',
    ]
);
while ($arSect = $rsSect->GetNext()) {
    if ($arSect['XML_ID'] != '') {
        $arSectionSyncs[ $arSect['XML_ID'] ] = $arSect;//['ID'];
    } else {
        $arSectionNames[ $arSect['NAME'] ] = $arSect;//['ID'];
    }
}

function canUpdate($source = [], $target = [])
{
    $bUpdate = false;
    foreach ($target as $key => $value) {
        if (!isset($source[ $key ])) {
            $bUpdate = true;
        } elseif ($value != $source[ $key ]) {
            $bUpdate = true;
        }
    }

    return $bUpdate;
}
$bs = new CIBlockSection();
foreach ($arSubdivisionsTree as $k => $v) {
    if (count($v['CHILD']) > 0) {
        if (!array_key_exists($v['ORGANISATION'], $arOrganisations)) {
            continue;
        }
        $arFields = [
            'ACTIVE'            => 'Y',
            'IBLOCK_ID'         => $IBLOCK_ID,
            'NAME'              => $v['FULLNAME'] ? $v['FULLNAME'] : $v['SHORTNAME'],
            'DESCRIPTION'       => $v['SHORTNAME'],
            'IBLOCK_SECTION_ID' => 53,
            'XML_ID'            => $v['ID'],
        ];

        if (isset($arSectionSyncs[ $arFields['XML_ID'] ])) {
            if (canUpdate($arSectionSyncs[ $arFields['XML_ID'] ], $arFields)) {
                $res = $bs->Update($arSectionSyncs[ $arFields['XML_ID'] ]['ID'], $arFields);
            } else {
                $res = true;
                $arSkip++;
            }
        } elseif (isset($arSectionNames[ $arFields['NAME'] ])) {
            if (canUpdate($arSectionNames[ $arFields['NAME'] ], $arFields)) {
                $res = $bs->Update($arSectionNames[ $arFields['NAME'] ]['ID'], $arFields);
            } else {
                $res = true;
                $arSkip++;
            }
            $arSectionSyncs[ $arFields['XML_ID'] ] = $arSectionNames[ $arFields['NAME'] ];
        } else {
            $ID = $bs->Add($arFields);
            $res = ($ID > 0);
            $arSectionSyncs[ $arFields['XML_ID'] ] = ['ID' => $ID];
        }

        if (!$res) {
            echo $bs->LAST_ERROR;
            $arErrors++;
        } else {
            $arCounts++;
            foreach ($v['CHILD'] as $k2 => $v2) {
                if (!array_key_exists($v2['ORGANISATION'], $arOrganisations)) {
                    continue;
                }
                $arFields = [
                    'ACTIVE'            => 'Y',
                    'IBLOCK_ID'         => $IBLOCK_ID,
                    'NAME'              => $v2['FULLNAME'] ? $v2['FULLNAME'] : $v2['SHORTNAME'],
                    'DESCRIPTION'       => $v2['SHORTNAME'],
                    'IBLOCK_SECTION_ID' => $arSectionSyncs[ $v2['PARENT_ID'] ]['ID'],
                    'XML_ID'            => $v2['ID'],
                ];
                if (isset($arSectionSyncs[ $arFields['XML_ID'] ])) {
                    if (canUpdate($arSectionSyncs[ $arFields['XML_ID'] ], $arFields)) {
                        $res = $bs->Update($arSectionSyncs[ $arFields['XML_ID'] ]['ID'], $arFields);
                    } else {
                        $res = true;
                        $arSkip++;
                    }
                } elseif (isset($arSectionNames[ $arFields['NAME'] ])) {
                    if (canUpdate($arSectionNames[ $arFields['NAME'] ], $arFields)) {
                        $res = $bs->Update($arSectionNames[ $arFields['NAME'] ]['ID'], $arFields);
                    } else {
                        $res = true;
                        $arSkip++;
                    }
                    $arSectionSyncs[ $arFields['XML_ID'] ] = $arSectionNames[ $arFields['NAME'] ];
                } else {
                    $ID = $bs->Add($arFields);
                    $res = ($ID > 0);
                    $arSectionSyncs[ $arFields['XML_ID'] ] = ['ID' => $ID];
                }

                if ($res) {
                    $arCounts++;
                } else {
                    $arErrors++;
                }
                if (count($v2['CHILD']) > 0) {
                    foreach ($v2['CHILD'] as $k3 => $v3) {
                        if (!array_key_exists($v3['ORGANISATION'], $arOrganisations)) {
                            continue;
                        }
                        $arFields = [
                            'ACTIVE'            => 'Y',
                            'IBLOCK_ID'         => $IBLOCK_ID,
                            'NAME'              => $v3['FULLNAME'] ? $v3['FULLNAME'] : $v3['SHORTNAME'],
                            'DESCRIPTION'       => $v3['SHORTNAME'],
                            'IBLOCK_SECTION_ID' => $arSectionSyncs[ $v3['PARENT_ID'] ]['ID'],
                            'XML_ID'            => $v3['ID'],
                        ];
                        if (isset($arSectionSyncs[ $arFields['XML_ID'] ])) {
                            if (canUpdate($arSectionSyncs[ $arFields['XML_ID'] ], $arFields)) {
                                $res = $bs->Update($arSectionSyncs[ $arFields['XML_ID'] ]['ID'], $arFields);
                            } else {
                                $res = true;
                                $arSkip++;
                            }
                        } elseif (isset($arSectionNames[ $arFields['NAME'] ])) {
                            if (canUpdate($arSectionNames[ $arFields['NAME'] ], $arFields)) {
                                $res = $bs->Update($arSectionNames[ $arFields['NAME'] ]['ID'], $arFields);
                            } else {
                                $res = true;
                                $arSkip++;
                            }
                            $arSectionSyncs[ $arFields['XML_ID'] ] = $arSectionNames[ $arFields['NAME'] ];
                        } else {
                            $ID = $bs->Add($arFields);
                            $res = ($ID > 0);
                            $arSectionSyncs[ $arFields['XML_ID'] ] = ['ID' => $ID];
                        }
                        if ($res) {
                            $arCounts++;
                        } else {
                            $arErrors++;
                        }
                        if (count($v3['CHILD']) > 0) {
                            foreach ($v3['CHILD'] as $k4 => $v4) {
                                if (!array_key_exists($v4['ORGANISATION'], $arOrganisations)) {
                                    continue;
                                }
                                $arFields = [
                                    'ACTIVE'            => 'Y',
                                    'IBLOCK_ID'         => $IBLOCK_ID,
                                    'NAME'              => $v4['FULLNAME'] ? $v4['FULLNAME'] : $v4['SHORTNAME'],
                                    'DESCRIPTION'       => $v4['SHORTNAME'],
                                    'IBLOCK_SECTION_ID' => $arSectionSyncs[ $v4['PARENT_ID'] ]['ID'],
                                    'XML_ID'            => $v4['ID'],
                                ];
                                if (isset($arSectionSyncs[ $arFields['XML_ID'] ])) {
                                    if (canUpdate($arSectionSyncs[ $arFields['XML_ID'] ], $arFields)) {
                                        $res = $bs->Update($arSectionSyncs[ $arFields['XML_ID'] ]['ID'], $arFields);
                                    } else {
                                        $res = true;
                                        $arSkip++;
                                    }
                                } elseif (isset($arSectionNames[ $arFields['NAME'] ])) {
                                    if (canUpdate($arSectionNames[ $arFields['NAME'] ], $arFields)) {
                                        $res = $bs->Update($arSectionNames[ $arFields['NAME'] ]['ID'], $arFields);
                                    } else {
                                        $res = true;
                                        $arSkip++;
                                    }
                                    $arSectionSyncs[ $arFields['XML_ID'] ] = $arSectionNames[ $arFields['NAME'] ];
                                } else {
                                    $ID = $bs->Add($arFields);
                                    $res = ($ID > 0);
                                    $arSectionSyncs[ $arFields['XML_ID'] ] = ['ID' => $ID];
                                }
                                if ($res) {
                                    $arCounts++;
                                } else {
                                    $arErrors++;
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}

foreach ($arSubdivisionsTreeSud as $k => $v) {
    if (!array_key_exists($v['ORGANISATION'], $arOrganisations)) {
        continue;
    }
    $arFields = [
        'ACTIVE'            => 'Y',
        'IBLOCK_ID'         => $IBLOCK_ID,
        'NAME'              => $v['FULLNAME'] ? $v['FULLNAME'] : $v['SHORTNAME'],
        'DESCRIPTION'       => $v['SHORTNAME'],
        'IBLOCK_SECTION_ID' => 2229,
        'XML_ID'            => $v['ID'],
    ];

    if (isset($arSectionSyncs[ $arFields['XML_ID'] ])) {
        if (canUpdate($arSectionSyncs[ $arFields['XML_ID'] ], $arFields)) {
            $res = $bs->Update($arSectionSyncs[ $arFields['XML_ID'] ]['ID'], $arFields);
        } else {
            $res = true;
            $arSkip++;
        }
    } elseif (isset($arSectionNames[ $arFields['NAME'] ])) {
        if (canUpdate($arSectionNames[ $arFields['NAME'] ], $arFields)) {
            $res = $bs->Update($arSectionNames[ $arFields['NAME'] ]['ID'], $arFields);
        } else {
            $res = true;
            $arSkip++;
        }
        $arSectionSyncs[ $arFields['XML_ID'] ] = $arSectionNames[ $arFields['NAME'] ];
    } else {
        $ID = $bs->Add($arFields);
        $res = ($ID > 0);
        $arSectionSyncs[ $arFields['XML_ID'] ] = ['ID' => $ID];
    }
    if ($res) {
        $arCounts++;
    } else {
        $arErrors++;
    }
}

CIBlockSection::ReSort($IBLOCK_ID);

$logger->info($started_name . ' Stopped Group Import Success', ['COUNTS' => $arCounts, 'ERRORS' => $arErrors, 'SKIP' => $arSkip]);
