<<<<<<< HEAD
<?php

if (isset($_REQUEST['DEBUG_125dsf4312'])) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
}
define('TMP', $_SERVER['DOCUMENT_ROOT']."/../newcorp_arch/mpdf_arch");
spl_autoload_register(function ($name) {
    if (strpos($name, "FpdiPdfParser") !== false) {
        $name = str_replace("FpdiPdfParser", "Fpdi", $name);
    }
    include $_SERVER['DOCUMENT_ROOT']."/local/php_interface/libs/mpdf/".preg_replace("/^([^\/]+)/i", "$1/src", str_replace('\\', '/', $name)).".php";
});
$tempnam = function ($ext) {
    $path = TMP.'/'.uniqid('temp_', true).".".$ext;
    $GLOBALS['tempnam_files'][] = $path;
    return $path;
};
register_shutdown_function(function () {
    if (empty($GLOBALS['tempnam_files'])) {
        return;
    }
    foreach ($GLOBALS['tempnam_files'] as $file) {
        if (file_exists($file) && strpos($file, TMP) === 0) {
            unlink($file);
        }
    }
});
$cropImage = function ($src) use (&$tempnam) {
    $result = $tempnam('jpg');
    $img    = imagecreatefromjpeg($src);
    $b_top  = 0;
    $b_btm  = 0;
    $b_lft  = 0;

    for (; $b_top < imagesy($img); ++$b_top) {
        for ($x = 0; $x < imagesx($img); ++$x) {
            if (imagecolorat($img, $x, $b_top) != 0xFFFFFF) {
                break 2;
            }
        }
    }
    for (; $b_btm < imagesy($img); ++$b_btm) {
        for ($x = 0; $x < imagesx($img); ++$x) {
            if (imagecolorat($img, $x, imagesy($img) - $b_btm-1) != 0xFFFFFF) {
                break 2;
            }
        }
    }
    for (; $b_lft < imagesx($img); ++$b_lft) {
        for ($y = 0; $y < imagesy($img); ++$y) {
            if (imagecolorat($img, $b_lft, $y) != 0xFFFFFF) {
                break 2;
            }
        }
    }

    $newimg = imagecreatetruecolor(imagesx($img)-($b_lft), imagesy($img)-($b_top+$b_btm));
    imagecopy($newimg, $img, 0, 0, $b_lft, $b_top, imagesx($newimg), imagesy($newimg));
    imagejpeg($newimg, $result);
    return $result;
};

