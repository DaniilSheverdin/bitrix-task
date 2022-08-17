<?php

namespace Citto\Profile;

use CFile;
use Exception;
use RuntimeException;
use CBitrixComponent;
use DirectoryIterator;
use Bitrix\Main\Loader;
use Bitrix\Main\IO\File;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Engine\ActionFilter;
use Bitrix\DocumentGenerator\Body\Docx;
use Bitrix\DocumentGenerator\DataProvider\ArrayDataProvider;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * Class PersonalAjaxController
 *
 * @package Citto\Profile
 */
class AjaxController extends Controller
{
    /**
     * Конфигурация действий
     *
     * @return array
     */
    public function configureActions(): array
    {
        $arParams = [
            'prefilters' => [
                new ActionFilter\Authentication(),
                new ActionFilter\HttpMethod(
                    [ActionFilter\HttpMethod::METHOD_POST]
                ),
                new ActionFilter\ContentType(
                    [ActionFilter\ContentType::JSON]
                ),
                new ActionFilter\Csrf(),
            ],
            'postfilters' => []
        ];

        return [
            'getReferenceLensData' => $arParams,
        ];
    }

    /**
     * Получить объективку для пользователя.
     *
     * @param int $userId
     *
     * @return string
     */
    public function getReferenceLensDataAction($userId)
    {
        CBitrixComponent::includeComponentClass('citto:profile.personal');
        $obComponent = new Personal();

        $docPath = '/upload/personal/ReferenceLens/';
        $path = $_SERVER['DOCUMENT_ROOT'] . $docPath;
        if (!mkdir($path, 0775, true) && !is_dir($path)) {
            throw new RuntimeException('Directory ' . $docPath . ' was not created');
        }

        $arUser = $obComponent->getUserPortalData($userId);
        $arData = [];
        if (!empty($arUser['UF_SID'])) {
            $arData = $obComponent->getUserOnesData($arUser['UF_SID'], 'ReferenceLensData');
        }

        if (empty($arData)) {
            throw new Exception('Отсутствуют данные для формирования справки');
        }

        Loader::includeModule('documentgenerator');
        $file = new File($_SERVER['DOCUMENT_ROOT'] . '/local/templates_docx/personal/ReferenceLensData.docx');
        $body = new Docx($file->getContents());
        $body->normalizeContent();

        if (!isset($arData['Family']['FamilyMember'][0])) {
            $arData['Family']['FamilyMember'] = [ $arData['Family']['FamilyMember'] ];
        }
        $arNewData = [
            'Family'         => new ArrayDataProvider(
                $arData['Family']['FamilyMember'],
                [
                    'ITEM_NAME'     => 'Item',
                    'ITEM_PROVIDER' => ArrayDataProvider::class
                ]
            ),
            'FamilyKinship' => 'Family.Item.Kinship',
            'FamilyName'    => 'Family.Item.FIO',
            'FamilyBirth'   => 'Family.Item.DatePlaceOfBirth',
            'FamilyWork'    => 'Family.Item.PlaceOfWork',
            'FamilyResid'   => 'Family.Item.PlaceOfResidence',

            'OrderRank'     => self::filterArray($arData['OrderRank']),
            'RankName'      => 'OrderRank.Item.NAME',

            'Position'      => self::filterArray($arData['Position']),
            'PositionName'  => 'Position.Item.NAME',

            'Awards'        => self::filterArray($arData['Awards']),
            'AwardName'     => 'Awards.Item.NAME',

            'WorkActivity'      => self::filterArray($arData['WorkActivityInfo']),
            'WorkActivityName'  => 'WorkActivity.Item.NAME',

            'EducationalInstitution'        => self::filterArray($arData['EducationalInstitution']),
            'EducationalInstitutionName'    => 'EducationalInstitution.Item.NAME',

            'Training'        => self::filterArray($arData['Training']),
            'TrainingName'    => 'Training.Item.NAME',

            'Retraining'        => self::filterArray($arData['Retraining']),
            'RetrainingName'    => 'Retraining.Item.NAME',

            'Abroad'        => self::filterArray($arData['Abroad']),
            'AbroadName'    => 'Abroad.Item.NAME',

            'ForeignLanguages'        => self::filterArray($arData['ForeignLanguages']),
            'ForeignLanguagesName'    => 'ForeignLanguages.Item.NAME',

            'Specialty'        => self::filterArray($arData['Specialty']),
            'SpecialtyName'    => 'Specialty.Item.NAME',

            'EducationLevel'        => self::filterArray($arData['EducationLevel']),
            'EducationLevelName'    => 'EducationLevel.Item.NAME',
        ];
        $arData = array_merge($arData, $arNewData);

        if (!empty($arData['Photo'])) {
            $fileName = $docPath . '/photo_' . $userId . '.' . $arData['Photo']['Ext'];
            $fileNameResize = $docPath . '/resize_' . $userId . '.' . str_replace('jpg', 'jpeg', $arData['Photo']['Ext']);
            $resizePath = $_SERVER['DOCUMENT_ROOT'] . $fileNameResize;
            file_put_contents($_SERVER['DOCUMENT_ROOT'] . $fileName, $arData['Photo']['Data']);
            CFile::ResizeImageFile(
                $_SERVER['DOCUMENT_ROOT'] . $fileName,
                $resizePath,
                [
                    'width' => 95,
                    'height' => 115,
                ]
            );
            $arData['PhotoName'] = $_SERVER['DOCUMENT_ROOT'] . $fileNameResize;
        }

        $body->setValues($arData);
        $body->process();
        $strContent = $body->getContent();
        $strFileName = 'ref_' . $userId . '_' . time() . '.docx';
        file_put_contents($path . $strFileName, $strContent);

        return $docPath . $strFileName;
    }

    /**
     * Подготовить массив для генерации docx.
     *
     * @param string $string
     *
     * @return ArrayDataProvider
     */
    private static function filterArray($string)
    {
        $arReturn = explode("\n", trim($string));
        // $arReturn = array_filter($arReturn);
        $arReturn = array_map(
            function ($el) {
                return ['NAME' => $el];
            },
            $arReturn
        );

        return new ArrayDataProvider(
            $arReturn,
            [
                'ITEM_NAME'     => 'Item',
                'ITEM_PROVIDER' => ArrayDataProvider::class
            ]
        );
    }
}
