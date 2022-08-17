<?

require $_SERVER["DOCUMENT_ROOT"] . "/local/vendor/autoload.php";
require $_SERVER['DOCUMENT_ROOT']. '/local/components/serg/super.component/templates/migration.docs/lib/DadataHelper.php';

/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

use Bitrix\Main\Grid\Options as GridOptions;
use Bitrix\Main\UI\PageNavigation;
use Bitrix\Main\Grid\Panel\DefaultValue;
use Bitrix\Main\Grid\Panel\Snippet\Button;
use Bitrix\Main\Grid\Panel\Snippet\Onchange;
use Bitrix\Main\Grid\Panel\Actions;
use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\Highloadblock as HL;
use Bitrix\Main\Entity;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;


if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) {
    die();
}


$obCache_trade = new CPHPCache();
$CACHE_ID = 'CACHE_OPINIONS_MAIN';
if ($obCache_trade->InitCache($arParams['CACHE_TIME'], $CACHE_ID, '/')) {
    $arResult = $obCache_trade->GetVars();
} else {
    $obCache_trade->StartDataCache();
    Loader::IncludeModule('iblock');
    Loader::includeModule("highloadblock");
    $AVIABLE_PAGES = array(
        'index',
        'RF_TABLE',
        'ADD',
        'MZH_TABLE',
        'MZH_ADD',
        'MP_TABLE',
        'MP_ADD',
        'CONT_TABLE',
        'COMING_TABLE',
        'TEST',
        'VIOLATORS_TABLE',
        'VIOLATORS_CCMIS_TABLE',
        'ARRIVED_TABLE',
        'UPDATE_ADDRESS_COMPONENTS',
        );
    $CUR_PAGE = $_REQUEST['PAGE'];
    $QUARANTINE=" +14 day";
    $QUARANTINE_CONTACT=" +13 day";
    if (!in_array($CUR_PAGE, $AVIABLE_PAGES)) {
        $CUR_PAGE = 'index';
    }
    $arResult['CUR_PAGE'] = $CUR_PAGE;
    $arResult['INCLUDE_FILE'] = 'page_' . mb_strtolower($CUR_PAGE) . '.php';

    $arResult['SECTIONS'] = [
        'RF' => SECTION_ID_MIGRATION_DOCS_RF,
        'MZH' => SECTION_ID_MIGRATION_DOCS_MZH,
        'MP' => SECTION_ID_MIGRATION_DOCS_MP,
        'COMING' => SECTION_ID_MIGRATION_DOCS_COMING,
        'ARRIVED' => SECTION_ID_MIGRATION_DOCS_ARRIVED,
        'CONT' => SECTION_ID_MIGRATION_CONT,
    ];

    $DADATA_KEY = '202ef02ba212fda90bb83c1957d4f84c1d14aea8';


    $arResult['PROPERTY_LIST'] = [
        'RF' => [
            'ATT_NAME',
            'ATT_SEX',
            'ATT_DATE_SVEDENO',
            'ATT_DATE_PERESECHENIYA',
            'ATT_COUNTRY',
            'ATT_PHONE',
            'ATT_PASSPORT',
            'ATT_ADDRESS',
            'ATT_LEGAL_REPRES',
            'ATT_LEGAL_REPRES_DATE',
            'ATT_ADDRESS_REPRES',
        ],
        'MZH' => [
            'ATT_DATE_BIRTHDAY',
            'ATT_COUNTRY',
            'ATT_PHONE',
            'ATT_PASSPORT',
            'ATT_DATE_PERESECHENIYA',
            'ATT_DATE_REG_START',
            'ATT_DATE_REG_END',
            'ATT_ADDRESS',
            'ATT_LEGAL_REPRES',
            'ATT_LEGAL_REPRES_DATE',
            'ATT_ADDRESS_REPRES',
        ],
        'MP' => [
            'ATT_FIO_LAT',
            'ATT_DATE_BIRTHDAY',
            'ATT_SEX',
            'ATT_COUNTRY',
            'ATT_PHONE',
            'ATT_PASSPORT',
            'ATT_DATE_PERESECHENIYA',
            'ATT_DATE_REG_START',
            'ATT_DATE_REG_END',
            'ATT_INCOMING_SIDE',
            'ATT_ADDRESS',
            'ATT_LEGAL_REPRES',
            'ATT_LEGAL_REPRES_DATE',
            'ATT_ADDRESS_REPRES',
        ],
        'COMING' => [
            'ATT_NAME',
            'ATT_SEX',
            'ATT_DATE_SVEDENO',
            'ATT_DATE_PERESECHENIYA',
            'ATT_COUNTRY',
            'ATT_PHONE',
            'ATT_PASSPORT',
            'ATT_ADDRESS',
            'ATT_LEGAL_REPRES',
            'ATT_LEGAL_REPRES_DATE',
            'ATT_ADDRESS_REPRES',
        ],
        'VIOLATORS' => [
            'UF_FIO',
            'UF_ADDRESS',
            'UF_PHONE',
            'UF_DATE_BIRTHDAY',
            'UF_DATE_VIOLATION',
            'UF_ADDRESS_VIOLATION',
            'UF_COORDINATES',
            'UF_COORDINATES_FIRST_NIGHT',
            'UF_DATA_TYPE',
        ],
		    'ARRIVED' => [
				    'ATT_NAME',
				    'ATT_DATE_BIRTHDAY',
				    'ATT_CITIZENSHIP',
				    'ATT_ARRIVED_COUNTRY',
				    'ATT_ARRIVED_DATE',
				    'ATT_PHONE',
				    'ATT_ADDRESS',
				    'ATT_PARENT_FIO',
				    'ATT_TOWN',
				    'ATT_INFO_KIND',
				    'ATT_TEST_DATE',
				    'ATT_TEST_RESULT',
				    'ATT_LEG_SERVICE_DATE',
				    'ATT_UVD_DATE',
				    'ATT_SMS_SEND',
				    'ATT_OWNER_INFO',
				    'ATT_KIND_INFO',
				    'ATT_UVD_RESULTS',
				    'ATT_PASSPORT_DATA',
				    'ATT_NOTE',
		    ],
		    'CONT' => [
				    "ATT_EPID",
				    "ATT_STEP",
				    "ATT_DATE_BIRTHDAY",
				    "ATT_ADDRESS",
				    "ATT_PHONE",
				    "ATT_WORK",
				    "ATT_POSITION",
				    "ATT_LEGAL_REPRES",
				    "ATT_ADDRESS_REPRES",
				    "ATT_DATE_PERESECHENIYA",
				    "ATT_PLACE_CONTACT",
				    "ATT_DATE_QUARANT",
				    "ATT_INCOMING_SIDE",
				    "ATT_DATE_RESOLUTION",
				    "ATT_RESOLUTION" ,
				    "ATT_SNILS",
				    "ATT_NOTIFY_DATE",
				    "ATT_NOTIFY_WAY",
				    "ATT_DATE_ADD_DATA",
				    "ATT_VAC_1_DATE",
				    "ATT_VAC_2_DATE",
				    "ATT_ILLNESS_DATE",
		    ],

    ];

    $arMonth = [
        'декабря',
        'января',
        'февраля',
        'марта',
        'апреля',
        'мая',
        'июня',
        'июля',
        'августа',
        'сентября',
        'октября',
        'ноября',
    ];

    function formatDateFromXLS($dateWithSlash)
    {

        if (stristr($dateWithSlash, '/') == true) {
            $strDate = explode(' ', $dateWithSlash)[0];
            $date = date_create_from_format('j/m/y', $strDate);
            return date_format($date, 'd.m.Y');
        } else {

        		if (stristr($dateWithSlash, '.') == false) {
				        $strFormatDateWithDots = '';
				        for ($i = 0; $i < mb_strlen($dateWithSlash); $i++) {
						        $strFormatDateWithDots .= $dateWithSlash[$i];
						        if ($i == 1 || $i == 3) {
								        $strFormatDateWithDots .= '.';
						        }
				        }
				        return date('d.m.Y', strtotime($strFormatDateWithDots));
		        } else {
				        return date('d.m.Y', strtotime($dateWithSlash));
		        }
        }
    }

		function addAddressComponents(&$arProperties, $enumCode, $iblockID, $kind, $dadataKey) {

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


				$arArea = DaDataHelper::getArea($dadataKey, $arProperties['ATT_ADDRESS']);

				$logger->info('Добавление адресных компонентов: ', $arArea);

				foreach ($enumCode as $key => $enum) {

						$arProperties[$enum] = false;

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
										$arProperties[$enum] = $enum_fields['ID'];
										$logger->info('Найден: ' .$enum. ' с ID: '.$enum_fields['ID']);
								}

						} else {
								$rsEnum = new \CIBlockPropertyEnum();
								$valueId = $rsEnum->Add([
										'PROPERTY_ID' => $propArea['ID'],
										'VALUE' => $arArea[$kind[$key]],
										'XML_ID' => CUtil::translit(strtolower($arArea[$kind[$key]]), "ru", $params)
								]);

								$arProperties[$enum] = $valueId;
								$logger->info('Добавлен: ' .$enum. ' с ID: '.$valueId);
						}
				}
		}

    function updateElement($section, $updateAddressComponents = false)
    {
        global $USER;
        $oEl = new CIBlockElement();
        foreach ($_POST["FIELDS"] as $key => $value) {

            if ($key == 0) {
                $name = array_shift($value);
                $arLoadProperties = $value;

                $oEl = new CIBlockElement;

                $arLoadArray = array(
                    "MODIFIED_BY"    => $USER->GetID(),
                    "IBLOCK_SECTION_ID" => $section,
                    "IBLOCK_ID"      => IBLOCK_ID_MIGRATION_DOCS,
                    "PROPERTY_VALUES"=> $arLoadProperties,
                    "NAME"           => $name,
                    "ACTIVE"         => "Y",
                );

                if ($ID = $oEl->Add($arLoadArray)) {
                }
            } else {



		            $logger = new Logger('UPDATE ELEMENT');
                $arProps = array();
                foreach ($value as $k => $val) {
                    if ($k == 'ATT_PHONE') {
                        $val = str_replace(' ', '', $val);
                    }

                    $arProps[$k] = $val;
                }

		            if ($updateAddressComponents) {
				            addAddressComponents($arProps, ['ATT_AREA', 'ATT_CITY'], IBLOCK_ID_MIGRATION_DOCS, ['area', 'city'], '202ef02ba212fda90bb83c1957d4f84c1d14aea8');

				            $md5Sum = md5(implode(';', $arProps));
				            $arProps['ATT_CHECK_SUM'] = $md5Sum;
		            }


		            if ($arProps['NAME']) {
				            $arLoadArray = array(
						            "MODIFIED_BY"    => $USER->GetID(),
						            "IBLOCK_SECTION" => $section,
						            "PROPERTY_VALUES"=> $arProps,
						            "NAME"           => $arProps['NAME'],
						            "ACTIVE"         => "Y",
				            );

				            $strUserFullName = $USER->GetFullName();

				            $logger->pushHandler(new StreamHandler($_SERVER['DOCUMENT_ROOT'] . '/local/logs/isolation_contact_table.log', Logger::INFO));

				            if ($oEl->Update($key, $arLoadArray)) {
						            if ($section == SECTION_ID_MIGRATION_CONT) {

								            $logger->info('Обновлен элемент с ID: '.$key, array('user' => $strUserFullName, 'new_params' => $arLoadArray));
						            }
				            } else {
						            $logger->error("Ошибка: ".$oEl->LAST_ERROR);
				            }
		            } else {
				            $logger->pushHandler(new StreamHandler($_SERVER['DOCUMENT_ROOT'] . '/local/logs/isolation_arrived_table.log', Logger::INFO));
		            		$intElementID = intval($key);
				            CIBlockElement::SetPropertyValuesEx($intElementID, IBLOCK_ID_MIGRATION_DOCS, $arProps);
				            $logger->info('Обновлен элемент с ID: '.$intElementID, array('new_params' => $arProps));
		            }

            }
        }
    }

    function updateElementHLB()
    {
        foreach ($_POST["FIELDS"] as $key => $value) {
            $arProps = array();
            foreach ($value as $k => $val) {
                if ($k == 'UF_PHONE') {
                    $val = str_replace(' ', '', $val);
                }
                if ($k == 'LAST_NAME') {
                    $lastName = $val;
                }
                if ($k == 'FIRST_SECOND_NAME') {
                    $firstSecondName = $val;
                }

                if ($k === 'LAST_NAME' || $k === 'FIRST_SECOND_NAME') {
                } else {
                    $arProps[$k] = $val;
                }
            }

            $arProps['UF_FIO'] = $lastName.' '.$firstSecondName;

            $hlblock = HL\HighloadBlockTable::getById(HLBLOCK_ID_VIOLATORS)->fetch();
            $entity = HL\HighloadBlockTable::compileEntity($hlblock);
            $entity_data_class = $entity->getDataClass();

            $result = $entity_data_class::update($key, $arProps);
        }
    }

    function format_phone($phone = '', $convert = false, $trim = true)
    {

        if (empty($phone)) {
            return '';
        }

        $phone = preg_replace("/[^0-9A-Za-z]/", "", $phone);

        if ($convert == true) {
            $replace = array('2'=>array('a','b','c'),
                '3'=>array('d','e','f'),
                '4'=>array('g','h','i'),
                '5'=>array('j','k','l'),
                '6'=>array('m','n','o'),
                '7'=>array('p','q','r','s'),
                '8'=>array('t','u','v'), '9'=>array('w','x','y','z'));

            foreach ($replace as $digit => $letters) {
                $phone = str_ireplace($letters, $digit, $phone);
            }
        }

        if ($trim == true && mb_strlen($phone)>11) {
            $phone = mb_substr($phone, 0, 11);
        }
        if (mb_strlen($phone) == 7) {
            return preg_replace("/([0-9a-zA-Z]{3})([0-9a-zA-Z]{4})/", "$1-$2", $phone);
        } elseif (mb_strlen($phone) == 10) {
            return preg_replace("/([0-9a-zA-Z]{3})([0-9a-zA-Z]{3})([0-9a-zA-Z]{2})([0-9a-zA-Z]{2})/", "+7 ($1) $2 $3 $4", $phone);
        } elseif (mb_strlen($phone) == 11) {
            if ($phone[0]==8) {
                $phone[0]=7;
            }
            return preg_replace("/([0-9a-zA-Z]{1})([0-9a-zA-Z]{3})([0-9a-zA-Z]{3})([0-9a-zA-Z]{2})([0-9a-zA-Z]{2})/", "+$1 ($2) $3 $4 $5", $phone);
        }
        return $phone;
    }

    $arResult['ACCESS'] = false;
    //Фильтрация результата по таблицам
    if (!empty($_REQUEST['q'])) {
        $filterSearch[]=array(
            "LOGIC" => "OR",
            "NAME" => "%".$_REQUEST['q']."%",
            "PROPERTY_ATT_ADDRESS" => "%".$_REQUEST['q']."%",
            "PROPERTY_ATT_ADDRESS_REPRES" => "%".$_REQUEST['q']."%",
            "PROPERTY_ATT_LEGAL_REPRES" => "%".$_REQUEST['q']."%",
            "PROPERTY_ATT_GUZ_HOSP_VALUE" => "%".$_REQUEST['q']."%",
            "PROPERTY_ATT_GUZ_NAV_VALUE" => "%".$_REQUEST['q']."%",
        );
    }

    if (!empty($_REQUEST['quarantine'])) {
        switch ($_REQUEST['quarantine']) {
            case "Y":
                $filterSearch['>=PROPERTY_ATT_DATE_PERESECHENIYA']=date('Y-m-d', time() - 86400 * (int)$QUARANTINE);
                break;
            case "N":
                $filterSearch['<PROPERTY_ATT_DATE_PERESECHENIYA']=date('Y-m-d', time() - 86400 * (int)$QUARANTINE);
                break;
        }
    }
    $arrListGuz=[];
    $arrListGuz['ATT_GUZ_NAV'][]='(Не выбрано)';
    $arrListGuz['ATT_GUZ_HOSP'][]='(Не выбрано)';
		$arrListGuz['ATT_ISOLATION_STATUS'][]='(Не выбрано)';
		$arrListGuz['ATT_NOTIFY_WAY_ENUM'][]='(Не выбрано)';

    $property_enums = CIBlockPropertyEnum::GetList(array("ID"=>"ASC", "SORT"=>"ASC"), array("IBLOCK_ID"=>IBLOCK_ID_MIGRATION_DOCS, "CODE"=>array("ATT_GUZ_NAV","ATT_GUZ_HOSP", "ATT_ISOLATION_STATUS", "ATT_NOTIFY_WAY_ENUM")));
    while ($enum_fields = $property_enums->GetNext()) {
        $arrListGuz[$enum_fields['PROPERTY_CODE']][$enum_fields["ID"]]=$enum_fields["VALUE"];
    }


    if (CSite::InGroup([GROUP_ID_MIGRATION_DOCS])) {
        $arResult['ACCESS'] = true;

        if ($CUR_PAGE == 'index') {
            function substr_count_array($haystack, $needle)
            {
                $count = 0;
                $haystack = mb_strtolower($haystack);
                foreach ($needle as $substring) {
                    $count += substr_count($haystack, mb_strtolower($substring));
                }
                return $count;
            }
            $arCities=array(
                'Тула',
                'Новомосковск',
                'Донской',
                'Алексин',
                'Щёкино,Щекино',
                'Узловая',
                'Ефремов',
                'Богородицк',
                'Кимовск',
                'Киреевск',
                'Суворов',
                'Ясногорск',
                'Плавск',
                'Венёв,Венев',
                'Белёв,Белев',
                'Первомайский',
                'Плеханово',
                'Болохово',
                'Дубовка',
                'Липки',
                'Товарковский',
                'Советск',
                'Заокский'
            );
            $arStats=[];
            foreach ($arCities as $sKey => $sCity) {
                $arStats[$sKey]=[
                    'FULL_CNT'=>0,
                    'SECTIONS'=>[],
                    'RESOLUT'=>0,
                    'QUARANTINE'=>0,
                    'FREE'=>0
                ];
            }
            $arOthers=[
                    'FULL_CNT'=>0,
                    'SECTIONS'=>[],
                    'RESOLUT'=>0,
                    'QUARANTINE'=>0,
                    'FREE'=>0
            ];
            $arFilter = array("IBLOCK_ID" => IBLOCK_ID_MIGRATION_DOCS, "ACTIVE" => "Y");
            $arSelect = array("ID","IBLOCK_SECTION_ID","IBLOCK_ID","NAME","PROPERTY_ATT_RESOLUTION","PROPERTY_ATT_DATE_PERESECHENIYA","PROPERTY_ATT_ADDRESS");
            $res = CIBlockElement::GetList($arResult['sort']['sort'], $arFilter, false, $arResult['nav_params'], $arSelect);

            $arr['RESOLUT']=$arr['QUARANTINE']=$arr['FREE']=0;
            $arr['ALL']=$res->selectedRowsCount();


            $obCache = new CPHPCache;
            $sCacheTime = 43200;
            $sCacheID = SITE_ID.'.'.md5('statistic');
            if ($obCache->InitCache($sCacheTime, $sCacheID, "/")) {
                $vars = $obCache->GetVars();
                $arResult['CITIES'] = $vars['STATISTIC']['CITIES'];
                $arResult['STATS'] = $vars['STATISTIC']['STATS'];
                $arResult['OTHERS'] = $vars['STATISTIC']['OTHERS'];
                $arResult['FULL'] = $vars['STATISTIC']['FULL'];
            } else {
                while ($arFields = $res->GetNext()) {
                    $find=false;
                    foreach ($arCities as $sKey => $sCity) {
                        if (substr_count_array(strtolower($arFields['PROPERTY_ATT_ADDRESS_VALUE']), explode(',', $sCity)) > 0) {
                            $find=true;
                            $arStats[$sKey]['FULL_CNT']++;
                            $arStats[$sKey]['SECTIONS'][$arFields['IBLOCK_SECTION_ID']]++;
                            if ($arFields['PROPERTY_ATT_RESOLUTION_VALUE']=="Y") {
                                $arStats[$sKey]['RESOLUT']++;
                            }
                            if (strtotime($arFields['PROPERTY_ATT_DATE_PERESECHENIYA_VALUE']." +14 day")<strtotime(date("d.m.Y"))) {
                                $arStats[$sKey]['FREE']++;
                            } else {
                                $arStats[$sKey]['QUARANTINE']++;
                            }
                        }
                    }
                    if (!$find) {
                        $arOthers['FULL_CNT']++;
                        $arOthers['SECTIONS'][$arFields['IBLOCK_SECTION_ID']]++;
                        if ($arFields['PROPERTY_ATT_RESOLUTION_VALUE']=="Y") {
                            $arOthers['RESOLUT']++;
                        }
                        if (strtotime($arFields['PROPERTY_ATT_DATE_PERESECHENIYA_VALUE']." +14 day")<strtotime(date("d.m.Y"))) {
                            $arOthers['FREE']++;
                        } else {
                            $arOthers['QUARANTINE']++;
                        }
                    }
                    $arr['SECTIONS'][$arFields['IBLOCK_SECTION_ID']][]=$arFields['ID'];
                    if ($arFields['PROPERTY_ATT_RESOLUTION_VALUE']=="Y") {
                        $arr['RESOLUT']++;
                    }
                    if (strtotime($arFields['PROPERTY_ATT_DATE_PERESECHENIYA_VALUE']." +14 day")<strtotime(date("d.m.Y"))) {
                        $arr['FREE']++;
                    } else {
                        $arr['QUARANTINE']++;
                    }
                }

                $arResultStatistic = [
                    'CITIES' => $arCities,
                    'STATS' => $arStats,
                    'OTHERS' => $arOthers,
                    'FULL' => $arr,
                ];


                if ($obCache->StartDataCache()) {
                    $obCache->EndDataCache(array("STATISTIC" => $arResultStatistic));

                    $arResult['CITIES'] = $arResultStatistic['CITIES'];
                    $arResult['STATS'] = $arResultStatistic['STATS'];
                    $arResult['OTHERS'] = $arResultStatistic['OTHERS'];
                    $arResult['FULL'] = $arResultStatistic['FULL'];
                }
            }
        }
        if ($CUR_PAGE == 'RF_TABLE') {
            if ($_POST["FIELDS"]) {
                updateElement(SECTION_ID_MIGRATION_DOCS_RF);
            }
            $arResult['grid_id'] = 'rus_isolation';

            $arResult['grid_options'] = new GridOptions($arResult['grid_id']);
            $arResult['sort'] = $arResult['grid_options']->GetSorting(['sort' => ['DATE_CREATE' => 'DESC'], 'vars' => ['by' => 'by', 'order' => 'order']]);
            $arResult['nav_params'] = $arResult['grid_options']->GetNavParams();


            $arResult['nav'] = new PageNavigation($arResult['grid_id']);
            $arResult['nav']->allowAllRecords(true)
                ->setPageSize($arResult['nav_params']['nPageSize'])
                ->initFromUri();
            if ($arResult['nav']->allRecordsShown()) {
                $arResult['nav_params'] = false;
            } else {
                $arResult['nav_params']['iNumPage'] = $arResult['nav']->getCurrentPage();
            }

            $arSelect = array(
                "ID",
                "IBLOCK_ID",
                "NAME",
                "DATE_ACTIVE_FROM",
                "PROPERTY_ATT_LAST_NAME",
                "PROPERTY_ATT_NAME",
                "PROPERTY_ATT_SEX",
                "PROPERTY_ATT_PHONE",
                "PROPERTY_ATT_DATE_SVEDENO",
                "PROPERTY_ATT_DATE_PERESECHENIYA",
                "PROPERTY_ATT_COUNTRY",
                "PROPERTY_ATT_PASSPORT",
                "PROPERTY_ATT_ADDRESS",
                "PROPERTY_ATT_LEGAL_REPRES",
                "PROPERTY_ATT_LEGAL_REPRES_DATE",
                "PROPERTY_ATT_ADDRESS_REPRES",
                "PROPERTY_ATT_DATE_RESOLUTION",
                "PROPERTY_ATT_RESOLUTION",
                "PROPERTY_ATT_GUZ_HOSP",
                "PROPERTY_ATT_GUZ_NAV",
            );

            $arResult['columns'] = [];
            $arResult['columns'][] = ['id' => 'ID', 'name' => 'ID', 'sort' => 'ID', 'default' => false];
            $arResult['columns'][] = ['id' => 'NAME', 'name' => 'Фамилия', 'sort' => 'NAME', 'default' => true, 'editable' => true];
            $arResult['columns'][] = ['id' => 'ATT_NAME', 'name' => 'Именные компоненты', 'sort' => 'PROPERTY_ATT_NAME', 'default' => true, 'editable' => true];
            $arResult['columns'][] = ['id' => 'ATT_SEX', 'name' => 'Пол', 'sort' => 'PROPERTY_ATT_SEX', 'default' => true, 'editable' => true];
            $arResult['columns'][] = ['id' => 'ATT_DATE_SVEDENO', 'name' => 'Дата рождения', 'sort' => 'PROPERTY_ATT_DATE_SVEDENO', 'default' => true, 'editable' => true, 'type'=>'date'];
            $arResult['columns'][] = ['id' => 'ATT_DATE_PERESECHENIYA', 'name' => 'Дата пересечения', 'sort' => 'PROPERTY_ATT_DATE_PERESECHENIYA', 'default' => true, 'editable' => true, 'type'=>'date'];
            $arResult['columns'][] = ['id' => 'ATT_COUNTRY', 'name' => 'Страна отбытия', 'sort' => 'PROPERTY_ATT_COUNTRY', 'default' => true, 'editable' => true];
            $arResult['columns'][] = ['id' => 'ATT_PASSPORT', 'name' => 'Данные паспорт-центр', 'default' => true, 'editable' => true, 'type'=>'text'];
            $arResult['columns'][] = ['id' => 'ATT_ADDRESS', 'name' => 'Адрес', 'default' => true, 'editable' => true];
            $arResult['columns'][] = ['id' => 'ATT_PHONE', 'name' => 'Номер телефона', 'default' => true, 'editable' => true, 'type'=>'text'];
            $arResult['columns'][] = ['id' => 'ATT_GUZ_NAV', 'name' => 'ГУЗ наблюдения', 'sort' => 'PROPERTY_ATT_GUZ_NAV', 'default' => true, 'editable' => ['TYPE'=>'DROPDOWN', 'items'=>$arrListGuz['ATT_GUZ_NAV']], 'items'=>$arrListGuz['ATT_GUZ_NAV']];
            $arResult['columns'][] = ['id' => 'ATT_GUZ_HOSP', 'name' => 'ГУЗ госпитализация', 'sort' => 'PROPERTY_GUZ_HOSP', 'default' => true, 'editable' => ['TYPE'=>'DROPDOWN', 'items'=>$arrListGuz['ATT_GUZ_HOSP']], 'type'=>'dropdown', 'items'=>$arrListGuz['ATT_GUZ_HOSP']];
            $arResult['columns'][] = ['id' => 'ATT_LEGAL_REPRES', 'name' => 'Законный представитель', 'sort' => 'PROPERTY_ATT_LEGAL_REPRES', 'default' => true, 'editable' => true];
            $arResult['columns'][] = ['id' => 'ATT_LEGAL_REPRES_DATE', 'name' => 'Дата рождения представителя', 'sort' => 'PROPERTY_ATT_LEGAL_REPRES_DATE', 'default' => true, 'editable' => true, 'type'=>'date'];
            $arResult['columns'][] = ['id' => 'ATT_ADDRESS_REPRES', 'name' => 'Адрес представителя', 'sort' => 'PROPERTY_ATT_ADDRESS_REPRES', 'default' => true, 'editable' => true];
            $arResult['columns'][] = ['id' => 'ATT_LAST_DATE', 'name' => 'Дата окончания карантина', 'sort' => 'PROPERTY_ATT_DATE_PERESECHENIYA', 'default' => true, 'editable' => false, 'type'=>'date'];
            $arResult['columns'][] = ['id' => 'ATT_DATE_RESOLUTION', 'name' => 'Дата вручения постановления', 'sort' => 'PROPERTY_ATT_DATE_RESOLUTION', 'default' => true, 'editable' => true, 'type'=>'date'];
            $arResult['columns'][] = ['id' => 'ATT_RESOLUTION', 'name' => 'Постановление вручено', 'sort' => 'PROPERTY_ATT_RESOLUTION', 'default' => true, 'editable' => true, 'type'=>'checkbox'];

            $arFilter = array("IBLOCK_ID" => IBLOCK_ID_MIGRATION_DOCS, "ACTIVE" => "Y","SECTION_ID"=>SECTION_ID_MIGRATION_DOCS_RF, $filterSearch);

            $res = CIBlockElement::GetList($arResult['sort']['sort'], $arFilter, false, $arResult['nav_params'], $arSelect);
            $arResult['nav']->setRecordCount($res->selectedRowsCount());
            while ($arFields = $res->GetNext()) {
                $arResult['list'][] = [
                    'data' => [
                        "ID" => $arFields['ID'],
                        "NAME" => $arFields['NAME'],
                        "ATT_NAME" => $arFields['PROPERTY_ATT_NAME_VALUE'],
                        "ATT_SEX" => $arFields['PROPERTY_ATT_SEX_VALUE'],
                        "ATT_DATE_SVEDENO" => $arFields['PROPERTY_ATT_DATE_SVEDENO_VALUE'],
                        "ATT_DATE_PERESECHENIYA" => $arFields['PROPERTY_ATT_DATE_PERESECHENIYA_VALUE'],
                        "ATT_COUNTRY" => $arFields['PROPERTY_ATT_COUNTRY_VALUE'],
                        "ATT_PASSPORT" => $arFields['PROPERTY_ATT_PASSPORT_VALUE'],
                        "ATT_ADDRESS" => $arFields['PROPERTY_ATT_ADDRESS_VALUE'],
                        "ATT_PHONE" => format_phone($arFields['PROPERTY_ATT_PHONE_VALUE']),
                        "ATT_GUZ_NAV" => $arFields['PROPERTY_ATT_GUZ_NAV_ENUM_ID'],
                        "ATT_GUZ_HOSP" => $arFields['PROPERTY_ATT_GUZ_HOSP_ENUM_ID'],
                        "ATT_LEGAL_REPRES" => $arFields['PROPERTY_ATT_LEGAL_REPRES_VALUE'],
                        "ATT_LEGAL_REPRES_DATE" => $arFields['PROPERTY_ATT_LEGAL_REPRES_DATE_VALUE'],
                        "ATT_LAST_DATE" => date("d.m.Y", strtotime($arFields['PROPERTY_ATT_DATE_PERESECHENIYA_VALUE'].$QUARANTINE)),
                        "ATT_ADDRESS_REPRES" => $arFields['PROPERTY_ATT_ADDRESS_REPRES_VALUE'],
                        "ATT_DATE_RESOLUTION" => ($arFields['PROPERTY_ATT_RESOLUTION_VALUE']=='Y'?date("d.m.Y", strtotime($arFields['PROPERTY_ATT_DATE_RESOLUTION_VALUE'])):''),
                        "ATT_RESOLUTION" => $arFields['PROPERTY_ATT_RESOLUTION_VALUE']=='Y'?"Y":"N",
                    ],
                    'columns'=>
                    [
                        "ATT_GUZ_NAV" => ($arFields['PROPERTY_ATT_GUZ_NAV_VALUE']? $arFields['PROPERTY_ATT_GUZ_NAV_VALUE']:"(Не выбрано)"),
                        "ATT_GUZ_HOSP" => ($arFields['PROPERTY_ATT_GUZ_HOSP_VALUE']? $arFields['PROPERTY_ATT_GUZ_HOSP_VALUE']:"(Не выбрано)"),
                    ],
                    'depth' => (strtotime($arFields['PROPERTY_ATT_DATE_PERESECHENIYA_VALUE'].$QUARANTINE)<strtotime(date("d.m.Y"))?1:0),
                    'editable' => true,
                    'actions' => [
                        [
                            'text' => 'Скачать',
                            'default' => true,
                            'onclick' => 'getFile(' . json_encode([
                                    'fio' => $arFields['NAME'],
                                    'name' => $arFields['PROPERTY_ATT_NAME_VALUE'],
                                    'birthday' => $arFields['PROPERTY_ATT_DATE_SVEDENO_VALUE'],
                                    'data_peresecheniya' => $arFields['PROPERTY_ATT_DATE_PERESECHENIYA_VALUE'],
                                    'address' => $arFields['PROPERTY_ATT_ADDRESS_VALUE'],
                                    'predstavitel' => $arFields['PROPERTY_ATT_LEGAL_REPRES_VALUE'],
                                    'predstavitel_year' => $arFields['PROPERTY_ATT_LEGAL_REPRES_DATE_VALUE'],
                                    'predstavitel_address' => $arFields['PROPERTY_ATT_ADDRESS_REPRES_VALUE'],
                                    'month' => $arMonth[date('n')],

                                ]) . ')',
                        ],
                    ]
                ];
            }
        }

        if ($CUR_PAGE == 'MZH_TABLE') {
            if ($_POST["FIELDS"]) {
                updateElement(SECTION_ID_MIGRATION_DOCS_MZH);
            }

            $arResult['grid_id'] = 'mzh_isolation';

            $arResult['grid_options'] = new GridOptions($arResult['grid_id']);
            $arResult['sort'] = $arResult['grid_options']->GetSorting(['sort' => ['DATE_CREATE' => 'DESC'], 'vars' => ['by' => 'by', 'order' => 'order']]);
            $arResult['nav_params'] = $arResult['grid_options']->GetNavParams();


            $arResult['nav'] = new PageNavigation($arResult['grid_id']);
            $arResult['nav']->allowAllRecords(true)
                ->setPageSize($arResult['nav_params']['nPageSize'])
                ->initFromUri();
            if ($arResult['nav']->allRecordsShown()) {
                $arResult['nav_params'] = false;
            } else {
                $arResult['nav_params']['iNumPage'] = $arResult['nav']->getCurrentPage();
            }

            $arSelect = array(
                "ID",
                "IBLOCK_ID",
                "NAME",
                "DATE_ACTIVE_FROM",
                "PROPERTY_ATT_DATE_BIRTHDAY",
                "PROPERTY_ATT_COUNTRY",
                "PROPERTY_ATT_PASSPORT",
                "PROPERTY_ATT_PHONE",
                "PROPERTY_ATT_DATE_PERESECHENIYA",
                "PROPERTY_ATT_DATE_REG_START",
                "PROPERTY_ATT_DATE_REG_END",
                "PROPERTY_ATT_ADDRESS",
                "PROPERTY_ATT_LEGAL_REPRES",
                "PROPERTY_ATT_LEGAL_REPRES_DATE",
                "PROPERTY_ATT_ADDRESS_REPRES",
                "PROPERTY_ATT_DATE_RESOLUTION",
                "PROPERTY_ATT_RESOLUTION",
                "PROPERTY_ATT_GUZ_HOSP",
                "PROPERTY_ATT_GUZ_NAV",
            );

            $arResult['columns'] = [];
            $arResult['columns'][] = ['id' => 'ID', 'name' => 'ID', 'sort' => 'ID', 'default' => false];
            $arResult['columns'][] = ['id' => 'NAME', 'name' => 'ФИО', 'sort' => 'NAME', 'default' => true, 'editable' => true];
            $arResult['columns'][] = ['id' => 'ATT_DATE_BIRTHDAY', 'name' => 'Дата рождения', 'sort' => 'PROPERTY_ATT_DATE_BIRTHDAY', 'default' => true, 'editable' => true, 'type'=>'date'];
            $arResult['columns'][] = ['id' => 'ATT_COUNTRY', 'name' => 'Гражданство', 'sort' => 'PROPERTY_ATT_COUNTRY', 'default' => true, 'editable' => true];
            $arResult['columns'][] = ['id' => 'ATT_PASSPORT', 'name' => 'Серия/номер документа', 'default' => true, 'editable' => true, 'type'=>'text'];
            $arResult['columns'][] = ['id' => 'ATT_DATE_PERESECHENIYA', 'name' => 'Дата пересечения границы', 'sort' => 'PROPERTY_ATT_DATE_PERESECHENIYA', 'default' => true, 'editable' => true, 'type'=>'date'];
            $arResult['columns'][] = ['id' => 'ATT_DATE_REG_START', 'name' => 'Дата регистрации с', 'sort' => 'PROPERTY_ATT_DATE_REG_START', 'default' => true, 'editable' => true, 'type'=>'date'];
            $arResult['columns'][] = ['id' => 'ATT_DATE_REG_END', 'name' => 'Дата регистрации по', 'sort' => 'PROPERTY_ATT_DATE_REG_END', 'default' => true, 'editable' => true, 'type'=>'date'];
            $arResult['columns'][] = ['id' => 'ATT_ADDRESS', 'name' => 'Адрес', 'default' => true, 'editable' => true];
            $arResult['columns'][] = ['id' => 'ATT_PHONE', 'name' => 'Номер телефона', 'default' => true, 'editable' => true, 'type'=>'text'];
            $arResult['columns'][] = ['id' => 'ATT_GUZ_NAV', 'name' => 'ГУЗ наблюдения', 'sort' => 'PROPERTY_ATT_GUZ_NAV', 'default' => true, 'editable' => ['TYPE'=>'DROPDOWN', 'items'=>$arrListGuz['ATT_GUZ_NAV']], 'type'=>'dropdown', 'items'=>$arrListGuz['ATT_GUZ_NAV']];
            $arResult['columns'][] = ['id' => 'ATT_GUZ_HOSP', 'name' => 'ГУЗ госпитализация', 'sort' => 'PROPERTY_GUZ_HOSP', 'default' => true, 'editable' => ['TYPE'=>'DROPDOWN', 'items'=>$arrListGuz['ATT_GUZ_HOSP']], 'type'=>'dropdown', 'items'=>$arrListGuz['ATT_GUZ_HOSP']];
            $arResult['columns'][] = ['id' => 'ATT_LEGAL_REPRES', 'name' => 'Законный представитель', 'sort' => 'PROPERTY_ATT_LEGAL_REPRES', 'default' => true, 'editable' => true];
            $arResult['columns'][] = ['id' => 'ATT_LEGAL_REPRES_DATE', 'name' => 'Дата рождения представителя', 'sort' => 'PROPERTY_ATT_LEGAL_REPRES_DATE', 'default' => true, 'editable' => true, 'type'=>'date'];
            $arResult['columns'][] = ['id' => 'ATT_ADDRESS_REPRES', 'name' => 'Адрес представителя', 'default' => true, 'editable' => true];
            $arResult['columns'][] = ['id' => 'ATT_LAST_DATE', 'name' => 'Дата окончания карантина', 'default' => true, 'editable' => false, 'type'=>'date'];
            $arResult['columns'][] = ['id' => 'ATT_DATE_RESOLUTION', 'name' => 'Дата вручения постановления', 'sort' => 'PROPERTY_ATT_DATE_RESOLUTION', 'default' => true, 'editable' => true, 'type'=>'date'];
            $arResult['columns'][] = ['id' => 'ATT_RESOLUTION', 'name' => 'Постановление вручено', 'sort' => 'PROPERTY_ATT_RESOLUTION', 'default' => true, 'editable' => true, 'type'=>'checkbox'];

            $arFilter = array("IBLOCK_ID" => IBLOCK_ID_MIGRATION_DOCS, "ACTIVE" => "Y", "SECTION_ID" => SECTION_ID_MIGRATION_DOCS_MZH, $filterSearch);

            $res = CIBlockElement::GetList($arResult['sort']['sort'], $arFilter, false, $arResult['nav_params'], $arSelect);
            $arResult['nav']->setRecordCount($res->selectedRowsCount());
            while ($arFields = $res->GetNext()) {
                $arResult['list'][] = [
                    'data' => [
                        "ID" => $arFields['ID'],
                        "NAME" => $arFields['NAME'],
                        "ATT_DATE_BIRTHDAY" => $arFields['PROPERTY_ATT_DATE_BIRTHDAY_VALUE'],
                        "ATT_COUNTRY" => $arFields['PROPERTY_ATT_COUNTRY_VALUE'],
                        "ATT_PASSPORT" => $arFields['PROPERTY_ATT_PASSPORT_VALUE'],
                        "ATT_DATE_PERESECHENIYA" => $arFields['PROPERTY_ATT_DATE_PERESECHENIYA_VALUE'],
                        "ATT_DATE_REG_START" => $arFields['PROPERTY_ATT_DATE_REG_START_VALUE'],
                        "ATT_DATE_REG_END" => $arFields['PROPERTY_ATT_DATE_REG_END_VALUE'],
                        "ATT_ADDRESS" => $arFields['PROPERTY_ATT_ADDRESS_VALUE'],
                        "ATT_PHONE" => format_phone($arFields['PROPERTY_ATT_PHONE_VALUE']),
                        "ATT_GUZ_NAV" => $arFields['PROPERTY_ATT_GUZ_NAV_ENUM_ID'],
                        "ATT_GUZ_HOSP" => $arFields['PROPERTY_ATT_GUZ_HOSP_ENUM_ID'],
                        "ATT_LEGAL_REPRES" => $arFields['PROPERTY_ATT_LEGAL_REPRES_VALUE'],
                        "ATT_LEGAL_REPRES_DATE" => $arFields['PROPERTY_ATT_LEGAL_REPRES_DATE_VALUE'],
                        "ATT_LAST_DATE" => date("d.m.Y", strtotime($arFields['PROPERTY_ATT_DATE_PERESECHENIYA_VALUE'].$QUARANTINE)),
                        "ATT_ADDRESS_REPRES" => $arFields['PROPERTY_ATT_ADDRESS_REPRES_VALUE'],
                        "ATT_DATE_RESOLUTION" => ($arFields['PROPERTY_ATT_RESOLUTION_VALUE']=='Y'?date("d.m.Y", strtotime($arFields['PROPERTY_ATT_DATE_RESOLUTION_VALUE'])):''),
                        "ATT_RESOLUTION" => $arFields['PROPERTY_ATT_RESOLUTION_VALUE']=='Y'?"Y":"N",
                    ],
                    'columns'=>
                    [
                        "ATT_GUZ_NAV" => ($arFields['PROPERTY_ATT_GUZ_NAV_VALUE']? $arFields['PROPERTY_ATT_GUZ_NAV_VALUE']:"(Не выбрано)"),
                        "ATT_GUZ_HOSP" => ($arFields['PROPERTY_ATT_GUZ_HOSP_VALUE']? $arFields['PROPERTY_ATT_GUZ_HOSP_VALUE']:"(Не выбрано)"),
                    ],
                    'depth' => (strtotime($arFields['PROPERTY_ATT_DATE_PERESECHENIYA_VALUE'].$QUARANTINE)<strtotime(date("d.m.Y"))?1:0),
                    'editable' => true,
                    'actions' => [
                        [
                            'text' => 'Скачать',
                            'default' => true,
                            'onclick' => 'getFileMZH(' . json_encode([
                                    'fio' => $arFields['NAME'],
                                    'birthday' => $arFields['PROPERTY_ATT_DATE_BIRTHDAY_VALUE'],
                                    'data_peresecheniya' => $arFields['PROPERTY_ATT_DATE_PERESECHENIYA_VALUE'],
                                    'address' => $arFields['PROPERTY_ATT_ADDRESS_VALUE'],
                                    'predstavitel' => $arFields['PROPERTY_ATT_LEGAL_REPRES_VALUE'],
                                    'predstavitel_year' => $arFields['PROPERTY_ATT_LEGAL_REPRES_DATE_VALUE'],
                                    'predstavitel_address' => $arFields['PROPERTY_ATT_ADDRESS_REPRES_VALUE'],
                                    'month' => $arMonth[date('n')],

                                ]) . ')',
                        ],
                    ]
                ];
            }
        }

        if ($CUR_PAGE == 'MP_TABLE') {
            if ($_POST["FIELDS"]) {
                updateElement(SECTION_ID_MIGRATION_DOCS_MP);
            }

            $arResult['grid_id'] = 'mp_isolation';

            $arResult['grid_options'] = new GridOptions($arResult['grid_id']);
            $arResult['sort'] = $arResult['grid_options']->GetSorting(['sort' => ['DATE_CREATE' => 'DESC'], 'vars' => ['by' => 'by', 'order' => 'order']]);
            $arResult['nav_params'] = $arResult['grid_options']->GetNavParams();


            $arResult['nav'] = new PageNavigation($arResult['grid_id']);
            $arResult['nav']->allowAllRecords(true)
                ->setPageSize($arResult['nav_params']['nPageSize'])
                ->initFromUri();
            if ($arResult['nav']->allRecordsShown()) {
                $arResult['nav_params'] = false;
            } else {
                $arResult['nav_params']['iNumPage'] = $arResult['nav']->getCurrentPage();
            }

            $arSelect = array(
                "ID",
                "IBLOCK_ID",
                "NAME",
                "DATE_ACTIVE_FROM",
                "PROPERTY_ATT_FIO_LAT",
                "PROPERTY_ATT_DATE_BIRTHDAY",
                "PROPERTY_ATT_SEX",
                "PROPERTY_ATT_COUNTRY",
                "PROPERTY_ATT_PHONE",
                "PROPERTY_ATT_PASSPORT",
                "PROPERTY_ATT_DATE_PERESECHENIYA",
                "PROPERTY_ATT_DATE_REG_START",
                "PROPERTY_ATT_DATE_REG_END",
                "PROPERTY_ATT_INCOMING_SIDE",
                "PROPERTY_ATT_ADDRESS",
                "PROPERTY_ATT_LEGAL_REPRES",
                "PROPERTY_ATT_LEGAL_REPRES_DATE",
                "PROPERTY_ATT_ADDRESS_REPRES",
                "PROPERTY_ATT_DATE_RESOLUTION",
                "PROPERTY_ATT_RESOLUTION",
                "PROPERTY_ATT_GUZ_HOSP",
                "PROPERTY_ATT_GUZ_NAV",
            );

            $arResult['columns'] = [];
            $arResult['columns'][] = ['id' => 'ID', 'name' => 'ID', 'sort' => 'ID', 'default' => false];
            $arResult['columns'][] = ['id' => 'NAME', 'name' => 'ФИО', 'sort' => 'NAME', 'default' => true, 'editable' => true];
            $arResult['columns'][] = ['id' => 'ATT_FIO_LAT', 'name' => 'ФИО ИГ лат', 'sort' => 'PROPERTY_ATT_FIO_LAT', 'default' => true, 'editable' => true];
            $arResult['columns'][] = ['id' => 'ATT_DATE_BIRTHDAY', 'name' => 'Дата рождения', 'sort' => 'PROPERTY_ATT_DATE_BIRTHDAY', 'default' => true, 'editable' => true, 'type'=>'date'];
            $arResult['columns'][] = ['id' => 'ATT_SEX', 'name' => 'Пол', 'sort' => 'PROPERTY_ATT_SEX', 'default' => true, 'editable' => true];
            $arResult['columns'][] = ['id' => 'ATT_COUNTRY', 'name' => 'Гражданство', 'sort' => 'PROPERTY_ATT_COUNTRY', 'default' => true, 'editable' => true];
            $arResult['columns'][] = ['id' => 'ATT_PASSPORT', 'name' => 'Серия/номер документа', 'default' => true, 'editable' => true, 'type'=>'text'];
            $arResult['columns'][] = ['id' => 'ATT_DATE_PERESECHENIYA', 'name' => 'Дата пересечения границы', 'sort' => 'PROPERTY_ATT_DATE_PERESECHENIYA', 'default' => true, 'editable' => true, 'type'=>'date'];
            $arResult['columns'][] = ['id' => 'ATT_DATE_REG_START', 'name' => 'Дата регистрации с', 'sort' => 'PROPERTY_ATT_DATE_REG_START', 'default' => true, 'editable' => true, 'type'=>'date'];
            $arResult['columns'][] = ['id' => 'ATT_DATE_REG_END', 'name' => 'Дата регистрации по', 'sort' => 'PROPERTY_ATT_DATE_REG_END', 'default' => true, 'editable' => true, 'type'=>'date'];
            $arResult['columns'][] = ['id' => 'ATT_INCOMING_SIDE', 'name' => 'Принимающая сторона', 'default' => true, 'editable' => true];
            $arResult['columns'][] = ['id' => 'ATT_ADDRESS', 'name' => 'Адрес', 'default' => true, 'editable' => true];
            $arResult['columns'][] = ['id' => 'ATT_PHONE', 'name' => 'Номер телефона', 'default' => true, 'editable' => true, 'type'=>'text'];
            $arResult['columns'][] = ['id' => 'ATT_GUZ_NAV', 'name' => 'ГУЗ наблюдения', 'sort' => 'PROPERTY_ATT_GUZ_NAV', 'default' => true, 'editable' => ['TYPE'=>'DROPDOWN', 'items'=>$arrListGuz['ATT_GUZ_NAV']], 'type'=>'dropdown', 'items'=>$arrListGuz['ATT_GUZ_NAV']];
            $arResult['columns'][] = ['id' => 'ATT_GUZ_HOSP', 'name' => 'ГУЗ госпитализация', 'sort' => 'PROPERTY_GUZ_HOSP', 'default' => true, 'editable' => ['TYPE'=>'DROPDOWN', 'items'=>$arrListGuz['ATT_GUZ_HOSP']], 'type'=>'dropdown', 'items'=>$arrListGuz['ATT_GUZ_HOSP']];
            $arResult['columns'][] = ['id' => 'ATT_LEGAL_REPRES', 'name' => 'Законный представитель', 'sort' => 'PROPERTY_ATT_LEGAL_REPRES', 'default' => true, 'editable' => true];
            $arResult['columns'][] = ['id' => 'ATT_LEGAL_REPRES_DATE', 'name' => 'Дата рождения представителя', 'sort' => 'PROPERTY_ATT_LEGAL_REPRES_DATE', 'default' => true, 'editable' => true, 'type'=>'date'];
            $arResult['columns'][] = ['id' => 'ATT_ADDRESS_REPRES', 'name' => 'Адрес представителя', 'default' => true, 'editable' => true];
            $arResult['columns'][] = ['id' => 'ATT_LAST_DATE', 'name' => 'Дата окончания карантина', 'default' => true, 'editable' => false, 'type'=>'date'];
            $arResult['columns'][] = ['id' => 'ATT_DATE_RESOLUTION', 'name' => 'Дата вручения постановления', 'sort' => 'PROPERTY_ATT_DATE_RESOLUTION', 'default' => true, 'editable' => true, 'type'=>'date'];
            $arResult['columns'][] = ['id' => 'ATT_RESOLUTION', 'name' => 'Постановление вручено', 'sort' => 'PROPERTY_ATT_RESOLUTION', 'default' => true, 'editable' => true, 'type'=>'checkbox'];

            $arFilter = array("IBLOCK_ID" => IBLOCK_ID_MIGRATION_DOCS, "ACTIVE" => "Y", "SECTION_ID" => SECTION_ID_MIGRATION_DOCS_MP, $filterSearch);

            $res = CIBlockElement::GetList($arResult['sort']['sort'], $arFilter, false, $arResult['nav_params'], $arSelect);
            $arResult['nav']->setRecordCount($res->selectedRowsCount());
            while ($arFields = $res->GetNext()) {
                $arResult['list'][] = [
                    'data' => [
                        "ID" => $arFields['ID'],
                        "NAME" => $arFields['NAME'],
                        "ATT_FIO_LAT" => $arFields['PROPERTY_ATT_FIO_LAT_VALUE'],
                        "ATT_DATE_BIRTHDAY" => $arFields['PROPERTY_ATT_DATE_BIRTHDAY_VALUE'],
                        "ATT_SEX" => $arFields['PROPERTY_ATT_SEX_VALUE'],
                        "ATT_COUNTRY" => $arFields['PROPERTY_ATT_COUNTRY_VALUE'],
                        "ATT_PASSPORT" => $arFields['PROPERTY_ATT_PASSPORT_VALUE'],
                        "ATT_PHONE" => format_phone($arFields['PROPERTY_ATT_PHONE_VALUE']),
                        "ATT_DATE_PERESECHENIYA" => $arFields['PROPERTY_ATT_DATE_PERESECHENIYA_VALUE'],
                        "ATT_DATE_REG_START" => $arFields['PROPERTY_ATT_DATE_REG_START_VALUE'],
                        "ATT_DATE_REG_END" => $arFields['PROPERTY_ATT_DATE_REG_END_VALUE'],
                        "ATT_INCOMING_SIDE" => $arFields['PROPERTY_ATT_INCOMING_SIDE_VALUE'],
                        "ATT_ADDRESS" => $arFields['PROPERTY_ATT_ADDRESS_VALUE'],
                        "ATT_GUZ_NAV" => $arFields['PROPERTY_ATT_GUZ_NAV_ENUM_ID'],
                        "ATT_GUZ_HOSP" => $arFields['PROPERTY_ATT_GUZ_HOSP_ENUM_ID'],
                        "ATT_LEGAL_REPRES" => $arFields['PROPERTY_ATT_LEGAL_REPRES_VALUE'],
                        "ATT_LEGAL_REPRES_DATE" => $arFields['PROPERTY_ATT_LEGAL_REPRES_DATE_VALUE'],
                        "ATT_LAST_DATE" => date("d.m.Y", strtotime($arFields['PROPERTY_ATT_DATE_PERESECHENIYA_VALUE'].$QUARANTINE)),
                        "ATT_ADDRESS_REPRES" => $arFields['PROPERTY_ATT_ADDRESS_REPRES_VALUE'],
                        "ATT_DATE_RESOLUTION" => ($arFields['PROPERTY_ATT_RESOLUTION_VALUE']=='Y'?date("d.m.Y", strtotime($arFields['PROPERTY_ATT_DATE_RESOLUTION_VALUE'])):''),
                        "ATT_RESOLUTION" => $arFields['PROPERTY_ATT_RESOLUTION_VALUE']=='Y'?"Y":"N",
                    ],
                    'columns'=>
                    [
                        "ATT_GUZ_NAV" => ($arFields['PROPERTY_ATT_GUZ_NAV_VALUE']? $arFields['PROPERTY_ATT_GUZ_NAV_VALUE']:"(Не выбрано)"),
                        "ATT_GUZ_HOSP" => ($arFields['PROPERTY_ATT_GUZ_HOSP_VALUE']? $arFields['PROPERTY_ATT_GUZ_HOSP_VALUE']:"(Не выбрано)"),
                    ],
                    'depth' => (strtotime($arFields['PROPERTY_ATT_DATE_PERESECHENIYA_VALUE'].$QUARANTINE)<strtotime(date("d.m.Y"))?1:0),
                    'editable' => true,
                    'actions' => [
                        [
                            'text' => 'Скачать',
                            'default' => true,
                            'onclick' => 'getFileMP(' . json_encode([
                                    'fio' => $arFields['NAME'],
                                    'birthday' => $arFields['PROPERTY_ATT_DATE_BIRTHDAY_VALUE'],
                                    'data_peresecheniya' => $arFields['PROPERTY_ATT_DATE_PERESECHENIYA_VALUE'],
                                    'address' => $arFields['PROPERTY_ATT_ADDRESS_VALUE'],
                                    'predstavitel' => $arFields['PROPERTY_ATT_LEGAL_REPRES_VALUE'],
                                    'predstavitel_year' => $arFields['PROPERTY_ATT_LEGAL_REPRES_DATE_VALUE'],
                                    'predstavitel_address' => $arFields['PROPERTY_ATT_ADDRESS_REPRES_VALUE'],
                                    'month' => $arMonth[date('n')],

                                ]) . ')',
                        ],
                    ]
                ];
            }
        }

		    if ($CUR_PAGE == 'UPDATE_ADDRESS_COMPONENTS') {

						switch ($_REQUEST['action']) {

								case 'create_check_sum':

										$arSelect = array(
												"ID",
												"IBLOCK_ID",
												"NAME",
												"DATE_ACTIVE_FROM",
												"PROPERTY_ATT_NAME",
												"PROPERTY_ATT_EPID",
												"PROPERTY_ATT_STEP",
												"PROPERTY_ATT_DATE_BIRTHDAY",
												"PROPERTY_ATT_WORK",
												"PROPERTY_ATT_PHONE",
												"PROPERTY_ATT_LEGAL_REPRES",
												"PROPERTY_ATT_LEGAL_REPRES_DATE",
												"PROPERTY_ATT_ADDRESS_REPRES",
												"PROPERTY_ATT_POSITION",
												"PROPERTY_ATT_DATE_PERESECHENIYA",
												"PROPERTY_ATT_DATE_QUARANT",
												"PROPERTY_ATT_PLACE_CONTACT",
												"PROPERTY_ATT_INCOMING_SIDE",
												"PROPERTY_ATT_ISOLATION_STATUS",
												"PROPERTY_ATT_DATE_RESOLUTION",
												"PROPERTY_ATT_RESOLUTION",
												"PROPERTY_ATT_GUZ_NAV",
												"PROPERTY_ATT_ADDRESS",
												"PROPERTY_ATT_DATE_SVEDENO",
												"PROPERTY_ATT_SNILS",
												"PROPERTY_ATT_NOTIFY_DATE",
												"PROPERTY_ATT_NOTIFY_WAY",
												"PROPERTY_ATT_DATE_ADD_DATA",
												"PROPERTY_ATT_AREA",
												"PROPERTY_ATT_CITY",
												"PROPERTY_ATT_VAC_1_DATE",
												"PROPERTY_ATT_VAC_2_DATE",
												"PROPERTY_ATT_ILLNESS_DATE",


										);
										$arFilter = Array(
												"IBLOCK_ID"=>IBLOCK_ID_MIGRATION_DOCS,
												"ACTIVE_DATE"=>"Y",
												"ACTIVE"=>"Y",
												"IBLOCK_SECTION_ID" => SECTION_ID_MIGRATION_CONT,
										);

										$res = CIBlockElement::GetList(Array('sort' => 'asc'), $arFilter, false, false, $arSelect);
										pre($res->SelectedRowsCount());
										while($arFields = $res->GetNext()) {
												$arProps = [
														'ATT_EPID' => $arFields['PROPERTY_ATT_EPID_VALUE'],
														'ATT_STEP' => $arFields['PROPERTY_ATT_STEP_VALUE'],
														'ATT_DATE_BIRTHDAY' => $arFields['PROPERTY_ATT_DATE_BIRTHDAY_VALUE'],
														'ATT_ADDRESS' => $arFields['PROPERTY_ATT_ADDRESS_VALUE'],
														'ATT_PHONE' => $arFields['PROPERTY_ATT_PHONE_VALUE'],
														'ATT_WORK' => $arFields['PROPERTY_ATT_WORK_VALUE'],
														'ATT_POSITION' => $arFields['PROPERTY_ATT_POSITION_VALUE'],
														'ATT_LEGAL_REPRES' => $arFields['PROPERTY_ATT_LEGAL_REPRES_VALUE'],
														'ATT_ADDRESS_REPRES' => $arFields['PROPERTY_ATT_ADDRESS_REPRES_VALUE'],
														'ATT_DATE_PERESECHENIYA' => $arFields['PROPERTY_ATT_DATE_PERESECHENIYA_VALUE'],
														'ATT_PLACE_CONTACT' => $arFields['PROPERTY_ATT_PLACE_CONTACT_VALUE'],
														'ATT_GUZ_NAV' => $arFields['PROPERTY_ATT_GUZ_NAV_VALUE'],
														'ATT_DATE_QUARANT' => $arFields['PROPERTY_ATT_DATE_QUARANT_VALUE'],
														'ATT_LAST_DATE' => $arFields['PROPERTY_ATT_LAST_DATE_VALUE'],
														'ATT_INCOMING_SIDE' => $arFields['PROPERTY_ATT_INCOMING_SIDE_VALUE'],
														'ATT_ISOLATION_STATUS' => $arFields['PROPERTY_ATT_ISOLATION_STATUS_VALUE'],
														'ATT_DATE_RESOLUTION' => $arFields['PROPERTY_ATT_DATE_RESOLUTION_VALUE'],
														'ATT_RESOLUTION' => $arFields['PROPERTY_ATT_RESOLUTION_VALUE'],
														'ATT_NOTIFY_DATE' => $arFields['PROPERTY_ATT_NOTIFY_DATE_VALUE'],
														'ATT_NOTIFY_WAY' => $arFields['PROPERTY_ATT_NOTIFY_WAY_VALUE'],
														'ATT_VAC_1_DATE' => $arFields['PROPERTY_ATT_VAC_1_DATE_VALUE'],
														'ATT_VAC_2_DATE' => $arFields['PROPERTY_ATT_VAC_2_DATE_VALUE'],
														'ATT_ILLNESS_DATE' => $arFields['PROPERTY_ATT_ILLNESS_DATE_VALUE'],
												];

												$md5Sum = md5($arFields['NAME'].';'.implode(';', $arProps));

												$arMD5[$arFields['ID']] = $md5Sum;

												CIBlockElement::SetPropertyValuesEx($arFields['ID'],IBLOCK_ID_MIGRATION_DOCS , array('ATT_CHECK_SUM' => $md5Sum));
										}

										break;

								case 'delete_repeat_rows':

										function getRepeatItems($id, $repeat) {

												$arRepeatIDS = [];

												foreach ($repeat as $key => $value) {
														foreach ($value as $k => $v) {
																if ($v == $id) {
																		$arRepeatIDS = $repeat[$key];
																}
														}
												}

												return $arRepeatIDS;
										}

										$arMD5 = [];
										$arRepeat = [];

										$arSelect = array(
												"ID",
												"IBLOCK_ID",
												"PROPERTY_ATT_CHECK_SUM"
										);
										$arFilter = Array(
												"IBLOCK_ID"=>IBLOCK_ID_MIGRATION_DOCS,
												"ACTIVE_DATE"=>"Y",
												"ACTIVE"=>"Y",
												"IBLOCK_SECTION_ID" => SECTION_ID_MIGRATION_CONT,
										);

										$res = CIBlockElement::GetList(Array('sort' => 'asc'), $arFilter, false, false, $arSelect);

										while($arFields = $res->GetNext()) {
												$arMD5[$arFields['ID']] = $arFields['PROPERTY_ATT_CHECK_SUM_VALUE'];
										}

										$arCheckUnique = array_count_values($arMD5);

										$arDeleteItems = [];
										foreach ($arCheckUnique as $key => $value) {
												if ($value > 1) {

														$arSelectS = Array("ID", "IBLOCK_ID", "NAME","PROPERTY_ATT_CHECK_SUM");
														$arFilterS = Array("IBLOCK_ID"=>IBLOCK_ID_MIGRATION_DOCS, "ACTIVE_DATE"=>"Y", "ACTIVE"=>"Y", "IBLOCK_SECTION_ID" => SECTION_ID_MIGRATION_CONT, "PROPERTY_ATT_CHECK_SUM" => $key);
														$resS = CIBlockElement::GetList(Array('sort' => 'asc'), $arFilterS, false, false, $arSelectS);
														while($arFields = $resS->GetNext()) {
																$arRepeat[$key][] = intval($arFields['ID']);

																$intMaxID = max($arRepeat[$key]);

																foreach ($arRepeat[$key] as $id) {
																		if ($id != $intMaxID) {
																				if (!in_array($id, $arDeleteItems)) {
																						$arDeleteItems[] = $id;
																				}
																		}
																}
														}
												}
										}


										$logger = new Logger('DELETE ELEMENT');
										$logger->pushHandler(new StreamHandler($_SERVER['DOCUMENT_ROOT'] . '/local/logs/isolation_contact_table_delete.log', Logger::INFO));


										if (count($arRepeat) > 0) {


												foreach ($arRepeat as $arDeleteIDS) {
														foreach ($arDeleteIDS as $deleteID) {
																if(CIBlock::GetPermission(IBLOCK_ID_MIGRATION_DOCS)>='W')
																{
																		$DB->StartTransaction();
																		if(!CIBlockElement::Delete(intval($deleteID)))
																		{
																				$DB->Rollback();
																		}
																		else {
																				$DB->Commit();
																				$logger->info('Удален элемент с ID: ' . $deleteID. '. Массив совпадений: '. implode(';', getRepeatItems($deleteID, $arRepeat)));
																		}

																}
														}
												}
										} else {
												$logger->info('Повторяющиеся элементы не найдены');
										}

										break;

								case 'add_address_components':

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
												"PROPERTY_ATT_AREA" => false,
												"PROPERTY_ATT_CITY" => false,
												"!PROPERTY_ATT_ADDRESS" => false
										);

										$res = CIBlockElement::GetList(Array('id' => 'asc'), $arFilter, false, ['nPageSize' => 1, 'nTopCount' => 100], $arSelect);
										pre($res->SelectedRowsCount());
										while($arFields = $res->GetNext()) {
												pre($arFields['ID']);

												$arProps['ATT_ADDRESS'] = $arFields['PROPERTY_ATT_ADDRESS_VALUE'];

												addAddressComponents($arProps, ['ATT_AREA', 'ATT_CITY'], IBLOCK_ID_MIGRATION_DOCS, ['area', 'city'], '202ef02ba212fda90bb83c1957d4f84c1d14aea8');

												CIBlockElement::SetPropertyValuesEx($arFields['ID'],IBLOCK_ID_MIGRATION_DOCS , array('ATT_AREA' => $arProps['ATT_AREA']));
												CIBlockElement::SetPropertyValuesEx($arFields['ID'],IBLOCK_ID_MIGRATION_DOCS , array('ATT_CITY' => $arProps['ATT_CITY']));

												$logger->info('Обновлен элемент с ID: '.$arFields['ID'], $arProps);

										}

										break;


								case 'clear_address_components':

										$logger = new Logger('CLEAR ADDRESS COMPONENTS ELEMENT');
										$logger->pushHandler(new StreamHandler($_SERVER['DOCUMENT_ROOT'] . '/local/logs/isolation_contact_table_clear_address.log', Logger::INFO));

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
												"PROPERTY_ATT_ADDRESS" => false,
												"!PROPERTY_ATT_AREA" => false,
												"!PROPERTY_ATT_CITY" => false,
										);

										$res = CIBlockElement::GetList(Array('sort' => 'asc'), $arFilter, false, false, $arSelect);
										pre($res->SelectedRowsCount());
										while($arFields = $res->GetNext()) {

												CIBlockElement::SetPropertyValuesEx($arFields['ID'],IBLOCK_ID_MIGRATION_DOCS , array('ATT_AREA' => false));
												CIBlockElement::SetPropertyValuesEx($arFields['ID'],IBLOCK_ID_MIGRATION_DOCS , array('ATT_CITY' => false));

												$logger->info('Обновлен элемент с ID: '.$arFields['ID'], array($arFields['PROPERTY_ATT_ADDRESS_VALUE']));

										}

										break;


								case 'clear_areas':

										$property = \CIBlockProperty::GetList([], ['IBLOCK_ID' => IBLOCK_ID_MIGRATION_DOCS, 'CODE' => "ATT_AREA"])->Fetch();

										$query = \CIBlockPropertyEnum::GetList(
												[],
												["IBLOCK_ID" => $iblockId, "PROPERTY_ID" => $property['ID']]
										);
										while ($value = $query->GetNext()) {
												pre($value);
												$delete = \CIBlockPropertyEnum::delete($value['ID']);
												if (! $delete) {
														throw new \Exception('Error while deleting the property value');
												}
										}


										break;

								case 'clear_cities':

										$property = \CIBlockProperty::GetList([], ['IBLOCK_ID' => IBLOCK_ID_MIGRATION_DOCS, 'CODE' => "ATT_CITY"])->Fetch();

										$query = \CIBlockPropertyEnum::GetList(
												[],
												["IBLOCK_ID" => $iblockId, "PROPERTY_ID" => $property['ID']]
										);
										while ($value = $query->GetNext()) {
												pre($value);
												$delete = \CIBlockPropertyEnum::delete($value['ID']);
												if (! $delete) {
														throw new \Exception('Error while deleting the property value');
												}
										}


										break;

								default:
										pre('Выберите действие');



						}



		    }

        if ($CUR_PAGE == 'CONT_TABLE') {


		        $bollFilterLogicOr = true;

		        $arAreaEnums = [];
		        $arCityEnums = [];
		        $arGUZEnums = [];

		        $propAreaEnums = CIBlockPropertyEnum::GetList(Array("DEF"=>"DESC", "SORT"=>"ASC"), Array("IBLOCK_ID"=>$iblockID, "CODE"=>'ATT_AREA'));

		        while($arFieldsEnum = $propAreaEnums->GetNext()) {
				        $arAreaEnums[$arFieldsEnum['ID']] = $arFieldsEnum['VALUE'];
				        asort($arAreaEnums);
		        }

		        $propCityEnums = CIBlockPropertyEnum::GetList(Array("DEF"=>"DESC", "SORT"=>"ASC"), Array("IBLOCK_ID"=>$iblockID, "CODE"=>'ATT_CITY'));

		        while($arFieldsEnum = $propCityEnums->GetNext()) {
				        $arCityEnums[$arFieldsEnum['ID']] = $arFieldsEnum['VALUE'];
				        asort($arCityEnums);
		        }

		        $propGUZEnums = CIBlockPropertyEnum::GetList(Array("DEF"=>"DESC", "SORT"=>"ASC"), Array("IBLOCK_ID"=>$iblockID, "CODE"=>'ATT_GUZ_NAV'));

		        while($arFieldsEnum = $propGUZEnums->GetNext()) {
				        $arGUZEnums[$arFieldsEnum['ID']] = str_replace('&quot;', '"', $arFieldsEnum['VALUE']);
				        asort($arGUZEnums);
		        }



		        $arResult['UI_FILTER'] = [
				        [
						        'id' => 'ATT_DATE_ADD_DATA',
						        'name' => 'Дата внесения данных',
						        'type'=>'date',
						        'default' => true,
						        "exclude" => array(
								        \Bitrix\Main\UI\Filter\DateType::LAST_7_DAYS,
								        \Bitrix\Main\UI\Filter\DateType::LAST_30_DAYS,
								        \Bitrix\Main\UI\Filter\DateType::LAST_60_DAYS,
								        \Bitrix\Main\UI\Filter\DateType::LAST_90_DAYS,
								        \Bitrix\Main\UI\Filter\DateType::CURRENT_MONTH,
								        \Bitrix\Main\UI\Filter\DateType::CURRENT_QUARTER,
								        \Bitrix\Main\UI\Filter\DateType::CURRENT_WEEK,
								        \Bitrix\Main\UI\Filter\DateType::LAST_MONTH,
								        \Bitrix\Main\UI\Filter\DateType::LAST_WEEK,
								        \Bitrix\Main\UI\Filter\DateType::MONTH,
								        \Bitrix\Main\UI\Filter\DateType::NEXT_MONTH,
								        \Bitrix\Main\UI\Filter\DateType::NEXT_DAYS,
								        \Bitrix\Main\UI\Filter\DateType::YEAR,
								        \Bitrix\Main\UI\Filter\DateType::NEXT_WEEK,
								        \Bitrix\Main\UI\Filter\DateType::TOMORROW,
								        \Bitrix\Main\UI\Filter\DateType::QUARTER,
								        \Bitrix\Main\UI\Filter\DateType::YESTERDAY,
								        \Bitrix\Main\UI\Filter\DateType::CURRENT_DAY,
								        \Bitrix\Main\UI\Filter\DateType::EXACT,
								        \Bitrix\Main\UI\Filter\DateType::PREV_DAYS,
						        )
				        ],
				        ['id' => 'ATT_AREA', 'name' => 'Район', 'type' => 'list', 'items' => $arAreaEnums, 'params' => ['multiple' => 'Y']],
				        ['id' => 'ATT_CITY', 'name' => 'Населенный пункт', 'type' => 'list', 'items' => $arCityEnums, 'params' => ['multiple' => 'Y']],
				        ['id' => 'ATT_GUZ_NAV', 'name' => 'ГУЗ наблюдения', 'type' => 'list', 'items' => $arGUZEnums, 'params' => ['multiple' => 'Y']],
				        ['id' => 'ATT_DATE_PERESECHENIYA', 'name' => 'На карантине', 'type' => 'list', 'items' => ['Y' => 'Да', 'N' => 'Нет'], 'params' => ['multiple' => 'N']],

		        ];


            if ($_POST["FIELDS"]) {
                updateElement(SECTION_ID_MIGRATION_CONT, true);
            }

            $arResult['grid_id'] = 'rus_isolation_contacts';

            $arResult['grid_options'] = new GridOptions($arResult['grid_id']);
            $arResult['sort'] = $arResult['grid_options']->GetSorting(['sort' => ['DATE_CREATE' => 'DESC'], 'vars' => ['by' => 'by', 'order' => 'order']]);
            $arResult['nav_params'] = $arResult['grid_options']->GetNavParams();


            $arResult['nav'] = new PageNavigation($arResult['grid_id']);
            $arResult['nav']->allowAllRecords(true)
                ->setPageSize($arResult['nav_params']['nPageSize'])
                ->initFromUri();
            if ($arResult['nav']->allRecordsShown()) {
                $arResult['nav_params'] = false;
            } else {
                $arResult['nav_params']['iNumPage'] = $arResult['nav']->getCurrentPage();
            }


            $arSelect = array(
                "ID",
                "IBLOCK_ID",
                "NAME",
                "DATE_ACTIVE_FROM",
                "PROPERTY_ATT_NAME",
                "PROPERTY_ATT_EPID",
                "PROPERTY_ATT_STEP",
                "PROPERTY_ATT_DATE_BIRTHDAY",
                "PROPERTY_ATT_WORK",
                "PROPERTY_ATT_PHONE",
                "PROPERTY_ATT_LEGAL_REPRES",
                "PROPERTY_ATT_LEGAL_REPRES_DATE",
                "PROPERTY_ATT_ADDRESS_REPRES",
                "PROPERTY_ATT_POSITION",
                "PROPERTY_ATT_DATE_PERESECHENIYA",
                "PROPERTY_ATT_DATE_QUARANT",
                "PROPERTY_ATT_PLACE_CONTACT",
                "PROPERTY_ATT_INCOMING_SIDE",
                "PROPERTY_ATT_ISOLATION_STATUS",
                "PROPERTY_ATT_DATE_RESOLUTION",
                "PROPERTY_ATT_RESOLUTION",
                "PROPERTY_ATT_GUZ_NAV",
                "PROPERTY_ATT_ADDRESS",
                "PROPERTY_ATT_DATE_SVEDENO",
                "PROPERTY_ATT_SNILS",
                "PROPERTY_ATT_NOTIFY_DATE",
                "PROPERTY_ATT_NOTIFY_WAY",
                "PROPERTY_ATT_DATE_ADD_DATA",
                "PROPERTY_ATT_VAC_1_DATE",
                "PROPERTY_ATT_VAC_2_DATE",
                "PROPERTY_ATT_ILLNESS_DATE",


            );

            $arResult['columns'] = [];
            $arResult['columns'][] = ['id' => 'ID', 'name' => 'ID', 'sort' => 'ID', 'default' => false];
            $arResult['columns'][] = ['id' => 'NAME', 'name' => 'ФИО  контактного', 'sort' => 'NAME', 'default' => true, 'editable' => true];
            $arResult['columns'][] = ['id' => 'ATT_EPID', 'name' => 'Эпид.анамнез', 'sort' => 'PROPERTY_ATT_EPID', 'default' => true, 'editable' => true];
            $arResult['columns'][] = ['id' => 'ATT_STEP', 'name' => 'Степень контакта', 'sort' => 'PROPERTY_ATT_STEP', 'default' => true, 'editable' => true];
            $arResult['columns'][] = ['id' => 'ATT_DATE_BIRTHDAY', 'name' => 'Дата рождения контактного', 'sort' => 'PROPERTY_ATT_DATE_BIRTHDAY', 'default' => true, 'editable' => true, 'type'=>'date'];
            $arResult['columns'][] = ['id' => 'ATT_VAC_1_DATE', 'name' => 'Дата первой вакцинации', 'sort' => 'PROPERTY_ATT_VAC_1_DATE', 'default' => true, 'editable' => true, 'type'=>'date'];
            $arResult['columns'][] = ['id' => 'ATT_VAC_2_DATE', 'name' => 'Дата второй вакцинации', 'sort' => 'PROPERTY_ATT_VAC_2_DATE', 'default' => true, 'editable' => true, 'type'=>'date'];
            $arResult['columns'][] = ['id' => 'ATT_ILLNESS_DATE', 'name' => 'Дата заболевания контактного', 'sort' => 'PROPERTY_ATT_ILLNESS_DATE', 'default' => true, 'editable' => true, 'type'=>'date'];
            $arResult['columns'][] = ['id' => 'ATT_ADDRESS', 'name' => 'Адрес проживания контактного', 'default' => true, 'editable' => true];
            $arResult['columns'][] = ['id' => 'ATT_PHONE', 'name' => 'Номер телефона', 'default' => true, 'editable' => true, 'type'=>'text'];
            $arResult['columns'][] = ['id' => 'ATT_WORK', 'name' => 'Место работы контактного', 'default' => true, 'editable' => true];
            $arResult['columns'][] = ['id' => 'ATT_POSITION', 'name' => 'Должность контактного', 'default' => true, 'editable' => true];
            $arResult['columns'][] = ['id' => 'ATT_LEGAL_REPRES', 'name' => 'ФИО больного по контакту', 'sort' => 'PROPERTY_ATT_LEGAL_REPRES', 'default' => true, 'editable' => true];
            $arResult['columns'][] = ['id' => 'ATT_ADDRESS_REPRES', 'name' => 'Адрес проживания больного по контакту', 'sort' => 'PROPERTY_ATT_ADDRESS_REPRES', 'default' => true, 'editable' => true];
            $arResult['columns'][] = ['id' => 'ATT_DATE_PERESECHENIYA', 'name' => 'Дата контакта', 'sort' => 'PROPERTY_ATT_DATE_PERESECHENIYA', 'default' => true, 'editable' => true, 'type'=>'date'];
            $arResult['columns'][] = ['id' => 'ATT_LAST_DATE', 'name' => 'Дата окончания карантина', 'sort' => 'PROPERTY_ATT_LAST_DATE', 'default' => true, 'editable' => false, 'type'=>'date'];
            $arResult['columns'][] = ['id' => 'ATT_PLACE_CONTACT', 'name' => 'Место контакта', 'default' => true, 'editable' => true];
            $arResult['columns'][] = [
              'id' => 'ATT_GUZ_NAV',
              'name' => 'ГУЗ наблюдения',
              'sort' => 'PROPERTY_ATT_GUZ_NAV',
              'default' => true,
              'editable' => ['TYPE' => 'DROPDOWN', 'items'=>$arrListGuz['ATT_GUZ_NAV']],
              'type'=>'list',
              'items'=>$arrListGuz['ATT_GUZ_NAV']
            ];
            $arResult['columns'][] = ['id' => 'ATT_DATE_QUARANT', 'name' => 'Дата начала карантина', 'sort' => 'PROPERTY_ATT_DATE_QUARANT', 'default' => true, 'editable' => true, 'type'=>'date'];
            $arResult['columns'][] = ['id' => 'ATT_INCOMING_SIDE', 'name' => 'Место изоляции (адрес)', 'default' => true, 'editable' => true];
		        $arResult['columns'][] = ['id' => 'ATT_ISOLATION_STATUS', 'name' => 'Статус изоляции', 'sort' => 'PROPERTY_ATT_ISOLATION_STATUS', 'default' => true, 'editable' => ['TYPE'=>'DROPDOWN', 'items'=>$arrListGuz['ATT_ISOLATION_STATUS']], 'type'=>'dropdown', 'items'=>$arrListGuz['ATT_ISOLATION_STATUS']];
		        $arResult['columns'][] = ['id' => 'ATT_DATE_RESOLUTION', 'name' => 'Дата вручения постановления', 'sort' => 'PROPERTY_ATT_DATE_RESOLUTION', 'default' => true, 'editable' => true, 'type'=>'date'];
            $arResult['columns'][] = ['id' => 'ATT_RESOLUTION', 'name' => 'Постановление вручено', 'sort' => 'PROPERTY_ATT_RESOLUTION', 'default' => true, 'editable' => true, 'type'=>'checkbox'];
            $arResult['columns'][] = ['id' => 'ATT_SNILS', 'name' => 'СНИЛС', 'sort' => 'PROPERTY_ATT_SNILS', 'default' => true, 'editable' => true];
            $arResult['columns'][] = ['id' => 'ATT_NOTIFY_DATE', 'name' => 'Дата оповещения', 'sort' => 'PROPERTY_ATT_NOTIFY_DATE', 'default' => true, 'editable' => true, 'type'=>'date'];
            $arResult['columns'][] = ['id' => 'ATT_NOTIFY_WAY', 'name' => 'Способ оповещения', 'sort' => 'PROPERTY_ATT_NOTIFY_WAY', 'default' => true, 'editable' => true];
		        $arResult['columns'][] = ['id' => 'ATT_DATE_ADD_DATA', 'name' => 'Дата внесения данных', 'sort' => 'PROPERTY_ATT_DATE_ADD_DATA', 'default' => true, 'editable' => true, 'type'=>'date'];



		        $arResult['FILTER_DATA'] = [];
		        $arResult['FILTER_DATA_OR'] = [];
		        $arResult['FILTER_DATA_AND'] = [];

		        if ($bollFilterLogicOr) {
				        $arResult['FILTER_DATA_AND'] = ['LOGIC' => 'AND'];
		        }
            if ($bollFilterLogicOr) {
              $arResult['FILTER_DATA_OR'] = ['LOGIC' => 'OR'];
            }

		        $filterOption = new Bitrix\Main\UI\Filter\Options($arResult['grid_id']);
		        $filterData = $filterOption->getFilter([]);

		        foreach ($filterData as $k => $v) {
				        if ($k == 'ATT_DATE_ADD_DATA_from') {
						        $dateFrom = $DB->FormatDate($v, "DD.MM.YYYY HH:MI:SS", "DD.MM.YYYY");
				        }
				        if ($k == 'ATT_DATE_ADD_DATA_to') {
						        $dateTo = $DB->FormatDate($v, "DD.MM.YYYY HH:MI:SS", "DD.MM.YYYY");
				        }

				        if ($dateFrom && $dateTo && $DB->CompareDates($dateFrom, $dateTo) == 0) {
						        $arResult['FILTER_DATA_AND']['PROPERTY_ATT_DATE_ADD_DATA'] = $DB->FormatDate($dateFrom, "DD.MM.YYYY", "YYYY-MM-DD");
				        } elseif ($dateFrom && $dateTo) {
						        $arResult['FILTER_DATA_AND']['>=PROPERTY_ATT_DATE_ADD_DATA'] = $DB->FormatDate($dateFrom, "DD.MM.YYYY", "YYYY-MM-DD");
						        $arResult['FILTER_DATA_AND']['<=PROPERTY_ATT_DATE_ADD_DATA'] = $DB->FormatDate($dateTo, "DD.MM.YYYY", "YYYY-MM-DD");
//						        unset($arResult['FILTER_DATA']['PROPERTY_ATT_DATE_ADD_DATA']);
				        }

				        if ($k == 'FIND' && $v) {
						        $arResult['FILTER_DATA_OR']['NAME']  = '%'.$v.'%';
						        $arResult['FILTER_DATA_OR']['PROPERTY_ATT_LEGAL_REPRES']  = '%'.$v.'%';
				        }

				        if ($k == 'ATT_AREA') {
				        		foreach ($v as $val) {
								        $arResult['FILTER_DATA_OR']['PROPERTY_ATT_AREA'][] = $val;
						        }
				        }
				        if ($k == 'ATT_CITY') {
						        foreach ($v as $val) {
								        $arResult['FILTER_DATA_OR']['PROPERTY_ATT_CITY'][] = $val;
						        }
				        }
				        if ($k == 'ATT_GUZ_NAV') {
						        foreach ($v as $val) {
								        $arResult['FILTER_DATA_OR']['PROPERTY_ATT_GUZ_NAV'][] = $val;
						        }
				        }
				        if ($k == 'ATT_DATE_PERESECHENIYA') {
						        if ($v == 'Y') {
								        $arResult['FILTER_DATA_OR']['>=PROPERTY_ATT_DATE_PERESECHENIYA'] = date('Y-m-d', time() - 86400 * (int)$QUARANTINE);
						        }
						        if ($v == 'N') {
								        $arResult['FILTER_DATA_OR']['<PROPERTY_ATT_DATE_PERESECHENIYA'] = date('Y-m-d', time() - 86400 * (int)$QUARANTINE);
						        }
				        }

		        }





		        $arFilter = array("IBLOCK_ID" => IBLOCK_ID_MIGRATION_DOCS, "ACTIVE" => "Y","SECTION_ID"=>SECTION_ID_MIGRATION_CONT);


		        if ($bollFilterLogicOr) {
				        $arResult['FILTER_DATA'][] = $arResult['FILTER_DATA_AND'];
				        $arResult['FILTER_DATA'][] = $arResult['FILTER_DATA_OR'];
		        }

		        $arFilter = array_merge($arFilter, $arResult['FILTER_DATA']);



		        $res = CIBlockElement::GetList($arResult['sort']['sort'], $arFilter, false, $arResult['nav_params'], $arSelect);
            $arResult['nav']->setRecordCount($res->selectedRowsCount());
            while ($arFields = $res->GetNext()) {
                $arResult['list'][] = [
                    'data' => [
                        "ID" => $arFields['ID'],
                        "NAME" => $arFields['NAME'],

                        "ATT_EPID" => $arFields['PROPERTY_ATT_EPID_VALUE'],
                        "ATT_STEP" => $arFields['PROPERTY_ATT_STEP_VALUE'],
                        "ATT_DATE_BIRTHDAY" => $arFields['PROPERTY_ATT_DATE_BIRTHDAY_VALUE'],
                        "ATT_VAC_1_DATE" => $arFields['PROPERTY_ATT_VAC_1_DATE_VALUE'],
                        "ATT_VAC_2_DATE" => $arFields['PROPERTY_ATT_VAC_2_DATE_VALUE'],
                        "ATT_ILLNESS_DATE" => $arFields['PROPERTY_ATT_ILLNESS_DATE_VALUE'],

                        "ATT_ADDRESS" => $arFields['PROPERTY_ATT_ADDRESS_VALUE'],
                        "ATT_PHONE" => format_phone($arFields['PROPERTY_ATT_PHONE_VALUE']),
                        "ATT_WORK" => $arFields['PROPERTY_ATT_WORK_VALUE'],
                        "ATT_POSITION" => $arFields['PROPERTY_ATT_POSITION_VALUE'],
                        "ATT_LEGAL_REPRES" => $arFields['PROPERTY_ATT_LEGAL_REPRES_VALUE'],
                        "ATT_ADDRESS_REPRES" => $arFields['PROPERTY_ATT_ADDRESS_REPRES_VALUE'],
                        "ATT_DATE_PERESECHENIYA" => $arFields['PROPERTY_ATT_DATE_PERESECHENIYA_VALUE'],
                        "ATT_PLACE_CONTACT" => $arFields['PROPERTY_ATT_PLACE_CONTACT_VALUE'],
                        "ATT_GUZ_NAV" => $arFields['PROPERTY_ATT_GUZ_NAV_ENUM_ID'],
                        "ATT_DATE_QUARANT" => $arFields['PROPERTY_ATT_DATE_QUARANT_VALUE'],
                        "ATT_LAST_DATE" => date("d.m.Y", strtotime($arFields['PROPERTY_ATT_DATE_PERESECHENIYA_VALUE'].$QUARANTINE_CONTACT)),
                        "ATT_INCOMING_SIDE" => $arFields['PROPERTY_ATT_INCOMING_SIDE_VALUE'],
                        "ATT_ISOLATION_STATUS" => $arFields['PROPERTY_ATT_ISOLATION_STATUS_ENUM_ID'],
                        "ATT_DATE_RESOLUTION" => ($arFields['PROPERTY_ATT_RESOLUTION_VALUE']=='Y'?date("d.m.Y", strtotime($arFields['PROPERTY_ATT_DATE_RESOLUTION_VALUE'])):''),
                        "ATT_RESOLUTION" => $arFields['PROPERTY_ATT_RESOLUTION_VALUE']=='Y'?"Y":"N",
		                    "ATT_SNILS" => $arFields['PROPERTY_ATT_SNILS_VALUE'],
		                    "ATT_NOTIFY_DATE" => $arFields['PROPERTY_ATT_NOTIFY_DATE_VALUE'],
		                    "ATT_NOTIFY_WAY" => $arFields['PROPERTY_ATT_NOTIFY_WAY_VALUE'],
		                    "ATT_DATE_ADD_DATA" => $arFields['PROPERTY_ATT_DATE_ADD_DATA_VALUE'],
                    ],
                    'columns'=>
                    [
                        "ATT_GUZ_NAV" => ($arFields['PROPERTY_ATT_GUZ_NAV_VALUE']? $arFields['PROPERTY_ATT_GUZ_NAV_VALUE']:"(Не выбрано)"),
                        "ATT_ISOLATION_STATUS" => ($arFields['PROPERTY_ATT_ISOLATION_STATUS_VALUE']? $arFields['PROPERTY_ATT_ISOLATION_STATUS_VALUE']:"(Не выбрано)"),
                    ],
                    'depth' => (strtotime($arFields['PROPERTY_ATT_DATE_PERESECHENIYA_VALUE'].$QUARANTINE)<strtotime(date("d.m.Y"))?1:0),
                    'editable' => true,
                    'actions' => [
                        [
                            'text' => 'Скачать',
                            'default' => true,
                            'onclick' => 'getFileContact(' . json_encode([
                                    'fio' => $arFields['NAME'],
                                    'phone' => format_phone($arFields['PROPERTY_ATT_PHONE_VALUE']),
                                    'data_quarant' => $arFields['PROPERTY_ATT_DATE_PERESECHENIYA_VALUE'],
                                    'birthday' => $arFields['PROPERTY_ATT_DATE_BIRTHDAY_VALUE'],
                                    'address' => $arFields['PROPERTY_ATT_ADDRESS_VALUE'],
                                    'predstavitel' => $arFields['PROPERTY_ATT_LEGAL_REPRES_VALUE'],
                                    'predstavitel_address' => $arFields['PROPERTY_ATT_ADDRESS_REPRES_VALUE'],
                                    'snils' => $arFields['PROPERTY_ATT_SNILS_VALUE'],
                                    'passport' => $arFields['PROPERTY_ATT_PASSPORT_DATA_VALUE'],
                                    'month' => $arMonth[date('n')],
                                ]) . ')',
                        ],
                    ]
                ];
            }
        }

        if ($CUR_PAGE == 'COMING_TABLE') {
            if ($_POST["FIELDS"]) {
                updateElement(SECTION_ID_MIGRATION_DOCS_COMING);
            }

            $arResult['grid_id'] = 'coming_isolation';

            $arResult['grid_options'] = new GridOptions($arResult['grid_id']);
            $arResult['sort'] = $arResult['grid_options']->GetSorting(['sort' => ['DATE_CREATE' => 'DESC'], 'vars' => ['by' => 'by', 'order' => 'order']]);
            $arResult['nav_params'] = $arResult['grid_options']->GetNavParams();


            $arResult['nav'] = new PageNavigation($arResult['grid_id']);
            $arResult['nav']->allowAllRecords(true)
                ->setPageSize($arResult['nav_params']['nPageSize'])
                ->initFromUri();
            if ($arResult['nav']->allRecordsShown()) {
                $arResult['nav_params'] = false;
            } else {
                $arResult['nav_params']['iNumPage'] = $arResult['nav']->getCurrentPage();
            }

            $arSelect = array(
                "ID",
                "IBLOCK_ID",
                "NAME",
                "DATE_ACTIVE_FROM",
                "PROPERTY_ATT_NAME",
                "PROPERTY_ATT_SEX",
                "PROPERTY_ATT_DATE_SVEDENO",
                "PROPERTY_ATT_DATE_PERESECHENIYA",
                "PROPERTY_ATT_COUNTRY",
                "PROPERTY_ATT_PHONE",
                "PROPERTY_ATT_PASSPORT",
                "PROPERTY_ATT_ADDRESS",
                "PROPERTY_ATT_LEGAL_REPRES",
                "PROPERTY_ATT_LEGAL_REPRES_DATE",
                "PROPERTY_ATT_ADDRESS_REPRES",
                "PROPERTY_ATT_DATE_RESOLUTION",
                "PROPERTY_ATT_RESOLUTION",
                "PROPERTY_ATT_GUZ_HOSP",
                "PROPERTY_ATT_GUZ_NAV",
                "PROPERTY_ATT_LAST_DATE",
            );

            $arResult['columns'] = [];
            $arResult['columns'][] = ['id' => 'ID', 'name' => 'ID', 'sort' => 'ID', 'default' => false];
            $arResult['columns'][] = ['id' => 'NAME', 'name' => 'Фамилия', 'sort' => 'NAME', 'default' => true, 'editable' => true];
            $arResult['columns'][] = ['id' => 'ATT_NAME', 'name' => 'Именные компоненты', 'sort' => 'PROPERTY_ATT_NAME', 'default' => true, 'editable' => true];
            $arResult['columns'][] = ['id' => 'ATT_SEX', 'name' => 'Пол', 'sort' => 'PROPERTY_ATT_SEX', 'default' => true, 'editable' => true];
            $arResult['columns'][] = ['id' => 'ATT_DATE_SVEDENO', 'name' => 'Дата рождения', 'sort' => 'PROPERTY_ATT_DATE_SVEDENO', 'default' => true, 'editable' => true, 'type'=>'date'];
            $arResult['columns'][] = ['id' => 'ATT_DATE_PERESECHENIYA', 'name' => 'Дата пересечения', 'sort' => 'PROPERTY_ATT_DATE_PERESECHENIYA', 'default' => true, 'editable' => true, 'type'=>'date'];
            $arResult['columns'][] = ['id' => 'ATT_COUNTRY', 'name' => 'Регион отбытия', 'sort' => 'PROPERTY_ATT_COUNTRY', 'default' => true, 'editable' => true];
            $arResult['columns'][] = ['id' => 'ATT_PASSPORT', 'name' => 'Данные паспорт-центр', 'default' => true, 'editable' => true, 'type'=>'text'];
            $arResult['columns'][] = ['id' => 'ATT_PHONE', 'name' => 'Номер телефона', 'default' => true, 'editable' => true, 'type'=>'text'];
            $arResult['columns'][] = ['id' => 'ATT_ADDRESS', 'name' => 'Адрес', 'default' => true, 'editable' => true];
            $arResult['columns'][] = ['id' => 'ATT_GUZ_NAV', 'name' => 'ГУЗ наблюдения', 'sort' => 'PROPERTY_ATT_GUZ_NAV', 'default' => true, 'editable' => ['TYPE'=>'DROPDOWN', 'items'=>$arrListGuz['ATT_GUZ_NAV']], 'type'=>'dropdown', 'items'=>$arrListGuz['ATT_GUZ_NAV']];
            $arResult['columns'][] = ['id' => 'ATT_GUZ_HOSP', 'name' => 'ГУЗ госпитализация', 'sort' => 'PROPERTY_GUZ_HOSP', 'default' => true, 'editable' => ['TYPE'=>'DROPDOWN', 'items'=>$arrListGuz['ATT_GUZ_HOSP']], 'type'=>'dropdown', 'items'=>$arrListGuz['ATT_GUZ_HOSP']];
            $arResult['columns'][] = ['id' => 'ATT_LEGAL_REPRES', 'name' => 'Законный представитель', 'sort' => 'PROPERTY_ATT_LEGAL_REPRES', 'default' => true, 'editable' => true];
            $arResult['columns'][] = ['id' => 'ATT_LEGAL_REPRES_DATE', 'name' => 'Дата рождения представителя', 'sort' => 'PROPERTY_ATT_LEGAL_REPRES_DATE', 'default' => true, 'editable' => true, 'type'=>'date'];
            $arResult['columns'][] = ['id' => 'ATT_ADDRESS_REPRES', 'name' => 'Адрес представителя', 'sort' => 'PROPERTY_ATT_ADDRESS_REPRES', 'default' => true, 'editable' => true];
            $arResult['columns'][] = ['id' => 'ATT_LAST_DATE', 'name' => 'Дата окончания карантина', 'sort' => 'PROPERTY_ATT_DATE_PERESECHENIYA', 'default' => true, 'editable' => false, 'type'=>'date'];
            $arResult['columns'][] = ['id' => 'ATT_DATE_RESOLUTION', 'name' => 'Дата вручения постановления', 'sort' => 'PROPERTY_ATT_DATE_RESOLUTION', 'default' => true, 'editable' => true, 'type'=>'date'];
            $arResult['columns'][] = ['id' => 'ATT_RESOLUTION', 'name' => 'Постановление вручено', 'sort' => 'PROPERTY_ATT_RESOLUTION', 'default' => true, 'editable' => true, 'type'=>'checkbox'];

            $arFilter = array("IBLOCK_ID" => IBLOCK_ID_MIGRATION_DOCS, "ACTIVE" => "Y","SECTION_ID"=>SECTION_ID_MIGRATION_DOCS_COMING, $filterSearch);


            $res = CIBlockElement::GetList($arResult['sort']['sort'], $arFilter, false, $arResult['nav_params'], $arSelect);
            $arResult['nav']->setRecordCount($res->selectedRowsCount());
            while ($arFields = $res->GetNext()) {
                $strLastDate = $arFields['PROPERTY_ATT_LAST_DATE_VALUE'] ?? date("d.m.Y", strtotime($arFields['PROPERTY_ATT_DATE_PERESECHENIYA_VALUE'].$QUARANTINE));

                $arResult['list'][] = [
                    'data' => [
                        "ID" => $arFields['ID'],
                        "NAME" => $arFields['NAME'],
                        "ATT_NAME" => $arFields['PROPERTY_ATT_NAME_VALUE'],
                        "ATT_SEX" => $arFields['PROPERTY_ATT_SEX_VALUE'],
                        "ATT_DATE_SVEDENO" => $arFields['PROPERTY_ATT_DATE_SVEDENO_VALUE'],
                        "ATT_DATE_PERESECHENIYA" => $arFields['PROPERTY_ATT_DATE_PERESECHENIYA_VALUE'],
                        "ATT_COUNTRY" => $arFields['PROPERTY_ATT_COUNTRY_VALUE'],
                        "ATT_PASSPORT" => $arFields['PROPERTY_ATT_PASSPORT_VALUE'],
                        "ATT_PHONE" => format_phone($arFields['PROPERTY_ATT_PHONE_VALUE']),
                        "ATT_ADDRESS" => $arFields['PROPERTY_ATT_ADDRESS_VALUE'],
                        "ATT_GUZ_NAV" => $arFields['PROPERTY_ATT_GUZ_NAV_ENUM_ID'],
                        "ATT_GUZ_HOSP" => $arFields['PROPERTY_ATT_GUZ_HOSP_ENUM_ID'],
                        "ATT_LEGAL_REPRES" => $arFields['PROPERTY_ATT_LEGAL_REPRES_VALUE'],
                        "ATT_LEGAL_REPRES_DATE" => $arFields['PROPERTY_ATT_LEGAL_REPRES_DATE_VALUE'],
                        "ATT_LAST_DATE" => $strLastDate,
                        "ATT_ADDRESS_REPRES" => $arFields['PROPERTY_ATT_ADDRESS_REPRES_VALUE'],
                        "ATT_DATE_RESOLUTION" => ($arFields['PROPERTY_ATT_RESOLUTION_VALUE']=='Y'?date("d.m.Y", strtotime($arFields['PROPERTY_ATT_DATE_RESOLUTION_VALUE'])):''),
                        "ATT_RESOLUTION" => $arFields['PROPERTY_ATT_RESOLUTION_VALUE']=='Y'?"Y":"N",
                    ],
                    'columns'=>
                        [
                            "ATT_GUZ_NAV" => ($arFields['PROPERTY_ATT_GUZ_NAV_VALUE']? $arFields['PROPERTY_ATT_GUZ_NAV_VALUE']:"(Не выбрано)"),
                            "ATT_GUZ_HOSP" => ($arFields['PROPERTY_ATT_GUZ_HOSP_VALUE']? $arFields['PROPERTY_ATT_GUZ_HOSP_VALUE']:"(Не выбрано)"),
                        ],
                    'depth' => (strtotime($arFields['PROPERTY_ATT_DATE_PERESECHENIYA_VALUE'].$QUARANTINE)<strtotime(date("d.m.Y"))?1:0),
                    'editable' => true,
                    'actions' => [
                        [
                            'text' => 'Скачать',
                            'default' => true,
                            'onclick' => 'getFileComing(' . json_encode([
                                    'fio' => $arFields['NAME'],
                                    'name' => $arFields['PROPERTY_ATT_NAME_VALUE'],
                                    'birthday' => $arFields['PROPERTY_ATT_DATE_SVEDENO_VALUE'],
                                    'data_peresecheniya' => $arFields['PROPERTY_ATT_DATE_PERESECHENIYA_VALUE'],
                                    'date_end' => $strLastDate,
                                    'address' => $arFields['PROPERTY_ATT_ADDRESS_VALUE'],
                                    'predstavitel' => $arFields['PROPERTY_ATT_LEGAL_REPRES_VALUE'],
                                    'predstavitel_year' => $arFields['PROPERTY_ATT_LEGAL_REPRES_DATE_VALUE'],
                                    'predstavitel_address' => $arFields['PROPERTY_ATT_ADDRESS_REPRES_VALUE'],
                                    'month' => $arMonth[date('n')],

                                ]) . ')',
                        ],
                    ]
                ];
            }
        }

        if ($CUR_PAGE == 'VIOLATORS_TABLE') {
            $arResult['UI_FILTER'] = [
                [
                    'id' => 'UF_DATE_VIOLATION',
                    'name' => 'Дата нарушения',
                    'type'=>'date',
                    'default' => true,
                    "exclude" => array(
                        \Bitrix\Main\UI\Filter\DateType::LAST_7_DAYS,
                        \Bitrix\Main\UI\Filter\DateType::LAST_30_DAYS,
                        \Bitrix\Main\UI\Filter\DateType::LAST_60_DAYS,
                        \Bitrix\Main\UI\Filter\DateType::LAST_90_DAYS,
                        \Bitrix\Main\UI\Filter\DateType::CURRENT_MONTH,
                        \Bitrix\Main\UI\Filter\DateType::CURRENT_QUARTER,
                        \Bitrix\Main\UI\Filter\DateType::CURRENT_WEEK,
                        \Bitrix\Main\UI\Filter\DateType::LAST_MONTH,
                        \Bitrix\Main\UI\Filter\DateType::LAST_WEEK,
                        \Bitrix\Main\UI\Filter\DateType::MONTH,
                        \Bitrix\Main\UI\Filter\DateType::NEXT_MONTH,
                        \Bitrix\Main\UI\Filter\DateType::NEXT_DAYS,
                        \Bitrix\Main\UI\Filter\DateType::YEAR,
                        \Bitrix\Main\UI\Filter\DateType::NEXT_WEEK,
                        \Bitrix\Main\UI\Filter\DateType::TOMORROW,
                        \Bitrix\Main\UI\Filter\DateType::QUARTER,
                    )
                ],
            ];

            if ($_POST["FIELDS"]) {
                updateElementHLB();
            }

            $arResult['grid_id'] = 'violators_s';

            $arResult['grid_options'] = new GridOptions($arResult['grid_id']);
            $arResult['sort'] = $arResult['grid_options']->GetSorting(['sort' => ['ID' => 'ASC'], 'vars' => ['by' => 'by', 'order' => 'order']]);
            $arResult['nav_params'] = $arResult['grid_options']->GetNavParams();


            $arResult['nav'] = new PageNavigation($arResult['grid_id']);
            $arResult['nav']->allowAllRecords(true)
                ->setPageSize($arResult['nav_params']['nPageSize'])
                ->initFromUri();
            if ($arResult['nav']->allRecordsShown()) {
                $arResult['nav_params'] = false;
            } else {
                $arResult['nav_params']['iNumPage'] = $arResult['nav']->getCurrentPage();
            }


            $rsUField = CUserFieldEnum::GetList(array(), array("USER_FIELD_NAME" => 'UF_DATA_TYPE'));

            while ($arUField = $rsUField->GetNext()) {
                $arResult['UF_DATA_TYPE'][$arUField['ID']] = $arUField['VALUE'];
            }

            $arResult['columns'] = [];
            $arResult['columns'][] = ['id' => 'ID', 'name' => 'ID', 'sort' => 'ID', 'default' => false];
            $arResult['columns'][] = ['id' => 'LAST_NAME', 'name' => 'Фамилия', 'default' => true, 'editable' => true];
            $arResult['columns'][] = ['id' => 'FIRST_SECOND_NAME', 'name' => 'Имя Отчество', 'default' => true, 'editable' => true];
            $arResult['columns'][] = ['id' => 'UF_ADDRESS', 'name' => 'Адрес из системы', 'sort' => 'UF_ADDRESS', 'default' => true, 'editable' => true];
            $arResult['columns'][] = ['id' => 'UF_PHONE', 'name' => 'Номер телефона', 'sort' => 'UF_PHONE', 'default' => true, 'editable' => true];
            $arResult['columns'][] = ['id' => 'UF_DATE_BIRTHDAY', 'name' => 'Дата рождения', 'sort' => 'UF_DATE_BIRTHDAY', 'default' => true, 'editable' => true, 'type'=>'date'];
            $arResult['columns'][] = ['id' => 'UF_DATE_VIOLATION', 'name' => 'Дата нарушения', 'sort' => 'UF_DATE_VIOLATION', 'default' => true, 'editable' => true, 'type'=>'date'];

            $arResult['columns'][] = ['id' => 'UF_REASON_ISOLATION', 'name' => 'Причина изоляции', 'sort' => 'UF_REASON_ISOLATION', 'default' => true, 'editable' => true, 'type'=>'date'];
            $arResult['columns'][] = ['id' => 'UF_OPERATOR', 'name' => 'Оператор', 'sort' => 'UF_OPERATOR', 'default' => true, 'editable' => true, 'type'=>'date'];

            $arResult['columns'][] = ['id' => 'UF_ADDRESS_VIOLATION', 'name' => 'Адрес нарушения', 'sort' => 'UF_ADDRESS_VIOLATION', 'default' => true, 'editable' => true];
            $arResult['columns'][] = ['id' => 'UF_COORDINATES', 'name' => 'Координаты нарушения', 'sort' => 'UF_COORDINATES', 'default' => true, 'editable' => true];


            $arResult['FILTER_DATA'] = [];
            $filterOption = new Bitrix\Main\UI\Filter\Options($arResult['grid_id']);
            $filterData = $filterOption->getFilter([]);

            foreach ($filterData as $k => $v) {
                if ($k == 'UF_DATE_VIOLATION_from') {
                    $dateFrom = $DB->FormatDate($v, "DD.MM.YYYY HH:MI:SS", "DD.MM.YYYY");
                }
                if ($k == 'UF_DATE_VIOLATION_to') {
                    $dateTo = $DB->FormatDate($v, "DD.MM.YYYY HH:MI:SS", "DD.MM.YYYY");
                }

                if ($dateFrom && $dateTo && $DB->CompareDates($dateFrom, $dateTo) == 0) {
                    $arResult['FILTER_DATA']['UF_DATE_VIOLATION'] = $dateFrom;
                } elseif ($dateFrom && $dateTo) {
                    $arResult['FILTER_DATA']['>=UF_DATE_VIOLATION'] = $dateFrom;
                    $arResult['FILTER_DATA']['<=UF_DATE_VIOLATION'] = $dateTo;
                    unset($arResult['FILTER_DATA']['UF_DATE_VIOLATION']);
                }

                if ($k == 'FIND' && $v) {
                    $arResult['FILTER_DATA']['UF_FIO'] = '%'.$v.'%';
                }
            }

            $hlblock = HL\HighloadBlockTable::getById(HLBLOCK_ID_VIOLATORS)->fetch();
            $entity = HL\HighloadBlockTable::compileEntity($hlblock);
            $entity_data_class = $entity->getDataClass();
            $intTotalCount = $entity_data_class::getCount();


            $rsData = $entity_data_class::getList(array(
                "select" => array("*"),
                "order" => $arResult['sort']['sort'],
                "filter" => $arResult['FILTER_DATA'],
                "limit" => $arResult['nav_params']['nPageSize'],
                "offset" => $arResult['nav']->getOffset()
            ));


            $arResult['nav']->setRecordCount($intTotalCount);

            while ($arData = $rsData->Fetch()) {
                $rsEnumDataType = CUserFieldEnum::GetList(array(), array("ID" =>$arData["UF_DATA_TYPE"]));
                $arEnumDataType = $rsEnumDataType->GetNext();

                $arDataTypeM = [$arEnumDataType['ID'] => $arEnumDataType['VALUE']] + $arResult['UF_DATA_TYPE'];


                $arResult['list'][] = [

                    'data' => [
                        'ID' => $arData['ID'],
                        'LAST_NAME' => explode(' ', $arData['UF_FIO'])[0],
                        'FIRST_SECOND_NAME' => explode(' ', $arData['UF_FIO'])[1] . ' ' . explode(' ', $arData['UF_FIO'])[2],
                        'UF_ADDRESS' => $arData['UF_ADDRESS'],
                        'UF_PHONE' => format_phone($arData['UF_PHONE']),
                        'UF_DATE_BIRTHDAY' => $arData['UF_DATE_BIRTHDAY'],
                        'UF_DATE_VIOLATION' => $arData['UF_DATE_VIOLATION'],
                        'UF_ADDRESS_VIOLATION' => $arData['UF_ADDRESS_VIOLATION'],
                        'UF_REASON_ISOLATION' => $arData['UF_REASON_ISOLATION'],
                        'UF_OPERATOR' => $arData['UF_OPERATOR'],
                        'UF_COORDINATES' => $arData['UF_COORDINATES'],
                        'UF_DATA_TYPE' => $arEnumDataType['VALUE'],
                    ],
                    'editable' => false,
                    'parent_id' => '0',
                    'has_child' => true,

                ];



                $arResult['columns'][count($arResult['columns'])] = [
                    'id' => 'UF_DATA_TYPE',
                    'name' => 'Тип найденных данных',
                    'sort' => 'UF_DATA_TYPE',
                    'default' => true,
                    'editable' => true,
                    'type' => 'dropdown',
                    'items' => $arDataTypeM
                ];
            }
        }

        if ($CUR_PAGE == 'VIOLATORS_CCMIS_TABLE') {
            $arResult['grid_id'] = 'violators_ccmis';

            $arResult['grid_options'] = new GridOptions($arResult['grid_id']);
            $arResult['sort'] = $arResult['grid_options']->GetSorting(['sort' => ['ID' => 'ASC'], 'vars' => ['by' => 'by', 'order' => 'order']]);
            $arResult['nav_params'] = $arResult['grid_options']->GetNavParams();


            $arResult['nav'] = new PageNavigation($arResult['grid_id']);
            $arResult['nav']->allowAllRecords(true)
                ->setPageSize($arResult['nav_params']['nPageSize'])
                ->initFromUri();
            if ($arResult['nav']->allRecordsShown()) {
                $arResult['nav_params'] = false;
            } else {
                $arResult['nav_params']['iNumPage'] = $arResult['nav']->getCurrentPage();
            }

            $rsUField = CUserFieldEnum::GetList(array(), array("USER_FIELD_NAME" => 'UF_TYPE'));

            while ($arUField = $rsUField->GetNext()) {
                $arResult['UF_TYPE'][$arUField['ID']] = $arUField['VALUE'];
            }

            $arResult['columns'] = [];
            $arResult['columns'][] = ['id' => 'ID', 'name' => 'ID', 'sort' => 'ID', 'default' => false];
            $arResult['columns'][] = ['id' => 'LAST_NAME', 'name' => 'Фамилия', 'sort' => 'LAST_NAME', 'default' => true, 'editable' => true];
            $arResult['columns'][] = ['id' => 'FIRST_SECOND_NAME', 'name' => 'Имя Отчество', 'sort' => 'FIRST_SECOND_NAME', 'default' => true, 'editable' => true];
            $arResult['columns'][] = ['id' => 'UF_DATE_BIRTHDAY', 'name' => 'Дата рождения', 'sort' => 'UF_DATE_BIRTHDAY', 'default' => true, 'editable' => true, 'type'=>'date'];
            $arResult['columns'][] = ['id' => 'UF_ADDRESS', 'name' => 'Адрес', 'sort' => 'UF_ADDRESS', 'default' => true, 'editable' => true];
            $arResult['columns'][] = ['id' => 'UF_PHONE', 'name' => 'Телефон', 'sort' => 'UF_PHONE', 'default' => true, 'editable' => true];

            $arResult['FILTER_DATA'] = [];
            $filterOption = new Bitrix\Main\UI\Filter\Options($arResult['grid_id']);
            $filterData = $filterOption->getFilter([]);

            foreach ($filterData as $k => $v) {
                if ($k == 'FIND' && $v) {
                    $arResult['FILTER_DATA']['UF_FIO'] = '%'.$v.'%';
                }
            }

            $hlblock = HL\HighloadBlockTable::getById(HLBLOCK_ID_VIOLATORS_CCMIS)->fetch();
            $entity = HL\HighloadBlockTable::compileEntity($hlblock);
            $entity_data_class = $entity->getDataClass();
            $intTotalCount = $entity_data_class::getCount();

            $rsData = $entity_data_class::getList(array(
                "select" => array("*"),
                "order" => $arResult['sort']['sort'],
                "filter" => $arResult['FILTER_DATA'],
                "limit" => $arResult['nav_params']['nPageSize'],
                "offset" => $arResult['nav']->getOffset()
            ));

            $arResult['nav']->setRecordCount($intTotalCount);

            while ($arData = $rsData->Fetch()){
                $rsEnumDataType = CUserFieldEnum::GetList(array(), array("ID" => $arData["UF_TYPE"]));
                $arEnumDataType = $rsEnumDataType->GetNext();
                $arDataTypeM = [$arEnumDataType['ID'] => $arEnumDataType['VALUE']] + $arResult['UF_TYPE'];

                $arResult['list'][] = [

                    'data' => [
                        'ID' => $arData['ID'],
                        'LAST_NAME' => explode(' ', $arData['UF_FIO'])[0],
                        'FIRST_SECOND_NAME' => explode(' ', $arData['UF_FIO'])[1] . ' ' . explode(' ', $arData['UF_FIO'])[2],
                        'UF_ADDRESS' => $arData['UF_ADDRESS'],
                        'UF_PHONE' => format_phone($arData['UF_PHONE']),
                        'UF_DATE_BIRTHDAY' => $arData['UF_DATE_BIRTHDAY'],
                        'UF_TYPE' => $arEnumDataType['VALUE'],
                    ],
                    'editable' => false,
                    'parent_id' => '0',
                    'has_child' => true,

                ];

                $arResult['columns'][count($arResult['columns'])] = [
                    'id' => 'UF_TYPE',
                    'name' => 'Тип',
                    'sort' => 'UF_TYPE',
                    'default' => true,
                    'editable' => true,
                    'type' => 'dropdown',
                    'items' => $arDataTypeM
                ];
            }
        }

		    if ($CUR_PAGE == 'ARRIVED_TABLE') {
				    if ($_POST["FIELDS"]) {
						    updateElement(SECTION_ID_MIGRATION_DOCS_ARRIVED);
				    }
				    $arResult['grid_id'] = 'arrived_isolation';

				    $arResult['grid_options'] = new GridOptions($arResult['grid_id']);
				    $arResult['sort'] = $arResult['grid_options']->GetSorting(['sort' => ['DATE_CREATE' => 'DESC'], 'vars' => ['by' => 'by', 'order' => 'order']]);
				    $arResult['nav_params'] = $arResult['grid_options']->GetNavParams();


				    $arResult['nav'] = new PageNavigation($arResult['grid_id']);
				    $arResult['nav']->allowAllRecords(true)
						    ->setPageSize($arResult['nav_params']['nPageSize'])
						    ->initFromUri();
				    if ($arResult['nav']->allRecordsShown()) {
						    $arResult['nav_params'] = false;
				    } else {
						    $arResult['nav_params']['iNumPage'] = $arResult['nav']->getCurrentPage();
				    }

				    $arSelect = array(
						    "ID",
						    "IBLOCK_ID",
						    "NAME",
						    "DATE_ACTIVE_FROM",
						    "PROPERTY_ATT_LAST_NAME",
						    "PROPERTY_ATT_NAME",
						    "PROPERTY_ATT_DATE_BIRTHDAY",
						    "PROPERTY_ATT_CITIZENSHIP",
						    "PROPERTY_ATT_ARRIVED_COUNTRY",
						    "PROPERTY_ATT_ARRIVED_DATE",
						    "PROPERTY_ATT_PHONE",
						    "PROPERTY_ATT_ADDRESS",
						    "PROPERTY_ATT_PARENT_FIO",
						    "PROPERTY_ATT_TOWN",
						    "PROPERTY_ATT_INFO_KIND",
						    "PROPERTY_ATT_TEST_DATE",
						    "PROPERTY_ATT_TEST_RESULT",
						    "PROPERTY_ATT_LEG_SERVICE_DATE",
						    "PROPERTY_ATT_UVD_DATE",
						    "PROPERTY_ATT_SMS_SEND",
						    "PROPERTY_ATT_OWNER_INFO",
						    "PROPERTY_ATT_KIND_INFO",
						    "PROPERTY_ATT_UVD_RESULTS",
						    "PROPERTY_ATT_PASSPORT_DATA",
						    "PROPERTY_ATT_NOTE",
				    );

				    $arResult['columns'] = [];
				    $arResult['columns'][] = ['id' => 'ID', 'name' => 'ID', 'sort' => 'ID', 'default' => false];
				    $arResult['columns'][] = ['id' => 'NAME', 'name' => 'Фамилия', 'sort' => 'NAME', 'default' => true, 'editable' => false];
				    $arResult['columns'][] = ['id' => 'ATT_NAME', 'name' => 'Имя Отчество', 'sort' => 'PROPERTY_ATT_NAME', 'default' => true, 'editable' => false];
				    $arResult['columns'][] = ['id' => 'ATT_DATE_BIRTHDAY', 'name' => 'Дата рождения', 'sort' => 'PROPERTY_ATT_DATE_BIRTHDAY', 'default' => true, 'editable' => false, 'type'=>'date'];
				    $arResult['columns'][] = ['id' => 'ATT_CITIZENSHIP', 'name' => 'Гражданство', 'sort' => 'PROPERTY_ATT_CITIZENSHIP', 'default' => true, 'editable' => false];
				    $arResult['columns'][] = ['id' => 'ATT_ARRIVED_COUNTRY', 'name' => 'Страна прибытия', 'sort' => 'PROPERTY_ATT_ARRIVED_COUNTRY', 'default' => true, 'editable' => false];
				    $arResult['columns'][] = ['id' => 'ATT_ARRIVED_DATE', 'name' => 'Дата прибытия / дата контакта', 'sort' => 'PROPERTY_ATT_ARRIVED_DATE', 'default' => true, 'editable' => false, 'type'=>'date'];
				    $arResult['columns'][] = ['id' => 'ATT_PHONE', 'name' => 'Номер телефона', 'default' => true, 'editable' => false, 'type'=>'text'];
				    $arResult['columns'][] = ['id' => 'ATT_ADDRESS', 'name' => 'Адрес', 'default' => true, 'editable' => false];
				    $arResult['columns'][] = ['id' => 'ATT_PARENT_FIO', 'name' => 'Родители', 'sort' => 'PROPERTY_ATT_PARENT_FIO', 'default' => true, 'editable' => true];
				    $arResult['columns'][] = ['id' => 'ATT_TOWN', 'name' => 'Город', 'sort' => 'PROPERTY_ATT_TOWN', 'default' => true, 'editable' => true];
				    $arResult['columns'][] = ['id' => 'ATT_INFO_KIND', 'name' => 'Как получены сведения об обследовании (ЕПГУ, анкета, лично)', 'sort' => 'PROPERTY_ATT_INFO_KIND', 'default' => true, 'editable' => true];
				    $arResult['columns'][] = ['id' => 'ATT_TEST_DATE', 'name' => 'Дата выполнения теста', 'sort' => 'PROPERTY_ATT_TEST_DATE', 'default' => true, 'editable' => true, 'type'=>'date'];
				    $arResult['columns'][] = ['id' => 'ATT_TEST_RESULT', 'name' => 'Гражданство', 'sort' => 'PROPERTY_ATT_TEST_RESULT', 'default' => true, 'editable' => true];
				    $arResult['columns'][] = ['id' => 'ATT_LEG_SERVICE_DATE', 'name' => 'Дата передачи в юрид службу', 'sort' => 'PROPERTY_ATT_LEG_SERVICE_DATE', 'default' => true, 'editable' => true, 'type'=>'date'];
				    $arResult['columns'][] = ['id' => 'ATT_UVD_DATE', 'name' => 'Дата передачи в УВД', 'sort' => 'PROPERTY_ATT_UVD_DATE', 'default' => true, 'editable' => true, 'type'=>'date'];
				    $arResult['columns'][] = ['id' => 'ATT_SMS_SEND', 'name' => 'Передано СМС', 'sort' => 'PROPERTY_ATT_SMS_SEND', 'default' => true, 'editable' => true, 'type'=>'checkbox'];
				    $arResult['columns'][] = ['id' => 'ATT_OWNER_INFO', 'name' => 'Кто передал данные', 'sort' => 'PROPERTY_ATT_OWNER_INFO', 'default' => true, 'editable' => true];
				    $arResult['columns'][] = ['id' => 'ATT_KIND_INFO', 'name' => 'От кого получены сведения(анкета, мвд)', 'sort' => 'PROPERTY_ATT_KIND_INFO', 'default' => true, 'editable' => true];
				    $arResult['columns'][] = ['id' => 'ATT_UVD_RESULTS', 'name' => 'Результаты из УВД', 'sort' => 'PROPERTY_ATT_UVD_RESULTS', 'default' => true, 'editable' => true];
				    $arResult['columns'][] = ['id' => 'ATT_PASSPORT_DATA', 'name' => 'Паспортные данные', 'sort' => 'PROPERTY_ATT_PASSPORT_DATA', 'default' => true, 'editable' => true];
				    $arResult['columns'][] = ['id' => 'ATT_NOTE', 'name' => 'Примечание', 'sort' => 'PROPERTY_ATT_NOTE', 'default' => true, 'editable' => true];


				    $filterOption = new Bitrix\Main\UI\Filter\Options($arResult['grid_id']);

				    $arFilter = $filterOption->getFilter([]);

				    foreach ($arFilter as $k => $v) {

						    $arFilter['ID'] = $arFilter['ID_VAL'];
						    if ($arFilter['FIND'] != '') {
								    $arFilter['NAME'] = "%".$arFilter['FIND']."%";
						    } else {
								    $arFilter['NAME'] = "%".$arFilter['NAME']."%";
						    }

				    }

				    $arFilter['IBLOCK_ID'] = IBLOCK_ID_MIGRATION_DOCS;
				    $arFilter['ACTIVE'] = "Y";
				    $arFilter['SECTION_ID'] = SECTION_ID_MIGRATION_DOCS_ARRIVED;

				    $res = CIBlockElement::GetList($arResult['sort']['sort'], $arFilter, false, $arResult['nav_params'], $arSelect);
				    $arResult['nav']->setRecordCount($res->selectedRowsCount());
				    while ($arFields = $res->GetNext()) {
						    $arResult['list'][] = [
								    'data' => [
										    "ID" => $arFields['ID'],
										    "NAME" => $arFields['NAME'],
										    "ATT_NAME" => $arFields['PROPERTY_ATT_NAME_VALUE'],
										    "ATT_DATE_BIRTHDAY" => $arFields['PROPERTY_ATT_DATE_BIRTHDAY_VALUE'],
										    "ATT_CITIZENSHIP" => $arFields['PROPERTY_ATT_CITIZENSHIP_VALUE'],
										    "ATT_ARRIVED_COUNTRY" => $arFields['PROPERTY_ATT_ARRIVED_COUNTRY_VALUE'],
										    "ATT_ARRIVED_DATE" => $arFields['PROPERTY_ATT_ARRIVED_DATE_VALUE'],
										    "ATT_PHONE" => format_phone($arFields['PROPERTY_ATT_PHONE_VALUE']),
										    "ATT_ADDRESS" => $arFields['PROPERTY_ATT_ADDRESS_VALUE'],
										    "ATT_PARENT_FIO" => $arFields['PROPERTY_ATT_PARENT_FIO_VALUE'],
										    "ATT_TOWN" => $arFields['PROPERTY_ATT_TOWN_VALUE'],
										    "ATT_INFO_KIND" => $arFields['PROPERTY_ATT_INFO_KIND_VALUE'],
										    "ATT_TEST_DATE" => $arFields['PROPERTY_ATT_TEST_DATE_VALUE'],
										    "ATT_TEST_RESULT" => $arFields['PROPERTY_ATT_TEST_RESULT_VALUE'],
										    "ATT_LEG_SERVICE_DATE" => $arFields['PROPERTY_ATT_LEG_SERVICE_DATE_VALUE'],
										    "ATT_UVD_DATE" => $arFields['PROPERTY_ATT_UVD_DATE_VALUE'],
										    "ATT_SMS_SEND" => $arFields['PROPERTY_ATT_SMS_SEND_VALUE'],
										    "ATT_OWNER_INFO" => $arFields['PROPERTY_ATT_OWNER_INFO_VALUE'],
										    "ATT_KIND_INFO" => $arFields['PROPERTY_ATT_KIND_INFO_VALUE'],
										    "ATT_UVD_RESULTS" => $arFields['PROPERTY_ATT_UVD_RESULTS_VALUE'],
										    "ATT_PASSPORT_DATA" => $arFields['PROPERTY_ATT_PASSPORT_DATA_VALUE'],
										    "ATT_NOTE" => $arFields['PROPERTY_ATT_NOTE_VALUE'],
								    ],
								    'editable' => true,

						    ];
				    }
		    }
    }

    // saving template name to cache array
    $arResult["__TEMPLATE_FOLDER"] = $this->__folder;
    // writing new $arResult to cache file
    $obCache_trade->EndDataCache($arResult);
}

$this->__component->arResult = $arResult;
