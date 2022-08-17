<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Engine\Controller;

class CustomAjaxController extends Controller
{
    /**
     * @return array
     */
    public function configureActions()
    {
        return [
            'file' => [
                'prefilters' => []
            ]
        ];
    }

    /**
     * @param array $files
     *
     * @return array
     */
    public static function fileAction($files = [])
    {
        $arFiles = [];
        foreach ($files as $item) {
            array_push($arFiles, CFile::GetPath($item));
        }

        return [
            'file' => ($arFiles)
        ];
    }
}
