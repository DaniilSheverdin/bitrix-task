<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arActivityDescription = array(
	"NAME" => GetMessage("BPAR_DESCR_NAME"),
	"DESCRIPTION" => GetMessage("BPAR_DESCR_DESCR"),
	"TYPE" => "activity",
	"CLASS" => "DocsignActivity",
	"JSCLASS" => "BizProcActivity",
	"CATEGORY" => array(
		"ID" => "document",
		'OWN_ID' => 'task',
		'OWN_NAME' => GetMessage('BPAR_DESCR_TASKS')
	),
	"RETURN" => array(
		'TaskId' => [
			'NAME' => 'ID',
			'TYPE' => 'int'
		],
		"Comments" => array(
			"NAME" => "BPAA_DESCR_CM",
			"TYPE" => "string",
		),
		"IsTimeout" => array(
			"NAME" => ("BPAA_DESCR_TA1"),
			"TYPE" => "int",
		),
		"InfoUser" => array(
			"NAME" => ("BPAA_DESCR_LU"),
			"TYPE" => "user",
		),
		"Changes" => array(
			"NAME" => ("BPAA_DESCR_CHANGES"),
			"TYPE" => "string",
		),
	),
);