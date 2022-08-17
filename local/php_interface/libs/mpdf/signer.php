<?
if(empty($argv[1]) || empty($argv[2]) || !file_exists($argv[1])){
	die(0);
}

include __DIR__."/autoload.php";

$MPDF_ARCH_FILE_ID  = isset($argv[3])?$argv[3]:NULL;
$source_file_path   = $argv[1];
$DESCRIPTION        = base64_decode($argv[2]);

if(!empty($argv[4])){
    $DESCRIPTION = ' <table border="0" width="250" style="text-align: center;page-break-inside:avoid">
    <tbody>
        <tr>
            <td style="border:1px solid #222;text-align: center;"><img width="240" src="'.$DOCUMENT_ROOT.'/upload/docsign.jpg" alt="ПОДПИСАНО ЭЛЕКТРОННОЙ ПОДПИСЬЮ"/></td>
        </tr>
        <tr>
            <td style="border:1px solid #222;background: #222;color: #fff;text-align: center;padding: 5px 5px;font-size: 10px;">СВЕДЕНИЯ О СЕРТИФИКАТЕ ЭП</td>
        </tr>
        <tr>
            <td style="border:1px solid #222;font-size: 10px;padding: 5px 5px;text-align: center;">'.nl2br(base64_decode($argv[2])).'</td>
        </tr>
    </tbody>
    </table>';
}

if($MPDF_ARCH_FILE_ID){
    $MPDF_ARCH_FILE_PATH = $MPDF_ARCH."/file_".$MPDF_ARCH_FILE_ID.".html";
    if(file_exists($MPDF_ARCH_FILE_PATH)){
        $mpdf = $getMpdf();

        $MPDF_ARCH_FILE_CONTENT = file_get_contents($MPDF_ARCH_FILE_PATH);

        if(strpos($MPDF_ARCH_FILE_CONTENT,"#PODPIS1#") !== false){
            $MPDF_ARCH_FILE_CONTENT = str_replace('#PODPIS1#', $DESCRIPTION, $MPDF_ARCH_FILE_CONTENT);;
        }elseif(strpos($MPDF_ARCH_FILE_CONTENT,"#PODPIS2#") !== false){
            $MPDF_ARCH_FILE_CONTENT = str_replace('#PODPIS2#', $DESCRIPTION, $MPDF_ARCH_FILE_CONTENT);;
        }elseif(strpos($MPDF_ARCH_FILE_CONTENT,"#PODPIS3#") !== false){
            $MPDF_ARCH_FILE_CONTENT = str_replace('#PODPIS3#', $DESCRIPTION, $MPDF_ARCH_FILE_CONTENT);;
        }else{
            $MPDF_ARCH_FILE_CONTENT = str_replace('</body>', $DESCRIPTION.'</body>', $MPDF_ARCH_FILE_CONTENT);;
        }
        
        file_put_contents($MPDF_ARCH_FILE_PATH, $MPDF_ARCH_FILE_CONTENT);

        $mpdf->WriteHTML(str_replace(['#PODPIS1#','#PODPIS2#','#PODPIS3#'],['','',''],$MPDF_ARCH_FILE_CONTENT));
        $mpdf->Output();
        die;
    }
}
if(!empty($argv[4])){
    $DESCRIPTION = '<table style="width:830px">
        <tr>
            <td>
                <table border="0" width="350">
                <tbody>
                    <tr>
                        <td style="border:1px solid #222;text-align: center;"><img width="350" src="'.$DOCUMENT_ROOT.'/upload/docsign.jpg" alt="ПОДПИСАНО ЭЛЕКТРОННОЙ ПОДПИСЬЮ"/></td>
                    </tr>
                    <tr>
                        <td style="border:1px solid #222;background: #222;color: #fff;text-align: center;padding: 5px 5px;font-size: 10px;">СВЕДЕНИЯ О СЕРТИФИКАТЕ ЭП</td>
                    </tr>
                    <tr>
                        <td style="border:1px solid #222;font-size: 10px;padding: 5px 5px;text-align: center;">'.nl2br(base64_decode($argv[2])).'</td>
                    </tr>
                </tbody>
                </table>
            </td>
            <td style="border-right:1px solid #f2f2f2;"></td>
        </tr>
        </table>';
}


$images = [];
$im = new Imagick();
$im->setResolution(400,400);
$im->readimage($source_file_path);
$im->setImageFormat('jpeg');
$im->setImageCompression(imagick::COMPRESSION_JPEG); 
$im->setImageCompressionQuality(100);
$num_pages = $im->getNumberImages();
for($i = 0;$i < $num_pages; $i++){
    $image_path = $MPDF_ARCH.'/signer_'.random_int(111111,999999).".jpg";
    $im->setIteratorIndex($i);
    $im->trimImage(0);

    $images[] = [
        'PATH'  => $image_path,
        'WIDTH' => $im->getImageWidth(),
        'HEIGHT'=> $im->getImageHeight(),
    ];
    $im->writeImage($image_path);
}
$im->clear(); 
$im->destroy();


$sign_path = $MPDF_ARCH.'/signer_'.random_int(111111,999999).".pdf";
$mpdf = new Mpdf\Mpdf();
$mpdf->WriteHTML($DESCRIPTION);
$mpdf->Output($sign_path,'F');

$sign_img_path = $MPDF_ARCH.'/signer_'.random_int(111111,999999).".jpg";
$im = new Imagick();
$im->setResolution(400,400);
$im->readimage($sign_path.'[0]');
$im->setImageFormat('jpeg');
$im->setImageCompression(imagick::COMPRESSION_JPEG); 
$im->setImageCompressionQuality(100);
$im->trimImage(0);
$images[] = [
    'PATH'  => $sign_img_path,
    'WIDTH' => $im->getImageWidth(),
    'HEIGHT'=> $im->getImageHeight(),
];
$im->writeImage($sign_img_path);


$mpdf = $getMpdf();
$mpdf->autoPageBreak = true;
$pdf_html = "";
foreach($images as $indx=>$image){
    $pdf_html .= '<div style="margin-bottom:50px"><img src="'.$image['PATH'].'"/></div>';
}

$mpdf->WriteHTML($pdf_html);
$mpdf->Output();

unlink($sign_path);
foreach($images as $image){
    unlink($image['PATH']);
}
die;
