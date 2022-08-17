<<<<<<< HEAD
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/intranet/public/services/index.php");
$APPLICATION->SetTitle(GetMessage("SERVICES_TITLE"));
?>
<p><?$APPLICATION->IncludeComponent(
	"bitrix:intranet.reserve_meeting",
	".default",
	array(
		"IBLOCK_TYPE" => "events",
		"IBLOCK_ID" => "180",
		"USERGROUPS_MODIFY" => array(),
		"USERGROUPS_RESERVE" => array(),
		"USERGROUPS_CLEAR" => array(),
		"SEF_MODE" => "N",
		"SET_NAVCHAIN" => "Y",
		"SET_TITLE" => "Y",
		"WEEK_HOLIDAYS" => array(0=>"5",1=>"6",),
	),
	false
);
?></p>

<p><a href="/edu/services/res_c.php"><?=GetMessage("SERVICES_LINK")?></a><br /></p>
=======
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/intranet/public/services/index.php");
$APPLICATION->SetTitle(GetMessage("SERVICES_TITLE"));
?>
<p><?$APPLICATION->IncludeComponent(
	"bitrix:intranet.reserve_meeting",
	".default",
	array(
		"IBLOCK_TYPE" => "events",
		"IBLOCK_ID" => "180",
		"USERGROUPS_MODIFY" => array(),
		"USERGROUPS_RESERVE" => array(),
		"USERGROUPS_CLEAR" => array(),
		"SEF_MODE" => "N",
		"SET_NAVCHAIN" => "Y",
		"SET_TITLE" => "Y",
		"WEEK_HOLIDAYS" => array(0=>"5",1=>"6",),
	),
	false
);
?></p>

<p><a href="/edu/services/res_c.php"><?=GetMessage("SERVICES_LINK")?></a><br /></p>
>>>>>>> e0a0eba79 (init)
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>