<?php

use Bitrix\Main\Loader;
use Bitrix\Main\Data\Cache;
use Sprint\Migration\Helpers\UserGroupHelper;

if (Loader::includeModule('sprint.migration')) {
    $obCache = Cache::createInstance();
    if ($obCache->initCache(86400, 'users_groups', '/init/')) {
        $arIncludeData = $obCache->getVars();
    } elseif ($obCache->startDataCache()) {
        $helper = new UserGroupHelper();
        $arIncludeData = [
            'OMNI' => $helper->getGroupId('omni_tracker_admins'),
            'CIT_EMPLOYEES' => $helper->getGroupId('EMPLOYEES_nh'),
            'MIGRATION_DOCS' => $helper->getGroupId('krpn'),
            'NO_REDIRECT' => $helper->getGroupId('NO_REDIRECTS'),
            'VACCINATION' => $helper->getGroupId('vaccination'),
            'EDU_OPERATOR' => $helper->getGroupId('operator-edu'),
            'EDU_KURATOR' => $helper->getGroupId('kurator-edu'),
            'EDU_TEHNADZOR' => $helper->getGroupId('tehnadzor-edu'),
            'EDU_FINANCE' => $helper->getGroupId('finance-edu'),
            'EDU_ADMIN' => $helper->getGroupId('admin-edu'),
        ];

        $obCache->endDataCache($arIncludeData);
    }

    foreach ($arIncludeData as $code => $id) {
        define('GROUP_ID_' . $code, $id);
    }
}
