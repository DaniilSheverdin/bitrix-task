<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle(GetMessage("WD_WEBDAV"));
?><?$APPLICATION->IncludeComponent(
	"bitrix:webdav",
	"",
	Array(
		"IBLOCK_TYPE" => "services", 
		"IBLOCK_ID" => "597", 
		
		"SEF_MODE" => "N", 
		"SEF_FOLDER" => "/docslibrary/", 
		"BASE_URL" => "/docslibrary/", 
		
		"CACHE_TYPE" => "A", 
		"CACHE_TIME" => "3600", 
		"SET_TITLE" => "Y", 
	)
);?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>