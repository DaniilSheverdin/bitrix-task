<?php

namespace Citto;

use CBlog;
use CBlogPost;
use CBlogGroup;
use Bitrix\Main\Loader;
use Bitrix\Main\UserTable;

class Pull
{
    public function handleOnBeforeSendPush($userId, $mode, $arFields)
    {
        $findStr = 'BLOG|POST|';
        if (false !== mb_strpos($arFields['TAG'], $findStr)) {
            $postId = (int)str_replace($findStr, '', $arFields['TAG']);

            Loader::includeModule('blog');
            $arPost = CBlogPost::GetByID($postId);
            $arBlog = CBlog::GetByID($arPost['BLOG_ID']);
            $arBlogGroup = CBlogGroup::GetByID($arBlog['GROUP_ID']);
            $arEmailUser = UserTable::getList([
                'select' => [
                    'ID', 'LID'
                ],
                'filter' => [
                    'ID' => $userId
                ]
            ])->fetch();

            if ($arEmailUser['LID'] != $arBlogGroup['SITE_ID']) {
                return 'SKIP';
            }
        }
    }
}
