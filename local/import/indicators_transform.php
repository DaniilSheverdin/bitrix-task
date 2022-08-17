<?

#$_SERVER['DOCUMENT_ROOT']='/var/www/corp';
require_once $_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_before.php";
CModule::IncludeModule('citto.integration');

#use \Citto\Integration;
$IBLOCK_ID = 5;
#$rConnect  = \Citto\Integration\Source1C::Connect1C();
CModule::IncludeModule('iblock');
//define(IBLOCK_ID_CONTROLS,545);
echo IBLOCK_ID_CONTROLS;
$arResult['CATEGORY']=[];
$csvFile = new CCSVData('R', true);
$csvFile->LoadFile($_SERVER['DOCUMENT_ROOT'].'/local/import/indicators.csv');
$csvFile->SetDelimiter(';');
$n=0;
while ($arRes = $csvFile->Fetch()) {
    if ($n==0) {
        pre($arRes);
    }
    $n++;
    $arResult['INDICATOR'][md5($arRes[0])]=$arRes;
    $arResult['OTDELS'][$arRes['7']][$arRes['6']][]=md5($arRes[0]);
    $arResult['THEMES'][$arRes['4']][]=md5($arRes[0]);
    $arResult['CATEGORY'][$arRes['2']][$arRes['3']][]=md5($arRes[0]);
}
//pre($arResult['THEMES']);
//pre($arResult['CATEGORY']);
$arFilter = array('IBLOCK_ID' => IBLOCK_ID_INDICATORS_DEPARTMENTS,'INCLUDE_SUBSECTION'=>'Y');
$arResult['OTDELS_NAMES']=[];
$arSelect = array('ID', 'NAME');
$rsSections = CIBlockSection::GetList([], $arFilter, false, $arSelect);
while ($arSection = $rsSections->Fetch()) {
        $arResult['OTDELS_NAMES'][$arSection['NAME']]=$arSection['ID'];
}
foreach ($arResult['OTDELS'] as $sKey => $arValue) {
    $bs = new CIBlockSection();
    $arFields = array(
        "ACTIVE" => 'Y',
        "IBLOCK_SECTION_ID" => $IBLOCK_SECTION_ID,
        "IBLOCK_ID" => IBLOCK_ID_INDICATORS_DEPARTMENTS,
        "NAME" => $sKey,
        );

    if ($arResult['OTDELS_NAMES'][$sKey] > 0) {
        $res = $bs->Update($arResult['OTDELS_NAMES'][$sKey], $arFields);
    } else {
        $arResult['OTDELS_NAMES'][$sKey] = $bs->Add($arFields);
        $res = ($arResult['OTDELS_NAMES'][$sKey]>0);
    }

    if (!$res) {
        echo $bs->LAST_ERROR;
    }

    foreach ($arValue as $sKey2 => $arValue2) {
        $bs = new CIBlockSection();
        $arFields = array(
            "ACTIVE" => 'Y',
            "IBLOCK_SECTION_ID" => $arResult['OTDELS_NAMES'][$sKey],
            "IBLOCK_ID" => IBLOCK_ID_INDICATORS_DEPARTMENTS,
            "NAME" => $sKey2,
        );

        if ($arResult['OTDELS_NAMES'][$sKey2] > 0) {
            $res = $bs->Update($arResult['OTDELS_NAMES'][$sKey2], $arFields);
        } else {
            $arResult['OTDELS_NAMES'][$sKey2] = $bs->Add($arFields);
            $res = ($arResult['OTDELS_NAMES'][$sKey2]>0);
        }

        if (!$res) {
            echo $bs->LAST_ERROR;
        }
    }
}
$arFilter = array('IBLOCK_ID' => IBLOCK_ID_INDICATORS_CATALOG,'INCLUDE_SUBSECTION'=>'Y');
$arResult['CATEGORY_NAMES']=[];
$arSelect = array('ID', 'NAME');
$rsSections = CIBlockSection::GetList([], $arFilter, false, $arSelect);
while ($arSection = $rsSections->Fetch()) {
    $arResult['CATEGORY_NAMES'][$arSection['NAME']]=$arSection['ID'];
}

