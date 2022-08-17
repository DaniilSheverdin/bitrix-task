BX.Tasks.GridActions = {
    gridId: null,
    groupSelector: null,
    registeredTimerNodes: {},
    defaultPresetId: '',

    initPopupBaloon: function(mode, searchField, groupIdField) {

        this.groupSelector = null;

        BX.bind(BX(searchField + '_control'), 'click', BX.delegate(function(){

            if (!this.groupSelector)
            {
                this.groupSelector = new BX.Tasks.Integration.Socialnetwork.NetworkSelector({
                    scope: BX(searchField + '_control'),
                    id: 'group-selector-' + this.gridId,
                    mode: mode,
                    query: false,
                    useSearch: true,
                    useAdd: false,
                    parent: this,
                    popupOffsetTop: 5,
                    popupOffsetLeft: 40
                });

                this.groupSelector.bindEvent('item-selected', BX.delegate(function(data){
                    BX(searchField + '_control').value = BX.util.htmlspecialcharsback(data.nameFormatted) || '';
                    BX(groupIdField + '_control').value = data.id || '';
                    this.groupSelector.close();
                }, this));
            }

            this.groupSelector.open();

        }, this));
    },
    filter: function(tag)
    {
        var filterManager = BX.Main.filterManager.getById(this.gridId);
        if(!filterManager)
        {
            alert('BX.Main.filterManager not initialised');
            return;
        }

        var fields = filterManager.getFilterFieldsValues();
        fields.TAG = tag;

        var filterApi = filterManager.getApi();
        filterApi.setFields(fields);
        filterApi.apply();
    },

    action: function (code, taskId, args) {
        switch (code) {
            default:
                this.doAction(code, taskId, args);
                break;
            case 'add2Timeman':
                if(BX.addTaskToPlanner)
                    BX.addTaskToPlanner(taskId);
                else if(window.top.BX.addTaskToPlanner)
                    window.top.BX.addTaskToPlanner(taskId);
                break;
            case 'delete':
                // BX.Tasks.confirmDelete(BX.message('TASKS_COMMON_TASK_ALT_A')).then(function () {
                this.doAction(code, taskId, args);

                // }.bind(this));
                break;
        }
    },
    confirmGroupAction: function (gridId) {
        BX.Tasks.confirm(BX.message('TASKS_CONFIRM_GROUP_ACTION')).then(function () {
            BX.Main.gridManager.getById(gridId).instance.sendSelected();
            counterUpdate();
        }.bind(this));
    },

    doAction: function (code, taskId, args) {
        args = args || {};
        args['id'] = taskId;

        // add action
        this.getQuery(code).add('task.' + code.toLowerCase(), args, {}, BX.delegate(function (errors, data) {

            if (!errors.checkHasErrors()) {
                if (data.OPERATION == 'task.delete') {
                    BX.Tasks.Util.fireGlobalTaskEvent('DELETE', {ID: taskId});

                    BX.UI.Notification.Center.notify({
                        content: BX.message('TASKS_DELETE_SUCCESS')
                    });
                }

                if (!this.gridId) {
                    window.location.href = window.location.href;
                    return;
                }

                this.reloadRow(taskId);
            }
        }, this));

    },
    reloadGrid: function()
    {

        if (BX.Bitrix24 && BX.Bitrix24.Slider && BX.Bitrix24.Slider.getLastOpenPage())
        {
            BX.Bitrix24.Slider.destroy(
                BX.Bitrix24.Slider.getLastOpenPage().getUrl()
            );
        }

        var reloadParams = { apply_filter: 'Y', clear_nav: 'Y' };
        var gridObject = BX.Main.gridManager.getById(this.gridId);

        if (gridObject.hasOwnProperty('instance'))
        {
            gridObject.instance.reloadTable('POST', reloadParams);
        }

        var filterObject = BX.Main.filterManager.getById(this.gridId);
        var fields = filterObject.getFilterFieldsValues();
        var roleid = fields.ROLEID || 'view_all';
        BX.onCustomEvent("Tasks.Toolbar.Reload", [roleid]); //FIRE
    },
    reloadRow: function(taskId)
    {
        reloadParams = {apply_filter: 'Y', clear_nav: 'Y'};
        gridObject = BX.Main.gridManager.getById(this.gridId);
        if (gridObject.hasOwnProperty('instance'))
            gridObject.instance.updateRow(taskId.toString());

        var filterObject = BX.Main.filterManager.getById(this.gridId);
        var fields = filterObject.getFilterFieldsValues();
        var roleid = fields.ROLEID || 'view_all';//debugger
        BX.onCustomEvent("Tasks.Toolbar.Reload", [roleid]); //FIRE
    },

    getQuery: function (code) {
        var code = code || '';
        var viewType = BX.message('_VIEW_TYPE');

        var url = '/bitrix/components/bitrix/tasks.base/ajax.php?_CODE=' + code + '&viewType=' + viewType;
        console.log(url);
        if (!this.query) {
            this.query = new BX.Tasks.Util.Query({
                url: url,
                autoExec: true
            });
        }

        return this.query;
    },
    onDeadlineChangeClick: function (taskId, node, curDeadline) {

        curDeadline = curDeadline || (new Date).getDate();

        BX.calendar({
            node: node,
            value: curDeadline,
            form: '',
            bTime: true,
            currentTime: Math.round((new Date()) / 1000) - (new Date()).getTimezoneOffset() * 60,
            bHideTimebar: true,
            callback_after: (function (node, taskId) {
                return function (value, bTimeIn) {
                    var bTime = true;

                    if (typeof bTimeIn !== 'undefined')
                        bTime = bTimeIn;

                    var path = BX.CJSTask.ajaxUrl;
                    BX.CJSTask.ajaxUrl = BX.CJSTask.ajaxUrl + '&_CODE=CHANGE_DEADLINE&viewType=VIEW_MODE_LIST';
                    BX.CJSTask.batchOperations(
                        [
                            {
                                operation: 'CTaskItem::update()',
                                taskData: {
                                    ID: taskId,
                                    DEADLINE: BX.calendar.ValueToString(value, bTime)
                                }
                            }
                        ],
                        {
                            callbackOnSuccess: (function (node, taskId, value) {
                                return function (reply) {

                                    // if (node.parentNode.parentNode.tagName === 'TD')
                                    //     node.parentNode.parentNode.innerHTML = tasksListNS.renderDeadline(taskId, value, true);
                                    // else
                                    //     node.parentNode.innerHTML = tasksListNS.renderDeadline(taskId, value, true);

                                };
                            })(node, taskId, value)
                        }
                    );
                    //BX.CJSTask.ajaxUrl = path;
                    location.href = location.href;
                    //BX.Tasks.GridActions.reloadRow(taskId);

                };
            })(node, taskId)
        });

    },

    onMarkChangeClick: function (taskId, bindElement, currentValues) {
        BX.TaskGradePopup.show(
            taskId,
            bindElement,
            currentValues,
            {
                events: {
                    onPopupClose: this.__onGradePopupClose,
                    onPopupChange: this.__onGradePopupChange
                }
            }
        );

        BX.addClass(bindElement, "task-grade-and-report-selected");

        return false;
    },

    __onGradePopupClose: function () {
        BX.removeClass(this.bindElement, "task-grade-and-report-selected");
    },

    __onGradePopupChange: function () {
        this.bindElement.className = "task-grade-and-report" + (this.listValue !== "NULL" ? " task-grade-" + this.listItem.className : "") + (this.report ? " task-in-report" : "");
        this.bindElement.title = BX.message("TASKS_MARK") + ": " + this.listItem.name;

        BX.Tasks.GridActions.action('update', this.id, {data: {MARK: this.listValue === "NULL" ? "" : this.listValue}});
    },

    renderTimerItem : function (taskId, timeSpentInLogs, timeEstimate, isRunning, taskTimersTotalValue, canStartTimeTracking)
    {
        var className = 'task-timer-inner';
        var timeSpent = timeSpentInLogs + taskTimersTotalValue;
        var canStartTimeTracking = canStartTimeTracking || false;

        if (isRunning)
            className = className + ' task-timer-play';
        else if (canStartTimeTracking)
            className = className + ' task-timer-pause';
        else
            className = className + ' task-timer-clock';

        if ((timeEstimate > 0) && (timeSpent > timeEstimate))
            className = className + ' task-timer-overdue';

        return (
            BX.create("span", {
                props : {
                    id : 'task-timer-block-' + taskId,
                    className : "task-timer-block"
                },
                events : {
                    click : (function(taskId, canStartTimeTracking){
                        return function(){
                            if (BX.hasClass(BX('task-timer-block-inner-' + taskId), 'task-timer-play'))
                            {
                                BX.TasksTimerManager.stop(taskId);

                            }
                            else if (canStartTimeTracking)
                            {
                                BX.TasksTimerManager.start(taskId);
                            }
                        }
                    })(taskId, canStartTimeTracking)
                },
                children : [
                    BX.create("span", {
                        props : {
                            id : 'task-timer-block-inner-' + taskId,
                            className : className
                        },
                        children : [
                            BX.create("span", {
                                props : {
                                    className : 'task-timer-icon'
                                }
                            }),
                            BX.create("span", {
                                props : {
                                    id : 'task-timer-block-value-' + taskId,
                                    className : 'task-timer-time'
                                },
                                text : BX.Tasks.GridActions.renderTimerTimes(timeSpent, timeEstimate, isRunning)
                            })
                        ]
                    })
                ]
            })
        );
    },

    renderTimerTimes : function(timeSpent, timeEstimate, isRunning)
    {
        var str = '';
        var bShowSeconds = false;

        if (isRunning)
            bShowSeconds = true;

        str = BX.Tasks.GridActions.renderSecondsToHHMMSS(timeSpent, bShowSeconds);

        if (timeEstimate > 0)
            str = str + ' / ' + BX.Tasks.GridActions.renderSecondsToHHMMSS(timeEstimate, false);

        return (str);
    },

    renderSecondsToHHMMSS : function(totalSeconds, bShowSeconds)
    {
        var pad = '00';
        var hours = '';
        var minutes = '';
        var seconds = 0;

        if (totalSeconds > 0)
        {
            hours += Math.floor(totalSeconds / 3600);
            minutes += Math.floor(totalSeconds / 60) % 60;
        }
        else
        {
            hours += Math.ceil(totalSeconds / 3600);
            minutes += Math.ceil(totalSeconds / 60) % 60;
        }

        var result = pad.substring(0, 2 - hours.length) + hours
            + ':' + pad.substring(0, 2 - minutes.length) + minutes;

        if (bShowSeconds)
        {
            seconds = '' + totalSeconds % 60;
            result = result + ':' + pad.substring(0, 2 - seconds.length) + seconds;
        }

        return (result);
    },

    redrawTimerNode : function (taskId, timeSpentInLogs, timeEstimate, isRunning, taskTimersTotalValue, canStartTimeTracking)
    {
        var taskTimerBlock = BX('task-timer-block-' + taskId);

        var newTaskTimerBlock = BX.Tasks.GridActions.renderTimerItem(
            taskId,
            timeSpentInLogs,
            timeEstimate,
            isRunning,
            taskTimersTotalValue,
            canStartTimeTracking
        );

        if (taskTimerBlock)
        {
            taskTimerBlock.parentNode.replaceChild(
                newTaskTimerBlock,
                taskTimerBlock
            );
        }
        else
        {
            var container = BX("task-timer-block-container-" + taskId);
            if (container)
            {
                // Unregister callback function for this item (if it exists)
                if (this.registeredTimerNodes[taskId])
                {
                    BX.removeCustomEvent(window, 'onTaskTimerChange', this.registeredTimerNodes[taskId]);
                }

                container.appendChild(newTaskTimerBlock);

                // If row inserted into DOM -> register callback function
                if (BX('task-timer-block-' + taskId))
                {
                    this.registeredTimerNodes[taskId] = this.__getTimerChangeCallback(taskId);
                    BX.addCustomEvent(window, 'onTaskTimerChange', this.registeredTimerNodes[taskId]);
                }

            }
        }
    },

    removeTimerNode : function (taskId)
    {
        var taskTimerBlock = BX('task-timer-block-' + taskId);

        if (this.registeredTimerNodes[taskId])
            BX.removeCustomEvent(window, 'onTaskTimerChange', this.registeredTimerNodes[taskId]);

        if (taskTimerBlock)
            taskTimerBlock.parentNode.removeChild(taskTimerBlock);
    },

    __getTimerChangeCallback : function(selfTaskId)
    {
        var state = null;

        return function(params)
        {
            var switchStateTo   = null;
            var innerTimerBlock = null;

            if (params.action === 'refresh_daemon_event')
            {
                if (params.taskId !== selfTaskId)
                {
                    if (state === 'paused')
                        return;
                    else
                        switchStateTo = 'paused';
                }
                else
                {
                    if (state !== 'playing')
                        switchStateTo = 'playing';

                    BX.Tasks.GridActions.redrawTimerNode(
                        params.taskId,
                        params.data.TASK.TIME_SPENT_IN_LOGS,
                        params.data.TASK.TIME_ESTIMATE,
                        true,	// IS_TASK_TRACKING_NOW
                        params.data.TIMER.RUN_TIME,
                        true
                    );
                }
            }
            else if (params.action === 'start_timer')
            {
                if (
                    (selfTaskId == params.taskId)
                    && params.timerData
                    && (selfTaskId == params.timerData.TASK_ID)
                )
                {
                    switchStateTo = 'playing';
                }
                else
                    switchStateTo = 'paused';	// other task timer started, so we need to be paused
            }
            else if (params.action === 'stop_timer')
            {
                if (selfTaskId == params.taskId)
                    switchStateTo = 'paused';
            }
            else if (params.action === 'init_timer_data')
            {
                if (params.data.TIMER)
                {
                    if (params.data.TIMER.TASK_ID == selfTaskId)
                    {
                        if (params.data.TIMER.TIMER_STARTED_AT > 0)
                            switchStateTo = 'playing';
                        else
                            switchStateTo = 'paused';
                    }
                    else if (params.data.TIMER.TASK_ID > 0)
                    {
                        // our task is not playing now
                        switchStateTo = 'paused';
                    }
                }
            }

            if (switchStateTo !== null)
            {
                innerTimerBlock = BX('task-timer-block-inner-' + selfTaskId);

                if (
                    innerTimerBlock
                    && ( ! BX.hasClass(innerTimerBlock, 'task-timer-clock') )
                )
                {
                    if (switchStateTo === 'paused')
                    {
                        BX.removeClass(innerTimerBlock, 'task-timer-play');
                        BX.addClass(innerTimerBlock, 'task-timer-pause');
                    }
                    else if (switchStateTo === 'playing')
                    {
                        BX.removeClass(innerTimerBlock, 'task-timer-pause');
                        BX.addClass(innerTimerBlock, 'task-timer-play');
                    }
                }

                state = switchStateTo;
            }
        }
    }

};


