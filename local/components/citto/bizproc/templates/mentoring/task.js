function addContentToField(obField, sContent) {
    obField.val(sContent);
}

function prepHtmlContent(sContent) {
    return sContent.replace(/\r?\n/g, "");
}

function hideField(ob) {
    ob.parent().parent().hide();
}

$('body').on('click', '.delete-row', function (e) {
    e.preventDefault();
    $(this).parent().parent().detach();
});

/*Все select, checkbox, textarea заменяем на обычный текст. Нужно для того, чтоб в документе не было селектов*/
function elemFormToText() {
    $('select').each(function () {
        sValue = ($(this).find('option:selected').text());
        $(this).parent().append(sValue);
        $(this).detach();
    });

    $('[type="checkbox"]').each(function () {
        sID = $(this).attr('id');
        obLabel = $(`[for="${sID}"]`);

        if ($(this).is(':checked')) {
            sValue = obLabel.text();
            $(`<span>${sValue}</span>`).insertBefore(`#${sID}`);
        }

        $(this).detach();
        $(`[for="${sID}"]`).detach();
    });

    $('textarea').each(function () {
        sValue = $(this).val();
        $(`<div>${sValue}</div>`).insertBefore($(this));
        $(this).detach();
    });
}

/*План наставничества*/
$('body').ready(function () {
    let sPlanContent = `<form id='mentoring-plan' class='table table-striped bg-light'>
        <table border="1">
            <thead>
                <tr>
                    <td>№ п/п</td>
                    <td>Наименование и содержание мероприятий</td>
                    <td>Период выполнения</td>
                    <td>Ответственный за выполнение</td>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><textarea class = 'form-control'>1</textarea><a href='#' class="delete-row">удалить</a></td>
                    <td>Представление лица, в отношении которого осуществляется наставничество, коллективу</td>
                    <td><textarea class = 'form-control'></textarea></td>
                    <td><textarea class = 'form-control'>Руководитель структурного подразделения</textarea></td>
                </tr>
                <tr>
                    <td><textarea class = 'form-control'>2</textarea><a href='#' class="delete-row">удалить</a></td>
                    <td>Ознакомление лица, в отношении которого осуществляется наставничество, с рабочим местом, его дооборудование (дооснащение)</td>
                    <td><textarea class = 'form-control'></textarea></td>
                    <td><textarea class = 'form-control'>Наставник</textarea></td>
                </tr>
                <tr>
                    <td><textarea class = 'form-control'>3</textarea><a href='#' class="delete-row">удалить</a></td>
                    <td>Ознакомление со структурным подразделением, его полномочиями, задачами, особенностями службы</td>
                    <td><textarea class = 'form-control'></textarea></td>
                    <td><textarea class = 'form-control'>Наставник</textarea></td>
                </tr>
                <tr>
                    <td><textarea class = 'form-control'>4</textarea><a href='#' class="delete-row">удалить</a></td>
                    <td>Ознакомление с историей создания государственного органа, его традициями</td>
                    <td><textarea class = 'form-control'></textarea></td>
                    <td><textarea class = 'form-control'>Наставник</textarea></td>
                </tr>
                <tr>
                    <td><textarea class = 'form-control'>5</textarea><a href='#' class="delete-row">удалить</a></td>
                    <td>Представление справочной информации</td>
                    <td><textarea class = 'form-control'></textarea></td>
                    <td><textarea class = 'form-control'>Наставник</textarea></td>
                </tr>
                <tr>
                    <td><textarea class = 'form-control'>6</textarea><a href='#' class="delete-row">удалить</a></td>
                    <td>Ознакомление с должностным регламентом (инструкцией) </td>
                    <td><textarea class = 'form-control'></textarea></td>
                    <td><textarea class = 'form-control'>Наставник</textarea></td>
                </tr>
                <tr>
                    <td><textarea class = 'form-control'>7</textarea><a href='#' class="delete-row">удалить</a></td>
                    <td>Ознакомление лица, в отношении которого осуществляется наставничество, с показателями эффективности</td>
                    <td><textarea class = 'form-control'></textarea></td>
                    <td><textarea class = 'form-control'>Наставник</textarea></td>
                </tr>
                <tr>
                    <td><textarea class = 'form-control'>8</textarea><a href='#' class="delete-row">удалить</a></td>
                    <td>Ознакомление с административными процедурами и системой документооборота</td>
                    <td><textarea class = 'form-control'></textarea></td>
                    <td><textarea class = 'form-control'>Наставник</textarea></td>
                </tr>
                <tr>
                    <td><textarea class = 'form-control'>9</textarea><a href='#' class="delete-row">удалить</a></td>
                    <td>Ознакомление с используемыми программными продуктами</td>
                    <td><textarea class = 'form-control'></textarea></td>
                    <td><textarea class = 'form-control'>Наставник</textarea></td>
                </tr>
                <tr>
                    <td><textarea class = 'form-control'>10</textarea><a href='#' class="delete-row">удалить</a></td>
                    <td>Ознакомление с планами, целями и задачами государственного органа и структурного подразделения</td>
                    <td><textarea class = 'form-control'></textarea></td>
                    <td><textarea class = 'form-control'>Наставник</textarea></td>
                </tr>
                <tr>
                    <td><textarea class = 'form-control'>11</textarea><a href='#' class="delete-row">удалить</a></td>
                    <td>Совместная постановка профессиональных целей и задач, разработка планов их достижения</td>
                    <td><textarea class = 'form-control'></textarea></td>
                    <td><textarea class = 'form-control'>Наставник</textarea></td>
                </tr>
                <tr>
                    <td><textarea class = 'form-control'>12</textarea><a href='#' class="delete-row">удалить</a></td>
                    <td>Организация изучения нормативной правовой базы по вопросам исполнения должностных обязанностей</td>
                    <td><textarea class = 'form-control'></textarea></td>
                    <td><textarea class = 'form-control'>Наставник</textarea></td>
                </tr>
                <tr>
                    <td><textarea class = 'form-control'>13</textarea><a href='#' class="delete-row">удалить</a></td>
                    <td>Ознакомление с порядком и особенностями ведения служебной документации</td>
                    <td><textarea class = 'form-control'></textarea></td>
                    <td><textarea class = 'form-control'>Наставник</textarea></td>
                </tr>
                <tr>
                    <td><textarea class = 'form-control'>14</textarea><a href='#' class="delete-row">удалить</a></td>
                    <td>Ознакомление с деятельностью подведомственных учреждений (при наличии)</td>
                    <td><textarea class = 'form-control'></textarea></td>
                    <td><textarea class = 'form-control'>Наставник</textarea></td>
                </tr>
                <tr>
                    <td><textarea class = 'form-control'>15</textarea><a href='#' class="delete-row">удалить</a></td>
                    <td>Контроль выполнения практических заданий (ответы на обращения граждан, подготовка писем и т.д.)</td>
                    <td><textarea class = 'form-control'></textarea></td>
                    <td><textarea class = 'form-control'>Наставник</textarea></td>
                </tr>
                <tr>
                    <td><textarea class = 'form-control'>16</textarea><a href='#' class="delete-row">удалить</a></td>
                    <td>Проверка знаний и навыков, приобретенных за месяц</td>
                    <td><textarea class = 'form-control'></textarea></td>
                    <td><textarea class = 'form-control'>Наставник</textarea></td>
                </tr>
                <tr>
                    <td><textarea class = 'form-control'>17</textarea><a href='#' class="delete-row">удалить</a></td>
                    <td>Индивидуальное собеседование с лицом, в отношении которого осуществлялось наставничество </td>
                    <td><textarea class = 'form-control'></textarea></td>
                    <td><textarea class = 'form-control'>Руководитель государственного органа</textarea></td>
                </tr>
                <tr>
                    <td><textarea class = 'form-control'>18</textarea><a href='#' class="delete-row">удалить</a></td>
                    <td>Определение индивидуальной траектории обучения (Лист адаптации)</td>
                    <td><textarea class = 'form-control'></textarea></td>
                    <td><textarea class = 'form-control'>Руководитель государственного органа</textarea></td>
                </tr>
                <tr>
                    <td><textarea class = 'form-control'>19</textarea><a href='#' class="delete-row">удалить</a></td>
                    <td>Ознакомление с ключевыми показателями эффективности</td>
                    <td><textarea class = 'form-control'></textarea></td>
                    <td><textarea class = 'form-control'></textarea></td>
                </tr>
                <tr>
                    <td><textarea class = 'form-control'>20</textarea><a href='#' class="delete-row">удалить</a></td>
                    <td>Отраслевая специфика</td>
                    <td><textarea class = 'form-control'></textarea></td>
                    <td><textarea class = 'form-control'></textarea></td>
                </tr>
            </tbody>
        </table>
    </form>
    
    <form id='mentoring-plan-new' class='table table-striped bg-light'>
        <textarea id="number_sting" class = 'form-control mb-1' placeholder='№ п/п'></textarea>
        <textarea id="events_sting" class = 'form-control mb-1' placeholder='Наименование и содержание мероприятий'></textarea>
        <textarea id="period_sting" class = 'form-control mb-1' placeholder='Период выполнения'></textarea>
        <textarea id="person_sting" class = 'form-control mb-1' placeholder='Ответственный за выполнение'></textarea>
    </form>
    <a href='#' class='bp-button bp-button bp-button-accept' id='add-plan'>Добавить новую строку</a>
    <a href='#' class='bp-button bp-button bp-button-decline' id='delete-plan'>Удалить все строки</a>
    <div class="mb-5"></div>
    
    <style>
    #mentoring-plan tbody td:nth-child(2) {
        width: 40%;
    }
    #mentoring-plan tbody td:nth-child(1) {
        width: 5%;
        white-space: nowrap;
    }
    .delete-row {
        color: red;
        font-weight: 700;
        width: 100%;
        display: block;
    }
    table,tr,td {
        border: unset !important;
    }
    </style>
    `;

    function addNewPlanString() {
        let sTr = `<tr>
                    <td><textarea class = 'form-control'>${$('#number_sting').val()}</textarea><a href='#' class="delete-row">удалить</a></td>
                    <td>${$('#events_sting').val()}</td>
                    <td><textarea class = 'form-control'>${$('#period_sting').val()}</textarea></td>
                    <td><textarea class = 'form-control'>${$('#person_sting').val()}</textarea></td>
                </tr>`;

        $('#mentoring-plan tbody').append(sTr);
        $('#mentoring-plan-new')[0].reset();
    }

    obFieldMentoringPlan = $("[name = 'bpriact_MENTORING_PLAN']");
    obFieldFeedbackEmployee = $("[name = 'bpriact_FEEDBACK_EMPLOYEE']");
    if (obFieldMentoringPlan.length > 0) {
        hideField(obFieldMentoringPlan);
        $(prepHtmlContent(sPlanContent)).insertBefore(".bizproc-table-main");
    }

    $('body').on('click', '#add-plan', function (e) {
        e.preventDefault();
        addNewPlanString();
    });

    $('body').on('click', '#delete-plan', function (e) {
        e.preventDefault();
        $('#mentoring-plan tbody tr').detach();
    });

    $('body').on('click', '.delete-row', function (e) {
        e.preventDefault();
        $(this).parent().parent().detach();
    });

    $('body').on('click', '[name="approve"]', function () {
        if (obFieldMentoringPlan.length > 0) {
            elemFormToText();
            addContentToField(obFieldMentoringPlan, $('#mentoring-plan').html());
        }
    })
})

