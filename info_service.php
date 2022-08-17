<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Title");
?><?
function to_csv($file, $array)
{
    $fp = fopen($file, 'w');
    foreach ($array as $k => $v) {
        if ($k == 0) {
            $arr_num = [];
            foreach ($v as $k2 => $v2) {
                $arr_num[] = $k2;
            }
            fputcsv($fp, $arr_num);
        }
        fputcsv($fp, $v);
    }
}
CModule::IncludeModule('citto.integration');

$client=\Citto\Integration\Source1C::Connect1C();
$params = array(
    "RequestData" => array(
        "Operation" => "Subdivisions",
    ),
);

function buildTree(array $listIdParent)
{
    foreach ($listIdParent as $id => $node) {
        if ($node['PARENT_ID']) {
            $listIdParent[$node['PARENT_ID']]['sub'][$id] = &$listIdParent[$id];
            unset($node['PARENT_ID']);
        } else {
            $rootId = $id;
        }
    }

    return $listIdParent[$rootId];
}
$response       = $client->__soapCall("Integration", array($params));
$arSubdivisions = [];
foreach ($response->return->Data->Subdivisions->Subdivision as $key => $value) {
    $arSubdivisions[$value->ID] = array("ID" => $value->ID, "PARENT_ID" => $value->ParentID, 'FULLNAME' => $value->FullName, 'SHORTNAME' => $value->ShortName);

}
$params = array(
    "RequestData" => array(
        "Operation" => "Employees",
    ),
);
$response                       = $client->__soapCall("Integration", array($params));
$arEmployees                    = [];
$arEmployees['NO_SID']          = [];
$arEmployees['NO_SUBDIVISIONS'] = [];
$arPositions                    = [];
foreach ($response->return->Data->Employees->Employee as $key => $value) {
    $arEmployee = array(
        "SID"             => $value->SID,
        "NAME"            => $value->Name,
        'SUBDIVISION'     => $value->SubdivisionID,
        'SUBDIVISIONNAME' => $value->SubdivisionName,
        'POSITION'        => $value->PositionID,
        'POSITIONNAME'    => $value->PositionName,
        'LOGIN'           => $value->NameAD,
        'EMAIL'           => $value->Mail,
    );
    if (!array_key_exists($arEmployee['POSITION'], $arPositions)) {
        $arPositions[$arEmployee['POSITION']] = $arEmployee['POSITIONNAME'];
    }

    if ($arEmployee['SID'] == '') {
        $arEmployees['NO_SID'][] = $arEmployee;
    } else {
        if (array_key_exists($arEmployee['SUBDIVISION'], $arSubdivisions)) {
            $arSubdivisions[$arEmployee['SUBDIVISION']]['EMPLOYEES'][$arEmployee['SID']] = $arEmployee;
        } else {
            $arEmployees['NO_SUBDIVISIONS'][] = $arEmployee;
        }
    }
}
foreach ($arEmployees as $key => $value) {
    echo $key . "-" . count($value) . "<br>";
}

//echo "<pre>";print_r($arEmployees);echo "</pre>";
$arSubdivisionsTree = buildTree($arSubdivisions);
//echo "<pre>";print_r($arPositions);echo "</pre>";
if($_REQUEST['setxml']=='Y'){

}else{
if($_REQUEST['tree']=='full'){
    echo "<pre>";
        print_r($arSubdivisions);
    echo "</pre>";
}else{
echo "<pre>";
    print_r('NO_SID-'.count($arEmployees['NO_SID']));
echo "</pre>";
echo "<pre>";
    print_r($arEmployees['NO_SID']);
echo "</pre>";
echo "<pre>";
    print_r('NO_SID-'.count($arEmployees['NO_SUBDIVISIONS']));
echo "</pre>";
echo "<pre>";
    print_r($arEmployees['NO_SUBDIVISIONS']);
echo "</pre>";
}
}
?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>