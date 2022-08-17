<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arActivityDescription = array(
	"NAME" => GetMessage("CITTO_GA2_DESCR_NAME"),
	"DESCRIPTION" => GetMessage("CITTO_GA2_DESCR_DESCR"),
	"TYPE" => "activity",
	"CLASS" => "CITTOSocnetGroupActivity",
	"JSCLASS" => "BizProcActivity",
	"CATEGORY" => array(
		"ID" => "interaction",
	),
	"RETURN" => array(
		"GroupId" => array(
			"NAME" => GetMessage("CITTO_GA2_DESCR_TASKID"),
			"TYPE" => "int",
		)
		
	),
);
?>