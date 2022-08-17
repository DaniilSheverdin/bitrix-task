<?
define('NO_MB_CHECK',true);
include($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');
CModule::IncludeModule('iblock');
$arSubdivisions=[];
$arSelect = Array("ID", "NAME");
$arFilter = Array("IBLOCK_ID"=>IntVal(508), "ACTIVE_DATE"=>"Y", "ACTIVE"=>"Y");
$res = CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
while($arFields = $res->GetNext())
{
	$arSubdivisions[$arFields['NAME']]=$arFields['ID'];
	//echo "<pre>";print_r($arFields);echo "</pre>";
}
$arErrors=[];
$arCounts=[];
$arCounts['Add']=0;
$arCounts['Error']=0;
$arCounts['Update']=0;
$rsParentSection = CIBlockSection::GetByID(1727);
if ($arParentSection = $rsParentSection->GetNext())
{
   $arFilter = array('IBLOCK_ID' => $arParentSection['IBLOCK_ID'],'>LEFT_MARGIN' => $arParentSection['LEFT_MARGIN'],'<RIGHT_MARGIN' => $arParentSection['RIGHT_MARGIN'],'DEPTH_LEVEL' => $arParentSection['DEPTH_LEVEL']+1); // выберет потомков без учета активности
   $rsSect = CIBlockSection::GetList(array('left_margin' => 'asc'),$arFilter,false,array('UF_HEAD'));
   while ($arSect = $rsSect->GetNext())
   {
   		if($arSect['UF_HEAD']!=''){
   			$el = new CIBlockElement;
   			$arData=array(
   				'NAME'=>$arSect['NAME'],
   				"IBLOCK_ID"      => 508,
				"PROPERTY_VALUES"=> array('RUKOVODITEL'=>$arSect['UF_HEAD']),
				"ACTIVE"         => "Y", 
   				);
   			
			if($arSubdivisions[$arData['NAME']]!=''){
				if($el->Update($arSubdivisions[$arData['NAME']], $arLoadProductArray)){
					$arCounts['Update']++;
				}
			}else{
				if($PRODUCT_ID = $el->Add($arData)) {
				   $arCounts['Add']++;
				} else {
					$arError=$arData;
					$arError['TEXT']=$el->LAST_ERROR;
				   	$arErrors['ADD_ERROR'][]=$arData;
				}
			}
   		}else{
   			$arErrors['NO_HEAD'][]=$arSect;
   			$arCounts['Error']++;
   		}
        
   }
}
echo "<pre>";print_r($arCounts);echo "</pre>";
echo "<pre>";print_r($arErrors);echo "</pre>";