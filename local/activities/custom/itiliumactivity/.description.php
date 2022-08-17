<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die();

$arActivityDescription = array(
	'NAME' => GetMessage('BPITILIUM_DESCR_NAME'),
	'DESCRIPTION' => GetMessage('BPITILIUM_DESCR_DESCR'),
	'TYPE' => array('activity'),
	'CLASS' => 'ItiliumActivity',
	'JSCLASS' => 'BizProcActivity',
	'CATEGORY' => array(
		'ID' => 'interaction',
	),
	'RETURN' => array(
		'SyncId' => array(
			'NAME' => 'ID синхронизации',
			'TYPE' => 'int',
		),
		'IncidentGuid' => array(
			'NAME' => 'GUID Инцидента',
			'TYPE' => 'int',
		),
		'TaskGuid' => array(
			'NAME' => 'GUID Задачи',
			'TYPE' => 'string',
		),
		'Solution' => array(
			'NAME' => 'Описание решения задачи',
			'TYPE' => 'string',
		),
		'SolutionUID' => array(
			'NAME' => 'Код решения задачи',
			'TYPE' => 'string',
		),
	),
);