try {
    if (empty($_SERVER['DOCUMENT_ROOT'])) {
        throw new Exception("No DOCUMENT_ROOT");
    }

    /**
     * @param string $html будет использован для создания(добавления в) pdf
     * @param string $src(optional) исходный pdf в который добавляется информация. При отсутствии будет создан чистый pdf
     * @param array $mpdf_params(optional) параметры mpdf
     * @return string путь до pdf
     * @throws  Exception
     */
    $result_pdf = (function (string $html, ?string $src, ?array $mpdf_params) use ($tempnam, &$cropImage) {
        $result_pdf = $tempnam('pdf');
        $result_html= $html;
        $mpdfParams = array_merge([
            'format'        => 'A4',
            'margin_left'   => 30,
            'margin_right'  => 10,
            'margin_top'    => 5,
            'margin_bottom' => 5,
            'orientation'   => "P",
            'default_font' => 'ptastraserif',
            'format'        => [170,240],
            // 'debug'  => true,
            'fontdata'      => [
                'ptastraserif' => [
                    'R' => 'PTAstraSerif-Regular.ttf',
                    'I' => 'PTAstraSerif-Italic.ttf',
                    'B' => 'PTAstraSerif-Bold.ttf',
                    'BI' => 'PTAstraSerif-BoldItalic.ttf',
                ]
            ],
        ], $mpdf_params?:[]);

        $mpdf   = new Mpdf\Mpdf($mpdfParams);
        $mpdf->shrink_tables_to_fit = 0;

        if ($src) {
            if (!file_exists($src)) {
                throw new Exception("Исходный файл не найден");
            }

            if (file_exists($src)) {
                $obHandle = fopen($src, "r");
                $sBuffer = fgets($obHandle, 4096);
                fclose($obHandle);

                if (mb_strpos($sBuffer, '1.5') !== false) {
                    $sTmpPdf = "{$src}_tmp";
                    $sCmd = "gs -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -dNOPAUSE -dQUIET -dBATCH -sOutputFile='$sTmpPdf' '$src'";
                    shell_exec($sCmd);
                    if ($sContent = file_get_contents($sTmpPdf)) {
                        file_put_contents($src, $sContent);
                    }
                }
            }

            $srcPageCount = $mpdf->SetSourceFile($src);
            for ($srcpageNo = 1; $srcpageNo <= ($srcPageCount > 1? $srcPageCount - 1: $srcPageCount); $srcpageNo++) {
                $tplId  = $mpdf->ImportPage($srcpageNo);
                $size   = $mpdf->GetTemplateSize($tplId);

                $mpdf->AddPageByArray([
                    'orientation'   => $size['orientation'],
                    'newformat'     => [$size['width'], $size['height']]
                ]);

                $mpdf->UseTemplate($tplId, 0, 0, $size['width'], $size['height']);
            }

            $mpdf->AddPageByArray([
                'orientation' => $mpdfParams['orientation']
            ]);
            if ($srcPageCount > 1) {
                $src_last_page = $tempnam('jpg');
                $im = new Imagick();
                $im->setResolution(400,400);
                $im->readimage($src.'['.($srcPageCount-1).']');
                $im->setImageFormat('jpeg');
                $im->setImageCompression(imagick::COMPRESSION_JPEG);
                $im->setImageCompressionQuality(100);
                $im->writeImage($src_last_page);
                $im->clear();
        
                $mpdf->WriteHTML('<img style="width:820px;height:auto" src="'.$cropImage($src_last_page).'"><br/><br/>');
            }
        }
        
        $mpdf->WriteHTML($result_html);
        $mpdf->Output($result_pdf, Mpdf\Output\Destination::FILE);

        if (!file_exists($result_pdf) || !is_readable($result_pdf) || filesize($result_pdf) < 1000) {
            throw new Exception("Не удалось создать файл");
        }
        return $result_pdf;
    })(
        $_REQUEST['html'] ?? null,
        $_REQUEST['src'] ?? null,
        $_REQUEST['mpdf_params'] ?? null
    );
    
    header('Cache-Control: public');
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="file.pdf"');
    header('Content-Length: '.filesize($result_pdf));

    readfile($result_pdf);
} catch (Exception | TypeError $exc) {
    http_response_code(500);
    file_put_contents(
        $_SERVER['DOCUMENT_ROOT'] . '/../newcorp_arch/pdf_gen.log',
        str_repeat('=', 25) . PHP_EOL .
        date('d.m.Y H:i:s') . PHP_EOL .
        'src = ' . print_r($_REQUEST['src'], true) . PHP_EOL .
        'mpdf_params = ' . print_r($_REQUEST['mpdf_params'], true) . PHP_EOL .
        'EXCEPTION = ' . print_r($exc->getMessage(), true) . PHP_EOL,
        FILE_APPEND
    );
    echo $exc->getMessage();
}
die;
=======
<?php

if (isset($_REQUEST['DEBUG_125dsf4312'])) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
}
define('TMP', $_SERVER['DOCUMENT_ROOT']."/../newcorp_arch/mpdf_arch");
spl_autoload_register(function ($name) {
    if (strpos($name, "FpdiPdfParser") !== false) {
        $name = str_replace("FpdiPdfParser", "Fpdi", $name);
    }
    include $_SERVER['DOCUMENT_ROOT']."/local/php_interface/libs/mpdf/".preg_replace("/^([^\/]+)/i", "$1/src", str_replace('\\', '/', $name)).".php";
});
$tempnam = function ($ext) {
    $path = TMP.'/'.uniqid('temp_', true).".".$ext;
    $GLOBALS['tempnam_files'][] = $path;
    return $path;
};
register_shutdown_function(function () {
    if (empty($GLOBALS['tempnam_files'])) {
        return;
    }
    foreach ($GLOBALS['tempnam_files'] as $file) {
        if (file_exists($file) && strpos($file, TMP) === 0) {
            unlink($file);
        }
    }
});
$cropImage = function ($src) use (&$tempnam) {
    $result = $tempnam('jpg');
    $img    = imagecreatefromjpeg($src);
    $b_top  = 0;
    $b_btm  = 0;
    $b_lft  = 0;

    for (; $b_top < imagesy($img); ++$b_top) {
        for ($x = 0; $x < imagesx($img); ++$x) {
            if (imagecolorat($img, $x, $b_top) != 0xFFFFFF) {
                break 2;
            }
        }
    }
    for (; $b_btm < imagesy($img); ++$b_btm) {
        for ($x = 0; $x < imagesx($img); ++$x) {
            if (imagecolorat($img, $x, imagesy($img) - $b_btm-1) != 0xFFFFFF) {
                break 2;
            }
        }
    }
    for (; $b_lft < imagesx($img); ++$b_lft) {
        for ($y = 0; $y < imagesy($img); ++$y) {
            if (imagecolorat($img, $b_lft, $y) != 0xFFFFFF) {
                break 2;
            }
        }
    }

    $newimg = imagecreatetruecolor(imagesx($img)-($b_lft), imagesy($img)-($b_top+$b_btm));
    imagecopy($newimg, $img, 0, 0, $b_lft, $b_top, imagesx($newimg), imagesy($newimg));
    imagejpeg($newimg, $result);
    return $result;
};

