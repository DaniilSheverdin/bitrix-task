<?php


if (isset($_REQUEST['token'])) {
		if ($_REQUEST['token'] == md5('Ghjdthrfyfrfhfynby')) {
				$strIncrementDays = ' + 14 day';
				$strLastName = $strPropertyName = $strSearchFio = $strBirthday = $phone = '';

				if (isset($_REQUEST['fio'])) {
						$arFIO = explode(' ', $_REQUEST['fio']);
						$strLastName = '%'.trim($arFIO[0]).'%';

						if (stristr($_REQUEST['fio'], '.')) {
								$strPropertyName = '%'.trim(explode(' ', $_REQUEST['fio'])[1]).'%';
						} else {
								$strFirstName = trim($arFIO[1]);
								$strSecondName = trim($arFIO[2]);
								$strPropertyName = '%'.$strFirstName . ' ' . $strSecondName.'%';
						}
						$strSearchFio = '%'.$_REQUEST['fio'].'%';
				}

				if (isset($_REQUEST['birthday'])) {
						$strBirthday = $_REQUEST['birthday'];
				}
				if (isset($_REQUEST['phone'])) {
						$phone = $_REQUEST['phone'];
				}

				debug([
						'Параметры запроса' => $_REQUEST,
						'Обработанные данные для поиска' => [
								'фамилия' => $strLastName,
								'именные компоненты' => $strPropertyName,
								'фио' => $strSearchFio,
								'номер телефона' => $phone
						]
				]);

				if (isset($_REQUEST['birthday']) && isset($_REQUEST['fio'])) {


						$arSelect = array("ID", "IBLOCK_ID", "NAME", "IBLOCK_SECTION_ID", "PROPERTY_ATT_PHONE", "PROPERTY_ATT_DATE_PERESECHENIYA", "PROPERTY_ATT_DATE_QUARANT");
						$arFilter = array(
								"IBLOCK_ID" => IBLOCK_ID_MIGRATION_DOCS,
								"ACTIVE_DATE" => "Y",
								"ACTIVE" => "Y",
								"IBLOCK_SECTION_ID" => SECTION_ID_MIGRATION_CONT,
								"NAME" => $_REQUEST['fio'],
								"PROPERTY_ATT_DATE_BIRTHDAY" => date("Y-m-d", strtotime($strBirthday))
						);

						$res = CIBlockElement::GetList(array('sort' => 'asc'), $arFilter, false, false, $arSelect);
						while ($arFields = $res->GetNext()) {
								debug($arFields);

								if ($arFields['PROPERTY_ATT_DATE_PERESECHENIYA_VALUE']) {
										$strStartDate = $arFields['PROPERTY_ATT_DATE_PERESECHENIYA_VALUE'];
								}
								if ($arFields['PROPERTY_ATT_DATE_QUARANT_VALUE']) {
										$strStartDate = $arFields['PROPERTY_ATT_DATE_QUARANT_VALUE'];
								}
								if ($arFields['IBLOCK_SECTION_ID'] == SECTION_ID_MIGRATION_DOCS_COMING) {
										$strStartDate = date('d.m.Y', strtotime($strStartDate . ' + 1 day'));
								}

								if ($arFields['IBLOCK_SECTION_ID'] == SECTION_ID_MIGRATION_CONT) {
										$strIncrementDays = ' + 13 day';
								}

								$strEndDate = date('d.m.Y', strtotime($strStartDate . $strIncrementDays));
						}

						if ($strStartDate && $strEndDate) {
								$dateNow = date('d.m.Y', strtotime('now'));
								$rsCompareDates = $DB->CompareDates($strEndDate, $dateNow);

								$arResult['ISOLATION_DATA'] = [
										'quarantine_start' => $strStartDate,
										'quarantine_end' => $strEndDate,
										'message' => 'user_found'
								];

								if ($rsCompareDates != -1) {
										$arResult['ISOLATION_DATA']['quarantine'] = 'true';
								} else {
										$arResult['ISOLATION_DATA']['quarantine'] = 'false';
								}
						} else {
								$arResult['ISOLATION_DATA']['quarantine'] = 'false';
								$arResult['ISOLATION_DATA']['message'] = 'user_not_found';
						}
				} else {
						$arResult['ISOLATION_DATA']['quarantine'] = 'false';
						$arResult['ISOLATION_DATA']['message'] = 'user_not_found';
				}
		} else {
				$arResult['ISOLATION_DATA'] = 'token_error';
		}
}

echo json_encode($arResult['ISOLATION_DATA']);
