<?php

use Bitrix\Main\Loader;
use Bitrix\Main\Data\Cache;
use Sprint\Migration\Helpers\HlblockHelper;

if (Loader::includeModule('sprint.migration')) {
    $obCache = Cache::createInstance();
    if ($obCache->initCache(86400, 'hlblocks', '/init/')) {
        $arIncludeData = $obCache->getVars();
    } elseif ($obCache->startDataCache()) {
        Loader::includeModule('sprint.migration');
        $helper = new HlblockHelper();
        $arIncludeData = [
            'VIOLATORS' => $helper->getHlblockId('Violators'),
            'VIOLATORS_CCMIS' => $helper->getHlblockId('Ccmis'),
            'COURSES_LA'      => $helper->getHlblockId('CoursesLa'),
            'KPI_RETRO'      => $helper->getHlblockId('KPIRetroData')
        ];

        $obCache->endDataCache($arIncludeData);
    }

    foreach ($arIncludeData as $code => $id) {
        define('HLBLOCK_ID_' . $code, $id);
    }
}
