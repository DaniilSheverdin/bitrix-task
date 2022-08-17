<<<<<<< HEAD
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/intranet/public/services/vote_result.php");
$APPLICATION->SetTitle(GetMessage("SERVICES_TITLE"));
?><?$APPLICATION->IncludeComponent(
	"bitrix:voting.result",
	"",
	Array(
		"VOTE_ID" => $_REQUEST["VOTE_ID"], 
		"CACHE_TYPE" => "A", 
		"CACHE_TIME" => "1200" 
	)
);?> 
<br />
 
<br />
 <a href="/services/votes.php"><?=GetMessage("SERVICES_LINK")?></a>
<br />
=======
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/intranet/public/services/vote_result.php");
$APPLICATION->SetTitle(GetMessage("SERVICES_TITLE"));
?><?$APPLICATION->IncludeComponent(
	"bitrix:voting.result",
	"",
	Array(
		"VOTE_ID" => $_REQUEST["VOTE_ID"], 
		"CACHE_TYPE" => "A", 
		"CACHE_TIME" => "1200" 
	)
);?> 
<br />
 
<br />
 <a href="/services/votes.php"><?=GetMessage("SERVICES_LINK")?></a>
<br />
>>>>>>> e0a0eba79 (init)
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>