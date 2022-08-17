<<<<<<< HEAD
<?
if(php_sapi_name() !== 'cli') die;
$date_to = new DateTime("-2 Years");
foreach (glob(__DIR__."/../../../../../newcorp_arch/zayavka_na_vydachu_ep/*") as $filename) {
	$basename = basename($filename);
	if(!preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/",$basename)) continue;

	$file_date = new DateTime($basename);
	if($file_date < $date_to){
		shell_exec("rm -rf ".realpath($filename));
	}
}
=======
<?
if(php_sapi_name() !== 'cli') die;
$date_to = new DateTime("-2 Years");
foreach (glob(__DIR__."/../../../../../newcorp_arch/zayavka_na_vydachu_ep/*") as $filename) {
	$basename = basename($filename);
	if(!preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/",$basename)) continue;

	$file_date = new DateTime($basename);
	if($file_date < $date_to){
		shell_exec("rm -rf ".realpath($filename));
	}
}
>>>>>>> e0a0eba79 (init)
