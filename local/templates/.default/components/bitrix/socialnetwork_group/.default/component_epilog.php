<?

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

if (in_array(SITE_ID, ['s1', 'nh'])) {
    $groupId = $arResult['VARIABLES']['group_id'];
    $curUserId = $GLOBALS['USER']->GetID();

    $arGroupSites = [];
    $rsGroupSite = CSocNetGroup::GetSite($groupId);
    while ($arGroupSite = $rsGroupSite->Fetch()) {
        $arGroupSites[] = $arGroupSite["LID"];
    }

    if (!in_array(SITE_ID, $arGroupSites) && !in_array('gi', $arGroupSites)) {
        if (false !== CSocNetUserToGroup::GetUserRole($curUserId, $groupId)) {
            \Bitrix\Socialnetwork\WorkgroupSiteTable::add([
                'GROUP_ID'  => $groupId,
                'SITE_ID'   => SITE_ID,
            ]);
            LocalRedirect($APPLICATION->GetCurPageParam());
        }
    }

    $orm = \Bitrix\Main\UserTable::getList([
        'select'    => ['ID', 'LID'],
        'filter'    => ['ID' => $curUserId]
    ]);
    $arUser = $orm->fetch();
    if (SITE_ID == 'nh' && $arUser['LID'] == 's1') {
        LocalRedirect(str_replace('/citto/', '/', $APPLICATION->GetCurPageParam()));
    }
}

if ($this->__buffer_template === true) {
    if (!in_array($this->__template->__page, array("user_files_menu", "group_files_menu"))) {
        $this->__template_html = ob_get_clean();
        $this->IncludeComponentTemplate(mb_strpos($this->__template->__page, "user_files") !== false ? "user_files_menu" : "group_files_menu");
    } else {
        echo $this->__template_html;
    }
}
