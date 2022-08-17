<?

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

global $USER, $DB;
if (!is_object($USER)) {
    $USER = new CUser();
}

$res = (object)[
    'code'      => "ERROR",
    'message'   => "Ошибка. Попробуйте позже",
];
try {
    if (!$USER->IsAuthorized()) {
        throw new Exception("Вы не авторизованы");
    }
    \Bitrix\Main\Loader::includeModule("citto.integration");

    $FILE_ID                = isset($_POST['id'])?intVal($_POST['id']):null;
    $FILE_DATA              = $_POST['file'] ?? null;
    $FILE_DESCRIPTION       = "";
    $file_sig__external_id  = ("SIGN_".$USER->GetID()."_".$FILE_ID);

    if (empty($FILE_ID)) {
        throw new Exception("Файл не передан");
    }
    if (empty($FILE_DATA)) {
        throw new Exception("Файл не передан");
    }

    $file_original = CFile::GetFileArray($FILE_ID);
    if (!$file_original) {
        throw new Exception("Файл не найден");
    }

    if ($DB->Query('SELECT ID FROM b_file WHERE EXTERNAL_ID="'.$DB->ForSql($file_sig__external_id).'"')->Fetch()) {
        throw new Exception("Файл уже подписан");
    }

    $signs = \Citto\Integration\Module::decodeSig($FILE_DATA);

    foreach ($signs as $sign) {
        if (empty($sign['signingTime'])) {
            $sign['signingTime'] = date('d.m.Y H:i:s');
        }

        $FILE_DESCRIPTION .= 'Дата: '.$sign['signingTime'].PHP_EOL;

        if (!empty($sign['cert']['subjectName'])) {
            $subjectName = array_unique(
                array_diff_key(
                    $sign['cert']['subjectName'],
                    [
                        'C'=>"C",
                        'L'=>"L",
                        'S'=>"S",
                        'OGRN'=>"OGRN",
                        'INN'=>"INN",
                        'SNILS'=>"SNILS",
                        'STREET'=>"STREET"
                    ]
                )
            );
            $FILE_DESCRIPTION .= "Владелец: ".implode(", ", $subjectName).PHP_EOL;
        }

        if (!empty($sign['cert']['validFromDate'])) {
            $FILE_DESCRIPTION .= "Действителен: с ".$sign['cert']['validFromDate'];
            if (!empty($sign['cert']['validToDate'])) {
                $FILE_DESCRIPTION .= " по ".$sign['cert']['validToDate'];
            }
            $FILE_DESCRIPTION .= PHP_EOL;
        }
    }

    if (empty($_POST['file'])) {
        throw new Exception("Ошибка загрузки файла");
    }

    $tmpfname = tempnam("/tmp", "signed_file");
    file_put_contents($tmpfname, $FILE_DATA);
    $uploaded_file = [
        'name'          => str_replace(".sig", "", $file_original['ORIGINAL_NAME'])."-".$USER->GetFullName().".sig",
        'type'          => "application/x-pkcs7-certreqresp",
        'tmp_name'      => $tmpfname,
        'error'         => 0,
        'size'          => filesize($tmpfname),
        'MODULE_ID'     => "bizproc",
        'external_id'   => $file_sig__external_id,
        'description'   => $FILE_DESCRIPTION
    ];

    $uploaded_file_id = (int)CFile::SaveFile($uploaded_file, "signed_files", true);
    if ($uploaded_file_id <= 0) {
        throw new Exception("Ошибка загрузки файла".$uploaded_file['name']);
    }

    $DB->Query('INSERT INTO b_file_info(FILE_ID, DESCRIPTION) VALUES('.$DB->ForSql($uploaded_file_id).',"'.$DB->ForSql($FILE_DESCRIPTION).'")');

    \Bitrix\Main\Loader::includeModule('citto.filesigner');
    $file = current(\Citto\Filesigner\Signer::getFiles([$FILE_ID]));
    if (!$file['SIGNED']) {
        \Citto\Filesigner\Signer::setSign($FILE_ID, $tmpfname, "", ['#PODPIS1#', '#PODPIS2#']);
    }
    unlink($tmpfname);
    $res->code = "OK";
} catch (Exception $exc) {
  $res->message = $exc->getMessage();
}

header('Content-Type: application/json');
echo json_encode($res);
