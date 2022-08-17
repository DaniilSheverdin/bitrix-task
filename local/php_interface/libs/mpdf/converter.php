<?
if(empty($argv[1]) || !file_exists($argv[1])){
	die(0);
}

include __DIR__."/autoload.php";

$mpdf = $getMpdf();
$mpdf->WriteHTML(str_replace(['#PODPIS1#','#PODPIS2#','#PODPIS3#'],['','',''],file_get_contents($argv[1])));
echo $mpdf->Output();
die;