try {
    if (empty($_SERVER['DOCUMENT_ROOT'])) {
        throw new Exception("No DOCUMENT_ROOT");
    }

    /**
     * @param string $html будет использован для создания(добавления в) pdf
     * @param string $src(optional) исходный pdf в который добавляется информация. При отсутствии будет создан чистый pdf
     * @param array $mpdf_params(optional) параметры mpdf
     * @return string путь до pdf
     * @throws  Exception
     */
    $result_pdf = (function (string $html, ?string $src, ?array $mpdf_params) use ($tempnam, &$cropImage) {
        $result_pdf = $tempnam('pdf');
        $result_html= $html;
        $mpdfParams = array_merge([
            'format'        => 'A4',
            'margin_left'   => 30,
            'margin_right'  => 10,
            'margin_top'    => 5,
            'margin_bottom' => 5,
            'orientation'   => "P",
            'default_font' => 'ptastraserif',
            'format'        => [170,240],
            // 'debug'  => true,
            'fontdata'      => [
                'ptastraserif' => [
                    'R' => 'PTAstraSerif-Regular.ttf',
                    'I' => 'PTAstraSerif-Italic.ttf',
                    'B' => 'PTAstraSerif-Bold.ttf',
                    'BI' => 'PTAstraSerif-BoldItalic.ttf',
                ]
            ],
        ], $mpdf_params?:[]);

        $mpdf   = new Mpdf\Mpdf($mpdfParams);
        $mpdf->shrink_tables_to_fit = 0;

        if ($src) {
            if (!file_exists($src)) {
                throw new Exception("Исходный файл не найден");
            }

            if (file_exists($src)) {
                $obHandle = fopen($src, "r");
                $sBuffer = fgets($obHandle, 4096);
                fclose($obHandle);

                if (mb_strpos($sBuffer, '1.5') !== false) {
                    $sTmpPdf = "{$src}_tmp";
                    $sCmd = "gs -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -dNOPAUSE -dQUIET -dBATCH -sOutputFile='$sTmpPdf' '$src'";
                    shell_exec($sCmd);
                    if ($sContent = file_get_contents($sTmpPdf)) {
                        file_put_contents($src, $sContent);
                    }
                }
            }

            $srcPageCount = $mpdf->SetSourceFile($src);
            for ($srcpageNo = 1; $srcpageNo <= ($srcPageCount > 1? $srcPageCount - 1: $srcPageCount); $srcpageNo++) {
                $tplId  = $mpdf->ImportPage($srcpageNo);
                $size   = $mpdf->GetTemplateSize($tplId);

                $mpdf->AddPageByArray([
                    'orientation'   => $size['orientation'],
                    'newformat'     => [$size['width'], $size['height']]
                ]);

                $mpdf->UseTemplate($tplId, 0, 0, $size['width'], $size['height']);
            }

            $mpdf->AddPageByArray([
                'orientation' => $mpdfParams['orientation']
            ]);
            if ($srcPageCount > 1) {
                $src_last_page = $tempnam('jpg');
                $im = new Imagick();
                $im->setResolution(400,400);
                $im->readimage($src.'['.($srcPageCount-1).']');
                $im->setImageFormat('jpeg');
                $im->setImageCompression(imagick::COMPRESSION_JPEG);
                $im->setImageCompressionQuality(100);
                $im->writeImage($src_last_page);
                $im->clear();
        
                $mpdf->WriteHTML('<img style="width:820px;height:auto" src="'.$cropImage($src_last_page).'"><br/><br/>');
            }
        }
        
        $mpdf->WriteHTML($result_html);
        $mpdf->Output($result_pdf, Mpdf\Output\Destination::FILE);

        if (!file_exists($result_pdf) || !is_readable($result_pdf) || filesize($result_pdf) < 1000) {
            throw new Exception("Не удалось создать файл");
        }
        return $result_pdf;
    })(
        $_REQUEST['html'] ?? null,
        $_REQUEST['src'] ?? null,
        $_REQUEST['mpdf_params'] ?? null
    );
    
    header('Cache-Control: public');
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="file.pdf"');
    header('Content-Length: '.filesize($result_pdf));

    readfile($result_pdf);
} catch (Exception | TypeError $exc) {
    http_response_code(500);
    file_put_contents(
        $_SERVER['DOCUMENT_ROOT'] . '/../newcorp_arch/pdf_gen.log',
        str_repeat('=', 25) . PHP_EOL .
        date('d.m.Y H:i:s') . PHP_EOL .
        'src = ' . print_r($_REQUEST['src'], true) . PHP_EOL .
        'mpdf_params = ' . print_r($_REQUEST['mpdf_params'], true) . PHP_EOL .
        'EXCEPTION = ' . print_r($exc->getMessage(), true) . PHP_EOL,
        FILE_APPEND
    );
    echo $exc->getMessage();
}
die;
>>>>>>> e0a0eba79 (init)
