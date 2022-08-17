<?php

namespace Citto;

use CBlog;
use CBlogPost;
use CBlogGroup;
use CBlogUserOptions;
use Bitrix\Main\Loader;
use CSocNetLogDestination;

class Blog
{
    public function handleOnPostAdd($ID, $arFields)
    {
        /*
         * на всех пользователей уйдёт штатно
         */
        if (in_array('UA', $arFields['SOCNET_RIGHTS'])) {
            return;
        }
        /*
         * Пока только для группы Руководители
         */
        if (!in_array('SG772', $arFields['SOCNET_RIGHTS'])) {
            return;
        }
        $isSonetGroup = false;
        foreach ($arFields['SOCNET_RIGHTS'] as $value) {
            if (false !== mb_strpos($value, 'SG')) {
                $isSonetGroup = true;
            }
        }
        if ($isSonetGroup) {
            Loader::includeModule('blog');
            Loader::includeModule('socialnetwork');
            $arPost = CBlogPost::GetByID($ID);
            $IMNotificationFields = [
                'TYPE'          => 'POST',
                'TITLE'         => $arPost['TITLE'],
                'URL'           => str_replace('#post_id#', $arPost['ID'], $arPost['PATH']),
                'ID'            => $arPost['ID'],
                'FROM_USER_ID'  => $arPost['AUTHOR_ID'],
                'TO_USER_ID'    => CSocNetLogDestination::GetDestinationUsers($arFields['SOCNET_RIGHTS']),
            ];
            $userIdSentList = CBlogPost::NotifyIm($IMNotificationFields);
        }
    }

    public function handleOnPostUpdate($ID, $arFields)
    {
        /*
         * на всех пользователей уйдёт штатно
         */
        if (in_array('UA', $arFields['SOCNET_RIGHTS'])) {
            return;
        }
        /*
         * Пока только для группы Руководители
         */
        if (!in_array('SG772', $arFields['SOCNET_RIGHTS'])) {
            return;
        }
        $arDiff = array_diff($arFields['SC_PERM'], $arFields['SC_PERM_OLD']);
        $isSonetGroup = false;
        foreach ($arFields['SOCNET_RIGHTS'] as $value) {
            if (false !== mb_strpos($value, 'SG')) {
                $isSonetGroup = true;
            }
        }
        if ($isSonetGroup && !empty($arDiff)) {
            Loader::includeModule('blog');
            $arPost = CBlogPost::GetByID($ID);
            $IMNotificationFields = [
                'TYPE'          => 'POST',
                'TITLE'         => $arPost['TITLE'],
                'URL'           => str_replace('#post_id#', $arPost['ID'], $arPost['PATH']),
                'ID'            => $arPost['ID'],
                'FROM_USER_ID'  => $arPost['AUTHOR_ID'],
                'TO_USER_ID'    => CSocNetLogDestination::GetDestinationUsers($arDiff),
            ];
            $userIdSentList = CBlogPost::NotifyIm($IMNotificationFields);
        }
    }

    public function handleOnAfterCBlogUserOptionsSet($arOptions)
    {
        if (
            $arOptions[0]['name'] == 'BLOG_POST_IMPRTNT' &&
            $arOptions[0]['value'] == 'Y'
        ) {
            Loader::includeModule('blog');
            $res = CBlogUserOptions::GetList(
                [
                    'ID' => 'DESC'
                ],
                [
                    'USER_ID'   => $GLOBALS['USER']->GetID(),
                    'POST_ID'   => $arOptions[0]['post_id'],
                    'NAME'      => $arOptions[0]['name'],
                    'VALUE'     => $arOptions[0]['value'],
                ]
            );
            global $USER_FIELD_MANAGER;
            while ($row = $res->Fetch()) {
                $USER_FIELD_MANAGER->Update(
                    'BLOG_POST_PARAM',
                    $row['ID'],
                    ['UF_DATE' => date('d.m.Y H:i:s')]
                );
            }
        }
    }
}
