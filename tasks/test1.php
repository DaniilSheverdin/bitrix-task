<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");?>
<?
	$client = new SoapClient("https://172.21.245.232/Webservice1.asmx?WSDL");
	$result = $client->get_rc();
	//print_r($result->get_rcResult);
	foreach($result->get_rcResult as $key => $value){
		foreach ($value as $key => $value1){
			if ($value1->DUE == "0.YQOB.YQOD."){
				$userid = 3;
			}
			else{
				$userid = 1;
			}
			$description="Номер документа: ".$value1->FREE_NUM."<br />Дата поступления: ".$value1->DOC_DATE."<br />Плановая дата исполнения: ".$value1->PLAN_DATE."<br />Отправитель: ".$value1->CORRESP;
			$description=htmlspecialchars($description);
			if (CModule::IncludeModule("tasks")){
				$res=CTasks::GetList(
					Array("TITLE"=>"ASC"),
					Array("ID"=>618)
				);
				while ($arTask=$res->GetNext()){
					echo '<pre>';print_r($arTask);echo '</pre>';
				}
			}
		}
	}
?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>


