<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/intranet/public_bitrix24/marketing/.left.menu.php");

$aMenuLinks = Array();

if (!\Bitrix\Main\Loader::includeModule('sender'))
{
	return;
}

if (\Bitrix\Sender\Security\Access::current()->canViewStart())
{
	$aMenuLinks[] = Array(
		GetMessage('SERVICES_MENU_MARKETING_START'),
		"/czn/marketing/",
		Array(),
		Array(),
		""
	);
}

if (\Bitrix\Sender\Security\Access::current()->canViewLetters())
{
	$aMenuLinks[] = Array(
		GetMessage('SERVICES_MENU_MARKETING_LETTERS'),
		"/czn/marketing/letter/",
		Array(),
		Array(),
		""
	);
}

if (\Bitrix\Sender\Security\Access::current()->canViewAds())
{
	$aMenuLinks[] = Array(
		GetMessage('SERVICES_MENU_MARKETING_ADS'),
		"/czn/marketing/ads/",
		Array(),
		Array(),
		""
	);
}

if (\Bitrix\Sender\Security\Access::current()->canViewSegments())
{
	$aMenuLinks[] = Array(
		GetMessage('SERVICES_MENU_MARKETING_SEGMENTS'),
		"/czn/marketing/segment/",
		Array(),
		Array(),
		""
	);
}

if (\Bitrix\Sender\Security\Access::current()->canViewRc())
{
	$aMenuLinks[] = Array(
		GetMessage('SERVICES_MENU_MARKETING_RETURN_CUSTOMER'),
		"/czn/marketing/rc/",
		Array(),
		Array(),
		""
	);
}
if (
	method_exists(\Bitrix\Sender\Security\Access::current(), 'canViewToloka')
	&& \Bitrix\Sender\Security\Access::current()->canViewToloka()
)
{
	$aMenuLinks[] = Array(
		GetMessage('SERVICES_MENU_MARKETING_YANDEX_TOLOKA'),
		"/czn/marketing/toloka/",
		Array(),
		Array(),
		""
	);
}

$canViewTemplates = method_exists(
	\Bitrix\Sender\Security\Access::class,
	'canViewTemplates') ?
	\Bitrix\Sender\Security\Access::current()->canViewTemplates() :
	\Bitrix\Sender\Security\Access::current()->canViewLetters();


if ($canViewTemplates)
{
	$aMenuLinks[] = Array(
		GetMessage('SERVICES_MENU_MARKETING_TEMPLATES'),
		"/czn/marketing/template/",
		Array(),
		Array(),
		""
	);
}

if (\Bitrix\Sender\Security\Access::current()->canViewBlacklist())
{
	$aMenuLinks[] = Array(
		GetMessage('SERVICES_MENU_MARKETING_BLACKLIST'),
		"/czn/marketing/blacklist/",
		Array(),
		Array(),
		""
	);
}

$canViewClientList = method_exists(
	\Bitrix\Sender\Security\Access::class,
	'canViewClientList') ?
	\Bitrix\Sender\Security\Access::current()->canViewClientList() :
	\Bitrix\Sender\Security\Access::current()->canViewSegments();

if ($canViewClientList)
{
	$aMenuLinks[] = Array(
		GetMessage('SERVICES_MENU_MARKETING_CONTACT'),
		"/czn/marketing/contact/",
		Array(),
		Array(),
		""
	);
}

if (\Bitrix\Sender\Security\Access::current()->canModifySettings())
{
	$aMenuLinks[] = Array(
		GetMessage('SERVICES_MENU_MARKETING_CONFIG'),
		"/czn/marketing/config.php",
		Array(),
		Array(),
		""
	);
}

if (\Bitrix\Sender\Security\Access::current()->canModifySettings())
{
	$aMenuLinks[] = Array(
		GetMessage('SERVICES_MENU_MARKETING_ROLE'),
		"/czn/marketing/config/role/",
		Array(),
		Array(),
		""
	);
}