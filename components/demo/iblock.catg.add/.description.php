<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

$arComponentDescription = array(
	"NAME" => "Компонент добавления категории",
	"DESCRIPTION" => "Добавляет новостную категорию"
	, "PATH" => array("ID" => "demo", "CHILD" => array(
		"ID" => "my_news",
			"NAME" => "Мои компоненты",
			"CHILD" => array(
				"ID" => "my_catg",
			),
	)
));
?>