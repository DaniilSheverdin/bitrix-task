<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) {
    die();
}
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */
$this->setFrameMode(true);

switch ($_REQUEST['action']) {
    case 'register':
        if ($_REQUEST['i'] == 'other') {
            ?>
            <div class="log-popup-header">Регистрация внешней организации</div>
            <hr class="b_line_gray">
            <form name="form_register" method="post">
                <div class="">
                    <div class="login-item col-4 my-2">
                        <!--[if IE]><span class="login-label">Имя</span><![endif]-->
                        <input class="login-inp form-control" type="text" name="USER_NAME" placeholder="Имя" value="" maxlength="255">
                    </div>
                    <div class="login-item col-4 my-2">
                        <!--[if IE]><span class="login-label">Логин</span><![endif]-->
                        <input class="login-inp form-control" type="text" name="USER_LOGIN" placeholder="Логин" value="" maxlength="255">
                    </div>
                    <div class="login-item col-4 my-2">
                        <!--[if IE]><span class="login-label">Пароль</span><![endif]-->
                        <input class="login-inp form-control" type="password" name="USER_PASSWORD" placeholder="Пароль" maxlength="255">
                    </div>
                    
                </div>
                <div class="log-popup-footer col-4 my-2">
                    <input type="submit" name="submit" value="Регистрация" class="login-btn btn btn-success">
                </div>
            </form>
            <?php
        } elseif ((int)$_REQUEST['i'] > 0 && $arResult['DEPARTMENTS'][ $_REQUEST['PARENT'] ][ $_REQUEST['i'] ]['NAME'] != '') {
            if ($arResult['STATUS'] != 'success') {
                ?>
                <h4 class="log-popup-header"><?=$arResult['DEPARTMENTS'][ $_REQUEST['PARENT'] ][ $_REQUEST['i'] ]['NAME']?></h4>
                <hr class="b_line_gray">
                <?if ($arResult['STATUS'] == 'error') {?>
                    <b>Ошибка!</b><br>
                    <?=$arResult['MESSAGE']?>
                <?}?>
                <form name="form_register" method="post">
                    <div class="">
                        <div class="login-item col-4 my-2">
                            <!--[if IE]><span class="login-label">Фамилия</span><![endif]-->
                            <input class="login-inp form-control" type="text" required="" name="LAST_NAME" placeholder="Фамилия *" value="<?=$_REQUEST['LAST_NAME']?>" maxlength="255">
                        </div>
                        <div class="login-item col-4 my-2">
                            <!--[if IE]><span class="login-label">Имя</span><![endif]-->
                            <input class="login-inp form-control" type="text" required="" name="NAME" placeholder="Имя *" value="<?=$_REQUEST['NAME']?>" maxlength="255">
                        </div>
                        <div class="login-item col-4 my-2">
                            <!--[if IE]><span class="login-label">Отчество</span><![endif]-->
                            <input class="login-inp form-control" type="text" name="SECOND_NAME" placeholder="Отчество" value="<?=$_REQUEST['SECOND_NAME']?>" maxlength="255">
                        </div>
                        <div class="login-item col-4 my-2">
                            <!--[if IE]><span class="login-label">Должность</span><![endif]-->
                            <input class="login-inp form-control" type="text" name="WORK_POSITION" placeholder="Должность" value="<?=$_REQUEST['WORK_POSITION']?>" maxlength="255">
                        </div>
                        <div class="login-item col-4 my-2">
                            <!--[if IE]><span class="login-label">Отчество</span><![endif]-->
                            <input class="login-inp form-control" type="email" required="" name="LOGIN" placeholder="Электронная почта *" value="<?=$_REQUEST['LOGIN']?>" maxlength="255">
                        </div>
                        <div class="login-item col-4 my-2">
                            <!--[if IE]><span class="login-label">Пароль</span><![endif]-->
                            <input class="login-inp form-control" type="password" required="" name="PASSWORD" placeholder="Пароль *" maxlength="255">
                        </div>
                        <div class="login-item col-4 my-2">
                            <!--[if IE]><span class="login-label">Пароль</span><![endif]-->
                            <input class="login-inp form-control" type="password" required="" name="CONFIRM_PASSWORD" placeholder="Повторите пароль *" maxlength="255">
                        </div>
                        
                    </div>
                    <div class="log-popup-footer col-4 mt-4">
                        <input type="submit" name="submit" value="Регистрация" class="login-btn btn btn-success">
                    </div>
                </form>
                <?php
            } else {
                ?>
                <div class="log-popup-header"><?=$arResult['DEPARTMENTS'][ $_REQUEST['PARENT'] ][ $_REQUEST['i'] ]['NAME']?></div>
                <hr class="b_line_gray">
                <?=$arResult['MESSAGE']?>
                <?php
            }
        } else {
            ?>
        <div class="log-popup-header">Ошибка!</div>
            Не передан исполнитель.<br>
            Обратитесь к администратору для получения ссылки
            <?php
        }
        break;
    
    default:
        if ($USER->IsAdmin()) {
            foreach ($arResult['DEPARTMENTS'] as $parent => $dep) {
                $arParent = CIBlockSection::GetByID($parent)->Fetch();
                ?>
                <h4><?=$arParent['NAME']?></h4>
                <table class="table">
                    <thead>
                        <th>Наименование</th>
                        <th>Исполнитель</th>
                        <th>Заместитель</th>
                        <th>Ответвенный исполнитель</th>
                    </thead>
                    <tbody>
                    <?
                    foreach ($dep as $sKey => $arValue) {
                        $link = 'https://corp.tularegion.local/reguser/?action=register&PARENT=' . $parent . '&i=' . $sKey;
                        ?>
                        <tr>
                            <td><?=$arValue['NAME']?></td>
                            <td><a href='<?=$link?>'><?=$link?></a></td>
                            <td><a href='<?=$link?>&subaction=deputy'><?=$link?>&subaction=deputy</a></td>
                            <td><a href='<?=$link?>&subaction=introduction'><?=$link?>&subaction=introduction</a></td>
                        </tr>
                        <?
                    }
                    ?>
                    </tbody>
                </table>
                <?
            }
        } else {
            ?>
            <div class="log-popup-header">Ошибка!</div>
            Не передан исполнитель.<br>
            Обратитесь к администратору для получения ссылки
            <?php
        }
        break;
}
