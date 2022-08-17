<?

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) {
    die();
}

$obCacheTrade = new CPHPCache();
$strCacheID = 'CACHE_OPINIONS_MAIN';
if ($obCacheTrade->InitCache($arParams['CACHE_TIME'], $strCacheID, '/')) {
    $arResult = $obCacheTrade->GetVars();
} else {
    $obCacheTrade->StartDataCache();

    $arAvailablePages = array('INDEX');
    $strCurPage = $_REQUEST['PAGE'];
    if (!in_array($strCurPage, $arAvailablePages)) {
        $strCurPage = 'INDEX';
    }
    $arResult['CUR_PAGE'] = $strCurPage;
    $arResult['INCLUDE_FILE'] = 'page_' . mb_strtolower($strCurPage) . '.php';

    CModule::IncludeModule("iblock");

    $arSelect = array(
        "ID",
        "IBLOCK_ID",
        "NAME",
        "DATE_ACTIVE_FROM",
        "PROPERTY_ATT_DEPARTMENT",
        "PROPERTY_ATT_USER",
        "PROPERTY_ATT_CREATED",
        "PROPERTY_ATT_COMPLETED",
        "PROPERTY_ATT_DEFECTION",
        "PROPERTY_ATT_PERCENT",
    );
    $arFilter = array("IBLOCK_ID"=>IBLOCK_ID_OMNI_TRACKER, "ACTIVE_DATE"=>"Y", "ACTIVE"=>"Y");
    $rsStatistic = CIBlockElement::GetList(array('PROPERTY_SORTEDLIST' => 'asc'), $arFilter, false, false, $arSelect);
    while ($arFields = $rsStatistic->GetNext()) {
        $rsDepartment = CIBlockSection::GetByID($arFields['PROPERTY_ATT_DEPARTMENT_VALUE']);
        if ($arDepartment = $rsDepartment->GetNext()) {
            $arResult['USERS_DATA'][$arFields['ID']]['DEPARTMENT'] =  $arDepartment['NAME'];
        }

        $rsUser = CUser::GetByID($arFields['PROPERTY_ATT_USER_VALUE']);
        $arUser = $rsUser->Fetch();
        $arResult['USERS_DATA'][$arFields['ID']]['USER']['ID'] =  $arFields['PROPERTY_ATT_USER_VALUE'];
        $arResult['USERS_DATA'][$arFields['ID']]['USER']['FULL_NAME'] =  $arUser['LAST_NAME'].' '.$arUser['NAME'].' '.$arUser['SECOND_NAME'];
        $arResult['USERS_DATA'][$arFields['ID']]['CREATED'] =  $arFields['PROPERTY_ATT_CREATED_VALUE'];
        $arResult['USERS_DATA'][$arFields['ID']]['COMPLETED'] =  $arFields['PROPERTY_ATT_COMPLETED_VALUE'];
        $arResult['USERS_DATA'][$arFields['ID']]['DEFECTION'] =  $arFields['PROPERTY_ATT_DEFECTION_VALUE'];
        $arResult['USERS_DATA'][$arFields['ID']]['PERCENT'] =  $arFields['PROPERTY_ATT_PERCENT_VALUE'];
    }

    $arFilterUsers = ['GROUPS_ID' => [GROUP_ID_CIT_EMPLOYEES]];
    $arParamsUsers = [
        'SELECT' => ['UF_*'],
        'FIELDS' => ['ID', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'LOGIN']
    ];

    /** @noinspection PhpPassByRefInspection */
    $rsUsers = CUser::GetList(($by = "NAME"), ($order = "asc"), $arFilterUsers, $arParamsUsers);
    while ($arUser = $rsUsers->Fetch()) {
        $arResult['USERS'][$arUser['ID']]['FULL_NAME'] = $arUser['LAST_NAME'].' '.$arUser['NAME'].' '.$arUser['SECOND_NAME'];

        foreach ($arUser['UF_DEPARTMENT'] as $departmentID) {
            $rsDepartment = CIBlockSection::GetByID($departmentID);
            if ($arDepartment = $rsDepartment->GetNext()) {
                $arResult['USERS'][$arUser['ID']]['DEPARTMENTS'][$arDepartment['ID']] = $arDepartment['NAME'];
            }
        }
    }


    $arResult["__TEMPLATE_FOLDER"] = $this->__folder;
    $obCacheTrade->EndDataCache($arResult);
}

$this->__component->arResult = $arResult;