/*Отзыв наставника*/
$('body').ready(function () {
    function buildFeedBackForm() {
        let sSelect = "";
        for (let i = 1; i <= 10; i++) {
            sSelect += `<option value="${i}">${i}</option>`
        }
        sSelect = `<select class="scores">${sSelect}<select>`;

        let sFeedbackEmployeeContent = `<form id='feedback-employee' class='table table-striped bg-light'>
            <table border="1">
                <thead>
                    <tr>
                        <td>Вопрос</td>
                        <td>Оценка</td>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1. Достаточно ли было времени, проведенного с Вами наставником, для получения необходимых знаний и умений?</td>
                        <td>${sSelect}</td>
                    </tr>
                    <tr>
                        <td>2. Как бы Вы оценили требовательность наставника?</td>
                        <td>${sSelect}</td>
                    </tr>
                    <tr>
                        <td>3. Насколько полезными в работе оказались полученныев ходе наставничества теоретические знания по Вашей специализации?</td>
                        <td>${sSelect}</td>
                    </tr>
                    <tr>
                        <td>4. Насколько полезными в работе оказались полученныев ходе наставничества практические навыки по Вашей должности?</td>
                        <td>${sSelect}</td>
                    </tr>
                    <tr>
                        <td>5. Насколько быстро Вам позволили освоиться на новомместе работы знания об истории, культуре, принятых нормах и процедурах работы внутри государственного органа?</td>
                        <td>${sSelect}</td>
                    </tr>
                    <tr>
                        <td>6. Являются ли полученные в ходе наставничества знания и умения достаточными для самостоятельного выполнения обязанностей, предусмотренных Вашей должностью?</td>
                        <td>${sSelect}</td>
                    </tr>
                    <tr>
                        <td>7. Расставьте баллы от 1 до 10 для каждого из методов при оценке времени, потраченного наставником на различные способы обучения при работе с Вами (1 - метод почти не использовался, 10 - максимальные затраты времени)</td>
                        <td></td>
                    </tr>
                    
                    <tr>
                        <td>7.1. В основном самостоятельное изучение материалов и выполнение заданий, ответы наставника на возникающие вопросы по электроннойпочте</td>
                        <td>${sSelect}</td>
                    </tr>
                    <tr>
                        <td>7.2. В основном самостоятельное изучение материалов и выполнение заданий, ответы наставника на возникающие вопросы по телефону</td>
                        <td>${sSelect}</td>
                    </tr>
                    <tr>
                        <td>7.3. Личные консультации в заранее определенное время</td>
                        <td>${sSelect}</td>
                    </tr>
                    <tr>
                        <td>7.4. Личные консультации по мере возникновения необходимости</td>
                        <td>${sSelect}</td>
                    </tr>
                    <tr>
                        <td>7.5. Поэтапный совместный разбор практических заданий</td>
                        <td>${sSelect}</td>
                    </tr>
                    <tr>
                        <td>Средний балл:</td>
                        <td id="middle-value"></td>
                    </tr>
                </tbody>
            </table>
    
            <div class="mt-5 bp-task-block-title">Оцените работу наставника:
                <select class = 'form-control' name="score">
                    <option value="5">Отлично</option>
                    <option value="4">Хорошо</option>
                    <option value="3">Удовлетворительно</option>
                    <option value="2">Неудовлетворительно</option>
                </select>
            </div>
            <textarea class = 'form-control' name="score-comment" style="display: none"></textarea>
            
            <p class="mt-3">8.  Какой  из  перечисленных  или  иных  использованных методов обучения Вы считаете наиболее эффективным и почему?</p>
            <textarea class = 'form-control'></textarea>
            <p class="mt-3">9.  Какие  наиболее  важные,  на  Ваш взгляд, знания и умения для успешного выполнения   должностных   обязанностей   Вам   удалось  освоить  благодаря прохождению наставничества?</p>
            <textarea class = 'form-control'></textarea>
            <p class="mt-3">10.  Кто  из  коллег  Вашего отдела, кроме наставника, особенно помог Вам в период адаптации?</p>
            <textarea class = 'form-control'></textarea>
            <p class="mt-3">11. Какой из аспектов адаптации показался Вам наиболее сложным?</p>
            <textarea class = 'form-control'></textarea>
            <p class="mt-3">12.  Кратко  опишите  Ваши  предложения  и  общие  впечатления  от работы с наставником:</p>
            <textarea class = 'form-control'></textarea>
        </form>
        <div class="mb-5"></div>
        <style>
        #feedback-employee tbody td:nth-child(2) {
            width: 10%;
        }
        #feedback-employee tbody td:nth-child(1) {
            width: 90%;
        }
        table,tr,td {
            border: unset !important;
        }
        </style>
        `;

        return sFeedbackEmployeeContent;
    }

    function countingMiddle() {
        let iSumScores = 0;
        $('.scores').each(function () {
            iSumScores += parseInt($(this).val())
        });
        let iMiddle = iSumScores / $('.scores').length;
        iMiddle = Math.round(iMiddle);
        $('#middle-value').text(iMiddle);
    }

    function scoreWorkMentor() {
        let iScore = parseInt($('[name="score"]').val());
        if (iScore < 4) {
            $('[name="score-comment"]').show();
        } else {
            $('[name="score-comment"]').hide();
        }
    }

    obFieldFeedbackEmployee = $("[name = 'bpriact_FEEDBACK_EMPLOYEE']");
    if (obFieldFeedbackEmployee.length > 0) {
        hideField(obFieldFeedbackEmployee)
        $(prepHtmlContent(buildFeedBackForm())).insertBefore(".bizproc-table-main");
        countingMiddle();
        scoreWorkMentor();
    }

    $('body').on('change', '.scores', function () {
        countingMiddle();
    });

    $('body').on('change', '[name="score"]', function () {
        scoreWorkMentor();
    });

    $('body').on('click', '[name="approve"]', function () {
        if (obFieldFeedbackEmployee.length > 0) {
            elemFormToText();
            addContentToField(obFieldFeedbackEmployee, $('#feedback-employee').html());
        }
    })
})

