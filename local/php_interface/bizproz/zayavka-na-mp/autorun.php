<<<<<<< HEAD
<?

if (php_sapi_name() !== 'cli') {
    die;
}
define('BP_LINK_IBLOCK', 525);
define('BP_IBLOCK', 526);
define('ORGANS_IBLOKC_ID', 5);
define('BP_TEMPLATE_ID', 411);

define('NEED_AUTH', false);
define('NOT_CHECK_PERMISSIONS', true);
define('NO_KEEP_STATISTIC', true);
define('LANG', "s1");
define('SITE_ID', "s1");
$DOCUMENT_ROOT = $_SERVER['DOCUMENT_ROOT'] = realpath(__DIR__."/../../../../");
include $_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/include/prolog_before.php";

\Bitrix\Main\Loader::includeModule("im");
\Bitrix\Main\Loader::includeModule("iblock");
\Bitrix\Main\Loader::includeModule("workflow");
\Bitrix\Main\Loader::includeModule("bizproc");

global $userFields;
try {
    $arGroups = [];
    $zayavki = [];
    $res = CIBlockElement::GetList(
        [],
        [
            'IBLOCK_ID' => BP_LINK_IBLOCK,
            'PROPERTY_OBRABOTANA' => 1177,
            'ACTIVE' => "Y"
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
            'PROPERTY_RUK_OIV_ORG',
            'PROPERTY_YEAR',
        ]
    );
    while ($ob = $res->fetch()) {
        $year = date('Y', strtotime($ob['DATE_CREATE']));
        if (!empty($ob['PROPERTY_YEAR_VALUE']) && in_array((int)$ob['PROPERTY_YEAR_VALUE'], [2020, 2021])) {
            $year = (int)$ob['PROPERTY_YEAR_VALUE'];
        }
        $arGroups[ $year ][ md5($ob['PROPERTY_ORGAN_VALUE'])."_".$ob['PROPERTY_RUK_OIV_ORG_VALUE']."_".$ob['PROPERTY_TIP_DOLZHNOSTI_ENUM_ID']."_".$ob['PROPERTY_PROSHU_PREDOSTAVIT_ENUM_ID'] ][ $ob['ID'] ] = $ob;
    }

    foreach ($arGroups as $year => $groups) {
        foreach ($groups as $group) {
            $group_item = current($group);

            $arUser = $userFields($group_item['CREATED_BY']);

            if (empty($arUser['DEPARTMENT'])) {
                throw new Exception("У сотрудника нет департамента " . $group_item['CREATED_BY']);
            }

            if (empty($group_item['PROPERTY_RUK_OIV_ORG_VALUE'])) {
                throw new Exception("Не указан руководитель " . $group_item['ID']);
            }

            $department = CIBlockSection::GetList(
                [],
                [
                    '=NAME'     => $arUser['DEPARTMENT'],
                    'IBLOCK_ID' => ORGANS_IBLOKC_ID
                ],
                false,
                ['ID','NAME'],
                ['nTopCount'=>1]
            )->Fetch();
            
            $arProps = [
                'ZAYAVKI'           => array_keys($group),
                'OIV'               => $department['ID'],
                'NAZVANIE_OIV'      => $department['NAME'],
                'RUKOVODITEL_OIV'   => $group_item['PROPERTY_RUK_OIV_ORG_VALUE'],
                'TIP'               => $group_item['PROPERTY_PROSHU_PREDOSTAVIT_VALUE']
            ];
            
            $el = new CIBlockElement();
            $arLoadProductArray = [
                'CREATED_BY'         => $group_item['PROPERTY_RUK_OIV_ORG_VALUE'],
                'MODIFIED_BY'        => $group_item['PROPERTY_RUK_OIV_ORG_VALUE'],
                'IBLOCK_SECTION_ID'  => false,
                'IBLOCK_ID'          => BP_IBLOCK,
                'PROPERTY_VALUES'    => $arProps,
                'NAME'               => "Заявка на ".$arProps['TIP'],
                'ACTIVE'             => "Y",
                'PREVIEW_TEXT'       => "",
            ];
            $documentId = $el->Add($arLoadProductArray);
            if (!$documentId) {
                throw new Exception($el->LAST_ERROR);
            }

            if ($reestGen($documentId) !== true) {
                throw new Exception("Ошибка создания реестра");
            }

            $arErrorsTmp = array();
            $wfId = CBPDocument::StartWorkflow(
                BP_TEMPLATE_ID,
                ["lists", "BizprocDocument", $documentId],
                ['TargetUser' => "user_".$group_item['PROPERTY_RUK_OIV_ORG_VALUE']],
                $arErrorsTmp
            );
            if (count($arErrorsTmp) > 0) {
                throw new Exception(array_reduce($arErrorsTmp, function ($carry, $item) {
                    return $carry.".".$item['message'];
                }, ""));
            }

            foreach ($group as $group_item) {
                CIBlockElement::SetPropertyValuesEx(
                    $group_item['ID'],
                    BP_LINK_IBLOCK,
                    ['OBRABOTANA'=>1176]
                );
            }
        }
    }
} catch (Exception $exc) {
    if (!empty($documentId)) {
        CIBlockElement::Delete($documentId);
    }
    CIMMessenger::Add([
        "MESSAGE_TYPE"  => "S",
        "TO_USER_ID"    => 2440,
        "FROM_USER_ID"  => 1,
        "MESSAGE"       => "Ошибка в обработке bp_mp: " . $exc->getMessage(),
        "AUTHOR_ID"     => 5,
        "EMAIL_TEMPLATE"=> "some",
        "NOTIFY_TYPE"   => 2,
        "NOTIFY_MODULE" => "main",
        "NOTIFY_EVENT"  => "IM_GROUP_INVITE",
        "NOTIFY_TITLE"  => "Ошибка в обработке bp_mp " . $exc->getMessage(),
    ]);
    throw new $exc;
}
=======
<?

