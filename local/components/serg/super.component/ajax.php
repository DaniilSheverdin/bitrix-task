<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
require $_SERVER["DOCUMENT_ROOT"] . "/local/vendor/autoload.php";
require $_SERVER['DOCUMENT_ROOT']. '/local/components/serg/super.component/templates/migration.docs/lib/DadataHelper.php';



use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\Request;
use Bitrix\Highloadblock as HL;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;


Loader::includeModule('citto.integration');
use Citto\Integration\Docx;


class ValidationFormAjaxController extends Controller
{
    /**
     * @param Request|null $request
     * @throws LoaderException
     */
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        Loader::includeModule('iblock');
    }

    private $DADATA_KEY = '202ef02ba212fda90bb83c1957d4f84c1d14aea8';

    /**
     * @return array
     */
    public function configureActions(): array
    {
        return [
            'createFile' => [
                'prefilters' => []
            ],
            'createFileComing' => [
                'prefilters' => []
            ],
            'createFileMZH' => [
                'prefilters' => []
            ],
            'createFileContact' => [
                'prefilters' => []
            ],
            'getTable' => [
                'prefilters' => []
            ],
		        'getTableContact' => [
                'prefilters' => []
            ],

            'getTableViolation' => [
                'prefilters' => []
            ],
            'addElementContact' => [
                'prefilters' => []
            ],
            'getTableAllContact' => [
                  'prefilters' => []
              ],


        ];
    }


		/**
		 * @param $arProperties
		 * @param $enumCode Array
		 * @param $iblockID
		 * @param $kind Array
		 */
		function addAddressComponents(&$arProperties, $enumCode, $iblockID, $kind) {

				$params = array(
						"max_len" => "100", // обрезает символьный код до 100 символов
						"change_case" => "L", // буквы преобразуются к нижнему регистру
						"replace_space" => "_", // меняем пробелы на нижнее подчеркивание
						"replace_other" => "_", // меняем левые символы на нижнее подчеркивание
						"delete_repeat_replace" => "true", // удаляем повторяющиеся нижние подчеркивания
						"use_google" => "false", // отключаем использование google
				);

				$arArea = DaDataHelper::getArea($this->DADATA_KEY, $arProperties['ATT_ADDRESS']);

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
										$arProperties[$enum] = $enum_fields['ID'];
								}

						} else {
								$rsEnum = new \CIBlockPropertyEnum();
								$valueId = $rsEnum->Add([
										'PROPERTY_ID' => $propArea['ID'],
										'VALUE' => $arArea[$kind[$key]],
										'XML_ID' => CUtil::translit(strtolower($arArea[$kind[$key]]), "ru", $params)
								]);

								$arProperties[$enum] = $valueId;
						}
				}
		}


    public function createFileAction(
        $fio,
        $name,
        $birthday,
        $data_peresecheniya,
        $address,
        $predstavitel,
        $predstavitel_year,
        $predstavitel_address,
        $month
    ) {
        $params = array(
            "max_len" => "100", // обрезает символьный код до 100 символов
            "change_case" => "L", // буквы преобразуются к нижнему регистру
            "replace_space" => "_", // меняем пробелы на нижнее подчеркивание
            "replace_other" => "_", // меняем левые символы на нижнее подчеркивание
            "delete_repeat_replace" => "true", // удаляем повторяющиеся нижние подчеркивания
            "use_google" => "false", // отключаем использование google
        );

        function calculate_age($birthday)
        {
            $birthday_timestamp = strtotime($birthday);
            $age = date('Y') - date('Y', $birthday_timestamp);
            if (date('md', $birthday_timestamp) > date('md')) {
                $age--;
            }
            return $age;
        }

        $data_peresecheniya_end = date("d.m.Y", strtotime($data_peresecheniya." +13 day"));

        if (calculate_age($birthday) > 17) {
            $path = '';
            $arData = [
                'DATE_ARRIVALS' => $data_peresecheniya,
                'FIO' => $fio.' '.$name,
                'YEAR_BIRTHDAY' => $birthday,
                'DATE_START' => $data_peresecheniya,
                'DATE_END' => $data_peresecheniya_end,
                'ADDRESS_TEXT' => $address,
                'MONTH' => $month
            ];

            if (CModule::IncludeModule("citto.integration")) {
                $path = Docx::generateDocument(CUtil::translit(strtolower($arData['FIO']), "ru", $params), $arData, 'isolation');
            }

            return $path;
        } else {
            //дети
            $arData = [
                'DATE_ARRIVALS' => $data_peresecheniya,
                'FIO' => $fio.' '.$name,
                'YEAR_BIRTHDAY' => $birthday,
                'DATE_START' => $data_peresecheniya,
                'DATE_END' => $data_peresecheniya_end,
                'ADDRESS_TEXT' => $address,
                'PREDSTAVITEL_FIO' => $predstavitel,
                'PREDSTAVITEL_ADDRESS' => $predstavitel_address,
                'MONTH' => $month
            ];

            $strTemplateName = 'isolation_child';

            if ($predstavitel == '') {
                $strTemplateName = 'isolation_child_no_predstavitel';
            }

            if (CModule::IncludeModule("citto.integration")) {
                $path = Docx::generateDocument(CUtil::translit(strtolower($arData['FIO']), "ru", $params), $arData, $strTemplateName);
            }

            return $path;
        }
    }

    public function createFileComingAction(
        $fio,
        $birthday,
        $data_peresecheniya,
        $date_end,
        $address,
        $predstavitel,
        $predstavitel_year,
        $predstavitel_address,
        $month
    ) {

        $params = array(
            "max_len" => "100", // обрезает символьный код до 100 символов
            "change_case" => "L", // буквы преобразуются к нижнему регистру
            "replace_space" => "_", // меняем пробелы на нижнее подчеркивание
            "replace_other" => "_", // меняем левые символы на нижнее подчеркивание
            "delete_repeat_replace" => "true", // удаляем повторяющиеся нижние подчеркивания
            "use_google" => "false", // отключаем использование google
        );



        function calculate_age($birthday)
        {
            $birthday_timestamp = strtotime($birthday);
            $age = date('Y') - date('Y', $birthday_timestamp);
            if (date('md', $birthday_timestamp) > date('md')) {
                $age--;
            }
            return $age;
        }




        if (calculate_age($birthday) > 17) {
            $path = '';
            $arData = [
                'DATE_ARRIVALS' => $data_peresecheniya,
                'FIO' => $fio,
                'YEAR_BIRTHDAY' => $birthday,
                'DATE_START' => $data_peresecheniya,
                'DATE_END' => $date_end,
                'ADDRESS_TEXT' => $address,
                'MONTH' => $month
            ];

            if (CModule::IncludeModule("citto.integration")) {
                $path = Docx::generateDocument(CUtil::translit(strtolower($arData['FIO']), "ru", $params), $arData, 'isolation');
            }

            return $path;
        } else {
            //дети
            $arData = [
                'DATE_ARRIVALS' => $data_peresecheniya,
                'FIO' => $fio,
                'YEAR_BIRTHDAY' => $birthday,
                'DATE_START' => $data_peresecheniya,
                'DATE_END' => $date_end,
                'ADDRESS_TEXT' => $address,
                'PREDSTAVITEL_FIO' => $predstavitel,
                'PREDSTAVITEL_ADDRESS' => $predstavitel_address,
                'MONTH' => $month
            ];

            $strTemplateName = 'isolation_child';

            if ($predstavitel == '') {
                $strTemplateName = 'isolation_child_no_predstavitel';
            }

            if (CModule::IncludeModule("citto.integration")) {
                $path = Docx::generateDocument(CUtil::translit(strtolower($arData['FIO']), "ru", $params), $arData, $strTemplateName);
            }

            return $path;
        }
    }

    public function createFileMZHAction(
        $fio,
        $birthday,
        $data_peresecheniya,
        $address,
        $predstavitel,
        $predstavitel_year,
        $predstavitel_address,
        $month
    ) {
        $params = array(
            "max_len" => "100", // обрезает символьный код до 100 символов
            "change_case" => "L", // буквы преобразуются к нижнему регистру
            "replace_space" => "_", // меняем пробелы на нижнее подчеркивание
            "replace_other" => "_", // меняем левые символы на нижнее подчеркивание
            "delete_repeat_replace" => "true", // удаляем повторяющиеся нижние подчеркивания
            "use_google" => "false", // отключаем использование google
        );



        function calculate_age($birthday)
        {
            $birthday_timestamp = strtotime($birthday);
            $age = date('Y') - date('Y', $birthday_timestamp);
            if (date('md', $birthday_timestamp) > date('md')) {
                $age--;
            }
            return $age;
        }

        $data_peresecheniya_end = date("d.m.Y", strtotime($data_peresecheniya." +13 day"));

        if (calculate_age($birthday) > 17) {
            $path = '';
            $arData = [
                'DATE_ARRIVALS' => $data_peresecheniya,
                'FIO' => $fio,
                'YEAR_BIRTHDAY' => $birthday,
                'DATE_START' => $data_peresecheniya,
                'DATE_END' => $data_peresecheniya_end,
                'ADDRESS_TEXT' => $address,
                'MONTH' => $month
            ];

            if (CModule::IncludeModule("citto.integration")) {
                $path = Docx::generateDocument(CUtil::translit(strtolower($arData['FIO']), "ru", $params), $arData, 'isolation');
            }

            return $path;
        } else {
            //дети
            $arData = [
                'DATE_ARRIVALS' => $data_peresecheniya,
                'FIO' => $fio,
                'YEAR_BIRTHDAY' => $birthday,
                'DATE_START' => $data_peresecheniya,
                'DATE_END' => $data_peresecheniya_end,
                'ADDRESS_TEXT' => $address,
                'PREDSTAVITEL_FIO' => $predstavitel,
                'YEAR_BIRTHDAY_PREDSTAVITEL' => $predstavitel_year,
                'PREDSTAVITEL_ADDRESS' => $predstavitel_address,
                'MONTH' => $month
            ];

            $strTemplateName = 'isolation_child';

            if ($predstavitel == '') {
                $strTemplateName = 'isolation_child_no_predstavitel';
            }

            if (CModule::IncludeModule("citto.integration")) {
                $path = Docx::generateDocument(CUtil::translit(strtolower($arData['FIO']), "ru", $params), $arData, $strTemplateName);
            }

            return $path;
        }
    }

    public function createFileContactAction(
        $fio,
        $phone,
        $data_quarant,
        $birthday,
        $address,
        $predstavitel,
        $predstavitel_address,
        $snils,
        $passport,
        $month
    ) {

        $params = array(
            "max_len" => "100", // обрезает символьный код до 100 символов
            "change_case" => "L", // буквы преобразуются к нижнему регистру
            "replace_space" => "_", // меняем пробелы на нижнее подчеркивание
            "replace_other" => "_", // меняем левые символы на нижнее подчеркивание
            "delete_repeat_replace" => "true", // удаляем повторяющиеся нижние подчеркивания
            "use_google" => "false", // отключаем использование google
        );

        function calculate_age($birthday)
        {
            $birthday_timestamp = strtotime($birthday);
            $age = date('Y') - date('Y', $birthday_timestamp);
            if (date('md', $birthday_timestamp) > date('md')) {
                $age--;
            }
            return $age;
        }

        $data_quarant_end = date("d.m.Y", strtotime($data_quarant." +13 day"));

        if (calculate_age($birthday) > 17) {
            $path = '';
            $arData = [
                'FIO' => $fio,
                'PHONE' => $phone,
                'DATE_QUARANT' => $data_quarant,
                'LAST_DATE' => $data_quarant_end,
                'ADDRESS_TEXT' => $address,
                'MONTH' => $month,
                'SNILS' => $snils,
                'PASSPORT' => $passport,
            ];

            if (CModule::IncludeModule("citto.integration")) {
                $path = Docx::generateDocument(CUtil::translit(strtolower($arData['FIO']), "ru", $params), $arData, 'isolation_contact');
            }

            return $path;
        } else {
            //дети
            $arData = [
                'FIO' => $fio,
                'YEAR_BIRTHDAY' => $birthday,
                'DATE_QUARANT' => $data_quarant,
                'LAST_DATE' => $data_quarant_end,
                'ADDRESS_TEXT' => $address,
                'PREDSTAVITEL_FIO' => $predstavitel,
                'PREDSTAVITEL_ADDRESS' => $predstavitel_address,
		            'SNILS' => $snils,
		            'PASSPORT' => $passport,
                'MONTH' => $month
            ];

            if (CModule::IncludeModule("citto.integration")) {
                $path = Docx::generateDocument(CUtil::translit(strtolower($arData['FIO'].'_child'), "ru", $params), $arData, 'isolation_contact_child');
            }

            return $path;
        }
    }

    public function getTableAction($columns, $section)
    {
        $arSections = [
            'rus_isolation_contacts' => SECTION_ID_MIGRATION_CONT,
            'rus_isolation' => SECTION_ID_MIGRATION_DOCS_RF,
            'mzh_isolation' => SECTION_ID_MIGRATION_DOCS_MZH,
            'mp_isolation' => SECTION_ID_MIGRATION_DOCS_MP,
            'coming_isolation' => SECTION_ID_MIGRATION_DOCS_COMING,
            'arrived_isolation' => SECTION_ID_MIGRATION_DOCS_ARRIVED,
        ];

        $strDaysForEndTime = ' +14 day';
        if ($section == 'rus_isolation_contacts') {
            $strDaysForEndTime = ' +13 day';
        }

        $columns = json_decode($columns, true);
        $arSelectDefault = ['ID', 'IBLOCK_ID', 'NAME', 'IBLOCK_SECTION_ID'];
        $arAllProps = $result = $arColsCode = [];

        foreach ($columns as $key => $prop) {
            $arColsCode[$prop['code']] = $prop['name'];
            if ($prop['code'] != 'ID' && $prop['code'] != 'NAME') {
                $arAllProps[] = 'PROPERTY_'.$prop['code'];
            }
        }
        $arSelect = array_merge($arSelectDefault, $arAllProps);

        $count = 0;
        $arFilter = array("IBLOCK_ID"=>IBLOCK_ID_MIGRATION_DOCS, "ACTIVE_DATE"=>"Y", "ACTIVE"=>"Y", 'SECTION_ID' => $arSections[$section]);
        $res = CIBlockElement::GetList(array('NAME' => 'ASC'), $arFilter, false, false, $arSelect);
        while ($arFields = $res->GetNext()) {
            $strEndQuarantineDate = '';
            foreach ($arFields as $key => $value) {
                $arPatterns = ['/PROPERTY_/', '/_VALUE/'];
                $arReplacements = ['', ''];
                $newKey = preg_replace($arPatterns, $arReplacements, $key);

                if (array_key_exists($newKey, $arColsCode)) {
                    if ($newKey == 'ATT_DATE_PERESECHENIYA') {
                        $strEndQuarantineDate = date('d.m.Y', strtotime($value . $strDaysForEndTime));
                    }
                    if ($newKey == 'ATT_DATE_QUARANT' && $value) {
                        $strEndQuarantineDate = date('d.m.Y', strtotime($value . $strDaysForEndTime));
                    }
                    if ($newKey == 'ATT_DATE_QUARANT' && $value == '') {
                        $strEndQuarantineDate = '';
                    }
                    if ($newKey == 'ATT_PHONE' && $value) {
                        $value = str_replace(array('(',')', ' ', '+7'), '', $value);
                    }

                    if ($arFields['IBLOCK_SECTION_ID'] !== SECTION_ID_MIGRATION_DOCS_COMING) {
                        if ($newKey == 'ATT_LAST_DATE') {
                            $value = $strEndQuarantineDate;
                        }
                    }

                    $result['list'][$count][$arColsCode[$newKey]] = $value;
                }
            }

            $count++;
        }

        return $result;
    }

  public function getTableAllContactAction($columns)
  {

    $loggerDebug = new Logger('CONTACT_TABLE');
    $loggerDebug->pushHandler(new StreamHandler($_SERVER['DOCUMENT_ROOT'] . '/local/logs/isolation_contact_getTable.log', Logger::INFO));


    require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/classes/general/csv_data.php");


    $strTemplatePath = '/upload/';
    $strDirSave = 'isolation_reports';
    $strPathToSaveDir = $_SERVER['DOCUMENT_ROOT'].$strTemplatePath.$strDirSave;

    $strDateNow = date('d-m-Y');
    $strFileExt = '.csv';
    $strFileName = 'isolation_contact_all-'.$strDateNow.$strFileExt;

    $filePath = $strPathToSaveDir.'/'.$strFileName;

    function toWindow($ii){
      return iconv( "utf-8", "windows-1251",$ii);
    }
    header('Content-Type: text/csv; charset=windows-1251');

    $fp = fopen($filePath, 'w+');
    $fields_type = 'R';
    $delimiter = ";";
    $csvFile = new \CCSVData($fields_type, true);
    $csvFile->SetFieldsType($fields_type);
    $csvFile->SetDelimiter($delimiter);


    $arHeaderFileNames = [
      'ID','ФИО контактного','Эпид.анамнез','Степень контакта',
      'Дата рождения контактного','Адрес проживания контактного','Номер телефона',
      'Место работы контактного','Должность контактного','ФИО больного по контакту',
      'Адрес проживания больного по контакту','Дата контакта','Дата окончания карантина',
      'Место контакта','ГУЗ наблюдения','Дата начала карантина',
      'Место изоляции (адрес)',
      'Статус изоляции','Дата вручения постановления','Постановление вручено',
      'СНИЛС','Дата оповещения','Способ оповещения','Дата внесения данных',
        'Дата первой вакцинации', 'Дата второй вакцинации', 'Дата заболевания'
    ];
    if (!file_exists($strPathToSaveDir)) {
      mkdir($strPathToSaveDir, 0775, true);
    }

//    foreach ($arHeaderFileNames as $key => $value) {
//      $arHeaderFileNames[$key] = toWindow($value);
//    }
    $csvFile->SaveFile($filePath, $arHeaderFileNames);


    $arFileColumns = [
      'A' => 'ID', 'B' => 'NAME', 'C' => 'PROPERTY_ATT_EPID_VALUE', 'D' => 'PROPERTY_ATT_STEP_VALUE',
      'E' => 'PROPERTY_ATT_DATE_BIRTHDAY_VALUE', 'F' => 'PROPERTY_ATT_ADDRESS_VALUE', 'G' => 'PROPERTY_ATT_PHONE_VALUE',
      'H' => 'PROPERTY_ATT_WORK_VALUE', 'I' => 'PROPERTY_ATT_POSITION_VALUE', 'J' => 'PROPERTY_ATT_LEGAL_REPRES_VALUE',
      'K' => 'PROPERTY_ATT_ADDRESS_REPRES_VALUE', 'L' => 'PROPERTY_ATT_DATE_PERESECHENIYA_VALUE', 'M' => 'PROPERTY_ATT_LAST_DATE_VALUE',
      'N' => 'PROPERTY_ATT_PLACE_CONTACT_VALUE', 'O' => 'PROPERTY_ATT_GUZ_NAV_VALUE', 'P' => 'PROPERTY_ATT_DATE_QUARANT_VALUE',
      'Q' => 'PROPERTY_ATT_INCOMING_SIDE_VALUE',
      'R' => 'PROPERTY_ATT_ISOLATION_STATUS_VALUE', 'S' => 'PROPERTY_ATT_DATE_RESOLUTION_VALUE', 'T' => 'PROPERTY_ATT_RESOLUTION_VALUE',
      'U' => 'PROPERTY_ATT_SNILS_VALUE', 'V' => 'PROPERTY_ATT_NOTIFY_DATE_VALUE', 'W' => 'PROPERTY_ATT_NOTIFY_WAY_VALUE', 'X' => 'PROPERTY_ATT_DATE_ADD_DATA_VALUE',
        'Y' => 'PROPERTY_ATT_VAC_1_DATE_VALUE', 'Z' => 'PROPERTY_ATT_VAC_2_DATE_VALUE', 'AA' => 'PROPERTY_ATT_ILLNESS_DATE_VALUE'
    ];



    $strNewFilePath = '';
    $strDaysForEndTime = ' +13 day';

    $arSelect = [
      'ID',
      'IBLOCK_ID',
      'NAME',
      'PROPERTY_ATT_EPID',
      'PROPERTY_ATT_STEP',
      'PROPERTY_ATT_DATE_BIRTHDAY',
      'PROPERTY_ATT_ADDRESS',
      'PROPERTY_ATT_PHONE',
      'PROPERTY_ATT_WORK',
      'PROPERTY_ATT_POSITION',
      'PROPERTY_ATT_LEGAL_REPRES',
      'PROPERTY_ATT_ADDRESS_REPRES',
      'PROPERTY_ATT_DATE_PERESECHENIYA',
      'PROPERTY_ATT_LAST_DATE',
      'PROPERTY_ATT_PLACE_CONTACT',
      'PROPERTY_ATT_GUZ_NAV',
      'PROPERTY_ATT_DATE_QUARANT',
      'PROPERTY_ATT_INCOMING_SIDE',
      'PROPERTY_ATT_ISOLATION_STATUS',
      'PROPERTY_ATT_DATE_RESOLUTION',
      'PROPERTY_ATT_RESOLUTION',
      'PROPERTY_ATT_SNILS',
      'PROPERTY_ATT_NOTIFY_DATE',
      'PROPERTY_ATT_NOTIFY_WAY',
      'PROPERTY_ATT_DATE_ADD_DATA',
      'PROPERTY_ATT_VAC_1_DATE',
      'PROPERTY_ATT_VAC_2_DATE',
      'PROPERTY_ATT_ILLNESS_DATE',
    ];


    $arFilter = array(
      "IBLOCK_ID" => IBLOCK_ID_MIGRATION_DOCS, "ACTIVE_DATE" => "Y", "ACTIVE" => "Y",
      "IBLOCK_SECTION_ID" => SECTION_ID_MIGRATION_CONT
    );

    $loggerDebug->info('начало выбора данных', [
      '$arFilter' => $arFilter,
    ]);


    $res = CIBlockElement::GetList(array('NAME' => 'ASC'), $arFilter, false, false, $arSelect);
    $intAllCount = $res->SelectedRowsCount();

    $intCountRows = 1;
    $arRows = [];
    while ($arFields = $res->GetNext()) {
        $arRow = [];
        foreach ($arFileColumns as $strColumnChar => $column) {
            $arRow[] = htmlspecialcharsback($arFields[$column]);
        }
        $arRows[] = $arRow;



        $intCountRows++;
    }

      $loggerDebug->info('выбор данных завершен', [
        '$arFilter' => $arFilter,
        'allCount' => $intAllCount,
        'intCountRows' => $intCountRows,
      ]);

    foreach ($arRows as $row) {
        $csvFile->SaveFile($filePath, $row);
    }

      $loggerDebug->info('Конец перебора данных', [
        '$arFilter' => $arFilter,
        'allCount' => $intAllCount,
        'intCountRows' => $intCountRows,
      ]);





    $arFile = CFile::MakeFileArray($strPathToSaveDir.'/'.$strFileName);

      $loggerDebug->info('создание фалйа', [
        '$arFile' => $arFile
      ]);

    $inrFileReportID = CFile::SaveFile($arFile, 'isolation-contact');



    unlink($strPathToSaveDir.'/'.$strFileName);
    $strNewFilePath = CFile::GetPath($inrFileReportID);

      $loggerDebug->info('фалй сохранен', [
        '$inrFileReportID' => $inrFileReportID,
        '$strNewFilePath' => $strNewFilePath,
      ]);


    return $strNewFilePath;
  }

		public function getTableContactAction($columns, $filter)
		{


				$strDaysForEndTime = ' +14 day';
				global $DB;



				$columns = json_decode($columns, true);
				$filterT = json_decode($filter, true);
				$filterT[0]['LOGIC'] = 'OR';
				$filterT[1]['LOGIC'] = 'AND';
				if ($filterT['NAME']) {
						$filterT[0]['NAME'] = '%'.$filterT['NAME'].'%';
            $filterT[0]['PROPERTY_ATT_LEGAL_REPRES'] = '%'.$filterT['NAME'].'%';
						unset($filterT['NAME']);
				}

				if ($filterT['PROPERTY_ATT_AREA']) {
						$filterT[0]['PROPERTY_ATT_AREA'] = $filterT['PROPERTY_ATT_AREA'];
						unset($filterT['PROPERTY_ATT_AREA']);

				}
				if ($filterT['PROPERTY_ATT_CITY']) {
						$filterT[0]['PROPERTY_ATT_CITY'] = $filterT['PROPERTY_ATT_CITY'];
						unset($filterT['PROPERTY_ATT_CITY']);

				}
				if ($filterT['PROPERTY_ATT_GUZ_NAV']) {
						$filterT[0]['PROPERTY_ATT_GUZ_NAV'] = $filterT['PROPERTY_ATT_GUZ_NAV'];
						unset($filterT['PROPERTY_ATT_GUZ_NAV']);

				}

				if ($filterT['>=PROPERTY_ATT_DATE_ADD_DATA'] && $filterT['<=PROPERTY_ATT_DATE_ADD_DATA']) {
						$filterT[1]['>=PROPERTY_ATT_DATE_ADD_DATA'] = $DB->FormatDate($filterT['>=PROPERTY_ATT_DATE_ADD_DATA'], "DD.MM.YYYY", "YYYY-MM-DD");
						$filterT[1]['<=PROPERTY_ATT_DATE_ADD_DATA'] = $DB->FormatDate($filterT['<=PROPERTY_ATT_DATE_ADD_DATA'], "DD.MM.YYYY", "YYYY-MM-DD");
						unset($filterT['>=PROPERTY_ATT_DATE_ADD_DATA']);
						unset($filterT['<=PROPERTY_ATT_DATE_ADD_DATA']);
				}
				if ($filterT['>=PROPERTY_ATT_DATE_PERESECHENIYA']) {
						$filterT[0]['>=PROPERTY_ATT_DATE_PERESECHENIYA'] = date('Y-m-d', time() - 86400 * 14);
						unset($filterT['>=PROPERTY_ATT_DATE_PERESECHENIYA']);

				}
				if ($filterT['<PROPERTY_ATT_DATE_PERESECHENIYA']) {
						$filterT[0]['<PROPERTY_ATT_DATE_PERESECHENIYA'] = date('Y-m-d', time() - 86400 * 14);
						unset($filterT['<PROPERTY_ATT_DATE_PERESECHENIYA']);
				}

				$arSelectDefault = ['ID', 'IBLOCK_ID', 'NAME', 'IBLOCK_SECTION_ID'];
				$arAllProps = $result = $arColsCode = [];

				foreach ($columns as $key => $prop) {
						$arColsCode[$prop['code']] = $prop['name'];
						if ($prop['code'] != 'ID' && $prop['code'] != 'NAME') {
								$arAllProps[] = 'PROPERTY_'.$prop['code'];
						}
				}

				$arSelect = array_merge($arSelectDefault, $arAllProps);

				$count = 0;
				$arFilter = array("IBLOCK_ID"=>IBLOCK_ID_MIGRATION_DOCS, "ACTIVE_DATE"=>"Y", "ACTIVE"=>"Y", 'IBLOCK_SECTION_ID' => SECTION_ID_MIGRATION_CONT);
				if (is_array($filterT)) {
						$arFilter = array_merge($arFilter, $filterT);
				}


				$res = CIBlockElement::GetList(array('NAME' => 'ASC'), $arFilter, false, false, $arSelect);
				while ($arFields = $res->GetNext()) {
						$strEndQuarantineDate = '';
						foreach ($arFields as $key => $value) {
								$arPatterns = ['/PROPERTY_/', '/_VALUE/'];
								$arReplacements = ['', ''];
								$newKey = preg_replace($arPatterns, $arReplacements, $key);

								if (array_key_exists($newKey, $arColsCode)) {
										if ($newKey == 'ATT_DATE_PERESECHENIYA') {
												$strEndQuarantineDate = date('d.m.Y', strtotime($value . $strDaysForEndTime));
										}
										if ($newKey == 'ATT_DATE_QUARANT' && $value) {
												$strEndQuarantineDate = date('d.m.Y', strtotime($value . $strDaysForEndTime));
										}
										if ($newKey == 'ATT_DATE_QUARANT' && $value == '') {
												$strEndQuarantineDate = '';
										}
										if ($newKey == 'ATT_PHONE' && $value) {
												$value = str_replace(array('(',')', ' ', '+7'), '', $value);
										}

										if ($arFields['IBLOCK_SECTION_ID'] !== SECTION_ID_MIGRATION_DOCS_COMING) {
												if ($newKey == 'ATT_LAST_DATE') {
														$value = $strEndQuarantineDate;
												}
										}

										$result['list'][$count][$arColsCode[$newKey]] = $value;
								}
						}

						$count++;
				}
				$result['filter'] = $arFilter;

				return $result;
		}

    public function getTableViolationAction($columns, $filter, $kind)
    {
        $columns = json_decode($columns, true);
        $filter = json_decode($filter, true);
        if ($kind == 'возвратившийся' || $kind == 'иные основания') {
            $filter = ['UF_REASON_ISOLATION' => $kind, 'UF_FIO' => false];
        }

        $arSelectDefault = ['ID', 'UF_FIO', 'UF_SERIALIZE_DATA'];

        foreach ($columns as $key => $prop) {
            $arColsCode[$prop['code']] = $prop['name'];
            if ($prop['code'] != "LAST_NAME" && $prop['code'] != "FIRST_SECOND_NAME") {
                $arAllProps[] = $prop['code'];
            }
        }

        $arSortableData = [
            'LAST_NAME' => '',
            'FIRST_SECOND_NAME' => '',
            'UF_ADDRESS' => '',
            'UF_PHONE' => '',
            'UF_DATE_BIRTHDAY' => '',
            'UF_DATE_VIOLATION' => '',
            'UF_ADDRESS_VIOLATION' => '',
            'UF_COORDINATES' => '',
            'UF_DATA_TYPE' => '',
        ];

        $arSelect = array_merge($arSelectDefault, $arAllProps);


        $hlblock = HL\HighloadBlockTable::getById(HLBLOCK_ID_VIOLATORS)->fetch();
        $entity = HL\HighloadBlockTable::compileEntity($hlblock);
        $entity_data_class = $entity->getDataClass();

        $rsData = $entity_data_class::getList(array(
            "select" => $arSelect,
            "order" => ['id' => 'asc'],
            "filter" => $filter,
        ));

        $intCount = 0;
        $arUnSerializeData = [];
        while ($arData = $rsData->Fetch()) {
            $rsEnumDataType = CUserFieldEnum::GetList(array(), array("ID" => $arData["UF_DATA_TYPE"]));
            $arEnumDataType = $rsEnumDataType->GetNext();
            $arDataType = [$arEnumDataType['ID'] => $arEnumDataType['VALUE']];

            $arData['UF_DATA_TYPE'] = $arDataType[$arData['UF_DATA_TYPE']];
            if ($arData['UF_FIO']) {
                $arData['FIRST_SECOND_NAME'] = explode(' ', $arData['UF_FIO'])[1].' '.explode(' ', $arData['UF_FIO'])[2];
                $arData['LAST_NAME'] = explode(' ', $arData['UF_FIO'])[0];
            }

            $arUnSerializeData[] = unserialize($arData['UF_SERIALIZE_DATA']);
            $arData['UF_DATE_BIRTHDAY'] = date('d.m.Y', strtotime($arData['UF_DATE_BIRTHDAY']));
            $arData['UF_DATE_VIOLATION'] = date('d.m.Y H:i', strtotime($arData['UF_DATE_VIOLATION']));
            $arData['UF_PHONE'] = str_replace(array('(',')', ' ', '+7'), '', $arData['UF_PHONE']);
            unset($arData['UF_FIO']);
            unset($arData['ID']);

            foreach ($arData as $key => $value) {
                if ($kind == 'main') {
                    if (array_key_exists($key, $arColsCode)) {
                        $result['list'][$intCount][$arColsCode[$key]] = $value;
                    }
                } else {
                    $arResultForFile[$intCount][$key] = $value;
                }
            }

            $intCount++;
        }

        if ($kind == 'возвратившийся') {
            foreach ($arResultForFile as $key => $value) {
                $result['list'][] = [
                    '',
                    '',
                    $value['UF_PHONE'],
                    '',
                    $kind,
                    $arUnSerializeData[$key]['reestr_data'],
                    $arUnSerializeData[$key]['gosudarstvo_vizita'],
                    $value['UF_DATE_VIOLATION'],
                    '',
                    '',
                    $value['UF_OPERATOR'],
                ];
            }
        } elseif ($kind == 'иные основания') {
            foreach ($arResultForFile as $key => $value) {
                $result['list'][] = [
                    '',
                    '',
                    $value['UF_PHONE'],
                    '',
                    $kind,
                    $arUnSerializeData[$key]['reestr_data'],
                    $value['UF_DATE_VIOLATION'],
                    '',
                    '',
                    $value['UF_OPERATOR'],
                ];
            }
        }

        return $result;
    }

    public function addElementContactAction(
        $NAME,
        $ATT_EPID,
        $ATT_STEP,
        $ATT_DATE_BIRTHDAY,
        $ATT_ADDRESS,
        $ATT_PHONE,
        $ATT_WORK,
        $ATT_POSITION,
        $ATT_LEGAL_REPRES,
        $ATT_ADDRESS_REPRES,
        $ATT_DATE_PERESECHENIYA,
        $ATT_PLACE_CONTACT,
        $ATT_GUZ_NAV,
        $ATT_DATE_QUARANT,
        $ATT_LAST_DATE,
        $ATT_INCOMING_SIDE,
        $ATT_ISOLATION_STATUS,
        $ATT_DATE_RESOLUTION,
        $ATT_NOTIFY_DATE,
        $ATT_NOTIFY_WAY,
        $ATT_DATE_ADD_DATA,
        $ATT_VAC_1_DATE,
        $ATT_VAC_2_DATE,
        $ATT_ILLNESS_DATE,
        $ATT_RESOLUTION = 'N'
    ) {

        global $USER;

		    $params = array(
				    "max_len" => "100", // обрезает символьный код до 100 символов
				    "change_case" => "L", // буквы преобразуются к нижнему регистру
				    "replace_space" => "_", // меняем пробелы на нижнее подчеркивание
				    "replace_other" => "_", // меняем левые символы на нижнее подчеркивание
				    "delete_repeat_replace" => "true", // удаляем повторяющиеся нижние подчеркивания
				    "use_google" => "false", // отключаем использование google
		    );

		    $arDuplicateRow['ISSET'] = false;


        $arProps = [
            'ATT_EPID' => $ATT_EPID,
            'ATT_STEP' => $ATT_STEP,
            'ATT_DATE_BIRTHDAY' => $ATT_DATE_BIRTHDAY,
            'ATT_ADDRESS' => $ATT_ADDRESS,
            'ATT_PHONE' => str_replace(' ', '', $ATT_PHONE),
            'ATT_WORK' => $ATT_WORK,
            'ATT_POSITION' => $ATT_POSITION,
            'ATT_LEGAL_REPRES' => $ATT_LEGAL_REPRES,
            'ATT_ADDRESS_REPRES' => $ATT_ADDRESS_REPRES,
            'ATT_DATE_PERESECHENIYA' => $ATT_DATE_PERESECHENIYA,
            'ATT_PLACE_CONTACT' => $ATT_PLACE_CONTACT,
            'ATT_GUZ_NAV' => $ATT_GUZ_NAV,
            'ATT_DATE_QUARANT' => $ATT_DATE_QUARANT,
            'ATT_LAST_DATE' => $ATT_LAST_DATE,
            'ATT_INCOMING_SIDE' => $ATT_INCOMING_SIDE,
            'ATT_ISOLATION_STATUS' => $ATT_ISOLATION_STATUS,
            'ATT_DATE_RESOLUTION' => $ATT_DATE_RESOLUTION,
            'ATT_RESOLUTION' => $ATT_RESOLUTION,
            'ATT_NOTIFY_DATE' => $ATT_NOTIFY_DATE,
            'ATT_NOTIFY_WAY' => $ATT_NOTIFY_WAY,
            'ATT_DATE_ADD_DATA' => $ATT_DATE_ADD_DATA,
            'ATT_VAC_1_DATE' => $ATT_VAC_1_DATE,
            'ATT_VAC_2_DATE' => $ATT_VAC_2_DATE,
            'ATT_ILLNESS_DATE' => $ATT_ILLNESS_DATE,
        ];

		    $md5Sum = md5($NAME.';'.implode(';', $arProps));
		    $arProps['ATT_CHECK_SUM'] = $md5Sum;

		    $arSelect = Array("ID", "IBLOCK_ID", "PROPERTY_ATT_CHECK_SUM");
		    $arFilter = Array(
		    		"IBLOCK_ID"=>IBLOCK_ID_MIGRATION_DOCS,
				    "ACTIVE_DATE"=>"Y",
				    "ACTIVE"=>"Y",
				    "IBLOCK_SECTION_ID" => SECTION_ID_MIGRATION_CONT,
				    "PROPERTY_ATT_CHECK_SUM" => $md5Sum,
				    );
		    $res = CIBlockElement::GetList(Array('sort' => 'asc'), $arFilter, false, false, $arSelect);
		    if ($res->SelectedRowsCount() > 0) {
				    $arDuplicateRow['ISSET'] = true;

				    while($arFields = $res->GetNext()) {
						    $arDuplicateRow['ID'][] = $arFields['ID'];
				    }
		    }




		    if ($NAME) {

		    		if (!$arDuplicateRow['ISSET']) {


						    $this->addAddressComponents($arProps, ['ATT_AREA', 'ATT_CITY'], IBLOCK_ID_MIGRATION_DOCS, ['area', 'city']);


						    $oEl = new CIBlockElement();
						    $arLoadArray = array(
								    "MODIFIED_BY" => $USER->GetID(),
								    "IBLOCK_SECTION_ID" => SECTION_ID_MIGRATION_CONT,
								    "IBLOCK_ID" => IBLOCK_ID_MIGRATION_DOCS,
								    "PROPERTY_VALUES" => $arProps,
								    "NAME" => $NAME,
								    "ACTIVE" => "Y",
						    );

						    $strUserFullName = $USER->GetFullName();

						    $logger = new Logger('ADD ELEMENT');
						    $logger->pushHandler(new StreamHandler($_SERVER['DOCUMENT_ROOT'] . '/local/logs/isolation_contact_table.log', Logger::INFO));

						    if ($iContactId = $oEl->Add($arLoadArray)) {
								    $logger->info('Добавлен элемент с ID: ' . $iContactId, array('user' => $strUserFullName, 'params' => $arLoadArray));

								    return ['status' => 'Y', 'message' => "Сохранено"];
						    } else {
								    $logger->error("Ошибка: " . $oEl->LAST_ERROR);

								    return ['status' => 'N', 'message' => "Ошибка: " . $oEl->LAST_ERROR];
						    }
				    } else {
						    $logger = new Logger('ADD ELEMENT');
						    $logger->pushHandler(new StreamHandler($_SERVER['DOCUMENT_ROOT'] . '/local/logs/isolation_contact_table.log', Logger::WARNING));
						    $logger->error('Ошибка добавления записи. Идентичная запись уже существует с ID: ' . implode(';', $arDuplicateRow['ID']));
						    return ['data' => $dadata,'status' => 'N', 'message' => "Ошибка: " . 'Ошибка добавления записи. Идентичная запись уже существует с ID: ' . array_shift($arDuplicateRow['ID'])];
				    }
        } else {
            return '"ФИО контактного" - обязательное поле';
        }
    }
}
