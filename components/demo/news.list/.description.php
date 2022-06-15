<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => "Комплексный нововостной компонент",
	"DESCRIPTION" => "Выводит список новостей, осуществляет переход к детальной новости",
	"ICON" => "/images/news_all.gif",
	"COMPLEX" => "Y",
	"PATH" => array(
		"ID" => "demo",
		"CHILD" => array(
			"ID" => "my_news",
			"NAME" => "Мои компоненты",
			"CHILD" => array(
				"ID" => "my_news_cmpx",
			),
		),
	),
);
?>