if (php_sapi_name() !== 'cli') {
    die;
}
define('BP_LINK_IBLOCK', 525);
define('BP_IBLOCK', 526);
define('ORGANS_IBLOKC_ID', 5);
define('BP_TEMPLATE_ID', 411);

define('NEED_AUTH', false);
define('NOT_CHECK_PERMISSIONS', true);
define('NO_KEEP_STATISTIC', true);
define('LANG', "s1");
define('SITE_ID', "s1");
$DOCUMENT_ROOT = $_SERVER['DOCUMENT_ROOT'] = realpath(__DIR__."/../../../../");
include $_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/include/prolog_before.php";

\Bitrix\Main\Loader::includeModule("im");
\Bitrix\Main\Loader::includeModule("iblock");
\Bitrix\Main\Loader::includeModule("workflow");
\Bitrix\Main\Loader::includeModule("bizproc");

global $userFields;
try {
    $arGroups = [];
    $zayavki = [];
    $res = CIBlockElement::GetList(
        [],
        [
            'IBLOCK_ID' => BP_LINK_IBLOCK,
            'PROPERTY_OBRABOTANA' => 1177,
            'ACTIVE' => "Y"
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
            'PROPERTY_RUK_OIV_ORG',
            'PROPERTY_YEAR',
        ]
    );
    while ($ob = $res->fetch()) {
        $year = date('Y', strtotime($ob['DATE_CREATE']));
        if (!empty($ob['PROPERTY_YEAR_VALUE']) && in_array((int)$ob['PROPERTY_YEAR_VALUE'], [2020, 2021])) {
            $year = (int)$ob['PROPERTY_YEAR_VALUE'];
        }
        $arGroups[ $year ][ md5($ob['PROPERTY_ORGAN_VALUE'])."_".$ob['PROPERTY_RUK_OIV_ORG_VALUE']."_".$ob['PROPERTY_TIP_DOLZHNOSTI_ENUM_ID']."_".$ob['PROPERTY_PROSHU_PREDOSTAVIT_ENUM_ID'] ][ $ob['ID'] ] = $ob;
    }

    foreach ($arGroups as $year => $groups) {
        foreach ($groups as $group) {
            $group_item = current($group);

            $arUser = $userFields($group_item['CREATED_BY']);

            if (empty($arUser['DEPARTMENT'])) {
                throw new Exception("У сотрудника нет департамента " . $group_item['CREATED_BY']);
            }

            if (empty($group_item['PROPERTY_RUK_OIV_ORG_VALUE'])) {
                throw new Exception("Не указан руководитель " . $group_item['ID']);
            }

            $department = CIBlockSection::GetList(
                [],
                [
                    '=NAME'     => $arUser['DEPARTMENT'],
                    'IBLOCK_ID' => ORGANS_IBLOKC_ID
                ],
                false,
                ['ID','NAME'],
                ['nTopCount'=>1]
            )->Fetch();
            
            $arProps = [
                'ZAYAVKI'           => array_keys($group),
                'OIV'               => $department['ID'],
                'NAZVANIE_OIV'      => $department['NAME'],
                'RUKOVODITEL_OIV'   => $group_item['PROPERTY_RUK_OIV_ORG_VALUE'],
                'TIP'               => $group_item['PROPERTY_PROSHU_PREDOSTAVIT_VALUE']
            ];
            
            $el = new CIBlockElement();
            $arLoadProductArray = [
                'CREATED_BY'         => $group_item['PROPERTY_RUK_OIV_ORG_VALUE'],
                'MODIFIED_BY'        => $group_item['PROPERTY_RUK_OIV_ORG_VALUE'],
                'IBLOCK_SECTION_ID'  => false,
                'IBLOCK_ID'          => BP_IBLOCK,
                'PROPERTY_VALUES'    => $arProps,
                'NAME'               => "Заявка на ".$arProps['TIP'],
                'ACTIVE'             => "Y",
                'PREVIEW_TEXT'       => "",
            ];
            $documentId = $el->Add($arLoadProductArray);
            if (!$documentId) {
                throw new Exception($el->LAST_ERROR);
            }

            if ($reestGen($documentId) !== true) {
                throw new Exception("Ошибка создания реестра");
            }

            $arErrorsTmp = array();
            $wfId = CBPDocument::StartWorkflow(
                BP_TEMPLATE_ID,
                ["lists", "BizprocDocument", $documentId],
                ['TargetUser' => "user_".$group_item['PROPERTY_RUK_OIV_ORG_VALUE']],
                $arErrorsTmp
            );
            if (count($arErrorsTmp) > 0) {
                throw new Exception(array_reduce($arErrorsTmp, function ($carry, $item) {
                    return $carry.".".$item['message'];
                }, ""));
            }

            foreach ($group as $group_item) {
                CIBlockElement::SetPropertyValuesEx(
                    $group_item['ID'],
                    BP_LINK_IBLOCK,
                    ['OBRABOTANA'=>1176]
                );
            }
        }
    }
} catch (Exception $exc) {
    if (!empty($documentId)) {
        CIBlockElement::Delete($documentId);
    }
    CIMMessenger::Add([
        "MESSAGE_TYPE"  => "S",
        "TO_USER_ID"    => 2440,
        "FROM_USER_ID"  => 1,
        "MESSAGE"       => "Ошибка в обработке bp_mp: " . $exc->getMessage(),
        "AUTHOR_ID"     => 5,
        "EMAIL_TEMPLATE"=> "some",
        "NOTIFY_TYPE"   => 2,
        "NOTIFY_MODULE" => "main",
        "NOTIFY_EVENT"  => "IM_GROUP_INVITE",
        "NOTIFY_TITLE"  => "Ошибка в обработке bp_mp " . $exc->getMessage(),
    ]);
    throw new $exc;
}
>>>>>>> e0a0eba79 (init)
