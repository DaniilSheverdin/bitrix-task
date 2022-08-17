<?php

use Bitrix\Main\Loader;
use Bitrix\Main\Data\Cache;
use Sprint\Migration\Helpers\IblockHelper;

if (Loader::includeModule('sprint.migration')) {
    $obCache = Cache::createInstance();
    if ($obCache->initCache(86400, 'iblocks_sections', '/init/')) {
        $arIncludeData = $obCache->getVars();
    } elseif ($obCache->startDataCache()) {
        Loader::includeModule('sprint.migration');
        $helper = new IblockHelper();
        $arIncludeData = [
            'IBLOCKS' => [
                'STRUCTURE' => $helper->getIblockId('departments', 'structure'),
                'CONTROLS' => $helper->getIblockId('controls', 'pokazateli'),
                'INDICATORS' => $helper->getIblockId('pokazateli', 'pokazateli'),
                'INDICATORS_CATALOG' => $helper->getIblockId('indicators_catalog', 'pokazateli'),
                'INDICATORS_THEMES' => $helper->getIblockId('indicators_themes', 'pokazateli'),
                'INDICATORS_DEPARTMENTS' => $helper->getIblockId('indicators_departments', 'pokazateli'),
                'OMNI_TRACKER' => $helper->getIblockId('omni_tracker', 'services'),
                'GEOPOSITION_DATA' => $helper->getIblockId('geoposition_data', 'docs'),
                'MIGRATION_DOCS' => $helper->getIblockId('docs_migration', 'docs'),
                'NEW_EMPLOYEE' => $helper->getIblockId('vihod_novogo_sotrudnika', 'bitrix_processes'),
                'FLA' => $helper->getIblockId('formirovanie_listka_adaptaciy', 'bitrix_processes'),
                'SZ' => $helper->getIblockId('sluzhebnaya_zapiska', 'bitrix_processes'),
                'SOLA' => $helper->getIblockId('soglasovanie_otcheta_la', 'bitrix_processes'),
                'VNPR' => $helper->getIblockId('vnutrenne_peremeshenie', 'bitrix_processes'),
                'ZKCH' => $helper->getIblockId('zakaz_kanctovarov', 'bitrix_processes'),
                'ZNKS' => $helper->getIblockId('zayavka_na_kartridzhy_sudyam', 'bitrix_processes'),
                'ZNMC' => $helper->getIblockId('zayavka_na_mc', 'bitrix_processes'),
                'ST' => $helper->getIblockId('prohozhdenie_stazhirovky', 'bitrix_processes'),
                'COMPENS_HOLIDAY' => $helper->getIblockId('holiday_compensation', 'bitrix_processes'),
                'KPI_STRUCTURE' => $helper->getIblockId('struct', 'kpi'),
                'KPI' => $helper->getIblockId('kpi', 'kpi'),
                'KPI_USERS' => $helper->getIblockId('kpi_data_users', 'kpi'),
                'KPI_WORK_POSITIONS' => $helper->getIblockId('work_positions', 'kpi'),
                'KPI_TEST_STRUCTURE' => $helper->getIblockId('struct_test', 'kpi'),
                'KPI_TEST' => $helper->getIblockId('kpi_test', 'kpi'),
                'KPI_TEST_USERS' => $helper->getIblockId('kpi_data_users_test', 'kpi'),
                'KPI_TEST_WORK_POSITIONS' => $helper->getIblockId('work_positions_test', 'kpi'),
                'VACCINATION' => $helper->getIblockId('vaccination', 'docs'),
                'BP_REMOTE_WORK' => $helper->getIblockId('remote_work', 'bitrix_processes'),
                'BP_HELPMONEY_REPORT' => $helper->getIblockId('helpmoney_report', 'bitrix_processes'),
            ],
            'SECTIONS' => []
        ];
        $arIncludeData['SECTIONS']['SECTION_ID_CONTROLS_CIT'] = $helper->getSectionId(
            $arIncludeData['IBLOCKS']['CONTROLS'],
            'cit'
        );
        $arIncludeData['SECTIONS']['CONTROLS_SECTION_ID_UIB'] = $helper->getSectionId(
            $arIncludeData['IBLOCKS']['CONTROLS'],
            'upravlenie-informatsionnoy-bezopasnosti'
        );
        $arIncludeData['SECTIONS']['SECTION_ID_MIGRATION_DOCS_RF'] = $helper->getSectionId(
            $arIncludeData['IBLOCKS']['MIGRATION_DOCS'],
            'rf'
        );
        $arIncludeData['SECTIONS']['SECTION_ID_MIGRATION_DOCS_MZH'] = $helper->getSectionId(
            $arIncludeData['IBLOCKS']['MIGRATION_DOCS'],
            'mzh'
        );
        $arIncludeData['SECTIONS']['SECTION_ID_MIGRATION_DOCS_MP'] = $helper->getSectionId(
            $arIncludeData['IBLOCKS']['MIGRATION_DOCS'],
            'mp'
        );
        $arIncludeData['SECTIONS']['SECTION_ID_MIGRATION_CONT'] = $helper->getSectionId(
            $arIncludeData['IBLOCKS']['MIGRATION_DOCS'],
            'contact'
        );
        $arIncludeData['SECTIONS']['SECTION_ID_MIGRATION_DOCS_COMING'] = $helper->getSectionId(
            $arIncludeData['IBLOCKS']['MIGRATION_DOCS'],
            'coming'
        );
        $arIncludeData['SECTIONS']['SECTION_ID_MIGRATION_DOCS_ARRIVED'] = $helper->getSectionId(
            $arIncludeData['IBLOCKS']['MIGRATION_DOCS'],
            'arrived'
        );
        $arIncludeData['SECTIONS']['SECTION_ID_CITTO_STRUCTURE'] = $helper->getSectionId(
            $arIncludeData['IBLOCKS']['STRUCTURE'],
            'citto'
        );
        $arIncludeData['SECTIONS']['SECTION_ID_CITTO_UIS_STRUCTURE'] = $helper->getSectionId(
            $arIncludeData['IBLOCKS']['STRUCTURE'],
            'uis'
        );
        $arIncludeData['SECTIONS']['SECTION_ID_KPI_EXTRA'] = $helper->getSectionId(
            $arIncludeData['IBLOCKS']['KPI'],
            'kpi_extra'
        );
        $arIncludeData['SECTIONS']['SECTION_ID_KPI_NOTIFIES'] = $helper->getSectionId(
            $arIncludeData['IBLOCKS']['KPI'],
            'notify'
        );
        $arIncludeData['SECTIONS']['SECTION_ID_KPI_TEST_EXTRA'] = $helper->getSectionId(
          $arIncludeData['IBLOCKS']['KPI_TEST'],
          'kpi_extra_test'
        );
        $arIncludeData['SECTIONS']['SECTION_ID_KPI_TEST_NOTIFIES'] = $helper->getSectionId(
          $arIncludeData['IBLOCKS']['KPI_TEST'],
          'notify_test'
        );
        $arIncludeData['SECTIONS']['SECTION_ID_MFC_STRUCTURE'] = $helper->getSectionId(
            $arIncludeData['IBLOCKS']['STRUCTURE'],
            'mfc'
        );
        $arIncludeData['SECTIONS']['SECTION_ID_KPI_KP_PROJECTS'] = $helper->getSectionId(
            $arIncludeData['IBLOCKS']['KPI'],
            'kp_projects'
        );
        $arIncludeData['SECTIONS']['SECTION_ID_KPI_KP_TOP_MANAGERS'] = $helper->getSectionId(
            $arIncludeData['IBLOCKS']['KPI'],
            'kp_top_managers'
        );
        $arIncludeData['SECTIONS']['SECTION_ID_KPI_KP_BASE_TASKS'] = $helper->getSectionId(
            $arIncludeData['IBLOCKS']['KPI'],
            'kp_base_tasks'
        );

        $obCache->endDataCache($arIncludeData);
    }

    foreach ($arIncludeData['IBLOCKS'] as $code => $id) {
        define('IBLOCK_ID_' . $code, $id);
    }
    foreach ($arIncludeData['SECTIONS'] as $code => $id) {
        define($code, $id);
    }
}
