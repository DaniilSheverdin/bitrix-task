<?
if(php_sapi_name() !== 'cli') die;
spl_autoload_register(function($name){
	$src = __DIR__."/".preg_replace("/^([^\/]+)/i","$1/src", str_replace('\\','/',$name)).".php";
	include $src;
});
$orientation = 'P';
if(!empty($argv[2])){
	$orientation_arr=explode('=',$argv[2]);
	if($orientation_arr[0]=='orientation'){
		$orientation='L';
		unset($argv[2]);
	}
}
$DOCUMENT_ROOT	= realpath(__DIR__."/../../../../");
$MPDF_ARCH		= ($DOCUMENT_ROOT."/../newcorp_arch/mpdf_arch");
$getMpdf		= function() use($orientation){
	$mpdf = new Mpdf\Mpdf([
		'format'		=> 'A4',
		'margin_left'	=> 30,
		'margin_right'	=> 10,
		'margin_top'	=> 5,
		'margin_bottom'	=> 5,
		'orientation'	=> $orientation,
		'fontdata' => [
			'ptastraserif' => [
				'R' => 'PTAstraSerif-Regular.ttf',
				'I' => 'PTAstraSerif-Italic.ttf',
				'B' => 'PTAstraSerif-Bold.ttf',
				'BI' => 'PTAstraSerif-BoldItalic.ttf',
			]
		],
		'default_font' => 'ptastraserif',
		// 'debug'	=> true,
		'format'=>[170,240]
	]);
	$mpdf->shrink_tables_to_fit=0;
	return $mpdf;
};

if(!file_exists($MPDF_ARCH)){
    mkdir($MPDF_ARCH);
}

$oldmtime = strtotime("-1 year");
foreach(glob($MPDF_ARCH."/file_*") as $file){
  if(!is_file($file)) continue;
  if(filemtime($file) > $oldmtime) continue;
  unlink($file);
}