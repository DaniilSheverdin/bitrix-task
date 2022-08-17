<?

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
global $APPLICATION;

$APPLICATION->SetTitle('Редактирование списков - Внешние организации');

if (
    $_POST['add_external'] &&
    $_POST['NAME']
) {
    $arTypes = Citto\ControlOrders\Executors::getTypesList(false);
    $extTypeId = 0;
    foreach ($arTypes as $type) {
        if ($type['EXTERNAL_ID'] == 'external') {
            $extTypeId = $type['ID'];
        }
    }

    (new CIBlockElement())->Add([
        'IBLOCK_ID'         => Citto\ControlOrders\Settings::$iblockId['ISPOLNITEL'],
        'ACTIVE'            => 'Y',
        'NAME'              => trim($_POST['NAME']),
        'PROPERTY_VALUES'   => [
            'TYPE' => $extTypeId,
        ],
    ]);
    LocalRedirect($APPLICATION->GetCurPageParam());
} elseif (
    $_POST['rename_external'] &&
    $_POST['ID'] > 0
) {
    (new CIBlockElement())->Update(
        $_POST['ID'],
        [
            'NAME' => trim($_POST['NAME']),
        ]
    );
    LocalRedirect($APPLICATION->GetCurPageParam());
} elseif (
    $_POST['remove_external'] &&
    $_POST['ID'] > 0
) {
    (new CIBlockElement())->Update(
        $_POST['ID'],
        [
            'ACTIVE' => 'N',
        ]
    );
    LocalRedirect($APPLICATION->GetCurPageParam());
}

$arCurrent = [];
foreach ($arResult['ISPOLNITELS'] as $arIspolnitel) {
    if ($arIspolnitel['PROPERTY_TYPE_CODE'] == 'external') {
        $arCurrent[] = $arIspolnitel;
    }
}

?>
<ul>
    <?foreach ($arCurrent as $extRow) : ?>
    <li>
        <?=$extRow['NAME']?>
        <small>
            <form class="d-inline" method="POST" action="<?=$APPLICATION->GetCurPageParam()?>">
                <input name="ID" type="hidden" value="<?=$extRow['ID']?>" />
                <input name="remove_external" type="hidden" value="Y" />
                [<a href="#" class="js-delete-external">удалить</a>]
            </form>
            [<a href="#" class="js-rename-external" data-id="<?=$extRow['ID']?>" data-name="<?=$extRow['NAME']?>">изменить</a>]
        </small>
    </li>
    <?endforeach;?>
    <li>
        <a href="#" class="js-add-external">Добавить</a>
        <br/>
        <br/>

        <form class="d-none row js-add-external-form" method="POST" action="<?=$APPLICATION->GetCurPageParam()?>">
            <div class="col-3">
                <input name="NAME" placeholder="Название" class="form-control" required />
            </div>
            <div class="col-3">
                <input name="add_external" type="submit" value="Добавить" class="btn btn-success" />
            </div>
        </form>

        <form class="d-none row js-rename-external-form" method="POST" action="<?=$APPLICATION->GetCurPageParam()?>">
            <div class="col-3">
                <input name="ID" type="hidden" />
                <input name="NAME" placeholder="Название" class="form-control" required />
            </div>
            <div class="col-3">
                <input name="rename_external" type="submit" value="Изменить" class="btn btn-success" />
            </div>
        </form>
    </li>
</ul>
<script type="text/javascript">
    $(document).ready(function(){
        $('.js-add-external').on('click', function(){
            $('.js-add-external-form').toggleClass('d-none');
            return false;
        });
        $('.js-delete-external').on('click', function(){
            $(this).closest('form').submit();
            return false;
        });
        $('.js-rename-external').on('click', function(){
            let form = $('.js-rename-external-form');
            form.find('[name=ID]').val($(this).data('id'));
            form.find('[name=NAME]').val($(this).data('name'));
            form.toggleClass('d-none');
            return false;
        });
    });
</script>