<?php

use Bitrix\Main\Type\DateTime;
use Bitrix\Disk\Internals\FileTable;
use Bitrix\Disk\Internals\FolderTable;

/*
// Выгрузка групп
$arList = [];
$res = CSocNetGroup::GetList(['ID' => 'ASC']);
while ($row = $res->Fetch()) {
    $row['IMAGE'] = CFile::GetPath($row['IMAGE_ID']);
    $arList[] = $row;
}
file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/local/groups.json', json_encode($arList));

// Выгрузка отношений
CModule::IncludeModule('socialnetwork');
$arList = [];
$res = CSocNetUserToGroup::GetList(['ID' => 'ASC']);
while ($row = $res->Fetch()) {
    unset($row['ID'], $row['DATE_CREATE'], $row['DATE_UPDATE']);
    $arList[] = $row;
}
file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/local/group_members.json', json_encode($arList));

// Выгрузка разделов
CModule::IncludeModule('iblock');
$objTree = CIBlockSection::GetTreeList(
    [
        "IBLOCK_ID" => 15,
    ],
    ["*", 'UF_*']
);

$arList = [];
while ($depItem = $objTree->GetNext(false, false)) {
    $arList[] = $depItem;
}
file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/local/group_sections.json', json_encode($arList));

// Выгрузка элементов
CModule::IncludeModule('iblock');

$arSelect = ['*', 'PROPERTY_*'];
$arFilter = [
    'IBLOCK_ID'     => 15,
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

file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/local/group_elements.json', json_encode($arList));
*/
require $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php";
require $_SERVER["DOCUMENT_ROOT"] . "/local/vendor/autoload.php";
CModule::IncludeModule('socialnetwork');
CModule::IncludeModule('disk');

// Соответствие тематик
// Старый портал - Новый портал
$arThemes = [
    1 => 196, // Продажи и Маркетинг
    2 => 197, // Проекты
    3 => 198, // Производство
    4 => 199, // Руководители
    5 => 200, // Совместный отдых
];

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

$sCreatedFile = $_SERVER['DOCUMENT_ROOT'] . '/upload/tmp/mfc_import/group.json';
$arCreated = [];
if (file_exists($sCreatedFile)) {
    $arCreated = json_decode(file_get_contents($sCreatedFile), true);
}

if (false) {
    $arGroups = json_decode(curlFile('/local/groups.json'), true);
    foreach ($arGroups as $row) {
        $oldId = $row['ID'];
        if (in_array($oldId, [58])) {
            continue;
        }
        if (isset($arCreated[ $oldId ])) {
            continue;
        }
        $iUser = 2548; // Грудинин
        if (isset($arMfcUsers[ $row['OWNER_ID'] ])) {
            $iUser = $arMfcUsers[ $row['OWNER_ID'] ];
        }

        $row['SITE_ID'] = 'gi';
        $row['OWNER_ID'] = $iUser;
        $row['IMAGE_ID'] = '';
        $row['SUBJECT_ID'] = $arThemes[ $row['SUBJECT_ID'] ];

        if (!empty($row['IMAGE'])) {
            $fileContent = curlFile(str_replace(' ', '%20', $row['IMAGE']));
            $path = $_SERVER['DOCUMENT_ROOT'] . '/upload/tmp/mfc_import/';
            $fileName =  $path . basename($row['IMAGE']);
            file_put_contents($fileName, $fileContent);

            $fileArray = \CFile::MakeFileArray($fileName);

            $fid = CFile::SaveFile($fileArray, "vote");
            if ($fid > 0) {
                $row['IMAGE_ID'] = $fid;
            }
        }

        unset($row['ID'], $row['SUBJECT_NAME'], $row['IMAGE']);

        $groupId = CSocNetGroup::CreateGroup($row['OWNER_ID'], $row);
        if (!$groupId) {
            if ($e = $GLOBALS["APPLICATION"]->GetException()) {
                pre($e->GetString());
            }
        } else {
            $arCreated[ $oldId ] = $groupId;
            CSocNetFeatures::setFeature(
                SONET_ENTITY_GROUP,
                $groupId,
                'files',
                true
            );
            file_put_contents($sCreatedFile, json_encode($arCreated));
        }
    }

    file_put_contents($sCreatedFile, json_encode($arCreated));
}

if (false) {
    $arMembers = json_decode(curlFile('/local/group_members.json'), true);

    $arCurrentMembers = [];
    $res = CSocNetUserToGroup::GetList(['ID' => 'ASC'], ['GROUP_SITE_ID' => 'gi']);
    while ($row = $res->Fetch()) {
        $arCurrentMembers[ $row['GROUP_ID'] ][ $row['USER_ID'] ] = $row;
    }

    foreach ($arMembers as $row) {
        if (!isset($arMfcUsers[ $row['USER_ID'] ])) {
            continue;
        }
        if (!isset($arCreated[ $row['GROUP_ID'] ])) {
            continue;
        }
        if (in_array($row['GROUP_ID'], [58])) {
            continue;
        }
        $row['USER_ID'] = $arMfcUsers[ $row['USER_ID'] ];
        $row['GROUP_ID'] = $arCreated[ $row['GROUP_ID'] ];

        if (isset($arCurrentMembers[ $row['GROUP_ID'] ][ $row['USER_ID'] ])) {
            if ($arCurrentMembers[ $row['GROUP_ID'] ][ $row['USER_ID'] ]['ROLE'] != $row['ROLE']) {
                pre('Юзер уже состоит в группе');
                pre($arCurrentMembers[ $row['GROUP_ID'] ][ $row['USER_ID'] ]);
                pre($row);
                continue;
            }
        }

        $arUserToGroupFields = [
            'USER_ID'               => $row['USER_ID'],
            'GROUP_ID'              => $row['GROUP_ID'],
            'ROLE'                  => $row['ROLE'],
            'MESSAGE'               => false,
            'INITIATED_BY_TYPE'     => "G",
            'INITIATED_BY_USER_ID'  => $row['USER_ID'],
            'SEND_MAIL'             => 'N'
        ];
        $USERTOGROUPID = CSocNetUserToGroup::Add($arUserToGroupFields);
    }
}

