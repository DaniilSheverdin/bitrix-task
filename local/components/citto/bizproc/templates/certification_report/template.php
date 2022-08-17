<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}
?>
<form enctype="multipart/form-data" class="needs-validation js-certification_report" id="js-certification_report" action="<?= htmlspecialchars($_SERVER['REQUEST_URI']) ?>"
      method="POST" autocomplete="Off">
    <input type="hidden" name="certification_report" value="add">
    <?= bitrix_sessid_post() ?>
    <div class="alert d-none"></div>
    <div id="js--form-action-content" class="show">
        <? foreach ($arResult['FIELDS'] as $arField): ?>
            <div class="form-group row"
                <? if ($arField['SHOW'] == 'N'): ?>
                    style="display: none"
                <? endif; ?>
            >
                <label class="col-sm-4 col-form-label" for="bp_<?= $arField['CODE'] ?>"><?= $arField['NAME'] ?>
                    <? if ($arField['IS_REQUIRED'] == 'Y'): ?>
                        <span class="text-danger">*</span>
                    <? endif; ?>
                </label>
                <div class="col-sm-8 py-2">
                    <? if ($arField['TYPE'] == 'EMPLOYEE'): ?>
                        <select
                            <? if ($arField['IS_REQUIRED'] == 'Y'): ?>
                                required="required"
                            <? endif; ?>
                                class="selectpicker form-control"
                                data-dropup-auto="false" data-size="5" title="Выбрать"
                                data-live-search="true" data-live-search-placeholder="Найти"
                                name="<?= $arField['CODE'] ?>" id="bp_<?= $arField['CODE'] ?>"
                        >
                            <? foreach ($arResult['USERS'] as $arUser) { ?>
                                <option value="<?= $arUser['ID'] ?>"><?= $arUser['USER_INFO'] ?></option>
                            <? } ?>
                        </select>
                    <? elseif ($arField['TYPE'] == 'STRING'): ?>
                        <? if ($arField['CODE'] == 'REVIEW'): ?>
                            <select id="select-reiew" class="selectpicker form-control" multiple="multiple">
                                <option>в пределах своей компетенции принимает конструктивные решения и несет ответственность за их реализацию;</option>
                                <option>проявляет принципиальность, требовательность;</option>
                                <option>проявляет себя как способный организатор, обладающий хорошими навыками деловой и творческой коммуникации;</option>
                                <option>оптимально использует технические возможности и ресурсы для обеспечения эффективности и результативности служебной деятельности;</option>
                                <option>ориентирован(а) на достижение результата;</option>
                                <option>умеет организовать свой труд, планировать рабочее время;</option>
                                <option>качественно и своевременно готовит служебные документы;</option>
                                <option>владеет навыками делового общения;</option>
                                <option>за время работы зарекомендовал(а) себя как надежный, ответственный сотрудник, способный оперативно и грамотно принимать решения, выполнять порученные задания в установленные сроки;</option>
                                <option>проявляет самостоятельность при исполнении обязанностей, умение применять новые подходы в решении возникающих проблем, способность адаптироваться в новой ситуации и рационально применять имеющиеся профессиональные знания и опыт;</option>
                                <option>обладает соответствующими знаниями федерального и регионального законодательства;</option>
                                <option>обладает высокой работоспособностью и мотивацией;</option>
                                <option>самостоятельно принимает необходимое решение по служебным вопросам и берет на себя ответственность за принятые решения и действия;</option>
                                <option>владеет персональным компьютером на уровне опытного пользователя, уверенно работает во всех программных продуктах, необходимых для данной деятельности, способен(на) быстро осваивать и пользоваться новыми программными продуктами;</option>
                                <option>поддерживает необходимый профессиональный уровень для выполнения своих должностных обязанностей, умело применяет полученные знания в практической деятельности, обладает высоким уровнем исполнительской дисциплины и ответственности;</option>
                            </select>
                        <? endif; ?>
                        <? if ($arField['CODE'] == 'PENALTIES'): ?>
                            <select id="select-penalties" class="selectpicker form-control">
                                <option>Не имеет</option>
                                <option>имеет дисциплинарное взыскание за неисполнение обязанностей, установленных в целях противодействия коррупции Федеральным законом от 27 июля 2004 года No 79-ФЗ «О государственной гражданской службе Российской Федерации», Федеральным законом от 25 декабря 2008 года № 273-ФЗ «О противодействии коррупции» </option>
                                <option>имеет дисциплинарное взыскание за ненадлежащее исполнение возложенных должностных обязанностей</option>
                            </select>
                        <? endif; ?>
                        <? if ($arField['CODE'] == 'TEXT_QUESTIONS' || $arField['CODE'] == 'INFO_ORGANISATOR'): ?>
                            <textarea
                                <? if ($arField['IS_REQUIRED'] == 'Y'): ?>
                                    required="required"
                                <? endif; ?>
                                    cols="30" rows="3"
                                    class="form-control" type="text" name="<?= $arField['CODE'] ?>" id="bp_<?= $arField['CODE'] ?>"
                            ></textarea>
                        <? else: ?>
                            <input
                                <? if ($arField['IS_REQUIRED'] == 'Y'): ?>
                                    required="required"
                                <? endif; ?>
                                    class="form-control" type="text" name="<?= $arField['CODE'] ?>" id="bp_<?= $arField['CODE'] ?>"
                                <? if ($arField['CODE'] == 'PENALTIES'): ?>
                                    placeholder="Номер приказа (если есть)"
                                <? endif; ?>
                            />

                            <? if ($arField['CODE'] == 'PENALTIES'): ?>
                                <input class="form-control" type="text" id="penalties_date" placeholder="Дата приказа (если есть)">
                            <? endif; ?>
                            <? if ($arField['CODE'] == 'RECOMMENDED_RATING'): ?>
                                <div class="form-check">
                                    <input class="form-check-input rank" type="checkbox">
                                    <label class="form-check-label">
                                        Соответствует замещаемой должности гражданской службы
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input rank" type="checkbox">
                                    <label class="form-check-label">
                                        Не соответствует замещаемой должности гражданской службы
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input rank" type="checkbox">
                                    <label class="form-check-label">
                                        Соответствует замещаемой должности гражданской службы при условии получения дополнительного профессионального образования
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input rank" type="checkbox">
                                    <label class="form-check-label">
                                        Соответствует замещаемой должности гражданской службы и рекомендуется к включению в кадровый резерв для замещения вакантной должности гражданской службы в порядке должностного роста
                                    </label>
                                </div>


                            <? endif; ?>
                        <? endif; ?>
                    <? elseif ($arField['TYPE'] == 'NUMBER'): ?>
                        <input
                            <? if ($arField['IS_REQUIRED'] == 'Y'): ?>
                                required="required"
                            <? endif; ?>
                                class="form-control" type="number" name="<?= $arField['CODE'] ?>" id="bp_<?= $arField['CODE'] ?>"
                        />
                    <? elseif ($arField['TYPE'] == 'LIST'): ?>
                        <select class="selectpicker form-control"
                            <? if ($arField['IS_REQUIRED'] == 'Y'): ?>
                                required="required"
                            <? endif; ?>
                                data-dropup-auto="false" data-size="5" title="Выбрать"
                                data-live-search="true" data-live-search-placeholder="Найти"
                                name="<?= $arField['CODE'] ?>" id="bp_<?= $arField['CODE'] ?>"
                        >
                            <? foreach ($arField['ENUMS'] as $arEnum) { ?>
                                <option value="<?= $arEnum['ID'] ?>"><?= $arEnum['VALUE'] ?></option>
                            <? } ?>
                        </select>
                    <? elseif ($arField['TYPE'] == 'FILE'): ?>
                        <input
                            <? if ($arField['IS_REQUIRED'] == 'Y'): ?>
                                required="required"
                            <? endif; ?>
                                class="form-control"
                                type="file"
                            <? if ($arField['MULTIPLE'] == 'Y'): ?>
                                name="<?= $arField['CODE'] ?>[]"
                                multiple
                            <? else: ?>
                                name="<?= $arField['CODE'] ?>"
                            <? endif; ?>
                                value="" id="bp_<?= $arField['CODE'] ?>"
                        />
                    <? elseif ($arField['TYPE'] == 'DATE'): ?>
                        <input
                            <? if ($arField['IS_REQUIRED'] == 'Y'): ?>
                                required="required"
                            <? endif; ?>
                                class="form-control"
                                type="date"
                                name="<?= $arField['CODE'] ?>"
                                value="" id="bp_<?= $arField['CODE'] ?>"
                        />
                    <? endif; ?>
                </div>
            </div>
        <? endforeach; ?>
        <div class="form-group row">
            <label class="col-sm-4 col-form-label">Отчеты о профессиональной служебной деятельности подлежащего аттестации служащего</label>
            <div class="col-sm-8 py-2 js-documets">
                <div class="row">
                    <div class="col-md-5">
                        <select class="form-control" name="YEARS[]">
                            <?
                            $iYear = date('Y');
                            for ($x = $iYear; $x >= $iYear - 5; $x--) { ?>
                                <option value="<?= $x ?>"><?= $x ?></option>
                                <?
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-5">
                        <input class="form-control" type="file" name="DOCUMENTS[]">
                    </div>
                    <div class="col-md-2">
                        <a class="btn btn-primary btn-block js-add" href="#">Добавить</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12 col-sm-2">
                <div class="text-right mb-3">
                    <button class="btn btn-primary btn-block" name="SUBMIT" type="submit">Далее &rarr;</button>
                </div>
            </div>
            <div class="col-12 col-sm-11">
            </div>
        </div>
    </div>
</form>
