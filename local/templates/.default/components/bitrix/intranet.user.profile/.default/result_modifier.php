<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) {
    die();
}

// Убирает Благодарности
$arResult["Gratitudes"] = [];
// Убирает Расскажи о себе
$arResult["ProfileBlogPost"] = false;
// Убирает Интересы
$arResult["Tags"] = false;

// Если чужой профиль и стоит галка скрывать ДР
if (!$arResult["IsOwnProfile"]) {
    $arResult['FormData']['UF_DATEOFBIRTHHIDE'] = '';
    if ($arResult['FormData']['UF_DATEOFBIRTHHIDE']['VALUE']) {
        $arResult['FormData']['PERSONAL_BIRTHDAY'] = '';
    }
} elseif ($arResult["IsOwnProfile"]) {
    if ($arResult['FormData']['UF_DATEOFBIRTHHIDE']['VALUE']) {
        $arResult['FormData']['PERSONAL_BIRTHDAY'] = $arResult['FormData']['UF_PERSONAL_BIRTHDAY']['VALUE'];
    }
}

$arUser = $USER->GetByID($arResult['User']['ID'])->GetNext();
$arResult['USER_AUTH_SELECT'] = [];
if (!empty($arUser['UF_AUTH_OTHER_USER'])) {
    foreach ($arUser['UF_AUTH_OTHER_USER'] as $id) {
        $arUserAuth = CUser::GetByID($id)->Fetch();
        $userFullName = implode(' ', [$arUserAuth['LAST_NAME'], $arUserAuth['NAME'], $arUserAuth['SECOND_NAME']]);
        $arResult['USER_AUTH_SELECT'][ $id ] = $userFullName;
    }
}

if (CSite::InGroup([121]) && SITE_ID == 'gi') {
    foreach ($arResult['FormFields'] as $key => $value) {
        if ($value['name'] == 'UF_DEPARTMENT') {
            $value['data']['items'] = [];

            $arDeps = Citto\Mfc::getDepartmentNames();
            foreach ($arDeps as $id => $name) {
                $value['data']['items'][] = [
                    'NAME'  => $name,
                    'VALUE' => $id,
                ];
            }

            $value['editable'] = true;

            $arResult['FormFields'][ $key ] = $value;
        }
    }
}

/**
 * Марков Дмитрий - добавить подчинённых костылём
 * @see https://corp.tularegion.local/company/personal/user/2440/tasks/task/view/55762/
 */
if ($arResult['User']['ID'] == 3751) {
    if (!isset($arResult['User']["SUBORDINATE"])) {
        $arResult['User']["SUBORDINATE"] = [];
    }
    $arDeps = [
        1719,
        458,
        474
    ];
    $arDepsData = CIntranetUtils::GetDepartmentsData($arDeps);
    foreach ($arDeps as $depId) {
        $department = [
            'ID'    => $depId,
            'NAME'  => $arDepsData[ $depId ],
        ];

        $department['URL'] = str_replace('#ID#', $department['ID'], $arParams['PATH_TO_CONPANY_DEPARTMENT']);
        $department['EMPLOYEE_COUNT'] = 0;

        $arResult['User']['DEPARTMENTS'][ $department['ID'] ] = $department;

        $dbUsers = \CUser::GetList($o = "", $b = "", array(
            "!ID" => $arResult['User']["ID"],
            'UF_DEPARTMENT' => $depId,
            'ACTIVE' => 'Y',
            'CONFIRM_CODE' => false,
            'IS_REAL_USER' => "Y"
        ), array('FIELDS' => array("ID", "NAME", "LAST_NAME", "SECOND_NAME", "LOGIN", "WORK_POSITION", "PERSONAL_PHOTO", "PERSONAL_GENDER")));

        while ($subUser = $dbUsers->GetNext()) {
            if (intVal($subUser["PERSONAL_PHOTO"]) <= 0) {
                switch ($subUser["PERSONAL_GENDER"]) {
                    case "M":
                        $suffix = "male";
                        break;
                    case "F":
                        $suffix = "female";
                        break;
                    default:
                        $suffix = "unknown";
                }
                $subUser["PERSONAL_PHOTO"] = Bitrix\Main\Config\Option::get('socialnetwork', 'default_user_picture_'.$suffix, false, SITE_ID);
            }

            $subUser["FULL_NAME"] = \CUser::FormatName(\CSite::GetNameFormat(), $subUser, true, false);
            $subUser["PHOTO"] = '';
            if ($subUser["PERSONAL_PHOTO"] > 0) {
                $file = \CFile::GetFileArray($subUser["PERSONAL_PHOTO"]);
                if ($file !== false) {
                    $fileTmp = \CFile::ResizeImageGet(
                        $file,
                        array("width" => $size, "height" => $size),
                        BX_RESIZE_IMAGE_PROPORTIONAL,
                        false
                    );

                    $subUser["PHOTO"] = $fileTmp["src"];
                }
            }
            $subUser["LINK"] = \CComponentEngine::MakePathFromTemplate($arParams['PATH_TO_USER'], array("user_id" => $subUser["ID"]));
            $arResult['User']["SUBORDINATE"][ $subUser["ID"] ] = $subUser;
        }
    }
}

//Отчёты при аттестации (появляются в ходе запуска БП "Отчёты при аттестации")
$arResult['REPORT_FILES'] = [];
$arSelect = array("ID", "IBLOCK_CODE", "PROPERTY_EMPLOYEE", "PROPERTY_REPORT_FILE", "PROPERTY_ADD_REPORT");
$arFilter = array("IBLOCK_CODE" => "certification_report", "ACTIVE_DATE" => "Y", "ACTIVE" => "Y", "PROPERTY_END" => "Y", "PROPERTY_EMPLOYEE" => $arResult['User']['ID']);
$obReport = CIBlockElement::GetList(array(), $arFilter, false, [], $arSelect);
$arTmpFiles = [];
while ($arReport = $obReport->GetNext()) {
    $arFiles = [$arReport['PROPERTY_REPORT_FILE_VALUE'], $arReport['PROPERTY_ADD_REPORT_VALUE']];

    foreach ($arFiles as $iFileID) {
        if ($iFileID && !in_array($iFileID, $arTmpFiles)) {
            $arTmpFiles[] = $iFileID;
            $arResult['REPORT_FILES'][] = CFile::GetPath($iFileID);
        }
    }
}
unset($arTmpFiles);
