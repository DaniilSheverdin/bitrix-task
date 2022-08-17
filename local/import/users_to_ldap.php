<?

#$_SERVER['DOCUMENT_ROOT']='/var/www/corp';
require_once $_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_before.php";
CModule::IncludeModule('citto.integration');

#use \Citto\Integration;
$IBLOCK_ID = 5;
#$rConnect  = \Citto\Integration\Source1C::Connect1C();
CModule::IncludeModule('iblock');
$filter = array
    (

);
$rsGroups = CGroup::GetList(($by = "c_sort"), ($order = "desc"), $filter); // выбираем группы
while ($arGroup = $rsGroups->GetNext()) {
    $NAME_DEP = explode(': Сотрудники', $arGroup['NAME']);
    if (count($NAME_DEP) > 1) {
        $arGroupsByName[$NAME_DEP[0]]['ID']      = $arGroup['ID'];
        $arGroupsByName[$NAME_DEP[0]]['SITE_ID'] = explode('_', $arGroup['STRING_ID'])[1];
    }
}

$arSectionSyncs  = [];
$arSectionGroups = [];
$arSectionNames  = [];
$arFilter        = array('IBLOCK_ID' => $IBLOCK_ID); // выберет потомков без учета активности
$rsSect          = CIBlockSection::GetList(array('left_margin' => 'asc'), $arFilter);
while ($arSect = $rsSect->GetNext()) {
    if ($arSect['XML_ID'] != '') {
        if ($arSect['DEPTH_LEVEL'] == 2) {
            $arGroup = $arGroupsByName['Корпоративный портал'];
        }
        if ($arGroupsByName[$arSect['NAME']]['ID'] != '') {
            $arGroup = $arGroupsByName[$arSect['NAME']];
        }

        //echo "<pre>";print_r($arGroup);echo "</pre>";
        $arSectionSyncs[$arSect['XML_ID']]  = $arSect['ID'];
        $arSectionGroups[$arSect['XML_ID']] = $arGroup;
        $depth_level                        = $arSect['DEPTH_LEVEL'];
    }
}
//echo "<pre>";print_r($arSectionGroups);echo "</pre>";
//$arEmployees    = \Citto\Integration\Source1C::GetEmployyes($rConnect);
$arUsersByLogin = [];
$arUsersBySID   = [];
$filter         = array
    (
    'ACTIVE' => "Y",
    'UF_SID'=>'',
    'EXTERNAL_AUTH_ID'=>''
);
$userParams = array(
    'SELECT' => array('UF_SID','UF_DEPARTMENT'),
    'FIELDS' => array(
        'ID',
        'LOGIN',
        'IS_ONLINE',
        'EXTERNAL_AUTH_ID'
    ),
);
$count=0;
$rsUsers = CUser::GetList(($by = "personal_country"), ($order = "desc"), $filter, $userParams);

while ($arUser = $rsUsers->GetNext()) {
    #echo "<pre>";print_r($arUser);echo "</pre>";
    #die();
    $need=true;
    $arGroups = CUser::GetUserGroup($arUser['ID']);
    if (in_array(85, $arGroups)) {
        $key=array_search(12, $arGroups);
        if ($key>0) {
            //echo "<pre>";print_r($arUser);echo "</pre>";
            unset($arGroups[$key]);
            $count++;
        }
        $key=array_search(25, $arGroups);
        if ($key>0) {
            //echo "<pre>";print_r($arUser);echo "</pre>";
            unset($arGroups[$key]);
            $count++;
        }
    } elseif (!in_array(12, $arGroups)) {
        $need=false;
    }

    if ($need) {
        $user = new CUser();
        $fields = array(
          "GROUP_ID"          => $arGroups,
          "EXTERNAL_AUTH_ID"          => "LDAP#1",
        );
        $user->Update($arUser['ID'], $fields);
        $strError .= $user->LAST_ERROR;
    }
}
//echo "<pre>";print_r();echo "</pre>";

foreach ($arErrors as $k => $v) {
    echo "<pre>";
    print_r($k);
    echo "</pre>";
    echo "<pre>";
    print_r(count($v));
    echo "</pre>";
}
echo "<pre>";
print_r($arCounts);
echo "</pre>";