foreach ($arResult['CATEGORY'] as $sKey => $arValue) {
    $bs = new CIBlockSection();
    $arFields = array(
        "ACTIVE" => 'Y',
        "IBLOCK_SECTION_ID" => $IBLOCK_SECTION_ID,
        "IBLOCK_ID" => IBLOCK_ID_INDICATORS_CATALOG,
        "NAME" => $sKey,
    );

    if ($arResult['CATEGORY_NAMES'][$sKey] > 0) {
        $res = $bs->Update($arResult['CATEGORY_NAMES'][$sKey], $arFields);
    } else {
        $arResult['CATEGORY_NAMES'][$sKey] = $bs->Add($arFields);
        $res = ($arResult['CATEGORY_NAMES'][$sKey]>0);
    }

    if (!$res) {
        echo $bs->LAST_ERROR;
    }
    foreach ($arValue as $sKey2 => $arValue2) {
        $bs = new CIBlockSection();
        $arFields = array(
            "ACTIVE" => 'Y',
            "IBLOCK_SECTION_ID" => $arResult['CATEGORY_NAMES'][$sKey],
            "IBLOCK_ID" => IBLOCK_ID_INDICATORS_CATALOG,
            "NAME" => $sKey2,
        );

        if ($arResult['CATEGORY_NAMES'][$sKey2] > 0) {
            $res = $bs->Update($arResult['CATEGORY_NAMES'][$sKey2], $arFields);
        } else {
            $arResult['CATEGORY_NAMES'][$sKey2] = $bs->Add($arFields);
            $res = ($arResult['CATEGORY_NAMES'][$sKey2]>0);
        }

        if (!$res) {
            echo $bs->LAST_ERROR;
        }
    }
}
//pre($arResult['CATEGORY_NAMES']);

$arSelect = array("ID", "IBLOCK_ID", "NAME", "IBLOCK_SECTION_ID", "DATE_ACTIVE_FROM", "PROPERTY_ATT_VALUE");
$arResult['INDICATORS']['BI_ID'] = $arResult['INDICATORS']['OTDEL'] = $arResult['INDICATORS']['FULL_NAME'] = $arResult['INDICATORS']['TARGET_VALUE'] = $arResult['INDICATORS']['SHORT_NAME'] = $arResult['INDICATORS']['BASE_SET'] = [];
$arFilter = array("IBLOCK_ID" => IBLOCK_ID_CONTROLS, "ACTIVE_DATE" => "Y", "ACTIVE" => "Y",'INCLUDE_SUBSECTION'=>'Y');
$res = CIBlockElement::GetList([], $arFilter, false, false, $arSelect);

while ($ob = $res->GetNextElement()) {
    $arFields = $ob->GetFields();
    $arProps = $ob->GetProperties();
    if (trim($arFields['NAME']) == 'Полное наименование показателя') {
        $arResult['INDICATORS']['FULL_NAME'] = array_merge($arResult['INDICATORS']['FULL_NAME'], $arProps['ATT_VALUE']['~VALUE']);
        $arResult['INDICATORS']['TARGET_VALUE'] = array_merge($arResult['INDICATORS']['TARGET_VALUE'], $arProps['ATT_VALUE']['DESCRIPTION']);
        $arResult['INDICATORS']['OTDEL'] = array_merge($arResult['INDICATORS']['OTDEL'], array_fill(0, count($arProps['ATT_VALUE']['~VALUE']), $arFields['IBLOCK_SECTION_ID']));
    }
    if (trim($arFields['NAME']) == 'Краткое наименование показателя') {
        $arResult['INDICATORS']['SHORT_NAME'] = array_merge($arResult['INDICATORS']['SHORT_NAME'], $arProps['ATT_VALUE']['~VALUE']);
        $arProps['ATT_VALUE']['~DESCRIPTION'] = array_map(function ($i) use ($arFields) {
             return IBLOCK_ID_CONTROLS.$arFields['IBLOCK_SECTION_ID'].$i;
        }, $arProps['ATT_VALUE']['DESCRIPTION']);
        $arResult['INDICATORS']['BI_ID'] = array_merge($arResult['INDICATORS']['BI_ID'], $arProps['ATT_VALUE']['~DESCRIPTION']);
    }
}

$arIndicators=[];
foreach ($arResult['INDICATORS'] as $key => $sValue) {
        pre($key);
}

