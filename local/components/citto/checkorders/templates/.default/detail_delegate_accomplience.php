<?

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

if (!isset($_REQUEST['detail'])) {
    return;
}

if (empty($arPerm['ispolnitel_delegated'])) {
    return;
}

$curUserId = $GLOBALS['USER']->GetID();
$arMyDelegates = [];
global $accomplicesTree;
$accomplicesTree = [];
foreach ($arElement['PROPERTY_ACCOMPLICES_VALUE'] as $uId) {
    $accomplicesTree[ $uId ] = [];
}
foreach ($arElement['~PROPERTY_DELEGATE_HISTORY_VALUE'] as $delegateRow) {
    $arDelegateRow = json_decode($delegateRow, true);
    if (($arDelegateRow['CURRENT_USER'] == $curUserId) && isset($arDelegateRow['ACCOMPLICES'])) {
        if (is_array($arDelegateRow['ACCOMPLICES'])) {
            $arMyDelegates = array_merge(
                $arMyDelegates,
                $arDelegateRow['ACCOMPLICES']
            );
        } else {
            $arMyDelegates[] = $arDelegateRow['ACCOMPLICES'];
        }
    }
    if (isset($arDelegateRow['ACCOMPLICES'])) {
        if (
            array_key_exists($arDelegateRow['CURRENT_USER'], $accomplicesTree) &&
            array_key_exists($arDelegateRow['ACCOMPLICES'], $accomplicesTree)
        ) {
            $accomplicesTree[ $arDelegateRow['CURRENT_USER'] ][] = $arDelegateRow['ACCOMPLICES'];
        }
    }
}

function renderAccTree(
    $tree,
    $delegate,
    $component,
    &$showed = [],
    $bAddBtn = false
) {
    global $accomplicesTree;
    $btn = '<a
                href="javascript:void(0);"
                class="ui-btn ui-btn-xs ui-btn-primary ui-btn-icon-add js-delegate-add"
                data-single="false"
                title="Добавить соисполнителя"></a>';
    $return = '<ul>';
    $curUserId = $GLOBALS['USER']->GetID();
    foreach ($tree as $accomplice => $subUsers) {
        if (!is_array($subUsers)) {
            $accomplice = $subUsers;
            $subUsers = [];
        }
        if (in_array($accomplice, $showed)) {
            continue;
        }
        if (empty($subUsers) && !empty($accomplicesTree[ $accomplice ])) {
            $subUsers = $accomplicesTree[ $accomplice ];
        }
        $bDelete = false;
        if ($accomplice == $curUserId) {
            $bDelete = false;
        } elseif (in_array($accomplice, $delegate)) {
            $bDelete = true;
        }

        $showed[] = $accomplice;
        
        $return .= '<li>';
        $return .= '<div class="order-tag">
            <span bx-tooltip-user-id="' . $accomplice . '">' . $component->getUserFullName($accomplice) . '</span>
            ' . ($bDelete ? '
            <i class="js-delegate-remove">&times;</i>
            <input name="DELEGATE_ACCOMPLICES[]" type="hidden" class="DELEGATE_ACCOMPLICES" value="' . $accomplice . '" />
            ' : '' ) . '
        </div>';

        if ($accomplice == $curUserId) {
            $return .= $btn;
        }

        $return .= renderAccTree(
            $subUsers,
            $delegate,
            $component,
            $showed,
            ($accomplice == $curUserId)
        );

        $return .= '</li>';
    }

    $return .= '</ul>';

    return $return;
}
?>
<div class="card mb-2" style="height:auto">
    <div class="card-body">
        <form method="POST" action="?detail=<?=$_REQUEST['detail']?>&back_url=<?=$backUrl?>">
            <div class="row">
                <div class="col-3">
                    <b>Соисполнители:</b>
                </div>
                <div class="col-9 order-tags js-user-selector">
                    <?=renderAccTree($accomplicesTree, $arMyDelegates, $this->__component)?>
                    <div class="d-none order-tag" data-li="true">
                        <span bx-tooltip-user-id=""></span>
                        <i class="js-delegate-remove">&times;</i>
                        <input name="DELEGATE_ACCOMPLICES[]" type="hidden" class="dummy DELEGATE_ACCOMPLICES" disabled />
                    </div>
                </div>
            </div>

            <div class="row mt-2">
                <div class="col-3">
                    <b>Комментарий:</b>
                </div>
                <div class="col-9">
                    <textarea name="DELEGATE_COMMENT" placeholder="Комментарий" cols="70" rows="1"></textarea>
                </div>
            </div>

            <button class="ui-btn js-accept-ispolnitel" type="submit" name="action" value="delegate_accomplices" data-field-class="DELEGATE_ACCOMPLICES">Изменить</button>
        </form>
        <div class="d-none" id="SELECT_USER">
            <select class="form-control" name="SELECT_USER">
                <?
                foreach ($arPerm['ispolnitel_delegated'] as $sKey => $sIspolnitelName) {
                    ?>
                    <option value="<?=$sKey?>"><?=$sIspolnitelName?></option>
                    <?
                }
                ?>
            </select>
        </div>
    </div>
</div>
