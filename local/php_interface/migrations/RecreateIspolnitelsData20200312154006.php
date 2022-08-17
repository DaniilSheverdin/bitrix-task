<?php

namespace Sprint\Migration;


class RecreateIspolnitelsData20200312154006 extends Version
{

    protected $description = "";

    public function up() {
        $helper = new HelperManager();
        $arOmsu=[];
		$arOthers=[];
		$filter = Array('UF_DEPARTMENT'=>2137);
		$rsUsers = \CUser::GetList(($by = "NAME"), ($order = "desc"), $filter,array("SELECT"=>array("UF_*")));
		while ($arUser = $rsUsers->Fetch()) {
			if(substr_count(strtolower($arUser['NAME']),'администрация')>0){
				$arOmsu[]=$arUser;
			}else{
				$arOthers[]=$arUser;
			}
		}
		$rsParentSection = \CIBlockSection::GetByID(2137);
		if ($arParentSection = $rsParentSection->GetNext())
		{
		   $arFilter = array('IBLOCK_ID' => $arParentSection['IBLOCK_ID'],'>LEFT_MARGIN' => $arParentSection['LEFT_MARGIN'],'<RIGHT_MARGIN' => $arParentSection['RIGHT_MARGIN'],'>DEPTH_LEVEL' => $arParentSection['DEPTH_LEVEL']);
		   $rsSect = \CIBlockSection::GetList(array('left_margin' => 'asc'),$arFilter);
		   while ($arSect = $rsSect->GetNext())
		   {
		       $arParentSection['CHILDS'][$arSect['ID']]=$arSect;
		   }
		}

		if(count($arParentSection['CHILDS'])==0){
			$bSection = new \CIBlockSection;
			$arFields = Array(
			  "ACTIVE" => 'Y',
			  "IBLOCK_SECTION_ID" => $arParentSection['ID'],
			  "IBLOCK_ID" => $arParentSection['IBLOCK_ID'],
			  "NAME" => 'ОМСУ',
			  "XML_ID"=>'control_poruch_omsu'
			  );


			$sIdOmsu = $bSection->Add($arFields);
			$rSection = ($sIdOmsu>0);
			if($sIdOmsu > 0){
				$arParentSection['CHILDS'][$sIdOmsu]=\CIBlockSection::GetByID($sIdOmsu)->GetNext();
			}

			if(!$rSection)
			  echo $bSection->LAST_ERROR;
			$bSection = new \CIBlockSection;
			$arFields = Array(
			  "ACTIVE" => 'Y',
			  "IBLOCK_SECTION_ID" => $arParentSection['ID'],
			  "IBLOCK_ID" => $arParentSection['IBLOCK_ID'],
			  "NAME" => 'Внешние организации',
			  "XML_ID"=>'control_poruch_others'
			  );

			  $sIdOther = $bSection->Add($arFields);
			  $rSection = ($sIdOther>0);
			if($sIdOther > 0){
				$arParentSection['CHILDS'][$sIdOther]=\CIBlockSection::GetByID($sIdOther)->GetNext();
			}
			if(!$rSection)
			  echo $bSection->LAST_ERROR;
		}
		foreach ($arParentSection['CHILDS'] as $sKey => $arSection) {
			if($arSection['XML_ID']=='control_poruch_omsu'){
				$arFilter = array('IBLOCK_ID' => $arSection['IBLOCK_ID'],'>LEFT_MARGIN' => $arSection['LEFT_MARGIN'],'<RIGHT_MARGIN' => $arSection['RIGHT_MARGIN'],'>DEPTH_LEVEL' => $arSection['DEPTH_LEVEL']);
			   $rsSect = \CIBlockSection::GetList(array('left_margin' => 'asc'),$arFilter);
			   while ($arSect = $rsSect->GetNext())
			   {
			       $arSection['CHILDS'][$arSect['ID']]=$arSect;
			       $arSection['CHILDS_XMLS'][$arSect['XML_ID']]=$arSect['ID'];
			   }
			   foreach ($arOmsu as $sKey => $arOmsuLog) {
			   	$bSection = new \CIBlockSection;
				$arFields = Array(
				  "ACTIVE" => 'Y',
				  "IBLOCK_SECTION_ID" => $arSection['ID'],
				  "IBLOCK_ID" => $arSection['IBLOCK_ID'],
				  "NAME" => $arOmsuLog['NAME'],
				  "XML_ID"=>'control_poruch_omsu_'.$arOmsuLog['LOGIN'],
				  'UF_HEAD'=>$arOmsuLog['ID']
				  );
				if($arSection['CHILDS_XMLS'][$arFields['XML_ID']] > 0)
				{
				  $res = $bSection->Update($arSection['CHILDS_XMLS'][$arFields['XML_ID']], $arFields);
				}
				else
				{
				  $arSection['CHILDS_XMLS'][$arFields['XML_ID']] = $bSection->Add($arFields);
				  $arSection['CHILDS'][$arSection['CHILDS_XMLS'][$arFields['XML_ID']]]=\CIBlockSection::GetByID($arSection['CHILDS_XMLS'][$arFields['XML_ID']])->GetNext();
				  $res = ($ID>0);
				}
				if(!$res){
				  echo $bSection->LAST_ERROR;
				}else{
					$oUser = new CUser;
					$arFields = Array( 
						"UF_DEPARTMENT" => array($arSection['CHILDS_XMLS'][$arFields['XML_ID']]), 
					); 
					$oUser->Update($arOmsuLog['ID'], $arFields);
				}
			   }
			   
			}elseif($arSection['XML_ID']=='control_poruch_others'){
				foreach ($arOthers as $sKey => $arOther) {
					$oUser = new CUser;
					$arFields = Array( 
						"UF_DEPARTMENT" => array($arSection['ID']), 
					); 
					$oUser->Update($arOther['ID'], $arFields);
				}
			}
		}

    }

    public function down() {
        $helper = new HelperManager();

        //your code ...

    }

}
