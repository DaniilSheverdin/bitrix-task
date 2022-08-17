<?php

require($_SERVER["DOCUMENT_ROOT"] . '/bitrix/modules/main/include/prolog_before.php');
if ($_POST['action']=='complete') {
    global $USER;
    $rsUsers = CUser::GetList($by = '', $order='ASC', '', ''); // выбираем пользователей

    $arr_users = [];
    while ($u=$rsUsers->Fetch()) {
        $user = $u['LAST_NAME'].' '.$u['NAME'].' '.$u['SECOND_NAME'];
        $pos = mb_strpos(mb_strtolower($user), mb_strtolower($_POST['q']));
        if ($pos !== false) {
            $arr_users[$u['ID']] = $user;
        }
    }
    echo json_encode($arr_users);
    die;
}

if ($_POST['action']=='sel_structure') {
    $types = [];
    $s = CIBlock::GetList(array("SORT"=>"ASC"), array('ACTIVE'=>'Y', 'TYPE'=>$_POST['type']));
    while ($item = $s->GetNext()) {
        $types[$item['ID']] = $item['NAME'];
    }
    echo json_encode($types);
    die;
}

if ($_POST['action']=='get_employes') {
    global $USERS;
    $userArr = [];
    $users = $_POST['usersArr'];
    $users = implode('|', $users);
    $rsUsers = CUser::GetList($by = "last_name", $order = "desc", ['ID' => $users], ['FIELDS' => ['ID', 'LAST_NAME', 'NAME', 'SECOND_NAME']]);
    while ($u = $rsUsers->getNext()) {
        $userArr[$u['ID']] = [$u['LAST_NAME'].' '.$u['FIRST_NAME'].' '.$u['SECOND_NAME']];
    }
    echo json_encode($userArr);
    die;
}
