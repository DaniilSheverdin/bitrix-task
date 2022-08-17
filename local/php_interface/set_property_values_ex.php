<?
/**
 * Стандартная функция
 * Изменения: При передаче ID файла, не создает новый, подробнее Search->//начало вставки
 * 
 */
$SetPropertyValuesEx = function($ELEMENT_ID, $IBLOCK_ID, $PROPERTY_VALUES, $FLAGS=array())
{
	//Check input parameters
	if(!is_array($PROPERTY_VALUES))
		return;

	if(!is_array($FLAGS))
		$FLAGS=array();
	//FLAGS - modify function behavior
	//NewElement - if present no db values select will be issued
	//DoNotValidateLists - if present list values do not validates against metadata tables

	global $DB;
	global $BX_IBLOCK_PROP_CACHE;

	$ELEMENT_ID = intval($ELEMENT_ID);
	if($ELEMENT_ID <= 0)
		return;

	$IBLOCK_ID = intval($IBLOCK_ID);
	if($IBLOCK_ID<=0)
	{
		$rs = $DB->Query("select IBLOCK_ID from b_iblock_element where ID=".$ELEMENT_ID);
		if($ar = $rs->Fetch())
			$IBLOCK_ID = $ar["IBLOCK_ID"];
		else
			return;
	}

	//Get property metadata
	$uniq_flt = $IBLOCK_ID."|SetPropertyValuesEx";

	if (!isset($BX_IBLOCK_PROP_CACHE[$IBLOCK_ID]))
	{
		$BX_IBLOCK_PROP_CACHE[$IBLOCK_ID] = array();
	}

	if (!isset($BX_IBLOCK_PROP_CACHE[$IBLOCK_ID][$uniq_flt]))
	{
		$BX_IBLOCK_PROP_CACHE[$IBLOCK_ID][$uniq_flt] = array(0=>array());
		$rs = CIBlockProperty::GetList(array(), array(
			"IBLOCK_ID"=>$IBLOCK_ID,
			"CHECK_PERMISSIONS"=>"N",
			"ACTIVE"=>"Y",
		));
		while($ar = $rs->Fetch())
		{
			$ar["ConvertToDB"] = false;
			if($ar["USER_TYPE"]!="")
			{
				$arUserType = CIBlockProperty::GetUserType($ar["USER_TYPE"]);
				if(array_key_exists("ConvertToDB", $arUserType))
					$ar["ConvertToDB"] = $arUserType["ConvertToDB"];
			}

			$BX_IBLOCK_PROP_CACHE[$IBLOCK_ID][$uniq_flt][$ar["ID"]] = $ar;
			//For CODE2ID conversion
			$BX_IBLOCK_PROP_CACHE[$IBLOCK_ID][$uniq_flt][0][$ar["CODE"]] = $ar["ID"];
			//VERSION
			$BX_IBLOCK_PROP_CACHE[$IBLOCK_ID][$uniq_flt]["VERSION"] = $ar["VERSION"];
		}
	}

	$PROPS_CACHE = $BX_IBLOCK_PROP_CACHE[$IBLOCK_ID][$uniq_flt];
	//Unify properties values arProps[$property_id]=>array($id=>array("VALUE", "DESCRIPTION"),....)
	$arProps = array();
	$propertyList = [];
	foreach($PROPERTY_VALUES as $key=>$value)
	{
		//Code2ID
		if(array_key_exists($key, $PROPS_CACHE[0]))
		{
			$key = $PROPS_CACHE[0][$key];
		}
		//It's not CODE so check if such ID exists
		else
		{
			$key = intval($key);
			if($key <= 0 || !array_key_exists($key, $PROPS_CACHE))
				continue;
		}

		$propertyList[$key] = $PROPS_CACHE[$key];
		if($PROPS_CACHE[$key]["PROPERTY_TYPE"]=="F")
		{
			if(is_array($value))
			{
				$ar = array_keys($value);
				if(array_key_exists("tmp_name", $value) || array_key_exists("del", $value))
				{
					$uni_value = array(array("ID"=>0,"VALUE"=>$value,"DESCRIPTION"=>""));
				}
				elseif($ar[0]==="VALUE" && $ar[1]==="DESCRIPTION")
				{
					$uni_value = array(array("ID"=>0,"VALUE"=>$value["VALUE"],"DESCRIPTION"=>$value["DESCRIPTION"]));
				}
				elseif(count($ar)===1 && $ar[0]==="VALUE")
				{
					$uni_value = array(array("ID"=>0,"VALUE"=>$value["VALUE"],"DESCRIPTION"=>""));
				}
				else //multiple values
				{
					$uni_value = array();
					foreach($value as $id=>$val)
					{
						if(is_array($val))
						{
							if(array_key_exists("tmp_name", $val) || array_key_exists("del", $val))
							{
								$uni_value[] = array("ID"=>$id,"VALUE"=>$val,"DESCRIPTION"=>"");
							}
							else
							{
								$ar = array_keys($val);
								if($ar[0]==="VALUE" && $ar[1]==="DESCRIPTION")
									$uni_value[] = array("ID"=>$id,"VALUE"=>$val["VALUE"],"DESCRIPTION"=>$val["DESCRIPTION"]);
								elseif(count($ar)===1 && $ar[0]==="VALUE")
									$uni_value[] = array("ID"=>$id,"VALUE"=>$val["VALUE"],"DESCRIPTION"=>"");
							}
						}
					}
				}
			}
			else
			{
				//There was no valid file array found so we'll skip this property
				$uni_value = array();
			}
		}
		elseif(!is_array($value))
		{
			$uni_value = array(array("VALUE"=>$value,"DESCRIPTION"=>""));
		}
		else
		{
			$ar = array_keys($value);
			if(count($ar)===2 && $ar[0]==="VALUE" && $ar[1]==="DESCRIPTION")
			{
				$uni_value = array(array("VALUE"=>$value["VALUE"],"DESCRIPTION"=>$value["DESCRIPTION"]));
			}
			elseif(count($ar)===1 && $ar[0]==="VALUE")
			{
				$uni_value = array(array("VALUE"=>$value["VALUE"],"DESCRIPTION"=>""));
			}
			else // multiple values
			{
				$uni_value = array();
				foreach($value as $id=>$val)
				{
					if(!is_array($val))
						$uni_value[] = array("VALUE"=>$val,"DESCRIPTION"=>"");
					else
					{
						$ar = array_keys($val);
						if($ar[0]==="VALUE" && $ar[1]==="DESCRIPTION")
							$uni_value[] = array("VALUE"=>$val["VALUE"],"DESCRIPTION"=>$val["DESCRIPTION"]);
						elseif(count($ar)===1 && $ar[0]==="VALUE")
							$uni_value[] = array("VALUE"=>$val["VALUE"],"DESCRIPTION"=>"");
					}
				}
			}
		}

		$arValueCounters = array();
		foreach($uni_value as $val)
		{
			if(!array_key_exists($key, $arProps))
			{
				$arProps[$key] = array();
				$arValueCounters[$key] = 0;
			}

			if($PROPS_CACHE[$key]["ConvertToDB"]!==false)
			{
				$arProperty = $PROPS_CACHE[$key];
				$arProperty["ELEMENT_ID"] = $ELEMENT_ID;
				$val = call_user_func_array($PROPS_CACHE[$key]["ConvertToDB"], array($arProperty, $val));
			}

			if(
				(!is_array($val["VALUE"]) && mb_strlen($val["VALUE"]) > 0)
				|| (is_array($val["VALUE"]) && count($val["VALUE"])>0)
			)
			{
				if(
					$arValueCounters[$key] == 0
					|| $PROPS_CACHE[$key]["MULTIPLE"]=="Y"
				)
				{
					if(!is_array($val["VALUE"]) || !isset($val["VALUE"]["del"]))
						$arValueCounters[$key]++;

					$arProps[$key][] = $val;
				}
			}
		}
	}

	if(count($arProps)<=0)
		return;

	//Read current property values from database
	$arDBProps = array();
	if(!array_key_exists("NewElement", $FLAGS))
	{
		if($PROPS_CACHE["VERSION"]==1)
		{
			$rs = $DB->Query("
				select *
				from b_iblock_element_property
				where IBLOCK_ELEMENT_ID=".$ELEMENT_ID."
				AND IBLOCK_PROPERTY_ID in (".implode(", ", array_keys($arProps)).")
			");
			while($ar=$rs->Fetch())
			{
				if(!array_key_exists($ar["IBLOCK_PROPERTY_ID"], $arDBProps))
					$arDBProps[$ar["IBLOCK_PROPERTY_ID"]] = array();
				$arDBProps[$ar["IBLOCK_PROPERTY_ID"]][$ar["ID"]] = $ar;
			}
		}
		else
		{
			$rs = $DB->Query("
				select *
				from b_iblock_element_prop_m".$IBLOCK_ID."
				where IBLOCK_ELEMENT_ID=".$ELEMENT_ID."
				AND IBLOCK_PROPERTY_ID in (".implode(", ", array_keys($arProps)).")
			");
			while($ar=$rs->Fetch())
			{
				if(!array_key_exists($ar["IBLOCK_PROPERTY_ID"], $arDBProps))
					$arDBProps[$ar["IBLOCK_PROPERTY_ID"]] = array();
				$arDBProps[$ar["IBLOCK_PROPERTY_ID"]][$ar["ID"]] = $ar;
			}
			$rs = $DB->Query("
				select *
				from b_iblock_element_prop_s".$IBLOCK_ID."
				where IBLOCK_ELEMENT_ID=".$ELEMENT_ID."
			");
			if($ar=$rs->Fetch())
			{
				foreach($PROPS_CACHE as $property_id=>$property)
				{
					if(	array_key_exists($property_id, $arProps)
						&& array_key_exists("PROPERTY_".$property_id, $ar)
						&& $property["MULTIPLE"]=="N"
						&& $ar["PROPERTY_".$property_id] != '')
					{
						$pr=array(
							"IBLOCK_PROPERTY_ID" => $property_id,
							"VALUE" => $ar["PROPERTY_".$property_id],
							"DESCRIPTION" => $ar["DESCRIPTION_".$property_id],
						);
						if(!array_key_exists($pr["IBLOCK_PROPERTY_ID"], $arDBProps))
							$arDBProps[$pr["IBLOCK_PROPERTY_ID"]] = array();
						$arDBProps[$pr["IBLOCK_PROPERTY_ID"]][$ELEMENT_ID.":".$property_id] = $pr;
					}
				}
			}
			else
			{
				$DB->Query("
				insert into b_iblock_element_prop_s".$IBLOCK_ID."
				(IBLOCK_ELEMENT_ID) values (".$ELEMENT_ID.")
			");
			}
		}
	}

	foreach (GetModuleEvents("iblock", "OnIBlockElementSetPropertyValuesEx", true) as $arEvent)
		ExecuteModuleEventEx($arEvent, array($ELEMENT_ID, $IBLOCK_ID, $PROPERTY_VALUES, $propertyList, $arDBProps));
	if (isset($arEvent))
		unset($arEvent);

	$arFilesToDelete = array();
	//Handle file properties
	foreach($arProps as $property_id=>$values)
	{
		if($PROPS_CACHE[$property_id]["PROPERTY_TYPE"]=="F")
		{
			foreach($values as $i=>$value)
			{
				$val = $value["VALUE"];
				if(strlen($val["del"]) > 0)
				{
					$val = "NULL";
				}
				else
				{
					$val["MODULE_ID"] = "iblock";
					unset($val["old_file"]);

					if(strlen($value["DESCRIPTION"])>0)
						$val["description"] = $value["DESCRIPTION"];
//начало вставки
					if(empty($val['ID'])){
						$val = CFile::SaveFile($val, "iblock");
					}else{
						$val = $val['ID'];
					}
//конец вставки
				}

				if($val=="NULL")
				{//Delete it! Actually it will not add an value
					unset($arProps[$property_id][$i]);
				}
				elseif(intval($val)>0)
				{
					$arProps[$property_id][$i]["VALUE"] = intval($val);
					if(strlen($value["DESCRIPTION"])<=0)
						$arProps[$property_id][$i]["DESCRIPTION"]=$arDBProps[$property_id][$value["ID"]]["DESCRIPTION"];
				}
				elseif(strlen($value["DESCRIPTION"])>0)
				{
					$arProps[$property_id][$i]["VALUE"] = $arDBProps[$property_id][$value["ID"]]["VALUE"];
					//Only needs to update description so CFile::Delete will not called
					unset($arDBProps[$property_id][$value["ID"]]);
				}
				else
				{
					$arProps[$property_id][$i]["VALUE"] = $arDBProps[$property_id][$value["ID"]]["VALUE"];
					//CFile::Delete will not called
					unset($arDBProps[$property_id][$value["ID"]]);
				}
			}

			if(array_key_exists($property_id, $arDBProps))
			{
				foreach($arDBProps[$property_id] as $id=>$value)
					$arFilesToDelete[] = array($value["VALUE"], $ELEMENT_ID, "PROPERTY", -1, $IBLOCK_ID);
			}
		}
	}

	foreach($arFilesToDelete as $ar)
		call_user_func_array(array("CIBlockElement", "DeleteFile"), $ar);

	//Now we'll try to find out properties which do not require any update
	if(!array_key_exists("NewElement", $FLAGS))
	{
		foreach($arProps as $property_id=>$values)
		{
			if($PROPS_CACHE[$property_id]["PROPERTY_TYPE"]!="F")
			{
				if(array_key_exists($property_id, $arDBProps))
				{
					$db_values = $arDBProps[$property_id];
					if(count($values) == count($db_values))
					{
						$bEqual = true;
						foreach($values as $id=>$value)
						{
							$bDBFound = false;
							foreach($db_values as $db_id=>$db_row)
							{
								if(strcmp($value["VALUE"],$db_row["VALUE"])==0 && strcmp($value["DESCRIPTION"],$db_row["DESCRIPTION"])==0)
								{
									unset($db_values[$db_id]);
									$bDBFound = true;
									break;
								}
							}
							if(!$bDBFound)
							{
								$bEqual = false;
								break;
							}
						}
						if($bEqual)
						{
							unset($arProps[$property_id]);
							unset($arDBProps[$property_id]);
						}
					}
				}
				elseif(count($values)==0)
				{
					//Values was not found in database neither no values input was given
					unset($arProps[$property_id]);
				}
			}
		}
	}

	//Init "commands" arrays
	$ar2Delete = array(
		"b_iblock_element_property" => array(/*property_id=>true, property_id=>true, ...*/),
		"b_iblock_element_prop_m".$IBLOCK_ID => array(/*property_id=>true, property_id=>true, ...*/),
		"b_iblock_section_element" => array(/*property_id=>true, property_id=>true, ...*/),
	);
	$ar2Insert = array(
		"values" => array(
			"b_iblock_element_property" => array(/*property_id=>value, property_id=>value, ...*/),
			"b_iblock_element_prop_m".$IBLOCK_ID => array(/*property_id=>value, property_id=>value, ...*/),
		),
		"sqls"=>array(
			"b_iblock_element_property" => array(/*property_id=>sql, property_id=>sql, ...*/),
			"b_iblock_element_prop_m".$IBLOCK_ID => array(/*property_id=>sql, property_id=>sql, ...*/),
			"b_iblock_section_element" => array(/*property_id=>sql, property_id=>sql, ...*/),
		),
	);
	$ar2Update = array(
		//"b_iblock_element_property" => array(/*property_id=>value, property_id=>value, ...*/),
		//"b_iblock_element_prop_m".$IBLOCK_ID => array(/*property_id=>value, property_id=>value, ...*/),
		//"b_iblock_element_prop_s".$IBLOCK_ID => array(/*property_id=>value, property_id=>value, ...*/),
	);

	foreach($arDBProps as $property_id=>$values)
	{
		if($PROPS_CACHE[$property_id]["VERSION"]==1)
		{
			$ar2Delete["b_iblock_element_property"][$property_id]=true;
		}
		elseif($PROPS_CACHE[$property_id]["MULTIPLE"]=="Y")
		{
			$ar2Delete["b_iblock_element_prop_m".$IBLOCK_ID][$property_id]=true;
			$ar2Update["b_iblock_element_prop_s".$IBLOCK_ID][$property_id]=false;//null
		}
		else
		{
			$ar2Update["b_iblock_element_prop_s".$IBLOCK_ID][$property_id]=false;//null
		}
		if($PROPS_CACHE[$property_id]["PROPERTY_TYPE"]=="G")
			$ar2Delete["b_iblock_section_element"][$property_id]=true;
	}

	foreach($arProps as $property_id=>$values)
	{
		$db_prop = $PROPS_CACHE[$property_id];
		if($db_prop["PROPERTY_TYPE"]=="L" && !array_key_exists("DoNotValidateLists",$FLAGS))
		{
			$arID=array();
			foreach($values as $value)
			{
				$value["VALUE"] = intval($value["VALUE"]);
				if($value["VALUE"]>0)
					$arID[]=$value["VALUE"];
			}
			if(count($arID)>0)
			{
				if($db_prop["VERSION"]==1)
				{
					$ar2Insert["sqls"]["b_iblock_element_property"][$property_id] = "
							INSERT INTO b_iblock_element_property
							(IBLOCK_ELEMENT_ID, IBLOCK_PROPERTY_ID, VALUE, VALUE_ENUM)
							SELECT ".$ELEMENT_ID.", P.ID, PEN.ID, PEN.ID
							FROM
								b_iblock_property P
								,b_iblock_property_enum PEN
							WHERE
								P.ID=".$property_id."
								AND P.ID=PEN.PROPERTY_ID
								AND PEN.ID IN (".implode(", ",$arID).")
					";
				}
				elseif($db_prop["MULTIPLE"]=="Y")
				{
					$ar2Insert["sqls"]["b_iblock_element_prop_m".$IBLOCK_ID][$property_id] = "
							INSERT INTO b_iblock_element_prop_m".$IBLOCK_ID."
							(IBLOCK_ELEMENT_ID, IBLOCK_PROPERTY_ID, VALUE, VALUE_ENUM)
							SELECT ".$ELEMENT_ID.", P.ID, PEN.ID, PEN.ID
							FROM
								b_iblock_property P
								,b_iblock_property_enum PEN
							WHERE
								P.ID=".$property_id."
								AND P.ID=PEN.PROPERTY_ID
								AND PEN.ID IN (".implode(", ",$arID).")
					";
					$ar2Update["b_iblock_element_prop_s".$IBLOCK_ID][$property_id]=false;//null
				}
				else
				{
					$rs = $DB->Query("
							SELECT PEN.ID
							FROM
								b_iblock_property P
								,b_iblock_property_enum PEN
							WHERE
								P.ID=".$property_id."
								AND P.ID=PEN.PROPERTY_ID
								AND PEN.ID IN (".implode(", ",$arID).")
					");
					if($ar = $rs->Fetch())
						$ar2Update["b_iblock_element_prop_s".$IBLOCK_ID][$property_id]=array("VALUE"=>$ar["ID"],"DESCRIPTION"=>"");
				}
			}
			continue;
		}
		if($db_prop["PROPERTY_TYPE"]=="G")
		{
			$arID=array();
			foreach($values as $value)
			{
				$value["VALUE"] = intval($value["VALUE"]);
				if($value["VALUE"]>0)
					$arID[]=$value["VALUE"];
			}
			if(count($arID)>0)
			{
				if($db_prop["VERSION"]==1)
				{
					$ar2Insert["sqls"]["b_iblock_element_property"][$property_id] = "
							INSERT INTO b_iblock_element_property
							(IBLOCK_ELEMENT_ID, IBLOCK_PROPERTY_ID, VALUE, VALUE_NUM)
							SELECT ".$ELEMENT_ID.", P.ID, S.ID, S.ID
							FROM
								b_iblock_property P
								,b_iblock_section S
							WHERE
								P.ID=".$property_id."
								AND S.IBLOCK_ID = P.LINK_IBLOCK_ID
								AND S.ID IN (".implode(", ",$arID).")
					";
				}
				elseif($db_prop["MULTIPLE"]=="Y")
				{
					$ar2Insert["sqls"]["b_iblock_element_prop_m".$IBLOCK_ID][$property_id] = "
							INSERT INTO b_iblock_element_prop_m".$IBLOCK_ID."
							(IBLOCK_ELEMENT_ID, IBLOCK_PROPERTY_ID, VALUE, VALUE_NUM)
							SELECT ".$ELEMENT_ID.", P.ID, S.ID, S.ID
							FROM
								b_iblock_property P
								,b_iblock_section S
							WHERE
								P.ID=".$property_id."
								AND S.IBLOCK_ID = P.LINK_IBLOCK_ID
								AND S.ID IN (".implode(", ",$arID).")
					";
					$ar2Update["b_iblock_element_prop_s".$IBLOCK_ID][$property_id]=false;//null
				}
				else
				{
					$rs = $DB->Query("
							SELECT S.ID
							FROM
								b_iblock_property P
								,b_iblock_section S
							WHERE
								P.ID=".$property_id."
								AND S.IBLOCK_ID = P.LINK_IBLOCK_ID
								AND S.ID IN (".implode(", ",$arID).")
					");
					if($ar = $rs->Fetch())
						$ar2Update["b_iblock_element_prop_s".$IBLOCK_ID][$property_id]=array("VALUE"=>$ar["ID"],"DESCRIPTION"=>"");
				}
				$ar2Insert["sqls"]["b_iblock_section_element"][$property_id] = "
					INSERT INTO b_iblock_section_element
					(IBLOCK_ELEMENT_ID, IBLOCK_SECTION_ID, ADDITIONAL_PROPERTY_ID)
					SELECT ".$ELEMENT_ID.", S.ID, P.ID
					FROM b_iblock_property P, b_iblock_section S
					WHERE P.ID=".$property_id."
						AND S.IBLOCK_ID = P.LINK_IBLOCK_ID
						AND S.ID IN (".implode(", ",$arID).")
				";
			}
			continue;
		}
		foreach($values as $value)
		{
			if($db_prop["VERSION"]==1)
			{
				$ar2Insert["values"]["b_iblock_element_property"][$property_id][]=$value;
			}
			elseif($db_prop["MULTIPLE"]=="Y")
			{
				$ar2Insert["values"]["b_iblock_element_prop_m".$IBLOCK_ID][$property_id][]=$value;
				$ar2Update["b_iblock_element_prop_s".$IBLOCK_ID][$property_id]=false;//null
			}
			else
			{
				$ar2Update["b_iblock_element_prop_s".$IBLOCK_ID][$property_id]=$value;
			}
		}
	}

	foreach($ar2Delete as $table=>$arID)
	{
		if(count($arID)>0)
		{
			if($table=="b_iblock_section_element")
				$DB->Query("
					delete from ".$table."
					where IBLOCK_ELEMENT_ID = ".$ELEMENT_ID."
					and  ADDITIONAL_PROPERTY_ID in (".implode(", ", array_keys($arID)).")
				");
			else
				$DB->Query("
					delete from ".$table."
					where IBLOCK_ELEMENT_ID = ".$ELEMENT_ID."
					and IBLOCK_PROPERTY_ID in (".implode(", ", array_keys($arID)).")
				");
		}
	}

	foreach($ar2Insert["values"] as $table=>$properties)
	{
		$strSqlPrefix = "
				insert into ".$table."
				(IBLOCK_PROPERTY_ID, IBLOCK_ELEMENT_ID, VALUE, VALUE_ENUM, VALUE_NUM, DESCRIPTION)
				values
		";

		$maxValuesLen = $DB->type=="MYSQL"?1024:0;
		$strSqlValues = "";
		foreach($properties as $property_id=>$values)
		{
			foreach($values as $value)
			{
				if(strlen($value["VALUE"])>0)
				{
					$strSqlValues .= ",\n(".
						$property_id.", ".
						$ELEMENT_ID.", ".
						"'".$DB->ForSQL($value["VALUE"])."', ".
						intval($value["VALUE"]).", ".
						CIBlock::roundDB($value["VALUE"]).", ".
						(strlen($value["DESCRIPTION"])? "'".$DB->ForSQL($value["DESCRIPTION"])."'": "null")." ".
					")";
				}
				if(strlen($strSqlValues)>$maxValuesLen)
				{
					$DB->Query($strSqlPrefix.substr($strSqlValues, 2));
					$strSqlValues = "";
				}
			}
		}
		if(strlen($strSqlValues)>0)
		{
			$DB->Query($strSqlPrefix.substr($strSqlValues, 2));
			$strSqlValues = "";
		}
	}

	foreach($ar2Insert["sqls"] as $table=>$properties)
	{
		foreach($properties as $property_id=>$sql)
		{
			$DB->Query($sql);
		}
	}

	foreach($ar2Update as $table=>$properties)
	{
		$tableFields = $DB->GetTableFields($table);
		if(count($properties)>0)
		{
			$arFields = array();
			foreach($properties as $property_id=>$value)
			{
				if($value===false || mb_strlen($value["VALUE"])<=0)
				{
					$arFields[] = "PROPERTY_".$property_id." = null";
					if (isset($tableFields["DESCRIPTION_".$property_id]))
					{
						$arFields[] = "DESCRIPTION_".$property_id." = null";
					}
				}
				else
				{
					$arFields[] = "PROPERTY_".$property_id." = '".$DB->ForSQL($value["VALUE"])."'";
					if (isset($tableFields["DESCRIPTION_".$property_id]))
					{
						if(strlen($value["DESCRIPTION"]))
							$arFields[] = "DESCRIPTION_".$property_id." = '".$DB->ForSQL($value["DESCRIPTION"])."'";
						else
							$arFields[] = "DESCRIPTION_".$property_id." = null";
					}
				}
			}
			$DB->Query("
				update ".$table."
				set ".implode(",\n", $arFields)."
				where IBLOCK_ELEMENT_ID = ".$ELEMENT_ID."
			");
		}
	}
	/****************************** QUOTA ******************************/
	$_SESSION["SESS_RECOUNT_DB"] = "Y";
	/****************************** QUOTA ******************************/

	foreach (GetModuleEvents("iblock", "OnAfterIBlockElementSetPropertyValuesEx", true) as $arEvent)
		ExecuteModuleEventEx($arEvent, array($ELEMENT_ID, $IBLOCK_ID, $PROPERTY_VALUES, $FLAGS));
};