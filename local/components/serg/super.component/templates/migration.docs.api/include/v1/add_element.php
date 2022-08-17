<?php

if (isset($_REQUEST['token']) && isset($_REQUEST['type'])) {
    if ($_REQUEST['token'] == md5('Ghjdthrfyfrfhfynby')) {
        function getEnumID($name, $code)
        {
            $enumID = null;
            $enumProps = CIBlockPropertyEnum::GetList(array("DEF"=>"DESC", "SORT"=>"ASC"), array("IBLOCK_ID"=>IBLOCK_ID_MIGRATION_DOCS, "CODE"=>$code));
            while ($enumFields = $enumProps->GetNext()) {
                if (htmlspecialchars_decode($enumFields['VALUE']) == trim($name)) {
                    $enumID = $enumFields['ID'];
                }
            }
            return $enumID;
        }

        $strIncrementDays = ' + 14 day';

        $arSections = [
            'rus_isolation_contacts' => SECTION_ID_MIGRATION_CONT,
            'rus_isolation' => SECTION_ID_MIGRATION_DOCS_RF,
            'mzh_isolation' => SECTION_ID_MIGRATION_DOCS_MZH,
            'mp_isolation' => SECTION_ID_MIGRATION_DOCS_MP,
            'coming_isolation' => SECTION_ID_MIGRATION_DOCS_COMING,
        ];

        $arParamsRequest = [
            'coming_isolation' => [
                "NAME" => $_REQUEST['last_name'] ?? false,
                "ATT_NAME" => $_REQUEST['first_second_name'] ?? false,
                "ATT_SEX" => $_REQUEST['sex'],
                "ATT_DATE_SVEDENO" => $_REQUEST['birthday'] ?? false,
                "ATT_DATE_PERESECHENIYA" => $_REQUEST['date_peresecheniya'],
                "ATT_COUNTRY" => $_REQUEST['country'],
                "ATT_PASSPORT" => $_REQUEST['passport'],
                "ATT_PHONE" => $_REQUEST['phone'] ?? false,
                "ATT_ADDRESS" => $_REQUEST['address'],
                "ATT_GUZ_NAV"  => $_REQUEST['guz_observe_enum_id'] ?? getEnumID($_REQUEST['guz_observe_name'], 'ATT_GUZ_NAV'),
                "ATT_GUZ_HOSP" => $_REQUEST['guz_hospital_enum_id'] ?? getEnumID($_REQUEST['guz_hospital_name'], 'ATT_GUZ_HOSP'),
                "ATT_LEGAL_REPRES" => $_REQUEST['legal_repres'],
                "ATT_LEGAL_REPRES_DATE" => $_REQUEST['legal_repres_date'],
                "ATT_LAST_DATE" => date("d.m.Y", strtotime(trim($_REQUEST['date_peresecheniya']).$strIncrementDays)),
                "ATT_ADDRESS_REPRES" => $_REQUEST['legal_repres_address'],
                "ATT_DATE_RESOLUTION" => $_REQUEST['date_resolution'],
                "ATT_RESOLUTION" => $_REQUEST['date_resolution'] ? 'Y': 'N',
            ]
        ];

        if (array_key_exists($_REQUEST['type'], $arParamsRequest)) {
            $strSearchLastName = $strSearchFirstSecondName = $strSearchBirthday = false;

            if ($arParamsRequest[$_REQUEST['type']]['NAME']) {
                $strSearchLastName = '%'.$arParamsRequest[$_REQUEST['type']]['NAME'].'%';
            }
            if ($arParamsRequest[$_REQUEST['type']]['ATT_NAME']) {
                $strSearchFirstSecondName = '%'.$arParamsRequest[$_REQUEST['type']]['ATT_NAME'].'%';
            }
            if ($arParamsRequest[$_REQUEST['type']]['ATT_DATE_SVEDENO']) {
                $strSearchBirthday = date("Y-m-d", strtotime($arParamsRequest[$_REQUEST['type']]['ATT_DATE_SVEDENO']));
            }
            $strSearchPhone = $arParamsRequest[$_REQUEST['type']]['ATT_PHONE'] ?? false;

            debug([
                'Параметры запроса' => $_REQUEST,
                'Параметры для поиска совпадений' => [
                    "NAME" => $strSearchLastName,
                    "PROPERTY_ATT_NAME" => $strSearchFirstSecondName,
                    "PROPERTY_ATT_PHONE" => $strSearchPhone,
                    "PROPERTY_ATT_DATE_SVEDENO" => $strSearchBirthday
                ]
            ]);

            $arSelect = array("ID", "IBLOCK_ID", "NAME", "IBLOCK_SECTION_ID", "PROPERTY_ATT_PHONE", "PROPERTY_ATT_DATE_PERESECHENIYA", "PROPERTY_ATT_DATE_QUARANT");
            $arFilter = array(
                "IBLOCK_ID"=>IBLOCK_ID_MIGRATION_DOCS,
                "ACTIVE_DATE"=>"Y",
                "ACTIVE"=>"Y",
                [
                    "LOGIC" => "OR",
                    [
                        "NAME" => $strSearchLastName,
                        "PROPERTY_ATT_NAME" => $strSearchFirstSecondName,
                        "PROPERTY_ATT_PHONE" => $strSearchPhone
                    ],
                    [
                        "NAME" => $strSearchLastName,
                        "PROPERTY_ATT_NAME" => $strSearchFirstSecondName,
                        "PROPERTY_ATT_DATE_SVEDENO" => $strSearchBirthday
                    ],
                ],
            );


            $user_id = false;


            $res = CIBlockElement::GetList(array('sort' => 'asc'), $arFilter, false, false, $arSelect);
            while ($arFields = $res->GetNext()) {
                debug($arFields);

                if ($arFields['ID']) {
                    $user_id = $arFields['ID'];
                }
                $datePeresecheniya = $arFields['PROPERTY_ATT_DATE_PERESECHENIYA_VALUE'];
            }

            $name = array_shift($arParamsRequest[$_REQUEST['type']]);
            $arLoadProperties = $arParamsRequest[$_REQUEST['type']];

            if ($name) {
                $el = new CIBlockElement();

                $arLoadArray = array(
                    "MODIFIED_BY"    => $USER->GetID(),
                    "IBLOCK_SECTION_ID" => $arSections[$_REQUEST['type']],
                    "IBLOCK_ID"      => IBLOCK_ID_MIGRATION_DOCS,
                    "PROPERTY_VALUES"=> $arLoadProperties,
                    "NAME"           => $name,
                    "ACTIVE"         => "Y",
                );

                if (!$user_id) {
                    if ($ID = $el->Add($arLoadArray)) {
                        $arResult['COMING']['message'] = "New ID: ".$ID;
                    } else {
                        $arResult['COMING']['message'] = "Error: ".$el->LAST_ERROR;
                    }
                } else {
                    $arLoadArray['PROPERTY_VALUES']['ATT_DATE_PERESECHENIYA'] = $datePeresecheniya;

                    if ($res = $el->Update($user_id, $arLoadArray)) {
                        $arResult['COMING']['message'] = "Update element with ID: ".$user_id;
                    }
                }
            }
        } else {
            $arResult['COMING'] = 'invalid section code';
        }
    } else {
        $arResult['COMING'] = 'token_error';
    }
}

echo json_encode($arResult['COMING']);
