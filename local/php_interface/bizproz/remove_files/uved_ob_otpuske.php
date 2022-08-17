<?
if(php_sapi_name() !== 'cli') die;
$date_to = new DateTime("-5 Years");
foreach (glob(__DIR__."/../../../../../newcorp_arch/uved_ob_otpuske/*") as $filename) {
	$basename = basename($filename);
	if(!preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/",$basename)) continue;

	$file_date = new DateTime($basename);
	if($file_date < $date_to){
		shell_exec("rm -rf ".realpath($filename));
	}
}
