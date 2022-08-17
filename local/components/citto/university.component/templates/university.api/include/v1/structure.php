<?php

use Bitrix\Main\UserTable, CIntranetUtils;

if (isset($_REQUEST['token'])) {
    if ($_REQUEST['token'] == md5('Ghjdthrfyfrfhfynby')) {
        $arDepartments = [];
        $arUsers = [];
        $arExcludesDepartments = CIntranetUtils::GetIBlockSectionChildren($arParams["EXCLUDED_SECTIONS"]);
        $obDepartments = CIBlockSection::GetList([], ["IBLOCK_ID" => (int)$arParams["IBLOCK_ID"], 'ACTIVE' => 'Y', '>DEPTH_LEVEL' => 1, ''], false, ['ID', 'NAME', 'IBLOCK_SECTION_ID', 'DEPTH_LEVEL', 'UF_PODVED']);

        function recDepartment($iID, $arDepartments)
        {
            $iParentID = $arDepartments[$iID]['PARENT_ID'];
            $iDepth = $arDepartments[$iID]['DEPTH'];

            if ($iDepth == 4) {
                return $arDepartments[$iID];
            } else if ($iDepth > 4) {
                return recDepartment($iParentID, $arDepartments);
            } else {
                return $arDepartments[$iID];
            }
        }

        while ($arDep = $obDepartments->GetNext()) {
            $iID = $arDep['ID'];
            if (!in_array($iID, $arExcludesDepartments)) {
                $sName = $arDep['NAME'];
                $bPodved = ($arDep['UF_PODVED'] == 1) ? true : false;
                $arDepartments[$iID] = [
                    'NAME' => $sName,
                    'PODVED' => $bPodved,
                    'DEPTH' => (int )$arDep['DEPTH_LEVEL'],
                    'PARENT_ID' => $arDep['IBLOCK_SECTION_ID']
                ];
            }
        }

        foreach ($arDepartments as $iDepID => $arDepartment) {
            if ($arDepartment['DEPTH'] > 4) {
                $arRecDepartment = recDepartment($iDepID, $arDepartments);
                $arDepartments[$iDepID]['NAME'] = $arRecDepartment['NAME'];
                $arDepartments[$iDepID]['PODVED'] = $arRecDepartment['PODVED'];
            } else if ($arDepartment['DEPTH'] == 4 && !$arDepartment['PODVED'])  {
                $iParentID = $arDepartment['PARENT_ID'];
                $arDepartments[$iDepID]['NAME'] = $arDepartments[$iParentID]['NAME'];
            }

            unset($arDepartments[$iDepID]['PARENT_ID'], $arDepartments[$iDepID]['DEPTH']);
        }

        $obUsers = UserTable::getList([
            'select' => ['ID', 'ACTIVE', 'LOGIN', 'XML_ID', 'UF_SID', 'EMAIL', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'UF_DEPARTMENT', 'UF_WORK_POSITION', 'UF_GOV_EMPLOYEE'],
        ]);

        while ($arUser = $obUsers->fetch()) {
            $iDepartment = current($arUser['UF_DEPARTMENT']);
            if (!empty($arDepartments[$iDepartment])) {
                $sSID = (!empty($arUser['UF_SID'])) ? $arUser['UF_SID'] : $arUser['XML_ID'];
                $arUsers[$arUser['ID']] = [
                    'ID' => $arUser ['ID'],
                    'LOGIN' => $arUser ['LOGIN'],
                    'SID' => $sSID,
                    'EMAIL' => $arUser ['EMAIL'],
                    'NAME' => $arUser ['NAME'],
                    'LAST_NAME' => $arUser ['LAST_NAME'],
                    'SECOND_NAME' => $arUser ['SECOND_NAME'],
                    'DEPARTMENT' => [
                        'NAME' => $arDepartments[$iDepartment]['NAME'],
                        'PODVED' => ($arDepartments[$iDepartment]['PODVED']) ? 'Y' : 'N',
                    ],
                    'POSITION' => $arUser ['UF_WORK_POSITION'],
                    'GOVERMENT' => (empty($arUser['UF_GOV_EMPLOYEE'])) ? 'N' : 'Y',
                    'ACTIVE' => $arUser['ACTIVE']
                ];
            }
        }
        $arResult['RESULT'] = $arUsers;
    } else {
        $arResult['RESULT'] = 'token_error';
    }
} else $arResult['RESULT'] = 'not_token';

echo json_encode($arResult);
