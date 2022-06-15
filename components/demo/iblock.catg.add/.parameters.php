<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();
use Bitrix\Main\Loader; // подключение бибилотек (?)

if (!Loader::includeModule("iblock")) //если не подключены инф. блоки, то ретерн
	return;

$arIBlockType = CIBlockParameters::GetIBlockTypes(); //получаем типы инф. блоков

$arIBlock = array(); //получаем ин. блоки
$rsIBlock = CIBlock::GetList(array("sort" => "asc"), array("TYPE" => $arCurrentValues["IBLOCK_TYPE"], "ACTIVE" => "Y"));
while ($arr = $rsIBlock->Fetch()) 
{
	$arIBlock[$arr["ID"]] = "[" . $arr["ID"] . "] " . $arr["NAME"];
}

$arComponentParameters = array(
	"GROUPS" => array(),
	"PARAMETERS" => array(
		"NAME_OF_SECTION" => array(
			"PARENT" => "BASE", "NAME" => "Заголовок поля формы добавления", "TYPE" => "STRING", "MULTIPLE" => "N", "DEFAULT" => "Имя категории", ),
		"IBLOCK_TYPE" => array(
			"PARENT" => "DATA_SOURCE",
			"NAME" => "Тип информационного блока",
			"TYPE" => "LIST",
			"ADDITIONAL_VALUES" => "Y",
			"VALUES" => $arIBlockType,
			"REFRESH" => "Y",
		),

		"IBLOCK_ID" => array(
			"PARENT" => "DATA_SOURCE",
			"NAME" => "Инормационный блок",
			"TYPE" => "LIST",
			"ADDITIONAL_VALUES" => "Y",
			"VALUES" => $arIBlock,
			"REFRESH" => "Y",
		),
	),
);