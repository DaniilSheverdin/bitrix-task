<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

global $USER, $APPLICATION;

$APPLICATION->SetTitle('Заяви о себе');

\Bitrix\Main\UI\Extension::load([
    'ui.buttons',
    'ui.alerts',
    'ui.tooltip',
    'ui.hint',
    'ui.buttons.icons',
    'ui.dialogs.messagebox'
]);

CJSCore::Init(['date', 'ui']);

include $_SERVER['DOCUMENT_ROOT'] . '/local/components/citto/profile.personal/templates/.default/reference-lens.php';

?>

<script type="text/javascript" src="/bitrix/templates/.default/bootstrap.min.js"></script>
<link rel="stylesheet" type="text/css" href="/bitrix/templates/.default/bootstrap.min.css">

<form method="POST" enctype="multipart/form-data">
    <input class="send" type="hidden" name="do" value="send" />

    <div class="mb-3">
        <label for="name" class="form-label">ФИО:</label>
        <input class="form-control" name="name" id="name" value="<?=$_REQUEST['name']??$arResult['USER']['FIO'];?>" required>
    </div>

    <div class="mb-3">
        <label for="file" class="form-label">Выберите файл для загрузки:</label>
        <input class="form-control" type="file" name="file" id="file" required />
        <p>Прикрепите заполненную <a href="/local/components/citto/zayavi.current/templates/.default/Zayavi.docx" target="_blank" donload>анкету</a>, либо <a class="js-reference-lens" href="javascript:void(0)">справку-объективку</a></p>
    </div>

    <div class="mb-3">
        <label for="comment" class="form-label">Комментарий:</label>
        <textarea class="form-control" name="comment" id="comment" rows="3"><?=$_REQUEST['comment']?></textarea>
    </div>

    <h4 class="mt-4">Ответьте на вопросы:</h4>

    <div class="mb-3">
        <label for="post" class="form-label">Укажите уровень должности, который Вы готовы рассматривать:</label>
        <select class="form-control" name="post" id="post" required>
            <option>Выберите из списка</option>
            <?
            foreach ($arResult['ENUMS']['POST'] as $enum) {
                ?>
                <option value="<?=$enum['ID']?>" <?=($enum['ID']==$_REQUEST['post']?'selected':'')?>><?=$enum['VALUE']?></option>
                <?
            }
            ?>
        </select>
    </div>

    <div class="mb-3">
        <label for="activities" class="form-label">Укажите направления деятельности, которые Вас интересуют:</label>
        <select class="form-control" name="activities" id="activities" required>
            <option>Выберите из списка</option>
            <?
            foreach ($arResult['ENUMS']['ACTIVITIES'] as $enum) {
                ?>
                <option value="<?=$enum['ID']?>" <?=($enum['ID']==$_REQUEST['activities']?'selected':'')?>><?=$enum['VALUE']?></option>
                <?
            }
            ?>
        </select>
    </div>

    <div class="mb-3">
        <label for="income" class="form-label">Укажите минимально комфортный уровень дохода на который Вы претендуете:</label>
        <input class="form-control" type="text" name="income" id="income" value="<?=$_REQUEST['income']?>" required>
    </div>

    <div class="mb-3">
        <label for="date" class="form-label">Запись на тестирование:</label>
        <input class="form-control" type="date" name="date" id="date" value="<?=$_REQUEST['date']?>" required>
    </div>

    <div class="mb-3">
        <input type="submit" value="Отправить" class="btn btn-success" />
    </div>
</form>