foreach ($arResult['INDICATORS']['FULL_NAME'] as $sKey => $value) {
    $el = new CIBlockElement();
    $arIndicator=[];
    $arIndicator['NAME']=$value['TEXT'];
    $arIndicator['XML_ID']=$arResult['INDICATORS']['BI_ID'][$sKey];
    $arIndicator['IBLOCK_ID']=592;
    $arIndicator['PREVIEW_TEXT']=$arResult['INDICATORS']['SHORT_NAME'][$sKey]['TEXT'];
    $arIndicator['PROPERTY_VALUES']['TARGET_VALUE']=$arResult['INDICATORS']['TARGET_VALUE'][$sKey];
    $arResult['INDICATORS_OLD'][md5($value['TEXT'])]=$arIndicator;
    /*if ($PRODUCT_ID = $el->Add($arIndicator))
        echo "New ID: ".$PRODUCT_ID;
    else
        echo "Error: ".$el->LAST_ERROR;*/
}

$arFilter = array('IBLOCK_ID' => IBLOCK_ID_INDICATORS_THEMES);
$arResult['INDICATOR_THEMES_NAMES']=[];
$arSelect = array('ID', 'NAME');
$res = CIBlockElement::GetList([], $arFilter, false, false, $arSelect);
while ($arEelement = $res->GetNext()) {
    $arResult['INDICATOR_THEMES_NAMES'][$arEelement['NAME']]=$arEelement['ID'];
}
foreach ($arResult['THEMES'] as $sKey => $arValue) {
    $el = new CIBlockElement();
    $arFields = array(
        "ACTIVE" => 'Y',
        "IBLOCK_ID" => IBLOCK_ID_INDICATORS_THEMES,
        "NAME" => $sKey,
    );

    if ($arResult['INDICATOR_THEMES_NAMES'][$sKey] > 0) {
        $res = $el->Update($arResult['INDICATOR_THEMES_NAMES'][$sKey], $arFields);
    } else {
        $arResult['INDICATOR_THEMES_NAMES'][$sKey] = $el->Add($arFields);
        $res = ($arResult['INDICATOR_NAMES'][$sKey]>0);
    }

    if (!$res) {
        echo $el->LAST_ERROR;
    }
}
$arFilter = array('IBLOCK_ID' => IBLOCK_ID_INDICATORS_CATALOG);
$arResult['INDICATOR_NAMES']=[];
$arSelect = array('ID', 'NAME');
$res = CIBlockElement::GetList([], $arFilter, false, false, $arSelect);
while ($arEelement = $res->GetNext()) {
    $arResult['INDICATOR_NAMES'][$arEelement['NAME']]=$arEelement['ID'];
}

$property_enums = CIBlockPropertyEnum::GetList(array("DEF"=>"DESC", "SORT"=>"ASC"), array("IBLOCK_ID"=>IBLOCK_ID_INDICATORS_CATALOG, "CODE"=>"TYPE"));
while ($arEnum = $property_enums->GetNext()) {
    $arResult['TYPES_NAME'][$arEnum['VALUE']]=$arEnum;
}

//pre($arResult['TYPES_NAME']);
foreach ($arResult['INDICATOR'] as $sKey => $arValue) {
    $el = new CIBlockElement();
    $arFields = array(
        "ACTIVE" => 'Y',
        "IBLOCK_ID" => IBLOCK_ID_INDICATORS_CATALOG,
        "NAME" => $arValue[0],
        "PREVIEW_TEXT"=>$arValue[0],
        'IBLOCK_SECTION_ID'=>$arResult['CATEGORY_NAMES'][$arValue[3]]
    );
    $arFields['PROPERTY_VALUES']['STRUCTURE']=$arResult['OTDELS_NAMES'][trim($arValue[6])];
    $arFields['PROPERTY_VALUES']['TYPE']=$arResult['TYPES_NAME'][trim($arValue[5])]['ID'];
    $arFields['PROPERTY_VALUES']['THEME']=$arResult['INDICATOR_THEMES_NAMES'][trim($arValue[4])];
    if ($arResult['INDICATORS_OLD'][md5($arValue[0])]!='') {
        $arFields['XML_ID']=$arResult['INDICATORS_OLD'][md5($arValue[0])]['XML_ID'];
        $arFields['PROPERTY_VALUES']['TARGET_VALUE']=$arResult['INDICATORS_OLD'][md5($arValue[0])]['PROPERTY_VALUES']['TARGET_VALUE'];
    }
    if ($arResult['INDICATOR_NAMES'][$arValue[0]] > 0) {
        $res = $el->Update($arResult['INDICATOR_NAMES'][$arValue[0]], $arFields);
    } else {
        $arResult['INDICATOR_NAMES'][$arValue[0]] = $el->Add($arFields);
        $res = ($arResult['INDICATOR_NAMES'][$arValue[0]]>0);
    }

    if (!$res) {
        echo $el->LAST_ERROR;
    }
}
