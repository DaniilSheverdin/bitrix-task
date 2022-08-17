<?

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

global $USER;
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

    if (empty($_POST['id'])) {
        throw new Exception("Файл не передан");
    }

    $file_original = CFile::GetFileArray($_POST['id']);
    if (!$file_original) {
        throw new Exception("Файл не найден");
    }

    $file_sig__external_id = ("SIGN_".$GLOBALS['USER']->GetID()."_".$file_original['ID']);

    if ($GLOBALS['DB']->Query('SELECT ID FROM b_file WHERE EXTERNAL_ID="'.$GLOBALS['DB']->ForSql($file_sig__external_id).'"')->Fetch()) {
        throw new Exception("Файл уже подписан");
    }

    if (empty($_POST['file'])) {
        throw new Exception("Ошибка загрузки файла");
    }

    $tmpfname = tempnam("/tmp", "signed_file");
    file_put_contents($tmpfname, $_POST['file']);
    $uploaded_file = [
        'name'          => str_replace(".sig", "", $file_original['ORIGINAL_NAME'])."-".$USER->GetFullName().".sig",
        'type'          => "application/x-pkcs7-certreqresp",
        'tmp_name'      => $tmpfname,
        'error'         => 0,
        'size'          => filesize($tmpfname),
        'MODULE_ID'     => "bizproc",
        'external_id'   => $file_sig__external_id,
        'description'   => $source_file
    ];

    $uploaded_file_id = (int)CFile::SaveFile($uploaded_file, "signed_files", true);
    if ($uploaded_file_id<=0) {
        throw new Exception("Ошибка загрузки файла".$uploaded_file['name']);
    }
    unlink($tmpfname);

    $res->code = "OK";
} catch (Exception $exc) {
  $res->message = $exc->getMessage();
}

header('Content-Type: application/json');
echo json_encode($res);
