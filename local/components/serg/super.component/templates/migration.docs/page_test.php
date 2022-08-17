<?php

require $_SERVER["DOCUMENT_ROOT"] . "/local/vendor/autoload.php";

/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

use Bitrix\Main\Loader;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

Loader::includeModule("highloadblock");
Loader::includeModule("iblock");

use Bitrix\Highloadblock as HL;
use Bitrix\Main\Entity;

function addAddressComponentsIsolationTest ()
{

		$dadataKey = '202ef02ba212fda90bb83c1957d4f84c1d14aea8';

		function getArea($sApiKey, $address, $debug = false)
		{
				$arResult = [];
				$area = [];
				if ($oCurl = curl_init("http://suggestions.dadata.ru/suggestions/api/4_1/rs/suggest/address" )) {
						curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
						curl_setopt($oCurl, CURLOPT_HTTPHEADER, [
								'Content-Type: application/json',
								'Accept: application/json',
								'Authorization: Token ' . $sApiKey
						]);
						curl_setopt($oCurl, CURLOPT_POST, 1);
						curl_setopt($oCurl, CURLOPT_POSTFIELDS, json_encode(['query' => $address, 'locations' => [['region_fias_id' => 'd028ec4f-f6da-4843-ada6-b68b3e0efa3d']]]));
						$sResult = curl_exec($oCurl);
						$arResult = json_decode($sResult, true);
						if ($arResult['suggestions'][0]['data']['city_district_with_type']){
								$area['area'] = $arResult['suggestions'][0]['data']['city_district_with_type'];
								if ($arResult[0]['settlement_type_full'] == 'поселок') {
										$area['city'] = 'п. '.$arResult['suggestions'][0]['data']['settlement'];
								} elseif ($arResult[0]['settlement_type_full'] == 'село') {
										$area['city'] = 'с. '.$arResult['suggestions'][0]['data']['settlement'];
								} elseif ($arResult[0]['settlement_type_full'] == 'деревня') {
										$area['city'] = 'д. '.$arResult['suggestions'][0]['data']['settlement'];
								} else {
										$area['city'] = $arResult['suggestions'][0]['data']['city'];
								}
						} elseif ($arResult['suggestions'][0]['data']['area_with_type']) {
								$area['area'] = $arResult['suggestions'][0]['data']['area_with_type'];
								if ($arResult['suggestions'][0]['data']['settlement_type_full'] == 'поселок') {
										$area['city'] = 'п. '.$arResult['suggestions'][0]['data']['settlement'];
								} elseif ($arResult['suggestions'][0]['data']['settlement_type_full'] == 'село') {
										$area['city'] = 'с. '.$arResult['suggestions'][0]['data']['settlement'];
								} elseif ($arResult['suggestions'][0]['data']['settlement_type_full'] == 'деревня') {
										$area['city'] = 'д. '.$arResult['suggestions'][0]['data']['settlement'];
								} else {
										$area['city'] = $arResult['suggestions'][0]['data']['city'];
								}
						} elseif ($arResult['suggestions'][0]['data']['region_with_type']) {
								$area['area'] = $arResult['suggestions'][0]['data']['region_with_type'];
								if ($arResult['suggestions'][0]['data']['settlement_type_full'] == 'поселок') {
										$area['city'] = 'п. '.$arResult['suggestions'][0]['data']['settlement'];
								} elseif ($arResult['suggestions'][0]['data']['settlement_type_full'] == 'село') {
										$area['city'] = 'с. '.$arResult['suggestions'][0]['data']['settlement'];
								} elseif ($arResult['suggestions'][0]['data']['settlement_type_full'] == 'деревня') {
										$area['city'] = 'д. '.$arResult['suggestions'][0]['data']['settlement'];
								} else {
										$area['city'] = $arResult['suggestions'][0]['data']['city'];
								}
						} else {
								$area['area'] = 'нет района';
								$area['city'] = 'нет города';
						}

						curl_close($oCurl);
				}

				if ($debug) {
						return ['area' => $area, 'rs' => $arResult];
				} else {
						return $area;
				}
		}

		function addAddressComponentsTest($arProperties, $enumCode, $iblockID, $kind, $dadataKey) {

				$arAreaComponents = [];

				Loader::IncludeModule('iblock');
				$logger = new Logger('ADD ADDRESS COMPONENTS');
				$logger->pushHandler(new StreamHandler($_SERVER['DOCUMENT_ROOT'] . '/local/logs/isolation_contact_table_address.log', Logger::INFO));

				$params = array(
						"max_len" => "100", // обрезает символьный код до 100 символов
						"change_case" => "L", // буквы преобразуются к нижнему регистру
						"replace_space" => "_", // меняем пробелы на нижнее подчеркивание
						"replace_other" => "_", // меняем левые символы на нижнее подчеркивание
						"delete_repeat_replace" => "true", // удаляем повторяющиеся нижние подчеркивания
						"use_google" => "false", // отключаем использование google
				);


				$arArea = getArea($dadataKey, $arProperties['ATT_ADDRESS']);

//				pre(['area' =>$arArea, 'key' => $dadataKey, 'address' => $arProperties['ATT_ADDRESS']]);

				$logger->info('Добавление адресных компонентов: ', $arArea);

				foreach ($enumCode as $key => $enum) {


						$propArea = \CIBlockProperty::GetList(
								[],
								[
										'IBLOCK_ID' => $iblockID,
										'CODE' => $enum
								])->Fetch();


						$property_enums = CIBlockPropertyEnum::GetList(Array("DEF"=>"DESC", "SORT"=>"ASC"), Array("IBLOCK_ID"=>$iblockID, "CODE"=>$enum, "VALUE" => $arArea[$kind[$key]]));
						if ($property_enums->SelectedRowsCount() > 0) {

								while($enum_fields = $property_enums->GetNext())
								{
										$arAreaComponents[$enum] = $enum_fields['ID'];
										$logger->info('Найден: ' .$enum. ' с ID: '.$enum_fields['ID']);
								}

						} else {
								$rsEnum = new \CIBlockPropertyEnum();
								$valueId = $rsEnum->Add([
										'PROPERTY_ID' => $propArea['ID'],
										'VALUE' => $arArea[$kind[$key]],
								]);

								$arAreaComponents[$enum] = $valueId;
								$logger->info('Добавлен: ' .$enum. ' с ID: '.$valueId);
						}
				}

				return $arAreaComponents;
		}

		$logger = new Logger('UPDATE ADDRESS COMPONENTS ELEMENT');
		$logger->pushHandler(new StreamHandler($_SERVER['DOCUMENT_ROOT'] . '/local/logs/isolation_contact_table_update_address.log', Logger::INFO));

		$arSelect = array(
				"ID",
				"IBLOCK_ID",
				"PROPERTY_ATT_AREA",
				"PROPERTY_ATT_ADDRESS",
				"PROPERTY_ATT_CITY",
		);
		$arFilter = Array(
				"IBLOCK_ID"=>IBLOCK_ID_MIGRATION_DOCS,
				"ACTIVE_DATE"=>"Y",
				"ACTIVE"=>"Y",
				"IBLOCK_SECTION_ID" => SECTION_ID_MIGRATION_CONT,
				"PROPERTY_ATT_AREA" => 2304,
				"PROPERTY_ATT_CITY" => 2305,
				"!PROPERTY_ATT_ADDRESS" => false
		);

		$res = CIBlockElement::GetList(Array('id' => 'asc'), $arFilter, false, ['nPageSize' => 1, 'nTopCount' => 20], $arSelect);
		pre($res->SelectedRowsCount());
		while($arFields = $res->GetNext()) {
				pre(['id' => $arFields['ID'], 'address' => $arFields['PROPERTY_ATT_ADDRESS_VALUE']]);


				$arProps['ATT_ADDRESS'] = $arFields['PROPERTY_ATT_ADDRESS_VALUE'];
				$newProps = [];


				$newProps = addAddressComponentsTest($arProps, ['ATT_AREA', 'ATT_CITY'], IBLOCK_ID_MIGRATION_DOCS, ['area', 'city'], '202ef02ba212fda90bb83c1957d4f84c1d14aea8');

				pre($newProps);

				if ($newProps['ATT_AREA'] != '' && $newProps['ATT_CITY'] != '') {
						CIBlockElement::SetPropertyValuesEx($arFields['ID'],IBLOCK_ID_MIGRATION_DOCS , array('ATT_AREA' => $newProps['ATT_AREA']));
						CIBlockElement::SetPropertyValuesEx($arFields['ID'],IBLOCK_ID_MIGRATION_DOCS , array('ATT_CITY' => $newProps['ATT_CITY']));
				}



				$logger->info('Обновлен элемент с ID: '.$arFields['ID'], $newProps);

		}


}

addAddressComponentsIsolationTest();
