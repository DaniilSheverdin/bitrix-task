<?php

define('NEED_AUTH', true);

require_once $_SERVER['DOCUMENT_ROOT'] .
    '/bitrix/modules/main/include/prolog_before.php';
require_once $_SERVER['DOCUMENT_ROOT'] .
    '/local/vendor/autoload.php';

use Bitrix\Main\Loader;

Loader::includeModule('sprint.migration');
Loader::includeModule("iblock");
Loader::includeModule("citto.filesigner");

use Citto\Filesigner\Signer;

$FILE_ID = $_REQUEST['fileid'] ?? null;
$REQUEST_CONTENT = $_REQUEST['reqConent'] ?? null;
$intIBLOCK_ID = $_REQUEST['iblock_id'] ?? null;

$resp->status = 0;

if ($FILE_ID) {

    $USER_ID = $USER->GetID();

    $files = \CFile::GetList(
        [],
        [
            'EXTERNAL_ID' => 'FSIGNER_' . $FILE_ID . '_' . $USER_ID
        ]
    );

    $fileData = $files->Fetch();

    if (is_array($fileData)) {
        $filePath = getenv("DOCUMENT_ROOT") . '/upload/' . $fileData['SUBDIR'] . '/' . $fileData['FILE_NAME'];

        if (file_exists($filePath)) {
            $sigFile = file_get_contents($filePath);
            $resultSigner = Signer::decodeSig($sigFile);

            $resp->status = 1;
            $resp->data = (object)$resultSigner;
        }
    }
} elseif ($REQUEST_CONTENT) {
    $docPath = '/upload/bp/' . $intIBLOCK_ID . '/';
    $strFileName = 'EP_request_' . crc32(serialize(microtime())) . '.req';
    $strPathDoc = $_SERVER['DOCUMENT_ROOT'] . $docPath;
    $resCreate = file_put_contents($strPathDoc . $strFileName, $REQUEST_CONTENT);

    $file['MODULE_ID'] = "bp_{$intIBLOCK_ID}";
    $file['external_id'] = uniqid(
        "toreq_" . $intIBLOCK_ID . "_" . $USER_ID . "_"
    );
    $file = \CFile::MakeFileArray($strPathDoc . $strFileName);
    $FID = CFile::SaveFile($file, "bp/{$intIBLOCK_ID}", true);

    $resp->status = 1;
    $resp->data = (object)['id' => $FID];
}

header('Content-Type: application/json');
echo json_encode($resp);
exit;