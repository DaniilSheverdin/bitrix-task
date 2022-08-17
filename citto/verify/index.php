<?
define('NO_MB_CHECK', true);
define('NOT_CHECK_PERMISSIONS', true);
define('SERVER_CRIPTOPRO', '172.21.254.50');

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
$APPLICATION->SetTitle("Верификация цифровой подписи");

$APPLICATION->SetAdditionalCss("/bitrix/css/main/bootstrap_v4/bootstrap.min.css");
?>

<? if($USER->IsAuthorized()): ?>
<?
    if (getenv("REQUEST_METHOD") == 'POST' && isset($_REQUEST['sign']['verif-sign'])) {
        if(!empty($_FILES['sign']['tmp_name']['file'])) {
            $sign = file_get_contents($_FILES['sign']['tmp_name']['file']);
        } elseif($_REQUEST['sign']['text']) {
            $sign = trim($_REQUEST['sign']['text']);
        }

        if(!empty($sign)) {
            $url = "http://".SERVER_CRIPTOPRO."/uverify.php";
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, ['sign' => $sign]);
            $returned = curl_exec($ch);
            $errors = curl_error($ch);
            curl_close($ch);

            if(empty($errors)) {
                $dataSert = json_decode($returned, true);
                ?>

                <div class="row">
                    <div class="col-lg-9 col-md-12 my-2 mx-auto">
                        <div class="panel panel-default">
                            <div class="panel-body">
                                <div class="col-xl-12 p-3 bg-info text-white">
                                <? foreach($dataSert as $ksert => $serInfo) {
                                    if ($ksert == 'data') {
                                        if($serInfo['verify'] == 0) {
                                            ?>
                                            <p class="task-message-label error">
                                                <?=$serInfo['verifyMessage']?>.
                                                <?=($serInfo['signersMessage']) ? $serInfo['signersMessage'].'.' : ''?>
                                            </p>
                                            <?
                                        } else if (count($serInfo['signers']) > 0) {
                                            ?>
                                            <? foreach($serInfo['signers'] as $ksig => $signing): ?>
                                            <p><strong>Дата подписания:</strong>&nbsp;<?=$signing['signingTime']?></p>
                                            <p><strong>Дата окончания действия:</strong>&nbsp;<?=$signing['cert']['validToDate']?></p>
                                            <p><strong>Дата начала действия:</strong>&nbsp;<?=$signing['cert']['validFromDate']?></p>
                                            <p class="mt-4"><strong>Кому выдан:</strong></p>
                                            <p><strong>СНИЛС:</strong>&nbsp;<?=$signing['cert']['subjectName']['SNILS']?></p>
                                            <p><strong>ОГРН:</strong>&nbsp;<?=$signing['cert']['subjectName']['OGRN']?></p>
                                            <p><strong>ИНН:</strong>&nbsp;<?=$signing['cert']['subjectName']['INN']?></p>
                                            <p><strong>ИО:</strong>&nbsp;<?=$signing['cert']['subjectName']['G']?></p>
                                            <p><strong>Фамилия:</strong>&nbsp;<?=$signing['cert']['subjectName']['SN']?></p>
                                            <p><strong>E-mail:</strong>&nbsp;<?=$signing['cert']['subjectName']['E']?></p>
                                            <p><strong>Город:</strong>&nbsp;<?=$signing['cert']['subjectName']['L']?></p>
                                            <p><strong>Регион:</strong>&nbsp;<?=$signing['cert']['subjectName']['S']?></p>
                                            <p><strong>Страна:</strong>&nbsp;<?=$signing['cert']['subjectName']['C']?></p>
                                            <p><strong>Должность:</strong>&nbsp;<?=$signing['cert']['subjectName']['T']?></p>
                                            <p><strong>Организация:</strong>&nbsp;<?=$signing['cert']['subjectName']['O']?></p>
                                            <p><strong>Имя сертификата (CN):</strong>&nbsp;<?=$signing['cert']['subjectName']['CN']?></p>
                                            <p class="mt-4"><strong>Кем выдан:</strong></p>
                                            <p><strong>ОГРН:</strong>&nbsp;<?=$signing['cert']['issuerName']['OGRN']?></p>
                                            <p><strong>ИНН:</strong>&nbsp;<?=$signing['cert']['issuerName']['INN']?></p>
                                            <p><strong>Улица:</strong>&nbsp;<?=$signing['cert']['issuerName']['STREET']?></p>
                                            <p><strong>E-mail:</strong>&nbsp;<?=$signing['cert']['issuerName']['E']?></p>
                                            <p><strong>Город:</strong>&nbsp;<?=$signing['cert']['issuerName']['L']?></p>
                                            <p><strong>Регион:</strong>&nbsp;<?=$signing['cert']['issuerName']['S']?></p>
                                            <p><strong>Страна:</strong>&nbsp;<?=$signing['cert']['issuerName']['C']?></p>
                                            <p><strong>Тип:</strong>&nbsp;<?=$signing['cert']['issuerName']['OU']?></p>
                                            <p><strong>Организация:</strong>&nbsp;<?=$signing['cert']['issuerName']['O']?></p>
                                            <p><strong>Идентификатор CN:</strong>&nbsp;<?=$signing['cert']['issuerName']['CN']?></p>
                                            <p class="mt-4"><strong>Серийный номер:</strong>&nbsp;<?=$signing['cert']['certSerial']?></p>
                                            <hr />
                                            <? endforeach; ?>
                                            <?
                                        }
                                    }
                                } ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?
            } else { ?>
                <div class="row">
                    <div class="col-lg-9 col-md-12 my-2 mx-auto">
                        <div class="panel panel-default">
                            <div class="panel-body">
                                <div class="col-xl-12">
                                <p class="task-message-label error">
                                    Ошибка CURL: <?=$errors?>
                                </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <? }
        }
    }
    ?>
    <div class="row">
        <div class="col-lg-9 col-md-12 my-2 mx-auto">
            <div class="panel panel-default">
                <div class="panel-body">
                    <div class="col-xl-12 task-detail-content">Верификация подписи. Вставьте содержимое ЭЦП или загрузите файл <mark>[filename].sig</mark></div>
                    <form id="verif-sign" action="<?= $APPLICATION->GetCurPage() ?>" method="post"
                          enctype="multipart/form-data" class="form-horizontal">
                        <div class="form-group d-md-flex py-2">
                            <div class="col-md-3 col-xl-2"><label for="sign-text">Содержимое ЭЦП:</label></div>
                            <div class="col-md-9 col-xl-10">
                                <textarea placeholder="Содержимое ЭЦП" name="sign[text]" id="sign-text"
                                          class="form-control"></textarea>
                            </div>
                        </div>
                        <div class="form-group d-md-flex py-2">
                            <div class="col-md-3 col-xl-2"><label for="sign-file">Файл ЭЦП:</label></div>
                            <div class="col-md-9 col-xl-10">
                                <input type="file" placeholder="Файл ЭЦП" name="sign[file]" value="" id="sign-file">
                            </div>
                        </div>
                        <div class="form-group text-center">
                            <input type="hidden" name="sign[verif-sign]" value="1"/>
                            <button type="submit" class="ui-btn ui-btn-success">Верификация</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <? else: ?>
       <? LocalRedirect('/'); ?>
       <p class="alert alert-danger">К данному адресу доступ без авторизации ограничен...</p>
    <? endif; ?>
<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>