/*Отчет по итогам наставничества*/
$('body').ready(function () {
    function buildMentorReport() {
        let sCharacteristic = '';
        let arCharacteristic = [
            `освоение и использование лицом, в отношении которого осуществлялось наставничество, в практической деятельности нормативных правовых актов, регламентирующих 
            исполнение должностных обязанностей, умение применять полученные теоретические знания в служебной деятельности;`,
            `положительная мотивация к профессиональной деятельности и профессиональному развитию, самостоятельность и инициативность в служебной деятельности;`,
            `самостоятельность лица, в отношении которого осуществлялось наставничество, при принятии решений и выполнении им должностных обязанностей;`,
            `дисциплинированность и исполнительность при выполнении поручений, связанных со служебной деятельностью.`
        ];
        arCharacteristic.forEach(function (v,i) {
            sCharacteristic += `<div class='char-item'><input type="checkbox" id="char_${i}" class='form-check-input'><label for="char_${i}">${v}</label></div>`;
        });

        sCharacteristic = `<div id="characteristic"><p>Краткая  характеристика,  отражающая личные и деловые качества наставляемого, в т.ч.:</p>${sCharacteristic}</div>`;

        let sContent = `<form id='report-mentor'>
            <p class="bp-task-block-title">Перечень   основных   мероприятий,   выполненных   наставляемым в период наставничества.</p>
            <table border="1" class='table table-striped bg-light'>
                <thead>
                    <tr>
                        <td>Наименование поручений, подготовленных проектов документов, выполненных работ (заданий)</td>
                        <td>Количество (или период исполнения)</td>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
            <textarea class = 'form-control' name="score-comment" style="display: none"></textarea>
        </form>
        
        <form id='report-mentor-new' class='mt-2 table table-striped bg-light'>
            <textarea id="question_sting" class = 'col-md-8 form-control mb-1 float-left' placeholder='Наименование поручений, подготовленных проектов документов, выполненных работ (заданий)'></textarea>
            <textarea id="score_sting" class = 'col-md-4 form-control mb-1' placeholder='Количество (или период исполнения)'></textarea>
        </form>
        
        <a href='#' class='bp-button bp-button bp-button-accept' id='add-mentor'>Добавить новую строку</a>
        <div class="mb-5"></div>
        ${sCharacteristic}
        <div class="mt-5 bp-task-block-title">Оценка наставляемого наставником:
            <select class = 'form-control' name="score">
                <option value="1">готов</option>
                <option value="2">готов при условии дополнительного сопровождения</option>
                <option value="3">не готов</option>
            </select>
        </div>
        <div class="mb-5"></div>
        <style>
        #report-mentor tbody td:nth-child(2) {
            width: 10%;
        }
        #report-mentor tbody td:nth-child(1) {
            width: 90%;
        }
        #report-mentor table {
            width: 100%;
        }
        .char-item {
            margin: 20px;
        }
        table,tr,td {
            border: unset !important;
        }
        [type="checkbox"] {
            margin-right: 5px;
        }
        .delete-row {
            color: red;
            font-weight: 700;
            width: 100%;
            display: block;
        }
        </style>
        `;

        return sContent;
    }

    function addNewMentorString() {
        let sTr = `<tr>
                    <td>${$('#question_sting').val()}<a href='#' class="delete-row">удалить</a></td>
                    <td>${$('#score_sting').val()}</td>
                </tr>`;

        $('#report-mentor tbody').append(sTr);
        $('#report-mentor-new')[0].reset();
    }

    $('body').on('click', '#add-mentor', function (e) {
        e.preventDefault();
        addNewMentorString();
    });

    obFieldMentorReport = $("[name = 'bpriact_MENTOR_REPORT']");
    if (obFieldMentorReport.length > 0) {
        hideField(obFieldMentorReport)
        $(prepHtmlContent(buildMentorReport())).insertBefore(".bizproc-table-main");
    }

    $('body').on('click', '[name="approve"]', function () {
        if (obFieldMentorReport.length > 0) {
            elemFormToText();
            addContentToField(obFieldMentorReport, $('#report-mentor').html());
        }
    })
})
