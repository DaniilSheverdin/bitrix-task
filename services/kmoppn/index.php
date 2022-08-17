<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");?>
<? /**
 * @global $APPLICATION
 */
$APPLICATION->SetTitle('Критерии подготовки к переписи населения для муниципальных образований');
?>

<?$APPLICATION->IncludeComponent("citto:kmoppn", 'kform',
	[],
	false
);?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>