if (false) {
    $arSections = json_decode(curlFile('/local/group_sections.json'), true);
    $arElements = json_decode(curlFile('/local/group_elements.json'), true);

    $arGroupList = [];
    $res = CSocNetGroup::GetList(['ID' => 'ASC']);
    while ($row = $res->Fetch()) {
        if (in_array($row['ID'], $arCreated)) {
            $arGroupList[ $row['NAME'] ] = $row['ID'];
        }
    }

    $arSectToGroup = [];
    $arSkip = [
        2168,
        5385,
    ];
    foreach ($arSections as $row) {
        $row['NAME'] = str_replace('&quot;', '"', $row['NAME']);
        if ($row['NAME'] == '.Trash') {
            $arSkip[] = $row['ID'];
            continue;
        }
        if (in_array($row['ID'], $arSkip)) {
            continue;
        }
        if (in_array($row['IBLOCK_SECTION_ID'], $arSkip)) {
            $arSkip[] = $row['ID'];
            continue;
        }
        if ((int)$row['IBLOCK_SECTION_ID'] <= 0) {
            $name = mb_substr($row['NAME'], 8);
            if (isset($arGroupList[ $name ])) {
                $arSectToGroup[ $row['ID'] ] = $arGroupList[ $name ];
            }
        }

        if (isset($arSectToGroup[ $row['IBLOCK_SECTION_ID'] ])) {
            $arSectToGroup[ $row['ID'] ] = $arSectToGroup[ $row['IBLOCK_SECTION_ID'] ];
        }
    }
    $arSectionList = [];
    foreach ($arSections as $row) {
        if (in_array($row['ID'], $arSkip)) {
            continue;
        }
        if (isset($arSectToGroup[ $row['ID'] ])) {
            $arSectionList[ $arSectToGroup[ $row['ID'] ] ][] = $row;
        } else {
            pre($row);
        }
    }

    $arFolders = [];
    $res = FolderTable::getList([
        'select' => ['*'],
        'filter' => ['%XML_ID' => $strMfcFolder]
    ]);
    foreach ($res as $row) {
        $arFolders[ str_replace($strMfcFolder, '', $row['XML_ID']) ] = \Bitrix\Disk\Folder::loadById($row['ID']);
    }

    $entityType = \Bitrix\Disk\ProxyType\Group::className();

    foreach ($arSectionList as $groupId => $sections) {
        $storage = \Bitrix\Disk\Storage::load(array(
            '=ENTITY_ID' => $groupId,
            '=ENTITY_TYPE' => $entityType,
        ));

        foreach ($sections as $row) {
            if (isset($arFolders[ $row['ID'] ])) {
                continue;
            }

            if ((int)$row['IBLOCK_SECTION_ID'] <= 0) {
                $arFolders[ $row['ID'] ] = $storage->getRootObject();
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

    $arFiles = [];
    $res = $GLOBALS['DB']->Query('SELECT ID, XML_ID FROM b_disk_object WHERE XML_ID LIKE "' . $strMfcFile . '%"');
    while ($row = $res->Fetch()) {
        $arFiles[ str_replace($strMfcFile, '', $row['XML_ID']) ] = $row['ID'];
    }

    foreach ($arElements as $row) {
        if (isset($arFiles[ $row['ID'] ])) {
            continue;
        }
        if (in_array($row['IBLOCK_SECTION_ID'], $arSkip)) {
            continue;
        }
        if (!isset($arFolders[ $row['IBLOCK_SECTION_ID'] ])) {
            pre($row);
            die;
            continue;
        }

        $path = $_SERVER['DOCUMENT_ROOT'] . '/upload/tmp/mfc_import/files/' . mb_substr(md5($row['FILE']), 0, 2) . '/';
        if (!is_dir($path)) {
            mkdir($path);
            chmod($path, 0775);
        }
        $fileName =  $path . $row['ID'] . '-' . basename($row['FILE']);
        if (!file_exists($fileName)) {
            $fileContent = curlFile(str_replace(' ', '%20', $row['FILE']));
            file_put_contents($fileName, $fileContent);
        } else {
            $iUser = 2548; // Грудинин
            if (isset($arMfcUsers[ $row['CREATED_BY'] ])) {
                $iUser = $arMfcUsers[ $row['CREATED_BY'] ];
            }
            $fileArray = \CFile::MakeFileArray($fileName);
            $fileArray['name'] = str_replace($row['ID'] . '-', '', $fileArray['name']);

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
}
