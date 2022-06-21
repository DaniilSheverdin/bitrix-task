<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();
/** @var CBitrixComponent $this */
/** @var array $arParams */
/** @var array $arResult */
/** @var string $componentPath */
/** @var string $componentName */
/** @var string $componentTemplate */
/** @global CDatabase $DB */
/** @global CUser $USER */


class CNewsComplex extends CBitrixComponent
{
	protected $arDefaultUrlTemplates404 = array(
		"main" => "",
		"detail" => "#ELEMENT_ID#/",
		"news_edit" => "",
		"catalog_edit"=>"",
		"form"=>'',
		'list'=>''
	);

	protected $arDefaultVariableAliases404 = array();
	protected $arDefaultVariableAliases = array();
	protected $componentPage="";

	//настоящие имена переменных
	protected $arComponentVariables = array(
		"SECTION_ID",
		"SECTION_CODE",
		"ELEMENT_ID",
		"ELEMENT_CODE",
	);

	function prepareData(){
		global $APPLICATION;
		$this->arParams["EDIT_URL"] = $APPLICATION->GetCurPage("", array("edit", "delete", "CODE"));
		$this->arParams["LIST_URL"] = $this->arParams["EDIT_URL"];
	}

	function getResult(){
		global $APPLICATION;
		$arParams = $this->arParams;
		$arVariables = array();
		//если включен режим ЧПУ
		if ($arParams["SEF_MODE"] == "Y") 
		{
			$arUrlTemplates = CComponentEngine::makeComponentUrlTemplates($this->arDefaultUrlTemplates404, $arParams["SEF_URL_TEMPLATES"]);
			$arVariableAliases = CComponentEngine::makeComponentVariableAliases($this->arDefaultVariableAliases404, $arParams["VARIABLE_ALIASES"]);

			$engine = new CComponentEngine($this);
			if (CModule::IncludeModule('iblock')) {
				$engine->addGreedyPart("#SECTION_CODE_PATH#");
				$engine->setResolveCallback(array("CIBlockFindTools", "resolveComponentEngine"));
			}
			$this->componentPage = $engine->guessComponentPath(
				$arParams["SEF_FOLDER"],
				$arUrlTemplates,
				$arVariables
			);

			$b404 = false;
			if (!$this->componentPage) {
				$this->componentPage = "main";
				$b404 = true;
			}

			if ($b404 && CModule::IncludeModule('iblock')) {
				$folder404 = str_replace("\\", "/", $arParams["SEF_FOLDER"]);
				if ($folder404 != "/")
					$folder404 = "/" . trim($folder404, "/ \t\n\r\0\x0B") . "/";
				if (mb_substr($folder404, -1) == "/")
					$folder404 .= "index.php";

				if ($folder404 != $APPLICATION->GetCurPage(true)) {
					\Bitrix\Iblock\Component\Tools::process404(
						""
						, ($arParams["SET_STATUS_404"] === "Y")
						, ($arParams["SET_STATUS_404"] === "Y")
						, ($arParams["SHOW_404"] === "Y")
						, $arParams["FILE_404"]
					);
				}
			}

			CComponentEngine::initComponentVariables($this->componentPage, $this->arComponentVariables, $arVariableAliases, $arVariables);

			$arResult = array(
				"FOLDER" => $arParams["SEF_FOLDER"],
				"URL_TEMPLATES" => $arUrlTemplates,
				"VARIABLES" => $arVariables,
				"ALIASES" => $arVariableAliases,
			);
		}
		else{
			$arVariableAliases = CComponentEngine::makeComponentVariableAliases($this->arDefaultVariableAliases, $arParams["VARIABLE_ALIASES"]);

			/*
			* Добавим в $arVariables переменные из $_REQUEST, которые есть в $arComponentVariables и в $arVariableAliases.
			* Переменные из $arComponentVariables просто добавляются в $arVariables, если они есть в $_REQUEST. Переменные 
			* из $arVariableAliases добавляютcя под своими реальными именами, если в $_REQUEST есть соответствующий псевдоним.
			*/
			CComponentEngine::initComponentVariables(false, $this->arComponentVariables, $arVariableAliases, $arVariables);

			

			if(isset($arVariables["ELEMENT_ID"]) && intval($arVariables["ELEMENT_ID"]) > 0)
				$this->componentPage = "detail";
			elseif(isset($arVariables["ELEMENT_CODE"]) && $arVariables["ELEMENT_CODE"] <> '')
				$this->componentPage = "detail";
			else
				$this->componentPage = "main";

			$arResult = array(
				"FOLDER" => "",
				"URL_TEMPLATES" => array(
					"main" => htmlspecialcharsbx($this->APPLICATION->GetCurPage()),
					"section" => htmlspecialcharsbx($this->APPLICATION->GetCurPage()."?".$arVariableAliases["SECTION_ID"]."=#SECTION_ID#"),
					"detail" => htmlspecialcharsbx($this->APPLICATION->GetCurPage()."?".$arVariableAliases["ELEMENT_ID"]."=#ELEMENT_ID#"),
				),
				"VARIABLES" => $arVariables,
				"ALIASES" => $arVariableAliases
			);
		}

		//если нажали на ссылку для редактирования категорий
		if (!empty($_REQUEST['catalog_edit'])) {
			$this->componentPage = "catalog.edit";
		}
		//если нажали на ссылку для редактирования новостей
		elseif (!empty($_REQUEST['news_edit'])) {
			$this->componentPage = "news.edit";
		}

		//в блоке новостей, если нажали на редактирование/добавление
		if (!empty($_REQUEST["edit"]))
			$this->componentPage = "form";
		elseif (!empty($_REQUEST["delete"])) //в блоке новостей, если нажали на удаление
			$this->componentPage = "news.edit";

		$this->arResult=$arResult;
	}


	public function executeComponent()
	{
		try {
			$this->prepareData();
			$this->getResult();
			$this->includeComponentTemplate($this->componentPage);
				
		}
		catch (SystemException $e) {
			ShowError($e->getMessage());
		}
	}
}
?>