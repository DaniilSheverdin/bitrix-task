$(document).ready(function () {
    var baseInput = $('input[name="bpriact_ZADACHY"]');
    var baseInputNUIS = $('input[name="bpriact_ZADACHY_NUIS"]');
    var baseInputCOMPLITE_COURSES = $('input[name="bpriact_COMPLITE_COURSES"]');
    var baseInputCOMPLITE_TASKS = $('input[name="bpriact_COMPLITE_TASKS"]');
    var baseInputZADACHY_CONTINUED = $('input[name="bpriact_ZADACHY_CONTINUED"]');

    $(document).on(
        'click',
        '#add-tsk',
        function () {
            let max = $(this).attr('data-max');
            let tplMC = $(this).closest('.bizproc-field-value').find('[data-badd="1"]').eq(0);
            if($(this).closest('.bizproc-field-value').find('[data-badd="1"]').length <= max) {
                let edomClone = tplMC.clone();
                tplMC.parent().append(edomClone);
                edomClone.find('input, textarea').val('');
            }
        }
    );

    $(document).on('click', '._add_consistlist .closed', function () {
        let block = $(this).closest('.bizproc-field-value').find('[data-badd="1"]');
        if (block.length > 1) {
            $(this).closest('[data-badd="1"]').remove();
        }
    });

    $(document).on('input keyup', '._add_consistlist input, ._add_consistlist textarea', function() {
        let _this = $(this);
        let base = _this.closest('._add_consistlist');

        let $listZadach = [];
        base.find("[data-badd=\"1\"]").each(function() {
            $listZadach.push({
                name: $.trim($(this).find('input[name="__ZADACHY_NAME[]"]').val()),
                description: $.trim($(this).find('textarea[name="__ZADACHY_DESCRIPTION[]"]').val()),
                date: $.trim($(this).find('input[name="__ZADACHY_DATE[]"]').val()),
            });
        });

        let strZadach = JSON.stringify($listZadach);
        baseInput.prop('value', strZadach);
        baseInputNUIS.prop('value', strZadach);
        baseInputZADACHY_CONTINUED.prop('value', strZadach);
        console.info(baseInputZADACHY_CONTINUED.val());
    });

    if(baseInput.length > 0 || baseInputNUIS.length > 0) {
        baseInput.addClass('d-none');
        baseInputNUIS.addClass('d-none');

        if (baseInputNUIS.length > 0) {
            let $strNuisTask = '';
            let arrListTasks = JSON.parse(baseInputNUIS.val());

            if(arrListTasks.length > 0) {
                $strNuisTask += '<div class="col-sm-12 px-0 _add_consistlist">\n';
                for(let item of arrListTasks) {
                    $strNuisTask += '<div class="col-12 mb-3" data-badd="1">\n'+
                        '                     <div class="row pb-3 my-2 position-relative">\n' +
                        '                         <div class="closed position-absolute text-danger" style="right: 5px; top: -15px; width: 20px; height: 20px; cursor: pointer; font-size: 20px;">×</div>\n' +
                        '                     </div>\n' +
                        '                     <div class="row">\n' +
                        '                         <div class="col-xl-4 mb-3">\n' +
                        '                             Назание\n' +
                        '                         </div>\n' +
                        '                         <div class="col-xl-8 mb-3">\n' +
                        '                             <input class="form-control" name="__ZADACHY_NAME[]" id="bp__ZADACHY_NAME" value="'+ item.name +'" type="text"\n' +
                        '                                />\n' +
                        '                         </div>\n' +
                        '                     </div>\n' +
                        '                     <div class="row">\n' +
                        '                         <div class="col-xl-4 mb-3">\n' +
                        '                             Описание\n' +
                        '                         </div>\n' +
                        '                         <div class="col-xl-8 mb-3">\n' +
                        '                             <textarea class="form-control" name="__ZADACHY_DESCRIPTION[]" id="bp__ZADACHY_DESCRIPTION">'+ item.description +'</textarea>\n' +
                        '                         </div>\n' +
                        '                     </div>\n' +
                        '                     <div class="row">\n' +
                        '                         <div class="col-xl-4 mb-3">\n' +
                        '                             Срок выполнения\n' +
                        '                         </div>\n' +
                        '                         <div class="col-xl-4">\n' +
                        '                             <input class="form-control" name="__ZADACHY_DATE[]" id="bp__ZADACHY_DATE" value="'+ item.date +'" type="date"\n' +
                        '                                />\n' +
                        '                         </div>\n' +
                        '                     </div>\n' +
                        '                 </div>\n';
                }

                $strNuisTask += '</div>' +
                    '<div class="col-12 py-2 px-0 mb-4">&nbsp;<button id="add-tsk" data-max="10" type="button" title="Добавить задачу на стажировку" class="btn btn-primary float-right">+</button></div>';
            }
            let countTasks = baseInputNUIS.parent().find('[data-badd]').length;

            if(countTasks < 10) {
                baseInputNUIS.parent().append($strNuisTask);
            }
        } else {

            let countTasks = baseInput.parent().find('[data-badd]').length;

            if(countTasks < 10) {
                baseInput.parent().append('<div class="col-sm-12 px-0 _add_consistlist">\n' +
                    '                <div class="col-12 mb-3" data-badd="1">\n' +
                    '                    <div class="row pb-3 my-2 position-relative">\n' +
                    '                        <div class="closed position-absolute text-danger" style="right: 5px; top: -15px; width: 20px; height: 20px; cursor: pointer; font-size: 20px;">×</div>\n' +
                    '                    </div>\n' +
                    '                    <div class="row">\n' +
                    '                        <div class="col-xl-4 mb-3">\n' +
                    '                            Назание\n' +
                    '                        </div>\n' +
                    '                        <div class="col-xl-8 mb-3">\n' +
                    '                            <input class="form-control" name="__ZADACHY_NAME[]" id="bp__ZADACHY_NAME" value="" type="text"\n' +
                    '                               />\n' +
                    '                        </div>\n' +
                    '                    </div>\n' +
                    '                    <div class="row">\n' +
                    '                        <div class="col-xl-4 mb-3">\n' +
                    '                            Описание\n' +
                    '                        </div>\n' +
                    '                        <div class="col-xl-8 mb-3">\n' +
                    '                            <textarea class="form-control" name="__ZADACHY_DESCRIPTION[]" id="bp__ZADACHY_DESCRIPTION"></textarea>\n' +
                    '                        </div>\n' +
                    '                    </div>\n' +
                    '                    <div class="row">\n' +
                    '                        <div class="col-xl-4 mb-3">\n' +
                    '                            Срок выполнения\n' +
                    '                        </div>\n' +
                    '                        <div class="col-xl-4">\n' +
                    '                            <input class="form-control" name="__ZADACHY_DATE[]" id="bp__ZADACHY_DATE" value="" type="date"\n' +
                    '                               />\n' +
                    '                        </div>\n' +
                    '                    </div>\n' +
                    '                </div>\n' +
                    '            </div>' +
                    '            <div class="col-12 py-2 px-0 mb-4">&nbsp;<button id="add-tsk" data-max="10" type="button" title="Добавить задачу на стажировку" class="btn btn-primary float-right">+</button></div>'
                );
            }
        }
    }

    if(baseInputCOMPLITE_COURSES.length > 0 || baseInputCOMPLITE_TASKS.length > 0) {
        baseInputCOMPLITE_COURSES.addClass('d-none');
        baseInputCOMPLITE_TASKS.addClass('d-none');

        let strJSONCourses = baseInputCOMPLITE_COURSES.val();
        let strJSONTasks = baseInputCOMPLITE_TASKS.val();

        if(strJSONCourses.length > 0 && strJSONTasks.length > 0) {
            let arCourses = JSON.parse(strJSONCourses);
            let arTasks = JSON.parse(strJSONTasks);

            let htmlCourses = '';

            htmlCourses += '<div class="col-sm-12 px-0 py-3 __check_courses">\n' +
                '<ul class="list-unstyled">';
            for(let cItem in arCourses) {
                htmlCourses += '<li>' +
                   '<label class="w-100" for="_p_check_courses-'+cItem+'"><input type="checkbox" id="_p_check_courses-'+cItem+'" name="_p_check_courses[]" value="'+ cItem +'" /> ' + arCourses[cItem].name + '</label>\n' +
                '</li>';
            }
            htmlCourses += '</ul>' +
            '</div>\n';

            baseInputCOMPLITE_COURSES.parent().append(htmlCourses);

            let htmlTasks = '';

            htmlTasks += '<div class="col-sm-12 px-0 py-3 __check_tasks">\n' +
                '<ul class="list-unstyled">';
            for(let cItem in arTasks) {
                htmlTasks += '<li>' +
                    '<label class="w-100" for="_p_check_tasks-'+cItem+'"><input type="checkbox" id="_p_check_tasks-'+cItem+'" name="_p_check_tasks[]" value="'+cItem+'" /> ' + arTasks[cItem].name + '</label>\n' +
                    '</li>';
            }
            htmlTasks += '</ul>' +
                '</div>\n';

            baseInputCOMPLITE_TASKS.parent().append(htmlTasks);
        }

        $(document).on('input change', '.__check_courses input[type="checkbox"]', function() {
            let _this = $(this);

            let complite = _this.is(':checked');
            let cVal = _this.val();

            let $listCoursesComplite = {};

            let strJSONCourses = baseInputCOMPLITE_COURSES.val();

            if(strJSONCourses.length > 0) {
                let arCourses = JSON.parse(strJSONCourses);

                for(let item in arCourses) {
                    if(parseInt(item) === parseInt(cVal)) {
                        $listCoursesComplite[item] = {
                            name: arCourses[item].name,
                            complite: complite
                        }
                    } else {
                        $listCoursesComplite[item] = {
                            name: arCourses[item].name,
                            complite: arCourses[item].complite
                        }
                    }
                }

            }

            let strCourses = JSON.stringify($listCoursesComplite);
            baseInputCOMPLITE_COURSES.prop('value', strCourses);
        });

        $(document).on('input change', '.__check_tasks input[type="checkbox"]', function() {
            let _this = $(this);

            let complite = _this.is(':checked');
            let cVal = _this.val();

            let $listTasksComplite = {};

            let strJSONTasks = baseInputCOMPLITE_TASKS.val();

            if(strJSONTasks.length > 0) {
                let arTasks = JSON.parse(strJSONTasks);

                for(let item in arTasks) {
                    if(parseInt(item) === parseInt(cVal)) {
                        $listTasksComplite[item] = {
                            name: arTasks[item].name,
                            date_deadline: arTasks[item].date_deadline,
                            complite: complite
                        }
                    } else {
                        $listTasksComplite[item] = {
                            name: arTasks[item].name,
                            date_deadline: arTasks[item].date_deadline,
                            complite: arTasks[item].complite ? arTasks[item].complite : false
                        }
                    }
                }

            }

            let strTasks = JSON.stringify($listTasksComplite);
            baseInputCOMPLITE_TASKS.prop('value', strTasks);
        });
    }

    $(document).on('change', "select[name='bpriact_STAZH_RESOLUTION']", function() {
        if($(this).val() == 'продлить') {
            $(this).closest('table').find('tr').each(function(i) {
                $(this).removeClass('d-none').addClass('d-table-row');
            });
        } else {
            $(this).closest('table').find('tr').each(function(i) {
                if(i === 0) {
                    $(this).removeClass('d-none').addClass('d-table-row');
                } else {
                    $(this).removeClass('d-table-row').addClass('d-none');
                }
            });
        }
    });

    if(baseInputZADACHY_CONTINUED.length > 0) {
        baseInputZADACHY_CONTINUED.addClass('d-none');

        let countDopTasks = baseInput.parent().find('[data-badd]').length;

        if(countDopTasks < 5) {
            baseInputZADACHY_CONTINUED.parent().append('<div class="col-sm-12 px-0 _add_consistlist">\n' +
                '                <div class="col-12 mb-3" data-badd="1">\n' +
                '                    <div class="row pb-3 my-2 position-relative">\n' +
                '                        <div class="closed position-absolute text-danger" style="right: 5px; top: -15px; width: 20px; height: 20px; cursor: pointer; font-size: 20px;">×</div>\n' +
                '                    </div>\n' +
                '                    <div class="row">\n' +
                '                        <div class="col-xl-4 mb-3">\n' +
                '                            Назание\n' +
                '                        </div>\n' +
                '                        <div class="col-xl-8 mb-3">\n' +
                '                            <input class="form-control" name="__ZADACHY_NAME[]" id="bp__ZADACHY_NAME" value="" type="text"\n' +
                '                               />\n' +
                '                        </div>\n' +
                '                    </div>\n' +
                '                    <div class="row">\n' +
                '                        <div class="col-xl-4 mb-3">\n' +
                '                            Описание\n' +
                '                        </div>\n' +
                '                        <div class="col-xl-8 mb-3">\n' +
                '                            <textarea class="form-control" name="__ZADACHY_DESCRIPTION[]" id="bp__ZADACHY_DESCRIPTION"></textarea>\n' +
                '                        </div>\n' +
                '                    </div>\n' +
                '                    <div class="row">\n' +
                '                        <div class="col-xl-4 mb-3">\n' +
                '                            Срок выполнения\n' +
                '                        </div>\n' +
                '                        <div class="col-xl-4">\n' +
                '                            <input class="form-control" name="__ZADACHY_DATE[]" id="bp__ZADACHY_DATE" value="" type="date"\n' +
                '                               />\n' +
                '                        </div>\n' +
                '                    </div>\n' +
                '                </div>\n' +
                '            </div>' +
                '            <div class="col-12 py-2 px-0 mb-4">&nbsp;<button id="add-tsk" data-max="5" type="button" title="Добавить задачу на стажировку" class="btn btn-primary float-right">+</button></div>'
            );
        }
    }
});
