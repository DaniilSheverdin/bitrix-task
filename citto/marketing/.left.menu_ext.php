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
		"/citto/marketing/",
		Array(),
		Array(),
		""
	);
}

if (\Bitrix\Sender\Security\Access::current()->canViewLetters())
{
	$aMenuLinks[] = Array(
		GetMessage('SERVICES_MENU_MARKETING_LETTERS'),
		"/citto/marketing/letter/",
		Array(),
		Array(),
		""
	);
}

if (\Bitrix\Sender\Security\Access::current()->canViewAds())
{
	$aMenuLinks[] = Array(
		GetMessage('SERVICES_MENU_MARKETING_ADS'),
		"/citto/marketing/ads/",
		Array(),
		Array(),
		""
	);
}

if (\Bitrix\Sender\Security\Access::current()->canViewSegments())
{
	$aMenuLinks[] = Array(
		GetMessage('SERVICES_MENU_MARKETING_SEGMENTS'),
		"/citto/marketing/segment/",
		Array(),
		Array(),
		""
	);
}

if (\Bitrix\Sender\Security\Access::current()->canViewRc())
{
	$aMenuLinks[] = Array(
		GetMessage('SERVICES_MENU_MARKETING_RETURN_CUSTOMER'),
		"/citto/marketing/rc/",
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
		"/citto/marketing/toloka/",
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
		"/citto/marketing/template/",
		Array(),
		Array(),
		""
	);
}

if (\Bitrix\Sender\Security\Access::current()->canViewBlacklist())
{
	$aMenuLinks[] = Array(
		GetMessage('SERVICES_MENU_MARKETING_BLACKLIST'),
		"/citto/marketing/blacklist/",
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
		"/citto/marketing/contact/",
		Array(),
		Array(),
		""
	);
}

if (\Bitrix\Sender\Security\Access::current()->canModifySettings())
{
	$aMenuLinks[] = Array(
		GetMessage('SERVICES_MENU_MARKETING_CONFIG'),
		"/citto/marketing/config.php",
		Array(),
		Array(),
		""
	);
}

if (\Bitrix\Sender\Security\Access::current()->canModifySettings())
{
	$aMenuLinks[] = Array(
		GetMessage('SERVICES_MENU_MARKETING_ROLE'),
		"/citto/marketing/config/role/",
		Array(),
		Array(),
		""
	);
}