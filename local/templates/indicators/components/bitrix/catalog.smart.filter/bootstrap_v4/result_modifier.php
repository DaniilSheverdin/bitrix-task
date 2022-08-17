<?

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) {
    die();
}

/*
if (isset($arParams["TEMPLATE_THEME"]) && !empty($arParams["TEMPLATE_THEME"])) {
    $arAvailableThemes = array();
    $dir = trim(preg_replace("'[\\\\/]+'", "/", dirname(__FILE__)."/themes/"));
    if (is_dir($dir) && $directory = opendir($dir)) {
        while (($file = readdir($directory)) !== false) {
            if ($file != "." && $file != ".." && is_dir($dir.$file)) {
                $arAvailableThemes[] = $file;
            }
        }
        closedir($directory);
    }

    if ($arParams["TEMPLATE_THEME"] == "site") {
        $solution = COption::GetOptionString("main", "wizard_solution", "", SITE_ID);
        if ($solution == "eshop") {
            $templateId = COption::GetOptionString("main", "wizard_template_id", "eshop_bootstrap", SITE_ID);
            $templateId = (preg_match("/^eshop_adapt/", $templateId)) ? "eshop_adapt" : $templateId;
            $theme = COption::GetOptionString("main", "wizard_".$templateId."_theme_id", "blue", SITE_ID);
            $arParams["TEMPLATE_THEME"] = (in_array($theme, $arAvailableThemes)) ? $theme : "blue";
        }
    } else {
        $arParams["TEMPLATE_THEME"] = (in_array($arParams["TEMPLATE_THEME"], $arAvailableThemes)) ? $arParams["TEMPLATE_THEME"] : "blue";
    }
} else {
    $arParams["TEMPLATE_THEME"] = "blue";
}
*/

$arParams["TEMPLATE_THEME"] = "blue";

$arParams["FILTER_VIEW_MODE"] = (isset($arParams["FILTER_VIEW_MODE"]) && toUpper($arParams["FILTER_VIEW_MODE"]) == "HORIZONTAL") ? "HORIZONTAL" : "VERTICAL";
$arParams["POPUP_POSITION"] = (isset($arParams["POPUP_POSITION"]) && in_array($arParams["POPUP_POSITION"], array("left", "right"))) ? $arParams["POPUP_POSITION"] : "left";

foreach ($arResult["ITEMS"] as &$arItem) {
    uasort(
        $arItem['VALUES'],
        function ($a, $b) {
            if ($a['DISABLED'] == $b['DISABLED']) {
                return strnatcmp($b['ELEMENT_COUNT'], $a['ELEMENT_COUNT']);
            }
            return strnatcmp($a['DISABLED'], $b['DISABLED']);
        }
    );
}

if (isset($_REQUEST['filter']['CATEGORY'])) {
    foreach ($_REQUEST['filter']['CATEGORY'] as $cat) {
        $arResult['HIDDEN'][] = [
            'CONTROL_ID'    => $cat,
            'CONTROL_NAME'  => 'filter[CATEGORY][]',
            'HTML_VALUE'    => $cat,
        ];
    }

    foreach ($arResult["ITEMS"] as $key => $value) {
        if ($value['CODE'] == 'TYPE' && count($value['VALUES']) == 1) {
            unset($arResult["ITEMS"][ $key ]);
        }
    }
}

// global $arrFilter;

// if ($arrFilter['=PROPERTY_2635'][0] == 1661) {
//     /*
//      * Статистические данные
//      */
//     foreach ($arResult["ITEMS"] as $key => $value) {
//         if ($value['CODE'] == 'STRUCTURE') {
//             $arResult["ITEMS"][ $key ]['DISPLAY_TYPE'] = 'P';
//             $arResult["ITEMS"][ $key ]['CODE'] = 'STRUCTURE_STAT';
//         } elseif ($value['CODE'] == 'THEME') {
//             unset($arResult["ITEMS"][ $key ]);
//         }
//     }
// } else {
//     /*
//      * Динамические данные
//      */
//     foreach ($arResult["ITEMS"] as $key => $value) {
//         if (in_array($value['CODE'], ['AFFILIATION', 'THEME_STAT'])) {
//             unset($arResult["ITEMS"][ $key ]);
//         }
//     }
// }

/*
 * Скрыть тип данных
 */
// foreach ($arResult["ITEMS"] as $key => $value) {
//     if ($value['CODE'] == 'TYPE') {
//         foreach ($value['VALUES'] as $val) {
//             if ($val['CHECKED']) {
//                 unset($arResult["ITEMS"][ $key ]);
//                 $arResult['HIDDEN'][] = [
//                     'CONTROL_ID'    => $val['FACET_VALUE'],
//                     'CONTROL_NAME'  => $val['CONTROL_NAME'],
//                     'HTML_VALUE'    => $val['HTML_VALUE'],
//                 ];
//             }
//         }
//     } elseif ($value['CODE'] == 'THEME_STAT') {
//         $arResult["ITEMS"][ $key ]['NAME'] = 'Тематика статданных';
//     }
// }
