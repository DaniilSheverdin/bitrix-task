<?php

class DaDataHelper
{
		/**
		 * @param string $sApiKey API Key сервиса DaData.ru
		 * @param string $sType Тип запроса address|bank и другие
		 * @param array $arFields
		 * @return array|mixed
		 * @link https://dadata.ru/suggestions/usage/#bank
		 */
		public static function dadata($sApiKey, $sType, $arFields)
		{
				$arResult = [];
				if ($oCurl = curl_init("http://suggestions.dadata.ru/suggestions/api/4_1/rs/suggest/" . $sType)) {
						curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
						curl_setopt($oCurl, CURLOPT_HTTPHEADER, [
								'Content-Type: application/json',
								'Accept: application/json',
								'Authorization: Token ' . $sApiKey
						]);
						curl_setopt($oCurl, CURLOPT_POST, 1);
						curl_setopt($oCurl, CURLOPT_POSTFIELDS, json_encode($arFields));
						$sResult = curl_exec($oCurl);
						$arResult = json_decode($sResult, true);
						curl_close($oCurl);
				}

				return $arResult;
		}

		/**
		 * @param $sApiKey
		 * @param $address
		 * @param bool $debug
		 * @return array
		 */
		public static function getArea($sApiKey, $address, $debug = false)
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
}
