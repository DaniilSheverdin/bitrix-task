<?php

use Bitrix\Main\Entity\Query;
use Bitrix\Main\Type\DateTime;
use Bitrix\Disk\Internals\FileTable;
use Bitrix\Disk\Internals\FolderTable;

/*

// Общий диск (19) = 3.85 Gb
// Библиотека пользователей (16) = 6.8 Gb
// Библиотека групп (15) = 1.65 Gb

// Выгрузка разделов
CModule::IncludeModule('iblock');
$objTree = CIBlockSection::GetTreeList(
    [
        "IBLOCK_ID" => 19,
    ],
    ["*", 'UF_*']
);

$arList = [];
while ($depItem = $objTree->GetNext(false, false)) {
    $arList[] = $depItem;
}
file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/local/doc_sections.json', json_encode($arList));

// Выгрузка элементов
CModule::IncludeModule('iblock');

$arSelect = ['*', 'PROPERTY_*'];
$arFilter = [
    'IBLOCK_ID'     => 19,
    'ACTIVE_DATE'   => 'Y',
    'ACTIVE'        => 'Y',
];

$arList = [];
$res = CIBlockElement::GetList(['ID' => 'ASC'], $arFilter, false, false, $arSelect);
while ($ob = $res->GetNextElement()) {
    $arFields = $ob->GetFields();
    $arProps = $ob->GetProperties();

    if ($arFields['ACTIVE'] != 'Y') {
        continue;
    }

    foreach ($arFields as $k => $v) {
        if (0 === mb_strpos($k, 'WF_') || 0 === mb_strpos($k, '~WF_')) {
            unset($arFields[ $k ]);
            continue;
        }
        if (0 === mb_strpos($k, '~')) {
            unset($arFields[ $k ]);
            $arFields[ str_replace('~', '', $k) ] = $v;
        }
    }
    unset($arFields['SEARCHABLE_CONTENT']);

    $arFields['FILE'] = CFile::GetPath($arProps['FILE']['VALUE']);

    $arList[] = $arFields;
}

file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/local/doc_elements.json', json_encode($arList));

*/
require $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php";
require $_SERVER["DOCUMENT_ROOT"] . "/local/vendor/autoload.php";
CModule::IncludeModule('iblock');
CModule::IncludeModule('disk');

@ini_set("memory_limit", "4096M");

function curlFile($path)
{
    if ($oCurl = curl_init('http://172.21.242.139' . $path)) {
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
        $sResult = curl_exec($oCurl);
        curl_close($oCurl);

        return $sResult;
    }
    return '';
}

$strMfcFolder = 'MFC_FOLDER_';
$strMfcFile = 'MFC_FILE_';

$arMfcUsers = [];
$rsUsers = CUser::GetList(($by = "ID"), ($order = "ASC"), ['ACTIVE' => 'Y'], ['SELECT' => ['*', 'UF_*']]);
while ($arUser = $rsUsers->GetNext(false, false)) {
    if (empty($arUser['ADMIN_NOTES'])) {
        continue;
    }
    if (false !== mb_strpos($arUser['ADMIN_NOTES'], 'MFC_')) {
        $arMfcUsers[ str_replace('MFC_', '', $arUser['ADMIN_NOTES']) ] = $arUser['ID'];
    }
}

$entityType = \Bitrix\Disk\ProxyType\Common::className();

$storage = \Bitrix\Disk\Storage::load(array(
    '=ENTITY_ID' => 'shared_files_gi',
    '=ENTITY_TYPE' => $entityType,
));

$arFolders = [
    0 => $storage->getRootObject()
];

$arParams = array(
    'select' => array(
        '*',
    ),
    'filter' => array(
        '%XML_ID' => $strMfcFolder
    ),
);
$res = FolderTable::getList($arParams);
foreach ($res as $row) {
    $arFolders[ str_replace($strMfcFolder, '', $row['XML_ID']) ] = \Bitrix\Disk\Folder::loadById($row['ID']);
}

$arFiles = [];
$res = $GLOBALS['DB']->Query('SELECT ID, XML_ID FROM b_disk_object WHERE XML_ID LIKE "' . $strMfcFile . '%"');
while ($row = $res->Fetch()) {
    $arFiles[ str_replace($strMfcFile, '', $row['XML_ID']) ] = $row['ID'];
}

if (false) {
    $arSections = json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/upload/tmp/doc_sections.json'), true);
    /*
     * Создание папок.
     */
    foreach ($arSections as $row) {
        if (isset($arFolders[ $row['ID'] ])) {
            continue;
        }
        if (!isset($arFolders[ (int)$row['IBLOCK_SECTION_ID'] ])) {
            pre($row);
            continue;
        }
        $iUser = 2548; // Грудинин
        if (isset($arMfcUsers[ $row['CREATED_BY'] ])) {
            $iUser = $arMfcUsers[ $row['CREATED_BY'] ];
        }
        $newFolder = $arFolders[ (int)$row['IBLOCK_SECTION_ID'] ]->addSubFolder([
            'NAME'          => str_replace("\t", ' ', $row['NAME']),
            'CREATED_BY'    => $iUser,
            'XML_ID'        => $strMfcFolder . $row['ID'],
            'CREATE_TIME'   => DateTime::createFromTimestamp(strtotime($row['DATE_CREATE'])),
            'UPDATE_TIME'   => DateTime::createFromTimestamp(strtotime($row['TIMESTAMP_X'])),
        ]);
        if ($newFolder) {
            $arFolders[ $row['ID'] ] = \Bitrix\Disk\Folder::loadById($newFolder->getId());
        }
    }
}

if (false) {
    $counter = 0;
    $arElements = json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/upload/tmp/doc_elements.json'), true);
    /*
     * Создание файлов.
     */
    foreach ($arElements as $row) {
        if (isset($arFiles[ $row['ID'] ])) {
            continue;
        }
        if (!isset($arFolders[ $row['IBLOCK_SECTION_ID'] ])) {
            continue;
        }

        $fileContent = curlFile(str_replace(' ', '%20', $row['FILE']));
        $path = $_SERVER['DOCUMENT_ROOT'] . '/upload/tmp/mfc_import/';
        $fileName =  $path. basename($row['FILE']);
        mkdir($path);
        chmod($path, 0775);

        file_put_contents($fileName, $fileContent);

        $iUser = 2548; // Грудинин
        if (isset($arMfcUsers[ $row['CREATED_BY'] ])) {
            $iUser = $arMfcUsers[ $row['CREATED_BY'] ];
        }
        $fileArray = \CFile::MakeFileArray($fileName);
        $arFileFields = [
            'NAME'          => $row['NAME'],
            'CREATED_BY'    => $iUser,
            'XML_ID'        => $strMfcFile . $row['ID'],
            'EXTERNAL_ID'   => $strMfcFile . $row['ID'],
            'CREATE_TIME'   => DateTime::createFromTimestamp(strtotime($row['DATE_CREATE'])),
            'UPDATE_TIME'   => DateTime::createFromTimestamp(strtotime($row['TIMESTAMP_X'])),
        ];
        try {
            $file = $arFolders[ $row['IBLOCK_SECTION_ID'] ]->uploadFile($fileArray, $arFileFields);
            if ($file) {
                $arFileFilter = [
                    'ID' => $file->getId(),
                ];
                FileTable::updateAttributesByFilter($arFileFields, $arFileFilter);
            }
        } catch (Exception $e) {
            pre($e->getMessage());
            die;
        }
    }
}
