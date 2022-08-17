<?php
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');
$APPLICATION->SetTitle('Показатели');
$arFiles = json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/citto/indicators_new/webpack-assets.json'), true);
$arJs = [];
$arCss = [];
foreach ($arFiles as $row) {
	if (is_array($row['js'])) {
		$arJs = array_merge($arJs, $row['js']);
	} else {
		$arJs[] = $row['js'];
	}
	if (is_array($row['css'])) {
		$arCss = array_merge($arCss, $row['css']);
	} else {
		$arCss[] = $row['css'];
	}
}
$arJs = array_filter($arJs);
$arCss = array_filter($arCss);
rsort($arCss);
rsort($arJs);
?>
<!DOCTYPE html>
	<html lang="ru">
	<head>
		<script type="text/javascript">
			var apiUrl = '/local/api/indicators';
			var apiToken = '<?=md5($GLOBALS['USER']->GetID());?>';
		</script>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width,initial-scale=1">
		<title>Показатели</title>
		<? foreach ($arCss as $link) : ?>
		<link href="<?=$link?>?_=<?=filemtime($_SERVER['DOCUMENT_ROOT'] . $link)?>" rel="stylesheet">
		<? endforeach; ?>
	</head>
	<body>
		<noscript><strong>We're sorry but indicators-vue doesn't work properly without JavaScript enabled. Please enable it to continue.</strong></noscript>
		<div id="app"></div>
		<? foreach ($arJs as $link) : ?>
		<script src="<?=$link?>?_=<?=filemtime($_SERVER['DOCUMENT_ROOT'] . $link)?>"></script>
		<? endforeach; ?>
	</body>
</html>