BX.ready(
    function () {

        if (namesData) {

            function changeData(func) {

                Donut3D.transition("quotesDonut", func, 150, 150, 130, 100, 20, 0);
            }

            function namesDataCreate(array) {
                $('.task_name_item').remove();
                return array.map(function (d) {
                    return {label: d.projects.length, value: d.projects.length, color: d.color};
                });
            }

            function projectsDataCreate(name, array) {
                $('.project_name_item').remove();
                $('.task_name_item').remove();
                var arrProj = [];
                array.map(function (project) {

                    if (project.name === name) {

                        project.projects.map(function (d) {

                            arrProj.push({label: d.tasks.length, value: d.tasks.length, color: d.color});

                            $('.name').each(function () {
                                if ($(this).attr('data-name') === name) {

                                    $(this).parent()
                                        .find('.project_name')
                                        .append(
                                            "<div data-name='" + name + "' data-name-project='" + d.name + "' class='project_name_item'>" +
                                            "<span style=' background-color:" + d.color + "' class='dot_item'></span>" + d.name + "" +
                                            "</div>" +
                                            "<div class='task_item' data-name-project='" + d.name + "'></div>")
                                }
                            })

                        });

                    }
                });

                return arrProj;

            }

            function tasksDataCreate(name, nameProject, array) {
                $('.task_name_item').remove();
                var arrTasks = [];
                array.map(function (project) {

                    if (project.name === name) {

                        project.projects.map(function (task) {

                            if (task.name === nameProject) {

                                task.tasks.map(function (d) {
                                    arrTasks.push({label: d.label, value: 1, color: d.color});

                                    $('.project_name_item').each(function () {
                                        if ($(this).attr('data-name-project') === nameProject) {

                                            $(this).next()
                                                .append(
                                                    "<p data-name-project='" + d.name + "' class='task_name_item'>" +
                                                    "<span style=' background-color:" + d.color + "' class='dot_item'></span>" + d.name + "</p>")

                                        }
                                    })

                                });

                            }

                        });
                    }

                });

                return arrTasks;
            }

            function addLegend(data) {
                return data.map(function (d) {
                    return "<div>" +
                        "<p  class='name' data-name='" + d.name + "' data-projects-count='" + d.projects.length + "'>" +
                        "<span style=' background-color:" + d.color + "' class='dot_item'></span>" + d.name + "</p>" +
                        "<div class='project_name'></div>" +
                        "</div>"
                })
            }


            var svg = d3.select("#graph").append("svg").attr("height", 300);
            svg.append("g").attr("id", "quotesDonut");
            Donut3D.draw("quotesDonut", namesDataCreate(namesData), 150, 150, 130, 100, 20, 0);

            var $legend = $('#legend');
            $legend.append(addLegend(namesData));
            var $name = $('.name');
            $name.on('click', function () {
                if (!$(this).hasClass('open')) {
                    $name.removeClass('open').css({ fontWeight: '400' });
                }
                $(this).toggleClass('open');

                if ($(this).hasClass('open')) {
                    $(this).css({ fontWeight: '600' });

                    var attrName = $(this).attr('data-name');

                    namesData.map(function (el) {
                        if (el.name === attrName) {
                            changeData(projectsDataCreate(attrName, namesData));
                        }
                    })

                } else {

                    $(this).css({ fontWeight: '400' });
                    $('.project_name_item').remove();
                    Donut3D.draw("quotesDonut", namesDataCreate(namesData), 150, 150, 130, 100, 20, 0);

                }

            });


            var $projectName = $('.project_name_item');
            $projectName.live('click', function () {

                if (!$(this).hasClass('open-project')) {
                    $('.project_name_item').removeClass('open-project').css({ fontWeight: '400' });
                }
                $(this).toggleClass('open-project');

                if ($(this).hasClass('open-project')) {
                    $(this).css({ fontWeight: '600' });

                    var attrNameProject = $(this).attr('data-name-project');
                    var attrName = $(this).attr('data-name');
                    namesData.map(function (name) {

                        name.projects.map(function (el) {

                            if (el.name === attrNameProject) {
                                changeData(tasksDataCreate(attrName, attrNameProject, namesData));
                            }
                        });

                    })

                } else {
                    $(this).css({ fontWeight: '400' });

                    var attrName = $(this).attr('data-name');
                    namesData.map(function (el) {
                        if (el.name === attrName) {
                            changeData(projectsDataCreate(attrName, namesData));
                        }
                    })
                }
            });


            BX.addCustomEvent('BX.Main.Filter:apply', BX.delegate(function (command, params, filterObj) {

                location.href = location.href;

            }));


            $('.users__list_title').live('click', function () {
                $(this).next().toggle('fast');
                $(this).find('.triangle_grey').toggleClass('rotate90')
            });
            $('.projects__list_title').live('click', function () {
                $(this).find('.triangle_grey').toggleClass('rotate90');
                var group = $(this).attr('data-group');
                $(this).parent().find('.tasks__list_container').each(function () {
                    console.log('group-task', $(this).parent().find('.tasks__list_container').attr('data-group'));
                    if ($(this).attr('data-group') === group) {
                        $(this).toggle('fast');
                    }
                });
            });

        }
    }
);