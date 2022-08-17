<?
$aMenuLinks = Array(
	Array(
		"Отправить заявку", 
		"/mfc/services/requests/index.php", 
		Array("/mfc/services/requests/form.php"), 
		Array(), 
		"" 
	),
	Array(
		"Мои заявки", 
		"/mfc/services/requests/my.php", 
		Array("/mfc/services/requests/form_list.php", "/mfc/services/requests/form_view.php", "/mfc/services/requests/form_edit.php"), 
		Array(), 
		"" 
	),
	Array(
		"Курьерская доставка", 
		"/mfc/services/requests/form_list.php?WEB_FORM_ID=436", 
		Array(), 
		Array(), 
		"CSite::InGroup([123])" 
	),
	Array(
		"Канц. товары и расходные материалы", 
		"/mfc/services/requests/form_list.php?WEB_FORM_ID=435", 
		Array(), 
		Array(), 
		"CSite::InGroup([124])" 
	),
	Array(
		"Основные средства", 
		"/mfc/services/requests/form_list.php?WEB_FORM_ID=443", 
		Array(), 
		Array(), 
		"CSite::InGroup([124])" 
	),
	Array(
		"Заказ картриджей для принтеров и МФУ", 
		"/mfc/services/requests/form_list.php?WEB_FORM_ID=444", 
		Array(), 
		Array(), 
		"CSite::InGroup([124])" 
	),
	Array(
		"Хозяйственная служба", 
		"/mfc/services/requests/form_list.php?WEB_FORM_ID=432", 
		Array(), 
		Array(), 
		"CSite::InGroup([125])" 
	),
	Array(
		"Получение экспертной помощи", 
		"/mfc/services/requests/form_list.php?WEB_FORM_ID=445", 
		Array(), 
		Array(), 
		"CSite::InGroup([132])" 
	),
//	Array(
//		"Служба информационной безопасности",
//		"/mfc/services/requests/form_list.php?WEB_FORM_ID=446",
//		Array(),
//		Array(),
//		"CSite::InGroup([133])"
//	),
	Array(
		"Изменения услуг в АИС МФЦ", 
		"/mfc/services/requests/form_list.php?WEB_FORM_ID=447", 
		Array(), 
		Array(), 
		"CSite::InGroup([134])" 
	)
);
?>