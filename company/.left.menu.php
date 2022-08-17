<?
$aMenuLinks = Array(
	Array(
		"Поиск сотрудника", 
		"/company/index.php", 
		Array(), 
		Array(), 
		"" 
	),
	Array(
		"Телефонный справочник", 
		"/company/telephones.php", 
		Array(), 
		Array(), 
		"" 
	),
	Array(
		"Структура компании", 
		"/company/vis_structure.php", 
		Array(), 
		Array(), 
		"" 
	),
	Array(
		"Кадровые изменения", 
		"/company/events.php", 
		Array(), 
		Array(), 
		"CBXFeatures::IsFeatureEnabled('StaffChanges')" 
	),
	Array(
		"Эффективность", 
		"/company/report.php", 
		Array(), 
		Array(), 
		"IsModuleInstalled('tasks')" 
	),
	Array(
		"Дни рождения", 
		"/company/birthdays.php", 
		Array(), 
		Array(), 
		"" 
	),
	Array(
		"Фотогалерея", 
		"/company/gallery/", 
		Array(), 
		Array(), 
		"CBXFeatures::IsFeatureEnabled('Gallery')" 
	)
);
?>