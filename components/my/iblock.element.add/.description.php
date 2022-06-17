<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => "Добавление и редактирование",
	"DESCRIPTION" => GetMessage("IBLOCK_ELEMENT_ADD_DESCRIPTION"),
	"ICON" => "/images/eadd.gif",
	"COMPLEX" => "Y",
	"PATH" => array(
		"ID" => "demo",
		"CHILD" => array(
			"ID" => "iblock_element_add",
			"NAME" => "Мои компоненты",
		),
	),
);
?>