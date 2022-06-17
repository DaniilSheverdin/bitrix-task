<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var CBitrixComponent $this */
/** @var array $arParams */
/** @var array $arResult */
/** @var string $componentPath */
/** @var string $componentName */
/** @var string $componentTemplate */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */



$arDefaultUrlTemplates404 = array(
	"main" => "",
	"detail" => "#ELEMENT_ID#/",
	"news_edit" => "",
	"catalog_edit"=>"",
	"form"=>'',
	'list'=>''
);

$arDefaultVariableAliases404 = array();

$arDefaultVariableAliases = array();

//настоящие имена переменных
$arComponentVariables = array(
	"SECTION_ID",
	"SECTION_CODE",
	"ELEMENT_ID",
	"ELEMENT_CODE",
);



/* Compatibility with deleted DETAIL_STRICT_SECTION_CHECK */
if (isset($arParams["STRICT_SECTION_CHECK"]))
	$arParams["DETAIL_STRICT_SECTION_CHECK"] = $arParams["STRICT_SECTION_CHECK"];
else
	$arParams["STRICT_SECTION_CHECK"] = $arParams["DETAIL_STRICT_SECTION_CHECK"];

if($arParams["SEF_MODE"] == "Y")
{
	$arVariables = array();

	$arUrlTemplates = CComponentEngine::makeComponentUrlTemplates($arDefaultUrlTemplates404, $arParams["SEF_URL_TEMPLATES"]);
	$arVariableAliases = CComponentEngine::makeComponentVariableAliases($arDefaultVariableAliases404, $arParams["VARIABLE_ALIASES"]);

	$engine = new CComponentEngine($this);
	if (CModule::IncludeModule('iblock'))
	{
		$engine->addGreedyPart("#SECTION_CODE_PATH#");
		$engine->setResolveCallback(array("CIBlockFindTools", "resolveComponentEngine"));
	}
	$componentPage = $engine->guessComponentPath(
		$arParams["SEF_FOLDER"],
		$arUrlTemplates,
		$arVariables
	);

	$b404 = false;
	if(!$componentPage)
	{
		$componentPage = "main";
		$b404 = true;
	}

	if($b404 && CModule::IncludeModule('iblock'))
	{
		$folder404 = str_replace("\\", "/", $arParams["SEF_FOLDER"]);
		if ($folder404 != "/")
			$folder404 = "/".trim($folder404, "/ \t\n\r\0\x0B")."/";
		if (mb_substr($folder404, -1) == "/")
			$folder404 .= "index.php";

		if ($folder404 != $APPLICATION->GetCurPage(true))
		{
			\Bitrix\Iblock\Component\Tools::process404(
				""
				,($arParams["SET_STATUS_404"] === "Y")
				,($arParams["SET_STATUS_404"] === "Y")
				,($arParams["SHOW_404"] === "Y")
				,$arParams["FILE_404"]
			);
		}
	}

	/*
	 * Добавим в $arVariables переменные из $_REQUEST, которые есть в $arComponentVariables и в $arVariableAliases.
	 * Переменные из $arComponentVariables просто добавляются в $arVariables, если они есть в $_REQUEST. Переменные 
	 * из $arVariableAliases добавляютcя под своими реальными именами, если в $_REQUEST есть соответствующий псевдоним.
	 */
	CComponentEngine::initComponentVariables($componentPage, $arComponentVariables, $arVariableAliases, $arVariables);

	$arResult = array(
		"FOLDER" => $arParams["SEF_FOLDER"],
		"URL_TEMPLATES" => $arUrlTemplates,
		"VARIABLES" => $arVariables,
		"ALIASES" => $arVariableAliases,
	);
}
else
{
	$arVariableAliases = CComponentEngine::makeComponentVariableAliases($arDefaultVariableAliases, $arParams["VARIABLE_ALIASES"]);

	/*
	 * Добавим в $arVariables переменные из $_REQUEST, которые есть в $arComponentVariables и в $arVariableAliases.
	 * Переменные из $arComponentVariables просто добавляются в $arVariables, если они есть в $_REQUEST. Переменные 
	 * из $arVariableAliases добавляютcя под своими реальными именами, если в $_REQUEST есть соответствующий псевдоним.
	 */
	CComponentEngine::initComponentVariables(false, $arComponentVariables, $arVariableAliases, $arVariables);

	$componentPage = "";

	if(isset($arVariables["ELEMENT_ID"]) && intval($arVariables["ELEMENT_ID"]) > 0)
		$componentPage = "detail";
	elseif(isset($arVariables["ELEMENT_CODE"]) && $arVariables["ELEMENT_CODE"] <> '')
		$componentPage = "detail";
	else
		$componentPage = "main";

	$arResult = array(
		"FOLDER" => "",
		"URL_TEMPLATES" => array(
			"main" => htmlspecialcharsbx($APPLICATION->GetCurPage()),
			"section" => htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arVariableAliases["SECTION_ID"]."=#SECTION_ID#"),
			"detail" => htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arVariableAliases["ELEMENT_ID"]."=#ELEMENT_ID#"),
		),
		"VARIABLES" => $arVariables,
		"ALIASES" => $arVariableAliases
	);
}

//если нажали на ссылку для редактирования категорий, то вызывается(?) компонент редактирования категорий
if (!empty($_REQUEST['catalog_edit'])){
	$componentPage = "catalog.edit";
}
//если нажали на ссылку для редактирования категорий, то вызывается(?) компонент редактирования категорий
elseif (!empty($_REQUEST['news_edit'])){
	$componentPage = "news.edit";
}

if (!empty($_REQUEST["edit"]))
	$componentPage = "form";
elseif (!empty($_REQUEST["delete"]))
	$componentPage = "news.edit";
$arParams["EDIT_URL"] = $APPLICATION->GetCurPage("", array("edit", "delete", "CODE"));
$this->includeComponentTemplate($componentPage);
?>